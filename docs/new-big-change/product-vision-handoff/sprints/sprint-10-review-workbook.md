# Sprint 10 Review Workbook

Дата: 02.06.2026

Статус: ready-for-owner-review workbook. Документ помогает провести PM/UX/Sales/Content review по pilot kits, role-based CJM, CTA taxonomy and returning-lead journey. Он не закрывает gaps без owner approval.

## Цель Сессии

Превратить текущие product use cases and product-aware CTA context в decision-grade CJM:

```text
product use case
  -> pilot kit
  -> role path
  -> CTA intent
  -> current form context or structured-field blocker
```

## Участники

| Role | Required | Decision area |
|---|---|---|
| PM / Product owner | Yes | pilot kit approval, product priority, CTA intent |
| UX / Designer | Yes | role path, page placement, CTA hierarchy |
| Sales | Yes | discovery realism, lead routing usefulness |
| Content / Editor | Yes | public wording and pilot kit clarity |
| Architect | Recommended | Platform and deployment/security readiness |
| Tech Lead | Recommended | Dev workflows |
| Backend + QA | Recommended | `lead_scenario`, fallback and structured field implications |
| Security / Legal | Conditional | documentation/procurement/proof wording |

## Pre-Read

- `sprint-10-pilot-kits-cjm-cta.md`
- `sprint-10-pilot-kit-records.md`
- `sprint-10-cjm-cta-records.md`
- `sprint-10-approval-request.md`
- `../25-post-challenge-use-cases-and-cjm.md`
- `../17-local-gap-decision-briefs.md`
- `../19-phase-3-architecture-integration-decision-pack.md`
- `../28-post-challenge-decision-backlog.md`

## Agenda

| Block | Duration | Output |
|---|---:|---|
| Sprint 09 dependency check | 10 min | Confirm taxonomy/claims assumptions or mark blockers |
| Pilot kit standard | 15 min | Approve required fields and proof status rules |
| Product pilot kit review | 50 min | Approve/rewrite Platform, Agents, Dev, Forum pilot kits |
| Role-based CJM | 25 min | Approve buyer role paths and returning-lead path |
| CTA taxonomy and Sales routing | 25 min | Confirm CTA intents and `lead_scenario` usefulness |
| Closure | 10 min | Assign blockers, update records |

## Decision Statuses

| Status | Meaning |
|---|---|
| `approved` | Owner approved for public/design/implementation use |
| `approved-v1-safe` | Safe for v1; stronger behavior or claim remains blocked |
| `rewrite-required` | Direction accepted; wording must change |
| `sprint-09-blocked` | Waiting on taxonomy/claims/packaging decisions from Sprint 09 |
| `integration-blocked` | Requires CRM/upstream/form contract decision |
| `deferred` | Not needed for current release |
| `rejected` | Must not be used |

## Pilot Kit Review Questions

For every pilot kit:

| Question | Required answer |
|---|---|
| Does the trigger sound like a real buyer situation? | approve / rewrite |
| Is the owner a real sponsor/operator? | approve / rewrite |
| Is pilot readiness realistic and non-sensitive? | approve / rewrite |
| Is pilot output an artifact, not result guarantee? | approve / rewrite |
| Is proof status honest? | approve / block |
| Does CTA fit current form contract? | approve / integration-blocked |

## Role Path Review Questions

| Role path | Required decision |
|---|---|
| Economic buyer | Does path explain ecosystem, product fit, pilot and proof boundaries? |
| Technical buyer | Does path lead to architecture/deployment/security detail? |
| Security/procurement | Does path request discussion/materials without unsupported claims? |
| Functional owner | Does path show effort, input and pilot output? |
| Returning lead | Does path continue discussion without personal recognition or hidden PII? |

## CTA Review Questions

| CTA family | Required decision |
|---|---|
| `pilot` | Which products/use cases use this as primary CTA? |
| `architecture-session` | Which technical/security flows use this? |
| `scenario-selection` | Is current `lead_scenario` select enough for v1? |
| `documentation-request` | Is request-only wording allowed without downloads? |
| `estimate` | When should route go to `/offer/`, `/calculator/`, `/price/`? |

## Closure Checklist

| Item | Required before Sprint 10 close |
|---|---|
| Pilot kit standard approved | yes |
| Platform pilot kits approved/rewrite-blocked | yes |
| Agents pilot kits approved/rewrite-blocked | yes |
| Dev pilot kits approved/rewrite-blocked | yes |
| Forum pilot kits approved/rewrite-blocked | yes |
| Role paths approved | yes |
| CTA taxonomy approved | yes |
| Sales confirms current context/fallback or opens integration blocker | yes |
| No new form/CRM fields assumed silently | yes |
| `npm run product:gaps:check` green | yes |

## After The Session

1. Update `sprint-10-pilot-kit-records.md` with owner statuses.
2. Update `sprint-10-cjm-cta-records.md` with approved role/CTA decisions.
3. Open Sprint 12 structured-fields scope if Sales rejects current fallback.
4. Update product data/copy only after approved wording exists.
5. Keep `CJM-*`, `PB-*` and `ARCH-003` gaps open until approvals/evidence exist.
