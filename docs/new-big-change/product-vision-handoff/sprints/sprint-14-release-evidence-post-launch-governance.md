# Sprint 14 - Release Evidence And Post-Launch Governance

Suggested duration: 1 week plus external owner availability

Status: planned

## Sprint Goal

Закрыть production reality: deploy/cache smoke, rendered SEO evidence, manual success-flow, Metrika goals, Bitrix admin, upstream/CRM evidence and post-launch ownership. This sprint prevents local documentation and guards from being mistaken for production readiness.

## Workflow Lane

Full Feature Lane with Post-Deploy gate. Security / Integration lane is required for upstream/CRM or data-policy changes.

## Source Decisions And Gaps

| Decision | Existing gaps |
|---|---|
| `D-13` Release evidence closure | `REL-001` - `REL-006`, `ARCH-007`, `ARCH-008` |

Related gaps: `ARCH-004`, `SEO-TOBE-002`, `SEO-TOBE-005`, `PB-005`, `PB-006`.

## Inputs

- Sprint 13 implementation-ready plan and completed implementation artifacts.
- `../21-phase-5-release-evidence-closure-pack.md`
- `../28-post-challenge-decision-backlog.md`
- `../../../workflow/release-signoff-gates.md`
- `../../../workflow/post-deploy-smoke.md`
- Current release sign-off draft for product-first release.

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S14-001 | Run product-first deploy/cache smoke after release | DevOps + QA | P1 | planned |
| S14-002 | Capture rendered SEO evidence for product pages and key AS IS commercial pages | SEO + QA | P1 | planned |
| S14-003 | Run manual success-flow for default form, modal form, product CTA, chat handoff, `/price/` staff order and prefill | QA + Backend/Frontend | P1 | planned |
| S14-004 | Confirm Metrika product funnel goals without PII | PM/Marketing + QA | P1 | planned |
| S14-005 | Run authenticated Bitrix admin/public toolbar smoke | QA/Admin | P1 | planned |
| S14-006 | Confirm staff-sale upstream/CRM recovery or controlled fallback evidence | Backend + DevOps + QA | P1 | planned |
| S14-007 | Complete legacy sale alias aggregate inventory | Backend + DevOps + PM | P1 | planned |
| S14-008 | Close strict release sign-off or document explicit accepted risks | PM + QA + DevOps | P1 | planned |
| S14-009 | Create post-launch measurement and backlog review plan | PM + Analytics + Sales | P2 | planned |

## Out Of Scope

- New product features.
- New public claims without evidence.
- SEO redirects not approved before release.
- Committing PII-containing evidence.
- Closing external gates by local docs only.

## Deliverables

- Deploy/cache smoke evidence.
- Rendered SEO manifest.
- Manual success-flow evidence without PII.
- Metrika goal evidence without PII.
- Bitrix admin/public toolbar smoke evidence.
- Upstream/CRM evidence or blocker report.
- Legacy alias aggregate inventory.
- Strict release sign-off result.
- Post-launch monitoring plan.

## Gates

| Gate | Required | Notes |
|---|---|---|
| Post-deploy | Yes | Main sprint gate |
| QA | Yes | Manual and automated evidence |
| SEO | Yes | Rendered head/schema/canonical/sitemap checks |
| Analytics | Yes | Metrika goals without PII |
| DevOps | Yes | Deploy/cache/upstream visibility |
| Security / Integration | Conditional | Required if upstream or data policy changes |
| Legal | Conditional | Required if public proof/claims changed |

## Acceptance Criteria

1. Product-first deploy/cache smoke passes or has a documented blocker with owner.
2. Rendered SEO evidence includes product schema and expected metadata.
3. Manual success-flow evidence exists for affected lead flows without PII.
4. Metrika goals are confirmed or remain an explicit external blocker.
5. Bitrix admin smoke is completed or remains an explicit external blocker.
6. Staff/upstream evidence is completed or recovery is assigned with date/owner.
7. Legacy sale alias inventory has aggregate evidence.
8. Strict release sign-off passes before final closure, or accepted risks are explicitly documented.
9. `gaps:known:strict` is used only when external gates are truly closed.

## Verification

- `npm run product:gaps:check`
- `npm run design:handoff:check`
- `npm run seo:check`
- `npm run gaps:known`
- `npm run gaps:known:strict` only for final closure
- `npm run release:signoff:draft-check -- <current-product-release-signoff.json>`
- Production smoke commands from `release-signoff-gates.md`

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Upstream returns 502 again | Backend + DevOps | Keep gate blocked and document recovery evidence requirement |
| Metrika access unavailable | PM/Marketing | Keep external blocker and do not mark analytics closed |
| Bitrix admin credentials unavailable | QA/Admin | Keep external blocker |
| Evidence contains PII | QA + PM | Redact, aggregate or regenerate; do not commit |
| Release pressure hides external blockers | PM + QA | Use strict sign-off only after evidence exists |

## Sprint Review

### Done

- To be filled at sprint end.

### Not Done

- To be filled at sprint end.

### Follow-Up

- To be filled at sprint end.
