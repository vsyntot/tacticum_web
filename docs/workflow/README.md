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
- Новые статические URL должны попадать в Bitrix-generated `sitemap-basic-files.xml` через штатную настройку sitemap; repo-owned `sitemap.xml` должен ссылаться на `sitemap-basic-files.xml` и отдельные dynamic sitemap, например `/offer/sitemap.php`.
- Generated sitemap artifacts (`sitemap-basic.xml`, `sitemap-basic-files.xml`, `sitemap-basic-iblock-*.xml`, legacy `sitemap-files.xml`) не коммитить; root `sitemap.xml` и `robots.txt` остаются в Git.
- `robots.txt` должен ссылаться на HTTPS sitemap.
- После изменения публичных URL, canonical, sitemap или robots запускать `npm run seo:check`; после deploy запускать `npm run seo:check:prod`.

## Документы Workflow

- `current-state.md` — фактическая карта приложения на момент аудита.
- `gap-analysis.md` — продуктовые и технологические gaps.
- `codex-plan-template.md` — шаблон плана перед реализацией.
- `sprint-template.md` — шаблон спринта.
- `post-deploy-smoke.md` — чеклист smoke-check.
- `lead-form-contract.md` — контракт `/local/rest/tacticum_form.php` и taxonomy `form_id`.
- `chat-offer-contract.md` — контракт AI chat, `group_id`, prefill и handoff в lead form.
- `chat-api-contract.md` — низкоуровневый contract `/local/rest/tacticum_chat.php`.
- `asset-layout-audit.md` — карта текущих CSS/JS assets, inline-долги и правила дальнейшей верстки.
- `seo-gap-analysis.md` — детальный SEO gap analysis: indexability, 404, structured data, sitemap/robots и social preview.
- `product-marketing-gap-analysis.md` — продуктово-маркетинговый gap analysis: positioning, funnel, CTA, proof, `/offer/` segmentation и lead qualification.
- `design-token-contract.md` — AS IS token contract, guard и правила обновления design tokens handoff.
- `component-state-contract.md` — AS IS component/state contract, guard и правила сохранения/migration behavior-bearing selectors.
- `design-migration-map.md` — AS IS -> TO BE migration map, migration types and gates для дизайн-системной миграции.
- `product-content-source-switch-runbook.md` — порядок проверки, переключения и rollback для `products.source=bitrix`.
- `offer-example-seed-runbook.md` — запуск и контроль CLI-сидера synthetic offer examples для `/offer/`.
- `local-public-browser-error-challenge.md` — challenge `/local`, публичной части и browser zero-error gate.
- `release-signoff-gates.md` — ручные/staging sign-off gates для success-flow, Метрики, config sync и Bitrix admin.
- `rest-response-contract-decision.md` — решение по сохранению доменных success/error response shapes.
- `sprints/` — snapshot-ы спринтов.

## Static Guards

- `npm run bitrix:check` — guard для Bitrix architecture: thin `init.php`, отсутствие direct `bitrix:*` в public page entries, отсутствие component-level global helper functions, наличие `/offer/` service/cache hardening и footer modal component.
- `npm run config:runtime:check` — Bitrix/PHP runtime check для ignored `tacticum_config.php`: health scopes, iblock IDs, product source, endpoint path explicit/default status, CSP mode and REST summary without secret values.
- `npm run gaps:known` — PM/QA guard для текущего известного хвоста: code-level open gaps, pending release gates, legacy inventory и post-deploy/cache smoke.
- `npm run product:content:cache-clear:dry-run` / `npm run product:content:cache-clear` — Bitrix/PHP helper для проверки и очистки product content cache dir plus managed-cache tags перед switch/rollback.
- `npm run product:content:switch-readiness:prod` — HTTP/readiness guard перед переключением `products.source=bitrix`: проверяет health `products` scope, rendered `data-product-source=bitrix` and required product blocks.
- `npm run release:manual-gates:helper` — read-only helper для оставшихся ручных release gates: читает текущий sign-off draft, показывает pending `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream`, next actions and safe evidence skeletons без PII; если `docs/` не выгружен на production, работает в standalone skeleton mode без draft-контекста.
- `npm run manual:success-flow:helper` — read-only helper для controlled `manual-success-flow`: генерирует payload/browser/curl templates and safe evidence skeleton для default form, modal form, AI chat and prefill без отправки запроса.
- `npm run metrika:goals:helper` — read-only helper для `metrika-goals`: показывает expected goals/events, проверяет deployed JS taxonomy, даёт browser observer snippet and safe evidence skeleton без доступа к кабинету Метрики.
- `npm run bitrix:admin:gate-helper` — read-only helper для `bitrix-admin`: выдаёт authenticated admin/public toolbar checklist, browser observer snippet and safe evidence skeleton без логина, запросов, cookie/session data.
- `npm run staff:sale:gate-helper` — helper для controlled `staff-sale-upstream` gate: генерирует staff-order payload, curl template и safe evidence block без отправки запроса.
- `npm run legacy:sale:inventory:logs` — aggregate-only parser для production access logs по legacy sale aliases; выводит endpoint/method/status/day counts без IP, query, referrer, cookie, user-agent и raw log lines.
- `npm run product:gaps:check` — guard для AS IS / TO BE product gap closure: сверяет source backlog `14-gap-backlog-and-decision-register.md`, master plan and `16-gap-closure-action-register.json`, чтобы каждый non-closed gap имел owner, next action, blocker/evidence model, review artifact coverage and package script.
- `npm run template-styles:check` — guard для CSS retirement и template public asset hygiene, включая запрет возврата Remixicon demo HTML в `local/templates/tacticum/fonts/`.
- `npm run design:tokens:check` — guard для AS IS token contract: сверяет `05-design-tokens-as-is.json` с Tailwind theme, `global.css`, `forms.js` и package script.
- `npm run design:components:check` — guard для AS IS component/state contract: сверяет `07-component-state-contract.json` с behavior-bearing templates/JS и package script.
- `npm run design:migration:check` — guard для AS IS -> TO BE migration map: проверяет покрытие всех component ids из `07-component-state-contract.json`, migration types, risk gates and package script.
- `npm run design:handoff:check` — aggregate guard для design handoff package: запускает token/component/migration checks и проверяет полноту `01`-`09`, README references, workflow docs and scripts.

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
