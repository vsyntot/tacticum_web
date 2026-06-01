# 03. Information Architecture TO BE

Дата: 01.06.2026

## IA Goal

Целевая информационная архитектура должна одновременно решать две задачи:

1. Объяснить Tacticum как продуктовую экосистему.
2. Сохранить понятные коммерческие входы для заявок и пилотов.

## Принципы

- Product-first, service-second: сначала продукты и платформа, затем способы внедрения.
- One ecosystem, four programs: Platform + Agents + Dev + Forum должны быть видны как линейка.
- Сервисные страницы не удаляются, а получают роль delivery/entry layer.
- Каждая продуктовая страница должна иметь собственный CTA и собственный proof.
- Новые URL должны быть управляемы через Bitrix sitemap/SEO workflow.

## Recommended Top Navigation

Вариант для первого TO BE релиза:

```text
Продукты
  - Tacticum Platform
  - Tacticum Agents
  - Tacticum Dev
  - Tacticum Forum

Внедрение
  - Рассчитать проект
  - AI-интеграция
  - Команда под проект
  - Пилот AI-решения

Кейсы
Процесс
FAQ
Контакты

Primary CTA: Обсудить пилот
Secondary CTA: Рассчитать проект
```

Если меню нужно короче, `Процесс` можно оставить секцией главной, а не пунктом header.

## Target URL Map

```text
/
  Главная экосистемы и маршрутизатор входов

/platform/
  Tacticum Platform

/agents/
  Tacticum Agents

/dev/
  Tacticum Dev

/forum/
  Tacticum Forum

/services/
  Delivery layer: внедрение, интеграции, AI-проекты

/calculator/
  Быстрый расчет / первичная квалификация задачи

/offer/
  Персональные расчеты и коммерческие предложения

/price/
  Команда под проект / staff augmentation

/aiagents/
  Legacy/current AI agents page: либо миграция в /agents/, либо redirect/alias

/cases/
  Product-specific cases, если будет выделено в отдельный раздел

/about/
/contacts/
/policies/
```

## Migration Notes

### `/aiagents/`

Текущий URL уже существует и индексируется как money page. Есть два варианта:

1. Сохранить `/aiagents/` как SEO/legacy URL, но визуально и содержательно привести к `Tacticum Agents`.
2. Создать `/agents/` как canonical product URL, а `/aiagents/` использовать как redirect или compatibility landing.

Рекомендация: для первого этапа безопаснее сохранить `/aiagents/` и добавить `/agents/` только после SEO/redirect решения.

### `/services/`

Не должен быть главным контейнером всех продуктов. Его роль: объяснить внедрение, интеграции, разработку, сопровождение и путь от пилота до production.

### `/price/`

Оставить как “команда под проект” и staff augmentation. Не смешивать с лицензиями Platform/Agents/Dev/Forum, если нет утвержденной pricing model.

### `/calculator/` и `/offer/`

Оставить как conversion layer. В TO BE они должны получать product context: Platform/Agents/Dev/Forum/services.

## Homepage Structure TO BE

Рекомендуемая структура главной:

1. Hero: Tacticum как российская AI software ecosystem.
2. Ecosystem map: Platform core + Agents/Dev/Forum.
3. Product cards: кому подходит каждый продукт.
4. Platform explanation: почему единое ядро важно.
5. Entry paths: пилот, расчет, внедрение, команда.
6. Proof: кейсы, метрики, архитектура, security.
7. Process: discovery -> pilot -> integration -> rollout.
8. CTA: обсудить пилот / получить оценку.
9. FAQ: закупка, безопасность, данные, on-prem, LLM, сроки.

## Product Page Standard Structure

Каждая продуктовая страница должна иметь:

1. Hero with product promise.
2. ICP: для кого продукт.
3. Problem: что ломается без продукта.
4. Solution: как продукт решает.
5. Architecture: как продукт использует Platform.
6. Key modules.
7. Use cases.
8. Deployment/security.
9. Proof and metrics.
10. Rollout plan.
11. FAQ.
12. Product-specific CTA.

## User Journeys

### CIO / Digital Transformation

```text
Главная -> Platform -> security/deployment -> cases/proof -> обсудить пилот
```

### Head of Support / CX

```text
Главная -> Forum -> metrics/A-B/journal -> integration channels -> pilot CTA
```

### Head of Engineering / CTO

```text
Главная -> Dev -> quality gates/profiles -> pilot metrics -> workshop CTA
```

### HR / Legal / Finance Function Owner

```text
Главная -> Agents -> business-function templates -> RAG/integration -> demo/pilot CTA
```

### Procurement / Security

```text
Product page -> deployment/security/regulatory block -> documentation request CTA
```

## SEO Clusters

Потенциальные кластеры:

- российская AI платформа;
- on-prem LLM платформа;
- корпоративные AI-агенты;
- мультиагентные ассистенты;
- AI для контакт-центра;
- сценарные чат-боты с LLM;
- AI в разработке ПО;
- agentic SDLC;
- AI платформа для КИИ;
- RAG платформа для предприятия.

SEO-кластеры нужно валидировать отдельно перед созданием текстов.

## Open IA Questions

- Делать ли `/platform/`, `/agents/`, `/dev/`, `/forum/` сразу публичными URL или сначала собрать одну ecosystem landing?
- Что выбрать canonical для Agents: `/agents/` или существующий `/aiagents/`?
- Нужен ли отдельный `/products/` catalog page?
- Должны ли product pages быть статическими page entries или Bitrix content-backed сущностями?
- Сколько proof можно показывать публично без NDA?

