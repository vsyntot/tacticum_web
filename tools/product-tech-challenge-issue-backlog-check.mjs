#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const sourcePath = 'docs/workflow/product-tech-challenge-gap-register-2026-06-04.md';
const trackerPath = 'docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json';
const backlogPath = 'docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.json';
const backlogDocPath = 'docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.md';

const gapIdPattern = /\b(?:CFG|UX|UI|ARCH|CMP|STACK|CONTENT|SEC|REL)-\d{3}\b/g;
const issueIdPattern = /^PTC-WP-\d{2}$/;
const wpIdPattern = /^WP-\d{2}$/;

const [source, trackerRaw, backlogRaw, backlogDoc] = await Promise.all([
  readFile(sourcePath, 'utf8'),
  readFile(trackerPath, 'utf8'),
  readFile(backlogPath, 'utf8'),
  readFile(backlogDocPath, 'utf8'),
]);

const errors = [];
const sourceIds = parseSourceIds(source);
let tracker;
let backlog;

try {
  tracker = JSON.parse(trackerRaw);
} catch (error) {
  console.error(`Owner status tracker is not valid JSON: ${error.message}`);
  process.exit(1);
}

try {
  backlog = JSON.parse(backlogRaw);
} catch (error) {
  console.error(`Issue backlog is not valid JSON: ${error.message}`);
  process.exit(1);
}

const trackerPackages = new Map((tracker.work_packages || []).map((pkg) => [pkg.id, pkg]));
const issues = Array.isArray(backlog.issues) ? backlog.issues : [];
const issueMap = new Map();
const allowedStartPolicies = new Set(backlog.rules?.allowed_start_policies || []);
const allowedIssueStatuses = new Set(backlog.rules?.allowed_issue_statuses || []);

const requiredSourceDocuments = [
  sourcePath,
  'docs/workflow/product-tech-challenge-execution-board-2026-06-04.md',
  trackerPath,
  'docs/workflow/product-tech-challenge-owner-approval-request-2026-06-04.md',
  'docs/workflow/product-tech-challenge-evidence-intake-2026-06-04.md',
  'docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md',
];

const expectedPolicyByStatus = new Map([
  ['pending-target-evidence', 'target-evidence-only'],
  ['pending-owner-review', 'owner-review-required'],
  ['blocked-external', 'blocked-external'],
  ['accepted-monitor', 'monitor-only'],
]);

if (backlog.schema_version !== 1) {
  errors.push('Issue backlog schema_version must be 1.');
}

if (backlog.document_date !== '2026-06-04') {
  errors.push('Issue backlog document_date must be 2026-06-04.');
}

if (backlog.status !== 'issue-backlog-draft-owner-approval-pending') {
  errors.push('Issue backlog status must remain issue-backlog-draft-owner-approval-pending until external tracker import is evidenced.');
}

if (backlog.rules?.no_pii_evidence !== true || backlog.rules?.do_not_commit_raw_evidence !== true) {
  errors.push('Issue backlog rules must require no-PII evidence and forbid raw evidence commits.');
}

const sourceDocumentValues = Object.values(backlog.source_documents || {});
for (const requiredDocument of requiredSourceDocuments) {
  if (!sourceDocumentValues.includes(requiredDocument)) {
    errors.push(`Issue backlog source_documents is missing ${requiredDocument}.`);
  }
}

if (allowedStartPolicies.size === 0) {
  errors.push('Issue backlog rules.allowed_start_policies must be non-empty.');
}

if (allowedIssueStatuses.size === 0) {
  errors.push('Issue backlog rules.allowed_issue_statuses must be non-empty.');
}

for (const issue of issues) {
  if (!issue || typeof issue !== 'object') {
    errors.push('Issue backlog issues must contain objects only.');
    continue;
  }

  if (!issueIdPattern.test(issue.issue_id || '')) {
    errors.push(`Issue has invalid issue_id: ${issue.issue_id || '<missing>'}.`);
    continue;
  }

  if (issueMap.has(issue.issue_id)) {
    errors.push(`Issue backlog has duplicate issue_id: ${issue.issue_id}.`);
  }
  issueMap.set(issue.issue_id, issue);

  if (!wpIdPattern.test(issue.work_package_id || '')) {
    errors.push(`${issue.issue_id} has invalid work_package_id: ${issue.work_package_id || '<missing>'}.`);
    continue;
  }

  const expectedIssueId = `PTC-${issue.work_package_id}`;
  if (issue.issue_id !== expectedIssueId) {
    errors.push(`${issue.issue_id} must match work package as ${expectedIssueId}.`);
  }

  const trackerPackage = trackerPackages.get(issue.work_package_id);
  if (!trackerPackage) {
    errors.push(`${issue.issue_id} references unknown tracker package ${issue.work_package_id}.`);
    continue;
  }

  if (issue.title !== trackerPackage.title) {
    errors.push(`${issue.issue_id} title does not match owner status tracker.`);
  }

  if (issue.issue_status !== trackerPackage.owner_status) {
    errors.push(`${issue.issue_id} issue_status must match tracker owner_status ${trackerPackage.owner_status}.`);
  }

  if (!allowedIssueStatuses.has(issue.issue_status)) {
    errors.push(`${issue.issue_id} has unsupported issue_status: ${issue.issue_status}.`);
  }

  if (!allowedStartPolicies.has(issue.start_policy)) {
    errors.push(`${issue.issue_id} has unsupported start_policy: ${issue.start_policy}.`);
  }

  const expectedPolicy = expectedPolicyByStatus.get(issue.issue_status);
  if (expectedPolicy && issue.start_policy !== expectedPolicy) {
    errors.push(`${issue.issue_id} start_policy must be ${expectedPolicy} for status ${issue.issue_status}.`);
  }

  if (!sameSet(issue.gap_ids || [], trackerPackage.gap_ids || [])) {
    errors.push(`${issue.issue_id} gap_ids must exactly match owner status tracker.`);
  }

  if (!sameSet(issue.owners || [], trackerPackage.owners || [])) {
    errors.push(`${issue.issue_id} owners must exactly match owner status tracker.`);
  }

  if (!sameSet(issue.primary_sprints || [], trackerPackage.primary_sprints || [])) {
    errors.push(`${issue.issue_id} primary_sprints must exactly match owner status tracker.`);
  }

  const requiredStringFields = ['workflow_lane', 'priority', 'objective'];
  for (const field of requiredStringFields) {
    if (typeof issue[field] !== 'string' || issue[field].trim() === '') {
      errors.push(`${issue.issue_id} is missing non-empty ${field}.`);
    }
  }

  const requiredArrayFields = [
    'affected_areas',
    'definition_of_ready',
    'acceptance_criteria',
    'verification_commands',
    'evidence_required',
    'blockers',
    'do_not_start',
    'source_documents',
  ];

  for (const field of requiredArrayFields) {
    if (!Array.isArray(issue[field]) || issue[field].length === 0) {
      errors.push(`${issue.issue_id} must have non-empty ${field}.`);
    }
  }

  if (!backlogDoc.includes(issue.issue_id)) {
    errors.push(`Markdown issue backlog does not mention ${issue.issue_id}.`);
  }
}

for (const wpId of trackerPackages.keys()) {
  const issueId = `PTC-${wpId}`;
  if (!issueMap.has(issueId)) {
    errors.push(`Issue backlog is missing issue ${issueId}.`);
  }
}

const backlogIds = unique(issues.flatMap((issue) => (Array.isArray(issue.gap_ids) ? issue.gap_ids : [])));
const missingIds = sourceIds.filter((id) => !backlogIds.includes(id));
const unknownIds = backlogIds.filter((id) => !sourceIds.includes(id));

for (const id of missingIds) {
  errors.push(`Issue backlog does not cover source gap ID ${id}.`);
}

for (const id of unknownIds) {
  errors.push(`Issue backlog references unknown gap ID ${id}.`);
}

const requiredDocSections = [
  '## Start Policy',
  '## Backlog Index',
  '## Import Rules',
  '## Copy Format',
  '## Sprint-To-Issue Sequence',
  '## Verification',
  '## Closure Rule',
];

for (const section of requiredDocSections) {
  if (!backlogDoc.includes(section)) {
    errors.push(`Markdown issue backlog is missing section: ${section}.`);
  }
}

if (!backlogDoc.includes('npm run product:challenge:issue-backlog:check')) {
  errors.push('Markdown issue backlog must mention npm run product:challenge:issue-backlog:check.');
}

for (const issue of scanForbiddenKeys(backlog)) {
  errors.push(issue);
}

if (errors.length > 0) {
  console.error('Product tech challenge issue backlog check failed:');
  for (const error of errors) {
    console.error(`- ${error}`);
  }
  process.exit(1);
}

console.log(`Product tech challenge issue backlog check passed: ${issues.length} issues, ${sourceIds.length} gap IDs covered.`);

function parseSourceIds(contents) {
  return unique(
    contents
      .split(/\r?\n/)
      .filter((line) => /^\|\s*(?:CFG|UX|UI|ARCH|CMP|STACK|CONTENT|SEC|REL)-\d{3}\s*\|/.test(line))
      .flatMap((line) => line.match(gapIdPattern) || [])
  );
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
      issues.push(`Forbidden evidence key ${[...path, key].join('.')} is present in issue backlog JSON.`);
    }
    issues.push(...scanForbiddenKeys(child, [...path, key]));
  }

  return issues;
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
