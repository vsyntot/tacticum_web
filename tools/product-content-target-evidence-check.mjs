#!/usr/bin/env node

import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const FIXTURE_DIR = path.join(ROOT, 'tools/fixtures/product-content-target-evidence');

const DEFAULT_PRODUCT_CODES = ['platform', 'agents', 'dev', 'forum'];
const REQUIRED_EVIDENCE_IBLOCK_KEYS = ['products', 'product_blocks', 'product_use_cases', 'faq'];
const REQUIRED_ADMIN_MODEL_IBLOCK_KEYS = ['products', 'product_blocks', 'product_use_cases'];
const DEFAULT_EXPECTED_SCHEMA_VERSION = 'v1';
const DEFAULT_ALLOWED_SOURCE_MODES = ['bitrix'];
const DEFAULT_EXPECTED_ROW_SOURCE = 'bitrix';
const DEFAULT_MIN_USE_CASES = 3;

const FORBIDDEN_KEYS = new Set([
  'raw',
  'raw_content',
  'raw_payload',
  'payload',
  'preview_text',
  'detail_text',
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
  const expectedProductCodes = options.productCodes ?? DEFAULT_PRODUCT_CODES;
  const allowedSourceModes = options.allowedSourceModes ?? DEFAULT_ALLOWED_SOURCE_MODES;
  const expectedSchemaVersion = options.expectedSchemaVersion ?? DEFAULT_EXPECTED_SCHEMA_VERSION;
  const expectedRowSource = options.expectedRowSource ?? DEFAULT_EXPECTED_ROW_SOURCE;
  const minUseCases = options.minUseCases ?? DEFAULT_MIN_USE_CASES;
  const allowWarnings = options.allowWarnings ?? false;

  if (!isPlainObject(evidence)) {
    return ['Evidence must be a JSON object.'];
  }

  if (evidence.success !== true) {
    errors.push('success must be true.');
  }
  if (evidence.strict !== true) {
    errors.push('strict must be true.');
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
  if (evidence.schema_version !== expectedSchemaVersion) {
    errors.push(`schema_version must be ${expectedSchemaVersion}.`);
  }
  if (!Number.isInteger(evidence.cache_ttl) || evidence.cache_ttl < 0) {
    errors.push('cache_ttl must be a non-negative integer.');
  }

  validateIblocks(evidence.iblocks, errors);
  validateRows(evidence.rows, expectedProductCodes, expectedRowSource, minUseCases, errors);
  validateAdminModel(evidence.admin_model, errors);
  validateMessages(evidence.errors, 'errors', false, errors);
  validateMessages(evidence.warnings, 'warnings', allowWarnings, errors);
  errors.push(...scanForbiddenEvidence(evidence));

  return errors;
}

function validateAdminModel(adminModel, errors) {
  if (!isPlainObject(adminModel)) {
    errors.push('admin_model must be an object.');
    return;
  }

  if (!isPlainObject(adminModel.v2_schema)) {
    errors.push('admin_model.v2_schema must be an object.');
  } else {
    for (const key of REQUIRED_ADMIN_MODEL_IBLOCK_KEYS) {
      const entry = adminModel.v2_schema[key];
      if (!isPlainObject(entry)) {
        errors.push(`admin_model.v2_schema.${key} must be an object.`);
        continue;
      }
      if (!Number.isInteger(entry.required_properties) || entry.required_properties <= 0) {
        errors.push(`admin_model.v2_schema.${key}.required_properties must be a positive integer.`);
      }
      if (!Array.isArray(entry.missing_properties)) {
        errors.push(`admin_model.v2_schema.${key}.missing_properties must be an array.`);
      } else if (entry.missing_properties.length > 0) {
        errors.push(`admin_model.v2_schema.${key}.missing_properties must be empty.`);
      }
      if (!Array.isArray(entry.inactive_properties)) {
        errors.push(`admin_model.v2_schema.${key}.inactive_properties must be an array.`);
      } else if (entry.inactive_properties.length > 0) {
        errors.push(`admin_model.v2_schema.${key}.inactive_properties must be empty.`);
      }
      if (!Array.isArray(entry.mismatched_properties)) {
        errors.push(`admin_model.v2_schema.${key}.mismatched_properties must be an array.`);
      } else if (entry.mismatched_properties.length > 0) {
        errors.push(`admin_model.v2_schema.${key}.mismatched_properties must be empty.`);
      }
    }
  }

  if (!isPlainObject(adminModel.legacy_json)) {
    errors.push('admin_model.legacy_json must be an object.');
  } else {
    for (const key of [
      'products_json_properties',
      'products_active_json_properties',
      'product_blocks_json_texts',
      'product_use_cases_json_texts',
    ]) {
      if (!Number.isInteger(adminModel.legacy_json[key]) || adminModel.legacy_json[key] !== 0) {
        errors.push(`admin_model.legacy_json.${key} must be 0 after V2 JSON retirement.`);
      }
    }
  }
}

function validateIblocks(iblocks, errors) {
  if (!isPlainObject(iblocks)) {
    errors.push('iblocks must be an object.');
    return;
  }

  for (const key of REQUIRED_EVIDENCE_IBLOCK_KEYS) {
    if (!Number.isInteger(iblocks[key]) || iblocks[key] <= 0) {
      errors.push(`iblocks.${key} must be a positive integer.`);
    }
  }
}

function validateRows(rows, expectedProductCodes, expectedRowSource, minUseCases, errors) {
  if (!Array.isArray(rows)) {
    errors.push('rows must be an array.');
    return;
  }

  const byCode = new Map();
  for (const [index, row] of rows.entries()) {
    if (!isPlainObject(row)) {
      errors.push(`rows[${index}] must be an object.`);
      continue;
    }

    if (typeof row.code !== 'string' || row.code.trim() === '') {
      errors.push(`rows[${index}].code must be a non-empty string.`);
      continue;
    }
    if (byCode.has(row.code)) {
      errors.push(`rows has duplicate product code: ${row.code}.`);
    }
    byCode.set(row.code, row);

    if (row.status !== 'ok') {
      errors.push(`rows[${index}].status for ${row.code} must be ok.`);
    }
    if (row.source !== expectedRowSource) {
      errors.push(`rows[${index}].source for ${row.code} must be ${expectedRowSource}.`);
    }
    if (row.faq_source !== 'iblock') {
      errors.push(`rows[${index}].faq_source for ${row.code} must be iblock.`);
    }
    if (!Number.isInteger(row.use_cases) || row.use_cases < minUseCases) {
      errors.push(`rows[${index}].use_cases for ${row.code} must be >= ${minUseCases}.`);
    }
    if (!Array.isArray(row.missing_blocks)) {
      errors.push(`rows[${index}].missing_blocks for ${row.code} must be an array.`);
    } else if (row.missing_blocks.length > 0) {
      errors.push(`rows[${index}].missing_blocks for ${row.code} must be empty.`);
    }
    if (!Number.isInteger(row.schema_issues) || row.schema_issues !== 0) {
      errors.push(`rows[${index}].schema_issues for ${row.code} must be 0.`);
    }
  }

  for (const code of expectedProductCodes) {
    if (!byCode.has(code)) {
      errors.push(`rows must include product code ${code}.`);
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
    expectedRowSource: DEFAULT_EXPECTED_ROW_SOURCE,
    productCodes: DEFAULT_PRODUCT_CODES,
    minUseCases: DEFAULT_MIN_USE_CASES,
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
    if (argument.startsWith('--expected-row-source=')) {
      options.expectedRowSource = argument.slice('--expected-row-source='.length);
      continue;
    }
    if (argument.startsWith('--product-code=')) {
      options.productCodes = argument
        .slice('--product-code='.length)
        .split(',')
        .map((value) => value.trim())
        .filter(Boolean);
      continue;
    }
    if (argument.startsWith('--min-use-cases=')) {
      options.minUseCases = Number(argument.slice('--min-use-cases='.length));
      continue;
    }
    throw new Error(`Unknown argument: ${argument}`);
  }

  if (options.allowedSourceModes.length === 0) {
    throw new Error('At least one allowed source mode must be provided.');
  }
  if (options.productCodes.length === 0) {
    throw new Error('At least one product code must be provided.');
  }
  if (!Number.isInteger(options.minUseCases) || options.minUseCases < 0) {
    throw new Error('--min-use-cases must be a non-negative integer.');
  }

  return options;
}

function runSelfTest() {
  const valid = readJson(path.join(FIXTURE_DIR, 'valid.json'));
  const invalid = readJson(path.join(FIXTURE_DIR, 'invalid.json'));

  const validErrors = validateEvidence(valid);
  if (validErrors.length > 0) {
    throw new Error(`Valid target evidence fixture failed:\n- ${validErrors.join('\n- ')}`);
  }

  const invalidErrors = validateEvidence(invalid);
  const expectedFragments = [
    'success must be true',
    'source_mode must be one of',
    'configured_source must be bitrix',
    'fallback_allowed must be false',
    'schema_version must be v1',
    'rows[0].missing_blocks',
    'rows[0].schema_issues',
    'admin_model must be an object',
    'errors must be empty',
    'raw_payload',
    'PII-like email value',
  ];

  for (const fragment of expectedFragments) {
    if (!invalidErrors.some((error) => error.includes(fragment))) {
      throw new Error(`Invalid target evidence fixture missed expected error: ${fragment}`);
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
    console.log('Product content target evidence self-test passed.');
    return;
  }

  if (!options.file) {
    throw new Error('Missing --file=/path/to/product-content-check.strict.json.');
  }

  const evidence = readJson(options.file);
  const errors = validateEvidence(evidence, options);

  if (errors.length > 0) {
    console.error('Product content target evidence check failed:');
    for (const error of errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  console.log('Product content target evidence check passed.');
}

try {
  main();
} catch (error) {
  console.error(error.message);
  process.exit(1);
}
