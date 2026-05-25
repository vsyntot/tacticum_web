# Release Sign-Off Gates

Дата фиксации: 23.05.2026

Этот документ закрывает release evidence gaps: автоматический rendered SEO head, `/price/` team presets, а также операционные проверки, которые нельзя безопасно автоматизировать в production: реальные успешные отправки форм/чата, проверка целей Метрики, синхронизация server config и проверка админки.

## Обязательное Правило

PM не закрывает release issue, пока для затронутых областей не заполнен sign-off:

| Gate | Когда обязателен | Owner | Evidence |
|---|---|---|---|
| `automated-deploy-smoke` | Любой production deploy | DevOps | Лог `health_config`, `visual:smoke`, warning-aware `browser:console` из `deploy.yml`; ссылки на оба smoke manifest |
| `seo-rendered-head` | Изменены публичные страницы, template head, SEO helper, sitemap/robots или assets, влияющие на rendered head | SEO + QA | `visual:smoke` manifest: для затронутых URL `seoErrors=[]`, `seoHead` содержит уникальные title/description/canonical/OpenGraph |
| `price-team-presets` | Изменены `/price/`, price component JS/template/style или staff-order flow | QA + Frontend | `browser:smoke` manifest: action `price team presets/summary` = `ok` для desktop/mobile, detail содержит `workers` и budget |
| `css-js-e2e-readiness` | Изменены CSS/JS, frontend assets, visual-smoke tooling или deploy/cache behavior | Frontend + QA | `e2e:css-js:prod` passed; при CSS PR также `e2e:css-js:local` passed; manifest не содержит browser/runtime/action blockers или `consoleWarnings` |
| `manual-success-flow` | Изменены формы, чат, prefill, sale/staff-order или upstream adapter | QA + Backend/Frontend owner | Staging lead ID или controlled production lead с временем проверки |
| `metrika-goals` | Изменены `analytics.js`, `metrika.js`, формы, чат или goal taxonomy | PM/Marketing + QA | Названия проверенных goals и время проверки в Yandex.Metrika |
| `config-sync` | Добавлены/изменены config keys | DevOps | Подтверждение, что production/staging `tacticum_config.php` синхронизирован с `tacticum_config.example.php` |
| `bitrix-admin` | Изменён template/header/assets/deploy/cache | QA/Admin | Авторизованный вход в Bitrix admin panel после deploy |
| `legacy-sunset` | Изменены legacy sale aliases или дата >= 30.09.2026 | Architect + Backend | Решение по Sprint 09 matrix: удалить aliases, вернуть `410/redirect` или продлить поддержку |
| `staff-sale-upstream` | Изменён `ai.endpoint_paths.staff_sale` или upstream workers contract | Architect + Backend + QA + DevOps | Config-sync, health-check и staging staff-order success-flow |

## Manual Success-Flow Matrix

Проверять на staging. Production допустим только с контролируемым тестовым лидом и пометкой в CRM/upstream.

| Flow | URL / Endpoint | Минимальная проверка |
|---|---|---|
| Default lead form | `/local/rest/tacticum_form.php` | Валидная форма возвращает `success=true`, upstream принял лид |
| Modal form | footer modal | Открытие, отправка, success state, закрытие modal |
| AI chat | `/local/rest/tacticum_chat.php` | Валидное сообщение получает controlled response без raw stack/PII |
| Prefill | `/local/rest/tacticum_prefill.php` | `group_id + sessid` возвращает ожидаемое заполнение или controlled empty state |
| Staff order | `/local/rest/tacticum_sale_staff.php` | `workers_json`, `team_preset`, `monthly_budget_estimate`, `endDate` доходят до upstream |
| Legacy aliases | `/local/rest/tacticum_offer.php`, `/local/rest/tacticum_sale.php` | Response shape сохранён, headers `Deprecation`, `Sunset`, `Link` присутствуют |

## Automated Evidence Matrix

| Evidence | Команда | Что считается passed |
|---|---|---|
| Rendered SEO head | `npm run seo:smoke` | В `manifest.json` у затронутых URL нет `seoErrors`; `seoHead` фиксирует один title, одну description, один HTTPS canonical и обязательные OpenGraph meta без дублей |
| `/price/` team presets | `npm run browser:smoke:price` | Для `/price/` desktop/mobile action `price team presets/summary` имеет `status=ok`, а `detail` показывает количество `workers` и рассчитанный monthly budget |
| CSS/JS e2e readiness | `npm run e2e:css-js:prod`; для CSS PR ещё `npm run e2e:css-js:local` | Manifest не содержит `errors`, `pageErrors`, `consoleErrors`, `consoleWarnings`, first-party `networkErrors`, `actionErrors`, broken images или horizontal overflow; `/price/` team presets проходят |

## Sale / Staff Process Rules

- До `30.09.2026` legacy aliases проходят по matrix из `docs/workflow/sprints/2026-05-23-sprint-09-sale-sunset-upstream.md`; consumer inventory ведётся в `docs/workflow/legacy-sale-alias-consumer-inventory.md`; release нельзя закрывать, если выбранный final mode не отражён в implementation, headers/checker и contract docs.
- `ai.endpoint_paths.staff_sale` меняется только при совместимом rich workers upstream contract. Если меняется request/response model, нужен новый Security / Integration scope с ADR-006 и Lead Form Contract update до deploy.

## PR / Issue Template Snippet

Для release closure можно вести machine-readable sign-off JSON по примеру `docs/workflow/release-signoff.example.json` и проверять:

```bash
npm run release:signoff:check -- path/to/release-signoff.json
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

Если evidence указывает на локальный `manifest.json`, checker парсит его и дополнительно проверяет:

- нет `errors`, `pageErrors`, `consoleErrors`, `networkErrors`, `actionErrors`;
- для `seo-rendered-head` нет `seoErrors`, есть один title/description/canonical/H1 и обязательные OpenGraph meta;
- для `price-team-presets` action `price team presets/summary` прошёл на desktop/mobile и содержит `workers` + `budget` в detail.
- для `css-js-e2e-readiness` production visual/browser/price manifests проходят общие browser guards, а `/price/` manifest дополнительно проверяет team presets.
- release metadata содержит `id`, `date`, `commit`; `date` имеет формат `YYYY-MM-DD`, `base_url` при наличии использует HTTPS, strict mode не принимает `working-tree` commit marker;
- неизвестные gates запрещены: release JSON должен использовать только список из этого документа;
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
- staff-sale-upstream: not applicable / pending / passed, evidence:
```

## Escalation

Если manual success-flow не может быть выполнен до deploy, PM явно оставляет release issue открытым со статусом `Review` и фиксирует:

- почему staging/production smoke отложен;
- кто владелец проверки;
- до какого времени проверка должна быть завершена;
- какие customer-facing сценарии остаются под риском.
