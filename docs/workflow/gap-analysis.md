# Gap Analysis — tacticum.ru

Дата аудита: 20.05.2026
Дата последнего обновления: 24.05.2026

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
4. Файловое runtime-логирование из `/local` и публичных скриптов удалено; endpoints сохраняют прежние response contracts без записи payload/response в лог.
5. Light chat и `/price/` component переведены на явные `data-*` контракты вместо presentation/text selectors.
6. Добавлен `browser:smoke` / `TACTICUM_VISUAL_ACTIONS=1` для non-network UI action checks.
7. Yandex Maps вынесен в explicit page asset, Metrika noscript pixel лишён inline style.
8. Sale upstream ownership централизован в shared `tacticum_rest_submit_chat_agent_sale(...)`.
9. `/price/` staff order расширен до multi-staff сценария: состав заявки, segmented-выбор уровня, стабильный порядок уровней, количество по ролям, `workers_json` и backend validation.
10. Light chat на `/price/` и `/calculator/` получил ограничение высоты и внутреннюю прокрутку сообщений.
11. Публичный вывод инфоблоков переведён на общий decode/sanitize helper, чтобы не показывать служебные HTML entities.
12. `/price/` staff order получил persistent summary, быстрые пресеты команды и расчёт ориентировочного месячного бюджета.
13. Post-deploy smoke стал частью deploy workflow: `health_config`, visual smoke и browser action smoke выполняются после очистки Bitrix cache.
14. Yandex.Metrika вынесена из inline script в централизованный template asset `js/metrika.js`.
15. Legacy sale aliases получили `Deprecation`/`Sunset` headers без изменения JSON contract.

Обязательные gates после последующих выкладок:

1. Для GitHub deploy эти проверки выполняются автоматически в `deploy.yml` после cache clear.
2. При локальной/ручной выкладке выполнить `npm run visual:smoke:prod`, `npm run browser:smoke:prod` и для `/price/` `npm run browser:smoke:price`.
3. Реальные success-flow форм/чата/заявок проверять на staging или вручную с контролем создаваемых лидов.

Проверка 23.05.2026 против production показала: initial-load smoke чистый (`pageErrors=0`, `consoleErrors=0`, runtime/network blockers отсутствуют), но `browser:smoke` падал на `/price/`, потому что production HTML содержит legacy-разметку `.filter-tab`, `.pricing-card`, `.order-specialist-btn` без `data-price-*` и без `#specialistOrderModal` / `#specialistOrderForm`, а deployed JS уже ожидал новый component contract. `news.list/price/script.js` доработан как mixed-rollout fallback: поддерживает legacy selectors и создаёт fallback-модалку только если template её не отдал. Инъекционный smoke исправленного JS поверх текущего production HTML прошёл.

Повторный обычный `npm run browser:smoke` без CSS/JS injection 23.05.2026 прошёл для 18 desktop/mobile проверок: `bad=0`, browser runtime/network errors не выявлены. TG-019 закрыт.

Закрытые, но требующие постоянного контроля области:

- HTTPS/config discipline для AI endpoints через deploy health smoke.
- Iblock registry вместо hardcoded IDs.
- Unified AI chat / lead form contracts.
- Post-deploy smoke для форм, AI-чата, API cache и SEO meta.
- Explicit component/data contracts вместо URL/text/inline handlers.

## SEO Deep-Dive Gap Analysis — 24.05.2026

Детальный SEO challenge вынесен в `docs/workflow/seo-gap-analysis.md`. Post-deploy `npm run seo:smoke` 24.05.2026 прошёл на 9 публичных URL в desktop/mobile; повторный `npm run seo:check:prod` после deploy dedupe fix прошёл. `SEO-001` - `SEO-008` закрыты, `SEO-009` принят как UX/navigation decision.

| ID | Status | Priority | Area | Summary |
|---|---|---|---|---|
| SEO-001 | closed | P1 | `/offer/` indexability | Валидный `/offer/<code>/` отдаёт 200/self-canonical/indexable head, invalid ID/code — 404/noindex; offer sitemap отдаёт активные ЧПУ |
| SEO-002 | closed | P1 | 404 | Production 404 отдаёт HTTP 404, `X-Robots-Tag: noindex,nofollow`, корректный title/H1 |
| SEO-003 | closed | P2 | SEO helper | Production rendered smoke подтвердил robots/Twitter Card/OG dimensions/JSON-LD/page-specific schema options |
| SEO-004 | closed | P2 | Structured data | Production rendered smoke подтвердил JSON-LD graph на публичных URL |
| SEO-005 | closed | P2 | Metadata quality | Production rendered smoke подтвердил title/description/canonical/H1 на 9 URL |
| SEO-006 | closed | P2 | Social preview | Production rendered smoke подтвердил Twitter Card, OG image dimensions/type, page-specific images и `og-default.jpg` fallback |
| SEO-007 | closed | P2 | Sitemap governance | Static и dynamic sitemap governance закрыты гибридной моделью: repo-owned root `sitemap.xml`, Bitrix-generated `sitemap-basic-files.xml`, custom `/offer/sitemap.php`; `seo:check:prod` проверяет sitemap/robots/canonical inventory/HTTPS/lastmod, forbidden locs, JSON endpoint noindex headers и unique locs в `/offer/sitemap.php` |
| SEO-008 | closed | P2 | Service endpoint indexing | Production checks подтвердили `X-Robots-Tag: noindex, nofollow` на JSON endpoints |
| SEO-009 | accepted | P3 | Internal linking | Money pages остаются дочерними пунктами `Услуги` через `services/.top.menu_ext.php`, чтобы не перегружать header; `npm run seo:check` контролирует `/price/`, `/calculator/`, `/aiagents/` в top menu structure |

## Follow-Up Gap Closure — Sprint 08

На 23.05.2026 оставшиеся follow-up gaps закрыты кодом, автоматикой или formal sign-off gate:

| ID | Status | Area | Closure |
|---|---|---|---|
| FUG-001 | closed | CSS retirement | Старый generated Tailwind block удалён из `template_styles.css`; добавлен CSS replacement smoke и PR guard против возврата Tailwind layer block |
| FUG-002 | closed as gate | Real success-flow | `release-signoff-gates.md`, `post-deploy-smoke.md` и PR template требуют staging/manual evidence для форм, чата, prefill, staff-order |
| FUG-003 | closed as gate | Metrika goals | Release sign-off требует подтверждения goals в Yandex.Metrika при изменении analytics/form/chat |
| FUG-004 | closed | CSP readiness | `header.php` отправляет `Content-Security-Policy-Report-Only`; ADR-005 обновлён под report-only rollout |
| FUG-005 | closed | Legacy sale lifecycle | Добавлены `tools/legacy-sale-sunset-check.mjs`, `npm run sale:sunset:check`; после `30.09.2026` CI потребует решение |
| FUG-006 | closed | Config sync | Добавлены `ai.endpoint_paths.*`, `tools/config-contract-check.mjs`, `npm run config:check`; PR template требует owner для server config sync |
| FUG-007 | closed | REST response bodies | `rest-response-contract-decision.md` фиксирует сохранение доменных response shapes и правила для новых endpoints |
| FUG-008 | closed | Rich workers upstream | `/price/` staff-order использует config-driven `ai.endpoint_paths.staff_sale`; ADR-006 принят |

## Overall Gap Closure — Sprint 09

На 23.05.2026 residual overall gaps закрыты единым Sprint 09 artifact: `docs/workflow/sprints/2026-05-23-sprint-09-overall-gap-closure.md`.

| ID | Status | Area | Closure |
|---|---|---|---|
| OGC-001 | closed as gate | Release sign-off evidence | `release-signoff-gates.md` расширен rendered SEO, `/price` preset, legacy sunset и staff upstream gates; `release:signoff:check` валидирует release metadata, unknown gates, JSON evidence, структуру manual gates и placeholder/PII-like значения; `release:signoff:summary` показывает PM/QA статус draft и pending reasons; `release:signoff:self-test` закрепляет негативные кейсы checker; `manual-release-gates-runbook.md` и `release-signoff-manual-evidence.template.json` фиксируют безопасное закрытие ручных gates |
| OGC-002 | closed | `/price/` team presets | `browser:smoke:price` проверяет persistent summary, modal open, `workers_json` и monthly budget; UX summary доработан |
| OGC-003 | closed | Rendered SEO head | `visual-smoke` получил `seoHead`/`seoErrors`; deploy включает `TACTICUM_EXPECT_SEO_HEAD=1`; `/calculator/` и `/offer/` получили H1 baseline |
| OGC-004 | closed | CSS retirement | Active CSS перенесён из `template_styles.css` в `styles/global.css`; `template_styles.css` стал comment-only shim; `template-styles:check` блокирует возврат правил |
| OGC-005 | closed | CSP enforcing runway | `header.php` поддерживает `security.csp_mode=report-only|enforce`; `health_config` валидирует security scope; default остаётся `report-only`; ADR-005 фиксирует rollout/rollback |
| OGC-006 | closed as process decision | Rich workers upstream | `ai.endpoint_paths.staff_sale` остаётся единственным config switch; sale workstream фиксирует compatibility matrix |
| OGC-007 | closed as process decision | Legacy sale aliases sunset | Sale workstream фиксирует inventory до `30.06.2026`, migration до `31.08.2026`, final mode до `30.09.2026` |
| OGC-008 | closed | Local PHP CLI gap | `npm run dev:preflight` запускает PHP lint при PHP 8.4+ или фиксирует degraded local state с GitHub `php-lint` fallback |

## Product Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| PG-001 | closed | P1 | Full Feature | AI chat / calculator | AI-chat пользовательский сценарий и REST contract унифицированы для production surfaces | `local/templates/tacticum/js/chat-agent.js`, `header.php`, `docs/workflow/chat-api-contract.md`; inline chat и legacy `chat.js` удалены | Поведение и ошибки чата теперь чинятся в одном модуле, API edge cases зафиксированы для QA | Поддерживать chat API contract при изменениях upstream |
| PG-002 | closed | P1 | Full Feature | Lead flow | Контракт лид-форм зафиксирован для QA и будущих правок | `docs/workflow/lead-form-contract.md`, `forms.js`, `/local/rest/tacticum_form.php` | Снижен риск расхождения форм, есть form_id taxonomy и smoke cases | Поддерживать Lead Form Contract и analytics taxonomy при новых формах |
| PG-003 | closed | P1 | Security / Integration | Offer flow | AI calculator → offer → prefill flow и `group_id` lifecycle задокументированы | `tacticum_chat.php`, `tacticum_prefill.php`, `chat-agent.js`, `docs/workflow/chat-offer-contract.md` | Риск поломки при изменении AI payload или свойств инфоблока снижен, QA получил smoke cases | Поддерживать contract при изменениях chat/prefill/offer |
| PG-004 | closed | P2 | Full Feature | SEO/content | Базовые `description`, canonical и OpenGraph добавлены на публичные страницы | `tacticum_apply_seo_defaults()`, public pages, `policies/index.php` | Сниппеты, шаринг и canonical policy приведены к единому baseline; follow-up offer detail ЧПУ/indexability ведётся в `SEO-001` | Post-deploy smoke: проверить rendered head и отсутствие дублей meta |
| PG-005 | closed | P2 | Fast Fix | Legal/consent | Consent-ссылки активных форм ведут на `/policies/` и открываются безопасно | `index.php`, `calculator/index.php`, `contacts/index.php`, `offer/template.php`, `footer.php`, `forms.js` | Legal UX consistency для публичных форм восстановлена | Поддерживать правило в Lead Form Contract и PR checks |
| PG-006 | closed | P2 | Full Feature | Analytics | Добавлена taxonomy и client-side events для форм, AI chat, prefill, Telegram resolver | `analytics.js`, `forms.js`, `chat-agent.js`, `tg-link-resolver.js`, `docs/workflow/analytics-events.md` | Conversion funnel можно мерить без отправки PII в аналитику | Post-deploy smoke: подтвердить goals в Yandex.Metrika/tag manager |
| PG-007 | closed | P2 | Full Feature | Content model | Ключи инфоблоков используются публичными страницами через config helper | `local/php_interface/include/tacticum_config.example.php`, `docs/adr/ADR-003-iblock-ids.md`, `tacticum_iblock_id()`, public `IncludeComponent` | Переносимость публичных страниц повышена, numeric public `IBLOCK_ID` устранены | Поддерживать правило в PR checks и не добавлять новые hardcoded IDs |
| PG-008 | closed | P2 | Full Feature | Layout consistency | Повторяемые CTA/form секции вынесены из публичных страниц в template includes с явными вариантами | `local/templates/tacticum/include/personal-offer-cta.php`, `local/templates/tacticum/include/project-discussion-cta.php`, `index.php`, `calculator/index.php`, `price/index.php`, `contacts/index.php`, `about/index.php`, `services/index.php` | UX-правки повторяемых CTA теперь делаются в одном месте; страницы передают только form config | Поддерживать PR guards и не копировать CTA markup обратно в public pages |
| PG-009 | closed | P1 | Full Feature | Price staff order | Заказ специалистов на `/price/` переведён из одиночной роли в состав multi-staff заявки с количеством по ролям, суммарной ставкой, persistent summary, быстрыми пресетами команды, segmented-выбором уровня, порядком уровней `Junior -> Middle -> Senior -> Lead`, фильтр-счётчиком, empty state, гибкими пресетами срока, календарём точной даты окончания работ и ориентировочным месячным бюджетом | `news.list/price/template.php`, `news.list/price/result_modifier.php`, `news.list/price/script.js`, `news.list/price/style.css`, `tacticum_sale_staff.php`, `lead-form-contract.md`, `price-staff-order-plan.md` | Пользователь может быстрее подобрать уровень/роль и собрать команду без повторных отдельных заявок; backend сохраняет rich `workers[]`, `team_preset`, `monthly_budget_estimate`, `end_date` и legacy fallback | Post-deploy `npm run browser:smoke`; отдельный ручной valid-submit smoke без боевого лида или на staging |
| PG-010 | closed | P1 | Fast Fix | Price AI calculator | Light chat на `/price/` больше не растягивает страницу при новых сообщениях; сообщения прокручиваются внутри блока | `styles/global.css`, `tools/visual-smoke.mjs` | Пользователь сохраняет контекст секции, форма ввода остаётся доступной, страница не получает резкий вертикальный рост | Post-deploy `browser:smoke` по `/price/` и `/calculator/` |

## Technology Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| TG-001 | closed | P0 | Security / Integration | HTTPS/config | Runtime REST не имеет HTTP fallback, требует HTTPS и production config health подтверждён | `rest_helpers.php`, REST endpoints, `local/php_interface/include/tacticum_config.example.php`, `.github/workflows/deploy.yml`, `GET https://tacticum.ru/local/rest/health_config.php` 21.05.2026 вернул `success: true` | Runtime защищён от silent HTTP fallback; deploy smoke продолжит ловить невалидный server config до пользовательских 500 | Поддерживать deploy health smoke и синхронизировать server `tacticum_config.php` при новых config keys |
| TG-002 | closed | P1 | Security / Integration | Config/iblocks | `init.php` и публичные `IncludeComponent` используют config registry для ID инфоблоков | `local/php_interface/init.php`, `docs/adr/ADR-003-iblock-ids.md`, public pages | Backend callbacks и публичные страницы стали переносимее между окружениями | Поддерживать `tacticum_iblock_id()` / `tacticum_rest_get_iblock_id()` как стандарт |
| TG-003 | closed | P1 | Security / Integration | REST consistency | Все outbound AI/Telegram requests в `/local/rest` проходят через shared helper; response shapes остаются доменными | `rest_helpers.php`, `tacticum_form.php`, `tacticum_chat.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `resolve_telegram_link.php` | Curl/timeout/TLS handling больше не расходится между endpoints | Отдельный будущий gap: унифицировать success-body contract, если потребуется продуктово |
| TG-004 | closed | P1 | Security / Integration | CSRF | `tacticum_rest_check_csrf()` требует явный token; chat/prefill/resolver frontend передаёт `BX.bitrix_sessid()` | `rest_helpers.php`, `index.php`, `calculator/index.php`, `price/index.php`, `tg-link-resolver.js` | CSRF модель приведена к явному Bitrix token для state-changing POST | Поддерживать правило в Lead Form Contract и PR checks |
| TG-005 | closed | P1 | Fast Fix | Logging/PII | Файловое runtime-логирование в `/local` и публичных скриптах удалено | `init.php`, `content_migrations.php`, `rest_helpers.php`, `tacticum_chat.php`, `tacticum_prefill.php`, `tacticum_sale_staff.php`, `resolve_telegram_link.php` | Payload/response/PII больше не попадают в файловые логи из кастомного runtime-кода | Поддерживать scan guard против `AddMessage2Log`, `error_log`, `file_put_contents`, `console.log/error/warn` в runtime-коде |
| TG-006 | closed | P1 | Full Feature | Frontend maintainability | Chat inline scripts/styles вынесены; устаревший offer inline script удалён | `chat-agent.js`, `header.php`, `offer/template.php` | Основной chat/prefill flow теперь тестируемый и не дублируется в публичных страницах | Поддерживать правило: новый JS/CSS только через assets/components |
| TG-007 | closed | P1 | Fast Fix | SEO/sitemap | Sitemap переведён на HTTPS, включает `/policies/` и систематизирован под Bitrix-generated static sitemap | `sitemap.xml`, `sitemap-basic-files.xml`, `/offer/sitemap.php` | SEO inconsistency устранена для sitemap; root index не зависит от перегенерации Bitrix | Поддерживать Bitrix sitemap settings и production `seo:check:prod` при новых публичных URL |
| TG-008 | closed | P2 | Security / Integration | Bitrix D7 | В `local/` и публичных страницах scan не нашёл `CModule::IncludeModule()`; touched code использует `Loader::includeModule()` | `rest_helpers.php`, `init.php`, `tacticum_prefill.php`, public pages | Новый runtime-код ближе к D7 best practice | Поддерживать `Loader::includeModule()` как стандарт |
| TG-009 | closed | P2 | Full Feature | API performance | GET API endpoints используют `Bitrix\Main\Data\Cache` через `tacticum_api_cached_payload(...)` | `local/api/*.php`, `rest_helpers.php`, `tacticum_config.example.php` | Повторные запросы к public API меньше нагружают инфоблоки; TTL управляется config | Post-deploy smoke: проверить first/second response и invalidate при изменении контента |
| TG-010 | closed | P2 | Security / Integration | REST method policy | Production prefill flow работает только через POST JSON; legacy GET fallback удалён | `tacticum_prefill.php`, `chat-agent.js`, `docs/workflow/chat-offer-contract.md` | Семантика production REST flow выровнена с остальными `/local/rest` endpoints и меньше раскрывает данные через URL | Поддерживать POST-only prefill в smoke |
| TG-011 | closed | P2 | Full Feature | Asset loading | Фактическая карта CSS/JS assets зафиксирована; optional assets подключаются через explicit page flags; static CSS build plan описан | `docs/workflow/asset-layout-audit.md`, `docs/workflow/static-css-build-plan.md`, `header.php`, public pages | Снижен риск случайного подключения assets на новых URL, следующий CSS cleanup имеет безопасный маршрут | Реализовать static CSS build plan после visual baseline |
| TG-012 | closed | P2 | Security / Integration | CI quality gates | Critical runtime checks стали blockers, public hardcoded iblocks остаются warning-level | `.github/workflows/pr-check.yml` | Нарушения REST/API conventions сложнее протащить в main | Поддерживать список checks при новых ADR |
| TG-013 | closed | P2 | Full Feature | Config validation | Добавлен `tacticum_rest_validate_config()` и same-origin health endpoint без вывода secret values | `rest_helpers.php`, `local/rest/health_config.php`, `tacticum_config.example.php` | Ошибки config можно проверить до пользовательского runtime 500 | Post-deploy smoke: `GET /local/rest/health_config.php` с allowed host/origin |
| TG-014 | closed | P2 | Fast Fix | Repository hygiene | `.DS_Store`/cache/backup/IDE files ignored; `tacticum_config.php` убран из Git index и остаётся локальным ignored config | `.gitignore`, `docs/workflow/repository-hygiene.md`, `git ls-files -c -i --exclude-standard` | Риск случайного commit local config/runtime мусора снижен | Поддерживать hygiene check перед PR |
| TG-015 | closed | P1 | Full Feature | CSS architecture | Browser Tailwind runtime удалён; static Tailwind bundle собирается через npm; dead CSS/JS artifacts удалены; `aiagents.css` слит в scoped-блок `styles/global.css`; generic Remixicon fallback удалён и `ri-*` классы валидируются; добавлен visual smoke и закрыты найденные overflow regressions | `package.json`, `tools/visual-smoke.mjs`, `tools/template-styles-retirement-check.mjs`, `local/templates/tacticum/assets/src/tailwind.css`, `local/templates/tacticum/tailwind.generated.css`, `styles/global.css`, `template_styles.css`, `header.php`, `.github/workflows/pr-check.yml`, `asset-layout-audit.md` | FOUC/no-JS риск снижен, CSS utilities воспроизводимы локально и в CI; CSS-local smoke поддерживает единый manual runtime CSS file `styles/global.css`, icon classes больше не маскируются generic fallback | После deploy выполнить `npm run visual:smoke` без CSS injection как обычный post-deploy gate |
| TG-016 | closed | P1 | Full Feature | Layout contracts | URL/text-based presentation и behavior убраны из затронутых мест | `faq/template.php`, `aiagents/index.php`, `modal.js`, `scroll.js`, `.github/workflows/pr-check.yml` | Компоненты меньше зависят от текущего URL и текста кнопок, риск случайного поведения ниже | Поддерживать explicit component params и data-* contracts в PR checks |
| TG-017 | closed | P1 | Full Feature | JS-owned markup | Specialist modal markup перенесён из JS в Bitrix component template; repeated CTA sections вынесены в template includes | `news.list/price/template.php`, `news.list/price/script.js`, `modal.js`, `local/templates/tacticum/include/personal-offer-cta.php`, `local/templates/tacticum/include/project-discussion-cta.php`, public pages | Заказ специалистов и CTA sections стали ближе к Bitrix component/include pattern; JS больше не владеет крупным modal markup | Поддерживать component/include pattern для новых повторяемых layout blocks |
| TG-018 | closed | P2 | Fast Fix | Inline markup cleanup | Убраны inline `onclick`, policy `<style>`/`style=`, progress inline widths и form UI inline style mutations; header logo получил `alt` | `about/index.php`, `services/index.php`, `policies/template.php`, `policies/style.css`, `index.php`, `forms.js`, `price/script.js`, `header.php` | HTML/JS стали семантичнее, меньше inline presentation/behavior | Поддерживать guard против inline `onclick`, policy inline styles и form inline style mutations |
| TG-019 | closed | P1 | Incident / Full Feature | Browser zero-error gate | Initial-load production smoke чистый; `/price/` regression подтверждён как mixed-rollout: legacy HTML без `data-price-*` + новый JS. Исправленный `news.list/price/script.js` поддерживает legacy/new selectors и fallback modal; обычный browser smoke без injection проходит | `tools/visual-smoke.mjs`, `news.list/price/template.php`, `news.list/price/script.js`, `local-public-browser-error-challenge.md`; manifests: `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-22T21-13-19-948Z/manifest.json`, `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-23T13-54-52-683Z/manifest.json` | Initial-load browser errors = 0 подтверждён; фильтры/search/order modal на `/price/` проходят desktop/mobile action smoke | Поддерживать `npm run browser:smoke` как post-deploy gate; после новых `/price/` правок проверять team presets/summary |
| TG-020 | closed | P1 | Security / Integration | REST bootstrap / PII logs | POST endpoints приведены к `validate_origin -> rate_limit -> method -> parse JSON -> CSRF`; файловое runtime-логирование удалено | `rest_helpers.php`, `tacticum_form.php`, `tacticum_chat.php`, `tacticum_prefill.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `resolve_telegram_link.php` | Большие/битые тела не читаются до guard; пользовательский текст и контакты не пишутся в кастомные файловые логи | Post-deploy REST smoke по chat/form/prefill/sale_staff |
| TG-021 | closed | P1 | Full Feature | Frontend data contracts | Light chat и price component переведены на явные `data-*` contracts: quick replies используют `data-message`, filters/modal/price state не зависят от presentation selectors | `chat-agent.js`, `calculator/index.php`, `price/index.php`, `news.list/price/template.php`, `news.list/price/script.js`, `.github/workflows/pr-check.yml` | Копирайтинг/CSS refactor больше не должен ломать отправку quick replies, фильтры ставок и модалку заказа специалиста | Поддерживать guard в `pr-check.yml`; покрыть клики отдельным action-smoke в TG-024 |
| TG-022 | closed | P2 | Security / Integration | Sale endpoint ownership | Upstream `/chat_agent/sale` call, group_id retry and upstream error handling centralized in shared sale adapter; `tacticum_offer.php` and `tacticum_sale.php` remain legacy aliases with preserved response shape, deprecation headers and Sprint 09 sunset matrix | `rest_helpers.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_form.php`, `lead-form-contract.md`, `current-state.md`, `sprints/2026-05-23-sprint-09-overall-gap-closure.md`, `sprints/2026-05-23-sprint-09-sale-sunset-upstream.md` | Будущие изменения sale upstream/retry делаются в одном helper; публичные формы продолжают использовать `tacticum_form.php`, staff-order — `tacticum_sale_staff.php`; внешние consumers видят lifecycle сигнал | Выполнить Sprint 09 matrix: inventory до `30.06.2026`, migration до `31.08.2026`, final alias mode до `30.09.2026` |
| TG-023 | closed | P2 | Full Feature | Inline/vendor assets / CSP | Yandex Maps constructor вынесен в explicit `yandex_map` asset; Metrika вынесена из inline script в centralized template asset `js/metrika.js`; noscript pixel использует CSS class | `contacts/index.php`, `header.php`, `js/yandex-map.js`, `js/metrika.js`, `styles/global.css`, `asset-layout-audit.md` | Карта и Метрика больше не живут inline в public page/header script block; будущий CSP проще строить вокруг `self` и vendor domains | При введении CSP явно разрешить `https://mc.yandex.ru` и проверить цели Метрики после deploy |
| TG-024 | closed | P2 | Full Feature | Browser action smoke | Добавлен `browser:smoke` поверх `visual:smoke`: non-network actions кликают меню, contact modal, empty form validation, empty chat send, price filters/search/empty-state/level и specialist modal | `tools/visual-smoke.mjs`, `package.json`, `post-deploy-smoke.md`, `local-public-browser-error-challenge.md` | Ошибки обработчиков теперь попадают в browser gate без создания лидов и содержательных AI-запросов | Deploy workflow запускает `browser:smoke`; реальные upstream success-flow по-прежнему проверять ручным/стейдж smoke |
| TG-025 | closed | P2 | Fast Fix | Agent instruction drift | Agent docs обновлены под static Tailwind и shared REST bootstrap | `.github/copilot-instructions.md`, `.github/agents/frontend-dev.md`, `.github/agents/backend-dev.md`, `.github/agents/designer.md`, `.github/agents/seo.md` | Новые агенты с меньшей вероятностью вернут удалённые CSS/JS artifacts или старый endpoint bootstrap | Поддерживать `.github/*` при изменении workflow docs |
| TG-026 | closed | P1 | Full Feature | Iblock content output | Публичные templates инфоблоков и GET API получили общий decode/sanitize path для повторно закодированных HTML entities | `content_helpers.php`, `init.php`, `rest_helpers.php`, `news.list/*/template.php`, `news.detail/*/template.php` | `&nbsp;`, `&amp;nbsp;` и похожие служебные последовательности больше не должны попадать в пользовательский интерфейс из контента инфоблоков | После deploy проверить FAQ/cases/services/offer/policies и выполнить visual smoke без injection |
| TG-027 | closed | P1 | Security / Integration | Deploy smoke gate | Post-deploy visual/browser smoke встроен в `deploy.yml`; runner ищет Chrome/Chromium на macOS/Linux; добавлены production smoke npm aliases | `.github/workflows/deploy.yml`, `tools/visual-smoke.mjs`, `package.json`, `post-deploy-smoke.md` | Релиз с browser runtime errors, broken images, horizontal overflow или сломанными `/price/` actions не должен пройти deploy gate | Следить за длительностью deploy; если появится staging environment, параметризовать base URL |
| TG-028 | closed | P2 | Full Feature | CSS retirement governance | Legacy `template_styles.css` выведен в comment-only shim: active CSS перенесён в `styles/global.css`, подключён через `Asset`, добавлен `template-styles:check` и CI/deploy guard; отдельные template-level CSS и generic icon fallbacks запрещены | `template-styles-retirement-plan.md`, `static-css-build-plan.md`, `asset-layout-audit.md`, `styles/global.css`, `template_styles.css`, `tools/template-styles-retirement-check.mjs`, `.github/workflows/pr-check.yml` | Active CSS больше не живёт в implicit Bitrix template file; generated utilities не дублируются; возврат правил в shim и маскировка битых иконок блокируются автоматикой | Дальнейший cleanup — component/page extraction из `styles/global.css` малыми партиями после чистого post-deploy smoke |

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
