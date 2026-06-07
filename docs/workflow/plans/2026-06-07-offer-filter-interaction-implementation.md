# Codex Plan: Offer Filter Interaction Implementation

Issue: `OFFER-FILTER-WP-01`, `OFFER-FILTER-WP-02`, `OFFER-FILTER-WP-03`, `OFFER-FILTER-WP-04`
Gap ID: `OFFER-FILTER-001` - `OFFER-FILTER-012`
Workflow lane: Full Feature with Fast Fix first slice
Owner agent: Codex
Date: 07.06.2026

## Goal

Make `/offer/` filter state visible, reversible and less disruptive while preserving the existing server-rendered URL model, no-JS fallback, canonical/noindex behavior and offer taxonomy source model.

## Non-Goals

- No taxonomy term, featured term or Bitrix schema changes.
- No offer detail, calculator, lead form, CRM/upstream or analytics payload changes.
- No route, sitemap, canonical or robots policy changes.
- No new JSON API or client-only SPA state.
- No inline JS/CSS in public HTML.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/offer-page-filter-interaction-challenge-gap-analysis-2026-06-07.md`
- [x] `docs/workflow/offer-page-filter-interaction-roadmap-2026-06-07.md`
- [x] `docs/workflow/offer-page-filter-interaction-issue-backlog-2026-06-07.md`
- [x] `local/components/tacticum/offer.catalog/templates/.default/*`
- [x] `local/lib/Tacticum/Offer/CatalogFilters.php`
- [x] `local/lib/Tacticum/Offer/Page/Response.php`

## Current Behavior

- Quick entries and pagination are normal links without active selected-state semantics.
- Filter form is a normal GET form and full reload can return the user to the top page context.
- Selected filters are mostly hidden inside native controls.
- Result count does not summarize active filters or provide individual removals.
- There is no progressive enhancement for same-URL catalog updates, history restoration or accessible loading state.

## Target Behavior

- URL remains the source of truth and no-JS fallback remains functional.
- Full reload fallback lands near `#offer-catalog`.
- Selected quick sector/scenario entries are visually and semantically active.
- Active filters render as readable Russian summary chips with individual removal and full reset.
- JS-enhanced controls update only the catalog subtree from the same server-rendered URL and maintain browser history.
- Loading, result updates and focus behavior are accessible and mobile-safe.
- Source hygiene catches regressions in the interaction contract.

## Planned Changes

| File | Change |
|---|---|
| `local/components/tacticum/offer.catalog/templates/.default/template.php` | Connect component CSS/JS, add catalog href helper, build applied-filter summary data. |
| `local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php` | Add catalog root, live region, active quick chip state, anchor fallback and replacement-semantics copy. |
| `local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php` | Add `#offer-catalog` action, applied summary chips, individual remove links, reset link and accessible status. |
| `local/components/tacticum/offer.catalog/templates/.default/parts/pagination.php` | Route pagination through catalog href helper and progressive-enhancement selector. |
| `local/components/tacticum/offer.catalog/templates/.default/script.js` | Add same-URL fetch/replace enhancement, head sync, `pushState`/`popstate`, focus and fallback navigation. |
| `local/components/tacticum/offer.catalog/templates/.default/style.css` | Add scroll-margin, loading state and mobile-safe loading feedback. |
| `tools/public-content-hygiene-check.mjs` | Guard active state, applied summary, progressive selectors, history contract and loading styles. |
| `docs/workflow/*offer-page-filter-interaction*` | Record local implementation status and remaining production/browser gates. |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS через component template asset connection
- [x] Infoblock IDs are not introduced or hardcoded
- [x] No new D7/Bitrix data writes
- [x] No POST REST changes

## Risks

| Risk | Mitigation |
|---|---|
| SEO regression | Fetch same server URLs; update title/canonical/robots from response; keep `seo:check` and prod SEO smoke mandatory. |
| No-JS regression | Keep links/form as normal GET controls with `#offer-catalog` fallback. |
| Browser history mismatch | Use URL as state, `pushState` on enhanced actions and `popstate` fetch from current URL. |
| Accessibility regression | Add `aria-live`, `aria-busy`, deterministic status focus and disabled submit during load. |
| Cache/deploy stale assets | Require public cache clear and rendered prod smoke after deploy. |
| Performance | Start with same-URL HTML fetch; keep `OFFER-FILTER-012` as accepted-monitor before adding partial endpoint. |

## Verification

### Automated

```bash
php -l local/components/tacticum/offer.catalog/templates/.default/template.php
php -l local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php
php -l local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php
php -l local/components/tacticum/offer.catalog/templates/.default/parts/pagination.php
node --check local/components/tacticum/offer.catalog/templates/.default/script.js
node --check tools/public-content-hygiene-check.mjs
npm run js:check
npm run css:syntax
npm run bitrix:check
npm run content:public-hygiene:self-test
npm run content:public-hygiene:check
npm run content:public-hygiene:rendered:self-test
npm run component:states:check
npm run product:content:safety:check
npm run seo:check
git diff --check
```

### Post-Deploy Smoke

```bash
npm run content:public-cache-clear
npm run content:public-hygiene:rendered:prod
npm run content:public-hygiene:rendered:prod:json
npm run seo:check:prod
```

### Manual / Browser Smoke

- URL: `/offer/`
- Actions: quick chip apply, form submit, individual summary chip remove, reset all, pagination, browser Back/Forward.
- Expected: URL updates, catalog stays near visible context, selected state is obvious, result count updates, canonical/noindex policy remains unchanged.
- Mobile: selected state and result count remain reachable after apply.
- No-JS: links/form still work via server-rendered navigation and `#offer-catalog` fallback.

## Rollback

- Remove/disable `script.js` asset connection to fall back to server-rendered links/form if JS enhancement regresses.
- Revert template/CSS changes if active summary UI regresses.
- Run public cache clear and rendered hygiene after rollback.
- Do not change canonical/noindex as rollback mitigation.

## Docs To Update

- [x] `docs/workflow/offer-page-filter-interaction-challenge-gap-analysis-2026-06-07.md`
- [x] `docs/workflow/offer-page-filter-interaction-roadmap-2026-06-07.md`
- [x] `docs/workflow/offer-page-filter-interaction-issue-backlog-2026-06-07.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/README.md`
