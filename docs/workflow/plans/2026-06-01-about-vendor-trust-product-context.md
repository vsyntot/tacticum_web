# Codex Plan: About Vendor Trust Product Context

Issue: internal product vision implementation continuation
Gap ID: `PV-006`, `PV-012`, `PV-019`, `PV-020`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Переупаковать `/about/` как vendor trust страницу для продуктовой экосистемы Tacticum и убрать неподтвержденные partner/status claims из публичного слоя.

## Non-Goals

- Не менять team/vacancies component contracts.
- Не менять legal/contact data.
- Не публиковать customer logos, partner statuses or certification claims without evidence.
- Не менять REST/upstream behavior.

## Target Behavior

- `/about/` explains Tacticum as product + delivery team for corporate AI.
- Page includes safe vendor-trust block: architecture, delivery, team, estimate.
- Previous partner-logo/status block is replaced by generic technology contours.
- `about-cta` gets safe `lead_product=ecosystem` context.

## Planned Changes

| File | Change |
|---|---|
| `about/index.php` | Update hero, company copy, history wording, add vendor trust block, replace partner claims, update CTA context |
| `docs/workflow/lead-form-contract.md` | Add/update `/about/` CTA row |
| `docs/workflow/current-state.md` | Document about vendor trust context |
| `docs/workflow/gap-analysis.md` | Document claim cleanup and trust page progress |

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

Вернуть previous `/about/` hero/copy/partner section and remove `lead_product=ecosystem` from `about-cta`.
