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

- остаются frontend-debts: runtime Tailwind, URL-substring asset routing и legacy `chat.js` artifact вне production flow;
- публичные страницы и компоненты всё ещё содержат отдельные inline/legacy styles, которые нельзя удалять без visual regression;
- production REST теперь требует HTTPS URL внешних AI-сервисов; серверный `tacticum_config.php` должен быть обновлён перед deploy;
- локальный `tacticum_config.php` хранится вне Git index и должен синхронизироваться с `tacticum_config.example.php` вручную на окружениях;
- продуктовые сценарии AI-чата/калькулятора/оффера требуют регулярного post-deploy smoke по зафиксированной матрице.

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
| `tacticum_form.php` | Default endpoint публичных лид-форм | HTTPS URL через shared outbound helper, validation, PII masking |
| `tacticum_chat.php` | AI chat | Origin/rate/явный CSRF, HTTPS URL через shared outbound helper, унифицированные log tags |
| `tacticum_offer.php` | Sale request | Origin/rate/явный CSRF, HTTPS URL через shared outbound helper; остаётся доменное пересечение с `tacticum_form.php` |
| `tacticum_sale.php` | Sale request | Origin/rate/явный CSRF, HTTPS URL через shared outbound helper; похоже дублирует `tacticum_offer.php` |
| `tacticum_sale_staff.php` | Заказ специалистов | Доменный staff endpoint для `/price/`: rich `workers[]` payload + adapter в `/tacticum/v1/chat_agent/sale`; outbound через shared helper |
| `tacticum_prefill.php` | Предзаполнение формы по `group_id` | Production path: POST JSON + явный `sessid`; legacy GET fallback временно сохранён; `Loader::includeModule`, masked summary log |
| `resolve_telegram_link.php` | Telegram link resolver | Origin/rate/явный CSRF, HTTPS URL через shared helper; logging taxonomy ещё можно улучшить |
| `health_config.php` | Проверка обязательной конфигурации | GET, origin/rate; возвращает только keys/codes ошибок, без значений secret/config |

## Frontend State

### Template / Assets

`header.php` использует `Bitrix\Main\Page\Asset::getInstance()` для подключения:

- `bundle.v3.4.16.js`;
- `init.js`;
- `menu.js`;
- `analytics.js`;
- `forms.js`;
- `chat-agent.js`;
- `modal.js`;
- `scroll.js`;
- `tg-link-resolver.js`;
- условно `faq.js`, `charts.js`;
- `fonts/remixicon.min.css`;
- `styles/aiagents.css` для `/aiagents/`.

Проблема: большинство page-specific CSS (`main.css`, `services.css`, `price.css`, `calculator.css`, `about.css`, `contacts.css`) не видно как явно подключённые в `header.php` по условиям, при этом файлы крупные. Нужно отдельно проверить фактическое подключение через template_styles/bundle/Bitrix settings.

### Forms

Формы с `data-tacticum-form` найдены:

- main CTA: `index.php`;
- about CTA: `about/index.php`;
- services CTA: `services/index.php`;
- calculator CTA: `calculator/index.php`;
- price CTA: `price/index.php`;
- contacts CTA: `contacts/index.php`;
- offer CTA: `local/templates/tacticum/components/bitrix/news.detail/offer/template.php`;
- aiagents inline: `aiagents/index.php`;
- modal form: `footer.php`;
- specialist order: `news.list/price/script.js`.

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

`local/templates/tacticum/js/chat.js` остаётся legacy/demo artifact и не должен использоваться для production REST flow без отдельного решения.

`chat-agent.js` отправляет события `tacticum_chat_*` и `tacticum_prefill_*` без текста сообщений и пользовательских контактов.

## SEO State

Текущий `sitemap.xml` — sitemap index, указывает на `https://tacticum.ru/sitemap-files.xml`.

`sitemap-files.xml` содержит:

- `/`
- `/about/`
- `/aiagents/`
- `/calculator/`
- `/contacts/`
- `/offer/`
- `/policies/`
- `/price/`
- `/services/`

Публичные страницы задают `SetTitle(...)`, `description` и вызывают `tacticum_apply_seo_defaults(...)`, который добавляет:

- canonical;
- `og:site_name`;
- `og:locale`;
- `og:type`;
- `og:url`;
- `og:title`;
- `og:description`, если задан description;
- `og:image`.

`/offer/` формирует canonical с `ID`, если он есть в запросе, потому что контент страницы зависит от конкретного предложения.

## CI/CD State

`deploy.yml`:

- lint PHP 8.4 по `local/`;
- rsync `local/`;
- rsync публичных разделов;
- rsync корневых файлов;
- чистит `bitrix/managed_cache` и `bitrix/cache/tacticum`.

`pr-check.yml`:

- PHP syntax по `local/`;
- blocker при хардкоде iblock ID, HTTP fallback, raw PII logging и пропущенном bootstrap в изменённых runtime-файлах;
- warning при hardcoded `IBLOCK_ID` в новом/legacy-коде вне разрешённых runtime исключений;
- blocker для изменений в `bitrix/`.

Gap: новые hardcoded `IBLOCK_ID` не допускаются; публичные страницы переведены на config helper, дальнейший scan нужен только для legacy-кода вне затронутого scope.

## Архитектурные Решения

Приняты ADR:

- ADR-001: REST endpoints как отдельные PHP-файлы.
- ADR-002: config через `tacticum_config.php`.
- ADR-003: ID инфоблоков через `tacticum_rest_get_iblock_id()`.
- ADR-004: PII masking до логирования.

Фактическое состояние runtime REST приведено ближе к ADR-003/ADR-004/HTTPS правилу. Основные остатки — runtime Tailwind/static CSS plan, URL-substring asset routing и legacy `chat.js` artifact.

## Текущее Резюме Здоровья

| Область | Состояние | Риск |
|---|---|---|
| Bitrix isolation | Хорошее: кастомный код в `local/`, ядро не рабочая зона | Низкий |
| REST bootstrap | Хорошее: pattern есть, outbound helper общий, response shapes оставлены доменными | Низкий/средний |
| Config discipline | Хорошее: config validation есть, local config вынесен из Git index | Низкий/средний |
| Frontend maintainability | Среднее: chat/forms унифицированы, но runtime Tailwind и URL-substring assets остаются | Средний |
| SEO | Среднее/хорошее: sitemap, description, canonical и OG добавлены; нужен post-deploy render check | Низкий/средний |
| CI/CD | Среднее/хорошее: runtime blockers есть, public hardcode warnings остаются | Средний |
| Product flows | Среднее: лид-формы есть, AI-chat есть, но сценарии разрознены | Средний |
