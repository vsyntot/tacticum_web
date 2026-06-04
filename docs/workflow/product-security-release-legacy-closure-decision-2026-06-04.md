# Product Security, Release And Legacy Closure Decision

Дата: 04.06.2026
Статус: draft / approval pending
Sprint: `docs/workflow/sprints/2026-06-04-sprint-22-security-release-legacy-closure.md`
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`

## Назначение

Этот документ фиксирует Sprint 22 baseline для security/release runway product-first слоя: endpoint sensitivity, rate classes, private proof/document access, CSP enforce path, release evidence discipline and legacy sale alias finalization.

Решение не меняет runtime headers, endpoint behavior, aliases, config or access model. Оно задаёт условия, при которых такие изменения можно делать.

## Covered Gaps

| Gap | Sprint item | Decision baseline | Remaining gate |
|---|---|---|---|
| `CFG-006` | S22-001 | Empty `rest.allowed_ips` / `trusted_proxies` remains acceptable for public flows; future sensitive classes need explicit config ownership | Security + DevOps approval |
| `ARCH-008` | S22-002 | Endpoint sensitivity classes define rate/auth/origin requirements by flow type | Security + Backend approval |
| `SEC-002` | S22-003 | Private proof/document access is blocked until an access model is approved | Security + PM + Legal gate |
| `ARCH-007` | S22-004 | CSP stays report-only until report triage, cleanup, smoke and rollback are complete | Security + Frontend + QA gate |
| `SEC-003` | S22-005 | CSP cleanup backlog is sequenced before enforce | Security + Frontend approval |
| `ARCH-012` | S22-006 | Release evidence remains aggregate/safe; new sensitive gates must extend checker before use | QA + Security approval |
| `REL-002` | S22-007 | Future product work must reference challenge gap IDs and release evidence | PM + QA approval |
| `REL-001` | S22-008 | Legacy aliases remain blocked until full-window logs + CRM/upstream report and final mode decision | External evidence after `2026-06-30` |

## Current Baseline

| Area | Current state | Current risk |
|---|---|---|
| Public POST endpoints | Shared REST helpers own origin/rate/CSRF/config/masking; lead/chat/prefill/staff-order have no raw PII evidence model | Future private flows could outgrow public-form controls |
| Config | Example config has empty `rest.allowed_ips` and `trusted_proxies`; runtime config evidence prints safe summaries | Empty values are fine for public flows but insufficient as a default for private/integration flows |
| CSP | `header.php` supports `security.csp_mode=report-only|enforce`; current accepted mode is report-only | Enforce could break Bitrix toolbar, Metrika, Yandex widgets or inline runtime |
| Release sign-off | Sign-off checker, helpers and manual evidence templates reject pending/PII-like evidence; Sprint 22 security-sensitive gate names now have checker/self-test support | Future security-sensitive flows still need trigger-specific owner evidence before being marked `passed` |
| Legacy sale aliases | `tacticum_offer.php` and `tacticum_sale.php` keep response shape and deprecation/sunset lifecycle | Final removal/410/redirect cannot happen before full external inventory |

## Endpoint Sensitivity Matrix

The matrix defines policy classes. It does not change current config values.

| Class | Examples | Origin/CSRF | Rate limit | IP/proxy/auth | Logging/evidence |
|---|---|---|---|---|---|
| `PUBLIC_READ` | `/local/api/*.php`, public product/content reads | Origin validation where helper applies; no CSRF for GET | Public GET class | No IP allowlist or auth by default | No raw content diagnostics beyond safe summaries; JSON endpoints stay noindex |
| `PUBLIC_CONFIG_HEALTH` | `/local/rest/health_config.php` | Same-origin/origin validation, GET only | Health/config class | No secrets; no auth by default while output is safe | Scope names, boolean/default/explicit state only |
| `PUBLIC_LEAD_POST` | `/local/rest/tacticum_form.php`, modal/product CTA forms | Origin, rate, JSON parse, CSRF/session model | Public lead class | No IP allowlist by default; no auth | PII masked; no raw request/response in docs/logs |
| `PUBLIC_CHAT_POST` | `/local/rest/tacticum_chat.php`, chat handoff | Origin, rate, explicit CSRF | Chat class, stricter than static GET | No IP allowlist by default; no auth | No raw prompts in analytics/evidence; safe `group_id` masking |
| `PUBLIC_STAFF_POST` | `/local/rest/tacticum_sale_staff.php` | Origin, rate, CSRF, HTTPS upstream | Staff-order class | No IP allowlist by default; no auth | Only aggregate worker counts, preset, budget presence and masked upstream IDs |
| `SCOPED_PREFILL_POST` | `/local/rest/tacticum_prefill.php` | POST JSON + explicit CSRF + scoped `group_id` | Prefill class | No GET fallback; no public raw data | Masked `group_id`, no raw prefill payload in evidence |
| `PUBLIC_RESOLVER_POST` | `/local/rest/resolve_telegram_link.php` | Origin, rate, CSRF; no initial-load background call | Resolver class | No auth by default | No raw external resolver response in docs |
| `LEGACY_ALIAS_POST` | `/local/rest/tacticum_offer.php`, `/local/rest/tacticum_sale.php` | Current alias controls and preserved response shape | Legacy class with sunset monitoring | No new consumers; no behavior expansion | Aggregate inventory only; deprecation/sunset headers stay until final mode |
| `PRIVATE_PROOF_DOC` | Future proof packet, procurement docs, gated downloads | Not allowed as a plain public POST/GET | Sensitive class | Requires approved signed-token/auth/session model and expiry | No raw documents, contacts, cookies, sessions or payloads in repo evidence |
| `INTERNAL_ADMIN_OR_INTEGRATION` | Future admin-like endpoint, webhook, system integration | Endpoint-specific | Integration class | Requires auth or signed token; IP allowlist/trusted proxy only with DevOps-owned config | Aggregate IDs only; secrets never in release evidence |

## IP Allowlist And Trusted Proxy Decision

Current empty `rest.allowed_ips` and `trusted_proxies` remain accepted for public lead-generation endpoints.

Do not use IP allowlist as the primary control for public visitor flows. It is appropriate only for:

- internal/admin-like endpoints;
- B2B integration endpoints with a stable caller network;
- webhook/callback endpoints where signed verification and IP allowlist can complement each other;
- emergency restriction during an incident, with rollback and owner approval.

Before using `trusted_proxies`, DevOps must document:

- which proxy/CDN/load balancer owns `X-Forwarded-For`;
- which environments use it;
- how spoofed client IPs are rejected;
- how runtime config evidence shows explicit vs default state without exposing IPs.

## Rate-Limit Classes

Rate policy should be defined by class, not copied ad hoc per endpoint.

| Class | Applies to | Policy direction |
|---|---|---|
| `rl-public-read` | GET content APIs | Protect Bitrix content reads without blocking normal crawling/visits |
| `rl-health` | `health_config.php` | Low-cost but not unlimited; same-origin/admin checks can run repeatedly |
| `rl-lead` | public lead forms | Human form submissions, burst-protected, no lead creation before guards |
| `rl-chat` | AI chat | Stricter than lead forms because upstream AI cost is higher |
| `rl-staff` | staff-order endpoint | Similar to lead but monitored as richer business payload |
| `rl-prefill` | prefill by `group_id` | Strict enough to prevent enumeration |
| `rl-resolver` | Telegram resolver | Strict and user-action-triggered only |
| `rl-sensitive` | future proof/doc access | Strict, tied to auth/signed token, no public anonymous access |
| `rl-legacy` | legacy sale aliases | Existing behavior plus sunset monitoring; no new capacity promises |

Numeric thresholds remain a Security + Backend decision and should live in config/helper policy, not page code.

## Private Proof / Document Access Model

V1 approved model: no private documents are downloaded directly from public product pages.

Allowed v1 path:

1. Public page explains that proof/security/procurement materials are available on request.
2. User submits an existing public lead/contact flow with safe context.
3. Sales/Security/PM shares approved materials through an owner-controlled channel after qualification/NDA if needed.
4. Release evidence stores only safe status, owner and masked reference, not document content or contact data.

Site-hosted private proof/document access requires a new Security / Integration scope before implementation:

- access method: authenticated session, expiring signed link or explicit owner-approved token;
- expiry and revocation rules;
- noindex headers and sitemap exclusion;
- authorization failure UX;
- no raw documents/links/tokens in analytics or docs;
- rate limit and abuse handling;
- legal/claims approval for wording and evidence status;
- post-deploy smoke for allowed, denied, expired and malformed access.

## CSP Report-Only / Enforce Decision

CSP remains report-only.

Enforce is allowed only after all items below are complete:

| Requirement | Evidence |
|---|---|
| Report-only baseline | Production/staging report sample or owner-confirmed no unexpected violations |
| Inline inventory | List of required inline scripts/styles and Bitrix/runtime sources |
| Vendor inventory | Yandex Metrika, Yandex map widget, Bitrix toolbar/admin and any remaining vendor origins classified |
| Cleanup plan | Remove unused origins and either remove, nonce/hash or justify remaining `unsafe-inline` |
| Staging enforce smoke | Public pages, forms, chat, `/price/`, Metrika, map and Bitrix toolbar checked |
| Rollback | Config switch back to `security.csp_mode=report-only` documented |
| Release sign-off | Dedicated `csp-enforce` gate and checker support added before use |

Do not set `security.csp_mode=enforce` as a config-only fast fix.

## CSP Cleanup Backlog

Recommended order:

1. Collect report-only baseline and classify violations.
2. Inventory Bitrix-generated inline code and admin toolbar requirements.
3. Confirm whether current `script-src 'unsafe-inline'` is still needed for public pages.
4. Confirm whether current `style-src 'unsafe-inline'` is still needed for Bitrix/runtime styles.
5. Narrow Yandex origins if report data allows it.
6. Add `report-uri` or `report-to` only after endpoint ownership and data retention are approved.
7. Run staging enforce smoke.
8. Update ADR-005, sign-off gates and rollback runbook before production enforce.

## Release Evidence Decision

Existing release sign-off discipline remains valid:

- no raw payloads, cookies, sessions, tokens, secrets, contact data or full upstream responses;
- manual helpers generate safe evidence skeletons and do not send production requests by themselves;
- `release:signoff:self-test` must stay green when checker rules change;
- `gaps:known` remains the draft visibility command for external tails.

Future security-sensitive flows need explicit release gates before use. At minimum:

| Future gate | Trigger | Checker support |
|---|---|---|
| `csp-enforce` | `security.csp_mode=enforce` or CSP policy change intended to block | Supported by `release-signoff-check.mjs`; requires baseline, inventory, staging smoke, triage and rollback evidence |
| `sensitive-endpoint-access` | private proof/doc/procurement endpoint or gated download | Supported by `release-signoff-check.mjs`; requires allowed/denied/expired access evidence without tokens/PII |
| `endpoint-risk-class` | new endpoint class or changed auth/rate/origin policy | Supported by `release-signoff-check.mjs`; requires endpoint matrix class and policy evidence |
| `legacy-final-mode` | alias removal, 410, redirect or support extension | Supported by `release-signoff-check.mjs`; requires final-mode decision and inventory evidence checks |

Use these gate names in release JSON as `not_applicable` until the trigger applies. Do not mark them `passed` without the required safe evidence shape and owner approval.

## Future Product Sign-Off Discipline

Every future product implementation issue must include:

- affected challenge gap IDs;
- workflow lane;
- affected files/areas;
- approval gates;
- release sign-off gates;
- smoke commands;
- no-PII evidence rule;
- rollback path if endpoints, CSP, content source, forms, chat, `/price/` or product renderer are touched.

This is the operational closure path for `REL-002`; it does not close future implementation evidence by itself.

## Legacy Sale Alias Finalization

Legacy aliases remain unchanged until the external inventory window closes.

Current aliases:

- `/local/rest/tacticum_offer.php`;
- `/local/rest/tacticum_sale.php`.

Successor:

- `/local/rest/tacticum_form.php`.

Decision matrix:

| Evidence after `2026-06-30` | Allowed final mode | Required action |
|---|---|---|
| Full-window access logs show zero hits and CRM/upstream shows zero legacy leads | Remove alias or return `410 Gone` | Architect + Backend decision, checker update, release notes, post-deploy smoke |
| Known consumers found and migration owner exists | Keep aliases until migration target date | Owner migration plan to `/local/rest/tacticum_form.php`, follow-up evidence by `2026-08-31` |
| Unknown non-zero traffic remains | Extend support as accepted risk | PM + Architect accepted-risk record, new sunset date, monitoring |
| SEO/crawler-only noise appears | Prefer `410 Gone` or noindex/nofollow response after owner review | SEO + Backend review, no redirect loops |
| Contract needs preserved for partner | Continue as explicit compatibility endpoint | Security/Integration review, owner, sunset extension and docs update |

No raw access logs, IPs, referrers, user agents, cookies, sessions, request bodies or lead contacts may be stored in repo evidence.

## Implementation Gates

| Change | Gate |
|---|---|
| New private proof/document flow | Security / Integration + Legal/PM + access model ADR if shared |
| New endpoint risk class or auth model | Security / Integration + config/runtime evidence |
| CSP enforce | Security + Frontend + QA + ADR-005 update + rollback |
| Release sign-off gate additions | Checker update + self-test + docs update |
| Legacy alias final mode | Full-window inventory + CRM/upstream report + checker/docs/contract update |
| IP allowlist/trusted proxy use | DevOps-owned environment matrix + runtime safe evidence |

## Verification For This Docs-Only Baseline

Expected local checks:

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
npm run release:signoff:self-test
npm run sale:sunset:check
npm run gaps:known
git diff --check
```

Production checks and external inventory are required only when runtime release/security work is in scope:

```bash
npm run release:public-precheck:prod
npm run release:manual-gates:helper
npm run legacy:sale:inventory:logs
```

## Remaining Decisions

| Decision | Owner | Status |
|---|---|---|
| Approve endpoint sensitivity matrix | Security + Backend + DevOps | pending |
| Approve rate-limit class naming and thresholds | Security + Backend | pending |
| Approve private proof/document access model | Security + PM + Legal + Backend | blocked until future flow is scoped |
| Approve CSP enforce checklist and cleanup backlog | Security + Frontend + QA | pending |
| Approve use of future release gates when a trigger applies | QA + Security + Backend | checker support added; trigger-specific owner evidence pending |
| Complete legacy full-window inventory after `2026-06-30` | Backend + DevOps + PM | blocked on date/external evidence |
