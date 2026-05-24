# Legacy Sale Alias Consumer Inventory

Дата фиксации: 24.05.2026

Status: `in progress` до завершения access logs / CRM inventory к `30.06.2026`.

## Purpose

Этот artifact закрывает операционную часть sunset-плана для legacy sale aliases:

- `/local/rest/tacticum_offer.php`;
- `/local/rest/tacticum_sale.php`.

Successor endpoint: `/local/rest/tacticum_form.php`.

Цель: до `30.06.2026` понять, есть ли реальные consumers legacy aliases, назначить owner для каждого consumer и подготовить миграцию до `31.08.2026`, чтобы к `30.09.2026` выбрать финальный режим aliases: удалить, вернуть `410 Gone`, применить проверенный redirect или продлить поддержку как accepted risk.

## Evidence Rules

- Не сохранять в репозитории имена, телефоны, email, тексты сообщений, raw request/response payloads, cookie, session IDs, CSRF tokens, IP addresses или user-agent strings целиком.
- В docs/issue хранить только агрегаты: endpoint, окно проверки, last seen timestamp, количество запросов, masked source label, owner, migration status.
- Если исходный лог содержит IP/user-agent/referrer, сохранить наружу только ссылку на внутренний ticket/report ID и masked consumer label.
- Время фиксировать в ISO-формате с timezone, например `2026-06-30T18:00:00+03:00`.
- Если consumer не идентифицирован, строка не считается закрытой без owner для расследования.

## Current Evidence

| Check | Date | Result | Notes |
|---|---|---|---|
| Repository source scan | 24.05.2026 | no first-party callers found | Command below returned no content references |
| Endpoint files | 24.05.2026 | aliases still exist | `local/rest/tacticum_offer.php` and `local/rest/tacticum_sale.php` keep response shape and send `Deprecation`, `Sunset`, `Link: rel="successor-version"` |
| Production access logs | pending | pending | PM + Backend must check exact endpoint hits through `30.06.2026` |
| CRM/upstream source reports | pending | pending | PM + Backend must match legacy endpoint leads without exposing PII |

```bash
rg -n '/local/rest/tacticum_(offer|sale)\.php|tacticum_offer\.php|tacticum_sale\.php' \
  --glob '!docs/**' \
  --glob '!tools/**' \
  --glob '!node_modules/**'
```

## Data Sources To Check

| Source | Owner | Window | Expected Output |
|---|---|---|---|
| Web access logs | Backend + DevOps | `2026-05-24` - `2026-06-30` | Aggregated hits by endpoint, week, masked source label and last seen timestamp |
| CRM/upstream lead source reports | PM + Backend | `2026-05-24` - `2026-06-30` | Lead counts where source endpoint is `tacticum_offer` or `tacticum_sale`, no PII |
| Partner/integration register | PM | до `2026-06-30` | Known external owners and migration contact path |
| Repo/source scan | Backend | each release touching sale flow | Confirmation that first-party code does not reintroduce direct calls to legacy aliases |

## Inventory Table

| Consumer / Source Label | Endpoint | Evidence Link / ID | Window | Last Seen At | Count | Owner | Migration Target | Target Date | Status | Notes |
|---|---|---|---|---|---:|---|---|---|---|---|
| first-party repo callers | both | local repo scan 24.05.2026 | current tree | not seen | 0 | Frontend + Backend | already uses current form/staff endpoints | done | done | No content references outside docs/tools |
| production access logs | `tacticum_offer.php` | pending internal report | `2026-05-24` - `2026-06-30` | pending | pending | Backend + DevOps | `/local/rest/tacticum_form.php` | `2026-08-31` | pending | Aggregate only |
| production access logs | `tacticum_sale.php` | pending internal report | `2026-05-24` - `2026-06-30` | pending | pending | Backend + DevOps | `/local/rest/tacticum_form.php` | `2026-08-31` | pending | Aggregate only |
| CRM/upstream source report | both | pending internal report | `2026-05-24` - `2026-06-30` | pending | pending | PM + Backend | `/local/rest/tacticum_form.php` | `2026-08-31` | pending | Match by safe source IDs only |

Allowed statuses: `pending`, `identified`, `owner-assigned`, `migration-planned`, `migrated`, `no-traffic`, `accepted-risk`, `done`.

## Runbook

1. Query web access logs for exact `POST /local/rest/tacticum_offer.php` and `POST /local/rest/tacticum_sale.php` paths.
2. Group results by endpoint, week and masked consumer label. Do not copy raw log lines into docs.
3. Cross-check CRM/upstream reports for leads created through legacy endpoints.
4. For every non-zero consumer, assign owner and migration path to `/local/rest/tacticum_form.php`.
5. If source cannot be identified, assign Backend + DevOps owner and leave status `owner-assigned`, not `done`.
6. After `30.06.2026`, update Sprint 10 and Sprint 09 artifacts with the inventory result.
7. After `31.08.2026`, confirm migration status for every consumer or create accepted-risk sign-off.
8. Before `30.09.2026`, choose final alias mode and sync implementation, `tools/legacy-sale-sunset-check.mjs`, release notes and release sign-off.

## Closure Criteria

- Access logs and CRM/upstream reports checked for the full window through `30.06.2026`.
- Every non-zero consumer has owner, target endpoint, migration target date and status.
- No PII or raw payload evidence is stored in repo.
- Migration is complete by `31.08.2026` or accepted risk is approved by PM + Architect.
- Final alias mode is implemented before `30.09.2026` or support extension is explicitly documented with a new sunset date.
