# ADR-003: ID инфоблоков только через tacticum_rest_get_iblock_id()

**Статус:** ✅ Принято
**Дата:** 2025
**Автор:** Architect

---

## Контекст

Bitrix-инфоблоки имеют автоинкрементные числовые ID, которые:
- **отличаются** между dev/staging/production окружениями
- **меняются** при переносе сайта на другой хостинг
- **непонятны** без контекста (`IBLOCK_ID => 5` — что это?)

Хардкод ID в коде приводит к поломке при смене окружения и усложняет сопровождение.

---

## Решение

Все обращения к инфоблокам используют **символьный ключ** через хелпер:

```php
// ❌ Запрещено — хардкод
$res = CIBlockElement::GetList([], ['IBLOCK_ID' => 5], ...);

// ✅ Правильно — через конфиг
$iblockId = tacticum_rest_get_iblock_id('offer');
$res = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId], ...);

// ✅ Для GET-эндпоинтов — ещё короче
$iblockId = tacticum_api_bootstrap('offer'); // внутри вызывает get_iblock_id
```

**Маппинг ключ → ID** хранится в `tacticum_config.php`, секция `iblocks`:

| Ключ | ID (prod) | Назначение |
|---|---|---|
| `offer` | 5 | Коммерческие предложения (calcrequests) |
| `vacancies` | 7 | Вакансии |
| `feedback` | 9 | Отзывы / feedback |
| `faq` | 10 | FAQ |
| `rates` | 11 | Тарифы |
| `services` | 12 | Услуги |
| `cases` | 13 | Кейсы / портфолио |
| `team` | 18 | Команда |
| `policies` | 19 | Политики / legal контент |
| `aiagents` | 20 | AI agents |

`local/php_interface/init.php` для `calcrequests.*` использует ключ `offer`.
Публичные страницы с legacy `IncludeComponent` остаются зоной планового рефакторинга.

---

## Альтернативы

| Вариант | Почему отклонён |
|---|---|
| Константы (`define('OFFER_IBLOCK_ID', 5)`) | Всё равно хардкод, только в другом месте |
| Хранение в `bitrix/.settings.php` | Смешивает с системным конфигом Bitrix |
| Поиск инфоблока по коду в БД | Лишний запрос в БД на каждом запросе |

---

## Последствия

✅ Смена ID при переносе — правка только в `tacticum_config.php`
✅ Код читаем: `get_iblock_id('cases')` самодокументируется
✅ `pr-check.yml` блокирует хардкод в изменённых runtime-файлах `local/rest`, `local/api`, `local/php_interface`
✅ `pr-check.yml` предупреждает о хардкоде в публичных legacy-страницах

⚠️ Если ключ отсутствует в конфиге — `get_iblock_id()` возвращает `0`, запрос упадёт
→ Митигация: `tacticum_api_bootstrap()` проверяет `$iblockId <= 0` и возвращает 500
