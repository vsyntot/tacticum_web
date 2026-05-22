# Post-Deploy Smoke Checklist

Использовать после deploy в production или staging. PM не закрывает Issue, пока релевантные пункты не подтверждены.

## Общие Проверки

- [ ] Production URL открывается.
- [ ] Header/menu/footer отображаются.
- [ ] Console без новых критичных JS errors.
- [ ] `npm run visual:smoke` проходит для затронутых публичных страниц; для browser zero-error gate manifest не содержит `pageErrors`, `consoleErrors`, first-party `networkErrors`.
- [ ] `npm run browser:smoke` проходит для non-network UI actions: меню, модалки, пустая валидация форм, empty-send чатов, `/price/` filters/modal.
- [ ] Нет 500/502 на затронутых страницах.
- [ ] Bitrix admin panel не сломана для авторизованного администратора.

## Forms

Контракт публичных лид-форм: `docs/workflow/lead-form-contract.md`.

Для каждой затронутой формы:

- [ ] Обязательные поля валидируются на клиенте.
- [ ] Consent checkbox обязателен.
- [ ] Consent-ссылка ведёт на `/policies/`.
- [ ] `sessid` передаётся или endpoint ожидаемо проходит CSRF policy.
- [ ] POST возвращает JSON `{ success: true }` или документированную ошибку.
- [ ] Пользователь видит success/error state.
- [ ] Срабатывают analytics events `tacticum_form_submit` и success/error без PII.
- [ ] В логах нет PII без маскировки.

Формы:

- [ ] Main CTA `/`
- [ ] Modal form
- [ ] `/about/` CTA
- [ ] `/services/` CTA
- [ ] `/calculator/` CTA
- [ ] `/price/` CTA
- [ ] Specialist order modal
- [ ] `/offer/` CTA
- [ ] `/aiagents/` inline

## AI Chat / Offer

- [ ] Hero chat отправляет message.
- [ ] `GET /local/rest/tacticum_chat.php` возвращает controlled `405 method_not_allowed`.
- [ ] Calculator chat отправляет message.
- [ ] Price chat отправляет message.
- [ ] Upstream errors показываются пользователю без raw stack/PII.
- [ ] `group_id` сохраняется и используется для prefill, если сценарий это предполагает.
- [ ] Prefill production path вызывает `POST /local/rest/tacticum_prefill.php` с JSON `group_id` + `sessid`.
- [ ] `GET /local/rest/tacticum_prefill.php` возвращает controlled `405 method_not_allowed`.
- [ ] `bitrix_url` открывает offer page, если AI вернул ссылку.
- [ ] Срабатывают analytics events `tacticum_chat_*` и `tacticum_prefill_*` без текста сообщений.

## REST/API

- [ ] `/local/api/cases.php`
- [ ] `/local/api/faq.php`
- [ ] `/local/api/rates.php`
- [ ] `/local/api/services.php`
- [ ] `/local/rest/tacticum_form.php`
- [ ] `/local/rest/tacticum_sale_staff.php`
- [ ] `/local/rest/tacticum_chat.php`
- [ ] `/local/rest/tacticum_prefill.php`
- [ ] `/local/rest/resolve_telegram_link.php`
- [ ] `/local/rest/health_config.php`

Проверять только затронутые endpoints, но при security/config изменениях — весь список.

## SEO

- [ ] `https://tacticum.ru/sitemap.xml` отдаёт XML.
- [ ] `https://tacticum.ru/sitemap-files.xml` отдаёт XML.
- [ ] Sitemap loc используют HTTPS.
- [ ] `/policies/` есть в `sitemap-files.xml`.
- [ ] `robots.txt` указывает HTTPS sitemap.
- [ ] Новый публичный URL есть в sitemap.
- [ ] У затронутой страницы есть один H1.
- [ ] Title/description корректны.
- [ ] Canonical присутствует и соответствует URL-стратегии страницы.
- [ ] OpenGraph meta присутствуют без дублей.

## Assets

- [ ] `/`, `/services/`, `/calculator/`, `/offer/`: `faq.js` подключается.
- [ ] `/price/`: `faq.js` и `charts.js` подключаются.
- [ ] `/contacts/`: `yandex-map.js` подключается, карта загружается без first-party JS errors.
- [ ] `/aiagents/`: `faq.js` и `aiagents.css` подключаются.
- [ ] `/about/`, `/contacts/`, `/policies/`: optional assets не подключаются без явной необходимости.

## DevOps Handoff

DevOps/PM фиксирует в Issue:

- commit / PR;
- время deploy;
- результат автоматического `Smoke config health` из deploy workflow;
- затронутые URL/API;
- кто выполнил smoke-check;
- найденные follow-up gaps.
