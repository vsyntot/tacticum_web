# Multisite operational handoff: Tacticum + Divatech

Дата: 16.10.2026
Статус: `partial` — runbook опубликован, owner-run 24-hour check и platform/core contour остаются открытыми.

Текущий baseline: [24-hour baseline evidence](evidence/2026-10-16-multisite-24h-baseline.json) снят 16.10.2026 в `13:15Z` canonical read-only smoke (14/14). Owner-run 24-hour check eligible after `2026-10-17T12:26:00Z`; запись baseline не закрывает gate и не создаёт scheduler.
Владелец процесса: project-manager/PM. Технические исполнители: DevOps, QA, Backend/Frontend owner по назначению на конкретный релиз.

Этот документ передаёт в эксплуатацию проверенный порядок работы с двумя сайтами из одной кодовой базы. Он дополняет, но не отменяет [production deployment governance](production-deployment-governance.md), [post-deploy smoke](post-deploy-smoke.md), [manual release gates](manual-release-gates-runbook.md) и [multisite delivery contract](../../tools/multisite-delivery.md).

## 1. Границы и источники истины

- Репозиторий хранит общий код и оба application-root: основной сайт Tacticum и `sites/divatech/`. Изменение общего `local/` считается затрагивающим оба сайта.
- `bitrix/`, production configuration, database, iblocks, uploads, users, cache, sessions, logs and runtime secrets не входят в обычный application artifact.
- Первый поддерживаемый автоматический контур — `FILE_ONLY`. Любой config/DB/iblock/schema/data change, штатное обновление Bitrix или не подтверждённая совместимость — `STATEFUL` и останавливает FILE_ONLY-процесс.
- GitLab, выбор/миграция хранилищ, platform snapshot storage и backup storage исключены из текущего решения. В этом документе они обозначены как внешние зависимости, но не выбираются.
- Канонические ссылки на текущие результаты: [FILE_ONLY evidence](evidence/2026-10-16-multisite-file-only-release-581ca8.json), [no-op evidence](evidence/2026-10-16-multisite-noop-rerun.json), [plan](plans/2026-10-14-multisite-ci-core-sync.md), [gap analysis](gap-analysis.md).

До любого production write должны одновременно выполняться: release classification, quality/secret checks, immutable artifact и manifest, staging evidence либо bounded waiver, BASE/PROD/CANDIDATE reconciliation, exact plan и approval, lock, backup/restore rehearsal, apply, post-deploy smoke/monitoring и verified dual BASE. Зелёный CI или совпадение файлов сами по себе этого не заменяют.

## 2. Рабочие циклы

| Сценарий | Класс | Разрешённое действие сейчас | Что блокирует продолжение |
|---|---|---|---|
| Изменение application-кода обоих сайтов | `FILE_ONLY`, если classifier и semantic review это подтверждают | PR → clean build → review → exact plan → approved production cycle | Drift, mixed/stateful path, failed smoke, stale BASE |
| Штатное обновление Bitrix на production | `STATEFUL` | Отдельная update session с backup core+DB и проверкой совместимости | Нет согласованного core lock/import и DB-compatible restore |
| Импорт production core в локальную среду/CI | `STATEFUL` / P2 | Только после отдельной инфраструктурной темы и pinned `core.lock.json` | P2 `deferred_external` |
| Воспроизводимая интеграционная среда двух сайтов | P3 | Disposable fixture checks остаются разрешены | P2 и отсутствие pinned runtime/DB profile |
| Divatech SMTP delivery, purge, legal entity details | `STATEFUL` | Отдельный адресный план | Не включать в application FILE_ONLY release |

## 3. Обычная разработка

1. Создать ветку от актуального `origin/main`. Перед началом прочитать `AGENTS.md`, `docs/workflow/README.md`, этот handoff и затронутые ADR.
2. Определить affected roots (`shared`, `tacticum`, `divatech`) и release class. Изменение только документации/fixture tooling не должно случайно становиться application artifact.
3. Локально выполнять быстрые проверки по затронутой области. Для полного delivery-контракта запускать:

   ```bash
   npm run ci:delivery
   npm run ci:multisite-build
   node tools/multisite-contract-self-test.mjs
   python3 tools/multisite-trusted-controller-self-test.py
   ```

4. Для production-like read-only проверки использовать только команды из approved plan. Не копировать весь production webroot в worktree и не переносить `bitrix/backup`, secrets, DB dumps, uploads или runtime logs в Git.
5. В PR указывать affected sites, release class, expected manifest paths, required smoke и открытые STATEFUL dependencies. PR code не получает production credentials.

## 4. FILE_ONLY release: пошаговая процедура

### 4.1 До сборки

- Зафиксировать commit и clean worktree.
- Проверить `node tools/production-deploy-cli.mjs scope-check` и классификатор полного diff относительно verified baseline:

  ```bash
  node tools/production-deploy-cli.mjs scope-check
  node tools/production-deploy-cli.mjs classify --base=<verified-ref> --head=<candidate-ref>
  ```

- Продолжать только при `FILE_ONLY` или `NO_DEPLOYABLE_CHANGE`. `STATEFUL`, `CONTROL_PLANE`, `UNKNOWN`, forbidden path или unresolved semantic impact переводятся в отдельный review.
- Выполнить quality, secret-scan, static sitemap и multisite build checks. Candidate build не получает production key.

### 4.2 Candidate artifact и manifests

Для каждого запуска использовать новый private directory с правами `0700`; output-файлы создавать один раз и не перезаписывать:

```bash
node tools/static-sitemap-generate.mjs
node tools/multisite-cli.mjs build \
  --platform=/private/core.lock.json \
  --manifest=/private/release/candidate.json \
  --output=/private/release/candidate.tar.gz
sha256sum /private/release/candidate.tar.gz /private/release/candidate.json
```

Если platform lock отсутствует или просрочен, это P2/P3 blocker. Не подставлять «похожую» локальную версию Bitrix и не включать `bitrix/` в application artifact.

### 4.3 Read-only PROD и three-way plan

Получить server manifest через manifest-only forced-command key; не использовать personal key, root shell, password helper или произвольный SSH command. Затем выполнить локальную сверку:

```bash
node tools/multisite-cli.mjs plan \
  --base=/private/release/BASE.json \
  --prod=/private/release/PROD.json \
  --candidate=/private/release/candidate.json \
  --output=/private/release/plan.json
```

Ожидаемый результат — redacted plan с `base`, `prod`, `candidate`, `driftResolved`, affected roots, explicit operations and `productionMutationAllowed=false` до отдельного approval. Любой `PROD_CHANGE`, `CONFLICT`, missing/unstable BASE или unexpected path останавливает выпуск и создаёт reconciliation task.

### 4.4 Exact approval и controlled apply

В approval должны быть связаны `plan_id`, artifact digest, candidate/PROD/BASE hashes, scope/controller versions, expiry, allowed operations/deletions, monitoring window и rollback owner. После approval:

1. получить общий server lock;
2. повторить final PROD manifest и сравнить с approved hash;
3. пройти disk/inode preflight, backup changed scope и restore rehearsal;
4. загрузить только exact plan/artifact/approval через trusted wrappers;
5. вызвать apply только по approved plan ID;
6. при `applied_unverified` не продвигать BASE и не считать выпуск завершённым.

Controller применяет только explicit create/replace/delete/rename в двух mapped roots. Широкий `rsync --delete`, смена `bitrix/`, DB, config или uploads этим runbook не разрешены.

### 4.5 После apply

После teardown production credentials выполнить без них:

```bash
npm run release:public-precheck:prod
npm run seo:check:prod
npm run browser:console:prod
python3 tools/multisite-public-smoke.py
```

Smoke включает Tacticum и Divatech: canonical/robots/sitemap, public pages, assets, 404, admin canonical redirect, forms rendering/security boundaries и mobile viewport. Реальные лиды не создаются; controlled success-flow выполняется только по manual gate с safe evidence.

Записать monitoring window (минимум три последовательных read-only раунда для exact FILE_ONLY contour), stop thresholds и результат. Только при совпадении post-manifest с candidate, успешных smoke/monitoring и финализаторе с двумя совпадающими BASE копиями статус меняется на `verified`.

## 5. Штатное обновление Bitrix (STATEFUL)

Это не shortcut для application delivery.

1. Оператор объявляет update session и прекращает новые application releases.
2. Проверяет, что нет активного deploy lock и нет другого updater; фиксирует current core/module versions и DB schema profile.
3. Делает согласованный backup пары core+DB и проверяет restore readiness. Файловый архив без DB не является rollback для STATEFUL update.
4. Запускает штатный Bitrix updater на production.
5. Проверяет оба сайта, admin canonical route, cache/OPcache policy, module versions, DB migrations and public smoke. При несовместимости не откатывать только файлы ядра на новую схему: использовать согласованную пару core+DB и отдельный recovery plan.
6. После стабильного окна экспортировать allowlisted vendor snapshot и metadata `core.lock.json` через отдельный P2-контур. Snapshot не содержит secrets, licence, DB dump, users/uploads, cache или logs.
7. До принятия P2/P3 не импортировать snapshot в Git/CI и не разрешать application release, который зависит от новой platform identity.

## 6. Reconciliation hotfix и recovery

### Out-of-band production change

Read-only manifest сравнивается с verified BASE. При изменении production-owned/repo-owned файла выпуск останавливается; путь, observed hash, owner decision, plan ID и действие фиксируются без raw content. Исправление делается в clean branch, review и новый artifact/plan.

### Apply failure

- Не запускать повторный apply вслепую.
- Сохранить redacted phase journal и controller evidence.
- Удерживать lock до решения recovery owner.
- Для FILE_ONLY вызвать только адресный `recover <plan_id>` после проверки backup/rehearsal; восстановить изменённые файлы и удалить только добавленные этим планом.
- Повторить post-recovery manifest, cache policy, public smoke и monitoring. BASE не продвигать при `failed`/`applied_unverified`.

### Smoke или monitoring failure

Остановить продвижение BASE и открыть incident/release follow-up. Rollback требует отдельного решения владельца и повторяет manifest/lock/recovery/smoke gates. Автоматический rollback и автоматическое расширение scope запрещены.

### CI failure

Classify failure as code, tooling/control-plane, environment or external platform. Не ретраить production job с тем же stale artifact. Сначала сохранить run URL, commit, job, redacted error and next owner; затем исправить в branch и повторить clean build. Production credentials не выдаются для диагностики PR.

### BASE/finalizer failure

Статус остаётся `applied_unverified`; не редактировать BASE вручную и не удалять предыдущую verified copy. Проверить CAS/sequence и независимые копии, затем выполнять recovery по finalizer evidence. Разрешение продолжить следующий release требует новой verified BASE.

## 7. 24-hour post-release check

Этот чеклист запускается owner-ом вручную через 24 часа после verified release; запись в этом документе не создаёт scheduler. Для каждого выпуска создать redacted evidence рядом с release record:

```json
{
  "release_id": "<plan_id>",
  "checked_at": "<ISO-8601 with timezone>",
  "owner": "<role or approved handle>",
  "sites": {
    "tacticum": {"status": "passed", "http_5xx": false, "new_console_errors": false, "forms": "not_checked_or_passed"},
    "divatech": {"status": "passed", "http_5xx": false, "new_console_errors": false, "forms": "not_checked_or_passed"}
  },
  "base_sequence": "<observed sequence>",
  "smtp_delivery": "not_in_scope_or_separately_evidenced",
  "incidents": [],
  "result": "passed|follow_up|rollback_review"
}
```

Проверить:

- HTTP 5xx/4xx spike на канонических страницах и asset/API endpoints;
- Tacticum и Divatech canonical/robots/sitemap, admin redirect и формы как минимум на рендер/controlled error state;
- новые browser/console errors и cache/OPcache anomalies;
- отсутствие незапланированного production drift и сохранность dual BASE sequence;
- Divatech SMTP, purge и legal details только если отдельный STATEFUL plan уже открыл эти gates.

Stop thresholds: любой новый 5xx, security boundary regression, form data loss, unexpected deletion, BASE divergence, unapproved drift или повторяемая console/runtime error переводят результат в `follow_up`/`rollback_review` и блокируют следующий release.

## 8. Cleanup и handoff record

После завершения local/CI run удалить только временные candidate archives, extracted directories, askpass files, SSH agents and temporary logs. Не удалять защищённые release evidence, verified BASE copies или backups, которые имеют retention decision. На сервере temporary upload files удаляются wrapper/controller после успешной фиксации evidence; private release state остаётся только в согласованном audit scope.

В release record всегда сохранять: commit/PR, release class, plan ID, artifact/candidate/PROD/BASE hashes, lock/apply/finalizer status, smoke/monitoring manifests, rollback outcome, 24-hour check owner/time/result и открытые follow-ups. Не сохранять PII, cookies, sessid, tokens, raw payloads, raw responses, passwords или private keys.

## 9. Current handoff status

- `FILE_ONLY` exact production cycle for both roots: verified, evidence sequence 2.
- P9 bounded adoption/no-op: verified for accepted scope.
- P2 platform/core import: `deferred_external`.
- P3 pinned integration environment: blocked by P2 and external runtime/DB contract.
- P8 full end-to-end rehearsal: not closed; fixture tests are not production integration evidence.
- P10 operational handoff: this runbook is the documented slice; owner-run 24-hour check and recurring operational ownership remain open.
- GitLab/storage migration and Divatech SMTP/purge/legal: outside this current plan or separate STATEFUL gates.
