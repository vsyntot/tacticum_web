# Codex Plan: Product CTA Scenario Qualification

Issue: internal product vision implementation continuation
Gap ID: `PV-012`, `PV-020`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Уточнить product-aware qualification на `/platform/`, `/agents/`, `/dev/`, `/forum/`: пользователь должен не только попадать в форму с hidden `lead_product`, но и выбирать безопасный сценарий следующего шага, который уйдет в уже allowlisted `lead_scenario`.

## Non-Goals

- Не менять REST endpoint, upstream response shape or analytics event params.
- Не добавлять новые обязательные поля формы.
- Не добавлять JS/CSS assets.
- Не менять `/price/`, `/calculator/`, `/offer/` contracts.

## Planned Changes

| File | Change |
|---|---|
| `local/components/tacticum/lead.cta/` | Add optional scenario select rendered from component params |
| `local/php_interface/include/product_page.php` | Pass product scenario options from product page config to `tacticum:lead.cta` |
| `local/rest/tacticum_form.php` | Map known `lead_scenario` slugs to readable labels in upstream task context |
| `/platform/`, `/agents/`, `/dev/`, `/forum/` | Add page-specific safe scenario options |
| `tools/seo-check.mjs` | Guard product pages and CTA component support scenario qualification |
| `docs/workflow/*`, product sprint docs | Document progress and verification |

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

Удалить `SCENARIO_OPTIONS` support from `tacticum:lead.cta`, убрать `scenario_options` из product pages and renderer, вернуть docs/guard updates.
