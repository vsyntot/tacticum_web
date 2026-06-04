# Product TO BE Design System Decision Pack

Дата: 04.06.2026
Статус: draft pending Designer/Frontend/QA/Legal approval
Sprint: `docs/workflow/sprints/2026-06-04-sprint-20-to-be-design-system.md`

## Purpose

Этот документ фиксирует Sprint 20 baseline для TO BE design system: token source, enterprise density, palette, CSS pipeline, hero/page taxonomy, proof/status UI, diagrams, form/chat states, `/price/` mobile behavior, icon taxonomy and AS IS -> TO BE traceability.

Документ не реализует редизайн и не меняет CSS/JS/runtime. Он задает approval package and implementation gates. Любая визуальная реализация после этого должна ссылаться на этот документ, AS IS handoff package and affected gap IDs.

## Covered Gaps

| Gap | Sprint Item | Current Output | Remaining Gate |
|---|---|---|---|
| `UI-001` | S20-001 | Token source recommendation | Designer + Frontend approval |
| `UI-002` | S20-002 | Enterprise density/radius/card policy | Designer + PM approval |
| `UI-003` | S20-003 | Palette/gradient policy | Designer approval |
| `STACK-003` | S20-004 | CSS/token pipeline policy | Frontend + Designer approval |
| `UI-004` | S20-005 | Hero/page visual taxonomy | Designer + Frontend approval |
| `UI-005` | S20-006 | Proof/status UI state rules | Legal + Designer + PM approval |
| `UI-006` | S20-007 | Architecture/procurement diagram spec | Architect + Designer approval |
| `UI-007` | S20-008 | Form/modal/CTA state matrix | Designer + QA + Frontend approval |
| `UI-008` | S20-009 | Chat visual/state matrix | Designer + Frontend + QA approval |
| `UI-009` | S20-010 | `/price/` mobile flow recommendation | Designer + Frontend + QA approval |
| `UI-010` | S20-011 | Icon taxonomy | Designer + Frontend approval |
| `CMP-008` | S20-012 | Traceability map | Designer + Frontend + PM approval |

## Source Baseline

| Artifact | Role |
|---|---|
| `docs/design-system-handoff/05-design-tokens-as-is.json` | AS IS token snapshot |
| `docs/design-system-handoff/07-component-state-contract.json` | Behavior-bearing component/state contract |
| `docs/design-system-handoff/08-as-is-to-be-migration-map.json` | AS IS -> TO BE migration map |
| `docs/workflow/design-token-contract.md` | Token guard/runbook |
| `docs/workflow/component-state-contract.md` | Component/state guard/runbook |
| `docs/workflow/design-migration-map.md` | Migration guard/runbook |
| `docs/workflow/asset-layout-audit.md` | CSS/JS asset constraints |
| `docs/workflow/proof-claims-matrix.md` | Public claim constraints |
| `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md` | CTA/success-state input |

## Token Source Recommendation

Recommended TO BE source model: hybrid with clear ownership.

| Layer | Owner | Source | Rule |
|---|---|---|---|
| Design source | Designer | Figma variables | Primary visual design source after approval |
| Repo bridge | Frontend + Designer | versioned token JSON or updated `05-design-tokens-as-is.json` successor | Machine-readable mapping for review and guards |
| Runtime implementation | Frontend | Tailwind source + `styles/global.css` | Only approved tokens flow into CSS/Tailwind |

Current AS IS tokens remain valid until TO BE approval:

- `color.brand.primary = #0066CC`;
- `color.brand.secondary = #001F3F`;
- `radius.button = 8px`;
- observed focus/elevation/motion/z-index candidates are tracked but not final TO BE tokens.

Decision rule: do not close `UI-001` until Designer and Frontend approve naming, Figma variable groups, JSON bridge shape and Tailwind/global CSS mapping.

## Enterprise Density, Radius And Card Policy

Target product UI should read as enterprise software/product operations, not as card-heavy marketing layout.

| Surface | Policy |
|---|---|
| Page sections | Full-width bands or unframed constrained layouts; do not style every section as a floating card |
| Repeated items | Cards allowed for repeated comparable objects: use cases, FAQ items, proof entries, price roles |
| Nested cards | Not allowed unless a modal/tool genuinely contains repeated sub-items |
| Radius | Default component radius should stay at or below 8px unless AS IS component requires compatibility |
| Panels | Use panels for dense information, comparisons, data-flow and procurement checklists |
| Spacing | Prefer denser vertical rhythm for product/procurement pages; avoid oversized marketing whitespace in operational sections |
| Buttons | Keep clear command hierarchy; icon+text where action benefits from affordance |
| Tables/comparison | Prefer scan-friendly rows/columns over decorative card grids for procurement and architecture comparisons |

Implementation rule: broad density changes need visual smoke and must preserve behavior-bearing selectors from `07-component-state-contract.json`.

## Palette And Gradient Policy

| Topic | Recommendation |
|---|---|
| Brand blue | Keep primary blue for action/focus, not as dominant background for every product block |
| Secondary navy | Keep for headings and high-contrast enterprise surfaces |
| Neutral surfaces | Add or normalize semantic neutral surfaces for dense panels, tables and forms |
| Status colors | Add semantic proof/status tokens only after Legal/PM approval |
| Gradients | Restrict to hero/accent use; do not use gradient/orb/bokeh backgrounds as default page language |
| Product differentiation | Differentiate by structure, icon, title and status, not by introducing four unrelated product color themes |

Blocked: one-note blue/purple-blue theme expansion, unapproved status colors that imply proof maturity, and new decorative gradients without design review.

## CSS / Token Pipeline Policy

| Step | Rule |
|---|---|
| Token proposal | Designer proposes Figma variables and semantic names |
| Repo mapping | Frontend maps approved names to JSON/Tailwind/global CSS |
| CSS source | `tailwind.css` remains source for generated utilities; `styles/global.css` remains the only approved manual template CSS file |
| New CSS files | Component `style.css` allowed only for local Bitrix component scope; new template-level page CSS requires asset audit update |
| Generated CSS | If Tailwind source changes, rebuild `tailwind.generated.css` and run `css:check` |
| Runtime CSS | Do not reactivate `template_styles.css`; guard with `template-styles:check` |
| Implementation batches | Small batches by component/page family with `visual:smoke:css-local` before deployment |

ADR trigger: open/update ADR if token JSON becomes a formal shared architecture contract or if the CSS pipeline changes beyond current Tailwind + global CSS model.

## Hero And Page Visual Taxonomy

| Page Family | Hero / First Screen Rule | Notes |
|---|---|---|
| Product pages `/platform/`, `/agents/`, `/dev/`, `/forum/` | Product name and product decision framing first; dense proof/fit hints below fold | Preserve `data-product-block` taxonomy |
| Service/delivery pages | Delivery route, implementation model and next-step CTA | Do not compete with product pages for product canonical language |
| Proof/evidence pages or sections | Evidence status and source rules before visual emphasis | Requires Sprint 17/18 approval |
| Operational pages `/price/`, `/contacts/`, `/policies/` | Task-first, dense, utility-oriented | Avoid decorative hero cards where user needs action |
| Legacy compatibility `/aiagents/` | Keep compatibility/service framing until Sprint 18 SEO decision | Do not visually imply it replaced `/agents/` product URL |

Hero rule: first viewport can be visually rich, but operational/product tools must remain usable and scannable. Do not add decorative orbs/blobs as backgrounds.

## Proof / Status UI Rules

Proof/status visuals must map to evidence states and never upgrade a claim by design treatment.

| State | Meaning | Visual Direction | Public Copy Rule |
|---|---|---|---|
| `public-safe` | Approved public source and wording exist | Stable badge/source note | Can publish approved wording |
| `private-evidence` | Evidence exists but is private/NDA | Muted badge, request/discussion note | Do not reveal details |
| `pilot-artifact` | Pilot checks/artifacts can be described | Process/readiness badge | Describe what is checked, not achieved result |
| `pending` | Source/approval missing | Neutral pending state | Do not publish claim |
| `blocked` | Not approved or unsafe | Do not render as positive proof | Do not publish |

Legal gate: proof/status component cannot be implemented until Legal/PM approve state labels, colors and copy.

## Architecture And Procurement Diagram Spec

| Diagram Type | Desktop Pattern | Mobile Pattern | Safe Labels |
|---|---|---|---|
| Platform layers | Three-layer stack: product layer, platform services, customer contour | Vertical stacked blocks | Runtime, RAG, access, audit, integrations |
| Data/access flow | Left-to-right flow with decision/checkpoints | Step list with numbered checkpoints | Data source, role, approval, log, owner |
| Procurement/security checklist | Dense checklist/table | Accordion or grouped list | Data, access, logs, integration, ownership, support boundary |
| Product comparison | Matrix/table with criteria rows | Stacked comparison rows | Fit, not fit, pilot input, output, limitation |

Diagram rules:

- no pseudo-certifications or shield badges implying compliance;
- no unapproved customer logos or metrics;
- labels must remain readable on mobile without overlapping;
- diagrams need text fallback for SEO/accessibility.

## Form / Modal / CTA State Matrix

| State | Required Visible Behavior | Contract Constraint |
|---|---|---|
| Normal | Clear required fields, consent, CTA and optional qualification | Preserve `[data-tacticum-form]`, `data-form-id`, names |
| Focus | Visible accessible focus ring | Do not remove keyboard navigation |
| Validation error | Field highlight and short hint; consent error visible | Preserve client validation and backend validation |
| Loading | Submit disabled, spinner/text change | Preserve `data-role='spinner'`, `data-role='btn-text'` where used |
| CSRF/origin failure | Generic error, no technical details | Do not expose raw backend messages beyond safe copy |
| Upstream/network error | Retry-friendly error state | No raw upstream body |
| Success | Context-specific next-step copy | No CRM/internal queue details |
| Modal close | Restores body scroll/focus expectations | Preserve modal selectors/contracts |

Implementation rule: if state visuals require markup/selector changes, classify as `contract-migration` in `08-as-is-to-be-migration-map.json`.

## Chat State Matrix

| State | Required Visible Behavior | Contract Constraint |
|---|---|---|
| Empty | Intro and quick replies are visible | Preserve chat surface selectors |
| Typing/loading | Message area remains stable; user understands wait state | No layout jump |
| Long answer | Internal scroll or wrapping; no overlap with CTA/form | Preserve mobile readability |
| Error | Safe retry/error copy | No raw prompt/upstream response |
| Prefill available | Handoff action clear and scoped | Do not send message text to analytics |
| Prefill success/error | Visible confirmation/failure without raw data | Preserve prefill contract |
| Mobile | Input and actions reachable; content does not overflow viewport | Browser smoke required after implementation |

## `/price/` Mobile Team-Builder Recommendation

Recommended v1 mobile direction: summary-first with stable inline builder and modal only for focused specialist/order details.

| Area | Recommendation |
|---|---|
| Team summary | Sticky or near-top summary after first role selection; stable height |
| Role selection | Keep cards/filter controls scannable; avoid hiding all roles behind a modal |
| Specialist details | Modal can remain for order details if existing selectors/contracts are preserved |
| Presets | Presets remain quick-start controls; smoke must still cover them |
| Budget estimate | Keep visible as estimate, not final quote |
| Submit | Preserve `price-specialist`, `workers_json`, `team_preset`, `monthly_budget_estimate` |

Do not rewrite `/price/` mobile flow without `browser:smoke:price` coverage and explicit selector/payload preservation.

## Icon Taxonomy

Recommendation: keep RemixIcon as runtime icon set for v1 and introduce a curated usage taxonomy before considering migration.

| Icon Use | Policy |
|---|---|
| Navigation/commands | Use clear familiar icons only where they improve scanning |
| Product cards | Use consistent product/category icons, not decorative variety |
| Status/proof | Icons must not imply approval/certification unless evidence state allows it |
| Forms/errors | Use standard success/error/loading affordances |
| New icon set | Requires asset/CSP/performance review and template guard update |

Migration to another icon library is a separate design/frontend scope.

## AS IS -> TO BE Traceability

| TO BE Area | AS IS Contract | Migration Type Baseline | Guard |
|---|---|---|---|
| Navigation | `global-navigation` | visual-restyle | `design:components:check` |
| Contact modal | `contact-modal` | visual-restyle | `design:components:check` |
| Lead CTA/forms | `lead-cta-form` | visual-restyle unless payload changes | `design:components:check`, `js:check` |
| Chat | `chat-surface` | visual-restyle unless behavior changes | `design:components:check`, `browser smoke` |
| FAQ | `faq-accordion` | visual-restyle | `design:components:check` |
| `/price/` team builder | `price-team-builder` | contract-preserving-split | `browser:smoke:price` |
| Product blocks | `product-page-blocks` | contract-preserving-split | product block markers and visual smoke |
| Proof/status UI | backlog component | new-interaction | Legal + Design + QA gate |
| Architecture diagrams | backlog component | new-interaction | Architect + Design + responsive smoke |

Traceability rule: every TO BE visual implementation task must list affected AS IS component ids, migration type, preserved selectors and smoke commands.

## Implementation Gates

Do not implement visual/runtime changes until:

1. Designer and Frontend approve token source and CSS mapping.
2. PM/Designer approve density, hero and page family taxonomy.
3. Legal/PM approve proof/status labels and visual semantics.
4. QA approves form/chat/price state smoke coverage.
5. Architect approves architecture/procurement diagram labels.

Docs/design-only verification:

```bash
npm run design:tokens:check
npm run design:components:check
npm run design:migration:check
npm run design:handoff:check
npm run product:gaps:check
```

Implementation verification must add focused checks:

```bash
npm run css:check
npm run template-styles:check
npm run js:check
npm run visual:smoke:css-local
npm run browser:smoke:price
```

## Open Decisions

| Decision | Status | Owner |
|---|---|---|
| Figma variable taxonomy and repo JSON bridge shape | pending | Designer + Frontend |
| Exact TO BE status colors and proof labels | pending | Designer + Legal + PM |
| `/price/` mobile summary-first details | pending | Designer + Frontend + QA |
| Whether product blocks become components before restyle | pending | Architect + Frontend + Designer |
| Whether icon taxonomy stays RemixIcon-only or migrates later | pending | Designer + Frontend |
