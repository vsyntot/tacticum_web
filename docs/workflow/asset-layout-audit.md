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

- `js/faq.js` подключается через explicit page asset flag `TACTICUM_PAGE_ASSETS = ['faq']`;
- `js/charts.js` подключается через explicit page asset flag `TACTICUM_PAGE_ASSETS = ['charts']`.

Global CSS:

- `fonts/remixicon.min.css`;
- `template_styles.css` подключается штатно как CSS активного Bitrix template;
- `styles/aiagents.css` подключается через explicit page asset flag `TACTICUM_PAGE_ASSETS = ['aiagents_css']`.

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
| `aiagents.css` | 28 | подключён через page asset flag на `/aiagents/` |

`template_styles.css` содержит 2185 строк и фактически является основным CSS bundle активного шаблона. Внутри уже смешаны global styles, Tailwind-generated utilities и page-specific sections.

## Inline Assets

После Sprint 02 inline chat code удалён из:

- `index.php`;
- `calculator/index.php`;
- `price/index.php`.

После Sprint 03 cleanup удалён legacy `local/templates/tacticum/js/chat.js`; production chat обслуживает только `chat-agent.js`.

Оставшиеся известные inline assets:

- Yandex.Metrika в `header.php` — допустимое vendor exception;
- Yandex Maps constructor script в `contacts/index.php` — vendor embed exception;
- JSON data islands в price component — допустимо как `application/json`;
- generated font demo HTML в `local/templates/tacticum/fonts/` — не production page flow.

После Sprint 04 cleanup:

- FAQ background больше не определяется через current URL; используется component param `SECTION_CLASS`;
- policy detail styles перенесены в `local/templates/tacticum/components/bitrix/news.detail/policies/style.css`;
- price specialist modal markup перенесён из JS в `news.list/price/template.php`;
- `modal.js` и `scroll.js` очищены от path/text selector heuristics.
- form/price UI state переведён с inline style mutations на CSS selectors/classes.

После Sprint 05 cleanup:

- основной personal-offer CTA для `/`, `/calculator/` и `/price/` вынесен в `local/templates/tacticum/include/personal-offer-cta.php`;
- project-discussion CTA для `/about/` и `/services/` вынесен в `local/templates/tacticum/include/project-discussion-cta.php`;
- public pages передают только page-specific `form_id`/HTML `id`/field prefix, а не копируют form markup.

## Risks

- Browser Tailwind/runtime bundle остаётся production dependency; часть классов продолжает зависеть от JS.
- Page-specific CSS files выглядят как generated/stale artifacts, но удалять их без visual regression нельзя.
- Optional assets больше не выбираются по URL substring; страницы объявляют их явно до `require bitrix/header.php`.
- `template_styles.css` остаётся общим местом для unrelated page rules.
- Contacts CTA variant ещё требует отдельного visual review перед унификацией с одним из shared includes или выделением третьего варианта.

## Rules Going Forward

- Новый JS/CSS подключать через `Asset`, компонентный `script.js/style.css` или approved template asset.
- Не добавлять inline JS/CSS в публичные страницы.
- Новый form/chat behavior добавлять в `forms.js`, `chat-agent.js` или компонентный asset, не копировать в page PHP.
- Page-specific asset подключать компонентом или через explicit page asset flag, не через URL-substring.
- Presentation differences between pages pass through component params, not current URL checks.
- JS behavior must bind to explicit selectors/data attributes; do not infer behavior from button text.
- Runtime Tailwind migration планировать отдельно: static build first, visual regression second, cleanup third.

## Recommended Next Step

Следующий cleanup:

1. Провести visual review CTA variant на contacts и решить: общий include с variant config или отдельный component/include.
2. Реализовать `docs/workflow/static-css-build-plan.md`.
3. После visual smoke пометить stale `styles/*.css` как used/dead и удалить только подтверждённые dead files.
