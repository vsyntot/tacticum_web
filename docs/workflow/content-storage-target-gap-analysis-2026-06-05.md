# Content Storage Target Gap Analysis — 2026-06-05

Дата: 05.06.2026
Статус: target model challenge / implementation backlog ready
Workflow lane: Full Feature

## Purpose

Этот документ фиксирует результат challenge по целевому хранению контента в Bitrix-инфоблоках после product content migration. Цель — отделить:

- что уже является корректно редактируемым Bitrix content;
- что лежит в Bitrix, но не на правильной доменной "полке";
- что всё ещё остается PHP/template content;
- что не надо переносить в существующие инфоблоки, чтобы не создать новую свалку.

Документ является входом для implementation planning. Он не закрывает gaps сам по себе.

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
| CSG-001 | open | P1 | FAQ / Product content | Product FAQ renders from `product_blocks`, not from `faq #10`. | Product FAQ elements exist in `faq`, product pages read them first, fallback is temporary. |
| CSG-002 | open | P1 | Config / Clients | `clients #8` exists in admin, but config registry does not expose `clients`. | Add `clients` key to config/example and use helpers only. |
| CSG-003 | open | P1 | Product relations | Existing migration adds `PRODUCT` relation to `faq/cases/offer/services/aiagents`, but not `feedback/clients`. | Extend relation migration to `feedback` and `clients` where configured. |
| CSG-004 | open | P1 | Services | `news.list/services` can render hardcoded "Расчет проекта" fallback when no service element exists. | Seed service element and remove fallback rendering from template. |
| CSG-005 | open | P1 | Cases / Proof | Product proof readiness is not mapped to real cases/evidence; cases are not tagged by product. | Related real cases have product/evidence tags; readiness artifacts stay non-case content. |
| CSG-006 | open | P2 | AI agents | Demo agent catalog and product `Agents` have unclear relation boundary. | Keep separate domain meanings; optional demo-agent relation to product/use case. |
| CSG-007 | open | P1 | Page sections | Many page-level content sections remain PHP partials. | Structured page-content model and staged migration. |
| CSG-008 | open | P1 | Static materials | `policies/static materials #19` can become overloaded if reused as raw page HTML storage. | Decide: keep legal-only or create structured page sections/page blocks. |
| CSG-009 | open | P2 | Admin/public parity | Screenshot/API mismatch observed: services admin count `4` vs API `2`, cases admin count `10` vs API `9`. | Add admin/public audit report explaining inactive/filtered/missing rows. |
| CSG-010 | open | P1 | Guards | Current strict product content checks do not validate domain iblock placement. | Add content storage governance check. |
| CSG-011 | open | P2 | Cache/release | Product cache tools cover product iblocks; non-product content source switches need release evidence too. | Add cache-clear/rendered-smoke runbook for FAQ/services/clients/feedback/cases changes. |
| CSG-012 | open | P2 | Boundaries | Existing narrow catalog iblocks can be polluted by page copy. | Enforce do-not-move policy for rates/team/vacancies and generic marketing sections. |

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
4. Do not remove `product_blocks` FAQ fallback until target FAQ iblock evidence passes on production.
5. Do not publish product proof as customer case without Sales/Content approval and evidence status.
6. Do not add new hardcoded iblock IDs.

## Related Existing Gap IDs

This content-storage layer extends existing product-tech gaps:

- `ARCH-004` Product content ownership;
- `ARCH-009` Product-specific cases/proof mapping;
- `CONTENT-003` Product evidence mapping;
- `CFG-005` FAQ fallback/source behavior;
- `STACK-007` Multi-environment content/source/cache ownership;
- `CMP-007` FAQ/content wrapper coverage.

