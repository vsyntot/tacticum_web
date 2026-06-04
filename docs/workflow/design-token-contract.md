# Design Token Contract

Дата: 01.06.2026

## Назначение

`docs/design-system-handoff/05-design-tokens-as-is.json` - проверяемый AS IS token contract для текущего сайта `tacticum.ru`.

Он нужен дизайнеру, frontend и LLM-assisted refactoring как мост между текущей реализацией и будущей TO BE дизайн-системой. Это не финальный token pipeline и не замена Figma variables.

## Что Зафиксировано

Контракт разделяет токены на три уровня:

| Уровень | Что означает | Источник |
|---|---|---|
| `canonicalTokens` | Уже реализованные project tokens | `local/templates/tacticum/assets/src/tailwind.css` |
| `observedTokenCandidates` | Повторяющиеся AS IS значения, которые стоит нормализовать | `local/templates/tacticum/styles/global.css`, `local/templates/tacticum/js/forms.js` |
| `knownDrift` | Значения, которые похожи на токены, но расходятся с canonical | `global.css` |

Сейчас canonical слой включает:

- `color.brand.primary` -> `--color-primary: #0066CC`;
- `color.brand.secondary` -> `--color-secondary: #001F3F`;
- `radius.button` -> `--radius-button: 8px`.

Observed слой включает focus ring, hover blue, form/control radius, pill radius, card/form/modal elevation, motion durations and toast z-index.

Known drift:

- `.to-primary` использует `#001F40`, хотя canonical navy - `#001F3F`;
- legacy icon hover использует `#007bff`, хотя brand primary - `#0066CC`.

## Guard

Запуск:

```bash
npm run design:tokens:check
```

Guard проверяет:

- JSON валиден;
- `meta.sources` содержит реальные source files;
- canonical JSON tokens совпадают с `tailwind.css`;
- mirrored legacy sections в JSON не расходятся с canonical values;
- observed candidates всё ещё присутствуют в `global.css` / `forms.js`;
- drift описан явно;
- `package.json` содержит script `design:tokens:check`.

## Когда Обновлять

Обновить контракт и запустить guard нужно, если меняется:

- `local/templates/tacticum/assets/src/tailwind.css`;
- `local/templates/tacticum/styles/global.css` в части цветов, radius, shadow, motion, focus, z-index;
- form toast layer in `local/templates/tacticum/js/forms.js`;
- TO BE token naming/mapping после решения дизайнера.

## Граница Решения

AS IS contract уже даёт воспроизводимый снимок текущих значений. Но открыты TO BE решения:

- Figma variables, token JSON, Tailwind theme или hybrid source of truth;
- naming convention for brand, semantic, surface, proof/status, motion and focus tokens;
- strategy for drift cleanup;
- mapping from new design tokens to Bitrix templates and generated Tailwind utilities.

Не закрывать `UI-001` как полностью done, пока TO BE source of truth и naming/mapping не утверждены дизайнером и frontend.

Sprint 20 draft decision 04.06.2026: `docs/workflow/product-to-be-design-system-decision-2026-06-04.md` recommends a hybrid TO BE source model: Figma variables as design source, repo-owned token JSON bridge for review/guards, and Tailwind source + `styles/global.css` as runtime implementation. This is not approved final token source until Designer + Frontend sign-off.
