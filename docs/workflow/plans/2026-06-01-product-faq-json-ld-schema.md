# Codex Plan: Product FAQ JSON-LD Schema

Issue: internal product vision implementation continuation
Gap ID: `PV-013`, `PV-014`, `PV-020`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Синхронизировать видимый static FAQ на `/platform/`, `/agents/`, `/dev/`, `/forum/` со structured data, чтобы один product page data array питал HTML, `SoftwareApplication` and `FAQPage` JSON-LD.

## Non-Goals

- Не добавлять `Offer`, pricing, rating, reviews, aggregateRating, customer logos or benchmark claims.
- Не менять visible FAQ copy, URL, sitemap, canonical or robots strategy.
- Не менять forms, REST/upstream contracts, analytics events, JS/CSS assets.
- Не подключать FAQ из инфоблока для product pages: product FAQ остаётся static, page-specific and claim-safe.

## Planned Changes

| File | Change |
|---|---|
| `local/php_interface/include/product_page.php` | Add product schema helpers for `SoftwareApplication` and rendered static `FAQPage` |
| `/platform/`, `/agents/`, `/dev/`, `/forum/` | Define `$tacticumProductPage` once, then use it for schema and HTML render |
| `tools/seo-check.mjs` | Guard data -> schema -> render ordering, product FAQ schema presence and risky schema field ban |
| `docs/workflow/current-state.md` | Document product FAQ JSON-LD behavior |
| `docs/workflow/gap-analysis.md` | Document product structured-data gap closure progress |
| `docs/workflow/seo-gap-analysis.md` | Update structured-data notes for product pages |
| `docs/new-big-change/product-vision-handoff/04-product-page-briefs.md` | Add implementation note for designers/product handoff |

## Acceptance Criteria

- Product pages render static FAQ as before and still request `faq.js` through `tacticum_page_assets=faq`.
- Product pages pass `schema` to `tacticum_apply_seo_defaults(...)` through a shared helper, not duplicated inline schema arrays.
- `FAQPage` schema is generated only from valid visible product FAQ items with non-empty question and answer.
- Product schema remains claim-safe: no offers, prices, reviews, ratings or public proof claims.
- Static guards fail if product page data is no longer defined before SEO schema or if render stops using the same data array.

## Verification

### Actual Checks

- [x] `node --check tools/seo-check.mjs`
- [x] `node --check tools/visual-smoke.mjs`
- [x] `npm run seo:check`
- [x] `npm run bitrix:check`
- [x] `npm run template-styles:check`
- [x] `npm run js:check`
- [x] `npm run css:check`
- [x] `npm run css:syntax`
- [x] `npm run config:check`
- [x] `npm run sale:sunset:check`
- [x] `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json`
- [x] `npm run release:signoff:self-test`
- [x] `npm run gaps:known`
- [x] `TACTICUM_RELEASE_SIGNOFF=docs/workflow/release-signoff-2026-06-01-product-first.draft.json npm run gaps:known`
- [x] `git diff --check`
- [ ] `php -v` / PHP lint: локально недоступен PHP CLI (`php: command not found`); GitHub PHP 8.4 lint remains fallback

## Rollback

Remove `tacticum_product_page_schema(...)` usage from product pages, remove the new product schema helpers and revert docs/guard updates. Static FAQ HTML can remain because this change only syncs schema generation with existing page data.
