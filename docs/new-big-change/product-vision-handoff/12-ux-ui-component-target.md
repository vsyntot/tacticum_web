# 12. UX / UI / Component Target

Дата: 01.06.2026

Статус: целевой UX/UI brief для дизайнера и frontend/Bitrix реализации. Документ дополняет AS IS дизайн-пакет и фиксирует, какие компоненты нужны, чтобы TO BE сайт выглядел как enterprise software product site, а не как лендинг услуг.

## UX Goal

TO BE UX должен помогать посетителю принять решение:

```text
я понимаю, что такое Tacticum
  -> понимаю, какой продукт подходит моей ситуации
  -> вижу, как это внедряется
  -> понимаю ограничения и proof status
  -> выбираю безопасный следующий шаг
```

## UX Principles

- Product-first, service-supported: продукты впереди, услуги как путь внедрения.
- Decision support over marketing decoration: меньше общих обещаний, больше сравнения, fit guidance, pilot outputs.
- Proof with status: показывать только подтвержденное; pending proof маркировать как пилотные артефакты или не показывать.
- Preserve AS IS conversion: не ломать `/offer/`, `/price/`, `/calculator/`, формы, chat-to-lead and modal.
- Enterprise density: интерфейс должен быть плотным, сканируемым, не hero-heavy.
- Role-aware next step: CTA должен соответствовать роли и стадии клиента.

## Target Page Templates

| Template | Pages | Purpose |
|---|---|---|
| Ecosystem homepage | `/` | Explain Tacticum, route to products and commercial entry points |
| Product page | `/platform/`, `/agents/`, `/dev/`, `/forum/` | Product promise, use cases, architecture, rollout, proof, CTA |
| Delivery page | `/services/` | Explain implementation path around products |
| Estimate/proof catalog | `/offer/`, `/offer/<code>/` | Proof-like examples and conversion to personal estimate |
| Team configurator | `/price/` | Staff/team selection and budget estimate |
| Qualification/calculator | `/calculator/` | AI/chat-guided estimate entry |
| Trust/legal/contact | `/about/`, `/contacts/`, `/policies/` | Vendor trust, contact, legal and consent base |

## Product Page Target Structure

The current renderer already covers hero, cards, architecture, rollout, proof readiness, FAQ and CTA. TO BE should evolve the structure:

1. Hero with clear product promise and fit statement.
2. Product fit strip: who it is for / not for.
3. Use-case cards with trigger, owner, input, output.
4. Problem matrix: self-build/generic tools/current process failure modes.
5. Architecture diagram or module map.
6. Deployment/security/procurement block.
7. Product-specific comparison block.
8. Rollout timeline.
9. Proof status: available evidence, pilot evidence, unavailable claims.
10. FAQ.
11. Product-aware CTA.

## Component Families Needed

### Product Storytelling

| Component | Purpose | Notes |
|---|---|---|
| `product.hero` | Product name, promise, ICP, primary/secondary CTA | Variants: Platform, Agents, Dev, Forum |
| `product.fit-guide` | "Подходит / не подходит / стартуйте с..." | Critical for enterprise decision support |
| `product.use-case-card` | Trigger, buyer, pilot input/output, CTA | Should be reusable across product pages |
| `product.problem-matrix` | Current pain vs consequence vs Tacticum answer | More useful than generic feature cards |
| `product.module-grid` | Product modules with status and dependency on Platform | Keep dense and scannable |
| `product.architecture-map` | Visual layer diagram, data flow, runtime boundary | Needs designer/architect collaboration |
| `product.deployment-block` | SaaS/on-prem/hybrid/PAK readiness with caveats | Must respect claims register |
| `product.comparison` | Category comparison, not risky brand attacks | Use "generic assistant", "self-build", "pure LLM" categories |
| `product.proof-status` | Evidence available / pilot artifacts / not public | Avoid fake metrics |
| `product.rollout-timeline` | Discovery -> pilot -> integration -> rollout | Already exists as MVP block |

### Conversion

| Component | Purpose | Notes |
|---|---|---|
| `cta.product` | Product-specific lead form | Preserve `[data-tacticum-form]` |
| `cta.procurement` | Request architecture/security documentation | May be modal or form variant |
| `cta.pilot` | Pilot/discovery request | Should carry product/use-case context |
| `cta.estimate` | Project estimate flow | Reuse `/offer/` and `/calculator/` where possible |
| `sticky.mobile-action` | Mobile CTA access | Validate before implementation |

### Proof And Trust

| Component | Purpose | Evidence Requirement |
|---|---|---|
| `proof.metric-card` | Numeric outcome | Real case/benchmark/pilot only |
| `proof.case-card` | Product-related case | Approved case source |
| `proof.logo-strip` | Customer logos | Written approval |
| `proof.testimonial` | Quotes | Consent and fact check |
| `proof.source-note` | Claim source / date | Required for strong claims |
| `security.badge` | Security/deployment status | Must map to claims register |

### Enterprise UI

| Component | Purpose |
|---|---|
| `status-badge` | ready / pilot / roadmap / needs assessment |
| `decision-table` | Compare products, deployment modes, or alternatives |
| `data-flow-diagram` | Show systems, model calls, storage, audit |
| `integration-card` | Connector/channel readiness |
| `role-path-card` | CIO/CTO/Security/Functional next steps |
| `download-request-card` | Gated docs/security brief |

## State Specification Required

Designer must specify states for:

- buttons: hover, focus, active, loading, disabled;
- links: default, hover, visited if needed, focus;
- inputs/selects: default, focus, filled, invalid, disabled, autofill;
- checkbox/consent: unchecked, checked, error, focus;
- cards: default, hover, selected, disabled, status;
- accordions: closed, open, long content, keyboard focus;
- modal: opening, open, scroll, error, success, close;
- toast: success, error, network error;
- chat: initial, typing, error, long answer, handoff CTA;
- configurator: filter selected, item selected, empty state, summary state;
- diagrams: desktop, tablet, mobile stacked fallback.

Current AS IS component/state baseline is also checked: `docs/design-system-handoff/07-component-state-contract.json` records behavior-bearing components, preserved selectors and required state coverage for navigation, contact modal, lead CTA forms, chat, FAQ, `/price/` team builder and product page blocks. `npm run design:components:check` validates it against templates and JS. Designer should use this contract to mark each TO BE component as `preserve selector`, `contract-preserving split` or `contract migration`.

The first migration baseline is checked in `docs/design-system-handoff/08-as-is-to-be-migration-map.json`: every AS IS component id from `07-component-state-contract.json` has a preliminary TO BE component name, migration type, risk level and gates. `npm run design:migration:check` validates coverage and high-risk gates. Designer + Frontend should approve or edit this map before detailed Figma variants are treated as implementation-ready.

The designer work order is fixed in `docs/design-system-handoff/09-to-be-design-work-order.md`. It defines required Figma/design deliverables, state matrix, red lines and acceptance criteria. `npm run design:handoff:check` must be green after any handoff document changes before the package is treated as ready for external design work.

## UI Challenge

Current AS IS UI is stable but generic. TO BE should avoid:

- oversized marketing hero blocks with weak proof;
- one-note card grids;
- decorative architecture without operational meaning;
- repeating same module cards across products;
- publishing proof-looking UI without evidence;
- hiding commercial entry points behind product language;
- making `/price/` look like product pricing.

## Design Token Target

TO BE token spec should include:

| Token Group | Required Decisions |
|---|---|
| Color | Brand, text, surfaces, borders, status, proof/evidence, warning |
| Typography | Headings, body, labels, code/mono, dense tables |
| Spacing | Section rhythm, card padding, grid gaps, form spacing |
| Radius | Button, input, card, modal, diagram nodes |
| Elevation | Card, popover, modal, sticky header |
| Focus | Keyboard-visible focus ring and contrast |
| Motion | Accordion, modal, hover, reduced motion |
| Z-index | Header, mobile menu, modal, toast |
| Breakpoints | Mobile, tablet, desktop, wide desktop |
| Diagram | Node colors, connectors, status marks |

Source of truth decision still needed:

```text
Figma variables
  -> token JSON or documented mapping
  -> Tailwind theme / global CSS
  -> Bitrix components
```

Current AS IS baseline is now checked, not just described: `docs/design-system-handoff/05-design-tokens-as-is.json` contains implemented Tailwind tokens, observed CSS/JS token candidates and known drift, while `npm run design:tokens:check` validates it against `tailwind.css`, `global.css`, `forms.js` and `package.json`. Designer should use this as the starting map for TO BE naming and scale decisions, but the final source of truth still needs approval.

## Component Implementation Target

Current MVP uses `local/php_interface/include/product_page.php` as renderer bootstrap and `local/php_interface/include/product_page_blocks/*.php` as visual block partials. For the next stage:

- keep renderer as orchestration layer;
- evolve large visual patterns from current partials into local components or previewable templates where design/QA needs it;
- keep product page data structured;
- avoid one-off inline JS/CSS;
- keep form/menu/chat/FAQ contracts unless deliberately migrated;
- add visual smoke coverage when new interaction appears.

Current partials expose a stable AS IS locator taxonomy through `data-product-block`: `hero`, `fit-guide`, `content-section`, `architecture`, `use-cases`, `comparison`, `procurement`, `rollout`, `proof`, `faq`, `lead-cta`. Designer can use this as the bridge from existing rendered pages to TO BE component names; QA can also see the rendered inventory in `visual-smoke` manifests as `productBlocks` / `productBlockErrors`. For AS IS screenshots, use `npm run product:block-previews`, which writes per-block PNG previews to `product-blocks/*.png`; workflow details are in `docs/workflow/product-block-preview-workflow.md`. This is only a locator/screenshot evidence contract: final component anatomy, states and tokens still need design-system decisions.

## UX/UI Gap Table

| ID | Gap | Current | Target | Priority |
|---|---|---|---|---|
| UIX-001 | Product fit guide | Missing | Product comparison / route selector | P1 |
| UIX-002 | Use-case anatomy | Scenario lists | Trigger/input/output/proof cards | P1 |
| UIX-003 | Architecture visualization | Text layers and chips | Diagram-grade architecture component | P2 |
| UIX-004 | Proof system | Proof readiness text | Evidence/status/source component system | P1 |
| UIX-005 | Procurement path | Missing | Security/docs request CTA | P1 |
| UIX-006 | Product-specific page character | Same structure across products | Product-specific decision blocks | P2 |
| UIX-007 | Form states | Checked AS IS form/modal/CTA contract exists | Full visual form state design spec | P1 |
| UIX-008 | `/price/` mobile UX | Checked AS IS team-builder contract exists | Dedicated mobile team builder spec | P2 |
| UIX-009 | Chat UI | Checked AS IS hero/light chat contract exists | Conversational component spec | P2 |
| UIX-010 | Token system | Checked AS IS token contract exists; TO BE source still undecided | Formal token and mapping spec | P1 |

## Acceptance Criteria

- Designer can map each AS IS component to a TO BE component.
- Migration type is explicit for each behavior-bearing AS IS component.
- Product pages are not just four copies of the same card grid.
- Every product has a distinct use-case block and CTA logic.
- All proof-looking elements have evidence state.
- All interactive components have states.
- New visual system can be implemented in Bitrix without breaking current JS/data contracts.
- `npm run design:handoff:check` is green for the handoff package.
