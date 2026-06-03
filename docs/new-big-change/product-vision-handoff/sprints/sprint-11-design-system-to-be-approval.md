# Sprint 11 - TO BE Design System And Component Approval

Suggested duration: 2 weeks

Status: ready-for-owner-review / Sprint 09-10 approvals pending

## Sprint Goal

Перевести checked AS IS design handoff and post-challenge UX/UI findings в approved TO BE design-system package: tokens, component family, proof/status UI, states, diagrams and migration decisions.

This sprint makes visual implementation possible without breaking current Bitrix/JS/form contracts.

## Workflow Lane

Full Feature Lane with mandatory Design gate and Frontend/QA review.

## Source Decisions And Gaps

| Decision | Existing gaps |
|---|---|
| `D-07` TO BE token source and mapping | `UI-001` |
| `D-08` Product component family and states | `UI-002`, `UI-003`, `UI-007`, `UI-008` |
| `D-09` Architecture diagrams and proof/status UI | `UI-004`, `UI-005`, `ARCH-002` |

Related gaps: `PB-005`, `PB-006`, `CJM-001` - `CJM-006`.

## Inputs

- Sprint 09 claims/proof decisions.
- Sprint 10 pilot kits and CTA taxonomy.
- `../12-ux-ui-component-target.md`
- `../22-phase-2-design-system-approval-pack.md`
- `../26-post-challenge-ux-ui-design-system.md`
- `../../../design-system-handoff/README.md`
- `../../../design-system-handoff/05-design-tokens-as-is.json`
- `../../../design-system-handoff/07-component-state-contract.json`
- `../../../design-system-handoff/08-as-is-to-be-migration-map.json`
- `../../../design-system-handoff/09-to-be-design-work-order.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S11-001 | Approve TO BE token source: Figma variables, token JSON or documented mapping | Designer + Frontend | P1 | ready-for-owner-review draft |
| S11-002 | Map AS IS token contract to TO BE names and semantic groups | Designer + Frontend | P1 | ready-for-owner-review draft |
| S11-003 | Approve product component family: hero, fit guide, pilot kit, diagram, comparison, procurement, rollout, proof, CTA | Designer + PM + Frontend | P1 | ready-for-owner-review draft |
| S11-004 | Design form/modal/CTA states preserving `[data-tacticum-form]` contracts | Designer + QA + Frontend | P1 | ready-for-owner-review draft |
| S11-005 | Design chat visual states preserving current selectors and scroll behavior | Designer + Frontend + QA | P2 | ready-for-owner-review draft |
| S11-006 | Design `/price/` mobile team-builder flow preserving `data-price-*` and `workers_json` | Designer + Frontend + QA | P2 | ready-for-owner-review draft |
| S11-007 | Approve architecture/data-flow diagram patterns and mobile fallback | Designer + Architect | P2 | ready-for-owner-review draft |
| S11-008 | Approve proof/status UI taxonomy and visuals | Designer + Legal + PM | P1 | ready-for-owner-review draft |
| S11-009 | Update AS IS -> TO BE migration map if component names or migration types change | Designer + Frontend | P1 | pending owner decisions |

## Out Of Scope

- Production implementation.
- New frontend framework.
- New form endpoints.
- Publishing proof metrics/logos without Sprint 09 approval.
- CRM/upstream field migration.

## Local Preparation Package

Sprint 11 local scaffolding is now prepared for owner review:

| Artifact | Purpose | Status |
|---|---|---|
| `sprint-11-review-workbook.md` | Review agenda, decision worksheets and closure checklist for `D-07` - `D-09` | ready for review session |
| `sprint-11-decision-records.md` | Draft decision records for token source, component family, diagrams and proof/status UI | ready-for-owner-review drafts |
| `sprint-11-state-matrix.md` | Detailed state matrix for behavior-bearing components, selectors and QA implications | ready-for-owner-review draft |
| `sprint-11-approval-request.md` | Owner-facing approval request for Designer, Frontend, QA, PM, Legal and Architect | ready for owner handoff |

This package does not close `UI-*`, `PB-*` or `ARCH-002` gaps. It removes local documentation scaffolding as a blocker; Figma/token/component artifacts, Frontend feasibility, QA state coverage, Legal proof/status approval and Architect diagram approval remain external owner gates.

## Deliverables

- Approved token source and AS IS -> TO BE token mapping.
- TO BE component family and variants.
- Desktop/mobile page templates for homepage and product pages.
- State matrix for forms, modal, FAQ, chat, `/price/` and product components.
- Architecture diagram patterns.
- Proof/status component spec.
- Updated `08-as-is-to-be-migration-map.json` if needed.
- Frontend feasibility note.

## Gates

| Gate | Required | Notes |
|---|---|---|
| Design | Yes | Main sprint gate |
| Frontend | Yes | Must confirm Tailwind/global CSS and Bitrix feasibility |
| QA early | Yes | Required for forms, chat, `/price/`, modal, FAQ states |
| Legal | Conditional | Required for proof/status visuals |
| Security / Integration | Conditional | Required if design introduces new payload/data behavior |
| ADR | Conditional | Required if component architecture changes become shared pattern |

## Acceptance Criteria

1. Token source and mapping are approved by Designer and Frontend.
2. TO BE components map to AS IS contracts or have explicit migration decisions.
3. Product pages are not four identical visual skins.
4. Proof/status visuals cannot overstate unavailable evidence.
5. All behavior-bearing components have visual states.
6. `/price/` mobile and chat states are design-specified.
7. Frontend confirms implementation path without runtime Tailwind or inline JS/CSS.
8. `npm run design:handoff:check` remains green after any handoff changes.

## Verification

- `npm run design:tokens:check`
- `npm run design:components:check`
- `npm run design:migration:check`
- `npm run design:handoff:check`
- `npm run product:gaps:check`

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Figma diverges from AS IS contracts | Designer + Frontend | Require migration map update before close |
| Proof visuals look like confirmed metrics | Designer + Legal | Use approved status taxonomy only |
| `/price/` mobile redesign breaks smoke assumptions | Frontend + QA | Preserve hidden fields and update QA plan before implementation |
| Diagrams become decorative | Designer + Architect | Require data/runtime/access boundaries |

## Sprint Review

### Done

- Local review workbook for `D-07` - `D-09` prepared.
- Draft decision records for token source, component family, diagrams and proof/status UI prepared.
- State matrix prepared for navigation, forms, modal, chat, FAQ, `/price/`, product blocks, proof/status and diagrams.
- Owner-facing approval request prepared.

### Not Done

- Token source, Figma variables and AS IS -> TO BE token mapping are not approved.
- TO BE component library, variants and page templates are not approved.
- Proof/status visuals are not Legal/PM approved and still depend on Sprint 09 claims/evidence.
- PilotKitCard and CTA hierarchy still depend on Sprint 10 owner approval.
- Architecture diagram patterns are not Architect approved.
- No production UI implementation has started.

### Follow-Up

- Run owner review using `sprint-11-review-workbook.md`.
- Update `sprint-11-decision-records.md` with owner statuses.
- Update `sprint-11-state-matrix.md` and `08-as-is-to-be-migration-map.json` only after Designer + Frontend approve changes.
- Move implementation-ready slices to Sprint 13 only after required design/frontend/QA/legal gates are satisfied.
