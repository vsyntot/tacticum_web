# Sprint 19 — CJM, CTA And CRM Qualification

Дата формирования: 04.06.2026
Статус: in-progress; decision package draft added
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Roadmap: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`

## Sprint Goal

Перевести product-first страницы из линейного lead-gen UX в enterprise decision journey: role-based CJM, CTA taxonomy, returning-lead path, pilot kits, Sales qualification and no-PII product funnel analytics.

## Capacity / Constraints

- Production freeze: payload/CRM changes prohibited until Security / Integration scope is approved.
- Known dependencies: Sprint 18 taxonomy/packaging and Sprint 17 proof constraints.
- Agents / roles:
  - PM: role journeys, CTA taxonomy and pilot kit acceptance;
  - UX: CJM and success states;
  - Sales: qualification usefulness and fallback approval;
  - Backend: lead contract and upstream/CRM payload scope;
  - Frontend: `tacticum:lead.cta` params and existing form contracts;
  - QA: form/chat/prefill/staff regression and no-PII evidence;
  - Analytics/Marketing: product funnel goals and Metrika evidence.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| S19-001 | `UX-001` Role-based CJM | Full Feature | PM + UX + Sales | P1 | in-progress | Role CJM draft added; approval pending |
| S19-002 | `UX-007` Procurement/security journey | Full Feature | PM + Security + UX | P1 | in-progress | Safe journey draft added; security/UX approval pending |
| S19-003 | `UX-008` Product pilot kits | Full Feature | PM + Content + Sales | P1 | in-progress | Product pilot kit draft added; owner approval pending |
| S19-004 | `UX-009` Product fit guide decision | Full Feature | PM + UX | P2 | in-progress | Static v1 recommendation drafted; PM/UX approval pending |
| S19-005 | `UX-002` CTA taxonomy | Full Feature | PM + UX + Sales | P1 | in-progress | CTA matrix draft added; no payload changes made |
| S19-006 | `UX-003` Returning-lead path | Full Feature | PM + Sales + UX | P2 | in-progress | Returning-lead path draft added; Sales routing pending |
| S19-007 | `UX-010` Success-state copy | Full Feature | PM + UX + QA | P2 | in-progress | Success copy targets drafted; implementation waits for Sprint 20 |
| S19-008 | `CMP-003` `tacticum:lead.cta` role/stage extension | Full Feature | Frontend + Backend + PM | P1 | in-progress | Existing params kept for v1; extension blocked until CTA/CRM approval |
| S19-009 | `ARCH-005` Structured CRM/upstream qualification | Security / Integration | Backend + PM + Sales + QA | P0 | blocked | Draft recommends current text fallback; structured field approval pending |
| S19-010 | `ARCH-006` Product funnel analytics goal map | Full Feature | PM + Analytics + QA | P1 | in-progress | No-PII funnel map drafted; Analytics/Metrika approval pending |
| S19-011 | `CFG-004` Config sync evidence in release | Full Feature | DevOps + Backend | P1 | in-progress | Safe config evidence rule drafted; target release evidence pending |

## Out Of Scope

- Adding top-level upstream/CRM fields before contract approval.
- Adding PII or raw params to analytics.
- Changing form endpoint response shape.
- Introducing new private document/proof flow; handled in Sprint 22.
- Visual redesign of forms/chat; detailed states are Sprint 20.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | Conditional | Required if lead/upstream contract or component parameter architecture changes |
| Design | Yes | Role CJM, CTA variants, returning-lead path and success states |
| QA early | Yes | Public forms, chat handoff, prefill, staff-order and analytics |
| Security / Integration | Yes | Structured CRM/upstream fields, procurement journey and no-PII evidence |
| SEO | Conditional | CTA/copy changes must not conflict with product metadata/canonical decisions |
| Post-deploy smoke | Yes if implementation changes | Form/chat/analytics/product page smoke |

## Acceptance Criteria

1. Role-based CJM is documented for CEO/business owner, CIO/CTO, CISO/security, procurement and product owner.
2. Each role has trigger, concern, proof need, CTA, expected next step and Sales routing note.
3. Product pilot kits define input artifacts, owner responsibilities, output, limitation and evidence status for Platform, Agents, Dev and Forum.
4. CTA taxonomy covers product, role and stage without assuming unapproved payload fields.
5. Returning-lead path is defined for architecture session, proof/doc request and pilot refinement.
6. Success-state copy explains next step by product/scenario without exposing CRM internals.
7. Sales either approves current `task` fallback for v1 or structured CRM/upstream fields are scoped through Security / Integration lane.
8. `lead-form-contract.md` is updated if component params or payload fields change.
9. Product funnel goal map is approved with no PII params.
10. Runtime config explicit/default evidence is attached to future release sign-off when affected.

## QA / Smoke Scope

| Scenario | URL/API/Tool | Expected |
|---|---|---|
| Product lead CTA | `/platform/`, `/agents/`, `/dev/`, `/forum/` | Scenario/context preserved; existing endpoint response unchanged |
| Default lead form | `/`, `/services/`, `/contacts/` | Existing behavior unaffected |
| Chat handoff | `/calculator/`, `/price/` | Safe context/handoff remains no-PII |
| Prefill | `/local/rest/tacticum_prefill.php` | Existing controlled prefill behavior remains unchanged |
| Staff order | `/local/rest/tacticum_sale_staff.php` | Rich workers payload unaffected unless explicitly scoped |
| Analytics | `analytics.js`, Metrika evidence | Product events contain controlled non-PII params only |
| Config evidence | `npm run config:runtime:check` | Explicit/default state reviewed safely |

## Verification

### Automated

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
npm run js:check
npm run browser:smoke
```

If form/chat/product implementation changes:

```bash
npm run browser:smoke:prod
npm run release:manual-gates:helper
npm run metrika:goals:helper
```

### Manual / Owner Evidence

- Sales approval of fallback or structured fields.
- PM/UX approval of CJM and CTA taxonomy.
- Analytics/Metrika goal evidence without PII.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| CTA taxonomy creates fake precision without CRM support | PM + Sales | Fallback approval or structured field contract |
| New hidden fields break old forms | Frontend + QA | Preserve current form contract; smoke old and new surfaces |
| Analytics becomes PII-adjacent | Analytics + QA | Controlled enums only; evidence skeleton rejects raw params |
| Procurement journey promises unsupported security artifacts | PM + Security | Use Sprint 17 claim matrix and Sprint 22 access model |
| Returning-lead path bypasses consent or context validation | Backend + QA | Keep existing form/CSRF/consent model |

## Definition Of Done

- `CFG-004`, `UX-001`, `UX-002`, `UX-003`, `UX-007`, `UX-008`, `UX-009`, `UX-010`, `ARCH-005`, `ARCH-006`, `CMP-003` have output, owner decision or explicit blocker.
- No payload/CRM changes are made without contract update and Security / Integration review.
- Product funnel analytics remains no-PII.
- Design inputs are ready for Sprint 20.

## Sprint Review

### Done

- Added `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md` as Sprint 19 approval package.
- Documented role-based enterprise CJM for CEO/business owner, CIO/CTO, CISO/security, procurement/legal and product owner.
- Documented procurement/security journey without public certification, SLA or private document promises.
- Drafted product pilot kits for Platform, Agents, Dev and Forum with input artifacts, owner responsibilities, output, limitation and `pilot-artifact` evidence status.
- Recommended keeping product fit guide static in v1; interactive/guided fit remains a future Full Feature + Security review.
- Drafted CTA taxonomy by stage: explore, pilot, architecture, procurement/security, team/delivery, estimate and returning lead.
- Drafted returning-lead path without adding a new form mode.
- Drafted success-state copy targets for future Sprint 20 UI/state work.
- Documented CRM/upstream decision: keep current `lead_*` text fallback inside `task` until Sales/upstream/Security approval.
- Documented no-PII product funnel goal map and config runtime evidence rule.
- Updated `lead-form-contract.md` and `analytics-events.md` with Sprint 19 decision references.

### Not Done

- PM/UX/Sales approval of CJM, CTA taxonomy and pilot kits remains pending.
- Sales/upstream approval of current fallback or structured CRM fields remains pending.
- Analytics/Metrika owner approval remains pending.
- No form payload, upstream JSON field, analytics implementation, endpoint response shape or public UI state changes were implemented.
- `browser:smoke` / production form success-flow were not run because this was docs/decision scope.

### Follow-Up

- Collect PM/UX/Sales/Security/Analytics approval against `product-cjm-cta-crm-qualification-decision-2026-06-04.md`.
- If structured CRM fields are required, open separate Security / Integration scope and update `lead-form-contract.md`.
- Feed success-state and CTA state requirements into Sprint 20 design-system/state spec.
