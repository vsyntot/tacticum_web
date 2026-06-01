# Codex Plan: Product Structured Data Schema

Issue: internal product vision implementation continuation
Gap ID: `PV-013`, `PV-004`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Добавить минимальную page-specific JSON-LD schema для `/platform/`, `/agents/`, `/dev/`, `/forum/`, чтобы product pages были представлены в rendered head не только как generic webpage, но и как software/product pages.

## Non-Goals

- Не добавлять `Offer`, pricing, rating, reviews, aggregateRating, customer logos or benchmark claims.
- Не менять sitemap/canonical/robots strategy.
- Не менять visible page copy, forms, REST/upstream or analytics events.
- Не добавлять новые JS/CSS assets.

## Planned Changes

| File | Change |
|---|---|
| `/platform/`, `/agents/`, `/dev/`, `/forum/` | Pass safe `SoftwareApplication` schema to `tacticum_apply_seo_defaults(...)` |
| `tools/seo-check.mjs` | Guard product schema presence and no risky commercial schema fields |
| `docs/workflow/current-state.md` | Document product structured data |
| `docs/workflow/gap-analysis.md` | Document `PV-013` progress |
| `docs/workflow/seo-gap-analysis.md` | Update structured data notes for product pages |
| Product sprint docs | Update Sprint 05/06/07 review/status |

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
- [ ] `php -v` / PHP lint: локально недоступен PHP CLI (`php: command not found`)

## Rollback

Remove product page `schema` options and related static guard/docs updates.
