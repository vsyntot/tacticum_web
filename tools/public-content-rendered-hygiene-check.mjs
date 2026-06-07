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
const DEFAULT_CHECKED_BY = 'automated-public-content-hygiene-check';
const DEFAULT_COMMAND = 'npm run content:public-hygiene:rendered:prod';

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
  'Оставьте /aiagents/ для демо',
  'Legacy AI-bot entry',
  'product path',
  'Agents rollout',
];

const FORBIDDEN_EXACT_LINES = ['fits', 'not_fits', 'start'];

const ABOUT_FORBIDDEN_VISIBLE_PHRASES = [
  'product-first',
  'delivery-команд',
  'delivery практи',
  'backend, data/RAG',
  'quality gates',
  'production rollout',
  'runtime-сервисы',
  'достичь новых высот',
  'BERT, GPT, NLTK',
  'Hadoop, Spark, Kafka',
  'Tableau, Power BI',
  'передовые технологии и инструменты',
];

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

  if (normalizePagePath(page) === '/about/') {
    issues.push(...scanAboutRenderedHtml(html, lines, page));
  }

  return issues;
}

function scanAboutRenderedHtml(html, lines, page) {
  const issues = [];
  const visibleText = lines.join(' ');
  if (visibleText.includes('2025') && visibleText.includes('Сегодня')) {
    issues.push({
      page,
      line: 0,
      rule: 'about-stale-timeline-current',
      text: 'Rendered /about/ contains stale 2025 + Сегодня pairing'
    });
  }

  const ids = renderedIds(html);
  for (const [id, count] of Object.entries(countOccurrences(ids))) {
    if (count > 1) {
      issues.push({
        page,
        line: 0,
        rule: 'about-duplicate-id',
        text: `duplicate id="${id}" count=${count}`
      });
    }
  }

  const anchors = aboutAnchors(html);
  const knownIds = new Set(ids);
  for (const anchor of anchors) {
    if (!knownIds.has(anchor)) {
      issues.push({
        page,
        line: 0,
        rule: 'about-missing-anchor',
        text: `/about/#${anchor} has no rendered target`
      });
    }
  }

  if (anchors.includes('partners')) {
    const partnersText = textAroundId(html, 'partners');
    if (!/(партнер|партнёр|клиент|довер|экосистем)/i.test(partnersText)) {
      issues.push({
        page,
        line: 0,
        rule: 'about-misleading-partners-anchor',
        text: '/about/#partners points to a non-partner section'
      });
    }
  }

  lines.forEach((line, index) => {
    for (const phrase of ABOUT_FORBIDDEN_VISIBLE_PHRASES) {
      if (line.includes(phrase)) {
        issues.push({
          page,
          line: index + 1,
          rule: 'about-forbidden-visible-phrase',
          text: line
        });
      }
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
  const safeAboutHtml = `
    <nav>
      <a href="/about/#about-company">Кто мы</a>
      <a href="/about/#team">Команда</a>
      <a href="/about/#careers">Карьера</a>
      <a href="/about/#technology">Технологии</a>
    </nav>
    <section id="about-company"><h2>Кто мы?</h2></section>
    <section><span id="team"></span><h2>Наша команда</h2></section>
    <span id="careers"></span>
    <section><h2>Карьера в Tacticum</h2></section>
    <section id="technology"><h2>Технологические контуры</h2></section>
    <section><span>2025</span><h3>Переход к продуктовой линейке</h3></section>
    <section><span>Сейчас</span><h3>Текущий фокус</h3></section>
  `;
  const unsafeHtml = `
    <section><p>Product fit</p><h3>fits</h3><h3>not_fits</h3><h3>start</h3></section>
    <section><p>Security / procurement</p><h3>Platform assessment</h3></section>
    <section><h3>Оставьте /aiagents/ для демо</h3><p>Legacy AI-bot entry</p><p>product path for Agents rollout</p></section>
  `;
  const unsafeAboutHtml = `
    <nav>
      <a href="/about/#careers">Карьера</a>
      <a href="/about/#partners">Партнеры</a>
    </nav>
    <section id="about-company"><h2>Кто мы?</h2></section>
    <input id="about-company" name="company">
    <section id="partners"><h2>Технологические контуры</h2></section>
    <section><span>2025</span><h3>Сегодня</h3></section>
    <p>Почему product-first модель требует сильной delivery-команды</p>
    <p>Роли, аудит, журналирование, quality gates и production rollout.</p>
  `;

  const safeIssues = scanRenderedHtml(safeHtml, '/safe/');
  if (safeIssues.length !== 0) {
    throw new Error(`Safe fixture failed:\n${formatIssues(safeIssues).join('\n')}`);
  }
  const safeAboutIssues = scanRenderedHtml(safeAboutHtml, '/about/');
  if (safeAboutIssues.length !== 0) {
    throw new Error(`Safe about fixture failed:\n${formatIssues(safeAboutIssues).join('\n')}`);
  }

  const unsafeIssues = scanRenderedHtml(unsafeHtml, '/unsafe/');
  for (const expected of ['Product fit', 'fits', 'not_fits', 'start', 'Security / procurement', 'Platform assessment', 'Оставьте /aiagents/ для демо', 'Legacy AI-bot entry', 'product path']) {
    if (!unsafeIssues.some((issue) => issue.text.includes(expected))) {
      throw new Error(`Unsafe fixture missed forbidden visible text: ${expected}`);
    }
  }
  const unsafeAboutIssues = scanRenderedHtml(unsafeAboutHtml, '/about/');
  for (const expectedRule of ['about-stale-timeline-current', 'about-duplicate-id', 'about-missing-anchor', 'about-misleading-partners-anchor', 'about-forbidden-visible-phrase']) {
    if (!unsafeAboutIssues.some((issue) => issue.rule === expectedRule)) {
      throw new Error(`Unsafe about fixture missed rule: ${expectedRule}`);
    }
  }

  const evidence = buildEvidence(
    { command: DEFAULT_COMMAND, baseUrl: 'https://example.com', checkedBy: 'self-test' },
    [{ page: '/safe/', status: 200, issues: 0, ok: true }],
    [],
  );
  if (evidence.issues_found !== 0 || evidence.pages_checked !== 1 || evidence.result !== 'public rendered content hygiene passed') {
    throw new Error(`Evidence fixture failed: ${JSON.stringify(evidence)}`);
  }
  if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/.test(evidence.checked_at)) {
    throw new Error(`Evidence checked_at fixture failed: ${evidence.checked_at}`);
  }
}

function parseArgs(argv) {
  const options = {
    baseUrl: process.env.TACTICUM_PUBLIC_CONTENT_BASE_URL
      || process.env.TACTICUM_VISUAL_BASE_URL
      || 'https://tacticum.ru',
    checkedBy: process.env.TACTICUM_PUBLIC_CONTENT_CHECKED_BY || DEFAULT_CHECKED_BY,
    command: process.env.TACTICUM_PUBLIC_CONTENT_COMMAND || DEFAULT_COMMAND,
    json: process.env.TACTICUM_PUBLIC_CONTENT_JSON === '1',
    pages: parseList(process.env.TACTICUM_PUBLIC_CONTENT_PAGES || process.env.TACTICUM_VISUAL_PAGES, DEFAULT_PAGES),
    selfTest: false,
  };

  for (const argument of argv.slice(2)) {
    if (argument === '--self-test') {
      options.selfTest = true;
      continue;
    }
    if (argument === '--json') {
      options.json = true;
      continue;
    }
    if (argument.startsWith('--base-url=')) {
      options.baseUrl = argument.slice('--base-url='.length);
      continue;
    }
    if (argument.startsWith('--checked-by=')) {
      options.checkedBy = argument.slice('--checked-by='.length);
      continue;
    }
    if (argument.startsWith('--command=')) {
      options.command = argument.slice('--command='.length);
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
    const statusOk = response.status >= 200 && response.status < 300;
    const ok = statusOk && pageIssues.length === 0;
    const issueCount = pageIssues.length + (statusOk ? 0 : 1);

    if (!statusOk) {
      issues.push({ page: url.pathname, line: 0, rule: 'http-status', text: `HTTP ${response.status}` });
    }
    issues.push(...pageIssues);
    results.push({ page: url.pathname, status: response.status, issues: issueCount, ok });
  }

  const evidence = buildEvidence(options, results, issues);
  if (options.json) {
    console.log(JSON.stringify(evidence, null, 2));
    if (issues.length > 0) {
      process.exit(1);
    }
    return;
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

function buildEvidence(options, results, issues) {
  return {
    command: options.command,
    base_url: options.baseUrl,
    checked_at: isoDateTimeWithoutMilliseconds(new Date()),
    checked_by: options.checkedBy,
    pages_checked: results.length,
    issues_found: issues.length,
    result: issues.length === 0
      ? 'public rendered content hygiene passed'
      : 'public rendered content hygiene failed',
    pages: results.map((result) => ({
      page: result.page,
      status: result.status,
      issues: result.issues,
      ok: result.ok,
    })),
  };
}

function normalizePagePath(page) {
  const normalized = String(page || '').trim();
  if (normalized === '/about' || normalized === '/about/') {
    return '/about/';
  }

  return normalized;
}

function renderedIds(html) {
  return [...html.matchAll(/\bid=["']([^"']+)["']/gi)]
    .map((match) => htmlDecode(match[1]).trim())
    .filter(Boolean);
}

function countOccurrences(items) {
  return items.reduce((accumulator, item) => {
    accumulator[item] = (accumulator[item] || 0) + 1;
    return accumulator;
  }, {});
}

function aboutAnchors(html) {
  return [...html.matchAll(/\bhref=["']\/about\/#([^"']+)["']/gi)]
    .map((match) => htmlDecode(match[1]).trim())
    .filter(Boolean);
}

function textAroundId(html, id) {
  const pattern = new RegExp(`\\bid=["']${escapeRegExp(id)}["']`, 'i');
  const match = pattern.exec(html);
  if (!match) {
    return '';
  }
  const start = match.index;
  const end = Math.min(html.length, match.index + 1500);

  return visibleTextLines(html.slice(start, end)).join(' ');
}

function escapeRegExp(value) {
  return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function isoDateTimeWithoutMilliseconds(date) {
  return date.toISOString().replace(/\.\d{3}Z$/, 'Z');
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
