# About Page UX / Content Challenge Guard Proposal — 2026-06-07

Дата: 07.06.2026

Статус: guard proposal; implemented through existing public content hygiene tools on 07.06.2026, production rendered evidence passed.
Source register: `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`

## Purpose

Этот документ фиксирует минимальный guard/smoke design для дефектов, найденных на `/about/`: stale timeline, duplicate IDs, missing anchors, misleading footer anchors and internal public language. Его можно использовать как acceptance basis для `ABOUT-WP-08`.

## Why A Guard Is Needed

The current `content:public-hygiene:*` checks already catch visible internal labels from product/page-content scope. They passed production after the previous content hotfix. However the `/about/` challenge exposed a different class of defects:

- temporal contradiction: `2025` badge with `Сегодня` heading on 2026-06-07;
- structural HTML issue: duplicate `id="about-company"`;
- navigation issue: footer link `/about/#careers` without a rendered target;
- semantic anchor issue: `/about/#partners` points to `Технологические контуры`;
- page-specific internal terms that are not covered as hard blockers globally.

These defects are easy to reintroduce through Bitrix page-content rows, PHP partials or footer menu edits, so they need either automated checks or an explicit release checklist.

## Proposed Checks

| Guard ID | Type | Scope | Failure Condition | Related Gaps |
|---|---|---|---|---|
| `ABOUT-GUARD-01` | rendered text | `/about/` | Visible text contains a year badge from a past year near heading `Сегодня`, or the exact stale pair `2025` + `Сегодня` remains after fix | `ABOUT-001`, `ABOUT-010` |
| `ABOUT-GUARD-02` | rendered HTML | `/about/` | Any rendered `id` attribute appears more than once | `ABOUT-007`, `ABOUT-010` |
| `ABOUT-GUARD-03` | rendered HTML | `/about/` | Any internal `/about/#anchor` link points to a missing rendered `id` or explicit alias target | `ABOUT-007`, `ABOUT-010` |
| `ABOUT-GUARD-04` | rendered semantic check | `/about/` | Footer link text/target says partners but target section heading is not about partners/trust ecosystem | `ABOUT-007`, `ABOUT-010` |
| `ABOUT-GUARD-05` | source text | about partials + page-content seed | Static source contains banned stale combo `2025` and `Сегодня` in same timeline item | `ABOUT-001`, `ABOUT-010` |
| `ABOUT-GUARD-06` | rendered/source content hygiene | `/about/` | Public headings/cards expose agreed internal terms after glossary approval | `ABOUT-003`, `ABOUT-006`, `ABOUT-010` |
| `ABOUT-GUARD-07` | release evidence | safe JSON output | Evidence output includes raw page HTML, form values, cookies or PII-like keys | `REL-002`, `ABOUT-010` |

Implementation note 07.06.2026: Option A was selected. `tools/public-content-hygiene-check.mjs` now checks `/about/` source partials, footer menu and page-content seed for stale or misleading source patterns. `tools/public-content-rendered-hygiene-check.mjs` now checks rendered `/about/` for stale `2025 + Сегодня`, duplicate IDs, missing `/about/#...` targets, misleading `#partners` target and about-specific internal visible phrases. Self-tests pass locally. Production rendered evidence passed after public cache clear: `content:public-hygiene:rendered:prod:json` reports `checked_at=2026-06-07T08:24:11Z`, `pages_checked=13`, `issues_found=0`.

## Production Evidence

| Command / Evidence | Result |
|---|---|
| `npm run content:public-cache-clear`, production 07.06.2026 | Passed; cleared public rendered/menu/component/composite/template cache after deploy. |
| `npm run content:public-hygiene:rendered:prod:json`, production 07.06.2026 | Passed at `2026-06-07T08:24:11Z`; 13 pages checked, 0 issues. |
| `npm run page-content:source:http:wave2:prod`, production 07.06.2026 | Passed; `/about/ source=bitrix sections=3/3 bytes=74964`. |

## Recommended Implementation Options

### Option A — Extend Existing Rendered Hygiene Guard

Extend `tools/public-content-rendered-hygiene-check.mjs` with page-specific checks for `/about/`.

Pros:

- Reuses existing production command and release sign-off path.
- Already has source/rendered self-test pattern.
- Fits previous `CLS-WP-01` and `CLS-WP-06` hygiene work.

Cons:

- Existing tool is broad public-content hygiene; about-specific semantic checks can make it more complex.
- Need careful allowlist for approved technical terms.

Suggested scripts if extended:

```bash
npm run content:public-hygiene:rendered:self-test
npm run content:public-hygiene:rendered:prod
npm run content:public-hygiene:rendered:prod:json
```

### Option B — Add About-Specific Rendered Guard

Add a focused tool, for example `tools/about-page-rendered-check.mjs`, and package scripts:

```json
{
  "about:rendered:check": "node ./tools/about-page-rendered-check.mjs",
  "about:rendered:prod": "TACTICUM_PUBLIC_CONTENT_BASE_URL=https://tacticum.ru node ./tools/about-page-rendered-check.mjs",
  "about:rendered:self-test": "node ./tools/about-page-rendered-check.mjs --self-test"
}
```

Pros:

- Keeps page-specific semantics out of global content hygiene.
- Easier to evolve as `/about/` gets redesigned.

Cons:

- Adds another script to maintain.
- Needs explicit release lifecycle wiring if it becomes required.

### Option C — Manual Release Checklist First

For a fast fix, use a manual checklist in the issue until automation is implemented.

Required checklist:

- Rendered `/about/` has no `2025 / Сегодня` stale pairing.
- Rendered IDs are unique.
- Footer `/about/#...` links all resolve to existing targets.
- `#partners` label either matches actual section or is removed/renamed.
- CTA no longer uses `достичь новых высот`.

Pros:

- Fastest path for immediate production hygiene.

Cons:

- Does not prevent recurrence.
- Should not be accepted as final closure for `ABOUT-010` unless PM/QA explicitly accept manual monitoring.

## Rendered Evidence Shape

If automated, output should be safe JSON similar to existing content hygiene evidence:

```json
{
  "command": "npm run about:rendered:prod",
  "base_url": "https://tacticum.ru",
  "checked_at": "2026-06-07T00:00:00Z",
  "checked_by": "automated-about-rendered-check",
  "page": "/about/",
  "status": 200,
  "issues_found": 0,
  "result": "about rendered check passed",
  "checks": {
    "stale_timeline": "passed",
    "duplicate_ids": "passed",
    "anchors": "passed",
    "semantic_footer_anchors": "passed",
    "public_language": "passed"
  }
}
```

Do not include raw HTML, cookies, form values, IPs, user agents or full page text.

## Suggested Self-Test Fixtures

| Fixture | Expected Result |
|---|---|
| HTML with `2025` timeline badge and `Сегодня` heading | fail `stale_timeline` |
| HTML with duplicate `id="about-company"` | fail `duplicate_ids` |
| HTML with link `/about/#careers` and no `id="careers"` | fail `anchors` |
| HTML with `/about/#partners` pointing to heading `Технологические контуры` | fail `semantic_footer_anchors` unless explicitly allowed |
| HTML with unique IDs, valid anchors and separated `2025` milestone/current focus | pass |

## Banned Or Review Terms For `/about/`

These should be treated as review terms, not universal hard blockers, until PM/Content/SEO approve the glossary exceptions:

| Term | Suggested Public Alternative |
|---|---|
| `product-first` | продуктовый подход, продуктовая модель |
| `delivery` | внедрение, проектная поставка, команда внедрения |
| `backend` | серверная разработка, интеграционный слой |
| `data/RAG` | работа с данными и поиском по базе знаний |
| `quality gates` | контрольные проверки качества |
| `production rollout` | запуск в рабочую эксплуатацию |
| `scope` | границы работ, состав пилота |

## Non-Goals

- Do not make a global ban on all English terms across the site from this about-specific guard.
- Do not parse or validate Bitrix admin data directly in rendered guard.
- Do not require browser/Chrome for these checks; HTTP HTML parsing is enough.
- Do not assert exact visual layout; use visual smoke separately for UI redesign.
- Do not inspect form submissions or PII.

## Adoption Recommendation

1. Use manual checklist for `ABOUT-WP-01` and `ABOUT-WP-02` if a fast production fix is needed immediately.
2. Implement `ABOUT-GUARD-01` to `ABOUT-GUARD-03` first; they are objective and low-noise.
3. Add `ABOUT-GUARD-04` after footer/section semantics are decided.
4. Add `ABOUT-GUARD-06` only after Russian-first glossary terms are approved for `/about/`.
5. Promote safe JSON evidence into release sign-off only after self-tests and one production pass.

## Related Documents

- `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-roadmap-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/content-language-storyline-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/release-signoff-gates.md`
