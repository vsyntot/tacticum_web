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
| PG-002 | open | P1 | Full Feature | Lead flow | Нет единой продуктовой карты лид-форм: формы есть, но разные тексты, consent-ссылки и ожидания ответа | `forms.js`, `footer.php`, `index.php`, `calculator/index.php`, `price/index.php`, `offer/template.php`, `aiagents/index.php` | Потеря заявок, разный legal copy, сложнее измерять conversion по `form_id` | Описать Lead Form Contract: обязательные поля, success/error states, form_id taxonomy |
| PG-003 | open | P1 | Security / Integration | Offer flow | AI calculator → offer → prefill flow зависит от `group_id`, но контракт не задокументирован | `tacticum_chat.php`, `tacticum_prefill.php`, `index.php` inline prefill, `offer` iblock | Риск поломки при изменении AI payload или свойств инфоблока | Создать API contract для chat/prefill/offer и smoke сценарий |
| PG-004 | open | P2 | Full Feature | SEO/content | Публичные страницы имеют неполный SEO contract: в основном `SetTitle`, нет системного `description`/OG | `index.php`, `about/index.php`, `services/index.php`, `price/index.php`, `calculator/index.php`, `aiagents/index.php` | Ниже качество сниппетов и шаринга, сложнее SEO-аудит | SEO Agent: audit meta для всех публичных URL, добавить checklist в sprint |
| PG-005 | open | P2 | Fast Fix | Legal/consent | Не все consent-ссылки ведут на `/policies/`; встречаются `href="#"` и "условия использования" без страницы | `index.php`, `calculator/index.php`, `offer/template.php` | Legal UX inconsistency, риск для форм с PII | Заменить consent links на `/policies/`, унифицировать текст |
| PG-006 | open | P2 | Full Feature | Analytics | Нет явного события/схемы аналитики для отправки форм, AI chat, Telegram resolver | `forms.js`, chat inline scripts, Yandex Metrika in `header.php` | Нельзя точно мерить conversion funnel | Добавить analytics event taxonomy и безопасные client-side events |
| PG-007 | open | P2 | Full Feature | Content model | Не все используемые инфоблоки описаны ключами config/ADR | IDs 7, 9, 18, 19, 20 на публичных страницах | Новым агентам непонятно назначение контента, миграции ломкие | Расширить `tacticum_config.php` keys и ADR-003 registry |

## Technology Gaps

| ID | Status | Priority | Lane | Area | Gap | Evidence | Impact | Suggested Next Step |
|---|---|---|---|---|---|---|---|---|
| TG-001 | open | P0 | Security / Integration | HTTPS/config | В нескольких REST endpoints есть HTTP fallback URL внешнего AI-сервиса; локальный config тоже указывает HTTP | `tacticum_chat.php`, `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php`, `resolve_telegram_link.php`, `tacticum_config.php` | Нарушает production HTTPS rule; риск MITM и несовместимость с ADR/Copilot rules | Убрать HTTP fallback, добавить scheme check helper для всех AI URLs, обновить config на HTTPS |
| TG-002 | open | P1 | Security / Integration | Config/iblocks | Хардкод `IBLOCK_ID => 5` и другие ID остаются в `init.php`, страницах и компонентах | `local/php_interface/init.php`, `index.php`, `about/index.php`, `price/index.php`, `offer/index.php`, `aiagents/index.php`, `policies/index.php` | Непереносимость dev/stage/prod, нарушение ADR-003 | Добавить config keys для всех iblocks, refactor high-risk areas first (`init.php`, REST) |
| TG-003 | open | P1 | Security / Integration | REST consistency | `tacticum_form.php` стал эталоном, но старые endpoints дублируют validation/curl/response logic | `tacticum_offer.php`, `tacticum_sale.php`, `tacticum_sale_staff.php` | Расхождение правил, сложнее фиксить security/UX в одном месте | Объединить sale/staff/form logic или сделать shared helper для outbound AI request |
| TG-004 | open | P1 | Security / Integration | CSRF | `tacticum_rest_check_csrf()` пропускает запрос при наличии session cookie без явного token; часть frontend fetch не передаёт `sessid` | `rest_helpers.php`, inline chat scripts, `tg-link-resolver.js` | CSRF модель слабее стандартного Bitrix expectation; поведение зависит от cookie/session | Ввести обязательный `sessid` для state-changing POST; для chat/resolver определить public-token policy |
| TG-005 | open | P1 | Fast Fix | Logging/PII | Некоторые log tags слишком общие, `tacticum_prefill.php` логирует весь объект из инфоблока | `tacticum_chat.php` tags `data/request/response`, `tacticum_prefill.php` `AddMessage2Log(serialize($ob))` | Логи трудно фильтровать, возможна утечка client_name/summary | Унифицировать log tags `tacticum_<endpoint>_<phase>`, маскировать prefill data |
| TG-006 | open | P1 | Full Feature | Frontend maintainability | Большие inline scripts/styles в публичных страницах | `index.php`, `calculator/index.php`, `price/index.php`, `offer/template.php` | Сложно тестировать/переиспользовать, не соответствует asset policy | Выносить в `local/templates/tacticum/js/*.js` и подключать через `Asset` |
| TG-007 | open | P1 | Fast Fix | SEO/sitemap | Sitemap использует HTTP и не содержит `/policies/` | `sitemap.xml`, `sitemap-files.xml`, `robots.txt` | SEO inconsistency, sitemap-validator проходит XML, но не quality | Перегенерировать sitemap на HTTPS и добавить `/policies/` |
| TG-008 | open | P2 | Security / Integration | Bitrix D7 | В новом/shared коде встречается `CModule::IncludeModule()` | `rest_helpers.php`, `init.php`, `tacticum_prefill.php` | Не соответствует D7 best practice, смешивает legacy/new style | Перевести shared/new code на `Bitrix\Main\Loader::includeModule()` |
| TG-009 | open | P2 | Full Feature | API performance | GET API endpoints не используют явное data cache поверх iblock calls | `local/api/*.php` | При росте внешнего использования могут нагружать инфоблоки | Добавить TTL cache или компонентный/API cache strategy |
| TG-010 | open | P2 | Security / Integration | REST method policy | `tacticum_prefill.php` живёт в `local/rest/`, но принимает GET query и делает CSRF без parse JSON | `tacticum_prefill.php` | Семантика REST/GET/POST смешана; сложно проверять CI | Перенести в `local/api` или сделать POST JSON endpoint |
| TG-011 | open | P2 | Full Feature | Asset loading | Неясно, как подключены page-specific CSS-файлы кроме `aiagents.css`; крупные CSS существуют отдельно | `local/templates/tacticum/styles/*.css`, `header.php` | Риск мёртвых/неподключённых стилей или глобального CSS debt | Провести asset audit: что реально используется, что подключать conditionally |
| TG-012 | open | P2 | Security / Integration | CI quality gates | Security checks в PR сейчас warning, не blocker, и не покрывают публичные страницы | `.github/workflows/pr-check.yml` | Нарушения ADR могут попадать в main | Повысить critical checks до blockers для новых/изменённых REST/API, добавить scan публичных `IBLOCK_ID` |
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
