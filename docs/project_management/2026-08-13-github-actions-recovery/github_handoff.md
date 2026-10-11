# GitHub handoff

Задача `CI-REC-009` требует владельца репозитория и отдельного разрешения на production deployment.

PR `#46` и CI-only PR `#47` смёрджены в `main` коммитами `9f4b7205e` и `2428bf812`. Quality Gate прошёл; исправленный sitemap workflow также прошёл run `38158939102` без apt package dependency.

Для `main` включены required PR, строгие PHP/CSS/Security checks, linear history, conversation resolution и запрет force-push/delete. Environment `production` ограничен protected branches и reviewer gate.

После merge legacy deploy runs `38158457245` и `38158939260` были отменены в состоянии environment wait/queue. SSH/rsync не запускались; production mutation после merge не выполнялась.

Trusted CI contour подготовлен в текущей ветке: `deploy.yml` больше не содержит legacy `rsync`, а использует immutable artifact → read-only PROD → BASE/PROD/CANDIDATE plan → protected production reviewer → root forced-command controller → smoke. На production установлены отдельные CI read-only/write ключи и pinned controller; первый запуск после merge должен оставаться controlled review.

Остаются gates: merge и controlled workflow run, CODEOWNERS/ruleset policy, observation window и promotion свежего post-manifest в durable BASE после успешного run.

## 1. Production environment secrets

В `Settings → Environments → production → Environment secrets` после реализации write gate подтвердить наличие пяти secrets:

- `SSH_PRIVATE_KEY` — приватный deploy key без passphrase, соответствующий public key на production;
- `SSH_KNOWN_HOSTS` — сверенная с администратором строка production host key;
- `SSH_HOST` — hostname или IPv4 без protocol/port;
- `SSH_USER` — ограниченный deploy user;
- `DEPLOY_PATH` — абсолютный корень сайта, не `/`.

Значения не копировать в issue, PR, workflow logs или этот пакет.

Для целевого `inspect-production` создан environment `production-readonly` с `SSH_READONLY_PRIVATE_KEY`, `SSH_KNOWN_HOSTS`, `SSH_HOST`, `SSH_USER`; server-side public key ограничен `restrict` + forced-command manifest wrapper. В `production` добавлены отдельные `SSH_WRITE_PRIVATE_KEY`, `SSH_KNOWN_HOSTS`, `SSH_HOST`, `SSH_USER`; ключ входит только в forced-command root wrapper. Личный `~/.ssh/id_ed25519` в CI не используется. Environment secrets не выдавать ни `pull_request`, ни `pull_request_target` workflows.

Fingerprints (public only): read-only `SHA256:dGLLM2EK4E4eemsZ7O1iEaFrsPXV+0ZUXTA4UCbVp2k`, write `SHA256:v+DsGRHx7Y1hnD//f2+sx/nyBPZKTYSAwmZjpgNga9I`. Private values не записываются в carrier.

ADR-013 и production governance приняты. До settings mutation должен быть реализован и проверен machine-readable deploy-scope. Privileged jobs используют immutable artifact и protected controller, не запускают `npm ci` или scripts кандидата. Третьи Actions в privileged jobs pin по commit SHA.

Проверка после merge: repository secrets по-прежнему пусты; environment `production` защищён reviewer gate, а `main` имеет required checks и запреты force-push/delete.

## 2. Ruleset для `main`

- Require a pull request before merging.
- Require status checks from `Quality Gate`: PHP syntax, static CSS build и security/convention checks.
- Block force pushes and branch deletion.
- Разрешить production environment deployment только доверенным веткам; при необходимости включить required reviewer.
- Добавить `CODEOWNERS`/required review для `.github/workflows/`, production tooling, deploy-scope, ADR-013 и server wrapper contract.
- Required reviewer подтверждает exact `plan_id`, artifact digest, PROD manifest hash и список deletions/tombstones; простой approve job без проверки plan summary недостаточен.

Проверка после merge показала: classic branch protection для `main` включена; отдельный repository ruleset и CODEOWNERS ещё не настроены.

## 3. Production reconciliation до публикации

1. Staging отсутствует; для exact plan оформлен owner-approved bounded waiver. Две BASE-копии сохранены локально и root-only на сервере; independent durable external store и GitHub release controls остаются открыты.
2. `CI-REC-016` выполнен локально; immutable artifact и bounded controller для exact plan также проверены evidence E-032. Это не закрывает trusted GitHub write path.
3. Host fingerprint закреплён; personal key использован только в bootstrap-сессии; dedicated `tacticum_prod_bitrix_ed25519` и forced-command manifest wrapper установлены и проверены.
4. Dedicated key проверен негативными shell/SFTP/PTY probes; forwarding и formal break-glass policy остаются отдельным security follow-up.
5. Два стабильных initial manifests построены; two-way drift разобран по четырём paths, `origin/main` BASE не считался.
6. Четыре принятых production-изменения перенесены через exact artifact; durable decisions и observed PROD hashes сохранены в E-032.
7. Quality Gate, final scan и post-deploy smoke повторены и прошли.
8. Immutable artifact, bounded controller, exclusive lock, approval binding, backup/restore rehearsal и dual BASE lifecycle выполнены для exact plan; trusted GitHub controller и rollback exercise остаются открыты.

## 4. Публикация и controlled run

1. PR `#46` от head `5b0aa0d1a` опубликован, не draft; все три checks зелёные.
2. Новый финальный head проверен run `38157300093`; Quality Gate зелёный.
3. До write approval проверить artifact `production-plan-*`: release class `FILE_ONLY`, `BASE/PROD/CANDIDATE`, artifact digest, additions/changes/deletions/tombstones/metadata, отсутствие conflicts, staging evidence/waiver и точный checksum dry-run.
4. Merge в `main` выполнен после включения базовой защиты. Legacy deploy runs отменены в reviewer wait; approval не выдавался, trusted controller и CI keys ещё не готовы.
5. Новый workflow `deploy.yml` подтверждает последовательность: quality → immutable artifact → read-only drift → BASE/PROD/CANDIDATE plan → approval binding → forced-command upload → exclusive lock → final manifest → disk/backup/restore rehearsal → exact apply → cache clear → post-apply manifest → smoke.
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

## Failure ledger — sitemap run 38158457159

- Operation/signal: `Validate XML sitemaps` failed with exit `100` while installing `libxml2-utils`; the GitHub Ubuntu mirror returned `404` for the package.
- Reproduction: failed on the runner at the package-install boundary; local `sitemap.xml` parses successfully.
- Localization: runner package mirror/tooling dependency; falsifier is a clean run that uses only preinstalled Python stdlib XML parsing.
- Reduction/root cause: replaced the apt dependency with `tools/xml-well-formed-check.py` using `xml.etree.ElementTree`; root cause is `known` for the workflow dependency, not sitemap content.
- Retry guard: run the sitemap workflow with no apt install, then require XML parsing, static sitemap generation/check and `npm run seo:check` to pass.
- Owner/next step: repository CI; push the bounded fix and accept only a green sitemap run before considering the main release fully green.

## Failure ledger — production-readonly environment bootstrap

- Operation/signal: initial GitHub API environment update returned `422`; `gh -f` serialized integer/boolean/object fields as strings and the environment public-key endpoint returned `404`.
- Reproduction: `gh api --method PUT ... -f wait_timer=0 -f prevent_self_review=false -f deployment_branch_policy='...'`.
- Localization: GitHub API request encoding, not SSH or key material. The production environment remained unchanged.
- Reduction/root cause: switched to a JSON `--input` payload; environment was created with protected-branch policy, then secrets were set successfully.
- Retry guard: require `gh api .../environments/production-readonly` to show the environment and `gh secret list --env production-readonly` to show only the four expected names.
- Owner/next step: repository owner; merge and execute one controlled review run before any application mutation.
