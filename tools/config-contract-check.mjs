#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const file = 'local/php_interface/include/tacticum_config.example.php';
const source = await readFile(file, 'utf8');
const packageSource = await readFile('package.json', 'utf8');

const requiredPatterns = [
  [/['"]iblocks['"]\s*=>\s*\[/, 'iblocks registry'],
  [/['"]offer['"]\s*=>\s*\d+/, 'offer iblock key'],
  [/['"]policies['"]\s*=>\s*\d+/, 'policies iblock key'],
  [/['"]aiagents['"]\s*=>\s*\d+/, 'aiagents iblock key'],
  [/['"]products['"]\s*=>\s*\d+/, 'products iblock key'],
  [/['"]product_blocks['"]\s*=>\s*\d+/, 'product_blocks iblock key'],
  [/['"]product_use_cases['"]\s*=>\s*\d+/, 'product_use_cases iblock key'],
  [/['"]base_urls['"]\s*=>\s*\[/, 'base_urls section'],
  [/['"]AI_SERVICE_BASE_URL['"]\s*=>\s*['"]https:\/\//, 'HTTPS AI_SERVICE_BASE_URL example'],
  [/['"]TELEGRAM_RESOLVER_URL['"]\s*=>\s*['"]https:\/\//, 'HTTPS TELEGRAM_RESOLVER_URL example'],
  [/['"]content['"]\s*=>\s*\[/, 'content section'],
  [/['"]faq_section_fallback_ids['"]\s*=>\s*\[/, 'FAQ section fallback map'],
  [/['"]products['"]\s*=>\s*\[/, 'products section'],
  [/['"]source['"]\s*=>\s*['"]bitrix['"]/, 'products source mode'],
  [/['"]allow_fallback['"]\s*=>\s*false/, 'products fallback disabled'],
  [/['"]cache_ttl['"]\s*=>\s*\d+/, 'products cache_ttl'],
  [/['"]ai['"]\s*=>\s*\[/, 'ai section'],
  [/['"]endpoint_paths['"]\s*=>\s*\[/, 'ai endpoint paths section'],
  [/['"]chat_agent_sale['"]\s*=>\s*['"]\//, 'chat sale endpoint path'],
  [/['"]staff_sale['"]\s*=>\s*['"]\//, 'staff sale endpoint path'],
  [/['"]security['"]\s*=>\s*\[/, 'security section'],
  [/['"]csp_mode['"]\s*=>\s*['"](?:report-only|enforce)['"]/, 'CSP mode'],
  [/['"]rest['"]\s*=>\s*\[/, 'rest section'],
  [/['"]allowed_origins['"]\s*=>\s*\[/, 'allowed origins'],
  [/['"]trusted_proxies['"]\s*=>\s*\[/, 'trusted proxies'],
];

const missing = requiredPatterns
  .filter(([pattern]) => !pattern.test(source))
  .map(([, label]) => label);

if (missing.length > 0) {
  console.error(`Config example contract check failed for ${file}:`);
  for (const label of missing) {
    console.error(`- missing ${label}`);
  }
  process.exit(1);
}

const insecureExample = source.match(/['"](?:AI_SERVICE_BASE_URL|TELEGRAM_RESOLVER_URL)['"]\s*=>\s*['"]http:\/\//);
if (insecureExample) {
  console.error('Config example must not use plain HTTP for external service URLs.');
  process.exit(1);
}

for (const scriptName of [
  'config:runtime:check',
  'config:runtime:check:json',
  'product:content:migrate',
  'product:content:migrate:apply',
  'product:content:check',
  'product:content:check:strict',
  'product:content:cache-clear',
  'product:content:cache-clear:dry-run'
]) {
  if (!packageSource.includes(`"${scriptName}"`)) {
    console.error(`package.json is missing ${scriptName} script.`);
    process.exit(1);
  }
}

console.log('Config example contract check passed.');
