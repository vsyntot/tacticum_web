# Static CSS Build Plan

Дата фиксации: 21.05.2026

## Current State

- В корне проекта есть минимальный `package.json`/`package-lock.json` для Tailwind CLI.
- Source entrypoint: `local/templates/tacticum/assets/src/tailwind.css`.
- Production static output: `local/templates/tacticum/tailwind.generated.css`.
- `local/templates/tacticum/styles/global.css` содержит migrated global/template CSS и подключается через `Asset`.
- `local/templates/tacticum/template_styles.css` оставлен пустым/comment-only Bitrix compatibility shim; active rules туда не возвращать.
- `local/templates/tacticum/styles/global.css` является единственным runtime manual CSS-файлом шаблона; бывший `aiagents.css` перенесён в scoped-блок `tacticum-aiagents-page`.
- Dead generated page-specific CSS artifacts и legacy browser Tailwind JS artifacts удалены после source/rendered asset inventory.

## Target

Перейти к воспроизводимой static CSS сборке, где:

- исходные CSS/Tailwind entrypoints лежат в контролируемой директории, например `local/templates/tacticum/assets/src/`;
- production output пишется в `local/templates/tacticum/tailwind.generated.css` и явные template/component assets;
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
   - зафиксировать scripts: `css:build`, `css:watch`, `css:check`, `visual:smoke`, `browser:smoke`.

3. Build Parity:
   - собрать CSS без изменения visual output;
   - сравнить размер, порядок critical rules и наличие custom classes из `styles/global.css`;
   - не удалять старые CSS до прохождения visual smoke.

4. Cleanup:
   - пометить `local/templates/tacticum/styles/*.css` как `used`, `dead` или `unknown`;
   - удалить только `dead` после deploy smoke;
   - оставить migration note в `asset-layout-audit.md`.

## Acceptance Criteria

- Сборка CSS воспроизводима локально и в CI.
- Нет runtime dependency на browser Tailwind generator в `header.php`.
- Все public pages проходят visual smoke на desktop/mobile.
- `tailwind.generated.css` обновляется только через `npm run css:build`.
- Удаление stale CSS не меняет rendered layout.

## Implemented

- `npm run css:build` собирает `local/templates/tacticum/tailwind.generated.css`.
- `npm run css:check` пересобирает CSS во временный файл, сравнивает с committed bundle и проверяет наличие Tailwind cascade layer order declaration.
- `.github/workflows/pr-check.yml` запускает `npm ci` и `npm run css:check`.
- `header.php` подключает `tailwind.generated.css` и больше не подключает `bundle.v3.4.16.js` / `init.js`.
- `tailwind.generated.css` намеренно собирается без Tailwind CLI `--minify`: minifier удаляет пустую декларацию порядка слоёв, после чего migrated global CSS может перебить utilities своим reset/compat rules.
- Rendered asset inventory выполнен для `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`.
- Dead `styles/*.css` artifacts удалены; после CSS consolidation в `styles/` оставлен только реально подключаемый `global.css`.
- Legacy browser Tailwind JS artifacts `bundle.v3.4.16.js` и `init.js` удалены.
- PR checks блокируют восстановление dead CSS/JS artifacts.
- `npm run visual:smoke` запускает headless Chrome smoke для публичных страниц на desktop/mobile и сохраняет screenshots + `manifest.json`.
- `TACTICUM_VISUAL_INJECT_CSS` позволяет проверить локальные CSS-файлы против staging/production HTML до deploy.
- `npm run browser:smoke` запускает тот же runner с `TACTICUM_VISUAL_ACTIONS=1` и добавляет non-network UI action checks.
- `npm run visual:smoke:css-local` и `npm run browser:smoke:css-local` удаляют production CSS links по `TACTICUM_VISUAL_REMOVE_CSS`, затем inject локальные `tailwind.generated.css` и `styles/global.css` против production HTML.
- Visual smoke с локально внедрёнными `tailwind.generated.css`, `template_styles.css`, `styles/aiagents.css` прошёл для `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/` до gap №8; после gap №8 `browser:smoke:css-local` прошёл с `styles/global.css`; после CSS consolidation `/aiagents/` rules также живут в `styles/global.css`.
- Найденные visual regressions закрыты: hidden off-canvas menu больше не создаёт horizontal overflow; step connectors ограничены контейнером; migrated global CSS получил parity block для responsive utilities, которые перебивались порядком подключения.
- Sprint 08: из `template_styles.css` удалён старый generated Tailwind block; generated utilities остаются только в `tailwind.generated.css`, а PR check блокирует возврат Tailwind layer block в legacy file.
- Gap №8: активные правила из `template_styles.css` перенесены в `styles/global.css`; `template_styles.css` стал пустым/comment-only shim; добавлен `npm run template-styles:check`.
- CSS consolidation 24.05.2026: `styles/aiagents.css` удалён, `/aiagents/` получил body class `tacticum-aiagents-page`, а page-specific rules перенесены в scoped-блок `styles/global.css`.

## Remaining

- Deploy workflow выполняет `npm run visual:smoke` и `npm run browser:smoke` против `https://tacticum.ru` без `TACTICUM_VISUAL_INJECT_CSS`; для локальной/ручной выкладки запускать `npm run visual:smoke:prod`, `npm run browser:smoke:prod` и при изменениях `/price/` `npm run browser:smoke:price`.
- Retirement strategy для `template_styles.css` выполнена до shim-состояния; дальнейший cleanup — component/page extraction из `styles/global.css` малыми партиями после чистого `visual:smoke:css-local`.
- `npm run dev:preflight` проверяет локальную готовность: если PHP CLI 8.4+ доступен, выполняет syntax lint по `local/**/*.php`; если PHP CLI отсутствует или версия ниже 8.4, фиксирует degraded local state и оставляет обязательным fallback GitHub PR check `php-lint` с PHP 8.4.

## Do Not Do

- Не менять классы в публичных страницах массово до появления visual regression baseline.
- Не включать Tailwind CLI `--minify` для `tailwind.generated.css`, пока файл склеивается Bitrix asset pipeline вместе с migrated global CSS.
- Не добавлять новые файлы в `local/templates/tacticum/styles/` без отдельного архитектурного решения; текущий approved template-level manual CSS file — только `styles/global.css`.
- Не возвращать активные правила или `@import` в `template_styles.css`.
- Не считать локальный preflight заменой CI: GitHub `php-lint` остаётся authoritative fallback, особенно если у разработчика нет PHP CLI 8.4+.
