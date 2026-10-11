# Legacy Sale Alias Support Extension — 2026-10-11

Дата решения: `2026-10-11`

## Решение

PM принял временно продлить поддержку legacy POST aliases до `31.12.2026`:

- `/local/rest/tacticum_offer.php`;
- `/local/rest/tacticum_sale.php`.

Сохраняются текущие JSON response shapes, security bootstrap, rate-limit class, `Deprecation`, `Sunset` и `Link: rel="successor-version"` headers. Successor для новых интеграций — `/local/rest/tacticum_form.php`.

## Основание

Полный inventory внешних consumers ещё не закрыт:

- repo scan не нашёл first-party callers;
- промежуточная проверка production access logs за `2026-05-24` — `2026-06-03` обработала `79384` строки и не нашла обращений к aliases;
- полный access-log window и CRM/upstream report остаются внешним evidence и не хранятся в репозитории.

При этих условиях немедленное удаление или `410 Gone` может нарушить неизвестный внешний consumer. Продление ограничено датой `31.12.2026` и не является бессрочной поддержкой.

## Обязательные изменения

- runtime `Sunset` headers: `Thu, 31 Dec 2026 00:00:00 GMT`;
- `local/rest/endpoint_policy.json`: sunset `2026-12-31`;
- `tools/legacy-sale-sunset-check.mjs` и production precheck синхронизированы с новой датой;
- lead-form contract, release-signoff gates и inventory runbook ссылаются на это решение.

## Контрольная точка

До `31.12.2026` PM + Backend + DevOps должны:

1. завершить aggregate access-log inventory и CRM/upstream cross-check без PII;
2. мигрировать подтверждённых consumers на `/local/rest/tacticum_form.php`;
3. выбрать и реализовать финальный режим aliases — удаление или `410 Gone`;
4. выполнить endpoint smoke и обновить release sign-off.

Если evidence не позволит завершить миграцию, новая дата требует отдельного accepted-risk решения PM + Architect.
