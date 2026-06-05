# Product Content Source Switch Runbook

Дата фиксации: 03.06.2026

Status: `completed on production`; runbook retained for repeat switches and rollback.

## Purpose

Этот runbook нужен для безопасного перехода product pages `/platform/`, `/agents/`, `/dev/`, `/forum/` из защитного режима:

```php
'products' => [
    'source' => 'auto',
    'cache_ttl' => 300,
],
```

в целевой режим:

```php
'products' => [
    'source' => 'bitrix',
    'cache_ttl' => 300,
],
```

`auto` уже отдаёт Bitrix content, если он minimum-renderable, но сохраняет Git fallback. `bitrix` отключает fallback-protection, поэтому switch разрешён только после content/admin evidence.

## Preconditions

- Product iblock IDs synced in target `local/php_interface/include/tacticum_config.php`:
  - `products`;
  - `product_blocks`;
  - `product_use_cases`.
- `products.source=auto`, `products.cache_ttl=300` verified before switch.
- Product cache clear helper dry-run passed on target Bitrix/PHP environment:

```bash
npm run product:content:cache-clear:dry-run
```
Dry-run evidence must include source mode, TTL, schema version and managed tags.
- Local Git seed/fallback content passes typed schema guard before migration/update:

```bash
npm run product:content:schema:self-test
npm run product:content:schema:negative-test
npm run product:content:schema:check
npm run product:content:safety:check
```
- Target Bitrix/PHP environment passed:

```bash
npm run product:content:check
npm run product:content:check:strict
npm run product:content:check:strict:json
```

Strict mode validates live assembled product page schema and prints `schema_issues` per product in addition to source, use-case and missing-block evidence. JSON mode is the preferred release evidence artifact.

Save and validate strict JSON evidence:

```bash
npm run product:content:check:strict:json > /tmp/tacticum-product-content-strict.json
npm run product:content:target-evidence:check -- --file=/tmp/tacticum-product-content-strict.json --allow-source=bitrix
```

- Public source marker passed:

```bash
npm run product:source:http:prod
```

- Public release precheck passed:

```bash
npm run release:public-precheck:prod
```

- Switch readiness helper passed:

```bash
npm run product:content:switch-readiness:prod
```

## Admin / Content Review

Перед switch content/admin owner должен проверить в Bitrix:

| Area | Required |
|---|---|
| Products | Four active product records with codes `platform`, `agents`, `dev`, `forum` |
| Hero / CTA | Title, lead, primary CTA, secondary CTA and CTA context are present |
| Blocks | Required TO BE blocks are present for every product: fit guide, architecture, use cases, comparison, procurement, rollout, proof, FAQ, lead CTA |
| Admin-editable V2 model | Editors use scalar/multiple properties and `product_blocks` container/item rows, not manual JSON editing, as primary workflow |
| Use cases | At least three active use cases per product; `product_use_cases` properties are primary over legacy JSON |
| Safe copy | No unapproved numeric claims, guarantees, registry/certification/КИИ/SLA/ПАК claims, customer logos or legal statements |
| Relations | `PRODUCT` relation properties exist on FAQ/cases/offer/services/aiagents where configured |

Allowed evidence in docs/release issue: owner, checked_at, product codes, counts, missing items, safe internal admin report ID. Do not store raw admin screenshots with user/session data.

## V2 Admin Model Migration

05.06.2026 model update: JSON fields are compatibility fallback, not the desired editor workflow. Before declaring product content fully editor-owned, target owners must run:

```bash
php tools/product-content-migration.php --apply --update-seed-content --retire-legacy-json
npm run product:content:check:strict:json > /tmp/tacticum-product-content-strict.json
npm run product:content:target-evidence:check -- --file=/tmp/tacticum-product-content-strict.json --allow-source=bitrix
```

The strict JSON evidence must include `admin_model.v2_schema` with no missing, inactive or mismatched required properties and `admin_model.legacy_json` counters equal to zero. Non-zero `products_json_properties`, `products_active_json_properties`, `product_blocks_json_texts` or `product_use_cases_json_texts` means the admin model is still in compatibility mode and cannot be declared editor-owned. Non-empty `inactive_properties` or `mismatched_properties` means the property exists but is not a valid target editor field because its active flag, type, multiplicity or linked iblock is wrong.

## Switch Steps

1. Update ignored target config `local/php_interface/include/tacticum_config.php`:

```php
'products' => [
    'source' => 'bitrix',
    'cache_ttl' => 300,
],
```

2. Clear product content cache:

```bash
npm run product:content:cache-clear
```

3. Clear Bitrix composite/template cache after config sync if the environment uses composite HTML cache.
4. Run:

```bash
npm run product:content:safety:check
npm run product:content:check:strict
npm run product:content:check:strict:json > /tmp/tacticum-product-content-strict.json
npm run product:content:target-evidence:check -- --file=/tmp/tacticum-product-content-strict.json --allow-source=bitrix
npm run product:source:http:prod
npm run release:public-precheck:prod
```

5. Where Chrome/Chromium is available, also run:

```bash
npm run product:source:smoke:prod
```

6. Confirm public product pages render `data-product-source=bitrix`, SEO/schema/head are valid, and all required product blocks are present.

## Rollback

If any switch check fails:

1. Set `products.source=auto` to restore Git fallback protection, or `fallback` to force Git-owned product data.
2. Clear product content cache:

```bash
npm run product:content:cache-clear
```

3. Clear Bitrix composite/template cache if enabled.
4. Run:

```bash
npm run product:source:http:prod
npm run release:public-precheck:prod
```

5. Keep `products.source=auto` until content/admin issue is fixed and strict checks pass again.

## Production Switch Evidence — 03.06.2026

Production `products.source=bitrix` switch completed on 03.06.2026.

```json
{
  "environment": "production",
  "checked_at": "2026-06-03T00:00:00+03:00",
  "source_before": "auto",
  "source_after": "bitrix",
  "product_content_cache_clear": {
    "status": "passed",
    "source_mode": "bitrix",
    "cache_ttl": 300,
    "schema_version": "v1",
    "managed_tags": ["iblock_id_21", "iblock_id_22", "iblock_id_23"]
  },
  "product_content_check_strict": {
    "status": "passed",
    "source_mode": "bitrix",
    "schema_version": "v1",
    "products": {
      "platform": {"source": "bitrix", "use_cases": 3, "missing_blocks": [], "schema_issues": 0},
      "agents": {"source": "bitrix", "use_cases": 3, "missing_blocks": [], "schema_issues": 0},
      "dev": {"source": "bitrix", "use_cases": 3, "missing_blocks": [], "schema_issues": 0},
      "forum": {"source": "bitrix", "use_cases": 3, "missing_blocks": [], "schema_issues": 0}
    }
  },
  "product_source_http": {
    "status": "passed",
    "pages": ["/platform/", "/agents/", "/dev/", "/forum/"],
    "expected_source": "bitrix",
    "blocks_per_page": 11
  },
  "release_public_precheck": "passed",
  "manual_release_gates": "closed separately in docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json on 2026-06-04",
  "rollback": "products.source=auto and npm run product:content:cache-clear"
}
```

## Production V2 Retirement Evidence — 05.06.2026

Production admin-editable V2 retirement completed on 05.06.2026 after deploying the current codebase and running the one-time data migration on the production Bitrix DB:

```bash
php tools/product-content-check.php --strict --json > /tmp/tacticum-product-content-before.json || true
php tools/product-content-migration.php --apply --update-seed-content --retire-legacy-json
php tools/product-content-check.php --strict
php tools/product-content-check.php --strict --json > /tmp/tacticum-product-content-after.json
node ./tools/product-content-target-evidence-check.mjs --file=/tmp/tacticum-product-content-after.json --allow-source=bitrix
php tools/product-content-cache-clear.php
php tools/product-content-check.php --strict --json > /tmp/tacticum-product-content-after-cache-clear.json
node ./tools/product-content-target-evidence-check.mjs --file=/tmp/tacticum-product-content-after-cache-clear.json --allow-source=bitrix
```

Safe summary:

```json
{
  "environment": "production",
  "checked_at": "2026-06-05T00:00:00+03:00",
  "source_mode": "bitrix",
  "configured_source": "bitrix",
  "fallback_allowed": false,
  "schema_version": "v1",
  "admin_model": {
    "v2_schema": {
      "products": {
        "required_properties": 22,
        "missing_properties": [],
        "inactive_properties": [],
        "mismatched_properties": []
      },
      "product_blocks": {
        "required_properties": 32,
        "missing_properties": [],
        "inactive_properties": [],
        "mismatched_properties": []
      },
      "product_use_cases": {
        "required_properties": 8,
        "missing_properties": [],
        "inactive_properties": [],
        "mismatched_properties": []
      }
    },
    "legacy_json": {
      "products_json_properties": 0,
      "products_active_json_properties": 0,
      "product_blocks_json_texts": 0,
      "product_use_cases_json_texts": 0
    }
  },
  "products": {
    "platform": {"status": "ok", "source": "bitrix", "use_cases": 3, "schema_issues": 0},
    "agents": {"status": "ok", "source": "bitrix", "use_cases": 3, "schema_issues": 0},
    "dev": {"status": "ok", "source": "bitrix", "use_cases": 3, "schema_issues": 0},
    "forum": {"status": "ok", "source": "bitrix", "use_cases": 3, "schema_issues": 0}
  },
  "cache_clear": "passed",
  "target_evidence_check": "passed",
  "product_content_switch_readiness_prod": "passed",
  "product_source_http_prod": "passed",
  "release_public_precheck_prod": "passed",
  "seo_check_prod": "passed",
  "browser_automation_on_server": "skipped: Chrome/Chromium executable is not installed on production server"
}
```

Production `seo:check:prod` passed after `tools/seo-check.mjs` was made compatible with production checkouts that do not include `.github` / full `docs` and with older Node runtimes without global `fetch`.

Follow-up production target audit with the strengthened checker also passed on 05.06.2026. It confirmed that all required V2 properties are present, active, have the expected property type and multiplicity, and element-link properties point to the target product iblocks (`products=#21`, `product_blocks=#22`, `product_use_cases=#23`). This makes the current product iblock structure, data and runtime model the target state; remaining browser automation evidence is environment-bound because Chrome/Chromium is absent on the production server.

## Evidence Template

```json
{
  "environment": "production",
  "checked_at": "2026-06-03T00:00:00+03:00",
  "checked_by": "replace-with-owner",
  "source_before": "auto",
  "source_after": "bitrix",
  "product_content_check": "passed",
  "product_content_check_strict": "passed",
  "product_content_check_strict_json": "passed",
  "product_content_cache_clear": "passed",
  "product_schema_version": "v1",
  "product_source_http": "passed",
  "release_public_precheck": "passed",
  "content_review": {
    "products": ["platform", "agents", "dev", "forum"],
    "use_cases_per_product": 3,
    "required_blocks_present": true,
    "safe_copy_review": "passed",
    "internal_report_id": "replace-with-safe-report-id"
  },
  "rollback": "products.source=auto and npm run product:content:cache-clear"
}
```
