# Public Site E2E Challenge Gap Analysis

Date: 2026-06-07
Scope: production public site `https://tacticum.ru` across JS/CSS, rendered browser behavior, content, SEO infrastructure, sitemap/robots and page-level editorial quality.
Workflow lane: Fast Fix / Audit documentation.
Status: docs-only challenge package; no runtime/code changes approved by this document.

## Executive Verdict

Production is operationally healthy: public pages render, browser smoke passes, JS runtime errors are not observed, page-content/product sources report Bitrix, rendered content hygiene is clean, SEO guards pass, robots/sitemap endpoints are reachable and the dynamic `/offer/` sitemap has no broken URLs in the full sampled crawl.

The challenge still found release-hygiene and editorial gaps that should not be ignored:

- Accessibility/SEO semantics: public HTML lacks explicit `lang="ru"`.
- CSS artifact drift: `npm run css:check` fails because generated Tailwind output is ahead of the committed artifact.
- Static sitemap freshness drift: `npm run sitemap:static:check` fails on `lastmod` freshness.
- Content Russian-first debt: product/technical pages still overuse English/internal terms.
- Public UI label debt: `/price/` role labels can expose raw specialist names instead of buyer-friendly short labels.
- Synthetic proof risk: `/offer/` examples still need durable disclosure/governance so synthetic calculations are not read as confirmed client cases.

## Evidence Snapshot

### Passed Source/Guard Checks

- `npm run js:check`: passed, 73 files.
- `npm run css:syntax`: passed, 13 runtime CSS files.
- `npm run template-styles:check`: passed.
- `npm run bitrix:check`: passed.
- `npm run component:states:check`: passed.
- `npm run content:public-hygiene:check`: passed, 11 files scanned.
- `npm run product:content:safety:check`: passed.
- `npm run seo:check`: passed.
- `npm run seo:check:prod`: passed.
- `npm run release:public-precheck:prod`: passed.
- `npm run release:manual-gates:helper`: no pending manual gates.
- `npm run gaps:known`: code-level open/in-progress gaps 0; external release gates pending 0; post-deploy/cache smoke pending 0.

### Passed Production Render/Browser Checks

- `npm run content:public-hygiene:rendered:prod:json`: passed at `2026-06-07T15:31:44Z`, `pages_checked=13`, `issues_found=0`.
- `npm run page-content:source:http:prod`: passed for `/services/`, `/price/`, `/contacts/`, `/offer/`, expected source `bitrix`.
- `npm run page-content:source:http:wave2:prod`: passed for `/`, `/about/`, `/calculator/`, `/aiagents/`, expected source `bitrix`.
- `npm run product:source:http:prod`: passed for `/platform/`, `/agents/`, `/dev/`, `/forum/`, `source=bitrix`, `faq_source=iblock`, `proof_source=iblock`, `blocks=11`.
- `npm run visual:smoke:prod`: passed for 13 pages on desktop/mobile, runtime errors 0.
- `npm run browser:console:prod`: passed for 13 pages on desktop/mobile, runtime errors 0.
- `npm run browser:smoke:price`: passed on `/price/` desktop/mobile with `TACTICUM_EXPECT_PRICE_TEAM_PRESETS=1`.
- `npm run browser:smoke:offer`: passed on `/offer/` desktop/mobile.

### Passed SEO/HTTP Checks

- `robots.txt`: status 200, contains `Sitemap: https://tacticum.ru/sitemap.xml`.
- `sitemap.xml`: status 200, references `https://tacticum.ru/sitemap-basic-files.xml` and `https://tacticum.ru/offer/sitemap.php`.
- `sitemap-basic-files.xml`: status 200, contains 13 public URLs.
- `/offer/sitemap.php`: status 200, contains 1118 offer URLs.
- Full dynamic sitemap HEAD crawl: 1118/1118 URLs returned 200, issues 0.
- Redirects: `http://tacticum.ru/`, `https://www.tacticum.ru/`, `http://www.tacticum.ru/` redirect to `https://tacticum.ru/`.
- Filtered `/offer/` examples redirect/resolve to pretty catalog URLs and render `robots=noindex,follow` with canonical `https://tacticum.ru/offer/`.
- 404 probe `/no-such-page-e2e-audit/`: status 404, `robots=noindex,nofollow`.
- Asset HEAD audit: 16 unique favicon/CSS/JS/manifest resources returned 200.

### Failed/Weak Checks

- `npm run css:check`: failed. Generated `/tmp/tacticum-tailwind.generated.css` differs from `local/templates/tacticum/tailwind.generated.css`; generated output includes `.border-primary/30` not present in the committed artifact.
- `npm run sitemap:static:check`: failed. Current generator expects `lastmod=2026-06-07`; current `sitemap-basic-files.xml` has `lastmod=2026-06-05`.
- Production HTML begins with `<html>` without `lang`; all 13 public pages therefore fail language declaration expectations.
- `site.webmanifest` responds 200 but without explicit `content-type`; this is low-priority hygiene.
- CSP is still `Content-Security-Policy-Report-Only`, not enforce; this is an accepted security hardening track, not a current browser error.

## Gap Register

| ID | Status | Priority | Area | Finding | Evidence | Closure Criteria |
|---|---|---:|---|---|---|---|
| `PUBLIC-E2E-001` | closed locally, pending deploy smoke | P1 | Accessibility / SEO semantics | All public pages lacked `<html lang="ru">`. | Local implementation renders safe `lang` from Bitrix `LANGUAGE_ID` with fallback `ru`; `seo:check` now guards source and HTTP mode; `release:public-precheck` now checks 13 public pages. | Production deploy/cache refresh must confirm 13/13 pages have `lang=ru` via `seo:check:prod` and `release:public-precheck:prod`. |
| `PUBLIC-E2E-002` | closed locally, pending deploy smoke | P1 | CSS build artifact | `tailwind.generated.css` was stale relative to Tailwind source/toolchain. | `npm run css:build` regenerated the artifact; `.border-primary/30` is now present; `npm run css:check` and `npm run css:syntax` pass locally. | Production visual/browser smoke must pass after deploy/cache refresh. |
| `PUBLIC-E2E-003` | closed locally, pending deploy smoke | P1 | Static sitemap freshness | Static sitemap generation check failed because `lastmod` was stale. | Local ignored `sitemap-basic-files.xml` was regenerated with `lastmod=2026-06-07`; `npm run sitemap:static:check` passes locally. | Production deploy must regenerate/publish `sitemap-basic-files.xml` with current release lastmod and `seo:check:prod` must pass. |
| `PUBLIC-E2E-004` | closed locally, pending deploy smoke | P2 | Sitemap index freshness | `sitemap.xml` index had `lastmod=2026-05-24` for sitemap files after later public content changes. | Repo-owned `sitemap.xml` lastmods updated to `2026-06-07T00:00:00+03:00`; local `seo:check` passes. | Production `sitemap.xml` must show the updated lastmods and `seo:check:prod` must pass after deploy. |
| `PUBLIC-E2E-005` | open | P2 | Russian-first content | Product/technical pages still overuse English/internal terms. | Text crawl found high counts of `AI`, `LLM`, `RAG`, `runtime`, `governance`, `workflow`, `delivery`, `discovery`, especially `/platform/`, `/dev/`, `/forum/`, `/services/`. | Content + Architect approve public glossary rules; page copy normalizes or explains terms; rendered hygiene/SEO pass; no unsupported claims added. |
| `PUBLIC-E2E-006` | open | P2 | `/price/` public labels | Rate/role catalog can expose raw specialist labels rather than buyer-friendly short labels. | `/price/` browser works, but rendered content includes long/raw role names from rates. | Add/approve public short-label strategy for rates/roles without breaking staff order payload; browser smoke price and content hygiene pass. |
| `PUBLIC-E2E-007` | open | P1 | `/offer/` proof/synthetic framing | Offer examples may still be read as real confirmed cases unless disclosure/governance remains explicit. | `/offer/` has 1118 live example URLs, dynamic sitemap all 200, and synthetic-offer risk already intersects `OFFER-TAX-005`. | PM/Content/Sales/Legal approve disclosure language and public proof policy; offer list/detail keep proof-safe framing; SEO/canonical/noindex policy remains explicit. |
| `PUBLIC-E2E-008` | accepted-monitor | P2 | `/aiagents/` vs `/agents/` positioning | `/aiagents/` remains a Telegram demo/prototype route and can compete semantically with product `/agents/`. | `/aiagents/` smoke and SEO pass; bridge copy exists; boundary is currently guarded. | Monitor search/query behavior and lead quality; reopen if users confuse `/aiagents/` with product `Agents` or SEO cannibalization appears. |
| `PUBLIC-E2E-009` | open | P2 | `/dev/` editorial clarity | `/dev/` is the most jargon-heavy product page. | Text crawl found repeated `AI-assisted`, `AI-coding`, `governance`, `workflow`, `knowledge backbone` terms. | Rewrite high-friction headings/intro into Russian-first executive language while preserving technical accuracy and product positioning. |
| `PUBLIC-E2E-010` | open | P3 | Web manifest headers | `site.webmanifest` returns 200 but no explicit content type in HEAD audit. | Asset audit row for `site.webmanifest` has empty `type`. | Configure static type as `application/manifest+json` or accepted equivalent; re-run asset audit/browser smoke. |
| `PUBLIC-E2E-011` | accepted-monitor | P2 | CSP hardening | CSP is still `Content-Security-Policy-Report-Only`. | Home response header shows report-only CSP with Yandex/inline allowances. | Keep as monitor until report-only baseline is triaged; enforce only after Security/QA review and production smoke. |
| `PUBLIC-E2E-012` | open | P3 | `/contacts/` conversion clarity | Contact page is technically clean but could better set response expectations and required input. | `/contacts/` smoke/SEO pass; content review notes weak expectation-setting. | Add concise response-time/channel/preparation copy if PM approves; no form payload contract change unless Security/Integration lane is opened. |
| `PUBLIC-E2E-013` | open | P3 | `/policies/` document navigation | Policy page has one main legal H1 and only CTA-level H2, making long legal text less navigable. | `/policies/` smoke/SEO pass; H2 count is 1 and it is `Связаться с нами`. | Add legal section anchors/headings if Content/Legal approve; keep canonical and policy source intact. |
| `PUBLIC-E2E-014` | partially closed locally | P2 | Guard coverage | Existing `seo:check` did not catch missing `<html lang>` before manual crawl, and sitemap freshness was split into a separate command. | `seo:check` now verifies template language declaration and HTTP `lang=ru`; `release:public-precheck` now checks public page `lang=ru`; `sitemap:static:check` passes after artifact regeneration. | Fully close after production `seo:check:prod` and `release:public-precheck:prod` pass with new lang assertions; optional future work can fold sitemap artifact freshness into release precheck. |

## Page-By-Page Challenge Notes

| Page | Technical State | Content / UX Challenge |
|---|---|---|
| `/` | 200, 1 H1, SEO ok, browser smoke ok, FAQ ok. | Stronger ecosystem router now works, but still uses many `AI`/product English terms; FAQ includes broad technology language that needs Content + Architect review. |
| `/platform/` | 200, product source Bitrix, 11 blocks, FAQ/proof iblock, browser smoke ok. | Valuable for technical buyers, heavy for business buyers: `Platform`, `LLM`, `RAG`, `runtime`, `governance`, `self-build` need Russian-first framing or glossary support. |
| `/agents/` | 200, product source Bitrix, 11 blocks, browser smoke ok. | Boundary with `/aiagents/` improved; continue watching chatbot-vs-corporate-assistant clarity. |
| `/dev/` | 200, product source Bitrix, 11 blocks, browser smoke ok. | Highest editorial friction: `AI-assisted`, `AI-coding`, `workflow`, `governance`, `knowledge backbone` read as internal/technical language. |
| `/forum/` | 200, product source Bitrix, 11 blocks, browser smoke ok. | Good concept boundary, but `LLM`, `enterprise`, `runtime`, `support` terms need clearer Russian explanation for nontechnical readers. |
| `/services/` | 200, page-content source Bitrix, FAQ ok, browser smoke ok. | Canonical tech-stack route works; `delivery`, `T&M`, `MVP`, `DevOps` need public-friendly wording. |
| `/price/` | 200, page-content source Bitrix, targeted price smoke ok. | Functional, but public rate labels can be too raw/long; short labels/aliases should be governed. |
| `/calculator/` | 200, page-content source Bitrix, FAQ ok, browser smoke ok. | Clear and short; keep emphasizing preliminary estimate vs final commercial offer. |
| `/offer/` | 200, page-content source Bitrix, targeted offer smoke ok; dynamic sitemap 1118/1118 OK. | Technically strong after filter work; biggest remaining content risk is synthetic example/proof-safe framing. |
| `/aiagents/` | 200, page-content source Bitrix, FAQ ok, browser smoke ok. | Telegram demo bridge is better, but route can still cannibalize/confuse product `/agents/`. Monitor. |
| `/about/` | 200, page-content source Bitrix, browser smoke ok. | 2025 appears only as historical marker, not current state; PM/Design visual acceptance still needed for full closure. |
| `/contacts/` | 200, page-content source Bitrix, browser smoke ok. | Operationally clear, but conversion clarity could improve: response time, channel choice, what to prepare. |
| `/policies/` | 200, browser smoke ok, WebPage JSON-LD. | Legal page is technically adequate but could use document navigation/headings if Legal approves. |

## Non-Goals

- This package does not approve runtime code changes.
- This package does not change sitemap/canonical/indexability behavior.
- This package does not authorize new public claims, logos, proof metrics or client-case language.
- This package does not change form payload contracts, analytics payloads or REST endpoints.
- This package does not close existing `OFFER-TAX-*`, `ABOUT-*`, `CLS-*` or `PTC-*` owner-gated gaps; it references overlaps where relevant.

## Implementation Update 2026-06-07

Local Fast Fix implementation closed the objective technical hygiene slice for deploy-ready review:

- `local/templates/tacticum/header.php` now renders a safe `<html lang="...">` from Bitrix `LANGUAGE_ID` with fallback `ru`.
- `tools/seo-check.mjs` now guards the source template language declaration and, in HTTP mode, verifies `lang=ru` on all 13 public pages.
- `tools/release-public-precheck.mjs` now verifies `lang=ru` on all 13 public pages before product source, Metrika, admin-surface and legacy-alias checks.
- `local/templates/tacticum/tailwind.generated.css` was regenerated through `npm run css:build`; `npm run css:check` now passes.
- Local ignored `sitemap-basic-files.xml` was regenerated with `lastmod=2026-06-07`; `npm run sitemap:static:check` now passes.
- Repo-owned `sitemap.xml` index lastmods were updated to `2026-06-07T00:00:00+03:00`.

Local verification passed:

- `php -l local/templates/tacticum/header.php`
- `npm run css:check`
- `npm run css:syntax`
- `npm run js:check`
- `npm run sitemap:static:check`
- `npm run seo:check`
- `npm run bitrix:check`
- `npm run content:public-hygiene:check`
- `npm run component:states:check`
- `npm run visual:smoke:css-local`
- `npm run browser:console:css-local`

CSS-local visual/action smoke passed for 13 pages on desktop/mobile with runtime errors 0; manifest: `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-07T16-00-37-047Z/manifest.json`.

Production verification remains pending until deploy/cache refresh because new `seo:check:prod` and `release:public-precheck:prod` assertions intentionally fail against the old production template that still lacks `lang=ru`.

## Reopen Triggers

- Any public page starts returning non-200 unexpectedly.
- Browser smoke reports runtime errors, action errors or warnings under `browser:console:prod`.
- A public CSS/JS asset returns 4xx/5xx.
- `content:public-hygiene:rendered:prod` reports visible internal labels.
- `seo:check:prod` fails.
- `/offer/sitemap.php` contains 4xx/redirecting URLs.
- Filtered `/offer/` pages become indexable without SEO approval.
- English/internal terminology increases on public product pages without glossary/owner approval.
