# 02. Component Inventory

Дата: 30.05.2026

## Назначение

Этот документ фиксирует AS IS компоненты и блоки, которые дизайнеру нужно учитывать при проектировании TO BE дизайн-системы. Таблицы описывают не только внешний вид, но и states, JS-контракты и вопросы для новой системы.

## Global Shell

| Компонент | Источник | Назначение | AS IS варианты / состояния | JS / DOM contract | TO BE вопросы |
|---|---|---|---|---|---|
| Header | `local/templates/tacticum/header.php` | Глобальная шапка сайта | Fixed header, logo, desktop menu, CTA, mobile toggle | `[data-tacticum-menu-toggle]`, `#contactUsBtn` | Нужны ли sticky states, compact state, transparent hero state |
| Desktop top menu | `components/bitrix/menu/topmenu` | Desktop navigation | Top-level + dropdown children | `.nav-link`, active class, hover dropdown | Как показывать коммерческие входы: top-level или nested |
| Mobile menu | `components/bitrix/menu/mobilemenu` | Mobile full-screen navigation | Closed/open overlay, nested links, CTA | `#tacticum-mobile-menu`, `.translate-x-full`, `.tacticum-contact-btn` | Нужен ли drawer вместо full-screen overlay |
| Footer | `local/templates/tacticum/footer.php` | Нижняя навигация, контакты, legal | Dense footer, contact/legal content | Telegram link uses `data-tacticum-tg-resolve` | Разделить ли footer на navigation/contact/legal zones визуально |
| Bottom menu | `components/bitrix/menu/bottommenu` | Footer navigation | Group headings represented by empty links | no JS | Нужна ли нормальная grouped menu model в дизайне |
| Contact modal | `local/components/tacticum/contact.modal` | Глобальная форма связи | Hidden/open, scrollable modal, focus trap | `#tacticum-modal`, `#tacticum-modal-form`, `#tacticum-modal-close` | Нужны modal sizes, mobile sheet, success state inside modal |

## Core Local Components

| Компонент | Источник | Назначение | AS IS варианты / состояния | JS / DOM contract | TO BE вопросы |
|---|---|---|---|---|---|
| Lead CTA | `local/components/tacticum/lead.cta` | Повторяемая CTA-секция с формой | `personal-offer`, `project-discussion`; visual `solid`, `glass`; optional qualification fields | `[data-tacticum-form]`, `data-form-id`, `[data-tacticum-consent]`, optional `data-endpoint` | Нужны ли отдельные CTA patterns: compact, full-width, split image, dark, light |
| Contact modal form | `local/components/tacticum/contact.modal` | Modal lead form | Required fields, consent, loading, error hints | same form contract + `data-tacticum-close-target` | Утвердить единый form field anatomy |
| Chat surface | `local/components/tacticum/chat.surface` | UI оболочка AI-чата | `hero`, `light`; quick replies; message scroll | `[data-tacticum-chat]`, `[data-chat-input]`, `[data-chat-send]`, `[data-chat-messages]` | Нужна спецификация chat bubble, typing, error, handoff CTA |
| FAQ section | `local/components/tacticum/faq.section` | FAQ wrapper над инфоблоком | Accordion collapsed/open | `.faq-item`, `.faq-question`, `.faq-answer`, `.faq-icon`, `.active` | Нужны states для long content, multiple open vs single open |
| Content list | `local/components/tacticum/content.list` | Унификация `bitrix:news.list` для списков | Depends on selected template | depends on child template | Нужны canonical list/card variants |
| Content detail | `local/components/tacticum/content.detail` | Унификация `bitrix:news.detail` | Static detail content | no generic JS | Нужен article/legal/detail template |
| Offer section | `local/components/tacticum/offer` | Router для `/offer/` list/detail/not_found | list, detail, not_found | filter links/forms in templates | Нужна система catalog/detail cards |
| Offer catalog | `local/components/tacticum/offer.catalog` | Каталог offer examples | Filters, pagination, stats | server-rendered filters/pagination | Нужны filter chips, empty state, pagination spec |
| AI Agents page | `local/components/tacticum/aiagents` | Section-level `/aiagents/` | Scoped body class `tacticum-aiagents-page` | no global special JS except FAQ | Решить, должен ли page-specific visual language стать общим |

## Bitrix Component Templates

| Template | Источник | Назначение | AS IS состояния | TO BE вопросы |
|---|---|---|---|---|
| `news.list/services` | `local/templates/tacticum/components/bitrix/news.list/services` | Список услуг | Card hover, icons/content | Единый service card anatomy |
| `news.list/price` | `local/templates/tacticum/components/bitrix/news.list/price` | Роли/ставки/заказ специалистов | Filters, search, level selection, team summary, modal, empty state | Самый важный interactive component для отдельной проработки |
| `news.list/cases` | `local/templates/tacticum/components/bitrix/news.list/cases` | Кейсы | Cards, categories, preview text | Нужна case card/detail модель |
| `news.list/feedback` | `local/templates/tacticum/components/bitrix/news.list/feedback` | Отзывы | Cards | Нужна proof/testimonial система |
| `news.list/team` | `local/templates/tacticum/components/bitrix/news.list/team` | Команда | Member cards, overlay hover | Нужны states для member card |
| `news.list/vacancies` | `local/templates/tacticum/components/bitrix/news.list/vacancies` | Вакансии | List/card | Нужен careers/list pattern |
| `news.list/faq` | `local/templates/tacticum/components/bitrix/news.list/faq` | FAQ item rendering | Accordion | Совместить с FAQ design spec |
| `news.list/aiagents` | `local/templates/tacticum/components/bitrix/news.list/aiagents` | AI agents cards | Cards, content from iblock | Нужен agent/scenario card pattern |
| `news.detail/offer` | `local/templates/tacticum/components/bitrix/news.detail/offer` | Offer detail | Detail layout, CTA, FAQ | Нужен offer detail page template |
| `news.detail/policies` | `local/templates/tacticum/components/bitrix/news.detail/policies` | Legal content | Text content, detail block | Нужен legal typography spec |

## Forms

AS IS формы имеют общий behavioral contract.

### Общие поля

| Поле | Required | Где используется | Комментарий TO BE |
|---|---:|---|---|
| `name` | yes | Lead CTA, modal, offer forms | Нужно определить label, placeholder, error |
| `email` | yes | Lead CTA, modal, offer forms | Сейчас validation простая: наличие `@` |
| `phone` | yes | Lead CTA, modal, offer forms | Нужна маска или нет — TO BE decision |
| `company` | no | Lead CTA, modal | Optional field |
| `message` | yes | Lead CTA, modal | Основное поле задачи |
| `lead_budget` | no | Some lead CTA | Qualification field |
| `lead_timeline` | no | Some lead CTA | Qualification field |
| consent checkbox | yes | All lead forms | Legal-critical state |

### Состояния, Которые Нужно Спроектировать

- default;
- hover;
- focus;
- filled;
- validation error;
- backend error;
- disabled;
- loading submit;
- success;
- consent unchecked error;
- autofill;
- mobile single-column.

## Cards

AS IS карточки не имеют единого base component. Повторяются паттерны:

- white background;
- `rounded-lg` / `rounded-xl`;
- `shadow-sm` / `shadow-md`;
- hover `translateY`;
- icon block with `bg-primary/10`;
- title + text + CTA/link.

Карточки, которые нужно нормализовать в TO BE:

- contact card;
- service card;
- case card;
- price/specialist card;
- offer card;
- AI agent card;
- testimonial card;
- team member card;
- legal/info card.

## Buttons And Links

AS IS button language:

- primary: blue background `#0066CC`, white text;
- secondary/dark: often white text on dark gradient;
- ghost/link: blue text with hover;
- rounded button radius: `8px`;
- some icon buttons use Remix Icon.

TO BE needs:

- button hierarchy: primary, secondary, tertiary, ghost, danger;
- sizes: sm, md, lg, icon-only;
- loading and disabled;
- icon placement;
- accessibility/focus ring;
- line wrapping rules for long Russian labels.

## Icons

AS IS icon set: Remix Icon via `local/templates/tacticum/fonts/remixicon.min.css`.

Examples:

- `ri-phone-line`;
- `ri-mail-line`;
- `ri-map-pin-line`;
- `ri-send-plane-fill`;
- `ri-close-line`;
- `ri-telegram-fill`.

TO BE decision:

- keep Remix Icon;
- migrate to another icon set;
- create a limited icon taxonomy for product/business scenarios.

## Page-Specific Visual Blocks

| Block | Источник | Current styling | TO BE need |
|---|---|---|---|
| Hero backgrounds | `global.css`, page classes | Image + overlay gradients | Define hero system: dark/light/media/text alignment |
| FAQ | `global.css` + `faq.js` | Max-height accordion | Define motion, spacing, icon |
| Chat | `chat.surface`, `chat-agent.js`, `global.css` | Window-like chrome, bubbles, scroll | Define conversational UI spec |
| Price builder | `news.list/price` + `script.js` | Cards + filters + modal + summary | Dedicated complex flow spec |
| Map/contact | `contacts/index.php` | iframe card + address/hours | Contact page pattern |

