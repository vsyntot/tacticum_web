#!/usr/bin/env node

import fs from 'node:fs';

const SITE = 'https://tacticum.ru';
const ROOT_SITEMAP_FILE = 'sitemap.xml';
const STATIC_SITEMAP_FILE = 'sitemap-basic-files.xml';
const ROOT_SITEMAP_URL = `${SITE}/sitemap.xml`;
const STATIC_SITEMAP_URL = `${SITE}/sitemap-basic-files.xml`;
const OFFER_SITEMAP_URL = `${SITE}/offer/sitemap.php`;
const MIN_LASTMOD_DATE = process.env.TACTICUM_SEO_MIN_LASTMOD || '2026-05-24';
const CHECK_HTTP = process.argv.includes('--http') || process.env.TACTICUM_SEO_CHECK_HTTP === '1';
const HTTP_BASE_URL = (process.env.TACTICUM_SEO_CHECK_BASE_URL || SITE).replace(/\/+$/, '');

const expectedStaticPages = new Map([
  ['index.php', '/'],
  ['about/index.php', '/about/'],
  ['platform/index.php', '/platform/'],
  ['agents/index.php', '/agents/'],
  ['dev/index.php', '/dev/'],
  ['forum/index.php', '/forum/'],
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
  STATIC_SITEMAP_URL,
  OFFER_SITEMAP_URL
];

const forbiddenRootSitemaps = [
  `${SITE}/sitemap-files.xml`,
  `${SITE}/sitemap-basic.xml`
];

const serviceEndpoints = [
  '/local/api/services.php',
  '/local/api/cases.php',
  '/local/rest/health_config.php'
];

const expectedTopMenuUrls = [
  '/price/',
  '/offer/',
  '/calculator/',
  '/aiagents/'
];

const expectedProductMenuUrls = [
  '/platform/',
  '/agents/',
  '/dev/',
  '/forum/'
];

const expectedProductScenarioValues = [
  'pilot',
  'architecture-session',
  'procurement-security',
  'team-delivery',
  'estimate'
];

const forbiddenProductSchemaFields = [
  'aggregateRating',
  'review',
  'offers',
  'price',
  'priceCurrency'
];

const errors = [];

function fail(message) {
  errors.push(message);
}

function read(path) {
  return fs.readFileSync(path, 'utf8');
}

function readOptional(path) {
  if (!fs.existsSync(path)) {
    return null;
  }

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

function assertNoUnexpected(actual, unexpected, label) {
  for (const value of unexpected) {
    if (actual.includes(value)) {
      fail(`${label} must not reference ${value}`);
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

function assertNoForbiddenSitemapLocs(values, label) {
  for (const value of values) {
    let pathname = '';
    try {
      pathname = new URL(value).pathname;
    } catch {
      fail(`${label} has invalid URL: ${value}`);
      continue;
    }

    if (pathname === '/404.php') {
      fail(`${label} must not include 404 page: ${value}`);
    }
    if (pathname.startsWith('/bitrix/')) {
      fail(`${label} must not include Bitrix system path: ${value}`);
    }
    if (pathname.startsWith('/local/')) {
      fail(`${label} must not include local service path: ${value}`);
    }
  }
}

function validateRootSitemap(xml, label) {
  const locs = extractTags(xml, 'loc');
  const lastmods = extractTags(xml, 'lastmod');

  assertNoMissing(locs, expectedSitemaps, label);
  assertNoUnexpected(locs, forbiddenRootSitemaps, label);
  assertHttps(locs, `${label} loc`);
  assertUnique(locs, `${label} loc`);
  assertLastmodCoverage(locs, lastmods, label);
  assertFreshLastmod(lastmods, label);
  assertNoForbiddenSitemapLocs(locs, `${label} loc`);
}

function validateStaticSitemap(xml, label) {
  const locs = extractTags(xml, 'loc');
  const lastmods = extractTags(xml, 'lastmod');

  assertHttps(locs, `${label} loc`);
  assertNoMissing(locs, expectedStaticUrls, label);
  assertUnique(locs, `${label} loc`);
  assertLastmodCoverage(locs, lastmods, label);
  assertFreshLastmod(lastmods, label);
  assertNoForbiddenSitemapLocs(locs, `${label} loc`);
}

function assertCanonicalPaths() {
  const canonicalPattern = /tacticum_apply_seo_defaults\s*\(\s*(['"])(\/[^'"]*)\1/s;

  for (const [file, expectedPath] of expectedStaticPages) {
    const source = file === 'offer/index.php'
      ? `${read(file)}\n${read('local/php_interface/include/offer_page.php')}`
      : read(file);
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
    read('services/.left.menu.php'),
    read('platform/.left.menu.php')
  ].join('\n');
  const bottomMenuSource = read('.bottom.menu.php');
  const servicesTemplateSource = read('local/templates/tacticum/components/bitrix/news.list/services/template.php');
  const headerSource = read('local/templates/tacticum/header.php');
  const footerSource = read('local/templates/tacticum/footer.php');

  if (fs.existsSync('services/.top.menu_ext.php')) {
    fail('services/.top.menu_ext.php must stay removed; use services/.left.menu.php for service children');
  }
  for (const [file, source] of [
    ['local/templates/tacticum/header.php', headerSource],
    ['local/templates/tacticum/footer.php', footerSource]
  ]) {
    if (!source.includes('"CHILD_MENU_TYPE" => "left"')) {
      fail(`${file} top/mobile menu must use left child menu type`);
    }
    if (!source.includes('"USE_EXT" => "N"')) {
      fail(`${file} top/mobile menu must keep USE_EXT=N to avoid section .top.menu_ext.php replacing root menu`);
    }
  }

  for (const url of expectedTopMenuUrls) {
    if (!menuSource.includes(`"${url}"`) && !menuSource.includes(`'${url}'`)) {
      fail(`top menu structure is missing money page ${url}`);
    }
    if (!bottomMenuSource.includes(`"${url}"`) && !bottomMenuSource.includes(`'${url}'`)) {
      fail(`bottom menu structure is missing money page ${url}`);
    }
  }
  for (const url of expectedProductMenuUrls) {
    if (!menuSource.includes(`"${url}"`) && !menuSource.includes(`'${url}'`)) {
      fail(`top/product menu structure is missing product page ${url}`);
    }
    if (!bottomMenuSource.includes(`"${url}"`) && !bottomMenuSource.includes(`'${url}'`)) {
      fail(`bottom menu structure is missing product page ${url}`);
    }
  }
  if (!servicesTemplateSource.includes('href="/offer/"') || !servicesTemplateSource.includes('Расчет проекта')) {
    fail('services block must include /offer/ as Расчет проекта');
  }
  for (const relativeFooterUrl of ['"services/"', '"price/"', '"offer/"', '"calculator/"', '"aiagents/"', '"platform/"', '"agents/"', '"dev/"', '"forum/"']) {
    if (bottomMenuSource.includes(relativeFooterUrl)) {
      fail(`bottom menu must use absolute public URLs, found ${relativeFooterUrl}`);
    }
  }
}

function assertDefaultSocialPreview() {
  const imagePath = 'local/templates/tacticum/images/og-default.jpg';
  if (!fs.existsSync(imagePath)) {
    fail(`${imagePath} is missing`);
  }

  const seoHelperSource = read('local/php_interface/include/seo_helpers.php');
  if (!seoHelperSource.includes("/images/og-default.jpg")) {
    fail('SEO helper must use og-default.jpg as the default social preview image');
  }
  if (!seoHelperSource.includes("?? 1200") || !seoHelperSource.includes("?? 630")) {
    fail('SEO helper default social preview dimensions must be 1200x630');
  }
}

function assertOfferCatalogRouting() {
  const rewriteSource = read('urlrewrite.php');
  const offerPageSource = read('offer/index.php');
  const offerPageControllerSource = read('local/php_interface/include/offer_page.php');
  const headerSource = read('local/templates/tacticum/header.php');
  const offerCatalogSource = read('local/php_interface/include/offer_catalog.php');
  const offerComponentSource = read('local/components/tacticum/offer/component.php');
  const offerListTemplate = read('local/components/tacticum/offer/templates/.default/list.php');
  const offerDetailTemplate = read('local/components/tacticum/offer/templates/.default/detail.php');
  const offerCatalogComponentSource = read('local/components/tacticum/offer.catalog/component.php');

  const catalogRuleIndex = rewriteSource.indexOf('#^/offer/catalog');
  const detailRuleIndex = rewriteSource.indexOf('#^/offer/([A-Za-z0-9_-]+');
  if (catalogRuleIndex === -1) {
    fail('urlrewrite.php is missing reserved /offer/catalog route');
  }
  if (detailRuleIndex === -1) {
    fail('urlrewrite.php is missing /offer/<code> detail route');
  }
  if (catalogRuleIndex !== -1 && detailRuleIndex !== -1 && catalogRuleIndex > detailRuleIndex) {
    fail('/offer/catalog route must be defined before /offer/<code> detail route');
  }
  if (!offerPageSource.includes('"tacticum:offer"')) {
    fail('offer/index.php must render section through tacticum:offer');
  }
  if (offerPageSource.includes('"tacticum:offer.catalog"') || offerPageSource.includes('"bitrix:news.detail"')) {
    fail('offer/index.php must not render list/detail branches directly; use tacticum:offer');
  }
  if (
    !offerPageSource.includes('offer_page.php')
    || !offerPageSource.includes('tacticum_offer_page_resolve')
    || !offerPageSource.includes('tacticum_offer_page_apply_redirects')
    || !offerPageSource.includes('tacticum_offer_page_apply_seo')
    || !offerPageSource.includes('tacticum_offer_page_apply_template')
    || !offerPageSource.includes('tacticum_offer_page_component_params')
  ) {
    fail('offer/index.php must stay a thin front controller around offer_page.php and tacticum:offer');
  }
  if (offerPageSource.includes('LocalRedirect(') || offerPageSource.includes('CHTTP::SetStatus')) {
    fail('offer/index.php must not own redirect/status logic; use offer_page.php');
  }
  if (!offerPageSource.includes('prolog_before.php') || !offerPageSource.includes('prolog_after.php')) {
    fail('offer/index.php must use Bitrix split prolog: prolog_before -> offer_page -> prolog_after');
  }
  if (offerPageSource.includes('TACTICUM_PAGE_ASSETS') || offerPageSource.includes('TACTICUM_BODY_CLASS')) {
    fail('offer/index.php must not set template globals; use offer_page.php page properties');
  }
  if (
    !offerPageControllerSource.includes('clear_cache')
    || !offerPageControllerSource.includes('tacticum_offer_catalog_path_filters')
    || !offerPageControllerSource.includes('tacticum_offer_page_apply_seo')
    || !offerPageControllerSource.includes('tacticum_offer_page_apply_template')
    || !offerPageControllerSource.includes('tacticum_page_assets')
    || !offerPageControllerSource.includes('tacticum_body_class')
    || !offerPageControllerSource.includes('tacticum_offer_page_component_params')
  ) {
    fail('offer_page.php must preserve Bitrix service params, pretty catalog parsing, SEO/template setup and component params');
  }
  if (!headerSource.includes("GetPageProperty('tacticum_page_assets'") || !headerSource.includes("GetPageProperty('tacticum_body_class'")) {
    fail('template header must support Bitrix page properties for page assets and body class');
  }
  if (headerSource.includes('TACTICUM_PAGE_ASSETS') || headerSource.includes('TACTICUM_BODY_CLASS')) {
    fail('template header must not support legacy TACTICUM_* globals; use Bitrix page properties');
  }
  if (!offerComponentSource.includes('MODE') || !offerComponentSource.includes('IncludeComponentTemplate')) {
    fail('tacticum:offer component must dispatch list/detail/not_found modes');
  }
  if (!offerListTemplate.includes('tacticum:offer.catalog')) {
    fail('tacticum:offer list template must render tacticum:offer.catalog');
  }
  if (!offerDetailTemplate.includes('bitrix:news.detail')) {
    fail('tacticum:offer detail template must render bitrix:news.detail');
  }
  if (!offerCatalogSource.includes('/offer/catalog/') || !offerCatalogSource.includes('tacticum_offer_catalog_url')) {
    fail('offer catalog helper must generate /offer/catalog/... URLs');
  }
  if (
    !offerCatalogSource.includes('final class TacticumOfferCatalogService')
    || !offerCatalogSource.includes('public static function items')
    || !offerCatalogSource.includes('public static function prepare')
    || !offerCatalogSource.includes('return TacticumOfferCatalogService::items')
    || !offerCatalogSource.includes('return TacticumOfferCatalogService::prepare')
  ) {
    fail('offer catalog helper must expose TacticumOfferCatalogService with compatibility wrappers');
  }
  if (!offerCatalogComponentSource.includes('tacticum_offer_catalog_prepare')) {
    fail('tacticum:offer.catalog component must use offer catalog helper');
  }
}

function assertPublicPageComponentization() {
  const productPages = [
    {
      file: 'platform/index.php',
      key: 'platform',
      dataFile: 'local/php_interface/include/product_data/platform.php'
    },
    {
      file: 'agents/index.php',
      key: 'agents',
      dataFile: 'local/php_interface/include/product_data/agents.php'
    },
    {
      file: 'dev/index.php',
      key: 'dev',
      dataFile: 'local/php_interface/include/product_data/dev.php'
    },
    {
      file: 'forum/index.php',
      key: 'forum',
      dataFile: 'local/php_interface/include/product_data/forum.php'
    }
  ];
  const ctaPages = [
    'index.php',
    'calculator/index.php',
    'price/index.php',
    'contacts/index.php',
    'about/index.php',
    'services/index.php'
  ];
  const faqHosts = [
    'index.php',
    'calculator/index.php',
    'price/index.php',
    'services/index.php',
    'local/components/tacticum/aiagents/templates/.default/template.php',
    'local/templates/tacticum/components/bitrix/news.detail/offer/template.php'
  ];
  const chatPages = [
    'index.php',
    'calculator/index.php',
    'price/index.php'
  ];
  const splitPrologAssetPages = [
    'index.php',
    'about/index.php',
    'calculator/index.php',
    'price/index.php',
    'contacts/index.php',
    'services/index.php',
    'platform/index.php',
    'agents/index.php',
    'dev/index.php',
    'forum/index.php',
    'aiagents/index.php',
    'offer/index.php',
    'policies/index.php',
    '404.php'
  ];
  const directContentListHosts = [
    'index.php',
    'about/index.php',
    'services/index.php',
    'price/index.php',
    'local/components/tacticum/aiagents/templates/.default/template.php'
  ];
  const directContentDetailHosts = [
    'policies/index.php'
  ];
  const publicComponentEntryPoints = [
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
    '404.php',
    'local/components/tacticum/aiagents/templates/.default/template.php'
  ];
  const productPageBlockFiles = [
    'local/php_interface/include/product_page_blocks/common.php',
    'local/php_interface/include/product_page_blocks/architecture.php',
    'local/php_interface/include/product_page_blocks/use_cases.php',
    'local/php_interface/include/product_page_blocks/procurement.php',
    'local/php_interface/include/product_page_blocks/comparison.php',
    'local/php_interface/include/product_page_blocks/rollout.php',
    'local/php_interface/include/product_page_blocks/proof.php',
    'local/php_interface/include/product_page_blocks/faq.php',
    'local/php_interface/include/product_page_blocks/page.php'
  ];
  const productRendererBootstrapSource = read('local/php_interface/include/product_page.php');
  const productContentSource = read('local/php_interface/include/product_content.php');
  const productContentSchemaSource = read('docs/workflow/product-content-schema-v1.json');
  const productContentSchemaCheckSource = read('tools/product-content-schema-check.mjs');
  const productMigrationSource = read('tools/product-content-migration.php');
  const productContentCheckSource = read('tools/product-content-check.php');
  const productContentCacheClearSource = read('tools/product-content-cache-clear.php');
  const productContentCacheClearEvidenceCheckSource = read('tools/product-content-cache-clear-evidence-check.mjs');
  const productSourceHttpCheckSource = read('tools/product-source-http-check.mjs');
  const productContentSwitchReadinessSource = read('tools/product-content-switch-readiness.mjs');
  const releasePublicPrecheckSource = read('tools/release-public-precheck.mjs');
  const releaseManualGatesHelperSource = read('tools/release-manual-gates-helper.mjs');
  const legacySaleAccessLogInventorySource = read('tools/legacy-sale-access-log-inventory.mjs');
  const manualSuccessFlowHelperSource = read('tools/manual-success-flow-helper.mjs');
  const metrikaGoalsHelperSource = read('tools/metrika-goals-helper.mjs');
  const bitrixAdminGateHelperSource = read('tools/bitrix-admin-gate-helper.mjs');
  const staffSaleGateHelperSource = read('tools/staff-sale-gate-helper.mjs');
  const initSource = read('local/php_interface/init.php');
  const configExampleSource = read('local/php_interface/include/tacticum_config.example.php');
  const configRuntimeCheckSource = read('tools/config-runtime-check.php');
  const productContentAdrSource = read('docs/adr/ADR-010-product-content-bitrix-model.md');
  const restHelpersSource = read('local/rest/rest_helpers.php');
  const restEndpointGuardCheckSource = read('tools/rest-endpoint-guard-check.mjs');
  const healthConfigSource = read('local/rest/health_config.php');
  const productRendererSource = [
    productRendererBootstrapSource,
    ...productPageBlockFiles.map((file) => read(file))
  ].join('\n');
  const homepageSource = read('index.php');
  const faqSectionComponentSource = read('local/components/tacticum/faq.section/component.php');
  const faqSectionTemplateSource = read('local/components/tacticum/faq.section/templates/.default/template.php');
  const leadCtaComponentSource = read('local/components/tacticum/lead.cta/component.php');
  const leadCtaTemplateSource = read('local/components/tacticum/lead.cta/templates/.default/template.php');
  const leadCtaFormTemplateSource = read('local/components/tacticum/lead.cta/templates/.default/form.php');
  const formEndpointSource = read('local/rest/tacticum_form.php');
  const analyticsSource = read('local/templates/tacticum/js/analytics.js');
  const formsSource = read('local/templates/tacticum/js/forms.js');
  const faqJsSource = read('local/templates/tacticum/js/faq.js');
  const visualSmokeSource = read('tools/visual-smoke.mjs');
  const releaseSignoffCheckSource = read('tools/release-signoff-check.mjs');
  const releaseSignoffSelfTestSource = read('tools/release-signoff-self-test.mjs');
  const packageSource = read('package.json');
  const productBlockPreviewWorkflow = read('docs/workflow/product-block-preview-workflow.md');
  const productContentSourceSwitchRunbook = read('docs/workflow/product-content-source-switch-runbook.md');
  const manualReleaseGatesRunbook = read('docs/workflow/manual-release-gates-runbook.md');
  const releaseSignoffGatesSource = read('docs/workflow/release-signoff-gates.md');

  assertLocalComponentMetadata();

  if (!productRendererSource.includes("'SCENARIO_OPTIONS'")) {
    fail('product page renderer must pass product scenario options into tacticum:lead.cta');
  }
  if (
    !productRendererBootstrapSource.includes('tacticum_product_page_cta_lead_context')
    || !productRendererBootstrapSource.includes('lead_page_role')
    || !productRendererBootstrapSource.includes('lead_product')
    || !productRendererSource.includes("'LEAD_CONTEXT' => tacticum_product_page_cta_lead_context")
  ) {
    fail('product page renderer must sanitize product CTA lead context before passing it into tacticum:lead.cta');
  }
  if (
    !productRendererBootstrapSource.includes('tacticum_product_page_safe_href')
    || !productRendererBootstrapSource.includes('tacticum_product_page_is_safe_href')
    || !productRendererBootstrapSource.includes('!str_starts_with($href, \'//\')')
    || !productRendererSource.includes('tacticum_product_page_safe_href($page[\'secondary_cta_href\']')
    || !productRendererSource.includes('tacticum_product_page_safe_href($procurement[\'cta_href\']')
    || !productRendererSource.includes('tacticum_product_page_safe_href($column[\'href\']')
  ) {
    fail('product page renderer must normalize product hrefs and block protocol-relative URLs at runtime');
  }
  if (
    !productRendererBootstrapSource.includes('tacticum_product_page_icon_class')
    || !productRendererBootstrapSource.includes('^ri-[a-z0-9]+(?:-[a-z0-9]+)*$')
    || !productRendererSource.includes('tacticum_product_page_icon_class($card[\'icon\']')
    || !productRendererSource.includes('tacticum_product_page_icon_class($item[\'icon\']')
  ) {
    fail('product page renderer must normalize product icon classes from content');
  }
  if (
    !productRendererBootstrapSource.includes('tacticum_product_page_columns_class')
    || !productRendererBootstrapSource.includes("'lg:grid-cols-2', 'lg:grid-cols-3', 'lg:grid-cols-4'")
    || !productRendererSource.includes('tacticum_product_page_columns_class($section[\'columns_class\']')
  ) {
    fail('product page renderer must normalize product grid column classes from content');
  }
  if (!productRendererBootstrapSource.includes('tacticum_product_content_bitrix_data') || !productRendererBootstrapSource.includes('tacticum_product_page_fallback_data')) {
    fail('product_page.php must support Bitrix product content with Git fallback');
  }
  if (!productRendererBootstrapSource.includes("$data['_product_code'] = $productCode") || !productContentSource.includes("'_product_code' => $productCode")) {
    fail('product Bitrix and fallback data must carry _product_code for product CTA context enforcement');
  }
  if (!productRendererBootstrapSource.includes('tacticum_product_content_is_minimum_renderable') || !productRendererBootstrapSource.includes('$source === \'bitrix\'')) {
    fail('product_page.php must guard auto Bitrix product content by minimum renderability and preserve bitrix-only mode');
  }
  const productContentIncludeIndex = initSource.indexOf('/include/product_content.php');
  const productPageIncludeIndex = initSource.indexOf('/include/product_page.php');
  if (productContentIncludeIndex < 0 || productPageIncludeIndex < 0 || productContentIncludeIndex > productPageIncludeIndex) {
    fail('init.php must load product_content.php before product_page.php');
  }
  for (const helperName of [
    'tacticum_product_content_source',
    'tacticum_product_content_bitrix_data',
    'tacticum_product_content_fetch_blocks',
    'tacticum_product_content_fetch_use_cases',
    'tacticum_product_content_is_minimum_renderable',
    'tacticum_product_content_completeness_diagnostics',
    'tacticum_product_content_cache_ttl',
    'tacticum_product_content_clear_cache',
    'tacticum_register_product_content_cache_handlers'
  ]) {
    if (!productContentSource.includes(helperName)) {
      fail(`product_content.php is missing ${helperName}`);
    }
  }
  if (!productContentSource.includes('Cache::createInstance()') || !productContentSource.includes('iblock_id_')) {
    fail('product_content.php must cache Bitrix product content with iblock managed tags');
  }
  if (!initSource.includes('tacticum_register_product_content_cache_handlers')) {
    fail('init.php must register product content cache invalidation handlers');
  }
  for (const configKey of ['products', 'product_blocks', 'product_use_cases']) {
    if (!configExampleSource.includes(`'${configKey}' => 0`)) {
      fail(`tacticum_config.example.php must document ${configKey} iblock key`);
    }
  }
  if (
    !configExampleSource.includes("'products' => [")
    || !configExampleSource.includes("'source' => 'bitrix'")
    || !configExampleSource.includes("'allow_fallback' => false")
    || !configExampleSource.includes("'cache_ttl' => 300")
  ) {
    fail('tacticum_config.example.php must document products.source=bitrix, disabled fallback and products.cache_ttl');
  }
  for (const rateClass of [
    'CONFIG_HEALTH_GET',
    'PUBLIC_LEAD_POST',
    'PUBLIC_CHAT_POST',
    'PUBLIC_STAFF_POST',
    'SCOPED_PREFILL_POST',
    'PUBLIC_RESOLVER_POST',
    'LEGACY_ALIAS_POST'
  ]) {
    if (!configExampleSource.includes(`'${rateClass}' => [`)) {
      fail(`tacticum_config.example.php must document REST rate limit class ${rateClass}`);
    }
  }
  if (
    !restHelpersSource.includes("in_array('products', $scopes, true)")
    || !restHelpersSource.includes("'products.source'")
    || !restHelpersSource.includes("'products.cache_ttl'")
  ) {
    fail('rest_helpers.php must validate products scope, products.source and products.cache_ttl');
  }
  if (
    !restHelpersSource.includes('tacticum_rest_rate_limit_classes')
    || !restHelpersSource.includes('tacticum_rest_rate_limit_by_class')
    || !restHelpersSource.includes("'rest.rate_limits'")
  ) {
    fail('rest_helpers.php must define and validate REST endpoint risk classes');
  }
  if (
    !restHelpersSource.includes('tacticum_rest_get_config_section_defaults')
    || !restHelpersSource.includes("'faq_section_fallback_ids'")
    || !restHelpersSource.includes("'home' => 17")
    || !restHelpersSource.includes('array_replace_recursive')
  ) {
    fail('rest_helpers.php must apply non-secret default content.faq_section_fallback_ids for FAQ wrapper fallback');
  }
  if (!healthConfigSource.includes("'products'")) {
    fail('health_config.php must include products scope in config validation');
  }
  for (const runtimeConfigNeedle of [
    'tacticum_rest_validate_config',
    'tacticum_config_runtime_check_endpoint_path',
    'tacticum_config_runtime_check_url_status',
    'source_config',
    'cache_ttl_config',
    'csp_mode_config',
    'faq_section_fallback_ids_count',
    'allowed_origins_count',
    'rate_limit_classes_count',
    'rate_limits_override_count',
    'rate_limits_config',
    'JSON_UNESCAPED_SLASHES'
  ]) {
    if (!configRuntimeCheckSource.includes(runtimeConfigNeedle)) {
      fail(`config-runtime-check.php must include ${runtimeConfigNeedle}`);
    }
  }
  for (const secretNeedle of ['password', 'secret', 'token', 'cookie', 'raw_payload', 'raw_response']) {
    const secretKeyPattern = new RegExp(`['"]${secretNeedle}['"]\\s*=>`);
    if (secretKeyPattern.test(configRuntimeCheckSource)) {
      fail(`config-runtime-check.php must not include secret-oriented output key ${secretNeedle}`);
    }
  }
  if (!packageSource.includes('"config:runtime:check"') || !packageSource.includes('"config:runtime:check:json"')) {
    fail('package.json must expose config runtime check scripts');
  }
  if (
    !packageSource.includes('"rest:endpoints:check"')
    || !restEndpointGuardCheckSource.includes('tacticum_rest_validate_origin(')
    || !restEndpointGuardCheckSource.includes('tacticum_rest_rate_limit_by_class(')
    || !restEndpointGuardCheckSource.includes('tacticum_rest_check_csrf(')
    || !restEndpointGuardCheckSource.includes('PUBLIC_LEAD_POST')
  ) {
    fail('REST endpoint guard checker must enforce public endpoint origin/rate/CSRF order');
  }
  if (!releaseSignoffGatesSource.includes('npm run config:runtime:check')) {
    fail('release-signoff-gates.md must require config:runtime:check for ignored runtime config evidence');
  }
  for (const productContentIblock of ['tacticum_products', 'tacticum_product_blocks', 'tacticum_product_use_cases']) {
    if (!productMigrationSource.includes(productContentIblock)) {
      fail(`product-content-migration.php must manage ${productContentIblock}`);
    }
  }
  if (!productMigrationSource.includes('ensureExistingIblockProductRelations') || !productMigrationSource.includes('--update-seed-content')) {
    fail('product-content-migration.php must support product relations and controlled seed updates');
  }
  for (const checkNeedle of [
    'tacticum_product_content_bitrix_data',
    'tacticum_product_content_is_minimum_renderable',
    'missing_to_be_blocks',
    'ALLOWED_LEAD_CONTEXT_KEYS',
    'isSafeUrl',
    '!str_starts_with($value, \'//\')',
    'validateIconClass',
    'single RemixIcon class token',
    'ALLOWED_COLUMNS_CLASSES',
    'validateColumnsClass',
    'checkRelationProperties',
    '--strict'
  ]) {
    if (!productContentCheckSource.includes(checkNeedle)) {
      fail(`product-content-check.php is missing ${checkNeedle}`);
    }
  }
  if (!productContentSchemaSource.includes('"lead_context_allowed_keys"') || !productContentSchemaSource.includes('"lead_product"')) {
    fail('product-content-schema-v1.json must constrain product CTA lead_context allowed keys');
  }
  if (!productContentSchemaSource.includes('"blocked_url_prefixes"') || !productContentSchemaSource.includes('"//"')) {
    fail('product-content-schema-v1.json must document blocked product URL prefixes');
  }
  if (!productContentSchemaSource.includes('"icon_class_pattern"') || !productContentSchemaSource.includes('^ri-[a-z0-9]+(?:-[a-z0-9]+)*$')) {
    fail('product-content-schema-v1.json must document safe product icon class pattern');
  }
  if (!productContentSchemaSource.includes('"allowed_columns_classes"') || !productContentSchemaSource.includes('"lg:grid-cols-4"')) {
    fail('product-content-schema-v1.json must document allowed product grid column classes');
  }
  if (!productContentSchemaCheckSource.includes('isSafeUrl') || !productContentSchemaCheckSource.includes('blocked_url_prefixes') || !productContentSchemaCheckSource.includes("!value.startsWith('//')")) {
    fail('product-content-schema-check.mjs must block protocol-relative product URLs');
  }
  if (!productContentSchemaCheckSource.includes('validateIconClass') || !productContentSchemaCheckSource.includes('single RemixIcon class token')) {
    fail('product-content-schema-check.mjs must validate optional product icon classes');
  }
  if (!productContentSchemaCheckSource.includes('validateColumnsClass') || !productContentSchemaCheckSource.includes('allowed_columns_classes')) {
    fail('product-content-schema-check.mjs must validate product grid column classes');
  }
  if (!packageSource.includes('"product:content:check"') || !packageSource.includes('"product:content:check:strict"')) {
    fail('package.json must expose product content check scripts');
  }
  for (const cacheClearNeedle of [
    'tacticum_product_content_clear_cache',
    'tacticum_product_content_related_iblock_ids',
    '--dry-run',
    '--json',
    'configured_source',
    'fallback_allowed',
    'JSON_UNESCAPED_SLASHES',
    'Managed tags'
  ]) {
    if (!productContentCacheClearSource.includes(cacheClearNeedle)) {
      fail(`product-content-cache-clear.php is missing ${cacheClearNeedle}`);
    }
  }
  for (const cacheClearEvidenceNeedle of [
    'cache_cleared must be false for dry-run evidence',
    'managed_tags must include',
    'FORBIDDEN_KEYS',
    'PII-like email value',
    'Product content cache-clear evidence self-test passed'
  ]) {
    if (!productContentCacheClearEvidenceCheckSource.includes(cacheClearEvidenceNeedle)) {
      fail(`product-content-cache-clear-evidence-check.mjs is missing ${cacheClearEvidenceNeedle}`);
    }
  }
  if (!productContentSource.includes('$tagIblockIds = $iblockId > 0 ? [$iblockId] : tacticum_product_content_related_iblock_ids()')) {
    fail('tacticum_product_content_clear_cache() must clear all product managed tags when called without a specific iblock ID');
  }
  if (/if\s*\(\s*&&/.test(productContentSource)) {
    fail('product_content.php must not contain a leading && inside an if condition');
  }
  if (
    !packageSource.includes('"product:content:cache-clear"')
    || !packageSource.includes('"product:content:cache-clear:dry-run"')
    || !packageSource.includes('"product:content:cache-clear:dry-run:json"')
    || !packageSource.includes('"product:content:cache-clear:evidence:check"')
    || !packageSource.includes('"product:content:cache-clear:evidence:self-test"')
    || !packageSource.includes('product:content:cache-clear:evidence:self-test && npm run product:public-claims:self-test')
  ) {
    fail('package.json must expose product content cache clear scripts');
  }
  for (const switchReadinessNeedle of [
    'TACTICUM_PRODUCT_SWITCH_BASE_URL',
    'health_config.php',
    'data-product-source=bitrix',
    'data-product-code',
    'lead_product',
    'products.source=bitrix',
    'product:content:cache-clear:dry-run',
    'product:content:cache-clear:evidence:check',
    'unsafe_hrefs',
    'rollback_steps',
    'required_pre_switch_evidence'
  ]) {
    if (!productContentSwitchReadinessSource.includes(switchReadinessNeedle)) {
      fail(`product-content-switch-readiness.mjs must include ${switchReadinessNeedle}`);
    }
  }
  if (!packageSource.includes('"product:content:switch-readiness:prod"') || !packageSource.includes('product-content-switch-readiness.mjs')) {
    fail('package.json must expose product:content:switch-readiness:prod for Bitrix source switch readiness');
  }
  for (const switchRunbookNeedle of [
    'products.source=bitrix',
    'npm run product:content:check:strict',
    'npm run product:source:http:prod',
    'Rollback',
    'Admin / Content Review'
  ]) {
    if (!productContentSourceSwitchRunbook.includes(switchRunbookNeedle)) {
      fail(`product-content-source-switch-runbook.md must include ${switchRunbookNeedle}`);
    }
  }
  if (!productContentAdrSource.includes('auto|bitrix|fallback') || !productContentAdrSource.includes('`products`') || !productContentAdrSource.includes('`product_blocks`') || !productContentAdrSource.includes('`product_use_cases`')) {
    fail('ADR-010 must document product source modes and product iblock model');
  }
  if (!productRendererBootstrapSource.includes('/local/php_interface/include/product_page_blocks/page.php')) {
    fail('product_page.php must load product page renderer blocks from local/php_interface/include/product_page_blocks');
  }
  if (productRendererBootstrapSource.includes('function tacticum_product_page_render_') || productRendererBootstrapSource.includes('function tacticum_render_product_page')) {
    fail('product_page.php must stay a bootstrap/helpers file; visual render functions belong in product_page_blocks');
  }
  for (const blockFile of productPageBlockFiles) {
    if (!fs.existsSync(blockFile)) {
      fail(`${blockFile} is missing`);
    }
  }
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
    'lead-cta'
  ];
  for (const blockName of productBlockNames) {
    if (!productRendererSource.includes(`data-product-block="${blockName}"`)) {
      fail(`product page renderer must expose data-product-block="${blockName}" for design-system QA and handoff`);
    }
  }
  if (!productRendererSource.includes('data-product-source=')) {
    fail('product page renderer must expose data-product-source for Bitrix/fallback runtime verification');
  }
  if (!productRendererSource.includes('data-product-code=')) {
    fail('product page renderer must expose data-product-code for product route/runtime traceability');
  }
  if (!visualSmokeSource.includes('expectProductBlocks') || !visualSmokeSource.includes('captureProductBlocks') || !visualSmokeSource.includes('captureProductBlockPreviews') || !visualSmokeSource.includes('productBlocks') || !visualSmokeSource.includes('productBlockErrors') || !visualSmokeSource.includes('productBlockScreenshots')) {
    fail('visual-smoke must expose rendered product block inventory and errors for product pages');
  }
  if (!visualSmokeSource.includes('TACTICUM_EXPECT_PRODUCT_SOURCE') || !visualSmokeSource.includes('product source mismatch')) {
    fail('visual-smoke must support expected product source verification');
  }
  if (!visualSmokeSource.includes('data-product-code') || !visualSmokeSource.includes('product code mismatch')) {
    fail('visual-smoke must support product code verification for product pages');
  }
  if (!visualSmokeSource.includes('lead_product') || !visualSmokeSource.includes('product lead context mismatch')) {
    fail('visual-smoke must support product lead context verification for product pages');
  }
  const productIconLinesWithoutHidden = productRendererSource
    .split(/\r?\n/)
    .filter((line) => line.includes('<i ') && !line.includes('aria-hidden="true"'));
  if (productIconLinesWithoutHidden.length > 0) {
    fail('product page renderer decorative icons must include aria-hidden="true"');
  }
  if (!visualSmokeSource.includes('product decorative icons must be aria-hidden')) {
    fail('visual-smoke must verify decorative product icons are hidden from assistive tech');
  }
  if (!visualSmokeSource.includes('product links must not use protocol-relative hrefs')) {
    fail('visual-smoke must verify product links do not render protocol-relative hrefs');
  }
  if (!visualSmokeSource.includes('homepageRouter') || !visualSmokeSource.includes('missing homepage router blocks') || !visualSmokeSource.includes('data-home-product-link')) {
    fail('visual-smoke must verify rendered homepage router markers');
  }
  if (!visualSmokeSource.includes('homepageCalculator') || !visualSmokeSource.includes('homepage calculator chat log must expose role=log') || !visualSmokeSource.includes('aria-valuenow 35,65,85')) {
    fail('visual-smoke must verify rendered homepage calculator accessibility');
  }
  if (!visualSmokeSource.includes('pageFaqErrors') || !visualSmokeSource.includes('expected rendered FAQ items') || !visualSmokeSource.includes('data-faq-section-status="missing"')) {
    fail('visual-smoke must verify rendered FAQ sections on public FAQ pages');
  }
  if (
    !productSourceHttpCheckSource.includes('data-product-source')
    || !productSourceHttpCheckSource.includes('data-product-code')
    || !productSourceHttpCheckSource.includes('TACTICUM_EXPECT_PRODUCT_SOURCE')
    || !productSourceHttpCheckSource.includes('unsafeHrefValues')
    || !productSourceHttpCheckSource.includes('protocol-relative or backslash product hrefs are not allowed')
  ) {
    fail('product-source-http-check.mjs must support server-safe product source/code verification');
  }
  if (!releasePublicPrecheckSource.includes('unsafeHrefValues') || !releasePublicPrecheckSource.includes('safe hrefs')) {
    fail('release-public-precheck.mjs must verify product links do not render protocol-relative hrefs');
  }
  if (!packageSource.includes('"product:block-previews"') || !packageSource.includes('TACTICUM_CAPTURE_PRODUCT_BLOCKS=1')) {
    fail('package.json must expose a product:block-previews script for design/QA screenshot handoff');
  }
  if (!packageSource.includes('"product:source:smoke:prod"') || !packageSource.includes('TACTICUM_EXPECT_PRODUCT_SOURCE=bitrix')) {
    fail('package.json must expose product:source:smoke:prod for Bitrix source verification');
  }
  if (!packageSource.includes('"product:source:http:prod"') || !packageSource.includes('product-source-http-check.mjs')) {
    fail('package.json must expose product:source:http:prod for Chrome-free source verification');
  }
  for (const releasePrecheckNeedle of [
    'health_config.php',
    'data-product-source',
    'data-product-code',
    'metrika.js',
    '/bitrix/admin/',
    'tacticum_offer.php',
    'tacticum_sale.php',
    'Manual release gates are tracked in release sign-off; current owner evidence is closed there.'
  ]) {
    if (!releasePublicPrecheckSource.includes(releasePrecheckNeedle)) {
      fail(`release-public-precheck.mjs must include ${releasePrecheckNeedle} public precheck`);
    }
  }
  if (!packageSource.includes('"release:public-precheck:prod"') || !packageSource.includes('release-public-precheck.mjs')) {
    fail('package.json must expose release:public-precheck:prod for safe production prechecks');
  }
  for (const manualGateHelperNeedle of [
    'manualGateNames',
    'manual-success-flow',
    'metrika-goals',
    'bitrix-admin',
    'staff-sale-upstream',
    'evidence_skeleton',
    'next_actions',
    'source_file_available',
    'standalone skeleton mode',
    'TACTICUM_RELEASE_SIGNOFF',
    'Do not store name, phone, email'
  ]) {
    if (!releaseManualGatesHelperSource.includes(manualGateHelperNeedle)) {
      fail(`release-manual-gates-helper.mjs must include ${manualGateHelperNeedle}`);
    }
  }
  if (!packageSource.includes('"release:manual-gates:helper"') || !packageSource.includes('release-manual-gates-helper.mjs')) {
    fail('package.json must expose release:manual-gates:helper for manual release gate evidence handoff');
  }
  if (!manualReleaseGatesRunbook.includes('npm run release:manual-gates:helper') || !releaseSignoffGatesSource.includes('npm run release:manual-gates:helper')) {
    fail('manual release gate docs must document release:manual-gates:helper');
  }
  for (const manualSuccessFlowNeedle of [
    'TACTICUM_MANUAL_FLOW_TEST_SESSID',
    'default_lead_form',
    'modal_form',
    'ai_chat',
    'prefill_controlled_empty',
    'tacticum_form.php',
    'tacticum_chat.php',
    'tacticum_prefill.php',
    'Controlled Browser Snippet',
    'Manual Success-Flow Evidence Template',
    'TACTICUM_MANUAL_FLOW_TEST_MARKER',
    'qa_marker',
    'tacticumManualSuccessFlowSafeBody',
    'safe_body',
    'masked_group_id'
  ]) {
    if (!manualSuccessFlowHelperSource.includes(manualSuccessFlowNeedle)) {
      fail(`manual-success-flow-helper.mjs must include ${manualSuccessFlowNeedle}`);
    }
  }
  if (!releaseManualGatesHelperSource.includes('manual:success-flow:helper')) {
    fail('release-manual-gates-helper.mjs must point manual-success-flow owners to manual:success-flow:helper');
  }
  if (!packageSource.includes('"manual:success-flow:helper"') || !packageSource.includes('manual-success-flow-helper.mjs')) {
    fail('package.json must expose manual:success-flow:helper for controlled manual success-flow evidence');
  }
  if (!manualReleaseGatesRunbook.includes('npm run manual:success-flow:helper') || !releaseSignoffGatesSource.includes('npm run manual:success-flow:helper')) {
    fail('manual release gate docs must document manual:success-flow:helper');
  }
  for (const metrikaGoalsNeedle of [
    'TACTICUM_METRIKA_COUNTER_ID',
    '103471113',
    'tacticum_form_submit',
    'tacticum_form_success',
    'tacticum_chat_send',
    'tacticum_prefill_submit',
    'tacticum_product_view',
    'tacticum_tg_resolver_success',
    'source_check',
    'owner_checklist',
    'Owner Checklist',
    'goal_observations',
    'params_safe',
    'checked_markers',
    'observed_after',
    'Browser Observer Snippet',
    'Metrika Goals Evidence Template'
  ]) {
    if (!metrikaGoalsHelperSource.includes(metrikaGoalsNeedle)) {
      fail(`metrika-goals-helper.mjs must include ${metrikaGoalsNeedle}`);
    }
  }
  if (!releaseManualGatesHelperSource.includes('metrika:goals:helper')) {
    fail('release-manual-gates-helper.mjs must point metrika-goals owners to metrika:goals:helper');
  }
  if (!packageSource.includes('"metrika:goals:helper"') || !packageSource.includes('metrika-goals-helper.mjs')) {
    fail('package.json must expose metrika:goals:helper for Metrika evidence handoff');
  }
  if (!manualReleaseGatesRunbook.includes('npm run metrika:goals:helper') || !releaseSignoffGatesSource.includes('npm run metrika:goals:helper')) {
    fail('manual release gate docs must document metrika:goals:helper');
  }
  for (const bitrixAdminGateNeedle of [
    'bitrix-admin',
    '/bitrix/admin/',
    'public_toolbar_url',
    'checked_at',
    'checked_by',
    'role',
    'release:public-precheck:prod',
    'Bitrix Admin Evidence Template'
  ]) {
    if (!bitrixAdminGateHelperSource.includes(bitrixAdminGateNeedle)) {
      fail(`bitrix-admin-gate-helper.mjs must include ${bitrixAdminGateNeedle}`);
    }
  }
  if (!releaseManualGatesHelperSource.includes('bitrix:admin:gate-helper')) {
    fail('release-manual-gates-helper.mjs must point bitrix-admin owners to bitrix:admin:gate-helper');
  }
  if (!packageSource.includes('"bitrix:admin:gate-helper"') || !packageSource.includes('bitrix-admin-gate-helper.mjs')) {
    fail('package.json must expose bitrix:admin:gate-helper for authenticated Bitrix admin evidence handoff');
  }
  if (!manualReleaseGatesRunbook.includes('npm run bitrix:admin:gate-helper') || !releaseSignoffGatesSource.includes('npm run bitrix:admin:gate-helper')) {
    fail('manual release gate docs must document bitrix:admin:gate-helper');
  }
  for (const legacyInventoryNeedle of [
    'TACTICUM_LEGACY_LOG_FILES',
    'TACTICUM_LEGACY_LOG_FROM',
    'TACTICUM_LEGACY_LOG_TO',
    'TACTICUM_LEGACY_SOURCE_LABEL',
    '--self-test',
    'createGunzip',
    'tacticum_offer.php',
    'tacticum_sale.php',
    'aggregate-only'
  ]) {
    if (!legacySaleAccessLogInventorySource.includes(legacyInventoryNeedle)) {
      fail(`legacy-sale-access-log-inventory.mjs must include ${legacyInventoryNeedle}`);
    }
  }
  if (!packageSource.includes('"legacy:sale:inventory:logs"') || !packageSource.includes('legacy-sale-access-log-inventory.mjs')) {
    fail('package.json must expose legacy:sale:inventory:logs for aggregate access-log inventory');
  }
  for (const staffSaleGateNeedle of [
    'TACTICUM_STAFF_TEST_SESSID',
    'TACTICUM_STAFF_TEST_MARKER',
    'qa_marker',
    'Controlled Staff-Order Browser Snippet',
    '--browser',
    'TACTICUM_STAFF_TEST_EMAIL',
    'workers_json',
    'team_preset',
    'monthly_budget_estimate',
    'end_date_present',
    'upstream_request_id'
  ]) {
    if (!staffSaleGateHelperSource.includes(staffSaleGateNeedle)) {
      fail(`staff-sale-gate-helper.mjs must include ${staffSaleGateNeedle}`);
    }
  }
  if (!packageSource.includes('"staff:sale:gate-helper"') || !packageSource.includes('staff-sale-gate-helper.mjs')) {
    fail('package.json must expose staff:sale:gate-helper for controlled staff upstream evidence');
  }
  if (!productBlockPreviewWorkflow.includes('npm run product:block-previews') || !productBlockPreviewWorkflow.includes('product-blocks/*.png')) {
    fail('product block preview workflow runbook must document command and screenshot outputs');
  }
  if (!releaseSignoffCheckSource.includes('requiredProductBlocks') || !releaseSignoffCheckSource.includes('validateProductBlocks')) {
    fail('release sign-off checker must validate rendered product block inventory for product SEO evidence');
  }
  if (!releaseSignoffSelfTestSource.includes('productSeoWithoutBlocksManifest')) {
    fail('release sign-off self-test must cover missing product block inventory');
  }
  if (!productRendererSource.includes('tacticum_product_page_data') || !productRendererSource.includes('/local/php_interface/include/product_data/')) {
    fail('product page renderer must load product page data from local/php_interface/include/product_data');
  }
  if (!productRendererSource.includes('tacticum_product_page_render_fit_guide')) {
    fail('product page renderer must support product fit guide decision-support blocks');
  }
  if (!productRendererSource.includes('tacticum_product_page_render_procurement')) {
    fail('product page renderer must support security/procurement decision-support blocks');
  }
  if (!productRendererSource.includes('tacticum_product_page_render_use_cases')) {
    fail('product page renderer must support use-case anatomy decision-support blocks');
  }
  if (!productRendererSource.includes('tacticum_product_page_render_comparison')) {
    fail('product page renderer must support product comparison decision-support blocks');
  }
  if (!productRendererSource.includes('tacticum_product_page_render_rollout')) {
    fail('product page renderer must support the product rollout delivery model block');
  }
  if (!productRendererSource.includes('tacticum_product_page_render_proof')) {
    fail('product page renderer must support product proof readiness blocks');
  }
  if (!productRendererSource.includes('tacticum_product_page_render_proof_status') || !productRendererSource.includes('data-product-proof-status')) {
    fail('product page renderer must expose safe product proof status badges for owner-evidence UI');
  }
  if (!productRendererSource.includes('tacticum_product_page_schema') || !productRendererSource.includes('tacticum_product_page_software_schema')) {
    fail('product page renderer must build product JSON-LD schema from shared product page data');
  }
  if (!productRendererSource.includes("'@type' => 'SoftwareApplication'") || !productRendererSource.includes("'@id' => tacticum_public_url") || !productRendererSource.includes("'provider'") || !productRendererSource.includes("'isPartOf'")) {
    fail('product page schema helper must include stable SoftwareApplication identity, provider and isPartOf references');
  }
  if (!productRendererSource.includes('tacticum_product_page_faq_schema') || !productRendererSource.includes("'@type' => 'FAQPage'") || !productRendererSource.includes("'acceptedAnswer'")) {
    fail('product page schema helper must expose rendered static product FAQ as FAQPage JSON-LD');
  }
  if (!leadCtaComponentSource.includes('SCENARIO_OPTIONS') || !leadCtaComponentSource.includes('normalizeScenarioOptions')) {
    fail('tacticum:lead.cta component must support normalized scenario options');
  }
  if (!leadCtaFormTemplateSource.includes('name="lead_scenario"')) {
    fail('tacticum:lead.cta template must render optional lead_scenario select');
  }
  const leadCtaSource = `${leadCtaTemplateSource}\n${leadCtaFormTemplateSource}`;
  if (!leadCtaTemplateSource.includes('^ri-[a-z0-9]+(?:-[a-z0-9]+)*$')) {
    fail('tacticum:lead.cta template must normalize feature icon classes');
  }
  const leadCtaIconLinesWithoutHidden = leadCtaSource
    .split(/\r?\n/)
    .filter((line) => line.includes('<i ') && !line.includes('aria-hidden="true"'));
  if (leadCtaIconLinesWithoutHidden.length > 0) {
    fail('tacticum:lead.cta decorative icons must include aria-hidden="true"');
  }
  if (!leadCtaFormTemplateSource.includes('data-tacticum-returning-lead-panel') || !formsSource.includes('tacticum:returningLead:v1')) {
    fail('tacticum:lead.cta and forms.js must support no-PII returning lead state');
  }
  const returningLeadStart = formsSource.indexOf('const markReturningLead');
  const returningLeadEnd = formsSource.indexOf('const applyReturningLeadState');
  const returningLeadSource = returningLeadStart >= 0 && returningLeadEnd > returningLeadStart
    ? formsSource.slice(returningLeadStart, returningLeadEnd)
    : '';
  if (!returningLeadSource.includes('product:') || !returningLeadSource.includes('form_id:')) {
    fail('forms.js returning lead marker must persist only safe product/form metadata');
  }
  for (const forbiddenReturningLeadField of ['email', 'phone', 'name', 'message', 'company']) {
    if (
      returningLeadSource.includes(`payload.${forbiddenReturningLeadField}`)
      || returningLeadSource.includes(`${forbiddenReturningLeadField}:`)
    ) {
      fail(`forms.js returning lead marker must not persist PII field ${forbiddenReturningLeadField}`);
    }
  }
  if (!homepageSource.includes('Как выбрать продукт') || !homepageSource.includes('Начните с ситуации') || !homepageSource.includes('Старт: architecture assessment')) {
    fail('homepage must include product fit matrix decision-support block');
  }
  for (const homeBlock of ['hero', 'ecosystem-map', 'fit-matrix', 'commercial-next-steps', 'calculator-preview']) {
    if (!homepageSource.includes(`data-home-block="${homeBlock}"`)) {
      fail(`homepage must expose data-home-block="${homeBlock}" for router smoke coverage`);
    }
  }
  for (const productLink of ['platform', 'agents', 'dev', 'forum']) {
    if (!homepageSource.includes(`data-home-product-link="${productLink}"`)) {
      fail(`homepage must expose data-home-product-link="${productLink}"`);
    }
  }
  for (const commercialLink of ['offer', 'services', 'price', 'aiagents']) {
    if (!homepageSource.includes(`data-home-commercial-link="${commercialLink}"`)) {
      fail(`homepage must expose data-home-commercial-link="${commercialLink}"`);
    }
  }
  const homepageIconLinesWithoutHidden = homepageSource
    .split(/\r?\n/)
    .filter((line) => line.includes('<i ') && !line.includes('aria-hidden="true"'));
  if (homepageIconLinesWithoutHidden.length > 0) {
    fail('homepage decorative icons must include aria-hidden="true"');
  }
  for (const calculatorA11yNeedle of [
    'id="chatMessages" role="log" aria-live="polite"',
    'id="userMessage" aria-label=',
    'id="sendMessage"',
    'aria-label="Отправить сообщение AI-калькулятору"',
    'role="progressbar"',
    'aria-valuenow="35"',
    'aria-valuenow="65"',
    'aria-valuenow="85"'
  ]) {
    if (!homepageSource.includes(calculatorA11yNeedle)) {
      fail(`homepage legacy calculator block must include ${calculatorA11yNeedle}`);
    }
  }
  for (const scenarioValue of expectedProductScenarioValues) {
    if (!formEndpointSource.includes(`'${scenarioValue}'`)) {
      fail(`tacticum_form.php must map product lead_scenario value ${scenarioValue}`);
    }
  }
  if (
    !formEndpointSource.includes('tacticum_form_build_lead_profile')
    || !formEndpointSource.includes("'product_interest'")
    || !formEndpointSource.includes("'use_case_interest'")
    || !formEndpointSource.includes("'deployment_interest'")
  ) {
    fail('tacticum_form.php must normalize product lead fields into a canonical lead qualification profile before task fallback');
  }
  for (const structuredLeadField of ['product_interest', 'use_case_interest', 'deployment_interest']) {
    if (formEndpointSource.includes(`$payload['${structuredLeadField}']`)) {
      fail(`tacticum_form.php must not forward structured lead field ${structuredLeadField} to upstream before CRM/upstream contract approval`);
    }
  }
  for (const productEvent of [
    'tacticum_product_view',
    'tacticum_product_cta_click',
    'tacticum_product_form_submit',
    'tacticum_product_form_success',
    'tacticum_product_form_error'
  ]) {
    if (!analyticsSource.includes(productEvent) && !formsSource.includes(productEvent)) {
      fail(`product funnel analytics event ${productEvent} is missing`);
    }
  }
  if (!formsSource.includes('productAnalyticsValues') || !formsSource.includes('normalizeControlledValue')) {
    fail('forms.js must allowlist product analytics values before sending product funnel events');
  }
  for (const formStateNeedle of ['aria-busy', 'aria-invalid', 'tacticumSubmitting', 'tacticumWasDisabled', 'Отправляем...']) {
    if (!formsSource.includes(formStateNeedle)) {
      fail(`forms.js must preserve accessible submit/invalid state hook: ${formStateNeedle}`);
    }
  }
  for (const forbiddenProductAnalyticsParam of ['lead_budget', 'lead_timeline', 'lead_offer_title', 'message', 'email', 'phone']) {
    if (analyticsSource.includes(`tacticum_product_${forbiddenProductAnalyticsParam}`)) {
      fail(`analytics.js must not send ${forbiddenProductAnalyticsParam} in product funnel analytics`);
    }
  }

  for (const { file, key, dataFile } of productPages) {
    const source = read(file);
    const dataSource = read(dataFile);
    const productDataIndex = source.indexOf(`$tacticumProductPage = tacticum_product_page_data('${key}')`);
    const seoIndex = source.indexOf('tacticum_apply_seo_defaults');
    const renderIndex = source.indexOf('tacticum_render_product_page($tacticumProductPage)');
    if (productDataIndex < 0) {
      fail(`${file} must load product page data through tacticum_product_page_data('${key}') before SEO and render`);
    }
    if (seoIndex < 0 || (productDataIndex >= 0 && seoIndex < productDataIndex)) {
      fail(`${file} must build SEO schema after product page data is defined`);
    }
    if (renderIndex < 0 || (seoIndex >= 0 && renderIndex < seoIndex)) {
      fail(`${file} must render the same product page data after SEO defaults`);
    }
    if (!source.includes('tacticum_render_product_page')) {
      fail(`${file} must render through the shared product page renderer`);
    }
    if (!new RegExp(`tacticum_apply_seo_defaults\\s*\\(\\s*['"]/${key}/['"]`).test(source)) {
      fail(`${file} must bind SEO defaults to /${key}/`);
    }
    if (!new RegExp(`tacticum_product_page_schema\\s*\\([\\s\\S]*?['"]/${key}/['"]`).test(source)) {
      fail(`${file} must bind product JSON-LD schema to /${key}/`);
    }
    if (source.includes('$tacticumProductPage = [')) {
      fail(`${file} must stay thin and must not inline the product page data array`);
    }
    if (!dataSource.includes('return [') || !dataSource.includes("'eyebrow'") || !dataSource.includes("'cta'")) {
      fail(`${dataFile} must return the product page data array`);
    }
    if (!/SetPageProperty\s*\(\s*["']tacticum_page_assets["']\s*,\s*["'][^"']*\bfaq\b/.test(source)) {
      fail(`${file} must request faq.js through tacticum_page_assets=faq`);
    }
    if (!dataSource.includes("'scenario_options'") && !dataSource.includes('"scenario_options"')) {
      fail(`${dataFile} must provide product scenario options for CTA qualification`);
    }
    if (!dataSource.includes("'fit_guide'") && !dataSource.includes('"fit_guide"')) {
      fail(`${dataFile} must include product fit guide decision-support items`);
    }
    if (!dataSource.includes("'procurement'") && !dataSource.includes('"procurement"')) {
      fail(`${dataFile} must include security/procurement decision-support items`);
    }
    if (!dataSource.includes("'use_cases'") && !dataSource.includes('"use_cases"')) {
      fail(`${dataFile} must include use-case anatomy decision-support items`);
    }
    if (!dataSource.includes("'comparison'") && !dataSource.includes('"comparison"')) {
      fail(`${dataFile} must include product comparison decision-support items`);
    }
    for (const useCaseField of ['trigger', 'owner', 'pilot_input', 'pilot_output', 'limitation']) {
      if (!dataSource.includes(`'${useCaseField}'`) && !dataSource.includes(`"${useCaseField}"`)) {
        fail(`${dataFile} use-case anatomy must include ${useCaseField}`);
      }
    }
    if (!dataSource.includes("'rollout'") && !dataSource.includes('"rollout"')) {
      fail(`${dataFile} must include product rollout/delivery model steps`);
    }
    if (!dataSource.includes("'proof'") && !dataSource.includes('"proof"')) {
      fail(`${dataFile} must include product proof readiness items`);
    }
    if (!source.includes("'schema' => tacticum_product_page_schema(")) {
      fail(`${file} must add SoftwareApplication and FAQPage JSON-LD through tacticum_product_page_schema`);
    }
    for (const forbiddenField of forbiddenProductSchemaFields) {
      if (source.includes(`'${forbiddenField}'`) || source.includes(`"${forbiddenField}"`) || dataSource.includes(`'${forbiddenField}'`) || dataSource.includes(`"${forbiddenField}"`)) {
        fail(`${file} / ${dataFile} product schema must not include risky commercial field ${forbiddenField}`);
      }
    }
  }

  const agentsPageSource = `${read('agents/index.php')}\n${read('local/php_interface/include/product_data/agents.php')}`;
  const forumPageSource = `${read('forum/index.php')}\n${read('local/php_interface/include/product_data/forum.php')}`;
  if (!agentsPageSource.includes('Сравнить с Forum') || !agentsPageSource.includes('/forum/')) {
    fail('agents page must explicitly link and compare against Forum');
  }
  if (!forumPageSource.includes('Смотреть Agents') || !forumPageSource.includes('/agents/')) {
    fail('forum page must explicitly link and compare against Agents');
  }

  for (const file of ctaPages) {
    const source = read(file);
    if (!source.includes('"tacticum:lead.cta"')) {
      fail(`${file} must render repeated CTA through tacticum:lead.cta`);
    }
    if (/personal-offer-cta|project-discussion-cta|tacticumPersonalOfferCta|tacticumProjectDiscussionCta/.test(source)) {
      fail(`${file} must not use legacy CTA include globals`);
    }
  }

  for (const legacyInclude of [
    'local/templates/tacticum/include/personal-offer-cta.php',
    'local/templates/tacticum/include/project-discussion-cta.php'
  ]) {
    if (fs.existsSync(legacyInclude)) {
      fail(`${legacyInclude} must stay removed; use tacticum:lead.cta`);
    }
  }

  for (const file of faqHosts) {
    const source = read(file);
    if (!source.includes('"tacticum:faq.section"')) {
      fail(`${file} must render FAQ through tacticum:faq.section`);
    }
    if (!source.includes('"SECTION_KEY"')) {
      fail(`${file} must pass semantic SECTION_KEY to tacticum:faq.section`);
    }
    if (/"PARENT_SECTION"\s*=>\s*["']\d+["']/.test(source)) {
      fail(`${file} must not pass numeric FAQ PARENT_SECTION; use semantic SECTION_KEY`);
    }
    if (/"bitrix:news\.list"\s*,\s*"faq"/s.test(source)) {
      fail(`${file} must not render FAQ through direct bitrix:news.list call`);
    }
  }
  if (
    !faqSectionComponentSource.includes("'status' => 'missing'")
    || !faqSectionTemplateSource.includes('data-faq-section-status="missing"')
  ) {
    fail('tacticum:faq.section must fail closed when semantic SECTION_KEY cannot resolve');
  }
  if (!productRendererSource.includes('aria-expanded="false"') || !productRendererSource.includes('aria-controls=') || !productRendererSource.includes('aria-hidden="true"')) {
    fail('product FAQ renderer must expose accessible accordion state attributes');
  }
  if (!faqJsSource.includes('aria-expanded') || !faqJsSource.includes('aria-hidden') || !faqJsSource.includes('keydown')) {
    fail('faq.js must synchronize FAQ accordion ARIA state and keyboard access');
  }
  if (!visualSmokeSource.includes('aria-expanded=true') || !visualSmokeSource.includes('FAQ controlled answer did not become accessible')) {
    fail('visual-smoke must verify accessible product FAQ toggle state');
  }

  for (const file of directContentListHosts) {
    const source = read(file);
    if (!source.includes('"tacticum:content.list"')) {
      fail(`${file} must render repeated content lists through tacticum:content.list`);
    }
  }

  for (const file of directContentDetailHosts) {
    const source = read(file);
    if (!source.includes('"tacticum:content.detail"')) {
      fail(`${file} must render static iblock detail through tacticum:content.detail`);
    }
    if (/"ELEMENT_ID"\s*=>\s*["']\d+["']/.test(source)) {
      fail(`${file} must not hardcode static detail ELEMENT_ID`);
    }
  }

  for (const file of publicComponentEntryPoints) {
    const source = read(file);
    if (/"bitrix:news\.list"/.test(source)) {
      fail(`${file} must not call bitrix:news.list directly; use tacticum local wrappers`);
    }
    if (/"bitrix:news\.detail"/.test(source)) {
      fail(`${file} must not call bitrix:news.detail directly; use tacticum local wrappers`);
    }
    if (/INCLUDE_IBLOCK_INТО_CHAIN/.test(source)) {
      fail(`${file} contains Cyrillic typo in INCLUDE_IBLOCK_INTO_CHAIN`);
    }
    if (/TACTICUM_PAGE_ASSETS|TACTICUM_BODY_CLASS/.test(source)) {
      fail(`${file} must not use template globals; use Bitrix page properties or component params`);
    }
  }

  for (const file of splitPrologAssetPages) {
    const source = read(file);
    if (!source.includes('prolog_before.php') || !source.includes('prolog_after.php')) {
      fail(`${file} must use Bitrix split prolog for page properties before header`);
    }
  }

  for (const file of chatPages) {
    const source = read(file);
    if (!source.includes('"tacticum:chat.surface"')) {
      fail(`${file} must render hero/light chat surfaces through tacticum:chat.surface`);
    }
    if (!/SetPageProperty\s*\(\s*["']tacticum_page_assets["']\s*,\s*["'][^"']*\bchat\b/.test(source)) {
      fail(`${file} must request chat-agent.js through tacticum_page_assets=chat`);
    }
  }
  for (const file of ['calculator/index.php', 'price/index.php']) {
    if (/data-tacticum-chat="light"/.test(read(file))) {
      fail(`${file} must not keep inline light chat markup`);
    }
  }

  const headerSource = read('local/templates/tacticum/header.php');
  if (!headerSource.includes("if ($hasPageAsset('chat'))")) {
    fail('template header must load chat-agent.js only through the chat page asset');
  }
  if (/fonts\.googleapis|fonts\.gstatic|readdy\.ai/.test(headerSource)) {
    fail('template header must not keep unused Google Fonts/Readdy external origins');
  }

  const aiagentsPageSource = read('aiagents/index.php');
  const aiagentsComponentSource = read('local/components/tacticum/aiagents/component.php');
  const aiagentsTemplateSource = read('local/components/tacticum/aiagents/templates/.default/template.php');
  const contentMigrationsSource = read('local/php_interface/include/content_migrations.php');

  if (!aiagentsPageSource.includes('"tacticum:aiagents"')) {
    fail('aiagents/index.php must render through tacticum:aiagents');
  }
  if (!aiagentsPageSource.includes('prolog_before.php') || !aiagentsPageSource.includes('prolog_after.php')) {
    fail('aiagents/index.php must use Bitrix split prolog to set page properties before header');
  }
  if (!aiagentsPageSource.includes('tacticum_page_assets') || !aiagentsPageSource.includes('tacticum_body_class')) {
    fail('aiagents/index.php must set template assets/body class through page properties');
  }
  if (/TACTICUM_PAGE_ASSETS|TACTICUM_BODY_CLASS/.test(aiagentsPageSource)) {
    fail('aiagents/index.php must not use template globals');
  }
  if (!aiagentsComponentSource.includes('FAQ_IBLOCK_ID') || !aiagentsComponentSource.includes('FAQ_SECTION_KEY')) {
    fail('tacticum:aiagents component must pass FAQ params to its template');
  }
  if (/"FAQ_PARENT_SECTION"\s*=>\s*['"]\d+['"]/.test(aiagentsPageSource + aiagentsComponentSource)) {
    fail('tacticum:aiagents must not hardcode numeric FAQ section fallback');
  }
  if (!aiagentsTemplateSource.includes("$arResult['AIAGENTS_IBLOCK_ID']")) {
    fail('tacticum:aiagents template must use component result for AI agents iblock id');
  }
  if (/bitrix\/header\.php|bitrix\/footer\.php|tacticum_apply_seo_defaults|TACTICUM_PAGE_ASSETS|TACTICUM_BODY_CLASS/.test(aiagentsTemplateSource)) {
    fail('tacticum:aiagents template must not own header/footer/SEO/template globals');
  }
  if (/['"]ID['"]\s*=>\s*515/.test(contentMigrationsSource)) {
    fail('content migrations must not hardcode policy element ID 515; resolve by iblock content');
  }
}

function assertLocalComponentMetadata() {
  const componentRoot = 'local/components/tacticum';
  const componentDirs = fs.readdirSync(componentRoot, { withFileTypes: true })
    .filter((entry) => entry.isDirectory())
    .map((entry) => `${componentRoot}/${entry.name}`);

  for (const componentDir of componentDirs) {
    for (const file of ['.description.php', '.parameters.php', 'component.php']) {
      const fullPath = `${componentDir}/${file}`;
      if (!fs.existsSync(fullPath)) {
        fail(`${fullPath} is missing`);
      }
    }
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

async function checkHttpOfferCatalogPrettyUrl() {
  const legacyPath = '/offer/?scenario=ai-kopaylot&page=2&clear_cache=Y';
  const expectedPrettyPath = '/offer/catalog/scenario/ai-kopaylot/page/2/?clear_cache=Y';
  const legacyResponse = await fetch(`${HTTP_BASE_URL}${legacyPath}`, {
    redirect: 'manual',
  });

  if (![301, 302, 303, 307, 308].includes(legacyResponse.status)) {
    fail(`offer catalog legacy query URL must redirect to pretty URL; got HTTP ${legacyResponse.status}: ${legacyPath}`);
  } else {
    const location = legacyResponse.headers.get('location') || '';
    if (!location.includes(expectedPrettyPath)) {
      fail(`offer catalog legacy query redirect must preserve clear_cache in pretty URL; got ${location || '(empty)'}`);
    }
  }

  const prettyResponse = await fetch(`${HTTP_BASE_URL}${expectedPrettyPath}`);
  if (prettyResponse.status !== 200) {
    fail(`offer catalog pretty URL returned HTTP ${prettyResponse.status}: ${expectedPrettyPath}`);
    return;
  }

  const html = await prettyResponse.text();
  if (!html.includes(`<link rel="canonical" href="${SITE}/offer/">`)) {
    fail(`offer catalog pretty URL must keep canonical /offer/: ${expectedPrettyPath}`);
  }
  if (!/<meta name="robots" content="noindex,follow"/i.test(html)) {
    fail(`offer catalog pretty URL must be noindex,follow: ${expectedPrettyPath}`);
  }
}

async function fetchHttpText(path, label) {
  const response = await fetch(`${HTTP_BASE_URL}${path}`);
  if (!response.ok) {
    fail(`${label} returned HTTP ${response.status}`);
    return '';
  }

  return response.text();
}

async function checkHttpSitemapGovernance() {
  const robots = await fetchHttpText('/robots.txt', 'production robots.txt');
  if (!robots.includes(`Sitemap: ${ROOT_SITEMAP_URL}`)) {
    fail(`production robots.txt must point to ${ROOT_SITEMAP_URL}`);
  }

  const rootSitemap = await fetchHttpText('/sitemap.xml', 'production sitemap.xml');
  validateRootSitemap(rootSitemap, 'production sitemap.xml');

  const staticSitemap = await fetchHttpText('/sitemap-basic-files.xml', 'production sitemap-basic-files.xml');
  validateStaticSitemap(staticSitemap, 'production sitemap-basic-files.xml');
}

async function checkHttpOfferSitemap() {
  const url = `${HTTP_BASE_URL}/offer/sitemap.php`;
  const response = await fetch(url);
  if (!response.ok) {
    fail(`/offer/sitemap.php returned HTTP ${response.status}`);
    return;
  }

  const xml = await response.text();
  const offerLocs = extractTags(xml, 'loc');
  assertHttps(offerLocs, '/offer/sitemap.php loc');
  assertUnique(offerLocs, '/offer/sitemap.php loc');
  assertNoForbiddenSitemapLocs(offerLocs, '/offer/sitemap.php loc');

  for (const loc of offerLocs) {
    if (!loc.startsWith(`${SITE}/offer/`)) {
      fail(`/offer/sitemap.php has non-offer loc: ${loc}`);
    }
  }

  if (offerLocs.length > 0) {
    await checkHttpOfferDetailWithQuery(offerLocs[0]);
  }
}

async function checkHttpOfferDetailWithQuery(offerLoc) {
  let url;
  try {
    url = new URL(offerLoc);
  } catch {
    fail(`/offer/sitemap.php has invalid offer detail URL: ${offerLoc}`);
    return;
  }

  url.searchParams.set('clear_cache', 'Y');
  const response = await fetch(url);
  if (response.status !== 200) {
    fail(`valid offer detail with clear_cache query returned HTTP ${response.status}: ${url.href}`);
    return;
  }

  const html = await response.text();
  if (!html.includes('<link rel="canonical" href="' + offerLoc + '">')) {
    fail(`valid offer detail with clear_cache query must keep self-canonical: ${url.href}`);
  }
  if (/Страница не найдена|Предложение не найдено/.test(html)) {
    fail(`valid offer detail with clear_cache query rendered a 404 page: ${url.href}`);
  }
}

const sitemapIndex = read(ROOT_SITEMAP_FILE);
const staticSitemap = readOptional(STATIC_SITEMAP_FILE);
const robots = read('robots.txt');
const staticSitemapGenerator = read('tools/static-sitemap-generate.mjs');
const packageJsonForSitemap = read('package.json');
const deployWorkflow = read('.github/workflows/deploy.yml');
const sitemapWorkflow = read('.github/workflows/sitemap.yml');

validateRootSitemap(sitemapIndex, ROOT_SITEMAP_FILE);
if (staticSitemap !== null) {
  validateStaticSitemap(staticSitemap, STATIC_SITEMAP_FILE);
}

for (const expectedPath of expectedStaticPages.values()) {
  if (!staticSitemapGenerator.includes(`'${expectedPath}'`)) {
    fail(`static sitemap generator is missing ${expectedPath}`);
  }
}
if (
  !packageJsonForSitemap.includes('"sitemap:static:generate"')
  || !packageJsonForSitemap.includes('"sitemap:static:check"')
) {
  fail('package.json must expose static sitemap generation/check scripts');
}
if (
  !deployWorkflow.includes('STATIC_SITEMAP_LASTMOD="$(TZ=Europe/Moscow date +%F)"')
  || !deployWorkflow.includes('static-sitemap-generate.mjs --output=sitemap-basic-files.xml --lastmod="$STATIC_SITEMAP_LASTMOD"')
  || !deployWorkflow.includes('static-sitemap-generate.mjs --output=sitemap-basic-files.xml --lastmod="$STATIC_SITEMAP_LASTMOD" --check')
  || !deployWorkflow.includes('sitemap-basic-files.xml urlrewrite.php')
) {
  fail('deploy workflow must generate, verify and rsync sitemap-basic-files.xml as a build artifact');
}
if (
  !sitemapWorkflow.includes('STATIC_SITEMAP_LASTMOD="$(TZ=Europe/Moscow date +%F)"')
  || !sitemapWorkflow.includes('static-sitemap-generate.mjs --output=/tmp/sitemap-basic-files.xml --lastmod="$STATIC_SITEMAP_LASTMOD"')
  || !sitemapWorkflow.includes('static-sitemap-generate.mjs --output=/tmp/sitemap-basic-files.xml --lastmod="$STATIC_SITEMAP_LASTMOD" --check')
  || !sitemapWorkflow.includes('xmllint --noout /tmp/sitemap-basic-files.xml')
) {
  fail('sitemap workflow must generate and validate static sitemap artifact');
}

assertCanonicalPaths();
assertTopMenuProminence();
assertDefaultSocialPreview();
assertOfferCatalogRouting();
assertPublicPageComponentization();

if (!robots.includes(`Sitemap: ${ROOT_SITEMAP_URL}`)) {
  fail(`robots.txt must point to ${ROOT_SITEMAP_URL}`);
}

if (CHECK_HTTP) {
  await checkHttpSitemapGovernance();
  await checkHttpRobots();
  await checkHttpOfferSitemap();
  await checkHttpOfferCatalogPrettyUrl();
}

if (errors.length > 0) {
  console.error('SEO check failed:');
  for (const error of errors) {
    console.error(`- ${error}`);
  }
  process.exit(1);
}

console.log('SEO check passed.');
