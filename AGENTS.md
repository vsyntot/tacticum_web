# AGENTS.md — tacticum.ru

Этот файл — входная точка для Codex, Copilot Agent Mode, Claude-агентов и людей, которые дорабатывают проект.
Подробные роли лежат в `.github/agents/`, архитектурные решения — в `docs/adr/`, операционный workflow — в `docs/workflow/`.

## Проект

Корпоративный сайт `tacticum.ru` на PHP 8.4 + 1C-Bitrix. Основная бизнес-функция: лидогенерация, AI-консультация, AI-калькулятор, коммерческие предложения, тарифы/услуги/кейсы.

Кастомный код находится в:

- `local/api/` — публичные GET JSON endpoints по инфоблокам;
- `local/rest/` — POST endpoints форм, чата, AI-интеграций;
- `local/php_interface/init.php` — Bitrix REST методы `calcrequests.*`;
- `local/php_interface/include/` — проектные include-файлы и конфиг;
- `local/templates/tacticum/` — активный шаблон, JS, CSS, компоненты;
- публичные разделы: `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`.

## Перед Работой

1. Прочитай `docs/workflow/README.md`.
2. Проверь `docs/workflow/current-state.md` и `docs/workflow/gap-analysis.md`.
3. Для backend/API/security задач прочитай `docs/adr/` и `.github/copilot-instructions.md`.
4. Выбери workflow lane: `Full Feature`, `Fast Fix`, `Security / Integration`, `Incident`.
5. Для нетривиальной задачи оформи план по `docs/workflow/codex-plan-template.md`.

## Жёсткие Ограничения

- Не редактировать `bitrix/`.
- Не хардкодить ID инфоблоков в новом коде — использовать `tacticum_rest_get_iblock_id('key')`.
- Не хардкодить URL внешних сервисов — использовать `tacticum_rest_get_ai_setting(...)`.
- Внешние запросы в production только HTTPS.
- PII в логах только через `tacticum_rest_mask_pii()` или `tacticum_rest_mask_string()`.
- Новые POST endpoints: `tacticum_rest_validate_origin()` → `tacticum_rest_rate_limit()` → parse JSON → `tacticum_rest_check_csrf()`.
- Новый JS/CSS подключать через `Bitrix\Main\Page\Asset`, не inline в HTML, если это не временный legacy cleanup.
- Новые глобальные функции: префиксы `tacticum_`, `tacticum_rest_`, `tacticum_api_`.

## Workflow Lanes

| Lane | Когда использовать | Минимальный маршрут |
|---|---|---|
| Full Feature Lane | новая фича, новый UX, новый публичный раздел, неясные требования | PM → Analyst → Designer/Architect → Dev → QA → DevOps → SEO |
| Fast Fix Lane | маленький баг, текст, CSS/JS/PHP-фикс без нового контракта | PM/Владелец → профильный Dev/SEO → QA smoke |
| Security / Integration Lane | REST, AI, PII, CSRF/CORS/rate limit, внешний сервис | PM → QA + Architect → Backend → QA security review |
| Incident Lane | P0/P1 production defect | PM → QA reproduction → Dev fix → QA smoke → DevOps deploy → PM summary |

## Definition Of Ready

Задача готова к работе, если указаны:

- `workflow_lane`;
- цель и business impact;
- affected files/areas;
- acceptance criteria;
- нужна ли ADR по `ADR gate`;
- нужен ли Designer по `Design gate`;
- для security/API/AI задач — ранний QA review.

## Definition Of Done

Задача закрывается, когда:

- изменения реализованы и проверены;
- PR/review checklist пройден;
- deploy выполнен, если нужен;
- post-deploy smoke-check выполнен;
- sitemap/meta/robots обновлены, если менялся публичный URL;
- ADR/docs/gap-analysis обновлены, если изменилось решение или закрыт gap;
- PM оставил краткий итог.

## Источники Истины

- Роли агентов: `.github/agents/`
- Правила кода: `.github/copilot-instructions.md`
- Архитектура: `docs/adr/`
- Workflow: `docs/workflow/README.md`
- Текущее состояние: `docs/workflow/current-state.md`
- Gaps: `docs/workflow/gap-analysis.md`
- MCP tools: `docs/mcp-tools.md`
