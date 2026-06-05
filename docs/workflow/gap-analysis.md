# Gap Analysis — tacticum.ru

Дата аудита: 20.05.2026
Дата последнего обновления: 05.06.2026

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

## Current Bitrix Componentization Challenge Layer — 05.06.2026

Свежий Bitrix best-practices challenge зафиксирован отдельным техническим слоем:

- source register: `docs/workflow/bitrix-componentization-gap-analysis-2026-06-05.md`;
- execution roadmap: `docs/workflow/bitrix-componentization-execution-roadmap-2026-06-05.md`;
- issue backlog: `docs/workflow/bitrix-componentization-issue-backlog-2026-06-05.md`;
- Codex plan: `docs/workflow/plans/2026-06-05-bitrix-componentization-challenge-documentation.md`.

Этот слой покрывает 100% выявленных на 05.06.2026 engineering gaps по Bitrix componentization, file-size budgets, D7/service layer, REST/API boundaries, frontend modules, CSS ownership and architecture guards. После локальной реализации все известные `BPC-*` implementation gaps закрыты кодом и guard evidence; единственный оставшийся хвост — `BPC-GUARD-001` в статусе `accepted-monitor` для будущего расширения guardrails на новые домены.

### Challenge Verdict

Текущее решение больше не выглядит как бесконтрольный Bitrix legacy. `init.php` тонкий и не тянет product runtime / one-off migrations на каждый запрос; публичные entry points из sitemap являются короткими orchestrators поверх `tacticum:*`; `/platform/`, `/agents/`, `/dev/`, `/forum/` используют two-phase `tacticum:product.page`; hero и product lead CTA вынесены в `tacticum:product.hero` / `tacticum:product.lead.cta`; оставшиеся product blocks зафиксированы в `product_block_policy.json` как accepted nested-template boundaries. `/price/`, forms, chat and CSS split закрыты локально. Product/content, offer, SEO, REST, `calcrequests.*` and shared content/API reads управляются через `local/lib/Tacticum` slices and compatibility facades. `component_cache_policy.json`, `component_wrapper_policy.json`, `component:states:check`, `bitrix:check`, `seo:check`, `rest:endpoints:check` и deploy/PR wiring теперь закрывают текущие component/cache/state boundaries.

### Current Gap Coverage

| Cluster | Gap IDs | Current Risk |
|---|---|---|
| Architecture / Bitrix layering | `BPC-ARCH-001` - `BPC-ARCH-005` | Closed locally: product shell/block policy, service/facade slices, lazy bootstrap, offer request snapshot and shared content repository are implemented and guarded |
| Components / public pages | `BPC-CMP-001` - `BPC-CMP-005` | Closed locally: public entries are thin, product/page components and wrapper/cache policies are explicit, and component metadata/cache/state guards run in PR/deploy lifecycle |
| Frontend modules / CSS | `BPC-FE-001` - `BPC-FE-004` | Closed locally: `/price/` configurator, forms runtime, chat runtime/surfaces and fixed template CSS split are under current file-size and ownership budgets |
| REST / API / integration | `BPC-REST-001` - `BPC-REST-003` | Closed locally: REST facade/service split, admin-only REST hook route, endpoint taxonomy/risk classes and thin lead/staff payload services are implemented and guarded |
| Guards / maintainability | `BPC-GUARD-001` - `BPC-GUARD-004` | Current BPC guardrails are enforced; `BPC-GUARD-001` remains `accepted-monitor` only for future domain/repository guard expansion |

### Planning Rule

Any future task touching product pages, public page entries, `/price/`, forms/chat JS, `rest_helpers.php`, product/offer helpers, local components or architecture guards must reference affected `BPC-*` IDs from `bitrix-componentization-gap-analysis-2026-06-05.md` and preserve the implemented guardrails from `bitrix-componentization-execution-roadmap-2026-06-05.md`.

New BPC work should start only when a future code/domain change reopens a boundary or extends the system beyond current guard coverage. Do not mark a future `BPC-*` gap closed because docs exist; closure requires code changes, verification commands and owner/review evidence where applicable.

## Current Product Tech Challenge Layer — 04.06.2026

Свежий технологический challenge текущего product-first решения зафиксирован отдельным документальным слоем:

- полный source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`;
- execution roadmap: `docs/workflow/product-tech-challenge-execution-roadmap-2026-06-04.md`;
- owner status tracker: `docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json`;
- issue backlog: `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.md` / `.json`;
- owner review runbook: `docs/workflow/product-tech-challenge-owner-review-runbook-2026-06-04.md`;
- sprint roadmap Sprint 17-23: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`;
- Codex plan: `docs/workflow/plans/2026-06-04-product-tech-challenge-documentation.md`.

Этот слой покрывает 100% выявленных на 04.06.2026 gaps/tasks по UX, UI, Architecture, Components, Stack, Security, Content, SEO and Release. Он не заменяет исторические sprint closure sections ниже и не меняет machine-readable product handoff register `docs/new-big-change/product-vision-handoff/16-gap-closure-action-register.json`.

### Challenge Verdict

Текущее решение является сильным transitional MVP: Bitrix SSR, product-first pages, unified lead CTA, safe analytics and release guards уже дают рабочую основу. Главные риски теперь не в выборе стека, а в зрелости product content governance, structured sales qualification, proof/claims evidence, TO BE design system, frontend modularity and release/security hardening.

### Current Gap Coverage

| Cluster | Gap IDs | Current Risk |
|---|---|---|
| Config/runtime/content source | `CFG-001` - `CFG-006` | `products.source=bitrix` требует schema validation, fail-fast checks, cache/version discipline and config sync evidence |
| UX/product journey | `UX-001` - `UX-010` | Product pages still need role-based CJM, CTA taxonomy, returning-lead path, pilot kits and `/agents/` vs `/aiagents/` decision |
| UI/design system | `UI-001` - `UI-010` | TO BE tokens, density, proof/status UI, diagrams, form/chat states and `/price/` mobile UX are not approved yet |
| Architecture/data/security | `ARCH-001` - `ARCH-012` | Product content governance, renderer ordering, CRM/upstream fields, analytics depth, CSP and release evidence need explicit decisions |
| Components/frontend modules | `CMP-001` - `CMP-008` | Product partial boundaries, price/forms/chat module decomposition and design traceability need planned hardening |
| Stack/build/quality | `STACK-001` - `STACK-007` | Stack should stay Bitrix SSR, but JS module policy, CSS/token pipeline and product schema guards need maturation |
| Content/SEO/claims | `CONTENT-001` - `CONTENT-005` | Packaging, taxonomy, proof/claims, product evidence mapping and metadata need owner approval |
| Security/release/legacy | `SEC-001` - `SEC-003`, `REL-001` - `REL-002` | Sensitive future flows, CSP enforce path, legacy sale alias inventory and future product sign-off discipline remain active |

### Planning Rule

Any future task that touches product pages, product content, forms/CRM, proof/claims, `/agents/` or `/aiagents/`, `/price/`, design system, CSP, frontend modules or release evidence should reference affected IDs from `product-tech-challenge-gap-register-2026-06-04.md` and follow the phase order in `product-tech-challenge-execution-roadmap-2026-06-04.md`.

Implementation planning should use Sprint 17-23 documents under `docs/workflow/sprints/2026-06-04-sprint-*.md`; each sprint lists in-scope gap IDs, gates, acceptance criteria, QA/smoke and risks.

Sprint 17 local progress 04.06.2026: `CFG-001`, `CFG-002`, `CFG-003`, `ARCH-001`, `ARCH-003` and `STACK-004` moved to local `in-progress` baseline through `docs/workflow/product-content-schema-contract.md`, `docs/workflow/product-content-schema-v1.json`, `tools/product-content-schema-check.mjs`, local negative fixture `tools/fixtures/product-content-schema-invalid/platform.php`, target evidence validator `tools/product-content-target-evidence-check.mjs`, npm scripts `product:content:schema:self-test` / `product:content:schema:negative-test` / `product:content:target-evidence:self-test` / `product:content:target-evidence:check` / `product:content:schema:check` / `product:content:safety:check`, typed validation and JSON evidence mode inside `tools/product-content-check.php --strict`, and schema/source-aware product cache keys. This validates schema logic, expected invalid fixture failure, saved target strict JSON evidence shape and Git seed/fallback schema, wires local schema safety into PR check, deploy lifecycle guard and `release:product-first:prod-check`, strengthens live assembled Bitrix page checks, documents fail-fast policy and prevents source/schema changes from reusing old product cache entries; target PHP/Bitrix run evidence, cache-clear dry-run evidence and Legal/Sales claim evidence remain separate Sprint 17 gates.

Sprint 18 decision progress 04.06.2026: `docs/workflow/product-taxonomy-seo-packaging-decision-2026-06-04.md` now records the local approval package for `CONTENT-005`, `UX-004`, `ARCH-010`, `UX-005`, `CONTENT-001`, `CONTENT-003` and `CONTENT-004`. It recommends keeping `Platform / Agents / Dev / Forum` taxonomy, preserving `/agents/` and `/aiagents/` as separate self-canonical routes until SEO approval, framing `/price/` as product implementation/team route rather than license pricing, and using a public/private/blocked packaging matrix. This is decision evidence only: Sales/PM/SEO/Legal approval, keyword validation, product evidence tagging and any route/meta implementation remain blocked or in-progress gates.

Sprint 19 decision progress 04.06.2026: `docs/workflow/product-cjm-cta-crm-qualification-decision-2026-06-04.md` now records the local approval package for `UX-001`, `UX-002`, `UX-003`, `UX-007`, `UX-008`, `UX-009`, `UX-010`, `CMP-003`, `ARCH-005`, `ARCH-006` and `CFG-004`. It defines role-based enterprise CJM, procurement/security journey, product pilot kits, static fit-guide v1 recommendation, CTA taxonomy, returning-lead path, success-state copy targets, CRM fallback decision and no-PII funnel goal map. Runtime form/upstream/analytics behavior was intentionally not changed: current `lead_*` canonical profile remains text fallback inside `task`, and structured CRM fields stay blocked until Sales/upstream/Security approval.

Sprint 20 decision progress 04.06.2026: `docs/workflow/product-to-be-design-system-decision-2026-06-04.md` now records the local approval package for `UI-001` - `UI-010`, `STACK-003` and `CMP-008`. It defines token source recommendation, enterprise density/card/radius policy, palette/gradient boundaries, CSS/token pipeline, hero/page taxonomy, proof/status visual states, architecture/procurement diagram rules, form/chat state matrices, `/price/` mobile direction, icon taxonomy and AS IS -> TO BE traceability. No visual implementation was made; design/legal/QA/frontend approvals and any CSS/JS smoke remain future gates.

Sprint 21 decision progress 04.06.2026: `docs/workflow/product-frontend-component-hardening-decision-2026-06-04.md` now records the local approval package for `ARCH-002`, `CMP-001`, `CMP-002`, `CMP-004`, `CMP-005`, `CMP-006`, `CMP-007`, `CFG-005`, `STACK-002`, `STACK-005` and `SEC-001`. It fixes the v1 product renderer order decision, partial-to-component promotion criteria, preview fixture scope, `/price/` split plan, forms/chat modularity plans, vanilla JS module/test policy, fixture-driven smoke map, FAQ fallback config rule and CSRF accepted-risk triggers. No frontend/runtime refactor was made; implementation requires approval plus baseline/post-change smoke.

Sprint 22 decision/tooling progress 04.06.2026: `docs/workflow/product-security-release-legacy-closure-decision-2026-06-04.md` now records the local approval package for `CFG-006`, `ARCH-007`, `ARCH-008`, `ARCH-012`, `REL-001`, `REL-002`, `SEC-002` and `SEC-003`. It defines endpoint sensitivity and rate classes, IP allowlist/trusted proxy usage rules, private proof/document access blocking model, CSP report-only to enforce checklist, CSP cleanup backlog, release evidence extension rules and legacy sale alias final-mode matrix. `release-signoff-check.mjs` now supports the future gates `csp-enforce`, `sensitive-endpoint-access`, `endpoint-risk-class` and `legacy-final-mode` with safe evidence validators and negative self-tests. No security runtime change was made; CSP enforce, private access and alias finalization remain gated by owner approval and external evidence.

Sprint 23 monitoring progress 04.06.2026: `docs/workflow/product-accepted-risk-monitoring-decision-2026-06-04.md` now records accepted-risk monitoring for `STACK-001`, `STACK-006`, `SEC-001`, `ARCH-007` and `REL-002`. It keeps Bitrix SSR + vanilla JS, current asset guard model, public CSRF posture, CSP report-only and product release sign-off discipline as monitored baselines with owner cadence and concrete revisit triggers. This does not close the risks; it makes future reopen/ADR/Security scope mandatory when a trigger fires.

Execution board progress 04.06.2026: `docs/workflow/product-tech-challenge-execution-board-2026-06-04.md` now converts Sprint 17-23 outputs into nine issue-ready work packages with status, owners, gates, acceptance criteria, verification commands, owner approval matrix, do-not-start board and full coverage index. `npm run product:challenge:board:check` validates that all 63 challenge gap IDs from the source register are covered and that the board references no unknown IDs.

Owner approval/evidence progress 04.06.2026: `docs/workflow/product-tech-challenge-owner-approval-request-2026-06-04.md` and `docs/workflow/product-tech-challenge-evidence-intake-2026-06-04.md` now make Sprint 17-23 transferable to owners. The package defines required responses by owner, allowed approval statuses, cross-owner safe defaults and no-PII evidence intake tables for WP-01 - WP-09. `npm run product:challenge:approval:check` validates work package coverage, owner coverage, status vocabulary and explicit evidence constraints.

Owner status tracker progress 04.06.2026: `docs/workflow/product-tech-challenge-owner-status-tracker-2026-06-04.json` now makes WP-01 - WP-09 owner/evidence status machine-readable without pretending approvals exist. It records board status, owner status, owners, gap IDs, blockers, next actions, required evidence and do-not-start notes; `npm run product:challenge:owner-status:check` validates exact board alignment and all 63 challenge gap IDs.

Issue backlog progress 04.06.2026: `docs/workflow/product-tech-challenge-issue-backlog-2026-06-04.md` and `.json` now convert WP-01 - WP-09 into `PTC-WP-01` - `PTC-WP-09` tracker-ready issues. Each issue has start policy, priority, owners, gap IDs, objective, affected areas, DoR, acceptance criteria, verification, evidence, blockers and do-not-start rules. `npm run product:challenge:issue-backlog:check` validates tracker alignment and all 63 challenge gap IDs.

Owner review runbook progress 04.06.2026: `docs/workflow/product-tech-challenge-owner-review-runbook-2026-06-04.md` now defines the operational flow for owner review, issue import, allowed status transitions, safe evidence intake, update order, implementation handoff and no-go rules. `npm run product:challenge:check` is the aggregate local/CI guard for board, approval/evidence, owner status and issue backlog consistency.

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
| S10-005 | closed | Bitrix admin smoke | 03.06.2026 QA/Admin owner evidence подтвердил authenticated `/bitrix/admin/` и public toolbar на `/price/` без 500/white screen |
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
| S11H-006 | done | Offer catalog service boundary | High-level логика `/offer/` catalog вынесена в `Tacticum\Offer\CatalogService`; старые функции и legacy classes оставлены wrappers |
| S11H-007 | done | Guards and docs | `seo-check`, ADR-008, `current-state`, `gap-analysis` и sprint artifact обновлены |
| S11H-008 | done | Static detail pages | `/about/`, `/policies/` и `404.php` переведены на split prolog; `/policies/` использует `tacticum:content.detail`, а policy migration больше не привязана к hardcoded `ELEMENT_ID=515` |

## Sprint 12 — Final Site Hardening, UX And Release Closure

На 24.05.2026 оставшийся external release handoff и новый финальный hardening scope упакованы в Sprint 12: `docs/workflow/sprints/2026-05-24-sprint-12-final-site-hardening.md`.

| ID | Status | Area | Closure Target |
|---|---|---|---|
| S12-001 | closed | Release sign-off closure | Release sign-off points to commit `46e2521edaa7f24a7e1ab242c4d9cd6626f341f3`; strict sign-off passes with manual gates closed and no PII-like evidence |
| S12-002 | closed | Manual success-flow | 04.06.2026 owner-run controlled production check passed: default/modal forms returned `success=true`, AI chat returned controlled response with masked `group_id`, controlled empty prefill returned expected `404 not_found`; staff-order covered by passed `staff-sale-upstream` |
| S12-003 | closed | Staff-sale upstream | 04.06.2026 controlled staff-order accepted by endpoint and upstream/CRM; release evidence stores `qa_marker` and masked Planka evidence without PII |
| S12-004 | closed | Metrika goals | 04.06.2026 owner evidence confirmed Metrika UI visibility for product/chat/prefill goals plus browser dispatch for form/product form goals; sign-off stores safe observations without raw params or PII |
| S12-005 | closed | Bitrix admin smoke | 03.06.2026 QA/Admin owner evidence подтвердил authenticated admin panel и public toolbar после deploy/cache refresh |
| S12-006 | external handoff | Legacy sale aliases inventory | External access logs/CRM inventory до `30.06.2026`, migration plan до `31.08.2026`, runway до `30.09.2026` |
| S12-007 | done | Top menu | `/services/` больше не подменяет root top menu: children живут в `services/.left.menu.php`, `/offer/` добавлен в dropdown/mobile/footer и блок `Наши услуги` как `Расчет проекта`, top/mobile menu используют `CHILD_MENU_TYPE=left`, `USE_EXT=N`, guard закреплён в `seo:check` |
| S12-008 | done | Code comments | Бессодержательные comments, commented dead markup/code и временные cleanup notes удалены из production PHP/JS/CSS scope; оставлены docs, compatibility и vendor/license comments |
| S12-009 | done | `/offer/` UX | `/offer/` list page получил больше воздуха, `bg-gray-50`, белые statistic/filter/cards, увеличенные gaps и mobile-safe метрики карточек |
| S12-010 | done | Bitrix component framework | Финальный challenge закреплён static guards: thin public entries, local component metadata, no direct `bitrix:*` page calls, menu architecture, chat page assets |
| S12-011 | done | JS/CSS optimization | `chat-agent.js` подключается только на chat pages через `tacticum_page_assets=chat`; CSS rebuild выполнен; неиспользуемые Google Fonts/Readdy origins удалены |
| S12-012 | done | Mobile/adaptive | `/offer/` mobile layout доработан; header/menu breakpoint переведён на `lg`, mobile menu стал scroll-safe для landscape, footer grid расширяет длинные контакты до `xl`; browser/visual smoke остаётся post-deploy gate для всех публичных URL |
| S12-013 | done | Page speed | Убраны внешние font/image origins, remote offer detail background, лишняя загрузка chat JS на non-chat pages; non-hero images получили lazy/async |
| S12-014 | done | Product Bitrix content verification | 03.06.2026 `npm run product:content:check` и `npm run product:content:check:strict` passed на target Bitrix/PHP environment; production `npm run seo:smoke` passed with product pages `seo=ok` / `blocks=ok`, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-06-03T06-07-04-201Z/manifest.json` |
| S12-015 | done | Product source marker smoke | 03.06.2026 `npm run product:source:http:prod` passed on production: `/platform/`, `/agents/`, `/dev/`, `/forum/` returned `source=bitrix` and 11 product blocks each; browser smoke remains optional where Chrome/Chromium is installed |

Repository closure refresh 25.05.2026: дополнительных repo/code-level gaps не найдено. Повторно прошли `npm run seo:check`, `npm run css:check`, `npm run template-styles:check`, `npm run config:check`, `npm run sale:sunset:check`, `npm run seo:check:prod`, `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json`, `npm run release:signoff:summary -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` и `npm run release:signoff:self-test`. `npm run dev:preflight` зафиксировал локальное degraded-состояние без PHP CLI; GitHub PHP 8.4 lint остаётся fallback. Production `visual:smoke:prod` / `browser:smoke:prod` для текущего menu/component пакета остаются post-deploy/cache gate.

Product layer MVP 01.06.2026: локально добавлены product pages `/platform/`, `/agents/`, `/dev/`, `/forum/`, общий renderer `local/php_interface/include/product_page.php`, product-first navigation и form taxonomy для `platform-cta`, `agents-cta`, `dev-cta`, `forum-cta`. Срез закрывает первый безопасный слой по product vision gaps `PV-001`, `PV-003`, `PV-004`, `PV-005`, `PV-009`, `PV-012`, `PV-013`, `PV-015` без изменения REST/upstream, `/offer/`, `/price/`, `/calculator/` и `/aiagents/` flows. Локальные checks: `bitrix:check`, `template-styles:check`, `seo:check`, `css:check`, `js:check` прошли; PHP lint не запускался из-за отсутствия локального PHP CLI.

Homepage ecosystem MVP 01.06.2026: главная страница получила ecosystem positioning, hero links на `Platform / Agents / Dev / Forum`, карту `Platform core -> Agents/Dev/Forum`, product fit matrix "ситуация -> продукт -> стартовый шаг", обновленный chat intro и product-aware контекст `home-cta`. Текущий commercial layer сохранён отдельным блоком и продолжает вести в `/offer/`, `/services/`, `/price/`, `/aiagents/`. Срез продвигает `PV-001`, `PV-002`, `PV-003`, `PV-009`, `PV-011`, `PV-012`, `PV-013` and `CJM-001`; визуальный/post-deploy smoke остаётся release gate.

Services delivery layer MVP 01.06.2026: `/services/` получил блок связи внедрения с `Platform / Agents / Dev / Forum` и уточненный `services-cta` context (`lead_product=ecosystem`, `lead_scenario=product-delivery`). Срез продвигает Phase C по reframe existing pages и `PV-020`, сохраняя `/offer/`, `/price/`, `/calculator/`, content list, FAQ и форму без REST/upstream изменений.

Estimate/proof product context MVP 01.06.2026: `/calculator/` и `/offer/` связаны с product-first моделью без изменения risky contracts. Calculator получил product-aware estimate paths и `calculator-cta` context (`lead_product=ecosystem`, `lead_scenario=product-estimate`). Offer catalog/detail получили product relation blocks and offer detail CTA context `lead_product=ecosystem`. Срез продвигает `PV-007` proof, `PV-012` lead qualification и Phase C/D migration map; visual/post-deploy smoke остаётся release gate.

Price product team context MVP 01.06.2026: `/price/` получил product workstreams для `Platform / Agents / Dev / Forum` и `price-cta` context (`lead_product=ecosystem`, `lead_scenario=product-team`) без изменения `price-specialist`, `workers_json`, team presets, `news.list/price/script.js` and staff upstream. Срез продвигает `PV-020` delivery/team linkage while preserving the highest-risk price flow.

AIAgents compatibility bridge 01.06.2026: `/aiagents/` сохранён как compatibility/money URL and не редиректится. Добавлен bridge к `/agents/`, а `aiagents-inline` получает `lead_product=agents`. Canonical/redirect decision для `/aiagents/` vs `/agents/` остаётся открытым SEO decision, но пользовательский путь теперь связан с product-first моделью.

About vendor trust context 01.06.2026: `/about/` получил product/vendor trust positioning and `about-cta` context `lead_product=ecosystem`. Неподтвержденные partner/status claims and vendor logo-style block удалены из публичного слоя и заменены безопасным technology-contours block. Срез продвигает `PV-019` claim hygiene and Phase C trust-page reframe without changing team/vacancies components.

Contacts routing context 01.06.2026: `/contacts/` получил next-step routing для product pilot, delivery, estimate and team paths, а `contacts-cta` context расширен до `lead_product=ecosystem`, `lead_scenario=contact-routing`. Factual contact/legal/map data preserved. Срез продвигает contact/legal hierarchy and `PV-012` lead qualification without changing REST/upstream behavior.

Product FAQ hardening 01.06.2026: `/platform/`, `/agents/`, `/dev/`, `/forum/` получили static product FAQ через общий product renderer и существующий `faq.js` asset. Срез закрывает practical FAQ часть `S05-009` / `S06-009`, продвигает `PV-004`, `PV-014`, `PV-020` и сохраняет claim hygiene: registry, partner, benchmark and guarantee claims не добавлялись. Follow-up schema hardening добавил `FAQPage` JSON-LD из того же product page data array, поэтому видимый FAQ и structured data больше не расходятся.

Product-first release hardening 01.06.2026: добавлены product-first draft sign-off and rollback runbook, post-deploy smoke расширен product URLs/forms/assets checks, а static sitemap expectation в current-state обновлён для `/platform/`, `/agents/`, `/dev/`, `/forum/`. Follow-up automated handoff добавил `npm run release:product-first:prod-check`, который после deploy/cache refresh агрегирует production sitemap/SEO smoke, browser console action smoke, focused `/price/` smoke, product-first draft validation and product-first `gaps:known` summary. Срез закрывает practical docs/tooling часть `S07-009` / `S07-010`; Metrika evidence закрыта owner evidence 04.06.2026.

Product-first CI/deploy smoke coverage 01.06.2026: PR/deploy workflows теперь включают `/platform/`, `/agents/`, `/dev/`, `/forum/` в PHP lint, convention scans and production rsync; deploy lifecycle показывает старый external хвост через `gaps:known` and проверяет переносимый product-first draft sign-off. `seo:check` блокирует выпадение product canonical paths and product nav/footer links, а `visual-smoke` рендерит product pages by default, validates product `SoftwareApplication` + `FAQPage` schema under rendered SEO mode and requires FAQ toggle action on them during browser/action smoke. Срез снижает риск, что product-first страницы существуют в repo, но не попадают в deploy/smoke.

Product CTA scenario qualification 01.06.2026: `tacticum:lead.cta` получил optional `SCENARIO_OPTIONS` для controlled `lead_scenario` select, product renderer пробрасывает эти варианты, а `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь позволяют пользователю выбрать безопасный сценарий следующего шага без новых required fields, JS/CSS, upstream changes or analytics params. `/local/rest/tacticum_form.php` мапит известные scenario slugs в readable task labels без изменения response shape. `seo:check` guard фиксирует наличие scenario qualification на product pages. Срез продвигает `PV-012` and `PV-020`, но strict form success-flow остаётся external release gate.

Product lead qualification profile 01.06.2026: `/local/rest/tacticum_form.php` now normalizes existing `lead_*` payload into an internal canonical profile (`product_interest`, `use_case_interest`, `deployment_interest`, funnel entry/stage, CTA, budget, timeline, industry, offer). This advances `ARCH-003` as an approved fallback: sales-readable qualification is no longer assembled directly from raw request fields, but top-level structured fields are intentionally not forwarded to upstream before CRM/upstream contract approval. `seo:check` guards that canonical profile exists and blocks accidental upstream forwarding of `product_interest`, `use_case_interest`, `deployment_interest`.

Product funnel analytics 01.06.2026: `analytics.js` and `forms.js` now emit first-pass product funnel events without PII: product page/router view, product CTA click, product form submit/success/error. This advances `ARCH-004` and `PTC-013` as a code-level taxonomy slice. Events use allowlisted `product`, `page_role`, controlled `scenario`, `form_id`, endpoint/status/code and `page_path`; budget, timeline, offer title/code, industry, user message and contact fields remain excluded. `seo:check` guards event presence and controlled-value allowlist, while Метрика goal configuration/evidence remains an external release gate.

Product block locator and preview workflow 01.06.2026: product renderer now exposes stable `data-product-block` markers for core AS IS product sections, `seo:check` guards the taxonomy, `visual-smoke` records rendered `productBlocks` / `productBlockErrors`, release sign-off validation blocks product SEO evidence when a rendered product page misses a required block, and `npm run product:block-previews` captures per-block PNG previews into `product-blocks/*.png`. This closes decision-register `ARCH-005` as a lightweight screenshot workflow and advances architecture-target `ARCH-008` / `PTC-017`: designers and QA can target and screenshot `hero`, `fit-guide`, `content-section`, `architecture`, `use-cases`, `comparison`, `procurement`, `rollout`, `proof`, `faq` and `lead-cta`. TO BE token/component system and any future Storybook/local component preview remain separate design/frontend decisions.

Design token AS IS contract 01.06.2026: `docs/design-system-handoff/05-design-tokens-as-is.json` now has a guarded `contract` section with implemented Tailwind tokens, observed CSS/JS candidates and known drift. `tools/design-token-contract-check.mjs` / `npm run design:tokens:check` verifies the JSON against `tailwind.css`, `global.css`, `forms.js` and `package.json`. This advances `PTC-016`, `UIX-010` and `UI-001` by giving Designer + Frontend a reproducible AS IS token baseline. It does not close `UI-001`: TO BE source of truth, naming, Figma variables and final Tailwind/global CSS mapping still require a design/frontend decision.

Component/state AS IS contract 02.06.2026: `docs/design-system-handoff/07-component-state-contract.json` now records behavior-bearing component contracts for navigation, modal, lead CTA, chat, FAQ, `/price/` team builder and product page blocks. `tools/component-state-contract-check.mjs` / `npm run design:components:check` verifies source files, selectors and required state coverage. This advances `UI-002`, `UI-003`, `UI-007`, `UIX-007`, `UIX-008`, `UIX-009` and `PTC-015` by giving Designer + Frontend a reproducible AS IS component/state baseline. It does not close those gaps: final visual anatomy, Figma variants, `/price/` mobile UX and chat state visuals still require design decisions.

Design migration map 02.06.2026: `docs/design-system-handoff/08-as-is-to-be-migration-map.json` now maps all checked AS IS component ids to preliminary TO BE component names, migration types, risk levels and gates. `tools/design-migration-map-check.mjs` / `npm run design:migration:check` verifies coverage against `07-component-state-contract.json`. This advances `UI-002`, `UI-003`, `UI-006`, `UI-007`, `ARCH-002` and `PTC-015` from raw inventory toward implementation planning. It still does not close them: Designer + Frontend must approve final names, variants, state visuals and any contract migrations before implementation.

Design handoff completeness 02.06.2026: `docs/design-system-handoff/09-to-be-design-work-order.md` now defines required TO BE design deliverables, state matrix, red lines and acceptance criteria. `tools/design-handoff-check.mjs` / `npm run design:handoff:check` validates the full handoff package, including `01`-`09`, README references, workflow docs and design guard scripts. This advances `UI-001` - `UI-008`, `PTC-016` and Sprint 02 readiness from scattered docs to a transferable package. It does not close the design gaps until Designer + Frontend approve actual Figma variables, component variants, page templates and migration decisions.

Product gap closure governance 02.06.2026: `docs/new-big-change/product-vision-handoff/15-gap-closure-master-plan.md` and `16-gap-closure-action-register.json` now turn the open AS IS / TO BE product backlog into phased execution work. `tools/product-gap-closure-check.mjs` / `npm run product:gaps:check` verifies that every non-closed gap from `14-gap-backlog-and-decision-register.md` has owner, closure mode, target checkpoint, next action and acceptance evidence; blocked gaps must keep explicit blockers and external-evidence closure mode. This closes the gap-governance layer, but does not close product/legal/design/SEO/release gaps until their evidence is approved and statuses are updated.

Local decision baseline 02.06.2026: `17-local-gap-decision-briefs.md` advances `PB-004`, `CJM-004`, `CJM-005`, `UI-004`, `UI-008` and `SEO-TOBE-005` from open questions to concrete review artifacts: Dev workflows, CTA matrix, returning-lead journey, architecture diagram brief, icon taxonomy and metadata draft. The gaps remain non-closed because PM/UX/Designer/SEO approval and any resulting implementation/evidence are still required.

Phase 1 product decision baseline 02.06.2026: `18-phase-1-product-decision-review-pack.md` advances `PB-001`, `PB-002`, `PB-003`, `PB-005`, `PB-006`, `PB-007`, `PB-008`, `CJM-001`, `CJM-002` and `CJM-003` from raw backlog items to a concrete approval package: taxonomy, Platform triggers, Agents/Forum boundary, proof/evidence matrix, safe claims rules, packaging matrix, `/agents/` vs `/aiagents/` options, fit guide review, procurement journey and use-case pilotability. This does not close P0/P1 gaps yet; PM/Sales/Legal/Security/SEO/UX approvals and external evidence are still required before status changes.

Phase 3 architecture/integration baseline 02.06.2026: `19-phase-3-architecture-integration-decision-pack.md` advances `ARCH-001`, `ARCH-002`, `ARCH-003`, `ARCH-004` and `CJM-006` from open architecture questions to a concrete review package: Git vs Bitrix/hybrid content ownership, product partials vs component system, canonical lead profile vs structured CRM/upstream fields, product analytics goal map and Metrika evidence rules. This does not change code contracts; gaps close only after Architect/Frontend/Backend/PM/QA/Analytics approval and, for `ARCH-004`, external Metrika evidence.

Phase 4 SEO/content baseline 02.06.2026: `20-phase-4-seo-content-decision-pack.md` advances `SEO-TOBE-001`, `SEO-TOBE-002`, `SEO-TOBE-003` and `SEO-TOBE-005` from open SEO/content questions to a concrete review package: product cluster map, `/agents/` vs `/aiagents/` options, product proof/case taxonomy, metadata approval matrix and rendered SEO evidence rules. This does not close those gaps; SEO research, PM/Content/Sales approval, canonical decisions and post-deploy rendered evidence are still required.

Phase 5 release/evidence baseline 02.06.2026: `21-phase-5-release-evidence-closure-pack.md` advances `REL-001` - `REL-006`, `ARCH-007` and `ARCH-008` from scattered external blockers to a concrete closure package: product-first deploy smoke, rendered SEO manifest, manual success-flow, Metrika goals, Bitrix admin smoke, legacy alias inventory, strict sign-off and staff/upstream recovery. Bitrix admin, staff upstream, manual success-flow, Metrika goals and strict release sign-off are now closed by owner evidence and commit marker; remaining owners still need legacy alias full-window/source inventory.

Phase 2 design-system approval baseline 02.06.2026: `22-phase-2-design-system-approval-pack.md` advances `UI-001`, `UI-002`, `UI-003`, `UI-005`, `UI-006` and `UI-007` from open design decisions to a concrete approval package: token source of truth, product storytelling components, form/modal/CTA states, proof/status UI, `/price/` mobile UX and chat states. This does not close design gaps; Figma variables/components, migration-map approvals, QA state coverage and Legal/PM proof/status decisions are still required.

Accepted-risk monitoring baseline 02.06.2026: `23-accepted-risk-monitoring-pack.md` gives explicit monitoring rules for `ARCH-006` and `SEO-TOBE-004`: CSP stays report-only until Security approves an enforce baseline, and industry/scenario pages stay noindex/deferred until proof/content readiness justifies indexation. This gives every actionable gap in `16-gap-closure-action-register.json` an artifact, while keeping accepted risks out of false closure.

Post-challenge detail package 02.06.2026: `24-post-challenge-gap-analysis.md`, `25-post-challenge-use-cases-and-cjm.md`, `26-post-challenge-ux-ui-design-system.md`, `27-post-challenge-architecture-components-stack.md` and `28-post-challenge-decision-backlog.md` refine the current AS IS / TO BE challenge into detailed gap analysis, role/use-case/CJM requirements, design-system requirements, architecture/stack decisions and a decision backlog. This advances planning quality for `PB-*`, `CJM-*`, `UI-*`, `ARCH-*`, `SEO-TOBE-*` and `REL-*`, but does not change statuses in the canonical gap register without owner approval and evidence.

Post-challenge sprint wave 02.06.2026: sprint docs `09` - `14` in `docs/new-big-change/product-vision-handoff/sprints/` convert `D-01` - `D-13` into detailed sprint work packages: taxonomy/claims/packaging, pilot kits/CJM/CTA, TO BE design-system approval, architecture/CRM/analytics foundation, implementation readiness and release evidence. This gives every post-challenge decision a sprint home without closing external/product/design gaps prematurely.

Sprint 09 execution bundle 02.06.2026: `sprint-09-review-workbook.md` and `sprint-09-decision-records.md` advance `PB-001`, `PB-002`, `PB-003`, `PB-005`, `PB-006`, `PB-007`, `PB-008`, `SEO-TOBE-002`, `SEO-TOBE-003` and `UI-005` from a sprint plan into concrete review worksheets and draft decision records. Status remains draft/evidence-blocked until PM/Sales/Legal/Security/SEO owners approve the records.

Sprint 09 owner-review readiness 02.06.2026: `sprint-09-approval-request.md` and `sprint-09-evidence-intake.md` complete the local preparation for owner review. The related gaps are now ready for external decision/evidence collection, not closed: PM/Sales/Legal/Security/SEO still need to approve taxonomy, proof/claims, packaging and `/agents/` vs `/aiagents/` decisions before statuses change.

Sprint 10 owner-review readiness 02.06.2026: `sprint-10-review-workbook.md`, `sprint-10-pilot-kit-records.md`, `sprint-10-cjm-cta-records.md` and `sprint-10-approval-request.md` complete local preparation for `D-05` and `D-06`. This advances `CJM-003`, `CJM-004`, `CJM-005`, `CJM-006`, `PB-002`, `PB-004` and `ARCH-003` into concrete owner-review artifacts, but statuses remain non-closed until PM/UX/Sales/Content approve pilot kits and either accept current lead fallback or open Sprint 12 structured-fields scope.

Sprint 11 owner-review readiness 02.06.2026: `sprint-11-review-workbook.md`, `sprint-11-decision-records.md`, `sprint-11-state-matrix.md` and `sprint-11-approval-request.md` complete local preparation for `D-07`, `D-08` and `D-09`. This advances `UI-001` - `UI-008`, `ARCH-002`, `PB-005`, `PB-006` and `SEO-TOBE-003` into concrete design-system owner-review artifacts, but statuses remain non-closed until token/Figma/component deliverables, Frontend feasibility, QA state/smoke review, Legal/PM proof-status approval and Architect diagram approval exist.

Product Bitrix content foundation 02.06.2026: `ARCH-001` moved from Git-only baseline toward the accepted Bitrix target model through ADR-010 and code foundation. New runtime reads Bitrix product content in `auto` mode only when minimum renderable content exists, with fallback to `product_data/*.php`; the CLI migration creates/seeds `products`, `product_blocks` and `product_use_cases` plus product relation properties on existing content iblocks. Manual local migration and ignored config ID sync have been completed, and `product-content-check.php` now gives a repeatable Bitrix runtime check for minimum-renderable records, TO BE block coverage, use cases and relation properties. On 03.06.2026 target Bitrix/PHP environment passed normal and strict product content checks: `source=auto`, rows resolve from `bitrix`, each product has three use cases and no missing TO BE blocks. Production rendered `npm run seo:smoke` also passed on 03.06.2026; product pages are `seo=ok` and `blocks=ok` on desktop/mobile. Production source switch to `products.source=bitrix` completed on 03.06.2026; strict product content check, product source HTTP check and release public precheck passed after cache clear. Production admin-editable V2 retirement completed on 05.06.2026: `--retire-legacy-json` migration passed on production, after-cache-clear strict evidence passed with `admin_model.legacy_json` counters all `0`, product rows `ok`, `source=bitrix`, `use_cases=3` and `schema_issues=0`; `product:content:switch-readiness:prod`, `product:source:http:prod`, `release:public-precheck:prod` and `seo:check:prod` passed. Product Bitrix content verification, source switch and JSON retirement are code/rendered closed; remaining release evidence is manual browser/admin smoke and optional browser automation from an environment with Chrome/Chromium.

Product source marker guard 03.06.2026: product renderer now exposes `data-product-source`; `tools/visual-smoke.mjs` supports `TACTICUM_EXPECT_PRODUCT_SOURCE=bitrix`, and `tools/product-source-http-check.mjs` provides a Chrome-free server check for the same marker plus product block inventory. `npm run product:source:smoke:prod` verifies source through browser-rendered HTML where Chrome/Chromium is available; `npm run product:source:http:prod` is the production-server-safe fallback and does not require `node_modules` or Chrome. Browser attempt on production server confirmed the failure mode when Chrome is absent: install Chrome/Chromium or use the HTTP check. `npm run product:source:http:prod` passed on 03.06.2026: `/platform/`, `/agents/`, `/dev/`, `/forum/` returned `source=bitrix` and 11 product blocks each, closing S12-015.

Product content source switch readiness 03.06.2026: `tools/product-content-switch-readiness.mjs`, `npm run product:content:switch-readiness:prod` and `product-content-source-switch-runbook.md` close the local process gap before `products.source=bitrix`. The readiness helper checks `health_config` products scope, rendered `data-product-source=bitrix` and required product blocks; the runbook defines admin/content review, switch steps, post-switch checks and rollback to `auto`/`fallback`. `tools/product-content-cache-clear.php` / `npm run product:content:cache-clear` adds a reproducible Bitrix/PHP cache clear for product content cache dir and managed tags. Target server PHP lint and dry-run passed on 03.06.2026: source `auto`, TTL `300`, tags `iblock_id_21`, `iblock_id_22`, `iblock_id_23`. Follow-up target run completed product cache clear in source mode `auto`, then passed strict content, source HTTP and public release checks. Final production switch run completed in source mode `bitrix`, then passed cache clear, strict content, source HTTP and public release checks. This closes the product content source-switch gate; post-switch manual release gates were later closed by owner evidence.

Release public precheck 03.06.2026: `npm run release:public-precheck:prod` passed and safely narrowed the remaining external gates without creating leads. It confirms public health/config scopes, product source marker, public Metrika tag, unauthenticated Bitrix admin surface and legacy sale alias headers. Bitrix admin, staff upstream, manual success-flow and Metrika goal visibility were later closed by owner evidence.

Runtime config evidence 03.06.2026: `tools/config-runtime-check.php` / `npm run config:runtime:check` closes the local visibility gap for ignored `tacticum_config.php`. Production run passed after PHP lint and confirmed explicit product config (`products.source=bitrix`, TTL `300`, iblocks `21/22/23`), HTTPS AI base URLs on `api.tacticum.ru`, and documented defaults for `ai.endpoint_paths.*`, `security.csp_mode=report-only` and FAQ section fallback. Release `config-sync` evidence now distinguishes explicit synced keys from defaulted runtime keys without exposing secret values.

Release evidence refresh 03.06.2026 - 04.06.2026: regenerated current production smoke manifests for rendered SEO, warning-aware browser actions, focused `/price/` team preset smoke and CSS-local visual/action checks under `/tmp/tacticum-release-closure-2026-06-03/`. Updated `release-signoff-2026-05-24-post-deploy.draft.json` now points to commit `46e2521edaa7f24a7e1ab242c4d9cd6626f341f3` and passes strict `release:signoff:check`; this closes the local stale-manifest and commit-marker tail. Remaining external handoff is legacy full-window inventory `S12-006`.

Manual release gates helper 03.06.2026: `tools/release-manual-gates-helper.mjs` / `npm run release:manual-gates:helper` closes the local handoff-format gap for the remaining manual gates. It reads the current draft sign-off when available, prints pending `manual-success-flow`, `metrika-goals`, `bitrix-admin` and `staff-sale-upstream` by default, and gives owner-specific next actions plus safe evidence skeletons that match `release-signoff-check.mjs`. If `docs/` is absent on production, it falls back to standalone skeleton mode instead of failing on missing draft JSON. Production server run from `/home/bitrix/www` on 03.06.2026 confirmed default, focused `staff-sale-upstream` and JSON outputs in standalone mode. Bitrix admin, staff upstream, manual success-flow and Metrika goals were later closed by owner evidence.

Manual success-flow helper 03.06.2026 - 04.06.2026: `tools/manual-success-flow-helper.mjs` / `npm run manual:success-flow:helper` closes the local helper gap for owner-run default form, modal form, AI chat and prefill checks. It generates controlled payloads, browser snippet, curl templates and evidence skeleton without sending requests; the helper adds a safe `qa_marker` to controlled form/chat messages and returns browser safe summary instead of raw response/body. Staff-order remains delegated to `staff:sale:gate-helper` / passed `staff-sale-upstream` because it has its own rich workers/upstream contract. Owner-run production browser check on 04.06.2026 passed for `qa_marker=manual-smoke-tdckbmex`: default and modal forms returned HTTP 200 `success=true code=ok`, AI chat returned HTTP 200 controlled response with masked `group_id`, and controlled empty prefill returned expected HTTP 404 `not_found`. Release sign-off stores only safe summary; `S12-002` is closed.

Metrika goals helper 03.06.2026 - 04.06.2026: `tools/metrika-goals-helper.mjs` / `npm run metrika:goals:helper` closes the local helper gap for `S12-004`. It prints expected goals/events, checks deployed JS taxonomy for event presence and Metrika counter wiring, emits an owner checklist, browser observer snippet for `tacticum:analytics` events, and gives a safe evidence skeleton with `observed_after`, `goal_observations`, `params_safe` and safe `checked_markers`. Production server run from `/home/bitrix/www` on 03.06.2026 confirmed default, source-check/evidence, browser and release helper outputs; source-check found `missing_events: -`, counter `103471113`, `reachGoal`, QA browser event and HTTPS Metrika tag. Owner browser debug on 04.06.2026 confirmed safe `ym(103471113, reachGoal, ...)` dispatch for `/price/` form/product form submit and success events. Later 04.06.2026 Metrika UI evidence confirmed product, chat and prefill goals in counter `103471113`; sign-off stores only safe observations and `S12-004` is closed.

Bitrix admin gate helper 03.06.2026: `tools/bitrix-admin-gate-helper.mjs` / `npm run bitrix:admin:gate-helper` closes the local helper gap for `S12-005`. It prints an authenticated admin/public toolbar checklist, a read-only browser observer snippet and a safe evidence skeleton without logging in, sending requests or reading cookie/session data. Production server run from `/home/bitrix/www` on 03.06.2026 confirmed default, evidence-only and release helper outputs in standalone mode because `docs/` is not deployed. Owner evidence from Ivan Monakhov on 03.06.2026 confirmed authenticated `/bitrix/admin/` and public toolbar on `/price/` without 500/white screen after deploy/cache refresh; `S12-005` is closed.

Staff-sale upstream gate hardening 03.06.2026 - 04.06.2026: `tools/staff-sale-gate-helper.mjs` and `npm run staff:sale:gate-helper` now generate a controlled staff-order payload, browser snippet, curl template and safe evidence block for owner-run `staff-sale-upstream` checks. The helper adds a safe letters-only `qa_marker` to the controlled message and evidence skeleton, so CRM/upstream owners can find the test lead without storing contact fields or raw payload/message text in release evidence. Browser-controlled `/price/` staff-order POST returned HTTP 200 `success=true` on 04.06.2026 for `qa_marker=staff-smoke-knfyzyhe`, `team_preset=mvp`, `workers_count=3`, monthly budget and exact end date. Upstream/CRM evidence confirmed the team summary, preset, budget and end date; sign-off stores masked Planka evidence `planka-card-masked-4804` and `S12-003` is closed.

Legacy sale inventory automation 03.06.2026: `tools/legacy-sale-access-log-inventory.mjs` and `npm run legacy:sale:inventory:logs` provide the missing access-log aggregation tool for `S12-006`. It reads regular and `.gz` access logs, matches exact legacy alias paths, filters the `2026-05-24` - `2026-06-30` window and outputs only safe aggregates: source label, endpoint, method, status, count, first/last seen and daily counts. This closes the local tooling part of `S12-006`, but not the external gap: production access-log report, CRM/upstream report, owner assignment for any non-zero consumer and migration decision remain required.

Legacy sale interim access-log evidence 03.06.2026: production run for `2026-05-24` - `2026-06-03` scanned `79384` nginx access-log lines and found `0` matched hits for both legacy aliases. This narrows `S12-006` risk but does not close it: full-window repeat after `30.06.2026`, CRM/upstream source report and final alias decision are still required.

Product fit guide implementation 01.06.2026: product renderer получил reusable `fit_guide` block, `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь имеют decision-support секцию "подходит / не подходит / с чего начать", а homepage получил product fit matrix для первичного выбора. Срез продвигает `CJM-001`, `UIX-001`, `PTC-003`, `PTC-004`, `PV-003`, `PV-004` and `PV-011` без изменения REST/upstream/forms/analytics contracts. `seo:check` guard фиксирует наличие fit guide на product pages и homepage fit matrix; production visual/browser smoke remains release gate.

Product security/procurement path 01.06.2026: product renderer получил reusable `procurement` block, а `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь имеют safe-copy секции для architecture/security/procurement review. Срез продвигает `CJM-002`, `UIX-005`, `PTC-004`, `PB-006`, `PV-006`, `PV-020` without adding downloads, new forms, REST endpoints, analytics params or risky registry/certification/КИИ/SLA/ПАК/automation-rate claims. `seo:check` guard фиксирует наличие procurement block на product pages; legal/security evidence and final wording review remain required before closing the gap.

Product use-case anatomy 01.06.2026: product renderer получил reusable `use_cases` block, а `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь имеют по 3 pilotable use cases with trigger, owner, pilot input, pilot output and limitation. Срез продвигает `CJM-003`, `UIX-002`, `PTC-003`, `PTC-005`, `PTC-007`, `PB-002`, `PB-004`, `PV-004` and `PV-020` without adding form fields, REST/upstream changes, analytics params or public metrics. `seo:check` guard фиксирует наличие use-case anatomy fields on product pages; PM/content/evidence review remains required before closing the gap.

Product comparison/boundary block 01.06.2026: product renderer получил reusable `comparison` block, а `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь имеют decision-support сравнение текущего продукта, соседних продуктов and service/delivery entries. Срез продвигает `PB-003`, `PTC-006`, `PTC-010`, `UIX-006`, `PV-002`, `PV-004` and `PV-011`; особенно закрывает first-pass boundary copy для `Agents` vs `Forum` without URL/canonical/REST/forms/analytics changes. `seo:check` guard фиксирует наличие comparison block and взаимные Agents/Forum links; PM/SEO message review remains required before closing `PB-003`.

Product rollout delivery model 01.06.2026: product renderer получил reusable rollout block, а `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь отвечают на brief-вопрос "как это внедряется" через безопасные шаги discovery/assessment, pilot, integration/deployment/workflow alignment and rollout/support decision. Срез продвигает `PV-020`, `PV-004` and S05/S06 acceptance without adding unapproved pricing, registry, ПАК, SLA or certification claims. `seo:check` guard фиксирует наличие rollout model на product pages; production visual/browser smoke remains release gate.

Product proof readiness model 01.06.2026: product renderer получил reusable proof readiness block, а `/platform/`, `/agents/`, `/dev/`, `/forum/` теперь показывают, какие артефакты проверяются на пилоте до появления публичных metrics/cases/logos. Срез продвигает `PV-007`, `PV-014`, `PV-016` and S06-007 as "readiness, evidence pending": product pages объясняют контрольные вопросы, trace, checkpoints, handoff and roadmap, но не публикуют numeric claims, customer proof, benchmark results or regulatory proof. `seo:check` guard фиксирует наличие proof readiness items на product pages.

Product structured data 01.06.2026: `/platform/`, `/agents/`, `/dev/`, `/forum/` получили page-specific `SoftwareApplication` + `FAQPage` JSON-LD via `tacticum_product_page_schema(...)` and `tacticum_apply_seo_defaults(...)`, без risky commercial schema fields (`offers`, `price`, `review`, `aggregateRating`). Срез продвигает `PV-013` product cluster SEO and keeps structured-data governance centralized in SEO helper options. `seo:check` guard validates schema presence, data/schema/render ordering and forbids risky product schema fields; `visual-smoke` now also fails rendered SEO mode if deployed product HTML misses product `SoftwareApplication` or `FAQPage` schema. Follow-up checker regression closed: `release:signoff:self-test` rejects a synthetic `/platform/` rendered SEO manifest with valid generic SEO head but missing `productSchemaSummary`.

Product data layer 01.06.2026: product content for `/platform/`, `/agents/`, `/dev/`, `/forum/` moved out of public page entries into `local/php_interface/include/product_data/*.php`, loaded through allowlisted `tacticum_product_page_data(...)`. Public product `index.php` files now stay thin orchestration files and keep split prolog, page properties, SEO/schema and shared render only. This advances `ARCH-001` and `PTC-014`: the page-local array problem is closed for product pages, but CMS/hybrid ownership remains open if content/proof/case editing must move to Bitrix admin. `seo:check` now validates thin page entries and shared data sources for scenario options, fit guide, procurement, use-case anatomy, comparison, rollout, proof and safe schema inputs.

Product renderer boundary 01.06.2026: `local/php_interface/include/product_page.php` split into bootstrap/helpers/data/schema plus visual block partials in `local/php_interface/include/product_page_blocks/*.php`. This advances `ARCH-002` and `PTC-015`: renderer monolith risk is reduced without changing HTML, CSS, JS, form contracts, REST endpoints or page data. `seo:check` guards that product page bootstrap loads the block taxonomy and that visual render functions do not move back into the bootstrap. A future move from PHP partials to Bitrix local components or previewable component harness remains open if TO BE design-system workflow requires it.

## Sprint 13 — Bitrix Framework Hardening

На 25.05.2026 gaps по результатам Bitrix framework challenge закрыты единым Sprint 13 artifact: `docs/workflow/sprints/2026-05-25-sprint-13-bitrix-framework-hardening.md`; архитектурный паттерн зафиксирован в `docs/adr/ADR-009-bitrix-framework-hardening.md`.

| ID | Status | Area | Closure |
|---|---|---|---|
| S13-001 | done | `init.php` bootstrap | `init.php` стал тонким include/registration bootstrap; helpers вынесены в `site_helpers.php`, `seo_helpers.php`, `calcrequests_rest.php` |
| S13-002 | done | `/offer/` cache/service | `/offer/` catalog получил лёгкие compatibility facades `offer_catalog.php` / `offer_catalog_cache.php`, namespaced `Tacticum\Offer\CatalogRepository` / `CatalogCache`, managed tag/cache dir и очистку после add/update/delete/property-update offer events |
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
| TG-030 | closed as operational guard | P1 | Full Feature | Known gap closure governance | Sprint 14 закрепил известный хвост как машинно проверяемый список: code-level open gaps = 0, external gates видны через `gaps:known`, pending gates требуют `due`, release checker self-test расширен | `tools/known-gaps-check.mjs`, `package.json`, `release-signoff-check.mjs`, `release-signoff-2026-05-24-post-deploy.draft.json`, `release-signoff-gates.md`, `sprints/2026-05-25-sprint-14-known-gap-operational-closure.md` | Нельзя потерять или устно "закрыть" external gates без owner/due/evidence; финальное закрытие проверяется strict release sign-off и `gaps:known:strict` | Remaining external gate requires access-log full-window inventory and CRM/upstream source report for legacy aliases |

## Sprint 14 — Known Gap Operational Closure

На 25.05.2026 известных code-level `open` / `in-progress` gaps нет. Известный хвост доработан как operational closure scope: `docs/workflow/sprints/2026-05-25-sprint-14-known-gap-operational-closure.md`.

| ID | Status | Area | Closure |
|---|---|---|---|
| S14-001 | done | Known gaps visibility | Добавлен `npm run gaps:known`; команда показывает code-level gaps, pending release gates, legacy inventory pending rows и post-deploy/cache smoke хвост |
| S14-002 | done | Pending gate discipline | `release:signoff:draft-check` требует `due` у `pending` gates; текущий draft release sign-off обновлён |
| S14-003 | done | Checker regression | `release:signoff:self-test` расширен негативным кейсом на pending gate без `due` |
| S14-004 | external handoff | External evidence | legacy access logs/CRM inventory остаётся внешним gate с owner/due/evidence rules; strict release sign-off закрыт commit marker и no-PII evidence |

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

External gates из Sprint 14 остаются отдельным хвостом: Метрика, CRM/upstream, access logs и post-deploy release sign-off evidence.

## Sprint 16 — Final Stabilization Closure

Финальный challenge 25.05.2026 зафиксирован в `docs/workflow/final-stabilization-challenge-gap-analysis-2026-05-25.md`.

Вывод challenge: сайт близок к целевому состоянию. Sprint 16 локально закрыл code/docs gaps по карте `/contacts/`, contrast offer detail, calculator/price chat-to-lead handoff, CTA image trust, proof matrix, SEO/CSP decisions и contact/legal hierarchy. Production deploy/cache smoke 25.05.2026 прошёл по rendered/action/price gates, включая новую карту `Тактикум` на `/contacts/`. Real success-flow, staff-order endpoint/upstream, Bitrix admin smoke, Metrika goals и strict release sign-off закрыты owner evidence and commit marker. Сайт всё ещё нельзя считать полностью стабилизированным только до legacy full-window/source inventory.

Новый спринт на команду: `docs/workflow/sprints/2026-05-25-sprint-16-final-stabilization-closure.md`.

| ID | Status | Priority | Area | Closure Target |
|---|---|---|---|---|
| FSC-001 | closed | P1 | `/contacts/` map correctness | Wrong placeholder/constructor state removed; Yandex map widget iframe points to `Тактикум` (`oid=243968538014`), `БЦ Victory Park` is a landmark, legal address is separate |
| FSC-002 | closed | P1 | Offer detail estimate contrast | `/offer/<code>/` estimate block now has explicit gradient background and `text-white` |
| FSC-003 | closed | P1 | Deploy/cache smoke | 25.05.2026 production `visual:smoke`, `browser:smoke`, focused `/price/` smoke and `seo:check:prod` passed; contacts map renders `Тактикум` |
| FSC-004 | closed | P1 | Real success-flow | 04.06.2026 controlled production smoke passed: default/modal forms returned `success=true`, AI chat returned controlled response with masked `group_id`, prefill controlled empty state returned expected `404`; staff-order endpoint/upstream evidence closed separately |
| FSC-005 | closed | P1 | Calculator/price chat-to-lead handoff | Light chat surfaces передают safe summary/scoped `group_id` в целевую CTA form без PII analytics |
| FSC-006 | closed | P1 | Metrika goals | 04.06.2026 owner evidence confirmed Metrika UI visibility and browser dispatch with no PII params in release sign-off |
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
