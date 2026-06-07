# Codex Plan: Content Language / Storyline Challenge Documentation

Issue: none / user-requested documentation snapshot
Gap ID: local docs-only `CLS-*` register
Workflow lane: Full Feature discovery / documentation
Owner agent: Codex
Date: 2026-06-07

## Goal

Зафиксировать результаты контентного challenge сайта `tacticum.ru`: подача, русский язык, связность, общий сторилайн, proof/claims consistency and page-level narrative risks. Создать документы, к которым можно вернуться при будущей переписи публичного контента.

## Non-Goals

- Не менять PHP/JS/CSS/runtime.
- Не менять Bitrix admin content.
- Не менять SEO routes, canonical, sitemap or metadata.
- Не менять REST/form/chat/CRM/analytics payloads.
- Не утверждать proof/claims without Sales/Legal/PM evidence.

## Context Read

- [x] `AGENTS.md`
- [ ] `.github/copilot-instructions.md` — not required; task is docs-only content/product challenge, not backend/API/security implementation
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] Relevant product challenge docs: `product-tech-challenge-gap-register-2026-06-04.md`, `product-cjm-usecases-ux-ui-challenge-2026-06-07.md`
- [x] Relevant files: rendered production pages, product/page-content mappers, product block renderers, page-content seed, offer detail templates

## Current Behavior

Public site has a product-first structure, but rendered content mixes Russian and English/internal terms, exposes service labels on product pages, and presents multiple company identities: product ecosystem, implementation agency, staffing/rates catalog, estimate catalog and Telegram bot demo.

Confirmed examples from rendered production pages on 2026-06-07:

- product pages show `Product fit`, `fits`, `not_fits`, `start`, `Use cases`, `Security / procurement`;
- `/services/` and `/price/` show `Delivery layer`, `Product workstreams`, `Platform assessment`, `Agents pilot`, `Dev workflow`, `Forum launch`;
- `/aiagents/` can read as standalone Telegram bot product;
- offer detail pages use risk/fear framing and generic claims;
- proof/case copy can conflict with cautious product copy.

## Target Behavior

The documentation layer should make future content work actionable:

- one source gap register with `CLS-*` IDs;
- execution roadmap by phases and gates;
- issue-ready backlog;
- Russian-first glossary and editorial rules;
- links from workflow index/current/gap docs.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/content-language-storyline-challenge-gap-analysis-2026-06-07.md` | New source register for content/language/storyline gaps |
| `docs/workflow/content-language-storyline-challenge-roadmap-2026-06-07.md` | New phased execution roadmap |
| `docs/workflow/content-language-storyline-challenge-issue-backlog-2026-06-07.md` | New issue-ready backlog `CLS-WP-*` |
| `docs/workflow/content-language-storyline-public-glossary-2026-06-07.md` | New Russian-first glossary and editorial rules |
| `docs/workflow/plans/2026-06-07-content-language-storyline-challenge-documentation.md` | This plan snapshot |
| `docs/workflow/README.md` | Add new docs to workflow document index |
| `docs/workflow/current-state.md` | Add content-language challenge documentation layer |
| `docs/workflow/gap-analysis.md` | Add content-language challenge layer and planning rule |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS через `Asset` — not applicable, docs-only
- [x] Infoblock IDs через config helper — not applicable, docs-only
- [x] D7 `Loader::includeModule()` в новом/shared коде — not applicable, no code
- [x] POST REST bootstrap соблюдён — not applicable, no REST changes

## Risks

| Risk | Mitigation |
|---|---|
| Documentation pretends approval exists | Mark all docs as docs-only / owner approvals pending |
| New IDs conflict with canonical product-tech gaps | Use local `CLS-*` IDs and map them to canonical IDs |
| Future implementation mixes copy and payload/route changes | Add explicit do-not-start and lane rules |
| Claims/legal risk | Block proof/claims rewrite behind Legal/Sales/PM evidence |
| Existing uncommitted docs overwritten | Add only new files and surgical references; do not revert previous docs |

## Verification

### Automated

```bash
git diff --check
```

### Manual Smoke

- Confirm new docs are linked from `README.md`, `current-state.md`, `gap-analysis.md`.
- Confirm no runtime files under `local/`, `bitrix/`, public page entries or templates were changed.

## Rollback

Remove the four new content-language docs, this plan file, and the small references added to workflow index/current/gap docs. No runtime rollback required.

## Docs To Update

- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/README.md`
- [ ] `docs/adr/*` — not required unless future implementation changes content storage/guard architecture
- [ ] `.github/copilot-instructions.md` — not required for docs-only snapshot
- [ ] sitemap/robots — not required, no public URL changes
