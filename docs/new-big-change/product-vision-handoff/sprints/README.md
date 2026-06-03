# TO BE Product Vision Sprints

Дата: 02.06.2026

Статус: implementation in progress. Базовый sprint backlog `00-08` сохранён как маршрут перехода от AS IS сайта `tacticum.ru` к TO BE продуктовой модели Tacticum Platform + Agents + Dev + Forum; post-challenge refinement спринты `09-14` добавлены 02.06.2026 на базе документов `24-28`.

## Назначение

Этот каталог фиксирует детализированный набор спринтов на базе:

- `../01-target-product-vision.md`;
- `../02-as-is-to-be-gap-analysis.md`;
- `../03-information-architecture-to-be.md`;
- `../04-product-page-briefs.md`;
- `../05-design-and-content-brief.md`;
- `../06-roadmap-and-workstreams.md`;
- `../07-risk-and-claims-register.md`;
- `../08-decisions-and-open-questions.md`;
- `../09-as-is-to-be-preservation-migration-map.md`;
- `../10-product-tech-challenge.md`;
- `../11-use-cases-and-cjm-target.md`;
- `../12-ux-ui-component-target.md`;
- `../13-architecture-components-stack-target.md`;
- `../14-gap-backlog-and-decision-register.md`;
- `../24-post-challenge-gap-analysis.md`;
- `../25-post-challenge-use-cases-and-cjm.md`;
- `../26-post-challenge-ux-ui-design-system.md`;
- `../27-post-challenge-architecture-components-stack.md`;
- `../28-post-challenge-decision-backlog.md`;
- `../../../design-system-handoff/`;
- `../../../workflow/current-state.md`;
- `../../../workflow/gap-analysis.md`.

Пакет нужен PM, дизайнеру, редактору, разработке, QA, SEO и legal/security, чтобы планировать работу не как общий редизайн, а как управляемую программу изменений.

## Список Спринтов

| Sprint | Документ | Цель | Статус |
|---|---|---|---|
| 00 | `sprint-00-decision-evidence-baseline.md` | Утвердить продуктовые решения и evidence до старта дизайна | planned |
| 01 | `sprint-01-ia-and-messaging.md` | Зафиксировать IA, URL strategy, messaging и page acceptance | planned |
| 02 | `sprint-02-design-system-and-prototypes.md` | Подготовить TO BE дизайн-систему и прототипы | planned |
| 03 | `sprint-03-implementation-foundation.md` | Спроектировать Bitrix/component/form foundation | planned |
| 04 | `sprint-04-homepage-and-navigation-mvp.md` | Реализовать новую главную и продуктовую навигацию | in-progress / first MVP slice |
| 05 | `sprint-05-platform-and-agents-pages.md` | Реализовать Platform и Agents pages | in-progress / FAQ + scenario + rollout + proof readiness + schema added |
| 06 | `sprint-06-dev-and-forum-pages.md` | Реализовать Dev и Forum pages | in-progress / FAQ + scenario + rollout + proof readiness + schema added |
| 07 | `sprint-07-proof-forms-seo-analytics-hardening.md` | Закрыть proof, forms, SEO, analytics и claim governance | in-progress / proof readiness + schema/static guards, external gates pending |
| 08 | `sprint-08-release-post-launch-and-handoff.md` | Провести release, smoke, sign-off и post-launch handoff | planned |
| 09 | `sprint-09-product-taxonomy-claims-packaging.md` | Закрыть taxonomy, claims, packaging and `/agents/` vs `/aiagents/` decisions | ready-for-owner-review / external approvals pending |
| 10 | `sprint-10-pilot-kits-cjm-cta.md` | Детализировать pilot kits, role-based CJM, CTA taxonomy and returning journey | ready-for-owner-review / Sprint 09 approvals pending |
| 11 | `sprint-11-design-system-to-be-approval.md` | Утвердить TO BE tokens, components, states, diagrams and proof/status UI | ready-for-owner-review / Sprint 09-10 approvals pending |
| 12 | `sprint-12-architecture-crm-analytics-foundation.md` | Зафиксировать content/component architecture, CRM/upstream qualification and analytics evidence model | planned / post-challenge |
| 13 | `sprint-13-product-copy-ui-implementation-readiness.md` | Подготовить implementation-ready copy/UI/SEO/QA scope after approvals | planned / post-challenge |
| 14 | `sprint-14-release-evidence-post-launch-governance.md` | Закрыть production release evidence and post-launch governance | planned / post-challenge |

Общая карта и зависимости: `00-sprint-roadmap.md`.

## Контрольные Документы

| Документ | Назначение |
|---|---|
| `99-gap-to-sprint-traceability.md` | Показывает, какие AS IS / TO BE gaps, challenge IDs и decision backlog IDs закрывает каждый спринт |
| `99-sprint-execution-board.md` | Дает PM/lead-level execution board: work packages, blockers, gates, verification and release readiness |
| `sprint-09-review-workbook.md` | Рабочая форма для проведения Sprint 09 review по taxonomy, boundaries, claims and packaging |
| `sprint-09-decision-records.md` | Draft decision records for `D-01` - `D-04`; фиксирует recommended v1 baseline and approval statuses |
| `sprint-09-approval-request.md` | Owner-facing approval request: кто и какие решения должен подтвердить по Sprint 09 |
| `sprint-09-evidence-intake.md` | Evidence/source intake template for proof, claims, packaging and `/aiagents/` SEO decision |
| `sprint-10-review-workbook.md` | Рабочая форма для Sprint 10 review по pilot kits, CJM and CTA taxonomy |
| `sprint-10-pilot-kit-records.md` | Draft pilot kits for Platform, Agents, Dev and Forum |
| `sprint-10-cjm-cta-records.md` | Draft role paths, CTA taxonomy, returning-lead journey and Sales routing decision |
| `sprint-10-approval-request.md` | Owner-facing approval request for `D-05` and `D-06` |
| `sprint-11-review-workbook.md` | Рабочая форма для Sprint 11 review по token source, component family, states, diagrams and proof/status UI |
| `sprint-11-decision-records.md` | Draft decision records for `D-07` - `D-09`; фиксирует recommended v1 baseline and approval questions |
| `sprint-11-state-matrix.md` | Detailed state matrix for behavior-bearing components, selectors and QA/smoke implications |
| `sprint-11-approval-request.md` | Owner-facing approval request for Designer/Frontend/QA/PM/Legal/Architect по Sprint 11 |

## Принцип Планирования

Спринты идут в строгой зависимости:

```text
Sprint 00 -> Sprint 01 -> Sprint 02 -> Sprint 03
                                      -> Sprint 04 -> Sprint 05 -> Sprint 06
                                      -> Sprint 07 -> Sprint 08
```

`Sprint 00` нельзя пропускать: без него дизайн и разработка начнут опираться на неподтвержденные regulatory, customer и metric claims.

Post-challenge wave follows the latest decision backlog:

```text
Sprint 09 -> Sprint 10 -> Sprint 11 -> Sprint 12 -> Sprint 13 -> Sprint 14
```

Эта волна не дублирует `00-08`: она уточняет оставшиеся decision/evidence/design/architecture gates после уже реализованного safe product-first MVP.

MVP-срезы, уже добавленные 01.06.2026 в Sprint 03-07, считаются безопасным product-first layer, но не закрывают весь TO BE scope. Они сохраняют AS IS лидогенерацию, добавляют shared product data layer and product pages, однако vendor-grade TO BE требует evidence, CJM, design system, structured qualification, content ownership decision and release gates.

## Workflow Lane

Основная lane: `Full Feature`.

Дополнительные gates:

- `Design gate` - обязателен для Sprint 01-06;
- `SEO gate` - обязателен для Sprint 01, 04-08;
- `QA early gate` - обязателен для Sprint 03, 07, 08;
- `Security / Integration` - обязателен для form payload, AI/chat, PII, claims про on-prem/КИИ/152-ФЗ;
- `ADR gate` - вероятен в Sprint 03, если меняются URL/content model/form contract/component architecture.

## Что Считать Готовностью Программы

Программа считается готовой к release, когда:

- продуктовая taxonomy утверждена;
- рискованные claim'ы имеют status и safe public wording;
- новая IA и URL strategy согласованы;
- TO BE дизайн-система покрывает homepage и product pages;
- product-aware lead flow описан и проверен;
- homepage, Platform, Agents, Dev, Forum реализованы или явно выведены из первого release scope;
- SEO/canonical/sitemap/analytics проверены;
- production smoke и manual success-flow пройдены;
- release sign-off закрыт.
