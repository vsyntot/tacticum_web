# Sprint 11 State Matrix

Дата: 02.06.2026

Статус: ready-for-owner-review draft state matrix. Документ переводит checked AS IS component/state contract into a TO BE review checklist for Designer, Frontend and QA.

## Purpose

TO BE дизайн нельзя считать implementation-ready, если он показывает только статичные desktop layouts. Для текущего сайта критичны behavior-bearing components:

- forms and modal;
- chat;
- FAQ;
- navigation and mobile menu;
- `/price/` team builder;
- product page blocks and proof/status UI.

Эта матрица фиксирует минимальные состояния, preserve/migrate rules and QA implications.

## Contract Rules

| Rule | Meaning |
|---|---|
| Preserve selectors by default | visual restyle can change classes/layout but must keep behavior-bearing selectors |
| Scope migrations explicitly | selector, payload, endpoint, analytics or PII changes need separate implementation and review |
| Design mobile states | mobile is not a resized desktop screenshot |
| Do not hide errors | validation, backend and network errors need visible states |
| Proof is status-based | proof-looking UI must map to evidence status |

## Global Primitives

| Primitive | Required states | Owner review |
|---|---|---|
| Button | default, hover, focus-visible, active, loading, disabled | Designer + Frontend + QA |
| Link | default, hover, focus-visible, current/active where relevant | Designer + Frontend |
| Text field | default, focus, filled, invalid, disabled, autofill | Designer + QA |
| Textarea | default, focus, filled, invalid, long text, disabled | Designer + QA |
| Select | default, focus, selected, invalid, disabled, mobile open treatment | Designer + QA |
| Checkbox consent | unchecked, checked, focus, error, disabled | Designer + QA |
| Toast/status message | success, validation error, backend error, network error | Designer + QA |
| Card | default, hover/focus, selected/current, disabled/not-supported, status | Designer + QA |
| Table/decision matrix | desktop scan, mobile stacked, long labels, empty row | Designer + PM + QA |

## NavigationShell

AS IS component id: `global-navigation`

Draft TO BE component: `NavigationShell`

Migration type: `visual-restyle`

Preserve:

- `[data-tacticum-menu-toggle]`;
- `#tacticum-mobile-menu`;
- `.tacticum-mobile-menu-close`;
- `.tacticum-contact-btn`;
- `#contactUsBtn`.

| State | Required design detail | QA / implementation implication |
|---|---|---|
| Desktop default | product and commercial entries visible | SEO/nav guard must still see product and money links |
| Desktop dropdown | hover/focus/open/close, keyboard path | current menu behavior or new JS scope |
| Current page | active/current styling | no URL-text-only dependency |
| Sticky/scroll | compact/shadow state if used | avoid layout jump |
| Mobile closed | menu button and focus state | preserve toggle selector |
| Mobile open | full-screen or drawer, close, nested links | preserve menu root and close behavior |
| Contact CTA | desktop/mobile trigger to modal | preserve contact modal trigger or scope migration |

## LeadCTAForm

AS IS component id: `lead-cta-form`

Draft TO BE component: `LeadCTAForm`

Migration type: `visual-restyle`

Preserve:

- `[data-tacticum-form]`;
- `[data-form-id]`;
- `[data-endpoint]`;
- `[data-tacticum-consent]`;
- `name`, `email`, `phone`, `message`;
- `lead_scenario`, `lead_budget`, `lead_timeline`.

| State | Required design detail | QA / implementation implication |
|---|---|---|
| Initial | fields, consent and CTA hierarchy | no extra required fields without backend scope |
| Product scenario selected | select state, label and help text | keep controlled `lead_scenario` |
| Field focus/filled | visible focus and filled treatment | keyboard accessibility |
| Field invalid | per-field error and form-level error | forms.js error mapping remains visible |
| Consent error | checkbox-specific error | preserve consent selector |
| Submit loading | button disabled/spinner/text | preserve spinner/text roles or scope visual migration |
| Backend error | neutral non-PII error | no raw upstream text |
| Network error | retry-friendly state | QA smokeable |
| Success | toast or inline success | decide if modal/page forms differ |
| Mobile | one-column, readable consent, usable submit | no overflow or hidden submit |

## ContactModal

AS IS component id: `contact-modal`

Draft TO BE component: `ContactModal`

Migration type: `visual-restyle`

Preserve:

- `#tacticum-modal`;
- `#tacticum-modal-close`;
- `#tacticum-modal-form`;
- `[data-tacticum-close-target]`;
- `[data-tacticum-close-mode]`;
- `[data-error]`.

| State | Required design detail | QA / implementation implication |
|---|---|---|
| Hidden | non-interactive and inaccessible background expectations | current JS controls visibility |
| Opening/open | backdrop, panel, close, title and first focus | focus behavior remains smokeable |
| Scrollable | long content and mobile keyboard | no trapped hidden submit |
| Validation error | same form error model as `LeadCTAForm` | forms.js contract |
| Submit loading | modal-specific loading state | no double submit |
| Success | close+toast or inline success | align with `[data-tacticum-close-target]` |
| Backend/network error | visible, non-PII | QA negative state |
| Mobile sheet option | if bottom sheet, preserve root/form selectors | frontend feasibility required |

## ChatSurface

AS IS component id: `chat-surface`

Draft TO BE component: `ChatSurface`

Migration type: `visual-restyle`

Preserve:

- `[data-tacticum-chat='hero']`;
- `[data-tacticum-chat='light']`;
- `[data-chat-surface]`;
- `[data-chat-messages]`;
- `[data-chat-input]`;
- `[data-chat-send]`;
- `[data-chat-quick-reply]`;
- `[data-message]`;
- `[data-chat-lead-handoff]`.

| State | Required design detail | QA / implementation implication |
|---|---|---|
| Initial | clear prompt, no fake finished dialog | current quick replies can stay |
| User message | long text wrapping, sender distinction | no raw analytics params |
| Assistant answer | long-answer containment | preserve `[data-chat-messages]` scroll |
| Typing/loading | waiting state | current JS or scoped implementation |
| Error | retry/neutral copy without stack | QA negative state |
| Quick reply | default, hover, focus, pressed/selected | preserve `data-message` |
| Handoff CTA | visible after useful answer | preserve lead handoff selector |
| Mobile keyboard | input/send remain usable | avoid viewport overlap |

## FAQAccordion

AS IS component id: `faq-accordion`

Draft TO BE component: `FAQAccordion`

Migration type: `visual-restyle`

Preserve:

- `.faq-item`;
- `.faq-question`;
- `.faq-answer`;
- `.faq-icon`;
- `.active`.

| State | Required design detail | QA / implementation implication |
|---|---|---|
| Collapsed | clear affordance and tap target | current selector contract |
| Open | answer spacing and icon state | `.active` remains meaningful |
| Long answer | readable legal/security text | no layout overflow |
| Keyboard focus | visible focus on question | accessibility |
| Motion | reduced motion expectation | if behavior changes, JS scope needed |
| Single vs multiple open | explicit decision | multiple-open is contract migration |

## TeamBuilder

AS IS component id: `price-team-builder`

Draft TO BE component: `TeamBuilder`

Migration type: `contract-preserving-split`

Preserve:

- `[data-price-list]`;
- `[data-price-card]`;
- `[data-price-filter-tab]`;
- `[data-price-search]`;
- `[data-price-level-option]`;
- `[data-price-value]`;
- `[data-price-order]`;
- `[data-price-team-preset]`;
- `[data-price-team-summary]`;
- `[data-price-empty]`;
- `[data-price-order-modal]`;
- `[data-price-order-form]`;
- `workers_json`;
- `monthly_budget_estimate`.

| State | Required design detail | QA / implementation implication |
|---|---|---|
| Browse roles | cards/list, dense comparison, mobile scan | preserve data attributes |
| Filter selected | category active state | browser smoke selector remains |
| Search/no results | empty state with recovery | preserve empty state |
| Role selected | selected card and quantity/level clarity | workers summary updates |
| Level selected | Junior/Middle/Senior/Lead treatment | preserve level option values |
| Team preset selected | preset, edited-after-preset state | preserve preset contract |
| Summary empty | clear "nothing selected" state | no hidden submit |
| Summary filled | sticky/collapsed/expanded mobile behavior | smoke update if UI changes |
| Order modal/sheet | open, validation, loading, success/error | preserve form and endpoint |
| Hidden payload | `workers_json`, duration, budget estimate | backend compatibility |

## ProductPageSystem

AS IS component id: `product-page-blocks`

Draft TO BE component: `ProductPageSystem`

Migration type: `contract-preserving-split`

Preserve or explicitly migrate:

- `data-product-block='hero'`;
- `data-product-block='fit-guide'`;
- `data-product-block='content-section'`;
- `data-product-block='architecture'`;
- `data-product-block='use-cases'`;
- `data-product-block='comparison'`;
- `data-product-block='procurement'`;
- `data-product-block='rollout'`;
- `data-product-block='proof'`;
- `data-product-block='faq'`;
- `data-product-block='lead-cta'`.

| Block | Required TO BE states / variants | QA / implementation implication |
|---|---|---|
| Hero | product-specific promise, fit signal, CTA, mobile | no unsupported claim |
| Fit guide | fits/not-fits/start-here, mobile stacked | role/product clarity |
| Use cases / Pilot kits | trigger, owner, readiness, input, output, limitation, proof status | depends on Sprint 10 approval |
| Architecture | desktop diagram, tablet, mobile fallback, long labels | Architect approval |
| Comparison | current product vs alternatives, selected/current states if interactive | no risky competitor claims |
| Procurement | data/access/audit/integration review path | safe security wording |
| Rollout | steps, current/next, mobile timeline | no guaranteed outcome |
| Proof | available/pilot/private/blocked states | depends on Sprint 09/Legal |
| FAQ | inherited FAQ states | preserve FAQ behavior |
| Lead CTA | inherited form states and product context | preserve form contract |

## Proof / Status Components

| Component | Required states | Public rule |
|---|---|---|
| `ProofStatus` | available, pilot-artifact, private-nda, needs-evidence, not-supported | public only for approved allowed statuses |
| `SourceNote` | source present, source missing, private source, stale source | strong proof requires source/date/owner |
| `EvidenceCard` | metric, case, artifact, regulatory wording | only with approved evidence |
| `PilotArtifactCard` | what we check, input, output, limitation | safe public v1 if PM approves |
| `UnavailableState` | internal blocked, hidden public | must not render as public proof |

## ArchitectureDiagram

| State / Variant | Required design detail | Owner |
|---|---|---|
| Desktop diagram | readable nodes, connectors, status marks | Designer + Architect |
| Tablet diagram | no tiny labels, clear grouping | Designer |
| Mobile fallback | stacked, table or accordion version | Designer + QA |
| Long labels | wrapping/truncation rules | Designer + Frontend |
| Data boundary | source/runtime/access/audit labels | Architect |
| Status marks | status not conveyed by color only | Designer + QA |

## QA Review Output

For each approved component, QA should record:

| Field | Required |
|---|---|
| Component | TO BE name and AS IS id |
| State coverage | approved / missing states |
| Smoke impact | none / update visual / update browser / update form/chat/price |
| Contract risk | none / selector risk / payload risk / analytics risk |
| Required evidence | screenshot, manual test, production smoke, Legal/PM approval |

## Implementation Readiness Rule

A component can move to implementation only when:

1. TO BE visual anatomy exists.
2. States in this matrix are approved or explicitly deferred.
3. Preserved selectors are listed.
4. Migration type is approved.
5. QA smoke impact is known.
6. Security / Integration scope is opened for payload, endpoint, analytics or PII changes.
7. `npm run design:handoff:check` remains green after handoff updates.
