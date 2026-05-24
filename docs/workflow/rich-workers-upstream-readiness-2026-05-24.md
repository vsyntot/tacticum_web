# Rich Workers Upstream Readiness — 24.05.2026

Status: `done as decision`

## Scope

Baseline для Sprint 10 item `S10-009`: определить, можно ли переключать `/price/` staff-order на отдельный rich workers upstream endpoint.

## Current Decision

Переключение не выполняется.

Причина: отдельный совместимый upstream workers contract в текущем repo/docs отсутствует; `lead-form-contract.md` прямо фиксирует, что отдельного `/tacticum/v1/sale/workers` в актуальном OpenAPI нет. Поэтому production/staging должны сохранять:

```text
ai.endpoint_paths.staff_sale = /tacticum/v1/chat_agent/sale
```

## Evidence

| Area | Result | Evidence |
|---|---|---|
| Config switch exists | passed | `tacticum_config.example.php` содержит `ai.endpoint_paths.staff_sale` |
| Default route is current adapter | passed | `local/rest/tacticum_sale_staff.php` использует `tacticum_rest_get_ai_endpoint_path('staff_sale', '/tacticum/v1/chat_agent/sale')` |
| Rich payload preserved | passed | `/price/` contract сохраняет `workers_json`, `workers[]`, `team_preset`, `monthly_budget_estimate`, `endDate` |
| Current adapter behavior | accepted | Rich staff details адаптируются в текстовый `task` для текущего `/tacticum/v1/chat_agent/sale` |
| Future switch matrix | passed | ADR-006 и Sprint 09 sale/upstream artifact фиксируют условия переключения и rollback |

## Rules

- Не менять `ai.endpoint_paths.staff_sale` без OpenAPI/contract от upstream.
- Если будущий contract принимает текущий `workers[]` без изменения frontend payload и response model, переключение делается только через server config, затем `config-sync`, `health_config` и staging staff-order smoke.
- Если contract требует другой request/response model, это новая Security / Integration задача с обновлением ADR-006, `lead-form-contract.md`, adapter и smoke.
- `staff-sale-upstream` release gate остаётся pending до реальной staging/controlled production проверки, что `workers_json`, `team_preset`, `monthly_budget_estimate` и `endDate` доходят до upstream/CRM.
