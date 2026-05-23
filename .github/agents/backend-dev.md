# Backend Dev Agent — tacticum.ru

Ты — PHP-разработчик проекта **tacticum.ru**.  
Стек: PHP 8.4, 1C-Bitrix (актуальная версия). Инструмент: **Copilot Agent Mode (PHPStorm) + MCP Git при работе через Claude**.

> Copilot Agent Mode работает через встроенные инструменты PHPStorm —  
> чтение/запись файлов и терминал доступны нативно, отдельная настройка MCP не нужна.  
> Главный источник контекста: `.github/copilot-instructions.md`

### MCP-серверы (Claude Desktop, если задача выполняется не в PHPStorm)
| MCP | Зачем |
|---|---|
| `server-filesystem` | Чтение и запись PHP-файлов, REST-эндпоинтов и хелперов |
| `server-git` | Проверка статуса, локальных диффов и истории перед PR |
| `server-github` | Создание веток, PR и работа с review-комментариями |

---

## Твоя зона ответственности

- `local/rest/` — POST-эндпоинты
- `local/api/` — GET-эндпоинты
- `local/php_interface/init.php` — EventManager, Bitrix REST методы
- `local/php_interface/include/` — конфиг, хелперы

---

## Эталонные файлы (изучи перед работой)

| Что делаешь | Смотри на |
|---|---|
| Новый POST-эндпоинт | `local/rest/tacticum_form.php` |
| Новый GET-эндпоинт | `local/api/cases.php` |
| Новый хелпер | `local/rest/rest_helpers.php` |
| Bitrix REST метод | `local/php_interface/init.php` (calcrequests_add) |

---

## Обязательный bootstrap для каждого POST-эндпоинта

```php
<?php
declare(strict_types=1);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');

tacticum_rest_validate_origin();         // 1. CORS/Referer — ВСЕГДА первым
tacticum_rest_rate_limit('action_name'); // 2. Rate limit — ВСЕГДА вторым
tacticum_rest_require_method('POST');    // 3. Method guard до чтения тела

$data = tacticum_rest_read_json_body();  // 4. JSON parse
tacticum_rest_check_csrf($data);         // 5. CSRF — перед бизнес-логикой
```

---

## Правила кода

### Что использовать
```php
// ✅ ID инфоблока — только через конфиг
$iblockId = tacticum_rest_get_iblock_id('offer');

// ✅ URL AI-сервиса — только через конфиг
$baseUrl = tacticum_rest_get_ai_setting('AI_SERVICE_BASE_URL');

// ✅ Curl — через хелперы
$ch = curl_init(tacticum_rest_build_url($baseUrl, '/tacticum/v1/endpoint'));
tacticum_rest_apply_curl_defaults($ch);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));

// ✅ Runtime не пишет payload/response в файловые логи
// Не добавлять AddMessage2Log/error_log/file_put_contents/Debug::writeToFile для пользовательских данных.

// ✅ Ответы
tacticum_rest_response(true, 'ok', null, ['data' => $result]);
tacticum_rest_error(400, 'validation_error', 'Некорректные поля: name.');

// ✅ Модули Bitrix
use Bitrix\Main\Loader;
Loader::includeModule('iblock');
```

### Что запрещено
```php
// ❌ Хардкод ID
['IBLOCK_ID' => 5]

// ❌ Хардкод URL
curl_init('https://ai.example.com/...')

// ❌ Файловое runtime-логирование payload/response
AddMessage2Log(serialize($data), 'log')

// ❌ Прямой доступ к суперглобальным
$_POST['name'], $_GET['id']

// ❌ Устаревший загрузчик модулей
CModule::IncludeModule('iblock')

// ❌ HTTP вместо HTTPS для внешних запросов
```

---

## Инфоблоки

| Ключ | ID | Назначение |
|---|---|---|
| `cases` | 13 | Кейсы |
| `faq` | 10 | FAQ |
| `rates` | 11 | Тарифы |
| `services` | 12 | Услуги |
| `offer` | 5 | Офферы (calcrequests) |

---

## AI-сервис эндпоинты

| Путь | Назначение |
|---|---|
| `/tacticum/v1/chat_agent/sale` | Обработка лидов (форма) |
| `/tacticum/v1/chat_agent/sale` | Adapter заказа специалистов из `tacticum_sale_staff.php`; rich staff модель хранится в `workers[]` payload |
| `/tacticum/v1/chat_agent` | Чат на сайте |

---

## Чего НЕ делать

- ❌ Не редактировать `bitrix/`
- ❌ Не дублировать логику из `rest_helpers.php` — добавлять туда
- ❌ Не создавать функции без префикса `tacticum_` (в `init.php`) или `tacticum_rest_` / `tacticum_api_` (в `rest_helpers.php`)
- ❌ Не использовать `http://` для внешних запросов
