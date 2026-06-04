# Sprint 18 — Taxonomy, SEO And Packaging

Дата формирования: 04.06.2026
Статус: in-progress; decision package draft added
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Roadmap: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`

## Sprint Goal

Утвердить рыночную продуктовую рамку Tacticum: названия, one-liners, boundaries, `/agents/` vs `/aiagents/`, `/price/` framing, packaging language and product SEO metadata.

## Capacity / Constraints

- Production freeze: no runtime changes until SEO/PM decision is approved.
- Known dependencies: Sprint 17 claims/proof outputs, SEO keyword/intent review, Sales approval.
- Agents / roles:
  - PM: taxonomy, packaging and product boundaries;
  - Sales: buying triggers and packaging reality;
  - SEO: canonical, metadata, sitemap and keyword intent;
  - Content: final copy and metadata sheet;
  - UX: route intent and `/price/` framing;
  - Legal: packaging wording and claim constraints.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| S18-001 | `CONTENT-005` Product taxonomy approval | Full Feature | PM + Sales + Architect | P1 | blocked | Taxonomy v1 recommendation drafted; Sales/market validation pending |
| S18-002 | `UX-004` `/agents/` vs `/aiagents/` decision | Full Feature | PM + SEO + Product | P1 | blocked | Draft recommends separate self-canonical routes; SEO/PM approval pending |
| S18-003 | `ARCH-010` Product SEO clusters/canonical validation | Full Feature | SEO + PM | P1 | blocked | SEO/canonical draft added; keyword/intent research pending |
| S18-004 | `UX-005` `/price/` product packaging framing | Full Feature | PM + Sales + UX | P1 | in-progress | Route intent drafted; current team-builder preservation confirmed |
| S18-005 | `CONTENT-001` Public packaging matrix | Full Feature | PM + Sales + Legal + Architect | P1 | blocked | Public/private/blocked packaging matrix drafted; Legal/Sales approval pending |
| S18-006 | `CONTENT-003` Product evidence/content map | Full Feature | Content + SEO + Sales | P1 | in-progress | Evidence map draft added; real content tagging pending |
| S18-007 | `CONTENT-004` Product metadata approval | Full Feature | SEO + Content + PM | P2 | in-progress | Metadata sheet draft added; keyword review and owner approval pending |

## Out Of Scope

- Implementing redirects, canonical changes or sitemap changes before decision approval.
- Publishing pricing/licensing/offers JSON-LD.
- Redesigning `/price/` mobile team-builder; handled in Sprint 20/21.
- Changing lead form payload or CRM routing.
- Publishing proof claims without Sprint 17 evidence.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | Conditional | Required if canonical/redirect model or product information architecture changes as a repeatable rule |
| Design | Yes | `/price/` route intent and packaging framing need UX/design review |
| QA early | Conditional | Required before redirect/canonical/sitemap implementation |
| SEO | Yes | Required for `/agents/`, `/aiagents/`, metadata, sitemap and product clusters |
| Legal/Claims | Yes | Required for packaging and public/private wording |
| Post-deploy smoke | Yes if route/meta changes | `seo:check`, rendered smoke, canonical/sitemap validation |

## Acceptance Criteria

1. Product taxonomy is approved: product names, one-liners, category boundaries and buying triggers.
2. `/agents/` and `/aiagents/` decision is recorded: canonical URL, redirect/no-redirect, compatibility period, sitemap, analytics and copy boundary.
3. Product SEO cluster decision is documented for Platform, Agents, Dev and Forum.
4. `/price/` is reframed as product implementation/package route while preserving team-builder utility and existing conversion flow.
5. Packaging matrix distinguishes public, private/NDA and blocked language for pilot, SaaS, on-prem, hybrid, PAK, support and SLA-like claims.
6. Product evidence map connects product taxonomy with cases/offers/FAQ/services and marks evidence status.
7. Product metadata sheet contains title, description, H1, intro and canonical notes for each product URL.
8. Any future route/meta implementation has explicit QA/SEO smoke scope.

## QA / Smoke Scope

| Scenario | URL/API/Tool | Expected |
|---|---|---|
| Product metadata | `/platform/`, `/agents/`, `/dev/`, `/forum/` | Final title/description/H1/canonical match approved sheet |
| Agents boundary | `/agents/`, `/aiagents/` | Copy and canonical behavior match decision |
| Price route intent | `/price/` | Team-builder still usable; product packaging framing visible if implemented |
| SEO inventory | `npm run seo:check` | No duplicate canonical/sitemap regressions |
| Rendered SEO | `npm run visual:smoke` with SEO expectation | Product schema remains safe; no offers/pricing/review fields |

## Verification

### Automated

```bash
npm run seo:check
npm run bitrix:check
npm run product:gaps:check
```

After implementation/deploy:

```bash
npm run seo:check:prod
npm run product:source:http:prod
npm run release:public-precheck:prod
```

### Manual / Owner Evidence

- PM/Sales taxonomy approval.
- SEO canonical/metadata decision.
- Legal packaging wording approval.
- Content evidence map review.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| `/agents/` redirect breaks existing traffic or lead source | SEO + PM | Compatibility period, analytics check, staged decision |
| `/price/` loses team-builder conversion value | UX + QA | Preserve existing selectors and smoke coverage |
| Packaging promises unsupported deployment/SLA/PAK readiness | PM + Legal | Public/private/blocked packaging matrix |
| Metadata changes create sitemap/canonical drift | SEO + QA | `seo:check` and rendered smoke before deploy |
| Taxonomy sounds coherent internally but not to buyers | PM + Sales | Sales validation with buying triggers |

## Definition Of Done

- `CONTENT-001`, `CONTENT-003`, `CONTENT-004`, `CONTENT-005`, `UX-004`, `UX-005`, `ARCH-010` have approved decision/output or explicit owner-blocked status.
- Route/canonical changes are not implemented until SEO decision is approved.
- `/price/` framing preserves team-builder behavior.
- Metadata and packaging copy do not contradict Sprint 17 claims/proof rules.

## Sprint Review

### Done

- Added `docs/workflow/product-taxonomy-seo-packaging-decision-2026-06-04.md` as Sprint 18 approval package.
- Documented recommended v1 taxonomy: `Tacticum Platform`, `Tacticum Agents`, `Tacticum Dev`, `Tacticum Forum`.
- Documented `/agents/` vs `/aiagents/` draft: keep `/agents/` as product URL and `/aiagents/` as self-canonical compatibility/service route until SEO approval; no redirect/canonical runtime changes.
- Documented `/price/` route intent: team/staffing route for product implementation and delivery stages, not product-license pricing; existing team-builder and staff-order flow must stay preserved.
- Added public/private/blocked packaging safety matrix for pilot, SaaS, on-prem, hybrid, PAK, support and procurement/security wording.
- Added product evidence map draft and evidence status taxonomy.
- Added product metadata sheet draft for `/platform/`, `/agents/`, `/dev/`, `/forum/`, `/aiagents/` and `/price/`.
- Updated workflow docs and source register with Sprint 18 draft status.

### Not Done

- PM/Sales taxonomy approval remains pending.
- SEO keyword/intent validation and `/agents/` vs `/aiagents/` canonical/sitemap decision remain pending.
- Legal/Sales packaging wording approval remains pending.
- Product-specific cases/offers/FAQ/services tagging remains pending.
- No route, canonical, sitemap, title/description/H1 or public runtime copy changes were implemented.

### Follow-Up

- Collect PM/Sales/SEO/Legal approvals against `product-taxonomy-seo-packaging-decision-2026-06-04.md`.
- If SEO approves route/meta changes, implement them in a separate scope with `seo:check`, rendered smoke and post-deploy checks.
- Convert product evidence map draft into Bitrix/content tagging backlog after Content/Sales review.
