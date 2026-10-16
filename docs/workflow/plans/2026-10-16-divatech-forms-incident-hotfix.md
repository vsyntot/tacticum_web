# Divatech forms incident hotfix — 16.10.2026

Статус: `in_progress`.
Workflow lane: `Incident` + `Security / Integration`.
Release class: `FILE_ONLY`.
Owner: project-manager; production approver: владелец проекта по текущему запросу.

## Симптомы и доказанная причина

- `POST https://divatech.space/local/rest/divatech_lead.php` из пользовательского сеанса зафиксирован как `422 invalid_fields`.
- Чистый браузерный JSON с `consent=true`, валидным UUID v4, Origin/Referer и cookie отвечает `200 request_saved`.
- Матричная проверка показывает, что эквивалентные браузерные значения согласия `"on"` и `"1"` дают тот же `422`; остальные проверки (Origin, CSRF, honeypot, UUID, обязательные поля) остаются строгими.
- `/bitrix/admin/` на Divatech отвечает `403` по принятой site-boundary политике. Канонический административный вход — `https://tacticum.ru/bitrix/admin/`, который отвечает `200`. Второй публичный admin-контур не открывается.

## Точный scope

Изменяются только repo-owned FILE_ONLY файлы:

- `local/lib/Tacticum/Divatech/LeadPayload.php` — принять только `true`, `"true"`, `"on"`, `"1"` как согласие;
- `local/templates/divatech/script.js` — получить checkbox через явный selector и отправлять boolean;
- `tools/divatech/contracts-test.php` — закрепить совместимость и отрицательные случаи.

`bitrix/`, DB, инфоблоки, server-owned config, Nginx boundary и Tacticum runtime не изменяются.

## Порядок и rollback

1. Собрать один неизменяемый архив с двумя runtime-файлами и manifest/hash.
2. На production под lock проверить ожидаемые pre-hash, сделать backup exact scope и restore rehearsal в отдельном временном каталоге.
3. Применить только утверждённый архив, проверить PHP/JS syntax и post-hash.
4. Выполнить непривилегированный HTTP smoke: contact/callback с cookie+CSRF, duplicate idempotency, invalid CSRF/foreign Origin; synthetic leads не оставлять сверх согласованной проверки.
5. При регрессии восстановить exact backup под тем же lock, повторить smoke и зафиксировать исходный post-state.

## Acceptance

- Payload с boolean и совместимыми string-вариантами согласия проходит только при валидных остальных полях.
- Invalid/false consent, invalid UUID, CSRF и Origin отклоняются. A browser-filled honeypot value is discarded after the same-origin/CSRF/rate-limit guards so autofill cannot reject a real visitor.
- Production contact и callback отвечают `200 request_saved`, повтор того же `request_id` — `200 request_saved` без нового элемента.
- `https://divatech.space/bitrix/admin/` остаётся `403`; `https://tacticum.ru/bitrix/admin/` остаётся доступен для канонического входа.
- Backup, restore rehearsal, hashes, smoke и cleanup записаны без PII/секретов.

## Follow-up hotfix — browser autofill

После повторного пользовательского `422` access log подтвердил, что live API
проходит с корректным payload, но Chrome-сеанс продолжает присылать значение
скрытого `website`. В версии `DIVA-FIX-incident-form-honeypot-compat` server
parser больше не использует honeypot как hard rejection после уже пройденных
Origin/CSRF/rate-limit guards; клиент дополнительно очищает поле перед POST.
Проверка с заполненным `website` и существующим synthetic `request_id` теперь
возвращает `200 request_saved`, а `consent=false` сохраняет `422`.

The later cache/admin correction is tracked separately in `docs/workflow/plans/2026-10-16-divatech-form-cache-and-admin-redirect.md`.
