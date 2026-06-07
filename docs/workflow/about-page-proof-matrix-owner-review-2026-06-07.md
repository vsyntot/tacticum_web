# About Page Proof Matrix Owner Review — 2026-06-07

Дата: 07.06.2026
Статус: owner-review package / no runtime approval / no public proof approval yet
Workflow lane: Full Feature discovery / owner review
Related gaps: `ABOUT-004`, `ABOUT-002`, `ABOUT-003`, `ABOUT-006`, `ABOUT-008`, canonical `CONTENT-001`, `CONTENT-002`, `CONTENT-003`, `ARCH-009`, `UI-005`

## Purpose

Этот документ задает proof-safe матрицу для страницы `/about/`. Он нужен, чтобы PM/Sales/Legal/Content могли решить, какие trust statements можно публиковать, какие можно давать только privately-by-request, а какие нельзя использовать без внешнего evidence.

Документ не разрешает публикацию новых claims. Он не содержит raw case text, testimonial text, customer names, contacts, admin links or private evidence.

## Decision Status Legend

| Status | Meaning |
|---|---|
| `public-safe-now` | Можно использовать как операционное описание без усиления claim, если текст не содержит метрик, логотипов, гарантий or named proof. |
| `public-after-owner-approval` | Можно публиковать только после named owner approval and evidence reference. |
| `private-by-request` | Можно использовать в sales/procurement conversation or private deck after owner approval; не публиковать на сайте. |
| `blocked-no-evidence` | Нельзя публиковать или обещать до появления проверяемого evidence and Legal/Sales/PM approval. |
| `remove-if-present` | Удалять из публичного слоя, если появится без approval. |

## No-Raw-Copy Evidence Rules

- Evidence references must use IDs, document names or safe summaries, not raw customer text.
- Do not include customer names, project titles, contacts, screenshots, email text, phone numbers or Bitrix admin URLs in Git docs.
- Do not include commercial terms, private pricing, procurement documents or NDA materials.
- Public proof needs a separate approval record before rendering.
- If evidence is private, the public page can say only that details are discussed during assessment, not that proof exists.

## Proof Matrix

| Trust Theme | Current Safe Public Direction | Decision Status | Owner Decision Needed | Allowed Public Wording | Blocked Wording / Risk |
|---|---|---|---|---|---|
| Company role | Tacticum is the team behind corporate AI products and implementation support. | `public-safe-now` | PM/Content confirm final positioning. | `Команда Tacticum помогает выбрать AI-сценарий, проверить пилот и встроить решение в рабочий процесс.` | `лидер рынка`, `лучшие эксперты`, unverifiable superiority claims. |
| Product line | Platform, Agents, Dev and Forum are product entry points already represented by public product pages. | `public-safe-now` | PM/Content maintain taxonomy. | `Platform, Agents, Dev и Forum помогают выбрать формат оценки, пилота, интеграций и запуска.` | Any claim that products are certified, market-leading, patented or universally suitable. |
| Operating model | Scenario, data, integrations, risk control, team roles and first step are safe process contours. | `public-safe-now` | PM/Architect confirm wording remains process-level. | `Перед пилотом фиксируем сценарий, данные, интеграции, ограничения и критерии следующего шага.` | Guarantees of launch success, fixed universal timelines or guaranteed ROI. |
| Data boundary | It is safe to say data/access/security constraints are checked before pilot. | `public-safe-now` for generic wording; `private-by-request` for client-specific details. | Security/Legal define what can be stated publicly. | `Учитываем доступы, источники данных, журналирование и ограничения безопасности.` | Personal data processing promises, compliance claims, certifications or exact security controls without approval. |
| Integration responsibility | It is safe to discuss CRM/ERP/wiki/helpdesk/documents as possible integration classes. | `public-safe-now` for examples; `public-after-owner-approval` for named systems as proof. | Architect/Content confirm examples are accurate. | `Интеграции с CRM, ERP, базами знаний, helpdesk, документами и внутренними API.` | Named customer stack, partner/vendor status, guaranteed compatibility. |
| Team expertise | Team section can show approved team iblock content already public. | `public-safe-now` for existing public team data; `public-after-owner-approval` for new bios/photos/roles. | PM/Team owner approve any person-data changes. | `Команда ведет проекты от оценки сценария до внедрения в рабочий процесс.` | New names, photos, bios, seniority, personal claims or certifications without approval. |
| Cases and outcomes | Public cases/results need existing proof approval path. | `blocked-no-evidence` for new `/about/` claims. | Sales/Legal/PM approve case/product evidence and public-render status. | None until evidence exists. | Customer logos, named cases, percentage improvements, cost reductions, error reduction, benchmark claims. |
| Certifications, registries, partnerships | No current approval in this package. | `blocked-no-evidence` | Legal/Sales/PM must provide evidence and exact wording. | None until approved. | `сертифицированный партнер`, registry claims, compliance badges, vendor logos. |
| Support and operation | Safe to say launch/support are discussed as part of implementation path. | `public-safe-now` for generic process; `public-after-owner-approval` for SLA. | PM/Sales/Legal decide if support model/SLA can be public. | `Планируем поддержку и развитие продукта после пилота.` | 24/7 support, fixed SLA, guaranteed response time, uptime, managed service promises. |
| Pricing and timelines | Safe to route to calculator/assessment and say estimate depends on scope. | `public-safe-now` for estimate route; `public-after-owner-approval` for fixed packages. | Sales/PM approve package claims. | `Состав работ, команда и бюджетный диапазон уточняются после оценки сценария.` | Fixed universal price/timeline for all clients, guaranteed budget or procurement promise. |
| Technology stack | Safe to describe capabilities and risk contours. | `public-safe-now` for contours; `public-after-owner-approval` for named stack as proof. | Architect/Content approve technical accuracy. | `Модели, база знаний, интеграции, аудит, наблюдаемость и эксплуатация.` | Tool zoo as proof, outdated tools, vendor partnership implication. |
| Governance / compliance | Can mention checks, audit and roles generically. | `private-by-request` or `public-after-owner-approval` for concrete governance artifacts. | Security/Legal/PM define public/private boundary. | `Фиксируем роли, аудит, журналирование и проверки качества.` | Compliance certifications, legal guarantees, procurement-ready claims without evidence. |

## Public Copy Rules For `/about/`

Use:

- operational verbs: `проверяем`, `фиксируем`, `планируем`, `уточняем`, `собираем`, `помогаем выбрать`;
- risk-aware nouns: `сценарий`, `данные`, `доступы`, `интеграции`, `ограничения`, `критерии`, `состав работ`, `ответственность`;
- soft next steps: `оценка`, `пилот`, `прототип`, `команда под запуск`.

Avoid without approval:

- `гарантируем`, `сократим на X%`, `лучший`, `лидер`, `сертифицированный`, `партнер`, `SLA`, `24/7`, `реестр`, `compliance`, `enterprise-grade security`;
- customer names, logos, private project details;
- statements that imply all clients get the same result, timeline or budget.

## Owner Review Checklist

| Owner | Required Decision |
|---|---|
| PM | Confirm `/about/` role as vendor trust page and approve public-safe positioning. |
| Content | Approve Russian-first wording and maintain glossary consistency. |
| Sales | Mark which proof themes are useful in sales but must remain private-by-request. |
| Legal | Approve or block claims touching customers, partnerships, certifications, compliance, SLA and guarantees. |
| Architect | Confirm technology/risk contour wording is accurate and not misleading. |
| Security | Confirm security/data/governance wording stays generic unless evidence exists. |
| QA | Verify rendered hygiene and no raw/PII evidence before release. |

## Approval Record Template

Use this format in a future owner evidence file. Keep it no-raw-copy.

```json
{
  "schema": "tacticum.about_proof_matrix.owner_decision.v1",
  "page": "/about/",
  "checked_at": "YYYY-MM-DDTHH:MM:SSZ",
  "checked_by": "owner-name-or-role",
  "decision": "approved-v1-safe | evidence-blocked | rewrite-required | deferred",
  "approved_public_themes": ["company-role", "operating-model"],
  "private_by_request_themes": ["data-boundary", "governance"],
  "blocked_themes": ["customer-results", "certifications", "sla"],
  "evidence_refs": ["safe-doc-id-or-ticket-id"],
  "notes_no_raw_copy": "No customer names, raw testimonials, private links or PII."
}
```

## Current Recommendation

- Keep current `/about/` production copy as proof-safe baseline.
- Do not add named proof, customer logos, metrics, certifications, partner status or SLA language until this matrix receives owner approval.
- If owners need stronger trust, prepare a private proof pack first, then decide which sanitized statements can become public.

## Verification Before Public Claim Changes

```bash
git diff --check
npm run content:public-hygiene:check
npm run content:public-hygiene:rendered:self-test
npm run seo:check
```

After deploy/cache clear:

```bash
npm run content:public-cache-clear
npm run content:public-hygiene:rendered:prod
npm run content:public-hygiene:rendered:prod:json
npm run page-content:source:http:wave2:prod
```

## Related Documents

- `docs/workflow/about-page-ux-content-challenge-gap-analysis-2026-06-07.md`
- `docs/workflow/about-page-ux-content-challenge-issue-backlog-2026-06-07.md`
- `docs/workflow/about-page-content-ownership-map-2026-06-07.md`
- `docs/workflow/content-storage-target-gap-analysis-2026-06-05.md`
- `docs/workflow/release-signoff-gates.md`
