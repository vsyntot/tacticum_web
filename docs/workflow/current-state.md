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
- CI уже проверяет PHP syntax и часть security conventions.

Основные риски:

- часть legacy-кода всё ещё нарушает принятые ADR: хардкод ID инфоблоков, HTTP fallback URL, `CModule::IncludeModule`;
- публичные страницы содержат много inline JS/CSS и дублируют chat logic;
- sitemap использует HTTP URL и не содержит все публичные страницы;
- локальный `tacticum_config.php` содержит HTTP base URLs, что конфликтует с правилом production HTTPS;
- GET API без явного кеширования может создавать лишнюю нагрузку при росте использования;
- продуктовые сценарии AI-чата/калькулятора/оффера реализованы, но не оформлены как единый контракт и smoke-suite.

## Структура Приложения

| Зона | Файлы | Назначение |
|---|---|---|
| Публичные страницы | `index.php`, `about/`, `services/`, `price/`, `calculator/`, `offer/`, `aiagents/`, `contacts/`, `policies/` | Основные страницы сайта |
| GET API | `local/api/cases.php`, `faq.php`, `rates.php`, `services.php` | JSON-выдача активных элементов инфоблоков |
| POST REST | `local/rest/tacticum_form.php`, `tacticum_chat.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `tacticum_prefill.php`, `resolve_telegram_link.php` | Формы, чат, AI-service, prefill, Telegram resolver |
| Shared REST helpers | `local/rest/rest_helpers.php` | Config, CORS/origin, IP allowlist, rate limit, CSRF, curl defaults, masking |
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

Дополнительно используются на страницах, но не закреплены в текущем config/ADR:

| ID | Где найдено | Предполагаемое назначение |
|---:|---|---|
| 7 | `about/index.php` | Партнёры/клиенты/командный контент |
| 9 | `index.php` | Services/clients/feedback content |
| 18 | `about/index.php` | Вакансии/команда |
| 19 | `policies/index.php` | Политика конфиденциальности |
| 20 | `aiagents/index.php` | AI agents/demo agents |

## REST/API State

### GET API

Все GET endpoints используют общий `tacticum_api_bootstrap($action)`, который:

- валидирует origin;
- применяет rate limit;
- проверяет HTTP method `GET`;
- подключает `iblock`;
- получает ID инфоблока по ключу.

Endpoints:

| Endpoint | Iblock key | Возвращает |
|---|---|---|
| `/local/api/cases.php` | `cases` | `name`, sections, preview, detail, properties |
| `/local/api/faq.php` | `faq` | `question`, `answer`, sections, properties |
| `/local/api/rates.php` | `rates` | `name`, sections, properties |
| `/local/api/services.php` | `services` | `name`, preview, detail, properties |

Замечание: используется legacy `CModule::IncludeModule()` внутри helper, хотя проектные правила требуют D7 `Loader::includeModule()` для нового кода.

### POST REST

| Endpoint | Назначение | Состояние |
|---|---|---|
| `tacticum_form.php` | Единый endpoint форм и заказа специалистов | Лучший текущий эталон: есть HTTPS scheme check, validation, PII masking |
| `tacticum_chat.php` | AI chat | Есть origin/rate/CSRF, но HTTP fallback URL и лог-теги `data/request/response` слишком общие |
| `tacticum_offer.php` | Sale request | Есть security bootstrap, но HTTP fallback URL и дублирование логики `tacticum_form.php` |
| `tacticum_sale.php` | Sale request | Похоже дублирует `tacticum_offer.php`; HTTP fallback URL |
| `tacticum_sale_staff.php` | Заказ специалистов | Похоже дублирует ветку `tacticum_form.php`; HTTP fallback URL |
| `tacticum_prefill.php` | Предзаполнение формы по `group_id` | GET-параметр читается через `$_GET`, используется `CModule`, есть fallback `offer=5`, логирует весь объект |
| `resolve_telegram_link.php` | Telegram link resolver | HTTP fallback URL, request payload без явного `sessid` на фронте, логирование может раскрывать URL/start-параметры |

## Frontend State

### Template / Assets

`header.php` использует `Bitrix\Main\Page\Asset::getInstance()` для подключения:

- `bundle.v3.4.16.js`;
- `init.js`;
- `menu.js`;
- `forms.js`;
- `modal.js`;
- `scroll.js`;
- `tg-link-resolver.js`;
- условно `faq.js`, `chat.js`, `charts.js`;
- `fonts/remixicon.min.css`;
- `styles/aiagents.css` для `/aiagents/`.

Проблема: большинство page-specific CSS (`main.css`, `services.css`, `price.css`, `calculator.css`, `about.css`, `contacts.css`) не видно как явно подключённые в `header.php` по условиям, при этом файлы крупные. Нужно отдельно проверить фактическое подключение через template_styles/bundle/Bitrix settings.

### Forms

Формы с `data-tacticum-form` найдены:

- main CTA: `index.php`;
- about CTA: `about/index.php`;
- calculator CTA: `calculator/index.php`;
- price CTA: `price/index.php`;
- offer CTA: `local/templates/tacticum/components/bitrix/news.detail/offer/template.php`;
- aiagents inline: `aiagents/index.php`;
- modal form: `footer.php`;
- specialist order: `news.list/price/script.js`.

Общий `forms.js`:

- валидирует `name`, `email`, `phone`, `message`;
- добавляет `page_url`;
- добавляет `sessid`, если доступен `BX.bitrix_sessid()`;
- добавляет `group_id` из `window.tacticum_offer_context`;
- отправляет всё в `/local/rest/tacticum_form.php`;
- показывает toast и reset/close modal.

### AI Chat

Есть несколько реализаций:

- `local/templates/tacticum/js/chat.js` — статический демонстрационный чат, не использует backend endpoint.
- inline chat logic в `index.php` — реальный fetch в `tacticum_chat.php`, prefill через `tacticum_prefill.php`.
- inline chat logic в `calculator/index.php`.
- inline chat logic в `price/index.php`.

Это создаёт расхождение UX, сложность тестирования и риск разного поведения.

## SEO State

Текущий `sitemap.xml` — sitemap index, указывает на `http://tacticum.ru/sitemap-files.xml`.

`sitemap-files.xml` содержит:

- `/`
- `/about/`
- `/aiagents/`
- `/calculator/`
- `/contacts/`
- `/offer/`
- `/price/`
- `/services/`

Не содержит:

- `/policies/`

Также sitemap URL используют HTTP, тогда как `robots.txt` указывает HTTPS sitemap.

Большинство страниц задают только `SetTitle(...)`; `description`/OpenGraph не систематизированы.

## CI/CD State

`deploy.yml`:

- lint PHP 8.4 по `local/`;
- rsync `local/`;
- rsync публичных разделов;
- rsync корневых файлов;
- чистит `bitrix/managed_cache` и `bitrix/cache/tacticum`.

`pr-check.yml`:

- PHP syntax по `local/`;
- warning при хардкоде iblock ID в `local/rest`/`local/api`;
- warning при возможном PII logging;
- warning при `curl_init('http://...')`;
- warning при отсутствии `validate_origin`/`rate_limit` в новых REST-файлах;
- blocker для изменений в `bitrix/`.

Gap: часть проверок warning, а не blocker; проверки не покрывают публичные страницы с хардкодом `IBLOCK_ID`.

## Архитектурные Решения

Приняты ADR:

- ADR-001: REST endpoints как отдельные PHP-файлы.
- ADR-002: config через `tacticum_config.php`.
- ADR-003: ID инфоблоков через `tacticum_rest_get_iblock_id()`.
- ADR-004: PII masking до логирования.

Фактическое состояние частично не соответствует ADR-003/ADR-004/HTTPS правилу из Copilot instructions.

## Текущее Резюме Здоровья

| Область | Состояние | Риск |
|---|---|---|
| Bitrix isolation | Хорошее: кастомный код в `local/`, ядро не рабочая зона | Низкий |
| REST bootstrap | Среднее: pattern есть, но endpoints неоднородны | Средний |
| Config discipline | Среднее: helpers есть, но есть fallback/hardcode | Высокий для переносимости/security |
| Frontend maintainability | Среднее/низкое: много inline JS и дублирования | Средний |
| SEO | Среднее/низкое: sitemap HTTP/stale, meta неполные | Средний |
| CI/CD | Среднее: есть базовые проверки, мало blockers | Средний |
| Product flows | Среднее: лид-формы есть, AI-chat есть, но сценарии разрознены | Средний |
