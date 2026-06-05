#!/usr/bin/env node

import { readFile, stat } from 'node:fs/promises';

const fixturePath = 'tools/fixtures/component-state-smoke.json';
const wrapperPolicyPath = 'local/components/tacticum/component_wrapper_policy.json';
const packagePath = 'package.json';
const expectedScript = 'node ./tools/component-state-smoke-check.mjs';
const expectedWrapperComponents = ['content.detail', 'content.list', 'faq.section'];
const failures = [];
const fileCache = new Map();

const [fixtureSource, wrapperPolicySource, packageSource] = await Promise.all([
  readRequiredFile(fixturePath),
  readRequiredFile(wrapperPolicyPath),
  readRequiredFile(packagePath),
]);

let fixture = null;
let wrapperPolicy = null;
let packageJson = null;

try {
  fixture = JSON.parse(fixtureSource);
} catch (error) {
  failures.push(`${fixturePath}: invalid JSON: ${error.message}`);
}

try {
  wrapperPolicy = JSON.parse(wrapperPolicySource);
} catch (error) {
  failures.push(`${wrapperPolicyPath}: invalid JSON: ${error.message}`);
}

try {
  packageJson = JSON.parse(packageSource);
} catch (error) {
  failures.push(`${packagePath}: invalid JSON: ${error.message}`);
}

if (packageJson?.scripts?.['component:states:check'] !== expectedScript) {
  failures.push(`${packagePath}: scripts.component:states:check must run ${expectedScript}.`);
}

if (fixture) {
  await validateFixture(fixture);
}

if (wrapperPolicy) {
  await validateWrapperPolicy(wrapperPolicy);
}

if (failures.length > 0) {
  console.error('Component state smoke check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

const groupCount = fixture.groups.length;
const caseCount = fixture.groups.reduce((count, group) => count + group.cases.length, 0);
console.log(`Component state smoke check passed: ${groupCount} groups, ${caseCount} cases.`);

async function validateFixture(document) {
  if (document.schemaVersion !== 1) {
    failures.push(`${fixturePath}: schemaVersion must be 1.`);
  }

  if (!Array.isArray(document.groups) || document.groups.length === 0) {
    failures.push(`${fixturePath}: groups must be a non-empty array.`);
    return;
  }

  const groupIds = new Set();
  for (const group of document.groups) {
    const groupId = typeof group?.id === 'string' && group.id.trim() !== '' ? group.id : '<missing-group-id>';
    if (groupIds.has(groupId)) {
      failures.push(`${fixturePath}: duplicate group id ${groupId}.`);
    }
    groupIds.add(groupId);

    if (typeof group?.title !== 'string' || group.title.trim() === '') {
      failures.push(`${fixturePath}: ${groupId} must include a title.`);
    }

    if (!Array.isArray(group?.cases) || group.cases.length === 0) {
      failures.push(`${fixturePath}: ${groupId} must include non-empty cases.`);
      continue;
    }

    const caseIds = new Set();
    for (const smokeCase of group.cases) {
      await validateCase(groupId, smokeCase, caseIds);
    }
  }
}

async function validateWrapperPolicy(document) {
  if (document.schemaVersion !== 1) {
    failures.push(`${wrapperPolicyPath}: schemaVersion must be 1.`);
  }

  if (!document.wrappers || typeof document.wrappers !== 'object' || Array.isArray(document.wrappers)) {
    failures.push(`${wrapperPolicyPath}: wrappers must be an object.`);
    return;
  }

  const expected = new Set(expectedWrapperComponents);
  for (const wrapperName of Object.keys(document.wrappers).sort()) {
    if (!expected.has(wrapperName)) {
      failures.push(`${wrapperPolicyPath}: stale wrapper contract for ${wrapperName}.`);
    }
  }

  for (const wrapperName of expectedWrapperComponents) {
    const contract = document.wrappers[wrapperName];
    if (!contract) {
      failures.push(`${wrapperPolicyPath}: missing wrapper contract for ${wrapperName}.`);
      continue;
    }

    await validateWrapperContract(wrapperName, contract);
  }
}

async function validateWrapperContract(wrapperName, contract) {
  const label = `${wrapperPolicyPath}: ${wrapperName}`;
  const componentFile = `local/components/tacticum/${wrapperName}/component.php`;
  const templateFile = `local/components/tacticum/${wrapperName}/templates/.default/template.php`;

  if (!contract || typeof contract !== 'object' || Array.isArray(contract)) {
    failures.push(`${label} contract must be an object.`);
    return;
  }

  for (const field of ['owner', 'childComponent', 'childTemplateResultKey', 'childParamsResultKey', 'templateOwnership']) {
    if (typeof contract[field] !== 'string' || contract[field].trim() === '') {
      failures.push(`${label} must include non-empty ${field}.`);
    }
  }

  if (!String(contract.childComponent || '').startsWith('bitrix:')) {
    failures.push(`${label} childComponent must be a delegated bitrix:* component.`);
  }

  const componentExists = await fileExists(componentFile);
  const templateExists = await fileExists(templateFile);
  if (!componentExists) {
    failures.push(`${label} references missing component file ${componentFile}.`);
  }
  if (!templateExists) {
    failures.push(`${label} references missing template file ${templateFile}.`);
  }
  if (!componentExists || !templateExists) {
    return;
  }

  const componentSource = await readCachedFile(componentFile);
  const templateSource = await readCachedFile(templateFile);
  const combinedSource = `${componentSource}\n${templateSource}`;

  if (!templateSource.includes(contract.childComponent)) {
    failures.push(`${label} template must mount ${contract.childComponent}.`);
  }

  for (const resultKey of [contract.childTemplateResultKey, contract.childParamsResultKey]) {
    if (typeof resultKey !== 'string' || resultKey.trim() === '') {
      continue;
    }
    if (!combinedSource.includes(resultKey)) {
      failures.push(`${label} result key ${resultKey} is not present in wrapper source.`);
    }
  }

  validateStringArray(label, contract.acceptedParams, 'acceptedParams');
  validateStringArray(label, contract.requiredChildParams, 'requiredChildParams');
  validateStringArray(label, contract.cacheParams, 'cacheParams');

  for (const param of contract.acceptedParams || []) {
    if (!componentSource.includes(param)) {
      failures.push(`${label} accepted param ${param} is not handled by component.php.`);
    }
  }

  for (const param of contract.requiredChildParams || []) {
    if (!componentSource.includes(param)) {
      failures.push(`${label} required child param ${param} is not prepared by component.php.`);
    }
  }

  const cacheParams = Array.isArray(contract.cacheParams) ? contract.cacheParams : [];
  for (const requiredCacheParam of ['CACHE_TYPE', 'CACHE_TIME']) {
    if (!cacheParams.includes(requiredCacheParam) || !componentSource.includes(requiredCacheParam)) {
      failures.push(`${label} must explicitly delegate ${requiredCacheParam}.`);
    }
  }

  if (contract.emptyState !== undefined) {
    validateWrapperEmptyState(label, contract.emptyState, combinedSource);
  }

  await validateWrapperEvidence(label, contract.guardEvidence);
}

function validateWrapperEmptyState(label, emptyState, source) {
  if (!emptyState || typeof emptyState !== 'object' || Array.isArray(emptyState)) {
    failures.push(`${label} emptyState must be an object when present.`);
    return;
  }

  for (const field of ['statusResultKey', 'missingStatus', 'marker']) {
    if (typeof emptyState[field] !== 'string' || emptyState[field].trim() === '') {
      failures.push(`${label} emptyState must include non-empty ${field}.`);
    } else if (!source.includes(emptyState[field])) {
      failures.push(`${label} emptyState ${field} marker ${emptyState[field]} is not present in wrapper source.`);
    }
  }
}

async function validateWrapperEvidence(label, evidence) {
  if (!evidence || typeof evidence !== 'object' || Array.isArray(evidence)) {
    failures.push(`${label} guardEvidence must be an object.`);
    return;
  }

  validateStringArray(label, evidence.files, 'guardEvidence.files');
  validateStringArray(label, evidence.requiredLiterals, 'guardEvidence.requiredLiterals');

  const sources = [];
  for (const file of evidence.files || []) {
    if (!(await fileExists(file))) {
      failures.push(`${label} guardEvidence references missing file ${file}.`);
      continue;
    }
    sources.push(await readCachedFile(file));
  }

  const combinedSource = sources.join('\n');
  for (const literal of evidence.requiredLiterals || []) {
    if (!combinedSource.includes(literal)) {
      failures.push(`${label} guardEvidence missing required literal: ${literal}`);
    }
  }
}

function validateStringArray(label, value, field) {
  if (!Array.isArray(value) || value.length === 0) {
    failures.push(`${label} must include non-empty ${field}.`);
    return;
  }

  const seen = new Set();
  for (const item of value) {
    if (typeof item !== 'string' || item.trim() === '') {
      failures.push(`${label} ${field} entries must be non-empty strings.`);
      continue;
    }
    if (seen.has(item)) {
      failures.push(`${label} ${field} contains duplicate ${item}.`);
    }
    seen.add(item);
  }
}

async function validateCase(groupId, smokeCase, caseIds) {
  const caseId = typeof smokeCase?.id === 'string' && smokeCase.id.trim() !== ''
    ? smokeCase.id
    : '<missing-case-id>';
  const label = `${groupId}.${caseId}`;

  if (caseIds.has(caseId)) {
    failures.push(`${fixturePath}: duplicate case id ${label}.`);
  }
  caseIds.add(caseId);

  if (!Array.isArray(smokeCase?.files) || smokeCase.files.length === 0) {
    failures.push(`${fixturePath}: ${label} must include non-empty files.`);
    return;
  }

  if (!Array.isArray(smokeCase.requiredLiterals) || smokeCase.requiredLiterals.length === 0) {
    failures.push(`${fixturePath}: ${label} must include non-empty requiredLiterals.`);
    return;
  }

  const sources = [];
  for (const file of smokeCase.files) {
    if (typeof file !== 'string' || file.trim() === '') {
      failures.push(`${fixturePath}: ${label} file entries must be non-empty strings.`);
      continue;
    }

    if (!(await fileExists(file))) {
      failures.push(`${fixturePath}: ${label} references missing file ${file}.`);
      continue;
    }

    sources.push(await readCachedFile(file));
  }

  const combinedSource = sources.join('\n');
  for (const literal of smokeCase.requiredLiterals) {
    if (typeof literal !== 'string' || literal.trim() === '') {
      failures.push(`${fixturePath}: ${label} requiredLiterals entries must be non-empty strings.`);
      continue;
    }

    if (!combinedSource.includes(literal)) {
      failures.push(`${fixturePath}: ${label} missing required literal: ${literal}`);
    }
  }
}

async function readRequiredFile(file) {
  try {
    return await readFile(file, 'utf8');
  } catch (error) {
    failures.push(`${file}: cannot read required file: ${error.message}`);
    return '';
  }
}

async function readCachedFile(file) {
  if (fileCache.has(file)) {
    return fileCache.get(file);
  }

  const source = await readFile(file, 'utf8');
  fileCache.set(file, source);
  return source;
}

async function fileExists(file) {
  try {
    await stat(file);
    return true;
  } catch {
    return false;
  }
}
