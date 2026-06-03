# 17. Local Gap Decision Briefs

Дата: 02.06.2026

Статус: draft baseline для локально продвигаемых gaps. Документ не заменяет approval PM/UX/Designer/SEO, но дает им конкретную starting point вместо открытого вопроса.

## Назначение

Этот документ закрывает первый локальный слой реализации после `15-gap-closure-master-plan.md` и `16-gap-closure-action-register.json`: для gaps, которые не требуют Метрики, Bitrix admin, production logs, CRM/upstream или Legal evidence, здесь зафиксированы рабочие baseline-решения.

Covered gaps:

- `PB-004` - Dev public workflow examples;
- `CJM-004` - role/stage/product-specific CTA variants;
- `CJM-005` - returning-lead journey;
- `UI-004` - architecture diagram design brief;
- `UI-008` - icon taxonomy baseline.

This document also contains a draft metadata matrix used as input for `SEO-TOBE-005`; final SEO/content coverage lives in `20-phase-4-seo-content-decision-pack.md`.

Важно: статусы этих gaps остаются non-closed до owner approval. Этот документ переводит их из пустого вопроса в проверяемый review package.

## Existing Contracts To Preserve

| Area | Preserve |
|---|---|
| Lead forms | Existing modal and product CTA form behavior; no new POST contract without Security / Integration lane |
| Product pages | Existing product context/prefill logic and `data-product-block` markers |
| CTA anchors | Existing `#contact-form`, contact modal, `/offer/`, `/calculator/`, `/price/` routes |
| Analytics | No PII params; product events require Metrika evidence before closure |
| Claims | No registry, certification, KPI, SLA, production-readiness or procurement guarantee without approved source |
| Icons | Do not add a second icon library until Designer + Frontend approve TO BE source |

## PB-004 - Dev Public Workflow Examples

Goal: make Tacticum Dev concrete enough for buyers without promising unsupported automation depth.

| Workflow | Buyer trigger | Primary user | Public output | Safe limitation |
|---|---|---|---|---|
| AI-assisted requirements clarification | Requirements are fragmented across chats, docs and stakeholder notes | Product owner, analyst | Structured brief, open questions, acceptance criteria draft | Final scope and responsibility stay with the project team |
| Legacy process decomposition | Existing workflow is manual or hidden inside spreadsheets/1C/Bitrix | Operations owner, architect | Process map, integration points, first automation backlog | Needs access to current process owners and systems |
| Prototype-to-production preparation | A pilot script or prototype works, but cannot be supported | Tech lead, delivery manager | Architecture outline, risk list, deployment plan | Production readiness depends on environment, security and ownership checks |
| Integration workflow design | AI feature must exchange data with CRM, Bitrix, helpdesk or internal systems | Architect, backend owner | API/data-flow sketch, error-handling assumptions, rollout plan | External system constraints must be validated separately |
| Human-in-the-loop quality workflow | AI answer or action must be reviewed before business impact | Department lead, QA owner | Review queue logic, escalation rules, acceptance checklist | Automation rate is not promised without measured pilot data |

Recommended public framing:

- "проектируем и внедряем AI-workflows вокруг существующих процессов";
- "начинаем с pilotable workflow, затем фиксируем integration, ownership and quality gates";
- "не обещаем автономность без измеримого пилота и согласованной ответственности".

Approval needed: PM + Tech Lead should confirm which 3-5 workflows become public on `/dev/`.

## CJM-004 - CTA Intent Matrix

Goal: CTA must reflect buyer stage and product context, not just repeat one generic consultation ask.

| Stage | Role | Primary CTA | Secondary CTA | Existing route/contract |
|---|---|---|---|---|
| Orientation | CEO, COO, product owner | "Подобрать продукт" | "Посчитать команду" | Homepage fit matrix, `/calculator/`, `/price/` |
| Product fit | Department owner, analyst | "Обсудить сценарий" | "Сравнить продукты" | Product page `#contact-form`, comparison block |
| Architecture review | Architect, IT lead | "Разобрать архитектуру" | "Запросить материалы для оценки" | Product page CTA, contact modal, no new download endpoint yet |
| Procurement/security | Security, procurement, legal | "Запросить пакет для согласования" | "Назначить технический разбор" | Existing form with safe comment/prefill; no new regulated claim |
| Commercial request | Sponsor, procurement | "Получить коммерческое предложение" | "Уточнить состав команды" | `/offer/`, `/price/`, contact modal |
| Returning lead | Known stakeholder | "Продолжить обсуждение" | "Назначить архитектурную сессию" | Existing contact modal or product CTA with returning-lead copy |

Product-specific CTA emphasis:

| Product | Primary CTA intent | Avoid |
|---|---|---|
| Platform | Architecture/session and platform assessment | Abstract "купить платформу" without trigger |
| Agents | Internal assistant pilot and knowledge/workflow selection | Mixing with public customer chat/forum unless boundary is clear |
| Dev | Workflow assessment and implementation scope | Claiming fixed automation rate or guaranteed production path |
| Forum | Customer/community dialog and moderated support scenario | Calling it just another internal agent |

TO BE design requirement:

- CTA component needs variants by `intent`: `consultation`, `architecture-session`, `proposal`, `documentation-request`, `calculator`, `continue-discussion`;
- visual hierarchy should make one primary action per block;
- secondary actions can be text links or compact buttons, not equal-weight duplicates.

Approval needed: PM + UX should confirm CTA labels and which variants are available on each product page.

## CJM-005 - Returning-Lead Journey

Goal: a person who already spoke with Tacticum should not restart from generic lead capture.

Baseline journey:

1. Entry from direct link, email, messenger, saved product page or returning visit.
2. Page detects no personal data client-side; it only shows public "continue discussion" CTA variant where appropriate.
3. User chooses one of three intents: continue product discussion, request architecture session, request documents for internal approval.
4. Existing form/modal captures the request text and product context; no new PII fields are required for v1.
5. Sales/PM follows up using CRM/context outside the public page.

Returning-lead CTA set:

| Intent | Public label | Existing implementation path |
|---|---|---|
| Continue discussion | "Продолжить обсуждение" | Contact modal or product CTA |
| Architecture session | "Назначить архитектурную сессию" | Product CTA with product context |
| Documents request | "Запросить материалы для согласования" | Contact modal comment/prefill, no file download endpoint yet |
| Commercial follow-up | "Уточнить коммерческое предложение" | `/offer/` or contact modal |

Do not implement before approval:

- personal recognition;
- CRM lookup from frontend;
- dynamic documents download;
- account-like returning user area;
- hidden PII transport without Security / Integration lane.

Approval needed: Sales + UX should confirm if v1 is copy/CTA only or requires structured CRM fields.

## UI-004 - Architecture Diagram Design Brief

Goal: product pages need diagrams that make deployment, integrations and responsibility boundaries easier to scan.

Required diagram patterns:

| Pattern | Use | Minimum content | Mobile fallback |
|---|---|---|---|
| Layered stack | Platform overview and shared architecture | Channels, AI layer, integrations, governance, logs/quality | Vertical ordered stack |
| Data-flow | Agents, Forum, procurement/security blocks | Input, processing, human review, output, audit trail | Numbered steps |
| Integration map | Dev and platform integration scenarios | Existing systems, adapters/API, ownership, error handling | Two-column source -> target list |
| Responsibility boundary | Security/procurement and rollout blocks | Tacticum-owned, client-owned, shared decisions | Compact matrix |

Visual rules:

- diagrams must be readable without hover;
- labels must fit mobile width and not depend on tiny text;
- no unapproved claims inside diagrams;
- use icons sparingly as orientation aids, not as proof;
- every diagram should have a text equivalent in the same section for accessibility and SEO.

Implementation implications:

- first version can be HTML/CSS blocks inside existing product renderer partials;
- SVG/Canvas is only justified if Designer needs complex topology;
- no new JS should be required for basic understanding;
- `data-product-block` markers should stay stable.

Approval needed: Designer + Architect should approve pattern library and page placement.

## UI-008 - Icon Taxonomy Baseline

Goal: stop ad hoc icon selection and prevent a second icon library from entering the template accidentally.

Current AS IS decision:

- keep Remix Icon as the implemented baseline;
- use curated subset per semantic category;
- do not add a second icon library until TO BE source is approved.

Semantic categories:

| Category | Use | Example meaning |
|---|---|---|
| Product | Product cards and navigation | platform, agents, dev, forum |
| Action | Buttons and CTAs | calculate, send, download/request, continue |
| Status | Proof and evidence states | available, pilot, needs review, unavailable |
| Process | Use cases and rollout | discovery, pilot, integration, support |
| Architecture | Diagrams | system, API, data, security, logs |
| Communication | Chat and forum flows | message, user, operator, handoff |

Rules for TO BE:

- one icon per semantic meaning across the site;
- icon-only buttons need accessible labels/tooltips;
- proof/status icons must not imply certification or approval without evidence;
- decorative icons should be removable without losing meaning;
- icon size, stroke/weight and container rules belong to token/component specs, not one-off CSS.

Approval needed: Designer should decide whether TO BE keeps Remix Icon, maps to a curated paid/open icon set, or replaces icons inside a Figma component library.

## SEO-TOBE-005 - Draft Metadata Matrix

Goal: provide SEO/Content with a draft title/description/H1 set that matches the current product taxonomy and avoids unsupported claims.

| Page | Draft title | Draft description | Draft H1 |
|---|---|---|---|
| `/platform/` | Tacticum Platform - AI-платформа для корпоративных процессов | Платформа для запуска AI-сценариев, интеграций, контроля качества и поэтапного внедрения в корпоративной среде. | Tacticum Platform |
| `/agents/` | Tacticum Agents - AI-агенты для внутренних команд | AI-агенты для рабочих сценариев, знаний и операционных процессов с контролем внедрения, интеграций и ответственности. | Tacticum Agents |
| `/dev/` | Tacticum Dev - разработка AI-workflows и интеграций | Проектирование и внедрение AI-workflows вокруг существующих процессов, систем и командных ролей. | Tacticum Dev |
| `/forum/` | Tacticum Forum - AI-форум и диалоговая платформа | Диалоговая платформа для клиентских и экспертных сценариев с модерацией, маршрутизацией и контролем качества. | Tacticum Forum |
| `/aiagents/` | AI-агенты для бизнеса - Tacticum | Сценарии AI-агентов для бизнеса, консультация по применению и переход к продукту Tacticum Agents. | AI-агенты для бизнеса |

SEO review questions:

- should `/agents/` become the canonical product URL and `/aiagents/` stay compatibility/lead-gen route;
- whether `/aiagents/` needs canonical to `/agents/` or separate intent with cross-linking;
- whether titles should include "Тактикум" or "Tacticum" consistently;
- whether product pages need industry modifiers only after proof/cases are ready.

Approval needed: SEO + Content should approve final copy and update implementation only after canonical decision for `/agents/` vs `/aiagents/`.

## Review Order

1. PM + Sales: approve `PB-004`, `CJM-004`, `CJM-005` business intent.
2. Designer + UX: turn CTA, returning-lead, diagram and icon baselines into Figma variants.
3. SEO + Content: approve metadata and `/agents/` vs `/aiagents/` direction.
4. Frontend + QA: confirm no selector/form/analytics contract changes are required for v1.
5. Update `14-gap-backlog-and-decision-register.md` and `16-gap-closure-action-register.json` statuses only after approvals exist.
