# Workflow Доработки Приложения

Этот документ описывает общий процесс развития Bitrix-приложения `tacticum.ru`: от идеи или дефекта до deploy, smoke-check и обновления документации.

## Официальные Bitrix-Ориентиры

При проектировании и ревью использовать D7/API-подходы Bitrix:

- `\Bitrix\Main\Loader::includeModule()` вместо `CModule::IncludeModule()` в новом коде: https://dev.1c-bitrix.ru/api_d7/bitrix/main/loader/includemodule.php
- `\Bitrix\Main\EventManager` для событий: https://dev.1c-bitrix.ru/api_d7/bitrix/main/EventManager/index.php
- `\Bitrix\Main\Page\Asset` для подключения JS/CSS: https://dev.1c-bitrix.ru/api_d7/bitrix/main/page/asset/index.php
- `bitrix_sessid()` / `bitrix_sessid_post()` / `check_bitrix_sessid()` как базовая модель CSRF в Bitrix: https://dev.1c-bitrix.ru/api_help/main/functions/other/bitrix_sessid_post.php
- `\Bitrix\Main\Engine\ActionFilter\Csrf` как ориентир для D7 controllers/actions: https://dev.1c-bitrix.ru/api_d7/bitrix/main/engine/actionfilter/index.php
- `\Bitrix\Main\Data\Cache` и компонентное кеширование для данных/HTML: https://dev.1c-bitrix.ru/api_d7/bitrix/main/data/cache/index.php

## Lifecycle

```text
Idea / Bug / Incident
  ↓
Workflow lane selection
  ↓
Issue / Gap / Sprint scope
  ↓
Spec / ADR / Design, если gate сработал
  ↓
Codex Plan
  ↓
Implementation
  ↓
QA / Review
  ↓
Deploy
  ↓
Post-deploy smoke
  ↓
Gap/Sprint/Docs update
```

## Workflow Lanes

| Lane | Использовать когда | Обязательные артефакты |
|---|---|---|
| Full Feature Lane | новая фича, новый пользовательский сценарий, новый публичный URL, неясные требования | Issue, AC, Analyst spec при необходимости, Design/ADR по gates, Codex Plan, QA checklist |
| Fast Fix Lane | небольшой баг, текстовая правка, локальный CSS/JS/PHP фикс без нового контракта | Issue или краткое описание, короткий plan, QA smoke |
| Security / Integration Lane | REST/API, AI-сервис, PII, CSRF/CORS/rate limit, внешний сервис | Issue, Architect + QA early review, ADR если gate сработал, security checklist |
| Incident Lane | P0/P1 production defect | Incident Issue, reproduction, impact, fix, smoke, PM summary |

## Gates

### ADR Gate

ADR нужен, если меняется:

- API-контракт;
- хранение данных, инфоблок, свойства инфоблока;
- security-паттерн;
- AI-интеграция или внешний сервис;
- CI/CD, deploy, cache, rollback;
- общий паттерн, который будут повторять другие агенты.

ADR не нужен для локального багфикса, текста, CSS-правки существующего компонента, мелкого рефакторинга без нового решения.

### Design Gate

Designer нужен, если меняется новый UX/UI, навигация, форма, первый экран, визуальный паттерн, адаптивная логика.

Designer не блокирует Fast Fix Lane для точечных багов существующей вёрстки.

### QA Early Gate

QA подключается до разработки, если задача касается:

- CSRF/CORS/rate limit;
- PII/logging;
- AI payload/upstream response;
- REST/API contract;
- public form flow;
- production incident.

### Post-Deploy Gate

После deploy должен быть smoke-check по затронутым URL/API/формам. Issue закрывается только после подтверждения.

## Bitrix Development Rules

### Backend / PHP

- Новый код пишет D7 style там, где это совместимо: `Loader`, `EventManager`, `Asset`, `Context`.
- Legacy `CIBlockElement` допустим в существующих шаблонах и простых интеграциях, но новый shared-код должен изолировать обращения в helpers/services.
- Не добавлять бизнес-логику в `bitrix/`.
- Не держать большие inline-скрипты на страницах: переносить в `local/templates/tacticum/js/`.
- Не смешивать schema/config с публичными страницами: ID и base URL только через `tacticum_config.php` + helpers.

### REST/API

- POST endpoints живут в `local/rest/`, GET endpoints — в `local/api/`.
- `rest_helpers.php` — compatibility facade для CORS/origin, rate limit, CSRF, curl defaults, masking, config access; реализация живёт в `local/lib/Tacticum/Rest/*`.
- `local/rest/endpoint_policy.json` — обязательная taxonomy для REST endpoints: method, action, risk class, CSRF/noindex/legacy policy and future sensitive classes.
- `tacticum_form.php` — текущий эталон тонкого POST endpoint over payload service.
- Контракт лид-форм зафиксирован в `docs/workflow/lead-form-contract.md`; изменения payload, `form_id`, consent/CSRF или error model требуют обновления этого документа.
- `local/api/cases.php` — текущий эталон GET endpoint.
- Для новых API контрактов добавить section в Issue или отдельную spec в `docs/workflow/`.

### Frontend / Template

- Общий frontend-код — в `local/templates/tacticum/js/`.
- Общие стили — в `local/templates/tacticum/styles/` или компонентном `style.css`.
- Подключение через `\Bitrix\Main\Page\Asset::getInstance()->addJs/addCss/addString`.
- Формы должны использовать `data-tacticum-form`, `data-form-id`, `data-tacticum-consent`.
- Inline JS/CSS допустим только как legacy, при доработке выносить в asset-файлы.

### Definition Of Componentized Done

Новая или переработанная публичная страница считается достаточно componentized, если:

- public `index.php` содержит только split prolog, `SetTitle`/page properties/SEO defaults, `IncludeComponent` calls and minimal page params;
- repeated, behavior-bearing or content-heavy blocks live in `local/components/tacticum/*`, component templates or documented service/template boundaries;
- local component has `component.php`, `.parameters.php`, `.description.php` and a template under `templates/.default/`;
- component params are explicit and sanitized; no new global `tacticum_*` functions are declared inside `component.php`;
- component cache/result policy is explicit: child `bitrix:*` cache params, `StartResultCache` usage, managed-cache tags or documented no-cache reason;
- JS/CSS behavior is owned by template assets or component assets, not inline public-page scripts/styles;
- REST/form/upstream payload contracts are unchanged unless the issue uses Security / Integration lane;
- affected pages pass required smoke: `bitrix:check`, `seo:check`, and relevant CSS/browser smoke.

### SEO

- Каждая публичная страница должна иметь уникальный `SetTitle`, `description`, один H1.
- Новые статические URL должны попадать в deploy-generated `sitemap-basic-files.xml` через `tools/static-sitemap-generate.mjs`; repo-owned `sitemap.xml` должен ссылаться на `sitemap-basic-files.xml` и отдельные dynamic sitemap, например `/offer/sitemap.php`.
- Generated sitemap artifacts (`sitemap-basic.xml`, `sitemap-basic-files.xml`, `sitemap-basic-iblock-*.xml`, legacy `sitemap-files.xml`) не коммитить; root `sitemap.xml` и `robots.txt` остаются в Git.
- `robots.txt` должен ссылаться на HTTPS sitemap.
- После изменения публичных URL, canonical, sitemap или robots запускать `npm run seo:check`; после deploy запускать `npm run seo:check:prod`.

## Документы Workflow

- `current-state.md` — фактическая карта приложения на момент аудита.
- `gap-analysis.md` — продуктовые и технологические gaps.
- `codex-plan-template.md` — шаблон плана перед реализацией.
- `sprint-template.md` — шаблон спринта.
- `post-deploy-smoke.md` — чеклист smoke-check.
- `lead-form-contract.md` — контракт `/local/rest/tacticum_form.php` и taxonomy `form_id`.
- `chat-offer-contract.md` — контракт AI chat, `group_id`, prefill и handoff в lead form.
- `chat-api-contract.md` — низкоуровневый contract `/local/rest/tacticum_chat.php`.
- `asset-layout-audit.md` — карта текущих CSS/JS assets, inline-долги и правила дальнейшей верстки.
- `seo-gap-analysis.md` — детальный SEO gap analysis: indexability, 404, structured data, sitemap/robots и social preview.
- `product-marketing-gap-analysis.md` — продуктово-маркетинговый gap analysis: positioning, funnel, CTA, proof, `/offer/` segmentation и lead qualification.
- `design-token-contract.md` — AS IS token contract, guard и правила обновления design tokens handoff.
- `component-state-contract.md` — AS IS component/state contract, guard и правила сохранения/migration behavior-bearing selectors.
- `design-migration-map.md` — AS IS -> TO BE migration map, migration types and gates для дизайн-системной миграции.
- `product-content-schema-contract.md` — typed schema baseline для product seed/fallback data and release fail-fast policy.
- `product-content-target-evidence-refresh-2026-06-07.md` — no-PII evidence refresh for `PTC-WP-01`: local safety guard and HTTP production product-source checks passed; target Bitrix/PHP strict JSON and cache-clear dry-run evidence remain pending.
- `product-content-source-switch-runbook.md` — порядок проверки, переключения и rollback для `products.source=bitrix`.
- `product-taxonomy-seo-packaging-decision-2026-06-04.md` — Sprint 18 approval package для product taxonomy, `/agents/` vs `/aiagents/`, `/price/`, packaging, SEO metadata and evidence mapping.
- `product-cjm-cta-crm-qualification-decision-2026-06-04.md` — Sprint 19 approval package для role CJM, CTA taxonomy, returning-lead path, pilot kits, CRM fallback and no-PII analytics.
- `product-to-be-design-system-decision-2026-06-04.md` — Sprint 20 approval package для TO BE tokens, density/card policy, proof/status UI, diagrams, form/chat/price states and AS IS -> TO BE traceability.
- `product-cjm-usecases-ux-ui-challenge-2026-06-07.md` — docs-only snapshot продуктового challenge по CJM, Use Cases, UX and UI; фиксирует observations/backlog/questions для будущего owner review without closing gaps.
- `content-language-storyline-challenge-gap-analysis-2026-06-07.md` — docs-only source register контентного challenge по подаче, русскому языку, связности, storyline, proof/claims and visible editorial defects.
- `content-language-storyline-challenge-roadmap-2026-06-07.md` — phase roadmap для закрытия `CLS-*` gaps: public label leak, Russian-first glossary, homepage/product copy, `/price/`, `/aiagents/`, `/offer/`, FAQ and governance.
- `content-language-storyline-challenge-issue-backlog-2026-06-07.md` — issue-ready backlog `CLS-WP-01` - `CLS-WP-08` for content/storyline work packages and owner gates.
- `content-language-storyline-public-glossary-2026-06-07.md` — draft Russian-first public glossary, tone rules, CTA language and editor checklist for future public content changes.
- `about-page-ux-content-challenge-gap-analysis-2026-06-07.md` — docs-only source register challenge страницы `/about/`: stale timeline, trust storyline, Russian-first language, team UI, anchors/IDs and page-content ownership.
- `about-page-ux-content-challenge-roadmap-2026-06-07.md` — phase roadmap для закрытия `ABOUT-*` gaps: fast fixes, trust narrative rewrite, team UI, content-storage sync and guards.
- `about-page-ux-content-challenge-issue-backlog-2026-06-07.md` — issue-ready backlog `ABOUT-WP-01` - `ABOUT-WP-08` for `/about/` implementation packages and owner gates.
- `about-page-ux-content-challenge-guard-proposal-2026-06-07.md` — guard proposal for stale `year + Сегодня`, duplicate IDs, missing anchors, misleading footer anchors and about-specific public language checks.
- `plans/2026-06-07-about-page-trust-storyline-implementation.md` — implementation plan for the proof-safe local `/about/` trust-storyline slice.
- `about-page-proof-matrix-owner-review-2026-06-07.md` — no-raw-copy owner-review proof/trust matrix for `/about/`; не разрешает public claims без PM/Sales/Legal approval.
- `about-page-content-ownership-map-2026-06-07.md` — actual source ownership map for `/about/`: PHP partials, Bitrix live rows, team iblock, lead CTA, footer anchors and cache/smoke rules.
- `plans/2026-06-07-about-owner-review-proof-ownership.md` — docs-only plan for the `/about/` proof matrix and content ownership owner-review package.
- `plans/2026-06-07-about-team-ui-accessibility-slice.md` — scoped implementation plan for `/about/` team card readability/accessibility without team data, claims, form or Bitrix row changes.
- `product-frontend-component-hardening-decision-2026-06-04.md` — Sprint 21 approval package для product renderer/component boundary, `/price/`, forms/chat modularity, JS module policy, fixture smoke, FAQ fallback and CSRF accepted-risk triggers.
- `product-security-release-legacy-closure-decision-2026-06-04.md` — Sprint 22 approval package для endpoint sensitivity, rate classes, private proof/document access, CSP enforce path, release evidence discipline and legacy sale alias final mode.
- `product-accepted-risk-monitoring-decision-2026-06-04.md` — Sprint 23 monitoring package для accepted stack, asset, CSRF, CSP and release-evidence baselines with owners and revisit triggers.
- `product-tech-challenge-gap-register-2026-06-04.md` — полный 2026-06-04 register UX/UI/Arch/Components/Stack/Security/Content/SEO/Release gaps and tasks после технологического challenge.
- `product-tech-challenge-execution-roadmap-2026-06-04.md` — phase roadmap, bundles, gates and ready-to-implement checklist для закрытия challenge gaps.
- `product-tech-challenge-execution-board-2026-06-04.md` — issue-ready execution board for Sprint 17-23: work packages, owner approval matrix, do-not-start board and 100% gap coverage index.
- `product-tech-challenge-owner-approval-request-2026-06-04.md` — owner-review request for Sprint 17-23 work packages: required responses by PM/Sales/Legal/SEO/Design/Engineering/Security/QA/DevOps/Content/Analytics.
- `product-tech-challenge-evidence-intake-2026-06-04.md` — no-PII evidence intake templates for WP-01 - WP-09: claims, SEO, CRM/analytics, design/frontend, security/release and target checks.
- `product-tech-challenge-owner-status-tracker-2026-06-04.json` — machine-readable owner/evidence status tracker for WP-01 - WP-09; keeps board status, owner status, gap IDs, blockers and required evidence aligned.
- `product-tech-challenge-issue-backlog-2026-06-04.md` / `.json` — issue-ready backlog for WP-01 - WP-09: tracker import rules, start policies, issue fields, acceptance criteria, verification and evidence requirements.
- `product-tech-challenge-owner-review-runbook-2026-06-04.md` — operational runbook for owner review, `PTC-WP-*` issue import, safe evidence intake, status updates and implementation handoff.
- `sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md` — master sprint roadmap Sprint 17-23 для закрытия 2026-06-04 challenge gaps.
- `bitrix-componentization-gap-analysis-2026-06-05.md` — source register для Bitrix best-practices challenge: file-size budgets, component boundaries, `local/lib`, product renderer, public page entries, `/price/`, forms/chat, REST helpers and architecture guards.
- `bitrix-componentization-execution-roadmap-2026-06-05.md` — phase roadmap для закрытия `BPC-*` gaps: guardrails, product page component, public page thinning, `/price/`, service layer, REST split, forms/chat and CSS.
- `bitrix-componentization-issue-backlog-2026-06-05.md` — issue-ready backlog `BPC-WP-01` - `BPC-WP-09` для последовательной реализации componentization gaps.
- `offer-example-seed-runbook.md` — запуск и контроль CLI-сидера synthetic offer examples для `/offer/`.
- `local-public-browser-error-challenge.md` — challenge `/local`, публичной части и browser zero-error gate.
- `release-signoff-gates.md` — release sign-off gates для deploy smoke, success-flow, Метрики, config sync, Bitrix admin, staff sale and Sprint 22 security-sensitive future gates.
- `release-manual-gates-handoff-2026-06-07-product-first.md` — owner-ready handoff для оставшихся product-first release manual gates: `manual-success-flow`, `metrika-goals`, `bitrix-admin`.
- `rest-response-contract-decision.md` — решение по сохранению доменных success/error response shapes.
- `sprints/` — snapshot-ы спринтов.

## Static Guards

- `npm run bitrix:check` — guard для Bitrix architecture: thin/lazy `init.php`, отсутствие direct `bitrix:*` в public page entries, отсутствие component-level global helper functions, local component metadata/cache policy, `/offer/` service/cache hardening и footer modal component.
- `npm run component:states:check` — deterministic source-level fixture guard для product blocks/degraded state, `/price/` team builder, forms, chat and FAQ/content wrappers; also validates `component_wrapper_policy.json` for `content.list`, `content.detail` and `faq.section` and product block policy evidence; запускается в PR/deploy lifecycle.
- `npm run config:runtime:check` — Bitrix/PHP runtime check для ignored `tacticum_config.php`: health scopes, iblock IDs, product source, endpoint path explicit/default status, CSP mode and REST summary without secret values.
- `npm run gaps:known` — PM/QA guard для текущего известного хвоста: code-level open gaps, pending release gates, legacy inventory и post-deploy/cache smoke.
- `npm run product:content:cache-clear:dry-run` / `npm run product:content:cache-clear` — Bitrix/PHP helper для проверки и очистки product content cache dir plus product/FAQ managed-cache tags перед switch/rollback.
- `npm run content:public-cache-clear:dry-run` / `npm run content:public-cache-clear` / `npm run content:public-cache-clear:json` — Bitrix/PHP helper для ручной очистки public rendered cache после footer/menu/template/page-content deploy: `bitrix:menu`, `news.list`, `news.detail`, composite HTML, managed cache and template CSS/JS cache.
- `npm run product:content:schema:self-test` / `npm run product:content:schema:negative-test` / `npm run product:content:schema:check` — локальные Node guards для typed product seed/fallback schema без PHP/Bitrix; self-test checks validator logic, negative-test proves invalid fixture failure, schema check validates `product_data/*.php`.
- `npm run product:content:target-evidence:self-test` / `npm run product:content:target-evidence:check -- --file=/path/to/evidence.json` — локальный validator для safe JSON output from target `product:content:check:strict:json`; проверяет structure, source/schema, product rows, empty errors/missing blocks/schema issues and no raw/PII-like evidence keys.
- `npm run content:public-hygiene:self-test` / `npm run content:public-hygiene:check` — source-level editorial guard для public labels: блокирует возврат `Product fit`, `Use cases`, `Security / procurement`, page-content service labels and mixed internal headings in product/page-content source files; также проверяет mapper/runtime normalization для legacy Bitrix `fits/not_fits/start`, product FAQ and page-content rows.
- `npm run content:public-hygiene:rendered:self-test` / `npm run content:public-hygiene:rendered:prod` / `npm run content:public-hygiene:rendered:prod:json` — Chrome-free rendered HTML guard для public labels after deploy/cache refresh; сканирует публичные страницы на visible `Product fit`, `fits`, `not_fits`, `start`, `Use cases`, `Security / procurement` and page-content service labels. JSON variant печатает safe `content-public-hygiene` evidence для release sign-off. Входит в `release:product-first:prod-check`, но не в локальный `product:content:safety:check`.
- `npm run content:storage:governance:check` — static guard для content-storage target: `clients` registry, `feedback/clients` PRODUCT relations, product FAQ `faq` first-read, proof public-render gate, FAQ fallback retirement approval, services fallback removal, `/agents/` vs `/aiagents/` boundary tooling, page-content schema tooling, runbook/scripts presence and target evidence FAQ source contract; на production без `docs/workflow` проверяет embedded safe baselines вместо repo docs.
- `npm run content:storage:audit` / `npm run content:storage:audit:strict` — Bitrix/PHP aggregate audit для configured iblocks, active/total counts, PRODUCT relation coverage, product FAQ/proof relation counts, proof public-render counts, page-content schema readiness and optional public API counts without raw content/PII.
- `npm run content:storage:faq:migrate` / `npm run content:storage:faq:migrate:apply` — Bitrix/PHP dry-run/apply migration that seeds product FAQ rows from `product_data/*.php` into `faq` with `PRODUCT` relation.
- `npm run content:storage:services:seed` / `npm run content:storage:services:seed:apply` — Bitrix/PHP dry-run/apply seed for the target six `services` catalog cards without PHP template fallback.
- `npm run content:storage:proof:approval-template` — Bitrix/PHP generator for a no-raw-copy owner approval draft from active `cases`/`feedback`/`clients` IDs; useful on production where `/docs` is not deployed.
- `npm run content:storage:proof:tagging-proposal` — Bitrix/PHP generator for a proposed no-raw-copy product tagging draft; reads proof fields internally, outputs only IDs, product codes, decisions and short reasons, with `public_render_approved=false`.
- `npm run content:storage:proof:tagging-apply` / `npm run content:storage:proof:tagging-apply:apply` — Bitrix/PHP dry-run/apply path for owner-approved `cases`/`feedback`/`clients` PRODUCT tags; requires approved no-raw-copy JSON and changes only the relation.
- `npm run content:storage:proof:public-render-apply` / `npm run content:storage:proof:public-render-apply:apply` — Bitrix/PHP dry-run/apply path for owner-approved `PUBLIC_RENDER_APPROVED` proof flags; requires approved no-raw-copy JSON, verifies current PRODUCT tags and changes only the public-render gate.
- `npm run content:storage:aiagents-boundary:check` — static guard that keeps `/agents/` as the product page and `/aiagents/` as the Telegram demo/prototype service route.
- `npm run content:storage:page-content-model:check` / `npm run content:storage:page-content-model:approved-check` / `npm run content:storage:page-content-model:approved-template` — validates the draft or schema-only approved structured `page_sections/page_blocks` model, can write the approved JSON to `/tmp` on production without `/docs`, and rejects narrow iblocks/raw HTML/JSON blob storage for generic page sections.
- `npm run content:storage:page-content:migrate` / `npm run content:storage:page-content:migrate:apply -- --model=/path/to/approved.json` — Bitrix/PHP dry-run/apply schema migration for `page_sections/page_blocks`; `--apply` requires `status=approved` and architect/content/frontend/qa/seo owner gates true, seeds no copy and changes no public runtime.
- `npm run content:storage:page-content:seed` / `npm run content:storage:page-content:seed:apply` — Bitrix/PHP dry-run/apply shadow seed for wave 1 page sections (`/services/`, `/price/`, `/contacts/`, `/offer/`) into `page_sections/page_blocks`; rows stay `MIGRATION_STATUS=shadow`, keep `FALLBACK_PARTIAL`, print no raw copy in logs and change no public runtime.
- `npm run content:storage:page-content:seed:wave2` / `npm run content:storage:page-content:seed:wave2:apply` — same shadow-only seed/update path for wave 2 (`/`, `/about/`, `/calculator/`, `/aiagents/`). This seeds editor rows only; it does not promote sections to live, does not change public rendering and does not approve fallback retirement.
- `npm run content:storage:page-content:live-approval-template:wave2` / `npm run content:storage:page-content:live-approval:check` / `npm run content:storage:page-content:live-apply` — no-raw-copy owner-gated path for scoped `MIGRATION_STATUS` promotion/demotion only; it does not set `page_content.source=bitrix` and does not retire PHP fallback partials. Use the wave 2 template to avoid mixing already-live wave 1 sections into a new approval file.
- `npm run content:storage:page-content:fallback-retirement-template` / `npm run content:storage:page-content:fallback-retirement-template:wave2` / `npm run content:storage:page-content:fallback-retirement:check` — no-raw-copy owner-gated path for deciding whether PHP fallback partials may be retired after `page_content.source=bitrix`, audit, SEO and targeted browser smoke; it does not remove files or change runtime by itself. Use the wave 2 template to avoid mixing already-retired wave 1 partials into a new approval file.
- Page-content runtime foundation is live-only and config-gated: default `page_content.source=fallback`; Bitrix rendering requires `page_content.source=bitrix` and section `MIGRATION_STATUS=live`, while PHP fallback partials remain available until approved retirement.
- `npm run page-content:source:http:fallback:prod` / `npm run page-content:source:http:prod` — Chrome-free server HTTP checks for page-content rendered source markers before and after `page_content.source=bitrix`; default scope remains wave 1 live pages.
- `npm run page-content:source:http:wave2:fallback:prod` / `npm run page-content:source:http:wave2:prod` — scoped Chrome-free source marker checks for wave 2 pages. Use fallback mode after shadow seed and before live approval; use bitrix mode only after live approval/apply. `/calculator/` expects the `calculator-chat-outcome` template in bitrix mode to preserve the chat surface.
- `npm run product:content:safety:check` — aggregate local safety guard for product content schema self-test + negative fixture + target evidence validator self-test + public content hygiene + content-storage governance + seed/fallback schema check; included in PR check, deploy lifecycle guard and `release:product-first:prod-check`; product/content draft guards do not require `/docs` on production.
- `npm run product:content:check:strict:json` — target Bitrix/PHP evidence mode для strict product content check: source, schema version, product rows, missing blocks and `schema_issues` без raw content/PII.
- `npm run product:content:switch-readiness:prod` — HTTP/readiness guard перед переключением `products.source=bitrix`: проверяет health `products` scope, rendered `data-product-source=bitrix` and required product blocks.
- `npm run release:manual-gates:helper` — read-only helper для ручных release gates: читает текущий sign-off draft, показывает pending `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream`, next actions and safe evidence skeletons без PII; если `docs/` не выгружен на production, работает в standalone skeleton mode без draft-контекста.
- `npm run release:signoff:self-test` — regression guard для release sign-off checker; обязателен при изменении evidence gates, manual/security-sensitive evidence rules or checker behavior.
- `npm run sale:sunset:check` — guard для legacy sale aliases: до `2026-09-30` подтверждает runway, после sunset требует final alias mode decision before deploy.
- `npm run manual:success-flow:helper` — read-only helper для controlled `manual-success-flow`: генерирует marker-based payload/browser/curl templates and safe evidence skeleton для default form, modal form, AI chat and prefill без отправки запроса; browser output отдаёт sanitized summary без raw response/body.
- `npm run metrika:goals:helper` — read-only helper для `metrika-goals`: показывает expected goals/events, проверяет deployed JS taxonomy, даёт owner checklist, browser observer snippet and safe evidence skeleton без доступа к кабинету Метрики.
- `npm run bitrix:admin:gate-helper` — read-only helper для `bitrix-admin`: выдаёт authenticated admin/public toolbar checklist, browser observer snippet and safe evidence skeleton без логина, запросов, cookie/session data.
- `npm run staff:sale:gate-helper` — helper для controlled `staff-sale-upstream` gate: генерирует staff-order payload, curl template и safe evidence block без отправки запроса.
- `npm run legacy:sale:inventory:logs` — aggregate-only parser для production access logs по legacy sale aliases; выводит endpoint/method/status/day counts без IP, query, referrer, cookie, user-agent и raw log lines.
- `npm run product:gaps:check` — guard для AS IS / TO BE product gap closure: сверяет source backlog `14-gap-backlog-and-decision-register.md`, master plan and `16-gap-closure-action-register.json`, чтобы каждый non-closed gap имел owner, next action, blocker/evidence model, review artifact coverage and package script.
- `npm run product:challenge:board:check` — guard для 2026-06-04 challenge execution board: сверяет, что все source gap IDs из `product-tech-challenge-gap-register-2026-06-04.md` покрыты в issue-ready board and no unknown IDs are referenced.
- `npm run product:challenge:approval:check` — guard для owner approval/evidence package: сверяет покрытие WP-01 - WP-09, обязательные owner sections, statuses and no-PII evidence rules.
- `npm run product:challenge:owner-status:check` — guard для machine-readable owner status tracker: сверяет WP-01 - WP-09 с execution board, покрытие всех 63 challenge gap IDs, допустимые статусы, blockers, evidence requirements and no raw-evidence keys.
- `npm run product:challenge:issue-backlog:check` — guard для issue-ready backlog: сверяет `PTC-WP-01` - `PTC-WP-09` с owner status tracker, start policies, issue statuses, owners, gaps, required sections and 63 gap ID coverage.
- `npm run product:challenge:check` — aggregate guard для всего 2026-06-04 challenge package: board, approval/evidence, owner status tracker and issue backlog.
- `npm run bitrix:check` — guard для Bitrix architecture and BPC guardrails: thin/lazy `init.php`, no eager product runtime/renderers/migrations in global bootstrap, `local/lib/Tacticum` autoload boundary, shared content/API repository boundary, product block policy, offer request boundary over Bitrix Context, local component metadata, local component cache/result policy, no component-level global helper functions, no direct public `bitrix:*` content calls, enforced REST endpoint policy/services and `local/rest/index.php` admin-only/noindex/private route markers, line budgets with current BPC allowlist, offer service/cache markers and footer modal component. Known remaining limitation: it does not yet enforce future-domain repository coverage automatically.
- `npm run template-styles:check` — guard для CSS retirement и template public asset hygiene, включая запрет возврата Remixicon demo HTML в `local/templates/tacticum/fonts/`.
- `npm run design:tokens:check` — guard для AS IS token contract: сверяет `05-design-tokens-as-is.json` с Tailwind theme, `global.css`, `forms.js` и package script.
- `npm run design:components:check` — guard для AS IS component/state contract: сверяет `07-component-state-contract.json` с behavior-bearing templates/JS и package script.
- `npm run design:migration:check` — guard для AS IS -> TO BE migration map: проверяет покрытие всех component ids из `07-component-state-contract.json`, migration types, risk gates and package script.
- `npm run design:handoff:check` — aggregate guard для design handoff package: запускает token/component/migration checks и проверяет полноту `01`-`09`, README references, workflow docs and scripts.

## Definition Of Ready

Задача готова к реализации, если:

- выбран lane;
- есть цель и acceptance criteria;
- указаны affected files/areas;
- gates проверены;
- для Security / Integration Lane есть QA + Architect early review;
- для Full Feature Lane есть spec/design/ADR там, где это нужно.

## Definition Of Done

Готово значит:

- код/документация обновлены;
- PR checklist пройден;
- QA или smoke-check выполнен;
- deploy выполнен, если нужен;
- post-deploy smoke-check выполнен;
- gap-analysis/sprint/ADR обновлены, если состояние изменилось.
