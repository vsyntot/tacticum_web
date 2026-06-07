# About Page Content Ownership Map — 2026-06-07

Дата: 07.06.2026
Статус: owner-review package / runtime evidence refreshed / fallback retirement not approved
Workflow lane: Full Feature discovery / content-storage gate
Related gaps: `ABOUT-009`, `ABOUT-002`, `ABOUT-003`, `ABOUT-006`, `ABOUT-008`, canonical `CSG-007`, `CSG-008`, `CSG-012`, `BPC-CMP-002`, `ARCH-001`

## Purpose

Этот документ фиксирует фактическую ownership map страницы `/about/` после production evidence `2026-06-07T08:49:12Z` and local team/readiness de-dup slice `07.06.2026`. Он нужен, чтобы будущие изменения не путали hardcoded PHP partials, Bitrix live page-content rows, team iblock, lead CTA component and footer menu.

Документ не меняет runtime, не разрешает Bitrix row edits and does not approve fallback retirement.

## Current Production Evidence

| Command / Evidence | Result |
|---|---|
| `npm run content:public-cache-clear`, production 07.06.2026 | Passed after trust-storyline deploy. |
| `npm run content:public-hygiene:rendered:prod:json`, production 07.06.2026 | Passed at `2026-06-07T08:49:12Z`; `pages_checked=13`, `issues_found=0`. |
| `npm run page-content:source:http:wave2:prod`, production 07.06.2026 | Passed; `/about/ source=bitrix sections=3/3 bytes=76054`. |
| `npm run content:public-cache-clear`, production 07.06.2026 | Passed after team/readiness de-dup deploy. |
| `npm run content:public-hygiene:rendered:prod:json`, production 07.06.2026 | Passed at `2026-06-07T11:17:39Z`; `pages_checked=13`, `issues_found=0`, `/about/ ok=true`. |
| `npm run page-content:source:http:wave2:prod`, production 07.06.2026 | Passed; `/about/ source=bitrix sections=3/3 bytes=76925`. |
| Chrome-capable visual smoke, production 07.06.2026 | Passed at `2026-06-07T11:18:17Z`; manifest `/tmp/tacticum-about-team-readiness-dedup-2026-06-07-visual/manifest.json`. |

## Actual Render Ownership

`/about/` public entry includes `local/components/tacticum/about.page/templates/.default/template.php`, which includes these parts in order:

1. `company-trust.php`
2. `values-team.php`
3. `stack-cta.php`
4. `career-final.php`

The page currently renders mixed sources. `page_content.source=bitrix` means live page-content rows render where `tacticum_page_content_render_if_live('/about/', section_key)` is called, but hardcoded PHP content in the same partials still renders too.

## Section Ownership Table

| Render Area | Current Source | Current Owner | Editable Source Of Truth | Fallback / Retirement Status | Cache / Smoke Required |
|---|---|---|---|---|---|
| Hero first screen | `company-trust.php` hardcoded PHP | Content + Frontend | Git PHP partial | Not page-content owned; no fallback retirement decision applies. | `content:public-hygiene:check`, `seo:check`, rendered `/about/` smoke after deploy. |
| Company responsibility intro | `company-trust.php` hardcoded PHP | Content + Frontend | Git PHP partial | Static body renders before live page-content. | Same as hero. |
| Company stat cards | `company-trust.php` hardcoded PHP | Content + Frontend | Git PHP partial | Static body renders before live page-content. | Same as hero. |
| Timeline | `company-trust.php` hardcoded PHP | Content + PM + QA | Git PHP partial | Static body renders before live page-content; stale-year guard applies. | Rendered hygiene must pass after deploy/cache clear. |
| `company-trust` trust cards | Bitrix `page_sections/page_blocks`, section key `company-trust`; seeded by `tools/content-storage-page-content-seed.php` | Content + PM + Architect | Bitrix live rows after owner-approved edit; Git seed as source baseline | PHP partial comment says page-content fallback body retired, but hardcoded hero/timeline still render. Do not remove or retire without approval. | Page-content source check + public cache clear + rendered hygiene. |
| `values-team` operating values | Bitrix `page_sections/page_blocks`, section key `values-team`; seeded by `tools/content-storage-page-content-seed.php` | Content + PM | Bitrix live rows after owner-approved edit; Git seed as source baseline | Live row renders before hardcoded team section. Do not retire comments/files without content-storage approval. | Page-content source check + public cache clear + rendered hygiene. |
| Team section wrapper | `values-team.php` hardcoded wrapper + `tacticum:content.list` | PM + Team owner + Frontend | Git wrapper and `team` iblock content | Team data is not page-content owned. Personal/team data changes require owner approval. | Browser/mobile smoke if layout/data changes; rendered hygiene for anchors. |
| Team cards data | Bitrix `team` iblock through `tacticum:content.list` and `news.list` template | Team owner + PM + Content | Bitrix `team` rows and template | No page-content fallback. Do not alter names/photos/roles without approval. | Team UI/accessibility smoke if changed. |
| Launch role matrix | `values-team.php` hardcoded PHP below team cards | Content + PM + Frontend | Git PHP partial | Not page-content owned; does not change `team` iblock data. | Browser/mobile smoke if layout changes. |
| Technology/readiness checks | `stack-cta.php` hardcoded PHP, anchor `#technology`, compatibility alias `#stack` | Content + Architect + Frontend | Git PHP partial | No Bitrix page-content section currently owns this block. `#technology` must remain rendered while footer points to it. | Source/rendered hygiene and architect review for technical wording. |
| Lead CTA form | `tacticum:lead.cta` included from `stack-cta.php` | Frontend + Backend + QA + PM | Shared component params in Git | Do not change form IDs, payload, hidden fields or analytics taxonomy in copy tasks. | Lead form smoke if component params/form markup change. |
| `career-final` cards | Bitrix `page_sections/page_blocks`, section key `career-final`; seeded by `tools/content-storage-page-content-seed.php` | Content + PM + UX | Bitrix live rows after owner-approved edit; Git seed as source baseline | Live row renders before hardcoded final CTA. | Page-content source check + public cache clear + rendered hygiene. |
| `#start-work` / `#careers` anchors | `career-final.php` hardcoded aliases | SEO + Frontend + Content | Git PHP partial | `#careers` kept as backward-compatible alias; footer uses `#start-work`. | Rendered anchor guard. |
| Final CTA band | `career-final.php` hardcoded PHP | Content + PM + Frontend | Git PHP partial | Not page-content owned. | `seo:check`, rendered hygiene. |
| Footer company links | `.bottom.menu.php` | SEO + Content + Frontend | Git menu file | Bitrix menu cache must be cleared after deploy. | `content:public-cache-clear`, rendered anchor guard. |
| Legacy live row normalization | `PublicCopyNormalizer::normalizePageContentSection()` inside `PageContent\Repository` | Backend + Content + QA | Git PHP normalizer | Compatibility layer only; not a replacement for owner-approved Bitrix row sync. | PHP lint, product content safety check, rendered hygiene. |

## Ownership Decision Rules

| Change Type | Allowed Without Owner Approval? | Required Owners | Notes |
|---|---|---|---|
| Fix typo in hardcoded PHP copy without claim change | Yes, Fast Fix | Content + QA | Run source/rendered hygiene and cache clear after deploy. |
| Change proof/trust claim strength | No | PM + Sales + Legal + Content | Use `about-page-proof-matrix-owner-review-2026-06-07.md`. |
| Change Bitrix live page-content rows | No for broad changes; yes only with scoped approval | Content + PM + QA, plus Architect for structure | Keep no-raw-copy evidence; run page-content source check. |
| Change team names/photos/roles/bios | No | PM + Team owner + Legal if needed | Do not treat as copy-only. |
| Change lead CTA form params or payload | No | PM + Frontend + Backend + QA + Analytics/Sales if payload changes | Requires lead-form contract review if payload changes. |
| Retire fallback/comments/files | No | Architect + Backend + Content + QA + SEO | Use fallback retirement approval path. |
| Change footer anchors/labels | Maybe Fast Fix if semantic only | SEO + Content + QA | Always run rendered anchor guard and clear menu cache. |

## Sync Procedure For Future `/about/` Copy Changes

1. Decide source owner for the affected block using the table above.
2. If hardcoded PHP changes, update the matching seed and `PublicCopyNormalizer` only if live Bitrix rows may lag.
3. If Bitrix live rows change, keep approval no-raw-copy and run page-content source checks after deploy.
4. Do not remove compatibility aliases such as `#careers` without SEO/backlink review.
5. Do not retire PHP fallback comments or static bodies until fallback retirement approval exists.
6. After production deploy, run:

```bash
npm run content:public-cache-clear
npm run content:public-hygiene:rendered:prod
npm run content:public-hygiene:rendered:prod:json
npm run page-content:source:http:wave2:prod
```

## Fallback Retirement Position

Current recommendation: do not retire any `/about/` fallback/static body yet.

Reasons:

- `/about/` is mixed-source by design today: hardcoded sections and live page-content rows both render.
- `page_content.source=bitrix` confirms three live sections, not full page ownership.
- Static hero, timeline, team wrapper, launch role matrix, readiness checks, lead CTA and final CTA are still Git-owned.
- Formal section ownership and fallback retirement need Architect/Content/QA/SEO approval.

## Open Decisions

| Decision | Owner Group | Current Status | Required Evidence |
|---|---|---|---|
| Is `/about/` fully a vendor trust page or also a careers page? | PM + Content + UX + SEO | Partially resolved by `#start-work`; `#careers` remains alias. | Owner decision on career content role. |
| Should `company-trust`, `values-team`, `career-final` live rows become sole owners of those sections? | Architect + Content + Backend + QA | Open. | Page-content ownership approval and fallback retirement decision. |
| Should team section be redesigned or data be revised? | Design + PM + Team owner + QA | Open. | Design gate and personal/team data approval. |
| Which proof statements can be public? | PM + Sales + Legal + Content | Blocked. | Proof matrix approval. |

## Verification

Docs-only changes:

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
```

Runtime/content changes additionally:

```bash
npm run product:content:safety:check
npm run content:public-hygiene:rendered:self-test
npm run bitrix:check
```

Production after deploy:

```bash
npm run content:public-cache-clear
npm run content:public-hygiene:rendered:prod
npm run content:public-hygiene:rendered:prod:json
npm run page-content:source:http:wave2:prod
```

## Related Documents

- `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/about-page-proof-matrix-owner-review-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`
- `docs/workflow/content-storage-target-roadmap-2026-06-05.md`
- `docs/workflow/release-signoff-gates.md`
