# Sprint 13 - Product Copy, UI And Implementation Readiness

Suggested duration: 2 weeks

Status: planned

## Sprint Goal

Подготовить implementation-ready scope после product/design/architecture decisions: final product copy updates, pilot kit rendering plan, TO BE visual restyle plan, SEO metadata updates and QA scope. This sprint bridges approved decisions into concrete implementation tasks without starting risky architecture or payload changes prematurely.

## Workflow Lane

Full Feature Lane with Design, SEO and QA gates.

## Source Decisions And Gaps

This sprint consumes approved outputs from:

- Sprint 09: `D-01` - `D-04`;
- Sprint 10: `D-05` - `D-06`;
- Sprint 11: `D-07` - `D-09`;
- Sprint 12: `D-10` - `D-12`.

Primary gaps: `PB-*`, `CJM-*`, `UI-*`, `ARCH-001` - `ARCH-004`, `SEO-TOBE-001` - `SEO-TOBE-005`.

## Inputs

- Approved Sprint 09-12 decision records.
- `../24-post-challenge-gap-analysis.md`
- `../25-post-challenge-use-cases-and-cjm.md`
- `../26-post-challenge-ux-ui-design-system.md`
- `../27-post-challenge-architecture-components-stack.md`
- Current `local/php_interface/include/product_data/*.php`
- Current `local/php_interface/include/product_page_blocks/*.php`
- Design handoff package in `../../../design-system-handoff/`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S13-001 | Prepare final approved copy changes for Platform, Agents, Dev and Forum product data | PM + Content + SEO | P1 | planned |
| S13-002 | Convert approved pilot kits into implementation-ready content structures | Content + Frontend | P1 | planned |
| S13-003 | Prepare proof/status content rules for public, private/NDA and blocked states | PM + Legal + Designer | P1 | planned |
| S13-004 | Prepare TO BE CSS/token implementation plan based on Sprint 11 mapping | Frontend + Designer | P1 | planned |
| S13-005 | Prepare component-level implementation tasks for product blocks, forms, chat, FAQ and `/price/` | Frontend + QA | P1 | planned |
| S13-006 | Prepare SEO metadata/canonical/schema update plan | SEO + Dev | P1 | planned |
| S13-007 | Prepare QA smoke matrix for old and new journeys | QA + Frontend + Backend | P1 | planned |
| S13-008 | Identify code changes that require ADR or Security / Integration and move them out of this sprint | Architect + PM | P1 | planned |

## Out Of Scope

- Deploy.
- Release evidence closure.
- Unapproved proof metrics, logos or regulatory badges.
- Structured upstream fields unless Sprint 12 opened and approved the integration scope.
- New public URLs unless SEO/canonical plan is approved.

## Deliverables

- Implementation-ready product copy packet.
- Product data update plan.
- Component implementation task list with preserved selectors.
- Token/CSS update plan.
- SEO update matrix.
- QA smoke matrix.
- ADR/Security / Integration split list.
- Updated sprint execution board status.

## Gates

| Gate | Required | Notes |
|---|---|---|
| Design | Yes | TO BE visual implementation must match approved component/state spec |
| SEO | Yes | Metadata, canonical, schema and sitemap implications |
| QA early | Yes | Required before code changes to behavior-bearing components |
| Legal | Conditional | Required for proof/status copy |
| Security / Integration | Conditional | Required for payload/endpoints/analytics data changes |
| ADR | Conditional | Required for component/content model architecture changes |

## Acceptance Criteria

1. Every planned copy/UI change traces back to an approved decision or gap.
2. Product copy does not include blocked claims.
3. Product use cases are ready to render as pilot kits.
4. TO BE implementation plan preserves form/chat/FAQ/menu/price contracts or scopes migration.
5. SEO plan includes metadata, canonical, schema and rendered evidence implications.
6. QA has a smoke matrix covering AS IS preserved flows and TO BE changes.
7. Architecture or payload changes are separated into proper lanes before coding.

## Verification

- `npm run product:gaps:check`
- `npm run design:handoff:check`
- `npm run seo:check` after any implementation changes, not required for planning-only close
- `npm run gaps:known`

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Planning silently becomes implementation without gates | PM + Tech Lead | Split tasks by gate before code starts |
| Approved copy still exceeds evidence | PM + Legal | Run proof/status review before implementation |
| Design implementation ignores current selectors | Frontend + QA | Use migration map and component/state contract |
| SEO changes happen without canonical decision | SEO + Dev | Block `/agents/` / `/aiagents/` changes until decision exists |

## Sprint Review

### Done

- To be filled at sprint end.

### Not Done

- To be filled at sprint end.

### Follow-Up

- To be filled at sprint end.
