#!/usr/bin/env node

import { existsSync, readFileSync } from 'node:fs';

const SCHEMA = 'tacticum.content_storage.page_content_fallback_retirement.v1';
const REQUIRED_OWNERS = ['architect', 'content', 'frontend', 'qa', 'seo'];
const REQUIRED_EVIDENCE = [
  'config_runtime_check_passed',
  'strict_page_content_audit_passed',
  'page_content_source_http_passed',
  'seo_check_prod_passed',
  'targeted_visual_smoke_passed',
  'targeted_browser_smoke_passed',
  'allow_fallback_true',
  'rollback_plan_approved',
  'admin_editability_review_passed',
];
const REQUIRED_OWNER_GATES = [
  'architect_runtime_boundary_approved',
  'content_admin_editability_approved',
  'frontend_fallback_removal_approved',
  'qa_rollback_window_approved',
  'seo_no_regression_approved',
];
const REQUIRED_FINAL_RECHECKS = [
  'npm run config:runtime:check',
  'npm run page-content:source:http:prod',
  'php tools/content-storage-audit.php --scope=page-content --strict --json',
  'npm run seo:check:prod',
  'TACTICUM_VISUAL_PAGES=/services/,/price/,/contacts/,/offer/ npm run visual:smoke:prod',
  'TACTICUM_VISUAL_PAGES=/services/,/price/,/contacts/,/offer/ npm run browser:smoke:prod',
];
const ALLOWED_DECISIONS = new Set(['pending', 'retire_fallback', 'keep_fallback']);
const ALLOWED_STATUSES = new Set(['live', 'fallback_retired']);
const ALLOWED_SECTIONS = new Map([
  ['/services/', new Set(['delivery-layer', 'process', 'tech'])],
  ['/price/', new Set(['features', 'workstreams'])],
  ['/contacts/', new Set(['routing', 'cards'])],
  ['/offer/', new Set(['product-bridge', 'bottom-cta'])],
]);
const FORBIDDEN_KEYS = new Set([
  'name',
  'title',
  'text',
  'copy',
  'html',
  'raw',
  'raw_html',
  'preview_text',
  'detail_text',
  'eyebrow',
  'cta_text',
  'admin_edit_path',
  'admin_url',
  'email',
  'phone',
  'contact',
  'cookie',
  'cookies',
  'session',
  'token',
  'secret',
  'password',
  'payload',
  'request',
]);

function usage() {
  return `Usage:
  node tools/content-storage-page-content-fallback-retirement-check.mjs <approval.json> [--allow-draft]
  node tools/content-storage-page-content-fallback-retirement-check.mjs --self-test

Validates owner approval for retiring PHP fallback partials for wave 1
page-content sections. The approval may only describe page/section IDs,
statuses, decisions and safe evidence booleans. It must not include raw page
copy, contacts, screenshots, admin links or request data.
`;
}

function parseArgs(argv) {
  const options = {
    file: '',
    allowDraft: false,
    selfTest: false,
  };

  for (const arg of argv.slice(2)) {
    if (arg === '--help' || arg === '-h') {
      console.log(usage());
      process.exit(0);
    }
    if (arg === '--allow-draft') {
      options.allowDraft = true;
      continue;
    }
    if (arg === '--self-test') {
      options.selfTest = true;
      continue;
    }
    if (!options.file) {
      options.file = arg;
      continue;
    }

    throw new Error(`Unknown argument: ${arg}`);
  }

  return options;
}

function isObject(value) {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}

function assertObject(value, path, errors) {
  if (!isObject(value)) {
    errors.push(`${path} must be an object.`);
    return false;
  }

  return true;
}

function validateSafeString(value, path, errors) {
  if (/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i.test(value)) {
    errors.push(`${path} appears to contain an email address.`);
  }
  const digitCount = value.replace(/\D/g, '').length;
  if (digitCount >= 10 && /(?:\+?\d[\s().-]*){10,}/.test(value)) {
    errors.push(`${path} appears to contain a phone-like value.`);
  }
  if (/https?:\/\//i.test(value)) {
    errors.push(`${path} appears to contain a URL.`);
  }
  if (/\/bitrix\/admin\//i.test(value)) {
    errors.push(`${path} appears to contain a Bitrix admin URL.`);
  }
}

function validateNoRawContent(value, path, errors) {
  if (Array.isArray(value)) {
    value.forEach((child, index) => validateNoRawContent(child, `${path}[${index}]`, errors));
    return;
  }
  if (!isObject(value)) {
    if (typeof value === 'string') {
      validateSafeString(value, path, errors);
    }
    return;
  }

  for (const [key, child] of Object.entries(value)) {
    const normalized = key.toLowerCase();
    if (FORBIDDEN_KEYS.has(normalized)) {
      errors.push(`${path}.${key} is forbidden; do not store raw page copy, contacts, admin links or sensitive evidence.`);
    }
    validateNoRawContent(child, `${path}.${key}`, errors);
  }
}

function validateBooleanMap(source, keys, path, requireTrue, errors) {
  if (!assertObject(source, path, errors)) {
    return;
  }

  for (const key of keys) {
    if (typeof source[key] !== 'boolean') {
      errors.push(`${path}.${key} must be boolean.`);
      continue;
    }
    if (requireTrue && source[key] !== true) {
      errors.push(`${path}.${key} must be true for approved retirement.`);
    }
  }
}

function validateOwners(payload, finalMode, errors) {
  if (!assertObject(payload.owners, 'owners', errors)) {
    return;
  }

  for (const owner of REQUIRED_OWNERS) {
    const row = payload.owners[owner];
    if (!assertObject(row, `owners.${owner}`, errors)) {
      continue;
    }
    if (typeof row.approved !== 'boolean') {
      errors.push(`owners.${owner}.approved must be boolean.`);
    }
    if (finalMode) {
      if (row.approved !== true) {
        errors.push(`owners.${owner}.approved must be true for approved retirement.`);
      }
      if (typeof row.approved_at !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(row.approved_at)) {
        errors.push(`owners.${owner}.approved_at must be YYYY-MM-DD for approved retirement.`);
      }
      if (typeof row.evidence_ref !== 'string' || row.evidence_ref.trim() === '') {
        errors.push(`owners.${owner}.evidence_ref is required for approved retirement.`);
      }
    }
  }
}

function validateItems(payload, options, finalMode, errors) {
  if (!Array.isArray(payload.items)) {
    errors.push('items must be an array.');
    return;
  }
  if (payload.items.length === 0) {
    errors.push('items must not be empty.');
    return;
  }

  const seen = new Set();
  for (const [index, item] of payload.items.entries()) {
    const path = `items[${index}]`;
    if (!assertObject(item, path, errors)) {
      continue;
    }

    if (!Number.isInteger(item.id) || item.id <= 0) {
      errors.push(`${path}.id must be a positive integer.`);
    }
    if (typeof item.page !== 'string' || !ALLOWED_SECTIONS.has(item.page)) {
      errors.push(`${path}.page must be one of ${Array.from(ALLOWED_SECTIONS.keys()).join(', ')}.`);
    }
    const sections = ALLOWED_SECTIONS.get(item.page) || new Set();
    if (typeof item.section_key !== 'string' || !sections.has(item.section_key)) {
      errors.push(`${path}.section_key is not allowed for page ${String(item.page)}.`);
    }
    if (typeof item.template_key !== 'string' || item.template_key.trim() === '') {
      errors.push(`${path}.template_key must be a non-empty string.`);
    }

    const key = `${item.page}:${item.section_key}`;
    if (seen.has(key)) {
      errors.push(`${path} duplicates ${key}.`);
    }
    seen.add(key);

    if (!ALLOWED_DECISIONS.has(item.decision)) {
      errors.push(`${path}.decision must be pending, retire_fallback or keep_fallback.`);
    }
    if (item.decision === 'pending' && finalMode && !options.allowDraft) {
      errors.push(`${path}.decision cannot be pending in approved retirement.`);
    }
    if (typeof item.current_status !== 'string' || !ALLOWED_STATUSES.has(item.current_status)) {
      errors.push(`${path}.current_status must be live or fallback_retired.`);
    }
    if (typeof item.fallback_partial_present !== 'boolean') {
      errors.push(`${path}.fallback_partial_present must be boolean.`);
    }
    if (!Number.isInteger(item.blocks_total) || item.blocks_total < 0) {
      errors.push(`${path}.blocks_total must be a non-negative integer.`);
    }
    if (!Number.isInteger(item.blocks_active) || item.blocks_active < 0) {
      errors.push(`${path}.blocks_active must be a non-negative integer.`);
    }
    if (Number.isInteger(item.blocks_total) && Number.isInteger(item.blocks_active) && item.blocks_active > item.blocks_total) {
      errors.push(`${path}.blocks_active must not exceed blocks_total.`);
    }
    if (typeof item.admin_editability_approved !== 'boolean') {
      errors.push(`${path}.admin_editability_approved must be boolean.`);
    }
    if (typeof item.fallback_retirement_approved !== 'boolean') {
      errors.push(`${path}.fallback_retirement_approved must be boolean.`);
    }

    if (item.decision === 'retire_fallback') {
      if (item.current_status !== 'live') {
        errors.push(`${path}.current_status must be live when decision=retire_fallback.`);
      }
      if (item.fallback_partial_present !== true) {
        errors.push(`${path}.fallback_partial_present must be true before retirement.`);
      }
      if (item.admin_editability_approved !== true) {
        errors.push(`${path}.admin_editability_approved must be true when decision=retire_fallback.`);
      }
      if (item.fallback_retirement_approved !== true) {
        errors.push(`${path}.fallback_retirement_approved must be true when decision=retire_fallback.`);
      }
    }
    if (item.decision === 'keep_fallback') {
      if (item.fallback_retirement_approved !== false) {
        errors.push(`${path}.fallback_retirement_approved must be false when decision=keep_fallback.`);
      }
    }
  }
}

function validatePayload(payload, options = {}) {
  const errors = [];
  if (!assertObject(payload, 'payload', errors)) {
    return { success: false, errors };
  }

  validateNoRawContent(payload, 'payload', errors);

  if (payload.schema !== SCHEMA) {
    errors.push(`schema must be ${SCHEMA}.`);
  }
  if (!['draft', 'approved'].includes(payload.status)) {
    errors.push('status must be draft or approved.');
  }
  if (payload.status === 'draft' && !options.allowDraft) {
    errors.push('draft status requires --allow-draft.');
  }
  if (payload.release_evidence !== false) {
    errors.push('release_evidence must be false; use aggregate audit/source/smoke outputs for release evidence.');
  }

  const finalMode = payload.status === 'approved' && !options.allowDraft;
  if (typeof payload.retirement_allowed !== 'boolean') {
    errors.push('retirement_allowed must be boolean.');
  } else if (finalMode && payload.retirement_allowed !== true) {
    errors.push('retirement_allowed must be true for approved retirement.');
  } else if (!finalMode && payload.retirement_allowed !== false) {
    errors.push('retirement_allowed must stay false in draft mode.');
  }

  validateOwners(payload, finalMode, errors);
  validateBooleanMap(payload.production_evidence, REQUIRED_EVIDENCE, 'production_evidence', finalMode, errors);
  validateBooleanMap(payload.owner_gates, REQUIRED_OWNER_GATES, 'owner_gates', finalMode, errors);
  validateItems(payload, options, finalMode, errors);

  if (!Array.isArray(payload.required_final_rechecks)) {
    errors.push('required_final_rechecks must be an array.');
  } else {
    for (const command of REQUIRED_FINAL_RECHECKS) {
      if (!payload.required_final_rechecks.includes(command)) {
        errors.push(`required_final_rechecks must include: ${command}`);
      }
    }
  }

  if (!assertObject(payload.rollback_plan, 'rollback_plan', errors)) {
    return { success: errors.length === 0, errors };
  }
  if (payload.rollback_plan.required !== true) {
    errors.push('rollback_plan.required must be true.');
  }
  if (typeof payload.rollback_plan.strategy !== 'string' || !payload.rollback_plan.strategy.includes('page_content.source=fallback')) {
    errors.push('rollback_plan.strategy must mention page_content.source=fallback rollback.');
  }
  if (payload.rollback_plan.final_recheck_command !== 'npm run page-content:source:http:fallback:prod') {
    errors.push('rollback_plan.final_recheck_command must be npm run page-content:source:http:fallback:prod.');
  }

  const rules = Array.isArray(payload.rules) ? payload.rules.join('\n') : '';
  for (const needle of [
    'Do not retire PHP fallback partials while status is draft.',
    'Do not set retirement_allowed=true until all evidence and owner gates are true.',
    'Fallback retirement is a separate code change and deployment.',
  ]) {
    if (!rules.includes(needle)) {
      errors.push(`rules must include: ${needle}`);
    }
  }

  return { success: errors.length === 0, errors };
}

function summarize(payload) {
  const counts = {
    total: 0,
    pending: 0,
    retire_fallback: 0,
    keep_fallback: 0,
  };
  for (const item of Array.isArray(payload.items) ? payload.items : []) {
    counts.total++;
    if (Object.prototype.hasOwnProperty.call(counts, item.decision)) {
      counts[item.decision]++;
    }
  }
  const evidencePassed = REQUIRED_EVIDENCE.filter((key) => payload.production_evidence?.[key] === true).length;
  const ownerPassed = REQUIRED_OWNER_GATES.filter((key) => payload.owner_gates?.[key] === true).length;

  return { ...counts, evidencePassed, ownerPassed };
}

function readJson(path) {
  if (!existsSync(path)) {
    throw new Error(`File not found: ${path}`);
  }

  return JSON.parse(readFileSync(path, 'utf8'));
}

function ownerRows(approved) {
  return Object.fromEntries(REQUIRED_OWNERS.map((owner) => [owner, {
    approved,
    approved_at: approved ? '2026-06-06' : '',
    evidence_ref: approved ? 'page-content-fallback-retirement-safe-ref' : '',
  }]));
}

function booleanRows(keys, value) {
  return Object.fromEntries(keys.map((key) => [key, value]));
}

function basePayload(status = 'draft') {
  const approved = status === 'approved';
  return {
    schema: SCHEMA,
    status,
    release_evidence: false,
    retirement_allowed: approved,
    owners: ownerRows(approved),
    production_evidence: booleanRows(REQUIRED_EVIDENCE, approved),
    owner_gates: booleanRows(REQUIRED_OWNER_GATES, approved),
    required_final_rechecks: REQUIRED_FINAL_RECHECKS,
    rollback_plan: {
      required: true,
      strategy: 'Set page_content.source=fallback, keep allow_fallback=true and redeploy previous partial fallback code if needed.',
      final_recheck_command: 'npm run page-content:source:http:fallback:prod',
    },
    rules: [
      'Do not retire PHP fallback partials while status is draft.',
      'Do not set retirement_allowed=true until all evidence and owner gates are true.',
      'Fallback retirement is a separate code change and deployment.',
    ],
    items: [
      {
        id: 3073,
        page: '/services/',
        section_key: 'delivery-layer',
        template_key: 'product-card-grid',
        decision: approved ? 'retire_fallback' : 'pending',
        current_status: 'live',
        fallback_partial_present: true,
        blocks_total: 4,
        blocks_active: 4,
        admin_editability_approved: approved,
        fallback_retirement_approved: approved,
      },
    ],
  };
}

function runSelfTest() {
  const validDraft = basePayload('draft');
  const validApproved = basePayload('approved');
  const invalidRawCopy = {
    ...validDraft,
    items: [{ ...validDraft.items[0], title: 'Raw page copy must not be stored here' }],
  };
  const invalidRetireWithoutEvidence = {
    ...validApproved,
    production_evidence: { ...validApproved.production_evidence, seo_check_prod_passed: false },
  };
  const invalidDraftAllowedRetirement = {
    ...validDraft,
    retirement_allowed: true,
  };

  const checks = [
    [validatePayload(validDraft, { allowDraft: true }).success, 'valid draft with allowDraft should pass'],
    [!validatePayload(validDraft, { allowDraft: false }).success, 'draft without allowDraft should fail'],
    [validatePayload(validApproved, { allowDraft: false }).success, 'valid approved should pass'],
    [!validatePayload(invalidRawCopy, { allowDraft: true }).success, 'raw copy should fail'],
    [!validatePayload(invalidRetireWithoutEvidence, { allowDraft: false }).success, 'approved retirement without evidence should fail'],
    [!validatePayload(invalidDraftAllowedRetirement, { allowDraft: true }).success, 'draft retirement_allowed=true should fail'],
  ];
  const failed = checks.filter(([passed]) => !passed);
  if (failed.length > 0) {
    for (const [, label] of failed) {
      console.error(`Self-test failed: ${label}`);
    }
    process.exit(1);
  }

  console.log('Content storage page-content fallback retirement check self-test passed.');
}

function main() {
  const options = parseArgs(process.argv);
  if (options.selfTest) {
    runSelfTest();
    return;
  }
  if (!options.file) {
    throw new Error('Missing approval JSON file.\n\n' + usage());
  }

  const payload = readJson(options.file);
  const result = validatePayload(payload, options);
  if (!result.success) {
    console.error('Content storage page-content fallback retirement check failed:');
    for (const error of result.errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  const counts = summarize(payload);
  console.log('Content storage page-content fallback retirement check passed.');
  console.log(`Status: ${payload.status}, retirement_allowed=${payload.retirement_allowed}`);
  console.log(`Items: total=${counts.total}, pending=${counts.pending}, retire_fallback=${counts.retire_fallback}, keep_fallback=${counts.keep_fallback}`);
  console.log(`Evidence: production=${counts.evidencePassed}/${REQUIRED_EVIDENCE.length}, owner_gates=${counts.ownerPassed}/${REQUIRED_OWNER_GATES.length}`);
}

try {
  main();
} catch (error) {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
