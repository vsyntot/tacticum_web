#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const args = process.argv.slice(2);
const knownOptions = new Set(['--taxonomy', '--source-check', '--browser', '--evidence', '--owner-checklist', '--json', '--all', '--help']);
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
  selectedModes.add('--taxonomy');
  selectedModes.add('--source-check');
  selectedModes.add('--owner-checklist');
  selectedModes.add('--browser');
  selectedModes.add('--evidence');
}

const jsonMode = selectedModes.has('--json') || args.includes('--json');
const counterId = String(process.env.TACTICUM_METRIKA_COUNTER_ID || '103471113');
const baseUrl = trimTrailingSlash(process.env.TACTICUM_METRIKA_BASE_URL || 'https://tacticum.ru');
const taxonomy = buildTaxonomy();
const sourceCheck = selectedModes.has('--source-check') ? await buildSourceCheck(taxonomy, counterId) : null;

const output = {
  counter_id: counterId,
  base_url: baseUrl,
  modes: Array.from(selectedModes).filter((mode) => mode !== '--json').map((mode) => mode.replace(/^--/, '')),
  taxonomy,
  source_check: sourceCheck,
  owner_checklist: selectedModes.has('--owner-checklist') ? buildOwnerChecklist(baseUrl, taxonomy) : null,
  browser_observer: selectedModes.has('--browser') ? buildBrowserObserverSnippet(counterId) : null,
  evidence_template: selectedModes.has('--evidence') ? buildEvidenceTemplate(counterId, baseUrl, taxonomy) : null,
  evidence_rules: [
    'Metrika owner must confirm goals/events in Yandex.Metrika or connected tag manager after checked_at.',
    'Evidence must not include names, phones, emails, message text, raw payloads, raw URLs with query, cookies or sessions.',
    'Allowed params are controlled ids/statuses, page_path, endpoint/form_id/surface, booleans and counters.',
  ],
};

if (jsonMode) {
  console.log(JSON.stringify(output, null, 2));
} else {
  printText(output);
}

function buildTaxonomy() {
  return [
    {
      scenario: 'forms',
      owner_check: 'Default lead/modal/staff forms produce submit and success/error goals.',
      events: [
        event('tacticum_form_submit', 'forms.js', ['form_id', 'endpoint', 'page_path']),
        event('tacticum_form_success', 'forms.js', ['form_id', 'endpoint', 'status', 'page_path']),
        event('tacticum_form_error', 'forms.js', ['form_id', 'endpoint', 'status', 'code', 'page_path']),
        event('tacticum_form_validation_error', 'forms.js', ['form_id', 'page_path']),
      ],
      required_for_gate: ['tacticum_form_submit', 'tacticum_form_success'],
    },
    {
      scenario: 'product-funnel',
      owner_check: 'Product page views, product CTA clicks and product form mirrors use only controlled product/page_role/scenario values.',
      events: [
        event('tacticum_product_view', 'analytics.js', ['product', 'page_role', 'page_path']),
        event('tacticum_product_cta_click', 'analytics.js', ['product', 'page_role', 'cta', 'page_path']),
        event('tacticum_product_form_submit', 'forms.js', ['product', 'page_role', 'scenario', 'form_id', 'endpoint', 'page_path']),
        event('tacticum_product_form_success', 'forms.js', ['product', 'page_role', 'scenario', 'form_id', 'endpoint', 'status', 'page_path']),
        event('tacticum_product_form_error', 'forms.js', ['product', 'page_role', 'scenario', 'form_id', 'endpoint', 'status', 'code', 'page_path']),
      ],
      required_for_gate: ['tacticum_product_view', 'tacticum_product_cta_click', 'tacticum_product_form_submit'],
    },
    {
      scenario: 'ai-chat',
      owner_check: 'AI chat produces send and success/error goals; params contain only surface/status/code and boolean flags.',
      events: [
        event('tacticum_chat_send', 'chat-agent.js', ['surface', 'page_path']),
        event('tacticum_chat_success', 'chat-agent.js', ['surface', 'status', 'has_group_id', 'has_offer_url', 'page_path']),
        event('tacticum_chat_error', 'chat-agent.js', ['surface', 'status', 'code', 'page_path']),
        event('tacticum_chat_lead_handoff', 'chat-agent.js', ['surface', 'has_group_id', 'has_prefill_summary', 'page_path']),
      ],
      required_for_gate: ['tacticum_chat_send', 'tacticum_chat_success'],
    },
    {
      scenario: 'prefill',
      owner_check: 'Prefill produces submit and success/error goals; controlled empty group can produce expected error.',
      events: [
        event('tacticum_prefill_submit', 'chat-agent.js', ['surface', 'page_path']),
        event('tacticum_prefill_success', 'chat-agent.js', ['surface', 'status', 'page_path']),
        event('tacticum_prefill_error', 'chat-agent.js', ['surface', 'status', 'code', 'page_path']),
      ],
      required_for_gate: ['tacticum_prefill_submit', 'tacticum_prefill_success'],
    },
    {
      scenario: 'telegram-resolver',
      owner_check: 'Required only when Telegram resolver/footer/social links changed.',
      events: [
        event('tacticum_tg_resolver_success', 'tg-link-resolver.js', ['status', 'links_count', 'page_path']),
        event('tacticum_tg_resolver_error', 'tg-link-resolver.js', ['status', 'code', 'page_path']),
        event('tacticum_tg_resolver_skip', 'tg-link-resolver.js', ['status', 'code', 'page_path']),
      ],
      required_for_gate: [],
    },
  ];
}

function event(name, source, params) {
  return { name, source, params };
}

async function buildSourceCheck(taxonomyValue, counterIdValue) {
  const sourceFiles = {
    'analytics.js': await readOptional('local/templates/tacticum/js/analytics.js'),
    'forms.js': await readOptional('local/templates/tacticum/js/forms.js'),
    'chat-agent.js': await readOptional('local/templates/tacticum/js/chat-agent.js'),
    'tg-link-resolver.js': await readOptional('local/templates/tacticum/js/tg-link-resolver.js'),
    'metrika.js': await readOptional('local/templates/tacticum/js/metrika.js'),
  };

  const eventCoverage = [];
  for (const group of taxonomyValue) {
    for (const item of group.events) {
      const source = sourceFiles[item.source] || '';
      eventCoverage.push({
        event: item.name,
        source: item.source,
        present: source.includes(`"${item.name}"`) || source.includes(`'${item.name}'`),
      });
    }
  }

  const analyticsSource = sourceFiles['analytics.js'] || '';
  const metrikaSource = sourceFiles['metrika.js'] || '';

  return {
    counter_id_present_in_analytics: analyticsSource.includes(counterIdValue),
    counter_id_present_in_metrika_asset: metrikaSource.includes(counterIdValue),
    reach_goal_present: analyticsSource.includes('reachGoal'),
    qa_browser_event_present: analyticsSource.includes('tacticum:analytics'),
    metrika_asset_uses_https_tag: metrikaSource.includes('https://mc.yandex.ru/metrika/tag.js'),
    event_coverage: eventCoverage,
    missing_events: eventCoverage.filter((item) => !item.present).map((item) => item.event),
  };
}

async function readOptional(path) {
  try {
    return await readFile(path, 'utf8');
  } catch {
    return '';
  }
}

function buildEvidenceTemplate(counterIdValue, baseUrlValue, taxonomyValue) {
  const requiredGoals = Array.from(new Set(taxonomyValue.flatMap((group) => group.required_for_gate)));
  return {
    counter_id: counterIdValue,
    checked_at: '<YYYY-MM-DDTHH:mm:ss+03:00>',
    checked_by: '<owner>',
    observed_after: '<YYYY-MM-DDTHH:mm:ss+03:00>',
    goals: requiredGoals,
    goal_observations: requiredGoals.map((goal) => ({
      goal,
      status: 'observed',
      params_safe: true,
    })),
    checked_markers: {
      manual_success_flow: '<safe-manual-qa-marker-or-not_applicable>',
      staff_sale_upstream: '<safe-staff-qa-marker-or-not_applicable>',
    },
    staff_order_goal_note: 'price-specialist observed through tacticum_form_success; use form_id=price-specialist in Metrika/tag-manager evidence',
    pii_check: 'goal params contain no name, phone, email, message, summary, raw payload, cookie/session material or raw URL query',
    checked_urls: [
      `${baseUrlValue}/`,
      `${baseUrlValue}/price/`,
      `${baseUrlValue}/calculator/`,
      `${baseUrlValue}/platform/`,
      `${baseUrlValue}/agents/`,
      `${baseUrlValue}/dev/`,
      `${baseUrlValue}/forum/`,
    ],
    external_evidence: '<safe-internal-ticket-or-screenshot-link>',
  };
}

function buildOwnerChecklist(baseUrlValue, taxonomyValue) {
  const requiredGoals = Array.from(new Set(taxonomyValue.flatMap((group) => group.required_for_gate)));
  return {
    counter_id: '103471113',
    where_to_check: 'Yandex.Metrika counter 103471113 or connected tag manager goal/event evidence view',
    checked_urls: [
      `${baseUrlValue}/price/`,
      `${baseUrlValue}/`,
      `${baseUrlValue}/calculator/`,
      `${baseUrlValue}/platform/`,
      `${baseUrlValue}/agents/`,
      `${baseUrlValue}/dev/`,
      `${baseUrlValue}/forum/`,
    ],
    required_goals: requiredGoals,
    acceptable_flow_results: [
      'forms: tacticum_form_submit and tacticum_form_success for controlled default/modal/staff forms',
      'product funnel: tacticum_product_view, tacticum_product_cta_click and tacticum_product_form_submit for product pages/CTA',
      'ai chat: tacticum_chat_send and tacticum_chat_success for controlled AI chat response, or tacticum_chat_error only if the controlled flow failed as expected and is documented',
      'prefill: tacticum_prefill_success for real group_id prefill, or tacticum_prefill_error with code/status for controlled empty group_id',
    ],
    safe_params_allowlist: [
      'page_path',
      'form_id',
      'endpoint',
      'surface',
      'status',
      'code',
      'product',
      'page_role',
      'scenario',
      'cta',
      'has_group_id',
      'has_offer_url',
      'has_prefill_summary',
    ],
    forbidden_params: [
      'name',
      'phone',
      'email',
      'message',
      'summary',
      'payload',
      'raw URL query',
      'cookie/session material',
      'token/secret',
    ],
    transfer_back: [
      'counter_id',
      'checked_at',
      'checked_by',
      'observed_after',
      'goals list',
      'goal_observations with observed/status and params_safe=true',
      'safe internal evidence link/id if screenshots are stored outside repo',
    ],
  };
}

function buildBrowserObserverSnippet(counterIdValue) {
  return `(() => {
  const counterId = '${counterIdValue}';
  const forbiddenKeys = /(?:name|phone|email|message|summary|payload|cookie|session|sessid|token|secret|password)/i;
  const emailPattern = /[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}/i;
  const phonePattern = /(?:\\+?\\d[\\d ()-]{8,}\\d)/;
  const evidence = {
    counter_id: counterId,
    started_at: new Date().toISOString(),
    events: [],
    pii_findings: []
  };

  window.__tacticumMetrikaEvidence = evidence;
  window.addEventListener('tacticum:analytics', (event) => {
    const detail = event.detail || {};
    const params = detail.params || {};
    const record = {
      event: detail.event || '',
      params,
      observed_at: new Date().toISOString()
    };

    for (const [key, value] of Object.entries(params)) {
      const stringValue = String(value || '');
      if (forbiddenKeys.test(key) || emailPattern.test(stringValue) || phonePattern.test(stringValue)) {
        evidence.pii_findings.push({event: record.event, key, value: stringValue});
      }
    }

    evidence.events.push(record);
    console.table(evidence.events.map((item) => ({event: item.event, params: JSON.stringify(item.params)})));
    if (evidence.pii_findings.length > 0) {
      console.warn('Potential PII findings in analytics params:', evidence.pii_findings);
    }
  });

  console.log('Tacticum Metrika observer installed. Run owner-controlled flows, then inspect window.__tacticumMetrikaEvidence.');
})();`;
}

function printText(outputValue) {
  console.log('Metrika Goals Helper');
  console.log('====================');
  console.log(`Counter ID: ${outputValue.counter_id}`);
  console.log(`Base URL: ${outputValue.base_url}`);

  if (outputValue.taxonomy && selectedModes.has('--taxonomy')) {
    printSection('Expected Goals / Events');
    for (const group of outputValue.taxonomy) {
      console.log(`${group.scenario}: ${group.owner_check}`);
      for (const item of group.events) {
        console.log(`- ${item.name} | source=${item.source} | params=${item.params.join(', ')}`);
      }
      if (group.required_for_gate.length > 0) {
        console.log(`  Gate focus: ${group.required_for_gate.join(', ')}`);
      }
      console.log('');
    }
  }

  if (outputValue.source_check) {
    printSection('Source Check');
    console.log(`counter_id_present_in_analytics: ${outputValue.source_check.counter_id_present_in_analytics}`);
    console.log(`counter_id_present_in_metrika_asset: ${outputValue.source_check.counter_id_present_in_metrika_asset}`);
    console.log(`reach_goal_present: ${outputValue.source_check.reach_goal_present}`);
    console.log(`qa_browser_event_present: ${outputValue.source_check.qa_browser_event_present}`);
    console.log(`metrika_asset_uses_https_tag: ${outputValue.source_check.metrika_asset_uses_https_tag}`);
    console.log(`missing_events: ${outputValue.source_check.missing_events.length > 0 ? outputValue.source_check.missing_events.join(', ') : '-'}`);
  }

  if (outputValue.owner_checklist) {
    printSection('Owner Checklist');
    console.log(JSON.stringify(outputValue.owner_checklist, null, 2));
  }

  if (outputValue.browser_observer) {
    printSection('Browser Observer Snippet');
    console.log('Paste this into a browser console before running owner-controlled flows.');
    console.log('It observes local tacticum:analytics events and checks params for obvious PII patterns; it does not prove Yandex.Metrika UI receipt.');
    console.log('');
    console.log(outputValue.browser_observer);
  }

  if (outputValue.evidence_template) {
    printSection('Metrika Goals Evidence Template');
    console.log(JSON.stringify(outputValue.evidence_template, null, 2));
  }

  printSection('Evidence Rules');
  for (const rule of outputValue.evidence_rules) {
    console.log(`- ${rule}`);
  }
}

function printSection(title) {
  console.log('');
  console.log(title);
  console.log('='.repeat(title.length));
}

function trimTrailingSlash(value) {
  return String(value || '').replace(/\/+$/, '');
}

function printUsage() {
  console.error('Usage: npm run metrika:goals:helper -- [--taxonomy] [--source-check] [--owner-checklist] [--browser] [--evidence] [--json] [--all]');
  console.error('');
  console.error('Environment:');
  console.error('  TACTICUM_METRIKA_COUNTER_ID=103471113');
  console.error('  TACTICUM_METRIKA_BASE_URL=https://tacticum.ru');
}
