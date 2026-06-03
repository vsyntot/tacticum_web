# Sprint 11 Review Workbook

Дата: 02.06.2026

Статус: ready-for-owner-review workbook. Документ помогает провести Design/Frontend/QA/PM review по `D-07` - `D-09`, но не является approval сам по себе.

## Цель Сессии

Превратить checked AS IS design handoff в согласуемый TO BE design-system package:

```text
D-07 token source and mapping
  -> D-08 component family and states
  -> D-09 architecture diagrams and proof/status UI
```

Сессия должна ответить не "какой стиль красивее", а можно ли на базе TO BE дизайна безопасно начинать реализацию в текущем Bitrix/PHP/vanilla JS контуре.

## Участники

| Role | Required | Decision area |
|---|---|---|
| Designer | Yes | tokens, component family, page templates, visual states |
| Frontend / Bitrix developer | Yes | Tailwind/global CSS feasibility, selectors, migration map |
| QA | Yes | form, modal, chat, FAQ, `/price/` and product block states |
| PM / Product owner | Yes | product components, proof/status treatment, CTA hierarchy |
| Legal | Required for proof/status | public proof, claims, source/status wording |
| Architect | Required for diagrams | architecture/data/runtime/access diagrams |
| Content / Editor | Recommended | component copy, labels, empty/error states |
| Backend / Analytics | Conditional | only if form payload, chat behavior or analytics params change |

## Pre-Read

Participants should read:

- `sprint-11-design-system-to-be-approval.md`
- `sprint-11-decision-records.md`
- `sprint-11-state-matrix.md`
- `sprint-11-approval-request.md`
- `../22-phase-2-design-system-approval-pack.md`
- `../26-post-challenge-ux-ui-design-system.md`
- `../../../design-system-handoff/05-design-tokens-as-is.json`
- `../../../design-system-handoff/07-component-state-contract.json`
- `../../../design-system-handoff/08-as-is-to-be-migration-map.json`
- `../../../design-system-handoff/09-to-be-design-work-order.md`

## Agenda

| Block | Duration | Output |
|---|---:|---|
| Sprint 09/10 dependency check | 10 min | Confirm taxonomy, proof and pilot-kit assumptions or mark blockers |
| D-07 token source | 25 min | Approve source chain and token groups to map |
| D-08 component family | 45 min | Approve TO BE component names, variants, preserved selectors and migration types |
| State matrix | 30 min | Approve required states and QA/smoke implications |
| D-09 diagrams and proof/status UI | 35 min | Approve diagram patterns and evidence-aware proof/status taxonomy |
| Closure | 15 min | Assign blockers, update decision records and migration map actions |

## Decision Statuses

Use these statuses in `sprint-11-decision-records.md`.

| Status | Meaning |
|---|---|
| `approved` | Owner approved and required evidence/design artifact exists |
| `approved-v1-safe` | Safe v1 path approved; stronger design/claim/interaction remains blocked |
| `rewrite-required` | Direction is acceptable but visual/copy/spec detail must change |
| `sprint-09-blocked` | Waiting on taxonomy, proof, claims or packaging decisions |
| `sprint-10-blocked` | Waiting on pilot kit, CTA or role-journey decisions |
| `design-blocked` | Figma/token/component artifact is missing |
| `frontend-blocked` | Proposed design cannot be implemented without migration scope |
| `qa-blocked` | Required states or smoke implications are not testable yet |
| `legal-blocked` | Proof/status or claim treatment needs Legal approval |
| `deferred` | Outside current release scope |
| `rejected` | Must not be used |

## D-07 Worksheet - Token Source And Mapping

### Questions To Answer

| Question | Required decision |
|---|---|
| Is Figma variables the design source of truth for v1? | approve / choose alternative |
| Will Git keep token JSON, mapping doc or both? | choose source chain |
| Are `#001F40` vs `#001F3F` and `#007bff` vs `#0066CC` resolved? | keep / rename / replace |
| Which semantic colors are required for proof/status states? | approve list |
| Can frontend map tokens to Tailwind/global CSS without runtime Tailwind? | approve / block |

### Token Groups To Review

| Group | Required TO BE decision | AS IS anchor |
|---|---|---|
| Brand | primary, navy, accent, link | `05-design-tokens-as-is.json` |
| Text/surface/border | page, card, modal, muted, inverted | Tailwind theme and `styles/global.css` |
| Status | success, warning, error, info, blocked | form and toast states |
| Proof | available, pilot-artifact, private, needs-evidence, not-supported | Sprint 09 proof taxonomy |
| Typography | dense headings, body, labels, meta/technical text | current rendered product pages |
| Spacing | section rhythm, card density, form spacing, table spacing | Tailwind spacing and component CSS |
| Radius/elevation | button, input, card, modal, diagram nodes | current forms/cards/modal |
| Focus/motion/z-index | keyboard focus, accordion/modal motion, overlays | JS interaction contracts |
| Breakpoints | mobile, tablet, desktop, wide | current Tailwind/static CSS |

### Output

- approved source chain;
- AS IS -> TO BE token mapping owner;
- drift decisions;
- frontend feasibility note;
- blocked token groups.

## D-08 Worksheet - Component Family And States

### AS IS Component Contracts To Preserve Or Migrate

| AS IS component id | Draft TO BE component | Current migration type | Required decision |
|---|---|---|---|
| `global-navigation` | `NavigationShell` | `visual-restyle` | approve grouping, dropdown/mobile states |
| `contact-modal` | `ContactModal` | `visual-restyle` | approve modal/sheet and success/error states |
| `lead-cta-form` | `LeadCTAForm` | `visual-restyle` | approve form density, CTA variants, selectors |
| `chat-surface` | `ChatSurface` | `visual-restyle` | approve long answer, quick reply, handoff states |
| `faq-accordion` | `FAQAccordion` | `visual-restyle` | approve open/closed/long-answer states |
| `price-team-builder` | `TeamBuilder` | `contract-preserving-split` | approve mobile builder without losing `workers_json` |
| `product-page-blocks` | `ProductPageSystem` | `contract-preserving-split` | approve product block family and `data-product-block` locators |

### Product Storytelling Family

| Component | Required design decision |
|---|---|
| `ProductHero` | product promise, fit signal, primary/secondary CTA, no unsupported claim |
| `FitGuide` | fits / not fits / start here, mobile scannable |
| `PilotKitCard` | trigger, owner, input, output, limitation, proof status |
| `ArchitectureDiagram` | layer/data/runtime/access diagram plus mobile fallback |
| `ComparisonBlock` | product boundary and alternatives |
| `ProcurementBlock` | security/procurement review path |
| `RolloutTimeline` | assessment -> pilot -> integration -> rollout decision |
| `ProofStatusBlock` | evidence status, source notes, pilot artifacts |
| `LeadCTAForm` | product-aware form states and scenario select |
| `ChatSurface` | initial/loading/error/long-answer/handoff states |
| `TeamBuilder` | mobile-first staff/team configurator |

### Output

- final TO BE component names;
- migration type per AS IS component;
- preserved selectors;
- required Figma variants/states;
- QA smoke implications;
- `08-as-is-to-be-migration-map.json` update list if names or migration types change.

## D-09 Worksheet - Diagrams And Proof / Status UI

### Architecture Diagram Questions

| Question | Required decision |
|---|---|
| Which diagram pattern is used for Platform? | layer map / data flow / runtime contour / hybrid |
| Which diagram pattern is used for Agents? | source/access/handoff map / assistant workflow |
| Which diagram pattern is used for Dev? | workflow trace / gates / repository context |
| Which diagram pattern is used for Forum? | scenario graph / escalation / journal / analytics |
| What is the mobile fallback? | stacked steps / table / accordion |
| Which labels must remain literal and non-decorative? | approve |

### Proof / Status UI Questions

| Question | Required decision |
|---|---|
| Is `pilot-artifact` public for v1? | approve / block |
| Can `private-nda` be shown as "available on request"? | approve / Legal block |
| How should unavailable proof appear in design files? | internal-only / hidden / explicit blocked state |
| Which status tokens map to public UI? | approve |
| Does every proof-looking card require source/date/owner? | approve |

### Allowed Status Model

| Status | Public treatment | Visual rule |
|---|---|---|
| `available` | Show only with approved source/date | strongest proof visual allowed |
| `pilot-artifact` | Show as "what we validate during pilot" | must not look like achieved result |
| `needs-evidence` | Internal/design only | do not publish as proof card |
| `private-nda` | Request-oriented copy only if approved | subdued, not metric-like |
| `not-supported` | Do not show | hidden from public UI |

### Output

- diagram pattern per product;
- proof/status component spec;
- Legal/PM constraints;
- source-note rules;
- mobile fallback decisions.

## Closure Checklist

| Item | Required before Sprint 11 close |
|---|---|
| D-07 status recorded | yes |
| D-08 status recorded | yes |
| D-09 status recorded | yes |
| Token source/mapping owner assigned | yes |
| Component family and migration decisions recorded | yes |
| State matrix approved or blockers assigned | yes |
| Proof/status Legal/PM status recorded | yes |
| Architecture diagram Architect status recorded | yes |
| `npm run design:handoff:check` green | yes |
| `npm run product:gaps:check` green | yes |

## After The Session

1. Update `sprint-11-decision-records.md` with owner statuses.
2. Update `sprint-11-state-matrix.md` where states or smoke implications change.
3. Update `../../../design-system-handoff/08-as-is-to-be-migration-map.json` only after Designer + Frontend approve name or migration changes.
4. Update product copy/UI tasks only after Sprint 09/10 and design decisions are approved.
5. Keep `UI-*`, `PB-*` and `ARCH-002` gaps open until Figma/design/frontend/QA/legal evidence exists.
