# ADR-005 — Vendor Analytics Assets And CSP Readiness

Статус: принято

Дата: 23.05.2026

## Контекст

Сайт использует внешние vendor scripts, в частности Yandex.Metrika. Inline vendor snippets усложняют будущую Content Security Policy: для них нужны `unsafe-inline`, nonce или hash, а это увеличивает риск регрессий и усложняет поддержку Bitrix template cache.

## Решение

Vendor analytics loader должен подключаться как явный template asset через `Bitrix\Main\Page\Asset`.

Для Yandex.Metrika:

- loader размещается в `local/templates/tacticum/js/metrika.js`;
- `header.php` подключает файл через `$obAsset->addJs(...)`;
- `noscript` pixel допустим в `header.php`, но без inline style;
- новые inline analytics scripts в public pages и template header не добавляются.

## Последствия

Плюсы:

- будущий CSP можно строить без nonce/hash для Metrika inline block;
- analytics ownership находится в template assets;
- browser smoke видит vendor loader как обычный JS asset.

Минусы:

- при введении строгой CSP всё равно нужно явно разрешить vendor domain `https://mc.yandex.ru`;
- после deploy нужно подтвердить работу целей в кабинете аналитики.

## Правило Для Нового Кода

Новые vendor scripts подключать через:

- `Bitrix\Main\Page\Asset` для template/public assets;
- explicit page asset flag, если vendor нужен только одной странице;
- component asset, если vendor принадлежит конкретному Bitrix component.

Inline vendor snippets допускаются только через отдельное ADR или documented security exception.
