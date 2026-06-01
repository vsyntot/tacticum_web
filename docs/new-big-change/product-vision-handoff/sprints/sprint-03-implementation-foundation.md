# Sprint 03 - Implementation Foundation

Suggested window: 06.07.2026 - 17.07.2026

Status: planned

## Sprint Goal

Подготовить техническую основу реализации: component boundaries, content model, form contract, analytics taxonomy, SEO plan, ADR decisions и QA smoke scope до массовой верстки страниц.

## Workflow Lane

Full Feature Lane with Security / Integration review for form and analytics decisions.

## Source Gaps

- `PV-008` Content model
- `PV-012` Lead qualification
- `PV-014` Analytics
- `PV-015` Dev implementation

## Inputs

- Sprint 01 IA.
- Sprint 02 design/component spec.
- `../09-as-is-to-be-preservation-migration-map.md`
- `../../../workflow/current-state.md`
- `../../../workflow/lead-form-contract.md`
- `../../../workflow/analytics-events.md`
- `../../../workflow/asset-layout-audit.md`
- `../../../workflow/post-deploy-smoke.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S03-001 | Decide content model: static pages, local components, new iblocks, or mixed | Tech Lead + PM | P1 | planned |
| S03-002 | Define component boundaries for product hero, product cards, module grid, proof, CTA, FAQ | Frontend + Designer | P1 | planned |
| S03-003 | Draft ADR if content model, URL strategy or form contract changes | Architect | P1 | planned |
| S03-004 | Update or draft lead form contract for product-aware fields | Backend + QA + PM | P1 | planned |
| S03-005 | Define analytics taxonomy for product funnel without PII | PM + Analytics + Frontend | P1 | planned |
| S03-006 | Define SEO implementation plan: metadata, canonical, sitemap, robots, noindex | SEO + Dev | P1 | planned |
| S03-007 | Define asset loading plan through `Bitrix\\Main\\Page\\Asset` | Frontend | P1 | planned |
| S03-008 | Prepare QA smoke plan for new pages and forms | QA + Dev | P1 | planned |
| S03-009 | Decide feature flag / staged rollout approach if needed | DevOps + Tech Lead | P2 | planned |

## Out Of Scope

- Full page implementation.
- Visual polish.
- Production deploy.
- New upstream integrations.
- New AI endpoints.

## Deliverables

- Implementation plan.
- Component boundary document or ADR section.
- Updated lead form contract draft.
- Analytics events draft.
- SEO technical checklist.
- QA smoke checklist.
- ADR(s), if gates trigger.
- Affected files/areas list.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | Conditional | Required for content model, form contract, URL migration, shared architecture |
| Design | Yes | Design handoff must be implementable |
| QA early | Yes | Forms, navigation, product pages, SEO smoke |
| SEO | Yes | New URLs and canonical/sitemap |
| Security / Integration | Yes | Product-aware form fields, analytics, PII, AI/chat context |

## Acceptance Criteria

1. Development knows whether product pages are static entries or content-backed.
2. No new hardcoded iblock IDs are planned.
3. JS/CSS asset strategy follows Bitrix `Asset`.
4. Product-aware form payload is documented and reviewed before implementation.
5. Analytics events exclude PII and raw user text.
6. SEO checklist covers every new/changed URL.
7. QA has smoke scenarios for desktop/mobile and forms.
8. ADR gate decision is explicit.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Content model over-engineered too early | Tech Lead + PM | Start static/local component unless editing workflow requires iblocks |
| Form changes break upstream | Backend + QA | Preserve existing endpoint/response shape where possible |
| Analytics leaks PII | PM + Frontend + QA | Allowlist event params only |
| SEO misses legacy `/aiagents/` | SEO | Include migration/canonical decision in checklist |
| Components duplicate existing lead/FAQ/chat code | Frontend | Reuse local components and document new variants |

## Verification Plan

No full automated run is required unless code is touched. If implementation scaffolding is added, run relevant static checks:

```bash
npm run bitrix:check
npm run template-styles:check
npm run seo:check
```

PHP lint remains CI fallback if local PHP CLI is unavailable.

## Sprint Review

### Done

- To be filled at sprint end.

### Not Done

- To be filled at sprint end.

### Follow-Up

- To be filled at sprint end.
