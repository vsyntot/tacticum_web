# ADR-002: Конфигурация через tacticum_config.php, не через .env

**Статус:** ✅ Принято
**Дата:** 2025
**Автор:** Architect

---

## Контекст

Проект требует хранения конфигурации:
- ID инфоблоков Bitrix (разные на dev и prod)
- URL внешнего AI-сервиса
- Списки разрешённых IP для CORS и API-доступа
- Настройки доверенных прокси

Нужно выбрать способ хранения конфига, совместимый с Bitrix и удобный для AI-агентов.

---

## Решение

Конфиг хранится в `local/php_interface/include/tacticum_config.php` как PHP-массив:

```php
<?php
return [
    'iblocks' => [
        'cases'    => 13,
        'faq'      => 10,
        'rates'    => 11,
        'services' => 12,
        'offer'    => 5,
    ],
    'base_urls' => [
        'AI_SERVICE_BASE_URL'    => 'https://...',
        'TELEGRAM_RESOLVER_URL'  => 'https://...',
    ],
    'rest' => [
        'allow_no_origin'  => false,
        'allowed_origins'  => ['tacticum.ru', '*.tacticum.ru'],
        'allowed_ips'      => ['...'],
        'trusted_proxies'  => [],
    ],
];
```

**Доступ только через хелперы** (`rest_helpers.php`):
- `tacticum_rest_get_config()` — весь конфиг (кешируется статически)
- `tacticum_rest_get_config_section('rest')` — секция
- `tacticum_rest_get_iblock_id('cases')` — ID инфоблока
- `tacticum_rest_get_ai_setting('AI_SERVICE_BASE_URL')` — URL сервиса

**Файл исключён из git** (`.gitignore`) и из rsync-деплоя (`--exclude` в `deploy.yml`).
Хранится на сервере отдельно — вне репозитория.

---

## Альтернативы

| Вариант | Почему отклонён |
|---|---|
| `.env` + vlucas/phpdotenv | Дополнительная зависимость; Bitrix не поддерживает нативно |
| `bitrix/.settings.php` | Смешивает кастомный конфиг с системным Bitrix; сложнее читать |
| `Configuration::getValue()` (Bitrix) | Данные в БД, труднее версионировать и читать |
| Переменные окружения сервера | Не портируемо между хостингами, сложнее для AI-агентов |

---

## Последствия

✅ Нативный PHP — нет внешних зависимостей
✅ Структурированный массив — IDE и Copilot дают автодополнение
✅ Статическое кеширование в `tacticum_rest_get_config()` — нет повторного чтения диска
✅ Изолирован от ядра Bitrix

⚠️ Нужно вручную создать файл на новом сервере при первом деплое
⚠️ Нет валидации схемы конфига при загрузке
→ Митигация: хелперы возвращают fallback-значения при отсутствии ключа

