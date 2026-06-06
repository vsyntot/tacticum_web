#!/usr/bin/env node

import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const FIXTURE_DIR = path.join(ROOT, 'tools/fixtures/product-content-cache-clear-evidence');

const DEFAULT_ALLOWED_SOURCE_MODES = ['bitrix'];
const DEFAULT_EXPECTED_SCHEMA_VERSION = 'v1';
const DEFAULT_CACHE_DIR = '/tacticum/product_content';
const MIN_MANAGED_TAGS = 4;

const FORBIDDEN_KEYS = new Set([
  'raw',
  'raw_content',
  'raw_payload',
  'payload',
  'document_root',
  'path',
  'cookie',
  'cookies',
  'session',
  'sessions',
  'csrf',
  'csrf_token',
  'token',
  'secret',
  'password',
  'phone',
  'email',
  'ip',
  'ip_address',
  'user_agent',
]);

function validateEvidence(evidence, options = {}) {
  const errors = [];
  const allowedSourceModes = options.allowedSourceModes ?? DEFAULT_ALLOWED_SOURCE_MODES;
  const expectedSchemaVersion = options.expectedSchemaVersion ?? DEFAULT_EXPECTED_SCHEMA_VERSION;
  const expectedCacheDir = options.expectedCacheDir ?? DEFAULT_CACHE_DIR;
  const allowWarnings = options.allowWarnings ?? false;

  if (!isPlainObject(evidence)) {
    return ['Evidence must be a JSON object.'];
  }

  if (evidence.success !== true) {
    errors.push('success must be true.');
  }
  if (evidence.dry_run !== true) {
    errors.push('dry_run must be true.');
  }
  if (evidence.cache_cleared !== false) {
    errors.push('cache_cleared must be false for dry-run evidence.');
  }
  if (!allowedSourceModes.includes(evidence.source_mode)) {
    errors.push(`source_mode must be one of: ${allowedSourceModes.join(', ')}.`);
  }
  if (evidence.configured_source !== 'bitrix') {
    errors.push('configured_source must be bitrix.');
  }
  if (evidence.fallback_allowed !== false) {
    errors.push('fallback_allowed must be false.');
  }
  if (!Number.isInteger(evidence.cache_ttl) || evidence.cache_ttl < 0) {
    errors.push('cache_ttl must be a non-negative integer.');
  }
  if (evidence.schema_version !== expectedSchemaVersion) {
    errors.push(`schema_version must be ${expectedSchemaVersion}.`);
  }
  if (evidence.cache_dir !== expectedCacheDir) {
    errors.push(`cache_dir must be ${expectedCacheDir}.`);
  }

  validateIblockIds(evidence.iblock_ids, errors);
  validateManagedTags(evidence.managed_tags, evidence.iblock_ids, errors);
  validateMessages(evidence.errors, 'errors', false, errors);
  validateMessages(evidence.warnings, 'warnings', allowWarnings, errors);
  errors.push(...scanForbiddenEvidence(evidence));

  return errors;
}

function validateIblockIds(iblockIds, errors) {
  if (!Array.isArray(iblockIds)) {
    errors.push('iblock_ids must be an array.');
    return;
  }
  if (iblockIds.length < MIN_MANAGED_TAGS) {
    errors.push(`iblock_ids must include at least ${MIN_MANAGED_TAGS} product content/FAQ/proof iblocks.`);
  }

  const seen = new Set();
  for (const [index, id] of iblockIds.entries()) {
    if (!Number.isInteger(id) || id <= 0) {
      errors.push(`iblock_ids[${index}] must be a positive integer.`);
      continue;
    }
    if (seen.has(id)) {
      errors.push(`iblock_ids has duplicate id: ${id}.`);
    }
    seen.add(id);
  }
}

function validateManagedTags(managedTags, iblockIds, errors) {
  if (!Array.isArray(managedTags)) {
    errors.push('managed_tags must be an array.');
    return;
  }
  if (!Array.isArray(iblockIds)) {
    return;
  }

  const expectedTags = iblockIds
    .filter((id) => Number.isInteger(id) && id > 0)
    .map((id) => `iblock_id_${id}`);
  const seen = new Set();

  for (const [index, tag] of managedTags.entries()) {
    if (typeof tag !== 'string' || tag.trim() === '') {
      errors.push(`managed_tags[${index}] must be a non-empty string.`);
      continue;
    }
    if (seen.has(tag)) {
      errors.push(`managed_tags has duplicate tag: ${tag}.`);
    }
    seen.add(tag);
  }

  for (const tag of expectedTags) {
    if (!managedTags.includes(tag)) {
      errors.push(`managed_tags must include ${tag}.`);
    }
  }
  for (const tag of managedTags) {
    if (typeof tag === 'string' && !expectedTags.includes(tag)) {
      errors.push(`managed_tags has unexpected tag ${tag}.`);
    }
  }
}

function validateMessages(messages, field, allowNonEmpty, errors) {
  if (!Array.isArray(messages)) {
    errors.push(`${field} must be an array.`);
    return;
  }
  if (!allowNonEmpty && messages.length > 0) {
    errors.push(`${field} must be empty.`);
  }
  for (const [index, message] of messages.entries()) {
    if (typeof message !== 'string') {
      errors.push(`${field}[${index}] must be a string.`);
    }
  }
}

function scanForbiddenEvidence(value, pathParts = []) {
  const issues = [];

  if (Array.isArray(value)) {
    value.forEach((item, index) => {
      issues.push(...scanForbiddenEvidence(item, [...pathParts, String(index)]));
    });
    return issues;
  }

  if (isPlainObject(value)) {
    for (const [key, child] of Object.entries(value)) {
      const normalizedKey = key.toLowerCase().replace(/[-\s]+/g, '_');
      if (FORBIDDEN_KEYS.has(normalizedKey)) {
        issues.push(`Forbidden evidence key ${[...pathParts, key].join('.')} is present.`);
      }
      issues.push(...scanForbiddenEvidence(child, [...pathParts, key]));
    }
    return issues;
  }

  if (typeof value === 'string') {
    const pathLabel = pathParts.join('.') || '<root>';
    if (/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i.test(value)) {
      issues.push(`PII-like email value is present at ${pathLabel}.`);
    }
    if (/(?:\+?\d[\s().-]*){10,}/.test(value)) {
      issues.push(`PII-like phone value is present at ${pathLabel}.`);
    }
  }

  return issues;
}

function parseArgs(argv) {
  const options = {
    file: null,
    selfTest: false,
    allowedSourceModes: DEFAULT_ALLOWED_SOURCE_MODES,
    expectedSchemaVersion: DEFAULT_EXPECTED_SCHEMA_VERSION,
    expectedCacheDir: DEFAULT_CACHE_DIR,
    allowWarnings: false,
  };

  for (const argument of argv.slice(2)) {
    if (argument === '--self-test') {
      options.selfTest = true;
      continue;
    }
    if (argument === '--allow-warnings') {
      options.allowWarnings = true;
      continue;
    }
    if (argument.startsWith('--file=')) {
      options.file = path.resolve(argument.slice('--file='.length));
      continue;
    }
    if (argument.startsWith('--allow-source=')) {
      options.allowedSourceModes = argument
        .slice('--allow-source='.length)
        .split(',')
        .map((value) => value.trim())
        .filter(Boolean);
      continue;
    }
    if (argument.startsWith('--expected-schema-version=')) {
      options.expectedSchemaVersion = argument.slice('--expected-schema-version='.length);
      continue;
    }
    if (argument.startsWith('--expected-cache-dir=')) {
      options.expectedCacheDir = argument.slice('--expected-cache-dir='.length);
      continue;
    }
    throw new Error(`Unknown argument: ${argument}`);
  }

  if (options.allowedSourceModes.length === 0) {
    throw new Error('At least one allowed source mode must be provided.');
  }

  return options;
}

function runSelfTest() {
  const valid = readJson(path.join(FIXTURE_DIR, 'valid.json'));
  const invalid = readJson(path.join(FIXTURE_DIR, 'invalid.json'));

  const validErrors = validateEvidence(valid);
  if (validErrors.length > 0) {
    throw new Error(`Valid cache-clear evidence fixture failed:\n- ${validErrors.join('\n- ')}`);
  }

  const invalidErrors = validateEvidence(invalid);
  const expectedFragments = [
    'success must be true',
    'dry_run must be true',
    'cache_cleared must be false',
    'source_mode must be one of',
    'configured_source must be bitrix',
    'fallback_allowed must be false',
    'cache_ttl must be a non-negative integer',
    'schema_version must be v1',
    'cache_dir must be /tacticum/product_content',
    'iblock_ids must include at least',
    'managed_tags must include',
    'errors must be empty',
    'document_root',
    'PII-like email value',
  ];

  for (const fragment of expectedFragments) {
    if (!invalidErrors.some((error) => error.includes(fragment))) {
      throw new Error(`Invalid cache-clear evidence fixture missed expected error: ${fragment}`);
    }
  }
}

function readJson(filePath) {
  try {
    return JSON.parse(fs.readFileSync(filePath, 'utf8'));
  } catch (error) {
    throw new Error(`Cannot read JSON evidence ${filePath}: ${error.message}`);
  }
}

function isPlainObject(value) {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}

function main() {
  const options = parseArgs(process.argv);

  if (options.selfTest) {
    runSelfTest();
    console.log('Product content cache-clear evidence self-test passed.');
    return;
  }

  if (!options.file) {
    throw new Error('Missing --file=/path/to/product-content-cache-clear.dry-run.json.');
  }

  const evidence = readJson(options.file);
  const errors = validateEvidence(evidence, options);

  if (errors.length > 0) {
    console.error('Product content cache-clear evidence check failed:');
    for (const error of errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  console.log('Product content cache-clear evidence check passed.');
}

try {
  main();
} catch (error) {
  console.error(error.message);
  process.exit(1);
}
