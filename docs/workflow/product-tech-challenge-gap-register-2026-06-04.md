# Product Tech Challenge Gap Register — 2026-06-04

Дата: 04.06.2026

Статус: актуальный рабочий register по итогам технологического challenge текущего product-first решения. Документ фиксирует 100% выявленных gaps and tasks по UX, UI, Architecture, Components, Stack, Security, Content, SEO and Release.

## Назначение

Этот register нужен, чтобы выводы challenge не остались устной оценкой. Он переводит наблюдения в управляемые задачи с owner, lane, gates and closure evidence.

Документ не заменяет:

- `docs/new-big-change/product-vision-handoff/14-gap-backlog-and-decision-register.md`;
- `docs/new-big-change/product-vision-handoff/16-gap-closure-action-register.json`;
- `docs/workflow/gap-analysis.md`.

Он является текущим 2026-06-04 challenge layer and должен использоваться вместе с execution roadmap `product-tech-challenge-execution-roadmap-2026-06-04.md`.

## Source Evidence

| Source | Challenge Signal |
|---|---|
| `local/php_interface/include/tacticum_config.php` | `products.source=bitrix`, TTL `300`, REST origins, no IP allowlist, explicit config vs defaults |
| `local/php_interface/include/product_content.php` | Bitrix product source, cache key, fallback/source mode, JSON payload handling, diagnostics |
| `local/php_interface/include/product_page_blocks/page.php` | Fixed product page block order, CTA placement, renderer-level page composition |
| `local/rest/tacticum_form.php` | canonical lead profile plus `task` fallback, product context not structured upstream |
| `local/templates/tacticum/header.php` | CSP default `report-only`, vendor allowances, inline tolerance |
| `local/templates/tacticum/js/forms.js` | form orchestration and product events in one browser module |
| `local/templates/tacticum/js/chat-agent.js` | multi-surface chat orchestration in one browser module |
| `local/templates/tacticum/components/bitrix/news.list/price/script.js` | large mixed legacy/new team-builder script with fallback UI generation |
| `local/templates/tacticum/styles/global.css` | large global CSS, repeated hero/page patterns, design-system drift risk |
| Product handoff docs `24` - `28` | older post-challenge baseline that needs 2026-06-04 runtime/config refresh |

## Statuses

| Status | Meaning |
|---|---|
| `open` | Confirmed gap; needs task/decision |
| `in-progress` | Partially addressed by current MVP/docs/guards but not mature enough to close |
| `blocked` | Requires external owner approval, production evidence, CRM/upstream data, legal/security input or design work |
| `accepted-monitor` | Deliberately accepted for now; must be monitored and revisited on trigger |
| `closed` | Do not use here unless evidence is already attached in this document |

## Priority Rules

| Priority | Meaning |
|---|---|
| `P0` | Can make public product promise unsafe, blank/invalid, legally risky or impossible to operate |
| `P1` | Required before mature TO BE release or major visual/product implementation |
| `P2` | Important scale/hardening backlog after near-term release decisions |
| `P3` | Maturity/nice-to-have |

## Complete Gap And Task Register

### Config / Runtime / Content Source

| ID | Status | Priority | Lane | Gap | Required Task | Owner | Gate | Closure Evidence |
|---|---|---:|---|---|---|---|---|---|
| CFG-001 | in-progress | P0 | Security / Integration | `products.source=bitrix` makes public product pages dependent on Bitrix content quality without runtime fallback. | Add fail-fast policy: if Bitrix product payload is non-renderable, release should fail and runtime should expose safe degraded state instead of silently rendering weak content. | Architect + Backend + QA | ADR/cache/deploy gate | Local fail-fast policy and local negative fixture guard are documented in `product-content-schema-contract.md`; target strict content check and negative live Bitrix fixture remain required. |
| CFG-002 | in-progress | P0 | Full Feature | Product block JSON/content schema is not strict enough for editor-owned Bitrix content. | Define typed schema for `products`, `product_blocks`, `product_use_cases`; validate required fields, allowed block types, safe URLs and text lengths. | Architect + Content + Backend | ADR gate if schema becomes shared contract | `product-content-schema-v1.json`, `product:content:schema:self-test`, `product:content:schema:negative-test` and `product:content:schema:check` validate guard behavior and Git seed/fallback data; `product:content:check:strict` validates live assembled Bitrix page schema and `product:content:target-evidence:check` validates saved strict JSON evidence, target run evidence pending. |
| CFG-003 | in-progress | P1 | Security / Integration | Product cache key does not include source mode, schema version or template/render version. | Add cache version strategy or documented cache clear trigger for source/schema/template changes. | Backend + DevOps | Cache/deploy gate | Cache key now includes product schema version, source mode and product iblock IDs; target cache-clear dry-run evidence still pending. |
| CFG-004 | in-progress | P1 | Full Feature | Runtime config check distinguishes explicit/default values, but real `tacticum_config.php` can still drift from example/default expectations. | Add owner-facing config sync checklist for explicit product/security/content keys and make defaults visible in release sign-off. | DevOps + Backend | Release gate | Sprint 19 decision pack documents safe `config:runtime:check` evidence rule; target release evidence and owner approval pending. |
| CFG-005 | in-progress | P2 | Fast Fix | `content.faq_section_fallback_ids` may be default-only or absent in real config, so FAQ fallback behavior can surprise editors. | Decide whether production config must explicitly set FAQ section fallbacks or whether default-only is accepted. | Content + Backend | Config gate | Sprint 21 draft in `product-frontend-component-hardening-decision-2026-06-04.md` records semantic-first FAQ lookup and explicit/default config rule; Content + Backend approval and runtime config evidence pending. |
| CFG-006 | in-progress | P2 | Security / Integration | `rest.allowed_ips` and `trusted_proxies` are empty; acceptable for public forms, weak for future sensitive flows. | Define which future endpoints require IP allowlist, auth, signed token or same-origin only controls. | Security + Backend + DevOps | Security gate | Sprint 22 draft in `product-security-release-legacy-closure-decision-2026-06-04.md` defines endpoint sensitivity classes and IP/proxy usage rules; Security/DevOps approval pending. |

### UX / Product Journey

| ID | Status | Priority | Lane | Gap | Required Task | Owner | Gate | Closure Evidence |
|---|---|---:|---|---|---|---|---|---|
| UX-001 | in-progress | P1 | Full Feature | Product journey is still mostly linear page reading, not role-based enterprise decision flow. | Define role journeys for CEO/business owner, CIO/CTO, CISO/security, procurement and product owner. | PM + UX + Sales | Design gate | Role CJM draft in `product-cjm-cta-crm-qualification-decision-2026-06-04.md`; PM/UX/Sales approval pending. |
| UX-002 | in-progress | P1 | Full Feature | CTA taxonomy is product-aware but not role/stage-aware enough. | Define CTA variants by product, buyer role and stage: pilot, architecture review, procurement/security session, documentation request. | PM + UX + Sales | Design + contract gate | CTA taxonomy draft in `product-cjm-cta-crm-qualification-decision-2026-06-04.md`; no payload changes made, approval pending. |
| UX-003 | in-progress | P2 | Full Feature | Returning-lead journey is missing. | Add a path for users who already had contact: architecture session, proof/doc request, pilot refinement. | PM + Sales + UX | Design gate | Returning-lead path draft in `product-cjm-cta-crm-qualification-decision-2026-06-04.md`; Sales routing decision pending. |
| UX-004 | blocked | P1 | Full Feature | `/agents/` and `/aiagents/` create product/SEO ambiguity. | Decide canonical, redirect/compatibility, copy boundary and sitemap treatment. | PM + SEO + Product | SEO gate | Draft decision in `product-taxonomy-seo-packaging-decision-2026-06-04.md` recommends `/agents/` as product URL and `/aiagents/` as self-canonical compatibility/service route; PM/SEO approval pending. |
| UX-005 | in-progress | P1 | Full Feature | `/price/` can still frame the company as staff augmentation instead of product ecosystem packaging. | Reframe `/price/` around product implementation packages while preserving team-builder utility. | PM + Sales + UX | Design + SEO gate | Draft route intent in `product-taxonomy-seo-packaging-decision-2026-06-04.md`; current `/price/` already has product workstreams and preserves team-builder, design/PM approval pending. |
| UX-006 | blocked | P0 | Full Feature | Proof readiness is not proof; enterprise trust lacks approved metrics, logos, cases and claim sources. | Build public/private proof matrix and decide what can be shown publicly, hidden, or provided under request/NDA. | PM + Sales + Legal | Legal/proof gate | Claim-source matrix with public wording and blocked claims. |
| UX-007 | in-progress | P1 | Full Feature | Security/procurement path exists as safe-copy block, but not as guided workflow. | Define procurement/security review journey: data flow, integration, access, logs, ownership, document request and handoff. | PM + Security + UX | Security + Design gate | Approved journey and checklist without risky certification/registry promises. |
| UX-008 | in-progress | P1 | Full Feature | Use cases are pilotable in copy, but not yet full pilot kits. | Define pilot kits per product with input artifacts, owner responsibilities, expected output, limits and evidence status. | PM + Content + Sales | PM/content gate | Product pilot kit draft in `product-cjm-cta-crm-qualification-decision-2026-06-04.md`; owner approval pending. |
| UX-009 | in-progress | P2 | Full Feature | Product fit guide is static and not personalized by role, industry or constraint. | Decide whether fit guide remains static v1 or becomes interactive/guided. | PM + UX | Design gate if interactive | Draft recommends static v1 and defines interactive revisit trigger; PM/UX approval pending. |
| UX-010 | in-progress | P2 | Full Feature | Post-submit/user expectation UX is generic. | Define success states and next-step copy by product/scenario without exposing CRM internals. | PM + UX + QA | Design + QA gate | Success-state copy matrix drafted in `product-cjm-cta-crm-qualification-decision-2026-06-04.md`; implementation waits for Sprint 20 state spec. |

### UI / Design System

| ID | Status | Priority | Lane | Gap | Required Task | Owner | Gate | Closure Evidence |
|---|---|---:|---|---|---|---|---|---|
| UI-001 | blocked | P1 | Full Feature | TO BE token source is not approved. | Decide Figma variables vs JSON token source vs Tailwind mapping and update design handoff. | Designer + Frontend | Design gate | Hybrid token-source recommendation drafted in `product-to-be-design-system-decision-2026-06-04.md`; Designer/Frontend approval pending. |
| UI-002 | in-progress | P1 | Full Feature | UI is card-heavy with large radius and marketing-style composition in places. | Define enterprise product density rules: card usage, radius, spacing, panels, proof blocks, tables and comparison layouts. | Designer + PM | Design gate | Enterprise density/radius/card policy drafted in `product-to-be-design-system-decision-2026-06-04.md`; design approval pending. |
| UI-003 | in-progress | P2 | Full Feature | Palette/gradient usage can read as one-note blue product marketing rather than mature software UI. | Define restrained palette expansion and gradient usage policy. | Designer | Design gate | Palette/gradient boundaries drafted in `product-to-be-design-system-decision-2026-06-04.md`; final token update pending. |
| UI-004 | in-progress | P2 | Full Feature | Hero/page visual system has repeated patterns and CSS definitions. | Define hero pattern family for product, service, proof and operational pages; remove duplicate CSS only in implementation scope. | Designer + Frontend | Design + CSS gate | Hero/page taxonomy drafted in `product-to-be-design-system-decision-2026-06-04.md`; implementation pending. |
| UI-005 | blocked | P1 | Full Feature | Proof/status UI is not defined. | Design status badges/cards/source notes for public, private, pending and blocked evidence. | Designer + Legal + PM | Legal + Design gate | Proof/status state rules drafted in `product-to-be-design-system-decision-2026-06-04.md`; Legal/PM/Designer approval pending. |
| UI-006 | in-progress | P2 | Full Feature | Architecture/procurement sections are text-heavy, not diagram-grade. | Design architecture/data-flow diagrams with mobile fallbacks and safe labels. | Designer + Architect | Design gate | Diagram patterns and safe labels drafted in `product-to-be-design-system-decision-2026-06-04.md`; Architect/Designer approval pending. |
| UI-007 | in-progress | P1 | Full Feature | Form/modal/CTA state spec is incomplete for TO BE implementation. | Specify normal, error, loading, consent, CSRF/origin failure, success and retry states. | Designer + QA + Frontend | Design + QA gate | Form/modal/CTA state matrix drafted in `product-to-be-design-system-decision-2026-06-04.md`; design implementation pending. |
| UI-008 | in-progress | P2 | Full Feature | Chat visual/state spec is incomplete across hero, calculator and price surfaces. | Specify chat bubble, long answer, error, retry, prefill, handoff and mobile behavior. | Designer + Frontend + QA | Design + QA gate | Chat state matrix drafted in `product-to-be-design-system-decision-2026-06-04.md`; approval pending. |
| UI-009 | in-progress | P1 | Full Feature | `/price/` team-builder mobile flow remains a high-risk UX surface. | Decide modal vs inline vs summary-first mobile flow and state transitions. | Designer + Frontend + QA | Design gate | Summary-first mobile direction drafted in `product-to-be-design-system-decision-2026-06-04.md`; design/QA approval pending. |
| UI-010 | in-progress | P3 | Full Feature | Icon taxonomy is not defined. | Decide whether to keep RemixIcon, migrate to curated icon set or restrict icon usage by component type. | Designer + Frontend | Design gate | Draft recommends keeping RemixIcon v1 with curated usage taxonomy; approval pending. |

### Architecture / Data / Security

| ID | Status | Priority | Lane | Gap | Required Task | Owner | Gate | Closure Evidence |
|---|---|---:|---|---|---|---|---|---|
| ARCH-001 | in-progress | P0 | Full Feature | Bitrix product content is target source of truth, but editor governance and validation lifecycle are not complete. | Define product content lifecycle: draft, review, publish, cache clear, smoke, rollback. | PM + Content + Backend + QA | ADR/deploy gate | Local lifecycle baseline added in `product-content-schema-contract.md`; owner workflow approval and live publish evidence pending. |
| ARCH-002 | in-progress | P1 | Full Feature | Product renderer block order is hardcoded and not per-product configurable. | Decide whether fixed order remains v1 baseline or introduce ordered block config with guardrails. | Architect + PM + Frontend | ADR gate if config model changes | Sprint 21 draft keeps fixed v1 block order and defines ADR triggers for configurable order; Architect/PM/Frontend approval pending. |
| ARCH-003 | in-progress | P1 | Full Feature | Product renderer can hide content diagnostics from editors/reviewers. | Surface product content diagnostics in checks and optionally admin/release evidence. | Backend + QA + Content | QA gate | `product:content:check:strict` now prints per-product `schema_issues`; `product:content:check:strict:json` provides safe release evidence format; `product:content:target-evidence:check` validates saved evidence; target run still pending. |
| ARCH-004 | in-progress | P1 | Full Feature | Product content ownership across product facts, proof, cases and FAQ is not fully settled. | Decide what lives in Bitrix, Git fallback, existing iblocks or private evidence docs. | Architect + Content + PM | ADR gate | Ownership matrix and ADR update if model changes. |
| ARCH-005 | blocked | P0 | Security / Integration | Lead qualification is still sent upstream mostly as text fallback, not structured CRM fields. | Confirm v1 fallback with Sales or scope structured upstream/CRM field contract. | Backend + PM + Sales + QA | Security / Integration gate | Sprint 19 decision draft recommends keeping current text fallback; Sales/upstream/Security approval or structured field scope still pending. |
| ARCH-006 | in-progress | P1 | Full Feature | Product analytics are safe but shallow. | Define product funnel goal map: view, CTA, scenario select, submit, success, chat handoff, prefill. | PM + Analytics + QA | Analytics gate | No-PII funnel goal map drafted in `product-cjm-cta-crm-qualification-decision-2026-06-04.md`; Metrika/Analytics approval pending. |
| ARCH-007 | accepted-monitor | P1 | Security / Integration | CSP is report-only and includes inline/vendor tolerances. | Keep report-only until vendor baseline and inline cleanup plan are approved; define enforce trigger. | Security + Frontend | Security gate | Sprint 22 draft defines enforce prerequisites; Sprint 23 monitoring package keeps report-only visible with Security/Frontend owner and concrete revisit triggers. |
| ARCH-008 | in-progress | P2 | Security / Integration | Rate limit policy is generic and not severity-tiered enough for future sensitive endpoints. | Define endpoint rate-limit classes and stronger controls for document/procurement/private flows. | Security + Backend | Security gate | Sprint 22 draft defines rate classes for public read, health, lead, chat, staff, prefill, resolver, sensitive and legacy endpoints; numeric thresholds pending. |
| ARCH-009 | blocked | P1 | Full Feature | Product-specific cases/proof are not mapped to product taxonomy. | Map cases/offers/FAQ/services to products and evidence status. | Content + Sales + SEO | Content/SEO gate | Evidence map draft in `product-taxonomy-seo-packaging-decision-2026-06-04.md`; actual content tagging and owner approval pending. |
| ARCH-010 | blocked | P1 | Full Feature | Product SEO clusters and canonical model are not externally validated. | Validate keywords/intent, `/agents/` vs `/aiagents/`, sitemap/canonical and metadata. | SEO + PM | SEO gate | SEO/canonical decision draft in `product-taxonomy-seo-packaging-decision-2026-06-04.md`; keyword/intent validation pending. |
| ARCH-011 | open | P2 | Security / Integration | Product content deploy/switch automation remains manual. | Decide automation level for cache clear, source switch, strict checks and rollback across environments. | DevOps + Backend | Deploy/cache gate | Automation decision and tested runbook. |
| ARCH-012 | in-progress | P1 | Security / Integration | Release evidence is safer now, but all future evidence docs must keep no-PII discipline. | Keep evidence skeletons aggregate/safe; extend templates when new flows are added. | QA + PM + Security | Release gate | Sign-off check rejects raw PII-like evidence and now supports `csp-enforce`, `sensitive-endpoint-access`, `endpoint-risk-class` and `legacy-final-mode` with safe evidence validators. |

### Components / Frontend Modules

| ID | Status | Priority | Lane | Gap | Required Task | Owner | Gate | Closure Evidence |
|---|---|---:|---|---|---|---|---|---|
| CMP-001 | in-progress | P2 | Full Feature | Product blocks are PHP partials, not isolated components with formal parameter contracts. | Decide which blocks stay partials and which graduate to local Bitrix components. | Architect + Frontend | ADR gate if component API pattern changes | Sprint 21 draft keeps PHP partials by default and defines promotion criteria: reuse, independent state, separate cache, complex params, fixture need or owner boundary; approval pending. |
| CMP-002 | in-progress | P2 | Full Feature | Product block previews exist, but isolated state/testing harness is limited. | Decide whether screenshot previews are enough or add component-level preview fixtures. | Frontend + QA + Designer | Design/QA gate | Sprint 21 draft keeps `product:block-previews` as v1 and scopes isolated fixtures to high-risk behavior-bearing components; QA/Designer approval pending. |
| CMP-003 | in-progress | P1 | Full Feature | `tacticum:lead.cta` supports product context but not full role/stage CTA taxonomy. | Extend component parameter model only after CTA decision; preserve existing form contract. | Frontend + Backend + PM | Contract gate | Sprint 19 draft keeps existing component params for v1 and blocks extension until CTA/CRM approval. |
| CMP-004 | in-progress | P1 | Full Feature | `/price/` script is large mixed-rollout code with legacy selectors, fallback modal, state and payload logic in one file. | Decompose into team builder state, DOM bindings, modal, payload adapter and smoke-friendly selectors. | Frontend + QA | Design/QA gate | Sprint 21 draft defines contract-preserving split order for DOM/state/filters/presets/modal/payload/analytics and requires clean `browser:smoke:price` before/after implementation. |
| CMP-005 | in-progress | P2 | Full Feature | `forms.js` centralizes many flows in one module. | Extract form adapter, product context normalization, analytics hooks and submit transport if future changes grow. | Frontend + QA | Contract gate | Sprint 21 draft defines optional split by selectors, validation, payload, transport, UI state and analytics while preserving payload, consent, CSRF and response model. |
| CMP-006 | in-progress | P2 | Full Feature | `chat-agent.js` owns hero, calculator, price, prefill and lead handoff behaviors. | Split by chat surface adapter and shared transport/state only when modifying chat behavior. | Frontend + QA | Integration gate if payload changes | Sprint 21 draft defines optional split by transport, state, renderer, hero/light surfaces, prefill and analytics while preserving `group_id`, prefill and handoff contracts. |
| CMP-007 | in-progress | P3 | Fast Fix | FAQ/content list wrappers have limited behavioral test coverage. | Add static/HTML smoke coverage for semantic section lookup and content wrapper parameters. | Frontend + QA | QA gate | Sprint 21 draft maps semantic found, fallback used and intentional empty FAQ states to future guard/smoke coverage; implementation pending. |
| CMP-008 | in-progress | P2 | Full Feature | AS IS to TO BE design traceability is spread across several docs. | Link design token, component state, migration map and this register in one handoff index. | Designer + Frontend + PM | Design gate | Traceability table drafted in `product-to-be-design-system-decision-2026-06-04.md` and linked from design handoff; owner review pending. |

### Stack / Build / Quality

| ID | Status | Priority | Lane | Gap | Required Task | Owner | Gate | Closure Evidence |
|---|---|---:|---|---|---|---|---|---|
| STACK-001 | accepted-monitor | P3 | Full Feature | Bitrix SSR + vanilla JS is not a modern SPA stack, but it is appropriate for current product pages. | Keep stack; revisit only if interactions become application-grade rather than content/lead-gen. | Architect + Frontend | ADR gate if changed | Sprint 23 monitoring package keeps Bitrix SSR + vanilla JS as accepted baseline and defines app-like/authenticated/offline/complex-state triggers for ADR. |
| STACK-002 | in-progress | P1 | Full Feature | Frontend JS lacks a clear module/test strategy for growing interactions. | Define ES module policy, test/smoke boundaries and shared helpers for forms/chat/price. | Frontend + QA | QA gate | Sprint 21 draft keeps vanilla JS + Bitrix Asset as v1 policy, requires ADR for ES modules/bundler/framework, and maps required checks by change type. |
| STACK-003 | in-progress | P1 | Full Feature | CSS is consolidated but still large and globally scoped. | Define token pipeline, CSS sectioning policy and component CSS rules before TO BE restyle. | Frontend + Designer | Design/CSS gate | CSS/token pipeline policy drafted in `product-to-be-design-system-decision-2026-06-04.md`; no CSS implementation yet. |
| STACK-004 | in-progress | P1 | Full Feature | Product content schema guard is not yet a first-class CI/release check. | Add or scope `product:content:schema:check` and include it in relevant release gates. | Backend + QA | CI/release gate | Local schema self-test, negative fixture, target evidence validator self-test and positive seed/fallback checks are added; `product:content:safety:check` is included in PR check, deploy lifecycle guard and `release:product-first:prod-check`; target PHP/Bitrix evidence still pending. |
| STACK-005 | in-progress | P2 | Full Feature | Browser smoke coverage is strong, but future UI states need deterministic fixtures. | Add fixture-driven smoke for product blocks, form states, chat states and price states when UI changes land. | QA + Frontend | QA gate | Sprint 21 draft maps product, `/price/`, forms, chat and FAQ wrapper fixture coverage; QA fixture approval and implementation pending. |
| STACK-006 | accepted-monitor | P2 | Fast Fix | Asset/dependency hygiene is controlled but must remain guarded. | Keep `template-styles:check`, `css:check`, `js:check`, design guards and no runtime Tailwind policy. | Frontend | PR/deploy gate | Sprint 23 monitoring package keeps current asset guard model and defines build/icon/token/CSS architecture triggers for Frontend/QA review. |
| STACK-007 | open | P2 | Security / Integration | Multi-environment product source/cache/deploy ownership is still manual. | Decide environment ownership and automation for local/staging/prod product content checks. | DevOps + Backend + QA | Deploy gate | Environment matrix and runbook updates. |

### SEO / Content / Claims

| ID | Status | Priority | Lane | Gap | Required Task | Owner | Gate | Closure Evidence |
|---|---|---:|---|---|---|---|---|---|
| CONTENT-001 | blocked | P1 | Full Feature | Public packaging is not approved: pilot, SaaS, on-prem, hybrid, PAK, support. | Approve packaging matrix and decide what is public, private, blocked or NDA-only. | PM + Sales + Legal + Architect | Legal/PM gate | Packaging safety matrix drafted in `product-taxonomy-seo-packaging-decision-2026-06-04.md`; Legal/Sales approval pending. |
| CONTENT-002 | blocked | P0 | Full Feature | Claims matrix lacks approved evidence for mature vendor proof. | Update proof/claims matrix with source, confidence, owner, public wording and blocked wording. | PM + Sales + Legal | Legal gate | Approved claim-source matrix. |
| CONTENT-003 | in-progress | P1 | Full Feature | Product-specific cases/proof mapping is incomplete. | Tag existing cases/offers/FAQ/services by product and evidence type. | Content + SEO + Sales | Content/SEO gate | Product evidence map draft in `product-taxonomy-seo-packaging-decision-2026-06-04.md`; content tagging backlog pending. |
| CONTENT-004 | in-progress | P2 | Full Feature | Final product metadata copy is not owner-approved. | Approve title, description, H1 and intro copy per product after keyword review. | SEO + Content + PM | SEO gate | Metadata sheet draft in `product-taxonomy-seo-packaging-decision-2026-06-04.md`; keyword review and owner approval pending. |
| CONTENT-005 | blocked | P1 | Full Feature | Product taxonomy still needs Sales/market approval. | Validate product names, one-liners, category boundaries and buyer triggers with Sales. | PM + Sales + Architect | PM/Sales gate | Taxonomy v1 recommendation drafted in `product-taxonomy-seo-packaging-decision-2026-06-04.md`; PM/Sales approval pending. |

### Security / Release / Legacy

| ID | Status | Priority | Lane | Gap | Required Task | Owner | Gate | Closure Evidence |
|---|---|---:|---|---|---|---|---|---|
| SEC-001 | accepted-monitor | P2 | Security / Integration | Lead form endpoint allows trusted browser-source CSRF fallback in some cases. | Keep only while same-origin/origin model is accepted; revisit if endpoint sensitivity increases. | Security + Backend + QA | Security gate | Sprint 21 draft lists sensitivity triggers; Sprint 23 monitoring package keeps the public CSRF posture visible with Security/Backend/QA owner and reopen rules. |
| SEC-002 | in-progress | P1 | Security / Integration | Future procurement/document request flows may require stronger auth than public form controls. | Define auth/access model before any gated docs, procurement packets or private proof downloads. | Security + PM + Backend | Security/Legal gate | Sprint 22 draft blocks direct public private docs and defines request-only v1 plus site-hosted access requirements; Security/PM/Legal approval pending. |
| SEC-003 | in-progress | P2 | Security / Integration | CSP enforce path is not tied to concrete inline cleanup tasks. | Inventory inline allowances and vendor domains; sequence cleanup before enforce. | Security + Frontend | Security gate | Sprint 22 draft sequences report-only collection, inline inventory, vendor narrowing, report endpoint ownership, staging enforce smoke and ADR/sign-off updates. |
| REL-001 | blocked | P1 | Security / Integration | Legacy sale alias external inventory remains a dated external obligation. | Repeat access-log inventory after 30.06.2026 and collect CRM/upstream source report. | Backend + DevOps + PM | Release/legacy gate | Sprint 22 draft records final-mode matrix; full-window access-log aggregate and CRM/upstream source report after `2026-06-30` remain required. |
| REL-002 | in-progress | P1 | Full Feature | Product release evidence is strong for current launch but future product changes need same strictness. | Add this register to release planning; require affected gaps and smoke evidence for future product work. | PM + QA | Release gate | Sprint 22 sign-off checker now carries security-sensitive future gates; Sprint 23 monitoring package adds issue snippet, review cadence and trigger rules for future product implementation. |

## Minimum Closure Bundles

### Bundle A - Public Promise Safety

Must close before publishing stronger vendor claims:

- `UX-006`
- `CONTENT-002`
- `CONTENT-001`
- `ARCH-009`
- `UI-005`
- `SEC-002` if private proof/document flow is introduced

### Bundle B - Product Source Safety

Must close before relying further on editor-owned Bitrix product content:

- `CFG-001`
- `CFG-002`
- `CFG-003`
- `ARCH-001`
- `ARCH-003`
- `STACK-004`

### Bundle C - Sales Qualification

Must close before promising sales automation or segmentation:

- `ARCH-005`
- `UX-002`
- `UX-010`
- `CMP-003`
- `ARCH-006`

### Bundle D - TO BE UI Implementation

Must close before large visual redesign:

- `UI-001`
- `UI-002`
- `UI-003`
- `UI-005`
- `UI-007`
- `UI-009`
- `STACK-003`
- `CMP-008`

### Bundle E - Frontend Maintainability

Must close before large interaction changes:

- `CMP-004`
- `CMP-005`
- `CMP-006`
- `STACK-002`
- `STACK-005`

Sprint 21 draft `product-frontend-component-hardening-decision-2026-06-04.md` provides the local decision package for this bundle. The bundle remains non-closed until Frontend/QA approval exists and implementation smoke evidence is attached for any runtime split.

### Bundle F - Security, Release And Legacy Closure

Must close before private proof/document flows, CSP enforce or legacy alias final mode:

- `CFG-006`
- `ARCH-007`
- `ARCH-008`
- `ARCH-012`
- `SEC-002`
- `SEC-003`
- `REL-001`
- `REL-002`

Sprint 22 draft `product-security-release-legacy-closure-decision-2026-06-04.md` provides the local decision package for this bundle. The bundle remains non-closed until Security/Backend/DevOps/QA/PM/Legal approvals exist and external legacy inventory is captured after `2026-06-30`.

### Bundle G - Accepted-Risk Monitoring

Must stay visible across future product work:

- `STACK-001`
- `STACK-006`
- `SEC-001`
- `ARCH-007`
- `REL-002`

Sprint 23 draft `product-accepted-risk-monitoring-decision-2026-06-04.md` provides the monitoring package for this bundle. These items remain accepted-monitor or in-progress until a revisit trigger opens implementation, ADR, Security / Integration or release scope with evidence.

## ADR Triggers

Create or update ADR if any task decides to:

- change product content schema or publish workflow;
- add ordered/configurable product blocks;
- change product cache key/versioning strategy;
- promote product partials into a reusable local component API;
- send structured lead/product fields upstream;
- introduce private document/proof access;
- enforce CSP;
- alter canonical/redirect model for `/agents/` or `/aiagents/`;
- introduce a frontend framework or separate build architecture.

## Definition Of Done For This Register

This register is operationally complete when:

- every 2026-06-04 challenge finding is represented by an ID above;
- every ID has task, owner, lane, gate and closure evidence;
- `docs/workflow/gap-analysis.md` links to this document;
- `docs/workflow/README.md` lists this document;
- an execution roadmap exists for prioritization;
- existing product gap guard remains green.
