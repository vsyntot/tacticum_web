# Настройка AI/Telegram сервисов

## Где менять URL

URL сервисов задаются в конфигурации Bitrix: `.settings_extra.php`, секция `ai_services`.

Поля:
- `AI_SERVICE_BASE_URL` — базовый URL для AI‑эндпоинтов (например, `https://ai.example.com`).
- `TELEGRAM_RESOLVER_URL` — базовый URL для резолвера Telegram‑ссылок.

Пример:

```php
'ai_services' =>
array (
  'value' =>
  array (
    'AI_SERVICE_BASE_URL' => 'https://5.35.90.193:8000',
    'TELEGRAM_RESOLVER_URL' => 'https://5.35.90.193:8000',
  ),
  'readonly' => false,
),
```

После изменения настроек очистите кэш Bitrix (если используется файловый/опционный кэш конфигурации).
