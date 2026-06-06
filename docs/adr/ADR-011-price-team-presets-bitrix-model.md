# ADR-011: Bitrix Team Presets Model For `/price/`

Дата: 06.06.2026

Статус: Принято

## Контекст

Страница `/price/` содержит team/staff configurator. Тарифные роли и ставки уже приходят из инфоблока `rates`, но быстрые пресеты команды исторически были разнесены между PHP-шаблоном и JS:

- кнопки пресетов рендерились из массива в `parts/catalog.php`;
- составы пресетов жили в `price-configurator-utils.js`;
- применение пресета искало карточки тарифов по ключевым словам в названии/категории;
- staff-order endpoint получал `team_preset` и фактический `workers_json`, но не имел серверного источника метаданных пресета.

Отдельный блок `/price/` `workstreams` уже перенесен в `page_sections/page_blocks` и не должен становиться частью узкого инфоблока пресетов, если речь идет только о контентных карточках страницы.

## Решение

1. Ввести доменную модель team presets для функционального состава команды, а не для generic page copy.
2. Целевая Bitrix-модель использует два инфоблока:
   - `team_presets` для карточек/кнопок пресетов;
   - `team_preset_roles` для состава пресета.
3. `team_presets` хранит:
   - `CODE` как стабильный публичный slug (`mvp`, `discovery`, `support`, `qa-burst`);
   - `NAME` как label;
   - `PREVIEW_TEXT` как краткое описание;
   - `SCENARIO`;
   - `DEFAULT_WORKLOAD`;
   - `RECOMMENDED_DURATION`;
   - `VERSION`;
   - `ANALYTICS_CODE`.
4. `team_preset_roles` хранит:
   - `PRESET` relation to `team_presets`;
   - `RATE_ELEMENT` relation to `rates`;
   - `PREFERRED_LEVELS` as ordered multiple string values;
   - `QUANTITY`;
   - `REQUIRED`;
   - `ROLE_KEY` for admin diagnostics;
   - `FALLBACK_KEYWORDS` only as migration/rollback aid.
5. Цены и названия ролей не дублируются в пресетах. Источник ставок остается `rates`.
6. Runtime для `/price/` читает пресеты через service-layer `Tacticum\Price`, а не из шаблона или inline JS.
7. JS получает JSON-модель, сгенерированную PHP, и применяет пресет по `RATE_ELEMENT`/`data-rate-ids`. Keyword matching остается только fallback-механикой для transitional mode.
8. Runtime source управляется секцией config `price`:

```php
'price' => [
    'team_presets_source' => 'fallback', // fallback|auto|bitrix
    'team_presets_cache_ttl' => 300,
    'allow_team_presets_fallback' => true,
],
```

9. `fallback` сохраняет существующие четыре пресета только как безопасный rollout/rollback path до создания инфоблоков на окружении.
10. `auto` сначала пытается прочитать Bitrix-модель, затем использует fallback, если он разрешен.
11. `bitrix` использует только Bitrix-модель; если строки отсутствуют или связи сломаны, пресеты не подменяются legacy-массивом.
12. Health/config validation включает scope `price` and validates source mode, cache TTL and required iblock keys when Bitrix source is mandatory.
13. CLI migration/check tooling создает схему, seed-ит текущие пресеты and validates strict readiness without raw PII output.
14. Staff-order текст обогащается серверно разрешенными label/source/version пресета, но внешний payload shape не меняется.
15. `workstreams` остается в `page_sections/page_blocks`. Если карточки workstreams должны применять пресет, связь добавляется отдельным полем/CTA behavior к page-content block, но не переносом этих карточек в `team_presets`.

## Последствия

Плюсы:

- пресеты становятся редактируемыми без деплоя после Bitrix migration;
- PHP и JS используют один источник данных;
- применение состава перестает зависеть от текста карточек тарифов в целевом режиме;
- заявки получают более понятное описание примененного пресета;
- rollout безопасен: до конфигурации Bitrix сохраняется legacy fallback.

Минусы и ограничения:

- нужен запуск migration на каждом Bitrix окружении;
- нужно обновить ignored `local/php_interface/include/tacticum_config.php` после apply;
- при source=`bitrix` сломанная связь `RATE_ELEMENT` может скрыть/не применить часть пресета, что должно ловиться checker-ом;
- fallback остается техническим долгом до подтверждения strict Bitrix readiness.

## Не Делаем В Этом Решении

- не переносим `workstreams` из `page_sections/page_blocks`;
- не меняем цены или структуру `rates`;
- не отправляем новые top-level structured fields во внешний AI/upstream endpoint;
- не меняем REST response contract `/local/rest/tacticum_sale_staff.php`;
- не делаем визуальный редизайн `/price/`.
