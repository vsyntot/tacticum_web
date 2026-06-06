# Content Storage Release Runbook — 2026-06-05

Статус: active release/runbook
Workflow lane: Full Feature / Security & Integration for external evidence

## Scope

Runbook applies to source switches and content migrations for:

- `faq`
- `services`
- `cases`
- `feedback`
- `clients`
- `aiagents`
- `page_sections`
- `page_blocks`

Product layout iblocks remain covered by `product-content-source-switch-runbook.md`, but product pages now also depend on related `faq` rows.

## Do-Not-Move Policy

- `services` stores service catalog cards only.
- `cases` stores real approved customer cases only.
- `feedback` stores real testimonials only.
- `clients` stores approved client/trust entities only.
- `rates` stores staff/rate rows only.
- `team` stores people profiles only.
- `vacancies` stores open positions only.
- Generic page sections must not be moved into these iblocks. Use the future structured page-content model.
- `policies/static materials` must stay legal/static-document focused until a structured page-content ADR is approved.

## FAQ Switch

1. Run schema/relation foundation:

```bash
php tools/product-content-migration.php
```

2. Dry-run product FAQ migration:

```bash
php tools/content-storage-faq-migration.php
```

3. Apply on target Bitrix:

```bash
php tools/content-storage-faq-migration.php --apply
```

The migration creates/uses FAQ sections:

- root section `products`;
- product sections `platform`, `agents`, `dev`, `forum`.

Existing product FAQ rows are linked to their product section without overwriting question/answer text. Use `--update-existing` only when the seed text itself must be overwritten.

4. Verify target FAQ relation counts:

```bash
php tools/content-storage-audit.php --scope=faq --strict --json
npm run product:content:check:strict:json
```

Expected:

- each product has at least 3 related FAQ items;
- each product has a matching FAQ section by product code;
- related product FAQ items are linked to that product FAQ section;
- `rows[].faq_source` is `iblock`;
- runtime source marker remains `iblock`; `product_blocks.faq` fallback is retired after approved decision evidence.

For historical/safe-default validation of the original fallback-retirement decision:

```bash
npm run content:storage:faq-fallback-retirement:check -- docs/workflow/content-storage-faq-fallback-retirement-2026-06-05.draft.json --allow-draft
```

If `/docs` is not deployed to production, the same command uses the embedded safe draft baseline in the checker when `--allow-draft` is present.

The approved retirement decision must pass without `--allow-draft` before deploying code that removes `product_blocks.faq` fallback:

```bash
npm run content:storage:faq-fallback-retirement:check -- docs/workflow/content-storage-faq-fallback-retirement-2026-06-06.approved.json
```

After deploying fallback-retirement code, clear product content cache and re-run FAQ/product HTTP source smoke with `faq_source=iblock`.

## Services Switch

1. Confirm every rendered service card is an active `services` element.
2. Seed or update the target six service cards:

```bash
php tools/content-storage-services-seed.php
php tools/content-storage-services-seed.php --apply
```

Target services:

- `Предпроектная оценка и дорожная карта`
- `AI discovery и архитектура внедрения`
- `Пилот AI-агента на ваших данных`
- `Автоматизация клиентских диалогов`
- `AI/IT-разработка и интеграции`
- `Управляемая AI/IT-команда`

3. Confirm no template fallback card exists:

```bash
npm run content:storage:governance:check
php tools/content-storage-audit.php --scope=services --strict --json --base-url=https://tacticum.ru
```

4. If public/API count differs from admin count, record aggregate explanation: inactive, section-filtered, permission-filtered or content missing.

Do not put methodology, process, delivery or technology page copy into `services`.
Do not use the old hardcoded `Расчет проекта` card. If estimate belongs in the service grid, use the seeded `Предпроектная оценка и дорожная карта` service element.

## Proof Mapping

Before public product proof rendering:

1. Confirm `PRODUCT` relation exists for `cases`, `feedback` and `clients`.
2. Get Sales/Content/SEO approval for each public case/testimonial/client claim.
3. Run:

```bash
php tools/content-storage-audit.php --scope=proof --strict --json
```

Expected:

- `relations.cases`, `relations.feedback` and `relations.clients` are present, active, multiple and linked to `products`;
- `products[]` reports aggregate `cases_items`, `feedback_items`, `clients_items`, `proof_items_total` and public-render counts per product code;
- zero proof counts are allowed until owners tag and approve real evidence.

4. If product proof counts are zero or incomplete, generate the internal owner-review worksheet:

```bash
php tools/content-storage-proof-tagging-helper.php --json
```

The helper is read-only. It prints active item IDs, current `PRODUCT` tags and Bitrix admin edit paths without names, texts, contacts or raw claims. Owners should open the admin links and either set approved product tags or leave an item global/unrelated. Do not use the helper output as public release evidence; use aggregate audit output.

5. Capture owner decisions in the no-raw-copy approval draft:

```bash
php tools/content-storage-proof-tagging-proposal.php --output=/tmp/content-storage-proof-tagging-proposal.draft.json
```

Use the blank template only if the automated proposal needs to be ignored:

```bash
php tools/content-storage-proof-approval-template.php --output=/tmp/content-storage-proof-tagging-approval.draft.json
```

If `/docs` is deployed in the environment, the repository draft can also be used as the source template: `docs/workflow/content-storage-proof-tagging-approval-2026-06-05.draft.json`.

The generated `/tmp` proposal contains active item IDs, proposed decisions, product codes and short reasons. It intentionally does not include names, proof copy, contacts or Bitrix admin links. Use `content-storage-proof-tagging-helper.php --json` separately as the internal owner-review worksheet.

Allowed item decisions:

- `tag`: owners approve product relation; `product_codes` must be set.
- `global`: item remains general proof and is not tied to product pages.
- `not_public`: item must not render in product proof blocks.
- `pending`: draft-only; not allowed in final approved mode.

Validate draft while decisions are still incomplete:

```bash
npm run content:storage:proof:approval:check -- /tmp/content-storage-proof-tagging-proposal.draft.json --allow-draft
```

Validate final approval before tagging or public rendering:

```bash
npm run content:storage:proof:approval:check -- /tmp/content-storage-proof-tagging-approved.json
```

6. Apply owner-approved `PRODUCT` tags through the checked CLI path. On production without `/docs`, pass the approval JSON path explicitly, for example `/tmp/content-storage-proof-tagging-approved.json`.

```bash
php tools/content-storage-proof-tagging-apply.php --approval=/path/to/content-storage-proof-tagging-approved.json
php tools/content-storage-proof-tagging-apply.php --approval=/path/to/content-storage-proof-tagging-approved.json --apply
```

Expected:

- the approval JSON has `status=approved`;
- all `content`, `sales` and `seo` owner gates are approved;
- `decision=tag` sets only the approved product relation;
- `decision=global` and `decision=not_public` clear product relation only;
- no text, active flag, customer data, claim copy or public rendering behavior is changed.

7. Apply public proof rendering approval only after owners explicitly set `public_render_approved=true` for the same approved `decision=tag` items. This is a separate durable Bitrix gate; `PRODUCT` relation alone is not enough.

```bash
php tools/content-storage-proof-public-render-apply.php --approval=/path/to/content-storage-proof-tagging-approved.json
php tools/content-storage-proof-public-render-apply.php --approval=/path/to/content-storage-proof-tagging-approved.json --apply
php tools/product-content-cache-clear.php
```

Expected:

- the tool creates/checks `PUBLIC_RENDER_APPROVED` on `cases`, `feedback` and `clients`;
- it changes only `PUBLIC_RENDER_APPROVED`;
- it refuses `public_render_approved=true` if the current `PRODUCT` relation does not match the approved `product_codes`;
- product runtime reads public proof only through `PROPERTY_PRODUCT` plus `PUBLIC_RENDER_APPROVED=Y`;
- product pages switch `proof_source` from `readiness` to `iblock` only when a product has at least 3 public-approved proof items.

8. Re-run proof/product audit after owner tagging and public render approval:

```bash
php tools/content-storage-audit.php --scope=proof --strict --json
php tools/product-content-check.php --strict --json
TACTICUM_EXPECT_PRODUCT_FAQ_SOURCE=iblock TACTICUM_EXPECT_PRODUCT_PROOF_SOURCE=iblock npm run product:source:http:prod
```

Use `TACTICUM_EXPECT_PRODUCT_PROOF_SOURCE=readiness` instead of `iblock` when the approval file intentionally keeps public proof disabled or fewer than 3 public-approved items per product exist.

Production evidence 06.06.2026 after public proof-render approval:

- proof approval check passed with 17 items, 12 `public_render_approved=true`, 5 global clients and zero pending decisions;
- tagging dry-run/apply changed exactly 2 PRODUCT relations to match approval: `cases #181` and `feedback #92`;
- public-render dry-run/apply changed exactly 12 rows and applied `PUBLIC_RENDER_APPROVED=Y` on approved `cases`/`feedback` items;
- product cache clear completed with managed tags for product, FAQ and proof iblocks;
- strict proof audit passed with `public_proof_render_ready=true` for `platform`, `agents`, `dev` and `forum`;
- strict product content check passed with `proof_source=iblock` for all four products;
- product HTTP source smoke passed for `/platform/`, `/agents/`, `/dev/`, `/forum/` with `source=bitrix`, `faq_source=iblock`, `proof_source=iblock`;
- `seo:check:prod` passed.

9. Render nothing from real proof iblocks when related public-approved evidence is empty.

Do not convert product readiness artifacts into fake cases or fake client proof.

## AI Agents Boundary

Before changing `/agents/`, `/aiagents/` or demo-agent catalog storage:

```bash
npm run content:storage:aiagents-boundary:check
php tools/content-storage-audit.php --scope=aiagents --strict --json
```

Expected:

- `/agents/` remains the product page rendered through `tacticum:product.page`;
- `/aiagents/` remains the Telegram demo/prototype service route rendered through `tacticum:aiagents`;
- demo-agent list can read `PRODUCT` relation for future tagging, but does not render product proof/claims;
- canonical/SEO strategy changes require PM/SEO/Content approval.

To add the optional relation without changing public rendering:

```bash
php tools/content-storage-aiagents-tagging.php
php tools/content-storage-aiagents-tagging.php --apply
php tools/content-storage-audit.php --scope=aiagents --strict --json
```

Expected after apply:

- active `aiagents` demo/prototype rows are tagged only to product `agents`;
- `agents` reports `aiagents_items` equal to the active `aiagents` count;
- `platform`, `dev` and `forum` report `aiagents_items=0`;
- the apply tool prints only aggregate IDs/product codes/statuses, not element names, copy, contacts or raw claims.

## Structured Page Content Model

Before creating `page_sections` / `page_blocks` or migrating static page sections:

```bash
npm run content:storage:page-content-model:check
npm run content:storage:page-content-model:approved-check
npm run content:storage:page-content-model:approved-template
npm run content:storage:page-content:migrate
npm run content:storage:governance:check
```

If `/docs` is not deployed to production, these guards use embedded draft/model baselines for production-safe validation and keep repository docs as the source-of-truth during local/CI checks.

Expected:

- target model is `page_sections/page_blocks`, not `services`, `cases`, `team`, `vacancies`, `rates`, `clients`, `feedback` or `policies`;
- section/block model uses structured fields, not raw HTML/JSON blobs as primary editor workflow;
- first release keeps PHP fallback partials and explicit migration statuses;
- dry-run migration prints only schema actions and config hints; it does not seed copy or change runtime;
- touched page migration has owner approval, seed/apply evidence, cache clear, SEO check, visual smoke and action smoke.

Do not create `page_sections` / `page_blocks` before Architect/Content/Frontend/QA/SEO approve the model draft. After approval, use an explicit approved model path:

```bash
npm run content:storage:page-content:migrate:apply -- --model=docs/workflow/content-storage-page-content-model-2026-06-06.approved.json
php tools/content-storage-audit.php --scope=page-content --strict --json
```

If `/docs` is not deployed to production, generate the same schema-only approved JSON into `/tmp` and pass that explicit path:

```bash
npm run content:storage:page-content-model:approved-check
npm run content:storage:page-content-model:approved-template
php tools/content-storage-page-content-migration.php --model=/tmp/content-storage-page-content-model-2026-06-06.approved.json --apply
php tools/content-storage-audit.php --scope=page-content --strict --json
```

This approval is schema-only: it permits empty iblock/property creation, but does not approve page copy seed, public runtime switch or fallback retirement.

Expected after approved schema apply:

- config registry has `page_sections` and `page_blocks` keys;
- `page_sections` has required `PAGE_KEY`, `SECTION_KEY`, `TEMPLATE_KEY`, `MIGRATION_STATUS` properties;
- `page_blocks` has required `SECTION`, `BLOCK_KEY`, `ITEM_TYPE` properties;
- `page_blocks.SECTION` links to `page_sections`;
- public runtime remains unchanged until a separate page-level seed/switch is approved.

After schema apply, seed wave 1 only in shadow mode:

```bash
npm run content:storage:page-content:seed
npm run content:storage:page-content:seed:apply
php tools/content-storage-audit.php --scope=page-content --strict --json
```

Expected after wave 1 shadow seed:

- seeded pages: `/services/`, `/price/`, `/contacts/`, `/offer/`;
- `page_sections` active rows: `9`;
- `page_blocks` active rows: `37`;
- `page_content_rows.orphan_blocks=0`;
- every seeded section has `MIGRATION_STATUS=shadow`;
- every seeded section keeps `FALLBACK_PARTIAL`;
- seed logs print only action/code/id/status, not section copy;
- public runtime remains unchanged and PHP fallback partials remain the rendered source.

Wave 1 scope:

- `/services/`: `delivery-layer`, `process`, `tech`;
- `/price/`: `features`, `workstreams`;
- `/contacts/`: `routing`, `cards`;
- `/offer/`: `product-bridge`, `bottom-cta`.

Historical wave 2 shadow seed step was completed on production on 06.06.2026.
Rerun only with the current seed tool version, which preserves an existing
non-empty `MIGRATION_STATUS` on updates; older seed code can demote live rows
back to `shadow` and must not be used on production:

```bash
npm run content:storage:page-content:seed:wave2
npm run content:storage:page-content:seed:wave2:apply
php tools/content-storage-audit.php --scope=page-content --strict --json
npm run page-content:source:http:wave2:fallback:prod
```

Expected after the historical wave 2 shadow seed:

- seeded pages: `/`, `/about/`, `/calculator/`, `/aiagents/`;
- seeded sections: `ecosystem`, `fit-matrix`, `commercial`, `company-trust`, `values-team`, `career-final`, `calculator-outcome-cards`, `product-aware-estimate-cards`, `agents-bridge`, `how-it-works`, `services`;
- every wave 2 section has `MIGRATION_STATUS=shadow`;
- every wave 2 section keeps `FALLBACK_PARTIAL`;
- public runtime remains unchanged;
- do not run fallback retirement for wave 2 until scoped live/source/smoke evidence exists.

Production evidence 06.06.2026 after wave 2 shadow seed:

- dry-run planned 11 new sections and 43 new blocks;
- apply created 11 sections and 43 blocks;
- strict page-content audit passed with 20 active sections, 80 active blocks and `orphan_blocks=0`;
- wave 1 pages remained `live`;
- wave 2 pages remained `shadow`;
- default wave 1 source HTTP check still passed with Bitrix markers;
- scoped wave 2 fallback HTTP check passed for `/`, `/about/`, `/calculator/` and `/aiagents/` with zero Bitrix markers.

If row content or template keys must be corrected after live, rerun the current
wave 2 seed apply only after confirming it preserves existing live statuses.
This updates existing rows only, including the `calculator-outcome-cards`
template key to `calculator-chat-outcome` so the calculator chat surface remains
present in Bitrix-rendered mode:

```bash
npm run content:storage:page-content:seed:wave2
npm run content:storage:page-content:seed:wave2:apply
php tools/content-storage-audit.php --scope=page-content --strict --json
```

Historical pre-live runtime foundation deploy was safe only while unapproved
page sections stayed `MIGRATION_STATUS=shadow` and
`page_content.allow_fallback=true` remained effective:

```bash
npm run config:runtime:check
php tools/content-storage-audit.php --scope=page-content --strict --json
npm run page-content:source:http:wave2:fallback:prod
npm run content:storage:governance:check
```

Historical expected state before the wave 2 live switch:

- config summary may already report `Page content: source=bitrix` for wave 1;
- wave 2 rows remain `MIGRATION_STATUS=shadow`;
- wave 2 public pages continue rendering PHP fallback partials because no wave 2 section is live;
- `page_content.allow_fallback=true` remains deployed for rollback;
- no wave 2 page section is promoted to `live`.

Historical live-status approval path, used before promoting seeded sections to
`live`:

```bash
npm run content:storage:page-content:live-approval-template:wave2 -- --output=/tmp/content-storage-page-content-live-approval-wave2.draft.json --force
npm run content:storage:page-content:live-approval:check -- /tmp/content-storage-page-content-live-approval-wave2.draft.json --allow-draft
```

For wave 2, prefer a scoped draft so already-live wave 1 sections are not mixed into the approval file:

```bash
npm run content:storage:page-content:live-approval-template:wave2 -- --output=/tmp/content-storage-page-content-live-approval-wave2.draft.json --force
npm run content:storage:page-content:live-approval:check -- /tmp/content-storage-page-content-live-approval-wave2.draft.json --allow-draft
```

The approval JSON may contain only section IDs, page keys, section keys, template keys, statuses and boolean gates. It must not contain section copy, CTA text, contacts, admin URLs, screenshots or raw HTML.

For approved promotion, owners must set:

- `status=approved`;
- owner gates `architect`, `content`, `frontend`, `qa`, `seo` to approved;
- gates `config_runtime_check`, `strict_page_content_audit`, `governance_check`, `seo_check`, `rollback_plan`, `post_switch_visual_smoke_required`, `post_switch_browser_smoke_required` to `true`;
- section decisions to `promote_live` with `target_status=live` and `section_live_approved=true`, or `keep_shadow` / `demote_shadow` with `target_status=shadow`;
- `source_switch_approved=false`;
- `fallback_retirement_approved=false`.

Validate and apply live-status decisions:

```bash
npm run content:storage:page-content:live-approval:check -- /tmp/content-storage-page-content-live-approval.approved.json
php tools/content-storage-page-content-live-apply.php --approval=/tmp/content-storage-page-content-live-approval.approved.json
php tools/content-storage-page-content-live-apply.php --approval=/tmp/content-storage-page-content-live-approval.approved.json --apply
php tools/content-storage-audit.php --scope=page-content --strict --json
```

Expected after live-status apply:

- only `page_sections.MIGRATION_STATUS` changes;
- `page_content.source` remains `fallback`;
- PHP fallback partials remain the rendered source until the later source switch and approved fallback-retirement deploy;
- audit shows approved sections as `live` and non-approved sections as `shadow`;
- `page_content.source=bitrix` remains a separate environment switch.

Confirm that live-status apply did not switch rendered HTML:

```bash
npm run page-content:source:http:fallback:prod
```

Use the default command only before a wave 1 source switch. If wave 1 is already live on Bitrix and only wave 2 is being prepared, use the scoped fallback check instead:

```bash
TACTICUM_PAGE_CONTENT_SOURCE_BASE_URL=https://tacticum.ru \
TACTICUM_EXPECT_PAGE_CONTENT_SOURCE=fallback \
TACTICUM_PAGE_CONTENT_SOURCE_PAGES=/,/about/,/calculator/,/aiagents/ \
node ./tools/page-content-source-http-check.mjs
```

Expected before a wave 2 source switch:

- `/`, `/about/`, `/calculator/`, `/aiagents/` return HTTP `200`;
- `sections=0`;
- no `data-page-content-source=bitrix` markers are present.

The live switch is a separate approval. It requires:

- promote only the approved sections from `shadow` to `live`;
- set `page_content.source=bitrix` only for the approved environment;
- keep `page_content.allow_fallback=true`;
- for `/contacts/` cards, keep office/legal facts sourced from contact component config, not generic iblock copy;
- run SEO, visual smoke and browser/action smoke for every touched page;
- keep PHP fallback partials until rendered equivalence and owner admin-editability are confirmed.

After deploying `page_content.source=bitrix`, run:

```bash
npm run config:runtime:check
npm run page-content:source:http:prod
php tools/content-storage-audit.php --scope=page-content --strict --json
npm run seo:check:prod
```

Expected after source switch:

- config summary reports `Page content: source=bitrix`;
- `/services/` has 3 Bitrix-rendered page-content sections;
- `/price/` has 2 Bitrix-rendered page-content sections;
- `/contacts/` has 2 Bitrix-rendered page-content sections;
- `/offer/` has 2 Bitrix-rendered page-content sections;
- rendered sections carry `data-page-content-source`, `data-page-content-page`, `data-page-content-section` and `data-page-content-template`;
- `allow_fallback=true` remains effective for rollback.

Wave 2 source evidence command, passed on production on 06.06.2026 after owner
approval and live-status apply:

```bash
npm run page-content:source:http:wave2:prod
```

Expected after a wave 2 source switch is page-specific and must match the approved sections for `/`, `/about/`, `/calculator/` and `/aiagents/`; `/calculator/` must report `calculator-outcome-cards` with `calculator-chat-outcome` so the chat surface is preserved. Do not use this command as release evidence before those sections are promoted to `live`.

Production evidence 06.06.2026 after source switch:

- `config:runtime:check` passed and reports `Page content: source=bitrix (explicit)`, `live_status=live (explicit)`, `allow_fallback=true (explicit)`;
- `page-content:source:http:prod` passed: `/services/ sections=3/3`, `/price/ sections=2/2`, `/contacts/ sections=2/2`, `/offer/ sections=2/2`;
- strict `content-storage-audit.php --scope=page-content` passed with 9 active sections, 37 active blocks, all wave 1 sections `live` and `orphan_blocks=0`;
- `seo:check:prod` passed;
- Targeted Chrome-capable visual smoke passed for `/services/`, `/price/`, `/contacts/`, `/offer/`; manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-06T08-46-50-062Z/manifest.json`.
- Targeted Chrome-capable browser/action smoke passed for `/services/`, `/price/`, `/contacts/`, `/offer/`; manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-06T08-47-36-697Z/manifest.json`.
- Production server has no Chrome/Chromium, so browser evidence is collected from a Chrome-capable local/CI runner.

Production evidence 06.06.2026 after wave 2 live/source switch:

- scoped live approval/check passed with 11 `promote_live` decisions;
- `content-storage-page-content-live-apply.php --apply` promoted all 11 wave 2 sections to `MIGRATION_STATUS=live`;
- strict `content-storage-audit.php --scope=page-content` passed with 20 active sections, 80 active blocks, all wave 2 sections `live` and `orphan_blocks=0`;
- initial scoped source HTTP check found `/calculator/` `calculator-outcome-cards` template `feature-card-grid`; production row #3148 was corrected to `calculator-chat-outcome`;
- `page-content:source:http:wave2:prod` then passed: `/` `3/3`, `/about/` `3/3`, `/calculator/` `2/2`, `/aiagents/` `3/3`;
- `seo:check:prod` passed;
- Chrome-capable targeted visual smoke passed locally for `/`, `/about/`, `/calculator/`, `/aiagents/`;
- Chrome-capable targeted visual/action smoke passed locally for `/`, `/about/`, `/calculator/`, `/aiagents/`; manifests: /var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-06T17-49-39-090Z/manifest.json and /var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-06T17-50-36-958Z/manifest.json.

## Page-Content Fallback Retirement

Fallback retirement is separate from live-status promotion and from the `page_content.source=bitrix` switch. Generate a no-raw-copy owner draft from production section IDs:

```bash
php tools/content-storage-page-content-fallback-retirement-template.php --output=/tmp/content-storage-page-content-fallback-retirement.draft.json --force
npm run content:storage:page-content:fallback-retirement:check -- /tmp/content-storage-page-content-fallback-retirement.draft.json --allow-draft
```

For wave 2, generate a scoped draft after the wave 2 sections are live and the
Chrome-capable smoke evidence exists:

```bash
npm run content:storage:page-content:fallback-retirement-template:wave2 -- --output=/tmp/content-storage-page-content-fallback-retirement-wave2.draft.json --force
npm run content:storage:page-content:fallback-retirement:check -- /tmp/content-storage-page-content-fallback-retirement-wave2.draft.json --allow-draft
```

Production draft/check on 06.06.2026 first passed as inventory only: 9 items, all pending, `retirement_allowed=false`, production evidence `0/9` and owner gates `0/5`. The owner-approved JSON then passed with `retirement_allowed=true`, production evidence `9/9`, owner gates `5/5` and 9 `retire_fallback` decisions. Local code retirement removes only the approved fallback section bodies; it does not change `page_content.source`, `live_status` or iblock data.

Production wave 2 retirement on 06.06.2026 is closed: the approved check passed
with `retirement_allowed=true`, 11 `retire_fallback` decisions, production
evidence `9/9` and owner gates `5/5`; deployed code removed the approved
fallback bodies, and post-deploy runtime/source/audit/SEO/browser checks passed.

Before approving retirement, owners must confirm:

- `page_content.source=bitrix` is explicit and `allow_fallback=true` remains available for rollback;
- strict page-content audit, page-content source HTTP check, `seo:check:prod`, targeted visual smoke and targeted browser/action smoke passed;
- Content/admin confirms the retiring sections are editable through `page_sections/page_blocks` fields, not through raw copy or HTML blobs;
- QA confirms rollback to `page_content.source=fallback` plus fallback source HTTP check;
- Frontend confirms removing PHP fallback partial bodies will not remove shared component/config logic;
- SEO confirms no title/H1/canonical/structured-data regression on the four switched pages.

Approved JSON must have `status=approved`, `retirement_allowed=true`, all production evidence booleans true, all owner gates true and each retiring item set to `decision=retire_fallback`, `admin_editability_approved=true`, `fallback_retirement_approved=true`. Validate without `--allow-draft`:

```bash
npm run content:storage:page-content:fallback-retirement:check -- /tmp/content-storage-page-content-fallback-retirement.approved.json
```

For wave 2 use the scoped approval path:

```bash
npm run content:storage:page-content:fallback-retirement:check -- /tmp/content-storage-page-content-fallback-retirement-wave2.approved.json
```

This approval still does not remove files or change runtime. Actual fallback retirement is a separate code/deploy change followed by the same source/audit/SEO/browser checks and rollback evidence. Post-deploy evidence 06.06.2026 passed: runtime config reported page-content source bitrix/live/allow_fallback, HTTP source markers reported /services/ 3/3, /price/ 2/2, /contacts/ 2/2 and /offer/ 2/2, strict page-content audit passed with 9 live sections, 37 active blocks and orphan_blocks=0, seo:check:prod passed, targeted Chrome-capable visual smoke passed (/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-06T09-32-49-026Z/manifest.json), and targeted Chrome-capable browser/action smoke passed (/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-06T09-33-33-068Z/manifest.json).

## Cache And Smoke

After FAQ or product-related content changes:

```bash
php tools/product-content-cache-clear.php --dry-run --json
php tools/product-content-cache-clear.php
TACTICUM_EXPECT_PRODUCT_FAQ_SOURCE=iblock npm run product:source:http:prod
npm run product:source:smoke:prod
```

If Chrome/Chromium is not installed on the target server, use `TACTICUM_EXPECT_PRODUCT_FAQ_SOURCE=iblock npm run product:source:http:prod` as the server-side source marker evidence and run browser smoke from a Chrome-capable runner.

After services/cases/feedback/clients public source changes:

```bash
php tools/content-storage-audit.php --json --base-url=https://tacticum.ru
npm run seo:check:prod
npm run visual:smoke:prod
```

If `visual:smoke:prod` fails with `Chrome executable not found`, treat it as an environment blocker. Install Chrome/Chromium, set `CHROME_PATH`, or run the same command from a Chrome-capable CI/developer machine; do not mark it as a content failure.

Evidence must be aggregate only: counts, statuses, source markers and safe internal links. Do not paste raw element text, contacts, cookies, session IDs, IPs, User-Agent strings or request bodies.

## Rollback

- After approved FAQ fallback retirement, runtime no longer uses `product_blocks.faq`; rollback is previous release redeploy or restoring the fallback code path, followed by product cache clear.
- Restore previous source rows or deactivate newly migrated rows only through Bitrix admin/migration with owner approval.
- Clear product content cache after rollback.
- Re-run product source smoke and SEO smoke.

## Closure Criteria

- `content:storage:governance:check` passes.
- `content-storage-audit` evidence is attached without PII/raw content.
- Product FAQ strict evidence has `faq_source=iblock`.
- Services fallback remains absent from the template.
- Owner-gated proof/page-section decisions are not marked closed without external evidence or ADR.
