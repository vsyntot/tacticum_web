# 04. Product Page Briefs

Дата: 01.06.2026

## Назначение

Этот документ фиксирует стартовые брифы для продуктовых страниц Tacticum Platform, Agents, Dev и Forum. Это не финальные тексты, а рамка для дизайнера, редактора, PM и разработки.

## Universal Product Page Template

Каждая продуктовая страница должна отвечать на 10 вопросов:

1. Что это за продукт?
2. Для кого он?
3. Какая проблема у клиента сейчас?
4. Почему self-build / generic tools ломаются?
5. Как Tacticum решает задачу?
6. Как продукт связан с Platform?
7. Какие модули и сценарии входят?
8. Как это внедряется?
9. Какие доказательства есть?
10. Как начать пилот?

## Tacticum Platform

### Page Role

Объяснить ядро экосистемы и доказать, что Tacticum не набор разрозненных AI-ботов, а программная платформа.

### Primary Audience

- CIO;
- enterprise architect;
- AI platform owner;
- security/compliance;
- руководитель инфраструктуры;
- руководитель цифровой трансформации.

### Problem

Крупная организация строит несколько AI-приложений, и каждая команда заново поднимает LLM gateway, RAG, MCP, memory, auth, audit, observability и connectors.

### Promise

Один инфраструктурный контур для LLM-приложений: routing, cost control, audit, tenancy, RAG, memory, MCP, guardrails и deployment в нужном контуре.

### Core Sections

- What breaks without a platform.
- Platform module map: Runtime, AI, Data, Access, IO, Ops.
- One Agent Runtime, three DSLs.
- LLM Gateway and sovereign model policy.
- On-prem deployment model.
- Security and audit.
- How Agents/Dev/Forum reuse Platform.
- Pilot requirements.

### CTA

- “Обсудить платформенный пилот”.
- “Получить архитектурную схему под ваш контур”.

### Claims To Verify

- Реестр Минцифры;
- доверенное ПО;
- Astra Linux / РЕД ОС compatibility;
- ФСТЭК/ФСБ;
- ПАК;
- налоговые льготы;
- поддерживаемые LLM-провайдеры.

## Tacticum Agents

### Page Role

Продать идею корпоративных мультиагентных ассистентов для бизнес-функций.

### Primary Audience

- HR director;
- legal director;
- finance/accounting;
- customer support;
- IT helpdesk;
- CIO/CDO как sponsor.

### Problem

Каждый департамент хочет своего AI-ассистента, но self-build дает разрозненные боты без tenancy, audit, memory isolation, prompt management и контроля стоимости.

### Promise

Набор корпоративных ассистентов, которые используют общую Platform-инфраструктуру, интегрируются с внутренними системами и управляются как продуктовый каталог.

### Core Sections

- Business-function scenarios.
- Agent Catalog.
- Templates: HR onboarding, CV screening, legal review, accounting FAQ, corporate KB, support, IT helpdesk.
- RAG and corporate knowledge.
- MCP tools and integrations.
- Channels: Telegram, web, API, VK Teams, MAX, SIP where confirmed.
- Governance: RBAC, tenancy, audit, guardrails.
- Rollout: discovery -> pilot -> integration -> rollout.

### CTA

- “Выбрать 1-2 сценария для пилота”.
- “Проверить ваши документы для RAG-пилота”.

### Claims To Verify

- Проценты снижения нагрузки по HR/legal/accounting/support;
- готовность конкретных каналов;
- готовность MCP-инструментов;
- ФЗ-152/КИИ формулировки;
- реальные templates “из коробки”.

## Tacticum Dev

### Page Role

Показать Tacticum Dev как governance слой для AI-assisted разработки в больших инженерных организациях.

### Primary Audience

- CTO;
- VP Engineering;
- Head of Development;
- engineering excellence;
- enterprise architect;
- design system owner;
- QA/DevSecOps lead.

### Problem

Обычный vibe coding ускоряет typing и прототипы, но в больших brownfield-командах приводит к architecture drift, regression risk, design violations, слабой traceability и неравномерному качеству.

### Promise

AI-разработка с профилями, knowledge layer, design token compliance, workflow gates и stack-specific MCP-инструментами.

### Core Sections

- Vibe coding ceiling.
- Profiles instead of personal prompts.
- RE Knowledge Layer.
- Design Token Layer.
- Analysis Gate: BRD/ADR/PIN/TESTS before code.
- Quality Gates.
- Stack-specific bundles.
- Multi-CLI adapter.
- Pilot metrics and rollout model.

### CTA

- “Провести pilot на одной команде”.
- “Оценить готовность codebase к agentic workflow”.

### Public Tone Guardrail

Публичный сайт должен избегать формулировок про массовое сокращение людей. Допустимый framing:

- ускорение lead time;
- снижение регрессий;
- контроль архитектуры;
- стандартизация работы с AI-инструментами;
- переобучение и повышение зрелости команд.

### Claims To Verify

- Reference pilot metrics;
- поддерживаемые CLI;
- premium gating;
- design token MCP;
- stack-specific tools;
- security model для codebase/context;
- любые workforce claims.

## Tacticum Forum

### Page Role

Показать Forum как управляемую диалоговую платформу для клиентских коммуникаций.

### Primary Audience

- Head of CX;
- contact center director;
- digital channels owner;
- support operations;
- telecom/finance/e-commerce/government service owners;
- CIO/security как technical sponsor.

### Problem

Классические сценарные боты жесткие и плохо понимают клиента. Чистые LLM-боты гибкие, но опасны для critical paths из-за галлюцинаций, слабой traceability и отсутствия гарантированных ответов.

### Promise

Гибрид сценарных графов и LLM: критичные пути остаются управляемыми, LLM помогает распознавать намерения, обрабатывать уточнения и находить новые потребности.

### Core Sections

- Scenarios + LLM, without illusions.
- Needs Catalog.
- Visual Scenario Editor.
- Scenario DSL.
- A/B Experiment Engine.
- Need Discovery Engine.
- Funnel Analytics.
- Dialog Journal.
- Integrations and channels.
- Security and audit.
- Rollout and KPI model.

### CTA

- “Разобрать поток обращений”.
- “Выбрать сценарии для 4-6 недельного пилота”.

### Claims To Verify

- automation rate ranges;
- FCR/drop-off/cost metrics;
- готовность каналов;
- регуляторные формулировки;
- импортозамещение Twilio/Genesys/Salesforce comparisons.

## Cross-Product Blocks

Эти блоки можно проектировать как переиспользуемые компоненты:

- Product hero.
- Product ICP strip.
- Problem matrix.
- Architecture diagram.
- Module grid.
- Use-case cards.
- Comparison table.
- Deployment/security block.
- Proof metrics.
- Rollout timeline.
- FAQ.
- Product-aware lead CTA.

## Implementation Notes

- 01.06.2026: первый public slice использует общий renderer `local/php_interface/include/product_page.php`.
- 01.06.2026: product pages получили reusable rollout block для вопроса "Как это внедряется?" с safe wording: discovery/assessment, pilot, integration/deployment/workflow alignment and rollout/support decision.
- Публичный rollout block не является pricing/licensing/SLA matrix и не должен включать registry, ПАК, certification, guarantee or hard performance claims без evidence из `07-risk-and-claims-register.md`.
- 01.06.2026: product pages получили reusable proof readiness block для вопроса "Какие доказательства есть?": он описывает, что проверяется на пилоте и какие артефакты нужны, но не публикует метрики, logos, testimonials, benchmarks or regulatory proof.
- 01.06.2026: product pages получили минимальную `SoftwareApplication` JSON-LD schema. Она описывает page/product identity, но не содержит offers, pricing, ratings, reviews, logos or proof claims.
- 01.06.2026: product pages переведены на единый data -> schema -> render flow: `$tacticumProductPage` питает HTML, `SoftwareApplication` and `FAQPage` JSON-LD, поэтому видимый static FAQ и structured data синхронизированы.
- 01.06.2026: rendered smoke gate проверяет deployed product schema: `/platform/`, `/agents/`, `/dev/`, `/forum/` должны иметь `SoftwareApplication` and `FAQPage` в `seoHead.productSchemaSummary` без risky commercial fields.
