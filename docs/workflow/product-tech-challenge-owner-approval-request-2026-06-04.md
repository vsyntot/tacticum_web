# Product Tech Challenge Owner Approval Request — 2026-06-04

Дата: 04.06.2026
Статус: ready-for-owner-review / approvals pending
Execution board: `docs/workflow/product-tech-challenge-execution-board-2026-06-04.md`
Evidence intake: `docs/workflow/product-tech-challenge-evidence-intake-2026-06-04.md`
Owner status tracker: `docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json`
Issue backlog: `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.md`
Owner review runbook: `docs/workflow/product-tech-challenge-owner-review-runbook-2026-06-04.md`

## Purpose

Этот документ нужен, чтобы владельцы быстро увидели, какие решения от них требуются по Sprint 17-23 execution board, что уже подготовлено локально и что нельзя внедрять без approval/evidence.

Он не является approval сам по себе. Owner decision должен быть записан в формате ниже and backed by safe evidence where required.

## Approval Scope

| Work package | Approval request | Primary owners | Current status |
|---|---|---|---|
| WP-01 | Approve target evidence path for product content validation | Backend + QA + Content + DevOps | ready-target-evidence |
| WP-02 | Decide product content ownership and environment automation | Content + Backend + DevOps + Architect | owner-review |
| WP-03 | Approve public/private/blocked proof, claims and evidence | PM + Sales + Legal + SEO + Security | blocked-external |
| WP-04 | Approve taxonomy, packaging and SEO route model | PM + Sales + SEO + Product + Content | owner-review |
| WP-05 | Approve CJM, CTA, CRM fallback/structured-field path and analytics | PM + UX + Sales + Backend + Security + Analytics + QA | owner-review |
| WP-06 | Approve TO BE design-system implementation readiness | Designer + Frontend + QA + Legal + PM | owner-review |
| WP-07 | Approve frontend/component hardening path and smoke baseline | Architect + Frontend + QA + Security + Backend | owner-review |
| WP-08 | Approve security/release model and legacy alias evidence path | Security + Backend + DevOps + QA + PM + Legal | blocked-external |
| WP-09 | Approve accepted-risk monitoring cadence and reopen rules | PM + Architect + Security + Frontend + QA | accepted-monitor |

## Prepared Materials

| Material | Use |
|---|---|
| `product-tech-challenge-gap-register-2026-06-04.md` | Source gap IDs, status, owner, gate and closure evidence |
| `product-tech-challenge-execution-roadmap-2026-06-04.md` | Phase ordering and do-not-start rules |
| `product-tech-challenge-execution-board-2026-06-04.md` | Issue-ready work packages and owner approval matrix |
| `product-content-schema-contract.md` | Sprint 17 product content schema/fail-fast baseline |
| `product-tech-challenge-owner-status-tracker-2026-06-04.json` | Machine-readable WP status, blockers, gap IDs and required evidence |
| `product-tech-challenge-issue-backlog-2026-06-04.md` / `.json` | Tracker-ready `PTC-WP-*` issue bodies and start policies |
| `product-tech-challenge-owner-review-runbook-2026-06-04.md` | Operational flow for owner review, issue import, evidence update and implementation handoff |
| `product-taxonomy-seo-packaging-decision-2026-06-04.md` | Sprint 18 taxonomy/SEO/packaging draft |
| `product-cjm-cta-crm-qualification-decision-2026-06-04.md` | Sprint 19 CJM/CTA/CRM/analytics draft |
| `product-to-be-design-system-decision-2026-06-04.md` | Sprint 20 TO BE design-system draft |
| `product-frontend-component-hardening-decision-2026-06-04.md` | Sprint 21 component/frontend hardening draft |
| `product-security-release-legacy-closure-decision-2026-06-04.md` | Sprint 22 security/release/legacy draft |
| `product-accepted-risk-monitoring-decision-2026-06-04.md` | Sprint 23 accepted-risk monitoring draft |
| `product-tech-challenge-evidence-intake-2026-06-04.md` | Safe evidence templates and intake rules |

## Required Owner Responses

### PM / Product

| Required response | Work package |
|---|---|
| Approve or revise product taxonomy and route intent | WP-04 |
| Approve role CJM, CTA taxonomy, returning-lead path and success-state direction | WP-05 |
| Decide which proof/status states may appear publicly | WP-03 / WP-06 |
| Approve accepted-risk review cadence | WP-09 |

### Sales

| Required response | Work package |
|---|---|
| Validate product names, one-liners, buyer triggers and packaging language | WP-04 |
| Provide or block proof/case/logo/testimonial evidence | WP-03 |
| Confirm pilot kits and Sales follow-up usefulness | WP-05 |
| Approve current lead context text fallback or request structured CRM scope | WP-05 |

### Legal

| Required response | Work package |
|---|---|
| Classify claims as public, private/NDA, rewrite, needs-evidence or blocked | WP-03 |
| Approve proof/status labels and public wording | WP-03 / WP-06 |
| Approve or block SaaS/on-prem/hybrid/PAK/SLA wording | WP-04 |
| Approve constraints for future private proof/document access | WP-08 |

### SEO

| Required response | Work package |
|---|---|
| Approve `/agents/` vs `/aiagents/` route/canonical/compatibility model | WP-04 |
| Approve product metadata and keyword/intent direction | WP-04 |
| Validate product evidence/content mapping for indexable clusters | WP-03 / WP-04 |

### Designer

| Required response | Work package |
|---|---|
| Approve TO BE token source and design-system mapping | WP-06 |
| Approve enterprise density, radius, card and palette policy | WP-06 |
| Approve proof/status UI, diagrams and form/chat/price states | WP-06 |
| Confirm whether component previews/fixtures are needed | WP-07 |

### Architect

| Required response | Work package |
|---|---|
| Approve product content ownership and renderer order baseline | WP-02 / WP-07 |
| Approve partial-to-component promotion criteria | WP-07 |
| Decide whether automation/environment changes need ADR | WP-02 |
| Confirm stack accepted-risk triggers | WP-09 |

### Frontend

| Required response | Work package |
|---|---|
| Approve JS module policy and contract-preserving split plan | WP-07 |
| Approve design-system implementation feasibility | WP-06 |
| Approve asset guard model and accepted-risk triggers | WP-09 |

### Backend

| Required response | Work package |
|---|---|
| Approve product content validation/evidence path | WP-01 |
| Approve product content ownership and environment automation approach | WP-02 |
| Approve CRM/upstream fallback or structured-field scope | WP-05 |
| Approve endpoint sensitivity/rate class model | WP-08 |

### Security

| Required response | Work package |
|---|---|
| Approve procurement/security journey wording constraints | WP-05 |
| Approve CSRF accepted-risk trigger list | WP-07 / WP-09 |
| Approve private proof/document access model before implementation | WP-08 |
| Approve CSP report-only to enforce checklist and endpoint classes | WP-08 |

### QA

| Required response | Work package |
|---|---|
| Approve target product content evidence and negative fixture path | WP-01 |
| Approve form/chat/price/component fixture and smoke map | WP-06 / WP-07 |
| Approve release evidence extension and no-PII discipline | WP-08 |
| Approve accepted-risk review integration with sign-off | WP-09 |

### DevOps

| Required response | Work package |
|---|---|
| Provide target Bitrix/PHP runtime evidence path | WP-01 |
| Approve source switch/cache/environment ownership | WP-02 |
| Provide legacy alias access-log full-window aggregate after `2026-06-30` | WP-08 |
| Confirm trusted proxy/IP allowlist ownership if future sensitive endpoints use it | WP-08 |

### Content

| Required response | Work package |
|---|---|
| Approve product content lifecycle and ownership | WP-01 / WP-02 |
| Tag product evidence across cases/offers/FAQ/services | WP-03 |
| Approve metadata/content updates after SEO/PM decision | WP-04 |

### Analytics

| Required response | Work package |
|---|---|
| Approve no-PII product funnel goal map | WP-05 |
| Confirm Metrika/analytics evidence format without raw params | WP-05 |

## Approval Output Format

Owners should return decisions in this format:

```text
Work package:
Gap IDs:
Owner:
Status:
Approved decision:
Blocked decision:
Approved public wording:
Private/NDA wording:
Evidence/source ID:
Implementation impact:
Required follow-up:
Due date:
```

Allowed statuses:

- `approved`;
- `approved-v1-safe`;
- `rewrite-required`;
- `evidence-blocked`;
- `deferred`;
- `rejected`;
- `accepted-monitor`.

## Cross-Owner Decisions

| Decision | Required owners | Safe default until approval |
|---|---|---|
| Product taxonomy names and boundaries | PM + Sales + Architect | Keep current Platform / Agents / Dev / Forum draft |
| `/agents/` vs `/aiagents/` | PM + SEO + Product | Keep separate self-canonical routes; no redirect/canonical change |
| Public proof/status UI | PM + Sales + Legal + Designer | Show only safe readiness/request copy, no logos/metrics/certifications |
| Packaging terms | PM + Sales + Legal + Architect | Use request/discussion wording, not fixed public offers |
| Structured CRM/upstream fields | PM + Sales + Backend + Security + QA | Keep existing text fallback inside `task` |
| TO BE design implementation | Designer + Frontend + QA + Legal | Do not start broad restyle |
| `/price/` mobile rewrite | Designer + Frontend + QA | Keep current flow and smoke contract |
| CSP enforce | Security + Frontend + QA + DevOps | Keep report-only |
| Legacy alias final mode | Backend + DevOps + PM + Architect | Keep aliases with sunset monitoring |

## Closure Rule

This approval request can move from `ready-for-owner-review` to `review-complete` only when:

- every WP-01 - WP-09 has owner status;
- every blocked decision has owner, reason and due date;
- evidence is recorded through `product-tech-challenge-evidence-intake-2026-06-04.md`;
- no PII/raw payload/confidential evidence is committed;
- `product-tech-challenge-owner-status-tracker-2026-06-04.json` is updated after each owner response or evidence status change;
- `product-tech-challenge-issue-backlog-2026-06-04.json` stays aligned after any status, owner, blocker or evidence update;
- source register statuses are updated only where actual evidence justifies it;
- `npm run product:challenge:check` passes, with individual challenge guards available for diagnostics.
