# Product Tech Challenge Sprint Roadmap — Sprint 17-23

Дата формирования: 04.06.2026
Статус: in-progress / Sprint 17-23 baselines drafted
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Execution roadmap: `docs/workflow/product-tech-challenge-execution-roadmap-2026-06-04.md`
Execution board: `docs/workflow/product-tech-challenge-execution-board-2026-06-04.md`
Owner approval request: `docs/workflow/product-tech-challenge-owner-approval-request-2026-06-04.md`
Evidence intake: `docs/workflow/product-tech-challenge-evidence-intake-2026-06-04.md`
Owner status tracker: `docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json`
Issue backlog: `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.md`
Owner review runbook: `docs/workflow/product-tech-challenge-owner-review-runbook-2026-06-04.md`

## Roadmap Goal

Разложить 2026-06-04 product tech challenge gaps в исполнимую sprint-сетку, где каждый gap/task ID имеет owner, lane, gate, dependency and closure evidence.

## Sprint Sequence

| Sprint | Document | Theme | Primary Output | Blocks |
|---|---|---|---|---|
| 17 | `2026-06-04-sprint-17-product-source-and-claims-safety.md` | Product source and claims safety | Bitrix product content safety, schema/fail-fast, proof/claims matrix | Stronger vendor claims, proof/status UI, editor-owned content scale |
| 18 | `2026-06-04-sprint-18-taxonomy-seo-packaging.md` | Taxonomy, SEO and packaging | Product taxonomy, `/agents/` vs `/aiagents/`, `/price/` framing, packaging and metadata | Final product copy, canonical/redirect changes, product SEO clusters |
| 19 | `2026-06-04-sprint-19-cjm-cta-crm-qualification.md` | CJM, CTA and CRM qualification | Role journeys, CTA taxonomy, returning-lead path, CRM/upstream decision, product funnel goals | Role-specific UX, form payload changes, sales automation claims |
| 20 | `2026-06-04-sprint-20-to-be-design-system.md` | TO BE design system | Token source, density/radius policy, proof/status UI, diagrams, form/chat/price states | Visual redesign and UI implementation |
| 21 | `2026-06-04-sprint-21-frontend-component-hardening.md` | Frontend component hardening | Product component boundary, price/forms/chat module plan, fixture-driven smoke | Large interaction changes and component API promotion |
| 22 | `2026-06-04-sprint-22-security-release-legacy-closure.md` | Security, release and legacy closure | Sensitive endpoint controls, CSP roadmap, release evidence model, legacy sale inventory | Private docs/proof flows, CSP enforce, alias removal |
| 23 | `2026-06-04-sprint-23-accepted-risk-monitoring.md` | Accepted-risk monitoring | Monitoring rules for accepted stack/security/asset baselines and program closeout | Risk drift and unowned accepted decisions |

## Dependency Model

```text
Sprint 17: source safety + claims safety
  ↓
Sprint 18: taxonomy + packaging + SEO
  ↓
Sprint 19: CJM + CTA + CRM/upstream + analytics
  ↓
Sprint 20: TO BE design system
  ↓
Sprint 21: frontend/component hardening
  ↓
Sprint 22: security/release/legacy closure
  ↓
Sprint 23: accepted-risk monitoring and program governance
```

Notes:

- Sprint 18 can start in parallel with parts of Sprint 17 only for research, but public copy decisions depend on proof/claims safety.
- Sprint 20 should not start implementation until Sprint 17-19 outputs are approved.
- Sprint 21 can do planning before Sprint 20, but code refactors should wait for design/state decisions where relevant.
- Sprint 22 can run security/release planning in parallel, but enforcement/removal actions require evidence gates.

## Gap Coverage Matrix

| Sprint | Gap IDs |
|---|---|
| 17 | `CFG-001`, `CFG-002`, `CFG-003`, `ARCH-001`, `ARCH-003`, `ARCH-004`, `ARCH-009`, `ARCH-011`, `STACK-004`, `STACK-007`, `UX-006`, `CONTENT-002` |
| 18 | `CONTENT-001`, `CONTENT-003`, `CONTENT-004`, `CONTENT-005`, `UX-004`, `UX-005`, `ARCH-010` |
| 19 | `CFG-004`, `UX-001`, `UX-002`, `UX-003`, `UX-007`, `UX-008`, `UX-009`, `UX-010`, `ARCH-005`, `ARCH-006`, `CMP-003` |
| 20 | `UI-001`, `UI-002`, `UI-003`, `UI-004`, `UI-005`, `UI-006`, `UI-007`, `UI-008`, `UI-009`, `UI-010`, `STACK-003`, `CMP-008` |
| 21 | `CFG-005`, `ARCH-002`, `CMP-001`, `CMP-002`, `CMP-004`, `CMP-005`, `CMP-006`, `CMP-007`, `STACK-002`, `STACK-005`, `SEC-001` |
| 22 | `CFG-006`, `ARCH-007`, `ARCH-008`, `ARCH-012`, `REL-001`, `REL-002`, `SEC-002`, `SEC-003` |
| 23 | `STACK-001`, `STACK-006`, `SEC-001`, `ARCH-007`, `REL-002` |

## Program-Level Gates

| Gate | Applies To | Rule |
|---|---|---|
| ADR | Sprint 17, 19, 21, 22 | Required if product schema, cache/versioning, component API, CRM/upstream contract, private access, CSP enforce or canonical model changes |
| Design | Sprint 18, 19, 20, 21 | Required for CTA taxonomy, `/price/` UX, proof/status UI, diagrams, form/chat states and TO BE visual implementation |
| QA Early | Sprint 17, 19, 21, 22 | Required for content validation, forms/CRM, analytics, frontend module changes, release evidence and security controls |
| Security / Integration | Sprint 17, 19, 22 | Required for product source/deploy controls, structured lead fields, private docs/proof, endpoint controls and CSP |
| SEO | Sprint 18 | Required for `/agents/` vs `/aiagents/`, metadata, canonical, sitemap and product clusters |
| Legal/Claims | Sprint 17, 18, 20 | Required before public proof/status/packaging copy |
| Post-deploy smoke | Implementation tasks in any sprint | Required when code, public URLs, product content, SEO, forms, assets or security headers change |

## Cross-Sprint Do Not Start Rules

| Work | Blocked Until |
|---|---|
| Public metrics, logos, certification or deployment claims | Sprint 17 claim-source matrix approved |
| `/agents/` redirect/canonical change | Sprint 18 SEO/product decision approved |
| Structured upstream/CRM fields | Sprint 19 Security / Integration contract approved |
| Large TO BE visual implementation | Sprint 20 token/component/state outputs approved |
| `/price/` mobile rewrite | Sprint 20 state spec and Sprint 21 module/smoke plan approved |
| Private proof/document request flow | Sprint 22 access/security model approved |
| CSP enforce | Sprint 22 CSP cleanup/enforce trigger approved |
| Legacy sale alias removal | Sprint 22 external inventory and final mode decision approved |

## Program Acceptance Criteria

1. Every source register ID is assigned to at least one sprint.
2. Every sprint has owners, gates, acceptance criteria, QA/smoke and risks.
3. Sprint 17-22 outputs feed implementation decisions; Sprint 23 monitors accepted baselines.
4. No sprint closes owner-blocked gaps without evidence.
5. Future implementation plans reference sprint number and source gap IDs.
6. Release evidence remains aggregate/safe and contains no PII, cookies, sessions or raw payloads.

## Program Verification

Docs adoption:

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

Implementation sprints add focused commands from their own sprint docs.

## Review Cadence

| Cadence | Owner | Agenda |
|---|---|---|
| Weekly PM/Owner review | PM | Sprint statuses, blocked decisions, evidence due dates |
| Design review | Designer + Frontend + PM | Sprint 18-20 outputs and AS IS -> TO BE traceability |
| Architecture review | Architect + Backend + Frontend | Sprint 17, 19, 21 ADR triggers and implementation constraints |
| Security/QA review | Security + QA | Sprint 17, 19, 22 gates and no-PII release evidence |
| Release review | QA + DevOps + PM | Sprint 22 evidence, smoke and accepted risks |

## Roadmap Review

### Done

- Planned sprint structure exists.
- Sprint 17 local product content safety baseline is drafted.
- Sprint 18 taxonomy/SEO/packaging decision baseline is drafted.
- Sprint 19 CJM/CTA/CRM qualification decision baseline is drafted.
- Sprint 20 TO BE design system decision baseline is drafted.
- Sprint 21 frontend/component hardening decision baseline is drafted.
- Sprint 22 security/release/legacy closure decision baseline is drafted.
- Sprint 23 accepted-risk monitoring baseline is drafted.
- Sprint 17-23 issue-ready execution board is drafted and covered by `product:challenge:board:check`.
- Sprint 17-23 owner approval/evidence intake package is drafted and covered by `product:challenge:approval:check`.
- Sprint 17-23 machine-readable owner status tracker is drafted and covered by `product:challenge:owner-status:check`.
- Sprint 17-23 issue backlog manifest is drafted and covered by `product:challenge:issue-backlog:check`.

### Not Done

- Owner approvals and implementation work are not completed by this document.

### Follow-Up

- Collect owner approvals and implementation evidence for Sprint 17-23 packages.
- Update `product-tech-challenge-owner-status-tracker-2026-06-04.json` after each owner response or evidence intake change.
- Import or map `PTC-WP-01` - `PTC-WP-09` issues from `product-tech-challenge-issue-backlog-2026-06-04.json`.
- Use `product-tech-challenge-owner-review-runbook-2026-06-04.md` for owner review, evidence intake and implementation handoff.
- Convert approved decisions into scoped implementation issues with affected gap IDs and release evidence.
