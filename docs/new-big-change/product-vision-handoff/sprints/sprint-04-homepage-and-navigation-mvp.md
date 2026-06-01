# Sprint 04 - Homepage And Navigation MVP

Suggested window: 20.07.2026 - 31.07.2026

Status: in-progress - first implementation slice added 01.06.2026

## Sprint Goal

Реализовать первую пользовательскую точку новой продуктовой модели: обновленную главную страницу и продуктовую навигацию, сохранив текущие lead flows и технические контракты.

## Workflow Lane

Full Feature Lane.

## Source Gaps

- `PV-001` Positioning
- `PV-002` Product taxonomy
- `PV-003` Homepage
- `PV-005` Platform proof
- `PV-009` Navigation
- `PV-011` Interaction
- `PV-012` Lead qualification

## Inputs

- Sprint 00 safe claims.
- Sprint 01 homepage and navigation spec.
- Sprint 02 design.
- Sprint 03 implementation plan.
- `../09-as-is-to-be-preservation-migration-map.md`
- `../../../workflow/current-state.md`
- `../../../design-system-handoff/04-interaction-contracts.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S04-001 | Update homepage hero to ecosystem positioning | Frontend + Editor | P1 | done-first-slice |
| S04-002 | Add ecosystem map: Platform + Agents + Dev + Forum | Frontend + Designer | P1 | done-first-slice |
| S04-003 | Add product cards and delivery entry paths | Frontend + PM | P1 | done-first-slice |
| S04-004 | Update header navigation to product-first model | Frontend + SEO + QA | P1 | done-first-slice |
| S04-005 | Update footer product/service hierarchy | Frontend + PM | P2 | done-first-slice |
| S04-006 | Add product-aware CTA context on homepage | Frontend + Backend + QA | P1 | done-first-slice |
| S04-007 | Preserve existing form, modal, chat and FAQ contracts | Frontend + QA | P1 | preserved |
| S04-008 | Add homepage SEO metadata for ecosystem positioning | SEO + Dev | P1 | done-first-slice |
| S04-009 | Run desktop/mobile browser and visual smoke | QA + Dev | P1 | planned |

## Out Of Scope

- Full product detail pages.
- New industry pages.
- Public pricing/licensing.
- New AI chat behavior unless separately scoped.
- Claims blocked by Sprint 00 evidence status.

## Deliverables

- Updated `index.php` or relevant homepage components.
- Updated header/footer menus.
- New/reused homepage components.
- Product-aware CTA context where approved.
- Updated SEO metadata.
- Updated docs if behavior/contracts change.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | Conditional | Required if navigation/content architecture changes shared pattern |
| Design | Yes | Homepage and nav are designed assets |
| QA early | Yes | Navigation and forms |
| SEO | Yes | Homepage metadata, menu linking, canonical |
| Security / Integration | Conditional | If form payload changes |
| Post-deploy smoke | Yes | Homepage is primary public URL |

## Acceptance Criteria

1. First screen communicates Tacticum as AI software ecosystem.
2. Platform + Agents + Dev + Forum are visible without scrolling too far.
3. Service/delivery entries remain available and understandable.
4. Header and mobile navigation work on desktop/mobile.
5. Existing forms still submit through approved contract.
6. Analytics remains PII-safe.
7. Homepage has one H1, correct title/description/canonical.
8. Browser smoke has no console errors/page errors/network blockers.
9. Text does not overlap in responsive states.

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

After deploy:

```bash
npm run visual:smoke:prod
npm run browser:smoke:prod
npm run seo:check:prod
```

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Homepage becomes too dense | Designer + PM | Prioritize ecosystem, then product cards, then entry paths |
| Menu harms existing money page SEO | SEO + Frontend | Preserve links to `/offer/`, `/calculator/`, `/price/`, `/aiagents/` or approved replacements |
| CTA context breaks form | Backend + QA | Keep existing `data-tacticum-form` and endpoint behavior |
| Product claims not approved | PM | Use safe generic wording and placeholders |

## Sprint Review

### Done

- 01.06.2026 first implementation slice:
  - homepage hero reframed to ecosystem positioning;
  - first screen links to `/platform/`, `/agents/`, `/dev/`, `/forum/`;
  - ecosystem map added under hero;
  - commercial entry paths `/offer/`, `/services/`, `/price/`, `/aiagents/` preserved;
  - `home-cta` context updated through existing `tacticum:lead.cta`;
  - header/footer product navigation implemented in Product layer MVP.
- 01.06.2026 homepage product fit matrix slice:
  - homepage now includes a decision-support block that maps buyer situations to Platform, Agents, Dev or Forum;
  - commercial entry paths remain separate and visible after the product fit matrix;
  - no form, REST, analytics, URL or upstream behavior changed.

### Not Done

- Desktop/mobile visual smoke against the changed homepage still requires local Bitrix/PHP runtime or post-deploy smoke.

### Follow-Up

- Decide whether to add a separate `/products/` catalog later or keep `/platform/` as the product-menu landing.
- Continue with `/aiagents/` vs `/agents/` canonical/migration decision before SEO consolidation.
