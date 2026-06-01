# 06. Roadmap And Workstreams

Дата: 01.06.2026

## Recommended Lane

Это `Full Feature Lane`: меняются позиционирование, навигация, публичные URL, UX/UI, формы, SEO и контент.

Security / Integration gate включается для:

- изменений lead form payload;
- новых REST endpoints;
- новых AI/chat сценариев;
- любых claim'ов про безопасность, ПДн, КИИ, on-prem;
- внешних сервисов, LLM-провайдеров, analytics.

Design gate обязателен: меняются первый экран, навигация, page templates и визуальный язык.

ADR gate вероятен, если будет принято решение о новых инфоблоках, content model, URL migration, form contract или shared component architecture.

## Workstreams

| Workstream | Owner | Main Output |
|---|---|---|
| Product Strategy | PM/Product Owner | Approved taxonomy, positioning, packaging |
| Evidence & Legal | PM + Legal + Security | Claims register, allowed public wording |
| Content | PM + Editor | Homepage and product page copy |
| UX/Design | Designer | IA, wireframes, token/component spec |
| Frontend/Bitrix | Dev | Components, templates, assets |
| Backend/Forms | Dev + QA | Product-aware lead contract |
| SEO/Analytics | SEO + PM | URL map, metadata, events |
| QA/Release | QA + DevOps | Smoke, browser checks, post-deploy gates |

## Phase 0 - Decision And Evidence Baseline

Goal: не дать редизайну стартовать на неподтвержденных утверждениях.

Outputs:

- approved product taxonomy;
- decision on `/agents/` vs `/aiagents/`;
- claim status for registry/security/customer logos/metrics;
- public/private split for Tacticum Dev workforce content;
- packaging assumptions: SaaS/on-prem/PAK/pilot;
- product-aware lead qualification fields draft.

Exit criteria:

- `07-risk-and-claims-register.md` заполнен владельцами/evidence;
- красные claim'ы удалены или переписаны;
- PM утвердил target positioning.

## Phase 1 - IA And Content Architecture

Goal: зафиксировать структуру сайта до дизайна.

Outputs:

- TO BE sitemap;
- header/footer navigation;
- homepage content model;
- product page template;
- briefs for Platform/Agents/Dev/Forum;
- SEO cluster draft;
- CTA taxonomy.

Exit criteria:

- согласован список новых/изменяемых URL;
- ясно, какие страницы остаются legacy;
- есть page-level acceptance criteria.

## Phase 2 - Design System And Prototypes

Goal: подготовить дизайн, который можно реализовать в Bitrix без хаотичных исключений.

Outputs:

- tokens;
- component library;
- desktop/mobile layouts;
- states for forms, nav, tabs, accordion, modal, chat;
- proof/claim components;
- migration map from AS IS to TO BE.

Exit criteria:

- дизайнер указал responsive behavior;
- интерактивные состояния описаны;
- разработка подтвердила feasibility;
- сохранение или изменение JS contracts зафиксировано.

## Phase 3 - Implementation Planning

Goal: разложить реализацию на безопасные инкременты.

Possible increments:

1. Добавить product navigation and shell components.
2. Обновить главную.
3. Создать Platform page.
4. Создать Agents page or migrate `/aiagents/`.
5. Создать Dev page.
6. Создать Forum page.
7. Обновить forms/analytics/SEO.
8. Расширить cases/proof.

Outputs:

- Codex plan;
- ADR if needed;
- updated lead form contract;
- component boundaries;
- QA smoke checklist.

## Phase 4 - Build And QA

Goal: реализовать без регресса текущих лидогенерационных сценариев.

Required checks:

- `npm run seo:check`;
- `npm run css:check`;
- `npm run template-styles:check`;
- `npm run bitrix:check`;
- browser/visual smoke for new pages;
- manual form success-flow on staging or controlled environment;
- analytics event check without PII;
- sitemap/robots/canonical check for new URLs.

PHP lint depends on local PHP availability; CI remains fallback when local PHP CLI is unavailable.

## Phase 5 - Post-Launch Iteration

Goal: превратить первый релиз в управляемую продуктовую систему.

Next possible iterations:

- product-specific calculators;
- industry pages;
- case library;
- technical architecture PDF downloads;
- partner/procurement package;
- product-specific demos;
- gated documentation requests;
- event tracking by product funnel.

## Recommended First Release Scope

Минимально ценный релиз:

- обновленная главная с ecosystem story;
- блок Platform + Agents + Dev + Forum;
- одна полноценная product page template;
- Platform page;
- Agents page или migration `/aiagents/`;
- product-aware CTA context;
- claims cleaned to safe public wording.

Не стоит в первый релиз включать:

- все возможные industry pages;
- сложный interactive calculator per product;
- публичные workforce transformation claims;
- неподтвержденные registry/trusted software claims;
- неподтвержденные customer logos/testimonials.

