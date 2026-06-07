# Content Language / Storyline Challenge Issue Backlog — 2026-06-07

Дата: 07.06.2026
Статус: issue backlog draft / `CLS-WP-01` closed by production rendered evidence / owner approvals pending for remaining work packages

Source register: `docs/workflow/content-language-storyline-challenge-gap-analysis-2026-06-07.md`
Roadmap: `docs/workflow/content-language-storyline-challenge-roadmap-2026-06-07.md`
Glossary: `docs/workflow/content-language-storyline-public-glossary-2026-06-07.md`

## Purpose

Этот документ переводит `CLS-*` gaps into backlog-ready work packages. Его можно использовать для ручного создания задач в трекере. Он не является owner approval and does not close any gap.

## Start Policy

| Policy | Meaning |
|---|---|
| `fast-fix-allowed` | Можно делать ограниченный фикс без нового продукта/контракта; smoke required |
| `owner-review-required` | Можно уточнять docs/copy proposals; implementation waits for PM/Content/Sales/SEO/Legal approval |
| `blocked-claims-evidence` | Нельзя переписывать/публиковать claims until evidence/legal/sales approval exists |
| `guard-scope-required` | Нужен отдельный scope for automation/checks before or with implementation |

## Backlog Index

| Issue | Status | Start policy | Priority | Owners | Gap IDs | Objective |
|---|---|---|---:|---|---|---|
| `CLS-WP-01` | closed | `fast-fix-allowed` + `guard-scope-required` | P0 | Backend + Content + QA | `CLS-001`, `CLS-012` | Remove public internal labels and define recurrence guard/checklist |
| `CLS-WP-02` | pending-owner-review | `owner-review-required` | P1 | PM + Content + Sales + SEO | `CLS-002`, `CLS-011` | Approve Russian-first glossary, voice rules and forbidden public terms |
| `CLS-WP-03` | pending-owner-review | `owner-review-required` | P1 | PM + UX + Content + Sales + SEO | `CLS-003`, `CLS-010` | Approve global storyline and homepage narrative rewrite |
| `CLS-WP-04` | pending-owner-review | `owner-review-required` | P1 | Content + Architect + Sales + PM | `CLS-004`, `CLS-002` | Rewrite product pages into public-readable Russian-first product copy |
| `CLS-WP-05` | pending-owner-review | `owner-review-required` | P1 | PM + Sales + UX + SEO + Frontend | `CLS-005`, `CLS-010` | Reframe `/price/` as implementation team/budget route while preserving team builder contracts |
| `CLS-WP-06` | pending-owner-review | `owner-review-required` | P1 | PM + SEO + Content + Sales | `CLS-006`, `CLS-009` | Clarify `/aiagents/` as demo/prototype route for Agents, not competing product |
| `CLS-WP-07` | blocked | `blocked-claims-evidence` | P0 | PM + Sales + Legal + Content + SEO | `CLS-007`, `CLS-008` | Build claims/evidence matrix and rewrite offer detail risk/reason copy safely |
| `CLS-WP-08` | pending-owner-review | `owner-review-required` | P2 | Content + PM + Legal + QA | `CLS-009`, `CLS-010`, `CLS-003` | Inventory and clean legacy FAQ/global copy that conflicts with product-first story |

## Issue Details

### CLS-WP-01 — Public Label Leak And Guard

Workflow lane: Fast Fix or Full Feature depending on guard scope.
Priority: P0.

Affected areas:

- `local/lib/Tacticum/Product/ContentBlockMapper.php`
- `local/php_interface/include/product_page_blocks/fit_guide.php`
- `local/php_interface/include/product_page_blocks/use_cases.php`
- `local/php_interface/include/product_page_blocks/procurement.php`
- `tools/public-content-hygiene-check.mjs`
- `tools/content-storage-page-content-seed.php`
- `local/components/tacticum/chat.surface/component.php`
- `local/components/tacticum/price.page/templates/.default/parts/calculator.php`
- Bitrix `product_blocks` / `product_use_cases` content rows if source data needs cleanup
- product rendered URLs: `/platform/`, `/agents/`, `/dev/`, `/forum/`
- price rendered URL: `/price/`

Acceptance criteria:

- Product pages do not render `Product fit`, `fits`, `not_fits`, `start`, `Use cases`, `Security / procurement` as public labels.
- Russian labels are used: `Когда подходит продукт`, `Подходит, если`, `Не подходит, если`, `С чего начать`, `Сценарии применения`, `Безопасность и закупка` or approved variants.
- Recurrence is covered by either automated source/rendered guard or explicit release checklist.
- No unrelated product content, route or payload changes.

Verification:

```bash
git diff --check
npm run content:public-hygiene:self-test
npm run content:public-hygiene:check
npm run content:public-hygiene:rendered:self-test
npm run content:public-hygiene:rendered:prod
npm run content:public-hygiene:rendered:prod:json
npm run product:content:safety:check
npm run seo:check
```

Implementation note 07.06.2026: local fallback/source labels, mapper normalization, exact-phrase runtime normalization for old Bitrix product/page-content rows and `tacticum:chat.surface` quick-reply normalization are implemented; `content:public-hygiene:*` guards recurrence. Production `npm run content:public-hygiene:rendered:prod` passed after deploy/cache refresh on 13 pages with `issues_found=0` at `2026-06-07T06:34:56Z`. The JSON evidence from `content:public-hygiene:rendered:prod:json` is stored in the `content-public-hygiene` release sign-off gate.

### CLS-WP-02 — Russian-First Glossary And Voice Rules

Workflow lane: Full Feature discovery / content governance.
Priority: P1.

Affected areas:

- `docs/workflow/content-language-storyline-public-glossary-2026-06-07.md`
- Bitrix page-content/product-content editor rules
- future content rewrite issues

Acceptance criteria:

- PM/Content/Sales/SEO approve the public term replacement table.
- Product names remain allowed, but generic English/internal terms require Russian alternatives.
- `AI` vs `ИИ` rule is explicit.
- Rules distinguish public copy, technical detail, SEO metadata and internal docs.

Verification:

```bash
git diff --check
```

### CLS-WP-03 — Global Storyline And Homepage

Workflow lane: Full Feature.
Priority: P1.

Affected areas:

- `/`
- footer/company one-liner
- top-level routing copy
- possible page-content rows for homepage

Acceptance criteria:

- Homepage first screen answers what Tacticum is in one stable promise.
- Product/commercial routes are presented hierarchically, not as unrelated company identities.
- Copy leads from business task to product/pilot/estimate/team/demo.
- No route/canonical/metadata changes without SEO approval.

Verification:

```bash
git diff --check
npm run seo:check
```

### CLS-WP-04 — Product Page Copy Rewrite

Workflow lane: Full Feature.
Priority: P1.

Affected areas:

- `/platform/`
- `/agents/`
- `/dev/`
- `/forum/`
- product data/content rows and product page blocks

Acceptance criteria:

- Public layer is Russian-first and buyer-readable.
- Technical terms remain only where useful and explained.
- Product boundaries remain aligned with approved taxonomy.
- Use cases remain cautious and do not introduce unsupported claims.

Verification:

```bash
git diff --check
npm run seo:check
```

Product content source/Bitrix checks should be added if runtime content source or mapper changes.

### CLS-WP-05 — `/price/` Reframing

Workflow lane: Full Feature.
Priority: P1.

Affected areas:

- `/price/` page-content rows
- `local/templates/tacticum/components/bitrix/news.list/price/parts/catalog.php` if static labels change
- team preset/rates visible copy

Acceptance criteria:

- First screen and workstream copy frame `/price/` as team composition and budget orientation for pilot/implementation.
- Hourly rates remain available but secondary.
- Team builder payload, `workers_json`, presets and staff-order endpoint contracts are unchanged unless separate issue.
- Mobile/team-builder behavior is not changed in a copy-only issue.

Verification:

```bash
git diff --check
npm run seo:check
```

If JS/template behavior changes, add current `/price/` browser smoke.

### CLS-WP-06 — `/agents/` vs `/aiagents/` Copy Boundary

Workflow lane: Full Feature.
Priority: P1.

Affected areas:

- `/agents/`
- `/aiagents/`
- footer/menu labels if owner-approved
- SEO metadata only with SEO approval

Acceptance criteria:

- `/agents/` remains enterprise product page for corporate assistants.
- `/aiagents/` clearly reads as demo/prototype stand for one scenario.
- Telegram bot wording does not imply it replaces the full Agents product.
- No redirect/canonical/sitemap change without SEO gate.

Verification:

```bash
git diff --check
npm run seo:check
```

### CLS-WP-07 — Proof/Claims And Offer Detail Cleanup

Workflow lane: Full Feature with Legal/Sales gate.
Priority: P0.
Start policy: blocked until claim evidence model exists.

Affected areas:

- product proof blocks
- cases/feedback/clients public-render copy
- `/offer/` catalog/detail
- FAQ claims
- `local/templates/tacticum/components/bitrix/news.detail/offer/parts/risks.php`
- `local/templates/tacticum/components/bitrix/news.detail/offer/parts/reasons.php`

Acceptance criteria:

- Claims are classified as public-approved, private-by-request, benchmark, example or blocked.
- Offer detail uses neutral decision/checklist language, not fear framing.
- Synthetic examples are not presented as customer proof.
- No new unsupported percentage, guarantee, certification, partner or result claims.

Verification:

```bash
git diff --check
npm run seo:check
```

Evidence required:

- Legal/Sales/PM-approved claim-source matrix.
- No PII/raw customer/private evidence in repo.

### CLS-WP-08 — FAQ And Legacy Global Copy Cleanup

Workflow lane: Full Feature or Fast Fix for small copy-only changes.
Priority: P2.

Affected areas:

- FAQ rows/sections
- `/about/`, `/contacts/`, footer
- legacy generic service copy on homepage/services/aiagents

Acceptance criteria:

- FAQ copy matches each page role.
- Old broad AI-service/bot-startup language is removed or reframed.
- Footer/company one-liner supports product-first storyline.
- No unsupported speed/result/guarantee claims.

Verification:

```bash
git diff --check
npm run seo:check
```

## Import Rules

1. Create one tracker issue per `CLS-WP-*` when implementation planning begins.
2. Copy affected `CLS-*` IDs and mapped canonical IDs into the tracker issue.
3. Keep copy-only and runtime/contract changes separate.
4. Do not store raw customer evidence, PII, raw form payloads, sessions, tokens, cookies, IP addresses or private contracts in issues.
5. If a task changes public title/meta/canonical/sitemap, add SEO owner and SEO verification.
6. If a task changes form, chat, analytics or upstream payload, create a separate Security / Integration issue.

## Copy Format

```text
Issue ID:
Title:
Workflow lane:
Priority:
Start policy:
Owners:
CLS IDs:
Mapped canonical IDs:

Objective:

Affected areas:

Definition of Ready:

Acceptance criteria:

Verification:

Evidence required:

Blockers:

Do not start:

Source documents:
```

## Verification For This Backlog Document

```bash
git diff --check
```

## Closure Rule

This backlog can move from `issue backlog draft` to `tracker-imported` only when:

- every `CLS-WP-*` issue exists in the external tracker or internal issue list;
- tracker issue IDs are recorded here or in a future machine-readable tracker;
- owner statuses are known;
- blocked claims/evidence issues have explicit owner, reason and evidence path;
- future implementation issues reference both `CLS-*` and canonical product-tech gap IDs where applicable.
