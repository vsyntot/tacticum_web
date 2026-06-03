# Current State — tacticum.ru

Дата аудита: 20.05.2026

## Краткое Резюме

Приложение — корпоративный сайт на 1C-Bitrix с кастомным шаблоном `local/templates/tacticum`, публичными страницами, инфоблоками для контента/офферов и набором кастомных REST/API endpoints для форм, чата и AI-интеграции.

Сильные стороны:

- кастомный код в основном изолирован в `local/`;
- ядро `bitrix/` не является рабочей зоной;
- есть ADR по REST, config, iblock IDs, PII masking;
- есть единый `rest_helpers.php` для CORS/rate limit/CSRF/config/curl/masking;
- формы унифицированы через `data-tacticum-form` и `forms.js`;
- публичные GET API используют TTL-кэш поверх инфоблоков;
- публичные страницы имеют базовый canonical/OpenGraph helper;
- формы, AI-chat, prefill и Telegram resolver отправляют безопасные analytics events без PII;
- CI уже проверяет PHP syntax и часть security conventions.

Основные риски:

- frontend-debts переведены в управляемый track: old generated Tailwind block удалён, активные legacy/global styles перенесены из `template_styles.css` в `styles/global.css`, CSS/JS readiness доступен через `npm run e2e:css-js:prod` и `npm run e2e:css-js:local`, `template_styles.css` контролируется `npm run template-styles:check`;
- repeated CTA/form sections на `/`, `/calculator/`, `/price/`, `/contacts/`, `/about/`, `/services/` вынесены в локальный компонент `tacticum:lead.cta` с явными page-specific params;
- production REST требует HTTPS URL внешних AI-сервисов; production health-check `GET /local/rest/health_config.php` подтверждён 21.05.2026, deploy health smoke остаётся обязательным guard;
- локальный `tacticum_config.php` хранится вне Git index и должен синхронизироваться с `tacticum_config.example.php` вручную на окружениях; example config проверяется `npm run config:check`;
- локальный PHP CLI у разработчика не считается гарантированным: `npm run dev:preflight` проверяет наличие PHP 8.4+ и запускает lint `local/`, корневых PHP и публичных разделов при доступности, а GitHub `php-lint` с PHP 8.4 остаётся обязательным CI fallback;
- продуктовые сценарии AI-чата/калькулятора/оффера требуют регулярного post-deploy smoke и manual/staging sign-off по `docs/workflow/release-signoff-gates.md`.

## Структура Приложения

| Зона | Файлы | Назначение |
|---|---|---|
| Публичные страницы | `index.php`, `platform/`, `agents/`, `dev/`, `forum/`, `about/`, `services/`, `price/`, `calculator/`, `offer/`, `aiagents/`, `contacts/`, `policies/` | Основные страницы сайта |
| GET API | `local/api/cases.php`, `faq.php`, `rates.php`, `services.php` | JSON-выдача активных элементов инфоблоков |
| POST REST | `local/rest/tacticum_form.php`, `tacticum_chat.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `tacticum_prefill.php`, `resolve_telegram_link.php` | Формы, чат, AI-service, prefill, Telegram resolver |
| Shared REST helpers | `local/rest/rest_helpers.php` | Config, CORS/origin, IP allowlist, rate limit, CSRF, curl defaults, masking |
| Config health | `local/rest/health_config.php` | Same-origin health-check config keys without secret values |
| Bitrix bootstrap / REST | `local/php_interface/init.php`, `local/php_interface/include/site_helpers.php`, `seo_helpers.php`, `component_helpers.php`, `calcrequests_rest.php` | Тонкий bootstrap, site/SEO/component helpers и методы `calcrequests.list` / `calcrequests.add` через `OnRestServiceBuildDescription` |
| Template | `local/templates/tacticum/header.php`, `footer.php`, `js/`, `styles/`, `components/bitrix/` | Активный шаблон сайта |
| Reusable local components | `local/components/tacticum/lead.cta/`, `faq.section/`, `chat.surface/`, `content.list/`, `content.detail/`, `contact.modal/`, `aiagents/`, `offer/`, `offer.catalog/` | Общие CTA/form sections, FAQ wrapper, chat surfaces, content list/detail wrappers, footer modal и section-level `/aiagents/` / `/offer/` |
| Content helpers | `local/php_interface/include/content_helpers.php` | Декодирование Bitrix HTML entities, escaping и sanitizer для публичного вывода инфоблоков |
| Offer section | `local/components/tacticum/offer/`, `local/components/tacticum/offer.catalog/`, `local/php_interface/include/offer_page.php`, `local/php_interface/include/offer_catalog.php`, `offer_catalog_cache.php`, `docs/adr/ADR-007-offer-section-component.md` | Кастомный section-level компонент `/offer/`: тонкий front controller, pre-header route/SEO helper, dispatch list/detail/404, catalog child component, `TacticumOfferCatalogService` + repository/cache layer, managed tag/cache invalidation и compatibility wrappers для поиска offer, фильтрации, ЧПУ-ссылок и пагинации |
| Frontend build | `package.json`, `package-lock.json`, `local/templates/tacticum/assets/src/tailwind.css`, `tailwind.generated.css` | Static Tailwind CSS сборка для шаблона |
| CI/CD | `.github/workflows/deploy.yml`, `pr-check.yml`, `sitemap.yml` | Lint, convention checks, deploy, sitemap validation |
| Architecture | `docs/adr/` | Принятые архитектурные решения |

## Инфоблоки

Задокументированные ключи в `tacticum_config.php` и ADR:

| Key | Prod ID | Назначение |
|---|---:|---|
| `cases` | 13 | Кейсы |
| `faq` | 10 | FAQ |
| `rates` | 11 | Тарифы |
| `services` | 12 | Услуги |
| `offer` | 5 | Коммерческие предложения / расчёты |
| `vacancies` | 7 | Вакансии |
| `feedback` | 9 | Отзывы / feedback |
| `team` | 18 | Команда |
| `policies` | 19 | Политики / legal контент |
| `aiagents` | 20 | AI agents |

Публичные страницы используют `tacticum_iblock_id('key')` для `IncludeComponent`; ключи берутся из config registry через `tacticum_rest_get_iblock_id()`.

## REST/API State

### GET API

Все GET endpoints используют общий `tacticum_api_bootstrap($action)`, который:

- валидирует origin;
- применяет rate limit;
- проверяет HTTP method `GET`;
- подключает `iblock`;
- получает ID инфоблока по ключу.

Payload строится через `tacticum_api_cached_payload(...)` с TTL из `tacticum_config.php`:

- `api.cache_ttl_default`;
- `api.cache_ttl[<action>]`.

Default TTL в example config: `300` секунд.

Endpoints:

| Endpoint | Iblock key | Возвращает |
|---|---|---|
| `/local/api/cases.php` | `cases` | `name`, sections, preview, detail, properties |
| `/local/api/faq.php` | `faq` | `question`, `answer`, sections, properties |
| `/local/api/rates.php` | `rates` | `name`, sections, properties |
| `/local/api/services.php` | `services` | `name`, preview, detail, properties |

Замечание: helper переведён на D7 `Loader::includeModule()` для подключения `iblock`.

### POST REST

| Endpoint | Назначение | Состояние |
|---|---|---|
| `tacticum_form.php` | Default endpoint публичных лид-форм | HTTPS URL через shared outbound helper, validation, без файлового runtime-логирования |
| `tacticum_chat.php` | AI chat | Origin/rate/явный CSRF, HTTPS URL через shared outbound helper, без файлового runtime-логирования |
| `tacticum_offer.php` | Legacy sale alias | Origin/rate/явный CSRF, preserved response shape; upstream call/retry через shared `tacticum_rest_submit_chat_agent_sale(...)`; `Deprecation`/`Sunset` headers указывают на `/local/rest/tacticum_form.php` |
| `tacticum_sale.php` | Legacy sale alias | Origin/rate/явный CSRF, preserved response shape; upstream call/retry через shared `tacticum_rest_submit_chat_agent_sale(...)`; `Deprecation`/`Sunset` headers указывают на `/local/rest/tacticum_form.php` |
| `tacticum_sale_staff.php` | Заказ специалистов | Доменный staff endpoint для `/price/`: rich `workers[]` payload + adapter в config-driven `ai.endpoint_paths.staff_sale`; outbound через shared helper |
| `tacticum_prefill.php` | Предзаполнение формы по `group_id` | POST JSON + явный `sessid`; GET не поддерживается; `Loader::includeModule`; без файлового runtime-логирования |
| `resolve_telegram_link.php` | Telegram link resolver | Origin/rate/явный CSRF, HTTPS URL через shared helper; без файлового runtime-логирования |
| `health_config.php` | Проверка обязательной конфигурации | GET, origin/rate; возвращает только keys/codes ошибок, без значений secret/config; scopes включают `security.csp_mode` и `products` registry/source/cache config |

Файловое runtime-логирование из `/local` и публичных PHP/JS-скриптов отключено: POST endpoints сохраняют прежние response contracts, но больше не вызывают `AddMessage2Log`, `error_log`, `file_put_contents` или console debug output.

JSON endpoints в `local/api/*.php` и доменных `local/rest/*.php` отправляют `X-Robots-Tag: noindex, nofollow` через `tacticum_rest_send_noindex_header()`, чтобы служебные ответы не попадали в поисковый индекс.

## Frontend State

### Template / Assets

`header.php` использует `Bitrix\Main\Page\Asset::getInstance()` для подключения:

- `menu.js`;
- `analytics.js`;
- `metrika.js`;
- `forms.js`;
- `chat-agent.js` условно через page property `tacticum_page_assets=chat`;
- `modal.js`;
- `scroll.js`;
- `tg-link-resolver.js`;
- `yandex-map.js` условно через page property `tacticum_page_assets`;
- `faq.js`, `charts.js` условно через page property `tacticum_page_assets`;
- `tailwind.generated.css`;
- `fonts/remixicon.min.css`;
- `styles/global.css`.

Browser Tailwind runtime `bundle.v3.4.16.js` и config `init.js` удалены после source/rendered asset inventory. Static utilities собираются командой `npm run css:build`, CI проверяет актуальность и cascade layer order через `npm run css:check`, а runtime CSS дополнительно парсится через `npm run css:syntax`. Active global/template CSS живёт в единственном manual runtime file `styles/global.css`, а `template_styles.css` оставлен comment-only Bitrix shim; `npm run template-styles:check` блокирует возврат активных правил в shim, generic Remixicon fallback и неизвестные `ri-*` классы в публичных страницах, локальных компонентах и шаблоне. First-party JS в `local/` и `tools/` проверяется через `npm run js:check`. `/aiagents/` больше не имеет отдельного CSS asset: его небольшой page-specific блок перенесён в `styles/global.css` и scoped через body class `tacticum-aiagents-page`. Для визуальной проверки добавлен `npm run visual:smoke`; перед deploy можно использовать `TACTICUM_VISUAL_INJECT_CSS`, после deploy workflow запускает smoke против production URL без injection. Для проверки обработчиков без создания лидов добавлен `npm run browser:console` (`TACTICUM_VISUAL_ACTIONS=1`, `TACTICUM_VISUAL_FAIL_ON_WARNINGS=1`), который блокирует browser errors и warnings. Для CSS retirement batch добавлены `npm run visual:smoke:css-local` и `npm run browser:console:css-local`, которые удаляют production CSS links и inject локальные CSS поверх production HTML.

После browser-error challenge 22.05.2026 `visual:smoke` также фиксирует `console.error`, page exceptions и network/resource errors. Фоновый Telegram resolver больше не должен вызывать `/local/rest/resolve_telegram_link.php` при initial page load; resolver включается только для ссылок с `data-tacticum-tg-resolve` и доступным `BX.bitrix_sessid()`. Production initial-load smoke 23.05.2026 прошёл без browser errors. `/price/` mixed-rollout regression устранён в `news.list/price/script.js`: скрипт поддерживает legacy/new selectors и fallback modal; обычный `npm run browser:smoke` без injection прошёл 23.05.2026.

`/contacts/` больше не зависит от Yandex Maps constructor script: карта рендерится через Yandex map widget iframe на объект `Тактикум` (`oid=243968538014`) с координатами `55.723957,37.503747` и ссылкой на карточку в Яндекс Картах. `БЦ Victory Park` показывается как навигационный ориентир, а юридический адрес `119285, г. Москва, ... Км Мжд Киевское 5-й, д. 1 стр. 1, помещ. 3/3` показывается текстом рядом как корректный адрес для документов, чтобы публичная карта, ориентир и реквизиты не конфликтовали. Опциональный asset `js/yandex-map.js` остаётся в template allowlist для будущих page-specific случаев, но текущая contact page его не подключает. Yandex.Metrika вынесена из inline script в centralized template asset `js/metrika.js`. Template по умолчанию отправляет transitional `Content-Security-Policy-Report-Only` header; `security.csp_mode=enforce` включает enforcing header только после report-only baseline, triage лишних источников и подтверждения карты/Метрики/post-deploy smoke.

Страницы объявляют page-specific assets до визуального пролога через split prolog и page property `tacticum_page_assets`, например:

- `['faq']` для главной, services, calculator;
- `['faq', 'charts']` для price;
- `tacticum_page_assets=faq` для offer и aiagents.

Dead page-specific CSS (`main.css`, `services.css`, `price.css`, `calculator.css`, `about.css`, `contacts.css`, `expertise.css`, `css2.css`, `aiagents.css`) удалены после source/rendered asset inventory и CSS consolidation. Approved file в `local/templates/tacticum/styles/` — только `global.css`, подключаемый как template asset. Старый generated Tailwind block удалён из `template_styles.css`; generated utilities должны жить только в `tailwind.generated.css`.

Template asset hygiene refresh 25.05.2026: пустая `local/templates/tacticum/include/` удалена и исключена из Tailwind source scan; `fonts/` содержит только runtime RemixIcon CSS/font binaries и `remixicon.css` для guard; неиспользуемый Pacifico, RemixIcon source/archive artifacts и dead image duplicates удалены; favicon/apple/android PNG соответствуют заявленным размерам.

FAQ presentation задаётся параметром wrapper-компонента `tacticum:faq.section` / `SECTION_CLASS`, а не текущим URL. FAQ-разделы на публичных страницах задаются semantic `SECTION_KEY`; компонент сначала ищет section по `CODE`, а numeric fallback допускается только через server config `content.faq_section_fallback_ids`, не через component code. `/aiagents/` явно передаёт `py-16 bg-gray-50`.

Повторяемые списки инфоблоков на публичных страницах (`cases`, `feedback`, `services`, `team`, `vacancies`, `rates`, `aiagents`) вызываются через wrapper-компонент `tacticum:content.list`, который нормализует общий boilerplate `bitrix:news.list` и позволяет page entries передавать только доменные параметры: `IBLOCK_KEY`, template, fields/properties, count и сортировку.

Статический detail-контент `/policies/` вызывается через wrapper-компонент `tacticum:content.detail`: публичная страница передаёт `IBLOCK_KEY=policies` и template, а numeric `ELEMENT_ID` не хардкодится в page entry.

Sprint 15 product marketing architecture: публичный сайт упакован в 4 коммерческих входа: рассчитать проект (`/offer/`, `/calculator/`), внедрить AI-решение (`/services/`), собрать команду (`/price/`), запустить AI-бота (`/aiagents/`). Главная работает как router этих входов; `/services/`, `/price/`, `/calculator/`, `/offer/` и `/aiagents/` получили page-specific promise, next-step CTA и безопасный proof copy без спорных claims вроде `98%`, `15+ лет` или “гарантия результата”. Меню `services/.left.menu.php` и footer labels отражают product ladder, при этом URL inventory и SEO guard по money pages сохранены.

Product layer MVP 01.06.2026: добавлен безопасный product-first слой поверх текущих коммерческих входов: `/platform/`, `/agents/`, `/dev/`, `/forum/`. Страницы используют общий renderer `local/php_interface/include/product_page.php`, текущий шаблон, `tacticum:lead.cta`, allowlisted `lead_*` context и optional product scenario select для `lead_scenario` без изменения REST/upstream contracts. Верхнее меню получило группу `Продукты` с дочерними product links через `platform/.left.menu.php`; footer разделён на `Продукты`, `Внедрение`, `Компания`. `/aiagents/` остаётся рабочим legacy/current AI-bot entry и не редиректится.

Homepage ecosystem MVP 01.06.2026: главная переупакована из general AI/delivery router в ecosystem router. Первый экран теперь показывает `Platform`, `Agents`, `Dev`, `Forum`, chat intro объясняет выбор продуктового входа, ниже добавлена карта `Platform core -> Agents/Dev/Forum` и product fit matrix "ситуация -> продукт -> стартовый шаг". Текущий commercial layer сохранён отдельным блоком: `/offer/`, `/services/`, `/price/`, `/aiagents/`. `home-cta` продолжает использовать `tacticum:lead.cta`, но контекст уточнён как `lead_page_role=ecosystem-router`, `lead_product=ecosystem`, `lead_scenario=product-routing`. `tools/seo-check.mjs` закрепляет наличие homepage fit matrix.

Services delivery layer MVP 01.06.2026: `/services/` сохранён как страница внедрения, но получил product-delivery блок для `Platform assessment`, `Agents pilot`, `Dev workflow`, `Forum launch`. Текущие entry cards `/offer/`, `/price/`, `/calculator/`, content list услуг, process, cases, FAQ и `services-cta` сохранены. Контекст `services-cta` дополнен `lead_product=ecosystem`, `lead_scenario=product-delivery` без изменения endpoint/upstream behavior.

Estimate/proof product context MVP 01.06.2026: `/calculator/` получил product-aware estimate paths для `Platform`, `Agents`, `Dev`, `Forum`, обновленные quick replies и контекст `calculator-cta` (`lead_product=ecosystem`, `lead_scenario=product-estimate`). `/offer/` catalog получил proof-layer блок, который связывает примеры расчетов с продуктовой линейкой, а offer detail получил product relation block и `lead_product=ecosystem` в существующей форме `offer-cta`. Offer filters, pagination, list/detail route behavior, canonical/noindex decisions and chat-to-lead contract не менялись.

Price product team context MVP 01.06.2026: `/price/` сохранён как team/staff configurator, но получил блок product workstreams (`Platform team`, `Agents pilot`, `Dev workflow`, `Forum launch`), обновленные light-chat quick replies и контекст `price-cta` (`lead_product=ecosystem`, `lead_scenario=product-team`). Сложный `price-specialist` modal, `workers_json`, team presets, `news.list/price/script.js` and `/local/rest/tacticum_sale_staff.php` не менялись.

AIAgents compatibility bridge 01.06.2026: `/aiagents/` сохранён как текущий Telegram-bot money/SEO entry без redirect/canonical изменений. В компонент `tacticum:aiagents` добавлен bridge к `/agents/`: Telegram-бот описан как первый сценарий Tacticum Agents, а пользователь может перейти на product page или остаться в demo/prototype flow. Форма `aiagents-inline` получила безопасный context `lead_product=agents`.

About vendor trust context 01.06.2026: `/about/` переупакован как страница доверия к product + delivery команде Tacticum. Hero и company copy связаны с `Platform / Agents / Dev / Forum`, добавлен vendor-trust блок про architecture/delivery/team/estimate. Неподтвержденный partner/status block с vendor logos заменён на безопасные технологические контуры без claims о партнёрствах и сертификациях. `about-cta` получил `lead_product=ecosystem`.

Contacts routing context 01.06.2026: `/contacts/` сохранён как operational contact/legal page с прежними телефоном, email, юридическим адресом, реквизитами и Yandex map widget. Добавлен routing block для next step: продуктовый пилот, внедрение, оценка проекта, команда. `contacts-cta` получил `lead_product=ecosystem`, `lead_scenario=contact-routing` без изменения form endpoint.

Product FAQ hardening 01.06.2026: `/platform/`, `/agents/`, `/dev/`, `/forum/` получили static product FAQ через общий renderer `local/php_interface/include/product_page.php`. Интерактив раскрытия использует существующий asset `faq.js`, подключенный page property `tacticum_page_assets=faq`; новых JS/CSS и backend contracts не добавлено. FAQ copy объясняет pilot/discovery/deployment boundaries и не публикует неподтвержденные registry, partner, benchmark или guarantee claims. Static FAQ items теперь также попадают в product `FAQPage` JSON-LD из того же page data array, который рендерит HTML.

Product-first release hardening 01.06.2026: добавлены draft sign-off `docs/workflow/release-signoff-2026-06-01-product-first.draft.json` и rollback runbook `docs/workflow/product-first-release-rollback-runbook.md`. Draft фиксирует pending gates для deploy smoke, rendered SEO, CSS/JS e2e, manual success-flow, Metrika and Bitrix admin; config, legacy sunset and staff-sale upstream помечены not applicable для этого scope, потому что config keys, sale aliases and staff upstream contract не менялись.

Product-first CI/deploy coverage 01.06.2026: `.github/workflows/pr-check.yml` and `.github/workflows/deploy.yml` теперь считают `/platform/`, `/agents/`, `/dev/`, `/forum/` публичными разделами для PHP lint, convention scans and rsync deploy. `tools/seo-check.mjs` проверяет canonical paths, static sitemap expectations and product navigation/footer links for product URLs. `tools/visual-smoke.mjs` включает product pages в default rendered smoke, проверяет presence product links in rendered navigation, rendered `SoftwareApplication` + `FAQPage` JSON-LD and rendered `data-product-block` inventory on product URLs under `TACTICUM_EXPECT_SEO_HEAD=1`, and required FAQ toggle action on product pages during action smoke. Deploy lifecycle guard показывает старый external хвост через `npm run gaps:known` и валидирует переносимый product-first draft sign-off.

Product CTA scenario qualification 01.06.2026: `local/components/tacticum/lead.cta/` получил optional `SCENARIO_OPTIONS` и рендерит controlled select `lead_scenario` без новых JS/CSS. Product pages передают page-specific варианты следующего шага: Platform assessment/pilot/deployment readiness, Agents scenario/RAG/rollout, Dev workflow/gates/design-system guardrails, Forum flow/scenario+LLM/support analytics. `local/rest/tacticum_form.php` строит internal canonical lead qualification profile (`product_interest`, `use_case_interest`, `deployment_interest`, funnel/CTA/budget/timeline fields) from existing `lead_*`, затем переводит известные scenario slugs в человекочитаемые подписи внутри upstream `task`. Top-level structured fields не отправляются во внешний sale endpoint до CRM/upstream approval; `tools/seo-check.mjs` фиксирует canonical profile and product pages scenario qualification.

Product funnel analytics 01.06.2026: `local/templates/tacticum/js/analytics.js` отправляет `tacticum_product_view` for `/`, `/platform/`, `/agents/`, `/dev/`, `/forum/` and `tacticum_product_cta_click` for product primary CTA links to `#contact-form`. `forms.js` adds product-specific mirrors for form submit/success/error: `tacticum_product_form_submit`, `tacticum_product_form_success`, `tacticum_product_form_error`. Params are allowlisted: `product`, `page_role`, controlled `scenario`, `form_id`, `endpoint`, status/code and `page_path`; no budget/timeline/offer title/industry/message/name/email/phone are sent to analytics. `tools/seo-check.mjs` guards product analytics event presence and controlled value allowlist.

Product block locator and preview workflow 01.06.2026: product renderer partials expose stable `data-product-block` markers for `hero`, `fit-guide`, `content-section`, `architecture`, `use-cases`, `comparison`, `procurement`, `rollout`, `proof`, `faq` and `lead-cta`. This gives designers, QA and LLM-assisted refactoring a machine-readable AS IS block map without changing visuals, CSS, JS or form behavior. `tools/seo-check.mjs` guards the source taxonomy, `tools/visual-smoke.mjs` records `productBlocks` / `productBlockErrors` in rendered smoke manifests, and release sign-off validation blocks product SEO evidence if a rendered product page misses a required block. Lightweight screenshot preview is available via `npm run product:block-previews` / `npm run product:block-previews:prod`; outputs include full screenshots, `manifest.json` and `product-blocks/*.png` per rendered block, documented in `docs/workflow/product-block-preview-workflow.md`.

Design token AS IS contract 01.06.2026: `docs/design-system-handoff/05-design-tokens-as-is.json` now separates implemented Tailwind tokens, observed manual CSS/JS token candidates and known drift (`#001F40` vs `#001F3F`, `#007bff` vs `#0066CC`). `npm run design:tokens:check` validates the JSON against `tailwind.css`, `global.css`, `forms.js` and `package.json`, and `docs/workflow/design-token-contract.md` documents the update workflow. This gives designers and frontend a checked AS IS token baseline; final TO BE token source of truth, naming and component specs remain open design/frontend decisions.

Component/state AS IS contract 02.06.2026: `docs/design-system-handoff/07-component-state-contract.json` now captures behavior-bearing components, preserved selectors, required states and migration policy for global navigation, contact modal, lead CTA forms, chat surface, FAQ accordion, `/price/` team builder and product page blocks. `npm run design:components:check` validates that the JSON still matches templates/JS/source files, while `docs/workflow/component-state-contract.md` documents update and migration rules. This gives Designer/Frontend/QA a checked baseline for TO BE component/state work; visual anatomy, state visuals, `/price/` mobile UX and chat/product proof component specs remain design decisions.

Design migration map 02.06.2026: `docs/design-system-handoff/08-as-is-to-be-migration-map.json` maps every checked AS IS component id from `07-component-state-contract.json` to a preliminary TO BE component name, migration type, risk level, gates, deliverables and open decisions. `npm run design:migration:check` validates coverage, allowed migration types, selector consistency and high-risk gates, while `docs/workflow/design-migration-map.md` documents the workflow. Current baseline recommends `visual-restyle` for navigation/modal/lead CTA/chat/FAQ, `contract-preserving-split` for `/price/` team builder and product page blocks, and tracks new proof/status, architecture diagram and procurement-doc request components in `toBeBacklog`.

Design handoff completeness 02.06.2026: `docs/design-system-handoff/09-to-be-design-work-order.md` now gives Designer + Frontend the implementation-ready design work order: required Figma/design deliverables, state matrix, migration decisions, red lines and acceptance criteria. `npm run design:handoff:check` aggregates token, component and migration checks, then validates package completeness for `01`-`09`, README references, workflow docs and npm scripts. This makes the AS IS/TO BE handoff package transferable to a designer or LLM without relying on implicit context; final Figma library, visual variants and approved TO BE decisions remain external design deliverables.

Product gap closure governance 02.06.2026: `docs/new-big-change/product-vision-handoff/15-gap-closure-master-plan.md` defines phased closure for known AS IS / TO BE product, CJM, UI, architecture, SEO and release gaps. `docs/new-big-change/product-vision-handoff/16-gap-closure-action-register.json` maps every non-closed gap from `14-gap-backlog-and-decision-register.md` to owner, closure mode, target checkpoint, next action and acceptance evidence; `npm run product:gaps:check` validates coverage and blocks silent loss of blocked/external gates. This closes the planning/governance layer only; PM/Sales/Legal/SEO/Designer/DevOps evidence gates remain external until their acceptance evidence exists.

Local gap decision baseline 02.06.2026: `docs/new-big-change/product-vision-handoff/17-local-gap-decision-briefs.md` gives first-pass review material for `PB-004`, `CJM-004`, `CJM-005`, `UI-004`, `UI-008` and `SEO-TOBE-005`: Dev workflows, CTA intent matrix, returning-lead journey, architecture diagram patterns, icon taxonomy and draft metadata. These gaps are intentionally not marked closed until owners approve the baseline and any implementation changes pass the relevant guards.

Phase 1 product decision baseline 02.06.2026: `docs/new-big-change/product-vision-handoff/18-phase-1-product-decision-review-pack.md` gives PM/Sales/Legal/Security/Architect/SEO/Content review tables for taxonomy, Platform triggers, Agents/Forum boundary, proof/evidence, regulatory/procurement wording, packaging, `/agents/` vs `/aiagents/`, fit guide, procurement journey and use-case pilotability. `16-gap-closure-action-register.json` now lists `17` and `18` as review artifacts; `npm run product:gaps:check` validates artifact existence and covered gap IDs. P0 proof/legal gaps remain blocked until external evidence and owner approval exist.

Phase 3 architecture/integration baseline 02.06.2026: `docs/new-big-change/product-vision-handoff/19-phase-3-architecture-integration-decision-pack.md` gives Architect/Dev/Content/Backend/PM/QA/Analytics review tables for product content ownership, product renderer/component boundary, lead qualification, CRM/upstream structured fields and product analytics/Metrika evidence. `16-gap-closure-action-register.json` now lists `19` as a review artifact for `ARCH-001`, `ARCH-002`, `ARCH-003`, `ARCH-004` and `CJM-006`; `npm run product:gaps:check` validates the artifact coverage. Recommended v1 path keeps Git-owned product data, PHP block partials, canonical lead profile with `task` fallback and current no-PII analytics events until owners approve ADR/Security/Integration changes.

Phase 4 SEO/content baseline 02.06.2026: `docs/new-big-change/product-vision-handoff/20-phase-4-seo-content-decision-pack.md` gives SEO/PM/Content/Sales/Dev/QA review tables for product SEO clusters, `/agents/` vs `/aiagents/` canonical/compatibility options, product proof/case taxonomy, final metadata approval and rendered SEO evidence. `16-gap-closure-action-register.json` now lists `20` as a review artifact for `SEO-TOBE-001`, `SEO-TOBE-002`, `SEO-TOBE-003` and `SEO-TOBE-005`; `npm run product:gaps:check` validates artifact coverage. Current technical baseline keeps product URLs self-canonical and `/aiagents/` as compatibility/money page until SEO approves a redirect/canonical change.

Phase 5 release/evidence baseline 02.06.2026: `docs/new-big-change/product-vision-handoff/21-phase-5-release-evidence-closure-pack.md` gives DevOps/QA/SEO/PM/Marketing/Backend/Admin closure rules for `REL-001` - `REL-006`, `ARCH-007` and `ARCH-008`: product-first deploy smoke, rendered SEO evidence, manual success-flow, Metrika, Bitrix admin, legacy alias inventory and staff/upstream recovery. `16-gap-closure-action-register.json` now lists `21` as a review artifact for those external gates; `npm run product:gaps:check` validates coverage. This does not close the gates: strict release sign-off, `gaps:known:strict`, no-PII evidence and external access remain required.

Phase 2 design-system approval baseline 02.06.2026: `docs/new-big-change/product-vision-handoff/22-phase-2-design-system-approval-pack.md` gives Designer/Frontend/QA/PM/Legal approval rules for `UI-001`, `UI-002`, `UI-003`, `UI-005`, `UI-006` and `UI-007`: TO BE token source, product storytelling component family, form/modal/CTA state matrix, proof/status UI, `/price/` mobile team builder and chat state spec. `16-gap-closure-action-register.json` now lists `22` as a review artifact for those UI gaps; `npm run product:gaps:check` validates coverage. This builds on checked AS IS handoff docs `05` / `07` / `08` / `09`; gaps remain non-closed until Figma/design/frontend/QA/legal approvals exist.

Accepted-risk monitoring baseline 02.06.2026: `docs/new-big-change/product-vision-handoff/23-accepted-risk-monitoring-pack.md` gives Security/SEO/PM monitoring rules for `ARCH-006` CSP enforce and `SEO-TOBE-004` industry/scenario noindex. `16-gap-closure-action-register.json` now lists `23` as a review artifact for those accepted-monitoring gaps; `npm run product:gaps:check` validates coverage. These risks stay accepted, not closed, until owners approve a status change.

Post-challenge detail package 02.06.2026: `docs/new-big-change/product-vision-handoff/24-post-challenge-gap-analysis.md` - `28-post-challenge-decision-backlog.md` document the latest product/technology challenge in a structured form: consolidated gap analysis, pilot-kit/CJM detail, UX/UI/design-system detail, architecture/components/stack detail and a prioritized decision backlog. This package refines existing gaps from `14-gap-backlog-and-decision-register.md`; it does not introduce new canonical gap IDs or close owner/evidence-dependent gaps.

Post-challenge sprint wave 02.06.2026: `docs/new-big-change/product-vision-handoff/sprints/sprint-09-product-taxonomy-claims-packaging.md` - `sprint-14-release-evidence-post-launch-governance.md` convert decision IDs `D-01` - `D-13` into detailed planning docs. Updated `sprints/README.md`, `00-sprint-roadmap.md`, `99-sprint-execution-board.md` and `99-gap-to-sprint-traceability.md` now show both baseline sprints `00-08` and post-challenge refinement sprints `09-14`.

Sprint 09 execution bundle 02.06.2026: `docs/new-big-change/product-vision-handoff/sprints/sprint-09-review-workbook.md` and `sprint-09-decision-records.md` provide a concrete review agenda, worksheets and draft decision records for taxonomy, Agents/Forum/`/aiagents/`, proof/claims and packaging decisions (`D-01` - `D-04`). These records are explicitly draft and do not close PM/Sales/Legal/Security/SEO-dependent gaps without owner approval.

Sprint 09 owner-review readiness 02.06.2026: `docs/new-big-change/product-vision-handoff/sprints/sprint-09-approval-request.md` and `sprint-09-evidence-intake.md` now make Sprint 09 transferable to owners. The sprint status is `ready-for-owner-review / external approvals pending`: local scaffolding is complete, but taxonomy, claims, packaging and `/aiagents/` SEO decisions remain unapproved until external owner responses and evidence are captured.

Sprint 10 owner-review readiness 02.06.2026: `docs/new-big-change/product-vision-handoff/sprints/sprint-10-review-workbook.md`, `sprint-10-pilot-kit-records.md`, `sprint-10-cjm-cta-records.md` and `sprint-10-approval-request.md` now make Sprint 10 transferable to PM/UX/Sales/Content owners. The sprint status is `ready-for-owner-review / Sprint 09 approvals pending`: local pilot kit, role path, CTA taxonomy and returning-lead scaffolding is complete, but owner approval and Sales routing confirmation are still required before product data or UI implementation changes.

Sprint 11 owner-review readiness 02.06.2026: `docs/new-big-change/product-vision-handoff/sprints/sprint-11-review-workbook.md`, `sprint-11-decision-records.md`, `sprint-11-state-matrix.md` and `sprint-11-approval-request.md` now make Sprint 11 transferable to Designer/Frontend/QA/PM/Legal/Architect owners. The sprint status is `ready-for-owner-review / Sprint 09-10 approvals pending`: local TO BE token, component family, state matrix, architecture diagram and proof/status scaffolding is complete, but Figma/design/frontend/QA/legal/architect approvals remain required before UI implementation starts.

Product Bitrix content foundation 02.06.2026: ADR-010 accepts Bitrix as target product content source of truth while preserving current Git-owned `product_data/*.php` as fallback and seed. `local/php_interface/include/product_content.php` adds `products.source=auto|bitrix|fallback` runtime selection; default `auto` reads Bitrix product content only when it passes minimum renderable validation, otherwise falls back to Git data. Bitrix product reads are cached in `/tacticum/product_content` with `products.cache_ttl`, cache keys tied to `products` / `product_blocks` / `product_use_cases` IDs and invalidation handlers on product iblock element/property changes. `health_config.php` now includes `products` scope and validates product iblock IDs, source mode and cache TTL without exposing config values. `tools/product-content-migration.php` provides dry-run/apply CLI bootstrap for `products`, `product_blocks`, `product_use_cases`, product relations on existing content iblocks and seed data from current product arrays. `tools/product-content-check.php` / `npm run product:content:check` now verify that Bitrix product records are minimum-renderable and report missing TO BE blocks, use cases and relation properties; strict mode is available through `npm run product:content:check:strict`. On 03.06.2026 target Bitrix/PHP environment checks passed in normal and strict modes: `products=#21`, `product_blocks=#22`, `product_use_cases=#23`, `source=auto`, `cache_ttl=300`, all four product pages resolve from Bitrix with three use cases and no missing TO BE blocks. Production rendered `npm run seo:smoke` also passed on 03.06.2026: product pages `/platform/`, `/agents/`, `/dev/`, `/forum/` are `seo=ok` and `blocks=ok` on desktop/mobile, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-03T06-07-04-201Z/manifest.json`. Product renderer now exposes `data-product-source`; `npm run product:source:smoke:prod` verifies rendered source through browser smoke where Chrome/Chromium is available, while `npm run product:source:http:prod` performs server-safe HTML verification without Chrome or `node_modules`. Production server attempt on 03.06.2026 showed that browser smoke failure is caused by missing Chrome executable, not missing `node_modules`; server-safe `npm run product:source:http:prod` passed the same day: `/platform/`, `/agents/`, `/dev/`, `/forum/` returned `source=bitrix` and 11 product blocks each. Remaining closure before optional `products.source=bitrix`: Bitrix admin/content review. Deploy automation remains intentionally deferred until this manual CLI path is confirmed.

Release public precheck 03.06.2026: `npm run release:public-precheck:prod` passed without creating leads. It confirmed `health_config` scopes `api, ai, telegram, offer, content, products, rest, security`, product source `bitrix` and 11 product blocks on `/platform/`, `/agents/`, `/dev/`, `/forum/`, public Metrika tag/pixel availability, unauthenticated `/bitrix/admin/` surface without 5xx and legacy alias `Deprecation`/`Sunset` headers. This is public precheck evidence only; `manual-success-flow`, `metrika-goals`, `bitrix-admin` authenticated smoke and `staff-sale-upstream` still require owner evidence.

Release evidence refresh 03.06.2026: regenerated production rendered SEO, warning-aware browser action, focused `/price/` and CSS-local visual/action manifests under `/tmp/tacticum-release-closure-2026-06-03/`. Updated `docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` to those live manifest paths; `npm run release:signoff:summary -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` and draft-check now pass again. `npm run release:public-precheck:prod` also passed on 03.06.2026. Remaining pending gates are manual/external only: `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream`.

Staff-sale upstream gate hardening 03.06.2026: added `tools/staff-sale-gate-helper.mjs` / `npm run staff:sale:gate-helper` to generate a controlled `/price/` staff-order payload, curl template and safe release evidence block without sending a request. Strict release sign-off now requires `team_preset`, positive `workers_count`, `monthly_budget_estimate_present=true`, `end_date_present=true` and `upstream_request_id`/`lead_id` for `staff-sale-upstream`. Browser-controlled `/price/` staff-order POST returned HTTP 200 `success=true` on 03.06.2026 for a controlled payload with `team_preset=mvp`, `workers_count=3`, monthly budget and exact end date. This narrows the gate to CRM/upstream confirmation with a safe lead/request ID; it does not close the gate until that evidence exists.

Legacy sale inventory automation 03.06.2026: added `tools/legacy-sale-access-log-inventory.mjs` and `npm run legacy:sale:inventory:logs` for aggregate-only production access log checks of `/local/rest/tacticum_offer.php` and `/local/rest/tacticum_sale.php`. The tool supports regular and `.gz` logs, date window `2026-05-24` - `2026-06-30` by default, source labels and JSON output; it discards IP, query strings, referrers, cookies and user-agent details. This closes the local tooling gap only: access-log report, CRM/upstream report and owner migration decisions remain external evidence.

Legacy sale interim access-log evidence 03.06.2026: production aggregate run for `2026-05-24` - `2026-06-03` scanned `79384` nginx access-log lines and found `0` hits for both `/local/rest/tacticum_offer.php` and `/local/rest/tacticum_sale.php`. This is a partial no-traffic signal only; the full-window access-log inventory must be repeated after `30.06.2026`, and CRM/upstream source reports remain pending.

Product fit guide implementation 01.06.2026: общий renderer `local/php_interface/include/product_page.php` получил reusable `fit_guide` block для product pages, а главная получила product fit matrix для первичного выбора. `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь показывают decision-support блок "подходит / не подходит / с чего начать", который помогает развести Platform, Agents, Dev and Forum до чтения подробных секций. Блоки не меняют forms/REST/analytics contracts и закреплены guard в `tools/seo-check.mjs`.

Product security/procurement path 01.06.2026: общий renderer `local/php_interface/include/product_page.php` получил reusable `procurement` block для product pages. `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь имеют safe-copy секцию для architecture/security/procurement review: контур данных, доступы, интеграции, журналы, handoff, quality gates and ownership. Блок ведёт в существующий CTA `#contact-form`, не добавляет downloads/endpoints/forms/analytics and не публикует claims про реестры, сертификацию, КИИ, SLA, ПАК, automation rate or guaranteed production readiness; `tools/seo-check.mjs` закрепляет наличие procurement block на product pages.

Product use-case anatomy 01.06.2026: общий renderer `local/php_interface/include/product_page.php` получил reusable `use_cases` block. `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь описывают по 3 pilotable use cases with trigger, owner, pilot input, pilot output and limitation. Это переводит сценарии из обычных feature cards в decision-support формат, но не добавляет новые CTA fields, forms, REST endpoints, analytics events or public metric claims; `tools/seo-check.mjs` закрепляет обязательные поля use-case anatomy на product pages.

Product comparison/boundary block 01.06.2026: общий renderer `local/php_interface/include/product_page.php` получил reusable `comparison` block для product pages. `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь объясняют, когда выбирать текущий продукт, соседний продукт или service/delivery вход. `Agents` явно сравнивается с `Forum` and `/aiagents/`, а `Forum` явно сравнивается с `Agents` and pure LLM bot category, чтобы снизить риск смешения внутренних ассистентов и клиентской диалоговой платформы. Блок не меняет URL/canonical/REST/forms/analytics; `tools/seo-check.mjs` закрепляет наличие comparison block and взаимные Agents/Forum links.

Product rollout delivery model 01.06.2026: общий renderer `local/php_interface/include/product_page.php` получил reusable rollout block для product pages. `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь показывают безопасный путь внедрения: assessment/discovery, ограниченный пилот, согласование deployment/integration/workflow, затем production/rollout/support decision. Блок не публикует pricing, registry, FSTEC/FSB, ПАК, гарантии или SLA tiers; `seo:check` закрепляет наличие rollout model на product pages.

Product proof readiness model 01.06.2026: product renderer получил reusable proof readiness block. `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь показывают, какие артефакты проверяются на пилоте: карта переиспользуемых слоёв, контрольные вопросы, workflow trace, карта обращений, checkpoints and rollout roadmap. Это не публичные metrics/cases/logos: блок описывает предмет проверки и evidence backlog, а `tools/seo-check.mjs` закрепляет наличие proof readiness items на product pages.

Product structured data 01.06.2026: `/platform/`, `/agents/`, `/dev/`, `/forum/` передают page-specific schema в `tacticum_apply_seo_defaults(...)` через `tacticum_product_page_schema(...)`. Schema строится из единого `$tacticumProductPage` и включает минимальный claim-safe `SoftwareApplication` (`name`, `applicationCategory`, `operatingSystem=Web`, `url`, `description`, `provider`, `isPartOf`) и `FAQPage` для реально рендеримого static FAQ. `offers`, `price`, `review`, `aggregateRating` and customer proof fields не добавляются; `seo:check` guard блокирует выпадение schema, рассинхрон page data/schema/render and risky commercial schema fields, `visual-smoke` rendered SEO gate проверяет эти schema на production/staging HTML, а `release:signoff:self-test` содержит негативный кейс на `/platform/` SEO manifest без `productSchemaSummary`.

Product data layer 01.06.2026: core product data для `/platform/`, `/agents/`, `/dev/`, `/forum/` вынесена из публичных page entries в `local/php_interface/include/product_data/*.php`. Публичные `index.php` остались orchestration files: split prolog, title/description/page assets, `$tacticumProductPage = tacticum_product_page_data('<product>')`, SEO/schema and shared render. `tacticum_product_page_data(...)` loads only allowlisted product data files, so HTML, CTA context, static FAQ, product schema, fit guide, procurement, use-case anatomy, comparison, rollout and proof readiness share one Git-reviewed source. Bitrix/hybrid content ownership for future proof/cases/FAQ remains an explicit architecture decision.

Product renderer boundary 01.06.2026: `local/php_interface/include/product_page.php` reduced to bootstrap/helpers/data/schema and explicit includes for `local/php_interface/include/product_page_blocks/*.php`. Visual render functions now live in block partials: common cards/fit guide, architecture, use cases, procurement, comparison, rollout, proof, FAQ and page orchestration. `tools/seo-check.mjs` validates that the bootstrap loads the block taxonomy and does not inline visual render functions. This is a first ARCH-002 slice; a later Bitrix local component or preview workflow remains optional and needs a separate decision.

Автоматическая проверка Sprint 15 прошла 25.05.2026: SEO/static guards, CSS build/check, Bitrix architecture check, browser smoke, price smoke, CSS-local visual/action smoke и production SEO check зелёные. PHP CLI локально недоступен, поэтому PHP lint остаётся CI/deploy fallback; production deploy и post-deploy smoke остаются release gates.
Final stabilization challenge 25.05.2026 зафиксирован в `docs/workflow/final-stabilization-challenge-gap-analysis-2026-05-25.md`. Sprint 16 закрыл локальные code/docs gaps по карте `/contacts/`, offer detail estimate contrast, calculator/price chat-to-lead handoff, CTA image cleanup, contact/legal hierarchy, proof matrix, industry/scenario SEO decision и CSP target-state decision. Production deploy/cache smoke, real success-flow/staff upstream и Metrika goals остаются внешними release gates и не считаются закрытыми без evidence.

Основной personal-offer CTA для `/`, `/calculator/`, `/price/`, `/contacts/` и project-discussion CTA для `/about/`, `/services/` отрисовываются через `local/components/tacticum/lead.cta/`. `/contacts/` использует явный вариант `glass`. Компонент поддерживает optional qualification fields `lead_budget` / `lead_timeline`, optional controlled scenario select `lead_scenario` and hidden `LEAD_CONTEXT`; backend `/local/rest/tacticum_form.php` добавляет allowlisted `lead_*` context в существующее поле `task`, не меняя upstream response shape. Footer contact modal отрисовывается через `local/components/tacticum/contact.modal/` и сохраняет DOM/JS contract `#tacticum-modal`, `#tacticum-modal-form`, `data-tacticum-form`. Страницы передают только page-specific `form_id`, HTML `id` формы, field prefix, variant, CTA copy и safe context.

Hero chat на главной и light chat surfaces на `/calculator/` и `/price/` отрисовываются через `local/components/tacticum/chat.surface/` и сохраняют явные `#main_chat`, `#aichat`, `data-tacticum-chat`, `data-chat-*` contracts; quick replies передают payload через `data-message`, а не через текст кнопки. Сообщения имеют общий CSS-ограничитель высоты и внутреннюю прокрутку `[data-chat-messages]`, чтобы новые ответы не растягивали всю секцию. Light chat на `/calculator/` и `/price/` после успешного AI-ответа может передать контекст в целевую CTA форму: используется существующий `group_id`/prefill contract, summary заполняет `message`, а analytics получает только boolean flags без текста пользователя. Тёмный calculator block на главной остаётся на legacy DOM contract `#chatMessages/#userMessage/#sendMessage`.

Specialist order modal для `/price/` находится в Bitrix component template `news.list/price/template.php`; component `script.js` управляет фильтрами, ценами, segmented-выбором уровня специалиста, счётчиком результатов, empty state, составом multi-staff заявки, пресетами срока, быстрыми пресетами команды, persistent summary, расчётом ориентировочного месячного бюджета и hidden fields через `data-price-*` contracts. Уровни сортируются в компонентном `result_modifier.php` в порядке `Junior -> Middle -> Senior -> Lead`. Frontend отправляет `workers_json`, `duration`, `endDate`, `team_preset`, `monthly_budget_estimate`, а `tacticum_sale_staff.php` сохраняет fallback по legacy `specialist/level/rate`.

Публичные component templates для инфоблоков используют `tacticum_escape_iblock_text(...)` / `tacticum_sanitize_iblock_html(...)`: данные сначала декодируются от повторных HTML entities (`&nbsp;`, `&amp;nbsp;`), затем экранируются как plain text или проходят Bitrix sanitizer для разрешённого HTML. GET API `tacticum_rest_html_to_text(...)` также декодирует entities повторно.

### Forms

Формы с `data-tacticum-form` найдены:

- main CTA: `index.php` через `tacticum:lead.cta`;
- about CTA: `about/index.php` через `tacticum:lead.cta`;
- services CTA: `services/index.php` через `tacticum:lead.cta`;
- calculator CTA: `calculator/index.php` через `tacticum:lead.cta`;
- price CTA: `price/index.php` через `tacticum:lead.cta`;
- contacts CTA: `contacts/index.php` через `tacticum:lead.cta` с `VARIANT=glass`;
- offer CTA: `local/templates/tacticum/components/bitrix/news.detail/offer/template.php`;
- aiagents inline: `local/components/tacticum/aiagents/templates/.default/template.php`;
- modal form: `footer.php`;
- specialist order: `news.list/price/template.php` + `news.list/price/script.js`.

Общий `forms.js`:

- валидирует `name`, `email`, `phone`, `message`;
- добавляет `page_url`;
- добавляет `sessid`, если доступен `BX.bitrix_sessid()`;
- добавляет `group_id` из `window.tacticum_offer_context`;
- отправляет формы в `/local/rest/tacticum_form.php` по умолчанию;
- поддерживает `data-endpoint` для доменных сценариев, например `/price/` staff-order;
- отправляет optional `lead_*` context из hidden/qualification fields; analytics по-прежнему не получает эти values;
- показывает toast и reset/close modal;
- отправляет события `tacticum_form_*` без PII.

### AI Chat

Production chat surfaces унифицированы через `local/templates/tacticum/js/chat-agent.js`, подключённый в `header.php` через `Bitrix\Main\Page\Asset` только при page asset `chat`; повторяемые hero/light DOM surfaces вынесены в `local/components/tacticum/chat.surface/`.

Покрытые поверхности:

- hero chat в `index.php`;
- calculator block в `index.php`;
- calculator chat в `calculator/index.php`;
- price chat в `price/index.php`.

REST contract `/local/rest/tacticum_chat.php` зафиксирован в `docs/workflow/chat-api-contract.md`: endpoint POST-only, `user_message` ограничен 2000 символами, `group_id` ограничен 64 символами, `startAgent` проходит allowlist.

`chat-agent.js` отправляет события `tacticum_chat_*` и `tacticum_prefill_*` без текста сообщений и пользовательских контактов.

## SEO State

Текущий `sitemap.xml` — repo-owned sitemap index, указывает на Bitrix-generated static sitemap `https://tacticum.ru/sitemap-basic-files.xml` и динамический custom sitemap `https://tacticum.ru/offer/sitemap.php`.

`sitemap-basic-files.xml` генерируется штатным механизмом Bitrix из файловой структуры и должен содержать публичные статические разделы:

- `/`
- `/about/`
- `/agents/`
- `/aiagents/`
- `/calculator/`
- `/contacts/`
- `/dev/`
- `/forum/`
- `/offer/`
- `/platform/`
- `/policies/`
- `/price/`
- `/services/`

Generated artifacts `sitemap-basic.xml`, `sitemap-basic-files.xml`, `sitemap-basic-iblock-*.xml` и legacy `sitemap-files.xml` не являются repo-owned файлами. В Git хранится только корневой `sitemap.xml`; `robots.txt` указывает именно на него. Bitrix-настройка sitemap должна оставлять выключенным автодобавление правила в `robots.txt` и не включать `/404.php` в файловую карту.

`/offer/sitemap.php` генерирует URL активных offer detail элементов с валидным `CODE` внутри `/offer/<ELEMENT_CODE>/` и дедуплицирует одинаковые canonical URL, если в старом контенте есть несколько активных элементов с одинаковым `CODE`.

`npm run seo:check` статически проверяет repo-owned `sitemap.xml`, `robots.txt` и canonical paths публичных страниц: HTTPS `loc`, отсутствие legacy `sitemap-files.xml` в root index, один `lastmod` на каждый `loc`, freshness от `2026-05-24` и `Sitemap: https://tacticum.ru/sitemap.xml`. `robots.txt` намеренно не закрывает `/local/templates/` и `/bitrix/cache/`, чтобы поисковики могли рендерить CSS/JS; для Яндекса добавлен `Clean-param` по tracking/cache-параметрам. Если локально есть generated `sitemap-basic-files.xml`, он тоже валидируется. `npm run seo:check:prod` дополнительно проверяет production `sitemap.xml`, `sitemap-basic-files.xml`, `X-Robots-Tag: noindex, nofollow` на JSON endpoints и отсутствие дублей в dynamic `/offer/sitemap.php`; production sitemap guard запрещает `/404.php`, `/bitrix/` и `/local/` в sitemap loc.

SEO/navigation decision: `/price/`, `/offer/`, `/calculator/` и `/aiagents/` остаются не отдельными top-level пунктами, а дочерними ссылками dropdown `Услуги` через `services/.left.menu.php`; это сохраняет короткий header и оставляет коммерческие URL в sitewide menu structure. Те же money pages закреплены в footer menu `.bottom.menu.php`, а `/offer/` дополнительно добавлен в блок `Наши услуги` как карточка `Расчет проекта`. `/offer/` в меню называется `Расчет проекта`: это коммерческая landing-страница с индексируемыми примерами расчётов, а `/calculator/` остаётся отдельным инструментом `ИИ-калькулятор`. Top/mobile menu components используют root `.top.menu.php`, `CHILD_MENU_TYPE=left` и `USE_EXT=N`, чтобы section-level menu files не заменяли верхний уровень на `/services/`. `npm run seo:check` блокирует выпадение этих ссылок из top/footer/services menu structures и запрещает относительные footer URLs для публичных money pages.

Публичные страницы задают `SetTitle(...)`, `description` и вызывают `tacticum_apply_seo_defaults(...)`, который добавляет:

- canonical;
- optional `robots`;
- `og:site_name`;
- `og:locale`;
- `og:type`;
- `og:url`;
- `og:title`;
- `og:description`, если задан description;
- `og:image`;
- `og:image:width`, `og:image:height`, `og:image:type`;
- Twitter Card meta;
- JSON-LD graph: `Organization`, `WebSite`, `BreadcrumbList` и page-specific schema через options helper; product pages use safe `SoftwareApplication` + rendered static `FAQPage` schema without offers/pricing/reviews/ratings.

Default social preview image: `local/templates/tacticum/images/og-default.jpg`, 1200x630. Страницы могут переопределять `image`, `image_width`, `image_height`, `image_type` через `tacticum_apply_seo_defaults(...)`; для fallback helper использует `og-default.jpg`.

FAQ JSON-LD включается только для страниц, где FAQ реально рендерится: `/`, `/services/`, `/price/`, `/calculator/`, `/aiagents/` через общий FAQ helper и `/platform/`, `/agents/`, `/dev/`, `/forum/` через static product FAQ из `tacticum_product_page_schema(...)`.

`/offer/` остаётся landing-входом в offer flow и работает как индексируемый hub-каталог примеров расчёта через кастомный section-level компонент `tacticum:offer`; архитектурная граница зафиксирована в `docs/adr/ADR-007-offer-section-component.md`. Публичная страница `offer/index.php` оставлена тонким front controller: она подключает `prolog_before.php`, вызывает `local/php_interface/include/offer_page.php` для route/redirect/SEO/template properties, затем подключает визуальный пролог через `prolog_after.php`, а весь render list/detail/404 делегирует компоненту. `/offer/` не задаёт template globals: `faq` asset и body class выставляются через Bitrix page properties `tacticum_page_assets` и `tacticum_body_class`, которые читает `local/templates/tacticum/header.php`. List mode внутри `tacticum:offer` вызывает child component `tacticum:offer.catalog`: hero, статистика, быстрые отраслевые/scenario chips, серверные фильтры по поиску/отрасли/типу задачи/бюджету/формату, сортировка и пагинация. Catalog data проходит через `TacticumOfferCatalogService`, `TacticumOfferCatalogRepository` и `TacticumOfferCatalogCache`; cache dir `/tacticum/offer_catalog` регистрирует managed tag `iblock_id_<offer_iblock_id>` и очищается после add/update/delete элементов offer-инфоблока и отдельных property-update событий. В Sprint 15 list/detail templates усиливают conversion bridge: карточки и detail CTA явно говорят, что пример не является финальной сметой, а форма `offer-cta` передаёт safe `lead_offer_code`, `lead_offer_title`, industry/scenario/budget/timeline context. Detail mode использует штатный `bitrix:news.detail` с шаблоном `offer`, чтобы сохранить Bitrix lifecycle для элемента. Фасеты и пагинация каталога используют зарезервированный namespace `/offer/catalog/...`, например `/offer/catalog/scenario/ai-kopaylot/page/2/`, чтобы не конфликтовать с detail URL `/offer/<ELEMENT_CODE>/`; search/sort остаются query-параметрами. Query/filtered состояния каталога получают canonical `/offer/` и `noindex,follow`, чтобы не плодить индексируемые дубли. Редирект со старых query-состояний каталога сохраняет Bitrix service-параметры вроде `clear_cache=Y`. Детальные offer pages считаются индексируемыми примерами расчёта и открываются по ЧПУ `/offer/<ELEMENT_CODE>/`; `CODE` для новых элементов формируется из `slug.title` и timestamp создания. Legacy `/offer/?ID=<valid>` должен отдавать 301 на канонический ЧПУ, а invalid ID/code - 404 с `noindex`.

Для массового наполнения индексируемых примеров расчета добавлен управляемый CLI-сидер `local/tools/seed_offer_examples.php`: он генерирует 1117 synthetic offer-запросов, берет инфоблок через key `offer`, по умолчанию работает в dry-run и пишет элементы только с явным `--apply`. Synthetic timestamp в `ELEMENT_CODE` и `DATE_ACTIVE_FROM` распределяется с 01.09.2022 по 24.05.2026. Runbook: `docs/workflow/offer-example-seed-runbook.md`.

Root `404.php` больше не использует `bitrix:main.map`: страница задаёт status 404, title `Страница не найдена - Тактикум`, robots meta через `tacticum_add_robots_meta(...)`, `X-Robots-Tag: noindex,nofollow`, один H1 и ссылки на ключевые разделы.

Post-deploy SEO smoke 24.05.2026: `npm run seo:smoke` прошёл по 9 публичным URL в desktop/mobile, все checks `seo=ok`, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T08-28-30-284Z/manifest.json`. Production checks подтвердили 404/noindex, valid offer detail self-canonical, invalid offer 404/noindex и `X-Robots-Tag` на JSON endpoints. Повторный `npm run seo:check:prod` после deploy dedupe fix прошёл; dynamic `/offer/sitemap.php` не содержит duplicate `<loc>`. Sprint 10 SEO-009 revalidation также прошёл `seo:check`, `seo:check:prod` и rendered `seo:smoke`, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T10-52-46-468Z/manifest.json`.

Детальные follow-up gaps по SEO зафиксированы в `docs/workflow/seo-gap-analysis.md`: `SEO-001` - `SEO-008` закрыты production evidence, `SEO-009` принят как navigation decision.

## CI/CD State

`deploy.yml`:

- lint PHP 8.4 по `local/`;
- rsync `local/`;
- rsync публичных разделов с `--delete`, чтобы удалённые repo-owned файлы не оставались stale на production;
- rsync корневых файлов;
- чистит `bitrix/managed_cache`, проектный cache, menu component cache, component HTML cache `bitrix/cache/s1/bitrix/news.list|news.detail`, composite HTML pages и CSS/JS asset cache активного шаблона.
- проверяет `https://tacticum.ru/local/rest/health_config.php` после deploy/cache clear.
- запускает `npm ci`, lifecycle guards `css:check` / `template-styles:check`, `npm run visual:smoke` и `npm run browser:smoke` против `https://tacticum.ru`; visual smoke в deploy включает `TACTICUM_EXPECT_SEO_HEAD=1` и проверяет title/description/canonical/OpenGraph/Twitter/JSON-LD/H1, rendered money/product navigation links, product `SoftwareApplication` + `FAQPage` schema and product `data-product-block` inventory on `/platform/`, `/agents/`, `/dev/`, `/forum/`, а `/price/` team presets обязательны через `TACTICUM_EXPECT_PRICE_TEAM_PRESETS=1`.
- запускает `npm run seo:check` до smoke и `npm run seo:check:prod` после browser smoke, чтобы поймать рассинхрон sitemap/robots/canonical, попадание `/404.php` в Bitrix-generated sitemap и отсутствие `X-Robots-Tag` у JSON endpoints.
- для ручной/PM проверки CSS/JS e2e readiness добавлены aggregate scripts `npm run e2e:css-js:prod` и `npm run e2e:css-js:local`; Sprint 10 использует их как единый browser/CSS/JS readiness gate.
- legacy sale aliases контролируются `npm run sale:sunset:check`; access-log inventory собирается агрегатно через `npm run legacy:sale:inventory:logs`; Sprint 09 фиксирует action matrix, Sprint 10 ведёт `docs/workflow/legacy-sale-alias-consumer-inventory.md` с repo scan evidence и внешним inventory по access logs/CRM до `30.06.2026`, migration до `31.08.2026` и final alias mode до `30.09.2026`.
- release evidence можно закрывать machine-readable JSON по `docs/workflow/release-signoff.example.json`; проверка `npm run release:signoff:check -- <file>` блокирует pending/missing evidence, unknown gates, placeholder/working-tree metadata, валидирует структуру ручных gates, CSS/JS e2e manifests and product rendered schema summary, отсекает PII-like evidence; draft-проверка требует `reason`, `due`, runbook и evidence template у pending gates; deploy lifecycle guard использует `npm run gaps:known` для текущего external хвоста и `release:signoff:draft-check` для переносимого product-first draft, а не strict example sign-off; `npm run release:product-first:prod-check` агрегирует product-first automated production checks after deploy/cache refresh; `npm run release:signoff:summary -- <file>` даёт PM/QA статус draft без чтения JSON; `npm run release:signoff:self-test` закрепляет негативные кейсы checker в PR/deploy lifecycle guard, включая missing product schema summary, `npm run gaps:known` показывает текущий известный хвост, а `docs/workflow/manual-release-gates-runbook.md` задаёт порядок закрытия ручных gates без PII.
- локальный `npm run dev:preflight` не блокирует работу без PHP CLI 8.4+, но явно сообщает degraded state; authoritative PHP syntax fallback — GitHub job `php-lint`, который устанавливает PHP 8.4 через `shivammathur/setup-php` и проверяет `local/`, корневые PHP и публичные разделы с `short_open_tag=1`.

Production smoke 21.05.2026: `GET https://tacticum.ru/local/rest/health_config.php` с `Origin: https://tacticum.ru` вернул `200` и `{"success":true,"code":"ok"}` по scopes `api`, `ai`, `telegram`, `offer`, `content`, `rest`. После добавления product content registry post-deploy health должен также вернуть scope `products`.

`pr-check.yml`:

- PHP syntax по `local/`, корневым PHP и публичным разделам с `short_open_tag=1`;
- `npm run seo:check` для sitemap/robots/canonical inventory;
- blocker при хардкоде iblock ID, HTTP fallback, файловом runtime-логировании и пропущенном bootstrap в изменённых runtime-файлах;
- warning при hardcoded `IBLOCK_ID` в новом/legacy-коде вне разрешённых runtime исключений;
- blocker при tracked ignored files, восстановлении legacy `chat.js`, восстановлении legacy Tailwind JS/dead page CSS artifacts, URL-substring asset routing в header, GET fallback в `tacticum_prefill.php`, direct curl вне `rest_helpers.php`;
- blocker при URL/text-based layout behavior, inline `onclick`, policy inline styles и JS-generated specialist modal markup;
- blocker при inline `<script>` в `header.php` и при удалении централизованного `js/metrika.js`;
- blocker при возврате active CSS/imports или generated Tailwind layer block в `template_styles.css`;
- blocker для изменений в `bitrix/`.

Gap: новые hardcoded `IBLOCK_ID` не допускаются; публичные страницы переведены на config helper, дальнейший scan нужен только для legacy-кода вне затронутого scope.

## Архитектурные Решения

Приняты ADR:

- ADR-001: REST endpoints как отдельные PHP-файлы.
- ADR-002: config через `tacticum_config.php`.
- ADR-003: ID инфоблоков через `tacticum_rest_get_iblock_id()`.
- ADR-004: PII masking до логирования; текущее кастомное runtime-логирование payload/response отключено.
- ADR-005: vendor analytics scripts подключаются как template/page/component assets; Yandex.Metrika вынесена в `js/metrika.js`, CSP rollout начинается с report-only header.
- ADR-006: AI sale endpoint paths задаются через config `ai.endpoint_paths.*`, host остаётся в HTTPS `AI_SERVICE_BASE_URL`; будущий compatible rich workers endpoint включается через `ai.endpoint_paths.staff_sale`.

Фактическое состояние runtime REST приведено ближе к ADR-003/ADR-004/HTTPS правилу, при этом файловое runtime-логирование из кастомного `/local` и публичного кода удалено. Основной CSS cleanup по static Tailwind bundle закрыт: stale CSS / legacy Tailwind JS artifacts классифицированы и удалены, добавлен visual smoke. Gap №8 довёл `template_styles.css` до comment-only shim и перенёс active global/template CSS в `styles/global.css`; после CSS-изменений остаётся обязательный post-deploy visual smoke.

## Текущее Резюме Здоровья

| Область | Состояние | Риск |
|---|---|---|
| Bitrix isolation | Хорошее: кастомный код в `local/`, ядро не рабочая зона | Низкий |
| REST bootstrap | Хорошее: pattern есть, outbound helper общий, response shapes оставлены доменными | Низкий/средний |
| Config discipline | Хорошее: config validation есть, local config вынесен из Git index, production health подтверждён, deploy проверяет health endpoint | Низкий |
| Frontend maintainability | Хорошее: chat/forms/assets, repeated CTA, light chat и price component contracts унифицированы; static Tailwind bundle, `styles/global.css`, browser-error smoke, non-network action-smoke, CSS replacement smoke, aggregate CSS/JS e2e scripts и CSP report-only есть; `template_styles.css` удерживается comment-only guard; `chat-agent.js` грузится только на chat pages через page asset `chat` | Низкий/средний |
| SEO | Среднее/хорошее: sitemap, description, canonical и OG добавлены; нужен post-deploy render check | Низкий/средний |
| CI/CD | Среднее/хорошее: runtime blockers и deploy health smoke есть, public hardcode warnings остаются | Средний |
| Product flows | Среднее/хорошее: лид-формы, AI-chat, prefill и staff-order имеют контракты и единые handlers; real success-flow закрывается staging/manual sign-off, автоматический deploy smoke покрывает non-network actions | Низкий/средний |
