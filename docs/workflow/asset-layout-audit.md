# Asset / Layout Audit

Дата: 21.05.2026

## Current Asset Loading

Global JS подключается в `local/templates/tacticum/header.php` через `Bitrix\Main\Page\Asset`:

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

- `tailwind.generated.css`;
- `fonts/remixicon.min.css`;
- `template_styles.css` подключается штатно как CSS активного Bitrix template;
- `styles/aiagents.css` подключается через explicit page asset flag `TACTICUM_PAGE_ASSETS = ['aiagents_css']`.

Legacy browser Tailwind artifacts `js/bundle.v3.4.16.js` и `js/init.js` удалены после source/rendered asset inventory. Static Tailwind utilities собираются из `local/templates/tacticum/assets/src/tailwind.css` командой `npm run css:build`.

## CSS Inventory

Файлы в `local/templates/tacticum/styles/`:

| File | Lines | Current status |
|---|---:|---|
| `aiagents.css` | 35 | подключён через page asset flag на `/aiagents/` |

Удалены как dead artifacts после source scan и rendered asset inventory на `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`: `main.css`, `services.css`, `price.css`, `calculator.css`, `contacts.css`, `about.css`, `expertise.css`, `css2.css`.

`template_styles.css` содержит около 2400 строк и фактически является основным CSS bundle активного шаблона. Внутри уже смешаны global styles, Tailwind-generated utilities и page-specific sections.

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

- основной personal-offer CTA для `/`, `/calculator/`, `/price/` и `/contacts/` вынесен в `local/templates/tacticum/include/personal-offer-cta.php`;
- `/contacts/` использует явный `glass` variant внутри shared personal-offer CTA;
- project-discussion CTA для `/about/` и `/services/` вынесен в `local/templates/tacticum/include/project-discussion-cta.php`;
- public pages передают только page-specific `form_id`/HTML `id`/field prefix, а не копируют form markup.

После Sprint 06 cleanup:

- добавлен минимальный Node/Tailwind toolchain (`package.json`, `package-lock.json`);
- добавлен source entrypoint `local/templates/tacticum/assets/src/tailwind.css`;
- сгенерирован и подключён `local/templates/tacticum/tailwind.generated.css`;
- `header.php` больше не подключает browser Tailwind runtime `bundle.v3.4.16.js` и `init.js`;
- `pr-check.yml` выполняет `npm run css:check` и блокирует возврат runtime Tailwind в `header.php`.
- Incident fix 21.05.2026: `tailwind.generated.css` собирается без `--minify`, чтобы сохранить `@layer theme, base, components, utilities;` перед Bitrix-склейкой с legacy `template_styles.css`.
- Legacy Tailwind JS artifacts и dead page-specific CSS artifacts удалены; `pr-check.yml` блокирует их восстановление.

После закрытия `TG-015` 22.05.2026:

- добавлен `npm run visual:smoke` (`tools/visual-smoke.mjs`) для desktop/mobile smoke публичных страниц через headless Chrome;
- добавлен режим `TACTICUM_VISUAL_INJECT_CSS` для проверки локального CSS против production/staging HTML до deploy;
- закрыты найденные horizontal overflow regressions: скрыто off-canvas меню в закрытом состоянии, ограничены step connectors на `/services/` и `/aiagents/`;
- в `template_styles.css` добавлен compatibility-блок responsive Tailwind utilities, потому что legacy bundle подключается после `tailwind.generated.css` и может перебивать responsive classes базовыми utilities.

## Risks

- Browser Tailwind/runtime bundle больше не подключается в production header; post-deploy visual smoke остаётся обязательным gate после CSS-правок.
- `styles/aiagents.css` остаётся единственным approved file в `local/templates/tacticum/styles/`; новые page CSS artifacts требуют явного asset contract.
- Optional assets больше не выбираются по URL substring; страницы объявляют их явно до `require bitrix/header.php`.
- `template_styles.css` остаётся общим местом для unrelated page rules.
- Если `tailwind.generated.css` потеряет декларацию порядка cascade layers, reset из legacy `template_styles.css` может обнулить spacing/border utilities.
- Repeated CTA markup больше не живёт копиями в public page PHP; новые варианты нужно добавлять через includes/components.

## Rules Going Forward

- Новый JS/CSS подключать через `Asset`, компонентный `script.js/style.css` или approved template asset.
- Не добавлять inline JS/CSS в публичные страницы.
- Новый form/chat behavior добавлять в `forms.js`, `chat-agent.js` или компонентный asset, не копировать в page PHP.
- Page-specific asset подключать компонентом или через explicit page asset flag, не через URL-substring.
- Presentation differences between pages pass through component params, not current URL checks.
- JS behavior must bind to explicit selectors/data attributes; do not infer behavior from button text.
- Static Tailwind source менять вместе с `tailwind.generated.css`; запускать `npm run css:build` и проверять `npm run css:check`; не включать Tailwind CLI `--minify`, пока CSS склеивается с legacy `template_styles.css`.

## Recommended Next Step

Следующий cleanup:

1. После deploy выполнить `npm run visual:smoke` против целевого URL без `TACTICUM_VISUAL_INJECT_CSS`.
2. Отдельно спланировать merge/retirement strategy для legacy `template_styles.css`.
3. Не расширять compatibility-блок responsive utilities без последующего visual smoke.
