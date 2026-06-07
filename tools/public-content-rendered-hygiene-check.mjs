#!/usr/bin/env node

import http from 'node:http';
import https from 'node:https';
import process from 'node:process';

const DEFAULT_PAGES = [
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

const FORBIDDEN_VISIBLE_PHRASES = [
  'Product fit',
  'Use cases',
  'Security / procurement',
  'Delivery layer',
  'Product workstreams',
  'Product-aware estimate',
  'Vendor trust',
  'Proof layer',
  'Platform assessment',
  'Agents pilot',
  'Dev workflow',
  'Forum launch',
  'Platform team',
  'Platform examples',
  'Agents examples',
  'Dev examples',
  'Forum examples',
  'Что не обещаем без assessment',
  'Deployment-модель',
  'deployment-модели',
  'пилотной evidence',
];

const FORBIDDEN_EXACT_LINES = ['fits', 'not_fits', 'start'];

function scanRenderedHtml(html, page = '<html>') {
  const issues = [];
  const lines = visibleTextLines(html);

  lines.forEach((line, index) => {
    for (const phrase of FORBIDDEN_VISIBLE_PHRASES) {
      if (line.includes(phrase)) {
        issues.push({ page, line: index + 1, rule: 'forbidden-visible-phrase', text: line });
      }
    }

    if (FORBIDDEN_EXACT_LINES.some((label) => line.toLowerCase() === label)) {
      issues.push({ page, line: index + 1, rule: 'forbidden-technical-label', text: line });
    }
  });

  return issues;
}

function visibleTextLines(html) {
  return html
    .replace(/<!--[\s\S]*?-->/g, '\n')
    .replace(/<script\b[\s\S]*?<\/script>/gi, '\n')
    .replace(/<style\b[\s\S]*?<\/style>/gi, '\n')
    .replace(/<noscript\b[\s\S]*?<\/noscript>/gi, '\n')
    .replace(/<[^>]+>/g, '\n')
    .split(/\r?\n/)
    .map((line) => htmlDecode(line).replace(/\s+/g, ' ').trim())
    .filter(Boolean);
}

function runSelfTest() {
  const safeHtml = `
    <section><p>Когда подходит продукт</p><h3>Подходит, если</h3></section>
    <section><p>Сценарии применения</p><h3>Примеры Dev</h3></section>
  `;
  const unsafeHtml = `
    <section><p>Product fit</p><h3>fits</h3><h3>not_fits</h3><h3>start</h3></section>
    <section><p>Security / procurement</p><h3>Platform assessment</h3></section>
  `;

  const safeIssues = scanRenderedHtml(safeHtml, '/safe/');
  if (safeIssues.length !== 0) {
    throw new Error(`Safe fixture failed:\n${formatIssues(safeIssues).join('\n')}`);
  }

  const unsafeIssues = scanRenderedHtml(unsafeHtml, '/unsafe/');
  for (const expected of ['Product fit', 'fits', 'not_fits', 'start', 'Security / procurement', 'Platform assessment']) {
    if (!unsafeIssues.some((issue) => issue.text.includes(expected))) {
      throw new Error(`Unsafe fixture missed forbidden visible text: ${expected}`);
    }
  }
}

function parseArgs(argv) {
  const options = {
    baseUrl: process.env.TACTICUM_PUBLIC_CONTENT_BASE_URL
      || process.env.TACTICUM_VISUAL_BASE_URL
      || 'https://tacticum.ru',
    pages: parseList(process.env.TACTICUM_PUBLIC_CONTENT_PAGES || process.env.TACTICUM_VISUAL_PAGES, DEFAULT_PAGES),
    selfTest: false,
  };

  for (const argument of argv.slice(2)) {
    if (argument === '--self-test') {
      options.selfTest = true;
      continue;
    }
    if (argument.startsWith('--base-url=')) {
      options.baseUrl = argument.slice('--base-url='.length);
      continue;
    }
    if (argument.startsWith('--pages=')) {
      options.pages = parseList(argument.slice('--pages='.length), DEFAULT_PAGES);
      continue;
    }
    throw new Error(`Unknown argument: ${argument}`);
  }

  options.baseUrl = normalizeBaseUrl(options.baseUrl);

  return options;
}

async function main() {
  const options = parseArgs(process.argv);

  if (options.selfTest) {
    runSelfTest();
    console.log('Public rendered content hygiene self-test passed.');
    return;
  }

  const issues = [];
  const results = [];

  for (const page of options.pages) {
    const url = new URL(page, options.baseUrl);
    const response = await requestText(url);
    const pageIssues = scanRenderedHtml(response.body, url.pathname);
    const ok = response.status >= 200 && response.status < 300 && pageIssues.length === 0;

    if (response.status < 200 || response.status >= 300) {
      issues.push({ page: url.pathname, line: 0, rule: 'http-status', text: `HTTP ${response.status}` });
    }
    issues.push(...pageIssues);
    results.push({ page: url.pathname, status: response.status, issues: pageIssues.length, ok });
  }

  for (const result of results) {
    console.log(`${result.ok ? 'OK' : 'FAIL'} ${result.page.padEnd(12)} status=${result.status} issues=${result.issues}`);
  }

  if (issues.length > 0) {
    console.error('');
    console.error(`Public rendered content hygiene check failed: ${issues.length} issue(s).`);
    for (const issue of formatIssues(issues)) {
      console.error(`- ${issue}`);
    }
    process.exit(1);
  }

  console.log('');
  console.log(`Public rendered content hygiene check passed: ${results.length} page(s).`);
}

function requestText(url, redirects = 0) {
  return new Promise((resolve, reject) => {
    const client = url.protocol === 'http:' ? http : https;
    const request = client.get(url, {
      headers: { 'User-Agent': 'tacticum-public-rendered-content-hygiene-check/1.0' },
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

function formatIssues(issues) {
  return issues.map((issue) => `${issue.page}:${issue.line} ${issue.rule}: ${issue.text}`);
}

function parseList(value, fallback) {
  const parsed = String(value || '').split(',').map((item) => item.trim()).filter(Boolean);

  return parsed.length > 0 ? parsed : fallback;
}

function normalizeBaseUrl(value) {
  const normalized = String(value || '').trim().replace(/\/+$/, '');
  if (!/^https?:\/\//i.test(normalized)) {
    throw new Error(`Invalid base URL: ${value}`);
  }

  return normalized;
}

function htmlDecode(value) {
  return value
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
}

try {
  await main();
} catch (error) {
  console.error(`Public rendered content hygiene check failed: ${error.message}`);
  process.exit(1);
}
