#!/usr/bin/env python3
import copy
import io
import json
import os
from pathlib import Path
import tarfile
import tempfile
import unittest
from unittest.mock import patch
from platform_snapshot import inventory, export, verify_or_import, validate_manifest, digest, canonical, file_digest


class SnapshotTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix='platform-snapshot-test-')
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        self.vendor = self.root / 'vendor'
        (self.vendor / 'modules/main').mkdir(parents=True)
        (self.vendor / 'modules/main/a.php').write_text('<?php /* synthetic vendor */')
        for p in ['backup/private.zip', 'cache/session.txt', '.settings.php', 'license_key.php', 'php_interface/dbconn.php']:
            file = self.vendor / p; file.parent.mkdir(parents=True, exist_ok=True); file.write_text('SYNTHETIC_TEST_ONLY')

    def package(self, name='snapshot'):
        manifest = inventory(self.vendor)
        out = self.root / name
        receipt = export(self.vendor, out, manifest['manifestSha256'])
        return out, receipt, manifest

    def test_reproducibility_and_isolated_import(self):
        out, receipt, manifest = self.package()
        os.utime(self.vendor / 'modules/main/a.php', (1,1))
        second, receipt2, _ = self.package('second')
        self.assertEqual(receipt, receipt2)
        self.assertEqual((out/'vendor.tar.gz').read_bytes(), (second/'vendor.tar.gz').read_bytes())
        dest = self.root / 'imported'
        verify_or_import(out/'vendor.tar.gz', manifest, receipt['snapshotSha256'], dest)
        self.assertEqual(inventory(dest/'bitrix'), manifest)
        self.assertEqual(len(manifest['entries']),1)
        with self.assertRaises(ValueError):
            verify_or_import(out/'vendor.tar.gz', manifest, receipt['snapshotSha256'], dest)
        # No mtime merge: a new snapshot drops removed upstream paths entirely.
        (self.vendor/'modules/main/a.php').unlink()
        (self.vendor/'modules/main/b.php').write_text('new upstream')
        out2, receipt2, manifest2 = self.package('third')
        verify_or_import(out2/'vendor.tar.gz', manifest2, receipt2['snapshotSha256'], self.root/'import2')
        self.assertFalse((self.root/'import2/bitrix/modules/main/a.php').exists())
        self.assertTrue((dest/'bitrix/modules/main/a.php').exists(), 'old snapshot lost')

    def test_reviewed_inventory_and_cleanup(self):
        before = inventory(self.vendor)
        (self.vendor/'modules/main/a.php').write_text('drift')
        with self.assertRaises(ValueError): export(self.vendor, self.root/'bad', before['manifestSha256'])
        current = inventory(self.vendor)
        with patch('platform_snapshot.inventory', side_effect=[current,before]):
            with self.assertRaises(ValueError): export(self.vendor, self.root/'unstable', current['manifestSha256'])
        self.assertFalse((self.root/'unstable').exists())

    def test_source_boundaries(self):
        (self.vendor/'modules/main/link').symlink_to('/tmp')
        with self.assertRaises(ValueError): inventory(self.vendor)
        (self.vendor/'modules/main/link').unlink()
        (self.vendor/'unreviewed.php').write_text('unknown')
        with self.assertRaises(ValueError): inventory(self.vendor)

    def test_case_collision_on_any_host_filesystem(self):
        manifest = inventory(self.vendor)
        manifest['entries'].insert(0, {**manifest['entries'][0], 'path':'modules/Main/b.php'})
        manifest['caseSensitiveRequired'] = True
        payload = {k:v for k,v in manifest.items() if k != 'manifestSha256'}
        manifest['manifestSha256'] = digest(canonical(payload))
        validate_manifest(manifest)
        archive = self.root/'case.tar.gz'
        with tarfile.open(archive,'w:gz') as tar:
            for e in manifest['entries']:
                info = tarfile.TarInfo('bitrix/'+e['path']); info.size=e['size']; info.mode=int(e['mode'],8)
                tar.addfile(info,io.BytesIO((self.vendor/'modules/main/a.php').read_bytes()))
        verify_or_import(archive,manifest,file_digest(archive))
        with patch('platform_snapshot.case_sensitive_filesystem',return_value=False):
            with self.assertRaisesRegex(ValueError,'case-sensitive'):
                verify_or_import(archive,manifest,file_digest(archive),self.root/'case-output')
        self.assertFalse((self.root/'case-output').exists())

    def test_tampered_archive_and_manifest(self):
        out, receipt, manifest = self.package()
        with self.assertRaises(ValueError): verify_or_import(out/'vendor.tar.gz',manifest,'0'*64)
        bad = copy.deepcopy(manifest); bad['entries'][0]['path']='../escape'
        payload = {k:v for k,v in bad.items() if k != 'manifestSha256'}
        bad['manifestSha256'] = digest(canonical(payload))
        with self.assertRaises(ValueError): verify_or_import(out/'vendor.tar.gz',bad,receipt['snapshotSha256'])
        for variant in ['symlink','duplicate','missing','wrong-content','traversal']:
            archive = self.root / (variant+'.tar.gz')
            with tarfile.open(archive,'w:gz') as tar:
                if variant == 'missing': pass
                else:
                    e = manifest['entries'][0]
                    info = tarfile.TarInfo('../escape' if variant == 'traversal' else 'bitrix/'+e['path'])
                    data = (self.vendor/e['path']).read_bytes()
                    info.mode=int(e['mode'],8); info.size=len(data)
                    if variant == 'symlink': info.type=tarfile.SYMTYPE; info.linkname='/tmp'; info.size=0
                    if variant == 'wrong-content': data=b'x'*len(data)
                    tar.addfile(info, io.BytesIO(data))
                    if variant == 'duplicate': tar.addfile(info, io.BytesIO(data))
            with self.assertRaises(ValueError):
                verify_or_import(archive,manifest,file_digest(archive),self.root/'unsafe')
            self.assertFalse((self.root/'unsafe').exists())
            self.assertFalse(list(self.root.glob('.platform-import-*')))


if __name__ == '__main__': unittest.main()
