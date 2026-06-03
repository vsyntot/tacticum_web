# 25. Post-Challenge Use Cases And CJM Detail

Дата: 02.06.2026

Статус: детализированный CJM/use-case brief по итогам challenge. Использовать как вход для PM, UX, Sales, Content and Designer.

## Назначение

Текущие product pages уже имеют use cases, fit guide, comparison, procurement, rollout and product-aware CTA. Challenge показал, что следующий уровень зрелости - не "добавить больше блоков", а сделать сценарии decision-grade.

Целевая логика:

```text
роль
  -> ситуация / trigger
  -> подходящий продукт
  -> готовность к пилоту
  -> проверяемый артефакт
  -> proof status / limitation
  -> правильный next step
```

## Pilot Kit Standard

Каждый TO BE use case должен быть оформлен как pilot kit, а не как обычная карточка возможностей.

| Field | Required content | Why |
|---|---|---|
| Trigger | Что произошло у клиента и почему сценарий стал актуален | Buyer узнает свою ситуацию |
| Buyer / owner | Кто может спонсировать и операционно владеть пилотом | Sales понимает, с кем говорить |
| Current pain | Что ломается в текущем процессе | Убирает абстрактный product-speak |
| Product fit | Почему подходит Platform / Agents / Dev / Forum | Помогает выбрать продукт |
| Pilot readiness | Какие данные, документы, роли, системы нужны до старта | Отсекает неподготовленные заявки |
| Pilot input | Что клиент предоставляет в пилот | Делает следующий шаг конкретным |
| Pilot output | Какой артефакт клиент получает | Не обещает неподтвержденный результат |
| Proof status | `available`, `pilot-artifact`, `needs-evidence`, `private-nda`, `not-supported` | Не смешивает evidence and aspiration |
| Limitation | Что не обещаем без assessment/evidence | Снижает legal/sales risk |
| CTA | Какой безопасный следующий шаг | Улучшает qualification |

## Role-Based CJM

### Economic Buyer

| Step | Current site coverage | TO BE challenge | Required output |
|---|---|---|---|
| Understand ecosystem | Homepage explains Platform / Agents / Dev / Forum | Needs stronger vendor model, not just product naming | Ecosystem map with product and delivery relationship |
| Choose direction | Fit matrix exists | Needs role/stage interpretation | Product route selector by situation |
| Validate business path | Rollout/proof readiness exists | Proof not yet evidence-backed | Pilot/package/proof status matrix |
| Convert | Product CTA exists | CTA still mostly general lead form | Pilot/discovery/architecture CTA taxonomy |

### Technical Buyer

| Step | Current site coverage | TO BE challenge | Required output |
|---|---|---|---|
| See architecture | Text layers exist | Not diagram-grade | Architecture/data-flow diagram |
| Check integration boundaries | Procurement copy exists | Connectors/deployment statuses not approved | Integration readiness status model |
| Understand data/access | Safe-copy exists | Needs data/RBAC/audit detail without risky claims | Security/procurement review path |
| Convert | Architecture/session CTA in copy | Needs returning-lead/docs request path | Architecture session or documentation request CTA |

### Security / Procurement

| Step | Current site coverage | TO BE challenge | Required output |
|---|---|---|---|
| Understand risk areas | Procurement block exists | No formal public/private evidence split | Claim/evidence matrix |
| Request docs | Generic form path | No dedicated docs/security request path | Gated or CTA-based procurement request |
| Review wording | Safe-copy only | Legal/security wording not approved | Approved wording table |
| Close loop | External manual process | No tracked returning journey | Returning-lead path and owner |

### Functional Owner

| Step | Current site coverage | TO BE challenge | Required output |
|---|---|---|---|
| Recognize scenario | Use-case cards exist | Need realistic owner/input/output | Pilot kit per use case |
| Understand effort | Rollout text exists | Need readiness checklist | "What you need for pilot" checklist |
| See outcome | Proof readiness exists | Need artifact-based output | Pilot output examples |
| Convert | Scenario select exists | Needs scenario-specific next steps | Product/use-case CTA mapping |

### Returning Lead

Current risk: returning users still see mostly the same generic conversion path.

Target returning-lead routes:

| Returning intent | Target action | Related gaps |
|---|---|---|
| Already chose product | Jump to product CTA with selected scenario | `CJM-005`, `CJM-006` |
| Needs architecture/security discussion | Request architecture/procurement session | `CJM-002`, `CJM-005` |
| Needs materials for internal approval | Request private/NDA proof or docs | `PB-005`, `PB-006`, `UI-005` |
| Has estimate from `/offer/` or `/calculator/` | Continue with context-aware lead form | `CJM-006`, `REL-003` |

## Product-Specific Challenge

### Tacticum Platform

Current strength: Platform has a clear infrastructure narrative: LLM Gateway, RAG, MCP, RBAC, audit, observability.

Challenge: Platform may still look like an architecture layer rather than a purchasable product.

Required sharpening:

| Need | Target detail |
|---|---|
| Buying triggers | portfolio consolidation, controlled LLM gateway, RAG governance, audit/observability, platform reuse |
| Pilot artifacts | AI scenario portfolio map, reusable layer map, deployment constraints, ownership model |
| Proof status | implementation examples or private architecture artifacts if public proof is unavailable |
| CTA | architecture assessment, platform pilot, deployment readiness |
| Red line | no promise of SaaS/on-prem/PAK/SLA/certification before evidence |

### Tacticum Agents

Current strength: Agents has clear internal assistant scenarios for HR, legal, finance, support, IT and knowledge.

Challenge: Agents can be confused with `/aiagents/` or Forum if the page reads like "AI bots".

Required sharpening:

| Need | Target detail |
|---|---|
| Boundary | internal function assistants, documents, access, handoff |
| Pilot readiness | documents, FAQ, owner, control questions, escalation rules |
| Pilot artifacts | test question set, answer quality review, handoff rules, knowledge gaps |
| CTA | scenario selection, RAG documents check, pilot rollout |
| Red line | no automation percentage, legal decision, ready integration or full rollout claim |

### Tacticum Dev

Current strength: Dev is meaningfully differentiated around AI-assisted workflow governance.

Challenge: without concrete workflows it can look like consulting, training or generic AI coding.

Required sharpening:

| Need | Target detail |
|---|---|
| Workflows | workflow governance, design-system compliance, brownfield refactor, requirements-to-tests, stack profiles |
| Pilot readiness | one team, one stack, one workflow, ADR/design/test context |
| Pilot artifacts | analysis gate, quality gate map, review protocol, regression-risk map |
| CTA | workflow assessment, quality gates pilot, design-system guardrails |
| Red line | no staff reduction, universal speedup or quality percentage claim |

### Tacticum Forum

Current strength: Forum is separated from pure LLM bot by scenario control, journal, escalation and analytics.

Challenge: it needs a stronger visual flow model, otherwise it can still read as another chatbot product.

Required sharpening:

| Need | Target detail |
|---|---|
| Flow model | channel -> scenario graph -> LLM enrichment -> checkpoint -> escalation -> journal/analytics |
| Pilot readiness | one channel, one request stream, rules, critical answers, event model |
| Pilot artifacts | flow map, escalation rules, event list, drop-off map, need catalog |
| CTA | dialog flow assessment, scenario+LLM pilot, support analytics review |
| Red line | no automation rate, all-channel readiness or production load promise |

## CTA Taxonomy

Current product pages already pass controlled `lead_scenario` and `lead_*` context. Keep this for v1.

Recommended CTA model:

| CTA family | Use when | Payload rule |
|---|---|---|
| `pilot` | buyer is ready to test one scenario | controlled `lead_scenario`; no new endpoint |
| `architecture-session` | technical/security concerns dominate | current form with `lead_next_step`; no new docs download yet |
| `scenario-selection` | product is relevant but use case unclear | controlled select and free text message |
| `documentation-request` | procurement/security materials needed | do not add file/download flow without approval |
| `estimate` | buyer needs budget/commercial route | route to `/offer/`, `/calculator/` or `/price/` |

Do not add new required fields or upstream payload fields until `ARCH-003` / `CJM-006` are approved.

## Acceptance Criteria

TO BE CJM/use-case layer is ready for design when:

- each product has at least 3 approved pilot kits;
- each pilot kit has owner, readiness, input, output, limitation and proof status;
- each buyer role has a clear path to relevant block or CTA;
- returning-lead path is explicitly designed;
- CTA taxonomy preserves current form contracts unless Security / Integration lane approves changes;
- Sales confirms that submitted context is useful for routing;
- PM/Content approve public wording;
- Legal/Security approve procurement/proof wording.
