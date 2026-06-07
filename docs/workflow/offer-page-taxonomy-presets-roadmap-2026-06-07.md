# Offer Page Taxonomy / Presets Roadmap — 2026-06-07

Дата: 07.06.2026

Статус: execution roadmap for `offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`; no runtime implementation is approved by this document.
Scope: `/offer/` filters, public taxonomy, quick presets, budget display and future Bitrix content ownership. No route/canonical/form/analytics changes are included.

## Purpose

Этот roadmap задает порядок закрытия `OFFER-TAX-*` gaps. Он нужен, чтобы не перенести текущую эвристику в Bitrix mechanically and to avoid turning synthetic/generated labels into permanent public taxonomy without owner review.

## Source Register

All local IDs below come from:

- `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`

Architecture decision support:

- `docs/workflow/offer-page-taxonomy-presets-decision-2026-06-07.md`

If implementation introduces an ID not present in the source register, update the source register before coding.

## Execution Principles

1. Fix visible public defects before schema migration.
2. Move taxonomy meaning, not computed facts, into Bitrix.
3. Counts and option availability stay runtime-derived from active offer items.
4. Public labels must be Russian-first and owner-approved.
5. Synthetic seed dictionaries are input evidence, not approved public vocabulary.
6. Preserve current cautious SEO posture for filtered URLs unless SEO approves a separate landing-page strategy.
7. Keep calculator, offer detail, lead forms and upstream payload contracts out of this scope.
8. Add guards before disabling fallback/heuristics.

## Phase 0 — Documentation Adoption

Goal: make the challenge routable for owners and future implementation.

| Work | Covered IDs | Owners | Output |
|---|---|---|---|
| Link docs from workflow index/current/gap docs | `OFFER-TAX-001` - `OFFER-TAX-012` | PM + Codex | Workflow docs reference this package. |
| Decide issue split and owners | all | PM + QA | Tracker issues use `OFFER-TAX-*` and `OFFER-TAX-WP-*`. |
| Confirm source-of-truth boundary | `OFFER-TAX-001`, `OFFER-TAX-006`, `OFFER-TAX-007` | Architect + Content + SEO | Decision: taxonomy terms in Bitrix/config, counts derived at runtime. |

Exit criteria:

- Documents are discoverable.
- Owner groups and gates are explicit.
- No implementation is implied by docs-only adoption.

## Phase 1 — Fast Fix: Visible Catalog Defects

Goal: improve public UX without changing storage model.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Budget display formatting | `OFFER-TAX-004` | Fast Fix | Backend/Frontend + QA + Content | Card budget renders public ruble format from normalized amount or clear fallback. |
| Quick-entry explicit list | `OFFER-TAX-002` | Fast Fix or Full Feature | PM + Content + Frontend/Backend | Quick entries no longer come from arbitrary first 8 sorted options. |
| Label cleanup shim | `OFFER-TAX-003` | Fast Fix | Content + Backend + QA | Most visible mixed labels normalized through mapper/glossary without schema change. |

Do not start:

- Bitrix schema migration;
- route/canonical changes;
- offer detail copy rewrite;
- changing offer item seed/generator in the same patch unless needed for label normalization.

Exit criteria:

- `/offer/` cards do not show machine-like budget strings.
- Quick entries are intentional and ordered.
- Production rendered hygiene and `/offer/` smoke pass after cache clear.

Status 07.06.2026: implemented and deployed for Fast Fix scope. `CatalogTaxonomy` owns temporary public label normalization, budget display formatting, budget buckets and curated featured option keys. `CatalogMapper` adds `budget_display` and preserves legacy raw keys/URLs; `quick-filters.php` no longer uses first-8 aggregation. Local PHP lint, `content:public-hygiene:*` self-tests, `content:public-hygiene:check`, `seo:check`, `bitrix:check` and `git diff --check` passed. Production cache clear, rendered hygiene, page-content source and SEO checks passed; rendered hygiene JSON at `2026-06-07T12:22:16Z` reports `pages_checked=13`, `issues_found=0`, `/offer/ ok=true`.

## Phase 2 — Taxonomy And Preset Model Approval

Goal: approve what should be edited in Bitrix and what remains runtime-derived.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Taxonomy term model | `OFFER-TAX-001`, `OFFER-TAX-006` | Full Feature with ADR gate | Architect + Backend + Content | Approved fields, config keys, source/fallback mode and cache policy. |
| Public label and alias draft | `OFFER-TAX-001`, `OFFER-TAX-003`, `OFFER-TAX-005` | Full Feature | PM + Content + SEO + Sales | Approved labels, aliases, sort order and hidden/merged terms. |
| Quick preset model | `OFFER-TAX-002` | Full Feature with Design/UX gate | PM + UX + Content + SEO | Featured terms or preset rows with title, filters, sort and active status. |
| Budget bucket decision | `OFFER-TAX-011` | Full Feature | Sales + PM + Architect | Decision: keep PHP/config or move buckets to taxonomy min/max rows. |
| SEO preservation decision | `OFFER-TAX-007` | Full Feature | SEO + PM + Backend | Confirm noindex/canonical stays, or open separate SEO landing-page issue. |

Exit criteria:

- Owner-approved model exists.
- ADR is added if new iblocks/config/runtime source switching are introduced.
- Do-not-store-counts rule is explicitly accepted.

Status 07.06.2026: owner-review package is prepared but not owner-approved. Accepted `docs/adr/ADR-012-offer-taxonomy-presets-bitrix-model.md` defines the target Bitrix/config/fallback pattern; `docs/workflow/offer-taxonomy-presets-owner-approval-2026-06-07.draft.json` provides a safe draft with current candidate terms and governance decisions; `tools/offer-taxonomy-approval-check.mjs` validates owner approvals, gates, labels, aliases, featured terms, budget policy, no stored counts and no runtime switch/iblock apply approval. `tools/offer-taxonomy-implementation-gate.mjs` blocks accidental runtime/schema markers while the owner approval JSON remains draft. Runtime implementation remains blocked until the approval JSON passes without `--allow-draft` and implementation gate passes with the approved artifact.

## Phase 3 — Runtime Implementation

Goal: make runtime use governed taxonomy while preserving existing catalog behavior.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Bitrix schema and config registry | `OFFER-TAX-006` | Full Feature | Backend + Architect + DevOps | Migration dry-run/apply, config keys, source/fallback switch. |
| Mapper alias normalization | `OFFER-TAX-001`, `OFFER-TAX-003`, `OFFER-TAX-005` | Full Feature | Backend + QA + Content | Raw offer labels map to canonical terms; unknowns handled safely. |
| Derived options with term metadata | `OFFER-TAX-001`, `OFFER-TAX-002`, `OFFER-TAX-007` | Full Feature | Backend + Frontend + QA | Options use canonical labels/order/featured flags and runtime counts. |
| Quick preset rendering | `OFFER-TAX-002` | Full Feature | Frontend + PM + QA | Quick entries come from featured terms/presets and hide if count is zero unless explicitly allowed. |
| Cache invalidation | `OFFER-TAX-006`, `OFFER-TAX-009` | Full Feature | Backend + DevOps + QA | Managed tags/cache clear cover offer iblock and taxonomy iblock edits. |

Exit criteria:

- Existing `/offer/` list/filter/detail routes still work.
- Filtered URLs remain noindexed/canonicalized unless SEO-approved otherwise.
- Counts match active offers; empty categories do not appear as normal quick entries.
- Fallback rollback path exists until target checks pass.

## Phase 4 — Guards And Release Evidence

Goal: prevent recurrence and make deployment safe.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Taxonomy integrity guard | `OFFER-TAX-009` | Full Feature | QA + Backend + Content | Duplicate codes, empty labels, overlapping budgets, unknown aliases and empty featured presets are detected. |
| Rendered public hygiene extension | `OFFER-TAX-003`, `OFFER-TAX-004` | Full Feature | QA + Content | Rendered `/offer/` blocks machine budget strings and unapproved public labels. |
| SEO regression check | `OFFER-TAX-007` | Full Feature | SEO + QA + Backend | Filtered URL canonical/noindex stays correct. |
| Production cache/source checks | `OFFER-TAX-006`, `OFFER-TAX-009` | Full Feature | DevOps + QA | Cache clear, rendered hygiene, page-content/offer source checks after deploy. |

Exit criteria:

- Source and rendered checks pass locally and on production after cache clear.
- Safe JSON evidence can be attached to release sign-off.
- Rollback instructions are documented.

Status 07.06.2026: guard slice implemented and production-smoked. Source hygiene rejects raw budget rendering and arbitrary first-8 quick filters; rendered hygiene rejects visible machine budget on `/offer/`. Production rendered hygiene passed at `2026-06-07T12:22:16Z`; `seo:check:prod` also passed. Full taxonomy integrity guard for duplicate codes, unknown aliases and Bitrix terms remains pending until the owner-approved model exists.

## Phase 5 — Product/SEO Maturity

Goal: improve `/offer/` beyond taxonomy hygiene.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Product-family relation | `OFFER-TAX-008` | Full Feature | PM + Product + SEO + Content | Decide whether scenarios/sectors map to Platform/Agents/Dev/Forum. |
| Offer detail content cleanup | Related `CLS-008`, `CLS-007` | Full Feature | Content + Sales + Legal + SEO | Replace fear/generic vendor claims with evidence-safe decision checklist. |
| Indexable landing-page decision | `OFFER-TAX-007` | Full Feature with SEO gate | SEO + PM + Content + Backend | Only if each taxonomy page has unique intent/copy and sitemap policy. |
| Performance scaling | `OFFER-TAX-010` | Full Feature | Backend + QA | Revisit PHP array filtering if catalog volume grows materially. |

## Suggested Issue Packaging

| Issue | Theme | Must Include |
|---|---|---|
| `OFFER-TAX-WP-01` | Budget and visible label fast fixes | `OFFER-TAX-003`, `OFFER-TAX-004` |
| `OFFER-TAX-WP-02` | Quick entries as curated presets | `OFFER-TAX-002` |
| `OFFER-TAX-WP-03` | Taxonomy source-of-truth decision | `OFFER-TAX-001`, `OFFER-TAX-005`, `OFFER-TAX-006`, `OFFER-TAX-011` |
| `OFFER-TAX-WP-04` | Bitrix taxonomy implementation | `OFFER-TAX-001`, `OFFER-TAX-006`, `OFFER-TAX-009` |
| `OFFER-TAX-WP-05` | SEO/cache/release guard package | `OFFER-TAX-007`, `OFFER-TAX-009`, `OFFER-TAX-012` |
| `OFFER-TAX-WP-06` | Product bridge and future landing strategy | `OFFER-TAX-008`, `OFFER-TAX-010` |

## Verification Guidance

Minimum verification for implementation tasks derived from this roadmap:

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
```

Additional verification depends on scope:

- PHP/template changes: `php -l` for changed PHP files.
- CSS/JS changes: relevant syntax/component checks.
- Bitrix schema/source changes: migration dry-run, target check, cache clear and production rendered source/hygiene checks.
- SEO-sensitive changes: filtered URL canonical/noindex smoke and `seo:check:prod` after deploy.
- Public filter label changes: rendered `/offer/` smoke on desktop/mobile if layout can shift.

## Related Documents

- `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/offer-page-taxonomy-presets-issue-backlog-2026-06-07.md`
- `docs/workflow/offer-page-taxonomy-presets-decision-2026-06-07.md`
- `docs/workflow/content-language-storyline-public-glossary-2026-06-07.md`
- `docs/workflow/content-storage-target-roadmap-2026-06-05.md`
- `docs/workflow/offer-example-seed-runbook.md`
