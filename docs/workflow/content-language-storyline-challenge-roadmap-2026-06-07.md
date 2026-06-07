# Content Language / Storyline Challenge Roadmap — 2026-06-07

Дата: 07.06.2026

Статус: execution roadmap for `content-language-storyline-challenge-gap-analysis-2026-06-07.md`.
Scope: content/editorial/storyline planning only. No runtime implementation is approved by this roadmap.

## Purpose

Этот roadmap задаёт порядок закрытия `CLS-*` gaps. Он нужен, чтобы команда не начинала широкую перепись сайта хаотично: сначала убрать публичные дефекты, затем утвердить русский storyline/glossary, затем переписывать product pages and commercial routes, and only then mature proof/FAQ.

## Source Register

All local IDs below come from:

- `docs/workflow/content-language-storyline-challenge-gap-analysis-2026-06-07.md`

If a task references a `CLS-*` ID not present in the source register, update both documents before implementation planning.

## Execution Principles

1. Fix trust-breaking visible labels before subjective copy improvements.
2. Use Russian-first public copy; keep English only for product names, standard acronyms and SEO-approved terms.
3. Keep product names `Tacticum Platform`, `Tacticum Agents`, `Tacticum Dev`, `Tacticum Forum` unless PM/Sales/SEO approve a taxonomy change.
4. Do not strengthen proof, metrics or guarantees while doing tone cleanup.
5. Do not change lead payload, CRM/upstream fields or analytics taxonomy as part of content rewrite.
6. Treat `/price/` as product implementation/team route first, staffing/rates utility second.
7. Treat `/aiagents/` as demo/prototype route for Agents, not as a competing product.
8. Keep route/canonical/sitemap decisions under SEO gate.

## Phase 0 — Adoption And Ownership

Goal: make the content challenge visible and assign review ownership.

| Work | Covered IDs | Owners | Output |
|---|---|---|---|
| Link docs from workflow index/current/gap docs | all | PM + Codex | `README.md`, `current-state.md`, `gap-analysis.md` reference this layer |
| Owner review of storyline and glossary | `CLS-002`, `CLS-003`, `CLS-011` | PM + Content + Sales + SEO | Approved or edited public narrative contract |
| Decide implementation lane per task | all | PM + QA | Tracker issues use `CLS-*` and canonical gap IDs |

Exit criteria:

- Documents are discoverable.
- Owner groups are explicit.
- No runtime implementation is implied by docs-only closure.

## Phase 1 — Public Defect Removal

Goal: remove visible internal/service labels and prevent recurrence.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Product label mapping/source fix | `CLS-001` | Fast Fix / Full Feature | Backend + Content + QA | Russian visible labels for fit guide/use cases/procurement blocks |
| Content hygiene guard/checklist | `CLS-012` | Full Feature | QA + Backend + Frontend + Content | Source/rendered guard or release checklist for forbidden labels |
| Bitrix editor remediation | `CLS-001`, `CLS-012` | Full Feature | Content + Backend | Clear editor rule: service element names must not become public titles |

Do not start:

- broad homepage/product rewrite;
- proof/claims changes;
- route/canonical changes.

Exit criteria:

- Rendered product pages no longer show `fits`, `not_fits`, `start`, `Product fit`, `Use cases`, `Security / procurement` as public labels.
- Guard/checklist catches those terms before deploy.

## Phase 2 — Russian-First Storyline Contract

Goal: define what Tacticum says publicly before rewriting pages.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Public glossary approval | `CLS-002`, `CLS-011` | Full Feature | PM + Content + SEO + Sales | Approved term replacement table and voice rules |
| Global one-liner and footer/nav promise | `CLS-003`, `CLS-010` | Full Feature | PM + Content + Sales | Company promise, footer text, CTA language baseline |
| Page role matrix | `CLS-003`, `CLS-004`, `CLS-005`, `CLS-006`, `CLS-008` | Full Feature | PM + UX + Content + SEO | Each page has one public role in the storyline |

Do not start:

- individual page rewrites that introduce new terminology outside the glossary;
- SEO metadata changes without SEO review.

Exit criteria:

- All content work can point to one approved narrative contract.
- Glossary separates product names from public Russian terms.

## Phase 3 — Homepage And Product Pages Rewrite

Goal: make the core product narrative public-readable and cohesive.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Homepage rewrite | `CLS-003`, `CLS-010` | Full Feature | PM + UX + Content + Sales + SEO | First screen and routing copy lead from problem to product/estimate/team/demo hierarchy |
| Platform page rewrite | `CLS-002`, `CLS-004` | Full Feature | Content + Architect + Sales | Business-first platform copy with technical detail preserved below |
| Agents page rewrite | `CLS-002`, `CLS-004`, `CLS-006` | Full Feature | Content + Sales + Architect | Assistant/product copy with Russian alternatives for handoff/access/audit terms |
| Dev page rewrite | `CLS-002`, `CLS-004` | Full Feature | Content + Architect + Engineering Sales | Russian public layer for AI-assisted engineering governance |
| Forum page rewrite | `CLS-002`, `CLS-004` | Full Feature | Content + Sales + Architect | Russian copy for managed customer communications and LLM-assisted scenarios |

Do not start:

- new interactive fit guide;
- new form fields;
- new proof metrics;
- visual redesign beyond copy/layout-safe changes.

Exit criteria:

- Product pages keep technical credibility but stop reading like internal architecture briefs.
- Headings/eyebrows are Russian-first except approved product names.

## Phase 4 — Commercial Route Reframing

Goal: align `/price/`, `/offer/`, `/calculator/` and `/aiagents/` with the product-first story.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| `/price/` copy hierarchy | `CLS-005`, `CLS-010` | Full Feature | PM + Sales + UX + SEO | Team composition and budget orientation first; rates as transparency layer |
| `/aiagents/` bridge rewrite | `CLS-006`, `CLS-009` | Full Feature | PM + SEO + Content + Sales | Demo/prototype stand clearly leads to `/agents/` when task is enterprise-wide |
| `/offer/` catalog/detail rewrite | `CLS-008`, `CLS-010` | Full Feature | Content + Sales + Legal + SEO | Examples framed as orientation, not final estimate or proof claim |
| `/calculator/` promise cleanup | `CLS-002`, `CLS-010` | Full Feature | PM + Content + UX | Public-readable estimate output promise, not internal `artifact` language |

Do not start:

- `/price/` JS/mobile interaction changes without separate Frontend/QA scope;
- `/aiagents/` redirects/canonicals without SEO gate;
- changing offer data model while only doing copy cleanup.

Exit criteria:

- Commercial routes become ways to start the same product journey, not separate company identities.

## Phase 5 — Proof, Claims And FAQ Cleanup

Goal: remove inconsistent proof maturity and legacy generic AI-service tone.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Proof/claims matrix | `CLS-007` | Full Feature | PM + Sales + Legal + Content | Public/private/benchmark/example/blocked status for claims |
| Offer-detail risk/reason rewrite | `CLS-008` | Full Feature | Content + Legal + Sales | Neutral decision checklist and evidence-safe sales copy |
| FAQ inventory and rewrite | `CLS-009`, `CLS-010` | Full Feature | Content + PM + Legal | Page-role-specific FAQ copy without unsupported guarantees |
| Footer/about/contact one-liner cleanup | `CLS-003`, `CLS-009` | Fast Fix / Full Feature | Content + PM | Company-wide story matches product-first positioning |

Do not start:

- publishing new percentages/logos/certifications;
- marking synthetic offer examples as customer proof;
- private proof download/procurement flows.

Exit criteria:

- Public proof copy no longer conflicts with cautious product/procurement copy.
- FAQ does not reintroduce old generic AI-bot/agency positioning.

## Phase 6 — Governance And Monitoring

Goal: keep future Bitrix/page-content edits from drifting back.

| Workstream | Covered IDs | Lane | Owners | Required Output |
|---|---|---|---|---|
| Editor checklist | `CLS-011`, `CLS-012` | Full Feature | Content + QA | Checklist for Bitrix content changes before public promotion |
| Rendered HTML content smoke | `CLS-001`, `CLS-012` | Full Feature | QA + Frontend + Backend | Automated or manual smoke for forbidden labels/terms on affected URLs |
| Release planning rule | all | Full Feature | PM + QA | Future content/product tasks reference `CLS-*` and canonical gap IDs |

Exit criteria:

- New public content work has editorial acceptance criteria.
- Content drift has a repeatable detection path.

## Suggested Issue Packaging

| Issue | Theme | Must Include |
|---|---|---|
| `CLS-WP-01` | Public label leak and guard | `CLS-001`, `CLS-012` |
| `CLS-WP-02` | Russian-first glossary and voice rules | `CLS-002`, `CLS-011` |
| `CLS-WP-03` | Global storyline and homepage | `CLS-003`, `CLS-010` |
| `CLS-WP-04` | Product page copy rewrite | `CLS-004`, `CLS-002` |
| `CLS-WP-05` | `/price/` reframing | `CLS-005`, `CLS-010` |
| `CLS-WP-06` | `/agents/` vs `/aiagents/` copy boundary | `CLS-006`, `CLS-009` |
| `CLS-WP-07` | Proof/claims and offer detail cleanup | `CLS-007`, `CLS-008` |
| `CLS-WP-08` | FAQ/global legacy copy cleanup | `CLS-009`, `CLS-010`, `CLS-003` |

## Verification Guidance

Minimum verification for implementation tasks derived from this roadmap:

```bash
git diff --check
npm run seo:check
```

Additional verification depends on scope:

- Product pages: product content check/source smoke if mapper/Bitrix content model changes.
- Public URL/title/meta changes: `npm run seo:check:prod` after deploy.
- JS/CSS interaction changes: relevant browser/visual smoke.
- `/price/` behavior changes: `npm run browser:smoke:price` or current equivalent.
- Proof/claims changes: owner-approved evidence matrix, no raw PII/customer-private evidence in repo.

## Related Documents

- `docs/workflow/content-language-storyline-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/content-language-storyline-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/content-language-storyline-public-glossary-2026-06-07.md`
- `docs/workflow/product-cjm-usecases-ux-ui-challenge-2026-06-07.md`
- `docs/workflow/product-tech-challenge-execution-roadmap-2026-06-04.md`
