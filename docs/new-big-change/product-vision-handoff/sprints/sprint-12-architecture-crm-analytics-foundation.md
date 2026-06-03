# Sprint 12 - Architecture, CRM And Analytics Foundation

Suggested duration: 1-2 weeks

Status: in-progress / Bitrix product content foundation implemented, production evidence pending

## Sprint Goal

Зафиксировать технологические решения, которые должны быть понятны до масштабирования TO BE реализации: product content ownership, component boundary, CRM/upstream qualification and product analytics evidence model.

This sprint moved from decision-first to implementation after owner decisions were accepted on 02.06.2026. Code changes are limited to product content foundation and do not change form/upstream payload contracts.

## Workflow Lane

Full Feature Lane with Architecture, Backend, QA and Analytics review. Security / Integration lane is required for payload changes.

## Source Decisions And Gaps

| Decision | Existing gaps |
|---|---|
| `D-10` Product data/component architecture v1 | `ARCH-001`, `ARCH-002` |
| `D-11` CRM/upstream qualification | `ARCH-003`, `CJM-006` |
| `D-12` Product analytics and Metrika evidence | `ARCH-004`, `REL-004` |

Related gaps: `REL-003`, `ARCH-007`, `ARCH-008`.

## Inputs

- Sprint 10 CTA taxonomy.
- Sprint 11 component/migration decisions.
- `../13-architecture-components-stack-target.md`
- `../19-phase-3-architecture-integration-decision-pack.md`
- `../27-post-challenge-architecture-components-stack.md`
- `../28-post-challenge-decision-backlog.md`
- `../../../workflow/lead-form-contract.md`
- `../../../workflow/release-signoff-gates.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S12-001 | Approve Bitrix product content model with Git fallback | Architect + Dev + Content | P2 | implemented locally / production evidence pending |
| S12-002 | Approve current `product_page_blocks/*.php` partial taxonomy or open ADR for local component/preview system | Architect + Frontend | P2 | planned |
| S12-003 | Decide component promotion criteria after Sprint 11 design output | Architect + Frontend + QA | P2 | planned |
| S12-004 | Confirm current `lead_*` canonical profile + `task` fallback is enough for v1 Sales routing or scope structured fields | Backend + PM + QA + Sales | P1 | accepted for v1 fallback |
| S12-005 | If structured fields are needed, define Security / Integration task, field names, types and fallback rule | Backend + QA + Security | P1 | planned |
| S12-006 | Approve product analytics goal map and no-PII evidence rules | PM + Analytics + QA | P2 | planned |
| S12-007 | Update release sign-off expectations for CRM/upstream and Metrika evidence | QA + PM | P1 release | planned |

## Out Of Scope

- Deploy automation for product content migration before CLI path is tested.
- Removing `task` fallback.
- Sending new structured upstream fields without contract approval.
- Adding analytics params with PII or free-form text.
- Introducing Storybook or SPA tooling by default.

## Deliverables

- Product content ownership decision: Bitrix SoT with `auto|bitrix|fallback` runtime source and Git fallback.
- Product content runtime cache and invalidation rule for Bitrix source mode.
- Product config health scope for post-deploy validation.
- Component boundary decision.
- Component promotion criteria.
- Lead qualification v1 decision: fallback accepted or structured field scope opened.
- Product analytics/Metrika goal map.
- Updated QA/release evidence requirements.
- Accepted ADR-010 and CLI migration/bootstrap script.
- Runtime product content checker for post-migration validation.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | Conditional | Required for Bitrix/hybrid content model or formal component architecture |
| Security / Integration | Conditional | Required for structured fields, new endpoints or data policy changes |
| QA early | Yes | Required for forms, analytics and release evidence |
| Analytics | Yes | Required for Metrika goals and no-PII proof |
| Design | Conditional | Required if component boundary affects TO BE components |

## Acceptance Criteria

1. Bitrix product content path is explicitly decided in ADR-010.
2. Product partials remain accepted for v1 or component architecture ADR is opened.
3. `data-product-block` locator contract is preserved unless migration is scoped.
4. Current `task` fallback remains accepted for v1; structured fields stay future Security / Integration scope.
5. No new upstream payload fields are assumed without contract update.
6. Product analytics goals are mapped without PII.
7. Release sign-off has evidence slots for CRM/upstream and Metrika.

## Verification

- `npm run product:gaps:check`
- `npm run design:handoff:check` if migration map or design handoff changes
- `npm run product:content:check` in Bitrix/PHP environment after migration/config sync
- `npm run product:content:check:strict` before switching source mode to `bitrix`
- `npm run gaps:known`
- Manual review of `lead-form-contract.md` if payload decisions change

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| CMS/hybrid model starts before proof governance | Architect + Content | Keep Git-owned core facts for v1 |
| Structured fields are added without upstream support | Backend + QA | Keep fallback until staging/production evidence |
| Analytics expands into PII | PM + Analytics + QA | Controlled slugs only, no raw text |
| Component system overbuilt too early | Architect + Frontend | Promote only stateful/reused/preview-critical blocks |
| Bitrix content migration overwrites editor changes | Architect + Dev | Migration is create-only by default; updates require `--update-seed-content` |

## Sprint Review

### Done

- Owner decisions accepted: Bitrix content model is target SoT; `product_data/*.php` remains fallback/seed.
- ADR-010 added for product content Bitrix model.
- Config example extended with product iblock keys and `products.source`.
- Runtime product source flag selected: `auto|bitrix|fallback`, default `auto`.
- Product content cache added with `products.cache_ttl`, product iblock managed tags and add/update/delete/property invalidation handlers.
- `health_config.php` now validates `products` scope without exposing config values.
- PHP CLI migration/bootstrap script added with dry-run default, `--apply` and `--update-seed-content`.
- Product content checker added for minimum-renderable Bitrix records, TO BE block coverage, use cases and product relation properties.
- Manual local migration/config sync completed outside this shell; local ignored config now contains created product iblock IDs.
- 03.06.2026 target Bitrix/PHP environment passed `npm run product:content:check` and `npm run product:content:check:strict`: all four products resolve from Bitrix, each has three use cases and no missing TO BE blocks.
- 03.06.2026 production `npm run seo:smoke` passed: product pages `/platform/`, `/agents/`, `/dev/`, `/forum/` are `seo=ok` and `blocks=ok` on desktop/mobile; manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-03T06-07-04-201Z/manifest.json`.
- Product renderer now exposes `data-product-source`; `npm run product:source:smoke:prod` verifies rendered `source=bitrix` with Chrome, and `npm run product:source:http:prod` verifies the same marker on production servers without Chrome or `node_modules`.
- 03.06.2026 `npm run product:source:http:prod` passed on production: `/platform/`, `/agents/`, `/dev/`, `/forum/` returned `source=bitrix` and 11 product blocks each.
- 03.06.2026 `npm run release:public-precheck:prod` passed without creating leads: health/config scopes, product source marker, public Metrika tag, unauthenticated Bitrix admin surface and legacy alias headers are publicly healthy.

### Not Done

- Deploy automation for product migration is intentionally not enabled.
- Structured CRM/upstream fields remain out of v1 scope.
- Metrika goal evidence remains an external release gate.
- Bitrix admin/content review is still pending before switching source mode to `bitrix`.
- Manual success-flow, authenticated Metrika goal visibility and staff upstream/CRM confirmation remain owner-evidence gates; public precheck does not replace them.

### Follow-Up

- Keep created IDs and `products.source=auto`, `products.cache_ttl=300` synced in `local/php_interface/include/tacticum_config.php` on every target environment.
- Optional design/QA handoff: run `npm run product:block-previews:prod` when block-level screenshots are needed.
- Switch `products.source` from `auto` only after Bitrix admin/content review.
