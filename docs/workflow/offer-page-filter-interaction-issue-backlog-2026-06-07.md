# Offer Page Filter Interaction Issue Backlog — 2026-06-07

Дата: 07.06.2026

Статус: local implementation backlog state for `offer-page-filter-interaction-challenge-gap-analysis-2026-06-07.md`; production deploy/cache/rendered/browser evidence pending before final closure.
Source register: `docs/workflow/offer-page-filter-interaction-challenge-gap-analysis-2026-06-07.md`
Roadmap: `docs/workflow/offer-page-filter-interaction-roadmap-2026-06-07.md`
Related taxonomy docs: `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`

## Purpose

Этот документ переводит `OFFER-FILTER-*` gaps into backlog-ready work packages. Его можно использовать для ручного создания задач в трекере.

## Start Policy

| Policy | Meaning |
|---|---|
| `fast-fix-allowed` | Можно делать локальный UI/PHP/CSS фикс без нового runtime contract; smoke required. |
| `owner-review-required` | Можно готовить proposal/docs; behavior change waits for PM/UX/Content/SEO owner decision. |
| `design-gate-required` | Нужен UX/UI review перед изменением interaction pattern или mobile behavior. |
| `seo-gate-required` | Нельзя менять canonical/noindex/URL/sitemap без SEO approval. |
| `guard-scope-required` | Нужно добавить или расширить smoke/checker scope до production closure. |
| `progressive-enhancement-required` | JS enhancement must preserve no-JS fallback and same URL contract. |

## Backlog Index

| Issue | Status | Start policy | Priority | Owners | Gap IDs | Objective |
|---|---|---|---:|---|---|---|
| `OFFER-FILTER-WP-01` | implemented-local-prod-pending | `fast-fix-allowed` + `design-gate-required` | P1 | UX + Frontend + QA + Content | `OFFER-FILTER-001`, `OFFER-FILTER-002`, `OFFER-FILTER-003`, `OFFER-FILTER-004` | Make selected filter state visible and reversible without changing architecture. |
| `OFFER-FILTER-WP-02` | implemented-local-prod-pending | `progressive-enhancement-required` + `seo-gate-required` | P1 | Frontend + Backend + Architect + QA + SEO | `OFFER-FILTER-005`, `OFFER-FILTER-006`, `OFFER-FILTER-009`, `OFFER-FILTER-010`, `OFFER-FILTER-012` | Add same-URL AJAX enhancement with history and pagination while preserving URL/SEO fallback. |
| `OFFER-FILTER-WP-03` | implemented-local-guarded-prod-pending | `design-gate-required` + `guard-scope-required` | P2 | UX + Frontend + QA | `OFFER-FILTER-007`, `OFFER-FILTER-008`, `OFFER-FILTER-011` | Harden accessibility, mobile state visibility and regression evidence. |
| `OFFER-FILTER-WP-04` | v1-decision-encoded | `owner-review-required` + `seo-gate-required` | P2 | PM + UX + Content + SEO | `OFFER-FILTER-004`, `OFFER-TAX-008` | Decide whether quick entries replace current filters or combine with product/taxonomy journey. |

## Local Challenge Evidence

| Evidence | Result |
|---|---|
| User screenshot, 07.06.2026 | Quick entries and form are visible, but selected state is not obvious. |
| Rendered `/offer/`, 07.06.2026 | Normal state has no active filters, quick chips are passive gray links and result count is shown. |
| Rendered `/offer/catalog/sector/meditsina/`, 07.06.2026 | Select value is selected and count reduces to 38, but matching quick chip is not visually active. |
| `quick-filters.php` review | Quick entries are direct links to filter URLs. |
| `filter-form.php` review | Form is GET-based and has count/reset, but no applied-filter chip summary. |
| `CatalogFilters::url` review | Pretty URL contract already exists and should be preserved. |
| `Response::applyListSeo` review | Filtered states are canonical `/offer/` and `noindex,follow`; this must not regress. |

## Local Implementation Evidence — 07.06.2026

| Work Package | Local Result | Production Gate Still Required |
|---|---|---|
| `OFFER-FILTER-WP-01` | Active quick chips, `#offer-catalog` fallback, applied summary, individual remove chips and full reset are implemented in `offer.catalog` templates. | Rendered prod smoke and desktop/mobile visual acceptance. |
| `OFFER-FILTER-WP-02` | Same-URL progressive enhancement implemented in component JS with scoped link/form interception, DOM replacement, head sync and history support. | Browser smoke for quick apply, form submit, pagination, Back/Forward and failed-fetch fallback. |
| `OFFER-FILTER-WP-03` | `aria-live`, `aria-busy`, deterministic focus target, mobile loading feedback and source hygiene guard coverage are implemented locally. | Keyboard/screen-reader smoke and mobile viewport evidence. |
| `OFFER-FILTER-WP-04` | V1 behavior keeps quick entries as replacement controls; UI copy states that quick entry resets other filters. | Future owner review only if additive/combined quick-entry behavior is desired. |

Local check set:

```bash
php -l local/components/tacticum/offer.catalog/templates/.default/template.php
php -l local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php
php -l local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php
php -l local/components/tacticum/offer.catalog/templates/.default/parts/pagination.php
node --check local/components/tacticum/offer.catalog/templates/.default/script.js
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

## Issue Details

### OFFER-FILTER-WP-01 — Visible Filter State Fast Fix

Workflow lane: Fast Fix with Design gate.
Priority: P1.

Affected areas:

- `local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php`
- `local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php`
- `local/components/tacticum/offer.catalog/templates/.default/template.php` if helper labels are needed
- component/template CSS if active chip styles are not expressible through existing utilities

Acceptance criteria:

- Selected sector/scenario quick chip is visibly active on matching filtered URL.
- Active quick chip has semantic state (`aria-current`, `aria-pressed` or equivalent pattern selected by QA/UX).
- Applied-filter summary appears near result count when any filter/search/sort is active.
- Summary chips include readable Russian labels and one-click remove links.
- Full reset remains available.
- Existing GET links/form still work without JS.
- Current canonical/noindex behavior is unchanged.

Verification:

```bash
git diff --check
php -l local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php
php -l local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php
npm run bitrix:check
npm run content:public-hygiene:check
npm run seo:check
```

Post-deploy smoke:

```bash
npm run content:public-cache-clear
npm run content:public-hygiene:rendered:prod
npm run seo:check:prod
```

Manual smoke:

- Open `/offer/catalog/sector/meditsina/`; expected active `медицина` chip and summary `Отрасль: медицина`.
- Remove only sector chip; expected URL returns to `/offer/` or remaining filters.
- Select `budget` and `sort`; expected summary shows both and reset all works.

### OFFER-FILTER-WP-02 — Progressive Filter Enhancement

Workflow lane: Full Feature.
Priority: P1.

Affected areas:

- `local/components/tacticum/offer.catalog/templates/.default/*`
- new component JS asset under component template or `local/templates/tacticum/js/`
- `local/templates/tacticum/header.php` / Asset connection if page-level asset is needed
- optional CSS for loading state

Acceptance criteria:

- JS intercepts quick-entry links, filter form submit, reset links and pagination links only inside the offer catalog scope.
- JS fetches the target URL and replaces the catalog interaction/result subtree with server-rendered HTML from the same URL.
- `history.pushState` updates browser URL to target pretty URL.
- `popstate` restores previous filter state and results.
- Failed fetch falls back to normal navigation or safe retry behavior.
- No-JS behavior remains identical to the server-rendered baseline.
- Filtered URLs keep canonical `/offer/`, `noindex,follow` and are not added to sitemap.

Verification:

```bash
git diff --check
npm run js:check
npm run bitrix:check
npm run seo:check
```

Browser smoke should cover:

- quick chip apply without full reload;
- form submit without full reload;
- reset all;
- individual summary chip remove;
- pagination next;
- browser Back/Forward;
- representative mobile viewport.

### OFFER-FILTER-WP-03 — Accessibility, Mobile And Guard Evidence

Workflow lane: Full Feature / QA guard.
Priority: P2.

Affected areas:

- offer catalog template markup
- offer catalog JS/CSS
- visual/browser smoke tooling if extended
- docs/release evidence if new gate is added

Acceptance criteria:

- Result count or status region announces updates through `aria-live`.
- Loading state is visible and does not trap focus.
- After update, focus lands on a deterministic safe target near applied filters/results.
- Mobile viewport shows selected state and result count after apply.
- Browser smoke detects missing active summary and broken history behavior.
- Production release notes include no-PII evidence for `/offer/` and one filtered URL.

Verification:

```bash
git diff --check
npm run js:check
npm run seo:check
```

Post-deploy smoke:

```bash
npm run content:public-cache-clear
npm run content:public-hygiene:rendered:prod
npm run seo:check:prod
```

Add targeted browser/visual smoke command once implementation defines selectors.

### OFFER-FILTER-WP-04 — Quick Entry Behavior Decision

Workflow lane: Full Feature discovery.
Priority: P2.

Affected areas:

- quick-entry UX copy
- future product/taxonomy relation docs
- possible interaction rules for combining filters

Acceptance criteria:

- PM/UX/Content/SEO decide whether quick entries are preset replacement controls or additive filter chips.
- Decision explains behavior for existing search query, sector, scenario, budget, phase and sort.
- Decision does not change taxonomy ownership or product-family relation without linking `OFFER-TAX-WP-06`.
- UI copy and labels match the chosen behavior.

Verification:

- Owner decision recorded in docs or tracker.
- No code change required unless separate implementation issue is opened.

## Release / Rollback Notes

- WP-01 rollback is normal template/CSS rollback; server URL behavior remains unchanged.
- WP-02 rollback should allow disabling/removing JS enhancement while retaining server-rendered form/link behavior.
- Do not deploy WP-02 without production cache clear, rendered hygiene, SEO check and browser smoke.
- If AJAX causes SEO/head regressions, rollback JS enhancement first; do not change canonical/noindex as mitigation.

## Related Documents

- `docs/workflow/offer-page-filter-interaction-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/offer-page-filter-interaction-roadmap-2026-06-07.md`
- `docs/workflow/plans/2026-06-07-offer-filter-interaction-documentation.md`
- `docs/workflow/plans/2026-06-07-offer-filter-interaction-implementation.md`
- `docs/workflow/offer-page-taxonomy-presets-issue-backlog-2026-06-07.md`
