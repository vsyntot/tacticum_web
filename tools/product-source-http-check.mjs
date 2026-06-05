#!/usr/bin/env node

import http from 'node:http';
import https from 'node:https';

const DEFAULT_PAGES = ['/platform/', '/agents/', '/dev/', '/forum/'];
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

const baseUrl = normalizeBaseUrl(
  process.env.TACTICUM_PRODUCT_SOURCE_BASE_URL
    || process.env.TACTICUM_VISUAL_BASE_URL
    || 'https://tacticum.ru'
);
const pages = parseList(
  process.env.TACTICUM_PRODUCT_SOURCE_PAGES || process.env.TACTICUM_VISUAL_PAGES,
  DEFAULT_PAGES
);
const expectedSource = String(
  process.env.TACTICUM_EXPECT_PRODUCT_SOURCE
    || process.env.TACTICUM_PRODUCT_SOURCE_EXPECTED
    || 'bitrix'
).trim();

const failures = [];
const results = [];

for (const page of pages) {
  const url = new URL(page, baseUrl);
  const result = await checkPage(url);
  results.push(result);
  console.log(formatResult(result));
}

if (failures.length > 0) {
  console.error('');
  console.error(`Product source HTTP check failed: ${failures.length} issue(s).`);
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('');
console.log(`Product source HTTP check passed: ${results.length} page(s), expected source=${expectedSource}.`);

async function checkPage(url) {
  const response = await requestText(url);
  const html = response.body;
  const sources = unique([...html.matchAll(/\bdata-product-source=(["'])(.*?)\1/gi)]
    .map((match) => match[2].trim())
    .filter(Boolean));
  const codes = unique([...html.matchAll(/\bdata-product-code=(["'])(.*?)\1/gi)]
    .map((match) => match[2].trim())
    .filter(Boolean));
  const blocks = unique([...html.matchAll(/\bdata-product-block=(["'])(.*?)\1/gi)]
    .map((match) => match[2].trim())
    .filter(Boolean));
  const missingBlocks = REQUIRED_PRODUCT_BLOCKS.filter((block) => !blocks.includes(block));
  const unsafeHrefs = unsafeHrefValues(html);
  const expectedCode = url.pathname.replace(/^\/+|\/+$/g, '');
  const sourceOk = sources.length === 1 && sources[0] === expectedSource;
  const codeOk = codes.length === 1 && codes[0] === expectedCode;
  const statusOk = response.status >= 200 && response.status < 300;

  if (!statusOk) {
    failures.push(`${url.pathname}: HTTP ${response.status}`);
  }
  if (!sourceOk) {
    failures.push(`${url.pathname}: expected data-product-source=${expectedSource}, got ${sources.length > 0 ? sources.join(',') : 'empty'}`);
  }
  if (!codeOk) {
    failures.push(`${url.pathname}: expected data-product-code=${expectedCode}, got ${codes.length > 0 ? codes.join(',') : 'empty'}`);
  }
  if (missingBlocks.length > 0) {
    failures.push(`${url.pathname}: missing product blocks ${missingBlocks.join(',')}`);
  }
  if (unsafeHrefs.length > 0) {
    failures.push(`${url.pathname}: protocol-relative or backslash product hrefs are not allowed: ${unsafeHrefs.slice(0, 3).join(',')}`);
  }

  return {
    page: url.pathname,
    status: response.status,
    source: sources[0] || '',
    code: codes[0] || '',
    sources,
    codes,
    blocks,
    missingBlocks,
    unsafeHrefs,
    bytes: Buffer.byteLength(html),
    ok: statusOk && sourceOk && codeOk && missingBlocks.length === 0 && unsafeHrefs.length === 0,
  };
}

function requestText(url, redirects = 0) {
  return new Promise((resolve, reject) => {
    const client = url.protocol === 'http:' ? http : https;
    const request = client.get(url, {
      headers: {
        'User-Agent': 'tacticum-product-source-http-check/1.0',
      },
      timeout: 15000,
    }, (response) => {
      const status = response.statusCode || 0;
      const location = response.headers.location || '';
      if ([301, 302, 303, 307, 308].includes(status) && location) {
        response.resume();
        if (redirects >= 5) {
          reject(new Error(`Too many redirects for ${url.toString()}`));
          return;
        }
        resolve(requestText(new URL(location, url), redirects + 1));
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

function formatResult(result) {
  const marker = result.ok ? 'OK' : 'FAIL';
  const source = result.source || 'empty';
  const code = result.code || 'empty';
  const missing = result.missingBlocks.length > 0 ? ` missing=${result.missingBlocks.join(',')}` : '';
  const unsafe = result.unsafeHrefs.length > 0 ? ` unsafe_hrefs=${result.unsafeHrefs.length}` : '';

  return `${marker} ${result.page.padEnd(11)} status=${result.status} source=${source} code=${code} blocks=${result.blocks.length} bytes=${result.bytes}${missing}${unsafe}`;
}

function parseList(value, fallback) {
  if (!value) {
    return fallback;
  }

  const parsed = value.split(',')
    .map((item) => item.trim())
    .filter(Boolean);

  return parsed.length > 0 ? parsed : fallback;
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

function unsafeHrefValues(html) {
  return unique([...html.matchAll(/<a\b[^>]*\bhref=(["'])(.*?)\1/gi)]
    .map((match) => match[2].trim())
    .filter((href) => href.startsWith('//') || href.startsWith('/\\')));
}
