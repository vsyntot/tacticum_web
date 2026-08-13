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
- [ ] Получить разрешение на push/PR и deployment.
- [ ] Настроить/подтвердить secrets и branch protection.
- [ ] Проверить новый GitHub Actions run и post-deploy smoke.
