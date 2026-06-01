# Sprint 05 - Platform And Agents Pages

Suggested window: 03.08.2026 - 14.08.2026

Status: in-progress - first implementation slice added 01.06.2026

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
| S05-001 | Implement Platform page shell and content | Frontend + Editor + Architect | P1 | done-first-slice |
| S05-002 | Implement Platform module map | Frontend + Designer + Architect | P1 | done-first-slice |
| S05-003 | Implement Platform deployment/security blocks with safe wording | Frontend + PM + Security | P1 | done-safe-rollout-model |
| S05-004 | Implement Agents page or approved `/aiagents/` migration | Frontend + SEO + PM | P1 | done-with-compatibility-bridge |
| S05-005 | Add Agents scenarios: HR, legal, accounting, corporate KB, support, IT helpdesk | Editor + Frontend | P1 | done-first-slice |
| S05-006 | Show Platform relation inside Agents page | Frontend + Architect | P1 | done-first-slice |
| S05-007 | Add product-aware CTAs for Platform and Agents | Frontend + Backend + QA | P1 | done-with-scenario-select |
| S05-008 | Add SEO metadata and sitemap/canonical coverage | SEO + Dev | P1 | done-static-guard-and-product-schema |
| S05-009 | Add FAQ blocks or product FAQ content | Editor + Frontend | P2 | done-static-faq |

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
| Platform CTA | Form opens/submits with Platform context and controlled scenario select |
| Agents CTA | Form opens/submits with Agents context and controlled scenario select |
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

- 01.06.2026 first implementation slice:
  - `/platform/` page added with product promise, module map, architecture and product-aware CTA;
  - `/agents/` page added with business-function scenarios, Platform relation and product-aware CTA;
  - `/aiagents/` preserved as compatibility/money URL and bridged to `/agents/` without redirect/canonical migration;
  - top/footer product navigation implemented;
  - static checks passed through product-layer, homepage and compatibility bridge plans.
- 01.06.2026 FAQ hardening slice:
  - `/platform/` and `/agents/` received static product FAQ content through the shared product page renderer;
  - existing `faq.js` is loaded through `tacticum_page_assets=faq`;
  - FAQ copy uses safe pilot/discovery/deployment wording and avoids unapproved claims.
- 01.06.2026 CTA qualification slice:
  - `/platform/` and `/agents/` product forms received optional controlled `lead_scenario` select through shared `tacticum:lead.cta`;
  - no new required fields, JS/CSS assets, analytics params or REST/upstream contract changes were added.
- 01.06.2026 rollout/delivery model slice:
  - shared product renderer received reusable rollout block;
  - `/platform/` and `/agents/` now explain assessment/discovery, pilot, integration/deployment alignment and rollout decision with safe wording;
  - no registry, ПАК, certification, SLA tier, guarantee or pricing claims were added.
- 01.06.2026 proof readiness slice:
  - shared product renderer received reusable proof readiness block;
  - `/platform/` and `/agents/` now describe pilot evidence artifacts without publishing metrics, logos, testimonials or regulatory proof;
  - real case/proof content remains evidence pending.
- 01.06.2026 structured-data slice:
  - `/platform/` and `/agents/` received minimal `SoftwareApplication` JSON-LD through SEO helper options;
  - schema intentionally excludes offers/pricing/reviews/ratings and proof claims.
- 01.06.2026 product fit guide slice:
  - shared product renderer received reusable `fit_guide` decision-support block;
  - `/platform/` and `/agents/` now explain "подходит / не подходит / с чего начать" before detailed product sections;
  - no form, REST, upstream, analytics or URL behavior changed.
- 01.06.2026 security/procurement path slice:
  - shared product renderer received reusable `procurement` block;
  - `/platform/` and `/agents/` now expose safe architecture/security/procurement review topics before rollout;
  - no downloads, new forms, REST endpoints, analytics params or registry/certification/SLA/guarantee claims were added.
- 01.06.2026 use-case anatomy slice:
  - shared product renderer received reusable `use_cases` block;
  - `/platform/` and `/agents/` now describe 3 pilotable use cases each with trigger, owner, pilot input, pilot output and limitation;
  - no new CTA fields, REST/upstream behavior, analytics params or public proof metrics were added.
- 01.06.2026 comparison/boundary slice:
  - shared product renderer received reusable `comparison` block;
  - `/platform/` now separates Platform from application products and delivery entries;
  - `/agents/` now explicitly compares Agents with Forum and `/aiagents/` demo flow.

### Not Done

- `/aiagents/` vs `/agents/` canonical/redirect decision is explicitly deferred.
- Product-specific public metrics/cases remain deferred until evidence and legal approval.
- Browser/visual smoke still requires local Bitrix runtime or post-deploy smoke.

### Follow-Up

- Decide `/aiagents/` canonical strategy after SEO review and production data.
- If FAQ content later moves to the FAQ iblock, preserve the current static questions as editorial baseline.
- Extend proof/case relation only after claim and evidence approval.
