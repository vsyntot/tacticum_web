# Sprint 23 — Accepted Risk Monitoring

Дата формирования: 04.06.2026
Статус: in-progress / monitoring package drafted
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Roadmap: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`
Monitoring package: `docs/workflow/product-accepted-risk-monitoring-decision-2026-06-04.md`

## Sprint Goal

Сделать accepted risks управляемыми: зафиксировать monitoring rules, revisit triggers and owner accountability для решений, которые сознательно не меняются сейчас.

## Capacity / Constraints

- Production freeze: no runtime changes by default; this is governance/monitoring scope.
- Known dependencies: Sprint 17-22 decisions and future implementation issues.
- Agents / roles:
  - PM: accepted-risk ownership and review cadence;
  - Architect: stack and ADR revisit triggers;
  - Security: CSP and CSRF accepted risk monitoring;
  - Frontend: asset hygiene and no-SPA baseline;
  - QA: guard and release evidence discipline;
  - DevOps: production evidence and deploy smoke continuity.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| S23-001 | `STACK-001` Keep Bitrix SSR + vanilla JS | Full Feature | Architect + Frontend | P3 | accepted-monitor documented | Revisit only on application-grade interaction trigger |
| S23-002 | `STACK-006` Asset/dependency hygiene guard model | Fast Fix | Frontend | P2 | accepted-monitor documented | Existing css/template/js/design guards |
| S23-003 | `SEC-001` CSRF trusted browser-source fallback | Security / Integration | Security + Backend + QA | P2 | accepted-monitor documented | Current public endpoint sensitivity |
| S23-004 | `ARCH-007` CSP report-only baseline | Security / Integration | Security + Frontend | P1 | accepted-monitor documented | Sprint 22 CSP cleanup/enforce plan |
| S23-005 | `REL-002` Product sign-off discipline | Full Feature | PM + QA | P1 | monitoring rules drafted | Sprint docs and release evidence templates |

## Out Of Scope

- SPA/framework migration.
- CSP enforce.
- CSRF model changes.
- Asset pipeline rewrite.
- Runtime implementation unless a revisit trigger fires.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | Conditional | Required if accepted baseline changes |
| Design | No | Unless accepted risk is reopened by UI/design implementation |
| QA early | Conditional | Required if monitoring trigger becomes implementation task |
| Security / Integration | Conditional | Required for CSP/CSRF changes |
| SEO | No | Unless routing/canonical risk is reopened |
| Post-deploy smoke | Conditional | Required only after implementation changes |

## Accepted Baselines

| Baseline | Current Decision | Revisit Trigger | Owner |
|---|---|---|---|
| Stack | Keep Bitrix SSR, static Tailwind and vanilla JS | Product interactions become app-like, require complex client state, offline workflow or multi-step authenticated app UX | Architect + Frontend |
| Assets | Keep static CSS build, `styles/global.css`, `template_styles.css` shim and existing guards | New build tool, icon system, token source or CSS architecture is introduced | Frontend |
| CSRF | Keep trusted browser-source fallback for current public lead form posture | Endpoint becomes private/sensitive, abuse signal appears, or structured CRM/private docs are added | Security + Backend + QA |
| CSP | Keep report-only until inline/vendor cleanup and report baseline are ready | Sprint 22 enforce checklist is complete and smoke/rollback are approved | Security + Frontend |
| Release evidence | Keep no-PII sign-off templates and require challenge IDs for product work | New product/security/form/release flow is added | PM + QA |

## Acceptance Criteria

1. Each accepted baseline has owner, revisit trigger and required lane.
2. Future issues touching accepted baseline must reference this sprint and source gap ID.
3. ADR gate is triggered when accepted baseline changes from monitor to implementation.
4. Guard commands remain part of PR/deploy discipline.
5. Release evidence continues to reject PII-like/raw evidence.
6. PM review cadence includes accepted-risk status and due triggers.

## QA / Smoke Scope

| Scenario | Tool | Expected |
|---|---|---|
| Asset guards | `css:check`, `template-styles:check`, `js:check` | Existing hygiene remains green |
| Product gap guard | `product:gaps:check` | Existing product handoff register remains green |
| Bitrix architecture | `bitrix:check` | No framework/asset/bootstrap regression |
| Release evidence | `release:signoff:self-test` | PII-like and malformed evidence rejected |
| CSP/CSRF trigger review | manual review | Triggered changes open Security / Integration scope |

## Verification

### Automated

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
npm run css:check
npm run template-styles:check
npm run js:check
npm run release:signoff:self-test
```

### Manual / Owner Evidence

- PM accepted-risk review notes.
- Security review for CSP/CSRF trigger status.
- Architect review for stack trigger status.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Accepted risk becomes invisible backlog | PM | Weekly/monthly review and issue references |
| Team treats accepted risk as closed forever | Architect + Security | Revisit triggers and ADR gate |
| New UI work bypasses asset guard model | Frontend + QA | Keep guards in PR/deploy and Sprint 20/21 dependencies |
| CSP/CSRF posture becomes insufficient for future sensitive flows | Security | Sprint 22 endpoint sensitivity matrix |
| Release sign-off weakens over time | QA + PM | Self-test and no-PII evidence templates |

## Definition Of Done

- `STACK-001`, `STACK-006`, `SEC-001`, `ARCH-007`, `REL-002` have monitoring rules and owners.
- Accepted risks are visible in sprint roadmap and future planning.
- Revisit triggers are concrete enough to open ADR/Security/Full Feature scope.
- No runtime changes are required by this sprint.

## Sprint Review

### Done

- Added `docs/workflow/product-accepted-risk-monitoring-decision-2026-06-04.md`.
- Documented accepted baselines for Bitrix SSR + vanilla JS, asset guard model, public CSRF posture, CSP report-only and product release evidence discipline.
- Added concrete revisit triggers for stack, assets, CSRF, CSP and release evidence.
- Added review cadence and issue/plan snippet for future work touching accepted baselines.
- Added status update rules that prevent false closure of accepted risks.

### Not Done

- No runtime, stack, CSP, CSRF, asset pipeline or release checker changes were made.
- PM/Architect/Security/QA owner review remains pending.
- Accepted risks remain monitored, not closed.

### Follow-Up

- PM should include accepted-risk review in product/platform cadence.
- Future issues touching stack, assets, CSRF, CSP or release evidence must reference Sprint 23 and affected gap IDs.
- If a trigger fires, open the appropriate ADR, Full Feature or Security / Integration scope before implementation.
