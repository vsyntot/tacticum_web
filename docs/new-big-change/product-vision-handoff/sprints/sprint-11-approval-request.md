# Sprint 11 Approval Request

Дата: 02.06.2026

Статус: ready-for-owner-review. Документ нужен, чтобы Designer/Frontend/QA/PM/Legal/Architect быстро увидели, какие решения требуются для закрытия Sprint 11 local review.

## Request Summary

Sprint 11 asks owners to approve or block three post-challenge decisions:

| Decision | Required owner response | Primary owner | Local status |
|---|---|---|---|
| `D-07` TO BE token source and mapping | Approve token source chain, drift decisions and AS IS -> TO BE mapping model | Designer + Frontend | draft baseline prepared |
| `D-08` Product component family and states | Approve TO BE component family, migration types and state matrix | Designer + PM + Frontend + QA | draft baseline prepared |
| `D-09` Architecture diagrams and proof/status UI | Approve diagram patterns and evidence-aware proof/status taxonomy | Designer + Architect + PM + Legal | draft baseline prepared |

## Prepared Materials

| Material | Use |
|---|---|
| `sprint-11-design-system-to-be-approval.md` | Sprint scope, gates and acceptance criteria |
| `sprint-11-review-workbook.md` | Review agenda and worksheets |
| `sprint-11-decision-records.md` | Draft records for `D-07` - `D-09` |
| `sprint-11-state-matrix.md` | Required states, selectors and QA implications |
| `../22-phase-2-design-system-approval-pack.md` | Phase 2 approval rules |
| `../26-post-challenge-ux-ui-design-system.md` | UX/UI challenge detail |
| `../../../design-system-handoff/05-design-tokens-as-is.json` | Checked AS IS token baseline |
| `../../../design-system-handoff/07-component-state-contract.json` | Checked behavior-bearing component/state baseline |
| `../../../design-system-handoff/08-as-is-to-be-migration-map.json` | Checked AS IS -> TO BE migration baseline |
| `../../../design-system-handoff/09-to-be-design-work-order.md` | Design deliverables and red lines |

## Required Owner Responses

### Designer

| Required response | Decision |
|---|---|
| Confirm source of truth for TO BE tokens | `D-07` |
| Provide or commit to Figma variables/token artifact | `D-07` |
| Approve component names and families | `D-08` |
| Confirm variants/states for product pages, forms, modal, chat, FAQ and `/price/` | `D-08` |
| Approve diagram and proof/status component anatomy | `D-09` |

### Frontend / Bitrix Developer

| Required response | Decision |
|---|---|
| Confirm token mapping can be implemented through static Tailwind/global CSS | `D-07` |
| Confirm migration types are feasible without hidden JS/PHP contract changes | `D-08` |
| Confirm preserved selectors or list required migrations | `D-08` |
| Confirm diagram/proof components can be rendered in current Bitrix/PHP component model | `D-09` |

### QA

| Required response | Decision |
|---|---|
| Confirm state matrix is testable | `D-08` |
| Mark smoke updates for form, modal, chat, FAQ, `/price/` and product blocks | `D-08` |
| Identify manual states that require staging/production evidence | `D-08`, `D-09` |

### PM / Product Owner

| Required response | Decision |
|---|---|
| Confirm product component family supports role-aware decision flow | `D-08` |
| Confirm proof/status model matches product and claims governance | `D-09` |
| Confirm design does not hide current commercial entry points | `D-08` |

### Legal

| Required response | Decision |
|---|---|
| Approve or block public `available`, `pilot-artifact`, `private-nda` proof treatments | `D-09` |
| Confirm unavailable proof cannot look like public claim | `D-09` |
| Confirm proof/source/date rules before strong proof visuals | `D-09` |

### Architect

| Required response | Decision |
|---|---|
| Approve architecture diagram patterns for Platform, Agents, Dev and Forum | `D-09` |
| Confirm diagrams show real data/runtime/access/handoff boundaries | `D-09` |
| Flag diagrams that require architecture copy rewrite | `D-09` |

## Recommended V1 Decisions To Review

### D-07

Recommended v1:

```text
Figma variables are the design source of truth.
Git keeps a reviewed token mapping or token JSON for frontend implementation.
Frontend maps approved tokens into static Tailwind/global CSS.
Runtime Tailwind and prose-only token decisions are not accepted.
```

### D-08

Recommended v1:

```text
Approve TO BE component family around NavigationShell, ContactModal, LeadCTAForm,
ChatSurface, FAQAccordion, TeamBuilder and ProductPageSystem.
Default migration type is visual-restyle or contract-preserving-split.
Contract migrations require explicit Frontend/QA/Security scope.
```

### D-09

Recommended v1:

```text
Architecture diagrams must show operational boundaries, not decorative module art.
Proof/status UI can publish pilot-artifact readiness, but cannot show metrics,
logos, certification, registry or production-readiness proof without Sprint 09 evidence.
```

## Approval Output Format

Owners should return:

```text
Decision ID:
Owner:
Status:
Approved option:
Rejected options:
Required design artifact:
Preserved selectors / migrations:
Proof or diagram constraints:
Implementation impact:
Required follow-up:
```

Allowed statuses:

- `approved`;
- `approved-v1-safe`;
- `rewrite-required`;
- `sprint-09-blocked`;
- `sprint-10-blocked`;
- `design-blocked`;
- `frontend-blocked`;
- `qa-blocked`;
- `legal-blocked`;
- `deferred`;
- `rejected`.

## External / Cross-Sprint Blockers

| Blocker | Why Sprint 11 cannot close it locally |
|---|---|
| Sprint 09 proof/claims not approved | proof/status UI cannot become stronger than safe pilot/status treatment |
| Sprint 10 pilot kits not approved | PilotKitCard copy and CTA hierarchy can still change |
| Figma variables/component library missing | design gaps cannot close without actual design artifact |
| Frontend rejects migration type | implementation needs explicit contract-migration scope |
| QA cannot test state model | implementation-ready status is premature |
| Legal blocks proof treatment | proof/status UI must be rewritten or hidden |
| Diagram requires architecture rewrite | Architect must approve the operational model first |

## Closure Rule

Sprint 11 can move from `ready-for-owner-review` to `completed` only when:

- `D-07`, `D-08` and `D-09` have owner statuses;
- Figma/token/component/state artifacts exist or blockers are assigned;
- preserved selectors and migration types are explicit;
- proof/status Legal/PM decision is recorded;
- architecture diagram Architect decision is recorded;
- QA state/smoke implications are recorded;
- no form, chat, `/price/`, analytics or PII behavior change is assumed silently;
- `npm run design:handoff:check` and `npm run product:gaps:check` remain green.
