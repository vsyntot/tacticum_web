# About Page UX / Content Challenge Roadmap — 2026-06-07

Дата: 07.06.2026

Статус: execution roadmap for `about-page-ux-content-challenge-gap-analysis-2026-06-07.md`; Phase 1 objective fast fixes, Phase 5 minimal guard and Phase 2 proof-safe copy slice are completed in production for the scoped issues; proof and content-ownership owner-review package is prepared; owner-gated proof/team/content-ownership work remains open.
Scope: `/about/` UX/UI/content planning only. No further runtime implementation is approved by this roadmap without the gates below.

## Purpose

Этот roadmap задаёт порядок закрытия `ABOUT-*` gaps. Он нужен, чтобы не начать переписывать "О компании" хаотично: сначала убрать trust-breaking дефекты, затем утвердить роль страницы, после этого менять контент, UI и content-storage ownership.

## Source Register

All local IDs below come from:

- `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`

Owner-review support documents:

- `docs/workflow/about-page-proof-matrix-owner-review-2026-06-07.md`
- `docs/workflow/about-page-content-ownership-map-2026-06-07.md`

If a task references an `ABOUT-*` ID not present in the source register, update both documents before implementation planning.

## Execution Principles

1. Fix objective defects before subjective copy improvements: stale timeline, duplicate IDs, broken anchors.
2. Treat `/about/` as a vendor trust page, not as a generic company brochure.
3. Keep Russian-first public language; technical terms must be either product names, standard acronyms or explained.
4. Separate historic milestones from current status; avoid year/current copy that will stale again.
5. Do not add proof stronger than existing approved evidence.
6. Do not mix copy-only work with form payload, CRM/upstream, analytics or route/canonical changes.
7. Decide content ownership before broad Bitrix live row edits.
8. Validate rendered production after cache clear if Bitrix content or cached component output changes.

## Phase 0 — Documentation Adoption

Goal: make the `/about/` challenge visible and owner-routable.

| Work | Covered IDs | Owners | Output |
|---|---|---|---|
| Link docs from workflow index/current/gap docs | all | PM + Codex | `README.md`, `current-state.md`, `gap-analysis.md` reference this layer |
| Decide implementation lane per issue | all | PM + QA | Tracker issues use `ABOUT-*` and canonical gap IDs |
| Confirm page owner and content source owner | `ABOUT-002`, `ABOUT-009` | PM + Content + Architect | Owner names for public copy, Bitrix rows and PHP partials |

Exit criteria:

- Documents are discoverable.
- Owner groups are explicit.
- No runtime implementation is implied by docs-only closure.

## Phase 1 — Fast Fix: Public Trust Defects

Goal: remove visible defects that currently reduce trust regardless of future redesign.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Timeline stale/current fix | `ABOUT-001`, `ABOUT-010` | Fast Fix | Content + Frontend/Backend + QA | No rendered `2025` + `Сегодня` stale pair; history/current split approved |
| Anchor and ID integrity | `ABOUT-007`, `ABOUT-010` | Fast Fix | Frontend + QA + SEO | Unique IDs; footer anchors point to existing semantic sections |
| Generic CTA cleanup | `ABOUT-008`, `ABOUT-003` | Fast Fix | Content + PM | Remove `достичь новых высот`; CTA names concrete next step |
| Minimal internal-term cleanup | `ABOUT-003`, `ABOUT-006` | Fast Fix / Full Feature | Content + Architect | Replace or explain most visible internal terms in current live sections |

Do not start:

- full layout redesign;
- new claims/proof/logos;
- form payload changes;
- route/canonical changes.

Exit criteria:

- Rendered `/about/` has no stale timeline contradiction.
- Rendered `/about/` has no duplicate IDs and no missing `/about/#...` anchor targets.
- Footer labels match visible section semantics or are adjusted with SEO awareness.
- `git diff --check`, `seo:check` and relevant rendered smoke pass.

Status 07.06.2026: completed for `ABOUT-001`, `ABOUT-007`, `ABOUT-010`, `ABOUT-WP-01` and `ABOUT-WP-02` after production public cache clear and rendered hygiene evidence at `2026-06-07T08:24:11Z`. Broader `ABOUT-003`, `ABOUT-006` and `ABOUT-008` maturity work remains owner-gated.

## Phase 2 — Trust Narrative Rewrite

Goal: make `/about/` a coherent buyer trust page.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Page role and narrative outline | `ABOUT-002`, `ABOUT-004` | Full Feature | PM + Content + Sales + UX | Approved section hierarchy and company promise |
| Russian-first rewrite | `ABOUT-003`, `ABOUT-006`, `ABOUT-008` | Full Feature | Content + PM + SEO + Architect | Public copy follows glossary and explains technical terms |
| Trust/proof matrix | `ABOUT-004` | Full Feature | PM + Sales + Legal + Content | Public/private/blocked trust statements |
| Timeline rewrite | `ABOUT-001`, `ABOUT-002` | Full Feature | PM + Content | Historical milestones and current operating focus separated |

Do not start:

- stronger proof/claims without approval;
- team/person content changes without owner review;
- visual restyle before copy hierarchy is approved.

Exit criteria:

- Page can be summarized as one trust story.
- Each section has a clear buyer purpose.
- Copy does not make unsupported claims or expose internal process labels as public headings.

Status 07.06.2026: proof-safe copy slice implemented for hardcoded partials, wave2 seed and runtime `/about/` page-content normalization; production rendered hygiene passed at `2026-06-07T08:49:12Z`. No-raw-copy proof matrix is prepared for owner review. This does not close Phase 2: PM/Sales/Legal/Content approval and owner decisions on public/private/blocked trust statements are still pending before any stronger public claims.

## Phase 3 — Team, Timeline And UI Treatment

Goal: align visual hierarchy and accessibility with the approved trust storyline.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Team card redesign | `ABOUT-005` | Full Feature with Design gate | Design + Frontend + QA + PM | Consistent crops, readable role summary, keyboard/mobile-accessible details |
| Timeline component treatment | `ABOUT-001`, `ABOUT-002` | Full Feature with Design gate | Design + Frontend + Content | Non-stale milestone/current visual pattern |
| Trust block visual hierarchy | `ABOUT-002`, `ABOUT-004`, `ABOUT-006` | Full Feature with Design gate | Design + PM + Content | Trust/proof blocks have stronger priority than generic stack/culture cards |
| Career/culture decision | `ABOUT-008`, `ABOUT-007` | Full Feature | PM + Content + UX | Either real `#careers` section or footer/copy removal/rename |

Do not start:

- broad design-system token changes unless TO BE design gate approves;
- changing behavior-bearing selectors without component/state contract review.

Exit criteria:

- Mobile and desktop render cleanly.
- Team details are accessible without hover-only dependency.
- Timeline and trust sections no longer look like generic template cards.

Status 07.06.2026: scoped `ABOUT-WP-04` team card implementation has production rendered/source/browser evidence. The team card no longer depends on hover-only overlay for detail. The timeline marker follow-up also has production rendered/source/browser evidence: quiet past years, bordered `2025` milestone and primary current focus with explicit `Сейчас` badge instead of color-only meaning. The work-model feature-grid follow-up also has production rendered/source/browser evidence: `values-team` restores section heading/intro, count-aware 3-card layout and process-card copy. A local team/readiness de-dup follow-up now makes the founder cards compact, adds a role-composition bridge and removes the duplicate technology contour grid by moving `#technology` to the single readiness matrix; production evidence is pending. Phase 3 is not fully closed until this follow-up is deployed/smoked and PM/Design visual acceptance is recorded.

## Phase 4 — Page-Content Ownership And Sync

Goal: remove ambiguity between Bitrix live rows, PHP partials and retired fallback comments.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Section ownership map | `ABOUT-009` | Full Feature | Architect + Backend + Content | Table: section key, live source, fallback status, owner, cache/smoke command |
| Seed/live row sync | `ABOUT-003`, `ABOUT-006`, `ABOUT-008`, `ABOUT-009` | Full Feature | Content + Backend + QA | `tools/content-storage-page-content-seed.php` and Bitrix rows aligned |
| Fallback/comment cleanup decision | `ABOUT-009` | Full Feature with content-storage gate | Architect + Backend + QA | No misleading `fallback retired` comment while static body still renders |
| Cache and source evidence | `ABOUT-009`, `ABOUT-010` | Full Feature | QA + DevOps/Backend | Cache clear and rendered source marker checks after deploy |

Do not start:

- manual Bitrix row edits without source/update plan;
- fallback retirement or resurrection without owner-approved evidence.

Exit criteria:

- Future editor/developer can tell where each public section comes from.
- Production rendered source matches intended owner model after cache clear.

Status 07.06.2026: wave2 seed was aligned with the trust-storyline copy, old live `/about/` rows are protected at runtime by `PublicCopyNormalizer::normalizePageContentSection()`, and production source check passed with `/about/ source=bitrix sections=3/3 bytes=76054`. Actual mixed-source ownership map is prepared for owner review. Formal source-of-truth transfer, Bitrix row sync and fallback retirement remain open under `ABOUT-WP-07`.

## Phase 5 — Guarding And Release Evidence

Goal: prevent recurrence of the exact defects found in the challenge.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Stale timeline guard | `ABOUT-001`, `ABOUT-010` | Fast Fix / Full Feature | QA + Backend/Frontend | Source or rendered guard for stale `year + Сегодня` pattern |
| Rendered ID/anchor guard | `ABOUT-007`, `ABOUT-010` | Full Feature | QA + Frontend | Duplicate ID and missing anchor detection for public pages or `/about/` scope |
| Semantic footer anchor check | `ABOUT-007`, `ABOUT-010` | Full Feature | QA + SEO + Content | `/about/#partners` cannot point to technology contours unless label is renamed |
| Public-language extension | `ABOUT-003`, `ABOUT-006`, `ABOUT-010` | Full Feature | QA + Content + Architect | Content hygiene covers approved about-page forbidden terms or checklist |

Do not start:

- adding brittle visual-only assertions before source/rendered text checks;
- blocking approved technical terms without glossary exceptions.

Exit criteria:

- `ABOUT-001`, `ABOUT-007` and key internal-term recurrences are caught before release.
- Guard produces safe, no-PII JSON evidence if used in release sign-off.

Status 07.06.2026: minimal guard package completed through existing `content:public-hygiene:*` tools. Latest production `content:public-hygiene:rendered:prod:json` passed for 13 pages with `issues_found=0` at `2026-06-07T08:49:12Z`; `page-content:source:http:wave2:prod` confirmed `/about/ source=bitrix sections=3/3 bytes=76054`.

## Suggested Issue Packaging

| Issue | Theme | Must Include |
|---|---|---|
| `ABOUT-WP-01` | Stale timeline/current status fast fix | `ABOUT-001`, `ABOUT-010` |
| `ABOUT-WP-02` | Anchor, ID and footer navigation integrity | `ABOUT-007`, `ABOUT-010` |
| `ABOUT-WP-03` | Trust narrative rewrite | `ABOUT-002`, `ABOUT-003`, `ABOUT-004` |
| `ABOUT-WP-04` | Team section UX/accessibility | `ABOUT-005` |
| `ABOUT-WP-05` | Technology stack and terminology cleanup | `ABOUT-003`, `ABOUT-006` |
| `ABOUT-WP-06` | CTA, career and culture cleanup | `ABOUT-008`, `ABOUT-007` |
| `ABOUT-WP-07` | Page-content/fallback ownership and sync | `ABOUT-009` |
| `ABOUT-WP-08` | Guard/smoke package | `ABOUT-010`, `ABOUT-007`, `ABOUT-001` |

Status 07.06.2026: `ABOUT-WP-01`, `ABOUT-WP-02` and `ABOUT-WP-08` are closed for fast-fix/guard scope. `ABOUT-WP-03`, `ABOUT-WP-05` and `ABOUT-WP-06` have a proof-safe copy implementation deployed with production rendered evidence; `ABOUT-WP-03` now also has a proof matrix ready for owner review. `ABOUT-WP-04` has production rendered/source/browser evidence and needs only PM/Design visual acceptance before closure. `ABOUT-WP-07` has an ownership map ready for owner review. Continue `ABOUT-WP-03/05/06/07` only after the relevant owner decisions.

## Verification Guidance

Minimum verification for implementation tasks derived from this roadmap:

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
```

Additional verification depends on scope:

- Bitrix/page-content source changes: page-content seed/audit/source-marker checks, cache clear, rendered production smoke.
- Anchor/ID fixes: rendered ID uniqueness and anchor existence check for `/about/`.
- Team UI/layout changes: desktop/mobile browser or visual smoke.
- Form markup/ID changes: lead form smoke and no payload contract change confirmation.
- Claims/proof changes: owner-approved claim matrix; no raw PII/customer-private evidence in repo.

## Related Documents

- `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-guard-proposal-2026-06-07.md`
- `docs/workflow/about-page-proof-matrix-owner-review-2026-06-07.md`
- `docs/workflow/about-page-content-ownership-map-2026-06-07.md`
- `docs/workflow/plans/2026-06-07-about-owner-review-proof-ownership.md`
- `docs/workflow/plans/2026-06-07-about-team-ui-accessibility-slice.md`
- `docs/workflow/content-language-storyline-public-glossary-2026-06-07.md`
- `docs/workflow/content-storage-target-roadmap-2026-06-05.md`
