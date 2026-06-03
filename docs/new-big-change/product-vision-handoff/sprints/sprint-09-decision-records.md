# Sprint 09 Decision Records

Дата: 02.06.2026

Статус: ready-for-owner-review draft decision records. These records contain recommended v1 baseline, not owner approval. Use `sprint-09-review-workbook.md` and `sprint-09-approval-request.md` to run the review session and then update statuses here.

## Status Legend

| Status | Meaning |
|---|---|
| `draft` | prepared for review, not approved |
| `approved` | approved by accountable owner |
| `approved-v1-safe` | safe v1 public wording approved; stronger claim remains blocked |
| `rewrite-required` | direction accepted, wording must change |
| `evidence-blocked` | cannot close until source/evidence exists |
| `deferred` | outside current release |
| `rejected` | must not be used |

## D-01 - Product Taxonomy And Public One-Liners

Status: `draft`

Related gaps: `PB-001`, `PB-002`

Primary owners: PM + Sales + Architect

### Recommended V1 Baseline

| Product | Public name | Descriptor | One-liner draft | Boundary |
|---|---|---|---|---|
| Platform | Tacticum Platform | инфраструктурное ядро | Единый слой для LLM gateway, RAG, MCP-инструментов, доступов, аудита and rollout control в корпоративных AI-продуктах. | Not a single chatbot, not one-off integration |
| Agents | Tacticum Agents | внутренние AI-ассистенты | Корпоративные ассистенты для HR, legal, finance, support, IT and knowledge workflows поверх общей Platform-инфраструктуры. | Not customer dialog platform, not uncontrolled bot constructor |
| Dev | Tacticum Dev | AI-assisted engineering governance | Управляемые AI-workflows для разработки: requirements, architecture, design compliance, quality gates and review discipline. | Not staff-reduction promise, not generic coding training |
| Forum | Tacticum Forum | клиентские диалоги | Управляемая диалоговая платформа для клиентских коммуникаций: сценарии, LLM enrichment, routing, escalation, journal and analytics. | Not internal assistant catalog, not pure LLM chatbot |

Umbrella phrase draft:

```text
Tacticum - корпоративная AI-экосистема: Platform, Agents, Dev and Forum.
```

Top Platform trigger draft:

1. Several AI/RAG/bot initiatives duplicate infrastructure.
2. Enterprise environment needs shared access, audit, data and provider governance.
3. Pilot must have a path to production decision, not stay a script/demo.

### Approval Questions

| Question | Decision |
|---|---|
| Public product names approved exactly? | pending |
| Russian descriptors approved? | pending |
| Umbrella phrase approved? | pending |
| Platform top triggers approved? | pending |
| Any terms rejected? | pending |

### Approved Decision

To be filled after review.

### Implementation Impact

Potentially affects:

- homepage hero and ecosystem copy;
- product page hero/meta;
- header/footer labels;
- design-system component naming;
- SEO title/description/H1 drafts.

### Required Evidence / Follow-Up

- PM/Sales approval;
- Architect approval for Platform triggers;
- SEO/content review before metadata changes.

## D-02 - Agents / Forum / `/aiagents/` Boundary

Status: `draft`

Related gaps: `PB-003`, `PB-008`, `SEO-TOBE-002`

Primary owners: PM + Product + SEO

### Recommended V1 Baseline

```text
/agents/ is the primary product URL for Tacticum Agents.
/aiagents/ remains a compatibility/demo/legacy AI-bot entry until SEO traffic, rankings and lead evidence are reviewed.
Do not canonical or redirect /aiagents/ before SEO approval and rollback plan.
```

### Boundary Copy Draft

| Decision area | Agents | Forum |
|---|---|---|
| Public framing | внутренние AI-ассистенты для рабочих функций и знаний | диалоговая платформа для клиентских коммуникаций |
| Data context | documents, policies, process knowledge, internal systems | dialog history, scenario graph, support content, channel context |
| Control model | assistant catalog, source boundaries, access, handoff | scenario graph, checkpoints, escalation, journal, funnel analytics |
| Pilot question | which internal function and knowledge base should be piloted first | which customer request stream should be controlled first |

### `/aiagents/` Decision Options

| Option | Recommended status | Reason |
|---|---|---|
| Keep both differentiated | recommended v1 | safest until SEO/lead evidence exists |
| Canonical `/aiagents/` to `/agents/` | evidence-blocked | needs duplicate-content and traffic review |
| 301 redirect `/aiagents/` to `/agents/` | evidence-blocked | needs traffic/CRM evidence and rollback |

### Approval Questions

| Question | Decision |
|---|---|
| Agents/Forum boundary copy approved? | pending |
| Cross-linking rules approved? | pending |
| `/aiagents/` kept differentiated for v1? | pending |
| SEO evidence needed before canonical/redirect? | pending |

### Approved Decision

To be filled after review.

### Implementation Impact

Potentially affects:

- `/agents/` and `/forum/` comparison blocks;
- `/aiagents/` copy and cross-links;
- sitemap/canonical decisions;
- product navigation and footer labels;
- SEO metadata.

### Required Evidence / Follow-Up

- SEO traffic/rankings/leads review for `/aiagents/`;
- PM/Product approval of boundary copy;
- SEO approval before any canonical/redirect change.

## D-03 - Proof And Claims Public / Private Split

Status: `draft`

Related gaps: `PB-005`, `PB-006`, `UI-005`, `SEO-TOBE-003`

Primary owners: PM + Sales + Legal + Security

### Recommended V1 Baseline

Public TO BE copy can show:

- product identity and safe use-case/pilot framing;
- pilot artifacts and proof readiness;
- architecture/security/procurement topics to discuss;
- request-oriented private/NDA wording only after owner approval.

Public TO BE copy must not show as confirmed proof:

- registry/trusted software status;
- FSTEC/FSB/KII sufficiency;
- universal SaaS/on-prem/PAK readiness;
- automation/performance percentages;
- customer logos/testimonials;
- SLA tiers;
- connector/channel readiness;
- workforce reduction.

### Claim Family Draft Status

| Claim family | Default v1 status | Related RC IDs | Public treatment |
|---|---|---|---|
| Product readiness / production-ready | `rewrite` | RC-001, RC-002 | pilot / rollout decision after assessment |
| Registry / trusted software | `needs-evidence` | RC-001, RC-002 | do not publish confirmed status |
| Security/regulatory | `rewrite` / `needs-evidence` | RC-004, RC-022 | safe-copy only |
| Deployment / on-prem / PAK | `rewrite` / `needs-evidence` | RC-003, RC-007, RC-027 | depends on agreed architecture |
| Foreign/proxy model access | `remove/private only` | RC-009, RC-010 | not public |
| Logos/testimonials | `needs-evidence` | RC-011, RC-012 | hide until written approval |
| Performance metrics | `needs-evidence` | RC-013 - RC-018 | hide or private case only |
| Workforce reduction | `private only` / `remove` | RC-019 | not public |
| SLA/support | `needs-evidence` | RC-024 | commercial proposal only |
| Connectors/channels | `needs-evidence` | RC-025, RC-026 | readiness table only after evidence |

### Approval Questions

| Question | Decision |
|---|---|
| Is pilot-artifact proof UI allowed for v1? | pending |
| Which RC IDs can be public after rewrite? | pending |
| Which RC IDs are private/NDA only? | pending |
| Which RC IDs must be removed from public scope? | pending |
| Does proof/status UI taxonomy need Legal approval before design starts? | pending |

### Approved Decision

To be filled after review.

### Implementation Impact

Potentially affects:

- proof/status UI;
- product proof blocks;
- `/about/`, homepage, product pages;
- SEO snippets and schema exclusions;
- sales/private handoff materials.

### Required Evidence / Follow-Up

- evidence matrix with owner/source/date;
- Legal/Security wording approval;
- Sales approval for any public case/logo/testimonial;
- Designer proof/status UI constraints.

## D-04 - Packaging Language

Status: `draft`

Related gaps: `PB-007`

Primary owners: PM + Sales + Architect + Legal

### Recommended V1 Baseline

Public site can describe:

- discovery / assessment;
- limited pilot;
- implementation and integration path;
- production/rollout decision after pilot;
- support model as part of commercial proposal.

Public site should not present as confirmed universal offering:

- fixed SaaS terms;
- universal on-prem readiness;
- PAK;
- SLA tiers;
- license pricing;
- registry/compliance package.

`/price/` remains staff/team/project composition, not product license pricing.

### Packaging Matrix Draft

| Package | Draft public wording | Strong claim blocked until | Draft status |
|---|---|---|---|
| Discovery / assessment | helps choose scenario, contour, risk and pilot path | fixed scope/duration/price | `approved-v1-safe` pending owner |
| Limited pilot | limited scenario with agreed acceptance criteria | metrics and production guarantee | `approved-v1-safe` pending owner |
| SaaS | format depends on requirements and agreed contour | hosting/security/commercial spec | `rewrite` |
| On-prem | discussed during architecture/security review | tested deployment matrix | `rewrite` |
| Hybrid | designed around customer data/integration contour | responsibility matrix | `rewrite` |
| PAK | not confirmed public offering | PAK spec and legal/commercial decision | `needs-evidence` |
| Implementation/integration | part of launch path | fixed commercial terms | `approved-v1-safe` pending owner |
| Support/SLA | fixed in commercial proposal | SLA tiers and legal wording | `needs-evidence` |

### Approval Questions

| Question | Decision |
|---|---|
| Which packages are public for v1? | pending |
| Which packages are private/NDA only? | pending |
| Is `/price/` explicitly not product license pricing? | pending |
| Are SaaS/on-prem/hybrid terms allowed as options or blocked? | pending |
| Is PAK mentioned publicly at all? | pending |

### Approved Decision

To be filled after review.

### Implementation Impact

Potentially affects:

- product page rollout/procurement sections;
- `/services/` copy;
- `/price/` framing;
- proof/status UI and schema exclusions;
- sales handoff.

### Required Evidence / Follow-Up

- PM/Sales commercial approval;
- Architect feasibility note;
- Legal approval for PAK/SLA/on-prem wording;
- SEO/schema review if offers/pricing language is introduced.

## Sprint 09 Closure Log

| Decision | Status | Owner | Evidence / blocker | Next action |
|---|---|---|---|---|
| `D-01` | ready-for-owner-review | PM + Sales + Architect | pending owner approval | collect owner response via `sprint-09-approval-request.md` |
| `D-02` | ready-for-owner-review | PM + Product + SEO | pending SEO review | collect owner response and aggregate SEO evidence |
| `D-03` | ready-for-owner-review | PM + Sales + Legal + Security | pending evidence matrix | collect evidence via `sprint-09-evidence-intake.md` |
| `D-04` | ready-for-owner-review | PM + Sales + Architect + Legal | pending packaging approval | collect owner response via `sprint-09-approval-request.md` |

## Status Update Rules

- Do not mark `PB-005` or `PB-006` closed unless evidence matrix and approved wording exist.
- Do not mark `PB-008` / `SEO-TOBE-002` closed unless SEO decision and implementation path are approved.
- Do not mark `PB-007` closed unless packaging matrix is approved.
- If only safe v1 wording is approved, use `approved-v1-safe` in this document and keep stronger claim gaps open/blocked as needed.
