# 23. Accepted Risk Monitoring Pack

Дата: 02.06.2026

Статус: monitoring package для accepted gaps. Документ не переводит risks в `closed`; он фиксирует условия пересмотра, owners and guardrails для решений, которые команда явно принимает на текущем этапе.

Workflow follow-up 04.06.2026: `docs/workflow/product-accepted-risk-monitoring-decision-2026-06-04.md` extends accepted-risk monitoring for the 2026-06-04 challenge register: `STACK-001`, `STACK-006`, `SEC-001`, `ARCH-007` and `REL-002`.

## Назначение

В `14-gap-backlog-and-decision-register.md` есть gaps со статусом `accepted`: они не блокируют текущую реализацию, но должны оставаться видимыми. Этот pack задает monitoring rules для:

- `ARCH-006` - CSP enforce not current target;
- `SEO-TOBE-004` - industry/scenario pages remain noindex.

## ARCH-006 - CSP Enforce Monitoring

Current accepted decision:

- keep Content Security Policy in report-only mode;
- do not switch to enforce until vendor/report baseline is reviewed;
- do not break Yandex map widget, Yandex.Metrika, Bitrix admin toolbar, forms, chat, visual smoke or production assets by enforcing too early.

Current guardrails:

| Area | Guardrail |
|---|---|
| Header policy | `security.csp_mode=enforce` only after explicit Security / Integration scope |
| Vendor scripts | Yandex.Metrika and map widget must be allowed deliberately |
| Template assets | new JS/CSS must use Bitrix Asset/page properties, not inline scripts |
| Post-deploy smoke | browser console and visual smoke must run after any CSP change |
| Evidence | report-only findings should be triaged before enforce |

Revisit trigger:

| Trigger | Required action |
|---|---|
| Security asks for enforce rollout | Create Security / Integration task and CSP ADR/update |
| New vendor asset added | Update report-only baseline and smoke affected pages |
| Metrika/map/admin toolbar reports violations | Triage before switching modes |
| Production report-only baseline is clean | Consider enforce plan with rollback |

Close or change status only when:

- Security approves enforce or explicit long-term accepted risk;
- CSP report-only baseline is documented;
- post-deploy smoke covers affected vendor/admin/form surfaces;
- rollback is defined.

## SEO-TOBE-004 - Industry / Scenario Noindex Monitoring

Current accepted decision:

- keep industry/scenario pages noindex or deferred;
- do not create indexable industry/scenario URLs until product proof, content depth and claim governance are ready.

Reason:

- product proof/case content is not yet approved enough for a reliable indexable expansion;
- weak thin pages would create SEO debt and claim risk;
- product pages and preserved money pages are the current safer SEO surface.

Current guardrails:

| Area | Guardrail |
|---|---|
| New public URL | Requires SEO gate, sitemap/canonical plan and content owner |
| Industry/scenario page | Stays noindex until proof/content readiness |
| Claims | Must follow `07-risk-and-claims-register.md` and Phase 1 proof matrix |
| Product proof | Must align with `20-phase-4-seo-content-decision-pack.md` |
| Sitemap | Indexable URLs must be added deliberately and pass `npm run seo:check` |

Revisit trigger:

| Trigger | Required action |
|---|---|
| 3-5 approved proof/case assets exist for a cluster | SEO + Content may propose indexable page |
| Sales validates a high-value industry cluster | PM + SEO review page intent and proof availability |
| Legal approves claim wording for a regulated cluster | Content can draft safe public page |
| Existing noindex page starts receiving useful leads | SEO reviews whether it deserves indexable status |

Close or change status only when:

- SEO + PM approve indexation strategy;
- Content/Sales/Legal approve proof and claims;
- sitemap/canonical/noindex implementation passes source and rendered checks;
- post-deploy rendered evidence is captured if public URLs change.

## Monitoring Checklist

| Gap | Owner | Review cadence | Keep accepted while |
|---|---|---|---|
| `ARCH-006` | Security | after vendor/script/CSP changes or quarterly | report-only is intentional and no enforce baseline exists |
| `SEO-TOBE-004` | SEO + PM | after proof/content readiness changes or quarterly | industry/scenario content is not strong enough for indexation |

## Status Update Rules

- Do not mark these gaps `closed` just because this monitoring pack exists.
- Keep `closureMode=accepted-monitoring` in `16-gap-closure-action-register.json`.
- If the accepted decision changes, update `14-gap-backlog-and-decision-register.md`, `16-gap-closure-action-register.json`, relevant ADR/docs and run `npm run product:gaps:check`.
