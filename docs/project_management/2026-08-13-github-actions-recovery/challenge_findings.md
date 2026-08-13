# Результаты challenge

## Блокирующие дефекты

1. `DEPLOY-001`: отсутствует `SSH_PRIVATE_KEY`; production job падает до соединения с сервером.
2. `DEPLOY-002`: проверки качества, кроме PHP lint, стоят после rsync/cache clear. Ошибка quality gate обнаруживается после mutation.
3. `DEPLOY-003`: generic deploy валидирует historical sign-off за июнь 2026 с локальными `/tmp` manifests; clean runner не может их прочитать.
4. `CI-001`: полноценный PR quality workflow не защищает direct push в `main`.
5. `CI-002`: deploy запускается даже для commit, который меняет только запрещённый для custom development каталог `bitrix/`, хотя workflow его не синхронизирует.

## Существенные риски

6. `SEC-001`: dependency tree содержит critical advisory для `tar@7.5.15`; CI не запускает `npm audit`.
7. `DEPLOY-004`: SSH host/path используются без явной проверки пустых/опасных значений; ошибка показывается поздно и неоперационно.
8. `DEPLOY-005`: smoke manifests остаются во временном каталоге runner и не публикуются как Actions artifacts.
9. `OPS-001`: actions на Node 20 compatibility path дают deprecation warning на runner 2026.

## Выбор направления

Оставить существующий auto-deploy при push в `main`, но поставить полный reusable quality job до deployment, добавить fail-fast configuration preflight, генерировать current-run manifests в стабильных каталогах и публиковать их. Historical release sign-off не использовать как generic deploy gate.

## Challenge E2E production reconciliation plan — 2026-08-13

Статус findings: `adopted_in_operational_model`, но не `resolved`; ADR и governance приняты, закрытие требует tooling, fixtures и production evidence.

| ID | Severity | Finding | Ответ в пересмотренном плане |
|---|---|---|---|
| E2E-CH-001 | critical | Production secrets могут оказаться рядом с candidate/PR code и package lifecycle scripts | PR events не получают secrets; immutable artifact собирается непривилегированно; privileged controller не выполняет `npm ci`/candidate scripts |
| E2E-CH-002 | critical | File backup не откатывает config/DB/iblock migrations | Первый контур только `FILE_ONLY`; `STATEFUL` блокируется и требует отдельного migration lane |
| E2E-CH-003 | high | Ownership plan не совпадает с directory/root/tombstone semantics текущего rsync | Введён единый machine-readable deploy-scope: authoritative dirs, exact files, exclusions, generated files и tombstones |
| E2E-CH-004 | high | BASE на сервере может быть изменён тем же write user/key | Требуются server operational copy и independent durable baseline copy с integrity binding |
| E2E-CH-005 | high | TOCTOU scan не защищает от изменения во время apply | Server-side exclusive lock охватывает final scan, backup, apply и post-apply manifest |
| E2E-CH-006 | high | При `BASE_UNKNOWN` невозможно доказанное three-way merge | Bootstrap переведён в two-way inventory; BASE создаётся только после verified deploy |
| E2E-CH-007 | high | Rollback не определяет точное восстановление всего file scope | Добавлены disk/inode checks, full scope backup, restore rehearsal, metadata/new-file handling и rollback policy gate |
| E2E-CH-008 | high | `.env` получает password/path authority | `.env` ограничен local connection metadata; password tooling запрещён; state/backup paths фиксирует server wrapper |
| E2E-CH-009 | high | Environment approval не привязан доказуемо к plan | Approval связывает plan ID, artifact digest, PROD hash, approver и approved deletions/expiry |
| E2E-CH-010 | medium | Manifest не фиксирует file type/mode/symlink semantics | Определена canonical NUL-safe `lstat` schema; special files запрещены |
| E2E-CH-011 | medium | Нет обязательного staging/pre-health/monitoring contract | Добавлены staging-or-waiver gate, pre-deploy baseline, file-to-smoke map и release-specific monitoring |
| E2E-CH-012 | medium | Target design слишком велик для первого validated slice | План разделён на contract, local bootstrap, guarded apply и GitHub rollout slices |
