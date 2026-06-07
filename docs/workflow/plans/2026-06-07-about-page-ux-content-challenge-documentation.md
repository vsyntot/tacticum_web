# Codex Plan: About Page UX / Content Challenge Documentation

Issue: none / user-requested documentation snapshot
Gap ID: local docs-only `ABOUT-*` register
Workflow lane: Full Feature discovery / documentation, with Fast Fix candidates
Owner agent: Codex
Date: 2026-06-07

## Goal

Зафиксировать результаты придирчивого challenge страницы `https://tacticum.ru/about/` по UX/UI, контенту, связности и доверию. Сформировать переносимый набор документов, к которому можно вернуться перед реализацией fast fixes или полной переработкой страницы "О компании".

## Non-Goals

- Не менять PHP/JS/CSS/runtime в этой задаче.
- Не менять Bitrix admin content и live `page_sections/page_blocks` rows.
- Не менять URL, canonical, sitemap, robots, SEO metadata.
- Не менять формы, `lead_*` context, REST, CRM/upstream, analytics payloads.
- Не публиковать новые claims, метрики, логотипы, сертификаты, партнерства или SLA without Legal/Sales/PM approval.
- Не менять фотографии, состав команды или персональные данные без owner approval.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] Production rendered `/about/` fetched 2026-06-07
- [x] User-supplied screenshots in chat: values, technology contours, culture/career cards, team cards, history timeline
- [x] `about/index.php`
- [x] `local/components/tacticum/about.page/templates/.default/template.php`
- [x] `local/components/tacticum/about.page/templates/.default/parts/company-trust.php`
- [x] `local/components/tacticum/about.page/templates/.default/parts/values-team.php`
- [x] `local/components/tacticum/about.page/templates/.default/parts/stack-cta.php`
- [x] `local/components/tacticum/about.page/templates/.default/parts/career-final.php`
- [x] `tools/content-storage-page-content-seed.php`
- [x] `.bottom.menu.php`

`.github/copilot-instructions.md` and `docs/adr/` are not required for this docs-only snapshot. Future implementation that changes content storage, guards, forms, REST or architecture should read them before code changes.

## Current Behavior

Confirmed on production rendered `/about/` on 2026-06-07:

- H1: `Команда Tacticum развивает корпоративные AI-продукты`.
- Timeline renders `2025` badge with heading `Сегодня`, which is stale and trust-breaking on 2026-06-07.
- Page reads as a hybrid of company story, product/delivery trust, technology stack, team gallery and career/culture content.
- Visible text contains internal or English-heavy terms: `product-first`, `delivery`, `backend`, `data/RAG`, `quality gates`, `production rollout`.
- Technology stack still lists generic/outdated tool collections: `BERT`, `NLTK`, `Hadoop`, `Tableau` and broad `передовые технологии` language.
- `id="about-company"` appears twice in rendered HTML: page section and lead form field.
- Footer links to `/about/#careers`, but rendered page has no `id="careers"`.
- Footer links to `/about/#partners`, while `id="partners"` is attached to `Технологические контуры`, not to partners.
- Final CTA includes generic phrase `достичь новых высот`.
- Local `about.page` partials mix retired fallback comments with still-rendered fallback/static bodies and live Bitrix page-content sections.

## Target Behavior

The page should behave as a trust page for a B2B AI vendor:

- Explain why Tacticum can be trusted with a corporate AI contour.
- Show current positioning without stale year/current contradictions.
- Use Russian-first public language with technical terms explained, not exposed as internal shorthand.
- Separate company trust from hiring/culture content or give career content its own clear section and anchor.
- Keep team presentation accessible without hover-only critical information.
- Use proof-safe statements only; no unsupported partner/certification/metric claims.
- Keep page-content/runtime ownership explicit: Bitrix live rows, PHP partials and fallback-retirement status must not contradict each other.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md` | New source register for `ABOUT-*` gaps |
| `docs/workflow/about-page-ux-content-challenge-roadmap-2026-06-07.md` | New phased roadmap for implementation planning |
| `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md` | New issue-ready backlog `ABOUT-WP-*` |
| `docs/workflow/about-page-ux-content-challenge-guard-proposal-2026-06-07.md` | New guard/check proposal for stale timeline, anchors, IDs and rendered content hygiene |
| `docs/workflow/plans/2026-06-07-about-page-ux-content-challenge-documentation.md` | This docs-only plan |
| `docs/workflow/README.md` | Add new documents to workflow index |
| `docs/workflow/current-state.md` | Add `/about/` challenge snapshot to current state |
| `docs/workflow/gap-analysis.md` | Reference `ABOUT-*` layer and planning rule |

## Bitrix Constraints

- [x] `bitrix/` is not touched.
- [x] No new iblock IDs are hardcoded.
- [x] No external URLs or service endpoints are changed.
- [x] No POST endpoints or CSRF/CORS/rate-limit code are changed.
- [x] No inline JS/CSS is introduced.
- [x] No runtime code changes in this documentation task.

## Gates For Future Implementation

| Gate | Required When |
|---|---|
| PM/Content/Sales | Rewriting positioning, company promise, proof-safe claims or CTA copy |
| Legal/Sales | Publishing claims, metrics, logos, partnerships, certifications or customer proof |
| Design | Redesigning team cards, timeline, trust blocks, layout density or visual hierarchy |
| SEO | Changing title/description/canonical, sitemap, routes or footer/navigation labels with SEO impact |
| QA | Adding rendered guards, changing anchors/IDs, changing public form markup, or deploying Bitrix content changes |
| Architect/Content storage | Changing `page_sections/page_blocks` ownership, fallback retirement status, renderer behavior or seed/live sync |

## Risks

| Risk | Mitigation |
|---|---|
| Docs imply approval exists | Mark all `ABOUT-*` gaps as local docs-only and owner-gated |
| Stale `2025 / Сегодня` is fixed superficially by changing only the year | Roadmap requires splitting past milestone from current focus |
| Copy rewrite introduces unsupported claims | Keep proof/claims behind Legal/Sales/PM gate |
| Page-content and PHP fallback drift further apart | Add separate `ABOUT-009` and `ABOUT-WP-07` ownership work package |
| Fast fixes break anchors or forms | Require rendered smoke for duplicate IDs, missing anchors and form interaction |
| UI improvements modify team/person data without approval | Keep team content/photo changes owner-gated |

## Verification

Automated for this docs-only task:

```bash
git diff --check
npm run content:public-hygiene:check
npm run seo:check
npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
```

Manual smoke for this docs-only task:

- Confirm new docs are linked from `README.md`, `current-state.md`, `gap-analysis.md`.
- Confirm no runtime files under `local/`, public page entries, templates or `bitrix/` were changed.

Future implementation verification is specified in the roadmap, backlog and guard proposal.

## Rollback

Remove the four new `about-page-*` docs, this plan file and the small references added to `README.md`, `current-state.md` and `gap-analysis.md`. No runtime rollback is required.

## Docs To Update

- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/README.md`
- [ ] `docs/adr/*` - not required unless future implementation changes content storage/runtime/guard architecture
- [ ] sitemap/robots - not required, no public URL changes
