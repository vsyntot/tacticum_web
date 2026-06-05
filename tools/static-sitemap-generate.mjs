#!/usr/bin/env node

import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';

const SITE = 'https://tacticum.ru';
const DEFAULT_OUTPUT = 'sitemap-basic-files.xml';
const STATIC_PATHS = [
  '/',
  '/about/',
  '/platform/',
  '/agents/',
  '/dev/',
  '/forum/',
  '/aiagents/',
  '/calculator/',
  '/contacts/',
  '/offer/',
  '/policies/',
  '/price/',
  '/services/'
];

const options = parseOptions(process.argv.slice(2));
const lastmod = normalizeLastmod(options.lastmod || process.env.TACTICUM_STATIC_SITEMAP_LASTMOD || currentDate());
const output = resolve(options.output || DEFAULT_OUTPUT);
const xml = buildSitemapXml(lastmod);

if (options.check) {
  const existing = await readFile(output, 'utf8').catch(() => '');
  if (normalizeXml(existing) !== normalizeXml(xml)) {
    console.error(`${options.output || DEFAULT_OUTPUT} is not up to date. Run npm run sitemap:static:generate.`);
    process.exit(1);
  }
  console.log(`Static sitemap is up to date: ${options.output || DEFAULT_OUTPUT}`);
  process.exit(0);
}

await mkdir(dirname(output), { recursive: true });
await writeFile(output, xml, 'utf8');
console.log(`Static sitemap written: ${output}`);
console.log(`URLs: ${STATIC_PATHS.length}; lastmod=${lastmod}`);

function parseOptions(args) {
  const parsed = {
    output: '',
    lastmod: '',
    check: false
  };

  for (const arg of args) {
    if (arg === '--check') {
      parsed.check = true;
      continue;
    }
    if (arg.startsWith('--output=')) {
      parsed.output = arg.slice('--output='.length);
      continue;
    }
    if (arg.startsWith('--lastmod=')) {
      parsed.lastmod = arg.slice('--lastmod='.length);
      continue;
    }
    if (arg === '--help' || arg === '-h') {
      console.log([
        'Usage:',
        '  node tools/static-sitemap-generate.mjs [--output=sitemap-basic-files.xml] [--lastmod=YYYY-MM-DD] [--check]',
        '',
        'Generates the Bitrix-compatible static file sitemap artifact for public page entries.',
        'The generated sitemap-basic-files.xml is ignored by Git and deployed as a build artifact.'
      ].join('\n'));
      process.exit(0);
    }

    throw new Error(`Unknown argument: ${arg}`);
  }

  return parsed;
}

function buildSitemapXml(lastmodDate) {
  const items = STATIC_PATHS
    .map((path) => {
      const loc = escapeXml(SITE + path);
      return [
        '  <url>',
        `    <loc>${loc}</loc>`,
        `    <lastmod>${lastmodDate}T00:00:00+03:00</lastmod>`,
        '  </url>'
      ].join('\n');
    })
    .join('\n');

  return [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
    items,
    '</urlset>',
    ''
  ].join('\n');
}

function currentDate() {
  return new Date().toISOString().slice(0, 10);
}

function normalizeLastmod(value) {
  const normalized = String(value || '').trim();
  if (!/^\d{4}-\d{2}-\d{2}$/.test(normalized)) {
    throw new Error(`Invalid --lastmod value: ${value}`);
  }

  return normalized;
}

function escapeXml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

function normalizeXml(value) {
  return String(value).replace(/\r\n/g, '\n').trim();
}
