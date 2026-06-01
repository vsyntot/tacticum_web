# Sprint 00 - Decision And Evidence Baseline

Suggested window: 01.06.2026 - 05.06.2026

Status: planned

## Sprint Goal

Зафиксировать продуктовые решения и evidence baseline до старта дизайна, контента и разработки. Спринт должен убрать главный риск: строить TO BE сайт на неподтвержденных claim'ах и неутвержденной taxonomy.

## Workflow Lane

Full Feature Lane with early Legal/Security review.

## Source Gaps

- `PV-001` Positioning
- `PV-002` Product taxonomy
- `PV-006` Regulatory claims
- `PV-016` Sales materials / source of truth
- `PV-017` Tacticum Dev tone
- `PV-018` External references
- `PV-019` Client logos/testimonials
- `PV-020` Delivery model

## Inputs

- `../01-target-product-vision.md`
- `../02-as-is-to-be-gap-analysis.md`
- `../07-risk-and-claims-register.md`
- `../08-decisions-and-open-questions.md`
- `../../tacticum.md`
- `../../platform.md`
- `../../agents.md`
- `../../dev.md`
- `../../forum.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S00-001 | Утвердить целевую taxonomy: Platform / Agents / Dev / Forum / delivery services | PM/Product Owner | P1 | planned |
| S00-002 | Утвердить основной positioning statement для сайта | PM + Sales | P1 | planned |
| S00-003 | Пройти `07-risk-and-claims-register.md` и назначить owner/evidence/status каждому P0/P1 claim | PM + Legal + Security | P0 | planned |
| S00-004 | Решить public/private split для Tacticum Dev workforce content | PM + Legal + HR/Sales | P0 | planned |
| S00-005 | Подтвердить или запретить customer logos/testimonials из `index.html` | Sales + Legal | P0 | planned |
| S00-006 | Подтвердить delivery model: SaaS / on-prem / ПАК / pilot / integration / support | PM + Sales + Architect | P1 | planned |
| S00-007 | Зафиксировать allowed public wording для registry/security/on-prem claims | Legal + Security + PM | P0 | planned |
| S00-008 | Подготовить first release scope decision | PM + Tech Lead + Designer | P1 | planned |

## Out Of Scope

- Дизайн экранов.
- Разработка страниц.
- Изменение форм или REST endpoints.
- Создание новых URL.
- Публичная публикация claim'ов.

## Deliverables

- Updated `../07-risk-and-claims-register.md` with owners and statuses.
- Updated `../08-decisions-and-open-questions.md` with answered P0/P1 questions.
- Approved one-liner and positioning statement.
- Product taxonomy decision.
- First release scope decision.
- Public/private content decision for Tacticum Dev.
- Evidence backlog with owners and due dates.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | No | Decision-only sprint |
| Design | No | Design starts after this sprint |
| QA early | No | QA joins later for implementation |
| SEO | Conditional | Needed if URL decision is made here |
| Security / Integration | Yes | Regulatory, on-prem, LLM, PII and КИИ claims |
| Legal | Yes | Registry, tax, logos, testimonials, named clients |

## Acceptance Criteria

1. Product taxonomy is approved and written down.
2. Public positioning statement is approved.
3. Every P0 claim in `07-risk-and-claims-register.md` has one of: `allowed`, `rewrite`, `needs evidence`, `private only`, `remove`.
4. No claim with `needs evidence` is allowed into public design copy.
5. Tacticum Dev public tone rules are documented.
6. First release scope is clear enough for Sprint 01 IA.
7. Open questions blocking IA/design are either answered or assigned to a named owner.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Product owner does not approve taxonomy | PM | Hold scope until taxonomy is decided |
| Legal/security unavailable | PM | Mark claims `needs evidence` and block public usage |
| Sales wants to keep unapproved logos | Sales + Legal | Use generic proof blocks until approval |
| Dev workforce content remains provocative | PM + Legal | Move to private deck or rewrite as governance/productivity |

## Sprint Review

### Done

- To be filled at sprint end.

### Not Done

- To be filled at sprint end.

### Follow-Up

- To be filled at sprint end.
