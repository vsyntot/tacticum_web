# About Page UX / Content Challenge Gap Analysis — 2026-06-07

Дата: 07.06.2026

Статус: challenge source register, not an approval package; fast-fix/guard implementation deployed and production-rendered evidence passed; owner approvals pending for broader work.
Workflow lane: Full Feature discovery / documentation, with Fast Fix candidates.
Scope: `/about/` product trust page, UX/UI, content, Russian-first language, storyline, rendered anchors/IDs and page-content ownership. No PHP, JS, CSS, Bitrix admin data, REST, CRM, analytics or SEO route changes in this task.

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

## Production Evidence After Fast Fix

| Command / Evidence | Result |
|---|---|
| `npm run content:public-cache-clear`, production 07.06.2026 | Passed. Cleared `bitrix:menu`, `bitrix:news.list`, `bitrix:news.detail`, managed cache, component cache, composite HTML and template CSS/JS public render cache. |
| `npm run content:public-hygiene:rendered:prod:json`, production 07.06.2026 | Passed at `2026-06-07T08:24:11Z`; `pages_checked=13`, `issues_found=0`, `/about/ ok=true`. |
| `npm run page-content:source:http:wave2:prod`, production 07.06.2026 | Passed; `/about/ source=bitrix sections=3/3 bytes=74964`, all wave2 pages rendered from Bitrix source as expected. |

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
| `accepted-monitor` | Risk is consciously monitored for future changes |
| `closed` | Do not use unless implementation and evidence exist |

## Complete Gap Register

| ID | Status | Priority | Area | Gap | Required Task | Existing Gap Mapping | Owner Group | Gates / Evidence |
|---|---|---:|---|---|---|---|---|---|
| `ABOUT-001` | closed | P0 | Timeline / trust | Rendered page showed `2025` badge with `Сегодня` heading on 07.06.2026. | Split `2025` as a past milestone and add a non-stale current-focus block, or use approved dynamic/current wording. | `CLS-002`, `CLS-003`, `REL-002`, `CONTENT-004` | PM + Content + QA | Production rendered hygiene passed at `2026-06-07T08:24:11Z`; `/about/` has no stale `year + Сегодня` contradiction. |
| `ABOUT-002` | open | P1 | Storyline | Page narrative is fragmented: company story, trust, tech stack, career/culture and CTA sections compete. | Rewrite `/about/` as one trust storyline with a clear page role and section hierarchy. | `CLS-003`, `CLS-010`, `UX-001`, `CONTENT-005`, `PCJMU-001` | PM + UX + Content + Sales | Approved page role and copy outline; no unsupported claims. |
| `ABOUT-003` | in-progress | P1 | Language | Public copy exposed internal English/product terms: `product-first`, `delivery`, `backend`, `data/RAG`, `quality gates`, `production rollout`. | Apply Russian-first glossary; explain necessary technical terms on first use. | `CLS-002`, `CLS-011`, `CONTENT-004`, `SEO-009` | Content + PM + SEO + Architect | Fast-fix removed guarded visible terms from rendered `/about/`; broader editorial review remains open. |
| `ABOUT-004` | blocked | P1 | Trust / proof | Page does not provide enough verifiable trust artifacts for enterprise AI: governance, data boundary, delivery responsibility, support model, evidence status. | Build proof-safe trust matrix for `/about/`; decide what can be public, private-by-request or blocked. | `CLS-007`, `CONTENT-001`, `CONTENT-002`, `CONTENT-003`, `ARCH-009`, `UI-005` | PM + Sales + Legal + Content | Legal/Sales/PM approval before new claims, metrics, logos, certifications or named proof. |
| `ABOUT-005` | open | P1 | Team UX/UI / accessibility | Team section uses large portrait crops and hover/overlay-heavy presentation; critical bio/detail text can be inaccessible or visually noisy. | Redesign team cards for consistent crop, readable role summary and keyboard/mobile accessibility. | `UI-001`, `UI-002`, `UI-005`, `UI-010`, `CMP-008` | Design + Frontend + QA + PM | Design gate; browser/mobile smoke; no personal/team data changes without owner approval. |
| `ABOUT-006` | in-progress | P1 | Technology stack | Stack block read generic and partly dated: `BERT`, `NLTK`, `Hadoop`, `Tableau`, `передовые технологии`; weak tie to current product trust. | Replace stack inventory with capability/risk contours tied to enterprise AI launch, or retire stack block. | `CLS-004`, `CLS-002`, `STACK-003`, `CONTENT-004`, `UI-006` | Content + Architect + Sales + PM | Fast-fix removed guarded generic stack terms; final technology narrative still needs Architect/Content review. |
| `ABOUT-007` | closed | P1 | Navigation / HTML integrity | Rendered HTML had duplicate `id="about-company"`; footer linked to missing `#careers`; `#partners` pointed to technology contours. | Fix form/section ID collision; add/remove/rename anchors; align footer labels with actual sections. | `CMP-001`, `CMP-008`, `SEO-009`, `REL-002`, `BPC-CMP-001` | Frontend + QA + SEO | Production rendered hygiene passed at `2026-06-07T08:24:11Z`; rendered IDs are unique and `/about/#...` anchors resolve. |
| `ABOUT-008` | in-progress | P2 | CTA / career-culture mismatch | Career/culture and final CTA copy were generic: `достичь новых высот`; page mixes hiring/culture with buyer trust without clear transition. | Decide whether careers remains on `/about/`; rewrite culture/CTA around buyer trust or create explicit careers section. | `CLS-010`, `CLS-009`, `UX-010`, `CONTENT-004` | PM + Content + UX | Generic CTA phrase removed and `#careers` target exists; career/culture strategy remains owner-review scope. |
| `ABOUT-009` | open | P1 | Content storage / runtime ownership | About page mixes hardcoded partials, live page-content rows and fallback-retirement comments. This makes source of truth unclear. | Decide per section whether Bitrix row, PHP partial or component owns public content; sync seed/live rows and comments. | `CSG-007`, `CSG-008`, `CSG-012`, `BPC-CMP-002`, `ARCH-001` | Architect + Backend + Content + QA | Page-content audit/source marker checks; cache clear after Bitrix row changes; no silent fallback resurrection. |
| `ABOUT-010` | closed | P2 | Guard coverage | Existing content hygiene guards passed public label checks, but did not explicitly catch stale timeline, duplicate IDs or missing anchors for `/about/`. | Add source/rendered guard or release checklist for `/about/` trust defects. | `CLS-012`, `STACK-004`, `STACK-005`, `REL-002` | QA + Frontend + Backend | Source/rendered hygiene guards implemented; self-tests pass locally and production rendered hygiene passed at `2026-06-07T08:24:11Z`. |

Implementation note 07.06.2026: fast-fix implementation updated `/about/` source partials, footer menu, page-content seed, `PublicCopyNormalizer`, source hygiene and rendered hygiene guards. The timeline now separates the 2025 milestone from current focus, `FIELD_PREFIX` for `about-cta` no longer collides with `id="about-company"`, footer navigation uses `/about/#technology` instead of `/about/#partners`, `#careers` has a real rendered anchor, generic CTA copy was removed, visible `quality gates` / `production rollout` / generic stack terms were replaced, and source/rendered hygiene self-tests pass. Production deploy/cache clear evidence passed on 07.06.2026: `content:public-hygiene:rendered:prod:json` reports `checked_at=2026-06-07T08:24:11Z`, `pages_checked=13`, `issues_found=0`; `page-content:source:http:wave2:prod` confirms `/about/ source=bitrix sections=3/3`. This closes `ABOUT-001`, `ABOUT-007` and `ABOUT-010` for fast-fix/guard scope. `ABOUT-003`, `ABOUT-006` and `ABOUT-008` remain partial owner-gated maturity work.

## Section-Level Findings

| Section / Area | Finding | Priority | Related IDs |
|---|---|---:|---|
| Hero | Strong product-first direction, but phrase `AI-продукты` and Platform/Agents/Dev/Forum list do not yet explain trust reason. | P1 | `ABOUT-002`, `ABOUT-003` |
| `Кто мы?` | Generic company wording remains weaker than a trust claim, even after internal-term cleanup. | P1 | `ABOUT-002`, `ABOUT-003`, `ABOUT-004` |
| Stats cards | `AI/IT`, `B2B`, `Team` are not evidence; they feel like labels, not proof. | P1 | `ABOUT-004`, `ABOUT-002` |
| Timeline | Original `2025 / Сегодня` stale issue is fixed in production; guard should prevent recurrence. | P0 | `ABOUT-001`, `ABOUT-010` |
| Trust page-content | Original copy exposed `product-first`, `delivery`, `backend`, `data/RAG`, `scope`; guarded terms are cleaned, but proof-safe trust content still needs owner review. | P1 | `ABOUT-003`, `ABOUT-004` |
| Values | `Инновационность`, `Прозрачность`, `Гибкость` are expected/generic unless tied to operating behavior. | P2 | `ABOUT-002`, `ABOUT-008` |
| Team | Portrait grid dominates; detail/role information needs buyer-trust purpose and accessible presentation. | P1 | `ABOUT-005` |
| `Технологические контуры` | Anchor semantics and guarded internal terms are fixed; broader capability/risk framing still needs Architect/Content review. | P1 | `ABOUT-003`, `ABOUT-006`, `ABOUT-007` |
| Stack | Original generic tool list was removed from guarded production output; final technology narrative can still be stronger. | P1 | `ABOUT-006` |
| Career/culture | `#careers` now exists, but the career/culture section still needs a clear buyer-trust role or owner decision. | P2 | `ABOUT-008`, `ABOUT-007` |
| CTA | Generic phrase was removed; remaining CTA strategy should be aligned with the future trust storyline. | P2 | `ABOUT-008`, `ABOUT-010` |
| Footer anchors | Original missing/misleading anchors are fixed in production and covered by rendered hygiene guard. | P1 | `ABOUT-007`, `ABOUT-010` |

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

### Bundle C — Team/UI And Accessibility

Should run after narrative direction is approved:

- `ABOUT-005`
- related `UI-*` and `CMP-*` gaps

### Bundle D — Page-Content Ownership

Must close before large Bitrix/live content edits:

- `ABOUT-009`
- `CSG-007`, `CSG-008`, `CSG-012`

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
3. Architect/Content decide page-content ownership before editing live Bitrix rows broadly.
4. Designer scopes team/timeline/trust block UI after narrative approval.
5. QA keeps `content:public-hygiene:*` in the release path and refreshes production rendered evidence after public template/menu/page-content deploys.
6. Use remaining `ABOUT-WP-*` backlog items to create tracker issues.

## Related Documents

- `docs/workflow/about-page-ux-content-challenge-roadmap-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-guard-proposal-2026-06-07.md`
- `docs/workflow/plans/2026-06-07-about-page-ux-content-challenge-documentation.md`
- `docs/workflow/content-language-storyline-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/content-language-storyline-public-glossary-2026-06-07.md`
- `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`
- `docs/workflow/product-cjm-usecases-ux-ui-challenge-2026-06-07.md`
- `docs/workflow/gap-analysis.md`
- `docs/workflow/current-state.md`
