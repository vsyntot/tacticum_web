# ADR-013: GitHub Actions quality, production reconciliation and deploy gates

Статус: принято
Дата: 2026-08-13
Уточнено: 2026-08-13 после E2E challenge; 2026-08-14 — разделение personal/local/CI credentials

## Контекст

Production workflow запускался на каждый push в `main`, но до rsync выполнял только PHP lint. Полные repository/security checks существовали только в PR workflow, который не запускался в доступной истории. Все 79 deploy runs завершились ошибкой на пустом `SSH_PRIVATE_KEY`; следующие шаги фактически не были проверены. Generic deploy также зависел от historical release sign-off с абсолютными `/tmp` smoke paths.

После исправления quality chain обнаружен более фундаментальный риск: прямой `rsync --delete` не сравнивает кандидат с фактическим production и способен молча уничтожить ручной hotfix. Один write key, server-side BASE и файловый backup недостаточны: PR code не должен получать production visibility, write user способен изменить live files и BASE, а файловый rollback не восстанавливает config/DB/iblock state.

Полный threat model, scope semantics и E2E flow описаны в [Production Deployment Governance](../workflow/production-deployment-governance.md) и [production drift reconciliation plan](../project_management/2026-08-13-github-actions-recovery/production-drift-reconciliation-plan.md).

## Решение

1. `pr-check.yml` остаётся reusable Quality Gate и вызывается для PR и до deployment. Production mutation разрешена только после полного quality gate и fail-fast preflight.
2. Первый production delivery contour поддерживает только доказанные `FILE_ONLY` releases. `STATEFUL` changes блокируются и требуют отдельного migration plan с data/config rollback.
3. Один machine-readable deploy-scope contract определяет authoritative directories, exact root files, generated files, tombstones и server-owned exclusions для artifact, manifest, scan, dry-run, apply, backup и rollback.
4. Candidate продвигается как immutable artifact с canonical manifest, commit/tree SHA, artifact digest, scope/controller/schema versions. Artifact строится без production secrets и не изменяется после сборки.
5. Production secrets запрещены для PR events. Privileged inspection/apply выполняет protected trusted controller, который не checkout-ит и не исполняет candidate-controlled scripts. Personal bootstrap/manual, dedicated local read-only, CI read-only и CI write keys — разные credentials. Штатный local inventory использует forced-command wrapper; personal key не считается read-only и не используется tooling.
6. До каждой записи выполняется `BASE ↔ PROD ↔ CANDIDATE` reconciliation. При `BASE_UNKNOWN` разрешён только two-way bootstrap; первый BASE создаётся после controlled deploy и verification. Необъяснённый drift блокирует deploy до Git reconciliation или durable reviewed decision.
7. BASE хранится в двух независимых trust domains: operational copy вне webroot и durable external copy. Обе копии связаны с commit, artifact digest, run и approval record.
8. Approval относится к exact canonical `plan_id`, artifact digest, PROD hash, scope/controller versions, expiry и deletions/tombstones. Изменение любого связанного входа инвалидирует approval.
9. Write controller берёт server-side exclusive lock от final manifest до post-apply manifest, повторно сверяет production, проверяет disk/inodes, создаёт полный scope backup и выполняет restore rehearsal до apply.
10. Переходная strategy — guarded in-place rsync из immutable artifact с единым filter/options contract, `--checksum`, `--delay-updates`, `--delete-delay`, `--no-owner`, `--no-group`. Atomic release directories/symlink остаются отдельным Bitrix feasibility/ADR решением.
11. Staging evidence обязательно. Если staging отсутствует, допускается только отдельный user-approved bounded waiver с scope, residual risk, expiry/review trigger и next validation; исполнитель не может self-waive.
12. После credential teardown отдельный unprivileged job выполняет smoke и monitoring. `verified` фиксируется только после совпадения post-deploy/candidate manifests, успешных gates и записи двух совпадающих BASE copies.
13. Host authenticity задаётся заранее проверенным `SSH_KNOWN_HOSTS`; runtime trust-on-first-use и автоматическое принятие нового host key запрещены.
14. Current-run smoke evidence публикуется как Actions artifact без raw production content, secrets или PII. Historical release-specific `/tmp` evidence не является generic blocker.

## Последствия

- Принятый process строже текущей реализации. До проверки deploy-scope/classifier/manifests, trusted wrappers, lock/backup/restore, staging/waiver, GitHub environments и dual BASE merge/deploy PR `#45` заблокирован.
- Auto-deploy trigger на `main` может сохраниться, но write job должен останавливаться на всех governance gates и требовать approval exact plan.
- Изменения `bitrix/**` и docs-only changes не должны приводить к production mutation; `bitrix/**` остаётся запрещённой рабочей зоной.
- Runtime server-owned config, uploads, logs, DB/iblocks и PII не входят в file inventory или artifact.
- Browser smoke не имеет SSH credentials. Privileged job не выполняет dependency installation или candidate package lifecycle scripts.
- Concrete external BASE store, staging path/waiver, wrapper installation paths, retention и monitoring thresholds фиксируются в implementation/runbook до первого apply, не ослабляя это решение.

## Rollback

Rollback является частью утверждённого plan, а не импровизированной командой. Для `FILE_ONLY` он восстанавливает полный pre-deploy managed-file manifest, включая удаление новых файлов и восстановление metadata, затем очищает cache и повторяет smoke/monitoring. Автоматический rollback запрещён до успешного restore rehearsal и отдельного решения.

Для `STATEFUL` файловый rollback недостаточен: действует отдельный migration rollback plan. Откат самого workflow/ADR commit не откатывает уже изменённый production.

## Альтернативы

- Прямой `rsync --delete` после quality gate отклонён: он не защищает ручные production changes.
- Один write key для inspection/apply отклонён: он нарушает least privilege.
- Единственная BASE copy на production отклонена: она находится в trust domain write user.
- Немедленный symlink/atomic rollout отложен: требует отдельной проверки совместимости Bitrix shared/runtime paths.
