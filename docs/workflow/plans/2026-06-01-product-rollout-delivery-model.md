# Codex Plan: Product Rollout Delivery Model

Issue: internal product vision implementation continuation
Gap ID: `PV-020`, `PV-004`, `PV-012`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Добавить на продуктовые страницы `/platform/`, `/agents/`, `/dev/`, `/forum/` единый безопасный блок "как внедряется": discovery / pilot / integration / rollout-support. Это закрывает недостающую часть product page brief про delivery model без изменения REST, JS, инфоблоков or upstream.

## Non-Goals

- Не публиковать pricing/licensing, SLA tiers, registry, FSTEC/FSB, Astra/RED OS, ПАК or guarantee claims.
- Не менять формы, endpoint contracts, analytics events or CTA payload.
- Не добавлять новые CSS/JS assets.
- Не менять `/services/`, `/price/`, `/calculator/`, `/offer/` flows.

## Planned Changes

| File | Change |
|---|---|
| `local/php_interface/include/product_page.php` | Add reusable product rollout renderer |
| `/platform/`, `/agents/`, `/dev/`, `/forum/` | Add page-specific safe rollout steps |
| `tools/seo-check.mjs` | Guard that product pages use rollout model |
| `docs/workflow/current-state.md` | Document product rollout delivery model |
| `docs/workflow/gap-analysis.md` | Document progress for `PV-020` |
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

Удалить `rollout` data blocks from product pages, remove rollout renderer/guard/docs additions.
