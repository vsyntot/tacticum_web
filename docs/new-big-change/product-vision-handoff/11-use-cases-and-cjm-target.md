# 11. Use Cases And CJM Target

Дата: 01.06.2026

Статус: целевая карта use cases и customer journeys для TO BE сайта. Документ уточняет, как продуктовая линейка должна объясняться разным ролям и как пользователь должен переходить от интереса к квалифицированной заявке.

## Цель

Сделать product-first сайт не набором one-pager страниц, а decision-support системой:

```text
роль пользователя
  -> ситуация / триггер
  -> подходящий продукт
  -> ограниченный пилот
  -> proof / ограничения
  -> квалифицированная заявка
```

## Target Buyer Roles

| Role | What They Need | Main Risk If Missing |
|---|---|---|
| CIO / CDO | Понять product ecosystem, business value, governance and rollout path | Сайт воспринимается как integrator/service vendor |
| CTO / Enterprise Architect | Увидеть architecture, deployment boundaries, integration and stack fit | Недостаточно технической доверенности |
| Security / Compliance | Понять data, access, audit, on-prem, regulatory wording | Блок на procurement/security review |
| Functional Owner | Понять сценарий, пилот, данные, метрики и effort со своей стороны | Нет перехода от "интересно" к "можно проверить" |
| Head of Engineering | Понять Dev workflows, gates, codebase readiness, team adoption | Dev кажется абстрактным AI-coding consulting |
| Head of CX / Support | Понять Forum vs bot vs LLM, channels, analytics and escalation | Forum смешивается с Agents или обычными чат-ботами |
| Procurement / Legal | Получить безопасные формулировки, документы, ownership and contract path | Сделка зависает после первичного интереса |

## Product Use Case Model

Каждый публичный use case должен иметь одинаковую структуру:

| Field | Meaning |
|---|---|
| Trigger | Что произошло у клиента |
| Buyer / Owner | Кто владеет проблемой |
| Current pain | Почему AS IS не работает |
| Tacticum fit | Какой продукт подходит и почему |
| Pilot input | Какие данные/системы нужны для проверки |
| Pilot output | Что получит клиент после пилота |
| Proof metric | Что измеряем, если есть evidence |
| Risk / limitation | Что не обещаем без проверки |
| CTA | Следующий безопасный шаг |

## Tacticum Platform Use Cases

| Use Case | Trigger | Primary Buyer | Pilot Output | CTA |
|---|---|---|---|---|
| AI portfolio consolidation | В компании уже есть несколько AI/RAG/bot инициатив | CIO / Enterprise Architect | Карта общих runtime/data/access/ops слоев | `platform-assessment` |
| Controlled LLM gateway | Нужен контроль моделей, ключей, квот, расходов и провайдеров | AI platform owner / Security | Минимальный LLM gateway policy and routing model | `deployment-readiness` |
| Corporate RAG and knowledge governance | Несколько команд индексируют документы по-разному | CTO / Knowledge owner | RAG contour, source access rules, update model | `platform-pilot` |
| Audit and observability for AI | Нужно понимать, кто что запросил, какой инструмент вызван и сколько это стоит | Security / Ops | Event/audit/cost tracking plan | `deployment-readiness` |
| Platform reuse for Agents/Dev/Forum | Нужно запускать несколько прикладных AI-продуктов | CIO / CDO | Product reuse architecture map | `platform-assessment` |

### Platform CJM

```text
Homepage
  -> Platform page
  -> Architecture / deployment / security block
  -> Proof readiness / pilot outputs
  -> Architecture session CTA
  -> Sales receives: scenarios, systems, deployment concerns, timeline
```

## Tacticum Agents Use Cases

| Use Case | Trigger | Primary Buyer | Pilot Output | CTA |
|---|---|---|---|---|
| HR onboarding assistant | HR получает повторяющиеся вопросы сотрудников | HR director | Контрольный FAQ/RAG set, handoff rules | `agent-scenario-selection` |
| Legal review assistant | Юристы тратят время на первичную проверку документов | Legal director | Checklist, deviation map, review workflow | `rag-documents-check` |
| Corporate knowledge assistant | Сотрудники не находят регламенты и инструкции | HR/IT/Operations | Knowledge scope, answer quality test set | `rag-documents-check` |
| Support/internal helpdesk assistant | Первая линия перегружена типовыми обращениями | Support/IT helpdesk owner | Intent list, escalation rules, pilot script | `pilot-rollout` |
| Finance/accounting FAQ assistant | Повторяются вопросы по документам и правилам | Finance director | FAQ/documents scope and answer test | `agent-scenario-selection` |

### Agents CJM

```text
Homepage
  -> Agents page
  -> Business-function scenarios
  -> Choose 1-2 pilot scenarios
  -> Data/documents readiness block
  -> Scenario selection CTA
  -> Sales receives: function, documents, systems, expected handoff
```

## Tacticum Dev Use Cases

| Use Case | Trigger | Primary Buyer | Pilot Output | CTA |
|---|---|---|---|---|
| AI-assisted workflow governance | Команды уже используют AI tools без единых правил | CTO / Head of Engineering | Workflow policy and gates map | `ai-workflow-assessment` |
| Design-system compliance | AI генерирует UI, расходящийся с дизайн-системой | Design system owner / Frontend lead | Token/component guardrail map | `design-system-guardrails` |
| Brownfield refactor control | AI ускоряет изменения, но растит regression risk | Tech lead / Architect | Analysis gate and test expectations | `quality-gates-pilot` |
| Requirements-to-tests traceability | Требования плохо связаны с кодом и тестами | QA / Engineering excellence | Feature lifecycle trace model | `ai-workflow-assessment` |
| Stack-specific agent profiles | Разные команды используют разные правила и инструменты | Engineering manager | Team/stack profile draft | `quality-gates-pilot` |

### Dev CJM

```text
Homepage
  -> Dev page
  -> Problem: AI speed vs architecture/control
  -> Workflow examples
  -> Quality/design/security gates
  -> Team readiness CTA
  -> Sales receives: stack, team size, current AI usage, process pain
```

## Tacticum Forum Use Cases

| Use Case | Trigger | Primary Buyer | Pilot Output | CTA |
|---|---|---|---|---|
| Contact-center flow automation | Операторы перегружены повторяющимися обращениями | Head of CX / Contact center director | Flow map, escalation rules, pilot scenario | `dialog-flow-assessment` |
| Scenario + LLM hybrid bot | Старое дерево сценариев не понимает живые формулировки | Digital channel owner | Scenario DSL + LLM enrichment hypothesis | `scenario-llm-pilot` |
| Funnel analytics for conversations | Непонятно, где клиент выпадает из сценария | CX analytics / Support ops | Funnel events and drop-off map | `support-analytics-review` |
| Regulated support flows | Ответы должны быть управляемыми и журналируемыми | Finance/telecom/gov service owner | Critical path and journal requirements | `dialog-flow-assessment` |
| Need discovery from dialogs | Из реальных обращений нужно находить новые потребности | Product/CX owner | Need catalog draft and review workflow | `support-analytics-review` |

### Forum CJM

```text
Homepage
  -> Forum page
  -> Pure LLM vs scenario bot comparison
  -> Flow / channel / escalation blocks
  -> Pilot metrics and dialog journal
  -> Flow assessment CTA
  -> Sales receives: channel, flow, volume, escalation and analytics context
```

## Cross-Product CJM

### Economic Buyer Journey

```text
Homepage
  -> ecosystem map
  -> product fit guide
  -> pilot / rollout model
  -> proof and risk boundaries
  -> discuss pilot CTA
```

Decision support needed:

- product fit matrix;
- "start with pilot" path;
- packaging overview without unapproved pricing;
- public/private proof split.

### Technical Buyer Journey

```text
Product page
  -> architecture block
  -> deployment/security block
  -> integration boundary
  -> proof readiness
  -> architecture session CTA
```

Decision support needed:

- architecture diagrams;
- deployment modes with evidence status;
- connector readiness statuses;
- data/audit/RBAC wording.

### Security / Procurement Journey

```text
Product page or footer
  -> security/procurement block
  -> request documentation CTA
  -> manual review flow
```

Decision support needed:

- safe regulatory wording;
- registry/readiness status;
- data processing statement;
- security checklist request;
- no unsupported legal/tax claims.

### Functional Owner Journey

```text
Homepage or product page
  -> use-case cards
  -> pilot inputs
  -> expected pilot outputs
  -> scenario-specific CTA
```

Decision support needed:

- process examples;
- required data/documents;
- "what happens in 4-6 weeks" only if delivery evidence exists;
- handoff and human-in-the-loop model.

## CJM Gaps

| ID | Gap | Impact | Target Fix |
|---|---|---|---|
| CJM-001 | Нет product fit guide | Пользователь не понимает, с чего начать | Add product comparison / fit matrix |
| CJM-002 | Нет procurement/security path | Enterprise сделки могут зависнуть после интереса | Add gated documentation/security CTA |
| CJM-003 | CTA почти всегда одинаковый | Sales получает мало структурного контекста | Add role/product/use-case-specific CTA variants |
| CJM-004 | Use cases не описаны как pilotable flows | Functional owner не видит, что нужно от него | Add pilot input/output blocks |
| CJM-005 | Agents and Forum boundary не достаточно явен | Confusion and SEO cannibalization | Add comparison and "when to choose" block |
| CJM-006 | Dev requires workflow examples | Product is too abstract for non-expert buyer | Add workflow use-case cards |

## Acceptance Criteria For TO BE CJM

- Each product page has at least 3 explicit use cases.
- Each use case names trigger, buyer, pilot input and pilot output.
- Homepage includes product fit guidance.
- Product pages include role-based next steps.
- Security/procurement journey is available without making unapproved claims.
- Forms or CRM handoff can distinguish product, use case and scenario.
- Analytics can measure product page view -> CTA click -> submit by product without PII.

