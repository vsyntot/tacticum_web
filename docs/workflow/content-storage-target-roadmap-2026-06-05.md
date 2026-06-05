# Content Storage Target Roadmap — 2026-06-05

Дата: 05.06.2026
Статус: implementation roadmap draft
Source gap analysis: `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`

## Goal

Довести хранение контента сайта до целевой Bitrix-модели:

- редактор находит сущность в ожидаемом инфоблоке;
- runtime читает доменные сущности из доменных инфоблоков;
- page-level marketing sections не загрязняют narrow catalog iblocks;
- legacy PHP/product-block fallbacks существуют только на время миграции;
- проверки ловят неправильное хранение до production.

## Non-Goals

- Не менять визуальный дизайн в этом scope.
- Не менять REST/upstream lead contracts.
- Не создавать fake cases/testimonials/clients without owner evidence.
- Не переносить все PHP partials одним big bang.
- Не редактировать `bitrix/`.

## Phase 0 — Ownership Matrix And Target Decision

Status: ready to start
Priority: P1
Related gaps: `CSG-001` - `CSG-012`

### Actions

1. Утвердить content ownership matrix:
   - `faq` owns FAQ;
   - `cases` owns real customer cases;
   - `feedback` owns testimonials;
   - `clients` owns client/trust logos/names;
   - `services` owns service catalog cards;
   - `rates` owns staff/rate records;
   - `team` owns people;
   - `vacancies` owns open positions;
   - product iblocks own product layout and product-local facts;
   - page-content model owns generic static sections.
2. Decide `policies/static materials #19`:
   - keep as legal-only;
   - or explicitly replace/extend with structured `page_sections/page_blocks`.
3. Update ADR-010 or add new ADR if target model changes product content source semantics.

### Challenge

Need: yes. This is the decision gate.

Do not approve vague wording like "content lives in Bitrix". The target must state the exact iblock or model per content class.

### Acceptance Criteria

- Ownership matrix has no "unknown" rows for screenshot iblocks.
- Each planned migration has a target iblock/model and fallback decision.
- Do-not-move policy is explicit for `rates`, `team`, `vacancies`, `cases`, `services`.

## Phase 1 — Registry And Relation Foundation

Status: ready after Phase 0
Priority: P1
Related gaps: `CSG-002`, `CSG-003`, `CSG-009`, `CSG-011`

### Actions

1. Add `clients` key to config/example if public clients/trust remains in scope.
2. Extend relation migration to configured existing content iblocks:
   - current: `faq`, `cases`, `offer`, `services`, `aiagents`;
   - add: `feedback`, `clients`.
3. Add dry-run audit command that reports:
   - configured iblock IDs;
   - missing config keys for screenshot-known iblocks;
   - presence/absence of `PRODUCT` relation;
   - active element counts by iblock;
   - public API counts where endpoint exists.

### Challenge

Need: yes for `clients`; yes for relation scope.

Do not add relation properties to iblocks that are not configured or not actually used. The migration should skip safely and report.

### Acceptance Criteria

- No new hardcoded iblock IDs.
- `clients` is either configured or explicitly documented as not used.
- `feedback` and `clients` relation properties are created only when their iblocks are configured.
- Audit output can be saved as safe owner evidence.

### Verification

```bash
php -l local/php_interface/include/tacticum_config.example.php
php tools/product-content-migration.php
php tools/content-storage-audit.php --json
```

## Phase 2 — FAQ Target Migration

Status: ready after Phase 1
Priority: P1
Related gaps: `CSG-001`, `CSG-010`, `CFG-005`, `CMP-007`

### Actions

1. Seed product FAQ into `faq #10`:
   - product sections or section codes: `platform`, `agents`, `dev`, `forum`;
   - stable `CODE`/`XML_ID`;
   - `PRODUCT` relation;
   - question in `NAME`;
   - answer in `DETAIL_TEXT`.
2. Update product runtime:
   - read FAQ from `faq` first by product relation/section code;
   - fallback to `product_blocks.faq` only while migration is incomplete.
3. Add guard:
   - rendered product FAQ question must exist in FAQ iblock evidence;
   - FAQPage schema count/questions must remain stable.

### Challenge

Need: yes. Product FAQ is a domain FAQ, not a layout block.

Do not delete `product_blocks.faq` during first release. It is rollback/fallback until production evidence passes.

### Acceptance Criteria

- Product pages still render 3 FAQ items each.
- `/local/api/faq.php` includes 12 product FAQ questions or a target audit proves they exist in FAQ iblock.
- Product renderer source marker/evidence can distinguish `faq_source=iblock|fallback`.
- No duplicate FAQ schema on product pages.

### Verification

```bash
php tools/content-storage-faq-migration.php --dry-run
php tools/content-storage-faq-migration.php --apply
php tools/content-storage-audit.php --scope=faq --strict
npm run product:source:http:prod
npm run product:source:smoke:prod
```

## Phase 3 — Services Source Of Truth

Status: ready after Phase 1
Priority: P1
Related gaps: `CSG-004`, `CSG-009`, `CSG-010`

### Actions

1. Ensure `Расчет проекта` exists as an active `services` element if it is a service card.
2. Remove hardcoded fallback card from `news.list/services`.
3. Audit admin/public count mismatch:
   - admin screenshot shows 4 service elements;
   - public API showed 2 active/public items.
4. Decide whether hidden/inactive services are intentional.

### Challenge

Need: yes for fallback removal.

Do not move service methodology/process/tech blocks into `services`. They are page sections.

### Acceptance Criteria

- `/services/` service card count comes only from iblock rows.
- Services template has no hardcoded "Расчет проекта" fallback.
- API/admin mismatch is explained by active/filter status or fixed.

### Verification

```bash
php tools/content-storage-audit.php --scope=services --strict
npm run visual:smoke:prod
npm run seo:check:prod
```

## Phase 4 — Product Proof, Cases, Feedback And Clients

Status: starts after Phase 1; runtime after owner evidence
Priority: P1
Related gaps: `CSG-003`, `CSG-005`, `CONTENT-003`, `ARCH-009`

### Actions

1. Add product relation tagging for real cases.
2. Add product relation tagging for feedback and clients.
3. Add product page related-proof block only for approved real evidence:
   - related cases;
   - related feedback;
   - related clients.
4. Keep product readiness/proof artifacts in product content, clearly not customer cases.
5. Add empty-state behavior: do not render trust block if no approved related evidence.

### Challenge

Need: yes, with Sales/Content/SEO approval.

Do not convert readiness items into fake cases. Do not show client/trust claims without evidence.

### Acceptance Criteria

- Existing cases can be tagged by product.
- Product pages render approved related cases only when tags exist.
- No product page claims unsupported by related evidence or approved wording.
- Empty state does not create blank/awkward sections.

### Verification

```bash
php tools/content-storage-audit.php --scope=proof --strict
npm run seo:check
npm run product:source:http:prod
```

## Phase 5 — AI Agents Boundary

Status: after Phase 1 or parallel with Phase 4
Priority: P2
Related gaps: `CSG-006`, `CONTENT-005`, `ARCH-010`

### Actions

1. Keep `/aiagents/` demo-agent catalog under `aiagents #20`.
2. Keep `/agents/` product content under product iblocks.
3. Optionally add relation:
   - demo agent -> product `agents`;
   - demo agent -> product use case;
   - demo agent -> lead scenario.
4. Add SEO/content check to prevent `/agents/` and `/aiagents/` copy/canonical confusion.

### Challenge

Need: yes for boundary; optional for relation.

Do not duplicate product `Agents` content into `aiagents`.

### Acceptance Criteria

- `/aiagents/` remains demo/prototype entry.
- `/agents/` remains product page.
- If relations are added, rendered links/cards are evidence-backed and non-duplicative.

## Phase 6 — Structured Page Content Model

Status: design/architecture decision before implementation
Priority: P1
Related gaps: `CSG-007`, `CSG-008`, `CSG-012`

### Actions

1. Decide storage:
   - recommended: new `page_sections` + `page_blocks`;
   - alternative: structured extension of `static materials`, only if renamed/governed clearly.
2. Define schema:
   - page key;
   - section key;
   - active/sort;
   - title/text/eyebrow/theme;
   - component/template type;
   - child cards/items with icon, title, text, href, tone, meta.
3. Define page renderer wrapper component.
4. Add migration seed from current PHP partials.
5. Add fallback to PHP partials for first release.

### Challenge

Need: yes. This is the largest scope.

Do not store a whole section as a raw HTML blob. Do not use existing narrow catalog iblocks as page section storage.

### Acceptance Criteria

- Editors can update cards/items without JSON.
- Runtime can render structured sections with stable templates.
- Page-level section migration can be staged page by page.
- Fallback and rollback are explicit.

## Phase 7 — Static Section Migration Waves

Status: after Phase 6
Priority: P1/P2 by page
Related gaps: `CSG-007`, `CSG-008`

### Wave 1 — Highest Value / Lowest Risk

- `/services/`: `delivery-layer`, `process`, `tech`;
- `/price/`: `features`, `workstreams`;
- `/contacts/`: routing cards;
- `/offer/`: product bridge, bottom CTA.

### Wave 2 — Product Router / Company Narrative

- `/`: `ecosystem`, `fit-matrix`, `commercial`;
- `/about/`: values, history, vendor trust, technology contours;
- `/calculator/`: outcome cards, product-aware estimate cards;
- `/aiagents/`: services/how-it-works/bridge.

### Challenge

Need: yes per wave.

Do not migrate hero/CTA/form sections first if they are tightly coupled to SEO, forms, or page assets unless the wrapper contract is ready.

### Acceptance Criteria

- Each migrated page renders the same content before/after within approved differences.
- Browser smoke passes.
- Source PHP partial no longer contains migrated business copy or is marked fallback-only.

## Phase 8 — Governance Checks, Release Evidence And Fallback Retirement

Status: after Phases 2-7
Priority: P1
Related gaps: `CSG-010`, `CSG-011`, `STACK-007`

### Actions

1. Add aggregate check:
   - `content:storage:audit`;
   - `content:storage:check`;
   - production target evidence mode.
2. Add release runbook for:
   - migration apply;
   - cache clear;
   - Bitrix composite/template cache clear;
   - public API smoke;
   - browser smoke;
   - SEO check;
   - rollback.
3. Retire fallbacks after two conditions:
   - target evidence passes on production;
   - owner confirms admin editability.

### Challenge

Need: yes.

Do not retire fallbacks in the same deploy that first switches source unless rollback is trivial and evidence is already captured.

### Acceptance Criteria

- Check fails on product FAQ missing from `faq`.
- Check fails on hardcoded services fallback.
- Check reports page sections still in PHP with phase status.
- Release evidence is safe: no PII, no raw private customer data.

## Recommended Implementation Sequence

| Step | Phase | Reason |
|---:|---|---|
| 1 | Phase 0 | Prevents wrong migrations. |
| 2 | Phase 1 | Unlocks config/relation/audit foundation. |
| 3 | Phase 2 | Closes clearest domain-storage gap: product FAQ. |
| 4 | Phase 3 | Removes service source-of-truth violation. |
| 5 | Phase 8 partial | Add guards for already migrated domain gaps. |
| 6 | Phase 4 | Adds evidence/trust layer without fake claims. |
| 7 | Phase 5 | Clarifies `/agents/` vs `/aiagents/`. |
| 8 | Phase 6 | Designs page-content model. |
| 9 | Phase 7 Wave 1 | Migrates static sections in controlled scope. |
| 10 | Phase 7 Wave 2 | Completes broader page content migration. |
| 11 | Phase 8 final | Retires fallbacks and updates release gates. |

