# 01. Target Product Vision

Дата: 01.06.2026

## One-Liner

Tacticum TO BE - российская экосистема AI-программ для enterprise и регулируемых организаций: общее платформенное ядро `Tacticum Platform` и три прикладных продукта `Tacticum Agents`, `Tacticum Dev`, `Tacticum Forum`.

## Главный Сдвиг

AS IS сайт продает четыре коммерческих входа:

- рассчитать проект;
- внедрить AI-решение;
- собрать команду;
- запустить AI-бота.

TO BE должен продавать более зрелую категорию:

- Tacticum как вендор AI-платформы и прикладных программ;
- услуги и проектное внедрение как способ купить, запустить и адаптировать продукты;
- пилот как низкорисковый вход в продуктовую экосистему.

Иными словами, услуги не исчезают. Они становятся упаковкой вокруг программных продуктов, а не главным смыслом компании.

## Целевая Категория

`decision`: Tacticum должен позиционироваться как российский AI-software vendor с сильной внедренческой экспертизой.

Не как:

- обычный AI-интегратор;
- заказная разработка на LLM API;
- агентство по чат-ботам;
- outstaffing-компания;
- обертка над одним зарубежным LLM-провайдером;
- набор несвязанных AI-сервисов.

А как:

- единая AI-инфраструктура;
- прикладные продукты поверх этой инфраструктуры;
- on-prem / sovereign LLM / enterprise governance;
- поставка через SaaS, on-prem, ПАК, пилот и внедрение.

## Продуктовая Архитектура

```text
Tacticum
  -> Tacticum Platform
       -> Agent Runtime
       -> MCP Runtime
       -> Workflow Spec Engine
       -> LLM Gateway
       -> Prompt Management
       -> Guardrails
       -> Knowledge / RAG
       -> Memory
       -> Identity / RBAC / Tenancy
       -> Connectors / Event Bus
       -> Observability / Cost

  -> Tacticum Agents
       -> business-function multi-agent assistants

  -> Tacticum Dev
       -> agentic SDLC governance and engineering profiles

  -> Tacticum Forum
       -> scenario + LLM dialogue platform for customer communications
```

Архитектурный принцип: прикладные продукты не реализуют собственные движки. Они описывают свою доменную логику через декларативные манифесты, а исполнение берет на себя Platform.

## Роли Продуктов

### Tacticum Platform

Роль: инфраструктурное ядро и самостоятельный enterprise-продукт для организаций, у которых уже есть несколько AI-приложений или требование к on-prem/sovereign deployment.

Главная ценность:

- единый LLM gateway;
- единая стоимость и аудит;
- единая модель tenancy/RBAC;
- единые MCP/RAG/memory/guardrails;
- меньше дублирования между AI-приложениями;
- повторное использование движков для Agents, Dev, Forum.

### Tacticum Agents

Роль: прикладной продукт для автоматизации бизнес-функций через мультиагентных ассистентов.

Основные домены:

- HR;
- юридический контур;
- бухгалтерия;
- клиентская поддержка;
- корпоративная база знаний;
- IT helpdesk.

Ключевой тезис: это не конструктор ботов, а управляемая корпоративная среда ассистентов поверх Platform.

### Tacticum Dev

Роль: продукт для управления AI-assisted / agentic разработкой в инженерных организациях.

Основные блоки:

- профили разработки;
- RE Knowledge Layer;
- Design Token Layer;
- Feature Lifecycle Workflow;
- Quality Gates;
- Stack-Specific MCP Bundles;
- Multi-CLI Adapter.

Публичный framing должен быть осторожным: фокус на управляемом росте производительности, traceability, architecture compliance и design compliance. Тезисы про сокращение персонала лучше оставить для закрытых стратегических материалов, если они вообще нужны.

### Tacticum Forum

Роль: импортонезависимая платформа для клиентских коммуникаций, объединяющая сценарные графы и LLM.

Ключевой тезис: не чистый LLM-бот и не жесткое дерево сценариев, а управляемые сценарии с LLM-обогащением, A/B-тестами, аналитикой и журналируемостью.

## Целевые Покупатели

### Economic buyer

- CIO;
- CTO;
- CDO;
- руководитель цифровой трансформации;
- руководитель контакт-центра/CX;
- руководитель разработки;
- функциональный директор HR/legal/finance/support.

### Technical buyer

- enterprise architect;
- AI/ML platform owner;
- руководитель инфраструктуры;
- security/compliance;
- DevOps/MLOps lead;
- архитектор интеграций.

### Influencers

- product owners внутренних систем;
- руководители команд разработки;
- владельцы процессов;
- procurement/legal;
- служба информационной безопасности.

## Коммерческая Упаковка

`decision`: сайт должен показывать продуктовую линейку, но сохранять понятный вход через пилот/расчет.

Целевые форматы:

- discovery / assessment;
- фиксированный пилот;
- SaaS;
- on-prem license;
- ПАК;
- внедрение и интеграция;
- support/SLA;
- strategic partnership для крупных заказчиков.

## Narrative Hierarchy

1. Tacticum - российская экосистема AI-программ для enterprise.
2. Platform - общее ядро: LLM, RAG, MCP, runtime, security, audit.
3. Agents, Dev, Forum - прикладные продукты для бизнес-функций, разработки и клиентских коммуникаций.
4. Пилот и внедрение - безопасный путь от гипотезы до production.
5. Proof - кейсы, метрики, архитектура, регуляторная готовность, безопасность.

## Что Должно Быть Видно На Первом Экране

Главная не должна начинаться с абстрактного “внедрим AI под ключ”, если цель - продуктовая экосистема.

Целевой первый экран должен быстро отвечать:

- кто мы: Tacticum;
- что это: российская экосистема AI-программ;
- из чего состоит: Platform + Agents + Dev + Forum;
- для кого: enterprise, регулируемые отрасли, крупные команды;
- как начать: пилот / расчет / архитектурная консультация.

## Proof Model

Для каждого продукта нужен свой proof:

- Platform: архитектура, deployment model, безопасность, поддерживаемые контуры, демонстрация reuse между продуктами;
- Agents: сценарии, business-function templates, снижение нагрузки, RAG/интеграции;
- Dev: lead time, quality gates, design token compliance, architecture compliance;
- Forum: automation rate, FCR, drop-off, cost per contact, A/B tests, journal.

Все публичные численные claim'ы должны иметь источник: реальный кейс, benchmark, пилот или явно маркированную оценку.

## Success Metrics Для TO BE Сайта

Продуктовые:

- посетитель понимает линейку Platform/Agents/Dev/Forum без звонка;
- заявки сегментируются по продукту и формату покупки;
- sales получает контекст: продукт, контур, отрасль, сроки, зрелость данных;
- дизайнер и разработка имеют стабильные page templates.

Маркетинговые:

- рост конверсии product pages -> qualified lead;
- рост доли enterprise/discovery заявок;
- снижение нецелевых заявок на “просто чат-бот”;
- улучшение SEO по продуктовым и регуляторным кластерам.

Технические:

- новые страницы используют Bitrix assets/components;
- формы сохраняют `data-tacticum-form` contracts;
- новые URL добавлены в SEO/sitemap governance;
- risky claims вынесены в управляемый реестр.

