# Sprint 09 Workstream - Sale Sunset And Rich Workers Upstream

Дата: 23.05.2026

## Workstream Goal

Поддерживающий artifact для Sprint 09 Overall Gap Closure: закрыть документационно-процессный gap по legacy sale aliases и будущему rich workers upstream. К `30.09.2026` есть явная матрица решений по `tacticum_offer.php` / `tacticum_sale.php`, а `/price/` staff-order имеет согласованный маршрут переключения через `ai.endpoint_paths.staff_sale`.

## Capacity / Constraints

- Production runtime не меняем в этом спринте.
- CSS, CSP, SEO, публичную вёрстку и JS не трогаем.
- Рабочая зона: `docs/adr/*`, `docs/workflow/*` по sale/upstream; `tools/legacy-sale-sunset-check.mjs` только если потребуется синхронизировать future decision gate.

## In Scope

| Item | Gap/Issue | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| 1 | Legacy sale aliases sunset | Security / Integration | Architect + Backend + DevOps | P1 | done as process decision | `tools/legacy-sale-sunset-check.mjs`, release sign-off `legacy-sunset` |
| 2 | Future rich workers upstream | Security / Integration | Architect + Backend + DevOps + QA | P1 | done as process decision | ADR-006, `ai.endpoint_paths.staff_sale`, staging upstream contract |

## Out Of Scope

- Удаление или изменение `local/rest/tacticum_offer.php` и `local/rest/tacticum_sale.php`.
- Переключение production `ai.endpoint_paths.staff_sale` на новый upstream до появления согласованного контракта.
- Изменение response shapes существующих endpoints.
- Package, PR-check и deploy workflow правки.

## Legacy Sale Alias Decision Matrix

Сейчас `tacticum_offer.php` и `tacticum_sale.php` остаются legacy aliases с сохранённым JSON contract, `Deprecation` / `Sunset` headers и `Link: rel="successor-version"` на `/local/rest/tacticum_form.php`.

| Дата / условие | Решение по умолчанию | Кто подтверждает | Обязательное действие |
|---|---|---|---|
| До `30.06.2026` | Оставить aliases, собрать потребителей | PM + Backend | Проверить access logs / CRM источники, зафиксировать известных внешних consumers |
| `01.07.2026` - `31.08.2026` | Мигрировать consumers на `/local/rest/tacticum_form.php` | PM + Backend | Уведомить владельцев интеграций, проверить что новые интеграции не используют aliases |
| `01.09.2026` - `29.09.2026` | Выбрать финальный режим | Architect + Backend + DevOps | Решить: удалить aliases, вернуть `410 Gone`, применить проверенный redirect для POST или продлить поддержку |
| `30.09.2026` | Не оставлять aliases без явного решения | Architect + Backend + DevOps | Перед deploy закрыть `legacy-sunset` sign-off; если files остаются, обновить implementation и sunset check под выбранный режим |
| После `30.09.2026`, если нужен extension | Продление только как accepted risk | PM + Architect | Обновить `Sunset` header, `tools/legacy-sale-sunset-check.mjs`, release notes и эту матрицу с новой датой |

Финальные варианты:

| Вариант | Когда выбирать | Что меняется | Проверка |
|---|---|---|---|
| Удалить aliases | Нет трафика и внешних consumers | Удалить `tacticum_offer.php` и `tacticum_sale.php` | `npm run sale:sunset:check`, scan ссылок на aliases, manual smoke default form |
| `410 Gone` | Consumers есть, но нужно явное машинное завершение lifecycle | Оставить endpoint files без upstream call, вернуть controlled JSON error и successor link | Contract doc update, security review, smoke headers/body |
| Redirect | Только если POST client behavior проверен | Использовать explicit redirect status, не менять successor contract | QA на реальных clients; без approval вариант не использовать |
| Продлить поддержку | Есть бизнес-зависимость и owner риска | Новая дата sunset, headers и checker синхронизированы | PM/Architect accepted risk в release issue |

## Rich Workers Upstream Matrix

`/price/` продолжает отправлять rich staff payload через `/local/rest/tacticum_sale_staff.php`. До появления upstream contract `ai.endpoint_paths.staff_sale` должен указывать на текущий adapter path `/tacticum/v1/chat_agent/sale`.

| Условие | Решение | Кто подтверждает | Обязательное действие |
|---|---|---|---|
| Отдельного upstream workers contract нет | Оставить `staff_sale=/tacticum/v1/chat_agent/sale` | Architect + Backend | Не менять frontend payload; rich details остаются в `task` adapter |
| Upstream прислал OpenAPI / contract для rich workers | Готовить config switch | Architect + Backend + QA | Проверить method, request model `workers[]`, contact fields, dates, workload, `team_preset`, `monthly_budget_estimate`, response success/error model |
| Contract совместим без frontend changes | Переключить только `ai.endpoint_paths.staff_sale` | DevOps + Backend | Обновить staging/production `tacticum_config.php`; `chat_agent_sale` не менять |
| Contract требует другой payload или response model | Открыть новую Security / Integration задачу | Architect + Backend + QA | Обновить ADR-006, `lead-form-contract.md`, adapter и smoke до deploy |
| Staging rich workers smoke не проходит | Rollback config path | DevOps + Backend | Вернуть `staff_sale=/tacticum/v1/chat_agent/sale`, сохранить текущий adapter behavior |

## Gates

| Gate | Required? | Notes |
|---|---|---|
| ADR | yes | ADR-006 фиксирует config-driven endpoint paths и switch matrix |
| Design | no | UX и frontend payload не меняются |
| QA early | yes | Нужно для alias sunset и upstream response/payload checks |
| SEO | no | Публичные URL не меняются |
| Post-deploy smoke | yes | Только если будущий PR меняет endpoints/config/runtime |

## QA / Smoke Scope

| Scenario | URL/API | Expected |
|---|---|---|
| Default lead form successor | `/local/rest/tacticum_form.php` | `success=true` на staging или controlled production lead |
| Legacy alias before sunset | `/local/rest/tacticum_offer.php`, `/local/rest/tacticum_sale.php` | Response shape сохранён, `Deprecation`, `Sunset`, `Link` headers присутствуют |
| Legacy alias after final decision | Same | Поведение соответствует выбранному варианту из matrix |
| Staff-order current adapter | `/local/rest/tacticum_sale_staff.php` | `workers_json`, `team_preset`, `monthly_budget_estimate`, `endDate` доходят до upstream через текущий adapter |
| Staff-order future rich endpoint | `/local/rest/tacticum_sale_staff.php` | После смены `ai.endpoint_paths.staff_sale` staging upstream принимает rich workers payload |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| Внешний consumer продолжает использовать aliases после `30.09.2026` | PM + Backend | Logs/CRM inventory до `30.06.2026`, миграция до `31.08.2026`, explicit final mode до `30.09.2026` |
| `410` или redirect ломает POST clients | Backend + QA | Не выбирать без client smoke; preferred финальный вариант при нулевом трафике - удалить aliases |
| Новый rich workers endpoint несовместим с текущим frontend payload | Architect + Backend | Не ограничиваться config switch; открыть новую задачу и обновить ADR/contract |
| Production config drift | DevOps | `config-sync` sign-off и health smoke перед закрытием release |

## Sprint Review

### Done

- Legacy sale sunset получил action/decision matrix с датами до `30.09.2026`.
- Rich workers upstream получил switch matrix через `ai.endpoint_paths.staff_sale`.
- ADR/contract/workflow docs теперь ссылаются на единый Sprint 09 decision artifact.

### Not Done

- Runtime endpoints не менялись.
- CI/package не менялись.
- Production config не переключался.

### Follow-Up

- До `30.06.2026` PM + Backend должны заполнить inventory consumers legacy aliases.
- До `31.08.2026` владельцы интеграций должны мигрировать на `/local/rest/tacticum_form.php`.
- До `30.09.2026` Architect + Backend + DevOps должны выбрать и реализовать финальный режим aliases.
