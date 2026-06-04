# Codex Plan: Product Tech Challenge Documentation

Issue:
Gap ID: PTC-2026-06-04
Workflow lane: Full Feature
Owner agent: Codex
Date: 2026-06-04

## Goal

Зафиксировать результаты технологического challenge текущего product-first решения как управляемый набор документов: полный gap/task register, execution roadmap и ссылки из workflow source-of-truth документов.

## Non-Goals

- Не менять runtime-код, публичную вёрстку, REST/API контракты или Bitrix content model.
- Не закрывать gaps без owner evidence.
- Не менять machine-readable product gap register `16-gap-closure-action-register.json` без отдельного scope, чтобы не нарушить существующий `npm run product:gaps:check`.
- Не создавать ADR, пока документ не утверждает новое архитектурное решение.

## Context Read

- [x] `AGENTS.md`
- [x] `.github/copilot-instructions.md`
- [x] `docs/workflow/README.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] Relevant ADR: `ADR-001` - `ADR-010`
- [x] Relevant files: `local/php_interface/include/tacticum_config.php`, product renderer/content helpers, lead form, template assets, price/forms/chat JS

## Current Behavior

Product-first layer is live and production config uses `products.source=bitrix`. Existing docs already contain product handoff and older post-challenge registers, but the 2026-06-04 challenge findings are not yet consolidated as one current execution register covering UX, UI, architecture, components and stack.

## Target Behavior

Docs should make it explicit:

- which challenge findings are gaps;
- which concrete tasks close each gap;
- who owns decisions/evidence;
- which gates apply before implementation;
- which documents are source of truth for planning.

## Planned Changes

| File | Change |
|---|---|
| `docs/workflow/product-tech-challenge-gap-register-2026-06-04.md` | New full gap/task register for 2026-06-04 challenge |
| `docs/workflow/product-tech-challenge-execution-roadmap-2026-06-04.md` | New prioritized execution roadmap and gates |
| `docs/workflow/gap-analysis.md` | Add current challenge index and link to the new register |
| `docs/workflow/current-state.md` | Add documentation refresh note |
| `docs/workflow/README.md` | Add new workflow docs to document index |

## Bitrix Constraints

- [x] `bitrix/` не трогаем
- [x] JS/CSS через `Asset`: not applicable, docs only
- [x] Infoblock IDs через config helper: not applicable, docs only
- [x] D7 `Loader::includeModule()` в новом/shared коде: not applicable, docs only
- [x] POST REST bootstrap соблюдён: not applicable, docs only

## Risks

| Risk | Mitigation |
|---|---|
| Security/PII | Do not store raw production evidence, contact data, cookies, sessions or request bodies |
| Backward compatibility | No code/runtime changes |
| Cache | No cache/runtime changes |
| SEO | Docs should call out canonical/redirect decisions before implementation |
| Deploy | No deploy needed for docs-only change |

## Verification

### Automated

```bash
npm run config:check
npm run bitrix:check
npm run product:gaps:check
```

### Manual Smoke

- Check that new docs are linked from workflow docs.
- Check that each challenge gap has a concrete task, owner and closure evidence.

## Rollback

Revert the docs-only commit or remove the new references from workflow docs. No runtime rollback required.

## Docs To Update

- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/README.md`
- [ ] ADR: not required until an implementation decision is accepted
- [ ] sitemap/robots: not applicable
