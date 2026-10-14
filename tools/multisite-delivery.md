# Multisite tooling: локальная подготовка, версия 2 candidate

Состояние и незакрытые gates — в [плане MSCI](../docs/workflow/plans/2026-10-14-multisite-ci-core-sync.md).
Контракт — [ADR-016](../docs/adr/ADR-016-multisite-platform-delivery.md).
Этот набор пока не устанавливает production controller и не выполняет deploy.

## Быстрая проверка

```sh
node tools/multisite-contract-self-test.mjs
node tools/production-release-gate-self-test.mjs
python3 tools/production-artifact-build-self-test.py
python3 tools/platform-snapshot-self-test.py
python3 tools/repository-security-self-test.py
python3 tools/security/crypto-key-rotation-test.py
python3 tools/security/crypto-key-rotation-apply-test.py
```

Security guard читает **index**, а не worktree. Перед последней проверкой
проиндексировать только предназначенные для commit файлы и выполнить
`python3 tools/repository-security-check.py`. Guard не проверяет всю историю
и не является доказательством отсутствия любых возможных секретов.
Reviewed fixture exceptions привязаны к exact content SHA-256 и rule;
environment-owned path не может быть исключён из запрета.

## Сборка приложения

После подготовки проверенного platform lock:

```sh
node tools/static-sitemap-generate.mjs
node tools/multisite-cli.mjs build --platform=/private/core.lock.json \
  --manifest=/private/release/candidate.json --output=/private/release/candidate.tar.gz
node tools/multisite-cli.mjs plan --base=/private/BASE.json \
  --prod=/private/PROD.json --candidate=/private/release/candidate.json \
  --output=/private/release/plan.json
```

Выходные файлы должны быть новыми. Минимальная identity для локального tooling:
`schemaVersion=1`, `snapshotSha256`, `vendorManifestSha256`, `schemaProfile`,
`phpVersion`. Синтетический lock из fixtures нельзя использовать для реального
release. Полный platform lock и trusted attestation ещё входят в P2/P3.
Build доказывает content integrity, но не соответствие произвольного worktree
Git commit: release build должен выполняться из clean checkout в trusted CI.

`classify --paths=/private/changed-paths.json` принимает JSON-массив путей.
Input должен покрывать diff от последнего verified commit; HEAD^ недостаточен.
STATEFUL, control-plane, forbidden и unknown возвращают ненулевой exit code.

## Platform snapshot

Python 3.9+; только filesystem tooling, без SSH, паролей, БД и автоматического
запуска updater. Production-read/export wrapper и стабильное update window
должны быть отдельно проверены. Не запускать exporter из PR с production key.

```sh
python3 tools/platform_snapshot.py inventory --root=/isolated/source/bitrix \
  --output=/private/vendor-inventory.json
python3 tools/platform_snapshot.py export --root=/isolated/source/bitrix \
  --expected-manifest=REVIEWED_MANIFEST_SHA256 --output=/private/new-snapshot
python3 tools/platform_snapshot.py verify \
  --archive=/private/new-snapshot/vendor.tar.gz \
  --manifest=/private/new-snapshot/vendor-manifest.json \
  --expected-sha256=EXPECTED_ARCHIVE_SHA256
python3 tools/platform_snapshot.py import \
  --archive=/private/new-snapshot/vendor.tar.gz \
  --manifest=/private/new-snapshot/vendor-manifest.json \
  --expected-sha256=EXPECTED_ARCHIVE_SHA256 --output=/isolated/new-platform
```

Importer никогда не перезаписывает существующий каталог. Digest должен прийти
из принятого lock/evidence, а не только из скачанного рядом непроверенного receipt.
Export требует неизменного reviewed manifest и повторяет inventory после чтения.
Это проверка стабильности файлов, не блокировка штатного updater и не проверка
совместимости схемы БД. Snapshot пока private candidate; публикация и activation
требуют завершения P0/P2/P3. Временный import staging удаляется при отказе.
Проверенные snapshots и прежние копии сохраняются как recovery data; они не
считаются временными архивами, которые можно сразу удалить.

## Ротация ключа

`security/crypto-key-rotation.py` поддерживает только подготовку. Private backup,
candidate и plan остаются вне Git/webroot. Подготовлен [отдельный exact STATEFUL plan](../docs/workflow/plans/2026-10-14-crypto-key-rotation-execution.md) и apply operator с шестью isolated tests. Ротация выполнена и verified: technical smoke/monitoring и owner-confirmed
admin login прошли, две scoped STATEFUL BASE copies совпадают. Для будущих
ротаций по-прежнему нужны exact approval, контроль PHP/cron writes и smoke
обоих сайтов. Наличие пустых таблиц в предварительной проверке не гарантирует,
что они останутся пустыми при apply. Возврат раскрытого ключа не считается
обычным безопасным rollback.


## Независимые от CI-провайдера команды

- `npm run ci:delivery` — offline contracts и отрицательные сценарии.
- `npm run ci:php` — PHP syntax приложения/инструментов и s1/s2 contracts.
- `npm run ci:multisite-build` — две сборки committed HEAD в новых каталогах,
  каждый раз `npm ci`, CSS и sitemap generation, оба корня, сравнение digest.
  Локальные staged/unstaged правки в такую сборку не попадают. Для проверки
  другой ревизии: `python3 tools/ci/multisite-build-check.py --revision=COMMIT`.
- `npm run smoke:multisite` — read-only HTTPS проверки двух production сайтов.
  Наблюдение: `python3 tools/multisite-public-smoke.py --duration=900
  --output=/private/new-smoke.json`. Формы не отправляются, admin login не проверяется.

Clean-build job использует явно synthetic platform identity и проверяет только
упаковку приложения; это не Bitrix integration и не production release.
В CI сохраняется только receipt; временные checkout/node_modules/архивы удаляются.
Проверки не получают production credentials и не требуют snapshot storage.
Выбор/настройка хранилищ отложены владельцем до переноса в GitLab; эти команды
переносятся в GitLab CI без изменения их реализации.


## Репетиция файловых операций

`python3 tools/multisite-file-transaction-self-test.py` выполняет реальные
create/replace/delete в двух временных каталогах. Проверяются journal intent,
backup/restore rehearsal, восстановление новым экземпляром после сбоя между
корнями и после записи, конфликты recovery, повреждение backup, symlink/path
boundaries, CONVERGED без записи и отказ повторного apply.

`multisite_file_transaction.py` — candidate kernel без production CLI. Он не
заменяет trusted controller: scope/full-manifest/platform/approval validation,
общие production locks, безопасная распаковка, disk checks, cache invalidation,
smoke и dual BASE finalization остаются обязательными во внешнем controller.
Kernel принимает только уже проверенные операции; его собственная блокировка
сериализует один state directory, а не конкурирующие production releases.
Тесты не выдают `productionApproved=true`. Атомарная замена отдельных файлов
не означает атомарной доставки двух корней.

`python3 tools/multisite-trusted-controller-self-test.py` добавляет изолированный
trusted-controller contour: binding plan/approval/artifact/manifests,
canonical roots, общий lock, disk preflight, безопасная распаковка, backup и
restore rehearsal, create/replace/delete/rename, failure journal и recovery.
Успешный apply остаётся `applied_unverified`; скрипт требует `--isolated` и не
имеет SSH/production transport. `multisite-release-finalizer.py` принимает
только controller evidence после smoke/monitoring/cache evidence и поднимает
две совпадающие BASE-копии с CAS, монотонной последовательностью и recovery
при частичной записи. Это проверяемый P6/P7 fixture contour, а не
установленный production controller.

```sh
python3 tools/multisite-trusted-controller-self-test.py
npm run multisite:controller:self-test
```


## Текущий GitHub release gate

Перед получением production credentials выполняется `--classify-only` по всему
Git diff с прежнего verified baseline. Только FILE_ONLY продолжает legacy
inspection; control-plane/stateful/forbidden/unknown получают явный
`separate_review_required`, без application deployment. Green quality/skip не
является verified release. Full comparison gate по BASE/PROD/CANDIDATE сохраняет
отказ при drift. Пока baseline не принят после изменений tooling/platform,
последующий app commit не маскирует эти изменения и также не обходит review.
