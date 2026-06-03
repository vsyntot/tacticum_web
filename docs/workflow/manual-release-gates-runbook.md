# Manual Release Gates Runbook

Дата фиксации: 24.05.2026

Этот runbook закрывает операционный слой release sign-off: проверки, которые нельзя безопасно автоматизировать в production без доступа к Яндекс.Метрике, Bitrix admin и внешнему upstream/CRM или без создания тестовых лидов.

## Scope

Использовать для gates из `docs/workflow/release-signoff-gates.md`:

- `manual-success-flow`;
- `metrika-goals`;
- `bitrix-admin`;
- `staff-sale-upstream`.

Staging предпочтителен. Production допустим только как controlled test lead с явной пометкой в CRM/upstream, чтобы тестовый лид не ушёл в коммерческую обработку.

## Evidence Rules

- Не сохранять в docs/issue имя, телефон, email, текст сообщения, полный payload или raw upstream response.
- Фиксировать только ID лида/заявки, masked `group_id`, checked_at, owner, URL, статус и короткий технический результат.
- Время писать в ISO-формате с timezone, например `2026-05-24T09:30:00+03:00`.
- Если проверка заблокирована, gate остаётся `pending` с `reason`, `owner` и ожидаемым сроком.
- После успешной ручной проверки обновить release sign-off JSON и запустить строгую проверку:

```bash
npm run release:signoff:check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
```

Strict checker валидирует manual evidence: объектную структуру, `checked_at` с timezone, обязательные поля конкретного gate, отсутствие placeholder-ов, email/phone-like значений и небезопасных ключей вроде `payload`, `raw_response`, `cookie`, `session`, `sessid`, `token`, `secret`.

До закрытия всех ручных gates допустима только draft-проверка:

```bash
npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
npm run release:signoff:summary -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
```

Перед передачей задачи QA/PM/Admin/Backend owner-ам можно сгенерировать текущий список pending manual gates, next actions and safe evidence skeletons:

```bash
npm run release:manual-gates:helper -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
```

Helper ничего не отправляет в production и не собирает PII. Он читает release sign-off draft, показывает только pending manual gates по умолчанию и печатает skeletons, которые нужно заполнить реальными safe ID/owner/time перед переносом в sign-off JSON. Для машинного handoff доступен формат:

```bash
npm run release:manual-gates:helper -- --json
```

Если `docs/` не выгружен на production server, helper не падает на отсутствующем дефолтном draft и запускается в standalone skeleton mode. В этом режиме он не знает текущие `reason` / `safe_summary` из repository sign-off, но всё ещё печатает валидные skeletons для owner evidence. Draft-aware вывод нужно запускать в репозитории с `docs/workflow/release-signoff-*.json` или явно передавать путь через `TACTICUM_RELEASE_SIGNOFF`.

## Gate: manual-success-flow

Owner: QA + Backend/Frontend owner.

Перед controlled проверкой default form, modal form, AI chat and prefill можно сгенерировать payload templates, browser snippet, curl templates and safe evidence block:

```bash
npm run manual:success-flow:helper
```

Helper ничего не отправляет сам. Browser/curl snippets создают тестовые лиды только после ручной замены controlled contact placeholders and owner-run отправки. Staff-order часть этого gate проверять через `npm run staff:sale:gate-helper`, потому что для неё нужен отдельный rich workers contract and CRM/upstream ID.

Минимальный набор для релиза с формами, AI flow и `/price/`:

| Flow | Где проверять | Passed |
|---|---|---|
| Default lead form | Любая затронутая публичная форма, предпочтительно `/price/` или `/calculator/` | UI показывает success state, backend вернул `success=true`, upstream/CRM принял лид |
| Modal form | Footer/modal CTA | Модалка открывается, валидная отправка даёт success state, закрытие не ломает страницу |
| AI chat | Hero/calculator/price chat | Валидное сообщение получает controlled response; нет raw stack/PII; если пришёл `group_id`, он пригоден для prefill |
| Prefill | CTA после AI response с `group_id` | Prefill возвращает ожидаемое заполнение или controlled empty state; manual submit остаётся доступен |
| Staff order | `/price/` team preset или состав заявки | `workers_json`, `team_preset`, `monthly_budget_estimate`, `endDate` доходят до backend/upstream |

Порядок:

1. Открыть staging URL или production URL в controlled test window.
2. Заполнить тестовыми неперсональными данными; в сообщении указать, что лид тестовый и не требует коммерческой обработки.
3. Отправить flow и проверить пользовательский success/error state.
4. Проверить upstream/CRM: лид создан или flow завершился ожидаемым controlled state.
5. Записать только безопасные evidence-поля: `lead_id`, `upstream_request_id`, `form_id`, `checked_at`, `owner`, `url`, `result`.

## Gate: metrika-goals

Owner: PM/Marketing + QA.

Проверять в Яндекс.Метрике counter `103471113` или в подключённом tag manager, если goals проксируются через него.

Перед owner-run проверкой можно сгенерировать expected goals/events, source taxonomy check, browser observer snippet and safe evidence block:

```bash
npm run metrika:goals:helper
```

Helper не имеет доступа к Яндекс.Метрике и не закрывает gate сам. Он проверяет локальный/deployed JS event contract and помогает owner-у понять, какие goals искать в Метрике и какие params считать безопасными.

Минимальный набор affected goals:

| Scenario | Goals/events |
|---|---|
| Forms | `tacticum_form_submit`, `tacticum_form_success` или ожидаемый `tacticum_form_error` |
| Staff order | `tacticum_form_submit`, `tacticum_form_success` с `form_id=price-specialist` |
| AI chat | `tacticum_chat_send`, `tacticum_chat_success` или ожидаемый `tacticum_chat_error` |
| Prefill | `tacticum_prefill_submit`, `tacticum_prefill_success` или ожидаемый `tacticum_prefill_error` |
| Telegram resolver | `tacticum_tg_resolver_success/error/skip`, только если менялся resolver или footer/social links |

Порядок:

1. Выполнить соответствующий user flow на staging/production.
2. В Метрике проверить, что goal/event появился после `checked_at`.
3. Убедиться, что параметры не содержат PII: только `page_path`, `form_id`, `endpoint`, `surface`, `status`, `code`, счётчики и boolean-флаги.
4. Записать `counter_id`, список goals, checked_at, owner и ссылку/ID внутреннего evidence, если скриншот хранится вне репозитория.

## Gate: bitrix-admin

Owner: QA/Admin.

Перед ручной проверкой можно вывести checklist, browser observer snippet и безопасный evidence block:

```bash
npm run bitrix:admin:gate-helper
```

Helper не логинится в Bitrix и не читает cookie/session; он только фиксирует формат проверки. Unauthenticated `npm run release:public-precheck:prod` остаётся baseline, но не заменяет authenticated admin smoke.

Порядок:

1. Авторизоваться в `/bitrix/admin/` пользователем с ролью администратора или контент-администратора.
2. Проверить, что admin panel открывается без 500/white screen.
3. Открыть публичную страницу с включённой верхней панелью Bitrix и убедиться, что template/header/assets не ломают admin toolbar.
4. Если deploy/cache задача затрагивала кеши, выполнить разрешённую cache operation или убедиться, что cache clear уже прошёл в deploy.
5. Записать role, checked_at, owner и проверенные admin/public URLs. Пароли, cookie и session IDs не сохранять.

## Gate: staff-sale-upstream

Owner: Architect + Backend + QA + DevOps.

Этот gate нужен, когда менялся `ai.endpoint_paths.staff_sale`, `/price/` staff-order payload или upstream workers contract.

Перед controlled проверкой можно сгенерировать тестовый payload, curl template и безопасный evidence block:

```bash
npm run staff:sale:gate-helper
```

Для curl template с реальными тестовыми данными задать env-переменные из вывода helper-а. Значение `sessid` брать из авторизованной браузерной сессии Bitrix на проверяемом окружении; тестовые контакты не сохранять в release evidence.

Порядок:

1. Подтвердить `health_config`: `success=true`, scopes включают `ai`, `rest`, `security`.
2. На `/price/` выбрать team preset или вручную собрать состав из нескольких специалистов.
3. Выбрать загрузку и срок; для `duration=exact-date` указать `endDate`.
4. Отправить заявку через modal `price-specialist`.
5. Проверить upstream/CRM: в заявке есть summary состава команды, количество worker rows, `team_preset`, `monthly_budget_estimate`, `end_date` или осознанный fallback.
6. Записать `upstream_request_id`/`lead_id`, `workers_count`, `team_preset`, `monthly_budget_estimate_present`, `end_date_present`, checked_at и owner.
7. Strict checker требует `team_preset` из `mvp`, `discovery`, `support`, `qa-burst`, `monthly_budget_estimate_present=true` и `end_date_present=true`.

## Closing The Release JSON

1. Взять безопасный формат из `docs/workflow/release-signoff-manual-evidence.template.json`.
2. Перенести evidence в `docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json`.
3. Для закрытого gate заменить `status: "pending"` на `status: "passed"` и удалить `reason`.
4. Запустить `npm run release:signoff:check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json`.
5. Если checker прошёл, PM может закрывать release issue; если нет, issue остаётся в `Review`.
