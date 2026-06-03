# Sprint 11 Decision Records

Дата: 02.06.2026

Статус: ready-for-owner-review draft decision records. These records contain recommended v1 baseline, not Designer/Frontend/QA/Legal approval. Use `sprint-11-review-workbook.md`, `sprint-11-state-matrix.md` and `sprint-11-approval-request.md` to run the review and then update statuses here.

## Status Legend

| Status | Meaning |
|---|---|
| `draft` | prepared for review, not approved |
| `approved` | approved by accountable owner |
| `approved-v1-safe` | safe v1 path approved while stronger behavior or claim remains blocked |
| `rewrite-required` | direction accepted, spec or wording must change |
| `sprint-09-blocked` | waiting on taxonomy, proof, claims or packaging decisions |
| `sprint-10-blocked` | waiting on pilot kit, CTA or role-journey decisions |
| `design-blocked` | Figma/token/component artifact is missing |
| `frontend-blocked` | implementation needs migration scope before coding |
| `qa-blocked` | states or smoke implications are not testable |
| `legal-blocked` | proof/status or claim treatment needs Legal approval |
| `deferred` | outside current release |
| `rejected` | must not be used |

## D-07 - TO BE Token Source And Mapping

Status: `draft`

Related gaps: `UI-001`, `PTC-016`, `UIX-010`

Primary owners: Designer + Frontend

### Recommended V1 Baseline

Use this source chain for v1:

```text
Figma variables
  -> reviewed token mapping document or token JSON in Git
  -> Tailwind theme / global CSS
  -> Bitrix templates and local components
```

Do not rely on screenshots, free-form prose or runtime Tailwind as the token source.

### Required Mapping Decisions

| Token area | Draft v1 direction | Approval status |
|---|---|---|
| Brand primary | keep current blue family but rename semantically | pending |
| Navy/dark | resolve `#001F40` vs `#001F3F` drift | pending |
| Link/accent | resolve `#007bff` vs `#0066CC` drift | pending |
| Surface/border/text | define semantic page/card/modal/form tokens | pending |
| Proof/status | define `available`, `pilot-artifact`, `private-nda`, `needs-evidence`, `not-supported` colors | pending |
| Error/success/warning/info | align forms, toast, status badges and proof states | pending |
| Typography | dense enterprise scale for hero, section, card, form, meta and technical labels | pending |
| Spacing | section rhythm, card density, form/table spacing and mobile gaps | pending |
| Radius/elevation | button, input, card, modal, diagram node and sticky UI rules | pending |
| Focus/motion/z-index | visible focus, reduced motion, overlay stack and sticky states | pending |
| Breakpoints | mobile/tablet/desktop/wide constraints | pending |

### Approval Questions

| Question | Decision |
|---|---|
| Figma variables approved as design source of truth? | pending |
| Git token JSON or mapping document required for frontend? | pending |
| Drift values resolved? | pending |
| Proof/status token names approved? | pending |
| Frontend confirms static Tailwind/global CSS mapping? | pending |

### Approved Decision

To be filled after review.

### Implementation Impact

Potentially affects:

- `docs/design-system-handoff/05-design-tokens-as-is.json`;
- Tailwind source and generated CSS;
- `local/templates/tacticum/styles/global.css`;
- form, modal, chat, proof/status and product page component styling;
- design QA and visual smoke baselines.

### Required Evidence / Follow-Up

- Figma variable collection or equivalent token artifact;
- AS IS -> TO BE token mapping;
- Frontend feasibility note;
- `npm run design:tokens:check`;
- `npm run design:handoff:check`.

## D-08 - Product Component Family And States

Status: `draft`

Related gaps: `UI-002`, `UI-003`, `UI-006`, `UI-007`, `UI-008`, `ARCH-002`, `PTC-015`

Primary owners: Designer + PM + Frontend + QA

### Recommended V1 Baseline

Approve TO BE components as a formal design-system family, not per-page card grids.

First implementation should default to:

```text
visual-restyle or contract-preserving-split
```

Use `contract-migration` or `new-interaction` only when the scope includes Frontend, QA and, for payload/analytics/PII changes, Security / Integration review.

### AS IS -> TO BE Component Draft

| AS IS component id | TO BE component name | Draft migration type | V1 rule |
|---|---|---|---|
| `global-navigation` | `NavigationShell` | `visual-restyle` | keep menu/modal triggers and SEO-visible product/money links |
| `contact-modal` | `ContactModal` | `visual-restyle` | keep modal root, form root, close/focus contracts |
| `lead-cta-form` | `LeadCTAForm` | `visual-restyle` | keep `[data-tacticum-form]`, consent, fields and `lead_*` context |
| `chat-surface` | `ChatSurface` | `visual-restyle` | keep chat selectors, scroll containment, quick replies and handoff |
| `faq-accordion` | `FAQAccordion` | `visual-restyle` | keep FAQ classes unless `faq.js` migration is scoped |
| `price-team-builder` | `TeamBuilder` | `contract-preserving-split` | keep `data-price-*`, `workers_json` and smokeable summary states |
| `product-page-blocks` | `ProductPageSystem` | `contract-preserving-split` | keep `data-product-block` locators or update smoke/sign-off |

### Required Product Components

| Component | Approval requirement |
|---|---|
| `ProductHero` | product promise and fit signal differ by product |
| `FitGuide` | fits / not fits / start here states are clear on mobile |
| `PilotKitCard` | trigger, owner, readiness/input/output/limitation and proof status visible |
| `ArchitectureDiagram` | diagram-grade, not decorative |
| `ComparisonBlock` | helps distinguish products and alternatives |
| `ProcurementBlock` | data/access/audit/integration review path |
| `RolloutTimeline` | assessment -> pilot -> integration -> rollout decision |
| `ProofStatusBlock` | source/status model cannot overstate proof |
| `LeadCTAForm` | product/scenario-aware states |
| `ChatSurface` | long-answer/error/handoff states |
| `TeamBuilder` | mobile-first builder preserving staff-order contract |

### Approval Questions

| Question | Decision |
|---|---|
| Component names approved? | pending |
| Migration types approved? | pending |
| Product pages have product-specific visual character? | pending |
| All behavior-bearing components have states? | pending |
| `08-as-is-to-be-migration-map.json` needs updates? | pending |
| QA confirms smoke implications? | pending |

### Approved Decision

To be filled after review.

### Implementation Impact

Potentially affects:

- Figma component library;
- `08-as-is-to-be-migration-map.json`;
- product page block partials or future local Bitrix components;
- global CSS/Tailwind utilities;
- QA browser/action/visual smoke;
- design handoff docs.

### Required Evidence / Follow-Up

- Figma component set with variants and states;
- approved migration map changes if any;
- `sprint-11-state-matrix.md` owner statuses;
- Frontend feasibility review;
- QA state/smoke review;
- `npm run design:components:check`;
- `npm run design:migration:check`;
- `npm run design:handoff:check`.

## D-09 - Architecture Diagrams And Proof / Status UI

Status: `draft`

Related gaps: `UI-004`, `UI-005`, `PB-005`, `PB-006`, `ARCH-002`, `SEO-TOBE-003`

Primary owners: Designer + Architect + PM + Legal

### Recommended V1 Baseline

Architecture diagrams should show operational meaning:

- data/runtime/access boundaries;
- source systems or knowledge sources;
- model/gateway/control layer where relevant;
- audit/journal/handoff points;
- mobile fallback that remains readable without decorative shrinking.

Proof/status UI should show evidence status, not desired marketing claims.

### Diagram Draft By Product

| Product | Draft diagram pattern | Approval status |
|---|---|---|
| Platform | layered runtime/data/access/control map | pending |
| Agents | source/access/assistant/handoff map | pending |
| Dev | workflow trace with gates and repository/design-system context | pending |
| Forum | scenario graph with escalation, journal and analytics | pending |

### Proof / Status Draft

| Status | Public treatment | Draft design rule |
|---|---|---|
| `available` | only owner-approved claim with source/date | strongest proof visual |
| `pilot-artifact` | safe "what we validate during pilot" | visible but lower hierarchy than proof |
| `needs-evidence` | internal/design only | not public proof |
| `private-nda` | "available on request" only if approved | request-oriented, not metric-like |
| `not-supported` | hidden from public UI | no proof card |

### Approval Questions

| Question | Decision |
|---|---|
| Diagram patterns approved by Architect? | pending |
| Mobile fallback approved? | pending |
| `pilot-artifact` public treatment approved? | pending |
| `private-nda` public wording approved? | pending |
| Proof/status visuals approved by Legal/PM? | pending |
| Does proof UI depend on Sprint 09 evidence? | pending |

### Approved Decision

To be filled after review.

### Implementation Impact

Potentially affects:

- `ArchitectureDiagram` and product-specific diagram blocks;
- proof/status component library;
- product proof blocks and source notes;
- claims governance and safe copy;
- SEO snippets if proof/case wording changes;
- visual smoke screenshots and QA review.

### Required Evidence / Follow-Up

- Architect review for diagram patterns;
- Legal/PM review for status taxonomy;
- Sprint 09 proof/claims evidence before strong public proof;
- source/date/owner rules for any `available` proof item;
- mobile diagram fallback spec;
- `npm run design:handoff:check`;
- `npm run product:gaps:check`.

## Sprint 11 Closure Rule

Sprint 11 can move from `ready-for-owner-review` to `completed` only when:

- `D-07`, `D-08` and `D-09` have owner statuses;
- token source and mapping artifact exists;
- Figma component family and state matrix exist;
- proof/status UI has PM/Legal status and does not depend on unapproved claims;
- diagrams have Architect status;
- Frontend confirms no hidden contract migration;
- QA confirms state and smoke implications;
- `design:handoff:check` and `product:gaps:check` remain green.
