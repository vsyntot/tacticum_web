# 02. AS IS / TO BE Gap Analysis

Дата: 01.06.2026

## Executive Summary

Текущий сайт уже стабилизирован как Bitrix-based lead generation platform с повторяемыми компонентами, формами, AI chat, calculator/offer flow и четырьмя коммерческими входами.

Целевое видение требует следующего уровня: сайт должен объяснять и продавать продуктовую AI-экосистему Tacticum. Главный gap не технический, а продуктово-информационный: текущая структура сайта не показывает Platform/Agents/Dev/Forum как связанную линейку программных продуктов.

## Базовая Точка AS IS

По `docs/workflow/current-state.md` и `docs/design-system-handoff/`:

- сайт работает на PHP 8.4 + 1C-Bitrix;
- активный шаблон: `local/templates/tacticum`;
- основные публичные страницы: `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`;
- текущая коммерческая архитектура: рассчитать проект, внедрить AI, собрать команду, запустить AI-бота;
- повторяемые CTA, FAQ, chat и content list вынесены в локальные компоненты;
- формы унифицированы через `data-tacticum-form`;
- JS интерактив работает через vanilla JS и DOM/data contracts;
- ручные стили живут в `local/templates/tacticum/styles/global.css`;
- формальной TO BE дизайн-системы пока нет.

## Целевая Точка TO BE

TO BE сайт должен:

- позиционировать Tacticum как AI-software vendor;
- иметь отдельный слой продуктовой навигации;
- объяснять Platform как ядро экосистемы;
- давать отдельные страницы Platform, Agents, Dev, Forum;
- сохранять сервисные входы как путь к пилоту/внедрению;
- поддерживать enterprise proof: архитектура, безопасность, deployment, регуляторика, кейсы;
- иметь контролируемый claim governance;
- использовать обновленную дизайн-систему и совместимые JS/form contracts.

## Gap Table

| ID | Area | AS IS | TO BE | Gap | Priority | Lane | Next Step |
|---|---|---|---|---|---|---|---|
| PV-001 | Positioning | AI/IT внедрение и лидогенерация | Российская экосистема AI-программ | Нет единого продуктового тезиса на уровне сайта | P1 | Full Feature | Утвердить `01-target-product-vision.md` |
| PV-002 | Product taxonomy | Услуги, price, calculator, aiagents | Platform + Agents + Dev + Forum + services as delivery | Нужно развести продукты и сервисные входы | P1 | Full Feature | Утвердить taxonomy и URL map |
| PV-003 | Homepage | Router коммерческих входов | Ecosystem overview + qualified entry points | Главная не объясняет платформенное ядро | P1 | Full Feature | Новый homepage brief и wireframe |
| PV-004 | Product pages | Есть `/aiagents/`, нет Platform/Dev/Forum как продуктов | 4 продуктовые страницы | Не хватает страниц и шаблонов one-pager | P1 | Full Feature | Создать page briefs из `04-product-page-briefs.md` |
| PV-005 | Platform proof | Платформа не является публично объясненной сущностью | Platform как ядро и продукт | Нет визуальной architecture story | P1 | Full Feature | Схема ecosystem/platform modules |
| PV-006 | Regulatory claims | Есть отдельные упоминания безопасности/152-ФЗ | Reg-ready narrative | Claim'ы требуют проверки и юридической редакции | P0 | Security / Integration | Закрыть `07-risk-and-claims-register.md` |
| PV-007 | Case proof | Кейсы и метрики есть, часть claim'ов спорная | Product-specific proof | Нужно связать proof с каждым продуктом | P1 | Full Feature | Proof matrix: product -> metric -> evidence |
| PV-008 | Content model | Инфоблоки services/cases/faq/rates/aiagents | Product pages + product facts + claims | Возможно нужны новые инфоблоки или статические компоненты | P2 | Full Feature | Решить Bitrix content model |
| PV-009 | Navigation | Header под текущие money pages | Product-first navigation | Нужна новая навигационная иерархия | P1 | Full Feature | IA из `03-information-architecture-to-be.md` |
| PV-010 | Design system | AS IS Tailwind/global CSS, no formal library | Enterprise product design system | Нет компонентов для сложной product storytelling | P1 | Full Feature | TO BE design library brief |
| PV-011 | Interaction | Forms, FAQ, chat, menu stable | Product tabs, diagrams, comparison, CTA qualification | Нужно расширить интерактив без ломки contracts | P2 | Full Feature | Interaction inventory and contracts |
| PV-012 | Lead qualification | Формы собирают базовый контекст | Product-aware qualification | Заявка должна передавать продукт, контур, формат, зрелость | P1 | Security / Integration | Обновить lead form contract после spec |
| PV-013 | SEO | Money pages и offer sitemap стабилизированы | Product cluster SEO | Новые URL и intent clusters не описаны | P2 | Full Feature | Product SEO map |
| PV-014 | Analytics | Events форм/чата без PII | Product funnel analytics | Нужно мерить product page -> CTA -> lead | P2 | Full Feature | Analytics taxonomy update |
| PV-015 | Dev implementation | Компонентная Bitrix база готова | Новые templates/components | Нужно решить component boundaries | P2 | Full Feature | Implementation plan and ADR gate |
| PV-016 | Sales materials | Docs/decks отдельно от сайта | Site/deck/message consistency | Нужен единый source of truth по claims | P1 | Full Feature | Claims register + proof library |
| PV-017 | Tacticum Dev tone | В исходнике сильные workforce тезисы | Публично безопасный enterprise governance tone | Риск репутационного и HR-негатива | P0 | Full Feature | Переписать public framing |
| PV-018 | External references | Упоминания Gartner/McKinsey/WEF, competitors | Аккуратные references или без них | Нужны источники/разрешения/формулировки | P1 | Full Feature | Evidence review |
| PV-019 | Client logos/testimonials | В `index.html` есть логотипы и цитаты | Только подтвержденные клиенты/отзывы | Нельзя публиковать без evidence | P0 | Full Feature | Customer proof approval |
| PV-020 | Delivery model | Услуги понятны, продукты нет | SaaS/on-prem/PAK/pilot/support | Нужно описать packaging и ограничения | P1 | Full Feature | Packaging matrix |

## Критические Разрывы

### 1. Product positioning gap

Текущий сайт отвечает на вопрос “что можно заказать”. TO BE должен отвечать на вопрос “что такое Tacticum как продуктовая экосистема”.

### 2. IA gap

Текущие URL могут остаться, но им нужна новая роль. `/services/`, `/price/`, `/calculator/`, `/offer/` не должны конкурировать с Platform/Agents/Dev/Forum. Они должны стать delivery/entry слой.

### 3. Proof gap

Новые материалы содержат сильные claim'ы. Без доказательной матрицы сайт рискует стать недостоверным. Нужен отдельный proof workflow до дизайна и разработки.

### 4. Design storytelling gap

AS IS дизайн-система покрывает формы, CTA, cards, chat, FAQ и страницы услуг. Но TO BE нужны сложные enterprise-паттерны: архитектурные схемы, module matrices, comparison tables, deployment diagrams, claim badges, product one-pagers.

### 5. Lead qualification gap

Если появляются 4 продукта, форма заявки должна понимать, по какому продукту пришел лид и какой формат интересует: discovery, pilot, SaaS, on-prem, ПАК, team augmentation, integration.

## Recommended Gap Closure Order

1. Утвердить целевую taxonomy: Platform / Agents / Dev / Forum / Services.
2. Закрыть risky claims и legal/regulatory evidence.
3. Утвердить IA и URL strategy.
4. Подготовить product page briefs.
5. Сформировать TO BE дизайн-систему и component map.
6. Спроектировать lead qualification и analytics taxonomy.
7. Реализовать сначала homepage + product shell pages.
8. Затем расширять proof, cases, calculators, industry pages.

