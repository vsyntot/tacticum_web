# Sprint 11 - Public Page Componentization

Дата: 24.05.2026 - 31.05.2026

## Sprint Goal

Закрыть 100% componentization backlog после challenge структуры сайта: вынести повторяемые CTA, FAQ, chat surfaces и `/aiagents/` page flow в Bitrix-friendly local components, не ломая SEO, формы, аналитику и browser smoke gates.

## Workflow Lane

Основной lane: `Full Feature` + `Fast Fix`.

Причина: меняется внутренняя организация публичных страниц и повторяемые UI/contracts, но не добавляются новые публичные URL и не меняется REST/API contract.

## Состав Команды

| Роль | Ответственность |
|---|---|
| PM | Scope, acceptance criteria, sprint review |
| Architect | Component boundaries, ADR/current-state consistency |
| Frontend Dev | Local components, page integration, CSS/JS contracts |
| Backend/Bitrix Dev | Bitrix component params, iblock helper discipline |
| QA/Reviewer | Browser smoke, form/chat non-network checks, SEO static checks |

## Backlog И Реализация

| ID | Componentization Item | Owner | Priority | Status | Acceptance Criteria |
|---|---|---|---|---|---|
| S11-001 | `tacticum:lead.cta` | Frontend + Bitrix Dev | P1 | done | `personal-offer-cta.php` и `project-discussion-cta.php` заменены локальным компонентом с явными params; `data-tacticum-form`, consent, form ids и analytics taxonomy сохранены |
| S11-002 | `tacticum:faq.section` | Frontend + SEO | P1 | done | Повторяемые FAQ вызовы идут через wrapper над `bitrix:news.list` template `faq`; `IBLOCK_ID` передаётся через helper, section class/cache params сохранены |
| S11-003 | `tacticum:chat.surface` | Frontend + QA | P1 | done | Hero/light chat HTML surfaces вынесены в компонент; `data-tacticum-chat`, `data-chat-surface`, quick replies и `chat-agent.js` contract сохранены |
| S11-004 | `/aiagents/` section-level component | Frontend + Architect | P1 | done | `aiagents/index.php` стал тонкой page entry; render flow живёт в `tacticum:aiagents`; page assets/body class задаются через Bitrix page properties |
| S11-005 | Public page integration | Frontend + QA | P1 | done | `/`, `/calculator/`, `/price/`, `/contacts/`, `/about/`, `/services/`, `/aiagents/` используют новые компоненты там, где был backlog; визуальный контент и form/chat contracts не меняются |
| S11-006 | Docs and guards | Architect + QA | P2 | done | `current-state`, `gap-analysis`, sprint artifact и relevant static guards обновлены; `seo:check`, `css:check`, `template-styles:check`, `dev:preflight` проходят |

## Out Of Scope

- Новый дизайн страниц или изменение маркетингового текста.
- Изменение REST endpoints, payload форм, AI chat contract или upstream.
- Перенос всех статичных hero/about/services блоков в универсальный `hero` компонент.
- Удаление legacy `TACTICUM_PAGE_ASSETS` globals со всех страниц вынесено в Sprint 11 Hardening и закрыто через page properties.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | yes | ADR-008 фиксирует public page local components; ADR-007 продолжает фиксировать `/offer/` routing/component pattern |
| Design | no | Визуально сохраняем текущие блоки |
| QA early | yes | Form/chat/browser contracts |
| SEO | yes | Head/canonical/H1/FAQ schema не должны регрессировать |
| Post-deploy smoke | yes | `seo:check:prod`, browser smoke и visual smoke после deploy/cache clear |

## QA / Smoke Scope

| Scenario | URL/API/Command | Expected |
|---|---|---|
| Static checks | `npm run seo:check` | sitemap/robots/canonical/menu/offer routing guards pass |
| CSS build | `npm run css:check` | Tailwind generated CSS актуален после новых component source paths |
| Template CSS guard | `npm run template-styles:check` | Нет возврата active CSS в `template_styles.css`, icon classes валидны |
| Dev preflight | `npm run dev:preflight` | Local PHP lint runs if PHP CLI exists, otherwise degraded state documented |
| Browser smoke | `npm run browser:smoke:prod` after deploy | Forms/chat/menu/modal/price actions pass without runtime errors |
| Visual smoke | `npm run visual:smoke:prod` after deploy | No broken images, overflow, SEO head regressions |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Компонентная миграция меняет form field names или `form_id` | Frontend + QA | Preserve current markup/data attributes; smoke empty validation and manual controlled submit |
| Chat component ломает `chat-agent.js` selectors | Frontend + QA | Preserve `data-*` contracts exactly; run browser smoke |
| FAQ wrapper меняет section/filter/cache behavior | SEO + Bitrix Dev | Keep current `bitrix:news.list` params as defaults and page-level overrides |
| `/aiagents/` перенос меняет page body class/assets | Architect + Frontend | Use page properties consumed by template header; verify rendered class/assets |
| Большой refactor мешает deploy review | PM + Architect | Keep public page entries thin but readable; avoid unrelated text/design changes |

## Definition Of Done

- S11-001 - S11-006 имеют status `done`.
- Новые components находятся в `local/components/tacticum/`.
- Public pages не используют новые hardcoded iblock IDs.
- `git diff --check`, `node --check tools/seo-check.mjs`, `npm run seo:check`, `npm run template-styles:check`, `npm run css:check`, `npm run config:check`, `npm run dev:preflight` выполнены.
- После deploy выполняются `npm run seo:check:prod` и browser/visual smoke.

## Sprint Review

### Verification

- `git diff --check` - passed.
- `node --check tools/seo-check.mjs` - passed.
- `npm run seo:check` - passed.
- `npm run template-styles:check` - passed.
- `npm run css:check` - passed.
- `npm run config:check` - passed.
- `npm run dev:preflight` - degraded local state: PHP CLI not found, GitHub PHP 8.4 lint remains fallback.
- Targeted CSS-local smoke for `/`, `/calculator/`, `/price/`, `/aiagents/` - passed: visual manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T16-58-58-475Z/manifest.json`, browser manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T16-58-58-476Z/manifest.json`.

### Done

- `tacticum:lead.cta` заменил старые template includes; сами includes удалены.
- `tacticum:faq.section` закрывает повторяемые FAQ-вызовы публичных страниц и offer detail.
- `tacticum:chat.surface` закрывает hero/light chat surfaces без изменения `chat-agent.js` contracts.
- `/aiagents/` переведён в section-level component `tacticum:aiagents`; page entry использует split prolog и page properties.
- ADR-008 принят как правило для новых повторяемых публичных UI-блоков.
- Static guards и workflow docs обновлены под component pattern.

### Not Done

- Нет.

### Follow-Up

- Cleanup legacy `TACTICUM_PAGE_ASSETS` globals закрыт в Sprint 11 Hardening; новые страницы должны использовать только Bitrix page properties.
