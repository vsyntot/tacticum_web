# Codex Plan: `/price/` Team Presets Bitrix Model

Issue: local owner request
Gap ID: `BPC-FE-001`, `CSG-007` guard-preserving extension, new `PRICE-PRESET-001`
Workflow lane: Full Feature + Security / Integration review surface
Owner agent: Codex
Date: 06.06.2026

## Goal

Заменить split PHP/JS hardcode быстрых team presets на доменную Bitrix-модель, связанную с `rates`, сохранив публичный UX `/price/` и contract staff-order формы.

## Non-Goals

- Не переносить `workstreams` из `page_sections/page_blocks`.
- Не менять структуру ставок в `rates`.
- Не менять внешний AI/upstream response или endpoint path.
- Не делать визуальный редизайн `/price/`.
- Не автоматизировать migration в deploy workflow.

## Context Read

- [x] `AGENTS.md`
- [x] `.github/copilot-instructions.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] Relevant ADR: `ADR-003`, `ADR-010`, `ADR-011`
- [x] Relevant files: `/price/` component shell, `news.list/price` template/JS, `StaffOrderPayload`, `tacticum_config.php`

## Current Behavior

- `parts/catalog.php` renders preset buttons from local PHP array.
- `price-configurator-utils.js` defines actual preset roles in JS.
- `price-configurator-order-state.js` applies presets by keyword matching visible rate cards.
- Staff endpoint validates workers shape but does not resolve preset metadata server-side.

## Target Behavior

- Active presets are loaded through `Tacticum\Price` from Bitrix when configured.
- Preset buttons and JS data come from the same PHP model.
- Preset roles reference `rates` through stable element IDs in target mode.
- Legacy keyword fallback exists only for rollout/rollback.
- Staff-order task text uses server-resolved preset label/source/version.

## Planned Changes

| File | Change |
|---|---|
| `docs/adr/ADR-011-price-team-presets-bitrix-model.md` | Document target model and rollout rules |
| `docs/workflow/plans/2026-06-06-price-team-presets-bitrix-model.md` | Track implementation plan |
| `local/php_interface/include/tacticum_config.example.php` | Add `team_presets`, `team_preset_roles`, `price` config |
| `local/lib/Tacticum/Rest/Config.php` | Add `price` defaults |
| `local/lib/Tacticum/Rest/ConfigValidator.php` | Add `price` scope validation |
| `local/lib/Tacticum/Price/*` | Add repository/service/fallback model |
| `local/templates/tacticum/components/bitrix/news.list/price/*` | Render presets from model and apply by rate IDs |
| `local/lib/Tacticum/Rest/StaffOrderPayload.php` | Resolve preset metadata server-side |
| `local/lib/Tacticum/Rest/StaffOrderText.php` | Include preset source/version in task text |
| `tools/price-team-presets-migration.php` | Create/seed Bitrix schema |
| `tools/price-team-presets-check.php` | Validate schema/data readiness |
| `package.json` | Add migration/check scripts |
| `docs/workflow/gap-analysis.md` / `current-state.md` | Record implementation status |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS через existing component assets
- [x] Infoblock IDs через config helper/service
- [x] D7 `Loader::includeModule()` in new shared code
- [x] POST REST bootstrap unchanged

## Risks

| Risk | Mitigation |
|---|---|
| Runtime config missing on production | Default fallback mode; explicit migration/config hints |
| Preset/rates relation broken | Strict checker and no silent Bitrix-to-keyword success in source=`bitrix` |
| Payload compatibility | Keep `workers_json`, `team_preset`, response shape unchanged |
| Cache stale after admin edit | Short TTL in v1; future managed tags if needed |
| Content governance violation | Keep `workstreams` in `page_sections/page_blocks`; `team_presets` only for functional composition |

## Verification

### Automated

```bash
php -l local/lib/Tacticum/Price/TeamPresetFallback.php
php -l local/lib/Tacticum/Price/TeamPresetRepository.php
php -l local/lib/Tacticum/Price/TeamPresetService.php
php -l tools/price-team-presets-migration.php
php -l tools/price-team-presets-check.php
npm run js:check
npm run config:check
npm run component:states:check
npm run bitrix:check
```

### Manual Smoke

- URL/API: `/price/`
- Action: apply each quick preset, open staff modal, submit only in controlled staging/manual flow.
- Expected: buttons render, team summary appears, `workers_json` and `team_preset` are populated, no console errors.

## Rollback

Set `price.team_presets_source=fallback` and clear Bitrix/cache if needed. Revert code only if fallback path itself fails. Existing staff endpoint contract remains compatible.

## Docs To Update

- [x] `docs/adr/*`
- [x] `docs/workflow/plans/*`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/current-state.md`
