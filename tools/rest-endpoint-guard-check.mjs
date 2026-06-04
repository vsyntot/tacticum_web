#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const REST_DIR = path.join(ROOT, 'local/rest');
const IGNORED_FILES = new Set(['index.php', 'rest_helpers.php']);

const EXPECTED_ENDPOINTS = {
  'health_config.php': { method: 'GET', riskClass: 'CONFIG_HEALTH_GET' },
  'resolve_telegram_link.php': { method: 'POST', riskClass: 'PUBLIC_RESOLVER_POST' },
  'tacticum_chat.php': { method: 'POST', riskClass: 'PUBLIC_CHAT_POST' },
  'tacticum_form.php': { method: 'POST', riskClass: 'PUBLIC_LEAD_POST' },
  'tacticum_offer.php': { method: 'POST', riskClass: 'LEGACY_ALIAS_POST' },
  'tacticum_prefill.php': { method: 'POST', riskClass: 'SCOPED_PREFILL_POST' },
  'tacticum_sale.php': { method: 'POST', riskClass: 'LEGACY_ALIAS_POST' },
  'tacticum_sale_staff.php': { method: 'POST', riskClass: 'PUBLIC_STAFF_POST' },
};

function indexOfNeedle(source, needle) {
  return source.indexOf(needle);
}

function requireNeedle(source, file, needle, errors) {
  const index = indexOfNeedle(source, needle);
  if (index < 0) {
    errors.push(`${file}: missing ${needle}`);
  }
  return index;
}

function assertBefore(file, firstLabel, firstIndex, secondLabel, secondIndex, errors) {
  if (firstIndex < 0 || secondIndex < 0) return;
  if (firstIndex > secondIndex) {
    errors.push(`${file}: ${firstLabel} must run before ${secondLabel}`);
  }
}

function requireRiskClass(file, source, riskClass, errors) {
  const singleQuoteNeedle = `tacticum_rest_rate_limit_by_class('${riskClass}'`;
  const doubleQuoteNeedle = `tacticum_rest_rate_limit_by_class("${riskClass}"`;
  const index = source.indexOf(singleQuoteNeedle);
  const doubleQuoteIndex = source.indexOf(doubleQuoteNeedle);
  if (index < 0 && doubleQuoteIndex < 0) {
    errors.push(`${file}: must use endpoint risk class ${riskClass}`);
    return -1;
  }

  return index >= 0 ? index : doubleQuoteIndex;
}

function checkPostEndpoint(file, source, riskClass, errors) {
  const originIndex = requireNeedle(source, file, 'tacticum_rest_validate_origin(', errors);
  const rateLimitIndex = requireNeedle(source, file, 'tacticum_rest_rate_limit_by_class(', errors);
  const riskClassIndex = requireRiskClass(file, source, riskClass, errors);
  const methodIndex = requireNeedle(source, file, "tacticum_rest_require_method('POST')", errors);
  const jsonIndex = requireNeedle(source, file, 'tacticum_rest_read_json_body(', errors);
  const csrfIndex = requireNeedle(source, file, 'tacticum_rest_check_csrf(', errors);

  assertBefore(file, 'origin validation', originIndex, 'rate limit', rateLimitIndex, errors);
  assertBefore(file, 'origin validation', originIndex, `${riskClass} rate class`, riskClassIndex, errors);
  assertBefore(file, 'rate limit', rateLimitIndex, 'POST method check', methodIndex, errors);
  assertBefore(file, 'POST method check', methodIndex, 'JSON body parse', jsonIndex, errors);
  assertBefore(file, 'JSON body parse', jsonIndex, 'CSRF check', csrfIndex, errors);

  if (/file_get_contents\s*\(\s*['"]php:\/\/input['"]\s*\)/.test(source) && jsonIndex < 0) {
    errors.push(`${file}: must parse request body through tacticum_rest_read_json_body()`);
  }
}

function checkGetEndpoint(file, source, riskClass, errors) {
  const originIndex = requireNeedle(source, file, 'tacticum_rest_validate_origin(', errors);
  const rateLimitIndex = requireNeedle(source, file, 'tacticum_rest_rate_limit_by_class(', errors);
  const riskClassIndex = requireRiskClass(file, source, riskClass, errors);
  const methodIndex = source.indexOf("$_SERVER['REQUEST_METHOD']");

  if (methodIndex < 0) {
    errors.push(`${file}: missing GET method check`);
  }

  assertBefore(file, 'origin validation', originIndex, 'rate limit', rateLimitIndex, errors);
  assertBefore(file, 'origin validation', originIndex, `${riskClass} rate class`, riskClassIndex, errors);
  assertBefore(file, 'rate limit', rateLimitIndex, 'GET method check', methodIndex, errors);

  if (source.includes('tacticum_rest_read_json_body(') || source.includes('tacticum_rest_check_csrf(')) {
    errors.push(`${file}: GET endpoint must not parse JSON body or require CSRF`);
  }
}

function runSelfTest() {
  const validPost = [
    'tacticum_rest_validate_origin();',
    "tacticum_rest_rate_limit_by_class('PUBLIC_LEAD_POST', 'demo');",
    "tacticum_rest_require_method('POST');",
    '$data = tacticum_rest_read_json_body();',
    'tacticum_rest_check_csrf($data);'
  ].join('\n');
  const invalidPost = [
    '$data = tacticum_rest_read_json_body();',
    'tacticum_rest_check_csrf($data);',
    'tacticum_rest_validate_origin();',
    "tacticum_rest_rate_limit_by_class('PUBLIC_LEAD_POST', 'demo');",
    "tacticum_rest_require_method('POST');"
  ].join('\n');

  const validErrors = [];
  checkPostEndpoint('valid.php', validPost, 'PUBLIC_LEAD_POST', validErrors);
  if (validErrors.length > 0) {
    throw new Error(`Valid POST fixture failed:\n- ${validErrors.join('\n- ')}`);
  }

  const invalidErrors = [];
  checkPostEndpoint('invalid.php', invalidPost, 'PUBLIC_LEAD_POST', invalidErrors);
  if (!invalidErrors.some((error) => error.includes('must run before'))) {
    throw new Error('Invalid POST fixture did not fail on guard order.');
  }
}

function main() {
  if (process.argv.includes('--self-test')) {
    runSelfTest();
    console.log('REST endpoint guard self-test passed.');
    return;
  }

  const errors = [];
  const files = fs.readdirSync(REST_DIR)
    .filter((file) => file.endsWith('.php') && !IGNORED_FILES.has(file))
    .sort();

  for (const file of Object.keys(EXPECTED_ENDPOINTS)) {
    if (!files.includes(file)) {
      errors.push(`local/rest/${file}: expected REST endpoint is missing`);
    }
  }

  for (const file of files) {
    const relativeFile = `local/rest/${file}`;
    const source = fs.readFileSync(path.join(REST_DIR, file), 'utf8');
    const expected = EXPECTED_ENDPOINTS[file];

    if (!expected) {
      errors.push(`${relativeFile}: endpoint is not classified in tools/rest-endpoint-guard-check.mjs`);
      continue;
    }

    if (expected.method === 'POST') {
      checkPostEndpoint(relativeFile, source, expected.riskClass, errors);
    } else if (expected.method === 'GET') {
      checkGetEndpoint(relativeFile, source, expected.riskClass, errors);
    }
  }

  if (errors.length > 0) {
    console.error('REST endpoint guard check failed:');
    for (const error of errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  console.log(`REST endpoint guard check passed: ${files.length} endpoints scanned.`);
}

try {
  main();
} catch (error) {
  console.error(`REST endpoint guard check failed: ${error.message}`);
  process.exit(1);
}
