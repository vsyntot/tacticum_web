# ADR-007: Offer Section Component Pattern

**Статус:** Принято
**Дата:** 24.05.2026
**Автор:** Architect

## Контекст

Раздел `/offer/` перестал быть простой детальной страницей коммерческого предложения. Сейчас он объединяет:

- индексируемый hub-каталог примеров расчета;
- детальные страницы `/offer/<ELEMENT_CODE>/`;
- ЧПУ-фасеты и пагинацию каталога в namespace `/offer/catalog/...`;
- редиректы со старых query-состояний, включая сохранение Bitrix service-параметров вроде `clear_cache=Y`;
- SEO-логику для canonical, `noindex,follow` на фильтрованных состояниях и 404/noindex для несуществующих расчетов;
- данные каталога, производные от JSON-like свойства offer response, а не только от стандартных полей инфоблока.

Из-за этого стандартный комплексный `bitrix:news` не покрывает весь сценарий без значительной подмены routing, фильтрации и SEO-подготовки. При этом детальная страница одного элемента должна оставаться на штатном `bitrix:news.detail`, чтобы сохранить Bitrix lifecycle и существующий шаблон offer detail.

## Решение

Используем custom section-level компонент `tacticum:offer` как render boundary для раздела `/offer/`.

Публичная страница `offer/index.php` остается тонким front controller:

- подключает `prolog_before.php`;
- подключает `local/php_interface/include/offer_page.php`;
- применяет route/redirect/SEO state до визуального пролога;
- задает page-specific template options через Bitrix page properties, а не через `$GLOBALS`;
- подключает визуальный пролог через `prolog_after.php`;
- вызывает `tacticum:offer` с подготовленными параметрами.

Pre-header controller-логика вызывается через `local/php_interface/include/offer_page.php`, который является compatibility facade над `Tacticum\Offer\Page\Query`, `Resolver` и `Response`:

- определяет mode: `list`, `detail`, `not_found`;
- разбирает URL `/offer/`, `/offer/catalog/...`, `/offer/<ELEMENT_CODE>/` и legacy `?ID=...`;
- сохраняет Bitrix service-параметры вроде `clear_cache=Y` при нормализации catalog URL;
- выполняет редиректы до визуального пролога;
- выставляет HTTP status, title, description, canonical, robots и JSON-LD до `ShowHead()`;
- выставляет `tacticum_page_assets=faq` и `tacticum_body_class=bg-gray-50` через `SetPageProperty(...)`;
- готовит параметры для `tacticum:offer`.

Компонент `tacticum:offer` отвечает только за render dispatch:

- `list` подключает child component `tacticum:offer.catalog`;
- `detail` подключает штатный `bitrix:news.detail` с template `offer`;
- `not_found` выводит offer-specific 404 block.

Catalog/data shared-логика живет в `local/php_interface/include/offer_catalog.php`:

- поиск offer element по `ID`/`CODE`;
- построение canonical detail path;
- нормализация фильтров каталога;
- разбор `/offer/catalog/...`;
- построение ЧПУ-ссылок каталога;
- выборка, фильтрация, сортировка и пагинация catalog items.

URL namespace фиксируется так:

- `/offer/` — индексируемый hub;
- `/offer/<ELEMENT_CODE>/` — индексируемая detail page;
- `/offer/catalog/...` — неиндексируемые состояния каталога с canonical `/offer/`;
- search и non-default sort остаются query-параметрами;
- `urlrewrite.php` должен матчить `/offer/catalog/...` раньше detail-rule `/offer/<code>/`.

Новые изменения в `/offer/` должны поддерживать guards:

- `npm run seo:check`;
- `npm run css:check`;
- `npm run template-styles:check`;
- `npm run dev:preflight`;
- после deploy — `npm run seo:check:prod`.

## Альтернативы

Перевести весь раздел на стандартный комплексный `bitrix:news`.

Отклонено: комплексный компонент хорошо подходит для стандартной связки list/detail, но здесь list — это продуктовый каталог с derived facets, ЧПУ-фильтрами, query-to-pretty redirects и особыми SEO-правилами. Перенос этой логики внутрь стандартного компонента сделал бы routing и head-подготовку менее явными.

Оставить всю логику в `offer/index.php`.

Отклонено: публичная страница быстро превращается в смешение routing, бизнес-логики, выборки данных и HTML. Это расходится с Bitrix component pattern и затрудняет повторное ревью агентами.

Написать полностью кастомную detail-страницу без `bitrix:news.detail`.

Отклонено: это дублировало бы штатный Bitrix lifecycle для элемента и повышало риск расхождения с существующим шаблоном offer detail.

## Последствия

Плюсы:

- `/offer/` получает понятную Bitrix-границу: controller готовит request/SEO, component рендерит;
- detail-страницы сохраняют штатный `bitrix:news.detail`;
- catalog list развивается независимо от detail template;
- routing `/offer/catalog/...` не конфликтует с `/offer/<ELEMENT_CODE>/`;
- SEO-правила раздела можно проверять статически и post-deploy smoke.

Минусы и ограничения:

- `offer/index.php` не может быть полностью пустым component wrapper, потому что часть status/head/redirect логики должна выполниться до визуального пролога; поэтому она вызывается через `offer_page.php` facade между `prolog_before.php` и `prolog_after.php`, а реализация живет в `Tacticum\Offer\Page\*`;
- `urlrewrite.php` теперь критичен к порядку rules для `/offer/catalog/...` и `/offer/<code>/`;
- изменения в helpers `offer_page.php` и `offer_catalog.php` требуют аккуратной проверки sitemap, canonical и legacy redirects.
