# Codex Plan: Public Release Precheck SEO Surface Guard

Issue: `PUBLIC-E2E-WP-05`
Gap ID: `PUBLIC-E2E-014`
Workflow lane: Fast Fix Lane
Owner agent: Codex
Date: 2026-06-07

## Goal

Make `release:public-precheck:prod` catch the public SEO surface issues that were previously split across separate manual/SEO checks: `robots.txt`, root sitemap, static sitemap, offer sitemap and webmanifest headers.

## Non-Goals

- No `robots.txt` content change.
- No sitemap URL, canonical, redirect or indexability policy change.
- No dynamic sitemap full HEAD crawl; `seo:check:prod` remains the deeper SEO guard.
- No browser/Chrome dependency.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] Relevant files: `robots.txt`, `sitemap.xml`, `tools/release-public-precheck.mjs`

## Current Behavior

`release:public-precheck:prod` already checked config health, public page `lang=ru`, product source markers, Metrika, admin surface and legacy alias headers. Robots/sitemap/manifest checks were covered by `seo:check:prod`, not by the release public precheck itself.

## Target Behavior

`release:public-precheck:prod` additionally reports:

- `robots_txt`
- `sitemap_index`
- `static_sitemap`
- `offer_sitemap`
- `webmanifest`

## Planned Changes

| File | Change |
|---|---|
| `tools/release-public-precheck.mjs` | Add HTTP-only SEO surface checks for robots, sitemap index, static sitemap, offer sitemap and webmanifest content type. |
| `docs/workflow/*` | Mark optional release guard consolidation as closed by production precheck evidence. |

## Risks

| Risk | Mitigation |
|---|---|
| Precheck becomes too slow | Check only sitemap inventories and offer sitemap loc ownership, not full offer URL HEAD crawl. |
| Duplicating `seo:check:prod` | Keep this as release critical-surface guard; `seo:check:prod` remains deeper SEO validation. |
| Chrome/server dependency | No browser dependency; HTTP-only checks. |

## Verification

### Automated

```bash
node --check tools/release-public-precheck.mjs
npm run release:public-precheck:prod
```

Production evidence 2026-06-07:

- `OK robots_txt status=200 sitemap=ok`
- `OK sitemap_index status=200 sitemaps=2`
- `OK static_sitemap status=200 urls=13 missing=0`
- `OK offer_sitemap status=200 urls=1118`
- `OK webmanifest status=200 type=application/manifest+json`

## Rollback

Revert the `tools/release-public-precheck.mjs` and docs changes from this slice. Public runtime, routes and SEO files are unchanged.
