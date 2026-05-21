# Sprint 02: 21.05.2026 - 04.06.2026

## Sprint Goal

Стабилизировать пользовательские сценарии публичных страниц после Sprint 01: привести верстку, формы, AI-чат и REST UX к единому, проверяемому поведению без изменения продуктовой модели и без расширения внешних API-контрактов сверх уже известных gaps.

## Context

Sprint основан на текущем challenge стабилизации фронтенда и REST UX:

- AI-chat реализован несколькими inline-скриптами и статическим `chat.js`, поэтому поведение `/`, `/calculator/`, `/price/` расходится.
- Лид-формы уже имеют общий contract, но UX состояний, consent links, staff-order и offer/prefill flow требуют smoke-проверки как единый funnel.
- Публичные страницы сохраняли legacy debts: inline JS/CSS, numeric `IBLOCK_ID`, неясное подключение page-specific CSS.
- REST runtime после Sprint 01 строже относится к HTTPS config, CSRF и masking; фронтенд должен показывать понятные состояния ошибок.

## Workflow Lanes

Основной lane: `Full Feature` для унификации AI-chat/frontend behavior.

Дополнительные lanes:

- `Security / Integration` для REST UX, CSRF, AI payload, prefill, error handling.
- `Fast Fix` для точечных правок верстки, consent links, CSS/assets.
- `Full Feature` с SEO participation для meta/OG audit, если меняются публичные страницы.

## Roles

| Role | Responsibility |
|---|---|
| PM / Owner | Утверждает sprint scope, приоритеты, trade-offs и release readiness |
| Analyst | Фиксирует сценарии chat/calculator/offer/forms и ожидаемые состояния |
| Architect | Проверяет, не нужен ли ADR при изменении shared frontend/REST patterns |
| Frontend | Унифицирует chat/forms UX, assets, layout fixes, responsive behavior |
| Backend | Поддерживает REST contract, response normalization и helper reuse |
| QA | Даёт early review для REST/form/chat сценариев и выполняет smoke |
| SEO | Проверяет публичные URL, meta/OG, sitemap impact при изменении страниц |
| DevOps | Подтверждает HTTPS AI config и post-deploy smoke environment |

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| 1 | PG-001, TG-006 | Full Feature | Frontend + Analyst + QA | P1 | done | Inline chat вынесен в общий `chat-agent.js`; static `chat.js` остаётся legacy artifact |
| 2 | PG-003, TG-010 | Security / Integration | Backend + Frontend + QA | P1 | done | `group_id`/prefill contract задокументирован; production prefill переведён на POST JSON |
| 3 | PG-002 follow-up, PG-005 | Fast Fix | Frontend + QA | P1 | done | Lead Form Contract уже зафиксирован; `/contacts/` и legacy staff fallback приведены к нему |
| 4 | TG-003 | Security / Integration | Backend + Architect + QA | P1 | done | Все outbound AI/Telegram requests в `/local/rest` переведены на shared helper |
| 5 | TG-002, PG-007 | Full Feature | Backend + Frontend + QA | P1 | done | Public `IncludeComponent` переведены на `tacticum_iblock_id('key')` |
| 6 | TG-011 | Fast Fix | Frontend | P2 | done | Новый chat asset подключён через `Asset`; asset/layout audit зафиксирован |
| 7 | PG-004 | Full Feature | SEO + Frontend | P2 | done | Базовые `description`, canonical и OpenGraph добавлены на публичные страницы |
| 8 | TG-008 | Fast Fix | Backend | P2 | done | Scan `CModule::IncludeModule()` в `local/` и public scope выполнен |
| 9 | TG-009 | Full Feature | Backend | P2 | done | Public GET API payload кэшируется через `Bitrix\Main\Data\Cache`, TTL вынесен в config |
| 10 | TG-013 | Security / Integration | Backend + DevOps | P2 | done | Добавлен `tacticum_rest_validate_config()` и `/local/rest/health_config.php` |
| 11 | PG-006 | Full Feature | Frontend + Analyst | P2 | done | Добавлен `analytics.js` и безопасная event taxonomy без PII |
| 12 | TG-014 | Fast Fix | PM + DevOps | P2 | done | Ignore rules и audit зафиксированы; local config убран из Git index и оставлен на диске |

## Out Of Scope

- Новый публичный раздел, новая навигация или редизайн сайта.
- Новая AI-функция, новый upstream provider или изменение бизнес-логики AI-консультации.
- Полная миграция всех публичных страниц на новую компонентную архитектуру.
- Полное e2e-покрытие; в спринте нужен targeted smoke и ручная QA matrix.
- Редактирование `bitrix/`.
- Изменение sitemap, если не появляется новый публичный URL.
- Изменение server secrets или production-only `tacticum_config.php` в репозитории.

## Backlog Acceptance Criteria

### 1. Unified AI Chat Frontend

Priority: P1

Status: done

Owners: Frontend, Analyst, QA

Affected areas: `index.php`, `calculator/index.php`, `price/index.php`, `local/templates/tacticum/js/chat.js`, template assets.

Acceptance criteria:

- Один общий frontend module управляет отправкой сообщений, loading/error/success states и CSRF token injection для chat surfaces.
- `/`, `/calculator/`, `/price/` используют одинаковую модель DOM/data-атрибутов или явно задокументированные различия.
- Static/demo behavior не подменяет production REST behavior на страницах с реальным chat flow.
- Ошибки REST/upstream показываются пользователю без технического stack/config текста.
- Повторная отправка во время pending state заблокирована или корректно сериализована.
- QA может выполнить один smoke-сценарий chat send для всех страниц по общей checklist.

### 2. Calculator -> Offer -> Prefill Flow Contract

Priority: P1

Status: done

Owners: Backend, Frontend, QA, Analyst

Affected areas: `/calculator/`, `/offer/`, `/local/rest/tacticum_chat.php`, `/local/rest/tacticum_prefill.php`, offer component.

Acceptance criteria:

- `group_id` lifecycle описан в issue/spec или sprint follow-up note: источник, где хранится на client side, как передаётся в form payload.
- Prefill success, empty result, invalid `group_id`, expired/missing CSRF и upstream/config error имеют различимые UX states.
- Frontend не логирует PII и не выводит raw payload пользователю.
- Backend сохраняет masking discipline для logs.
- Изменение payload или error model проходит ADR/contract gate до реализации.

### 3. Lead Forms And Consent UX Stabilization

Priority: P1

Status: done

Owners: Frontend, QA

Affected areas: public lead forms, modal form, `forms.js`, consent copy/links.

Acceptance criteria:

- Все формы с `data-tacticum-form` имеют корректные `data-form-id`, consent control и submit state.
- Consent links ведут на `/policies/`, без `href="#"` для юридически значимых ссылок.
- Success/error toast или inline state единообразны для main CTA, modal, calculator, services, price, about, offer, aiagents.
- Submit disabled/loading state не ломает layout на desktop/mobile.
- `page_url`, `sessid`, `group_id` при наличии контекста отправляются согласно Lead Form Contract.
- QA smoke подтверждает отсутствие raw PII в browser console и server-visible UI.

### 4. REST UX And Response Normalization

Priority: P1

Status: done

Owners: Backend, Architect, QA

Affected areas: `local/rest/tacticum_form.php`, `tacticum_chat.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, shared helpers.

Acceptance criteria:

- Пользовательские ошибки в формах/chat мапятся на понятные frontend states.
- Security failures для origin/rate limit/CSRF остаются строгими и не раскрывают внутренние детали.
- AI outbound helper или normalization вводятся только если уменьшают дублирование без изменения внешнего contract; при изменении pattern срабатывает ADR gate.
- Staff-order `/price/` сохраняет текущий rich `workers[]` payload или изменение явно согласовано.
- QA получает таблицу expected HTTP status/body shape для smoke.

### 5. Public Iblock ID Cleanup

Priority: P1

Status: done

Owners: Backend, Frontend, QA

Affected areas: public pages and included components using legacy numeric `IBLOCK_ID`.

Acceptance criteria:

- Новый или затронутый код не содержит hardcoded numeric `IBLOCK_ID`; используется `tacticum_rest_get_iblock_id('key')` или существующий approved helper.
- Refactor выполняется small batches с проверкой каждой публичной страницы.
- В случае невозможности безопасной замены конкретный block фиксируется как follow-up с причиной.
- Контентные блоки на `/`, `/services/`, `/price/`, `/offer/`, `/aiagents/` сохраняют фактическую выдачу.

### 6. Asset And Layout Audit

Priority: P2

Status: done

Owners: Frontend

Affected areas: `local/templates/tacticum/header.php`, `local/templates/tacticum/styles/`, page-specific CSS/JS.

Acceptance criteria:

- Зафиксировано, какие page-specific CSS files реально подключаются и где.
- Новые или переносимые JS/CSS подключаются через `Bitrix\Main\Page\Asset`.
- Inline JS/CSS сокращается только в затронутых сценариях, без широкого cleanup вне sprint scope.
- Главные страницы проходят responsive smoke: no horizontal scroll, no overlapping CTA/form/chat controls, stable header/menu behavior.

### 7. SEO Meta / OG Audit

Priority: P2

Status: done

Owners: SEO, Frontend

Affected areas: public pages only if touched by layout/forms/chat work.

Acceptance criteria:

- Для затронутых публичных страниц проверены `SetTitle`, `description`, H1, canonical/OG impact.
- Не появляется новый публичный URL без sitemap decision.
- SEO changes не блокируют P1 stabilization, если не затрагивают release safety.

### 9. Public API Cache

Priority: P2

Status: done

Owners: Backend, QA

Affected areas: `local/api/*.php`, `local/rest/rest_helpers.php`, config example.

Acceptance criteria:

- Public GET API сохраняет прежний JSON contract.
- Повторные ответы строятся через явный TTL cache.
- TTL управляется config и может быть выключен значением `0`.
- Ошибки bootstrap/method/origin/rate не кэшируются.

### 10. Config Validation Health

Priority: P2

Status: done

Owners: Backend, DevOps, QA

Affected areas: `rest_helpers.php`, `/local/rest/health_config.php`, config example.

Acceptance criteria:

- Health endpoint не выводит secret/config values.
- Проверяются iblock keys, HTTPS base URLs, rest origins/IP/proxy shape и API TTL types.
- Ошибка config возвращает список `key`/`code` для диагностики.
- Endpoint остаётся защищён origin/host/rate guardrails и не имеет state-changing side effects.

### 11. Analytics Events

Priority: P2

Status: done

Owners: Frontend, Analyst, QA

Affected areas: `analytics.js`, `forms.js`, `chat-agent.js`, `tg-link-resolver.js`.

Acceptance criteria:

- Формы, chat, prefill и Telegram resolver отправляют события попытки/успеха/ошибки.
- В события не попадают имя, телефон, email, текст сообщения, summary или query string.
- Event taxonomy зафиксирована в `docs/workflow/analytics-events.md`.

### 12. Repository Hygiene

Priority: P2

Status: done

Owners: PM, DevOps

Affected areas: `.gitignore`, Git index, local config.

Acceptance criteria:

- `.DS_Store`, logs, Bitrix runtime/cache/backup и IDE files игнорируются.
- Tracked ignored files выявлены командой `git ls-files -c -i --exclude-standard`.
- `local/php_interface/include/tacticum_config.php` удалён из Git index после approval владельца и остаётся ignored local config.

### 8. Legacy D7 Scan

Priority: P2

Status: done

Owners: Backend

Affected areas: touched backend/runtime files.

Acceptance criteria:

- В затронутых backend/runtime файлах новый module loading использует `\Bitrix\Main\Loader::includeModule()`.
- Existing `CModule::IncludeModule()` вне touched scope фиксируется как backlog, если safe refactor не входит в задачу.
- Scan result приложен к sprint review или issue comment.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | conditional | Нужен при изменении API contract, shared REST/security pattern, AI integration или общего chat/frontend pattern |
| Design | yes | Требуется review для layout/chat/form states и responsive behavior |
| QA early | yes | Обязательно для chat, prefill, form, CSRF/rate/origin сценариев |
| SEO | conditional | Требуется для touched public pages, meta/OG или URL changes |
| Post-deploy smoke | yes | Обязательно по всем затронутым pages/endpoints/forms |

## QA Smoke Scope

| Scenario | URL/API | Expected |
|---|---|---|
| Main lead form | `/` | Submit success or controlled error, loading state, consent required, no raw PII in UI |
| Modal lead form | Any public page with modal CTA | Opens/closes cleanly, submit uses default endpoint, success closes or resets predictably |
| Services/about CTA forms | `/services/`, `/about/` | Correct `form_id`, `page_url`, `sessid`, consent link `/policies/` |
| Calculator chat | `/calculator/` | Sends message with CSRF, handles AI success/error, does not duplicate pending messages |
| Main chat | `/` | Same UX model as calculator chat, no static demo response on production flow |
| Price chat / staff order | `/price/` | Chat state works; specialist order preserves workers payload and shows controlled error on failure |
| Offer prefill | `/offer/` with valid/invalid `group_id` | Valid data prefilled; invalid/missing data shows controlled fallback |
| AI agents form/link resolver | `/aiagents/` | Form still submits; Telegram resolver failure does not break page JS |
| REST CSRF failure | POST endpoint without valid `sessid` | 403/controlled JSON error, frontend shows safe message |
| REST rate/origin failure | POST endpoint under blocked condition | Strict failure, no internal config/upstream details in UI |
| Responsive layout | Mobile and desktop for `/`, `/calculator/`, `/price/`, `/offer/` | No overlap, no horizontal scroll, submit/chat controls remain reachable |

## Definition Of Done

- P1 backlog items are implemented or explicitly moved to `Not Done` with owner and reason.
- All touched public forms and chat surfaces pass QA smoke.
- REST/API/security touched code passes origin/rate/CSRF expectations.
- No new hardcoded iblock IDs, external HTTP URLs, raw PII logs or inline assets outside approved legacy cleanup constraints.
- New JS/CSS assets are registered through `Bitrix\Main\Page\Asset`.
- Design review confirms layout/form/chat states on desktop and mobile.
- SEO review is completed for touched public pages.
- Deploy is completed if needed, followed by post-deploy smoke.
- Sprint review records Done, Not Done and Follow-Up without silently changing gap statuses.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Unified chat frontend changes behavior on one page while fixing another | Frontend + QA | Inventory current flows first, use shared smoke matrix across `/`, `/calculator/`, `/price/` |
| REST strict CSRF/HTTPS config surfaces as confusing user error | Backend + Frontend | Map technical failures to safe UX copy and verify stage config before deploy |
| Public iblock cleanup breaks content rendering | Backend + QA | Refactor in small batches and compare page output before/after |
| Inline asset cleanup expands beyond sprint scope | Frontend + PM | Limit cleanup to touched chat/forms/layout surfaces |
| Staff-order payload regression breaks `/price/` leads | Backend + QA | Preserve `workers[]` contract and add dedicated smoke case |
| SEO work competes with P1 stabilization | PM + SEO | Treat SEO as conditional P2 unless touched page lacks required basic metadata |
| Production AI upstream unavailable during smoke | DevOps + QA | Prepare expected controlled failure path and confirm HTTPS config before release |

## Sprint Review

### Done

- Зафиксирован Sprint 02 backlog с ролями, scope, acceptance criteria, QA smoke и DoD.
- Создан единый frontend module `local/templates/tacticum/js/chat-agent.js` для hero chat, calculator chat на `/` и light calculator chat на `/calculator/` и `/price/`.
- Удалены inline chat handlers/styles из `index.php`, `calculator/index.php`, `price/index.php`; новый asset подключён через `local/templates/tacticum/header.php`.
- Удалён устаревший offer detail inline script: prefill на offer уже обслуживается общим `forms.js` через `data-tacticum-prefill-*`.
- `/contacts/` CTA переведена из псевдоформы в рабочую форму с `data-tacticum-form`, `data-form-id`, `name`, consent и submit button.
- Legacy specialist modal fallback в `modal.js` переведён на общий form-handler и больше не показывает fake success без отправки.
- Исправлены явные layout/HTML defects: nested `button > a`, `justify_between`, `bg_white`, лишний `target="_blank"` у anchor, missing `rel="noopener"` у активных внешних ссылок.
- REST UX усилен точечно: `tacticum_offer.php` и `tacticum_sale.php` различают curl timeout/connect errors; `resolve_telegram_link.php` возвращает `invalid_json` для битого JSON.
- Все outbound AI/Telegram requests в `/local/rest` переведены на shared helper `tacticum_rest_post_json()` / `tacticum_rest_fail_on_curl_error()`.
- Public `IBLOCK_ID` cleanup выполнен: добавлен `tacticum_iblock_id()` и активные публичные `IncludeComponent` переведены на config registry.
- `group_id` lifecycle, chat response states и prefill handoff зафиксированы в `docs/workflow/chat-offer-contract.md`.
- Production prefill flow переведён на `POST /local/rest/tacticum_prefill.php`; legacy GET fallback удалён в Sprint 03 cleanup.
- Asset/layout audit зафиксирован в `docs/workflow/asset-layout-audit.md`.
- D7 scan выполнен: `CModule::IncludeModule()` в `local/` и публичных страницах не найден.
- На публичные страницы добавлены базовые meta `description`.
- Добавлен `tacticum_apply_seo_defaults(...)`; публичные страницы получили canonical и OpenGraph baseline.
- `/policies/` переведена на `tacticum_iblock_id('policies')` и получила description/canonical/OG.
- Добавлен `analytics.js`; формы, AI-chat, prefill и Telegram resolver отправляют safe events без PII.
- Public GET API `/local/api/{cases,faq,rates,services}.php` переведены на TTL-кэш через `tacticum_api_cached_payload(...)`.
- Добавлен `tacticum_rest_validate_config(...)` и `GET /local/rest/health_config.php`.
- Repository hygiene audit зафиксирован в `docs/workflow/repository-hygiene.md`.
- `local/php_interface/include/tacticum_config.php` убран из Git index и оставлен на диске как ignored local config.
- Optional assets переведены с URL-substring routing на explicit page flags.
- Legacy `local/templates/tacticum/js/chat.js` удалён.

### Not Done

- Полная унификация success-body contract для всех endpoints не выполнена: response shapes оставлены доменными, чтобы не ломать frontend/API consumers.
- Отказ от runtime Tailwind не входил в реализованный кодовый scope.

### Follow-Up

- Sprint 03 candidate: унифицировать success-body contract только после отдельного API decision.
- Sprint 03 candidate: спланировать переход с runtime Tailwind на static build.
- DevOps/PM: синхронизировать production/staging `tacticum_config.php` с `tacticum_config.example.php` при новых config keys.
