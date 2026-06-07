# Offer Page Taxonomy / Presets Decision — 2026-06-07

Дата: 07.06.2026
Статус: architecture/product decision proposal, not owner approval and not an ADR.
Scope: `/offer/` public filters, quick entries/presets and future taxonomy governance.

## Decision Question

Should `/offer/` filters and presets move into Bitrix iblocks?

## Recommended Decision

Move governed taxonomy and presets into Bitrix or an equivalent governed content model, but do not move runtime counts and result availability into editor-managed rows.

In practical terms:

- yes: public labels, stable codes, aliases, sort order, active/hidden status, featured quick entries, optional product relation;
- maybe: budget bucket thresholds, if Sales/PM need editor-managed ranges;
- no: counts, result lists, actual offer item membership and SEO indexability state.

## Rationale

Current implementation derives public filters from active offer items. That part is good because users see categories that actually have examples.

The weak part is that raw generated `RESPONSE` labels become public vocabulary and slugs. This creates governance risks:

- label edits can create URL churn;
- duplicate variants can appear as separate filters;
- synthetic seed dictionaries become product language;
- quick entries are arbitrary first-8 choices rather than PM/UX decisions;
- public Russian-first language is not enforceable at taxonomy level.

Therefore the target model should separate raw data from public meaning.

## Proposed Model

### `offer_taxonomy_terms`

One iblock is enough unless Bitrix admin UX requires separate iblocks per dimension.

Recommended fields/properties:

| Field | Purpose |
|---|---|
| `DIMENSION` | `sector`, `scenario`, `phase`, `budget`. |
| `CODE` | Stable canonical public code/slug. |
| `PUBLIC_LABEL` | Main Russian-first public label. |
| `SHORT_LABEL` | Optional label for chips/quick entries. |
| `ALIASES` | Raw labels from offer `RESPONSE`, seed generator and legacy variants. |
| `SORT` | Public option order. |
| `ACTIVE` | Whether the term can render publicly. |
| `FEATURED` | Whether the term can appear in quick entries. |
| `PRODUCT_FAMILY` | Optional relation to `platform`, `agents`, `dev`, `forum`. |
| `DESCRIPTION` | Optional future landing-page intro; do not use for indexable pages without SEO approval. |
| `BUDGET_MIN` / `BUDGET_MAX` | Only for `budget` dimension if budget buckets move to Bitrix. |

### `offer_filter_presets` Optional

Use this only if quick entries need combinations rather than single terms.

Recommended fields/properties:

| Field | Purpose |
|---|---|
| `TITLE` | Public preset label. |
| `FILTER_JSON` or typed relation fields | `sector`, `scenario`, `budget`, `phase`, query/sort if approved. |
| `SORT` | Public order. |
| `ACTIVE` | Render switch. |
| `PAGE_CONTEXT` | Optional: `offer_top`, `offer_empty_state`, etc. |
| `OWNER_NOTE` | Internal note; must not render publicly. |

Prefer featured taxonomy terms first. Add separate presets only when combinations are needed.

## Runtime Rules

1. `CatalogMapper` reads raw offer `RESPONSE` as today.
2. A taxonomy normalizer maps raw sector/scenario/phase/budget to canonical term code using aliases.
3. `CatalogFilters::options()` computes counts from active offer items, then decorates options with approved public labels/order/featured flags.
4. Unknown raw values are reported by checker and either hidden or mapped to approved fallback, not silently published as new public taxonomy.
5. Quick entries render from `FEATURED` terms or approved preset rows, not from first N sorted options.
6. Filter URLs continue using stable canonical codes.
7. Filtered pages stay `noindex,follow` with canonical `/offer/` unless SEO approves a separate landing-page project.

## Cache And Rollback

Target implementation should include:

- config key for taxonomy source mode, e.g. `offer.taxonomy_source=fallback|bitrix`;
- config registry keys for any new iblocks;
- managed-cache tags for offer iblock and taxonomy/preset iblock changes;
- cache clear command or extension of existing public/offer cache clear path;
- fallback taxonomy generated from current runtime labels during rollout;
- strict checker before disabling fallback.

Rollback should be source-mode switch back to fallback/PHP-derived taxonomy plus cache clear, not data deletion.

## SEO Position

Current SEO behavior is safe:

- base `/offer/` is indexable;
- filtered `/offer/catalog/.../` URLs are `noindex,follow` and canonical to `/offer/`;
- offer detail pages remain separate detail URLs/sitemap entries.

Taxonomy governance does not itself justify indexable filtered pages. Indexable sector/scenario pages require a separate SEO/content decision with unique copy, sitemap rules and proof that pages are not thin/duplicative.

## ADR Gate

ADR is required if implementation adds:

- new iblock(s);
- source-mode config and fallback/finalize pattern;
- managed-cache changes tied to new taxonomy tags;
- reusable content-storage pattern that other domains may copy.

ADR is not required for the limited Fast Fix path: budget formatting, temporary approved quick list, or a small label normalization shim without new schema/source switch.

## Non-Goals

- Do not rewrite offer detail sales copy here.
- Do not change calculator logic.
- Do not change lead forms, hidden fields, CRM/upstream payloads or analytics events.
- Do not make filter pages indexable.
- Do not turn synthetic examples into customer proof.
- Do not store counts manually in Bitrix.

## Acceptance Criteria For Future Implementation

- Public filters use approved Russian-first labels and stable canonical codes.
- Counts are derived from active offer items.
- Featured quick entries are owner-approved and ordered.
- Unknown raw labels are caught before release.
- Duplicate slugs/codes are rejected.
- Budget display is public-readable.
- SEO canonical/noindex behavior is unchanged unless separately approved.
- Cache clear and rollback are documented and tested.

## Related Documents

- `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/offer-page-taxonomy-presets-roadmap-2026-06-07.md`
- `docs/workflow/offer-page-taxonomy-presets-issue-backlog-2026-06-07.md`
- `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`
- `docs/workflow/product-taxonomy-seo-packaging-decision-2026-06-04.md`
