# Product Frontend Component Hardening Decision

Дата: 04.06.2026
Статус: draft / approval pending
Sprint: `docs/workflow/sprints/2026-06-04-sprint-21-frontend-component-hardening.md`
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`

## Назначение

Этот документ фиксирует Sprint 21 baseline по frontend/component hardening перед крупными изменениями UI and interaction layer.

Решение намеренно не делает runtime-рефакторинг. Оно задаёт границы, при которых product partials, `/price/`, forms, chat, FAQ/content wrappers and JS module policy можно менять без потери текущих contracts, smoke coverage and security posture.

## Covered Gaps

| Gap | Sprint item | Decision baseline | Remaining gate |
|---|---|---|---|
| `ARCH-002` | S21-001 | Product renderer keeps fixed v1 block order | ADR only if configurable order is introduced |
| `CMP-001` | S21-002 | Product blocks stay PHP partials until promotion criteria trigger | Architect + Frontend approval |
| `CMP-002` | S21-003 | Screenshot previews remain v1; isolated fixtures are scoped to high-risk components | Designer + QA approval |
| `CMP-004` | S21-004 | `/price/` split plan is contract-preserving and smoke-gated | `browser:smoke:price` before/after implementation |
| `CMP-005` | S21-005 | `forms.js` split plan preserves payload, consent, CSRF and analytics | Contract + QA gate |
| `CMP-006` | S21-006 | `chat-agent.js` split plan preserves surfaces, prefill and handoff | Integration + QA gate |
| `CMP-007` | S21-009 | FAQ/content wrapper guard should cover semantic lookup and fallback behavior | QA/config gate |
| `CFG-005` | S21-010 | Numeric FAQ fallback is allowed only as explicit config/default decision, not component hardcode | Config owner approval |
| `STACK-002` | S21-007 | Vanilla JS + Bitrix Asset remains v1 module policy | ADR if stack/build model changes |
| `STACK-005` | S21-008 | Fixture-driven smoke map is tied to product/forms/chat/price states | QA fixture approval |
| `SEC-001` | S21-011 | Trusted browser-source CSRF fallback remains accepted-monitor for current public lead sensitivity only | Revisit on sensitive/private flows |

## Current Runtime Baseline

| Area | Current state | Contract to preserve |
|---|---|---|
| Product renderer | Product pages use `local/php_interface/include/product_page.php` and PHP partials under `local/php_interface/include/product_page_blocks/` | `data-product-block`, schema/render ordering, product source diagnostics, lead CTA context |
| Product previews | `product:block-previews` captures product blocks on `/platform/`, `/agents/`, `/dev/`, `/forum/` | Required block inventory and screenshots remain release evidence |
| `/price/` | `news.list/price/script.js` supports current and legacy selectors, team presets, fallback modal and staff order payload | `data-price-*`, `workers_json`, `team_preset`, `monthly_budget_estimate`, endpoint `/local/rest/tacticum_sale_staff.php` |
| Forms | `local/templates/tacticum/js/forms.js` owns public form validation, payload, consent, CSRF and analytics hooks | `data-tacticum-form`, `data-form-id`, consent, existing response/error model, no raw PII analytics |
| Chat | `local/templates/tacticum/js/chat-agent.js` owns hero/light chat, calculator/price surfaces, prefill and lead handoff | `group_id`, scoped prefill, safe analytics booleans, no payload contract drift |
| FAQ/content wrappers | `tacticum:faq.section` and `tacticum:content.list/detail` normalize Bitrix component params | Semantic `SECTION_KEY` first; numeric fallback only through content config |
| Stack | Bitrix SSR + vanilla JS assets loaded via `Bitrix\Main\Page\Asset` | No SPA/bundler/framework unless ADR and deploy model are approved |

## Product Renderer Order Decision

V1 keeps fixed renderer block order.

Rationale:

- product pages currently serve product positioning and lead generation, not per-product editorial page builders;
- schema validation and block screenshots are simpler and safer when order is stable;
- configurable ordering would create a new content governance model, editor UX, validation rules and SEO/render acceptance criteria.

Current v1 order can be changed only as code/design implementation with updated product schema/render checks. Per-product configurable order requires ADR and must define:

- allowed block types and order constraints;
- required block minimums;
- validation errors shown in release evidence;
- cache/version invalidation;
- SEO/H1/schema guard;
- rollback to a known safe order.

## Partial vs Local Component Criteria

Product page blocks stay PHP partials by default.

A product block can graduate to a local Bitrix component only when at least one of these criteria is true:

| Criterion | Meaning | Required evidence |
|---|---|---|
| Reuse | The same block is reused outside product pages or across unrelated section owners | Source inventory and params contract |
| Independent state | The block gains JS state, async data, form behavior or analytics events | Component state contract and smoke case |
| Separate cache | The block needs independent cache key, tag invalidation or data source lifecycle | Cache/ADR review |
| Complex params | The block needs a stable public parameter API beyond current partial props | Component parameter contract |
| Preview need | Designer/QA need isolated state previews for this block | Fixture/screenshot acceptance |
| Owner boundary | Content/PM/Frontend ownership differs from the rest of product renderer | DoR owner and review path |

Do not promote all product blocks by default. Mechanical componentization would add Bitrix overhead without reducing the current risk.

## Preview Fixture Decision

V1 keeps `product:block-previews` as the product-page preview evidence.

Isolated fixtures are approved only for high-risk behavior-bearing components:

| Component | Fixture trigger | Minimum fixture states |
|---|---|---|
| `/price/` team builder | Any split or mobile rewrite | empty, filtered, preset selected, multi-worker, modal open, payload fields filled |
| Lead forms | Payload, validation, consent or success-state change | empty validation, consent missing, loading, success, controlled error |
| Chat | Chat surface, prefill or handoff change | empty send, pending, long answer, controlled error, handoff to form |
| Product proof/status | New proof UI implementation | public, private/NDA, pending, blocked |
| FAQ wrapper | Section lookup/fallback change | semantic section found, fallback used, intentional empty |

Static product blocks can remain screenshot-based until they gain independent behavior or owner-specific preview needs.

## `/price/` Decomposition Plan

The current `/price/` script should be split only after a clean baseline of `browser:smoke:price`.

Recommended split order:

| Module | Responsibility | Contract notes |
|---|---|---|
| `price-dom` | Selector lookup, legacy/current selector compatibility, DOM references | Preserve all `data-price-*` selectors and current legacy fallback until rollout risk is retired |
| `price-state` | Selected workers, filters, presets, totals, derived monthly budget | No DOM side effects in pure state helpers |
| `price-filters` | Category tabs, search, empty state and result summary | Preserve reset/search smoke |
| `price-presets` | Team preset application and summary labels | Preserve `team_preset` hidden field |
| `price-modal` | Modal open/close, focus and selected worker rendering | Keep fallback modal only until production rendered template no longer needs it |
| `price-payload` | Hidden fields and staff-order form data | Preserve `workers_json`, `amount_of_workers`, `monthly_budget_estimate`, start/end dates |
| `price-analytics` | Safe events and smoke-friendly markers | No raw contacts/task text |

Implementation rule: each split must pass `npm run js:check` and focused `/price/` smoke before and after the change. Payload or endpoint changes are out of scope and require Security / Integration review.

## `forms.js` Modularity Plan

Keep `forms.js` as the v1 orchestrator until form behavior changes grow beyond a small patch.

If split is needed, split by responsibility:

| Module | Responsibility | Must preserve |
|---|---|---|
| `forms-selectors` | Find form, consent, submit and fields | `data-tacticum-form`, `data-form-id`, consent selectors |
| `forms-validation` | Required fields, consent and user messages | Current error shape and accessible state |
| `forms-payload` | FormData/JSON construction and lead context | `lead_*`, `group_id` scoping, staff-order passthrough |
| `forms-transport` | POST endpoint call and CSRF/session handling | Current endpoint and response model |
| `forms-ui-state` | loading, disabled, success/error, modal close target | No inline style regressions |
| `forms-analytics` | Safe submit/success/failure events | No PII params |

No split may change `form_id`, consent, CSRF behavior, response handling or upstream payload without updating `lead-form-contract.md`.

## `chat-agent.js` Modularity Plan

Keep `chat-agent.js` as the v1 single owner of chat surfaces until a chat behavior change is approved.

If split is needed, split by surface and shared contract:

| Module | Responsibility | Must preserve |
|---|---|---|
| `chat-transport` | `/local/rest/tacticum_chat.php`, CSRF, response handling | Existing request/response and controlled errors |
| `chat-state` | messages, pending state, active group and retries | `group_id` continuity |
| `chat-renderer` | bubbles, quick replies, loading/error UI | Safe long-answer rendering |
| `chat-surface-hero` | Homepage/product hero style chat | Existing hero behavior |
| `chat-surface-light` | Calculator and `/price/` light chat | Current page-specific handoff |
| `chat-prefill` | Scoped prefill and lead-form handoff | No raw message analytics; only scoped target form receives context |
| `chat-analytics` | Safe events | Boolean/status params only |

Any change to `group_id`, prefill, endpoint, analytics payload or handoff form targeting is Security / Integration scope.

## JS Module And Test Policy

V1 stack remains Bitrix SSR + vanilla JS loaded through `Bitrix\Main\Page\Asset`.

Policy:

- use page/component-scoped asset files for new behavior;
- keep init idempotent and resilient to missing DOM;
- prefer plain JS files while the current asset order is Bitrix-managed;
- do not introduce ES modules, bundler, TypeScript, React, Vue or SPA routing without ADR;
- if ES modules are introduced, ADR must cover browser support, deploy asset order, cache busting and fallback behavior;
- all behavior-bearing selectors must be reflected in component state/migration docs when changed;
- no JS-generated large markup unless it is a temporary compatibility fallback with a removal trigger.

Minimum verification by change type:

| Change type | Required checks |
|---|---|
| Any JS change | `npm run js:check` |
| Product block change | `npm run product:block-previews` or production equivalent after deploy |
| `/price/` change | `npm run browser:smoke:price` |
| Forms/chat change | `npm run browser:smoke` plus controlled/manual success-flow when endpoint behavior is affected |
| Design selector change | `npm run design:components:check` and `npm run design:migration:check` |
| Public URL/head change | `npm run seo:check` and production SEO smoke after deploy |

## Fixture-Driven Smoke Map

| Surface | Current guard | Fixture backlog |
|---|---|---|
| Product pages | `product:block-previews`, `product:content:check:strict`, `product:source:http:prod` | Block order fixture and missing-block negative evidence |
| `/price/` | `browser:smoke:price` | Deterministic fixture for preset/multi-worker/modal/payload states |
| Forms | `browser:smoke` non-network validation, manual success-flow helper | Local no-network fixture for validation/loading/error/success states |
| Chat | `browser:smoke`, manual success-flow helper for controlled chat | Mocked response fixture for pending/error/long-answer/handoff states |
| FAQ/content wrappers | `bitrix:check`, rendered page smoke | Static/HTML guard for `SECTION_KEY`, fallback config and empty-section behavior |

QA should approve fixture scope before adding new tooling. The default path is to extend existing smoke only where the state has real regression risk.

## FAQ Wrapper And Config Decision

Semantic FAQ lookup remains the primary model: components pass `SECTION_KEY`, and `tacticum:faq.section` resolves the Bitrix section by semantic code.

Numeric fallback remains a safety net only when configured through `content.faq_section_fallback_ids`.

Decision baseline for `CFG-005`:

- production may use documented defaults only if Content + Backend explicitly accept default-owned fallback behavior;
- otherwise production config should explicitly set `content.faq_section_fallback_ids`;
- component code must not hardcode numeric FAQ section IDs;
- `config:runtime:check` evidence must state whether FAQ fallback is explicit or default;
- any FAQ wrapper change should add a guard or smoke case for semantic found, fallback used and intentional empty states.

## SEC-001 Accepted-Risk Review

`SEC-001` remains `accepted-monitor` for the current public lead-form sensitivity only.

This accepted state is invalidated if any of the following are introduced:

- private proof downloads;
- procurement/security document requests;
- authenticated or semi-private lead flows;
- customer-specific evidence packets;
- file upload;
- endpoint carrying materially more sensitive data than current public lead/contact forms.

On any trigger, the flow must move to Security / Integration lane and re-evaluate CSRF, origin, auth/access, rate limit, logging and no-PII evidence.

## Implementation Gates

| Change | Gate |
|---|---|
| Configurable product renderer order | ADR + Product schema/render guard + SEO smoke |
| Product partial promoted to local component API | ADR if shared API, component params contract, design/component guard |
| `/price/` JS split | Clean baseline and post-change `browser:smoke:price` |
| Form payload/response change | `lead-form-contract.md`, Security / Integration, controlled success-flow |
| Chat/prefill/handoff change | `chat-api-contract.md` / `chat-offer-contract.md`, Security / Integration, controlled smoke |
| FAQ fallback behavior change | Config evidence and FAQ wrapper smoke |
| Frontend stack/build change | ADR and deploy asset/cache plan |

## Verification For This Docs-Only Baseline

Expected local docs/static checks:

```bash
npm run bitrix:check
npm run js:check
npm run product:gaps:check
npm run design:components:check
npm run design:migration:check
npm run design:handoff:check
npm run config:check
npm run seo:check
npm run gaps:known
git diff --check
```

Browser/product smoke is required when runtime implementation changes land; this Sprint 21 package itself does not change runtime behavior.

## Remaining Decisions

| Decision | Owner | Status |
|---|---|---|
| Approve fixed v1 product order | Architect + PM + Frontend | pending |
| Approve partial/component promotion criteria | Architect + Frontend | pending |
| Approve fixture scope and smoke expansion | QA + Designer + Frontend | pending |
| Approve `/price/` split order | Frontend + QA | pending |
| Approve forms/chat split only-on-trigger rule | Frontend + Backend + Security + QA | pending |
| Approve FAQ fallback explicit/default rule | Content + Backend | pending |
| Reconfirm `SEC-001` accepted-monitor trigger list | Security + Backend + QA | pending |
