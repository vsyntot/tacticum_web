# Product Accepted Risk Monitoring Decision

Дата: 04.06.2026
Статус: draft / monitoring baseline
Sprint: `docs/workflow/sprints/2026-06-04-sprint-23-accepted-risk-monitoring.md`
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`

## Назначение

Этот документ фиксирует Sprint 23 baseline для accepted-risk monitoring. Он не закрывает risks как устранённые; он делает сознательно принятые решения видимыми, назначает owners, revisit triggers, review cadence and required gates.

## Covered Gaps

| Gap | Sprint item | Current baseline | Revisit gate |
|---|---|---|---|
| `STACK-001` | S23-001 | Keep Bitrix SSR + vanilla JS for current product/lead-gen site | ADR if stack/build model changes |
| `STACK-006` | S23-002 | Keep current asset/dependency guard model | Frontend/QA gate if build, CSS or icon system changes |
| `SEC-001` | S23-003 | Keep CSRF trusted browser-source fallback only for current public lead sensitivity | Security / Integration if sensitivity changes |
| `ARCH-007` | S23-004 | Keep CSP report-only until Sprint 22 enforce checklist is complete | Security / Integration + release gate |
| `REL-002` | S23-005 | Keep product sign-off discipline as mandatory planning/release rule | PM + QA review for every product implementation |

## Monitoring Rule

Accepted-monitor means:

- the risk is intentionally accepted for now;
- it must be visible in planning and release docs;
- it has a named owner;
- it has concrete revisit triggers;
- it must not be marked `closed` unless the underlying risk is removed or replaced by an approved implementation with evidence.

## Accepted Baselines

| Baseline | Keep while | Owner | Guard |
|---|---|---|---|
| Bitrix SSR + vanilla JS | Site remains content/product/lead-gen with bounded interactions | Architect + Frontend | `bitrix:check`, `js:check`, Sprint 21 module policy |
| Static CSS/assets | Current Tailwind build, `styles/global.css`, `template_styles.css` shim and RemixIcon runtime remain sufficient | Frontend + QA | `css:check`, `template-styles:check`, `design:handoff:check` |
| Public CSRF posture | Endpoints stay public lead/contact/chat/staff flows without private proof/doc access or higher sensitivity | Security + Backend + QA | REST smoke, lead/chat contracts, Sprint 21 trigger list |
| CSP report-only | Vendor/inline baseline is not triaged for enforce and public smoke/rollback are not complete | Security + Frontend | Sprint 22 CSP checklist, browser smoke after CSP work |
| Product release evidence | Product changes can be governed by gap IDs, sign-off gates, no-PII evidence and smoke | PM + QA | `release:signoff:self-test`, `gaps:known`, sign-off docs |

## Revisit Triggers

### `STACK-001` - Stack Baseline

Open ADR / Full Feature scope if any trigger appears:

| Trigger | Required action |
|---|---|
| Product flow becomes an authenticated dashboard or multi-step app | Stack ADR, frontend architecture proposal, security/access review |
| Interaction state becomes too complex for page-scoped vanilla JS | Module/state strategy review and QA fixture plan |
| Real-time, offline, editor, account or role-based app UX is introduced | Architecture + Security + Design gate |
| Build tooling is needed for typed shared modules or imports | Asset/deploy/cache ADR and CI update |

Do not start a framework migration as generic modernization. It must be tied to a product workflow that current Bitrix SSR cannot safely support.

### `STACK-006` - Asset / Dependency Hygiene

Open Frontend/QA scope if any trigger appears:

| Trigger | Required action |
|---|---|
| New CSS source, Tailwind version, PostCSS/Vite/Webpack or bundler is introduced | Update static CSS/build docs, CI, deploy and rollback |
| Icon system changes from RemixIcon or adds a new runtime library | Design/Frontend approval, CSP/performance review, guard update |
| `template_styles.css` shim changes from comment-only | Update retirement plan and run template guard |
| `styles/global.css` is split into component CSS | Update asset layout docs and run CSS/browser smoke |
| Design token source changes | Update token contract and design handoff guards |

### `SEC-001` - Public CSRF Posture

Open Security / Integration scope if any trigger appears:

| Trigger | Required action |
|---|---|
| Private proof/document/procurement flow is added | Use Sprint 22 private access model before implementation |
| File upload or customer-specific evidence packet appears | Auth/access, CSRF, logging and release evidence review |
| Endpoint starts carrying materially more sensitive data | Re-evaluate CSRF, origin, rate, auth and masking |
| Abuse signal appears in logs/support/security review | Incident or Security / Integration lane |
| Structured CRM/upstream fields expose sensitive classification | Security + Backend + Sales review |

### `ARCH-007` - CSP Report-Only

Open Security / Integration scope if any trigger appears:

| Trigger | Required action |
|---|---|
| Security requests enforce rollout | Complete Sprint 22 CSP enforce checklist |
| New vendor script/frame/image/connect origin is added | Update CSP inventory, report-only baseline and smoke |
| Report-only violations show real unexpected sources | Triage before enforce and before broadening policy |
| `security.csp_mode=enforce` is proposed as config-only change | Block until rollback, smoke and sign-off gate are ready |

### `REL-002` - Product Sign-Off Discipline

Open PM/QA release scope if any trigger appears:

| Trigger | Required action |
|---|---|
| Product pages/content/source/schema changes | Reference gap IDs, sprint docs, product checks and release evidence |
| Forms, chat, `/price/`, CSP, endpoints or analytics change | Add focused gates and no-PII evidence requirements |
| New accepted risk is introduced | Add owner, review cadence, revisit trigger and status update rule |
| Release checker/sign-off gates change | Run self-test and update docs/checker together |

## Review Cadence

| Cadence | Owner | Scope |
|---|---|---|
| Every product implementation issue | PM + owner | Confirm affected gap IDs and whether accepted baseline is touched |
| Monthly product/platform review | PM + Architect + Security + QA | Review accepted risks, blockers and trigger status |
| After security or release incident | Security + QA + DevOps | Re-evaluate CSRF, CSP and evidence assumptions |
| After new frontend/build/design tool proposal | Architect + Frontend + Designer + QA | Re-evaluate stack/asset baselines |
| After deploy involving product/forms/chat/price/security | QA + DevOps | Confirm sign-off evidence and no-PII discipline |

## Issue / Plan Snippet

Any issue touching an accepted baseline should include:

```markdown
### Accepted-risk impact
- affected accepted risk IDs:
- baseline kept or revisit triggered:
- owner:
- required lane if revisit is triggered:
- ADR/Security/QA gates:
- verification commands:
- release evidence:
```

## Status Update Rules

| Status change | Allowed when |
|---|---|
| `accepted-monitor` -> `in-progress` | Revisit trigger fires and an implementation/ADR/security task is opened |
| `accepted-monitor` -> `blocked` | Owner approval or external evidence is required before any safe implementation |
| `accepted-monitor` -> `closed` | Risk is removed by approved implementation and evidence, or replaced by a stricter baseline with docs and guards |
| `in-progress` -> `accepted-monitor` | Owner explicitly chooses continued acceptance with updated triggers and evidence |

Do not close a risk because a monitoring document exists.

## Verification

Expected local checks for this monitoring baseline:

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
npm run css:check
npm run template-styles:check
npm run js:check
npm run design:handoff:check
npm run release:signoff:self-test
npm run gaps:known
git diff --check
```

## Remaining Decisions

| Decision | Owner | Status |
|---|---|---|
| Approve accepted-risk review cadence | PM + Architect + Security + QA | pending |
| Confirm stack baseline remains sufficient | Architect + Frontend | accepted-monitor |
| Confirm asset guard model remains sufficient | Frontend + QA | accepted-monitor |
| Confirm CSRF trigger list | Security + Backend + QA | accepted-monitor / approval pending |
| Confirm CSP report-only trigger list | Security + Frontend + QA | accepted-monitor / approval pending |
| Confirm product sign-off issue snippet adoption | PM + QA | pending |
