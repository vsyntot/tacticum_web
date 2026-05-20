# Sprint 01: 20.05.2026 - 03.06.2026

## Sprint Goal

Стабилизировать основу Bitrix-приложения перед дальнейшими продуктовыми доработками: config/HTTPS, iblock portability, REST consistency, sitemap/legal hygiene, минимальные CI gates.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| 1 | TG-001 | Security / Integration | Architect + QA + Backend | P0 | planned | Проверить production HTTPS URL AI-сервиса |
| 2 | TG-002 | Security / Integration | Architect + Backend | P1 | planned | Расширить config keys для IDs 7/9/18/19/20 |
| 3 | TG-004 | Security / Integration | QA + Backend + Frontend | P1 | planned | Решить public CSRF policy для chat/resolver |
| 4 | TG-005 | Fast Fix | Backend + QA | P1 | planned | Unified log tags и masking prefill |
| 5 | TG-007 + PG-004 | Fast Fix | SEO | P1 | planned | HTTPS sitemap, `/policies/`, meta minimum |
| 6 | TG-012 | Security / Integration | DevOps + QA | P2 | planned | Перевести критичные warning в blockers для новых REST/API |
| 7 | PG-002 | Full Feature | Analyst + Frontend + Backend | P1 | planned | Lead Form Contract |

## Out Of Scope

- Полный редизайн страниц.
- Переписывание всех компонентов на D7 ORM.
- Новый AI-сценарий или новая продуктовая фича.
- Полное покрытие автоматическими e2e-тестами.

## Gates

| Gate | Required | Notes |
|---|---|---|
| ADR | yes | Для CSRF policy и/или unified AI outbound helper, если меняется общий паттерн |
| Design | no | Только review, если меняется вид форм |
| QA early | yes | TG-001, TG-004, TG-012 |
| SEO | yes | TG-007, PG-004 |
| Post-deploy smoke | yes | Все endpoints/forms после config/security изменений |

## QA / Smoke Scope

| Scenario | URL/API | Expected |
|---|---|---|
| Main lead form | `/` | success/error state, no unmasked PII |
| Modal lead form | any page header CTA | success/error state |
| AI chat | `/`, `/calculator/`, `/price/` | message sent, upstream handled |
| Offer prefill | `/` → generated `group_id` | prefilled form or documented error |
| Telegram resolver | `/aiagents/` | links resolved or unchanged without JS failure |
| Sitemap | `/sitemap.xml`, `/sitemap-files.xml`, `/robots.txt` | HTTPS URLs, valid XML |

## Risks

| Risk | Owner | Mitigation |
|---|---|---|
| AI service is only available over HTTP today | PM/DevOps | Confirm infrastructure before enforcing production HTTPS |
| Changing CSRF policy can break chat/resolver | QA/Backend/Frontend | Stage rollout, smoke all POST endpoints |
| Iblock key refactor can break content blocks | Backend/QA | Refactor in small chunks, verify each public page |

## Expected Deliverables

- Config/HTTPS cleanup plan and implementation.
- `tacticum_config.php` example/schema updated without secrets.
- ADR or workflow note for CSRF policy.
- Sitemap fixed.
- Lead Form Contract documented.
- PR checks stricter for new REST/API.

## Sprint Review

### Done

- TBD

### Not Done

- TBD

### Follow-Up

- PG-001 unified AI chat implementation likely becomes Sprint 02 item.
