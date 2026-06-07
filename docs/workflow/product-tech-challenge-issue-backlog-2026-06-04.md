# Product Tech Challenge Issue Backlog — 2026-06-04

Дата: 04.06.2026
Статус: issue backlog draft / owner approvals pending

Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Execution board: `docs/workflow/product-tech-challenge-execution-board-2026-06-04.md`
Owner status tracker: `docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json`
Machine-readable backlog: `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.json`

## Purpose

Этот документ переводит WP-01 - WP-09 в backlog-ready issue set. Его можно использовать для ручного создания задач в GitHub/Jira/YouTrack/Linear, но он не является owner approval and не закрывает gaps.

Детальные machine-readable поля задач лежат в `product-tech-challenge-issue-backlog-2026-06-04.json`. Этот Markdown фиксирует правила импорта, compact backlog table and copy format.

## Start Policy

| Policy | Meaning |
|---|---|
| `target-evidence-only` | Можно собирать target evidence and safe command output; runtime/product changes не входят |
| `owner-review-required` | Можно проводить owner review and refine docs; implementation waits for approval |
| `blocked-external` | Нельзя стартовать implementation; нужен внешний source/Legal/Sales/SEO/Security/DevOps evidence |
| `monitor-only` | Ничего не внедрять; track triggers and reopen by lane |

## Backlog Index

| Issue | WP | Status | Start policy | Sprint | Priority | Owners | Gap IDs |
|---|---|---|---|---:|---|---|---|
| PTC-WP-01 | WP-01 | pending-target-evidence | target-evidence-only | 17 | P0 | Backend + QA + Content + DevOps | `CFG-001`, `CFG-002`, `CFG-003`, `ARCH-001`, `ARCH-003`, `STACK-004` |
| PTC-WP-02 | WP-02 | pending-owner-review | owner-review-required | 17 | P1 | Content + Backend + DevOps + Architect | `ARCH-004`, `ARCH-011`, `STACK-007` |
| PTC-WP-03 | WP-03 | blocked-external | blocked-external | 17/18/20 | P0 | PM + Sales + Legal + SEO + Security | `UX-006`, `CONTENT-001`, `CONTENT-002`, `CONTENT-003`, `ARCH-009`, `UI-005` |
| PTC-WP-04 | WP-04 | pending-owner-review | owner-review-required | 18 | P1 | PM + Sales + SEO + Content + Architect | `CONTENT-004`, `CONTENT-005`, `UX-004`, `UX-005`, `ARCH-010` |
| PTC-WP-05 | WP-05 | pending-owner-review | owner-review-required | 19 | P1 | PM + UX + Sales + Backend + Security + Analytics + QA | `CFG-004`, `UX-001`, `UX-002`, `UX-003`, `UX-007`, `UX-008`, `UX-009`, `UX-010`, `ARCH-005`, `ARCH-006`, `CMP-003` |
| PTC-WP-06 | WP-06 | pending-owner-review | owner-review-required | 20 | P1 | Designer + Frontend + QA + Legal + PM | `UI-001`, `UI-002`, `UI-003`, `UI-004`, `UI-005`, `UI-006`, `UI-007`, `UI-008`, `UI-009`, `UI-010`, `STACK-003`, `CMP-008` |
| PTC-WP-07 | WP-07 | pending-owner-review | owner-review-required | 21 | P1 | Architect + Frontend + QA + Security + Backend | `CFG-005`, `ARCH-002`, `CMP-001`, `CMP-002`, `CMP-004`, `CMP-005`, `CMP-006`, `CMP-007`, `STACK-002`, `STACK-005`, `SEC-001` |
| PTC-WP-08 | WP-08 | blocked-external | blocked-external | 22 | P1 | Security + Backend + DevOps + QA + PM + Legal | `CFG-006`, `ARCH-007`, `ARCH-008`, `ARCH-012`, `SEC-002`, `SEC-003`, `REL-001`, `REL-002` |
| PTC-WP-09 | WP-09 | accepted-monitor | monitor-only | 23 | P2 | PM + Architect + Security + Frontend + QA | `STACK-001`, `STACK-006`, `SEC-001`, `ARCH-007`, `REL-002` |

## Evidence Refreshes

| Work Package | Date | Evidence | Status |
|---|---|---|---|
| `PTC-WP-01` | 07.06.2026 | `product:content:safety:check`, `product:source:http:prod`, `release:public-precheck:prod`; details in `docs/workflow/product-content-target-evidence-refresh-2026-06-07.md` | Local and HTTP production evidence refreshed; target Bitrix/PHP strict JSON, cache-clear dry-run JSON and owner-approved target negative fixture path remain pending. |

## Import Rules

1. Create one tracker issue per `PTC-WP-*`.
2. Copy title, owners, gap IDs, priority, start policy, acceptance criteria, verification and do-not-start rules from JSON.
3. Keep issue status aligned with `product-tech-challenge-owner-status-tracker-2026-06-04.json`.
4. Do not mark an issue approved/ready-for-implementation until the relevant owner response and evidence are recorded.
5. Do not store PII, raw form payloads, raw chat prompts, cookies, sessions, tokens, IP addresses, full user agents, private contracts or unredacted customer data in issues.
6. If implementation changes runtime behavior, open a scoped implementation issue after approval instead of expanding the approval/evidence issue.

## Copy Format

```text
Issue ID:
Work package:
Title:
Workflow lane:
Priority:
Start policy:
Owners:
Gap IDs:

Objective:

Definition of Ready:

Acceptance criteria:

Verification:

Evidence required:

Blockers:

Do not start:

Source documents:
```

## Sprint-To-Issue Sequence

| Sprint | Issues | Gate before implementation |
|---|---|---|
| Sprint 17 | PTC-WP-01, PTC-WP-02, PTC-WP-03 | Target Bitrix/PHP evidence, product content ownership and claim-source approval |
| Sprint 18 | PTC-WP-04, PTC-WP-03 | PM/Sales/SEO/Legal route, taxonomy, packaging and evidence approval |
| Sprint 19 | PTC-WP-05 | PM/Sales/Security/Analytics approval for CJM, CTA, CRM fallback or structured fields |
| Sprint 20 | PTC-WP-06, PTC-WP-03 | Designer/Frontend/QA/Legal state approval and claim/status constraints |
| Sprint 21 | PTC-WP-07 | Frontend/QA/Architect/Security baseline smoke and contract-preserving split approval |
| Sprint 22 | PTC-WP-08 | Security/DevOps/QA release evidence, CSP/access model and legacy full-window evidence |
| Sprint 23 | PTC-WP-09 | Monitoring cadence and reopen trigger acceptance |

## Verification

```bash
npm run product:challenge:check
npm run product:challenge:issue-backlog:check
npm run product:challenge:owner-status:check
npm run product:challenge:board:check
npm run product:challenge:approval:check
```

## Closure Rule

This backlog can move from `issue backlog draft` to `tracker-imported` only when:

- every `PTC-WP-*` issue exists in the external tracker or internal issue list;
- tracker issue IDs are recorded in this document or in the JSON manifest;
- owner statuses remain aligned with `product-tech-challenge-owner-status-tracker-2026-06-04.json`;
- blocked issues have owner, reason and evidence path;
- `npm run product:challenge:issue-backlog:check` passes.
