#!/usr/bin/env node

import { mkdtemp, rm, writeFile, readFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { spawnSync } from 'node:child_process';

const sourceFile = 'docs/workflow/release-signoff.example.json';
const checker = 'tools/release-signoff-check.mjs';
const source = JSON.parse(await readFile(sourceFile, 'utf8'));
const tempDir = await mkdtemp(join(tmpdir(), 'tacticum-release-signoff-self-test-'));

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
          workers_count: 3,
          monthly_budget_estimate_present: false,
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
];
const draftCases = [
  {
    name: 'pending manual gate without runbook',
    expected: /evidence\.runbook/,
    mutate(payload) {
      payload.gates['manual-success-flow'] = {
        status: 'pending',
        owner: 'QA + Backend/Frontend',
        reason: 'Waiting for staging owner',
        evidence: {
          evidence_template: 'docs/workflow/release-signoff-manual-evidence.template.json',
        },
      };
    },
  },
];

try {
  for (const testCase of cases) {
    const payload = JSON.parse(JSON.stringify(source));
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
    const payload = JSON.parse(JSON.stringify(source));
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
