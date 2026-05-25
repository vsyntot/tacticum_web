# ADR-008: Public Page Local Components

Дата: 24.05.2026

Статус: Принято

## Контекст

После Sprint 05 повторяемые CTA-блоки жили в template includes, FAQ-вызовы копировали длинные `bitrix:news.list` params, light/hero chat markup повторялся на публичных страницах, а `/aiagents/index.php` содержал почти весь page flow. Это работало, но противоречило Bitrix component model: страницы становились владельцами разметки, а повторяемые контракты форм/чата было сложнее ревьюить и тестировать.

## Решение

Повторяемые публичные UI-блоки переносим в локальные компоненты `local/components/tacticum/*`:

- `tacticum:lead.cta` — personal-offer и project-discussion CTA;
- `tacticum:faq.section` — wrapper над `bitrix:news.list` template `faq`;
- `tacticum:chat.surface` — hero/light chat DOM surfaces;
- `tacticum:content.list` — thin wrapper над `bitrix:news.list` для повторяемых публичных списков инфоблоков;
- `tacticum:content.detail` — thin wrapper над `bitrix:news.detail` для статических public detail pages;
- `tacticum:aiagents` — section-level render flow страницы `/aiagents/`.

Публичные `index.php` остаются entry points: до header используют split prolog, задают SEO/page properties, вызывают компоненты и передают параметры. ID инфоблоков передаются только через `tacticum_iblock_id(...)` или semantic `IBLOCK_KEY` внутри wrapper-компонента. Компоненты сохраняют existing frontend contracts: `data-tacticum-form`, `data-form-id`, `data-tacticum-consent`, `#main_chat`, `#aichat`, `data-tacticum-chat`, `data-chat-*`.

FAQ-разделы задаются semantic `SECTION_KEY`; numeric fallback допустим только централизованно внутри `tacticum:faq.section`, чтобы публичные страницы не зависели от ID секций.

## Последствия

- Template includes для CTA больше не являются production pattern и удалены.
- Новые повторяемые публичные блоки нужно проектировать как локальный компонент или шаблон существующего Bitrix-компонента, а не копировать разметку между страницами.
- Page-specific assets/body class для split-prolog pages задаются через page properties (`tacticum_page_assets`, `tacticum_body_class`), а не через новые globals.
- Direct FAQ-вызовы на публичных страницах не добавлять; использовать `tacticum:faq.section`.
- Direct `bitrix:news.list` на публичных страницах не добавлять для повторяемого контента; использовать `tacticum:content.list` или специализированный локальный component/template.
- Direct `bitrix:news.detail` на публичных страницах не добавлять для статического detail-контента; использовать `tacticum:content.detail` или специализированный section-level component.
- Все локальные компоненты `local/components/tacticum/*` должны иметь `.description.php`, `.parameters.php` и `component.php`, чтобы оставаться редактируемыми через Bitrix UI.
- Перед изменением form/chat markup проверять `forms.js`, `chat-agent.js`, `browser:smoke` и static guards.
