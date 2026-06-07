# Product CJM / Use Cases / UX / UI Challenge Snapshot — 2026-06-07

Статус: docs-only challenge snapshot, not an approval package.
Workflow lane: Full Feature discovery / documentation.
Scope: product challenge of current public application and product funnel. No runtime, payload, CRM, analytics, CSS, JS or Bitrix content changes.

## Purpose

Этот документ фиксирует результаты продуктового challenge текущего приложения по CJM, Use Cases, UX and UI. Его нужно использовать как memory artifact for future product work: при возврате к CJM, pilot kits, `/price/`, `/offer/`, `/calculator/`, `/agents/` vs `/aiagents/`, forms/chat states and TO BE UI.

Документ не заменяет source registers and does not close gaps. Все наблюдения ниже мапятся на уже существующие IDs from `product-tech-challenge-gap-register-2026-06-04.md`, `product-cjm-cta-crm-qualification-decision-2026-06-04.md` and `product-to-be-design-system-decision-2026-06-04.md`.

## Source Evidence

| Source | Signal |
|---|---|
| `local/php_interface/include/tacticum_config.php` | Current runtime config: product/page content and price presets are Bitrix-backed, REST origins are explicit, public forms remain browser-facing |
| `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md` | Role CJM, CTA taxonomy, returning lead, pilot kits, success-state copy and CRM fallback are drafted but pending owner approval |
| `docs/workflow/product-to-be-design-system-decision-2026-06-04.md` | TO BE design system decisions are drafted but pending Designer/Frontend/QA/Legal approval |
| `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md` | Canonical product-tech challenge register for UX/UI/Architecture/Content/Release gaps |
| `local/components/tacticum/home.page/templates/.default/parts/hero.php` | Homepage acts as product/ecosystem router with Platform/Agents/Dev/Forum links and AI chat |
| `local/php_interface/include/product_page_blocks/page.php` | Product pages are fixed linear block sequences: hero, fit guide, sections, architecture, use cases, comparison, procurement, rollout, proof, FAQ, CTA |
| `local/php_interface/include/product_page_blocks/use_cases.php` | Use cases expose trigger, owner, pilot input, pilot output and limitation, but not full pilot-kit workflow |
| `local/php_interface/include/product_page_blocks/procurement.php` | Security/procurement exists as safe-copy block, not as guided enterprise workflow |
| `local/components/tacticum/lead.cta/templates/.default/form.php` | Forms support hidden `lead_*`, scenario, budget/timeline and returning-lead panel, but visible UX remains generic |
| `local/components/tacticum/offer.catalog/templates/.default/parts/*.php` | `/offer/` is a searchable/filterable estimate catalog by industry/scenario/budget/phase |
| `local/templates/tacticum/components/bitrix/news.detail/offer/parts/*.php` | Offer detail can prefill context and links examples to products, but product interest often remains `ecosystem` |
| `local/templates/tacticum/components/bitrix/news.list/price/parts/*.php` | `/price/` team builder is powerful but still visibly rate/role-first |
| `local/components/tacticum/aiagents/templates/.default/parts/*.php` | `/aiagents/` keeps Telegram bot/prototype flow and can compete with `/agents/` unless explicitly framed as compatibility/service route |
| `local/templates/tacticum/js/forms.js`, `local/templates/tacticum/js/forms-runtime.js` | Form success/errors are safe and analytics-aware, but success copy is generic by default |
| `local/templates/tacticum/js/chat-hero.js`, `local/templates/tacticum/js/chat-calculators.js` | Chat/prefill/handoff surfaces exist, but error/long-answer/enterprise trust states need TO BE state design |

## Challenge Verdict

Текущее приложение уже является product-first lead-gen system, not a generic corporate site. The product taxonomy is visible, product pages exist, forms carry controlled context, and commercial tools exist around the products.

The main product gap is not missing pages. The main product gap is that the public journey is still mostly page/surface-driven, not buyer-decision-driven. Enterprise users should be able to move from role and concern to scenario, proof need, pilot boundary and next step. Today they mostly choose between product pages, offer examples, calculator, price builder and a generic form.

## Product Model Observed

| Surface | Current Product Role | Challenge |
|---|---|---|
| `/` | Ecosystem router to Platform / Agents / Dev / Forum plus chat and commercial entry | Routes by product, not by buyer role or enterprise concern |
| `/platform/`, `/agents/`, `/dev/`, `/forum/` | Product pages with fit guide, architecture, use cases, procurement, proof and CTA | Strong structure, but linear and not role/stage adaptive |
| `/offer/` | Example/estimate catalog and conversion bridge | Strong browsing utility, weaker decision conversion into pilot kit/template |
| `/calculator/` | AI-assisted estimate entry | Promise depends on clear sample output and live page-content quality |
| `/price/` | Team builder / staff-order utility around product delivery | Risk of staff augmentation framing rather than product implementation packaging |
| `/services/` | Delivery/implementation route | Can overlap product pages unless framed as delivery layer |
| `/aiagents/` | Telegram demo/prototype entry for Agents | Can dilute `/agents/` enterprise product positioning |
| Forms/chat | Context capture, no-PII analytics, prefill/handoff | Visible UX states and next-step promise lag behind technical contract |

## CJM Challenge

| Observation | Priority | Existing Gap Mapping | Challenge |
|---|---:|---|---|
| Role-based CJM is drafted but not operationalized in UI | P1 | `UX-001`, `UX-002`, `CMP-003` | CEO, CTO, CISO, Procurement and Product Owner need different entry points and proof needs; current flow mostly routes to products/pages |
| Security/procurement path is present but not guided | P1 | `UX-007`, `UI-006`, `SEC-002` | Current block says what to discuss, but does not behave as data/access/integration/document workflow |
| Returning-lead journey is session-level, not sales-stage-level | P2 | `UX-003`, `ARCH-005` | Current returning panel only says a request was sent in this session; it does not distinguish proof request, pilot refinement or architecture follow-up |
| Post-submit expectation remains generic | P2 | `UX-010`, `UI-007` | Success state must say what happens next by context without implying guarantees or exposing CRM internals |
| Homepage is a product router, not a decision router | P1 | `UX-001`, `UX-009` | Good product taxonomy, but not enough guided entry by role, maturity, data/security constraint or desired artifact |

### CJM Direction To Preserve

- Keep product URLs as canonical product pages.
- Add visible role/stage routing before adding any new payload fields.
- Keep fit guide static v1 until PM/UX approve interactive selection and Analytics/Security approve data model.
- Use existing `lead_*` context first; do not add structured CRM fields until Sales/upstream/Security approve.

## Use Cases Challenge

| Observation | Priority | Existing Gap Mapping | Challenge |
|---|---:|---|---|
| Use cases are pilotable in anatomy but not yet packaged as pilot kits | P1 | `UX-008`, `CONTENT-003` | They expose trigger/owner/input/output/limitation, but not first-meeting checklist, timeline, success criteria and artifact promise |
| Offer examples are strong evidence-like assets but not product-tagged enough for decision | P1 | `ARCH-009`, `CONTENT-003` | `/offer/` filters by industry/scenario/budget/phase; it should also support product relation and use-as-template conversion |
| Product taxonomy is visible but needs Sales/market validation | P1 | `CONTENT-005`, `UX-004` | Platform/Agents/Dev/Forum names and boundaries need approval against real buyer language and Sales routing |
| `/price/` use case is still team construction more than packaged product workstream | P1 | `UX-005`, `UI-009` | Team presets help, but the page still foregrounds roles, levels and hourly rates |
| `/aiagents/` Telegram bot use case can narrow Agents perception | P1 | `UX-004`, `ARCH-010` | It should remain demo/prototype/service route and should visibly lead to `/agents/` enterprise product framing |

### Pilot Kit Minimum Shape

Each product pilot kit should include:

- product and scenario;
- buyer role and owner from client side;
- input artifacts required before first session;
- what Tacticum checks during pilot;
- expected output artifact;
- limitation / what the pilot does not prove;
- safe proof status: `pilot-artifact`, `private-evidence`, `public-safe`, `pending` or `blocked`;
- recommended CTA and success copy.

## UX Challenge

| Observation | Priority | Existing Gap Mapping | Challenge |
|---|---:|---|---|
| CTA taxonomy exists in docs and hidden context, but visible CTA hierarchy is not role/stage-specific enough | P1 | `UX-002`, `CMP-003` | Product pages and forms should separate explore, pilot, architecture, procurement/security, team, estimate and returning-lead intents |
| Forms collect useful context but can be high-friction for early research traffic | P2 | `UX-010`, `ARCH-005` | Required name/email/phone/message is reasonable for high-intent leads, but weak for lower-friction artifacts like pilot format or security checklist request |
| Calculator promise needs a visible sample deliverable | P2 | `UX-010`, `UI-008` | Users need to see expected output before investing in chat input |
| Chat handoff is useful but state trust is weak | P2 | `UI-008`, `ARCH-006` | Error, long answer, prefill and retry states should be safe, stable and enterprise-grade |
| `/offer/` can become browsing without commitment | P2 | `UX-002`, `ARCH-009` | Add stronger bridge from example to pilot kit / personalized scope, not only open detail and form |
| `/price/` mobile/team-builder remains a high-risk interaction | P1 | `UI-009`, `CMP-004` | Summary-first flow and smoke coverage are required before broad mobile UX changes |
| Consent and trust cues need review on custom offer forms | P2 | `UI-007`, `SEC-001` | Keep explicit, accessible consent and avoid defaults that reduce trust |

## UI Challenge

| Observation | Priority | Existing Gap Mapping | Challenge |
|---|---:|---|---|
| Current UI reads as safe corporate Tailwind/blue card system, not distinctive enterprise AI product UI | P1 | `UI-001`, `UI-002`, `UI-003`, `STACK-003` | TO BE token source, density, palette and card policy need approval before redesign |
| Product pages have strong block taxonomy but weak product-specific visual differentiation | P2 | `UI-004`, `CMP-008` | Differentiate by structure, diagram/proof hierarchy and decision content, not four unrelated color themes |
| Proof/status UI exists but is not an approved trust system | P1 | `UX-006`, `UI-005`, `CONTENT-002` | Badges must not upgrade evidence maturity by visual treatment |
| Architecture/procurement sections are text-heavy | P2 | `UI-006` | Enterprise users need diagrams, matrices and mobile fallbacks for data/access/integration/procurement paths |
| Form/chat states are not TO BE designed | P1 | `UI-007`, `UI-008` | Normal, focus, validation, loading, error, success, retry, prefill and handoff states need component-level state design |
| `/aiagents/` visual language can look like a separate product | P1 | `UX-004`, `UI-004` | Legacy/service route must not visually imply it replaced `/agents/` |

## Priority Backlog Snapshot

Observation IDs below are local to this snapshot and are not canonical tracker IDs.

| Snapshot ID | Priority | Maps To | Owner Group | Next Action |
|---|---:|---|---|---|
| `PCJMU-001` | P1 | `UX-001`, `UX-002`, `CMP-003` | PM + UX + Sales | Approve role/stage CJM and decide where role entrypoints appear in homepage/product pages/forms |
| `PCJMU-002` | P1 | `UX-008`, `CONTENT-003` | PM + Content + Sales | Turn current use-case anatomy into product pilot kits with owner checklist and expected output |
| `PCJMU-003` | P1 | `UX-006`, `CONTENT-002`, `UI-005` | PM + Sales + Legal + Designer | Finalize proof/claims matrix and approved proof/status visual semantics |
| `PCJMU-004` | P1 | `UX-005`, `UI-009`, `CMP-004` | PM + UX + Designer + Frontend + QA | Reframe `/price/` around product implementation packages while preserving team builder contracts |
| `PCJMU-005` | P1 | `UX-004`, `ARCH-010`, `CONTENT-005` | PM + SEO + Sales | Decide `/agents/` vs `/aiagents/` canonical/copy boundary and update route intent if approved |
| `PCJMU-006` | P1 | `UI-001`, `UI-002`, `UI-003`, `STACK-003` | Designer + Frontend | Approve TO BE token/density/palette pipeline before broad restyle |
| `PCJMU-007` | P2 | `UX-003`, `UX-010`, `UI-007` | PM + UX + QA + Sales | Define visible returning-lead and context-specific success states without new CRM fields |
| `PCJMU-008` | P2 | `UX-007`, `UI-006`, `SEC-002` | PM + Security + Architect + Designer | Design procurement/security guided workflow and safe diagram labels |
| `PCJMU-009` | P2 | `ARCH-006`, `UX-002` | PM + Analytics + QA | Validate no-PII funnel measurement for role/stage journeys |
| `PCJMU-010` | P2 | `ARCH-009`, `CONTENT-003` | Content + SEO + Sales | Tag offers/cases/FAQ/services by product and evidence type |

## Owner Questions To Reopen Later

- Which buyer role is primary for the next growth cycle: CEO/business owner, CTO/CIO, CISO, Procurement/Legal or Product Owner?
- What is the main conversion event: product pilot, architecture session, security/procurement discussion, estimate, team selection or Telegram prototype?
- What exactly is a pilot: duration, owner, input artifacts, output artifact, limitation and commercial next step?
- Which proof can be public, private-by-request, pending or blocked?
- Is `/price/` a product implementation package route, a staff augmentation route or both with explicit hierarchy?
- Is `/aiagents/` a lead magnet, legacy compatibility route, service page or separate product?
- Can Sales route leads from current `lead_product`, `lead_scenario`, `lead_next_step` text fallback, or is a structured CRM migration required?
- Which procurement/security artifacts can be promised after a request, and which require NDA/private access model?
- Do we need a low-friction artifact CTA before asking for phone, or is current required-contact model acceptable for all traffic?
- What role/stage events must Analytics/Metrika measure without PII?

## Do Not Start Without Gates

- Do not add structured CRM/upstream fields before Sales/upstream/Security approval.
- Do not add private proof downloads or procurement packets before access/auth model approval.
- Do not publish stronger metrics, logos, certifications, SLA or registry claims before proof/claims matrix approval.
- Do not start broad visual redesign before TO BE token, density, proof/status and state approvals.
- Do not rewrite `/price/` mobile flow without baseline and post-change browser smoke.
- Do not make `/agents/` redirect/canonical changes without PM/SEO decision.
- Do not create interactive fit guide until role CJM, analytics and payload/privacy model are approved.

## Suggested Return Path

When this challenge is reopened, use this order:

1. Owner review: PM/UX/Sales confirm or reject `PCJMU-001`, `PCJMU-002`, `PCJMU-004`, `PCJMU-005`.
2. Legal/Security review: proof/status and procurement/private-document boundaries.
3. Design review: TO BE token/density/proof/form/chat/price state package.
4. Analytics review: no-PII role/stage funnel goals.
5. Implementation planning: create issue(s) that reference the canonical gap IDs, not only local snapshot IDs.
6. Verification planning: define smoke per affected surface before code changes.

## Related Documents

- `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
- `docs/workflow/product-tech-challenge-execution-roadmap-2026-06-04.md`
- `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md`
- `docs/workflow/product-to-be-design-system-decision-2026-06-04.md`
- `docs/workflow/product-taxonomy-seo-packaging-decision-2026-06-04.md`
- `docs/workflow/product-frontend-component-hardening-decision-2026-06-04.md`
- `docs/workflow/product-security-release-legacy-closure-decision-2026-06-04.md`
- `docs/workflow/gap-analysis.md`
- `docs/workflow/current-state.md`
