# 18. Phase 1 Product Decision Review Pack

Дата: 02.06.2026

Статус: draft review package для Phase 1 product decision closure. Документ не закрывает gaps сам по себе; он дает PM, Sales, Legal, Security, Architect, SEO and Content готовые таблицы для approval.

## Назначение

Phase 1 из `15-gap-closure-master-plan.md` должен убрать продуктовую неопределенность до TO BE design implementation. Этот документ собирает baseline для P0/P1 product decisions, которые нельзя оставлять на уровень визуального дизайна.

Covered gaps:

- `PB-001` - product taxonomy;
- `PB-002` - Platform buying triggers and use cases;
- `PB-003` - Agents/Forum boundaries;
- `PB-005` - product proof evidence;
- `PB-006` - regulatory/procurement wording;
- `PB-007` - packaging matrix;
- `PB-008` - `/agents/` vs `/aiagents/`;
- `CJM-001` - fit guide final review;
- `CJM-002` - security/procurement journey;
- `CJM-003` - use-case pilotability review.

## Approval Model

| Decision area | Primary owner | Secondary owner | Required status before public TO BE release |
|---|---|---|---|
| Taxonomy and one-liners | PM | Sales | Approved |
| Product boundaries | PM | Product + SEO | Approved |
| Platform triggers | PM | Architect | Approved |
| Dev workflows | PM | Tech Lead | Approved via `17-local-gap-decision-briefs.md` |
| Proof/evidence matrix | PM | Sales + Legal | Approved or explicitly hidden |
| Regulatory/procurement wording | Legal | Security + PM | Approved or explicitly safe-copy only |
| Packaging matrix | PM | Sales + Architect | Approved |
| `/agents/` vs `/aiagents/` | SEO | PM | Approved canonical/compatibility decision |
| Fit guide | UX | PM | Approved |
| Procurement journey | Security | PM + UX | Approved |
| Use-case anatomy | PM | Content | Approved |

## Taxonomy Baseline

| Product | Category | One-liner draft | Primary buyer | Boundary |
|---|---|---|---|---|
| Tacticum Platform | Enterprise AI platform | Инфраструктурное ядро для AI-приложений: LLM gateway, RAG, MCP, governance, интеграции, аудит and rollout control. | CIO, CTO, enterprise architect, AI platform owner | Not a single chatbot, not a custom one-off integration, not a public LLM wrapper |
| Tacticum Agents | Internal AI assistants | Корпоративные AI-агенты для внутренних функций, знаний and workflows поверх общей Platform-инфраструктуры. | HR/legal/finance/support owners, CIO/CDO sponsor | Not a customer forum/chat product and not an uncontrolled bot constructor |
| Tacticum Dev | AI-assisted engineering workflow | Управляемые AI-workflows для разработки: requirements, architecture, design compliance, quality gates and implementation support. | CTO, Head of Development, engineering excellence, architect | Not staff reduction promise, not generic vibe-coding training, not unmanaged code generation |
| Tacticum Forum | Dialog platform | Управляемая диалоговая платформа для клиентских коммуникаций: сценарии, LLM assistance, moderation, routing and quality control. | CX, contact center, digital channels, support operations | Not an internal assistant catalog and not a pure LLM chatbot |

Decision needed for `PB-001`:

- approve names exactly as public product names;
- approve whether Russian descriptors are used next to English product names;
- approve one-liners for public hero/meta/navigation;
- confirm whether "экосистема AI-программ" remains the umbrella phrase.

## Platform Buying Triggers

| Trigger | Buyer pain | Platform response | Proof needed before strong public claim |
|---|---|---|---|
| Several teams launch AI pilots independently | Duplicated LLM/RAG/connectors/audit work and no shared governance | Shared runtime, gateway, RAG, MCP, logging and deployment model | Architecture diagram and implementation examples |
| Regulated or enterprise environment | Public SaaS tools are hard to approve for data, audit and access control | Deployment in agreed contour, RBAC/tenancy, audit and integration governance | Security/legal wording approval |
| Need to scale from pilot to production | Pilot scripts are not supportable as products | Rollout model: discovery -> pilot -> integration -> production decision | Pilot-to-rollout case evidence |
| Multiple applied AI products planned | Each product would otherwise build its own foundation | Platform reused by Agents, Dev and Forum | Product architecture explanation |
| AI cost and provider policy need control | Teams use different providers without visibility | Gateway/provider policy and observability framing | Confirmed implementation scope and non-PII metrics |

Decision needed for `PB-002`:

- approve top 3 public Platform triggers;
- decide how much technical architecture is public vs NDA;
- confirm that cost/control wording does not imply unsupported guarantees.

## Product Boundary Decisions

### Agents vs Forum

| Question | Tacticum Agents | Tacticum Forum |
|---|---|---|
| Primary context | Internal business functions and employee workflows | External or semi-external customer/community dialog |
| Main user | Department employee, expert, internal operator | Customer, citizen, community member, contact center operator |
| Typical data | Corporate knowledge, policies, process documents, internal systems | Dialog history, scenario graph, support content, channel context |
| Control model | Role-based assistant catalog, knowledge boundaries, workflow ownership | Scenario graph, routing, moderation, escalation, dialog journal |
| Pilot question | Which internal function and knowledge base should we automate first? | Which customer flow or request stream should be controlled first? |

Draft public boundary:

- Agents: "внутренние AI-ассистенты для рабочих функций и знаний";
- Forum: "диалоговая платформа для клиентских коммуникаций и управляемых сценариев";
- if both are relevant, Platform can be positioned as the common foundation.

Decision needed for `PB-003`:

- PM/Product approves public comparison copy;
- SEO approves cross-linking between Agents and Forum;
- Designer keeps comparison block visible enough on product pages.

### `/agents/` vs `/aiagents/`

Draft recommendation for review:

| URL | Role | Draft canonical direction | Notes |
|---|---|---|---|
| `/agents/` | Product page for Tacticum Agents | Primary product URL | Should be in product navigation, sitemap and product schema |
| `/aiagents/` | Existing lead-gen / legacy AI agents page | Compatibility route until SEO traffic/lead evidence is reviewed | Keep differentiated intent or prepare canonical/redirect plan |

Decision options:

| Option | When to choose | Risk |
|---|---|---|
| Keep both with differentiated intent | `/aiagents/` has useful existing traffic or broader query intent | Requires unique content and clear cross-linking |
| Canonical `/aiagents/` to `/agents/` | Content becomes substantially duplicate | Could lose broad SEO intent if not migrated carefully |
| 301 redirect `/aiagents/` to `/agents/` | Legacy page is no longer needed and traffic impact is accepted | Requires traffic/CRM evidence and rollback plan |

Decision needed for `PB-008` / `SEO-TOBE-002`:

- SEO reviews traffic, rankings, leads and duplicate risk;
- PM approves product navigation role;
- implementation waits for SEO decision, not just visual redesign.

## Proof And Claims Evidence Matrix

`PB-005` and `PB-006` remain blocked until external evidence exists. Use this matrix to decide what can appear publicly.

| Claim family | Example public desire | Required evidence | Current safe treatment |
|---|---|---|---|
| Product readiness | "готовый продукт" / "production-ready" | Product readiness matrix, supported environments, owner approval | Use "пилот / внедрение / production decision по результатам проверки" |
| Registry/trusted software | Реестр, доверенное ПО, ПП №1937 | Legal docs, registry status, exact date/status | Use "проектируется/готовится с учетом требований" only if approved |
| Security/regulatory | ФЗ-152, ФЗ-187, ФСТЭК/ФСБ, КИИ | Legal/security review, architecture summary, scope limits | Use "поддерживает сценарии размещения с учетом требований" |
| Deployment | on-prem, isolated contour, sovereign LLM, PAK | Deployment spec, tested configurations, commercial packaging | Use "доступно при согласованной архитектуре/контуре" |
| Performance | automation rate, lead time, TCO, FCR | Case sheet, methodology, date, context, permission | Hide numbers or mark as pilot-specific after approval |
| Logos/testimonials | Named customers and people | Written permission, approved wording, PII review | Hide until permission exists |
| Connectors/channels | MAX, VK Teams, SIP, CRM connectors | Readiness table with status/version/scope | Split into ready / pilot / roadmap only after approval |
| SLA/support | Bronze/Silver/Gold, guaranteed response | Approved tariff/SLA documents | Say "support model is defined during commercial proposal" |

Evidence sheet template:

| Product | Claim ID | Claim text | Source/evidence | Owner | Public status | Approved wording |
|---|---|---|---|---|---|---|
| Platform | TBD | TBD | TBD | PM + Legal/Security | blocked | Do not publish |
| Agents | TBD | TBD | TBD | PM + Sales/Legal | blocked | Do not publish |
| Dev | TBD | TBD | TBD | PM + Tech Lead | blocked | Do not publish |
| Forum | TBD | TBD | TBD | PM + Sales/Legal | blocked | Do not publish |

Decision needed:

- PM/Sales/Legal fill source rows;
- Legal/Security approve public wording;
- Designer uses proof/status UI only after statuses exist.

## Packaging Matrix

| Package | Public meaning | Good fit | Public wording allowed now | Needs approval before strong claim |
|---|---|---|---|---|
| Discovery / assessment | Short diagnostic of scenario, data, risks and rollout path | Early buyer, unclear scope | "помогаем выбрать сценарий и путь пилота" | Fixed scope/duration/price |
| Fixed pilot | Limited scenario with acceptance criteria | Buyer needs low-risk validation | "ограниченный пилот с согласованными критериями" | Metrics, automation percentage, production guarantee |
| SaaS | Hosted use where acceptable | Non-regulated or lower-risk workflows | "формат поставки зависит от требований контура" | Hosting details, regions, security docs |
| On-prem | Deployment in customer contour | Regulated enterprise, data restrictions | "on-prem обсуждается в рамках архитектуры" | Supported versions, infra requirements, support model |
| Hybrid | Mixed cloud/on-prem architecture | Existing systems and staged rollout | "гибридная архитектура проектируется под контур" | Clear responsibility matrix |
| PAK | Hardware/software package | Procurement-driven environments | Do not use as confirmed offering until approved | PAK spec, legal/commercial decision |
| Implementation/integration | Services around product deployment | Most enterprise clients | "внедрение и интеграция входят в путь запуска" | Fixed commercial terms |
| Support/SLA | Post-launch support | Production rollout | "модель поддержки фиксируется в предложении" | SLA tiers and legal text |

Decision needed for `PB-007`:

- approve which packages are public;
- approve which remain private/NDA;
- confirm whether `/price/` shows packaging or only team/project composition.

## Fit Guide Review

Current product pages already include fit guide blocks. Review them against these criteria for `CJM-001`:

| Criterion | Required answer |
|---|---|
| Product route clarity | Can a user choose Platform/Agents/Dev/Forum without a call? |
| Negative fit clarity | Is "not for you if..." safe and helpful, not dismissive? |
| CTA alignment | Does each fit outcome lead to the right next step? |
| No risky promise | Does the guide avoid guaranteed readiness, metrics or legal claims? |
| Mobile scanability | Can the guide be read without large comparison tables? |

Decision needed:

- UX/PM approve current fit guide logic or request copy/design changes.

## Procurement Journey Review

Current product pages use safe-copy procurement blocks. Review them against these criteria for `CJM-002`:

| Step | Public page should answer | Must not claim without evidence |
|---|---|---|
| Data contour | What data/integrations need discussion | Certified compliance, full isolation, registry status |
| Access model | Who owns roles, permissions and review | Ready-made universal RBAC for every client |
| Logs/audit | What needs to be logged and reviewed | Specific regulatory sufficiency |
| Integration | Which systems are in scope | Ready connector availability without table |
| Handoff | How pilot moves to production decision | Guaranteed production launch |

Decision needed:

- Security/Legal approve wording;
- UX decides whether procurement path stays as section, CTA variant or separate downloadable package later.

## Use-Case Pilotability Review

Current product pages include use-case anatomy. Review every public use case for `CJM-003`:

| Field | Good use-case standard |
|---|---|
| Trigger | A buyer recognizes when the scenario starts |
| Owner | A real role can sponsor or operate it |
| Input | Pilot input is realistic and non-sensitive in public wording |
| Output | Output is a reviewable artifact, not an unsupported result guarantee |
| Limitation | Boundary is explicit enough to prevent over-selling |
| CTA | Next step is product-specific and low-risk |

Decision needed:

- PM/Content approve current use cases;
- Sales confirms they match likely discovery conversations;
- evidence-heavy use cases stay safe-copy until proof matrix is filled.

## Phase 1 Closure Checklist

| Gap | Close only when |
|---|---|
| `PB-001` | taxonomy and one-liners are approved by PM + Sales |
| `PB-002` | Platform top triggers and public use cases are approved by PM + Architect |
| `PB-003` | Agents/Forum boundary copy is approved by PM/Product/SEO |
| `PB-005` | product proof evidence matrix has source, owner and approved wording |
| `PB-006` | legal/security claims have approved safe wording or are hidden |
| `PB-007` | packaging matrix is approved for public/private/NDA split |
| `PB-008` | `/agents/` vs `/aiagents/` canonical/compatibility decision is approved |
| `CJM-001` | fit guide logic is approved by UX + PM |
| `CJM-002` | procurement path is approved by Security/Legal + UX |
| `CJM-003` | use cases are approved by PM + Content and risky proof is blocked |

## Recommended Review Session

1. PM leads taxonomy, packaging and product boundaries.
2. Architect validates Platform triggers, packaging feasibility and on-prem/hybrid wording.
3. Legal/Security classifies claims into public / rewrite / private / blocked.
4. SEO decides `/agents/` vs `/aiagents/` direction and metadata implications.
5. UX/Designer marks fit guide, procurement journey and use-case blocks as keep/rewrite/redesign.
6. After approvals, update `14-gap-backlog-and-decision-register.md`, `16-gap-closure-action-register.json`, product page copy and relevant guards.
