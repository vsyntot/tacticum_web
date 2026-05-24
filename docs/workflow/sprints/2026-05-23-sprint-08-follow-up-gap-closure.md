# Sprint 08 — Follow-Up Gap Closure

Дата: 23.05.2026

## Цель

Закрыть 100% follow-up gaps, оставшихся после Sprint 07: CSS retirement, реальные success-flow gates, Metrika/CSP readiness, legacy sale lifecycle, config sync discipline, REST success-body decision и future rich workers upstream readiness.

## Workflow Lane

Основной lane: `Full Feature` + `Security / Integration`.

Причина: sprint затрагивает CSS asset pipeline, CI checks, CSP header, AI endpoint configuration, REST contract docs и release handoff process.

## Состав Команды

| Роль | Ответственность |
|---|---|
| PM | Sprint scope, priorities, sign-off gates, sprint review |
| Analyst | Разделить product follow-up и code gaps, зафиксировать AC |
| Architect | ADR для config-driven AI sale endpoint paths, CSP/report-only review |
| Frontend Dev | CSS retirement batch, smoke tooling, CSP header в template |
| Backend Dev | Configurable sale/staff endpoint paths, config validation |
| QA/Reviewer | Manual success-flow matrix, PR template, smoke requirements |
| DevOps | CI guards, sunset/config checks, deploy handoff |
| SEO/Marketing | Metrika goal confirmation как release sign-off |

## Backlog И Реализация

| ID | Gap | Owner | Priority | Status | Реализация |
|---|---|---|---|---|---|
| FUG-001 | Legacy `template_styles.css` retirement | Frontend + QA | P1 | done | Удалён old generated Tailwind block; добавлен `TACTICUM_VISUAL_REMOVE_CSS`, `visual:smoke:css-local`, `browser:smoke:css-local`; PR check блокирует возврат Tailwind layer block |
| FUG-002 | Real success-flow forms/chat/prefill/staff-order | QA + PM | P1 | done as gate | Добавлен `release-signoff-gates.md`; `post-deploy-smoke.md` и PR template требуют owner/evidence для staging/manual success-flow |
| FUG-003 | Metrika goals confirmation | PM/Marketing + QA | P1 | done as gate | Release sign-off требует evidence по целям Метрики при изменении analytics/form/chat; PR template обновлён |
| FUG-004 | CSP strategy | Architect + Frontend | P1 | done | `header.php` отправляет `Content-Security-Policy-Report-Only`; ADR-005 обновлён под report-only rollout |
| FUG-005 | Legacy sale aliases lifecycle after `30.09.2026` | Architect + Backend + DevOps | P1 | done | Добавлен `tools/legacy-sale-sunset-check.mjs`, npm script `sale:sunset:check`, PR check; после sunset CI потребует решение |
| FUG-006 | Production/staging config sync discipline | DevOps + Backend | P1 | done | Добавлен `ai.endpoint_paths` в example config, `tools/config-contract-check.mjs`, npm script `config:check`, PR checklist owner для config sync |
| FUG-007 | REST success-body contract decision | Architect + QA | P2 | done | Добавлен `rest-response-contract-decision.md`: существующие response shapes сохраняются; новые endpoints используют shared response helpers |
| FUG-008 | Rich workers upstream readiness | Backend + Architect | P2 | done | Добавлен config-driven `ai.endpoint_paths.staff_sale`; `tacticum_sale_staff.php` может переключиться на будущий endpoint без frontend changes; ADR-006 |

## Acceptance Criteria

- Все 8 follow-up gaps имеют реализацию или formal sign-off gate.
- `template_styles.css` больше не содержит generated Tailwind layer block.
- CSS replacement smoke можно запускать против production HTML без старых production CSS links.
- CSP введён только в report-only режиме и не должен блокировать пользователей.
- Legacy aliases не могут пережить `30.09.2026` без явного CI-сигнала.
- Example config содержит endpoint path registry и проверяется автоматикой.
- REST success-body unification имеет explicit decision, а не скрытый backlog.
- `/price/` staff-order готов к будущему rich upstream через config.

## QA / Smoke

Локально:

- `npm run css:check`
- `npm run config:check`
- `npm run sale:sunset:check`
- `node --check tools/visual-smoke.mjs`
- `node --check tools/config-contract-check.mjs`
- `node --check tools/legacy-sale-sunset-check.mjs`
- PHP syntax по изменённым runtime files

Перед/после deploy:

- `npm run visual:smoke:css-local` при CSS retirement PR
- `npm run browser:smoke:css-local` если CSS меняет интерактивные состояния
- GitHub deploy gate: `health_config`, `visual:smoke`, `browser:smoke`
- Manual/staging success-flow по `release-signoff-gates.md`

## Sprint Review

### Done

- Sprint scope сформирован по всем обозначенным gaps.
- Code-level gaps закрыты в repo: CSS retirement batch, smoke tooling, CSP report-only, AI endpoint path config, sunset/config checks.
- Process-level gaps закрыты formal sign-off gates: real success-flow, Metrika goals, config sync, Bitrix admin smoke.
- ADR/docs обновлены.

### Verified Locally

- `npm run css:check`
- `npm run config:check`
- `npm run sale:sunset:check`
- `node --check tools/visual-smoke.mjs`
- `node --check tools/config-contract-check.mjs`
- `node --check tools/legacy-sale-sunset-check.mjs`
- `npm run visual:smoke:css-local` — manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-23T18-49-00-527Z/manifest.json`
- `npm run browser:smoke:css-local` — manifest `/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-23T18-52-36-823Z/manifest.json`
- `git diff --check`
- `git ls-files -c -i --exclude-standard`
- guard scan: `template_styles.css` has no `tailwindcss v*` / generated Tailwind layer block

### Not Done

- Enforcing CSP не включался намеренно: сначала нужен report-only baseline без browser errors.
- Полное удаление `template_styles.css` не выполнялось одномоментно: следующий безопасный шаг — owner mapping и component/page extraction.
- Реальные production/staging success-flow и цели Метрики требуют внешнего окружения/кабинета и закрываются PM через release sign-off.
- PHP CLI локально недоступен (`php: command not found`); PHP 8.4 syntax check остаётся за GitHub Actions `pr-check.yml`/`deploy.yml`.
