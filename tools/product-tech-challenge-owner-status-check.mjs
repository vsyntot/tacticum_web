#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const sourcePath = 'docs/workflow/product-tech-challenge-gap-register-2026-06-04.md';
const boardPath = 'docs/workflow/product-tech-challenge-execution-board-2026-06-04.md';
const trackerPath = 'docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json';

const idPattern = /\b(?:CFG|UX|UI|ARCH|CMP|STACK|CONTENT|SEC|REL)-\d{3}\b/g;
const wpPattern = /^WP-\d{2}$/;

const [source, board, trackerRaw] = await Promise.all([
  readFile(sourcePath, 'utf8'),
  readFile(boardPath, 'utf8'),
  readFile(trackerPath, 'utf8'),
]);

const errors = [];
let tracker;

try {
  tracker = JSON.parse(trackerRaw);
} catch (error) {
  console.error(`Owner status tracker is not valid JSON: ${error.message}`);
  process.exit(1);
}

const sourceIds = parseSourceIds(source);
const boardPackages = parseBoardPackages(board);
const packages = Array.isArray(tracker.work_packages) ? tracker.work_packages : [];
const trackerPackages = new Map();
const allowedOwnerStatuses = new Set(tracker.rules?.allowed_owner_statuses || []);
const allowedBoardStatuses = new Set(tracker.rules?.allowed_board_statuses || []);

const requiredSourceDocuments = [
  sourcePath,
  boardPath,
  'docs/workflow/product-tech-challenge-owner-approval-request-2026-06-04.md',
  'docs/workflow/product-tech-challenge-evidence-intake-2026-06-04.md',
  'docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md',
];

const allowedOwners = new Set([
  'PM',
  'Product',
  'Sales',
  'Legal',
  'SEO',
  'Designer',
  'Architect',
  'Frontend',
  'Backend',
  'Security',
  'QA',
  'DevOps',
  'Content',
  'Analytics',
  'UX',
]);

if (tracker.schema_version !== 1) {
  errors.push('Tracker schema_version must be 1.');
}

if (tracker.document_date !== '2026-06-04') {
  errors.push('Tracker document_date must be 2026-06-04.');
}

if (tracker.status !== 'owner-review-open') {
  errors.push('Tracker status must remain owner-review-open until every WP has owner evidence.');
}

if (tracker.rules?.no_pii_evidence !== true || tracker.rules?.do_not_commit_raw_evidence !== true) {
  errors.push('Tracker rules must require no-PII evidence and forbid raw evidence commits.');
}

const sourceDocumentValues = Object.values(tracker.source_documents || {});
for (const requiredDocument of requiredSourceDocuments) {
  if (!sourceDocumentValues.includes(requiredDocument)) {
    errors.push(`Tracker source_documents is missing ${requiredDocument}.`);
  }
}

if (allowedOwnerStatuses.size === 0) {
  errors.push('Tracker rules.allowed_owner_statuses must be non-empty.');
}

if (allowedBoardStatuses.size === 0) {
  errors.push('Tracker rules.allowed_board_statuses must be non-empty.');
}

for (const pkg of packages) {
  if (!pkg || typeof pkg !== 'object') {
    errors.push('Tracker work_packages must contain objects only.');
    continue;
  }

  if (!wpPattern.test(pkg.id || '')) {
    errors.push(`Tracker package has invalid id: ${pkg.id || '<missing>'}.`);
    continue;
  }

  if (trackerPackages.has(pkg.id)) {
    errors.push(`Tracker has duplicate package id: ${pkg.id}.`);
  }
  trackerPackages.set(pkg.id, pkg);

  const boardPackage = boardPackages.get(pkg.id);
  if (!boardPackage) {
    errors.push(`Tracker references unknown board package: ${pkg.id}.`);
    continue;
  }

  if (pkg.title !== boardPackage.title) {
    errors.push(`${pkg.id} title does not match execution board.`);
  }

  if (!allowedBoardStatuses.has(pkg.board_status)) {
    errors.push(`${pkg.id} has unsupported board_status: ${pkg.board_status}.`);
  }

  if (pkg.board_status !== boardPackage.status) {
    errors.push(`${pkg.id} board_status must match execution board status ${boardPackage.status}.`);
  }

  if (!allowedOwnerStatuses.has(pkg.owner_status)) {
    errors.push(`${pkg.id} has unsupported owner_status: ${pkg.owner_status}.`);
  }

  const requiredStringFields = ['required_owner_response', 'next_action'];
  for (const field of requiredStringFields) {
    if (typeof pkg[field] !== 'string' || pkg[field].trim() === '') {
      errors.push(`${pkg.id} is missing non-empty ${field}.`);
    }
  }

  const requiredArrayFields = [
    'primary_sprints',
    'gap_ids',
    'owners',
    'blockers',
    'evidence_required',
    'source_documents',
    'do_not_start',
  ];

  for (const field of requiredArrayFields) {
    if (!Array.isArray(pkg[field]) || pkg[field].length === 0) {
      errors.push(`${pkg.id} must have non-empty ${field}.`);
    }
  }

  if (!sameSet(pkg.gap_ids || [], boardPackage.gapIds)) {
    errors.push(`${pkg.id} gap_ids must exactly match the execution board gap IDs.`);
  }

  for (const owner of pkg.owners || []) {
    if (!allowedOwners.has(owner)) {
      errors.push(`${pkg.id} references unknown owner: ${owner}.`);
    }
  }

  if (['blocked-external', 'evidence-blocked'].includes(pkg.owner_status) && (pkg.blockers || []).length === 0) {
    errors.push(`${pkg.id} is blocked but has no blockers.`);
  }

  if (['approved', 'approved-v1-safe'].includes(pkg.owner_status) && !pkg.approval_record_id) {
    errors.push(`${pkg.id} is marked approved but has no approval_record_id.`);
  }
}

for (const wp of boardPackages.keys()) {
  if (!trackerPackages.has(wp)) {
    errors.push(`Tracker is missing board package ${wp}.`);
  }
}

const trackerIds = unique(packages.flatMap((pkg) => (Array.isArray(pkg.gap_ids) ? pkg.gap_ids : [])));
const missingIds = sourceIds.filter((id) => !trackerIds.includes(id));
const unknownIds = trackerIds.filter((id) => !sourceIds.includes(id));

for (const id of missingIds) {
  errors.push(`Tracker does not cover source gap ID ${id}.`);
}

for (const id of unknownIds) {
  errors.push(`Tracker references unknown gap ID ${id}.`);
}

for (const issue of scanForbiddenKeys(tracker)) {
  errors.push(issue);
}

if (errors.length > 0) {
  console.error('Product tech challenge owner status tracker check failed:');
  for (const error of errors) {
    console.error(`- ${error}`);
  }
  process.exit(1);
}

console.log(`Product tech challenge owner status tracker check passed: ${packages.length} work packages, ${sourceIds.length} gap IDs covered.`);

function parseSourceIds(contents) {
  return unique(
    contents
      .split(/\r?\n/)
      .filter((line) => /^\|\s*(?:CFG|UX|UI|ARCH|CMP|STACK|CONTENT|SEC|REL)-\d{3}\s*\|/.test(line))
      .flatMap((line) => line.match(idPattern) || [])
  );
}

function parseBoardPackages(contents) {
  const result = new Map();

  for (const line of contents.split(/\r?\n/)) {
    const cells = line
      .trim()
      .split('|')
      .slice(1, -1)
      .map((cell) => cell.trim());

    if (cells.length < 6 || !wpPattern.test(cells[0])) {
      continue;
    }

    result.set(cells[0], {
      status: stripMarkdown(cells[1]),
      primarySprint: stripMarkdown(cells[2]),
      gapIds: unique(cells[3].match(idPattern) || []),
      title: stripMarkdown(cells[4]),
    });
  }

  return result;
}

function scanForbiddenKeys(value, path = []) {
  const forbiddenKeyNames = new Set([
    'raw_payload',
    'cookie',
    'cookies',
    'session',
    'sessions',
    'csrf_token',
    'token',
    'secret',
    'phone',
    'email',
    'ip_address',
    'user_agent',
  ]);
  const issues = [];

  if (!value || typeof value !== 'object') {
    return issues;
  }

  if (Array.isArray(value)) {
    value.forEach((item, index) => {
      issues.push(...scanForbiddenKeys(item, [...path, String(index)]));
    });
    return issues;
  }

  for (const [key, child] of Object.entries(value)) {
    const normalizedKey = key.toLowerCase().replace(/[-\s]+/g, '_');
    if (forbiddenKeyNames.has(normalizedKey)) {
      issues.push(`Forbidden evidence key ${[...path, key].join('.')} is present in tracker JSON.`);
    }
    issues.push(...scanForbiddenKeys(child, [...path, key]));
  }

  return issues;
}

function stripMarkdown(value) {
  return value.replace(/`/g, '').trim();
}

function sameSet(a, b) {
  const left = unique(a);
  const right = unique(b);

  if (left.length !== right.length) {
    return false;
  }

  return left.every((value, index) => value === right[index]);
}

function unique(values) {
  return [...new Set(values)].sort((a, b) => {
    const [aPrefix, aNum] = splitId(a);
    const [bPrefix, bNum] = splitId(b);
    if (aPrefix === bPrefix) {
      return aNum - bNum;
    }
    return aPrefix.localeCompare(bPrefix);
  });
}

function splitId(id) {
  const [prefix, number] = id.split('-');
  return [prefix, Number(number)];
}
