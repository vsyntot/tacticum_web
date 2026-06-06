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

Status: proof audit passed / public rendering gated
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
   - per-product aggregate counts for related `cases`, `feedback` and `clients`;
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

Implementation update 05.06.2026:

- `clients` config key added.
- Relation migration/check now includes `feedback` and `clients`.
- `content-storage-audit.php` added for aggregate counts, relation coverage, per-product proof relation counts and optional public API counts.
- Production migration created `PRODUCT` properties on `feedback #9` and `clients #8`.
- Strict proof-scope audit passed; public proof rendering still requires owner evidence.

## Phase 2 — FAQ Target Migration

Status: implemented / production evidence passed
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
   - no runtime fallback to `product_blocks.faq` after final retirement approval.
3. Add guard:
   - rendered product FAQ question must exist in FAQ iblock evidence;
   - FAQPage schema count/questions must remain stable.

### Challenge

Need: yes. Product FAQ is a domain FAQ, not a layout block.

Do not delete historical `product_blocks.faq` rows during the first retirement release. Runtime fallback is removed, but rollback remains a previous release redeploy or restoring the fallback code path.

### Acceptance Criteria

- Product pages still render 3 FAQ items each.
- `/local/api/faq.php` includes 12 product FAQ questions or a target audit proves they exist in FAQ iblock.
- Product renderer source marker/evidence reports `faq_source=iblock`; `fallback` is no longer an accepted runtime source after retirement.
- No duplicate FAQ schema on product pages.

### Verification

```bash
php tools/content-storage-faq-migration.php --dry-run
php tools/content-storage-faq-migration.php --apply
php tools/content-storage-audit.php --scope=faq --strict
TACTICUM_EXPECT_PRODUCT_FAQ_SOURCE=iblock npm run product:source:http:prod
npm run product:source:smoke:prod
```

Implementation update 05.06.2026:

- Product runtime reads `faq` iblock first by `PROPERTY_PRODUCT`.
- `product_blocks.faq` fallback was removed locally after final retirement approval.
- Rendered hero exposes `data-product-faq-source`.
- Strict product evidence requires `faq_source=iblock`.
- Product cache tags include `faq` to invalidate product pages after related FAQ edits.
- Production FAQ seed/audit passed: 12 FAQ rows created and each product has 3 related FAQ items.
- Production cache clear passed with FAQ managed tag `iblock_id_10`.
- Strict product content check passed with `faq_source=iblock` for all four products.
- Production HTTP source smoke confirmed `data-product-faq-source=iblock` on `/platform/`, `/agents/`, `/dev/` and `/forum/`.
- Browser `product:source:smoke:prod` still requires a Chrome-capable environment; server-side HTTP evidence is accepted fallback on production server.
- Follow-up section challenge: product runtime does not need FAQ sections, but editor UX and page FAQ governance do. `content-storage-faq-migration.php` now creates root section `products`, product sections `platform/agents/dev/forum`, links existing/new product FAQ rows to the matching product section, and strict FAQ audit checks `faq_items_without_section`.
- Production section sync passed: sections were created, 12 existing product FAQ rows were linked, and strict FAQ audit returned `faq_items_in_section=3` / `faq_items_without_section=0` per product.
- Chrome-capable rendered and action production smoke passed for all product pages.
- `content-storage-faq-fallback-retirement-2026-06-06.approved.json` passes the final gate with `retirement_allowed=true`, production evidence `9/9` and owner gates `4/4`; post-deploy cache clear, strict FAQ audit, strict product content check and HTTP source smoke passed with `faq_source=iblock`.

## Phase 3 — Services Source Of Truth

Status: production evidence passed
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
php tools/content-storage-audit.php --scope=services --strict --base-url=https://tacticum.ru
npm run content:storage:governance:check
npm run visual:smoke:prod
npm run seo:check:prod
```

Implementation update 05.06.2026:

- `news.list/services` no longer renders the hardcoded "Расчет проекта" fallback.
- `content-storage-services-seed.php` defines six target service cards and updates the legacy AI-agents/T&M rows by name.
- `/services/` service list now expects six source-of-truth iblock cards.
- Production seed/audit passed: `created=4`, `updated=2`, active service cards `6`, public API items `6`, `PRODUCT` relation `link_ok=true`.
- `seo:check:prod` passed after the services seed.
- Chrome-capable `visual:smoke:prod` and `browser:smoke:prod` passed after the services seed.

## Phase 4 — Product Proof, Cases, Feedback And Clients

Status: product tags applied / public rendering gated
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

Implementation update 05.06.2026:

- Strict proof audit passed on production.
- Active/total counts: `clients 5/5`, `feedback 3/3`, `cases 9/10`.
- `PRODUCT` relations are present, active, multiple and linked to `products #21` for `faq`, `cases`, `offer`, `services`, `aiagents`, `feedback` and `clients`.
- Owner-approved proof tagging proposal/check/apply passed for 17 active proof items without storing raw proof copy or changing public rendering.
- Corrected production proof count evidence reports `platform proof_items_total=6`, `agents proof_items_total=6`, `dev proof_items_total=6`, `forum proof_items_total=5`; these counts are relation readiness evidence, not approval to render public proof.
- `content-storage-proof-tagging-helper.php` provides a read-only internal owner-review worksheet with item IDs, current product tags and admin edit paths.
- `content-storage-proof-approval-template.php` generates a no-raw-copy blank approval draft and `content-storage-proof-tagging-proposal.php` generates a proposed product-tagging draft from active proof IDs for production environments without `/docs`.
- `content-storage-proof-tagging-approval-2026-06-05.draft.json` and `content-storage-proof-approval-check.mjs` define the no-raw-copy owner approval contract before public proof implementation.
- `content-storage-proof-tagging-apply.php` provides dry-run/apply for approved `PRODUCT` tags and changes only the relation.
- Public product proof rendering remains gated by Sales/Content/SEO approval and approved product tags.

## Phase 5 — AI Agents Boundary

Status: product relation applied / boundary guarded
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

### Verification

```bash
npm run content:storage:aiagents-boundary:check
php tools/content-storage-audit.php --scope=aiagents --strict --json
```

Implementation update 05.06.2026:

- `/agents/` remains rendered through `tacticum:product.page` with `PRODUCT_CODE=agents` and canonical `/agents/`.
- `/aiagents/` remains rendered through `tacticum:aiagents`, reads `aiagents #20`, uses `Service` schema and keeps the Telegram demo/prototype route.
- Demo-agent list now requests `PRODUCT` property for owner tagging but does not render product claims.
- `content-storage-aiagents-boundary-check.mjs` guards the source boundary and is part of product content safety checks.
- `content-storage-aiagents-tagging.php` can dry-run/apply `PRODUCT=agents` for active demo-agent rows without printing names/copy or changing public rendering.
- `content-storage-audit.php --scope=aiagents --strict --json` now reports `aiagents_items` per product and expects active demo-agent rows to relate only to product `agents`.
- Production apply evidence passed: active demo-agent rows #523, #524 and #525 now have `PRODUCT=agents`; strict aiagents audit reports `agents aiagents_items=3` and zero items for other products. SEO/canonical strategy remains unchanged.
- Chrome-capable targeted visual/action smoke passed for `/agents/` and `/aiagents/`; public copy/canonical strategy was not changed.

## Phase 6 — Structured Page Content Model

Status: source switch and smoke passed
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

### Verification

```bash
npm run content:storage:page-content-model:check
npm run content:storage:page-content:migrate
npm run content:storage:page-content:seed
npm run content:storage:page-content:live-approval:check -- /tmp/content-storage-page-content-live-approval.approved.json
php tools/content-storage-page-content-live-apply.php --approval=/tmp/content-storage-page-content-live-approval.approved.json
npm run content:storage:governance:check
```

Implementation update 05.06.2026:

- `content-storage-page-content-model-2026-06-05.draft.json` defines the draft `page_sections/page_blocks` target model.
- The draft explicitly rejects `services`, `cases`, `feedback`, `clients`, `team`, `vacancies`, `rates` and `policies` as generic page-section storage.
- The draft requires structured section/block fields, fallback partials, migration statuses, owner gates and staged waves.
- `content-storage-page-content-model-check.mjs` validates this contract and is part of product content safety checks.
- `content-storage-page-content-migration.php` can dry-run the `page_sections/page_blocks` schema and refuses `--apply` unless the model JSON is `status=approved` with architect/content/frontend/qa/seo gates true.
- `content-storage-page-content-seed.php` can dry-run/apply wave 1 rows in shadow mode only, retains fallback partial references and changes no public runtime.
- `content-storage-page-content-live-approval-template.php`, `content-storage-page-content-live-approval-check.mjs` and `content-storage-page-content-live-apply.php` gate `MIGRATION_STATUS=live` promotion/demotion without raw copy, without source switch and without fallback retirement.
- `content-storage-page-content-fallback-retirement-template.php` and `content-storage-page-content-fallback-retirement-check.mjs` gate fallback retirement separately, including scoped wave 2 approval files; approved JSON is process evidence only and file/runtime removal remains a separate code/deploy change.
- `page_content.source=fallback|bitrix` and `MIGRATION_STATUS=live` guard the runtime foundation; default config remains fallback, while production can explicitly switch to `bitrix` only after owner-gated live status and source-check evidence.
- `content-storage-audit.php --scope=page-content --strict --json` verifies registry keys, required schema and aggregate row/orphan evidence after approved apply/seed.
- Production pre-apply dry-run 06.06.2026 confirmed the schema plan: `tacticum_page_sections`, `tacticum_page_blocks` and 25 planned properties; no seed or runtime switch happened.
- `content-storage-page-content-model-2026-06-06.approved.json` is schema-only approval: empty schema creation is allowed, but page copy seed, public runtime switch and fallback retirement remain out of scope.
- Production schema apply passed on 06.06.2026: `tacticum_page_sections #24`, `tacticum_page_blocks #25`, 25 properties created, config registry updated and strict page-content audit passed with `page_blocks.SECTION` linked to #24.
- Production wave 1 shadow seed passed for `/services/`, `/price/`, `/contacts/` and `/offer/`: 9 active sections, 37 active blocks and `orphan_blocks=0`. Production live-approval check and live-status apply promoted all 9 sections to `MIGRATION_STATUS=live`; pre-switch fallback HTTP source check passed with zero Bitrix markers. After the explicit environment switch to `page_content.source=bitrix`, runtime config check, page-content source HTTP check, strict page-content audit and `seo:check:prod` passed; `/services/`, `/price/`, `/contacts/` and `/offer/` now report Bitrix-rendered section counts `3/3`, `2/2`, `2/2` and `2/2` respectively.
- Live-only runtime foundation is active on production for wave 1 behind `page_content.source=bitrix` plus `MIGRATION_STATUS=live`; approved PHP fallback section bodies are retired after owner approval and post-deploy source/audit/SEO/browser checks.
- Production fallback-retirement approval on 06.06.2026 passed with 9 `retire_fallback` decisions, `retirement_allowed=true`, production evidence `9/9` and owner gates `5/5`; deployed code removed the approved fallback section bodies, post-deploy runtime/source/audit/SEO and targeted Chrome-capable visual/browser checks passed, and governance now forbids static `<section>` fallback reintroduction.

## Phase 7 — Static Section Migration Waves

Status: source switch and smoke passed
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
- First wave seed is applied in `MIGRATION_STATUS=shadow`; sections must be promoted to `live` only after page-level renderer approval, config switch plan and smoke evidence pass.

## Phase 8 — Governance Checks, Release Evidence And Fallback Retirement

Status: production evidence passed / FAQ fallback retired
Priority: P1
Related gaps: `CSG-010`, `CSG-011`, `STACK-007`

### Actions

1. Add aggregate check:
   - `content:storage:audit`;
   - `content:storage:governance:check`;
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

Implementation update 05.06.2026:

- `content-storage-release-runbook-2026-06-05.md` added.
- `content:storage:governance:check` guards config, relations, FAQ source, services fallback and runbook presence.
- Product target evidence validator now requires `iblocks.faq` and `rows[].faq_source=iblock`.
- Production cache/source/audit/SEO evidence passed for FAQ and services source switches.
- FAQ fallback retirement approval passed and post-deploy cache clear, strict FAQ audit, strict product content check and HTTP source smoke passed with `faq_source=iblock`.
- Chrome-capable rendered/action smoke passed from local environment after production server was confirmed to lack Chrome/Chromium.
- Follow-up 06.06.2026 server visual/action smoke remained blocked by missing Chrome/Chromium; targeted Chrome-capable local visual/action smoke passed for changed URLs `/platform/`, `/agents/`, `/dev/`, `/forum/`, `/aiagents/`.

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
