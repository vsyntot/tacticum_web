# SEO Gap Analysis — tacticum.ru

Дата аудита: 24.05.2026  
Scope: публичные URL сайта, rendered head, sitemap/robots, 404, `/offer/`, служебные JSON endpoints, social preview и structured data.

## Executive Summary

SEO baseline сайта технически рабочий: production `npm run seo:smoke` 24.05.2026 прошёл по основным публичным URL, rendered head содержит один `title`, одну `description`, один HTTPS canonical, обязательные OpenGraph/Twitter meta, JSON-LD, один H1 и ссылки money pages в верхней навигации.

Evidence:

- production manifest: `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T06-56-34-214Z/manifest.json`;
- post-deploy production manifest: `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T08-28-30-284Z/manifest.json`;
- repo-owned `sitemap.xml` указывает на HTTPS `sitemap-basic-files.xml`, который генерируется штатным Bitrix sitemap, и на custom `https://tacticum.ru/offer/sitemap.php`;
- `robots.txt` указывает HTTPS sitemap;
- sitemap покрывает текущие публичные URL: `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`.

Post-deploy закрыты `SEO-001` - `SEO-008`; `SEO-009` принят как UX/navigation decision. Повторный `npm run seo:check:prod` после deploy dedupe fix прошёл, duplicate `<loc>` в динамическом `/offer/sitemap.php` устранены. После перевода Bitrix sitemap на `sitemap-basic*.xml` governance модель уточнена: root `sitemap.xml` остаётся в Git, generated static sitemap живёт на сервере, production guard проверяет отсутствие `/404.php`, `/bitrix/` и `/local/` в sitemap loc.

## Confirmed Baseline

| Area | Status | Evidence |
|---|---|---|
| Rendered title/description/canonical/OG/Twitter/JSON-LD/H1 | ok | `npm run seo:smoke`, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T08-28-30-284Z/manifest.json` |
| Sitemap XML validity | ok with guard | `npm run seo:check` проверяет repo-owned root index; `npm run seo:check:prod` проверяет production `sitemap.xml`, Bitrix-generated `sitemap-basic-files.xml` и dynamic `/offer/sitemap.php` |
| Robots sitemap pointer | ok | `robots.txt` содержит `Sitemap: https://tacticum.ru/sitemap.xml`, явно разрешает crawl публичного сайта и использует Yandex `Clean-param` для tracking/cache-параметров |
| Public URL coverage in sitemap | ok | sitemap содержит все 9 текущих публичных разделов |
| Image alt baseline | ok | production scan по `/`, `/about/`, `/price/`: missing `alt=0` |

## Open SEO Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| SEO-001 | closed | P1 | Full Feature | `/offer/` indexability | Валидные offer detail стали индексируемыми landing example pages на ЧПУ; invalid ID/code отдают 404/noindex | Production `https://tacticum.ru/offer/marketingoviy-marketpleys-dlya-medikov-i-klinik/`: `200`, self-canonical, H1, Twitter Card, JSON-LD; `/offer/?ID=999999` и `/offer/seo-invalid-offer-code-check/`: `404` + `meta robots noindex,nofollow`; `/offer/sitemap.php` отдаёт активные `/offer/<code>/` | Soft-404 и параметрический canonical для invalid offer устранены; ЧПУ details индексируемы | Поддерживать генерацию уникального `CODE`; duplicate loc guard ведётся в `SEO-007` |
| SEO-002 | closed | P1 | Fast Fix | 404 | 404-страница заменена явным template с правильными SEO-сигналами | Production `/no-such-page-seo-audit`: `HTTP 404`, `X-Robots-Tag: noindex, nofollow`; rendered smoke подтверждает head baseline | Поисковики получают корректный 404/noindex, пользователь видит понятную страницу ошибки | Поддерживать 404 в post-deploy smoke |
| SEO-003 | closed | P2 | Full Feature | SEO helper | Helper поддерживает `robots`, Twitter Card, OG image dimensions/type, JSON-LD и page-specific schema | Production `npm run seo:smoke` 24.05.2026: все 18 desktop/mobile checks `seo=ok`; manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T08-28-30-284Z/manifest.json` | SEO-правки централизованы в helper options | Поддерживать helper contract при новых публичных URL |
| SEO-004 | closed | P2 | Full Feature | Structured data | JSON-LD graph добавлен на публичные URL: Organization/WebSite/BreadcrumbList + page-specific schema и FAQPage там, где FAQ рендерится | Production `npm run seo:smoke`: JSON-LD валиден на 9 публичных URL; valid offer detail содержит JSON-LD BreadcrumbList | Rich-results семантика включена для организации, услуг, контактов, FAQ и offer details | При новых FAQ/страницах включать schema только там, где контент реально рендерится |
| SEO-005 | closed | P2 | Full Feature | Metadata quality | Money pages получили уточнённые title/meta, `/policies/` больше не перезаписывает meta компонентом | Production `npm run seo:smoke`: title/description/canonical/H1 ok на `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/` | SERP intent и CTR baseline улучшены | Следующий copy-слой вести как контентную SEO-задачу, не технический gap |
| SEO-006 | closed | P2 | Full Feature | Social preview | Twitter Card, OG dimensions/type, page-specific OG images и default `og-default.jpg` 1200x630 задеплоены | Production `npm run seo:smoke`: `og:image:width/height/type` и Twitter meta ok; helper default image `og-default.jpg` контролируется `npm run seo:check` | Внешние preview получают large-image fallback и page-specific изображения | При замене visual assets сохранять 1200x630 fallback |
| SEO-007 | closed | P2 | Fast Fix | Sitemap governance | Static и dynamic sitemap governance закрыты гибридной моделью: repo-owned root `sitemap.xml`, Bitrix-generated `sitemap-basic-files.xml`, custom `/offer/sitemap.php`; sitemap/robots/canonical inventory, HTTPS loc, fresh lastmod, JSON endpoint noindex headers, forbidden locs и unique dynamic offer locs проверяются автоматикой | `tools/seo-check.mjs` проверяет root index, legacy `sitemap-files.xml` reference, production static sitemap coverage, запрет `/404.php`/`/bitrix/`/`/local/` и uniqueness `/offer/sitemap.php`; `deploy.yml` больше не синхронизирует legacy `sitemap-files.xml` и удаляет его на сервере | Рассинхрон sitemap/canonical/robots, возврат legacy sitemap и попадание 404/system URL теперь ловятся до закрытия deploy smoke | Поддерживать `npm run seo:check`/`seo:check:prod`; в Bitrix sitemap держать выключенным robots auto-rule и не включать `/404.php` |
| SEO-008 | closed | P2 | Security / Integration | Service endpoints indexing | JSON endpoints отдают `X-Robots-Tag: noindex, nofollow` | Production `npm run seo:check:prod` до dynamic sitemap guard: service endpoint header checks passed; `curl -I /local/api/services.php`, `/local/rest/health_config.php`: `x-robots-tag: noindex, nofollow` | JSON endpoints не должны попадать в индекс как thin/service pages | Держать `tacticum_rest_send_noindex_header()` обязательным для новых JSON endpoints |
| SEO-009 | accepted | P3 | Full Feature | Internal linking | Money pages не вынесены отдельными top-level пунктами, потому что это перегружает header; они закреплены как дочерние пункты `Услуги` через `services/.left.menu.php`, footer menu и блок `Наши услуги` | `.top.menu.php` держит 4 top-level пункта; `services/.left.menu.php` и `.bottom.menu.php` содержат `/price/`, `/offer/`, `/calculator/`, `/aiagents/`; блок `Наши услуги` содержит карточку `/offer/` `Расчет проекта`; `tools/seo-check.mjs` блокирует выпадение этих URL из top/footer/services structures; `visual-smoke` с `TACTICUM_EXPECT_SEO_HEAD=1` проверяет rendered nav links | UX сохраняет короткий header, SEO получает sitewide DOM links через services dropdown/footer/content; `/offer/` добавлен как `Расчет проекта`, потому что это коммерческая landing-страница, а не системный раздел | После deploy проверить rendered header/footer/menu links в `visual:smoke` manifest; не расширять top-level без отдельного UX-решения |

## Non-Gaps / Already Covered

- Базовые `description`, canonical и OpenGraph meta добавлены на публичные страницы.
- На основных публичных URL rendered H1 count равен 1.
- `robots.txt` указывает HTTPS sitemap, не блокирует CSS/JS render resources и использует Yandex `Clean-param` для tracking/cache-параметров.
- `sitemap-basic-files.xml` должен покрывать текущий набор публичных разделов и генерируется штатным Bitrix sitemap.
- Динамический `/offer/sitemap.php` дедуплицирует offer canonical URL и проверяется `seo:check:prod`.
- Alt baseline на проверенных production страницах не выявил пропущенных `alt`.

## Product-First Structured Data Update

01.06.2026 product pages `/platform/`, `/agents/`, `/dev/`, `/forum/` получили минимальную page-specific `SoftwareApplication` schema через `tacticum_apply_seo_defaults(...)`. Follow-up hardening перевёл product structured data на `tacticum_product_page_schema(...)`: тот же `$tacticumProductPage`, который рендерит HTML, теперь отдаёт `SoftwareApplication` and `FAQPage` для реально видимого static FAQ. Это не коммерческий proof: schema не содержит `Offer`, pricing, reviews, ratings, customer logos or benchmark fields. `npm run seo:check` фиксирует presence product schema, ordering data -> schema -> render and forbids risky commercial schema fields; rendered `visual-smoke` with `TACTICUM_EXPECT_SEO_HEAD=1` additionally validates deployed product schema and records `productSchemaSummary` in the manifest.

## Proposed Closure Plan

### Phase 1 - Indexability And 404

Owner: SEO + Frontend/Backend  
Priority: P1

Acceptance criteria:

- `/offer/<valid-code>/` и `/offer/<valid-code>/?clear_cache=Y` отдают 200, indexable head и self-canonical;
- `/offer/?ID=<valid>` отдаёт 301 на `/offer/<valid-code>/`;
- `/offer/?ID=<invalid>` и `/offer/<invalid-code>/` отдают 404 и `noindex`;
- title, description, keywords и H1 валидного offer detail берутся из свойств элемента инфоблока;
- `/offer/` остаётся индексируемым landing-входом в offer flow;
- `sitemap.xml` содержит `https://tacticum.ru/sitemap-basic-files.xml` и `https://tacticum.ru/offer/sitemap.php`, а offer sitemap отдаёт активные `/offer/<code>/`;
- 404 имеет один title, один H1, `noindex`, понятные ссылки на ключевые разделы;
- `npm run seo:smoke` и manual curl checks проходят.

### Phase 2 - SEO Helper And Structured Data

Owner: SEO + Frontend  
Priority: P2

Acceptance criteria:

- SEO helper поддерживает `robots`, Twitter Card, OG image dimensions/type и JSON-LD;
- глобально добавлены `Organization`/`LocalBusiness` и `WebSite`;
- на публичных страницах добавлены `BreadcrumbList`;
- на `/services/` и `/aiagents/` добавлены `Service`;
- на `/contacts/` добавлен `ContactPage`;
- FAQ JSON-LD добавлен только там, где FAQ реально рендерится на странице.

### Phase 3 - Metadata And Social Preview

Owner: SEO + Designer + Frontend  
Priority: P2

Acceptance criteria:

- утверждена таблица title/description/H1/canonical для всех публичных URL;
- money pages получили более точные title и description;
- создан `og-default.jpg` 1200x630;
- для `/services/`, `/price/`, `/calculator/`, `/aiagents/` заданы page-specific OG images или явно принято использовать default;
- rendered smoke подтверждает отсутствие дублей meta.

### Phase 4 - Sitemap, Robots And CI Guards

Owner: SEO + QA/DevOps  
Priority: P2

Acceptance criteria:

- добавлен `npm run seo:check`;
- checker сверяет sitemap URL, canonical URL, public URL inventory, HTTPS и `lastmod`;
- checker проверяет `X-Robots-Tag` на JSON endpoints;
- sitemap `lastmod` обновлён после релиза;
- post-deploy smoke содержит valid offer canonical/legacy redirect, valid offer with `clear_cache=Y`, `/offer/?ID=<invalid>`, 404 и service endpoint noindex checks.

## Verification Commands

```bash
npm run seo:smoke
npm run seo:check
npm run seo:check:prod
npm run browser:smoke:prod
curl -I https://tacticum.ru/sitemap.xml
curl -I https://tacticum.ru/sitemap-basic-files.xml
curl -I https://tacticum.ru/no-such-page-seo-audit
curl -I https://tacticum.ru/offer/sitemap.php
curl -I 'https://tacticum.ru/offer/?ID=<valid-id>'
curl -I 'https://tacticum.ru/offer/<valid-code>/'
curl -I 'https://tacticum.ru/offer/?ID=999999'
curl -I https://tacticum.ru/local/api/services.php
curl -I https://tacticum.ru/local/rest/health_config.php
```

## Definition Of Done

- Gaps `SEO-001` - `SEO-008` закрыты production evidence.
- `SEO-009` переведён в `accepted` с rationale: money pages остаются в dropdown `Услуги`, guard закреплён в `npm run seo:check`.
- `docs/workflow/current-state.md`, `docs/workflow/gap-analysis.md` и `docs/workflow/post-deploy-smoke.md` обновлены.
- Production `npm run seo:smoke` проходит.
- Release sign-off содержит rendered SEO head evidence для затронутых URL.
