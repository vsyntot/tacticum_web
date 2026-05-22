# Local / Public Browser Error Challenge

Дата: 22.05.2026

## Scope

Проверены:

- организация `/local/api`, `/local/rest`, `/local/php_interface`;
- активный шаблон `local/templates/tacticum`;
- публичные страницы `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`;
- браузерные runtime errors через `npm run visual:smoke`.

## Browser Error Baseline

После расширения `tools/visual-smoke.mjs` smoke ловит:

- `Runtime.exceptionThrown`;
- `console.error` / `console.assert`;
- Chrome `Log.entryAdded` level `error`;
- first-party/third-party network responses `>=400`;
- failed resource loads;
- broken images;
- horizontal overflow.

Pre-deploy smoke с локально внедрёнными CSS:

```bash
TACTICUM_VISUAL_INJECT_CSS=local/templates/tacticum/tailwind.generated.css,local/templates/tacticum/template_styles.css,local/templates/tacticum/styles/aiagents.css npm run visual:smoke
```

Manifest: `/private/tmp/tacticum-browser-errors-after-tool-fix/manifest.json`.

Результат: layout/image false positives от CSS injection устранены. Оставшиеся runtime issues на production HTML вызваны только текущим deployed `tg-link-resolver.js`, который автоматически вызывает `/local/rest/resolve_telegram_link.php` при загрузке страниц и получает `403` / `429`.

## Fixes Applied

- `tools/visual-smoke.mjs`:
  - добавлены browser runtime checks;
  - CSS injection теперь переписывает относительные `url(...)` в абсолютные URL относительно исходного CSS-файла;
  - load ожидание стало менее хрупким: DOMContentLoaded допустим, если `load` зависает на внешнем виджете.
- `tg-link-resolver.js`:
  - resolver больше не делает фоновые запросы при загрузке страницы;
  - обработка включается только для ссылок с `data-tacticum-tg-resolve`;
  - resolver не вызывается без доступного `BX.bitrix_sessid()`;
  - ожидаемые resolver failures не пишутся в `console.warn`.
- Telegram links в footer и `/aiagents/` получили явный `data-tacticum-tg-resolve`.
- `/local/rest`:
  - добавлены shared `tacticum_rest_require_method(...)` и `tacticum_rest_read_json_body(...)`;
  - POST endpoints приведены к порядку `validate_origin -> rate_limit -> method -> parse JSON -> CSRF`;
  - `tacticum_rest_mask_pii(...)` стал консервативнее и больше не логирует имя, компанию, free-text message/task/user_message/summary целиком;
  - generic `sale_request` / `sale_response` tags заменены на endpoint-specific tags в `tacticum_offer.php` и `tacticum_sale.php`.
- Agent docs обновлены под текущий static Tailwind workflow и новый REST bootstrap.
- Frontend data contracts:
  - light chat surfaces на `/calculator/` и `/price/` получили явные `data-tacticum-chat`, `data-chat-input`, `data-chat-send`, `data-chat-messages`, `data-chat-quick-reply`, `data-message`;
  - `/price/` component filters/cards/modal state переведены на `data-price-*` contracts;
  - `chat-agent.js` и `news.list/price/script.js` больше не зависят от quick reply text, `.mt-3 button`, `.filter-tab.bg-primary`, `h3.section-title + .grid` и rendered `innerText` цены.
- Browser action smoke:
  - добавлен `npm run browser:smoke`, который запускает `visual-smoke` с `TACTICUM_VISUAL_ACTIONS=1`;
  - action mode выполняет non-network клики по меню, contact modal, пустой валидации форм, empty-send чатов, controls `/price/` и specialist modal;
  - реальные upstream success-flow остаются в ручном/staging smoke, чтобы browser gate не создавал лиды и не зависел от AI SLA.
- Inline/vendor assets:
  - Yandex Maps constructor на `/contacts/` вынесен из public page inline script в explicit asset `js/yandex-map.js`, подключаемый через `TACTICUM_PAGE_ASSETS=['yandex_map']`;
  - Metrika остаётся централизованной analytics exception в `header.php`; noscript pixel больше не использует inline `style=`.
- REST ownership:
  - общий upstream call в `/tacticum/v1/chat_agent/sale`, retry без `group_id`, masked request/response logs и upstream error handling вынесены в `tacticum_rest_submit_chat_agent_sale(...)`;
  - `tacticum_offer.php` и `tacticum_sale.php` сохранены как legacy aliases с прежним success body.

## Remaining Gaps

| ID | Priority | Area | Gap | Next step |
|---|---|---|---|---|
| TG-019 | P1 | Browser zero-error gate | Подтвердить initial-load browser errors = 0 после deploy локальных JS/CSS/PHP правок | Запустить `npm run visual:smoke` без `TACTICUM_VISUAL_INJECT_CSS` после deploy |

## Acceptance For Zero Browser Errors

- После deploy `npm run visual:smoke` проходит для всех публичных страниц desktop/mobile.
- После deploy `npm run browser:smoke` проходит для non-network UI actions.
- Manifest не содержит `pageErrors`, `consoleErrors`, first-party `networkErrors`.
- Third-party network failures допускаются только как явно классифицированные diagnostics, не как silent errors.
- Для форм/чата/price modal добавлен отдельный action smoke, иначе “0 ошибок” подтверждается только для initial page load.
