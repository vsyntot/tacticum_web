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
- Не удалять `local/templates/tacticum/styles/*.css`.
- Не удалять `bundle.v3.4.16.js` и `init.js` до отдельного JS inventory и staging visual smoke.

## Scope

| Item | Gap | Lane | Status | Acceptance Criteria |
|---|---|---|---|---|
| Frontend package | TG-015 | Full Feature | done | `package.json`/`package-lock.json` фиксируют Tailwind CLI 4.1.8 и scripts `css:build`, `css:check` |
| Tailwind source entrypoint | TG-015 | Full Feature | done | `local/templates/tacticum/assets/src/tailwind.css` содержит controlled `@source` paths и theme tokens |
| Static generated CSS | TG-015 | Full Feature | done | `local/templates/tacticum/tailwind.generated.css` собирается `npm run css:build` |
| Header runtime switch | TG-015 | Full Feature | done | `header.php` подключает `tailwind.generated.css` и не подключает `bundle.v3.4.16.js` / `init.js` |
| CI guard | TG-015 | Security / Integration | done | `pr-check.yml` запускает `npm run css:check` и блокирует возврат browser Tailwind runtime |

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

- Run staging/production visual smoke on desktop/mobile.
- Inventory and classify `local/templates/tacticum/styles/*.css`.
- Delete legacy Tailwind JS artifacts only after a separate JS inventory confirms no runtime use.

## Sprint Review

### Done

- Static Tailwind CSS build added to the working tree.
- Browser Tailwind runtime no longer loads from `header.php`.
- CI now checks generated CSS parity.
- TG-015 moved to `in-progress` until staging visual smoke confirms layout parity.

### Verified Locally

- `npm run css:check`
- YAML parse for `.github/workflows/pr-check.yml`
- `node --check` for touched/guarded frontend scripts
- `git diff --check`
- `git ls-files -c -i --exclude-standard`
- `git diff --name-only -- bitrix/`
- Header guard: no `bundle.v3.4.16.js` / `js/init.js` load, `tailwind.generated.css` is loaded

### Not Run Locally

- PHP syntax lint: local `php` binary is not available in this environment.
- Browser visual smoke: requires staging/production Bitrix runtime.
