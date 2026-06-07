# Codex Plan: Product CJM / Use Cases / UX / UI Challenge Snapshot

Issue: not created
Gap ID: `UX-001` - `UX-010`, `UI-001` - `UI-010`, `ARCH-005`, `ARCH-006`, `ARCH-009`, `CONTENT-001` - `CONTENT-005`, `CMP-003`
Workflow lane: Full Feature discovery / documentation
Owner agent: Codex
Date: 2026-06-07

## Goal

Зафиксировать результаты продуктового challenge текущего приложения по CJM, Use Cases, UX and UI as a durable docs-only snapshot, чтобы будущие задачи могли вернуться к выводам без потери контекста.

## Non-Goals

- Не менять runtime, PHP, JS, CSS, REST payloads, analytics or Bitrix content.
- Не создавать новые canonical gap IDs вместо существующих `UX-*`, `UI-*`, `ARCH-*`, `CONTENT-*`, `CMP-*`.
- Не закрывать owner/evidence-dependent gaps без PM/Sales/Legal/UX/Design/QA/Security/Analytics approval.
- Не менять `/agents/` vs `/aiagents/`, `/price/`, forms, chat, offer or calculator behavior.

## Context Read

- [x] `AGENTS.md`
- [x] `.github/copilot-instructions.md` indirectly covered by prior repo workflow review for backend/security conventions; this docs-only task does not touch code contracts
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] Relevant ADR: not required, no architecture/runtime decision changes
- [x] Relevant files:
  - `local/php_interface/include/tacticum_config.php`
  - `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md`
  - `docs/workflow/product-to-be-design-system-decision-2026-06-04.md`
  - `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
  - `docs/workflow/product-tech-challenge-execution-roadmap-2026-06-04.md`
  - `local/components/tacticum/home.page/templates/.default/parts/hero.php`
  - `local/php_interface/include/product_page_blocks/page.php`
  - `local/php_interface/include/product_page_blocks/use_cases.php`
  - `local/php_interface/include/product_page_blocks/procurement.php`
  - `local/components/tacticum/lead.cta/templates/.default/form.php`
  - `local/components/tacticum/offer.catalog/templates/.default/parts/*.php`
  - `local/templates/tacticum/components/bitrix/news.detail/offer/parts/*.php`
  - `local/templates/tacticum/components/bitrix/news.list/price/parts/*.php`
  - `local/components/tacticum/aiagents/templates/.default/parts/*.php`

## Current Behavior

Product-first structure exists: `/platform/`, `/agents/`, `/dev/`, `/forum/` are product pages; `/offer/`, `/calculator/`, `/price/`, `/services/` and `/aiagents/` act as commercial/utility routes. Forms send controlled `lead_*` context and product analytics are no-PII. Current weakness is not missing surfaces, but the missing enterprise decision path across buyer role, use case, proof and next step.

## Target Behavior

Docs capture the 2026-06-07 challenge verdict, observations, mapped existing gaps, priority backlog, owner questions and future implementation guards. Future work can reference this snapshot without treating it as owner approval or runtime evidence.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/product-cjm-usecases-ux-ui-challenge-2026-06-07.md` | New docs-only challenge snapshot |
| `docs/workflow/plans/2026-06-07-product-cjm-usecases-ux-ui-challenge-snapshot.md` | This plan |
| `docs/workflow/README.md` | Add snapshot to workflow docs index |
| `docs/workflow/current-state.md` | Add current-state note for the 2026-06-07 product challenge snapshot |
| `docs/workflow/gap-analysis.md` | Add planning rule and pointer under product challenge layer |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS через `Asset` not applicable; no JS/CSS changes
- [x] Infoblock IDs через config helper not applicable; no code changes
- [x] D7 `Loader::includeModule()` not applicable; no PHP changes
- [x] POST REST bootstrap not applicable; no endpoint changes

## Risks

| Risk | Mitigation |
|---|---|
| Security/PII | Snapshot uses source paths and aggregate observations only; no payloads, raw leads, raw logs or private evidence |
| Backward compatibility | Docs-only changes do not affect runtime |
| Cache | No runtime/cache changes |
| SEO | No public URL/meta/sitemap changes |
| Deploy | No deploy required beyond docs sync |

## Verification

### Automated

```bash
npm run product:gaps:check
npm run product:challenge:check
```

### Manual Smoke

- URL/API: not applicable
- Action: review generated docs and links
- Expected: snapshot is discoverable from workflow docs and does not claim owner approval

## Rollback

Remove the new snapshot and plan files, and revert the three index references in `README.md`, `current-state.md` and `gap-analysis.md`.

## Docs To Update

- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/README.md`
- [ ] `docs/adr/*` not required
- [ ] `.github/copilot-instructions.md` not required
- [ ] sitemap/robots not required
