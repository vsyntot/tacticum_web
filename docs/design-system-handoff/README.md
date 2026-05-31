# Design System Handoff — Tacticum AS IS

Дата: 30.05.2026

Статус: входной пакет для дизайнера, который будет формировать новую версию дизайн-системы `tacticum.ru`.

## Назначение

Этот каталог описывает фактическую организацию UI на сайте `tacticum.ru`: дизайн-токены, стили, компоненты, интерактивные JS-контракты и известные долги. Цель пакета — дать дизайнеру достаточно контекста, чтобы проектировать TO BE дизайн-систему, опираясь на текущую реализацию, а не только на скриншоты.

Пакет не заменяет продуктовую стратегию, UX-исследование или финальную Figma-библиотеку. Он фиксирует AS IS и формулирует вопросы, которые нужно закрыть в TO BE.

## Как Читать

1. `01-as-is-brief.md` — быстрый обзор текущей UI-архитектуры.
2. `02-component-inventory.md` — инвентаризация компонентов, вариантов и состояний.
3. `03-page-inventory.md` — карта публичных страниц и их роли в воронке.
4. `04-interaction-contracts.md` — интерактив, DOM-контракты и JS-состояния.
5. `05-design-tokens-as-is.json` — машиночитаемый снимок текущих токенов.
6. `06-known-debt-and-to-be-questions.md` — долги AS IS и вопросы для TO BE дизайн-системы.

## Короткая Картина AS IS

Сайт построен на 1C-Bitrix с кастомным шаблоном:

```text
public page entry
  -> local/templates/tacticum/header.php
  -> Tailwind generated utilities
  -> local/templates/tacticum/styles/global.css
  -> local/components/tacticum/*
  -> local/templates/tacticum/js/*.js
  -> local/templates/tacticum/footer.php
```

Текущий подход — прагматичная Bitrix/Tailwind-система:

- визуальные блоки часто собираются utility-классами прямо в PHP-шаблонах;
- повторяемые секции вынесены в локальные Bitrix-компоненты;
- ручные стили собраны в одном runtime-файле `styles/global.css`;
- интерактив сделан на vanilla JS и привязан к `data-*`, id и class контрактам;
- отдельной формальной дизайн-системы, Storybook или token pipeline нет.

## Основные Исходники

| Зона | Файл / каталог | Что смотреть |
|---|---|---|
| Template shell | `local/templates/tacticum/header.php` | Подключение CSS/JS, CSP, header, top menu |
| Footer shell | `local/templates/tacticum/footer.php` | Footer, bottom menu, mobile menu, contact modal |
| Tailwind source | `local/templates/tacticum/assets/src/tailwind.css` | AS IS токены и source scan |
| Generated CSS | `local/templates/tacticum/tailwind.generated.css` | Сгенерированные utilities |
| Manual CSS | `local/templates/tacticum/styles/global.css` | Ручные стили компонентов и страниц |
| JS | `local/templates/tacticum/js/` | Интерактив сайта |
| Local components | `local/components/tacticum/` | Повторяемые UI-компоненты |
| Bitrix component templates | `local/templates/tacticum/components/bitrix/` | Шаблоны меню, списков, detail-страниц |
| Example page | `contacts/index.php` | Пример публичной страницы с общей CTA-формой |

## Что Важно Для TO BE

Дизайнеру нужно проектировать не только статичные экраны, а систему, которая сможет лечь на текущую архитектуру или заменить ее с понятной миграцией.

Минимальные TO BE результаты:

- token spec: color, typography, spacing, radius, elevation, z-index, breakpoints;
- component spec: buttons, links, inputs, selects, checkbox, cards, badges, accordion, modal, toast, chat, CTA;
- page templates: marketing page, catalog/list, detail, contacts/legal;
- state spec: hover, focus, active, selected, loading, disabled, error, empty, success;
- interaction spec: mobile menu, modal, forms, FAQ, chat, filters;
- migration map: AS IS component/selector -> TO BE component.

## Важное Ограничение

JS сейчас зависит от конкретных DOM-контрактов. При редизайне можно менять внешний вид, но нужно явно решить, какие контракты сохраняются, а какие требуют разработки.

Примеры контрактов:

- forms: `[data-tacticum-form]`, `data-form-id`, `[data-tacticum-consent]`;
- mobile menu: `[data-tacticum-menu-toggle]`, `#tacticum-mobile-menu`;
- modal: `#tacticum-modal`, `#contactUsBtn`, `.tacticum-contact-btn`;
- chat: `[data-tacticum-chat]`, `[data-chat-input]`, `[data-chat-send]`;
- FAQ: `.faq-item`, `.faq-question`, `.faq-answer`, `.active`.

## Рекомендованный Формат Работы С Дизайнером

1. Дать дизайнеру этот каталог и текущие production/staging ссылки.
2. Совместно пройти `02-component-inventory.md` и отметить компоненты, которые должны войти в Figma library.
3. По `04-interaction-contracts.md` согласовать обязательные состояния.
4. По `05-design-tokens-as-is.json` решить, что остается, что переименовывается и какие токены добавляются.
5. По `06-known-debt-and-to-be-questions.md` закрыть решения до старта детальной отрисовки.

