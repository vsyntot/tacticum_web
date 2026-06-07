# Public Site E2E Challenge Roadmap

Date: 2026-06-07
Source register: `public-site-e2e-challenge-gap-analysis-2026-06-07.md`
Scope: implementation roadmap for `PUBLIC-E2E-*` gaps.

## Principles

- Fix release-hygiene defects before editorial polish.
- Preserve current page URLs, canonical rules and `/offer/` filtered `noindex,follow` policy unless SEO explicitly approves a change.
- Do not introduce public claims while editing copy.
- Do not change form payloads, REST endpoints or analytics payloads in this scope.
- Treat Russian-first terminology as Content + Architect work: copy must stay technically accurate.

## Phase 0: Documentation Baseline

Status: complete when this package is committed.

Deliverables:

- Gap analysis/source register with `PUBLIC-E2E-*` IDs.
- Roadmap with technical and editorial phases.
- Issue backlog with acceptance criteria and checks.
- Links from central workflow docs.

Covered gaps:

- All `PUBLIC-E2E-*` documented, none closed by documentation alone.

## Phase 1: Immediate Technical Hygiene

Goal: close objective release-hygiene defects found by the challenge.

Status: implemented locally on 2026-06-07; production smoke pending after deploy/cache refresh.

Work packages:

- `PUBLIC-E2E-WP-01`: add `html lang` and guard coverage.
- `PUBLIC-E2E-WP-02`: sync Tailwind generated artifact and CSS check evidence.
- `PUBLIC-E2E-WP-03`: fix static sitemap freshness flow and sitemap index lastmod decision.

Expected checks:

- `npm run js:check`
- `npm run css:check`
- `npm run css:syntax`
- `npm run template-styles:check`
- `npm run seo:check`
- `npm run sitemap:static:check` or explicit approved deploy-lastmod command
- `npm run content:public-hygiene:check`
- `npm run visual:smoke:prod` after deploy
- `npm run browser:console:prod` after deploy
- `npm run seo:check:prod` after deploy

Closure targets:

- `PUBLIC-E2E-001`: closed locally by template `lang` rendering and source/HTTP guards.
- `PUBLIC-E2E-002`: closed locally by Tailwind artifact regeneration and passing `css:check`.
- `PUBLIC-E2E-003`: closed locally by regenerating static sitemap artifact with `2026-06-07`.
- `PUBLIC-E2E-004`: closed locally by updating repo-owned `sitemap.xml` index lastmods to `2026-06-07`.
- `PUBLIC-E2E-014`: partially closed locally by `seo:check` and `release:public-precheck` lang assertions; sitemap freshness remains covered by `sitemap:static:check` and deploy workflow.

## Phase 2: SEO/HTTP Hardening

Goal: remove low-risk infrastructure hygiene issues and prevent regression.

Work packages:

- `PUBLIC-E2E-WP-04`: asset/header hygiene: webmanifest content type and optional asset audit check.
- `PUBLIC-E2E-WP-05`: release guard consolidation: ensure public precheck reports lang, sitemap freshness, critical asset status and `/offer/` sitemap sample/full mode.

Expected checks:

- `npm run release:public-precheck:prod`
- `npm run seo:check:prod`
- Asset HEAD audit or updated release precheck.
- `npm run browser:console:prod`

Closure targets:

- `PUBLIC-E2E-010`, `PUBLIC-E2E-014`.
- `PUBLIC-E2E-011` remains accepted-monitor unless Security chooses CSP enforce path.

## Phase 3: Russian-First Editorial Pass

Goal: reduce public-facing English/internal terminology without losing precision.

Work packages:

- `PUBLIC-E2E-WP-06`: public glossary/rules update for `AI/ИИ`, `LLM`, `RAG`, `runtime`, `governance`, `workflow`, `delivery`, `discovery`, `support/helpdesk`, `T&M`.
- `PUBLIC-E2E-WP-07`: product page copy pass for `/platform/`, `/dev/`, `/forum/`, with `/agents/` as monitor.
- `PUBLIC-E2E-WP-08`: services/calculator/supporting page copy pass for `/services/`, `/calculator/`, `/contacts/`, `/policies/`.

Expected checks:

- `npm run content:public-hygiene:check`
- `npm run content:public-hygiene:rendered:prod`
- `npm run seo:check`
- `npm run seo:check:prod`
- Page-content source checks if Bitrix rows are updated.

Closure targets:

- `PUBLIC-E2E-005`, `PUBLIC-E2E-009`, `PUBLIC-E2E-012`, `PUBLIC-E2E-013`.

Owner gates:

- Content owner: approves Russian-first wording.
- Architect: approves technical accuracy.
- SEO: approves title/description/canonical-sensitive copy if changed.
- Legal: approves `/policies/` structural copy if changed.

## Phase 4: Public UI Label Governance

Goal: prevent raw internal catalog labels from leaking into buyer-facing UI.

Work packages:

- `PUBLIC-E2E-WP-09`: `/price/` public short-label model for rate/role names.
- `PUBLIC-E2E-WP-10`: `/offer/` synthetic example disclosure and proof-safe framing review.

Expected checks:

- `npm run browser:smoke:price`
- `npm run browser:smoke:offer`
- `npm run content:public-hygiene:rendered:prod`
- `npm run seo:check:prod`
- Relevant Bitrix runtime checks if iblock properties/source model changes.

Closure targets:

- `PUBLIC-E2E-006`, `PUBLIC-E2E-007`.

Owner gates:

- `/price/`: PM + Sales + Content, and Engineering if rate schema changes.
- `/offer/`: PM + Content + Sales + Legal + SEO.

## Phase 5: Monitoring

Goal: keep accepted risks visible without blocking current release.

Items:

- `/aiagents/` vs `/agents/` positioning/cannibalization monitor: `PUBLIC-E2E-008`.
- CSP report-only to enforce path: `PUBLIC-E2E-011`.
- Existing legacy sale inventory external handoff remains tracked by `gaps:known`, outside this package.

Monitoring evidence:

- Search query/performance review.
- Lead source quality review.
- CSP report triage.
- Regular `browser:console:prod` and `release:public-precheck:prod`.

## Recommended Sequence

1. `PUBLIC-E2E-WP-01`: lang + guard. Status: implemented locally, pending production smoke.
2. `PUBLIC-E2E-WP-02`: Tailwind artifact sync. Status: implemented locally, pending production smoke.
3. `PUBLIC-E2E-WP-03`: sitemap freshness. Status: implemented locally, pending production smoke.
4. `PUBLIC-E2E-WP-05`: release guard consolidation. Status: partially implemented locally for lang assertions; optional sitemap consolidation remains backlog.
5. `PUBLIC-E2E-WP-06`: glossary decision.
6. `PUBLIC-E2E-WP-07` / `PUBLIC-E2E-WP-08`: editorial pass.
7. `PUBLIC-E2E-WP-09` / `PUBLIC-E2E-WP-10`: public label/proof governance.
8. Monitoring items continue without blocking implementation.
