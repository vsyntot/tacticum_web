# Sprint 01 - IA And Messaging

Suggested window: 08.06.2026 - 19.06.2026

Status: planned

## Sprint Goal

Зафиксировать целевую информационную архитектуру, URL strategy, messaging hierarchy, CTA taxonomy и page-level acceptance criteria для нового product-first сайта.

## Workflow Lane

Full Feature Lane with Design and SEO gates.

## Source Gaps

- `PV-002` Product taxonomy
- `PV-003` Homepage
- `PV-004` Product pages
- `PV-009` Navigation
- `PV-013` SEO
- `PV-020` Delivery model

## Inputs

- Sprint 00 decisions.
- `../03-information-architecture-to-be.md`
- `../04-product-page-briefs.md`
- `../05-design-and-content-brief.md`
- `../09-as-is-to-be-preservation-migration-map.md`
- `../../../workflow/current-state.md`
- `../../../workflow/seo-gap-analysis.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S01-001 | Утвердить sitemap TO BE и список новых/изменяемых URL | PM + SEO + Tech Lead | P1 | planned |
| S01-002 | Решить canonical strategy для `/agents/` vs `/aiagents/` | PM + SEO | P1 | planned |
| S01-003 | Спроектировать header/footer navigation | UX + PM + SEO | P1 | planned |
| S01-004 | Сформировать homepage content model | UX + PM + Editor | P1 | planned |
| S01-005 | Сформировать product page template model | UX + PM + Editor | P1 | planned |
| S01-006 | Уточнить briefs для Platform, Agents, Dev, Forum | PM + Editor + Architect | P1 | planned |
| S01-007 | Подготовить SEO cluster draft and metadata requirements | SEO + Editor | P2 | planned |
| S01-008 | Сформировать CTA taxonomy by product and journey stage | PM + Sales + UX | P1 | planned |
| S01-009 | Определить page-level acceptance criteria | PM + QA + UX | P1 | planned |

## Out Of Scope

- Final UI design.
- Code implementation.
- REST/form payload changes.
- Deep SEO copywriting for every page.
- Industry landing pages.

## Deliverables

- Approved TO BE sitemap.
- URL migration table.
- Header/footer navigation spec.
- Homepage wire-content outline.
- Product page standard outline.
- Updated product page briefs.
- CTA taxonomy.
- SEO metadata draft: URL, title intent, H1 intent, description intent, canonical notes.
- Page acceptance criteria.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | Conditional | Required if URL/content model decision changes architecture |
| Design | Yes | IA and navigation become design input |
| QA early | Conditional | Needed for acceptance criteria around forms/navigation |
| SEO | Yes | New URL and canonical decisions |
| Security / Integration | Conditional | Only if CTA/form requirements imply new payload |

## Acceptance Criteria

1. Product-first navigation is approved.
2. New URL list is explicit and includes canonical/redirect/noindex decisions.
3. `/aiagents/` migration strategy is decided.
4. Homepage model explains ecosystem before service entries.
5. Product page template covers problem, solution, Platform relation, modules, use cases, proof, CTA.
6. CTA taxonomy maps products to lead intent.
7. SEO has enough input to review URL/indexation risk.
8. Development can estimate implementation scope from page briefs.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Too many pages in first release | PM | Split first release and post-launch backlog |
| SEO/canonical decision delayed | SEO + PM | Keep `/aiagents/` unchanged until safe migration plan |
| Navigation becomes overloaded | UX | Use dropdowns and separate product/service layers |
| Messaging repeats source decks too literally | Editor | Rewrite into web hierarchy, not slide copy |

## Sprint Review

### Done

- To be filled at sprint end.

### Not Done

- To be filled at sprint end.

### Follow-Up

- To be filled at sprint end.
