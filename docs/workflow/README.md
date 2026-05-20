# Workflow Доработки Приложения

Этот документ описывает общий процесс развития Bitrix-приложения `tacticum.ru`: от идеи или дефекта до deploy, smoke-check и обновления документации.

## Официальные Bitrix-Ориентиры

При проектировании и ревью использовать D7/API-подходы Bitrix:

- `\Bitrix\Main\Loader::includeModule()` вместо `CModule::IncludeModule()` в новом коде: https://dev.1c-bitrix.ru/api_d7/bitrix/main/loader/includemodule.php
- `\Bitrix\Main\EventManager` для событий: https://dev.1c-bitrix.ru/api_d7/bitrix/main/EventManager/index.php
- `\Bitrix\Main\Page\Asset` для подключения JS/CSS: https://dev.1c-bitrix.ru/api_d7/bitrix/main/page/asset/index.php
- `bitrix_sessid()` / `bitrix_sessid_post()` / `check_bitrix_sessid()` как базовая модель CSRF в Bitrix: https://dev.1c-bitrix.ru/api_help/main/functions/other/bitrix_sessid_post.php
- `\Bitrix\Main\Engine\ActionFilter\Csrf` как ориентир для D7 controllers/actions: https://dev.1c-bitrix.ru/api_d7/bitrix/main/engine/actionfilter/index.php
- `\Bitrix\Main\Data\Cache` и компонентное кеширование для данных/HTML: https://dev.1c-bitrix.ru/api_d7/bitrix/main/data/cache/index.php

## Lifecycle

```text
Idea / Bug / Incident
  ↓
Workflow lane selection
  ↓
Issue / Gap / Sprint scope
  ↓
Spec / ADR / Design, если gate сработал
  ↓
Codex Plan
  ↓
Implementation
  ↓
QA / Review
  ↓
Deploy
  ↓
Post-deploy smoke
  ↓
Gap/Sprint/Docs update
```

## Workflow Lanes

| Lane | Использовать когда | Обязательные артефакты |
|---|---|---|
| Full Feature Lane | новая фича, новый пользовательский сценарий, новый публичный URL, неясные требования | Issue, AC, Analyst spec при необходимости, Design/ADR по gates, Codex Plan, QA checklist |
| Fast Fix Lane | небольшой баг, текстовая правка, локальный CSS/JS/PHP фикс без нового контракта | Issue или краткое описание, короткий plan, QA smoke |
| Security / Integration Lane | REST/API, AI-сервис, PII, CSRF/CORS/rate limit, внешний сервис | Issue, Architect + QA early review, ADR если gate сработал, security checklist |
| Incident Lane | P0/P1 production defect | Incident Issue, reproduction, impact, fix, smoke, PM summary |

## Gates

### ADR Gate

ADR нужен, если меняется:

- API-контракт;
- хранение данных, инфоблок, свойства инфоблока;
- security-паттерн;
- AI-интеграция или внешний сервис;
- CI/CD, deploy, cache, rollback;
- общий паттерн, который будут повторять другие агенты.

ADR не нужен для локального багфикса, текста, CSS-правки существующего компонента, мелкого рефакторинга без нового решения.

### Design Gate

Designer нужен, если меняется новый UX/UI, навигация, форма, первый экран, визуальный паттерн, адаптивная логика.

Designer не блокирует Fast Fix Lane для точечных багов существующей вёрстки.

### QA Early Gate

QA подключается до разработки, если задача касается:

- CSRF/CORS/rate limit;
- PII/logging;
- AI payload/upstream response;
- REST/API contract;
- public form flow;
- production incident.

### Post-Deploy Gate

После deploy должен быть smoke-check по затронутым URL/API/формам. Issue закрывается только после подтверждения.

## Bitrix Development Rules

### Backend / PHP

- Новый код пишет D7 style там, где это совместимо: `Loader`, `EventManager`, `Asset`, `Context`.
- Legacy `CIBlockElement` допустим в существующих шаблонах и простых интеграциях, но новый shared-код должен изолировать обращения в helpers/services.
- Не добавлять бизнес-логику в `bitrix/`.
- Не держать большие inline-скрипты на страницах: переносить в `local/templates/tacticum/js/`.
- Не смешивать schema/config с публичными страницами: ID и base URL только через `tacticum_config.php` + helpers.

### REST/API

- POST endpoints живут в `local/rest/`, GET endpoints — в `local/api/`.
- `rest_helpers.php` — единственная точка для CORS/origin, rate limit, CSRF, curl defaults, masking, config access.
- `tacticum_form.php` — текущий эталон POST endpoint.
- Контракт лид-форм зафиксирован в `docs/workflow/lead-form-contract.md`; изменения payload, `form_id`, consent/CSRF или error model требуют обновления этого документа.
- `local/api/cases.php` — текущий эталон GET endpoint.
- Для новых API контрактов добавить section в Issue или отдельную spec в `docs/workflow/`.

### Frontend / Template

- Общий frontend-код — в `local/templates/tacticum/js/`.
- Общие стили — в `local/templates/tacticum/styles/` или компонентном `style.css`.
- Подключение через `\Bitrix\Main\Page\Asset::getInstance()->addJs/addCss/addString`.
- Формы должны использовать `data-tacticum-form`, `data-form-id`, `data-tacticum-consent`.
- Inline JS/CSS допустим только как legacy, при доработке выносить в asset-файлы.

### SEO

- Каждая публичная страница должна иметь уникальный `SetTitle`, `description`, один H1.
- Новые URL добавлять в `sitemap-files.xml`; `sitemap.xml` должен ссылаться на HTTPS sitemap.
- `robots.txt` должен ссылаться на HTTPS sitemap.

## Документы Workflow

- `current-state.md` — фактическая карта приложения на момент аудита.
- `gap-analysis.md` — продуктовые и технологические gaps.
- `codex-plan-template.md` — шаблон плана перед реализацией.
- `sprint-template.md` — шаблон спринта.
- `post-deploy-smoke.md` — чеклист smoke-check.
- `lead-form-contract.md` — контракт `/local/rest/tacticum_form.php` и taxonomy `form_id`.
- `chat-offer-contract.md` — контракт AI chat, `group_id`, prefill и handoff в lead form.
- `asset-layout-audit.md` — карта текущих CSS/JS assets, inline-долги и правила дальнейшей верстки.
- `sprints/` — snapshot-ы спринтов.

## Definition Of Ready

Задача готова к реализации, если:

- выбран lane;
- есть цель и acceptance criteria;
- указаны affected files/areas;
- gates проверены;
- для Security / Integration Lane есть QA + Architect early review;
- для Full Feature Lane есть spec/design/ADR там, где это нужно.

## Definition Of Done

Готово значит:

- код/документация обновлены;
- PR checklist пройден;
- QA или smoke-check выполнен;
- deploy выполнен, если нужен;
- post-deploy smoke-check выполнен;
- gap-analysis/sprint/ADR обновлены, если состояние изменилось.
