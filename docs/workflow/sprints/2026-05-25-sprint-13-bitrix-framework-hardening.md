# Sprint 13 — Bitrix Framework Hardening

Дата: 25.05.2026

## Цель

Закрыть 100% технологических gaps, выявленных в challenge применения лучших практик разработки на Bitrix framework: сделать `init.php` тонким bootstrap, усилить компонентную модель, систематизировать `/offer/` service/cache boundary, убрать публичные vendor demo artifacts и закрепить guard.

ADR: `docs/adr/ADR-009-bitrix-framework-hardening.md`.

## Scope

| ID | Status | Area | Closure |
|---|---|---|---|
| S13-001 | done | `init.php` bootstrap | `local/php_interface/init.php` сокращён до guarded include/registration bootstrap; site helpers, SEO helpers и Bitrix REST `calcrequests.*` вынесены в отдельные include-файлы |
| S13-002 | done | `/offer/` cache | Лёгкий include `offer_catalog_cache.php` и `TacticumOfferCatalogCache` добавляют stable cache dir, TTL, managed cache tag `iblock_id_<id>` и explicit clean; cache очищается после add/update/delete и property-update событий offer elements |
| S13-003 | done | `/offer/` service boundary | Добавлен `TacticumOfferCatalogRepository`; `TacticumOfferCatalogService` стал фасадом для repository/cache/prepare, compatibility wrappers сохранены для текущих templates/routing helpers |
| S13-004 | done | Component namespace | Локальные компоненты больше не объявляют global `tacticum_*` helper-функции; общие нормализаторы параметров вынесены в `TacticumComponentParams` |
| S13-005 | done | FAQ section fallback | Numeric FAQ section fallback вынесен из component code в `content.faq_section_fallback_ids` example config; code-first lookup остаётся основным механизмом |
| S13-006 | done | SEO robots | Direct robots meta generation в 404/offer not-found переведён на общий helper `tacticum_add_robots_meta(...)` |
| S13-007 | done | Footer modal component | Footer contact modal вынесен в локальный компонент `tacticum:contact.modal` с сохранением DOM/JS contract |
| S13-008 | done | Vendor demo cleanup | Public font demo HTML (`index.html`, `symbol.html`, `unicode.html`) удалены из template fonts dir; `template-styles:check` блокирует их возврат |
| S13-009 | done | Guards | Добавлен `npm run bitrix:check`; PR/deploy workflows запускают Bitrix architecture guard вместе с CSS/config/SEO checks |
| S13-010 | done | Docs | `current-state`, `gap-analysis`, sprint artifact обновлены под новое состояние |

## Acceptance Criteria

- `init.php` не содержит domain functions, iblock queries или event body.
- Public pages не вызывают direct `bitrix:*` для контентных блоков.
- `local/components/tacticum/*/component.php` не объявляют global `tacticum_*` helper functions.
- `/offer/` catalog cache имеет service/repository/cache layer и event-based invalidation, включая отдельные изменения свойств элемента.
- Footer modal рендерится через локальный компонент.
- Vendor font demo HTML не деплоится.
- Все новые guards проходят локально.

## Verification

- `npm run bitrix:check`
- `npm run template-styles:check`
- `npm run config:check`
- `npm run seo:check`
- `npm run css:check`
- `npm run sale:sunset:check`
- `npm run dev:preflight` фиксирует degraded local PHP CLI state, GitHub PHP 8.4 lint остаётся fallback
