# Codex Plan: Product-First CI Deploy Smoke Coverage

Issue: internal product vision implementation continuation
Gap ID: `PV-013`, `PV-014`, `PV-016`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Закрыть инфраструктурный хвост product-first релиза: новые публичные разделы `/platform/`, `/agents/`, `/dev/`, `/forum/` должны попадать в PHP lint, rsync deploy, static SEO/canonical guard and rendered visual/SEO smoke coverage.

## Non-Goals

- Не менять runtime PHP/API/form behavior.
- Не запускать production smoke до deploy/cache refresh.
- Не менять `/aiagents/` canonical/redirect decision.
- Не менять Bitrix-generated sitemap artifacts in Git.

## Target Behavior

- CI/deploy treats product pages as public pages.
- `seo:check` validates product page canonical paths and product menu/footer links.
- `visual-smoke` default pages include product URLs and rendered SEO nav check expects product links along with preserved money pages.
- Deploy lifecycle draft guard also validates the product-first release draft.

## Planned Changes

| File | Change |
|---|---|
| `.github/workflows/deploy.yml` | Include product dirs in PHP lint/rsync, keep known-gaps guard and add product-first draft check |
| `.github/workflows/pr-check.yml` | Include product dirs in PHP lint/security/frontend convention scans |
| `tools/seo-check.mjs` | Add product pages to static canonical/sitemap expectations and product navigation checks |
| `tools/visual-smoke.mjs` | Add product pages to default smoke pages and rendered nav expectations |
| `docs/workflow/current-state.md` | Document CI/deploy/smoke product-first coverage |
| `docs/workflow/gap-analysis.md` | Document S07/S08 hardening progress |
| `docs/new-big-change/product-vision-handoff/sprints/sprint-07-proof-forms-seo-analytics-hardening.md` | Mark automated product URL guard coverage |

## Verification

### Actual Checks

- [x] `node --check tools/seo-check.mjs`
- [x] `node --check tools/visual-smoke.mjs`
- [x] `npm run seo:check`
- [x] `npm run bitrix:check`
- [x] `npm run template-styles:check`
- [x] `npm run js:check`
- [x] `npm run config:check`
- [x] `npm run sale:sunset:check`
- [x] `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json`
- [x] `npm run release:signoff:self-test`
- [x] `npm run gaps:known`
- [x] `TACTICUM_RELEASE_SIGNOFF=docs/workflow/release-signoff-2026-06-01-product-first.draft.json npm run gaps:known`
- [x] `git diff --check`
- [x] `php -v` / local PHP lint: не выполнен, локально нет PHP CLI (`zsh:1: command not found: php`); CI/deploy PHP 8.4 lint updated to include product dirs.

## Rollback

Remove product dirs from CI/deploy/smoke guard lists and revert docs updates. Runtime product pages can remain if a docs/tooling-only rollback is needed.
