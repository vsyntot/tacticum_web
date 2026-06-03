#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const manualGateNames = [
  'manual-success-flow',
  'metrika-goals',
  'bitrix-admin',
  'staff-sale-upstream',
];

const defaultSignoffPath = process.env.TACTICUM_RELEASE_SIGNOFF
  || 'docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json';

const options = parseArgs(process.argv.slice(2));

if (options.help) {
  printUsage();
  process.exit(0);
}

let signoff;
let sourceFileAvailable = true;
try {
  signoff = JSON.parse(await readFile(options.file, 'utf8'));
} catch (error) {
  const explicitSignoffPath = options.fileSet || Boolean(process.env.TACTICUM_RELEASE_SIGNOFF);
  if (!isMissingFileError(error) || explicitSignoffPath) {
    console.error(`Cannot read release sign-off file: ${options.file}`);
    console.error(error instanceof Error ? error.message : String(error));
    process.exit(1);
  }

  sourceFileAvailable = false;
  signoff = buildStandaloneSignoff(options.baseUrl || 'https://tacticum.ru');
}

const release = signoff.release || {};
const baseUrl = trimTrailingSlash(options.baseUrl || release.base_url || 'https://tacticum.ru');
const gateFilter = new Set(options.gates);
const allManualGates = manualGateNames
  .map((name) => buildGateSummary(name, signoff.gates?.[name], baseUrl))
  .filter((gate) => gateFilter.size === 0 || gateFilter.has(gate.name));

const selectedGates = options.all
  ? allManualGates
  : allManualGates.filter((gate) => gate.status === 'pending');

const payload = {
  source_file: options.file,
  source_file_available: sourceFileAvailable,
  source_file_note: sourceFileAvailable
    ? 'release sign-off draft was loaded'
    : 'release sign-off draft was not found; helper is running in standalone skeleton mode',
  release: {
    id: release.id || 'unknown',
    date: release.date || 'unknown',
    base_url: baseUrl,
  },
  mode: options.all ? 'all-manual-gates' : 'pending-manual-gates',
  manual_gates: selectedGates,
  closing_commands_note: sourceFileAvailable
    ? 'Run after updating this release sign-off JSON.'
    : 'Transfer safe evidence to the repository sign-off JSON first; run these commands in the repository where docs/ is available.',
  closing_commands: buildClosingCommands(options.file),
  evidence_rules: [
    'Do not store name, phone, email, message text, full payload, cookies, sessions, tokens or raw upstream response.',
    'Use ISO datetime with timezone, for example 2026-06-03T12:30:00+03:00.',
    'Leave a gate pending until owner evidence exists; do not synthesize external access evidence.',
  ],
};

if (options.json) {
  console.log(JSON.stringify(payload, null, 2));
} else {
  printText(payload);
}

function parseArgs(args) {
  const parsed = {
    all: false,
    baseUrl: process.env.TACTICUM_RELEASE_BASE_URL || '',
    file: defaultSignoffPath,
    fileSet: false,
    gates: [],
    help: false,
    json: false,
  };

  for (let index = 0; index < args.length; index += 1) {
    const arg = args[index];
    if (arg === '--help') {
      parsed.help = true;
      continue;
    }
    if (arg === '--all') {
      parsed.all = true;
      continue;
    }
    if (arg === '--json') {
      parsed.json = true;
      continue;
    }
    if (arg === '--gate') {
      const value = args[index + 1];
      if (!value || value.startsWith('--')) {
        die('Missing value for --gate');
      }
      parsed.gates.push(value);
      index += 1;
      continue;
    }
    if (arg.startsWith('--gate=')) {
      parsed.gates.push(arg.slice('--gate='.length));
      continue;
    }
    if (arg === '--base-url') {
      const value = args[index + 1];
      if (!value || value.startsWith('--')) {
        die('Missing value for --base-url');
      }
      parsed.baseUrl = value;
      index += 1;
      continue;
    }
    if (arg.startsWith('--base-url=')) {
      parsed.baseUrl = arg.slice('--base-url='.length);
      continue;
    }
    if (arg.startsWith('--')) {
      die(`Unknown option: ${arg}`);
    }
    if (parsed.fileSet) {
      die(`Unexpected positional argument: ${arg}`);
    }
    parsed.file = arg;
    parsed.fileSet = true;
  }

  for (const gate of parsed.gates) {
    if (!manualGateNames.includes(gate)) {
      die(`Unknown manual gate: ${gate}`);
    }
  }

  return parsed;
}

function buildGateSummary(name, gate, baseUrl) {
  const evidence = gate?.evidence && typeof gate.evidence === 'object' && !Array.isArray(gate.evidence)
    ? gate.evidence
    : {};

  return {
    name,
    status: String(gate?.status || 'missing'),
    owner: String(gate?.owner || defaultOwner(name)),
    due: String(gate?.due || ''),
    reason: String(gate?.reason || ''),
    runbook: String(evidence.runbook || `docs/workflow/manual-release-gates-runbook.md#gate-${name}`),
    evidence_template: String(evidence.evidence_template || 'docs/workflow/release-signoff-manual-evidence.template.json'),
    current_safe_summary: safeCurrentSummary(evidence),
    next_actions: nextActions(name),
    commands: gateCommands(name),
    evidence_skeleton: evidenceSkeleton(name, baseUrl),
  };
}

function buildStandaloneSignoff(baseUrlValue) {
  const baseUrl = trimTrailingSlash(baseUrlValue || 'https://tacticum.ru');
  return {
    release: {
      id: 'manual-gates-standalone',
      date: isoDate(),
      base_url: baseUrl,
    },
    gates: Object.fromEntries(manualGateNames.map((name) => [
      name,
      {
        status: 'pending',
        owner: defaultOwner(name),
        due: 'before-strict-release-closure',
        reason: `Default release sign-off draft is not available on this host; collect safe owner evidence for ${name} and transfer it to the repository sign-off JSON.`,
        evidence: {},
      },
    ])),
  };
}

function safeCurrentSummary(evidence) {
  const keys = [
    'safe_summary',
    'browser_controlled_staff_order',
    'public_precheck',
    'controlled_smoke',
  ];
  return keys
    .filter((key) => typeof evidence[key] === 'string' && evidence[key].trim())
    .map((key) => `${key}: ${evidence[key].trim()}`);
}

function nextActions(name) {
  const actions = {
    'manual-success-flow': [
      'Run controlled staging or controlled-production checks for default form, modal form, AI chat, prefill and staff-order.',
      'Confirm UI success or expected controlled error state and upstream/CRM receipt where the flow creates a lead.',
      'Record only safe lead/request IDs, flow names, URLs, checked_at, checked_by and short result text.',
    ],
    'metrika-goals': [
      'Open Yandex.Metrika counter 103471113 or the connected tag manager evidence view.',
      'Confirm affected form, chat, prefill and staff-order goals after the checked_at window.',
      'Confirm goal params contain no name, phone, email, message, raw payload or raw URL query.',
    ],
    'bitrix-admin': [
      'Use the read-only Bitrix admin helper to collect checklist format and safe evidence skeleton.',
      'Sign in to /bitrix/admin/ with admin or content-admin role.',
      'Confirm admin panel opens without 500 or white screen after deploy/cache refresh.',
      'Open a public page with Bitrix toolbar enabled and confirm template/assets do not break it.',
    ],
    'staff-sale-upstream': [
      'Use the controlled /price/ staff-order payload or an equivalent staging payload.',
      'Confirm upstream/CRM received team summary, team_preset, workers count, monthly budget and exact end date.',
      'Record only safe upstream_request_id or lead_id plus contract booleans required by the checker.',
    ],
  };

  return actions[name] || [];
}

function gateCommands(name) {
  const commands = {
    'manual-success-flow': [
      'npm run release:manual-gates:helper -- --gate manual-success-flow',
      'npm run manual:success-flow:helper -- --payloads --browser --curl --evidence',
      'npm run staff:sale:gate-helper -- --payload --curl --evidence',
    ],
    'metrika-goals': [
      'npm run release:manual-gates:helper -- --gate metrika-goals',
      'npm run metrika:goals:helper -- --taxonomy --source-check --browser --evidence',
    ],
    'bitrix-admin': [
      'npm run release:public-precheck:prod',
      'npm run bitrix:admin:gate-helper -- --checklist --browser --evidence',
      'npm run release:manual-gates:helper -- --gate bitrix-admin',
    ],
    'staff-sale-upstream': [
      'npm run staff:sale:gate-helper -- --payload --curl --evidence',
      'npm run release:manual-gates:helper -- --gate staff-sale-upstream',
    ],
  };

  return commands[name] || [];
}

function evidenceSkeleton(name, baseUrl) {
  const skeletons = {
    'manual-success-flow': {
      environment: 'controlled-production',
      checked_at: '<YYYY-MM-DDTHH:mm:ss+03:00>',
      checked_by: 'replace-with-owner',
      flows: [
        {
          flow: 'default-lead-form',
          url: `${baseUrl}/price/`,
          form_id: 'price-cta',
          result: 'UI success state, backend success=true and upstream/CRM accepted safe test lead',
          lead_id: 'replace-with-safe-lead-id',
        },
        {
          flow: 'modal-form',
          url: `${baseUrl}/`,
          form_id: 'contact-modal',
          result: 'Modal opens, valid submit reaches success state and upstream/CRM accepted safe test lead',
          lead_id: 'replace-with-safe-lead-id',
        },
        {
          flow: 'ai-chat',
          url: `${baseUrl}/calculator/`,
          result: 'Controlled response without raw stack or PII',
          masked_group_id: 'replace-with-masked-group-id-or-not_applicable',
        },
        {
          flow: 'prefill',
          url: `${baseUrl}/offer/`,
          result: 'Expected prefill or controlled empty state; manual submit remains available',
          masked_group_id: 'replace-with-masked-group-id-or-not_applicable',
        },
        {
          flow: 'staff-order',
          url: `${baseUrl}/price/`,
          form_id: 'price-specialist',
          result: 'Staff-order endpoint accepted controlled payload and upstream/CRM accepted team summary',
          lead_id: 'replace-with-safe-lead-id',
        },
      ],
    },
    'metrika-goals': {
      counter_id: '103471113',
      checked_at: '<YYYY-MM-DDTHH:mm:ss+03:00>',
      checked_by: 'replace-with-owner',
      goals: [
        'tacticum_form_submit',
        'tacticum_form_success',
        'tacticum_product_view',
        'tacticum_product_cta_click',
        'tacticum_product_form_submit',
        'tacticum_chat_send',
        'tacticum_chat_success',
        'tacticum_prefill_submit',
        'tacticum_prefill_success',
      ],
      staff_order_goal_note: 'price-specialist observed through tacticum_form_success',
      pii_check: 'goal params contain no name, phone, email, message, raw payload or raw URL query',
      external_evidence: 'replace-with-safe-internal-ticket-or-screenshot-link',
    },
    'bitrix-admin': {
      checked_at: '<YYYY-MM-DDTHH:mm:ss+03:00>',
      checked_by: 'replace-with-owner',
      admin_url: `${baseUrl}/bitrix/admin/`,
      role: 'replace-with-admin-or-content-admin-role',
      public_toolbar_url: `${baseUrl}/price/`,
      result: 'admin panel and public toolbar open without 500 or white screen',
      cache_note: 'deploy/cache refresh did not break admin panel or public toolbar',
      external_evidence: 'replace-with-safe-internal-ticket-or-screenshot-link',
    },
    'staff-sale-upstream': {
      environment: 'controlled-production',
      checked_at: '<YYYY-MM-DDTHH:mm:ss+03:00>',
      checked_by: 'replace-with-owner',
      health_config: 'success=true; scopes include ai, rest, security',
      url: `${baseUrl}/price/`,
      form_id: 'price-specialist',
      team_preset: 'mvp',
      workers_count: 3,
      monthly_budget_estimate_present: true,
      end_date_present: true,
      upstream_request_id: 'replace-with-safe-upstream-id-or-lead-id',
      result: 'upstream/CRM received team summary, team_preset, monthly_budget_estimate and end_date',
    },
  };

  return skeletons[name] || {};
}

function buildClosingCommands(file) {
  return [
    `npm run release:signoff:draft-check -- ${file}`,
    `npm run release:signoff:summary -- ${file}`,
    `npm run release:signoff:check -- ${file}`,
  ];
}

function printText(payload) {
  console.log('Manual Release Gates Helper');
  console.log('===========================');
  console.log(`Release: ${payload.release.id} | date=${payload.release.date} | base_url=${payload.release.base_url}`);
  console.log(`Sign-off: ${payload.source_file}`);
  console.log(`Sign-off file: ${payload.source_file_available ? 'loaded' : 'not found; standalone skeleton mode'}`);
  console.log(`Mode: ${payload.mode}`);
  console.log('');

  if (payload.manual_gates.length === 0) {
    console.log('No matching manual gates found in the selected mode.');
    console.log('Use --all to print templates for passed/not_applicable manual gates too.');
  }

  for (const gate of payload.manual_gates) {
    console.log(`Gate: ${gate.name}`);
    console.log('-'.repeat(`Gate: ${gate.name}`.length));
    console.log(`Status: ${gate.status}`);
    console.log(`Owner: ${gate.owner}`);
    if (gate.due) {
      console.log(`Due: ${gate.due}`);
    }
    if (gate.reason) {
      console.log(`Reason: ${gate.reason}`);
    }
    console.log(`Runbook: ${gate.runbook}`);
    console.log(`Evidence template: ${gate.evidence_template}`);
    if (gate.current_safe_summary.length > 0) {
      console.log('Current safe summary:');
      for (const item of gate.current_safe_summary) {
        console.log(`- ${item}`);
      }
    }
    console.log('Next actions:');
    for (const action of gate.next_actions) {
      console.log(`- ${action}`);
    }
    if (gate.commands.length > 0) {
      console.log('Useful commands:');
      for (const command of gate.commands) {
        console.log(`- ${command}`);
      }
    }
    console.log('Evidence skeleton:');
    console.log(JSON.stringify(gate.evidence_skeleton, null, 2));
    console.log('');
  }

  console.log('Evidence rules:');
  for (const rule of payload.evidence_rules) {
    console.log(`- ${rule}`);
  }
  console.log('');
  console.log(payload.source_file_available
    ? 'After updating release sign-off JSON:'
    : 'After transferring safe evidence to the repository sign-off JSON:');
  console.log(payload.closing_commands_note);
  for (const command of payload.closing_commands) {
    console.log(`- ${command}`);
  }
}

function defaultOwner(name) {
  const owners = {
    'manual-success-flow': 'QA + Backend/Frontend',
    'metrika-goals': 'PM/Marketing + QA',
    'bitrix-admin': 'QA/Admin',
    'staff-sale-upstream': 'Architect + Backend + QA + DevOps',
  };

  return owners[name] || '';
}

function trimTrailingSlash(value) {
  return String(value || '').replace(/\/+$/, '');
}

function isoDate() {
  return new Date().toISOString().slice(0, 10);
}

function isMissingFileError(error) {
  return error && typeof error === 'object' && error.code === 'ENOENT';
}

function die(message) {
  console.error(message);
  printUsage();
  process.exit(1);
}

function printUsage() {
  console.error('Usage: npm run release:manual-gates:helper -- [signoff.json] [--all] [--gate <name>] [--json] [--base-url https://tacticum.ru]');
  console.error('');
  console.error('Default sign-off file: docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json');
  console.error('Default mode prints pending manual gates only. Use --all to print every manual gate.');
  console.error('If the default docs/ sign-off file is absent, the helper runs in standalone skeleton mode.');
  console.error(`Manual gates: ${manualGateNames.join(', ')}`);
}
