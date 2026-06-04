# Sprint 17 — Product Source And Claims Safety

Дата формирования: 04.06.2026
Статус: in-progress; local schema baseline added
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Roadmap: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`

## Sprint Goal

Сделать product-first слой безопасным для масштабирования: Bitrix product content должен проходить строгую проверку, cache/source governance должен быть явным, а публичные proof/claims не должны выходить за пределы подтверждённой evidence.

## Capacity / Constraints

- Production freeze: нет, но любые runtime/code changes требуют deploy/cache smoke and release evidence.
- Known dependencies: Bitrix product content owners, Legal/Sales proof evidence, DevOps cache/source ownership.
- Agents / roles:
  - PM: public promise and proof split;
  - Content: product content schema and editor checklist;
  - Architect: schema/cache/source decisions and ADR gate;
  - Backend: validators, diagnostics, cache/versioning scope;
  - QA: positive/negative product content fixtures and release gate;
  - DevOps: source switch/cache clear automation decision;
  - Legal/Sales: claim-source approval.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| S17-001 | `CFG-001` Bitrix source fail-fast | Security / Integration | Architect + Backend + QA | P0 | in-progress | Local fail-fast policy documented; target Bitrix strict/runtime checks still required |
| S17-002 | `CFG-002` Product content schema | Full Feature | Architect + Content + Backend | P0 | in-progress | Local schema guard and PHP strict assembled-page schema validation added; target run evidence still required |
| S17-003 | `ARCH-001` Product content lifecycle | Full Feature | PM + Content + Backend + QA | P0 | in-progress | Lifecycle documented in schema contract; owner workflow approval pending |
| S17-004 | `ARCH-003` Product diagnostics surfacing | Full Feature | Backend + QA + Content | P1 | in-progress | Product content checker now prints `schema_issues`; strict JSON evidence validator added; target evidence run pending |
| S17-005 | `ARCH-004` Content ownership matrix | Full Feature | Architect + Content + PM | P1 | planned | Bitrix vs Git fallback vs existing iblocks vs private docs |
| S17-006 | `CFG-003` Cache/source/version governance | Security / Integration | Backend + DevOps | P1 | in-progress | Cache key now includes schema version and source mode; target dry-run evidence pending |
| S17-007 | `ARCH-011` Product source/deploy automation decision | Security / Integration | DevOps + Backend + QA | P2 | planned | Multi-environment ownership |
| S17-008 | `STACK-004` Product schema guard scope | Full Feature | Backend + QA | P1 | in-progress | Local `product:content:safety:check` includes self-test, negative fixture, target evidence validator self-test and seed/fallback check; target PHP evidence pending |
| S17-009 | `STACK-007` Environment matrix | Security / Integration | DevOps + Backend + QA | P2 | planned | Local/staging/prod product source checks |
| S17-010 | `UX-006` Proof readiness is not proof | Full Feature | PM + Sales + Legal | P0 | blocked | Claim-source evidence |
| S17-011 | `CONTENT-002` Claims source matrix | Full Feature | PM + Sales + Legal | P0 | blocked | Approved source, confidence and wording |
| S17-012 | `ARCH-009` Product proof mapping | Full Feature | Content + Sales + SEO | P1 | blocked | Existing cases/offers/FAQ/services evidence |

## Out Of Scope

- Public metrics/logos/certification copy without evidence.
- Product page visual redesign.
- `/agents/` vs `/aiagents/` canonical decision; handled in Sprint 18.
- Structured CRM/upstream fields; handled in Sprint 19.
- CSP enforce; handled in Sprint 22.
- Removing Git fallback before rollback model is proven.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | Conditional | Required if schema, cache/versioning, content lifecycle or ownership model becomes a repeatable architecture pattern |
| Design | Conditional | Only for proof/status UI rules; full UI spec is Sprint 20 |
| QA early | Yes | Required for strict content validation and negative fixtures |
| SEO | Conditional | Product proof mapping affects future proof hub and product SEO |
| Legal/Claims | Yes | Required for all public proof/claims decisions |
| Security / Integration | Yes | Required for source switch, cache/deploy, fail-fast and future private evidence paths |
| Post-deploy smoke | Yes if code/runtime changes | Product source, content schema and rendered product pages |

## Acceptance Criteria

1. Product content lifecycle is documented: draft, review, publish, cache clear, strict check, smoke and rollback.
2. Product schema is defined for `products`, `product_blocks` and `product_use_cases`: required fields, allowed block types, URLs, text limits and safe content rules.
3. Invalid/non-renderable Bitrix product payload cannot pass release checks silently.
4. Product diagnostics are visible in checker/release evidence and include missing blocks, invalid payload and source mode.
5. Cache/source/version strategy is explicit: either versioned key or mandatory cache clear trigger is documented.
6. Ownership matrix states what lives in Bitrix, Git fallback, existing content iblocks and private evidence docs.
7. Claim-source matrix distinguishes public, private/NDA, pending and blocked claims.
8. Product proof map connects products to cases/offers/FAQ/services/evidence status without publishing unapproved claims.
9. Multi-environment source/cache responsibility is assigned for local/staging/prod.
10. No PII, raw payloads, cookies, sessions or private evidence details are stored in docs.

## QA / Smoke Scope

| Scenario | URL/API/Tool | Expected |
|---|---|---|
| Strict product content check | `npm run product:content:check:strict` | All product rows are minimum-renderable; failures are explicit |
| Negative schema fixture | `npm run product:content:schema:negative-test` | Invalid product fixture fails before release |
| Strict JSON evidence validation | `npm run product:content:target-evidence:check -- --file=/path/to/product-content-strict.json --allow-source=bitrix` | Saved target evidence is safe and release-usable |
| Product source HTTP smoke | `/platform/`, `/agents/`, `/dev/`, `/forum/` | `data-product-source=bitrix` and required blocks present |
| Cache clear dry run | `npm run product:content:cache-clear:dry-run` | Expected product tags and cache dir are reported |
| Runtime config | `npm run config:runtime:check` on target | Product source, TTL, explicit/default fields reviewed safely |
| Claim review | proof/claims matrix | No public wording lacks approved source |

## Verification

### Automated

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
npm run product:content:schema:self-test
npm run product:content:schema:negative-test
npm run product:content:target-evidence:self-test
npm run product:content:schema:check
npm run product:content:safety:check
npm run product:content:check:strict
npm run product:content:check:strict:json
npm run product:content:target-evidence:check -- --file=/path/to/product-content-strict.json --allow-source=bitrix
npm run product:content:cache-clear:dry-run
```

If runtime/code changes are implemented:

```bash
npm run product:source:http:prod
npm run release:public-precheck:prod
```

### Manual / Owner Evidence

- Legal/Sales claim-source approval.
- Content owner editor checklist review.
- DevOps cache/source ownership confirmation.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Bitrix editor publishes incomplete product content | Content + QA | Schema validation, strict checks, publish checklist |
| Cache serves stale product blocks after schema/source change | Backend + DevOps | Version strategy or mandatory cache clear runbook |
| Public proof overstates readiness | PM + Legal | Claim-source matrix and blocked wording list |
| Private evidence leaks into docs | PM + Security | Store only safe status, owner and masked reference |
| Guard changes create false blockers | Backend + QA | Positive and negative fixtures, staged rollout |

## Definition Of Done

- `CFG-001`, `CFG-002`, `CFG-003`, `ARCH-001`, `ARCH-003`, `ARCH-004`, `ARCH-009`, `ARCH-011`, `STACK-004`, `STACK-007`, `UX-006`, `CONTENT-002` have documented closure path.
- Owner-blocked claims remain blocked until evidence exists.
- Product source safety is enforceable by checks or explicitly staged with due owner.
- Release/runbook docs are updated if cache/source/schema behavior changes.
- Sprint Review records what is closed, blocked or moved.

## Sprint Review

### Done

- Added `docs/workflow/product-content-schema-v1.json` as machine-readable product content schema baseline.
- Added `tools/product-content-schema-check.mjs` and npm scripts `product:content:schema:self-test` / `product:content:schema:check`.
- Added local negative fixture `tools/fixtures/product-content-schema-invalid/platform.php`, `--expect-fail` checker mode and npm script `product:content:schema:negative-test`.
- Added `tools/product-content-target-evidence-check.mjs`, self-test fixtures and npm scripts `product:content:target-evidence:self-test` / `product:content:target-evidence:check`.
- Local schema guard passed for Git seed/fallback product data: `platform`, `agents`, `dev`, `forum`.
- Added `docs/workflow/product-content-schema-contract.md` with fail-fast policy, local/target guard split and editor lifecycle.
- Updated `product-content-source-switch-runbook.md` so source switch/repeat checks include local schema guard before target Bitrix strict checks.
- Extended `tools/product-content-check.php --strict` to validate live assembled Bitrix product page schema and print `schema_issues` per product.
- Added `product:content:check:strict:json` evidence mode for safe machine-readable target release evidence.
- Added `tacticum_product_content_schema_version()` and included schema version plus source mode in product content cache key; cache dry-run evidence now prints schema version.
- Added product schema version to `config:runtime:check` safe summary.
- Added `product:content:safety:check` and wired it into PR check, deploy lifecycle guard and `release:product-first:prod-check`; it now runs self-test, negative fixture, target evidence validator self-test and positive seed/fallback schema validation.

### Not Done

- Target Bitrix/PHP run of the strengthened `product:content:check:strict` / `product:content:check:strict:json` is still pending because local PHP CLI is unavailable.
- Target strict JSON evidence file and `product:content:target-evidence:check -- --file=... --allow-source=bitrix` run are still pending until target Bitrix/PHP access exists.
- Target `product:content:cache-clear:dry-run` evidence with schema version remains pending.
- Target/live Bitrix negative fixture evidence remains pending; local negative fixture evidence is implemented.
- Claim-source matrix and Legal/Sales proof approvals remain blocked owner evidence.

### Follow-Up

- Extend live Bitrix/PHP checker if owner wants typed validation of `product_blocks` JSON payloads in target environments.
- Collect Legal/Sales claim-source evidence before marking proof/claims items closed.
