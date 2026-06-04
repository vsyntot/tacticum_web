#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const boardPath = 'docs/workflow/product-tech-challenge-execution-board-2026-06-04.md';
const approvalPath = 'docs/workflow/product-tech-challenge-owner-approval-request-2026-06-04.md';
const evidencePath = 'docs/workflow/product-tech-challenge-evidence-intake-2026-06-04.md';

const wpPattern = /\bWP-\d{2}\b/g;

const [board, approval, evidence] = await Promise.all([
  readFile(boardPath, 'utf8'),
  readFile(approvalPath, 'utf8'),
  readFile(evidencePath, 'utf8'),
]);

const workPackages = unique(board.match(wpPattern) || []);
const approvalWps = unique(approval.match(wpPattern) || []);
const evidenceWps = unique(evidence.match(wpPattern) || []);

const requiredApprovalSections = [
  '## Approval Scope',
  '## Prepared Materials',
  '## Required Owner Responses',
  '## Approval Output Format',
  '## Cross-Owner Decisions',
  '## Closure Rule',
];

const requiredEvidenceSections = [
  '## Evidence Rules',
  '## Status Values',
  '## Evidence Intake Matrix',
  '## Claim / Proof Evidence Table',
  '## SEO / Route Evidence Table',
  '## CRM / Analytics Evidence Table',
  '## Design / Frontend Evidence Table',
  '## Security / Release Evidence Table',
  '## Target Evidence Commands',
  '## Update Path',
];

const requiredOwners = [
  'PM',
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
];

const requiredStatusValues = [
  'approved',
  'approved-v1-safe',
  'rewrite-required',
  'evidence-blocked',
  'deferred',
  'rejected',
  'accepted-monitor',
];

const errors = [];

for (const section of requiredApprovalSections) {
  if (!approval.includes(section)) {
    errors.push(`Approval request is missing section: ${section}`);
  }
}

for (const section of requiredEvidenceSections) {
  if (!evidence.includes(section)) {
    errors.push(`Evidence intake is missing section: ${section}`);
  }
}

for (const wp of workPackages) {
  if (!approvalWps.includes(wp)) {
    errors.push(`Approval request does not cover ${wp}`);
  }
  if (!evidenceWps.includes(wp)) {
    errors.push(`Evidence intake does not cover ${wp}`);
  }
}

for (const owner of requiredOwners) {
  if (!new RegExp(`\\b${escapeRegExp(owner)}\\b`).test(approval)) {
    errors.push(`Approval request does not mention owner: ${owner}`);
  }
}

for (const status of requiredStatusValues) {
  if (!approval.includes(status) && !evidence.includes(status)) {
    errors.push(`Approval/evidence package does not define status: ${status}`);
  }
}

const forbiddenEvidenceTerms = [
  'raw payload',
  'cookie',
  'session',
  'CSRF token',
  'IP address',
  'phone numbers',
  'emails',
];

for (const term of forbiddenEvidenceTerms) {
  if (!evidence.toLowerCase().includes(term.toLowerCase())) {
    errors.push(`Evidence intake does not explicitly forbid or constrain: ${term}`);
  }
}

if (errors.length > 0) {
  console.error('Product tech challenge approval/evidence check failed:');
  for (const error of errors) {
    console.error(`- ${error}`);
  }
  process.exit(1);
}

console.log(`Product tech challenge approval/evidence check passed: ${workPackages.length} work packages covered.`);

function unique(values) {
  return [...new Set(values)].sort();
}

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
