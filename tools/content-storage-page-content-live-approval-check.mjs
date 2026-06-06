#!/usr/bin/env node

import { existsSync, readFileSync } from 'node:fs';

const SCHEMA = 'tacticum.content_storage.page_content_live_approval.v1';
const REQUIRED_OWNERS = ['architect', 'content', 'frontend', 'qa', 'seo'];
const REQUIRED_GATES = [
  'config_runtime_check',
  'strict_page_content_audit',
  'governance_check',
  'seo_check',
  'rollback_plan',
  'post_switch_visual_smoke_required',
  'post_switch_browser_smoke_required',
];
const ALLOWED_DECISIONS = new Set(['pending', 'promote_live', 'keep_shadow', 'demote_shadow']);
const ALLOWED_STATUSES = new Set(['', 'draft', 'seeded', 'shadow', 'live', 'fallback_retired']);
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
]);

function usage() {
  return `Usage:
  node tools/content-storage-page-content-live-approval-check.mjs <approval.json> [--allow-draft]
  node tools/content-storage-page-content-live-approval-check.mjs --self-test

Validates owner approval for page-content section live-status promotion. The
approval may only describe page/section IDs, decisions, statuses and gate
booleans. It must not include raw page copy, admin links or public source switch
approval.
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

function assertObject(value, path, errors) {
  if (!value || typeof value !== 'object' || Array.isArray(value)) {
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
    value.forEach((item, index) => validateNoRawContent(item, `${path}[${index}]`, errors));
    return;
  }
  if (!value || typeof value !== 'object') {
    if (typeof value === 'string') {
      validateSafeString(value, path, errors);
    }
    return;
  }

  for (const [key, child] of Object.entries(value)) {
    const normalized = key.toLowerCase();
    if (FORBIDDEN_KEYS.has(normalized)) {
      errors.push(`${path}.${key} is forbidden; do not store raw page copy, contacts or admin links.`);
    }
    validateNoRawContent(child, `${path}.${key}`, errors);
  }
}

function validateOwners(payload, errors) {
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
    if (payload.status === 'approved') {
      if (row.approved !== true) {
        errors.push(`owners.${owner}.approved must be true for approved status.`);
      }
      if (typeof row.approved_at !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(row.approved_at)) {
        errors.push(`owners.${owner}.approved_at must be YYYY-MM-DD for approved status.`);
      }
      if (typeof row.evidence_ref !== 'string' || row.evidence_ref.trim() === '') {
        errors.push(`owners.${owner}.evidence_ref is required for approved status.`);
      }
    }
  }
}

function validateGates(payload, errors) {
  if (!assertObject(payload.gates, 'gates', errors)) {
    return;
  }

  for (const gate of REQUIRED_GATES) {
    if (typeof payload.gates[gate] !== 'boolean') {
      errors.push(`gates.${gate} must be boolean.`);
      continue;
    }
    if (payload.status === 'approved' && payload.gates[gate] !== true) {
      errors.push(`gates.${gate} must be true for approved status.`);
    }
  }
}

function validateItems(payload, options, errors) {
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

    const key = `${item.page}:${item.section_key}`;
    if (seen.has(key)) {
      errors.push(`${path} duplicates ${key}.`);
    }
    seen.add(key);

    if (!ALLOWED_DECISIONS.has(item.decision)) {
      errors.push(`${path}.decision must be pending, promote_live, keep_shadow or demote_shadow.`);
    }
    if (item.decision === 'pending' && payload.status === 'approved' && !options.allowDraft) {
      errors.push(`${path}.decision cannot be pending in approved mode.`);
    }

    if (typeof item.current_status !== 'string' || !ALLOWED_STATUSES.has(item.current_status)) {
      errors.push(`${path}.current_status must be one of draft, seeded, shadow, live, fallback_retired or empty string.`);
    }
    if (typeof item.target_status !== 'string' || !ALLOWED_STATUSES.has(item.target_status)) {
      errors.push(`${path}.target_status must be one of draft, seeded, shadow, live, fallback_retired or empty string.`);
    }
    if (typeof item.section_live_approved !== 'boolean') {
      errors.push(`${path}.section_live_approved must be boolean.`);
    }
    if (typeof item.fallback_retirement_approved !== 'boolean') {
      errors.push(`${path}.fallback_retirement_approved must be boolean.`);
    }
    if (item.fallback_retirement_approved !== false) {
      errors.push(`${path}.fallback_retirement_approved must remain false; fallback retirement is a separate gate.`);
    }

    if (item.decision === 'promote_live') {
      if (item.target_status !== 'live') {
        errors.push(`${path}.target_status must be live when decision=promote_live.`);
      }
      if (item.section_live_approved !== true) {
        errors.push(`${path}.section_live_approved must be true when decision=promote_live.`);
      }
    }
    if (item.decision === 'keep_shadow') {
      if (item.target_status !== 'shadow') {
        errors.push(`${path}.target_status must be shadow when decision=keep_shadow.`);
      }
      if (item.section_live_approved !== false) {
        errors.push(`${path}.section_live_approved must be false when decision=keep_shadow.`);
      }
    }
    if (item.decision === 'demote_shadow') {
      if (item.target_status !== 'shadow') {
        errors.push(`${path}.target_status must be shadow when decision=demote_shadow.`);
      }
      if (item.section_live_approved !== false) {
        errors.push(`${path}.section_live_approved must be false when decision=demote_shadow.`);
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
    errors.push('release_evidence must be false; use aggregate audit output for release evidence.');
  }
  if (payload.source_switch_approved !== false) {
    errors.push('source_switch_approved must be false; this approval cannot switch page_content.source.');
  }

  validateOwners(payload, errors);
  validateGates(payload, errors);
  validateItems(payload, options, errors);

  return { success: errors.length === 0, errors };
}

function summarize(payload) {
  const counts = {
    total: 0,
    pending: 0,
    promote_live: 0,
    keep_shadow: 0,
    demote_shadow: 0,
  };
  for (const item of Array.isArray(payload.items) ? payload.items : []) {
    counts.total++;
    if (Object.prototype.hasOwnProperty.call(counts, item.decision)) {
      counts[item.decision]++;
    }
  }

  return counts;
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
    evidence_ref: approved ? 'page-content-live-approval-safe-ref' : '',
  }]));
}

function gateRows(approved) {
  return Object.fromEntries(REQUIRED_GATES.map((gate) => [gate, approved]));
}

function runSelfTest() {
  const validDraft = {
    schema: SCHEMA,
    status: 'draft',
    release_evidence: false,
    source_switch_approved: false,
    owners: ownerRows(false),
    gates: gateRows(false),
    items: [
      {
        id: 3073,
        page: '/services/',
        section_key: 'delivery-layer',
        decision: 'pending',
        current_status: 'shadow',
        target_status: '',
        section_live_approved: false,
        fallback_retirement_approved: false,
      },
    ],
  };
  const validApproved = {
    ...validDraft,
    status: 'approved',
    owners: ownerRows(true),
    gates: gateRows(true),
    items: [
      {
        ...validDraft.items[0],
        decision: 'promote_live',
        target_status: 'live',
        section_live_approved: true,
      },
    ],
  };
  const invalidRawCopy = {
    ...validDraft,
    items: [{ ...validDraft.items[0], title: 'Raw page copy must not be stored here' }],
  };
  const invalidSourceSwitch = {
    ...validApproved,
    source_switch_approved: true,
  };

  const checks = [
    [validatePayload(validDraft, { allowDraft: true }).success, 'valid draft with allowDraft should pass'],
    [!validatePayload(validDraft, { allowDraft: false }).success, 'draft without allowDraft should fail'],
    [validatePayload(validApproved, { allowDraft: false }).success, 'valid approved should pass'],
    [!validatePayload(invalidRawCopy, { allowDraft: true }).success, 'raw copy should fail'],
    [!validatePayload(invalidSourceSwitch, { allowDraft: false }).success, 'source switch approval should fail'],
  ];
  const failed = checks.filter(([passed]) => !passed);
  if (failed.length > 0) {
    for (const [, label] of failed) {
      console.error(`Self-test failed: ${label}`);
    }
    process.exit(1);
  }

  console.log('Content storage page-content live approval check self-test passed.');
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
    console.error('Content storage page-content live approval check failed:');
    for (const error of result.errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  const counts = summarize(payload);
  console.log('Content storage page-content live approval check passed.');
  console.log(`Status: ${payload.status}`);
  console.log(`Items: total=${counts.total}, pending=${counts.pending}, promote_live=${counts.promote_live}, keep_shadow=${counts.keep_shadow}, demote_shadow=${counts.demote_shadow}`);
  console.log('Source switch: not approved by this file.');
}

try {
  main();
} catch (error) {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
