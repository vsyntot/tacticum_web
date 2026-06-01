# Sprint 07 - Proof, Forms, SEO And Analytics Hardening

Suggested window: 31.08.2026 - 11.09.2026

Status: planned

## Sprint Goal

Закрыть качество публичного product-first релиза: proof, claims, forms, analytics, SEO, sitemap/canonical, browser smoke and documentation.

## Workflow Lane

Full Feature Lane with Security / Integration and SEO gates.

## Source Gaps

- `PV-006` Regulatory claims
- `PV-007` Case proof
- `PV-012` Lead qualification
- `PV-013` SEO
- `PV-014` Analytics
- `PV-016` Sales materials/source of truth
- `PV-018` External references
- `PV-019` Client logos/testimonials

## Inputs

- Implemented pages from Sprint 04-06.
- `../07-risk-and-claims-register.md`
- `../09-as-is-to-be-preservation-migration-map.md`
- `../../../workflow/lead-form-contract.md`
- `../../../workflow/analytics-events.md`
- `../../../workflow/seo-gap-analysis.md`
- `../../../workflow/post-deploy-smoke.md`

## In Scope

| Item | Description | Owner | Priority | Status |
|---|---|---|---|---|
| S07-001 | Final claim scan across all new/changed pages | PM + Legal + Editor | P0 | planned |
| S07-002 | Replace/remove unapproved customer logos, testimonials, metrics | PM + Sales + Legal | P0 | planned |
| S07-003 | Finalize product-aware lead fields and backend handling | Backend + QA + PM | P1 | planned |
| S07-004 | Update lead form contract | Backend + PM + QA | P1 | planned |
| S07-005 | Update analytics taxonomy and no-PII event rules | PM + Frontend + Analytics | P1 | planned |
| S07-006 | Verify SEO metadata, canonical, sitemap and robots for all new URLs | SEO + Dev | P1 | planned |
| S07-007 | Verify browser/visual behavior desktop/mobile | QA + Frontend | P1 | planned |
| S07-008 | Update current-state/gap-analysis docs after implementation | PM + Dev | P1 | planned |
| S07-009 | Prepare release sign-off evidence template | QA + PM + DevOps | P1 | planned |
| S07-010 | Prepare rollback notes for new pages/navigation | DevOps + Tech Lead | P1 | planned |

## Out Of Scope

- New product pages beyond approved scope.
- New feature development after freeze.
- New claims not already in register.
- Unplanned AI/backend integrations.

## Deliverables

- Clean claim register with public release status.
- Updated lead form contract.
- Updated analytics events documentation.
- SEO checklist results.
- Smoke checklist.
- Updated workflow docs.
- Release sign-off draft.
- Rollback plan.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | Conditional | If final form/content/URL model differs from approved plan |
| Design | Yes | Final visual QA against approved design |
| QA early | Yes | Forms, navigation, product pages |
| SEO | Yes | Must pass before release |
| Security / Integration | Yes | Forms, PII, analytics, claims |
| Legal | Yes | Claims, logos, testimonials, benchmarks |
| Post-deploy smoke | Planned | Executed in Sprint 08 |

## Acceptance Criteria

1. No page contains `needs evidence`, `private only` or `remove` claims.
2. All public metrics have approved source/status or are removed.
3. All public logos/testimonials are approved or removed.
4. Forms submit with product context without changing unsupported upstream behavior.
5. Analytics does not include PII, raw message text, raw lead context, phone/email/name or unmasked URLs with sensitive query.
6. Every public URL has correct title, description, canonical, one H1 and index/noindex decision.
7. Sitemap/canonical strategy covers new product URLs.
8. Browser and visual smoke pass for desktop/mobile.
9. Documentation reflects implemented behavior.

## Verification

Recommended:

```bash
npm run css:check
npm run template-styles:check
npm run bitrix:check
npm run config:check
npm run seo:check
npm run browser:smoke
npm run visual:smoke
npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
```

Additional manual checks:

| Scenario | Expected |
|---|---|
| Product lead form | Product context present, no PII analytics |
| Header/mobile nav | All product/service links reachable |
| Claim scan | No blocked claim on public pages |
| SEO rendered head | Correct title/H1/canonical/description |
| Old money pages | `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/` still behave according to migration decision |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Late claim rejection forces content rewrites | PM + Editor | Freeze new claims before Sprint 07 |
| Product-aware form creates CRM/upstream mismatch | Backend + QA | Preserve old response shape and adapter behavior |
| SEO finds canonical conflict late | SEO | Run check before content freeze |
| Analytics owner unavailable | PM | Use conservative no-new-event approach |

## Sprint Review

### Done

- To be filled at sprint end.

### Not Done

- To be filled at sprint end.

### Follow-Up

- To be filled at sprint end.
