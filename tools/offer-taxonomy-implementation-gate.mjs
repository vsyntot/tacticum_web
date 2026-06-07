#!/usr/bin/env node

import { existsSync, mkdtempSync, readdirSync, readFileSync, rmSync, statSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';

const ADR_PATH = 'docs/adr/ADR-012-offer-taxonomy-presets-bitrix-model.md';
const DEFAULT_APPROVAL_PATH = 'docs/workflow/offer-taxonomy-presets-owner-approval-2026-06-07.draft.json';
const APPROVAL_DIR = 'docs/workflow';
const APPROVAL_FILE_RE = /^offer-taxonomy-presets-owner-approval-.*\.json$/;

const SCAN_FILES = [
  'package.json',
  'local/php_interface/include/tacticum_config.example.php',
  'local/php_interface/include/tacticum_config.php',
];

const SCAN_DIRS = [
  'local/lib',
  'local/php_interface/include',
  'local/components/tacticum/offer',
  'local/components/tacticum/offer.catalog',
  'tools',
];

const IGNORED_FILES = new Set([
  'tools/offer-taxonomy-approval-check.mjs',
  'tools/offer-taxonomy-implementation-gate.mjs',
]);

const MARKERS = [
  { id: 'iblock-key-terms', pattern: /\boffer_taxonomy_terms\b/ },
  { id: 'iblock-key-presets', pattern: /\boffer_filter_presets\b/ },
  { id: 'offer-taxonomy-config-section', pattern: /['"]offer_taxonomy['"]\s*=>/ },
  { id: 'offer-taxonomy-source-config', pattern: /\boffer\.taxonomy_source\b/ },
  { id: 'offer-taxonomy-source-mode', pattern: /\boffer_taxonomy_source\b/ },
  { id: 'offer-taxonomy-migration-script', pattern: /\boffer[-_]taxonomy[-_](?:migration|migrate|cache|runtime|source|check|finalize)\b/i },
  { id: 'offer-taxonomy-npm-runtime-script', pattern: /\boffer:taxonomy:(?:migrate|cache-clear|runtime|source|finalize|strict-check)\b/ },
  { id: 'offer-taxonomy-runtime-class', pattern: /\bOfferTaxonomy(?:Repository|Service|Runtime|Migration|Cache|Checker|Terms|Presets)\b/ },
];

function usage() {
  return `Usage:
  node tools/offer-taxonomy-implementation-gate.mjs [--approval=/path/to/approved.json]
  node tools/offer-taxonomy-implementation-gate.mjs --self-test

Blocks accidental /offer/ taxonomy Bitrix runtime/schema work before ADR-012 is
accepted and a safe owner approval JSON is approved. Approved JSON can be passed
explicitly or discovered in docs/workflow. The current fast-fix CatalogTaxonomy
shim is intentionally allowed.
`;
}

function parseArgs(argv) {
  const options = {
    approvalPath: process.env.TACTICUM_OFFER_TAXONOMY_APPROVAL || DEFAULT_APPROVAL_PATH,
    approvalPathExplicit: Boolean(process.env.TACTICUM_OFFER_TAXONOMY_APPROVAL),
    selfTest: false,
  };

  for (const arg of argv.slice(2)) {
    if (arg === '--help' || arg === '-h') {
      console.log(usage());
      process.exit(0);
    }
    if (arg === '--self-test') {
      options.selfTest = true;
      continue;
    }
    if (arg.startsWith('--approval=')) {
      options.approvalPath = arg.slice('--approval='.length);
      options.approvalPathExplicit = true;
      continue;
    }
    throw new Error(`Unknown argument: ${arg}`);
  }

  return options;
}

function readText(path) {
  return existsSync(path) ? readFileSync(path, 'utf8') : '';
}

function isAcceptedAdr(text) {
  return /^\s*Статус:\s*Принято\s*$/im.test(text);
}

function isApprovedJson(payload) {
  if (!payload || typeof payload !== 'object' || Array.isArray(payload)) {
    return false;
  }
  if (payload.status !== 'approved') {
    return false;
  }
  if (payload.runtime_switch_approved !== false || payload.iblock_apply_approved !== false) {
    return false;
  }

  const owners = payload.owners || {};
  const gates = payload.gates || {};
  return Object.values(owners).length > 0
    && Object.values(gates).length > 0
    && Object.values(owners).every((row) => row && row.approved === true)
    && Object.values(gates).every((value) => value === true);
}

function readApproval(path) {
  if (!existsSync(path)) {
    return { ok: false, reason: `approval file not found: ${path}` };
  }
  try {
    const payload = JSON.parse(readFileSync(path, 'utf8'));
    if (!isApprovedJson(payload)) {
      return { ok: false, reason: `approval file is not approved: ${path}` };
    }
    return { ok: true, reason: '' };
  } catch (error) {
    return { ok: false, reason: `approval file is invalid JSON: ${path}` };
  }
}

function discoverApprovedApproval(root = APPROVAL_DIR) {
  if (!existsSync(root)) {
    return { ok: false, reason: `approval directory not found: ${root}` };
  }

  const approved = [];
  for (const entry of readdirSync(root)) {
    if (!APPROVAL_FILE_RE.test(entry)) {
      continue;
    }
    const path = `${root}/${entry}`;
    const approval = readApproval(path);
    if (approval.ok) {
      approved.push(path);
    }
  }

  if (approved.length === 0) {
    return { ok: false, reason: `approved owner JSON not found in ${root}` };
  }
  if (approved.length > 1) {
    return {
      ok: false,
      reason: `multiple approved owner JSON files found; pass --approval=<file>: ${approved.join(', ')}`,
    };
  }

  return { ok: true, path: approved[0], reason: '' };
}

function resolveApproval({ approvalPath, approvalPathExplicit }) {
  const direct = readApproval(approvalPath);
  if (direct.ok || approvalPathExplicit) {
    return direct;
  }

  const discovered = discoverApprovedApproval();
  if (discovered.ok) {
    return { ok: true, reason: '', path: discovered.path };
  }

  return {
    ok: false,
    reason: `${direct.reason}; ${discovered.reason}`,
  };
}

function collectFiles() {
  const files = new Set();
  for (const file of SCAN_FILES) {
    if (existsSync(file) && !IGNORED_FILES.has(file)) {
      files.add(file);
    }
  }

  for (const dir of SCAN_DIRS) {
    for (const file of walkFiles(dir)) {
      if (!IGNORED_FILES.has(file)) {
        files.add(file);
      }
    }
  }

  return Array.from(files).sort();
}

function walkFiles(root) {
  if (!existsSync(root)) {
    return [];
  }
  const output = [];
  const stack = [root];
  while (stack.length > 0) {
    const current = stack.pop();
    const stats = statSync(current);
    if (stats.isDirectory()) {
      for (const entry of readdirSync(current)) {
        if (entry === '.' || entry === '..') {
          continue;
        }
        stack.push(`${current}/${entry}`);
      }
      continue;
    }
    if (stats.isFile() && /\.(?:php|mjs|js|json)$/i.test(current)) {
      output.push(current);
    }
  }

  return output;
}

function findMarkersInContent(content, file) {
  const findings = [];
  for (const marker of MARKERS) {
    if (marker.pattern.test(content)) {
      findings.push({ file, marker: marker.id });
    }
  }

  return findings;
}

function findMarkers(files = collectFiles()) {
  const findings = [];
  for (const file of files) {
    const content = readText(file);
    findings.push(...findMarkersInContent(content, file));
  }

  return findings;
}

function validateGate({ adrText, approvalPath, approvalPathExplicit = false, findings }) {
  const errors = [];
  if (findings.length === 0) {
    return { ok: true, errors };
  }

  if (!isAcceptedAdr(adrText)) {
    errors.push('ADR-012 must be accepted before offer taxonomy runtime/schema markers are added.');
  }

  const approval = resolveApproval({ approvalPath, approvalPathExplicit });
  if (!approval.ok) {
    errors.push(approval.reason);
  }

  return { ok: errors.length === 0, errors };
}

function approvedPayload() {
  const owners = Object.fromEntries(['architect', 'backend', 'pm', 'content', 'seo', 'sales', 'qa'].map((owner) => [owner, { approved: true }]));
  const gates = Object.fromEntries(['adr_review', 'content_storage_boundary', 'seo_noindex_preserved', 'counts_runtime_derived'].map((gate) => [gate, true]));
  return {
    status: 'approved',
    runtime_switch_approved: false,
    iblock_apply_approved: false,
    owners,
    gates,
  };
}

function runSelfTest() {
  const noMarkers = [];
  const markers = [{ file: 'local/lib/Tacticum/Offer/OfferTaxonomyRepository.php', marker: 'offer-taxonomy-runtime-class' }];
  const proposedAdr = 'Статус: Предложено';
  const acceptedAdr = 'Статус: Принято';
  const approval = approvedPayload();
  const tempRoot = mkdtempSync(`${tmpdir()}/offer-taxonomy-gate-`);

  const checks = [
    [validateGate({ adrText: proposedAdr, approvalPath: DEFAULT_APPROVAL_PATH, findings: noMarkers }).ok, 'no markers should pass without approval'],
    [!validateGate({ adrText: proposedAdr, approvalPath: DEFAULT_APPROVAL_PATH, findings: markers }).ok, 'markers with proposed ADR should fail'],
    [isApprovedJson(approval), 'approved payload shape should pass local approval check'],
    [validateGate({
      adrText: acceptedAdr,
      approvalPath: DEFAULT_APPROVAL_PATH,
      findings: noMarkers,
    }).ok, 'accepted ADR without markers should pass'],
  ];

  const failed = checks.filter(([passed]) => !passed);
  if (failed.length > 0) {
    for (const [, label] of failed) {
      console.error(`Self-test failed: ${label}`);
    }
    process.exit(1);
  }

  const markerFindings = findMarkersInContent('offer_taxonomy_terms OfferTaxonomyRepository', 'fixture.php');
  if (markerFindings.length !== 2) {
    console.error('Self-test failed: marker detection should find two markers.');
    process.exit(1);
  }

  try {
    const approvedPath = `${tempRoot}/offer-taxonomy-presets-owner-approval-2026-06-07.approved.json`;
    writeFileSync(approvedPath, JSON.stringify(approval), 'utf8');
    const discovered = discoverApprovedApproval(tempRoot);
    if (!discovered.ok || discovered.path !== approvedPath) {
      console.error('Self-test failed: approved owner JSON discovery should find the approved fixture.');
      process.exit(1);
    }
  } finally {
    rmSync(tempRoot, { recursive: true, force: true });
  }

  console.log('Offer taxonomy implementation gate self-test passed.');
}

function main() {
  const options = parseArgs(process.argv);
  if (options.selfTest) {
    runSelfTest();
    return;
  }

  const findings = findMarkers();
  const gate = validateGate({
    adrText: readText(ADR_PATH),
    approvalPath: options.approvalPath,
    approvalPathExplicit: options.approvalPathExplicit,
    findings,
  });

  if (!gate.ok) {
    console.error('Offer taxonomy implementation gate failed:');
    for (const error of gate.errors) {
      console.error(`- ${error}`);
    }
    console.error('Detected implementation markers:');
    for (const finding of findings) {
      console.error(`- ${finding.file}: ${finding.marker}`);
    }
    process.exit(1);
  }

  if (findings.length === 0) {
    console.log('Offer taxonomy implementation gate passed: no runtime/schema markers found.');
    return;
  }

  console.log(`Offer taxonomy implementation gate passed: ${findings.length} runtime/schema marker(s) found with accepted ADR and approved owner JSON.`);
}

try {
  main();
} catch (error) {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
