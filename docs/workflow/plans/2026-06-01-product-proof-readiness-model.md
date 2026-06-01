# Codex Plan: Product Proof Readiness Model

Issue: internal product vision implementation continuation
Gap ID: `PV-007`, `PV-014`, `PV-016`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Добавить на product pages claim-safe proof readiness model: не публичные метрики/кейсы/логотипы, а перечень того, что проверяется на пилоте и какие артефакты могут стать evidence после подтверждения.

## Non-Goals

- Не публиковать customer logos, testimonials, numeric metrics, benchmark claims or regulatory proof.
- Не добавлять новые analytics events.
- Не менять forms, REST/upstream, sitemap or URL strategy.
- Не подключать новые JS/CSS assets.

## Planned Changes

| File | Change |
|---|---|
| `local/php_interface/include/product_page.php` | Add reusable proof readiness renderer |
| `/platform/`, `/agents/`, `/dev/`, `/forum/` | Add product-specific proof readiness items |
| `tools/seo-check.mjs` | Guard product proof readiness blocks |
| `docs/workflow/proof-claims-matrix.md` | Document allowed product proof readiness wording |
| `docs/workflow/current-state.md` | Document implementation state |
| `docs/workflow/gap-analysis.md` | Document `PV-007` progress |
| Product sprint docs | Update Sprint 05/06/07 status/review |

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

Удалить `proof` data blocks from product pages, remove proof readiness renderer/guard/docs additions.
