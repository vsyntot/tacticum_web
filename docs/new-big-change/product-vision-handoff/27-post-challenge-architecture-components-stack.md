# 27. Post-Challenge Architecture / Components / Stack Detail

Дата: 02.06.2026

Статус: post-challenge architecture brief for Architect, Frontend, Backend, QA and DevOps.

## Назначение

Challenge подтвердил: текущая техническая база достаточно здорова для v1, но не должна бесконтрольно расти как набор PHP partials, safe-copy sections and manual release assumptions.

Цель этого документа - отделить:

- что сохраняем как v1 baseline;
- где нужен ADR или Security / Integration lane;
- какие stack improvements имеют смысл;
- чего не стоит делать ради визуального редизайна.

## Current Baseline

| Layer | Current state | Challenge verdict |
|---|---|---|
| Platform | PHP 8.4 + 1C-Bitrix | Keep |
| Custom code | isolated in `local/` | Keep |
| Product pages | `/platform/`, `/agents/`, `/dev/`, `/forum/` | Keep |
| Product data | Git-owned `product_data/*.php`, Bitrix target SoT selected in ADR-010 | Git data remains fallback/seed; Bitrix becomes target content ownership |
| Renderer | `product_page.php` bootstrap + `product_page_blocks/*.php` | Good v1 baseline, watch growth |
| Block identity | `data-product-block` markers | Keep as QA/design locator contract |
| Forms | `tacticum:lead.cta`, `forms.js`, `/local/rest/tacticum_form.php` | Preserve |
| Qualification | `lead_*` canonical profile + `task` fallback | Safe v1, not mature CRM model |
| Analytics | no-PII product events | Keep, external goal evidence pending |
| CSS | static Tailwind + `styles/global.css` | Keep, needs token pipeline |
| JS | vanilla JS and page asset flags | Keep for current interactions |
| Release | local guards strong | External evidence still blocking |

## V1 Architecture Recommendations

| Decision | Recommendation | Why |
|---|---|---|
| Product content ownership | Move product content to Bitrix via controlled CLI migration, keep Git fallback | Owner decision accepted: editors need Bitrix content management, but rollout must preserve current pages |
| Proof/cases ownership | Keep evidence-heavy proof out of public UI until model is approved | Prevents false vendor maturity |
| Product renderer | Keep partial split; do not convert all blocks to components yet | Current system is simple, server-rendered, SEO-safe |
| Component preview | Keep `product:block-previews`; add isolated previews only if design/QA needs it | Avoid Storybook/process overhead too early |
| Forms | Preserve current form contract | Prevents conversion regression |
| CRM/upstream | Keep `task` fallback until structured fields are accepted | Avoid unapproved payload migration |
| Analytics | Keep safe events; close Metrika evidence externally | No PII and no goal drift |
| Frontend framework | Do not introduce SPA/React/Next for product pages | Bitrix server render is enough and safer |
| CSS/tokens | Add approved token mapping before redesign implementation | Avoid new visual drift |

## Component Boundary Decision

Current product blocks are PHP partials, not formal components. That is acceptable for v1.

Promote a partial to a local Bitrix component only when at least one condition is true:

- the block has complex interactive state;
- the block is reused outside product pages;
- Designer/QA need isolated preview and state testing;
- the block owns a stable API-like parameter contract;
- multiple products need divergent variants that are hard to maintain in a shared partial.

Candidate promotion order:

| Candidate | Promote when | Current action |
|---|---|---|
| `ProofStatusBlock` | proof/status taxonomy is approved | Wait for `PB-005`, `PB-006`, `UI-005` |
| `ArchitectureDiagram` | diagram anatomy and mobile fallback are approved | Design first |
| `PilotKitCard` | use-case pilot kit model is approved | Design/content first |
| `ProcurementBlock` | docs/security request path is approved | Security/legal first |
| `LeadCTAForm` | already componentized as `tacticum:lead.cta` | Preserve |
| `/price/` TeamBuilder | mobile UX changes require stateful work | Design + QA before code |

## Product Data Model Challenge

Git-owned data remains useful while:

- product facts are reviewed by developers/PM;
- proof is mostly safe-copy or pending;
- pages are few;
- SEO/schema/render need one source.

Bitrix product content model is now selected because:

- Content needs non-developer editing;
- proof/cases/FAQ need frequent updates;
- product-specific cases require tagging/filtering;
- private/public evidence status needs admin workflow;
- SEO wants scalable clusters beyond four product pages.

ADR-010 now defines the Bitrix model: `products`, `product_blocks`, `product_use_cases`, product relations on existing `faq`, `cases`, `offer`, `services` and `aiagents`, CLI migration with dry-run/apply, create-only seed by default, `products.source=auto|bitrix|fallback` and fallback to `product_data/*.php`.

## Lead Qualification Challenge

Current `lead_*` context is valuable but shallow.

| Layer | Current | Target |
|---|---|---|
| Frontend | controlled `lead_scenario` select and hidden context | Keep |
| Backend | canonical profile and `task` fallback | Keep for v1 |
| Upstream/CRM | receives text fallback, not structured product fields | Needs approval |
| Sales | may read context manually | Needs confirmation |
| Analytics | no-PII controlled events | Keep |

Do not add top-level structured upstream fields until:

1. CRM/upstream accepts field names, types and max lengths.
2. `lead-form-contract.md` is updated.
3. QA has smoke for default, product, offer, calculator and price flows.
4. Security confirms no PII/logging regression.
5. Release sign-off captures evidence without committing personal data.

## Stack Improvements Worth Doing

| Improvement | Priority | Notes |
|---|---:|---|
| Token mapping from Figma/JSON to Tailwind/global CSS | P1 | Required before TO BE implementation |
| Product block content schema guard | P2 | Could validate pilot kit/proof/status fields in `product_data/*.php` |
| Global CSS sectioning or component CSS policy | P2 | Useful before large redesign |
| Preview workflow documentation | P2/P3 | Current screenshot previews may be enough |
| Product analytics goal map | P2 | Needs external Metrika evidence |
| Release evidence strictness | P1 | Must distinguish local readiness from production reality |

## Stack Changes To Avoid For Now

| Avoid | Reason |
|---|---|
| SPA rewrite for marketing/product pages | Adds risk without solving current gaps |
| Runtime Tailwind | Already retired; static build is safer |
| Inline JS/CSS for new TO BE blocks | Violates Bitrix asset discipline |
| Duplicated form endpoints | Breaks lead contract and QA scope |
| Product pricing schema/offers JSON-LD | Not approved and can create SEO/legal risk |
| Publishing connectors/deployment badges without readiness table | Creates false proof |

## ADR / Lane Triggers

| Change | Required route |
|---|---|
| Product facts/proof/cases/FAQ move to Bitrix/hybrid | ADR + Full Feature lane |
| Product blocks become formal component API/preview system | ADR if shared architecture pattern |
| Structured lead fields sent upstream | Security / Integration lane + contract update |
| New analytics params or PII-adjacent measurement | Security / Integration or QA/Analytics review |
| Gated document download/request flow | Full Feature + Security/Legal review |
| CSP enforce | Security / Integration lane |
| Public packaging/pricing/licensing model | PM/Sales/Legal decision, SEO/schema review |

## Architecture Acceptance Criteria

TO BE technology foundation is ready when:

- product page entries remain thin orchestration files;
- product data is structured and reused for HTML/schema/CTA;
- proof/case ownership is explicitly approved;
- form/chat/FAQ/menu contracts are preserved or migrated deliberately;
- CRM/upstream qualification has v1 fallback approval or structured field evidence;
- product analytics can be measured without PII;
- token mapping can be implemented in Tailwind/global CSS;
- external release gates are closed through sign-off evidence, not local assumptions.
