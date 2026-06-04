# Codex Plan: Product Tech Challenge Sprint Set

Issue:
Gap ID: PTC-SPRINTS-2026-06-04
Workflow lane: Full Feature
Owner agent: Codex
Date: 2026-06-04

## Goal

Сформировать детализированный набор спринтов на базе актуальных документов `product-tech-challenge-gap-register-2026-06-04.md` and `product-tech-challenge-execution-roadmap-2026-06-04.md`, чтобы 100% gap/task IDs имели понятный sprint container, owners, gates, acceptance criteria and verification.

## Non-Goals

- Не менять runtime-код, CSS/JS/PHP или Bitrix content.
- Не закрывать gaps без owner approval/evidence.
- Не менять machine-readable product handoff register `16-gap-closure-action-register.json`.
- Не создавать ADR до принятия архитектурного решения.

## Context Read

- [x] `AGENTS.md`
- [x] `.github/copilot-instructions.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md`
- [x] `docs/workflow/product-tech-challenge-execution-roadmap-2026-06-04.md`
- [x] `docs/workflow/sprint-template.md`

## Current Behavior

The challenge register and execution roadmap define phases and bundles, but there is no detailed sprint-level documentation for planning, owners, gates and verification.

## Target Behavior

Docs contain:

- master sprint roadmap for Sprint 17-23;
- detailed sprint docs with in-scope gaps, dependencies, gates, AC, QA/smoke and risks;
- links from workflow docs and challenge roadmap;
- coverage check that every challenge gap ID belongs to at least one sprint.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/sprints/2026-06-04-product-tech-challenge-sprint-roadmap.md` | New master sprint board for Sprint 17-23 |
| `docs/workflow/sprints/2026-06-04-sprint-17-product-source-and-claims-safety.md` | Product source, schema, cache and public claim safety sprint |
| `docs/workflow/sprints/2026-06-04-sprint-18-taxonomy-seo-packaging.md` | Taxonomy, `/agents/` vs `/aiagents/`, `/price/` framing, packaging and SEO sprint |
| `docs/workflow/sprints/2026-06-04-sprint-19-cjm-cta-crm-qualification.md` | Role CJM, CTA, returning lead, CRM/upstream and product analytics sprint |
| `docs/workflow/sprints/2026-06-04-sprint-20-to-be-design-system.md` | TO BE tokens, components, states, proof UI and design-system sprint |
| `docs/workflow/sprints/2026-06-04-sprint-21-frontend-component-hardening.md` | Product component boundary and frontend module hardening sprint |
| `docs/workflow/sprints/2026-06-04-sprint-22-security-release-legacy-closure.md` | Security controls, CSP, release evidence and legacy alias sprint |
| `docs/workflow/sprints/2026-06-04-sprint-23-accepted-risk-monitoring.md` | Accepted-risk monitoring and program governance sprint |
| `docs/workflow/product-tech-challenge-execution-roadmap-2026-06-04.md` | Link detailed sprint docs |
| `docs/workflow/README.md` / `current-state.md` / `gap-analysis.md` | Reference the sprint set |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS через `Asset`: not applicable, docs only
- [x] Infoblock IDs через config helper: not applicable, docs only
- [x] D7 `Loader::includeModule()` в новом/shared коде: not applicable, docs only
- [x] POST REST bootstrap соблюдён: not applicable, docs only

## Risks

| Risk | Mitigation |
|---|---|
| Sprint docs duplicate source register inconsistently | Treat source register as canonical; sprint docs reference IDs and closure evidence |
| Accepted risks are omitted because they are not implementation tasks | Add Sprint 23 for monitoring/governance |
| Security/PII evidence leaks into docs | Evidence rules explicitly prohibit raw PII, cookies, sessions and request bodies |
| Existing product gap guard breaks | Do not edit `16-gap-closure-action-register.json` |

## Verification

### Automated

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
```

### Manual Smoke

- Every source register ID appears in master sprint roadmap.
- Sprint docs link back to source register and execution roadmap.

## Rollback

Revert docs-only files and remove references from workflow docs. No runtime rollback required.

## Docs To Update

- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/product-tech-challenge-execution-roadmap-2026-06-04.md`
- [ ] ADR: not required yet
- [ ] sitemap/robots: not applicable
