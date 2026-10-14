#!/usr/bin/env python3
"""Lint tracked application/tooling PHP and exercise both site contracts."""
from concurrent.futures import ThreadPoolExecutor
import subprocess


def lint(name):
    result = subprocess.run(['php', '-d', 'short_open_tag=1', '-l', name], capture_output=True, text=True)
    if result.returncode:
        raise RuntimeError(result.stdout + result.stderr)


if __name__ == '__main__':
    names = subprocess.check_output(['git', 'ls-files', '-z', '--', '*.php', ':!:bitrix/**']).decode().split('\0')
    names = [name for name in names if name]
    with ThreadPoolExecutor(max_workers=4) as pool:
        list(pool.map(lint, names))
    for args in [['tools/divatech/contracts-test.php'], ['tools/divatech/review-regression-test.php'],
                 ['tools/divatech/review-regression-test.php', '--s1']]:
        subprocess.run(['php', *args], check=True)
    print('PHP syntax passed:', len(names), 'application/tooling files; s1+s2 contracts passed.')
