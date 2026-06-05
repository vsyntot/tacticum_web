#!/usr/bin/env node

import { readdir, readFile, stat } from 'node:fs/promises';
import path from 'node:path';

const failures = [];
const knownDebts = [];
const componentCachePolicyPath = 'local/components/tacticum/component_cache_policy.json';
const productBlockPolicyPath = 'local/components/tacticum/product_block_policy.json';
const productBlockNames = [
  'hero',
  'fit-guide',
  'content-section',
  'architecture',
  'use-cases',
  'comparison',
  'procurement',
  'rollout',
  'proof',
  'faq',
  'lead-cta',
];
const allowedComponentCachePolicies = new Set([
  'component-html-cache',
  'delegated-child-cache',
  'delegated-service-cache',
  'page-shell-no-cache',
  'request-router-no-cache',
  'runtime-surface-no-cache',
]);
const noCachePolicies = new Set([
  'page-shell-no-cache',
  'request-router-no-cache',
  'runtime-surface-no-cache',
]);

const publicPageEntries = [
  'index.php',
  'about/index.php',
  'services/index.php',
  'price/index.php',
  'calculator/index.php',
  'contacts/index.php',
  'aiagents/index.php',
  'offer/index.php',
  'policies/index.php',
  'platform/index.php',
  'agents/index.php',
  'dev/index.php',
  'forum/index.php',
];

const productPageEntryAllowlist = new Map([
  ['platform/index.php', 'BPC-ARCH-001: product pages still use include renderer until tacticum:product.page migration'],
  ['agents/index.php', 'BPC-ARCH-001: product pages still use include renderer until tacticum:product.page migration'],
  ['dev/index.php', 'BPC-ARCH-001: product pages still use include renderer until tacticum:product.page migration'],
  ['forum/index.php', 'BPC-ARCH-001: product pages still use include renderer until tacticum:product.page migration'],
]);

const sizeDebtAllowlist = new Map();

const init = await readFile('local/php_interface/init.php', 'utf8');
if (/function\s+tacticum_|CIBlockElement|EventManager|Loader::includeModule/.test(init)) {
  failures.push('local/php_interface/init.php must stay a thin bootstrap without domain functions, iblock queries or event bodies.');
}
for (const [includeFile, reason] of [
  ['content_migrations.php', 'one-off migrations must run only through an explicit migration runner, not every request'],
  ['product_content.php', 'product content runtime must load lazily through product pages/tools; init should register only cache handlers'],
  ['product_page.php', 'product renderer/block partials must load lazily through tacticum:product.page'],
]) {
  if (init.includes(includeFile)) {
    failures.push(`local/php_interface/init.php must not eagerly include ${includeFile}; ${reason}.`);
  }
}
if (!init.includes('/local/php_interface/include/product_content_events.php')) {
  failures.push('local/php_interface/init.php must load product_content_events.php for lazy product content cache invalidation registration.');
}

if (!(await fileExists('local/php_interface/include/autoload.php'))) {
  failures.push('local/php_interface/include/autoload.php must register the Tacticum service namespace for local/lib classes.');
} else {
  const autoload = await readFile('local/php_interface/include/autoload.php', 'utf8');
  if (!/spl_autoload_register/.test(autoload) || !/Tacticum\\\\/.test(autoload) || !/local\/lib\/Tacticum/.test(autoload)) {
    failures.push('local/php_interface/include/autoload.php must register a local/lib/Tacticum autoload boundary.');
  }
}

const contentMigrations = await readFile('local/php_interface/include/content_migrations.php', 'utf8');
if (
  /tacticum_content_migration_fix_policy_contacts\(\);\s*$/m.test(contentMigrations)
  && !/TACTICUM_RUN_CONTENT_MIGRATIONS/.test(contentMigrations)
) {
  failures.push('local/php_interface/include/content_migrations.php must not auto-run one-off migrations without explicit TACTICUM_RUN_CONTENT_MIGRATIONS guard.');
}
const productContentEvents = await readFile('local/php_interface/include/product_content_events.php', 'utf8');
if (
  !/function\s+tacticum_register_product_content_cache_handlers/.test(productContentEvents)
  || !/tacticum_product_content_events_include_runtime/.test(productContentEvents)
  || !/\/local\/php_interface\/include\/product_content\.php/.test(productContentEvents)
) {
  failures.push('local/php_interface/include/product_content_events.php must register product content cache handlers and lazy-load product_content.php inside event callbacks.');
}

const componentDirectories = [];
await collectFiles('local/components/tacticum', componentDirectories, (file) => path.basename(file) === 'component.php');
for (const componentFile of componentDirectories) {
  const componentDir = path.dirname(componentFile);
  for (const requiredFile of ['component.php', '.parameters.php', '.description.php']) {
    const requiredPath = path.join(componentDir, requiredFile);
    if (!(await fileExists(requiredPath))) {
      failures.push(`${componentDir} must include ${requiredFile} for Bitrix local component metadata completeness.`);
    }
  }
}
await validateComponentCachePolicy(componentDirectories);
await validateProductBlockPolicy();

const leadCtaController = await readFile('local/components/tacticum/lead.cta/component.php', 'utf8');
if (
  !/Tacticum\\Component\\LeadCtaParams/.test(leadCtaController)
  || !/LeadCtaParams::prepare\(\$arParams\)/.test(leadCtaController)
  || /normalizeScenarioOptions|normalizeLeadContext|\$defaults\s*=/.test(leadCtaController)
) {
  failures.push('local/components/tacticum/lead.cta/component.php must remain a thin controller over Tacticum\\Component\\LeadCtaParams.');
}
if (!(await fileExists('local/lib/Tacticum/Component/LeadCtaParams.php'))) {
  failures.push('local/lib/Tacticum/Component/LeadCtaParams.php must exist after BPC-CMP-004 lead.cta parameter split.');
}

const componentFiles = [];
await collectFiles('local/components/tacticum', componentFiles, (file) => path.basename(file) === 'component.php');
for (const file of componentFiles) {
  const source = await readFile(file, 'utf8');
  if (/function\s+tacticum_[a-z0-9_]+\s*\(/i.test(source)) {
    failures.push(`${file} must not declare global tacticum_* helper functions; use component_helpers.php or local closures.`);
  }
}

const bitrixTemplateFiles = [];
await collectFiles('local/templates/tacticum/components', bitrixTemplateFiles, (file) => path.basename(file) === 'template.php');
for (const file of bitrixTemplateFiles) {
  const source = await readFile(file, 'utf8');
  const assetVars = Array.from(source.matchAll(/\$([A-Za-z_][A-Za-z0-9_]*)\s*=\s*Asset::getInstance\(\)/g))
    .map((match) => match[1]);
  const usesDirectAssetRegistration = /Asset::getInstance\(\)\s*->\s*add(?:Js|Css)\s*\(/.test(source)
    || assetVars.some((variable) => new RegExp(`\\$${variable}\\s*->\\s*add(?:Js|Css)\\s*\\(`).test(source));
  if (usesDirectAssetRegistration) {
    failures.push(`${file} must register component-owned JS/CSS through $this->addExternalJs/addExternalCss, not late Asset::getInstance()->addJs/addCss.`);
  }
}

for (const publicFile of publicPageEntries) {
  const source = await readFile(publicFile, 'utf8');
  if (/IncludeComponent\(\s*[\r\n\t ]*['"]bitrix:/m.test(source)) {
    failures.push(`${publicFile} must call local tacticum:* components for public page content.`);
  }
}

for (const publicFile of ['platform/index.php', 'agents/index.php', 'dev/index.php', 'forum/index.php']) {
  const source = await readFile(publicFile, 'utf8');
  const usesProductComponent = /IncludeComponent\(\s*[\r\n\t ]*['"]tacticum:product\.page['"]/m.test(source);
  const usesLegacyRenderer = /tacticum_product_page_data|tacticum_render_product_page/.test(source);
  if (!usesProductComponent) {
    const debt = productPageEntryAllowlist.get(publicFile);
    if (debt && usesLegacyRenderer) {
      knownDebts.push(`${publicFile}: ${debt}`);
    } else {
      failures.push(`${publicFile} must render product pages through tacticum:product.page or be explicitly allowlisted as BPC-ARCH-001 debt.`);
    }
  }
}

const restFiles = [];
await collectFiles('local/rest', restFiles, (file) => file.endsWith('.php'));
for (const file of restFiles) {
  const source = await readFile(file, 'utf8');
  const rendersHtmlOrAdminUi = /prolog_admin|IncludeComponent\(\s*[\r\n\t ]*['"]bitrix:rest\.hook|<html|<div\s|<section\s/i.test(source);
  if (!rendersHtmlOrAdminUi) {
    continue;
  }

  if (file === 'local/rest/index.php') {
    const protectedAdminRoute = /define\(\s*['"]ADMIN_SECTION['"]\s*,\s*true\s*\)/.test(source)
      && /prolog_admin\.php/.test(source)
      && /X-Robots-Tag:\s*noindex,\s*nofollow/.test(source)
      && /Cache-Control:\s*private,\s*no-store/.test(source)
      && /bitrix:rest\.hook/.test(source);
    if (!protectedAdminRoute) {
      failures.push('local/rest/index.php must stay an explicit admin-only non-indexable Bitrix REST hook route.');
    }
    continue;
  }

  failures.push(`${file} renders HTML/admin UI inside local/rest; REST namespace must stay JSON/API-only or use explicit admin-only noindex handling.`);
}

const offerCatalog = [
  await readFile('local/php_interface/include/offer_catalog.php', 'utf8'),
  await readFile('local/php_interface/include/offer_catalog_cache.php', 'utf8'),
].join('\n');
for (const pattern of [
  /TacticumOfferCatalogCache/,
  /TacticumOfferCatalogRepository/,
  /OnAfterIBlockElementAdd/,
  /OnAfterIBlockElementUpdate/,
  /OnAfterIBlockElementDelete/,
  /OnAfterIBlockElementSetPropertyValues/,
]) {
  if (!pattern.test(offerCatalog)) {
    failures.push(`local/php_interface/include/offer_catalog.php is missing ${pattern.source} cache/service hardening.`);
  }
}

const productPageFacade = await readFile('local/php_interface/include/product_page.php', 'utf8');
if (
  !/Tacticum\\Product\\Page\\/.test(productPageFacade)
  || !/function\s+tacticum_product_page_data/.test(productPageFacade)
  || !/function\s+tacticum_product_page_schema/.test(productPageFacade)
  || !/\/local\/php_interface\/include\/product_page_blocks\/page\.php/.test(productPageFacade)
  || /tacticum_product_content_bitrix_data|tacticum_product_content_is_minimum_renderable|tacticum_public_url|preg_match\(/.test(productPageFacade)
) {
  failures.push('local/php_interface/include/product_page.php must remain a thin facade over Tacticum\\Product\\Page services and product page block partials.');
}
for (const file of [
  'local/lib/Tacticum/Product/Page/Cta.php',
  'local/lib/Tacticum/Product/Page/DataProvider.php',
  'local/lib/Tacticum/Product/Page/Schema.php',
  'local/lib/Tacticum/Product/Page/Text.php',
]) {
  if (!(await fileExists(file))) {
    failures.push(`${file} must exist after BPC-ARCH-001 product page service split.`);
  }
}
for (const file of [
  'local/components/tacticum/product.hero/component.php',
  'local/components/tacticum/product.hero/templates/.default/template.php',
  'local/components/tacticum/product.lead.cta/component.php',
  'local/components/tacticum/product.lead.cta/templates/.default/template.php',
]) {
  if (!(await fileExists(file))) {
    failures.push(`${file} must exist after BPC-CMP-002 product hero/CTA block promotion.`);
  }
}
const productPageOrchestrator = await readFile('local/php_interface/include/product_page_blocks/page.php', 'utf8');
if (!/tacticum:product\.hero/.test(productPageOrchestrator) || !/tacticum:product\.lead\.cta/.test(productPageOrchestrator)) {
  failures.push('local/php_interface/include/product_page_blocks/page.php must delegate hero and lead CTA blocks to product block components.');
}
if (/data-product-block="hero"|data-product-block="lead-cta"/.test(productPageOrchestrator)) {
  failures.push('local/php_interface/include/product_page_blocks/page.php must not inline product hero or lead CTA markup; use product block components.');
}

const offerPageFacade = await readFile('local/php_interface/include/offer_page.php', 'utf8');
if (
  !/Tacticum\\Offer\\Page\\/.test(offerPageFacade)
  || !/function\s+tacticum_offer_page_resolve/.test(offerPageFacade)
  || !/function\s+tacticum_offer_page_apply_seo/.test(offerPageFacade)
  || !/function\s+tacticum_offer_page_component_params/.test(offerPageFacade)
  || /LocalRedirect|CHTTP|tacticum_offer_catalog_path_filters|preg_match\(/.test(offerPageFacade)
) {
  failures.push('local/php_interface/include/offer_page.php must remain a thin facade over Tacticum\\Offer\\Page services.');
}
for (const file of [
  'local/lib/Tacticum/Offer/Page/Query.php',
  'local/lib/Tacticum/Offer/Page/RequestSnapshot.php',
  'local/lib/Tacticum/Offer/Page/Resolver.php',
  'local/lib/Tacticum/Offer/Page/Response.php',
]) {
  if (!(await fileExists(file))) {
    failures.push(`${file} must exist after BPC-ARCH-004 offer page service split.`);
  }
}
if (await fileExists('local/lib/Tacticum/Offer/Page/RequestSnapshot.php')) {
  const requestSnapshot = await readFile('local/lib/Tacticum/Offer/Page/RequestSnapshot.php', 'utf8');
  if (!/Context::getCurrent\(\)/.test(requestSnapshot) || !/getRequest/.test(requestSnapshot)) {
    failures.push('local/lib/Tacticum/Offer/Page/RequestSnapshot.php must be the Bitrix Context request boundary for offer page routing.');
  }
}
for (const file of [
  'local/lib/Tacticum/Offer/Page/Query.php',
  'local/lib/Tacticum/Offer/Page/Resolver.php',
]) {
  if (await fileExists(file)) {
    const source = await readFile(file, 'utf8');
    if (/\$_(GET|REQUEST|SERVER)/.test(source)) {
      failures.push(`${file} must not read superglobals directly; use Tacticum\\Offer\\Page\\RequestSnapshot.`);
    }
  }
}

const seoFacade = await readFile('local/php_interface/include/seo_helpers.php', 'utf8');
if (
  !/Tacticum\\Seo\\/.test(seoFacade)
  || !/function\s+tacticum_apply_seo_defaults/.test(seoFacade)
  || !/function\s+tacticum_add_robots_meta/.test(seoFacade)
  || !/function\s+tacticum_faq_json_ld/.test(seoFacade)
  || /CIBlockElement|Loader::includeModule|Cache::createInstance|AddHeadString|json_encode/.test(seoFacade)
) {
  failures.push('local/php_interface/include/seo_helpers.php must remain a thin facade over Tacticum\\Seo services.');
}
for (const file of [
  'local/lib/Tacticum/Seo/FaqSchema.php',
  'local/lib/Tacticum/Seo/JsonLd.php',
  'local/lib/Tacticum/Seo/Meta.php',
]) {
  if (!(await fileExists(file))) {
    failures.push(`${file} must exist after BPC-ARCH-002 SEO helper service split.`);
  }
}

const calcRequestsFacade = await readFile('local/php_interface/include/calcrequests_rest.php', 'utf8');
if (
  !/Tacticum\\CalcRequests\\/.test(calcRequestsFacade)
  || !/function\s+tacticum_register_calcrequests_rest/.test(calcRequestsFacade)
  || /CIBlockElement|EventManager|Loader::includeModule|Configuration::getValue|Context::getCurrent/.test(calcRequestsFacade)
) {
  failures.push('local/php_interface/include/calcrequests_rest.php must remain a thin facade over Tacticum\\CalcRequests services.');
}
for (const file of [
  'local/lib/Tacticum/CalcRequests/Access.php',
  'local/lib/Tacticum/CalcRequests/CodeGenerator.php',
  'local/lib/Tacticum/CalcRequests/PropertyMapper.php',
  'local/lib/Tacticum/CalcRequests/Registrar.php',
  'local/lib/Tacticum/CalcRequests/Repository.php',
  'local/lib/Tacticum/CalcRequests/Response.php',
  'local/lib/Tacticum/CalcRequests/Runtime.php',
  'local/lib/Tacticum/CalcRequests/Service.php',
  'local/lib/Tacticum/CalcRequests/Validator.php',
]) {
  if (!(await fileExists(file))) {
    failures.push(`${file} must exist after BPC-ARCH-002 calcrequests service split.`);
  }
}

const restFacade = await readFile('local/rest/rest_helpers.php', 'utf8');
if (!/Tacticum\\Rest\\/.test(restFacade) || !/function\s+tacticum_rest_validate_config/.test(restFacade)) {
  failures.push('local/rest/rest_helpers.php must remain a compatibility facade over Tacticum\\Rest services.');
}
if (!(await fileExists('local/lib/Tacticum/Content/IblockRepository.php'))) {
  failures.push('local/lib/Tacticum/Content/IblockRepository.php must centralize shared content/API iblock reads.');
} else {
  const contentRepository = await readFile('local/lib/Tacticum/Content/IblockRepository.php', 'utf8');
  if (!/CIBlockElement::GetList/.test(contentRepository) || !/CIBlockSection::GetList/.test(contentRepository)) {
    failures.push('local/lib/Tacticum/Content/IblockRepository.php must own shared element and section iblock reads.');
  }
}
const restApiFacade = await readFile('local/lib/Tacticum/Rest/Api.php', 'utf8');
if (!/Tacticum\\Content\\IblockRepository/.test(restApiFacade) || /CIBlockElement::GetList|CIBlockElement::GetElementGroups|GetProperties\(/.test(restApiFacade)) {
  failures.push('local/lib/Tacticum/Rest/Api.php must delegate public API iblock reads to Tacticum\\Content\\IblockRepository.');
}
for (const file of [
  'local/lib/Tacticum/Rest/Api.php',
  'local/lib/Tacticum/Rest/Config.php',
  'local/lib/Tacticum/Rest/ConfigValidator.php',
  'local/lib/Tacticum/Rest/LeadContext.php',
  'local/lib/Tacticum/Rest/LeadPayload.php',
  'local/lib/Tacticum/Rest/Masker.php',
  'local/lib/Tacticum/Rest/Outbound.php',
  'local/lib/Tacticum/Rest/RateLimiter.php',
  'local/lib/Tacticum/Rest/Response.php',
  'local/lib/Tacticum/Rest/Security.php',
  'local/lib/Tacticum/Rest/StaffOrderPayload.php',
  'local/lib/Tacticum/Rest/StaffOrderText.php',
  'local/lib/Tacticum/Rest/StaffOrderWorkers.php',
  'local/lib/Tacticum/Rest/Text.php',
  'local/rest/endpoint_policy.json',
]) {
  if (!(await fileExists(file))) {
    failures.push(`${file} must exist after BPC REST helper/endpoint policy split.`);
  }
}
const publicApiFiles = [];
await collectFiles('local/api', publicApiFiles, (file) => file.endsWith('.php'));
for (const file of [
  ...publicApiFiles,
  'local/components/tacticum/content.detail/component.php',
  'local/components/tacticum/faq.section/component.php',
  'local/templates/tacticum/components/bitrix/news.list/aiagents/result_modifier.php',
  'local/templates/tacticum/components/bitrix/news.list/cases/result_modifier.php',
  'local/templates/tacticum/components/bitrix/news.list/price/result_modifier.php',
]) {
  const source = await readFile(file, 'utf8');
  if (/CIBlock(Element|Section)::|GetProperties\(|Loader::includeModule\(\s*['"]iblock['"]/.test(source)) {
    failures.push(`${file} must not perform raw iblock reads; use Tacticum\\Content\\IblockRepository through a facade/helper.`);
  }
}

const footer = await readFile('local/templates/tacticum/footer.php', 'utf8');
if (!/tacticum:contact\.modal/.test(footer)) {
  failures.push('local/templates/tacticum/footer.php must render the contact modal through tacticum:contact.modal.');
}

const budgetFiles = [];
for (const entry of [
  'index.php',
  '404.php',
  'about',
  'agents',
  'aiagents',
  'calculator',
  'contacts',
  'dev',
  'forum',
  'offer',
  'platform',
  'policies',
  'price',
  'services',
  'local/api',
  'local/components/tacticum',
  'local/php_interface/include',
  'local/lib',
  'local/rest',
  'local/templates/tacticum/components',
  'local/templates/tacticum/js',
  'local/templates/tacticum/styles',
]) {
  if (await fileExists(entry)) {
    await collectFiles(entry, budgetFiles, isBudgetedSourceFile);
  }
}

for (const file of [...new Set(budgetFiles)]) {
  if (isIgnoredBudgetFile(file)) {
    continue;
  }

  const source = await readFile(file, 'utf8');
  const lines = countLines(source);
  const budget = fileBudget(file);
  const debt = sizeDebtAllowlist.get(file);
  if (debt) {
    if (lines > debt.maxLines) {
      failures.push(`${file} is allowlisted as ${debt.gap} (${debt.reason}) at ${debt.maxLines} lines, but now has ${lines}. Split it or update the allowlist with review.`);
    } else if (lines > budget.maxLines) {
      knownDebts.push(`${file}: ${debt.gap} ${lines}/${debt.maxLines} lines (${debt.reason})`);
    }
    continue;
  }

  if (lines > budget.maxLines) {
    failures.push(`${file} has ${lines} lines; ${budget.label} budget is ${budget.maxLines}. Split into component/service/module or add a reviewed BPC allowlist entry.`);
  }
}

if (failures.length > 0) {
  console.error('Bitrix architecture check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

if (knownDebts.length > 0) {
  console.log(`Bitrix architecture check passed with ${knownDebts.length} tracked BPC debts.`);
} else {
  console.log('Bitrix architecture check passed.');
}

async function collectFiles(entry, files, predicate) {
  const entryStat = await stat(entry);
  if (entryStat.isDirectory()) {
    for (const child of await readdir(entry)) {
      await collectFiles(path.join(entry, child), files, predicate);
    }
    return;
  }

  if (!predicate || predicate(entry)) {
    files.push(entry);
  }
}

async function fileExists(file) {
  try {
    await stat(file);
    return true;
  } catch {
    return false;
  }
}

function isBudgetedSourceFile(file) {
  return /\.(php|js|css)$/.test(file);
}

function isIgnoredBudgetFile(file) {
  return file === 'local/php_interface/include/tacticum_config.php'
    || file === 'local/templates/tacticum/tailwind.generated.css'
    || file.startsWith('local/templates/tacticum/fonts/')
    || file.startsWith('local/tools/');
}

function fileBudget(file) {
  if (publicPageEntries.includes(file) || /^404\.php$/.test(file)) {
    return { maxLines: 220, label: 'public page entry' };
  }
  if (file.startsWith('local/components/tacticum/')) {
    if (path.basename(file) === 'component.php') {
      return { maxLines: 180, label: 'local component controller' };
    }
    if (['.parameters.php', '.description.php'].includes(path.basename(file))) {
      return { maxLines: 120, label: 'local component metadata' };
    }
    return { maxLines: 220, label: 'local component template' };
  }
  if (file.startsWith('local/templates/tacticum/components/')) {
    return { maxLines: 220, label: 'Bitrix component template override' };
  }
  if (file.startsWith('local/templates/tacticum/js/')) {
    return { maxLines: 350, label: 'template JS module' };
  }
  if (file.startsWith('local/templates/tacticum/styles/')) {
    return { maxLines: 600, label: 'template CSS file' };
  }
  if (file.startsWith('local/php_interface/include/product_data/')) {
    return { maxLines: 500, label: 'product fallback data file' };
  }
  if (file.startsWith('local/php_interface/include/')) {
    return { maxLines: 220, label: 'shared include/service facade' };
  }
  if (file.startsWith('local/lib/')) {
    return { maxLines: 220, label: 'local service class' };
  }
  if (file.startsWith('local/rest/')) {
    return { maxLines: 220, label: 'REST endpoint/helper' };
  }
  if (file.startsWith('local/api/')) {
    return { maxLines: 140, label: 'public GET API endpoint' };
  }
  return { maxLines: 300, label: 'source file' };
}

function countLines(source) {
  if (source === '') {
    return 0;
  }
  const lines = source.split(/\r\n|\r|\n/).length;
  return source.endsWith('\n') ? lines - 1 : lines;
}

async function validateProductBlockPolicy() {
  if (!(await fileExists(productBlockPolicyPath))) {
    failures.push(`${productBlockPolicyPath} must define product block component/nested-template boundaries.`);
    return;
  }

  let policyDocument;
  try {
    policyDocument = JSON.parse(await readFile(productBlockPolicyPath, 'utf8'));
  } catch (error) {
    failures.push(`${productBlockPolicyPath} must be valid JSON: ${error.message}`);
    return;
  }

  if (policyDocument.schemaVersion !== 1) {
    failures.push(`${productBlockPolicyPath} must use schemaVersion 1.`);
  }

  const blocks = policyDocument.blocks;
  if (!blocks || typeof blocks !== 'object' || Array.isArray(blocks)) {
    failures.push(`${productBlockPolicyPath} must contain a blocks object.`);
    return;
  }

  const expectedBlocks = new Set(productBlockNames);
  for (const blockName of Object.keys(blocks).sort()) {
    if (!expectedBlocks.has(blockName)) {
      failures.push(`${productBlockPolicyPath} contains stale policy for unknown product block ${blockName}.`);
    }
  }

  for (const blockName of productBlockNames) {
    const policy = blocks[blockName];
    if (!policy) {
      failures.push(`${productBlockPolicyPath} must include product block ${blockName}.`);
      continue;
    }

    await validateProductBlockPolicyEntry(blockName, policy);
  }
}

async function validateProductBlockPolicyEntry(blockName, policy) {
  if (!policy || typeof policy !== 'object' || Array.isArray(policy)) {
    failures.push(`${blockName} product block policy must be an object.`);
    return;
  }

  if (!['component', 'nested-template'].includes(policy.boundary)) {
    failures.push(`${blockName} product block policy has unsupported boundary ${policy.boundary}.`);
  }

  for (const field of ['owner', 'dataKey']) {
    if (typeof policy[field] !== 'string' || policy[field].trim() === '') {
      failures.push(`${blockName} product block policy must include a non-empty ${field}.`);
    }
  }

  if (!Array.isArray(policy.evidence) || policy.evidence.length === 0) {
    failures.push(`${blockName} product block policy must include evidence files.`);
    return;
  }

  const sources = [];
  for (const evidencePath of policy.evidence) {
    if (typeof evidencePath !== 'string' || evidencePath.trim() === '') {
      failures.push(`${blockName} product block policy evidence paths must be non-empty strings.`);
      continue;
    }
    if (!(await fileExists(evidencePath))) {
      failures.push(`${blockName} product block policy evidence file ${evidencePath} is missing.`);
      continue;
    }
    sources.push(await readFile(evidencePath, 'utf8'));
  }

  const combinedSource = sources.join('\n');
  if (!combinedSource.includes(`data-product-block="${blockName}"`)) {
    failures.push(`${blockName} product block policy evidence must expose data-product-block="${blockName}".`);
  }

  const requiredLiterals = Array.isArray(policy.requiredLiterals) ? policy.requiredLiterals : [];
  if (requiredLiterals.length === 0) {
    failures.push(`${blockName} product block policy must include requiredLiterals.`);
  }
  for (const literal of requiredLiterals) {
    if (typeof literal !== 'string' || literal.trim() === '') {
      failures.push(`${blockName} product block policy requiredLiterals must be non-empty strings.`);
      continue;
    }
    if (!combinedSource.includes(literal)) {
      failures.push(`${blockName} product block policy evidence is missing literal ${literal}.`);
    }
  }

  if (policy.boundary === 'component') {
    if (typeof policy.component !== 'string' || policy.component.trim() === '') {
      failures.push(`${blockName} component product block must include component name.`);
      return;
    }
    const componentDir = `local/components/tacticum/${policy.component}`;
    for (const requiredFile of ['component.php', '.parameters.php', '.description.php', 'templates/.default/template.php']) {
      if (!(await fileExists(path.join(componentDir, requiredFile)))) {
        failures.push(`${blockName} component product block is missing ${componentDir}/${requiredFile}.`);
      }
    }
  }

  if (policy.boundary === 'nested-template') {
    if (typeof policy.renderer !== 'string' || policy.renderer.trim() === '') {
      failures.push(`${blockName} nested-template product block must include renderer.`);
    } else if (!combinedSource.includes(policy.renderer)) {
      failures.push(`${blockName} nested-template product block evidence must include renderer ${policy.renderer}.`);
    }
  }
}

async function validateComponentCachePolicy(componentFiles) {
  if (!(await fileExists(componentCachePolicyPath))) {
    failures.push(`${componentCachePolicyPath} must define explicit cache/result policy for every local tacticum component.`);
    return;
  }

  let policyDocument;
  try {
    policyDocument = JSON.parse(await readFile(componentCachePolicyPath, 'utf8'));
  } catch (error) {
    failures.push(`${componentCachePolicyPath} must be valid JSON: ${error.message}`);
    return;
  }

  if (policyDocument.schemaVersion !== 1) {
    failures.push(`${componentCachePolicyPath} must use schemaVersion 1.`);
  }

  const policies = policyDocument.components;
  if (!policies || typeof policies !== 'object' || Array.isArray(policies)) {
    failures.push(`${componentCachePolicyPath} must contain a components object.`);
    return;
  }

  const componentNames = componentFiles.map((file) => path.basename(path.dirname(file))).sort();
  const componentNameSet = new Set(componentNames);
  for (const policyName of Object.keys(policies).sort()) {
    if (!componentNameSet.has(policyName)) {
      failures.push(`${componentCachePolicyPath} contains stale policy for missing component ${policyName}.`);
    }
  }

  for (const componentFile of componentFiles) {
    const componentName = path.basename(path.dirname(componentFile));
    const policy = policies[componentName];
    if (!policy) {
      failures.push(`${componentFile} must have a cache/result policy entry in ${componentCachePolicyPath}.`);
      continue;
    }

    validateComponentPolicyShape(componentName, policy);

    const componentSource = await readFile(componentFile, 'utf8');
    const componentTreeSource = await readSourceTree(path.dirname(componentFile), isPolicyEvidenceSource);
    const evidenceSource = await readPolicyEvidenceSource(componentName, policy.evidence);
    const combinedSource = [componentTreeSource, evidenceSource].join('\n');
    const markers = Array.isArray(policy.markers) ? policy.markers : [];
    for (const marker of markers) {
      if (typeof marker !== 'string' || marker.trim() === '') {
        failures.push(`${componentName} cache policy markers must be non-empty strings.`);
      } else if (!combinedSource.includes(marker)) {
        failures.push(`${componentName} cache policy marker "${marker}" is not present in component/evidence sources.`);
      }
    }

    if (policy.policy === 'component-html-cache' && !/StartResultCache\s*\(/.test(componentSource)) {
      failures.push(`${componentFile} is marked component-html-cache but does not call StartResultCache().`);
    }

    if (policy.policy === 'delegated-child-cache') {
      if (!/IncludeComponent\(\s*[\r\n\t ]*['"]bitrix:/m.test(combinedSource)) {
        failures.push(`${componentName} is marked delegated-child-cache but evidence does not include a bitrix:* child component.`);
      }
      if (!/['"]CACHE_TYPE['"]/.test(combinedSource) || !/['"]CACHE_TIME['"]/.test(combinedSource)) {
        failures.push(`${componentName} delegated-child-cache policy must pass CACHE_TYPE and CACHE_TIME to the child component.`);
      }
    }

    if (policy.policy === 'delegated-service-cache' && !/Cache::createInstance|startDataCache|initCache|StartTagCache|startTagCache/.test(combinedSource)) {
      failures.push(`${componentName} is marked delegated-service-cache but evidence does not include Bitrix Data\\Cache or managed-cache markers.`);
    }

    if (noCachePolicies.has(policy.policy) && /CIBlock(Element|Section)::GetList|Cache::createInstance|startDataCache|initCache/.test(componentSource)) {
      failures.push(`${componentFile} is marked ${policy.policy} but performs direct iblock/cache work in the component controller.`);
    }
  }
}

function validateComponentPolicyShape(componentName, policy) {
  if (!policy || typeof policy !== 'object' || Array.isArray(policy)) {
    failures.push(`${componentName} cache policy must be an object.`);
    return;
  }

  if (!allowedComponentCachePolicies.has(policy.policy)) {
    failures.push(`${componentName} cache policy "${policy.policy}" is not an allowed policy.`);
  }

  for (const field of ['owner', 'reason']) {
    if (typeof policy[field] !== 'string' || policy[field].trim() === '') {
      failures.push(`${componentName} cache policy must include a non-empty ${field}.`);
    }
  }

  if (!Array.isArray(policy.evidence) || policy.evidence.length === 0) {
    failures.push(`${componentName} cache policy must include at least one evidence path.`);
  }
}

async function readPolicyEvidenceSource(componentName, evidencePaths) {
  if (!Array.isArray(evidencePaths)) {
    return '';
  }

  const sources = [];
  for (const evidencePath of evidencePaths) {
    if (typeof evidencePath !== 'string' || evidencePath.trim() === '') {
      failures.push(`${componentName} cache policy evidence paths must be non-empty strings.`);
      continue;
    }

    if (!(await fileExists(evidencePath))) {
      failures.push(`${componentName} cache policy evidence path does not exist: ${evidencePath}`);
      continue;
    }

    sources.push(await readSourceTree(evidencePath, isPolicyEvidenceSource));
  }

  return sources.join('\n');
}

async function readSourceTree(entry, predicate) {
  const files = [];
  await collectFiles(entry, files, predicate);
  const sources = [];
  for (const file of files) {
    sources.push(await readFile(file, 'utf8'));
  }

  return sources.join('\n');
}

function isPolicyEvidenceSource(file) {
  return /\.(php|js|css|json)$/.test(file);
}
