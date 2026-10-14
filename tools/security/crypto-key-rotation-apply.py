#!/usr/bin/env python3
"""Apply an exactly approved key rotation during a bounded quiescent window.

The root-only state directory contains execution-plan.json and approval.json.
No application writes occur without approval. Never restores an exposed key.
An applied result remains unverified until external smoke/monitoring and BASE.
"""
import argparse
from contextlib import ExitStack
from datetime import datetime, timezone
import fcntl
import hashlib
import importlib.util
import json
import os
from pathlib import Path
import re
import signal
import stat
import subprocess
import tempfile
import time

ROOT = Path('/home/bitrix/www')
UNITS = ['crond', 'nginx', 'httpd']
EXPECTED = {'crond':'active', 'nginx':'active', 'httpd':'active', 'php-fpm':'inactive'}
LOCKS = ['/run/lock/tacticum-production.lock', '/run/lock/tacticum-adp-release.lock']


def require(condition, reason):
    if not condition: raise ValueError(reason)


def digest(data): return hashlib.sha256(data).hexdigest()


def canonical(value): return json.dumps(value, sort_keys=True, separators=(',', ':')).encode()


def atomic_json(file, value):
    fd, name = tempfile.mkstemp(prefix='.journal-', dir=file.parent)
    try:
        with os.fdopen(fd, 'wb') as out:
            out.write(canonical(value)); out.flush(); os.fsync(out.fileno())
        os.replace(name, file)
        directory = os.open(file.parent, os.O_RDONLY)
        try: os.fsync(directory)
        finally: os.close(directory)
    finally:
        Path(name).unlink(missing_ok=True)


class System:
    def state(self, unit):
        return subprocess.run(['systemctl','is-active',unit],capture_output=True,text=True,timeout=15).stdout.strip()

    def action(self, action, unit):
        subprocess.run(['systemctl',action,unit],check=True,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL,timeout=35)

    def php_workers(self):
        names = subprocess.check_output(['ps','-eo','comm='],text=True,timeout=15).splitlines()
        return sum(name.strip().lower().startswith('php') for name in names)


def preflight(state, preparation, helper):
    target = ROOT / 'bitrix/.settings.php'
    require(ROOT.resolve() == ROOT and (ROOT/'bitrix').resolve() == ROOT/'bitrix'
            and not target.is_symlink(), 'unsafe_live_path')
    require(not (ROOT/'bitrix/.settings_extra.php').exists()
            and not (ROOT/'bitrix/.settings_extra.php').is_symlink(), 'settings_override_present')
    require(digest(target.read_bytes()) == preparation['before_sha256'], 'live_settings_drift')
    info = target.stat()
    require([info.st_uid,info.st_gid,format(stat.S_IMODE(info.st_mode),'04o')] ==
            [preparation['uid'],preparation['gid'],preparation['mode']], 'live_metadata_drift')
    for name, field in [('settings.before.php','before_sha256'),('settings.candidate.php','candidate_sha256')]:
        file = state/name
        require(not file.is_symlink() and stat.S_IMODE(file.stat().st_mode)==0o600, 'unsafe_private_config')
        require(digest(file.read_bytes())==preparation[field], 'private_config_drift')
    require(helper.references(ROOT)==preparation['reference_files'], 'crypto_source_drift')
    rows = helper.counts()
    require(rows == preparation['table_row_counts'] and all(v in (0,None) for v in rows.values()), 'encrypted_data_changed')
    helper.verify_candidate(ROOT,state/'settings.before.php',state/'settings.candidate.php')


def execute(state, plan, approval, helper, system):
    payload = {k:v for k,v in plan.items() if k != 'plan_id'}
    require(digest(canonical(payload)) == plan.get('plan_id') == approval.get('plan_id'), 'approval_plan_mismatch')
    require(approval.get('approved_by') and approval.get('approval_source') and approval.get('allow_service_outage') is True,
            'explicit_outage_approval_required')
    require(approval.get('release_class') == 'STATEFUL' and approval.get('apply') is True, 'stateful_approval_required')
    expiry = datetime.fromisoformat(approval['expires_at'].replace('Z','+00:00'))
    remaining = (expiry-datetime.now(timezone.utc)).total_seconds()
    require(0 < remaining <= 3600, 'approval_expired_or_too_long')
    require(plan['stop_units']==UNITS and plan['expected_units']==EXPECTED and plan['max_quiescent_seconds']==120,
            'unsupported_quiescence_contract')
    require(not (state/'rotation-journal.json').exists(), 'prior_attempt_requires_manual_review')
    preparation=plan['preparation']
    require(preparation['target']==str(ROOT/'bitrix/.settings.php'), 'unexpected_target')
    require(preparation['restore_rehearsal']=='passed' and preparation['candidate_semantics_and_cipher']=='passed', 'preparation_not_verified')
    require({name:system.state(name) for name in EXPECTED} == EXPECTED, 'service_state_drift')
    preflight(state, preparation, helper)
    require(system.php_workers()==0, 'php_cli_jobs_active')
    journal={'plan_id':plan['plan_id'],'status':'prepared','stopped':[],'restarted':[], 'settings_replaced':False}
    atomic_json(state/'rotation-journal.json',journal)
    restore_errors=[]
    original_error=None
    temporary=None
    try:
        started=time.monotonic()
        for unit in UNITS:
            # Record intent first: a timeout can stop a unit without reporting success.
            journal['stopped'].append(unit); atomic_json(state/'rotation-journal.json',journal)
            system.action('stop',unit)
        require(all(system.state(unit)=='inactive' for unit in UNITS) and system.php_workers()==0, 'php_not_quiescent')
        preflight(state, preparation, helper)
        require(time.monotonic()-started < plan['max_quiescent_seconds'], 'quiescent_window_expired')
        target=ROOT/'bitrix/.settings.php'
        fd, temporary=tempfile.mkstemp(prefix='.settings-rotation-',suffix='.php',dir=target.parent)
        with os.fdopen(fd,'wb') as out:
            out.write((state/'settings.candidate.php').read_bytes()); out.flush(); os.fsync(out.fileno())
            os.fchown(out.fileno(),preparation['uid'],preparation['gid'])
            os.fchmod(out.fileno(),int(preparation['mode'],8))
        require(digest(Path(temporary).read_bytes())==preparation['candidate_sha256'], 'replacement_copy_mismatch')
        require(digest(target.read_bytes())==preparation['before_sha256'], 'last_moment_settings_drift')
        journal['status']='replacing'; atomic_json(state/'rotation-journal.json',journal)
        os.replace(temporary,target); temporary=None
        journal['settings_replaced']=True
        directory=os.open(target.parent,os.O_RDONLY)
        try: os.fsync(directory)
        finally: os.close(directory)
        require(digest(target.read_bytes())==preparation['candidate_sha256'], 'post_write_mismatch')
        journal['status']='applied_unverified'
    except BaseException as error:
        original_error=error
        journal['status']='failed_requires_review'
        journal['failure_type']=type(error).__name__
    finally:
        # Signals must not interrupt service recovery. SIGKILL requires operator recovery.
        if hasattr(signal,'SIGALRM'): signal.alarm(0)
        if temporary: Path(temporary).unlink(missing_ok=True)
        for unit in reversed(journal['stopped']):
            try:
                system.action('start',unit)
                require(system.state(unit)=='active', 'service_not_active')
                journal['restarted'].append(unit)
            except BaseException:
                restore_errors.append(unit)
        if restore_errors: journal['status']='failed_service_recovery'; journal['restart_failed']=restore_errors
        try: journal['observed_settings_sha256']=digest((ROOT/'bitrix/.settings.php').read_bytes())
        except OSError: journal['observed_settings_sha256']=None
        atomic_json(state/'rotation-journal.json',journal)
    require(not restore_errors, 'service_recovery_required')
    if original_error: raise original_error
    return journal


def main():
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--state',type=Path,required=True)
    args=parser.parse_args(); state=args.state
    require(os.geteuid()==0 and state.resolve()==state and state.parent==Path('/root/tacticum-releases')
            and re.fullmatch('MSCI-SEC-[A-Za-z0-9-]+',state.name), 'private_release_directory_required')
    require(stat.S_IMODE(state.stat().st_mode)==0o700 and state.stat().st_uid==0,'unsafe_release_directory')
    plan=json.loads((state/'execution-plan.json').read_text())
    helper_file=state/'crypto-key-rotation.py'
    require(digest(Path(__file__).read_bytes())==plan['operator_sha256'], 'operator_digest_mismatch')
    require(digest(helper_file.read_bytes())==plan['helper_sha256'], 'helper_digest_mismatch')
    spec=importlib.util.spec_from_file_location('rotation_prepare',helper_file)
    helper=importlib.util.module_from_spec(spec);spec.loader.exec_module(helper)
    approval=json.loads((state/'approval.json').read_text())
    def interrupted(*_): raise TimeoutError('rotation_interrupted')
    signal.signal(signal.SIGTERM,interrupted);signal.signal(signal.SIGALRM,interrupted)
    with ExitStack() as locks:
        for file in LOCKS:
            lock=locks.enter_context(open(file,'a'));fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
        # Includes final scans; an overrun aborts and restarts stopped services.
        signal.alarm(180)
        try: result=execute(state,plan,approval,helper,System())
        finally: signal.alarm(0)
    print(json.dumps(result))


if __name__=='__main__':
    try: main()
    except BaseException as error:
        print(json.dumps({'status':'failed','reason':type(error).__name__, 'inspect_private_journal':True}))
        raise SystemExit(1)
