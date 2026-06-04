# Product Taxonomy, SEO And Packaging Decision Pack

Дата: 04.06.2026
Статус: draft pending PM/Sales/SEO/Legal approval
Sprint: `docs/workflow/sprints/2026-06-04-sprint-18-taxonomy-seo-packaging.md`

## Purpose

Этот документ фиксирует Sprint 18 baseline для product taxonomy, `/agents/` vs `/aiagents/`, `/price/` route intent, packaging language, product SEO metadata and product evidence mapping.

Документ не меняет runtime, canonical, redirects, sitemap или публичную copy. Он задает approval package: что можно принять как текущую v1 рекомендацию, что остается заблокированным owner evidence and что нельзя внедрять до approval.

## Covered Gaps

| Gap | Sprint Item | Current Output | Remaining Gate |
|---|---|---|---|
| `CONTENT-005` | S18-001 | Product taxonomy recommendation | PM + Sales approval |
| `UX-004` | S18-002 | `/agents/` vs `/aiagents/` boundary recommendation | PM + SEO approval |
| `ARCH-010` | S18-003 | SEO/canonical decision draft | SEO keyword/intent validation |
| `UX-005` | S18-004 | `/price/` route intent and packaging framing | PM + Sales + UX approval |
| `CONTENT-001` | S18-005 | Packaging safety matrix | PM + Sales + Legal approval |
| `CONTENT-003` | S18-006 | Product evidence map draft | Content + Sales + SEO review |
| `CONTENT-004` | S18-007 | Product metadata sheet draft | SEO + Content + PM approval |

## Factual Baseline

| URL | Current Role | Current Canonical | Current Runtime State |
|---|---|---|---|
| `/platform/` | Product page for shared enterprise AI platform layer | `/platform/` | Renders through product renderer and product content source |
| `/agents/` | Product page for corporate AI assistants | `/agents/` | Renders through product renderer; secondary CTA points to `/aiagents/` |
| `/dev/` | Product page for AI-assisted engineering governance | `/dev/` | Renders through product renderer |
| `/forum/` | Product page for managed customer dialog scenarios | `/forum/` | Renders through product renderer |
| `/aiagents/` | Existing compatibility/money/service URL for AI bots and Telegram agents | `/aiagents/` | Renders via `tacticum:aiagents`; not redirected |
| `/price/` | Team/staffing route with product workstream context | `/price/` | Preserves team-builder, rates, staff order and chat surface |

## Recommended Taxonomy V1

| Product | One-Liner | Buyer Trigger | Boundary |
|---|---|---|---|
| Tacticum Platform | Единое runtime/data/access ядро для нескольких корпоративных AI-продуктов | Есть портфель AI-сценариев, нужны RAG, LLM gateway, доступы, аудит and observability | Не продается как быстрый single-bot demo; не заменяет delivery/team route |
| Tacticum Agents | Корпоративные AI-ассистенты для внутренних функций и знаний поверх Platform | Нужно запустить управляемого ассистента для HR, legal, finance, IT/helpdesk or knowledge base | Не является generic chatbot-конструктором; `/aiagents/` остается demo/service bridge |
| Tacticum Dev | Governance layer для AI-assisted разработки без потери архитектуры, design rules and quality gates | Команда уже использует AI coding tools или готовится к controlled rollout | Не является staff augmentation page; delivery-команда выбирается через `/price/` or `/services/` |
| Tacticum Forum | Управляемые клиентские диалоги: сценарные графы плюс LLM enrichment and funnel analytics | Есть поток обращений, каналы, escalation, журнал и метрики коммуникаций | Не является внутренним ассистентом and not a pure free-form LLM bot |

Decision recommendation: keep product names `Platform`, `Agents`, `Dev`, `Forum` for v1. They are already represented in current public URLs, product seed/fallback data, navigation and rendered product schema.

Blocked until approval: renaming products, collapsing products, introducing product licensing names, or adding enterprise proof claims beyond `proof-claims-matrix.md`.

## `/agents/` vs `/aiagents/` Decision Draft

Recommended v1 decision:

| Topic | Recommendation | Rationale | Approval Needed |
|---|---|---|---|
| Primary product URL | Keep `/agents/` as canonical product page for Tacticum Agents | It matches product-first IA and renders structured product page schema | PM + SEO |
| Compatibility URL | Keep `/aiagents/` live with self-canonical for now | It is an existing money/service route and current content is still narrower: AI bots / Telegram agents | SEO + PM |
| Redirect | Do not redirect `/aiagents/` to `/agents/` in v1 | Redirect could break existing traffic intent and lead attribution before SEO intent validation | SEO |
| Sitemap | Keep both URLs discoverable until SEO approval says otherwise | Their intent differs today: product page vs service/demo route | SEO |
| Copy boundary | `/agents/` = corporate assistants product; `/aiagents/` = bot/demo/service entry and bridge to Agents | Prevents duplicate product promise while preserving compatibility | PM + Content |
| Analytics | Track as separate entries until redirect/canonical decision is approved | Needed to compare current money URL value vs product path | Analytics + PM |

Do not implement before approval:

- 301/302 redirect between `/aiagents/` and `/agents/`;
- canonical override from `/aiagents/` to `/agents/`;
- removing `/aiagents/` from sitemap/navigation;
- changing `/aiagents/` form or lead context in a way that breaks existing service route analytics.

## `/price/` Route Intent

Recommended v1 route intent:

`/price/` is not a product licensing/pricing page. It is the staffing and team composition route for product implementation, assessment, pilot and delivery stages.

Current preserved behavior:

- rates list stays tied to `rates` iblock;
- team-builder, role levels, monthly estimate, presets and staff-order payload stay unchanged;
- `price-cta` keeps `lead_product=ecosystem` and `lead_scenario=product-team`;
- `/price/` can link to Platform / Agents / Dev / Forum workstreams but must not claim fixed product license prices.

Approved public framing candidates:

| Phrase | Status | Notes |
|---|---|---|
| `Команда под продуктовый пилот или delivery-этап` | allowed draft | Already aligned with current page copy |
| `Роли и загрузка под Platform, Agents, Dev или Forum` | allowed draft | Describes team composition, not license pricing |
| `Ориентир бюджета команды` | allowed draft | Must remain estimate/discovery wording |
| `Стоимость лицензии Platform/Agents/Dev/Forum` | blocked | No approved licensing model |
| `SLA`, `поддержка 24/7`, fixed support package | blocked | Requires Legal/Sales source and support contract model |

## Packaging Safety Matrix

| Packaging Topic | Public Wording Status | Allowed Public Copy | Private/NDA Only | Blocked Until Evidence |
|---|---|---|---|---|
| Pilot / assessment | allowed draft | `пилот`, `assessment`, `проверочный контур`, `что подтверждаем на пилоте` | Detailed customer-specific pilot results | Guaranteed outcome, guaranteed timing |
| Implementation team | allowed draft | `роли`, `загрузка`, `управляемая команда`, `delivery-этап` | Named specialist availability, internal staffing limits | Fixed number of specialists without source |
| SaaS | blocked | None for public product pages | Architecture discussion if owner confirms future model | `SaaS-платформа доступна`, public pricing, subscription terms |
| On-prem | conditional private | `обсуждаем контур размещения` only if scoped as discovery | Deployment architecture, infra sizing, security docs | Public claim that on-prem is available by default |
| Hybrid | conditional private | `гибридный контур требует архитектурного согласования` | Target architecture and integration details | Public promise of ready hybrid delivery |
| PAK / appliance | blocked | None | Internal feasibility notes only | Any public PAK/readiness claim |
| Support | conditional private | `обсуждаем эксплуатацию и ownership` | Support model, SLA terms, escalation docs | `24/7`, SLA %, guaranteed response time |
| Procurement/security docs | conditional private | `можно запросить архитектурную сессию / checklist` | Data flow, access model, logs, vendor questionnaire | Certifications, registry status, compliance claims without source |

Packaging rule: public copy may describe decision process and pilot artifacts, not unapproved commercial availability, certifications, SLA, deployment promises or customer outcomes.

## Product Evidence Map Draft

| Product | Current Evidence Type | Public Evidence Status | Needed Next Evidence |
|---|---|---|---|
| Platform | Product page architecture/procurement/proof-readiness blocks; product use cases | Safe readiness wording only | Case/offer/FAQ/service tags by product, architecture owner review |
| Agents | Product page use cases; bridge to `/aiagents/`; AI bot service content | Safe readiness wording only | Decide which `/aiagents/` content supports Agents proof and which remains service/demo |
| Dev | Product page use cases around engineering workflow and quality gates | Safe readiness wording only | Link to delivery examples, design-system compliance evidence and internal workflow owner |
| Forum | Product page use cases around contact-center/dialog flows | Safe readiness wording only | Link to cases/offers/FAQ where customer communication flow is present |
| Ecosystem / team route | `/price/`, `/services/`, `/offer/`, `/calculator/` | Safe delivery/team language | Product tags for offers and service sections; Sales approval for packaging language |

Evidence status taxonomy:

| Status | Meaning | Public Runtime Rule |
|---|---|---|
| `public-safe` | Approved source and wording exist | Can be published with source discipline |
| `private-evidence` | Evidence exists but is NDA/internal | Public copy can say `обсудим на встрече`, not details |
| `pilot-artifact` | We can describe what pilot checks | Allowed as process/readiness wording |
| `pending` | Owner/source not approved yet | Do not publish as claim |
| `blocked` | Legal/Sales/SEO/security does not approve | Do not publish |

Current Sprint 18 baseline: product pages should stay in `pilot-artifact` wording unless a separate owner-approved proof record exists.

## Product Metadata Sheet Draft

| URL | Product/Route | Current Title | Current Description | Current H1 / Page Title Source | Canonical Decision |
|---|---|---|---|---|---|
| `/platform/` | Tacticum Platform | `Tacticum Platform - платформа для корпоративных AI-продуктов` | `Tacticum Platform - единое инфраструктурное ядро для корпоративных AI-продуктов: LLM-шлюз, RAG, память, MCP-инструменты, права доступа, аудит и контроль стоимости.` | Product data: `Единое ядро для корпоративных AI-продуктов` | Keep `/platform/`; approved keywords pending |
| `/agents/` | Tacticum Agents | `Tacticum Agents - корпоративные AI-ассистенты для бизнес-функций` | `Tacticum Agents помогает запускать корпоративных AI-ассистентов для HR, юридического, бухгалтерского, клиентского и внутреннего IT-контуров поверх общей AI-платформы.` | Product data: `Корпоративные AI-ассистенты для бизнес-функций` | Keep `/agents/`; `/aiagents/` decision pending |
| `/dev/` | Tacticum Dev | `Tacticum Dev - управление AI-assisted разработкой` | `Tacticum Dev помогает инженерным организациям управлять AI-assisted разработкой: профили, knowledge layer, design token compliance, quality gates и traceability.` | Product data: `AI-assisted разработка без потери архитектуры и качества` | Keep `/dev/`; approved keywords pending |
| `/forum/` | Tacticum Forum | `Tacticum Forum - сценарии и LLM для клиентских коммуникаций` | `Tacticum Forum - диалоговая платформа для клиентских коммуникаций: сценарные графы, LLM-обогащение, аналитика воронок, A/B-проверки и журнал диалогов.` | Product data: `Сценарии и LLM для управляемых клиентских коммуникаций` | Keep `/forum/`; approved keywords pending |
| `/aiagents/` | AI bots / Telegram agents service route | `AI-боты и Telegram-агенты для B2B-сценариев - Тактикум` | `Tacticum помогает быстро проверить и запустить AI-бота в Telegram для продаж, консультаций и лидогенерации: демо, прототип и внедрение.` | Component content | Keep self-canonical until SEO decision |
| `/price/` | Team/staffing route for product implementation | `Команда под AI- и IT-задачу: роли, ставки и быстрый старт - Тактикум` | `Соберите управляемую AI- или IT-команду под задачу: роли, уровни специалистов, ставки, пресеты команды и заявка на старт работ.` | Page H1: `Соберите AI- и IT-команду под вашу задачу` | Keep `/price/`; not a product-license pricing page |

Metadata rule: current metadata is acceptable as a technical baseline, but not final owner-approved SEO copy. Any change to title/description/H1/canonical requires SEO smoke and update of this sheet.

## Implementation Gates

Do not implement route/meta/runtime changes until:

1. PM/Sales approve product taxonomy and buying triggers.
2. SEO approves `/agents/` vs `/aiagents/` canonical/sitemap treatment.
3. Legal/Sales approve packaging matrix public wording.
4. Content/SEO approve product evidence map and metadata sheet.
5. QA confirms smoke scope for changed URLs.

If approved implementation changes public route/meta behavior, run:

```bash
npm run seo:check
npm run bitrix:check
npm run product:gaps:check
```

After deploy/cache refresh:

```bash
npm run seo:check:prod
npm run product:source:http:prod
npm run release:public-precheck:prod
```

## Approval Checklist

| Role | Must Approve | Evidence Format |
|---|---|---|
| PM | Product names, one-liners, route intent, blocked claims | Comment or sign-off referencing this document |
| Sales | Buyer triggers, packaging reality, public/private boundaries | Safe summary; no raw CRM/customer data |
| SEO | Keyword intent, canonical, sitemap, metadata | Decision note and smoke commands |
| Legal | Packaging and support/SLA/deployment wording | Approved/blocked wording list |
| Content | Metadata and public copy changes | Updated copy sheet |
| QA | Smoke scope and no-regression checklist | Command output paths or safe summary |

## Open Decisions

| Decision | Status | Owner |
|---|---|---|
| Keep `/aiagents/` self-canonical indefinitely or define sunset period | pending | PM + SEO |
| Add product tags to cases/offers/FAQ/services for evidence mapping | pending | Content + SEO + Sales |
| Publicly mention SaaS/on-prem/hybrid/PAK/support packaging | blocked | PM + Sales + Legal |
| Change product titles/descriptions after keyword research | pending | SEO + Content + PM |
| Reframe `/price/` visually beyond current product workstreams block | pending | UX + Designer + PM |
