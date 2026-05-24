#!/usr/bin/env node

import fs from 'node:fs';

const SITE = 'https://tacticum.ru';
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
  `${SITE}/sitemap-files.xml`,
  `${SITE}/offer/sitemap.php`
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

const sitemapIndex = read('sitemap.xml');
const staticSitemap = read('sitemap-files.xml');
const robots = read('robots.txt');

const sitemapLocs = extractTags(sitemapIndex, 'loc');
const sitemapLastmods = extractTags(sitemapIndex, 'lastmod');
const staticLocs = extractTags(staticSitemap, 'loc');
const staticLastmods = extractTags(staticSitemap, 'lastmod');

assertNoMissing(sitemapLocs, expectedSitemaps, 'sitemap.xml');
assertHttps(sitemapLocs, 'sitemap.xml loc');
assertHttps(staticLocs, 'sitemap-files.xml loc');
assertNoMissing(staticLocs, expectedStaticUrls, 'sitemap-files.xml');
assertUnique(sitemapLocs, 'sitemap.xml loc');
assertUnique(staticLocs, 'sitemap-files.xml loc');
assertLastmodCoverage(sitemapLocs, sitemapLastmods, 'sitemap.xml');
assertLastmodCoverage(staticLocs, staticLastmods, 'sitemap-files.xml');
assertFreshLastmod([...sitemapLastmods, ...staticLastmods], 'sitemap');
assertCanonicalPaths();
assertTopMenuProminence();
assertDefaultSocialPreview();

if (!robots.includes(`Sitemap: ${SITE}/sitemap.xml`)) {
  fail('robots.txt must point to HTTPS sitemap.xml');
}

if (CHECK_HTTP) {
  await checkHttpRobots();
}

if (errors.length > 0) {
  console.error('SEO check failed:');
  for (const error of errors) {
    console.error(`- ${error}`);
  }
  process.exit(1);
}

console.log('SEO check passed.');
