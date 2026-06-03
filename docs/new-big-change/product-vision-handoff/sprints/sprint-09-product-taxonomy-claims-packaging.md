# Sprint 09 - Product Taxonomy, Claims And Packaging Decision Closure

Suggested duration: 1 week decision sprint

Status: ready-for-owner-review / external approvals pending

## Sprint Goal

Закрыть P0/P1 продуктовые решения, без которых TO BE дизайн и реализация могут закрепить неверную или рискованную публичную модель: taxonomy, product boundaries, proof/claims, packaging and `/agents/` vs `/aiagents/`.

Этот спринт является post-challenge уточнением Sprint 00. Он не заменяет source backlog, а переводит decision IDs `D-01` - `D-04` в review-ready work packages.

## Workflow Lane

Full Feature Lane with Legal, Security and SEO gates.

## Source Decisions And Gaps

| Decision | Existing gaps |
|---|---|
| `D-01` Product taxonomy and public one-liners | `PB-001`, `PB-002` |
| `D-02` Agents / Forum / `/aiagents/` boundary | `PB-003`, `PB-008`, `SEO-TOBE-002` |
| `D-03` Proof and claims public/private split | `PB-005`, `PB-006`, `UI-005`, `SEO-TOBE-003` |
| `D-04` Packaging language | `PB-007` |

## Inputs

- `../18-phase-1-product-decision-review-pack.md`
- `../20-phase-4-seo-content-decision-pack.md`
- `../24-post-challenge-gap-analysis.md`
- `../28-post-challenge-decision-backlog.md`
- `../07-risk-and-claims-register.md`
- `../14-gap-backlog-and-decision-register.md`
- `../16-gap-closure-action-register.json`
- `sprint-09-review-workbook.md`
- `sprint-09-decision-records.md`
- `sprint-09-approval-request.md`
- `sprint-09-evidence-intake.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S09-001 | Approve final product names, Russian descriptors and one-liners for Platform, Agents, Dev and Forum | PM + Sales | P1 | planned |
| S09-002 | Approve top Platform buying triggers and public framing | PM + Architect | P1 | planned |
| S09-003 | Approve Agents vs Forum boundary copy and cross-linking rules | PM + Product + SEO | P1 | planned |
| S09-004 | Decide `/agents/` vs `/aiagents/`: keep differentiated, canonical, or redirect plan | SEO + PM | P1 | planned |
| S09-005 | Build evidence matrix: product -> claim -> source -> owner -> public status -> wording | PM + Sales + Legal | P0 | planned |
| S09-006 | Classify regulatory/procurement/deployment claims: public, private/NDA, rewrite, blocked | Legal + Security + PM | P0 | planned |
| S09-007 | Approve packaging language for assessment, pilot, SaaS, on-prem, hybrid, PAK, implementation and support | PM + Sales + Architect + Legal | P1 | planned |
| S09-008 | Update decision records after approvals or blockers | PM | P1 | planned |
| S09-009 | Prepare owner approval request, evidence intake and review workbook | PM support / Codex | P1 | done locally |

## Out Of Scope

- Visual redesign.
- Product page implementation.
- CRM/upstream payload changes.
- New proof/status UI implementation.
- SEO redirects or canonical changes before SEO evidence and rollback plan.

## Deliverables

- Approved taxonomy and one-liners.
- Approved Platform buying triggers.
- Agents / Forum / `/aiagents/` decision record.
- Proof and claims evidence matrix.
- Public/private/NDA/blocked claim split.
- Packaging language matrix.
- Updated review notes in `18` / `20` or a linked decision record.
- Status update proposal for `14-gap-backlog-and-decision-register.md` only where owner evidence exists.
- Completed `sprint-09-decision-records.md` after owner review.
- Completed review notes from `sprint-09-review-workbook.md`.
- Owner approval request in `sprint-09-approval-request.md`.
- Evidence intake template in `sprint-09-evidence-intake.md`.

## Gates

| Gate | Required | Notes |
|---|---|---|
| Legal | Yes | Required for proof, logos, metrics, registry, PAK, SLA, regulatory claims |
| Security | Yes | Required for procurement, deployment, data, audit and compliance wording |
| SEO | Yes | Required for `/agents/` vs `/aiagents/`, product metadata and duplicate risk |
| Design | No | Design consumes decisions after sprint close |
| ADR | No | Unless packaging decision changes architecture/content model |
| QA early | No | No code changes in this sprint |

## Acceptance Criteria

1. Product names and one-liners are approved or explicitly blocked for rewrite.
2. Platform public triggers are approved by PM and Architect.
3. Agents vs Forum boundary is approved by PM/Product and SEO.
4. `/agents/` vs `/aiagents/` has one chosen direction or a dated evidence blocker.
5. Every P0 claim has a status: `available`, `private-nda`, `rewrite`, `needs-evidence`, `not-supported` or `blocked`.
6. Proof/status UI may only use statuses approved in this sprint.
7. Packaging language does not imply unsupported SaaS/on-prem/PAK/SLA/certification promises.
8. No gap is marked closed without owner approval and evidence.

## Local Preparation Status

| Artifact | Status | Notes |
|---|---|---|
| Sprint 09 scope | done locally | Current document |
| Review workbook | done locally | `sprint-09-review-workbook.md` |
| Draft decision records | done locally | `sprint-09-decision-records.md`; statuses remain draft |
| Owner approval request | done locally | `sprint-09-approval-request.md` |
| Evidence intake template | done locally | `sprint-09-evidence-intake.md` |
| Owner approvals | external pending | PM/Sales/Legal/Security/SEO required |
| Gap status updates | blocked until approvals | No canonical gap status changes without evidence |

## Verification

- `npm run product:gaps:check`
- Manual review of public wording against `07-risk-and-claims-register.md`
- SEO owner review for `/agents/` / `/aiagents/`
- Completed Sprint 09 decision records with status and owner blockers.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Proof evidence unavailable | PM + Sales | Keep proof UI as pilot artifacts or private/NDA request path |
| Legal blocks desired claims | PM + Legal | Rewrite or remove from public scope |
| SEO cannot decide `/aiagents/` quickly | SEO + PM | Keep both differentiated and mark canonical decision blocked |
| Packaging becomes too commercial too early | PM + Sales | Use safe assessment/pilot wording until commercial model is approved |

## Sprint Review

### Done

- Local owner-review packet prepared: scope, review workbook, draft decision records, approval request and evidence intake.

### Not Done

- Owner approvals for `D-01` - `D-04`.
- Evidence for proof/claims, `/aiagents/` SEO decision and packaging language.

### Follow-Up

- Run Sprint 09 owner review.
- Update `sprint-09-decision-records.md` with owner statuses.
- Update canonical gap/register docs only where evidence supports status change.
