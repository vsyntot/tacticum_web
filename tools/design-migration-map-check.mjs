#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const componentContractPath = 'docs/design-system-handoff/07-component-state-contract.json';
const migrationMapPath = 'docs/design-system-handoff/08-as-is-to-be-migration-map.json';
const packagePath = 'package.json';

const [componentContractSource, migrationMapSource, packageSource] = await Promise.all([
  readFile(componentContractPath, 'utf8'),
  readFile(migrationMapPath, 'utf8'),
  readFile(packagePath, 'utf8'),
]);

const componentContract = JSON.parse(componentContractSource);
const migrationMap = JSON.parse(migrationMapSource);
const packageJson = JSON.parse(packageSource);
const failures = [];

const components = componentContract.components ?? [];
const mappings = migrationMap.mappings ?? [];
const allowedMigrationTypes = new Set(migrationMap.contract?.allowedMigrationTypes ?? []);
const allowedRiskLevels = new Set(migrationMap.contract?.allowedRiskLevels ?? []);
const highRiskRequiredGates = ['Design', 'Frontend'];

if (!Array.isArray(components) || components.length === 0) {
  failures.push(`${componentContractPath}: components must be a non-empty array.`);
}

if (!Array.isArray(mappings) || mappings.length === 0) {
  failures.push(`${migrationMapPath}: mappings must be a non-empty array.`);
}

if (!migrationMap.meta?.dependsOn?.includes(componentContractPath)) {
  failures.push(`${migrationMapPath}: meta.dependsOn must include ${componentContractPath}.`);
}

for (const type of ['visual-restyle', 'contract-preserving-split', 'contract-migration', 'new-interaction']) {
  if (!allowedMigrationTypes.has(type)) {
    failures.push(`${migrationMapPath}: allowedMigrationTypes must include ${type}.`);
  }
}

for (const level of ['low', 'medium', 'high']) {
  if (!allowedRiskLevels.has(level)) {
    failures.push(`${migrationMapPath}: allowedRiskLevels must include ${level}.`);
  }
}

const componentIds = new Set(components.map((component) => component.id));
const mappedIds = new Set();
for (const mapping of mappings) {
  validateMapping(mapping, componentIds, mappedIds);
}

for (const componentId of componentIds) {
  if (!mappedIds.has(componentId)) {
    failures.push(`${migrationMapPath}: missing mapping for AS IS component ${componentId}.`);
  }
}

const unknownMappings = [...mappedIds].filter((id) => !componentIds.has(id));
for (const id of unknownMappings) {
  failures.push(`${migrationMapPath}: mapping references unknown AS IS component ${id}.`);
}

const duplicateMappedIds = mappings
  .map((mapping) => mapping.asIsComponentId)
  .filter((id, index, ids) => ids.indexOf(id) !== index);
for (const id of new Set(duplicateMappedIds)) {
  failures.push(`${migrationMapPath}: duplicate mapping for ${id}.`);
}

if (!Array.isArray(migrationMap.toBeBacklog)) {
  failures.push(`${migrationMapPath}: toBeBacklog must be an array.`);
} else {
  for (const backlogItem of migrationMap.toBeBacklog) {
    validateBacklogItem(backlogItem);
  }
}

if (migrationMap.contract?.guard?.script !== 'tools/design-migration-map-check.mjs') {
  failures.push(`${migrationMapPath}: contract.guard.script must point to tools/design-migration-map-check.mjs.`);
}

if (migrationMap.contract?.guard?.npmScript !== 'design:migration:check') {
  failures.push(`${migrationMapPath}: contract.guard.npmScript must be design:migration:check.`);
}

if (packageJson.scripts?.['design:migration:check'] !== 'node ./tools/design-migration-map-check.mjs') {
  failures.push(`${packagePath}: scripts.design:migration:check must run node ./tools/design-migration-map-check.mjs.`);
}

if (failures.length > 0) {
  console.error('Design migration map check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('Design migration map check passed.');

function validateMapping(mapping, componentIds, mappedIds) {
  const id = mapping?.asIsComponentId;
  if (!id) {
    failures.push(`${migrationMapPath}: mapping is missing asIsComponentId.`);
    return;
  }

  mappedIds.add(id);

  if (!componentIds.has(id)) {
    return;
  }

  for (const requiredField of ['toBeComponentName', 'toBeFamily', 'migrationType', 'riskLevel']) {
    if (!mapping[requiredField]) {
      failures.push(`${migrationMapPath}: mapping ${id} is missing ${requiredField}.`);
    }
  }

  if (!allowedMigrationTypes.has(mapping.migrationType)) {
    failures.push(`${migrationMapPath}: mapping ${id} has invalid migrationType ${mapping.migrationType}.`);
  }

  if (!allowedRiskLevels.has(mapping.riskLevel)) {
    failures.push(`${migrationMapPath}: mapping ${id} has invalid riskLevel ${mapping.riskLevel}.`);
  }

  for (const arrayField of ['preserveSelectors', 'requiredGates', 'designDeliverables', 'implementationNotes', 'openDecisions']) {
    if (!Array.isArray(mapping[arrayField]) || mapping[arrayField].length === 0) {
      failures.push(`${migrationMapPath}: mapping ${id} must include non-empty ${arrayField}.`);
    }
  }

  if (mapping.riskLevel === 'high') {
    for (const gate of highRiskRequiredGates) {
      if (!mapping.requiredGates?.includes(gate)) {
        failures.push(`${migrationMapPath}: high-risk mapping ${id} must include gate ${gate}.`);
      }
    }

    const hasQaGate = mapping.requiredGates?.some((gate) => /^QA\b/.test(gate));
    if (!hasQaGate) {
      failures.push(`${migrationMapPath}: high-risk mapping ${id} must include a QA gate.`);
    }
  }

  const sourceComponent = components.find((component) => component.id === id);
  const sourceSelectors = new Set(sourceComponent?.preservedSelectors ?? []);
  const missingSourceSelectors = (mapping.preserveSelectors ?? [])
    .filter((selector) => !sourceSelectors.has(selector));
  for (const selector of missingSourceSelectors) {
    failures.push(`${migrationMapPath}: mapping ${id} preserves selector ${selector}, but ${componentContractPath} does not list it.`);
  }
}

function validateBacklogItem(item) {
  const id = item?.id || '<missing-id>';
  for (const requiredField of ['id', 'toBeComponentName', 'source', 'migrationType', 'riskLevel', 'reason']) {
    if (!item?.[requiredField]) {
      failures.push(`${migrationMapPath}: toBeBacklog item ${id} is missing ${requiredField}.`);
    }
  }

  if (!allowedMigrationTypes.has(item?.migrationType)) {
    failures.push(`${migrationMapPath}: toBeBacklog item ${id} has invalid migrationType ${item?.migrationType}.`);
  }

  if (!allowedRiskLevels.has(item?.riskLevel)) {
    failures.push(`${migrationMapPath}: toBeBacklog item ${id} has invalid riskLevel ${item?.riskLevel}.`);
  }

  if (!Array.isArray(item?.requiredGates) || item.requiredGates.length === 0) {
    failures.push(`${migrationMapPath}: toBeBacklog item ${id} must include requiredGates.`);
  }
}
