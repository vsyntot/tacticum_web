# Lead Form Contract

Дата фиксации: 20.05.2026

Документ описывает текущий контракт публичных лид-форм, которые обслуживаются `local/templates/tacticum/js/forms-runtime.js` и отправляются через `local/templates/tacticum/js/forms.js`. По умолчанию формы идут в `/local/rest/tacticum_form.php`; доменные сценарии могут задать свой backend через `data-endpoint`. Это рабочий контракт для smoke-check, QA и будущих правок форм.

## Endpoint

- Default URL: `/local/rest/tacticum_form.php`
- Override: атрибут формы `data-endpoint="/local/rest/..."`, только относительный путь от корня сайта.
- Method: `POST`
- Content-Type: `application/json; charset=UTF-8`
- Response: JSON
- Security bootstrap: `tacticum_rest_validate_origin()` -> `tacticum_rest_rate_limit_by_class('PUBLIC_LEAD_POST', 'tacticum_form')` -> `tacticum_rest_require_method('POST')` -> parse JSON -> `tacticum_rest_check_csrf($data, true)` -> validation -> upstream request.
- Production upstream должен использовать только HTTPS через `tacticum_rest_get_required_https_ai_url('AI_SERVICE_BASE_URL')`.

## Обязательные Поля

| Поле | Тип | Источник | Правило |
|---|---|---|---|
| `name` | string | input `name` | Обязательно, не длиннее 200 символов |
| `email` | string | input `email` | Обязательно, валидный email |
| `phone` | string | input `phone` | Обязательно, должен проходить нормализацию телефона |
| `message` | string | textarea/input `message` | Обязательно, не длиннее 2000 символов |
| `page_url` | string | `forms.js`, `window.location.href` | Обязательно, не длиннее 1000 символов |
| `sessid` | string | `BX.bitrix_sessid()` | Целевое правило для публичных форм; при отсутствии допускается только разрешённый browser source |

`message` на backend также может быть получен из `task`, `description` или `project`, но новые формы должны отправлять именно `message`.

## Дополнительные Поля

| Поле | Тип | Когда используется |
|---|---|---|
| `company` | string | Опционально, не длиннее 200 символов |
| `form_id` | string | Идентификатор формы для аналитики, маршрутизации и QA |
| `group_id` | string | Контекст AI offer/prefill, не длиннее 64 символов |
| `lead_entry` | enum/string | Safe source context: `home`, `services`, `price`, `calculator`, `offer-detail`, `aiagents`, `contacts`, etc. |
| `lead_page_role` | enum/string | Роль страницы в funnel: router, implementation-entry, team-entry, estimate-entry, etc. |
| `lead_intent` | enum/string | Неперсональный intent пользователя, например `build-managed-team` |
| `lead_cta` | enum/string | CTA source, обычно совпадает с `form_id` |
| `lead_next_step` | enum/string | Ожидаемый следующий шаг sales/PM |
| `lead_product` | enum/string | Опциональный продуктовый вход, если нужен |
| `lead_scenario` | enum/string | Сценарий/тип задачи, не пользовательский free text |
| `lead_industry` | enum/string | Отрасль/sector из offer catalog или контролируемого списка |
| `lead_budget` | enum/string | Мягкая квалификация бюджета: `up-to-1m`, `1-3m`, `3-7m`, `7m-plus` или safe label из offer |
| `lead_timeline` | enum/string | Мягкая квалификация срока: `asap`, `1-2-months`, `3-6-months`, `6-plus-months` или safe label из offer |
| `lead_offer_code` | string | Код offer detail, если форма отправляется с `/offer/<code>/` |
| `lead_offer_title` | string | Заголовок offer example, если форма отправляется с detail page |
| `specialist` | string | Заказ специалиста через `tacticum_sale_staff.php` |
| `rate` | string | Заказ специалиста через `tacticum_sale_staff.php` |
| `duration` | string | Заказ специалиста через `tacticum_sale_staff.php` |
| `startDate` / `start_date` | string | Заказ специалиста через `tacticum_sale_staff.php` |
| `endDate` / `end_date` | string | Обязательно только если `duration=exact-date`; точная дата окончания работ |
| `level` | string | Заказ специалиста через `tacticum_sale_staff.php` |
| `amount_of_workers` / `amount` | integer | Опциональное количество специалистов; по умолчанию `1` |
| `workers_json` | JSON string | Основной frontend payload для заказа нескольких специалистов; массив объектов `role`, `level`, `cost_per_hour`, `amount_of_workers` |
| `workers` | array | Backend-compatible structured payload для API clients; тот же состав, что `workers_json` |
| `workload` | string | Формат загрузки: `flexible`, `part-time`, `full-time` |
| `team_preset` | string | Опциональный пресет команды на `/price/`: `mvp`, `discovery`, `support`, `qa-burst` |
| `monthly_budget_estimate` | string/number | Ориентировочный месячный бюджет, рассчитанный на frontend по ставке, количеству и загрузке |

Обычные CTA формы могут содержать optional qualification controls `lead_budget`, `lead_timeline` и controlled scenario select `lead_scenario`. Они не обязательны и не должны называться `budget`, `timeline`, `duration`, `rate` или `specialist`, чтобы не конфликтовать с legacy/staff-order веткой. `/local/rest/tacticum_form.php` allowlist-ит `lead_*` context, нормализует его в canonical lead qualification profile and добавляет человекочитаемый fallback внутрь существующего upstream поля `task`. Response shape и upstream endpoint path не меняются.

## Canonical Lead Qualification Profile

Backend строит внутренний canonical profile через `Tacticum\Rest\LeadPayload` / `LeadContext`. На текущем этапе профиль не отправляется отдельными upstream JSON fields, потому что внешний sale/CRM contract не подтвержден. Он используется как нормализованный источник для блока `Контекст заявки` внутри `task`.

| Canonical field | Source field(s) | Meaning |
|---|---|---|
| `product_interest` | `lead_product` | Product line or ecosystem interest |
| `use_case_interest` | `lead_scenario` | Controlled scenario / use case slug |
| `deployment_interest` | `lead_next_step` | Expected next step or deployment/procurement path |
| `funnel_entry` | `lead_entry` | Entry page/context |
| `funnel_stage` | `lead_page_role` | Funnel role of the page |
| `lead_intent` | `lead_intent` | Non-PII intent hint |
| `cta_id` | `lead_cta`, fallback `form_id` | CTA/source identifier |
| `budget_band` | `lead_budget` | Controlled budget band |
| `timeline_band` | `lead_timeline` | Controlled timeline band |
| `industry` | `lead_industry` | Controlled industry/sector hint |
| `offer_code` | `lead_offer_code` | Offer example code |
| `offer_title` | `lead_offer_title` | Offer example title |

Migration rule: до подтверждения CRM/upstream support нельзя отправлять `product_interest`, `use_case_interest`, `deployment_interest` or other canonical fields as top-level upstream JSON fields. Они остаются approved text fallback in `task`. Если upstream/CRM готов принимать structured fields, это новая Security / Integration задача с обновлением этого документа, smoke cases and release evidence.

`tacticum:lead.cta` поддерживает scenario select через параметр `SCENARIO_OPTIONS`. Значения должны быть короткими controlled slugs без PII/free text; подписи видны пользователю, но analytics events продолжают отправлять только form-level metadata. Product pages `/platform/`, `/agents/`, `/dev/`, `/forum/` используют этот механизм для уточнения ближайшего следующего шага без изменения REST/upstream contract.

Backend `Tacticum\Rest\LeadContext` сначала переводит входные `lead_*` fields в canonical profile, затем переводит известные `lead_scenario`, budget and timeline slugs в человекочитаемые подписи перед добавлением блока `Контекст заявки` в upstream `task`. Unknown slugs не блокируются, но попадают в контекст как короткая строка после общей нормализации.

Light chat handoff на `/calculator/` и `/price/` использует существующий `group_id` / prefill contract без новых upstream fields. После успешного AI-ответа пользователь может передать вводные в CTA: frontend пробует `POST /local/rest/tacticum_prefill.php` с `group_id + sessid`, заполняет только целевую CTA форму внутри `#contact-form`, сохраняет `group_id` в `form.dataset.tacticumOfferGroupId` и не пишет текст сообщения в analytics params. `forms.js` добавляет scoped `group_id` только для этой формы; глобальный `window.tacticum_offer_context` остаётся compatibility path для hero chat и не применяется к формам без `lead_*` context.

Форма `price-specialist` на `/price/` должна использовать `data-endpoint="/local/rest/tacticum_sale_staff.php"`. Этот endpoint сохраняет доменную модель заказа сотрудников (`workers[]`, `start_date`, `end_date`, `worker_timeline`, `workload`, `cost_per_hour`, `amount_of_workers`, `team_preset`, `monthly_budget_estimate`) и временно адаптирует её в `HotSaleRequestDTO` для `/tacticum/v1/chat_agent/sale`. Отдельный `/tacticum/v1/sale/workers` в актуальном OpenAPI отсутствует.

AI sale path берётся из config `ai.endpoint_paths.*`: обычные лиды используют `chat_agent_sale`, staff-order использует `staff_sale`. Если ключ отсутствует, backend сохраняет default `/tacticum/v1/chat_agent/sale`. Это позволяет переключить `/price/` на будущий rich workers upstream без изменения frontend payload.

## Sale Alias Lifecycle And Staff Upstream

`tacticum_offer.php` и `tacticum_sale.php` являются legacy aliases. Successor endpoint для новых интеграций - `/local/rest/tacticum_form.php`; aliases должны отдавать `Deprecation`, `Sunset` с target date `30.09.2026` и `Link: rel="successor-version"` до финального решения. Sprint 09 фиксирует обязательную матрицу действий: inventory consumers до `30.06.2026`, миграция до `31.08.2026`, финальное решение до `30.09.2026`.

Для `/price/` staff-order единственный поддерживаемый путь переключения на будущий rich workers upstream - изменение server config `ai.endpoint_paths.staff_sale`. До появления совместимого upstream contract значение остаётся `/tacticum/v1/chat_agent/sale`; если новый contract требует другой payload или response shape, это новая Security / Integration задача с обновлением ADR-006 и этого contract.

## Form ID Taxonomy

`form_id` задаётся в HTML через `data-form-id`, а `forms.js` переносит его в JSON payload. Значения должны быть стабильными и человекочитаемыми.

Текущие значения:

| `form_id` | Где используется |
|---|---|
| `home-cta` | Главная CTA форма на `/` |
| `about-cta` | CTA форма на `/about/` |
| `services-cta` | CTA форма на `/services/` |
| `calculator-cta` | CTA форма на `/calculator/` |
| `price-cta` | CTA форма на `/price/` |
| `price-specialist` | Модалка заказа специалиста на `/price/` |
| `contacts-cta` | CTA форма на `/contacts/` |
| `offer-cta` | CTA форма на `/offer/` / detail offer template |
| `aiagents-inline` | Inline форма на `/aiagents/` |
| `contact-modal` | Общая модальная форма из footer |

Правило для новых форм: `page-or-context-purpose`, например `contacts-cta` или `service-detail-cta`. Не переиспользовать один `form_id` для разных пользовательских сценариев.

## Sprint 15 CTA Taxonomy

| Page / context | `form_id` | Primary promise | Context |
|---|---|---|---|
| `/` | `home-cta` | Получить следующий шаг после выбора product или commercial entry | `lead_entry=home`, `lead_page_role=ecosystem-router`, `lead_product=ecosystem`, `lead_scenario=product-routing` |
| `/about/` | `about-cta` | Обсудить задачу и fit с командой Tacticum | `lead_entry=about`, `lead_page_role=trust-entry`, `lead_product=ecosystem` |
| `/services/` | `services-cta` | Обсудить внедрение AI-решения или product-delivery пилот | `lead_entry=services`, `lead_page_role=implementation-entry`, `lead_product=ecosystem`, `lead_scenario=product-delivery` |
| `/price/` | `price-cta` | Подобрать команду под задачу или product workstream | `lead_entry=price`, `lead_page_role=team-entry`, `lead_product=ecosystem`, `lead_scenario=product-team` |
| `/calculator/` | `calculator-cta` | Уточнить предварительную оценку по product-aware задаче | `lead_entry=calculator`, `lead_page_role=estimate-entry`, `lead_product=ecosystem`, `lead_scenario=product-estimate` |
| `/contacts/` | `contacts-cta` | Направить обращение к продукту, внедрению, оценке или команде | `lead_entry=contacts`, `lead_page_role=contact-entry`, `lead_product=ecosystem`, `lead_scenario=contact-routing` |
| `/offer/<code>/` | `offer-cta` | Получить персональную оценку по похожей задаче | `lead_entry=offer-detail`, `lead_product=ecosystem`, `lead_offer_code`, `lead_offer_title` |
| `/aiagents/` | `aiagents-inline` | Запросить бот-прототип как первый Agents-сценарий | `lead_entry=aiagents`, `lead_page_role=telegram-bot-entry`, `lead_product=agents` |
| `/platform/` | `platform-cta` | Обсудить платформенный assessment или пилот | `lead_entry=platform`, `lead_page_role=product-page`, `lead_product=platform` |
| `/agents/` | `agents-cta` | Выбрать бизнес-сценарий для Agents-пилота | `lead_entry=agents`, `lead_page_role=product-page`, `lead_product=agents` |
| `/dev/` | `dev-cta` | Оценить готовность команды к AI-assisted workflow | `lead_entry=dev`, `lead_page_role=product-page`, `lead_product=dev` |
| `/forum/` | `forum-cta` | Разобрать поток клиентских обращений | `lead_entry=forum`, `lead_page_role=product-page`, `lead_product=forum` |

Product page CTAs дополнительно показывают optional `lead_scenario` select. Текущие controlled values:

| Page | `lead_scenario` values |
|---|---|
| `/platform/` | `platform-assessment`, `platform-pilot`, `deployment-readiness` |
| `/agents/` | `agent-scenario-selection`, `rag-documents-check`, `pilot-rollout` |
| `/dev/` | `ai-workflow-assessment`, `quality-gates-pilot`, `design-system-guardrails` |
| `/forum/` | `dialog-flow-assessment`, `scenario-llm-pilot`, `support-analytics-review` |

## Sprint 19 CJM / CRM Qualification Decision

04.06.2026 Sprint 19 decision baseline зафиксирован в `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md`.

Contract decision:

- current `lead_*` profile remains approved v1 text fallback inside upstream `task`;
- top-level structured CRM/upstream fields remain blocked until Sales/upstream/Security approval;
- role/stage CTA taxonomy may use existing `lead_product`, `lead_page_role`, `lead_scenario`, `lead_cta`, `lead_next_step` only as controlled context;
- returning-lead, procurement/security and private proof/document flows must not add hidden fields or new endpoints before separate Security / Integration scope;
- any structured field migration must update this contract, smoke cases and release evidence.

## Consent И CSRF

- Каждая публичная форма должна иметь чекбокс с `data-tacticum-consent` и ссылкой на `/policies/`.
- Клиентская валидация блокирует отправку, если consent не отмечен.
- `forms.js` добавляет `sessid`, если доступен `BX.bitrix_sessid()`.
- State-changing POST должен передавать явный `sessid`, когда он доступен на странице.
- Backend не принимает запрос только по факту наличия Bitrix session cookie без явного token; fallback возможен только после `validate_origin()` и проверки разрешённого `Origin` / `Referer`.

## JSON Request

Минимальный payload:

```json
{
  "name": "Иван Иванов",
  "email": "ivan@example.com",
  "phone": "+7 999 123-45-67",
  "message": "Нужна консультация по внедрению AI.",
  "page_url": "https://tacticum.ru/calculator/",
  "form_id": "calculator-cta",
  "lead_entry": "calculator",
  "lead_page_role": "estimate-entry",
  "lead_intent": "clarify-budget-timeline-team",
  "lead_budget": "3-7m",
  "lead_timeline": "3-6-months",
  "sessid": "bitrix-session-token"
}
```

## JSON Response

Успешный ответ обычной формы:

```json
{
  "success": true,
  "error": null,
  "code": "ok"
}
```

Backend treats any upstream `2xx` response from `/tacticum/v1/chat_agent/sale` as accepted, including an empty upstream body. The default lead endpoint, `tacticum_offer.php` and `tacticum_sale.php` use shared `tacticum_rest_submit_chat_agent_sale(...)` for the upstream call and retry policy. `tacticum_offer.php` and `tacticum_sale.php` are legacy aliases: they preserve response shape, but return `Deprecation`, `Sunset` target `30.09.2026` and `Link: rel="successor-version"` headers pointing to `/local/rest/tacticum_form.php`. If upstream rejects a sale payload that contains `group_id`, backend retries the same lead once without `group_id`; this keeps the manual contact request deliverable when AI chat context is stale or malformed upstream. Non-2xx after retry remains `502 upstream_error`.

For default lead forms, allowlisted `lead_*` fields are normalized into the canonical lead profile and appended to upstream `task` as a short `Контекст заявки` block. Unknown request fields are not forwarded. Contact data and free-form `message` remain subject to existing validation and masking/logging rules.

`tacticum_sale_staff.php` возвращает тот же формат успешного ответа. Если передан `workers_json` / `workers`, endpoint валидирует до 20 позиций и суммарно до 100 специалистов; legacy-поля `specialist`, `level`, `rate`, `amount_of_workers` остаются fallback для одиночного заказа. Детали состава команды, выбранный пресет и ориентировочный месячный бюджет передаются upstream внутри `task`; файловое runtime-логирование payload/response в кастомном коде отключено.

## Error Model

Ошибки возвращаются JSON-объектом с `success: false`, `code` и `message`. Для ошибок из `tacticum_form_response()` поле называется `error`, но штатные ошибки endpoint сейчас идут через `tacticum_rest_error()` и используют `message`.

Типовые коды:

| HTTP | `code` | Когда |
|---:|---|---|
| 400 | `invalid_json` | Тело запроса не JSON-объект |
| 400 | `validation_error` | Не заполнены или невалидны обязательные поля |
| 403 | `invalid_origin` | Origin/host не разрешён |
| 403 | `invalid_csrf` | Нет валидного CSRF token и запрос не прошёл разрешённый browser-source fallback |
| 429 | `rate_limited` | Сработал rate limit |
| 500 | `config_error` | Не настроен HTTPS upstream |
| 502 | `curl_error` | Ошибка соединения с внешним сервисом |
| 502 | `upstream_error` | Внешний сервис не вернул успешный ответ |

Frontend не должен показывать пользователю raw upstream response, stack trace или PII. Для пользователя достаточно нейтрального сообщения об ошибке и возможности повторить отправку.

## Smoke Cases

- Валидная отправка каждой формы из списка `form_id`: получаем `success: true`, пользователь видит success state.
- Пустые `name`, `email`, `phone`, `message`: клиентская валидация блокирует отправку.
- Невалидный email: клиентская или backend-валидация возвращает ошибку без отправки в upstream.
- Неотмеченный consent: клиентская валидация блокирует отправку.
- Отсутствующий `sessid` с разрешённым `Origin` / `Referer`: endpoint проходит browser-source fallback.
- Отсутствующий или неправильный `sessid` без разрешённого browser source: endpoint возвращает `403 invalid_csrf`.
- Невалидный JSON: endpoint возвращает `400 invalid_json`.
- Слишком длинные `name`, `company`, `message`, `page_url`, `group_id`: endpoint возвращает `400 validation_error`.
- Optional `lead_budget` / `lead_timeline` на shared CTA: форма отправляется без обязательности этих полей; backend добавляет человекочитаемый context в `task`, если значения выбраны.
- Product lead qualification profile: `lead_product`, `lead_scenario`, `lead_next_step`, `lead_entry`, `lead_page_role`, `lead_cta`, `lead_budget`, `lead_timeline` нормализуются в canonical profile, но top-level `product_interest` / `use_case_interest` / `deployment_interest` не отправляются upstream до отдельного CRM/upstream approval.
- Offer detail context: `offer-cta` отправляет `lead_offer_code`, `lead_offer_title`, safe industry/scenario/budget/timeline context; analytics events не содержат эти values.
- Calculator/price light chat handoff: после AI-ответа кнопка handoff заполняет CTA message, скроллит к `#contact-form`, сохраняет scoped `group_id` на форме и отправляет analytics `tacticum_chat_lead_handoff` только с boolean flags.
- Prefilled chat form with a valid lead payload but upstream failure on `group_id`: endpoint retries without `group_id` and returns success if the plain lead is accepted.
- Specialist order с `/price/`: форма отправляется в `/local/rest/tacticum_sale_staff.php`, `workers[]` валидируется backend-ом, adapter отправляет заявку в config-driven `ai.endpoint_paths.staff_sale`, файловое runtime-логирование payload/response отключено.
- Multi-staff order с `/price/`: пользователь добавляет несколько ролей/уровней, меняет количество, frontend отправляет `workers_json`, backend формирует `workers[]` и текстовое резюме команды в `task`.
- Exact deadline order с `/price/`: при выборе `duration=exact-date` frontend раскрывает календарь `endDate`, блокирует отправку без даты, backend также возвращает `validation_error`, если дата не передана.
- Team preset order с `/price/`: пользователь выбирает `MVP`, `Discovery`, `Support` или `QA burst`, frontend подбирает доступные роли из текущих карточек, показывает persistent summary, отправляет `team_preset` и `workers_json`.
- Monthly budget estimate с `/price/`: при выбранной загрузке `part-time` или `full-time` frontend считает ориентировочный бюджет, отправляет `monthly_budget_estimate`, backend добавляет его в `task`.
- Upstream недоступен: endpoint возвращает `502`, пользователь видит общий error state без технических деталей.
