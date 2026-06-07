#!/usr/bin/env node

import { mkdtemp, rm, writeFile, readFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { spawnSync } from 'node:child_process';

const sourceFile = 'docs/workflow/release-signoff.example.json';
const checker = 'tools/release-signoff-check.mjs';
const source = JSON.parse(await readFile(sourceFile, 'utf8'));
const tempDir = await mkdtemp(join(tmpdir(), 'tacticum-release-signoff-self-test-'));
const validManifestFile = 'release-signoff-manifest.example.json';
const productSeoWithoutSchemaManifestFile = 'product-seo-without-schema-summary.json';
const productSeoWithoutBlocksManifestFile = 'product-seo-without-blocks.json';
const validProductBlocks = [
  'hero',
  'fit-guide',
  'content-section',
  'architecture',
  'use-cases',
  'comparison',
  'procurement',
  'rollout',
  'proof',
  'faq',
  'lead-cta',
];
const validManifest = {
  baseUrl: 'https://tacticum.ru/',
  outputDir: '/tmp/tacticum-release-smoke-self-test',
  generatedAt: '2026-06-01T00:00:00.000Z',
  runActions: true,
  expectSeoHead: true,
  expectPriceTeamPresets: true,
  failOnWarnings: true,
  results: [
    {
      page: '/price/',
      viewport: 'desktop',
      url: 'https://tacticum.ru/price/',
      title: 'Tacticum price',
      status: 200,
      textLength: 2000,
      screenshotBytes: 100000,
      pageErrors: [],
      consoleErrors: [],
      consoleWarnings: [],
      networkErrors: [],
      actionErrors: [],
      errors: [],
      actions: [
        {
          label: 'price team presets/summary',
          status: 'ok',
          detail: 'workers=3; budget=present',
        },
      ],
      seoHead: {
        title: 'Tacticum price',
        titleCount: 1,
        descriptions: ['Tacticum price and team presets.'],
        canonicals: ['https://tacticum.ru/price/'],
        openGraph: {
          'og:site_name': ['Tacticum'],
          'og:type': ['website'],
          'og:url': ['https://tacticum.ru/price/'],
          'og:title': ['Tacticum price'],
          'og:description': ['Tacticum price and team presets.'],
          'og:image': ['https://tacticum.ru/local/templates/tacticum/images/hero_bg.jpg'],
        },
        duplicateOpenGraphProperties: [],
        h1Count: 1,
      },
      seoErrors: [],
    },
    {
      page: '/price/',
      viewport: 'mobile',
      url: 'https://tacticum.ru/price/',
      title: 'Tacticum price',
      status: 200,
      textLength: 2000,
      screenshotBytes: 100000,
      pageErrors: [],
      consoleErrors: [],
      consoleWarnings: [],
      networkErrors: [],
      actionErrors: [],
      errors: [],
      actions: [
        {
          label: 'price team presets/summary',
          status: 'ok',
          detail: 'workers=3; budget=present',
        },
      ],
      seoHead: {
        title: 'Tacticum price',
        titleCount: 1,
        descriptions: ['Tacticum price and team presets.'],
        canonicals: ['https://tacticum.ru/price/'],
        openGraph: {
          'og:site_name': ['Tacticum'],
          'og:type': ['website'],
          'og:url': ['https://tacticum.ru/price/'],
          'og:title': ['Tacticum price'],
          'og:description': ['Tacticum price and team presets.'],
          'og:image': ['https://tacticum.ru/local/templates/tacticum/images/hero_bg.jpg'],
        },
        duplicateOpenGraphProperties: [],
        h1Count: 1,
      },
      seoErrors: [],
    },
  ],
};
const productSeoWithoutBlocksManifest = {
  ...validManifest,
  results: [
    {
      page: '/platform/',
      viewport: 'desktop',
      url: 'https://tacticum.ru/platform/',
      title: 'Tacticum Platform',
      status: 200,
      textLength: 2000,
      screenshotBytes: 100000,
      pageErrors: [],
      consoleErrors: [],
      consoleWarnings: [],
      networkErrors: [],
      actionErrors: [],
      errors: [],
      actions: [],
      seoHead: {
        title: 'Tacticum Platform',
        titleCount: 1,
        descriptions: ['Tacticum Platform product page.'],
        canonicals: ['https://tacticum.ru/platform/'],
        openGraph: {
          'og:site_name': ['Tacticum'],
          'og:type': ['website'],
          'og:url': ['https://tacticum.ru/platform/'],
          'og:title': ['Tacticum Platform'],
          'og:description': ['Tacticum Platform product page.'],
          'og:image': ['https://tacticum.ru/local/templates/tacticum/images/hero_bg.jpg'],
        },
        duplicateOpenGraphProperties: [],
        h1Count: 1,
        productSchemaSummary: {
          softwareApplicationCount: 1,
          faqPageCount: 1,
          schemaTypes: ['SoftwareApplication', 'FAQPage'],
        },
      },
      seoErrors: [],
      productBlocks: {
        isProductPage: true,
        required: validProductBlocks,
        found: validProductBlocks.filter((block) => block !== 'lead-cta'),
        missing: ['lead-cta'],
      },
      productBlockErrors: ['missing product blocks on /platform: lead-cta'],
    },
  ],
};
const productSeoWithoutSchemaManifest = {
  ...validManifest,
  results: [
    {
      page: '/platform/',
      viewport: 'desktop',
      url: 'https://tacticum.ru/platform/',
      title: 'Tacticum Platform',
      status: 200,
      textLength: 2000,
      screenshotBytes: 100000,
      pageErrors: [],
      consoleErrors: [],
      consoleWarnings: [],
      networkErrors: [],
      actionErrors: [],
      errors: [],
      actions: [],
      seoHead: {
        title: 'Tacticum Platform',
        titleCount: 1,
        descriptions: ['Tacticum Platform product page.'],
        canonicals: ['https://tacticum.ru/platform/'],
        openGraph: {
          'og:site_name': ['Tacticum'],
          'og:type': ['website'],
          'og:url': ['https://tacticum.ru/platform/'],
          'og:title': ['Tacticum Platform'],
          'og:description': ['Tacticum Platform product page.'],
          'og:image': ['https://tacticum.ru/local/templates/tacticum/images/hero_bg.jpg'],
        },
        duplicateOpenGraphProperties: [],
        h1Count: 1,
      },
      seoErrors: [],
      productBlocks: {
        isProductPage: true,
        required: validProductBlocks,
        found: validProductBlocks,
        missing: [],
      },
      productBlockErrors: [],
    },
  ],
};

function cloneSource() {
  return JSON.parse(JSON.stringify(source));
}

function validCspEnforceGate() {
  return {
    status: 'passed',
    owner: 'Security + Frontend + QA',
    evidence: {
      environment: 'staging',
      checked_at: '2026-06-04T12:00:00+03:00',
      checked_by: 'Security Owner',
      mode: 'enforce',
      report_only_baseline: 'aggregate report-only baseline has no unexpected violations',
      inline_inventory: 'Bitrix inline inventory reviewed without raw script content',
      vendor_inventory: 'Yandex widgets and Bitrix toolbar origins reviewed',
      staging_enforce_smoke: 'public pages, forms, chat, price, map and toolbar smoke passed',
      rollback: 'config switch back to security.csp_mode=report-only is documented',
      violations_triaged: true,
      rollback_to_report_only_documented: true,
    },
  };
}

function validSensitiveEndpointAccessGate() {
  return {
    status: 'passed',
    owner: 'Security + Backend + QA',
    evidence: {
      environment: 'staging',
      checked_at: '2026-06-04T12:10:00+03:00',
      checked_by: 'Security Owner',
      flow: 'private proof request fixture',
      access_model: 'authenticated-session',
      allowed_result: 'qualified owner session reaches allowed state',
      denied_result: 'anonymous request reaches denied state',
      expired_or_malformed_result: 'expired or malformed credential reaches denied state',
      noindex_or_cache_policy: 'noindex and private/no-cache policy confirmed',
      logging_pii_check: 'logs and release evidence contain safe aggregate IDs only',
    },
  };
}

function validEndpointRiskClassGate() {
  return {
    status: 'passed',
    owner: 'Security + Backend + DevOps',
    evidence: {
      checked_at: '2026-06-04T12:20:00+03:00',
      checked_by: 'Backend Owner',
      endpoint: '/local/rest/private_proof.php',
      risk_class: 'PRIVATE_PROOF_DOC',
      origin_csrf: 'origin and CSRF requirements match Sprint 22 matrix',
      rate_limit: 'rl-sensitive policy selected',
      auth_ip_proxy: 'authenticated access required; proxy ownership documented',
      logging_evidence: 'aggregate status counters only; no contact fields',
    },
  };
}

function validLegacyFinalModeGate() {
  return {
    status: 'passed',
    owner: 'Architect + Backend + DevOps + PM',
    evidence: {
      checked_at: '2026-07-01T12:00:00+03:00',
      checked_by: 'Backend Owner',
      aliases: [
        '/local/rest/tacticum_offer.php',
        '/local/rest/tacticum_sale.php',
      ],
      final_mode: '410',
      inventory_window: 'full external inventory window complete; aggregate only',
      access_log_aggregate: 'zero hits by endpoint, day and status; safe counts only',
      crm_upstream_report: 'zero legacy source leads; safe counts only',
      implementation_result: 'final mode smoke passed for both aliases',
      rollback_or_support_plan: 'restore compatibility endpoint if owner evidence requires support extension',
    },
  };
}

const cases = [
  {
    name: 'placeholder manual evidence',
    expected: /placeholder/,
    mutate(payload) {
      payload.gates['manual-success-flow'].evidence.checked_by = 'replace-with-owner';
    },
  },
  {
    name: 'unsafe raw response key',
    expected: /unsafe key.*raw_response/,
    mutate(payload) {
      payload.gates['bitrix-admin'].evidence.raw_response = '{"session":"secret"}';
    },
  },
  {
    name: 'email-like manual evidence value',
    expected: /email/,
    mutate(payload) {
      payload.gates['manual-success-flow'].evidence.flows[0].lead_id = 'qa@example.com';
    },
  },
  {
    name: 'incomplete staff-sale upstream evidence',
    expected: /monthly_budget_estimate_present/,
    mutate(payload) {
      payload.gates['staff-sale-upstream'] = {
        status: 'passed',
        owner: 'Architect + Backend + QA + DevOps',
        evidence: {
          environment: 'staging',
          checked_at: '2026-05-23T00:00:00+03:00',
          checked_by: 'QA Owner',
          health_config: 'success=true; scopes include ai, rest, security',
          url: 'https://staging.tacticum.ru/price/',
          form_id: 'price-specialist',
          team_preset: 'mvp',
          workers_count: 3,
          monthly_budget_estimate_present: false,
          end_date_present: true,
          upstream_request_id: 'stg-upstream-1001',
          result: 'upstream accepted lead',
        },
      };
    },
  },
  {
    name: 'missing staff-sale end date evidence',
    expected: /end_date_present/,
    mutate(payload) {
      payload.gates['staff-sale-upstream'] = {
        status: 'passed',
        owner: 'Architect + Backend + QA + DevOps',
        evidence: {
          environment: 'staging',
          checked_at: '2026-05-23T00:00:00+03:00',
          checked_by: 'QA Owner',
          health_config: 'success=true; scopes include ai, rest, security',
          url: 'https://staging.tacticum.ru/price/',
          form_id: 'price-specialist',
          team_preset: 'mvp',
          workers_count: 3,
          monthly_budget_estimate_present: true,
          end_date_present: false,
          upstream_request_id: 'stg-upstream-1001',
          result: 'upstream accepted lead',
        },
      };
    },
  },
  {
    name: 'invalid staff-sale team preset evidence',
    expected: /team_preset/,
    mutate(payload) {
      payload.gates['staff-sale-upstream'] = {
        status: 'passed',
        owner: 'Architect + Backend + QA + DevOps',
        evidence: {
          environment: 'staging',
          checked_at: '2026-05-23T00:00:00+03:00',
          checked_by: 'QA Owner',
          health_config: 'success=true; scopes include ai, rest, security',
          url: 'https://staging.tacticum.ru/price/',
          form_id: 'price-specialist',
          team_preset: 'custom',
          workers_count: 3,
          monthly_budget_estimate_present: true,
          end_date_present: true,
          upstream_request_id: 'stg-upstream-1001',
          result: 'upstream accepted lead',
        },
      };
    },
  },
  {
    name: 'missing css js e2e manifest',
    expected: /css-js-e2e-readiness: manifest evidence is missing/,
    mutate(payload) {
      delete payload.gates['css-js-e2e-readiness'].evidence.production_browser_manifest;
    },
  },
  {
    name: 'missing product schema summary',
    expected: /missing product SoftwareApplication schema summary/,
    mutate(payload) {
      payload.gates['seo-rendered-head'].evidence.seo_smoke_manifest = productSeoWithoutSchemaManifestFile;
    },
  },
  {
    name: 'missing product block summary',
    expected: /missing product block lead-cta|has productBlockErrors/,
    mutate(payload) {
      payload.gates['seo-rendered-head'].evidence.seo_smoke_manifest = productSeoWithoutBlocksManifestFile;
    },
  },
  {
    name: 'content public hygiene issues found',
    expected: /content-public-hygiene: issues_found must be 0/,
    mutate(payload) {
      payload.gates['content-public-hygiene'].evidence.issues_found = 2;
    },
  },
  {
    name: 'unknown gate',
    expected: /unknown gate/,
    mutate(payload) {
      payload.gates['unexpected-gate'] = {
        status: 'passed',
        owner: 'QA',
        evidence: 'not allowed',
      };
    },
  },
  {
    name: 'strict working-tree commit',
    expected: /working-tree/,
    mutate(payload) {
      payload.release.commit = 'abc123 + working-tree changes';
    },
  },
  {
    name: 'csp enforce missing rollback evidence',
    expected: /csp-enforce: missing rollback/,
    mutate(payload) {
      payload.gates['csp-enforce'] = validCspEnforceGate();
      delete payload.gates['csp-enforce'].evidence.rollback;
    },
  },
  {
    name: 'sensitive endpoint invalid access model',
    expected: /access_model/,
    mutate(payload) {
      payload.gates['sensitive-endpoint-access'] = validSensitiveEndpointAccessGate();
      payload.gates['sensitive-endpoint-access'].evidence.access_model = 'plain-public-url';
    },
  },
  {
    name: 'endpoint risk class http endpoint',
    expected: /endpoint URL must use HTTPS/,
    mutate(payload) {
      payload.gates['endpoint-risk-class'] = validEndpointRiskClassGate();
      payload.gates['endpoint-risk-class'].evidence.endpoint = 'http://example.test/local/rest/private_proof.php';
    },
  },
  {
    name: 'legacy final mode missing alias',
    expected: /aliases must include \/local\/rest\/tacticum_sale\.php/,
    mutate(payload) {
      payload.gates['legacy-final-mode'] = validLegacyFinalModeGate();
      payload.gates['legacy-final-mode'].evidence.aliases = ['/local/rest/tacticum_offer.php'];
    },
  },
  {
    name: 'future gate raw logs key',
    expected: /unsafe key.*raw_logs/,
    mutate(payload) {
      payload.gates['legacy-final-mode'] = validLegacyFinalModeGate();
      payload.gates['legacy-final-mode'].evidence.raw_logs = 'raw access log lines are not allowed';
    },
  },
];
const draftCases = [
  {
    name: 'pending manual gate without runbook',
    expected: /evidence\.runbook/,
    mutate(payload) {
      payload.gates['manual-success-flow'] = {
        status: 'pending',
        owner: 'QA + Backend/Frontend',
        due: 'before-strict-release-closure',
        reason: 'Waiting for staging owner',
        evidence: {
          evidence_template: 'docs/workflow/release-signoff-manual-evidence.template.json',
        },
      };
    },
  },
  {
    name: 'pending gate without due',
    expected: /pending gate must include due/,
    mutate(payload) {
      payload.gates['manual-success-flow'] = {
        status: 'pending',
        owner: 'QA + Backend/Frontend',
        reason: 'Waiting for staging owner',
        evidence: {
          runbook: 'docs/workflow/manual-release-gates-runbook.md#gate-manual-success-flow',
          evidence_template: 'docs/workflow/release-signoff-manual-evidence.template.json',
        },
      };
    },
  },
];

try {
  await writeFile(join(tempDir, validManifestFile), `${JSON.stringify(validManifest, null, 2)}\n`);
  await writeFile(
    join(tempDir, productSeoWithoutSchemaManifestFile),
    `${JSON.stringify(productSeoWithoutSchemaManifest, null, 2)}\n`,
  );
  await writeFile(
    join(tempDir, productSeoWithoutBlocksManifestFile),
    `${JSON.stringify(productSeoWithoutBlocksManifest, null, 2)}\n`,
  );

  const validFutureGatePayload = cloneSource();
  validFutureGatePayload.gates['csp-enforce'] = validCspEnforceGate();
  validFutureGatePayload.gates['sensitive-endpoint-access'] = validSensitiveEndpointAccessGate();
  validFutureGatePayload.gates['endpoint-risk-class'] = validEndpointRiskClassGate();
  validFutureGatePayload.gates['legacy-final-mode'] = validLegacyFinalModeGate();
  const validFutureGateFile = join(tempDir, 'valid-future-gates.json');
  await writeFile(validFutureGateFile, `${JSON.stringify(validFutureGatePayload, null, 2)}\n`);
  const validFutureGateResult = spawnSync(process.execPath, [checker, validFutureGateFile], {
    cwd: process.cwd(),
    encoding: 'utf8',
  });
  if (validFutureGateResult.status !== 0) {
    console.error('Release sign-off self-test failed: valid future security gates did not pass.');
    console.error(`${validFutureGateResult.stderr}\n${validFutureGateResult.stdout}`.trim());
    process.exit(1);
  }

  for (const testCase of cases) {
    const payload = cloneSource();
    testCase.mutate(payload);

    const file = join(tempDir, `${testCase.name.replace(/[^a-z0-9]+/gi, '-')}.json`);
    await writeFile(file, `${JSON.stringify(payload, null, 2)}\n`);

    const result = spawnSync(process.execPath, [checker, file], {
      cwd: process.cwd(),
      encoding: 'utf8',
    });

    if (result.status === 0) {
      console.error(`Release sign-off self-test failed: "${testCase.name}" unexpectedly passed.`);
      process.exit(1);
    }

    const output = `${result.stderr}\n${result.stdout}`;
    if (!testCase.expected.test(output)) {
      console.error(`Release sign-off self-test failed: "${testCase.name}" failed for an unexpected reason.`);
      console.error(output.trim());
      process.exit(1);
    }
  }

  for (const testCase of draftCases) {
    const payload = cloneSource();
    testCase.mutate(payload);

    const file = join(tempDir, `${testCase.name.replace(/[^a-z0-9]+/gi, '-')}.json`);
    await writeFile(file, `${JSON.stringify(payload, null, 2)}\n`);

    const result = spawnSync(process.execPath, [checker, '--allow-pending', file], {
      cwd: process.cwd(),
      encoding: 'utf8',
    });

    if (result.status === 0) {
      console.error(`Release sign-off self-test failed: "${testCase.name}" unexpectedly passed.`);
      process.exit(1);
    }

    const output = `${result.stderr}\n${result.stdout}`;
    if (!testCase.expected.test(output)) {
      console.error(`Release sign-off self-test failed: "${testCase.name}" failed for an unexpected reason.`);
      console.error(output.trim());
      process.exit(1);
    }
  }
} finally {
  await rm(tempDir, { recursive: true, force: true });
}

console.log(`Release sign-off self-test passed: ${cases.length + draftCases.length} negative cases rejected.`);
