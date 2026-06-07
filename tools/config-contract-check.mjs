#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const file = 'local/php_interface/include/tacticum_config.example.php';
const source = await readFile(file, 'utf8');
const packageSource = await readFile('package.json', 'utf8');

const requiredPatterns = [
  [/['"]iblocks['"]\s*=>\s*\[/, 'iblocks registry'],
  [/['"]offer['"]\s*=>\s*\d+/, 'offer iblock key'],
  [/['"]offer_taxonomy_terms['"]\s*=>\s*\d+/, 'offer taxonomy terms iblock key'],
  [/['"]clients['"]\s*=>\s*\d+/, 'clients iblock key'],
  [/['"]policies['"]\s*=>\s*\d+/, 'policies iblock key'],
  [/['"]aiagents['"]\s*=>\s*\d+/, 'aiagents iblock key'],
  [/['"]products['"]\s*=>\s*\d+/, 'products iblock key'],
  [/['"]product_blocks['"]\s*=>\s*\d+/, 'product_blocks iblock key'],
  [/['"]product_use_cases['"]\s*=>\s*\d+/, 'product_use_cases iblock key'],
  [/['"]page_sections['"]\s*=>\s*\d+/, 'page_sections iblock key'],
  [/['"]page_blocks['"]\s*=>\s*\d+/, 'page_blocks iblock key'],
  [/['"]team_presets['"]\s*=>\s*\d+/, 'team_presets iblock key'],
  [/['"]team_preset_roles['"]\s*=>\s*\d+/, 'team_preset_roles iblock key'],
  [/['"]base_urls['"]\s*=>\s*\[/, 'base_urls section'],
  [/['"]AI_SERVICE_BASE_URL['"]\s*=>\s*['"]https:\/\//, 'HTTPS AI_SERVICE_BASE_URL example'],
  [/['"]TELEGRAM_RESOLVER_URL['"]\s*=>\s*['"]https:\/\//, 'HTTPS TELEGRAM_RESOLVER_URL example'],
  [/['"]content['"]\s*=>\s*\[/, 'content section'],
  [/['"]faq_section_fallback_ids['"]\s*=>\s*\[/, 'FAQ section fallback map'],
  [/['"]products['"]\s*=>\s*\[/, 'products section'],
  [/['"]source['"]\s*=>\s*['"]bitrix['"]/, 'products source mode'],
  [/['"]allow_fallback['"]\s*=>\s*false/, 'products fallback disabled'],
  [/['"]cache_ttl['"]\s*=>\s*\d+/, 'products cache_ttl'],
  [/['"]page_content['"]\s*=>\s*\[/, 'page_content section'],
  [/['"]source['"]\s*=>\s*['"]fallback['"]/, 'page_content fallback source'],
  [/['"]live_status['"]\s*=>\s*['"]live['"]/, 'page_content live status'],
  [/['"]allow_fallback['"]\s*=>\s*true/, 'page_content fallback enabled'],
  [/['"]offer['"]\s*=>\s*\[/, 'offer section'],
  [/['"]taxonomy_source['"]\s*=>\s*['"](?:fallback|auto|bitrix)['"]/, 'offer taxonomy source mode'],
  [/['"]taxonomy_cache_ttl['"]\s*=>\s*\d+/, 'offer taxonomy cache ttl'],
  [/['"]allow_taxonomy_fallback['"]\s*=>\s*(?:true|false)/, 'offer taxonomy fallback flag'],
  [/['"]price['"]\s*=>\s*\[/, 'price section'],
  [/['"]team_presets_source['"]\s*=>\s*['"](?:fallback|auto|bitrix)['"]/, 'team presets source mode'],
  [/['"]team_presets_cache_ttl['"]\s*=>\s*\d+/, 'team presets cache ttl'],
  [/['"]allow_team_presets_fallback['"]\s*=>\s*(?:true|false)/, 'team presets fallback flag'],
  [/['"]ai['"]\s*=>\s*\[/, 'ai section'],
  [/['"]endpoint_paths['"]\s*=>\s*\[/, 'ai endpoint paths section'],
  [/['"]chat_agent_sale['"]\s*=>\s*['"]\//, 'chat sale endpoint path'],
  [/['"]staff_sale['"]\s*=>\s*['"]\//, 'staff sale endpoint path'],
  [/['"]security['"]\s*=>\s*\[/, 'security section'],
  [/['"]csp_mode['"]\s*=>\s*['"](?:report-only|enforce)['"]/, 'CSP mode'],
  [/['"]rest['"]\s*=>\s*\[/, 'rest section'],
  [/['"]allowed_origins['"]\s*=>\s*\[/, 'allowed origins'],
  [/['"]trusted_proxies['"]\s*=>\s*\[/, 'trusted proxies'],
  [/['"]rate_limits['"]\s*=>\s*\[/, 'REST rate limit classes'],
  [/['"]CONFIG_HEALTH_GET['"]\s*=>\s*\[/, 'CONFIG_HEALTH_GET rate limit class'],
  [/['"]PUBLIC_LEAD_POST['"]\s*=>\s*\[/, 'PUBLIC_LEAD_POST rate limit class'],
  [/['"]PUBLIC_CHAT_POST['"]\s*=>\s*\[/, 'PUBLIC_CHAT_POST rate limit class'],
  [/['"]PUBLIC_STAFF_POST['"]\s*=>\s*\[/, 'PUBLIC_STAFF_POST rate limit class'],
  [/['"]SCOPED_PREFILL_POST['"]\s*=>\s*\[/, 'SCOPED_PREFILL_POST rate limit class'],
  [/['"]PUBLIC_RESOLVER_POST['"]\s*=>\s*\[/, 'PUBLIC_RESOLVER_POST rate limit class'],
  [/['"]LEGACY_ALIAS_POST['"]\s*=>\s*\[/, 'LEGACY_ALIAS_POST rate limit class'],
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
  'price:team-presets:migrate',
  'price:team-presets:migrate:apply',
  'price:team-presets:check',
  'price:team-presets:check:strict',
  'price:team-presets:finalize',
  'price:team-presets:cache-clear',
  'price:team-presets:cache-clear:dry-run',
  'offer:taxonomy:migrate',
  'offer:taxonomy:migrate:apply',
  'offer:taxonomy:check',
  'offer:taxonomy:check:strict',
  'offer:taxonomy:cache-clear',
  'offer:taxonomy:cache-clear:dry-run',
  'product:content:migrate',
  'product:content:migrate:apply',
  'content:storage:faq:migrate',
  'content:storage:faq:migrate:apply',
  'content:storage:services:seed',
  'content:storage:services:seed:apply',
  'content:storage:audit',
  'content:storage:audit:json',
  'content:storage:audit:strict',
  'content:storage:governance:check',
  'product:content:check',
  'product:content:check:strict',
  'product:content:cache-clear',
  'product:content:cache-clear:dry-run',
  'rest:endpoints:check',
  'rest:endpoints:self-test'
]) {
  if (!packageSource.includes(`"${scriptName}"`)) {
    console.error(`package.json is missing ${scriptName} script.`);
    process.exit(1);
  }
}

console.log('Config example contract check passed.');
