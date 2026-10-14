#!/usr/bin/env python3
"""Read-only HTTPS smoke/monitoring of both sites. Never submits forms.

Uses system curl certificate trust, does not follow redirects, does not record
response bodies, cookies or credentials. This does not prove admin login.
"""
import argparse
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime, timezone
import json
from pathlib import Path
import subprocess
import time

CASES = [
    ('s1', '/', 200, 'Tacticum'), ('s1', '/adp/', 200, 'Tacticum'),
    ('s1', '/contacts/', 200, 'Tacticum'), ('s1', '/price/', 200, 'Tacticum'),
    ('s1', '/calculator/', 200, 'Tacticum'), ('s1', '/offer/', 200, 'Tacticum'),
    ('s1', '/robots.txt', 200, 'sitemap'), ('s1', '/sitemap.xml', 200, '<?xml'),
    ('s2', '/', 200, 'DIVA Technologies'), ('s2', '/robots.txt', 200, 'sitemap'),
    ('s2', '/sitemap.xml', 200, '<?xml'), ('s2', '/bitrix/admin/', 403, None),
    ('s2', '/local/.env', 403, None), ('s2', '/release-smoke-missing-page/', 404, None),
]
HOSTS = {'s1': 'https://tacticum.ru', 's2': 'https://divatech.space'}


def evaluate(case, status, body):
    site, path, expected, marker = case
    failures = []
    if status != expected:
        failures.append('unexpected_status')
    if marker and marker.lower() not in body.lower():
        failures.append('missing_identity')
    if any(token in body for token in ['Fatal error:', 'Parse error:']):
        failures.append('php_error')
    if site == 's2' and path == '/' and 'data-divatech-form' in body:
        failures.append('forms_enabled')
    return {'site': site, 'path': path, 'status': status, 'passed': not failures, 'failures': failures}


def probe(case):
    site, path, *_ = case
    try:
        result = subprocess.run(['curl', '--silent', '--show-error', '--max-time', '20',
                                 '--proto', '=https', '--write-out', '\n%{http_code}',
                                 HOSTS[site] + path], capture_output=True, timeout=25)
    except subprocess.TimeoutExpired:
        return {'site': site, 'path': path, 'passed': False, 'failures': ['transport_timeout']}
    if result.returncode:
        return {'site': site, 'path': path, 'passed': False, 'failures': ['transport_error'],
                'transportCode': result.returncode}
    body, status = result.stdout.decode(errors='replace').rsplit('\n', 1)
    return evaluate(case, int(status), body)


def monitor(duration, interval):
    if not 0 <= duration <= 3600 or not 1 <= interval <= 60:
        raise ValueError('duration must be 0..3600 and interval 1..60 seconds')
    started = time.monotonic()
    report = {'startedAt': datetime.now(timezone.utc).isoformat(), 'samples': [],
              'adminLoginVerified': False, 'mutations': False}
    while True:
        with ThreadPoolExecutor(max_workers=6) as pool:
            checks = list(pool.map(probe, CASES))
        elapsed = time.monotonic() - started
        report['samples'].append({'elapsedSeconds': round(elapsed, 2), 'checks': checks})
        report['elapsedSeconds'] = round(elapsed, 2)
        report['passed'] = all(c['passed'] for sample in report['samples'] for c in sample['checks'])
        if not report['passed'] or elapsed >= duration:
            return report
        time.sleep(min(interval, duration - elapsed))


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--duration', type=int, default=0)
    parser.add_argument('--interval', type=int, default=45)
    parser.add_argument('--output', type=Path)
    args = parser.parse_args()
    report = monitor(args.duration, args.interval)
    if args.output:
        with args.output.open('x') as file:
            json.dump(report, file, indent=2)
    print(json.dumps(report))
    raise SystemExit(0 if report['passed'] else 1)
