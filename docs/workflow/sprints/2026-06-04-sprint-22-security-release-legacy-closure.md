# Sprint 22 — Security, Release And Legacy Closure

Дата формирования: 04.06.2026
Статус: in-progress / decision package drafted
Source register: `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
Roadmap: `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md`
Decision package: `docs/workflow/product-security-release-legacy-closure-decision-2026-06-04.md`

## Sprint Goal

Закрыть security/release runway вокруг product-first слоя: future sensitive endpoint model, CSP enforce path, endpoint rate classes, no-PII release evidence discipline and legacy sale alias finalization.

## Capacity / Constraints

- Production freeze: CSP enforce, private docs/proof flows and alias removal require explicit approval and rollback.
- Known dependencies: Sprint 17 proof/private evidence decisions, Sprint 19 CRM/form decisions, external logs/CRM access after 30.06.2026.
- Agents / roles:
  - Security: endpoint sensitivity, CSP, auth/access model;
  - Backend: rate limits, origin/IP/proxy config and endpoint contracts;
  - DevOps: trusted proxies, IP allowlist, deploy/cache and access logs;
  - QA: release sign-off, no-PII evidence and smoke;
  - PM: accepted risks and owner assignments;
  - Frontend: inline/vendor cleanup and asset behavior;
  - Legal: private proof/document access constraints.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| S22-001 | `CFG-006` Future IP/auth/proxy controls | Security / Integration | Security + Backend + DevOps | P2 | decision drafted | Endpoint sensitivity matrix |
| S22-002 | `ARCH-008` Endpoint rate-limit classes | Security / Integration | Security + Backend | P2 | decision drafted | Future sensitive flow policy |
| S22-003 | `SEC-002` Private proof/document access model | Security / Integration | Security + PM + Backend | P1 | decision drafted / implementation blocked | Sprint 17 private proof split |
| S22-004 | `ARCH-007` CSP report-only/enforce trigger | Security / Integration | Security + Frontend | P1 | accepted-monitor reviewed | Vendor/report baseline |
| S22-005 | `SEC-003` CSP inline/vendor cleanup backlog | Security / Integration | Security + Frontend | P2 | backlog drafted | Current report-only header |
| S22-006 | `ARCH-012` Safe release evidence model | Security / Integration | QA + PM + Security | P1 | in-progress; future gate checker support added | Current sign-off templates |
| S22-007 | `REL-002` Future product sign-off discipline | Full Feature | PM + QA | P1 | in-progress; security-sensitive gates wired into sign-off | Challenge register and sprint docs |
| S22-008 | `REL-001` Legacy sale alias external inventory | Security / Integration | Backend + DevOps + PM | P1 | blocked / final-mode matrix drafted | Access logs/CRM report after 30.06.2026 |

## Out Of Scope

- Enforcing CSP without report triage and rollback.
- Implementing private proof/document downloads before access model approval.
- Removing legacy aliases before external inventory and final mode decision.
- Adding authentication to public lead forms without product/security decision.
- Storing raw logs, cookies, sessions, request bodies or PII evidence.

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | Conditional | Required for new security pattern, private access model, CSP enforce or alias lifecycle change |
| Design | Conditional | Required only if private proof/document request UX is introduced |
| QA early | Yes | Required for release evidence, CSP, endpoints and alias changes |
| Security / Integration | Yes | Primary lane for most sprint items |
| SEO | Conditional | Legacy alias/canonical changes and private proof pages must not create index risk |
| Legal/Claims | Conditional | Required for private proof/doc access and evidence wording |
| Post-deploy smoke | Yes if implementation changes | Headers, forms, endpoints, aliases, SEO and browser smoke |

## Acceptance Criteria

1. Endpoint sensitivity matrix classifies public forms, chat, prefill, staff sale, health/config, future document/proof flows and admin-like flows.
2. Each endpoint class has origin, CSRF, rate limit, IP allowlist/proxy, auth/signed-token and logging requirements.
3. Private proof/document access model is approved before any implementation.
4. CSP enforce trigger is explicit: required inline cleanup, vendor/report baseline, smoke and rollback.
5. CSP inline/vendor cleanup backlog identifies concrete sources and order.
6. Release evidence templates remain no-PII and reject raw production evidence.
7. Future product work must reference affected challenge IDs and sprint docs in issue/plan/release sign-off.
8. Legacy sale alias inventory repeats after 30.06.2026 and includes no-PII access log aggregates plus CRM/upstream source report.
9. Alias migration/final mode decision is recorded before removal or behavior change.

## QA / Smoke Scope

| Scenario | URL/API/Tool | Expected |
|---|---|---|
| CSP report-only | public pages | Header remains report-only until enforce decision; no unexpected browser blockers |
| Security headers | public pages/endpoints | Headers match accepted mode |
| Public POST endpoints | lead/chat/prefill/staff | Existing CSRF/origin/rate behavior remains as scoped |
| Future private flow fixture | planned only | Access model blocks unauthenticated/invalid access if implemented |
| Release sign-off | sign-off checker | Rejects placeholder/PII-like evidence and unknown gates |
| Legacy alias inventory | `npm run legacy:sale:inventory:logs` | Aggregate-only endpoint/method/status/day counts |

## Verification

### Automated

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
npm run release:signoff:self-test
npm run sale:sunset:check
```

If production/release checks are in scope:

```bash
npm run release:public-precheck:prod
npm run release:manual-gates:helper
npm run legacy:sale:inventory:logs
```

### Manual / Owner Evidence

- Security approval of endpoint sensitivity matrix.
- PM/Legal approval of private proof/document access model.
- QA approval of evidence templates.
- DevOps access-log aggregate and CRM/upstream inventory for legacy aliases.

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| CSP enforce breaks Bitrix toolbar, Metrika or map widgets | Security + Frontend + QA | Keep report-only until violations are triaged and smoke passes |
| Private proof flow leaks sensitive documents | Security + PM + Legal | Access model and no-PII logging review before implementation |
| Legacy alias removal breaks unknown external caller | Backend + DevOps + PM | Repeat aggregate access-log inventory and CRM source report |
| Release evidence stores PII accidentally | QA + PM | Sign-off checker and evidence skeleton discipline |
| IP/trusted proxy config misclassifies clients | DevOps + Backend | Proxy ownership and environment matrix before enforcement |

## Definition Of Done

- `CFG-006`, `ARCH-007`, `ARCH-008`, `ARCH-012`, `REL-001`, `REL-002`, `SEC-002`, `SEC-003` have decisions, evidence or explicit blockers.
- CSP remains report-only unless enforce checklist is complete.
- Private proof/document flows are blocked until access model is approved.
- Legacy alias final mode is not changed until external inventory is complete.
- Release evidence rules are linked from future product implementation tasks.

## Sprint Review

### Done

- Added `docs/workflow/product-security-release-legacy-closure-decision-2026-06-04.md`.
- Drafted endpoint sensitivity matrix for public read, config health, public lead/chat/staff/prefill/resolver, legacy aliases, private proof/docs and internal integrations.
- Drafted IP allowlist/trusted proxy usage rules: current empty config remains accepted for public flows only.
- Drafted rate-limit class taxonomy without hardcoding thresholds.
- Drafted private proof/document model: request-only v1, no public private downloads until access model approval.
- Reconfirmed CSP report-only baseline and drafted enforce prerequisites plus cleanup backlog.
- Drafted release evidence extension rules for future `csp-enforce`, `sensitive-endpoint-access`, `endpoint-risk-class` and `legacy-final-mode` gates.
- Added `release-signoff-check.mjs` support for `csp-enforce`, `sensitive-endpoint-access`, `endpoint-risk-class` and `legacy-final-mode`, including safe evidence shapes and raw/PII-like evidence rejection.
- Added release sign-off self-test coverage for valid future gate evidence and negative cases for missing CSP rollback, invalid access model, HTTP endpoint evidence, incomplete legacy alias set and raw logs key.
- Updated current sign-off example/drafts so new gates are explicitly `not_applicable` when their trigger does not apply.
- Drafted legacy sale alias final-mode matrix after full-window logs and CRM/upstream report.

### Not Done

- No CSP enforce, endpoint auth/rate/config change, private access implementation, alias removal, `410` or redirect was implemented.
- Full-window legacy alias access-log and CRM/upstream inventory remains external and date-blocked until after `2026-06-30`.
- Security/Backend/DevOps/QA/PM/Legal approvals remain pending.

### Follow-Up

- Security + Backend should approve or amend endpoint sensitivity and rate class matrix.
- Security + Frontend + QA should approve CSP enforce checklist before any config switch to `enforce`.
- PM + Legal + Security should approve private proof/document access before any gated content implementation.
- Backend + DevOps + PM must repeat legacy alias inventory after `2026-06-30` and choose final mode before `2026-09-30`.
