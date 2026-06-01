# Codex Plan: Product-First Release And Rollback Hardening

Issue: internal product vision implementation continuation
Gap ID: `PV-013`, `PV-014`, `PV-016`, `PV-020`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Закрыть Sprint 07 хвост по release sign-off evidence template и rollback notes для product-first релиза: новые product pages, product navigation, page-specific CTA contexts, FAQ assets и сохранение legacy money flows.

## Non-Goals

- Не менять production deploy workflow.
- Не менять REST/API, form contracts или upstream behavior.
- Не запускать production smoke без deploy/cache refresh.
- Не принимать окончательное решение по `/aiagents/` vs `/agents/` canonical/redirect.

## Target Behavior

- Есть отдельный draft release sign-off для product-first release scope.
- Есть rollback runbook, который описывает быстрый откат новых страниц/навигации/контекстов без разрушения `/offer/`, `/price/`, `/calculator/`, `/aiagents/`.
- Workflow docs отражают, что product URLs должны быть в Bitrix-generated static sitemap после генерации.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/release-signoff-2026-06-01-product-first.draft.json` | Add product-first release draft with pending gates |
| `docs/workflow/product-first-release-rollback-runbook.md` | Add rollback/runbook for product pages, nav, CTA contexts and FAQ assets |
| `docs/workflow/release-signoff-gates.md` | Reference product-first draft command |
| `docs/workflow/post-deploy-smoke.md` | Add product-first URLs/forms/assets checks |
| `docs/workflow/current-state.md` | Update static sitemap expected URL list and release hardening note |
| `docs/workflow/gap-analysis.md` | Document S07-009/S07-010 progress |
| `docs/new-big-change/product-vision-handoff/sprints/sprint-07-proof-forms-seo-analytics-hardening.md` | Mark release/rollback hardening progress |

## Verification

### Actual Checks

- [x] `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json`
- [x] `npm run release:signoff:summary -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json`
- [x] `npm run seo:check`
- [x] `npm run gaps:known`
- [x] `TACTICUM_RELEASE_SIGNOFF=docs/workflow/release-signoff-2026-06-01-product-first.draft.json npm run gaps:known`
- [x] `git diff --check`

## Rollback

Удалить новый draft sign-off и rollback runbook, вернуть docs-only additions в `post-deploy-smoke.md`, `current-state.md`, `gap-analysis.md` and Sprint 07 doc.
