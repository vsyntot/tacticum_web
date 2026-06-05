# Bitrix Componentization Execution Roadmap — 2026-06-05

Дата: 05.06.2026

Статус: execution roadmap for `bitrix-componentization-gap-analysis-2026-06-05.md`.

## Purpose

Roadmap задает порядок закрытия Bitrix/componentization gaps. Цель — не переписать сайт ради красоты, а последовательно уменьшить риск регрессий, сделать публичные страницы тонкими Bitrix entry points, оформить доменную логику в компоненты/services и расширить guards так, чтобы долг не вернулся.

## Source Register

Все IDs ниже берутся из:

- `docs/workflow/bitrix-componentization-gap-analysis-2026-06-05.md`

Если gap отсутствует в source register, его нельзя считать частью этого roadmap без обновления обоих документов.

## Execution Principles

1. Сначала ставим guards и budgets, затем двигаем код.
2. Не рефакторим одновременно product pages, `/price/`, forms/chat and REST.
3. Сохраняем Bitrix SSR + текущие публичные URL.
4. Не меняем REST/form/upstream payload как часть чистой componentization.
5. Любой split должен иметь before/after smoke: SEO, browser console, CSS/JS, affected-page visual/action smoke.
6. Compatibility facades допустимы, если они уменьшают риск миграции.

## Phase 0 — Documentation Adoption

Goal: сделать новый technical debt layer видимым.

| Work | Covered IDs | Output |
|---|---|---|
| Link register/roadmap/backlog from workflow docs | all | `gap-analysis.md`, `current-state.md`, `README.md` references |
| Add issue planning rule | all | Future work references `BPC-*` IDs |
| Keep current guards green | all | `bitrix:check`, `product:challenge:check`, `seo:check` pass |

Exit criteria:

- New docs are discoverable.
- Open gaps are not hidden by older product challenge docs.

## Phase 1 — Architecture Guards And Budgets

Goal: stop further architectural drift before refactoring.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Extend Bitrix architecture check | `BPC-GUARD-001`, `BPC-GUARD-004` | Fast Fix | Architect + Frontend + Backend + QA | Done locally 05.06.2026: check covers product component boundary, REST index ownership markers, local component metadata, component cache/result policy and Definition of Componentized Done |
| Add file-size/responsibility budget | `BPC-GUARD-002` | Fast Fix | Architect + QA | Budget thresholds and allowlist for current known monoliths |
| Define component cache/result policy | `BPC-CMP-003` | Full Feature | Architect + Backend + Frontend | Done locally 05.06.2026: `component_cache_policy.json` covers all local components and `bitrix:check` enforces policy/evidence/cache markers |
| Define content wrapper contracts | `BPC-CMP-005`, `BPC-GUARD-003` | Full Feature | Frontend + QA | Done locally 05.06.2026: `component_wrapper_policy.json` covers `content.list`, `content.detail`, `faq.section`; `component:states:check` validates wrapper params, child params, cache delegation and FAQ missing state |

Do not start:

- broad product renderer rewrite;
- `/price/` JS split;
- REST helper split.

Exit criteria:

- New large files and new non-componentized pages can be blocked.
- Existing monoliths are tracked by explicit allowlist, not ignored.

## Phase 2 — Product Page Component Boundary

Goal: convert the current product renderer into a proper Bitrix local component without changing public behavior.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Create `tacticum:product.page` | `BPC-ARCH-001`, `BPC-CMP-002` | Full Feature | Architect + Frontend + Backend | Done locally 05.06.2026: component shell with params, two-phase prepare/render, product source/status, `tacticum:product.hero`, `tacticum:product.lead.cta` and `product_block_policy.json` for accepted nested-template blocks |
| Migrate product entry points | `BPC-CMP-001` | Full Feature | Frontend + QA | `/platform/`, `/agents/`, `/dev/`, `/forum/` call component only |
| Add product fixture/state smoke | `BPC-GUARD-003` | Full Feature | QA + Frontend | Done locally 05.06.2026: `component:states:check` validates product blocks/degraded state, price builder, forms, chat and FAQ/content wrapper fixtures and runs in PR/deploy lifecycle |

Do not start:

- product copy changes;
- Bitrix content schema changes;
- new product routes.

Verification:

```bash
npm run bitrix:check
npm run seo:check
npm run visual:smoke:css-local
npm run browser:console:css-local
```

Exit criteria:

- Rendered HTML is behaviorally equivalent.
- `data-product-source` and `data-product-block` contracts survive.
- Product pages are true component calls.

## Phase 3 — Public Page Entry Thinning

Goal: reduce thick public `index.php` files to orchestration-only entries.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Calculator page shell | `BPC-CMP-001` | Full Feature | Frontend + QA | Done locally 05.06.2026: `/calculator/` body moved to `tacticum:calculator.page`, public entry is 39 lines |
| Contacts page shell | `BPC-CMP-001` | Full Feature | Frontend + QA | Done locally 05.06.2026: `/contacts/` body moved to `tacticum:contacts.page`, public entry is 30 lines |
| Price page shell | `BPC-CMP-001`, `BPC-FE-001` | Full Feature | Frontend + QA | Shell done locally 05.06.2026: `/price/` body moved to `tacticum:price.page`, public entry is 39 lines; configurator split completed locally in Phase 4 |
| Services page shell | `BPC-CMP-001`, `BPC-CMP-005` | Full Feature | Frontend + QA | Done locally 05.06.2026: `/services/` body moved to `tacticum:services.page`, public entry is 39 lines |
| About page shell | `BPC-CMP-001`, `BPC-CMP-005` | Full Feature | Frontend + QA | Done locally 05.06.2026: `/about/` body moved to `tacticum:about.page`, public entry is 33 lines |
| Homepage section components | `BPC-CMP-001` | Full Feature | Frontend + Designer + QA | Done locally 05.06.2026: home hero/router/fit/next-step/calculator sections moved to `tacticum:home.page`, public entry is 25 lines |
| About/services/contacts components | `BPC-CMP-001`, `BPC-CMP-005` | Full Feature | Frontend + QA | Done locally for public entry shell; wrapper boundary/test coverage closed by `component_wrapper_policy.json` plus `component:states:check`, while cache policy is guarded by `component_cache_policy.json` |
| Lead CTA preparation cleanup | `BPC-CMP-004` | Full Feature | Frontend + Backend | Done locally 05.06.2026: `lead.cta/component.php` is an 18-line controller over `Tacticum\Component\LeadCtaParams`; API/form contract preserved |

Do not start:

- visual redesign;
- SEO copy changes unless separately scoped;
- form contract changes.

Verification:

```bash
npm run seo:check
npm run visual:smoke:css-local
npm run browser:console:css-local
```

Exit criteria:

- Public entries are mostly prolog, title/description, page assets and component params.
- H1/canonical/schema behavior unchanged.

## Phase 4 — `/price/` Configurator Decomposition

Goal: split the largest interaction surface safely.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Preserve DOM/data contract | `BPC-FE-001` | Full Feature | Frontend + QA | Done locally 05.06.2026: `data-price-*`, legacy selectors, `workers_json`, `team_preset`, `monthly_budget_estimate` and endpoint contract preserved |
| Split JS responsibilities | `BPC-FE-001`, `BPC-GUARD-003` | Full Feature | Frontend + QA | Done locally 05.06.2026: main `script.js` is a 78-line orchestrator; behavior lives in focused `price-configurator-*.js` chunks; component-owned chunks are registered through `$this->addExternalJs` after production smoke exposed that late `Asset::getInstance()->addJs` did not put them into the rendered Bitrix page bundle |
| Tighten component template | `BPC-CMP-001` | Full Feature | Frontend + Backend | Done locally 05.06.2026: `template.php` delegates to short `parts/catalog.php`, `parts/price-card.php`, `parts/order-modal.php` |

Do not start:

- staff upstream payload changes;
- new pricing business logic;
- CSS redesign beyond layout defects.

Verification:

```bash
npm run js:check
npm run bitrix:check
npm run component:states:check
npm run browser:smoke:price
npm run browser:console:css-local
```

Exit criteria:

- Multi-staff order, presets, monthly budget and fallback modal behavior still pass.
- `script.js` responsibility is reduced or split with explicit modules. Local static closure completed 05.06.2026; rendered deploy smoke remains required after release.

## Phase 5 — Backend Service Layer And REST Split

Goal: move large function-only subsystems toward maintainable service/repository boundaries.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Introduce service namespace and bootstrap footprint | `BPC-ARCH-002`, `BPC-ARCH-003` | Full Feature | Architect + Backend | Done locally 05.06.2026: `local/lib/Tacticum` autoload plus product content, product page text/CTA/data/schema, offer page query/request snapshot/resolver/response, offer catalog/cache, SEO JSON-LD/FAQ/meta, REST runtime and `calcrequests.*` classes added; `init.php` now loads `product_content_events.php` instead of eager product runtime/renderers/migrations, and guards enforce lazy product-content bootstrap |
| Product/offer/content repositories | `BPC-ARCH-005` | Full Feature | Backend + QA | Done locally 05.06.2026: product content, offer catalog and shared content/API reads live in repositories; public API, content wrappers and content result modifiers no longer own raw iblock reads |
| Offer request boundary | `BPC-ARCH-004` | Security / Integration | Backend + QA | Done locally 05.06.2026: `Tacticum\Offer\Page\RequestSnapshot` owns Bitrix `Context` request extraction; `Query`/`Resolver` no longer read superglobals directly and guards enforce it |
| Offer detail template split | `BPC-ARCH-002` | Full Feature | Frontend + Backend + QA | Done locally 05.06.2026: `news.detail/offer` template reduced to a 54-line orchestrator over 13-182-line parts; `offer-cta`, prefill, product relation and FAQ contracts preserved |
| Offer catalog template split | `BPC-CMP-003` | Full Feature | Frontend + Backend + QA | Done locally 05.06.2026: `offer.catalog` template reduced to a 52-line orchestrator over 19-79-line parts; cache/result policy is `delegated-service-cache` and guarded by `bitrix:check` |
| AIAgents template split | `BPC-CMP-003` | Full Feature | Frontend + QA | Done locally 05.06.2026: `aiagents` template reduced to a 21-line orchestrator over 15-83-line parts; form, Telegram resolver, content list and FAQ contracts preserved |
| Split REST helpers | `BPC-REST-001`, `BPC-REST-003` | Security / Integration | Backend + Security + QA | Done locally 05.06.2026: `rest_helpers.php` facade plus `Tacticum\Rest\*`, machine-readable endpoint policy, policy-driven `rest:endpoints:check`, and thin lead/staff endpoints over payload services |
| Clean REST index ownership | `BPC-REST-002` | Security / Integration | Backend + DevOps | Done locally 05.06.2026: kept as protected admin-only/non-indexable/private Bitrix REST hook route because `SEF_FOLDER=/local/rest/`; architecture and SEO guards enforce the markers |

Do not start:

- endpoint response shape changes;
- upstream URL/config changes;
- auth model changes outside Security / Integration lane.

Verification:

```bash
npm run bitrix:check
npm run rest:endpoints:check
npm run config:runtime:check
```

Exit criteria:

- Existing endpoints keep contracts.
- New backend code has service/repository home.
- `rest_helpers.php` is no longer the only place for unrelated REST concerns.
- New or changed REST endpoints must be classified in `local/rest/endpoint_policy.json`.

## Phase 6 — Forms, Chat And CSS Modularization

Goal: reduce frontend monoliths after the highest-risk `/price/` split.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Form adapter split | `BPC-FE-003` | Full Feature / Security | Frontend + Backend + QA | Implemented locally: `forms-runtime.js` owns shared helpers/analytics/returning-lead/UI error helpers; `forms.js` remains payload/submit orchestrator |
| Chat surface adapters | `BPC-FE-002` | Full Feature / Security | Frontend + Backend + QA | Implemented locally: shared runtime plus hero and calculator/light surface adapters; rendered smoke remains post-deploy gate |
| CSS sectioning | `BPC-FE-004` | Full Feature | Frontend + Designer + QA | Implemented locally: fixed ordered CSS split, template guard and local CSS smoke injection updated |

Do not start:

- AI chat contract changes;
- new analytics event params;
- broad restyle without design gate.

Verification:

```bash
npm run js:check
npm run css:check
npm run css:syntax
npm run template-styles:check
npm run browser:console:css-local
```

Exit criteria:

- Browser smoke has no console errors/warnings.
- Form/chat contracts remain stable.
- CSS split does not reintroduce deleted legacy assets.

## Phase 7 — Accepted Baselines And Monitoring

| Accepted Baseline | Related IDs | Revisit Trigger |
|---|---|---|
| Bitrix SSR remains the stack | product challenge `STACK-001` plus `BPC-*` componentization | Interactions become authenticated/application-grade |
| Legacy `CIBlockElement` is allowed in repositories/facades | `BPC-ARCH-005` | New shared code adds raw iblock calls outside service boundary |
| Compatibility facades can stay during migration | `BPC-ARCH-002`, `BPC-REST-001` | Facade becomes the only implementation and split stalls |
| Current public URL inventory remains unchanged | all sitemap URLs | SEO/canonical owner decision explicitly changes URLs |

## Suggested Work Package Order

1. `BPC-WP-01` — Architecture guardrails and file-size budgets.
2. `BPC-WP-02` — `tacticum:product.page` component boundary.
3. `BPC-WP-03` — Public page entry thinning.
4. `BPC-WP-04` — `/price/` configurator decomposition.
5. `BPC-WP-05` — Backend service layer and iblock repositories.
6. `BPC-WP-06` — REST helper split and REST namespace cleanup.
7. `BPC-WP-07` — Forms/chat frontend modularity.
8. `BPC-WP-08` — CSS/global style budget and component CSS policy.
9. `BPC-WP-09` — Component fixture/state smoke coverage.
