# План восстановления

| Фаза | Задача | Результат | Приёмка |
|---|---|---|---|
| 1 | Собрать remote/local evidence | Карта отказов | Причины подтверждены логами и командами |
| 2 | Обновить CI dependencies | Без known critical audit findings | `npm ci`, `npm audit --audit-level=high`, CSS guard |
| 3 | Унифицировать quality gate | Одинаковые checks для PR и `main` | Workflow syntax + локальный эквивалент проходят |
| 4 | Harden deploy | Все проверки до rsync; fail-fast secrets/path | Негативные preflight fixtures и static review |
| 5 | Сделать smoke evidence переносимым | Current-run manifests как artifacts | Paths внутри workspace; artifact upload `always()` |
| 6 | Убрать stale sign-off blocker | Historical evidence не блокирует generic deploy | Self-test остаётся; historical draft не вызывается |
| 7 | Локальная/production валидация | Evidence по каждому доступному gate | Все non-mutating checks passed |
| 8 | Публикация GitHub fix | Draft PR и зелёный remote Quality Gate | PR #45, run `31690956455` |
| 9 | Зафиксировать и почелленджить E2E contract | `FILE_ONLY` boundary, trust model, deploy scope, `BASE/PROD/CANDIDATE`, stop conditions | Revised plan отражает challenge |
| 10a | Включить contract в операционную модель | Accepted ADR-013, workflow governance, agent instructions и gap/task traceability | Источники согласованы и не выдают documentation за executable enforcement |
| 10b | Реализовать contract/fixtures и local tooling | Machine-readable scope, release classifier, canonical manifest, `.env`/SSH preflight | Directory/root/generated/tombstone и negative security fixtures проходят |
| 11 | Dedicated-key two-way production bootstrap | Pinned host key, personal bootstrap only, dedicated forced-command local key, два стабильных manifests и redacted inventory | Shell/SFTP/rsync/PTY/forwarding/write denied; personal key не используется tooling; ни один production-файл не изменён; `origin/main` не назван BASE |
| 12 | Свести production drift | Принятые server changes перенесены через clean worktree/PR; решения сохранены | Повторный scan не содержит необъяснённого drift; durable decision records валидны |
| 13 | Trusted guarded apply | Immutable artifact, trusted controller, independent BASE, lock, backup/restore, exact apply, smoke/rollback | PR не получает secrets; lock/TOCTOU/restore fixtures проходят; stateful release блокируется |
| 14 | External unblock и controlled deploy | Staging или approved waiver, keys/environments/CODEOWNERS/ruleset, approval, deploy/monitoring | Approval связан с plan ID; первый `FILE_ONLY` deploy создаёт две BASE copies и evidence |

Rollback изменения governance выполняется только новым ADR/решением, потому что contract принят и действует как stop-policy. Rollback реализации: revert workflow/tooling commit без ослабления governance. Rollback production задаётся exact plan: восстановить весь managed-file backup, удалить новые managed files, проверить metadata, очистить cache, выполнить affected smoke и записать `rolled_back`; production не затрагивается до отдельного разрешения.

Статус 14.08.2026: фаза `10b` завершена локально и закреплена в PR Quality Gate. Реализованный classifier является path/scope gate с обязательным data-lifecycle review и не выдаёт разрешение на production mutation; фазы 11—14 не запускались.
