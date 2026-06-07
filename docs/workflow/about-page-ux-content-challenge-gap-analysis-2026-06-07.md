# About Page UX / Content Challenge Gap Analysis — 2026-06-07

Дата: 07.06.2026

Статус: challenge source register, not an approval package; fast-fix/guard implementation, proof-safe trust-storyline implementation and timeline marker UI follow-up are deployed with production-rendered/browser evidence; proof/ownership owner-review package is prepared; owner approvals pending for broader work.
Workflow lane: Full Feature discovery / documentation, with Fast Fix candidates.
Scope: `/about/` product trust page, UX/UI, content, Russian-first language, storyline, rendered anchors/IDs and page-content ownership. Original challenge was docs-only; implementation notes below record later scoped PHP/CSS fast-fixes. No Bitrix admin data, REST, CRM, analytics or SEO route changes are implied by this document.

## Purpose

Этот документ фиксирует результаты придирчивого challenge страницы `https://tacticum.ru/about/`. Его нужно использовать как source register перед будущими задачами по странице "О компании": быстрые правки, перепись доверительного сторилайна, переработка team/UI секции, синхронизация Bitrix page-content rows and guard coverage.

Документ не закрывает существующие canonical product-tech gaps. Local IDs `ABOUT-*` ниже мапятся на canonical and adjacent IDs from:

- `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`;
- `docs/workflow/product-cjm-usecases-ux-ui-challenge-2026-06-07.md`;
- `docs/workflow/content-language-storyline-challenge-gap-analysis-2026-06-07.md`;
- `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`;
- `docs/workflow/bitrix-componentization-gap-analysis-2026-06-05.md`.

## Audit Method

- Fetched production rendered HTML for `/about/` on 07.06.2026.
- Extracted visible text, H1, key labels, timeline copy, CTA copy, rendered IDs and `/about/#...` footer anchors.
- Reviewed user-supplied screenshots in chat: values cards, technology contours, career/culture cards, team cards and timeline fragment.
- Cross-checked rendered issues against local component partials and content seed files.
- Did not use Bitrix admin access and did not change production content.

## Source Evidence

| Source | Signal |
|---|---|
| Rendered `/about/`, fetched 2026-06-07T07:45:54Z | H1 `Команда Tacticum развивает корпоративные AI-продукты`; page renders `2025` and `Сегодня` together; text contains internal terms and generic CTA language |
| Rendered `/about/` ID scan | Duplicate `id="about-company"` appears twice |
| Rendered `/about/` anchor scan | Footer anchors found: `about-company`, `team`, `careers`, `partners`; `careers` target is missing |
| Rendered `/about/` semantic scan | `id="partners"` points to `Технологические контуры`, not a partners section |
| `local/components/tacticum/about.page/templates/.default/parts/company-trust.php` | Hero, company intro and timeline are still hardcoded before `tacticum_page_content_render_if_live('/about/', 'company-trust')`; timeline has `2025` badge and `Сегодня` heading |
| `local/components/tacticum/about.page/templates/.default/parts/values-team.php` | Live `values-team` page-content renders first, then hardcoded team section and `Технологические контуры`; `id="partners"` is attached to technology contours |
| `local/components/tacticum/about.page/templates/.default/parts/stack-cta.php` | Generic technology stack section lists `BERT`, `NLTK`, `Hadoop`, `Tableau`; copy says `передовые технологии` |
| `local/components/tacticum/about.page/templates/.default/parts/career-final.php` | Live `career-final` page-content renders, then hardcoded gradient CTA with `достичь новых высот` |
| `tools/content-storage-page-content-seed.php` | `/about/` seed contains `product-first`, `delivery`, `backend`, `data/RAG` and related internal terms in page-content rows |
| `.bottom.menu.php` | Footer links to `/about/#careers` and `/about/#partners` |
| User screenshots, 07.06.2026 | Confirm visual concerns: generic values/culture cards, large team portraits/crops, `2025 / Сегодня` timeline and technology contours positioning |
| User screenshot follow-up, 07.06.2026 | Timeline marker color states were ambiguous: filled blue `2025` read as active/current, dark `Сейчас` read as a separate special case, and older years read as quiet history |
| User screenshot follow-up, 07.06.2026 | `values-team` feature cards rendered without section heading/intro, used a 5-column grid for 3 cards, looked like generic value cards and weakened the bridge from operating model to team |

## Production Evidence After Fast Fix

| Command / Evidence | Result |
|---|---|
| `npm run content:public-cache-clear`, production 07.06.2026 | Passed. Cleared `bitrix:menu`, `bitrix:news.list`, `bitrix:news.detail`, managed cache, component cache, composite HTML and template CSS/JS public render cache. |
| `npm run content:public-hygiene:rendered:prod:json`, production 07.06.2026 | Passed at `2026-06-07T08:24:11Z`; `pages_checked=13`, `issues_found=0`, `/about/ ok=true`. |
| `npm run page-content:source:http:wave2:prod`, production 07.06.2026 | Passed; `/about/ source=bitrix sections=3/3 bytes=74964`, all wave2 pages rendered from Bitrix source as expected. |

## Trust Storyline Slice Evidence

| Command / Evidence | Result |
|---|---|
| Local PHP lint, 07.06.2026 | Passed for about partials, `PublicCopyNormalizer`, `PageContent\\Repository` and page-content seed. |
| `npm run content:public-hygiene:check`, local 07.06.2026 | Passed; source guard covers about generic stack/career/company wording recurrence. |
| `npm run content:public-hygiene:rendered:self-test`, local 07.06.2026 | Passed; rendered guard fixture covers `#start-work` and old career/stack terms. |
| `npm run product:content:safety:check`, local 07.06.2026 | Passed. |
| `npm run seo:check` / `npm run bitrix:check`, local 07.06.2026 | Passed. |
| `npm run content:public-cache-clear`, production 07.06.2026 | Passed after trust-storyline deploy. |
| `npm run content:public-hygiene:rendered:prod:json`, production 07.06.2026 | Passed at `2026-06-07T08:49:12Z`; `pages_checked=13`, `issues_found=0`, `/about/ ok=true`. |
| `npm run page-content:source:http:wave2:prod`, production 07.06.2026 | Passed; `/about/ source=bitrix sections=3/3 bytes=76054`, all wave2 pages rendered from Bitrix source as expected. |

## Owner-Review Package Evidence

| Document / Evidence | Result |
|---|---|
| `docs/workflow/about-page-proof-matrix-owner-review-2026-06-07.md` | Prepared no-raw-copy proof/trust matrix for `ABOUT-004`: public-safe-now, public-after-owner-approval, private-by-request, blocked-no-evidence and remove-if-present categories. No public claims approved. |
| `docs/workflow/about-page-content-ownership-map-2026-06-07.md` | Prepared actual render ownership map for `ABOUT-009`: PHP partials, Bitrix live page-content rows, team iblock, lead CTA, footer menu and `PublicCopyNormalizer` compatibility. No fallback retirement approved. |
| `docs/workflow/plans/2026-06-07-about-owner-review-proof-ownership.md` | Docs-only plan for the owner-review package; no PHP, JS, CSS, Bitrix row, route, metadata, form or analytics changes. |

## Timeline Marker UI Follow-Up

| Command / Evidence | Result |
|---|---|
| Local implementation, 07.06.2026 | Timeline markers now use explicit `past`, `milestone` and `current` visual states. `2025` is a bordered key milestone, while `Текущий фокус` carries the primary current marker and visible `Сейчас` badge. |
| PHP lint, local 07.06.2026 | Passed for `local/components/tacticum/about.page/templates/.default/parts/company-trust.php`. |
| `npm run css:syntax`, local 07.06.2026 | Passed after adding timeline marker/badge CSS. |
| `npm run content:public-hygiene:check` / `npm run seo:check`, local 07.06.2026 | Passed. |
| Rendered HTML check, production 07.06.2026 | Passed; `/about/` contains `timeline-marker--milestone`, `timeline-marker--current`, `Ключевой этап` and `timeline-badge--current`. |
| `npm run content:public-cache-clear`, production 07.06.2026 | Passed after timeline marker deploy. |
| `npm run content:public-hygiene:rendered:prod:json`, production 07.06.2026 | Passed at `2026-06-07T10:33:14Z`; `pages_checked=13`, `issues_found=0`, `/about/ ok=true`. |
| `npm run page-content:source:http:wave2:prod`, production 07.06.2026 | Passed; `/about/ source=bitrix sections=3/3 bytes=77870`. |
| Chrome-capable visual smoke, production 07.06.2026 | Passed at `2026-06-07T10:34:10Z`: desktop/mobile status `200`, runtime errors `0`, warnings `0`, broken images `0`, action errors `0`, SEO ok; manifest `/tmp/tacticum-about-timeline-marker-2026-06-07-visual/manifest.json`. |

## Work Model Feature Grid Follow-Up

| Command / Evidence | Result |
|---|---|
| Local implementation, 07.06.2026 | `feature-card-grid` renderer now outputs existing section heading/intro, uses count-aware grid density and renders static process cards without hover-only affordance. `/about/` `values-team` copy now frames the block as pre-launch operating model: scenario, responsibility and constraints. |
| Compatibility | `PublicCopyNormalizer::normalizePageContentSection()` maps both old `innovation/transparency/flexibility` and current `hypothesis/responsibility/constraints` block keys to the revised public copy, so old live Bitrix rows are protected without manual row edits. |
| Local PHP fixture, 07.06.2026 | Passed: renderer output contains `h2`, `Как мы работаем до запуска`, `tacticum-feature-grid--count-3` and `tacticum-feature-card__title`. |
| PHP lint, local 07.06.2026 | Passed for `Renderer.php`, `PublicCopyNormalizer.php` and `content-storage-page-content-seed.php`. |
| `npm run css:check` / `css:syntax` / `template-styles:check`, local 07.06.2026 | Passed after adding component-level feature-grid CSS without Tailwind artifact changes. |
| `npm run content:public-hygiene:check` / `content:public-hygiene:rendered:self-test` / `seo:check` / `bitrix:check` / `component:states:check`, local 07.06.2026 | Passed. |
| Production evidence | Pending deploy, public cache clear, rendered hygiene, wave2 source check and desktop/mobile browser smoke. |

## Challenge Verdict

Страница `/about/` сейчас выглядит как несколько страниц, склеенных в одну:

- company intro and timeline;
- product/delivery trust section;
- values and team gallery;
- technology stack inventory;
- career/culture cards;
- repeated CTA blocks.

Главный риск не в том, что на странице мало контента. Риск в том, что страница не отвечает достаточно жестко на вопрос B2B-покупателя: почему этой команде можно доверить корпоративный AI-контур, данные, интеграции и запуск в рабочий процесс.

`2025 / Сегодня` на 07.06.2026 является отдельным trust defect. Его нельзя исправлять механически заменой `2025` на `2026`: нужно разделить исторический milestone 2025 и текущий фокус без устаревающего ярлыка.

## Target Public Role For `/about/`

Recommended role:

> `/about/` is a vendor trust page: it explains who is responsible for Tacticum products, how the team works with enterprise AI risks, what operating model protects the client, and how to start a safe assessment.

Recommended hierarchy:

| Layer | Public Purpose |
|---|---|
| Hero | Company promise: safe corporate AI launch from scenario to pilot and operation |
| Trust proof | How Tacticum reduces delivery, data, security and integration risk |
| Operating model | Assessment, pilot, launch, support, roles and responsibility boundaries |
| Team | Who owns expertise and how to contact the team, not a decorative portrait grid |
| Technology | Only technology contours tied to buyer risk, not a generic tool zoo |
| Timeline | Past milestones separated from current focus |
| CTA | Specific artifact or next step: assessment, architecture call, pilot scope |

## Statuses

| Status | Meaning |
|---|---|
| `open` | Confirmed gap, needs task or owner decision |
| `blocked` | Requires PM/Sales/Legal/SEO/Design/Architect approval before implementation |
| `in-progress` | Partial basis exists, but public page is not mature enough |
| `owner-review-ready` | Decision package exists for owners, but no runtime/public approval or closure is granted |
| `accepted-monitor` | Risk is consciously monitored for future changes |
| `closed` | Do not use unless implementation and evidence exist |

## Complete Gap Register

| ID | Status | Priority | Area | Gap | Required Task | Existing Gap Mapping | Owner Group | Gates / Evidence |
|---|---|---:|---|---|---|---|---|---|
| `ABOUT-001` | closed | P0 | Timeline / trust | Rendered page showed `2025` badge with `Сегодня` heading on 07.06.2026. | Split `2025` as a past milestone and add a non-stale current-focus block, or use approved dynamic/current wording. | `CLS-002`, `CLS-003`, `REL-002`, `CONTENT-004` | PM + Content + QA | Production rendered hygiene passed at `2026-06-07T08:24:11Z`; `/about/` has no stale `year + Сегодня` contradiction. |
| `ABOUT-002` | in-progress | P1 | Storyline | Page narrative was fragmented: company story, trust, tech stack, career/culture and CTA sections competed. | Rewrite `/about/` as one trust storyline with a clear page role and section hierarchy. | `CLS-003`, `CLS-010`, `UX-001`, `CONTENT-005`, `PCJMU-001` | PM + UX + Content + Sales | Proof-safe rewrite is deployed and rendered hygiene passed at `2026-06-07T08:49:12Z`; owner review and proof decisions pending. |
| `ABOUT-003` | in-progress | P1 | Language | Public copy exposed internal English/product terms: `product-first`, `delivery`, `backend`, `data/RAG`, `quality gates`, `production rollout`. | Apply Russian-first glossary; explain necessary technical terms on first use. | `CLS-002`, `CLS-011`, `CONTENT-004`, `SEO-009` | Content + PM + SEO + Architect | Source/runtime guard and production rendered hygiene remove visible old terms and generic about wording; broader editorial review remains open. |
| `ABOUT-004` | blocked | P1 | Trust / proof | Page does not provide enough verifiable trust artifacts for enterprise AI: governance, data boundary, delivery responsibility, support model, evidence status. | Build proof-safe trust matrix for `/about/`; decide what can be public, private-by-request or blocked. | `CLS-007`, `CONTENT-001`, `CONTENT-002`, `CONTENT-003`, `ARCH-009`, `UI-005` | PM + Sales + Legal + Content | Owner-review proof matrix is prepared; Legal/Sales/PM approval still required before new claims, metrics, logos, certifications or named proof. |
| `ABOUT-005` | in-progress | P1 | Team UX/UI / accessibility | Team section used large portrait crops and hover/overlay-heavy presentation; critical bio/detail text could be inaccessible or visually noisy. | Redesign team cards for consistent crop, readable role summary and keyboard/mobile accessibility. | `UI-001`, `UI-002`, `UI-005`, `UI-010`, `CMP-008` | Design + Frontend + QA + PM | Card template slice has production rendered/source/browser evidence without personal data changes; PM/Design visual acceptance still required before full closure. |
| `ABOUT-006` | in-progress | P1 | Technology stack | Stack block read generic and partly dated: `BERT`, `NLTK`, `Hadoop`, `Tableau`, `передовые технологии`; weak tie to current product trust. | Replace stack inventory with capability/risk contours tied to enterprise AI launch, or retire stack block. | `CLS-004`, `CLS-002`, `STACK-003`, `CONTENT-004`, `UI-006` | Content + Architect + Sales + PM | Stack copy now describes launch-readiness checks and production rendered hygiene passed; Architect/Content review pending. |
| `ABOUT-007` | closed | P1 | Navigation / HTML integrity | Rendered HTML had duplicate `id="about-company"`; footer linked to missing `#careers`; `#partners` pointed to technology contours. | Fix form/section ID collision; add/remove/rename anchors; align footer labels with actual sections. | `CMP-001`, `CMP-008`, `SEO-009`, `REL-002`, `BPC-CMP-001` | Frontend + QA + SEO | Production rendered hygiene passed at `2026-06-07T08:24:11Z`; rendered IDs are unique and `/about/#...` anchors resolve. |
| `ABOUT-008` | in-progress | P2 | CTA / career-culture mismatch | Career/culture and final CTA copy were generic: `достичь новых высот`; page mixed hiring/culture with buyer trust without clear transition. | Decide whether careers remains on `/about/`; rewrite culture/CTA around buyer trust or create explicit careers section. | `CLS-010`, `CLS-009`, `UX-010`, `CONTENT-004` | PM + Content + UX | Footer/final section uses `#start-work`, keeps `#careers` alias and passed production rendered hygiene; final career strategy pending. |
| `ABOUT-009` | owner-review-ready | P1 | Content storage / runtime ownership | About page mixes hardcoded partials, live page-content rows and fallback-retirement comments. This makes source of truth unclear. | Decide per section whether Bitrix row, PHP partial or component owns public content; sync seed/live rows and comments. | `CSG-007`, `CSG-008`, `CSG-012`, `BPC-CMP-002`, `ARCH-001` | Architect + Backend + Content + QA | Actual ownership map is prepared; page-content audit/source marker checks, cache clear and owner approval still required before Bitrix/fallback changes. |
| `ABOUT-010` | closed | P2 | Guard coverage | Existing content hygiene guards passed public label checks, but did not explicitly catch stale timeline, duplicate IDs or missing anchors for `/about/`. | Add source/rendered guard or release checklist for `/about/` trust defects. | `CLS-012`, `STACK-004`, `STACK-005`, `REL-002` | QA + Frontend + Backend | Source/rendered hygiene guards implemented; self-tests pass locally and production rendered hygiene passed at `2026-06-07T08:24:11Z`. |

Implementation note 07.06.2026: fast-fix implementation updated `/about/` source partials, footer menu, page-content seed, `PublicCopyNormalizer`, source hygiene and rendered hygiene guards. The timeline now separates the 2025 milestone from current focus, `FIELD_PREFIX` for `about-cta` no longer collides with `id="about-company"`, footer navigation uses `/about/#technology` instead of `/about/#partners`, `#careers` has a real rendered anchor, generic CTA copy was removed, visible `quality gates` / `production rollout` / generic stack terms were replaced, and source/rendered hygiene self-tests pass. Production deploy/cache clear evidence passed on 07.06.2026: `content:public-hygiene:rendered:prod:json` reports `checked_at=2026-06-07T08:24:11Z`, `pages_checked=13`, `issues_found=0`; `page-content:source:http:wave2:prod` confirms `/about/ source=bitrix sections=3/3`. This closes `ABOUT-001`, `ABOUT-007` and `ABOUT-010` for fast-fix/guard scope. `ABOUT-003`, `ABOUT-006` and `ABOUT-008` remain partial owner-gated maturity work.

Implementation note 07.06.2026, trust-storyline slice: source partials and wave2 page-content seed now frame `/about/` around corporate AI launch responsibility: scenario, data/integration contour, risk control, launch plan, team roles and first safe step. `.bottom.menu.php` now points company footer navigation to `/about/#start-work`; `#careers` remains as a backward-compatible alias. `PublicCopyNormalizer::normalizePageContentSection()` and `PageContent\\Repository` protect old live `/about/` Bitrix rows without global short-word replacements. Production deploy/cache clear evidence passed on 07.06.2026: `content:public-hygiene:rendered:prod:json` reports `checked_at=2026-06-07T08:49:12Z`, `pages_checked=13`, `issues_found=0`; `page-content:source:http:wave2:prod` confirms `/about/ source=bitrix sections=3/3 bytes=76054`. This advances `ABOUT-002`, `ABOUT-003`, `ABOUT-006` and `ABOUT-008` with production evidence, but does not close owner-gated proof, team UI or page-content ownership gates.

Implementation note 07.06.2026, owner-review package: proof/trust matrix and actual render ownership map are documented for owner decisions. `ABOUT-004` remains blocked for public claims until PM/Sales/Legal/Content approve evidence and wording. `ABOUT-009` is owner-review-ready, but runtime ownership, Bitrix live-row sync and fallback retirement remain unapproved.

Implementation note 07.06.2026, team UI/accessibility slice: `news.list/team` now renders team data in normal card flow instead of hover-only overlay. Role, preview, non-duplicate detail and labelled contact links are visible and keyboard/mobile reachable; `values-team.php` explicitly requests `PHOTO`; obsolete `.member-overlay` CSS/JS was removed. No names, photos, roles, bios, emails, LinkedIn values, Bitrix rows, claims, form payloads or SEO metadata changed. Production cache clear, rendered hygiene and wave2 source checks passed at `2026-06-07T10:01:10Z` with `/about/ source=bitrix sections=3/3 bytes=77945`. Chrome-capable visual smoke passed at `2026-06-07T10:05:26Z` for desktop/mobile with runtime errors `0`, warnings `0`, broken images `0`, action errors `0` and manifest `/tmp/tacticum-about-team-ui-2026-06-07-visual/manifest.json`. `ABOUT-005` remains in-progress only because PM/Design visual acceptance is not recorded.

## Section-Level Findings

| Section / Area | Finding | Priority | Related IDs |
|---|---|---:|---|
| Hero | Production rendered hygiene passed after copy was reframed around responsibility for corporate AI launch. | P1 | `ABOUT-002`, `ABOUT-003` |
| Company intro | Production copy explains scenario, data, roles, integrations, risks and next step; proof matrix is prepared for owner review and remains owner-gated. | P1 | `ABOUT-002`, `ABOUT-003`, `ABOUT-004` |
| Stats cards | Old `AI/IT`, `B2B`, `Team` labels are removed and guarded; production rendered hygiene passed. | P1 | `ABOUT-004`, `ABOUT-002` |
| Timeline | Original `2025 / Сегодня` stale issue is fixed in production; guard should prevent recurrence. | P0 | `ABOUT-001`, `ABOUT-010` |
| Trust page-content | Local seed and runtime normalizer reframe live rows around launch risk; proof-safe trust artifacts have an owner-review matrix but no public proof approval. | P1 | `ABOUT-003`, `ABOUT-004` |
| Values | Local seed replaces generic value labels with operating behavior: hypothesis, responsibility and constraints. | P2 | `ABOUT-002`, `ABOUT-008` |
| Team | Card template removes hover-only detail dependency and exposes role/summary/detail in normal flow; production rendered/source/browser evidence passed, PM/Design acceptance pending. | P1 | `ABOUT-005` |
| `Технологические контуры` | Local source reframes the section as reliable AI launch contours; Architect/Content review pending. | P1 | `ABOUT-003`, `ABOUT-006`, `ABOUT-007` |
| Stack | Tool inventory was replaced with launch-readiness checks and production rendered hygiene passed. | P1 | `ABOUT-006` |
| Career/culture | Local footer/final section use start-work framing; `#careers` remains as compatibility alias. | P2 | `ABOUT-008`, `ABOUT-007` |
| CTA | CTA asks for the AI scenario and first safe step; production rendered hygiene passed. | P2 | `ABOUT-008`, `ABOUT-010` |
| Footer anchors | Original missing/misleading anchors are fixed in production and covered by rendered hygiene guard. | P1 | `ABOUT-007`, `ABOUT-010` |
| Content ownership | Actual mixed-source ownership is mapped for owner review; fallback retirement and Bitrix row source-of-truth decisions remain open. | P1 | `ABOUT-009` |

## Minimum Closure Bundles

### Bundle A — Trust-Breaking Fast Fixes

Must close before broader content/UI work:

- `ABOUT-001`
- `ABOUT-007`
- `ABOUT-010` minimal guard/checklist

Status 07.06.2026: closed for fast-fix/guard scope after production cache clear and rendered hygiene evidence at `2026-06-07T08:24:11Z`.

### Bundle B — Trust Narrative Rewrite

Must close before calling `/about/` product-trust mature:

- `ABOUT-002`
- `ABOUT-003`
- `ABOUT-004`
- `ABOUT-006`
- `ABOUT-008`

Status 07.06.2026: proof-safe copy/storyline slice is implemented, deployed and guarded with production rendered evidence at `2026-06-07T08:49:12Z`, and proof matrix is prepared for owner review. Bundle is not fully closed until owner decisions on public/private/blocked trust statements exist.

### Bundle C — Team/UI And Accessibility

Should run after narrative direction is approved:

- `ABOUT-005`
- related `UI-*` and `CMP-*` gaps

Status 07.06.2026: `ABOUT-WP-04` template slice has production rendered/source/browser evidence. Bundle is not fully closed only because PM/Design visual acceptance is not recorded.

### Bundle D — Page-Content Ownership

Must close before large Bitrix/live content edits:

- `ABOUT-009`
- `CSG-007`, `CSG-008`, `CSG-012`

Status 07.06.2026: ownership map is prepared for owner review. Bundle is not closed; no Bitrix row sync, source-of-truth transfer or fallback retirement is approved.

## Do Not Start Without Gates

- Do not add public claims, metrics, partner logos, certifications, registry/SLA promises or customer names without Legal/Sales/PM approval.
- Do not change title, description, canonical, sitemap, robots or public URL behavior without SEO approval.
- Do not change lead form payload, hidden fields, CRM/upstream fields or analytics taxonomy inside an `/about/` copy task.
- Do not change team photos, names, roles or personal details without owner approval.
- Do not redesign team cards, timeline or page layout density without Design gate.
- Do not retire or reintroduce page-content fallback bodies without content-storage approval and source/rendered evidence.
- Do not fix `2025 / Сегодня` by simply changing the year to `2026`; separate history from current state.

## Suggested Return Path

1. Keep `ABOUT-WP-01`, `ABOUT-WP-02` and `ABOUT-WP-08` closed unless rendered hygiene catches a regression.
2. PM/Content/Sales approve the `/about/` target role: vendor trust page, not generic company/career page.
3. PM/Sales/Legal/Content review the proof matrix before any stronger public trust claims.
4. Architect/Content decide page-content ownership from the ownership map before editing live Bitrix rows broadly.
5. Designer scopes team/timeline/trust block UI after narrative approval.
6. QA keeps `content:public-hygiene:*` in the release path and refreshes production rendered evidence after public template/menu/page-content deploys.
7. Use remaining `ABOUT-WP-*` backlog items to create tracker issues.

## Related Documents

- `docs/workflow/about-page-ux-content-challenge-roadmap-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-guard-proposal-2026-06-07.md`
- `docs/workflow/about-page-proof-matrix-owner-review-2026-06-07.md`
- `docs/workflow/about-page-content-ownership-map-2026-06-07.md`
- `docs/workflow/plans/2026-06-07-about-owner-review-proof-ownership.md`
- `docs/workflow/plans/2026-06-07-about-team-ui-accessibility-slice.md`
- `docs/workflow/plans/2026-06-07-about-page-ux-content-challenge-documentation.md`
- `docs/workflow/content-language-storyline-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/content-language-storyline-public-glossary-2026-06-07.md`
- `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`
- `docs/workflow/product-cjm-usecases-ux-ui-challenge-2026-06-07.md`
- `docs/workflow/gap-analysis.md`
- `docs/workflow/current-state.md`
