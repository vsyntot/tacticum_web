# Codex Plan: AIAgents To Agents Compatibility Bridge

Issue: internal product vision implementation continuation
Gap ID: `PV-002`, `PV-004`, `PV-009`, `PV-012`, `PV-013`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Сохранить `/aiagents/` как текущий рабочий money/SEO entry для Telegram-ботов и добавить bridge к новой странице `/agents/`, не принимая преждевременное redirect/canonical решение.

## Non-Goals

- Не редиректить `/aiagents/`.
- Не менять canonical strategy.
- Не менять aiagents iblock/list component contract.
- Не менять form endpoint or JS.
- Не удалять Telegram demo flow.

## Target Behavior

- `/aiagents/` объясняет, что Telegram-бот может быть первым сценарием `Tacticum Agents`.
- Пользователь может перейти на `/agents/` или остаться в Telegram demo path.
- Existing `aiagents-inline` form sends safe `lead_product=agents` context.

## Planned Changes

| File | Change |
|---|---|
| `local/components/tacticum/aiagents/templates/.default/template.php` | Add compatibility bridge and `lead_product=agents` hidden context |
| `docs/workflow/lead-form-contract.md` | Update `/aiagents/` context row |
| `docs/workflow/current-state.md` | Document compatibility bridge |
| `docs/workflow/gap-analysis.md` | Document deferred canonical/redirect decision |

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

Удалить compatibility bridge and `lead_product=agents` hidden input from aiagents component template.
