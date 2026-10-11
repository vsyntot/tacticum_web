# Статус выполнения

Обновлено: 2026-10-11

| Метрика | Значение |
|---|---:|
| Всего задач | 16 |
| Выполнено | 13 |
| В работе | 3 |
| Заблокировано | 0 |
| Осталось | 3 |

## Сейчас в работе

- В работе `CI-REC-009`, `CI-REC-014` и `CI-REC-015`: production FILE_ONLY apply завершён, main/environment protection включены, trusted CI keys/controller установлены, а merge, controlled run, observation и durable BASE promotion остаются отдельными шагами.

## Последние изменения

- Подтверждены 79/79 failed deploy runs и единая историческая причина `Setup SSH key`.
- Локально воспроизведён stale `/tmp` sign-off blocker.
- Tailwind toolchain обновлён до `4.3.3`; `npm audit` возвращает `0 vulnerabilities`.
- Reusable quality gate выполняется до SSH/rsync; deployment configuration и rsync exclusion имеют self-tests.
- Smoke evidence направлен в current-run Actions artifact; historical `/tmp` draft исключён из generic deploy.
- `actionlint`, PHP lint и полный локальный quality/release matrix прошли; production public precheck прошёл.
- Обновлённый `release:product-first:prod-check` полностью прошёл против production в read-only режиме.
- Draft PR `#45` создан; первый GitHub run подтвердил PHP/security jobs и обнаружил drift generated CSS после Tailwind upgrade.
- `tailwind.generated.css` пересобран Tailwind `4.3.3`; полный injected CSS visual/action smoke прошёл на 13 публичных страницах desktop/mobile.
- Повторный GitHub run `31690737872` прошёл: PHP 8.4, CSS/dependency/repository matrix и security conventions зелёные.
- Draft PR `#45` обновлён из `agent/recover-github-actions`: contract-tooling commit `778c12e9` прошёл remote Quality Gate run `31745423204` — PHP 8.4, security/conventions и static/config lifecycle jobs зелёные; PR остаётся draft.
- Принят обязательный E2E gate: до production mutation показать `BASE/PROD/CANDIDATE` drift, перенести нужные server changes в Git/свести конфликты, повторить scan, показать checksum dry-run и проверить неизменность production непосредственно перед apply.
- Новый trusted workflow фиксирует immutable artifact, read-only manifest, three-way plan и forced-command apply; legacy production `rsync`/shell steps удалены.
- Сохранён `production-drift-reconciliation-plan.md`; production scan, SSH mutation и deploy на этом шаге не выполнялись.
- Challenge выявил 12 design findings: trust boundary, stateful rollback, ownership mismatch, BASE integrity, server lock, bootstrap semantics, rollback completeness, `.env` authority, approval binding, manifest schema, staging/monitoring и чрезмерный initial scope.
- E2E-план пересобран: первый контур только `FILE_ONLY`; введены machine-readable deploy-scope, immutable artifact, trusted controller, dual BASE, two-way bootstrap, exclusive lock, restore rehearsal и staging-or-waiver gate.
- Contract принят в обновлённом ADR-013 и включён в `production-deployment-governance.md`, lifecycle/DoR/DoD, AGENTS/Copilot/DevOps instructions, current-state и gap-analysis. Документация явно отделяет действующую stop-policy от ещё не реализованного enforcement.
- `CI-REC-016` закрыт локально: единый `tools/deploy-scope.json`, path/scope classifier, canonical manifest/plan, fail-closed `.env`/SSH preflight и directory/root/generated/tombstone/security fixtures проходят и включены в PR Quality Gate.
- `npm run prod:scope:prepare` создаёт ignored generated sitemap на clean runner и подтверждает 375 canonical entries, scope `2026-08-14.1`, manifest hash `2cd54724b29b343623473d74d020c1559a97387b80a1bb451b02b8ea59ce5b73`; `prod:contract:self-test`, `js:check` и `npm audit --audit-level=high` проходят.
- Реальный `prod:preflight` останавливается до сети на ожидаемом нарушении `.env` mode `0644`; значения `.env` не выводились. `FILE_ONLY` результат помечен как path-only assurance и всегда оставляет `productionMutationAllowed=false` до data-lifecycle review и guarded apply.
- Clean GitHub runner подтвердил новый gate: generated sitemap создаётся до scope check, contract/self-tests проходят без production credentials; run `31745423204` success для head `778c12e9`.
- После повторного local SSH review принято разделение credentials: `id_ed25519` — только explicit bootstrap/break-glass, `tacticum_prod_bitrix_ed25519` — штатный forced-command read-only inventory; CI read/write keys остаются отдельными.
- Локальные metadata подтверждают personal Ed25519 mode `0600`, passphrase и fingerprint, но access ещё не готов: `.env` mode `0644`, dedicated key/public path и pinned known-hosts отсутствуют, default SSH допускает interactive trust/password/multiple identities.

- Выполнен user-authorized bounded FILE_ONLY production snapshot: 14 authoritative directories, 371 одинаковый файл, 0 remote-only, 2 расхождения. Production Tailwind 4.1.8 и sitemap с lastmod 2026-06-07 старее локального baseline (Tailwind 4.3.3 / 2026-08-13), поэтому импорт не выполнялся; `bitrix/`, `upload/`, config и data не затрагивались. Evidence: `production-reconciliation-2026-10-11.json`.
- Установлен dedicated passphrase-protected `bitrix` key с pinned host fingerprint и forced-command manifest wrapper; shell, empty command, SFTP и PTY probes rejected. Два consecutive production manifests совпали: 376 entries, hash `736095634beb3738dc70f595745620481dc26969ffc36863a64b30c399f05063`; evidence: `production-reconciliation-2026-10-11-dedicated-manifest.json`.
- Выполнен утверждённый FILE_ONLY rollout по plan `637e64de088c5ac1519b7539923bacba8cf35f7c7e6f3eaebfbe25fc58818f70`: четыре path-level drift changes применены под двумя lock-файлами, immutable artifact, backup/restore rehearsal и cache clear; post-manifest стабилен, public precheck/SEO/browser smoke зелёные. Evidence: `production-rollout-2026-10-11-file-only.json`; две hash-only BASE-копии сохранены локально и root-only на сервере.
- PR `#46` и CI-only PR `#47` смёрджены в `main`; branch protection, required Quality Gate checks и `production` reviewer gate включены. Sitemap workflow исправлен на stdlib XML parser и прошёл run `38158939102`; legacy deploy runs отменены в ожидании environment, без SSH/rsync mutation. Evidence: `github-controls-merge-2026-10-11.json`.
- Trusted GitHub FILE_ONLY run `38162927993` approved by the owner and completed successfully: exact plan `f136361e…`, immutable artifact, forced-command apply, lock, backup/restore rehearsal, cache clear and post-deploy smoke all passed. Post-manifest `98fd4544…` contains 376 entries and matches controller evidence; local production precheck, SEO, console and price smoke also passed. Evidence: `production-trusted-ci-run-2026-10-11.json`.

## Блокеры

- GitHub repository secrets остаются пустыми; environment `production` и `production-readonly` содержат только отдельные CI names, без personal key.
- `main` classic branch protection включена; отдельные CODEOWNERS/ruleset ещё не настроены.
- После bootstrap personal `id_ed25519` остаётся только break-glass средством текущей сессии; dedicated key не использует personal shell boundary.
- Path-level drift reconciliation закрыт для plan `637e64de`; дальнейшие изменения требуют нового manifest/approval, а observation window после rollout остаётся открытым.
- Trusted workflow останавливает production drift до approval, показывает plan hashes и передаёт immutable artifact в pinned controller; первый remote run и отдельный rollback exercise остаются открытыми.
- Рабочий wrapper/controller, immutable artifact, lock, approval binding и backup/restore rehearsal доказаны для этого rollout; GitHub trusted write path установлен, но ещё не прошёл controlled run.
- Staging отсутствует и закрыт bounded owner waiver; две BASE-копии сохранены в локальном evidence и root-only server domains, но durable external store и GitHub release controls ещё не настроены.
- PR #46 и PR #47 смёрджены; main protection и production reviewer gate включены. Legacy deploy после merge не запускался; новый trusted workflow ждёт merge, controlled reviewer run и observation window.

## Следующий шаг

- Для `CI-REC-009`, `CI-REC-014` и `CI-REC-015` завершить observation window, обновить durable BASE двумя независимыми копиями и оформить CODEOWNERS/ruleset; application apply и controlled reviewer run уже доказаны evidence E-034.

## Gate state

- `security/access`: `partial` — CI read-only/write keys, pinned host identity and forced wrappers installed; CODEOWNERS/ruleset and separate rollback exercise remain open.
- `privacy/data`: `open` — до snapshot нужны allowlist, secret/PII scan и redaction; raw production content запрещён в GitHub artifacts.
- `compatibility/data-lifecycle`: `blocked` — path/scope classifier работает и блокирует известные stateful paths, но семантически не доказывает отсутствие config/DB/iblock impact; перед каждым artifact promotion нужен ручной review.
- `QA/release`: `blocked` — staging/waiver exists, immutable artifact and controller checks pass locally, but trusted workflow run and post-deploy monitoring are not yet accepted.
- `external mutation`: `partial` — GitHub environments/secrets and production forced-command tooling changed; application files were not mutated.
- `operational docs validation`: `passed` после contract self-test, scope check, JS/audit checks и синхронизации реестров.
