# Product-First Manual Gates Handoff

Дата: 07.06.2026

Release: `2026-06-01-product-first`

Sign-off draft: `docs/workflow/release-signoff-2026-06-01-product-first.draft.json`

Base URL: `https://tacticum.ru`

Release commit: `218ce2119617a160e42772ba5bbb5acb1c533551`

## Статус

Этот документ фиксирует handoff по ручным release gates после deploy/cache refresh и автоматических проверок 07.06.2026.

Автоматические gates уже закрыты в sign-off draft:

| Gate | Status | Evidence |
|---|---|---|
| `automated-deploy-smoke` | `passed` | `release:public-precheck:prod`, production visual/SEO smoke, warning-aware browser action smoke |
| `seo-rendered-head` | `passed` | `/tmp/tacticum-release-product-first-2026-06-07/seo/manifest.json` |
| `price-team-presets` | `passed` | `/tmp/tacticum-release-product-first-2026-06-07/price/manifest.json` |
| `css-js-e2e-readiness` | `passed` | JS/CSS/template checks plus production visual/browser/price manifests |
| `content-public-hygiene` | `passed` | Production rendered hygiene, 13 pages, `issues_found=0`, `checked_at=2026-06-07T06:34:56Z` |

Ручные gates остаются pending и не должны закрываться синтетически:

| Gate | Owner | Due | Почему pending |
|---|---|---|---|
| `manual-success-flow` | QA + Backend/Frontend | `before-strict-product-first-release-closure` | Нужен controlled staging или controlled production submit по affected form/chat/prefill flows без PII в evidence |
| `metrika-goals` | PM/Marketing + QA | `before-strict-product-first-release-closure` | Нужна owner-проверка целей в Яндекс.Метрике после product-first CTA/navigation changes |
| `bitrix-admin` | QA/Admin | `after-product-first-deploy-cache-refresh` | Нужен authenticated smoke `/bitrix/admin/` и public Bitrix toolbar после deploy/cache refresh |

`staff-sale-upstream` в этом релизе `not_applicable`, потому что `staff_sale` endpoint path, `workers_json` schema и upstream workers contract не менялись. Если QA решит включить `/price/` staff-order в общий `manual-success-flow`, фиксировать его как safe flow evidence внутри `manual-success-flow`; отдельный `staff-sale-upstream` gate открывать только при изменении staff/upstream contract.

## Helper Artifacts

Helper outputs сгенерированы локально как операционный материал, а не как release evidence:

| Artifact | Назначение |
|---|---|
| `/tmp/tacticum-release-product-first-2026-06-07/manual-gates/release-manual-gates-helper.json` | Draft-aware список pending/manual gates, next actions and safe skeletons |
| `/tmp/tacticum-release-product-first-2026-06-07/manual-gates/manual-success-flow-helper.txt` | Payload/browser/curl/evidence templates for owner-run success-flow |
| `/tmp/tacticum-release-product-first-2026-06-07/manual-gates/metrika-goals-helper.txt` | Goal taxonomy, deployed JS source check, owner checklist, browser observer and evidence skeleton |
| `/tmp/tacticum-release-product-first-2026-06-07/manual-gates/bitrix-admin-gate-helper.txt` | Authenticated admin/public toolbar checklist and evidence skeleton |

Эти файлы не коммитить как доказательство. В sign-off переносить только owner-confirmed safe evidence.

## Commands

Сводный helper:

```bash
npm run release:manual-gates:helper -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json --all
npm run release:manual-gates:helper -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json --all --json
```

`manual-success-flow`:

```bash
npm run release:manual-gates:helper -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json --gate manual-success-flow
npm run manual:success-flow:helper -- --payloads --browser --curl --evidence
```

`metrika-goals`:

```bash
npm run release:manual-gates:helper -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json --gate metrika-goals
npm run metrika:goals:helper -- --taxonomy --source-check --owner-checklist --browser --evidence
```

`bitrix-admin`:

```bash
npm run release:public-precheck:prod
npm run bitrix:admin:gate-helper -- --checklist --browser --evidence
npm run release:manual-gates:helper -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json --gate bitrix-admin
```

Optional staff-order helper, только если owner явно включает `/price/` staff-order в ручной success-flow:

```bash
npm run staff:sale:gate-helper -- --payload --browser --curl --evidence
```

## Safe Evidence Rules

В репозиторий, release issue and sign-off JSON нельзя переносить:

| Forbidden | Почему |
|---|---|
| Name, phone, email, contact fields | PII |
| Full message text, payload, raw request, raw response, raw body | May contain PII, internal errors or upstream details |
| Cookie, session, sessid, token, secret, password | Credentials/session material |
| Raw URL query, referer, user-agent, access log lines | May identify users or sessions |
| Unmasked screenshots from CRM, Bitrix admin or Metrika | May expose PII or account data |

Разрешённые поля:

| Field Type | Examples |
|---|---|
| Time and owner | `checked_at`, `checked_by`, owner role |
| Safe marker | `qa_marker=manual-smoke-safeletters` |
| Safe technical IDs | masked lead/request ID, masked `group_id`, internal ticket ID |
| Controlled status | `success=true`, HTTP status/code, `params_safe=true` |
| Flow metadata | URL path, `form_id`, goal name, role, short result |

## Evidence Skeletons

Минимальные поля для `manual-success-flow`:

```json
{
  "environment": "staging or controlled-production",
  "checked_at": "YYYY-MM-DDTHH:mm:ss+03:00",
  "checked_by": "owner name or role",
  "qa_marker": "manual-smoke-safeletters",
  "flows": [
    {
      "flow": "default-lead-form",
      "url": "https://tacticum.ru/price/",
      "form_id": "price-cta",
      "result": "safe short result",
      "upstream_request_id": "safe-or-masked-id"
    },
    {
      "flow": "modal-form",
      "url": "https://tacticum.ru/",
      "form_id": "contact-modal",
      "result": "safe short result",
      "upstream_request_id": "safe-or-masked-id"
    },
    {
      "flow": "ai-chat",
      "url": "https://tacticum.ru/calculator/",
      "result": "controlled response without raw stack or PII",
      "masked_group_id": "masked-or-not_applicable"
    },
    {
      "flow": "prefill",
      "url": "https://tacticum.ru/offer/",
      "result": "expected prefill or controlled empty state",
      "masked_group_id": "masked-or-not_applicable"
    }
  ]
}
```

Минимальные поля для `metrika-goals`:

```json
{
  "counter_id": "103471113",
  "checked_at": "YYYY-MM-DDTHH:mm:ss+03:00",
  "checked_by": "owner name or role",
  "observed_after": "YYYY-MM-DDTHH:mm:ss+03:00",
  "goals": [
    "tacticum_form_submit",
    "tacticum_form_success",
    "tacticum_product_form_submit",
    "tacticum_product_form_success",
    "tacticum_chat_send",
    "tacticum_chat_success",
    "tacticum_prefill_submit",
    "tacticum_prefill_success"
  ],
  "goal_observations": [
    {
      "goal": "tacticum_form_success",
      "status": "observed",
      "params_safe": true
    }
  ],
  "checked_markers": {
    "manual_success_flow": "manual-smoke-safeletters"
  },
  "pii_check": "goal params contain no name, phone, email, message, raw payload or raw URL query",
  "external_evidence": "internal-ticket-or-masked-screenshot-reference"
}
```

Минимальные поля для `bitrix-admin`:

```json
{
  "checked_at": "YYYY-MM-DDTHH:mm:ss+03:00",
  "checked_by": "owner name or role",
  "admin_url": "https://tacticum.ru/bitrix/admin/",
  "role": "admin or content-admin",
  "public_toolbar_url": "https://tacticum.ru/price/",
  "result": "admin panel and public toolbar open without 500/white screen",
  "cache_note": "deploy/cache refresh did not break admin panel or public toolbar",
  "external_evidence": "internal-ticket-or-masked-screenshot-reference"
}
```

## Closing Procedure

1. Owner запускает нужный helper и выполняет controlled проверку в staging или production test window.
2. Owner переносит в sign-off только safe evidence из раздела выше.
3. Для закрытого gate в `docs/workflow/release-signoff-2026-06-01-product-first.draft.json` заменить `status` на `passed` и заменить pending `reason`/`due` на фактический owner-run `evidence`.
4. Запустить строгий check:

```bash
npm run release:signoff:check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
```

Если хотя бы один ручной gate остаётся pending, использовать draft mode:

```bash
npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
npm run release:signoff:summary -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
```

## Current Blocker

Strict release closure currently blocked only by owner evidence for `manual-success-flow`, `metrika-goals` and `bitrix-admin`.

Do not mark these gates `passed` until the corresponding owner-run checks exist.
