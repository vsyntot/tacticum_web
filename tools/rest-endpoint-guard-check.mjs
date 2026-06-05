#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const REST_DIR = path.join(ROOT, 'local/rest');
const POLICY_FILE = path.join(REST_DIR, 'endpoint_policy.json');
const RATE_LIMITER_FILE = path.join(ROOT, 'local/lib/Tacticum/Rest/RateLimiter.php');
const IGNORED_FILES = new Set(['index.php', 'rest_helpers.php']);

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

function loadEndpointPolicy(errors) {
  if (!fs.existsSync(POLICY_FILE)) {
    errors.push('local/rest/endpoint_policy.json is missing');
    return { riskClasses: {}, endpoints: {} };
  }

  let policy;
  try {
    policy = JSON.parse(fs.readFileSync(POLICY_FILE, 'utf8'));
  } catch (error) {
    errors.push(`local/rest/endpoint_policy.json is invalid JSON: ${error.message}`);
    return { riskClasses: {}, endpoints: {} };
  }

  const riskClasses = policy.risk_classes;
  const endpoints = policy.endpoints;
  if (policy.version !== 1) {
    errors.push('local/rest/endpoint_policy.json: version must be 1');
  }
  if (!riskClasses || typeof riskClasses !== 'object' || Array.isArray(riskClasses)) {
    errors.push('local/rest/endpoint_policy.json: risk_classes must be an object');
  }
  if (!endpoints || typeof endpoints !== 'object' || Array.isArray(endpoints)) {
    errors.push('local/rest/endpoint_policy.json: endpoints must be an object');
  }

  return {
    riskClasses: riskClasses && typeof riskClasses === 'object' && !Array.isArray(riskClasses) ? riskClasses : {},
    endpoints: endpoints && typeof endpoints === 'object' && !Array.isArray(endpoints) ? endpoints : {},
  };
}

function riskClassesFromRateLimiter() {
  if (!fs.existsSync(RATE_LIMITER_FILE)) {
    return new Set();
  }

  const source = fs.readFileSync(RATE_LIMITER_FILE, 'utf8');
  const matches = source.matchAll(/['"]([A-Z0-9_]+)['"]\s*=>\s*\[/g);
  return new Set(Array.from(matches, (match) => match[1]));
}

function validatePolicyEndpoint(file, expected, riskClasses, rateLimiterClasses, errors) {
  if (!expected || typeof expected !== 'object' || Array.isArray(expected)) {
    errors.push(`local/rest/endpoint_policy.json: ${file} endpoint policy must be an object`);
    return;
  }

  const method = String(expected.method || '').toUpperCase();
  const riskClass = String(expected.risk_class || '').toUpperCase();
  const action = String(expected.action || '').trim();
  const csrf = String(expected.csrf || '').trim();
  const body = String(expected.body || '').trim();

  if (!['GET', 'POST'].includes(method)) {
    errors.push(`local/rest/endpoint_policy.json: ${file} method must be GET or POST`);
  }
  if (!riskClass || !riskClasses[riskClass]) {
    errors.push(`local/rest/endpoint_policy.json: ${file} risk_class ${riskClass || '<empty>'} is not defined`);
  }
  if (rateLimiterClasses.size > 0 && riskClass && !rateLimiterClasses.has(riskClass)) {
    errors.push(`local/rest/endpoint_policy.json: ${file} risk_class ${riskClass} is missing from RateLimiter::classes() defaults`);
  }
  if (!action || !/^[a-z0-9_]+$/.test(action)) {
    errors.push(`local/rest/endpoint_policy.json: ${file} action must be a lowercase endpoint action`);
  }
  if (expected.origin !== 'required') {
    errors.push(`local/rest/endpoint_policy.json: ${file} origin must be required`);
  }
  if (expected.rate_limit !== 'required') {
    errors.push(`local/rest/endpoint_policy.json: ${file} rate_limit must be required`);
  }
  if (method === 'GET' && (csrf !== 'none' || body !== 'none')) {
    errors.push(`local/rest/endpoint_policy.json: ${file} GET endpoints must use csrf=none and body=none`);
  }
  if (method === 'POST' && !['required', 'required_with_trusted_browser_fallback'].includes(csrf)) {
    errors.push(`local/rest/endpoint_policy.json: ${file} POST endpoints must declare explicit csrf policy`);
  }
  if (method === 'POST' && body !== 'json') {
    errors.push(`local/rest/endpoint_policy.json: ${file} POST endpoints must declare body=json`);
  }
  if (expected.noindex !== true) {
    errors.push(`local/rest/endpoint_policy.json: ${file} noindex must be true`);
  }
  if (!expected.contract || typeof expected.contract !== 'string') {
    errors.push(`local/rest/endpoint_policy.json: ${file} contract must name the response contract`);
  }

  if (expected.legacy_alias) {
    const successor = expected.legacy_alias.successor;
    const sunset = expected.legacy_alias.sunset;
    if (typeof successor !== 'string' || !successor.startsWith('/local/rest/')) {
      errors.push(`local/rest/endpoint_policy.json: ${file} legacy successor must be a /local/rest/ path`);
    }
    if (typeof sunset !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(sunset)) {
      errors.push(`local/rest/endpoint_policy.json: ${file} legacy sunset must use YYYY-MM-DD`);
    }
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

function requireRiskAction(file, source, expected, errors) {
  const riskClass = String(expected.risk_class || '').toUpperCase();
  const action = String(expected.action || '').trim();
  const singleQuoteNeedle = `tacticum_rest_rate_limit_by_class('${riskClass}', '${action}'`;
  const doubleQuoteNeedle = `tacticum_rest_rate_limit_by_class("${riskClass}", "${action}"`;
  const mixedQuoteNeedle = `tacticum_rest_rate_limit_by_class('${riskClass}', "${action}"`;
  const reverseMixedQuoteNeedle = `tacticum_rest_rate_limit_by_class("${riskClass}", '${action}'`;
  const needles = [singleQuoteNeedle, doubleQuoteNeedle, mixedQuoteNeedle, reverseMixedQuoteNeedle];
  const index = needles.map((needle) => source.indexOf(needle)).find((needleIndex) => needleIndex >= 0);

  if (index === undefined) {
    errors.push(`${file}: must use endpoint risk class ${riskClass} with action ${action}`);
    return -1;
  }

  return index;
}

function requireNoindexHeader(file, source, errors) {
  if (!source.includes('tacticum_rest_send_noindex_header();')) {
    errors.push(`${file}: must send noindex header through tacticum_rest_send_noindex_header()`);
  }
}

function requireCsrfPolicy(file, source, expected, errors) {
  const csrf = String(expected.csrf || '').trim();
  const allowsTrustedBrowserFallback = /tacticum_rest_check_csrf\s*\([^;]*,\s*true\s*\)/.test(source);

  if (csrf === 'required' && allowsTrustedBrowserFallback) {
    errors.push(`${file}: CSRF trusted browser fallback is not allowed by endpoint_policy.json`);
  }
  if (csrf === 'required_with_trusted_browser_fallback' && !allowsTrustedBrowserFallback) {
    errors.push(`${file}: must explicitly pass true to tacticum_rest_check_csrf() for documented trusted browser fallback`);
  }
}

function requireLegacyAliasHeaders(file, source, expected, errors) {
  if (!expected.legacy_alias) {
    return;
  }

  const successor = String(expected.legacy_alias.successor || '');
  if (!source.includes("header('Deprecation: true')") && !source.includes('header("Deprecation: true")')) {
    errors.push(`${file}: legacy alias must send Deprecation header`);
  }
  if (!source.includes("header('Sunset:") && !source.includes('header("Sunset:')) {
    errors.push(`${file}: legacy alias must send Sunset header`);
  }
  if (successor && !source.includes(`Link: <${successor}>; rel="successor-version"`)) {
    errors.push(`${file}: legacy alias must link successor ${successor}`);
  }
}

function checkPostEndpoint(file, source, expected, errors) {
  const riskClass = String(expected.risk_class || '').toUpperCase();
  const originIndex = requireNeedle(source, file, 'tacticum_rest_validate_origin(', errors);
  const rateLimitIndex = requireNeedle(source, file, 'tacticum_rest_rate_limit_by_class(', errors);
  const riskClassIndex = requireRiskClass(file, source, riskClass, errors);
  const riskActionIndex = requireRiskAction(file, source, expected, errors);
  const methodIndex = requireNeedle(source, file, "tacticum_rest_require_method('POST')", errors);
  const jsonIndex = requireNeedle(source, file, 'tacticum_rest_read_json_body(', errors);
  const csrfIndex = requireNeedle(source, file, 'tacticum_rest_check_csrf(', errors);

  assertBefore(file, 'origin validation', originIndex, 'rate limit', rateLimitIndex, errors);
  assertBefore(file, 'origin validation', originIndex, `${riskClass} rate class`, riskClassIndex, errors);
  assertBefore(file, 'origin validation', originIndex, `${riskClass} action`, riskActionIndex, errors);
  assertBefore(file, 'rate limit', rateLimitIndex, 'POST method check', methodIndex, errors);
  assertBefore(file, 'POST method check', methodIndex, 'JSON body parse', jsonIndex, errors);
  assertBefore(file, 'JSON body parse', jsonIndex, 'CSRF check', csrfIndex, errors);
  requireNoindexHeader(file, source, errors);
  requireCsrfPolicy(file, source, expected, errors);
  requireLegacyAliasHeaders(file, source, expected, errors);

  if (/file_get_contents\s*\(\s*['"]php:\/\/input['"]\s*\)/.test(source) && jsonIndex < 0) {
    errors.push(`${file}: must parse request body through tacticum_rest_read_json_body()`);
  }
}

function checkGetEndpoint(file, source, expected, errors) {
  const riskClass = String(expected.risk_class || '').toUpperCase();
  const originIndex = requireNeedle(source, file, 'tacticum_rest_validate_origin(', errors);
  const rateLimitIndex = requireNeedle(source, file, 'tacticum_rest_rate_limit_by_class(', errors);
  const riskClassIndex = requireRiskClass(file, source, riskClass, errors);
  const riskActionIndex = requireRiskAction(file, source, expected, errors);
  const methodIndex = source.indexOf("$_SERVER['REQUEST_METHOD']");

  if (methodIndex < 0) {
    errors.push(`${file}: missing GET method check`);
  }

  assertBefore(file, 'origin validation', originIndex, 'rate limit', rateLimitIndex, errors);
  assertBefore(file, 'origin validation', originIndex, `${riskClass} rate class`, riskClassIndex, errors);
  assertBefore(file, 'origin validation', originIndex, `${riskClass} action`, riskActionIndex, errors);
  assertBefore(file, 'rate limit', rateLimitIndex, 'GET method check', methodIndex, errors);
  requireNoindexHeader(file, source, errors);

  if (source.includes('tacticum_rest_read_json_body(') || source.includes('tacticum_rest_check_csrf(')) {
    errors.push(`${file}: GET endpoint must not parse JSON body or require CSRF`);
  }
}

function runSelfTest() {
  const requiredCsrfPolicy = {
    method: 'POST',
    risk_class: 'PUBLIC_LEAD_POST',
    action: 'demo',
    csrf: 'required',
  };
  const fallbackCsrfPolicy = {
    ...requiredCsrfPolicy,
    csrf: 'required_with_trusted_browser_fallback',
  };
  const validPost = [
    'tacticum_rest_send_noindex_header();',
    'tacticum_rest_validate_origin();',
    "tacticum_rest_rate_limit_by_class('PUBLIC_LEAD_POST', 'demo');",
    "tacticum_rest_require_method('POST');",
    '$data = tacticum_rest_read_json_body();',
    'tacticum_rest_check_csrf($data);'
  ].join('\n');
  const validPostWithFallback = [
    'tacticum_rest_send_noindex_header();',
    'tacticum_rest_validate_origin();',
    "tacticum_rest_rate_limit_by_class('PUBLIC_LEAD_POST', 'demo');",
    "tacticum_rest_require_method('POST');",
    '$data = tacticum_rest_read_json_body();',
    'tacticum_rest_check_csrf($data, true);'
  ].join('\n');
  const invalidPost = [
    '$data = tacticum_rest_read_json_body();',
    'tacticum_rest_check_csrf($data);',
    'tacticum_rest_validate_origin();',
    "tacticum_rest_rate_limit_by_class('PUBLIC_LEAD_POST', 'demo');",
    "tacticum_rest_require_method('POST');"
  ].join('\n');

  const validErrors = [];
  checkPostEndpoint('valid.php', validPost, requiredCsrfPolicy, validErrors);
  if (validErrors.length > 0) {
    throw new Error(`Valid POST fixture failed:\n- ${validErrors.join('\n- ')}`);
  }

  const validFallbackErrors = [];
  checkPostEndpoint('valid-fallback.php', validPostWithFallback, fallbackCsrfPolicy, validFallbackErrors);
  if (validFallbackErrors.length > 0) {
    throw new Error(`Valid trusted-browser fallback POST fixture failed:\n- ${validFallbackErrors.join('\n- ')}`);
  }

  const forbiddenFallbackErrors = [];
  checkPostEndpoint('forbidden-fallback.php', validPostWithFallback, requiredCsrfPolicy, forbiddenFallbackErrors);
  if (!forbiddenFallbackErrors.some((error) => error.includes('fallback is not allowed'))) {
    throw new Error('Forbidden CSRF fallback fixture did not fail on csrf policy.');
  }

  const invalidErrors = [];
  checkPostEndpoint('invalid.php', invalidPost, requiredCsrfPolicy, invalidErrors);
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
  const policy = loadEndpointPolicy(errors);
  const rateLimiterClasses = riskClassesFromRateLimiter();
  const files = fs.readdirSync(REST_DIR)
    .filter((file) => file.endsWith('.php') && !IGNORED_FILES.has(file))
    .sort();

  for (const [riskClass, settings] of Object.entries(policy.riskClasses)) {
    const limit = Number(settings?.limit || 0);
    const ttl = Number(settings?.ttl || 0);
    if (!/^[A-Z0-9_]+$/.test(riskClass)) {
      errors.push(`local/rest/endpoint_policy.json: risk class ${riskClass} must be uppercase snake case`);
    }
    if (!Number.isInteger(limit) || limit <= 0 || !Number.isInteger(ttl) || ttl <= 0) {
      errors.push(`local/rest/endpoint_policy.json: risk class ${riskClass} must define positive integer limit and ttl`);
    }
  }

  for (const [file, expected] of Object.entries(policy.endpoints)) {
    validatePolicyEndpoint(file, expected, policy.riskClasses, rateLimiterClasses, errors);
    if (!files.includes(file)) {
      errors.push(`local/rest/${file}: expected REST endpoint is missing`);
    }
  }

  for (const file of files) {
    const relativeFile = `local/rest/${file}`;
    const source = fs.readFileSync(path.join(REST_DIR, file), 'utf8');
    const expected = policy.endpoints[file];

    if (!expected) {
      errors.push(`${relativeFile}: endpoint is not classified in local/rest/endpoint_policy.json`);
      continue;
    }

    if (expected.method === 'POST') {
      checkPostEndpoint(relativeFile, source, expected, errors);
    } else if (expected.method === 'GET') {
      checkGetEndpoint(relativeFile, source, expected, errors);
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
