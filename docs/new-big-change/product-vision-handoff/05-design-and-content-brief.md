# 05. Design And Content Brief

Дата: 01.06.2026

## Design Challenge

TO BE сайт должен выглядеть и работать как enterprise software сайт, а не как лендинг агентства услуг. Дизайн должен помогать объяснять сложную архитектуру: Platform core, прикладные продукты, deployment, security, proof, rollout.

## Связь С AS IS Дизайн-Системой

AS IS описан в `../../design-system-handoff/`:

- Bitrix template;
- Tailwind generated utilities;
- `styles/global.css`;
- локальные компоненты;
- vanilla JS;
- стабильные DOM/data contracts для форм, меню, модалок, чата и FAQ.

TO BE дизайн может менять визуальный язык, но не должен случайно ломать:

- `[data-tacticum-form]`;
- `data-form-id`;
- `[data-tacticum-consent]`;
- `[data-tacticum-chat]`;
- `[data-chat-input]`;
- `[data-chat-send]`;
- mobile menu contracts;
- footer/contact modal contracts;
- FAQ accordion contracts.

## Визуальное Направление

Рабочее направление из `../index.html`:

- спокойная корпоративная палитра;
- светлый фон;
- navy/accent как системные цвета;
- карточки с умеренным radius;
- крупные, но не маркетингово-пустые hero-блоки;
- технические схемы и module grids;
- моноширинная типографика для labels/architecture/code-like snippets.

Важно: `index.html` является прототипом, а не финальной дизайн-системой. Его токены можно использовать как стартовую гипотезу, но нужно привести к формальной token spec.

## Required Component Families

### Navigation

- desktop header with product dropdown;
- mobile menu;
- sticky CTA behavior;
- footer with product/service separation.

### Product Storytelling

- ecosystem map;
- platform layer diagram;
- product cards;
- module matrix;
- architecture callout;
- manifest/code snippet block;
- deployment model block;
- security/compliance badges.

### Commercial Conversion

- product-aware CTA;
- pilot CTA;
- estimate form;
- qualification fields;
- NDA/documentation request CTA;
- sticky mobile action bar if validated.

### Proof

- metric cards;
- case cards;
- benchmark cards;
- customer logo strip only if approved;
- testimonial cards only if approved;
- claim footnote/source pattern.

### Comparison And Decision Support

- comparison tables;
- self-build vs Tacticum;
- pure LLM vs scenario+LLM;
- generic coding assistant vs Tacticum Dev;
- SaaS vs on-prem vs PAK.

### Interaction

- tabs;
- accordions;
- filters;
- expandable architecture sections;
- FAQ;
- forms;
- chat surface;
- modal;
- toast/success/error states.

## Content Principles

### Tone

Тон должен быть:

- инженерный;
- уверенный;
- конкретный;
- проверяемый;
- без hype и обещаний “магического AI”.

### Avoid

- “AI решит все”;
- неподтвержденные проценты;
- “гарантируем результат” без условий;
- агрессивные claims против конкурентов;
- публичные workforce reduction тезисы;
- юридические утверждения без evidence.

### Prefer

- “пилот за 4-6 недель” только если подтверждено delivery;
- “по benchmark / по reference pilot / по кейсу” с явной маркировкой;
- “on-prem доступен при...” вместо абсолютного обещания;
- “поддержка канала X” только если есть готовность или roadmap-статус.

## Product-Aware Form Requirements

Новые формы должны передавать контекст:

- `product_interest`: platform / agents / dev / forum / services / team / unknown;
- `deployment_interest`: SaaS / on-prem / PAK / hybrid / unknown;
- `timeline`: urgent / quarter / half-year / research;
- `company_size` или диапазон;
- `data_readiness`: docs exist / systems integration / unknown;
- `source_page`;
- `lead_context`.

Изменение payload требует обновления `docs/workflow/lead-form-contract.md` и QA/security review.

## Interaction Notes For Implementation

- Новый JS/CSS подключать через `Bitrix\Main\Page\Asset`.
- Inline JS/CSS в публичных страницах не использовать как целевое решение.
- Новые страницы должны передавать page-specific assets через page properties.
- Product pages лучше собирать из локальных компонентов, чтобы не размножать CTA/proof/FAQ разметку.
- Любая форма должна сохранять CSRF/origin/rate-limit модель текущих REST endpoints.

## Design Deliverables Needed

Минимальный пакет от дизайнера:

- token spec;
- component library;
- homepage desktop/mobile;
- product page template desktop/mobile;
- 4 product page applications of the template;
- form states;
- navigation states;
- proof/claim component rules;
- migration map from AS IS components to TO BE components.

## Content Deliverables Needed

Минимальный пакет от редактора/PM:

- approved product taxonomy;
- product one-liners;
- homepage copy;
- four product page drafts;
- claim evidence table;
- FAQ by product;
- CTA taxonomy;
- product-aware lead form copy;
- SEO title/description/H1 for new pages.

