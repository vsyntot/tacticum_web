# 09. TO BE Design Work Order

Дата: 02.06.2026

## Назначение

Этот документ переводит AS IS handoff в рабочее задание для дизайнера, который формирует новую версию дизайн-системы `tacticum.ru`.

Он не заменяет Figma, дизайн-ревью или продуктовые решения. Его задача - задать состав deliverables, границы редизайна, acceptance criteria и точки, где дизайнерское решение требует frontend/backend/security scope.

## Входные Артефакты

Перед стартом дизайнер должен прочитать:

| Файл | Зачем |
|---|---|
| `01-as-is-brief.md` | Быстро понять текущую UI-архитектуру |
| `02-component-inventory.md` | Увидеть AS IS components and variants |
| `03-page-inventory.md` | Понять публичные страницы и funnel role |
| `04-interaction-contracts.md` | Понять JS/DOM contracts |
| `05-design-tokens-as-is.json` | Получить checked AS IS token baseline |
| `06-known-debt-and-to-be-questions.md` | Увидеть нерешённые design questions |
| `07-component-state-contract.json` | Получить checked behavior-bearing component/state baseline |
| `08-as-is-to-be-migration-map.json` | Получить preliminary AS IS -> TO BE migration map |

Перед передачей пакета выполнить:

```bash
npm run design:handoff:check
```

## Цель TO BE Дизайн-Системы

TO BE система должна сделать сайт:

- product-first, а не service-only;
- плотным и сканируемым для B2B/enterprise аудитории;
- пригодным для Bitrix/PHP server-rendered implementation;
- безопасным относительно claims, proof, forms, analytics and PII;
- поддерживаемым через tokens, components, states and migration map;
- совместимым с текущими conversion flows: forms, modal, chat, `/price/`, `/offer/`, product pages.

## Required Figma / Design Deliverables

### 1. Token System

Дизайнер должен подготовить token spec:

- brand colors;
- semantic/status colors;
- text/surface/border colors;
- proof/evidence/warning colors;
- typography scale;
- spacing scale;
- radius scale;
- elevation;
- focus rings;
- motion;
- z-index;
- breakpoints;
- diagram colors/connectors/status marks.

Обязательно:

- указать source of truth: Figma variables, JSON, Tailwind mapping or hybrid;
- замапить TO BE tokens на `05-design-tokens-as-is.json`;
- явно решить drift: `#001F40` vs `#001F3F`, `#007bff` vs `#0066CC`.

### 2. Core Components

Минимальный набор Figma components:

| Family | Components |
|---|---|
| Global shell | `NavigationShell`, `Footer`, `MobileMenu` |
| Conversion | `LeadCTAForm`, `ContactModal`, `CTASection`, `Toast` |
| Forms | `TextField`, `Textarea`, `Select`, `CheckboxConsent`, `SubmitButton`, `FieldError` |
| Disclosure | `FAQAccordion`, `AccordionItem` |
| Conversational UI | `ChatSurface`, `MessageBubble`, `TypingIndicator`, `QuickReply`, `LeadHandoffCTA` |
| Product storytelling | `ProductHero`, `FitGuide`, `UseCaseCard`, `ArchitectureDiagram`, `ComparisonBlock`, `ProcurementBlock`, `RolloutTimeline`, `ProofStatusBlock` |
| Configurator | `TeamBuilder`, `RoleCard`, `LevelSelector`, `TeamPreset`, `TeamSummary`, `OrderModal` |
| Proof/trust | `ProofStatus`, `SourceNote`, `CaseCard`, `MetricCard`, `SecurityBadge` |
| Content | `Card`, `InfoCard`, `LegalContent`, `CatalogCard`, `Pagination`, `EmptyState` |

Обязательно:

- каждый TO BE component должен ссылаться на AS IS component id from `07-component-state-contract.json` or mark itself as new;
- каждый behavior-bearing component должен иметь migration type from `08-as-is-to-be-migration-map.json`;
- states must be visible, not only described in text.

### 3. Page Templates

Дизайнер должен подготовить desktop and mobile templates:

| Template | Pages |
|---|---|
| Ecosystem homepage | `/` |
| Product page | `/platform/`, `/agents/`, `/dev/`, `/forum/` |
| Delivery page | `/services/` |
| Estimate/proof catalog | `/offer/`, `/offer/<code>/` |
| Team configurator | `/price/` |
| Qualification/calculator | `/calculator/` |
| Trust/legal/contact | `/about/`, `/contacts/`, `/policies/` |

Обязательно:

- не делать landing-only hero вместо рабочей страницы;
- не прятать commercial entry points behind product language;
- сохранить текущие conversion paths or explicitly mark contract migration.

### 4. State Matrix

Должны быть покрыты состояния:

- buttons: default, hover, focus, active, loading, disabled;
- links: default, hover, focus;
- fields: default, focus, filled, invalid, disabled, autofill;
- checkbox: unchecked, checked, error, focus;
- forms: validation error, backend error, network error, success;
- modal: hidden, open, scroll, focus trap, close, success/error;
- toast: success, error, network error;
- FAQ: collapsed, open, hover, focus, long answer;
- chat: initial, user message, assistant message, typing, error, long answer, quick reply, handoff CTA;
- `/price/`: filter selected, search result, empty, card selected, level selected, preset selected, summary empty/filled, order modal, submit states;
- product proof/status: available, pilot-only, unavailable, not public;
- diagrams: desktop, tablet, mobile stacked fallback.

## Required Migration Decisions

For every mapping in `08-as-is-to-be-migration-map.json`, designer and frontend must approve:

| Decision | Meaning |
|---|---|
| `toBeComponentName` | Final component name in Figma/design docs |
| `migrationType` | `visual-restyle`, `contract-preserving-split`, `contract-migration`, `new-interaction` |
| `preserveSelectors` | Selectors/fields that must remain in implementation |
| `requiredGates` | Design/Frontend/QA/Security/SEO/Legal gates |
| `openDecisions` | Items that block implementation-ready status |

If `migrationType` becomes `contract-migration` or `new-interaction`, implementation cannot start as a simple visual task.

## Red Lines

Do not approve a TO BE design as implementation-ready if:

- form fields/payload change without lead-form/security review;
- chat payload, `group_id` or prefill behavior changes without integration review;
- `/price/` removes `workers_json` or staff-order selectors without endpoint review;
- FAQ drops `.faq-*` contract without `faq.js` migration;
- product page blocks lose `data-product-block` locators without visual smoke/sign-off update;
- proof-looking UI implies metrics, logos, certification or security claims without PM/Legal evidence;
- new analytics params can contain PII;
- mobile states are not designed for forms, chat and `/price/`.

## Acceptance Criteria

Design handoff is implementation-ready only when:

1. Token spec is approved and mapped to `05-design-tokens-as-is.json`.
2. Component/state spec covers every component in `07-component-state-contract.json`.
3. Migration map in `08-as-is-to-be-migration-map.json` is approved or updated.
4. Every high-risk component has Design, Frontend and QA gates.
5. Form/chat/price/security-affecting changes are marked as Security / Integration scope.
6. Product proof/status components map to claims governance.
7. Desktop and mobile templates exist for all target page families.
8. `npm run design:handoff:check` is green after documentation updates.

## Suggested Review Flow

1. Designer prepares token proposal and component inventory.
2. Frontend checks token/component feasibility against Bitrix and Tailwind.
3. PM/Legal review proof/status and claim components.
4. QA reviews state matrix and smoke requirements.
5. Designer updates migration map decisions.
6. Frontend runs:

```bash
npm run design:handoff:check
```

7. Implementation starts only for mappings whose gates and migration type are clear.
