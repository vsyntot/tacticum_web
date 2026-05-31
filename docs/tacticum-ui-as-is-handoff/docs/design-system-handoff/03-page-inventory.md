# 03. Page Inventory

Дата: 30.05.2026

## Назначение

Этот документ описывает публичные страницы как UX-поверхности: роль в воронке, ключевой CTA, подключаемые assets, текущие компоненты и вопросы для TO BE.

## Page Map

| URL | Роль в воронке | Основной CTA | Page assets | Основные компоненты / блоки | TO BE фокус |
|---|---|---|---|---|---|
| `/` | Главный routing hub | Рассчитать проект / обсудить задачу | `faq,chat` | Hero, hero chat, services, cases, FAQ, CTA | Главный narrative и routing к коммерческим входам |
| `/services/` | Внедрение AI-решений | Обсудить проект | `faq` | Hero, service cards, steps, FAQ, CTA | Service page template, proof, process |
| `/price/` | Собрать команду | Заказать специалистов | `faq,charts,chat` | Price cards, filters, level selector, team presets, staff modal, light chat, CTA | Complex configurator UX |
| `/calculator/` | Предварительная оценка проекта | Отправить вводные | `faq,chat` | Calculator hero, light chat, FAQ, CTA | Chat-to-lead flow |
| `/offer/` | Примеры расчетов | Смотреть пример / оставить заявку | `faq` via helper | Catalog, filters, detail pages, FAQ | Catalog/detail system |
| `/aiagents/` | AI-боты и Telegram-агенты | Заявка на AI-бота | `faq` | Section-level component, scoped body class | Agent/scenario card system |
| `/contacts/` | Прямой контактный вход | Отправить обращение | none page-specific | Contact cards, lead CTA, legal details, map | Contact/legal page template |
| `/about/` | Доверие и компания | Обсудить проект | none page-specific | About hero, team, vacancies, partners, CTA | Company/proof page template |
| `/policies/` | Legal | none | none page-specific | Content detail | Legal text typography |

## `/contacts/` As Reference Page

Файл: `contacts/index.php`.

### Почему Эта Страница Важна Для Handoff

`/contacts/` показывает типичный AS IS подход:

- данные страницы задаются в PHP в начале файла;
- SEO defaults применяются до визуального пролога;
- большая часть разметки — Tailwind utility-классы;
- повторяемая CTA-форма подключается компонентом `tacticum:lead.cta`;
- карта не требует отдельного JS, работает через iframe;
- глобальная модалка и footer приходят из template shell.

### Текущая Структура

| Секция | Описание | Styling approach | Интерактив |
|---|---|---|---|
| Hero | `Контакты` + supporting copy | Utility classes | none |
| Contact cards | Phone, email, office | Utility classes + Remix icons | tel/mailto/hash link |
| Lead CTA | Form for routing request | `tacticum:lead.cta` + `global.css` | `forms.js` |
| Legal details | Company requisites and IT activity | Utility classes | none |
| Map | Yandex map iframe + address/hours | Utility classes | iframe native |

### Текущие Контакты

- Phone: `+7 (495) 561-20-84`;
- Email: `project@tacticum.ru`;
- Place: `Тактикум`, `БЦ Victory Park`;
- Legal address: `119285, г. Москва, Вн.Тер.г. Муниципальный округ Раменки, Км Мжд Киевское 5-й, д. 1 стр. 1, помещ. 3/3`;
- Map: Yandex object id `243968538014`.

### TO BE Вопросы Для `/contacts/`

- Должны ли контакты быть одной плотной operational page или более маркетинговой trust page?
- Нужно ли разделить публичный ориентир, юридический адрес и маршрут более явно?
- Какой формат CTA лучше: форма сразу на странице, compact card, modal-first или split section?
- Нужна ли fallback-зона, если iframe карты не загрузился?
- Нужны ли дополнительные каналы связи: Telegram, календарь, адрес для документов, support?
- Как показывать реквизиты: card, table, downloadable block?

## Commercial Entry Pages

### `/services/`

Текущая роль: объяснить AI-внедрение и направить в project discussion.

TO BE needs:

- service card anatomy;
- process/timeline component;
- proof block;
- FAQ placement;
- CTA variant for service intent.

### `/price/`

Текущая роль: дать пользователю собрать команду и отправить staff-order заявку.

Это самый сложный UI flow.

AS IS interaction:

- фильтры ролей;
- поиск;
- выбор уровня специалиста;
- team presets;
- persistent summary;
- monthly budget estimate;
- modal staff order;
- light chat handoff.

TO BE needs:

- configurator pattern;
- selected item state;
- summary panel;
- empty search state;
- mobile flow;
- budget estimate presentation;
- modal vs inline checkout decision.

### `/calculator/`

Текущая роль: AI-калькулятор/чат как вход к оценке проекта.

TO BE needs:

- conversational form pattern;
- AI answer readability;
- handoff to lead CTA;
- trust/proof near AI output;
- fallback state when AI endpoint fails.

### `/offer/`

Текущая роль: примеры расчетов и offer detail pages.

TO BE needs:

- catalog filter system;
- offer card;
- detail page template;
- metrics/stat cards;
- 404/not found design;
- pagination.

### `/aiagents/`

Текущая роль: отдельный продуктовый вход для AI-ботов.

AS IS имеет scoped body class `tacticum-aiagents-page`, то есть визуально частично выделен из общей системы.

TO BE needs:

- решить, становится ли AI agents visual language частью общей системы;
- agent card;
- scenario card;
- integration/process blocks.

## Company And Legal Pages

### `/about/`

Текущая роль: доверие, команда, карьера, партнеры.

TO BE needs:

- company hero;
- timeline/process;
- team card;
- value/proof cards;
- vacancy card;
- partner/client block.

### `/policies/`

Текущая роль: legal content.

TO BE needs:

- readable legal typography;
- table/list styling;
- anchor navigation if content grows;
- print/download decision if needed.

## Cross-Page Patterns

| Pattern | Где встречается | TO BE spec |
|---|---|---|
| Section container | Almost every page | max-width, gutters, vertical rhythm |
| Hero | home/services/about/calculator/price/aiagents | variants: dark image, light image, simple text |
| CTA section | home/about/services/contacts/price/calculator | intent variants and form variants |
| Cards grid | services/cases/contacts/price/offer/aiagents | card anatomy and responsive behavior |
| FAQ | home/services/calculator/price/offer/aiagents | accordion states |
| Form | CTA/modal/offer/staff order | field anatomy and validation states |
| Chat | home/calculator/price | chat UI spec |
| Catalog filters | offer/price | filter chips/buttons/select/search |

