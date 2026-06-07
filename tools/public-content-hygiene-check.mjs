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
  'tools/content-storage-page-content-seed.php'
];

const MAPPER_FILE = 'local/lib/Tacticum/Product/ContentBlockMapper.php';

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
  'пилотной evidence'
];

const MAPPER_REQUIRED_LITERALS = [
  'normalizeFitGuideColumn',
  'normalizePublicBlockLabels',
  "'fits' => 'fits'",
  "'not_fits' => 'not_fits'",
  "'start' => 'start'",
  "['eyebrow', 'Product fit', 'Когда подходит продукт']",
  "['eyebrow', 'Use cases', 'Сценарии применения']",
  "['eyebrow', 'Security / procurement', 'Безопасность и закупка']"
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

function verifyMapperNormalizers() {
  const mapperPath = path.resolve(ROOT, MAPPER_FILE);
  const issues = [];

  if (!fs.existsSync(mapperPath)) {
    return [{
      file: MAPPER_FILE,
      line: 0,
      rule: 'missing-mapper',
      text: 'mapper file does not exist'
    }];
  }

  const source = fs.readFileSync(mapperPath, 'utf8');
  for (const literal of MAPPER_REQUIRED_LITERALS) {
    if (source.includes(literal)) {
      continue;
    }

    issues.push({
      file: MAPPER_FILE,
      line: 0,
      rule: 'missing-mapper-normalizer',
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
    "$this->block('dev-examples', 'product_card', 'Dev examples', 'Text')"
  ].join('\n');

  const safeIssues = scanSource(safeSource, 'safe-fixture.php');
  if (safeIssues.length !== 0) {
    throw new Error(`Safe fixture failed:\n${formatIssues(safeIssues).join('\n')}`);
  }

  const unsafeIssues = scanSource(unsafeSource, 'unsafe-fixture.php');
  for (const expected of ['Product fit', 'Use cases', 'Security / procurement', 'Platform assessment', 'Dev examples']) {
    if (!unsafeIssues.some((issue) => issue.text.includes(expected))) {
      throw new Error(`Unsafe fixture missed forbidden label: ${expected}`);
    }
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

  issues.push(...verifyMapperNormalizers());

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
