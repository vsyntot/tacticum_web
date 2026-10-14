#!/usr/bin/env python3
"""Build a secret-free, hash-bound two-root candidate. Does not connect or deploy."""
import argparse
import hashlib
import io
import json
from pathlib import Path
import tarfile

ROOT = Path(__file__).resolve().parents[2]
SHARED = [
    'local/templates/divatech', 'local/components/tacticum/divatech.page',
    'local/lib/Tacticum/Divatech', 'local/lib/Tacticum/Rest/Response.php',
    'local/lib/Tacticum/Rest/Security.php', 'local/lib/Tacticum/Rest/RequestSite.php',
    'local/rest/rest_helpers.php', 'local/rest/divatech_lead.php',
    'local/rest/endpoint_policy.json', 'local/components/tacticum/component_cache_policy.json',
]
TOMBSTONE = 'local/divatech.space_70bce3996707255859d8f26899151c00283bbd9f.zip'
INFRA = {
    'nginx-http.conf': '/etc/nginx/bx/site_avaliable/bx_ext_divatech.space.conf',
    'nginx-https.conf': '/etc/nginx/bx/site_avaliable/bx_ext_ssl_divatech.space.conf',
    'apache-port.conf': '/etc/httpd/bx/conf/bx_apache_site_name_port.conf',
    'apache-site.conf': '/etc/httpd/bx/conf/bx_ext_divatech.space.conf',
    'tmpfiles.conf': '/etc/tmpfiles.d/divatech.space.conf',
    'domains.txt': '/home/bitrix/dehydrated/domains/divatech.space.txt',
    'domains-all.txt': '/home/bitrix/dehydrated/domains.txt',
}


def files_for(path):
    path = ROOT / path
    if path.is_file():
        return [path]
    if not path.is_dir():
        raise ValueError(f'Missing source: {path}')
    return sorted(p for p in path.rglob('*') if p.is_file() or p.is_symlink())


def build(output):
    output = output.resolve()
    if output.is_relative_to(ROOT) or output.exists():
        raise ValueError('Use a new private output directory outside the project.')
    output.mkdir(mode=0o700, parents=True)
    entries = []
    for source in SHARED:
        entries += [(p, 'shared/' + p.relative_to(ROOT).as_posix(), '/home/bitrix/www/' + p.relative_to(ROOT).as_posix()) for p in files_for(source)]
    for p in files_for('sites/divatech'):
        name = p.relative_to(ROOT / 'sites/divatech').as_posix()
        entries.append((p, 'site/' + name, '/home/bitrix/ext_www/divatech.space/' + name))
    for name in ['migrate.php', 'migration.php']:
        entries.append((ROOT / 'tools/divatech' / name, 'operator/' + name, None))
    for name, destination in INFRA.items():
        entries.append((ROOT / 'tools/divatech/infrastructure' / name, 'infrastructure/' + name, destination))
    entries.append((ROOT / 'tools/divatech/nginx-site-boundary.conf', 'infrastructure/nginx-site-boundary.conf', '/etc/nginx/bx/site_settings/divatech.space/20-site-boundary.conf'))
    entries.append((ROOT / 'tools/divatech/infrastructure/nginx-http-maintenance.conf', 'operator/nginx-http-maintenance.conf', None))
    manifest = {'version': 1, 'release_class': 'STATEFUL', 'files': [],
                'symlinks': {
                    **{'/home/bitrix/ext_www/divatech.space/' + name: '/home/bitrix/www/' + name for name in ['bitrix', 'local', 'upload']},
                    '/etc/nginx/bx/site_enabled/bx_ext_divatech.space.conf': '/etc/nginx/bx/site_avaliable/bx_ext_divatech.space.conf',
                    '/etc/nginx/bx/site_enabled/bx_ext_ssl_divatech.space.conf': '/etc/nginx/bx/site_avaliable/bx_ext_ssl_divatech.space.conf',
                },
                'tombstones': [{'path': '/home/bitrix/www/' + TOMBSTONE,
                                'expected_sha256': 'e5736a677fb855c9c9c3ab0060fa5ad58fcf3467520c0cbef577fc4e47a6de0d'}],
                'production_apply_ready': False,
                'pending': ['exact release plan approval', 'final locked production reconciliation', 'post-deploy verification']}
    archive = output / 'candidate.tar'
    with tarfile.open(archive, 'w', format=tarfile.PAX_FORMAT) as tar:
        for source, name, destination in sorted(entries, key=lambda item: item[1]):
            if source.is_symlink() or not source.is_file():
                raise ValueError(f'Only regular source files are allowed: {source}')
            relative = source.relative_to(ROOT)
            if any(ROOT.joinpath(*relative.parts[:i]).is_symlink() for i in range(1, len(relative.parts) + 1)):
                raise ValueError(f'Symlink ancestry is forbidden: {source}')
            if source.name.startswith('.env') or source.suffix.lower() not in {'.php', '.css', '.js', '.svg', '.jpg', '.png', '.ico', '.txt', '.xml', '.json', '.conf'} and source.name not in {'.htaccess', '.htsecure'}:
                raise ValueError(f'Unexpected source type: {source}')
            data = source.read_bytes()
            info = tarfile.TarInfo(name)
            info.size, info.mode, info.mtime = len(data), 0o644, 0
            tar.addfile(info, io.BytesIO(data))
            manifest['files'].append({'source': source.relative_to(ROOT).as_posix(), 'member': name,
                                      'destination': destination, 'size': len(data), 'mode': '0644',
                                      'owner': 'bitrix:bitrix' if destination and destination.startswith('/home/bitrix/') else 'root:root',
                                      'sha256': hashlib.sha256(data).hexdigest()})
    manifest['artifact_sha256'] = hashlib.sha256(archive.read_bytes()).hexdigest()
    raw = (json.dumps(manifest, ensure_ascii=False, sort_keys=True, indent=2) + '\n').encode()
    (output / 'manifest.json').write_bytes(raw)
    (output / 'manifest.sha256').write_text(hashlib.sha256(raw).hexdigest() + '\n')
    # Re-open every member and compare bytes, never execute package code during verification.
    with tarfile.open(archive) as tar:
        assert len(tar.getmembers()) == len(manifest['files'])
        for item in manifest['files']:
            assert hashlib.sha256(tar.extractfile(item['member']).read()).hexdigest() == item['sha256']
    print(json.dumps({'output': str(output), 'files': len(entries), 'artifact_sha256': manifest['artifact_sha256'], 'production_apply_ready': False}))


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', type=Path, required=True)
    build(parser.parse_args().output)
