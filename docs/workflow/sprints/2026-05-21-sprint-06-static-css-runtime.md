# Sprint 06: Static CSS Runtime

Дата старта: 21.05.2026

## Sprint Goal

Снять production dependency с browser Tailwind generator и ввести воспроизводимую static CSS сборку для шаблона без массового удаления legacy CSS/JS artifacts.

## Codex Plan

Issue: internal sprint
Gap ID: TG-015
Workflow lane: Full Feature
Owner agent: PM + Frontend Dev + QA
Date: 21.05.2026

### Goal

- Добавить минимальный Node/Tailwind toolchain.
- Сгенерировать static Tailwind bundle с project theme tokens `primary`, `secondary`, `rounded-button`.
- Подключить static CSS через Bitrix `Asset`.
- Убрать загрузку browser Tailwind runtime из `header.php`.
- Добавить CI check, который гарантирует актуальность generated CSS.

### Non-Goals

- Не удалять `template_styles.css`.
- Не сливать CSS bundles в рамках этого спринта.
- Не удалять `styles/aiagents.css`, пока `/aiagents/` использует explicit page asset flag.

## Scope

| Item | Gap | Lane | Status | Acceptance Criteria |
|---|---|---|---|---|
| Frontend package | TG-015 | Full Feature | done | `package.json`/`package-lock.json` фиксируют Tailwind CLI 4.1.8 и scripts `css:build`, `css:check` |
| Tailwind source entrypoint | TG-015 | Full Feature | done | `local/templates/tacticum/assets/src/tailwind.css` содержит controlled `@source` paths и theme tokens |
| Static generated CSS | TG-015 | Full Feature | done | `local/templates/tacticum/tailwind.generated.css` собирается `npm run css:build` |
| Header runtime switch | TG-015 | Full Feature | done | `header.php` подключает `tailwind.generated.css` и не подключает `bundle.v3.4.16.js` / `init.js` |
| CI guard | TG-015 | Security / Integration | done | `pr-check.yml` запускает `npm run css:check` и блокирует возврат browser Tailwind runtime |
| Dead asset cleanup | TG-015 | Fast Fix | done | Legacy Tailwind JS artifacts и dead page-specific CSS artifacts удалены после source/rendered asset inventory |
| Visual smoke tooling | TG-015 | Full Feature | done | `npm run visual:smoke` проверяет public pages desktop/mobile и сохраняет screenshots/manifest |
| Layout parity fixes | TG-015 | Fast Fix | done | Закрыты найденные horizontal overflow regressions на `/about/`, `/services/`, `/aiagents/` |

## QA Smoke

- `/`: hero, chat block, cards, calculator block and CTA keep expected primary/secondary colors.
- `/about/`, `/services/`, `/contacts/`: CTA sections keep layout and form controls.
- `/price/`: filters, cards and specialist modal keep styling and interaction states.
- `/calculator/`: chat input and CTA keep styling.
- `/offer/`: generated offer page keeps primary buttons, gradients and form styling.
- `/aiagents/`: inline form and agent cards keep styling with `styles/aiagents.css`.
- `/policies/`: legal content remains readable after static CSS load.

## Verification

- `npm install`
- `npm run css:build`
- `npm run css:check`
- YAML parse for `.github/workflows/pr-check.yml`
- Frontend convention scans from `pr-check.yml`
- `git diff --check`

## Follow-Up

- Run post-deploy visual smoke on desktop/mobile without `TACTICUM_VISUAL_INJECT_CSS`.
- Plan merge/retirement strategy for legacy `template_styles.css`.

## Sprint Review

### Done

- Static Tailwind CSS build added to the working tree.
- Browser Tailwind runtime no longer loads from `header.php`.
- CI now checks generated CSS parity.
- Incident fix: build no longer uses Tailwind CLI `--minify`, because preserving cascade layer order is required while Bitrix combines `tailwind.generated.css` with legacy `template_styles.css`.
- Dead generated CSS files and legacy Tailwind JS artifacts removed after inventory.
- Visual smoke tooling added for desktop/mobile page checks.
- Horizontal overflow regressions fixed in hidden mobile menu, services step connectors and aiagents step connector.
- TG-015 closed at code level; post-deploy visual smoke remains the deployment gate.

### Verified Locally

- `npm run css:check`
- `npm run visual:smoke` with `TACTICUM_VISUAL_INJECT_CSS=local/templates/tacticum/tailwind.generated.css,local/templates/tacticum/template_styles.css,local/templates/tacticum/styles/aiagents.css` against `https://tacticum.ru`
- Generated CSS starts with Tailwind cascade layer order declaration
- Source/rendered asset inventory confirms only `styles/aiagents.css` is still approved under `local/templates/tacticum/styles/`
- YAML parse for `.github/workflows/pr-check.yml`
- `node --check` for touched/guarded frontend scripts
- `git diff --check`
- `git ls-files -c -i --exclude-standard`
- `git diff --name-only -- bitrix/`
- Header guard: no `bundle.v3.4.16.js` / `js/init.js` load, `tailwind.generated.css` is loaded

### Not Run Locally

- PHP syntax lint: local `php` binary is not available in this environment.
- Production smoke without CSS injection: requires deploy of the changed CSS first.
