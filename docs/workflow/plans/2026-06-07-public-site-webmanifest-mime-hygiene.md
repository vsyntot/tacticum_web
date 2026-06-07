# Codex Plan: Public Site Webmanifest MIME Hygiene

Issue: `PUBLIC-E2E-WP-04`
Gap ID: `PUBLIC-E2E-010`
Workflow lane: Fast Fix Lane
Owner agent: Codex
Date: 2026-06-07

## Goal

Make the public web manifest return an explicit manifest-compatible content type and add a repeatable guard so the issue is not missed by future SEO checks.

## Non-Goals

- No `robots.txt` change.
- No sitemap, canonical, redirect or indexability change.
- No manifest URL change.
- No nginx/server package change in repo; if `.htaccess` is ignored by production static serving, DevOps must add the MIME mapping server-side.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] Relevant files: `.htaccess`, `robots.txt`, `tools/seo-check.mjs`, `local/templates/tacticum/images/site.webmanifest`

## Current Behavior

`robots.txt` points to the HTTPS sitemap and is not the source of this gap. The remaining SEO hygiene issue is that `/local/templates/tacticum/images/site.webmanifest` can return HTTP 200 without an explicit `Content-Type`.

## Target Behavior

- Apache-compatible environments map `.webmanifest` to `application/manifest+json`.
- `npm run seo:check` guards the repo MIME hint.
- `npm run seo:check:prod` validates the real production `Content-Type`.

## Planned Changes

| File | Change |
|---|---|
| `.htaccess` | Add `mod_mime` mapping for `.webmanifest`. |
| `tools/seo-check.mjs` | Add local `.htaccess` guard and production HTTP HEAD content-type guard. |
| `docs/workflow/*` | Mark `PUBLIC-E2E-010` as implemented and then closed by production evidence. |

## Risks

| Risk | Mitigation |
|---|---|
| Production static files are served by nginx before Apache | `seo:check:prod` will fail with the actual content type; then fix belongs to server MIME config. |
| Release-blocking P3 check surprises deploy | Keep evidence explicit in docs and run `seo:check:prod` after deploy/cache clear. |

## Verification

### Automated

```bash
node --check tools/seo-check.mjs
npm run seo:check
git diff --check
```

### Post-Deploy

```bash
npm run seo:check:prod
```

Post-deploy evidence 2026-06-07:

- `npm run content:public-cache-clear`: passed.
- `npm run seo:check:prod`: passed, including the production manifest `Content-Type` guard.
- `npm run content:public-hygiene:rendered:prod:json`: passed at `2026-06-07T20:37:59Z`, `pages_checked=13`, `issues_found=0`.

`PUBLIC-E2E-010` is closed. If the production manifest content type regresses, `seo:check:prod` should fail and the likely fix is server/nginx MIME configuration.

## Rollback

Revert the `.htaccess`, `tools/seo-check.mjs` and docs changes from this slice. No data or Bitrix schema changes are involved.
