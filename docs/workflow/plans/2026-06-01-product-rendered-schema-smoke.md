# Codex Plan: Product Rendered Schema Smoke

Issue: internal product vision implementation continuation
Gap ID: `PV-013`, `PV-014`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Усилить post-deploy rendered SEO gate: product pages должны проходить не только generic JSON-LD validation, но и проверку deployed `SoftwareApplication` + `FAQPage` schema для `/platform/`, `/agents/`, `/dev/`, `/forum/`.

## Non-Goals

- Не менять PHP schema generation после `tacticum_product_page_schema(...)`.
- Не добавлять commercial schema: `Offer`, prices, reviews, ratings, customer proof fields.
- Не менять visible page copy, sitemap/canonical/robots, forms, REST/upstream or analytics.
- Не запускать production smoke из локальной среды до deploy/cache refresh.

## Planned Changes

| File | Change |
|---|---|
| `tools/visual-smoke.mjs` | Parse rendered JSON-LD graph, expose `seoHead.productSchemaSummary`, fail product URLs without matching `SoftwareApplication` and `FAQPage` under `TACTICUM_EXPECT_SEO_HEAD=1` |
| `tools/release-signoff-check.mjs` | Reject `seo-rendered-head` evidence for product URLs if manifest has no product schema summary |
| `tools/release-signoff-self-test.mjs` | Add a negative case proving product `seo-rendered-head` evidence without product schema summary is rejected |
| `docs/workflow/current-state.md` | Document rendered product schema gate |
| `docs/workflow/gap-analysis.md` | Record product structured-data rendered smoke hardening |
| `docs/workflow/seo-gap-analysis.md` | Document product schema rendered validation |
| `docs/workflow/post-deploy-smoke.md` | Add product schema expectations to deploy checklist |
| `docs/workflow/release-signoff-gates.md` | Clarify `seo-rendered-head` evidence for product URLs |
| `docs/workflow/release-signoff-2026-06-01-product-first.draft.json` | Update pending SEO reason |
| `docs/new-big-change/product-vision-handoff/04-product-page-briefs.md` | Add handoff note about rendered schema smoke |

## Acceptance Criteria

- `visual-smoke` keeps existing generic SEO checks for title/description/canonical/OpenGraph/Twitter/JSON-LD/H1.
- In rendered SEO mode, product URLs fail if they miss a product-local `SoftwareApplication` schema with `#software`.
- In rendered SEO mode, product URLs fail if they miss a product-local `FAQPage` schema with `#faq` and valid question/answer entities.
- Product `SoftwareApplication` schema is checked for `name`, `description`, `operatingSystem=Web`, `provider`, `isPartOf` and absence of risky commercial fields.
- Manifest includes product schema summary for QA/debug without storing raw payloads.
- Release sign-off self-test rejects a synthetic `/platform/` SEO manifest that has generic SEO head data but no `productSchemaSummary`.

## Verification

### Actual Checks

- [x] `node --check tools/visual-smoke.mjs`
- [x] `node --check tools/release-signoff-check.mjs`
- [x] `npm run js:check`
- [x] `node --check tools/seo-check.mjs`
- [x] `npm run seo:check`
- [x] `npm run bitrix:check`
- [x] `npm run template-styles:check`
- [x] `npm run css:check`
- [x] `npm run css:syntax`
- [x] `npm run config:check`
- [x] `npm run sale:sunset:check`
- [x] `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json`
- [x] `npm run release:signoff:self-test` (`10` negative cases, including missing product schema summary)
- [x] `npm run gaps:known`
- [x] `TACTICUM_RELEASE_SIGNOFF=docs/workflow/release-signoff-2026-06-01-product-first.draft.json npm run gaps:known`
- [x] `git diff --check`
- [ ] `php -v` / PHP lint: локально недоступен PHP CLI (`php: command not found`); GitHub PHP 8.4 lint remains fallback

## Rollback

Remove product-specific schema assertions and `productSchemaSummary` from `tools/visual-smoke.mjs`, then revert docs updates. Static product schema generation and `seo:check` guards can remain.
