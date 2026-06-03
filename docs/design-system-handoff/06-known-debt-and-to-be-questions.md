# 06. Known Debt And TO BE Questions

Дата: 30.05.2026

## Назначение

Этот документ формулирует, что в AS IS UI работает, но недостаточно систематизировано, и какие вопросы нужно закрыть при проектировании новой дизайн-системы.

## Что Уже Хорошо Работает

- Есть централизованный Bitrix template shell.
- CSS/JS подключаются через `Bitrix\Main\Page\Asset`.
- Повторяемые CTA-формы вынесены в `tacticum:lead.cta`.
- Footer modal вынесен в `tacticum:contact.modal`.
- Chat surfaces вынесены в `tacticum:chat.surface`.
- FAQ, content list/detail, offer и aiagents имеют локальные компоненты.
- Формы имеют единый JS submit flow.
- Analytics wrapper маскирует/ограничивает параметры и не отправляет PII.
- Optional JS подключается через page asset flags.
- `template_styles.css` выведен из активного использования; рабочий ручной CSS — `styles/global.css`.

## Основные Долги AS IS

### 1. Токены Формализованы Только На AS IS Уровне

Есть минимальные implemented Tailwind tokens:

- `primary`;
- `secondary`;
- `button radius`.

На 01.06.2026 добавлен проверяемый AS IS token contract:

- `05-design-tokens-as-is.json` фиксирует implemented tokens, observed CSS candidates, known drift and migration rules;
- `npm run design:tokens:check` сверяет JSON с `tailwind.css`, `global.css`, `forms.js` и `package.json`;
- drift уже отмечен явно: `#001F40` vs `#001F3F`, `#007bff` vs `#0066CC`.

Но TO BE token system всё ещё не утверждена. Нужно нормализовать:

- typography scale;
- spacing scale;
- elevation;
- semantic colors;
- surface colors;
- border colors;
- focus ring;
- z-index;
- component radius;
- chart colors;
- motion tokens.

TO BE question: какие токены становятся canonical source of truth — Figma variables, Tailwind theme, JSON token file или комбинация, и как approved TO BE names мапятся на текущий AS IS contract?

### 2. Два Языка Styling

Сейчас используются одновременно:

- Tailwind utility classes в PHP;
- BEM-like классы в `global.css`, например `tacticum-contact-form__control`;
- page-specific classes, например `price-hero-bg`, `services-hero-bg`;
- generic classes, например `.feature-card`, `.nav-link`, `.faq-item`.

TO BE question: какой стиль реализации должен быть основным для новых компонентов?

Варианты:

- Tailwind utilities only;
- component CSS classes;
- hybrid, but with strict rules;
- generated token classes from design tokens.

### 3. `global.css` Содержит Разные Слои

В одном файле сейчас смешаны:

- global fixes;
- page-specific hero backgrounds;
- form styles;
- modal styles;
- FAQ styles;
- chat styles;
- price/services/about/aiagents styles;
- compatibility responsive utility overrides.

TO BE question: нужно ли разделить CSS на layers/modules или оставить один runtime файл с четкой структурой?

### 4. Карточки Не Имеют Единого Base Component

AS IS карточки визуально похожи, но реализованы по-разному:

- contact cards;
- service cards;
- price cards;
- offer cards;
- case cards;
- AI agent cards;
- team cards;
- testimonial cards.

TO BE question: какие card variants нужны в дизайн-системе?

Минимум:

- info card;
- feature card;
- product/service card;
- catalog card;
- person card;
- testimonial card;
- pricing/staff card;
- metric/stat card.

На 02.06.2026 AS IS -> TO BE migration baseline зафиксирован в `08-as-is-to-be-migration-map.json`: текущие behavior-bearing components получили preliminary TO BE names, migration types and gates. Это не заменяет финальный Figma component inventory, но задает маршрут: где достаточно `visual-restyle`, где нужен `contract-preserving-split`, а где потребуется `contract-migration` или `new-interaction`.

### 5. Формы Нуждаются В Полной State Spec

Технически формы работают. На 02.06.2026 behavior-bearing selectors and required state coverage зафиксированы в `07-component-state-contract.json` и проверяются через `npm run design:components:check`.

Но визуальная дизайн-спека состояний всё ещё не оформлена.

Нужно формализовать:

- labels;
- placeholders;
- help text;
- required mark;
- validation error;
- backend error;
- success;
- loading;
- disabled;
- consent;
- autofill;
- mobile layout;
- field groups;
- optional qualification fields.

TO BE question: форма должна быть dense B2B form или более легкая conversational form?

### 6. CTA-Секции Требуют Intent-Based System

AS IS `lead.cta` поддерживает:

- `personal-offer`;
- `project-discussion`;
- `solid`;
- `glass`.

Но на уровне дизайна нужно определить intent variants:

- request consultation;
- calculate project;
- assemble team;
- request AI bot;
- contact routing;
- offer/detail inquiry.

TO BE question: CTA variants должны различаться только copy или layout/color/form fields тоже?

### 7. Chat UI Требует Отдельной Системы

AS IS есть hero chat и light chat. Они уже имеют отдельные DOM contracts и message scroll.

На 02.06.2026 chat selectors, typing, quick replies and lead handoff contract зафиксированы в `07-component-state-contract.json`, но это не финальная визуальная chat spec.

Нужно спроектировать:

- message bubbles;
- assistant identity;
- typing;
- errors;
- quick replies;
- final actions;
- handoff to lead form;
- long AI response;
- mobile keyboard behavior;
- loading states.

TO BE question: chat — это часть общей дизайн-системы или отдельный product pattern?

### 8. `/price/` — Самый Сложный Интерактив

`/price/` содержит configurator-like flow:

- filters;
- search;
- role cards;
- level selection;
- team presets;
- persistent summary;
- budget estimate;
- order modal;
- staff-order endpoint;
- fallback legacy selectors.

На 02.06.2026 core `/price/` selectors, legacy fallback and required state coverage зафиксированы в `07-component-state-contract.json`. Это снижает риск поломать flow при редизайне, но не заменяет dedicated mobile/team-builder UX.

TO BE question: проектировать `/price/` как каталог карточек или как полноценный team builder?

### 9. Навигация И Product Ladder

AS IS header держит простое top-level меню, а коммерческие входы находятся под `Услуги` и в footer/content.

TO BE question: нужно ли сделать `/price/`, `/offer/`, `/calculator/`, `/aiagents/` более явными в navigation?

Решение влияет на:

- header layout;
- mobile menu;
- dropdown;
- footer;
- active states;
- SEO/internal linking.

### 10. Иконки Не Имеют Taxonomy

Remix Icon используется широко, но нет правил:

- какие иконки для AI;
- какие для контактов;
- какие для документов/legal;
- какие для states;
- когда icon-only, когда icon+text.

TO BE question: оставить Remix Icon или создать curated icon set с назначениями?

### 11. Hero System Не Формализована

Есть разные hero patterns:

- dark hero with background image and overlay;
- light hero with background image and white overlay;
- simple gray hero (`/contacts/`);
- AI agents scoped hero.

TO BE question: какие hero variants нужны и какие правила по media/background/copy/CTA?

### 12. Accessibility Не Описана Как Design Requirement

В коде уже есть некоторые accessibility меры:

- aria labels;
- aria-hidden/expanded for mobile menu;
- focus trap in modal;
- consent checkbox;
- link rel noopener.

Но дизайн-система должна явно задать:

- focus ring;
- color contrast;
- keyboard states;
- mobile tap target;
- error messaging;
- modal focus behavior;
- reduced motion policy.

## TO BE Decision Checklist

Перед детальным дизайном нужно ответить:

1. Какая структура design tokens будет source of truth и как она мапится на `05-design-tokens-as-is.json`?
2. Какие компоненты входят в первую версию Figma library?
3. Какие страницы являются canonical templates?
4. Какие AS IS DOM-контракты нужно сохранить в первой миграции?
5. Какие JS-поведения можно менять только через отдельную dev-задачу?
6. Какая icon library остается?
7. Какой подход к форме: compact B2B, conversational или mixed?
8. Как выглядит unified CTA system?
9. Какой visual language для AI/chat блоков?
10. Как `/price/` должен работать на mobile?
11. Нужна ли новая navigation architecture?
12. Как дизайн-система будет документировать states?

## Рекомендуемый TO BE Deliverable

Для первой версии дизайн-системы стоит запросить у дизайнера:

- Figma variables for tokens;
- mapping from Figma variables to AS IS token contract / Tailwind / global CSS;
- component library with variants and states;
- responsive page templates;
- state matrix for forms/chat/price/FAQ/modal/menu;
- icon taxonomy;
- migration notes from AS IS selectors/components to TO BE components;
- review of `07-component-state-contract.json` with explicit preserve/migrate decisions;
- review of `08-as-is-to-be-migration-map.json` with approved migration type and gates for each AS IS component;
- examples for `/contacts/`, `/services/`, `/price/`, `/offer/`.

## Suggested Migration Map Format

| AS IS | TO BE | Migration type | Notes |
|---|---|---|---|
| `tacticum:lead.cta` | `CTASection` | component restyle | Preserve form contract |
| `.tacticum-personal-offer-form` | `Form/CardForm` | CSS refactor | Define field states |
| `tacticum:contact.modal` | `ContactModal` | component restyle | Preserve modal ids unless JS updated |
| `[data-tacticum-chat]` | `ChatSurface` | component redesign | Preserve data attributes initially |
| `.faq-item` | `AccordionItem` | component restyle | Preserve active behavior or update JS |
| `news.list/price` | `TeamBuilder` | product redesign | Requires dedicated UX/dev scope |
| `topmenu/mobilemenu` | `Navigation` | shell redesign | Requires menu template updates |
