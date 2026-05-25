# Codex Plan: Sprint 15 Product Marketing Architecture

Issue: Sprint 15
Gap ID: PMG-001 - PMG-010
Workflow lane: Full Feature Lane + Security / Integration review for lead context
Owner agent: Codex
Date: 25.05.2026

## Goal

Собрать публичный сайт в понятную продуктовую лестницу: рассчитать проект, внедрить AI-решение, собрать команду и запустить AI-бота. Усилить CTA, proof copy, `/offer/` conversion и lead qualification context без новых публичных URL и без изменения AI upstream contract.

## Non-Goals

- Не создавать новые индексируемые routes для отраслевых кластеров.
- Не менять Bitrix core.
- Не менять AI upstream endpoint paths или response shapes.
- Не добавлять неподтвержденные метрики, гарантии и testimonials.
- Не закрывать внешние Sprint 14 gates через Sprint 15.

## Context Read

- [x] `AGENTS.md`
- [x] `.github/copilot-instructions.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/product-marketing-gap-analysis.md`
- [x] Relevant ADR: ADR-001, ADR-003, ADR-004, ADR-005, ADR-007, ADR-008, ADR-009
- [x] Relevant files: public pages, `lead.cta`, `/offer/` catalog/detail templates, `forms.js`, `tacticum_form.php`, lead form contract

## Current Behavior

Главная и money pages описывают AI-разработку, тарифы, калькулятор, каталог расчетов и AI-ботов как соседние сущности. CTA похожи между страницами, proof claims не полностью согласованы, `/offer/` сильнее работает как каталог, чем как bridge к персональной оценке, а обычная lead form не передает structured non-PII context в sales.

## Target Behavior

Первый экран и ключевые страницы объясняют роль каждого входа в funnel. CTA обещают конкретный следующий шаг. `/offer/` показывает отраслевые/scenario кластеры через существующие noindex catalog states. Формы передают safe context через hidden fields, backend добавляет этот context в существующее поле `task`, а analytics по-прежнему не содержит PII.

## Planned Changes

| File | Change |
|---|---|
| `index.php`, `services/index.php`, `price/index.php`, `calculator/index.php`, `aiagents/index.php` | Обновить positioning, route cards, page framing, CTA copy и hidden context params |
| `local/components/tacticum/lead.cta/*` | Поддержать hidden lead context для shared CTA |
| `local/components/tacticum/aiagents/templates/.default/template.php` | Привести tone к B2B entry и добавить context fields |
| `local/components/tacticum/offer.catalog/templates/.default/template.php` | Усилить conversion bridge и scenario/industry cluster links без indexable routes |
| `local/templates/tacticum/components/bitrix/news.detail/offer/template.php` | Уточнить contextual CTA и hidden offer context |
| `local/rest/tacticum_form.php` | Упаковать safe lead context в существующий `task` payload |
| `docs/workflow/*` | Обновить sprint, gap, current-state и lead form contract |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS через `Asset`
- [x] Infoblock IDs через config helper
- [x] D7 `Loader::includeModule()` в новом/shared коде
- [x] POST REST bootstrap соблюдён

## Risks

| Risk | Mitigation |
|---|---|
| Security/PII | Hidden context содержит только page/intent/scenario/budget labels; analytics не получает message/contact fields |
| Backward compatibility | Existing `form_id`, `data-tacticum-form`, chat and price `data-*` contracts сохраняются |
| Cache | Не меняем cache architecture; `/offer/` только template/render |
| SEO | Новые отраслевые links ведут в существующие noindex/canonical catalog states |
| Deploy | Запустить static guards; production smoke остается post-deploy gate |

## Verification

### Automated

```bash
php -l local/rest/tacticum_form.php
npm run css:check
npm run template-styles:check
npm run seo:check
npm run bitrix:check
```

### Manual Smoke

- URL/API: `/`, `/services/`, `/price/`, `/offer/`, `/calculator/`, `/aiagents/`
- Action: rendered CTA/chat/filter/form smoke
- Expected: no broken contracts, CTA context hidden fields present, `/offer/catalog/...` remains noindex/canonical `/offer/`

## Rollback

Откатить touched public/component/templates/docs and `tacticum_form.php` changes одним PR revert; hidden context removal leaves default form payload intact.

## Docs To Update

- [x] `docs/workflow/gap-analysis.md`
- [ ] `docs/workflow/current-state.md`
- [ ] `docs/workflow/product-marketing-gap-analysis.md`
- [ ] `docs/workflow/lead-form-contract.md`
- [ ] `docs/workflow/sprints/2026-05-25-sprint-15-product-marketing-architecture.md`
