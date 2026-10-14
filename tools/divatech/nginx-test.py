#!/usr/bin/env python3
"""Exercise real Nginx location precedence using synthetic, loopback-only fixtures."""
import json
from pathlib import Path
import subprocess
import tempfile
import time
import urllib.error
import urllib.request
import uuid

IMAGE = 'nginx:stable-alpine@sha256:0985e772fb9f729e6fa0980da05fca5d9c468e870eed43071545afa9d2e27d94'


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


def run():
    name = 'divatech-nginx-test-' + uuid.uuid4().hex[:10]
    with tempfile.TemporaryDirectory(prefix='divatech-nginx-') as directory:
        root = Path(directory)
        assets = root / 'site/local/templates/divatech/assets'
        assets.mkdir(parents=True)
        (assets / 'hero.jpg').write_bytes(b'synthetic image fixture')
        for filename in ['style.css', 'script.js']:
            (assets.parent / filename).write_text('/* synthetic fixture */')
        (root / 'boundary.conf').write_bytes(Path(__file__).with_name('nginx-site-boundary.conf').read_bytes())
        (root / 'nginx.conf').write_text('''events {} http {
 include /etc/nginx/mime.types;
 server { listen 8081; return 200 "synthetic backend"; }
 server {
  listen 8080; server_name divatech.space www.divatech.space;
  root /fixtures/site; set $proxyserver http://127.0.0.1:8081;
  proxy_set_header Host $host:443;
  include /fixtures/boundary.conf;
  # Match BitrixEnv errors.conf: a bare 404 re-enters the application.
  error_page 404 = @fallback;
  error_page 403 /403.html;
  location @fallback { proxy_pass $proxyserver; }
  location = /403.html { return 403; }
  location ~ \\.php$ { proxy_pass $proxyserver; }
  location ~ /$ { proxy_pass $proxyserver; }
  location / { proxy_pass $proxyserver; }
 }
}''')
        # Nginx workers need traversal of the synthetic tempfile directory.
        root.chmod(0o755)
        try:
            subprocess.run(['docker', 'run', '-d', '--name', name, '-p', '127.0.0.1::8080',
                            '--mount', f'type=bind,src={root},dst=/fixtures,readonly', IMAGE,
                            'nginx', '-c', '/fixtures/nginx.conf', '-g', 'daemon off;'], check=True, capture_output=True)
            subprocess.run(['docker', 'exec', name, 'nginx', '-c', '/fixtures/nginx.conf', '-t'], check=True, capture_output=True)
            port = subprocess.check_output(['docker', 'port', name, '8080/tcp'], text=True).strip().rsplit(':', 1)[1]
            opener = urllib.request.build_opener(NoRedirect())

            def request(path, host='divatech.space'):
                req = urllib.request.Request(f'http://127.0.0.1:{port}{path}', headers={'Host': host})
                try:
                    return opener.open(req, timeout=5)
                except urllib.error.HTTPError as error:
                    return error

            for attempt in range(20):
                try:
                    request('/').close()
                    break
                except (urllib.error.URLError, ConnectionError, TimeoutError):
                    # Docker may publish the port before Nginx accepts connections.
                    if attempt == 19:
                        raise
                    time.sleep(0.1)
            cases = [('/', 200), ('/local/templates/divatech/assets/hero.jpg', 200),
                     ('/local/templates/divatech/style.css', 200), ('/local/templates/divatech/script.js', 200),
                     ('/local/templates/divatech/assets/private.php', 403), ('/local/templates/divatech/header.php', 403),
                     ('/local/rest/divatech_lead.php', 200), ('/local/rest/tacticum_form.php', 403),
                     ('/local/api/cases.php', 403), ('/local/php_interface/include/tacticum_config.php', 403),
                     ('/upload/file.php', 403), ('/bitrix/admin/', 403), ('/.access.php', 403),
                     ('/bitrix/admin', 403), ('/bitrix/admin/index.php', 403), ('/local', 403),
                     ('/upload', 403), ('/local/templates/divatech/assets/missing.jpg', 403),
                     ('/page28604425.html?utm_source=test', 301), ('/index.php?x=1', 301)]
            for path, expected in cases:
                with request(path) as response:
                    assert response.status == expected, (path, response.status)
                    if expected == 301:
                        assert response.headers['Location'] == 'https://divatech.space/?' + path.split('?')[1]
            with request('/?x=2', 'www.divatech.space') as response:
                assert response.status == 301 and response.headers['Location'] == 'https://divatech.space/?x=2'
            print(json.dumps({'nginx_boundary_scenarios': len(cases) + 1, 'passed': True, 'image': IMAGE}))
        finally:
            subprocess.run(['docker', 'rm', '-f', name], capture_output=True)


if __name__ == '__main__':
    run()
