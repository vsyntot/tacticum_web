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
| `health_config.php` | Проверка обязательной конфигурации | GET, origin/rate; возвращает только keys/codes ошибок, без значений secret/config; scopes включают `security.csp_mode` |

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

Homepage ecosystem MVP 01.06.2026: главная переупакована из general AI/delivery router в ecosystem router. Первый экран теперь показывает `Platform`, `Agents`, `Dev`, `Forum`, chat intro объясняет выбор продуктового входа, ниже добавлена карта `Platform core -> Agents/Dev/Forum`. Текущий commercial layer сохранён отдельным блоком: `/offer/`, `/services/`, `/price/`, `/aiagents/`. `home-cta` продолжает использовать `tacticum:lead.cta`, но контекст уточнён как `lead_page_role=ecosystem-router`, `lead_product=ecosystem`, `lead_scenario=product-routing`.

Services delivery layer MVP 01.06.2026: `/services/` сохранён как страница внедрения, но получил product-delivery блок для `Platform assessment`, `Agents pilot`, `Dev workflow`, `Forum launch`. Текущие entry cards `/offer/`, `/price/`, `/calculator/`, content list услуг, process, cases, FAQ и `services-cta` сохранены. Контекст `services-cta` дополнен `lead_product=ecosystem`, `lead_scenario=product-delivery` без изменения endpoint/upstream behavior.

Estimate/proof product context MVP 01.06.2026: `/calculator/` получил product-aware estimate paths для `Platform`, `Agents`, `Dev`, `Forum`, обновленные quick replies и контекст `calculator-cta` (`lead_product=ecosystem`, `lead_scenario=product-estimate`). `/offer/` catalog получил proof-layer блок, который связывает примеры расчетов с продуктовой линейкой, а offer detail получил product relation block и `lead_product=ecosystem` в существующей форме `offer-cta`. Offer filters, pagination, list/detail route behavior, canonical/noindex decisions and chat-to-lead contract не менялись.

Price product team context MVP 01.06.2026: `/price/` сохранён как team/staff configurator, но получил блок product workstreams (`Platform team`, `Agents pilot`, `Dev workflow`, `Forum launch`), обновленные light-chat quick replies и контекст `price-cta` (`lead_product=ecosystem`, `lead_scenario=product-team`). Сложный `price-specialist` modal, `workers_json`, team presets, `news.list/price/script.js` and `/local/rest/tacticum_sale_staff.php` не менялись.

AIAgents compatibility bridge 01.06.2026: `/aiagents/` сохранён как текущий Telegram-bot money/SEO entry без redirect/canonical изменений. В компонент `tacticum:aiagents` добавлен bridge к `/agents/`: Telegram-бот описан как первый сценарий Tacticum Agents, а пользователь может перейти на product page или остаться в demo/prototype flow. Форма `aiagents-inline` получила безопасный context `lead_product=agents`.

About vendor trust context 01.06.2026: `/about/` переупакован как страница доверия к product + delivery команде Tacticum. Hero и company copy связаны с `Platform / Agents / Dev / Forum`, добавлен vendor-trust блок про architecture/delivery/team/estimate. Неподтвержденный partner/status block с vendor logos заменён на безопасные технологические контуры без claims о партнёрствах и сертификациях. `about-cta` получил `lead_product=ecosystem`.

Contacts routing context 01.06.2026: `/contacts/` сохранён как operational contact/legal page с прежними телефоном, email, юридическим адресом, реквизитами и Yandex map widget. Добавлен routing block для next step: продуктовый пилот, внедрение, оценка проекта, команда. `contacts-cta` получил `lead_product=ecosystem`, `lead_scenario=contact-routing` без изменения form endpoint.

Product FAQ hardening 01.06.2026: `/platform/`, `/agents/`, `/dev/`, `/forum/` получили static product FAQ через общий renderer `local/php_interface/include/product_page.php`. Интерактив раскрытия использует существующий asset `faq.js`, подключенный page property `tacticum_page_assets=faq`; новых JS/CSS и backend contracts не добавлено. FAQ copy объясняет pilot/discovery/deployment boundaries и не публикует неподтвержденные registry, partner, benchmark или guarantee claims. Static FAQ items теперь также попадают в product `FAQPage` JSON-LD из того же page data array, который рендерит HTML.

Product-first release hardening 01.06.2026: добавлены draft sign-off `docs/workflow/release-signoff-2026-06-01-product-first.draft.json` и rollback runbook `docs/workflow/product-first-release-rollback-runbook.md`. Draft фиксирует pending gates для deploy smoke, rendered SEO, CSS/JS e2e, manual success-flow, Metrika and Bitrix admin; config, legacy sunset and staff-sale upstream помечены not applicable для этого scope, потому что config keys, sale aliases and staff upstream contract не менялись.

Product-first CI/deploy coverage 01.06.2026: `.github/workflows/pr-check.yml` and `.github/workflows/deploy.yml` теперь считают `/platform/`, `/agents/`, `/dev/`, `/forum/` публичными разделами для PHP lint, convention scans and rsync deploy. `tools/seo-check.mjs` проверяет canonical paths, static sitemap expectations and product navigation/footer links for product URLs. `tools/visual-smoke.mjs` включает product pages в default rendered smoke, проверяет presence product links in rendered navigation, rendered `SoftwareApplication` + `FAQPage` JSON-LD on product URLs under `TACTICUM_EXPECT_SEO_HEAD=1`, and required FAQ toggle action on product pages during action smoke. Deploy lifecycle guard показывает старый external хвост через `npm run gaps:known` и валидирует переносимый product-first draft sign-off.

Product CTA scenario qualification 01.06.2026: `local/components/tacticum/lead.cta/` получил optional `SCENARIO_OPTIONS` и рендерит controlled select `lead_scenario` без новых JS/CSS. Product pages передают page-specific варианты следующего шага: Platform assessment/pilot/deployment readiness, Agents scenario/RAG/rollout, Dev workflow/gates/design-system guardrails, Forum flow/scenario+LLM/support analytics. `local/rest/tacticum_form.php` переводит известные scenario slugs в человекочитаемые подписи внутри upstream `task`, а `tools/seo-check.mjs` фиксирует, что product pages сохраняют scenario qualification через общий renderer/component.

Product rollout delivery model 01.06.2026: общий renderer `local/php_interface/include/product_page.php` получил reusable rollout block для product pages. `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь показывают безопасный путь внедрения: assessment/discovery, ограниченный пилот, согласование deployment/integration/workflow, затем production/rollout/support decision. Блок не публикует pricing, registry, FSTEC/FSB, ПАК, гарантии или SLA tiers; `seo:check` закрепляет наличие rollout model на product pages.

Product proof readiness model 01.06.2026: product renderer получил reusable proof readiness block. `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь показывают, какие артефакты проверяются на пилоте: карта переиспользуемых слоёв, контрольные вопросы, workflow trace, карта обращений, checkpoints and rollout roadmap. Это не публичные metrics/cases/logos: блок описывает предмет проверки и evidence backlog, а `tools/seo-check.mjs` закрепляет наличие proof readiness items на product pages.

Product structured data 01.06.2026: `/platform/`, `/agents/`, `/dev/`, `/forum/` передают page-specific schema в `tacticum_apply_seo_defaults(...)` через `tacticum_product_page_schema(...)`. Schema строится из единого `$tacticumProductPage` и включает минимальный claim-safe `SoftwareApplication` (`name`, `applicationCategory`, `operatingSystem=Web`, `url`, `description`, `provider`, `isPartOf`) и `FAQPage` для реально рендеримого static FAQ. `offers`, `price`, `review`, `aggregateRating` and customer proof fields не добавляются; `seo:check` guard блокирует выпадение schema, рассинхрон page data/schema/render and risky commercial schema fields, `visual-smoke` rendered SEO gate проверяет эти schema на production/staging HTML, а `release:signoff:self-test` содержит негативный кейс на `/platform/` SEO manifest без `productSchemaSummary`.

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
- запускает `npm ci`, lifecycle guards `css:check` / `template-styles:check`, `npm run visual:smoke` и `npm run browser:smoke` против `https://tacticum.ru`; visual smoke в deploy включает `TACTICUM_EXPECT_SEO_HEAD=1` и проверяет title/description/canonical/OpenGraph/Twitter/JSON-LD/H1, rendered money/product navigation links, product `SoftwareApplication` + `FAQPage` schema on `/platform/`, `/agents/`, `/dev/`, `/forum/`, а `/price/` team presets обязательны через `TACTICUM_EXPECT_PRICE_TEAM_PRESETS=1`.
- запускает `npm run seo:check` до smoke и `npm run seo:check:prod` после browser smoke, чтобы поймать рассинхрон sitemap/robots/canonical, попадание `/404.php` в Bitrix-generated sitemap и отсутствие `X-Robots-Tag` у JSON endpoints.
- для ручной/PM проверки CSS/JS e2e readiness добавлены aggregate scripts `npm run e2e:css-js:prod` и `npm run e2e:css-js:local`; Sprint 10 использует их как единый browser/CSS/JS readiness gate.
- legacy sale aliases контролируются `npm run sale:sunset:check`; Sprint 09 фиксирует action matrix, Sprint 10 ведёт `docs/workflow/legacy-sale-alias-consumer-inventory.md` с repo scan evidence и внешним inventory по access logs/CRM до `30.06.2026`, migration до `31.08.2026` и final alias mode до `30.09.2026`.
- release evidence можно закрывать machine-readable JSON по `docs/workflow/release-signoff.example.json`; проверка `npm run release:signoff:check -- <file>` блокирует pending/missing evidence, unknown gates, placeholder/working-tree metadata, валидирует структуру ручных gates, CSS/JS e2e manifests and product rendered schema summary, отсекает PII-like evidence; draft-проверка требует `reason`, `due`, runbook и evidence template у pending gates; deploy lifecycle guard использует `npm run gaps:known` для текущего external хвоста и `release:signoff:draft-check` для переносимого product-first draft, а не strict example sign-off; `npm run release:product-first:prod-check` агрегирует product-first automated production checks after deploy/cache refresh; `npm run release:signoff:summary -- <file>` даёт PM/QA статус draft без чтения JSON; `npm run release:signoff:self-test` закрепляет негативные кейсы checker в PR/deploy lifecycle guard, включая missing product schema summary, `npm run gaps:known` показывает текущий известный хвост, а `docs/workflow/manual-release-gates-runbook.md` задаёт порядок закрытия ручных gates без PII.
- локальный `npm run dev:preflight` не блокирует работу без PHP CLI 8.4+, но явно сообщает degraded state; authoritative PHP syntax fallback — GitHub job `php-lint`, который устанавливает PHP 8.4 через `shivammathur/setup-php` и проверяет `local/`, корневые PHP и публичные разделы с `short_open_tag=1`.

Production smoke 21.05.2026: `GET https://tacticum.ru/local/rest/health_config.php` с `Origin: https://tacticum.ru` вернул `200` и `{"success":true,"code":"ok"}` по scopes `api`, `ai`, `telegram`, `offer`, `content`, `rest`.

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
