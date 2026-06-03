# Sprint Execution Board

Дата: 02.06.2026

Статус: PM / lead-level execution board для TO BE product program, including post-challenge sprint wave `09-14`.

## Purpose

Этот документ дает один рабочий экран для управления программой: что делает каждый спринт, какой outcome должен появиться, какие blockers нельзя игнорировать, какие gates и проверки нужны.

## Current Reality Overlay

На 01.06.2026 сайт уже имеет безопасный product-first MVP slice:

- `/platform/`, `/agents/`, `/dev/`, `/forum/` добавлены как product pages;
- product page entries are thin orchestration files; core content lives in `local/php_interface/include/product_data/*.php`;
- product renderer bootstrap loads visual partials from `local/php_interface/include/product_page_blocks/*.php`;
- product renderer partials expose stable `data-product-block` markers for design/QA/refactor targeting, rendered smoke manifests record product block inventory, and `product:block-previews` captures per-block PNG screenshots;
- lead forms build a canonical backend qualification profile before falling back to current upstream `task` text;
- product funnel code events exist without PII; Метрика goal evidence remains pending;
- главная переупакована в ecosystem router;
- `/services/`, `/calculator/`, `/offer/`, `/price/`, `/aiagents/`, `/about/`, `/contacts/` сохранены и получили product context;
- product FAQ, rollout, proof readiness and JSON-LD schema added;
- release gates still need real external evidence.

Это не отменяет Sprint 00-03. Эти спринты остаются обязательным governance layer, потому что часть реализации была сделана safe-first, без финальной дизайн-системы, market-tested taxonomy, structured CRM fields and approved public proof.

## Program Board

| Sprint | Status | Primary Outcome | Main Blocker | Gate Focus |
|---|---|---|---|---|
| 00 | planned | Decisions and evidence baseline | Legal/security/proof owners | Legal, Security |
| 01 | planned | IA, URL, CJM and messaging spec | `/agents/` vs `/aiagents/`, product boundaries | SEO, Design |
| 02 | planned | Design system and product prototypes | Token source, proof/status UI | Design |
| 03 | planned | Implementation foundation | Content model, structured lead fields | ADR, QA early, Security |
| 04 | in-progress | Homepage and product navigation MVP | Runtime visual/browser smoke | Design, SEO, post-deploy |
| 05 | in-progress | Platform and Agents product pages | Proof evidence, Agents compatibility | SEO, Security |
| 06 | in-progress | Dev and Forum product pages | Dev public tone, Agents/Forum boundary | Legal, SEO |
| 07 | in-progress | Proof/forms/SEO/analytics hardening | External evidence, claim approval | Security, QA, SEO, Legal |
| 08 | planned | Release and post-launch handoff | Strict sign-off closure | Post-deploy, PM |

## Post-Challenge Program Board

| Sprint | Status | Primary Outcome | Main Blocker | Gate Focus |
|---|---|---|---|---|
| 09 | ready-for-owner-review | Taxonomy, claims, packaging and `/agents/` vs `/aiagents/` decisions | PM/Sales/Legal/SEO owner approvals | Legal, Security, SEO |
| 10 | ready-for-owner-review | Pilot kits, role-based CJM, CTA taxonomy and returning journey | Sales/PM confirmation of real discovery paths and Sprint 09 dependency | Design, Sales |
| 11 | ready-for-owner-review | Approved TO BE design-system package | Token source, Figma components, proof/status UI and Sprint 09/10 dependencies | Design, Frontend, QA |
| 12 | planned | Architecture, CRM/upstream and analytics decisions | CRM/upstream and Metrika evidence | ADR, Security, QA, Analytics |
| 13 | planned | Implementation-ready copy/UI/SEO/QA scope | All required upstream decisions from 09-12 | Design, SEO, QA |
| 14 | planned | Production evidence and post-launch governance | External access: deploy, Metrika, Bitrix admin, upstream | Post-deploy, QA, DevOps |

## Detailed Work Packages

### Sprint 00 - Decision And Evidence Baseline

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Product taxonomy approval | Final product names, one-liners, boundaries | PM/Sales availability | `PB-001` - `PB-004` have decision status |
| Proof and claims triage | Public/private/blocked claim table | Legal/Security/Sales | P0 claims have owner and allowed wording or removal |
| Packaging decision | Safe public pilot/SaaS/on-prem/PAK/support wording | PM + Architect | No unapproved commercial/legal promise remains |
| First release scope | Explicit release scope and deferred backlog | Product owner | Sprint 01 can build IA without ambiguity |

### Sprint 01 - IA And Messaging

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Sitemap and URL strategy | Product/service/trust URL map | Sprint 00 taxonomy | Canonical/noindex/redirect rules are written |
| `/agents/` vs `/aiagents/` decision | Compatibility, canonical or migration plan | SEO + current traffic context | `PB-008` / `SEO-TOBE-002` status is explicit |
| CJM and fit guide | Role/product/use-case journey map | `11-use-cases-and-cjm-target.md` | Homepage/product pages have decision-support model |
| Messaging hierarchy | Homepage and product page content outlines | Claims baseline | Page briefs are ready for design |

### Sprint 02 - Design System And Prototypes

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Token system | Color/type/spacing/status/focus/motion spec mapped from checked AS IS token contract | Brand/design decision | Frontend can map approved tokens to Tailwind/global CSS and keep `design:tokens:check` green |
| Product components | Component inventory and responsive behavior mapped from checked component/state contract and migration map | Sprint 01 briefs | Product hero, fit guide, use cases, diagrams, proof and CTA states exist and preserve/migrate decisions are explicit |
| Proof/status UI | Evidence-aware UI rules | Claims register | UI cannot imply unapproved metrics/logos/certifications |
| Interaction states | Form/nav/modal/accordion/chat/configurator states | Checked AS IS component/state contract and migration map | QA and frontend can verify states without guessing; `design:components:check` and `design:migration:check` stay green |

### Sprint 03 - Implementation Foundation

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Product data model | Shared Git data layer exists; CMS/hybrid decision remains | Content ownership decision | ADR decision is explicit if content moves to Bitrix/hybrid |
| Component boundary | Renderer partial split, `data-product-block` locators and rendered screenshot previews exist; isolated component previews remain optional | Design component inventory | `product_page.php` stays bootstrap-only or ADR explains next component split |
| Lead qualification | Canonical fallback exists; structured fields decision remains | CRM/upstream readiness | `lead-form-contract.md` is updated and fallback or upstream field support is explicitly accepted |
| Product analytics | No-PII code events exist; Метрика goals/evidence remain | PM/Analytics owner | Events and params are allowlisted and goal evidence is attached |
| QA/SEO plan | Smoke checklist and SEO checklist | URL/component plan | Build sprints know required verification |

### Sprint 04 - Homepage And Navigation MVP

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Ecosystem homepage | Product-first first screen and router | Sprint 01/02 or safe interim copy | Platform/Agents/Dev/Forum visible and AS IS entries preserved |
| Navigation update | Header/footer/mobile product/service model | URL strategy | Existing money pages remain reachable |
| Homepage CTA context | Product-aware lead context | Lead contract/fallback | Forms still use `data-tacticum-form` and no new PII analytics |
| Runtime smoke | Desktop/mobile no-error evidence | Local runtime or deploy | Browser/visual smoke has real manifest |

### Sprint 05 - Platform And Agents Pages

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Platform page | Platform promise, modules, rollout, proof readiness | Architecture wording | Platform has buying triggers, not only tech terms |
| Agents page | Business-function assistant story | Agents/Forum boundary | Agents is not presented as generic chatbot |
| `/aiagents/` bridge | Compatibility path | SEO decision | Legacy entry remains safe or has approved migration |
| Product CTAs/FAQ/schema | CTA context, static FAQ, safe schema | Component foundation | SEO guard and form smoke cover pages |

### Sprint 06 - Dev And Forum Pages

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Dev page | Workflow examples and governance tone | Public/private Dev decision | No workforce-reduction public claim remains |
| Forum page | Scenario + LLM + CX analytics story | Agents/Forum boundary | Forum is distinct from Agents and old bot framing |
| Product CTAs/FAQ/schema | CTA context, static FAQ, safe schema | Shared product renderer | Forms and SEO behave like Sprint 05 pages |
| Product proof placeholders | Proof readiness without fake proof | Claim register | No metrics/logos/testimonials without approval |

### Sprint 07 - Proof, Forms, SEO And Analytics Hardening

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Claim and proof freeze | Final public claim state | Legal/Sales/Security | No blocked claim is on public pages |
| Lead contract hardening | Structured fields or accepted text fallback | CRM/upstream review | Sales can use product/scenario context |
| SEO hardening | Canonical/sitemap/schema/rendered head evidence | Implemented pages | `seo:check` and rendered checks pass |
| Analytics hardening | Product funnel goals without PII | Analytics owner | Metrika/goals evidence is attached |
| Release draft | Sign-off JSON/runbook/checklist | QA/DevOps | All external pending gates are visible |

### Sprint 08 - Release, Post-Launch And Handoff

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Pre-release checks | Local/CI guard evidence | Code freeze | CSS/JS/Bitrix/SEO checks pass or risks accepted |
| Deploy and cache clear | Production release | DevOps window | Approved deploy workflow completed |
| Post-deploy smoke | Visual/browser/SEO/form evidence | Production access | Release sign-off can be strict, not assumed |
| Handoff | Sales/design/content/QA post-launch notes | Release result | Owners know what to measure and iterate |
| Post-launch backlog | Next iteration list | Analytics/Sales feedback | Deferred items are not hidden as "done" |

### Sprint 09 - Product Taxonomy, Claims And Packaging

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Taxonomy and one-liners | Approved Platform/Agents/Dev/Forum names and public descriptors | PM/Sales/Architect review | `D-01` has approved wording or explicit blocker |
| Product boundary | Agents/Forum and `/aiagents/` decision | SEO and PM review | `D-02` direction is documented |
| Proof/claims split | Evidence matrix and public/private/blocked statuses | Legal/Security/Sales evidence | `D-03` has status per claim family |
| Packaging language | Assessment/pilot/SaaS/on-prem/hybrid/PAK/support wording | PM/Sales/Architect/Legal | `D-04` public wording is approved |
| Review workbook and records | Completed `sprint-09-review-workbook.md` and `sprint-09-decision-records.md` | Sprint 09 session | Every decision has status, owner and next action |
| Approval/evidence packet | Completed `sprint-09-approval-request.md` and `sprint-09-evidence-intake.md` | Local preparation | Owners can provide approval/evidence without new doc scaffolding |

### Sprint 10 - Pilot Kits, CJM And CTA

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Product pilot kits | Trigger/owner/readiness/input/output/proof/limitation/CTA per use case | Sprint 09 taxonomy/claims | `D-05` is approved by PM/Content/Sales |
| Role paths | Economic, technical, security, functional and returning-lead CJM | Product pilot kits | `D-06` role journey map is approved |
| CTA taxonomy | Pilot, architecture, scenario, documentation-request and estimate routes | Current form contracts | No new field/payload is assumed without Sprint 12 |
| Sales routing review | Current `lead_scenario` + `task` fallback suitability | Sales/Backend/QA | Fallback accepted or structured-field scope opened |
| Review bundle | Completed `sprint-10-review-workbook.md`, `sprint-10-pilot-kit-records.md`, `sprint-10-cjm-cta-records.md`, `sprint-10-approval-request.md` | Local preparation | Owners can review D-05/D-06 without new scaffolding |

### Sprint 11 - Design System Approval

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Token source | Figma variables/token mapping | AS IS token contract | `D-07` approved by Designer + Frontend |
| Component family | Product components and states | Sprint 10 pilot kits | `D-08` component/state spec approved |
| Diagrams and proof/status | Architecture diagram and evidence status UI | Sprint 09 claims | `D-09` approved by Designer/Architect/Legal |
| Migration map update | AS IS -> TO BE preserve/migrate decisions | Component design | `design:handoff:check` remains green |
| Review bundle | Completed `sprint-11-review-workbook.md`, `sprint-11-decision-records.md`, `sprint-11-state-matrix.md`, `sprint-11-approval-request.md` | Local preparation | Owners can review `D-07` - `D-09` without new scaffolding |

### Sprint 12 - Architecture, CRM And Analytics

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Content/component architecture | Git-only v1 or ADR scope | Design/component decisions | `D-10` is explicit |
| CRM/upstream qualification | Fallback acceptance or structured field contract | Sprint 10 CTA taxonomy | `D-11` is explicit |
| Product analytics | Metrika goal map and no-PII evidence rules | Current analytics events | `D-12` is explicit |
| Release evidence model | Updated external evidence slots | QA/DevOps/Analytics | Release sign-off knows remaining blockers |

### Sprint 13 - Implementation Readiness

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Copy packet | Approved product copy and pilot kit rendering plan | Sprints 09-10 | Copy traces to approved decisions |
| UI implementation plan | Token/CSS/component task split | Sprint 11 | Selectors and states are preserved or scoped |
| SEO plan | Metadata/canonical/schema update matrix | Sprint 09 SEO decisions | SEO impact is known before code |
| QA matrix | Smoke matrix for AS IS + TO BE journeys | Sprints 11-12 | QA can verify old and new flows |

### Sprint 14 - Release Evidence And Governance

| Work Package | Output | Depends On | Done When |
|---|---|---|---|
| Deploy/cache smoke | Product-first production evidence | Implementation complete | `REL-001` evidence exists or blocker recorded |
| Rendered SEO evidence | Product SEO manifest | SEO implementation | `REL-002` evidence exists or blocker recorded |
| Manual success-flow | Form/chat/price/prefill evidence without PII | QA/staging/prod access | `REL-003` evidence exists or blocker recorded |
| Metrika/admin/upstream | External evidence | External credentials/access | `REL-004` - `REL-006`, `ARCH-008` evidence or blockers recorded |
| Strict sign-off | Final closure or accepted risks | All evidence | `gaps:known:strict` only used after external gates close |

## Release Readiness Checklist

| Area | Required Before Release |
|---|---|
| Product | Taxonomy, product boundaries, packaging and first release scope approved |
| Claims | P0/P1 claims allowed, rewritten, private-only or removed |
| UX/UI | Designs cover desktop/mobile and all interactive states |
| Implementation | AS IS flows preserved, product pages implemented, no inline JS/CSS debt introduced |
| Forms | Product context works and lead contract/fallback is documented |
| Analytics | Product funnel events/goals contain no PII |
| SEO | Canonical, sitemap, metadata, schema and rendered head verified |
| QA | Browser/visual smoke and manual success-flow evidence attached |
| Release | Sign-off closed or explicit accepted risks recorded |
