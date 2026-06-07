# ADR-012: Offer Taxonomy And Presets Bitrix Model

Дата: 07.06.2026

Статус: Принято

## Контекст

Страница `/offer/` показывает каталог примеров расчетов, фасетные фильтры и быстрые входы. После fast-fix 07.06.2026 публичные карточки больше не показывают машинный бюджет, а быстрые входы больше не берутся из произвольных первых восьми агрегированных опций. Но это промежуточная защита:

- sector/scenario/phase по-прежнему происходят из `RESPONSE` offer items;
- часть публичных label-ов нормализуется временным `Tacticum\Offer\CatalogTaxonomy`;
- quick entries заданы curated PHP-списком;
- budget buckets остаются PHP-defined;
- неизвестные raw labels нельзя считать автоматически approved public taxonomy.

Текущую эвристику нельзя переносить в Bitrix one-to-one. Это закрепит synthetic/generated vocabulary как публичный справочник и создаст административный долг без управления смыслом.

## Решение

Предлагается ввести governed taxonomy/preset layer для `/offer/`, но не хранить вычисляемые факты в редакторских строках.

1. Целевая модель использует `offer_taxonomy_terms` или эквивалентный Bitrix content model для публичной таксономии.
2. Term model хранит:
   - `DIMENSION`: `sector`, `scenario`, `phase`, optional `budget`;
   - `CODE`: стабильный canonical code/slug;
   - `PUBLIC_LABEL`: approved Russian-first public label;
   - `SHORT_LABEL`: optional compact chip/quick-entry label;
   - `ALIASES`: raw labels and legacy/generated variants that map to the canonical code;
   - `SORT`;
   - `ACTIVE`;
   - `FEATURED`;
   - optional `PRODUCT_FAMILY`: `platform`, `agents`, `dev`, `forum`;
   - optional `BUDGET_MIN` / `BUDGET_MAX` only if budget buckets move from PHP/config to Bitrix.
3. Separate `offer_filter_presets` model is optional and should be used only if quick entries become combinations of filters rather than featured single terms.
4. Counts, result availability and item membership remain runtime-derived from active offer items.
5. Filtered URLs keep current `noindex,follow` and canonical `/offer/` unless SEO opens a separate indexable landing-page project.
6. Runtime source must be controlled by explicit config source mode and fallback policy before any production switch.
7. Unknown raw labels must be detected by checker and handled as hidden/mapped/pending review, not silently rendered as new public taxonomy.
8. Synthetic examples remain examples/orientation, not proof, case evidence or market validation.
9. Owner approval must be captured by a safe approval JSON validated by `tools/offer-taxonomy-approval-check.mjs`.

## Suggested Runtime Shape

The implementation after approval should follow this pattern:

```php
'offer_taxonomy' => [
    'source' => 'fallback', // fallback|auto|bitrix
    'cache_ttl' => 300,
    'allow_fallback' => true,
],
```

Expected iblock keys, if Bitrix model is approved:

- `offer_taxonomy_terms`;
- optional `offer_filter_presets`.

New code must use `tacticum_rest_get_iblock_id('offer_taxonomy_terms')` or an equivalent helper. New hardcoded iblock IDs are not allowed.

## Rollout And Rollback

Target rollout should be split:

1. Owner approval JSON passes checker.
2. ADR status is confirmed as `Принято`.
3. Migration dry-run creates schema plan with no public runtime change.
4. Migration apply creates iblocks/properties and seeds approved terms/presets.
5. Strict taxonomy checker passes against Bitrix rows and active offer items.
6. Source mode switches to `auto`, then `bitrix` only after production evidence.
7. Cache clear covers offer iblock, taxonomy iblock, preset iblock and public rendered cache.

Rollback is source-mode switch back to `fallback` plus cache clear. Rollback must not delete Bitrix rows.

## Последствия

Плюсы:

- public labels/order/featured flags become governed rather than incidental;
- raw generated labels can be normalized through aliases;
- quick entries become owner-approved and stable;
- unknown labels become visible to QA/Content before release;
- current URL/SEO posture can be preserved while improving governance.

Минусы и ограничения:

- нужны owner approvals before migration;
- потребуется config sync on every Bitrix environment;
- taxonomy cache invalidation must include new iblock tags;
- approved public labels still need Content/SEO ownership, not only backend schema;
- existing PHP fallback remains until strict target evidence exists.

## Не Делаем В Этом Решении

- не делаем `/offer/catalog/...` indexable pages;
- не меняем calculator logic;
- не меняем offer detail canonical/sitemap behavior;
- не меняем lead forms, hidden fields, CRM/upstream payloads or analytics taxonomy;
- не переносим counts/result lists/item membership into Bitrix rows;
- не превращаем synthetic examples into proof/cases;
- не создаем production iblocks without approved owner JSON.

## Acceptance Before Runtime Implementation

- `npm run offer:taxonomy:approval:self-test` passes.
- `npm run offer:taxonomy:implementation-gate:self-test` passes.
- `npm run offer:taxonomy:implementation-gate` passes before runtime markers are introduced.
- Owner approval JSON passes `npm run offer:taxonomy:approval:check -- <file>` without `--allow-draft`.
- ADR status is `Принято`.
- `OFFER-TAX-WP-04` references the approved JSON and this ADR.
- Migration/check/cache-clear tooling is included in the implementation issue.
