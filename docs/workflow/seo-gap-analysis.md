# SEO Gap Analysis — tacticum.ru

Дата аудита: 24.05.2026  
Scope: публичные URL сайта, rendered head, sitemap/robots, 404, `/offer/`, служебные JSON endpoints, social preview и structured data.

## Executive Summary

SEO baseline сайта технически рабочий: production `npm run seo:smoke` 24.05.2026 прошёл по основным публичным URL, rendered head содержит один `title`, одну `description`, один HTTPS canonical, обязательные OpenGraph meta и один H1.

Evidence:

- production manifest: `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T06-56-34-214Z/manifest.json`;
- `sitemap.xml` указывает на HTTPS `sitemap-files.xml`; offer detail sitemap добавляется как `https://tacticum.ru/offer/sitemap.php`;
- `robots.txt` указывает HTTPS sitemap;
- sitemap покрывает текущие публичные URL: `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`.

Оставшиеся SEO gaps не про отсутствие базовых meta, а про качество индексации, soft-404, служебные URL, structured data, social previews и автоматические guards.

## Confirmed Baseline

| Area | Status | Evidence |
|---|---|---|
| Rendered title/description/canonical/OG/H1 | ok | `npm run seo:smoke`, manifest выше |
| Sitemap XML validity | ok | `.github/workflows/sitemap.yml`, `sitemap.xml`, `sitemap-files.xml`, `npm run seo:check`; dynamic offer sitemap требует post-deploy curl |
| Robots sitemap pointer | ok | `robots.txt` содержит `Sitemap: https://tacticum.ru/sitemap.xml` |
| Public URL coverage in sitemap | ok | sitemap содержит все 9 текущих публичных разделов |
| Image alt baseline | ok | production scan по `/`, `/about/`, `/price/`: missing `alt=0` |

## Open SEO Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| SEO-001 | in_progress | P1 | Full Feature | `/offer/` indexability | Валидные offer detail должны быть индексируемыми landing example pages на ЧПУ, но production `/offer/?ID=*` всё ещё отдаёт параметрический canonical, а несуществующие ID отдают `200` soft-404 | Production `https://tacticum.ru/offer/?ID=1`: `status=200`, `canonical=https://tacticum.ru/offer/?ID=1`, `h1=[]`; source `offer/index.php` строил canonical по любому положительному `ID` | Риск soft-404 в индексе, дублей параметрических URL и слабых landing pages для расчётов | Генерировать `CODE` из `slug.title` + timestamp; отдавать `/offer/<code>/` с self-canonical и indexable head; `/offer/?ID=<valid>` редиректить 301 на ЧПУ; invalid ID/code отдавать 404/noindex; включить активные offer details в `/offer/sitemap.php` |
| SEO-002 | in_progress | P1 | Fast Fix | 404 | Production 404-страница отдаёт статус 404, но title `Карта сайта`, H1 отсутствует, canonical/noindex отсутствуют; local fix заменяет карту сайта явным 404 template | `404.php` подключал `bitrix:main.map` с `SET_TITLE => Y`; production `/no-such-page-seo-audit`: `status=404`, `title=Карта сайта`, `h1=[]` | Поисковики получают слабый 404-сигнал, пользователь видит техническую карту вместо нормальной страницы ошибки | После deploy проверить: статус 404, title `Страница не найдена - Тактикум`, один H1, `meta robots` и `X-Robots-Tag: noindex,nofollow`, ссылки на ключевые разделы |
| SEO-003 | in_progress | P2 | Full Feature | SEO helper | Production helper покрывает только canonical и OpenGraph; local helper уже поддерживает `robots`, Twitter Card, OG image dimensions/type, JSON-LD и page-specific schema | `local/php_interface/init.php` на production содержит только canonical + `og:*`; `twitter:*` и `application/ld+json` отсутствуют до deploy | Новые SEO-правки будут копироваться вручную по страницам, а социальные preview и rich results останутся неполными | После deploy проверить rendered head: Twitter meta, `og:image:*`, JSON-LD graph без дублей; новые страницы должны использовать options helper |
| SEO-004 | in_progress | P2 | Full Feature | Structured data | Production страницы без JSON-LD; local helper добавляет `Organization`, `WebSite`, `BreadcrumbList`, а страницы передают `Service`, `WebApplication`, `ContactPage`, `AboutPage`, `WebPage` и `FAQPage` там, где FAQ рендерится | Production scan: на `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/` `jsonLd=0` | Недобор семантики для организации, услуг, контактов, хлебных крошек и FAQ | После deploy проверить `jsonLd>0` на публичных URL и валидность schema graph |
| SEO-005 | in_progress | P2 | Full Feature | Metadata quality | Production title части money pages слишком общие; local fix уточняет `/services/`, `/price/`, `/contacts/` и блокирует перезапись `/policies/` компонентом | Manifest 24.05.2026: `/services/ title(17)`, `/price/ title(17)`, `/contacts/ title(19)`, `/policies/ title=Политика конфиденциальности персональных данных` | Технически валидно, но слабее для SERP intent и CTR | После deploy проверить title/description/H1/canonical на 9 URL; доработать оставшиеся title по SEO copy table при необходимости |
| SEO-006 | in_progress | P2 | Full Feature | Social preview | Production страницы ещё ждут deploy, но local fix добавляет Twitter Card, OG dimensions/type, page-specific OG images для `/services/`, `/price/`, `/calculator/`, `/aiagents/`, `/about/` и default `og-default.jpg` 1200x630 | `local/templates/tacticum/images/og-default.jpg` создан из hero asset с размером 1200x630; `tacticum_apply_seo_defaults(...)` использует его как default; `tools/seo-check.mjs` контролирует default image и размеры | Внешние preview получают корректное large-image соотношение и fallback, page-specific previews остаются точнее для money pages | После deploy проверить `og:image`, Twitter Card и image dimensions в rendered manifest |
| SEO-007 | in_progress | P2 | Fast Fix | Sitemap governance | Production sitemap ещё ждёт deploy, но local fix добавляет `npm run seo:check`, свежие `lastmod`, canonical/static URL inventory guard и CI/deploy checks | `tools/seo-check.mjs` сверяет sitemap index, static sitemap, public page canonical paths, HTTPS loc, one `lastmod` per `loc`, freshness от `2026-05-24`, robots sitemap pointer; `.github/workflows/sitemap.yml`, `pr-check.yml` и `deploy.yml` запускают checker | Поисковикам лучше видны изменения, а рассинхрон public URL/canonical/sitemap теперь ловится до deploy | После deploy выполнить `npm run seo:check:prod`: проверить production sitemap и `X-Robots-Tag: noindex, nofollow` на JSON endpoints |
| SEO-008 | in_progress | P2 | Security / Integration | Service endpoints indexing | Production служебные JSON endpoints отдают 200 без `X-Robots-Tag: noindex`; local fix добавляет общий `X-Robots-Tag: noindex, nofollow` для JSON endpoints | Production `/local/api/services.php`, `/local/api/cases.php`, `/local/rest/health_config.php`: `x-robots` пустой | JSON endpoints могут попасть в индекс как thin/service pages | После deploy проверить `X-Robots-Tag: noindex, nofollow` на `/local/api/*` и JSON `/local/rest/*`; держать `tacticum_rest_send_noindex_header()` обязательным для новых JSON endpoints |
| SEO-009 | accepted | P3 | Full Feature | Internal linking | Money pages не вынесены отдельными top-level пунктами, потому что это перегружает header; они закреплены как дочерние пункты `Услуги` через `services/.top.menu_ext.php` и доступны из footer/контента | `.top.menu.php` держит 4 top-level пункта; `services/.top.menu_ext.php` содержит `/price/`, `/calculator/`, `/aiagents/`; `tools/seo-check.mjs` блокирует выпадение этих URL из top menu structure; `visual-smoke` с `TACTICUM_EXPECT_SEO_HEAD=1` проверяет rendered nav links | UX сохраняет короткий header, SEO получает sitewide DOM links через services dropdown; риск принят как navigation decision | После deploy проверить rendered header/menu links в `visual:smoke` manifest; не расширять top-level без отдельного UX-решения |

## Non-Gaps / Already Covered

- Базовые `description`, canonical и OpenGraph meta добавлены на публичные страницы.
- На основных публичных URL rendered H1 count равен 1.
- `robots.txt` указывает HTTPS sitemap.
- `sitemap-files.xml` покрывает текущий набор публичных разделов.
- Alt baseline на проверенных production страницах не выявил пропущенных `alt`.

## Proposed Closure Plan

### Phase 1 - Indexability And 404

Owner: SEO + Frontend/Backend  
Priority: P1

Acceptance criteria:

- `/offer/<valid-code>/` отдаёт 200, indexable head и self-canonical;
- `/offer/?ID=<valid>` отдаёт 301 на `/offer/<valid-code>/`;
- `/offer/?ID=<invalid>` и `/offer/<invalid-code>/` отдают 404 и `noindex`;
- title, description, keywords и H1 валидного offer detail берутся из свойств элемента инфоблока;
- `/offer/` остаётся индексируемым landing-входом в offer flow;
- `sitemap.xml` содержит `https://tacticum.ru/offer/sitemap.php`, а offer sitemap отдаёт активные `/offer/<code>/`;
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
- post-deploy smoke содержит valid offer canonical/legacy redirect, `/offer/?ID=<invalid>`, 404 и service endpoint noindex checks.

## Verification Commands

```bash
npm run seo:smoke
npm run seo:check
npm run seo:check:prod
npm run browser:smoke:prod
curl -I https://tacticum.ru/no-such-page-seo-audit
curl -I https://tacticum.ru/offer/sitemap.php
curl -I 'https://tacticum.ru/offer/?ID=<valid-id>'
curl -I 'https://tacticum.ru/offer/<valid-code>/'
curl -I 'https://tacticum.ru/offer/?ID=999999'
curl -I https://tacticum.ru/local/api/services.php
curl -I https://tacticum.ru/local/rest/health_config.php
```

## Definition Of Done

- Все gaps `SEO-001` - `SEO-008` закрыты кодом, контентом или автоматикой.
- `SEO-009` переведён в `accepted` с rationale: money pages остаются в dropdown `Услуги`, guard закреплён в `npm run seo:check`.
- `docs/workflow/current-state.md`, `docs/workflow/gap-analysis.md` и `docs/workflow/post-deploy-smoke.md` обновлены.
- Production `npm run seo:smoke` проходит.
- Release sign-off содержит rendered SEO head evidence для затронутых URL.
