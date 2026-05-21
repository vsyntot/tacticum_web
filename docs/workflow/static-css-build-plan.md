# Static CSS Build Plan

Дата фиксации: 21.05.2026

## Current State

- В корне проекта нет `package.json`, `tailwind.config.*`, `postcss.config.*`, `vite.config.*`, `webpack.config.*` или `gulpfile.*`.
- `local/templates/tacticum/template_styles.css` уже содержит generated Tailwind CSS (`tailwindcss v4.1.8`) и является основным шаблонным CSS.
- `local/templates/tacticum/styles/*.css` выглядят как generated page-specific artifacts, но почти не подключаются явно.
- Runtime/legacy JS bundle `local/templates/tacticum/js/bundle.v3.4.16.js` остаётся production dependency; удалять или заменять его без visual regression нельзя.

## Target

Перейти к воспроизводимой static CSS сборке, где:

- исходные CSS/Tailwind entrypoints лежат в контролируемой директории, например `local/templates/tacticum/assets/src/`;
- production output пишется в `local/templates/tacticum/template_styles.css` или новый версионированный bundle;
- список content paths покрывает публичные страницы, шаблон и компоненты;
- stale `styles/*.css` удаляются только после доказанного отсутствия подключений и visual regression.

## Proposed Steps

1. Inventory:
   - снять rendered asset list для `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`;
   - проверить, какие CSS реально попадают в HTML через Bitrix asset pipeline;
   - зафиксировать screenshots desktop/mobile для ключевых страниц.

2. Tooling:
   - добавить минимальный frontend package только для template assets;
   - выбрать Tailwind v4 CLI/PostCSS strategy;
   - зафиксировать scripts: `css:build`, `css:watch`, `css:check`.

3. Build Parity:
   - собрать CSS без изменения visual output;
   - сравнить размер, порядок critical rules и наличие custom classes из `template_styles.css`;
   - не удалять старые CSS до прохождения visual smoke.

4. Cleanup:
   - пометить `local/templates/tacticum/styles/*.css` как `used`, `dead` или `unknown`;
   - удалить только `dead` после deploy smoke;
   - оставить migration note в `asset-layout-audit.md`.

## Acceptance Criteria

- Сборка CSS воспроизводима локально и в CI.
- Нет runtime dependency на browser Tailwind generator.
- Все public pages проходят visual smoke на desktop/mobile.
- `template_styles.css` или новый bundle обновляется только через documented build command.
- Удаление stale CSS не меняет rendered layout.

## Do Not Do

- Не удалять `bundle.v3.4.16.js` без отдельного JS inventory.
- Не удалять `styles/*.css` только потому, что они не видны в `header.php`.
- Не менять классы в публичных страницах массово до появления visual regression baseline.

