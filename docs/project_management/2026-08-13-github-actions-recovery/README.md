# Восстановление GitHub Actions

Цель пакета — восстановить проверяемую цепочку `push/PR → quality gates → deploy → post-deploy smoke` для `vsyntot/tacticum_web`.

## Навигация

- `product_technical_profile.md` — фактический профиль delivery-контура.
- `challenge_findings.md` — подтверждённые дефекты и неподтверждённые состояния.
- `work_breakdown_plan.md` — план исправления.
- `task_register.csv` — реестр задач и счётчики.
- `progress.md` — текущее состояние.
- `risk_register.csv` — риски.
- `decision_log.md` — принятые решения.
- `evidence_index.csv` — доказательства.
- `execution_checklist.md` — последовательные проверки.
- `github_handoff.md` — точные внешние настройки и контрольный запуск.
- `production-drift-reconciliation-plan.md` — E2E-сверка `BASE/PROD/CANDIDATE`, безопасное сведение production drift, immutable deploy plan и rollback.
- `../../workflow/production-deployment-governance.md` — канонический ежедневный production release process; ADR-013 закрепляет архитектурный contract.

## Scope boundary

- `read_scope`: весь локальный репозиторий; публичные GitHub Actions metadata/logs; публичные GET/HEAD `https://tacticum.ru`; после настройки доступа — read-only manifest и явно выбранные repo-owned файлы production через пользователя `bitrix` по allowlist из E2E-плана.
- `write_scope`: `.github/workflows/`, `package.json`, `package-lock.json`, generated `local/templates/tacticum/tailwind.generated.css`, непосредственно связанные `tools/`, ADR, workflow governance, agent instructions и этот operational package. На текущем шаге contract включён в операционные документы; production tooling остаётся следующей задачей.
- `off_limits`: `bitrix/`, production data, GitHub secrets values, authenticated Bitrix admin, формы/лиды, deployment без отдельного разрешения.
- `remote mutation`: запрещена до отдельного разрешения пользователя после production drift report и dry-run.
