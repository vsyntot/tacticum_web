# Sprint 04: Layout Architecture Hardening

Дата старта: 21.05.2026

## Sprint Goal

Снизить хрупкость верстки в `/local` и публичной части сайта без рискованной массовой CSS-миграции: убрать URL/text-based поведение, inline presentation/behavior и крупный JS-owned modal markup.

## Codex Plan

Issue: internal sprint
Gap ID: PG-008, TG-015, TG-016, TG-017, TG-018
Workflow lane: Full Feature
Owner agent: PM + Frontend Dev + QA
Date: 21.05.2026

### Goal

- Сделать layout behavior более явным и переносимым.
- Закрепить правила PR checks, чтобы старые паттерны не вернулись.
- Оставить static CSS build как отдельный controlled follow-up после visual baseline.

### Non-Goals

- Не удалять `bundle.v3.4.16.js` в Sprint 04; follow-up inventory/cleanup выполнен в Sprint 06.
- Не удалять `styles/*.css` в Sprint 04; follow-up inventory/cleanup выполнен в Sprint 06, а 24.05.2026 бывший `styles/aiagents.css` слит в scoped `styles/global.css`.
- Не переписывать все публичные страницы и повторяемые CTA blocks в одном спринте.

### Context Read

- [x] `AGENTS.md`
- [x] `.github/copilot-instructions.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/asset-layout-audit.md`
- [x] `docs/workflow/static-css-build-plan.md`

## Scope

| Item | Gap | Lane | Status | Acceptance Criteria |
|---|---|---|---|---|
| FAQ explicit presentation param | TG-016 | Full Feature | done | FAQ component no longer uses `GetCurPage()`/`substr_count()`; `/aiagents/` passes `SECTION_CLASS` explicitly |
| Inline navigation cleanup | TG-018 | Fast Fix | done | Public markup has no inline `onclick`; navigation buttons are semantic links |
| Policy component CSS | TG-018 | Fast Fix | done | Policy detail template has no inline `<style>` or `style=`; styles live in component `style.css` |
| Progress width cleanup | TG-018 | Fast Fix | done | Home calculator progress bars use CSS classes instead of inline width |
| Specialist modal componentization | TG-017 | Full Feature | done | Price specialist modal markup lives in `news.list/price/template.php`; JS only opens/closes, filters by CSS classes and fills values |
| Remove legacy selector heuristics | TG-016, TG-017 | Full Feature | done | `modal.js` has no path-based aibot branch or text-based specialist fallback; `scroll.js` no longer toggles arbitrary `.container button` by text |
| Form UI state via CSS | TG-018 | Fast Fix | done | `forms.js` and price component script no longer mutate inline styles for labels/checkboxes/display; CSS classes/selectors own visual state |
| PR layout guards | TG-016, TG-018 | Security / Integration | done | `pr-check.yml` blocks URL/text selector regressions, inline `onclick`, policy inline styles, form inline style mutations and JS-generated specialist modal markup |
| Static CSS build | TG-015 | Full Feature | deferred | Remains in `docs/workflow/static-css-build-plan.md`; requires visual baseline before implementation |

## QA Smoke

- `/aiagents/`: FAQ section keeps expected gray background.
- `/`, `/calculator/`, `/price/`, `/services/`, `/offer/`: FAQ section renders with default spacing/background.
- `/price/`: specialist order modal opens, selected specialist/rate/level are filled, submit still uses `/local/rest/tacticum_sale_staff.php`.
- `/about/` and `/services/`: hero/CTA navigation links work without inline JS.
- `/policies/`: policy text keeps readable spacing and justified body after moving styles to component CSS.
- `/`: calculator progress bars preserve 65/35/85% visual widths.

## Verification

- Run `node --check` for changed JS.
- Run YAML parse for `.github/workflows/pr-check.yml`.
- Run `git diff --check`.
- Run guard scans from PR checks locally.
- PHP lint requires GitHub Actions or local PHP; local workstation currently has no `php` in `PATH`.

## Follow-Up

- PG-008: componentize repeated CTA/form sections.
- TG-015: implement static CSS build after rendered asset list and screenshots baseline.
- TG-017: continue moving large reusable layout blocks from public page PHP into includes/components.

## Sprint Review

### Done

- Новые layout gaps зафиксированы в `docs/workflow/gap-analysis.md`.
- Sprint scope, plan, QA smoke и follow-up зафиксированы в этом документе.
- Кодовые задачи Sprint 04 реализованы без изменения `bitrix/`.
- PR checks расширены layout/frontend guards.

### Verified Locally

- `node --check` для `analytics.js`, `forms.js`, `chat-agent.js`, `modal.js`, `scroll.js`, `tg-link-resolver.js`, `news.list/price/script.js`.
- `.github/workflows/pr-check.yml` успешно парсится Ruby YAML loader.
- Новый frontend convention block из `pr-check.yml` выполнен локально и прошёл.
- `git diff --check` без whitespace errors.

### Not Run Locally

- PHP lint: локально нет `php` в `PATH`; CI/deploy workflow запускает PHP 8.4 lint.
- Browser visual smoke: требует staging/production URL; выполнить по QA Smoke выше после deploy.
