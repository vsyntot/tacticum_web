# Offer Page Filter Interaction Challenge Gap Analysis — 2026-06-07

Дата: 07.06.2026

Статус: production-validated for the current V1 UX/progressive-enhancement slice. No routes, canonical/noindex, Bitrix schema, taxonomy labels, offer detail, calculator or form payload changes are approved by this document.
Workflow lane: Full Feature discovery / UX documentation, with Fast Fix candidates.
Scope: `/offer/` catalog page `https://tacticum.ru/offer/`, quick entries, filter form, result state, pagination, progressive enhancement, URL/history behavior, accessibility and SEO preservation.

## Purpose

Этот документ фиксирует результаты UX/UI challenge по взаимодействию с фильтрами и пресетами на странице `Примеры расчетов`.

Главный вывод: проблема не в самом факте server-rendered фильтрации. Проблема в том, что пользовательское состояние каталога плохо выражено: выбор перезагружает страницу, пользователь возвращается наверх, быстрые входы не показывают активность, а примененные фильтры не собраны в явный summary.

## Audit Method

- Reviewed user-provided screenshot of `/offer/` filter area on 07.06.2026.
- Fetched rendered production `/offer/` and `/offer/catalog/sector/meditsina/` on 07.06.2026.
- Reviewed local component templates and offer URL/SEO logic under `local/components/tacticum/offer.catalog/*` and `local/lib/Tacticum/Offer/*`.
- Initial challenge did not use Bitrix admin access and did not change production content, cache, routes or assets.
- Local implementation on 07.06.2026 changed only offer catalog template assets/markup, component-owned CSS/JS and source hygiene guard coverage.

## Source Evidence

| Source | Signal |
|---|---|
| User screenshot, 07.06.2026 | Quick entries and filter form are visible in the catalog area; chips look passive; no applied-filter summary is visible. |
| Rendered `/offer/`, 07.06.2026 | Quick entries are rendered as links to `/offer/catalog/...`; filter form submits to `/offer/`; result count shows `Показано 1-24 из 1 118`. |
| Rendered `/offer/catalog/sector/meditsina/`, 07.06.2026 | Sector select has `meditsina` selected and result count is reduced, but quick chip `медицина` remains visually inactive. |
| `local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php:20` | Sector quick entries are normal `<a>` links to filter URLs. |
| `local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php:36` | Scenario quick entries are normal `<a>` links to filter URLs. |
| `local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php:3` | Filter form is a normal GET form with `action="/offer/"`. |
| `local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php:55` - `:60` | Result count is shown, but it does not explain which filters are active. |
| `local/components/tacticum/offer.catalog/templates/.default/parts/filter-form.php:63` - `:67` | `Сбросить` appears only as a full reset when any filter is active; there are no individual remove chips. |
| `local/lib/Tacticum/Offer/CatalogFilters.php:165` - `:191` | Pretty URL generation already provides shareable `/offer/catalog/...` states. |
| `local/lib/Tacticum/Offer/Page/Response.php:99` - `:103` | Filtered states keep canonical `/offer/` and `noindex,follow`; SEO posture is intentionally cautious. |

## Challenge Verdict

The current implementation is robust as a fallback, but weak as an interaction model.

A full SPA rewrite would be the wrong response. `/offer/` already has a valuable URL and SEO contract: shareable pretty URLs, server-rendered fallback, canonical `/offer/`, `noindex,follow` for filtered states and normal pagination. The target should be progressive enhancement: preserve the server URL model while making filter state feel immediate, visible and reversible.

## Target Interaction Role For `/offer/`

Recommended role:

> `/offer/` filters are an orientation control for narrowing estimate examples. Users should always understand what is selected, how many examples match and how to remove or share the current state.

It should not behave like:

- a full SPA with hidden client-only state;
- a search page that discards shareable URL/history;
- a passive tag cloud where links look like metadata rather than controls;
- a form flow where every small choice sends the user back to the top of the page.

## Interaction Principles

1. URL remains the source of truth for filter state.
2. Server-rendered behavior remains the fallback.
3. Progressive enhancement may fetch and replace the catalog area, but must update `history.pushState`.
4. Active state must be visible without opening a select.
5. Every active filter needs a one-click removal path.
6. Quick entries must clearly communicate whether they replace current filters or combine with them.
7. Filtered URLs must keep current canonical/noindex rules unless SEO approves a separate landing-page strategy.
8. Loading and result updates must be accessible via focus management and `aria-live`.

## Statuses

| Status | Meaning |
|---|---|
| `open` | Confirmed gap, needs task or owner decision. |
| `blocked` | Requires PM/UX/SEO/Architect/QA approval before implementation. |
| `implemented-local` | Implemented and locally checked, but not production-closed until deploy/cache/rendered/browser evidence exists. |
| `accepted-monitor` | Current risk is accepted and must be monitored on future changes. |
| `closed` | Implementation, local checks and production evidence exist for the stated scope. |

## Complete Gap Register

| ID | Status | Priority | Area | Gap | Required Task | Existing Gap Mapping | Owner Group | Gates / Evidence |
|---|---|---:|---|---|---|---|---|---|
| `OFFER-FILTER-001` | closed | P1 | Context retention | Applying quick entry/filter performs full navigation and leaves the user at the top of the page, away from the catalog area. | Preserve context after apply through anchor/scroll fallback and progressive AJAX replacement. | `UX-010`, `REL-002`, `CMP-008` | UX + Frontend + QA | Production browser smoke confirms quick apply, reset and pagination keep catalog context. |
| `OFFER-FILTER-002` | closed | P1 | Active state | Quick chips do not visually indicate the selected sector/scenario. | Add active styles, semantic attributes and current-state labels for selected quick entries. | `UX-002`, `UI-002`, `A11Y-001` | UX + Frontend + QA | Production browser smoke confirms active quick chip with `aria-current` after quick apply. |
| `OFFER-FILTER-003` | closed | P1 | Applied summary | Result count exists, but applied filters are not summarized as readable removable chips. | Add applied-filter summary with individual remove actions and full reset. | `UX-010`, `CLS-002`, `REL-002` | UX + Frontend + Content + QA | Production browser smoke confirms applied summary, individual remove and reset. |
| `OFFER-FILTER-004` | closed | P1 | Quick-entry semantics | Quick entries currently replace other dimensions, but the UI does not explain this. | Decide and encode behavior: replace-preset vs additive filter; update labels/copy accordingly. | `UX-002`, `CONTENT-004`, `OFFER-TAX-002` | PM + UX + Content + SEO | V1 replacement semantics are encoded in UI copy; reopen only if additive behavior is desired. |
| `OFFER-FILTER-005` | closed | P2 | Progressive enhancement | There is no AJAX layer for filter submit, quick links or pagination. | Add JS enhancement that fetches same URL, replaces catalog subtree and updates history. | `CMP-008`, `STACK-003`, `REL-002` | Frontend + Backend + QA | Production browser smoke confirms same-URL quick/filter/pagination updates. |
| `OFFER-FILTER-006` | closed | P2 | Browser history | A future AJAX implementation could break Back/Forward if state is not tied to URL. | Use `history.pushState`/`popstate`; always fetch/render from canonical pretty URL. | `SEO-009`, `REL-002`, `STACK-005` | Frontend + QA + SEO | Production browser smoke confirms Back/Forward restore offer catalog states. |
| `OFFER-FILTER-007` | closed | P2 | Accessibility | Filter updates have no loading state, focus management or `aria-live` result announcement. | Add accessible busy state, result-count live region and deterministic focus target. | `A11Y-001`, `UX-010`, `CMP-008` | UX + Frontend + QA | Production browser smoke confirms status `aria-live` and focus target after update. |
| `OFFER-FILTER-008` | closed | P2 | Mobile UX | On mobile, full reload and long page structure make filtering feel especially lossy. | Add mobile-safe sticky/compact applied-state area or scroll positioning after apply. | `UX-006`, `UI-004`, `REL-002` | UX + Frontend + QA | Production browser smoke passed on desktop and mobile viewports. |
| `OFFER-FILTER-009` | closed | P1 | SEO contract | AJAX can accidentally create client-only filtered states or change canonical/indexability behavior. | Preserve current server URLs, canonical `/offer/`, `noindex,follow` and sitemap behavior. | `SEO-009`, `OFFER-TAX-007`, `REL-002` | SEO + Architect + Frontend + QA | Production `seo:check:prod` and rendered hygiene passed after deploy/cache clear. |
| `OFFER-FILTER-010` | closed | P2 | Pagination integration | Pagination is part of filter state and must not regress under AJAX or active chips. | Include pagination links in the same enhancement path and reset page on filter changes. | `REL-002`, `STACK-005`, `UX-010` | Frontend + QA | Production browser smoke confirms pagination AJAX and Back/Forward. |
| `OFFER-FILTER-011` | closed | P2 | Guard coverage | Existing hygiene/SEO checks do not assert active chip, applied summary or no-JS fallback behavior. | Add source/browser smoke scope for selected-state UI and URL/history behavior. | `REL-002`, `STACK-005`, `CMP-008` | QA + Frontend + SEO | Source guard and new `browser:smoke:offer` cover the interaction contract. |
| `OFFER-FILTER-012` | accepted-monitor | P3 | Response strategy | Fetching full HTML and replacing catalog is acceptable now, but may need a partial endpoint later. | Start with same-URL HTML fetch; revisit partial endpoint only if payload/performance becomes an issue. | `BPC-ARCH-003`, `STACK-005` | Architect + Frontend + Backend | Performance smoke after implementation; no new API unless justified. |

## Minimum Closure Bundles

### Bundle A — State Visibility Fast Fix

Fix first because it improves UX without changing architecture:

- `OFFER-FILTER-002` active quick chips;
- `OFFER-FILTER-003` applied-filter summary;
- `OFFER-FILTER-001` anchor/scroll fallback after normal navigation;
- `OFFER-FILTER-004` explicit quick-entry semantics copy or owner decision.

### Bundle B — Progressive AJAX Enhancement

Only after state visibility is clear:

- `OFFER-FILTER-005` same-URL fetch/replace;
- `OFFER-FILTER-006` history Back/Forward;
- `OFFER-FILTER-007` loading/focus/aria-live;
- `OFFER-FILTER-010` pagination integration.

### Bundle C — Mobile And Guard Hardening

Before production closure:

- `OFFER-FILTER-008` mobile behavior;
- `OFFER-FILTER-009` SEO preservation;
- `OFFER-FILTER-011` browser/source guard coverage;
- `OFFER-FILTER-012` monitor performance and response strategy.

## Do Not Start Without Gates

- Do not replace server-rendered URLs with client-only state.
- Do not make filtered URLs indexable in this UX task.
- Do not change offer detail routes, sitemap or calculator/lead form contracts.
- Do not add a new JSON API unless full-HTML progressive enhancement is proven insufficient.
- Do not hide the submit button until auto-apply, loading, keyboard and screen-reader behavior are tested.
- Do not change taxonomy labels/featured terms in this package; that belongs to `OFFER-TAX-*`.
- Do not hardcode new Bitrix IDs or touch `bitrix/`.

## Suggested Return Path

1. Implement server-rendered active-state UI and applied-filter summary first.
2. Keep current GET links/form as fallback and add scroll/anchor behavior for full reloads.
3. Add progressive enhancement that fetches the same URL and replaces only the catalog area.
4. Preserve URL/history and current SEO rules.
5. Add browser smoke for quick chip, select submit, reset, pagination, Back/Forward and no-JS fallback.

## Local Implementation Evidence — 07.06.2026

Implemented slice:

- component-owned CSS/JS assets connected from `offer.catalog` template, not inline;
- `#offer-catalog` anchor fallback added to quick entries, form action, reset links and pagination;
- quick sector/scenario entries show active visual state, check marker and `aria-current` when selected;
- applied-filter summary renders readable Russian chips for search, sector, scenario, budget, phase and non-default sort;
- individual remove links and full reset preserve server-rendered URL fallback;
- progressive enhancement intercepts only catalog-scoped links/form, fetches the same `/offer/` URL, replaces catalog root, updates browser history and handles `popstate`;
- loading/focus/accessibility state uses `aria-busy`, `aria-live`, deterministic result-count focus and mobile-aware loading feedback;
- source hygiene guard now fails if the active state, summary, catalog selectors, AJAX/history contract or loading styles are removed.

Local checks executed before production deploy:

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

## Production Closure Evidence — 07.06.2026

Production checks after deploy/cache clear:

- `npm run content:public-cache-clear` completed on production.
- `npm run content:public-hygiene:rendered:prod` passed for 13 public pages, including `/offer/`.
- `npm run content:public-hygiene:rendered:prod:json` passed at `2026-06-07T15:05:42Z` with `pages_checked=13`, `issues_found=0`, `/offer/ ok=true`.
- `npm run seo:check:prod` passed.
- `npm run browser:smoke:offer` passed locally against `https://tacticum.ru/offer/` after deploy: desktop and mobile status `200`, runtime errors `0`, SEO ok, action errors `0`.
- Browser smoke manifest: `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-07T15-08-08-184Z/manifest.json`.
- Manifest action evidence: `offer filter interaction ok quick, summary, form, pagination, history` for both desktop and mobile.

Repeatable post-deploy command set:

```bash
npm run content:public-cache-clear
npm run content:public-hygiene:rendered:prod
npm run content:public-hygiene:rendered:prod:json
npm run seo:check:prod
npm run browser:smoke:offer
```

## Related Documents

- `docs/workflow/offer-page-filter-interaction-roadmap-2026-06-07.md`
- `docs/workflow/offer-page-filter-interaction-issue-backlog-2026-06-07.md`
- `docs/workflow/plans/2026-06-07-offer-filter-interaction-documentation.md`
- `docs/workflow/plans/2026-06-07-offer-filter-interaction-implementation.md`
- `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/offer-page-taxonomy-presets-roadmap-2026-06-07.md`
- `docs/adr/ADR-012-offer-taxonomy-presets-bitrix-model.md`
- `docs/workflow/gap-analysis.md`
- `docs/workflow/current-state.md`
