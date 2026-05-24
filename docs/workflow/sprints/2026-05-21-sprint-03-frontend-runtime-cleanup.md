# Sprint 03: Frontend Runtime Cleanup

Дата старта: 21.05.2026

## Sprint Goal

Сократить frontend/runtime-долг после стабилизации форм и AI-чата: убрать хрупкое подключение assets, удалить неиспользуемый demo chat artifact и довести prefill до POST-only REST policy.

## Scope

| Item | Gap | Lane | Status | Acceptance Criteria |
|---|---|---|---|---|
| Explicit page asset flags | TG-011 | Fast Fix | done | `header.php` больше не использует URL substring для optional assets; страницы объявляют `TACTICUM_PAGE_ASSETS` до `require bitrix/header.php` |
| Legacy chat artifact cleanup | PG-001, TG-006 | Fast Fix | done | `local/templates/tacticum/js/chat.js` удалён; production chat обслуживает `chat-agent.js`; ссылок на `chat.js` в коде нет |
| Prefill POST-only | TG-010 | Security / Integration | done | `tacticum_prefill.php` принимает только POST JSON, GET возвращает `405 method_not_allowed`; `chat-offer-contract.md` обновлён |
| Chat API contract | PG-001 | Security / Integration | done | `/local/rest/tacticum_chat.php` POST-only, валидирует `group_id`, contract зафиксирован в `docs/workflow/chat-api-contract.md` |
| Static CSS build plan | TG-011 follow-up | Full Feature | done | План отказа от browser Tailwind/runtime bundle и visual regression matrix зафиксирован в `docs/workflow/static-css-build-plan.md` |
| CI convention guards | TG-012 | Security / Integration | done | `pr-check.yml` блокирует tracked ignored files, legacy `chat.js`, URL-substring assets, GET fallback prefill и direct curl вне helper |

## QA Smoke

- `/`, `/services/`, `/calculator/`, `/offer/`: `faq.js` загружается там, где ожидается.
- `/price/`: `faq.js` и `charts.js` загружаются, графики ставок не ломаются.
- `/aiagents/`: `faq.js` загружается явно; former `aiagents.css` позже слит в scoped `styles/global.css`.
- `/about/`, `/contacts/`, `/policies/`: optional assets не подключаются без явной необходимости.
- Hero chat на `/` доходит до prefill через `POST /local/rest/tacticum_prefill.php`.
- `GET /local/rest/tacticum_chat.php` возвращает controlled `405 method_not_allowed`.
- `GET /local/rest/tacticum_prefill.php` возвращает controlled `405 method_not_allowed`.

## Follow-Up

- Реализовать static Tailwind/CSS build plan из `docs/workflow/static-css-build-plan.md`.
- После visual regression разобрать `local/templates/tacticum/styles/*.css` на used/dead.
- После deploy выполнить browser smoke из `docs/workflow/post-deploy-smoke.md` по asset loading и chat/prefill flow.

## Sprint Review

### Done

- Optional page assets переведены на явные `TACTICUM_PAGE_ASSETS` flags в публичных страницах и `header.php`.
- Удалён legacy artifact `local/templates/tacticum/js/chat.js`; production chat остаётся на `chat-agent.js`.
- `tacticum_prefill.php` и `tacticum_chat.php` закреплены как POST-only endpoints, `group_id` валидируется до upstream request.
- Chat API contract зафиксирован в `docs/workflow/chat-api-contract.md`, offer/prefill contract обновлён.
- Static CSS migration plan зафиксирован отдельно, без рискованного удаления generated/runtime CSS до visual baseline.
- PR checks блокируют regressions по legacy chat artifact, URL-substring asset routing, GET fallback prefill, direct curl вне helper и tracked ignored files.
- Deploy pipeline после rsync/cache clear проверяет `https://tacticum.ru/local/rest/health_config.php` и падает, если production config невалиден.

### Verification

- `node --check` пройден для `analytics.js`, `forms.js`, `chat-agent.js`, `tg-link-resolver.js`.
- `.github/workflows/pr-check.yml` и `.github/workflows/deploy.yml` успешно парсятся через Ruby YAML loader.
- Новый shell-блок PR checks проходит `bash -n`.
- `git diff --check` без whitespace errors.
- Manual guard scan подтверждает: `chat.js` удалён, `header.php` не использует `substr_count`/`GetCurPage`/`addString`, `tacticum_prefill.php` не содержит GET fallback, direct curl в `/local/rest/*.php` вне helper отсутствует.

### Not Run Locally

- PHP lint: локально нет `php` в `PATH`; deploy workflow запускает PHP 8.4 lint на GitHub Actions.
- Browser/production smoke: требует deployed/staging окружение; чеклист находится в `docs/workflow/post-deploy-smoke.md`.
