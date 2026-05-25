# Sprint 12 - Final Site Hardening, UX And Release Closure

Дата: 24.05.2026 - 31.05.2026

## Sprint Goal

Закрыть оставшийся release handoff Sprint 10 и провести финальный hardening публичной части: меню, бессодержательные комментарии, `/offer/` визуальный слой, Bitrix component structure, CSS/JS, адаптив и скорость загрузки.

## Workflow Lane

Основной lane: `Full Feature` + `Fast Fix` + `Security / Integration`.

Причина: задачи затрагивают публичную навигацию, UX/UI, компоненты Bitrix, frontend assets, browser/runtime качество, performance и внешние release gates.

## Состав Команды

| Роль | Ответственность |
|---|---|
| PM | Scope, приоритеты, release sign-off, external gates |
| Analyst | Проверка навигации, комментариев, структуры страниц и acceptance criteria |
| Designer | `/offer/` визуальный слой, адаптив, mobile UX |
| Bitrix Dev | Верхнее меню, component framework challenge, cache-aware fixes |
| Frontend Dev | CSS/JS cleanup, responsive fixes, `/offer/` spacing/background |
| Backend Dev | Release success-flow, staff-sale upstream evidence, legacy aliases |
| QA | Browser smoke, mobile smoke, menu regression, release evidence без PII |
| DevOps/Admin | Bitrix cache reset, authenticated admin smoke, production checks |
| SEO/Marketing | Metrika goals, internal navigation impact, SEO no-regression |

## In Scope

| ID | Gap / Task | Lane | Owner | Priority | Status | Acceptance Criteria |
|---|---|---|---|---|---|---|
| S12-001 | Release sign-off closure carryover | Full Feature | PM + QA + DevOps | P1 | external handoff | `release:signoff:check` проходит strict после закрытия manual gates; evidence не содержит PII |
| S12-002 | Manual success-flow carryover | Security / Integration | QA + Backend + Frontend | P1 | external handoff | На staging или controlled production проверены default form, modal form, AI chat, prefill и staff-order; evidence заполнен по runbook |
| S12-003 | Staff-sale upstream evidence carryover | Security / Integration | Architect + Backend + QA + DevOps | P1 | external handoff | Upstream/CRM подтверждает `workers_json`, `team_preset`, `monthly_budget_estimate`, `endDate` или зафиксирован controlled fallback |
| S12-004 | Metrika goals evidence carryover | Full Feature | PM/Marketing + QA | P1 | external handoff | В Яндекс.Метрике подтверждены affected form/chat/prefill/staff-order goals/events; параметры без PII |
| S12-005 | Bitrix admin smoke carryover | Fast Fix | QA/Admin + DevOps | P1 | external handoff | Authenticated `/bitrix/admin/` открывается; public admin toolbar работает после deploy/cache refresh; evidence без cookie/session |
| S12-006 | Legacy sale aliases external inventory carryover | Security / Integration | PM + Backend | P1 | external handoff | До `30.06.2026` заполнен inventory consumers из access logs/CRM; до `31.08.2026` есть migration plan; runway до `30.09.2026` подтверждён |
| S12-007 | Top menu architecture and `/services/` disappearing items | Fast Fix | Bitrix Dev + QA + SEO | P1 | done | Верхнее меню стабильно показывает все top-level пункты на `/services/` и остальных публичных URL; причина дефекта зафиксирована; после deploy нужен relevant Bitrix/menu/component cache reset; `browser:smoke` не ловит menu regressions |
| S12-008 | Remove meaningless and placeholder comments | Fast Fix | Frontend + Backend + QA | P2 | done | Из production PHP/JS/CSS удалены бессодержательные комментарии, TODO-заглушки, dead commented markup/code; сохранены только полезные технические комментарии, docs и vendor/license blocks |
| S12-009 | `/offer/` visual spacing and background refinement | Full Feature | Designer + Frontend + QA + SEO | P1 | done | `/offer/` получил больше визуального воздуха и аккуратный background/section rhythm, сопоставимый по качеству с `/contacts/` и `/about/`; mobile карточки не сжимают метрики в 3 колонки; SEO/canonical не меняются |
| S12-010 | Final Bitrix component/framework challenge | Full Feature | Architect + Bitrix Dev + QA | P1 | done | Проведён аудит public pages, local components, template component overrides и helpers; дополнительные guards закрепляют thin page entries, local component metadata, menu child type и отсутствие direct `bitrix:*` в page layer |
| S12-011 | Final public JS/CSS audit and optimization | Full Feature | Frontend + QA | P1 | done | Проверены и оптимизированы JS/CSS в публичной части и `/local/`: `chat-agent.js` грузится только через page asset `chat`, внешние Google Fonts/Readdy origins удалены, lazy/async добавлен для неhero images; `css:check` проходит |
| S12-012 | Mobile/adaptive challenge | Full Feature | Designer + Frontend + QA | P1 | done | `/offer/` catalog получил mobile-first spacing/cards, фильтры и cards не должны давать horizontal overflow; финальная проверка закреплена `visual-smoke` desktop/mobile |
| S12-013 | Page speed challenge and optimization | Full Feature | Frontend + DevOps + QA | P2 | done | Убраны неиспользуемые third-party font/image origins, chat JS снят с неchat страниц, remote Readdy background удалён из offer detail, static images получили lazy/decoding и размеры там, где известны |

## Challenge Notes

### S12-007 Top Menu

Проверить в первую очередь:

- `.top.menu.php`, `services/.left.menu.php`, параметры `bitrix:menu` в `local/templates/tacticum/header.php`;
- не подменяет ли расширенное меню `/services/` верхний уровень вместо добавления дочерних пунктов;
- cache keys/menu cache после изменения структуры меню;
- поведение на `/`, `/services/`, `/price/`, `/calculator/`, `/aiagents/`, `/offer/`, `/contacts/`, `/about/`, `/policies/`;
- после fix выполнить очистку relevant Bitrix cache и smoke.

### S12-008 Comments

Удалять:

- комментарии-заглушки (`TODO`, `placeholder`, `Исправлен якорь`, commented dead markup/code);
- исторические комментарии, которые дублируют очевидный HTML/PHP;
- временные комментарии из предыдущих cleanup-задач.

Оставлять:

- комментарии, объясняющие неочевидный compatibility behavior;
- comments in docs, ADR, runbooks;
- required vendor/license comments.

### S12-010 Components

Проверить:

- public entry points должны быть тонкими: SEO/page properties + `IncludeComponent`;
- repeated layout/data sections должны жить в local components или templates;
- shared data logic должна жить в helpers/services, а не в page templates;
- `.description.php`, `.parameters.php`, `component.php` у local components;
- JS/CSS подключаются через `Asset` или component assets, не inline;
- ID инфоблоков через `IBLOCK_KEY` / config helpers.

### S12-013 Performance

Минимальный audit scope:

- HTML/CSS/JS weight и duplicate assets;
- hero/background image sizes и lazy/eager loading decisions;
- blocking third-party assets: Метрика, Yandex Maps, fonts;
- Bitrix/component cache behavior;
- browser smoke network errors and broken images;
- mobile first render and interaction readiness.

## Out Of Scope

- Enforcing CSP в production без отдельного rollout/rollback task.
- Удаление legacy sale aliases до external inventory и final decision.
- Переключение `ai.endpoint_paths.staff_sale` без совместимого upstream contract.
- Новый redesign всего сайта.
- Изменение публичных URL, canonical, sitemap или REST contracts без отдельного SEO/API scope.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | conditional | Нужен, если меняется общий component architecture, CSP/performance policy, REST/upstream contract или menu architecture как повторяемый pattern |
| Design | yes | `/offer/`, adaptive/mobile и visible navigation changes |
| QA early | yes | Menu regression, forms/chat, mobile, browser errors, speed checks |
| SEO | yes | Menu/internal linking, `/offer/`, performance and sitemap/canonical no-regression |
| Post-deploy smoke | yes | Cache reset + browser/visual/SEO/performance smoke |

## QA / Smoke Scope

| Scenario | URL / Command | Expected |
|---|---|---|
| Static guards | `npm run seo:check` | pass |
| CSS governance | `npm run css:check` + `npm run template-styles:check` | pass |
| Config | `npm run config:check` | pass |
| Local preflight | `npm run dev:preflight` | pass or documented PHP CLI degraded state |
| Menu regression | `/`, `/services/`, `/price/`, `/calculator/`, `/aiagents/`, `/offer/`, `/contacts/`, `/about/`, `/policies/` | Верхнее меню показывает expected top-level items |
| Browser smoke | `npm run browser:smoke:prod` | no page/action/runtime errors |
| Visual smoke | `npm run visual:smoke:prod` | no broken images, no horizontal overflow |
| Price actions | `npm run browser:smoke:price` | team presets/summary/modal still pass |
| CSS/JS readiness | `npm run e2e:css-js:prod`; for CSS/JS PR also `npm run e2e:css-js:local` | pass |
| SEO rendered head | `npm run seo:smoke` | public URL head remains valid |
| Release sign-off | `npm run release:signoff:check -- <json>` | strict passes after external gates |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Bitrix menu cache hides or preserves old menu behavior | DevOps + Bitrix Dev | Explicit cache reset after menu changes; test `/services/` before and after |
| Removing comments accidentally removes useful compatibility notes | QA + Architect | Keep comments only where they explain non-obvious behavior; review deleted comments |
| `/offer/` visual changes shift SEO/content hierarchy | Designer + SEO | Preserve H1/canonical/schema, visual-only changes first |
| CSS optimization causes production cascade regression | Frontend + QA | Use `e2e:css-js:local` and visual smoke before deploy |
| Performance changes break third-party integrations | Frontend + DevOps | Keep Метрика/Yandex Maps smoke and CSP report-only baseline |
| External gates remain blocked by access | PM | Keep explicit owners, due dates and safe evidence rules |

## Definition Of Done

- S12 code-level tasks are `done` or have explicit follow-up issue with owner/date.
- External gates are either `passed`, `not_applicable` with reason, or remain tracked with owner/due and safe evidence rules.
- `gap-analysis.md`, sprint artifact and related docs are updated.
- Automated checks pass.
- After deploy/cache reset, production smoke passes.
- No PII, raw payload, token, cookie/session or secret is committed as evidence.

## Sprint Review

### Done

- S12-007: `/services/` menu root-collision устранён: дочерние пункты перенесены в `services/.left.menu.php`, `/offer/` добавлен в dropdown/mobile/footer и блок `Наши услуги` как `Расчет проекта`, top/mobile menu используют `CHILD_MENU_TYPE=left`, `USE_EXT=N`, helper дерева меню префиксован как `tacticum_build_menu_tree(...)`; footer money links приведены к абсолютным URL.
- S12-008: удалены бессодержательные HTML/PHP comments, commented dead markup/code и временные cleanup-комментарии из public/templates/local runtime scope; оставлены docs, compatibility notes и vendor/license comments.
- S12-009/S12-012: `/offer/` list page получил `bg-gray-50`, более высокий hero без лишнего eyebrow `Каталог расчетов`, белые statistic/filter/cards, увеличенные gaps и mobile-safe card metrics.
- S12-010: component/framework guards расширены в `tools/seo-check.mjs`: menu architecture, thin `/offer/` controller, local component metadata, page assets и отсутствие direct `bitrix:*` calls в page layer.
- S12-011/S12-013: `chat-agent.js` теперь подключается только через page asset `chat`; удалены неиспользуемые Google Fonts/Readdy origins; external Readdy background на offer detail заменён локальным CSS-слоем; non-hero images получили lazy/async и статические размеры там, где известны.
- S12-012 follow-up: header/menu breakpoint переведён с `md` на `lg`, чтобы 768/820px touch-планшеты не получали hover-only desktop menu; mobile menu стал scroll-safe в landscape и закрывается по ссылкам/CTA/Escape; footer grid на `lg` больше не зажимает контактно-юридическую колонку в 224px.

### Repository Closure Refresh - 25.05.2026

- Repo/static guards passed: `seo:check`, `css:check`, `template-styles:check`, `config:check`, `sale:sunset:check`, `release:signoff:draft-check`, `release:signoff:summary`, `release:signoff:self-test`, `node --check tools/seo-check.mjs`, `node --check tools/visual-smoke.mjs`, `node --check tools/release-signoff-check.mjs`.
- Production-safe prechecks passed without creating leads: `seo:check:prod`; unauthenticated `/bitrix/admin/` returns `HTTP/2 200` with `x-bitrix-ajax-status: Authorize`; GET guards for `tacticum_form.php`, `tacticum_chat.php`, `tacticum_prefill.php`, `tacticum_sale_staff.php` and `resolve_telegram_link.php` return controlled `405` JSON with `X-Robots-Tag: noindex, nofollow`.
- `dev:preflight` remains degraded locally because PHP CLI is absent; GitHub PHP 8.4 lint remains the required fallback.
- Full production `visual:smoke:prod` / `browser:smoke:prod` for this working-tree package must run only after deploy and Bitrix menu/component cache refresh, because the checks now expect `/offer/` in rendered navigation.

### Not Done

- External handoff gates S12-001 - S12-006 остаются вне репозитория: release sign-off, real success-flow, staff-sale upstream, Metrika goals, authenticated Bitrix admin smoke, legacy sale aliases inventory.
- Post-deploy cache reset и production smoke выполняются после deploy.

### Follow-Up

- После deploy очистить relevant Bitrix/menu/component cache и выполнить `npm run seo:check:prod`, `npm run browser:smoke:prod`, `npm run browser:smoke:price` и `npm run visual:smoke:prod`.
