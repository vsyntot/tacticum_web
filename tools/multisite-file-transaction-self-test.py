#!/usr/bin/env python3
"""Actual file operations in temporary roots only; no production credentials."""
import hashlib
import json
import os
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch

from multisite_file_transaction import FileTransaction


class TransactionTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory(prefix='multisite-rehearsal-')
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name).resolve()
        self.roots = {name: self.root / name for name in ['primary', 'divatech']}
        self.state, self.staged = self.root / 'state', self.root / 'staged'
        for path in [*self.roots.values(), self.state, self.staged]:
            path.mkdir(mode=0o700)
        self.tx = FileTransaction(self.roots, self.state, os.getuid(), os.getgid())

    def entry(self, root, path, content, staged=False):
        target = (self.staged / root if staged else self.roots[root]) / path
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_bytes(content)
        target.chmod(0o644)
        return {'root': root, 'path': path, 'type': 'file', 'mode': '0644', 'size': len(content),
                'sha256': hashlib.sha256(content).hexdigest()}

    def changes(self):
        operations = []
        for root, path, before, after in [('primary', 'local/example.php', b'old', b'new'),
                                          ('divatech', 'old.html', b'old page', None),
                                          ('divatech', 'new/path.html', None, b'new page')]:
            operations.append({'root': root, 'path': path, 'status': 'LOCAL_CHANGE',
                'before': self.entry(root, path, before) if before is not None else None,
                'after': self.entry(root, path, after, staged=True) if after is not None else None})
        return operations

    def assert_restored(self):
        self.assertEqual((self.roots['primary'] / 'local/example.php').read_bytes(), b'old')
        self.assertEqual((self.roots['divatech'] / 'old.html').read_bytes(), b'old page')
        self.assertFalse((self.roots['divatech'] / 'new').exists())

    def test_create_replace_delete_explicit_recovery(self):
        result = self.tx.apply(self.changes(), self.staged)
        self.assertEqual(result['status'], 'applied_unverified')
        self.assertFalse(result['productionApproved'])
        self.assertEqual((self.roots['primary'] / 'local/example.php').read_bytes(), b'new')
        self.assertFalse((self.roots['divatech'] / 'old.html').exists())
        self.assertEqual((self.roots['divatech'] / 'new/path.html').read_bytes(), b'new page')
        self.assertEqual(self.tx.recover()['status'], 'recovered')
        self.assert_restored()

    def test_failure_between_roots_then_fresh_process_recovers(self):
        operations = self.changes()
        real = self.tx.apply_one
        def fail_second(op, staged, journal):
            if op['root'] == 'divatech':
                raise OSError('injected failure at second root')
            return real(op, staged, journal)
        with patch.object(self.tx, 'apply_one', side_effect=fail_second):
            with self.assertRaises(OSError):
                self.tx.apply(operations, self.staged)
        self.assertEqual(json.loads(self.tx.journal_file.read_text())['status'], 'failed_requires_recovery')
        restarted = FileTransaction(self.roots, self.state, os.getuid(), os.getgid())
        restarted.recover()
        self.assert_restored()

    def test_recovery_refuses_post_failure_hotfix(self):
        self.tx.apply(self.changes(), self.staged)
        file = self.roots['primary'] / 'local/example.php'
        file.write_bytes(b'new hotfix')
        with self.assertRaisesRegex(ValueError, 'recovery drift'):
            self.tx.recover()
        self.assertEqual(file.read_bytes(), b'new hotfix')
        self.assertTrue((self.roots['divatech'] / 'new/path.html').exists())

    def test_interrupt_after_create_recovers_from_persisted_intent(self):
        operations = self.changes()
        real = self.tx.apply_one
        def interrupted(op, staged, journal):
            real(op, staged, journal)
            if op['path'] == 'new/path.html':
                raise KeyboardInterrupt('injected after atomic replacement')
        with patch.object(self.tx, 'apply_one', side_effect=interrupted):
            with self.assertRaises(KeyboardInterrupt):
                self.tx.apply(operations, self.staged)
        restarted = FileTransaction(self.roots, self.state, os.getuid(), os.getgid())
        restarted.recover()
        self.assert_restored()

    def test_recovery_cannot_be_redirected_to_another_root(self):
        self.tx.apply(self.changes(), self.staged)
        other = self.root / 'other'
        other.mkdir()
        redirected = FileTransaction(dict(self.roots, divatech=other), self.state, os.getuid(), os.getgid())
        with self.assertRaisesRegex(ValueError, 'target binding'):
            redirected.recover()
        self.assertEqual(list(other.iterdir()), [])

    def test_final_scan_and_source_integrity_before_writes(self):
        operations = self.changes()
        (self.staged / 'divatech/new/path.html').write_bytes(b'tampered')
        with self.assertRaisesRegex(ValueError, 'candidate drift'):
            self.tx.apply(operations, self.staged)
        self.assert_restored()
        self.assertFalse(self.tx.journal_file.exists())

    def test_noop_and_repeat_apply(self):
        entry = self.entry('primary', 'index.php', b'unchanged')
        result = self.tx.apply([{'root': 'primary', 'path': 'index.php', 'status': 'CONVERGED',
                                'before': entry, 'after': entry}], self.staged)
        self.assertEqual(result['status'], 'converged_unverified')
        self.assertEqual(result['operations'], [])
        with self.assertRaisesRegex(ValueError, 'prior attempt'):
            self.tx.apply([], self.staged)

    def test_path_and_symlink_boundaries(self):
        for root, path in [('primary', '../outside'), ('primary', 'bitrix/version.php'),
                           ('primary', 'local/.env'), ('primary', 'local/php_interface/include/tacticum_config.php'),
                           ('divatech', 'local/shared.php'), ('divatech', 'upload/image.png')]:
            with self.assertRaises(ValueError):
                self.tx.target({'root': root, 'path': path})
        (self.roots['divatech'] / 'escape').symlink_to(self.root)
        with self.assertRaisesRegex(ValueError, 'symlink'):
            self.tx.target({'root': 'divatech', 'path': 'escape/file.php'})

    def test_corrupted_backup_stops_before_any_recovery(self):
        self.tx.apply(self.changes(), self.staged)
        (self.state / 'before/0').write_bytes(b'corrupt')
        with self.assertRaisesRegex(ValueError, 'backup corrupted'):
            self.tx.recover()
        self.assertEqual((self.roots['primary'] / 'local/example.php').read_bytes(), b'new')

    def test_recovery_preserves_unrelated_new_files(self):
        self.tx.apply(self.changes(), self.staged)
        extra = self.roots['divatech'] / 'new/unrelated.txt'
        extra.write_bytes(b'keep')
        with self.assertRaises(OSError):
            self.tx.recover()
        self.assertEqual(extra.read_bytes(), b'keep')
        self.assertEqual((self.roots['primary'] / 'local/example.php').read_bytes(), b'old')


if __name__ == '__main__':
    unittest.main()
