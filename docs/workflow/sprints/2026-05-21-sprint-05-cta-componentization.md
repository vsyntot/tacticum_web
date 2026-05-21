# Sprint 05: CTA Componentization

Дата старта: 21.05.2026

## Sprint Goal

Закрыть ближайшие безопасные куски PG-008/TG-017: убрать копирование основных CTA/form sections между ключевыми публичными страницами без изменения REST-контракта форм.

## Codex Plan

Issue: internal sprint
Gap ID: PG-008, TG-017
Workflow lane: Full Feature
Owner agent: PM + Frontend Dev + QA
Date: 21.05.2026

### Goal

- Вынести повторяемый personal-offer CTA/form markup в общий include активного шаблона.
- Поддержать `glass` variant для contacts CTA без изменения form contract.
- Вынести повторяемый project-discussion CTA/form markup для `/about/` и `/services/`.
- Сохранить разные `data-form-id` и field IDs для каждой публичной страницы.
- Добавить PR guards против обратного копирования CTA markup в публичные страницы.

### Non-Goals

- Не менять backend endpoints форм.
- Не трогать static CSS build из TG-015.

### Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/asset-layout-audit.md`

## Scope

| Item | Gap | Lane | Status | Acceptance Criteria |
|---|---|---|---|---|
| Shared personal offer CTA include | PG-008, TG-017 | Full Feature | done | CTA markup lives in `local/templates/tacticum/include/personal-offer-cta.php`; public pages pass only page-specific config and optional `variant` |
| Home CTA migration | PG-008 | Full Feature | done | `index.php` uses shared include and keeps `data-form-id="home-cta"` plus `id="cta-form"` |
| Calculator CTA migration | PG-008 | Full Feature | done | `calculator/index.php` uses shared include and keeps `data-form-id="calculator-cta"` |
| Price CTA migration | PG-008 | Full Feature | done | `price/index.php` uses shared include and keeps `data-form-id="price-cta"` plus `id="pricing-cta-form"` |
| Contacts CTA migration | PG-008 | Full Feature | done | `contacts/index.php` uses shared include with `variant="glass"` and keeps `data-form-id="contacts-cta"` plus `id="contacts-cta-form"` |
| Project discussion CTA include | PG-008, TG-017 | Full Feature | done | CTA markup lives in `local/templates/tacticum/include/project-discussion-cta.php`; `/about/` and `/services/` pass only page-specific config |
| About CTA migration | PG-008 | Full Feature | done | `about/index.php` uses shared include and keeps `data-form-id="about-cta"` plus `id="about-cta-form"` |
| Services CTA migration | PG-008 | Full Feature | done | `services/index.php` uses shared include and keeps `data-form-id="services-cta"` plus `id="services-cta-form"` |
| PR guards | TG-017 | Security / Integration | done | `pr-check.yml` fails if shared CTA markup is copied back into migrated public pages |

## QA Smoke

- `/`: CTA renders with the same layout, image, consent link and submits as `home-cta`.
- `/calculator/`: CTA renders with the same layout, image, consent link and submits as `calculator-cta`.
- `/price/`: CTA renders with the same layout, image, consent link and submits as `price-cta`.
- `/contacts/`: CTA renders with the same glass form layout, image, consent link and submits as `contacts-cta`.
- `/about/`: project discussion CTA renders with the same layout, benefit bullets and submits as `about-cta`.
- `/services/`: project discussion CTA renders with the same layout, benefit bullets and submits as `services-cta`.
- All consent links inside the shared CTA open `/policies/` in a new tab.
- No duplicate IDs appear on any single migrated page.

## Verification

- Run YAML parse for `.github/workflows/pr-check.yml`.
- Run CTA guard scans from `pr-check.yml`.
- Run `git diff --check`.
- PHP lint requires GitHub Actions or local PHP; local workstation currently has no `php` in `PATH`.

## Follow-Up

- Keep new CTA variants include/component driven; do not copy form markup into public pages.
- TG-015 static CSS build remains deferred until rendered visual baseline.

## Sprint Review

### Done

- Основной repeated CTA вынесен в template include.
- Project discussion CTA для `/about/` и `/services/` вынесен во второй template include.
- Главная, `/calculator/`, `/price/`, `/contacts/`, `/about/` и `/services/` переведены на includes с сохранением analytics/form IDs.
- Gap registry и asset/layout audit обновлены.
- PR checks расширены guard против повторного копирования CTA markup.

### Verified Locally

- `node --check` для production JS bundles и price component script.
- `.github/workflows/pr-check.yml` успешно парсится Ruby YAML loader.
- Guard scans подтверждают, что migrated public pages больше не содержат duplicated CTA markup.
- `git diff --check` без whitespace errors.

### Not Run Locally

- PHP lint: локально нет `php` в `PATH`; CI/deploy workflow запускает PHP 8.4 lint.
- Browser visual smoke: требует staging/production URL; выполнить по QA Smoke выше после deploy.
