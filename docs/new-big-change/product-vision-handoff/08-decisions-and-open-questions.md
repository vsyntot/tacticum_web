# 08. Decisions And Open Questions

Дата: 01.06.2026

## Working Decisions

Эти решения можно считать рабочими для дальнейшего проектирования, пока владелец продукта не изменит их явно.

| ID | Decision | Rationale |
|---|---|---|
| D-001 | Tacticum TO BE позиционируется как AI software ecosystem | Это общий смысл `tacticum.md` и остальных product docs |
| D-002 | Platform является ядром и самостоятельным продуктом | Без Platform история Agents/Dev/Forum выглядит как набор несвязанных решений |
| D-003 | Agents, Dev, Forum должны иметь отдельные page briefs | Каждый продукт имеет свою аудиторию, pain, proof и CTA |
| D-004 | Услуги остаются, но становятся delivery layer | Текущий сайт уже конвертирует через сервисные входы; их не нужно ломать |
| D-005 | Claim governance обязателен до публичного редизайна | В материалах много сильных юридических/метрических утверждений |
| D-006 | Tacticum Dev требует отдельной редакционной осторожности | Workforce тезисы рискованны для публичного сайта |
| D-007 | Existing form/JS contracts нужно сохранять или мигрировать явно | AS IS сайт уже стабилизирован через data/id contracts |
| D-008 | Первый релиз должен быть ограниченным | Лучше выпустить ecosystem + product shell, чем пытаться сразу покрыть все отрасли |

## Open Product Questions

| ID | Question | Why It Matters | Suggested Owner |
|---|---|---|---|
| Q-001 | Какой официальный статус у каждого продукта по реестру? | Публичные формулировки и закупочный proof | PM + Legal |
| Q-002 | Есть ли подтвержденная совместимость с Astra Linux и РЕД ОС? | Platform/reg-ready блок | Tech Lead + QA |
| Q-003 | Какие LLM-провайдеры реально поддерживаются в production? | Security, procurement, product copy | Architect |
| Q-004 | Какие коннекторы готовы, какие в pilot, какие roadmap? | Product pages and implementation proof | Product + Dev |
| Q-005 | Какие клиентские логотипы можно использовать публично? | Homepage proof | Sales + Legal |
| Q-006 | Какие кейсы можно раскрывать без NDA? | Case cards and product proof | Sales + PM |
| Q-007 | Что является canonical URL для Agents: `/aiagents/` или `/agents/`? | SEO and migration | PM + SEO |
| Q-008 | Делать ли `/platform/`, `/agents/`, `/dev/`, `/forum/` сразу? | Scope and sitemap | PM |
| Q-009 | Нужна ли отдельная страница `/products/`? | Navigation depth | UX + SEO |
| Q-010 | Будет ли pricing/licensing публичным? | Price page relationship | PM + Sales |
| Q-011 | Какой минимальный product-aware form payload допустим? | Lead qualification and REST contract | PM + Backend + QA |
| Q-012 | Нужно ли создавать новые инфоблоки для продуктов? | Bitrix architecture and content operations | Dev + Content |
| Q-013 | Какие Dev claims можно публиковать? | Reputation and sales tone | PM + Legal + HR |
| Q-014 | Нужен ли gated content: architecture PDF / security brief? | Enterprise lead capture | PM + Sales |
| Q-015 | Какая версия визуального направления из `index.html` принимается как основа? | Design system scope | Designer + PM |

## Decisions Needed Before Design

1. Product taxonomy and names.
2. URL strategy.
3. Public/private claims split.
4. Product page list for first release.
5. Hero message.
6. Navigation model.
7. Proof that can be shown publicly.
8. Design direction baseline.

## Decisions Needed Before Development

1. Static pages vs content-backed products.
2. New/changed form fields.
3. Component boundaries.
4. Asset loading strategy for new interactions.
5. Sitemap/canonical plan.
6. Analytics events.
7. QA smoke list.
8. ADR requirements.

## Suggested Next Workshop

Продолжительность: 90-120 минут.

Участники:

- Product owner;
- Designer;
- Tech lead;
- SEO/content;
- Sales;
- Legal/security if available.

Agenda:

1. Утвердить target positioning.
2. Пройти risk register и отметить красные claim'ы.
3. Выбрать URL strategy.
4. Утвердить first release scope.
5. Назначить владельцев evidence backlog.

