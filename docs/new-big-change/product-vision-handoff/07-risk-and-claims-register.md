# 07. Risk And Claims Register

Дата: 01.06.2026

## Назначение

Новые материалы содержат сильные продуктовые, регуляторные, технические и коммерческие утверждения. Этот документ фиксирует, что нельзя автоматически переносить на публичный сайт без evidence.

## Publication Rule

Публичный сайт может использовать claim только если у него есть:

- владелец;
- источник;
- дата актуальности;
- разрешенная формулировка;
- понимание, где claim показывается: public site, sales deck, NDA deck, internal only.

## Claim Statuses

- `allowed` - можно публиковать в текущей формулировке.
- `rewrite` - смысл можно оставить, но формулировку нужно смягчить.
- `needs evidence` - нужна проверка/документ/подтверждение.
- `private only` - оставить для закрытых материалов.
- `remove` - не использовать.

## Register

| ID | Claim / Topic | Source | Risk | Recommended Status | Public Treatment |
|---|---|---|---|---|---|
| RC-001 | “Все четыре продукта подаются в реестр” | `tacticum.md` | Юридическая/закупочная точность | needs evidence | Публиковать только подтвержденный статус: “планируется”, “готовится”, “подано”, “включено” |
| RC-002 | “Готово к ПП №1937 / доверенное ПО” | `tacticum.md`, `platform.md`, `forum.md` | Высокий регуляторный риск | needs evidence | До подтверждения заменить на “проектируется с учетом требований...” |
| RC-003 | “Совместимость с Astra Linux и РЕД ОС нативно” | `tacticum.md`, `platform.md` | Техническое доказательство | needs evidence | Нужны матрица тестов и версии ОС |
| RC-004 | “Поддерживается сертификация ФСТЭК/ФСБ” | `platform.md`, `forum.md` | Юридическая точность | needs evidence | Не публиковать без security/legal approval |
| RC-005 | Налоговые льготы: НДС 0%, прибыль 5% | `tacticum.md` | Налоговая консультация | needs evidence | Только в закрытых материалах после юриста/бухгалтера |
| RC-006 | “Правообладатель РФ без иностранного контроля” | `tacticum.md` | Корпоративная точность | needs evidence | Подтвердить corporate docs |
| RC-007 | “On-prem полностью изолированный контур” | all product docs | Техническое обещание | rewrite | “доступна on-prem поставка при согласованной архитектуре” |
| RC-008 | “Sovereign LLM” | all product docs | Термин требует определения | rewrite | Ввести glossary: российские провайдеры / open-source в контуре / foreign optional |
| RC-009 | OpenAI/Anthropic/Google optional | `platform.md`, `forum.md` | Санкции, compliance, policy | needs evidence | Публиковать только как “по политике заказчика” после legal/security review |
| RC-010 | “GPT-4o через российские прокси” | `index.html` | Высокий compliance/reputation риск | remove/private only | Не использовать на публичном сайте без отдельного решения |
| RC-011 | Customer logos: МТС, СберТех, X5, VK Cloud, Sapiens | `index.html` | Разрешение на использование брендов | needs evidence | Только с письменным approval |
| RC-012 | Named testimonials with people | `index.html` | Персональные данные/разрешения | needs evidence | Не публиковать без согласия и проверки фактов |
| RC-013 | “80+ проектов с 2018” | `index.html` | Проверяемый commercial claim | needs evidence | Нужен список/методика подсчета |
| RC-014 | “180 инженеров” | `index.html` | HR/commercial claim | needs evidence | Уточнить: штат, пул, партнеры, доступность |
| RC-015 | “-60% TCO after SAP migration” | `index.html` | Сильный performance claim | needs evidence | Только как кейс с NDA/source или “до -60% по отдельному кейсу” |
| RC-016 | “90% automation first line” | `index.html`, `agents.md` | Performance claim | needs evidence | Указать контекст: типовой поток, период, ограничение |
| RC-017 | “Lead time -70%, output x2.5” | `dev.md` | Pilot claim | needs evidence | Можно как reference pilot после подтверждения methodology |
| RC-018 | “Design token violations -100%” | `dev.md` | Требует definition of metric | needs evidence | Указать, что считается violation и где измерено |
| RC-019 | “100 FTE -> 50 FTE” | `dev.md` | Репутационный/HR/юридический риск | private only | Исключить с публичного сайта |
| RC-020 | Сравнения с Microsoft Copilot, Google Duet, Cursor | source docs | Риск некорректного сравнения | rewrite | Сравнивать категории, а не делать спорные claims о конкретных брендах |
| RC-021 | Gartner/McKinsey/WEF/Forrester references | source docs | Требуются точные источники и права цитирования | needs evidence | Использовать только с точной ссылкой и коротким paraphrase |
| RC-022 | “ФЗ-152 / ФЗ-187 compatible” | source docs | Юридическая точность | rewrite | “архитектура поддерживает сценарии размещения с учетом требований...” |
| RC-023 | “Код и модели принадлежат клиенту” | `index.html` | Зависит от договора | rewrite | “условия передачи фиксируются в договоре” |
| RC-024 | “SLA Bronze/Silver/Gold” | `index.html` | Commercial promise | needs evidence | Публиковать только после утверждения тарифов SLA |
| RC-025 | “Готовые коннекторы Salesforce/HubSpot/Dynamics” | `index.html` | Техническая готовность/импортозависимость | needs evidence | Указать только подтвержденные коннекторы |
| RC-026 | “MAX / VK Teams / SIP готово” | `forum.md`, `index.html` | Техническая готовность | needs evidence | Разделить statuses: ready / pilot / roadmap |
| RC-027 | “ПАК поддерживается” | `platform.md`, `tacticum.md` | Delivery/legal/ops claim | needs evidence | Требует packaging spec |

## Public Copy Safer Patterns

Вместо абсолютных утверждений:

- “проектируется с учетом”;
- “поддерживает сценарии”;
- “может быть развернуто”;
- “при согласованном контуре”;
- “по результатам пилота”;
- “на отдельных потоках”;
- “по benchmark”;
- “после подтверждения данных заказчика”.

## Claims That Should Be Kept Private Initially

- workforce reduction;
- equity partnership details;
- неподтвержденные registry statuses;
- неподтвержденные named customer results;
- details of proxies/foreign model access;
- legal/tax advantages without formal review.

## Implementation Notes

- 01.06.2026: публичный `/about/` очищен от неподтвержденного partner/status блока с vendor logo-style presentation. Вместо него используется безопасный technology-contours block без claims о партнерствах, сертификациях или статусах.
- 01.06.2026: первый product-layer MVP использует safe wording: без registry/trusted software/FSTEC/FSB/customer-logo/performance-percentage/workforce-reduction claims.
- 01.06.2026: product CTA scenario qualification использует только controlled slugs (`lead_scenario`) и пользовательские labels без метрик, customer logos, regulatory statuses или free-text claims.
- 01.06.2026: product rollout/delivery blocks описывают только discovery/pilot/integration/rollout decision и не публикуют registry, ПАК, certification, guaranteed SLA, pricing/licensing или automation/performance claims.
- 01.06.2026: product proof readiness blocks описывают только проверяемые пилотные артефакты и не публикуют numeric metrics, customer logos, testimonials, benchmarks, SLA or regulatory proof.
- 01.06.2026: product JSON-LD использует только минимальную `SoftwareApplication` identity schema и не добавляет `offers`, `price`, `review`, `aggregateRating` или proof/commercial claims.

## Evidence Backlog

Нужно собрать:

- product readiness matrix;
- deployment compatibility matrix;
- registry application/status evidence;
- legal wording approvals;
- customer logo permissions;
- case proof sheets;
- benchmark source links;
- SLA/tariff documents;
- connector readiness table;
- security architecture summary.
