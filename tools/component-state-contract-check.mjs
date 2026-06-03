#!/usr/bin/env node

import { readFile, stat } from 'node:fs/promises';

const contractPath = 'docs/design-system-handoff/07-component-state-contract.json';
const packagePath = 'package.json';

const [contractSource, packageSource] = await Promise.all([
  readFile(contractPath, 'utf8'),
  readFile(packagePath, 'utf8'),
]);

const contract = JSON.parse(contractSource);
const packageJson = JSON.parse(packageSource);
const failures = [];
const fileCache = new Map();

const requiredComponentIds = [
  'global-navigation',
  'contact-modal',
  'lead-cta-form',
  'chat-surface',
  'faq-accordion',
  'price-team-builder',
  'product-page-blocks',
];

const components = contract.components;
if (!Array.isArray(components)) {
  failures.push(`${contractPath}: components must be an array.`);
} else {
  const ids = components.map((component) => component.id);
  const duplicateIds = ids.filter((id, index) => ids.indexOf(id) !== index);
  for (const id of new Set(duplicateIds)) {
    failures.push(`${contractPath}: duplicate component id ${id}.`);
  }

  for (const id of requiredComponentIds) {
    if (!ids.includes(id)) {
      failures.push(`${contractPath}: missing required component ${id}.`);
    }
  }

  for (const component of components) {
    await validateComponent(component);
  }
}

for (const requiredSource of [
  'docs/design-system-handoff/02-component-inventory.md',
  'docs/design-system-handoff/04-interaction-contracts.md',
  'docs/new-big-change/product-vision-handoff/12-ux-ui-component-target.md',
]) {
  if (!contract.meta?.sources?.includes(requiredSource)) {
    failures.push(`${contractPath}: meta.sources must include ${requiredSource}.`);
  }
}

if (contract.contract?.guard?.script !== 'tools/component-state-contract-check.mjs') {
  failures.push(`${contractPath}: contract.guard.script must point to tools/component-state-contract-check.mjs.`);
}

if (contract.contract?.guard?.npmScript !== 'design:components:check') {
  failures.push(`${contractPath}: contract.guard.npmScript must be design:components:check.`);
}

if (packageJson.scripts?.['design:components:check'] !== 'node ./tools/component-state-contract-check.mjs') {
  failures.push(`${packagePath}: scripts.design:components:check must run node ./tools/component-state-contract-check.mjs.`);
}

validateStateCoverage(contract.stateCoverageRequirements);

if (failures.length > 0) {
  console.error('Component/state contract check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('Component/state contract check passed.');

async function validateComponent(component) {
  if (!component || typeof component !== 'object') {
    failures.push(`${contractPath}: component entries must be objects.`);
    return;
  }

  const label = component.id || '<missing-id>';

  if (!component.id) {
    failures.push(`${contractPath}: component is missing id.`);
  }

  if (!component.toBeComponent) {
    failures.push(`${contractPath}: ${label} is missing toBeComponent.`);
  }

  if (!Array.isArray(component.sourceFiles) || component.sourceFiles.length === 0) {
    failures.push(`${contractPath}: ${label} must list sourceFiles.`);
  } else {
    for (const file of component.sourceFiles) {
      await assertFileExists(file, `${label}.sourceFiles`);
    }
  }

  if (!Array.isArray(component.preservedSelectors) || component.preservedSelectors.length === 0) {
    failures.push(`${contractPath}: ${label} must list preservedSelectors.`);
  }

  if (!Array.isArray(component.requiredSourcePatterns) || component.requiredSourcePatterns.length === 0) {
    failures.push(`${contractPath}: ${label} must list requiredSourcePatterns.`);
  } else {
    for (const requirement of component.requiredSourcePatterns) {
      await validateRequirement(label, requirement);
    }
  }

  if (!Array.isArray(component.statesToDesign) || component.statesToDesign.length < 4) {
    failures.push(`${contractPath}: ${label} must list at least 4 statesToDesign.`);
  }

  if (!Array.isArray(component.openToBeDecisions) || component.openToBeDecisions.length === 0) {
    failures.push(`${contractPath}: ${label} must list openToBeDecisions.`);
  }
}

async function validateRequirement(componentId, requirement) {
  if (!requirement?.file || !requirement?.pattern) {
    failures.push(`${contractPath}: ${componentId} has invalid requiredSourcePatterns entry.`);
    return;
  }

  const source = await readSource(requirement.file);
  if (source === null) {
    failures.push(`${contractPath}: ${componentId} references missing source ${requirement.file}.`);
    return;
  }

  if (!source.includes(requirement.pattern)) {
    failures.push(`${requirement.file}: missing required pattern for ${componentId}: ${requirement.pattern}`);
  }
}

async function assertFileExists(file, context) {
  try {
    await stat(file);
  } catch (error) {
    if (error?.code === 'ENOENT') {
      failures.push(`${contractPath}: ${context} references missing file ${file}.`);
      return;
    }

    throw error;
  }
}

async function readSource(file) {
  if (fileCache.has(file)) {
    return fileCache.get(file);
  }

  try {
    const source = await readFile(file, 'utf8');
    fileCache.set(file, source);
    return source;
  } catch (error) {
    if (error?.code === 'ENOENT') {
      fileCache.set(file, null);
      return null;
    }

    throw error;
  }
}

function validateStateCoverage(stateCoverage) {
  const requiredGroups = {
    forms: ['invalid field', 'submit loading', 'backend error'],
    chat: ['typing indicator', 'lead handoff CTA'],
    configurator: ['empty state', 'summary filled'],
    productBlocks: ['proof status', 'diagram fallback'],
  };

  for (const [group, requiredStates] of Object.entries(requiredGroups)) {
    const actualStates = stateCoverage?.[group];
    if (!Array.isArray(actualStates)) {
      failures.push(`${contractPath}: stateCoverageRequirements.${group} must be an array.`);
      continue;
    }

    for (const state of requiredStates) {
      if (!actualStates.includes(state)) {
        failures.push(`${contractPath}: stateCoverageRequirements.${group} must include "${state}".`);
      }
    }
  }
}
