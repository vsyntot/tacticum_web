#!/usr/bin/env python3
"""Prepare a private, exact crypto-key replacement. Never mutates live config.

Preparation is deliberately separate from a reviewed production apply operator.
Output contains hashes/counts only. Backups and candidates contain secrets and
must remain outside the webroot/Git with mode 0600.
"""
import argparse
import hashlib
import json
import os
from pathlib import Path
import re
import secrets
import stat
import subprocess

TABLES = ['b_main_mail_sender', 'b_socialservices_user', 'b_socialservices_zoom_meeting',
          'b_socialservices_zoom_meeting_recording', 'b_user_phone_auth', 'b_user_auth_code']
KEY = re.compile(rb'''(["']crypto_key["']\s*=>\s*)(["'])([^"'\\\r\n]*)\2''')


def digest(data):
    return hashlib.sha256(data).hexdigest()


def canonical(data):
    return json.dumps(data, sort_keys=True, separators=(',', ':')).encode()


def patch_settings(data, new_key):
    matches = list(KEY.finditer(data))
    if len(matches) != 1 or not re.fullmatch(rb'[a-f0-9]{64}', new_key):
        raise ValueError('unsupported_settings_layout')
    match = matches[0]
    if not match.group(3) or match.group(3) == new_key:
        raise ValueError('invalid_key_transition')
    return data[:match.start(3)] + new_key + data[match.end(3):]


def references(root):
    result = []
    for folder in [root / 'bitrix/modules', root / 'local']:
        for path in folder.rglob('*.php'):
            if path.is_symlink():
                raise ValueError('unexpected_source_symlink')
            data = path.read_bytes()
            if any(token in data.lower() for token in [b'crypto_key', b'cryptofield', b'secretfield']):
                result.append({'path': str(path.relative_to(root)), 'sha256': digest(data)})
    return sorted(result, key=lambda row: row['path'])


def counts():
    query = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (" + ','.join("'" + t + "'" for t in TABLES) + ');'
    command = ['mysql', '--batch', '--skip-column-names', 'tacticum_db', '-e']
    available = subprocess.check_output(command + [query], stderr=subprocess.DEVNULL, text=True).splitlines()
    selected = [table for table in TABLES if table in available]
    query = 'START TRANSACTION READ ONLY;' + ''.join('SELECT COUNT(*) FROM ' + table + ';' for table in selected) + 'COMMIT;'
    values = subprocess.check_output(command + [query], stderr=subprocess.DEVNULL, text=True).splitlines()
    result = dict.fromkeys(TABLES)
    result.update(zip(selected, map(int, values)))
    return result


def private_write(path, data):
    fd = os.open(path, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
    with os.fdopen(fd, 'wb') as out:
        out.write(data)
        out.flush()
        os.fsync(out.fileno())


def verify_candidate(root, old, new):
    ciphers = [p for p in (root / 'bitrix/modules/main/lib').rglob('*.php')
               if p.relative_to(root).as_posix().lower() == 'bitrix/modules/main/lib/security/cipher.php']
    if len(ciphers) != 1:
        raise ValueError('ambiguous_cipher')
    code = r'''namespace Bitrix\Main\Security;
class SecurityException extends \Exception {}
require $argv[3];
$a = require $argv[1]; $b = require $argv[2];
$old = $a['crypto']['value']['crypto_key']; $new = $b['crypto']['value']['crypto_key'];
unset($a['crypto']['value']['crypto_key'], $b['crypto']['value']['crypto_key']);
if ($a !== $b || $old === $new || strlen($new) !== 64) { exit(2); }
$cipher = new Cipher(); $data = 'SYNTHETIC_TEST_ONLY_rotation';
$encrypted = $cipher->encrypt($data, $new);
if ($cipher->decrypt($encrypted, $new) !== $data) { exit(3); }
$rejected = false;
try { $cipher->decrypt($encrypted, $old); } catch (SecurityException $e) { $rejected = true; }
if (!$rejected) { exit(4); }
echo "candidate_semantics_and_cipher_passed";
'''
    out = subprocess.run(['php', '-r', code, str(old), str(new), str(ciphers[0])],
                         stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    if out.returncode or out.stdout != b'candidate_semantics_and_cipher_passed':
        raise ValueError('candidate_validation_failed')


def prepare(root, directory):
    if root != Path('/home/bitrix/www') or root.resolve() != root:
        raise ValueError('unexpected_production_root')
    if directory.parent != Path('/root/tacticum-releases') or not directory.name.startswith('MSCI-SEC-'):
        raise ValueError('private_release_directory_required')
    if directory.parent.resolve() != directory.parent:
        raise ValueError('unsafe_release_parent')
    target = root / 'bitrix/.settings.php'
    if target.is_symlink() or (root / 'bitrix').is_symlink() or (root / 'bitrix/.settings_extra.php').exists():
        raise ValueError('unsupported_config_overlay')
    directory.mkdir(mode=0o700, exist_ok=False)
    # On failure keep this private directory for bounded diagnosis; never print content.
    before = target.read_bytes()
    metadata = target.stat()
    inventory = references(root)
    storage = counts()
    if any(value not in (None, 0) for value in storage.values()):
        raise ValueError('encrypted_storage_requires_migration')
    backup = directory / 'settings.before.php'
    candidate = directory / 'settings.candidate.php'
    private_write(backup, before)
    after = patch_settings(before, secrets.token_hex(32).encode())
    private_write(candidate, after)
    restore = directory / 'restore-rehearsal.php'
    private_write(restore, backup.read_bytes())
    os.chown(restore, metadata.st_uid, metadata.st_gid)
    restore.chmod(stat.S_IMODE(metadata.st_mode))
    restored = restore.stat()
    if restore.read_bytes() != before or (restored.st_uid, restored.st_gid, stat.S_IMODE(restored.st_mode)) != (metadata.st_uid, metadata.st_gid, stat.S_IMODE(metadata.st_mode)):
        raise ValueError('restore_rehearsal_failed')
    verify_candidate(root, backup, candidate)
    restore.unlink()
    if target.read_bytes() != before or references(root) != inventory or counts() != storage:
        raise ValueError('production_changed_during_preparation')
    plan = {'schema_version': 1, 'release_class': 'STATEFUL', 'status': 'prepared_not_applied',
            'target': str(target), 'before_sha256': digest(before), 'candidate_sha256': digest(after),
            'uid': metadata.st_uid, 'gid': metadata.st_gid, 'mode': format(stat.S_IMODE(metadata.st_mode), '04o'),
            'reference_files': inventory, 'table_row_counts': storage,
            'restore_rehearsal': 'passed', 'candidate_semantics_and_cipher': 'passed',
            'preparation_tool_sha256': digest(Path(__file__).read_bytes()),
            'application_mutation_performed': False,
            'required_before_apply': ['exact approval', 'shared locks', 'repeat config/code/data preflight',
                                      'post-deploy two-site smoke and monitoring', 'independent state baselines'],
            'impact': ['existing signatures/crypto cookies can expire', 'refresh/re-authentication may be required']}
    plan['plan_id'] = digest(canonical(plan))
    private_write(directory / 'plan.json', canonical(plan) + b'\n')
    return plan


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--release-dir', type=Path, required=True)
    args = parser.parse_args()
    try:
        result = prepare(Path('/home/bitrix/www'), args.release_dir)
        print(json.dumps(result))
    except (ValueError, OSError, subprocess.SubprocessError) as error:
        reason = str(error) if isinstance(error, ValueError) else 'preparation_failed'
        print(json.dumps({'status': 'failed', 'reason': reason, 'live_config_modified': False}))
        raise SystemExit(1)
