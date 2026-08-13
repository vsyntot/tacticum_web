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

## Scope boundary

- `read_scope`: весь локальный репозиторий; публичные GitHub Actions metadata/logs; публичные GET/HEAD `https://tacticum.ru`.
- `write_scope`: `.github/workflows/`, `package.json`, `package-lock.json`, generated `local/templates/tacticum/tailwind.generated.css`, непосредственно связанные `tools/`, ADR и этот operational package. Generated CSS добавлен после того, как GitHub runner подтвердил drift при обновлении Tailwind.
- `off_limits`: `bitrix/`, production data, GitHub secrets values, authenticated Bitrix admin, формы/лиды, deployment без отдельного разрешения.
- `remote mutation`: запрещена до явного разрешения пользователя.
