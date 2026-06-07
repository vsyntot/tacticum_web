# Codex Plan: About Owner Review Proof And Ownership Package

Issue: `/about/` owner-gated follow-up after trust-storyline production evidence
Gap ID: `ABOUT-004`, `ABOUT-009`, adjacent `ABOUT-002`, `ABOUT-003`, `ABOUT-006`, `ABOUT-008`
Workflow lane: Full Feature discovery / owner-review package, docs-only
Owner agent: Codex
Date: 2026-06-07

## Goal

Подготовить owner-review package для следующих заблокированных слоев `/about/`: proof/trust claims matrix and page-content ownership map. Цель — дать PM/Sales/Legal/Content/Architect/QA безопасный документ для решений, не публикуя новые claims и не меняя runtime.

## Non-Goals

- Не менять PHP, JS, CSS, Bitrix rows, меню, формы, маршруты или SEO metadata.
- Не добавлять public proof, customer names, metrics, logos, certifications, registry claims, SLA promises or partner status.
- Не включать raw case text, testimonial text, contact data, admin URLs, cookies, IPs or PII.
- Не retire/reintroduce fallback partials.
- Не менять lead form payload, analytics taxonomy, CRM/upstream fields.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`
- [x] `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md`
- [x] Existing proof/content-storage runbooks and no-raw-copy policy references

## Current Behavior

`/about/` fast-fix and proof-safe trust-storyline slices are deployed and guarded. Remaining blockers are owner decisions: which trust statements can be public/private/blocked, and which source owns each page section because the page mixes hardcoded partials, Bitrix live page-content rows, team iblock output, lead CTA component and footer menu anchors.

## Target Behavior

- Owners can review a proof matrix without raw proof or private data.
- Owners can review an ownership map before future Bitrix row edits, fallback retirement, page redesign or proof publication.
- Existing `ABOUT-*` statuses are advanced only to review-ready/in-progress where evidence exists; blocked approval gates remain blocked.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/about-page-proof-matrix-owner-review-2026-06-07.md` | Add no-raw-copy proof/trust matrix and owner decision checklist. |
| `docs/workflow/about-page-content-ownership-map-2026-06-07.md` | Add actual source ownership map for `/about/` sections, cache/smoke rules and fallback policy. |
| `docs/workflow/README.md` | Link new owner-review docs. |
| `docs/workflow/current-state.md` | Record review package status. |
| `docs/workflow/gap-analysis.md` | Record `ABOUT-004`/`ABOUT-009` review readiness without closure. |
| `/about/` challenge docs | Link package and update backlog/roadmap statuses. |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] No runtime code in this task
- [x] No infoblock ID usage added
- [x] No REST/API/security behavior changed
- [x] No public URL/SEO behavior changed

## Risks

| Risk | Mitigation |
|---|---|
| Accidentally publishing unsupported proof | Matrix is decision-only; all stronger claims stay blocked until owner evidence exists. |
| Raw/private evidence in repo | Documents use no-raw-copy references and decision categories only. |
| Misleading source ownership | Ownership map distinguishes PHP partials, Bitrix live rows, team iblock, lead CTA and footer menu. |
| Premature fallback retirement | Map explicitly keeps fallback retirement blocked behind content-storage gate. |

## Verification

### Automated

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
```

### Manual Smoke

None. Docs-only package.

## Rollback

Revert the documentation changes. No cache clear is required because runtime is unchanged.

## Docs To Update

- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `/about/` challenge gap/backlog/roadmap docs
