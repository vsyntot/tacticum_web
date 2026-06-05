# Codex Plan: Bitrix Componentization Challenge Documentation

Issue:
Gap ID: BPC-2026-06-05
Workflow lane: Full Feature
Owner agent: Codex
Date: 2026-06-05

## Goal

Зафиксировать результаты технологического challenge с точки зрения лучших практик разработки на 1C-Bitrix: размер файлов, изоляция логики в компоненты, D7/service layer, REST/API boundaries, frontend module boundaries and architecture guards.

## Non-Goals

- Не менять runtime-код.
- Не рефакторить компоненты, JS, REST helpers или public entries в рамках docs-only задачи.
- Не закрывать `BPC-*` gaps без реализации и verification evidence.
- Не менять sitemap/robots/public URLs.
- Не создавать ADR до утверждения конкретного архитектурного решения.

## Context Read

- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/adr/ADR-008-public-page-local-components.md`
- [x] `docs/adr/ADR-009-bitrix-framework-hardening.md`
- [x] `docs/adr/ADR-010-product-content-bitrix-model.md`
- [x] `sitemap-basic-files.xml`
- [x] Public page and component line-count snapshot
- [x] `tools/bitrix-architecture-check.mjs`

## Current Behavior

Current codebase already has meaningful Bitrix hardening:

- `init.php` is thin.
- Local components exist and have required metadata.
- Many public pages use `tacticum:*` wrappers.
- `Asset` is used for template JS/CSS.
- Product content is Bitrix-backed and guarded by product content checks.

But componentization is incomplete:

- product renderer is include/function-based, not a local component;
- several public entries are still thick HTML pages;
- `/price/`, `forms.js`, `chat-agent.js`, `rest_helpers.php` are large multi-responsibility files;
- no `local/lib` service layer exists;
- current `bitrix:check` does not catch file-size or component-boundary drift.

## Target Behavior

Docs should make it explicit:

- which Bitrix/componentization gaps exist;
- which work packages close them;
- which order is safest;
- which verification commands are required;
- which gaps are still open after documentation.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/bitrix-componentization-gap-analysis-2026-06-05.md` | New source register for `BPC-*` gaps |
| `docs/workflow/bitrix-componentization-execution-roadmap-2026-06-05.md` | New phase roadmap and do-not-start rules |
| `docs/workflow/bitrix-componentization-issue-backlog-2026-06-05.md` | New issue-ready backlog `BPC-WP-01` - `BPC-WP-09` |
| `docs/workflow/gap-analysis.md` | Add current Bitrix componentization challenge layer |
| `docs/workflow/current-state.md` | Add current Bitrix architecture snapshot |
| `docs/workflow/README.md` | Add new documents to workflow index |

## Bitrix Constraints

- [x] `bitrix/` not touched.
- [x] No new hardcoded iblock IDs.
- [x] No REST/API contract changes.
- [x] No JS/CSS asset changes.
- [x] No public URL/sitemap behavior changes.

## Risks

| Risk | Mitigation |
|---|---|
| False closure | New docs mark gaps `open/in-progress`, not `closed` |
| Docs drift | Link docs from `gap-analysis.md`, `current-state.md`, `README.md` |
| Overlapping with 2026-06-04 challenge | Use distinct `BPC-*` namespace and separate docs |
| Scope creep into refactor | Docs-only non-goal; implementation goes through issue backlog |

## Verification

```bash
npm run bitrix:check
npm run product:challenge:check
npm run seo:check
```

Manual:

- New docs are discoverable from workflow index.
- Every `BPC-*` gap has required task, affected area and closure evidence.
- Every work package references source gap IDs.

## Rollback

Revert docs-only changes. No runtime rollback required.
