# Sprint 10 - Release Evidence, Browser Errors And CSS/JS E2E Readiness

Дата: 24.05.2026 - 31.05.2026

## Sprint Goal

Закрыть 100% оставшегося операционного хвоста после SEO/sitemap cleanup: release sign-off evidence, ручные success-flow gates, browser zero-error challenge, CSS/JS e2e readiness, legacy alias inventory и readiness по future rich workers upstream.

## Workflow Lane

Основной lane: `Full Feature` + `Security / Integration` + `Fast Fix`.

Причина: sprint затрагивает release evidence, реальные пользовательские сценарии, browser runtime качество, CSS/JS smoke tooling, Bitrix admin, Метрику, upstream/CRM и future lifecycle decisions.

## Состав Команды

| Роль | Ответственность |
|---|---|
| PM | Scope, owners, release issue, финальный sign-off |
| QA/Reviewer | Manual success-flow, browser-error challenge, evidence без PII |
| Frontend Dev | CSS/JS e2e readiness, исправление найденных UI/runtime ошибок |
| Backend Dev | Form/chat/prefill/staff endpoints, legacy aliases, upstream evidence |
| DevOps | Deploy/cache/config health, production/staging smoke, Bitrix admin handoff |
| SEO/Marketing | Metrika goals, rendered SEO head evidence, internal linking accepted risk |
| Architect | Legacy alias final-route runway, rich workers upstream contract decision |
| Admin | Bitrix admin smoke после deploy/cache refresh |

## Backlog И Реализация

| ID | Gap / Tail | Owner | Priority | Status | Acceptance Criteria |
|---|---|---|---|---|---|
| S10-001 | Release sign-off closure | PM + QA + DevOps | P1 | external handoff | Draft sign-off проходит; strict closure заблокирован только внешними gates `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream` |
| S10-002 | Manual success-flow | QA + Backend + Frontend | P1 | external handoff | На staging или controlled production проверены default form, modal form, AI chat, prefill и staff-order; evidence заполнен без PII по runbook |
| S10-003 | Staff-sale upstream evidence | Architect + Backend + QA + DevOps | P1 | external handoff | Подтверждено, что `workers_json`, `team_preset`, `monthly_budget_estimate`, `endDate` доходят до upstream/CRM или зафиксирован controlled fallback; gate `staff-sale-upstream` закрыт |
| S10-004 | Metrika goals evidence | PM/Marketing + QA | P1 | external handoff | В Яндекс.Метрике подтверждены affected goals/events для form, chat, prefill и staff-order; параметры не содержат PII |
| S10-005 | Bitrix admin smoke | QA/Admin + DevOps | P1 | external handoff | `/bitrix/admin/` открывается после deploy/cache refresh; публичная страница с admin toolbar не ломается; evidence сохранён без cookie/session |
| S10-006 | Browser zero-error challenge | QA + Frontend | P1 | done | Production/staging smoke не содержит `pageErrors`, `consoleErrors`, first-party `networkErrors`, `actionErrors`, broken images и horizontal overflow; найденные ошибки исправлены или заведены как explicit blocker |
| S10-007 | CSS/JS e2e readiness | Frontend + QA | P1 | done | `npm run e2e:css-js:prod` проходит после deploy; при CSS PR дополнительно проходит `npm run e2e:css-js:local`; manifest paths приложены к release evidence |
| S10-008 | Legacy sale aliases inventory | PM + Backend | P1 | external handoff | `docs/workflow/legacy-sale-alias-consumer-inventory.md` создан; до `30.06.2026` заполнен inventory consumers `tacticum_offer.php` / `tacticum_sale.php` из access logs/CRM; создан owner-backed plan миграции до `31.08.2026` |
| S10-009 | Rich workers upstream decision | Architect + Backend + DevOps | P2 | done | Подтверждено, что отдельного compatible upstream contract пока нет, или создан Security / Integration scope для переключения `ai.endpoint_paths.staff_sale` |
| S10-010 | CSP report-only baseline | Architect + Frontend + QA | P2 | done | Собран report-only baseline: нет first-party violations, карта `/contacts/` и Метрика работают; enforcing CSP не включается без отдельного rollout/rollback |
| S10-011 | SEO-009 accepted-risk revalidation | SEO + QA | P3 | done | `npm run seo:check`/`seo:smoke` подтверждают, что `/price/`, `/calculator/`, `/aiagents/` остаются в rendered navigation; решение не пересматривается без отдельного UX scope |
| S10-012 | Offer detail clear-cache routing | Backend + SEO + QA | P1 | done | `/offer/<code>/?clear_cache=Y` не уходит в root `404.php`; `npm run seo:check:prod` проходит после deploy |

## Browser Error Challenge

Цель: не обещать невозможное "абсолютно все ошибки", а закрыть наблюдаемые классы браузерных регрессий на всех текущих публичных URL desktop/mobile.

Обязательные классы:

- `pageerror`, unhandled runtime exceptions и failed script execution;
- `console.error` и browser console errors;
- first-party failed requests, unexpected 4xx/5xx, blocked resources;
- broken images;
- horizontal overflow;
- action failures: menu, modal, empty form validation, empty chat send, `/price/` filters/search/level/modal/team presets;
- CSS replacement regressions при локальных CSS правках;
- JS mixed-rollout regressions при deploy/cache refresh.

Команды:

```bash
npm run visual:smoke:prod
npm run browser:smoke:prod
npm run browser:smoke:price
npm run e2e:css-js:prod
```

Для CSS/JS PR до deploy:

```bash
npm run e2e:css-js:local
TACTICUM_VISUAL_PAGES=/price/ TACTICUM_VISUAL_INJECT_JS=local/templates/tacticum/components/bitrix/news.list/price/script.js npm run browser:smoke
```

Если команда падает, Sprint 10 не закрывается до fix или явного blocker issue с owner, severity и rollback/mitigation.

## CSS/JS E2E Readiness

| Area | Command | Expected |
|---|---|---|
| Tailwind/generated CSS | `npm run css:check` | Generated bundle актуален, cascade layer order сохранён |
| Template CSS governance | `npm run template-styles:check` | `template_styles.css` остаётся comment-only shim, active CSS не возвращается |
| Production visual/runtime | `npm run visual:smoke:prod` | Desktop/mobile screenshots есть, broken images/overflow/runtime errors отсутствуют |
| Production interactions | `npm run browser:smoke:prod` | Non-network UI actions проходят без создания лидов |
| `/price/` team presets | `npm run browser:smoke:price` | Team presets, summary, modal, `workers_json`, budget проходят desktop/mobile |
| CSS replacement | `npm run e2e:css-js:local` | Локальные CSS-файлы проходят поверх production HTML без visual/action regressions |
| Release bundle | `npm run e2e:css-js:prod` | CSS/JS readiness закрыта единым production smoke пакетом |

## Out Of Scope

- Enforcing CSP в production без отдельного rollout issue.
- Удаление legacy aliases до inventory и финального решения по Sprint 09 matrix.
- Переключение `ai.endpoint_paths.staff_sale` без совместимого upstream contract.
- Автоматизация реальных submit success-flow в production без controlled test lead.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | conditional | Нужен, если меняется CSP enforcing, legacy alias final mode или rich workers upstream contract |
| Design | conditional | Нужен при UX-изменениях, найденных browser-error challenge |
| QA early | yes | Manual success-flow, browser errors, staff upstream, Bitrix admin |
| SEO | yes | `seo:check`, `seo:smoke`, SEO-009 accepted-risk revalidation |
| Post-deploy smoke | yes | `health_config`, `seo:check:prod`, `e2e:css-js:prod`, release sign-off strict check |

## QA / Smoke Scope

| Scenario | URL/API/Command | Expected |
|---|---|---|
| Release sign-off summary | `npm run release:signoff:summary -- <json>` | Нет unexpected pending; оставшиеся pending имеют owner/reason/runbook |
| Strict release sign-off | `npm run release:signoff:check -- <json>` | Все обязательные gates `passed` или `not_applicable` |
| Browser zero-error | `npm run browser:smoke:prod` | `bad=0`, no `pageErrors`, `consoleErrors`, first-party `networkErrors`, `actionErrors` |
| CSS/JS prod e2e | `npm run e2e:css-js:prod` | CSS/JS readiness passed, manifests сохранены |
| CSS local e2e | `npm run e2e:css-js:local` | CSS replacement passed against production HTML |
| SEO/sitemap | `npm run seo:check:prod` | Root sitemap, Bitrix static sitemap, offer sitemap и JSON noindex guards проходят |
| Manual forms/chat | staging or controlled production | Success-flow evidence без PII |
| Staff order upstream | `/price/` + `/local/rest/tacticum_sale_staff.php` | Rich staff payload подтверждён upstream/CRM |
| Bitrix admin | `/bitrix/admin/` | Admin panel и toolbar работают после deploy/cache refresh |
| Metrika | Counter `103471113` | Goals/events видны, параметры без PII |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Browser smoke не покрывает реальный submit success-flow | QA + Backend | Success-flow закрывается отдельным manual/staging gate |
| Production test lead попадёт в коммерческую обработку | PM + QA | Использовать staging; production только controlled test lead с CRM пометкой |
| Metrika evidence требует доступа вне репозитория | PM/Marketing | Сохранять только безопасные ссылки/ID evidence, не скриншоты с PII |
| Bitrix cache снова отдаст mixed HTML/JS | DevOps + Frontend | Deploy cache clear + `browser:smoke:price`; при провале cache refresh до закрытия release |
| Rich workers upstream несовместим с текущим payload | Architect + Backend | Не переключать config; открыть новую Security / Integration задачу |
| CSP report-only violations требуют кода | Architect + Frontend | Enforce не включать; triage violations отдельным hardening task |

## Definition Of Done

- Все Sprint 10 backlog items имеют status `done`, `not_applicable` с reason или explicit follow-up issue с owner/date.
- Release sign-off JSON проходит strict `npm run release:signoff:check -- <file>`.
- `npm run e2e:css-js:prod` проходит после deploy.
- `npm run seo:check:prod` проходит после sitemap/Bitrix regeneration.
- Browser smoke manifests не содержат `pageErrors`, `consoleErrors`, first-party `networkErrors`, `actionErrors`, broken images или horizontal overflow.
- Manual evidence не содержит PII, raw payload, cookie/session/token/secret.
- Gap-analysis/current-state обновлены по факту закрытия Sprint 10.

## Kickoff Verification — 24.05.2026

- `npm run e2e:css-js:prod` прошёл после добавления aggregate script; manifests:
  - `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T10-23-35-033Z/manifest.json`
  - `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T10-24-59-321Z/manifest.json`
  - `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T10-26-34-792Z/manifest.json`
- `npm run e2e:css-js:local` сначала выявил tooling race в CDP runner: timeout `Page.domContentEventFired` после первого URL при CSS injection mode.
- `tools/visual-smoke.mjs` исправлен: Chrome target открывается на `about:blank`, целевой URL навигируется после подключения CDP listeners, добавлен fallback через `document.readyState`.
- `npm run e2e:css-js:local` после фикса прошёл; manifests:
  - `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T10-20-02-262Z/manifest.json`
  - `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T10-21-32-736Z/manifest.json`
- `node --check tools/visual-smoke.mjs` прошёл.
- `css-js-e2e-readiness` добавлен как machine-readable release gate в `release-signoff-gates.md` и `tools/release-signoff-check.mjs`.
- `docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` обновлён e2e evidence; gate `css-js-e2e-readiness` имеет status `passed`.
- `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` прошёл.
- `npm run release:signoff:summary -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` показывает оставшиеся pending gates: `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream`.
- `npm run release:signoff:self-test` прошёл, включая negative case на missing CSS/JS e2e manifest.
- Legacy alias inventory оформлен в `docs/workflow/legacy-sale-alias-consumer-inventory.md`: repo source scan не нашёл first-party callers вне docs/tools, access logs и CRM/upstream reports оставлены как external pending evidence до `30.06.2026`.
- SEO-009 revalidation закрыт: `npm run seo:check`, `npm run seo:check:prod` и `npm run seo:smoke` прошли; rendered smoke manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T10-52-46-468Z/manifest.json`.
- S10-012 закрыт после deploy: `/offer/marketingoviy-marketpleys-dlya-medikov-i-klinik/?clear_cache=Y` отдаёт HTTP 200, self-canonical и offer form; `npm run seo:check:prod` прошёл.
- S10-010 закрыт baseline artifact `docs/workflow/csp-report-only-baseline-2026-05-24.md`: `/contacts/` отдаёт `Content-Security-Policy-Report-Only`, enforcing CSP не включён, rendered smoke по `/contacts/` прошёл без browser/runtime/network errors.
- S10-009 закрыт decision artifact `docs/workflow/rich-workers-upstream-readiness-2026-05-24.md`: отдельного compatible upstream workers contract в repo/docs нет, `staff_sale` остаётся текущим adapter path.
- External gates handoff оформлен в `docs/workflow/sprint-10-external-gates-handoff-2026-05-24.md`; публичные prechecks выполнены, strict release closure остаётся заблокирован только авторизованным external evidence.

## Sprint Review

### Done

- S10-001 release sign-off переведён в explicit external handoff: draft-check/summary проходят, strict check ждёт ручные gates.
- S10-002/S10-003/S10-004/S10-005 переведены в external handoff с owners, due и evidence rules.
- S10-006 Browser zero-error challenge закрыт production/local e2e smoke и фиксом CDP readiness race.
- S10-007 CSS/JS e2e readiness закреплён aggregate scripts, release gate и sign-off checker.
- S10-008 legacy alias inventory переведён в external handoff с due `30.06.2026` и готовым inventory artifact.
- S10-009 Rich workers upstream decision закрыт как config-switch readiness без production переключения.
- S10-010 CSP report-only baseline закрыт отдельным artifact; enforcing CSP остаётся out of scope.
- S10-011 SEO-009 accepted-risk revalidation закрыт `seo:check`, `seo:check:prod` и rendered `seo:smoke`.
- S10-012 Offer detail clear-cache routing закрыт production `200` check, `seo:check:prod` и post-deploy `e2e:css-js:prod`.

### Not Done

- Strict release sign-off не может пройти без внешнего evidence: `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream`.
- Legacy alias traffic inventory не может быть заполнен без access logs/CRM reports и остаётся due `30.06.2026`.

### Follow-Up

- PM/QA закрывают pending manual gates по `docs/workflow/manual-release-gates-runbook.md` и `docs/workflow/sprint-10-external-gates-handoff-2026-05-24.md`.
- PM + Backend заполняют `docs/workflow/legacy-sale-alias-consumer-inventory.md` до `30.06.2026`.
- Architect + Backend возвращаются к rich workers upstream и CSP enforcing только отдельными Security / Integration scopes.
