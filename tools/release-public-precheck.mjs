#!/usr/bin/env node

import http from 'node:http';
import https from 'node:https';

const DEFAULT_PRODUCT_PAGES = ['/platform/', '/agents/', '/dev/', '/forum/'];
const DEFAULT_PUBLIC_PAGES = [
  '/',
  '/platform/',
  '/agents/',
  '/dev/',
  '/forum/',
  '/services/',
  '/price/',
  '/calculator/',
  '/offer/',
  '/aiagents/',
  '/about/',
  '/contacts/',
  '/policies/',
];
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
const REQUIRED_HEALTH_SCOPES = ['api', 'ai', 'telegram', 'offer', 'content', 'products', 'rest', 'security'];
const LEGACY_ENDPOINTS = ['/local/rest/tacticum_offer.php', '/local/rest/tacticum_sale.php'];
const ROOT_SITEMAP_PATH = '/sitemap.xml';
const STATIC_SITEMAP_PATH = '/sitemap-basic-files.xml';
const OFFER_SITEMAP_PATH = '/offer/sitemap.php';
const WEBMANIFEST_PATH = '/local/templates/tacticum/images/site.webmanifest';
const ACCEPTED_WEBMANIFEST_CONTENT_TYPES = new Set([
  'application/manifest+json',
  'application/json',
]);

const baseUrl = normalizeBaseUrl(
  process.env.TACTICUM_RELEASE_BASE_URL
    || process.env.TACTICUM_PRODUCT_SOURCE_BASE_URL
    || 'https://tacticum.ru'
);
const siteOrigin = new URL(baseUrl).origin;

const checks = [];
const failures = [];

await checkHealthConfig();
await checkPublicHtmlLanguage();
await checkSeoPublicSurface();
await checkProductSource();
await checkMetrikaPublicTag();
await checkBitrixAdminSurface();
await checkLegacyAliasHeaders();

for (const check of checks) {
  const marker = check.ok ? 'OK' : 'FAIL';
  console.log(`${marker} ${check.name} ${check.detail || ''}`.trim());
}

if (failures.length > 0) {
  console.error('');
  console.error(`Release public precheck failed: ${failures.length} issue(s).`);
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('');
console.log(`Release public precheck passed for ${baseUrl}.`);
console.log('Manual release gates are tracked in release sign-off; current owner evidence is closed there.');
console.log('Bitrix admin authenticated smoke is tracked in release sign-off separately from this unauthenticated public precheck.');

async function checkHealthConfig() {
  const url = new URL('/local/rest/health_config.php', baseUrl);
  const response = await requestText(url, {
    headers: {
      Origin: baseUrl,
      'User-Agent': 'tacticum-release-public-precheck/1.0',
    },
  });
  let payload = null;
  try {
    payload = JSON.parse(response.body);
  } catch {
    fail('health_config', `invalid JSON response, HTTP ${response.status}`);
  }

  const scopes = Array.isArray(payload?.scopes) ? payload.scopes : [];
  const missingScopes = REQUIRED_HEALTH_SCOPES.filter((scope) => !scopes.includes(scope));
  const ok = response.status === 200 && payload?.success === true && missingScopes.length === 0;
  addCheck('health_config', ok, `status=${response.status} scopes=${scopes.join(',') || '-'}`);
  if (!ok) {
    fail('health_config', `expected success=true and scopes ${REQUIRED_HEALTH_SCOPES.join(',')}; missing=${missingScopes.join(',') || '-'} status=${response.status}`);
  }
}

async function checkPublicHtmlLanguage() {
  for (const page of DEFAULT_PUBLIC_PAGES) {
    const response = await requestText(new URL(page, baseUrl));
    const ok = response.status === 200 && /<html\b[^>]*\blang=(["'])ru\1/i.test(response.body);
    addCheck(`html_lang ${page}`, ok, `status=${response.status} lang=${ok ? 'ru' : 'missing'}`);
    if (!ok) {
      fail(`html_lang ${page}`, `expected public page to render <html lang="ru">; status=${response.status}`);
    }
  }
}

async function checkSeoPublicSurface() {
  const rootSitemapUrl = `${siteOrigin}${ROOT_SITEMAP_PATH}`;
  const staticSitemapUrl = `${siteOrigin}${STATIC_SITEMAP_PATH}`;
  const offerSitemapUrl = `${siteOrigin}${OFFER_SITEMAP_PATH}`;

  const robots = await requestText(new URL('/robots.txt', baseUrl));
  const robotsOk = robots.status === 200 && robots.body.includes(`Sitemap: ${rootSitemapUrl}`);
  addCheck('robots_txt', robotsOk, `status=${robots.status} sitemap=${robotsOk ? 'ok' : 'missing'}`);
  if (!robotsOk) {
    fail('robots_txt', `expected HTTP 200 and Sitemap: ${rootSitemapUrl}; status=${robots.status}`);
  }

  const sitemapIndex = await requestText(new URL(ROOT_SITEMAP_PATH, baseUrl));
  const sitemapIndexLocs = extractTags(sitemapIndex.body, 'loc');
  const sitemapIndexOk = sitemapIndex.status === 200
    && sitemapIndexLocs.includes(staticSitemapUrl)
    && sitemapIndexLocs.includes(offerSitemapUrl)
    && !sitemapIndexLocs.some((loc) => loc.includes('/sitemap-files.xml') || loc.includes('/sitemap-basic.xml'));
  addCheck('sitemap_index', sitemapIndexOk, `status=${sitemapIndex.status} sitemaps=${sitemapIndexLocs.length}`);
  if (!sitemapIndexOk) {
    fail('sitemap_index', `expected root sitemap to include ${staticSitemapUrl} and ${offerSitemapUrl} only; got=${sitemapIndexLocs.join(',') || '-'}`);
  }

  const staticSitemap = await requestText(new URL(STATIC_SITEMAP_PATH, baseUrl));
  const staticSitemapLocs = extractTags(staticSitemap.body, 'loc');
  const expectedStaticUrls = DEFAULT_PUBLIC_PAGES.map((page) => `${siteOrigin}${page}`);
  const missingStaticUrls = expectedStaticUrls.filter((loc) => !staticSitemapLocs.includes(loc));
  const unsafeStaticUrls = staticSitemapLocs.filter((loc) => loc.includes('/bitrix/') || loc.includes('/local/') || loc.includes('/404.php'));
  const staticSitemapOk = staticSitemap.status === 200 && missingStaticUrls.length === 0 && unsafeStaticUrls.length === 0;
  addCheck('static_sitemap', staticSitemapOk, `status=${staticSitemap.status} urls=${staticSitemapLocs.length} missing=${missingStaticUrls.length}`);
  if (!staticSitemapOk) {
    fail('static_sitemap', `expected ${expectedStaticUrls.length} public URLs and no unsafe locs; missing=${missingStaticUrls.join(',') || '-'} unsafe=${unsafeStaticUrls.join(',') || '-'} status=${staticSitemap.status}`);
  }

  const offerSitemap = await requestText(new URL(OFFER_SITEMAP_PATH, baseUrl));
  const offerSitemapLocs = extractTags(offerSitemap.body, 'loc');
  const offerSitemapOk = offerSitemap.status === 200
    && offerSitemapLocs.length > 0
    && offerSitemapLocs.every((loc) => loc.startsWith(`${siteOrigin}/offer/`))
    && !offerSitemapLocs.some((loc) => loc.includes('/bitrix/') || loc.includes('/local/') || loc.includes('/404.php'));
  addCheck('offer_sitemap', offerSitemapOk, `status=${offerSitemap.status} urls=${offerSitemapLocs.length}`);
  if (!offerSitemapOk) {
    fail('offer_sitemap', `expected offer sitemap locs under ${siteOrigin}/offer/ and no unsafe locs; urls=${offerSitemapLocs.length} status=${offerSitemap.status}`);
  }

  const manifest = await requestText(new URL(WEBMANIFEST_PATH, baseUrl), { method: 'HEAD' });
  const manifestType = String(manifest.headers['content-type'] || '').split(';', 1)[0].trim().toLowerCase();
  const manifestOk = manifest.status === 200 && ACCEPTED_WEBMANIFEST_CONTENT_TYPES.has(manifestType);
  addCheck('webmanifest', manifestOk, `status=${manifest.status} type=${manifestType || '-'}`);
  if (!manifestOk) {
    fail('webmanifest', `expected application/manifest+json or application/json; got type=${manifestType || '-'} status=${manifest.status}`);
  }
}

async function checkProductSource() {
  for (const page of DEFAULT_PRODUCT_PAGES) {
    const url = new URL(page, baseUrl);
    const response = await requestText(url);
    const sources = unique([...response.body.matchAll(/\bdata-product-source=(["'])(.*?)\1/gi)]
      .map((match) => match[2].trim())
      .filter(Boolean));
    const codes = unique([...response.body.matchAll(/\bdata-product-code=(["'])(.*?)\1/gi)]
      .map((match) => match[2].trim())
      .filter(Boolean));
    const blocks = unique([...response.body.matchAll(/\bdata-product-block=(["'])(.*?)\1/gi)]
      .map((match) => match[2].trim())
      .filter(Boolean));
    const missingBlocks = REQUIRED_PRODUCT_BLOCKS.filter((block) => !blocks.includes(block));
    const unsafeHrefs = unsafeHrefValues(response.body);
    const expectedCode = page.replace(/^\/+|\/+$/g, '');
    const ok = response.status === 200
      && sources.length === 1
      && sources[0] === 'bitrix'
      && codes.length === 1
      && codes[0] === expectedCode
      && missingBlocks.length === 0
      && unsafeHrefs.length === 0;
    addCheck(`product_source ${page}`, ok, `status=${response.status} source=${sources[0] || 'empty'} code=${codes[0] || 'empty'} blocks=${blocks.length} unsafe_hrefs=${unsafeHrefs.length}`);
    if (!ok) {
      fail(`product_source ${page}`, `expected source=bitrix, code=${expectedCode}, ${REQUIRED_PRODUCT_BLOCKS.length} blocks and safe hrefs; got source=${sources.join(',') || 'empty'}, code=${codes.join(',') || 'empty'}, missing=${missingBlocks.join(',') || '-'}, unsafe_hrefs=${unsafeHrefs.join(',') || '-'}, status=${response.status}`);
    }
  }
}

async function checkMetrikaPublicTag() {
  const home = await requestText(new URL('/', baseUrl));
  const script = await requestText(new URL('/local/templates/tacticum/js/metrika.js', baseUrl));
  const htmlOk = home.status === 200 && home.body.includes('mc.yandex.ru/watch/103471113');
  const scriptOk = script.status === 200
    && script.body.includes('103471113')
    && script.body.includes('mc.yandex.ru/metrika/tag.js');
  const ok = htmlOk && scriptOk;
  addCheck('metrika_public_tag', ok, `home=${home.status} script=${script.status}`);
  if (!ok) {
    fail('metrika_public_tag', `expected noscript pixel and metrika.js counter 103471113; home=${home.status} script=${script.status}`);
  }
}

async function checkBitrixAdminSurface() {
  const response = await requestText(new URL('/bitrix/admin/', baseUrl));
  const ok = response.status >= 200 && response.status < 500 && response.body.length > 200;
  addCheck('bitrix_admin_surface', ok, `status=${response.status} bytes=${Buffer.byteLength(response.body)}`);
  if (!ok) {
    fail('bitrix_admin_surface', `expected unauthenticated admin surface without 5xx; status=${response.status}`);
  }
}

async function checkLegacyAliasHeaders() {
  for (const endpoint of LEGACY_ENDPOINTS) {
    const response = await requestText(new URL(endpoint, baseUrl), {
      headers: {
        Origin: baseUrl,
        'User-Agent': 'tacticum-release-public-precheck/1.0',
      },
    });
    const headers = response.headers;
    const deprecation = String(headers.deprecation || '').toLowerCase();
    const sunset = String(headers.sunset || '');
    const link = String(headers.link || '');
    const robots = String(headers['x-robots-tag'] || '').toLowerCase();
    const ok = response.status === 405
      && deprecation === 'true'
      && sunset.includes('30 Sep 2026')
      && link.includes('/local/rest/tacticum_form.php')
      && robots.includes('noindex');
    addCheck(`legacy_alias ${endpoint}`, ok, `status=${response.status} deprecation=${deprecation || '-'} sunset=${sunset || '-'}`);
    if (!ok) {
      fail(`legacy_alias ${endpoint}`, `expected 405 + deprecation/sunset/link/noindex headers; status=${response.status}`);
    }
  }
}

function requestText(url, options = {}, redirects = 0) {
  return new Promise((resolve, reject) => {
    const client = url.protocol === 'http:' ? http : https;
    const request = client.request(url, {
      ...options,
      method: options.method || 'GET',
      headers: {
        'User-Agent': 'tacticum-release-public-precheck/1.0',
        ...(options.headers || {}),
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
        resolve(requestText(new URL(location, url), options, redirects + 1));
        return;
      }

      if ((options.method || 'GET').toUpperCase() === 'HEAD') {
        response.resume();
        response.on('end', () => {
          resolve({ status, headers: response.headers, body: '' });
        });
        return;
      }

      response.setEncoding('utf8');
      let body = '';
      response.on('data', (chunk) => {
        body += chunk;
      });
      response.on('end', () => {
        resolve({ status, headers: response.headers, body });
      });
    });

    request.on('timeout', () => {
      request.destroy(new Error(`Request timeout: ${url.toString()}`));
    });
    request.on('error', reject);
    request.end();
  });
}

function addCheck(name, ok, detail = '') {
  checks.push({ name, ok, detail });
}

function fail(name, message) {
  failures.push(`${name}: ${message}`);
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

function extractTags(xml, tagName) {
  const pattern = new RegExp(`<${tagName}>([^<]+)</${tagName}>`, 'gi');
  return [...String(xml || '').matchAll(pattern)].map((match) => decodeXml(match[1].trim()));
}

function decodeXml(value) {
  return value
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'");
}

function unsafeHrefValues(html) {
  return unique([...html.matchAll(/<a\b[^>]*\bhref=(["'])(.*?)\1/gi)]
    .map((match) => match[2].trim())
    .filter((href) => href.startsWith('//') || href.startsWith('/\\')));
}
