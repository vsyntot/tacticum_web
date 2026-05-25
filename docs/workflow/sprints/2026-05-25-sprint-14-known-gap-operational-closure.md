# Sprint 14 — Known Gap Operational Closure

Дата: 25.05.2026

## Цель

Доработать 100% известных незакрытых gaps/tasks, которые остались после Sprint 13, не подменяя внешние проверки синтетическим закрытием. Code-level gaps в репозитории отсутствуют; оставшийся хвост относится к release evidence, внешним доступам, логам/CRM и post-deploy smoke после cache refresh.

## Scope

| ID | Status | Area | Closure |
|---|---|---|---|
| S14-001 | done | Known gaps visibility | Добавлен `npm run gaps:known`, который машинно показывает code-level open gaps, pending release gates, legacy inventory pending rows и post-deploy/cache smoke хвост |
| S14-002 | done | Release pending gates discipline | `release:signoff:draft-check` теперь требует `due` у каждого `pending` gate; draft sign-off обновлён явными `due` для ручных gates |
| S14-003 | done | Release checker regression | `release:signoff:self-test` расширен негативным кейсом: pending gate без `due` должен падать |
| S14-004 | done | Documentation | `release-signoff-gates.md`, `gap-analysis.md` и этот sprint artifact фиксируют текущее состояние известных gaps |
| S14-005 | external handoff | Manual/external evidence | `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream`, legacy access logs/CRM inventory и post-deploy smoke остаются внешними gates с owner/due/evidence rules |

## Known Remaining External Work

| Gap | Owner | Due | Closure evidence |
|---|---|---|---|
| `manual-success-flow` | QA + Backend/Frontend | before strict release closure | Staging или controlled production проверка форм, modal, AI chat, prefill и staff-order без PII |
| `metrika-goals` | PM/Marketing + QA | before strict release closure | Подтверждение целей/событий в Яндекс.Метрике без PII в параметрах |
| `bitrix-admin` | QA/Admin | before strict release closure | Authenticated `/bitrix/admin/` и public admin toolbar после deploy/cache refresh |
| `staff-sale-upstream` | Architect + Backend + QA + DevOps | before strict release closure | Upstream/CRM подтверждает staff-order поля `workers_json`, `team_preset`, `monthly_budget_estimate`, `endDate` |
| Legacy aliases inventory | PM + Backend + DevOps | inventory `30.06.2026`, migration plan `31.08.2026`, final mode `30.09.2026` | Access logs/CRM aggregate report без PII по `tacticum_offer.php` и `tacticum_sale.php` |
| Post-deploy/cache smoke | DevOps + QA | after deploy/cache refresh | `visual:smoke:prod`, `browser:smoke:prod`, `browser:smoke:price` после очистки Bitrix/menu/component cache |

## Verification

- `npm run gaps:known`
- `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json`
- `npm run release:signoff:self-test`
- `node --check tools/known-gaps-check.mjs`
