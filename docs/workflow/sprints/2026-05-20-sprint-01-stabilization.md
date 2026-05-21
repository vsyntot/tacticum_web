# Sprint 01: 20.05.2026 - 03.06.2026

## Sprint Goal

Стабилизировать основу Bitrix-приложения перед дальнейшими продуктовыми доработками: config/HTTPS, iblock portability, REST consistency, sitemap/legal hygiene, минимальные CI gates.

## In Scope

| Item | Gap | Lane | Owner | Priority | Status | Dependencies |
|---|---|---|---|---|---|---|
| 1 | TG-001 | Security / Integration | Architect + QA + Backend | P0 | done | Production config health подтверждён 21.05.2026 |
| 2 | TG-002 | Security / Integration | Architect + Backend | P1 | done | Backend `init.php` и публичные страницы используют config registry |
| 3 | TG-004 | Security / Integration | QA + Backend + Frontend | P1 | done | Явный `sessid` обязателен и передаётся chat/prefill/resolver |
| 4 | TG-005 | Fast Fix | Backend + QA | P1 | done | Chat tags и prefill masking исправлены |
| 5 | TG-007 + PG-004 | Fast Fix | SEO | P1 | done | Sitemap исправлен; базовый meta/OG/canonical baseline добавлен |
| 6 | TG-012 | Security / Integration | DevOps + QA | P2 | done | Runtime critical checks стали blockers |
| 7 | PG-002 | Full Feature | Analyst + Frontend + Backend | P1 | done | Lead Form Contract задокументирован |

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

- `rest_helpers.php`: добавлен обязательный HTTPS helper для AI URLs, CSRF требует явный token, `iblock` подключается через `Loader`.
- REST endpoints AI/chat/sale/resolver/form переведены с HTTP fallback на shared HTTPS helper.
- Production config health 21.05.2026: `GET https://tacticum.ru/local/rest/health_config.php` вернул `success: true`.
- Frontend chat/prefill/Telegram resolver передаёт `BX.bitrix_sessid()`.
- `tacticum_prefill.php` больше не логирует весь объект инфоблока.
- `local/php_interface/init.php` использует config key `offer` вместо hardcoded `IBLOCK_ID => 5`.
- Sitemap переведён на HTTPS и включает `/policies/`.
- Lead Form Contract добавлен в workflow docs.
- PR checks усилены: runtime security/convention violations стали blockers.
- ADR/config example обновлены под полный registry используемых iblocks.

### Closed By Follow-Up

- Серверный `local/php_interface/include/tacticum_config.php` подтверждён production health-check без вывода секретов.
- Публичные страницы переведены на `tacticum_iblock_id(...)`.
- Базовый SEO meta/OG/canonical baseline добавлен.
- Дублированные chat implementations закрыты в Sprint 02.

### Follow-Up

- PG-001: unified AI chat implementation как задача Sprint 02.
- TG-002: refactor public page iblocks как технический долг Sprint 02.
- PG-004: meta/OG audit запланировать с SEO owner.
