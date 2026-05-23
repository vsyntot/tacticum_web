# Lead Form Contract

Дата фиксации: 20.05.2026

Документ описывает текущий контракт публичных лид-форм, которые отправляются через `local/templates/tacticum/js/forms.js`. По умолчанию формы идут в `/local/rest/tacticum_form.php`; доменные сценарии могут задать свой backend через `data-endpoint`. Это рабочий контракт для smoke-check, QA и будущих правок форм.

## Endpoint

- Default URL: `/local/rest/tacticum_form.php`
- Override: атрибут формы `data-endpoint="/local/rest/..."`, только относительный путь от корня сайта.
- Method: `POST`
- Content-Type: `application/json; charset=UTF-8`
- Response: JSON
- Security bootstrap: `tacticum_rest_validate_origin()` -> `tacticum_rest_rate_limit('tacticum_form')` -> `tacticum_rest_require_method('POST')` -> parse JSON -> `tacticum_rest_check_csrf($data, true)` -> validation -> upstream request.
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

Форма `price-specialist` на `/price/` должна использовать `data-endpoint="/local/rest/tacticum_sale_staff.php"`. Этот endpoint сохраняет доменную модель заказа сотрудников (`workers[]`, `start_date`, `end_date`, `worker_timeline`, `workload`, `cost_per_hour`, `amount_of_workers`) и временно адаптирует её в `HotSaleRequestDTO` для `/tacticum/v1/chat_agent/sale`. Отдельный `/tacticum/v1/sale/workers` в актуальном OpenAPI отсутствует.

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
| `offer-cta` | CTA форма на `/offer/` / detail offer template |
| `aiagents-inline` | Inline форма на `/aiagents/` |
| `contact-modal` | Общая модальная форма из footer |

Правило для новых форм: `page-or-context-purpose`, например `contacts-cta` или `service-detail-cta`. Не переиспользовать один `form_id` для разных пользовательских сценариев.

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

Backend treats any upstream `2xx` response from `/tacticum/v1/chat_agent/sale` as accepted, including an empty upstream body. The default lead endpoint, `tacticum_offer.php` and `tacticum_sale.php` use shared `tacticum_rest_submit_chat_agent_sale(...)` for the upstream call, masked logs and retry policy. If upstream rejects a sale payload that contains `group_id`, backend retries the same lead once without `group_id`; this keeps the manual contact request deliverable when AI chat context is stale or malformed upstream. Non-2xx after retry remains `502 upstream_error`.

`tacticum_sale_staff.php` возвращает тот же формат успешного ответа. Если передан `workers_json` / `workers`, endpoint валидирует до 20 позиций и суммарно до 100 специалистов; legacy-поля `specialist`, `level`, `rate`, `amount_of_workers` остаются fallback для одиночного заказа. Детали состава команды передаются upstream внутри `task`, а rich staff payload остаётся в backend-логике и masked logs.

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
- Prefilled chat form with a valid lead payload but upstream failure on `group_id`: endpoint retries without `group_id` and returns success if the plain lead is accepted.
- Specialist order с `/price/`: форма отправляется в `/local/rest/tacticum_sale_staff.php`, rich staff payload содержит `workers[]`, adapter отправляет заявку в `/tacticum/v1/chat_agent/sale`, PII в логах маскируется.
- Multi-staff order с `/price/`: пользователь добавляет несколько ролей/уровней, меняет количество, frontend отправляет `workers_json`, backend формирует `workers[]` и текстовое резюме команды в `task`.
- Exact deadline order с `/price/`: при выборе `duration=exact-date` frontend раскрывает календарь `endDate`, блокирует отправку без даты, backend также возвращает `validation_error`, если дата не передана.
- Upstream недоступен: endpoint возвращает `502`, пользователь видит общий error state без технических деталей.
