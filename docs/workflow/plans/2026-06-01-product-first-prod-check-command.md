# Codex Plan: Product-First Production Check Command

Issue: internal product-first release closure continuation
Gap ID: product-first release evidence handoff
Workflow lane: Full Feature
Owner agent: Codex
Date: 01.06.2026

## Goal

Свести automated product-first post-deploy checks в одну npm-команду, чтобы DevOps/QA не закрывали release gates неполным набором evidence после deploy/cache refresh.

## Non-Goals

- Не запускать production smoke до фактического deploy/cache refresh.
- Не закрывать ручные gates за Метрику, Bitrix admin, CRM/upstream или real success-flow.
- Не менять runtime сайта, формы, REST contracts or analytics.

## Planned Changes

| File | Change |
|---|---|
| `package.json` | Add `release:product-first:prod-check` aggregate script |
| `docs/workflow/post-deploy-smoke.md` | Document the aggregate command in the product-first post-deploy checklist |
| `docs/workflow/product-first-release-rollback-runbook.md` | Reference the aggregate command for forward product-first verification |
| `docs/workflow/current-state.md` | Record the command in release evidence tooling |
| `docs/workflow/gap-analysis.md` | Record the tooling handoff as a local closure step |

## Acceptance Criteria

- The command runs `seo:check:prod`.
- The command runs rendered SEO smoke through `seo:smoke`, preserving product schema checks.
- The command runs warning-aware browser action smoke and focused `/price/` team preset smoke.
- The command validates the product-first draft sign-off and prints product-first `gaps:known` summary.
- Manual/external gates remain pending until real evidence is attached.

## Verification

### Actual Checks

- [x] `node -e "const p=require('./package.json'); if (!p.scripts['release:product-first:prod-check']) process.exit(1);"`
- [x] `npm run release:signoff:draft-check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json`
- [x] `TACTICUM_RELEASE_SIGNOFF=docs/workflow/release-signoff-2026-06-01-product-first.draft.json npm run gaps:known`
- [x] `git diff --check`
- [ ] `npm run release:product-first:prod-check`: not run locally before deploy/cache refresh; run after product-first deploy.

## Rollback

Remove the npm script and the related documentation references. Existing individual commands (`seo:check:prod`, `seo:smoke`, `browser:console:prod`, `browser:smoke:price`, draft check and `gaps:known`) remain available.
