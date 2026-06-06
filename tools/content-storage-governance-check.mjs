#!/usr/bin/env node

import { existsSync, readFileSync } from 'node:fs';

const failures = [];
const docsWorkflowAvailable = process.env.TACTICUM_CONTENT_STORAGE_ASSUME_NO_DOCS !== '1'
  && existsSync('docs/workflow');

function read(path) {
  return readFileSync(path, 'utf8');
}

function requireFile(path) {
  if (!existsSync(path)) {
    failures.push(`Missing required file: ${path}`);
    return '';
  }

  return read(path);
}

function requireDocFile(path) {
  if (!docsWorkflowAvailable) {
    return '';
  }

  return requireFile(path);
}

function requireDocAll(path, patterns) {
  if (!docsWorkflowAvailable) {
    return;
  }

  requireAll(path, patterns);
}

function requirePattern(path, pattern, label) {
  const source = requireFile(path);
  if (source && !pattern.test(source)) {
    failures.push(`${path}: missing ${label}.`);
  }
}

function requireAll(path, patterns) {
  const source = requireFile(path);
  if (!source) {
    return;
  }
  for (const [pattern, label] of patterns) {
    if (!pattern.test(source)) {
      failures.push(`${path}: missing ${label}.`);
    }
  }
}

function forbidPattern(path, pattern, label) {
  const source = requireFile(path);
  if (source && pattern.test(source)) {
    failures.push(`${path}: forbidden ${label}.`);
  }
}

requirePattern(
  'local/php_interface/include/tacticum_config.example.php',
  /['"]clients['"]\s*=>\s*\d+/,
  'clients iblock registry key'
);
requireAll('local/php_interface/include/tacticum_config.example.php', [
  [/['"]page_sections['"]\s*=>\s*\d+/, 'page_sections iblock registry key'],
  [/['"]page_blocks['"]\s*=>\s*\d+/, 'page_blocks iblock registry key'],
  [/['"]page_content['"]\s*=>\s*\[/, 'page_content runtime section'],
  [/['"]source['"]\s*=>\s*['"]fallback['"]/, 'page_content safe fallback default'],
  [/['"]live_status['"]\s*=>\s*['"]live['"]/, 'page_content live status gate'],
  [/['"]allow_fallback['"]\s*=>\s*true/, 'page_content fallback enabled'],
]);

requireAll('local/lib/Tacticum/Rest/ConfigValidator.php', [
  [/['"]clients['"]/, 'clients in content validation scope'],
  [/function\s+validatePageContent\b/, 'page_content config validator'],
  [/page_content\.source/, 'page_content source validation'],
  [/page_content\.allow_fallback/, 'page_content fallback validation'],
]);

requireAll('tools/config-runtime-check.php', [
  [/['"]clients['"]/, 'clients in runtime config summary'],
  [/page_content/, 'page_content in runtime config summary'],
]);

for (const path of ['tools/product-content-migration.php', 'tools/product-content-check.php']) {
  requireAll(path, [
    [/['"]feedback['"]/, 'feedback PRODUCT relation target'],
    [/['"]clients['"]/, 'clients PRODUCT relation target'],
  ]);
}

requireAll('local/lib/Tacticum/Product/ContentRuntime.php', [
  [/['"]faq['"]/, 'faq in product content cache identity and tags'],
  [/['"]cases['"]/, 'cases in product content cache identity and tags'],
  [/['"]feedback['"]/, 'feedback in product content cache identity and tags'],
  [/['"]clients['"]/, 'clients in product content cache identity and tags'],
]);

requireAll('local/lib/Tacticum/Product/ContentRepository.php', [
  [/function\s+fetchProductFaq\b/, 'product FAQ repository fetch'],
  [/PROPERTY_PRODUCT/, 'product FAQ relation filter'],
]);
requireFile('local/lib/Tacticum/Product/ContentProofRepository.php');
requireAll('local/lib/Tacticum/Product/ContentProofRepository.php', [
  [/function\s+fetchProductProof\b/, 'product proof repository fetch'],
  [/PUBLIC_RENDER_APPROVED/, 'public proof render approval filter'],
]);
requireFile('local/lib/Tacticum/Product/ContentProofService.php');
requireAll('local/lib/Tacticum/Product/ContentProofService.php', [
  [/ContentProofRepository::fetchProductProof/, 'product proof repository orchestration'],
  [/_proof_source/, 'product proof source marker orchestration'],
]);

requireAll('local/lib/Tacticum/Product/ContentService.php', [
  [/fetchProductFaq\(/, 'product FAQ iblock first read'],
  [/_faq_source/, 'product FAQ source marker'],
  [/ContentProofService::applyPublicProof/, 'product proof approved iblock read'],
]);
forbidPattern(
  'local/lib/Tacticum/Product/ContentService.php',
  /_faq_source[\s\S]{0,40}['"]fallback|fallbackFaq/,
  'product_blocks.faq runtime fallback after retirement'
);

requireAll('local/components/tacticum/services.page/templates/.default/parts/services-list.php', [
  [/["']NEWS_COUNT["']\s*=>\s*["']6["']/, 'six services cards in services list'],
  [/["']CLASS["']/, 'services CLASS property'],
  [/["']LINK["']/, 'services LINK property'],
  [/["']LINKTEXT["']/, 'services LINKTEXT property'],
  [/["']PRODUCT["']/, 'services PRODUCT relation property'],
]);

requireAll('tools/product-content-target-evidence-check.mjs', [
  [/faq_source/, 'target evidence FAQ source validation'],
  [/['"]faq['"]/, 'target evidence FAQ iblock validation'],
]);

requireFile('tools/content-storage-audit.php');
requireAll('tools/content-storage-audit.php', [
  [/aiagents['"]?\s*,\s*['"]proof|aiagents\|proof/, 'aiagents audit scope'],
  [/page-content/, 'page-content audit scope'],
  [/faq_items_without_section/, 'FAQ product section audit'],
  [/cases_items/, 'product cases relation count audit'],
  [/feedback_items/, 'product feedback relation count audit'],
  [/clients_items/, 'product clients relation count audit'],
  [/proof_items_total/, 'product proof aggregate count audit'],
  [/aiagents_items/, 'product aiagents relation count audit'],
  [/page_content_schema/, 'page-content schema audit'],
]);
requireAll('tools/content-storage-faq-migration.php', [
  [/CIBlockSection/, 'FAQ section creation'],
  [/SetElementSection/, 'FAQ section link sync'],
]);
requireFile('tools/content-storage-services-seed.php');
requireFile('tools/content-storage-proof-tagging-helper.php');
requireAll('tools/content-storage-proof-tagging-helper.php', [
  [/needs_owner_review/, 'proof owner-review marker'],
  [/admin_edit_path/, 'Bitrix admin edit path for owner review'],
  [/GetProperty\([\s\S]*['"]PRODUCT['"]/, 'current PRODUCT tag read'],
  [/safe_for_release_evidence/, 'release evidence safety marker'],
]);
forbidPattern(
  'tools/content-storage-proof-tagging-helper.php',
  /['"](?:NAME|PREVIEW_TEXT|DETAIL_TEXT)['"]/,
  'raw proof content field output'
);
requireFile('tools/content-storage-proof-approval-template.php');
requireAll('tools/content-storage-proof-approval-template.php', [
  [/proof_tagging_approval\.v1/, 'proof approval template schema'],
  [/decision['"]?\s*=>\s*['"]pending|["']decision["']\s*:\s*["']pending/, 'pending-only generated decisions'],
  [/raw_copy_included/, 'raw copy exclusion marker'],
  [/admin_links_included/, 'admin link exclusion marker'],
]);
forbidPattern(
  'tools/content-storage-proof-approval-template.php',
  /['"](?:NAME|PREVIEW_TEXT|DETAIL_TEXT)['"]/,
  'raw proof content field output'
);
requireFile('tools/content-storage-proof-tagging-proposal.php');
requireAll('tools/content-storage-proof-tagging-proposal.php', [
  [/proof_tagging_approval\.v1/, 'proof tagging proposal schema'],
  [/proposal_only/, 'proposal-only marker'],
  [/public_render_approved['"]?\s*=>\s*false|["']public_render_approved["']\s*:\s*false/, 'public render blocked by proposal'],
  [/proposal_reason/, 'proposal reason field'],
]);
requireFile('tools/content-storage-proof-tagging-apply.php');
requireAll('tools/content-storage-proof-tagging-apply.php', [
  [/proof_tagging_approval\.v1/, 'proof tagging approval schema validation'],
  [/status must be approved/, 'approved-only apply gate'],
  [/SetPropertyValuesEx\([\s\S]*PRODUCT/, 'PRODUCT relation apply path'],
  [/safe_for_release_evidence/, 'release evidence safety marker'],
]);
forbidPattern(
  'tools/content-storage-proof-tagging-apply.php',
  /['"](?:NAME|PREVIEW_TEXT|DETAIL_TEXT)['"]/,
  'raw proof content field output'
);
requireFile('tools/content-storage-proof-public-render-apply.php');
requireAll('tools/content-storage-proof-public-render-apply.php', [
  [/proof_tagging_approval\.v1/, 'proof public render approval schema validation'],
  [/status must be approved/, 'approved-only public render apply gate'],
  [/PUBLIC_RENDER_APPROVED/, 'public render approval property'],
  [/productIdsForElement/, 'PRODUCT relation verification before public render'],
  [/SetPropertyValuesEx\([\s\S]*PUBLIC_RENDER_APPROVED/, 'PUBLIC_RENDER_APPROVED apply path'],
  [/safe_for_release_evidence/, 'release evidence safety marker'],
]);
forbidPattern(
  'tools/content-storage-proof-public-render-apply.php',
  /['"](?:PREVIEW_TEXT|DETAIL_TEXT)['"]/,
  'raw proof content field output'
);
requireFile('tools/content-storage-proof-approval-check.mjs');
requireAll('tools/content-storage-proof-approval-check.mjs', [
  [/proof_tagging_approval\.v1/, 'proof approval schema validation'],
  [/FORBIDDEN_KEYS/, 'raw proof content key guard'],
  [/public_render_approved/, 'public proof render approval gate'],
  [/--allow-draft/, 'draft approval mode'],
]);
requireFile('tools/content-storage-aiagents-tagging.php');
requireAll('tools/content-storage-aiagents-tagging.php', [
  [/findElementIdByCode\(\$productsIblockId,\s*['"]agents['"]\)/, 'AI agents product target lookup'],
  [/\[\s*['"]PRODUCT['"]\s*=>\s*\[\$agentsProductId\]\s*\]/, 'AI agents PRODUCT=agents tagging target'],
  [/safe_for_release_evidence/, 'AI agents tagging release evidence safety marker'],
  [/SetPropertyValuesEx\([\s\S]*PRODUCT/, 'AI agents PRODUCT relation apply path'],
]);
forbidPattern(
  'tools/content-storage-aiagents-tagging.php',
  /['"](?:NAME|PREVIEW_TEXT|DETAIL_TEXT)['"]/,
  'raw aiagents content field output'
);
requireDocFile('docs/workflow/content-storage-proof-tagging-approval-2026-06-05.draft.json');
requireDocFile('docs/workflow/content-storage-faq-fallback-retirement-2026-06-05.draft.json');
requireDocAll('docs/workflow/content-storage-faq-fallback-retirement-2026-06-05.draft.json', [
  [/product_blocks\.faq/, 'FAQ fallback retirement target'],
  [/retirement_allowed["']?\s*:\s*false/, 'FAQ fallback retirement blocked by default'],
  [/content_admin_editability_approved/, 'content admin editability gate'],
  [/qa_rollback_window_approved/, 'rollback-window gate'],
]);
requireDocFile('docs/workflow/content-storage-faq-fallback-retirement-2026-06-06.approved.json');
requireDocAll('docs/workflow/content-storage-faq-fallback-retirement-2026-06-06.approved.json', [
  [/product_blocks\.faq/, 'approved FAQ fallback retirement target'],
  [/retirement_allowed["']?\s*:\s*true/, 'approved FAQ fallback retirement allowed'],
  [/content_admin_editability_approved["']?\s*:\s*true/, 'approved content admin editability gate'],
  [/qa_rollback_window_approved["']?\s*:\s*true/, 'approved rollback-window gate'],
  [/backend_removal_approved["']?\s*:\s*true/, 'approved backend removal gate'],
  [/seo_no_regression_approved["']?\s*:\s*true/, 'approved SEO no-regression gate'],
]);
requireFile('tools/content-storage-faq-fallback-retirement-check.mjs');
requireAll('tools/content-storage-faq-fallback-retirement-check.mjs', [
  [/faq_fallback_retirement\.v1/, 'FAQ fallback retirement schema validation'],
  [/DEFAULT_DRAFT_DECISION/, 'FAQ fallback embedded production-safe draft'],
  [/REQUIRED_OWNER_GATES/, 'FAQ fallback owner gates'],
  [/REQUIRED_FINAL_RECHECKS/, 'FAQ fallback final rechecks'],
  [/Restore product_blocks\.faq fallback/, 'FAQ fallback rollback guard'],
]);
requireFile('tools/content-storage-aiagents-boundary-check.mjs');
requireAll('tools/content-storage-aiagents-boundary-check.mjs', [
  [/\/agents\/ renders product\.page/, '/agents/ product page guard'],
  [/tacticum:aiagents/, '/aiagents/ component guard'],
  [/Telegram bot prototype and service route/, '/aiagents/ service route guard'],
  [/PROPERTY.*PRODUCT|PRODUCT.*PROPERTY/, 'aiagents PRODUCT relation guard'],
]);
requireDocFile('docs/workflow/content-storage-page-content-model-2026-06-05.draft.json');
requireDocAll('docs/workflow/content-storage-page-content-model-2026-06-05.draft.json', [
  [/page_sections/, 'page sections target model'],
  [/page_blocks/, 'page blocks target model'],
  [/raw_html_blob/, 'raw HTML blob prohibition'],
  [/json_blob/, 'JSON blob prohibition'],
  [/service_catalog_pollution/, 'service catalog pollution prohibition'],
]);
requireDocFile('docs/workflow/content-storage-page-content-model-2026-06-06.approved.json');
requireDocAll('docs/workflow/content-storage-page-content-model-2026-06-06.approved.json', [
  [/status["']?\s*:\s*["']approved/, 'approved page-content model status'],
  [/schema_only/, 'schema-only approval scope'],
  [/seeds_copy["']?\s*:\s*false/, 'page-content seed blocked by model approval'],
  [/changes_public_runtime["']?\s*:\s*false/, 'page-content runtime switch blocked by model approval'],
  [/architect["']?\s*:\s*true/, 'architect owner gate'],
  [/content["']?\s*:\s*true/, 'content owner gate'],
  [/frontend["']?\s*:\s*true/, 'frontend owner gate'],
  [/qa["']?\s*:\s*true/, 'qa owner gate'],
  [/seo["']?\s*:\s*true/, 'seo owner gate'],
]);
requireFile('tools/content-storage-page-content-model-check.mjs');
requireAll('tools/content-storage-page-content-model-check.mjs', [
  [/page_content_model\.v1/, 'page-content model schema validation'],
  [/DEFAULT_MODEL/, 'page-content embedded production-safe model'],
  [/approvedSchemaOnlyModel/, 'page-content embedded schema-only approved model'],
  [/--write-approved-model/, 'page-content approved model writer'],
  [/FORBIDDEN_TARGETS/, 'narrow iblock target guard'],
  [/REQUIRED_WAVE_PAGES/, 'migration wave page coverage guard'],
]);
requireFile('tools/content-storage-page-content-migration.php');
requireAll('tools/content-storage-page-content-migration.php', [
  [/page_content_model\.v1/, 'page-content migration model schema validation'],
  [/status=approved/, 'page-content approved-only apply gate'],
  [/owner_gates/, 'page-content owner gates'],
  [/tacticum_page_sections/, 'page_sections iblock creation'],
  [/tacticum_page_blocks/, 'page_blocks iblock creation'],
  [/SECTION['"]?,\s*['"]Page section|['"]SECTION['"]\s*=>/, 'page_blocks SECTION link property'],
  [/Runtime switch: unchanged/, 'page-content no public runtime change guard'],
]);
requireFile('tools/content-storage-page-content-seed.php');
requireAll('tools/content-storage-page-content-seed.php', [
  [/\$migrationStatus\s*=\s*['"]shadow['"]/, 'page-content shadow migration status default'],
  [/propertyString\(\$sectionsIblockId,\s*\$sectionId,\s*['"]MIGRATION_STATUS['"]\)/, 'page-content seed preserves existing migration status'],
  [/FALLBACK_PARTIAL/, 'page-content fallback partial preservation'],
  [/Runtime switch: unchanged/, 'page-content seed no runtime switch guard'],
  [/safe_for_release_evidence/, 'page-content seed release evidence safety marker'],
  [/function line\s*\(/, 'page-content seed text output helper'],
  [/\/services\//, 'page-content wave 1 services seed'],
  [/\/price\//, 'page-content wave 1 price seed'],
  [/\/contacts\//, 'page-content wave 1 contacts seed'],
  [/\/offer\//, 'page-content wave 1 offer seed'],
  [/\/about\//, 'page-content wave 2 about seed'],
  [/\/calculator\//, 'page-content wave 2 calculator seed'],
  [/\/aiagents\//, 'page-content wave 2 aiagents seed'],
  [/ecosystem/, 'page-content wave 2 home seed'],
  [/calculator-chat-outcome/, 'page-content wave 2 calculator chat outcome template'],
  [/wave_2\|all|wave_2['"]\s*=>/, 'page-content seed wave 2 option'],
  [/does not change public runtime[\s\S]*does not retire PHP fallback partials/, 'page-content seed scope warning'],
]);
requireFile('tools/content-storage-page-content-live-approval-template.php');
requireAll('tools/content-storage-page-content-live-approval-template.php', [
  [/page_content_live_approval\.v1/, 'page-content live approval template schema'],
  [/source_switch_approved['"]?\s*=>\s*false|["']source_switch_approved["']\s*:\s*false/, 'page-content live approval source switch blocked'],
  [/decision['"]?\s*=>\s*['"]pending|["']decision["']\s*:\s*["']pending/, 'page-content live approval pending-only draft'],
  [/--wave=wave_1\|wave_2\|all/, 'page-content live approval wave scope usage'],
  [/WAVE_PAGES/, 'page-content live approval wave page map'],
  [/mutually exclusive/, 'page-content live approval page/wave exclusivity guard'],
  [/raw_copy_included/, 'page-content live approval raw copy exclusion marker'],
  [/admin_links_included/, 'page-content live approval admin link exclusion marker'],
  [/fallback_retirement_approved['"]?\s*=>\s*false|["']fallback_retirement_approved["']\s*:\s*false/, 'page-content live approval fallback retirement blocked'],
]);
forbidPattern(
  'tools/content-storage-page-content-live-approval-template.php',
  /['"](?:NAME|PREVIEW_TEXT|DETAIL_TEXT)['"]/,
  'raw page-content copy field output'
);
requireFile('tools/content-storage-page-content-live-approval-check.mjs');
requireAll('tools/content-storage-page-content-live-approval-check.mjs', [
  [/page_content_live_approval\.v1/, 'page-content live approval schema validation'],
  [/FORBIDDEN_KEYS/, 'page-content live approval raw content key guard'],
  [/source_switch_approved/, 'page-content source switch approval blocker'],
  [/fallback_retirement_approved/, 'page-content fallback retirement approval blocker'],
  [/--allow-draft/, 'page-content live approval draft mode'],
  [/--self-test/, 'page-content live approval self-test mode'],
  [/\/about\//, 'page-content live approval wave 2 about section allowlist'],
  [/\/calculator\//, 'page-content live approval wave 2 calculator section allowlist'],
  [/\/aiagents\//, 'page-content live approval wave 2 aiagents section allowlist'],
]);
requireFile('tools/content-storage-page-content-live-apply.php');
requireAll('tools/content-storage-page-content-live-apply.php', [
  [/page_content_live_approval\.v1/, 'page-content live apply approval schema validation'],
  [/status must be approved/, 'page-content live apply approved-only gate'],
  [/SetPropertyValuesEx\([\s\S]*MIGRATION_STATUS/, 'page-content live status apply path'],
  [/source_switch_approved/, 'page-content live apply source switch blocked'],
  [/Runtime switch: unchanged/, 'page-content live apply no runtime switch guard'],
  [/safe_for_release_evidence/, 'page-content live apply release evidence safety marker'],
  [/\/about\//, 'page-content live apply wave 2 about section allowlist'],
  [/\/calculator\//, 'page-content live apply wave 2 calculator section allowlist'],
  [/\/aiagents\//, 'page-content live apply wave 2 aiagents section allowlist'],
]);
forbidPattern(
  'tools/content-storage-page-content-live-apply.php',
  /['"](?:NAME|PREVIEW_TEXT|DETAIL_TEXT)['"]/,
  'raw page-content copy field output'
);
requireFile('tools/content-storage-page-content-fallback-retirement-template.php');
requireAll('tools/content-storage-page-content-fallback-retirement-template.php', [
  [/page_content_fallback_retirement\.v1/, 'page-content fallback retirement template schema'],
  [/retirement_allowed['"]?\s*=>\s*false|["']retirement_allowed["']\s*:\s*false/, 'page-content fallback retirement blocked by default'],
  [/raw_copy_included/, 'page-content fallback retirement raw copy exclusion marker'],
  [/admin_links_included/, 'page-content fallback retirement admin link exclusion marker'],
  [/fallback_partial_values_included/, 'page-content fallback partial path exclusion marker'],
  [/Fallback retirement is a separate code change and deployment/, 'page-content fallback retirement scope warning'],
  [/--wave=wave_1\|wave_2\|all/, 'page-content fallback retirement wave scope usage'],
  [/WAVE_PAGES/, 'page-content fallback retirement wave page map'],
  [/mutually exclusive/, 'page-content fallback retirement page/wave exclusivity guard'],
  [/\/about\//, 'page-content fallback retirement wave 2 about section allowlist'],
  [/\/calculator\//, 'page-content fallback retirement wave 2 calculator section allowlist'],
  [/\/aiagents\//, 'page-content fallback retirement wave 2 aiagents section allowlist'],
]);
forbidPattern(
  'tools/content-storage-page-content-fallback-retirement-template.php',
  /['"](?:NAME|PREVIEW_TEXT|DETAIL_TEXT)['"]/,
  'raw page-content copy field output'
);
requireFile('tools/content-storage-page-content-fallback-retirement-check.mjs');
requireAll('tools/content-storage-page-content-fallback-retirement-check.mjs', [
  [/page_content_fallback_retirement\.v1/, 'page-content fallback retirement schema validation'],
  [/FORBIDDEN_KEYS/, 'page-content fallback retirement raw content key guard'],
  [/retirement_allowed/, 'page-content fallback retirement allowed gate'],
  [/admin_editability_approved/, 'page-content fallback retirement admin editability gate'],
  [/page_content\.source=fallback/, 'page-content fallback retirement rollback source'],
  [/WAVE_PAGES/, 'page-content fallback retirement checker wave page map'],
  [/page-content:source:http:wave2:prod/, 'page-content fallback retirement scoped source recheck'],
  [/\/about\//, 'page-content fallback retirement checker wave 2 about allowlist'],
  [/\/calculator\//, 'page-content fallback retirement checker wave 2 calculator allowlist'],
  [/\/aiagents\//, 'page-content fallback retirement checker wave 2 aiagents allowlist'],
  [/--allow-draft/, 'page-content fallback retirement draft mode'],
  [/--self-test/, 'page-content fallback retirement self-test mode'],
]);
requireFile('tools/page-content-source-http-check.mjs');
requireAll('tools/page-content-source-http-check.mjs', [
  [/TACTICUM_EXPECT_PAGE_CONTENT_SOURCE/, 'page-content HTTP expected source env'],
  [/data-page-content-source/, 'page-content HTTP source marker check'],
  [/data-page-content-page/, 'page-content HTTP page marker check'],
  [/data-page-content-section/, 'page-content HTTP section marker check'],
  [/data-page-content-template/, 'page-content HTTP template marker check'],
  [/expected source=fallback|expectedSource === ['"]fallback['"]/, 'page-content HTTP fallback mode'],
  [/validateBitrixMarkers/, 'page-content HTTP bitrix mode'],
  [/\/services\//, 'page-content HTTP services page'],
  [/\/price\//, 'page-content HTTP price page'],
  [/\/contacts\//, 'page-content HTTP contacts page'],
  [/\/offer\//, 'page-content HTTP offer page'],
  [/DEFAULT_PAGES/, 'page-content HTTP default live wave page list'],
  [/\/about\//, 'page-content HTTP wave 2 about page'],
  [/\/calculator\//, 'page-content HTTP wave 2 calculator page'],
  [/\/aiagents\//, 'page-content HTTP wave 2 aiagents page'],
  [/calculator-chat-outcome/, 'page-content HTTP wave 2 calculator template expectation'],
]);
requireFile('local/php_interface/include/page_content.php');
requireAll('local/php_interface/include/page_content.php', [
  [/source\(\)\s*!==\s*['"]bitrix['"]/, 'page-content source gate before Bitrix reads'],
  [/tacticum_page_content_live_status/, 'page-content live status helper'],
  [/Repository::fetchSection/, 'page-content repository facade'],
  [/Renderer::render/, 'page-content renderer facade'],
]);
requireAll('local/php_interface/init.php', [
  [/include\/page_content\.php/, 'page-content facade bootstrap include'],
]);
requireFile('local/lib/Tacticum/PageContent/Repository.php');
requireAll('local/lib/Tacticum/PageContent/Repository.php', [
  [/Config::iblockId\(['"]page_sections['"]\)/, 'page-content repository section iblock config'],
  [/Config::iblockId\(['"]page_blocks['"]\)/, 'page-content repository block iblock config'],
  [/PROPERTY_MIGRATION_STATUS/, 'page-content repository live status filter'],
  [/PROPERTY_SECTION/, 'page-content block section relation filter'],
]);
requireFile('local/lib/Tacticum/PageContent/Renderer.php');
requireAll('local/lib/Tacticum/PageContent/Renderer.php', [
  [/product-card-grid/, 'page-content product card renderer'],
  [/step-list/, 'page-content step renderer'],
  [/tech-grid/, 'page-content tech renderer'],
  [/feature-card-grid/, 'page-content feature renderer'],
  [/calculator-chat-outcome/, 'page-content calculator chat outcome renderer'],
  [/CalculatorRenderer::renderChatOutcome/, 'page-content calculator renderer handoff'],
  [/HomeRenderAttributes::linkDataAttributes/, 'page-content homepage smoke marker handoff'],
  [/contact-card-grid/, 'page-content contact renderer'],
  [/cta-band/, 'page-content cta renderer'],
  [/sectionOpen/, 'page-content rendered source marker helper'],
  [/source=contact_config/, 'page-content contact config bridge'],
]);
requireFile('local/lib/Tacticum/PageContent/CalculatorRenderer.php');
requireAll('local/lib/Tacticum/PageContent/CalculatorRenderer.php', [
  [/tacticum:chat\.surface/, 'page-content calculator chat surface preservation'],
  [/calculator/, 'page-content calculator surface context'],
]);
requireFile('local/lib/Tacticum/PageContent/HomeRenderAttributes.php');
requireAll('local/lib/Tacticum/PageContent/HomeRenderAttributes.php', [
  [/data-home-product-link/, 'page-content homepage product smoke markers'],
  [/data-home-commercial-link/, 'page-content homepage commercial smoke markers'],
  [/product=/, 'page-content homepage product metadata mapping'],
  [/\/platform\/[\s\S]*\/agents\/[\s\S]*\/dev\/[\s\S]*\/forum\//, 'page-content homepage product href mapping'],
]);
requireFile('local/lib/Tacticum/PageContent/RenderSupport.php');
requireAll('local/lib/Tacticum/PageContent/RenderSupport.php', [
  [/OFFICE_ADDRESS/, 'page-content contact office context'],
  [/data-page-content-source="bitrix"/, 'page-content rendered source marker'],
  [/data-page-content-page/, 'page-content rendered page marker'],
  [/data-page-content-section/, 'page-content rendered section marker'],
  [/data-page-content-template/, 'page-content rendered template marker'],
  [/data-home-block/, 'page-content homepage block smoke marker'],
  [/function\s+href\b[\s\S]*mailto\|tel/, 'page-content href sanitizer'],
  [/function\s+icon\b[\s\S]*ri-\[a-z0-9-\]/, 'page-content icon sanitizer'],
]);
for (const [path, page, section] of [
  ['local/components/tacticum/services.page/templates/.default/parts/delivery-layer.php', '/services/', 'delivery-layer'],
  ['local/components/tacticum/services.page/templates/.default/parts/process.php', '/services/', 'process'],
  ['local/components/tacticum/services.page/templates/.default/parts/tech.php', '/services/', 'tech'],
  ['local/components/tacticum/price.page/templates/.default/parts/features.php', '/price/', 'features'],
  ['local/components/tacticum/price.page/templates/.default/parts/workstreams.php', '/price/', 'workstreams'],
  ['local/components/tacticum/contacts.page/templates/.default/parts/routing.php', '/contacts/', 'routing'],
  ['local/components/tacticum/contacts.page/templates/.default/parts/cards.php', '/contacts/', 'cards'],
  ['local/components/tacticum/offer.catalog/templates/.default/parts/product-bridge.php', '/offer/', 'product-bridge'],
  ['local/components/tacticum/offer.catalog/templates/.default/parts/bottom-cta.php', '/offer/', 'bottom-cta'],
]) {
  requireAll(path, [
    [/tacticum_page_content_render_if_live/, 'page-content live renderer guard'],
    [new RegExp(page.replaceAll('/', '\\/')), `page-content page key ${page}`],
    [new RegExp(section.replaceAll('-', '\\-')), `page-content section key ${section}`],
    [/Fallback body retired/, 'page-content fallback body retirement marker'],
  ]);
  forbidPattern(path, /<section\b/i, 'retired page-content fallback section body');
}
for (const [path, page, section] of [
  ['local/components/tacticum/home.page/templates/.default/parts/ecosystem.php', '/', 'ecosystem'],
  ['local/components/tacticum/home.page/templates/.default/parts/fit-matrix.php', '/', 'fit-matrix'],
  ['local/components/tacticum/home.page/templates/.default/parts/commercial.php', '/', 'commercial'],
  ['local/components/tacticum/about.page/templates/.default/parts/company-trust.php', '/about/', 'company-trust'],
  ['local/components/tacticum/about.page/templates/.default/parts/values-team.php', '/about/', 'values-team'],
  ['local/components/tacticum/about.page/templates/.default/parts/career-final.php', '/about/', 'career-final'],
  ['local/components/tacticum/calculator.page/templates/.default/template.php', '/calculator/', 'calculator-outcome-cards'],
  ['local/components/tacticum/calculator.page/templates/.default/template.php', '/calculator/', 'product-aware-estimate-cards'],
  ['local/components/tacticum/aiagents/templates/.default/parts/agents-bridge.php', '/aiagents/', 'agents-bridge'],
  ['local/components/tacticum/aiagents/templates/.default/parts/how-it-works.php', '/aiagents/', 'how-it-works'],
  ['local/components/tacticum/aiagents/templates/.default/parts/services.php', '/aiagents/', 'services'],
]) {
  requireAll(path, [
    [/tacticum_page_content_render_if_live/, 'page-content wave 2 live renderer guard'],
    [new RegExp(page.replaceAll('/', '\\/')), `page-content wave 2 page key ${page}`],
    [new RegExp(section.replaceAll('-', '\\-')), `page-content wave 2 section key ${section}`],
    [/Fallback body retired/, 'page-content wave 2 fallback body retirement marker'],
  ]);
}
for (const path of [
  'local/components/tacticum/home.page/templates/.default/parts/ecosystem.php',
  'local/components/tacticum/home.page/templates/.default/parts/fit-matrix.php',
  'local/components/tacticum/home.page/templates/.default/parts/commercial.php',
  'local/components/tacticum/aiagents/templates/.default/parts/agents-bridge.php',
  'local/components/tacticum/aiagents/templates/.default/parts/how-it-works.php',
  'local/components/tacticum/aiagents/templates/.default/parts/services.php',
]) {
  forbidPattern(path, /<section\b/i, 'retired page-content wave 2 fallback section body');
}
for (const [path, pattern, label] of [
  ['local/components/tacticum/about.page/templates/.default/parts/company-trust.php', /Vendor trust|Почему product-first модель требует сильной delivery-команды/, 'retired about company-trust fallback body'],
  ['local/components/tacticum/about.page/templates/.default/parts/values-team.php', /Ценности и подход|От консалтинга до результата/, 'retired about values-team fallback body'],
  ['local/components/tacticum/about.page/templates/.default/parts/career-final.php', /<section id="career-section"|Корпоративная культура|Преимущества работы у нас/, 'retired about career-final fallback body'],
  ['local/components/tacticum/calculator.page/templates/.default/template.php', /AI-калькулятор для предварительной оценки проекта|Что вы получите после диалога|Что можно оценить через AI-калькулятор/, 'retired calculator fallback body'],
]) {
  forbidPattern(path, pattern, label);
}
requireDocFile('docs/workflow/content-storage-release-runbook-2026-06-05.md');

forbidPattern(
  'local/templates/tacticum/components/bitrix/news.list/services/template.php',
  /Расчет проекта|hasOfferService|Смотреть расчеты/,
  'hardcoded services fallback card'
);

const packageSource = requireFile('package.json');
for (const scriptName of [
  'content:storage:governance:check',
  'content:storage:audit',
  'content:storage:audit:strict',
  'content:storage:faq:migrate',
  'content:storage:faq:migrate:apply',
  'content:storage:services:seed',
  'content:storage:services:seed:apply',
  'content:storage:proof:tagging-helper',
  'content:storage:proof:approval-template',
  'content:storage:proof:tagging-proposal',
  'content:storage:proof:tagging-apply',
  'content:storage:proof:tagging-apply:apply',
  'content:storage:proof:approval:check',
  'content:storage:proof:approval:self-test',
  'content:storage:aiagents:tagging',
  'content:storage:aiagents:tagging:apply',
  'content:storage:faq-fallback-retirement:check',
  'content:storage:aiagents-boundary:check',
  'content:storage:page-content-model:check',
  'content:storage:page-content-model:approved-check',
  'content:storage:page-content-model:approved-template',
  'content:storage:page-content:migrate',
  'content:storage:page-content:migrate:apply',
  'content:storage:page-content:seed',
  'content:storage:page-content:seed:apply',
  'content:storage:page-content:live-approval-template',
  'content:storage:page-content:live-approval-template:wave2',
  'content:storage:page-content:live-approval:check',
  'content:storage:page-content:live-approval:self-test',
  'content:storage:page-content:live-apply',
  'content:storage:page-content:live-apply:apply',
  'content:storage:page-content:fallback-retirement-template',
  'content:storage:page-content:fallback-retirement-template:wave2',
  'content:storage:page-content:fallback-retirement:check',
  'content:storage:page-content:fallback-retirement:self-test',
  'content:storage:page-content:seed:wave2',
  'content:storage:page-content:seed:wave2:apply',
  'page-content:source:http:fallback:prod',
  'page-content:source:http:prod',
  'page-content:source:http:wave2:fallback:prod',
  'page-content:source:http:wave2:prod',
]) {
  if (!packageSource.includes(`"${scriptName}"`)) {
    failures.push(`package.json: missing ${scriptName} script.`);
  }
}

if (failures.length > 0) {
  console.error('Content storage governance check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('Content storage governance check passed.');
if (!docsWorkflowAvailable) {
  console.log('Docs workflow directory is absent; checked production-safe embedded baselines instead of repo docs.');
}
