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

- часть legacy-кода всё ещё нарушает принятые ADR: хардкод ID инфоблоков в публичных страницах и дублированная chat logic;
- публичные страницы содержат много inline JS/CSS и дублируют chat logic;
- production REST теперь требует HTTPS URL внешних AI-сервисов; серверный `tacticum_config.php` должен быть обновлён перед deploy;
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
| `vacancies` | 7 | Вакансии |
| `feedback` | 9 | Отзывы / feedback |
| `team` | 18 | Команда |
| `policies` | 19 | Политики / legal контент |
| `aiagents` | 20 | AI agents |

Публичные страницы пока продолжают использовать legacy numeric `IBLOCK_ID` в `IncludeComponent`; ключи уже описаны для планового рефакторинга.

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

Замечание: helper переведён на D7 `Loader::includeModule()` для подключения `iblock`.

### POST REST

| Endpoint | Назначение | Состояние |
|---|---|---|
| `tacticum_form.php` | Default endpoint публичных лид-форм | HTTPS URL через shared helper, validation, PII masking |
| `tacticum_chat.php` | AI chat | Origin/rate/явный CSRF, HTTPS URL через shared helper, унифицированные log tags |
| `tacticum_offer.php` | Sale request | Origin/rate/явный CSRF, HTTPS URL через shared helper; остаётся дублирование `tacticum_form.php` |
| `tacticum_sale.php` | Sale request | Origin/rate/явный CSRF, HTTPS URL через shared helper; похоже дублирует `tacticum_offer.php` |
| `tacticum_sale_staff.php` | Заказ специалистов | Доменный staff endpoint для `/price/`: rich `workers[]` payload + adapter в `/tacticum/v1/chat_agent/sale` до восстановления workers API |
| `tacticum_prefill.php` | Предзаполнение формы по `group_id` | GET-параметр + явный `sessid`, `Loader::includeModule`, masked summary log; семантика GET/REST остаётся debt |
| `resolve_telegram_link.php` | Telegram link resolver | Origin/rate/явный CSRF, HTTPS URL через shared helper; logging taxonomy ещё можно улучшить |

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
- services CTA: `services/index.php`;
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
- отправляет формы в `/local/rest/tacticum_form.php` по умолчанию;
- поддерживает `data-endpoint` для доменных сценариев, например `/price/` staff-order;
- показывает toast и reset/close modal.

### AI Chat

Есть несколько реализаций:

- `local/templates/tacticum/js/chat.js` — статический демонстрационный чат, не использует backend endpoint.
- inline chat logic в `index.php` — реальный fetch в `tacticum_chat.php`, prefill через `tacticum_prefill.php`.
- inline chat logic в `calculator/index.php`.
- inline chat logic в `price/index.php`.

Это создаёт расхождение UX, сложность тестирования и риск разного поведения.

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
- blocker при хардкоде iblock ID, HTTP fallback, raw PII logging и пропущенном bootstrap в изменённых runtime-файлах;
- warning при hardcoded `IBLOCK_ID` в публичных legacy-страницах;
- blocker для изменений в `bitrix/`.

Gap: public page hardcoded `IBLOCK_ID` пока warning-level, потому что полный refactor публичных компонентов вынесен в следующий спринт.

## Архитектурные Решения

Приняты ADR:

- ADR-001: REST endpoints как отдельные PHP-файлы.
- ADR-002: config через `tacticum_config.php`.
- ADR-003: ID инфоблоков через `tacticum_rest_get_iblock_id()`.
- ADR-004: PII masking до логирования.

Фактическое состояние runtime REST приведено ближе к ADR-003/ADR-004/HTTPS правилу. Основной остаток — legacy публичные страницы и унификация chat/frontend.

## Текущее Резюме Здоровья

| Область | Состояние | Риск |
|---|---|---|
| Bitrix isolation | Хорошее: кастомный код в `local/`, ядро не рабочая зона | Низкий |
| REST bootstrap | Среднее: pattern есть, но endpoints неоднородны | Средний |
| Config discipline | Среднее: runtime helpers enforce HTTPS, public pages still legacy | Средний |
| Frontend maintainability | Среднее/низкое: много inline JS и дублирования | Средний |
| SEO | Среднее: sitemap исправлен, meta неполные | Средний |
| CI/CD | Среднее/хорошее: runtime blockers есть, public hardcode warnings остаются | Средний |
| Product flows | Среднее: лид-формы есть, AI-chat есть, но сценарии разрознены | Средний |
