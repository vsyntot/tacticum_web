# Content Storage Target Gap Analysis — 2026-06-05

Дата: 05.06.2026
Статус: production evidence passed / future changes guarded
Workflow lane: Full Feature

## Purpose

Этот документ фиксирует результат challenge по целевому хранению контента в Bitrix-инфоблоках после product content migration. Цель — отделить:

- что уже является корректно редактируемым Bitrix content;
- что лежит в Bitrix, но не на правильной доменной "полке";
- что всё ещё остается PHP/template content;
- что не надо переносить в существующие инфоблоки, чтобы не создать новую свалку.

Документ был входом для implementation planning; текущий production scope закрыт evidence-цепочкой, а будущие изменения должны сохранять эти границы.

## Source Evidence

Проверенные источники:

- `local/php_interface/include/tacticum_config.php`: текущий registry инфоблоков;
- admin screenshots 05.06.2026:
  - `services`: `AI-агенты #20`, `Услуги #12`, `Ставки out-staff #11`;
  - `company`: `Статические материалы #19`, `Команда #18`, `Вакансии #7`, `Клиенты #8`, `FAQ #10`, `Кейсы #13`, `Отзывы #9`;
- `docs/adr/ADR-010-product-content-bitrix-model.md`;
- `tools/product-content-migration.php`;
- product seed data in `local/php_interface/include/product_data/*.php`;
- public pages/components under `local/components/tacticum/*`;
- public API endpoints `/local/api/{faq,cases,services,rates}.php`.

## Current Verdict

Product content migration successfully moved product pages into Bitrix-managed product iblocks:

- `products` `#21`;
- `product_blocks` `#22`;
- `product_use_cases` `#23`.

But this is not the same as final semantic content storage. Several domain entities are still either:

- stored inside `product_blocks` when editors would expect an existing domain iblock;
- hardcoded in PHP page partials;
- present in admin but not wired into config/runtime;
- rendered through template fallback instead of source-of-truth iblock data.

## Target Principles

| Principle | Target Rule |
|---|---|
| Domain iblock ownership | FAQ lives in `faq`, cases in `cases`, rates in `rates`, team in `team`, vacancies in `vacancies`, clients in `clients`, feedback in `feedback`. |
| Product layout ownership | Product page layout/facts may stay in `products/product_blocks/product_use_cases` when there is no better domain iblock. |
| Page section ownership | Generic marketing/page sections must use a structured page-content model, not fake `services`, `cases`, `team` or raw HTML blobs. |
| Editor UX | No primary editor workflow should require JSON editing or opaque HTML walls. |
| Runtime safety | New source reads must have temporary fallback until target migration and owner review pass. |
| Evidence | Every source switch must have automated checks, target Bitrix evidence, cache clear and rendered smoke. |
| Config discipline | New code uses symbolic iblock keys via config helpers; no hardcoded IDs. |

## Iblock Challenge Matrix

| Iblock | Current State | Target Decision | Need / Do Not Need Challenge |
|---|---|---|---|
| `faq` `#10` | Public page FAQ uses this iblock, but product FAQ lives in `product_blocks`. | Product FAQ must be migrated/read from `faq`, with product relation and section/code grouping. | Need. This is the clearest wrong-storage gap. |
| `services` `#12` | `/services/` list reads the iblock, but template can hardcode fallback service card. | Every service card rendered as service must exist in `services`. | Need. Remove template fallback after seed. Do not put service methodology/process/tech sections here. |
| `cases` `#13` | Home/services use cases list; product proof readiness is separate in product blocks. | Real approved cases get product relation and can appear on product pages. Product readiness artifacts stay separate. | Need. Do not convert readiness/proof artifacts into fake customer cases. |
| `feedback` `#9` | Home uses feedback list; product relation was not created by current migration. | Add product relation for product-specific trust. | Need. Empty state must be allowed; no fake testimonials. |
| `clients` `#8` | Exists in admin screenshot but missing from config registry and not visibly used in active page code. | Add `clients` config key and decide public use/trust relation. | Need. Either wire it or explicitly retire public usage. |
| `aiagents` `#20` | Used as demo-agent catalog on `/aiagents/`; product `/agents/` is separate. | Keep demo agents separate from product Agents; optional relation to product/use case. | Need boundary decision. Do not duplicate `products.agents`. |
| `rates` `#11` | Correctly used by `/price/` staff/rate list. | Keep narrow: roles/rates/options. | Do not move price feature/workstream copy here. |
| `team` `#18` | Correctly used for team members. | Keep narrow: people profiles. | Do not move values/history/trust cards here. |
| `vacancies` `#7` | Correctly used for vacancy list. | Keep narrow: open positions. | Do not move culture/benefits/career intro here. |
| `policies/static materials` `#19` | Used as policy/static detail content. | Keep legal docs or explicitly evolve into structured page-content model. | Need decision. Do not store page sections as one HTML blob. |
| `offer` `#5` | Offer catalog/detail is its own domain model. | Keep offers/estimates; add product relation only if evidence/proof map requires. | Need only in proof/evidence mapping scope. |

## Gap Register

| ID | Status | Priority | Area | Gap | Target |
|---|---|---|---|---|---|
| CSG-001 | implemented / production evidence passed | P1 | FAQ / Product content | Product FAQ renders from `product_blocks`, not from `faq #10`; first seed created related FAQ rows without product FAQ sections. | Runtime reads FAQ only from `faq #10` through `PROPERTY_PRODUCT`; production FAQ seed/audit, HTTP source smoke, product FAQ section sync and Chrome-capable browser smoke passed. Final retirement decision is approved with owner gates `4/4`; post-deploy cache clear, strict FAQ audit, strict product content check and HTTP source smoke passed with `faq_source=iblock`. |
| CSG-002 | implemented | P1 | Config / Clients | `clients #8` exists in admin, but config registry does not expose `clients`. | `clients` key added to config/example/runtime summary and content config validation. |
| CSG-003 | public rendering live / production evidence passed | P1 | Product relations | Existing migration adds `PRODUCT` relation to `faq/cases/offer/services/aiagents`, but not `feedback/clients`. | Production migration created `PRODUCT` relation for `feedback #9` and `clients #8`; strict proof-scope audit passed and now reports per-product aggregate and public-render proof counts. Owner-approved public-render apply passed, and product pages now use approved `PUBLIC_RENDER_APPROVED=Y` proof rows with `proof_source=iblock`. |
| CSG-004 | implemented / production evidence passed | P1 | Services | `news.list/services` can render hardcoded "Расчет проекта" fallback when no service element exists. | Template fallback removed; six target service cards seeded on production; strict services audit passed with active/API count `6`. |
| CSG-005 | public rendering live / production evidence passed | P1 | Cases / Proof | Product proof readiness is not mapped to real cases/evidence; cases are not tagged by product. | Owner-approved `PRODUCT` tags and public-render flags were applied for active `cases`/`feedback` proof items. Strict production audit reports public proof readiness for all products (`platform=6`, `agents=7`, `dev=6`, `forum=5` public proof items), and HTTP source smoke passed with `proof_source=iblock`. |
| CSG-006 | product relation applied / boundary guarded | P2 | AI agents | Demo agent catalog and product `Agents` have unclear relation boundary. | Static boundary guard keeps `/agents/` as product page and `/aiagents/` as Telegram demo/prototype service route; active demo-agent rows #523, #524 and #525 are linked to product `agents` without changing public rendering. |
| CSG-007 | page-content live / fallback retired | P1 | Page sections | Many page-level content sections remain PHP partials. | `page_sections #24` and `page_blocks #25` exist; wave 1 and wave 2 seed/live/source checks passed on production. Wave 2 source HTTP passed for `/`, `/about/`, `/calculator/` and `/aiagents/` after the `/calculator/` template correction to `calculator-chat-outcome`; fallback retirement has approved owner evidence and post-deploy rechecks. |
| CSG-008 | fallback retirement deployed / rechecked | P1 | Static materials | `policies/static materials #19` can become overloaded if reused as raw page HTML storage. | Separate `page_sections/page_blocks` model is approved/applied and live for both waves; legal-only `policies` boundary remains enforced. Wave 1 and wave 2 fallback-retirement checks passed with production evidence `9/9` and owner gates `5/5`, deployed code removed the approved PHP fallback bodies, and governance forbids static fallback reintroduction. |
| CSG-009 | audit-ready | P2 | Admin/public parity | Screenshot/API mismatch observed: services admin count `4` vs API `2`, cases admin count `10` vs API `9`. | `content-storage-audit.php` reports active/total counts and optional public API counts without raw content. |
| CSG-010 | implemented | P1 | Guards | Current strict product content checks do not validate domain iblock placement. | `content:storage:governance:check`, strict product evidence `faq_source=iblock`, and target evidence validator added. |
| CSG-011 | implemented | P2 | Cache/release | Product cache tools cover product iblocks; non-product content source switches need release evidence too. | Runbook added; product cache tags include `faq`; cache-clear evidence requires product/FAQ managed tags. |
| CSG-012 | guarded / source switch passed | P2 | Boundaries | Existing narrow catalog iblocks can be polluted by page copy. | Do-not-move policy is documented and statically guarded; wave 1 and wave 2 page-content runtime uses only `page_sections/page_blocks` and rejects narrow iblocks as section storage. |

## Page Section Migration Candidates

These sections are content-managed candidates, but not candidates for existing narrow catalog iblocks:

| Page | Current PHP Section Examples | Target Storage |
|---|---|---|
| `/` | `hero`, `ecosystem`, `fit-matrix`, `commercial`, calculator preview | `page_sections/page_blocks` |
| `/services/` | `hero-entry`, `delivery-layer`, `process`, `tech` | `page_sections/page_blocks`; `services-list` remains `services` |
| `/price/` | `hero`, `features`, `workstreams`, calculator intro | `page_sections/page_blocks`; rates remain `rates` |
| `/calculator/` | result cards, product-aware estimate cards | `page_sections/page_blocks`; chat surface remains component config/runtime |
| `/contacts/` | contact cards, routing, CTA copy | `page_sections/page_blocks` plus config for legal/contact facts where needed |
| `/about/` | company history, values, vendor trust, career culture/benefits | `page_sections/page_blocks`; team/vacancies stay narrow |
| `/aiagents/` | services/process/how-it-works/bridge copy | `page_sections/page_blocks`; demo-agent cards remain `aiagents` |
| `/offer/` | product bridge, bottom CTA, catalog intro | `page_sections/page_blocks`; offer list/detail remains `offer` |

## Do Not Start Rules

1. Do not migrate generic page sections into `services`, `cases`, `team`, `vacancies` or `rates` only because those iblocks exist.
2. Do not store structured page content as raw JSON or one raw HTML blob in a single property.
3. Do not switch runtime source without fallback, cache clear and rendered smoke.
4. Do not remove, restore or change `product_blocks` FAQ fallback behavior without approved FAQ retirement/rollback evidence.
5. Do not publish product proof as customer case without Sales/Content approval and evidence status.
6. Do not add new hardcoded iblock IDs.

## Implementation Update — 05.06.2026

Local implementation added the first enforceable content-storage closure layer:

- `clients` is now part of the config registry contract.
- `PRODUCT` relation migration/check covers `feedback` and `clients`.
- Product FAQ runtime reads `faq` iblock rows and exposes `faq_source`; after approved fallback retirement, rollback is previous release redeploy or restoring the fallback code path plus product cache clear.
- `tools/content-storage-faq-migration.php` seeds product FAQ rows into `faq`, creates product FAQ sections and links existing/new product FAQ rows to them.
- `tools/content-storage-services-seed.php` seeds six target delivery service cards into `services`.
- `tools/content-storage-audit.php` provides safe aggregate count/relation/API evidence and per-product proof relation counts.
- `tools/content-storage-faq-fallback-retirement-check.mjs` validates the decision gate before removing `product_blocks.faq` fallback.
- `tools/content-storage-proof-tagging-helper.php` provides a read-only internal owner-review worksheet for proof tagging by item ID/admin edit link.
- `tools/content-storage-proof-approval-template.php` generates a no-raw-copy blank approval draft and `tools/content-storage-proof-tagging-proposal.php` generates a proposed product-tagging draft from active proof IDs for production environments without `/docs`.
- `tools/content-storage-proof-approval-check.mjs` validates the no-raw-copy owner approval JSON before proof tagging/public implementation.
- `tools/content-storage-proof-tagging-apply.php` applies approved proof `PRODUCT` tags after owner approval without changing proof copy, active flags or public rendering behavior.
- `tools/content-storage-aiagents-boundary-check.mjs` guards `/agents/` vs `/aiagents/` source and SEO boundary.
- `tools/content-storage-page-content-model-check.mjs` validates the draft structured page-content model before any page-section migration starts.
- `tools/content-storage-page-content-migration.php` dry-runs the `page_sections/page_blocks` schema and refuses `--apply` until an approved owner-gated model is supplied.
- `tools/content-storage-page-content-seed.php` dry-runs/applies wave 1 and wave 2 page-section rows into `page_sections/page_blocks`, keeps `FALLBACK_PARTIAL`, prints no raw copy and preserves existing non-empty `MIGRATION_STATUS` on updates so reruns do not demote live rows. Default remains wave 1; wave 2 is explicit through `--wave=wave_2`.
- `local/php_interface/include/page_content.php` plus `Tacticum\PageContent\Repository/Renderer` provide a live-only runtime foundation behind `page_content.source=fallback|bitrix`; it reads Bitrix only for `MIGRATION_STATUS=live` rows.
- `tools/content-storage-page-content-live-approval-template.php`, `tools/content-storage-page-content-live-approval-check.mjs` and `tools/content-storage-page-content-live-apply.php` provide a no-raw-copy, owner-gated path to promote/demote only `page_sections.MIGRATION_STATUS`; the template supports `--wave=wave_2` for scoped approvals, and the workflow does not approve `page_content.source=bitrix` or retire fallback partials.
- `tools/content-storage-page-content-fallback-retirement-template.php` and `tools/content-storage-page-content-fallback-retirement-check.mjs` provide a separate no-raw-copy owner gate for retiring PHP fallback partials after source/audit/SEO/browser evidence and admin-editability approval; the template/checker now support scoped wave 2 approvals and dynamic final rechecks, and they do not remove files or change runtime by themselves. Production owner-approved wave 1 check on 06.06.2026 passed with 9 `retire_fallback` decisions, `retirement_allowed=true`, production evidence `9/9` and owner gates `5/5`.
- `tools/content-storage-audit.php --scope=page-content --strict --json` verifies page-content registry keys, required schema and aggregate row counts/orphan-block evidence after approved apply/seed.
- Production page-content pre-apply dry-run 06.06.2026 confirmed the intended Bitrix schema plan: 2 planned iblocks (`tacticum_page_sections`, `tacticum_page_blocks`) and 25 planned properties; no seed or runtime switch happened.
- `content-storage-page-content-model-2026-06-06.approved.json` is a schema-only approved artifact: it allows empty schema creation only and explicitly does not approve page copy seed, public runtime switch or fallback retirement.
- Production page-content schema apply passed on 06.06.2026: `tacticum_page_sections #24`, `tacticum_page_blocks #25`, 25 properties created, config registry updated and strict `page-content` audit passed with `page_blocks.SECTION` linked to #24.
- Production wave 1 shadow seed passed for `/services/`, `/price/`, `/contacts/` and `/offer/`: 9 active sections, 37 active blocks and `orphan_blocks=0`. Production live-approval check and live-status apply promoted all 9 sections to `MIGRATION_STATUS=live`; pre-switch fallback HTTP source check passed with zero Bitrix markers. After the explicit environment switch to `page_content.source=bitrix`, runtime config check, page-content source HTTP check, strict page-content audit and `seo:check:prod` passed; `/services/`, `/price/`, `/contacts/` and `/offer/` now report Bitrix-rendered section counts `3/3`, `2/2`, `2/2` and `2/2` respectively.
- Production wave 2 shadow seed, scoped live approval/apply and source-marker smoke passed on 06.06.2026 for `/`, `/about/`, `/calculator/` and `/aiagents/`: strict audit reported 20 active sections, 80 active blocks, all wave 2 sections `live` and `orphan_blocks=0`; `page-content:source:http:wave2:prod` passed after correcting `/calculator/` `calculator-outcome-cards` to `calculator-chat-outcome`, and `seo:check:prod` passed.
- Chrome-capable targeted visual/action smoke passed locally for wave 2 pages; manifests: /var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-06T17-49-39-090Z/manifest.json and /var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-06T17-50-36-958Z/manifest.json.
- Wave 2 fallback-retirement owner-approved check passed on production with `retirement_allowed=true`, 11 `retire_fallback` decisions, production evidence `9/9` and owner gates `5/5`; deployed code removed the approved fallback bodies, and post-deploy runtime config, strict page-content audit, wave 1/wave 2 source HTTP checks, governance, `seo:check:prod` and Chrome-capable targeted visual/action smoke passed.
- `news.list/services` no longer synthesizes the hardcoded "Расчет проекта" service card.
- `content:storage:governance:check` and target evidence checks guard the new boundaries.
- `docs/workflow/content-storage-release-runbook-2026-06-05.md` defines cache/smoke/rollback and do-not-move policy.

The current content-storage target scope is production-closed. Page-content schema, both wave live statuses, public source switch, source/audit/SEO/browser evidence, fallback-retirement checker and owner-approved retirement evidence exist; deployed code removed the approved wave 1 and wave 2 fallback bodies. Proof public rendering also has owner approval/apply evidence and product pages now report `proof_source=iblock`.

Production evidence update 05-06.06.2026:

- FAQ migration apply created 12 product FAQ elements; strict FAQ audit passed with 3 related FAQ items for each product and `PRODUCT` relation link to `products #21`.
- Product cache clear passed with managed tags `iblock_id_21`, `iblock_id_22`, `iblock_id_23`, `iblock_id_10`.
- Strict product content check passed with `source=bitrix`, `fallback_allowed=false`, `faq_source=iblock` for `platform`, `agents`, `dev` and `forum`.
- Product source HTTP check passed for `/platform/`, `/agents/`, `/dev/`, `/forum/` with `source=bitrix`, `faq_source=iblock` and 11 product blocks each.
- Follow-up challenge found that first product FAQ seed rows had no FAQ section assignment. This did not break product runtime, but was treated as an admin UX/governance gap.
- Product FAQ section sync passed on production: root/product sections were created, 12 existing FAQ rows were linked to product sections, and strict FAQ audit reported `faq_items_without_section=0` for `platform`, `agents`, `dev` and `forum`.
- FAQ fallback retirement approval passed with `retirement_allowed=true` and owner gates `4/4`; post-deploy cache clear, strict FAQ audit, strict product content check and HTTP source smoke passed with `faq_source=iblock`.
- Product relation migration created `PRODUCT` properties on `feedback #9` and `clients #8`.
- Services seed dry-run: `created=4`, `updated=2`, `skipped=0`.
- Services seed apply: `created=4`, `updated=2`, `skipped=0`.
- Strict services audit passed: `services #12`, `elements_total=8`, `elements_active=6`, `inactive_or_filtered=2`, `PRODUCT` relation active/multiple/link_ok, public API status `200`, public API items `6`.
- `seo:check:prod` passed.
- Chrome-capable `visual:smoke:prod` passed from local environment; manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-05T18-26-21-044Z/manifest.json`.
- Chrome-capable `browser:smoke:prod` passed from local environment; manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-05T18-28-58-493Z/manifest.json`.
- Strict proof audit passed: `clients #8` active `5/5`, `feedback #9` active `3/3`, `cases #13` active `9/10`, and all checked `PRODUCT` relations link to `products #21`.
- Owner-approved proof tagging proposal/check/apply passed for 17 active proof items without storing raw proof copy; `clients` remain global because logo/trust rows have no product-specific proof context.
- Owner-approved proof public-render apply passed after aligning `cases #181` and `feedback #92` PRODUCT tags to the approval file. `PUBLIC_RENDER_APPROVED=Y` was applied to 12 `cases`/`feedback` rows; strict proof audit reports `platform public_proof_items_total=6`, `agents public_proof_items_total=7`, `dev public_proof_items_total=6`, `forum public_proof_items_total=5`, all `public_proof_render_ready=true`; product content check and HTTP source smoke passed with `proof_source=iblock`.
- AI agents tagging dry-run/apply passed on production: active rows #523, #524 and #525 now have `PRODUCT=agents`.
- Strict aiagents audit passed: `aiagents #20` active `3/3`, relation `link_ok=true`, `agents aiagents_items=3`, and `platform/dev/forum aiagents_items=0`.
- Production server `visual:smoke:prod` and `browser:smoke:prod` are environment-blocked by missing Chrome/Chromium. Chrome-capable targeted visual/action smoke passed locally for changed URLs `/platform/`, `/agents/`, `/dev/`, `/forum/`, `/aiagents/`; broad all-page local runs showed isolated CDP/tooling timeouts without network, console or page errors.

## Related Existing Gap IDs

This content-storage layer extends existing product-tech gaps:

- `ARCH-004` Product content ownership;
- `ARCH-009` Product-specific cases/proof mapping;
- `CONTENT-003` Product evidence mapping;
- `CFG-005` FAQ fallback/source behavior;
- `STACK-007` Multi-environment content/source/cache ownership;
- `CMP-007` FAQ/content wrapper coverage.
