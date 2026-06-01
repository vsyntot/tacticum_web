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
| S03-001 | Decide content model: static pages, local components, new iblocks, or mixed | Tech Lead + PM | P1 | in-progress-first-slice |
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

1. Development knows whether product pages are static entries or content-backed; current first slice uses shared Git data files and keeps Bitrix/hybrid as an explicit later decision.
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

- 01.06.2026 first implementation slice:
  - product page entries `/platform/`, `/agents/`, `/dev/`, `/forum/` no longer contain large product arrays;
  - core product data moved to `local/php_interface/include/product_data/*.php`;
  - `tacticum_product_page_data(...)` in `local/php_interface/include/product_page.php` loads allowlisted product data files;
  - `tools/seo-check.mjs` validates thin page entries, shared data files, data/schema/render ordering, CTA scenario options, fit guide, procurement, use-case anatomy, comparison, rollout, proof and safe schema fields.
- 01.06.2026 renderer boundary slice:
  - `local/php_interface/include/product_page.php` reduced to bootstrap/helpers/data/schema and block includes;
  - visual render functions moved into `local/php_interface/include/product_page_blocks/*.php`;
  - `tools/seo-check.mjs` validates that render functions stay in block files and product page bootstrap loads the block taxonomy.
- 01.06.2026 lead qualification fallback slice:
  - `/local/rest/tacticum_form.php` builds canonical lead profile from existing `lead_*` fields: `product_interest`, `use_case_interest`, `deployment_interest`, funnel, CTA, budget, timeline, industry and offer context;
  - current upstream response shape and endpoint payload remain unchanged: canonical fields are rendered into the existing `task` fallback only;
  - `tools/seo-check.mjs` guards the canonical profile and blocks accidental top-level upstream forwarding of unapproved structured fields.
- 01.06.2026 product analytics taxonomy slice:
  - `analytics.js` emits `tacticum_product_view` and `tacticum_product_cta_click` for product router/product pages;
  - `forms.js` emits product-specific form submit/success/error mirrors when a form has allowlisted product context;
  - product analytics params are allowlisted to `product`, `page_role`, controlled `scenario`, `form_id`, endpoint/status/code and `page_path`; no budget/timeline/offer/free-text/contact fields are sent.
- 01.06.2026 product block locator slice:
  - product renderer partials expose stable `data-product-block` markers for `hero`, `fit-guide`, `content-section`, `architecture`, `use-cases`, `comparison`, `procurement`, `rollout`, `proof`, `faq` and `lead-cta`;
  - `tools/seo-check.mjs` guards the locator taxonomy so design/QA/LLM-assisted refactors can target AS IS blocks without relying on CSS classes or text copy;
  - `tools/visual-smoke.mjs` records rendered product block inventory in manifests, and release sign-off validation fails product SEO evidence if required blocks are missing.
- 01.06.2026 lightweight preview workflow slice:
  - `tools/visual-smoke.mjs` supports `TACTICUM_CAPTURE_PRODUCT_BLOCKS=1` and saves per-block product screenshots into `product-blocks/*.png`;
  - `package.json` exposes `npm run product:block-previews` and `npm run product:block-previews:prod`;
  - workflow documented in `docs/workflow/product-block-preview-workflow.md`.

### Not Done

- Final CMS/hybrid ownership decision remains open if content team needs Bitrix-admin editing for product proof, cases, FAQ or releases.
- Further split from PHP partials into Bitrix local components or Storybook-like isolated previews remains open only if design/QA needs component-level previews; current lightweight workflow is rendered-page screenshot preview.
- Structured CRM/upstream lead fields remain open; current state is approved-fallback candidate, not final upstream integration.
- Product funnel Метрика goals and release evidence remain open.

### Follow-Up

- Decide whether shared Git data is enough for first release.
- Draft ADR only if product data moves into Bitrix/hybrid content model or renderer partials become Bitrix local components/shared preview architecture.
- Decide whether canonical `task` fallback is acceptable for first release or whether CRM/upstream can accept structured fields in staging.
- Confirm Метрика goals and QA evidence for product funnel events.
- Use `product:block-previews` for AS IS design handoff and revisit isolated component previews only if TO BE design-system workflow requires them.
