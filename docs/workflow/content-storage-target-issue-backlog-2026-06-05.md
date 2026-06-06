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
| CST-WP-02 | proof audit passed / public rendering gated | ready | P1 | Backend + Bitrix Dev + QA | `CSG-002`, `CSG-003`, `CSG-009`, `CSG-011` | Config registry, `clients`, relation foundation and audit |
| CST-WP-03 | implemented / production evidence passed | ready after WP-02 | P1 | Backend + Content + QA + SEO | `CSG-001`, `CSG-010` | Product FAQ migration to `faq #10` and renderer switch |
| CST-WP-04 | production evidence passed | ready after WP-02 | P1 | Backend + Content + QA | `CSG-004`, `CSG-009` | Services source-of-truth and fallback removal |
| CST-WP-05 | product tags applied / public rendering gated | evidence-required | P1 | Content + Sales + SEO + Backend | `CSG-003`, `CSG-005` | Product related cases/feedback/clients proof mapping |
| CST-WP-06 | product relation applied / boundary guarded | owner-decision-required for SEO changes | P2 | PM + SEO + Content + Backend | `CSG-006` | `/agents/` vs `/aiagents/` content boundary and optional relation |
| CST-WP-07 | fallback-retirement deployed / rechecked | wave 2 shadow-seeded / runtime-ready locally | P1 | Architect + Backend + Content + Frontend + QA | `CSG-007`, `CSG-008`, `CSG-012` | Structured page-content model |
| CST-WP-08 | wave 1 source switch and smoke passed | wave 2 live/source approval pending | P1/P2 | Backend + Frontend + Content + QA | `CSG-007`, `CSG-008` | Static section migration wave 1 and wave 2 |
| CST-WP-09 | production evidence passed / FAQ fallback retired | ready after WP-03/WP-04 | P1 | Backend + QA + DevOps | `CSG-010`, `CSG-011`, `STACK-007` | Governance checks, release evidence and fallback retirement |

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

Implementation note 05.06.2026:
`clients` is in config/example, relation migration/check covers `feedback` and `clients`, and `content-storage-audit.php` reports safe aggregate evidence. Production migration created `PRODUCT` properties on `feedback #9` and `clients #8`; proof-scope strict audit passed with valid `PRODUCT` relations for `cases`, `feedback` and `clients`. The audit now also reports product-level aggregate proof counts for owner review. Public proof rendering remains gated by owner evidence.

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
- `product_blocks.faq` runtime fallback is removed after approved retirement; rollback is previous release redeploy or restoring the fallback code path.
- Product FAQPage schema remains valid and non-duplicated.

Verification:

```bash
php tools/content-storage-faq-migration.php
php tools/content-storage-faq-migration.php --apply
php tools/content-storage-audit.php --scope=faq --strict
npm run product:source:http:prod
npm run product:source:smoke:prod
npm run seo:check:prod
```

Implementation note 05.06.2026:
Product runtime reads related `faq` rows first and exposes `faq_source` in rendered/evidence data. Production FAQ seed/audit passed with 12 rows and 3 related FAQ items per product. After cache clear, strict product content check and HTTP source smoke passed with `faq_source=iblock` for all four product pages. Follow-up section challenge found that product FAQ rows should also belong to product FAQ sections for admin UX and section-governed FAQ behavior; production section sync created the section layer, linked 12 existing rows and strict FAQ audit passed with `faq_items_without_section=0` for all products. Chrome-capable rendered/action smoke passed for all product pages. `content-storage-faq-fallback-retirement-check.mjs` now guards the fallback-removal decision; `content-storage-faq-fallback-retirement-2026-06-06.approved.json` passes with `retirement_allowed=true`, production evidence `9/9` and owner gates `4/4`. Local runtime no longer uses `product_blocks.faq` as product FAQ fallback.

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

Implementation note 05.06.2026:
The hardcoded "Расчет проекта" fallback was removed from `news.list/services`. `content-storage-services-seed.php` applied six target delivery-package service cards on production; strict services audit passed with active/API count `6` and relation `link_ok=true`. `seo:check:prod`, Chrome-capable `visual:smoke:prod` and `browser:smoke:prod` passed.

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

Implementation note 05.06.2026:
Strict proof audit passed on production: `clients 5/5`, `feedback 3/3`, `cases 9/10`, and all checked `PRODUCT` relations are active/multiple and linked to `products #21`. Owner-approved proof tagging proposal/check/apply passed for 17 active proof items without storing raw proof copy or changing public rendering. Corrected production aggregate counts are `platform proof_items_total=6`, `agents proof_items_total=6`, `dev proof_items_total=6`, `forum proof_items_total=5`; `clients` remain global because logo/trust rows have no product-specific proof context. This validates relation/tagging readiness only; it does not approve any public customer proof copy because `public_render_approved=false`.

Implementation note 06.06.2026:
Public proof rendering runtime/tooling is implementation-ready but still owner-gated. `content-storage-proof-public-render-apply.php` creates/checks the durable `PUBLIC_RENDER_APPROVED` property on `cases/feedback/clients`, verifies current PRODUCT tags against approved `product_codes`, changes only that public-render flag and prints no proof names/copy/claims. Product runtime reads real public proof only through `PROPERTY_PRODUCT + PUBLIC_RENDER_APPROVED=Y`; product pages keep readiness proof unless a product has at least 3 public-approved proof items, then expose `proof_source=iblock`.

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

Implementation note 05.06.2026:
`content-storage-aiagents-boundary-check.mjs` now guards that `/agents/` uses `tacticum:product.page` with `PRODUCT_CODE=agents`, while `/aiagents/` uses `tacticum:aiagents`, `aiagents #20`, `Service` schema and Telegram demo/prototype positioning. The demo-agent list requests `PRODUCT` for tagging but does not render product claims. Production dry-run/apply tagged active demo-agent rows #523, #524 and #525 with `PRODUCT=agents` without printing names/copy or changing public rendering. Strict aiagents audit reports `agents aiagents_items=3` and zero items for other products; SEO/canonical strategy is unchanged.

Verification note 06.06.2026:
Production server browser automation remains blocked by missing Chrome/Chromium. Chrome-capable targeted visual/action smoke passed locally for changed URLs `/platform/`, `/agents/`, `/dev/`, `/forum/`, `/aiagents/`; broad all-page local runs showed isolated CDP/tooling timeouts without network, console or page errors.

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

Implementation note 05.06.2026:
`content-storage-page-content-model-2026-06-05.draft.json` now defines the draft `page_sections/page_blocks` target model, rejected target iblocks, required section/block fields, runtime fallback policy and two migration waves. `content-storage-page-content-model-check.mjs` validates the draft and prevents raw HTML/JSON blob or narrow-iblock storage from becoming the accepted model. `content-storage-page-content-migration.php` can dry-run the target schema and refuses `--apply` unless the model JSON is `status=approved` with architect/content/frontend/qa/seo gates true. Production dry-run 06.06.2026 confirmed `tacticum_page_sections`, `tacticum_page_blocks` and 25 planned properties, with registry hints still `0` and runtime unchanged. `content-storage-page-content-model-2026-06-06.approved.json` is schema-only approval: it allows empty schema creation and explicitly does not approve page copy seed, public runtime switch or fallback retirement. Production schema apply passed on 06.06.2026: `tacticum_page_sections #24`, `tacticum_page_blocks #25`, 25 properties created, config registry updated and strict `page-content` audit passed with `page_blocks.SECTION` linked to #24. `content-storage-page-content-seed.php` covers wave 1 shadow seed for `/services/`, `/price/`, `/contacts/` and `/offer/`; it also supports explicit wave 2 shadow seed/update for `/`, `/about/`, `/calculator/` and `/aiagents/`. `content-storage-audit.php --scope=page-content` reports aggregate row evidence and orphan blocks without raw copy. Production wave 1 shadow seed passed with 9 active sections, 37 active blocks and `orphan_blocks=0`. Production live-approval check and live-status apply promoted all 9 sections to `MIGRATION_STATUS=live`; pre-switch fallback HTTP source check passed with zero Bitrix markers. After the explicit environment switch to `page_content.source=bitrix`, runtime config check, page-content source HTTP check, strict page-content audit and `seo:check:prod` passed; `/services/`, `/price/`, `/contacts/` and `/offer/` now report Bitrix-rendered section counts `3/3`, `2/2`, `2/2` and `2/2` respectively. Production wave 2 shadow seed passed with 11 new sections, 43 new blocks, strict audit totals 20 active sections and 80 active blocks, and scoped fallback HTTP source check still reporting zero Bitrix markers for wave 2 pages. Local runtime guards are now ready for wave 2 deploy and preserve homepage smoke markers plus the calculator chat surface through `calculator-chat-outcome`; live/source approval remains pending. Live-only runtime foundation is active for wave 1; `content-storage-page-content-live-approval-*` / `content-storage-page-content-live-apply.php` gate live-status promotion only and validate wave 2 page/section keys and provide a scoped --wave=wave_2 draft path so wave 1 live sections are not mixed into future approval files. `content-storage-page-content-fallback-retirement-template.php` and `content-storage-page-content-fallback-retirement-check.mjs` gate fallback retirement separately and now support scoped wave 2 drafts/checks; targeted Chrome-capable post-switch browser smoke has passed for wave 1. Production owner-approved fallback-retirement check passed with 9 `retire_fallback` decisions, `retirement_allowed=true`, production evidence `9/9` and owner gates `5/5`; deployed code removed the approved fallback section bodies, post-deploy runtime/source/audit/SEO/browser checks passed and governance forbids static `<section>` fallback reintroduction for wave 1.

### CST-WP-08 — Static Section Migration

Workflow lane: Full Feature
Priority: P1/P2
Start: after shadow seed evidence; live switch requires page-level approval and smoke plan

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

Implementation note 06.06.2026:
`content-storage-page-content-seed.php` provided the first safe migration slice: production wave 1 rows exist in `page_sections/page_blocks`, preserve `FALLBACK_PARTIAL` and passed strict audit with 9 active sections, 37 active blocks and `orphan_blocks=0`. Owner-approved live-status apply promoted all 9 sections to `MIGRATION_STATUS=live`; the separate production config switch to `page_content.source=bitrix` passed runtime config, page-content source HTTP, strict audit and SEO checks. Fallback retirement has a separate owner-gated approval/checker contract; production owner-approved check passed with production evidence `9/9` and owner gates `5/5`, deployed code removed the approved fallback section bodies and post-deploy source/audit/SEO/browser checks passed. Targeted Chrome-capable post-switch visual/browser smoke has passed for wave 1. Wave 2 shadow seed passed on production; live approval, source checks and fallback retirement remain separate scoped gates.

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

Implementation note 05.06.2026:
`content:storage:governance:check`, `content-storage-audit.php`, stricter product target evidence and `content-storage-release-runbook-2026-06-05.md` are in place. Production target evidence passed for FAQ/services/proof relation foundation, FAQ section sync and Chrome-capable rendered/action smoke. FAQ fallback retirement is owner-approved in `content-storage-faq-fallback-retirement-2026-06-06.approved.json`; post-deploy cache clear, strict FAQ audit, strict product content check and HTTP source smoke passed with `faq_source=iblock`.

Verification note 06.06.2026:
Server-side `seo:check:prod` passed. Server-side `visual:smoke:prod` and `browser:smoke:prod` failed only because Chrome/Chromium is not installed on production. Chrome-capable targeted local visual/action smoke passed for changed URLs after broad-run CDP/tooling timeouts were isolated.

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
