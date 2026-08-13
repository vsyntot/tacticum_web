# Production Deployment Governance

Статус: `accepted`; executable enforcement находится в реализации.
Дата вступления в силу: 2026-08-13.

Этот документ — операционный источник истины для любой сверки и записи в production `tacticum.ru`. Архитектурное решение закреплено в [ADR-013](../adr/ADR-013-github-actions-delivery-gates.md), детальный E2E design и план внедрения — в [production-drift-reconciliation-plan.md](../project_management/2026-08-13-github-actions-recovery/production-drift-reconciliation-plan.md).

До реализации и проверки перечисленных ниже controls production deploy считается заблокированным. Зелёный quality gate сам по себе не разрешает merge/deploy.

## Область действия и роли

- Любая задача с production deploy использует `Security / Integration Lane`; для P0/P1 incident дополнительно применяется `Incident Lane` и break-glass policy.
- PM отвечает за scope, release class, решения по drift, approval и итоговый evidence.
- DevOps отвечает за artifact, trusted controller, lock, backup/restore, apply и технический evidence.
- QA отвечает за staging/waiver, smoke matrix и post-deploy verification.
- Architect нужен при изменении deploy scope, trust boundary, rollback, stateful lane или server-owned paths.
- Пользователь/назначенный reviewer отдельно разрешает точный production plan; исполнитель не может self-approve или self-waive.

## Классификация релиза

Первый поддерживаемый контур — только `FILE_ONLY`: изменения repo-owned файлов из deploy scope без изменения production config, БД, инфоблоков, schema/runtime data и без migration/seed/backfill/finalize.

Любая зависимость от config, DB, iblock/schema/data, необратимого преобразования или неподтверждённого внешнего контракта классифицирует релиз как `STATEFUL` и блокирует этот контур. Для него нужен отдельный migration plan с forward/backward compatibility, data/config backup, verification и rollback. Файловый backup не считается rollback для `STATEFUL`.

## Обязательный release flow

```text
Issue / release scope
  → FILE_ONLY / STATEFUL classification
  → quality + secret scan
  → immutable artifact + candidate manifest
  → staging evidence или approved bounded waiver
  → pre-deploy health baseline
  → trusted read-only PROD manifest
  → BASE / PROD / CANDIDATE reconciliation
  → exact plan + dry-run + user approval
  → server lock + final scan + backup/restore rehearsal
  → exact artifact apply + cache clear + post-apply manifest
  → unprivileged smoke + monitoring
  → verified dual BASE или rollback
```

### 1. Scope, artifact и secrets

- Один machine-readable deploy-scope определяет artifact, manifest, dry-run, apply, backup и rollback.
- Scope различает authoritative directories, exact root files, generated files, explicit tombstones и server-owned exclusions.
- Artifact собирается один раз без production secrets, получает digest и после сборки не изменяется.
- Production secrets не выдаются `pull_request`/`pull_request_target`; privileged controller не исполняет candidate scripts и не запускает `npm ci`.
- Личный SSH-ключ пользователя не переносится в GitHub и не используется штатным production tooling. Он допустим только для явно подтверждённого bootstrap/break-glass. Постоянный local inventory использует отдельный passphrase-protected forced-command read-only key; CI read-only и write keys также отдельны и ограничены server-side wrappers.

### 2. Production drift

- Git — источник истины для repo-owned кода, но необъяснённый production drift всегда блокирует запись.
- При доказанном `BASE` выполняется three-way `BASE ↔ PROD ↔ CANDIDATE`; при `BASE_UNKNOWN` разрешён только two-way `PROD ↔ CANDIDATE` bootstrap.
- Нужные production-правки переносятся через clean worktree/branch, review и повторный quality/scan.
- Отклонение или reclassification drift фиксируется durable decision с path, observed PROD hash, action, approver, plan ID и scope version.
- Raw production content, secrets и PII не попадают в Git, Actions logs или artifacts.

### 3. Approval и apply

- Пользователь видит redacted diff summary и точный dry-run до write approval.
- Approval связан с `plan_id`, artifact digest, PROD manifest hash, scope/controller versions, expiry и разрешёнными deletions/tombstones.
- Server-side exclusive lock удерживается от финального PROD manifest до post-apply manifest.
- Final PROD hash обязан совпасть с утверждённым plan; иначе plan устарел.
- До apply обязательны disk/inode checks, полный backup изменяемого scope и restore rehearsal в безопасный временный каталог.
- Переходная стратегия — guarded in-place apply с единым rsync contract, включая `--checksum`, `--delay-updates`, `--delete-delay`, без наследования owner/group от runner. Atomic Bitrix releases требуют отдельного ADR.

### 4. Verification, rollback и baseline

- После удаления SSH credentials отдельный непривилегированный job выполняет affected/full browser, health, SEO и resource/console smoke.
- Staging evidence обязательно; при отсутствии staging нужен отдельный bounded waiver пользователя с scope, residual risk, expiry/review trigger и next validation.
- Monitoring window и stop thresholds задаются до approval.
- `verified` разрешён только после совпадения post-deploy и candidate manifests, успешных smoke/monitoring и записи двух совпадающих BASE copies в независимых trust domains.
- При regression rollback восстанавливает изменённые и удаляет добавленные managed files, проверяет metadata, очищает cache и повторяет smoke. Автоматический rollback запрещён до отдельного решения и production-proven restore rehearsal.

## Stop conditions

Production mutation запрещена, если выполняется хотя бы одно условие:

- класс не доказан как `FILE_ONLY`;
- отсутствуют staging evidence или явно утверждённый bounded waiver;
- production secret доступен PR job либо privileged job исполняет candidate-controlled code;
- deploy-scope, controller или workflow change не прошёл обязательный review;
- host identity, SSH user, canonical paths или permissions не подтверждены;
- штатный local inventory выбирает personal/shared key, dedicated key не ограничен forced command либо denial checks для shell/SFTP/rsync/PTY/forwarding/write не пройдены;
- нет независимой BASE copy, manifests нестабильны или `BASE_UNKNOWN` трактуется как three-way baseline;
- есть secret/PII finding, special/unsafe path, unresolved drift/conflict или неутверждённое удаление;
- artifact, PROD hash, approval или plan ID не совпадают;
- не получен lock, не прошли disk/inode, backup или restore rehearsal;
- не прошли quality, pre-health, cache clear, post-deploy smoke или monitoring.

## Evidence и Definition of Done

Для production-релиза обязательны:

- release class и scope version;
- quality/staging evidence либо approved waiver;
- artifact digest, candidate/PROD/BASE manifest hashes и redacted drift decisions;
- exact dry-run, plan ID и approval record;
- lock, backup/restore rehearsal и apply status;
- post-apply manifest, smoke/monitoring и rollback outcome при необходимости;
- две совпадающие BASE copies и финальный audit record без secret/PII.

Issue/релиз нельзя закрыть только по факту merge или rsync.

## Incident / break-glass

До принятия отдельной policy break-glass deploy не автоматизируется. Incident требует ссылки на incident, snapshot до hotfix, минимального patch scope, явного approver, post-hotfix manifest/smoke и обязательной reconciliation/backport задачи с owner и сроком. Нехватка времени не разрешает отключать host verification, backup или audit trail.

## Текущее состояние внедрения

Операционный contract принят, но executable gates ещё не завершены. До закрытия задач `CI-REC-016`, `CI-REC-012`—`CI-REC-015` и отдельного production approval:

- draft PR `#45` не переводить в ready и не merge;
- текущий `deploy.yml` не считать безопасным для production mutation;
- разрешены только локальные/CI checks и явно согласованные read-only probes после настройки доступа.
