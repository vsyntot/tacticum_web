#!/usr/bin/env node

const args = process.argv.slice(2);
const knownOptions = new Set(['--payloads', '--browser', '--curl', '--evidence', '--all', '--help']);
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
  selectedModes.add('--payloads');
  selectedModes.add('--browser');
  selectedModes.add('--curl');
  selectedModes.add('--evidence');
}

const baseUrl = trimTrailingSlash(process.env.TACTICUM_MANUAL_FLOW_BASE_URL || 'https://tacticum.ru');
const payloads = buildPayloads(baseUrl);

if (selectedModes.has('--payloads')) {
  printSection('Controlled Manual Success-Flow Payloads');
  console.log(JSON.stringify(payloads, null, 2));
}

if (selectedModes.has('--browser')) {
  printSection('Controlled Browser Snippet');
  printBrowserSnippet(baseUrl, payloads);
}

if (selectedModes.has('--curl')) {
  printSection('Controlled Curl Templates');
  printCurlTemplates(baseUrl, payloads);
}

if (selectedModes.has('--evidence')) {
  printSection('Manual Success-Flow Evidence Template');
  console.log(JSON.stringify(buildEvidenceTemplate(baseUrl), null, 2));
}

function buildPayloads(baseUrlValue) {
  return {
    default_lead_form: {
      endpoint: '/local/rest/tacticum_form.php',
      expected_result: 'HTTP 200 success=true and upstream/CRM safe lead ID',
      payload: {
        sessid: '<bitrix_sessid_from_browser>',
        name: '<controlled_test_name>',
        company: 'Tacticum QA controlled test',
        email: '<controlled_test_email>',
        phone: '<controlled_test_phone>',
        message: 'Controlled default lead smoke. Test lead; no commercial processing.',
        form_id: 'price-cta',
        page_url: `${baseUrlValue}/price/`,
        lead_product: 'ecosystem',
        lead_scenario: 'product-routing',
        lead_intent: 'manual-success-flow-smoke',
      },
    },
    modal_form: {
      endpoint: '/local/rest/tacticum_form.php',
      expected_result: 'HTTP 200 success=true and upstream/CRM safe lead ID',
      payload: {
        sessid: '<bitrix_sessid_from_browser>',
        name: '<controlled_test_name>',
        company: 'Tacticum QA controlled test',
        email: '<controlled_test_email>',
        phone: '<controlled_test_phone>',
        message: 'Controlled modal form smoke. Test lead; no commercial processing.',
        form_id: 'contact-modal',
        page_url: `${baseUrlValue}/`,
        lead_product: 'ecosystem',
        lead_scenario: 'contact-routing',
        lead_intent: 'manual-success-flow-smoke',
      },
    },
    ai_chat: {
      endpoint: '/local/rest/tacticum_chat.php',
      expected_result: 'HTTP 200 with controlled AI response; no raw stack or PII',
      payload: {
        sessid: '<bitrix_sessid_from_browser>',
        user_message: 'Controlled AI chat smoke. Please return a short qualification response for a test lead; no commercial processing.',
        startAgent: 'SalesConsultantAgent',
      },
    },
    prefill_controlled_empty: {
      endpoint: '/local/rest/tacticum_prefill.php',
      expected_result: 'HTTP 404 code=not_found is acceptable for controlled empty group_id; a real AI group_id can be checked separately',
      payload: {
        sessid: '<bitrix_sessid_from_browser>',
        group_id: 'controlled-empty-manual-success-flow',
      },
    },
  };
}

function buildEvidenceTemplate(baseUrlValue) {
  return {
    environment: 'controlled-production',
    checked_at: '<YYYY-MM-DDTHH:mm:ss+03:00>',
    checked_by: '<owner>',
    flows: [
      {
        flow: 'default-lead-form',
        url: `${baseUrlValue}/price/`,
        form_id: 'price-cta',
        result: 'success=true and UI success state; upstream/CRM accepted safe test lead',
        lead_id: '<safe-lead-id>',
      },
      {
        flow: 'modal-form',
        url: `${baseUrlValue}/`,
        form_id: 'contact-modal',
        result: 'success=true and modal success state; upstream/CRM accepted safe test lead',
        lead_id: '<safe-lead-id>',
      },
      {
        flow: 'ai-chat',
        url: `${baseUrlValue}/calculator/`,
        result: 'controlled response without raw stack or PII',
        masked_group_id: '<masked-group-id-or-not_applicable>',
      },
      {
        flow: 'prefill',
        url: `${baseUrlValue}/offer/`,
        result: 'expected prefill or controlled empty state',
        masked_group_id: '<masked-group-id-or-not_applicable>',
      },
      {
        flow: 'staff-order',
        url: `${baseUrlValue}/price/`,
        form_id: 'price-specialist',
        result: 'see staff-sale-upstream evidence; staff-order endpoint accepted controlled payload',
        lead_id: '<safe-lead-id-or-upstream-request-id>',
      },
    ],
  };
}

function printBrowserSnippet(baseUrlValue, payloadsValue) {
  const snippetPayloads = Object.fromEntries(Object.entries(payloadsValue).map(([key, flow]) => [
    key,
    {
      endpoint: `${baseUrlValue}${flow.endpoint}`,
      expected_result: flow.expected_result,
      payload: flow.payload,
    },
  ]));

  console.log('Paste this into a browser console on the checked host only after replacing controlled contact placeholders.');
  console.log('The snippet sends POST requests and may create controlled test leads.');
  console.log('');
  console.log(`const tacticumManualSuccessFlowPayloads = ${JSON.stringify(snippetPayloads, null, 2)};`);
  console.log(`const tacticumManualSuccessFlowContact = {
  name: '<controlled_test_name>',
  email: '<controlled_test_email>',
  phone: '<controlled_test_phone>'
};

for (const value of Object.values(tacticumManualSuccessFlowContact)) {
  if (/^<.*>$/.test(value)) {
    throw new Error('Replace controlled test contact placeholders before sending requests.');
  }
}

async function tacticumManualSuccessFlowPost(flowName, flow) {
  const payload = {
    ...flow.payload,
    sessid: BX.bitrix_sessid(),
    name: flow.payload.name ? tacticumManualSuccessFlowContact.name : flow.payload.name,
    email: flow.payload.email ? tacticumManualSuccessFlowContact.email : flow.payload.email,
    phone: flow.payload.phone ? tacticumManualSuccessFlowContact.phone : flow.payload.phone
  };

  const response = await fetch(flow.endpoint, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(payload)
  });

  let body;
  try {
    body = await response.json();
  } catch {
    body = {raw: await response.text()};
  }

  return {
    flow: flowName,
    status: response.status,
    expected_result: flow.expected_result,
    body
  };
}

const tacticumManualSuccessFlowResults = [];
for (const [flowName, flow] of Object.entries(tacticumManualSuccessFlowPayloads)) {
  tacticumManualSuccessFlowResults.push(await tacticumManualSuccessFlowPost(flowName, flow));
}
tacticumManualSuccessFlowResults;`);
}

function printCurlTemplates(baseUrlValue, payloadsValue) {
  const missing = requiredCurlEnv().filter((name) => !String(process.env[name] || '').trim());
  if (missing.length > 0) {
    console.log('Set these environment variables before using executable curl commands:');
    for (const name of missing) {
      console.log(`- ${name}`);
    }
    console.log('');
    console.log('The commands below are templates; do not run them until placeholders are replaced.');
  }

  for (const [flowName, flow] of Object.entries(payloadsValue)) {
    const payload = {
      ...flow.payload,
      sessid: process.env.TACTICUM_MANUAL_FLOW_TEST_SESSID || '<bitrix_sessid_from_browser>',
    };
    if (payload.name) {
      payload.name = process.env.TACTICUM_MANUAL_FLOW_TEST_NAME || '<controlled_test_name>';
    }
    if (payload.email) {
      payload.email = process.env.TACTICUM_MANUAL_FLOW_TEST_EMAIL || '<controlled_test_email>';
    }
    if (payload.phone) {
      payload.phone = process.env.TACTICUM_MANUAL_FLOW_TEST_PHONE || '<controlled_test_phone>';
    }

    console.log('');
    console.log(`# ${flowName}: ${flow.expected_result}`);
    console.log(`curl -i -sS -X POST '${baseUrlValue}${flow.endpoint}' \\`);
    console.log(`  -H 'Origin: ${baseUrlValue}' \\`);
    console.log("  -H 'Content-Type: application/json' \\");
    console.log(`  --data-binary '${shellSingleQuote(JSON.stringify(payload))}'`);
  }
}

function requiredCurlEnv() {
  return [
    'TACTICUM_MANUAL_FLOW_TEST_SESSID',
    'TACTICUM_MANUAL_FLOW_TEST_NAME',
    'TACTICUM_MANUAL_FLOW_TEST_EMAIL',
    'TACTICUM_MANUAL_FLOW_TEST_PHONE',
  ];
}

function printSection(title) {
  console.log(title);
  console.log('='.repeat(title.length));
}

function trimTrailingSlash(value) {
  return String(value || '').replace(/\/+$/, '');
}

function shellSingleQuote(value) {
  return String(value).replace(/'/g, "'\\''");
}

function printUsage() {
  console.error('Usage: npm run manual:success-flow:helper -- [--payloads] [--browser] [--curl] [--evidence] [--all]');
  console.error('');
  console.error('Environment for executable curl output:');
  console.error('  TACTICUM_MANUAL_FLOW_BASE_URL=https://tacticum.ru');
  console.error('  TACTICUM_MANUAL_FLOW_TEST_SESSID=<bitrix_sessid_from_browser>');
  console.error('  TACTICUM_MANUAL_FLOW_TEST_NAME=<controlled test name>');
  console.error('  TACTICUM_MANUAL_FLOW_TEST_EMAIL=<controlled test email>');
  console.error('  TACTICUM_MANUAL_FLOW_TEST_PHONE=<controlled test phone>');
}
