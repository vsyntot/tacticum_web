# TO BE Product Vision Sprints

Дата: 01.06.2026

Статус: implementation in progress. Базовый sprint backlog сохранён как маршрут перехода от AS IS сайта `tacticum.ru` к TO BE продуктовой модели Tacticum Platform + Agents + Dev + Forum; первые безопасные MVP-срезы добавлены 01.06.2026.

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

Общая карта и зависимости: `00-sprint-roadmap.md`.

## Принцип Планирования

Спринты идут в строгой зависимости:

```text
Sprint 00 -> Sprint 01 -> Sprint 02 -> Sprint 03
                                      -> Sprint 04 -> Sprint 05 -> Sprint 06
                                      -> Sprint 07 -> Sprint 08
```

`Sprint 00` нельзя пропускать: без него дизайн и разработка начнут опираться на неподтвержденные regulatory, customer и metric claims.

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
