# 14. Gap Backlog And Decision Register

Дата: 01.06.2026

Статус: рабочий backlog решений и гэпов после product/tech challenge. Использовать для планирования следующих спринтов и для подготовки задач дизайнеру, PM, архитектору, SEO, QA и legal/security.

## Statuses

| Status | Meaning |
|---|---|
| `open` | Требует решения или задачи |
| `in-progress` | Уже частично закрывается текущим MVP или документами |
| `blocked` | Нужны внешние доступы/evidence/owner |
| `accepted` | Риск принят явно |
| `closed` | Закрыто документально, кодом или evidence |

## Backlog Summary

| Priority | Count | Meaning |
|---|---:|---|
| P0 | 3 | Нельзя публиковать/масштабировать без решения |
| P1 | 12 | Нужно закрыть до полноценного TO BE release |
| P2 | 10 | Важные улучшения после MVP |
| P3 | 4 | Улучшения зрелости |

## Product / Strategy Gaps

| ID | Status | Priority | Gap | Decision Needed | Owner |
|---|---|---:|---|---|---|
| PB-001 | open | P1 | Product taxonomy не подтверждена рынком/sales | Утвердить финальные names, one-liners, category boundaries | PM + Sales |
| PB-002 | open | P1 | Platform может казаться абстрактным tech layer | Утвердить buying triggers and primary use cases | PM + Architect |
| PB-003 | in-progress | P1 | Agents and Forum boundaries insufficiently explicit | Product pages now include comparison/boundary copy and mutual Agents/Forum links; PM/SEO review still needed | PM + Product |
| PB-004 | open | P1 | Dev needs concrete workflow examples | Утвердить 3-5 public Dev workflows | PM + Tech Lead |
| PB-005 | blocked | P0 | Product proof lacks approved evidence | Собрать product -> claim -> source -> public wording matrix | PM + Sales + Legal |
| PB-006 | blocked | P0 | Regulatory/procurement claims unresolved | Утвердить safe wording by claim ID from register | PM + Legal + Security |
| PB-007 | open | P1 | Packaging unclear: pilot/SaaS/on-prem/PAK/support | Утвердить public packaging matrix without risky promises | PM + Sales + Architect |
| PB-008 | open | P1 | `/agents/` vs `/aiagents/` canonical unresolved | SEO/canonical/redirect/compatibility decision | PM + SEO |

## Use Case / CJM Gaps

| ID | Status | Priority | Gap | Decision Needed | Owner |
|---|---|---:|---|---|---|
| CJM-001 | in-progress | P1 | No product fit guide | Product pages now have `fit_guide` and homepage has product fit matrix; final UX/design review still needed | UX + PM |
| CJM-002 | in-progress | P1 | No security/procurement journey | Product pages now have safe-copy procurement block; legal/security evidence and final UX review still needed | PM + Security + UX |
| CJM-003 | in-progress | P1 | Use cases not fully pilotable | Product pages now have use-case anatomy with trigger/owner/input/output/limitation; PM/content/evidence review still needed | PM + Content |
| CJM-004 | open | P2 | CTA variants not role-specific enough | Define CTA by role/stage/product | PM + UX |
| CJM-005 | open | P2 | No returning-lead journey | Define quick path for architecture session / documentation request | Sales + UX |
| CJM-006 | in-progress | P1 | Product-aware lead context exists but shallow | Decide structured CRM/upstream qualification | Backend + PM + QA |

## UX / UI / Design System Gaps

| ID | Status | Priority | Gap | Decision Needed | Owner |
|---|---|---:|---|---|---|
| UI-001 | open | P1 | TO BE token source not decided | Figma variables vs JSON vs Tailwind mapping | Designer + Frontend |
| UI-002 | open | P1 | Product storytelling components not specified | Component family from `12-ux-ui-component-target.md` | Designer + PM |
| UI-003 | open | P1 | Form state spec incomplete | Full state spec for lead/product/procurement forms | Designer + QA |
| UI-004 | open | P2 | Architecture diagrams are not visual enough | Diagram patterns and mobile fallbacks | Designer + Architect |
| UI-005 | open | P2 | Proof/status UI not defined | Evidence status badges/cards/source notes | Designer + Legal + PM |
| UI-006 | open | P2 | `/price/` mobile team builder needs dedicated UX | Mobile flow decision: modal/inline/summary | Designer + Frontend |
| UI-007 | open | P2 | Chat component lacks TO BE visual/state spec | Chat bubbles, errors, handoff, long answers | Designer + Frontend |
| UI-008 | open | P3 | Icon taxonomy missing | Keep Remix or curated icon set | Designer |

## Architecture / Stack Gaps

| ID | Status | Priority | Gap | Decision Needed | Owner |
|---|---|---:|---|---|---|
| ARCH-001 | in-progress | P2 | Product content moved from page PHP arrays to shared Git data files; CMS/hybrid ownership remains open | Decide whether current shared data layer is enough or Bitrix/hybrid content model is needed | Architect + Dev + Content |
| ARCH-002 | in-progress | P2 | Product renderer split into bootstrap plus `product_page_blocks/*.php`; local component/preview boundary remains open | Decide whether current partial taxonomy is enough or Bitrix local components/previews are needed | Architect + Frontend |
| ARCH-003 | in-progress | P1 | Lead qualification has canonical backend profile with `task` fallback; CRM/upstream structured fields remain open | Approve fallback for first release or confirm structured upstream/CRM fields | Backend + PM + QA |
| ARCH-004 | in-progress | P2 | Product funnel code events added without PII; Метрика goals/evidence remain open | Confirm goals and evidence for product view/CTA/form events | PM + Analytics + QA |
| ARCH-005 | closed | P3 | Lightweight product block screenshot workflow exists via `product:block-previews`; Storybook/local component previews remain future optional scope | Use rendered block previews for AS IS handoff; revisit isolated previews only if TO BE design workflow needs them | Frontend + QA |
| ARCH-006 | accepted | P2 | CSP enforce not current target | Keep report-only until vendor/report baseline | Security |
| ARCH-007 | blocked | P1 | External release gates pending | Deploy/cache, forms, Metrika, Bitrix admin evidence | QA + DevOps |
| ARCH-008 | blocked | P1 | Staff/upstream success-flow external issues remain | Upstream recovery and CRM evidence | Backend + DevOps |

## SEO / Content Gaps

| ID | Status | Priority | Gap | Decision Needed | Owner |
|---|---|---:|---|---|---|
| SEO-TOBE-001 | open | P1 | Product SEO clusters not validated | Keyword/intent research for Platform/Agents/Dev/Forum | SEO + PM |
| SEO-TOBE-002 | open | P1 | `/agents/` and `/aiagents/` duplication risk | Canonical/redirect/compatibility decision | SEO + PM |
| SEO-TOBE-003 | open | P2 | Product-specific cases/proof not mapped | Product tags and proof hub plan | SEO + Content + Sales |
| SEO-TOBE-004 | accepted | P2 | Industry/scenario pages remain noindex | Revisit only after content/proof readiness | SEO + PM |
| SEO-TOBE-005 | open | P2 | Metadata final copy not approved | Final title/description/H1 by product | SEO + Content |

## Release / Evidence Gaps

| ID | Status | Priority | Gap | Closure Evidence | Owner |
|---|---|---:|---|---|---|
| REL-001 | blocked | P1 | Product-first automated deploy smoke pending | `release:product-first:prod-check` after deploy/cache refresh | DevOps + QA |
| REL-002 | blocked | P1 | Rendered product SEO evidence pending | Smoke manifest with `SoftwareApplication` + `FAQPage` | SEO + QA |
| REL-003 | blocked | P1 | Manual success-flow pending | Staging/controlled evidence without PII | QA + Backend/Frontend |
| REL-004 | blocked | P1 | Metrika goals pending | Goal evidence without PII params | PM/Marketing + QA |
| REL-005 | blocked | P1 | Bitrix admin smoke pending | Authenticated admin/public toolbar smoke | QA/Admin |
| REL-006 | blocked | P1 | Legacy sale aliases external inventory | Access logs/CRM aggregate report | Backend + DevOps + PM |

## Decisions Needed Before Design

1. Final product taxonomy and one-liners.
2. Product boundaries, especially Agents vs Forum.
3. Public/private proof split.
4. Packaging language: pilot, SaaS, on-prem, hybrid, PAK, support.
5. Product fit guide logic.
6. Product page component taxonomy.
7. Token source of truth.
8. Form state and CTA variant model.
9. Security/procurement path.
10. `/agents/` vs `/aiagents/` SEO decision.

## Decisions Needed Before Development

1. Product data model beyond current shared Git data files: keep Git-only or introduce Bitrix/hybrid ownership for proof/cases/FAQ.
2. Product renderer split boundary.
3. Structured lead fields and upstream/CRM readiness.
4. Product analytics taxonomy.
5. New page asset flags for any new interaction.
6. SEO/canonical/sitemap final plan.
7. ADR requirements for content model, lead contract, analytics or component architecture.
8. QA smoke list for old + new journeys.

## Recommended Sprint Packaging

### Sprint A - Product Decision Closure

Goal: close PM/Sales/Legal decisions before design.

Outputs:

- taxonomy;
- product boundaries;
- packaging matrix;
- claims status;
- `/agents/` vs `/aiagents/` decision.

### Sprint B - CJM And UX System

Goal: make the product site decision-ready.

Outputs:

- use-case cards;
- role-based CJM;
- product fit guide;
- procurement/security path;
- CTA taxonomy.

### Sprint C - Design System Target

Goal: give designer and frontend a precise system.

Outputs:

- tokens;
- product components;
- form states;
- diagrams;
- proof/status UI;
- responsive behavior.

### Sprint D - Architecture Foundation

Goal: prepare implementation for scale.

Outputs:

- product data model decision beyond current shared Git data layer;
- component boundaries;
- structured lead/analytics contract;
- ADRs if needed.

### Sprint E - Release Evidence

Goal: close production reality.

Outputs:

- deploy/cache smoke;
- rendered SEO evidence;
- manual success-flow;
- Metrika;
- Bitrix admin;
- upstream/CRM checks.

## Definition Of Done For TO BE Program

TO BE can be considered product-ready only when:

- product taxonomy and public copy are approved;
- each product has use cases, CJM and proof status;
- old commercial flows still work;
- lead qualification is useful for sales;
- analytics can measure product funnel;
- risky claims are blocked or evidenced;
- design system has tokens, components and states;
- product implementation has clear component/data boundaries;
- release sign-off is strict and external gates are closed.
