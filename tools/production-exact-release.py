#!/usr/bin/env python3
"""Bounded controller for approved plan 637e64de. Installed outside webroot.

No candidate code is executed. Only existing regular files may be replaced.
The forced-command SSH entry point supplies fixed state/root paths and mode.
"""
import argparse
import fcntl
import hashlib
import json
import os
from pathlib import Path, PurePosixPath
import shutil
import stat
import subprocess
import tarfile
import tempfile

PLAN = '637e64de088c5ac1519b7539923bacba8cf35f7c7e6f3eaebfbe25fc58818f70'
ARTIFACT = '71b2ab4fd4fe5d411dd063a178996eb38301b689359c3aae095fa9cac148f33d'
PROD = '736095634beb3738dc70f595745620481dc26969ffc36863a64b30c399f05063'
CANDIDATE = '1c0a7b32969e744cffcf0f663e6fa567deba34088edbda1539b88c3807ee4d33'
CHANGED = {
    'local/rest/endpoint_policy.json', 'local/rest/tacticum_offer.php',
    'local/rest/tacticum_sale.php', 'local/templates/tacticum/tailwind.generated.css',
}
CACHE_PATHS = [
    'bitrix/managed_cache', 'bitrix/cache/tacticum',
    'bitrix/cache/s1/bitrix/menu', 'bitrix/cache/s1/bitrix/news.list',
    'bitrix/cache/s1/bitrix/news.detail', 'bitrix/html_pages',
    'bitrix/cache/css/s1/tacticum', 'bitrix/cache/js/s1/tacticum',
]


def require(condition, message):
    if not condition:
        raise RuntimeError(message)


def digest(data):
    return hashlib.sha256(data).hexdigest()


def canonical(data):
    return json.dumps(data, sort_keys=True, separators=(',', ':'), ensure_ascii=False).encode()


def load_manifest(path, expected):
    data = json.loads(path.read_text())
    payload = {k: v for k, v in data.items() if k != 'manifestSha256'}
    require(digest(canonical(payload)) == data['manifestSha256'] == expected, 'manifest integrity')
    return data


def safe_file(root, name):
    rel = PurePosixPath(name)
    require(not rel.is_absolute() and '..' not in rel.parts and str(rel) == name, 'unsafe path')
    current = root
    for part in rel.parts:
        current = current / part
        require(not current.is_symlink(), 'symlink path')
    require(current.is_file(), 'missing regular file: ' + name)
    return current


def verify_files(root, manifest):
    for entry in manifest['entries']:
        require(entry['type'] == 'file', 'this controller accepts regular files only')
        file = safe_file(root, entry['path'])
        st = file.stat()
        require(digest(file.read_bytes()) == entry['sha256'], 'content mismatch: ' + entry['path'])
        require(st.st_size == entry['size'] and format(stat.S_IMODE(st.st_mode), '04o') == entry['mode'], 'metadata mismatch')
    for name in manifest['tombstones']:
        require(not (root / name).exists() and not (root / name).is_symlink(), 'tombstone reappeared; plan stale')


def clear_cache(root):
    for relative in CACHE_PATHS:
        directory = root / relative
        if not directory.is_dir() or directory.is_symlink():
            continue
        for child in directory.iterdir():
            if child.is_dir() and not child.is_symlink():
                shutil.rmtree(child)
            else:
                child.unlink()


def extract_candidate(archive, destination, manifest):
    expected = {e['path']: e for e in manifest['entries']}
    seen = set()
    with tarfile.open(archive) as tar:
        for member in tar:
            require(member.isfile(), 'archive special member')
            if member.name not in expected:
                # macOS AppleDouble metadata in the approved archive is never deployed.
                require(PurePosixPath(member.name).name.startswith('._'), 'unlisted archive member')
                continue
            require(member.name not in seen, 'duplicate archive member')
            seen.add(member.name)
            require(member.size == expected[member.name]['size'], 'archive size mismatch')
            target = destination / member.name
            target.parent.mkdir(parents=True, exist_ok=True)
            with tar.extractfile(member) as source, target.open('xb') as output:
                shutil.copyfileobj(source, output)
            target.chmod(int(expected[member.name]['mode'], 8))
    require(seen == set(expected), 'archive manifest set mismatch')
    verify_files(destination, manifest)


def scan(root, tool_root):
    script = "import {buildCanonicalManifest,loadDeployScope} from './production-deploy-contract.mjs';const s=await loadDeployScope('deploy-scope.json');console.log(JSON.stringify(await buildCanonicalManifest(process.argv[1],s,{treeKind:'prod'})));"
    return json.loads(subprocess.check_output(['node', '--input-type=module', '-e', script, str(root)], cwd=tool_root))


def run(root, state, tool_root, apply):
    require(root.is_dir() and root.resolve() == root, 'canonical root required')
    require(state.is_dir() and state.resolve() == state, 'canonical state required')
    require(not (state / 'applied.json').exists(), 'release already applied')
    prod = load_manifest(state / 'prod.json', PROD)
    candidate = load_manifest(state / 'candidate.json', CANDIDATE)
    approval = json.loads((state / 'approval.json').read_text())
    require(approval['plan_id'] == PLAN and approval['artifact_sha256'] == ARTIFACT, 'approval binding')
    require(approval['prod_manifest_sha256'] == PROD and approval['candidate_manifest_sha256'] == CANDIDATE, 'approval inputs')
    from datetime import datetime, timezone
    expires_at = approval['expires_at'].replace('Z', '+00:00')
    require(datetime.now(timezone.utc) < datetime.fromisoformat(expires_at), 'approval expired')
    require(digest((state / 'artifact.tar.gz').read_bytes()) == ARTIFACT, 'artifact integrity')
    old = {e['path']: e for e in prod['entries']}
    new = {e['path']: e for e in candidate['entries']}
    require(old.keys() == new.keys(), 'additions/deletions are outside this release')
    changed = {name for name in old if old[name] != new[name]}
    require(changed == CHANGED, 'changed path set mismatch')
    require(prod['tombstones'] == candidate['tombstones'] == ['sitemap-files.xml'], 'tombstone scope')
    # Shared lock plus earlier ADP controller lock: cover final scan through post-scan.
    with open('/run/lock/tacticum-production.lock', 'a') as lock, open('/run/lock/tacticum-adp-release.lock', 'a') as previous_lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        fcntl.flock(previous_lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        before = scan(root, tool_root)
        require(before['manifestSha256'] == PROD, 'production drift; approved plan stale')
        space = os.statvfs(state)
        require(space.f_bavail * space.f_frsize > 500_000_000 and space.f_favail > 5000, 'disk/inodes')
        with tempfile.TemporaryDirectory(prefix='prepare-', dir=state) as temporary:
            temp = Path(temporary)
            staged = temp / 'candidate'
            staged.mkdir()
            extract_candidate(state / 'artifact.tar.gz', staged, candidate)
            for name in changed:
                if name.endswith('.php'):
                    subprocess.run(['php', '-l', str(staged / name)], check=True, stdout=subprocess.DEVNULL)
            backup = state / 'backup.tar.gz'
            require(not backup.exists(), 'backup already exists; inspect before retry')
            metadata = {}
            with tarfile.open(backup, 'w:gz') as tar:
                for name in sorted(old):
                    file = safe_file(root, name)
                    st = file.stat()
                    metadata[name] = [st.st_uid, st.st_gid, stat.S_IMODE(st.st_mode)]
                    tar.add(file, arcname=name, recursive=False)
            backup.chmod(0o600)
            rehearsal = temp / 'restore'
            rehearsal.mkdir()
            with tarfile.open(backup) as tar:
                for member in tar:
                    require(member.name in old and member.isfile(), 'unsafe backup entry')
                    target = rehearsal / member.name
                    target.parent.mkdir(parents=True, exist_ok=True)
                    with tar.extractfile(member) as src, target.open('xb') as dst:
                        shutil.copyfileobj(src, dst)
                    os.chown(target, member.uid, member.gid)
                    target.chmod(member.mode)
                    os.utime(target, (member.mtime, member.mtime))
            verify_files(rehearsal, prod)
            for name, expected in metadata.items():
                st = (rehearsal / name).stat()
                require([st.st_uid, st.st_gid, stat.S_IMODE(st.st_mode)] == expected, 'restore ownership mismatch')
            (state / 'metadata.json').write_bytes(canonical(metadata))
            paths = state / 'changed-files.txt'
            paths.write_text('\n'.join(sorted(changed)) + '\n')
            # Fixed server-owned ownership; never inherit runner identities.
            import pwd, grp
            uid, gid = pwd.getpwnam('bitrix').pw_uid, grp.getgrnam('bitrix').gr_gid
            require(all(metadata[n][:2] == [uid, gid] for n in changed), 'unexpected server ownership')
            scope = json.loads((tool_root / 'deploy-scope.json').read_text())
            options = scope['rsyncOptions']
            require(all(x in options for x in ['--checksum', '--delay-updates', '--delete-delay', '--no-owner', '--no-group']), 'rsync contract')
            command = ['rsync', *options, '--chown=bitrix:bitrix', '--itemize-changes', '--out-format=%i %n', '--files-from=' + str(paths), str(staged) + '/', str(root) + '/']
            dry = subprocess.check_output([*command[:1], '--dry-run', *command[1:]], text=True)
            (state / 'dry-run.txt').write_text(dry)
            print(dry, end='', flush=True)
            require(scan(root, tool_root)['manifestSha256'] == PROD, 'drift during preparation')
            report = {'plan_id': PLAN, 'backup_sha256': digest(backup.read_bytes()), 'restore_rehearsal': 'passed', 'disk_inodes': 'passed', 'lock': 'held', 'changed_paths': sorted(changed), 'tombstone': 'already_absent', 'applied': False}
            (state / 'prepared.json').write_bytes(canonical(report))
            if apply:
                subprocess.run(command, check=True)
                clear_cache(root)
                after = scan(root, tool_root)
                require(after['entries'] == candidate['entries'], 'post-apply manifest mismatch')
                for name in changed:
                    st = (root / name).stat()
                    require([st.st_uid, st.st_gid, stat.S_IMODE(st.st_mode)] == metadata[name], 'post-apply metadata mismatch')
                (state / 'post-manifest.json').write_bytes(canonical(after))
                report['applied'] = True
                report['post_manifest_sha256'] = after['manifestSha256']
                report['cache_clear'] = 'passed'
                (state / 'applied.json').write_bytes(canonical(report))
            print(json.dumps(report), flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--root', type=Path, required=True)
    parser.add_argument('--state', type=Path, required=True)
    parser.add_argument('--tool-root', type=Path, required=True)
    parser.add_argument('--apply', action='store_true')
    args = parser.parse_args()
    run(args.root, args.state, args.tool_root, args.apply)
