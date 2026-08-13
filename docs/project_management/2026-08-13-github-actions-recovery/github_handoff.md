# GitHub handoff

Задача `CI-REC-008` требует владельца репозитория и отдельного разрешения на production deployment.

## 1. Production environment secrets

В `Settings → Environments → production → Environment secrets` подтвердить наличие пяти secrets:

- `SSH_PRIVATE_KEY` — приватный deploy key без passphrase, соответствующий public key на production;
- `SSH_KNOWN_HOSTS` — сверенная с администратором строка production host key;
- `SSH_HOST` — hostname или IPv4 без protocol/port;
- `SSH_USER` — ограниченный deploy user;
- `DEPLOY_PATH` — абсолютный корень сайта, не `/`.

Значения не копировать в issue, PR, workflow logs или этот пакет.

## 2. Ruleset для `main`

- Require a pull request before merging.
- Require status checks from `Quality Gate`: PHP syntax, static CSS build и security/convention checks.
- Block force pushes and branch deletion.
- Разрешить production environment deployment только доверенным веткам; при необходимости включить required reviewer.

## 3. Публикация и controlled run

1. Создать ветку от `8ef02a60a0641f2acda821cbcf82e98eb8f05c51` и перенести текущий working-tree diff.
2. Открыть PR и дождаться всех jobs `Quality Gate`.
3. После review merge в `main`; это запускает `🚀 Deploy to Production`.
4. В deploy log подтвердить последовательность: quality → config preflight → SSH → rsync → cache clear → health → four smoke checks → artifact upload → final assert.
5. Скачать `post-deploy-smoke-<run_id>-<attempt>` и проверить три manifest плюс screenshots.
6. Повторить `npm run release:public-precheck:prod` и affected smoke; сохранить ссылки на run/artifact в `evidence_index.csv`.

## Stop conditions

- Preflight сообщает missing/unsafe secret — deployment не продолжать, исправить environment secret.
- Host key mismatch — не обновлять secret автоматически; сверить fingerprint с администратором.
- Quality gate failed — production mutation не должна начаться.
- Любой post-deploy smoke failed — использовать Actions artifact для triage; при подтверждённой regression выполнить documented rollback.
