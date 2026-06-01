# 10. Product And Tech Challenge

Дата: 01.06.2026

Статус: challenge-документ к текущему AS IS / TO BE пакету. Использовать перед дизайном, перед следующими спринтами и перед утверждением product-first релиза.

## Executive Verdict

Текущее решение корректно как безопасный MVP:

- AS IS лидогенерация, `/offer/`, `/price/`, `/calculator/`, формы, SEO и Bitrix contracts сохранены;
- добавлен product-first слой `/platform/`, `/agents/`, `/dev/`, `/forum/`;
- продуктовые страницы собраны через общий renderer;
- claims очищены до safe wording;
- product CTA передает controlled `lead_*` context без изменения upstream contract.

Но это пока не финальный TO BE сайт продуктовой экосистемы.

Текущее состояние ближе к:

```text
стабильный lead-generation сайт
  + безопасный product-first MVP слой
  + proof readiness вместо реального proof
```

Целевое состояние должно быть:

```text
enterprise AI software vendor site
  + ясная продуктовая линейка
  + role-based CJM
  + use-case decision support
  + procurement/security proof
  + structured product qualification
  + design-system based implementation
```

Главный риск: сайт уже начинает звучать как продуктовый vendor, но пока не доказывает vendor-grade зрелость через use cases, deployment model, procurement/security evidence, кейсы, метрики, packaging и real funnel data.

## Challenge Scope

Проверяем не только код и не только дизайн. Проверяем связку:

- product strategy;
- use cases;
- CJM;
- UX;
- UI;
- content/proof;
- architecture;
- components;
- stack;
- release evidence.

## Maturity Snapshot

| Область | Current state | Target state | Maturity |
|---|---|---|---|
| Positioning | Product-first narrative добавлен | Vendor-grade AI software ecosystem | medium |
| Product taxonomy | Platform/Agents/Dev/Forum есть в URL и nav | Taxonomy подтверждена PM/Sales/SEO и понятна покупателям | medium |
| Use cases | Есть категории и safe descriptions | Есть decision-ready use cases с trigger, actor, data, outcome, proof | low/medium |
| CJM | Есть product page -> CTA | Есть role-based enterprise journeys | low |
| UX | MVP one-pagers и routing | Decision support, comparison, procurement/security paths | medium |
| UI | Generic B2B SaaS blocks | Enterprise software visual system with diagrams/proof/status components | medium |
| Architecture | Shared renderer + Bitrix contracts | Content model, product components, CRM-ready lead qualification | medium |
| Components | CTA/chat/FAQ/content/offer stable | Product storytelling component family | low/medium |
| Stack | Bitrix + static Tailwind + vanilla JS stable | Token pipeline, previewable components, stronger frontend governance | medium |
| Proof | Claim hygiene and proof readiness | Evidence-backed product proof | low |
| Release | Automated local gates strong | External evidence closed: deploy, forms, Метрика, admin, upstream | medium/external |

## Challenge Matrix

| ID | Area | Challenge | Why It Matters | Target Decision / Output | Priority |
|---|---|---|---|---|---|
| PTC-001 | Positioning | Product-first слой может восприниматься как rebrand услуг, а не реальный vendor model | Enterprise buyer ищет продукт, не только команду внедрения | Утвердить product packaging and vendor proof model | P1 |
| PTC-002 | Taxonomy | Platform / Agents / Dev / Forum понятны внутри команды, но не доказано, что понятны рынку | Непонятная taxonomy снижает conversion и усложняет sales | Провести message test / sales review по taxonomy | P1 |
| PTC-003 | Use cases | Страницы перечисляют сценарии, но не дают decision-grade use cases | Buyer не понимает, какой продукт выбрать и что будет в пилоте | Для каждого продукта описать 3-5 primary use cases | P1 |
| PTC-004 | CJM | Все пути сходятся в общую форму | Enterprise CJM требует разных next steps для CIO, architect, security, function owner | Role-based journey map and CTA taxonomy | P1 |
| PTC-005 | Platform | Platform звучит архитектурно, но не привязана к buying triggers | Platform может казаться "лишним слоем" | Добавить triggers: multi-AI portfolio, cost, audit, RBAC, on-prem | P1 |
| PTC-006 | Agents vs Forum | Оба продукта могут читаться как "AI-боты" | Каннибализация смыслов и SEO | Зафиксировать boundary: internal assistants vs customer dialogue platform | P1 |
| PTC-007 | Dev | Dev понятен engineering audience, но требует более конкретных workflows | Без workflow examples продукт выглядит абстрактно | Dev use cases: review, design compliance, legacy refactor, requirements-to-tests | P1 |
| PTC-008 | Proof | Proof readiness не заменяет customer proof | Vendor credibility требует фактов | Evidence backlog with owners, source, public/private wording | P0/P1 |
| PTC-009 | Claims | Safe wording есть, но evidence backlog не закрыт | Регуляторные и customer claims нельзя случайно опубликовать | Claims governance workflow before design/content release | P0 |
| PTC-010 | UX | Product pages слишком однотипны | Разные продукты требуют разных decision-support patterns | Product-specific blocks and comparison modules | P2 |
| PTC-011 | UI | Product architecture block сейчас текстовый, не diagram-grade | Enterprise buyers ожидают схемы, матрицы, deployment boundaries | Architecture diagram component family | P2 |
| PTC-012 | Forms | `lead_*` context нормализуется в backend canonical profile, но upstream пока получает только `task` fallback | Sales/CRM лучше читает заявку, но полноценная сегментация требует CRM/upstream contract | Approve fallback for first release or decide CRM/upstream structured product fields | P1 |
| PTC-013 | Analytics | Product funnel events добавлены в code-level taxonomy, Метрика evidence pending | Можно измерять first-pass product view/CTA/form funnel без PII, но goals ещё нужно подтвердить | Product funnel analytics map, Метрика goals and evidence | P2 |
| PTC-014 | Content model | Product data вынесена из page entries в shared PHP data files, но CMS/hybrid ownership ещё не решён | Масштабирование proof/use cases/FAQ всё ещё требует явного ownership model | Decide whether shared Git data is enough or Bitrix/hybrid content model is needed | P2 |
| PTC-015 | Components | `product_page.php` теперь bootstrap/helpers, visual blocks вынесены в `product_page_blocks/*.php`; Bitrix component/preview split ещё не решён | Partial split снижает монолитность, но не заменяет TO BE component previews/design specs | Decide whether partials are enough for first release or local components/previews are needed | P2 |
| PTC-016 | Design system | AS IS tokens минимальны | Дизайнер не сможет дать воспроизводимую систему без token spec | TO BE token/component/state specification | P1 |
| PTC-017 | Stack | Storybook/component preview/token pipeline still absent; first `data-product-block` locator contract and rendered smoke evidence exist | Regression risk при росте UI complexity снижен на уровне QA/refactor selectors and rendered evidence, но не закрыт визуальный preview gap | Decide lightweight preview/doc workflow for Bitrix components | P3 |
| PTC-018 | Release evidence | Code-level gaps = 0, external gates pending | Нельзя считать TO BE production-ready без фактических gates | Close product-first release sign-off evidence | P1 |

## Strong Current Assets To Protect

Эти элементы нельзя ослабить редизайном без отдельного migration decision:

- `/offer/` как proof/estimate catalog and indexed detail layer;
- `/price/` как сложный staff/team configurator;
- `/calculator/` chat-to-lead handoff;
- `tacticum:lead.cta` and `forms.js` contracts;
- `tacticum:chat.surface` contracts;
- `faq.js` and FAQ markup contract;
- SEO/canonical/sitemap governance;
- Bitrix local component boundary;
- no-PII analytics model;
- claim hygiene and proof matrix discipline.

## Main Product Challenge

Сайт должен перестать просто отвечать "что можно заказать" и начать отвечать:

1. Почему Tacticum является продуктовым AI vendor.
2. Какой продукт нужен конкретной роли и ситуации.
3. Как выглядит безопасный путь от интереса к пилоту.
4. Какие proof/evidence доступны публично.
5. Что пока является roadmap/evidence pending.

## Main Technology Challenge

Технологически текущий MVP защищает AS IS, но для TO BE нужны решения:

- где хранить product facts/use cases/proof;
- какие product blocks становятся компонентами;
- какие fields должны стать структурированными в CRM/upstream;
- как дизайн-токены попадут в Tailwind/Bitrix;
- какие интерактивы можно оставить vanilla JS, а какие требуют отдельной архитектуры;
- как закрывать release evidence без ручного "поверили на слово".

## Recommended Next Move

Перед следующей реализацией провести короткий decision pass:

1. Утвердить `11-use-cases-and-cjm-target.md`.
2. Утвердить `12-ux-ui-component-target.md` как brief для дизайнера.
3. Утвердить `13-architecture-components-stack-target.md` как dev/architect target.
4. Превратить `14-gap-backlog-and-decision-register.md` в sprint backlog.
