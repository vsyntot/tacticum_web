# Release Sign-Off Gates

Дата фиксации: 23.05.2026

Этот документ закрывает release evidence gaps: автоматический rendered SEO head, `/price/` team presets, а также операционные проверки, которые нельзя безопасно автоматизировать в production: реальные успешные отправки форм/чата, проверка целей Метрики, синхронизация server config и проверка админки.

## Обязательное Правило

PM не закрывает release issue, пока для затронутых областей не заполнен sign-off:

| Gate | Когда обязателен | Owner | Evidence |
|---|---|---|---|
| `automated-deploy-smoke` | Любой production deploy | DevOps | Лог `health_config`, `visual:smoke`, warning-aware `browser:console` из `deploy.yml`; ссылки на оба smoke manifest |
| `seo-rendered-head` | Изменены публичные страницы, template head, SEO helper, sitemap/robots или assets, влияющие на rendered head | SEO + QA | `visual:smoke` manifest: для затронутых URL `seoErrors=[]`, `seoHead` содержит уникальные title/description/canonical/OpenGraph; product URLs содержат rendered `SoftwareApplication` + `FAQPage` and required `data-product-block` inventory |
| `price-team-presets` | Изменены `/price/`, price component JS/template/style или staff-order flow | QA + Frontend | `browser:smoke` manifest: action `price team presets/summary` = `ok` для desktop/mobile, detail содержит `workers` и budget |
| `css-js-e2e-readiness` | Изменены CSS/JS, frontend assets, visual-smoke tooling или deploy/cache behavior | Frontend + QA | `e2e:css-js:prod` passed; при CSS PR также `e2e:css-js:local` passed; manifest не содержит browser/runtime/action blockers или `consoleWarnings` |
| `manual-success-flow` | Изменены формы, чат, prefill, sale/staff-order или upstream adapter | QA + Backend/Frontend owner | Staging lead ID или controlled production lead с временем проверки |
| `metrika-goals` | Изменены `analytics.js`, `metrika.js`, формы, чат или goal taxonomy | PM/Marketing + QA | Названия проверенных goals и время проверки в Yandex.Metrika |
| `config-sync` | Добавлены/изменены config keys | DevOps | Подтверждение, что production/staging `tacticum_config.php` синхронизирован с `tacticum_config.example.php`; для ignored runtime config приложить `npm run config:runtime:check` без secret values |
| `bitrix-admin` | Изменён template/header/assets/deploy/cache | QA/Admin | Авторизованный вход в Bitrix admin panel после deploy |
| `legacy-sunset` | Изменены legacy sale aliases или дата >= 30.09.2026 | Architect + Backend | Решение по Sprint 09 matrix: удалить aliases, вернуть `410/redirect` или продлить поддержку |
| `staff-sale-upstream` | Изменён `ai.endpoint_paths.staff_sale` или upstream workers contract | Architect + Backend + QA + DevOps | Config-sync, health-check и staging staff-order success-flow |
| `csp-enforce` | `security.csp_mode=enforce` или CSP policy меняется в сторону blocking behavior | Security + Frontend + QA | Report-only baseline, inline/vendor inventory, staging enforce smoke, rollback to report-only |
| `sensitive-endpoint-access` | Добавлен private proof/doc/procurement endpoint, gated download or sensitive access flow | Security + PM + Backend + QA | Allowed/denied/expired access smoke, noindex/cache policy, no-PII logging evidence |
| `endpoint-risk-class` | Добавлен endpoint class или изменены auth/rate/origin/IP/proxy rules | Security + Backend + DevOps | Sprint 22 endpoint class, origin/CSRF, rate-limit, auth/IP/proxy and logging evidence |
| `legacy-final-mode` | Legacy alias удаляется, возвращает `410`, redirect или получает support-extension final mode | Architect + Backend + DevOps + PM | Full-window aggregate inventory, CRM/upstream source report, implementation smoke and rollback/support plan |

## Manual Success-Flow Matrix

Проверять на staging. Production допустим только с контролируемым тестовым лидом и пометкой в CRM/upstream.

| Flow | URL / Endpoint | Минимальная проверка |
|---|---|---|
| Default lead form | `/local/rest/tacticum_form.php` | Валидная форма возвращает `success=true`, upstream принял лид; для product CTA controlled `lead_scenario` select уходит только в lead payload |
| Modal form | footer modal | Открытие, отправка, success state, закрытие modal |
| AI chat | `/local/rest/tacticum_chat.php` | Валидное сообщение получает controlled response без raw stack/PII |
| Prefill | `/local/rest/tacticum_prefill.php` | `group_id + sessid` возвращает ожидаемое заполнение или controlled empty state |
| Staff order | `/local/rest/tacticum_sale_staff.php` | `workers_json`, `team_preset`, `monthly_budget_estimate`, `endDate` доходят до upstream |
| Legacy aliases | `/local/rest/tacticum_offer.php`, `/local/rest/tacticum_sale.php` | Response shape сохранён, headers `Deprecation`, `Sunset`, `Link` присутствуют |

## Automated Evidence Matrix

| Evidence | Команда | Что считается passed |
|---|---|---|
| Rendered SEO head | `npm run seo:smoke` | В `manifest.json` у затронутых URL нет `seoErrors`; `seoHead` фиксирует один title, одну description, один HTTPS canonical и обязательные OpenGraph meta без дублей; для `/platform/`, `/agents/`, `/dev/`, `/forum/` manifest фиксирует product `SoftwareApplication` + `FAQPage` schema and required `data-product-block` inventory |
| Product source marker | `npm run product:source:http:prod` on production server; `npm run product:source:smoke:prod` where Chrome/Chromium is available | После deploy/cache refresh `/platform/`, `/agents/`, `/dev/`, `/forum/` имеют rendered `data-product-source=bitrix`; browser smoke additionally checks `seo=ok` and `blocks=ok`, but requires Chrome/Chromium |
| Public release precheck | `npm run release:public-precheck:prod` | Без создания лидов проверяет `health_config`, product source marker, public Metrika tag, unauth `/bitrix/admin/` surface and legacy alias deprecation headers; это precheck, а не замена manual gates |
| Product block previews | `npm run product:block-previews:prod` | Для design/QA handoff manifest содержит `productBlockScreenshots[]`, `productBlockErrors=[]`, а `product-blocks/*.png` содержит rendered AS IS screenshots по product blocks |
| `/price/` team presets | `npm run browser:smoke:price` | Для `/price/` desktop/mobile action `price team presets/summary` имеет `status=ok`, а `detail` показывает количество `workers` и рассчитанный monthly budget |
| CSS/JS e2e readiness | `npm run e2e:css-js:prod`; для CSS PR ещё `npm run e2e:css-js:local` | Manifest не содержит `errors`, `pageErrors`, `consoleErrors`, `consoleWarnings`, first-party `networkErrors`, `actionErrors`, broken images или horizontal overflow; product `lead_scenario` selects and FAQ toggles pass; `/price/` team presets проходят |

## Sale / Staff Process Rules

- До `30.09.2026` legacy aliases проходят по matrix из `docs/workflow/sprints/2026-05-23-sprint-09-sale-sunset-upstream.md`; consumer inventory ведётся в `docs/workflow/legacy-sale-alias-consumer-inventory.md`; release нельзя закрывать, если выбранный final mode не отражён в implementation, headers/checker и contract docs.
- `ai.endpoint_paths.staff_sale` меняется только при совместимом rich workers upstream contract. Если меняется request/response model, нужен новый Security / Integration scope с ADR-006 и Lead Form Contract update до deploy.

## PR / Issue Template Snippet

Для release closure можно вести machine-readable sign-off JSON по примеру `docs/workflow/release-signoff.example.json` и проверять:

```bash
npm run release:signoff:check -- path/to/release-signoff.json
```

Product-first draft для релиза `Platform / Agents / Dev / Forum` лежит в `docs/workflow/release-signoff-2026-06-01-product-first.draft.json`. Он намеренно содержит pending gates до deploy/cache refresh and manual evidence closure:

```bash
npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
```

Regression coverage для самого checker:

```bash
npm run release:signoff:self-test
```

Для промежуточного PM/QA handoff, где часть ручных gates ещё не закрыта, допустим draft со статусом `pending`, обязательным `reason` и явным `due`:

```bash
npm run release:signoff:draft-check -- path/to/release-signoff.draft.json
npm run release:signoff:summary -- path/to/release-signoff.draft.json
```

Draft-check не является release closure: перед закрытием issue тот же файл должен пройти строгий `release:signoff:check`, где все gates имеют только `passed` или `not_applicable`.

Ручные gates закрываются по `docs/workflow/manual-release-gates-runbook.md`. Безопасный JSON-формат evidence для переноса в release sign-off лежит в `docs/workflow/release-signoff-manual-evidence.template.json`; в него нельзя добавлять PII, raw payload, cookie/session IDs или полный upstream response.
Для pending manual gates draft JSON обязан содержать `due`, ссылки `evidence.runbook` и `evidence.evidence_template`; draft-check печатает список оставшихся pending gates.
Чтобы передать текущий ручной хвост owner-ам без ручной сборки формата, использовать helper:

```bash
npm run release:manual-gates:helper -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
```

Он читает текущий draft, показывает pending `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream`, next actions and safe evidence skeletons. Helper read-only: он не создаёт лиды, не ходит в production and не сохраняет PII.
Если `docs/` не выгружается на production server, helper запускается в standalone skeleton mode и печатает универсальные skeletons без текущего draft-контекста; финальный перенос evidence всё равно выполняется в repository sign-off JSON.
Для owner-run проверки `manual-success-flow` использовать `npm run manual:success-flow:helper`: он генерирует controlled payload/browser/curl templates для default form, modal form, AI chat and prefill, добавляет безопасный `qa_marker` для поиска тестовых лидов в upstream/CRM, но не отправляет запросы сам. Browser output переносить в sign-off только как safe summary, без raw response/body.
Для owner-run проверки `metrika-goals` использовать `npm run metrika:goals:helper`: он показывает expected goals/events, проверяет deployed JS taxonomy and даёт browser observer snippet, но не заменяет проверку goals в Яндекс.Метрике.
Для owner-run проверки `bitrix-admin` использовать `npm run bitrix:admin:gate-helper`: он показывает authenticated admin/public toolbar checklist and safe evidence skeleton, но не логинится в Bitrix и не сохраняет cookie/session data.

Sprint 22 implementation follow-up 04.06.2026: `release-signoff-check.mjs`, `release-signoff-self-test.mjs` and this document now support security-sensitive gates `csp-enforce`, `sensitive-endpoint-access`, `endpoint-risk-class` and `legacy-final-mode`. Current releases must include them as `not_applicable` when the trigger does not apply; when a trigger applies, `passed` evidence must follow the safe shapes below.

## Security-Sensitive Gate Evidence

All security-sensitive gates reject raw payload/log/request/response keys, contact fields, cookie/session/token/secret keys, email-like values and phone-like values.

| Gate | Required passed evidence |
|---|---|
| `csp-enforce` | `environment`, `checked_at`, `checked_by`, `mode=enforce`, `report_only_baseline`, `inline_inventory`, `vendor_inventory`, `staging_enforce_smoke`, `rollback`, `violations_triaged=true`, `rollback_to_report_only_documented=true` |
| `sensitive-endpoint-access` | `environment`, `checked_at`, `checked_by`, `flow`, `access_model` one of `authenticated-session`, `expiring-signed-link`, `owner-approved-token`, `allowed_result`, `denied_result`, `expired_or_malformed_result`, `noindex_or_cache_policy`, `logging_pii_check` |
| `endpoint-risk-class` | `checked_at`, `checked_by`, `endpoint` as site path or HTTPS URL, `risk_class` from Sprint 22 endpoint sensitivity matrix, `origin_csrf`, `rate_limit`, `auth_ip_proxy`, `logging_evidence` |
| `legacy-final-mode` | `checked_at`, `checked_by`, both legacy aliases in `aliases`, `final_mode` one of `remove`, `410`, `redirect`, `extend-support`, `compatibility-endpoint`, `inventory_window`, `access_log_aggregate`, `crm_upstream_report`, `implementation_result`, `rollback_or_support_plan` |

Если evidence указывает на локальный `manifest.json`, checker парсит его и дополнительно проверяет:

- нет `errors`, `pageErrors`, `consoleErrors`, `networkErrors`, `actionErrors`;
- для `seo-rendered-head` нет `seoErrors`, есть один title/description/canonical/H1, обязательные OpenGraph meta, product schema summary and required product block inventory for product URLs;
- для `price-team-presets` action `price team presets/summary` прошёл на desktop/mobile и содержит `workers` + `budget` в detail.
- для `css-js-e2e-readiness` production visual/browser/price manifests проходят общие browser guards, а `/price/` manifest дополнительно проверяет team presets.
- release metadata содержит `id`, `date`, `commit`; `date` имеет формат `YYYY-MM-DD`, `base_url` при наличии использует HTTPS, strict mode не принимает `working-tree` commit marker;
- неизвестные gates запрещены: release JSON должен использовать только список из этого документа;
- security-sensitive gates `csp-enforce`, `sensitive-endpoint-access`, `endpoint-risk-class`, `legacy-final-mode` имеют обязательные evidence fields, no-PII/raw evidence scan and dedicated negative self-tests;
- для manual gates в статусе `passed` evidence должен быть объектом с обязательными полями из runbook; checker дополнительно отсекает placeholder-ы, email/phone-like значения и ключи, похожие на raw payload, cookie/session/token/secret.
- для manual gates в статусе `pending` draft должен содержать `due`, а evidence должен ссылаться на runbook и evidence template.

```markdown
### Release sign-off
- automated-deploy-smoke: pending / passed, link:
- seo-rendered-head: not applicable / pending / passed, manifest:
- price-team-presets: not applicable / pending / passed, manifest:
- css-js-e2e-readiness: not applicable / pending / passed, manifests:
- manual-success-flow: not applicable / pending / passed, evidence:
- metrika-goals: not applicable / pending / passed, evidence:
- config-sync: not applicable / pending / passed, owner:
- bitrix-admin: not applicable / pending / passed, owner:
- legacy-sunset: not applicable / pending / passed, decision:
- staff-sale-upstream: not applicable / pending / passed, evidence with team_preset, workers_count, monthly_budget_estimate_present, end_date_present, upstream_request_id/lead_id:
- csp-enforce: not applicable / pending / passed, safe evidence:
- sensitive-endpoint-access: not applicable / pending / passed, safe evidence:
- endpoint-risk-class: not applicable / pending / passed, safe evidence:
- legacy-final-mode: not applicable / pending / passed, safe evidence:
```

## Escalation

Если manual success-flow не может быть выполнен до deploy, PM явно оставляет release issue открытым со статусом `Review` и фиксирует:

- почему staging/production smoke отложен;
- кто владелец проверки;
- до какого времени проверка должна быть завершена;
- какие customer-facing сценарии остаются под риском.
