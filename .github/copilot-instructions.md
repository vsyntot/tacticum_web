# Copilot Instructions — tacticum.ru

## Проект

Корпоративный сайт **tacticum.ru** — IT-компания, специализируется на внедрении AI.
Стек: **PHP 8.4**, **1C-Bitrix** (актуальная версия), шаблон `local/templates/tacticum/`.

Перед началом нетривиальной задачи прочитать `AGENTS.md`, `docs/workflow/README.md`,
`docs/workflow/current-state.md` и `docs/workflow/gap-analysis.md`.
Для задач, которые не являются простым Fast Fix, оформить план по `docs/workflow/codex-plan-template.md`.

---

## Структура проекта

```
/
├── local/
│   ├── api/                        # GET-эндпоинты (cases, faq, rates, services)
│   ├── rest/                       # POST-эндпоинты (форма, чат, оффер, продажи)
│   │   ├── rest_helpers.php        # Facade для общих REST/API функций: CSRF, rate limit, IP, origin, curl
│   │   ├── tacticum_form.php       # ЭТАЛОН для новых POST-эндпоинтов
│   │   ├── tacticum_chat.php       # Чат-агент → AI_SERVICE_BASE_URL/tacticum/v1/chat_agent
│   │   ├── tacticum_offer.php      # Коммерческое предложение
│   │   ├── tacticum_sale.php       # Продажи
│   │   ├── tacticum_sale_staff.php # Заказ специалистов
│   │   ├── tacticum_prefill.php    # Предзаполнение форм
│   │   └── resolve_telegram_link.php
│   ├── php_interface/
│   │   ├── init.php                # Тонкий bootstrap include/registration
│   │   └── include/
│   │       ├── tacticum_config.php # Конфиг: инфоблоки, URL, CORS, IP (НЕ в git)
│   │       ├── site_helpers.php    # Site URL / iblock key helpers
│   │       ├── seo_helpers.php     # Canonical/OG/JSON-LD/robots helpers
│   │       ├── component_helpers.php # Shared component parameter helpers
│   │       ├── calcrequests_rest.php # Bitrix REST calcrequests.*
│   │       ├── offer_catalog_cache.php # Лёгкий cache/event layer для /offer/
│   │       └── menu_helpers.php
│   ├── lib/Tacticum/Rest/         # Реализация REST/API runtime: config, response, security, rate, masking, outbound
│   └── templates/tacticum/        # Активный шаблон Bitrix
│       ├── header.php              # Подключение JS/CSS, Яндекс.Метрика (ID: 103471113)
│       ├── footer.php              # Футер, попап "Связаться с нами", мобильное меню
│       ├── assets/src/tailwind.css # Source entrypoint static Tailwind CSS
│       ├── tailwind.generated.css  # Generated CSS, обновлять через npm run css:build
│       ├── template_styles.css     # Пустой Bitrix compatibility shim
│       ├── styles/global.css       # Единственный manual runtime CSS через Asset
│       ├── js/                     # analytics.js, forms.js, modal.js, chat-agent.js...
│       └── components/bitrix/      # Компоненты Bitrix шаблона
├── local/api/cases.php             # ЭТАЛОН для новых GET-эндпоинтов
├── about/, services/, contacts/   # Страницы сайта
├── calculator/, price/, offer/
├── aiagents/, policies/
└── bitrix/                         # Ядро Bitrix — НЕ РЕДАКТИРОВАТЬ
```

---

## Инфоблоки

| Ключ конфига | ID | Назначение |
|---|---|---|
| `cases` | 13 | Кейсы / портфолио |
| `faq` | 10 | FAQ |
| `rates` | 11 | Тарифы |
| `services` | 12 | Услуги |
| `offer` | 5 | Коммерческие предложения (calcrequests) |

---

## Соглашения по коду

### PHP
- Стиль: **PSR-12**, в новых файлах `declare(strict_types=1)`.
- PHP 8.4: `match`, `readonly`, named arguments, `enum` — приветствуется.
- `init.php` должен оставаться тонким bootstrap/registration файлом; domain helpers и REST callbacks выносить в `local/php_interface/include/`.
- Функции в `local/php_interface/include/*.php` — префикс `tacticum_`, shared component parameter helpers — через `TacticumComponentParams`.
- Функции в `rest_helpers.php` — только compatibility facade с префиксом `tacticum_rest_` или `tacticum_api_`; новую реализацию класть в `local/lib/Tacticum/Rest/*`.
- Предпочитать Bitrix D7 / ORM над старым API где возможно.
- Загрузка модулей: `Loader::includeModule()`, не `CModule::IncludeModule()`.

### REST/API эндпоинты — обязательный bootstrap (см. `tacticum_form.php`)

**POST-эндпоинт** (шаблон):
```php
<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');

tacticum_rest_validate_origin();                                  // 1. CORS/Referer
tacticum_rest_rate_limit_by_class('PUBLIC_LEAD_POST', 'action_name'); // 2. Rate limiting
tacticum_rest_require_method('POST');                             // 3. Method guard

$data = tacticum_rest_read_json_body();  // 4. JSON parse
tacticum_rest_check_csrf($data);         // 5. CSRF

// ... валидация, бизнес-логика, вызов AI-сервиса
```

**GET-эндпоинт** (шаблон, см. `local/api/cases.php`):
```php
<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/rest/rest_helpers.php");

header('Content-Type: application/json; charset=UTF-8');

$iblockId = tacticum_api_bootstrap('key_from_config'); // validate_origin + rate_limit + GET check
$res = tacticum_api_fetch_elements($iblockId, ['ID', 'NAME', '...']);
```

### Работа с конфигом
```php
// ❌ Нельзя
$url = 'https://ai.example.com';
$iblockId = 5;

// ✅ Правильно
$url = tacticum_rest_get_required_https_ai_url('AI_SERVICE_BASE_URL');
$iblockId = tacticum_rest_get_iblock_id('offer');
```

### Runtime-логирование
```php
// ❌ Нельзя
AddMessage2Log(serialize($data), 'my_action');
error_log($errorMessage);
file_put_contents('/tmp/debug.log', serialize($payload));
```

Кастомный runtime-код в `/local` и публичной части не должен писать payload/response в файловые логи. Временная диагностика допустима только отдельной incident-задачей с явным сроком удаления.

### Внешние HTTP-запросы
```php
$url = tacticum_rest_build_url($base_url, '/tacticum/v1/endpoint');
$ch = curl_init($url);
tacticum_rest_apply_curl_defaults($ch); // таймауты, SSL, JSON headers
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
```

---

## Безопасность — обязательные правила

| Правило | Где реализовано |
|---|---|
| CORS/Referer проверка | `tacticum_rest_validate_origin()` |
| Rate limiting (IP + сессия) | `tacticum_rest_rate_limit_by_class('RISK_CLASS', 'action')` |
| CSRF для POST-форм | `tacticum_rest_check_csrf($data)` |
| Запрет файлового runtime-логирования payload/response | PR-check scan по `/local` и публичным скриптам |
| Только HTTPS для внешних запросов | проверка scheme в `tacticum_form.php` |
| IP allowlist | `rest.allowed_ips` в `tacticum_config.php` |
| Разрешённые origins | `tacticum.ru`, `*.tacticum.ru` |

---

## Frontend (шаблон `local/templates/tacticum/`)

- CSS: static Tailwind bundle собирается из `assets/src/tailwind.css` в `tailwind.generated.css`.
- `styles/global.css` содержит migrated global/template CSS и scoped page blocks, подключается через `Asset`; `template_styles.css` должен оставаться пустым/comment-only Bitrix shim.
- `local/templates/tacticum/styles/` использует approved fixed split: `styles/global.css`, `styles/components.css`, `styles/page-about-calculator.css`, `styles/page-offer-price-services.css`, `styles/page-aiagents.css`; состав проверяет `npm run template-styles:check`, а новые template-level CSS файлы требуют отдельного архитектурного решения.
- Legacy browser Tailwind runtime `bundle.v3.4.16.js` и `js/init.js` удалены и не должны возвращаться.
- Новый JS для страницы подключается в `header.php` через `$obAsset->addJs(...)` по explicit page asset flag.
- Новый CSS предпочтительно добавлять через Tailwind source, scoped block в `styles/global.css` с body/page class или component `style.css`; новый template-level page CSS не добавлять без отдельного архитектурного решения и обновления `docs/workflow/asset-layout-audit.md`.
- Форма: атрибут `data-tacticum-form` на `<form>` — автоматически подхватывается `forms.js`.
- После CSS/JS правок запускать `npm run e2e:css-js:local` до deploy и `npm run e2e:css-js:prod` после deploy, если PR затрагивает browser runtime/assets; точечные guards `css:check`, `template-styles:check`, `visual:smoke:*` и `browser:smoke:*` можно использовать для локализации падения.
- После изменений public page/component/bootstrap patterns запускать `npm run bitrix:check`.

---

## Чего НЕ делать

- ❌ Не редактировать файлы в `bitrix/`
- ❌ Не хардкодить ID инфоблоков — только `tacticum_rest_get_iblock_id('key')`
- ❌ Не хардкодить URL AI-сервиса — только `tacticum_rest_get_ai_setting('AI_SERVICE_BASE_URL')`
- ❌ Не дублировать логику из `rest_helpers.php` / `local/lib/Tacticum/Rest/*`
- ❌ Не добавлять файловое/debug runtime-логирование payload/response
- ❌ Не использовать `$_GET`/`$_POST` напрямую — только Bitrix Context или `php://input`
- ❌ Не использовать `http://` для внешних curl-запросов в production
- ❌ Не создавать глобальные функции без префикса `tacticum_`

---

## AI-интеграция

Сайт интегрирован с внешним AI-сервисом (`AI_SERVICE_BASE_URL`):

| Эндпоинт AI | Вызывается из |
|---|---|
| `/tacticum/v1/chat_agent/sale` | `tacticum_form.php` — обработка лидов |
| `/tacticum/v1/chat_agent/sale` | `tacticum_sale_staff.php` — adapter заказа специалистов; rich staff модель хранится в `workers[]` payload |
| `/tacticum/v1/chat_agent` | `tacticum_chat.php` — чат на сайте |

Bitrix REST API: `calcrequests.add` / `calcrequests.list` — работа с расчётами (инфоблок 5).

---

## GitHub Projects / рабочий процесс

- Ветки: `feature/<issue-number>-slug`, `fix/<issue-number>-slug`
- Коммиты: Conventional Commits (`feat:`, `fix:`, `refactor:`, `docs:`, `infra:`)
- PR: закрывает Issue (`Closes #N`), заполнен шаблон `PULL_REQUEST_TEMPLATE.md`
- Production trigger: push/merge в `main` через GitHub Actions, но mutation разрешена только после gates из `docs/workflow/production-deployment-governance.md` и ADR-013
- Первый поддерживаемый production contour — `FILE_ONLY`; `STATEFUL` требует отдельного migration plan
- Перед deploy обязательны immutable artifact, `BASE/PROD/CANDIDATE` reconciliation, exact plan approval, server lock, backup/restore rehearsal, smoke/monitoring и dual BASE
- Production secrets не выдавать PR jobs; личный SSH private key не переносить в GitHub Actions
- Личный SSH key не использовать штатным `prod:*` tooling: bootstrap/manual, dedicated local read-only, CI read-only и CI write credentials разделены; read-only обеспечивается server forced command
- Пока executable production reconciliation gates не реализованы и не проверены, зелёный PR не является разрешением на merge/deploy

---

## ADR — архитектурные решения

Документация в `docs/adr/`. При значимом архитектурном решении — создавать новый ADR.
Текущие решения: см. `docs/adr/README.md`.
