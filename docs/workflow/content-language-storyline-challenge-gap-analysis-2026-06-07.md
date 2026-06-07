# Content Language / Storyline Challenge Gap Analysis — 2026-06-07

Дата: 07.06.2026

Статус: challenge source register, not an approval package; `CLS-WP-01` has local implementation and awaits rendered smoke evidence.
Workflow lane: Full Feature discovery / documentation.
Scope: public site content, Russian-first language, tone of voice, storyline coherence, proof/claims copy and visible editorial defects. No PHP, JS, CSS, Bitrix admin data, REST, CRM, analytics or SEO route changes in this task.

## Purpose

Этот документ фиксирует результаты придирчивого контентного challenge текущего сайта `tacticum.ru`: подача, язык, связность and общий сторилайн. Его нужно использовать как source register for future content work before rewriting homepage, product pages, `/price/`, `/offer/`, `/calculator/`, `/aiagents/`, FAQ, proof blocks or visible CTA copy.

Документ не закрывает существующие canonical product-tech gaps. Local IDs `CLS-*` ниже мапятся на canonical IDs из:

- `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`;
- `docs/workflow/product-cjm-usecases-ux-ui-challenge-2026-06-07.md`;
- `docs/workflow/product-taxonomy-seo-packaging-decision-2026-06-04.md`;
- `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md`;
- `docs/workflow/product-to-be-design-system-decision-2026-06-04.md`.

## Audit Method

- Fetched rendered production HTML on 07.06.2026 for `/`, `/platform/`, `/agents/`, `/dev/`, `/forum/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/about/`, `/contacts/`, `/policies/`.
- Fetched three live `/offer/` detail pages to inspect detail-level sales copy.
- Extracted visible text, title, meta description, H1-H3, CTA-like lines and English/mixed-language terms from rendered HTML.
- Cross-checked visible issues against local sources and mappers under `local/` and content seed tools under `tools/`.
- Did not use Bitrix admin access and did not change production content.

## Source Evidence

| Source | Signal |
|---|---|
| Rendered `/`, 07.06.2026 | Homepage presents product ecosystem, commercial routes, proof, calculator, FAQ and footer in one long mixed narrative |
| Rendered `/platform/`, `/agents/`, `/dev/`, `/forum/`, 07.06.2026 | Visible internal labels: `Product fit`, `fits`, `not_fits`, `start`, `Use cases`, `Security / procurement` |
| Rendered `/services/`, 07.06.2026 | Product/delivery block exposes `Delivery layer`, `Platform assessment`, `Agents pilot`, `Dev workflow`, `Forum launch` |
| Rendered `/price/`, 07.06.2026 | Page foregrounds team/staffing/rates; product workstream copy is present but secondary |
| Rendered `/offer/` and offer detail pages, 07.06.2026 | Catalog works as estimate/proof bridge, but detail pages use fear framing and generic vendor claims |
| Rendered `/aiagents/`, 07.06.2026 | Telegram bot/prototype route can still read as a separate bot product rather than a demo entry to Tacticum Agents |
| Rendered `/about/`, `/contacts/`, footer, 07.06.2026 | Operational trust copy exists, but final company one-liner remains generic and not strongly tied to product-first model |
| `local/lib/Tacticum/Product/ContentBlockMapper.php` | `childCard()` maps Bitrix child element `NAME` to public `title`; service keys can become public H3 if Bitrix/seed names are technical |
| `local/php_interface/include/product_page_blocks/fit_guide.php` | Renderer has Russian default labels, but mapped `title` overrides them |
| `local/php_interface/include/product_page_blocks/use_cases.php` | Default eyebrow is `Use cases`, not Russian-first |
| `local/php_interface/include/product_page_blocks/procurement.php` | Default eyebrow/note include `Security / procurement`, `assessment`, `Deployment`, `evidence` |
| `tools/content-storage-page-content-seed.php` | Page-content seed contains many public English/internal terms across homepage, services, price, calculator and aiagents |
| `local/templates/tacticum/components/bitrix/news.detail/offer/parts/risks.php` | Offer detail uses negative framing: risks without professional team |
| `local/templates/tacticum/components/bitrix/news.detail/offer/parts/reasons.php` | Offer detail contains generic claims: rich industry experience, optimal solutions, always know result timing |

Implementation note 07.06.2026: local `CLS-WP-01` implementation normalized product page fallback labels, added `ContentBlockMapper` protection for legacy Bitrix `fits/not_fits/start` names, cleaned page-content seed labels and added source/rendered public hygiene guards. Follow-up local implementation added exact-phrase runtime normalization for assembled product content, product FAQ and live page-content rows, so closure does not depend only on manual Bitrix reseed. Production baseline before deploy/cache refresh still fails `npm run content:public-hygiene:rendered:prod` with 26 visible issues across product/page-content pages; rerun after deploy is required before using `closed`. For release closure, `npm run content:public-hygiene:rendered:prod:json` prints safe JSON evidence for the `content-public-hygiene` sign-off gate.

## Challenge Verdict

The site has a real product-first structure, but the public content is not yet a disciplined Russian-language B2B narrative. It reads as a hybrid of:

- enterprise AI product ecosystem;
- AI/IT implementation agency;
- staff augmentation and hourly-rate catalog;
- synthetic estimate catalog;
- Telegram bot demo/service;
- internal product/architecture handoff.

The main content risk is not that pages are missing. The main risk is that visible copy does not consistently tell one story in one language. It frequently exposes internal product-management and engineering vocabulary to public users.

## Target Public Storyline

Recommended north-star narrative:

> Tacticum helps companies safely launch AI in work processes: choose a scenario, estimate budget, validate a pilot and assemble the implementation team.

Every public page should support this hierarchy:

| Layer | Public Role |
|---|---|
| `/` | Explain the model and route the user to the right first step |
| `/platform/`, `/agents/`, `/dev/`, `/forum/` | Product entry points by task type |
| `/services/` | How Tacticum brings a selected scenario to implementation |
| `/price/` | Team composition and budget orientation for pilot/implementation, not just staff rates |
| `/calculator/` | Preliminary budget/timeline/team/risk estimate |
| `/offer/` | Example estimates as orientation, not final commercial offers |
| `/aiagents/` | Demo/prototype stand for one Agents scenario, not a replacement for `/agents/` |
| `/about/`, `/contacts/`, `/policies/` | Vendor trust, legal/contact route and operational confidence |

## Statuses

| Status | Meaning |
|---|---|
| `open` | Confirmed content/storyline gap, needs task or owner decision |
| `blocked` | Requires PM/Sales/Legal/SEO/Security/Design approval before implementation |
| `in-progress` | Partial working basis exists, but public content is not mature enough |
| `accepted-monitor` | Deliberately accepted for now, must be monitored on future changes |
| `closed` | Do not use here unless actual implementation/evidence exists |

## Priority Rules

| Priority | Meaning |
|---|---|
| `P0` | Publicly visible defect, unsafe claim inconsistency or trust-breaking content issue |
| `P1` | Required before major content/product/SEO/design implementation |
| `P2` | Important quality backlog after priority narrative fixes |
| `P3` | Editorial maturity / nice-to-have |

## Complete Gap Register

| ID | Status | Priority | Area | Gap | Required Task | Existing Gap Mapping | Owner Group | Gates / Evidence |
|---|---|---:|---|---|---|---|---|---|
| `CLS-001` | in-progress | P0 | Product pages / content mapper | Public pages expose internal labels: `Product fit`, `fits`, `not_fits`, `start`, `Use cases`, `Security / procurement`. | Fix public label mapping/source data and add rendered/source guard against service labels in H1-H3/eyebrows. | `CFG-002`, `ARCH-003`, `CONTENT-004`, `UX-010`, `CMP-001` | Backend + Content + QA | Local source/mapper fix exists; rendered pages must show Russian labels after deploy/cache refresh; guard blocks recurrence. |
| `CLS-002` | open | P1 | Language | Russian-first public language is not enforced; ordinary explanatory copy uses too much English/internal terminology. | Approve public glossary and rewrite visible non-brand terms into Russian-first copy. | `CONTENT-004`, `CONTENT-005`, `UX-001`, `SEO-009` by relation | PM + Content + SEO + Sales | Glossary approved; rewritten pages pass editorial review and SEO title/description review. |
| `CLS-003` | open | P1 | Homepage storyline | Homepage has product ecosystem plus commercial routes plus proof/calculator/FAQ, but does not establish one stable company promise strongly enough. | Approve homepage narrative contract and rewrite first screen + routing copy around scenario/pilot/estimate/team hierarchy. | `UX-001`, `UX-002`, `CONTENT-005`, `CONTENT-004`, `PCJMU-001` | PM + UX + Content + Sales | Homepage copy reviewed; no route/canonical change unless SEO approves. |
| `CLS-004` | open | P1 | Product pages | Product pages are structurally strong but often read like internal architecture specs rather than public B2B product pages. | Rewrite product page public layer: business outcome first, technical terms second, Russian-first labels. | `UX-008`, `CONTENT-003`, `CONTENT-004`, `CONTENT-005`, `UI-006`, `PCJMU-002` | PM + Content + Architect + Sales | Product pages keep technical accuracy, but headings and intros are public-readable. |
| `CLS-005` | open | P1 | `/price/` | `/price/` still frames Tacticum as staff/rate catalog more than product implementation partner. | Reframe `/price/` around implementation team composition and budget orientation; keep rates as transparency layer. | `UX-005`, `UI-009`, `CMP-004`, `CONTENT-005`, `PCJMU-004` | PM + Sales + UX + SEO + Frontend | Copy hierarchy approved; team builder contracts unchanged unless separate issue. |
| `CLS-006` | open | P1 | `/agents/` vs `/aiagents/` | `/aiagents/` can dilute enterprise Agents positioning and make company look like a Telegram bot shop. | Rewrite `/aiagents/` as demo/prototype stand for one Agents scenario; strengthen bridge to `/agents/`. | `UX-004`, `ARCH-010`, `CONTENT-005`, `UI-004`, `PCJMU-005` | PM + SEO + Sales + Content | PM/SEO route intent approved; no redirect/canonical change without SEO gate. |
| `CLS-007` | blocked | P0 | Proof / claims | Cautious product copy says not to promise without assessment, while proof/case/FAQ areas still show strong percentages and broad success claims. | Build proof/claims matrix and mark each public claim as approved public, private-by-request, benchmark, example or blocked. | `UX-006`, `CONTENT-001`, `CONTENT-002`, `CONTENT-003`, `ARCH-009`, `UI-005`, `PCJMU-003` | PM + Sales + Legal + Content | Legal/Sales approval; no stronger public metrics/logos/certifications without evidence. |
| `CLS-008` | open | P1 | `/offer/` detail | Offer detail pages use fear framing and generic vendor claims: risks without professional team, rich experience, optimal solutions. | Rewrite offer detail reasons/risks as neutral decision checklist and evidence-safe next step. | `CONTENT-002`, `CONTENT-003`, `UX-002`, `CONTENT-004`, `ARCH-009` | Content + Sales + Legal + SEO | Detail copy reviewed; examples remain clearly non-final estimates. |
| `CLS-009` | open | P2 | FAQ / legacy copy | FAQ and old generic blocks retain earlier broad AI-service tone and conflict with product-first narrative. | Inventory and rewrite FAQ/global copy by page role; retire bot/startup phrasing where enterprise trust is needed. | `CONTENT-004`, `UX-010`, `CONTENT-005` | Content + PM + Sales + Legal | FAQ source review; no unsupported guarantee/speed/result claims. |
| `CLS-010` | open | P2 | CTA / next-step copy | CTA copy repeats generic `следующий шаг`, `обсудить`, `уточнить`, and often does not say what artifact user receives. | Define CTA/action language by stage: scenario, pilot, architecture, estimate, team, procurement, demo. | `UX-002`, `UX-010`, `CMP-003`, `PCJMU-007` | PM + UX + Content + Sales | CTA matrix approved; form/upstream payload unchanged unless separate Security / Integration issue. |
| `CLS-011` | open | P1 | Editorial governance | No public tone-of-voice and glossary document governs Bitrix/page-content/product-content edits. | Adopt `content-language-storyline-public-glossary-2026-06-07.md` as editorial baseline and add owner review path. | `CONTENT-004`, `CONTENT-005`, `CFG-002`, `ARCH-001` | Content + PM + SEO | Owner-approved glossary and editor checklist. |
| `CLS-012` | in-progress | P1 | QA / release guard | Current guards do not explicitly scan rendered public HTML for forbidden editorial labels and internal terms. | Add source/rendered content hygiene guard or checklist before content release. | `STACK-004`, `STACK-005`, `ARCH-003`, `REL-002` | QA + Frontend + Backend + Content | Source guard `npm run content:public-hygiene:check`, rendered guard `npm run content:public-hygiene:rendered:prod` and sign-off JSON `npm run content:public-hygiene:rendered:prod:json` exist; post-deploy rendered evidence remains pending. |

## Page-Specific Findings

| Page / Area | Finding | Priority | Related IDs |
|---|---|---:|---|
| `/` | Strong ecosystem idea, but too many equal entry points and mixed AI/ИИ/product/commercial framing. | P1 | `CLS-002`, `CLS-003`, `CLS-010` |
| `/platform/` | Mature architecture content, but public copy overuses `runtime`, `data`, `access`, `observability`, `assessment`, `deployment`. | P1 | `CLS-001`, `CLS-002`, `CLS-004` |
| `/agents/` | Good enterprise assistant boundary, but `handoff` and internal labels repeat heavily. | P1 | `CLS-001`, `CLS-002`, `CLS-004`, `CLS-006` |
| `/dev/` | Most internal/English page; useful for CTO/engineering audience, but needs Russian public layer before technical terms. | P1 | `CLS-001`, `CLS-002`, `CLS-004` |
| `/forum/` | Clear scenario+LLM logic, but too many terms like `checkpoints`, `funnel`, `runtime`, `review workflow`. | P1 | `CLS-001`, `CLS-002`, `CLS-004` |
| `/services/` | Strong buyer-friendly entry, but product/delivery block exposes internal labels. | P1 | `CLS-002`, `CLS-004`, `CLS-010` |
| `/price/` | Useful configurator, but visible framing is rates/staffing-first. | P1 | `CLS-005`, `CLS-010` |
| `/calculator/` | Useful estimate promise; wording like `черновой артефакт` should become public-readable. | P2 | `CLS-002`, `CLS-010` |
| `/offer/` | Catalog useful for estimate orientation, but examples need clearer non-final-estimate status. | P1 | `CLS-007`, `CLS-008` |
| Offer detail | Sales copy uses fear framing and broad claims. | P1 | `CLS-007`, `CLS-008` |
| `/aiagents/` | Current page still risks positioning Tacticum as Telegram bot vendor. | P1 | `CLS-006`, `CLS-009` |
| `/about/`, `/contacts/`, footer | Trust and legal content exist, but company one-liner remains generic. | P2 | `CLS-003`, `CLS-009`, `CLS-010` |

## Minimum Closure Bundles

### Bundle A — Public Defect Removal

Must close before any broad content rewrite:

- `CLS-001`
- `CLS-012`

### Bundle B — Russian-First Narrative

Must close before homepage/product page rewrite:

- `CLS-002`
- `CLS-003`
- `CLS-004`
- `CLS-010`
- `CLS-011`

### Bundle C — Commercial Route Reframing

Must close before changing `/price/`, `/offer/`, `/calculator/`, `/aiagents/` copy:

- `CLS-005`
- `CLS-006`
- `CLS-008`
- `CLS-009`

### Bundle D — Claims And Evidence Safety

Must close before publishing stronger cases, metrics, logos or guarantees:

- `CLS-007`
- `CLS-008`
- existing `CONTENT-001`, `CONTENT-002`, `CONTENT-003`, `UX-006`, `ARCH-009`, `UI-005`

## Do Not Start Without Gates

- Do not publish rewritten claims, metrics, logos, partner/certification/SLA/registry promises without Legal/Sales/PM approval.
- Do not change `/agents/` vs `/aiagents/` canonical/redirect/sitemap behavior without SEO/PM approval.
- Do not change lead form payload, hidden fields or upstream CRM fields as part of copy work; use a separate Security / Integration task.
- Do not rewrite `/price/` interaction or mobile team builder while doing content-only reframing.
- Do not treat synthetic/typical offer examples as real customer proof unless evidence status is approved.
- Do not use internal terms as public headings unless they are approved product names or standard acronyms with first-use explanation.

## Suggested Return Path

1. PM/Content/Sales approve or edit the north-star storyline and glossary.
2. Backend/QA scope the visible internal-label fix and content hygiene guard.
3. PM/SEO decide copy-only vs route/metadata implications for `/agents/` and `/aiagents/`.
4. Legal/Sales/Content review proof/claims status before any case/metric rewrite.
5. Create tracker issues from `content-language-storyline-challenge-issue-backlog-2026-06-07.md`.
6. Implement in small waves: labels/guard, global narrative, product pages, commercial routes, FAQ/proof cleanup.

## Related Documents

- `docs/workflow/content-language-storyline-challenge-roadmap-2026-06-07.md`
- `docs/workflow/content-language-storyline-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/content-language-storyline-public-glossary-2026-06-07.md`
- `docs/workflow/product-cjm-usecases-ux-ui-challenge-2026-06-07.md`
- `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
- `docs/workflow/product-taxonomy-seo-packaging-decision-2026-06-04.md`
- `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md`
- `docs/workflow/product-to-be-design-system-decision-2026-06-04.md`
- `docs/workflow/gap-analysis.md`
- `docs/workflow/current-state.md`
