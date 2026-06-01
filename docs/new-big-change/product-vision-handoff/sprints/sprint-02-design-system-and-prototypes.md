# Sprint 02 - Design System And Prototypes

Suggested window: 22.06.2026 - 03.07.2026

Status: planned

## Sprint Goal

Подготовить TO BE дизайн-систему, прототипы главной и продуктовых страниц, а также migration map от AS IS компонентов к новым компонентам.

## Workflow Lane

Full Feature Lane with mandatory Design gate.

## Source Gaps

- `PV-003` Homepage
- `PV-004` Product pages
- `PV-005` Platform proof
- `PV-010` Design system
- `PV-011` Interaction

## Inputs

- Sprint 00 approved claims.
- Sprint 01 IA and page briefs.
- `../05-design-and-content-brief.md`
- `../../../design-system-handoff/README.md`
- `../../../design-system-handoff/02-component-inventory.md`
- `../../../design-system-handoff/04-interaction-contracts.md`
- `../../index.html` as visual hypothesis, not final source of truth.

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S02-001 | Define TO BE tokens: color, typography, spacing, radius, elevation, motion | Designer | P1 | planned |
| S02-002 | Design homepage desktop/mobile | Designer + PM | P1 | planned |
| S02-003 | Design product page template desktop/mobile | Designer + PM | P1 | planned |
| S02-004 | Apply template to Platform, Agents, Dev, Forum | Designer + PM + Editor | P1 | planned |
| S02-005 | Design ecosystem/platform architecture diagrams | Designer + Architect | P1 | planned |
| S02-006 | Design proof/claim components with source/status behavior | Designer + PM + Legal | P1 | planned |
| S02-007 | Design product-aware lead CTA and form states | Designer + PM + QA | P1 | planned |
| S02-008 | Specify interactive states: nav, tabs, accordion, modal, forms, chat | Designer + Frontend | P1 | planned |
| S02-009 | Create AS IS -> TO BE component migration map | Designer + Frontend | P1 | planned |
| S02-010 | Feasibility review against Bitrix/current JS contracts | Frontend + Designer | P1 | planned |

## Out Of Scope

- Production code.
- Final copy for every text block.
- New illustrations that require legal/customer approval.
- Interactive demo engineering.
- Changes to REST endpoints.

## Deliverables

- TO BE token spec.
- Component inventory for implementation.
- Homepage design.
- Product page template.
- Product-specific designs for Platform, Agents, Dev, Forum.
- Interaction/state spec.
- Responsive rules.
- Claim/proof component rules.
- AS IS -> TO BE component migration map.
- Feasibility notes for implementation.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | No | Unless design requires architecture changes |
| Design | Yes | Main gate of sprint |
| QA early | Conditional | For form and interactive states |
| SEO | Conditional | Header/H1/content hierarchy review |
| Security / Integration | Conditional | Only for form fields and proof/security blocks |

## Acceptance Criteria

1. Designs cover desktop and mobile.
2. Text fits expected containers and no section relies on unbounded hero-scale typography.
3. Product pages are not visually identical service cards; they explain enterprise software.
4. Architecture diagrams have clear information hierarchy and can be implemented without fragile SVG-only copy.
5. Components have states: hover, focus, active, selected, loading, disabled, error, empty, success.
6. JS/data contracts to preserve or replace are explicitly listed.
7. Designer and frontend agree which components become reusable Bitrix/local components.
8. Risky claims are represented with approved or placeholder-safe copy only.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Design ignores Bitrix/current contracts | Designer + Frontend | Feasibility review before sprint close |
| Prototype becomes too decorative | PM + Designer | Tie every block to product/page acceptance |
| Proof blocks imply unapproved claims | PM + Legal | Use claim status labels and placeholders |
| Mobile states under-specified | Designer + QA | Include mobile screens for nav, forms, accordions |

## Sprint Review

### Done

- To be filled at sprint end.

### Not Done

- To be filled at sprint end.

### Follow-Up

- To be filled at sprint end.
