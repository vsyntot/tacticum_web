# Bitrix Componentization Issue Backlog — 2026-06-05

Дата: 05.06.2026

Статус: issue-ready backlog for `bitrix-componentization-gap-analysis-2026-06-05.md`.

## Import Rules

- Issue prefix: `BPC-WP-*`.
- Every implementation issue must reference affected `BPC-*` gap IDs.
- Do not combine `/price/`, product renderer, REST helper split and forms/chat split in one issue.
- Code closure requires verification commands and before/after behavior evidence.
- Docs-only completion does not close code gaps.

## BPC-WP-01 — Architecture Guardrails And File-Size Budgets

Status: implemented-local / verification-passed-2026-06-05

Priority: P1

Workflow lane: Fast Fix

Owners: Architect, QA, Frontend, Backend

Covered gaps: `BPC-GUARD-001`, `BPC-GUARD-002`, `BPC-GUARD-004`, `BPC-CMP-003`

Objective: make known architecture debt visible to CI and prevent new large non-componentized files.

Affected areas:

- `tools/bitrix-architecture-check.mjs`
- `package.json`
- `docs/workflow/README.md`
- local component directories

Acceptance criteria:

- Check covers product component boundary or temporary allowlist.
- Check covers REST index ownership markers or temporary allowlist.
- Check covers file-size budget with current debt allowlist.
- Check covers cache/result policy for every local component.
- Definition of Componentized Done exists in workflow docs.
- Existing `npm run bitrix:check` stays green after accepted allowlists.

Verification:

```bash
npm run bitrix:check
npm run js:check
```

Implementation note 05.06.2026:

- `tools/bitrix-architecture-check.mjs` now tracks product component boundary debt, REST index ownership markers, local component metadata and file-size budgets with BPC allowlist.
- `local/components/tacticum/component_cache_policy.json` now records cache/result ownership for all 18 local components; `bitrix:check` validates coverage, evidence paths, child Bitrix cache params and service-cache markers.
- Known monoliths are allowed only up to their recorded line count; growth fails the guard.
- `docs/workflow/README.md` now contains Definition Of Componentized Done.
- Verified with `npm run bitrix:check` and `npm run js:check`.

## BPC-WP-02 — Product Page Local Component

Status: implemented-local / product-block-policy-closed

Priority: P1

Workflow lane: Full Feature

Owners: Architect, Frontend, Backend, QA

Covered gaps: `BPC-ARCH-001`, `BPC-CMP-002`, `BPC-GUARD-003`

Objective: replace global include renderer calls on `/platform/`, `/agents/`, `/dev/`, `/forum/` with `tacticum:product.page`.

Affected areas:

- `local/components/tacticum/product.page/`
- `local/php_interface/include/product_page.php`
- `local/php_interface/include/product_page_blocks/*`
- `platform/index.php`, `agents/index.php`, `dev/index.php`, `forum/index.php`

Acceptance criteria:

- Product public entries call one local component.
- Product component has `component.php`, `.parameters.php`, `.description.php`, template files.
- Existing `data-product-source` and `data-product-block` contracts stay stable.
- Degraded/unavailable product state remains safe.
- SEO/schema output remains equivalent.

Verification:

```bash
npm run bitrix:check
npm run seo:check
npm run visual:smoke:css-local
npm run browser:console:css-local
```

Implementation note 05.06.2026:

- Added `local/components/tacticum/product.page/` with `component.php`, `.parameters.php`, `.description.php` and `.default/template.php`.
- `/platform/`, `/agents/`, `/dev/`, `/forum/` now call `IncludeComponent("tacticum:product.page")` in `PREPARE_ONLY` mode before `prolog_after`, then render the same prepared `PAGE_DATA` after `prolog_after`.
- Direct `tacticum_product_page_data()`, `tacticum_product_page_schema()` and `tacticum_render_product_page()` calls were removed from product page entries.
- `seo-check.mjs` now enforces the two-phase component contract and verifies schema/SEO wiring in `tacticum:product.page`.
- Added `tacticum:product.hero` and `tacticum:product.lead.cta` as formal product block components. `product.hero` owns hero `data-product-source` / `data-product-code` / unavailable-state markers; `product.lead.cta` owns product CTA defaults and delegates the form runtime to `tacticum:lead.cta`.
- `local/php_interface/include/product_page_blocks/page.php` now delegates hero and lead CTA to product block components and is reduced to an 87-line orchestration function.
- `tools/bitrix-architecture-check.mjs` guards that `page.php` keeps hero/CTA delegated to `tacticum:product.hero` and `tacticum:product.lead.cta` instead of inlining those blocks again.
- Verified with `npm run bitrix:check`, `npm run seo:check`, `npm run component:states:check`, `npm run js:check` and PHP syntax lint for the new component and product page entries.
- Added `local/components/tacticum/product_block_policy.json`; it defines every product block as either `component` (`hero`, `lead-cta`) or accepted `nested-template` with renderer/evidence/required-literal contract.
- `tools/bitrix-architecture-check.mjs` validates the product block policy, component files and nested-template evidence; `tools/fixtures/component-state-smoke.json` includes the policy in product page state evidence.
- Product block boundary is closed locally; future block promotion is now a policy change, not an open BPC gap.

## BPC-WP-03 — Public Page Entry Thinning

Status: implemented-local / wrapper-contract-closed

Priority: P1

Workflow lane: Full Feature

Owners: Frontend, Designer, QA, PM

Covered gaps: `BPC-CMP-001`, `BPC-CMP-004`, `BPC-CMP-005`

Objective: reduce thick public entries into Bitrix orchestration files.

Affected areas:

- `index.php` (implemented slice: body moved to `tacticum:home.page`)
- `about/index.php` (implemented slice: body moved to `tacticum:about.page`)
- `services/index.php` (implemented slice: body moved to `tacticum:services.page`)
- `contacts/index.php` (implemented slice: body moved to `tacticum:contacts.page`)
- `calculator/index.php` (implemented slice: body moved to `tacticum:calculator.page`)
- `price/index.php` (implemented shell slice: body moved to `tacticum:price.page`; configurator decomposition closed in BPC-WP-04)
- local components under `local/components/tacticum/`

Acceptance criteria:

- Each touched page keeps title, description, canonical, H1 and sitemap URL stable.
- Repeated or behavior-bearing sections move behind local components/section templates.
- No form/REST/analytics contract changes.
- Visual smoke passes for all sitemap URLs.

Verification:

```bash
npm run seo:check
npm run visual:smoke:css-local
npm run browser:console:css-local
```

Implementation notes:

- 05.06.2026: `/calculator/` public entry reduced to SEO/assets/bootstrap + `tacticum:calculator.page`; body markup, `tacticum:chat.surface`, `tacticum:faq.section` and `tacticum:lead.cta` moved to `local/components/tacticum/calculator.page/templates/.default/template.php`.
- 05.06.2026: `/contacts/` public entry reduced to SEO/bootstrap + `tacticum:contacts.page`; office/map data moved to component controller, body split into short component parts.
- 05.06.2026: `/price/` public entry reduced to SEO/assets/bootstrap + `tacticum:price.page`; page shell, light chat, FAQ and CTA moved to short component parts; `news.list/price` template/script decomposition is closed in BPC-WP-04.
- 05.06.2026: `/services/` public entry reduced to SEO/assets/bootstrap + `tacticum:services.page`; delivery shell, `content.list` calls, process, tech, CTA and FAQ moved to short component parts.
- 05.06.2026: `/about/` public entry reduced to SEO/bootstrap + `tacticum:about.page`; company/trust/team/stack/career body moved to short component parts.
- 05.06.2026: `/` public entry reduced to SEO/assets/bootstrap + `tacticum:home.page`; homepage router, hero chat surface, content lists, legacy calculator preview, FAQ and CTA moved to short component parts.
- 05.06.2026: `local/components/tacticum/component_wrapper_policy.json` now fixes `content.list`, `content.detail` and `faq.section` wrapper contracts: accepted params, delegated `bitrix:*` child component, result keys, required child params, cache params, template ownership and FAQ missing-state marker.
- 05.06.2026: `tools/component-state-smoke-check.mjs` validates the wrapper policy against PHP wrapper source; `tools/fixtures/component-state-smoke.json` includes the policy in FAQ/content wrapper fixture evidence.
- 05.06.2026: `tools/seo-check.mjs` now reads componentized page render sources for `/`, `/about/`, `/calculator/`, `/contacts/`, `/price/` and `/services/`; `tools/bitrix-architecture-check.mjs` removed all tracked thick public entries from the debt allowlist.
- Local verification: `php -l` for touched PHP files, `npm run bitrix:check`, `npm run seo:check`, `npm run component:states:check`, `npm run css:check`, `npm run js:check`. Rendered visual/browser smoke remains deploy gate because current smoke scripts use production HTML.

## BPC-WP-04 — `/price/` Configurator Decomposition

Status: implemented-local / static-verification-passed-2026-06-05

Priority: P1

Workflow lane: Full Feature

Owners: Frontend, QA, Backend

Covered gaps: `BPC-FE-001`, `BPC-GUARD-003`, `BPC-CMP-001`

Objective: split the 928-line `/price/` JS configurator while preserving behavior.

Affected areas:

- `local/templates/tacticum/components/bitrix/news.list/price/script.js`
- `local/templates/tacticum/components/bitrix/news.list/price/template.php`
- `local/templates/tacticum/components/bitrix/news.list/price/result_modifier.php`
- `local/rest/tacticum_sale_staff.php` only for contract verification, not payload change

Acceptance criteria:

- Filters, level selection, presets, team state, modal, monthly budget and hidden payload are isolated.
- Mixed legacy/new selector compatibility is either preserved or removed only after deployed-template evidence.
- Staff order payload remains compatible.
- Browser smoke for `/price/` passes.

Verification:

```bash
npm run js:check
npm run browser:smoke:price
npm run browser:console:css-local
```

Implementation note 05.06.2026:

- `local/templates/tacticum/components/bitrix/news.list/price/script.js` reduced from 928 lines to a 78-line orchestrator.
- Added focused component-owned JS chunks: `price-configurator-utils.js`, `price-configurator-fallback.js`, `price-configurator-catalog.js`, `price-configurator-filters.js`, `price-configurator-order-state.js`, `price-configurator-order-render.js`, `price-configurator-modal.js`.
- `template.php` reduced from 383 to 32 lines and split into `parts/catalog.php`, `parts/price-card.php`, `parts/order-modal.php`.
- Preserved `data-price-*`, legacy `.pricing-card` / `.filter-tab` / `.order-specialist-btn` compatibility, `/local/rest/tacticum_sale_staff.php`, `workers_json`, `team_preset` and `monthly_budget_estimate`.
- Removed `/price/` script/template from BPC size-debt allowlist; `npm run bitrix:check` now passes with no tracked BPC debts after the full BPC implementation sequence.
- Local verification: PHP syntax lint for price template files, `npm run js:check`, `npm run bitrix:check`, `npm run seo:check`, `git diff --check`.
- Focused browser action smoke with local JS injection over production `/price/` passed on desktop/mobile: runtime errors `0`, SEO/FAQ ok, actions `10/13`, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-05T06-59-22-966Z/manifest.json`. Full post-deploy smoke remains a release gate.

## BPC-WP-05 — Backend Service Layer And Iblock Repositories

Status: implemented-local / service-bootstrap-request-and-repository-boundaries-closed

Priority: P1

Workflow lane: Full Feature

Owners: Architect, Backend, QA

Covered gaps: `BPC-ARCH-002`, `BPC-ARCH-003`, `BPC-ARCH-004`, `BPC-ARCH-005`

Objective: create a maintainable D7-compatible service/repository layer for product and offer domains.

Affected areas:

- `local/lib/Tacticum/...` or agreed equivalent
- `local/php_interface/include/product_content.php`
- `local/php_interface/include/offer_catalog.php`
- `local/php_interface/include/offer_page.php`
- `local/php_interface/init.php`

Acceptance criteria:

- New shared domain logic has class/service home.
- Compatibility functions remain only as facades during migration.
- Raw iblock queries are centralized for product, offer, public API and content-wrapper/result-modifier domains.
- `init.php` remains thin and does not eagerly load product runtime/renderers or one-off migrations.

Verification:

```bash
npm run bitrix:check
npm run product:content:safety:check
npm run seo:check
```

Implementation note 05.06.2026:

- Added `local/php_interface/include/autoload.php` and registered it from thin `init.php`.
- Added namespaced service classes under `local/lib/Tacticum/`: `Product/ContentRuntime.php`, `Product/ContentMapper.php`, `Product/ContentRepository.php`, `Product/ContentService.php`, `Offer/CatalogMapper.php`, `Offer/CatalogFilters.php`, `Offer/CatalogRepository.php`, `Offer/CatalogService.php`, `Offer/CatalogCache.php`.
- Added `local/lib/Tacticum/Content/IblockRepository.php` for shared content/API element, property and section reads.
- Converted `local/php_interface/include/product_content.php` into a 212-line compatibility facade; product content config/source/cache key/tag cache, raw product iblock reads, block/use-case mapping and cached Bitrix data assembly now live in `local/lib`.
- Split `local/php_interface/include/product_page.php` into a 115-line compatibility facade over `Tacticum\Product\Page\Text`, `Cta`, `DataProvider` and `Schema`; product text sanitizing, safe href/icon/grid normalization, CTA lead context/scenario options, fallback/unavailable data and product schema logic now live in `local/lib`.
- Reduced global bootstrap footprint: `init.php` now loads `product_content_events.php` instead of `product_content.php`, and no longer includes `product_page.php` or `content_migrations.php` on every request.
- Added 58-line `local/php_interface/include/product_content_events.php` for product cache event registration; it lazy-loads `product_content.php` only inside iblock change handlers before calling the existing cache invalidation functions.
- `local/php_interface/include/product_page.php` now lazy-loads `product_content.php` only when the product page facade is actually used.
- `local/php_interface/include/content_migrations.php` now runs the one-off contacts/policy migration only behind explicit `TACTICUM_RUN_CONTENT_MIGRATIONS`.
- Converted legacy global `TacticumOfferCatalogCache`, `TacticumOfferCatalogRepository`, `TacticumOfferCatalogService` and `tacticum_offer_catalog_*` functions into facades over `Tacticum\Offer\*`; `offer_catalog.php` is now a 129-line compatibility facade instead of a 786-line domain implementation.
- Split `local/php_interface/include/offer_page.php` into a 68-line compatibility facade over `Tacticum\Offer\Page\Query`, `Resolver` and `Response`; route state, legacy/query redirects, SEO/template setup and component params now live in `local/lib`.
- Added `Tacticum\Offer\Page\RequestSnapshot` as the Bitrix `Context::getCurrent()->getRequest()` boundary; `Query` and `Resolver` no longer read `$_GET`, `$_REQUEST` or `$_SERVER` directly while keeping explicit array arguments for CLI/test sanity.
- Moved public API reads in `/local/api/cases.php`, `faq.php`, `rates.php` and `services.php` to `tacticum_api_fetch_content_items()`, backed by `Tacticum\Content\IblockRepository`; endpoints remain 35-40-line JSON mappers.
- Moved `content.detail` first-element lookup, `faq.section` section-code lookup and `news.list` `cases` / `aiagents` / `price` result-modifier section reads to `Tacticum\Content\IblockRepository`.
- Split `local/php_interface/include/seo_helpers.php` into a 41-line compatibility facade over `Tacticum\Seo\JsonLd`, `FaqSchema` and `Meta`; canonical/OpenGraph/Twitter/robots/default JSON-LD/FAQ schema behavior is preserved.
- Split `local/components/tacticum/lead.cta/component.php` into an 18-line controller over `Tacticum\Component\LeadCtaParams`; lead context, scenario options, defaults and endpoint/close mode normalization are preserved.
- Split `local/components/tacticum/offer.catalog/templates/.default/template.php` into a 52-line orchestrator plus 19-79-line `parts/*`; hero, product bridge, quick filters, filter form, results, pagination and CTA markup keep the same server-side data contract.
- Split `local/components/tacticum/aiagents/templates/.default/template.php` into a 21-line orchestrator plus 15-83-line `parts/*`; `tacticum:content.list`, `tacticum:faq.section`, Telegram resolver link and `aiagents-inline` form hidden lead context are preserved.
- Split `local/templates/tacticum/components/bitrix/news.detail/offer/template.php` into a 54-line orchestrator plus 13-182-line `parts/*`; estimate prefill links, product relation block, risk sections, `offer-cta` hidden lead context and FAQ wrapper are preserved.
- Split `local/php_interface/include/calcrequests_rest.php` into a 78-line compatibility facade over `Tacticum\CalcRequests\Access`, `CodeGenerator`, `PropertyMapper`, `Registrar`, `Repository`, `Response`, `Runtime`, `Service` and `Validator`; `calcrequests.list/add` callback names and response shapes are preserved.
- `tools/bitrix-architecture-check.mjs` now includes `local/lib` in file-size budgets, requires the Tacticum autoload boundary, guards lazy product-content bootstrap, guards the offer Bitrix request boundary/no-superglobal rule, guards shared content/API repository usage, blocks eager `content_migrations.php` / `product_content.php` / `product_page.php` includes in `init.php`, and no longer allowlists `lead.cta/component.php`, `offer.catalog` template, `aiagents` template, offer detail template, `seo_helpers.php`, `offer_page.php`, `product_page.php`, `product_content.php`, `offer_catalog.php` or `calcrequests_rest.php` as size debts.
- Local verification: PHP syntax lint for touched PHP files, `npm run bitrix:check`, `npm run rest:endpoints:check`, `npm run seo:check`, `npm run component:states:check`, `npm run js:check`, `npm run product:content:safety:check`, `git diff --check`.
- Focused `calcrequests` facade/validator CLI sanity passed with local autoload.
- Focused product page facade CLI sanity passed for fallback data, safe href fallback, CTA lead context and schema.
- Split `product_page_blocks/common.php` by moving `fit_guide` into a 101-line partial; `common.php` is now 179 lines and no longer allowlisted as a size debt.
- Focused `offer_page` resolver CLI sanity passed for semantic query redirect and empty catalog redirect.
- Focused SEO facade CLI sanity passed with mocked `$APPLICATION` for canonical, OG/Twitter, JSON-LD and robots meta.
- Focused `LeadCtaParams` CLI sanity passed for scenario normalization, hidden lead context, endpoint filtering and close mode preservation.
- Remaining scope: REST helper split is handled in BPC-WP-06; `BPC-ARCH-001` - `BPC-ARCH-005` are closed locally. Future domain/service expansion is tracked as `BPC-GUARD-001` accepted-monitor, not as an open implementation gap.

## BPC-WP-06 — REST Helper Split And REST Namespace Cleanup

Status: implemented-local / static-verification-passed-2026-06-05

Priority: P1

Workflow lane: Security / Integration

Owners: Backend, Security, QA, DevOps

Covered gaps: `BPC-REST-001`, `BPC-REST-002`, `BPC-REST-003`

Objective: split `rest_helpers.php` responsibilities and clarify `local/rest/` as JSON/API endpoint namespace.

Affected areas:

- `local/rest/rest_helpers.php`
- `local/rest/*.php`
- `local/rest/index.php`
- REST/API workflow docs

Acceptance criteria:

- Existing endpoint response shapes remain unchanged.
- Security controls remain in the required order for POST endpoints.
- `local/rest/index.php` is moved, protected, documented or blocked as non-public legacy route.
- Endpoint risk classes are documented for future endpoints.

Verification:

```bash
npm run bitrix:check
npm run rest:endpoints:check
npm run config:runtime:check
```

Implementation note 05.06.2026:

- Split `local/rest/rest_helpers.php` from 1119 lines into a 129-line compatibility facade.
- Added `local/lib/Tacticum/Rest/Api.php`, `Config.php`, `ConfigValidator.php`, `Response.php`, `Security.php`, `RateLimiter.php`, `Masker.php`, `Outbound.php`, `Text.php`.
- Kept all existing `tacticum_rest_*` and `tacticum_api_*` function names and endpoint response contracts.
- Updated `tools/bitrix-architecture-check.mjs` and `tools/seo-check.mjs` to validate the new REST runtime boundary.
- Kept `local/rest/index.php` as the Bitrix REST hook admin route because `bitrix:rest.hook` is configured with `SEF_FOLDER=/local/rest/`; the route now explicitly defines `ADMIN_SECTION`, loads through `prolog_admin.php`, sends `X-Robots-Tag: noindex, nofollow` and `Cache-Control: private, no-store`.
- Updated `tools/bitrix-architecture-check.mjs` and `tools/seo-check.mjs` to guard the admin-only/non-indexable/private route markers; `BPC-REST-002` is closed locally.
- Added machine-readable `local/rest/endpoint_policy.json` with current endpoint method/action/risk-class/CSRF/noindex/legacy-alias policy plus future sensitive classes `PRIVATE_PROOF_DOC` and `INTERNAL_ADMIN_OR_INTEGRATION`.
- Updated `tools/rest-endpoint-guard-check.mjs` to load endpoint policy instead of hardcoded endpoint taxonomy and to verify action, CSRF fallback allowance, noindex and legacy deprecation/sunset/successor headers.
- Updated `Tacticum\Rest\RateLimiter` to read policy risk-class limits with safe defaults.
- Split rich lead/staff endpoints: `tacticum_form.php` is now 31 lines over `LeadPayload`/`LeadContext`, `tacticum_sale_staff.php` is 36 lines over `StaffOrderPayload`/`StaffOrderText`/`StaffOrderWorkers`.
- Removed `tacticum_form.php` and `tacticum_sale_staff.php` from BPC size-debt allowlist; `npm run bitrix:check` now passes with no tracked BPC debts after the later `lead.cta`, offer page, offer detail template, SEO helper, `calcrequests.*`, product page, offer catalog template, aiagents template, forms runtime, chat runtime, CSS split and product block partial splits.
- Local verification passed: PHP syntax lint, `npm run bitrix:check`, `npm run seo:check`, `npm run js:check`, `npm run rest:endpoints:check`, `npm run product:content:safety:check`.
- Focused payload-builder sanity passed with mocked REST helper functions for lead and staff payload assembly.
- Local `npm run config:runtime:check` is blocked by ignored `local/php_interface/include/tacticum_config.php` parse error (`Unclosed '{' on line 340`); tracked checker PHP syntax passes.
- Remaining scope for this work package: none in tracked code; post-deploy REST smoke remains release gate.

## BPC-WP-07 — Forms And Chat Frontend Modularity

Status: implemented-local / static-verification-passed-2026-06-05

Priority: P2

Workflow lane: Full Feature / Security

Owners: Frontend, Backend, QA, Analytics

Covered gaps: `BPC-FE-002`, `BPC-FE-003`

Objective: split `forms.js` and `chat-agent.js` after guardrails and `/price/` lessons are applied.

Affected areas:

- `local/templates/tacticum/js/forms.js`
- `local/templates/tacticum/js/forms-runtime.js`
- `local/templates/tacticum/js/chat-runtime.js`
- `local/templates/tacticum/js/chat-hero.js`
- `local/templates/tacticum/js/chat-calculators.js`
- `local/templates/tacticum/js/chat-agent.js`
- `local/components/tacticum/chat.surface/`
- `local/components/tacticum/lead.cta/`

Acceptance criteria:

- No payload, CSRF, consent, `group_id`, prefill or analytics PII contract change.
- Shared transport/state logic is isolated.
- Surface adapters are explicit.
- Manual success-flow helper remains valid.

Implementation note 05.06.2026:

- `forms.js` was reduced from 458 lines to a 289-line submit/payload orchestrator.
- Added `local/templates/tacticum/js/forms-runtime.js` for shared form runtime helpers: safe form id/context helpers, controlled product analytics metadata, no-PII returning-lead state, toast/error helpers and field error helpers.
- `header.php` loads `forms-runtime.js` before `forms.js`; `tools/seo-check.mjs` now validates returning-lead and product analytics contracts across the combined forms runtime.
- Removed `forms.js` from `tools/bitrix-architecture-check.mjs` size-debt allowlist. Remaining WP-07 code scope is `chat-agent.js`.
- Split `chat-agent.js` from 697 lines into an 18-line bootstrap plus `chat-runtime.js` 156, `chat-hero.js` 242 and `chat-calculators.js` 321. Chat transport, prefill, controlled errors, hero surface, dark calculator and light calculator handoff remain first-party assets under the same page asset `chat`.
- Removed `chat-agent.js` from `tools/bitrix-architecture-check.mjs` size-debt allowlist.
- Local verification: `npm run js:check`, `npm run seo:check`, `npm run bitrix:check`, `git diff --check`. Rendered chat smoke remains post-deploy gate because local split JS cannot safely replace the already-loaded production `chat-agent.js` in the current production-HTML smoke harness.

Verification:

```bash
npm run js:check
npm run seo:check
npm run bitrix:check
npm run browser:console:css-local
npm run manual:success-flow:helper
```

## BPC-WP-08 — Global CSS Budget And Component CSS Policy

Status: implemented-local / static-verification-passed-2026-06-05

Priority: P2

Workflow lane: Full Feature

Owners: Frontend, Designer, QA

Covered gaps: `BPC-FE-004`

Objective: reduce `global.css` risk by defining CSS ownership and budget before broad visual changes.

Affected areas:

- `local/templates/tacticum/styles/global.css`
- `local/templates/tacticum/styles/components.css`
- `local/templates/tacticum/styles/page-about-calculator.css`
- `local/templates/tacticum/styles/page-offer-price-services.css`
- `local/templates/tacticum/styles/page-aiagents.css`
- `local/templates/tacticum/assets/src/tailwind.css`
- component templates/styles
- `tools/template-styles-retirement-check.mjs`

Acceptance criteria:

- Global CSS has ownership sections and budget/allowlist.
- Component-specific CSS is allowed only where component ownership is clear.
- Tailwind source scan remains complete.
- No deleted legacy CSS assets are reintroduced.

Verification:

```bash
npm run css:build
npm run css:check
npm run css:syntax
npm run template-styles:check
npm run visual:smoke:css-local
```

Implementation note 05.06.2026:

- Split the former 1280-line `styles/global.css` into fixed ordered assets: `global.css` 279, `components.css` 294, `page-about-calculator.css` 289, `page-offer-price-services.css` 367 and `page-aiagents.css` 50.
- `header.php` loads the fixed CSS split after `tailwind.generated.css` and RemixIcon, preserving original cascade order.
- `tools/template-styles-retirement-check.mjs` now allows only the fixed CSS split and validates URLs/icons across every template CSS file.
- `package.json` local CSS smoke scripts now remove/inject the full split instead of only `global.css`.
- `tools/bitrix-architecture-check.mjs` no longer has any size-debt allowlist; `npm run bitrix:check` passes with no tracked debts.
- Local verification: `npm run css:check`, `npm run css:syntax`, `npm run template-styles:check`, `npm run design:tokens:check`, `npm run bitrix:check`, `npm run seo:check`, `npm run js:check`, `git diff --check`, `npm run visual:smoke:css-local`.
- CSS-local visual smoke passed across sitemap desktop/mobile with runtime errors `0`; manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-05T10-27-22-698Z/manifest.json`.
- Post-deploy smoke remains release gate.

## BPC-WP-09 — Component Fixture And State Smoke Coverage

Status: implemented-local / source-fixture-and-wrapper-contract-guard-passed

Priority: P2

Workflow lane: Full Feature

Owners: QA, Frontend, Designer

Covered gaps: `BPC-GUARD-003`, `BPC-CMP-005`

Objective: add deterministic state smoke coverage so future component splits do not rely only on visual inspection.

Affected areas:

- `tools/component-state-smoke-check.mjs`
- `tools/fixtures/component-state-smoke.json`
- PR/deploy lifecycle scripts
- existing `tools/visual-smoke.mjs`
- product page block checks
- `/price/`, forms, chat, FAQ/content wrappers

Acceptance criteria:

- Product component smoke covers required blocks and unavailable state.
- `/price/` smoke covers preset, multi-worker, modal and payload summary.
- Forms/chat smoke covers loading/error/success-safe states where non-destructive.
- FAQ/content wrapper smoke covers semantic section lookup and intentional empty state.
- Source-level fixture guard is required in PR/deploy lifecycle before broad component refactors.

Verification:

```bash
npm run component:states:check
npm run js:check
npm run bitrix:check
npm run visual:smoke:css-local
npm run browser:console:css-local
npm run browser:smoke:price
```

Implementation note 05.06.2026:

- Added `tools/fixtures/component-state-smoke.json` with 5 deterministic fixture groups: product page blocks/degraded state, price team builder, forms, chat and FAQ/content wrappers.
- Added `local/components/tacticum/component_wrapper_policy.json` with explicit contracts for `content.list`, `content.detail` and `faq.section`: owner, delegated child component, result keys, accepted params, required child params, cache params, template ownership and FAQ missing-state marker.
- Added `tools/component-state-smoke-check.mjs` and `npm run component:states:check`; the guard validates fixture schema, evidence files, required state literals, wrapper policy/source consistency and npm script wiring.
- `.github/workflows/pr-check.yml` and `.github/workflows/deploy.yml` run `component:states:check` in lifecycle checks after `bitrix:check`.
- Verified with `npm run component:states:check`, `npm run js:check` and `npm run bitrix:check`; rendered browser smoke remains post-deploy evidence for actual runtime behavior.
