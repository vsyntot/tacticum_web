# Sprint 10 CJM And CTA Records

Дата: 02.06.2026

Статус: ready-for-owner-review drafts. Документ фиксирует recommended v1 role paths, CTA taxonomy, returning-lead journey and current form-context assumptions for Sprint 10.

## Scope

Related decisions:

- `D-05` Product pilot kits;
- `D-06` Role-based CTA and returning journey.

Related gaps:

- `CJM-001` - `CJM-006`;
- `PB-002`, `PB-004`;
- `ARCH-003`, `ARCH-004`;
- `REL-003`.

## Role-Based Journey Map

### Economic Buyer

Status: `draft`

```text
Homepage ecosystem view
  -> product fit matrix
  -> product page fit guide
  -> rollout / proof status
  -> pilot or assessment CTA
```

| Need | Current support | TO BE Sprint 10 decision |
|---|---|---|
| Understand vendor/product model | homepage ecosystem router | keep, but depends on Sprint 09 taxonomy |
| Choose product | fit matrix + product fit guide | approve role-specific route labels |
| Understand commercial path | rollout/proof readiness | packaging wording depends on Sprint 09 |
| Convert safely | product CTA | use `pilot` or `scenario-selection` CTA |

### Technical Buyer

Status: `draft`

```text
Product page
  -> architecture / procurement block
  -> pilot kit readiness
  -> architecture-session CTA
```

| Need | Current support | TO BE Sprint 10 decision |
|---|---|---|
| See architecture | text layers and procurement copy | pass to Sprint 11 diagram design |
| Understand integrations/data | procurement block | use `architecture-session` CTA |
| Avoid unsupported claims | safe-copy | depends on Sprint 09 claim statuses |
| Convert | product form | current `lead_next_step` fallback is v1-safe |

### Security / Procurement

Status: `draft`

```text
Product page procurement block
  -> request materials / architecture discussion
  -> existing form with product context
  -> manual follow-up
```

| Need | Current support | TO BE Sprint 10 decision |
|---|---|---|
| Identify review topics | procurement block | keep safe-copy only |
| Request documents | generic form/modal | `documentation-request` CTA is request-only, no downloads |
| Avoid false compliance | claims register | depends on Sprint 09 Legal/Security statuses |
| Continue discussion | returning-lead CTA | no personal recognition in v1 |

### Functional Owner

Status: `draft`

```text
Homepage or product page
  -> pilot kit
  -> readiness/input/output
  -> scenario-selection CTA
```

| Need | Current support | TO BE Sprint 10 decision |
|---|---|---|
| Recognize scenario | use-case cards | replace/extend with pilot kits after approval |
| Understand effort | rollout text | add readiness language in copy/design |
| See output | proof readiness | output must be artifact, not guarantee |
| Convert | `lead_scenario` select | current controlled select remains v1 path |

### Returning Lead

Status: `draft`

```text
Known stakeholder returns by direct link/search/email
  -> public page shows non-personal continue/discuss CTA
  -> user selects product/scenario/request type
  -> existing form/modal captures request context
  -> Sales continues manually in CRM
```

V1 rules:

- no frontend personal recognition;
- no CRM lookup from browser;
- no hidden PII transport beyond existing form;
- no document download endpoint;
- no account area.

## CTA Taxonomy

| CTA family | Purpose | Existing path | Payload rule | Status |
|---|---|---|---|---|
| `pilot` | User is ready to test a limited scenario | product page `#contact-form` | controlled `lead_scenario`, current form fields | draft |
| `architecture-session` | Technical/security/deployment review | product CTA or contact modal | `lead_next_step` context via fallback | draft |
| `scenario-selection` | User knows product area but not exact use case | product CTA with scenario select | controlled `lead_scenario` | draft |
| `documentation-request` | Procurement/security materials request | existing form/modal | request text only; no downloads | draft |
| `estimate` | Budget/team/project route | `/offer/`, `/calculator/`, `/price/` | existing routes/contracts | draft |
| `continue-discussion` | Returning lead wants follow-up | product CTA or modal | free text + product context | draft |

## Product CTA Mapping

| Product | Primary CTA family | Current scenarios | Recommended v1 CTA labels |
|---|---|---|---|
| Platform | `architecture-session` / `pilot` | `platform-assessment`, `platform-pilot`, `deployment-readiness` | "Обсудить платформенный пилот", "Разобрать архитектуру" |
| Agents | `scenario-selection` / `pilot` | `agent-scenario-selection`, `rag-documents-check`, `pilot-rollout` | "Выбрать сценарий пилота", "Проверить документы" |
| Dev | `scenario-selection` / `pilot` | `ai-workflow-assessment`, `quality-gates-pilot`, `design-system-guardrails` | "Оценить workflow", "Пилот quality gates" |
| Forum | `scenario-selection` / `pilot` | `dialog-flow-assessment`, `scenario-llm-pilot`, `support-analytics-review` | "Разобрать поток", "Проверить сценарий" |

## Sales Routing Decision

Current v1 mechanism:

```text
lead_product + lead_scenario + lead_next_step
  -> backend canonical profile
  -> human-readable context inside upstream task fallback
```

### Review Question

Is this enough for v1 Sales routing?

| Option | Use when | Implication | Status |
|---|---|---|---|
| Accept fallback for v1 | Sales can route by text context | no payload change, no Security / Integration scope | pending |
| Add structured CRM/upstream fields | Sales needs structured routing/reporting | open Sprint 12 `D-11`, update lead contract | pending |
| Add extra public fields | Sales needs more qualification from user | Design + QA + Security / Integration review required | pending |

Recommended v1:

```text
Accept current fallback for first release.
Open structured fields only after CRM/upstream confirms names, types and evidence path.
```

## Returning-Lead CTA Set

| Intent | Public label | Existing implementation path | Status |
|---|---|---|---|
| Continue product discussion | "Продолжить обсуждение" | product CTA or contact modal | draft |
| Architecture session | "Назначить архитектурную сессию" | product CTA with context | draft |
| Internal approval materials | "Запросить материалы для согласования" | existing form/modal request, no download | draft |
| Commercial follow-up | "Уточнить коммерческое предложение" | `/offer/` or contact modal | draft |

## Analytics Rule

Do not add new analytics params in Sprint 10. Current no-PII product events remain:

- product/page role;
- controlled scenario slug;
- form id;
- endpoint/status/code;
- page path.

No raw message, name, email, phone, company, document names or sensitive query strings.

## Approval Board

| Area | Owner | Status | Next action |
|---|---|---|---|
| Role paths | UX + PM | ready-for-owner-review | approve/rewrite journeys |
| CTA taxonomy | PM + UX + Sales | ready-for-owner-review | approve labels and mapping |
| Returning-lead path | Sales + UX + PM | ready-for-owner-review | confirm v1 copy-only path |
| Sales routing fallback | Sales + Backend + QA | ready-for-owner-review | accept fallback or open Sprint 12 structured-fields scope |
| Analytics scope | PM + Analytics + QA | unchanged | no new params in Sprint 10 |

## Implementation Notes

- Sprint 10 does not implement UI changes directly.
- Approved labels and pilot kits feed Sprint 11 design and Sprint 13 implementation readiness.
- If any CTA requires new fields, endpoint, document delivery or CRM lookup, move to Sprint 12 and Security / Integration lane.
