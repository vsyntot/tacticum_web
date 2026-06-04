#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

const DEFAULT_FILES = [
  'index.php',
  'calculator/index.php',
  'price/index.php',
  'services/index.php',
  'about/index.php',
  'aiagents/index.php',
  'local/components/tacticum/aiagents/templates/.default/template.php',
  'local/php_interface/include/product_data/platform.php',
  'local/php_interface/include/product_data/agents.php',
  'local/php_interface/include/product_data/dev.php',
  'local/php_interface/include/product_data/forum.php'
];

const SAFE_CONTEXT_PATTERN = /(?:не\s+явля[а-яёa-z-]*|нельзя\s+считать|до\s+проверки|после\s+проверки|перед\s+точной\s+сметой|после\s+этого\s+можно\s+обсуждать|уточн[а-яёa-z-]*|требован[а-яёa-z-]*|ограничен[а-яёa-z-]*|не\s+публикуем|нельзя\s+публиковать|без\s+публикации|без\s+спорных|неподтвержд[а-яёa-z-]*)/iu;

const HARD_CLAIM_RULES = [
  {
    id: 'quantified-claim',
    description: 'quantified public outcome claim',
    pattern: /(?:(?<!\[)\b\d+(?:[.,]\d+)?\s*%|\b\d+\+\s*(?:лет|years)\b|\b24\/7\b|\b99(?:[.,]\d+)?\s*%)/iu
  },
  {
    id: 'guarantee-claim',
    description: 'guarantee or error-free claim',
    pattern: /(?:гарант(?:ия|ии|ируем|ирует|ирован[а-яёa-z-]*)|guarantee[a-z-]*|безошибочн[а-яёa-z-]*|без\s+ошибок)/iu
  },
  {
    id: 'regulated-deployment-claim',
    description: 'deployment, SLA, registry or certification claim',
    pattern: /(?:\bSLA\b|on[-\s]?prem|(?<![A-Za-zА-Яа-яЁё])ПАК(?![A-Za-zА-Яа-яЁё])|сертифик[а-яёa-z-]*|ФСТЭК|ФСБ|ГОСТ|\bISO(?:\s?\d+)?\b|реестр[а-яёa-z-]*|registry)/u
  },
  {
    id: 'logo-partner-claim',
    description: 'logo or partner proof claim',
    pattern: /(?:логотип[а-яёa-z-]*|logo[a-z-]*|партн[её]р[а-яёa-z-]*|partner[a-z-]*)/iu
  }
];

function visibleLineText(line) {
  return line.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

function scanSource(source, fileLabel = '<source>') {
  const issues = [];
  const lines = source.split(/\r?\n/);

  lines.forEach((line, index) => {
    const normalized = visibleLineText(line);
    if (normalized === '' || SAFE_CONTEXT_PATTERN.test(normalized)) {
      return;
    }

    for (const rule of HARD_CLAIM_RULES) {
      if (!rule.pattern.test(normalized)) {
        continue;
      }

      issues.push({
        file: fileLabel,
        line: index + 1,
        rule: rule.id,
        description: rule.description,
        text: normalized
      });
    }
  });

  return issues;
}

function runSelfTest() {
  const safeSource = [
    "'note_text' => 'SaaS, on-prem, ПАК, SLA и реестры нельзя считать публичным обещанием до проверки требований и evidence.'",
    "'limitation' => 'Не является обещанием конкретного провайдера, SLA или сертификационного статуса.'",
    "'text' => 'Что нужно уточнить перед точной сметой: данные, интеграции, уровень поддержки, безопасность и нагрузка.'"
  ].join('\n');
  const unsafeSource = [
    "'text' => 'Гарантируем SLA 99.9% и сертификацию для enterprise-клиентов.'",
    "'text' => 'Покажем логотипы партнеров и 15+ лет опыта как proof.'"
  ].join('\n');

  const safeIssues = scanSource(safeSource, 'safe-fixture.php');
  if (safeIssues.length !== 0) {
    throw new Error(`Safe claims fixture failed:\n${formatIssues(safeIssues).join('\n')}`);
  }

  const unsafeIssues = scanSource(unsafeSource, 'unsafe-fixture.php');
  for (const expectedRule of ['guarantee-claim', 'quantified-claim', 'regulated-deployment-claim', 'logo-partner-claim']) {
    if (!unsafeIssues.some((issue) => issue.rule === expectedRule)) {
      throw new Error(`Unsafe claims fixture missed rule: ${expectedRule}`);
    }
  }
}

function formatIssues(issues) {
  return issues.map((issue) =>
    `${issue.file}:${issue.line} ${issue.rule}: ${issue.text}`
  );
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
    console.log('Product public claims self-test passed.');
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
        description: 'configured public copy source is missing',
        text: 'file does not exist'
      });
      continue;
    }

    const source = fs.readFileSync(filePath, 'utf8');
    issues.push(...scanSource(source, relativeFile));
  }

  if (issues.length > 0) {
    console.error('Product public claims check failed:');
    for (const issue of formatIssues(issues)) {
      console.error(`- ${issue}`);
    }
    process.exit(1);
  }

  console.log(`Product public claims check passed: ${files.length} files scanned.`);
}

try {
  main();
} catch (error) {
  console.error(`Product public claims check failed: ${error.message}`);
  process.exit(1);
}
