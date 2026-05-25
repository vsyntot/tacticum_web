# Sprint 11 Hardening - Bitrix Component Framework

Дата: 24.05.2026 - 31.05.2026

## Sprint Goal

Довести компонентную архитектуру Sprint 11 до более строгих Bitrix Framework practices: компоненты должны быть оформлены для редактора, публичные страницы не должны держать длинные `news.list` конфиги и template globals, FAQ-секции должны задаваться устойчивыми ключами, а offer catalog helper должен получить service boundary.

## Workflow Lane

Основной lane: `Full Feature` + `Fast Fix`.

## In Scope

| ID | Gap | Lane | Owner | Priority | Status | Acceptance Criteria |
|---|---|---|---|---|---|---|
| S11H-001 | Missing `.parameters.php` у локальных компонентов | Fast Fix | Bitrix Dev | P1 | done | У всех `local/components/tacticum/*` есть `.parameters.php` с базовыми params для редактора |
| S11H-002 | Длинные public `bitrix:news.list` configs | Full Feature | Frontend + Bitrix Dev | P1 | done | Повторяемые content list вызовы идут через `tacticum:content.list`; public pages не копируют boilerplate `news.list` params |
| S11H-003 | Legacy `TACTICUM_PAGE_ASSETS` globals | Fast Fix | Frontend | P1 | done | Публичные страницы с page-specific assets используют split prolog + `SetPageProperty(...)`, а не template globals |
| S11H-004 | FAQ section numeric IDs в pages | Fast Fix | SEO + Bitrix Dev | P2 | done | Страницы передают semantic `SECTION_KEY`; `faq.section` code-first resolves section and fallback хранит централизованно |
| S11H-005 | Кириллическая опечатка в Bitrix param key | Fast Fix | QA | P1 | done | `INCLUDE_IBLOCK_INТО_CHAIN` устранён; guard ловит возврат |
| S11H-006 | Offer catalog procedural helper слишком широкий | Full Feature | Architect + Bitrix Dev | P2 | done | High-level `items`/`prepare` логика закрыта service class boundary; старые функции остаются compatibility wrappers |
| S11H-007 | Guards/docs | Full Feature | Architect + QA | P1 | done | `seo-check`, `current-state`, `gap-analysis`, sprint docs обновлены; проверки проходят |
| S11H-008 | Старые public entry points и hardcoded policy element | Fast Fix | Bitrix Dev + SEO | P1 | done | `/about/`, `/policies/` и `404.php` используют split prolog; `/policies/` вызывает `tacticum:content.detail` без hardcoded `ELEMENT_ID`; policy migration не привязана к `ID=515` |

## Out Of Scope

- Новый дизайн страниц.
- Изменение URL, sitemap или REST/API contracts.
- Переписывание template-specific `bitrix:news.list/*` шаблонов.
- Полный перевод offer helper в namespaced D7 service с автозагрузкой; это отдельный ADR/scope.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | no | ADR-008 уже фиксирует local component pattern |
| Design | no | Визуальный output не меняется |
| QA early | yes | Проверить form/chat/news-list contracts |
| SEO | yes | FAQ schema, canonical, sitemap checks не должны регрессировать |
| Post-deploy smoke | yes | Browser/visual/SEO smoke после deploy/cache clear |

## QA / Smoke Scope

| Scenario | Command | Expected |
|---|---|---|
| Static SEO/component guards | `npm run seo:check` | pass |
| CSS component scan | `npm run css:check` | pass |
| Template CSS guard | `npm run template-styles:check` | pass |
| Config | `npm run config:check` | pass |
| Local preflight | `npm run dev:preflight` | pass or documented PHP CLI degraded state |

## Sprint Review

### Done

- `tacticum:content.list` добавлен как wrapper над `bitrix:news.list` для повторяемого публичного контента.
- `tacticum:content.detail` добавлен как wrapper над `bitrix:news.detail` для статического detail-контента.
- Все локальные компоненты `local/components/tacticum/*` получили `.parameters.php`.
- Public pages с page-specific assets переведены на split prolog + page properties.
- `/about/`, `/policies/` и `404.php` переведены на split prolog, чтобы SEO/page properties задавались до visual header.
- `/policies/` больше не хардкодит `ELEMENT_ID=515` в page entry.
- `content_migrations.php` больше не хардкодит policy element `ID=515`; миграция ищет активные policy-элементы с legacy placeholders.
- FAQ-вызовы используют semantic `SECTION_KEY`; numeric fallback централизован внутри `tacticum:faq.section`.
- `offer_catalog.php` получил `TacticumOfferCatalogService` для high-level `items()` / `prepare()` и сохранил старые compatibility wrappers.
- `tools/seo-check.mjs`, ADR/current-state/gap-analysis обновлены под новый component hardening baseline.

### Not Done

- Нет code-level хвостов в рамках спринта.

### Follow-Up

- После deploy/cache clear: `npm run seo:check:prod`, `npm run visual:smoke:prod`, `npm run browser:smoke:prod`.
