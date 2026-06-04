# Sprint 20 — TO BE Design System

Дата формирования: 04.06.2026
Статус: in-progress; TO BE decision package draft added
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Roadmap: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`

## Sprint Goal

Подготовить TO BE дизайн-систему к реализации: token source, enterprise density rules, component/state specs, proof/status UI, architecture diagrams, `/price/` mobile behavior and AS IS -> TO BE traceability.

## Capacity / Constraints

- Production freeze: implementation should not start before design decisions are approved.
- Known dependencies: Sprint 17 proof/claims matrix, Sprint 18 taxonomy/packaging, Sprint 19 CJM/CTA.
- Agents / roles:
  - Designer: token source, component family, states, visual system;
  - Frontend: Tailwind/global CSS mapping, implementation constraints;
  - PM: product narrative and component priorities;
  - QA: state coverage and responsive acceptance criteria;
  - Architect: diagram correctness and component boundaries;
  - Legal: proof/status visual constraints.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| S20-001 | `UI-001` TO BE token source | Full Feature | Designer + Frontend | P1 | blocked | Hybrid token-source recommendation drafted; approval pending |
| S20-002 | `UI-002` Enterprise density/radius/card policy | Full Feature | Designer + PM | P1 | in-progress | Density/card/radius policy drafted |
| S20-003 | `UI-003` Palette/gradient policy | Full Feature | Designer | P2 | in-progress | Restrained palette/gradient boundaries drafted |
| S20-004 | `STACK-003` CSS/token pipeline policy | Full Feature | Frontend + Designer | P1 | in-progress | Tailwind/global CSS pipeline policy drafted |
| S20-005 | `UI-004` Hero/page visual taxonomy | Full Feature | Designer + Frontend | P2 | in-progress | Product/service/proof/operational taxonomy drafted |
| S20-006 | `UI-005` Proof/status UI | Full Feature | Designer + Legal + PM | P1 | blocked | Evidence-state UI rules drafted; Legal/PM approval pending |
| S20-007 | `UI-006` Architecture/procurement diagrams | Full Feature | Designer + Architect | P2 | in-progress | Diagram patterns and safe labels drafted |
| S20-008 | `UI-007` Form/modal/CTA state matrix | Full Feature | Designer + QA + Frontend | P1 | in-progress | State matrix drafted |
| S20-009 | `UI-008` Chat visual/state spec | Full Feature | Designer + Frontend + QA | P2 | in-progress | Chat state matrix drafted |
| S20-010 | `UI-009` `/price/` team-builder mobile flow | Full Feature | Designer + Frontend + QA | P1 | in-progress | Summary-first mobile direction drafted |
| S20-011 | `UI-010` Icon taxonomy | Full Feature | Designer + Frontend | P3 | in-progress | RemixIcon v1 taxonomy drafted |
| S20-012 | `CMP-008` Design handoff traceability | Full Feature | Designer + Frontend + PM | P2 | in-progress | Traceability table drafted and linked |

## Out Of Scope

- Implementing the visual redesign in code.
- Rewriting CSS or JS before state/contract approval.
- Publishing proof/status components without Sprint 17 Legal/claims evidence.
- Changing form/chat payloads.
- Adding new icon library without asset policy and implementation plan.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | Conditional | Required if token source or component system becomes a new architecture contract |
| Design | Yes | Primary gate for all sprint outputs |
| QA early | Yes | State matrix and responsive behavior must be testable |
| SEO | Conditional | Hero/copy/component changes must preserve heading/schema/canonical constraints |
| Legal/Claims | Yes | Proof/status visuals cannot overstate evidence |
| Post-deploy smoke | Yes if implementation follows | Visual/browser/SEO smoke required in implementation sprint |

## Acceptance Criteria

1. Token source is approved: Figma variables, JSON, Tailwind mapping or explicit hybrid.
2. Density/radius/card policy defines when cards are allowed, target radius, spacing, panels, comparison layouts and proof blocks.
3. Palette/gradient policy prevents one-note product marketing style and sets usage boundaries.
4. CSS/token pipeline explains how TO BE tokens map to Tailwind source and `styles/global.css`.
5. Hero/page visual taxonomy covers product, service, proof, operational and legacy-compatible pages.
6. Proof/status UI has public/private/pending/blocked states tied to Sprint 17 claim matrix.
7. Architecture diagrams have desktop/mobile patterns and safe labels.
8. Form/modal/CTA state matrix covers normal, loading, validation, consent, CSRF/origin failure, success and retry.
9. Chat state spec covers long answers, errors, retry, prefill, handoff and mobile behavior.
10. `/price/` mobile flow decision is documented: modal/inline/summary-first, state transitions and smoke needs.
11. Icon taxonomy and asset policy are documented.
12. AS IS -> TO BE traceability links design tokens, component state contract, migration map and challenge sprint outputs.

## QA / Smoke Scope

| Scenario | Artifact | Expected |
|---|---|---|
| Token mapping | design token contract | Figma/JSON/Tailwind mapping is testable |
| Forms | state matrix | Each error/success/loading state has expected visible behavior |
| Chat | state matrix | Long answers and failures have non-overlapping responsive behavior |
| Price mobile | UX spec | Team-builder state is stable and smokeable |
| Proof UI | proof/status spec | Evidence state cannot imply unapproved proof |
| Diagrams | responsive spec | Mobile fallback remains readable |

## Verification

### Automated

```bash
npm run design:tokens:check
npm run design:components:check
npm run design:migration:check
npm run design:handoff:check
npm run product:gaps:check
```

If implementation follows:

```bash
npm run css:check
npm run template-styles:check
npm run e2e:css-js:local
npm run visual:smoke
```

### Manual / Owner Evidence

- Designer approval of TO BE component/state package.
- Frontend approval of implementation feasibility.
- QA approval of state smoke coverage.
- Legal approval of proof/status visual constraints.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Design system ignores Bitrix/Tailwind constraints | Designer + Frontend | Token mapping and CSS pipeline before implementation |
| Proof/status UI overstates evidence | Designer + Legal | Tie every state to claim-source matrix |
| `/price/` mobile spec breaks existing builder behavior | Designer + QA | Preserve current selectors and smoke contracts |
| State matrix is too abstract for QA | QA + Designer | Include concrete visible expected behavior |
| Icon change adds asset/dependency debt | Frontend | Asset policy and guard before migration |

## Definition Of Done

- `UI-001` - `UI-010`, `STACK-003`, `CMP-008` have approved design/system outputs or explicit blocker.
- TO BE implementation can start only for components whose decisions are approved.
- Design docs preserve existing behavior-bearing selectors and contracts unless migration is explicit.
- Legal/proof constraints are visible before any proof/status component is implemented.

## Sprint Review

### Done

- Added `docs/workflow/product-to-be-design-system-decision-2026-06-04.md` as Sprint 20 approval package.
- Drafted hybrid token source recommendation: Figma variables, repo-owned token JSON bridge and Tailwind/global CSS runtime mapping.
- Drafted enterprise density/radius/card policy for product/procurement UI.
- Drafted restrained palette/gradient policy and status-color gating.
- Drafted CSS/token pipeline policy that preserves current Tailwind source, `tailwind.generated.css`, `styles/global.css` and `template_styles.css` guard model.
- Drafted hero/page visual taxonomy for product, service, proof, operational and compatibility pages.
- Drafted proof/status UI state rules tied to evidence statuses: `public-safe`, `private-evidence`, `pilot-artifact`, `pending`, `blocked`.
- Drafted architecture/procurement diagram patterns and safe labels.
- Drafted form/modal/CTA and chat state matrices.
- Drafted summary-first `/price/` mobile team-builder direction while preserving `price-specialist`, `workers_json`, presets and smoke needs.
- Drafted RemixIcon v1 icon taxonomy and migration gate.
- Drafted AS IS -> TO BE traceability table and linked the decision pack from design handoff and workflow docs.

### Not Done

- Designer/Frontend approval of token source and naming/mapping remains pending.
- Legal/PM approval of proof/status labels, colors and copy remains pending.
- QA approval of form/chat/price state smoke coverage remains pending.
- No CSS, JS, token JSON, Figma export, markup or visual runtime implementation was made.

### Follow-Up

- Collect Designer/Frontend/QA/Legal approval against `product-to-be-design-system-decision-2026-06-04.md`.
- If implementation starts, update AS IS -> TO BE mapping for affected components and run design, CSS, JS and visual/browser smoke.
- Feed proof/status component design only after Sprint 17/18 claim evidence approval.
