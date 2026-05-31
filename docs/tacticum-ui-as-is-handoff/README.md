# Tacticum UI AS IS Handoff

Дата сборки: 30.05.2026

Этот каталог — автономный UI-срез текущей кодовой базы `tacticum.ru` для дизайнера и его LLM. Он предназначен для проектирования TO BE дизайн-системы на основе фактического AS IS.

## Что Внутри

```text
tacticum-ui-as-is-handoff/
  docs/design-system-handoff/
  code/
    public-pages/
    template/
    helpers/
    local-components/
    bitrix-component-templates/
    menus/
    package.json
  assets/
    selected-images/
```

## Рекомендуемый Порядок Чтения

1. `docs/design-system-handoff/README.md`
2. `docs/design-system-handoff/01-as-is-brief.md`
3. `docs/design-system-handoff/02-component-inventory.md`
4. `docs/design-system-handoff/03-page-inventory.md`
5. `docs/design-system-handoff/04-interaction-contracts.md`
6. `docs/design-system-handoff/05-design-tokens-as-is.json`
7. `docs/design-system-handoff/06-known-debt-and-to-be-questions.md`

После документов смотреть код:

1. `code/template/header.php`
2. `code/template/footer.php`
3. `code/template/assets/src/tailwind.css`
4. `code/template/styles/global.css`
5. `code/helpers/component_helpers.php`
6. `code/local-components/lead.cta/`
7. `code/local-components/contact.modal/`
8. `code/local-components/chat.surface/`
9. `code/bitrix-component-templates/news.list/price/`
10. `code/public-pages/contacts/index.php`

## Границы Пакета

В пакет включен только UI/frontend slice:

- публичные page entries;
- глобальный template shell;
- CSS/Tailwind;
- JS интерактива;
- локальные UI-компоненты;
- безопасные helper-файлы для параметров компонентов и меню;
- Bitrix component templates для меню, списков и detail pages;
- меню;
- выбранные визуальные assets.

В пакет намеренно не включены:

- `bitrix/`;
- `local/rest/`;
- `local/api/`;
- private config;
- upload/cache/runtime logs;
- `.git`;
- `node_modules`;
- backend/integration code.

## Важные AS IS Контракты

Дизайнеру можно менять внешний вид, но при проектировании TO BE нужно явно решить, что происходит с этими контрактами:

- forms: `[data-tacticum-form]`, `data-form-id`, `[data-tacticum-consent]`;
- modal: `#tacticum-modal`, `#tacticum-modal-form`, `#contactUsBtn`, `.tacticum-contact-btn`;
- mobile menu: `[data-tacticum-menu-toggle]`, `#tacticum-mobile-menu`;
- FAQ: `.faq-item`, `.faq-question`, `.faq-answer`, `.active`;
- chat: `[data-tacticum-chat]`, `[data-chat-input]`, `[data-chat-send]`, `[data-chat-messages]`;
- price flow: `[data-price-*]` contracts in `code/bitrix-component-templates/news.list/price/`.

## Что Просить У Дизайнера На Выходе

- Figma variables / design tokens;
- component library with variants and states;
- page templates for marketing, catalog, detail, contacts/legal;
- state matrix for forms, menu, modal, FAQ, chat and price builder;
- migration map from AS IS components/selectors to TO BE components;
- notes about which JS/DOM contracts can be preserved and which need dev changes.
