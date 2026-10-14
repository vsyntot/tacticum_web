# ADR-016: общая сборка двух сайтов и версия платформы

Дата: 14.10.2026. Статус: **candidate, локальная реализация начата**.
Основание: принятый [план MSCI](../workflow/plans/2026-10-14-multisite-ci-core-sync.md).
Не заменяет approvals и ограничения [ADR-013](ADR-013-github-actions-delivery-gates.md).

## Контекст

Оба сайта используют общие ядро, БД и `local/`, но прежний FILE_ONLY manifest
охватывает только `/home/bitrix/www`. `sites/divatech/` выпадает из общей сборки.
Штатное обновление Битрикс меняет платформу и иногда схему БД; Git application
diff и сравнение mtime не подтверждают совместимость с этой платформой.

## Реализуемая модель

| Владелец | Источник | Назначение | Доставка |
|---|---|---|---|
| Tacticum public | существующие разделы и exact files по `deploy-scope.json` | `/home/bitrix/www` | `primary/<path>` |
| Shared custom | `local/` по exclusions того же scope | `/home/bitrix/www/local` | один экземпляр в `primary/local/`; smoke s1+s2 |
| Divatech public | `sites/divatech/` | `/home/bitrix/ext_www/divatech.space` | `divatech/<path>` |
| Vendor platform | проверенный production export | изолированный локальный/CI snapshot | отдельный private archive, не application artifact |
| Environment/state | production settings, license, DB, upload, cache | текущие серверные пути | не Git и не FILE_ONLY artifact |

Физических назначений два, логических владельцев три. Ссылки второго корня
`bitrix`, `local`, `upload` проверяются при inventory, но не доставляются как
обычные файлы. Manifest v2 содержит root+path, content SHA-256, size/mode,
scope digest и platform identity. Изменение scope требует нового bootstrap;
старый 399-file BASE не становится multi-root BASE автоматически.

Сравнение различает LOCAL_CHANGE, PROD_CHANGE, CONFLICT и CONVERGED.
CONVERGED требует проверки и возможного продвижения BASE, но не application
write. Rename представляет собой create+delete; удаления должны попасть в
exact approval. Обнаруженный core/schema drift блокирует application release.
Сравнение само по себе всегда выдаёт `productionMutationAllowed=false`.

Архив приложения содержит два namespace; target paths выбирает trusted mapping,
а не tar header. Нормализованы порядок, gzip timestamp/name и tar metadata.
Sitemap lastmod — версионируемый вход `tools/static-sitemap-input.json`; дату
меняют осознанно при изменении содержания, а не при каждом запуске CI.

Platform export имеет отдельный allowlist и exclusions. Не экспортируются
settings/license, `php_interface`, cache, backup, updater temp, OTP/runtime.
Исключённые environment files создаёт отдельный bootstrap. Код в `modules`,
`templates`, `components` требует inventory/review: allowlist не доказывает,
что там никогда не было пользовательских изменений или секретов.

Импорт создаёт новую изолированную копию, проверяя полный manifest и digest.
Он не сравнивает mtime и не накладывает snapshot поверх приложения. Поэтому
удалённого upstream файла нет в новой копии, а прежняя копия остаётся для
восстановления. Ссылки, специальные файлы, дубли и path traversal запрещены.
Коллизии регистра сохраняются в архиве и требуют чувствительного к регистру
тома при импорте; на несовместимом томе импорт прекращается до распаковки.
Пустые каталоги не входят в manifest; runtime directories создаёт bootstrap.

## Границы доверия и открытые решения

PR jobs получают только публичные fixtures. Private platform snapshot и
synthetic DB требуют отдельного trusted integration contour; production
credentials туда не передаются. Установка trusted controller/forced-command
exporter, storage permissions и новые required checks — отдельные reviewable
control-plane changes. Привилегированный controller не исполняет candidate code.

Окончательный core lock с schema/module/runtime provenance, совместимая
synthetic DB, multi-root apply/recovery, independent BASE CAS и полная
integration smoke matrix ещё не приняты как работающий результат.
Репозиторий и внешняя инфраструктура для snapshot/BASE/evidence/backup вынесены
в отдельную тему владельца; этот ADR не выбирает провайдера, endpoint,
retention или миграционный маршрут. Production crypto_key ротирован и принят,
но исторические blobs и полный credential inventory остаются открытым P0.

Переходный v1 workflow теперь классифицирует **весь diff от verified baseline
commit**, включая STATEFUL/control-plane/unknown paths, а не только последний
push. Исходная привязка взята из проверенного Divatech delivery record. Пока
нет v2 bootstrap/finalizer, это адресная защита старого контура, а не обещание
автоматической доставки обоих сайтов. Control-plane changes этой ветки
должны остановить автоматический FILE_ONLY apply до отдельного review.

## Условия принятия

P0–P9 и A1–A12 плана дают отдельные evidence. Fixtures и синтаксическая
совместимость PHP 8.5 не заменяют запуск обоих сайтов на реальном ядре,
проверенную пару core+DB, server-side backup/restore и actual apply.
`productionReady` остаётся false до этих проверок и точного approval.


## Внешняя инфраструктурная граница — 14.10.2026

Репозиторий, snapshot/BASE/evidence/backup storage и связанная миграционная
история не являются частью текущего ADR. Их решения будут оформлены отдельным
accepted plan владельца. До этого команды export/import/build/verify остаются
provider-neutral, а `productionReady=false` сохраняется.
