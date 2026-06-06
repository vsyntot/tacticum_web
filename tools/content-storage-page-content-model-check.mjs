#!/usr/bin/env node

import { existsSync, readFileSync, writeFileSync } from 'node:fs';

const DEFAULT_MODEL_PATH = 'docs/workflow/content-storage-page-content-model-2026-06-05.draft.json';
const DEFAULT_APPROVED_OUTPUT_PATH = '/tmp/content-storage-page-content-model-2026-06-06.approved.json';
const DEFAULT_MODEL = {
  schema: 'tacticum.content_storage.page_content_model.v1',
  status: 'draft',
  date: '2026-06-05',
  release_evidence: false,
  decision_owner_required: true,
  summary: 'Draft target model for generic page sections. It does not create iblocks, migrate content or approve public copy changes.',
  storage: {
    recommended: {
      section_iblock_key: 'page_sections',
      block_iblock_key: 'page_blocks',
      status: 'draft',
      reason: 'Generic page sections need a structured editor workflow without polluting narrow catalog/domain iblocks.',
    },
    rejected_targets: [
      {
        iblock_key: 'services',
        reason: 'Service catalog cards only; not methodology/process/page copy.',
      },
      {
        iblock_key: 'cases',
        reason: 'Real approved customer cases only.',
      },
      {
        iblock_key: 'feedback',
        reason: 'Real testimonials only.',
      },
      {
        iblock_key: 'clients',
        reason: 'Approved client/trust entities only.',
      },
      {
        iblock_key: 'team',
        reason: 'People profiles only.',
      },
      {
        iblock_key: 'vacancies',
        reason: 'Open positions only.',
      },
      {
        iblock_key: 'rates',
        reason: 'Staff/rate rows only.',
      },
      {
        iblock_key: 'policies',
        reason: 'Legal/static documents only until a separate static-content ADR changes this.',
      },
    ],
  },
  section_schema: {
    iblock_key: 'page_sections',
    required_fields: [
      'PAGE_KEY',
      'SECTION_KEY',
      'TEMPLATE_KEY',
      'MIGRATION_STATUS',
    ],
    recommended_fields: [
      'EYEBROW',
      'TITLE',
      'TEXT',
      'THEME',
      'TONE',
      'CTA_TEXT',
      'CTA_HREF',
      'FALLBACK_PARTIAL',
      'OWNER_SCOPE',
    ],
    rules: [
      'PAGE_KEY and SECTION_KEY form the stable lookup key.',
      'TEMPLATE_KEY selects an allowlisted renderer.',
      'FALLBACK_PARTIAL points to the current PHP partial during the first migration cycle.',
      'MIGRATION_STATUS is one of draft, seeded, shadow, live, fallback_retired.',
    ],
  },
  block_schema: {
    iblock_key: 'page_blocks',
    required_fields: [
      'SECTION',
      'BLOCK_KEY',
      'ITEM_TYPE',
    ],
    recommended_fields: [
      'TITLE',
      'TEXT',
      'ICON',
      'HREF',
      'META',
      'VALUE',
      'LABEL',
      'TONE',
      'PROOF_STATUS',
    ],
    rules: [
      'SECTION links to page_sections.',
      'Child blocks store cards/items/steps without JSON as the primary editor workflow.',
      'Renderer escapes or sanitizes fields using the existing content helper path.',
    ],
  },
  forbidden_primary_storage: [
    'raw_html_blob',
    'json_blob',
    'single_DETAIL_TEXT_wall',
    'service_catalog_pollution',
    'case_or_testimonial_without_evidence',
  ],
  runtime_policy: {
    first_release_source: 'php_fallback_first',
    target_source: 'bitrix_first_with_php_fallback',
    cache: 'managed_tags_for_page_sections_and_page_blocks',
    rollback: 'switch section/page back to PHP fallback and clear cache',
    public_switch_requires: [
      'owner approval',
      'seed/apply evidence',
      'strict content audit',
      'seo check',
      'visual smoke',
      'browser action smoke for touched pages',
    ],
  },
  migration_waves: [
    {
      wave: 'wave_1',
      status: 'draft',
      pages: [
        {
          page: '/services/',
          sections: ['delivery-layer', 'process', 'tech'],
          stay_in_domain_iblocks: ['services-list', 'cases-list', 'faq'],
        },
        {
          page: '/price/',
          sections: ['features', 'workstreams'],
          stay_in_domain_iblocks: ['price-list', 'faq'],
        },
        {
          page: '/contacts/',
          sections: ['routing', 'cards'],
          stay_in_domain_iblocks: [],
        },
        {
          page: '/offer/',
          sections: ['product-bridge', 'bottom-cta'],
          stay_in_domain_iblocks: ['offer-list', 'offer-detail'],
        },
      ],
    },
    {
      wave: 'wave_2',
      status: 'draft',
      pages: [
        {
          page: '/',
          sections: ['ecosystem', 'fit-matrix', 'commercial'],
          stay_in_domain_iblocks: ['content-lists', 'faq'],
        },
        {
          page: '/about/',
          sections: ['company-trust', 'values-team', 'career-final'],
          stay_in_domain_iblocks: ['team', 'vacancies'],
        },
        {
          page: '/calculator/',
          sections: ['calculator-outcome-cards', 'product-aware-estimate-cards'],
          stay_in_domain_iblocks: [],
        },
        {
          page: '/aiagents/',
          sections: ['agents-bridge', 'how-it-works', 'services'],
          stay_in_domain_iblocks: ['demoagents-list', 'faq'],
        },
      ],
    },
  ],
  owner_gates: {
    architect: false,
    content: false,
    frontend: false,
    qa: false,
    seo: false,
  },
};
const REQUIRED_SECTION_FIELDS = new Set(['PAGE_KEY', 'SECTION_KEY', 'TEMPLATE_KEY', 'MIGRATION_STATUS']);
const REQUIRED_BLOCK_FIELDS = new Set(['SECTION', 'BLOCK_KEY', 'ITEM_TYPE']);
const REQUIRED_REJECTED_TARGETS = new Set(['services', 'cases', 'feedback', 'clients', 'team', 'vacancies', 'rates', 'policies']);
const REQUIRED_WAVE_PAGES = new Set(['/services/', '/price/', '/contacts/', '/offer/', '/', '/about/', '/calculator/', '/aiagents/']);
const FORBIDDEN_TARGETS = new Set(['services', 'cases', 'feedback', 'clients', 'team', 'vacancies', 'rates', 'policies', 'aiagents', 'offer']);
const REQUIRED_FORBIDDEN_STORAGE = new Set(['raw_html_blob', 'json_blob', 'single_DETAIL_TEXT_wall']);

function usage() {
  return `Usage:
  node tools/content-storage-page-content-model-check.mjs [model.json] [--use-embedded-model] [--use-embedded-approved-model] [--write-approved-model[=path]]

Validates the draft structured page-content target model. This is a planning
guard only: it does not create Bitrix iblocks or approve a migration.
When the default draft file is absent on production, the checker uses the
embedded safe draft baseline so /docs is not a runtime deploy dependency.
Use --write-approved-model to write a schema-only approved JSON for production
apply when /docs is not deployed. This still approves only empty schema creation,
not page copy seed, public runtime switch or fallback retirement.
`;
}

function assertObject(value, path, errors) {
  if (!value || typeof value !== 'object' || Array.isArray(value)) {
    errors.push(`${path} must be an object.`);
    return false;
  }

  return true;
}

function hasAll(values, required, path, errors) {
  const set = new Set(Array.isArray(values) ? values : []);
  for (const value of required) {
    if (!set.has(value)) {
      errors.push(`${path} must include ${value}.`);
    }
  }
}

function validateModel(model) {
  const errors = [];
  if (!assertObject(model, 'model', errors)) {
    return errors;
  }

  if (model.schema !== 'tacticum.content_storage.page_content_model.v1') {
    errors.push('schema must be tacticum.content_storage.page_content_model.v1.');
  }
  if (!['draft', 'approved'].includes(model.status)) {
    errors.push('status must be draft or approved.');
  }
  if (model.release_evidence !== false) {
    errors.push('release_evidence must be false; page-content model draft is not release evidence.');
  }
  if (model.decision_owner_required !== true) {
    errors.push('decision_owner_required must be true.');
  }

  if (assertObject(model.storage, 'storage', errors)) {
    if (assertObject(model.storage.recommended, 'storage.recommended', errors)) {
      if (model.storage.recommended.section_iblock_key !== 'page_sections') {
        errors.push('storage.recommended.section_iblock_key must be page_sections.');
      }
      if (model.storage.recommended.block_iblock_key !== 'page_blocks') {
        errors.push('storage.recommended.block_iblock_key must be page_blocks.');
      }
      if (FORBIDDEN_TARGETS.has(model.storage.recommended.section_iblock_key)) {
        errors.push('section_iblock_key must not use a narrow/domain iblock.');
      }
      if (FORBIDDEN_TARGETS.has(model.storage.recommended.block_iblock_key)) {
        errors.push('block_iblock_key must not use a narrow/domain iblock.');
      }
    }

    const rejectedTargets = Array.isArray(model.storage.rejected_targets)
      ? model.storage.rejected_targets.map((target) => target?.iblock_key)
      : [];
    hasAll(rejectedTargets, REQUIRED_REJECTED_TARGETS, 'storage.rejected_targets', errors);
  }

  if (assertObject(model.section_schema, 'section_schema', errors)) {
    if (model.section_schema.iblock_key !== 'page_sections') {
      errors.push('section_schema.iblock_key must be page_sections.');
    }
    hasAll(model.section_schema.required_fields, REQUIRED_SECTION_FIELDS, 'section_schema.required_fields', errors);
    const sectionRules = Array.isArray(model.section_schema.rules) ? model.section_schema.rules.join('\n') : '';
    for (const needle of ['allowlisted renderer', 'FALLBACK_PARTIAL', 'MIGRATION_STATUS']) {
      if (!sectionRules.includes(needle)) {
        errors.push(`section_schema.rules must mention ${needle}.`);
      }
    }
  }

  if (assertObject(model.block_schema, 'block_schema', errors)) {
    if (model.block_schema.iblock_key !== 'page_blocks') {
      errors.push('block_schema.iblock_key must be page_blocks.');
    }
    hasAll(model.block_schema.required_fields, REQUIRED_BLOCK_FIELDS, 'block_schema.required_fields', errors);
    const blockRules = Array.isArray(model.block_schema.rules) ? model.block_schema.rules.join('\n') : '';
    if (!/without JSON as the primary editor workflow/.test(blockRules)) {
      errors.push('block_schema.rules must forbid JSON as primary editor workflow.');
    }
  }

  hasAll(model.forbidden_primary_storage, REQUIRED_FORBIDDEN_STORAGE, 'forbidden_primary_storage', errors);

  if (assertObject(model.runtime_policy, 'runtime_policy', errors)) {
    if (model.runtime_policy.target_source !== 'bitrix_first_with_php_fallback') {
      errors.push('runtime_policy.target_source must be bitrix_first_with_php_fallback.');
    }
    const publicSwitchRequires = Array.isArray(model.runtime_policy.public_switch_requires)
      ? model.runtime_policy.public_switch_requires.join('\n')
      : '';
    for (const gate of ['owner approval', 'strict content audit', 'visual smoke']) {
      if (!publicSwitchRequires.includes(gate)) {
        errors.push(`runtime_policy.public_switch_requires must include ${gate}.`);
      }
    }
  }

  if (!Array.isArray(model.migration_waves) || model.migration_waves.length < 2) {
    errors.push('migration_waves must include at least two waves.');
  } else {
    const pages = new Set();
    for (const [waveIndex, wave] of model.migration_waves.entries()) {
      const wavePath = `migration_waves[${waveIndex}]`;
      if (!assertObject(wave, wavePath, errors)) {
        continue;
      }
      if (!Array.isArray(wave.pages) || wave.pages.length === 0) {
        errors.push(`${wavePath}.pages must be a non-empty array.`);
        continue;
      }
      for (const [pageIndex, page] of wave.pages.entries()) {
        const pagePath = `${wavePath}.pages[${pageIndex}]`;
        if (!assertObject(page, pagePath, errors)) {
          continue;
        }
        if (typeof page.page !== 'string' || page.page === '') {
          errors.push(`${pagePath}.page is required.`);
        } else {
          pages.add(page.page);
        }
        if (!Array.isArray(page.sections) || page.sections.length === 0) {
          errors.push(`${pagePath}.sections must be a non-empty array.`);
        }
        if (!Array.isArray(page.stay_in_domain_iblocks)) {
          errors.push(`${pagePath}.stay_in_domain_iblocks must be an array.`);
        }
      }
    }
    for (const page of REQUIRED_WAVE_PAGES) {
      if (!pages.has(page)) {
        errors.push(`migration_waves must include ${page}.`);
      }
    }
  }

  if (assertObject(model.owner_gates, 'owner_gates', errors)) {
    for (const owner of ['architect', 'content', 'frontend', 'qa', 'seo']) {
      if (typeof model.owner_gates[owner] !== 'boolean') {
        errors.push(`owner_gates.${owner} must be boolean.`);
      }
    }
    if (model.status === 'approved') {
      for (const owner of ['architect', 'content', 'frontend', 'qa', 'seo']) {
        if (model.owner_gates[owner] !== true) {
          errors.push(`owner_gates.${owner} must be true for approved model.`);
        }
      }
    }
  }

  return errors;
}

function cloneJson(value) {
  return JSON.parse(JSON.stringify(value));
}

function approvedSchemaOnlyModel(baseModel = DEFAULT_MODEL) {
  const model = cloneJson(baseModel);
  model.status = 'approved';
  model.date = '2026-06-06';
  model.release_evidence = false;
  model.decision_owner_required = true;
  model.summary = 'Approved schema-only target model for generic page sections. It permits creating empty page_sections/page_blocks iblocks, but does not approve page copy seed, public runtime switch or fallback retirement.';
  if (!model.storage || typeof model.storage !== 'object' || Array.isArray(model.storage)) {
    model.storage = {};
  }
  if (!model.storage.recommended || typeof model.storage.recommended !== 'object' || Array.isArray(model.storage.recommended)) {
    model.storage.recommended = {};
  }
  model.storage.recommended.section_iblock_key = 'page_sections';
  model.storage.recommended.block_iblock_key = 'page_blocks';
  model.storage.recommended.status = 'approved';
  model.owner_gates = {
    architect: true,
    content: true,
    frontend: true,
    qa: true,
    seo: true,
  };
  model.schema_apply_policy = {
    allowed: true,
    scope: 'schema_only',
    creates_iblocks: ['tacticum_page_sections', 'tacticum_page_blocks'],
    creates_properties_count: 25,
    seeds_copy: false,
    changes_public_runtime: false,
    config_registry_update_required_after_apply: true,
    required_post_apply_checks: [
      'php tools/content-storage-page-content-migration.php --model=docs/workflow/content-storage-page-content-model-2026-06-06.approved.json --apply',
      'update local/php_interface/include/tacticum_config.php with page_sections/page_blocks IDs from migration output',
      'php tools/content-storage-audit.php --scope=page-content --strict --json',
      'npm run content:storage:governance:check',
    ],
  };
  model.rules = [
    'This approval allows schema creation only.',
    'Do not seed page copy from this file.',
    'Do not switch public pages to Bitrix page-content source from this file.',
    'Do not retire PHP fallback partials from this file.',
    'Use separate page-level seed, smoke and owner approval before any runtime switch.',
  ];

  return model;
}

function isDefaultModelPath(path) {
  return path === DEFAULT_MODEL_PATH || path.endsWith(`/${DEFAULT_MODEL_PATH}`);
}

function parseArgs(argv) {
  const options = {
    path: DEFAULT_MODEL_PATH,
    useEmbeddedModel: false,
    useEmbeddedApprovedModel: false,
    writeApprovedModelPath: '',
  };
  const args = argv.slice(2);
  for (let index = 0; index < args.length; index++) {
    const arg = args[index];
    if (arg === '--help' || arg === '-h') {
      console.log(usage());
      process.exit(0);
    }
    if (arg === '--use-embedded-model') {
      options.useEmbeddedModel = true;
      continue;
    }
    if (arg === '--use-embedded-approved-model') {
      options.useEmbeddedApprovedModel = true;
      continue;
    }
    if (arg === '--write-approved-model') {
      const next = args[index + 1] || '';
      if (next && !next.startsWith('--')) {
        options.writeApprovedModelPath = next;
        index++;
      } else {
        options.writeApprovedModelPath = DEFAULT_APPROVED_OUTPUT_PATH;
      }
      continue;
    }
    if (arg.startsWith('--write-approved-model=')) {
      options.writeApprovedModelPath = arg.slice('--write-approved-model='.length) || DEFAULT_APPROVED_OUTPUT_PATH;
      continue;
    }
    if (options.path === DEFAULT_MODEL_PATH) {
      options.path = arg;
      continue;
    }
    throw new Error(`Unknown argument: ${arg}`);
  }

  return options;
}

function readModel(options) {
  if (options.useEmbeddedApprovedModel) {
    return {
      model: approvedSchemaOnlyModel(DEFAULT_MODEL),
      source: 'embedded schema-only approved model',
    };
  }

  if (options.useEmbeddedModel) {
    return {
      model: cloneJson(DEFAULT_MODEL),
      source: 'embedded default model',
    };
  }

  if (!existsSync(options.path)) {
    if (isDefaultModelPath(options.path)) {
      return {
        model: cloneJson(DEFAULT_MODEL),
        source: `embedded default model; ${options.path} is not present`,
      };
    }

    throw new Error(`File not found: ${options.path}`);
  }

  return {
    model: JSON.parse(readFileSync(options.path, 'utf8')),
    source: options.path,
  };
}

function main() {
  const options = parseArgs(process.argv);
  let { model, source } = readModel(options);
  if (options.writeApprovedModelPath) {
    model = approvedSchemaOnlyModel(model);
    model.schema_apply_policy.required_post_apply_checks[0] = `php tools/content-storage-page-content-migration.php --model=${options.writeApprovedModelPath} --apply`;
    source = `${source}; schema-only approved model written to ${options.writeApprovedModelPath}`;
    const approvedErrors = validateModel(model);
    if (approvedErrors.length > 0) {
      console.error('Content storage page-content approved model check failed:');
      for (const error of approvedErrors) {
        console.error(`- ${error}`);
      }
      process.exit(1);
    }
    writeFileSync(options.writeApprovedModelPath, `${JSON.stringify(model, null, 2)}\n`);
    console.log(`Schema-only approved page-content model written: ${options.writeApprovedModelPath}`);
  }

  const errors = validateModel(model);
  if (errors.length > 0) {
    console.error('Content storage page-content model check failed:');
    for (const error of errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  const waveCount = Array.isArray(model.migration_waves) ? model.migration_waves.length : 0;
  const pageCount = Array.isArray(model.migration_waves)
    ? model.migration_waves.reduce((count, wave) => count + (Array.isArray(wave.pages) ? wave.pages.length : 0), 0)
    : 0;
  console.log('Content storage page-content model check passed.');
  console.log(`Model source: ${source}.`);
  console.log(`Status: ${model.status}, waves=${waveCount}, pages=${pageCount}`);
}

try {
  main();
} catch (error) {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
