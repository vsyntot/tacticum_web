# Sprint 09 Approval Request

Дата: 02.06.2026

Статус: ready-for-owner-review. Документ нужен, чтобы PM/Sales/Legal/Security/SEO могли быстро понять, какие решения от них требуются, что уже подготовлено локально and what remains externally blocked.

## Request Summary

Sprint 09 asks owners to approve or block four decisions before TO BE design and implementation proceed:

| Decision | What owner must provide | Primary owner | Current local status |
|---|---|---|---|
| `D-01` Product taxonomy and one-liners | Approve names, descriptors, one-liners and Platform triggers | PM + Sales + Architect | baseline drafted |
| `D-02` Agents / Forum / `/aiagents/` | Approve boundary copy and SEO direction | PM + Product + SEO | baseline drafted |
| `D-03` Proof and claims split | Classify claims and approve public/private/blocked wording | PM + Sales + Legal + Security | intake template drafted |
| `D-04` Packaging language | Approve safe public packaging vocabulary | PM + Sales + Architect + Legal | baseline drafted |

## Prepared Materials

| Material | Use |
|---|---|
| `sprint-09-product-taxonomy-claims-packaging.md` | Sprint scope, gates and acceptance criteria |
| `sprint-09-review-workbook.md` | Review agenda and worksheets |
| `sprint-09-decision-records.md` | Draft decision records with recommended v1 baseline |
| `sprint-09-evidence-intake.md` | Evidence/source intake template for proof, claims and packaging |
| `../18-phase-1-product-decision-review-pack.md` | Source review tables for Phase 1 product decisions |
| `../07-risk-and-claims-register.md` | Source list of risky claims |

## Required Owner Responses

### PM / Product Owner

| Required response | Decision |
|---|---|
| Approve or revise product taxonomy and umbrella phrase | `D-01` |
| Confirm Platform is positioned as product, architecture foundation or both | `D-01` |
| Approve Agents/Forum public boundary | `D-02` |
| Approve which proof/status states may appear publicly | `D-03` |
| Approve public packaging language and release scope implications | `D-04` |

### Sales

| Required response | Decision |
|---|---|
| Confirm one-liners match sales discovery language | `D-01` |
| Confirm proof/evidence desired for buyer trust | `D-03` |
| Provide or block customer logo/testimonial/case evidence | `D-03` |
| Confirm packaging terms do not conflict with commercial model | `D-04` |

### Architect

| Required response | Decision |
|---|---|
| Approve Platform top buying triggers | `D-01` |
| Confirm deployment, on-prem, hybrid and PAK wording feasibility | `D-03`, `D-04` |
| Identify wording that needs technical evidence or ADR before public use | `D-03`, `D-04` |

### Legal

| Required response | Decision |
|---|---|
| Classify registry, trusted software, PAK, SLA, tax, logo and testimonial claims | `D-03`, `D-04` |
| Approve allowed public wording or mark private/blocked | `D-03`, `D-04` |
| Confirm no proof/status UI can imply unsupported legal status | `D-03` |

### Security

| Required response | Decision |
|---|---|
| Approve safe procurement/security wording | `D-03` |
| Block or rewrite FSTEC/FSB/KII/FZ-152/FZ-187 claims without evidence | `D-03` |
| Confirm deployment/security wording for SaaS/on-prem/hybrid | `D-04` |

### SEO

| Required response | Decision |
|---|---|
| Approve Agents/Forum cross-linking and duplication risk handling | `D-02` |
| Decide whether `/aiagents/` stays differentiated for v1 or needs canonical/redirect analysis | `D-02` |
| Confirm metadata implications for approved taxonomy | `D-01`, `D-02` |

## Recommended V1 Decisions To Review

### D-01

Use public product names:

- `Tacticum Platform`;
- `Tacticum Agents`;
- `Tacticum Dev`;
- `Tacticum Forum`.

Use umbrella phrase:

```text
Tacticum - корпоративная AI-экосистема: Platform, Agents, Dev and Forum.
```

Platform top triggers:

1. Several AI/RAG/bot initiatives duplicate infrastructure.
2. Enterprise environment needs shared access, audit, data and provider governance.
3. Pilot must have a path to production decision, not stay a script/demo.

### D-02

Recommended safe v1:

```text
/agents/ is the primary product URL for Tacticum Agents.
/aiagents/ remains compatibility/demo/legacy AI-bot entry until SEO traffic, rankings and lead evidence are reviewed.
No canonical or redirect change without SEO approval and rollback plan.
```

### D-03

Recommended safe v1:

- public site can show pilot artifacts and proof readiness;
- public site must not show unapproved registry, regulatory, performance, logo, SLA, connector or workforce-reduction proof;
- proof/status UI must map to approved evidence status.

### D-04

Recommended safe v1:

- public site can describe assessment, limited pilot, implementation and rollout decision;
- SaaS/on-prem/hybrid/PAK/SLA are options for architecture/commercial discussion, not confirmed universal public offers;
- `/price/` remains team/project composition, not product license pricing.

## Approval Output Format

Owners should return decisions in this format:

```text
Decision ID:
Owner:
Status:
Approved wording:
Blocked wording:
Evidence/source:
Private/NDA wording:
Implementation impact:
Required follow-up:
```

Allowed statuses:

- `approved`;
- `approved-v1-safe`;
- `rewrite-required`;
- `evidence-blocked`;
- `deferred`;
- `rejected`.

## External Blockers

| Blocker | Why local team cannot close it |
|---|---|
| Customer/logo/testimonial proof | Requires written owner/customer permission |
| Registry/trusted software status | Requires legal/source evidence |
| FSTEC/FSB/KII/regulatory wording | Requires Legal/Security approval |
| `/aiagents/` SEO decision | Requires traffic/ranking/lead review |
| SLA/PAK/public packaging | Requires commercial/legal/architecture approval |

## Closure Rule

Sprint 09 can move from `ready-for-owner-review` to `completed` only when:

- all four decision records have owner statuses;
- evidence blockers have named owner and due date;
- public wording changes are listed;
- `14-gap-backlog-and-decision-register.md` is updated only where evidence justifies status change;
- `npm run product:gaps:check` remains green.
