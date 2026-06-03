# 28. Post-Challenge Decision Backlog

Дата: 02.06.2026

Статус: prioritized decision backlog по итогам post-challenge. Использовать для PM/design/architecture planning before next implementation sprint.

## Назначение

Этот документ переводит challenge выводы из `24`-`27` в порядок решений. Он не заменяет `14-gap-backlog-and-decision-register.md` and `16-gap-closure-action-register.json`; он помогает спланировать, какие решения закрывать первыми.

## Decision Principles

1. Сначала закрывать решения, которые могут сделать публичный сайт неверным или рискованным.
2. Не начинать визуальный TO BE дизайн без product taxonomy, proof/claims and component-state constraints.
3. Не менять form/upstream payload without Security / Integration lane.
4. Не публиковать proof/status UI without evidence status.
5. Не считать release-ready без external evidence.

## Priority Decision Board

| ID | Decision | Existing gaps | Owner | Priority | Output |
|---|---|---|---|---:|---|
| D-01 | Product taxonomy and public one-liners | `PB-001`, `PB-002` | PM + Sales + Architect | P1 | approved names, one-liners, top Platform triggers |
| D-02 | Agents / Forum / `/aiagents/` boundary | `PB-003`, `PB-008`, `SEO-TOBE-002` | PM + SEO + Product | P1 | boundary copy and canonical/compatibility decision |
| D-03 | Proof and claims public/private split | `PB-005`, `PB-006`, `UI-005`, `SEO-TOBE-003` | PM + Sales + Legal + Security | P0 | evidence matrix and approved safe wording |
| D-04 | Packaging language | `PB-007` | PM + Sales + Architect + Legal | P1 | public/private/NDA packaging matrix |
| D-05 | Product pilot kits | `CJM-003`, `PB-004`, `PB-002` | PM + Content + Sales | P1 | approved pilot kits for four products |
| D-06 | Role-based CTA and returning journey | `CJM-004`, `CJM-005`, `CJM-006` | PM + UX + Sales | P2 | CTA taxonomy and returning-lead path |
| D-07 | TO BE token source and mapping | `UI-001` | Designer + Frontend | P1 | Figma variables or token source plus AS IS mapping |
| D-08 | Product component family and states | `UI-002`, `UI-003`, `UI-007`, `UI-008` | Designer + PM + QA | P1/P2 | Figma components, variants, states and migration map |
| D-09 | Architecture diagrams and proof/status UI | `UI-004`, `UI-005`, `ARCH-002` | Designer + Architect + Legal | P2 | diagram patterns and status taxonomy |
| D-10 | Product data/component architecture v1 | `ARCH-001`, `ARCH-002` | Architect + Frontend + Content | P2 | Git-only v1 approval or ADR scope |
| D-11 | CRM/upstream qualification | `ARCH-003`, `CJM-006` | Backend + PM + QA | P1 | fallback approval or structured field contract |
| D-12 | Product analytics and Metrika evidence | `ARCH-004`, `REL-004` | PM + Analytics + QA | P2/P1 release | goal map and external evidence |
| D-13 | Release evidence closure | `REL-001` - `REL-006`, `ARCH-007`, `ARCH-008` | QA + DevOps + Backend + Admin | P1 | strict sign-off evidence |

## Recommended Review Sequence

### Session 1 - Product And Claims

Participants: PM, Sales, Legal, Security, Architect, SEO.

Decisions:

- D-01 taxonomy and Platform triggers;
- D-02 Agents / Forum / `/aiagents/`;
- D-03 proof and claims split;
- D-04 packaging language.

Exit criteria:

- product names and one-liners are approved or rewritten;
- proof matrix has public/private/blocked statuses;
- risky deployment/regulatory wording is blocked or approved;
- `/agents/` vs `/aiagents/` has SEO decision path.

### Session 2 - Use Cases And CJM

Participants: PM, UX, Sales, Content, Security where needed.

Decisions:

- D-05 product pilot kits;
- D-06 role-based CTA and returning journey.

Exit criteria:

- each product has approved pilot kits;
- use cases map to real discovery conversations;
- role journeys are explicit;
- no new payload fields are assumed without `D-11`.

### Session 3 - Design System

Participants: Designer, Frontend, PM, QA, Legal for proof/status.

Decisions:

- D-07 token source;
- D-08 component family and states;
- D-09 diagrams and proof/status UI.

Exit criteria:

- Figma/token source exists;
- AS IS -> TO BE mapping is updated where needed;
- form/chat/modal/FAQ/team-builder states are specified;
- proof/status visuals cannot overstate evidence.

### Session 4 - Architecture, CRM And Analytics

Participants: Architect, Frontend, Backend, PM, QA, Analytics.

Decisions:

- D-10 product data/component architecture;
- D-11 CRM/upstream qualification;
- D-12 analytics goals.

Exit criteria:

- v1 keeps Git data/partials or ADR scope is opened;
- Sales confirms fallback context is enough or structured fields are scoped;
- Metrika goal map is approved without PII.

### Session 5 - Release Evidence

Participants: QA, DevOps, SEO, Backend, Marketing, Admin, PM.

Decisions:

- D-13 release evidence closure.

Exit criteria:

- deploy/cache smoke evidence exists;
- rendered SEO evidence exists;
- manual success-flow evidence exists without PII;
- Metrika/admin/upstream/legacy inventory gates have owners and artifacts.

## Implementation Order

| Phase | Can be done locally? | Work |
|---|---|---|
| 1. Decision closure | Partly | update docs, copy, gap statuses only after owner approval |
| 2. Design system approval | External + local docs | Figma/token/component/state work; update handoff maps |
| 3. Safe product copy refinement | Yes after approvals | update product data safe wording and use-case pilot kits |
| 4. UI implementation | Yes after design | implement visual restyle preserving contracts |
| 5. Architecture changes | Depends | ADR/security lane for CMS, components, CRM payload, analytics changes |
| 6. Release evidence | External | deploy/manual/admin/Metrika/upstream sign-off |

## Do Not Start Until

| Work | Blocker |
|---|---|
| Proof/status UI with metrics/logos | `D-03` approved evidence |
| `/agents/` redirect/canonical changes | `D-02` SEO decision |
| Product pricing/packaging publication | `D-04` PM/Sales/Legal approval |
| Structured CRM/upstream fields | `D-11` Security / Integration scope |
| TO BE visual implementation | `D-07` and `D-08` approved |
| `/price/` mobile rewrite | `UI-006` design + QA state spec |
| Chat behavior changes | `UI-007` design + QA + integration approval if payload changes |
| Bitrix/hybrid product content model | `D-10` ADR |

## Decision Record Template

Use this template inside the relevant review pack or issue.

```text
Decision:
Owner:
Date:
Related gaps:
Approved option:
Rejected options:
Evidence/source:
Public wording:
Private/NDA wording:
Implementation impact:
Required docs/ADR updates:
Required QA/release evidence:
```

## Ready For Implementation

A TO BE implementation slice is ready only when:

- related decision IDs are approved;
- affected gap IDs remain covered in `16-gap-closure-action-register.json`;
- Design gate and ADR gate are checked;
- acceptance criteria are explicit;
- existing forms/chat/FAQ/price/menu contracts are preserved or migration is scoped;
- SEO/canonical/schema impact is known;
- release evidence requirements are listed before coding starts.
