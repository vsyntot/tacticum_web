# Sprint 08 - Release, Post-Launch And Handoff

Suggested window: 14.09.2026 - 18.09.2026

Status: planned

## Sprint Goal

Выпустить product-first версию сайта, пройти production smoke and sign-off, зафиксировать post-launch measurement и передать командным владельцам дальнейшие итерации.

## Workflow Lane

Full Feature Lane with mandatory post-deploy smoke.

## Source Gaps

All release gates from `PV-001` - `PV-020`.

## Inputs

- Sprint 04-07 implemented changes.
- Sprint 07 release sign-off draft.
- `../../../workflow/post-deploy-smoke.md`
- `../../../workflow/release-signoff-gates.md`
- `../../../workflow/current-state.md`
- `../../../workflow/gap-analysis.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S08-001 | Run pre-release automated checks | QA + Dev | P1 | planned |
| S08-002 | Deploy through approved workflow with cache clear | DevOps | P1 | planned |
| S08-003 | Run production visual/browser smoke | QA + DevOps | P1 | planned |
| S08-004 | Run production SEO check | SEO + DevOps | P1 | planned |
| S08-005 | Run manual/staging success-flow for forms and product CTAs | QA + PM | P1 | planned |
| S08-006 | Confirm analytics goals/events without PII | PM + Analytics + QA | P1 | planned |
| S08-007 | Close release sign-off | PM + QA + DevOps | P1 | planned |
| S08-008 | Update current-state/gap-analysis and sprint review docs | PM + Dev | P1 | planned |
| S08-009 | Create post-launch backlog | PM + Product + SEO | P2 | planned |
| S08-010 | Prepare handoff note for sales/design/content | PM | P2 | planned |

## Out Of Scope

- New product scope after release freeze.
- New unapproved claims.
- Last-minute design redesign.
- New external integrations.

## Deliverables

- Production release.
- Post-deploy smoke evidence.
- Release sign-off.
- Updated workflow docs.
- Post-launch backlog.
- Handoff note.
- Rollback/incident notes if needed.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | No | Unless emergency architecture change happens |
| Design | Yes | Final visual sign-off |
| QA early | Already completed | Sprint 07 |
| SEO | Yes | Production check |
| Security / Integration | Yes | Forms, PII, analytics, regulatory claims |
| Post-deploy smoke | Yes | Blocking |
| PM sign-off | Yes | Blocking |

## Acceptance Criteria

1. Production deploy completes through approved workflow.
2. Cache clear is confirmed.
3. Homepage and all product pages render without browser errors.
4. Mobile navigation works.
5. Product-aware CTAs/forms work in staging or controlled production smoke.
6. No PII is sent to analytics.
7. SEO checks pass for new and changed URLs.
8. Release sign-off is closed or has explicit accepted risks.
9. Docs reflect the release state.
10. Post-launch backlog is created.

## Verification

Pre-release:

```bash
npm run css:check
npm run template-styles:check
npm run bitrix:check
npm run config:check
npm run seo:check
npm run browser:smoke
npm run visual:smoke
```

Post-deploy:

```bash
npm run visual:smoke:prod
npm run browser:smoke:prod
npm run seo:check:prod
npm run release:signoff:check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
```

Manual smoke:

| Scenario | URL/API | Expected |
|---|---|---|
| Homepage | `/` | Ecosystem hero, product nav, CTAs, no browser errors |
| Platform | `/platform/` or approved URL | Page loads, CTA works, safe claims |
| Agents | `/agents/` or `/aiagents/` | Page loads, CTA works, canonical strategy correct |
| Dev | `/dev/` | No workforce reduction public claim |
| Forum | `/forum/` | Scenario+LLM story visible, CTA works |
| Existing money pages | `/services/`, `/price/`, `/calculator/`, `/offer/` | No regression in core flows |
| Forms | relevant pages | Required fields, consent, CSRF, success/error state |
| Analytics | affected events | No PII params |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Production cache serves mixed assets | DevOps | Clear Bitrix/component/composite/template caches and rerun smoke |
| New URL returns wrong canonical | SEO + Dev | Run production SEO check before sign-off |
| Form success-flow cannot be safely tested in prod | QA + PM | Use staging or controlled lead with evidence policy |
| Late blocker after deploy | DevOps + Tech Lead | Use rollback notes from Sprint 07 |
| External approvals still missing | PM | Remove blocked content or mark accepted risk before release |

## Post-Launch Measurement

Track for at least 2-4 weeks:

- product page visits;
- product page -> CTA conversion;
- lead form completion by product interest;
- homepage route card clicks;
- navigation usage;
- SEO indexation/canonical status;
- browser error rate;
- lead quality from Sales feedback.

## Sprint Review

### Done

- To be filled at sprint end.

### Not Done

- To be filled at sprint end.

### Follow-Up

- To be filled at sprint end.

