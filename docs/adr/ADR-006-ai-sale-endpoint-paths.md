# ADR-006: AI Sale Endpoint Paths Through Config

**Статус:** Принято
**Дата:** 23.05.2026
**Автор:** Architect

## Контекст

Публичные лид-формы и заказ специалистов используют внешний AI/upstream. До Sprint 08 все sale-сценарии адаптировались в один путь `/tacticum/v1/chat_agent/sale`; при этом `/price/` уже отправляет доменную модель команды (`workers[]`, `team_preset`, `monthly_budget_estimate`) внутри текстового `task`.

Следующий продуктовый шаг зависит от upstream: если появится отдельный endpoint для rich workers payload, сайт должен переключить staff-order без правки публичного frontend-контракта и без хардкода URL.

## Решение

Пути AI sale endpoints задаются через `tacticum_config.php`:

- `ai.endpoint_paths.chat_agent_sale` — путь для обычных lead/sale requests;
- `ai.endpoint_paths.staff_sale` — путь для `/price/` staff-order requests.

Значение должно быть относительным HTTPS-base path, например `/tacticum/v1/chat_agent/sale`. Host и scheme остаются только в `base_urls.AI_SERVICE_BASE_URL`, который проходит существующую HTTPS validation.

Backend использует:

- `tacticum_rest_get_required_https_ai_url('AI_SERVICE_BASE_URL')` для base URL;
- `tacticum_rest_get_ai_endpoint_path(...)` для endpoint path;
- `tacticum_rest_build_url(...)` для финального URL.

Если config key отсутствует, сохраняется прежний путь `/tacticum/v1/chat_agent/sale`.

## Матрица Переключения Staff Sale

`ai.endpoint_paths.staff_sale` - единственный разрешённый переключатель для будущего rich workers upstream. Frontend payload `/price/` и публичный endpoint `/local/rest/tacticum_sale_staff.php` не меняются только в случае, если upstream contract совместим с текущей моделью `workers[]`.

| Условие | Значение `staff_sale` | Действие |
|---|---|---|
| Отдельного rich workers endpoint нет | `/tacticum/v1/chat_agent/sale` | Оставить текущий adapter: rich staff details передаются в `task` |
| Upstream опубликовал совместимый endpoint | Новый относительный path из OpenAPI / contract | Обновить server config, выполнить `config-sync`, `health_config` и staging staff-order smoke |
| Upstream требует иной request/response contract | Не переключать config | Открыть Security / Integration задачу, обновить adapter, ADR и `lead-form-contract.md` |
| Staging smoke нового path не прошёл | `/tacticum/v1/chat_agent/sale` | Откатить config path без frontend rollback |

Процессный owner и даты handoff зафиксированы в `docs/workflow/sprints/2026-05-23-sprint-09-sale-sunset-upstream.md`.

## Альтернативы

Оставить путь захардкоженным в коде.

Отклонено: при появлении upstream workers endpoint пришлось бы менять PHP-код и деплоить сайт ради конфигурационного переключения.

Задать полный URL для каждого endpoint.

Отклонено: это увеличивает риск HTTP fallback и расходится с ADR-002/ADR-003 discipline, где host и secret-like config живут отдельно от публичного кода.

## Последствия

Плюсы:

- `/price/` staff-order можно переключить на будущий rich workers upstream через config;
- default behavior не меняется для production;
- health/config validation ловит некорректные endpoint paths без вывода секретов.

Минусы:

- production/staging `tacticum_config.php` нужно синхронизировать с `tacticum_config.example.php` при изменении endpoint paths;
- пока отдельный upstream endpoint не согласован, `staff_sale` указывает на текущий `/chat_agent/sale` adapter.
