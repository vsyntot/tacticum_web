# Product Vision Handoff - Tacticum TO BE

Дата: 01.06.2026

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
15. `sprints/README.md` - детализированный sprint backlog для перехода AS IS -> TO BE.

## Как Читать

Для продуктовой синхронизации начать с `01-target-product-vision.md`, затем перейти к `02-as-is-to-be-gap-analysis.md` и `10-product-tech-challenge.md`.

Для дизайнера начать с `03-information-architecture-to-be.md`, `04-product-page-briefs.md`, `05-design-and-content-brief.md`, `11-use-cases-and-cjm-target.md` и `12-ux-ui-component-target.md`, параллельно держа открытым `../../design-system-handoff/README.md`.

Для разработки и PM начать с `02-as-is-to-be-gap-analysis.md`, `06-roadmap-and-workstreams.md`, `07-risk-and-claims-register.md`, `09-as-is-to-be-preservation-migration-map.md`, `13-architecture-components-stack-target.md` и `14-gap-backlog-and-decision-register.md`.

## Challenge Layer

Документы `10-14` добавлены после отдельного challenge текущего product-first MVP. Они не заменяют исходное видение `01-09`, а уточняют:

- где MVP уже достаточно безопасен;
- где TO BE всё ещё не доказан продуктово;
- какие use cases и CJM нужны до полноценного редизайна;
- какие компоненты и состояния нужны дизайнеру;
- какие архитектурные решения нужно принять до масштабирования;
- какие gaps нельзя считать закрытыми без внешней evidence.

## Уровни Уверенности

В документах используются три уровня:

- `decision` - можно брать как рабочее решение для TO BE;
- `hypothesis` - логически следует из материалов, но требует подтверждения владельцем продукта;
- `needs evidence` - нельзя публиковать или реализовывать как публичное обещание без фактического подтверждения.

## Важное Ограничение

Этот пакет не является юридическим заключением, регуляторной экспертизой или финальным sales deck. Он фиксирует продуктовую логику и список проверок, которые нужно закрыть перед публичной публикацией claim'ов.
