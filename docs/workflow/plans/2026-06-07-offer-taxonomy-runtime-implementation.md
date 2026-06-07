# Codex Plan: Offer Taxonomy Runtime Implementation

Issue: `OFFER-TAX-WP-04`
Gap ID: `OFFER-TAX-001`, `OFFER-TAX-002`, `OFFER-TAX-006`, `OFFER-TAX-009`
Workflow lane: Full Feature
Owner agent: Codex
Date: 07.06.2026

## Goal

Implement the local runtime/tooling slice for the approved `/offer/` taxonomy model from `ADR-012` and `offer-taxonomy-presets-owner-approval-2026-06-07.approved.json`.

## Non-Goals

- No production `offer.taxonomy_source=bitrix` switch.
- No fallback retirement.
- No route, canonical, sitemap, robots, form payload, analytics or upstream contract changes.
- No stored taxonomy counts or offer item membership in Bitrix.
- No `offer_filter_presets` iblock while the approved model uses `preset_source=featured_terms`.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/adr/ADR-012-offer-taxonomy-presets-bitrix-model.md`
- [x] `docs/workflow/offer-taxonomy-presets-owner-approval-2026-06-07.approved.json`
- [x] `local/lib/Tacticum/Offer/*`
- [x] `local/php_interface/include/tacticum_config.php`

## Target Behavior

Runtime can use an approved taxonomy model for sector/scenario/phase labels, aliases, ordering and featured quick entries while preserving current `/offer/` behavior and deriving counts from active offer items.

## Planned Changes

| File / Area | Change |
|---|---|
| `local/lib/Tacticum/Offer/OfferTaxonomy*` | Add fallback, repository, cache and service classes for approved taxonomy terms. |
| `local/lib/Tacticum/Offer/CatalogTaxonomy.php` | Delegate labels, aliases and featured terms to the taxonomy service; keep budget formatting in PHP. |
| `local/lib/Tacticum/Offer/CatalogMapper.php` | Canonicalize sector/scenario/phase keys through approved aliases. |
| `local/lib/Tacticum/Offer/CatalogFilters.php` | Order options through taxonomy terms and keep counts runtime-derived. |
| `local/lib/Tacticum/Offer/CatalogCache.php` | Include taxonomy source/config in cache ID and register offer/taxonomy managed tags. |
| `local/php_interface/include/offer_catalog_cache.php` | Clear offer catalog and taxonomy cache together. |
| `local/lib/Tacticum/Rest/Config*.php` | Add offer taxonomy config defaults and validation. |
| `local/php_interface/include/tacticum_config*.php` | Add `offer_taxonomy_terms` key and fallback offer taxonomy config. |
| `tools/offer-taxonomy-*.php` | Add migration, check and cache-clear CLI tooling. |
| `tools/offer-taxonomy-approved-model.php` | Deployable embedded approved model for production environments where `/docs` is not deployed. |
| `package.json` | Add npm scripts for migration, check and cache clear. |
| `docs/workflow/*` | Record local implementation status and remaining production gates. |

## Rollout Gates

1. Keep local/default config on `offer.taxonomy_source=fallback`.
2. Run target dry-run migration.
3. Run target apply only after owner/operator approval.
4. Sync `offer_taxonomy_terms` ID into environment config.
5. Run non-strict target check and cache clear.
6. Run public rendered hygiene and source checks.
7. Switch to `auto` or `bitrix` only after target evidence is clean.
8. Disable fallback only after strict target check and rollback path are proven.

## Verification

```bash
php -l local/lib/Tacticum/Offer/CatalogTaxonomy.php
php -l local/lib/Tacticum/Offer/CatalogMapper.php
php -l local/lib/Tacticum/Offer/CatalogFilters.php
php -l local/lib/Tacticum/Offer/CatalogCache.php
php -l local/php_interface/include/offer_catalog_cache.php
php -l local/lib/Tacticum/Rest/Config.php
php -l local/lib/Tacticum/Rest/ConfigValidator.php
php -l local/lib/Tacticum/Rest/OfferConfigValidator.php
php -l tools/offer-taxonomy-migration.php
php -l tools/offer-taxonomy-check.php
php -l tools/offer-taxonomy-cache-clear.php
npm run offer:taxonomy:approval:check -- docs/workflow/offer-taxonomy-presets-owner-approval-2026-06-07.approved.json
npm run offer:taxonomy:implementation-gate
npm run config:check
npm run bitrix:check
npm run seo:check
npm run product:content:safety:check
git diff --check
```

Target-only verification:

```bash
npm run offer:taxonomy:migrate
npm run offer:taxonomy:migrate:apply
npm run offer:taxonomy:check
npm run offer:taxonomy:cache-clear
npm run content:public-hygiene:rendered:prod
npm run offer:taxonomy:check:strict
```

## Known Local Blocker

Local Bitrix/PHP commands that require database access fail on this workstation with `Mysql connect error [localhost]: (2002) No such file or directory`. Migration/check evidence must therefore be collected on target/prod.

Production deploy note: `/docs` may be absent on the target server. The migration script must use `docs/workflow/offer-taxonomy-presets-owner-approval-2026-06-07.approved.json` when it is present, but must fall back to `tools/offer-taxonomy-approved-model.php` for default production runs without weakening the approval checks.

## Rollback

- Keep or restore `offer.taxonomy_source=fallback`.
- Keep `offer.allow_taxonomy_fallback=true` until strict target evidence passes.
- Run `npm run offer:taxonomy:cache-clear` and public rendered hygiene after rollback.
