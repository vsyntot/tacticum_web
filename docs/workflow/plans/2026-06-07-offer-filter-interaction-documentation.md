# Codex Plan: Offer Filter Interaction Challenge Documentation

Issue: docs-only challenge package
Gap ID: `OFFER-FILTER-001` - `OFFER-FILTER-012`
Workflow lane: Full Feature discovery / documentation
Owner agent: Codex
Date: 2026-06-07

## Goal

Document UX/UI gaps for `/offer/` filter and quick-entry interaction so future implementation can improve state visibility, context retention and progressive filtering without breaking URL, SEO or no-JS behavior.

## Non-Goals

- No PHP, JS or CSS implementation in this docs-only task.
- No taxonomy/source-switch changes; `OFFER-TAX-*` remains separate.
- No Bitrix schema/admin data changes.
- No canonical/noindex/sitemap behavior changes.
- No calculator, offer detail, lead form, CRM/upstream or analytics contract changes.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`
- [x] `local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php`
- [x] `local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php`
- [x] `local/lib/Tacticum/Offer/CatalogFilters.php`
- [x] `local/lib/Tacticum/Offer/Page/Response.php`

## Current Behavior

- Quick entries are direct links to `/offer/catalog/...`.
- Filter form submits via normal GET.
- Full navigation returns user to top of page context.
- Active selected state is mostly hidden inside native selects.
- Matching quick chips are not visually active.
- Result count exists, but no applied-filter chip summary exists.
- Filtered URLs are intentionally canonical `/offer/` and `noindex,follow`.

## Target Behavior

- Selected state is visible and removable near the results.
- Quick chips show active state when selected.
- Full reload fallback keeps user near catalog/results.
- Progressive JS enhancement may update results without full reload, but URL/history remain authoritative.
- No-JS fallback remains functional.
- SEO rules remain unchanged.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/offer-page-filter-interaction-challenge-gap-analysis-2026-06-07.md` | New source gap register and challenge verdict. |
| `docs/workflow/offer-page-filter-interaction-roadmap-2026-06-07.md` | New phased roadmap for state UI, AJAX enhancement and guards. |
| `docs/workflow/offer-page-filter-interaction-issue-backlog-2026-06-07.md` | New tracker-ready work packages. |
| `docs/workflow/README.md` | Link new documents in workflow index. |
| `docs/workflow/current-state.md` | Add concise current-state snapshot. |
| `docs/workflow/gap-analysis.md` | Add concise gap-analysis entry. |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS через `Asset` in future implementation
- [x] Infoblock IDs через config helper in future implementation
- [x] D7/API style preserved for future shared code
- [x] No POST REST changes in this docs task

## Risks

| Risk | Mitigation |
|---|---|
| SEO regression | Docs require preserving canonical `/offer/`, `noindex,follow` and sitemap behavior. |
| Client-only state | Docs require URL as source of truth and no-JS fallback. |
| Accessibility regression | Docs require loading, focus and `aria-live` acceptance criteria. |
| Scope creep into taxonomy | Docs separate `OFFER-FILTER-*` from `OFFER-TAX-*`. |
| Performance | Docs recommend same-URL HTML fetch first and monitor before adding partial API. |

## Verification

### Automated

```bash
git diff --check
```

### Manual Smoke

- URL: `docs/workflow/*offer-page-filter-interaction*`
- Action: review document links and status language.
- Expected: package is discoverable, docs-only and does not approve runtime changes.

## Rollback

Remove the new docs and index references. No runtime rollback is needed because this is docs-only.

## Docs To Update

- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/README.md`
