# Sprint 09 Review Workbook

Дата: 02.06.2026

Статус: рабочая форма для review-сессии Sprint 09. Документ помогает провести product/claims/packaging review по `D-01` - `D-04`, но не является approval сам по себе.

## Цель Сессии

За одну или несколько рабочих сессий принять или заблокировать решения, без которых нельзя безопасно запускать TO BE дизайн и публичную product-first реализацию:

```text
D-01 taxonomy and one-liners
  -> D-02 product boundaries and /agents/ vs /aiagents/
  -> D-03 proof and claims split
  -> D-04 packaging language
```

## Участники

| Role | Required | Decision area |
|---|---|---|
| PM / Product owner | Yes | all decisions |
| Sales lead | Yes | taxonomy, buyer language, packaging, proof usefulness |
| Architect | Yes | Platform triggers, deployment/packaging feasibility |
| Legal | Yes | proof, logos, registry, SLA, PAK, regulatory wording |
| Security | Yes | procurement, data, deployment, audit, compliance wording |
| SEO | Yes | `/agents/` vs `/aiagents/`, product metadata, duplicate risk |
| Content / Editor | Recommended | public wording and implementation copy |
| Designer | Optional for this sprint | consumes decisions in Sprint 11 |

## Pre-Read

Participants should read:

- `../18-phase-1-product-decision-review-pack.md`
- `../20-phase-4-seo-content-decision-pack.md`
- `../24-post-challenge-gap-analysis.md`
- `../28-post-challenge-decision-backlog.md`
- `../07-risk-and-claims-register.md`
- `sprint-09-product-taxonomy-claims-packaging.md`
- `sprint-09-decision-records.md`

## Agenda

| Block | Duration | Output |
|---|---:|---|
| Context and constraints | 10 min | Confirm this is decision sprint, not design/code sprint |
| D-01 taxonomy and Platform triggers | 25 min | Approve/rewrite product names, one-liners and Platform top triggers |
| D-02 boundaries and `/aiagents/` | 25 min | Approve Agents/Forum boundary and SEO direction |
| D-03 proof and claims | 40 min | Classify P0/P1 claims and define public/private/blocked statuses |
| D-04 packaging language | 25 min | Approve safe public packaging vocabulary |
| Closure and owners | 15 min | Assign evidence blockers, update decision records |

If Legal/Security/SEO evidence is missing, the correct outcome is `blocked with owner/date`, not silent approval.

## Decision Statuses

Use these statuses in `sprint-09-decision-records.md`.

| Status | Meaning |
|---|---|
| `approved` | Owner approved wording/decision and evidence requirements are satisfied |
| `approved-v1-safe` | Owner approves safe public v1 wording while stronger claim remains blocked |
| `rewrite-required` | Direction is acceptable but public wording must be rewritten |
| `evidence-blocked` | Decision cannot close until source/evidence exists |
| `deferred` | Not needed for current release scope |
| `rejected` | Option must not be used |

## D-01 Worksheet - Product Taxonomy And One-Liners

### Questions To Answer

| Question | Required decision |
|---|---|
| Are public product names exactly `Tacticum Platform`, `Tacticum Agents`, `Tacticum Dev`, `Tacticum Forum`? | approve / rename |
| Do Russian descriptors appear beside English names? | yes / no / page-specific |
| What is the umbrella phrase? | approve one phrase |
| Which Platform buying triggers are public top 3? | choose top 3 |
| Does Platform sound like purchasable product, architecture layer, or both? | choose framing |

### Draft Baseline To Review

| Product | Draft public descriptor | Review decision |
|---|---|---|
| Tacticum Platform | инфраструктурное ядро для корпоративных AI-продуктов | pending |
| Tacticum Agents | корпоративные AI-ассистенты для внутренних функций и знаний | pending |
| Tacticum Dev | governance-слой для AI-assisted engineering workflow | pending |
| Tacticum Forum | управляемая диалоговая платформа для клиентских коммуникаций | pending |

Recommended v1 umbrella phrase:

```text
корпоративная AI-экосистема Tacticum: Platform, Agents, Dev and Forum
```

### Output

- approved product names;
- approved descriptors;
- approved one-liners for hero/meta/navigation;
- Platform top triggers;
- rejected terms list.

## D-02 Worksheet - Agents / Forum / `/aiagents/`

### Boundary Decision

| Area | Agents | Forum |
|---|---|---|
| Primary context | internal business functions and knowledge | customer/service communication flow |
| Main owner | HR, legal, finance, support, IT, knowledge owner | CX, contact center, digital channel, support operations |
| Control model | documents, access, sources, handoff | scenario graph, checkpoints, escalation, journal |
| Pilot question | which internal function assistant to test first | which request stream or channel flow to control first |

### `/agents/` vs `/aiagents/` Options

| Option | Choose when | Evidence required | Decision |
|---|---|---|---|
| Keep both differentiated | `/aiagents/` has broad/demo/traffic value | traffic, leads, content uniqueness | pending |
| Canonical `/aiagents/` to `/agents/` | content becomes substantially duplicate | SEO risk review, rollback plan | pending |
| 301 redirect `/aiagents/` to `/agents/` | legacy route no longer needed | traffic/CRM evidence and PM approval | pending |

Recommended v1 safe baseline:

```text
/agents/ = primary product URL for Tacticum Agents.
/aiagents/ = compatibility/demo/legacy AI-bot entry until SEO traffic and lead evidence are reviewed.
No canonical or redirect change without SEO approval.
```

### Output

- approved boundary copy;
- cross-linking rules;
- `/agents/` vs `/aiagents/` decision or evidence blocker;
- SEO follow-up owner.

## D-03 Worksheet - Proof And Claims Split

### Status Model

| Public status | Meaning | Public UI treatment |
|---|---|---|
| `available` | source exists and owner approved | can show as proof with source/date |
| `pilot-artifact` | validated during pilot, not final result | show as "what we check in pilot" |
| `private-nda` | evidence may be shared privately | request-oriented copy only |
| `rewrite` | claim direction ok, wording unsafe | rewrite before use |
| `needs-evidence` | source missing | do not publish as proof |
| `not-supported` | claim must not be used | hidden from public site |

### Claim Families To Classify

| Claim family | Current safe default | Owner | Review status |
|---|---|---|---|
| Product readiness / production-ready | pilot / rollout decision after assessment | PM + Architect | pending |
| Registry / trusted software / ПП №1937 | do not publish confirmed status | Legal | pending |
| Security / ФЗ-152 / ФЗ-187 / ФСТЭК / ФСБ | safe-copy only | Legal + Security | pending |
| Deployment / SaaS / on-prem / hybrid / PAK | depends on agreed architecture | Architect + Legal | pending |
| Performance metrics | hide or private case only | PM + Sales + Legal | pending |
| Logos/testimonials | hide until written permission | Sales + Legal | pending |
| Connectors/channels | readiness table only after evidence | Architect + Product | pending |
| SLA/support | commercial proposal only | Sales + Legal | pending |

### Evidence Intake

For every claim considered for public use:

| Field | Required |
|---|---|
| Claim ID | Existing `RC-*` if possible |
| Product | Platform / Agents / Dev / Forum / all |
| Claim text | Exact desired wording |
| Source | Document, case, legal status, test matrix, owner statement |
| Evidence date | Date or currentness rule |
| Owner | Person/function accountable |
| Public status | allowed status from above |
| Approved public wording | exact public copy |
| Private/NDA wording | if applicable |
| Where shown | page/block/component |

### Output

- evidence matrix;
- blocked claim list;
- allowed public wording list;
- design constraints for proof/status UI;
- follow-up owners for missing evidence.

## D-04 Worksheet - Packaging Language

### Packaging Review Table

| Package | Public v1 wording | Strong claim blocked until | Decision |
|---|---|---|---|
| Discovery / assessment | short diagnostic of scenario, data, risks and rollout path | fixed scope/duration/price approved | pending |
| Fixed pilot | limited pilot with agreed acceptance criteria | metric/production guarantee evidence | pending |
| SaaS | delivery format depends on requirements and agreed contour | hosting/security/commercial spec | pending |
| On-prem | discussed within architecture and security review | tested deployment matrix and support model | pending |
| Hybrid | designed for the customer's data/integration contour | responsibility matrix | pending |
| PAK | do not use as confirmed public offering | PAK spec and legal/commercial decision | pending |
| Implementation/integration | part of launch path | fixed commercial terms | pending |
| Support/SLA | fixed in commercial proposal | SLA tiers and legal wording | pending |

Recommended v1 safe baseline:

```text
Public site can describe assessment, limited pilot, implementation and rollout decision.
SaaS/on-prem/hybrid/PAK/SLA are discussed as architecture/commercial options, not confirmed universal offers.
/price/ remains team/project composition, not product license pricing.
```

### Output

- packaging matrix;
- allowed public wording;
- blocked terms;
- implications for `/price/`, `/services/`, product pages and schema.

## Closure Checklist

| Item | Required before Sprint 09 close |
|---|---|
| D-01 status recorded | yes |
| D-02 status recorded | yes |
| D-03 status recorded | yes |
| D-04 status recorded | yes |
| Owner blockers assigned | yes |
| Public wording changes listed | yes |
| Gaps proposed for status update only with evidence | yes |
| `npm run product:gaps:check` green | yes |

## After The Session

1. Update `sprint-09-decision-records.md`.
2. Update `14-gap-backlog-and-decision-register.md` only for decisions that genuinely changed status.
3. Update `16-gap-closure-action-register.json` if next actions/evidence changed.
4. Update product copy or product data only after owner-approved wording exists.
5. Keep blocked gaps blocked until evidence exists.
