# GitHub handoff

Задача `CI-REC-009` требует владельца репозитория и отдельного разрешения на production deployment.

Draft PR `#45` (`agent/recover-github-actions` → `main`) создан. Последний подтверждённый remote baseline до contract-tooling slice — GitHub Actions run `31690956455` для head `01eb8995`, status success, merge state `CLEAN`; новый head обязан повторно пройти Quality Gate. `CI-REC-011` и локальный `CI-REC-016` завершены. Финальный production gate `CI-REC-009` остаётся зависим от `CI-REC-012`—`CI-REC-015`.

До реализации минимального `FILE_ONLY` slice из `production-drift-reconciliation-plan.md` PR не переводить из draft и не merge: текущий workflow способен выполнить `rsync --delete`, не показав предварительно ручные production-изменения, и сохраняет SSH agent до последующего `npm ci`.

## 1. Production environment secrets

В `Settings → Environments → production → Environment secrets` после реализации write gate подтвердить наличие пяти secrets:

- `SSH_PRIVATE_KEY` — приватный deploy key без passphrase, соответствующий public key на production;
- `SSH_KNOWN_HOSTS` — сверенная с администратором строка production host key;
- `SSH_HOST` — hostname или IPv4 без protocol/port;
- `SSH_USER` — ограниченный deploy user;
- `DEPLOY_PATH` — абсолютный корень сайта, не `/`.

Значения не копировать в issue, PR, workflow logs или этот пакет.

Для целевого `inspect-production` создать отдельный environment `production-readonly` и secret `SSH_READONLY_PRIVATE_KEY`; server-side public key должен быть ограничен `restrict` + forced-command manifest wrapper. Не использовать для CI личный `~/.ssh/id_ed25519`. Environment secrets не выдавать ни `pull_request`, ни `pull_request_target` workflows.

ADR-013 и production governance приняты. До settings mutation должен быть реализован и проверен machine-readable deploy-scope. Privileged jobs используют immutable artifact и protected controller, не запускают `npm ci` или scripts кандидата. Третьи Actions в privileged jobs pin по commit SHA.

Проверка 13.08.2026 показала пустые списки repository secrets и `production` environment secrets.

## 2. Ruleset для `main`

- Require a pull request before merging.
- Require status checks from `Quality Gate`: PHP syntax, static CSS build и security/convention checks.
- Block force pushes and branch deletion.
- Разрешить production environment deployment только доверенным веткам; при необходимости включить required reviewer.
- Добавить `CODEOWNERS`/required review для `.github/workflows/`, production tooling, deploy-scope, ADR-013 и server wrapper contract.
- Required reviewer подтверждает exact `plan_id`, artifact digest, PROD manifest hash и список deletions/tombstones; простой approve job без проверки plan summary недостаточен.

Проверка 13.08.2026 показала: branch protection для `main` отсутствует, repository rulesets отсутствуют.

## 3. Production reconciliation до публикации

1. Выбрать independent durable BASE store и staging path либо оформить отдельный user-approved bounded waiver в рамках принятого ADR-013.
2. `CI-REC-016` выполнен локально: deploy-scope/classifier/canonical manifest/plan/preflight fixtures проходят. Не трактовать этот path-only gate как immutable artifact или production apply readiness.
3. Закрепить host fingerprint из независимого канала. Существующий personal key использовать только в отдельно подтверждённой bootstrap-сессии для установки dedicated `tacticum_prod_bitrix_ed25519.pub` с forced-command manifest wrapper.
4. Проверить dedicated key негативными shell/SFTP/rsync/PTY/forwarding/write cases; исправить `.env` на dedicated paths/mode `0600`. Отдельно решить revoke personal authorization либо formal break-glass policy.
5. Построить dedicated key два совпадающих initial manifests и показать two-way redacted drift относительно candidate; не называть `origin/main` BASE.
6. Перенести принятые production-изменения через clean worktree/PR; durable decisions связать с observed PROD hashes.
7. Повторить Quality Gate и scan до отсутствия необъяснённого drift.
8. Добавить immutable artifact, trusted controller, exclusive lock, approval binding, backup/restore rehearsal и dual BASE lifecycle.

## 4. Публикация и controlled run

1. Ветка от `8ef02a60a0641f2acda821cbcf82e98eb8f05c51` и draft PR `#45` уже опубликованы.
2. PR Quality Gate уже прошёл; после reconciliation дождаться зелёного run для нового финального head.
3. До write approval проверить artifact `production-plan-*`: release class `FILE_ONLY`, `BASE/PROD/CANDIDATE`, artifact digest, additions/changes/deletions/tombstones/metadata, отсутствие conflicts, staging evidence/waiver и точный checksum dry-run.
4. После review и отдельного merge/deploy решения перевести PR из draft и merge в `main`; это запускает gated `🚀 Deploy to Production`.
5. В deploy log подтвердить последовательность: quality/classification → immutable artifact → read-only drift → approval binding → exclusive lock → final manifest → disk/backup/restore rehearsal → exact apply → cache clear → post-apply manifest → credential teardown → health/smoke/monitoring → dual BASE finalize.
6. Скачать `post-deploy-smoke-<run_id>-<attempt>` и проверить manifests plus screenshots; raw production snapshot в artifact отсутствует.
7. Повторить `npm run release:public-precheck:prod` и affected smoke; сохранить ссылки на plan/run/artifact в `evidence_index.csv`.

## Stop conditions

- Preflight сообщает missing/unsafe secret — deployment не продолжать, исправить environment secret.
- Host key mismatch — не обновлять secret автоматически; сверить fingerprint с администратором.
- Любой `PROD_CHANGE`, `PROD_ONLY`, `PROD_MISSING`, `CONFLICT`, secret finding или неожиданное удаление — не разрешать write job; сначала reconciliation.
- `STATEFUL` classification, отсутствие staging evidence/approved waiver, independent BASE store или exclusive lock — apply запрещён.
- Production secret доступен PR event либо privileged job запускает `npm ci`/candidate script — workflow считается небезопасным и блокируется.
- Повторный production manifest отличается от утверждённого `plan_id` — план устарел, начать inspection заново.
- Backup или restore verification failed — rsync не запускать.
- Quality gate failed — production mutation не должна начаться.
- Любой post-deploy smoke failed — использовать Actions artifact для triage; при подтверждённой regression выполнить documented rollback.
