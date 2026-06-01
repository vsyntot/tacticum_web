# Codex Plan: Services Delivery Layer MVP

Issue: internal product vision implementation continuation
Gap ID: `PV-002`, `PV-003`, `PV-009`, `PV-012`, `PV-020`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Переупаковать `/services/` как delivery layer для продуктовой экосистемы, сохранив текущую роль страницы как входа во внедрение AI-решений и не ломая форму `services-cta`.

## Non-Goals

- Не превращать `/services/` в основной каталог продуктов.
- Не менять REST/upstream contracts.
- Не менять компонент `tacticum:lead.cta`.
- Не убирать текущие entry cards на `/offer/`, `/price/`, `/calculator/`.
- Не публиковать неподтвержденные claims.

## Target Behavior

- `/services/` показывает, что внедрение связано с `Platform`, `Agents`, `Dev`, `Forum`.
- Текущий delivery process и services content list остаются.
- CTA получает безопасный context `lead_product=ecosystem`, `lead_scenario=product-delivery`.

## Planned Changes

| File | Change |
|---|---|
| `services/index.php` | Add product-delivery block and update `services-cta` context |
| `docs/workflow/lead-form-contract.md` | Update services CTA row |
| `docs/workflow/current-state.md` | Document delivery layer MVP |
| `docs/workflow/gap-analysis.md` | Document progress for delivery/product linkage |

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
- [ ] `php -v` / PHP lint: локально недоступен PHP CLI (`php: command not found`)

## Rollback

Удалить новый product-delivery block из `services/index.php` и вернуть `services-cta` context к предыдущему значению.
