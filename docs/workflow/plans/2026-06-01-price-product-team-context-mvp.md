# Codex Plan: Price Product Team Context MVP

Issue: internal product vision implementation continuation
Gap ID: `PV-002`, `PV-012`, `PV-020`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Связать `/price/` с продуктовой моделью как team/staff layer для `Platform / Agents / Dev / Forum`, сохранив AS IS роль страницы: подбор ролей, ставок, пресетов и заявка на подключение команды.

## Non-Goals

- Не превращать `/price/` в pricing/licensing page для продуктов.
- Не менять `price-specialist` modal, `workers_json`, team presets или `/local/rest/tacticum_sale_staff.php`.
- Не менять `news.list/price/script.js`.
- Не менять staff upstream contract.

## Target Behavior

- `/price/` показывает product workstreams: Platform team, Agents pilot, Dev workflow, Forum launch.
- Light chat quick replies отражают продуктовые workstreams.
- `price-cta` получает safe context `lead_product=ecosystem`, `lead_scenario=product-team`.
- Existing `price-specialist` contract remains untouched.

## Planned Changes

| File | Change |
|---|---|
| `price/index.php` | Add product workstreams block, update light chat quick replies, update `price-cta` context |
| `docs/workflow/lead-form-contract.md` | Update price CTA context row |
| `docs/workflow/current-state.md` | Document price product team context MVP |
| `docs/workflow/gap-analysis.md` | Document progress for delivery/team linkage |

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

Удалить product workstreams block из `price/index.php`, вернуть quick replies and `price-cta` context to previous values.
