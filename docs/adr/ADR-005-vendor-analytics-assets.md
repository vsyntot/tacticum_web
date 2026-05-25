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

После Sprint 08 template отправляет transitional `Content-Security-Policy-Report-Only` header. Sprint 09 добавляет config switch `security.csp_mode=report-only|enforce`; default остаётся `report-only`. Sprint 12 убрал неиспользуемые Google Fonts/Readdy origins. Sprint 16 заменил `/contacts/` Yandex constructor script на Yandex map widget iframe с координатами московского офиса; политика в report-only режиме всё ещё содержит Yandex origins для compatibility, но enforcing rollout должен убрать неиспользуемые источники после triage.

## Runway До Enforcing CSP

Перевод из report-only в enforcing делать отдельным hardening PR после deploy baseline:

1. Собрать report-only evidence минимум с production/staging smoke: нет first-party inline script/style violations, `/contacts/` Yandex iframe работает, Yandex.Metrika goals подтверждены.
2. Убрать лишние источники из политики и оставить только реально используемые vendor domains; `unsafe-inline` не расширять без отдельного security exception.
3. Включать enforcing header только через `security.csp_mode=enforce` вместе с rollback path на прежний `report-only`.
4. После deploy выполнить visual/browser smoke и ручное подтверждение Метрики/карты.

## Последствия

Плюсы:

- будущий CSP можно строить без nonce/hash для Metrika inline block;
- analytics ownership находится в template assets;
- browser smoke видит vendor loader как обычный JS asset.
- CSP rollout начинается с report-only режима, без риска заблокировать карту, Метрику или другой production UX.

Минусы:

- при переводе CSP из report-only в enforcing режим нужно убрать лишние разрешения, по возможности отказаться от `unsafe-inline` и повторно проверить карту/Метрику и публичный UX;
- после deploy нужно подтвердить работу целей в кабинете аналитики.

## Правило Для Нового Кода

Новые vendor scripts подключать через:

- `Bitrix\Main\Page\Asset` для template/public assets;
- explicit page asset flag, если vendor нужен только одной странице;
- component asset, если vendor принадлежит конкретному Bitrix component.

Inline vendor snippets допускаются только через отдельное ADR или documented security exception.
