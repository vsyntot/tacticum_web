# REST Response Contract Decision

Дата фиксации: 23.05.2026

## Решение

Полную унификацию success-body для всех существующих endpoints не выполняем в Sprint 08.

Причина: `tacticum_form.php`, `tacticum_chat.php`, `tacticum_prefill.php`, `tacticum_sale_staff.php`, `tacticum_offer.php` и `tacticum_sale.php` уже имеют публичные или frontend-зависимые response shapes. Их механическое выравнивание создаст риск для текущих consumers без продуктовой выгоды.

## Правило

- Существующие endpoints сохраняют текущий JSON contract.
- Новые endpoints используют `tacticum_rest_response(true, 'ok', null)` / `tacticum_rest_error(...)`, если нет явного contract reason.
- Legacy aliases `tacticum_offer.php` и `tacticum_sale.php` сохраняют response shape до решения по sunset после `30.09.2026`.
- Любое изменение success/error model требует обновления соответствующего contract doc и ADR/API decision.

## Текущий Baseline

| Endpoint | Success shape | Решение |
|---|---|---|
| `/local/rest/tacticum_form.php` | `{ "success": true, "error": null, "code": "ok" }` | Сохраняем |
| `/local/rest/tacticum_sale_staff.php` | `{ "success": true, "code": "ok" }` | Сохраняем |
| `/local/rest/tacticum_offer.php` | `{ "success": true }` | Сохраняем до sunset decision |
| `/local/rest/tacticum_sale.php` | `{ "success": true }` | Сохраняем до sunset decision |
| `/local/rest/tacticum_chat.php` | Chat-specific payload | Сохраняем |
| `/local/rest/tacticum_prefill.php` | Prefill-specific payload | Сохраняем |

## Follow-Up Trigger

Вернуться к унификации только если появится один из факторов:

- новый внешний consumer требует единый envelope;
- frontend больше не зависит от legacy shapes;
- legacy aliases удалены или переведены в `410/redirect`;
- появляется versioned API namespace для публичных REST endpoints.
