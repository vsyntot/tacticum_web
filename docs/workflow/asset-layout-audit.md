# Asset / Layout Audit

Дата: 25.05.2026

## Current Asset Loading

Global JS подключается в `local/templates/tacticum/header.php` через `Bitrix\Main\Page\Asset`:

- `js/menu.js`;
- `js/analytics.js`;
- `js/metrika.js`;
- `js/forms.js`;
- `js/chat-agent.js`;
- `js/modal.js`;
- `js/scroll.js`;
- `js/tg-link-resolver.js`.

Conditional JS:

- `js/faq.js` подключается через page property `tacticum_page_assets=faq`;
- `js/charts.js` подключается через page property `tacticum_page_assets=charts`;
- `js/yandex-map.js` остаётся conditional asset через page property `tacticum_page_assets=yandex_map`, но текущая `/contacts/` карта использует iframe без этого JS.

Global CSS:

- `tailwind.generated.css`;
- `fonts/remixicon.min.css`;
- fixed template CSS split подключается явно через `Asset`: `styles/global.css`, `styles/components.css`, `styles/page-about-calculator.css`, `styles/page-offer-price-services.css`, `styles/page-aiagents.css`;
- `template_styles.css` оставлен пустым/comment-only Bitrix compatibility shim.

Legacy browser Tailwind artifacts `js/bundle.v3.4.16.js` и `js/init.js` удалены после source/rendered asset inventory. Static Tailwind utilities собираются из `local/templates/tacticum/assets/src/tailwind.css` командой `npm run css:build`.

## CSS Inventory

Файлы в `local/templates/tacticum/styles/`:

| File | Lines | Current status |
|---|---:|---|
| `global.css` | 279 | базовые migrated/global rules; подключён через `header.php` |
| `components.css` | 294 | shared form/modal/FAQ component rules; подключён через `header.php` |
| `page-about-calculator.css` | 289 | about/calculator/chat page rules; подключён через `header.php` |
| `page-offer-price-services.css` | 367 | contacts/offer/price/services page rules; подключён через `header.php` |
| `page-aiagents.css` | 50 | scoped `/aiagents/` page rules; подключён через `header.php` |

Удалены как dead artifacts после source scan и rendered asset inventory на `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`: `main.css`, `services.css`, `price.css`, `calculator.css`, `contacts.css`, `about.css`, `expertise.css`, `css2.css`.

`template_styles.css` больше не содержит активных правил: файл оставлен только как compatibility shim для Bitrix template asset pipeline. Старый custom/global bundle split into fixed files under `styles/`, а generated utilities должны жить только в `tailwind.generated.css`. Мини-блок `/aiagents/` больше не держится legacy `styles/aiagents.css`: правила живут в `styles/page-aiagents.css` и изолированы body class `tacticum-aiagents-page`.

Legacy Remixicon fallback `:where([class^="ri-"])::before` удалён из template CSS: иконки должны использовать реальные классы из локального `fonts/remixicon.css`. `npm run template-styles:check` валидирует используемые `ri-*` классы, fixed CSS split and blocks generic fallback.

## Inline Assets

После Sprint 02 inline chat code удалён из:

- `index.php`;
- `calculator/index.php`;
- `price/index.php`.

После Sprint 03 cleanup удалён legacy `local/templates/tacticum/js/chat.js`; production chat обслуживает только `chat-agent.js`.

Оставшиеся известные inline assets:

- `/contacts/` использует Yandex map widget iframe с координатами московского офиса; legacy constructor script не нужен для текущей страницы;
- Yandex.Metrika подключается через centralized template asset `js/metrika.js`; noscript pixel использует CSS class вместо inline `style=`;
- JSON data islands в price component — допустимо как `application/json`.

После Sprint 04 cleanup:

- FAQ background больше не определяется через current URL; используется component param `SECTION_CLASS`;
- policy detail styles перенесены в `local/templates/tacticum/components/bitrix/news.detail/policies/style.css`;
- price specialist modal markup перенесён из JS в `news.list/price/template.php`;
- price filters/cards/modal state и light chat quick replies переведены на явные `data-*` contracts;
- `modal.js` и `scroll.js` очищены от path/text selector heuristics.
- form/price UI state переведён с inline style mutations на CSS selectors/classes.

После Sprint 05 cleanup:

- основной personal-offer CTA для `/`, `/calculator/`, `/price/` и `/contacts/` затем переведён на локальный компонент `tacticum:lead.cta`;
- `/contacts/` использует явный `glass` variant внутри shared CTA component;
- project-discussion CTA для `/about/` и `/services/` затем переведён на локальный компонент `tacticum:lead.cta`;
- public pages передают только page-specific `form_id`/HTML `id`/field prefix, а не копируют form markup.

После Sprint 06 cleanup:

- добавлен минимальный Node/Tailwind toolchain (`package.json`, `package-lock.json`);
- добавлен source entrypoint `local/templates/tacticum/assets/src/tailwind.css`;
- сгенерирован и подключён `local/templates/tacticum/tailwind.generated.css`;
- `header.php` больше не подключает browser Tailwind runtime `bundle.v3.4.16.js` и `init.js`;
- `pr-check.yml` выполняет `npm run css:check` и блокирует возврат runtime Tailwind в `header.php`.
- Incident fix 21.05.2026: `tailwind.generated.css` собирается без `--minify`, чтобы сохранить `@layer theme, base, components, utilities;` перед склейкой с migrated global CSS.
- Legacy Tailwind JS artifacts и dead page-specific CSS artifacts удалены; `pr-check.yml` блокирует их восстановление.

После закрытия `TG-015` 22.05.2026:

- добавлен `npm run visual:smoke` (`tools/visual-smoke.mjs`) для desktop/mobile smoke публичных страниц через headless Chrome;
- добавлен режим `TACTICUM_VISUAL_INJECT_CSS` для проверки локального CSS против production/staging HTML до deploy;
- добавлен `npm run browser:smoke` (`TACTICUM_VISUAL_ACTIONS=1`) для non-network UI action smoke;
- закрыты найденные horizontal overflow regressions: скрыто off-canvas меню в закрытом состоянии, ограничены step connectors на `/services/` и `/aiagents/`;
- в migrated global CSS добавлен compatibility-блок responsive Tailwind utilities, потому что global rules подключаются после `tailwind.generated.css` и могут перебивать responsive classes базовыми utilities.

После Sprint 08 gap-closure:

- старый generated Tailwind block удалён из `template_styles.css`;
- `npm run visual:smoke:css-local` / `npm run browser:smoke:css-local` могут заменить production CSS links локальными файлами для честной проверки CSS retirement batch;
- PR checks блокируют возврат generated Tailwind layer block в `template_styles.css`;
- `header.php` отправляет transitional `Content-Security-Policy-Report-Only` header для vendor/CSP readiness.

После gap №8:

- активные правила из `template_styles.css` перенесены в `styles/global.css` и подключаются через `Asset`;
- относительные `url(...)` в migrated CSS поправлены под директорию `styles/`;
- `template_styles.css` стал пустым/comment-only shim;
- `npm run template-styles:check` и PR/deploy guards блокируют возврат активных CSS-правил в `template_styles.css`.

После CSS consolidation 24.05.2026:

- `styles/aiagents.css` удалён как слишком мелкий отдельный runtime asset;
- `/aiagents/` объявляет только JS asset `faq` и body class `tacticum-aiagents-page`;
- page-specific rules `/aiagents/` живут в scoped-блоке `styles/global.css`;
- generic Remixicon fallback удалён, битые `ri-*` классы заменены на классы из локального icon font и покрыты `template-styles:check`.

После BPC CSS split 05.06.2026 `local/templates/tacticum/styles/` допускает только fixed CSS split: `global.css`, `components.css`, `page-about-calculator.css`, `page-offer-price-services.css`, `page-aiagents.css`.

После Sprint 12 hardening:

- `chat-agent.js` подключается только на страницах с `tacticum:chat.surface` через explicit page asset `chat`;
- `index.php`, `calculator/index.php`, `price/index.php` объявляют `tacticum_page_assets=...chat`;
- неиспользуемые Google Fonts preconnect/CSP origins и Readdy image origin удалены из `header.php`;
- remote Readdy background в offer detail заменён локальным CSS-слоем, чтобы не добавлять third-party image request;
- non-hero images получили `loading="lazy"` / `decoding="async"`, а статические `about.jpg` и `ai.jpg` - явные dimensions.

После template asset hygiene 25.05.2026:

- пустой `local/templates/tacticum/include/` удалён, Tailwind source list больше не сканирует `../../include/**/*.php`;
- неиспользуемые `Pacifico` `@font-face` и `fonts/pacifico/` удалены;
- из публичного `fonts/` удалены архив/исходники RemixIcon (`zip`, `glyph.json`, `less`, `scss`, `styl`, `symbol.svg`); runtime-набор оставлен консервативно: `remixicon.min.css`, `remixicon.css` для guard и font binaries, на которые ссылается vendor CSS;
- из `images/` удалены неиспользуемые старые/дублирующие картинки: `aibot_hero_bg.jpg`, `aibot_hero_bg_2.png`, `aibot_hero_bg_old.jpg`, `background.jpg`, `finance.jpg`, `logistics.jpg`, `retail.jpg`, `tm1.jpg`, `tm2.jpg`, `tm3.jpg`;
- favicon/apple/android PNG приведены к точным размерам, заявленным в `header.php` и `site.webmanifest`;
- `npm run template-styles:check` блокирует возврат удалённых template asset artifacts и проверяет реальные PNG-размеры favicon/apple/android icons.

## Risks

- Browser Tailwind/runtime bundle больше не подключается в production header; post-deploy visual smoke остаётся обязательным gate после CSS-правок.
- `local/templates/tacticum/styles/` допускает только fixed CSS split; новые page CSS artifacts на уровне шаблона запрещены без отдельного asset contract и audit update.
- Optional assets больше не выбираются по URL substring; страницы объявляют их явно до `require bitrix/header.php`.
- `template_styles.css` не должен снова принимать активные CSS-правила или imports.
- template CSS split не должен содержать generic Remixicon fallback; новые `ri-*` классы должны существовать в `fonts/remixicon.css`.
- `local/templates/tacticum/include/`, неиспользуемые font-source файлы и dead images не должны возвращаться в публичный шаблон; это покрывает `npm run template-styles:check`.
- Если `tailwind.generated.css` потеряет декларацию порядка cascade layers, reset из fixed template CSS split может обнулить spacing/border utilities.
- Repeated CTA markup больше не живёт копиями в public page PHP; новые варианты нужно добавлять через includes/components.
- Metrika больше не требует inline-script exception; report-only CSP уже разрешает `https://mc.yandex.ru`, но enforcing CSP требует отдельного hardening шага.
- Google Fonts/Readdy origins не используются в runtime assets; возврат этих third-party origins требует отдельного asset/CSP review.
- Enforcing CSP нельзя включать тем же PR, где меняются vendor/assets: сначала нужен чистый report-only baseline, triage лишних источников, затем отдельный deploy с rollback на `Content-Security-Policy-Report-Only`.

## Rules Going Forward

- Новый JS/CSS подключать через `Asset`, компонентный `script.js/style.css` или approved template asset.
- Не добавлять inline JS/CSS в публичные страницы.
- Новый form/chat behavior добавлять в `forms.js`, `chat-agent.js` или компонентный asset, не копировать в page PHP.
- Page-specific asset подключать компонентом или через explicit page asset flag, не через URL-substring.
- Presentation differences between pages pass through component params, not current URL checks.
- JS behavior must bind to explicit selectors/data attributes; do not infer behavior from button text.
- Static Tailwind source менять вместе с `tailwind.generated.css`; запускать `npm run css:build` и проверять `npm run css:check`; не включать Tailwind CLI `--minify`, пока CSS склеивается с migrated global CSS.
- Не возвращать активные правила в `template_styles.css`; проверять `npm run template-styles:check`.
- Новые изображения/шрифты в `local/templates/tacticum/images|fonts` добавлять только при фактической ссылке из PHP/CSS/manifest/helper и после source/rendered asset inventory.

## Recommended Next Step

Следующий cleanup:

1. Deploy workflow уже выполняет `npm run visual:smoke` и `npm run browser:smoke` против production URL без `TACTICUM_VISUAL_INJECT_CSS`; при локальной выкладке запускать те же команды вручную.
2. Следовать `docs/workflow/template-styles-retirement-plan.md`: дальнейший cleanup переносит конкретные блоки из `styles/global.css` в component/page assets малыми партиями после `visual:smoke:css-local`.
3. Для CSS retirement использовать `npm run visual:smoke:css-local` перед PR и не расширять compatibility-блок responsive utilities без последующего visual smoke.
4. Перед CSP enforcing собрать report-only evidence: нет first-party inline violations, Metrika работает, goals проверены после deploy, `/contacts/` Yandex iframe smoke чистый, visual/browser smoke зелёные.
