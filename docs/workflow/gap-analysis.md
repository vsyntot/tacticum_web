# Gap Analysis — tacticum.ru

Дата аудита: 20.05.2026
Дата последнего обновления: 24.05.2026

Статусы:

- `open` — gap подтверждён и требует задачи;
- `in-progress` — уже есть активная работа;
- `accepted` — осознанно принимаем риск;
- `closed` — закрыто, оставить ссылку на PR/Issue.

Приоритеты:

- `P0` — production/security blocker;
- `P1` — важно для ближайшего спринта;
- `P2` — плановый backlog;
- `P3` — nice-to-have.

## Executive Summary

На 22.05.2026 повторный challenge `/local` и публичной части выявил новый набор operational gaps, связанных с browser zero-error gate, action-smoke и остаточными frontend/backend ownership debt.

Быстрые P1-правки уже внесены на уровне кода:

1. `visual:smoke` расширен до browser runtime checks.
2. Фоновый Telegram resolver переведён в lazy/on-click режим.
3. POST endpoints приведены к единому method/body bootstrap.
4. Файловое runtime-логирование из `/local` и публичных скриптов удалено; endpoints сохраняют прежние response contracts без записи payload/response в лог.
5. Light chat и `/price/` component переведены на явные `data-*` контракты вместо presentation/text selectors.
6. Добавлен `browser:smoke` / `TACTICUM_VISUAL_ACTIONS=1` для non-network UI action checks.
7. Yandex Maps вынесен в explicit page asset, Metrika noscript pixel лишён inline style.
8. Sale upstream ownership централизован в shared `tacticum_rest_submit_chat_agent_sale(...)`.
9. `/price/` staff order расширен до multi-staff сценария: состав заявки, segmented-выбор уровня, стабильный порядок уровней, количество по ролям, `workers_json` и backend validation.
10. Light chat на `/price/` и `/calculator/` получил ограничение высоты и внутреннюю прокрутку сообщений.
11. Публичный вывод инфоблоков переведён на общий decode/sanitize helper, чтобы не показывать служебные HTML entities.
12. `/price/` staff order получил persistent summary, быстрые пресеты команды и расчёт ориентировочного месячного бюджета.
13. Post-deploy smoke стал частью deploy workflow: `health_config`, visual smoke и browser action smoke выполняются после очистки Bitrix cache.
14. Yandex.Metrika вынесена из inline script в централизованный template asset `js/metrika.js`.
15. Legacy sale aliases получили `Deprecation`/`Sunset` headers без изменения JSON contract.

Обязательные gates после последующих выкладок:

1. Для GitHub deploy эти проверки выполняются автоматически в `deploy.yml` после cache clear.
2. При локальной/ручной выкладке выполнить `npm run visual:smoke:prod`, `npm run browser:smoke:prod` и для `/price/` `npm run browser:smoke:price`.
3. Реальные success-flow форм/чата/заявок проверять на staging или вручную с контролем создаваемых лидов.

Проверка 23.05.2026 против production показала: initial-load smoke чистый (`pageErrors=0`, `consoleErrors=0`, runtime/network blockers отсутствуют), но `browser:smoke` падал на `/price/`, потому что production HTML содержит legacy-разметку `.filter-tab`, `.pricing-card`, `.order-specialist-btn` без `data-price-*` и без `#specialistOrderModal` / `#specialistOrderForm`, а deployed JS уже ожидал новый component contract. `news.list/price/script.js` доработан как mixed-rollout fallback: поддерживает legacy selectors и создаёт fallback-модалку только если template её не отдал. Инъекционный smoke исправленного JS поверх текущего production HTML прошёл.

Повторный обычный `npm run browser:smoke` без CSS/JS injection 23.05.2026 прошёл для 18 desktop/mobile проверок: `bad=0`, browser runtime/network errors не выявлены. TG-019 закрыт.

Закрытые, но требующие постоянного контроля области:

- HTTPS/config discipline для AI endpoints через deploy health smoke.
- Iblock registry вместо hardcoded IDs.
- Unified AI chat / lead form contracts.
- Post-deploy smoke для форм, AI-чата, API cache и SEO meta.
- Explicit component/data contracts вместо URL/text/inline handlers.

## SEO Deep-Dive Gap Analysis — 24.05.2026

Детальный SEO challenge вынесен в `docs/workflow/seo-gap-analysis.md`. Post-deploy `npm run seo:smoke` 24.05.2026 прошёл на 9 публичных URL в desktop/mobile; повторный `npm run seo:check:prod` после deploy dedupe fix прошёл. `SEO-001` - `SEO-008` закрыты, `SEO-009` принят как UX/navigation decision.

| ID | Status | Priority | Area | Summary |
|---|---|---|---|---|
| SEO-001 | closed | P1 | `/offer/` indexability | Валидный `/offer/<code>/` отдаёт 200/self-canonical/indexable head, invalid ID/code — 404/noindex; offer sitemap отдаёт активные ЧПУ |
| SEO-002 | closed | P1 | 404 | Production 404 отдаёт HTTP 404, `X-Robots-Tag: noindex,nofollow`, корректный title/H1 |
| SEO-003 | closed | P2 | SEO helper | Production rendered smoke подтвердил robots/Twitter Card/OG dimensions/JSON-LD/page-specific schema options |
| SEO-004 | closed | P2 | Structured data | Production rendered smoke подтвердил JSON-LD graph на публичных URL |
| SEO-005 | closed | P2 | Metadata quality | Production rendered smoke подтвердил title/description/canonical/H1 на 9 URL |
| SEO-006 | closed | P2 | Social preview | Production rendered smoke подтвердил Twitter Card, OG image dimensions/type, page-specific images и `og-default.jpg` fallback |
| SEO-007 | closed | P2 | Sitemap governance | Static и dynamic sitemap governance закрыты гибридной моделью: repo-owned root `sitemap.xml`, Bitrix-generated `sitemap-basic-files.xml`, custom `/offer/sitemap.php`; `seo:check:prod` проверяет sitemap/robots/canonical inventory/HTTPS/lastmod, forbidden locs, JSON endpoint noindex headers и unique locs в `/offer/sitemap.php` |
| SEO-008 | closed | P2 | Service endpoint indexing | Production checks подтвердили `X-Robots-Tag: noindex, nofollow` на JSON endpoints |
| SEO-009 | accepted | P3 | Internal linking | Money pages остаются дочерними пунктами `Услуги` через `services/.left.menu.php`, footer menu и блок `Наши услуги`, чтобы не перегружать header; `npm run seo:check` контролирует `/price/`, `/offer/`, `/calculator/`, `/aiagents/` в menu/content structures |

## Follow-Up Gap Closure — Sprint 08

На 23.05.2026 оставшиеся follow-up gaps закрыты кодом, автоматикой или formal sign-off gate:

| ID | Status | Area | Closure |
|---|---|---|---|
| FUG-001 | closed | CSS retirement | Старый generated Tailwind block удалён из `template_styles.css`; добавлен CSS replacement smoke и PR guard против возврата Tailwind layer block |
| FUG-002 | closed as gate | Real success-flow | `release-signoff-gates.md`, `post-deploy-smoke.md` и PR template требуют staging/manual evidence для форм, чата, prefill, staff-order |
| FUG-003 | closed as gate | Metrika goals | Release sign-off требует подтверждения goals в Yandex.Metrika при изменении analytics/form/chat |
| FUG-004 | closed | CSP readiness | `header.php` отправляет `Content-Security-Policy-Report-Only`; ADR-005 обновлён под report-only rollout |
| FUG-005 | closed | Legacy sale lifecycle | Добавлены `tools/legacy-sale-sunset-check.mjs`, `npm run sale:sunset:check`; после `30.09.2026` CI потребует решение |
| FUG-006 | closed | Config sync | Добавлены `ai.endpoint_paths.*`, `tools/config-contract-check.mjs`, `npm run config:check`; PR template требует owner для server config sync |
| FUG-007 | closed | REST response bodies | `rest-response-contract-decision.md` фиксирует сохранение доменных response shapes и правила для новых endpoints |
| FUG-008 | closed | Rich workers upstream | `/price/` staff-order использует config-driven `ai.endpoint_paths.staff_sale`; ADR-006 принят |

## Overall Gap Closure — Sprint 09

На 23.05.2026 residual overall gaps закрыты единым Sprint 09 artifact: `docs/workflow/sprints/2026-05-23-sprint-09-overall-gap-closure.md`.

| ID | Status | Area | Closure |
|---|---|---|---|
| OGC-001 | closed as gate | Release sign-off evidence | `release-signoff-gates.md` расширен rendered SEO, `/price` preset, legacy sunset и staff upstream gates; `release:signoff:check` валидирует release metadata, unknown gates, JSON evidence, структуру manual gates и placeholder/PII-like значения; `release:signoff:summary` показывает PM/QA статус draft и pending reasons; `release:signoff:self-test` закрепляет негативные кейсы checker; `manual-release-gates-runbook.md` и `release-signoff-manual-evidence.template.json` фиксируют безопасное закрытие ручных gates |
| OGC-002 | closed | `/price/` team presets | `browser:smoke:price` проверяет persistent summary, modal open, `workers_json` и monthly budget; UX summary доработан |
| OGC-003 | closed | Rendered SEO head | `visual-smoke` получил `seoHead`/`seoErrors`; deploy включает `TACTICUM_EXPECT_SEO_HEAD=1`; `/calculator/` и `/offer/` получили H1 baseline |
| OGC-004 | closed | CSS retirement | Active CSS перенесён из `template_styles.css` в `styles/global.css`; `template_styles.css` стал comment-only shim; `template-styles:check` блокирует возврат правил |
| OGC-005 | closed | CSP enforcing runway | `header.php` поддерживает `security.csp_mode=report-only|enforce`; `health_config` валидирует security scope; default остаётся `report-only`; ADR-005 фиксирует rollout/rollback |
| OGC-006 | closed as process decision | Rich workers upstream | `ai.endpoint_paths.staff_sale` остаётся единственным config switch; sale workstream фиксирует compatibility matrix |
| OGC-007 | closed as process decision | Legacy sale aliases sunset | Sale workstream фиксирует inventory до `30.06.2026`, migration до `31.08.2026`, final mode до `30.09.2026` |
| OGC-008 | closed | Local PHP CLI gap | `npm run dev:preflight` запускает PHP lint при PHP 8.4+ или фиксирует degraded local state с GitHub `php-lint` fallback |

## Sprint 10 — Release Evidence, Browser Errors And CSS/JS E2E

На 24.05.2026 открытых code-level `open` / `in-progress` gaps нет. Оставшийся хвост упакован в Sprint 10: `docs/workflow/sprints/2026-05-24-sprint-10-release-evidence-browser-e2e.md`.

| ID | Status | Area | Closure Target |
|---|---|---|---|
| S10-001 | external handoff | Release sign-off closure | Draft-check/summary проходят; strict closure ждёт external gates из `sprint-10-external-gates-handoff-2026-05-24.md` |
| S10-002 | external handoff | Manual success-flow | Staging или controlled production evidence для форм, modal, AI chat, prefill и staff-order без PII |
| S10-003 | external handoff | Staff-sale upstream | Подтверждение rich staff payload в upstream/CRM или controlled fallback |
| S10-004 | external handoff | Metrika goals | Подтверждение affected goals/events в Яндекс.Метрике без PII в параметрах |
| S10-005 | external handoff | Bitrix admin smoke | Unauthenticated admin surface precheck прошёл; authenticated admin + toolbar smoke ждёт QA/Admin |
| S10-006 | done | Browser zero-error challenge | `npm run e2e:css-js:prod` и `npm run e2e:css-js:local` прошли; CDP readiness race в `visual-smoke` исправлен |
| S10-007 | done | CSS/JS E2E readiness | Добавлен `css-js-e2e-readiness` release gate; draft sign-off содержит passed evidence по production и CSS-local manifests |
| S10-008 | external handoff | Legacy sale aliases inventory | `legacy-sale-alias-consumer-inventory.md` создан; repo scan не нашёл first-party callers, external access logs/CRM inventory до `30.06.2026`, migration plan до `31.08.2026`, final mode runway до `30.09.2026` |
| S10-009 | done | Rich workers upstream decision | `rich-workers-upstream-readiness-2026-05-24.md` фиксирует: compatible upstream workers contract в repo/docs отсутствует, `staff_sale` остаётся `/tacticum/v1/chat_agent/sale`; будущий switch только через Security / Integration scope |
| S10-010 | done | CSP report-only baseline | `csp-report-only-baseline-2026-05-24.md` зафиксировал report-only header, отсутствие enforcing CSP и чистый `/contacts/` rendered smoke; goal-level Метрика остаётся S10-004 |
| S10-011 | done | SEO-009 revalidation | `npm run seo:check`, `npm run seo:check:prod` и `npm run seo:smoke` прошли; `/price/`, `/offer/`, `/calculator/`, `/aiagents/` остаются в rendered navigation, accepted risk не пересматривается без UX scope |
| S10-012 | done | Offer detail clear-cache routing | Старое `urlrewrite.php` rule не матчило `/offer/<code>/?clear_cache=Y`; после fix и deploy production URL отдаёт 200/self-canonical, `seo:check:prod` проходит |

## Sprint 11 — Public Page Componentization

На 24.05.2026 componentization backlog после challenge структуры сайта закрыт единым Sprint 11 artifact: `docs/workflow/sprints/2026-05-24-sprint-11-componentization.md`.

| ID | Status | Area | Closure |
|---|---|---|---|
| S11-001 | done | CTA sections | `personal-offer` и `project-discussion` CTA для `/`, `/calculator/`, `/price/`, `/contacts/`, `/about/`, `/services/` переведены на `tacticum:lead.cta`; старые template includes удалены |
| S11-002 | done | FAQ sections | Повторяемые FAQ-вызовы на публичных страницах и offer detail переведены на wrapper `tacticum:faq.section` поверх `bitrix:news.list` template `faq` |
| S11-003 | done | Chat surfaces | Hero chat главной и light chat на `/calculator/`, `/price/` переведены на `tacticum:chat.surface` с сохранением `chat-agent.js` contracts |
| S11-004 | done | `/aiagents/` | `/aiagents/index.php` стал тонкой split-prolog page entry; основной render flow живёт в `tacticum:aiagents`, assets/body class задаются через page properties |
| S11-005 | done | Public page integration | Публичные страницы больше не держат повторяемую CTA/FAQ/light-chat разметку; page entries передают только параметры |
| S11-006 | done | Docs and guards | `current-state`, `gap-analysis`, sprint artifact и static checks обновлены под локальные компоненты |

## Sprint 11 Hardening — Bitrix Component Framework

На 24.05.2026 follow-up challenge Sprint 11 закрыт отдельным hardening backlog: `docs/workflow/sprints/2026-05-24-sprint-11-bitrix-component-hardening.md`.

| ID | Status | Area | Closure |
|---|---|---|---|
| S11H-001 | done | Component metadata | Все локальные компоненты `local/components/tacticum/*` имеют `.description.php`, `.parameters.php`, `component.php` |
| S11H-002 | done | Public content lists | Повторяемые `bitrix:news.list` вызовы в public pages заменены wrapper-компонентом `tacticum:content.list` |
| S11H-003 | done | Page properties | Public pages с page-specific assets используют split prolog + `SetPageProperty(...)`, а не `TACTICUM_*` globals |
| S11H-004 | done | FAQ section model | FAQ-вызовы используют semantic `SECTION_KEY`; code-first lookup и numeric fallback централизованы внутри `tacticum:faq.section` |
| S11H-005 | done | Bitrix param typo | Кириллическая опечатка `INCLUDE_IBLOCK_INТО_CHAIN` удалена и закреплена static guard |
| S11H-006 | done | Offer catalog service boundary | High-level логика `/offer/` catalog вынесена в `TacticumOfferCatalogService`; старые функции оставлены wrappers |
| S11H-007 | done | Guards and docs | `seo-check`, ADR-008, `current-state`, `gap-analysis` и sprint artifact обновлены |
| S11H-008 | done | Static detail pages | `/about/`, `/policies/` и `404.php` переведены на split prolog; `/policies/` использует `tacticum:content.detail`, а policy migration больше не привязана к hardcoded `ELEMENT_ID=515` |

## Sprint 12 — Final Site Hardening, UX And Release Closure

На 24.05.2026 оставшийся external release handoff и новый финальный hardening scope упакованы в Sprint 12: `docs/workflow/sprints/2026-05-24-sprint-12-final-site-hardening.md`.

| ID | Status | Area | Closure Target |
|---|---|---|---|
| S12-001 | external handoff | Release sign-off closure | Strict release sign-off после закрытия manual gates без PII evidence |
| S12-002 | external handoff | Manual success-flow | Staging/controlled production проверка форм, modal, AI chat, prefill и staff-order |
| S12-003 | external handoff | Staff-sale upstream | Подтверждение rich staff payload в upstream/CRM или controlled fallback |
| S12-004 | external handoff | Metrika goals | Подтверждение affected goals/events в Яндекс.Метрике без PII |
| S12-005 | external handoff | Bitrix admin smoke | Authenticated admin panel и public toolbar после deploy/cache refresh |
| S12-006 | external handoff | Legacy sale aliases inventory | External access logs/CRM inventory до `30.06.2026`, migration plan до `31.08.2026`, runway до `30.09.2026` |
| S12-007 | done | Top menu | `/services/` больше не подменяет root top menu: children живут в `services/.left.menu.php`, `/offer/` добавлен в dropdown/mobile/footer и блок `Наши услуги` как `Расчет проекта`, top/mobile menu используют `CHILD_MENU_TYPE=left`, `USE_EXT=N`, guard закреплён в `seo:check` |
| S12-008 | done | Code comments | Бессодержательные comments, commented dead markup/code и временные cleanup notes удалены из production PHP/JS/CSS scope; оставлены docs, compatibility и vendor/license comments |
| S12-009 | done | `/offer/` UX | `/offer/` list page получил больше воздуха, `bg-gray-50`, белые statistic/filter/cards, увеличенные gaps и mobile-safe метрики карточек |
| S12-010 | done | Bitrix component framework | Финальный challenge закреплён static guards: thin public entries, local component metadata, no direct `bitrix:*` page calls, menu architecture, chat page assets |
| S12-011 | done | JS/CSS optimization | `chat-agent.js` подключается только на chat pages через `tacticum_page_assets=chat`; CSS rebuild выполнен; неиспользуемые Google Fonts/Readdy origins удалены |
| S12-012 | done | Mobile/adaptive | `/offer/` mobile layout доработан; header/menu breakpoint переведён на `lg`, mobile menu стал scroll-safe для landscape, footer grid расширяет длинные контакты до `xl`; browser/visual smoke остаётся post-deploy gate для всех публичных URL |
| S12-013 | done | Page speed | Убраны внешние font/image origins, remote offer detail background, лишняя загрузка chat JS на non-chat pages; non-hero images получили lazy/async |

Repository closure refresh 25.05.2026: дополнительных repo/code-level gaps не найдено. Повторно прошли `npm run seo:check`, `npm run css:check`, `npm run template-styles:check`, `npm run config:check`, `npm run sale:sunset:check`, `npm run seo:check:prod`, `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json`, `npm run release:signoff:summary -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` и `npm run release:signoff:self-test`. `npm run dev:preflight` зафиксировал локальное degraded-состояние без PHP CLI; GitHub PHP 8.4 lint остаётся fallback. Production `visual:smoke:prod` / `browser:smoke:prod` для текущего menu/component пакета остаются post-deploy/cache gate.

Product layer MVP 01.06.2026: локально добавлены product pages `/platform/`, `/agents/`, `/dev/`, `/forum/`, общий renderer `local/php_interface/include/product_page.php`, product-first navigation и form taxonomy для `platform-cta`, `agents-cta`, `dev-cta`, `forum-cta`. Срез закрывает первый безопасный слой по product vision gaps `PV-001`, `PV-003`, `PV-004`, `PV-005`, `PV-009`, `PV-012`, `PV-013`, `PV-015` без изменения REST/upstream, `/offer/`, `/price/`, `/calculator/` и `/aiagents/` flows. Локальные checks: `bitrix:check`, `template-styles:check`, `seo:check`, `css:check`, `js:check` прошли; PHP lint не запускался из-за отсутствия локального PHP CLI.

Homepage ecosystem MVP 01.06.2026: главная страница получила ecosystem positioning, hero links на `Platform / Agents / Dev / Forum`, карту `Platform core -> Agents/Dev/Forum`, обновленный chat intro и product-aware контекст `home-cta`. Текущий commercial layer сохранён отдельным блоком и продолжает вести в `/offer/`, `/services/`, `/price/`, `/aiagents/`. Срез продвигает `PV-001`, `PV-002`, `PV-003`, `PV-009`, `PV-011`, `PV-012`, `PV-013`; визуальный/post-deploy smoke остаётся release gate.

Services delivery layer MVP 01.06.2026: `/services/` получил блок связи внедрения с `Platform / Agents / Dev / Forum` и уточненный `services-cta` context (`lead_product=ecosystem`, `lead_scenario=product-delivery`). Срез продвигает Phase C по reframe existing pages и `PV-020`, сохраняя `/offer/`, `/price/`, `/calculator/`, content list, FAQ и форму без REST/upstream изменений.

Estimate/proof product context MVP 01.06.2026: `/calculator/` и `/offer/` связаны с product-first моделью без изменения risky contracts. Calculator получил product-aware estimate paths и `calculator-cta` context (`lead_product=ecosystem`, `lead_scenario=product-estimate`). Offer catalog/detail получили product relation blocks and offer detail CTA context `lead_product=ecosystem`. Срез продвигает `PV-007` proof, `PV-012` lead qualification и Phase C/D migration map; visual/post-deploy smoke остаётся release gate.

Price product team context MVP 01.06.2026: `/price/` получил product workstreams для `Platform / Agents / Dev / Forum` и `price-cta` context (`lead_product=ecosystem`, `lead_scenario=product-team`) без изменения `price-specialist`, `workers_json`, team presets, `news.list/price/script.js` and staff upstream. Срез продвигает `PV-020` delivery/team linkage while preserving the highest-risk price flow.

AIAgents compatibility bridge 01.06.2026: `/aiagents/` сохранён как compatibility/money URL and не редиректится. Добавлен bridge к `/agents/`, а `aiagents-inline` получает `lead_product=agents`. Canonical/redirect decision для `/aiagents/` vs `/agents/` остаётся открытым SEO decision, но пользовательский путь теперь связан с product-first моделью.

## Sprint 13 — Bitrix Framework Hardening

На 25.05.2026 gaps по результатам Bitrix framework challenge закрыты единым Sprint 13 artifact: `docs/workflow/sprints/2026-05-25-sprint-13-bitrix-framework-hardening.md`; архитектурный паттерн зафиксирован в `docs/adr/ADR-009-bitrix-framework-hardening.md`.

| ID | Status | Area | Closure |
|---|---|---|---|
| S13-001 | done | `init.php` bootstrap | `init.php` стал тонким include/registration bootstrap; helpers вынесены в `site_helpers.php`, `seo_helpers.php`, `calcrequests_rest.php` |
| S13-002 | done | `/offer/` cache/service | `/offer/` catalog получил лёгкий `offer_catalog_cache.php`, `TacticumOfferCatalogCache`, `TacticumOfferCatalogRepository`, managed tag/cache dir и очистку после add/update/delete/property-update offer events |
| S13-003 | done | Component namespace | Локальные component.php больше не объявляют global helper functions; параметры нормализуются через `TacticumComponentParams` |
| S13-004 | done | FAQ section fallback | Numeric fallback ID вынесены в config example `content.faq_section_fallback_ids`; code-first lookup остаётся основным |
| S13-005 | done | SEO robots | 404/offer not-found используют общий `tacticum_add_robots_meta(...)` |
| S13-006 | done | Footer modal | Footer contact modal вынесен в `tacticum:contact.modal` |
| S13-007 | done | Vendor demos | Public Remixicon demo HTML удалены и закреплены `template-styles:check` |
| S13-008 | done | Guards/docs | Добавлен `npm run bitrix:check`, подключён в PR/deploy workflow; docs обновлены |

## Product Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| PG-001 | closed | P1 | Full Feature | AI chat / calculator | AI-chat пользовательский сценарий и REST contract унифицированы для production surfaces | `local/templates/tacticum/js/chat-agent.js`, `header.php`, `docs/workflow/chat-api-contract.md`; inline chat и legacy `chat.js` удалены | Поведение и ошибки чата теперь чинятся в одном модуле, API edge cases зафиксированы для QA | Поддерживать chat API contract при изменениях upstream |
| PG-002 | closed | P1 | Full Feature | Lead flow | Контракт лид-форм зафиксирован для QA и будущих правок | `docs/workflow/lead-form-contract.md`, `forms.js`, `/local/rest/tacticum_form.php` | Снижен риск расхождения форм, есть form_id taxonomy и smoke cases | Поддерживать Lead Form Contract и analytics taxonomy при новых формах |
| PG-003 | closed | P1 | Security / Integration | Offer flow | AI calculator → offer → prefill flow и `group_id` lifecycle задокументированы | `tacticum_chat.php`, `tacticum_prefill.php`, `chat-agent.js`, `docs/workflow/chat-offer-contract.md` | Риск поломки при изменении AI payload или свойств инфоблока снижен, QA получил smoke cases | Поддерживать contract при изменениях chat/prefill/offer |
| PG-004 | closed | P2 | Full Feature | SEO/content | Базовые `description`, canonical и OpenGraph добавлены на публичные страницы | `tacticum_apply_seo_defaults()`, public pages, `policies/index.php` | Сниппеты, шаринг и canonical policy приведены к единому baseline; follow-up offer detail ЧПУ/indexability ведётся в `SEO-001` | Post-deploy smoke: проверить rendered head и отсутствие дублей meta |
| PG-005 | closed | P2 | Fast Fix | Legal/consent | Consent-ссылки активных форм ведут на `/policies/` и открываются безопасно | `index.php`, `calculator/index.php`, `contacts/index.php`, `offer/template.php`, `footer.php`, `forms.js` | Legal UX consistency для публичных форм восстановлена | Поддерживать правило в Lead Form Contract и PR checks |
| PG-006 | closed | P2 | Full Feature | Analytics | Добавлена taxonomy и client-side events для форм, AI chat, prefill, Telegram resolver | `analytics.js`, `forms.js`, `chat-agent.js`, `tg-link-resolver.js`, `docs/workflow/analytics-events.md` | Conversion funnel можно мерить без отправки PII в аналитику | Post-deploy smoke: подтвердить goals в Yandex.Metrika/tag manager |
| PG-007 | closed | P2 | Full Feature | Content model | Ключи инфоблоков используются публичными страницами через config helper | `local/php_interface/include/tacticum_config.example.php`, `docs/adr/ADR-003-iblock-ids.md`, `tacticum_iblock_id()`, public `IncludeComponent` | Переносимость публичных страниц повышена, numeric public `IBLOCK_ID` устранены | Поддерживать правило в PR checks и не добавлять новые hardcoded IDs |
| PG-008 | closed | P2 | Full Feature | Layout consistency | Повторяемые CTA/form секции вынесены из публичных страниц в локальный компонент `tacticum:lead.cta` с явными вариантами | `local/components/tacticum/lead.cta/`, `index.php`, `calculator/index.php`, `price/index.php`, `contacts/index.php`, `about/index.php`, `services/index.php` | UX-правки повторяемых CTA теперь делаются в одном месте; страницы передают только form config | Поддерживать PR guards и не копировать CTA markup обратно в public pages |
| PG-009 | closed | P1 | Full Feature | Price staff order | Заказ специалистов на `/price/` переведён из одиночной роли в состав multi-staff заявки с количеством по ролям, суммарной ставкой, persistent summary, быстрыми пресетами команды, segmented-выбором уровня, порядком уровней `Junior -> Middle -> Senior -> Lead`, фильтр-счётчиком, empty state, гибкими пресетами срока, календарём точной даты окончания работ и ориентировочным месячным бюджетом | `news.list/price/template.php`, `news.list/price/result_modifier.php`, `news.list/price/script.js`, `news.list/price/style.css`, `tacticum_sale_staff.php`, `lead-form-contract.md`, `price-staff-order-plan.md` | Пользователь может быстрее подобрать уровень/роль и собрать команду без повторных отдельных заявок; backend сохраняет rich `workers[]`, `team_preset`, `monthly_budget_estimate`, `end_date` и legacy fallback | Post-deploy `npm run browser:smoke`; отдельный ручной valid-submit smoke без боевого лида или на staging |
| PG-010 | closed | P1 | Fast Fix | Price AI calculator | Light chat на `/price/` больше не растягивает страницу при новых сообщениях; сообщения прокручиваются внутри блока | `styles/global.css`, `tools/visual-smoke.mjs` | Пользователь сохраняет контекст секции, форма ввода остаётся доступной, страница не получает резкий вертикальный рост | Post-deploy `browser:smoke` по `/price/` и `/calculator/` |

## Technology Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| TG-001 | closed | P0 | Security / Integration | HTTPS/config | Runtime REST не имеет HTTP fallback, требует HTTPS и production config health подтверждён | `rest_helpers.php`, REST endpoints, `local/php_interface/include/tacticum_config.example.php`, `.github/workflows/deploy.yml`, `GET https://tacticum.ru/local/rest/health_config.php` 21.05.2026 вернул `success: true` | Runtime защищён от silent HTTP fallback; deploy smoke продолжит ловить невалидный server config до пользовательских 500 | Поддерживать deploy health smoke и синхронизировать server `tacticum_config.php` при новых config keys |
| TG-002 | closed | P1 | Security / Integration | Config/iblocks | `init.php` и публичные `IncludeComponent` используют config registry для ID инфоблоков | `local/php_interface/init.php`, `docs/adr/ADR-003-iblock-ids.md`, public pages | Backend callbacks и публичные страницы стали переносимее между окружениями | Поддерживать `tacticum_iblock_id()` / `tacticum_rest_get_iblock_id()` как стандарт |
| TG-003 | closed | P1 | Security / Integration | REST consistency | Все outbound AI/Telegram requests в `/local/rest` проходят через shared helper; response shapes остаются доменными | `rest_helpers.php`, `tacticum_form.php`, `tacticum_chat.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `resolve_telegram_link.php` | Curl/timeout/TLS handling больше не расходится между endpoints | Отдельный будущий gap: унифицировать success-body contract, если потребуется продуктово |
| TG-004 | closed | P1 | Security / Integration | CSRF | `tacticum_rest_check_csrf()` требует явный token; chat/prefill/resolver frontend передаёт `BX.bitrix_sessid()` | `rest_helpers.php`, `index.php`, `calculator/index.php`, `price/index.php`, `tg-link-resolver.js` | CSRF модель приведена к явному Bitrix token для state-changing POST | Поддерживать правило в Lead Form Contract и PR checks |
| TG-005 | closed | P1 | Fast Fix | Logging/PII | Файловое runtime-логирование в `/local` и публичных скриптах удалено | `init.php`, `content_migrations.php`, `rest_helpers.php`, `tacticum_chat.php`, `tacticum_prefill.php`, `tacticum_sale_staff.php`, `resolve_telegram_link.php` | Payload/response/PII больше не попадают в файловые логи из кастомного runtime-кода | Поддерживать scan guard против `AddMessage2Log`, `error_log`, `file_put_contents`, `console.log/error/warn` в runtime-коде |
| TG-006 | closed | P1 | Full Feature | Frontend maintainability | Chat inline scripts/styles вынесены; устаревший offer inline script удалён | `chat-agent.js`, `header.php`, `offer/template.php` | Основной chat/prefill flow теперь тестируемый и не дублируется в публичных страницах | Поддерживать правило: новый JS/CSS только через assets/components |
| TG-007 | closed | P1 | Fast Fix | SEO/sitemap | Sitemap переведён на HTTPS, включает `/policies/` и систематизирован под Bitrix-generated static sitemap | `sitemap.xml`, `sitemap-basic-files.xml`, `/offer/sitemap.php` | SEO inconsistency устранена для sitemap; root index не зависит от перегенерации Bitrix | Поддерживать Bitrix sitemap settings и production `seo:check:prod` при новых публичных URL |
| TG-008 | closed | P2 | Security / Integration | Bitrix D7 | В `local/` и публичных страницах scan не нашёл `CModule::IncludeModule()`; touched code использует `Loader::includeModule()` | `rest_helpers.php`, `init.php`, `tacticum_prefill.php`, public pages | Новый runtime-код ближе к D7 best practice | Поддерживать `Loader::includeModule()` как стандарт |
| TG-009 | closed | P2 | Full Feature | API performance | GET API endpoints используют `Bitrix\Main\Data\Cache` через `tacticum_api_cached_payload(...)` | `local/api/*.php`, `rest_helpers.php`, `tacticum_config.example.php` | Повторные запросы к public API меньше нагружают инфоблоки; TTL управляется config | Post-deploy smoke: проверить first/second response и invalidate при изменении контента |
| TG-010 | closed | P2 | Security / Integration | REST method policy | Production prefill flow работает только через POST JSON; legacy GET fallback удалён | `tacticum_prefill.php`, `chat-agent.js`, `docs/workflow/chat-offer-contract.md` | Семантика production REST flow выровнена с остальными `/local/rest` endpoints и меньше раскрывает данные через URL | Поддерживать POST-only prefill в smoke |
| TG-011 | closed | P2 | Full Feature | Asset loading | Фактическая карта CSS/JS assets зафиксирована; optional assets подключаются через explicit page flags; static CSS build plan описан | `docs/workflow/asset-layout-audit.md`, `docs/workflow/static-css-build-plan.md`, `header.php`, public pages | Снижен риск случайного подключения assets на новых URL, следующий CSS cleanup имеет безопасный маршрут | Реализовать static CSS build plan после visual baseline |
| TG-012 | closed | P2 | Security / Integration | CI quality gates | Critical runtime checks стали blockers, public hardcoded iblocks остаются warning-level | `.github/workflows/pr-check.yml` | Нарушения REST/API conventions сложнее протащить в main | Поддерживать список checks при новых ADR |
| TG-013 | closed | P2 | Full Feature | Config validation | Добавлен `tacticum_rest_validate_config()` и same-origin health endpoint без вывода secret values | `rest_helpers.php`, `local/rest/health_config.php`, `tacticum_config.example.php` | Ошибки config можно проверить до пользовательского runtime 500 | Post-deploy smoke: `GET /local/rest/health_config.php` с allowed host/origin |
| TG-014 | closed | P2 | Fast Fix | Repository hygiene | `.DS_Store`/cache/backup/IDE files ignored; `tacticum_config.php` убран из Git index и остаётся локальным ignored config | `.gitignore`, `docs/workflow/repository-hygiene.md`, `git ls-files -c -i --exclude-standard` | Риск случайного commit local config/runtime мусора снижен | Поддерживать hygiene check перед PR |
| TG-015 | closed | P1 | Full Feature | CSS architecture | Browser Tailwind runtime удалён; static Tailwind bundle собирается через npm; dead CSS/JS artifacts удалены; `aiagents.css` слит в scoped-блок `styles/global.css`; generic Remixicon fallback удалён и `ri-*` классы валидируются; добавлен visual smoke и закрыты найденные overflow regressions | `package.json`, `tools/visual-smoke.mjs`, `tools/template-styles-retirement-check.mjs`, `local/templates/tacticum/assets/src/tailwind.css`, `local/templates/tacticum/tailwind.generated.css`, `styles/global.css`, `template_styles.css`, `header.php`, `.github/workflows/pr-check.yml`, `asset-layout-audit.md` | FOUC/no-JS риск снижен, CSS utilities воспроизводимы локально и в CI; CSS-local smoke поддерживает единый manual runtime CSS file `styles/global.css`, icon classes больше не маскируются generic fallback | После deploy выполнить `npm run visual:smoke` без CSS injection как обычный post-deploy gate |
| TG-016 | closed | P1 | Full Feature | Layout contracts | URL/text-based presentation и behavior убраны из затронутых мест | `faq/template.php`, `aiagents/index.php`, `modal.js`, `scroll.js`, `.github/workflows/pr-check.yml` | Компоненты меньше зависят от текущего URL и текста кнопок, риск случайного поведения ниже | Поддерживать explicit component params и data-* contracts в PR checks |
| TG-017 | closed | P1 | Full Feature | JS-owned markup | Specialist modal markup перенесён из JS в Bitrix component template; repeated CTA sections вынесены в локальный component `tacticum:lead.cta` | `news.list/price/template.php`, `news.list/price/script.js`, `modal.js`, `local/components/tacticum/lead.cta/`, public pages | Заказ специалистов и CTA sections стали ближе к Bitrix component pattern; JS больше не владеет крупным modal markup | Поддерживать component pattern для новых повторяемых layout blocks |
| TG-018 | closed | P2 | Fast Fix | Inline markup cleanup | Убраны inline `onclick`, policy `<style>`/`style=`, progress inline widths и form UI inline style mutations; header logo получил `alt` | `about/index.php`, `services/index.php`, `policies/template.php`, `policies/style.css`, `index.php`, `forms.js`, `price/script.js`, `header.php` | HTML/JS стали семантичнее, меньше inline presentation/behavior | Поддерживать guard против inline `onclick`, policy inline styles и form inline style mutations |
| TG-019 | closed | P1 | Incident / Full Feature | Browser zero-error gate | Initial-load production smoke чистый; `/price/` regression подтверждён как mixed-rollout: legacy HTML без `data-price-*` + новый JS. Исправленный `news.list/price/script.js` поддерживает legacy/new selectors и fallback modal; обычный browser smoke без injection проходит | `tools/visual-smoke.mjs`, `news.list/price/template.php`, `news.list/price/script.js`, `local-public-browser-error-challenge.md`; manifests: `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-22T21-13-19-948Z/manifest.json`, `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-23T13-54-52-683Z/manifest.json` | Initial-load browser errors = 0 подтверждён; фильтры/search/order modal на `/price/` проходят desktop/mobile action smoke | Поддерживать `npm run browser:smoke` как post-deploy gate; после новых `/price/` правок проверять team presets/summary |
| TG-020 | closed | P1 | Security / Integration | REST bootstrap / PII logs | POST endpoints приведены к `validate_origin -> rate_limit -> method -> parse JSON -> CSRF`; файловое runtime-логирование удалено | `rest_helpers.php`, `tacticum_form.php`, `tacticum_chat.php`, `tacticum_prefill.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `resolve_telegram_link.php` | Большие/битые тела не читаются до guard; пользовательский текст и контакты не пишутся в кастомные файловые логи | Post-deploy REST smoke по chat/form/prefill/sale_staff |
| TG-021 | closed | P1 | Full Feature | Frontend data contracts | Light chat и price component переведены на явные `data-*` contracts: quick replies используют `data-message`, filters/modal/price state не зависят от presentation selectors | `chat-agent.js`, `calculator/index.php`, `price/index.php`, `news.list/price/template.php`, `news.list/price/script.js`, `.github/workflows/pr-check.yml` | Копирайтинг/CSS refactor больше не должен ломать отправку quick replies, фильтры ставок и модалку заказа специалиста | Поддерживать guard в `pr-check.yml`; покрыть клики отдельным action-smoke в TG-024 |
| TG-022 | closed | P2 | Security / Integration | Sale endpoint ownership | Upstream `/chat_agent/sale` call, group_id retry and upstream error handling centralized in shared sale adapter; `tacticum_offer.php` and `tacticum_sale.php` remain legacy aliases with preserved response shape, deprecation headers and Sprint 09 sunset matrix | `rest_helpers.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_form.php`, `lead-form-contract.md`, `current-state.md`, `sprints/2026-05-23-sprint-09-overall-gap-closure.md`, `sprints/2026-05-23-sprint-09-sale-sunset-upstream.md` | Будущие изменения sale upstream/retry делаются в одном helper; публичные формы продолжают использовать `tacticum_form.php`, staff-order — `tacticum_sale_staff.php`; внешние consumers видят lifecycle сигнал | Выполнить Sprint 09 matrix: inventory до `30.06.2026`, migration до `31.08.2026`, final alias mode до `30.09.2026` |
| TG-023 | closed | P2 | Full Feature | Inline/vendor assets / CSP | `/contacts/` использует Yandex map widget iframe без constructor script; Metrika вынесена из inline script в centralized template asset `js/metrika.js`; noscript pixel использует CSS class | `contacts/index.php`, `header.php`, `js/yandex-map.js`, `js/metrika.js`, `styles/global.css`, `asset-layout-audit.md` | Карта и Метрика больше не живут inline в public page/header script block; будущий CSP проще строить вокруг `self` и vendor domains | При введении CSP явно разрешить Yandex map widget/Метрику и проверить карту и цели Метрики после deploy |
| TG-024 | closed | P2 | Full Feature | Browser action smoke | Добавлен `browser:smoke` поверх `visual:smoke`: non-network actions кликают меню, contact modal, empty form validation, empty chat send, price filters/search/empty-state/level и specialist modal | `tools/visual-smoke.mjs`, `package.json`, `post-deploy-smoke.md`, `local-public-browser-error-challenge.md` | Ошибки обработчиков теперь попадают в browser gate без создания лидов и содержательных AI-запросов | Deploy workflow запускает `browser:smoke`; реальные upstream success-flow по-прежнему проверять ручным/стейдж smoke |
| TG-025 | closed | P2 | Fast Fix | Agent instruction drift | Agent docs обновлены под static Tailwind и shared REST bootstrap | `.github/copilot-instructions.md`, `.github/agents/frontend-dev.md`, `.github/agents/backend-dev.md`, `.github/agents/designer.md`, `.github/agents/seo.md` | Новые агенты с меньшей вероятностью вернут удалённые CSS/JS artifacts или старый endpoint bootstrap | Поддерживать `.github/*` при изменении workflow docs |
| TG-026 | closed | P1 | Full Feature | Iblock content output | Публичные templates инфоблоков и GET API получили общий decode/sanitize path для повторно закодированных HTML entities | `content_helpers.php`, `init.php`, `rest_helpers.php`, `news.list/*/template.php`, `news.detail/*/template.php` | `&nbsp;`, `&amp;nbsp;` и похожие служебные последовательности больше не должны попадать в пользовательский интерфейс из контента инфоблоков | После deploy проверить FAQ/cases/services/offer/policies и выполнить visual smoke без injection |
| TG-027 | closed | P1 | Security / Integration | Deploy smoke gate | Post-deploy visual/browser smoke встроен в `deploy.yml`; runner ищет Chrome/Chromium на macOS/Linux; добавлены production smoke npm aliases | `.github/workflows/deploy.yml`, `tools/visual-smoke.mjs`, `package.json`, `post-deploy-smoke.md` | Релиз с browser runtime errors, broken images, horizontal overflow или сломанными `/price/` actions не должен пройти deploy gate | Следить за длительностью deploy; если появится staging environment, параметризовать base URL |
| TG-028 | closed | P2 | Full Feature | CSS retirement governance | Legacy `template_styles.css` выведен в comment-only shim: active CSS перенесён в `styles/global.css`, подключён через `Asset`, добавлен `template-styles:check` и CI/deploy guard; отдельные template-level CSS и generic icon fallbacks запрещены | `template-styles-retirement-plan.md`, `static-css-build-plan.md`, `asset-layout-audit.md`, `styles/global.css`, `template_styles.css`, `tools/template-styles-retirement-check.mjs`, `.github/workflows/pr-check.yml` | Active CSS больше не живёт в implicit Bitrix template file; generated utilities не дублируются; возврат правил в shim и маскировка битых иконок блокируются автоматикой | Дальнейший cleanup — component/page extraction из `styles/global.css` малыми партиями после чистого post-deploy smoke |
| TG-029 | closed | P2 | Full Feature | Bitrix framework hardening | Sprint 13 закрыл residual Bitrix framework gaps: thin `init.php`, local component namespace cleanup, `/offer/` repository/cache invalidation, footer modal component, config-based FAQ fallback и architecture guard | `init.php`, `site_helpers.php`, `seo_helpers.php`, `component_helpers.php`, `calcrequests_rest.php`, `offer_catalog.php`, `offer_catalog_cache.php`, `local/components/tacticum/contact.modal/`, `tools/bitrix-architecture-check.mjs`, `.github/workflows/pr-check.yml`, `.github/workflows/deploy.yml` | Runtime стал ближе к Bitrix best practice: bootstrap не смешан с бизнес-логикой, повторяемая форма стала компонентом, offer catalog cache сбрасывается по событиям инфоблока и отдельным изменениям свойств, регрессии ловятся static guard | Поддерживать `npm run bitrix:check`; при следующем крупном `/offer/` scope можно дальше сокращать compatibility wrappers |
| TG-030 | closed as operational guard | P1 | Full Feature | Known gap closure governance | Sprint 14 закрепил известный хвост как машинно проверяемый список: code-level open gaps = 0, external gates видны через `gaps:known`, pending gates требуют `due`, release checker self-test расширен | `tools/known-gaps-check.mjs`, `package.json`, `release-signoff-check.mjs`, `release-signoff-2026-05-24-post-deploy.draft.json`, `release-signoff-gates.md`, `sprints/2026-05-25-sprint-14-known-gap-operational-closure.md` | Нельзя потерять или устно "закрыть" external gates без owner/due/evidence; финальное закрытие проверяется strict release sign-off и `gaps:known:strict` | External gates всё ещё требуют внешних доступов: Метрика, Bitrix auth, CRM/upstream, access logs и post-deploy smoke после cache refresh |

## Sprint 14 — Known Gap Operational Closure

На 25.05.2026 известных code-level `open` / `in-progress` gaps нет. Известный хвост доработан как operational closure scope: `docs/workflow/sprints/2026-05-25-sprint-14-known-gap-operational-closure.md`.

| ID | Status | Area | Closure |
|---|---|---|---|
| S14-001 | done | Known gaps visibility | Добавлен `npm run gaps:known`; команда показывает code-level gaps, pending release gates, legacy inventory pending rows и post-deploy/cache smoke хвост |
| S14-002 | done | Pending gate discipline | `release:signoff:draft-check` требует `due` у `pending` gates; текущий draft release sign-off обновлён |
| S14-003 | done | Checker regression | `release:signoff:self-test` расширен негативным кейсом на pending gate без `due` |
| S14-004 | external handoff | External evidence | `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream`, legacy access logs/CRM inventory и post-deploy smoke остаются внешними gates с owner/due/evidence rules |

Template asset hygiene refresh 25.05.2026: аудит `local/templates/tacticum/fonts`, `images`, `include` закрыт кодом и guard-ами. Пустой template `include/` удалён, Tailwind source scan очищен, неиспользуемый Pacifico и публичные RemixIcon source/archive artifacts удалены, dead image duplicates удалены, favicon/webmanifest PNG приведены к точным размерам, а `template-styles:check` блокирует возврат этих artifacts и регресс размеров favicon/apple/android PNG.

## Sprint 15 — Product Marketing Architecture

На 25.05.2026 product/marketing gaps `PMG-001` - `PMG-010` закрыты кодом и документацией в Sprint 15: `docs/workflow/sprints/2026-05-25-sprint-15-product-marketing-architecture.md`.
Локальные automated checks прошли, включая SEO/static guards, CSS build/check, Bitrix architecture check, browser smoke, price smoke, CSS-local visual/action smoke и production SEO check. PHP CLI в локальном окружении отсутствует, поэтому PHP lint остаётся CI/deploy fallback.

| ID | Status | Area | Closure |
|---|---|---|---|
| S15-001 | done | Positioning / home | Главная hero формулирует business outcome и ведёт в 4 входа: `/offer/`, `/services/`, `/price/`, `/aiagents/` |
| S15-002 | done | Product ladder | Product ladder отражён в route cards, page intros, service cross-links и labels меню |
| S15-003 | done | `/price/` | `/price/` продаёт управляемую команду под задачу, сохраняя ставки, filters, presets, modal и `data-price-*` contracts |
| S15-004 | done | `/offer/` conversion | Catalog/detail объясняют, что пример не является финальной сметой, и ведут к персональной оценке с контекстом примера |
| S15-005 | done | `/calculator/` | Страница показывает формат результата: бюджет, сроки, команда, риски и next step |
| S15-006 | done | Proof system | Спорные claims `98%`, `15+ лет`, “гарантия результата” удалены или переписаны в безопасные формулировки |
| S15-007 | done | `/aiagents/` | Страница приведена к B2B-service tone: демо Telegram-сценария, прототип, интеграции и связь с `/services/` |
| S15-008 | done | CTA taxonomy | CTA получили page-specific promise, stable `form_id`, hidden `lead_*` context и next-step copy |
| S15-009 | done | Segmentation | Industry/scenario входы реализованы через существующие `/offer/catalog/...` states, которые остаются `noindex,follow` и canonical `/offer/` |
| S15-010 | done | Lead qualification | Shared CTA добавил optional `lead_budget` / `lead_timeline`; backend append-ит allowlisted context в существующий `task` без нового upstream contract |
| S15-011 | automated checks passed | Smoke gates | Static/browser/SEO checks прошли; post-deploy smoke после deploy/cache refresh обязателен перед закрытием release evidence |

External gates из Sprint 14 остаются отдельным хвостом: Метрика, Bitrix auth, CRM/upstream, access logs и post-deploy release sign-off evidence.

## Sprint 16 — Final Stabilization Closure

Финальный challenge 25.05.2026 зафиксирован в `docs/workflow/final-stabilization-challenge-gap-analysis-2026-05-25.md`.

Вывод challenge: сайт близок к целевому состоянию. Sprint 16 локально закрыл code/docs gaps по карте `/contacts/`, contrast offer detail, calculator/price chat-to-lead handoff, CTA image trust, proof matrix, SEO/CSP decisions и contact/legal hierarchy. Production deploy/cache smoke 25.05.2026 прошёл по rendered/action/price gates, включая новую карту `Тактикум` на `/contacts/`. Сайт всё ещё нельзя считать полностью стабилизированным до восстановления upstream success-flow/staff-order, CRM/upstream evidence, подтверждения Metrika goals и authenticated Bitrix admin smoke.

Новый спринт на команду: `docs/workflow/sprints/2026-05-25-sprint-16-final-stabilization-closure.md`.

| ID | Status | Priority | Area | Closure Target |
|---|---|---|---|---|
| FSC-001 | closed | P1 | `/contacts/` map correctness | Wrong placeholder/constructor state removed; Yandex map widget iframe points to `Тактикум` (`oid=243968538014`), `БЦ Victory Park` is a landmark, legal address is separate |
| FSC-002 | closed | P1 | Offer detail estimate contrast | `/offer/<code>/` estimate block now has explicit gradient background and `text-white` |
| FSC-003 | closed | P1 | Deploy/cache smoke | 25.05.2026 production `visual:smoke`, `browser:smoke`, focused `/price/` smoke and `seo:check:prod` passed; contacts map renders `Тактикум` |
| FSC-004 | external blocker | P1 | Real success-flow / staff upstream | 25.05.2026 controlled production smoke attempted; default form, modal form, AI chat and staff-order returned upstream `502`; prefill controlled empty state returned expected `404` |
| FSC-005 | closed | P1 | Calculator/price chat-to-lead handoff | Light chat surfaces передают safe summary/scoped `group_id` в целевую CTA form без PII analytics |
| FSC-006 | external handoff | P1 | Metrika goals | Affected form/chat/staff-order goals требуют подтверждения в Яндекс.Метрике без PII params |
| FSC-007 | closed | P2 | CTA image trust | Generic `specialoffer.jpg` suppressed by default; personal-offer CTA supports no-image form-only layout |
| FSC-008 | closed | P2 | Proof evidence matrix | `docs/workflow/proof-claims-matrix.md` фиксирует allowed proof, source rules и forbidden formulations; runtime numeric claims removed |
| FSC-009 | accepted | P2 | Industry/scenario SEO decision | Accepted noindex strategy documented; indexable cluster pages are future SEO scope |
| FSC-010 | accepted | P2 | CSP target-state decision | Report-only accepted as stabilization target; enforce remains future Security / Integration rollout |
| FSC-011 | accepted | P3 | Local PHP CLI | Локальный PHP CLI отсутствует; GitHub PHP 8.4 lint остаётся authoritative fallback |
| FSC-012 | closed | P2 | Contact/legal content hierarchy | `/contacts/` CTA moved before legal details; legal/trust copy remains available below |

## Recommended First Sprint

См. `docs/workflow/sprints/2026-05-20-sprint-01-stabilization.md`.

Цель первого спринта — не новая фича, а стабилизация основы:

- закрыть P0/P1 security/config gaps;
- привести sitemap/legal минимально в порядок;
- начать унификацию AI chat/form contracts;
- усилить CI gates.

## Gap Lifecycle

1. PM выбирает gap и создаёт Issue.
2. Issue получает `workflow_lane`, priority, owner.
3. Если gap проходит ADR/Design/QA gates — подключить нужного агента до разработки.
4. После PR/deploy обновить status gap:
   - `closed`, если полностью устранён;
   - `in-progress`, если часть работ осталась;
   - добавить ссылки на Issue/PR/ADR.
