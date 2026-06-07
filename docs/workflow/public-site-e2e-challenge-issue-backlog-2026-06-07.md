# Public Site E2E Challenge Issue Backlog

Date: 2026-06-07
Source register: `public-site-e2e-challenge-gap-analysis-2026-06-07.md`
Roadmap: `public-site-e2e-challenge-roadmap-2026-06-07.md`

## Board Summary

| ID | Status | Priority | Owner Gate | Gap IDs | Title |
|---|---|---:|---|---|---|
| `PUBLIC-E2E-WP-01` | closed | P1 | Engineering + QA | `PUBLIC-E2E-001`, `PUBLIC-E2E-014` | Add public HTML language declaration and guard |
| `PUBLIC-E2E-WP-02` | closed with server-Chrome caveat | P1 | Engineering + QA | `PUBLIC-E2E-002` | Sync Tailwind generated artifact and CSS evidence |
| `PUBLIC-E2E-WP-03` | closed | P1 | Engineering + SEO | `PUBLIC-E2E-003`, `PUBLIC-E2E-004` | Fix static sitemap freshness and index lastmod policy |
| `PUBLIC-E2E-WP-04` | closed | P3 | Engineering / DevOps | `PUBLIC-E2E-010` | Fix webmanifest response type hygiene |
| `PUBLIC-E2E-WP-05` | closed for lang guard | P2 | Engineering + QA + SEO | `PUBLIC-E2E-014` | Consolidate release public E2E guard coverage |
| `PUBLIC-E2E-WP-06` | owner-review | P2 | Content + Architect | `PUBLIC-E2E-005` | Approve Russian-first public glossary rules |
| `PUBLIC-E2E-WP-07` | blocked-owner | P2 | Content + Architect + SEO | `PUBLIC-E2E-005`, `PUBLIC-E2E-009` | Rewrite jargon-heavy product pages |
| `PUBLIC-E2E-WP-08` | blocked-owner | P3 | Content + Legal where needed | `PUBLIC-E2E-012`, `PUBLIC-E2E-013` | Polish supporting page content |
| `PUBLIC-E2E-WP-09` | owner-review | P2 | PM + Sales + Content + Engineering | `PUBLIC-E2E-006` | Govern public short labels for `/price/` roles |
| `PUBLIC-E2E-WP-10` | blocked-owner | P1 | PM + Content + Sales + Legal + SEO | `PUBLIC-E2E-007` | Strengthen synthetic `/offer/` example disclosure |
| `PUBLIC-E2E-WP-11` | accepted-monitor | P2 | PM + SEO + Analytics | `PUBLIC-E2E-008` | Monitor `/aiagents/` vs `/agents/` positioning |
| `PUBLIC-E2E-WP-12` | accepted-monitor | P2 | Security + QA + DevOps | `PUBLIC-E2E-011` | CSP report-only to enforce monitoring |

## PUBLIC-E2E-WP-01: Add Public HTML Language Declaration And Guard

Status: closed
Priority: P1
Workflow lane: Fast Fix Lane
Affected areas: `local/templates/tacticum/header.php`, SEO/release guard tooling.

Problem:

All public pages render `<html>` without `lang`. This is a site-wide accessibility and SEO semantics gap for a Russian-first site.

Acceptance criteria:

- Public template renders `lang="ru"` or a safe Bitrix-derived Russian language value.
- Production crawl confirms all 13 public pages expose `lang=ru` on `<html>`.
- Guard coverage is added to prevent regression, preferably in `seo:check` or release precheck.
- No page titles, canonical, form behavior or Bitrix content source changes.

Implementation evidence 2026-06-07:

- `local/templates/tacticum/header.php` renders safe `lang` from Bitrix `LANGUAGE_ID` with fallback `ru`.
- `tools/seo-check.mjs` guards source and HTTP-mode `lang=ru`.
- `tools/release-public-precheck.mjs` checks `lang=ru` on all 13 public pages.
- Local checks passed: `php -l local/templates/tacticum/header.php`, `npm run seo:check`, `npm run bitrix:check`, `npm run content:public-hygiene:check`.

Closure evidence:

- Closed by production deploy/cache refresh, `npm run seo:check:prod` and `npm run release:public-precheck:prod`.
- Production `release:public-precheck:prod` on 2026-06-07 confirmed `lang=ru` for 13/13 public pages.

Required checks:

- `npm run seo:check`
- `npm run bitrix:check`
- `npm run content:public-hygiene:check`
- `npm run visual:smoke:prod`
- `npm run browser:console:prod`
- `npm run seo:check:prod`

## PUBLIC-E2E-WP-02: Sync Tailwind Generated Artifact And CSS Evidence

Status: closed with server-Chrome caveat
Priority: P1
Workflow lane: Fast Fix Lane
Affected areas: `local/templates/tacticum/assets/src/tailwind.css`, `local/templates/tacticum/tailwind.generated.css`, CSS build/check workflow.

Problem:

`npm run css:check` fails because generated Tailwind output differs from the committed artifact. The challenge observed missing generated utility `.border-primary/30` in the current artifact.

Acceptance criteria:

- `npm run css:check` passes.
- `npm run css:syntax` passes.
- Generated CSS change is reviewed as artifact sync, not an unbounded visual redesign.
- Browser smoke remains clean after deploy/cache clear.

Implementation evidence 2026-06-07:

- `npm run css:build` regenerated `local/templates/tacticum/tailwind.generated.css`.
- `npm run css:check` and `npm run css:syntax` pass locally.

Closure evidence:

- Production HTTP/SEO/content checks passed after deploy/cache refresh.
- Production-server visual/browser smoke is environment-blocked by missing Chrome/Chromium. Use a Chrome-capable local/CI runner or install Chrome/Chromium and set `CHROME_PATH` before treating this as a browser runtime gate.

Required checks:

- `npm run css:check`
- `npm run css:syntax`
- `npm run template-styles:check`
- `npm run visual:smoke:prod`
- `npm run browser:console:prod`

## PUBLIC-E2E-WP-03: Fix Static Sitemap Freshness And Index Lastmod Policy

Status: closed
Priority: P1
Workflow lane: Fast Fix Lane with SEO owner review for index policy
Affected areas: `sitemap.xml`, deploy-generated `sitemap-basic-files.xml`, `tools/static-sitemap-generate.mjs`, SEO runbook/CI if needed.

Problem:

`npm run sitemap:static:check` fails because the generated static sitemap expects `lastmod=2026-06-07`, while the current artifact has `2026-06-05`. `sitemap.xml` also has `lastmod=2026-05-24` for sitemap files.

Acceptance criteria:

- Static sitemap freshness check has an explicit, reproducible rule.
- Production `sitemap-basic-files.xml` lastmod matches the current public release or an SEO-approved stable value.
- SEO owner decides whether sitemap index lastmod should update with content releases.
- `seo:check:prod` passes.

Implementation evidence 2026-06-07:

- Local ignored `sitemap-basic-files.xml` regenerated with `lastmod=2026-06-07`.
- Repo-owned `sitemap.xml` index lastmods updated to `2026-06-07T00:00:00+03:00`.
- `npm run sitemap:static:check` and `npm run seo:check` pass locally.

Closure evidence:

- Closed by production deploy/cache refresh and `npm run seo:check:prod`.

Required checks:

- `npm run sitemap:static:check` or explicit approved `TACTICUM_STATIC_SITEMAP_LASTMOD=... npm run sitemap:static:check`
- `npm run seo:check`
- `npm run seo:check:prod`
- HTTP check for `/robots.txt`, `/sitemap.xml`, `/sitemap-basic-files.xml`, `/offer/sitemap.php`

## PUBLIC-E2E-WP-04: Fix Webmanifest Response Type Hygiene

Status: closed
Priority: P3
Workflow lane: Fast Fix Lane / DevOps if server MIME config is needed
Affected areas: static server MIME config or manifest asset location.

Problem:

`site.webmanifest` returns 200 but HEAD audit showed no explicit content type.

Acceptance criteria:

- Manifest returns `application/manifest+json` or an accepted equivalent content type.
- Browser smoke remains clean.

Implementation evidence 2026-06-07:

- `robots.txt` was checked and left unchanged: current crawl/sitemap policy is already correct.
- `.htaccess` maps `.webmanifest` to `application/manifest+json` through `mod_mime`.
- `tools/seo-check.mjs` checks the local MIME hint and validates production manifest `Content-Type` in HTTP mode.
- Local checks passed: `node --check tools/seo-check.mjs`, `npm run seo:check`.

Closure evidence:

- Closed by production deploy/cache refresh and `npm run seo:check:prod`.
- Production rendered hygiene JSON also passed at `2026-06-07T20:37:59Z` with `pages_checked=13`, `issues_found=0`.
- If production later returns empty/wrong `Content-Type`, close the regression through nginx/server MIME config rather than PHP/template code.

Required checks:

- `npm run seo:check`
- `npm run seo:check:prod`
- `npm run browser:console:prod`.

## PUBLIC-E2E-WP-05: Consolidate Release Public E2E Guard Coverage

Status: closed for lang guard; backlog for optional sitemap consolidation
Priority: P2
Workflow lane: Fast Fix Lane
Affected areas: `tools/seo-check.mjs`, `tools/release-public-precheck.mjs`, package scripts if needed.

Problem:

The challenge caught issues manually that existing green guards did not catch together: missing `<html lang>` and static sitemap drift.

Acceptance criteria:

- Release/precheck tooling asserts public HTML language declaration.
- Release/precheck either validates static sitemap freshness or documents the explicit lastmod bypass contract.
- Optional safe mode can sample or fully check `/offer/sitemap.php` URLs without overloading production.
- Existing release checks remain deterministic.

Implementation evidence 2026-06-07:

- `tools/seo-check.mjs` now checks source template language declaration and HTTP-mode public page `lang=ru`.
- `tools/release-public-precheck.mjs` now checks `lang=ru` on all 13 public pages.
- Production `npm run seo:check:prod` and `npm run release:public-precheck:prod` passed after deploy/cache refresh.
- Static sitemap artifact freshness remains covered by `npm run sitemap:static:check` and deploy workflow generation/check; optional consolidation into `release-public-precheck` remains a future hardening task.

Required checks:

- `npm run seo:check`
- `npm run release:public-precheck:prod`
- `npm run content:public-hygiene:rendered:prod`
- `npm run browser:console:prod`

## PUBLIC-E2E-WP-06: Approve Russian-First Public Glossary Rules

Status: owner-review
Priority: P2
Workflow lane: Full Feature Lane only if broad copy changes are included; docs/content lane otherwise
Affected areas: public glossary docs, product/page-content copy.

Problem:

Pages overuse English/internal terms: `AI`, `LLM`, `RAG`, `runtime`, `governance`, `workflow`, `delivery`, `discovery`, `support`, `helpdesk`, `T&M`, `MVP`.

Acceptance criteria:

- Content + Architect approve which terms stay in English and which get Russian equivalents/explanations.
- Glossary is linked from future content-editing checklist.
- No unsupported technical simplification or public claims are introduced.

Required checks:

- `npm run content:public-hygiene:check`
- `npm run product:public-claims:check`

## PUBLIC-E2E-WP-07: Rewrite Jargon-Heavy Product Pages

Status: blocked-owner
Priority: P2
Workflow lane: Full Feature Lane if page copy changes are broad
Affected pages: `/platform/`, `/dev/`, `/forum/`, monitor `/agents/`.

Problem:

Product pages are technically correct but often written for internal/technical readers, not necessarily business decision makers.

Acceptance criteria:

- H1/H2/storyline remain product-specific and SEO-safe.
- Technical terms are reduced or explained according to approved glossary.
- `SoftwareApplication` JSON-LD remains valid.
- Product source remains Bitrix.

Required checks:

- `npm run product:source:http:prod`
- `npm run content:public-hygiene:rendered:prod`
- `npm run seo:check:prod`
- `npm run visual:smoke:prod`

## PUBLIC-E2E-WP-08: Polish Supporting Page Content

Status: blocked-owner
Priority: P3
Workflow lane: Fast Fix Lane for small copy changes; Full Feature Lane if structure changes
Affected pages: `/contacts/`, `/policies/`, optional `/calculator/`.

Problem:

Supporting pages are technically clean but can better guide users: response expectations on contacts, legal document navigation on policies, and preliminary-estimate framing on calculator.

Acceptance criteria:

- `/contacts/` sets clear next-step expectations without changing form contract.
- `/policies/` gets legal-approved headings/anchors if needed.
- `/calculator/` keeps preliminary estimate framing clear.
- No new legal or delivery guarantees are introduced.

Required checks:

- `npm run content:public-hygiene:check`
- `npm run seo:check`
- `npm run content:public-hygiene:rendered:prod`

## PUBLIC-E2E-WP-09: Govern Public Short Labels For `/price/` Roles

Status: owner-review
Priority: P2
Workflow lane: Full Feature Lane if Bitrix schema/source changes; Fast Fix if template-only label normalization
Affected areas: `/price/`, rates iblock rendering, price team preset runtime.

Problem:

The price page works, but public role labels can be too raw or long for buyer-facing UI.

Acceptance criteria:

- PM/Sales/Content approve short-label policy.
- Engineering confirms no break to `workers_json`, team presets, rate IDs or staff-order payload.
- Browser smoke price passes.

Required checks:

- `npm run browser:smoke:price`
- `npm run component:states:check`
- `npm run seo:check:prod`

## PUBLIC-E2E-WP-10: Strengthen Synthetic `/offer/` Example Disclosure

Status: blocked-owner
Priority: P1
Workflow lane: Full Feature Lane / Content + Legal review
Affected areas: `/offer/` list/detail, offer taxonomy/proof docs, SEO copy if changed.

Problem:

The dynamic offer catalog is large and technically healthy, but examples can be mistaken for verified client cases unless disclosure is durable and visible enough.

Acceptance criteria:

- PM/Content/Sales/Legal approve synthetic/example disclosure wording.
- SEO approves that disclosure does not damage intended indexing of detail pages.
- List and detail pages keep current route/canonical rules unless explicitly changed.
- No client/logo/metric claims are added.

Required checks:

- `npm run browser:smoke:offer`
- `npm run content:public-hygiene:rendered:prod`
- Full or sampled `/offer/sitemap.php` URL status check.
- `npm run seo:check:prod`

## PUBLIC-E2E-WP-11: Monitor `/aiagents/` Vs `/agents/` Positioning

Status: accepted-monitor
Priority: P2
Workflow lane: Monitoring
Affected areas: `/aiagents/`, `/agents/`, analytics/search evidence.

Monitoring criteria:

- Reopen if users confuse Telegram demo/prototype route with product `Agents`.
- Reopen if SEO cannibalization or lead-quality issues appear.
- Reopen if `/aiagents/` copy drifts away from bridge positioning.

## PUBLIC-E2E-WP-12: CSP Report-Only To Enforce Monitoring

Status: accepted-monitor
Priority: P2
Workflow lane: Security / Integration Lane if enforcement starts
Affected areas: template security headers, Yandex Maps/Metrika, inline scripts/styles.

Monitoring criteria:

- Keep report-only while sources and inline allowances are not fully triaged.
- Move to enforce only after Security + QA approve and browser smoke passes with maps/metrika/forms/chat coverage.
