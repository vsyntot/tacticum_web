# Platform core lock contract (`core.lock.json`)

Дата: 16.10.2026
Статус: `candidate`; production/CI не переключены на этот contract.
Владелец: Architect + DevOps; приёмка: PM, QA и владелец platform update.

Документ фиксирует минимальный machine-readable контракт для будущего P2-импорта ядра Bitrix из production в локальную интеграционную среду и CI. Он не выбирает GitLab, snapshot/BASE/evidence/backup storage, transport или retention. До отдельного внешнего решения и фактического export/update window `productionReady=false`.

## Зачем нужен отдельный lock

Текущий multisite builder принимает server-declared identity с четырьмя полями: `snapshotSha256`, `vendorManifestSha256`, `schemaProfile`, `phpVersion`. Это достаточно для проверки, что candidate объявляет одну и ту же identity, но недостаточно, чтобы доказать совместимость штатно обновлённого ядра с module set, PHP extensions, DB engine/version и schema contract.

`core.lock.json` связывает application candidate с конкретным immutable vendor snapshot и совместимой парой runtime/schema. Lock не является архивом, DB dump, секретом или разрешением на deploy.

## Формат v2 candidate

Пример содержит только synthetic/redacted значения:

```json
{
  "schemaVersion": 2,
  "lockId": "core-2026-10-16-<stable-id>",
  "snapshotId": "prod-core-2026-10-16-<stable-id>",
  "snapshotSha256": "<64 lowercase hex>",
  "vendorManifestSha256": "<64 lowercase hex>",
  "exportPolicySha256": "<64 lowercase hex>",
  "moduleVersions": {
    "main": "<version>",
    "catalog": "<version>",
    "iblock": "<version>"
  },
  "runtime": {
    "phpVersion": "8.5.11",
    "extensions": ["ctype", "curl", "dom", "fileinfo", "json", "mbstring", "mysqli", "openssl", "zip"],
    "db": {"engine": "mysql", "version": "8.4.11"}
  },
  "schemaCompatibilityId": "<64 lowercase hex>",
  "provenance": {
    "source": "production-updater",
    "capturedAt": "2026-10-16T12:00:00Z",
    "updaterCompletedAt": "2026-10-16T11:45:00Z",
    "exportRulesVersion": "2026-10-14.candidate.1"
  },
  "databaseIncluded": false,
  "productionReady": false
}
```

Нормативные правила:

- все digest-поля — lowercase SHA-256; `snapshotSha256` относится к immutable vendor archive, `vendorManifestSha256` — к полному allowlisted vendor manifest, `exportPolicySha256` — к зафиксированной export policy;
- `lockId` и `snapshotId` стабильны для одной публикации и не содержат hostnames, passwords, tokens или PII;
- `moduleVersions` — полный allowlisted набор штатных и Marketplace-модулей, отсортированный по ключу; неизвестный модуль или custom file останавливает export;
- `runtime.extensions` нормализован и отсортирован; PHP и DB versions — фактически проверенные версии, а не целевые значения из локального окружения;
- `schemaCompatibilityId` связывает lock с проверенной schema/profile и backup/restore rehearsal; одного version string недостаточно;
- `provenance.source` должен быть `production-updater` или явно принятым эквивалентом; `capturedAt` и `updaterCompletedAt` имеют timezone;
- `databaseIncluded` всегда `false`: DB backup хранится и проверяется отдельно, не попадает в lock или application artifact;
- `productionReady` остаётся `false` до принятия P2/P3/P8 и точного STATEFUL решения; поле не может быть поднято сборочным job.

## Capture и import gates

1. **Update window.** Администратор завершает штатный Bitrix updater, фиксирует core/module versions и schema profile; параллельный updater или изменяющийся manifest останавливает capture.
2. **Allowlist inventory.** `tools/platform_snapshot.py inventory` строит canonical vendor manifest. `bitrix/backup`, cache, managed cache, updater temp, OTP, `php_interface`, settings/license, logs, keys, DB dumps, uploads, users и runtime files исключены.
3. **Customisation review.** Любой custom file под `bitrix/` или Marketplace module вне allowlist выносится в отдельное решение; автоматический импорт не «поглощает» его в vendor snapshot.
4. **Archive.** `tools/platform_snapshot.py export` создаёт новый archive с digest, deterministic metadata и receipt. При изменении дерева во время export операция прерывается и повторяется только после стабильного окна.
5. **Metadata join.** Owner-ы подтверждают module versions, PHP extensions, DB profile, schema compatibility и updater provenance. Без этих полей v2 lock не выпускается.
6. **Isolated import.** `tools/platform_snapshot.py import` проверяет archive digest, полный manifest, типы, modes, paths, symlinks, duplicates, traversal и case-sensitivity; новая копия создаётся рядом с проектом и никогда не накладывается поверх live `bitrix/`.
7. **Compatibility.** В disposable environment запускаются оба сайта с imported core и synthetic DB; проверяются Bitrix bootstrap, module loading, schema prerequisites, cache policy и multisite smoke. Это не production approval.
8. **Promotion.** Только после P2/P3/P8 acceptance lock может стать trusted input для builder/CI. Current v1 production identity остаётся совместимым переходным форматом до отдельного migration decision.

## Fail-closed условия

Импорт и application release останавливаются при missing/unknown module, изменении vendor manifest, mismatch archive/manifest digest, stale updater state, неполном DB/schema profile, несовместимом PHP extension, symlink/special file/path traversal, case collision, `databaseIncluded=true`, `productionReady=true` без evidence или расхождении lock с PROD platform identity.

Файловый rollback ядра на новую DB schema запрещён. Recovery для STATEFUL update использует согласованную пару core+DB и отдельный backup/restore plan.

## Acceptance для P2

P2 можно переводить из `deferred_external` только когда есть:

- один immutable production vendor snapshot и полный manifest без secrets/PII;
- подписанный/зафиксированный v2 lock с module/runtime/schema/provenance fields;
- проверенный isolated import в новую директорию, включая удаления upstream-файлов;
- совместимая synthetic DB или обезличенный approved fixture без production credentials;
- evidence updater completion, backup/restore rehearsal, import cleanup и verification обоих сайтов;
- принятое место хранения и права доступа для snapshot/lock/BASE/evidence/backup в отдельном инфраструктурном решении.

До этого состояния текущие fixture tests и synthetic `platformEvidence` остаются только packaging evidence. Они не закрывают P2/P3/P8 и не разрешают production mutation.
