# Gap Analysis — tacticum.ru

Дата аудита: 20.05.2026

Статусы:

- `open` — gap подтверждён и требует задачи;
- `in-progress` — уже есть активная работа;
- `accepted` — осознанно принимаем риск;
- `closed` — закрыто, оставить ссылку на PR/Issue.

Приоритеты:

- `P0` — production/security blocker;
- `P1` — важно для ближайшего спринта;
- `P2` — плановый backlog;
- `P3` — nice-to-have.

## Executive Summary

Главные gaps на ближайший стабилизационный спринт:

1. HTTPS/config discipline для AI endpoints.
2. Убрать хардкод инфоблоков из `init.php`, REST и публичных страниц.
3. Унифицировать AI chat frontend вместо нескольких inline реализаций.
4. Привести sitemap/meta к production SEO состоянию.
5. Усилить CI: warnings для security conventions должны стать blockers хотя бы для новых REST/API.

## Product Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| PG-001 | open | P1 | Full Feature | AI chat / calculator | AI-chat пользовательский сценарий не единый: главный экран, calculator, price и `chat.js` живут разными реализациями | `index.php` inline chat, `calculator/index.php` inline chat, `price/index.php` inline chat, `local/templates/tacticum/js/chat.js` static demo | Поведение и ошибки чата трудно тестировать, UX может расходиться между страницами | Создать единый `js/ai-chat.js` + data-атрибуты, оформить API contract для `/local/rest/tacticum_chat.php` |
| PG-002 | closed | P1 | Full Feature | Lead flow | Контракт лид-форм зафиксирован для QA и будущих правок | `docs/workflow/lead-form-contract.md`, `forms.js`, `/local/rest/tacticum_form.php` | Снижен риск расхождения форм, есть form_id taxonomy и smoke cases | Следующий шаг: вынести аналитику форм в PG-006 |
| PG-003 | open | P1 | Security / Integration | Offer flow | AI calculator → offer → prefill flow зависит от `group_id`, но контракт не задокументирован | `tacticum_chat.php`, `tacticum_prefill.php`, `index.php` inline prefill, `offer` iblock | Риск поломки при изменении AI payload или свойств инфоблока | Создать API contract для chat/prefill/offer и smoke сценарий |
| PG-004 | open | P2 | Full Feature | SEO/content | Публичные страницы имеют неполный SEO contract: в основном `SetTitle`, нет системного `description`/OG | `index.php`, `about/index.php`, `services/index.php`, `price/index.php`, `calculator/index.php`, `aiagents/index.php` | Ниже качество сниппетов и шаринга, сложнее SEO-аудит | SEO Agent: audit meta для всех публичных URL, добавить checklist в sprint |
| PG-005 | open | P2 | Fast Fix | Legal/consent | Не все consent-ссылки ведут на `/policies/`; встречаются `href="#"` и "условия использования" без страницы | `index.php`, `calculator/index.php`, `offer/template.php` | Legal UX inconsistency, риск для форм с PII | Заменить consent links на `/policies/`, унифицировать текст |
| PG-006 | open | P2 | Full Feature | Analytics | Нет явного события/схемы аналитики для отправки форм, AI chat, Telegram resolver | `forms.js`, chat inline scripts, Yandex Metrika in `header.php` | Нельзя точно мерить conversion funnel | Добавить analytics event taxonomy и безопасные client-side events |
| PG-007 | in-progress | P2 | Full Feature | Content model | Ключи инфоблоков расширены в ADR/example config, но публичные страницы ещё используют legacy ID | `local/php_interface/include/tacticum_config.example.php`, `docs/adr/ADR-003-iblock-ids.md`, публичные страницы | Новым агентам понятнее модель контента, но переносимость страниц ещё не закрыта полностью | Sprint 02: refactor public `IncludeComponent` на config helper |

## Technology Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| TG-001 | in-progress | P0 | Security / Integration | HTTPS/config | Runtime REST больше не имеет HTTP fallback и требует HTTPS, но серверный `tacticum_config.php` нужно обновить на реальный HTTPS URL | `rest_helpers.php`, REST endpoints, `local/php_interface/include/tacticum_config.example.php` | Runtime защищён от silent HTTP fallback; без HTTPS config endpoints вернут `500 config_error` | DevOps: прописать реальные HTTPS URLs в серверном `tacticum_config.php` |
| TG-002 | in-progress | P1 | Security / Integration | Config/iblocks | `init.php` переведён на `offer` config key, registry расширен; публичные страницы всё ещё содержат legacy `IBLOCK_ID` | `local/php_interface/init.php`, `docs/adr/ADR-003-iblock-ids.md`, public pages | Backend REST callbacks переносимее; публичные страницы остаются technical debt | Sprint 02: refactor public pages gradually |
| TG-003 | in-progress | P1 | Security / Integration | REST consistency | `tacticum_form.php` стал default endpoint, `tacticum_sale_staff.php` выделен как доменный staff adapter, но старые sale endpoints ещё дублируют validation/curl/response logic | `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php` | Расхождение правил, сложнее фиксить security/UX в одном месте | Вынести outbound AI request и response normalization в shared helper |
| TG-004 | closed | P1 | Security / Integration | CSRF | `tacticum_rest_check_csrf()` требует явный token; chat/prefill/resolver frontend передаёт `BX.bitrix_sessid()` | `rest_helpers.php`, `index.php`, `calculator/index.php`, `price/index.php`, `tg-link-resolver.js` | CSRF модель приведена к явному Bitrix token для state-changing POST | Поддерживать правило в Lead Form Contract и PR checks |
| TG-005 | closed | P1 | Fast Fix | Logging/PII | Chat log tags унифицированы, prefill больше не логирует весь объект инфоблока | `tacticum_chat.php`, `tacticum_prefill.php` | Меньше риск PII в логах, проще фильтрация | Sprint 02: привести остальные tags к единой taxonomy при объединении endpoints |
| TG-006 | open | P1 | Full Feature | Frontend maintainability | Большие inline scripts/styles в публичных страницах | `index.php`, `calculator/index.php`, `price/index.php`, `offer/template.php` | Сложно тестировать/переиспользовать, не соответствует asset policy | Выносить в `local/templates/tacticum/js/*.js` и подключать через `Asset` |
| TG-007 | closed | P1 | Fast Fix | SEO/sitemap | Sitemap переведён на HTTPS и включает `/policies/` | `sitemap.xml`, `sitemap-files.xml` | SEO inconsistency устранена для sitemap | Дальше: meta/OG audit в PG-004 |
| TG-008 | in-progress | P2 | Security / Integration | Bitrix D7 | Shared/runtime touched code переведён на `Loader::includeModule()`, но нужен общий scan legacy-кода | `rest_helpers.php`, `init.php`, `tacticum_prefill.php` | Новый runtime-код ближе к D7 best practice | Sprint 02: полный scan `CModule::IncludeModule()` |
| TG-009 | open | P2 | Full Feature | API performance | GET API endpoints не используют явное data cache поверх iblock calls | `local/api/*.php` | При росте внешнего использования могут нагружать инфоблоки | Добавить TTL cache или компонентный/API cache strategy |
| TG-010 | open | P2 | Security / Integration | REST method policy | `tacticum_prefill.php` живёт в `local/rest/`, но принимает GET query и делает CSRF без parse JSON | `tacticum_prefill.php` | Семантика REST/GET/POST смешана; сложно проверять CI | Перенести в `local/api` или сделать POST JSON endpoint |
| TG-011 | open | P2 | Full Feature | Asset loading | Неясно, как подключены page-specific CSS-файлы кроме `aiagents.css`; крупные CSS существуют отдельно | `local/templates/tacticum/styles/*.css`, `header.php` | Риск мёртвых/неподключённых стилей или глобального CSS debt | Провести asset audit: что реально используется, что подключать conditionally |
| TG-012 | closed | P2 | Security / Integration | CI quality gates | Critical runtime checks стали blockers, public hardcoded iblocks остаются warning-level | `.github/workflows/pr-check.yml` | Нарушения REST/API conventions сложнее протащить в main | Поддерживать список checks при новых ADR |
| TG-013 | open | P2 | Full Feature | Config validation | `tacticum_config.php` не валидируется схемой | `rest_helpers.php`, ADR-002 | Ошибки config проявляются runtime 500 | Добавить `tacticum_rest_validate_config()` и CI/doc example |
| TG-014 | open | P2 | Fast Fix | Repository hygiene | В рабочем дереве есть `.DS_Store`, `log.txt`, локальный config file | root/local dirs | Риск случайного commit мусора/секретов | Убедиться, что файлы untracked/ignored или удалить из индекса, добавить hygiene check |

## Recommended First Sprint

См. `docs/workflow/sprints/2026-05-20-sprint-01-stabilization.md`.

Цель первого спринта — не новая фича, а стабилизация основы:

- закрыть P0/P1 security/config gaps;
- привести sitemap/legal минимально в порядок;
- начать унификацию AI chat/form contracts;
- усилить CI gates.

## Gap Lifecycle

1. PM выбирает gap и создаёт Issue.
2. Issue получает `workflow_lane`, priority, owner.
3. Если gap проходит ADR/Design/QA gates — подключить нужного агента до разработки.
4. После PR/deploy обновить status gap:
   - `closed`, если полностью устранён;
   - `in-progress`, если часть работ осталась;
   - добавить ссылки на Issue/PR/ADR.
