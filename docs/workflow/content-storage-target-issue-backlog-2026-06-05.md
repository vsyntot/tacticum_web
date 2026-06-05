# Content Storage Target Issue Backlog — 2026-06-05

Дата: 05.06.2026
Статус: implementation backlog draft

Source documents:

- `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`
- `docs/workflow/content-storage-target-roadmap-2026-06-05.md`
- `docs/workflow/plans/2026-06-05-content-storage-target-model.md`

## Purpose

Этот backlog переводит content-storage challenge в набор рабочих пакетов. Его можно использовать для создания задач перед реализацией. Backlog не является approval evidence and не закрывает gaps.

## Start Policy

| Policy | Meaning |
|---|---|
| `ready` | Можно начинать implementation после чтения source docs. |
| `owner-decision-required` | Нужен owner/architecture decision до кода. |
| `evidence-required` | Нужна Sales/Content/SEO/QA evidence до публичного рендера. |
| `deferred` | Реализация только после предыдущих пакетов. |

## Backlog Index

| Issue | Status | Start Policy | Priority | Owners | Gaps | Summary |
|---|---|---|---|---|---|---|
| CST-WP-01 | planned | owner-decision-required | P1 | Architect + Content + PM | `CSG-001` - `CSG-012` | Content ownership matrix and target model decision |
| CST-WP-02 | planned | ready | P1 | Backend + Bitrix Dev + QA | `CSG-002`, `CSG-003`, `CSG-009`, `CSG-011` | Config registry, `clients`, relation foundation and audit |
| CST-WP-03 | planned | ready after WP-02 | P1 | Backend + Content + QA + SEO | `CSG-001`, `CSG-010` | Product FAQ migration to `faq #10` and renderer switch |
| CST-WP-04 | planned | ready after WP-02 | P1 | Backend + Content + QA | `CSG-004`, `CSG-009` | Services source-of-truth and fallback removal |
| CST-WP-05 | planned | evidence-required | P1 | Content + Sales + SEO + Backend | `CSG-003`, `CSG-005` | Product related cases/feedback/clients proof mapping |
| CST-WP-06 | planned | owner-decision-required | P2 | PM + SEO + Content + Backend | `CSG-006` | `/agents/` vs `/aiagents/` content boundary and optional relation |
| CST-WP-07 | planned | owner-decision-required | P1 | Architect + Backend + Content + Frontend + QA | `CSG-007`, `CSG-008`, `CSG-012` | Structured page-content model |
| CST-WP-08 | deferred | deferred | P1/P2 | Backend + Frontend + Content + QA | `CSG-007`, `CSG-008` | Static section migration wave 1 and wave 2 |
| CST-WP-09 | planned | ready after WP-03/WP-04 | P1 | Backend + QA + DevOps | `CSG-010`, `CSG-011`, `STACK-007` | Governance checks, release evidence and fallback retirement |

## Issue Details

### CST-WP-01 — Content Ownership Matrix

Workflow lane: Full Feature
Priority: P1

Objective:
Define exact source of truth for every content class and screenshot iblock.

Affected areas:

- ADR-010 or new ADR;
- workflow docs;
- Bitrix admin/content ownership.

Acceptance criteria:

- Matrix covers `faq`, `services`, `cases`, `feedback`, `clients`, `aiagents`, `rates`, `team`, `vacancies`, `policies/static materials`, `offer`, product iblocks.
- Each row has target source, migration decision, fallback decision and do-not-move rule.
- `page_sections/page_blocks` vs `static materials` decision is explicit.

Do not start:

- Runtime source changes;
- data migrations;
- fallback removal.

### CST-WP-02 — Registry, Relations And Audit

Workflow lane: Full Feature
Priority: P1

Objective:
Make configured iblocks and relation properties match target content model.

Affected areas:

- `local/php_interface/include/tacticum_config.example.php`;
- environment `tacticum_config.php`;
- `tools/product-content-migration.php` or new content-storage migration tool;
- new audit tool.

Acceptance criteria:

- `clients` is configured or explicitly retired.
- `PRODUCT` relation exists for target existing iblocks, including `feedback` and `clients` when configured.
- Audit reports admin/public count mismatch and relation coverage.
- Dry-run evidence is safe to paste into release docs.

Verification:

```bash
php tools/content-storage-audit.php --json
php tools/product-content-migration.php
```

### CST-WP-03 — Product FAQ To FAQ Iblock

Workflow lane: Full Feature
Priority: P1

Objective:
Move product FAQ from product layout storage to FAQ domain storage.

Affected areas:

- FAQ migration tool;
- product content runtime/repository;
- product renderer/schema;
- FAQ/content storage checks.

Acceptance criteria:

- 12 product FAQ items exist in `faq #10` with product relation.
- Product pages read FAQ from `faq` first.
- Fallback to `product_blocks.faq` remains for one migration cycle.
- Product FAQPage schema remains valid and non-duplicated.

Verification:

```bash
php tools/content-storage-audit.php --scope=faq --strict
npm run product:source:http:prod
npm run product:source:smoke:prod
npm run seo:check:prod
```

### CST-WP-04 — Services Source Of Truth

Workflow lane: Fast Fix / Full Feature depending on migration scope
Priority: P1

Objective:
Remove hardcoded service cards from templates.

Affected areas:

- `local/templates/tacticum/components/bitrix/news.list/services/template.php`;
- `services` iblock seed/migration;
- audit guard.

Acceptance criteria:

- `Расчет проекта` is an active service element if it is rendered as a service.
- Services template does not synthesize missing business cards.
- Public `/services/` remains visually and semantically stable.

Do not do:

- Move process/tech/delivery copy into `services`.

### CST-WP-05 — Product Proof Mapping

Workflow lane: Full Feature
Priority: P1

Objective:
Connect products with real cases, feedback and clients without fake claims.

Affected areas:

- `cases`, `feedback`, `clients` iblocks;
- product page renderer;
- proof/claims docs and checks.

Acceptance criteria:

- Related cases/feedback/clients can be tagged by product.
- Product pages render only approved related evidence.
- Product readiness artifacts stay in product content and are labeled accordingly.
- Empty state renders nothing.

Do not start:

- Public rendering before Content/Sales/SEO evidence.

### CST-WP-06 — AI Agents Boundary

Workflow lane: Full Feature
Priority: P2

Objective:
Prevent `/agents/` product content and `/aiagents/` demo-agent catalog from collapsing into one confused model.

Acceptance criteria:

- `/aiagents/` remains demo/prototype entry.
- `/agents/` remains product page.
- Optional relations are explicit and do not duplicate product content.
- SEO/canonical/copy guard remains green.

### CST-WP-07 — Structured Page Content Model

Workflow lane: Full Feature
Priority: P1

Objective:
Create a target model for static page sections.

Affected areas:

- new or repurposed iblocks;
- local page section component/repository;
- migration tool;
- component/cache policy;
- architecture guard.

Acceptance criteria:

- Model stores page sections and child cards/items without JSON/HTML blobs as primary editor workflow.
- Page renderer supports fallback to PHP partials.
- Cache invalidation is defined.
- First migration wave can start with clear schema.

Do not do:

- Use `services`, `cases`, `team`, `vacancies`, `rates` as generic page section storage.

### CST-WP-08 — Static Section Migration

Workflow lane: Full Feature
Priority: P1/P2
Start: deferred until CST-WP-07

Wave 1:

- `/services/` delivery/process/tech;
- `/price/` features/workstreams;
- `/contacts/` routing/contact cards;
- `/offer/` product bridge/bottom CTA.

Wave 2:

- homepage ecosystem/fit/commercial sections;
- `/about/` values/history/vendor trust/career culture;
- `/calculator/` outcome/product-aware cards;
- `/aiagents/` services/how-it-works/bridge.

Acceptance criteria:

- Each page has before/after rendered smoke.
- Migrated PHP partials become fallback-only or are removed.
- Editors can update migrated cards/items through admin fields.

### CST-WP-09 — Governance Checks And Fallback Retirement

Workflow lane: Full Feature / DevOps
Priority: P1

Objective:
Make target storage enforceable.

Affected areas:

- new content storage check;
- package scripts;
- release runbook/sign-off;
- product content/source checks.

Acceptance criteria:

- Check fails when product FAQ is missing from `faq`.
- Check fails when services template fallback returns.
- Check reports missing `clients` config or retired status.
- Check reports page static sections by migration phase.
- Fallbacks are retired only after production evidence and owner admin-editability review.

## Verification Bundle

Minimum expected command bundle after implementation begins:

```bash
php tools/content-storage-audit.php --strict
php tools/product-content-check.php --strict
npm run product:source:http:prod
npm run release:public-precheck:prod
npm run seo:check:prod
```

Browser smoke remains required where runtime rendering changes.

