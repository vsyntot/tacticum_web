# 21. Phase 5 Release Evidence Closure Pack

Дата: 02.06.2026

Статус: draft closure package для Phase 5 release / external evidence gates. Документ не закрывает внешние gates; он фиксирует, что именно должны сделать QA, DevOps, SEO, PM/Marketing, Backend, Admin and Sales/PM owners, чтобы gaps можно было закрыть без PII and without optimistic assumptions.

## Назначение

Phase 5 из `15-gap-closure-master-plan.md` должен перевести оставшийся blocked tail из "известно, что нужно проверить" в управляемую модель closure: команды, due, evidence, strict checks and status update path.

Covered gaps:

- `REL-001` - product-first automated deploy smoke;
- `REL-002` - rendered product SEO evidence;
- `REL-003` - manual success-flow;
- `REL-004` - Metrika goals;
- `REL-005` - Bitrix admin smoke;
- `REL-006` - legacy sale aliases external inventory;
- `ARCH-007` - external release gates;
- `ARCH-008` - staff/upstream success-flow.

## Current Release Evidence Baseline

| Area | Current state | Source |
|---|---|---|
| Local code gaps | `npm run gaps:known` reports `Code-level open/in-progress gaps: 0` | `tools/known-gaps-check.mjs` |
| Product-first draft | Pending post-deploy/cache, rendered SEO, CSS/JS, manual, Metrika and Bitrix admin gates | `docs/workflow/release-signoff-2026-06-01-product-first.draft.json` |
| Post-deploy closure draft | Some production checks passed on 25.05.2026, but manual/upstream/Metrika/Bitrix gates remain pending | `docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` |
| Manual gates runbook | Defines safe evidence for manual success-flow, Metrika, Bitrix admin and staff upstream | `docs/workflow/manual-release-gates-runbook.md` |
| Evidence template | Provides PII-safe JSON structure for manual gates | `docs/workflow/release-signoff-manual-evidence.template.json` |
| Legacy aliases | Repo scan done; production logs and CRM aggregate inventory still pending through `30.06.2026` | `docs/workflow/legacy-sale-alias-consumer-inventory.md` |
| Staff upstream | Rich workers endpoint switch is not approved; current adapter remains `chat_agent_sale` | `docs/workflow/rich-workers-upstream-readiness-2026-05-24.md` |

## Evidence Rules

Hard rules:

- do not commit names, phones, emails, company names, free-form messages, raw request payloads, raw upstream responses, cookies, session IDs, CSRF tokens, secrets, IPs or full user agents;
- use safe evidence only: command names, manifest paths, internal ticket/report IDs, checked_at, owner, status, masked IDs and aggregate counts;
- manual gate evidence must follow `release-signoff-manual-evidence.template.json`;
- pending gates must keep `status=pending`, `owner`, `due`, `reason`, runbook and evidence template links;
- strict closure requires no `pending` gates in the target release sign-off JSON.

Recommended evidence locations:

| Evidence type | Store in repo? | Safe location |
|---|---|---|
| Static command output summary | Yes | release sign-off JSON note or issue comment |
| Visual/browser smoke manifest path | Yes if path contains no PII | release sign-off JSON evidence |
| Metrika screenshots/export | No, unless sanitized and policy-approved | internal ticket/report ID |
| CRM/upstream lead evidence | No raw payload; only safe IDs | internal ticket/report ID + safe lead/request ID |
| Bitrix admin screenshots | Usually no; avoid session/admin data | internal ticket/report ID |
| Access log inventory | Aggregates only | `legacy-sale-alias-consumer-inventory.md` after masking |

## REL-001 - Product-First Automated Deploy Smoke

Goal: prove that product-first public pages and preserved commercial pages work after deploy/cache refresh.

Command path:

```bash
npm run release:product-first:prod-check
```

This aggregates:

- production SEO check;
- rendered SEO smoke;
- warning-aware browser console/action smoke;
- focused `/price/` smoke;
- product-first draft sign-off validation;
- product-first `gaps:known` summary.

Closure evidence:

| Required item | Evidence |
|---|---|
| Deploy/cache refresh completed | DevOps deployment/run ID or internal ticket |
| `release:product-first:prod-check` passed | command output summary and manifest paths |
| Product URLs covered | `/platform/`, `/agents/`, `/dev/`, `/forum/` in rendered smoke |
| Preserved money pages covered | `/price/`, `/offer/`, `/calculator/`, `/aiagents/` where relevant |
| No new code gaps | `TACTICUM_RELEASE_SIGNOFF=docs/workflow/release-signoff-2026-06-01-product-first.draft.json npm run gaps:known` summary |

Close only when:

- product-first draft automated gates are updated from `pending` to `passed` or explicitly `not_applicable`;
- evidence paths are safe and accessible to release owners.

## REL-002 - Rendered Product SEO Evidence

Goal: prove deployed HTML, not only source files, has correct product SEO head and schema.

Minimum rendered evidence:

| URL | Required evidence |
|---|---|
| `/platform/` | one title, description, canonical, H1, OpenGraph, `SoftwareApplication`, `FAQPage`, required `data-product-block` inventory |
| `/agents/` | same as above, plus Agents/Forum and `/aiagents/` boundary links visible in rendered page where applicable |
| `/dev/` | same as above, no risky workforce or unsupported metrics in head/schema |
| `/forum/` | same as above, no unsupported automation/FCR claims in head/schema |

Command path:

```bash
npm run seo:smoke
```

or product-first aggregate:

```bash
npm run release:product-first:prod-check
```

Close only when:

- rendered manifest has no `seoErrors`;
- product schema summary exists for product URLs;
- `FAQPage` schema exists only when rendered FAQ exists;
- no `offers`, `price`, `review`, `aggregateRating` or unsupported proof fields are present;
- release sign-off checker accepts the manifest.

## REL-003 - Manual Success-Flow

Goal: prove the user-facing lead flows can complete in staging or controlled production.

Minimum flows:

| Flow | URL / endpoint | Required result |
|---|---|---|
| Default lead form | Product or commercial CTA using `/local/rest/tacticum_form.php` | UI success state and upstream/CRM accepted lead |
| Modal form | Footer/contact modal | Open, validate, submit, success, close path works |
| AI chat | Chat surface on homepage/calculator/price where affected | Controlled response without raw stack or PII |
| Prefill | `/local/rest/tacticum_prefill.php` after AI response | Expected prefill or controlled empty state |
| Staff order | `/local/rest/tacticum_sale_staff.php` from `/price/` | workers/team summary reaches backend/upstream or documented blocked state |

Close only when:

- `manual-success-flow` gate in release sign-off has `status=passed`;
- evidence follows the manual runbook;
- no PII or raw payload is committed;
- upstream 502 is resolved or explicitly accepted by PM/QA for a scoped release.

Current blocker:

- previous controlled production smoke returned upstream `502` for default form, modal, AI chat and staff-order; this cannot be closed locally from the repository.

## REL-004 - Metrika Goals

Goal: prove Yandex.Metrika receives affected product/form/chat events without PII.

Minimum goals/events to confirm:

| Scenario | Events |
|---|---|
| Product view | `tacticum_product_view` |
| Product CTA | `tacticum_product_cta_click` |
| Product form | `tacticum_product_form_submit`, `tacticum_product_form_success`, `tacticum_product_form_error` |
| Generic forms | `tacticum_form_submit`, `tacticum_form_success`, expected `tacticum_form_error` |
| Staff order | form events with `form_id=price-specialist` |
| AI chat/prefill | affected chat and prefill goals if changed or part of release smoke |

Allowed params:

- `page_path`;
- `product`;
- `page_role`;
- controlled `scenario`;
- `form_id`;
- `endpoint`;
- `surface`;
- `status`;
- `code`;
- boolean/count-like technical flags.

Forbidden params:

- name, phone, email, company, message text, raw URL query, raw payload, document names or free-form request data.

Close only when:

- PM/Marketing + QA attach Metrika evidence outside repo or sanitized internal ID;
- `metrika-goals` gate is `passed`;
- evidence explicitly says params contain no PII.

## REL-005 - Bitrix Admin Smoke

Goal: prove deploy/template/header/assets changes do not break authenticated Bitrix admin or public toolbar.

Minimum checks:

| Check | Required result |
|---|---|
| `/bitrix/admin/` authenticated login | admin panel opens without 500/white screen |
| Public toolbar | public page with admin toolbar renders and remains usable |
| Cache/admin surface | cache/admin operations affected by deploy do not break the template |
| Product pages | product pages render under admin toolbar without template asset errors |

Close only when:

- QA/Admin records checked_at, role, admin URL, public toolbar URL and result;
- no credentials, cookies or session screenshots are committed;
- `bitrix-admin` gate is `passed`.

## REL-006 - Legacy Sale Aliases External Inventory

Goal: finish the lifecycle plan for `/local/rest/tacticum_offer.php` and `/local/rest/tacticum_sale.php`.

Required timeline:

| Date | Required milestone |
|---|---|
| `2026-06-30` | access log and CRM/upstream aggregate inventory complete |
| `2026-08-31` | migration plan complete for any identified consumers |
| `2026-09-30` | final alias mode chosen and implemented or support extension accepted |

Required evidence:

| Source | Evidence |
|---|---|
| Web access logs | aggregate hits by endpoint/week/masked source/last seen |
| CRM/upstream reports | aggregate lead counts by legacy source endpoint |
| Partner/integration register | owner and migration contact for known external consumers |
| Repo/source scan | no first-party callers outside docs/tools |

Close only when:

- `legacy-sale-alias-consumer-inventory.md` has full-window aggregate data;
- every non-zero consumer has owner and migration target;
- final alias mode is implemented or accepted-risk extension is approved.

## ARCH-007 - External Release Gates

`ARCH-007` closes only when the strict release closure model passes.

Required command path:

```bash
npm run release:signoff:check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
TACTICUM_RELEASE_SIGNOFF=docs/workflow/release-signoff-2026-06-01-product-first.draft.json npm run gaps:known:strict
```

Close only when:

- no gate is `pending`;
- strict release sign-off accepts evidence;
- `gaps:known:strict` passes;
- no placeholder/working-tree metadata remains in strict sign-off;
- no external gate is closed with repository-only assumptions.

## ARCH-008 - Staff / Upstream Success-Flow

Goal: prove staff-order and upstream/CRM path is operational or explicitly accepted as blocked for a scoped release.

Current recommended v1:

- keep `ai.endpoint_paths.staff_sale = /tacticum/v1/chat_agent/sale`;
- do not switch to rich workers upstream without OpenAPI/contract approval;
- preserve `/price/` rich frontend payload and server-side `task` fallback.

Close only when:

- staff-order controlled test reaches upstream/CRM successfully; or
- PM/Architect/Backend accept scoped release with staff-order blocked and customer-facing risk documented;
- if upstream contract changes, ADR-006 and `lead-form-contract.md` are updated before deploy;
- `staff-sale-upstream` gate is `passed` or explicitly not applicable for the release scope.

## Phase 5 Closure Board

| Gap | Current closure mode | Owner | Close only when |
|---|---|---|---|
| `REL-001` | external evidence | DevOps + QA | post-deploy/cache product-first automated smoke passed |
| `REL-002` | external evidence | SEO + QA | rendered product SEO manifest accepted |
| `REL-003` | external evidence | QA + Backend/Frontend | manual success-flow passed without PII |
| `REL-004` | external evidence | PM/Marketing + QA | Metrika goals verified without PII params |
| `REL-005` | external evidence | QA/Admin | authenticated Bitrix admin/public toolbar smoke passed |
| `REL-006` | external evidence | Backend + DevOps + PM | access logs/CRM aggregate inventory complete |
| `ARCH-007` | external evidence | QA + DevOps | strict release sign-off and `gaps:known:strict` pass |
| `ARCH-008` | external evidence | Backend + DevOps | upstream/staff success-flow recovered or scoped risk accepted |

## Status Update Path

When evidence is collected:

1. Update the relevant release sign-off JSON gate from `pending` to `passed` or `not_applicable`.
2. Remove `reason` from passed gates and attach safe evidence.
3. Run draft check while any gates remain pending:

```bash
npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
npm run release:signoff:summary -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
```

4. Run strict check only when all gates are complete:

```bash
npm run release:signoff:check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
TACTICUM_RELEASE_SIGNOFF=docs/workflow/release-signoff-2026-06-01-product-first.draft.json npm run gaps:known:strict
```

5. Update `14-gap-backlog-and-decision-register.md` and `16-gap-closure-action-register.json` statuses only after strict evidence exists.

## Recommended Review Session

1. DevOps confirms deploy/cache refresh process and automated smoke owner.
2. SEO + QA confirm rendered SEO manifest requirements.
3. QA + Backend/Frontend schedule controlled success-flow.
4. PM/Marketing + QA confirm Metrika access and evidence owner.
5. QA/Admin confirms Bitrix admin smoke access.
6. Backend + DevOps + PM confirm legacy alias inventory timeline.
7. Backend + DevOps confirm staff/upstream recovery path.
8. PM decides whether any remaining blocker is acceptable as scoped release risk or keeps release issue open.
