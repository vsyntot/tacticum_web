# 26. Post-Challenge UX / UI / Design System Detail

Дата: 02.06.2026

Статус: детализация design challenge для Designer, Frontend, PM, QA and Legal. Использовать вместе с `12-ux-ui-component-target.md`, `22-phase-2-design-system-approval-pack.md` and `docs/design-system-handoff/`.

## UX / UI Verdict

AS IS UI стабилен и достаточно безопасен для MVP, но пока выглядит как generic B2B SaaS поверх сильной backend/Bitrix дисциплины.

TO BE должен стать не "более красивой версией сайта", а системой enterprise decision support:

```text
clear product route
  + role-aware next step
  + architecture/procurement clarity
  + proof status
  + stateful conversion components
```

## Design Risks

| Risk | Why it matters | Guardrail |
|---|---|---|
| Marketing-heavy hero redesign | Can hide product decision support behind visual polish | Keep dense route/fit/proof blocks visible |
| Four identical product pages | Makes Platform/Agents/Dev/Forum feel like labels, not products | Product-specific blocks and diagrams |
| Decorative architecture | Enterprise buyer needs operational meaning | Diagrams must show data/runtime/access boundaries |
| Proof-looking cards without proof | Creates legal/sales trust risk | Every proof UI maps to evidence status |
| Form restyle that breaks JS | Current forms depend on DOM/data contracts | Preserve selectors or define migration gates |
| `/price/` redesigned as product pricing | It is currently a staff/team configurator | Keep commercial decision separate |
| Chat redesign as fake conversation | Can reduce trust and break handoff | Preserve chat state/scroll/handoff contracts |

## Product-Specific Visual Direction

| Product | Visual emphasis | Avoid |
|---|---|---|
| Platform | layered architecture, runtime, data/access/control, reuse map | abstract module grid without deployment meaning |
| Agents | function scenarios, document readiness, handoff and source quality | looking like a bot marketplace |
| Dev | workflow trace, gates, design-system and codebase context | generic AI coding illustration |
| Forum | dialog flow, scenario graph, escalation, journal and analytics | pure chatbot visual language |

## Required Component Families

| Component | Current AS IS anchor | TO BE requirement | Related gaps |
|---|---|---|---|
| `ProductHero` | `data-product-block=hero` | Product promise, fit signal, primary/secondary CTA, no unsupported claim | `UI-002`, `PB-001` |
| `FitGuide` | `data-product-block=fit-guide` | Fits / not fits / start here, mobile scannable | `CJM-001`, `UI-002` |
| `PilotKitCard` | `data-product-block=use-cases` | Trigger, owner, readiness, input, output, limitation, proof status | `CJM-003`, `UI-002`, `UI-005` |
| `ArchitectureDiagram` | `data-product-block=architecture` | Layer/data-flow diagram with mobile fallback | `UI-004`, `ARCH-002` |
| `ComparisonBlock` | `data-product-block=comparison` | Product boundary and alternative decision table | `PB-003`, `PB-008` |
| `ProcurementBlock` | `data-product-block=procurement` | Data/access/audit/integration review path | `CJM-002`, `PB-006` |
| `RolloutTimeline` | `data-product-block=rollout` | Assessment -> pilot -> integration -> rollout decision | `PB-007`, `CJM-003` |
| `ProofStatusBlock` | `data-product-block=proof` | Evidence status, source notes, pilot artifacts | `PB-005`, `UI-005` |
| `LeadCTAForm` | `tacticum:lead.cta`, `[data-tacticum-form]` | Product-aware form states and scenario select | `UI-003`, `CJM-006` |
| `ChatSurface` | `[data-tacticum-chat]` | Initial, loading, error, long answer, handoff CTA | `UI-007` |
| `TeamBuilder` | `/price/` `data-price-*` | Mobile-first builder preserving `workers_json` | `UI-006` |

## Proof / Status UI Taxonomy

TO BE design must not make unapproved evidence look confirmed.

| Status | Public treatment | Visual rule |
|---|---|---|
| `available` | Claim can be shown with source/date if owner-approved | strongest proof visual allowed |
| `pilot-artifact` | Safe "what we validate during pilot" | must not look like achieved result |
| `needs-evidence` | Internal/design only | do not publish as proof card |
| `private-nda` | May say "available on request" only if approved | subdued, request-oriented |
| `not-supported` | Claim should not appear | hidden from public UI |

Required proof component states:

- source present / source missing;
- public / private / blocked;
- metric / case / artifact / regulatory wording;
- desktop card / mobile stacked fallback;
- accessible status text, not color-only meaning.

## Interaction State Requirements

Designer must specify these states before implementation starts.

| Area | Required states |
|---|---|
| Buttons and links | default, hover, focus, active, loading, disabled |
| Inputs/select/textarea | default, focus, filled, invalid, disabled, autofill |
| Consent checkbox | unchecked, checked, focus, error |
| Form shell | initial, validation error, backend error, network error, success |
| Modal | opening, open, scroll, focus, close, success/error |
| FAQ | closed, open, long answer, keyboard focus |
| Chat | initial, user message, assistant answer, typing, error, quick reply, handoff, long answer |
| Product cards | default, hover, selected/current, disabled/not supported, status |
| Diagrams | desktop, tablet, mobile stacked, overflow/long labels |
| `/price/` team builder | filters, selected role, level, preset, empty, summary, submit states |

## Design Token Decisions

The checked AS IS token contract is the baseline, not the final source of truth.

TO BE must decide:

| Token group | Decision needed |
|---|---|
| Brand color | keep/rename primary blue and navy, resolve drift between close values |
| Semantic colors | success, warning, error, info, proof, pilot, blocked |
| Typography | dense enterprise headings, body, labels, technical/meta text |
| Spacing | section rhythm, card density, form spacing, table spacing |
| Radius | button, input, card, modal, diagram nodes |
| Elevation | card, modal, sticky header, mobile bottom sheet |
| Focus | visible keyboard focus with contrast |
| Motion | reduced motion, accordion, modal, hover |
| Z-index | header, mobile menu, modal, toast, sticky CTA |
| Breakpoints | mobile, tablet, desktop, wide desktop |

Recommended source chain:

```text
Figma variables
  -> reviewed token JSON or mapping document
  -> Tailwind theme / global CSS
  -> Bitrix templates and components
```

## UI Review Checklist

Before design is considered implementation-ready:

- Product pages are not four visual clones.
- Each product has at least one product-specific decision block.
- Product architecture is diagram-grade, not decorative.
- Proof/status UI has evidence mapping.
- Form, chat, FAQ, modal and `/price/` states are specified.
- AS IS selectors are preserved or migration is explicitly approved.
- Mobile states are designed for dense enterprise content.
- No claim, badge or diagram implies unsupported deployment, certification, registry, SLA, metric, logo or customer proof.

## Implementation Gate

Frontend implementation can start only when:

1. Figma/component deliverable exists.
2. Token mapping to AS IS contract exists.
3. `08-as-is-to-be-migration-map.json` is updated if component names or migration types change.
4. QA state expectations are explicit.
5. Legal/PM approve proof/status visuals.
6. `npm run design:handoff:check` remains green after handoff changes.
7. `npm run product:gaps:check` remains green after product docs/register changes.
