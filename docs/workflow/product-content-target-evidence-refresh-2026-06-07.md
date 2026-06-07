# Product Content Target Evidence Refresh — 2026-06-07

Дата: 07.06.2026
Статус: evidence refresh / not closure
Workflow lane: Security / Integration + Full Feature
Related issue: `PTC-WP-01`

## Purpose

Документ фиксирует безопасное no-PII обновление evidence по `PTC-WP-01` после очередного product/content deploy cycle. Это не owner approval и не закрытие `pending-target-evidence`: target Bitrix/PHP CLI evidence still must be collected on production or another authorized Bitrix target.

## Scope

Included:

- local product content safety guard;
- production HTTP product source smoke;
- production public release precheck;
- local attempt to run target-only cache dry-run, recorded as environment limitation.

Not included:

- no product copy changes;
- no Bitrix row changes;
- no cache clear;
- no claims/proof changes;
- no form, CRM, analytics or SEO behavior changes;
- no raw content, PII, cookies, sessions, tokens or admin data.

## Evidence Summary

| Command / Evidence | Environment | Result | Notes |
|---|---|---|---|
| `npm run product:content:safety:check` | local | Passed | Schema self-test passed; negative fixture failed as expected with 7 errors; target-evidence and cache-clear evidence self-tests passed; product schema check passed for 4 products; public claims check scanned 11 files; public hygiene check scanned 11 files. |
| `npm run product:source:http:prod` | HTTP production | Passed | `/platform/`, `/agents/`, `/dev/`, `/forum/` returned `status=200`, `source=bitrix`, `faq_source=iblock`, `proof_source=iblock`, `blocks=11`. |
| `npm run release:public-precheck:prod` | HTTP production | Passed | Health config, product source checks, public Metrika tag, unauthenticated Bitrix admin surface check and legacy alias 405/deprecation checks passed. |
| `npm run product:content:cache-clear:dry-run:json` | local | Not applicable / target-only | Failed with `Mysql connect error [localhost]: (2002) No such file or directory`, confirming this evidence must be collected on target Bitrix/PHP, not from the local workspace. |

## Remaining Target Evidence

`PTC-WP-01` remains `pending-target-evidence` until the commands below are run on an authorized target Bitrix/PHP environment and the safe JSON artifacts validate.

Recommended target sequence:

```bash
npm run product:content:check:strict:json > /tmp/tacticum-product-content-strict-2026-06-07.json
npm run product:content:target-evidence:check -- --file=/tmp/tacticum-product-content-strict-2026-06-07.json --allow-source=bitrix

npm run product:content:cache-clear:dry-run:json > /tmp/tacticum-product-content-cache-clear-dry-run-2026-06-07.json
npm run product:content:cache-clear:evidence:check -- --file=/tmp/tacticum-product-content-cache-clear-dry-run-2026-06-07.json

npm run config:runtime:check
```

Optional HTTP checks after target CLI evidence:

```bash
npm run product:source:http:prod
npm run release:public-precheck:prod
```

## Closure Rule

Do not mark `PTC-WP-01`, `CFG-001`, `CFG-002`, `CFG-003`, `ARCH-001`, `ARCH-003` or `STACK-004` closed from this document alone.

Closure still requires:

- strict live Bitrix product content JSON evidence from target;
- validated strict JSON evidence with `--allow-source=bitrix`;
- target product cache-clear dry-run JSON evidence;
- validated cache-clear evidence;
- owner-approved target negative fixture or simulated target evidence path;
- no raw content or PII in committed evidence.
