# Журнал решений

## 2026-08-13 — accepted

- Решение: сохранить auto-deploy на `main`, но запретить production mutation до полного quality gate и deployment-config preflight.
- Источник: запрос пользователя восстановить весь GitHub delivery chain; существующий workflow contract.
- Рассмотрено: временно перевести deploy только на `workflow_dispatch`.
- Обоснование: manual-only режим меняет release expectation сильнее, чем требуется для исправления.
- Влияние: порядок jobs меняется; production trigger остаётся прежним.

## 2026-08-13 — accepted

- Решение: historical release sign-off остаётся архивным evidence, но не generic blocker будущих deploy runs.
- Источник: clean-runner failure на абсолютных `/tmp` manifests.
- Обоснование: release-specific evidence не переносимо между runners и commits.
- Влияние: generic CI проверяет checker self-test; current run сохраняет собственные smoke artifacts.

## 2026-08-13 — accepted

- Решение: Git остаётся источником истины для repo-owned кода, но любой production drift до деплоя является blocking condition. Отличия нужно показать в redacted-виде, перенести нужные server changes в отдельную Git-ветку или явно отклонить, свести конфликты и повторить scan до записи.
- Источник: явное требование пользователя проверять изменения на сервере и забирать/сводить их перед локальным deploy.
- Рассмотрено: продолжить прямой `rsync --delete`; считать production источником истины; автоматически копировать production поверх локального worktree.
- Обоснование: прямой rsync может уничтожить ручной hotfix, а автоматический import способен протащить секреты, runtime data и непроверенный код.
- Влияние: deploy получает `BASE/PROD/CANDIDATE` gate, immutable plan, checksum dry-run, повторную TOCTOU-проверку, backup и manifest lifecycle. До реализации gate merge PR #45 запрещён.

## 2026-08-13 — accepted

- Решение: разделить CI-доступ на `production-readonly` с forced-command key и `production` с отдельным write deploy key; личный Ed25519-ключ использовать только локально для пользователя `bitrix`.
- Источник: least-privilege design из `production-drift-reconciliation-plan.md`.
- Рассмотрено: один write key для inspection и deploy; передать личный private key в GitHub.
- Обоснование: read-only job должен быть способен доказать drift, но не менять production; компрометация личного ключа не должна затрагивать CI.
- Влияние: нужен дополнительный `SSH_READONLY_PRIVATE_KEY`, server-side wrapper и настройка двух GitHub environments. Contract действует, но production mutation заблокирована до executable evidence.
- Уточнение: формулировка про local personal key с 2026-08-14 ограничена bootstrap/break-glass; штатный local inventory переведён на отдельный forced-command key решением ниже.

## 2026-08-13 — accepted after challenge

- Решение: первый E2E-контур поддерживает только `FILE_ONLY`; любое требование изменить config, DB, iblock/schema/data или выполнить migration/seed/finalize блокирует apply и маршрутизируется в отдельный `STATEFUL` plan.
- Источник: E2E challenge `E2E-CH-002`; явное поручение пользователя доработать план по результатам challenge.
- Рассмотрено: считать файловый backup достаточным для любого release.
- Обоснование: откат Git-файлов не восстанавливает production state.
- Влияние: нужен release classifier и `compatibility/data-lifecycle` gate; stateful automation не входит в первый slice.

## 2026-08-13 — accepted after challenge

- Решение: временный deployment strategy — guarded in-place rsync с единым scope contract, `--delay-updates`, `--delete-delay` и server exclusive lock; atomic Bitrix releases исследовать отдельным ADR.
- Источник: E2E challenge `E2E-CH-003`, `E2E-CH-005`, `E2E-CH-012`.
- Рассмотрено: немедленно строить release-directory/symlink rollout; оставить текущие три независимые rsync-команды.
- Обоснование: guarded режим даёт меньший первый срез, но честно сохраняет residual mixed-state risk.
- Влияние: apply запрещён без lock/backup/restore fixtures; принятие residual risk остаётся ADR decision.

## 2026-08-13 — accepted after challenge

- Решение: production secrets не выдаются PR jobs; кандидат продвигается как immutable artifact, а privileged inspection/apply выполняет protected trusted controller. BASE хранится на сервере и в независимом durable store.
- Источник: E2E challenge `E2E-CH-001`, `E2E-CH-004`, `E2E-CH-009`.
- Рассмотрено: исполнять PR tooling с read-only key; доверять server-side manifest как единственной копии.
- Обоснование: read-only key всё равно даёт production visibility, а write user может изменить code и BASE одновременно.
- Влияние: нужны artifact digest, CODEOWNERS/ruleset, wrapper keys, approval binding и решение по external baseline store.

## 2026-08-13 — accepted after challenge

- Решение: перед production apply обязательно staging evidence; при отсутствии staging возможен только отдельный user-approved bounded waiver с residual risk, scope, expiry/review trigger и next validation.
- Источник: E2E challenge `E2E-CH-011`.
- Рассмотрено: считать локальные/production read-only smoke эквивалентом staging.
- Обоснование: production не должна молча оставаться первой полноценной integration environment.
- Влияние: `CI-REC-015` остаётся blocked до staging decision или явного waiver; self-waive запрещён.

## 2026-08-13 — accepted

- Решение: включить E2E production reconciliation plan в постоянную операционную модель: workflow governance является ежедневным process source, ADR-013 — architecture source, detailed PM plan — design/implementation source, а agent instructions и gap/task registers обязаны ссылаться на них.
- Источник: прямое поручение пользователя включить план в операционную модель проекта.
- Рассмотрено: оставить план только в разовом PM-пакете восстановления GitHub Actions.
- Обоснование: разовый документ не гарантирует применение правил будущими задачами, агентами и релизами.
- Влияние: governance действует немедленно как stop-policy; executable enforcement по-прежнему требует `CI-REC-016`, `CI-REC-012`—`CI-REC-015` и отдельного production approval.

## 2026-08-14 — accepted

- Решение: разделить локальный production access на personal bootstrap/manual key `id_ed25519` и отдельный постоянный `tacticum_prod_bitrix_ed25519` для forced-command read-only inventory. Штатный `.env` и `prod:*` tooling используют только dedicated key; CI получает ещё две отдельные read-only/write пары.
- Источник: решение пользователя после повторного security/access review локального ключа.
- Рассмотрено: использовать личный ключ для всех local inventory; считать client-side SSH options достаточным read-only ограничением; создать один общий local/CI key.
- Обоснование: ключ пользователя `bitrix` с обычным shell сохраняет право записи независимо от имени ключа и client flags; server-side forced command уменьшает blast radius и делает read-only свойство проверяемым.
- Влияние: `CI-REC-012` остаётся blocked до pinned host identity, dedicated key generation, wrapper installation, negative denial checks, `.env` mode/path correction и отдельного решения об отзыве personal authorization либо break-glass policy. На этом шаге local/production credentials не изменяются.
