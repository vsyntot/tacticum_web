# Sprint 10 Approval Request

Дата: 02.06.2026

Статус: ready-for-owner-review. Документ нужен, чтобы PM/UX/Sales/Content/Backend/QA быстро увидели, какие решения требуются для закрытия Sprint 10 local review.

## Request Summary

Sprint 10 asks owners to approve or block two post-challenge decisions:

| Decision | Required owner response | Primary owner | Local status |
|---|---|---|---|
| `D-05` Product pilot kits | Approve/rewrite pilot kits for Platform, Agents, Dev and Forum | PM + Content + Sales | drafts prepared |
| `D-06` Role-based CTA and returning journey | Approve role paths, CTA taxonomy, returning-lead path and Sales routing assumption | PM + UX + Sales | drafts prepared |

## Prepared Materials

| Material | Use |
|---|---|
| `sprint-10-pilot-kits-cjm-cta.md` | Sprint scope, gates and acceptance criteria |
| `sprint-10-review-workbook.md` | Review agenda and worksheets |
| `sprint-10-pilot-kit-records.md` | Draft pilot kits for four products |
| `sprint-10-cjm-cta-records.md` | Draft role paths, CTA taxonomy, returning-lead journey and Sales routing decision |
| `../25-post-challenge-use-cases-and-cjm.md` | Source target model |
| `../17-local-gap-decision-briefs.md` | CTA/returning-lead and Dev workflow baseline |

## Required Owner Responses

### PM / Product Owner

| Required response | Decision |
|---|---|
| Approve pilot kit standard | `D-05` |
| Approve which pilot kits are public v1 | `D-05` |
| Approve role-based journeys | `D-06` |
| Approve CTA taxonomy and primary/secondary action hierarchy | `D-06` |

### UX / Designer

| Required response | Decision |
|---|---|
| Confirm pilot kit anatomy can become component/design input | `D-05` |
| Confirm role paths can be represented without bloating pages | `D-06` |
| Confirm returning-lead path is copy/CTA-only for v1 | `D-06` |

### Sales

| Required response | Decision |
|---|---|
| Confirm pilot kit triggers match real buyer conversations | `D-05` |
| Confirm pilot output helps discovery and follow-up | `D-05` |
| Confirm current `lead_scenario` + `task` fallback is usable for v1 routing | `D-06` |
| If fallback is not enough, request structured-fields scope for Sprint 12 | `D-06` |

### Content / Editor

| Required response | Decision |
|---|---|
| Rewrite pilot kits for public tone where needed | `D-05` |
| Ensure limitations are clear and not overly defensive | `D-05` |
| Confirm CTA labels are concise and consistent | `D-06` |

### Backend + QA

| Required response | Decision |
|---|---|
| Confirm Sprint 10 does not require form/payload changes | `D-06` |
| If changes are requested, scope Security / Integration work in Sprint 12 | `D-06` |
| Confirm current smoke implications | `D-06` |

## Recommended V1 Decisions To Review

### D-05

Use 3 pilot kits per product for v1:

| Product | Pilot kits |
|---|---|
| Platform | AI portfolio consolidation, Controlled LLM Gateway, Corporate RAG governance |
| Agents | HR onboarding assistant, Legal review assistant, Corporate knowledge assistant |
| Dev | AI-assisted workflow governance, Design-system compliance, Brownfield refactor control |
| Forum | Contact-center flow automation, Scenario + LLM hybrid bot, Funnel analytics for conversations |

Default proof status:

```text
pilot-artifact
```

No pilot kit should publish metric, logo, regulatory or production-readiness claim until Sprint 09 evidence approves it.

### D-06

Use v1 role paths:

- Economic buyer;
- Technical buyer;
- Security/procurement;
- Functional owner;
- Returning lead.

Use v1 CTA families:

- `pilot`;
- `architecture-session`;
- `scenario-selection`;
- `documentation-request`;
- `estimate`;
- `continue-discussion`.

Recommended v1 Sales routing:

```text
Accept current lead_product + lead_scenario + lead_next_step fallback for first release.
Do not add structured CRM/upstream fields until Sprint 12 D-11 approval.
```

## Approval Output Format

Owners should return:

```text
Decision ID:
Owner:
Status:
Approved pilot kits / CTA families:
Rewrite required:
Rejected items:
Sales routing decision:
Implementation impact:
Required follow-up:
```

Allowed statuses:

- `approved`;
- `approved-v1-safe`;
- `rewrite-required`;
- `sprint-09-blocked`;
- `integration-blocked`;
- `deferred`;
- `rejected`.

## External / Cross-Sprint Blockers

| Blocker | Why Sprint 10 cannot close it locally |
|---|---|
| Sprint 09 taxonomy not approved | Pilot kit wording depends on final product names and boundaries |
| Sprint 09 proof/claims not approved | Proof status cannot become stronger than `pilot-artifact` |
| Sales rejects current fallback | Requires Sprint 12 CRM/upstream structured-fields decision |
| Documentation request needs downloads | Requires Security/Legal/Product scope beyond Sprint 10 |
| CTA changes require new fields | Requires Design + QA + Security / Integration lane |

## Closure Rule

Sprint 10 can move from `ready-for-owner-review` to `completed` only when:

- pilot kits have owner statuses;
- role paths and CTA taxonomy have owner statuses;
- Sales routing decision is recorded;
- any structured-field need is moved to Sprint 12;
- no new form or analytics behavior is assumed silently;
- `npm run product:gaps:check` remains green.
