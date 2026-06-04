# Product CJM, CTA And CRM Qualification Decision Pack

Дата: 04.06.2026
Статус: draft pending PM/UX/Sales/Security/Analytics approval
Sprint: `docs/workflow/sprints/2026-06-04-sprint-19-cjm-cta-crm-qualification.md`

## Purpose

Этот документ фиксирует Sprint 19 baseline для enterprise CJM, CTA taxonomy, returning-lead path, product pilot kits, success-state copy, CRM/upstream qualification and product funnel analytics.

Документ не меняет runtime form contract, upstream JSON fields, endpoint response shape, analytics implementation or public UI. Он задает approval package and implementation gates. Любые payload/CRM изменения после этого документа являются отдельной Security / Integration задачей.

## Covered Gaps

| Gap | Sprint Item | Current Output | Remaining Gate |
|---|---|---|---|
| `UX-001` | S19-001 | Role-based CJM draft | PM + UX + Sales approval |
| `UX-007` | S19-002 | Procurement/security journey draft | PM + Security + UX approval |
| `UX-008` | S19-003 | Product pilot kit draft | PM + Content + Sales approval |
| `UX-009` | S19-004 | Static fit-guide v1 recommendation | PM + UX approval |
| `UX-002` | S19-005 | CTA taxonomy draft | PM + UX + Sales approval |
| `UX-003` | S19-006 | Returning-lead path draft | PM + Sales approval |
| `UX-010` | S19-007 | Success-state copy matrix | PM + UX + QA approval |
| `CMP-003` | S19-008 | Component parameter decision | Frontend + Backend + PM approval |
| `ARCH-005` | S19-009 | CRM fallback decision draft | Sales/CRM/upstream approval |
| `ARCH-006` | S19-010 | No-PII funnel goal map | Analytics + QA approval |
| `CFG-004` | S19-011 | Config evidence rule | DevOps + Backend approval |

## Current Contract Baseline

| Area | Current State | Sprint 19 Rule |
|---|---|---|
| Default form endpoint | `/local/rest/tacticum_form.php` | Keep response shape and validation model |
| Product context | `lead_*` fields from `tacticum:lead.cta` and product data | Keep controlled values; no free-text analytics params |
| CRM/upstream qualification | Backend builds canonical profile and appends safe text to upstream `task` | Do not send top-level structured CRM fields until Sales/upstream approval |
| Product scenario select | `SCENARIO_OPTIONS` on product pages | Keep optional; no required fields |
| Product analytics | `product`, `page_role`, `scenario`, `form_id`, endpoint/status/code | No PII, no budget/timeline/offer title/code/industry/message |
| Success UI | Generic toast and reset | Copy can be specified, but implementation is Sprint 20/UI scope |
| Config evidence | `config:runtime:check` target command exists | Attach explicit/default summary to future release evidence when config-affecting changes ship |

## Role-Based CJM Draft

| Buyer Role | Trigger | Primary Concern | Proof Need | Recommended CTA | Sales Routing Note |
|---|---|---|---|---|---|
| CEO / Business Owner | Wants business AI outcome, cost control or new channel | Business impact, time-to-value, responsibility, risk | Safe pilot scope, expected artifacts, commercial next step | `Обсудить пилот` / `Получить формат первого этапа` | Route to PM/Sales discovery; avoid technical deep dive first |
| CIO / CTO | Needs architecture, integration and scaling plan | Runtime, data, existing systems, ownership, maintainability | Architecture session, integration map, technical constraints | `Архитектурная сессия` | Route to Architect + Backend; collect systems/integration context |
| CISO / Security | Needs data/control assurance before any AI rollout | Data boundaries, access, logs, model/provider handling, PII | Security/procurement checklist and data-flow discussion | `Обсудить контур данных и доступа` | Route to Security/Architect; no public certification promises |
| Procurement / Legal | Needs vendor fit and contract/process clarity | Legal entity, scope, responsibility, support, procurement docs | Company/legal details, delivery model, support boundaries | `Запросить вводные для закупки` | Route to PM + Legal/Sales; private docs only after access model |
| Product Owner / Business Function Owner | Has process/use case and wants pilot | Use-case fit, users, inputs, handoff, success criteria | Product pilot kit and expected output | `Выбрать сценарий пилота` | Route to PM + Analyst; collect process owner and artifacts |

Role journey rule: public pages may guide the next step, but should not imply that all procurement/security artifacts, certifications, SLA or private proof are immediately public.

## Procurement / Security Journey Draft

| Step | User Need | Safe Public Response | Owner |
|---|---|---|---|
| 1. Identify AI/data contour | Which data, systems and users are involved | Architecture/security session CTA; no blanket compatibility promise | PM + Architect |
| 2. Define access and audit | Who can see, edit, approve and audit answers | Discuss RBAC, logs, ownership and review checkpoints | Security + Architect |
| 3. Review integrations | Which internal systems/tools are touched | Integration map and constraints review | Backend + Architect |
| 4. Agree pilot boundary | What can be checked without production risk | Pilot kit with inputs, outputs and limitations | PM + QA |
| 5. Decide private docs | What can be shared under request/NDA | Private evidence/docs path only after Sprint 22 access model | PM + Legal + Security |

Blocked public wording:

- public certification/registry claims without source;
- `SLA`, `24/7`, guaranteed response/support times;
- ready on-prem/hybrid/PAK availability without Sprint 18 packaging approval;
- private architecture/security documents exposed without access model.

## Product Pilot Kits Draft

| Product | Pilot Input Artifacts | Owner Responsibilities | Expected Output | Limitation | Evidence Status |
|---|---|---|---|---|---|
| Platform | AI scenario portfolio, system map, data/RAG sources, access model assumptions | Provide systems/data owners and target scenarios | Reusable platform layer map, minimum runtime scope, production-readiness roadmap | Does not prove all future products or production load | `pilot-artifact` |
| Agents | One business function, knowledge/documents, handoff rules, answer quality criteria | Provide process owner, docs, reviewers and escalation path | First assistant scenario, knowledge setup checklist, answer review plan | Does not replace full bot/contact-center platform | `pilot-artifact` |
| Dev | One team/workflow, repo/design-system rules, QA gates, review process | Provide codebase/process owner and quality criteria | AI-assisted workflow governance map, rule/profile draft, quality-gate plan | Does not promise productivity uplift or autonomous development | `pilot-artifact` |
| Forum | One customer/support flow, channels, escalation rules, dialog analytics needs | Provide flow owner, operator feedback and sample intents | Scenario graph outline, LLM enrichment boundary, dialog journal metrics | Does not guarantee channel coverage or accuracy without validation | `pilot-artifact` |

Pilot kit rule: pilot copy can describe what will be checked and produced. It must not publish performance metrics, customer outcomes, logos or contractual guarantees without Sprint 17/18 evidence approval.

## Product Fit Guide Decision

Recommendation: keep product fit guide static in v1.

Rationale:

- current product pages already expose `fit_guide` blocks through product content schema;
- interactive fit would create new UX state, analytics decisions and possibly new lead qualification fields;
- role-based CJM and CTA matrix should be approved before adding guided selection UI.

Revisit trigger: if PM/UX wants role/industry/constraint-based guided selection, open a Full Feature + Security review for payload, analytics, accessibility and smoke coverage.

## CTA Taxonomy Draft

| Stage | User Intent | CTA Family | Current Contract Mapping | Implementation Rule |
|---|---|---|---|---|
| Explore | Understand which product fits | `Сравнить продукты`, `Выбрать сценарий` | Product page links and optional `lead_scenario` | Keep as navigation or controlled select |
| Pilot | Check one product/use case | `Обсудить пилот`, `Выбрать сценарий пилота` | `lead_product`, `lead_scenario`, `lead_next_step` | Use existing `tacticum:lead.cta` params |
| Architecture | Assess data/runtime/integration | `Архитектурная сессия` | `lead_next_step=architecture-session` or product-specific next step | No new upstream fields without approval |
| Procurement/Security | Discuss data, access, docs, owner model | `Обсудить контур данных и доступа` | Text fallback in `task` only | Do not create private docs flow before Sprint 22 |
| Team / Delivery | Staff product workstream | `Подобрать команду`, `Оценить состав` | `/price/`, `price-cta`, `price-specialist` | Preserve staff endpoint and workers payload |
| Estimate | Convert example/calculator into lead | `Уточнить оценку`, `Получить расчет` | `/offer/`, `/calculator/`, `group_id`, offer context | Keep no-PII analytics; no raw message params |
| Returning Lead | Continue existing conversation | `Уточнить пилот`, `Запросить архитектурную сессию` | Current fallback text only | Needs Sales approval before special routing |

Current product CTAs remain valid:

| Product | Form ID | Current Scenario Values | Recommendation |
|---|---|---|---|
| Platform | `platform-cta` | `platform-assessment`, `platform-pilot`, `deployment-readiness` | Keep; maps to architecture/pilot readiness |
| Agents | `agents-cta` | `agent-scenario-selection`, `rag-documents-check`, `pilot-rollout` | Keep; maps to business assistant pilot |
| Dev | `dev-cta` | `ai-workflow-assessment`, `quality-gates-pilot`, `design-system-guardrails` | Keep; maps to engineering governance |
| Forum | `forum-cta` | `dialog-flow-assessment`, `scenario-llm-pilot`, `support-analytics-review` | Keep; maps to customer dialog flow |

## Returning-Lead Path Draft

| Returning Situation | Safe Next Step | Current Contract Support | Pending Decision |
|---|---|---|---|
| Already had discovery call | Architecture/session refinement | Free-form `message` plus `lead_*` context | Sales routing rule |
| Needs proof or references | Public/private evidence discussion | Safe text fallback only | Sprint 17/18 proof approval and Sprint 22 access model |
| Wants pilot scope refinement | Pilot kit review | Product CTA and scenario select | PM/Sales owner path |
| Has AI chat/calculator context | Handoff with `group_id` and scoped prefill | Existing prefill and retry without `group_id` | No change needed |
| Needs procurement docs | Request discussion, not automatic download | Existing form only | Private document flow in Sprint 22 |

Recommendation: do not add a separate returning-lead form mode yet. Use copy and Sales routing until CRM/upstream structured fields are approved.

## Success-State Copy Matrix

Current UI shows generic toast. Sprint 19 recommends copy targets for future Sprint 20 UI/state design:

| Context | Success Copy Direction | Do Not Say |
|---|---|---|
| Product pilot CTA | `Заявка отправлена. Вернемся с вопросами по сценарию и формату пилота.` | `Пилот запущен`, `результат гарантирован` |
| Architecture/security CTA | `Запрос получен. Подготовим вопросы по данным, доступам и интеграциям.` | `Документы доступны сразу`, certification claims |
| `/price/` team CTA | `Заявка отправлена. Уточним состав, загрузку и формат подключения.` | `Команда забронирована`, fixed availability |
| Offer/calculator | `Заявка отправлена. Уточним вводные и подготовим персональную оценку.` | `Финальная смета готова` |
| Contact/modal | `Обращение отправлено. Направим его ответственному специалисту.` | CRM/internal queue details |
| Error | `Не удалось отправить форму. Попробуйте позже.` | Raw upstream response, stack trace, config names |

Implementation rule: success states can be customized later through component/UI state spec, but must not expose CRM internals, private evidence or raw request data.

## CRM / Upstream Qualification Decision Draft

Recommendation: keep current text fallback v1 until Sales/upstream approval.

Current flow:

1. Frontend sends allowlisted `lead_*` fields and optional controlled `lead_scenario`.
2. Backend builds canonical profile with `tacticum_form_build_lead_profile(...)`.
3. Backend appends safe `Контекст заявки` block to existing upstream `task`.
4. Backend does not send `product_interest`, `use_case_interest`, `deployment_interest`, `budget_band` or similar top-level upstream JSON fields.

Why keep fallback now:

- external CRM/upstream schema is not approved in this sprint;
- existing endpoint response shape and legacy alias compatibility stay stable;
- text fallback already improves Sales context without creating partial structured integration.

Structured field migration requires:

| Required Item | Owner | Evidence |
|---|---|---|
| Upstream accepts structured fields and ignores/handles unknowns safely | Backend + upstream owner | API/schema evidence |
| Sales confirms field usefulness and routing | Sales + PM | Approval note |
| Security/QA approve no-PII scope and field lengths | Security + QA | Test cases |
| `lead-form-contract.md` updated | Backend | Contract diff |
| Manual/staging success-flow evidence | QA | Safe release evidence |

Blocked without approval: top-level upstream fields, CRM-specific field names, hidden role/stage fields that are not visible in contract, analytics params containing budget/timeline/industry/offer/user text.

## Product Funnel Analytics Goal Map

| Funnel Moment | Existing / Proposed Event | Allowed Params | Notes |
|---|---|---|---|
| Product page view | `tacticum_product_view` | `product`, `page_role`, `page_path` | Existing taxonomy |
| Product CTA click | `tacticum_product_cta_click` | `product`, `page_role`, `cta`, `page_path` | Existing taxonomy |
| Product form submit | `tacticum_product_form_submit` | `product`, `page_role`, `scenario`, `form_id`, `endpoint`, `page_path` | Existing no-PII payload |
| Product form success | `tacticum_product_form_success` | same plus `status` | Existing no-PII payload |
| Product form error | `tacticum_product_form_error` | same plus `status`, `code` | Existing no raw response |
| AI chat send/success/error | `tacticum_chat_*` | `surface`, `status`, `code`, boolean flags, `page_path` | Existing no message text |
| Prefill submit/success/error | `tacticum_prefill_*` | `surface`, `status`, `code`, `page_path` | Existing no payload text |
| Chat lead handoff | `tacticum_chat_lead_handoff` | `surface`, `has_group_id`, `has_prefill_summary`, `page_path` | Existing boolean-only handoff |

Do not send to analytics:

- name, phone, email, company;
- message, prompt, summary, chat answer;
- raw `page_url` with query;
- `lead_budget`, `lead_timeline`, `lead_industry`;
- `lead_offer_code`, `lead_offer_title`;
- raw CRM/upstream response.

Approval needed: Analytics/Metrika owner must confirm goal mapping in UI without storing raw params in docs.

## Config Evidence Rule

For future releases touching product forms, CRM/upstream, AI endpoints, product source or security mode, attach target `config:runtime:check` safe summary:

```bash
npm run config:runtime:check
```

Evidence must include only:

- scope status;
- explicit/default status;
- product source/cache/schema summary;
- AI endpoint path explicit/default status;
- CSP mode;
- no secret values, no raw config, no PII.

## Implementation Gates

Do not implement runtime changes until:

1. PM/UX/Sales approve role CJM and CTA taxonomy.
2. Sales approves current fallback or structured CRM fields are scoped.
3. Security/QA approve any structured payload or private/procurement flow.
4. Analytics approves goal map and no-PII evidence model.
5. Design accepts success-state requirements for Sprint 20.

If implementation changes forms/chat/analytics/product CTAs, run:

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
npm run js:check
```

If deploy/runtime behavior changes, add:

```bash
npm run browser:smoke:prod
npm run release:manual-gates:helper
npm run metrika:goals:helper
```

## Approval Checklist

| Role | Must Approve | Evidence Format |
|---|---|---|
| PM | Role CJM, pilot kits, CTA matrix, success copy | Safe approval note |
| UX | Journey, returning-lead path, success-state requirements | Design-ready notes |
| Sales | Qualification usefulness, fallback vs structured fields | Safe Sales approval; no CRM raw data |
| Security | Procurement path and payload/private-flow boundaries | Security review note |
| Backend | Lead contract and upstream migration gate | Contract/update plan |
| QA | Smoke scope for old/new forms, chat and prefill | Command list and safe summaries |
| Analytics | Goal/event map and no-PII params | Metrika evidence without raw params |

## Open Decisions

| Decision | Status | Owner |
|---|---|---|
| Sales accepts text fallback as v1 or requires structured CRM fields | pending | Sales + PM + Backend |
| Returning-lead path needs separate form mode or remains copy/routing only | pending | PM + Sales + UX |
| Product fit guide remains static or becomes guided/interactive | pending | PM + UX |
| Success-state copy becomes component parameter or design-system state spec | pending | UX + Frontend + QA |
| Config runtime evidence is mandatory for which release scopes | pending | DevOps + Backend + PM |
