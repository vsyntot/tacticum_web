# Public Content Glossary And Editorial Rules — 2026-06-07

Дата: 07.06.2026

Статус: editorial baseline draft for the content language / storyline challenge. Owner approval pending.
Scope: public website copy in Russian. Applies to headings, hero copy, CTAs, FAQ, product pages, page-content rows, product-content rows, offer detail copy and footer/company text. Internal docs, code comments, config keys and analytics identifiers are out of scope unless rendered publicly.

## Purpose

Этот документ задаёт Russian-first правила языка для будущей переписи сайта после content challenge. Его цель — не запретить технические термины, а убрать из публичного слоя внутренний handoff-язык and make Tacticum sound like one coherent B2B product/delivery company.

## North-Star Public Promise

Recommended baseline:

> Tacticum helps companies safely launch AI in work processes: choose a scenario, estimate budget, validate a pilot and assemble the implementation team.

Russian public version:

> Tacticum помогает компаниям безопасно запускать ИИ в рабочих процессах: выбрать сценарий, оценить бюджет, проверить пилот и собрать команду внедрения.

## Core Editorial Rules

1. Russian-first by default.
2. Product names may stay English: `Tacticum Platform`, `Tacticum Agents`, `Tacticum Dev`, `Tacticum Forum`.
3. Generic terms should be Russian unless an acronym is standard for the buyer audience.
4. Use `ИИ` as the primary generic Russian term. Use `AI` only in product/SEO contexts where owner-approved.
5. Avoid internal labels in public headings: `Product fit`, `Use cases`, `Delivery layer`, `Product workstreams`, `Vendor trust`.
6. Do not promise outcomes, percentages, guarantees, timelines, certifications, partner status or SLA without approved evidence.
7. One sentence should usually express one idea. Avoid long chains of architecture nouns.
8. Every CTA should say what the user gets next: plan, assessment, pilot format, budget orientation, team composition, architecture session or demo.
9. If a term is technical and necessary, explain it in Russian on first mention.
10. Public copy should tell the buyer what changes for them, not how internal taxonomy is organized.

## AI / ИИ Rule

| Context | Preferred |
|---|---|
| Generic public copy | `ИИ` |
| First technical mention where SEO/recognition matters | `ИИ (AI)` or `AI/ИИ` only if approved |
| Product names already using AI | Keep only if PM/SEO approved |
| Meta title/description | SEO decides case-by-case; avoid English overload |
| Internal code/config/analytics | Keep existing identifiers |

Examples:

| Avoid | Prefer |
|---|---|
| `корпоративный AI` | `корпоративный ИИ` |
| `AI-решение` | `ИИ-решение` or `решение на базе ИИ` |
| `AI-калькулятор` | `ИИ-калькулятор`, unless SEO keeps `AI-калькулятор` |
| `AI-боты` | `ИИ-боты` or `демо ассистента`, depending on page role |

## Replacement Table

| Current / Avoid In Public Copy | Preferred Public Russian | Notes |
|---|---|---|
| `Product fit` | `Когда подходит продукт` | Heading/eyebrow |
| `fits` | `Подходит, если` | Never public as raw key |
| `not_fits` | `Не подходит, если` | Never public as raw key |
| `start` | `С чего начать` | Never public as raw key |
| `Use cases` | `Сценарии применения` | Product pages and catalogs |
| `Security / procurement` | `Безопасность и закупка` | Enterprise path |
| `Delivery layer` | `Слой внедрения` / `Как доводим до внедрения` | Prefer action-oriented heading |
| `Product workstreams` | `Команда под этап внедрения` | `/price/` |
| `Product-aware estimate` | `Оценка с учетом продуктового сценария` | `/calculator/` |
| `Vendor trust` | `Почему нам можно доверять` / `Доверие к команде` | `/about/` |
| `assessment` | `оценка` / `архитектурная проверка` | Choose by context |
| `discovery` | `разбор задачи` / `предпроектная диагностика` | First mention can include `discovery` only if Sales wants |
| `delivery` | `внедрение` / `команда внедрения` | Avoid `delivery-команда` |
| `workflow` | `рабочий процесс` / `инженерный процесс` | `Dev` page can mention English later |
| `handoff` | `передача сотруднику` / `эскалация к человеку` | Use by business context |
| `runtime` | `рабочий контур` / `исполнительный слой` | Technical details only |
| `deployment` | `размещение` / `модель размещения` | Product/procurement pages |
| `production` | `промышленная эксплуатация` / `рабочий контур` | Avoid as generic English |
| `scope` | `объем работ` | Offer/detail/forms |
| `quality gates` | `контрольные проверки качества` | `Dev` page |
| `design tokens` | `правила дизайн-системы` / `токены дизайн-системы` | Technical detail only |
| `review` | `проверка` / `ревью` | `ревью` acceptable for engineering audience |
| `proof` | `подтверждения` / `доказательная база` | Use claim status |
| `evidence` | `подтверждение` / `источник подтверждения` | Legal/proof contexts |
| `lead` | `заявка` / `обращение` | `лид` internal only |
| `rollout` | `масштабирование` / `вывод в рабочий контур` | Product pages |
| `quick replies` | `быстрые варианты ответа` | UI copy |
| `examples` | `примеры` / `ориентиры` | `/offer/` |
| `team preset` | `типовой состав команды` | `/price/` |
| `staff augmentation` | `усиление команды` | Use only if this is the explicit service |
| `chatbot` | `чат-бот` / `бот` / `ассистент` | Product-specific |

## Approved Acronyms And Technical Terms

These can appear when relevant, but should not dominate headings:

| Term | Rule |
|---|---|
| `CRM`, `ERP`, `API`, `SLA`, `RAG`, `LLM`, `MVP`, `QA`, `DevOps`, `BI` | Allowed for B2B/technical pages; explain if used in broad homepage copy |
| `MCP`, `RBAC`, `MLOps`, `CI/CD` | Use only in technical sections, not top-level public promise |
| `Telegram`, `WhatsApp`, `amoCRM`, product/vendor names | Allowed as channel/system names |
| Programming languages and stacks | Allowed in team/rates/technical sections |

## Tone Rules

### Use

- спокойный B2B tone;
- concrete next step;
- cautious proof language;
- business outcome before architecture;
- transparent limitations.

### Avoid

- fear framing: `без профессиональной команды вы рискуете...`;
- generic excellence claims: `богатый опыт`, `оптимальные решения`, `передовые технологии` without proof;
- unsupported guarantees: `гарантируем результат`, `точная цена`, `за 5 минут полноценный агент`;
- internal taxonomy as public story: `product workstreams`, `delivery layer`, `source`, `runtime` in headings;
- overusing `контур`, `следующий шаг`, `артефакт`, `scope`.

## Page-Level Narrative Rules

| Page | Primary Story | Avoid |
|---|---|---|
| `/` | One company promise and guided route to product/pilot/estimate/team/demo | Equal-weight list of unrelated commercial routes |
| `/platform/` | Common AI foundation for several scenarios: data, access, audit, cost, operations | Architecture-only pitch with unexplained runtime/deployment terms |
| `/agents/` | Corporate assistants with documents, permissions, tools and human escalation | Telegram bot/conversational demo framing as main product |
| `/dev/` | Controlled use of AI in engineering without losing architecture, tests and standards | English-heavy internal engineering governance jargon in top-level copy |
| `/forum/` | Managed customer communications: scenarios plus LLM assistance where safe | Pure free-form LLM bot promise |
| `/services/` | How Tacticum takes a scenario from assessment to implementation | Product names as unexplained workstream labels |
| `/price/` | Team composition and budget orientation for pilot/implementation | Marketplace/rates-first staff catalog positioning |
| `/calculator/` | Preliminary estimate: budget range, timeline, team, risks and next step | Internal `artifact` language without sample output |
| `/offer/` | Example estimates to orient discussion, not final offers | Synthetic examples as real proof or exact price |
| `/aiagents/` | Quick demo/prototype of one assistant scenario | Separate bot product competing with Tacticum Agents |
| `/about/` | Why the product + implementation team can be trusted | Generic company boast without evidence |
| `/contacts/` | Operational route to the right next step | Unclear broad invitation to discuss anything |

## CTA Language Rules

| Intent | Preferred CTA |
|---|---|
| Product selection | `Подобрать продуктовый сценарий` |
| Pilot | `Получить план пилота` |
| Architecture | `Обсудить архитектуру` |
| Procurement/security | `Запросить разбор требований` |
| Estimate | `Получить предварительную оценку` |
| Team | `Подобрать состав команды` |
| Offer detail | `Уточнить оценку по своей задаче` |
| Demo bot | `Проверить сценарий в Telegram` |
| Generic contact | `Описать задачу` |

Avoid generic CTAs when context is known:

- `Обсудить` without object;
- `Оставить заявку` as the only CTA;
- `Получить расчёт` if the result is only preliminary;
- `Снизить риски с командой` if the section uses fear framing.

## Proof / Claims Rules

Every public proof-like statement should have one status:

| Status | Public Treatment |
|---|---|
| `public-approved` | Can be used publicly with source-approved wording |
| `private-by-request` | Can say that details are available on request, without raw numbers/logos |
| `benchmark` | Must be marked as industry/benchmark estimate, not Tacticum result |
| `example-estimate` | Must be marked as example/orientation, not final price or real customer proof |
| `pending` | Do not use as proof; can stay in internal docs |
| `blocked` | Do not publish |

## Editor Checklist

Before promoting public Bitrix/page-content/product-content rows:

- [ ] Headings and eyebrows are Russian-first.
- [ ] No raw internal keys are visible: `fits`, `not_fits`, `start`, `Product fit`, `Use cases`, `Security / procurement`.
- [ ] English terms are either product names, standard acronyms or explained technical details.
- [ ] `AI` vs `ИИ` usage follows page/SEO decision.
- [ ] CTA says what the user gets next.
- [ ] Claims have evidence status.
- [ ] Synthetic examples are marked as examples/orientirs.
- [ ] `/aiagents/` does not compete with `/agents/`.
- [ ] `/price/` does not become rates-first in first screen unless PM/Sales intentionally approve staffing positioning.
- [ ] No PII, private customer proof or unapproved logos/certifications appear in public copy.

## Related Documents

- `docs/workflow/content-language-storyline-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/content-language-storyline-challenge-roadmap-2026-06-07.md`
- `docs/workflow/content-language-storyline-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
- `docs/workflow/product-taxonomy-seo-packaging-decision-2026-06-04.md`
