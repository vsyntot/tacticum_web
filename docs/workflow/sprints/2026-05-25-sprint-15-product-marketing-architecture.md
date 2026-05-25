# Sprint 15 — Product Marketing Architecture

Дата формирования: 25.05.2026
Дата реализации: 25.05.2026
Статус: implemented, automated verification passed, awaiting deploy/post-deploy smoke

## Sprint Goal

Переупаковать публичный сайт Tacticum как понятную продуктово-маркетинговую систему: главный positioning, 4 коммерческих входа, более сильные CTA, квалифицированные лиды, отраслевые/scenario входы на базе `/offer/` и согласованная proof system.

Подтверждение на старт получено отдельным сообщением владельца сайта 25.05.2026.

## Workflow Lane

Full Feature Lane.

Причина: поменялись positioning, UX-пути, CTA, формы, SEO-входы и несколько публичных страниц.

Security / Integration review применён ограниченно: lead qualification реализован без нового upstream contract. Optional context остаётся в payload `/local/rest/tacticum_form.php`, но во внешний AI sale adapter уходит внутри существующего `task`.

## Source Gap Analysis

Основной документ: `docs/workflow/product-marketing-gap-analysis.md`.

В реализацию включены 100% gaps product/marketing challenge:

- `PMG-001` Positioning / main hero;
- `PMG-002` Product architecture / navigation;
- `PMG-003` `/price/` value framing;
- `PMG-004` `/offer/` conversion;
- `PMG-005` `/calculator/` promise;
- `PMG-006` Proof system;
- `PMG-007` `/aiagents/` product tone;
- `PMG-008` CTA taxonomy;
- `PMG-009` Industry / scenario segmentation;
- `PMG-010` Lead qualification.

## Implementation Summary

- Главная стала маршрутизатором по 4 входам: примеры расчетов, внедрение AI, команда под задачу, AI-боты.
- `/services/` переупакован как AI/automation delivery path с links на `/offer/`, `/price/`, `/calculator/`.
- `/price/` переупакован из commodity-ставок в подбор управляемой команды; price/staff DOM contracts сохранены.
- `/calculator/` показывает формат результата: бюджет, сроки, команда, риски и следующий шаг.
- `/offer/` получил более сильный bridge от примера расчета к персональной оценке; отраслевые/scenario chips используют существующие `/offer/catalog/...` noindex states.
- `/aiagents/` переведён в B2B-тон: демо Telegram-бота как проверка сценария и вход в прототип/интеграцию.
- Shared `tacticum:lead.cta` получил optional qualification fields `lead_budget` и `lead_timeline`, а также hidden `LEAD_CONTEXT`.
- `/local/rest/tacticum_form.php` добавляет allowlist lead context в существующий upstream `task`, не меняя response shape и endpoint bootstrap.
- Спорные proof claims вроде `98%`, `15+ лет`, “гарантия результата” убраны из публичного runtime copy.

## In Scope

| ID | Gap | Status | Closure |
|---|---|---|---|
| S15-001 | `PMG-001` | done | Главная hero promise и route cards обновлены |
| S15-002 | `PMG-002` | done | Product ladder закреплён в copy, route cards, service links и menu labels |
| S15-003 | `PMG-003` | done | `/price/` H1, intro, benefits, chat quick replies и modal copy говорят про команду под задачу |
| S15-004 | `PMG-004` | done | `/offer/` list/detail объясняют, что пример не финальная смета, и ведут к уточнению |
| S15-005 | `PMG-005` | done | `/calculator/` показывает expected artifact и safe accuracy disclaimer |
| S15-006 | `PMG-006` | done | Risky claims переписаны; proof copy приведён к безопасным формулировкам |
| S15-007 | `PMG-007` | done | `/aiagents/` выбран как B2B-service entry и связан с основной лестницей |
| S15-008 | `PMG-008` | done | CTA по страницам получили purpose, form_id и next-step context |
| S15-009 | `PMG-009` | done | Industry/scenario segmentation реализован через существующие noindex catalog states |
| S15-010 | `PMG-010` | done | Hidden context и optional `lead_budget`/`lead_timeline` добавлены без PII analytics |
| S15-011 | Supports `PMG-001`-`PMG-010` | automated checks passed | Static/browser/SEO checks прошли; deploy и post-deploy smoke остаются release gates |

## Key Files

| Area | Files |
|---|---|
| Public pages | `index.php`, `services/index.php`, `price/index.php`, `calculator/index.php`, `aiagents/index.php`, `contacts/index.php`, `about/index.php` |
| Offer | `local/components/tacticum/offer.catalog/templates/.default/template.php`, `local/templates/tacticum/components/bitrix/news.detail/offer/template.php`, `local/php_interface/include/offer_page.php` |
| CTA/forms | `local/components/tacticum/lead.cta/`, `local/rest/tacticum_form.php`, `docs/workflow/lead-form-contract.md` |
| Navigation | `services/.left.menu.php`, `.bottom.menu.php` |
| Docs | `docs/workflow/product-marketing-gap-analysis.md`, `docs/workflow/current-state.md`, `docs/workflow/gap-analysis.md`, `docs/workflow/analytics-events.md`, `docs/workflow/post-deploy-smoke.md` |

## SEO Decision

No new indexable URLs introduced.

- `/offer/` remains indexable hub.
- `/offer/<code>/` remains indexable self-canonical detail.
- `/offer/catalog/...` remains `noindex,follow` with canonical `/offer/`.
- `/offer/sitemap.php` remains detail-only and must not include catalog/filter states.

## Form / Analytics Decision

- New optional lead context keys: `lead_entry`, `lead_page_role`, `lead_intent`, `lead_cta`, `lead_next_step`, `lead_product`, `lead_scenario`, `lead_industry`, `lead_budget`, `lead_timeline`, `lead_offer_code`, `lead_offer_title`.
- `forms.js` still sends `FormData` + `page_url` + `sessid` + `form_id` + optional `group_id`.
- `tacticum_form.php` allowlists context keys and appends them to existing `task`.
- Analytics events remain unchanged and must not send contact data, message text, raw URLs with query, `group_id` or offer summary text.

## Verification Results

Automated checks passed 25.05.2026:

- `npm run seo:check`
- `npm run bitrix:check`
- `npm run css:check` after `npm run css:build`
- `npm run template-styles:check`
- `npm run config:check`
- `npm run gaps:known`
- `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json`
- `npm run release:signoff:self-test`
- `npm run sale:sunset:check`
- `npm run browser:smoke`
- `npm run browser:smoke:price`
- `npm run seo:check:prod`
- `npm run visual:smoke:css-local`
- `npm run browser:smoke:css-local`

`npm run dev:preflight` completed in degraded local mode because PHP CLI is not installed in this environment. Direct `php -l` could not run (`php: command not found`), so PHP 8.4 lint remains a CI/deploy fallback.

Post-deploy gates still required after deploy/cache refresh:

- `npm run visual:smoke:prod`
- `npm run browser:smoke:prod`
- manual/staging success-flow for lead forms, AI chat and staff sale upstream according to `docs/workflow/post-deploy-smoke.md`.

## Risks

| Risk | Mitigation |
|---|---|
| SEO duplication from clusters | Only existing `/offer/catalog/...` noindex states used |
| CTA/form payload drift | Existing `form_id`, required fields and response shape preserved |
| PII in analytics | No new analytics params added; docs reinforce allowed params |
| Hidden context too broad | Backend uses explicit allowlist and 900-character context cap |
| Existing external gates | Sprint 14 external gates remain separate and not closed by Sprint 15 |

## Sprint Review

### Done

- Product ladder and page roles implemented.
- Public copy/CTA/form context updated across target pages.
- `/offer/` conversion and segmentation improved without new indexable routes.
- Lead qualification context implemented without changing upstream response/contract shape.
- Docs updated to reflect new behavior.

### Not Done

- Production deploy and post-deploy smoke are not performed inside this implementation turn.
- External Sprint 14 gates remain external handoff: Метрика, Bitrix auth, CRM/upstream, access logs and release sign-off evidence.

### Follow-Up

- After deploy, complete post-deploy smoke and release sign-off evidence.
