# E2E-план сверки production и безопасного деплоя

Статус: `accepted_operational_contract`; локальный contract/fixtures для scope, classification, canonical manifest/plan и SSH preflight реализованы и включены в PR Quality Gate. Production inspection/apply enforcement остаётся pending до dedicated key/wrapper, trusted controller и внешних gates.

Workflow lane: `Security / Integration` с обязательными `security/access`, `privacy/data`, `compatibility/data-lifecycle`, `QA/release` и `external mutation` gates.

## Место в операционной модели

- Канонический ежедневный process: `docs/workflow/production-deployment-governance.md`.
- Архитектурное решение: `docs/adr/ADR-013-github-actions-delivery-gates.md`.
- Этот документ: детальный E2E design, threat model, acceptance criteria и implementation roadmap.
- Исполнительные инструкции: `AGENTS.md`, `.github/copilot-instructions.md`, `.github/agents/devops.md`.
- Статус и зависимости внедрения: `task_register.csv`, `progress.md`, `docs/workflow/gap-analysis.md`.

Принятие contract и прохождение локальных fixtures не доказывают production wrapper/apply enforcement и не разрешают production mutation. До закрытия остальных implementation tasks действуют stop conditions ниже.

## Цель

Перед каждой записью в production:

1. классифицировать релиз и доказать, что первый контур поддерживает только `FILE_ONLY` изменения;
2. собрать неизменяемый deploy artifact и manifest без production secrets;
3. получить read-only состояние управляемых файлов на сервере доверенным controller;
4. показать отличия production от последнего подтверждённого деплоя и кандидата;
5. перенести нужные production-правки в Git или явно отклонить их с durable decision record;
6. свести конфликты и повторно пройти проверки;
7. показать пользователю точный dry-run, связанный с artifact и production manifest;
8. под server-side lock повторно проверить production, backup и rollback;
9. применить ровно утверждённый artifact и пройти post-deploy smoke/monitoring;
10. записать новый baseline в двух независимых trust domains.

Git остаётся источником истины для кода. Ручное изменение production не становится правильным автоматически, но не может быть молча перезаписано.

## Принятые границы первого контура

### Поддерживается: `FILE_ONLY`

Первый контур разрешает только изменения файлов из deploy scope, которые:

- не требуют изменения `tacticum_config.php`, `.settings.php`, БД, инфоблоков или runtime data;
- не запускают migration/apply/finalize/seed команды;
- обратно совместимы с текущей safe summary production config/schema;
- имеют файловый backup и восстановимый предыдущий manifest;
- прошли staging либо отдельный user-approved waiver с ограниченным scope, residual risk, сроком пересмотра и next validation.

### Не поддерживается: `STATEFUL`

Релиз автоматически классифицируется как `STATEFUL` и блокируется этим контуром, если меняет или требует:

- production config, schema/IDs/источники данных;
- БД или инфоблоки, migration, seed, backfill, finalize или data cleanup;
- необратимое cache/data преобразование;
- новый внешний integration contract, который нельзя подтвердить безопасным read-only preflight.

Для `STATEFUL` нужен отдельный migration plan: совместимость вперёд/назад, безопасный aggregate preflight без raw data/PII, backup данных/config, порядок apply, verification и rollback. Файловый backup не считается rollback stateful-релиза.

### Не входит в первый контур

- автоматический stateful deploy;
- автоматическое принятие production-кода в Git;
- чтение `bitrix/**`, uploads, logs, БД, config или PII;
- выдача production secrets PR-job;
- атомарный release-directory/symlink rollout: это целевое улучшение после отдельного Bitrix ADR.

## Единый deploy-scope contract

Единый machine-readable source реализован в `tools/deploy-scope.json`. Локальные manifest/classification/rsync-contract fixtures уже порождаются только из него; будущие artifact, dry-run, apply, backup и rollback обязаны потреблять тот же contract без повторения фильтров.

Контракт различает четыре семантики:

### 1. Authoritative directories

Внутри них неизвестный production-файл считается `PROD_ONLY`, а отсутствующий кандидатский путь — плановым удалением только после approval:

- `local/**` с server-owned exclusions;
- `about/**`, `services/**`, `contacts/**`, `calculator/**`, `price/**`, `offer/**`, `aiagents/**`, `policies/**`;
- `platform/**`, `agents/**`, `dev/**`, `forum/**`.

### 2. Exact root files

Управляются только перечисленные пути; другие корневые файлы production не сканируются и не удаляются:

- `.bottom.menu.php`, `.htaccess`, `.section.php`, `.top.menu.php`;
- `index.php`, `404.php`, `favicon.ico`, `robots.txt`, `sitemap.xml`, `urlrewrite.php`.

### 3. Generated files

- `sitemap-basic-files.xml` генерируется один раз при сборке immutable artifact;
- дата/параметры генерации и hash входят в manifest;
- dry-run/apply не регенерируют файл.

### 4. Explicit tombstones

- legacy `sitemap-files.xml` — явная операция удаления;
- будущие удаления вне authoritative directories добавляются только как reviewed tombstone, а не произвольная shell-команда.

### Server-owned exclusions

- `local/php_interface/include/tacticum_config.php`;
- `*.log` внутри `local/`, пока фактический rsync сохраняет этот exclude;
- `bitrix/**`, `upload/**`, runtime cache, sessions, logs и backups;
- `.settings.php`, `.settings_extra.php`, `.access.php`, лицензии и ключи;
- БД, содержимое инфоблоков и любые пользовательские/PII-данные;
- всё вне contract.

Если появляется новый server-owned путь внутри authoritative directory, сначала меняются ADR, deploy-scope и единые behavioral fixtures. Исключение нельзя добавлять во время deploy.

Контракт включает полный набор rsync options. Для переходного guarded in-place режима обязательны `--archive`, `--no-owner`, `--no-group`, `--checksum`, `--delay-updates`, `--delete-delay`, одинаковые filters/source/destination и безопасная обработка partial files. Если server ownership должен меняться, это задаётся отдельным server-side правилом и проверяется manifest, а не наследуется от runner. Scan/dry-run/apply не собирают команды независимо.

## Artifact и manifest contract

### `CANDIDATE`

`CANDIDATE` — не live checkout runner, а immutable artifact, собранный job без production secrets. Он содержит:

- только deploy-scope files;
- один раз созданные generated files;
- canonical candidate manifest;
- `candidate_tree_sha`, commit SHA и artifact digest;
- `scope_version` и версию trusted controller contract;
- declared release class `FILE_ONLY`.

После сборки artifact не изменяется. Privileged jobs только скачивают его и проверяют digest; они не выполняют `npm ci`, package lifecycle scripts или произвольные scripts из кандидата.

### `PROD` и `BASE`

- `PROD` строится read-only server-side helper по фиксированному deploy-scope;
- `BASE` — manifest последнего `verified` deploy;
- server-side operational copy хранится вне webroot в пути, жёстко заданном trusted wrapper;
- вторая durable copy хранится вне production server и связывается с Git commit, artifact digest, run ID и approval record;
- конкретный внешний store и механизм integrity/подписи выбираются в ADR до реализации tooling;
- server copy сама по себе не является доверенным BASE, потому что доступна write deploy user.

### Canonical manifest

Для каждого управляемого пути manifest содержит:

- нормализованный относительный путь с NUL-safe обработкой;
- `lstat` type; symlink никогда не разыменовывается;
- SHA-256 содержимого regular file либо literal symlink target;
- размер и mode;
- для directory — ожидаемый type/mode, если contract им управляет;
- special files запрещены;
- uid/gid фиксируются как operational observation, а ожидаемый owner/group задаётся server-side contract, а не локальным Git checkout;
- mtime не участвует в drift decision.

Canonical serialization, locale и field order фиксируются в schema/self-tests. `plan_id` — hash canonical plan payload, а не конкатенация строк.

## Модель состояний

После появления достоверного `BASE` используется three-way сравнение:

| Состояние | Условие | Действие |
|---|---|---|
| `UNCHANGED` | `BASE = PROD = CANDIDATE` | Ничего не делать |
| `LOCAL_CHANGE` | `BASE = PROD`, кандидат изменён | Включить в deploy plan |
| `PROD_CHANGE` | `BASE != PROD`, кандидат равен `BASE` | Стоп; показать и принять решение |
| `CONVERGED` | `PROD = CANDIDATE`, оба отличаются от `BASE` | Проверить metadata и записать совпадение |
| `CONFLICT` | PROD и кандидат изменились относительно `BASE` и не равны | Стоп; three-way merge |
| `PROD_ONLY` | Лишний путь внутри authoritative directory | Стоп; adopt, approved deletion или reclassification |
| `PROD_MISSING` | На production отсутствует ожидаемый управляемый путь | Стоп; approved restore/deletion decision |
| `BINARY_CONFLICT` | Конфликт нельзя безопасно слить построчно | Только явный выбор версии и ручная проверка |

Решения `keep_candidate`, `delete_from_prod` и `reclassify_server_owned` сохраняются как durable record: path, observed PROD hash, action, approver, plan ID, scope version и срок действия. Они не должны бесконечно повторяться как неразрешённый drift.

## Первый запуск: `BASE_UNKNOWN`

Без доказанного baseline three-way выводы запрещены. `origin/main` и PR artifact считаются только кандидатами для two-way сравнения.

1. Из независимого административного канала получить и вручную сверить production host fingerprint. До этого не выполнять ни bootstrap login, ни `ssh-keyscan` с последующим автоматическим trust.
2. Существующий личный `~/.ssh/id_ed25519` использовать только как явный bootstrap/manual key: не передавать в GitHub Actions, не выбирать через неявный agent fallback и не использовать штатным `prod:*` tooling.
3. Bootstrap-session выполнять с explicit `IdentityFile`, `IdentitiesOnly=yes`, pinned `UserKnownHostsFile`, `StrictHostKeyChecking=yes`, отключёнными password/keyboard-interactive authentication и forwarding. Проверить `id`, shell, home, canonical `PROD_WEB_ROOT` и возможность установить административно согласованный wrapper; при mismatch остановиться.
4. Создать отдельную passphrase-protected пару `~/.ssh/tacticum_prod_bitrix_ed25519[.pub]`; private key остаётся mode `0600`, public key можно установить только для `bitrix` после fingerprint review.
5. Установить dedicated public key отдельной строкой `authorized_keys` с `restrict,command="<ADMIN_MANAGED_READONLY_MANIFEST_WRAPPER>"`. Wrapper использует фиксированные server-side scope/path, отвергает неизвестный `SSH_ORIGINAL_COMMAND`, не выдаёт shell/SFTP/rsync и возвращает только canonical redacted manifest.
6. В новой сессии проверить dedicated key: фактическая команда всегда заменяется wrapper, PTY/SFTP/forwarding/write-команды отвергаются, а manifest read проходит. Не считать ключ read-only только по имени файла или клиентским options.
7. После успешной проверки dedicated key прекратить штатное использование личного ключа. Его server authorization отозвать; сохранить можно только по отдельно принятой break-glass policy с owner, expiry/review и audit. Отзыв/сохранение — отдельное production access decision.
8. Исправить `.env` на dedicated key paths и pinned known-hosts path; проверить mode `0600`. Все последующие local inventory выполняются только dedicated forced-command key.
9. Построить два одинаковых подряд read-only manifests. Если hashes различаются, inventory нестабилен и останавливается.
10. Выполнить two-way `PROD ↔ CANDIDATE` inventory. Нельзя называть сторону более новой или выполнять автоматический merge.
11. Скачать только явно выбранные text-файлы через отдельный reviewed read-only wrapper capability в `mktemp -d` mode `0700`, вне worktree. Если wrapper поддерживает только manifest, content download остаётся blocked до отдельного capability review. Ограничить size/type; symlinks/binary/special files не скачивать автоматически.
12. Выполнить fail-closed secret/PII scan. Raw snapshot не коммитить, не логировать и не загружать в GitHub; при finding показывать только путь/класс проблемы.
13. Принятые production-изменения переносить через clean reconciliation worktree/branch и обычный review. Текущий worktree целиком production snapshot не заменяет.
14. После reconciliation повторить two-way scan и quality gate.
15. Только после первого controlled deploy и совпадения post-deploy manifest с artifact создать первый `BASE` в server-side и независимом внешнем store.

## Локальный доступ через `.env`

`.env` — только локальный operator input, не runtime-конфиг приложения и не замена `tacticum_config.php` из ADR-002. Parser не выполняет `source`/`eval`, читает отдельные allowlisted имена и не печатает значения.

Допустимые параметры:

```dotenv
PROD_SSH_USER=bitrix
PROD_SSH_PORT=22
PROD_SSH_KEY_PATH=/Users/ivanmonakhov/.ssh/tacticum_prod_bitrix_ed25519
PROD_SSH_PUBLIC_KEY_PATH=/Users/ivanmonakhov/.ssh/tacticum_prod_bitrix_ed25519.pub
PROD_SSH_KNOWN_HOSTS_PATH=/Users/ivanmonakhov/.ssh/tacticum_prod_known_hosts
```

`PROD_HOST` и `PROD_WEB_ROOT` допустимы только для local bootstrap и проходят строгую validation/canonicalization. Целевой server wrapper игнорирует caller-controlled state/backup/deploy paths и использует административно заданные значения вне webroot.

`PROD_BASIC_USER` относится к HTTP Basic Auth и не подменяет `PROD_SSH_USER`. `PROD_ROOT_PASSWORD`, MySQL и другие password values не используются tooling; их целевое место — Keychain/secret manager, а не workspace `.env`. До первого доступа `.env` обязан иметь mode `0600`. Parser требует абсолютные существующие paths, сверяет private/public fingerprints и запрещает personal/shared key basename для штатного tooling.

Dedicated local key защищается passphrase и загружается пользователем в локальный `ssh-agent`/Keychain; tooling не хранит passphrase и работает fail-closed, если ключ недоступен. Каждый вызов строится из фиксированного argv без shell interpolation и задаёт `BatchMode=yes`, `StrictHostKeyChecking=yes`, `UserKnownHostsFile=<dedicated path>`, `PreferredAuthentications=publickey`, `PasswordAuthentication=no`, `KbdInteractiveAuthentication=no`, `IdentitiesOnly=yes`, `IdentityFile=<dedicated key>`, `ForwardAgent=no`, `ClearAllForwardings=yes`, `PermitLocalCommand=no`, `RequestTTY=no`. User SSH config не может ослабить эти options.

Личный `id_ed25519` не является read-only credential: client-side flags и prefix без forced command не лишают пользователя `bitrix` права записи. Он допускается только в явно подтверждённой bootstrap/manual процедуре и не попадает в `.env` штатного tooling. Dedicated local, CI read-only и CI write keys — четыре разные роли вместе с personal bootstrap key; private material между ролями не переиспользуется.

## E2E-поток `FILE_ONLY` релиза

### 1. Quality, classification и build без production secrets

- PR/main quality gate проходит до privileged jobs;
- release classifier доказывает `FILE_ONLY` либо останавливает pipeline;
- staging evidence существует либо зарегистрирован явный user-approved waiver;
- immutable deploy artifact и candidate manifest собираются один раз;
- secret scan artifact/repository проходит до передачи artifact дальше;
- workflow/deploy tooling changes требуют отдельного review и не могут самоустановить ослабленный controller без ADR/CODEOWNERS gate.

### 2. Pre-deploy baseline

- сохранить public health/config safe summary и affected smoke до mutation;
- определить affected URLs/actions через явную file-to-smoke map; неизвестное влияние расширяет smoke до полного публичного набора;
- существующий production failure не смешивается с регрессией кандидата и сам может стать stop condition.

### 3. Trusted read-only inspection

- production secrets не выдаются `pull_request` или `pull_request_target` job;
- локальная pre-merge проверка выполняется dedicated local forced-command read-only key; personal key разрешён только для bootstrap/break-glass по отдельной policy;
- CI inspection запускается только доверенным workflow/controller после merge либо manual dispatch на защищённой ref;
- read-only CI key ограничен `restrict,command="..."` wrapper, который выдаёт только canonical manifest фиксированного scope;
- job не checkout-ит и не исполняет PR code после получения ключа;
- два последовательных PROD manifests должны совпасть.

### 4. Drift reconciliation

- сравнить `BASE ↔ PROD ↔ CANDIDATE`, а при bootstrap только `PROD ↔ CANDIDATE`;
- сформировать redacted summary additions/changes/deletions/conflicts/binary/metadata;
- raw content не является Actions artifact;
- каждый drift item получает durable decision;
- `adopt_to_git` проходит через clean worktree/branch, tests и PR;
- `reclassify_server_owned` требует ADR/deploy-scope/filter update;
- после изменения кандидата artifact, manifests и plan создаются заново.

### 5. Immutable deploy plan и approval

Canonical plan payload содержит:

- candidate tree/commit и artifact digest;
- hashes `BASE` и `PROD` manifests;
- scope/controller/schema versions;
- exact source/destination и rsync options;
- additions/changes/deletions/tombstones/metadata changes;
- generated file hashes;
- backup/restore recipe и smoke matrix;
- staging evidence либо approved waiver;
- plan expiry policy и `plan_id`.

Пользователь видит plan summary и exact dry-run до write approval. Approval record связывает approver, `plan_id`, artifact digest, PROD hash и разрешённые deletions. Истёкший или не совпавший approval недействителен.

### 6. Exclusive lock, final check и backup

- write wrapper берёт server-side exclusive deploy lock;
- lock удерживается от финального manifest до post-apply manifest; GitHub `concurrency` остаётся только дополнительной защитой;
- final PROD hash обязан совпасть с approved plan;
- canonical deploy root/state/backup paths проверяются server-side: absolute, outside webroot where required, no symlink escape;
- проверяются свободное место и inodes;
- backup охватывает весь изменяемый file scope и tombstone targets;
- restore rehearsal выполняется в безопасный временный каталог и должен воспроизвести pre-deploy manifest;
- retention, access mode и cleanup policy задаются ADR/runbook до apply.

### 7. Guarded in-place apply

- применить только immutable artifact по exact plan;
- использовать единый rsync contract с `--checksum --delay-updates --delete-delay`;
- записать `deployed_pending_smoke` только после совпадения post-apply manifest;
- обязательный cache clear не скрывает ошибки через unconditional `|| true`;
- privileged job не выполняет `npm ci`; SSH agent/key уничтожается до browser tooling;
- write CI key по возможности ограничен forced-command deploy wrapper, а не произвольным shell.

Guarded in-place режим принят как transitional strategy, а не как эквивалент атомарного deploy. Его использование всё равно запрещено до lock/backup/restore fixtures и exact-plan approval. Atomic release directories/symlink требуют отдельного Bitrix feasibility/ADR решения.

### 8. Verify, monitoring и rollback

- после удаления SSH credentials отдельный unprivileged job выполняет health, affected/full browser actions, `/price/`, SEO и resource/console checks;
- controlled lead success-flow выполняется только по существующему no-PII marker/owner gate и не включается молча;
- monitoring window, источники 5xx/error evidence и stop thresholds задаются release plan до approval;
- post-deploy manifest и smoke evidence связываются с plan ID/artifact digest;
- для первого controlled deploy rollback остаётся manual-confirmed; автоматический rollback можно включить только после успешного restore rehearsal и отдельного решения;
- при rollback восстанавливаются старые и удаляются новые managed files, проверяются metadata, очищается cache, повторяется smoke и записывается `rolled_back`.

### 9. Baseline commit

Статус `verified` разрешён только когда:

- post-deploy manifest равен candidate manifest с учётом server metadata contract;
- smoke и monitoring gates прошли;
- server-side state и независимая durable baseline copy записаны и совпадают;
- audit record содержит actor/approver, run URL/ID, plan ID, artifact digest, timestamps, backup ID и final status без secret/PII.

## Целевая схема GitHub Actions

1. `quality-and-build`: без production environment/secrets; классификация `FILE_ONLY`, quality, secret scan, immutable artifact.
2. `inspect-production`: только после merge/manual protected ref; environment `production-readonly`, restricted key, trusted controller, manifest-only output.
3. `plan`: без write key; проверяет BASE/PROD/CANDIDATE, dry-run output и формирует canonical plan/artifact digest.
4. `deploy-production`: environment `production` с required reviewer; проверяет approval binding, берёт lock, final scan, backup, apply и post-apply manifest.
5. `smoke`: новый runner/job без SSH credentials; скачивает только safe evidence и выполняет public checks.
6. `finalize-or-rollback`: меняет final state только по проверенному smoke/monitoring result и принятой rollback policy.

Дополнительные controls:

- запрет production secrets во всех PR events;
- запрет `pull_request_target` checkout/execute candidate;
- `CODEOWNERS` и required review для workflows, `tools/prod-*`, deploy scope, ADR и server wrapper contract;
- сторонние Actions в privileged jobs pin по immutable commit SHA;
- read-only/write CI keys различаются между собой и с личным ключом;
- raw snapshots/diffs не попадают в GitHub artifacts/logs;
- независимый durable baseline store выбирается до реализации CI apply.

## Минимальный CLI-контракт

Первый implementation slice не должен начинаться с восьми частично дублирующих команд:

```text
npm run prod:scope:check
npm run prod:classify -- <changed-path>...
npm run prod:preflight
npm run prod:manifest -- --root=<tree> --output=<new-file.json>
npm run prod:drift:plan -- --base=<optional.json> --prod=<json> --candidate=<json>
npm run prod:deploy:apply -- --plan=<plan_id>
npm run prod:deploy:verify -- --plan=<plan_id>
npm run prod:rollback -- --plan=<plan_id>
```

- `scope:check`, `classify`, `manifest`, `preflight` и offline `drift:plan` реализованы локально и покрыты `prod:contract:self-test`;
- `preflight` — local env/access checks без production connection или mutation;
- `drift:plan` — offline compare ранее полученных canonical manifests и safe plan; production manifest retrieval и checksum dry-run появятся только после trusted wrapper;
- `apply`/`rollback` — только после отдельного production approval;
- реализации используют один deploy-scope library/contract, а не повторяют фильтры.

`FILE_ONLY` classifier в этом slice является path/scope gate. Он блокирует известные config/migration/seed/finalize paths, но не доказывает семантическое отсутствие DB/iblock/config impact. Поэтому результат содержит `assurance=PATH_SCOPE_ONLY_REQUIRES_DATA_LIFECYCLE_REVIEW` и `productionMutationAllowed=false`; ручной `compatibility/data-lifecycle` review остаётся обязательным до artifact promotion и apply.

## Incident / break-glass

До принятия отдельной policy break-glass deploy не автоматизируется. Incident Lane требует:

- incident reference и явного пользователя/approver;
- snapshot до hotfix;
- минимального patch scope;
- запрета raw secret/PII evidence;
- обязательного backport/reconciliation task с owner и контролируемым сроком, который должен быть определён policy;
- повторного manifest/smoke после hotfix.

Отсутствие времени на обычный процесс не разрешает отключать host verification, backup или audit trail.

## Stop conditions

- release class не доказан как `FILE_ONLY`;
- staging evidence отсутствует и нет user-approved waiver;
- production secret запрошен PR-job либо privileged job исполняет candidate-controlled code/package scripts;
- workflow/controller/scope change не прошёл обязательный review;
- host fingerprint mismatch, unexpected SSH-user или password fallback;
- штатный local tooling выбирает personal/shared key, dedicated key не связан с forced-command wrapper либо негативные shell/SFTP/rsync/PTY/forwarding/write checks не пройдены;
- `.env` mode не `0600`, parser видит неизвестный key либо password используется tooling;
- canonical deploy/state/backup path не подтверждён server wrapper или есть symlink escape;
- `BASE_UNKNOWN` ошибочно используется для three-way/automatic merge;
- независимая durable BASE copy отсутствует или не совпадает с server copy;
- два последовательных PROD manifests различаются;
- manifest содержит special file, unsafe path, unknown type/encoding или неканонический формат;
- найден secret/PII в candidate, selected snapshot, report или artifact;
- есть нерешённый conflict либо drift decision не сохранён;
- artifact/scope/controller/PROD hash или approval не совпадает с plan ID;
- exclusive deploy lock не получен;
- dry-run содержит неутверждённое удаление/tombstone/metadata change;
- disk/inode, backup или restore rehearsal failed;
- обязательный cache clear failed;
- pre-deploy health, quality, post-deploy smoke или monitoring gate failed.

## Критерии приёмки первого E2E-контура

- ADR-013 обновлён: `FILE_ONLY` boundary, trusted controller, deploy-scope, artifact, BASE trust, lock, rollback и staging decision;
- один machine-readable deploy-scope порождает manifest, artifact, dry-run, apply, backup и rollback fixtures;
- root exact files, authoritative directories, exclusions, generated file и tombstone проверены positive/negative tests;
- production secrets недоступны PR jobs, а privileged job не запускает `npm ci`/candidate scripts;
- personal key используется только для явного bootstrap/break-glass, а штатный local inventory проходит отдельным passphrase-protected forced-command key с pinned host identity и fail-closed SSH options;
- каждый deploy использует immutable artifact digest и canonical plan ID;
- `BASE_UNKNOWN` проходит только two-way bootstrap;
- production drift блокирует запись до durable reviewed decision;
- пользователь видит redacted plan/dry-run и approval связан с exact plan ID;
- server exclusive lock покрывает final scan, backup, apply и post-apply manifest;
- backup restore rehearsal воспроизводит pre-deploy manifest;
- server-owned scope не читается в snapshot и не затрагивается apply;
- stateful change автоматически блокируется и маршрутизируется в отдельный migration plan;
- staging evidence либо explicit approved waiver присутствует;
- после deploy записаны две совпадающие baseline copies, smoke/monitoring evidence и final state;
- raw production content, secrets и PII не попадают в Git, Actions logs или artifacts.

## Последовательность реализации

### Slice 1 — Contract и fixtures

1. Поддерживать принятые ADR-013 и production governance при изменении `FILE_ONLY`, transitional guarded in-place, trusted controller или independent BASE contract.
2. Выбрать durable external baseline store и staging path либо оформить user-approved bounded waiver.
3. `[done-local 2026-08-14]` Создать machine-readable deploy-scope и behavioral fixtures для directories/exclusions/root files/generated/tombstones.
4. `[done-local 2026-08-14]` Реализовать path/scope release classifier и canonical manifest/plan schema self-tests; semantic data-lifecycle review остаётся отдельным gate.

### Slice 2 — Локальный безопасный bootstrap

5. `[done-local 2026-08-14]` Реализовать `.env` parser, offline SSH preflight и local manifest-to-manifest `prod:drift:plan` без `source/eval`, вывода secrets, password fallback или user-config weakening. Фактический preflight ожидаемо блокируется текущим `.env` mode `0644` до отдельной настройки доступа.
6. Закрепить host fingerprint; personal key использовать только для bootstrap установки dedicated `tacticum_prod_bitrix_ed25519.pub` с forced-command read-only wrapper.
7. Негативно проверить shell/SFTP/rsync/PTY/forwarding/write denial, затем выполнить dedicated-key two-pass read-only inventory. Решение об отзыве personal server authorization зафиксировать отдельно.
8. Показать two-way production drift, выполнить reconciliation через clean worktree/PR и повторить scan.

### Slice 3 — Guarded apply

9. Реализовать immutable artifact, write server wrapper, exclusive lock, path/disk checks, backup/restore rehearsal и exact apply fixtures.
10. Реализовать post-apply manifest, cache-clear fail-fast, credential teardown, smoke/monitoring и manual-confirmed rollback.

### Slice 4 — GitHub controls и первый deploy

11. Настроить CODEOWNERS/ruleset, отдельные от local/personal CI read-only/write keys и GitHub environments; убедиться, что PR events не получают production secrets.
12. Прогнать end-to-end на synthetic/local fixtures и staging либо получить явный bounded waiver.
13. На финальном candidate показать plan/dry-run и запросить отдельное разрешение на production mutation.
14. Выполнить первый controlled `FILE_ONLY` deploy, smoke/monitoring, записать две baseline copies и evidence.

### Позже, отдельным решением

15. Исследовать atomic Bitrix releases/shared paths.
16. Спроектировать `STATEFUL` migration lane.
17. После restore rehearsal и production evidence решить, допустим ли автоматический rollback.
