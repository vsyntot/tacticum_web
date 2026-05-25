# Product / Marketing Gap Analysis

Дата: 25.05.2026

## Context

Аудит проведен после закрытия текущего code-level хвоста по сайту. Техническая база, Bitrix-компоненты, CSS/JS hygiene, SEO sitemap/indexability и known gaps guard уже приведены в управляемое состояние. Новый challenge касается не runtime-стабильности, а продуктовой упаковки, маркетинговой архитектуры и конверсионной логики публичного сайта.

Проверенные зоны:

- `/` - главный экран, общий positioning, hero chat, кейсы, отзывы, CTA;
- `/services/` - услуги, процесс внедрения, technology proof, CTA;
- `/price/` - T&M ставки, заказ специалистов, AI-калькулятор, CTA;
- `/calculator/` - AI-калькулятор как лид-магнит;
- `/offer/` и `/offer/<code>/` - каталог и детали расчетов;
- `/aiagents/` - Telegram AI-бот как отдельный продуктовый сценарий;
- `/contacts/` - контактная конверсия и trust/legal context;
- верхнее, мобильное и нижнее меню;
- shared CTA и form taxonomy.

## Executive Summary

Сайт уже имеет сильные продуктовые активы: AI-калькулятор, каталог расчетов, тарифы специалистов, Telegram AI-агенты, кейсы и формы заявок. Основной незакрытый разрыв находится не в технологии, а в коммерческой упаковке: сайт одновременно продает AI-внедрение, T&M-команды, расчет проекта и готового Telegram-бота, но не всегда объясняет, какой путь подходит конкретному покупателю.

Целевая продуктовая архитектура должна собрать сайт в понятную лестницу:

| Entry | URL | User intent | Target conversion |
|---|---|---|---|
| Рассчитать проект | `/offer/`, `/calculator/` | Пользователь хочет понять бюджет, сроки, команду | Получить точную оценку под свою задачу |
| Внедрить AI-решение | `/services/` | Пользователь ищет подрядчика под AI/automation initiative | Обсудить проект и следующий этап работ |
| Собрать команду | `/price/` | Пользователь ищет специалистов или T&M-команду | Заказать состав команды |
| Запустить AI-бота | `/aiagents/` | Пользователь хочет быстро проверить Telegram-bot scenario | Попробовать демо или запросить прототип |

## Implementation Status — 25.05.2026

Sprint 15 implemented the product-marketing architecture without creating new indexable URLs and without changing AI upstream contracts. Automated local checks passed on 25.05.2026; PHP lint remains a CI/deploy fallback because PHP CLI is not installed locally.

| ID | Status | Closure |
|---|---|---|
| `PMG-001` | closed | Home hero now states the business outcome and routes users into estimate, implementation, team and AI-bot paths |
| `PMG-002` | closed | Product ladder is reflected in route cards, page intros, service cross-links and menu labels |
| `PMG-003` | closed | `/price/` now frames rates as a managed team selection flow while preserving price cards and staff-order contracts |
| `PMG-004` | closed | `/offer/` list/detail copy explains examples vs final estimates and preserves context in the offer CTA |
| `PMG-005` | closed | `/calculator/` shows expected output: budget range, timeline, team, risks and next step |
| `PMG-006` | closed | Risky proof claims were removed or rewritten into safer, verifiable wording |
| `PMG-007` | closed | `/aiagents/` is positioned as a B2B-service entry for checking Telegram bot scenarios and requesting a prototype |
| `PMG-008` | closed | CTA taxonomy is represented through stable `form_id`, page-specific copy and hidden `lead_*` context |
| `PMG-009` | closed | Industry/scenario segmentation uses existing `/offer/catalog/...` states that remain `noindex,follow` with canonical `/offer/` |
| `PMG-010` | closed | Shared CTA forms collect optional `lead_budget`/`lead_timeline`; backend appends allowlisted context to existing `task` |

Residual external work remains outside Sprint 15 implementation: production deploy, post-deploy smoke, Метрика goal confirmation and manual release sign-off evidence.

## Gaps

| ID | Priority | Area | Current state | Product impact | Target behavior |
|---|---|---|---|---|---|
| PMG-001 | P1 | Positioning / main hero | Главный экран говорит "Искусственный интеллект для реального бизнеса", но не фиксирует ICP, боль, business outcome и основной путь | Пользователь понимает категорию, но не получает достаточно сильной причины продолжить путь | Hero должен ясно отвечать: кому помогаем, какую бизнес-задачу закрываем, какой результат даем, какой первый шаг выбрать |
| PMG-002 | P1 | Product architecture / navigation | AI-внедрение, T&M, расчет, AI-боты и каталог расчетов существуют рядом, но не собраны в единую лестницу | Пользователь сам выбирает между похожими пунктами и может уйти не в тот сценарий | Навигация и страницы должны явно разделять 4 входа: рассчитать проект, внедрить AI, собрать команду, запустить AI-бота |
| PMG-003 | P1 | `/price/` value framing | `/price/` упакован как "почасовые ставки IT-специалистов по модели T&M" | Есть риск commodity-восприятия: сайт выглядит как прайс-лист людей, а не как AI/IT delivery team | `/price/` должен продавать управляемую команду под задачу, сохранив прозрачность ставок как proof и инструмент выбора |
| PMG-004 | P1 | `/offer/` conversion | `/offer/` хорошо работает как каталог примеров расчетов, но слабее ведет к заявке на точный расчет | SEO-трафик может потреблять карточки без следующего конверсионного действия | Каталог и detail pages должны усиливать bridge: "нашли похожий расчет - получите точную смету под свою задачу" |
| PMG-005 | P1 | `/calculator/` promise | Калькулятор выглядит как чат, но слабо объясняет итоговый артефакт и зачем оставлять контакты | Пользователь может не понимать, что именно получит и насколько результат применим | Страница должна показать формат результата: команда, сроки, бюджетный диапазон, риски, следующий шаг |
| PMG-006 | P1 | Proof system | На сайте встречаются разные proof claims: `120+ проектов`, `50+ AI-проектов`, `98%`, `15+ лет`, "гарантия результата" | Несогласованные цифры и сильные обещания могут снижать доверие | Нужна единая proof system: какие цифры используем, где, чем подтверждаем, какие формулировки считаем безопасными |
| PMG-007 | P2 | `/aiagents/` product tone | `/aiagents/` звучит как отдельный B2C/SaaS лендинг: "создай за 60 секунд", "начни продавать сегодня" | Тональность выбивается из B2B-консалтинга и может размывать бренд | Либо явно позиционировать AI-агентов как отдельный продукт, либо привести тон к B2B и связать с основной лестницей |
| PMG-008 | P1 | CTA taxonomy | CTA в разных местах похожи: "Получить предложение", "Запросить расчет", "Оставить заявку" | CTA не всегда соответствует стадии пользователя и странице | Для каждого entry нужен свой CTA promise, form_id, hidden context и expected next step |
| PMG-009 | P1 | Industry / scenario segmentation | 1117 расчетов дают отраслевой контент, но сайт почти не использует его как маркетинговую систему | Потерян потенциал SEO и персонализации под отрасли и сценарии | На базе `/offer/` нужно сформировать отраслевые и сценарные входы, внутренние перелинковки и landing logic |
| PMG-010 | P1 | Lead qualification | Формы собирают контакт и сообщение, но не везде квалифицируют бюджет, срок, тип задачи, выбранный расчет или состав команды | Sales получает менее структурированные лиды, а пользователь не видит персонализацию пути | Формы должны передавать контекст источника и мягкие квалификационные поля без лишнего трения |

## Detailed Notes

### PMG-001 - Positioning / main hero

Evidence:

- `index.php` hero H1: "Искусственный интеллект для реального бизнеса";
- первичные CTA: "Оценить свою идею" и "Получить консультацию";
- hero chat показывает возможности AI, но не привязывает их к четкому коммерческому пакету.

Challenge:

Фраза корректная, но слишком широкая. Для B2B-покупателя лучше сразу обозначить: "оценим, спроектируем и внедрим AI/IT-решение под бизнес-задачу", либо выбрать еще более узкий ICP. Главная должна быть маршрутизатором, а не просто декларацией компетенции.

Acceptance criteria:

- главный hero фиксирует ICP, problem, outcome и primary next step;
- в первом viewport есть 2-3 маршрута, а не конкурирующие абстрактные CTA;
- формулировка не конфликтует с `/price/`, `/offer/`, `/calculator/`, `/aiagents/`.

### PMG-002 - Product architecture / navigation

Evidence:

- top menu содержит `Услуги`, дочерние пункты ведут на `/price/`, `/offer/`, `/calculator/`, `/aiagents/`;
- footer группирует эти же пункты, но не объясняет их разницу;
- `/services/` является общим разделом, а `/price/`, `/offer/`, `/calculator/` и `/aiagents/` являются самостоятельными продуктово-коммерческими входами.

Challenge:

Все пункты полезны, но сейчас пользователь видит "услуги" как список возможностей. Нужна понятная коммерческая лестница: оценка → команда/внедрение → запуск/прототип → заявка.

Acceptance criteria:

- меню и первые экраны страниц используют единую terminology;
- у каждого входа есть своя роль в funnel;
- внутренние ссылки объясняют, зачем переходить в соседний сценарий.

### PMG-003 - `/price/` value framing

Evidence:

- H1 `/price/`: "Почасовые ставки IT-специалистов по модели T&M";
- страница сильна как pricing/product tool, но верхняя рамка ставит цену выше результата.

Challenge:

Ставки нужны, но главный продукт не "часы специалистов", а управляемая команда и прогнозируемый старт работ. Иначе Tacticum конкурирует с биржами специалистов, а не с AI/IT delivery providers.

Acceptance criteria:

- hero `/price/` продает команду под задачу;
- ставки остаются как прозрачный механизм выбора;
- team preset и modal copy говорят языком результата и ответственности.

### PMG-004 - `/offer/` conversion

Evidence:

- `/offer/` содержит каталог расчетов, фильтры, карточки, пагинацию;
- primary CTA в hero: "Рассчитать свой проект";
- detail pages имеют заявку, но каталог можно сильнее связать с точным расчетом.

Challenge:

Каталог ценен для SEO и доверия. Следующий шаг должен быть очевиден: похожий расчет не является финальной сметой, зато является быстрым стартом персональной оценки.

Acceptance criteria:

- list cards и detail pages явно объясняют, что расчет примерный;
- CTA использует контекст выбранного расчета;
- формы передают offer context.

### PMG-005 - `/calculator/` promise

Evidence:

- `/calculator/` обещает оценку команды, сроков и бюджета;
- интерфейс визуально похож на AI-chat;
- benefits блок содержит proof, но не показывает итоговый output.

Challenge:

Пользователь должен понимать, что получит после диалога: диапазон бюджета, команду, сроки, риски, next step. Без этого "калькулятор" может восприниматься как обычный чат.

Acceptance criteria:

- страница показывает пример результата расчета;
- CTA после/рядом с чатом говорит, что будет в следующем шаге;
- copy не обещает точность выше, чем реально дает AI-estimation.

### PMG-006 - Proof system

Evidence:

- `120+ проектов`, `50+ AI-проектов`, `98% довольных клиентов`, `15+ лет опыта`, "гарантия результата";
- часть proof claims повторяется в CTA, `/about/`, `/calculator/`, offer detail.

Challenge:

Доверие строится не количеством цифр, а согласованностью и проверяемостью. Claims вроде "гарантия результата" требуют аккуратной формулировки, иначе создают юридический и репутационный риск.

Acceptance criteria:

- выбран единый набор proof claims;
- спорные обещания переписаны в проверяемые формулировки;
- кейсы и отзывы поддерживают claims на ключевых страницах.

### PMG-007 - `/aiagents/` product tone

Evidence:

- hero: "Создай собственного AI-бота-продавца за 60 секунд и начни продавать уже сегодня";
- CTA ведут в Telegram и демо-сценарий;
- стиль страницы заметно более промо/SaaS, чем остальной B2B-сайт.

Challenge:

Это может быть нормальным для отдельного продукта, но тогда его нужно явно изолировать и объяснить роль внутри основного сайта. Если это часть B2B-воронки, тон лучше сделать более деловым.

Acceptance criteria:

- принято решение: separate product или B2B-service entry;
- hero, CTA и form copy соответствуют выбранной роли;
- связи с `/services/`, `/offer/`, `/calculator/` не размывают основной бренд.

### PMG-008 - CTA taxonomy

Evidence:

- shared `tacticum:lead.cta` задает универсальные тексты;
- разные страницы используют близкие CTA при разных стадиях funnel.

Challenge:

CTA должен обещать конкретный следующий шаг: "получить точную смету", "подобрать команду", "обсудить внедрение", "получить прототип бота". Универсальное "оставить заявку" хуже конвертирует warm traffic.

Acceptance criteria:

- создана CTA taxonomy по страницам и стадиям;
- каждый CTA имеет form_id, purpose, expected response promise;
- тексты форм не конфликтуют между страницами.

### PMG-009 - Industry / scenario segmentation

Evidence:

- `/offer/` содержит большую базу расчетов по отраслям и сценариям;
- фильтры уже позволяют работать с sector/scenario/budget/phase;
- отдельных отраслевых landing routes или контентных блоков пока нет.

Challenge:

Это сильный SEO и product-led growth актив. Его нужно использовать как сеть посадочных под запросы: отрасль + задача + бюджетный интент.

Acceptance criteria:

- сформирована карта industry/scenario clusters;
- определено, какие кластеры получают индексируемые страницы, а какие остаются фильтрами/noindex;
- перелинковка между `/services/`, `/offer/`, `/calculator/` поддерживает clusters.

### PMG-010 - Lead qualification

Evidence:

- формы собирают контакты и сообщение;
- staff-order уже передает богатый context;
- `/offer/` и `/calculator/` могут передавать больше контекста, чем сейчас видно в общей CTA taxonomy.

Challenge:

Лид с контекстом дешевле обрабатывать и легче квалифицировать. Но поля не должны ломать конверсию. Нужны hidden context поля и мягкие optional controls там, где они уместны.

Acceptance criteria:

- формы получают source context: page, CTA type, offer code, calculator state, selected team preset where applicable;
- optional fields добавлены только там, где они помогают sales;
- analytics events не передают PII.

## Non-Goals

- Не начинать реализацию без отдельного подтверждения.
- Не менять REST/AI upstream contracts без отдельного Security / Integration scope.
- Не придумывать неподтвержденные proof metrics.
- Не создавать новые публичные индексируемые URL без SEO gate, sitemap/canonical plan и post-deploy smoke.
- Не делать визуальный редизайн ради визуального редизайна: сначала продуктовая архитектура и copy.

## Recommended Sprint

Все gaps `PMG-001` - `PMG-010` упакованы в Sprint 15: `docs/workflow/sprints/2026-05-25-sprint-15-product-marketing-architecture.md`.
