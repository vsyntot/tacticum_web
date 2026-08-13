# Чеклист исполнения

- [x] Прочитать project workflow, current state, gaps, CI roles и release runbooks.
- [x] Проверить dirty state/base commit и remote Actions history.
- [x] Воспроизвести PHP и repository quality checks.
- [x] Выполнить read-only production public precheck.
- [x] Обновить Node dependencies и пройти `npm audit --audit-level=high`.
- [x] Добавить общий quality gate для PR и `main`.
- [x] Добавить deployment config preflight без вывода secret values.
- [x] Поставить все blockers до rsync/cache mutation.
- [x] Генерировать и upload current-run smoke manifests.
- [x] Удалить generic dependency от historical `/tmp` evidence.
- [x] Проверить YAML, shell, npm scripts, PHP lint, dependency audit и production read-only smoke.
- [x] Получить разрешение на `.git`, push и draft PR; deployment approval остаётся отдельным gate.
- [x] Проверить новый GitHub PR Actions run.
- [ ] Настроить/подтвердить secrets и branch protection.
- [ ] Merge PR и проверить production deployment/post-deploy smoke artifact.
