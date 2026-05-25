# Sprint 16 — Final Stabilization Closure

Дата формирования: 25.05.2026
Статус: implemented locally; external release gates pending
Source gap analysis: `docs/workflow/final-stabilization-challenge-gap-analysis-2026-05-25.md`

## Sprint Goal

Закрыть финальный стабилизационный хвост после Sprint 15 и перевести сайт в целевое состояние: публичные коммерческие пути работают end-to-end, критичные trust/render defects устранены, post-deploy/cache smoke пройден, а оставшиеся внешние проверки имеют evidence или explicit accepted-risk решение.

## Capacity / Constraints

- Production freeze: нет, но deploy должен идти только через workflow с cache clear и post-deploy smoke.
- Known dependencies: доступ к Yandex.Metrika, Bitrix admin, staging/controlled production lead flow, upstream/CRM evidence, production deploy.
- Agents / roles:
  - PM: финальное решение по accepted risks и sign-off;
  - Product/Analyst: chat-to-lead handoff и CTA acceptance;
  - Designer/Frontend: contact map, offer detail contrast, CTA image/visual trust;
  - Backend/Architect: lead context, upstream/staff evidence, release constraints;
  - QA: browser/render/manual success-flow smoke;
  - DevOps: deploy/cache clear, production smoke, release evidence;
  - SEO/Marketing: Metrika goals, cluster decision, proof matrix.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| S16-001 | `FSC-001` Contact map correctness | Fast Fix / Full Feature | Frontend + QA + DevOps | P1 | done locally | Yandex map widget iframe for `Тактикум`; `БЦ Victory Park` as landmark; legal address shown separately; post-deploy visual/manual smoke |
| S16-002 | `FSC-002` Offer detail estimate contrast | Fast Fix | Frontend + QA | P1 | done locally | Valid `/offer/<code>/` fixture from production/staging |
| S16-003 | `FSC-003` Deploy/cache smoke for current tree | Security / Integration | DevOps + QA | P1 | external handoff | Deploy workflow hardened; production deploy/cache manifests required |
| S16-004 | `FSC-004` Real success-flow + staff upstream evidence | Security / Integration | QA + Backend + Architect + DevOps | P1 | external handoff | Staging or controlled production lead, upstream/CRM access |
| S16-005 | `FSC-005` Calculator/price chat-to-lead handoff | Full Feature / Security Integration review | Product + Frontend + Backend + QA | P1 | done locally | Preserve no-PII analytics; no upstream contract change |
| S16-006 | `FSC-006` Metrika goals evidence | Full Feature | PM/Marketing + QA | P1 | external handoff | Yandex.Metrika access, affected goal list |
| S16-007 | `FSC-007` CTA image trust cleanup | Fast Fix / Design | Designer + Frontend + PM | P2 | done | Generic default image suppressed; no-image layout supported |
| S16-008 | `FSC-008` Proof evidence matrix | Full Feature | PM + Marketing + Analyst + SEO | P2 | done | `proof-claims-matrix.md`; numeric runtime claims removed |
| S16-009 | `FSC-009` Industry/scenario SEO decision | Full Feature / SEO gate | SEO + PM + Architect | P2 | accepted | Current noindex strategy accepted for stabilization |
| S16-010 | `FSC-010` CSP target-state decision | Security / Integration | Architect + DevOps + QA | P2 | accepted | Report-only accepted as target state for current release |
| S16-011 | `FSC-012` Contact/legal content hierarchy decision | Fast Fix / Design | PM + Designer + Frontend | P2 | done locally | CTA moved before legal block; legal details remain available |
| S16-012 | Release sign-off closure | Full Feature | PM + QA + DevOps | P1 | external handoff | `release:signoff:check` strict after gates |

## Out Of Scope

- Новый редизайн всего сайта.
- Новые AI/upstream contracts без отдельного ADR и Security / Integration task.
- Индексируемые industry/scenario landing pages, если S16-009 решит оставить noindex strategy accepted.
- Удаление legacy sale aliases до завершения external inventory and sunset matrix.
- CSP enforce без report-only triage.
- Закрытие manual gates без evidence.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | Conditional | Required only if chat-to-lead handoff changes upstream contract, new SEO URL model is introduced, or CSP enforce policy changes |
| Design | Yes | Contact map/CTA image/legal hierarchy and offer detail visual contrast |
| QA early | Yes | Lead forms, chat handoff, staff upstream, post-deploy smoke |
| SEO | Yes | Offer detail, sitemap/canonical unchanged; S16-009 cluster decision |
| Security / Integration | Yes | Real success-flow, staff upstream, CSP, analytics no-PII evidence |
| Post-deploy smoke | Yes | Required before final stabilization sign-off |

## Acceptance Criteria

1. `/contacts/` map points to `Тактикум` through Yandex map widget iframe, `БЦ Victory Park` remains the navigation landmark, and the legal address is shown as a separate text address; evidence includes screenshot/manual check.
2. `/contacts/` lead CTA select labels do not overlap after deploy/cache clear on desktop and mobile.
3. Valid `/offer/<code>/` detail page has readable estimate block with explicit background and accessible contrast.
4. `/calculator/` and `/price/` chat surfaces can hand off safe context to the relevant lead CTA or document an accepted alternative.
5. `manual-success-flow` evidence confirms forms, modal form, AI chat, prefill and staff-order behavior without PII in evidence.
6. `staff-sale-upstream` evidence confirms `workers_json`, `team_preset`, `monthly_budget_estimate` and `endDate` reach upstream/CRM or a controlled fallback is accepted by Architect + PM.
7. Yandex.Metrika goals/events are confirmed for affected form/chat/staff-order flows without PII params.
8. CTA imagery and proof claims pass PM/Design/Marketing review or are explicitly accepted with owner and due date.
9. `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` passes locally; strict `release:signoff:check` passes only after external gates.
10. `npm run gaps:known` is green in draft mode; strict remains expected to fail until external release gates are closed.

## QA / Smoke Scope

| Scenario | URL/API | Expected |
|---|---|---|
| Contact map | `/contacts/` | Yandex iframe shows `Тактикум`; map link opens the Yandex Maps organization card; `БЦ Victory Park` and legal address remain visible; no JS/runtime errors |
| Contact CTA | `/contacts/` | `lead_budget` / `lead_timeline` labels readable, no overlap, empty validation works |
| Offer detail | `/offer/<valid-code>/` | Estimate block visible/readable, CTA prefill works, hidden offer context exists |
| Calculator chat handoff | `/calculator/` | Chat result can be transferred to form or documented fallback works |
| Price chat handoff | `/price/` | Chat result can be transferred to form or documented fallback works |
| Staff order | `/price/` | Team presets, `workers_json`, monthly estimate and end date pass frontend/backend/upstream evidence |
| Forms | `/`, `/services/`, `/calculator/`, `/price/`, `/contacts/`, `/aiagents/`, `/offer/<code>/` | Required fields, consent, CSRF, success/error states, no PII analytics |
| SEO/rendered head | public URLs + valid offer detail | `seo:check`, `seo:check:prod`, rendered head smoke green |
| Release | release sign-off JSON | Strict release checker passes |

## Verification

### Automated

```bash
npm run css:check
npm run template-styles:check
npm run bitrix:check
npm run config:check
npm run seo:check
npm run gaps:known
npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
npm run release:signoff:self-test
npm run sale:sunset:check
npm run browser:smoke
npm run browser:smoke:price
npm run visual:smoke:css-local
npm run browser:smoke:css-local
TACTICUM_VISUAL_BASE_URL=https://tacticum.ru TACTICUM_VISUAL_PAGES=/offer/rpa-i-dokumentooborot-dlya-finansovoy-kompanii-v-penze-20260524-180000/ TACTICUM_EXPECT_SEO_HEAD=1 npm run visual:smoke
```

After deploy/cache refresh:

```bash
npm run visual:smoke:prod
npm run browser:smoke:prod
npm run browser:smoke:price
npm run seo:check:prod
npm run release:signoff:check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
```

`php -l` remains CI/deploy fallback if local PHP CLI is unavailable.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Map provider/widget remains wrong after source change | Frontend + QA | Use coordinate-based Yandex iframe plus direct route link; manually verify rendered geography |
| Chat-to-lead handoff leaks PII into analytics | Frontend + Backend + QA | Pass context only in form payload; analytics remains limited to event/form/surface/status codes |
| Offer detail fix changes layout for old content | Frontend + QA | Test several offer examples and mobile viewport |
| External gates block final closure | PM + QA + DevOps | Keep owner/due/evidence in release sign-off; do not mark stable until resolved or accepted |
| CSP enforce breaks Yandex/Metrika/Bitrix toolbar | Architect + DevOps | Keep report-only unless triage and rollback plan are complete |
| Production cache serves mixed markup/assets | DevOps | Clear managed/component/composite/template asset cache; run smoke immediately after deploy |

## Definition Of Done

- Local code/docs gaps `FSC-001`, `FSC-002`, `FSC-005`, `FSC-007`, `FSC-008`, `FSC-012` are closed.
- P2 decisions `FSC-009` / `FSC-010` are explicitly accepted for stabilization.
- External P1 gates `FSC-003`, `FSC-004`, `FSC-006` remain visible with owner/due/evidence rule until strict release closure.
- Release sign-off draft check passes; strict check passes only after external evidence.
- `docs/workflow/gap-analysis.md`, `docs/workflow/current-state.md`, release sign-off and this sprint doc are updated.
- PM summary states whether site is now stabilized and in target state.

## Sprint Review

### Done

- `/contacts/` map uses the Yandex map widget iframe for `Тактикум` (`oid=243968538014`, coordinates `55.723957,37.503747`); `БЦ Victory Park` remains visible as a landmark, legal address remains visible separately, and wrong placeholder image no longer appears under the map container.
- `/contacts/` conversion hierarchy changed: CTA appears before legal/requisites block.
- Offer detail estimate block now has explicit gradient background and white text contrast.
- `/calculator/` and `/price/` light chat can hand off safe summary/scoped `group_id` to the page CTA without PII analytics params.
- Generic default `specialoffer.jpg` suppressed in `tacticum:lead.cta`; no-image personal-offer CTA collapses to a centered form layout.
- Proof matrix, industry/scenario SEO decision and CSP report-only target decision documented.
- Deploy workflow hardened for stale public-section file deletion, menu cache clear and real release draft guard.
- Automated verification passed: `css:build`, `css:check`, `template-styles:check`, `bitrix:check`, `config:check`, `seo:check`, `seo:check:prod`, `gaps:known`, `release:signoff:draft-check`, `release:signoff:self-test`, `sale:sunset:check`, `browser:smoke`, `browser:smoke:price`, `visual:smoke:css-local`, `browser:smoke:css-local`, JS syntax checks for `chat-agent.js`, `forms.js`, `yandex-map.js`.
- Map widget endpoint returned HTTP 200; local static assertion confirms `/contacts/` uses `map-widget/v1`, coordinates `55.724140/37.504663`, no old constructor container and no wrong `map-container` placeholder class in contact markup.

### Not Done

- Production deploy/cache smoke for this working tree.
- Manual success-flow evidence for forms, modal form, AI chat, prefill and staff-order.
- Staff-sale upstream/CRM evidence for `workers_json`, `team_preset`, `monthly_budget_estimate`, `endDate`.
- Yandex.Metrika goal confirmation.
- Strict release sign-off closure.
- Local PHP lint was not run because PHP CLI is not installed; CI/deploy PHP 8.4 lint remains authoritative fallback.

### Follow-Up

- Execute deploy/cache refresh, then run production visual/browser/SEO smoke and strict release sign-off when external gates are evidenced.
