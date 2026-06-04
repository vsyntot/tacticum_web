# Product Tech Challenge Owner Review Runbook — 2026-06-04

Дата: 04.06.2026
Статус: owner review / issue import runbook

Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Execution board: `docs/workflow/product-tech-challenge-execution-board-2026-06-04.md`
Owner approval request: `docs/workflow/product-tech-challenge-owner-approval-request-2026-06-04.md`
Evidence intake: `docs/workflow/product-tech-challenge-evidence-intake-2026-06-04.md`
Owner status tracker: `docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json`
Issue backlog: `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.md`

## Purpose

Этот runbook задаёт операционный порядок: как провести owner review по Sprint 17-23, импортировать `PTC-WP-01` - `PTC-WP-09` в трекер задач, собрать safe evidence and обновить документы без потери gaps.

Runbook не разрешает implementation сам по себе. Implementation starts only after owner decision, evidence and lane gates.

## Preflight

Перед owner review или импортом задач запустить:

```bash
npm run product:challenge:check
npm run product:gaps:check
npm run gaps:known
```

Если менялись product content schema, release evidence, SEO or runtime-adjacent docs, дополнительно:

```bash
npm run product:content:safety:check
npm run seo:check
npm run release:signoff:self-test
```

## Issue Import

1. Open `product-tech-challenge-issue-backlog-2026-06-04.json`.
2. Create one tracker issue per `PTC-WP-01` - `PTC-WP-09`.
3. Copy title, objective, workflow lane, priority, owners, gap IDs, DoR, acceptance criteria, verification, evidence, blockers and do-not-start rules.
4. Keep the tracker issue status aligned with `product-tech-challenge-owner-status-tracker-2026-06-04.json`.
5. Record external tracker IDs only after import is confirmed.
6. Run `npm run product:challenge:check` after any tracker ID/status/evidence update.

## Review Order

| Step | Work packages | Review focus | Required output |
|---|---|---|---|
| 1 | WP-01 | Target Bitrix/PHP evidence path | Safe target evidence plan |
| 2 | WP-02 | Product content ownership and automation | Owner/environment matrix |
| 3 | WP-03 | Claims, proof and public wording | Claim-source matrix and Legal/Sales decision |
| 4 | WP-04 | Taxonomy, packaging and SEO routes | PM/Sales/SEO route and metadata decision |
| 5 | WP-05 | CJM, CTA, CRM and analytics | Fallback approval or structured-field scope |
| 6 | WP-06 | TO BE design system | Token/state/proof UI approval |
| 7 | WP-07 | Frontend/component hardening | Contract-preserving split and smoke plan |
| 8 | WP-08 | Security, release, CSP and legacy | Endpoint/CSP/access/legacy evidence decision |
| 9 | WP-09 | Accepted-risk monitoring | Review cadence and reopen trigger acceptance |

## Allowed Status Flow

| Current | Allowed next statuses | Notes |
|---|---|---|
| `pending-target-evidence` | `approved`, `approved-v1-safe`, `evidence-blocked`, `deferred`, `rejected` | Only after target evidence path is recorded |
| `pending-owner-review` | `approved`, `approved-v1-safe`, `rewrite-required`, `evidence-blocked`, `deferred`, `rejected` | Requires named owner response |
| `blocked-external` | `approved`, `approved-v1-safe`, `evidence-blocked`, `deferred`, `rejected` | Requires external evidence or explicit owner decision |
| `accepted-monitor` | `accepted-monitor`, `deferred` | Implementation requires trigger and new lane issue |

Do not use `approved` or `approved-v1-safe` without owner, evidence/source ID, implementation impact and required follow-up.

## Evidence Rules

Allowed repository evidence:

- command name, date, pass/fail and safe product codes;
- aggregate traffic/SEO/log counts;
- internal source IDs;
- masked/redacted summaries;
- owner decision notes without private customer or contact data.

Forbidden repository evidence:

- names, phone numbers, emails, raw lead text, raw chat prompts or raw CRM records;
- cookies, sessions, CSRF tokens, secrets, IP addresses or full user agents;
- confidential contract terms, private docs, customer screenshots or unredacted reports;
- raw production logs or raw upstream responses.

## Update Order After Owner Response

1. Update `product-tech-challenge-evidence-intake-2026-06-04.md` with safe evidence/status.
2. Update `product-tech-challenge-owner-approval-request-2026-06-04.md` if owner status or decision text changes.
3. Update `product-tech-challenge-owner-status-tracker-2026-06-04.json` with machine-readable status, blockers and evidence refs.
4. Keep `product-tech-challenge-issue-backlog-2026-06-04.json` aligned when status, owner, blocker or evidence requirements change.
5. Update affected sprint document review sections.
6. Update `product-tech-challenge-gap-register-2026-06-04.md` only when actual evidence changes a gap state.
7. Run `npm run product:challenge:check`.

## Implementation Handoff

After approval, create a separate implementation issue when runtime, public URL, form payload, CRM/upstream contract, CSP, CSS/JS or Bitrix content behavior changes.

Implementation issue must include:

- source `PTC-WP-*` issue ID;
- affected gap IDs;
- workflow lane;
- affected files/areas;
- ADR/design/security/SEO gates;
- acceptance criteria and verification commands;
- no-PII evidence plan;
- rollback or no-runtime-change statement.

## No-Go Rules

| Work | No-go until |
|---|---|
| Public metrics, logos, certifications or deployment claims | WP-03 Legal/Sales/PM evidence approval |
| `/agents/` redirect/canonical behavior change | WP-04 SEO/PM approval |
| Structured CRM/upstream fields | WP-05 Security / Integration scope |
| Broad TO BE visual implementation | WP-06 state/token approval |
| `/price/` mobile rewrite | WP-06 state approval + WP-07 smoke plan |
| Forms/chat payload change during modular split | WP-05/WP-07 Security / Integration approval |
| Private proof/document downloads | WP-08 access model approval |
| CSP enforce | WP-08 CSP checklist and rollback |
| Legacy sale alias removal/410/redirect | WP-08 full-window inventory after `2026-06-30` and final mode decision |
| SPA/framework/bundler migration | WP-09 trigger + ADR |

## Review Commands

```bash
npm run product:challenge:check
npm run product:content:safety:check
npm run config:check
npm run bitrix:check
npm run product:gaps:check
npm run gaps:known
npm run release:signoff:self-test
```

Target-only commands, when target Bitrix/PHP access exists:

```bash
npm run product:content:check:strict:json
npm run product:content:target-evidence:check -- --file=/path/to/product-content-strict.json --allow-source=bitrix
npm run product:content:cache-clear:dry-run
npm run config:runtime:check
```

## Closure Criteria

Owner review phase is complete only when:

- all `PTC-WP-01` - `PTC-WP-09` tracker issues exist or have a documented reason for non-import;
- every WP has owner status;
- blocked decisions have owner, reason, evidence path and follow-up;
- no forbidden evidence is committed;
- approved runtime work is split into separate implementation issues;
- `npm run product:challenge:check`, `npm run product:gaps:check` and `npm run gaps:known` pass.
