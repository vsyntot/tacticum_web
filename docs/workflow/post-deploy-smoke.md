# Post-Deploy Smoke Checklist

Использовать после deploy в production или staging. PM не закрывает Issue, пока релевантные пункты не подтверждены.

## Общие Проверки

- [ ] Production URL открывается.
- [ ] Header/menu/footer отображаются.
- [ ] Console без новых критичных JS errors.
- [ ] Нет 500/502 на затронутых страницах.
- [ ] Bitrix admin panel не сломана для авторизованного администратора.

## Forms

Для каждой затронутой формы:

- [ ] Обязательные поля валидируются на клиенте.
- [ ] Consent checkbox обязателен.
- [ ] `sessid` передаётся или endpoint ожидаемо проходит CSRF policy.
- [ ] POST возвращает JSON `{ success: true }` или документированную ошибку.
- [ ] Пользователь видит success/error state.
- [ ] В логах нет PII без маскировки.

Формы:

- [ ] Main CTA `/`
- [ ] Modal form
- [ ] `/calculator/` CTA
- [ ] `/price/` CTA
- [ ] Specialist order modal
- [ ] `/offer/` CTA
- [ ] `/aiagents/` inline

## AI Chat / Offer

- [ ] Hero chat отправляет message.
- [ ] Calculator chat отправляет message.
- [ ] Price chat отправляет message.
- [ ] Upstream errors показываются пользователю без raw stack/PII.
- [ ] `group_id` сохраняется и используется для prefill, если сценарий это предполагает.
- [ ] `bitrix_url` открывает offer page, если AI вернул ссылку.

## REST/API

- [ ] `/local/api/cases.php`
- [ ] `/local/api/faq.php`
- [ ] `/local/api/rates.php`
- [ ] `/local/api/services.php`
- [ ] `/local/rest/tacticum_form.php`
- [ ] `/local/rest/tacticum_chat.php`
- [ ] `/local/rest/tacticum_prefill.php`
- [ ] `/local/rest/resolve_telegram_link.php`

Проверять только затронутые endpoints, но при security/config изменениях — весь список.

## SEO

- [ ] `https://tacticum.ru/sitemap.xml` отдаёт XML.
- [ ] Sitemap loc используют HTTPS.
- [ ] `robots.txt` указывает HTTPS sitemap.
- [ ] Новый публичный URL есть в sitemap.
- [ ] У затронутой страницы есть один H1.
- [ ] Title/description корректны.

## DevOps Handoff

DevOps/PM фиксирует в Issue:

- commit / PR;
- время deploy;
- затронутые URL/API;
- кто выполнил smoke-check;
- найденные follow-up gaps.
