#!/usr/bin/env node

import { readFile, stat } from 'node:fs/promises';

const readmePath = 'docs/design-system-handoff/README.md';
const packagePath = 'package.json';

const requiredHandoffFiles = [
  'docs/design-system-handoff/01-as-is-brief.md',
  'docs/design-system-handoff/02-component-inventory.md',
  'docs/design-system-handoff/03-page-inventory.md',
  'docs/design-system-handoff/04-interaction-contracts.md',
  'docs/design-system-handoff/05-design-tokens-as-is.json',
  'docs/design-system-handoff/06-known-debt-and-to-be-questions.md',
  'docs/design-system-handoff/07-component-state-contract.json',
  'docs/design-system-handoff/08-as-is-to-be-migration-map.json',
  'docs/design-system-handoff/09-to-be-design-work-order.md',
];

const requiredWorkflowFiles = [
  'docs/workflow/design-token-contract.md',
  'docs/workflow/component-state-contract.md',
  'docs/workflow/design-migration-map.md',
];

const requiredScripts = {
  'design:tokens:check': 'node ./tools/design-token-contract-check.mjs',
  'design:components:check': 'node ./tools/component-state-contract-check.mjs',
  'design:migration:check': 'node ./tools/design-migration-map-check.mjs',
  'design:handoff:check': 'npm run design:tokens:check && npm run design:components:check && npm run design:migration:check && node ./tools/design-handoff-check.mjs',
};

const requiredReadmeMentions = [
  '05-design-tokens-as-is.json',
  '07-component-state-contract.json',
  '08-as-is-to-be-migration-map.json',
  '09-to-be-design-work-order.md',
  'npm run design:tokens:check',
  'npm run design:components:check',
  'npm run design:migration:check',
  'npm run design:handoff:check',
];

const requiredWorkOrderMentions = [
  '05-design-tokens-as-is.json',
  '07-component-state-contract.json',
  '08-as-is-to-be-migration-map.json',
  'npm run design:handoff:check',
  'visual-restyle',
  'contract-preserving-split',
  'contract-migration',
  'new-interaction',
];

const failures = [];

const [readme, packageSource, workOrder] = await Promise.all([
  readFile(readmePath, 'utf8'),
  readFile(packagePath, 'utf8'),
  readFile('docs/design-system-handoff/09-to-be-design-work-order.md', 'utf8'),
]);
const packageJson = JSON.parse(packageSource);

for (const file of [...requiredHandoffFiles, ...requiredWorkflowFiles]) {
  await assertFileExists(file);
}

for (const file of requiredHandoffFiles) {
  const basename = file.split('/').pop();
  if (!readme.includes(basename)) {
    failures.push(`${readmePath}: missing reference to ${basename}.`);
  }
}

for (const mention of requiredReadmeMentions) {
  if (!readme.includes(mention)) {
    failures.push(`${readmePath}: missing required mention ${mention}.`);
  }
}

for (const mention of requiredWorkOrderMentions) {
  if (!workOrder.includes(mention)) {
    failures.push('docs/design-system-handoff/09-to-be-design-work-order.md: missing required mention ' + mention + '.');
  }
}

for (const [script, expectedCommand] of Object.entries(requiredScripts)) {
  if (packageJson.scripts?.[script] !== expectedCommand) {
    failures.push(`${packagePath}: scripts.${script} must be "${expectedCommand}".`);
  }
}

const tokenContract = JSON.parse(await readFile('docs/design-system-handoff/05-design-tokens-as-is.json', 'utf8'));
const componentContract = JSON.parse(await readFile('docs/design-system-handoff/07-component-state-contract.json', 'utf8'));
const migrationMap = JSON.parse(await readFile('docs/design-system-handoff/08-as-is-to-be-migration-map.json', 'utf8'));

if (tokenContract.contract?.guard?.npmScript !== 'design:tokens:check') {
  failures.push('05-design-tokens-as-is.json: guard.npmScript must be design:tokens:check.');
}

if (componentContract.contract?.guard?.npmScript !== 'design:components:check') {
  failures.push('07-component-state-contract.json: guard.npmScript must be design:components:check.');
}

if (migrationMap.contract?.guard?.npmScript !== 'design:migration:check') {
  failures.push('08-as-is-to-be-migration-map.json: guard.npmScript must be design:migration:check.');
}

if (!Array.isArray(componentContract.components) || componentContract.components.length < 7) {
  failures.push('07-component-state-contract.json: expected at least 7 behavior-bearing components.');
}

if (!Array.isArray(migrationMap.mappings) || migrationMap.mappings.length !== componentContract.components.length) {
  failures.push('08-as-is-to-be-migration-map.json: mappings count must match component contract components count.');
}

if (failures.length > 0) {
  console.error('Design handoff check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('Design handoff check passed.');

async function assertFileExists(file) {
  try {
    await stat(file);
  } catch (error) {
    if (error?.code === 'ENOENT') {
      failures.push(`Missing required handoff file: ${file}.`);
      return;
    }

    throw error;
  }
}
