# Product Tech Challenge Evidence Intake — 2026-06-04

Дата: 04.06.2026
Статус: safe evidence intake template / owner evidence pending
Approval request: `docs/workflow/product-tech-challenge-owner-approval-request-2026-06-04.md`
Execution board: `docs/workflow/product-tech-challenge-execution-board-2026-06-04.md`
Owner status tracker: `docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json`
Issue backlog: `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.md`
Owner review runbook: `docs/workflow/product-tech-challenge-owner-review-runbook-2026-06-04.md`

## Purpose

Этот документ задаёт безопасный формат evidence intake для WP-01 - WP-09. Его цель — собрать owner decisions, target evidence and external reports without storing PII, raw payloads, cookies, sessions, secrets, confidential contracts or unredacted customer data in the repository.

## Evidence Rules

1. Public claims need owner, safe source ID, evidence date/currentness and approved wording.
2. Private/NDA evidence may be referenced by internal source ID only.
3. Do not store names, phone numbers, emails, raw lead text, raw chat prompts, raw upstream responses, cookies, sessions, CSRF tokens, IP addresses or full user agents.
4. Do not store confidential contract terms, customer screenshots, private documents or unredacted CRM records.
5. Production evidence should be aggregate, masked, redacted or referenced by internal ticket/report ID.
6. If evidence is missing, status remains `needs-evidence`, `evidence-blocked` or `blocked-external`.
7. Evidence date should be explicit, for example `2026-06-04` or `2026-06-30T18:00:00+03:00`.

## Status Values

| Status | Meaning | Public/runtime use |
|---|---|---|
| `approved` | Owner approved exact decision/wording/evidence | allowed within approved scope |
| `approved-v1-safe` | Owner approved safe v1 only | allowed as safe v1 |
| `pilot-artifact` | Evidence is about pilot process, not outcome | allowed only as "what we validate" |
| `private-nda` | Evidence may support private discussion | public request-only wording |
| `rewrite-required` | Direction is acceptable, wording unsafe | rewrite before use |
| `needs-evidence` | Source missing or stale | not public proof |
| `evidence-blocked` | Owner/source unavailable | no implementation depending on it |
| `not-supported` | Claim/decision should not be used | hidden or rejected |
| `accepted-monitor` | Risk accepted with triggers | monitor only |

## Evidence Intake Matrix

| WP | Evidence needed | Safe source format | Owner | Current status | Repository-safe output |
|---|---|---|---|---|---|
| WP-01 | Target strict product content check, negative fixture result, cache-clear dry-run | command name, date, product codes, pass/fail, no raw content | Backend + QA + DevOps + Content | pending | safe summary / JSON evidence without raw text |
| WP-02 | Product content ownership and environment matrix | owner table, environment names, no secrets | Content + Backend + DevOps + Architect | pending | ownership matrix and runbook update |
| WP-03 | Claim source matrix, proof/case mapping, public/private/blocked status | safe claim ID, source ID, owner, date, approved wording | PM + Sales + Legal + SEO + Security | pending | claim matrix with no private docs |
| WP-04 | Taxonomy, packaging and SEO evidence | aggregate SEO/lead evidence, keyword intent summary, owner decision | PM + Sales + SEO + Content | pending | route/canonical/metadata decision |
| WP-05 | CJM/CTA/CRM/analytics approval | owner notes, CRM source aggregate, goal map, no contact data | PM + UX + Sales + Backend + Security + Analytics + QA | pending | approved fallback or structured-field scope |
| WP-06 | TO BE design-system approval | Figma/file reference or token source ID, state matrix approval | Designer + Frontend + QA + Legal + PM | pending | implementation-ready design decision |
| WP-07 | Component/frontend hardening approval | smoke manifest path, selector contract, owner decision | Architect + Frontend + QA + Security + Backend | pending | approved split/smoke plan |
| WP-08 | Endpoint/CSP/release/legacy evidence | endpoint class decision, CSP report summary, log aggregate, CRM aggregate | Security + Backend + DevOps + QA + PM + Legal | pending | no-PII security/release evidence |
| WP-09 | Accepted-risk monitoring approval | review cadence, owner names, trigger decision | PM + Architect + Security + Frontend + QA | pending | monitoring status notes |

## Claim / Proof Evidence Table

| Evidence ID | Product | Claim family | Desired claim or proof | Safe source ID | Owner | Evidence date | Status | Approved public wording | Private/NDA wording | Follow-up |
|---|---|---|---|---|---|---|---|---|---|---|
| EV-CLAIM-001 | all | metrics | public performance/result metric | TBD | PM + Sales + Legal | TBD | needs-evidence | Do not publish numeric claim | TBD | provide source/methodology |
| EV-CLAIM-002 | all | logos/testimonials | customer logo or testimonial | TBD | Sales + Legal | TBD | needs-evidence | Hide until written permission | TBD | collect permission |
| EV-CLAIM-003 | all | regulatory/security | registry/certification/security status | TBD | Legal + Security | TBD | needs-evidence | Do not publish confirmed status | TBD | provide legal/security evidence |
| EV-CLAIM-004 | Platform / Agents / Dev / Forum | cases/proof | product-specific case mapping | TBD | Content + SEO + Sales | TBD | needs-evidence | Use generic safe proof readiness | TBD | tag cases/offers/FAQ/services |
| EV-CLAIM-005 | all | packaging | SaaS/on-prem/hybrid/PAK/SLA public terms | TBD | PM + Sales + Legal + Architect | TBD | rewrite-required | Discussed after architecture/commercial review | TBD | approve wording |

## SEO / Route Evidence Table

| Evidence ID | Question | Required source | Owner | Status | Safe output |
|---|---|---|---|---|---|
| EV-SEO-001 | `/agents/` vs `/aiagents/` route model | aggregate traffic, ranking, lead contribution, content intent | SEO + PM | pending | canonical/redirect/no-change decision |
| EV-SEO-002 | Product metadata | keyword/intent review and approved copy | SEO + Content + PM | pending | title/description/H1 sheet |
| EV-SEO-003 | Product evidence clusters | product-tagged cases/offers/FAQ/services | SEO + Content + Sales | pending | content tagging backlog or approval |

## CRM / Analytics Evidence Table

| Evidence ID | Question | Required source | Owner | Status | Safe output |
|---|---|---|---|---|---|
| EV-CRM-001 | Current text fallback usefulness | CRM/upstream owner review, no contact data | Sales + Backend + QA | pending | approve fallback or open structured-field scope |
| EV-CRM-002 | Structured field need | field list, sensitivity, mapping, upstream support | PM + Sales + Backend + Security | pending | Security / Integration issue if needed |
| EV-AN-001 | Product funnel goals | Metrika/analytics owner review, no raw params | Analytics + PM + QA | pending | no-PII goal map approval |

## Design / Frontend Evidence Table

| Evidence ID | Question | Required source | Owner | Status | Safe output |
|---|---|---|---|---|---|
| EV-DES-001 | Token source and mapping | Figma/token source or approved JSON/Tailwind mapping | Designer + Frontend | pending | implementation-ready token decision |
| EV-DES-002 | Proof/status states | state labels, color use, Legal mapping | Designer + Legal + PM | pending | state matrix approval |
| EV-DES-003 | Form/chat/price states | component state matrix and QA smoke scope | Designer + Frontend + QA | pending | implementation batch scope |
| EV-FE-001 | `/price/` split baseline | browser smoke manifest and selector/payload contract | Frontend + QA | pending | split approval |
| EV-FE-002 | Forms/chat split baseline | browser smoke/contract review | Frontend + Backend + QA + Security | pending | split approval |

## Security / Release Evidence Table

| Evidence ID | Question | Required source | Owner | Status | Safe output |
|---|---|---|---|---|---|
| EV-SEC-001 | Endpoint sensitivity classes | Security/Backend review | Security + Backend + DevOps | pending | endpoint class matrix approval |
| EV-SEC-002 | Private proof/document model | Security/Legal/PM review | Security + PM + Legal + Backend | blocked-external | access model decision |
| EV-CSP-001 | CSP enforce readiness | report-only summary, inline/vendor inventory, staging smoke | Security + Frontend + QA + DevOps | pending | enforce/no-enforce decision |
| EV-REL-001 | Legacy alias full-window access logs | aggregate-only log report after `2026-06-30` | Backend + DevOps | blocked-external | endpoint/day/status counts only |
| EV-REL-002 | Legacy alias CRM/upstream source report | aggregate source counts, no contacts | PM + Backend | blocked-external | safe CRM/source summary |
| EV-REL-003 | Future sign-off gate extension | checker self-test and gate evidence model | QA + Security + Backend | local-tooling-ready; trigger evidence pending | `csp-enforce`, `sensitive-endpoint-access`, `endpoint-risk-class`, `legacy-final-mode` supported by checker/self-test/docs |

## Target Evidence Commands

| Evidence | Command | Notes |
|---|---|---|
| Product schema local safety | `npm run product:content:safety:check` | no PHP/Bitrix required; runs self-test, negative fixture and seed/fallback schema check |
| Product schema negative fixture | `npm run product:content:schema:negative-test` | no PHP/Bitrix required; invalid fixture must fail as expected |
| Product strict target check | `npm run product:content:check:strict:json` | target Bitrix/PHP only; no raw content |
| Product strict target evidence validation | `npm run product:content:target-evidence:check -- --file=/path/to/product-content-strict.json --allow-source=bitrix` | local or target; validates saved safe JSON evidence |
| Product cache dry-run | `npm run product:content:cache-clear:dry-run` | target Bitrix/PHP only |
| Runtime config evidence | `npm run config:runtime:check` | target Bitrix/PHP; no secrets |
| Full challenge package coverage | `npm run product:challenge:check` | local aggregate |
| Execution board coverage | `npm run product:challenge:board:check` | local |
| Approval/evidence coverage | `npm run product:challenge:approval:check` | local |
| Owner status tracker coverage | `npm run product:challenge:owner-status:check` | local |
| Issue backlog coverage | `npm run product:challenge:issue-backlog:check` | local |
| Release checker regression | `npm run release:signoff:self-test` | local |
| Legacy alias inventory | `npm run legacy:sale:inventory:logs` | production/internal logs; aggregate only |

## Update Path

After evidence review:

1. Update this intake with safe statuses only.
2. Update `product-tech-challenge-owner-approval-request-2026-06-04.md` with owner statuses.
3. Update `product-tech-challenge-owner-status-tracker-2026-06-04.json` with machine-readable WP status, blockers and evidence refs.
4. Keep `product-tech-challenge-issue-backlog-2026-06-04.json` aligned when status, owner, blocker or evidence requirements change.
5. Update source register status only where actual evidence changes the gap state.
6. Update sprint review sections for affected Sprint 17-23 docs.
7. Run:

```bash
npm run product:challenge:check
npm run product:challenge:approval:check
npm run product:challenge:owner-status:check
npm run product:challenge:issue-backlog:check
npm run product:challenge:board:check
npm run product:gaps:check
```
