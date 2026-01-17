# Tacticum Web

## Настройка окружений

Основная конфигурация для API/REST находится в файле:

`/local/php_interface/include/tacticum_config.php`

В файле задаются:

- `iblocks`: идентификаторы инфоблоков для кейсов/FAQ/ставок/сервисов/offer.
- `base_urls`: базовые URL внешних сервисов (например, `AI_SERVICE_BASE_URL`, `TELEGRAM_RESOLVER_URL`).

При необходимости обновите значения под окружение и очистите кеш Битрикс.
