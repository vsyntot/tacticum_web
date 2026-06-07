# Codex Plan: Offer Taxonomy / Presets Challenge Documentation

Issue: not assigned
Gap ID: `OFFER-TAX-*`
Workflow lane: Full Feature discovery / documentation
Owner agent: Codex
Date: 07.06.2026

## Goal

Зафиксировать результаты challenge страницы `/offer/` and create implementation-ready documentation for taxonomy, quick presets, budget display and Bitrix ownership decisions.

## Non-Goals

- No PHP/JS/CSS changes.
- No Bitrix schema or admin data changes.
- No route, canonical, sitemap, robots, form payload, analytics or upstream changes.
- No claim that owner approvals already exist.

## Context Read

- [x] `AGENTS.md`
- [ ] `.github/copilot-instructions.md` not needed for docs-only challenge package
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] Relevant docs: content-storyline, content-storage, offer seed runbook
- [x] Relevant files: `local/lib/Tacticum/Offer/*`, `local/components/tacticum/offer.catalog/*`

## Current Behavior

`/offer/` renders 1,118 examples and derives filter options from active offer items. Public sector/scenario/phase labels come from raw `RESPONSE` values and generated slugs. Quick entries are first 8 aggregated options. Budget buckets are hardcoded in PHP. Filtered URLs are noindexed/canonicalized to `/offer/`.

## Target Behavior

Documentation should make the future target explicit: governed taxonomy/preset source for labels/order/aliases/featured flags, with runtime-derived counts and preserved SEO behavior.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/offer-page-taxonomy-presets-challenge-gap-analysis-2026-06-07.md` | Source register with findings and `OFFER-TAX-*` gaps. |
| `docs/workflow/offer-page-taxonomy-presets-roadmap-2026-06-07.md` | Phased execution roadmap. |
| `docs/workflow/offer-page-taxonomy-presets-issue-backlog-2026-06-07.md` | Tracker-ready work packages. |
| `docs/workflow/offer-page-taxonomy-presets-decision-2026-06-07.md` | Decision proposal: what should/should not move to Bitrix. |
| `docs/workflow/README.md` | Add package to workflow index. |
| `docs/workflow/current-state.md` | Add current-state note for `/offer/` taxonomy challenge. |
| `docs/workflow/gap-analysis.md` | Add current gap layer summary. |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS not changed
- [x] Infoblock IDs not added
- [x] D7 code not changed
- [x] POST REST not changed

## Risks

| Risk | Mitigation |
|---|---|
| Documentation overstates approval | Mark all docs as docs-only / not approval package. |
| Future implementation moves counts into Bitrix | Explicit do-not-store-counts rule in all docs. |
| SEO regression from taxonomy migration | Preserve noindex/canonical unless SEO approves separate project. |
| Synthetic data treated as proof | Explicit synthetic governance gaps and Legal/Sales gates. |

## Verification

### Automated

```bash
git diff --check
```

### Manual Smoke

- Review docs references and status language.
- Confirm no runtime files changed.

## Rollback

Revert documentation files and index references. No production/runtime rollback needed.

## Docs To Update

- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/README.md`
