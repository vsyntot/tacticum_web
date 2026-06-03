#!/usr/bin/env node

const args = process.argv.slice(2);
const knownOptions = new Set(['--checklist', '--browser', '--evidence', '--json', '--all', '--help', '--base-url']);
const parsed = parseArgs(args);

if (parsed.help) {
  printUsage();
  process.exit(0);
}

const selectedModes = new Set(parsed.modes);
if (selectedModes.size === 0 || selectedModes.has('--all')) {
  selectedModes.add('--checklist');
  selectedModes.add('--browser');
  selectedModes.add('--evidence');
}

const jsonMode = selectedModes.has('--json');
const baseUrl = trimTrailingSlash(parsed.baseUrl || process.env.TACTICUM_BITRIX_ADMIN_BASE_URL || 'https://tacticum.ru');

const output = {
  gate: 'bitrix-admin',
  base_url: baseUrl,
  modes: Array.from(selectedModes).filter((mode) => mode !== '--json').map((mode) => mode.replace(/^--/, '')),
  checklist: selectedModes.has('--checklist') ? buildChecklist(baseUrl) : null,
  browser_snippet: selectedModes.has('--browser') ? buildBrowserSnippet() : null,
  evidence_template: selectedModes.has('--evidence') ? buildEvidenceTemplate(baseUrl) : null,
  useful_commands: [
    'npm run release:public-precheck:prod',
    'npm run bitrix:admin:gate-helper -- --checklist --browser --evidence',
    'npm run release:manual-gates:helper -- --gate bitrix-admin',
  ],
  evidence_rules: [
    'Do not store Bitrix login, password, cookies, sessid, session IDs, personal admin data or raw screenshots with unmasked personal data.',
    'Unauthenticated public precheck is useful baseline evidence, but it does not replace authenticated admin and public toolbar smoke.',
    'Record only checked_at, checked_by, role, safe URLs, short result text and safe internal ticket/screenshot links.',
  ],
};

if (jsonMode) {
  console.log(JSON.stringify(output, null, 2));
} else {
  printText(output);
}

function parseArgs(values) {
  const parsedArgs = {
    baseUrl: '',
    help: false,
    modes: [],
  };

  for (let index = 0; index < values.length; index += 1) {
    const arg = values[index];
    if (arg === '--help') {
      parsedArgs.help = true;
      continue;
    }
    if (arg === '--base-url') {
      const value = values[index + 1];
      if (!value || value.startsWith('--')) {
        die('Missing value for --base-url');
      }
      parsedArgs.baseUrl = value;
      index += 1;
      continue;
    }
    if (arg.startsWith('--base-url=')) {
      parsedArgs.baseUrl = arg.slice('--base-url='.length);
      continue;
    }
    if (arg.startsWith('--')) {
      if (!knownOptions.has(arg)) {
        die(`Unknown option: ${arg}`);
      }
      parsedArgs.modes.push(arg);
      continue;
    }
    die(`Unexpected positional argument: ${arg}`);
  }

  return parsedArgs;
}

function buildChecklist(baseUrlValue) {
  return [
    {
      step: 'public-baseline',
      action: 'Run npm run release:public-precheck:prod or confirm existing current-production precheck.',
      pass_condition: 'public /bitrix/admin/ surface has no 5xx or white screen; this is baseline only.',
    },
    {
      step: 'authenticated-admin-login',
      action: `Open ${baseUrlValue}/bitrix/admin/ with admin or content-admin role.`,
      pass_condition: 'Bitrix admin panel opens without 500, white screen, broken CSS or broken admin navigation.',
    },
    {
      step: 'public-toolbar',
      action: `Open ${baseUrlValue}/price/ with Bitrix public toolbar enabled.`,
      pass_condition: 'Toolbar renders and public template/header/assets do not break toolbar controls or page layout.',
    },
    {
      step: 'content-admin-surface',
      action: 'Open an affected content/admin surface, for example product iblocks or public page edit mode if this release changed content ownership.',
      pass_condition: 'Admin lists/forms render; no stale asset/cache artifact blocks editing or navigation.',
    },
    {
      step: 'cache-refresh-sanity',
      action: 'Confirm deploy/cache refresh has completed, or note the allowed cache operation that was performed by DevOps.',
      pass_condition: 'Admin and public toolbar remain stable after cache refresh.',
    },
  ];
}

function buildEvidenceTemplate(baseUrlValue) {
  return {
    checked_at: '<YYYY-MM-DDTHH:mm:ss+03:00>',
    checked_by: '<owner>',
    admin_url: `${baseUrlValue}/bitrix/admin/`,
    role: '<admin-or-content-admin>',
    public_toolbar_url: `${baseUrlValue}/price/`,
    result: 'admin panel and public toolbar open without 500 or white screen',
    cache_note: 'deploy/cache refresh did not break admin panel or public toolbar',
    external_evidence: '<safe-internal-ticket-or-screenshot-link>',
  };
}

function buildBrowserSnippet() {
  return `(() => {
  const selectors = [
    '#bx-panel',
    '.bx-panel',
    '.adm-header',
    '.adm-main-menu',
    '[id^="bx-panel"]'
  ];
  const foundSelectors = selectors.filter((selector) => document.querySelector(selector));
  const evidence = {
    checked_at: new Date().toISOString(),
    url: location.origin + location.pathname,
    title_present: Boolean(document.title && document.title.trim()),
    bx_present: typeof window.BX !== 'undefined',
    toolbar_or_admin_selectors: foundSelectors,
    body_has_content: Boolean(document.body && document.body.innerText && document.body.innerText.trim().length > 0)
  };

  window.__tacticumBitrixAdminEvidence = evidence;
  console.table([evidence]);
  console.log('Tacticum Bitrix admin observer installed. Inspect window.__tacticumBitrixAdminEvidence and transfer only safe summary data.');
})();`;
}

function printText(payload) {
  console.log('Bitrix Admin Gate Helper');
  console.log('========================');
  console.log(`Gate: ${payload.gate}`);
  console.log(`Base URL: ${payload.base_url}`);
  console.log('');

  if (payload.checklist) {
    console.log('Authenticated Admin Checklist');
    console.log('=============================');
    for (const item of payload.checklist) {
      console.log(`${item.step}:`);
      console.log(`- action: ${item.action}`);
      console.log(`- pass: ${item.pass_condition}`);
    }
    console.log('');
  }

  if (payload.browser_snippet) {
    console.log('Bitrix Admin Browser Snippet');
    console.log('============================');
    console.log('Paste this into an authenticated admin page or a public page with the Bitrix toolbar enabled.');
    console.log('The snippet is read-only and does not inspect cookies, sessions or form values.');
    console.log('');
    console.log(payload.browser_snippet);
    console.log('');
  }

  if (payload.evidence_template) {
    console.log('Bitrix Admin Evidence Template');
    console.log('==============================');
    console.log(JSON.stringify(payload.evidence_template, null, 2));
    console.log('');
  }

  console.log('Useful Commands');
  console.log('===============');
  for (const command of payload.useful_commands) {
    console.log(`- ${command}`);
  }
  console.log('');

  console.log('Evidence Rules');
  console.log('==============');
  for (const rule of payload.evidence_rules) {
    console.log(`- ${rule}`);
  }
}

function printUsage() {
  console.log('Usage: npm run bitrix:admin:gate-helper -- [--checklist] [--browser] [--evidence] [--json] [--base-url https://tacticum.ru]');
}

function trimTrailingSlash(value) {
  return String(value || '').replace(/\/+$/, '');
}

function die(message) {
  console.error(message);
  printUsage();
  process.exit(1);
}
