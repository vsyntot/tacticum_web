# 19. Phase 3 Architecture And Integration Decision Pack

Дата: 02.06.2026

Статус: draft review package для Phase 3 architecture / integration decisions. Документ не меняет кодовые контракты и не закрывает external gates; он фиксирует рекомендованный v1 путь и условия, при которых нужно переходить к ADR или Security / Integration lane.

## Назначение

Phase 3 из `15-gap-closure-master-plan.md` должен решить, как масштабировать product-first MVP без потери текущих безопасных контрактов. Этот документ переводит архитектурные gaps в конкретный набор решений для Architect, Dev, Content, Backend, PM, QA and Analytics.

Covered gaps:

- `ARCH-001` - product content ownership;
- `ARCH-002` - product renderer / component boundary;
- `ARCH-003` - structured lead qualification and upstream/CRM fields;
- `ARCH-004` - product analytics goals and evidence;
- `CJM-006` - structured CRM/upstream qualification.

## Current Implementation Baseline

| Area | Current state | Files / contracts |
|---|---|---|
| Product data | Core product content lives in Git-reviewed PHP data files | `local/php_interface/include/product_data/*.php` |
| Product renderer | Product pages call shared bootstrap and block partials | `local/php_interface/include/product_page.php`, `product_page_blocks/*.php` |
| Page entries | Public product pages are thin orchestration files | `/platform/`, `/agents/`, `/dev/`, `/forum/` |
| Block identity | Rendered blocks expose stable `data-product-block` markers | `hero`, `fit-guide`, `architecture`, `use-cases`, `comparison`, `procurement`, `rollout`, `proof`, `faq`, `lead-cta` |
| CTA/form | Product pages reuse existing `tacticum:lead.cta` and `/local/rest/tacticum_form.php` | `lead-form-contract.md`, `forms.js` |
| Lead qualification | Backend builds internal canonical profile and appends safe text context to upstream `task` | `tacticum_form_build_lead_profile(...)`, `tacticum_form_build_lead_context(...)` |
| Upstream payload | No top-level structured product fields are sent to external sale endpoint yet | Existing response shape and endpoint path preserved |
| Analytics | Safe product events exist with allowlisted params and no PII | `analytics.js`, `forms.js`, `seo-check.mjs` |
| External evidence | Metrika goals, CRM/upstream confirmation and manual success-flow remain external | `gaps:known`, release sign-off drafts |

## Decision 1 - Product Content Ownership

Recommended v1 decision for `ARCH-001`: keep core product content in Git-reviewed structured data files for the first TO BE release. Do not introduce Bitrix product iblocks until content owners, proof governance and admin QA are ready.

| Option | Use now? | Why |
|---|---|---|
| Page-local PHP arrays | No | Already retired for product pages; would reintroduce duplicated page logic |
| Shared Git PHP data files | Yes for v1 | Keeps product facts reviewable, feeds HTML/schema/CTA consistently, avoids admin workflow risk |
| Bitrix iblock products | Not yet | Needs content model, permissions, admin QA, SEO/schema mapping and ADR |
| Hybrid Git + Bitrix proof/cases | Candidate TO BE target | Good when proof/cases/FAQ need non-developer updates, but only after proof model approval |

V1 rules:

- keep product identity, one-liners, page sections, FAQ and safe proof-readiness in Git data;
- keep evidence-heavy proof/cases out of public UI until `PB-005` and `PB-006` are approved;
- do not add hardcoded iblock IDs;
- use `tacticum_rest_get_iblock_id('key')` if a future approved model needs iblocks;
- update product schema from the same structured source that renders HTML.

ADR gate:

- ADR is required if product facts, proof, cases, FAQ or pricing move to Bitrix/hybrid ownership.

Approval needed:

- Architect + Dev + Content approve Git-only v1 or request ADR for hybrid.

## Decision 2 - Renderer And Component Boundary

Recommended v1 decision for `ARCH-002`: keep the current product renderer split into bootstrap/helpers plus PHP block partials. Do not convert product blocks to Bitrix local components until TO BE design variants and content model are approved.

| Layer | V1 decision | Change trigger |
|---|---|---|
| `product_page.php` | Keep as bootstrap/data/schema/block include layer | Split only if helper surface grows or ADR requires it |
| `product_page_blocks/*.php` | Keep visual block partials | Convert to local components only after repeated cross-page state/preview needs |
| `tacticum:lead.cta` | Keep as the only product conversion component | Do not duplicate form markup in product blocks |
| FAQ | Keep existing FAQ asset and block contract | Change only if TO BE design requires new state contract |
| Product previews | Use `product:block-previews` first | Add Storybook/local isolated previews only if Designer/QA need component-level workflow |
| `data-product-block` | Preserve stable markers | Any rename needs SEO/visual smoke and release sign-off updates |

Implementation rules:

- new visual blocks should live under `product_page_blocks/` until component architecture ADR says otherwise;
- block partials must not create new form endpoints or inline JS/CSS;
- shared product blocks should remain server-rendered for SEO;
- any new page-specific JS must be attached through Bitrix Asset page flags;
- rendered smoke and block preview tooling remain the practical AS IS review workflow.

ADR gate:

- ADR is required if product page blocks become a formal local component system, preview system or reusable component API.

Approval needed:

- Architect + Frontend confirm current partial taxonomy is enough for v1.

## Decision 3 - Lead Qualification And CRM/Upstream Fields

Recommended v1 decision for `ARCH-003` and `CJM-006`: keep the current canonical profile plus `task` fallback for the first release. Do not send structured qualification as top-level upstream fields until CRM/upstream support is confirmed.

Current canonical profile:

| Canonical field | Source | V1 handling |
|---|---|---|
| `product_interest` | `lead_product` | Appended to `task` as safe text context |
| `use_case_interest` | `lead_scenario` | Appended to `task`; known slugs get human-readable labels |
| `deployment_interest` | `lead_next_step` | Appended to `task` |
| `funnel_entry` | `lead_entry` | Appended to `task` |
| `funnel_stage` | `lead_page_role` | Appended to `task` |
| `lead_intent` | `lead_intent` | Appended to `task` |
| `cta_id` | `lead_cta`, `form_id` | Appended to `task` |
| `budget_band` | `lead_budget` | Appended to `task`; controlled values are labelized |
| `timeline_band` | `lead_timeline` | Appended to `task`; controlled values are labelized |
| `industry` | `lead_industry` | Appended to `task` |
| `offer_code` / `offer_title` | offer detail context | Appended to `task` |

Structured upstream candidate fields:

| Field | Priority | Reason | Send now? |
|---|---|---|---|
| `product_interest` | P1 | Sales routing by Platform/Agents/Dev/Forum | No |
| `use_case_interest` | P1 | Scenario qualification | No |
| `deployment_interest` | P1 | Security/procurement and rollout routing | No |
| `funnel_entry` | P2 | Source attribution | No |
| `funnel_stage` | P2 | CJM/funnel analytics | No |
| `cta_id` | P2 | CTA quality review | No |
| `budget_band` | P2 | Commercial qualification | No |
| `timeline_band` | P2 | Sales prioritization | No |

Security / Integration gate for structured fields:

1. CRM/upstream confirms accepted field names, types and max lengths.
2. `docs/workflow/lead-form-contract.md` is updated before implementation.
3. Backend keeps `task` fallback until staged evidence proves structured fields are received.
4. QA adds smoke for default lead, product CTA, offer detail, price staff-order and AI handoff.
5. Logs and analytics still exclude PII beyond existing approved lead payload.
6. Release sign-off captures upstream/CRM evidence without committing PII.

Decision needed:

- Backend + PM + QA approve fallback for v1 or open Security / Integration task for structured fields.

## Decision 4 - Product Analytics And Metrika Evidence

Recommended v1 decision for `ARCH-004`: keep current safe product funnel events and treat Metrika goal configuration as an external evidence gate. Do not add new analytics params until PM/Analytics/QA approve a goal map.

Current safe events:

| Event | Params | Source |
|---|---|---|
| `tacticum_product_view` | `product`, `page_role`, `page_path` | `analytics.js` |
| `tacticum_product_cta_click` | `product`, `page_role`, `cta`, `page_path` | `analytics.js` |
| `tacticum_product_form_submit` | `product`, `page_role`, `scenario`, `form_id`, `endpoint`, `page_path` | `forms.js` |
| `tacticum_product_form_success` | `product`, `page_role`, `scenario`, `form_id`, `endpoint`, `status`, `page_path` | `forms.js` |
| `tacticum_product_form_error` | `product`, `page_role`, `scenario`, `form_id`, `endpoint`, `status`, `code`, `page_path` | `forms.js` |

Do not send:

- name, email, phone, company, free-form message;
- raw URL query strings with sensitive values;
- offer titles beyond approved form context;
- budget/timeline as analytics params unless PM/Analytics explicitly approve controlled bands.

Metrika goal map for review:

| Goal | Event | Closure evidence |
|---|---|---|
| Product page viewed | `tacticum_product_view` | Metrika goal receives product/page_role without PII |
| Product CTA clicked | `tacticum_product_cta_click` | Goal fires on `#contact-form` clicks on product pages |
| Product form submitted | `tacticum_product_form_submit` | Goal fires before request, no PII params |
| Product form accepted | `tacticum_product_form_success` | Goal fires after successful response |
| Product form failed | `tacticum_product_form_error` | Goal fires with status/code only |

Evidence rules:

- screenshots/exports must not include personal data;
- goal names should match event names or a documented mapping;
- production evidence belongs in release sign-off, not in source code comments;
- `gaps:known:strict` cannot pass until Metrika external gate is closed.

Decision needed:

- PM + Analytics + QA approve goals and provide external evidence.

## Decision 5 - Product Qualification CJM

Recommended v1 decision for `CJM-006`: use existing controlled `lead_scenario` select on product pages and current context fallback. This gives Sales enough first-pass routing while avoiding an unapproved CRM payload migration.

| Product | Current qualification values | Sales meaning |
|---|---|---|
| Platform | `platform-assessment`, `platform-pilot`, `deployment-readiness` | Assess existing AI contour, pilot platform core, review deployment constraints |
| Agents | `agent-scenario-selection`, `rag-documents-check`, `pilot-rollout` | Pick first internal assistant scenario, check RAG documents, plan rollout |
| Dev | `ai-workflow-assessment`, `quality-gates-pilot`, `design-system-guardrails` | Assess AI-assisted workflow, pilot gates, connect with design system |
| Forum | `dialog-flow-assessment`, `scenario-llm-pilot`, `support-analytics-review` | Review request flow, pilot scenario+LLM, review analytics/escalation |

V1 acceptance:

- product page CTA keeps controlled scenario values;
- backend keeps human-readable context inside `task`;
- no new required fields are added to public forms;
- Sales confirms fallback text is usable for routing;
- CRM/upstream structured fields are a separate decision.

## Phase 3 Closure Checklist

| Gap | Close only when |
|---|---|
| `ARCH-001` | Architect + Dev + Content approve Git-only v1 or ADR for Bitrix/hybrid |
| `ARCH-002` | Architect + Frontend approve partial-based v1 or ADR for component/preview system |
| `ARCH-003` | Backend + PM + QA approve task fallback for v1 or structured upstream contract |
| `ARCH-004` | PM + Analytics + QA approve Metrika goals and provide no-PII evidence |
| `CJM-006` | Sales/PM confirm current qualification context is usable or CRM fields are approved |

## ADR And Lane Matrix

| Decision | ADR needed? | Workflow lane |
|---|---|---|
| Keep Git-only product data for v1 | No, document decision in this pack and current-state | Full Feature / Architecture review |
| Move product proof/cases/FAQ to Bitrix/hybrid | Yes | Full Feature |
| Keep block partial renderer for v1 | No, document decision in this pack | Full Feature / Frontend |
| Convert product blocks to local components/previews | Yes if it becomes shared architecture | Full Feature |
| Keep canonical profile + `task` fallback | No, already covered by `lead-form-contract.md` | Security / Integration review optional |
| Send structured CRM/upstream fields | Yes or contract decision update | Security / Integration |
| Keep current analytics event names | No | QA + Analytics review |
| Add new product analytics params or goals | Contract update and external evidence | Security / Integration if data policy changes |

## Recommended Review Session

1. Architect confirms content ownership and renderer boundary.
2. Frontend confirms partial/component/previews path for TO BE implementation.
3. Backend + QA review lead qualification fallback and structured field gate.
4. PM + Sales confirm current product scenario context is useful enough for v1.
5. Analytics/QA confirm Metrika goal map and evidence requirements.
6. If any v1 recommendation changes, update ADR/docs before code changes.
