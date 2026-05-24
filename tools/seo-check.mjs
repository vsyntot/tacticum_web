#!/usr/bin/env node

import fs from 'node:fs';

const SITE = 'https://tacticum.ru';
const ROOT_SITEMAP_FILE = 'sitemap.xml';
const STATIC_SITEMAP_FILE = 'sitemap-basic-files.xml';
const ROOT_SITEMAP_URL = `${SITE}/sitemap.xml`;
const STATIC_SITEMAP_URL = `${SITE}/sitemap-basic-files.xml`;
const OFFER_SITEMAP_URL = `${SITE}/offer/sitemap.php`;
const MIN_LASTMOD_DATE = process.env.TACTICUM_SEO_MIN_LASTMOD || '2026-05-24';
const CHECK_HTTP = process.argv.includes('--http') || process.env.TACTICUM_SEO_CHECK_HTTP === '1';
const HTTP_BASE_URL = (process.env.TACTICUM_SEO_CHECK_BASE_URL || SITE).replace(/\/+$/, '');

const expectedStaticPages = new Map([
  ['index.php', '/'],
  ['about/index.php', '/about/'],
  ['aiagents/index.php', '/aiagents/'],
  ['calculator/index.php', '/calculator/'],
  ['contacts/index.php', '/contacts/'],
  ['offer/index.php', '/offer/'],
  ['policies/index.php', '/policies/'],
  ['price/index.php', '/price/'],
  ['services/index.php', '/services/']
]);

const expectedStaticUrls = Array.from(expectedStaticPages.values()).map((path) => SITE + path);

const expectedSitemaps = [
  STATIC_SITEMAP_URL,
  OFFER_SITEMAP_URL
];

const forbiddenRootSitemaps = [
  `${SITE}/sitemap-files.xml`,
  `${SITE}/sitemap-basic.xml`
];

const serviceEndpoints = [
  '/local/api/services.php',
  '/local/api/cases.php',
  '/local/rest/health_config.php'
];

const expectedTopMenuUrls = [
  '/price/',
  '/calculator/',
  '/aiagents/'
];

const errors = [];

function fail(message) {
  errors.push(message);
}

function read(path) {
  return fs.readFileSync(path, 'utf8');
}

function readOptional(path) {
  if (!fs.existsSync(path)) {
    return null;
  }

  return fs.readFileSync(path, 'utf8');
}

function extractTags(xml, tag) {
  const pattern = new RegExp(`<${tag}[^>]*>([\\s\\S]*?)<\\/${tag}>`, 'gi');
  const values = [];
  let match;
  while ((match = pattern.exec(xml)) !== null) {
    values.push(decodeXml(match[1].trim()));
  }
  return values;
}

function decodeXml(value) {
  return value
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&apos;/g, "'");
}

function assertHttps(values, label) {
  for (const value of values) {
    if (!value.startsWith('https://')) {
      fail(`${label} is not HTTPS: ${value}`);
    }
  }
}

function assertNoMissing(actual, expected, label) {
  for (const value of expected) {
    if (!actual.includes(value)) {
      fail(`${label} is missing ${value}`);
    }
  }
}

function assertNoUnexpected(actual, unexpected, label) {
  for (const value of unexpected) {
    if (actual.includes(value)) {
      fail(`${label} must not reference ${value}`);
    }
  }
}

function assertUnique(values, label) {
  const seen = new Set();
  for (const value of values) {
    if (seen.has(value)) {
      fail(`${label} has duplicate value: ${value}`);
    }
    seen.add(value);
  }
}

function assertLastmodCoverage(locs, lastmods, label) {
  if (locs.length !== lastmods.length) {
    fail(`${label} must have one lastmod per loc: loc=${locs.length}, lastmod=${lastmods.length}`);
  }
}

function assertFreshLastmod(values, label) {
  const minTime = Date.parse(`${MIN_LASTMOD_DATE}T00:00:00+03:00`);
  if (!Number.isFinite(minTime)) {
    fail(`Invalid TACTICUM_SEO_MIN_LASTMOD: ${MIN_LASTMOD_DATE}`);
    return;
  }

  for (const value of values) {
    const time = Date.parse(value);
    if (!Number.isFinite(time)) {
      fail(`${label} has invalid lastmod: ${value}`);
      continue;
    }
    if (time < minTime) {
      fail(`${label} has stale lastmod ${value}; expected >= ${MIN_LASTMOD_DATE}`);
    }
  }
}

function assertNoForbiddenSitemapLocs(values, label) {
  for (const value of values) {
    let pathname = '';
    try {
      pathname = new URL(value).pathname;
    } catch {
      fail(`${label} has invalid URL: ${value}`);
      continue;
    }

    if (pathname === '/404.php') {
      fail(`${label} must not include 404 page: ${value}`);
    }
    if (pathname.startsWith('/bitrix/')) {
      fail(`${label} must not include Bitrix system path: ${value}`);
    }
    if (pathname.startsWith('/local/')) {
      fail(`${label} must not include local service path: ${value}`);
    }
  }
}

function validateRootSitemap(xml, label) {
  const locs = extractTags(xml, 'loc');
  const lastmods = extractTags(xml, 'lastmod');

  assertNoMissing(locs, expectedSitemaps, label);
  assertNoUnexpected(locs, forbiddenRootSitemaps, label);
  assertHttps(locs, `${label} loc`);
  assertUnique(locs, `${label} loc`);
  assertLastmodCoverage(locs, lastmods, label);
  assertFreshLastmod(lastmods, label);
  assertNoForbiddenSitemapLocs(locs, `${label} loc`);
}

function validateStaticSitemap(xml, label) {
  const locs = extractTags(xml, 'loc');
  const lastmods = extractTags(xml, 'lastmod');

  assertHttps(locs, `${label} loc`);
  assertNoMissing(locs, expectedStaticUrls, label);
  assertUnique(locs, `${label} loc`);
  assertLastmodCoverage(locs, lastmods, label);
  assertFreshLastmod(lastmods, label);
  assertNoForbiddenSitemapLocs(locs, `${label} loc`);
}

function assertCanonicalPaths() {
  const canonicalPattern = /tacticum_apply_seo_defaults\s*\(\s*(['"])(\/[^'"]*)\1/s;

  for (const [file, expectedPath] of expectedStaticPages) {
    const source = read(file);
    const match = source.match(canonicalPattern);
    if (!match) {
      fail(`${file} is missing static tacticum_apply_seo_defaults canonical path`);
      continue;
    }

    const actualPath = match[2];
    if (actualPath !== expectedPath) {
      fail(`${file} canonical path is ${actualPath}; expected ${expectedPath}`);
    }
  }
}

function assertTopMenuProminence() {
  const menuSource = [
    read('.top.menu.php'),
    read('services/.top.menu_ext.php')
  ].join('\n');

  for (const url of expectedTopMenuUrls) {
    if (!menuSource.includes(`"${url}"`) && !menuSource.includes(`'${url}'`)) {
      fail(`top menu structure is missing money page ${url}`);
    }
  }
}

function assertDefaultSocialPreview() {
  const imagePath = 'local/templates/tacticum/images/og-default.jpg';
  if (!fs.existsSync(imagePath)) {
    fail(`${imagePath} is missing`);
  }

  const initSource = read('local/php_interface/init.php');
  if (!initSource.includes("/images/og-default.jpg")) {
    fail('SEO helper must use og-default.jpg as the default social preview image');
  }
  if (!initSource.includes("?? 1200") || !initSource.includes("?? 630")) {
    fail('SEO helper default social preview dimensions must be 1200x630');
  }
}

async function checkHttpRobots() {
  for (const endpoint of serviceEndpoints) {
    const url = HTTP_BASE_URL + endpoint;
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        Origin: SITE
      }
    });
    if (!response.ok) {
      fail(`${endpoint} returned HTTP ${response.status}`);
    }
    const robots = (response.headers.get('x-robots-tag') || '').toLowerCase();
    if (!robots.includes('noindex') || !robots.includes('nofollow')) {
      fail(`${endpoint} is missing X-Robots-Tag noindex, nofollow`);
    }
  }
}

async function fetchHttpText(path, label) {
  const response = await fetch(`${HTTP_BASE_URL}${path}`);
  if (!response.ok) {
    fail(`${label} returned HTTP ${response.status}`);
    return '';
  }

  return response.text();
}

async function checkHttpSitemapGovernance() {
  const robots = await fetchHttpText('/robots.txt', 'production robots.txt');
  if (robots && !robots.includes(`Sitemap: ${ROOT_SITEMAP_URL}`)) {
    fail(`production robots.txt must point to ${ROOT_SITEMAP_URL}`);
  }

  const rootSitemap = await fetchHttpText('/sitemap.xml', 'production sitemap.xml');
  if (rootSitemap) {
    validateRootSitemap(rootSitemap, 'production sitemap.xml');
  }

  const staticSitemap = await fetchHttpText('/sitemap-basic-files.xml', 'production sitemap-basic-files.xml');
  if (staticSitemap) {
    validateStaticSitemap(staticSitemap, 'production sitemap-basic-files.xml');
  }
}

async function checkHttpOfferSitemap() {
  const url = `${HTTP_BASE_URL}/offer/sitemap.php`;
  const response = await fetch(url);
  if (!response.ok) {
    fail(`/offer/sitemap.php returned HTTP ${response.status}`);
    return;
  }

  const xml = await response.text();
  const offerLocs = extractTags(xml, 'loc');
  assertHttps(offerLocs, '/offer/sitemap.php loc');
  assertUnique(offerLocs, '/offer/sitemap.php loc');
  assertNoForbiddenSitemapLocs(offerLocs, '/offer/sitemap.php loc');

  for (const loc of offerLocs) {
    if (!loc.startsWith(`${SITE}/offer/`)) {
      fail(`/offer/sitemap.php has non-offer loc: ${loc}`);
    }
  }
}

const sitemapIndex = read(ROOT_SITEMAP_FILE);
const staticSitemap = readOptional(STATIC_SITEMAP_FILE);
const robots = read('robots.txt');

validateRootSitemap(sitemapIndex, ROOT_SITEMAP_FILE);
if (staticSitemap !== null) {
  validateStaticSitemap(staticSitemap, STATIC_SITEMAP_FILE);
}

assertCanonicalPaths();
assertTopMenuProminence();
assertDefaultSocialPreview();

if (!robots.includes(`Sitemap: ${ROOT_SITEMAP_URL}`)) {
  fail(`robots.txt must point to ${ROOT_SITEMAP_URL}`);
}

if (CHECK_HTTP) {
  await checkHttpSitemapGovernance();
  await checkHttpRobots();
  await checkHttpOfferSitemap();
}

if (errors.length > 0) {
  console.error('SEO check failed:');
  for (const error of errors) {
    console.error(`- ${error}`);
  }
  process.exit(1);
}

console.log('SEO check passed.');
