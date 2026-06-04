# Product Tech Challenge Execution Board — 2026-06-04

Дата: 04.06.2026
Статус: issue-ready execution board / owner approvals pending
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Sprint roadmap: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`
Owner approval request: `docs/workflow/product-tech-challenge-owner-approval-request-2026-06-04.md`
Evidence intake: `docs/workflow/product-tech-challenge-evidence-intake-2026-06-04.md`
Owner status tracker: `docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json`
Issue backlog: `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.md`
Owner review runbook: `docs/workflow/product-tech-challenge-owner-review-runbook-2026-06-04.md`

## Purpose

Этот board переводит Sprint 17-23 decision packages в issue-ready work packages. Он нужен PM, tech lead, owners and agents как один рабочий экран: что можно брать в работу, что blocked, какие owners нужны, какие gates/evidence обязательны, and какие gaps покрыты каждым work package.

Документ не закрывает gaps. Закрытие возможно только через owner approval, implementation, verification and evidence.

## Status Model

| Status | Meaning |
|---|---|
| `ready-local` | Можно делать локально без внешних решений, если scope не меняет runtime contracts |
| `ready-target-evidence` | Нужен target Bitrix/PHP/production or owner-run evidence |
| `owner-review` | Есть draft decision package, но нужна явная owner approval до implementation |
| `blocked-external` | Нужна внешняя дата, Legal/Sales/SEO/Security/CRM/DevOps evidence or access |
| `accepted-monitor` | Ничего не внедрять; мониторить triggers and reopen only by lane |

## Work Packages

| WP | Status | Primary sprint | Gap IDs | Issue title | Main blocker |
|---|---|---|---|---|---|
| WP-01 | ready-target-evidence | 17 | `CFG-001`, `CFG-002`, `CFG-003`, `ARCH-001`, `ARCH-003`, `STACK-004` | Product content validation and target evidence closure | Target Bitrix/PHP strict check, target negative fixture, cache-clear evidence |
| WP-02 | owner-review | 17 | `ARCH-004`, `ARCH-011`, `STACK-007` | Product content ownership and environment automation | Content/DevOps/Backend ownership decision |
| WP-03 | blocked-external | 17/18/20 | `UX-006`, `CONTENT-001`, `CONTENT-002`, `CONTENT-003`, `ARCH-009`, `UI-005` | Public proof, claims and evidence approval | PM/Sales/Legal/SEO evidence approval |
| WP-04 | owner-review | 18 | `CONTENT-004`, `CONTENT-005`, `UX-004`, `UX-005`, `ARCH-010` | Product taxonomy, packaging and SEO route approval | PM/Sales/SEO/Product approval |
| WP-05 | owner-review | 19 | `CFG-004`, `UX-001`, `UX-002`, `UX-003`, `UX-007`, `UX-008`, `UX-009`, `UX-010`, `ARCH-005`, `ARCH-006`, `CMP-003` | Enterprise CJM, CTA and CRM qualification approval | PM/UX/Sales/Security/Analytics approval |
| WP-06 | owner-review | 20 | `UI-001`, `UI-002`, `UI-003`, `UI-004`, `UI-005`, `UI-006`, `UI-007`, `UI-008`, `UI-009`, `UI-010`, `STACK-003`, `CMP-008` | TO BE design system implementation readiness | Designer/Frontend/QA/Legal approval |
| WP-07 | owner-review | 21 | `CFG-005`, `ARCH-002`, `CMP-001`, `CMP-002`, `CMP-004`, `CMP-005`, `CMP-006`, `CMP-007`, `STACK-002`, `STACK-005`, `SEC-001` | Frontend/component hardening implementation path | Frontend/QA/Architect/Security approval and smoke baseline |
| WP-08 | blocked-external | 22 | `CFG-006`, `ARCH-007`, `ARCH-008`, `ARCH-012`, `SEC-002`, `SEC-003`, `REL-001`, `REL-002` | Security, release evidence and legacy alias closure | Security/DevOps/QA approval; legacy full-window inventory after `2026-06-30` |
| WP-09 | accepted-monitor | 23 | `STACK-001`, `STACK-006`, `SEC-001`, `ARCH-007`, `REL-002` | Accepted-risk monitoring and reopen rules | Revisit trigger must fire before implementation |

## Issue Cards

### WP-01 — Product Content Validation And Target Evidence Closure

| Field | Value |
|---|---|
| Lane | Security / Integration + Full Feature |
| Affected areas | `tools/product-content-check.php`, `tools/product-content-schema-check.mjs`, product iblocks, product cache, release evidence |
| Definition of Ready | Target Bitrix/PHP access, local negative fixture guard, no-PII evidence path |
| Acceptance criteria | Strict live Bitrix product content check passes; local negative fixture fails as expected; target negative fixture/simulated target evidence is captured where approved; cache-clear dry-run evidence captured; release evidence uses `schema_issues` without raw content |
| Verification | `npm run product:content:safety:check`, target `npm run product:content:check:strict:json`, `npm run product:content:target-evidence:check -- --file=/path/to/product-content-strict.json --allow-source=bitrix`, target `npm run product:content:cache-clear:dry-run`, `npm run product:content:switch-readiness:prod` if switch readiness is touched |
| Do not start | Do not change public product copy or stronger claims as part of this issue |

### WP-02 — Product Content Ownership And Environment Automation

| Field | Value |
|---|---|
| Lane | Security / Integration |
| Affected areas | Product content workflow, Bitrix admin ownership, source switch/cache runbooks, staging/prod parity |
| Definition of Ready | Content + Backend + DevOps owner named; environments listed |
| Acceptance criteria | Ownership matrix says what lives in Bitrix, Git fallback, existing iblocks or private evidence docs; environment matrix covers local/staging/prod; automation decision covers cache clear, source switch, strict checks and rollback |
| Verification | Docs review, `npm run config:check`, target `npm run config:runtime:check`, product switch/readiness checks where applicable |
| Do not start | Do not automate deploy/source switch across environments without rollback and owner sign-off |

### WP-03 — Public Proof, Claims And Evidence Approval

| Field | Value |
|---|---|
| Lane | Full Feature |
| Affected areas | Product copy, proof/status UI, cases/offers/FAQ/services evidence mapping, packaging claims |
| Definition of Ready | PM, Sales, Legal and SEO owners can review actual evidence; public/private/blocked evidence categories agreed |
| Acceptance criteria | Claims matrix has source, owner, confidence, public wording and blocked wording; product evidence map tags cases/offers/FAQ/services by product; proof/status UI can only display approved evidence state |
| Verification | Owner approval notes, no-PII evidence references, `npm run seo:check`, design/claims review before public changes |
| Do not start | Do not publish metrics, logos, certifications, registry claims, guarantees or private proof links without approval |

### WP-04 — Product Taxonomy, Packaging And SEO Route Approval

| Field | Value |
|---|---|
| Lane | Full Feature |
| Affected areas | `/agents/`, `/aiagents/`, `/price/`, product metadata, sitemap/canonical decisions |
| Definition of Ready | PM/Sales/SEO decision session scheduled; keyword/intent evidence available |
| Acceptance criteria | Product taxonomy approved or amended; `/agents/` vs `/aiagents/` canonical/compatibility decision recorded; `/price/` route intent approved; title/description/H1 intro copy approved |
| Verification | `npm run seo:check`, rendered SEO smoke after implementation, sitemap/canonical review if URLs change |
| Do not start | Do not redirect, canonicalize or deindex `/agents/` / `/aiagents/` before SEO approval |

### WP-05 — Enterprise CJM, CTA And CRM Qualification Approval

| Field | Value |
|---|---|
| Lane | Full Feature + Security / Integration if payload changes |
| Affected areas | Role CJM, CTA taxonomy, success states, `lead-form-contract.md`, analytics events, CRM/upstream |
| Definition of Ready | PM/UX/Sales approve journeys; Sales/CRM owner decides text fallback vs structured fields |
| Acceptance criteria | Role journeys approved; pilot kits approved; CTA taxonomy and returning-lead path approved; success states defined; CRM fallback or structured-field scope is explicit; no-PII funnel goals approved |
| Verification | `npm run js:check`, `npm run browser:smoke` for implementation, controlled success-flow if forms/chat/upstream change |
| Do not start | Do not add hidden fields, upstream payload fields or analytics params beyond approved no-PII taxonomy |

### WP-06 — TO BE Design System Implementation Readiness

| Field | Value |
|---|---|
| Lane | Full Feature |
| Affected areas | Design tokens, `styles/global.css`, component states, proof/status UI, diagrams, forms/chat/price states |
| Definition of Ready | Designer/Frontend/QA/Legal approve token source, density/card/radius policy, proof/status rules and state matrices |
| Acceptance criteria | TO BE token source approved; design states cover form/chat/price/proof; migration map classifies visual-restyle vs contract-preserving-split vs contract-migration; implementation batch has smoke scope |
| Verification | `npm run design:handoff:check`, `npm run css:check`, `npm run template-styles:check`, `npm run js:check`, focused browser smoke for touched surfaces |
| Do not start | Do not start broad restyle, proof/status components or `/price/` mobile rewrite without approved states and smoke |

### WP-07 — Frontend/Component Hardening Implementation Path

| Field | Value |
|---|---|
| Lane | Full Feature, Fast Fix for FAQ guard, Security / Integration if forms/chat payload changes |
| Affected areas | Product renderer, product partials, `/price/` script, `forms.js`, `chat-agent.js`, FAQ/content wrappers |
| Definition of Ready | Sprint 21 decision package approved or amended; baseline smoke available for touched surface |
| Acceptance criteria | Product block order/component boundary approved; `/price/`, forms and chat split only contract-preserving; FAQ fallback explicit/default decision captured; fixture/smoke map approved |
| Verification | `npm run js:check`, `npm run design:components:check`, `npm run design:migration:check`, `npm run browser:smoke:price` for `/price/`, `npm run browser:smoke` for forms/chat |
| Do not start | Do not split price/forms/chat simultaneously; do not change payloads while doing UI modularity |

### WP-08 — Security, Release Evidence And Legacy Alias Closure

| Field | Value |
|---|---|
| Lane | Security / Integration |
| Affected areas | Endpoint sensitivity, CSP, release sign-off, private proof/doc access, legacy sale aliases |
| Definition of Ready | Security/Backend/DevOps/QA approve endpoint classes; legacy inventory window is complete for alias final mode |
| Acceptance criteria | Endpoint sensitivity and rate classes approved; private proof/doc access model approved before implementation; CSP enforce checklist complete before config switch; future release gates have checker support; legacy final mode chosen after full-window logs and CRM/upstream report |
| Verification | `npm run release:signoff:self-test`, `npm run sale:sunset:check`, `npm run gaps:known`, `npm run legacy:sale:inventory:logs` on production logs, production smoke if runtime changes |
| Do not start | Do not enforce CSP, add private downloads, remove aliases, return `410` or redirect aliases before gates/evidence |

### WP-09 — Accepted-Risk Monitoring And Reopen Rules

| Field | Value |
|---|---|
| Lane | Monitoring; implementation lane depends on trigger |
| Affected areas | Stack, assets, CSRF, CSP, release evidence discipline |
| Definition of Ready | PM/Architect/Security/QA review cadence accepted |
| Acceptance criteria | Accepted risks have owners, triggers and issue snippet; future work references affected accepted risk IDs; triggers open ADR/Security/Full Feature scope before implementation |
| Verification | `npm run product:challenge:board:check`, `npm run gaps:known`, `npm run release:signoff:self-test` |
| Do not start | Do not treat accepted-monitor as closed or as permission to bypass gates |

## Owner Approval Matrix

| Owner | Decisions / evidence needed | Blocks |
|---|---|---|
| PM | Product taxonomy, role CJM, CTA taxonomy, returning-lead path, accepted-risk cadence | WP-04, WP-05, WP-09 |
| Sales | Product names/boundaries, pilot kits, CRM follow-up usefulness, proof/evidence sources | WP-03, WP-04, WP-05 |
| Legal | Public/private/blocked claims, proof/status wording, private document constraints | WP-03, WP-06, WP-08 |
| SEO | `/agents/` vs `/aiagents/`, metadata, sitemap/canonical, product clusters | WP-04, WP-03 |
| Designer | TO BE tokens, density/card policy, proof/status UI, diagrams, form/chat/price states | WP-06, WP-07 |
| Architect | Product renderer order, component promotion, product content ownership, stack triggers | WP-02, WP-07, WP-09 |
| Frontend | JS module policy, `/price/` split plan, forms/chat split plan, asset guard model | WP-06, WP-07, WP-09 |
| Backend | Product content validation lifecycle, CRM/upstream fallback, endpoint classes | WP-01, WP-02, WP-05, WP-08 |
| Security | CSRF triggers, private proof/doc model, CSP enforce checklist, endpoint classes | WP-05, WP-07, WP-08, WP-09 |
| QA | Fixture/smoke map, release evidence, no-PII sign-off, browser/runtime gates | WP-01, WP-06, WP-07, WP-08, WP-09 |
| DevOps | Target checks, runtime config evidence, cache/source automation, access logs | WP-01, WP-02, WP-08 |
| Content | Product content lifecycle, evidence mapping, metadata/content ownership | WP-01, WP-02, WP-03, WP-04 |
| Analytics | Product funnel goals, Metrika mapping, no-PII event approval | WP-05 |

## Do Not Start Board

| Work | Blocked until |
|---|---|
| Public metrics, logos, certifications, registry/deployment claims | WP-03 Legal/Sales/PM evidence approval |
| `/agents/` redirect, canonical switch or `/aiagents/` route behavior change | WP-04 SEO/PM decision |
| Structured CRM/upstream fields | WP-05 Security / Integration contract |
| Broad TO BE visual implementation | WP-06 design/state approval |
| Proof/status UI on public pages | WP-03 claims approval + WP-06 design approval |
| `/price/` mobile rewrite | WP-06 state approval + WP-07 smoke plan |
| Splitting `/price/`, forms and chat in one batch | WP-07 forbids simultaneous high-risk split |
| Private proof/document downloads | WP-08 access model approval |
| CSP enforce | WP-08 CSP checklist, checker/sign-off and rollback |
| Legacy sale alias removal/410/redirect | WP-08 full-window inventory after `2026-06-30` and final mode decision |
| SPA/framework/bundler migration | WP-09 stack trigger + ADR |

## Coverage Index

| Gap ID | Work package |
|---|---|
| `CFG-001` | WP-01 |
| `CFG-002` | WP-01 |
| `CFG-003` | WP-01 |
| `CFG-004` | WP-05 |
| `CFG-005` | WP-07 |
| `CFG-006` | WP-08 |
| `UX-001` | WP-05 |
| `UX-002` | WP-05 |
| `UX-003` | WP-05 |
| `UX-004` | WP-04 |
| `UX-005` | WP-04 |
| `UX-006` | WP-03 |
| `UX-007` | WP-05 |
| `UX-008` | WP-05 |
| `UX-009` | WP-05 |
| `UX-010` | WP-05 |
| `UI-001` | WP-06 |
| `UI-002` | WP-06 |
| `UI-003` | WP-06 |
| `UI-004` | WP-06 |
| `UI-005` | WP-03 / WP-06 |
| `UI-006` | WP-06 |
| `UI-007` | WP-06 |
| `UI-008` | WP-06 |
| `UI-009` | WP-06 / WP-07 |
| `UI-010` | WP-06 |
| `ARCH-001` | WP-01 |
| `ARCH-002` | WP-07 |
| `ARCH-003` | WP-01 |
| `ARCH-004` | WP-02 |
| `ARCH-005` | WP-05 |
| `ARCH-006` | WP-05 |
| `ARCH-007` | WP-08 / WP-09 |
| `ARCH-008` | WP-08 |
| `ARCH-009` | WP-03 |
| `ARCH-010` | WP-04 |
| `ARCH-011` | WP-02 |
| `ARCH-012` | WP-08 |
| `CMP-001` | WP-07 |
| `CMP-002` | WP-07 |
| `CMP-003` | WP-05 |
| `CMP-004` | WP-07 |
| `CMP-005` | WP-07 |
| `CMP-006` | WP-07 |
| `CMP-007` | WP-07 |
| `CMP-008` | WP-06 |
| `STACK-001` | WP-09 |
| `STACK-002` | WP-07 |
| `STACK-003` | WP-06 |
| `STACK-004` | WP-01 |
| `STACK-005` | WP-07 |
| `STACK-006` | WP-09 |
| `STACK-007` | WP-02 |
| `CONTENT-001` | WP-03 / WP-04 |
| `CONTENT-002` | WP-03 |
| `CONTENT-003` | WP-03 / WP-04 |
| `CONTENT-004` | WP-04 |
| `CONTENT-005` | WP-04 |
| `SEC-001` | WP-07 / WP-09 |
| `SEC-002` | WP-08 |
| `SEC-003` | WP-08 |
| `REL-001` | WP-08 |
| `REL-002` | WP-08 / WP-09 |

## Board Verification

```bash
npm run product:challenge:check
npm run product:challenge:board:check
npm run product:challenge:approval:check
npm run product:challenge:owner-status:check
npm run product:challenge:issue-backlog:check
npm run config:check
npm run bitrix:check
npm run product:gaps:check
```

Implementation issues must add focused commands from their own work package.
