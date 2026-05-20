# Asset / Layout Audit

Дата: 21.05.2026

## Current Asset Loading

Global JS подключается в `local/templates/tacticum/header.php` через `Bitrix\Main\Page\Asset`:

- `js/bundle.v3.4.16.js`;
- `js/init.js`;
- `js/menu.js`;
- `js/analytics.js`;
- `js/forms.js`;
- `js/chat-agent.js`;
- `js/modal.js`;
- `js/scroll.js`;
- `js/tg-link-resolver.js`.

Conditional JS:

- `js/faq.js` по URL-substring для главной, services, aiagents, price, offer, calculator;
- `js/charts.js` для `price` через `addString`.

Global CSS:

- `fonts/remixicon.min.css`;
- `template_styles.css` подключается штатно как CSS активного Bitrix template;
- `styles/aiagents.css` подключается только для `aiagents`.

## CSS Inventory

Крупные файлы в `local/templates/tacticum/styles/`:

| File | Lines | Current status |
|---|---:|---|
| `main.css` | 1155 | не подключён явно в `header.php` |
| `services.css` | 901 | не подключён явно |
| `price.css` | 1009 | не подключён явно |
| `calculator.css` | 1086 | не подключён явно |
| `contacts.css` | 1053 | не подключён явно |
| `about.css` | 946 | не подключён явно |
| `expertise.css` | 950 | не подключён явно |
| `css2.css` | 45 | не подключён явно |
| `aiagents.css` | 28 | подключён conditionally |

`template_styles.css` содержит 2185 строк и фактически является основным CSS bundle активного шаблона. Внутри уже смешаны global styles, Tailwind-generated utilities и page-specific sections.

## Inline Assets

После Sprint 02 inline chat code удалён из:

- `index.php`;
- `calculator/index.php`;
- `price/index.php`.

Оставшиеся известные inline assets:

- Yandex.Metrika в `header.php` — допустимое vendor exception;
- policy detail inline style в `local/templates/tacticum/components/bitrix/news.detail/policies/template.php`;
- JSON data islands в price component — допустимо как `application/json`;
- generated font demo HTML в `local/templates/tacticum/fonts/` — не production page flow.

## Risks

- Browser Tailwind/runtime bundle остаётся production dependency; часть классов продолжает зависеть от JS.
- Page-specific CSS files выглядят как generated/stale artifacts, но удалять их без visual regression нельзя.
- Header использует URL-substring routing для optional assets; это хрупко при новых URL.
- `template_styles.css` остаётся общим местом для unrelated page rules.

## Rules Going Forward

- Новый JS/CSS подключать через `Asset`, компонентный `script.js/style.css` или approved template asset.
- Не добавлять inline JS/CSS в публичные страницы.
- Новый form/chat behavior добавлять в `forms.js`, `chat-agent.js` или компонентный asset, не копировать в page PHP.
- Page-specific asset подключать компонентом или через явное page property, не через новый URL-substring.
- Runtime Tailwind migration планировать отдельно: static build first, visual regression second, cleanup third.

## Recommended Next Step

Sprint 03:

1. Заменить URL-substring asset routing на component/page-property routing.
2. Подготовить static CSS build plan для отказа от browser Tailwind runtime.
3. После visual smoke пометить stale `styles/*.css` как used/dead и удалить только подтверждённые dead files.
