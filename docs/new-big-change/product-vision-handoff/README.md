# Product Vision Handoff - Tacticum TO BE

Дата: 02.06.2026

Статус: рабочий пакет для фиксации целевого продуктового видения и планирования перехода от текущего сайта `tacticum.ru` к новой продуктовой архитектуре.

## Назначение

Этот каталог переводит исходные материалы из `docs/new-big-change/` в управляемый набор решений для продукта, сайта, дизайна, контента и разработки.

Пакет отвечает на вопросы:

- каким должен быть целевой продуктовый образ Tacticum;
- как новая модель соотносится с текущим сайтом;
- какие gaps нужно закрыть перед редизайном и реализацией;
- какие страницы, компоненты и доказательства нужны;
- какие утверждения нельзя публиковать без проверки.

## Исходники

Основные материалы:

- `../tacticum.md` - зонтичная экосистемная презентация;
- `../platform.md` - Tacticum Platform как инфраструктурное ядро;
- `../agents.md` - Tacticum Agents;
- `../dev.md` - Tacticum Dev;
- `../forum.md` - Tacticum Forum;
- `../index.html` - прототип новой главной и черновой визуальной системы;
- `../../design-system-handoff/` - AS IS дизайн-система, компоненты и JS-контракты;
- `../../workflow/current-state.md` - фактическое состояние сайта;
- `../../workflow/gap-analysis.md` - текущие технологические и продуктовые gaps сайта.

PDF-файлы в `docs/new-big-change/` рассматриваются как презентационные экспорты соответствующих markdown/html материалов.

## Карта Документов

1. `01-target-product-vision.md` - целевое продуктово-рыночное видение.
2. `02-as-is-to-be-gap-analysis.md` - gap analysis между текущим сайтом и целевой моделью.
3. `03-information-architecture-to-be.md` - целевая информационная архитектура сайта.
4. `04-product-page-briefs.md` - брифы страниц Platform, Agents, Dev, Forum.
5. `05-design-and-content-brief.md` - требования к TO BE дизайну, контенту и интерактиву.
6. `06-roadmap-and-workstreams.md` - дорожная карта и рабочие потоки.
7. `07-risk-and-claims-register.md` - реестр рискованных claim'ов и правил публикации.
8. `08-decisions-and-open-questions.md` - зафиксированные решения и открытые вопросы.
9. `09-as-is-to-be-preservation-migration-map.md` - карта сохранения и миграции AS IS возможностей в TO BE.
10. `10-product-tech-challenge.md` - продуктово-технологический challenge текущего решения.
11. `11-use-cases-and-cjm-target.md` - целевые use cases и CJM по ролям.
12. `12-ux-ui-component-target.md` - целевая UX/UI и компонентная модель для дизайна.
13. `13-architecture-components-stack-target.md` - целевая архитектура, компоненты и stack decisions.
14. `14-gap-backlog-and-decision-register.md` - backlog гэпов и решений после challenge.
15. `15-gap-closure-master-plan.md` - последовательный план закрытия известных AS IS / TO BE gaps.
16. `16-gap-closure-action-register.json` - machine-readable actions по каждому non-closed gap ID; проверяется через `npm run product:gaps:check`.
17. `17-local-gap-decision-briefs.md` - первый baseline для локально продвигаемых gaps: Dev workflows, CTA, returning lead, diagrams, icons, metadata.
18. `18-phase-1-product-decision-review-pack.md` - Phase 1 review package для taxonomy, boundaries, proof/claims, packaging, `/agents/` vs `/aiagents/`, fit guide and procurement/use-case review.
19. `19-phase-3-architecture-integration-decision-pack.md` - Phase 3 review package для content ownership, renderer boundary, lead qualification, CRM/upstream fields and product analytics evidence.
20. `20-phase-4-seo-content-decision-pack.md` - Phase 4 review package для product SEO clusters, `/agents/` vs `/aiagents/`, proof/case map, metadata and rendered SEO evidence.
21. `21-phase-5-release-evidence-closure-pack.md` - Phase 5 closure package для deploy smoke, rendered SEO evidence, manual success-flow, Metrika, Bitrix admin, legacy inventory and upstream/staff gates.
22. `22-phase-2-design-system-approval-pack.md` - Phase 2 approval package для TO BE token source, component family, form states, proof/status UI, `/price/` mobile UX and chat states.
23. `23-accepted-risk-monitoring-pack.md` - monitoring package для accepted risks: CSP enforce and industry/scenario noindex.
24. `24-post-challenge-gap-analysis.md` - детализированный post-challenge gap analysis: что сильное, что не закрыто, какие gaps уточнены.
25. `25-post-challenge-use-cases-and-cjm.md` - детализация use cases, pilot kits, role-based CJM and CTA taxonomy.
26. `26-post-challenge-ux-ui-design-system.md` - детализация UX/UI, product components, proof/status UI, states and design-system gates.
27. `27-post-challenge-architecture-components-stack.md` - детализация architecture, component boundaries, content ownership, CRM/upstream and stack decisions.
28. `28-post-challenge-decision-backlog.md` - prioritized decision backlog and review sequence по итогам challenge.
29. `sprints/README.md` - детализированный sprint backlog для перехода AS IS -> TO BE, включая базовую волну `00-08` and post-challenge refinement wave `09-14`.

## Как Читать

Для продуктовой синхронизации начать с `01-target-product-vision.md`, затем перейти к `02-as-is-to-be-gap-analysis.md` и `10-product-tech-challenge.md`.

Для дизайнера начать с `03-information-architecture-to-be.md`, `04-product-page-briefs.md`, `05-design-and-content-brief.md`, `11-use-cases-and-cjm-target.md` и `12-ux-ui-component-target.md`, параллельно держа открытым `../../design-system-handoff/README.md`.

Для разработки и PM начать с `02-as-is-to-be-gap-analysis.md`, `06-roadmap-and-workstreams.md`, `07-risk-and-claims-register.md`, `09-as-is-to-be-preservation-migration-map.md`, `13-architecture-components-stack-target.md` и `14-gap-backlog-and-decision-register.md`.

Для post-challenge review начать с `24-post-challenge-gap-analysis.md`, затем разобрать `25-post-challenge-use-cases-and-cjm.md`, `26-post-challenge-ux-ui-design-system.md`, `27-post-challenge-architecture-components-stack.md` and `28-post-challenge-decision-backlog.md`.

## Challenge Layer

Документы `10-16` добавлены после отдельного challenge текущего product-first MVP. Они не заменяют исходное видение `01-09`, а уточняют:

- где MVP уже достаточно безопасен;
- где TO BE всё ещё не доказан продуктово;
- какие use cases и CJM нужны до полноценного редизайна;
- какие компоненты и состояния нужны дизайнеру;
- какие архитектурные решения нужно принять до масштабирования;
- какие gaps нельзя считать закрытыми без внешней evidence.

Документы `17-23` переводят gaps из `14` в review/approval packages. Документы `24-28` фиксируют повторный post-challenge: они детализируют слабые места текущего решения, но не создают новую систему gap ID и не закрывают gaps без owner approval and evidence.

Sprint-пакет в `sprints/` теперь содержит две волны: baseline `00-08` and post-challenge `09-14`. Вторая волна напрямую опирается на `24-28` and decision IDs `D-01` - `D-13`.

## Уровни Уверенности

В документах используются три уровня:

- `decision` - можно брать как рабочее решение для TO BE;
- `hypothesis` - логически следует из материалов, но требует подтверждения владельцем продукта;
- `needs evidence` - нельзя публиковать или реализовывать как публичное обещание без фактического подтверждения.

## Важное Ограничение

Этот пакет не является юридическим заключением, регуляторной экспертизой или финальным sales deck. Он фиксирует продуктовую логику и список проверок, которые нужно закрыть перед публичной публикацией claim'ов.
