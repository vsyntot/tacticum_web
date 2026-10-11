#!/usr/bin/env python3
"""Generic bounded FILE_ONLY controller. Installed outside webroot.

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
    approval = json.loads((state / 'approval.json').read_text())
    plan = json.loads((state / 'plan.json').read_text())
    plan_id = approval.get('plan_id')
    artifact_expected = approval.get('artifact_sha256')
    prod_expected = approval.get('prod_manifest_sha256')
    candidate_expected = approval.get('candidate_manifest_sha256')
    base_expected = approval.get('base_manifest_sha256')
    changed_expected = set(approval.get('changed_paths', []))
    tombstones_expected = approval.get('tombstones', [])
    require(isinstance(plan_id, str) and len(plan_id) == 64, 'approval plan binding')
    require(isinstance(artifact_expected, str) and len(artifact_expected) == 64, 'approval artifact binding')
    require(isinstance(prod_expected, str) and len(prod_expected) == 64, 'approval prod binding')
    require(isinstance(candidate_expected, str) and len(candidate_expected) == 64, 'approval candidate binding')
    require(isinstance(base_expected, str) and len(base_expected) == 64, 'approval base binding')
    require(approval.get('release_class') == 'FILE_ONLY', 'release class')
    require(approval.get('production_mutation_allowed') is True, 'production approval')
    require(approval.get('approved_by') and approval.get('approval_source'), 'approval owner/source')
    require(approval.get('scope_version'), 'scope binding')
    plan_payload = {key: value for key, value in plan.items() if key != 'planId'}
    require(plan.get('planId') == plan_id and digest(canonical(plan_payload)) == plan_id, 'plan integrity')
    require(plan.get('productionMutationAllowed') is False, 'plan must be comparison-only')
    require(plan.get('baseManifestSha256') == base_expected, 'approval/base plan mismatch')
    require(plan.get('prodManifestSha256') == prod_expected, 'approval/prod plan mismatch')
    require(plan.get('candidateManifestSha256') == candidate_expected, 'approval/candidate plan mismatch')
    plan_changed = {item['path'] for item in plan.get('changes', []) if item.get('status') == 'LOCAL_CHANGE'}
    require(plan_changed == changed_expected, 'approval changed path set mismatch')
    prod = load_manifest(state / 'prod.json', prod_expected)
    candidate = load_manifest(state / 'candidate.json', candidate_expected)
    base = load_manifest(state / 'base.json', base_expected)
    require(base['scopeVersion'] == prod['scopeVersion'] == candidate['scopeVersion'] == approval['scope_version'], 'scope mismatch')
    require(base['treeKind'] == 'prod' and base['manifestSha256'] == plan.get('baseManifestSha256'), 'base plan binding')
    require(prod['treeKind'] == 'prod' and candidate['treeKind'] == 'candidate', 'manifest tree kind')
    require(all(change['status'] in {'LOCAL_CHANGE', 'CONVERGED'} for change in plan.get('changes', [])), 'unresolved production drift')
    from datetime import datetime, timezone
    expires_at = approval['expires_at'].replace('Z', '+00:00')
    require(datetime.now(timezone.utc) < datetime.fromisoformat(expires_at), 'approval expired')
    require(digest((state / 'artifact.tar.gz').read_bytes()) == artifact_expected, 'artifact integrity')
    old = {e['path']: e for e in prod['entries']}
    new = {e['path']: e for e in candidate['entries']}
    require(old.keys() == new.keys(), 'additions/deletions are outside this release')
    changed = {name for name in old if old[name] != new[name]}
    require(changed == changed_expected and changed, 'changed path set mismatch')
    require(prod['tombstones'] == candidate['tombstones'] == tombstones_expected, 'tombstone scope')
    # Shared lock plus earlier ADP controller lock: cover final scan through post-scan.
    with open('/run/lock/tacticum-production.lock', 'a') as lock, open('/run/lock/tacticum-adp-release.lock', 'a') as previous_lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        fcntl.flock(previous_lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        before = scan(root, tool_root)
        require(before['manifestSha256'] == prod_expected, 'production drift; approved plan stale')
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
            require(scan(root, tool_root)['manifestSha256'] == prod_expected, 'drift during preparation')
            report = {'plan_id': plan_id, 'backup_sha256': digest(backup.read_bytes()), 'restore_rehearsal': 'passed', 'disk_inodes': 'passed', 'lock': 'held', 'changed_paths': sorted(changed), 'tombstone': 'already_absent', 'applied': False}
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
