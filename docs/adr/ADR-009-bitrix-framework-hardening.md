# ADR-009: Bitrix Framework Hardening Pattern

Дата: 25.05.2026

Статус: Принято

## Контекст

После componentization Sprint 11/12 публичные страницы стали тоньше, но оставался технологический долг относительно Bitrix best practices:

- `local/php_interface/init.php` смешивал bootstrap, SEO helpers, Bitrix REST callbacks и бизнес-валидацию;
- local component `component.php` файлы объявляли global helper functions;
- `/offer/` catalog cache жил как ручной `Data\Cache` без явной привязки к событиям offer-инфоблока;
- footer contact modal оставался крупным HTML-блоком в template shell;
- часть публичных vendor demo HTML файлов лежала в template fonts directory.

## Решение

1. `init.php` остаётся только bootstrap/registration файлом.
2. Shared helpers живут в отдельных include-файлах:
   - `site_helpers.php`;
   - `seo_helpers.php`;
   - `component_helpers.php`;
   - `calcrequests_rest.php`.
3. Повторяемые component parameter нормализаторы живут в `TacticumComponentParams`; component-level global helper functions не добавляем.
4. `/offer/` catalog data boundary строится как service/repository/cache:
   - `TacticumOfferCatalogRepository` отвечает за чтение инфоблока;
   - `offer_catalog_cache.php` подключается из bootstrap как лёгкий event/cache layer;
   - `TacticumOfferCatalogCache` отвечает за cache ID, cache dir, TTL, managed tag и clean;
   - `TacticumOfferCatalogService` остаётся публичным фасадом для templates/routing helpers;
   - compatibility wrappers допустимы, пока templates и `offer_page.php` на них завязаны.
5. Cache `/offer/` каталога очищается по событиям `OnAfterIBlockElementAdd`, `OnAfterIBlockElementUpdate`, `OnAfterIBlockElementDelete`, `OnAfterIBlockElementSetPropertyValues` и `OnAfterIBlockElementSetPropertyValuesEx` для offer-инфоблока.
6. Footer modal является локальным компонентом `tacticum:contact.modal`.
7. Static guard `npm run bitrix:check` закрепляет архитектурные ограничения.

## Последствия

Плюсы:

- `init.php` больше не становится местом для произвольной бизнес-логики;
- локальные компоненты меньше загрязняют global namespace;
- editor/admin изменения offer-инфоблока быстрее сбрасывают catalog cache;
- footer template остаётся shell-слоем;
- регрессии component/bootstrap паттернов ловятся CI/deploy.

Минусы и ограничения:

- `offer_catalog.php` всё ещё содержит compatibility wrappers ради существующих templates и routing helpers;
- полный переход offer helpers на namespaced classes можно делать отдельным рефакторингом, когда не меняется SEO/routing behavior;
- `content.faq_section_fallback_ids` в server config нужно синхронизировать на окружениях, если FAQ sections не имеют стабильных `CODE`.
