# Sprint 09 - Overall Gap Closure

Дата: 23.05.2026

## Sprint Goal

Закрыть 100% residual overall gaps после Sprint 08: release evidence, `/price/` team preset deploy gate, SEO rendered head, `template_styles.css` retirement governance, CSP enforcing runway, rich workers upstream switch, legacy sale alias sunset и локальный PHP CLI fallback.

## Workflow Lane

Основной lane: `Full Feature` + `Security / Integration` + `Fast Fix`.

Причина: sprint затрагивает deploy gates, SEO smoke, template header security policy, публичные страницы, config contract, dev preflight и workflow/ADR.

## Состав Команды

| Роль | Ответственность |
|---|---|
| PM | Scope, приоритеты, sprint review, release sign-off |
| Architect | CSP mode, sale/upstream decision matrix, ADR alignment |
| Frontend Dev | `/price/` summary follow-up, H1 fixes, CSS retirement owner mapping |
| Backend Dev | Config contract, staff upstream switch discipline |
| QA/Reviewer | Visual/browser/SEO smoke gates, manual success-flow matrix |
| DevOps | Deploy env, config sync, PHP CLI/CI fallback |
| SEO/Marketing | Rendered head checks и Метрика evidence |

## Backlog И Реализация

| ID | Gap | Owner | Priority | Status | Реализация |
|---|---|---|---|---|---|
| OGC-001 | Release sign-off evidence | PM + QA + DevOps | P1 | done as gate | `release-signoff-gates.md` расширен SEO `/price`/staff-upstream gates; deploy запускает visual smoke с SEO head expectation; `release:signoff:check` валидирует JSON evidence и локальные smoke manifests |
| OGC-002 | `/price/` team preset deploy gate | Frontend + QA | P1 | done | `browser:smoke:price` остаётся обязательным; summary UX доработан, smoke проверяет preset, `workers_json` и monthly budget |
| OGC-003 | Rendered SEO head | SEO + Frontend | P1 | done | `tools/visual-smoke.mjs` собирает `seoHead` и при `TACTICUM_EXPECT_SEO_HEAD=1` блокирует missing/duplicate title, description, canonical, OpenGraph и H1; `/calculator/` и `/offer/` получили H1 baseline |
| OGC-004 | `template_styles.css` retirement | Frontend + QA | P2 | done | Active CSS перенесён в `styles/global.css`, подключён через `Asset`; `template_styles.css` стал comment-only shim; `template-styles:check` добавлен в CI/deploy |
| OGC-005 | CSP enforcing runway | Architect + Frontend + DevOps | P2 | done | `header.php` поддерживает config switch `security.csp_mode=report-only|enforce`; default остаётся report-only; `health_config` валидирует security scope; ADR-005 фиксирует runway и rollback |
| OGC-006 | Rich workers upstream | Architect + Backend + QA | P2 | done as decision | `ai.endpoint_paths.staff_sale` остаётся единственным switch; detailed matrix в workstream `2026-05-23-sprint-09-sale-sunset-upstream.md` |
| OGC-007 | Legacy sale alias sunset | Architect + Backend + DevOps | P2 | done as decision | Workstream фиксирует inventory до `30.06.2026`, migration до `31.08.2026`, final alias mode до `30.09.2026`; `sale:sunset:check` остаётся CI guard |
| OGC-008 | Local PHP CLI gap | DevOps + Backend | P3 | done | `npm run dev:preflight` запускает PHP lint при доступном PHP 8.4+ или явно фиксирует degraded local state с GitHub `php-lint` fallback |

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | yes | ADR-005 и ADR-006 обновлены под CSP/upstream decisions |
| Design | yes, scoped | `/price/` summary UX уже доработан как Fast Fix |
| QA early | yes | Release evidence, SEO head, staff-order, legacy aliases |
| SEO | yes | `TACTICUM_EXPECT_SEO_HEAD=1` в deploy visual smoke |
| Post-deploy smoke | yes | `health_config`, `visual:smoke`, `browser:smoke`, `/price` team preset flow, manual success-flow |

## QA / Smoke Scope

| Scenario | Command / URL | Expected |
|---|---|---|
| Static CSS | `npm run css:check` | Generated Tailwind актуален, layer order сохранён |
| Template styles retirement | `npm run template-styles:check` | `template_styles.css` остаётся comment-only shim, `styles/global.css` подключён через `Asset` |
| Config contract | `npm run config:check` | Example config содержит `ai.endpoint_paths.*` и `security.csp_mode` |
| Sale sunset | `npm run sale:sunset:check` | До `30.09.2026` aliases допустимы; после даты нужен explicit decision |
| Dev preflight | `npm run dev:preflight` | PHP lint проходит при PHP 8.4+ или degraded state явно зафиксирован |
| SEO smoke after deploy | `npm run seo:smoke` | `seoErrors=[]`, один H1/title/description/canonical и OpenGraph без дублей |
| Price team presets | `npm run browser:smoke:price` | Summary, modal open, `workers_json`, monthly budget проходят desktop/mobile |
| Full browser smoke | `npm run browser:smoke:prod` | Runtime/action errors отсутствуют |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| SEO smoke провалится до deploy из-за старой production HTML | SEO + DevOps | `TACTICUM_EXPECT_SEO_HEAD=1` используется как post-deploy gate; pre-deploy для CSS не включает SEO expectation |
| Enforcing CSP может заблокировать карту/Метрику | Architect + QA | Default `report-only`; switch в `enforce` только отдельным release с rollback |
| Legacy aliases всё ещё нужны внешнему consumer после sunset | PM + Backend | Inventory/migration/final decision matrix в sale workstream |
| Rich workers endpoint окажется несовместимым | Architect + Backend | Config-only switch разрешён только при совместимом contract; иначе новая Security / Integration задача |

## Sprint Review

### Done

- Все residual gaps заведены в единый Sprint 09 backlog.
- Кодовые закрытия выполнены: SEO head smoke, H1 baseline, CSP config switch, config contract, dev preflight, deploy SEO expectation.
- Процессные закрытия оформлены gates/matrices: release sign-off, sale sunset, rich workers upstream; CSS retirement доведён до shim-state.
- Docs/current-state/gap-analysis/ADR обновлены.

### Verified Locally

- `node --check tools/visual-smoke.mjs`
- `node --check tools/config-contract-check.mjs`
- `node --check tools/legacy-sale-sunset-check.mjs`
- `node --check tools/dev-env-preflight.mjs`
- `node --check tools/template-styles-retirement-check.mjs`
- `node --check tools/release-signoff-check.mjs`
- `npm run template-styles:check`
- `npm run config:check`
- `npm run sale:sunset:check`
- `npm run release:signoff:check -- docs/workflow/release-signoff.example.json`
- `npm run release:signoff:summary -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json`
- `npm run release:signoff:self-test` — негативные кейсы: placeholder, unsafe raw response key, email-like value, incomplete staff upstream, unknown gate, strict working-tree commit, pending manual gate without runbook
- `npm run dev:preflight` — degraded mode без локального PHP CLI, GitHub `php-lint` остаётся fallback
- `npm run css:check`
- `npm run browser:smoke:css-local` — migrated `styles/global.css` manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-23T20-15-32-366Z/manifest.json`
- Production 24.05.2026: `health_config` вернул `success=true`; rendered CSS bundle содержит root-relative `styles/global.css` image/font URLs.
- Production 24.05.2026: `npm run seo:smoke` прошёл, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T05-21-37-564Z/manifest.json`.
- Production 24.05.2026: `npm run browser:smoke:price` выявил stale component HTML cache: JS bundle уже `multi-staff-v4`, но rendered `/price/` не содержит `data-price-team-preset`; deploy cache clear расширен на `bitrix/cache/s1/bitrix/news.list|news.detail` и `bitrix/html_pages`.
- Production 24.05.2026 после cache refresh: `npm run browser:smoke:price` прошёл, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T05-32-34-008Z/manifest.json`.
- Production 24.05.2026: `npm run browser:smoke:prod` прошёл по всем публичным страницам, manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T05-33-05-900Z/manifest.json`.
- Manual gates handoff: `docs/workflow/manual-release-gates-runbook.md` и `docs/workflow/release-signoff-manual-evidence.template.json` фиксируют порядок закрытия real success-flow, Metrika goals, Bitrix admin и staff-sale upstream без PII в evidence.
- Deploy lifecycle guard дополнен `release:signoff:check` на example и `release:signoff:self-test`, чтобы production deploy не проходил со сломанным release evidence tooling.
- `git diff --check`
- workflow YAML parse через Ruby для `pr-check.yml` и `deploy.yml`

### Not Done

- Enforcing CSP не включался в production config: default остаётся `report-only`.
- `template_styles.css` физически не удалялся: файл оставлен как Bitrix compatibility shim; следующий шаг — extraction малыми партиями из `styles/global.css`.
- Legacy aliases не удалялись до consumer inventory и даты `30.09.2026`.
- Rich workers upstream не переключался без внешнего compatible contract.
- Ручные gates `manual-success-flow`, `metrika-goals`, `bitrix-admin`, `staff-sale-upstream` остаются pending до действий QA/PM/Admin по runbook; они не должны закрываться синтетически без доступа к Метрике, админке и upstream/CRM.

### Follow-Up

- После deploy зафиксировать manifests `visual:smoke`, `browser:smoke`, `browser:smoke:price` и при SEO changes `seo:smoke`.
- До `30.06.2026` PM + Backend заполняют inventory legacy alias consumers.
- Следующий CSS cleanup PR начинает с owner mapping `styles/global.css`, без массового переноса правил.
