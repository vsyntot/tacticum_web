#!/usr/bin/env node

import { existsSync, readFileSync } from 'node:fs';

const ALLOWED_IBLOCKS = new Set(['cases', 'feedback', 'clients']);
const ALLOWED_PRODUCTS = new Set(['platform', 'agents', 'dev', 'forum']);
const ALLOWED_DECISIONS = new Set(['pending', 'tag', 'global', 'not_public']);
const REQUIRED_OWNERS = ['content', 'sales', 'seo'];
const FORBIDDEN_KEYS = new Set([
  'name',
  'title',
  'text',
  'preview_text',
  'detail_text',
  'client_name',
  'contact',
  'email',
  'phone',
  'url',
  'admin_edit_path',
  'admin_url',
]);

function usage() {
  return `Usage:
  node tools/content-storage-proof-approval-check.mjs <approval.json> [--allow-draft]
  node tools/content-storage-proof-approval-check.mjs --self-test

Validates the owner approval contract for product proof tagging. The file must
store only iblock keys, element IDs, product codes, decisions and approval
booleans. Raw case/testimonial/client copy is forbidden.
`;
}

function parseArgs(argv) {
  const args = argv.slice(2);
  const options = {
    file: '',
    allowDraft: false,
    selfTest: false,
  };

  for (const arg of args) {
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
      errors.push(`${path}.${key} is forbidden; do not store raw proof copy, contacts or admin links.`);
    }
    validateNoRawContent(child, `${path}.${key}`, errors);
  }
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

function validateItems(payload, options, errors) {
  if (!Array.isArray(payload.items)) {
    errors.push('items must be an array.');
    return;
  }

  const seen = new Set();
  for (const [index, item] of payload.items.entries()) {
    const path = `items[${index}]`;
    if (!assertObject(item, path, errors)) {
      continue;
    }

    if (!ALLOWED_IBLOCKS.has(item.iblock)) {
      errors.push(`${path}.iblock must be one of cases, feedback, clients.`);
    }
    if (!Number.isInteger(item.id) || item.id <= 0) {
      errors.push(`${path}.id must be a positive integer.`);
    }
    const key = `${item.iblock}:${item.id}`;
    if (seen.has(key)) {
      errors.push(`${path} duplicates ${key}.`);
    }
    seen.add(key);

    if (!ALLOWED_DECISIONS.has(item.decision)) {
      errors.push(`${path}.decision must be pending, tag, global or not_public.`);
    }
    if (item.decision === 'pending' && payload.status === 'approved' && !options.allowDraft) {
      errors.push(`${path}.decision cannot be pending in approved mode.`);
    }

    if (!Array.isArray(item.product_codes)) {
      errors.push(`${path}.product_codes must be an array.`);
    } else {
      const productSet = new Set();
      for (const code of item.product_codes) {
        if (!ALLOWED_PRODUCTS.has(code)) {
          errors.push(`${path}.product_codes contains unknown product code: ${String(code)}.`);
        }
        if (productSet.has(code)) {
          errors.push(`${path}.product_codes contains duplicate product code: ${String(code)}.`);
        }
        productSet.add(code);
      }
    }

    if (typeof item.product_tag_approved !== 'boolean') {
      errors.push(`${path}.product_tag_approved must be boolean.`);
    }
    if (typeof item.public_render_approved !== 'boolean') {
      errors.push(`${path}.public_render_approved must be boolean.`);
    }

    const productCount = Array.isArray(item.product_codes) ? item.product_codes.length : 0;
    if (item.decision === 'tag') {
      if (productCount === 0) {
        errors.push(`${path}.product_codes must be non-empty when decision=tag.`);
      }
      if (item.product_tag_approved !== true) {
        errors.push(`${path}.product_tag_approved must be true when decision=tag.`);
      }
    }
    if ((item.decision === 'global' || item.decision === 'not_public' || item.decision === 'pending') && productCount > 0) {
      errors.push(`${path}.product_codes must be empty when decision=${item.decision}.`);
    }
    if (item.public_render_approved === true && item.decision !== 'tag') {
      errors.push(`${path}.public_render_approved can be true only when decision=tag.`);
    }
    if (item.public_render_approved === true && payload.status !== 'approved') {
      errors.push(`${path}.public_render_approved cannot be true before approved status.`);
    }
  }
}

function validatePayload(payload, options = {}) {
  const errors = [];
  if (!assertObject(payload, 'payload', errors)) {
    return { success: false, errors };
  }

  validateNoRawContent(payload, 'payload', errors);

  if (payload.schema !== 'tacticum.content_storage.proof_tagging_approval.v1') {
    errors.push('schema must be tacticum.content_storage.proof_tagging_approval.v1.');
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

  validateOwners(payload, errors);
  validateItems(payload, options, errors);

  return {
    success: errors.length === 0,
    errors,
  };
}

function summarize(payload) {
  const counts = {
    total: 0,
    pending: 0,
    tag: 0,
    global: 0,
    not_public: 0,
    public_render_approved: 0,
  };
  for (const item of Array.isArray(payload.items) ? payload.items : []) {
    counts.total++;
    if (Object.prototype.hasOwnProperty.call(counts, item.decision)) {
      counts[item.decision]++;
    }
    if (item.public_render_approved === true) {
      counts.public_render_approved++;
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

function runSelfTest() {
  const validDraft = {
    schema: 'tacticum.content_storage.proof_tagging_approval.v1',
    status: 'draft',
    release_evidence: false,
    owners: {
      content: { approved: false, approved_at: '', evidence_ref: '' },
      sales: { approved: false, approved_at: '', evidence_ref: '' },
      seo: { approved: false, approved_at: '', evidence_ref: '' },
    },
    items: [
      {
        iblock: 'cases',
        id: 98,
        decision: 'pending',
        product_codes: [],
        product_tag_approved: false,
        public_render_approved: false,
      },
    ],
  };
  const validApproved = {
    ...validDraft,
    status: 'approved',
    owners: {
      content: { approved: true, approved_at: '2026-06-05', evidence_ref: 'content-approval-safe-ref' },
      sales: { approved: true, approved_at: '2026-06-05', evidence_ref: 'sales-approval-safe-ref' },
      seo: { approved: true, approved_at: '2026-06-05', evidence_ref: 'seo-approval-safe-ref' },
    },
    items: [
      {
        iblock: 'cases',
        id: 98,
        decision: 'tag',
        product_codes: ['platform'],
        product_tag_approved: true,
        public_render_approved: true,
      },
    ],
  };
  const invalidRawCopy = {
    ...validDraft,
    items: [{ ...validDraft.items[0], title: 'Do not store raw copy' }],
  };

  const validDraftResult = validatePayload(validDraft, { allowDraft: true });
  const strictDraftResult = validatePayload(validDraft, { allowDraft: false });
  const validApprovedResult = validatePayload(validApproved, { allowDraft: false });
  const invalidRawCopyResult = validatePayload(invalidRawCopy, { allowDraft: true });
  const checks = [
    [validDraftResult.success, 'valid draft with allowDraft should pass', validDraftResult.errors],
    [strictDraftResult.success === false, 'draft without allowDraft should fail', strictDraftResult.errors],
    [validApprovedResult.success, 'valid approved should pass', validApprovedResult.errors],
    [invalidRawCopyResult.success === false, 'raw copy should fail', invalidRawCopyResult.errors],
  ];
  const failed = checks.filter(([passed]) => !passed);
  if (failed.length > 0) {
    for (const [, label, errors] of failed) {
      console.error(`Self-test failed: ${label}`);
      for (const error of errors) {
        console.error(`- ${error}`);
      }
    }
    process.exit(1);
  }

  console.log('Content storage proof approval check self-test passed.');
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
    console.error('Content storage proof approval check failed:');
    for (const error of result.errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  const counts = summarize(payload);
  console.log('Content storage proof approval check passed.');
  console.log(`Status: ${payload.status}`);
  console.log(`Items: total=${counts.total}, pending=${counts.pending}, tag=${counts.tag}, global=${counts.global}, not_public=${counts.not_public}, public_render_approved=${counts.public_render_approved}`);
}

try {
  main();
} catch (error) {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
