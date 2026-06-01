# Codex Plan: Contacts Routing Product Context

Issue: internal product vision implementation continuation
Gap ID: `PV-009`, `PV-012`, `PV-020`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Уточнить `/contacts/` как operational contact + routing page для product-first сайта, сохранив фактические контакты, юридический адрес, карту и существующую форму.

## Non-Goals

- Не менять телефон, email, юридический адрес, карту или реквизиты.
- Не менять Yandex map widget / CSP assumptions.
- Не менять `tacticum:lead.cta`, `forms.js` or REST/upstream behavior.
- Не добавлять новые analytics events.

## Target Behavior

- `/contacts/` помогает выбрать ближайший next step: продуктовый пилот, внедрение, оценка проекта или команда.
- `contacts-cta` получает safe context `lead_product=ecosystem`, `lead_scenario=contact-routing`.
- Legal/contact details remain factual and unchanged.

## Planned Changes

| File | Change |
|---|---|
| `contacts/index.php` | Update SEO copy, hero copy, add routing cards, update `contacts-cta` context |
| `docs/workflow/lead-form-contract.md` | Update `/contacts/` CTA row |
| `docs/workflow/current-state.md` | Document contacts routing context |
| `docs/workflow/gap-analysis.md` | Document progress for contact routing |

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

Удалить routing cards from `/contacts/`, restore previous title/description/hero copy and remove added `lead_product` / `lead_scenario` context from `contacts-cta`.
