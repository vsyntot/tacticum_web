# Sprint 07 — Release Hardening / Operational Gates

Дата: 23.05.2026

## Цель

Закрыть оставшиеся operational gaps после стабилизации `/local`, публичной части сайта и `/price/`: сделать post-deploy smoke обязательным gate, уменьшить CSP/inline debt, зафиксировать lifecycle legacy sale endpoints и оформить безопасный маршрут дальнейшего CSS cleanup.

## Workflow Lane

Основной lane: `Full Feature` + `Security / Integration`.

Почему не `Fast Fix`: изменения затрагивают deploy workflow, browser smoke tooling, template asset loading и публичные REST endpoints.

## Состав Команды

| Роль | Ответственность |
|---|---|
| PM | Зафиксировать scope, acceptance criteria, остаточные ручные проверки |
| Architect | Проверить, что изменения не ломают Bitrix asset pipeline и REST contracts |
| Frontend Dev | Вынести Метрику из inline в template asset, сохранить analytics behavior |
| Backend Dev | Сохранить legacy endpoint response shape и добавить deprecation headers |
| QA | Уточнить automated/manual smoke matrix |
| DevOps | Сделать post-deploy visual/browser smoke частью deploy gate |

## Реализованный Scope

### RH-001 — Post-Deploy Smoke Gate

Файлы:

- `.github/workflows/deploy.yml`
- `tools/visual-smoke.mjs`
- `package.json`

Изменения:

- deploy после cache clear и `health_config` запускает `npm run visual:smoke`;
- deploy запускает `npm run browser:smoke` с `TACTICUM_EXPECT_PRICE_TEAM_PRESETS=1`;
- smoke runner сам ищет Chrome/Chromium в macOS/Linux paths и больше не привязан только к `/Applications/Google Chrome.app/...`;
- добавлены npm aliases `visual:smoke:prod`, `browser:smoke:prod`, `browser:smoke:price`.

Acceptance criteria:

- deploy падает, если production после выкладки имеет browser runtime errors, broken images, horizontal overflow или action smoke failures;
- `/price/` team presets/summary входят в обязательный browser smoke после deploy;
- runner работает локально на macOS и в GitHub Actions Linux без ручного `CHROME_PATH`, если Chrome/Chromium установлен в стандартном месте.

### RH-002 — Metrika CSP Readiness

Файлы:

- `local/templates/tacticum/header.php`
- `local/templates/tacticum/js/metrika.js`

Изменения:

- inline Yandex.Metrika script удалён из `header.php`;
- Metrika подключается как обычный template asset через `Bitrix\Main\Page\Asset`;
- `noscript` pixel сохранён.

Acceptance criteria:

- счетчик `103471113` и параметры `ssr`, `webvisor`, `clickmap`, `accurateTrackBounce`, `trackLinks` сохранены;
- в production template больше нет inline JS для Метрики;
- будущий CSP может строиться вокруг `script-src 'self' https://mc.yandex.ru` без nonce/hash для этого блока.

### RH-003 — Legacy Sale Endpoint Lifecycle

Файлы:

- `local/rest/tacticum_offer.php`
- `local/rest/tacticum_sale.php`

Изменения:

- response shape и upstream logic сохранены;
- добавлены HTTP headers `Deprecation`, `Sunset` и `Link: rel="successor-version"` на `/local/rest/tacticum_form.php`.

Acceptance criteria:

- существующие потребители продолжают получать прежний JSON success/error contract;
- новые интеграции получают явный сигнал не использовать legacy aliases;
- дата sunset зафиксирована: `30.09.2026`.

### RH-004 — Template Styles Retirement Plan

Файлы:

- `docs/workflow/template-styles-retirement-plan.md`
- `docs/workflow/static-css-build-plan.md`
- `docs/workflow/asset-layout-audit.md`

Изменения:

- зафиксирован безопасный поэтапный план вывода `template_styles.css`;
- прямое удаление файла отложено до production visual baseline и rule ownership mapping.

Acceptance criteria:

- есть понятный маршрут миграции без одномоментного риска сломать весь шаблон;
- новые CSS-правки не расширяют legacy файл без причины;
- будущая миграция имеет stop criteria и rollback plan.

## Ручные Проверки После Deploy

Автоматизировано в deploy:

- `health_config`;
- `visual:smoke`;
- `browser:smoke`;
- `/price/` team presets через `TACTICUM_EXPECT_PRICE_TEAM_PRESETS=1`.

Остаётся ручным/staging-smoke:

- реальные успешные POST-сценарии форм, чата, prefill и specialist order, потому что production-запуск создаёт лиды и может дергать внешний AI/upstream;
- подтверждение целей Метрики в кабинете аналитики;
- проверка Bitrix admin panel авторизованным администратором.

## Definition Of Done

- Кодовые изменения внесены.
- Документация обновлена.
- Локальные syntax/static checks пройдены настолько, насколько позволяет окружение.
- Production deploy должен выполнить автоматический post-deploy smoke перед закрытием релиза.
