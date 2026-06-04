# Sprint 21 — Frontend Component Hardening

Дата формирования: 04.06.2026
Статус: in-progress / decision package drafted
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Roadmap: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`
Decision package: `docs/workflow/product-frontend-component-hardening-decision-2026-06-04.md`

## Sprint Goal

Снизить regression risk перед крупными UI/interaction изменениями: определить product component boundary, module strategy for `/price/`, forms and chat, fixture-driven smoke coverage and wrapper guards.

## Capacity / Constraints

- Production freeze: no large JS refactors without smoke baseline and rollback.
- Known dependencies: Sprint 20 state specs, Sprint 19 CTA/CRM decisions, existing form/chat contracts.
- Agents / roles:
  - Architect: partial vs component boundary and ADR gate;
  - Frontend: module split plans and selectors/contracts;
  - QA: regression smoke, fixtures and no-network checks;
  - Designer: component preview/state needs;
  - Backend: form/chat payload compatibility;
  - Security: CSRF/origin/rate constraints when forms/chat touched.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| S21-001 | `ARCH-002` Product renderer order/boundary decision | Full Feature | Architect + PM + Frontend | P1 | decision drafted | Sprint 18/19 product narrative decisions |
| S21-002 | `CMP-001` Partial vs local component criteria | Full Feature | Architect + Frontend | P2 | decision drafted | Sprint 20 component/state needs |
| S21-003 | `CMP-002` Preview fixture decision | Full Feature | Frontend + QA + Designer | P2 | decision drafted | Existing `product:block-previews` |
| S21-004 | `CMP-004` `/price/` script decomposition | Full Feature | Frontend + QA | P1 | split plan drafted | Sprint 20 `/price/` mobile state spec |
| S21-005 | `CMP-005` `forms.js` modularity plan | Full Feature | Frontend + QA | P2 | split plan drafted | Sprint 19 lead contract decision |
| S21-006 | `CMP-006` `chat-agent.js` modularity plan | Full Feature | Frontend + QA | P2 | split plan drafted | Chat contract and handoff behavior |
| S21-007 | `STACK-002` JS module/test strategy | Full Feature | Frontend + QA | P1 | policy drafted | Existing vanilla JS stack |
| S21-008 | `STACK-005` Fixture-driven smoke coverage | Full Feature | QA + Frontend | P2 | smoke map drafted | Browser smoke baseline |
| S21-009 | `CMP-007` FAQ/content wrapper guard | Fast Fix | Frontend + Backend + QA | P3 | guard scope drafted | FAQ section config behavior |
| S21-010 | `CFG-005` FAQ fallback config decision | Fast Fix | Content + Backend | P2 | decision drafted | Production config explicit/default decision |
| S21-011 | `SEC-001` CSRF trusted browser-source accepted risk review | Security / Integration | Security + Backend + QA | P2 | accepted-monitor reviewed | Endpoint sensitivity unchanged |

## Out Of Scope

- Introducing React/Vue/SPA stack.
- Refactoring price/forms/chat all at once.
- Changing upstream/form/chat payloads without Sprint 19 contract.
- Promoting all product blocks to Bitrix components by default.
- Changing CSP or private endpoint access model; handled in Sprint 22.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | Conditional | Required if local component API or configurable renderer order becomes shared architecture |
| Design | Conditional | Required for component preview/states and `/price/` mobile behavior |
| QA early | Yes | Required before JS decomposition and fixture-driven smoke |
| Security / Integration | Conditional | Required if forms/chat payload, CSRF, origin, rate or endpoint behavior changes |
| SEO | Conditional | Product renderer changes must preserve schema/render ordering and H1/head |
| Post-deploy smoke | Yes if implementation changes | Browser/CSS/JS/price/product smoke required |

## Acceptance Criteria

1. Product partial/component promotion criteria are documented.
2. Product renderer order decision is explicit: fixed v1 baseline or ordered config with guardrails.
3. Product preview fixture scope is decided: screenshot previews only or isolated component fixtures.
4. `/price/` script split plan identifies state, DOM bindings, modal, payload adapter, analytics and smoke selectors.
5. `forms.js` split plan preserves payload, consent, CSRF and analytics contracts.
6. `chat-agent.js` split plan preserves chat, prefill and lead handoff contracts.
7. JS module/test policy defines when to use ES modules, shared helpers and smoke fixtures.
8. Fixture-driven smoke map covers product blocks, forms, chat and price states targeted by Sprint 20.
9. FAQ/content wrapper guard or smoke plan prevents broad/empty FAQ regressions.
10. FAQ fallback config decision is recorded.
11. CSRF accepted risk is reviewed and remains valid only for current public endpoint sensitivity.

## QA / Smoke Scope

| Scenario | URL/API/Tool | Expected |
|---|---|---|
| Product renderer | `/platform/`, `/agents/`, `/dev/`, `/forum/` | Required blocks, schema and CTA remain stable |
| Price team builder | `/price/` | Presets, modal, workers JSON, budget and end date still smoke |
| Forms | public lead forms | Payload and response shape unchanged |
| Chat | `/`, `/calculator/`, `/price/` where chat is enabled | No regression in send, error, prefill and handoff |
| FAQ wrapper | pages with `tacticum:faq.section` | Correct section or intentional empty/fallback behavior |
| Browser runtime | visual/browser smoke | No console/page/resource blockers |

## Verification

### Automated

```bash
npm run bitrix:check
npm run js:check
npm run browser:smoke
npm run browser:smoke:price
npm run product:source:http:prod
npm run product:gaps:check
```

If CSS/JS implementation changes:

```bash
npm run e2e:css-js:local
npm run e2e:css-js:prod
```

For the current docs-only decision package, browser/runtime smoke is not required because no JS, PHP, component markup, config or payload was changed.

### Manual / Owner Evidence

- Architect approval of component boundary.
- QA approval of fixture/smoke map.
- Security acceptance for unchanged CSRF/origin posture.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| JS split breaks legacy/new mixed rollout on `/price/` | Frontend + QA | Split behind tests/smoke; preserve selectors and fallback behavior |
| Componentization creates unnecessary Bitrix overhead | Architect + Frontend | Promote only blocks with real reuse/state/API needs |
| Form/chat modularity changes payload accidentally | Frontend + Backend | Contract tests and unchanged request/response smoke |
| Fixture coverage grows without value | QA | Tie fixtures to Sprint 20 state specs and high-risk flows |
| CSRF accepted risk drifts into sensitive endpoints | Security | Endpoint sensitivity matrix in Sprint 22 |

## Definition Of Done

- `CFG-005`, `ARCH-002`, `CMP-001`, `CMP-002`, `CMP-004`, `CMP-005`, `CMP-006`, `CMP-007`, `STACK-002`, `STACK-005`, `SEC-001` have decisions, plans or explicit accepted-monitor status.
- High-risk JS work has smoke baseline and rollback plan before implementation.
- No payload/security posture changes are made accidentally.
- Component boundary is clear enough for future design implementation.

## Sprint Review

### Done

- Added `docs/workflow/product-frontend-component-hardening-decision-2026-06-04.md`.
- Fixed v1 product renderer order decision and ADR trigger for configurable order.
- Documented partial-to-component promotion criteria and preview fixture scope.
- Documented contract-preserving split plans for `/price/`, `forms.js` and `chat-agent.js`.
- Documented JS module/test policy: vanilla JS + Bitrix Asset remains v1, ES modules/bundler/framework require ADR.
- Documented fixture-driven smoke map for product blocks, `/price/`, forms, chat and FAQ wrappers.
- Documented FAQ fallback explicit/default config decision baseline.
- Reconfirmed `SEC-001` accepted-monitor triggers for private/procurement/document/file-upload flows.

### Not Done

- No runtime JS/PHP/component refactor was implemented in this sprint package.
- Owner approvals from Architect, Frontend, QA, Designer, Backend, Content and Security remain pending.
- Fixture/smoke implementation remains pending and should land only with the related runtime changes.

### Follow-Up

- Approve or amend the Sprint 21 decision package before changing `/price/`, forms, chat or product renderer boundaries.
- Add focused fixtures/smoke only when high-risk runtime changes land.
- If production should not rely on default FAQ fallback config, update real `tacticum_config.php` and capture safe `config:runtime:check` evidence.
