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

## Remaining Gaps

| ID | Priority | Area | Gap | Next step |
|---|---|---|---|---|
| TG-019 | P1 | Browser zero-error gate | Подтвердить initial-load browser errors = 0 после deploy локальных JS/CSS/PHP правок | Запустить `npm run visual:smoke` без `TACTICUM_VISUAL_INJECT_CSS` после deploy |
| TG-021 | P1 | Frontend contracts | Light chat и price component всё ещё частично завязаны на presentation selectors / button text | Перевести на `data-*` контракты |
| TG-022 | P2 | REST ownership | `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_form.php` пересекаются по sale flow | Спланировать единый sale handler/adapters или deprecation |
| TG-023 | P2 | Inline/vendor assets | Yandex Maps и Metrika остаются inline/vendor exceptions | Описать CSP-ready strategy и explicit map asset |
| TG-024 | P2 | Action smoke | `visual:smoke` покрывает initial load, но не клики, формы, чаты и price modal | Добавить action-smoke режим или отдельный `browser:smoke` |

## Acceptance For Zero Browser Errors

- После deploy `npm run visual:smoke` проходит для всех публичных страниц desktop/mobile.
- Manifest не содержит `pageErrors`, `consoleErrors`, first-party `networkErrors`.
- Third-party network failures допускаются только как явно классифицированные diagnostics, не как silent errors.
- Для форм/чата/price modal добавлен отдельный action smoke, иначе “0 ошибок” подтверждается только для initial page load.
