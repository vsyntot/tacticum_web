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
  'platform-assessment',
  'platform-pilot',
  'deployment-readiness',
  'agent-scenario-selection',
  'rag-documents-check',
  'pilot-rollout',
  'ai-workflow-assessment',
  'quality-gates-pilot',
  'design-system-guardrails',
  'dialog-flow-assessment',
  'scenario-llm-pilot',
  'support-analytics-review'
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
    'platform/index.php',
    'agents/index.php',
    'dev/index.php',
    'forum/index.php'
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
  const productRendererSource = read('local/php_interface/include/product_page.php');
  const leadCtaComponentSource = read('local/components/tacticum/lead.cta/component.php');
  const leadCtaFormTemplateSource = read('local/components/tacticum/lead.cta/templates/.default/form.php');
  const formEndpointSource = read('local/rest/tacticum_form.php');

  assertLocalComponentMetadata();

  if (!productRendererSource.includes("'SCENARIO_OPTIONS'")) {
    fail('product page renderer must pass product scenario options into tacticum:lead.cta');
  }
  if (!productRendererSource.includes('tacticum_product_page_render_rollout')) {
    fail('product page renderer must support the product rollout delivery model block');
  }
  if (!productRendererSource.includes('tacticum_product_page_render_proof')) {
    fail('product page renderer must support product proof readiness blocks');
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
  for (const scenarioValue of expectedProductScenarioValues) {
    if (!formEndpointSource.includes(`'${scenarioValue}'`)) {
      fail(`tacticum_form.php must map product lead_scenario value ${scenarioValue}`);
    }
  }

  for (const file of productPages) {
    const source = read(file);
    const productDataIndex = source.indexOf('$tacticumProductPage =');
    const seoIndex = source.indexOf('tacticum_apply_seo_defaults');
    const renderIndex = source.indexOf('tacticum_render_product_page($tacticumProductPage)');
    if (productDataIndex < 0) {
      fail(`${file} must define one product page data array before SEO and render`);
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
    if (!/SetPageProperty\s*\(\s*["']tacticum_page_assets["']\s*,\s*["'][^"']*\bfaq\b/.test(source)) {
      fail(`${file} must request faq.js through tacticum_page_assets=faq`);
    }
    if (!source.includes("'scenario_options'") && !source.includes('"scenario_options"')) {
      fail(`${file} must provide product scenario options for CTA qualification`);
    }
    if (!source.includes("'rollout'") && !source.includes('"rollout"')) {
      fail(`${file} must include product rollout/delivery model steps`);
    }
    if (!source.includes("'proof'") && !source.includes('"proof"')) {
      fail(`${file} must include product proof readiness items`);
    }
    if (!source.includes("'schema' => tacticum_product_page_schema(")) {
      fail(`${file} must add SoftwareApplication and FAQPage JSON-LD through tacticum_product_page_schema`);
    }
    for (const forbiddenField of forbiddenProductSchemaFields) {
      if (source.includes(`'${forbiddenField}'`) || source.includes(`"${forbiddenField}"`)) {
        fail(`${file} product schema must not include risky commercial field ${forbiddenField}`);
      }
    }
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
  if (robots && !robots.includes(`Sitemap: ${ROOT_SITEMAP_URL}`)) {
    fail(`production robots.txt must point to ${ROOT_SITEMAP_URL}`);
  }

  const rootSitemap = await fetchHttpText('/sitemap.xml', 'production sitemap.xml');
  if (rootSitemap) {
    validateRootSitemap(rootSitemap, 'production sitemap.xml');
  }

  const staticSitemap = await fetchHttpText('/sitemap-basic-files.xml', 'production sitemap-basic-files.xml');
  if (staticSitemap) {
    validateStaticSitemap(staticSitemap, 'production sitemap-basic-files.xml');
  }
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

validateRootSitemap(sitemapIndex, ROOT_SITEMAP_FILE);
if (staticSitemap !== null) {
  validateStaticSitemap(staticSitemap, STATIC_SITEMAP_FILE);
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
