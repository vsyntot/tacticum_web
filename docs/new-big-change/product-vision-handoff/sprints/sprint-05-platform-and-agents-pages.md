# Sprint 05 - Platform And Agents Pages

Suggested window: 03.08.2026 - 14.08.2026

Status: planned

## Sprint Goal

Реализовать первые продуктовые страницы новой линейки: Tacticum Platform и Tacticum Agents. Эти страницы должны доказать платформенное ядро и показать первый прикладной продукт поверх него.

## Workflow Lane

Full Feature Lane with SEO and Security / Integration review for claims.

## Source Gaps

- `PV-004` Product pages
- `PV-005` Platform proof
- `PV-007` Case proof
- `PV-012` Lead qualification
- `PV-013` SEO
- `PV-015` Dev implementation
- `PV-020` Delivery model

## Inputs

- Sprint 00 safe claims.
- Sprint 01 URL/page specs.
- Sprint 02 product page design.
- Sprint 03 implementation foundation.
- `../04-product-page-briefs.md`
- `../../platform.md`
- `../../agents.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S05-001 | Implement Platform page shell and content | Frontend + Editor + Architect | P1 | planned |
| S05-002 | Implement Platform module map | Frontend + Designer + Architect | P1 | planned |
| S05-003 | Implement Platform deployment/security blocks with safe wording | Frontend + PM + Security | P1 | planned |
| S05-004 | Implement Agents page or approved `/aiagents/` migration | Frontend + SEO + PM | P1 | planned |
| S05-005 | Add Agents scenarios: HR, legal, accounting, corporate KB, support, IT helpdesk | Editor + Frontend | P1 | planned |
| S05-006 | Show Platform relation inside Agents page | Frontend + Architect | P1 | planned |
| S05-007 | Add product-aware CTAs for Platform and Agents | Frontend + Backend + QA | P1 | planned |
| S05-008 | Add SEO metadata and sitemap/canonical coverage | SEO + Dev | P1 | planned |
| S05-009 | Add FAQ blocks or product FAQ content | Editor + Frontend | P2 | planned |

## Out Of Scope

- Dev and Forum pages.
- Full public pricing.
- Unapproved customer logos/case metrics.
- New AI demos.
- New backend integrations.

## Deliverables

- `/platform/` page or approved equivalent URL.
- `/agents/` page or `/aiagents/` migrated page, based on Sprint 01 decision.
- Product-aware CTA context.
- SEO metadata and canonical decisions.
- Updated sitemap/check docs if new URLs are introduced.
- Documentation update for current-state/gap-analysis when implemented.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | Conditional | New URL/content model/form contract |
| Design | Yes | Product pages implement approved design |
| QA early | Yes | New pages, forms, responsive states |
| SEO | Yes | New product URLs and `/aiagents/` strategy |
| Security / Integration | Yes | Security, on-prem, LLM, regulatory claims |
| Post-deploy smoke | Yes | New public URLs |

## Acceptance Criteria

1. Platform page clearly explains why the platform exists and how it underpins products.
2. Platform module map is readable on desktop and mobile.
3. Platform claims about registry/security/on-prem use only approved wording.
4. Agents page is not a generic bot landing. It shows business-function assistants over Platform.
5. Agents scenarios are concrete but not overpromised.
6. Product-aware CTAs identify Platform vs Agents lead context.
7. SEO metadata is unique and page has one H1.
8. `/aiagents/` canonical/migration decision is implemented or explicitly deferred.
9. Browser/visual smoke passes for both pages.

## Verification

Recommended:

```bash
npm run css:check
npm run template-styles:check
npm run bitrix:check
npm run seo:check
npm run browser:smoke
npm run visual:smoke
```

Manual smoke:

| Scenario | Expected |
|---|---|
| Platform CTA | Form opens/submits with Platform context |
| Agents CTA | Form opens/submits with Agents context |
| Mobile navigation | Product pages reachable and menu closes correctly |
| Claim blocks | No `needs evidence` wording published |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Platform page too technical | PM + Architect + Designer | Use layers: business value first, technical detail after |
| Agents page duplicates old `/aiagents/` tone | Editor + PM | Reframe as product over Platform |
| `/aiagents/` SEO regression | SEO | Keep canonical/redirect strategy explicit |
| Regulatory claim drift | Security + PM | Compare copy against claim register before release |

## Sprint Review

### Done

- To be filled at sprint end.

### Not Done

- To be filled at sprint end.

### Follow-Up

- To be filled at sprint end.
