# Product Tech Challenge Execution Roadmap — 2026-06-04

Дата: 04.06.2026

Статус: execution roadmap for `product-tech-challenge-gap-register-2026-06-04.md`.

## Purpose

Этот документ задаёт порядок закрытия gaps из 2026-06-04 challenge. Он нужен, чтобы команда не начинала UI/implementation work до решений, от которых зависят публичные обещания, Bitrix product content safety, CRM/upstream contract and release evidence.

## Source Register

Все IDs ниже берутся из:

- `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`

Если gap отсутствует в source register, его нельзя считать частью этого roadmap без обновления обоих документов.

## Execution Principles

1. Сначала закрывать gaps, которые могут сделать публичное обещание неверным или операционно опасным.
2. Не начинать крупную TO BE UI реализацию без token/component/state decisions.
3. Не менять REST/upstream/form payload без Security / Integration lane.
4. Не публиковать proof/status UI без approved claim-source matrix.
5. Не превращать Bitrix editor content в hard dependency без schema validation and fail-fast checks.
6. Не переписывать stack ради современности: текущий Bitrix SSR baseline сохраняется до конкретного product/interaction trigger.

## Phase 0 — Register Adoption

Goal: make challenge gaps visible in workflow planning.

| Work | Covered IDs | Owner | Output |
|---|---|---|---|
| Link source register from workflow docs | all | PM + Codex | `gap-analysis.md`, `README.md`, `current-state.md` reference the register |
| Add issue/PR rule for future work | `REL-002` | PM + QA | Product work references affected challenge IDs |
| Keep existing guards green | all | Dev + QA | `config:check`, `bitrix:check`, `product:gaps:check` pass |

Exit criteria:

- Docs are linked.
- Future implementation scope can point to concrete gap IDs.
- No existing product gap guard is broken.

## Phase 1 — Public Promise And Product Source Safety

Goal: prevent unsafe product claims and invalid Bitrix-owned product pages.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Product content fail-fast and schema | `CFG-001`, `CFG-002`, `ARCH-001`, `ARCH-003`, `ARCH-004`, `STACK-004` | Full Feature / Security | Architect + Backend + QA + Content | Schema spec, ownership matrix, validator, negative fixtures, release gate |
| Cache/source/version governance | `CFG-003`, `ARCH-011`, `STACK-007` | Security / Integration | Backend + DevOps | Cache/version decision, source switch automation decision |
| Claims and proof safety | `UX-006`, `CONTENT-002`, `CONTENT-001`, `CONTENT-003`, `ARCH-009`, `UI-005` | Full Feature | PM + Sales + Legal + Security + Designer | Public/private/blocked claim matrix, product evidence map, proof/status UI rules |
| Product taxonomy and packaging approval | `CONTENT-005`, `UX-004`, `UX-005`, `ARCH-010`, `CONTENT-004` | Full Feature | PM + Sales + SEO + Architect | Approved names, boundaries, `/price/` framing, canonical/SEO decisions |

Do not start:

- public metrics/logos/certification copy;
- `/agents/` redirect/canonical changes;
- proof/status component implementation;
- schema/offers JSON-LD for products.

Exit criteria:

- Invalid Bitrix product payloads cannot silently pass release.
- Public product copy is backed by approved evidence or explicitly safe-copy.
- Canonical/product taxonomy decisions are recorded.

## Phase 2 — Enterprise CJM And Sales Qualification

Goal: make product pages support actual buyer roles and Sales follow-up.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Role-based CJM | `UX-001`, `UX-007`, `UX-008`, `UX-009` | Full Feature | PM + UX + Sales + Security | Role journey maps and product pilot kits |
| CTA and success states | `UX-002`, `UX-003`, `UX-010`, `CMP-003` | Full Feature | PM + UX + Frontend + QA | CTA matrix, returning-lead path, success-state matrix |
| Structured lead/CRM decision | `ARCH-005`, `ARCH-006`, `CFG-004` | Security / Integration | Backend + PM + Sales + Analytics + QA | Fallback approval or structured field contract; no-PII goal map |

Do not start:

- new hidden fields or upstream payload fields;
- role-specific form behavior;
- analytics params beyond approved no-PII taxonomy.

Exit criteria:

- Sales can use submitted product context.
- CTA variants do not break existing lead form contract.
- Product funnel can be measured without PII.

## Phase 3 — TO BE Design System And UX Components

Goal: create design-ready inputs before visual implementation.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Tokens, density and palette | `UI-001`, `UI-002`, `UI-003`, `STACK-003` | Full Feature | Designer + Frontend | Token source, density/radius/card policy, CSS architecture note |
| Hero, diagrams and proof UI | `UI-004`, `UI-005`, `UI-006` | Full Feature | Designer + Architect + Legal | Hero taxonomy, diagram spec, proof/status visual system |
| Forms, chat and price states | `UI-007`, `UI-008`, `UI-009` | Full Feature | Designer + Frontend + QA | State matrices and responsive/mobile acceptance criteria |
| Icons and handoff traceability | `UI-010`, `CMP-008` | Full Feature | Designer + Frontend + PM | Icon taxonomy and design handoff index |

Do not start:

- broad CSS restyle;
- `/price/` mobile rewrite;
- proof/status cards;
- new visual component variants.

Exit criteria:

- Designer and frontend share one TO BE token/component/state contract.
- QA knows which states to smoke.
- Legal/proof visual constraints are explicit.

## Phase 4 — Component And Frontend Maintainability

Goal: reduce regression risk before large interaction changes.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Product component boundary | `ARCH-002`, `CMP-001`, `CMP-002` | Full Feature | Architect + Frontend + Designer + QA | Partial/component promotion criteria and preview fixture decision |
| Price team builder decomposition | `CMP-004`, `UI-009`, `STACK-002`, `STACK-005` | Full Feature | Frontend + QA | Module split plan and regression smoke |
| Forms/chat modularity | `CMP-005`, `CMP-006`, `SEC-001`, `ARCH-006` | Full Feature / Security | Frontend + Backend + QA | Module boundaries, unchanged payload tests, chat/form contract review |
| Content wrapper coverage | `CMP-007`, `CFG-005` | Fast Fix | Frontend + Backend + QA | FAQ/content wrapper guard or smoke |

Do not start:

- simultaneous price/forms/chat rewrites;
- component API promotion without ADR trigger check;
- payload changes while only doing UI modularity.

Exit criteria:

- Large JS files have safe split path or accepted baseline.
- Smoke coverage exists before high-risk UI changes.
- Product block component policy is explicit.

## Phase 5 — Security, Release And Legacy Closure

Goal: keep release discipline strict while the product layer matures.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Future sensitive endpoints | `CFG-006`, `ARCH-008`, `SEC-002` | Security / Integration | Security + Backend + DevOps + PM | Endpoint sensitivity/auth matrix |
| CSP enforce runway | `ARCH-007`, `SEC-003` | Security / Integration | Security + Frontend | Inline/vendor cleanup backlog and enforce trigger |
| Safe evidence model | `ARCH-012`, `REL-002` | Full Feature | QA + PM + Security | Release evidence templates and sign-off references |
| Legacy sale aliases | `REL-001` | Security / Integration | Backend + DevOps + PM | Post-30.06 aggregate inventory and final alias decision |

Do not start:

- private proof downloads;
- procurement packets;
- enforcing CSP;
- alias removal.

Exit criteria:

- New sensitive flows have auth/rate/origin/IP decisions before implementation.
- CSP move to enforce has a concrete cleanup plan.
- Legacy alias external inventory is complete.

## Phase 6 — Accepted Risk Monitoring

Goal: keep deliberate non-actions visible.

| Accepted Baseline | IDs | Revisit Trigger |
|---|---|---|
| Keep Bitrix SSR + vanilla JS | `STACK-001` | Product interactions become app-like, require complex client state or offline workflow |
| Keep CSP report-only | `ARCH-007` | Vendor/inline violations are triaged and inline cleanup is done |
| Keep CSRF trusted browser-source fallback | `SEC-001` | Endpoint becomes private/sensitive or evidence shows abuse risk |
| Keep asset hygiene guard model | `STACK-006` | New build tool, CSS source or icon system is introduced |

Monitoring evidence:

- accepted risks are referenced in issues when touched;
- no implementation assumes accepted risk is closed;
- ADR is opened if a revisit trigger fires.

## Suggested Sprint Packaging

| Sprint | Theme | Must Include |
|---|---|---|
| Sprint A | Product Source And Claims Safety | `CFG-001`, `CFG-002`, `ARCH-001`, `UX-006`, `CONTENT-002` |
| Sprint B | Taxonomy, SEO And Packaging | `CONTENT-005`, `UX-004`, `UX-005`, `ARCH-010`, `CONTENT-001`, `CONTENT-003`, `CONTENT-004` |
| Sprint C | CJM, CTA And CRM Qualification | `UX-001`, `UX-002`, `UX-008`, `ARCH-005`, `ARCH-006` |
| Sprint D | TO BE Design System | `UI-001`, `UI-002`, `UI-005`, `UI-007`, `UI-009`, `STACK-003` |
| Sprint E | Frontend Component Hardening | `CMP-004`, `CMP-005`, `CMP-006`, `STACK-002`, `STACK-005` |
| Sprint F | Security/Release Closure | `SEC-002`, `SEC-003`, `REL-001`, `REL-002`, `ARCH-012` |

## Detailed Sprint Documents

04.06.2026 sprint packaging is now fixed as Sprint 17-23:

| Sprint | Document | Covers |
|---|---|---|
| Sprint 17 | `docs/workflow/sprints/2026-06-04-sprint-17-product-source-and-claims-safety.md` | Product source, schema, cache, content governance, proof and claims safety |
| Sprint 18 | `docs/workflow/sprints/2026-06-04-sprint-18-taxonomy-seo-packaging.md` | Taxonomy, `/agents/` vs `/aiagents/`, `/price/`, packaging, product SEO and metadata |
| Sprint 19 | `docs/workflow/sprints/2026-06-04-sprint-19-cjm-cta-crm-qualification.md` | Role CJM, CTA taxonomy, returning lead, CRM/upstream decision and analytics |
| Sprint 20 | `docs/workflow/sprints/2026-06-04-sprint-20-to-be-design-system.md` | TO BE tokens, density, proof/status UI, diagrams, forms/chat/price states and design traceability |
| Sprint 21 | `docs/workflow/sprints/2026-06-04-sprint-21-frontend-component-hardening.md` | Product component boundary, price/forms/chat modularity, fixtures and wrapper guards |
| Sprint 22 | `docs/workflow/sprints/2026-06-04-sprint-22-security-release-legacy-closure.md` | Sensitive endpoint model, CSP runway, release evidence and legacy aliases |
| Sprint 23 | `docs/workflow/sprints/2026-06-04-sprint-23-accepted-risk-monitoring.md` | Accepted-risk monitoring for stack, assets, CSRF, CSP and sign-off discipline |

Master sprint board: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`.

Issue-ready execution board: `docs/workflow/product-tech-challenge-execution-board-2026-06-04.md`.

Owner approval and evidence intake:

- `docs/workflow/product-tech-challenge-owner-approval-request-2026-06-04.md`;
- `docs/workflow/product-tech-challenge-evidence-intake-2026-06-04.md`.

Machine-readable status tracker:

- `docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json`.

Issue-ready backlog:

- `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.md`;
- `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.json`.

Owner review runbook:

- `docs/workflow/product-tech-challenge-owner-review-runbook-2026-06-04.md`.

## Ready-To-Implement Checklist

A task from this roadmap is ready only when:

- related gap IDs are listed in the issue/plan;
- workflow lane is selected;
- affected files/areas are named;
- Design/ADR/Security/SEO gates are checked;
- acceptance criteria include closure evidence;
- existing form/chat/SEO/release contracts are preserved or migration is explicit;
- PII and raw production evidence are not stored in docs.

## Verification Commands

Docs-only adoption:

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
npm run product:challenge:check
npm run product:challenge:board:check
npm run product:challenge:approval:check
npm run product:challenge:owner-status:check
npm run product:challenge:issue-backlog:check
```

Implementation scopes must add focused checks from the relevant lane, for example:

```bash
npm run product:content:schema:self-test
npm run product:content:schema:negative-test
npm run product:content:schema:check
npm run product:content:safety:check
npm run product:content:check:strict
npm run product:content:check:strict:json
npm run product:content:target-evidence:check -- --file=/path/to/product-content-strict.json --allow-source=bitrix
npm run seo:check
npm run browser:smoke:price
npm run e2e:css-js:local
```

## Roadmap Definition Of Done

Roadmap is useful when:

- every source register ID appears in at least one phase or accepted-risk table;
- phase ordering blocks risky UI/claims/content work until decisions are approved;
- future implementation issues can copy gap IDs and acceptance evidence directly;
- existing workflow docs link to both source register and roadmap.
