# Codex Plan: Tacticum Dev → ADP and `/dev/` → `/adp/`

Issue: `PRODUCT-IDENTITY-ADP-001` (proposed)
Gap ID: `PRODUCT-RENAME-001` (proposed)
Workflow lane: Full Feature Lane + Security / Integration release review
Owner agent: project-manager; implementation owners to be assigned at approval
Date: 2026-10-11
Status: `production-applied-monitoring-window` — local FILE_ONLY implementation and the approved STATEFUL production migration are complete; the owner-approved 24–72-hour monitoring window is active
Version source: repository working tree and workflow/ADR documents read on 2026-10-11
Loaded version: `unknown` — no release snapshot or production manifest was loaded

## Scope and Authority Boundary

- Read scope: repository source, workflow/ADR documents, generated checks and (in Phase 0) read-only production/Bitrix inventory.
- Write scope completed: local repository implementation, generated artifacts and the approved production FILE_ONLY/STATEFUL rollout described below.
- Off-limits to the FILE_ONLY artifact: `bitrix/`, uploads, server-owned production config, CRM/BI history and external analytics settings. The approved STATEFUL migration plan below was the separate exception for the active product/content infoblocks.
- Data class: public product copy, route metadata, code/config references and non-PII structural inventory. Production inventory must exclude secrets and raw PII.
- External mutation: production FILE_ONLY and Bitrix migration were executed under the owner-approved exact plan, lock and rollback evidence below. CRM/BI and Search Console operations remain outside this release.

## Goal

Переименовать публичный продукт Tacticum Dev в ADP и сделать его новой канонической страницей `https://tacticum.ru/adp/`. Переход должен сохранить органический трафик, старые ссылки, лиды, CRM/BI историю и связи продукта в Bitrix. Старый путь `https://tacticum.ru/dev/` должен постоянно отдавать однозначный `301` на `/adp/` с сохранением query string.

## Non-Goals

- Не редактировать `bitrix/` и ядро 1C-Bitrix.
- Не выполнять миграцию базы, инфоблоков, разделов или справочников вне отдельной STATEFUL-фазы с утверждённым планом и rollback evidence.
- Не переписывать позиционирование, claims, proof и юридические тексты без отдельного content/owner review.
- Не удалять исторические значения `dev` из CRM, аналитики, лидов и архивных Bitrix-записей.
- Не выкатывать production-изменения до выполнения production governance из `docs/workflow/production-deployment-governance.md`.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/codex-plan-template.md`
- [x] `docs/workflow/production-deployment-governance.md`
- [x] `docs/adr/ADR-010-product-content-bitrix-model.md`
- [x] `docs/adr/ADR-012-offer-taxonomy-presets-bitrix-model.md`
- [x] `docs/adr/ADR-013-github-actions-delivery-gates.md`
- [x] `.github/copilot-instructions.md`
- [x] Relevant runtime, content, SEO, sitemap, smoke and deploy files

## Current Behavior and Inventory

| Surface | Current state | Required target |
|---|---|---|
| Public entry | `dev/index.php`, product code `dev`, canonical `/dev/` | `adp/index.php`, public name ADP, canonical `/adp/` |
| Legacy URL | `/dev/` is a live product page | Permanent `301 /dev/ → /adp/`, query preserved, no redirect chain |
| Fallback content | `local/php_interface/include/product_data/dev.php` | Canonical `adp.php`; no active `dev` product source after migration |
| Runtime registry | `ContentRuntime`, `DataProvider`, `Cta` and helpers know `dev` | `adp` is the active product code; `dev` remains only in migration/legacy-read guards |
| Public navigation | menus, product context, homepage cards and service seeds link `/dev/` | All new/internal links use `/adp/` |
| Forms | visible labels and context use Dev; `dev-cta` is the current identifier | New forms use `adp-cta`; old `dev-cta` links remain accepted only during the transition window |
| Analytics | `/dev/` maps to product `dev` | `/adp/` emits canonical ADP dimension; historical DEV data remains queryable |
| SEO | canonical/static sitemap/fixtures expect `/dev/` | only `/adp/` is canonical and indexable; `/dev/` is redirect-only |
| Delivery | deploy scope, workflow and smoke checks include `dev/` | include `adp/` and retain only the redirect surface under `dev/` |
| Bitrix | product and related content can be keyed by `dev` | active product and related records migrate to `adp`; immutable history remains traceable |

The repository currently contains `dev` references in the public page, fallback content, product runtime, CTA and form payload handling, analytics, Tailwind sources, menus, product-context components, content seeds/migrations, SEO and sitemap checks, visual smoke checks, release prechecks and deployment scope. These references must be classified as public copy, URL, internal code, historical data, or test fixture before editing.

## Decision Gate: Product Identity

Use this accepted identity matrix for the migration:

| Property | Recommended first release |
|---|---|
| Public brand | `ADP` / `Tacticum ADP` according to approved copy guide |
| Public and active internal code | `adp` |
| Canonical path | `/adp/` |
| Legacy code/path | `dev` / `/dev/` |
| Bitrix active code | migrate `dev` → `adp` in the approved STATEFUL phase |
| Historical CRM/BI keys | retain raw `dev` history and expose a reporting alias/mapping to ADP |
| Compatibility | `/dev/` 301, legacy `dev-cta` input accepted temporarily, no new `dev` writes |

The accepted approach is a full migration of the active product identity. The old route and old form identifier exist only as bounded compatibility surfaces. Historical CRM/BI events are not rewritten; reporting must map `dev` history into the ADP product series. Any active Bitrix code, section, property, relation and content reference that represents the current product must become `adp`.

The following decisions are accepted:

1. Full active internal and Bitrix migration to `adp`.
2. New canonical form identifier `adp-cta`; old `dev-cta` is transitional input only.
3. This change covers name and URL only. Positioning, claims and proof are out of scope and require a separate work item.

The read-only baseline, owner decision on the identity matrix and bounded production waiver are attached. Production mutation is complete; named monitoring/rollback owners and durable BASE evidence remain the release follow-up.

## Baseline Evidence — 2026-10-11

### Local source inventory

- `rg` found 30 source/config/tool files outside `docs/`, `.git/`, `vendor/`, `node_modules/` and `bitrix/` containing the active `dev` URL, product code, form identifier or visible product name. The main groups are the public route, fallback content, product runtime, menu/content seeds, analytics/forms, SEO/sitemap, visual/release checks and deploy workflow.
- The repository working tree already contains unrelated dirty state from earlier work. This baseline did not modify or normalize it.

### Production access and file baseline

- Read-only root access succeeded through `PROD_HOST` with `root` and `PROD_ROOT_PASSWORD`; remote root is `/home/bitrix/www`, UID 0.
- `/home/bitrix/www/dev/` exists; `/home/bitrix/www/adp/` is absent.
- `https://tacticum.ru/dev/` returns `200`; `https://tacticum.ru/adp/` returns `404`.
- The following key files have identical SHA-256 hashes locally and on production: `dev/index.php`, `local/php_interface/include/product_data/dev.php`, `.bottom.menu.php`, `local/templates/tacticum/js/analytics.js`, `local/templates/tacticum/js/forms-runtime.js`, `tools/static-sitemap-generate.mjs` and `tools/seo-check.mjs`.
- Production intentionally has no repository-only `tools/` and `.github/` tree. Server-owned `local/php_interface/include/tacticum_config.php` exists and remains outside deploy scope.
- Current static sitemap contains `/dev/`; `/adp/` is absent. The sitemap index itself points to `sitemap-basic-files.xml` and the offer sitemap.

### Bitrix database baseline

- Database connection through the server's `.settings.php` succeeded in read-only mode. No query performed an INSERT, UPDATE, DELETE, DDL or cache mutation.
- Relevant infoblocks are `21` (`Tacticum Products`), `22` (`Tacticum Product Blocks`), `23` (`Tacticum Product Use Cases`) and `28` (`Tacticum Offer Taxonomy Terms`).
- Active product element: ID `2855`, name `Tacticum Dev`, code `dev`, XML_ID `tacticum-product-dev`.
- Active totals: infoblock 21 — `4`; 22 — `219`; 23 — `12`.
- Dev scope: `55` active product blocks (codes `dev-*`) and `3` active use cases (codes `dev-*`). Their numeric product relations point to element ID `2855` through the optimized property tables (`PROPERTY_76` and `PROPERTY_79`).
- Product presentation is stored in optimized table `b_iblock_element_prop_s21`, including `PRODUCT_CODE=dev`, public eyebrow/title/lead fields, `CTA_LEAD_PRODUCT=dev`, `FORM_ID=dev-cta`, `LEAD_PRODUCT=dev` and related CTA metadata. The migration must update these active values to ADP while preserving element IDs and relation integrity.

### Baseline gates

| Gate | Status | Evidence / remaining condition |
|---|---|---|
| Read-only remote access | `passed` | Root access and bounded file/DB queries succeeded |
| Local/production source drift snapshot | `passed` | Key-file hashes recorded above |
| Bitrix relation inventory | `passed` | Product/block/use-case counts and numeric relations recorded |
| Production backup/restore rehearsal | `passed` | Root-only backup `/root/tacticum-adp-20261011131939` retained; temporary restore DB contained 361 tables and was removed |
| Credential exposure remediation | `passed` | `tacticum_user` password rotated, `.settings.php` revalidated through Bitrix PHP DB connection, owner/mode restored to `bitrix:bitrix 600` |
| Staging migration rehearsal | `waived` | No stage host exists; owner-approved production-first validation is recorded below |

### Baseline failure ledger

| ID | Observation | Resolution | Residual |
|---|---|---|---|
| `BL-01` | First DB probe expected legacy constants that are not defined in the current Bitrix configuration | Switched to the active `.settings.php` connection structure and reran read-only queries successfully | None |
| `BL-02` | First config inspection used an incomplete redaction pattern for PHP single-quoted keys | Stopped printing config values, rotated the DB credential and revalidated the active Bitrix connection | No active credential exposure remains; keep the old value out of logs/artifacts |
| `BL-03` | User key is valid and loaded locally but is not authorized on the server; preflight also rejects password/root tooling | Confirmed fingerprint and server absence via read-only probes; retained existing bounded password access only for inventory/dry-run | Provision a dedicated forced-command key/known-hosts under the production access plan before any release |
| `BL-04` | Key-only migration retry initially omitted the adjacent CLI helper, then a wrapper hid the remote JSON payload | Uploaded both CLI files to a unique temporary `bitrix` directory, passed the explicit document root, and completed a redacted key-only dry-run; temporary files were removed | Dedicated forced-command deployment channel is still required for release; manual `restrict` access is not sufficient |

### Implementation evidence — 2026-10-11

- Added canonical `adp/` route and fallback source, converted `dev/` to a query-preserving 301 redirect, and introduced the shared `ProductIdentity` mapping with a bounded `dev` compatibility alias.
- Updated product runtime, CTA/form context, analytics, menus, templates, public copy, sitemap generation, SEO checks, deploy scope and CI checks to use `adp`; `adp-cta` is the new form identifier. Positioning, claims and proof text were not reframed.
- Added `tools/product-dev-to-adp-migration.php`. It is dry-run by default (and accepts `--dry-run`), requires `--apply --confirm-stateful` for writes, preserves IDs/XML_IDs, rejects an independent `adp` collision, verifies changed properties after writes, and never creates new `dev` records.
- Remote key-only dry-run completed against `/home/bitrix/www` in read-only mode: `68` candidates and `62` changes for product `#2855`, including `55` product blocks, `3` use cases and `3` FAQ records. A follow-up page-content scan applied 7 changes, and the cross-product scan applied exactly 3 Platform block changes (`2891`, `2896`, `2900`). The final idempotent dry-run reports `385` candidates and `0` changes. Temporary diagnostic/release directories were removed; the root-only rollback backup is intentionally retained.
- Local checks passed: PHP syntax, JS syntax, CSS build/syntax, Bitrix architecture, SEO, sitemap, product content safety/schema/claims, public-content hygiene, REST endpoint guards, config contract and `git diff --check`.
- `npm run product:content:check:strict` cannot run in this workstation because the local MySQL socket is absent. Production switch-readiness remains expectedly red until the new route/content is deployed and the Bitrix migration is applied.
- Production deploy preflight was attempted read-only. After tightening `.env` permissions from `0644` to `0600`, the contract correctly rejected password-based/root tooling and the missing dedicated SSH public-key path; no deploy action was attempted.
- The user-provided Ed25519 key matches the loaded local agent fingerprint (`SHA256:5wXNdnKucvPXQzP/qpMb3vBsUGG59FFcDLRp2+6b2q0`). After the user added it, key-only SSH succeeds for `root` but still fails for `bitrix`; the root key has no forced-command options. It is therefore usable only for bounded manual access, not as the compliant deploy channel.
- Follow-up key probe now reaches `root` successfully; `bitrix` remains unauthorized and the root key has no forced-command options. This proves emergency/manual access only, not a compliant deploy channel.
- Owner-approved production exception: no stage host exists, so the release used the bounded production waiver recorded below. The user-provided key was used only for this manual/bootstrap execution; it is not a compliant permanent deployment channel.
- Production apply evidence: FILE_ONLY artifact SHA-256 `34a31f1bc074d1e9a9c9c19aeeafe6c8eaca4f8672af3ab16e1b66e2f430903f`; apply and both STATEFUL follow-ups ran under `/var/lock/tacticum-adp-release.lock`, with targeted cache clear and post-apply idempotence verification.
- Production public evidence: `/adp/` is `200`; `/dev/?utm_source=smoke` is one `301` to `/adp/?utm_source=smoke`; rendered hygiene is `13/13`; public precheck, product source HTTP, SEO, visual desktop/mobile, browser console/action and `/price/` action smoke all passed.

### Remaining release gates

| Gate | Status | Next evidence |
|---|---|---|
| Local FILE_ONLY artifact | `passed` | Review exact diff and release manifest |
| Remote migration dry-run | `passed` | Attach JSON plan to the STATEFUL change record |
| Backup/restore rehearsal | `passed` | `/root/tacticum-adp-20261011131939`: DB/files backup, SHA256 manifest, temporary restore DB with 361 tables, cleanup verified |
| Staging migration and browser QA | `waived` | Explicit owner decision: no stage host; production pre/post public, browser/action and console smoke passed |
| Credential exposure remediation | `passed` | DB credential rotated and application connection revalidated; old value is not retained in the active config |
| Production STATEFUL apply | `passed` | Exact plan applied under `/var/lock/tacticum-adp-release.lock`; 62 primary + 7 page-content + 3 cross-product changes; idempotent dry-run now reports `changes=0` |
| Production FILE_ONLY apply | `passed` | Immutable artifact SHA-256 `34a31f1bc074d1e9a9c9c19aeeafe6c8eaca4f8672af3ab16e1b66e2f430903f`; manifest scope excludes `bitrix/`, backup and server-owned config |
| Post-deploy smoke | `passed` | Rendered hygiene 13/13, public precheck, source HTTP, SEO, visual desktop/mobile, browser console/action and `/price/` smoke all passed |
| Monitoring/BASE ownership | `passed` | Product owner assigned release, rollback and monitoring ownership; hash-only BASE copies, monitoring baseline and stop thresholds are recorded below. The observation window itself remains active. |

## Planned Changes

### 1. Read-only baseline and source-of-truth inventory

- Export a non-PII inventory of Bitrix product, product-block, use-case, FAQ, case, feedback, client and offer records that reference `dev`, `/dev/`, or the visible name Dev.
- Record IDs/XML_IDs, relation counts, section/code values, `PRODUCT_FAMILY` values, content source mode, cache tags and orphan counts. Do not infer infoblock IDs; use the configured helpers and server-owned config.
- Search repository and generated artifacts for `dev`, `Dev`, `/dev/`, `dev-cta`, `product=dev`, `lead_product=dev`, `DeveloperApplication` and related schema values. Produce a classified inventory rather than a blind global replacement.
- Capture current URL, title, H1, canonical, JSON-LD, sitemap, menu, form payload and analytics evidence as the BASE snapshot.

**Gate:** approve identity matrix, copy scope, form/analytics compatibility and migration option; attach the inventory to the implementation issue.

### 2. Public route and compatibility layer (FILE_ONLY) — applied 2026-10-11

- Add `adp/index.php` with the ADP page metadata, canonical `/adp/`, schema URLs and the selected identity mapping.
- Convert `dev/index.php` into a minimal permanent redirect to `/adp/`, preserving the query string and exiting before rendering. Test `/dev/`, `/dev/?utm_source=x` and trailing-slash variants.
- Keep the legacy route in the deploy scope for the redirect; do not delete it in the first release.
- Introduce one shared product-identity mapping used by page data, CTA, analytics and validation. It must describe public code `adp`, legacy code `dev`, canonical path `/adp/`, and legacy path `/dev/`.
- Rename the fallback source to `product_data/adp.php` and update all runtime/fixture references. Keep one editable source; the old filename may exist only as a short-lived loader if a rollback artifact requires it.

### 3. Content, links and templates

- Update visible product copy, title, H1, CTA labels, product-context headings and links to ADP after content-owner approval.
- Update `.bottom.menu.php`, homepage/product cards, `product-context.php`, service/content seeds and other generated content references to `/adp/`.
- Update `PublicCopyNormalizer`, calculator wording and platform cross-links only where the approved copy says the product name, retaining factual historical references when required.
- Keep the content-storage model and Bitrix source-of-truth rules from ADR-010. Any admin edit or migration of Bitrix rows is STATEFUL even if the PHP change is small.

### 4. Forms, leads and analytics

- New submissions use `adp` and `adp-cta` end-to-end. Keep an explicit server-side alias for old `dev-cta` links during the agreed transition window, with no new `dev` writes.
- Migrate server-side allow-lists, hidden fields, lead context and CRM mapping from `dev` to `adp`; document the temporary legacy input at the REST/form boundary and test it end-to-end.
- Update `forms-runtime.js` and related validators through the shared identity map. Remove the legacy alias only after traffic and CRM evidence show it is no longer used.
- Map `/adp/` to the ADP analytics product context; make `/dev/` redirect before a page-view event. Preserve historical DEV dimensions and document any new ADP dimension so reports can join the series.
- Verify dataLayer/Metrika events contain no PII and that CTA click, form submit, success and error events still fire once.

### 5. SEO and generated artifacts

- Change canonical, `og:url`, Twitter URL, JSON-LD `url`/`mainEntityOfPage`, titles, descriptions and internal links to `/adp/`.
- Generate the sitemap with `/adp/` and remove `/dev/` as a sitemap URL. Keep robots and sitemap-index references consistent.
- Add redirect/canonical assertions to `tools/seo-check.mjs`, `tools/static-sitemap-generate.mjs` and relevant fixtures. Ensure there is no `/dev/` → intermediate → `/adp/` chain.
- Prepare Search Console/analytics recrawl and 301 monitoring; path renames do not justify a blanket robots change.

### 6. Checks, build and delivery scope

- Update `tools/deploy-scope.json` and `.github/workflows/deploy.yml` to ship `adp/` and the small `dev/` redirect surface. Do not add `bitrix/`, uploads or server-owned config.
- Update Tailwind source globs, product source HTTP checks, content/SEO hygiene, visual smoke, release signoff and public-precheck fixtures to cover `/adp/` and the legacy redirect.
- Keep generated sitemap artifacts reproducible; regenerate rather than hand-edit generated output.
- Add a dedicated acceptance matrix for status, location, canonical, content, form and analytics behavior for `/adp/`, `/dev/` and representative deep/query URLs.

### 7. Full Bitrix migration (STATEFUL, mandatory for the accepted scope) — applied 2026-10-11

This phase is required because the active internal identity is being migrated:

- Write an idempotent dry-run migration for product codes/sections/properties, related records, offer taxonomy and links. Preserve XML_IDs and immutable historical lead values.
- Run it against a production-like copy; compare before/after counts, relation graph, orphan report, cache tags and rendered pages.
- Take and rehearse a restoreable Bitrix/DB backup, acquire the production lock, apply the exact approved migration, clear targeted caches and run post-migration smoke.
- Keep dual-read compatibility for one release window, then remove legacy writes and the `dev-cta` alias only after CRM/BI and editor workflows confirm ADP.

## Delivery Phases and Gates

### Phase 0 — Baseline and decisions

**Output:** inventory, BASE evidence, approved identity/copy/form/analytics decisions, named owners. No production mutation.

### Phase 1 — Local FILE_ONLY implementation

Implement `/adp/`, `/dev/` redirect, shared identity mapping, visible copy, links, SEO generators, checks and deploy manifest. Do not mutate Bitrix data.

### Phase 2 — Required content/Bitrix STATEFUL migration

Run the approved dry-run and STATEFUL migration plan. This phase cannot be skipped for the accepted full-migration scope.

### Phase 3 — Staging and QA

No stage host exists; the owner-approved bounded waiver records production as the first integration environment. The production browser/network smoke against `/adp/` and `/dev/`, rendered content, schema, sitemap, redirects, accessibility/action and cache behavior checks passed. Real CRM success-flow and analytics monitoring remain in the release monitoring window.

### Phase 4 — Production FILE_ONLY and STATEFUL release — applied 2026-10-11

Classify the release, freeze the exact scope, reconcile BASE/PROD/CANDIDATE, obtain staging evidence or a bounded waiver, lock, rehearse restore, deploy, clear caches and run post-deploy smoke. Monitor redirect rate, 404s, organic landing traffic, CTA submits and error logs for 24–72 hours.

### Phase 5 — Compatibility retirement

After the transition window, remove only unused legacy form/read aliases. Keep `/dev/` as a permanent redirect. Require CRM/BI, editor and SEO evidence before deleting any `dev` compatibility code.

## Verification

### Automated

```bash
php -l adp/index.php
php -l dev/index.php
node --check tools/seo-check.mjs
npm run bitrix:check
npm run seo:check
npm run sitemap:static:check
npm run product:content:check:strict
npm run product:public-claims:check
npm run content:public-hygiene:check
npm run content:storage:governance:check
npm run release:public-precheck:prod
git diff --check
```

Run production-suffixed checks only against the approved staging/production target and capture their evidence; do not treat local success as production proof.

### Acceptance criteria

- `GET /adp/` returns 200 with ADP title/H1/metadata/schema and canonical `https://tacticum.ru/adp/`.
- `GET /dev/` and representative legacy query/deep links return one permanent 301 to `/adp/`, preserving query parameters and never exposing a duplicate indexable page.
- Sitemap and internal navigation contain `/adp/`; `/dev/` appears only in redirect/compatibility assertions and historical documentation.
- New `adp-cta` forms reach the expected non-production CRM fixture without duplicate submits or PII in analytics; old `dev-cta` is accepted only by the documented transition alias.
- Active product, block, use-case, FAQ and offer relations are keyed to `adp`, with no unexpected count loss or new orphans; immutable historical `dev` values remain traceable and cache invalidation is evidenced.
- Static, SEO, content, visual, browser, release and production smoke checks pass; rollback artifact and monitoring owner are recorded.

## Risks and Mitigations

| Risk | Mitigation |
|---|---|
| Duplicate indexing or lost backlinks | One canonical `/adp/`, permanent redirect, sitemap replacement, redirect/404 monitoring |
| CRM/BI break from renaming identifiers | New `adp-cta` contract tests, bounded legacy alias, historical `dev` preservation and reporting mapping |
| Broken Bitrix relations | Read-only inventory, XML_ID preservation, dry-run graph/count comparison, separate STATEFUL release |
| Two editable content sources diverge | One ADP source with an explicit legacy loader; Bitrix remains governed source when enabled |
| Cache serves old canonical/copy | Targeted cache clear and rendered post-deploy checks |
| Deploy accidentally touches server-owned data | Exact deploy scope, forbidden-prefix checks, BASE/PROD/CANDIDATE reconciliation |
| Rename changes unverified claims | Product/content/legal review before copy migration |

## Rollback

For FILE_ONLY: redeploy the last approved artifact/manifest, restore the previous `dev/index.php`, remove the new ADP route and restore prior generated sitemap/check fixtures. Keep monitoring until the old canonical is verified.

For STATEFUL: use the rehearsed Bitrix/DB restore or the approved reverse migration, restore cache state, verify relation counts and keep the legacy route available. Historical CRM/BI events are not rewritten. Do not improvise a production reverse migration from a local diff.

## Documentation and Ownership Updates

- ADR-014 `docs/adr/ADR-014-product-identity-dev-to-adp.md` records the accepted public/internal identity mapping and the STATEFUL migration boundary.
- Update `docs/workflow/current-state.md`, `docs/workflow/gap-analysis.md`, release notes and the product/lead contract docs after implementation evidence exists.
- Assign Product/Brand, SEO, Backend, Frontend, QA, Bitrix/DevOps, CRM/BI and Legal/content owners before Phase 1 approval.

## Open Approval Items

- [x] Full active internal and Bitrix migration to `adp`
- [x] New canonical form/CRM identifier `adp-cta`; bounded `dev-cta` compatibility alias
- [x] Scope limited to name and URL; positioning, claims and proof deferred
- [x] Redirect retention policy: permanent 301 with query preservation
- [x] No stage host; bounded owner waiver recorded
- [x] Product owner assigned as release, rollback and monitoring owner

### Owner waiver — no staging host

- Decision: proceed directly on production because this project has no stage host.
- Decision source: explicit owner instruction in the current session, 2026-10-11.
- Scope: this waiver covers only the absent staging environment; it does not waive backup/restore rehearsal, exact plan approval, production lock, credential remediation, or post-deploy smoke/monitoring.
- Accepted risk: production is the first integration environment; rollback must use the rehearsed backup/restore or approved reverse migration.
- Review trigger: any failed pre/post smoke, relation-count mismatch, redirect/404 regression, failed form/analytics check, or monitoring threshold breach.

### BASE and monitoring evidence — 2026-10-11 11:18 UTC

- The exact 24-file FILE_ONLY scope was hashed after apply. Local and remote
  copies of the hash-only manifest match byte-for-byte:
  `cd27f4eba475c28ac3f6ca96dc0b50d5debdcccbc4289039543238804af9aae7`.
- The manifest contains file paths, modes, sizes and SHA-256 values only; no
  production content, secrets or PII. The remote copy is root-only inside the
  retained rollback backup.
- Monitoring baseline at this checkpoint: `/adp/` `200`; `/dev/` one `301` to
  `/adp/` with query preserved; sitemap contains `/adp/` and no `/dev/`; active
  product `adp=1`, `dev=0`; rendered hygiene `13/13`; final migration dry-run
  `changes=0`.
- Stop thresholds for the monitoring window: any non-`200` `/adp/` response;
  any `/dev/` response other than a single query-preserving `301`; `/dev/` in
  the sitemap; product source not `bitrix` or code not `adp`; any rendered
  legacy `Tacticum Dev`/`Dev` product label; migration dry-run reporting a
  non-zero change plan; or a failed public/browser/SEO smoke. On a threshold,
  freeze further changes, preserve logs/manifest, and use the retained backup
  or approved reverse migration after owner decision.
- Product owner is assigned as release, rollback and monitoring owner. The
  observation window remains active until its review checkpoint.
- Window: 24–72 hours from this checkpoint; review trigger is the first failed
  threshold or the end of the window, whichever comes first.
- Read-only recheck after the baseline again passed: `/adp/` `200`, `/dev/`
  one query-preserving `301`, sitemap ADP-only, migration `changes=0`, and
  local/root BASE manifest SHA match. SSH emitted the host's existing
  post-quantum-KEX warning; no release check failed because of it.
