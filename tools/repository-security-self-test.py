#!/usr/bin/env python3
"""Synthetic secret/path guard and staged-content boundary regression checks."""
import importlib.util
import hashlib
import json
import subprocess
import tempfile
from pathlib import Path

spec = importlib.util.spec_from_file_location('guard', Path(__file__).with_name('repository-security-check.py'))
guard = importlib.util.module_from_spec(spec)
spec.loader.exec_module(guard)

for path in ['bitrix/.settings.php', 'bitrix/.settings_extra.php', '.env', 'sub/.env.prod',
             'bitrix/license_key.php', 'local/php_interface/include/tacticum_config.php']:
    assert guard.forbidden(path), path
for path in ['.env.example', 'local/php_interface/include/tacticum_config.example.php', 'sites/divatech/index.php']:
    assert not guard.forbidden(path), path
key = b'-----BEGIN ' + b'OPENSSH PRIVATE KEY-----'
assert guard.findings('example.txt', key)[0]['rule'] == 'private_key'
assert guard.findings('example.php', b"'crypto_key' => '" + b'x' * 40 + b"'")[0]['rule'] == 'literal_crypto_key'
assert not guard.findings('fixture.php', b"'crypto_key' => 'SYNTHETIC_TEST_ONLY_example'")

with tempfile.TemporaryDirectory(prefix='repository-security-') as temp:
    root = Path(temp)
    subprocess.run(['git', 'init', '-q', str(root)], check=True)
    file = root / 'public.txt'
    file.write_bytes(key)
    subprocess.run(['git', '-C', str(root), 'add', 'public.txt'], check=True)
    file.write_text('worktree hides the indexed secret')
    assert guard.scan(root)[0]['rule'] == 'private_key'
    subprocess.run(['git', '-C', str(root), 'add', 'public.txt'], check=True)
    (root / '.env').write_text('private untracked value')
    assert guard.scan(root) == []
    # A fixture exception cannot bypass forbidden configuration paths.
    config = root / 'bitrix/.settings.php'; config.parent.mkdir(); config.write_bytes(key)
    policy = root / 'tools/security-reviewed-fixtures.json'; policy.parent.mkdir()
    policy.write_text(json.dumps({'exceptions':[{'path':'bitrix/.settings.php',
        'sha256':hashlib.sha256(key).hexdigest(),'rule':'private_key'}]}))
    subprocess.run(['git','-C',str(root),'add','.'],check=True)
    assert any(f['rule']=='environment_owned_path' and f['path']=='bitrix/.settings.php' for f in guard.scan(root))
    # Test actual commit-to-commit core guard: only environment-owned deletion passes.
    subprocess.run(['git','-C',str(root),'config','user.name','Synthetic Fixture'],check=True)
    subprocess.run(['git','-C',str(root),'config','user.email','fixture@example.invalid'],check=True)
    (root/'bitrix/vendor.php').write_text('vendor before')
    subprocess.run(['git','-C',str(root),'add','.'],check=True)
    subprocess.run(['git','-C',str(root),'commit','-qm','fixture baseline'],check=True)
    base=subprocess.check_output(['git','-C',str(root),'rev-parse','HEAD'],text=True).strip()
    config.unlink()
    subprocess.run(['git','-C',str(root),'add','-u'],check=True)
    subprocess.run(['git','-C',str(root),'commit','-qm','remove environment config'],check=True)
    assert guard.core_changes(root,base)==[]
    (root/'bitrix/vendor.php').write_text('reviewed vendor change')
    subprocess.run(['git','-C',str(root),'add','-u'],check=True)
    subprocess.run(['git','-C',str(root),'commit','-qm','vendor edit'],check=True)
    tree = subprocess.check_output(['git','-C',str(root),'rev-parse','HEAD:bitrix'],text=True).strip()
    evidence = root/'docs/workflow/evidence/bitrix-production-sync-fixture.json'
    evidence.parent.mkdir(parents=True, exist_ok=True)
    evidence.write_text(json.dumps({
        'schema_version': 1,
        'evidence_type': 'bitrix_production_sync',
        'status': 'verified',
        'production_mutation': False,
        'source': 'production',
        'scope': {'root': 'bitrix', 'excluded': ['bitrix/backup'],
                  'server_newer_only': True, 'local_backup_unchanged': True},
        'candidate': {'git_tree_sha': tree, 'vendor_manifest_sha256': 'a'*64,
                      'snapshot_sha256': 'b'*64, 'policy_sha256': 'c'*64,
                      'entries': 1, 'production_ready': False, 'database_included': False},
        'sync': {'archive_bytes': 1, 'archive_entries': 1, 'archive_files': 1,
                 'merge_paths': 1, 'transferred_files': 1,
                 'remote_cleanup': 'OK', 'local_cleanup': 'OK'},
        'approval': {'approved_by': 'fixture-owner', 'source': 'fixture approval'},
    }))
    subprocess.run(['git','-C',str(root),'add','docs/workflow/evidence/bitrix-production-sync-fixture.json'],check=True)
    subprocess.run(['git','-C',str(root),'commit','--amend','--no-edit','-q'],check=True)
    assert guard.core_changes(root,base)==[]
    base = subprocess.check_output(['git','-C',str(root),'rev-parse','HEAD'],text=True).strip()
    (root/'bitrix/vendor.php').write_text('unreviewed vendor change')
    subprocess.run(['git','-C',str(root),'add','-u'],check=True)
    subprocess.run(['git','-C',str(root),'commit','-qm','vendor edit without evidence'],check=True)
    assert guard.core_changes(root,base)==[{'path':'bitrix/vendor.php','rule':'unapproved_vendor_change'}]
print('Repository security self-test passed: forbidden paths, redacted detection, index boundary.')
