# Codex Plan: Homepage Ecosystem MVP

Issue: internal product vision implementation continuation
Gap ID: `PV-001`, `PV-002`, `PV-003`, `PV-009`, `PV-011`, `PV-012`, `PV-013`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Связать уже добавленный product-first слой с главной страницей: первый экран должен показывать Tacticum как экосистему `Platform / Agents / Dev / Forum`, а текущие commercial entry paths должны остаться доступными и понятными.

## Non-Goals

- Не менять REST/upstream контракты.
- Не менять behavior `chat.surface`, `forms.js`, `tacticum:lead.cta`.
- Не убирать `/offer/`, `/services/`, `/price/`, `/calculator/`, `/aiagents/` из пользовательских путей.
- Не публиковать неподтвержденные claims, customer logos, регуляторные статусы или проценты эффекта.
- Не делать полный redesign всей главной и всех downstream pages.

## Context Read

- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/new-big-change/product-vision-handoff/03-information-architecture-to-be.md`
- [x] `docs/new-big-change/product-vision-handoff/09-as-is-to-be-preservation-migration-map.md`
- [x] `docs/new-big-change/product-vision-handoff/sprints/sprint-04-homepage-and-navigation-mvp.md`

## Target Behavior

- Homepage title/description and H1 communicate ecosystem positioning.
- First screen links to `Tacticum Platform`, `Tacticum Agents`, `Tacticum Dev`, `Tacticum Forum`.
- A dedicated ecosystem map explains Platform as core and Agents/Dev/Forum as product layers.
- Existing commercial entry section remains available for project estimate, implementation, team and AI-bot prototype paths.
- Homepage CTA keeps existing form contract and adds safe product-aware `lead_*` context.

## Planned Changes

| File | Change |
|---|---|
| `index.php` | Update SEO metadata, hero copy, hero quick links, chat intro, ecosystem map, commercial entry copy, home CTA context |
| `docs/workflow/lead-form-contract.md` | Update home CTA context row |
| `docs/workflow/current-state.md` | Document homepage ecosystem MVP |
| `docs/workflow/gap-analysis.md` | Document closure/progress for homepage product vision gaps |
| `docs/new-big-change/product-vision-handoff/sprints/sprint-04-homepage-and-navigation-mvp.md` | Mark first implementation slice |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] Новые JS/CSS не добавляются
- [x] Infoblock IDs не добавляются
- [x] POST REST bootstrap не меняется
- [x] Existing `tacticum:lead.cta` and `tacticum:chat.surface` contracts preserved

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

- `/` содержит один H1.
- Hero links reach `/platform/`, `/agents/`, `/dev/`, `/forum/`.
- Commercial entry links to `/offer/`, `/services/`, `/price/`, `/aiagents/` remain visible.
- `home-cta` still uses `tacticum:lead.cta`, consent and `data-tacticum-form`.
- Chat surface keeps existing data contract and no new endpoint behavior.

## Rollback

Вернуть `index.php` к предыдущему hero/router состоянию и откатить документационные строки по homepage ecosystem MVP.
