#!/usr/bin/env python3
"""Check indexed sources without ever printing credential values.

This is a bounded high-confidence guard, not a claim that history is secret-free.
Only the Git index is read, so ignored production/local configs are never scanned.
"""
import argparse
import hashlib
import json
import re
import subprocess
from pathlib import PurePosixPath


def forbidden(name):
    path = PurePosixPath(name)
    if path.name == '.env' or (path.name.startswith('.env.') and path.name != '.env.example'):
        return True
    if name in {'bitrix/license_key.php', 'bitrix/php_interface/dbconn.php',
                'local/php_interface/include/tacticum_config.php', '.settings.php', '.settings_extra.php'}:
        return True
    return name.startswith('bitrix/.settings') and name.endswith('.php')


PATTERNS = {
    'private_key': re.compile(rb'-----BEGIN (?:RSA |EC |OPENSSH |DSA |ENCRYPTED )?PRIVATE KEY-----'),
    'github_token': re.compile(rb'\b(?:gh[pousr]_[A-Za-z0-9]{36,}|github_pat_[A-Za-z0-9_]{60,})\b'),
    'aws_access_key': re.compile(rb'\b(?:AKIA|ASIA)[A-Z0-9]{16}\b'),
    'literal_crypto_key': re.compile(rb'''["']crypto_key["']\s*=>\s*["']([^"'\r\n]{16,})["']'''),
}


def findings(name, data):
    result = []
    if forbidden(name):
        result.append({'path': name, 'rule': 'environment_owned_path'})
    for rule, pattern in PATTERNS.items():
        for match in pattern.finditer(data):
            # Only explicit synthetic examples may carry a literal crypto fixture.
            if rule == 'literal_crypto_key' and match.group(1).startswith(b'SYNTHETIC_TEST_ONLY_'):
                continue
            result.append({'path': name, 'rule': rule, 'line': data[:match.start()].count(b'\n') + 1})
    return result


def scan(root):
    names = subprocess.check_output(['git', '-C', str(root), 'ls-files', '-z']).split(b'\0')
    results = []
    policy = subprocess.run(['git', '-C', str(root), 'show', ':tools/security-reviewed-fixtures.json'],
                            capture_output=True)
    exceptions = json.loads(policy.stdout).get('exceptions', []) if policy.returncode == 0 else []
    if not isinstance(exceptions, list) or any(not isinstance(e, dict)
        or not all(isinstance(e.get(key), str) for key in ['path', 'sha256', 'rule'])
        or not re.fullmatch('[a-f0-9]{64}', e['sha256']) or e['rule'] not in PATTERNS for e in exceptions):
        raise ValueError('invalid reviewed fixture policy')
    # A single batch process avoids tens of thousands of subprocesses.
    proc = subprocess.Popen(['git', '-C', str(root), 'cat-file', '--batch'],
                            stdin=subprocess.PIPE, stdout=subprocess.PIPE)
    try:
        for raw in names:
            if not raw:
                continue
            name = raw.decode('utf-8', errors='strict')
            if b'\n' in raw or b'\r' in raw:
                raise ValueError('unsupported indexed filename')
            proc.stdin.write(b':' + raw + b'\n')
            proc.stdin.flush()
            header = proc.stdout.readline().split()
            if len(header) != 3 or header[1] != b'blob':
                raise ValueError('indexed object must be a blob')
            data = proc.stdout.read(int(header[2]))
            if proc.stdout.read(1) != b'\n':
                raise ValueError('invalid git batch framing')
            found = findings(name, data)
            checksum = hashlib.sha256(data).hexdigest()
            results.extend(item for item in found if not any(
                e['path'] == name and e['sha256'] == checksum and e['rule'] == item['rule']
                and item['rule'] != 'environment_owned_path' for e in exceptions))
    finally:
        proc.stdin.close()
        proc.stdout.close()
        proc.wait()
    return results


def core_changes(root, base):
    records = diff_records(root, base, 'bitrix/')
    vendor_sync_allowed = approved_vendor_sync(root, base)
    blocked = []
    for status, name in records:
        if status != 'D' or not forbidden(name):
            if vendor_sync_allowed and not forbidden(name):
                continue
            blocked.append({'path': name, 'rule': 'unapproved_vendor_change'})
    return blocked


def diff_records(root, base, pathspec):
    raw = subprocess.check_output(['git', '-C', str(root), 'diff', '--no-renames',
        '--name-status', '-z', base, 'HEAD', '--', pathspec]).split(b'\0')
    return [(raw[index].decode(), raw[index + 1].decode())
            for index in range(0, len(raw) - 1, 2) if raw[index]]


def approved_vendor_sync(root, base):
    """Allow only an evidenced production vendor sync, never arbitrary core edits."""
    evidence_paths = [name for status, name in diff_records(root, base, 'docs/workflow/evidence/')
                      if status in {'A', 'M'} and name.startswith('docs/workflow/evidence/bitrix-production-sync-')
                      and name.endswith('.json')]
    if len(evidence_paths) != 1:
        return False
    try:
        evidence = json.loads(subprocess.check_output(
            ['git', '-C', str(root), 'show', f'HEAD:{evidence_paths[0]}']))
        scope = evidence['scope']
        candidate = evidence['candidate']
        sync = evidence['sync']
        approval = evidence['approval']
        if evidence.get('schema_version') != 1 or evidence.get('evidence_type') != 'bitrix_production_sync':
            return False
        if evidence.get('status') != 'verified' or evidence.get('production_mutation') is not False:
            return False
        if evidence.get('source') != 'production' or scope != {
            'root': 'bitrix',
            'excluded': ['bitrix/backup'],
            'server_newer_only': True,
            'local_backup_unchanged': True,
        }:
            return False
        if not re.fullmatch('[0-9a-f]{40}', candidate['git_tree_sha']):
            return False
        tree = subprocess.check_output(['git', '-C', str(root), 'rev-parse', 'HEAD:bitrix'], text=True).strip()
        if candidate['git_tree_sha'] != tree:
            return False
        for key in ['vendor_manifest_sha256', 'snapshot_sha256', 'policy_sha256']:
            if not re.fullmatch('[0-9a-f]{64}', candidate[key]):
                return False
        if type(candidate['entries']) is not int or candidate['entries'] <= 0:
            return False
        if candidate.get('production_ready') is not False or candidate.get('database_included') is not False:
            return False
        for key in ['archive_bytes', 'archive_entries', 'archive_files', 'merge_paths', 'transferred_files']:
            if type(sync[key]) is not int or sync[key] <= 0:
                return False
        if sync.get('remote_cleanup') != 'OK' or sync.get('local_cleanup') != 'OK':
            return False
        if not isinstance(approval.get('approved_by'), str) or not approval['approved_by']:
            return False
        if not isinstance(approval.get('source'), str) or not approval['source']:
            return False
        return True
    except (KeyError, TypeError, ValueError, json.JSONDecodeError, OSError, subprocess.SubprocessError):
        return False


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--root', default='.')
    parser.add_argument('--check-core-diff')
    args = parser.parse_args()
    try:
        found = core_changes(args.root, args.check_core_diff) if args.check_core_diff else scan(args.root)
        print(json.dumps({'status': 'failed' if found else 'passed', 'scope': 'git_diff' if args.check_core_diff else 'git_index',
                          'findings': found, 'history_checked': False}, ensure_ascii=True))
        raise SystemExit(bool(found))
    except (ValueError, UnicodeError, OSError, subprocess.SubprocessError):
        print(json.dumps({'status': 'failed', 'reason': 'cannot_verify_index'}))
        raise SystemExit(2)
