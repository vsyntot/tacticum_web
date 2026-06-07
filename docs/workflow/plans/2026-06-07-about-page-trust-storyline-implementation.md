# Codex Plan: About Page Trust Storyline Implementation

Issue: follow-up `/about/` UX/content challenge implementation
Gap ID: `ABOUT-002`, `ABOUT-003`, `ABOUT-006`, `ABOUT-008`
Workflow lane: Full Feature lane, proof-safe copy/runtime guard slice
Owner agent: Codex
Date: 2026-06-07

## Goal

Сделать `/about/` более связной trust page: объяснить, за что отвечает Tacticum при корпоративном AI-запуске, какие контуры проверяются перед пилотом, как команда ведет внедрение и какой первый шаг предлагается пользователю.

## Non-Goals

- Не добавлять новые claims, метрики, логотипы, сертификаты, customer proof или юридически значимые обещания.
- Не менять публичные URL, canonical, sitemap, robots, title/description.
- Не менять lead form payload, `form_id`, CRM/upstream fields, CSRF/CORS/rate limit or analytics taxonomy.
- Не менять данные команды: имена, фото, роли, контакты, персональные сведения.
- Не менять `bitrix/` и не выполнять live Bitrix row edits из локальной задачи.

## Context Read

- [x] `AGENTS.md`
- [ ] `.github/copilot-instructions.md` (not needed; no backend/API/security contract change)
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] Relevant docs: `about-page-ux-content-challenge-*`
- [x] Relevant files: about page partials, page-content seed, `PublicCopyNormalizer`, `PageContent\Repository`, content hygiene tools

## Current Behavior

`/about/` fast-fix defects are already closed in production, but the page still reads as a mix of company intro, product/delivery trust, technology stack, team/career content and repeated CTA. Bitrix live page-content rows can also keep older generic wording until seed apply/cache refresh.

## Target Behavior

- Hero and company intro explain Tacticum as a team responsible for safe corporate AI launch.
- Trust blocks focus on scenario, data/integration contour, risk control, launch plan and responsibility.
- Technology section describes launch readiness, not a generic tool list.
- Career/final section becomes start-work CTA while keeping `#careers` as a backward-compatible anchor alias.
- Bitrix live rows get proof-safe runtime normalization for `/about/` only, without global short-word replacements.
- Source/rendered hygiene guards block recurrence of generic about-page copy.

## Planned Changes

| File | Change |
|---|---|
| `.bottom.menu.php` | Rename footer career link to start-work anchor. |
| `local/components/tacticum/about.page/templates/.default/parts/company-trust.php` | Rewrite hero, company responsibility copy, stat cards and current-focus timeline wording. |
| `local/components/tacticum/about.page/templates/.default/parts/values-team.php` | Reframe team intro and technology contours around reliable AI launch. |
| `local/components/tacticum/about.page/templates/.default/parts/stack-cta.php` | Replace generic stack/tool list with readiness checks before launch. |
| `local/components/tacticum/about.page/templates/.default/parts/career-final.php` | Add `#start-work`, keep `#careers`, rewrite final CTA. |
| `tools/content-storage-page-content-seed.php` | Align `/about/` wave2 seed rows with the new trust storyline. |
| `local/lib/Tacticum/Content/PublicCopyNormalizer.php` | Add context-aware `/about/` page-content normalization for legacy live rows. |
| `local/lib/Tacticum/PageContent/Repository.php` | Use page-content section normalizer instead of generic array-only normalization. |
| `tools/public-content-hygiene-check.mjs` | Extend source guard for generic about-page terms and new repository normalizer literal. |
| `tools/public-content-rendered-hygiene-check.mjs` | Extend rendered guard/self-test for generic about-page terms and `#start-work`. |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS changes are not introduced
- [x] Infoblock IDs are not hardcoded in new code
- [x] D7 module loading not changed
- [x] POST REST bootstrap not touched

## Risks

| Risk | Mitigation |
|---|---|
| Unsupported proof claims | Copy stays operational and proof-safe; no metrics/logos/certifications/customer names added. |
| Global normalizer side effects | Short/generic title replacements are scoped to `/about/` page-content section/block keys. |
| Stale Bitrix live rows | Runtime normalizer protects old live rows; seed is aligned for future apply. |
| Cache | Production deploy must run `content:public-cache-clear` before rendered hygiene. |
| SEO/navigation | Footer label changes target an existing anchor; `seo:check` required. |

## Verification

### Automated

```bash
php -l local/components/tacticum/about.page/templates/.default/parts/company-trust.php
php -l local/components/tacticum/about.page/templates/.default/parts/values-team.php
php -l local/components/tacticum/about.page/templates/.default/parts/stack-cta.php
php -l local/components/tacticum/about.page/templates/.default/parts/career-final.php
php -l local/lib/Tacticum/Content/PublicCopyNormalizer.php
php -l local/lib/Tacticum/PageContent/Repository.php
php -l tools/content-storage-page-content-seed.php
npm run content:public-hygiene:check
npm run content:public-hygiene:rendered:self-test
npm run product:content:safety:check
npm run seo:check
npm run bitrix:check
git diff --check
```

### Manual Smoke

- URL: `/about/`
- Action: render after deploy and public cache clear
- Expected: no stale timeline, no duplicate IDs, footer anchors resolve, no old generic stack/career copy, page-content source remains `bitrix` for wave2.

## Rollback

Revert the local commit or restore changed files. If deployed, run `npm run content:public-cache-clear` and `npm run content:public-hygiene:rendered:prod` after rollback.

## Docs To Update

- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `/about/` challenge source/backlog/roadmap docs
- [ ] Production evidence after deploy/cache clear
