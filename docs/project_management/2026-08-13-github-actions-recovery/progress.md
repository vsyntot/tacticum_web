# Статус выполнения

Обновлено: 2026-08-14

| Метрика | Значение |
|---|---:|
| Всего задач | 16 |
| Выполнено | 11 |
| В работе | 0 |
| Заблокировано | 4 |
| Осталось | 5 |

## Сейчас в работе

- Активных локальных задач нет. Следующий E2E-шаг `CI-REC-012` требует внешнего access decision и trusted server inputs; production access и deploy не выполнялись.

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

## Блокеры

- GitHub не содержит repository или `production` environment secrets; обязательные пять secret names отсутствуют.
- `main` не имеет branch protection/ruleset.
- На production ещё не установлен/не проверен dedicated forced-command public key для `bitrix`, host fingerprint не закреплён и достоверный deployment manifest отсутствует.
- Не принято отдельное решение: отозвать personal `id_ed25519` authorization после bootstrap либо сохранить его по formal break-glass policy.
- Текущий workflow не умеет останавливать deploy при ручном production drift, не показывает checksum dry-run и не проверяет TOCTOU перед rsync.
- Принятый ADR/governance ещё не обеспечен production wrapper/controller, immutable artifact promotion, lock, backup/restore, approval binding и rollback tooling.
- Независимый durable baseline store и staging path не выбраны; waiver отсутствует.
- Merge PR запускает production deployment, поэтому до настройки secrets и явного merge/deploy решения выполнять его нельзя.

## Следующий шаг

- Разблокировать `CI-REC-012`: получить trusted host fingerprint независимым каналом, создать dedicated passphrase-protected key, исправить `.env` mode/paths, согласовать и установить forced-command read-only wrapper через отдельную bootstrap-сессию. До этого не подключаться к production.

## Gate state

- `security/access`: `blocked` — credential roles приняты, но dedicated local key, pinned host identity, forced-command denial checks, personal-key revoke/break-glass decision и CI wrappers ещё не реализованы.
- `privacy/data`: `open` — до snapshot нужны allowlist, secret/PII scan и redaction; raw production content запрещён в GitHub artifacts.
- `compatibility/data-lifecycle`: `blocked` — path/scope classifier работает и блокирует известные stateful paths, но семантически не доказывает отсутствие config/DB/iblock impact; перед каждым artifact promotion нужен ручной review.
- `QA/release`: `blocked` — staging/waiver, immutable artifact, lock, dry-run, restore rehearsal, smoke и monitoring ещё не реализованы end-to-end.
- `external mutation`: `not_run` — production и GitHub settings на этом шаге не изменялись.
- `operational docs validation`: `passed` после contract self-test, scope check, JS/audit checks и синхронизации реестров.
