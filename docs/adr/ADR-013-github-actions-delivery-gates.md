# ADR-013: GitHub Actions delivery gates

Статус: принято
Дата: 2026-08-13

## Контекст

Production workflow запускался на каждый push в `main`, но до rsync выполнял только PHP lint. Полные repository/security checks существовали только в PR workflow, который не запускался в доступной истории. Все 79 deploy runs завершились ошибкой на пустом `SSH_PRIVATE_KEY`; следующие шаги фактически не были проверены. Дополнительно generic deploy зависел от historical release sign-off с абсолютными `/tmp` smoke paths, а exclude server-owned `tacticum_config.php` не соответствовал корню rsync source.

## Решение

1. `.github/workflows/pr-check.yml` становится reusable `Quality Gate` и вызывается как PR, так и перед deployment из `deploy.yml`.
2. Production mutation разрешена только после полного quality gate и fail-fast проверки обязательных deployment secrets.
3. Host authenticity задаётся secret `SSH_KNOWN_HOSTS`; runtime `ssh-keyscan` не используется.
4. Server-owned `local/php_interface/include/tacticum_config.php` защищён rsync exclude, относительным к source root `local/`.
   Behavioral self-test закрепляет этот контракт при `rsync --delete`.
5. Generic deploy не валидирует historical release-specific `/tmp` manifests. Каждый run создаёт собственные visual/browser/price manifests в workspace и публикует их как Actions artifact.
6. Pure `bitrix/**` и `docs/**` push не запускает production deployment. Изменения `bitrix/` по-прежнему блокируются quality gate, если commit одновременно затрагивает deployable scope.
7. Repo-owned root delivery включает menu files и `.htaccess`, необходимые для navigation и webmanifest MIME contracts; `.access.php` остаётся вне автоматического deploy scope.

## Последствия

- Требуются secrets `SSH_PRIVATE_KEY`, `SSH_KNOWN_HOSTS`, `SSH_HOST`, `SSH_USER`, `DEPLOY_PATH` в GitHub environment `production` или repository Actions secrets.
- Ошибка кода/config обнаруживается до rsync/cache clear.
- Browser smokes выполняются независимо после успешного deployment setup; общий assert завершает job ошибкой, если любой smoke failed.
- Evidence хранится 30 дней как `post-deploy-smoke-<run_id>-<attempt>`.

## Rollback

Откатить workflow/ADR commit. Если deployment уже произошёл и production regression подтверждён, использовать `docs/workflow/product-first-release-rollback-runbook.md`; после rollback deployment выполнить cache clear и affected smoke.
