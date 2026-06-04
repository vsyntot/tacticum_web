#!/usr/bin/env node

import http from 'node:http';
import https from 'node:https';

const PRODUCT_PAGES = ['/platform/', '/agents/', '/dev/', '/forum/'];
const REQUIRED_PRODUCT_BLOCKS = [
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
const REQUIRED_HEALTH_SCOPES = ['products'];

const args = process.argv.slice(2);
const jsonOutput = args.includes('--json') || process.env.TACTICUM_PRODUCT_SWITCH_JSON === '1';
const baseUrl = normalizeBaseUrl(process.env.TACTICUM_PRODUCT_SWITCH_BASE_URL || 'https://tacticum.ru');
const failures = [];

const health = await checkHealthConfig();
const products = [];
for (const page of PRODUCT_PAGES) {
  products.push(await checkProductPage(page));
}

const summary = {
  base_url: baseUrl,
  checked_at: new Date().toISOString(),
  ready_for_config_switch: failures.length === 0,
  health_config: health,
  product_pages: products,
  required_pre_switch_evidence: [
    'npm run product:content:check on target Bitrix/PHP environment',
    'npm run product:content:check:strict on target Bitrix/PHP environment',
    'npm run product:content:cache-clear:dry-run on target Bitrix/PHP environment',
    'npm run product:source:http:prod on target/public URL',
    'npm run release:public-precheck:prod on target/public URL',
    'Bitrix admin/content review for products, product_blocks and product_use_cases',
  ],
  switch_steps: [
    'Set products.source=bitrix in ignored local/php_interface/include/tacticum_config.php on the target environment.',
    'Run npm run product:content:cache-clear after config sync.',
    'Clear Bitrix composite/template cache if enabled.',
    'Run npm run product:content:check:strict.',
    'Run npm run product:source:http:prod.',
    'Run npm run release:public-precheck:prod.',
    'Run rendered SEO/browser smoke where Chrome/Chromium is available.',
  ],
  rollback_steps: [
    'Keep products.source=bitrix and fix Bitrix product content; silent Git fallback is disabled by owner decision.',
    'Run npm run product:content:cache-clear.',
    'Clear Bitrix composite/template cache if enabled.',
    'Run npm run product:source:http:prod and confirm rendered source/blocks recover.',
  ],
  failures,
};

if (jsonOutput) {
  console.log(JSON.stringify(summary, null, 2));
} else {
  printHuman(summary);
}

if (failures.length > 0) {
  process.exit(1);
}

function printHuman(summary) {
  console.log('Product Content Source Switch Readiness');
  console.log('=======================================');
  console.log(`Base URL: ${summary.base_url}`);
  console.log(`Ready for products.source=bitrix switch: ${summary.ready_for_config_switch ? 'yes' : 'no'}`);
  console.log('');

  console.log(`Health config: status=${summary.health_config.status} success=${summary.health_config.success} scopes=${summary.health_config.scopes.join(',') || '-'}`);
  if (summary.health_config.missing_scopes.length > 0) {
    console.log(`Missing health scopes: ${summary.health_config.missing_scopes.join(',')}`);
  }
  console.log('');

  console.log('Product pages:');
  for (const product of summary.product_pages) {
    const missing = product.missing_blocks.length > 0 ? ` missing=${product.missing_blocks.join(',')}` : '';
    console.log(`- ${product.page}: status=${product.status}, source=${product.source || 'empty'}, blocks=${product.blocks.length}${missing}`);
  }

  console.log('');
  console.log('Required pre-switch evidence:');
  for (const item of summary.required_pre_switch_evidence) {
    console.log(`- ${item}`);
  }

  console.log('');
  console.log('Switch steps:');
  for (const item of summary.switch_steps) {
    console.log(`- ${item}`);
  }

  console.log('');
  console.log('Rollback steps:');
  for (const item of summary.rollback_steps) {
    console.log(`- ${item}`);
  }

  if (summary.failures.length > 0) {
    console.log('');
    console.log('Failures:');
    for (const failure of summary.failures) {
      console.log(`- ${failure}`);
    }
  }
}

async function checkHealthConfig() {
  const url = new URL('/local/rest/health_config.php', baseUrl);
  const response = await requestText(url, {
    Origin: baseUrl,
    'User-Agent': 'tacticum-product-content-switch-readiness/1.0',
  });
  let payload = {};
  try {
    payload = JSON.parse(response.body);
  } catch {
    failures.push(`health_config: invalid JSON response, HTTP ${response.status}`);
  }

  const scopes = Array.isArray(payload.scopes)
    ? payload.scopes.map((scope) => String(scope).trim()).filter(Boolean)
    : [];
  const missingScopes = REQUIRED_HEALTH_SCOPES.filter((scope) => !scopes.includes(scope));
  if (response.status < 200 || response.status >= 300) {
    failures.push(`health_config: HTTP ${response.status}`);
  }
  if (payload.success !== true) {
    failures.push('health_config: success=true is required');
  }
  if (missingScopes.length > 0) {
    failures.push(`health_config: missing scopes ${missingScopes.join(',')}`);
  }

  return {
    status: response.status,
    success: payload.success === true,
    code: String(payload.code || ''),
    scopes,
    missing_scopes: missingScopes,
  };
}

async function checkProductPage(page) {
  const url = new URL(page, baseUrl);
  const response = await requestText(url, {
    'User-Agent': 'tacticum-product-content-switch-readiness/1.0',
  });
  const html = response.body;
  const sources = unique([...html.matchAll(/\bdata-product-source=(["'])(.*?)\1/gi)]
    .map((match) => match[2].trim())
    .filter(Boolean));
  const blocks = unique([...html.matchAll(/\bdata-product-block=(["'])(.*?)\1/gi)]
    .map((match) => match[2].trim())
    .filter(Boolean));
  const missingBlocks = REQUIRED_PRODUCT_BLOCKS.filter((block) => !blocks.includes(block));

  if (response.status < 200 || response.status >= 300) {
    failures.push(`${page}: HTTP ${response.status}`);
  }
  if (sources.length !== 1 || sources[0] !== 'bitrix') {
    failures.push(`${page}: expected data-product-source=bitrix, got ${sources.join(',') || 'empty'}`);
  }
  if (missingBlocks.length > 0) {
    failures.push(`${page}: missing product blocks ${missingBlocks.join(',')}`);
  }

  return {
    page,
    status: response.status,
    source: sources[0] || '',
    sources,
    blocks,
    missing_blocks: missingBlocks,
    bytes: Buffer.byteLength(html),
  };
}

function requestText(url, headers = {}, redirects = 0) {
  return new Promise((resolve, reject) => {
    const client = url.protocol === 'http:' ? http : https;
    const request = client.get(url, { headers, timeout: 15000 }, (response) => {
      const status = response.statusCode || 0;
      const location = response.headers.location || '';
      if ([301, 302, 303, 307, 308].includes(status) && location) {
        response.resume();
        if (redirects >= 5) {
          reject(new Error(`Too many redirects for ${url.toString()}`));
          return;
        }
        resolve(requestText(new URL(location, url), headers, redirects + 1));
        return;
      }

      response.setEncoding('utf8');
      let body = '';
      response.on('data', (chunk) => {
        body += chunk;
      });
      response.on('end', () => {
        resolve({ status, body });
      });
    });

    request.on('timeout', () => {
      request.destroy(new Error(`Request timeout: ${url.toString()}`));
    });
    request.on('error', reject);
  });
}

function normalizeBaseUrl(value) {
  const normalized = String(value || '').trim().replace(/\/+$/, '');
  if (!/^https?:\/\//i.test(normalized)) {
    throw new Error(`Invalid base URL: ${value}`);
  }

  return normalized;
}

function unique(items) {
  return items.filter((item, index) => items.indexOf(item) === index);
}
