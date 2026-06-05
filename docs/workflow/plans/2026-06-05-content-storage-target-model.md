# Codex Plan: Content Storage Target Model

Issue: TBD
Gap ID: `CSG-001` - `CSG-012`
Workflow lane: Full Feature
Owner agent: Codex
Date: 05.06.2026

## Goal

Довести сайт до целевого хранения контента в Bitrix: доменные сущности редактируются в своих инфоблоках, продуктовый layout остается в product model, generic page sections получают структурную модель, а runtime/checks не позволяют снова спрятать FAQ/services/proof/page content в неподходящих местах.

## Non-Goals

- Не менять визуальный дизайн.
- Не менять REST/upstream lead contracts.
- Не переносить все статические секции за один релиз.
- Не публиковать неподтвержденные customer claims.
- Не редактировать `bitrix/`.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/adr/ADR-003-iblock-ids.md`
- [x] `docs/adr/ADR-010-product-content-bitrix-model.md`
- [x] `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`
- [x] `docs/workflow/content-storage-target-roadmap-2026-06-05.md`
- [x] `docs/workflow/content-storage-target-issue-backlog-2026-06-05.md`

## Current Behavior

1. Product pages read Bitrix product content from `products #21`, `product_blocks #22`, `product_use_cases #23`.
2. Product FAQ is rendered from `product_blocks`, not from `faq #10`.
3. Public page FAQ sections use `tacticum:faq.section` and `faq #10`.
4. `clients #8` exists in admin screenshots but is not present in current config registry.
5. `news.list/services` can hardcode fallback "Расчет проекта" if the service element is missing.
6. `cases`, `feedback`, `team`, `vacancies`, `rates`, `aiagents` are partly used through `tacticum:content.list`.
7. Large page-level sections remain PHP partial content.

## Target Behavior

1. FAQ questions, including product FAQ, are stored and editable in `faq`.
2. Real cases, feedback and clients can be related to products and rendered as approved evidence.
3. Product proof readiness remains clearly separate from real customer cases.
4. `services` renders only service elements from the iblock.
5. Generic page sections use a structured page-content model, not narrow catalog iblocks and not raw HTML/JSON as primary editing.
6. Governance checks catch wrong storage before deploy.

## Planned Changes

| Phase | Files / Areas | Change |
|---|---|---|
| Phase 0 | `docs/adr/*`, workflow docs | Approve content ownership matrix and page-content storage decision. |
| Phase 1 | config/example, migration/audit tools | Add/confirm `clients`, extend product relation coverage, add content storage audit. |
| Phase 2 | FAQ migration, product runtime | Seed product FAQ into `faq`, read FAQ from `faq` first, keep fallback. |
| Phase 3 | `news.list/services`, services seed | Remove hardcoded service fallback after iblock content exists. |
| Phase 4 | cases/feedback/clients runtime | Add related evidence model and product rendering with empty state. |
| Phase 5 | `/agents/`, `/aiagents/` content model | Clarify demo-agent vs product-agent boundary and optional relation. |
| Phase 6 | new page-content model | Add structured page sections/page blocks schema and renderer. |
| Phase 7 | page partials | Migrate static sections in waves. |
| Phase 8 | tools/release docs | Add strict governance checks and retire fallbacks after evidence. |

## Bitrix Constraints

- [x] `bitrix/` не трогаем.
- [x] New iblock access must use config helpers, not hardcoded IDs.
- [x] New shared code uses `Loader::includeModule('iblock')`.
- [x] Migrations are idempotent and dry-run by default.
- [x] Public source switches include cache clear and rendered smoke.
- [x] POST REST bootstrap is out of scope unless forms/runtime change.

## Risks

| Risk | Mitigation |
|---|---|
| Wrong iblock pollution | Ownership matrix and do-not-move rules before migration. |
| Duplicate FAQ/schema | Product renderer reads one FAQ source and schema uses rendered source. |
| Data loss | Migrations create/update stable codes, no destructive delete in first phase. |
| Cache stale after admin edit | Add cache clear/runbook and managed tag coverage. |
| Fake proof claims | Related cases/feedback/clients require owner evidence and empty state. |
| Editor-unfriendly page content | Structured fields/cards, no raw JSON/HTML as primary workflow. |
| Production regression | Fallback-first rollout, browser/SEO/product smoke, rollback steps. |

## Verification

### Automated

```bash
php -l local/php_interface/include/tacticum_config.example.php
php tools/content-storage-audit.php --strict
php tools/product-content-check.php --strict
npm run seo:check
npm run release:public-precheck:prod
```

### Manual / Target Evidence

- Bitrix admin review for `faq`, `services`, `cases`, `feedback`, `clients`.
- Product page rendered smoke for `/platform/`, `/agents/`, `/dev/`, `/forum/`.
- Public page smoke for pages whose static sections migrate.
- No raw PII, customer-private claims or unapproved evidence in saved artifacts.

## Rollback

1. Keep old runtime fallback until target source passes production evidence.
2. If FAQ source fails, switch product renderer back to `product_blocks.faq` fallback and clear product/template cache.
3. If services seed/fallback removal fails, restore template fallback only as emergency hotfix and reopen `CSG-004`.
4. If page-content model fails, render old PHP partials and keep new iblock rows inactive.
5. Re-run public precheck, SEO check and browser smoke after rollback.

## Docs To Update During Implementation

- `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`
- `docs/workflow/content-storage-target-roadmap-2026-06-05.md`
- `docs/workflow/content-storage-target-issue-backlog-2026-06-05.md`
- `docs/workflow/current-state.md`
- `docs/workflow/gap-analysis.md`
- `docs/adr/ADR-010-product-content-bitrix-model.md` or new ADR for page-content model
- release runbook/sign-off docs if public runtime source changes

