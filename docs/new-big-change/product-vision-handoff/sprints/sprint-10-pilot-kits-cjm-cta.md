# Sprint 10 - Pilot Kits, Role-Based CJM And CTA Taxonomy

Suggested duration: 1-2 weeks

Status: ready-for-owner-review / Sprint 09 approvals pending

## Sprint Goal

Превратить текущие product use cases and safe CTA context в decision-grade CJM: pilot kits, role paths, returning-lead journey and CTA taxonomy. Спринт должен сделать сайт полезным для выбора продукта и следующего шага, а не просто для чтения продуктовых страниц.

## Workflow Lane

Full Feature Lane with Design and Sales/PM review.

## Source Decisions And Gaps

| Decision | Existing gaps |
|---|---|
| `D-05` Product pilot kits | `CJM-003`, `PB-004`, `PB-002` |
| `D-06` Role-based CTA and returning journey | `CJM-004`, `CJM-005`, `CJM-006` |

Related gaps: `CJM-001`, `CJM-002`, `PB-003`, `ARCH-003`, `ARCH-004`.

## Inputs

- Sprint 09 approved taxonomy/claims decisions.
- `../11-use-cases-and-cjm-target.md`
- `../17-local-gap-decision-briefs.md`
- `../25-post-challenge-use-cases-and-cjm.md`
- `../19-phase-3-architecture-integration-decision-pack.md`
- `../28-post-challenge-decision-backlog.md`
- Current product data files in `local/php_interface/include/product_data/*.php`
- `sprint-10-review-workbook.md`
- `sprint-10-pilot-kit-records.md`
- `sprint-10-cjm-cta-records.md`
- `sprint-10-approval-request.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S10-001 | Define pilot kit standard for all public product use cases | PM + UX + Content | P1 | planned |
| S10-002 | Approve 3-5 Platform pilot kits | PM + Architect + Sales | P1 | planned |
| S10-003 | Approve 3-5 Agents pilot kits | PM + Sales + Content | P1 | planned |
| S10-004 | Approve 3-5 Dev pilot kits | PM + Tech Lead + Content | P1 | planned |
| S10-005 | Approve 3-5 Forum pilot kits | PM + CX/Sales + Content | P1 | planned |
| S10-006 | Define buyer role paths: Economic, Technical, Security/Procurement, Functional, Returning Lead | UX + PM | P1 | planned |
| S10-007 | Define CTA taxonomy: pilot, architecture-session, scenario-selection, documentation-request, estimate | PM + UX + Sales | P2 | planned |
| S10-008 | Decide whether current `lead_scenario` select is enough for v1 Sales routing | PM + Sales + Backend + QA | P1 | planned |
| S10-009 | Define returning-lead path without adding unapproved downloads/endpoints | Sales + UX + PM | P2 | planned |
| S10-010 | Prepare review workbook, pilot kit records, CJM/CTA records and approval request | PM support / Codex | P1 | done locally |

## Out Of Scope

- New CRM/upstream structured fields.
- New gated download endpoint.
- New analytics parameters beyond approved no-PII taxonomy.
- Visual component implementation.
- Changing form required fields.

## Deliverables

- Pilot kit table for each product.
- Role-based CJM map.
- CTA taxonomy and context mapping.
- Returning-lead journey.
- Sales routing note for current `lead_scenario` and `task` fallback.
- Update proposal for product copy and `local/php_interface/include/product_data/*.php`, but no code change unless approved separately.
- Owner-review workbook in `sprint-10-review-workbook.md`.
- Draft pilot kit records in `sprint-10-pilot-kit-records.md`.
- Draft role/CJM/CTA records in `sprint-10-cjm-cta-records.md`.
- Owner approval request in `sprint-10-approval-request.md`.

## Gates

| Gate | Required | Notes |
|---|---|---|
| Design | Yes | Designer needs CJM and pilot kit anatomy |
| Sales | Yes | Pilot kits must match discovery conversations |
| Security / Integration | Conditional | Required only if fields/payload/endpoints change |
| SEO | Conditional | Required if pilot kits create new URL or metadata scope |
| QA early | Conditional | Required if form behavior changes |

## Acceptance Criteria

1. Each product has approved pilot kits with trigger, owner, readiness, input, output, proof status, limitation and CTA.
2. Role paths explain how CIO/CTO/Security/Functional/Returning users move through the site.
3. CTA taxonomy maps to current form contracts without adding unapproved required fields.
4. Sales confirms submitted product/scenario context is useful enough for v1 or opens `D-11` structured-fields scope.
5. Returning-lead journey is defined without promising unapproved downloads or private proof.
6. Any implementation follow-up is mapped to existing gaps and sprint scope.

## Local Preparation Status

| Artifact | Status | Notes |
|---|---|---|
| Sprint 10 scope | done locally | Current document |
| Review workbook | done locally | `sprint-10-review-workbook.md` |
| Pilot kit records | done locally | `sprint-10-pilot-kit-records.md` |
| CJM/CTA records | done locally | `sprint-10-cjm-cta-records.md` |
| Owner approval request | done locally | `sprint-10-approval-request.md` |
| Owner approvals | external pending | PM/UX/Sales/Content required |
| Sprint 09 dependency | external pending | taxonomy/claims/packaging decisions may affect wording |
| Structured fields decision | pending Sprint 12 if needed | only if Sales rejects current fallback |

## Verification

- `npm run product:gaps:check`
- Manual review against `25-post-challenge-use-cases-and-cjm.md`
- Sales/PM approval notes

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Use cases remain generic | PM + Content | Require pilot input/output and owner for every use case |
| CTA taxonomy implies new backend fields | Backend + PM | Keep current fallback until Sprint 12 decision |
| Returning-lead path requires gated docs | PM + Security | Scope as request CTA first, not download flow |
| Role paths create too much UI complexity | UX | Keep as route cards/anchors before adding new pages |

## Sprint Review

### Done

- Local owner-review packet prepared: review workbook, pilot kit records, CJM/CTA records and approval request.

### Not Done

- Owner approvals for `D-05` and `D-06`.
- Sprint 09 taxonomy/claims dependencies.
- Sales routing decision on current fallback vs structured fields.

### Follow-Up

- Run Sprint 10 owner review.
- Update pilot kit and CJM/CTA records with owner statuses.
- Move structured-field requests to Sprint 12 `D-11` if needed.
- Update product data/copy only after approvals.
