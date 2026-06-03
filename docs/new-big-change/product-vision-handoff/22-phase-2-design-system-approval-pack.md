# 22. Phase 2 Design System Approval Pack

Дата: 02.06.2026

Статус: draft approval package для Phase 2 design-system decisions. Документ не заменяет Figma library, но фиксирует, какие design/frontend/QA/legal решения должны быть утверждены, чтобы TO BE дизайн-система стала implementation-ready.

## Назначение

Phase 2 из `15-gap-closure-master-plan.md` должен перевести проверенный AS IS design handoff в утвержденную TO BE дизайн-систему. AS IS baseline уже зафиксирован и проверяется:

- `docs/design-system-handoff/05-design-tokens-as-is.json`;
- `docs/design-system-handoff/07-component-state-contract.json`;
- `docs/design-system-handoff/08-as-is-to-be-migration-map.json`;
- `docs/design-system-handoff/09-to-be-design-work-order.md`;
- `npm run design:handoff:check`.

Этот документ задает approval-level решения для remaining design gaps.

Covered gaps:

- `UI-001` - TO BE token source of truth;
- `UI-002` - product storytelling component family;
- `UI-003` - visual form/modal/CTA state spec;
- `UI-005` - proof/status UI;
- `UI-006` - `/price/` mobile team-builder UX;
- `UI-007` - chat visual/state spec.

Related gaps:

- `UI-004` - architecture diagram baseline lives in `17-local-gap-decision-briefs.md`;
- `UI-008` - icon taxonomy baseline lives in `17-local-gap-decision-briefs.md`;
- `PB-005` / `PB-006` - proof/status UI cannot imply evidence that Legal/Sales did not approve.

## Input Baseline

| Input | Current status | Approval implication |
|---|---|---|
| Token AS IS contract | Checked JSON baseline | TO BE must choose source of truth and mapping |
| Component/state contract | Checked behavior-bearing components | TO BE components must preserve or deliberately migrate selectors |
| Migration map | Checked AS IS -> TO BE baseline | Designer + Frontend must approve names, migration type and gates |
| Work order | Deliverables and red lines fixed | Figma library must satisfy acceptance criteria before implementation |
| Product block locators | `data-product-block` exists | TO BE product page design must preserve block identity or update smoke/sign-off |

## UI-001 - Token Source Of Truth

Recommended v1 decision: Figma variables are the design source of truth; Git keeps a reviewed token JSON or documented mapping that frontend can translate into Tailwind/global CSS. Do not rely on screenshots or prose-only token decisions.

| Token group | Required TO BE decision | AS IS anchor |
|---|---|---|
| Brand colors | Keep/rename primary blue and navy, resolve drift | `#0066CC`, `#001F3F`, `#001F40`, `#007bff` |
| Semantic colors | Status, proof, warning, error, success | observed CSS/form states |
| Typography | Headings, body, labels, mono/technical labels | Tailwind theme + rendered product pages |
| Spacing | Section rhythm, card/form density, mobile gaps | Tailwind spacing + `global.css` |
| Radius | Buttons, cards, inputs, modal, diagram nodes | `rounded-button`, card/modals |
| Elevation | Cards, modal, sticky header, toast | `global.css` modal/toast areas |
| Focus | Keyboard-visible focus ring and contrast | form controls, buttons, links |
| Motion | FAQ, modal, hover, reduced motion | existing vanilla JS interactions |
| Breakpoints | Mobile/tablet/desktop/wide | Tailwind breakpoints |

Approval deliverable:

| Deliverable | Owner | Done when |
|---|---|---|
| Figma variable collection or token file | Designer | all token groups exist and have semantic names |
| AS IS -> TO BE token mapping | Designer + Frontend | every existing core token has keep/rename/replace decision |
| Frontend feasibility note | Frontend | mapping can be implemented in Tailwind/global CSS without runtime Tailwind |
| Drift decision | Designer + Frontend | `#001F40`/`#001F3F` and `#007bff`/`#0066CC` resolved |

Close `UI-001` only when:

- approved token source exists;
- mapping to `05-design-tokens-as-is.json` exists;
- `npm run design:tokens:check` remains green after any handoff updates.

## UI-002 - Product Storytelling Component Family

Recommended v1 decision: approve TO BE product page components as a formal family, not page-specific card grids. Component names should align with `08-as-is-to-be-migration-map.json`.

Required product storytelling components:

| Component | Purpose | AS IS anchor / migration |
|---|---|---|
| `ProductHero` | Product name, fit, primary/secondary CTA | product page hero block |
| `FitGuide` | fits / not fits / start here | `data-product-block=fit-guide` |
| `UseCaseCard` | trigger, owner, input, output, limitation | `data-product-block=use-cases` |
| `ArchitectureDiagram` | layers, data flow, runtime boundaries | `data-product-block=architecture`; diagram baseline in `17` |
| `ComparisonBlock` | product boundaries and alternatives | `data-product-block=comparison` |
| `ProcurementBlock` | security/procurement review path | `data-product-block=procurement` |
| `RolloutTimeline` | discovery -> pilot -> rollout | `data-product-block=rollout` |
| `ProofStatusBlock` | evidence status and source notes | `data-product-block=proof`; see `UI-005` |
| `LeadCTAForm` | product-aware conversion | `tacticum:lead.cta`, `[data-tacticum-form]` |

Approval deliverable:

| Deliverable | Owner | Done when |
|---|---|---|
| Component list and naming | Designer + PM | each page block has a final TO BE component name |
| Variants per product | Designer + PM | Platform/Agents/Dev/Forum differences are visible |
| Migration map update | Designer + Frontend | `08` component names and migration types match final design |
| QA state list | QA | interactive/variant states are testable |

Close `UI-002` only when:

- Figma component family exists;
- names/migration types are approved;
- product pages are not four identical card-grid skins;
- `npm run design:migration:check` remains green after updates.

## UI-003 - Form / Modal / CTA State Spec

Recommended v1 decision: visual restyle only unless Designer + Frontend + QA explicitly request a contract migration. Public forms must preserve current DOM/data/API contracts for first implementation.

Contracts to preserve:

| Area | Preserve |
|---|---|
| Form root | `[data-tacticum-form]`, `data-form-id`, optional `data-endpoint` |
| Consent | `[data-tacticum-consent]`, `/policies/` link |
| Required fields | `name`, `email`, `phone`, `message`, `page_url`, `sessid` |
| Product context | controlled `lead_*` fields and `lead_scenario` select |
| Modal | `#tacticum-modal`, `#tacticum-modal-form`, close/focus behavior |
| Response UX | loading, validation error, backend error, network error, success |

Required visual states:

| Component | States |
|---|---|
| Text field / textarea | default, focus, filled, invalid, disabled, autofill |
| Select | default, open/selected, focus, invalid, disabled |
| Checkbox consent | unchecked, checked, focus, error |
| Submit button | default, hover, focus, loading, disabled |
| Form shell | initial, validation error, backend error, network error, success |
| Modal | hidden, open, scroll, focus trap, close, success/error |
| Toast | success, error, network error |

Close `UI-003` only when:

- Figma state matrix exists for lead forms, modal and product/procurement CTA variants;
- QA confirms smoke implications;
- any payload/field/endpoint change is moved to Security / Integration lane.

## UI-005 - Proof / Status UI

Recommended v1 decision: proof/status UI must communicate evidence status, not decorate unapproved claims. Until `PB-005` and `PB-006` are approved, UI can show only safe readiness/pilot artifacts and "not public / needs evidence" states.

Allowed status model:

| Status | Meaning | Public treatment |
|---|---|---|
| `available` | Approved source exists | Can show claim with source/date note |
| `pilot-artifact` | We validate this during pilot | Can show as "что проверяем на пилоте" |
| `needs-evidence` | Claim desired but source missing | Do not publish as proof; show internally only |
| `private-nda` | Evidence exists but cannot be public | Public page can say evidence available on request only if approved |
| `not-supported` | Claim should not be used | Do not show proof-looking UI |

Required proof components:

| Component | Purpose |
|---|---|
| `ProofStatus` | status badge with accessible text |
| `SourceNote` | source/date/owner note for approved claim |
| `EvidenceCard` | approved case/metric/proof item |
| `PilotArtifactCard` | safe "what we validate" item |
| `UnavailableState` | internal/design-only state for blocked proof |

Red lines:

- no customer logos without written approval;
- no numeric metrics without source/methodology/date;
- no registry/certification/security status without Legal/Security approval;
- no visual hierarchy that makes `pilot-artifact` look like confirmed result.

Close `UI-005` only when:

- Designer + Legal + PM approve status taxonomy and component visuals;
- proof components map to claims governance from `18-phase-1-product-decision-review-pack.md`;
- blocked proof stays visually and content-wise blocked.

## UI-006 - `/price/` Mobile Team Builder UX

Recommended v1 decision: preserve `/price/` staff-order contract and design a mobile-first team builder around existing `data-price-*`, `workers_json`, team presets and persistent summary. Do not turn `/price/` into product license pricing.

Mobile flow options:

| Option | Good for | Risk |
|---|---|---|
| Inline cards + sticky summary | transparent browsing and editing | long page, needs careful scroll behavior |
| Modal-first order builder | focused checkout-like flow | hides comparison and may break current smoke assumptions |
| Hybrid list + bottom summary sheet | best balance for staff selection | needs detailed states and QA smoke |

Recommended v1: hybrid list + bottom summary sheet, preserving existing endpoint and hidden fields.

Required mobile states:

| Area | States |
|---|---|
| Filters/search | default, selected, no results |
| Role cards | default, selected, expanded/collapsed, disabled if unavailable |
| Level selector | selected level, unavailable level, focus |
| Team presets | default, selected, edited after preset |
| Summary | empty, filled, sticky/collapsed, expanded |
| Order modal/sheet | open, validation error, submit loading, success/error |

Close `UI-006` only when:

- mobile team builder spec exists;
- Frontend confirms it preserves `workers_json`, endpoint override and smokeable states;
- QA updates `/price/` browser smoke expectations if UI state model changes.

## UI-007 - Chat Visual / State Spec

Recommended v1 decision: visual restyle only. Preserve current chat selectors, message containment, quick replies, `group_id`/prefill behavior and no-PII analytics unless Security / Integration lane approves changes.

Contracts to preserve:

| Area | Preserve |
|---|---|
| Root | `[data-tacticum-chat='hero']`, `[data-tacticum-chat='light']` |
| Messages | `[data-chat-messages]`, scroll containment |
| Input/send | `[data-chat-input]`, `[data-chat-send]` |
| Quick replies | `[data-chat-quick-reply]`, `data-message` |
| Handoff | `[data-chat-lead-handoff]`, `#contact-form` path |
| Prefill | `group_id + sessid` POST-only flow |
| Analytics | no raw message/user text in params |

Required chat states:

| State | Design requirement |
|---|---|
| Initial | clear prompt and affordance without fake conversation |
| User message | visible sender distinction, long text wrapping |
| Assistant message | readable long-answer containment |
| Typing/loading | clear waiting state |
| Error | retry/neutral error without raw stack |
| Quick replies | tap targets, selected/pressed/focus states |
| Handoff CTA | lead CTA after useful answer |
| Mobile keyboard | input and send button remain usable |

Close `UI-007` only when:

- chat component state spec exists;
- Frontend confirms selector and scroll behavior feasibility;
- QA confirms chat smoke and handoff checks.

## Phase 2 Approval Board

| Gap | Owner | Close only when |
|---|---|---|
| `UI-001` | Designer + Frontend | token source and AS IS -> TO BE mapping are approved |
| `UI-002` | Designer + PM | product storytelling component family and migration names are approved |
| `UI-003` | Designer + QA | form/modal/CTA state matrix is approved |
| `UI-005` | Designer + Legal + PM | proof/status taxonomy and evidence mapping are approved |
| `UI-006` | Designer + Frontend | `/price/` mobile team builder spec is approved |
| `UI-007` | Designer + Frontend | chat visual/state spec is approved |

## Implementation Gate

Implementation can start only for components where:

1. Figma/design artifact exists.
2. Migration type remains `visual-restyle` or approved `contract-preserving-split`.
3. Preserved selectors are listed.
4. QA states are explicit.
5. Security / Integration is opened for payload, endpoint, analytics or PII changes.
6. `npm run design:handoff:check` and `npm run product:gaps:check` remain green after docs updates.

## Recommended Review Session

1. Designer presents token source and component family.
2. Frontend maps tokens/components to Tailwind/global CSS and PHP/Bitrix implementation.
3. QA reviews form, chat, `/price/` and proof/status states.
4. PM/Legal reviews proof/status taxonomy and blocked claims.
5. Designer updates Figma and, if needed, `08-as-is-to-be-migration-map.json`.
6. Frontend reruns design and product gap guards before implementation.
