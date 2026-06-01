# 13. Architecture / Components / Stack Target

Дата: 01.06.2026

Статус: целевой технологический документ для перехода от product-first MVP к устойчивой TO BE реализации.

## Current Technical Baseline

AS IS сильная база:

- PHP 8.4 + 1C-Bitrix;
- кастомный код изолирован в `local/`;
- публичные страницы стали тоньше за счет локальных компонентов;
- формы, chat, FAQ, offer, price имеют явные contracts;
- CSS/JS подключаются через Bitrix `Asset`;
- static Tailwind bundle заменил runtime Tailwind;
- `styles/global.css` является единственным manual runtime CSS;
- CI/deploy guards покрывают SEO, CSS, JS, Bitrix architecture, known gaps.

Product-first MVP:

- `/platform/`, `/agents/`, `/dev/`, `/forum/` добавлены как static public pages;
- product pages используют общий renderer bootstrap `local/php_interface/include/product_page.php`;
- reusable visual blocks live in `local/php_interface/include/product_page_blocks/*.php`;
- visual blocks expose stable `data-product-block` markers for design/QA/refactor targeting;
- core page data вынесена в `local/php_interface/include/product_data/*.php`;
- shared product data feeds HTML and JSON-LD schema;
- CTA context передается через allowlisted `lead_*`;
- product schema/rendered SEO smoke добавлен;
- external release gates остаются pending.

## Target Architecture Principle

```text
public page entry
  -> product data source
  -> SEO/schema helpers
  -> product page renderer
  -> reusable local components
  -> existing Bitrix template shell
  -> existing JS/form contracts
```

Page entry должен оставаться orchestration layer, а не местом, где копится вся визуальная и бизнес-логика.

## Product Content Model Decision

Сейчас core product content lives in Git-reviewed PHP data files under `local/php_interface/include/product_data/`. Это уже лучше page-local arrays: публичные `index.php` стали orchestration files, а один data source питает HTML, FAQ schema, CTA context and proof/readiness blocks.

Открытый вопрос теперь уже не "page arrays или нет", а "достаточно ли Git-owned data layer или нужен Bitrix/hybrid content workflow для proof, cases, FAQ and content operations".

### Options

| Option | Description | Pros | Cons | Recommended Use |
|---|---|---|---|---|
| Static PHP arrays | Product data remains in page files | Fast, safe, no Bitrix admin dependency | Hard to edit, no structured ownership | Retired for product pages |
| Shared PHP data files | Product data moved to `local/php_interface/include/product_data/*.php` | Keeps Git review and structured reuse | Still developer-owned content | Current baseline |
| Bitrix iblock products | Products/use cases/proof as content entities | Content/admin workflow | Needs new content model, ADR, admin QA | Later if content team needs CMS |
| Hybrid | Core product facts in Git, cases/proof in iblocks | Balance of control and content ops | More integration complexity | Likely TO BE target |

### Recommendation

Next step should be hybrid-safe:

1. Keep core product facts in Git-reviewed structured arrays.
2. Keep arrays out of page entries in named product data files.
3. Keep proof/cases in existing iblocks until a product proof model is approved.
4. Add Bitrix content model only after ADR and content workflow decision.

## Product Component Boundary

Current `product_page.php` should not keep growing indefinitely. First split is already applied: bootstrap/helpers/data/schema stay in `product_page.php`, while visual render functions live in `product_page_blocks/*.php`.

### Target Split

| Layer | Responsibility |
|---|---|
| `product_page.php` | Bootstrap product helpers, data loader, schema helpers and block includes |
| `product_page_blocks/page.php` | Orchestrate product page block order and CTA component call |
| `product.hero` or partial | Hero, CTA buttons, badges |
| `product.fit-guide` | Product fit / not fit / start here |
| `product.use-cases` | Use-case cards |
| `product.architecture-map` | Layers, modules, data flow |
| `product.deployment` | Deployment/security/procurement status |
| `product.comparison` | Category comparison |
| `product.proof` | Proof readiness/evidence cards |
| `product.rollout` | Rollout timeline |
| `tacticum:lead.cta` | Conversion form and product context |

Keep `tacticum:lead.cta` as the conversion component; do not duplicate form markup in product components.

First locator contract is implemented at HTML level: `data-product-block` marks `hero`, `fit-guide`, `content-section`, `architecture`, `use-cases`, `comparison`, `procurement`, `rollout`, `proof`, `faq` and `lead-cta`. `visual-smoke` records this inventory as `productBlocks` / `productBlockErrors` in rendered manifests, release sign-off validation now fails product SEO evidence if required product blocks are absent, and `npm run product:block-previews` captures per-block PNG screenshots for AS IS design review. This is intentionally lighter than Storybook: it protects AS IS block identity and gives QA/design stable selectors and screenshots while the team decides whether product blocks should later become local Bitrix components or component-level previews.

## Form / CRM Architecture Target

Current behavior:

- frontend submits ordinary fields and optional `lead_*`;
- backend normalizes `lead_*` into canonical lead qualification profile;
- backend appends the profile into upstream `task` as approved text fallback;
- response shape and upstream endpoint remain unchanged.

This is safe for MVP, but not enough for mature product funnel.

### Target Decisions

| Decision | Why |
|---|---|
| Define canonical `product_interest` / `use_case_interest` / `deployment_interest` fields | Sales and CRM need structured segmentation |
| Decide whether upstream accepts structured fields | Avoid hiding key data in text blob |
| Update `lead-form-contract.md` before changing payload | Security/QA gate |
| Keep analytics no-PII | Current safe model must remain |
| Add product funnel event taxonomy | Measure product page conversion |

### Migration Rule

Do not remove current text-context fallback until CRM/upstream structured fields are confirmed in staging/production.

## Analytics Target

Current analytics is safe but shallow for product funnel.

First implementation slice is now available in `analytics.js` and `forms.js`: page/router view, product CTA click and product form submit/success/error are emitted with allowlisted `product`, `page_role` and controlled `scenario` only. Метрика goal configuration and release evidence remain external gates.

Target events:

| Event | Params | PII Rule |
|---|---|---|
| `tacticum_product_view` | product, page_role | no PII |
| `tacticum_product_cta_click` | product, page_role, cta | no PII |
| `tacticum_product_form_submit` | product, page_role, scenario, form_id, endpoint | no PII |
| `tacticum_product_form_success` | product, page_role, scenario, form_id, endpoint, status | no PII |
| `tacticum_product_form_error` | product, page_role, scenario, form_id, endpoint, status, code | no PII |
| `tacticum_product_fit_select` | product/use_case controlled slug | no PII |
| `tacticum_security_doc_request` | product, doc_type controlled slug | no PII |

No raw message, company, email, phone, document names, URLs with sensitive query values.

## Stack Target

### Keep

- Bitrix public pages and local components;
- PHP server-rendered pages for SEO and reliability;
- vanilla JS for simple interactions;
- static Tailwind generation;
- existing smoke tooling and release sign-off discipline;
- `Bitrix\Main\Page\Asset` asset loading.

### Improve

| Area | Target |
|---|---|
| Tokens | Documented token source and mapping to Tailwind/global CSS |
| Component previews | Lightweight local HTML/PHP preview or documented screenshot harness |
| CSS structure | Split `global.css` by documented sections or component CSS when safe |
| JS modules | Keep explicit page asset flags; add new JS only for real interaction |
| Product data | Keep structured Git data source; decide Bitrix/hybrid model only for content ops need |
| Schema | Keep data -> schema -> render consistency |
| Release | Close external gates with evidence, not only local checks |

### Avoid

- introducing a frontend framework only for marketing pages;
- adding inline JS/CSS to product pages;
- hardcoding iblock IDs;
- duplicating form logic outside `forms.js`;
- turning `/price/` into product pricing without commercial decision;
- adding commercial JSON-LD offers/prices before approval;
- hiding product qualification only in free text.

## Architecture Gap Table

| ID | Gap | Current | Target | Gate |
|---|---|---|---|---|
| ARCH-001 | Product content ownership | Shared PHP data files | Decide whether Git layer is enough or CMS/hybrid is needed | ADR likely if CMS-backed |
| ARCH-002 | Product components | PHP block partials | Decide if partials are enough or if local components/previews are needed | Design + Architect |
| ARCH-003 | Lead qualification | Canonical profile + `task` fallback | Structured upstream/CRM fields after approval | Security / Integration |
| ARCH-004 | Analytics | Product funnel code events | Метрика goals/evidence and future interaction events | QA + Marketing |
| ARCH-005 | Proof model | Docs/register plus static proof readiness | Evidence-backed proof components | Legal/Sales/PM |
| ARCH-006 | Design tokens | Minimal Tailwind tokens | Formal token pipeline | Design + Frontend |
| ARCH-007 | CSS organization | Large `global.css` | Layered/component CSS strategy | Frontend |
| ARCH-008 | Component preview | Lightweight rendered screenshot workflow exists through `product:block-previews`; Storybook/local component previews absent | Decide later only if design/QA needs component-level isolated previews | Frontend/QA |
| ARCH-009 | CSP | Report-only | Enforce after baseline and vendor review | Security / Integration |
| ARCH-010 | Release evidence | Local gates strong, external pending | Strict sign-off evidence closure | QA/DevOps |

## ADR Candidates

ADR is needed if any of these decisions are made:

- new product/content iblock model;
- structured lead payload change;
- product analytics event taxonomy with new data governance rules;
- component architecture split for product pages as shared pattern;
- CSP enforce rollout;
- gated documentation/download flow;
- product pricing/licensing publication model.

## Acceptance Criteria For TO BE Tech Foundation

- Product page data is structured and reused for HTML, SEO and schema.
- Public page entries remain thin orchestration files.
- New product components preserve existing form/FAQ/menu contracts or document migrations.
- Product qualification can be consumed by sales/CRM in structured form or has approved canonical fallback.
- Product analytics can be measured without PII.
- Design token decisions are implementable in Tailwind/global CSS.
- Release gates distinguish local code readiness from external production evidence.
