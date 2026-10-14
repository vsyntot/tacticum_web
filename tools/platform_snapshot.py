#!/usr/bin/env python3
"""Provider-neutral snapshot preparation. No network, live import, DB or updater actions.

Imports always create a new isolated directory; an existing project is never overlaid.
Policy is a candidate until vendor/custom inventory and update window are reviewed.
"""
import argparse
import gzip
import hashlib
import io
import json
import os
from pathlib import Path, PurePosixPath
import re
import shutil
import stat
import tarfile
import tempfile
import unicodedata

POLICY = Path(__file__).with_name('platform-snapshot-policy.json')
MAX_FILES = 250_000
MAX_BYTES = 8 * 1024**3
MAX_FILE_BYTES = 256 * 1024**2


def require(condition, message):
    if not condition:
        raise ValueError(message)


def canonical(value):
    return json.dumps(value, sort_keys=True, separators=(',', ':'), ensure_ascii=False).encode()


def digest(data):
    return hashlib.sha256(data).hexdigest()


def file_digest(file):
    checksum = hashlib.sha256()
    with open(file, 'rb') as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b''):
            checksum.update(chunk)
    return checksum.hexdigest()


def rules():
    result = json.loads(POLICY.read_text())
    require(result['schemaVersion'] == 1, 'unsupported export policy')
    return result


def safe_name(name):
    p = PurePosixPath(name)
    require(name and name != '.' and not p.is_absolute() and '..' not in p.parts
            and str(p) == name and '\\' not in name and not any(ord(c) < 32 for c in name)
            and unicodedata.normalize('NFC', name) == name, 'unsafe snapshot path')
    return p


def excluded(name, policy):
    p = safe_name(name)
    return (p.parts[0] in policy['excludedDirectories'] or name in policy['excludedFiles']
            or any(part in {'.git', '.env', '.DS_Store', 'dbconn.php', 'license_key.php'}
                   or part.startswith(('.env.', '.settings')) for part in p.parts)
            or name.endswith(('.log', '.pem', '.key')))


def allowed(name, policy):
    p = safe_name(name)
    return not excluded(name, policy) and (name in policy['files'] or
        (len(p.parts) > 1 and p.parts[0] in policy['directories']))


def needs_case_sensitive(entries):
    folded = {}
    collision = False
    for e in entries:
        parts = PurePosixPath(e['path']).parts
        for length in range(1, len(parts)+1):
            prefix = '/'.join(parts[:length])
            key = prefix.casefold()
            collision |= folded.get(key, prefix) != prefix
            folded[key] = prefix
    return collision


def case_sensitive_filesystem(directory):
    probe = directory / '.CaseProbe'
    probe.write_bytes(b'')
    try: return not (directory / '.caseprobe').exists()
    finally: probe.unlink()


def inventory(root):
    root = Path(root)
    require(root.is_dir() and not root.is_symlink(), 'vendor root must be a real directory')
    policy = rules()
    entries = []

    def walk(relative=''):
        for file in sorted((root / relative).iterdir()):
            name = file.relative_to(root).as_posix()
            if excluded(name, policy):
                continue
            info = file.lstat()
            require(not stat.S_ISLNK(info.st_mode), 'vendor symlink requires explicit review')
            if stat.S_ISDIR(info.st_mode):
                require(PurePosixPath(name).parts[0] in policy['directories'], 'unknown vendor directory; review policy')
                walk(name)
            else:
                require(allowed(name, policy) and stat.S_ISREG(info.st_mode), 'unknown or special vendor file')
                require(not info.st_mode & 0o7000, 'special vendor file mode')
                require(info.st_size <= MAX_FILE_BYTES, 'vendor file exceeds size bound')
                entries.append({'path': name, 'size': info.st_size, 'mode': format(info.st_mode & 0o777, '04o'),
                                'sha256': file_digest(file)})
    walk()
    require(0 < len(entries) <= MAX_FILES and sum(e['size'] for e in entries) <= MAX_BYTES, 'snapshot bounds exceeded or empty')
    payload = {'schemaVersion': 1, 'policySha256': digest(canonical(policy)),
               'caseSensitiveRequired': needs_case_sensitive(entries), 'entries': sorted(entries, key=lambda e:e['path'])}
    return {**payload, 'manifestSha256': digest(canonical(payload))}


def validate_manifest(manifest):
    payload = {k:v for k,v in manifest.items() if k != 'manifestSha256'}
    require(manifest.get('schemaVersion') == 1 and digest(canonical(payload)) == manifest.get('manifestSha256'), 'invalid vendor manifest')
    policy = rules()
    require(manifest.get('policySha256') == digest(canonical(policy)), 'export policy mismatch')
    entries = manifest.get('entries')
    require(isinstance(entries, list) and 0 < len(entries) <= MAX_FILES, 'invalid inventory size')
    names, total, previous = set(), 0, ''
    for e in entries:
        name = e['path']
        require(allowed(name, policy) and name not in names, 'unsafe or duplicate inventory path')
        require(name > previous, 'inventory order is not canonical')
        names.add(name); previous = name
        require(re.fullmatch('[0-9a-f]{64}', e['sha256']) and re.fullmatch('0[0-7]{3}', e['mode']), 'invalid vendor metadata')
        require(type(e['size']) is int and 0 <= e['size'] <= MAX_FILE_BYTES, 'invalid file size')
        total += e['size']
    require(total <= MAX_BYTES, 'snapshot exceeds size bound')
    require(manifest.get('caseSensitiveRequired') == needs_case_sensitive(entries), 'filesystem requirements mismatch')
    require(all(not any(str(p) in names for p in PurePosixPath(name).parents if str(p) != '.') for name in names),
            'file/directory path collision')
    return entries


def export(root, destination, expected_manifest):
    root, destination = Path(root), Path(destination)
    require(not destination.exists() and not destination.is_symlink(), 'snapshot output must be new')
    require(not destination.resolve().is_relative_to(root.resolve()), 'output must be outside vendor root')
    before = inventory(root)
    require(before['manifestSha256'] == expected_manifest, 'vendor inventory changed since review')
    destination.mkdir(mode=0o700)
    try:
        archive = destination / 'vendor.tar.gz'
        with archive.open('xb') as stream:
            with gzip.GzipFile(filename='', fileobj=stream, mode='wb', mtime=0) as compressed:
                with tarfile.open(fileobj=compressed, mode='w', format=tarfile.PAX_FORMAT) as tar:
                    for e in before['entries']:
                        file = root / e['path']
                        require(not file.is_symlink(), 'vendor path changed during export')
                        content = file.read_bytes()
                        require(len(content) == e['size'] and digest(content) == e['sha256'], 'vendor file changed during export')
                        info = tarfile.TarInfo('bitrix/' + e['path'])
                        info.size, info.mode, info.mtime = len(content), int(e['mode'], 8), 0
                        tar.addfile(info, io.BytesIO(content))
        require(inventory(root) == before, 'vendor tree changed during export; retry after updater completes')
        (destination / 'vendor-manifest.json').write_bytes(canonical(before) + b'\n')
        receipt = {'schemaVersion':1, 'status':'prepared_not_published', 'snapshotSha256':file_digest(archive),
                   'vendorManifestSha256':before['manifestSha256'], 'policySha256':before['policySha256'],
                   'productionReady':False, 'databaseIncluded':False}
        (destination / 'receipt.json').write_bytes(canonical(receipt) + b'\n')
        return receipt
    except BaseException:
        shutil.rmtree(destination)
        raise


def verify_or_import(archive, manifest, expected_digest, destination=None):
    require(re.fullmatch('[a-f0-9]{64}', expected_digest or '') is not None, 'expected archive digest required')
    require(file_digest(archive) == expected_digest, 'archive digest mismatch')
    entries = validate_manifest(manifest)
    expected = {'bitrix/' + e['path']: e for e in entries}
    seen = set()
    destination = Path(destination) if destination is not None else None
    staging = None
    if destination:
        require(not destination.exists() and not destination.is_symlink(), 'import destination must be new; live overlay is forbidden')
        require(destination.parent.is_dir(), 'import parent must exist')
        staging = Path(tempfile.mkdtemp(prefix='.platform-import-', dir=destination.parent))
    try:
        if staging and manifest['caseSensitiveRequired']:
            require(case_sensitive_filesystem(staging), 'snapshot requires a case-sensitive filesystem; use Linux volume')
        with tarfile.open(archive, 'r:gz') as tar:
            for item in tar:
                require(item.name in expected and item.name not in seen, 'unexpected or duplicate archive member')
                e = expected[item.name]
                require(item.isfile() and not item.issparse() and item.size == e['size']
                        and item.mode == int(e['mode'], 8), 'archive type/metadata mismatch')
                data = tar.extractfile(item).read()
                require(digest(data) == e['sha256'], 'archive file digest mismatch')
                if staging:
                    file = staging / item.name
                    file.parent.mkdir(parents=True, exist_ok=True)
                    with file.open('xb') as stream: stream.write(data)
                    file.chmod(int(e['mode'], 8))
                seen.add(item.name)
        require(seen == set(expected), 'incomplete snapshot')
        if staging:
            require(inventory(staging / 'bitrix') == manifest, 'restored inventory differs')
            # mkdir is the exclusive claim; never replace even an empty existing directory.
            destination.mkdir(mode=0o700)
            try:
                os.rename(staging / 'bitrix', destination / 'bitrix')
            except BaseException:
                destination.rmdir()
                raise
        return {'status':'verified', 'entries':len(seen), 'isolatedImport':destination is not None}
    finally:
        if staging: shutil.rmtree(staging)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('command', choices=['inventory','export','verify','import'])
    for option in ['root','output','expected-manifest','manifest','archive','expected-sha256']:
        parser.add_argument('--' + option)
    args = parser.parse_args()
    if args.command == 'inventory':
        result = inventory(args.root)
        if args.output:
            with open(args.output, 'xb') as stream: stream.write(canonical(result) + b'\n')
            result = {'status':'inventoried', 'manifestSha256':result['manifestSha256'], 'entries':len(result['entries'])}
    elif args.command == 'export': result = export(args.root, args.output, args.expected_manifest)
    else:
        require(args.command != 'import' or args.output, 'isolated --output required')
        result = verify_or_import(args.archive, json.loads(Path(args.manifest).read_text()), args.expected_sha256,
                                  args.output if args.command == 'import' else None)
    print(json.dumps(result))


if __name__ == '__main__':
    try: main()
    except (ValueError, OSError, TypeError, KeyError, tarfile.TarError):
        # No file contents, passwords or untrusted tar headers in diagnostics.
        print(json.dumps({'status':'failed', 'reason':'snapshot_validation_failed'}))
        raise SystemExit(1)
