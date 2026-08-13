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
