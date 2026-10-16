#!/usr/bin/env python3
import importlib.util
from pathlib import Path
import unittest

spec = importlib.util.spec_from_file_location('smoke', Path(__file__).with_name('multisite-public-smoke.py'))
smoke = importlib.util.module_from_spec(spec)
spec.loader.exec_module(smoke)


class ResponseTests(unittest.TestCase):
    def test_identity_and_php_errors(self):
        case = ('s1', '/', 200, 'Tacticum')
        self.assertTrue(smoke.evaluate(case, 200, 'Tacticum')['passed'])
        for status, body in [(200, 'DIVA Technologies'), (500, 'Tacticum'), (200, 'Tacticum Fatal error: broken')]:
            self.assertFalse(smoke.evaluate(case, status, body)['passed'])

    def test_secondary_forms_and_boundaries(self):
        self.assertTrue(smoke.evaluate(('s2', '/', 200, 'DIVA Technologies'), 200,
                                       'DIVA Technologies data-divatech-form')['passed'])
        self.assertFalse(smoke.evaluate(('s2', '/', 200, 'DIVA Technologies'), 200,
                                        'DIVA Technologies')['passed'])
        self.assertTrue(smoke.evaluate(('s2', '/bitrix/admin/', 301, None), 301, '')['passed'])
        self.assertFalse(smoke.evaluate(('s2', '/bitrix/admin/', 301, None), 200, 'login')['passed'])
        self.assertTrue(smoke.evaluate(('s2', '/missing', 404, None), 404, 'not found')['passed'])


if __name__ == '__main__':
    unittest.main()
