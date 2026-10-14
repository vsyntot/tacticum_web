#!/usr/bin/env python3
"""Build both sites twice from a Git commit; no core, secrets or deployment.

The synthetic platform identity tests packaging only. Output is a receipt, never
a production release. Temporary checkouts, node_modules and archives are removed.
"""
import argparse
import hashlib
import json
import os
from pathlib import Path, PurePosixPath
import subprocess
import tarfile
import tempfile


def run(args, cwd, env=None):
    return subprocess.check_output(args, cwd=cwd, env=env, stderr=subprocess.STDOUT)


def checkout(repo, commit, target):
    names = run(['git', 'ls-tree', '--name-only', '-z', commit], repo).decode().split('\0')
    # Platform/runtime are not inputs to application packaging. Never copy a
    # dirty worktree, local credentials, or ignored files into the build.
    names = [n for n in names if n and n not in ('bitrix', 'upload')]
    if any(n == '.env' or n.startswith('.env.') and n != '.env.example' for n in names):
        raise ValueError('tracked environment configuration in source commit')
    proc = subprocess.Popen(['git', 'archive', commit, '--', *names], cwd=repo, stdout=subprocess.PIPE)
    try:
        with tarfile.open(fileobj=proc.stdout, mode='r|') as archive:
            for member in archive:
                rel = PurePosixPath(member.name.rstrip('/'))
                if rel.is_absolute() or '..' in rel.parts or '\\' in member.name:
                    raise ValueError('unsafe checkout entry')
                file = target.joinpath(*rel.parts)
                if member.isdir():
                    file.mkdir(parents=True, exist_ok=True)
                elif member.isfile():
                    file.parent.mkdir(parents=True, exist_ok=True)
                    with archive.extractfile(member) as source, file.open('xb') as output:
                        import shutil
                        shutil.copyfileobj(source, output)
                    file.chmod(member.mode & 0o777)
                else:
                    raise ValueError('checkout must contain regular files and directories only')
        if proc.wait() != 0:
            raise ValueError('Git archive failed')
    finally:
        proc.stdout.close()
        if proc.poll() is None:
            proc.terminate()
            proc.wait()


def check(repo, revision='HEAD'):
    commit = run(['git', 'rev-parse', '--verify', revision + '^{commit}'], repo).decode().strip()
    platform = {'schemaVersion': 1, 'snapshotSha256': '1' * 64,
                'vendorManifestSha256': '2' * 64, 'schemaProfile': 'SYNTHETIC_PACKAGING_ONLY',
                'phpVersion': '8.5.11'}
    results = []
    with tempfile.TemporaryDirectory(prefix='multisite-clean-build-') as directory:
        temporary = Path(directory)
        for number, timezone in enumerate(['UTC', 'Pacific/Honolulu']):
            root = temporary / str(number)
            root.mkdir()
            checkout(repo, commit, root)
            env = dict(os.environ, TZ=timezone, SOURCE_DATE_EPOCH='0')
            run(['npm', 'ci', '--no-audit', '--no-fund'], root, env)
            run(['npm', 'run', 'css:build'], root, env)
            run(['node', 'tools/static-sitemap-generate.mjs'], root, env)
            lock = temporary / f'platform-{number}.json'
            lock.write_text(json.dumps(platform))
            manifest, artifact = temporary / f'manifest-{number}.json', temporary / f'artifact-{number}.tar.gz'
            run(['node', 'tools/multisite-cli.mjs', 'build', '--root', str(root),
                 '--platform', str(lock), '--manifest', str(manifest), '--output', str(artifact)], root, env)
            entries = json.loads(manifest.read_text())['entries']
            roots = {name: sum(e['root'] == name for e in entries) for name in ['primary', 'divatech']}
            if not all(roots.values()):
                raise ValueError('both sites must be packaged')
            # This also checks source files against every archive entry/digest.
            with tarfile.open(artifact) as archive:
                members = archive.getmembers()
                expected = {e['root'] + '/' + e['path']: e for e in entries}
                if len(members) != len(expected) or {m.name for m in members} != set(expected):
                    raise ValueError('archive member set differs from manifest')
                for member in members:
                    data = archive.extractfile(member).read()
                    if not member.isfile() or hashlib.sha256(data).hexdigest() != expected[member.name]['sha256']:
                        raise ValueError('archive content mismatch')
            results.append({'roots': roots, 'artifactSha256': hashlib.sha256(artifact.read_bytes()).hexdigest(),
                            'manifestSha256': hashlib.sha256(manifest.read_bytes()).hexdigest(),
                            'archiveBytes': artifact.stat().st_size})
        if results[0] != results[1]:
            raise ValueError('independent builds differ')
    return {'status': 'passed', 'sourceCommit': commit, 'builds': 2, **results[0],
            'platformEvidence': 'synthetic_packaging_only', 'productionMutationAllowed': False,
            'temporaryBuildsRemoved': True}


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--revision', default='HEAD')
    parser.add_argument('--output', type=Path)
    args = parser.parse_args()
    try:
        receipt = check(Path.cwd(), args.revision)
        raw = json.dumps(receipt, indent=2) + '\n'
        if args.output:
            with args.output.open('x') as file:
                file.write(raw)
        print(raw, end='')
    except subprocess.CalledProcessError as error:
        # Build logs are unprivileged; do not print inherited environment values.
        print('Clean build failed:', error.cmd[0], 'exit', error.returncode)
        print(error.output.decode(errors='replace')[-6000:])
        raise SystemExit(1)
