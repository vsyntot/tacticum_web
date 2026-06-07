# Codex Plan: Public Site E2E Challenge Documentation

Date: 2026-06-07
Workflow lane: Fast Fix / Audit documentation
Scope: docs-only package for production public-site E2E challenge results.

## Goal

Document the 2026-06-07 production E2E challenge of `tacticum.ru` across JS/CSS, public content, SEO infrastructure, sitemap/robots, browser smoke and page-by-page editorial review.

## Business Impact

The site is currently technically healthy on production, but several release-hygiene and editorial gaps can silently reduce accessibility, SEO trust and Russian-first clarity. The package keeps those issues visible and issue-ready before implementation.

## Inputs

- `npm run js:check` passed: 73 files.
- `npm run css:syntax` passed: 13 runtime CSS files.
- `npm run template-styles:check` passed.
- `npm run bitrix:check` passed.
- `npm run component:states:check` passed.
- `npm run content:public-hygiene:check` passed.
- `npm run product:content:safety:check` passed.
- `npm run seo:check` and `npm run seo:check:prod` passed.
- `npm run content:public-hygiene:rendered:prod:json` passed at `2026-06-07T15:31:44Z`: 13 pages, 0 issues.
- `npm run page-content:source:http:prod` and `npm run page-content:source:http:wave2:prod` passed.
- `npm run product:source:http:prod` passed for `/platform/`, `/agents/`, `/dev/`, `/forum/` with `source=bitrix`, `faq_source=iblock`, `proof_source=iblock`.
- `npm run visual:smoke:prod` passed on all 13 pages, desktop and mobile.
- `npm run browser:console:prod` passed on all 13 pages, desktop and mobile.
- `npm run browser:smoke:price` passed on `/price/`, desktop and mobile, with `TACTICUM_EXPECT_PRICE_TEAM_PRESETS=1`.
- `npm run browser:smoke:offer` passed on `/offer/`, desktop and mobile.
- `npm run release:public-precheck:prod` passed.
- `npm run release:manual-gates:helper` reported no pending manual gates.
- `npm run gaps:known` reported code-level open/in-progress gaps `0`, external release gates pending `0`, post-deploy/cache smoke pending `0`, with only legacy sale inventory external handoff still tracked.

## Findings To Document

- Missing `<html lang="ru">` on all 13 public pages.
- `npm run css:check` fails because generated Tailwind output differs from committed `tailwind.generated.css`; generated output includes `.border-primary/30` absent from the current artifact.
- `npm run sitemap:static:check` fails because generated static sitemap expects current lastmod `2026-06-07`, while current `sitemap-basic-files.xml` artifact has `2026-06-05`.
- `sitemap.xml` index still has `lastmod=2026-05-24` for sitemap files, which is stale after current page/content changes.
- `robots.txt`, `sitemap.xml`, `sitemap-basic-files.xml` and `/offer/sitemap.php` are reachable on production.
- Full `/offer/sitemap.php` HEAD crawl passed: 1118 URLs, 1118 status 200, 0 issues.
- Filtered `/offer/` states preserve `noindex,follow` and canonical `/offer/`.
- Page-by-page content review found Russian-first terminology debt on product/technical pages, public role-label debt on `/price/`, synthetic-example framing risk on `/offer/`, `/aiagents/` vs `/agents/` positioning monitor, and light content polish opportunities for `/contacts/` and `/policies/`.

## Artifacts

- `docs/workflow/public-site-e2e-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/public-site-e2e-challenge-roadmap-2026-06-07.md`
- `docs/workflow/public-site-e2e-challenge-issue-backlog-2026-06-07.md`

## Acceptance Criteria

- Gap register has stable `PUBLIC-E2E-*` IDs with status, priority, evidence and closure criteria.
- Roadmap separates immediate technical hygiene, guard hardening, editorial owner-review and monitoring.
- Issue backlog is implementation-ready and maps each work package to gap IDs and required checks.
- Main workflow docs link the new package.
- No code/runtime changes are made in this docs-only scope.
