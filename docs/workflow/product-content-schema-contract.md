# Product Content Schema Contract

Дата: 04.06.2026
Статус: Sprint 17 local baseline

## Purpose

Этот contract фиксирует typed schema baseline для product content Platform / Agents / Dev / Forum. Он закрывает локальную часть Sprint 17: Git seed/fallback data больше не считается валидным только потому, что PHP array синтаксически корректен.

## Source Of Truth

| Artifact | Role |
|---|---|
| `docs/workflow/product-content-schema-v1.json` | Machine-readable schema contract |
| `tools/product-content-schema-check.mjs` | Local Node checker for Git seed/fallback data |
| `tools/fixtures/product-content-schema-invalid/platform.php` | Local negative fixture proving schema guard fails invalid product data |
| `tools/product-content-target-evidence-check.mjs` | Local validator for safe JSON evidence from target `product:content:check:strict:json` |
| `local/php_interface/include/product_data/*.php` | Git-owned fallback and seed source for Bitrix migration |
| `tools/product-content-check.php --strict` | Target Bitrix/PHP strict checker for live Bitrix product content, required blocks, relations and assembled page schema |
| `docs/workflow/product-content-source-switch-runbook.md` | Source switch / rollback process |

## Runtime Cache Identity

Product content cache key includes:

- schema version from `tacticum_product_content_schema_version()`;
- source mode from `products.source`;
- configured product iblock IDs: `products`, `product_blocks`, `product_use_cases`.

Current schema version: `v1`.

Changing schema requirements or source mode should therefore create a new cache key. Cache clear is still required for publish/switch/rollback discipline because managed tags and composite/template caches may also be involved.

## Guard Commands

Local, no PHP/Bitrix required:

```bash
npm run product:content:schema:self-test
npm run product:content:schema:negative-test
npm run product:content:target-evidence:self-test
npm run product:content:schema:check
npm run product:content:safety:check
```

Target Bitrix/PHP environment:

```bash
npm run product:content:check:strict
npm run product:content:check:strict:json
npm run product:content:target-evidence:check -- --file=/path/to/product-content-strict.json
npm run product:source:http:prod
npm run release:public-precheck:prod
```

## What The Local Schema Guard Validates

The local guard parses `local/php_interface/include/product_data/*.php` and validates:

- required product codes: `platform`, `agents`, `dev`, `forum`;
- top-level hero and CTA fields;
- required blocks: `fit_guide`, `architecture`, `use_cases`, `comparison`, `procurement`, `rollout`, `proof`, `faq`, `cta`;
- `hero_cards` shape;
- fit guide columns `fits`, `not_fits`, `start`;
- content sections and cards;
- architecture layers;
- at least 3 pilotable use cases per product with trigger, owner, input, output and limitation;
- comparison columns;
- procurement items and safe note text;
- rollout steps;
- proof readiness items;
- FAQ items;
- CTA scenario options and lead context;
- safe internal/HTTPS URL prefixes for schema-controlled links.

The local negative fixture guard validates the validator path itself against `tools/fixtures/product-content-schema-invalid/platform.php`. It intentionally expects failures for:

- empty product title;
- unsafe `http://` schema-controlled URL;
- too few use cases;
- CTA `lead_context.lead_product` mismatch.

## What The Target Strict Checker Validates

On target Bitrix/PHP environments `npm run product:content:check:strict` now validates:

- product iblock IDs and source mode;
- minimum-renderable product data;
- required TO BE blocks and use cases;
- assembled live product page schema using the same v1 contract shape;
- relation properties on FAQ/cases/offer/services/aiagents;
- per-product summary including `schema_issues` count.

For release evidence use `npm run product:content:check:strict:json`. The JSON output is safe to store in release evidence because it contains product codes, counts, source mode, schema version, iblock IDs, warnings and error codes/messages only; it does not include raw content, admin session data, contact data or request payloads.

After saving target JSON evidence, validate it locally or on target with:

```bash
npm run product:content:target-evidence:check -- --file=/path/to/product-content-strict.json --allow-source=bitrix
```

The validator requires `success=true`, `strict=true`, schema version `v1`, configured product iblocks, `platform` / `agents` / `dev` / `forum` rows with `status=ok`, `source=bitrix`, no missing blocks, `schema_issues=0`, empty errors and no raw/PII-like evidence keys.

## What The Local Schema Guard Does Not Validate

The local guard does not replace:

- live Bitrix iblock presence;
- live Bitrix `product_blocks` raw JSON diagnostics;
- live `product_use_cases` element/property values;
- relation properties on FAQ/cases/offer/services/aiagents;
- rendered HTML/source marker smoke;
- Legal/Sales proof or claims approval.

Those checks remain target environment gates through `product:content:check:strict`, `product:source:http:prod`, release precheck and owner evidence. Invalid or partial Bitrix block JSON is expected to surface through missing blocks, typed assembled-page schema issues or rendered block smoke.

## Fail-Fast Policy

For product content changes, the release sequence should fail before public smoke when:

1. `product:content:safety:check` fails on validator self-test, local negative fixture, target-evidence validator self-test or Git seed/fallback data.
2. `product:content:check:strict` fails on target Bitrix content.
3. `product:content:check:strict:json` reports `success=false` or any product row with `schema_issues > 0`.
4. `product:content:target-evidence:check` rejects saved strict JSON evidence.
5. Public product pages do not render expected `data-product-source=bitrix`.
6. Required product blocks are missing from rendered HTML.
7. Claim-source review marks a public proof/packaging statement as blocked.

`products.source=bitrix` remains an operationally strict mode. If live Bitrix content is invalid, the safe rollback is:

```bash
# target config
'products' => [
    'source' => 'auto', // or fallback
    'cache_ttl' => 300,
],

npm run product:content:cache-clear
```

## Editor / Content Lifecycle

| Stage | Owner | Required Evidence |
|---|---|---|
| Draft | Content | Product code, block type and required payload fields present |
| Review | PM + Content | Product narrative, CTA and pilot use cases reviewed |
| Claims review | PM + Sales + Legal | Public/private/blocked claim status confirmed |
| Pre-publish | QA + Backend | Local schema guard and target strict check passed |
| Publish | Content + DevOps | Cache clear and source/check commands executed |
| Post-publish | QA | Rendered source, required blocks and release precheck passed |
| Rollback | DevOps + Backend | `products.source=auto|fallback` plus product cache clear |

## ADR Trigger

Open or update ADR if this contract changes:

- product block type taxonomy;
- required block set;
- Bitrix iblock/property model;
- cache key/versioning;
- runtime fail-fast behavior;
- structured proof/status model;
- private/public evidence workflow.

## Current Limitations

- The Node checker validates Git seed/fallback data, not live Bitrix content.
- The local negative fixture proves schema failure behavior without PHP/Bitrix; live Bitrix negative evidence still requires target environment access or owner-approved target fixture process.
- PHP CLI is still required on target Bitrix environments for authoritative live content checks.
- Proof/status and public claims remain owner-blocked until Legal/Sales evidence is approved.
