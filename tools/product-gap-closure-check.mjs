#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const SOURCE_PATH = 'docs/new-big-change/product-vision-handoff/14-gap-backlog-and-decision-register.md';
const REGISTER_PATH = 'docs/new-big-change/product-vision-handoff/16-gap-closure-action-register.json';
const PLAN_PATH = 'docs/new-big-change/product-vision-handoff/15-gap-closure-master-plan.md';
const WORKFLOW_README_PATH = 'docs/workflow/README.md';
const PACKAGE_PATH = 'package.json';

const [
  sourceMarkdown,
  registerRaw,
  masterPlan,
  workflowReadme,
  packageRaw,
] = await Promise.all([
  readFile(SOURCE_PATH, 'utf8'),
  readFile(REGISTER_PATH, 'utf8'),
  readFile(PLAN_PATH, 'utf8'),
  readFile(WORKFLOW_README_PATH, 'utf8'),
  readFile(PACKAGE_PATH, 'utf8'),
]);

const register = JSON.parse(registerRaw);
const packageJson = JSON.parse(packageRaw);
const errors = [];

const sourceGaps = parseSourceGaps(sourceMarkdown);
const sourceById = new Map(sourceGaps.map((gap) => [gap.id, gap]));
const actionableSourceGaps = sourceGaps.filter((gap) => gap.status !== 'closed');
const actionableSourceIds = new Set(actionableSourceGaps.map((gap) => gap.id));
const actions = Array.isArray(register.actions) ? register.actions : [];
const actionsById = new Map();

if (!Array.isArray(register.actions)) {
  errors.push(`${REGISTER_PATH}: "actions" must be an array`);
}

for (const action of actions) {
  const id = String(action.id || '').trim();
  if (!id) {
    errors.push(`${REGISTER_PATH}: action without id`);
    continue;
  }
  if (actionsById.has(id)) {
    errors.push(`${REGISTER_PATH}: duplicate action id ${id}`);
  }
  actionsById.set(id, action);
}

for (const gap of actionableSourceGaps) {
  const action = actionsById.get(gap.id);
  if (!action) {
    errors.push(`${REGISTER_PATH}: missing action for ${gap.id}`);
    continue;
  }

  if (action.sourceStatus !== gap.status) {
    errors.push(`${gap.id}: sourceStatus must be "${gap.status}", got "${action.sourceStatus || ''}"`);
  }

  if (action.priority !== gap.priority) {
    errors.push(`${gap.id}: priority must be "${gap.priority}", got "${action.priority || ''}"`);
  }

  requireNonEmpty(action.track, `${gap.id}: track is required`);
  requireNonEmpty(action.closureMode, `${gap.id}: closureMode is required`);
  requireNonEmpty(action.owner, `${gap.id}: owner is required`);
  requireNonEmpty(action.targetCheckpoint, `${gap.id}: targetCheckpoint is required`);
  requireNonEmpty(action.nextAction, `${gap.id}: nextAction is required`);

  if (!Array.isArray(action.acceptanceEvidence) || action.acceptanceEvidence.length === 0) {
    errors.push(`${gap.id}: acceptanceEvidence must be a non-empty array`);
  } else {
    for (const [index, item] of action.acceptanceEvidence.entries()) {
      requireNonEmpty(item, `${gap.id}: acceptanceEvidence[${index}] is empty`);
    }
  }

  if (gap.status === 'blocked') {
    requireNonEmpty(action.blocker, `${gap.id}: blocked gaps must define blocker`);
    if (action.closureMode !== 'external-evidence') {
      errors.push(`${gap.id}: blocked gaps must use closureMode "external-evidence"`);
    }
  }

  if (gap.status === 'accepted' && action.closureMode !== 'accepted-monitoring') {
    errors.push(`${gap.id}: accepted gaps must use closureMode "accepted-monitoring"`);
  }

  if (gap.status !== 'accepted' && action.closureMode === 'accepted-monitoring') {
    errors.push(`${gap.id}: only accepted gaps can use closureMode "accepted-monitoring"`);
  }
}

for (const id of actionsById.keys()) {
  if (!sourceById.has(id)) {
    errors.push(`${REGISTER_PATH}: action ${id} does not exist in ${SOURCE_PATH}`);
    continue;
  }

  if (!actionableSourceIds.has(id)) {
    errors.push(`${REGISTER_PATH}: action ${id} points to a closed gap and should be omitted`);
  }
}

for (const gap of sourceGaps) {
  if (gap.status === 'closed' && actionsById.has(gap.id)) {
    errors.push(`${REGISTER_PATH}: closed gap ${gap.id} must not have an action`);
  }
}

validateMetadata();
validateAllowedValues();
await validateReviewArtifacts();
validateScriptAndDocs();

if (errors.length > 0) {
  console.error('Product gap closure check failed:');
  for (const error of errors) {
    console.error(`- ${error}`);
  }
  process.exit(1);
}

const grouped = actions.reduce((acc, action) => {
  const status = String(action.sourceStatus || 'unknown');
  acc[status] = (acc[status] || 0) + 1;
  return acc;
}, {});

console.log('Product gap closure check passed.');
console.log(`Source gaps: ${sourceGaps.length}; actionable gaps: ${actionableSourceGaps.length}; actions: ${actions.length}.`);
console.log(`By status: ${Object.entries(grouped).map(([status, count]) => `${status}=${count}`).join(', ')}.`);

function parseSourceGaps(markdown) {
  const rows = [];

  for (const line of markdown.split(/\r?\n/)) {
    const trimmed = line.trim();
    if (!trimmed.startsWith('|') || /^\|\s*-+/.test(trimmed) || /\|\s*ID\s*\|/i.test(trimmed)) {
      continue;
    }

    const cells = trimmed
      .slice(1, trimmed.endsWith('|') ? -1 : undefined)
      .split('|')
      .map((cell) => cell.trim());

    const [id, status, priority] = cells;
    if (!/^(?:PB|CJM|UI|ARCH|REL)-\d+$/.test(id) && !/^SEO-TOBE-\d+$/.test(id)) {
      continue;
    }

    rows.push({
      id,
      status,
      priority,
      row: trimmed,
    });
  }

  return rows;
}

function validateMetadata() {
  if (register.meta?.source !== SOURCE_PATH) {
    errors.push(`${REGISTER_PATH}: meta.source must be ${SOURCE_PATH}`);
  }

  if (register.meta?.plan !== PLAN_PATH) {
    errors.push(`${REGISTER_PATH}: meta.plan must be ${PLAN_PATH}`);
  }

  if (register.guard?.script !== 'tools/product-gap-closure-check.mjs') {
    errors.push(`${REGISTER_PATH}: guard.script must be tools/product-gap-closure-check.mjs`);
  }

  if (register.guard?.npmScript !== 'product:gaps:check') {
    errors.push(`${REGISTER_PATH}: guard.npmScript must be product:gaps:check`);
  }
}

function validateAllowedValues() {
  const allowedClosureModes = new Set(register.allowedClosureModes || []);
  const allowedTracks = new Set(register.allowedTracks || []);

  if (allowedClosureModes.size === 0) {
    errors.push(`${REGISTER_PATH}: allowedClosureModes must not be empty`);
  }

  if (allowedTracks.size === 0) {
    errors.push(`${REGISTER_PATH}: allowedTracks must not be empty`);
  }

  for (const action of actions) {
    if (!allowedClosureModes.has(action.closureMode)) {
      errors.push(`${action.id}: unknown closureMode "${action.closureMode || ''}"`);
    }

    if (!allowedTracks.has(action.track)) {
      errors.push(`${action.id}: unknown track "${action.track || ''}"`);
    }
  }
}

async function validateReviewArtifacts() {
  const artifacts = register.reviewArtifacts;

  if (!Array.isArray(artifacts) || artifacts.length === 0) {
    errors.push(`${REGISTER_PATH}: reviewArtifacts must be a non-empty array`);
    return;
  }

  const artifactCoverage = new Map();

  for (const [index, artifact] of artifacts.entries()) {
    const path = String(artifact?.path || '').trim();
    const covers = Array.isArray(artifact?.covers) ? artifact.covers : [];

    if (!path) {
      errors.push(`${REGISTER_PATH}: reviewArtifacts[${index}].path is required`);
      continue;
    }

    if (covers.length === 0) {
      errors.push(`${REGISTER_PATH}: reviewArtifacts[${index}].covers must be a non-empty array`);
      continue;
    }

    let artifactContent = '';
    try {
      artifactContent = await readFile(path, 'utf8');
    } catch (error) {
      errors.push(`${REGISTER_PATH}: review artifact ${path} cannot be read (${error.message})`);
      continue;
    }

    for (const coveredId of covers) {
      const id = String(coveredId || '').trim();

      if (!id) {
        errors.push(`${REGISTER_PATH}: review artifact ${path} has empty covered id`);
        continue;
      }

      if (!actionsById.has(id)) {
        errors.push(`${REGISTER_PATH}: review artifact ${path} covers unknown or closed action ${id}`);
        continue;
      }

      if (artifactCoverage.has(id)) {
        errors.push(`${REGISTER_PATH}: action ${id} is covered by multiple review artifacts (${artifactCoverage.get(id)} and ${path})`);
      }

      artifactCoverage.set(id, path);

      if (!artifactContent.includes(id)) {
        errors.push(`${path}: missing covered gap id ${id}`);
      }
    }
  }
}

function validateScriptAndDocs() {
  const expectedScript = 'node ./tools/product-gap-closure-check.mjs';

  if (packageJson.scripts?.['product:gaps:check'] !== expectedScript) {
    errors.push(`${PACKAGE_PATH}: product:gaps:check must be "${expectedScript}"`);
  }

  const requiredPlanRefs = [
    '16-gap-closure-action-register.json',
    '17-local-gap-decision-briefs.md',
    '18-phase-1-product-decision-review-pack.md',
    '19-phase-3-architecture-integration-decision-pack.md',
    '20-phase-4-seo-content-decision-pack.md',
    '21-phase-5-release-evidence-closure-pack.md',
    '22-phase-2-design-system-approval-pack.md',
    '23-accepted-risk-monitoring-pack.md',
    'npm run product:gaps:check',
  ];

  for (const ref of requiredPlanRefs) {
    if (!masterPlan.includes(ref)) {
      errors.push(`${PLAN_PATH}: missing reference "${ref}"`);
    }
  }

  const requiredWorkflowRefs = [
    'product:gaps:check',
    '16-gap-closure-action-register.json',
  ];

  for (const ref of requiredWorkflowRefs) {
    if (!workflowReadme.includes(ref)) {
      errors.push(`${WORKFLOW_README_PATH}: missing reference "${ref}"`);
    }
  }
}

function requireNonEmpty(value, message) {
  if (String(value || '').trim() === '') {
    errors.push(message);
  }
}
