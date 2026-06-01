# Gap To Sprint Traceability

Дата: 01.06.2026

Статус: контрольный документ sprint-пакета. Использовать при planning, review и перед release sign-off, чтобы проверить, что спринты действительно закрывают актуальный AS IS / TO BE backlog, а не только реализуют новые страницы.

## Purpose

Этот документ связывает:

- product gaps из `../02-as-is-to-be-gap-analysis.md`;
- challenge IDs из `../10-product-tech-challenge.md`;
- CJM / UX / architecture targets из `../11-use-cases-and-cjm-target.md`, `../12-ux-ui-component-target.md`, `../13-architecture-components-stack-target.md`;
- backlog IDs из `../14-gap-backlog-and-decision-register.md`;
- sprint documents `sprint-00` - `sprint-08`.

Правило: gap считается закрытым только если у него есть sprint owner, deliverable, acceptance criteria and evidence. Наличие страницы или блока на сайте само по себе не закрывает product/evidence gap.

## Sprint Layering

| Layer | Sprints | Role |
|---|---|---|
| Decision baseline | 00 | Убирает P0/P1 неопределенность до дизайна и разработки |
| Product definition | 01 | Фиксирует IA, URL, messaging, CJM entry points |
| Design system | 02 | Переводит видение в реализуемые tokens/components/states |
| Technical foundation | 03 | Фиксирует content model, components, forms, analytics, SEO gates |
| MVP implementation | 04-06 | Дает homepage and product pages without breaking AS IS flows |
| Hardening | 07 | Закрывает proof/forms/SEO/analytics/claims до release |
| Release | 08 | Закрывает deploy, smoke, sign-off and post-launch ownership |

## PV Gap Coverage

| Source Gap | Meaning | Sprint(s) | Closure Evidence |
|---|---|---|---|
| `PV-001` | Positioning | 00, 04 | Approved one-liner, homepage copy, PM review |
| `PV-002` | Product taxonomy | 00, 01, 04 | Product names, boundaries, navigation spec |
| `PV-003` | Homepage | 01, 02, 04 | Homepage content model, design, rendered MVP, smoke |
| `PV-004` | Product pages | 01, 02, 05, 06 | Page briefs, product template, implemented URLs |
| `PV-005` | Platform proof | 02, 05, 07 | Architecture/proof blocks plus evidence status |
| `PV-006` | Regulatory claims | 00, 07 | Claim register approval or removal |
| `PV-007` | Case proof | 05, 06, 07 | Product proof matrix, approved cases or placeholders |
| `PV-008` | Content model | 03 | Content model decision, ADR if CMS-backed |
| `PV-009` | Navigation | 01, 04 | Header/footer spec, rendered nav, mobile smoke |
| `PV-010` | Design system | 02 | Token/component/state spec |
| `PV-011` | Interaction | 02, 04 | Interaction contracts, browser smoke |
| `PV-012` | Lead qualification | 03, 04, 05, 06, 07 | Lead contract, product context, QA form smoke |
| `PV-013` | SEO | 01, 05, 06, 07, 08 | Metadata/canonical/sitemap/SEO checks |
| `PV-014` | Analytics | 03, 07, 08 | No-PII taxonomy and goal evidence |
| `PV-015` | Dev implementation | 03, 05, 06 | Component boundaries, implementation plan |
| `PV-016` | Sales materials / SoT | 00, 07 | Claims/proof source of truth |
| `PV-017` | Tacticum Dev tone | 00, 06, 07 | Public tone guardrail, claim scan |
| `PV-018` | External references | 00, 06, 07 | Evidence review or removal |
| `PV-019` | Logos/testimonials | 00, 07 | Written approval or no public usage |
| `PV-020` | Delivery model | 00, 01, 05, 06 | Public packaging/rollout wording |

## Product / Tech Challenge Coverage

| Challenge | Sprint(s) | Required Output |
|---|---|---|
| `PTC-001` Vendor model vs service rebrand | 00, 04 | Positioning decision and homepage ecosystem framing |
| `PTC-002` Taxonomy market clarity | 00, 01 | Sales/PM taxonomy review and page messaging |
| `PTC-003` Decision-grade use cases | 01, 05, 06 | Product pages now include use-case cards with trigger, owner, pilot input, pilot output and limitation |
| `PTC-004` Role-based CJM | 01, 02, 07 | CJM map, CTA taxonomy, product funnel events; product pages now include `fit_guide`, use-case anatomy and procurement path, homepage includes first product fit matrix |
| `PTC-005` Platform buying triggers | 01, 05 | Platform fit/use-case blocks |
| `PTC-006` Agents vs Forum boundary | 00, 01, 05, 06 | Product pages now include comparison blocks and mutual Agents/Forum links; SEO/canonical decision remains separate |
| `PTC-007` Dev workflow specificity | 00, 06 | Dev workflow examples and public tone rules |
| `PTC-008` Evidence-backed proof | 00, 07 | Evidence backlog and public/private proof split |
| `PTC-009` Claims governance | 00, 07 | Claim register, safe wording, release claim scan |
| `PTC-010` Product-specific UX | 02, 05, 06 | Product-specific fit guide, use-case anatomy, comparison and procurement blocks |
| `PTC-011` Diagram-grade architecture | 02, 05 | Architecture component/design spec |
| `PTC-012` Structured lead fields | 03, 07 | Canonical backend profile + approved fallback implemented; CRM/upstream structured-field review remains |
| `PTC-013` Product funnel analytics | 03, 07, 08 | Code-level event taxonomy implemented; Metrika goals and release evidence remain |
| `PTC-014` Product content model | 03 | Shared Git data layer implemented for product pages; Bitrix/hybrid ownership decision remains |
| `PTC-015` Renderer/component split | 03 | Renderer partial split implemented; Bitrix local component/preview decision remains |
| `PTC-016` Token/component spec | 02 | TO BE design token and state specification |
| `PTC-017` Preview workflow | 03, 07 | Lightweight rendered screenshot workflow implemented through `product:block-previews`; isolated Storybook/local component previews remain optional future scope |
| `PTC-018` External release evidence | 07, 08 | Strict sign-off with real smoke evidence |

## Decision Backlog Coverage

| Backlog Area | IDs | Sprint(s) | Notes |
|---|---|---|---|
| Product / strategy | `PB-001` - `PB-008` | 00, 01, 05, 06, 07 | `PB-005` and `PB-006` are P0 blockers for public proof/claims |
| CJM | `CJM-001` - `CJM-006` | 01, 02, 05, 06, 07 | `CJM-001` is in progress through product page `fit_guide` and homepage fit matrix; `CJM-002` is in progress through procurement blocks; `CJM-003` is in progress through use-case anatomy |
| UX / UI | `UI-001` - `UI-008` | 02, 03, 07 | Token source and state spec must be known before design handoff is considered complete |
| Architecture / stack | `ARCH-001` - `ARCH-008` | 03, 07, 08 | `ARCH-001` has a shared-data first slice; `ARCH-002` has a renderer-partial first slice; `ARCH-003` has a canonical fallback first slice; `ARCH-004` has product code events; decision-register `ARCH-005` / architecture-target `ARCH-008` has rendered product block preview workflow; isolated component previews, structured upstream fields and external gates remain open where needed |
| SEO / content | `SEO-TOBE-001` - `SEO-TOBE-005` | 01, 05, 06, 07, 08 | `/agents/` vs `/aiagents/` remains the main duplication risk |
| Release / evidence | `REL-001` - `REL-006` | 07, 08 | Cannot be closed by docs; needs deploy/staging/production evidence |

## P0 / P1 Blocking Items

| ID | Why Blocking | Earliest Sprint | Latest Safe Closure |
|---|---|---|---|
| `PB-005` | Product proof cannot be published without evidence | 00 | 07 |
| `PB-006` | Regulatory/procurement claims can create legal/security risk | 00 | 07 |
| `PV-019` | Logos/testimonials need approval | 00 | 07 |
| `ARCH-003` | Lead qualification needs CRM/upstream-readable structure beyond fallback | 03 | 07 |
| `ARCH-007` / `REL-001` - `REL-006` | Production readiness requires external evidence | 07 | 08 |
| `SEO-TOBE-002` / `PB-008` | `/agents/` and `/aiagents/` duplication can create SEO/UX confusion | 01 | 07 |

## Sprint Review Rule

At each sprint review, update:

1. The sprint document `Sprint Review` section.
2. This traceability document if a gap moved sprint or changed status.
3. `../14-gap-backlog-and-decision-register.md` if a decision is made or blocked.
4. `../../../workflow/current-state.md` and `../../../workflow/gap-analysis.md` only when implementation state changed.
