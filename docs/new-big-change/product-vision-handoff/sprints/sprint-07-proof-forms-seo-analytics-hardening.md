# Sprint 07 - Proof, Forms, SEO And Analytics Hardening

Suggested window: 31.08.2026 - 11.09.2026

Status: in-progress - implementation hardening slices added 01.06.2026

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
| S07-001 | Final claim scan across all new/changed pages | PM + Legal + Editor | P0 | partial-static-scan-proof-readiness |
| S07-002 | Replace/remove unapproved customer logos, testimonials, metrics | PM + Sales + Legal | P0 | partial-about-cleanup |
| S07-003 | Finalize product-aware lead fields and backend handling | Backend + QA + PM | P1 | done-with-existing-allowlist-and-scenario-select |
| S07-004 | Update lead form contract | Backend + PM + QA | P1 | done-first-slice |
| S07-005 | Update analytics taxonomy and no-PII event rules | PM + Frontend + Analytics | P1 | not-needed-no-new-events |
| S07-006 | Verify SEO metadata, canonical, sitemap and robots for all new URLs | SEO + Dev | P1 | done-static-product-guard-and-schema |
| S07-007 | Verify browser/visual behavior desktop/mobile | QA + Frontend | P1 | tooling-ready-pending-runtime-smoke |
| S07-008 | Update current-state/gap-analysis docs after implementation | PM + Dev | P1 | done-first-slice |
| S07-009 | Prepare release sign-off evidence template | QA + PM + DevOps | P1 | done-draft-pending-gates |
| S07-010 | Prepare rollback notes for new pages/navigation | DevOps + Tech Lead | P1 | done-runbook |

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

- 01.06.2026 implementation hardening slices:
  - product-aware `lead_*` context added for product pages, homepage, `/services/`, `/calculator/`, `/offer/`, `/price/`, `/aiagents/`, `/about/`;
  - `tacticum:lead.cta` now supports optional controlled `lead_scenario` select for product CTAs without new JS/CSS or REST/upstream changes;
  - known `lead_scenario` slugs are converted to readable task labels by `/local/rest/tacticum_form.php`;
  - backend handling reused existing `lead_*` allowlist, no upstream response shape change;
  - `/offer/` proof layer and detail CTA preserve current routing/canonical behavior;
  - `/price/` team/staff flow preserved without changing `price-specialist`, `workers_json` or staff endpoint;
  - `/about/` unapproved partner/status logo-style block replaced by safe technology-contours block;
  - static claim scan and `seo:check` passed locally.
- 01.06.2026 release/rollback hardening slice:
  - product-first draft sign-off added: `docs/workflow/release-signoff-2026-06-01-product-first.draft.json`;
  - product-first rollback runbook added: `docs/workflow/product-first-release-rollback-runbook.md`;
  - post-deploy smoke checklist expanded with product URLs, product forms, product sitemap and FAQ asset checks;
  - static sitemap expectation in current-state now includes `/platform/`, `/agents/`, `/dev/`, `/forum/`.
- 01.06.2026 CI/deploy smoke coverage slice:
  - PR/deploy PHP lint and deploy rsync include `/platform/`, `/agents/`, `/dev/`, `/forum/`;
  - deploy lifecycle guard uses `gaps:known` for the existing external handoff and validates product-first draft sign-off;
  - `seo:check` validates product canonical paths and product navigation/footer links;
  - `visual-smoke` includes product pages by default and runs required FAQ toggle action on product pages during browser/action smoke.
- 01.06.2026 rollout/delivery model slice:
  - product renderer and product pages now include a safe rollout/delivery model block;
  - `seo:check` guards that product pages keep rollout steps alongside FAQ and CTA qualification.
- 01.06.2026 proof readiness slice:
  - product renderer and product pages now include a claim-safe proof readiness block;
  - proof-claims matrix documents allowed pilot artifact wording;
  - `seo:check` guards that product pages keep proof readiness items.
- 01.06.2026 structured-data slice:
  - product pages now include minimal `SoftwareApplication` JSON-LD via SEO helper options;
  - `seo:check` guards product schema presence and blocks risky commercial schema fields.

### Not Done

- Full legal/PM claim approval is still required before public release.
- Public metrics/logos/testimonials/benchmarks remain blocked until evidence and legal approval.
- Browser/visual smoke still requires local Bitrix runtime or post-deploy smoke.
- Product-first strict release sign-off is not closed while draft gates remain pending.

### Follow-Up

- Close `/aiagents/` vs `/agents/` canonical/redirect decision.
- Run production/staging visual and browser smoke after deploy/cache refresh.
- Replace pending gates in product-first draft sign-off with real evidence before strict release closure.
- Add verified proof/case content only after claim register approval.
