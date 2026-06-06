#!/usr/bin/env node

import { existsSync, readFileSync } from 'node:fs';

const DEFAULT_PATH = 'docs/workflow/content-storage-faq-fallback-retirement-2026-06-05.draft.json';
const DEFAULT_DRAFT_DECISION = {
  schema: 'tacticum.content_storage.faq_fallback_retirement.v1',
  status: 'draft',
  date: '2026-06-05',
  release_evidence: false,
  retirement_allowed: false,
  target: {
    fallback: 'product_blocks.faq',
    primary_source: 'faq',
    runtime_marker: 'faq_source=iblock',
  },
  production_evidence: {
    faq_migration_apply_passed: true,
    faq_section_sync_passed: true,
    strict_faq_audit_passed: true,
    strict_product_content_check_passed: true,
    product_source_http_faq_source_passed: true,
    product_cache_clear_with_faq_tag_passed: true,
    seo_check_prod_passed: true,
    chrome_capable_visual_smoke_prod_passed: true,
    chrome_capable_browser_smoke_prod_passed: true,
  },
  owner_gates: {
    content_admin_editability_approved: false,
    qa_rollback_window_approved: false,
    backend_removal_approved: false,
    seo_no_regression_approved: false,
  },
  required_final_rechecks: [
    'php tools/content-storage-audit.php --scope=faq --strict --json',
    'php tools/product-content-check.php --strict --json',
    'TACTICUM_EXPECT_PRODUCT_FAQ_SOURCE=iblock npm run product:source:http:prod',
    'npm run seo:check:prod',
    'npm run visual:smoke:prod',
    'npm run browser:smoke:prod',
  ],
  rollback_plan: {
    required: true,
    strategy: 'Restore product_blocks.faq fallback code path or redeploy previous release, then clear product content cache.',
    cache_clear_command: 'php tools/product-content-cache-clear.php',
  },
  rules: [
    'Do not remove product_blocks.faq fallback while status is draft.',
    'Do not set retirement_allowed=true until all owner gates are true.',
    'Do not store raw FAQ text, raw page HTML, cookies, tokens, contacts or request payloads in this file.',
    'Final approval validates process readiness only; the fallback removal is a separate code change and deployment.',
  ],
};
const REQUIRED_EVIDENCE = [
  'faq_migration_apply_passed',
  'faq_section_sync_passed',
  'strict_faq_audit_passed',
  'strict_product_content_check_passed',
  'product_source_http_faq_source_passed',
  'product_cache_clear_with_faq_tag_passed',
  'seo_check_prod_passed',
  'chrome_capable_visual_smoke_prod_passed',
  'chrome_capable_browser_smoke_prod_passed',
];
const REQUIRED_OWNER_GATES = [
  'content_admin_editability_approved',
  'qa_rollback_window_approved',
  'backend_removal_approved',
  'seo_no_regression_approved',
];
const REQUIRED_FINAL_RECHECKS = [
  'php tools/content-storage-audit.php --scope=faq --strict --json',
  'php tools/product-content-check.php --strict --json',
  'TACTICUM_EXPECT_PRODUCT_FAQ_SOURCE=iblock npm run product:source:http:prod',
  'npm run seo:check:prod',
  'npm run visual:smoke:prod',
  'npm run browser:smoke:prod',
];
const FORBIDDEN_KEYS = new Set([
  'raw',
  'raw_text',
  'raw_html',
  'html',
  'cookie',
  'cookies',
  'session',
  'token',
  'secret',
  'password',
  'phone',
  'email',
  'payload',
  'request',
]);

function usage() {
  return `Usage:
  node tools/content-storage-faq-fallback-retirement-check.mjs [decision.json] [--allow-draft] [--use-embedded-draft]

Validates the decision gate for retiring product_blocks.faq fallback. The checker
does not remove fallback code; it only validates process/readiness evidence.
When the default draft file is absent on production, --allow-draft permits the
embedded safe draft baseline so /docs is not a runtime deploy dependency.
`;
}

function parseArgs(argv) {
  const options = {
    path: DEFAULT_PATH,
    allowDraft: false,
    useEmbeddedDraft: false,
  };
  const args = argv.slice(2);
  for (const arg of args) {
    if (arg === '--help' || arg === '-h') {
      console.log(usage());
      process.exit(0);
    }
    if (arg === '--allow-draft') {
      options.allowDraft = true;
      continue;
    }
    if (arg === '--use-embedded-draft') {
      options.useEmbeddedDraft = true;
      continue;
    }
    if (options.path === DEFAULT_PATH) {
      options.path = arg;
      continue;
    }
    throw new Error(`Unknown argument: ${arg}`);
  }

  return options;
}

function cloneJson(value) {
  return JSON.parse(JSON.stringify(value));
}

function isDefaultDraftPath(path) {
  return path === DEFAULT_PATH || path.endsWith(`/${DEFAULT_PATH}`);
}

function readDecision(options) {
  if (options.useEmbeddedDraft) {
    return {
      decision: cloneJson(DEFAULT_DRAFT_DECISION),
      source: 'embedded default draft',
    };
  }

  if (!existsSync(options.path)) {
    if (options.allowDraft && isDefaultDraftPath(options.path)) {
      return {
        decision: cloneJson(DEFAULT_DRAFT_DECISION),
        source: `embedded default draft; ${options.path} is not present`,
      };
    }

    throw new Error(`File not found: ${options.path}`);
  }

  return {
    decision: JSON.parse(readFileSync(options.path, 'utf8')),
    source: options.path,
  };
}

function isObject(value) {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}

function validateNoForbiddenKeys(value, path, errors) {
  if (Array.isArray(value)) {
    value.forEach((child, index) => validateNoForbiddenKeys(child, `${path}[${index}]`, errors));
    return;
  }
  if (!isObject(value)) {
    if (typeof value === 'string') {
      if (/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i.test(value)) {
        errors.push(`${path} appears to contain an email address.`);
      }
      if (/https?:\/\/|\/bitrix\/admin\//i.test(value)) {
        errors.push(`${path} appears to contain a URL/admin link; use safe evidence refs instead.`);
      }
    }
    return;
  }

  for (const [key, child] of Object.entries(value)) {
    if (FORBIDDEN_KEYS.has(key.toLowerCase())) {
      errors.push(`${path}.${key} is forbidden; do not store raw content or sensitive evidence.`);
    }
    validateNoForbiddenKeys(child, `${path}.${key}`, errors);
  }
}

function validateBooleanMap(source, keys, path, requireTrue, errors) {
  if (!isObject(source)) {
    errors.push(`${path} must be an object.`);
    return;
  }

  for (const key of keys) {
    if (typeof source[key] !== 'boolean') {
      errors.push(`${path}.${key} must be boolean.`);
      continue;
    }
    if (requireTrue && source[key] !== true) {
      errors.push(`${path}.${key} must be true for final approval.`);
    }
  }
}

function validateDecision(decision, options) {
  const errors = [];
  if (!isObject(decision)) {
    return ['Decision must be an object.'];
  }

  validateNoForbiddenKeys(decision, 'decision', errors);

  if (decision.schema !== 'tacticum.content_storage.faq_fallback_retirement.v1') {
    errors.push('schema must be tacticum.content_storage.faq_fallback_retirement.v1.');
  }
  if (!['draft', 'approved'].includes(decision.status)) {
    errors.push('status must be draft or approved.');
  }
  if (decision.status === 'draft' && !options.allowDraft) {
    errors.push('draft status requires --allow-draft.');
  }
  if (decision.release_evidence !== false) {
    errors.push('release_evidence must be false; this file is a decision gate, not release evidence.');
  }

  if (!isObject(decision.target)) {
    errors.push('target must be an object.');
  } else {
    if (decision.target.fallback !== 'product_blocks.faq') {
      errors.push('target.fallback must be product_blocks.faq.');
    }
    if (decision.target.primary_source !== 'faq') {
      errors.push('target.primary_source must be faq.');
    }
    if (decision.target.runtime_marker !== 'faq_source=iblock') {
      errors.push('target.runtime_marker must be faq_source=iblock.');
    }
  }

  const finalMode = decision.status === 'approved' && !options.allowDraft;
  validateBooleanMap(decision.production_evidence, REQUIRED_EVIDENCE, 'production_evidence', finalMode, errors);
  validateBooleanMap(decision.owner_gates, REQUIRED_OWNER_GATES, 'owner_gates', finalMode, errors);

  if (typeof decision.retirement_allowed !== 'boolean') {
    errors.push('retirement_allowed must be boolean.');
  }
  if (finalMode && decision.retirement_allowed !== true) {
    errors.push('retirement_allowed must be true for final approval.');
  }
  if (!finalMode && decision.retirement_allowed === true) {
    errors.push('retirement_allowed must stay false in draft mode.');
  }

  if (!Array.isArray(decision.required_final_rechecks)) {
    errors.push('required_final_rechecks must be an array.');
  } else {
    for (const command of REQUIRED_FINAL_RECHECKS) {
      if (!decision.required_final_rechecks.includes(command)) {
        errors.push(`required_final_rechecks must include: ${command}`);
      }
    }
  }

  if (!isObject(decision.rollback_plan)) {
    errors.push('rollback_plan must be an object.');
  } else {
    if (decision.rollback_plan.required !== true) {
      errors.push('rollback_plan.required must be true.');
    }
    if (typeof decision.rollback_plan.strategy !== 'string' || !decision.rollback_plan.strategy.includes('Restore product_blocks.faq fallback')) {
      errors.push('rollback_plan.strategy must mention restoring product_blocks.faq fallback.');
    }
    if (decision.rollback_plan.cache_clear_command !== 'php tools/product-content-cache-clear.php') {
      errors.push('rollback_plan.cache_clear_command must be php tools/product-content-cache-clear.php.');
    }
  }

  const rules = Array.isArray(decision.rules) ? decision.rules.join('\n') : '';
  for (const ruleNeedle of ['Do not remove product_blocks.faq fallback', 'Do not set retirement_allowed=true', 'separate code change']) {
    if (!rules.includes(ruleNeedle)) {
      errors.push(`rules must include: ${ruleNeedle}`);
    }
  }

  return errors;
}

function summarize(decision) {
  const evidencePassed = REQUIRED_EVIDENCE.filter((key) => decision.production_evidence?.[key] === true).length;
  const ownerPassed = REQUIRED_OWNER_GATES.filter((key) => decision.owner_gates?.[key] === true).length;
  return `Status: ${decision.status}, retirement_allowed=${decision.retirement_allowed}, production_evidence=${evidencePassed}/${REQUIRED_EVIDENCE.length}, owner_gates=${ownerPassed}/${REQUIRED_OWNER_GATES.length}`;
}

function main() {
  const options = parseArgs(process.argv);
  const { decision, source } = readDecision(options);
  const errors = validateDecision(decision, options);
  if (errors.length > 0) {
    console.error('Content storage FAQ fallback retirement check failed:');
    for (const error of errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  console.log('Content storage FAQ fallback retirement check passed.');
  console.log(`Decision source: ${source}.`);
  console.log(summarize(decision));
}

try {
  main();
} catch (error) {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
