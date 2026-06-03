# 15. Gap Closure Master Plan

Дата: 02.06.2026

Статус: execution plan for known AS IS / TO BE gaps.

## Назначение

Этот документ фиксирует последовательный план закрытия уже известных нереализованных gaps по продукту, UX/UI, архитектуре, SEO/content and release evidence.

Важно: `npm run gaps:known` сейчас показывает `Code-level open/in-progress gaps: 0`. Поэтому оставшийся хвост нельзя закрыть только локальным кодом. Основные gaps требуют продуктовых решений, дизайн-решений, legal/sales evidence, SEO research, upstream/CRM recovery, Метрики and authenticated Bitrix/Admin checks.

## Источники

- `14-gap-backlog-and-decision-register.md` - source of truth по gap IDs.
- `16-gap-closure-action-register.json` - machine-readable action register.
- `09-to-be-design-work-order.md` - TO BE design work order.
- `docs/workflow/gap-analysis.md` - operational/site gap history.
- `docs/workflow/release-signoff-gates.md` - release evidence model.

## Closure Principles

1. Не закрывать `blocked` gaps локальной документацией без внешнего evidence.
2. Не закрывать design gaps без approved Figma/token/component/state deliverables.
3. Не менять forms/chat/staff payloads без Security / Integration lane.
4. Не публиковать proof/status/claims без PM/Legal/Sales source.
5. Не считать production-ready без deploy/cache smoke and release sign-off evidence.
6. Любой gap должен иметь owner, next action and acceptance evidence.

## Execution Tracks

| Track | Цель | Primary gaps |
|---|---|---|
| A. Product Decisions | Утвердить taxonomy, boundaries, packaging, proof and claims | `PB-*`, `CJM-*` |
| B. Design System TO BE | Превратить AS IS handoff в approved Figma/token/component/state system | `UI-*`, `PTC-016` |
| C. Architecture / Integration | Решить content ownership, component boundary, CRM/upstream fields, analytics evidence | `ARCH-*`, `CJM-006` |
| D. SEO / Content | Подтвердить clusters, canonical decisions, metadata, proof/case map | `SEO-TOBE-*`, `PB-008` |
| E. Release Evidence | Закрыть deploy/cache, success-flow, Metrika, Bitrix admin, legacy inventory | `REL-*`, `ARCH-007`, `ARCH-008` |

## Sequenced Plan

### Phase 0 - Register And Guard

Status: implemented locally by this work package.

Outputs:

- `16-gap-closure-action-register.json`;
- `npm run product:gaps:check`;
- workflow docs updated.

Acceptance:

- every non-closed gap in `14-gap-backlog-and-decision-register.md` is covered;
- blocked gaps have explicit blocker and evidence model;
- P0/P1 gaps have target checkpoints;
- `product:gaps:check` is green.

### Phase 1 - P0 / P1 Product Decision Closure

Goal: remove unsafe ambiguity before design implementation.

Priority gaps:

- `PB-001` taxonomy;
- `PB-002` Platform buying triggers;
- `PB-003` Agents/Forum boundaries;
- `PB-004` Dev workflows;
- `PB-005` proof evidence;
- `PB-006` regulatory/procurement wording;
- `PB-007` packaging matrix;
- `PB-008` `/agents/` vs `/aiagents/`;
- `CJM-001` - `CJM-003` final PM/UX/content review.

Required deliverables:

- product taxonomy sheet;
- product one-liners and boundary copy;
- proof matrix: product -> claim -> source -> public wording;
- safe procurement wording matrix;
- public packaging matrix;
- `/agents/` vs `/aiagents/` SEO/canonical decision;
- Dev public workflow examples.

Acceptance:

- PM/Sales/Legal/SEO owners approve decisions;
- decision register statuses updated;
- no new risky public claims added without source.

### Phase 2 - TO BE Design System Approval

Goal: turn checked AS IS handoff into approved TO BE design system.

Priority gaps:

- `UI-001` token source;
- `UI-002` storytelling components;
- `UI-003` form state spec;
- `UI-004` architecture diagrams;
- `UI-005` proof/status UI;
- `UI-006` `/price/` mobile team builder;
- `UI-007` chat state spec;
- `UI-008` icon taxonomy.

Required deliverables:

- Figma variables or approved token source;
- component library with variants and states;
- page templates desktop/mobile;
- state matrix;
- approved AS IS -> TO BE migration map;
- proof/status component rules;
- chat and price mobile specs.

Acceptance:

- `npm run design:handoff:check` remains green after docs updates;
- every high-risk component has Design + Frontend + QA gates;
- contract migrations are explicitly scoped before implementation.

### Phase 3 - Architecture And Integration Decisions

Goal: decide implementation foundation after design/product choices.

Priority gaps:

- `ARCH-001` content model;
- `ARCH-002` component boundary;
- `ARCH-003` structured lead fields;
- `ARCH-004` analytics goals/evidence;
- `CJM-006` structured qualification.

Required deliverables:

- ADR if content moves to Bitrix/hybrid;
- ADR/spec if product page partials become local components;
- CRM/upstream field approval or explicit fallback acceptance;
- Metrika goal map and evidence;
- QA smoke updates for any new interactions.

Acceptance:

- ADR gates resolved where needed;
- lead-form contract updated if payload changes;
- analytics events still exclude PII;
- release sign-off gates updated.

### Phase 4 - SEO And Content Closure

Goal: make product-first public layer searchable and safe.

Priority gaps:

- `SEO-TOBE-001` clusters;
- `SEO-TOBE-002` `/agents/` vs `/aiagents/`;
- `SEO-TOBE-003` product proof/cases;
- `SEO-TOBE-005` final metadata;
- `SEO-TOBE-004` accepted noindex scenario monitoring.

Required deliverables:

- keyword/intent research;
- canonical/redirect decision;
- metadata by product page;
- proof/case tagging model;
- sitemap/canonical smoke after deploy.

Acceptance:

- `npm run seo:check` and rendered SEO smoke pass;
- final title/description/H1 approved;
- noindex/accepted decisions documented.

### Phase 5 - Release Evidence Closure

Goal: close external blockers and strict release sign-off.

Priority gaps:

- `REL-001` deploy/cache smoke;
- `REL-002` rendered product SEO evidence;
- `REL-003` manual success-flow;
- `REL-004` Metrika goals;
- `REL-005` Bitrix admin smoke;
- `REL-006` legacy sale inventory;
- `ARCH-007` external release gates;
- `ARCH-008` staff/upstream success-flow.

Required deliverables:

- `release:product-first:prod-check` evidence after deploy/cache refresh;
- manual success-flow evidence without PII;
- upstream/CRM recovery evidence;
- Metrika goal screenshots/export without PII;
- authenticated Bitrix admin smoke evidence;
- legacy endpoint aggregate access/CRM inventory.

Acceptance:

- strict release sign-off passes;
- `npm run gaps:known:strict` passes only after external gates close;
- no placeholder or PII evidence is committed.

## Immediate Local Implementation

This plan intentionally starts by closing the governance gap:

- action register added;
- guard added;
- docs updated;
- local checks run.

The next local implementation should target one of the non-external, low-risk decision gaps, for example:

1. `UI-008` icon taxonomy baseline;
2. `CJM-004` CTA intent matrix;
3. `PB-004` Dev public workflow examples;
4. `SEO-TOBE-005` draft metadata matrix;
5. `UI-004` architecture diagram design brief.

Status 02.06.2026: first local implementation package added as `17-local-gap-decision-briefs.md`. It gives PM/UX/Designer/SEO concrete review material for `PB-004`, `CJM-004`, `CJM-005`, `UI-004`, `UI-008` and `SEO-TOBE-005`, while keeping those gaps non-closed until owner approval exists.

Status 02.06.2026: Phase 1 product decision review package added as `18-phase-1-product-decision-review-pack.md`. It gives PM/Sales/Legal/Security/Architect/SEO/Content concrete review material for `PB-001`, `PB-002`, `PB-003`, `PB-005`, `PB-006`, `PB-007`, `PB-008`, `CJM-001`, `CJM-002` and `CJM-003`; blocked gaps still require external evidence before closure.

Status 02.06.2026: Phase 3 architecture/integration review package added as `19-phase-3-architecture-integration-decision-pack.md`. It gives Architect/Dev/Content/Backend/PM/QA/Analytics concrete review material for `ARCH-001`, `ARCH-002`, `ARCH-003`, `ARCH-004` and `CJM-006`, with explicit ADR and Security / Integration gates before content model, component architecture, upstream payload or analytics data policy changes.

Status 02.06.2026: Phase 4 SEO/content review package added as `20-phase-4-seo-content-decision-pack.md`. It gives SEO/PM/Content/Sales/Dev/QA concrete review material for `SEO-TOBE-001`, `SEO-TOBE-002`, `SEO-TOBE-003` and `SEO-TOBE-005`, while linking the `/agents/` vs `/aiagents/` decision to `PB-008` and keeping rendered SEO evidence as an external release gate.

Status 02.06.2026: Phase 5 release/evidence closure package added as `21-phase-5-release-evidence-closure-pack.md`. It gives DevOps/QA/SEO/PM/Marketing/Backend/Admin concrete closure rules for `REL-001` - `REL-006`, `ARCH-007` and `ARCH-008`, while keeping all external gates blocked until strict sign-off evidence exists.

Status 02.06.2026: Phase 2 design-system approval package added as `22-phase-2-design-system-approval-pack.md`. It gives Designer/Frontend/QA/PM/Legal concrete approval rules for `UI-001`, `UI-002`, `UI-003`, `UI-005`, `UI-006` and `UI-007`, building on checked AS IS token/component/migration contracts and the `09-to-be-design-work-order.md` handoff.

Status 02.06.2026: accepted-risk monitoring package added as `23-accepted-risk-monitoring-pack.md`. It gives Security/SEO/PM monitoring rules for `ARCH-006` and `SEO-TOBE-004`, keeping them visible as accepted risks rather than silently closing them.

Status 02.06.2026: post-challenge detail package added as `24-post-challenge-gap-analysis.md`, `25-post-challenge-use-cases-and-cjm.md`, `26-post-challenge-ux-ui-design-system.md`, `27-post-challenge-architecture-components-stack.md` and `28-post-challenge-decision-backlog.md`. It sharpens the current AS IS / TO BE challenge into detailed gap analysis, pilot-kit/CJM requirements, UX/UI/design-system requirements, architecture/stack decisions and a prioritized decision board. It intentionally refines existing gap IDs from `14-gap-backlog-and-decision-register.md` instead of creating a parallel backlog.

Status 02.06.2026: post-challenge sprint wave added in `sprints/sprint-09-product-taxonomy-claims-packaging.md` through `sprints/sprint-14-release-evidence-post-launch-governance.md`. These sprints convert decision IDs `D-01` - `D-13` into execution-ready work packages, while `sprints/README.md`, `00-sprint-roadmap.md`, `99-sprint-execution-board.md` and `99-gap-to-sprint-traceability.md` now show the full baseline plus post-challenge sprint model.

Status 02.06.2026: Sprint 09 execution bundle added as `sprints/sprint-09-review-workbook.md` and `sprints/sprint-09-decision-records.md`. It gives PM/Sales/Legal/Security/SEO a concrete review agenda, worksheets and draft decision records for `D-01` - `D-04`, while keeping approvals pending until owners provide evidence.

Status 02.06.2026: Sprint 09 moved to local `ready-for-owner-review` state with `sprints/sprint-09-approval-request.md` and `sprints/sprint-09-evidence-intake.md`. Local preparation is complete for taxonomy, Agents/Forum/`/aiagents/`, proof/claims and packaging review, but `PB-*`, `SEO-TOBE-*` and `UI-005` statuses remain dependent on PM/Sales/Legal/Security/SEO evidence.

Status 02.06.2026: Sprint 10 moved to local `ready-for-owner-review` state with `sprints/sprint-10-review-workbook.md`, `sprints/sprint-10-pilot-kit-records.md`, `sprints/sprint-10-cjm-cta-records.md` and `sprints/sprint-10-approval-request.md`. Local preparation is complete for pilot kits, role-based CJM, CTA taxonomy, returning-lead path and Sales routing review, while `CJM-*`, `PB-*` and `ARCH-003` statuses remain dependent on PM/UX/Sales/Content approval and Sprint 12 structured-field decisions if needed.

Status 02.06.2026: Sprint 11 moved to local `ready-for-owner-review` state with `sprints/sprint-11-review-workbook.md`, `sprints/sprint-11-decision-records.md`, `sprints/sprint-11-state-matrix.md` and `sprints/sprint-11-approval-request.md`. Local preparation is complete for `D-07` - `D-09`: TO BE token source, AS IS -> TO BE component family, state matrix, architecture diagram patterns and proof/status UI review. `UI-*`, `PB-005`, `PB-006`, `ARCH-002` and `SEO-TOBE-003` remain non-closed until Designer/Frontend/QA/Legal/PM/Architect approvals and Sprint 09/10 dependencies are resolved.
