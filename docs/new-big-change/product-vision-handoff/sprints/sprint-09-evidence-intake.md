# Sprint 09 Evidence Intake

Дата: 02.06.2026

Статус: evidence intake template for Sprint 09. Use this document to collect source-backed evidence for `D-03` proof/claims and `D-04` packaging language. Do not commit PII, private customer data, raw contracts, screenshots with personal data or confidential commercial terms.

## Evidence Rules

1. Every public claim needs owner, source, date/currentness and approved wording.
2. Strong claims without evidence remain `needs-evidence` or `blocked`.
3. Private/NDA evidence can support sales materials, but not public proof UI unless public wording is approved.
4. Customer names, logos and testimonials require written permission.
5. Regulatory/security/tax claims require Legal/Security approval.
6. Evidence should be aggregate, redacted or referenced by internal source ID when sensitive.

## Status Values

| Status | Meaning | Public use |
|---|---|---|
| `available` | source exists and owner approved | allowed with source/date note |
| `pilot-artifact` | validated during pilot, not confirmed outcome | allowed as "what we validate" |
| `private-nda` | evidence may be shared privately | public request-only wording if approved |
| `rewrite` | claim direction ok, wording unsafe | rewrite before use |
| `needs-evidence` | source missing | not public proof |
| `not-supported` | should not be used | hidden |
| `blocked` | owner blocks public use | hidden |

## Claim Evidence Matrix

Fill this table during owner review. Keep sensitive source details out of Git; use safe source IDs where needed.

| Claim ID | Product | Claim family | Desired claim | Source / safe source ID | Owner | Evidence date | Public status | Approved public wording | Private/NDA wording | Where shown | Follow-up |
|---|---|---|---|---|---|---|---|---|---|---|---|
| RC-001 | all | registry | TBD | TBD | Legal + PM | TBD | needs-evidence | Do not publish confirmed status | TBD | product/procurement blocks | provide registry status |
| RC-002 | all | trusted software | TBD | TBD | Legal | TBD | needs-evidence | Do not publish confirmed status | TBD | product/procurement blocks | provide legal evidence |
| RC-004 | Platform / Forum | security certification | TBD | TBD | Security + Legal | TBD | needs-evidence | Do not publish certification claim | TBD | procurement block | provide approval |
| RC-007 | all | deployment | on-prem fully isolated contour | TBD | Architect + Security | TBD | rewrite | Deployment model is defined after architecture/security review | TBD | rollout/procurement | approve wording |
| RC-011 | all | customer logos | named customer logos | TBD | Sales + Legal | TBD | needs-evidence | Hide until written permission | TBD | proof block/homepage | collect permission |
| RC-012 | all | testimonials | named testimonials | TBD | Sales + Legal | TBD | needs-evidence | Hide until consent and fact check | TBD | proof block | collect consent |
| RC-013 | all | commercial metric | 80+ projects | TBD | Sales + PM | TBD | needs-evidence | Hide or rewrite after methodology | TBD | about/homepage | define source |
| RC-016 | Agents / Forum | automation metric | 90% automation first line | TBD | PM + Sales + Legal | TBD | needs-evidence | Do not publish numeric claim | TBD | proof/status | provide case methodology |
| RC-017 | Dev | performance metric | lead time -70%, output x2.5 | TBD | PM + Tech Lead + Legal | TBD | needs-evidence | Do not publish numeric claim | TBD | Dev/proof | provide methodology |
| RC-019 | Dev | workforce reduction | 100 FTE -> 50 FTE | TBD | PM + Legal | TBD | not-supported | Do not publish | private only if approved | none | remove from public scope |
| RC-024 | all | SLA/support | Bronze/Silver/Gold SLA | TBD | Sales + Legal | TBD | needs-evidence | Support model is fixed in commercial proposal | TBD | packaging/services | approve SLA docs |
| RC-027 | Platform | PAK | PAK supported | TBD | Architect + Legal + Sales | TBD | needs-evidence | Do not publish as confirmed offering | TBD | procurement/packaging | provide PAK spec |

## Packaging Evidence Matrix

| Package | Public v1 wording | Required source for stronger claim | Owner | Status | Approved public wording | Private/NDA wording | Follow-up |
|---|---|---|---|---|---|---|---|
| Discovery / assessment | helps choose scenario, contour, risks and pilot path | approved scope/duration/price | PM + Sales | pending | TBD | TBD | approve wording |
| Limited pilot | limited scenario with agreed acceptance criteria | pilot contract template, methodology | PM + Sales + Legal | pending | TBD | TBD | approve wording |
| SaaS | format depends on requirements and agreed contour | hosting/security/commercial spec | Architect + Legal | pending | TBD | TBD | decide public wording |
| On-prem | discussed during architecture/security review | deployment matrix, support model | Architect + Security + Legal | pending | TBD | TBD | provide evidence |
| Hybrid | designed around customer data/integration contour | responsibility matrix | Architect + Legal | pending | TBD | TBD | provide evidence |
| PAK | not confirmed public offering | PAK spec and legal/commercial decision | Architect + Sales + Legal | pending | TBD | TBD | decide if public |
| Implementation/integration | part of launch path | commercial terms | PM + Sales | pending | TBD | TBD | approve wording |
| Support/SLA | fixed in commercial proposal | SLA tiers/legal wording | Sales + Legal | pending | TBD | TBD | provide SLA docs |

## SEO Evidence For `/agents/` vs `/aiagents/`

Use aggregate, non-PII evidence only.

| Evidence item | Required source | Owner | Status | Notes |
|---|---|---|---|---|
| `/aiagents/` organic traffic | Search Console / analytics aggregate | SEO | pending | no user data |
| `/aiagents/` lead contribution | CRM/source aggregate | PM + Sales | pending | no PII |
| Ranking/query intent | SEO research | SEO | pending | aggregate queries only |
| Content uniqueness vs `/agents/` | SEO/content review | SEO + Content | pending | compare intent, not just text |
| Redirect/canonical risk | SEO technical review | SEO + Dev | pending | include rollback plan |

## Approval Checklist

| Check | Required |
|---|---|
| No raw PII committed | yes |
| No confidential contract terms committed | yes |
| Every public claim has owner/source/date | yes |
| Every blocked claim has public treatment | yes |
| Every private/NDA claim has public-safe alternative or is hidden | yes |
| Packaging claims have commercial/legal/architecture owner | yes |
| `/aiagents/` decision uses aggregate SEO/lead evidence | yes |

## Update Path

After evidence review:

1. Update `sprint-09-decision-records.md` with owner statuses.
2. Update `sprint-09-product-taxonomy-claims-packaging.md` Sprint Review section.
3. Update `14-gap-backlog-and-decision-register.md` only if gap status genuinely changes.
4. Update `16-gap-closure-action-register.json` if next actions or blockers change.
5. Run `npm run product:gaps:check`.
