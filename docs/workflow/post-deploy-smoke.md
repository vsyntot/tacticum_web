# Post-Deploy Smoke Checklist

Использовать после deploy в production или staging. PM не закрывает Issue, пока релевантные пункты не подтверждены.

`deploy.yml` автоматически выполняет `health_config`, `npm run js:check`, `npm run css:check`, `npm run css:syntax`, `npm run seo:check`, `npm run visual:smoke`, `npm run browser:console` и `npm run seo:check:prod` против `https://tacticum.ru` после очистки Bitrix cache. Очистка должна включать managed cache, component HTML cache для `news.list`/`news.detail`, composite HTML pages и CSS/JS asset cache активного шаблона, иначе production может отдать новый JS/CSS поверх старого component HTML. В deploy `visual:smoke` запускается с `TACTICUM_EXPECT_SEO_HEAD=1`, сохраняет rendered SEO head and product block inventory в `manifest.json` и падает при отсутствующих/дублирующихся title, description, canonical, OpenGraph meta, выпадении money/product pages из rendered navigation, отсутствии product `SoftwareApplication` + `FAQPage` schema или отсутствии required `data-product-block` на `/platform/`, `/agents/`, `/dev/`, `/forum/`. `browser:console` в deploy запускается с `TACTICUM_VISUAL_FAIL_ON_WARNINGS=1`, product scenario select checks, product FAQ toggle checks и обязательной проверкой `/price/` team presets. `seo:check:prod` дополнительно проверяет production sitemap governance и `X-Robots-Tag` на JSON endpoints.

Этот чеклист остаётся ручной матрицей для staging, локальных выкладок и real success-flow, которые нельзя безопасно автоматизировать в production без создания лидов.

Release sign-off gates для ручных проверок зафиксированы в `docs/workflow/release-signoff-gates.md`; пошаговое закрытие pending gates описано в `docs/workflow/manual-release-gates-runbook.md`.

Для product-first релиза после deploy/cache refresh можно запустить единый automated handoff:

```bash
npm run release:product-first:prod-check
```

Команда объединяет `seo:check:prod`, rendered SEO smoke with product schema checks, warning-aware browser action smoke, focused `/price/` smoke, product-first draft validation and `gaps:known` summary for the product-first sign-off draft.

Для AS IS screenshot handoff по product blocks:

```bash
npm run product:block-previews:prod
```

Команда пишет full-page screenshots, `manifest.json` and `product-blocks/*.png`; подробности в `docs/workflow/product-block-preview-workflow.md`.

## Общие Проверки

- [ ] Production URL открывается.
- [ ] Header/menu/footer отображаются.
- [ ] Console без новых критичных JS errors.
- [ ] `npm run visual:smoke` проходит для затронутых публичных страниц; manifest не содержит `pageErrors`, `consoleErrors`, first-party `networkErrors`, `seoErrors` и `productBlockErrors`; для product pages `seoHead.productSchemaSummary` содержит `SoftwareApplication` and `FAQPage`, а `productBlocks.found` содержит required `data-product-block` taxonomy.
- [ ] `npm run browser:console` проходит для non-network UI actions и browser console cleanliness: меню, модалки, пустая валидация форм, empty-send чатов, `/price/` filters/modal; manifest не содержит `consoleWarnings`.
- [ ] Для полной production CSS/JS e2e readiness проходит `npm run e2e:css-js:prod`; manifest не содержит runtime/action/browser blockers.
- [ ] Для product-first release после deploy/cache refresh проходит `npm run release:product-first:prod-check`; pending ручные gates остаются pending до отдельной evidence.
- [ ] Для design/QA AS IS handoff по product pages при необходимости проходит `npm run product:block-previews:prod`; manifest содержит `productBlockScreenshots[]`, а `productBlockErrors=[]`.
- [ ] При CSS-правках проходит `npm run visual:smoke:css-local`; при изменении интерактивных CSS-состояний также `npm run browser:smoke:css-local`.
- [ ] При CSS/JS PR до deploy проходит `npm run e2e:css-js:local`; для локальной JS-проверки конкретного компонента используется `TACTICUM_VISUAL_INJECT_JS=<path> npm run browser:smoke`.
- [ ] `/price/` action smoke подтверждает team presets, persistent summary и расчёт месячного бюджета: `npm run browser:smoke:price`; в manifest action `price team presets/summary` имеет `status=ok` для desktop/mobile.
- [ ] Валидная `/offer/<code>/?clear_cache=Y` не отдаёт 404; этот guard входит в `npm run seo:check:prod`.
- [ ] Валидная `/offer/<code>/` визуально проверена на readable estimate block; при ручной проверке можно использовать focused smoke: `TACTICUM_VISUAL_PAGES=/offer/<code>/ TACTICUM_EXPECT_SEO_HEAD=1 npm run visual:smoke`.
- [ ] `/offer/?scenario=ai-kopaylot&page=2&clear_cache=Y` редиректит на `/offer/catalog/scenario/ai-kopaylot/page/2/?clear_cache=Y`; этот guard входит в `npm run seo:check:prod`.
- [ ] Если `/price/` smoke падает с `team preset controls are missing`, проверить, что очищен component cache `bitrix/cache/s1/bitrix/news.list/*` и composite cache `bitrix/html_pages/*`, а rendered HTML содержит `data-price-team-preset`.
- [ ] Release sign-off JSON заполнен по `docs/workflow/release-signoff.example.json`, ручные gates закрыты по `docs/workflow/manual-release-gates-runbook.md` и файл проходит `npm run release:signoff:check -- <file>`.
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
- [ ] Для Sprint 15 CTA формы optional `lead_budget` / `lead_timeline` отправляются только в lead payload и не появляются в analytics event params.
- [ ] Для product CTA `/platform/`, `/agents/`, `/dev/`, `/forum/` optional `lead_scenario` select содержит только controlled values, отправляется в lead payload и не появляется в analytics event params.
- [ ] `/offer/<code>/` form smoke подтверждает hidden `lead_offer_code` / `lead_offer_title` context и отсутствие этих values в analytics events.
- [ ] `/calculator/` и `/price/` light chat handoff заполняет только целевую CTA form, сохраняет scoped `group_id` на форме и не передаёт текст сообщения в analytics events.
- [ ] Если проверка выполняется как real success-flow, evidence записан по `release-signoff-gates.md`.
- [ ] Кастомный runtime-код `/local` и публичных скриптов не пишет payload/response в файловые логи.

Формы:

- [ ] Main CTA `/`
- [ ] `/platform/` CTA
- [ ] `/agents/` CTA
- [ ] `/dev/` CTA
- [ ] `/forum/` CTA
- [ ] Modal form
- [ ] `/about/` CTA
- [ ] `/contacts/` CTA
- [ ] `/services/` CTA
- [ ] `/calculator/` CTA
- [ ] `/price/` CTA
- [ ] Specialist order modal
- [ ] `/price/` team presets + persistent summary
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
- [ ] Calculator/price chat после успешного ответа показывает action `Передать вводные...`; click скроллит к CTA, заполняет message и отправляет только safe boolean analytics event `tacticum_chat_lead_handoff`.
- [ ] `GET /local/rest/tacticum_prefill.php` возвращает controlled `405 method_not_allowed`.
- [ ] `bitrix_url` открывает canonical offer page `/offer/<element-code>/`, если AI вернул ссылку.
- [ ] `/offer/?ID=<valid>` отдаёт 301 на canonical `/offer/<element-code>/`.
- [ ] `/offer/?ID=<invalid>` и `/offer/<invalid-code>/` отдают 404 и `noindex`.
- [ ] Срабатывают analytics events `tacticum_chat_*` и `tacticum_prefill_*` без текста сообщений.
- [ ] Если менялись analytics events или `metrika.js`, цели подтверждены в Yandex.Metrika/tag manager и записаны в release issue.

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

- [ ] JSON endpoints из списка выше отдают `X-Robots-Tag: noindex, nofollow`.

## SEO

- [ ] `https://tacticum.ru/sitemap.xml` отдаёт XML.
- [ ] `https://tacticum.ru/sitemap.xml` содержит `https://tacticum.ru/sitemap-basic-files.xml` и `https://tacticum.ru/offer/sitemap.php`.
- [ ] `https://tacticum.ru/sitemap-basic-files.xml` отдаёт XML, сгенерированный штатным Bitrix sitemap.
- [ ] `https://tacticum.ru/sitemap-basic-files.xml` не содержит `/404.php`, `/bitrix/` и `/local/`.
- [ ] `https://tacticum.ru/sitemap-basic-files.xml` содержит product URLs `/platform/`, `/agents/`, `/dev/`, `/forum/` после штатной генерации Bitrix sitemap.
- [ ] `https://tacticum.ru/offer/sitemap.php` отдаёт XML с активными canonical `/offer/<element-code>/`, если offer elements есть.
- [ ] `/offer/sitemap.php` не содержит повторяющихся `<loc>` даже при старых offer elements с одинаковым `CODE`.
- [ ] `npm run seo:check` проходит локально/в CI: sitemap, robots и canonical inventory синхронизированы.
- [ ] `npm run seo:check:prod` проходит после deploy: production sitemap/robots синхронизированы, static sitemap покрывает публичные URL, JSON endpoints отдают `X-Robots-Tag: noindex, nofollow`.
- [ ] Sitemap loc используют HTTPS.
- [ ] `/policies/` есть в `sitemap-basic-files.xml`.
- [ ] `robots.txt` указывает HTTPS sitemap.
- [ ] Новый публичный URL есть в sitemap.
- [ ] В rendered верхней навигации/dropdown `Услуги`, mobile menu и footer доступны `/price/`, `/offer/`, `/calculator/`, `/aiagents/`.
- [ ] В rendered product navigation и footer доступны `/platform/`, `/agents/`, `/dev/`, `/forum/`.
- [ ] В блоке `/services/` → `Наши услуги` есть карточка `Расчет проекта` со ссылкой `/offer/`.
- [ ] У затронутой страницы есть один H1.
- [ ] 404 URL отдаёт HTTP 404, title `Страница не найдена - Тактикум`, один H1 и `noindex` в meta/header.
- [ ] Rendered head подтверждён автоматикой: `npm run seo:smoke` прошёл, а в manifest для затронутых URL `seoErrors=[]`.
- [ ] Manifest `seoHead` содержит один `title`, одну `description`, один HTTPS `canonical` с path текущей страницы, top navigation links `/price/`, `/offer/`, `/calculator/`, `/aiagents/` и OpenGraph `og:site_name`, `og:type`, `og:url`, `og:title`, `og:description`, `og:image` без дублей.
- [ ] Manifest/rendered HTML содержит Twitter Card, `og:image:width/height/type` и JSON-LD graph на публичных URL; `/platform/`, `/agents/`, `/dev/`, `/forum/` содержат rendered `SoftwareApplication` + `FAQPage` and required product `data-product-block` inventory.
- [ ] Страницы без page-specific social image используют `og-default.jpg` 1200x630.
- [ ] Если SEO head проверяется вручную на staging, результат перенесён в release issue по `release-signoff-gates.md`.

## Assets

- [ ] `/platform/`, `/agents/`, `/dev/`, `/forum/`: `faq.js` подключается, FAQ раскрывается без console errors, CTA остаётся после FAQ.
- [ ] `/`, `/services/`, `/calculator/`, `/offer/`: `faq.js` подключается.
- [ ] `/price/`: `faq.js` и `charts.js` подключаются.
- [ ] `/contacts/`: Yandex map widget iframe показывает объект `Тактикум`, ссылка "Открыть в Яндекс Картах" открывает карточку `Тактикум`, `БЦ Victory Park` остаётся ориентиром, а юридический адрес остаётся видимым текстом; `yandex-map.js` не обязателен и не должен давать first-party JS errors.
- [ ] `/aiagents/`: `faq.js` подключается, body содержит `tacticum-aiagents-page`, hero/background styles приходят из `styles/global.css`.
- [ ] `/about/`, `/contacts/`, `/policies/`: optional assets не подключаются без явной необходимости.
- [ ] Response headers содержат `Content-Security-Policy-Report-Only` без новых browser console errors.

## DevOps Handoff

DevOps/PM фиксирует в Issue:

- commit / PR;
- время deploy;
- результат автоматического `Smoke config health`, `Post-deploy visual smoke` и `Post-deploy browser action smoke` из deploy workflow;
- результат `npm run seo:check` и `npm run seo:check:prod`;
- путь/ссылка на `visual:smoke` manifest с `seoErrors=0`;
- путь/ссылка на `browser:smoke` manifest, где `/price/` action `price team presets/summary` прошёл для desktop/mobile, если затронут `/price/`;
- результат обязательных sign-off gates из `release-signoff-gates.md`;
- путь к release sign-off JSON, прошедшему `npm run release:signoff:check -- <file>`;
- затронутые URL/API;
- кто выполнил smoke-check;
- найденные follow-up gaps.
