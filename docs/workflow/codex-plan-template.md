# Codex Plan Template

Использовать для задач, которые не являются тривиальным Fast Fix. План можно вставить в Issue/PR comment или сохранить отдельным файлом в `docs/workflow/plans/ISSUE-N-slug.md`.

```markdown
# Codex Plan: [Название задачи]

Issue:
Gap ID:
Workflow lane:
Owner agent:
Date:

## Goal

Что должно измениться для пользователя/бизнеса/техсистемы.

## Non-Goals

Что явно не делаем в этой задаче.

## Context Read

- [ ] `AGENTS.md`
- [ ] `.github/copilot-instructions.md`
- [ ] `docs/workflow/current-state.md`
- [ ] `docs/workflow/gap-analysis.md`
- [ ] Relevant ADR:
- [ ] Relevant files:

## Current Behavior

Фактическое поведение сейчас, со ссылками на файлы/URL.

## Target Behavior

Ожидаемое поведение после изменения.

## Planned Changes

| File | Change |
|---|---|
| `...` | `...` |

## Bitrix Constraints

- [ ] `bitrix/` не трогаем
- [ ] JS/CSS через `Asset`
- [ ] Infoblock IDs через config helper
- [ ] D7 `Loader::includeModule()` в новом/shared коде
- [ ] POST REST bootstrap соблюдён

## Risks

| Risk | Mitigation |
|---|---|
| Security/PII | |
| Backward compatibility | |
| Cache | |
| SEO | |
| Deploy | |

## Verification

### Automated

```bash
php -l path/to/file.php
```

### Manual Smoke

- URL/API:
- Action:
- Expected:

## Rollback

Как безопасно откатить изменение.

## Docs To Update

- [ ] `docs/workflow/gap-analysis.md`
- [ ] `docs/adr/*`
- [ ] `.github/copilot-instructions.md`
- [ ] sitemap/robots
```
