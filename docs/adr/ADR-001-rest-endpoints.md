# ADR-001: REST-эндпоинты как отдельные PHP-файлы

**Статус:** ✅ Принято
**Дата:** 2025
**Автор:** Architect

---

## Контекст

Сайту tacticum.ru требуются собственные API-эндпоинты для:
- приёма форм обратной связи с фронтенда
- интеграции с внешним AI-сервисом (`AI_SERVICE_BASE_URL`)
- чата, создания офферов, выбора специалистов

Bitrix не предоставляет встроенного REST-роутера уровня современного фреймворка.
Нужно выбрать подход к организации эндпоинтов.

---

## Решение

Каждый эндпоинт — **отдельный PHP-файл** в `local/rest/` (POST) или `local/api/` (GET).

Каждый POST-файл обязан начинаться с bootstrap-последовательности:

```php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

tacticum_rest_validate_origin();          // 1. CORS/Referer
tacticum_rest_rate_limit('action_name'); // 2. Rate limiting
// ... далее: check_csrf, валидация, бизнес-логика
```

Каждый GET-файл использует `tacticum_api_bootstrap('iblock_key')` из `rest_helpers.php`.

**Эталонные файлы:**
- POST: `local/rest/tacticum_form.php`
- GET: `local/api/cases.php`

Все общие утилиты безопасности и HTTP — только в `local/rest/rest_helpers.php`.

---

## Альтернативы

| Вариант | Почему отклонён |
|---|---|
| Bitrix REST API (`OnRestServiceBuildDescription`) | Используется для внутренних методов (`calcrequests.*`), не подходит для публичных CORS-форм |
| Единый `index.php` с роутингом | Избыточно для текущего масштаба, усложняет деплой отдельных эндпоинтов |
| Symfony/Laravel поверх Bitrix | Конфликт с Bitrix bootstrap, высокая стоимость интеграции |

---

## Последствия

✅ Просто добавлять новые эндпоинты — скопировать эталон, изменить логику
✅ Каждый файл изолирован и тестируется независимо (curl/Postman)
✅ Деплой одного эндпоинта не затрагивает остальные
✅ Понятная структура для AI-агентов (Copilot видит эталон)

⚠️ Нет автодокументации (OpenAPI/Swagger) — документировать вручную в Issues
⚠️ Проверки безопасности вызываются явно в каждом файле — риск пропустить
→ Митигация: `pr-check.yml` проверяет наличие `validate_origin` и `rate_limit` в новых файлах

