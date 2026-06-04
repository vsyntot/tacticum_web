#!/usr/bin/env node

import { readFile } from 'node:fs/promises';
import { dirname, isAbsolute, resolve } from 'node:path';

const args = process.argv.slice(2);
const allowPending = args.includes('--allow-pending') || process.env.TACTICUM_RELEASE_SIGNOFF_ALLOW_PENDING === '1';
const summaryMode = args.includes('--summary') || process.env.TACTICUM_RELEASE_SIGNOFF_SUMMARY === '1';
const positionalArgs = args.filter((arg) => arg !== '--allow-pending' && arg !== '--summary');
const file = positionalArgs[0] || process.env.TACTICUM_RELEASE_SIGNOFF || 'docs/workflow/release-signoff.example.json';
const requiredGates = [
  'automated-deploy-smoke',
  'seo-rendered-head',
  'price-team-presets',
  'css-js-e2e-readiness',
  'manual-success-flow',
  'metrika-goals',
  'config-sync',
  'bitrix-admin',
  'legacy-sunset',
  'staff-sale-upstream',
  'csp-enforce',
  'sensitive-endpoint-access',
  'endpoint-risk-class',
  'legacy-final-mode',
];
const requiredGateSet = new Set(requiredGates);
const manualEvidenceGates = new Set([
  'manual-success-flow',
  'metrika-goals',
  'bitrix-admin',
  'staff-sale-upstream',
]);
const safetyScannedEvidenceGates = new Set([
  ...manualEvidenceGates,
  'csp-enforce',
  'sensitive-endpoint-access',
  'endpoint-risk-class',
  'legacy-final-mode',
]);
const forbiddenManualEvidenceKeys = [
  'cookie',
  'email',
  'contact',
  'contacts',
  'document_url',
  'message',
  'password',
  'payload',
  'phone',
  'raw',
  'raw_body',
  'raw_log',
  'raw_logs',
  'raw_payload',
  'raw_request',
  'raw_response',
  'referer',
  'referrer',
  'secret',
  'session',
  'sessid',
  'signed_url',
  'token',
  'user_agent',
];
const allowedStatuses = new Set(allowPending ? ['passed', 'not_applicable', 'pending'] : ['passed', 'not_applicable']);
const productPages = new Set(['/platform/', '/agents/', '/dev/', '/forum/']);
const endpointRiskClasses = new Set([
  'PUBLIC_READ',
  'PUBLIC_CONFIG_HEALTH',
  'PUBLIC_LEAD_POST',
  'PUBLIC_CHAT_POST',
  'PUBLIC_STAFF_POST',
  'SCOPED_PREFILL_POST',
  'PUBLIC_RESOLVER_POST',
  'LEGACY_ALIAS_POST',
  'PRIVATE_PROOF_DOC',
  'INTERNAL_ADMIN_OR_INTEGRATION',
]);
const sensitiveAccessModels = new Set([
  'authenticated-session',
  'expiring-signed-link',
  'owner-approved-token',
]);
const legacyFinalModes = new Set([
  'remove',
  '410',
  'redirect',
  'extend-support',
  'compatibility-endpoint',
]);
const legacyAliasPaths = [
  '/local/rest/tacticum_offer.php',
  '/local/rest/tacticum_sale.php',
];
const requiredProductBlocks = [
  'hero',
  'fit-guide',
  'content-section',
  'architecture',
  'use-cases',
  'comparison',
  'procurement',
  'rollout',
  'proof',
  'faq',
  'lead-cta',
];
const failures = [];
const pendingGates = [];

let payload;
try {
  payload = JSON.parse(await readFile(file, 'utf8'));
} catch (error) {
  console.error(`Cannot read release sign-off file: ${file}`);
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}

const signoffDir = dirname(resolve(file));

if (!payload || typeof payload !== 'object' || Array.isArray(payload)) {
  fail('root must be an object');
}

validateReleaseMetadata(payload.release);

const gates = payload.gates || {};
if (!gates || typeof gates !== 'object' || Array.isArray(gates)) {
  fail('gates must be an object');
}

for (const gateName of Object.keys(gates)) {
  if (!requiredGateSet.has(gateName)) {
    fail(`${gateName}: unknown gate`);
  }
}

for (const gateName of requiredGates) {
  const gate = gates[gateName];
  if (!gate || typeof gate !== 'object' || Array.isArray(gate)) {
    fail(`${gateName}: missing gate object`);
    continue;
  }

  const status = String(gate.status || '').trim();
  if (!allowedStatuses.has(status)) {
    const allowed = allowPending ? '"passed", "not_applicable" or "pending"' : '"passed" or "not_applicable"';
    fail(`${gateName}: status must be ${allowed} before release closure`);
  }

  if (status === 'passed' && !hasMeaningfulEvidence(gate.evidence)) {
    fail(`${gateName}: passed gate must include evidence`);
  }

  if (status === 'not_applicable' && !hasMeaningfulEvidence(gate.reason)) {
    fail(`${gateName}: not_applicable gate must include reason`);
  }

  if (status === 'pending' && !hasMeaningfulEvidence(gate.reason)) {
    fail(`${gateName}: pending gate must include reason`);
  }

  if (status === 'pending') {
    pendingGates.push(gateName);
    if (!hasMeaningfulEvidence(gate.due)) {
      fail(`${gateName}: pending gate must include due`);
    }
    if (manualEvidenceGates.has(gateName)) {
      validatePendingManualGate(gateName, gate.evidence);
    }
  }

  if (!hasMeaningfulEvidence(gate.owner)) {
    fail(`${gateName}: owner is required`);
  }

  if (status === 'passed') {
    await validateGateEvidence(gateName, gate.evidence);
  }
}

if (failures.length > 0) {
  console.error(`Release sign-off check failed for ${file}:`);
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

if (summaryMode) {
  printSummary(payload, gates, file);
} else {
  console.log(`${allowPending ? 'Release sign-off draft check' : 'Release sign-off check'} passed: ${file}`);
  if (allowPending && pendingGates.length > 0) {
    console.log(`Pending gates: ${pendingGates.join(', ')}`);
  }
}

function fail(message) {
  failures.push(message);
}

function printSummary(payload, gates, sourceFile) {
  const release = payload.release || {};
  console.log(`Release sign-off summary: ${sourceFile}`);
  console.log(`Release: ${release.id || 'unknown'} | date=${release.date || 'unknown'} | commit=${release.commit || 'unknown'}`);
  if (release.base_url) {
    console.log(`Base URL: ${release.base_url}`);
  }
  console.log('');

  const rows = requiredGates.map((gateName) => {
    const gate = gates[gateName] || {};
    const status = String(gate.status || 'missing');
    const owner = String(gate.owner || '-');
    const detail = status === 'pending'
      ? `due=${String(gate.due || '-')}; ${String(gate.reason || '-')}`
      : summarizeEvidence(gate);
    return [gateName, status, owner, detail];
  });

  const widths = [0, 0, 0, 0];
  for (const row of [['Gate', 'Status', 'Owner', 'Detail'], ...rows]) {
    row.forEach((value, index) => {
      widths[index] = Math.max(widths[index], String(value).length);
    });
  }

  printSummaryRow(['Gate', 'Status', 'Owner', 'Detail'], widths);
  printSummaryRow(widths.map((width) => '-'.repeat(width)), widths);
  for (const row of rows) {
    printSummaryRow(row, widths);
  }

  if (pendingGates.length > 0) {
    console.log('');
    console.log(`Pending gates: ${pendingGates.join(', ')}`);
  }
}

function printSummaryRow(values, widths) {
  console.log(values.map((value, index) => String(value).padEnd(widths[index])).join(' | '));
}

function summarizeEvidence(gate) {
  if (gate.status === 'not_applicable') {
    return String(gate.reason || '-');
  }

  if (!gate.evidence || typeof gate.evidence !== 'object' || Array.isArray(gate.evidence)) {
    return hasMeaningfulEvidence(gate.evidence) ? String(gate.evidence) : '-';
  }

  const keys = Object.keys(gate.evidence);
  return keys.length > 0 ? keys.join(', ') : '-';
}

function validateReleaseMetadata(release) {
  if (!release || typeof release !== 'object' || Array.isArray(release)) {
    fail('release must be an object');
    return;
  }

  requireFields('release', release, ['id', 'date', 'commit']);

  if (hasMeaningfulEvidence(release.id) && hasPlaceholder(release.id)) {
    fail('release.id contains placeholder');
  }

  if (hasMeaningfulEvidence(release.date) && !/^\d{4}-\d{2}-\d{2}$/.test(String(release.date).trim())) {
    fail('release.date must be YYYY-MM-DD');
  }

  if (hasMeaningfulEvidence(release.commit)) {
    const commit = String(release.commit).trim();
    if (hasPlaceholder(commit)) {
      fail('release.commit contains placeholder');
    }
    if (!allowPending && /working-tree/i.test(commit)) {
      fail('release.commit must reference a release commit, not working-tree state');
    }
  }

  if (hasMeaningfulEvidence(release.base_url) && !/^https:\/\//i.test(String(release.base_url).trim())) {
    fail('release.base_url must use HTTPS');
  }
}

function validatePendingManualGate(gateName, evidence) {
  if (!evidence || typeof evidence !== 'object' || Array.isArray(evidence)) {
    fail(`${gateName}: pending manual gate must include evidence.runbook and evidence_template`);
    return;
  }

  if (!hasMeaningfulEvidence(evidence.runbook)) {
    fail(`${gateName}: pending manual gate must include evidence.runbook`);
  }

  if (!hasMeaningfulEvidence(evidence.evidence_template)) {
    fail(`${gateName}: pending manual gate must include evidence.evidence_template`);
  }
}

async function validateGateEvidence(gateName, evidence) {
  if (!evidence || typeof evidence !== 'object' || Array.isArray(evidence)) {
    if (manualEvidenceGates.has(gateName)) {
      fail(`${gateName}: manual evidence must be an object`);
    }
    return;
  }

  if (gateName === 'automated-deploy-smoke') {
    await validateManifestEvidence(gateName, evidence.visual_smoke_manifest, {});
    await validateManifestEvidence(gateName, evidence.browser_smoke_manifest, { requireWarningsGate: true });
  }

  if (gateName === 'seo-rendered-head') {
    await validateManifestEvidence(gateName, evidence.seo_smoke_manifest, { requireSeo: true });
  }

  if (gateName === 'price-team-presets') {
    await validateManifestEvidence(gateName, evidence.price_smoke_manifest, { requirePriceTeam: true });
  }

  if (gateName === 'css-js-e2e-readiness') {
    await validateManifestEvidence(gateName, evidence.production_visual_manifest, {});
    await validateManifestEvidence(gateName, evidence.production_browser_manifest, { requireWarningsGate: true });
    await validateManifestEvidence(gateName, evidence.production_price_manifest, { requirePriceTeam: true });
    if (hasMeaningfulEvidence(evidence.css_local_visual_manifest)) {
      await validateManifestEvidence(gateName, evidence.css_local_visual_manifest, {});
    }
    if (hasMeaningfulEvidence(evidence.css_local_browser_manifest)) {
      await validateManifestEvidence(gateName, evidence.css_local_browser_manifest, { requireWarningsGate: true });
    }
  }

  if (safetyScannedEvidenceGates.has(gateName)) {
    validateEvidenceSafety(gateName, evidence);
  }

  if (gateName === 'manual-success-flow') {
    validateManualSuccessFlow(evidence);
  }

  if (gateName === 'metrika-goals') {
    validateMetrikaGoals(evidence);
  }

  if (gateName === 'bitrix-admin') {
    validateBitrixAdmin(evidence);
  }

  if (gateName === 'staff-sale-upstream') {
    validateStaffSaleUpstream(evidence);
  }

  if (gateName === 'csp-enforce') {
    validateCspEnforce(evidence);
  }

  if (gateName === 'sensitive-endpoint-access') {
    validateSensitiveEndpointAccess(evidence);
  }

  if (gateName === 'endpoint-risk-class') {
    validateEndpointRiskClass(evidence);
  }

  if (gateName === 'legacy-final-mode') {
    validateLegacyFinalMode(evidence);
  }
}

async function validateManifestEvidence(gateName, value, options) {
  if (!hasMeaningfulEvidence(value)) {
    fail(`${gateName}: manifest evidence is missing`);
    return;
  }

  const manifestPath = String(value).trim();
  if (/^https?:\/\//i.test(manifestPath)) {
    return;
  }

  let manifest;
  const resolvedPath = isAbsolute(manifestPath)
    ? manifestPath
    : resolve(signoffDir, manifestPath);

  try {
    manifest = JSON.parse(await readFile(resolvedPath, 'utf8'));
  } catch (error) {
    fail(`${gateName}: cannot read manifest ${manifestPath}`);
    return;
  }

  validateVisualManifest(gateName, manifest, options);
}

function validateVisualManifest(gateName, manifest, options = {}) {
  const results = manifest?.results;
  if (!Array.isArray(results) || results.length === 0) {
    fail(`${gateName}: manifest has no results`);
    return;
  }

  if (options.requireWarningsGate && manifest.failOnWarnings !== true) {
    fail(`${gateName}: manifest must be generated with TACTICUM_VISUAL_FAIL_ON_WARNINGS=1`);
  }

  for (const result of results) {
    const label = `${result.page || 'unknown'} ${result.viewport || 'unknown'}`;
    for (const field of ['errors', 'pageErrors', 'consoleErrors', 'consoleWarnings', 'networkErrors', 'actionErrors']) {
      if (Array.isArray(result[field]) && result[field].length > 0) {
        fail(`${gateName}: ${label} has ${field}`);
      }
    }

    if (options.requireSeo) {
      validateSeoResult(gateName, result, label);
    }
  }

  if (options.requirePriceTeam) {
    validatePriceTeamResults(gateName, results);
  }
}

function validateSeoResult(gateName, result, label) {
  const seoHead = result.seoHead || {};
  if (Array.isArray(result.seoErrors) && result.seoErrors.length > 0) {
    fail(`${gateName}: ${label} has seoErrors`);
  }
  if (seoHead.titleCount !== 1) {
    fail(`${gateName}: ${label} titleCount must be 1`);
  }
  if (!Array.isArray(seoHead.descriptions) || seoHead.descriptions.length !== 1) {
    fail(`${gateName}: ${label} must have exactly one description`);
  }
  if (!Array.isArray(seoHead.canonicals) || seoHead.canonicals.length !== 1) {
    fail(`${gateName}: ${label} must have exactly one canonical`);
  }
  if (seoHead.h1Count !== 1) {
    fail(`${gateName}: ${label} h1Count must be 1`);
  }
  if (Array.isArray(seoHead.duplicateOpenGraphProperties) && seoHead.duplicateOpenGraphProperties.length > 0) {
    fail(`${gateName}: ${label} has duplicate OpenGraph properties`);
  }

  const openGraph = seoHead.openGraph || {};
  for (const property of ['og:site_name', 'og:type', 'og:url', 'og:title', 'og:description', 'og:image']) {
    if (!Array.isArray(openGraph[property]) || openGraph[property].length !== 1 || !hasMeaningfulEvidence(openGraph[property][0])) {
      fail(`${gateName}: ${label} missing ${property}`);
    }
  }

  if (productPages.has(result.page)) {
    const productSchemaSummary = seoHead.productSchemaSummary || {};
    const schemaTypes = Array.isArray(productSchemaSummary.schemaTypes) ? productSchemaSummary.schemaTypes : [];
    if (!schemaTypes.includes('SoftwareApplication') || productSchemaSummary.softwareApplicationCount < 1) {
      fail(`${gateName}: ${label} missing product SoftwareApplication schema summary`);
    }
    if (!schemaTypes.includes('FAQPage') || productSchemaSummary.faqPageCount < 1) {
      fail(`${gateName}: ${label} missing product FAQPage schema summary`);
    }
    validateProductBlocks(gateName, result, label);
  }
}

function validateProductBlocks(gateName, result, label) {
  if (Array.isArray(result.productBlockErrors) && result.productBlockErrors.length > 0) {
    fail(`${gateName}: ${label} has productBlockErrors`);
  }

  const productBlocks = result.productBlocks || {};
  const foundBlocks = Array.isArray(productBlocks.found) ? productBlocks.found : [];
  for (const block of requiredProductBlocks) {
    if (!foundBlocks.includes(block)) {
      fail(`${gateName}: ${label} missing product block ${block}`);
    }
  }
}

function validatePriceTeamResults(gateName, results) {
  const priceResults = results.filter((result) => result.page === '/price/');
  const requiredViewports = new Set(['desktop', 'mobile']);

  for (const viewport of requiredViewports) {
    const result = priceResults.find((item) => item.viewport === viewport);
    if (!result) {
      fail(`${gateName}: missing /price/ ${viewport} result`);
      continue;
    }

    const action = Array.isArray(result.actions)
      ? result.actions.find((item) => item.label === 'price team presets/summary')
      : null;

    if (!action || action.status !== 'ok') {
      fail(`${gateName}: /price/ ${viewport} team preset action is not ok`);
      continue;
    }

    const detail = String(action.detail || '');
    if (!/workers=\d+/.test(detail) || !/budget=/.test(detail)) {
      fail(`${gateName}: /price/ ${viewport} team preset action lacks workers/budget detail`);
    }
  }
}

function validateManualSuccessFlow(evidence) {
  requireFields('manual-success-flow', evidence, ['environment', 'checked_at', 'checked_by', 'flows']);
  validateIsoDateTime('manual-success-flow', evidence.checked_at);

  if (!Array.isArray(evidence.flows) || evidence.flows.length === 0) {
    fail('manual-success-flow: evidence.flows must be a non-empty array');
    return;
  }

  for (const [index, flow] of evidence.flows.entries()) {
    const label = `manual-success-flow: flows[${index}]`;
    if (!flow || typeof flow !== 'object' || Array.isArray(flow)) {
      fail(`${label} must be an object`);
      continue;
    }
    requireFields(label, flow, ['flow', 'url', 'result']);
  }
}

function validateMetrikaGoals(evidence) {
  requireFields('metrika-goals', evidence, ['counter_id', 'checked_at', 'checked_by', 'goals', 'pii_check']);
  validateIsoDateTime('metrika-goals', evidence.checked_at);

  if (!Array.isArray(evidence.goals) || evidence.goals.length === 0) {
    fail('metrika-goals: evidence.goals must be a non-empty array');
    return;
  }

  for (const [index, goal] of evidence.goals.entries()) {
    if (!hasMeaningfulEvidence(goal)) {
      fail(`metrika-goals: goals[${index}] is empty`);
    }
  }
}

function validateBitrixAdmin(evidence) {
  requireFields('bitrix-admin', evidence, ['checked_at', 'checked_by', 'admin_url', 'role', 'result']);
  validateIsoDateTime('bitrix-admin', evidence.checked_at);
}

function validateStaffSaleUpstream(evidence) {
  requireFields('staff-sale-upstream', evidence, [
    'environment',
    'checked_at',
    'checked_by',
    'health_config',
    'url',
    'form_id',
    'team_preset',
    'workers_count',
    'monthly_budget_estimate_present',
    'end_date_present',
    'result',
  ]);
  validateIsoDateTime('staff-sale-upstream', evidence.checked_at);

  if (!hasMeaningfulEvidence(evidence.upstream_request_id) && !hasMeaningfulEvidence(evidence.lead_id)) {
    fail('staff-sale-upstream: missing upstream_request_id or lead_id');
  }

  const workersCount = Number(evidence.workers_count);
  if (!Number.isFinite(workersCount) || workersCount < 1) {
    fail('staff-sale-upstream: workers_count must be a positive number');
  }

  if (evidence.monthly_budget_estimate_present !== true) {
    fail('staff-sale-upstream: monthly_budget_estimate_present must be true');
  }

  if (evidence.end_date_present !== true) {
    fail('staff-sale-upstream: end_date_present must be true');
  }

  const teamPreset = String(evidence.team_preset || '').trim();
  if (!['mvp', 'discovery', 'support', 'qa-burst'].includes(teamPreset)) {
    fail('staff-sale-upstream: team_preset must be one of mvp, discovery, support, qa-burst');
  }
}

function validateCspEnforce(evidence) {
  requireFields('csp-enforce', evidence, [
    'environment',
    'checked_at',
    'checked_by',
    'mode',
    'report_only_baseline',
    'inline_inventory',
    'vendor_inventory',
    'staging_enforce_smoke',
    'rollback',
  ]);
  validateIsoDateTime('csp-enforce', evidence.checked_at);

  if (String(evidence.mode || '').trim() !== 'enforce') {
    fail('csp-enforce: mode must be enforce');
  }

  if (evidence.violations_triaged !== true) {
    fail('csp-enforce: violations_triaged must be true');
  }

  if (evidence.rollback_to_report_only_documented !== true) {
    fail('csp-enforce: rollback_to_report_only_documented must be true');
  }
}

function validateSensitiveEndpointAccess(evidence) {
  requireFields('sensitive-endpoint-access', evidence, [
    'environment',
    'checked_at',
    'checked_by',
    'flow',
    'access_model',
    'allowed_result',
    'denied_result',
    'expired_or_malformed_result',
    'noindex_or_cache_policy',
    'logging_pii_check',
  ]);
  validateIsoDateTime('sensitive-endpoint-access', evidence.checked_at);

  const accessModel = String(evidence.access_model || '').trim();
  if (!sensitiveAccessModels.has(accessModel)) {
    fail('sensitive-endpoint-access: access_model must be one of authenticated-session, expiring-signed-link, owner-approved-token');
  }
}

function validateEndpointRiskClass(evidence) {
  requireFields('endpoint-risk-class', evidence, [
    'checked_at',
    'checked_by',
    'endpoint',
    'risk_class',
    'origin_csrf',
    'rate_limit',
    'auth_ip_proxy',
    'logging_evidence',
  ]);
  validateIsoDateTime('endpoint-risk-class', evidence.checked_at);

  const endpoint = String(evidence.endpoint || '').trim();
  if (endpoint && !endpoint.startsWith('/') && !/^https:\/\//i.test(endpoint)) {
    fail('endpoint-risk-class: endpoint must be a site path or HTTPS URL');
  }

  if (/^http:\/\//i.test(endpoint)) {
    fail('endpoint-risk-class: endpoint URL must use HTTPS');
  }

  const riskClass = String(evidence.risk_class || '').trim();
  if (!endpointRiskClasses.has(riskClass)) {
    fail('endpoint-risk-class: risk_class must match the Sprint 22 endpoint sensitivity matrix');
  }
}

function validateLegacyFinalMode(evidence) {
  requireFields('legacy-final-mode', evidence, [
    'checked_at',
    'checked_by',
    'aliases',
    'final_mode',
    'inventory_window',
    'access_log_aggregate',
    'crm_upstream_report',
    'implementation_result',
    'rollback_or_support_plan',
  ]);
  validateIsoDateTime('legacy-final-mode', evidence.checked_at);

  const finalMode = String(evidence.final_mode || '').trim();
  if (!legacyFinalModes.has(finalMode)) {
    fail('legacy-final-mode: final_mode must be one of remove, 410, redirect, extend-support, compatibility-endpoint');
  }

  if (!Array.isArray(evidence.aliases)) {
    fail('legacy-final-mode: aliases must be an array');
    return;
  }

  for (const alias of legacyAliasPaths) {
    if (!evidence.aliases.includes(alias)) {
      fail(`legacy-final-mode: aliases must include ${alias}`);
    }
  }
}

function validateEvidenceSafety(gateName, evidence) {
  for (const item of flattenEvidence(evidence)) {
    const key = item.path.split('.').at(-1) || '';
    const normalizedKey = key.toLowerCase();
    if (forbiddenManualEvidenceKeys.includes(normalizedKey)) {
      fail(`${gateName}: manual evidence must not include unsafe key "${item.path}"`);
    }

    if (typeof item.value !== 'string') {
      continue;
    }

    const value = item.value.trim();
    if (!value) {
      continue;
    }

    if (hasPlaceholder(value)) {
      fail(`${gateName}: manual evidence contains placeholder at "${item.path}"`);
    }
    if (/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i.test(value)) {
      fail(`${gateName}: manual evidence appears to contain an email at "${item.path}"`);
    }
    if (!isAllowedDatetimeEvidence(item.path, value) && /(?:\+?\d[\d ()-]{8,}\d)/.test(value)) {
      fail(`${gateName}: manual evidence appears to contain a phone number at "${item.path}"`);
    }
    if (/(cookie|session|sessid|password|token|secret)\s*[:=]/i.test(value)) {
      fail(`${gateName}: manual evidence appears to contain secret/session material at "${item.path}"`);
    }
  }
}

function hasPlaceholder(value) {
  return /\b(replace-with|todo|tbd)\b/i.test(String(value || ''));
}

function isAllowedDatetimeEvidence(path, value) {
  if (!path.endsWith('.checked_at') && !path.endsWith('.observed_after')) {
    return false;
  }

  return /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/.test(String(value || '').trim());
}

function flattenEvidence(value, path = 'evidence') {
  if (Array.isArray(value)) {
    return value.flatMap((item, index) => flattenEvidence(item, `${path}[${index}]`));
  }

  if (value && typeof value === 'object') {
    return Object.entries(value).flatMap(([key, nestedValue]) => flattenEvidence(nestedValue, `${path}.${key}`));
  }

  return [{ path, value }];
}

function requireFields(label, value, fields) {
  for (const field of fields) {
    if (!hasMeaningfulEvidence(value?.[field])) {
      fail(`${label}: missing ${field}`);
    }
  }
}

function validateIsoDateTime(label, value) {
  if (!hasMeaningfulEvidence(value)) {
    return;
  }

  if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/.test(String(value).trim())) {
    fail(`${label}: checked_at must be ISO datetime with timezone`);
  }
}

function hasMeaningfulEvidence(value) {
  if (Array.isArray(value)) {
    return value.some(hasMeaningfulEvidence);
  }
  if (value && typeof value === 'object') {
    return Object.values(value).some(hasMeaningfulEvidence);
  }
  return String(value || '').trim().length > 0;
}
