#!/usr/bin/env python3
import importlib.util
from pathlib import Path

spec = importlib.util.spec_from_file_location('rotation', Path(__file__).with_name('crypto-key-rotation.py'))
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)
old = b"<?php return ['crypto'=>['value'=>['crypto_key'=>'SYNTHETIC_TEST_ONLY_old']], 'other'=>'unchanged'];"
new = b'a' * 64
patched = module.patch_settings(old, new)
assert patched.replace(new, b'SYNTHETIC_TEST_ONLY_old') == old
for invalid in [b'<?php return [];', old + b"'crypto_key'=>'duplicate'", old.replace(b'SYNTHETIC_TEST_ONLY_old', b'')]:
    try:
        module.patch_settings(invalid, new)
    except ValueError:
        pass
    else:
        raise AssertionError('ambiguous/empty layout accepted')
try:
    module.patch_settings(old, b'invalid')
except ValueError:
    pass
else:
    raise AssertionError('invalid key format accepted')
print('Crypto replacement tests passed; no live configuration used.')
