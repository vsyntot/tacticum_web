# Component State Contract

Дата: 02.06.2026

## Назначение

`docs/design-system-handoff/07-component-state-contract.json` - проверяемый AS IS -> TO BE мост по компонентам и состояниям.

Он нужен дизайнеру, frontend, QA и LLM-assisted refactoring, чтобы редизайн не опирался только на скриншоты. В текущем сайте часть UI является behavior-bearing: визуальный слой можно менять, но selectors, form fields, page asset flags and JS contracts нельзя терять без отдельной dev-задачи.

## Что Зафиксировано

Контракт покрывает семь критичных зон:

| ID | Компонент | Почему критичен |
|---|---|---|
| `global-navigation` | Header, desktop/mobile menu, contact CTA | Меню и modal trigger завязаны на JS selectors |
| `contact-modal` | Глобальная contact form modal | Focus trap, close behavior and form submit contract |
| `lead-cta-form` | Page/product CTA forms | Lead generation, consent, qualification fields and analytics taxonomy |
| `chat-surface` | Hero/light AI chat | Chat REST, typing, quick replies and lead handoff |
| `faq-accordion` | FAQ sections | `faq.js` зависит от class contract |
| `price-team-builder` | `/price/` staff/team configurator | Самый сложный interactive flow, rich staff payload |
| `product-page-blocks` | `/platform/`, `/agents/`, `/dev/`, `/forum/` blocks | AS IS block locator taxonomy for design/QA previews |

Для каждого компонента указаны:

- source files;
- preserved selectors / attributes;
- required source patterns;
- states that TO BE design must cover;
- open design/frontend decisions.

## Guard

Запуск:

```bash
npm run design:components:check
```

Guard проверяет:

- JSON валиден;
- обязательные component ids присутствуют;
- source files существуют;
- required selectors/attributes всё ещё есть в templates/JS;
- state coverage groups не потеряли ключевые состояния;
- `package.json` содержит script `design:components:check`.

## Когда Обновлять

Обновить контракт и запустить guard нужно, если меняются:

- `local/components/tacticum/lead.cta/`;
- `local/components/tacticum/contact.modal/`;
- `local/components/tacticum/chat.surface/`;
- `local/templates/tacticum/js/forms.js`;
- `local/templates/tacticum/js/modal.js`;
- `local/templates/tacticum/js/menu.js`;
- `local/templates/tacticum/js/faq.js`;
- `local/templates/tacticum/js/chat-agent.js`;
- `local/templates/tacticum/components/bitrix/news.list/price/`;
- `local/php_interface/include/product_page_blocks/`;
- component/state naming in TO BE design specs.

## Migration Rule

Если дизайнер предлагает изменить markup, нужно явно классифицировать изменение:

| Migration type | Что разрешено | Что нужно |
|---|---|---|
| `visual restyle` | classes, colors, spacing, layout | Preserve selectors, run guards |
| `contract-preserving component split` | move markup into component/partial | Preserve selectors, update sources in JSON |
| `contract migration` | change selectors/fields/behavior | Dev task, JS update, smoke, docs update |
| `new interaction` | new states/controls | Page asset decision, JS implementation, QA/browser smoke |

## Граница Решения

Этот contract закрывает AS IS reproducibility для компонентов и состояний. Он не закрывает финальную TO BE дизайн-систему:

- visual anatomy still needs Figma/spec decisions;
- state visuals still need design approval;
- `/price/` mobile team builder UX remains a dedicated design task;
- chat visual/state spec remains a dedicated product pattern decision;
- product proof/status components still need claim governance mapping.

Sprint 20 draft decision 04.06.2026: `docs/workflow/product-to-be-design-system-decision-2026-06-04.md` adds TO BE state matrices for forms/modal/CTA, chat and `/price/` mobile behavior. These matrices are approval inputs; selectors and behavior-bearing contracts from `07-component-state-contract.json` remain mandatory until a separate contract migration is approved.

Sprint 21 draft decision 04.06.2026: `docs/workflow/product-frontend-component-hardening-decision-2026-06-04.md` defines the runtime-safe component boundary for product blocks, `/price/`, forms, chat and FAQ wrappers. It keeps current behavior-bearing selectors as mandatory and treats any selector/payload/endpoint change as a separate contract migration with smoke evidence.
