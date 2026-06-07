#!/usr/bin/env node

import { existsSync, readFileSync } from 'node:fs';

const SCHEMA = 'tacticum.offer.taxonomy_presets_owner_approval.v1';
const REQUIRED_OWNERS = ['architect', 'backend', 'pm', 'content', 'seo', 'sales', 'qa'];
const REQUIRED_GATES = [
  'adr_review',
  'content_storage_boundary',
  'seo_noindex_preserved',
  'counts_runtime_derived',
  'no_synthetic_proof_claims',
  'rollback_plan',
  'cache_strategy',
  'qa_checker_scope',
  'config_source_mode',
];
const ALLOWED_DIMENSIONS = new Set(['sector', 'scenario', 'phase', 'budget']);
const REQUIRED_APPROVED_DIMENSIONS = ['sector', 'scenario', 'phase'];
const ALLOWED_TAXONOMY_SOURCES = new Set(['defer', 'bitrix_terms', 'config_terms', 'php_fallback_only']);
const ALLOWED_PRESET_SOURCES = new Set(['defer', 'featured_terms', 'filter_presets']);
const ALLOWED_BUDGET_DECISIONS = new Set(['defer', 'php_config', 'bitrix_terms']);
const ALLOWED_PRODUCT_RELATIONS = new Set(['defer', 'optional_relation', 'none']);
const ALLOWED_INDEXABILITY = new Set(['defer', 'keep_noindex', 'seo_landing_project']);
const ALLOWED_PRODUCT_FAMILIES = new Set(['', 'none', 'platform', 'agents', 'dev', 'forum']);
const FORBIDDEN_KEYS = new Set([
  'html',
  'raw_html',
  'copy',
  'raw_copy',
  'preview_text',
  'detail_text',
  'case_text',
  'proof_copy',
  'admin_url',
  'admin_edit_path',
  'email',
  'phone',
  'contact',
  'customer',
  'client_name',
  'offer_ids',
  'item_ids',
  'result_ids',
  'offer_count',
  'result_count',
  'items_count',
  'available_count',
]);

function usage() {
  return `Usage:
  node tools/offer-taxonomy-approval-check.mjs <approval.json> [--allow-draft]
  node tools/offer-taxonomy-approval-check.mjs --self-test

Validates safe owner approval for /offer/ taxonomy and preset model decisions.
The file may approve model semantics, labels, aliases and governance gates, but
must not approve runtime source switch, iblock apply, stored counts, raw offer
copy, admin links, PII or SEO indexability changes.
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

function safeString(value, path, errors, options = {}) {
  if (typeof value !== 'string') {
    errors.push(`${path} must be a string.`);
    return '';
  }

  const trimmed = value.trim();
  const maxLength = options.maxLength || 160;
  if (trimmed.length > maxLength) {
    errors.push(`${path} must be ${maxLength} characters or shorter.`);
  }
  if (/<[a-z][\s\S]*>/i.test(trimmed)) {
    errors.push(`${path} appears to contain HTML.`);
  }
  if (/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i.test(trimmed)) {
    errors.push(`${path} appears to contain an email address.`);
  }
  if (/https?:\/\//i.test(trimmed) || /\/bitrix\/admin\//i.test(trimmed)) {
    errors.push(`${path} appears to contain a URL or Bitrix admin path.`);
  }
  const digitCount = trimmed.replace(/\D/g, '').length;
  if (digitCount >= 10 && /(?:\+?\d[\s().-]*){10,}/.test(trimmed)) {
    errors.push(`${path} appears to contain a phone-like value.`);
  }

  return trimmed;
}

function validateSafePayload(value, path, errors) {
  if (Array.isArray(value)) {
    value.forEach((item, index) => validateSafePayload(item, `${path}[${index}]`, errors));
    return;
  }
  if (!value || typeof value !== 'object') {
    if (typeof value === 'string') {
      safeString(value, path, errors, { maxLength: 320 });
    }
    return;
  }

  for (const [key, child] of Object.entries(value)) {
    const normalized = key.toLowerCase();
    if (FORBIDDEN_KEYS.has(normalized)) {
      errors.push(`${path}.${key} is forbidden; approval must not store raw copy, PII, admin links or stored result counts.`);
    }
    validateSafePayload(child, `${path}.${key}`, errors);
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

function validateEnum(value, allowed, path, errors) {
  if (typeof value !== 'string' || !allowed.has(value)) {
    errors.push(`${path} must be one of ${Array.from(allowed).join(', ')}.`);
  }
}

function validateDecisions(payload, errors) {
  if (!assertObject(payload.decisions, 'decisions', errors)) {
    return;
  }

  const decisions = payload.decisions;
  validateEnum(decisions.taxonomy_source, ALLOWED_TAXONOMY_SOURCES, 'decisions.taxonomy_source', errors);
  validateEnum(decisions.preset_source, ALLOWED_PRESET_SOURCES, 'decisions.preset_source', errors);
  validateEnum(decisions.budget_buckets, ALLOWED_BUDGET_DECISIONS, 'decisions.budget_buckets', errors);
  validateEnum(decisions.product_family_relation, ALLOWED_PRODUCT_RELATIONS, 'decisions.product_family_relation', errors);
  validateEnum(decisions.filtered_indexability, ALLOWED_INDEXABILITY, 'decisions.filtered_indexability', errors);

  if (payload.status === 'approved') {
    if (!['bitrix_terms', 'config_terms'].includes(decisions.taxonomy_source)) {
      errors.push('decisions.taxonomy_source must be bitrix_terms or config_terms for approved status.');
    }
    if (!['featured_terms', 'filter_presets'].includes(decisions.preset_source)) {
      errors.push('decisions.preset_source must be featured_terms or filter_presets for approved status.');
    }
    if (!['php_config', 'bitrix_terms'].includes(decisions.budget_buckets)) {
      errors.push('decisions.budget_buckets must be php_config or bitrix_terms for approved status.');
    }
    if (decisions.filtered_indexability !== 'keep_noindex') {
      errors.push('decisions.filtered_indexability must stay keep_noindex; indexable landing pages need a separate SEO project.');
    }
  }
}

function validatePolicies(payload, errors) {
  if (!assertObject(payload.policies, 'policies', errors)) {
    return;
  }

  const expected = {
    counts: 'runtime_derived',
    membership: 'offer_items_runtime',
    filtered_urls: 'keep_noindex_canonical_offer',
    synthetic_examples: 'examples_not_proof',
  };
  for (const [key, value] of Object.entries(expected)) {
    if (payload.policies[key] !== value) {
      errors.push(`policies.${key} must be ${value}.`);
    }
  }
}

function validateTerms(payload, errors) {
  if (!Array.isArray(payload.terms)) {
    errors.push('terms must be an array.');
    return { byDimension: new Map(), activeFeatured: 0 };
  }

  const seenCodes = new Set();
  const aliasMap = new Map();
  const byDimension = new Map();
  const budgetRanges = [];
  let activeFeatured = 0;
  const budgetDecision = payload.decisions?.budget_buckets;
  const productRelation = payload.decisions?.product_family_relation;

  for (const [index, term] of payload.terms.entries()) {
    const path = `terms[${index}]`;
    if (!assertObject(term, path, errors)) {
      continue;
    }

    if (typeof term.dimension !== 'string' || !ALLOWED_DIMENSIONS.has(term.dimension)) {
      errors.push(`${path}.dimension must be one of ${Array.from(ALLOWED_DIMENSIONS).join(', ')}.`);
      continue;
    }
    if (typeof term.code !== 'string' || !/^[a-z0-9_-]{1,80}$/.test(term.code)) {
      errors.push(`${path}.code must match /^[a-z0-9_-]{1,80}$/.`);
    }
    const identity = `${term.dimension}:${term.code}`;
    if (seenCodes.has(identity)) {
      errors.push(`${path} duplicates taxonomy code ${identity}.`);
    }
    seenCodes.add(identity);

    const publicLabel = safeString(term.public_label ?? '', `${path}.public_label`, errors, { maxLength: 80 });
    if (publicLabel === '') {
      errors.push(`${path}.public_label must not be empty.`);
    }
    if (Object.prototype.hasOwnProperty.call(term, 'short_label')) {
      safeString(term.short_label ?? '', `${path}.short_label`, errors, { maxLength: 80 });
    }
    if (!Number.isInteger(term.sort) || term.sort < 1 || term.sort > 100000) {
      errors.push(`${path}.sort must be an integer from 1 to 100000.`);
    }
    if (typeof term.active !== 'boolean') {
      errors.push(`${path}.active must be boolean.`);
    }
    if (typeof term.featured !== 'boolean') {
      errors.push(`${path}.featured must be boolean.`);
    }
    if (term.active === true && term.featured === true) {
      activeFeatured++;
    }
    if (typeof term.product_family !== 'string' || !ALLOWED_PRODUCT_FAMILIES.has(term.product_family)) {
      errors.push(`${path}.product_family must be empty, none, platform, agents, dev or forum.`);
    }
    if (productRelation === 'none' && term.product_family && term.product_family !== 'none') {
      errors.push(`${path}.product_family must be empty when decisions.product_family_relation=none.`);
    }
    if (productRelation === 'defer' && term.product_family && term.product_family !== 'none') {
      errors.push(`${path}.product_family must stay empty while product-family relation is deferred.`);
    }

    if (!Array.isArray(term.aliases)) {
      errors.push(`${path}.aliases must be an array.`);
    } else {
      for (const [aliasIndex, alias] of term.aliases.entries()) {
        const aliasPath = `${path}.aliases[${aliasIndex}]`;
        const normalizedAlias = safeString(alias, aliasPath, errors, { maxLength: 120 }).toLowerCase();
        if (normalizedAlias === '') {
          errors.push(`${aliasPath} must not be empty.`);
          continue;
        }
        const aliasIdentity = `${term.dimension}:${normalizedAlias}`;
        const previous = aliasMap.get(aliasIdentity);
        if (previous && previous !== term.code) {
          errors.push(`${aliasPath} maps duplicate alias "${normalizedAlias}" to both ${previous} and ${term.code}.`);
        }
        aliasMap.set(aliasIdentity, term.code);
      }
    }

    if (!byDimension.has(term.dimension)) {
      byDimension.set(term.dimension, []);
    }
    byDimension.get(term.dimension).push(term);

    const hasBudgetMin = Object.prototype.hasOwnProperty.call(term, 'budget_min');
    const hasBudgetMax = Object.prototype.hasOwnProperty.call(term, 'budget_max');
    if (term.dimension !== 'budget' && (hasBudgetMin || hasBudgetMax)) {
      errors.push(`${path} must not include budget_min/budget_max outside budget dimension.`);
    }
    if (term.dimension === 'budget') {
      if (budgetDecision !== 'bitrix_terms') {
        errors.push(`${path} budget terms require decisions.budget_buckets=bitrix_terms.`);
      }
      if (!Number.isInteger(term.budget_min) || term.budget_min < 0) {
        errors.push(`${path}.budget_min must be a non-negative integer.`);
      }
      if (!(term.budget_max === null || (Number.isInteger(term.budget_max) && term.budget_max > term.budget_min))) {
        errors.push(`${path}.budget_max must be null or an integer greater than budget_min.`);
      }
      if (Number.isInteger(term.budget_min) && (term.budget_max === null || Number.isInteger(term.budget_max))) {
        budgetRanges.push({
          path,
          min: term.budget_min,
          max: term.budget_max === null ? Number.POSITIVE_INFINITY : term.budget_max,
        });
      }
    }
  }

  if (payload.status === 'approved') {
    for (const dimension of REQUIRED_APPROVED_DIMENSIONS) {
      if (!byDimension.has(dimension) || byDimension.get(dimension).filter((term) => term.active === true).length === 0) {
        errors.push(`approved taxonomy requires at least one active ${dimension} term.`);
      }
    }
    if (budgetDecision === 'bitrix_terms' && (!byDimension.has('budget') || byDimension.get('budget').filter((term) => term.active === true).length === 0)) {
      errors.push('approved taxonomy with Bitrix budget buckets requires at least one active budget term.');
    }
  }

  budgetRanges.sort((a, b) => a.min - b.min);
  for (let index = 1; index < budgetRanges.length; index++) {
    const previous = budgetRanges[index - 1];
    const current = budgetRanges[index];
    if (current.min < previous.max) {
      errors.push(`${current.path} overlaps with previous budget range ${previous.path}.`);
    }
  }

  return { byDimension, activeFeatured };
}

function validatePresets(payload, termSummary, errors) {
  if (!Array.isArray(payload.presets)) {
    errors.push('presets must be an array.');
    return;
  }

  const presetSource = payload.decisions?.preset_source;
  if (payload.status === 'approved' && presetSource === 'featured_terms' && termSummary.activeFeatured < 1) {
    errors.push('approved featured_terms preset source requires at least one active featured taxonomy term.');
  }
  if (payload.status === 'approved' && presetSource === 'filter_presets' && payload.presets.filter((preset) => preset.active === true).length < 1) {
    errors.push('approved filter_presets source requires at least one active preset.');
  }

  const termCodes = new Map();
  for (const [dimension, terms] of termSummary.byDimension.entries()) {
    termCodes.set(dimension, new Set(terms.map((term) => term.code)));
  }

  const seenPresets = new Set();
  for (const [index, preset] of payload.presets.entries()) {
    const path = `presets[${index}]`;
    if (!assertObject(preset, path, errors)) {
      continue;
    }
    if (typeof preset.code !== 'string' || !/^[a-z0-9_-]{1,80}$/.test(preset.code)) {
      errors.push(`${path}.code must match /^[a-z0-9_-]{1,80}$/.`);
    }
    if (seenPresets.has(preset.code)) {
      errors.push(`${path} duplicates preset code ${preset.code}.`);
    }
    seenPresets.add(preset.code);
    const title = safeString(preset.title ?? '', `${path}.title`, errors, { maxLength: 80 });
    if (title === '') {
      errors.push(`${path}.title must not be empty.`);
    }
    if (!Number.isInteger(preset.sort) || preset.sort < 1 || preset.sort > 100000) {
      errors.push(`${path}.sort must be an integer from 1 to 100000.`);
    }
    if (typeof preset.active !== 'boolean') {
      errors.push(`${path}.active must be boolean.`);
    }
    if (!assertObject(preset.filters, `${path}.filters`, errors)) {
      continue;
    }
    if (Object.keys(preset.filters).length === 0) {
      errors.push(`${path}.filters must not be empty.`);
    }
    for (const [dimension, code] of Object.entries(preset.filters)) {
      if (!ALLOWED_DIMENSIONS.has(dimension)) {
        errors.push(`${path}.filters.${dimension} is not an allowed dimension.`);
        continue;
      }
      if (typeof code !== 'string' || !/^[a-z0-9_-]{1,80}$/.test(code)) {
        errors.push(`${path}.filters.${dimension} must be a taxonomy code.`);
        continue;
      }
      const codes = termCodes.get(dimension);
      if (codes && codes.size > 0 && !codes.has(code)) {
        errors.push(`${path}.filters.${dimension} references unknown term code ${code}.`);
      }
    }
  }
}

function validatePayload(payload, options = {}) {
  const errors = [];
  if (!assertObject(payload, 'payload', errors)) {
    return { success: false, errors };
  }

  validateSafePayload(payload, 'payload', errors);

  if (payload.schema !== SCHEMA) {
    errors.push(`schema must be ${SCHEMA}.`);
  }
  if (!['draft', 'approved'].includes(payload.status)) {
    errors.push('status must be draft or approved.');
  }
  if (payload.status === 'draft' && !options.allowDraft) {
    errors.push('draft status requires --allow-draft.');
  }
  if (payload.runtime_switch_approved !== false) {
    errors.push('runtime_switch_approved must be false; source switch needs a separate implementation/release gate.');
  }
  if (payload.iblock_apply_approved !== false) {
    errors.push('iblock_apply_approved must be false; schema apply needs a separate migration gate.');
  }

  validateOwners(payload, errors);
  validateGates(payload, errors);
  validateDecisions(payload, errors);
  validatePolicies(payload, errors);
  const termSummary = validateTerms(payload, errors);
  validatePresets(payload, termSummary, errors);

  return { success: errors.length === 0, errors };
}

function summarize(payload) {
  const terms = { sector: 0, scenario: 0, phase: 0, budget: 0, featured: 0 };
  for (const term of Array.isArray(payload.terms) ? payload.terms : []) {
    if (Object.prototype.hasOwnProperty.call(terms, term.dimension)) {
      terms[term.dimension]++;
    }
    if (term.active === true && term.featured === true) {
      terms.featured++;
    }
  }
  const activePresets = (Array.isArray(payload.presets) ? payload.presets : []).filter((preset) => preset.active === true).length;

  return { terms, activePresets };
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
    approved_at: approved ? '2026-06-07' : '',
    evidence_ref: approved ? 'offer-taxonomy-owner-review-safe-ref' : '',
  }]));
}

function gateRows(approved) {
  return Object.fromEntries(REQUIRED_GATES.map((gate) => [gate, approved]));
}

function basePayload(status) {
  const approved = status === 'approved';
  return {
    schema: SCHEMA,
    status,
    date: '2026-06-07',
    source: 'tools/offer-taxonomy-approval-check.mjs self-test',
    runtime_switch_approved: false,
    iblock_apply_approved: false,
    owners: ownerRows(approved),
    gates: gateRows(approved),
    decisions: {
      taxonomy_source: approved ? 'bitrix_terms' : 'defer',
      preset_source: approved ? 'featured_terms' : 'defer',
      budget_buckets: approved ? 'php_config' : 'defer',
      product_family_relation: 'defer',
      filtered_indexability: approved ? 'keep_noindex' : 'defer',
    },
    policies: {
      counts: 'runtime_derived',
      membership: 'offer_items_runtime',
      filtered_urls: 'keep_noindex_canonical_offer',
      synthetic_examples: 'examples_not_proof',
    },
    terms: [],
    presets: [],
  };
}

function sampleTerm(dimension, code, label, sort, featured = false) {
  return {
    dimension,
    code,
    public_label: label,
    short_label: label,
    aliases: [label, code],
    sort,
    active: true,
    featured,
    product_family: '',
  };
}

function runSelfTest() {
  const validDraft = basePayload('draft');
  const validApproved = {
    ...basePayload('approved'),
    terms: [
      sampleTerm('sector', 'meditsina', 'медицина', 100, true),
      sampleTerm('scenario', 'ai-assistent-podderzhki', 'AI-ассистент поддержки', 100, true),
      sampleTerm('phase', 'production-vnedrenie', 'внедрение в рабочую эксплуатацию', 100, false),
    ],
  };
  const invalidStoredCounts = {
    ...validApproved,
    terms: [{ ...validApproved.terms[0], offer_count: 12 }],
  };
  const invalidDuplicateAlias = {
    ...validApproved,
    terms: [
      sampleTerm('sector', 'meditsina', 'медицина', 100, true),
      { ...sampleTerm('sector', 'kliniki', 'клиники', 200, false), aliases: ['медицина'] },
      sampleTerm('scenario', 'ai-assistent-podderzhki', 'AI-ассистент поддержки', 100, true),
      sampleTerm('phase', 'production-vnedrenie', 'внедрение в рабочую эксплуатацию', 100, false),
    ],
  };
  const invalidUnsafe = {
    ...validApproved,
    terms: [{ ...validApproved.terms[0], public_label: '<b>медицина</b>' }],
  };
  const invalidBudgetOverlap = {
    ...validApproved,
    decisions: { ...validApproved.decisions, budget_buckets: 'bitrix_terms' },
    terms: [
      ...validApproved.terms,
      { ...sampleTerm('budget', 'up-to-1m', 'до 1 млн руб.', 100), budget_min: 0, budget_max: 1000000 },
      { ...sampleTerm('budget', '500k-2m', '500 тыс. - 2 млн руб.', 200), budget_min: 500000, budget_max: 2000000 },
    ],
  };
  const invalidEmptyPreset = {
    ...validApproved,
    decisions: { ...validApproved.decisions, preset_source: 'filter_presets' },
    presets: [{ code: 'empty', title: '', sort: 100, active: true, filters: {} }],
  };

  const checks = [
    [validatePayload(validDraft, { allowDraft: true }).success, 'valid draft with allowDraft should pass'],
    [!validatePayload(validDraft, { allowDraft: false }).success, 'draft without allowDraft should fail'],
    [validatePayload(validApproved, { allowDraft: false }).success, 'valid approved should pass'],
    [!validatePayload(invalidStoredCounts, { allowDraft: false }).success, 'stored counts should fail'],
    [!validatePayload(invalidDuplicateAlias, { allowDraft: false }).success, 'duplicate aliases should fail'],
    [!validatePayload(invalidUnsafe, { allowDraft: false }).success, 'unsafe label should fail'],
    [!validatePayload(invalidBudgetOverlap, { allowDraft: false }).success, 'overlapping budget buckets should fail'],
    [!validatePayload(invalidEmptyPreset, { allowDraft: false }).success, 'empty active preset should fail'],
  ];
  const failed = checks.filter(([passed]) => !passed);
  if (failed.length > 0) {
    for (const [, label] of failed) {
      console.error(`Self-test failed: ${label}`);
    }
    process.exit(1);
  }

  console.log('Offer taxonomy approval check self-test passed.');
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
    console.error('Offer taxonomy approval check failed:');
    for (const error of result.errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  const summary = summarize(payload);
  console.log('Offer taxonomy approval check passed.');
  console.log(`Status: ${payload.status}`);
  console.log(`Terms: sector=${summary.terms.sector}, scenario=${summary.terms.scenario}, phase=${summary.terms.phase}, budget=${summary.terms.budget}, featured=${summary.terms.featured}`);
  console.log(`Active presets: ${summary.activePresets}`);
  console.log('Runtime switch: not approved by this file.');
  console.log('Iblock apply: not approved by this file.');
}

try {
  main();
} catch (error) {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
