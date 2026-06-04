# Analytics Events

Дата фиксации: 21.05.2026

## Rules

- Client-side events отправляются через `window.tacticumTrackEvent(...)`.
- Модуль: `local/templates/tacticum/js/analytics.js`.
- Подключение: `local/templates/tacticum/header.php` перед `forms.js`, `chat-agent.js`, `tg-link-resolver.js`.
- В события нельзя передавать PII: имя, телефон, email, текст сообщения, summary, raw URL с query.
- Допустимые параметры: `page_path`, `form_id`, `endpoint`, `surface`, `status`, `code`, controlled `product`, controlled `page_role`, controlled `scenario`, `cta`, boolean-флаги и счётчики.
- Sprint 15 lead qualification fields (`lead_budget`, `lead_timeline`, `lead_offer_code`, `lead_offer_title`, `lead_industry`, `lead_scenario`) отправляются в lead payload only. Backend normalizes them into a canonical lead qualification profile for CRM/upstream fallback text. Product funnel analytics may use only allowlisted controlled `product`, `page_role` and `scenario`; budget, timeline, offer code/title, industry, user message, name, email and phone must not be sent to analytics.
- Sprint 19 CJM/CRM decision is documented in `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md`; it keeps current no-PII product funnel analytics and blocks structured CRM/upstream analytics params until owner approval.

## Event Taxonomy

| Event | Source | Params |
|---|---|---|
| `tacticum_form_submit` | `forms.js` | `form_id`, `endpoint`, `page_path` |
| `tacticum_form_success` | `forms.js` | `form_id`, `endpoint`, `status`, `page_path` |
| `tacticum_form_error` | `forms.js` | `form_id`, `endpoint`, `status`, `code`, `page_path` |
| `tacticum_form_validation_error` | `forms.js` | `form_id`, `page_path` |
| `tacticum_product_view` | `analytics.js` | `product`, `page_role`, `page_path` |
| `tacticum_product_cta_click` | `analytics.js` | `product`, `page_role`, `cta`, `page_path` |
| `tacticum_product_form_submit` | `forms.js` | `product`, `page_role`, `scenario`, `form_id`, `endpoint`, `page_path` |
| `tacticum_product_form_success` | `forms.js` | `product`, `page_role`, `scenario`, `form_id`, `endpoint`, `status`, `page_path` |
| `tacticum_product_form_error` | `forms.js` | `product`, `page_role`, `scenario`, `form_id`, `endpoint`, `status`, `code`, `page_path` |
| `tacticum_chat_send` | `chat-agent.js` | `surface`, `page_path` |
| `tacticum_chat_success` | `chat-agent.js` | `surface`, `status`, `has_group_id`, `has_offer_url`, `page_path` |
| `tacticum_chat_error` | `chat-agent.js` | `surface`, `status`, `code`, `page_path` |
| `tacticum_prefill_submit` | `chat-agent.js` | `surface`, `page_path` |
| `tacticum_prefill_success` | `chat-agent.js` | `surface`, `status`, `page_path` |
| `tacticum_prefill_error` | `chat-agent.js` | `surface`, `status`, `code`, `page_path` |
| `tacticum_chat_lead_handoff` | `chat-agent.js` | `surface`, `has_group_id`, `has_prefill_summary`, `page_path` |
| `tacticum_tg_resolver_success` | `tg-link-resolver.js` | `status`, `links_count`, `page_path` |
| `tacticum_tg_resolver_error` | `tg-link-resolver.js` | `status`, `code`, `page_path` |
| `tacticum_tg_resolver_skip` | `tg-link-resolver.js` | `status`, `code`, `page_path` |

## Destinations

`analytics.js` отправляет событие в:

- Yandex.Metrika `reachGoal`, если доступен `window.ym`;
- `window.dataLayer`, если он уже создан внешним tag manager;
- browser event `tacticum:analytics` для QA/debug hooks.
