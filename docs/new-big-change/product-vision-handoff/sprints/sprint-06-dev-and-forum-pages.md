# Sprint 06 - Dev And Forum Pages

Suggested window: 17.08.2026 - 28.08.2026

Status: in-progress - first implementation slice added 01.06.2026

## Sprint Goal

Реализовать оставшиеся продуктовые страницы Tacticum Dev и Tacticum Forum с безопасным публичным framing, понятной связью с Platform и product-specific proof.

## Workflow Lane

Full Feature Lane with Design, SEO and claim review.

## Source Gaps

- `PV-004` Product pages
- `PV-007` Case proof
- `PV-013` SEO
- `PV-017` Tacticum Dev tone
- `PV-018` External references
- `PV-020` Delivery model

## Inputs

- Sprint 00 Dev public/private tone decision.
- Sprint 01 product page briefs.
- Sprint 02 product page designs.
- Sprint 03 implementation foundation.
- `../04-product-page-briefs.md`
- `../../dev.md`
- `../../forum.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S06-001 | Implement Tacticum Dev page with safe governance/productivity framing | Frontend + Editor + PM | P1 | done-first-slice |
| S06-002 | Remove or rewrite public workforce reduction language | PM + Legal + Editor | P0 | done-first-slice |
| S06-003 | Show Dev layers: profiles, RE knowledge, design tokens, gates, MCP bundles | Frontend + Architect | P1 | done-first-slice |
| S06-004 | Implement Tacticum Forum page | Frontend + Editor + PM | P1 | done-first-slice |
| S06-005 | Show Forum model: scenario + LLM, Needs Catalog, A/B tests, analytics, journal | Frontend + Designer | P1 | done-first-slice |
| S06-006 | Add product-aware CTAs for Dev and Forum | Frontend + Backend + QA | P1 | done-with-scenario-select |
| S06-007 | Add safe proof/benchmark sections | PM + Editor + Legal | P1 | proof-readiness-added-evidence-pending |
| S06-008 | Add SEO metadata and canonical/sitemap coverage | SEO + Dev | P1 | done-static-guard-and-product-schema |
| S06-009 | Add product FAQs | Editor + Frontend | P2 | done-static-faq |

## Out Of Scope

- Real Dev tooling integration.
- Public training/cohort sales page unless separately scoped.
- Full Forum visual scenario editor demo.
- Unapproved benchmark citations.
- Workforce transformation public deck.

## Deliverables

- `/dev/` product page or approved equivalent.
- `/forum/` product page or approved equivalent.
- Safe Dev copy.
- Forum page with scenario+LLM explanation.
- Product-aware CTA context.
- SEO metadata.
- Updated claim register statuses for Dev/Forum claims.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | Conditional | New URL/content model/form contract |
| Design | Yes | Product pages implement approved design |
| QA early | Yes | Forms and responsive pages |
| SEO | Yes | New product URLs |
| Security / Integration | Conditional | Claims about codebase security, on-prem, LLM, data processing |
| Legal | Yes | Dev workforce language, benchmarks, competitor comparisons |
| Post-deploy smoke | Yes | New public URLs |

## Acceptance Criteria

1. Dev page does not publish workforce reduction claims.
2. Dev page explains governance over AI-assisted development, not generic coding assistant hype.
3. Dev proof is either verified or framed as pilot/benchmark with source status.
4. Forum page clearly contrasts scenario bots, pure LLM bots and Tacticum hybrid model.
5. Forum metrics use approved ranges or safe benchmark language.
6. Both pages explicitly show how they rely on Platform.
7. Product-aware CTAs identify Dev vs Forum lead context.
8. SEO metadata is unique and pages have one H1.
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
| Dev CTA | Form carries Dev context and controlled scenario select |
| Forum CTA | Form carries Forum context and controlled scenario select |
| Dev claim scan | No workforce reduction public claim |
| Forum metrics scan | No unapproved hard performance promise |
| Mobile product pages | Text/cards/tables do not overlap |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Dev content sounds threatening to engineering teams | PM + Editor | Frame as governance, quality, productivity and reskilling |
| Forum overpromises automation | PM + Legal | Use range with conditions and benchmark source status |
| Competitor comparisons become legally risky | Legal + Editor | Compare categories or remove named competitor claims |
| Technical detail overwhelms buyer | Designer + Editor | Use progressive disclosure and summary blocks |

## Sprint Review

### Done

- 01.06.2026 first implementation slice:
  - `/dev/` page added with safe governance/productivity framing and no public workforce reduction claims;
  - `/forum/` page added with scenario + LLM framing, analytics/journal modules and product-aware CTA;
  - both pages show Platform relation and have unique SEO metadata;
  - navigation and footer expose both URLs.
- 01.06.2026 FAQ hardening slice:
  - `/dev/` and `/forum/` received static product FAQ content through the shared product page renderer;
  - existing `faq.js` is loaded through `tacticum_page_assets=faq`;
  - FAQ copy stays within safe governance/pilot/integration wording and avoids unapproved benchmarks.
- 01.06.2026 CTA qualification slice:
  - `/dev/` and `/forum/` product forms received optional controlled `lead_scenario` select through shared `tacticum:lead.cta`;
  - no new required fields, JS/CSS assets, analytics params or REST/upstream contract changes were added.
- 01.06.2026 rollout/delivery model slice:
  - shared product renderer received reusable rollout block;
  - `/dev/` now explains one-team/one-stack workflow rollout, and `/forum/` explains one-dialog-flow rollout;
  - no workforce reduction, benchmark, automation-rate, SLA, guarantee or channel-readiness claims were added.
- 01.06.2026 proof readiness slice:
  - shared product renderer received reusable proof readiness block;
  - `/dev/` and `/forum/` now describe pilot evidence artifacts without publishing workforce, benchmark, automation-rate or channel-readiness claims;
  - real benchmark/proof content remains evidence pending.
- 01.06.2026 structured-data slice:
  - `/dev/` and `/forum/` received minimal `SoftwareApplication` JSON-LD through SEO helper options;
  - schema intentionally excludes offers/pricing/reviews/ratings and proof claims.

### Not Done

- Public benchmark/metric proof remains deferred until evidence and legal status are approved.
- Browser/visual smoke still requires local Bitrix runtime or post-deploy smoke.

### Follow-Up

- Add verified proof only through the claim register.
- Add product FAQ content when FAQ section keys and content are ready.
- Keep Dev public language focused on governance, quality, architecture and team maturity.
