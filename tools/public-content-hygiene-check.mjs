#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

const DEFAULT_FILES = [
  'local/php_interface/include/product_page_blocks/fit_guide.php',
  'local/php_interface/include/product_page_blocks/use_cases.php',
  'local/php_interface/include/product_page_blocks/procurement.php',
  'local/php_interface/include/product_data/platform.php',
  'local/php_interface/include/product_data/agents.php',
  'local/php_interface/include/product_data/dev.php',
  'local/php_interface/include/product_data/forum.php',
  'local/lib/Tacticum/PageContent/CalculatorRenderer.php',
  'local/components/tacticum/chat.surface/component.php',
  'local/components/tacticum/price.page/templates/.default/parts/calculator.php',
  'tools/content-storage-page-content-seed.php'
];

const MAPPER_FILE = 'local/lib/Tacticum/Product/ContentBlockMapper.php';
const PRODUCT_SERVICE_FILE = 'local/lib/Tacticum/Product/ContentService.php';
const PAGE_CONTENT_REPOSITORY_FILE = 'local/lib/Tacticum/PageContent/Repository.php';
const CHAT_SURFACE_FILE = 'local/components/tacticum/chat.surface/component.php';
const ABOUT_STATIC_SOURCE_FILES = [
  '.bottom.menu.php',
  'local/components/tacticum/about.page/templates/.default/parts/company-trust.php',
  'local/components/tacticum/about.page/templates/.default/parts/values-team.php',
  'local/components/tacticum/about.page/templates/.default/parts/stack-cta.php',
  'local/components/tacticum/about.page/templates/.default/parts/career-final.php'
];
const ABOUT_SEED_FILE = 'tools/content-storage-page-content-seed.php';

const FORBIDDEN_PUBLIC_LABELS = [
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
  'без assessment',
  'пилотной evidence',
  'Оставьте /aiagents/ для демо',
  'Legacy AI-bot entry',
  'product path',
  'Agents rollout'
];

const ABOUT_STATIC_FORBIDDEN_LABELS = [
  '/about/#partners',
  'id="partners"',
  '"FIELD_PREFIX" => "about"',
  'delivery практи',
  'под delivery',
  'quality gates',
  'production rollout',
  'runtime-сервисы',
  'достичь новых высот',
  'Технологии, с которыми мы работаем',
  'Карьера в Tacticum',
  'Мы не просто консультируем',
  'Инновационность',
  'AI/IT',
  'Team</div>',
  'BERT, GPT, NLTK',
  'Hadoop, Spark, Kafka',
  'Tableau, Power BI',
  'передовые технологии и инструменты',
];

const ABOUT_SEED_FORBIDDEN_LABELS = [
  'Почему product-first модель требует сильной delivery-команды',
  'Почему продуктовая модель требует сильной команды внедрения',
  'Ценности и подход',
  'Карьера в Tacticum',
  'Мы не просто консультируем',
  'Инновационность',
  'до production.',
  'backend, data/RAG',
  'scope, риски',
];

const MAPPER_REQUIRED_LITERALS = [
  'PublicCopyNormalizer::normalizeArray',
  'normalizeFitGuideColumn',
  'normalizePublicBlockLabels',
  "'fits' => 'fits'",
  "'not_fits' => 'not_fits'",
  "'start' => 'start'",
  "['eyebrow', 'Product fit', 'Когда подходит продукт']",
  "['eyebrow', 'Use cases', 'Сценарии применения']",
  "['eyebrow', 'Security / procurement', 'Безопасность и закупка']"
];

const PAGE_CONTENT_REQUIRED_LITERALS = [
  'use Tacticum\\Content\\PublicCopyNormalizer;',
  'PublicCopyNormalizer::normalizePageContentSection'
];

const PRODUCT_SERVICE_REQUIRED_LITERALS = [
  'use Tacticum\\Content\\PublicCopyNormalizer;',
  'PublicCopyNormalizer::normalizeArray'
];

const CHAT_SURFACE_REQUIRED_LITERALS = [
  '\\Tacticum\\Content\\PublicCopyNormalizer',
  'PublicCopyNormalizer::normalizeString'
];

function visibleLineText(line) {
  return line.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

function scanSource(source, fileLabel = '<source>') {
  const issues = [];
  const lines = source.split(/\r?\n/);

  lines.forEach((line, index) => {
    const normalized = visibleLineText(line);
    if (normalized === '') {
      return;
    }

    for (const label of FORBIDDEN_PUBLIC_LABELS) {
      if (!normalized.includes(label)) {
        continue;
      }

      issues.push({
        file: fileLabel,
        line: index + 1,
        rule: 'forbidden-public-label',
        text: normalized
      });
    }
  });

  return issues;
}

function scanAboutSource(source, fileLabel = '<source>', forbiddenLabels = ABOUT_STATIC_FORBIDDEN_LABELS) {
  const issues = [];
  const lines = source.split(/\r?\n/);

  lines.forEach((line, index) => {
    const normalized = visibleLineText(line);
    const raw = line.trim();
    if (normalized === '' && raw === '') {
      return;
    }

    for (const label of forbiddenLabels) {
      if (!normalized.includes(label) && !raw.includes(label)) {
        continue;
      }

      issues.push({
        file: fileLabel,
        line: index + 1,
        rule: 'about-forbidden-public-copy',
        text: raw.includes(label) ? raw : normalized
      });
    }
  });

  if (/2025[\s\S]{0,700}Сегодня/.test(source)) {
    issues.push({
      file: fileLabel,
      line: 0,
      rule: 'about-stale-timeline-source',
      text: 'source contains stale 2025 + Сегодня timeline pattern'
    });
  }

  return issues;
}

function scanFooterTechnologySource(source, fileLabel = '<source>') {
  const issues = [];
  const match = /["']Технологии["'][\s\S]{0,180}?["']([^"']+)["']/.exec(source);
  const href = match ? match[1].trim() : '';

  if (href !== '/services/#technology') {
    issues.push({
      file: fileLabel,
      line: 0,
      rule: 'footer-technology-link-target',
      text: href === ''
        ? 'footer technology link is missing'
        : `footer technology link points to ${href}; expected /services/#technology`
    });
  }

  return issues;
}

function verifyRequiredLiterals(relativeFile, requiredLiterals) {
  const sourcePath = path.resolve(ROOT, relativeFile);
  const issues = [];

  if (!fs.existsSync(sourcePath)) {
    return [{
      file: relativeFile,
      line: 0,
      rule: 'missing-file',
      text: 'required guard source file does not exist'
    }];
  }

  const source = fs.readFileSync(sourcePath, 'utf8');
  for (const literal of requiredLiterals) {
    if (source.includes(literal)) {
      continue;
    }

    issues.push({
      file: relativeFile,
      line: 0,
      rule: 'missing-public-copy-normalizer',
      text: `missing literal: ${literal}`
    });
  }

  return issues;
}

function runSelfTest() {
  const safeSource = [
    "'eyebrow' => 'Когда подходит продукт',",
    "'eyebrow' => 'Сценарии применения',",
    "'eyebrow' => 'Безопасность и закупка',",
    "$this->block('dev-examples', 'product_card', 'Примеры Dev', 'Пилоты для инженерных команд: процесс, слой знаний, правила и проверки качества.')"
  ].join('\n');

  const unsafeSource = [
    "'eyebrow' => 'Product fit',",
    "'eyebrow' => 'Use cases',",
    "'eyebrow' => 'Security / procurement',",
    "$this->block('platform', 'product_card', 'Platform assessment', 'Text')",
    "$this->block('dev-examples', 'product_card', 'Dev examples', 'Text')",
    "'title' => 'Оставьте /aiagents/ для демо',",
    "'text' => 'Legacy AI-bot entry полезен как быстрый демонстрационный вход.',",
    "'text' => 'Не заменяет product path для Agents rollout.',"
  ].join('\n');
  const unsafeAboutSource = [
    '<a href="/about/#partners">Партнеры</a>',
    '<div id="partners"><h2>Технологические контуры</h2></div>',
    '"FIELD_PREFIX" => "about",',
    '<span>2025</span><h4>Сегодня</h4>',
    '<p>Роли, аудит, журналирование, quality gates и production rollout</p>',
    '<p>Свяжитесь с нами, чтобы достичь новых высот.</p>'
  ].join('\n');
  const safeFooterSource = [
    'Array(',
    '  "Технологии",',
    '  "/services/#technology",',
    '  Array(),',
    ')'
  ].join('\n');
  const unsafeFooterSource = [
    'Array(',
    '  "Технологии",',
    '  "/about/#technology",',
    '  Array(),',
    ')'
  ].join('\n');

  const safeIssues = scanSource(safeSource, 'safe-fixture.php');
  if (safeIssues.length !== 0) {
    throw new Error(`Safe fixture failed:\n${formatIssues(safeIssues).join('\n')}`);
  }

  const unsafeIssues = scanSource(unsafeSource, 'unsafe-fixture.php');
  for (const expected of ['Product fit', 'Use cases', 'Security / procurement', 'Platform assessment', 'Dev examples', 'Оставьте /aiagents/ для демо', 'Legacy AI-bot entry', 'product path']) {
    if (!unsafeIssues.some((issue) => issue.text.includes(expected))) {
      throw new Error(`Unsafe fixture missed forbidden label: ${expected}`);
    }
  }
  const unsafeAboutIssues = scanAboutSource(unsafeAboutSource, 'unsafe-about.php');
  for (const expected of ['/about/#partners', 'id="partners"', '"FIELD_PREFIX" => "about"', 'quality gates', 'production rollout', 'достичь новых высот', '2025 + Сегодня']) {
    const found = expected === '2025 + Сегодня'
      ? unsafeAboutIssues.some((issue) => issue.rule === 'about-stale-timeline-source')
      : unsafeAboutIssues.some((issue) => issue.text.includes(expected));
    if (!found) {
      throw new Error(`Unsafe about fixture missed forbidden label: ${expected}`);
    }
  }

  const safeFooterIssues = scanFooterTechnologySource(safeFooterSource, 'safe-footer.php');
  if (safeFooterIssues.length !== 0) {
    throw new Error(`Safe footer fixture failed:\n${formatIssues(safeFooterIssues).join('\n')}`);
  }
  const unsafeFooterIssues = scanFooterTechnologySource(unsafeFooterSource, 'unsafe-footer.php');
  if (!unsafeFooterIssues.some((issue) => issue.rule === 'footer-technology-link-target')) {
    throw new Error('Unsafe footer fixture missed footer-technology-link-target');
  }
}

function formatIssues(issues) {
  return issues.map((issue) => `${issue.file}:${issue.line} ${issue.rule}: ${issue.text}`);
}

function parseArgs(argv) {
  const options = {
    files: [],
    selfTest: false
  };

  for (const argument of argv.slice(2)) {
    if (argument === '--self-test') {
      options.selfTest = true;
      continue;
    }
    if (argument.startsWith('--file=')) {
      options.files.push(argument.slice('--file='.length));
      continue;
    }
    throw new Error(`Unknown argument: ${argument}`);
  }

  return options;
}

function main() {
  const options = parseArgs(process.argv);

  if (options.selfTest) {
    runSelfTest();
    console.log('Public content hygiene self-test passed.');
    return;
  }

  const files = options.files.length > 0 ? options.files : DEFAULT_FILES;
  const issues = [];

  for (const relativeFile of files) {
    const filePath = path.resolve(ROOT, relativeFile);
    if (!fs.existsSync(filePath)) {
      issues.push({
        file: relativeFile,
        line: 0,
        rule: 'missing-file',
        text: 'configured public copy source is missing'
      });
      continue;
    }

    const source = fs.readFileSync(filePath, 'utf8');
    issues.push(...scanSource(source, relativeFile));
  }

  for (const relativeFile of ABOUT_STATIC_SOURCE_FILES) {
    const filePath = path.resolve(ROOT, relativeFile);
    if (!fs.existsSync(filePath)) {
      issues.push({
        file: relativeFile,
        line: 0,
        rule: 'missing-file',
        text: 'configured about source is missing'
      });
      continue;
    }

    issues.push(...scanAboutSource(fs.readFileSync(filePath, 'utf8'), relativeFile));
  }

  const bottomMenuPath = path.resolve(ROOT, '.bottom.menu.php');
  if (!fs.existsSync(bottomMenuPath)) {
    issues.push({
      file: '.bottom.menu.php',
      line: 0,
      rule: 'missing-file',
      text: 'bottom menu source is missing'
    });
  } else {
    issues.push(...scanFooterTechnologySource(fs.readFileSync(bottomMenuPath, 'utf8'), '.bottom.menu.php'));
  }

  const aboutSeedPath = path.resolve(ROOT, ABOUT_SEED_FILE);
  if (!fs.existsSync(aboutSeedPath)) {
    issues.push({
      file: ABOUT_SEED_FILE,
      line: 0,
      rule: 'missing-file',
      text: 'configured about page-content seed is missing'
    });
  } else {
    issues.push(...scanAboutSource(fs.readFileSync(aboutSeedPath, 'utf8'), ABOUT_SEED_FILE, ABOUT_SEED_FORBIDDEN_LABELS));
  }

  issues.push(...verifyRequiredLiterals(MAPPER_FILE, MAPPER_REQUIRED_LITERALS));
  issues.push(...verifyRequiredLiterals(PRODUCT_SERVICE_FILE, PRODUCT_SERVICE_REQUIRED_LITERALS));
  issues.push(...verifyRequiredLiterals(PAGE_CONTENT_REPOSITORY_FILE, PAGE_CONTENT_REQUIRED_LITERALS));
  issues.push(...verifyRequiredLiterals(CHAT_SURFACE_FILE, CHAT_SURFACE_REQUIRED_LITERALS));

  if (issues.length > 0) {
    console.error('Public content hygiene check failed:');
    for (const issue of formatIssues(issues)) {
      console.error(`- ${issue}`);
    }
    process.exit(1);
  }

  console.log(`Public content hygiene check passed: ${files.length} files scanned.`);
}

try {
  main();
} catch (error) {
  console.error(`Public content hygiene check failed: ${error.message}`);
  process.exit(1);
}
