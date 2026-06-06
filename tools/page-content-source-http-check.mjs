#!/usr/bin/env node

import http from 'node:http';
import https from 'node:https';

const DEFAULT_PAGE_SECTIONS = new Map([
  ['/services/', [
    ['delivery-layer', 'product-card-grid'],
    ['process', 'step-list'],
    ['tech', 'tech-grid'],
  ]],
  ['/price/', [
    ['features', 'feature-card-grid'],
    ['workstreams', 'product-card-grid'],
  ]],
  ['/contacts/', [
    ['routing', 'routing-card-grid'],
    ['cards', 'contact-card-grid'],
  ]],
  ['/offer/', [
    ['product-bridge', 'product-card-grid'],
    ['bottom-cta', 'cta-band'],
  ]],
]);

const baseUrl = normalizeBaseUrl(
  process.env.TACTICUM_PAGE_CONTENT_SOURCE_BASE_URL
    || process.env.TACTICUM_VISUAL_BASE_URL
    || 'https://tacticum.ru'
);
const pages = parseList(
  process.env.TACTICUM_PAGE_CONTENT_SOURCE_PAGES || process.env.TACTICUM_VISUAL_PAGES,
  Array.from(DEFAULT_PAGE_SECTIONS.keys())
);
const expectedSource = String(
  process.env.TACTICUM_EXPECT_PAGE_CONTENT_SOURCE
    || process.env.TACTICUM_PAGE_CONTENT_SOURCE_EXPECTED
    || 'bitrix'
).trim().toLowerCase();

if (!['fallback', 'bitrix'].includes(expectedSource)) {
  throw new Error(`Invalid expected page-content source: ${expectedSource}`);
}

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
  console.error(`Page-content source HTTP check failed: ${failures.length} issue(s).`);
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('');
console.log(`Page-content source HTTP check passed: ${results.length} page(s), expected source=${expectedSource}.`);

async function checkPage(url) {
  const response = await requestText(url);
  const html = response.body;
  const markers = pageContentMarkers(html);
  const expectedSections = DEFAULT_PAGE_SECTIONS.get(url.pathname) || [];
  const unsafeHrefs = unsafeHrefValues(html);
  const statusOk = response.status >= 200 && response.status < 300;

  if (!statusOk) {
    failures.push(`${url.pathname}: HTTP ${response.status}`);
  }

  if (expectedSource === 'fallback') {
    if (markers.length > 0) {
      failures.push(`${url.pathname}: expected fallback source with no page-content markers, got ${markers.length}`);
    }
  } else {
    validateBitrixMarkers(url.pathname, markers, expectedSections);
  }

  if (unsafeHrefs.length > 0) {
    failures.push(`${url.pathname}: protocol-relative or backslash hrefs are not allowed: ${unsafeHrefs.slice(0, 3).join(',')}`);
  }

  const ok = statusOk
    && unsafeHrefs.length === 0
    && (expectedSource === 'fallback'
      ? markers.length === 0
      : expectedSections.length > 0 && markers.length === expectedSections.length);

  return {
    page: url.pathname,
    status: response.status,
    expectedSections: expectedSections.length,
    sections: markers,
    unsafeHrefs,
    bytes: Buffer.byteLength(html),
    ok,
  };
}

function validateBitrixMarkers(page, markers, expectedSections) {
  if (expectedSections.length === 0) {
    failures.push(`${page}: no expected page-content sections are configured for this path`);
    return;
  }
  if (markers.length !== expectedSections.length) {
    failures.push(`${page}: expected ${expectedSections.length} page-content sections, got ${markers.length}`);
  }

  const markerBySection = new Map(markers.map((marker) => [marker.section, marker]));
  for (const [section, template] of expectedSections) {
    const marker = markerBySection.get(section);
    if (!marker) {
      failures.push(`${page}: missing page-content section ${section}`);
      continue;
    }
    if (marker.source !== 'bitrix') {
      failures.push(`${page}: section ${section} expected source=bitrix, got ${marker.source || 'empty'}`);
    }
    if (marker.page !== page) {
      failures.push(`${page}: section ${section} expected page marker ${page}, got ${marker.page || 'empty'}`);
    }
    if (marker.template !== template) {
      failures.push(`${page}: section ${section} expected template ${template}, got ${marker.template || 'empty'}`);
    }
  }

  for (const marker of markers) {
    if (!expectedSections.some(([section]) => section === marker.section)) {
      failures.push(`${page}: unexpected page-content section ${marker.section || 'empty'}`);
    }
  }
}

function pageContentMarkers(html) {
  return [...html.matchAll(/<section\b[^>]*\bdata-page-content-source=(['"])(.*?)\1[^>]*>/gi)]
    .map((match) => {
      const tag = match[0];
      return {
        source: attributeValue(tag, 'data-page-content-source'),
        page: attributeValue(tag, 'data-page-content-page'),
        section: attributeValue(tag, 'data-page-content-section'),
        template: attributeValue(tag, 'data-page-content-template'),
      };
    });
}

function attributeValue(tag, name) {
  const pattern = new RegExp(`\\b${escapeRegExp(name)}=(['\"])(.*?)\\1`, 'i');
  const match = tag.match(pattern);

  return match ? htmlDecode(match[2].trim()) : '';
}

function requestText(url, redirects = 0) {
  return new Promise((resolve, reject) => {
    const client = url.protocol === 'http:' ? http : https;
    const request = client.get(url, {
      headers: {
        'User-Agent': 'tacticum-page-content-source-http-check/1.0',
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
  const source = result.sections.length > 0 ? 'bitrix' : 'fallback';
  const sectionSummary = expectedSource === 'bitrix'
    ? `${result.sections.length}/${result.expectedSections}`
    : String(result.sections.length);
  const unsafe = result.unsafeHrefs.length > 0 ? ` unsafe_hrefs=${result.unsafeHrefs.length}` : '';

  return `${marker} ${result.page.padEnd(11)} status=${result.status} source=${source} sections=${sectionSummary} bytes=${result.bytes}${unsafe}`;
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

function unsafeHrefValues(html) {
  return unique([...html.matchAll(/<a\b[^>]*\bhref=(["'])(.*?)\1/gi)]
    .map((match) => match[2].trim())
    .filter((href) => href.startsWith('//') || href.startsWith('/\\')));
}

function htmlDecode(value) {
  return value
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
}

function unique(items) {
  return items.filter((item, index) => items.indexOf(item) === index);
}

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
