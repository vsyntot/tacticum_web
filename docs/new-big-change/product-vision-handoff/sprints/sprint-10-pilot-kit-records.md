# Sprint 10 Pilot Kit Records

Дата: 02.06.2026

Статус: ready-for-owner-review drafts. Эти pilot kits являются recommended v1 baseline for PM/UX/Sales/Content review. They are not approved public copy until owners confirm.

## Pilot Kit Field Contract

| Field | Required |
|---|---|
| Trigger | Buyer-recognizable event or situation |
| Buyer / owner | Sponsor or operating owner |
| Current pain | Why current process/tooling fails |
| Product fit | Why this Tacticum product is relevant |
| Pilot readiness | What must exist before pilot starts |
| Pilot input | What customer provides |
| Pilot output | Reviewable artifact, not guaranteed outcome |
| Proof status | `pilot-artifact`, `available`, `needs-evidence`, `private-nda`, `not-supported` |
| Limitation | What is not promised |
| CTA | Controlled next step, compatible with current forms |

## Status Legend

| Status | Meaning |
|---|---|
| `draft` | locally prepared, not approved |
| `approved` | approved for public/design/implementation use |
| `approved-v1-safe` | safe v1 wording approved, stronger proof remains blocked |
| `rewrite-required` | direction accepted, copy must change |
| `sprint-09-blocked` | depends on taxonomy/claims/packaging approval |
| `evidence-blocked` | depends on source/proof |
| `rejected` | do not use |

## Platform Pilot Kits

### Platform Kit 1 - AI Portfolio Consolidation

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | Several teams launch separate AI/RAG/bot initiatives with duplicated infrastructure. |
| Buyer / owner | CIO, CDO, enterprise architect, AI platform owner. |
| Current pain | Model access, documents, tools, audit and cost tracking are implemented differently by each team. |
| Product fit | Platform gives a shared runtime/data/access/ops layer for multiple AI products. |
| Pilot readiness | Current AI scenario list, systems, data sources, owners, access constraints. |
| Pilot input | 3-5 current or planned AI scenarios, architecture notes, security constraints. |
| Pilot output | Reusable layer map, shared service candidates, first platform contour backlog. |
| Proof status | `pilot-artifact` |
| Limitation | Does not prove full production rollout without deployment, ownership and security review. |
| CTA | `platform-assessment` |

### Platform Kit 2 - Controlled LLM Gateway

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | Company needs policy over models, providers, keys, quotas, cost and logging. |
| Buyer / owner | AI platform owner, Security, Infrastructure, FinOps. |
| Current pain | Teams use providers and keys inconsistently; cost and risk are hard to attribute. |
| Product fit | Platform frames gateway policy, routing, access and observability. |
| Pilot readiness | Allowed providers/models, request classes, logging and policy requirements. |
| Pilot input | Provider list, policy constraints, sample request categories, audit expectations. |
| Pilot output | Minimal LLM gateway policy and routing model. |
| Proof status | `pilot-artifact` |
| Limitation | No SLA, provider availability, sovereign model or compliance claim without evidence. |
| CTA | `deployment-readiness` |

### Platform Kit 3 - Corporate RAG Governance

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | Several teams index corporate documents differently and get inconsistent answers. |
| Buyer / owner | CTO, knowledge owner, Security, data owner. |
| Current pain | Source ownership, access, update rules and quality tests are unclear. |
| Product fit | Platform can define shared knowledge/RAG contour and source access model. |
| Pilot readiness | Selected document set, owners, access rules, update frequency, test questions. |
| Pilot input | Documents, access matrix, control question set, quality criteria. |
| Pilot output | RAG contour, source access model, update model and gap list. |
| Proof status | `pilot-artifact` |
| Limitation | Answer quality depends on documents, access and test set quality. |
| CTA | `platform-pilot` |

## Agents Pilot Kits

### Agents Kit 1 - HR Onboarding Assistant

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | HR repeatedly answers onboarding and policy questions. |
| Buyer / owner | HR director, HR operations, onboarding owner. |
| Current pain | Employees ask the same questions; documents and handoff rules are scattered. |
| Product fit | Agents supports internal assistants with documents, access and human handoff. |
| Pilot readiness | FAQ, onboarding checklist, policies, handoff rules, selected channel. |
| Pilot input | 30-50 typical questions, source documents, escalation cases. |
| Pilot output | Test question set, assistant flow, answer-quality review, handoff rules. |
| Proof status | `pilot-artifact` |
| Limitation | No automation percentage or HR decision replacement claim. |
| CTA | `agent-scenario-selection` |

### Agents Kit 2 - Legal Review Assistant

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | Legal team spends time on first-pass review of typical documents. |
| Buyer / owner | Legal director, contract process owner. |
| Current pain | Deviations from templates are found manually and review logic is not standardized. |
| Product fit | Agents can support checklist-based review and handoff to lawyer. |
| Pilot readiness | Templates, checklists, sample documents, mandatory human review rules. |
| Pilot input | Document samples, rule list, expected deviations, escalation policy. |
| Pilot output | Checklist, deviation map, review workflow and limitation list. |
| Proof status | `pilot-artifact` |
| Limitation | Not legal advice; responsible decisions require human review. |
| CTA | `rag-documents-check` |

### Agents Kit 3 - Corporate Knowledge Assistant

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | Employees cannot find regulations, instructions and process answers. |
| Buyer / owner | Knowledge owner, HR/IT/Operations, internal portal owner. |
| Current pain | Search and documentation quality vary; access rules may be unclear. |
| Product fit | Agents can provide controlled answers over approved sources with handoff. |
| Pilot readiness | Document scope, access model, control questions, update owner. |
| Pilot input | Selected knowledge base, access matrix, questions, expected answers. |
| Pilot output | Knowledge scope, answer test set, source gap list, handoff model. |
| Proof status | `pilot-artifact` |
| Limitation | Quality depends on source completeness and access correctness. |
| CTA | `rag-documents-check` |

## Dev Pilot Kits

### Dev Kit 1 - AI-Assisted Workflow Governance

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | Teams already use AI tools but analysis, implementation, tests and review rules differ. |
| Buyer / owner | CTO, Head of Engineering, engineering excellence, tech lead. |
| Current pain | AI speed creates uneven quality, architecture drift and unclear accountability. |
| Product fit | Dev defines workflow policy, profiles, knowledge layer and quality gates. |
| Pilot readiness | One team, one stack, one task type, review rules, ADR/test context. |
| Pilot input | Current workflow, codebase constraints, test expectations, review practices. |
| Pilot output | Workflow policy, analysis gate, quality gate map and scaling rules. |
| Proof status | `pilot-artifact` |
| Limitation | No universal speedup or workforce reduction claim. |
| CTA | `ai-workflow-assessment` |

### Dev Kit 2 - Design-System Compliance

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | AI-generated UI diverges from tokens, components and visual rules. |
| Buyer / owner | Design system owner, frontend lead, product design lead. |
| Current pain | AI creates local visual decisions and breaks component/state consistency. |
| Product fit | Dev can frame design token guardrails and UI review workflow. |
| Pilot readiness | Token/component inventory, UI rules, examples, frontend stack. |
| Pilot input | Tokens, components, restrictions, sample screens, acceptance criteria. |
| Pilot output | Design token guardrail map, UI review checklist, AI-assisted frontend rules. |
| Proof status | `pilot-artifact` |
| Limitation | Requires approved or stable design-system baseline. |
| CTA | `design-system-guardrails` |

### Dev Kit 3 - Brownfield Refactor Control

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | AI helps change legacy code faster, but regression and architecture risk grow. |
| Buyer / owner | Tech lead, architect, QA lead, brownfield owner. |
| Current pain | Legacy conventions, edge cases and tests are not consistently considered. |
| Product fit | Dev can define refactor analysis gate, required checks and review protocol. |
| Pilot readiness | One module, known risks, tests, acceptance criteria, rollback expectations. |
| Pilot input | Module context, constraints, test list, known incidents, review rules. |
| Pilot output | Refactor risk map, required checks, review protocol and regression expectations. |
| Proof status | `pilot-artifact` |
| Limitation | Does not replace tests, review or owner responsibility. |
| CTA | `quality-gates-pilot` |

## Forum Pilot Kits

### Forum Kit 1 - Contact-Center Flow Automation

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | Operators handle repeated requests and current bot flow does not reduce load safely. |
| Buyer / owner | Head of CX, contact-center director, support operations. |
| Current pain | Repetitive requests, unclear escalation, weak funnel visibility. |
| Product fit | Forum combines scenario control, LLM clarification, escalation and dialog journal. |
| Pilot readiness | One channel, one request stream, rules, escalation reasons, control dialogs. |
| Pilot input | Current flow, request examples, escalation rules, critical answer constraints. |
| Pilot output | Flow map, escalation rules, pilot scenario, event list. |
| Proof status | `pilot-artifact` |
| Limitation | No automation rate without real flow data and measured pilot. |
| CTA | `dialog-flow-assessment` |

### Forum Kit 2 - Scenario + LLM Hybrid Bot

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | Rigid scenario tree cannot handle live wording, but pure LLM is too risky. |
| Buyer / owner | Digital channel owner, CX product owner, chatbot/channel owner. |
| Current pain | The bot is either controlled but rigid, or flexible but hard to govern. |
| Product fit | Forum separates controlled checkpoints from LLM enrichment zones. |
| Pilot readiness | Scenario graph, forbidden topics, LLM zones, critical answer rules. |
| Pilot input | Current scenario, live wording examples, escalation rules, checkpoints. |
| Pilot output | Scenario DSL hypothesis, LLM enrichment zones, checkpoint list, journal requirements. |
| Proof status | `pilot-artifact` |
| Limitation | LLM enrichment is not used for critical answers without rules and sources. |
| CTA | `scenario-llm-pilot` |

### Forum Kit 3 - Funnel Analytics For Conversations

Status: `draft`

| Field | Draft |
|---|---|
| Trigger | Team cannot see where clients exit scenarios or why escalation happens. |
| Buyer / owner | CX analytics, support ops, product owner, digital service owner. |
| Current pain | Dialog outcomes are hidden in transcripts; improvement backlog is subjective. |
| Product fit | Forum can define funnel events, drop-off points, journal and need catalog. |
| Pilot readiness | Event taxonomy, exit points, escalation reasons, privacy/data rules. |
| Pilot input | Dialog event examples, current flow, statuses, review rules. |
| Pilot output | Funnel event list, drop-off map, need catalog draft and review workflow. |
| Proof status | `pilot-artifact` |
| Limitation | Analytics depends on agreed data/privacy contour and event quality. |
| CTA | `support-analytics-review` |

## Approval Board

| Product | Draft kits | Owner | Status | Next action |
|---|---:|---|---|---|
| Platform | 3 | PM + Architect + Sales | ready-for-owner-review | approve/rewrite pilot kits |
| Agents | 3 | PM + Sales + Content | ready-for-owner-review | approve/rewrite pilot kits |
| Dev | 3 | PM + Tech Lead + Content | ready-for-owner-review | approve/rewrite pilot kits |
| Forum | 3 | PM + CX/Sales + Content | ready-for-owner-review | approve/rewrite pilot kits |

## Implementation Notes

- Do not update product data files until pilot kits are approved.
- Do not add new fields or endpoints in Sprint 10.
- If Sales rejects current fallback context, open Sprint 12 `D-11` structured qualification scope.
- Proof statuses remain `pilot-artifact` unless Sprint 09 evidence approves stronger status.
