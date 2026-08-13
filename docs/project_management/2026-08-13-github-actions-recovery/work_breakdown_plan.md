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
| 8 | External unblock | Secrets/settings настроены, новый run зелёный | Требует user-approved GitHub mutation/deploy |

Rollback: откатить изменения workflows/dependencies одним revert-коммитом; production не затрагивается до отдельного запуска deployment.
