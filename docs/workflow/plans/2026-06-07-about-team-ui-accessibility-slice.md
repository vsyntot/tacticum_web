# Codex Plan: About Team UI Accessibility Slice

Issue: `ABOUT-WP-04` team section UX/accessibility
Gap ID: `ABOUT-005`, adjacent `UI-001`, `UI-002`, `UI-005`, `UI-010`, `CMP-008`
Workflow lane: Full Feature scoped implementation / Design-gated slice
Owner agent: Codex
Date: 2026-06-07

## Goal

Improve `/about/` team cards so role, short expertise summary and available detail are readable without hover-only interaction and usable on keyboard/mobile. Keep the slice limited to rendering/accessibility of existing approved team data.

## Non-Goals

- Do not change team names, photos, roles, bios, emails or LinkedIn values.
- Do not edit Bitrix rows or iblock schema.
- Do not add claims, metrics, logos, certifications or customer proof.
- Do not change lead form payloads, analytics, routes or SEO metadata.
- Do not introduce a broad design-system/token redesign.

## Current Behavior

`local/templates/tacticum/components/bitrix/news.list/team/template.php` renders a tall photo card and hides `DETAIL_TEXT` inside `.member-overlay`. The overlay appears on mouse hover via CSS/JS, so details and contact links are not reliably discoverable for keyboard and touch users.

## Target Behavior

- Team card remains a card inside the existing `tacticum:content.list` / `news.list/team` boundary.
- Photo crop remains consistent.
- Name, role, preview and non-duplicate detail are visible in normal reading flow.
- Contact links are visible and have accessible labels.
- Existing data is escaped/sanitized through current helpers.
- Legacy `.team-member` selector remains for state/QA continuity; hover-only overlay is removed from the public-critical path.

## Planned Changes

| File | Change |
|---|---|
| `local/templates/tacticum/components/bitrix/news.list/team/template.php` | Render accessible card structure with visible detail and labelled contact actions. |
| `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md` | Record local `ABOUT-WP-04` implementation status. |
| `docs/workflow/about-page-ux-content-challenge-roadmap-2026-06-07.md` | Record Phase 3 local slice. |
| `docs/workflow/current-state.md` / `gap-analysis.md` | Record local slice without production closure. |

## Constraints

- [x] `bitrix/` not touched
- [x] No new iblock IDs
- [x] No REST/API/security behavior changes
- [x] No form or analytics payload changes
- [x] No public URL/SEO metadata changes

## Verification

```bash
php -l local/templates/tacticum/components/bitrix/news.list/team/template.php
git diff --check
npm run content:public-hygiene:check
npm run seo:check
npm run bitrix:check
```

CSS/browser smoke is recommended after deploy because the slice changes rendered card layout.

## Rollback

Revert the template and docs changes. Clear public render cache after production rollback if deployed.
