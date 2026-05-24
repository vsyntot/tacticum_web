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
- repeated CTA/form sections на `/`, `/calculator/`, `/price/`, `/contacts/`, `/about/`, `/services/` вынесены в template includes с явными page-specific form config;
- production REST требует HTTPS URL внешних AI-сервисов; production health-check `GET /local/rest/health_config.php` подтверждён 21.05.2026, deploy health smoke остаётся обязательным guard;
- локальный `tacticum_config.php` хранится вне Git index и должен синхронизироваться с `tacticum_config.example.php` вручную на окружениях; example config проверяется `npm run config:check`;
- локальный PHP CLI у разработчика не считается гарантированным: `npm run dev:preflight` проверяет наличие PHP 8.4+ и запускает lint при доступности, а GitHub `php-lint` с PHP 8.4 остаётся обязательным CI fallback;
- продуктовые сценарии AI-чата/калькулятора/оффера требуют регулярного post-deploy smoke и manual/staging sign-off по `docs/workflow/release-signoff-gates.md`.

## Структура Приложения

| Зона | Файлы | Назначение |
|---|---|---|
| Публичные страницы | `index.php`, `about/`, `services/`, `price/`, `calculator/`, `offer/`, `aiagents/`, `contacts/`, `policies/` | Основные страницы сайта |
| GET API | `local/api/cases.php`, `faq.php`, `rates.php`, `services.php` | JSON-выдача активных элементов инфоблоков |
| POST REST | `local/rest/tacticum_form.php`, `tacticum_chat.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `tacticum_prefill.php`, `resolve_telegram_link.php` | Формы, чат, AI-service, prefill, Telegram resolver |
| Shared REST helpers | `local/rest/rest_helpers.php` | Config, CORS/origin, IP allowlist, rate limit, CSRF, curl defaults, masking |
| Config health | `local/rest/health_config.php` | Same-origin health-check config keys without secret values |
| Bitrix REST | `local/php_interface/init.php` | Методы `calcrequests.list` и `calcrequests.add` через `OnRestServiceBuildDescription` |
| Template | `local/templates/tacticum/header.php`, `footer.php`, `js/`, `styles/`, `components/bitrix/` | Активный шаблон сайта |
| Template includes | `local/templates/tacticum/include/personal-offer-cta.php`, `project-discussion-cta.php` | Общие CTA/form sections для ключевых публичных страниц |
| Content helpers | `local/php_interface/include/content_helpers.php` | Декодирование Bitrix HTML entities, escaping и sanitizer для публичного вывода инфоблоков |
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
- `chat-agent.js`;
- `modal.js`;
- `scroll.js`;
- `tg-link-resolver.js`;
- `yandex-map.js` условно через `TACTICUM_PAGE_ASSETS`;
- `faq.js`, `charts.js` условно через `TACTICUM_PAGE_ASSETS`;
- `tailwind.generated.css`;
- `fonts/remixicon.min.css`;
- `styles/global.css`.

Browser Tailwind runtime `bundle.v3.4.16.js` и config `init.js` удалены после source/rendered asset inventory. Static utilities собираются командой `npm run css:build`, CI проверяет актуальность и cascade layer order через `npm run css:check`. Active global/template CSS живёт в единственном manual runtime file `styles/global.css`, а `template_styles.css` оставлен comment-only Bitrix shim; `npm run template-styles:check` блокирует возврат активных правил в shim, generic Remixicon fallback и неизвестные `ri-*` классы. `/aiagents/` больше не имеет отдельного CSS asset: его небольшой page-specific блок перенесён в `styles/global.css` и scoped через body class `tacticum-aiagents-page`. Для визуальной проверки добавлен `npm run visual:smoke`; перед deploy можно использовать `TACTICUM_VISUAL_INJECT_CSS`, после deploy workflow запускает smoke против production URL без injection. Для проверки обработчиков без создания лидов добавлен `npm run browser:smoke` (`TACTICUM_VISUAL_ACTIONS=1`). Для CSS retirement batch добавлены `npm run visual:smoke:css-local` и `npm run browser:smoke:css-local`, которые удаляют production CSS links и inject локальные CSS поверх production HTML.

После browser-error challenge 22.05.2026 `visual:smoke` также фиксирует `console.error`, page exceptions и network/resource errors. Фоновый Telegram resolver больше не должен вызывать `/local/rest/resolve_telegram_link.php` при initial page load; resolver включается только для ссылок с `data-tacticum-tg-resolve` и доступным `BX.bitrix_sessid()`. Production initial-load smoke 23.05.2026 прошёл без browser errors. `/price/` mixed-rollout regression устранён в `news.list/price/script.js`: скрипт поддерживает legacy/new selectors и fallback modal; обычный `npm run browser:smoke` без injection прошёл 23.05.2026.

Yandex Maps constructor на `/contacts/` загружается через explicit asset `js/yandex-map.js` и контейнер `data-yandex-constructor-map`, а не через inline script в public page. Yandex.Metrika вынесена из inline script в centralized template asset `js/metrika.js`. Template по умолчанию отправляет transitional `Content-Security-Policy-Report-Only` header; `security.csp_mode=enforce` включает enforcing header только после report-only baseline, triage лишних источников и подтверждения карты/Метрики post-deploy smoke.

Страницы объявляют page-specific assets до `require bitrix/header.php`, например:

- `['faq']` для главной, services, calculator, offer;
- `['faq', 'charts']` для price;
- `['yandex_map']` для contacts;
- `['faq']` для aiagents.

Dead page-specific CSS (`main.css`, `services.css`, `price.css`, `calculator.css`, `about.css`, `contacts.css`, `expertise.css`, `css2.css`, `aiagents.css`) удалены после source/rendered asset inventory и CSS consolidation. Approved file в `local/templates/tacticum/styles/` — только `global.css`, подключаемый как template asset. Старый generated Tailwind block удалён из `template_styles.css`; generated utilities должны жить только в `tailwind.generated.css`.

FAQ presentation задаётся параметром компонента `SECTION_CLASS`, а не текущим URL. `/aiagents/` явно передаёт `py-16 bg-gray-50`.

Основной personal-offer CTA для `/`, `/calculator/`, `/price/`, `/contacts/` вынесен в `local/templates/tacticum/include/personal-offer-cta.php`. `/contacts/` использует явный вариант `glass`. Project-discussion CTA для `/about/` и `/services/` вынесен в `local/templates/tacticum/include/project-discussion-cta.php`. Страницы передают только page-specific `form_id`, HTML `id` формы, field prefix и variant.

Light chat surfaces на `/calculator/` и `/price/` размечены явными `data-tacticum-chat`, `data-chat-*` contracts; quick replies передают payload через `data-message`, а не через текст кнопки. Сообщения имеют общий CSS-ограничитель высоты и внутреннюю прокрутку `[data-chat-messages]`, чтобы новые ответы не растягивали всю секцию.

Specialist order modal для `/price/` находится в Bitrix component template `news.list/price/template.php`; component `script.js` управляет фильтрами, ценами, segmented-выбором уровня специалиста, счётчиком результатов, empty state, составом multi-staff заявки, пресетами срока, быстрыми пресетами команды, persistent summary, расчётом ориентировочного месячного бюджета и hidden fields через `data-price-*` contracts. Уровни сортируются в компонентном `result_modifier.php` в порядке `Junior -> Middle -> Senior -> Lead`. Frontend отправляет `workers_json`, `duration`, `endDate`, `team_preset`, `monthly_budget_estimate`, а `tacticum_sale_staff.php` сохраняет fallback по legacy `specialist/level/rate`.

Публичные component templates для инфоблоков используют `tacticum_escape_iblock_text(...)` / `tacticum_sanitize_iblock_html(...)`: данные сначала декодируются от повторных HTML entities (`&nbsp;`, `&amp;nbsp;`), затем экранируются как plain text или проходят Bitrix sanitizer для разрешённого HTML. GET API `tacticum_rest_html_to_text(...)` также декодирует entities повторно.

### Forms

Формы с `data-tacticum-form` найдены:

- main CTA: `index.php` через `include/personal-offer-cta.php`;
- about CTA: `about/index.php` через `include/project-discussion-cta.php`;
- services CTA: `services/index.php` через `include/project-discussion-cta.php`;
- calculator CTA: `calculator/index.php` через `include/personal-offer-cta.php`;
- price CTA: `price/index.php` через `include/personal-offer-cta.php`;
- contacts CTA: `contacts/index.php` через `include/personal-offer-cta.php` с `variant=glass`;
- offer CTA: `local/templates/tacticum/components/bitrix/news.detail/offer/template.php`;
- aiagents inline: `aiagents/index.php`;
- modal form: `footer.php`;
- specialist order: `news.list/price/template.php` + `news.list/price/script.js`.

Общий `forms.js`:

- валидирует `name`, `email`, `phone`, `message`;
- добавляет `page_url`;
- добавляет `sessid`, если доступен `BX.bitrix_sessid()`;
- добавляет `group_id` из `window.tacticum_offer_context`;
- отправляет формы в `/local/rest/tacticum_form.php` по умолчанию;
- поддерживает `data-endpoint` для доменных сценариев, например `/price/` staff-order;
- показывает toast и reset/close modal;
- отправляет события `tacticum_form_*` без PII.

### AI Chat

Production chat surfaces унифицированы через `local/templates/tacticum/js/chat-agent.js`, подключённый в `header.php` через `Bitrix\Main\Page\Asset`.

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
- `/aiagents/`
- `/calculator/`
- `/contacts/`
- `/offer/`
- `/policies/`
- `/price/`
- `/services/`

Generated artifacts `sitemap-basic.xml`, `sitemap-basic-files.xml`, `sitemap-basic-iblock-*.xml` и legacy `sitemap-files.xml` не являются repo-owned файлами. В Git хранится только корневой `sitemap.xml`; `robots.txt` указывает именно на него. Bitrix-настройка sitemap должна оставлять выключенным автодобавление правила в `robots.txt` и не включать `/404.php` в файловую карту.

`/offer/sitemap.php` генерирует URL активных offer detail элементов с валидным `CODE` внутри `/offer/<ELEMENT_CODE>/` и дедуплицирует одинаковые canonical URL, если в старом контенте есть несколько активных элементов с одинаковым `CODE`.

`npm run seo:check` статически проверяет repo-owned `sitemap.xml`, `robots.txt` и canonical paths публичных страниц: HTTPS `loc`, отсутствие legacy `sitemap-files.xml` в root index, один `lastmod` на каждый `loc`, freshness от `2026-05-24` и `Sitemap: https://tacticum.ru/sitemap.xml`. Если локально есть generated `sitemap-basic-files.xml`, он тоже валидируется. `npm run seo:check:prod` дополнительно проверяет production `sitemap.xml`, `sitemap-basic-files.xml`, `X-Robots-Tag: noindex, nofollow` на JSON endpoints и отсутствие дублей в dynamic `/offer/sitemap.php`; production sitemap guard запрещает `/404.php`, `/bitrix/` и `/local/` в sitemap loc.

SEO/navigation decision: `/price/`, `/calculator/` и `/aiagents/` остаются не отдельными top-level пунктами, а дочерними ссылками dropdown `Услуги` через `services/.top.menu_ext.php`; это сохраняет короткий header и оставляет коммерческие URL в sitewide menu structure. `npm run seo:check` блокирует выпадение этих ссылок из top menu structure.

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
- JSON-LD graph: `Organization`, `WebSite`, `BreadcrumbList` и page-specific schema через options helper.

Default social preview image: `local/templates/tacticum/images/og-default.jpg`, 1200x630. Страницы могут переопределять `image`, `image_width`, `image_height`, `image_type` через `tacticum_apply_seo_defaults(...)`; для fallback helper использует `og-default.jpg`.

FAQ JSON-LD включается только для страниц, где реально рендерится FAQ component: `/`, `/services/`, `/price/`, `/calculator/`, `/aiagents/`.

`/offer/` остаётся landing-входом в offer flow. Детальные offer pages считаются индексируемыми примерами расчёта и открываются по ЧПУ `/offer/<ELEMENT_CODE>/`; `CODE` для новых элементов формируется из `slug.title` и timestamp создания. Legacy `/offer/?ID=<valid>` должен отдавать 301 на канонический ЧПУ, а invalid ID/code - 404 с `noindex`.

Root `404.php` больше не использует `bitrix:main.map`: страница задаёт status 404, title `Страница не найдена - Тактикум`, `meta robots` и `X-Robots-Tag: noindex,nofollow`, один H1 и ссылки на ключевые разделы.

Post-deploy SEO smoke 24.05.2026: `npm run seo:smoke` прошёл по 9 публичным URL в desktop/mobile, все checks `seo=ok`, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T08-28-30-284Z/manifest.json`. Production checks подтвердили 404/noindex, valid offer detail self-canonical, invalid offer 404/noindex и `X-Robots-Tag` на JSON endpoints. Повторный `npm run seo:check:prod` после deploy dedupe fix прошёл; dynamic `/offer/sitemap.php` не содержит duplicate `<loc>`. Sprint 10 SEO-009 revalidation также прошёл `seo:check`, `seo:check:prod` и rendered `seo:smoke`, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T10-52-46-468Z/manifest.json`.

Детальные follow-up gaps по SEO зафиксированы в `docs/workflow/seo-gap-analysis.md`: `SEO-001` - `SEO-008` закрыты production evidence, `SEO-009` принят как navigation decision.

## CI/CD State

`deploy.yml`:

- lint PHP 8.4 по `local/`;
- rsync `local/`;
- rsync публичных разделов;
- rsync корневых файлов;
- чистит `bitrix/managed_cache`, проектный cache, component HTML cache `bitrix/cache/s1/bitrix/news.list|news.detail`, composite HTML pages и CSS/JS asset cache активного шаблона.
- проверяет `https://tacticum.ru/local/rest/health_config.php` после deploy/cache clear.
- запускает `npm ci`, lifecycle guards `css:check` / `template-styles:check`, `npm run visual:smoke` и `npm run browser:smoke` против `https://tacticum.ru`; visual smoke в deploy включает `TACTICUM_EXPECT_SEO_HEAD=1` и проверяет title/description/canonical/OpenGraph/Twitter/JSON-LD/H1/top navigation money links, а `/price/` team presets обязательны через `TACTICUM_EXPECT_PRICE_TEAM_PRESETS=1`.
- запускает `npm run seo:check` до smoke и `npm run seo:check:prod` после browser smoke, чтобы поймать рассинхрон sitemap/robots/canonical, попадание `/404.php` в Bitrix-generated sitemap и отсутствие `X-Robots-Tag` у JSON endpoints.
- для ручной/PM проверки CSS/JS e2e readiness добавлены aggregate scripts `npm run e2e:css-js:prod` и `npm run e2e:css-js:local`; Sprint 10 использует их как единый browser/CSS/JS readiness gate.
- legacy sale aliases контролируются `npm run sale:sunset:check`; Sprint 09 фиксирует action matrix, Sprint 10 ведёт `docs/workflow/legacy-sale-alias-consumer-inventory.md` с repo scan evidence и внешним inventory по access logs/CRM до `30.06.2026`, migration до `31.08.2026` и final alias mode до `30.09.2026`.
- release evidence можно закрывать machine-readable JSON по `docs/workflow/release-signoff.example.json`; проверка `npm run release:signoff:check -- <file>` блокирует pending/missing evidence, unknown gates, placeholder/working-tree metadata, валидирует структуру ручных gates, CSS/JS e2e manifests и отсекает PII-like evidence; `npm run release:signoff:summary -- <file>` даёт PM/QA статус draft без чтения JSON; `npm run release:signoff:self-test` закрепляет негативные кейсы checker в PR/deploy lifecycle guard, а `docs/workflow/manual-release-gates-runbook.md` задаёт порядок закрытия ручных gates без PII.
- локальный `npm run dev:preflight` не блокирует работу без PHP CLI 8.4+, но явно сообщает degraded state; authoritative PHP syntax fallback — GitHub job `php-lint`, который устанавливает PHP 8.4 через `shivammathur/setup-php`.

Production smoke 21.05.2026: `GET https://tacticum.ru/local/rest/health_config.php` с `Origin: https://tacticum.ru` вернул `200` и `{"success":true,"code":"ok"}` по scopes `api`, `ai`, `telegram`, `offer`, `content`, `rest`.

`pr-check.yml`:

- PHP syntax по `local/`;
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
| Frontend maintainability | Хорошее: chat/forms/assets, repeated CTA, light chat и price component contracts унифицированы; static Tailwind bundle, `styles/global.css`, browser-error smoke, non-network action-smoke, CSS replacement smoke, aggregate CSS/JS e2e scripts и CSP report-only есть; `template_styles.css` удерживается comment-only guard | Низкий/средний |
| SEO | Среднее/хорошее: sitemap, description, canonical и OG добавлены; нужен post-deploy render check | Низкий/средний |
| CI/CD | Среднее/хорошее: runtime blockers и deploy health smoke есть, public hardcode warnings остаются | Средний |
| Product flows | Среднее/хорошее: лид-формы, AI-chat, prefill и staff-order имеют контракты и единые handlers; real success-flow закрывается staging/manual sign-off, автоматический deploy smoke покрывает non-network actions | Низкий/средний |
