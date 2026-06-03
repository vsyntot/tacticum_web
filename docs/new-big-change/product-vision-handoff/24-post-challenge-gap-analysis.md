# 24. Post-Challenge Gap Analysis

Дата: 02.06.2026

Статус: детализированный gap analysis по итогам повторного продуктово-технологического challenge. Документ уточняет существующие gaps из `14-gap-backlog-and-decision-register.md`, но не заменяет его как source of truth.

## Назначение

Этот документ фиксирует, что стало понятно после дополнительного challenge текущего product-first MVP:

- какие зоны уже достаточно сильны и должны быть сохранены;
- где TO BE пока выглядит зрелым только на уровне структуры и safe-copy;
- какие gaps нельзя отдавать в дизайн или разработку как закрытые;
- какие решения нужны до полноценного TO BE release.

## Source Of Truth

Canonical backlog остается в `14-gap-backlog-and-decision-register.md`.

Этот документ:

- не вводит новую систему gap ID;
- не меняет статусы gaps сам по себе;
- мапит post-challenge выводы на существующие `PB-*`, `CJM-*`, `UI-*`, `ARCH-*`, `SEO-TOBE-*`, `REL-*`;
- должен использоваться вместе с `16-gap-closure-action-register.json` and `npm run product:gaps:check`.

## Executive Verdict

Текущее решение является сильным product-first MVP, но не финальным vendor-grade TO BE сайтом.

Фактически сейчас есть:

```text
стабильный lead-generation сайт
  + product-first слой Platform / Agents / Dev / Forum
  + checked AS IS design/interaction contracts
  + safe-copy proof readiness
  + governance docs and guards
```

Целевое состояние требует большего:

```text
enterprise AI software vendor site
  + подтвержденная продуктовая таксономия
  + role-based CJM
  + decision-grade use cases
  + evidence-backed proof/status system
  + procurement/security path
  + structured lead qualification
  + approved TO BE design system
  + external release evidence
```

Главный риск: сайт уже начинает говорить как продуктовый vendor, но proof, packaging, procurement, CRM qualification, SEO canonical decisions and design system approvals еще не подтверждены внешними владельцами.

## Assets To Protect

Эти элементы являются сильной AS IS базой и должны сохраниться в TO BE, если нет отдельного migration decision:

| Asset | Why protect | Related gaps |
|---|---|---|
| `/offer/` and `/offer/<code>/` | indexed estimate/proof-like catalog and commercial route | `SEO-TOBE-003`, `REL-002` |
| `/price/` team configurator | complex staff/team selection and budget estimate flow | `UI-006`, `ARCH-008` |
| `/calculator/` chat-to-lead flow | existing qualification and estimate entry | `CJM-006`, `REL-003` |
| `tacticum:lead.cta` and `forms.js` | unified conversion contract | `UI-003`, `ARCH-003` |
| `tacticum:chat.surface` and chat contracts | reusable chat UX and prefill handoff | `UI-007`, `REL-003` |
| SEO/canonical/sitemap guards | indexability and noindex discipline | `SEO-TOBE-*`, `REL-002` |
| no-PII analytics model | safe measurement baseline | `ARCH-004`, `REL-004` |
| claim hygiene | prevents overpromising and legal/security debt | `PB-005`, `PB-006`, `UI-005` |

## Consolidated Gap Matrix

| Gap cluster | Existing IDs | Post-challenge finding | Severity | Target output |
|---|---|---|---|---|
| Vendor proof gap | `PB-005`, `PB-006`, `UI-005`, `SEO-TOBE-003` | Proof readiness is not proof. Current copy is safe, but vendor-grade trust still lacks approved evidence, sources, logos, metrics, cases and regulatory wording. | P0/P1 | Evidence matrix and proof/status UI with owner-approved public wording |
| Product taxonomy and boundary gap | `PB-001`, `PB-003`, `PB-008`, `SEO-TOBE-002` | Platform / Agents / Dev / Forum are coherent internally, but market clarity and `/agents/` vs `/aiagents/` intent are not externally validated. | P1 | Approved taxonomy, one-liners, boundary copy and canonical/compatibility decision |
| Decision-grade use case gap | `PB-002`, `PB-004`, `CJM-003` | Product pages have use-case anatomy, but scenarios need stronger pilot kits: artifacts, readiness inputs, owner responsibilities and evidence status. | P1 | Product use-case pilot kits for Platform, Agents, Dev, Forum |
| Role-based CJM gap | `CJM-001`, `CJM-002`, `CJM-004`, `CJM-005`, `CJM-006` | Current journey is still mostly product page -> general CTA. Enterprise roles need different paths and returning-lead shortcuts. | P1/P2 | Role/stage CTA taxonomy, returning-lead path and structured qualification decision |
| Design-system implementation gap | `UI-001`, `UI-002`, `UI-003`, `UI-007`, `UI-008` | AS IS contracts are checked, but TO BE tokens, component family, states and icon taxonomy are not approved implementation artifacts yet. | P1/P2 | Figma/token/component/state package mapped to AS IS contracts |
| Architecture visualization gap | `UI-004`, `ARCH-002` | Current architecture sections are text-layered, not diagram-grade decision tools for enterprise buyers. | P2 | Architecture/data-flow diagram patterns with mobile fallback |
| Content ownership gap | `ARCH-001`, `SEO-TOBE-003` | Git-owned product data is good for v1 governance, but proof/cases/FAQ may need hybrid Bitrix ownership later. | P2 | Git-only v1 approval or ADR for hybrid model |
| Lead qualification gap | `ARCH-003`, `CJM-006` | `lead_*` context and `task` fallback are safe, but CRM/upstream cannot yet consume structured product fields. | P1 | Explicit v1 fallback approval or structured upstream contract |
| Analytics evidence gap | `ARCH-004`, `REL-004` | Safe product events exist, but Metrika goals and no-PII evidence remain external. | P2/P1 release | Product funnel goal map and external evidence |
| Production evidence gap | `REL-001` - `REL-006`, `ARCH-007`, `ARCH-008` | Local guards are strong; release readiness still depends on deploy/cache, rendered SEO, forms, Metrika, Bitrix admin and upstream/CRM evidence. | P1 | Strict release sign-off without placeholders or PII |

## What Is Strong Enough For V1

These decisions can be treated as practical v1 baseline unless owners explicitly reject them:

| Area | V1 baseline |
|---|---|
| Product pages | Keep `/platform/`, `/agents/`, `/dev/`, `/forum/` as product-first public URLs |
| Product data | Keep core product facts in Git-reviewed `product_data/*.php` for v1 |
| Rendering | Keep shared renderer and `product_page_blocks/*.php` with `data-product-block` markers |
| Conversion | Reuse `tacticum:lead.cta`; do not duplicate forms in product blocks |
| Forms | Preserve current DOM/API contracts and `task` fallback |
| Analytics | Keep no-PII event model until goal map is approved |
| Stack | Keep Bitrix server render, static Tailwind and vanilla JS for current complexity |
| Claims | Keep safe-copy and proof readiness until approved evidence exists |

## What Must Not Be Treated As Closed

| Area | Why not closed |
|---|---|
| Product proof | No approved source matrix for metrics, logos, cases, regulatory or deployment claims |
| Packaging | Pilot/SaaS/on-prem/hybrid/PAK/support wording not approved as public commercial model |
| `/agents/` vs `/aiagents/` | SEO traffic, duplicate risk, canonical and redirect path not approved |
| TO BE design system | AS IS handoff is checked, but Figma variables/components/states are not final deliverables |
| CRM/upstream fields | Structured qualification has not been accepted by CRM/upstream |
| Metrika goals | Event code exists, external goal evidence pending |
| Release gates | Local checks do not replace production deploy/manual success-flow/admin/upstream evidence |

## Hard Challenge Questions

Use these questions in review sessions before design or development scope is approved.

| Question | Blocks | Owner |
|---|---|---|
| Is Platform sold as standalone product, architecture foundation, or both? | `PB-002`, `PB-007` | PM + Architect + Sales |
| Are Agents and Forum clear enough for buyers without sales explanation? | `PB-003`, `SEO-TOBE-002` | PM + SEO |
| Is `/aiagents/` a legacy demo route, SEO route, or future redirect? | `PB-008`, `SEO-TOBE-002` | SEO + PM |
| Which proof can be public now, private on request, or hidden? | `PB-005`, `PB-006`, `UI-005` | PM + Sales + Legal |
| Do current product use cases match real sales discovery conversations? | `CJM-003`, `PB-004` | PM + Sales + Content |
| Is `task` fallback enough for Sales routing in v1? | `ARCH-003`, `CJM-006` | Backend + PM + QA |
| Does the designer have enough approved input to create TO BE components? | `UI-001` - `UI-007` | Designer + Frontend + PM |
| Which release gates require authenticated/external access and cannot be closed in repo? | `REL-*`, `ARCH-007`, `ARCH-008` | QA + DevOps |

## Closure Criteria For This Post-Challenge Layer

The post-challenge layer is considered operationally useful when:

- this document is referenced from product handoff README;
- detailed companion docs exist for use cases/CJM, UX/UI/design system and architecture/stack;
- a decision backlog exists with owners, order and acceptance criteria;
- no new standalone gap ID system is introduced;
- `npm run product:gaps:check` remains green.

This document does not close product gaps. It sharpens the review and implementation path for closing them.
