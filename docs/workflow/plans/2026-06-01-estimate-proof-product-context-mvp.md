# Codex Plan: Estimate And Proof Product Context MVP

Issue: internal product vision implementation continuation
Gap ID: `PV-002`, `PV-007`, `PV-012`, `PV-013`, `PV-020`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Связать текущие AS IS estimate/proof assets с продуктовой моделью TO BE: `/calculator/` должен помогать оценивать `Platform / Agents / Dev / Forum`, а `/offer/` должен работать как proof/estimate layer, который ведёт к продуктовым страницам и персональной оценке.

## Non-Goals

- Не менять `/offer/` routing, filters, canonical/noindex decisions or detail behavior.
- Не менять chat-to-lead contract на `/calculator/`.
- Не менять REST/upstream endpoints.
- Не добавлять новые analytics events.
- Не публиковать неподтвержденные claims, metrics, logos or regulatory statuses.

## Target Behavior

- `/calculator/` показывает четыре product-aware estimate paths: Platform, Agents, Dev, Forum.
- Calculator quick replies отражают новую продуктовую модель.
- `calculator-cta` получает safe context `lead_product=ecosystem`, `lead_scenario=product-estimate`.
- `/offer/` catalog получает proof-layer block с ссылками на Platform, Agents, Dev, Forum.
- Offer detail получает product relation block и `lead_product=ecosystem` в существующей `offer-cta`.

## Planned Changes

| File | Change |
|---|---|
| `calculator/index.php` | Add product-aware estimate paths, update quick replies and CTA context |
| `local/components/tacticum/offer.catalog/templates/.default/template.php` | Add proof-layer product relation block |
| `local/templates/tacticum/components/bitrix/news.detail/offer/template.php` | Add product relation block and safe lead context |
| `docs/workflow/lead-form-contract.md` | Update calculator and offer detail context rows |
| `docs/workflow/current-state.md` | Document estimate/proof product context MVP |
| `docs/workflow/gap-analysis.md` | Document progress for proof and product-aware qualification |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] Новые JS/CSS не добавляются
- [x] Infoblock IDs не добавляются
- [x] POST REST bootstrap не меняется
- [x] Offer list/detail component boundaries preserved

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

### Manual Smoke

- `/calculator/` still renders `tacticum:chat.surface` and `calculator-cta`.
- `/offer/` filters, pagination and cards remain unchanged.
- `/offer/<code>/` still has one CTA form with `data-tacticum-form`.
- Product links reach `/platform/`, `/agents/`, `/dev/`, `/forum/`.

## Rollback

Удалить product-aware blocks from `/calculator/`, offer catalog and offer detail; remove added `lead_product` / `lead_scenario` context rows.
