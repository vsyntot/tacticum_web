# 20. Phase 4 SEO And Content Decision Pack

Дата: 02.06.2026

Статус: draft review package для Phase 4 SEO / content decisions. Документ не заменяет SEO research, search console, Метрику, production crawl или PM/Content approval; он фиксирует рабочую структуру решений и evidence, которые нужны перед TO BE SEO release.

## Назначение

Phase 4 из `15-gap-closure-master-plan.md` должен превратить product-first MVP в безопасную SEO/content модель: product clusters, canonical decisions, proof/case map and final metadata. Этот документ дает SEO, PM, Content, Sales and Dev единый review package.

Covered gaps:

- `SEO-TOBE-001` - product SEO clusters;
- `SEO-TOBE-002` - `/agents/` and `/aiagents/` duplication risk;
- `SEO-TOBE-003` - product-specific cases/proof map;
- `SEO-TOBE-005` - final metadata copy.

Related gaps:

- `PB-008` - `/agents/` vs `/aiagents/` canonical decision;
- `PB-005` / `PB-006` - proof and risky claims evidence;
- `REL-002` - rendered product SEO evidence after deploy.

## Current SEO Baseline

| Area | Current state | Guard / evidence |
|---|---|---|
| Product URLs | `/platform/`, `/agents/`, `/dev/`, `/forum/` are public static product pages | `tools/seo-check.mjs` expected static pages |
| Existing money URLs | `/aiagents/`, `/offer/`, `/price/`, `/calculator/`, `/services/` remain public | top/bottom menu and sitemap checks |
| Canonical paths | Static pages call `tacticum_apply_seo_defaults(...)` with expected canonical paths | `npm run seo:check` |
| Sitemap | root sitemap links static sitemap and offer sitemap | `sitemap.xml`, `sitemap-basic-files.xml`, `/offer/sitemap.php` |
| Product schema | Product pages render minimal `SoftwareApplication` + `FAQPage` schema | rendered smoke gate remains external |
| Forbidden schema | Product pages do not publish `offers`, price, reviews or ratings | `tools/seo-check.mjs` |
| `/aiagents/` | Preserved as compatibility / existing lead-gen page | no redirect/canonical migration yet |
| Product content | Safe-copy product sections exist, but final keyword/intent validation is open | this pack + SEO owner review |

## SEO-TOBE-001 - Product Cluster Map

The following is a starting hypothesis, not final keyword research.

| Page | Primary intent | Draft primary cluster | Secondary clusters | Avoid |
|---|---|---|---|---|
| `/platform/` | Enterprise buyer looking for AI infrastructure | корпоративная AI платформа | LLM gateway, RAG платформа, MCP, on-prem AI, AI governance | unsupported registry/certification claims |
| `/agents/` | Internal automation buyer looking for assistants | корпоративные AI-агенты | AI-ассистенты для бизнеса, RAG ассистенты, мультиагентные системы, внутренние AI-боты | duplicate generic Telegram bot content |
| `/dev/` | Engineering leader looking for controlled AI development | AI в разработке ПО | agentic SDLC, AI-assisted development, quality gates, design token compliance | workforce reduction promises |
| `/forum/` | CX/support buyer looking for managed dialogs | AI для контакт-центра | LLM чат-бот, сценарный бот, диалоговая платформа, support automation | pure LLM chatbot claims without control model |
| `/aiagents/` | Existing broad lead-gen intent | AI-боты для бизнеса | Telegram AI-бот, AI agent prototype, бот для лидогенерации | duplicate product page copy from `/agents/` |
| `/offer/` | Commercial proof and estimate intent | примеры расчетов AI проектов | AI проект бюджет, AI внедрение стоимость, product examples | unapproved case metrics |

Cluster validation checklist:

| Check | Required evidence |
|---|---|
| Search demand | SEO tool export or manual research snapshot |
| Intent fit | Page intent map approved by PM + SEO |
| SERP type | Notes on whether SERP expects product, service, article or case page |
| Competition | 3-5 competing page types and their positioning |
| Content gap | Required sections and FAQ questions for the page |
| Risk | Claims/legal/proof dependencies before publication |

Decision needed:

- SEO + PM approve one primary intent per product page;
- Content updates page copy only after primary/secondary clusters are approved;
- new indexable pages are not created just for keywords until proof/content readiness is confirmed.

## SEO-TOBE-002 - `/agents/` And `/aiagents/`

Current recommendation for review: keep both URLs for now with differentiated intent, until SEO has traffic/ranking/lead evidence for `/aiagents/`.

| URL | Current role | Proposed role for v1 | Canonical/redirect now? |
|---|---|---|---|
| `/agents/` | New product page for Tacticum Agents | Canonical product page for corporate internal assistants | Keep self-canonical |
| `/aiagents/` | Existing AI bots / Telegram agents money page | Compatibility and broad-intent lead-gen bridge to Agents | Keep self-canonical until SEO decision |

Differentiation rules:

- `/agents/` should focus on corporate internal assistants, business functions, RAG, governance, rollout and Platform relation;
- `/aiagents/` should focus on existing AI bot / Telegram bot / prototype demand and bridge users to `/agents/`;
- both pages should cross-link with clear intent labels;
- do not copy hero/H1/metadata from one page to the other;
- do not 301 redirect `/aiagents/` without traffic, ranking, leads and rollback review.

Decision options:

| Option | Choose when | Required evidence | Implementation implication |
|---|---|---|---|
| Keep both differentiated | `/aiagents/` has distinct traffic or broad-intent conversion value | traffic/ranking/lead report | keep self-canonicals, improve copy split |
| Canonical `/aiagents/` to `/agents/` | content becomes duplicate but route still needed | duplicate-content review and traffic impact acceptance | update SEO helper/canonical, keep compatibility page |
| 301 `/aiagents/` to `/agents/` | legacy route is no longer valuable | traffic/lead loss accepted, redirect rollback plan | update rewrite/deploy smoke/sitemap |

Decision needed:

- SEO + PM approve v1 option;
- if anything beyond "keep both differentiated" is chosen, create implementation task with sitemap/canonical/redirect smoke and rollback.

## SEO-TOBE-003 - Product Proof And Case Map

Product-specific proof is blocked by `PB-005` / `PB-006` until source evidence exists. SEO/content can still define the map without publishing unsupported claims.

| Proof type | Public readiness now | Best TO BE placement | Required owner |
|---|---|---|---|
| Product proof readiness | Allowed as safe "what we validate in pilot" copy | Product proof blocks | PM + Content |
| Offer examples | Existing indexed commercial/proof layer | `/offer/` hub and detail pages with product relation | SEO + Content + Sales |
| Case studies | Use only verified and approved cases | Future `/cases/` or product proof sections | Sales + Legal + Content |
| Metrics | Do not publish numeric claims without evidence | Case/proof cards with source notes | PM + Legal |
| Logos/testimonials | Do not publish without permission | Trust/proof sections | Legal + Sales |
| Regulatory proof | Do not imply status without legal evidence | Procurement/security section with status badges | Legal + Security |

Product proof tagging model for review:

| Product | Suggested tags | Proof examples to collect | Public copy rule |
|---|---|---|---|
| Platform | `platform`, `architecture`, `deployment`, `governance`, `rag`, `mcp` | architecture map, deployment constraints, reuse across products | no registry/certification unless approved |
| Agents | `agents`, `hr`, `legal`, `support`, `knowledge-base`, `rag` | pilot Q&A set, source coverage, handoff flow | no automation percentage without case |
| Dev | `dev`, `sdlc`, `quality-gates`, `design-system`, `architecture` | workflow trace, gate examples, defect/lead-time methodology | no workforce reduction claim |
| Forum | `forum`, `cx`, `contact-center`, `scenario-graph`, `analytics` | dialog flow, escalation map, journal example | no FCR/automation rate without evidence |
| Ecosystem | `ecosystem`, `delivery`, `pilot`, `integration` | rollout roadmap, team composition, offer estimate | keep services/delivery distinct from product license pricing |

Content model decision:

- for v1, product proof tags can remain in docs/data until `ARCH-001` approves content ownership;
- if `/offer/` or future `/cases/` needs product tags in Bitrix, create content model ADR or explicit iblock property migration task;
- do not publish product-specific case metrics until the claim register has source, owner and approved wording.

Decision needed:

- SEO + Content + Sales approve product proof taxonomy;
- PM/Legal decide which proof can be public, private/NDA or hidden.

## SEO-TOBE-005 - Metadata Approval Matrix

Current implementation has unique titles/descriptions. `17-local-gap-decision-briefs.md` provided draft alternatives. This Phase 4 pack turns that into an approval matrix.

| Page | Current title direction | Draft SEO direction | Approval question |
|---|---|---|---|
| `/platform/` | Platform as corporate AI product infrastructure | "AI-платформа / enterprise AI platform" | Should the title lead with "платформа" or "инфраструктурное ядро"? |
| `/agents/` | Corporate AI assistants for business functions | "корпоративные AI-агенты" | How strongly to distinguish from Telegram bots and `/aiagents/`? |
| `/dev/` | AI-assisted development governance | "AI в разработке ПО / agentic SDLC" | How much English terminology is acceptable in title/H1? |
| `/forum/` | Scenario + LLM for customer communications | "AI для контакт-центра / диалоговая платформа" | Should page target CX/contact-center cluster or broader dialog platform? |
| `/aiagents/` | AI bots and Telegram agents | "AI-боты для бизнеса" | Should it stay broad-intent page or become compatibility landing only? |

Metadata rules:

- each indexable page must have one clear primary intent;
- title, description and H1 should not all be identical;
- no unapproved registry/security/performance claims in metadata;
- `/agents/` and `/aiagents/` metadata must be visibly different;
- `/price/` must remain team/staff configurator, not product licensing;
- `/offer/` must remain examples/estimate/proof layer, not final price promise.

Approval deliverable:

| Page | Final title | Final description | Final H1 | Owner approval |
|---|---|---|---|---|
| `/platform/` | TBD | TBD | TBD | SEO + Content + PM |
| `/agents/` | TBD | TBD | TBD | SEO + Content + PM |
| `/dev/` | TBD | TBD | TBD | SEO + Content + PM |
| `/forum/` | TBD | TBD | TBD | SEO + Content + PM |
| `/aiagents/` | TBD | TBD | TBD | SEO + Content + PM |

Implementation rule:

- update page metadata only after this matrix is approved;
- run `npm run seo:check` locally after source changes;
- run rendered SEO smoke after deploy/cache refresh before closing `REL-002`.

## Accepted Monitoring - SEO-TOBE-004

`SEO-TOBE-004` remains accepted monitoring: industry/scenario pages should stay noindex or deferred until proof/content readiness exists.

Revisit trigger:

- product proof taxonomy is approved;
- at least 3-5 approved proof/case assets exist for a cluster;
- Legal/Sales approve claims and logos/testimonials where used;
- SEO confirms the page deserves an indexable standalone URL.

## Technical SEO Guardrails

| Guardrail | Current command / evidence |
|---|---|
| Static canonical and sitemap coverage | `npm run seo:check` |
| Product navigation/footer links | `npm run seo:check` |
| Product page rendered schema | `TACTICUM_EXPECT_SEO_HEAD=1 node ./tools/visual-smoke.mjs` or release smoke |
| Product block presence | `product:block-previews` / rendered smoke manifests |
| No commercial product schema offers/prices | `npm run seo:check` |
| Production rendered evidence | `REL-002` release sign-off gate |

Do not close Phase 4 only from source checks. Final closure needs:

- approved SEO/content decisions;
- source checks green;
- rendered production/staging evidence after deploy/cache refresh;
- no PII in evidence artifacts.

## Phase 4 Closure Checklist

| Gap | Close only when |
|---|---|
| `SEO-TOBE-001` | SEO + PM approve product keyword/intent clusters and page intent map |
| `SEO-TOBE-002` | SEO + PM approve `/agents/` vs `/aiagents/` canonical/compatibility strategy |
| `SEO-TOBE-003` | SEO + Content + Sales approve product proof/case taxonomy and content ownership path |
| `SEO-TOBE-005` | SEO + Content approve final title/description/H1 and implementation passes `seo:check` |
| `REL-002` | rendered product SEO evidence is captured after deploy/cache refresh |

## Recommended Review Session

1. SEO presents product cluster research and `/aiagents/` traffic/lead evidence.
2. PM confirms page intent and product taxonomy implications.
3. Content confirms metadata and page copy changes.
4. Sales/Legal classify proof assets as public, NDA/private or blocked.
5. Dev confirms canonical/sitemap/schema implementation scope.
6. QA defines rendered SEO smoke evidence for release sign-off.
