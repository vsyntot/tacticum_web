# About Page UX / Content Challenge Issue Backlog — 2026-06-07

Дата: 07.06.2026
Статус: issue backlog draft / fast-fix, guard and trust-storyline slices deployed with production evidence / proof and ownership owner-review package prepared / owner approvals pending for broader work

Source register: `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`
Roadmap: `docs/workflow/about-page-ux-content-challenge-roadmap-2026-06-07.md`
Guard proposal: `docs/workflow/about-page-ux-content-challenge-guard-proposal-2026-06-07.md`
Proof matrix: `docs/workflow/about-page-proof-matrix-owner-review-2026-06-07.md`
Ownership map: `docs/workflow/about-page-content-ownership-map-2026-06-07.md`

## Purpose

Этот документ переводит `ABOUT-*` gaps into backlog-ready work packages. Его можно использовать для ручного создания задач в трекере. Он не является owner approval. Closed statuses below mean only that the scoped fast-fix/guard evidence exists; broader trust, proof, team UI and content ownership work remains owner-gated.

## Start Policy

| Policy | Meaning |
|---|---|
| `fast-fix-allowed` | Можно делать ограниченный фикс без нового продукта/контракта; smoke required |
| `owner-review-required` | Можно уточнять docs/copy proposals; implementation waits for PM/Content/Sales/SEO/Legal/Design approval as applicable |
| `blocked-claims-evidence` | Нельзя публиковать claims, metrics, logos, certifications or named proof until evidence/legal/sales approval exists |
| `design-gate-required` | Нужен Designer before UI/card/layout implementation |
| `content-storage-gate-required` | Нужен Architect/Content/QA decision before changing page-content source/fallback ownership |
| `guard-scope-required` | Нужен отдельный scope for automation/checks before or with implementation |

## Backlog Index

| Issue | Status | Start policy | Priority | Owners | Gap IDs | Objective |
|---|---|---|---:|---|---|---|
| `ABOUT-WP-01` | closed | `fast-fix-allowed` + `guard-scope-required` | P0 | PM + Content + QA + Frontend/Backend | `ABOUT-001`, `ABOUT-010` | Remove stale `2025 / Сегодня` contradiction and prevent recurrence |
| `ABOUT-WP-02` | closed | `fast-fix-allowed` + `guard-scope-required` | P1 | Frontend + QA + SEO + Content | `ABOUT-007`, `ABOUT-010` | Fix duplicate IDs, missing `#careers` and misleading `#partners` anchor |
| `ABOUT-WP-03` | owner-review-ready-production-guarded | `owner-review-required` + `blocked-claims-evidence` | P1 | PM + Content + Sales + Legal + UX | `ABOUT-002`, `ABOUT-003`, `ABOUT-004` | Rewrite `/about/` as coherent vendor trust page |
| `ABOUT-WP-04` | local-implemented-prod-pending | `design-gate-required` | P1 | Design + Frontend + QA + PM | `ABOUT-005` | Redesign team section for trust, readability and accessibility |
| `ABOUT-WP-05` | in-progress-production-guarded | `owner-review-required` | P1 | Content + Architect + Sales + PM | `ABOUT-003`, `ABOUT-006` | Replace generic stack/internal terminology with buyer-relevant capability contours |
| `ABOUT-WP-06` | in-progress-production-guarded | `owner-review-required` | P2 | PM + Content + UX + SEO | `ABOUT-008`, `ABOUT-007` | Decide career/culture role and rewrite final CTA/anchors |
| `ABOUT-WP-07` | owner-review-ready | `content-storage-gate-required` | P1 | Architect + Backend + Content + QA | `ABOUT-009` | Define and sync page-content/PHP partial ownership for `/about/` |
| `ABOUT-WP-08` | closed | `guard-scope-required` | P2 | QA + Frontend + Backend + Content | `ABOUT-010`, `ABOUT-007`, `ABOUT-001` | Implement or adopt `/about/` rendered/source guard package |

## Production Evidence

| Command / Evidence | Result |
|---|---|
| `npm run content:public-cache-clear`, production 07.06.2026 | Passed. Public rendered/menu/component/composite/template caches were cleared after deploy. |
| `npm run content:public-hygiene:rendered:prod:json`, production 07.06.2026 | Passed at `2026-06-07T08:24:11Z`; `pages_checked=13`, `issues_found=0`, `/about/ ok=true`. |
| `npm run page-content:source:http:wave2:prod`, production 07.06.2026 | Passed; `/about/ source=bitrix sections=3/3 bytes=74964`, expected source `bitrix`. |

## Local Trust Storyline Evidence

| Command / Evidence | Result |
|---|---|
| PHP lint, local 07.06.2026 | Passed for about partials, `PublicCopyNormalizer`, `PageContent\\Repository` and page-content seed. |
| `npm run content:public-hygiene:check`, local 07.06.2026 | Passed. |
| `npm run content:public-hygiene:rendered:self-test`, local 07.06.2026 | Passed. |
| `npm run product:content:safety:check`, local 07.06.2026 | Passed. |
| `npm run seo:check` / `npm run bitrix:check`, local 07.06.2026 | Passed. |

## Trust Storyline Production Evidence

| Command / Evidence | Result |
|---|---|
| `npm run content:public-cache-clear`, production 07.06.2026 | Passed after trust-storyline deploy. |
| `npm run content:public-hygiene:rendered:prod:json`, production 07.06.2026 | Passed at `2026-06-07T08:49:12Z`; `pages_checked=13`, `issues_found=0`, `/about/ ok=true`. |
| `npm run page-content:source:http:wave2:prod`, production 07.06.2026 | Passed; `/about/ source=bitrix sections=3/3 bytes=76054`, expected source `bitrix`. |

## Owner Review Package Evidence

| Document / Evidence | Result |
|---|---|
| `about-page-proof-matrix-owner-review-2026-06-07.md` | Prepared no-raw-copy matrix for `ABOUT-WP-03` / `ABOUT-004`; stronger public claims remain blocked until owner approval. |
| `about-page-content-ownership-map-2026-06-07.md` | Prepared actual source ownership map for `ABOUT-WP-07` / `ABOUT-009`; Bitrix row sync and fallback retirement remain blocked until content-storage approval. |
| `plans/2026-06-07-about-owner-review-proof-ownership.md` | Confirms docs-only scope; no runtime, Bitrix, route, form, analytics or SEO behavior changed. |

## Issue Details

### ABOUT-WP-01 — Stale Timeline / Current Status Fast Fix

Workflow lane: Fast Fix.
Priority: P0.

Affected areas:

- `https://tacticum.ru/about/`
- `local/components/tacticum/about.page/templates/.default/parts/company-trust.php`
- Bitrix `page_sections/page_blocks` rows for `/about/` if live content owns timeline after implementation
- `tools/content-storage-page-content-seed.php` if seed/source rows are updated
- future guard tooling from `about-page-ux-content-challenge-guard-proposal-2026-06-07.md`

Acceptance criteria:

- Rendered `/about/` no longer shows `2025` badge paired with heading `Сегодня` on 2026-06-07 or later.
- `2025` remains only as a past milestone if historically accurate.
- Current focus is expressed without a hardcoded stale year, or with an approved dynamic/current-year policy.
- No route, metadata, form payload or proof claim changes.
- Cache clear/source check is performed if Bitrix live rows are changed.

Verification:

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
```

Rendered `/about/` smoke is now covered by `content:public-hygiene:rendered:prod`; refresh it after any future public template/menu/page-content deploy.

Implementation note 07.06.2026: source timeline now separates the `2025` milestone from current focus and rendered hygiene covers stale `2025 + Сегодня`. Production closure passed after public cache clear: rendered hygiene JSON reports `checked_at=2026-06-07T08:24:11Z`, `pages_checked=13`, `issues_found=0`.

### ABOUT-WP-02 — Anchor, ID And Footer Navigation Integrity

Workflow lane: Fast Fix.
Priority: P1.

Affected areas:

- `https://tacticum.ru/about/`
- `.bottom.menu.php`
- `local/components/tacticum/about.page/templates/.default/parts/company-trust.php`
- `local/components/tacticum/about.page/templates/.default/parts/values-team.php`
- `local/components/tacticum/about.page/templates/.default/parts/stack-cta.php`
- `local/components/tacticum/lead.cta/` rendered form IDs only if collision source is inside shared component params

Acceptance criteria:

- Rendered `/about/` has unique `id` attributes.
- No collision between section `id="about-company"` and lead form field ID.
- Every footer `/about/#...` target exists on rendered page.
- `#partners` is either a real partners/trust section, renamed to the actual technology section, or removed from footer.
- `#careers` either points to a real careers section or the footer link is removed/renamed.
- SEO approves footer nav label changes if they affect public navigation semantics.

Verification:

```bash
git diff --check
npm run seo:check
```

Rendered ID/anchor checks are now covered by `content:public-hygiene:rendered:prod`; refresh them after any future public template/menu/page-content deploy.

Implementation note 07.06.2026: source changes set `FIELD_PREFIX=about-cta`, add `#careers`, rename footer `/about/#partners` to `/about/#technology`, and rendered hygiene covers duplicate IDs, missing anchors and misleading `#partners`. Production closure passed after public cache clear: rendered hygiene JSON reports `checked_at=2026-06-07T08:24:11Z`, `pages_checked=13`, `issues_found=0`.

### ABOUT-WP-03 — Trust Narrative Rewrite

Workflow lane: Full Feature.
Priority: P1.
Start policy: owner review and claims evidence required.

Affected areas:

- `/about/` page-content rows and/or PHP partials
- `local/components/tacticum/about.page/templates/.default/parts/company-trust.php`
- `local/components/tacticum/about.page/templates/.default/parts/values-team.php`
- `local/components/tacticum/about.page/templates/.default/parts/career-final.php`
- `tools/content-storage-page-content-seed.php`
- footer/company one-liners if reused elsewhere

Acceptance criteria:

- `/about/` has one approved page role: vendor trust page for corporate AI products and implementation.
- First screen answers who Tacticum is and why the user should trust the team with an enterprise AI initiative.
- Section order follows a buyer decision path: trust reason, operating model, team, technology/risk contours, history/current focus, CTA.
- Public language is Russian-first; technical terms are explained or moved below business explanation.
- No unsupported metrics, logos, certifications, guarantees or partner claims are introduced.

Verification:

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
```

Implementation note 07.06.2026: local source and page-content seed replace the most visible generic stack and internal terms in `/about/` scope; `PublicCopyNormalizer` protects old live Bitrix rows for the same strings. This is not the full trust narrative rewrite.

If Bitrix rows are changed, add page-content audit/source checks and production cache clear.

Implementation note 07.06.2026: proof-safe slice reframes hardcoded `/about/` copy and wave2 seed around corporate AI launch responsibility without adding claims, metrics, logos or customer proof. Runtime `PublicCopyNormalizer::normalizePageContentSection()` protects old live `/about/` Bitrix rows by section/block key. Production rendered hygiene passed at `2026-06-07T08:49:12Z`. A no-raw-copy proof matrix is prepared for owner review. This advances `ABOUT-WP-03`, but does not close the full trust narrative rewrite until owners approve public/private/blocked trust statements.

### ABOUT-WP-04 — Team Section UX / Accessibility

Workflow lane: Full Feature with Design gate.
Priority: P1.

Affected areas:

- `local/components/tacticum/about.page/templates/.default/parts/values-team.php`
- `local/components/tacticum/content.list` wrapper if behavior changes
- Bitrix `team` iblock rendering template, likely `local/templates/tacticum/components/bitrix/news.list/team/` or current wrapper output
- Team images/content in Bitrix admin only with owner approval
- CSS assets under `local/templates/tacticum/styles/` if layout changes

Acceptance criteria:

- Team cards have consistent crop/aspect and do not dominate page hierarchy more than trust sections.
- Role and short expertise summary are visible without hover-only dependency.
- Keyboard and mobile users can access the same critical information.
- No personal data, photos, names, roles or bios are changed without owner approval.
- Component/state selectors are preserved or updated with contract/QA review.

Verification:

```bash
git diff --check
npm run seo:check
```

Implementation note 07.06.2026: production fast-fix removed `достичь новых высот`; `#careers` now exists as a rendered anchor. Career/culture strategy remains owner-review scope.

Add relevant browser/mobile visual smoke after UI changes.

Implementation note 07.06.2026: local scoped slice replaces hover-only team overlay with readable cards. `news.list/team` renders existing approved team data as article cards with visible role, preview, non-duplicate detail and labelled contact links; `values-team.php` explicitly requests `PHOTO`; legacy `.member-overlay` CSS/JS was removed. No personal/team data or Bitrix rows changed. Production closure still requires browser/mobile smoke, cache clear after deploy and rendered evidence.

### ABOUT-WP-05 — Technology Stack And Terminology Cleanup

Workflow lane: Full Feature.
Priority: P1.

Affected areas:

- `local/components/tacticum/about.page/templates/.default/parts/stack-cta.php`
- `local/components/tacticum/about.page/templates/.default/parts/values-team.php`
- `tools/content-storage-page-content-seed.php`
- Bitrix page-content rows for `/about/`
- public glossary `content-language-storyline-public-glossary-2026-06-07.md`

Acceptance criteria:

- Generic stack list is replaced by buyer-relevant capability/risk contours, or retired.
- `BERT`, `NLTK`, `Hadoop`, `Tableau` are not used as generic proof of expertise unless the claim is accurate, current and intentionally public.
- Internal terms like `quality gates`, `production rollout`, `backend`, `data/RAG`, `delivery` are replaced or explained.
- Architect reviews technical accuracy.

Verification:

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
```

Implementation note 07.06.2026: production fast-fix removed guarded visible generic stack and internal terminology from `/about/`, but the final capability/risk narrative still requires Architect/Content review.

Implementation note 07.06.2026: stack section now describes launch-readiness checks instead of a tool inventory. Source/rendered guards block the old generic about stack wording and production rendered hygiene passed at `2026-06-07T08:49:12Z`.

### ABOUT-WP-06 — CTA, Career And Culture Cleanup

Workflow lane: Full Feature or Fast Fix depending on scope.
Priority: P2.

Affected areas:

- `local/components/tacticum/about.page/templates/.default/parts/career-final.php`
- Bitrix page-content `career-final` rows
- `.bottom.menu.php`
- CTA copy and anchors on `/about/`

Acceptance criteria:

- Final CTA describes a concrete next step: assessment, architecture call, pilot scope or team discussion.
- Generic phrase `достичь новых высот` is removed.
- Career/culture content either has a real `#careers` section and purpose, or is removed/renamed from public navigation.
- CTA changes do not modify form payload, hidden fields or analytics taxonomy.

Verification:

```bash
git diff --check
npm run seo:check
```

Implementation note 07.06.2026: production fast-fix removed generic final CTA phrase and exposes a real `#careers` target. Career/culture role and buyer-trust transition remain owner-review scope.

Implementation note 07.06.2026: footer now points to `/about/#start-work`, final CTA uses first-step assessment framing, and `#careers` remains as a compatibility alias. Production cache clear/rendered evidence passed at `2026-06-07T08:49:12Z`; final career strategy decision remains pending.

### ABOUT-WP-07 — Page-Content / Fallback Ownership And Sync

Workflow lane: Full Feature with content-storage gate.
Priority: P1.

Affected areas:

- `local/components/tacticum/about.page/templates/.default/parts/company-trust.php`
- `local/components/tacticum/about.page/templates/.default/parts/values-team.php`
- `local/components/tacticum/about.page/templates/.default/parts/stack-cta.php`
- `local/components/tacticum/about.page/templates/.default/parts/career-final.php`
- `local/lib/Tacticum/PageContent/Renderer.php`
- `local/php_interface/include/page_content.php`
- `tools/content-storage-page-content-seed.php`
- Bitrix `page_sections #24` and `page_blocks #25` rows for `/about/`

Acceptance criteria:

- Each `/about/` section has a documented owner: Bitrix live row, PHP partial, local component or removed fallback.
- `fallback retired` comments match actual render behavior.
- Seed, live rows and rendered output are aligned after cache clear.
- No fallback bodies are silently reintroduced or left active by mistake.
- Page-content audit/source marker checks pass for the intended source mode.

Verification:

```bash
git diff --check
npm run content:storage:governance:check
npm run seo:check
```

Add production page-content source/audit/cache-clear commands when implementation touches Bitrix rows.

Implementation note 07.06.2026: actual mixed-source ownership map is prepared for owner review. It separates hardcoded PHP partials, Bitrix live page-content sections, team iblock output, lead CTA component, footer menu and `PublicCopyNormalizer` compatibility. No Bitrix row sync, source-of-truth transfer, fallback comment cleanup or fallback retirement is approved by this note.

### ABOUT-WP-08 — Guard / Smoke Package

Workflow lane: Full Feature / QA guard.
Priority: P2.

Affected areas:

- `tools/public-content-hygiene-check.mjs`
- `tools/public-content-rendered-hygiene-check.mjs`
- possible new about-specific tool under `tools/`
- `package.json` scripts if a new command is added
- release sign-off evidence schema only if new gate is promoted

Acceptance criteria:

- Guard or checklist catches stale timeline/current contradiction on `/about/`.
- Guard or checklist catches duplicate rendered IDs and missing `/about/#...` targets.
- Guard or checklist catches misleading footer anchor mapping for `#partners` unless explicitly allowed.
- Output is safe: no PII, no raw page dump, only URL/status/issues/counts.
- Guard has self-test if implemented as automated script.

Verification:

```bash
git diff --check
npm run content:public-hygiene:self-test
npm run content:public-hygiene:check
npm run content:public-hygiene:rendered:self-test
npm run seo:check
```

Add specific new script self-test/check commands if introduced.

Implementation note 07.06.2026: existing `content:public-hygiene:check` and `content:public-hygiene:rendered:self-test` were extended instead of adding a separate tool. The rendered guard now covers stale timeline, duplicate IDs, missing `/about/#...` anchors, misleading `#partners` and about-specific visible internal phrases. Production rendered check passed after public cache clear at `2026-06-07T08:24:11Z` with `issues_found=0`.

## Do Not Combine

- Do not combine `ABOUT-WP-03` trust rewrite with `ABOUT-WP-04` visual redesign unless Design has approved both copy and layout.
- Do not combine `ABOUT-WP-07` content-storage ownership changes with copy-only fast fixes unless cache/source evidence is explicitly scoped.
- Do not combine claims/proof rewrite with timeline/anchor fixes.
- Do not combine form ID collision cleanup with form payload or endpoint changes.

## Related Documents

- `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-roadmap-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-guard-proposal-2026-06-07.md`
- `docs/workflow/about-page-proof-matrix-owner-review-2026-06-07.md`
- `docs/workflow/about-page-content-ownership-map-2026-06-07.md`
- `docs/workflow/plans/2026-06-07-about-owner-review-proof-ownership.md`
- `docs/workflow/plans/2026-06-07-about-team-ui-accessibility-slice.md`
