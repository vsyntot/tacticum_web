# Codex Plan: Product Pages FAQ Hardening

Issue: internal product vision implementation continuation
Gap ID: `PV-006`, `PV-014`, `PV-020`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Добавить на продуктовые страницы `/platform/`, `/agents/`, `/dev/`, `/forum/` короткие product-specific FAQ блоки, чтобы закрыть часть TO BE ожиданий по объяснению продукта, внедрения и безопасных ограничений без новых публичных claims.

## Non-Goals

- Не менять REST, формы, backend, AI endpoints или аналитику.
- Не добавлять новые JS/CSS assets.
- Не публиковать неподтвержденные claims про реестр, сертификации, партнерства, гарантии эффективности или vendor statuses.
- Не менять текущие URL и navigation.

## Target Behavior

- Product pages используют общий renderer FAQ.
- FAQ интерактивно раскрывается через существующий `faq.js`, подключенный через `tacticum_page_assets=faq`.
- Контент FAQ объясняет pilot/discovery/deployment boundaries и не обещает неподтвержденный результат.

## Planned Changes

| File | Change |
|---|---|
| `local/php_interface/include/product_page.php` | Add reusable static FAQ renderer and call it before CTA |
| `platform/index.php` | Enable FAQ asset and add Platform FAQ content |
| `agents/index.php` | Enable FAQ asset and add Agents FAQ content |
| `dev/index.php` | Enable FAQ asset and add Dev FAQ content |
| `forum/index.php` | Enable FAQ asset and add Forum FAQ content |
| `docs/new-big-change/product-vision-handoff/sprints/sprint-05-platform-and-agents-pages.md` | Mark FAQ implementation progress |
| `docs/new-big-change/product-vision-handoff/sprints/sprint-06-dev-and-forum-pages.md` | Mark FAQ implementation progress |
| `docs/new-big-change/product-vision-handoff/sprints/README.md` | Sync sprint index status with implementation progress |
| `docs/workflow/current-state.md` | Document product page FAQ implementation |
| `docs/workflow/gap-analysis.md` | Document product page FAQ progress |

## Verification

### Actual Checks

- [x] `npm run css:build`
- [x] `npm run bitrix:check`
- [x] `npm run template-styles:check`
- [x] `npm run seo:check`
- [x] `npm run css:check`
- [x] `npm run css:syntax`
- [x] `npm run js:check`
- [x] `npm run config:check`
- [x] `npm run gaps:known`
- [x] `git diff --check`
- [x] Claim scan по product-facing страницам: marketing partner/status claims не найдены; единственное совпадение `Минцифры` находится в factual legal details `/contacts/`.
- [x] `php -v` / PHP lint: не выполнен, локально нет PHP CLI (`zsh:1: command not found: php`)

## Rollback

Удалить `faq` arrays из четырех product pages, убрать `tacticum_page_assets=faq` на этих страницах и удалить renderer `tacticum_product_page_render_faq()` / вызов из `tacticum_render_product_page()`.
