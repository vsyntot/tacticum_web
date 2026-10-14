#!/usr/bin/env python3
import copy
from datetime import datetime,timedelta,timezone
import importlib.util
import json
import os
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch

spec=importlib.util.spec_from_file_location('rotate',Path(__file__).with_name('crypto-key-rotation-apply.py'))
rotate=importlib.util.module_from_spec(spec);spec.loader.exec_module(rotate)


class FakeSystem:
    def __init__(self): self.states=copy.deepcopy(rotate.EXPECTED);self.actions=[];self.fail_stop=None;self.fail_start=None
    def state(self,unit): return self.states[unit]
    def php_workers(self): return 0
    def action(self,action,unit):
        self.actions.append((action,unit))
        self.states[unit]='inactive' if action=='stop' else 'active'
        if action=='stop' and unit==self.fail_stop: raise TimeoutError('synthetic timeout after stop')
        if action=='start' and unit==self.fail_start: raise TimeoutError('synthetic restart failure')


class FakeHelper:
    def references(self,root): return []
    def counts(self): return {'synthetic_table':0}
    def verify_candidate(self,*args): pass


class RotationTests(unittest.TestCase):
    def setUp(self):
        self.temp=tempfile.TemporaryDirectory(prefix='crypto-apply-test-');self.addCleanup(self.temp.cleanup)
        root=Path(self.temp.name).resolve()
        self.web=root/'web';(self.web/'bitrix').mkdir(parents=True)
        self.state=root/'private';self.state.mkdir(mode=0o700)
        self.target=self.web/'bitrix/.settings.php';self.target.write_bytes(b'SYNTHETIC_TEST_ONLY_before');self.target.chmod(0o600)
        (self.state/'settings.before.php').write_bytes(self.target.read_bytes())
        (self.state/'settings.candidate.php').write_bytes(b'SYNTHETIC_TEST_ONLY_after')
        for f in self.state.iterdir():f.chmod(0o600)
        self.system=FakeSystem();self.helper=FakeHelper()
        self.original=self.target.read_bytes()
        preparation={'target':str(self.target),'before_sha256':rotate.digest(self.original),
            'candidate_sha256':rotate.digest((self.state/'settings.candidate.php').read_bytes()),
            'uid':os.getuid(),'gid':os.getgid(),'mode':'0600','reference_files':[],
            'table_row_counts':{'synthetic_table':0},'restore_rehearsal':'passed','candidate_semantics_and_cipher':'passed'}
        self.plan={'preparation':preparation,'stop_units':rotate.UNITS,'expected_units':rotate.EXPECTED,'max_quiescent_seconds':120}
        self.plan['plan_id']=rotate.digest(rotate.canonical(self.plan))
        self.approval={'plan_id':self.plan['plan_id'],'approved_by':'SYNTHETIC_FIXTURE','approval_source':'unit_test',
            'allow_service_outage':True,'release_class':'STATEFUL','apply':True,
            'expires_at':(datetime.now(timezone.utc)+timedelta(minutes=5)).isoformat()}
        p=patch.object(rotate,'ROOT',self.web);p.start();self.addCleanup(p.stop)

    def execute(self):return rotate.execute(self.state,self.plan,self.approval,self.helper,self.system)

    def test_apply_and_restart_order_without_auto_verification(self):
        result=self.execute()
        self.assertEqual(result['status'],'applied_unverified')
        self.assertEqual(self.target.read_bytes(),b'SYNTHETIC_TEST_ONLY_after')
        self.assertEqual(self.system.actions,[('stop',u) for u in rotate.UNITS]+[('start',u) for u in reversed(rotate.UNITS)])
        self.assertEqual(self.target.stat().st_mode & 0o777,0o600)
        with self.assertRaisesRegex(ValueError,'prior_attempt'):self.execute()

    def test_missing_outage_approval_stops_before_actions(self):
        self.approval['allow_service_outage']=False
        with self.assertRaisesRegex(ValueError,'outage'):self.execute()
        self.assertEqual(self.system.actions,[])
        self.assertEqual(self.target.read_bytes(),self.original)

    def test_stop_timeout_restores_even_uncertain_unit(self):
        self.system.fail_stop='httpd'
        with self.assertRaises(TimeoutError):self.execute()
        self.assertEqual(self.system.states,rotate.EXPECTED)
        self.assertEqual(self.target.read_bytes(),self.original)
        self.assertEqual(json.loads((self.state/'rotation-journal.json').read_text())['status'],'failed_requires_review')

    def test_new_encrypted_rows_abort_under_maintenance(self):
        with patch.object(self.helper,'counts',side_effect=[{'synthetic_table':0},{'synthetic_table':1}]):
            with self.assertRaisesRegex(ValueError,'encrypted_data'):self.execute()
        self.assertEqual(self.system.states,rotate.EXPECTED)
        self.assertEqual(self.target.read_bytes(),self.original)

    def test_expiry_and_input_drift(self):
        self.approval['expires_at']=(datetime.now(timezone.utc)-timedelta(seconds=1)).isoformat()
        with self.assertRaisesRegex(ValueError,'expired'):self.execute()
        self.assertEqual(self.system.actions,[])

        self.approval['expires_at']=(datetime.now(timezone.utc)+timedelta(minutes=5)).isoformat()
        self.target.write_bytes(b'SYNTHETIC_TEST_ONLY_drift')
        with self.assertRaisesRegex(ValueError,'drift'):self.execute()
        self.assertEqual(self.system.actions,[])

    def test_recovery_failure_retains_new_key_and_explicit_journal(self):
        self.system.fail_start='httpd'
        with self.assertRaisesRegex(ValueError,'service_recovery'):self.execute()
        journal=json.loads((self.state/'rotation-journal.json').read_text())
        self.assertEqual(journal['status'],'failed_service_recovery')
        self.assertEqual(journal['restart_failed'],['httpd'])
        self.assertTrue(journal['settings_replaced'])
        self.assertEqual(journal['observed_settings_sha256'],self.plan['preparation']['candidate_sha256'])


if __name__=='__main__':unittest.main()
