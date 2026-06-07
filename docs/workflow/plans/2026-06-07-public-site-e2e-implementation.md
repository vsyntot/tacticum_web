# Codex Plan: Public Site E2E Hygiene Implementation

Date: 2026-06-07
Workflow lane: Fast Fix Lane
Source package: `public-site-e2e-challenge-gap-analysis-2026-06-07.md`
Scope: implement ready technical hygiene gaps from the public-site E2E challenge.

## Goal

Close the immediate implementation-ready `PUBLIC-E2E-*` gaps that do not require owner proof/copy/legal decisions:

- `PUBLIC-E2E-001`: public HTML language declaration.
- `PUBLIC-E2E-002`: Tailwind generated artifact drift.
- `PUBLIC-E2E-003`: static sitemap freshness drift.
- `PUBLIC-E2E-004`: sitemap index freshness, local/repo side.
- `PUBLIC-E2E-014`: guard coverage for public HTML language and related release hygiene.

## Non-Goals

- No public copy rewrite.
- No `/price/` rate/role schema change.
- No `/offer/` synthetic disclosure/proof wording change.
- No CSP enforce rollout.
- No server/nginx MIME configuration change for `site.webmanifest`.
- No form, REST, analytics or Bitrix schema changes.

## Implementation Steps

1. Add safe `lang` rendering to `local/templates/tacticum/header.php` using Bitrix `LANGUAGE_ID` with fallback `ru`.
2. Extend `tools/seo-check.mjs` to guard source template language declaration and production `lang=ru` under HTTP mode.
3. Extend `tools/release-public-precheck.mjs` to fail production release precheck when any public page misses `<html lang="ru">`.
4. Rebuild `local/templates/tacticum/tailwind.generated.css` through `npm run css:build`.
5. Regenerate local ignored `sitemap-basic-files.xml` with `lastmod=2026-06-07` and update repo-owned `sitemap.xml` index lastmods to `2026-06-07`.
6. Update docs statuses/evidence for local implementation and post-deploy gates.

## Verification

Local checks expected to pass before deploy:

- `php -l local/templates/tacticum/header.php`
- `npm run css:check`
- `npm run css:syntax`
- `npm run js:check`
- `npm run sitemap:static:check`
- `npm run seo:check`
- `npm run bitrix:check`
- `npm run content:public-hygiene:check`
- `npm run component:states:check`
- `git diff --check`

Post-deploy checks expected to pass only after deploy/cache refresh:

- `npm run content:public-cache-clear`
- `npm run seo:check:prod`
- `npm run release:public-precheck:prod`
- `npm run visual:smoke:prod`
- `npm run browser:console:prod`
- `npm run content:public-hygiene:rendered:prod:json`

## Rollback

- Revert template/tooling/sitemap/CSS changes in the same commit if production smoke fails.
- If only static sitemap artifact is stale on production, rerun deploy sitemap generation/cache workflow rather than changing runtime code.
