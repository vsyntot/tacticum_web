#!/usr/bin/env node

import { randomBytes } from 'node:crypto';

const args = process.argv.slice(2);
const knownOptions = new Set(['--payload', '--browser', '--curl', '--evidence', '--all', '--help']);
const unknownOptions = args.filter((arg) => arg.startsWith('--') && !knownOptions.has(arg));

if (unknownOptions.length > 0) {
  console.error(`Unknown option: ${unknownOptions.join(', ')}`);
  printUsage();
  process.exit(1);
}

if (args.includes('--help')) {
  printUsage();
  process.exit(0);
}

const selectedModes = new Set(args.filter((arg) => arg.startsWith('--')));
if (selectedModes.size === 0 || selectedModes.has('--all')) {
  selectedModes.add('--payload');
  selectedModes.add('--browser');
  selectedModes.add('--curl');
  selectedModes.add('--evidence');
}

const baseUrl = trimTrailingSlash(process.env.TACTICUM_STAFF_GATE_BASE_URL || 'https://tacticum.ru');
const qaMarker = buildQaMarker();
const payload = buildPayload(baseUrl, qaMarker);

if (selectedModes.has('--payload')) {
  printSection('Controlled Staff-Order Payload');
  console.log(JSON.stringify(payload, null, 2));
}

if (selectedModes.has('--curl')) {
  printSection('Controlled Staff-Order Curl');
  printCurl(baseUrl, payload);
}

if (selectedModes.has('--browser')) {
  printSection('Controlled Staff-Order Browser Snippet');
  printBrowserSnippet(baseUrl, payload);
}

if (selectedModes.has('--evidence')) {
  printSection('Staff-Sale Upstream Evidence Template');
  console.log(JSON.stringify(buildEvidenceTemplate(baseUrl, qaMarker), null, 2));
}

function buildPayload(baseUrlValue, marker) {
  const workers = [
    {
      role: 'Backend developer',
      level: 'Middle',
      cost_per_hour: '3500',
      amount_of_workers: 2,
    },
    {
      role: 'QA engineer',
      level: 'Middle',
      cost_per_hour: '2800',
      amount_of_workers: 1,
    },
  ];

  return {
    sessid: process.env.TACTICUM_STAFF_TEST_SESSID || '<bitrix_sessid_from_browser>',
    name: process.env.TACTICUM_STAFF_TEST_NAME || '<controlled_test_name>',
    company: process.env.TACTICUM_STAFF_TEST_COMPANY || 'Tacticum QA controlled test',
    email: process.env.TACTICUM_STAFF_TEST_EMAIL || '<controlled_test_email>',
    phone: process.env.TACTICUM_STAFF_TEST_PHONE || '<controlled_test_phone>',
    message: `Controlled staff-order smoke. QA marker: ${marker}. Test lead; no commercial processing.`,
    form_id: 'price-specialist',
    page_url: `${baseUrlValue}/price/`,
    team_preset: 'mvp',
    workers,
    workers_json: JSON.stringify(workers),
    amount_of_workers: 3,
    workload: 'full-time',
    duration: 'exact-date',
    endDate: '2026-07-31',
    monthly_budget_estimate: '1232000',
  };
}

function buildEvidenceTemplate(baseUrlValue, marker) {
  return {
    environment: 'controlled-production',
    checked_at: '<YYYY-MM-DDTHH:mm:ss+03:00>',
    checked_by: '<owner>',
    health_config: 'success=true; scopes include ai, rest, security',
    qa_marker: marker,
    url: `${baseUrlValue}/price/`,
    form_id: 'price-specialist',
    team_preset: 'mvp',
    workers_count: 3,
    monthly_budget_estimate_present: true,
    end_date_present: true,
    upstream_request_id: '<safe-upstream-id-or-lead-id>',
    result: 'upstream/CRM received team summary, team_preset, monthly_budget_estimate and end_date',
  };
}

function buildQaMarker() {
  const explicit = String(process.env.TACTICUM_STAFF_TEST_MARKER || '').trim();
  if (explicit) {
    return explicit;
  }

  const alphabet = 'abcdefghijklmnopqrstuvwxyz';
  const bytes = randomBytes(8);
  const suffix = Array.from(bytes, (byte) => alphabet[byte % alphabet.length]).join('');
  return `staff-smoke-${suffix}`;
}

function printCurl(baseUrlValue, payloadValue) {
  const missing = requiredCurlEnv().filter((name) => !String(process.env[name] || '').trim());
  if (missing.length > 0) {
    console.log('Set these environment variables before using the curl command:');
    for (const name of missing) {
      console.log(`- ${name}`);
    }
    console.log('');
    console.log('The command below is a template; do not run it until placeholders are replaced.');
  }

  const data = JSON.stringify(payloadValue);
  console.log(`curl -i -sS -X POST '${baseUrlValue}/local/rest/tacticum_sale_staff.php' \\`);
  console.log(`  -H 'Origin: ${baseUrlValue}' \\`);
  console.log("  -H 'Content-Type: application/json' \\");
  console.log(`  --data-binary '${shellSingleQuote(data)}'`);
}

function printBrowserSnippet(baseUrlValue, payloadValue) {
  const browserPayload = {
    ...payloadValue,
    sessid: '<bitrix_sessid_from_browser>',
    name: '<controlled_test_name>',
    email: '<controlled_test_email>',
    phone: '<controlled_test_phone>',
  };

  console.log('Paste this into a browser console on the checked host only after replacing controlled contact placeholders.');
  console.log('The snippet sends a POST request and may create a controlled test lead.');
  console.log('');
  console.log(`const tacticumStaffSalePayload = ${JSON.stringify(browserPayload, null, 2)};`);
  console.log(`const tacticumStaffSaleContact = {
  name: '<controlled_test_name>',
  email: '<controlled_test_email>',
  phone: '<controlled_test_phone>'
};

for (const value of Object.values(tacticumStaffSaleContact)) {
  if (/^<.*>$/.test(value)) {
    throw new Error('Replace controlled test contact placeholders before sending the staff-order request.');
  }
}

const tacticumStaffSaleResponse = await fetch('${baseUrlValue}/local/rest/tacticum_sale_staff.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({
    ...tacticumStaffSalePayload,
    sessid: BX.bitrix_sessid(),
    name: tacticumStaffSaleContact.name,
    email: tacticumStaffSaleContact.email,
    phone: tacticumStaffSaleContact.phone
  })
});

let tacticumStaffSaleBody;
try {
  tacticumStaffSaleBody = await tacticumStaffSaleResponse.json();
} catch {
  tacticumStaffSaleBody = {raw: await tacticumStaffSaleResponse.text()};
}

({
  qa_marker: tacticumStaffSalePayload.message.match(/QA marker: ([a-z-]+)/)?.[1] || '',
  status: tacticumStaffSaleResponse.status,
  body: tacticumStaffSaleBody
});`);
}

function requiredCurlEnv() {
  return [
    'TACTICUM_STAFF_TEST_SESSID',
    'TACTICUM_STAFF_TEST_NAME',
    'TACTICUM_STAFF_TEST_EMAIL',
    'TACTICUM_STAFF_TEST_PHONE',
  ];
}

function printSection(title) {
  console.log(title);
  console.log('='.repeat(title.length));
}

function trimTrailingSlash(value) {
  return String(value).replace(/\/+$/, '');
}

function shellSingleQuote(value) {
  return String(value).replace(/'/g, "'\\''");
}

function printUsage() {
  console.error('Usage: npm run staff:sale:gate-helper -- [--payload] [--browser] [--curl] [--evidence] [--all]');
  console.error('');
  console.error('Environment for executable curl output:');
  console.error('  TACTICUM_STAFF_GATE_BASE_URL=https://tacticum.ru');
  console.error('  TACTICUM_STAFF_TEST_SESSID=<bitrix_sessid_from_browser>');
  console.error('  TACTICUM_STAFF_TEST_NAME=<controlled test name>');
  console.error('  TACTICUM_STAFF_TEST_EMAIL=<controlled test email>');
  console.error('  TACTICUM_STAFF_TEST_PHONE=<controlled test phone>');
  console.error('  TACTICUM_STAFF_TEST_MARKER=<optional-safe-letters-only-marker>');
}
