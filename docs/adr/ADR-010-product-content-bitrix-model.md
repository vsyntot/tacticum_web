# ADR-010: Bitrix Product Content Model

Дата: 02.06.2026

Статус: Принято

## Контекст

Product-first MVP для `/platform/`, `/agents/`, `/dev/` and `/forum/` уже работает через Git-owned structured data в `local/php_interface/include/product_data/*.php` and shared renderer `local/php_interface/include/product_page.php`.

После product challenge принято целевое решение: продуктовый контент должен стать редактируемым в Bitrix, но без риска сломать production pages, SEO/schema and lead forms.

## Решение

1. Bitrix становится целевым source of truth для product content.
2. V1 content model использует новые инфоблоки:
   - `products`;
   - `product_blocks`;
   - `product_use_cases`.
3. Существующие инфоблоки расширяются связью с продуктами, если они есть в registry:
   - `faq`;
   - `cases`;
   - `offer`;
   - `services`;
   - `aiagents`.
4. Сложные product sections хранятся в `product_blocks`; architecture diagrams остаются code-rendered templates with Bitrix-managed labels/content.
5. Use cases / pilot kits хранятся в отдельном `product_use_cases`.
6. Proof readiness для v1 хранится в `product_blocks` as claim-safe readiness content. Approved case proof позже подтягивается через расширенный `cases` model.
6.1. После повторного admin-editability challenge 05.06.2026 модель уточнена как V2-compatible:
   - JSON в `BADGES_JSON`, `HERO_CARDS_JSON`, `CTA_JSON`, `SOURCE_DATA_JSON` and JSON `DETAIL_TEXT` считается legacy seed/compatibility, not primary editor workflow;
   - `products` получает admin-editable scalar/multiple properties for badges and CTA/form/lead context;
   - `product_blocks` работает как container/item model: block containers have `PRODUCT`, `BLOCK_TYPE`, `BLOCK_KEY`; child rows use `PARENT_BLOCK`, `ITEM_TYPE`, plain `NAME`/`PREVIEW_TEXT`/`DETAIL_TEXT` and item properties (`ICON`, `META`, `HREF`, `ITEMS`, `PROOF_STATUS`, scenario `VALUE`/`LABEL`);
   - `product_use_cases` properties are primary; legacy JSON in `DETAIL_TEXT` is read only as fallback for old rows;
   - runtime reads V2 fields first and falls back to legacy JSON only while target content migration is incomplete;
   - `tools/product-content-migration.php --apply --update-seed-content --retire-legacy-json` clears product JSON values and deactivates product JSON properties after V2 content is seeded;
   - target release evidence requires zero legacy JSON counters in `admin_model.legacy_json`, including active product JSON properties.
7. Runtime source управляется config flag:

```php
'products' => [
    'source' => 'auto', // auto|bitrix|fallback
    'cache_ttl' => 300,
],
```

8. Default mode `auto`: сначала пробуем валидный Bitrix content, затем fallback на `product_data/*.php`.
9. `fallback`: всегда использовать Git-owned product data.
10. `bitrix`: использовать только Bitrix content; если он не проходит minimum renderable validation, fallback не включается.
11. Minimum renderable validation проверяет product code, title, lead and CTA content. TO BE completeness diagnostics отдельно проверяет fit guide, use cases, comparison, procurement, rollout, proof readiness, FAQ and architecture.
12. Инфоблоки и seed создаются PHP CLI migration/bootstrap script:

```bash
php tools/product-content-migration.php
php tools/product-content-migration.php --apply
php tools/product-content-migration.php --apply --update-seed-content
php tools/product-content-migration.php --apply --update-seed-content --retire-legacy-json
php tools/product-content-check.php
php tools/product-content-check.php --strict
```

13. Migration script:
   - dry-run по умолчанию;
   - idempotent;
   - ищет/создаёт инфоблоки и свойства по `CODE` / `XML_ID`;
   - не хардкодит runtime IDs;
   - seed-ит текущий `product_data/*.php`;
   - create-only по умолчанию;
   - обновляет существующий seed content только с `--update-seed-content`;
   - после apply выводит IDs для `tacticum_config.php`.
14. Runtime readiness проверяется отдельным CLI checker:
   - default mode падает только при отсутствии core product content;
   - `--strict` дополнительно требует TO BE blocks, use cases, product relation properties and non-`fallback` source mode;
   - `--strict` also validates the admin-editable V2 property schema for `products`, `product_blocks` and `product_use_cases`;
   - JSON evidence includes safe `admin_model.v2_schema` and `admin_model.legacy_json` summary without raw content, and fails when legacy JSON counters are non-zero;
   - checker не заменяет rendered smoke, но ловит рассинхрон Bitrix content/config до открытия публичных страниц.
15. Bitrix product content кешируется через `Bitrix\Main\Data\Cache` в `/tacticum/product_content`:
   - TTL задаётся `products.cache_ttl`;
   - cache key включает schema version, source mode and IDs `products`, `product_blocks`, `product_use_cases`;
   - managed tags регистрируются как `iblock_id_*` для product-инфоблоков;
   - event handlers чистят cache при add/update/delete/property update product elements;
   - `npm run product:content:cache-clear` gives owners a Bitrix/PHP CLI cache clear for switch/rollback, with `--dry-run` evidence available through `product:content:cache-clear:dry-run`.
16. `health_config.php` включает scope `products` and validates product iblock IDs, source mode and cache TTL without returning secret/config values.
17. Product renderer exposes safe `data-product-source` (`bitrix|fallback|unknown`), visual smoke can verify rendered source with `TACTICUM_EXPECT_PRODUCT_SOURCE=bitrix`, and HTTP source check can verify the same marker without Chrome.

## Последствия

Плюсы:

- редакторы получают Bitrix-managed product content model;
- production rollout безопасен через `auto` + fallback;
- текущие product pages, schema and CTA contracts сохраняют тот же `tacticum_product_page_data(...)` interface;
- public requests не читают три product-инфоблока без cache на каждом request;
- risky proof/claims не становятся публичными автоматически;
- runtime-код продолжает читать iblock IDs through config registry.
- редакторская модель больше не требует править JSON; legacy JSON properties are retired from admin after V2 migration.

Минусы и ограничения:

- нужен запуск CLI migration на окружениях;
- `tacticum_config.php` нужно обновить новыми iblock keys after apply;
- Bitrix content не считается complete только потому, что создана schema;
- после миграции нужен `product:content:check` в окружении с PHP/Bitrix; target check passed 03.06.2026 for `products=#21`, `product_blocks=#22`, `product_use_cases=#23`, source `auto`, all product rows from Bitrix;
- post-deploy health должен включать scope `products`;
- rendered production `npm run seo:smoke` passed 03.06.2026 with product pages `seo=ok` and `blocks=ok`;
- `npm run product:source:smoke:prod` is browser source-marker evidence when Chrome/Chromium is available;
- `npm run product:source:http:prod` is production-server-safe source-marker evidence without Chrome or `node_modules`;
- server attempt 03.06.2026 showed `product:source:smoke:prod` fails if Chrome executable is absent; this is a browser dependency issue, not a Node package issue;
- `npm run product:source:http:prod` passed 03.06.2026 on production: all four product URLs returned `source=bitrix` and 11 product blocks each;
- `npm run product:content:switch-readiness:prod` checks health `products` scope, rendered `data-product-source=bitrix`, required product blocks, and prints switch/rollback evidence requirements before `products.source=bitrix`;
- `npm run product:content:cache-clear:dry-run` passed 03.06.2026 on target Bitrix/PHP server after PHP lint: source `auto`, TTL `300`, tags `iblock_id_21`, `iblock_id_22`, `iblock_id_23`;
- target server post-cache sequence passed 03.06.2026 in source mode `auto`: readiness, cache clear, strict content check, source HTTP check and public release precheck;
- production source switched to `products.source=bitrix` on 03.06.2026; cache clear, strict content check, source HTTP check and public release precheck passed in source mode `bitrix`;
- rollback remains `products.source=auto|fallback` plus `npm run product:content:cache-clear`;
- future proof/cases model still needs owner evidence and Legal/PM approval;
- target V2 migration must be applied with `--retire-legacy-json` and strict admin-model evidence captured before declaring legacy JSON retired;
- deploy automation for migration deferred until CLI path is tested.

## Не Делаем В Этом Решении

- не отправляем новые structured CRM/upstream fields;
- не меняем form endpoint response shape;
- не добавляем product pricing/schema offers;
- не делаем visual redesign;
- не автоматизируем migration в deploy workflow.
