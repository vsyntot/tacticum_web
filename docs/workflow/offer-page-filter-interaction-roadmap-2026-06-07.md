# Offer Page Filter Interaction Roadmap — 2026-06-07

Дата: 07.06.2026

Статус: production-validated for current V1 implementation on 07.06.2026; only `OFFER-FILTER-012` remains accepted-monitor for future performance/partial-endpoint decisions.
Scope: `/offer/` filter and quick-entry interaction, active state, applied summary, progressive enhancement, browser history, accessibility and SEO-preserving filtered URLs. No taxonomy source, Bitrix schema, offer detail, sitemap, calculator, forms or analytics changes are included.

## Purpose

Этот roadmap задает порядок закрытия `OFFER-FILTER-*` gaps. Он отделяет UX/state проблемы фильтрации от уже закрытого `OFFER-TAX-*` taxonomy/source-switch трека.

## Source Register

All local IDs below come from:

- `docs/workflow/offer-page-filter-interaction-challenge-gap-analysis-2026-06-07.md`

If implementation introduces an ID not present in the source register, update the source register before coding.

## Execution Principles

1. Keep URL as source of truth.
2. Keep no-JS server-rendered fallback.
3. Make selected state visible before adding AJAX.
4. Do not change canonical/noindex behavior.
5. Treat quick entries as controls, not passive tags.
6. Add accessible loading/focus/result announcements with any asynchronous update.
7. Verify mobile and Back/Forward behavior before production closure.

## Phase 0 — Documentation Adoption

Goal: make the challenge routable for owners and future implementation.

| Work | Covered IDs | Owners | Output |
|---|---|---|---|
| Link docs from workflow index/current/gap docs | `OFFER-FILTER-001` - `OFFER-FILTER-012` | PM + Codex | Workflow docs reference this package. |
| Confirm lane and owners | all | PM + UX + QA | Tracker issues use `OFFER-FILTER-*` and `OFFER-FILTER-WP-*`. |
| Confirm no taxonomy/schema scope | all | Architect + Backend + Content | This package does not alter `OFFER-TAX-*` source model. |

Exit criteria:

- Documents are discoverable.
- Owner groups and gates are explicit.
- No implementation is implied by docs-only adoption.

Status 07.06.2026: completed. Workflow docs now reference the package and implementation scope stayed outside taxonomy/schema/routes/canonical changes.

## Phase 1 — Fast Fix: Visible State And Fallback Context

Goal: improve user clarity without introducing AJAX complexity.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Active quick chips | `OFFER-FILTER-002` | Fast Fix | UX + Frontend + QA | Selected sector/scenario quick entry has clear active visual state and semantic attribute. |
| Applied-filter summary | `OFFER-FILTER-003` | Fast Fix | UX + Frontend + Content + QA | Active filters render as readable removable chips near result count. |
| Context retention fallback | `OFFER-FILTER-001` | Fast Fix | Frontend + QA + SEO | Full reload path lands near catalog/results, not visually detached at top. |
| Quick-entry semantics copy | `OFFER-FILTER-004` | Fast Fix or owner decision | PM + UX + Content + SEO | Decide whether quick entry replaces or combines filters; UI copy matches behavior. |

Exit criteria:

- Filtered URL shows active quick chip when matching sector/scenario is selected.
- User can understand selected filters without opening native selects.
- User can remove one selected filter without rebuilding the full form manually.
- No route, SEO or taxonomy behavior changes.

Status 07.06.2026: closed for current V1 scope. Quick chips expose active state, summary chips provide individual removal, reset/full reload paths target `#offer-catalog`, and quick-entry copy explicitly says the quick entry resets other filters. Production rendered hygiene and browser smoke passed.

Suggested verification:

```bash
git diff --check
php -l local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php
php -l local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php
npm run content:public-hygiene:check
npm run seo:check
```

Post-deploy smoke:

```bash
npm run content:public-cache-clear
npm run content:public-hygiene:rendered:prod
npm run seo:check:prod
```

## Phase 2 — Progressive AJAX Enhancement

Goal: make filtering feel immediate while preserving URL and fallback behavior.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Same-URL fetch/replace | `OFFER-FILTER-005`, `OFFER-FILTER-012` | Full Feature | Frontend + Backend + Architect | JS fetches the selected URL and replaces only catalog interaction subtree. |
| History integration | `OFFER-FILTER-006` | Full Feature | Frontend + QA + SEO | `pushState`/`popstate` preserve shareable URLs and Back/Forward. |
| Pagination integration | `OFFER-FILTER-010` | Full Feature | Frontend + QA | Pagination links use same enhancement path and remain no-JS compatible. |
| Accessibility states | `OFFER-FILTER-007` | Full Feature | UX + Frontend + QA | Loading state, focus target and `aria-live` result announcements exist. |

Exit criteria:

- Clicking quick chip updates result area without full reload when JS is enabled.
- Form submit updates URL and results without full reload when JS is enabled.
- Browser Back/Forward restores URL, selected filters, result count and pagination.
- Disabling JS still works through links/form.
- Failed fetch degrades to normal navigation or shows a safe retry state.

Status 07.06.2026: closed for current V1 scope. Component JS intercepts only catalog-scoped links/form, fetches the same server URL, replaces the catalog root, updates head canonical/robots/title from the fetched document and uses `pushState`/`popstate`. Production browser smoke confirms quick apply, form submit, pagination and Back/Forward.

Suggested verification:

```bash
git diff --check
npm run js:check
npm run bitrix:check
npm run seo:check
```

Add targeted browser smoke for:

- quick chip apply;
- select submit;
- individual chip remove;
- reset all;
- pagination next/back;
- browser Back/Forward;
- JS disabled fallback if tooling supports it.

## Phase 3 — Mobile, Guards And Release Evidence

Goal: close production risk and prevent regression.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Mobile state visibility | `OFFER-FILTER-008` | Full Feature | UX + Frontend + QA | Active filters/result count are visible and usable on mobile after apply. |
| SEO preservation guard | `OFFER-FILTER-009` | Full Feature / QA guard | SEO + QA + Frontend | Filtered URLs keep canonical `/offer/`, `noindex,follow` and no sitemap expansion. |
| Interaction guard coverage | `OFFER-FILTER-011` | QA guard | QA + Frontend | Browser/source smoke catches missing active summary and broken URL/history. |
| Performance monitor | `OFFER-FILTER-012` | accepted-monitor | Architect + Frontend + Backend | Revisit partial endpoint only if same-URL HTML fetch is too heavy. |

Exit criteria:

- Production smoke passes for desktop and mobile.
- Rendered hygiene and SEO checks pass.
- Manual or automated browser evidence is attached to release notes.
- Rollback is simple: JS enhancement can be disabled while server-rendered filters keep working.

Status 07.06.2026: closed for current V1 source/browser scope. Source hygiene guard asserts the selected-state summary, AJAX/history selectors and loading/mobile style contract; production rendered hygiene, SEO and desktop/mobile browser smoke passed. `OFFER-FILTER-012` remains accepted-monitor for future performance/partial-endpoint review.

## Non-Goals

- No taxonomy source changes.
- No new Bitrix iblock/schema.
- No offer detail routing changes.
- No sitemap/canonical/indexability change.
- No calculator or lead form payload changes.
- No analytics event taxonomy change unless opened as a separate issue.

## Verification Matrix

| Change Type | Required Checks |
|---|---|
| PHP template state UI | `php -l`, `bitrix:check`, `content:public-hygiene:check`, `seo:check` |
| CSS active/loading/mobile UI | CSS syntax/checks, visual smoke if layout changes |
| JS progressive enhancement | JS syntax/checks, browser smoke for filters/history/pagination |
| SEO-sensitive filter URL behavior | `seo:check`, `seo:check:prod`, filtered URL rendered head smoke |
| Production deploy | cache clear, rendered hygiene, browser smoke on `/offer/` and representative filtered URL |

## Related Documents

- `docs/workflow/offer-page-filter-interaction-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/offer-page-filter-interaction-issue-backlog-2026-06-07.md`
- `docs/workflow/plans/2026-06-07-offer-filter-interaction-documentation.md`
- `docs/workflow/plans/2026-06-07-offer-filter-interaction-implementation.md`
- `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`
