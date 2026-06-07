# Codex Plan: About Team And Readiness De-Dup Slice

Issue: `ABOUT-WP-04`, `ABOUT-WP-05`
Gap ID: `ABOUT-005`, `ABOUT-006`, adjacent `ABOUT-002`
Workflow lane: Fast Fix with Design-gate residue
Owner agent: Codex
Date: 07.06.2026

## Goal

Improve the `/about/` blocks challenged in screenshots: make the team section read as responsibility for launch, reduce portrait dominance, add a role-composition bridge, and remove duplicate technology/readiness card blocks.

## Non-Goals

- Do not change Bitrix `team` names, photos, roles, bios, emails or LinkedIn values.
- Do not change forms, lead payload, analytics taxonomy, routes, SEO metadata or Bitrix schema.
- Do not add proof claims, customer logos, metrics or certifications.
- Do not retire page-content fallbacks beyond the already approved state.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`
- [x] `docs/workflow/about-page-content-ownership-map-2026-06-07.md`

## Current Behavior

- `/about/#team` shows two large team cards under `Команда, которая ведёт запуск`; the copy implies a launch team, but the rendered data shows founders only.
- `values-team.php` renders a hardcoded `Контуры надёжного AI-запуска` card row after the team cards.
- `stack-cta.php` then renders another launch-readiness card grid with overlapping content, so the page repeats the same risk/technology story.

## Target Behavior

- Team section headline is honest about responsibility: `Кто отвечает за запуск`.
- Team cards are compact, readable and do not depend on giant portrait crops.
- A small role matrix explains that the working launch team is assembled per scenario.
- The duplicate hardcoded technology contour block is removed from `values-team.php`.
- `#technology` points to the single readiness section in `stack-cta.php`, which becomes a four-group readiness matrix with explicit client outcomes.

## Planned Changes

| File | Change |
|---|---|
| `local/components/tacticum/about.page/templates/.default/parts/values-team.php` | Update team heading/copy, remove duplicate technology cards, add role-composition matrix. |
| `local/templates/tacticum/components/bitrix/news.list/team/template.php` | Add count-aware semantic team card classes and expose preview/detail as focus/experience without changing source data. |
| `local/components/tacticum/about.page/templates/.default/parts/stack-cta.php` | Move `#technology` anchor to the single readiness section and collapse eight checklist cards into four readiness groups. |
| `local/templates/tacticum/components/bitrix/news.list/team/style.css` | Add component-owned CSS for compact team cards. |
| `local/components/tacticum/about.page/templates/.default/style.css` | Add about-page component CSS for launch role matrix and readiness matrix. |
| `docs/workflow/*` | Record local implementation and pending production evidence. |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] CSS lives in component template `style.css` files, no inline CSS
- [x] No infoblock ID changes
- [x] No POST REST/API changes
- [x] No team data changes in Bitrix

## Risks

| Risk | Mitigation |
|---|---|
| Team portrait crop still depends on uploaded source image. | Reduce photo dominance and use safer object positioning without editing images. |
| `/about/#technology` anchor could disappear. | Move `id="technology"` to `stack-cta.php` and keep `#stack` as an alias. |
| General CSS could affect other pages. | Use namespaced `tacticum-*` classes and run CSS/template/source guards. |
| Production cache can serve stale component HTML. | Require public cache clear and rendered/source checks after deploy. |

## Verification

### Automated

```bash
php -l local/components/tacticum/about.page/templates/.default/parts/values-team.php
php -l local/templates/tacticum/components/bitrix/news.list/team/template.php
php -l local/components/tacticum/about.page/templates/.default/parts/stack-cta.php
npm run css:check
npm run css:syntax
npm run template-styles:check
npm run content:public-hygiene:check
npm run content:public-hygiene:rendered:self-test
npm run seo:check
npm run bitrix:check
npm run component:states:check
```

### Manual Smoke

- URL: `/about/`
- Check: team section, role matrix, `#technology` anchor, readiness matrix on desktop and mobile.
- Expected: no duplicate technology contour block, no runtime errors, anchors resolve.

## Rollback

Revert this slice in the changed PHP/CSS files and clear public render cache. No Bitrix data rollback is required because no Bitrix rows are changed.

## Docs To Update

- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`
- [x] `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md`
- [x] `docs/workflow/about-page-ux-content-challenge-roadmap-2026-06-07.md`
- [x] `docs/workflow/about-page-content-ownership-map-2026-06-07.md`
