# Gap Analysis — tacticum.ru

Дата аудита: 20.05.2026
Дата последнего обновления: 22.05.2026

Статусы:

- `open` — gap подтверждён и требует задачи;
- `in-progress` — уже есть активная работа;
- `accepted` — осознанно принимаем риск;
- `closed` — закрыто, оставить ссылку на PR/Issue.

Приоритеты:

- `P0` — production/security blocker;
- `P1` — важно для ближайшего спринта;
- `P2` — плановый backlog;
- `P3` — nice-to-have.

## Executive Summary

На 22.05.2026 повторный challenge `/local` и публичной части выявил новый набор operational gaps, связанных с browser zero-error gate, action-smoke и остаточными frontend/backend ownership debt.

Быстрые P1-правки уже внесены на уровне кода:

1. `visual:smoke` расширен до browser runtime checks.
2. Фоновый Telegram resolver переведён в lazy/on-click режим.
3. POST endpoints приведены к единому method/body bootstrap.
4. PII masking в логах стал консервативнее.
5. Light chat и `/price/` component переведены на явные `data-*` контракты вместо presentation/text selectors.
6. Добавлен `browser:smoke` / `TACTICUM_VISUAL_ACTIONS=1` для non-network UI action checks.
7. Yandex Maps вынесен в explicit page asset, Metrika noscript pixel лишён inline style.
8. Sale upstream ownership централизован в shared `tacticum_rest_submit_chat_agent_sale(...)`.

Оставшийся обязательный gate после выкладки:

1. Выполнить post-deploy `npm run visual:smoke` против целевого staging/production URL без `TACTICUM_VISUAL_INJECT_CSS`.
2. Подтвердить initial-load browser errors = 0 по manifest.

Закрытые, но требующие постоянного контроля области:

- HTTPS/config discipline для AI endpoints через deploy health smoke.
- Iblock registry вместо hardcoded IDs.
- Unified AI chat / lead form contracts.
- Post-deploy smoke для форм, AI-чата, API cache и SEO meta.
- Explicit component/data contracts вместо URL/text/inline handlers.

## Product Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| PG-001 | closed | P1 | Full Feature | AI chat / calculator | AI-chat пользовательский сценарий и REST contract унифицированы для production surfaces | `local/templates/tacticum/js/chat-agent.js`, `header.php`, `docs/workflow/chat-api-contract.md`; inline chat и legacy `chat.js` удалены | Поведение и ошибки чата теперь чинятся в одном модуле, API edge cases зафиксированы для QA | Поддерживать chat API contract при изменениях upstream |
| PG-002 | closed | P1 | Full Feature | Lead flow | Контракт лид-форм зафиксирован для QA и будущих правок | `docs/workflow/lead-form-contract.md`, `forms.js`, `/local/rest/tacticum_form.php` | Снижен риск расхождения форм, есть form_id taxonomy и smoke cases | Поддерживать Lead Form Contract и analytics taxonomy при новых формах |
| PG-003 | closed | P1 | Security / Integration | Offer flow | AI calculator → offer → prefill flow и `group_id` lifecycle задокументированы | `tacticum_chat.php`, `tacticum_prefill.php`, `chat-agent.js`, `docs/workflow/chat-offer-contract.md` | Риск поломки при изменении AI payload или свойств инфоблока снижен, QA получил smoke cases | Поддерживать contract при изменениях chat/prefill/offer |
| PG-004 | closed | P2 | Full Feature | SEO/content | Базовые `description`, canonical и OpenGraph добавлены на публичные страницы | `tacticum_apply_seo_defaults()`, public pages, `policies/index.php` | Сниппеты, шаринг и canonical policy приведены к единому baseline; `/offer/` canonical учитывает `ID` | Post-deploy smoke: проверить rendered head и отсутствие дублей meta |
| PG-005 | closed | P2 | Fast Fix | Legal/consent | Consent-ссылки активных форм ведут на `/policies/` и открываются безопасно | `index.php`, `calculator/index.php`, `contacts/index.php`, `offer/template.php`, `footer.php`, `forms.js` | Legal UX consistency для публичных форм восстановлена | Поддерживать правило в Lead Form Contract и PR checks |
| PG-006 | closed | P2 | Full Feature | Analytics | Добавлена taxonomy и client-side events для форм, AI chat, prefill, Telegram resolver | `analytics.js`, `forms.js`, `chat-agent.js`, `tg-link-resolver.js`, `docs/workflow/analytics-events.md` | Conversion funnel можно мерить без отправки PII в аналитику | Post-deploy smoke: подтвердить goals в Yandex.Metrika/tag manager |
| PG-007 | closed | P2 | Full Feature | Content model | Ключи инфоблоков используются публичными страницами через config helper | `local/php_interface/include/tacticum_config.example.php`, `docs/adr/ADR-003-iblock-ids.md`, `tacticum_iblock_id()`, public `IncludeComponent` | Переносимость публичных страниц повышена, numeric public `IBLOCK_ID` устранены | Поддерживать правило в PR checks и не добавлять новые hardcoded IDs |
| PG-008 | closed | P2 | Full Feature | Layout consistency | Повторяемые CTA/form секции вынесены из публичных страниц в template includes с явными вариантами | `local/templates/tacticum/include/personal-offer-cta.php`, `local/templates/tacticum/include/project-discussion-cta.php`, `index.php`, `calculator/index.php`, `price/index.php`, `contacts/index.php`, `about/index.php`, `services/index.php` | UX-правки повторяемых CTA теперь делаются в одном месте; страницы передают только form config | Поддерживать PR guards и не копировать CTA markup обратно в public pages |

## Technology Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| TG-001 | closed | P0 | Security / Integration | HTTPS/config | Runtime REST не имеет HTTP fallback, требует HTTPS и production config health подтверждён | `rest_helpers.php`, REST endpoints, `local/php_interface/include/tacticum_config.example.php`, `.github/workflows/deploy.yml`, `GET https://tacticum.ru/local/rest/health_config.php` 21.05.2026 вернул `success: true` | Runtime защищён от silent HTTP fallback; deploy smoke продолжит ловить невалидный server config до пользовательских 500 | Поддерживать deploy health smoke и синхронизировать server `tacticum_config.php` при новых config keys |
| TG-002 | closed | P1 | Security / Integration | Config/iblocks | `init.php` и публичные `IncludeComponent` используют config registry для ID инфоблоков | `local/php_interface/init.php`, `docs/adr/ADR-003-iblock-ids.md`, public pages | Backend callbacks и публичные страницы стали переносимее между окружениями | Поддерживать `tacticum_iblock_id()` / `tacticum_rest_get_iblock_id()` как стандарт |
| TG-003 | closed | P1 | Security / Integration | REST consistency | Все outbound AI/Telegram requests в `/local/rest` проходят через shared helper; response shapes остаются доменными | `rest_helpers.php`, `tacticum_form.php`, `tacticum_chat.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `resolve_telegram_link.php` | Curl/timeout/TLS handling больше не расходится между endpoints | Отдельный будущий gap: унифицировать success-body contract, если потребуется продуктово |
| TG-004 | closed | P1 | Security / Integration | CSRF | `tacticum_rest_check_csrf()` требует явный token; chat/prefill/resolver frontend передаёт `BX.bitrix_sessid()` | `rest_helpers.php`, `index.php`, `calculator/index.php`, `price/index.php`, `tg-link-resolver.js` | CSRF модель приведена к явному Bitrix token для state-changing POST | Поддерживать правило в Lead Form Contract и PR checks |
| TG-005 | closed | P1 | Fast Fix | Logging/PII | Chat log tags унифицированы, prefill больше не логирует весь объект инфоблока | `tacticum_chat.php`, `tacticum_prefill.php` | Меньше риск PII в логах, проще фильтрация | Sprint 02: привести остальные tags к единой taxonomy при объединении endpoints |
| TG-006 | closed | P1 | Full Feature | Frontend maintainability | Chat inline scripts/styles вынесены; устаревший offer inline script удалён | `chat-agent.js`, `header.php`, `offer/template.php` | Основной chat/prefill flow теперь тестируемый и не дублируется в публичных страницах | Поддерживать правило: новый JS/CSS только через assets/components |
| TG-007 | closed | P1 | Fast Fix | SEO/sitemap | Sitemap переведён на HTTPS и включает `/policies/` | `sitemap.xml`, `sitemap-files.xml` | SEO inconsistency устранена для sitemap | Поддерживать sitemap при новых публичных URL |
| TG-008 | closed | P2 | Security / Integration | Bitrix D7 | В `local/` и публичных страницах scan не нашёл `CModule::IncludeModule()`; touched code использует `Loader::includeModule()` | `rest_helpers.php`, `init.php`, `tacticum_prefill.php`, public pages | Новый runtime-код ближе к D7 best practice | Поддерживать `Loader::includeModule()` как стандарт |
| TG-009 | closed | P2 | Full Feature | API performance | GET API endpoints используют `Bitrix\Main\Data\Cache` через `tacticum_api_cached_payload(...)` | `local/api/*.php`, `rest_helpers.php`, `tacticum_config.example.php` | Повторные запросы к public API меньше нагружают инфоблоки; TTL управляется config | Post-deploy smoke: проверить first/second response и invalidate при изменении контента |
| TG-010 | closed | P2 | Security / Integration | REST method policy | Production prefill flow работает только через POST JSON; legacy GET fallback удалён | `tacticum_prefill.php`, `chat-agent.js`, `docs/workflow/chat-offer-contract.md` | Семантика production REST flow выровнена с остальными `/local/rest` endpoints и меньше раскрывает данные через URL | Поддерживать POST-only prefill в smoke |
| TG-011 | closed | P2 | Full Feature | Asset loading | Фактическая карта CSS/JS assets зафиксирована; optional assets подключаются через explicit page flags; static CSS build plan описан | `docs/workflow/asset-layout-audit.md`, `docs/workflow/static-css-build-plan.md`, `header.php`, public pages | Снижен риск случайного подключения assets на новых URL, следующий CSS cleanup имеет безопасный маршрут | Реализовать static CSS build plan после visual baseline |
| TG-012 | closed | P2 | Security / Integration | CI quality gates | Critical runtime checks стали blockers, public hardcoded iblocks остаются warning-level | `.github/workflows/pr-check.yml` | Нарушения REST/API conventions сложнее протащить в main | Поддерживать список checks при новых ADR |
| TG-013 | closed | P2 | Full Feature | Config validation | Добавлен `tacticum_rest_validate_config()` и same-origin health endpoint без вывода secret values | `rest_helpers.php`, `local/rest/health_config.php`, `tacticum_config.example.php` | Ошибки config можно проверить до пользовательского runtime 500 | Post-deploy smoke: `GET /local/rest/health_config.php` с allowed host/origin |
| TG-014 | closed | P2 | Fast Fix | Repository hygiene | `.DS_Store`/cache/backup/IDE files ignored; `tacticum_config.php` убран из Git index и остаётся локальным ignored config | `.gitignore`, `docs/workflow/repository-hygiene.md`, `git ls-files -c -i --exclude-standard` | Риск случайного commit local config/runtime мусора снижен | Поддерживать hygiene check перед PR |
| TG-015 | closed | P1 | Full Feature | CSS architecture | Browser Tailwind runtime удалён; static Tailwind bundle собирается через npm; dead CSS/JS artifacts удалены; добавлен visual smoke и закрыты найденные overflow regressions | `package.json`, `tools/visual-smoke.mjs`, `local/templates/tacticum/assets/src/tailwind.css`, `local/templates/tacticum/tailwind.generated.css`, `template_styles.css`, `styles/aiagents.css`, `header.php`, `.github/workflows/pr-check.yml`, `asset-layout-audit.md` | FOUC/no-JS риск снижен, CSS utilities воспроизводимы локально и в CI; visual smoke с локально внедрённым CSS прошёл desktop/mobile для `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/` | После deploy выполнить `npm run visual:smoke` без CSS injection как обычный post-deploy gate |
| TG-016 | closed | P1 | Full Feature | Layout contracts | URL/text-based presentation и behavior убраны из затронутых мест | `faq/template.php`, `aiagents/index.php`, `modal.js`, `scroll.js`, `.github/workflows/pr-check.yml` | Компоненты меньше зависят от текущего URL и текста кнопок, риск случайного поведения ниже | Поддерживать explicit component params и data-* contracts в PR checks |
| TG-017 | closed | P1 | Full Feature | JS-owned markup | Specialist modal markup перенесён из JS в Bitrix component template; repeated CTA sections вынесены в template includes | `news.list/price/template.php`, `news.list/price/script.js`, `modal.js`, `local/templates/tacticum/include/personal-offer-cta.php`, `local/templates/tacticum/include/project-discussion-cta.php`, public pages | Заказ специалистов и CTA sections стали ближе к Bitrix component/include pattern; JS больше не владеет крупным modal markup | Поддерживать component/include pattern для новых повторяемых layout blocks |
| TG-018 | closed | P2 | Fast Fix | Inline markup cleanup | Убраны inline `onclick`, policy `<style>`/`style=`, progress inline widths и form UI inline style mutations; header logo получил `alt` | `about/index.php`, `services/index.php`, `policies/template.php`, `policies/style.css`, `index.php`, `forms.js`, `price/script.js`, `header.php` | HTML/JS стали семантичнее, меньше inline presentation/behavior | Поддерживать guard против inline `onclick`, policy inline styles и form inline style mutations |
| TG-019 | in-progress | P1 | Incident / Full Feature | Browser zero-error gate | Initial-load browser errors найдены и частично устранены: `visual:smoke` ловит runtime/network errors, Telegram resolver больше не делает фоновые POST при загрузке | `tools/visual-smoke.mjs`, `tg-link-resolver.js`, `footer.php`, `aiagents/index.php`, `local-public-browser-error-challenge.md` | После deploy должен исчезнуть текущий first-party console/network шум от `/local/rest/resolve_telegram_link.php`; gate становится воспроизводимым | После deploy запустить `npm run visual:smoke` без injection и добиться manifest без `pageErrors`, `consoleErrors`, first-party `networkErrors` |
| TG-020 | closed | P1 | Security / Integration | REST bootstrap / PII logs | POST endpoints приведены к `validate_origin -> rate_limit -> method -> parse JSON -> CSRF`; PII masking больше не логирует free text целиком | `rest_helpers.php`, `tacticum_form.php`, `tacticum_chat.php`, `tacticum_prefill.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `resolve_telegram_link.php` | Большие/битые тела не читаются до guard; логи меньше раскрывают пользовательский текст и контакты | Post-deploy REST smoke по chat/form/prefill/sale_staff |
| TG-021 | closed | P1 | Full Feature | Frontend data contracts | Light chat и price component переведены на явные `data-*` contracts: quick replies используют `data-message`, filters/modal/price state не зависят от presentation selectors | `chat-agent.js`, `calculator/index.php`, `price/index.php`, `news.list/price/template.php`, `news.list/price/script.js`, `.github/workflows/pr-check.yml` | Копирайтинг/CSS refactor больше не должен ломать отправку quick replies, фильтры ставок и модалку заказа специалиста | Поддерживать guard в `pr-check.yml`; покрыть клики отдельным action-smoke в TG-024 |
| TG-022 | closed | P2 | Security / Integration | Sale endpoint ownership | Upstream `/chat_agent/sale` call, group_id retry, request/response masked logging and upstream error handling centralized in shared sale adapter; `tacticum_offer.php` and `tacticum_sale.php` remain legacy aliases with preserved response shape | `rest_helpers.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_form.php`, `lead-form-contract.md`, `current-state.md` | Будущие изменения sale upstream/retry/logging теперь делаются в одном helper; публичные формы продолжают использовать `tacticum_form.php`, staff-order — `tacticum_sale_staff.php` | Если внешние consumers legacy endpoints больше не нужны, отдельной задачей удалить/redirect `tacticum_offer.php` и `tacticum_sale.php` |
| TG-023 | closed | P2 | Full Feature | Inline/vendor assets / CSP | Yandex Maps constructor вынесен в explicit `yandex_map` asset; Metrika остаётся централизованной analytics exception, noscript pixel перешёл с inline style на CSS class | `contacts/index.php`, `header.php`, `js/yandex-map.js`, `template_styles.css`, `asset-layout-audit.md` | Карта больше не живёт inline в public page; CSP-ready переход теперь сводится к nonce/loader policy для Metrika, а не к разбросанным inline embeds | При введении CSP добавить nonce/hash strategy для Metrika или локальный analytics loader |
| TG-024 | closed | P2 | Full Feature | Browser action smoke | Добавлен `browser:smoke` поверх `visual:smoke`: non-network actions кликают меню, contact modal, empty form validation, empty chat send, price filters/search/level и specialist modal | `tools/visual-smoke.mjs`, `package.json`, `post-deploy-smoke.md`, `local-public-browser-error-challenge.md` | Ошибки обработчиков теперь попадают в browser gate без создания лидов и содержательных AI-запросов | Post-deploy запускать `npm run browser:smoke`; реальные upstream success-flow по-прежнему проверять ручным/стейдж smoke |
| TG-025 | closed | P2 | Fast Fix | Agent instruction drift | Agent docs обновлены под static Tailwind и shared REST bootstrap | `.github/copilot-instructions.md`, `.github/agents/frontend-dev.md`, `.github/agents/backend-dev.md`, `.github/agents/designer.md`, `.github/agents/seo.md` | Новые агенты с меньшей вероятностью вернут удалённые CSS/JS artifacts или старый endpoint bootstrap | Поддерживать `.github/*` при изменении workflow docs |

## Recommended First Sprint

См. `docs/workflow/sprints/2026-05-20-sprint-01-stabilization.md`.

Цель первого спринта — не новая фича, а стабилизация основы:

- закрыть P0/P1 security/config gaps;
- привести sitemap/legal минимально в порядок;
- начать унификацию AI chat/form contracts;
- усилить CI gates.

## Gap Lifecycle

1. PM выбирает gap и создаёт Issue.
2. Issue получает `workflow_lane`, priority, owner.
3. Если gap проходит ADR/Design/QA gates — подключить нужного агента до разработки.
4. После PR/deploy обновить status gap:
   - `closed`, если полностью устранён;
   - `in-progress`, если часть работ осталась;
   - добавить ссылки на Issue/PR/ADR.
