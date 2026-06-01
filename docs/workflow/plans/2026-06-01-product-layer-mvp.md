# Codex Plan: Product Layer MVP

Issue: internal product vision implementation
Gap ID: `PV-001` - `PV-005`, `PV-009`, `PV-012`, `PV-013`, `PV-015`
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Добавить первый безопасный product-first слой сайта: новые страницы `Tacticum Platform`, `Tacticum Agents`, `Tacticum Dev`, `Tacticum Forum` и продуктовую навигацию, сохранив текущие коммерческие сценарии `/offer/`, `/price/`, `/calculator/`, `/services/`, `/aiagents/`.

## Non-Goals

- Не менять REST/upstream контракты.
- Не менять `/price/` staff-order flow.
- Не менять `/offer/` роутинг, sitemap и detail behavior.
- Не публиковать неподтвержденные claims про реестр, доверенное ПО, ФСТЭК/ФСБ, клиентские логотипы или проценты эффекта.
- Не делать полный редизайн главной и всех money pages в этом срезе.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/design-system-handoff/README.md`
- [x] `docs/new-big-change/product-vision-handoff/README.md`
- [x] `docs/new-big-change/product-vision-handoff/09-as-is-to-be-preservation-migration-map.md`

## Current Behavior

Сайт работает как lead-generation system с четырьмя коммерческими входами:

- `/offer/` - примеры расчетов;
- `/services/` - внедрение AI;
- `/price/` - команда под задачу;
- `/aiagents/` - AI-боты;
- `/calculator/` - оценка проекта через чат.

Отдельного публичного продуктового слоя `Platform / Agents / Dev / Forum` нет.

## Target Behavior

- Новые продуктовые URL доступны и имеют SEO defaults:
  - `/platform/`;
  - `/agents/`;
  - `/dev/`;
  - `/forum/`.
- Верхнее и нижнее меню показывают продуктовый слой, не скрывая текущие delivery/money pages.
- Продуктовые страницы используют текущий Bitrix шаблон, общую CTA форму и safe `lead_*` context.
- `/aiagents/` остается рабочим legacy/current entry и не редиректится в этом срезе.

## Planned Changes

| File | Change |
|---|---|
| `local/php_interface/init.php` | Подключить shared renderer для продуктовых страниц |
| `local/php_interface/include/product_page.php` | Добавить безопасный renderer для product pages |
| `.top.menu.php` | Добавить product-first navigation |
| `.bottom.menu.php` | Добавить product links в footer |
| `platform/.left.menu.php` | Добавить dropdown children для product menu |
| `platform/index.php` | Новая страница Tacticum Platform |
| `agents/index.php` | Новая страница Tacticum Agents |
| `dev/index.php` | Новая страница Tacticum Dev |
| `forum/index.php` | Новая страница Tacticum Forum |
| `docs/workflow/lead-form-contract.md` | Добавить form IDs/context для product pages |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS через `Asset`; новых JS/CSS не планируется
- [x] Infoblock IDs не добавляются
- [x] POST REST bootstrap не меняется
- [x] Новые функции с префиксом `tacticum_`

## Risks

| Risk | Mitigation |
|---|---|
| SEO duplicate around `/agents/` and `/aiagents/` | `/aiagents/` не трогаем; `/agents/` позиционируем как корпоративный product overview |
| Unsafe claims | Использовать только safe wording без статусов реестра, логотипов и процентов |
| Form context drift | Использовать уже allowlisted `lead_*` fields |
| Navigation overload | Product dropdown + delivery dropdown, текущие money pages остаются в меню |
| Broken Bitrix menu tree | Использовать существующий menu component pattern and `platform/.left.menu.php` |

## Verification

### Automated

```bash
npm run bitrix:check
npm run seo:check
npm run template-styles:check
```

Если CSS не менялся, `css:check` не обязателен, но может быть запущен как guard.

### Actual Checks

- [x] `npm run bitrix:check`
- [x] `npm run template-styles:check`
- [x] `npm run seo:check`
- [x] `npm run css:check`
- [x] `npm run js:check`
- [x] `npm run config:check`
- [x] `npm run gaps:known`
- [ ] `php -v` / PHP lint: локально недоступен PHP CLI (`php: command not found`)

### Manual Smoke

- `/platform/`, `/agents/`, `/dev/`, `/forum/` открываются.
- Header desktop/mobile содержит product links.
- Footer содержит product links.
- Product CTA forms сохраняют `data-tacticum-form`, consent и `lead_product`.
- Existing URLs `/offer/`, `/price/`, `/calculator/`, `/services/`, `/aiagents/` не меняются.

## Rollback

Удалить новые product page directories, вернуть `.top.menu.php`, `.bottom.menu.php`, `init.php` и docs к предыдущему состоянию.

## Docs To Update

- [x] `docs/workflow/plans/2026-06-01-product-layer-mvp.md`
- [x] `docs/workflow/lead-form-contract.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
