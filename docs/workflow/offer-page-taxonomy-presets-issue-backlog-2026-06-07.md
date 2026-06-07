# Offer Page Taxonomy / Presets Issue Backlog — 2026-06-07

Дата: 07.06.2026
Статус: issue backlog draft; `OFFER-TAX-WP-01` and fast-fix scope of `OFFER-TAX-WP-02` are implemented locally with source/architecture evidence; production evidence and owner-approved durable taxonomy/preset model remain pending.

Source register: `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`
Roadmap: `docs/workflow/offer-page-taxonomy-presets-roadmap-2026-06-07.md`
Decision support: `docs/workflow/offer-page-taxonomy-presets-decision-2026-06-07.md`

## Purpose

Этот документ переводит `OFFER-TAX-*` gaps into backlog-ready work packages. Его можно использовать для ручного создания задач в трекере.

## Start Policy

| Policy | Meaning |
|---|---|
| `fast-fix-allowed` | Можно делать ограниченный публичный фикс без новой схемы и контрактов; smoke required. |
| `owner-review-required` | Можно готовить proposals/docs; implementation waits for PM/Content/Sales/SEO/Architect approval. |
| `adr-gate-required` | Нужен ADR/architecture decision before Bitrix schema/source-switch/runtime pattern change. |
| `content-storage-gate-required` | Нужен approved content-storage model before changing iblocks/source ownership. |
| `seo-gate-required` | Нужно SEO approval before canonical/indexability/sitemap behavior changes. |
| `guard-scope-required` | Нужен checker/smoke scope before or with implementation. |

## Backlog Index

| Issue | Status | Start policy | Priority | Owners | Gap IDs | Objective |
|---|---|---|---:|---|---|---|
| `OFFER-TAX-WP-01` | local-implemented-pending-production | `fast-fix-allowed` + `guard-scope-required` | P1 | Backend/Frontend + QA + Content | `OFFER-TAX-003`, `OFFER-TAX-004` | Fix visible catalog defects: budget formatting and most obvious mixed-language labels. |
| `OFFER-TAX-WP-02` | local-fast-fix-implemented-owner-model-pending | `owner-review-required` or `fast-fix-allowed` depending on implementation | P1 | PM + UX + Content + SEO + Frontend/Backend | `OFFER-TAX-002` | Replace arbitrary first-8 quick entries with curated presets/featured terms. |
| `OFFER-TAX-WP-03` | open | `owner-review-required` + `adr-gate-required` | P1 | Architect + Backend + PM + Content + SEO + Sales | `OFFER-TAX-001`, `OFFER-TAX-005`, `OFFER-TAX-006`, `OFFER-TAX-011` | Approve taxonomy source-of-truth, labels, aliases, budget bucket governance and Bitrix model. |
| `OFFER-TAX-WP-04` | blocked | `adr-gate-required` + `content-storage-gate-required` | P1 | Backend + Architect + QA + DevOps + Content | `OFFER-TAX-001`, `OFFER-TAX-006`, `OFFER-TAX-009` | Implement governed taxonomy runtime with Bitrix source, fallback and derived counts. |
| `OFFER-TAX-WP-05` | local-guard-slice-implemented | `guard-scope-required` + `seo-gate-required` | P2 | QA + SEO + Backend + DevOps | `OFFER-TAX-007`, `OFFER-TAX-009`, `OFFER-TAX-012` | Add taxonomy/content/SEO/cache guards and production evidence path. |
| `OFFER-TAX-WP-06` | open | `owner-review-required` + `seo-gate-required` | P2 | PM + Product + SEO + Content + Backend | `OFFER-TAX-008`, `OFFER-TAX-010` | Decide product-family relation and future landing/performance strategy. |

## Issue Details

## Local Implementation Evidence

| Command / Evidence | Result |
|---|---|
| PHP lint, local 07.06.2026 | Passed for `CatalogTaxonomy.php`, `CatalogMapper.php`, `CatalogFilters.php`, `offer_catalog.php`, `quick-filters.php` and `results.php`. |
| JS syntax, local 07.06.2026 | Passed for `public-content-hygiene-check.mjs` and `public-content-rendered-hygiene-check.mjs`. |
| `npm run content:public-hygiene:self-test`, local 07.06.2026 | Passed; negative fixtures cover arbitrary first-8 quick filters and raw budget source rendering. |
| `npm run content:public-hygiene:rendered:self-test`, local 07.06.2026 | Passed; negative fixture covers visible machine budget `50600000 RUB` on `/offer/`. |
| `npm run content:public-hygiene:check`, local 07.06.2026 | Passed; source guard now scans offer taxonomy/quick-filter/budget rendering sources. |
| `npm run seo:check`, local 07.06.2026 | Passed; no route/canonical/sitemap behavior changed. |
| `npm run bitrix:check`, local 07.06.2026 | Passed after splitting taxonomy/preset presentation into `CatalogTaxonomy`; `CatalogMapper` and `CatalogFilters` remain under file-size budget. |
| `git diff --check`, local 07.06.2026 | Passed. |
| PHP smoke snippets, local 07.06.2026 | Passed: `50600000` formats as `50 600 000 руб.`, visible labels map to `бьюти и салоны`, `онлайн-торговля`, `Платформа данных и MLOps`, and curated options return only configured active keys. |

Production rendered evidence is pending until deploy and public cache clear.

### OFFER-TAX-WP-01 — Budget And Visible Label Fast Fixes

Workflow lane: Fast Fix.
Priority: P1.

Affected areas:

- `https://tacticum.ru/offer/`
- `local/lib/Tacticum/Offer/CatalogMapper.php`
- `local/components/tacticum/offer.catalog/templates/.default/parts/results.php`
- optional public label normalization helpers if used
- public content hygiene checks if extended

Acceptance criteria:

- Catalog cards do not render machine-like budget strings such as `50600000 RUB`.
- Budget display uses `budget_amount` when available and formats rubles as public copy.
- If amount is missing, fallback is human-readable: `по запросу` or approved equivalent.
- No change to budget sort, bucket assignment, detail page data, form payload or upstream contracts.
- Any label shim is limited, documented and does not pretend to be the final taxonomy model.

Verification:

```bash
git diff --check
php -l local/lib/Tacticum/Offer/CatalogMapper.php
php -l local/components/tacticum/offer.catalog/templates/.default/parts/results.php
npm run content:public-hygiene:check
npm run seo:check
```

After deploy/cache clear, run rendered `/offer/` or public hygiene smoke.

Implementation note 07.06.2026: local fast-fix adds `CatalogTaxonomy`, `budget_display` and card rendering from normalized amount. The raw `budget` field remains in the item array for compatibility and search context, but catalog card output uses `budget_display`. The same service provides limited Russian-first label normalization for the most visible taxonomy labels without changing existing filter keys/URLs. Source and rendered hygiene self-tests cover regression; production rendered evidence remains pending.

### OFFER-TAX-WP-02 — Quick Entries As Curated Presets

Workflow lane: Fast Fix if using temporary approved list; Full Feature if using Bitrix-managed presets.
Priority: P1.

Affected areas:

- `local/components/tacticum/offer.catalog/templates/.default/parts/quick-filters.php`
- `local/lib/Tacticum/Offer/CatalogFilters.php`
- future taxonomy/preset repository if Bitrix source is approved
- PM/Content/SEO preset list

Acceptance criteria:

- Quick entries are not produced by `array_slice(first 8)` over alphabetical aggregated options.
- Each quick entry is intentionally selected, ordered and has at least one matching active offer unless explicitly approved as a discovery route.
- Quick entries keep current filter URL mechanics and noindex/canonical behavior.
- Empty quick entries do not render as normal links.
- Labels are Russian-first and consistent with approved glossary.

Verification:

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
```

Add rendered smoke for `/offer/` after cache clear.

Implementation note 07.06.2026: local fast-fix removes `array_slice(first 8)` quick entries. `quick-filters.php` now renders `CatalogFilters::featuredOptions()`, delegated to `CatalogTaxonomy::featuredOptions()`, using curated stable keys and hiding missing/empty options. This is not the final owner-approved Bitrix preset model; it is a safe interim runtime fix that preserves current URLs and SEO posture.

### OFFER-TAX-WP-03 — Taxonomy Source-Of-Truth Decision

Workflow lane: Full Feature discovery / ADR gate.
Priority: P1.

Affected areas:

- future `offer_taxonomy_terms` / `offer_filter_presets` model or equivalent
- config registry in `tacticum_config.php` / example config if schema is approved
- `CatalogMapper`, `CatalogFilters`, `CatalogService` target design
- content/SEO glossary and label ownership
- offer seed generator and synthetic example governance

Acceptance criteria:

- Owners approve whether taxonomy terms live in Bitrix, config or another governed model.
- Approved model distinguishes raw labels, canonical codes, public labels and aliases.
- Counts remain runtime-derived from active offer items; counts are not stored as editor-maintained truth.
- Budget buckets have explicit decision: PHP/config remains acceptable or Bitrix min/max rows are approved.
- Synthetic examples are explicitly classified as examples/orientation, not real proof.
- ADR is created if new iblocks/source switch/cache pattern is introduced.

Verification:

- Docs review by PM/Content/SEO/Architect/Backend/QA.
- ADR review if schema/runtime source changes.
- No production code change should be merged under this issue unless explicitly scoped.

### OFFER-TAX-WP-04 — Bitrix Taxonomy Runtime Implementation

Workflow lane: Full Feature.
Priority: P1.
Start policy: blocked until `OFFER-TAX-WP-03` approval exists.

Affected areas:

- new Bitrix taxonomy/preset iblock(s) if approved
- `local/lib/Tacticum/Offer/*`
- `local/components/tacticum/offer.catalog/*`
- config registry and cache clear tooling
- migration/checker scripts

Acceptance criteria:

- Runtime maps raw offer labels to canonical taxonomy terms through aliases.
- Unknown raw values are either hidden, mapped to approved fallback or reported by safe checker.
- Options display approved public labels/order and runtime-derived counts.
- Quick entries use featured terms or preset rows.
- Current `/offer/`, `/offer/catalog/.../`, detail pages and sitemap behavior remain backward-compatible.
- Fallback/rollback path exists until target checks pass.
- No hardcoded new iblock IDs are introduced.

Verification:

```bash
git diff --check
npm run bitrix:check
npm run content:public-hygiene:check
npm run seo:check
```

Plus target-specific migration dry-run/apply/check/cache-clear commands once implemented.

### OFFER-TAX-WP-05 — SEO / Cache / Guard Package

Workflow lane: Full Feature / QA guard.
Priority: P2.

Affected areas:

- rendered public content hygiene checks
- taxonomy integrity checker
- SEO smoke/checker if filter URL assertions are added
- cache clear tooling if taxonomy iblock exists
- release sign-off evidence path

Acceptance criteria:

- Guard detects duplicate taxonomy codes/slugs.
- Guard detects empty public labels, unknown raw labels and empty featured quick entries.
- If budget buckets are governed, guard detects overlaps/gaps and invalid min/max values.
- Rendered `/offer/` check catches machine-like budget strings and unapproved public label leakage.
- Filtered URLs remain `noindex,follow` and canonical `/offer/` unless SEO approves otherwise.
- Cache clear instructions cover offer iblock and taxonomy iblock edits.

Verification:

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
```

Add taxonomy-specific self-test/check commands when implemented.

Implementation note 07.06.2026: local guard slice extends existing source/rendered public hygiene checks. Source guard rejects raw budget card rendering and arbitrary first-8 quick filters; rendered self-test rejects visible machine budget on `/offer/`. Full duplicate-code/unknown-alias/Bitrix taxonomy checker remains pending until `OFFER-TAX-WP-03/04` define the target taxonomy model.

### OFFER-TAX-WP-06 — Product Bridge And Future Landing Strategy

Workflow lane: Full Feature discovery.
Priority: P2.

Affected areas:

- `/offer/` product bridge blocks
- future taxonomy term fields if product relation is approved
- SEO metadata/landing-page strategy if filter pages become indexable
- offer detail content strategy by scenario/product family

Acceptance criteria:

- PM/Product/SEO decide whether sectors/scenarios/phases map to `Platform / Agents / Dev / Forum`.
- Product relation does not imply proof or readiness if the offer example is synthetic.
- Current filtered pages remain noindexed unless unique landing-page content and sitemap policy are approved.
- Any performance/search scaling plan defines threshold for moving beyond PHP cached-array filtering.

Verification:

- Owner approval package; no runtime change unless separate implementation issue exists.
- SEO review for any future indexable URL strategy.

## Related Documents

- `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/offer-page-taxonomy-presets-roadmap-2026-06-07.md`
- `docs/workflow/offer-page-taxonomy-presets-decision-2026-06-07.md`
- `docs/workflow/content-language-storyline-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/content-storage-target-issue-backlog-2026-06-05.md`
