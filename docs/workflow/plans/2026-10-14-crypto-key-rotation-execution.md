# STATEFUL: ротация раскрытого crypto_key

Статус: **verified**. Production key заменён 14.10.2026 в 09:55 UTC; технические проверки пройдены, владелец подтвердил вход в админ-панель, две совпадающие scoped STATEFUL BASE copies сохранены.
Parent: [MSCI P0 / D2](2026-10-14-multisite-ci-core-sync.md).
Exact plan ID: `7ff5ac7c9d71772f5968bb2ab7ae8330ed0fd8e7e69826da69d9894437c9fef0`.
Operator SHA-256: `3f0995e13ac65c4d804f362926abc52354c0521c3bfbfa611acbc2860cff5975`.

## Согласованный scope

Только замену `crypto_key` внутри `/home/bitrix/www/bitrix/.settings.php`,
с сохранением остальных значений, mode 0600 и владельца 600:600.
Остановить `crond`, `nginx`, `httpd`, повторно проверить отсутствие PHP workers,
config/source drift и строк в шести проверенных таблицах зашифрованных данных,
атомарно заменить конфигурацию, затем запустить `httpd`, `nginx`, `crond`.
`php-fpm` сейчас inactive; смена этого состояния инвалидирует план.

**Оба сайта временно будут недоступны**, запуск новых cron tasks задержится.
Для записи отведено 120 секунд после начала остановки, общий watchdog оператора
180 секунд; это лимиты прекращения попытки, а не гарантия длительности простоя.
При неудачном старте сервисов потребуется ручное восстановление. Старые
подписи/cookies могут перестать работать; открытые страницы нужно обновить,
пользователям может понадобиться повторный вход.

Approval должен явно включать этот plan ID, STATEFUL apply, допустимость
остановки сервисов, источник решения владельца и expiry не более часа.
До получения решения `approval.json` отсутствовал. Решение владельца получено 14.10.2026: пункт 2 «Да, сейчас.» в ответ на exact plan; approval записан перед apply с expiry 45 минут.
Изменение script/plan/source/config/table counts требует нового review.

## Проверено до решения

- SHA-256 старой конфигурации: `fcc2d51cf61d9b2afb719b90bd557fadcd53d48fe8e8ab2b32407a6722e10dbb`.
- SHA-256 candidate: `27f2c9a9ea39ff02733fe091d7b6c1e9430371b95114c67a81e912183c3c89f6`.
- Private backup есть на сервере и локально; restore rehearsal content/mode/uid/gid прошёл.
- PHP загрузка старого/candidate config подтверждает, что изменён только ключ.
- Установленный Bitrix Cipher: новый ключ шифрует/расшифровывает synthetic data,
  старый ключ не расшифровывает новые данные.
- `b_main_mail_sender`, `b_socialservices_user`, `b_socialservices_zoom_meeting`,
  `b_socialservices_zoom_meeting_recording`, `b_user_phone_auth`, `b_user_auth_code`: 0 строк.
- Повторный read-only preflight exact operator на сервере прошёл; сервисы и settings не менялись.
- 6 isolated tests покрывают apply order, metadata, повторный запуск, отсутствие
  approval, expiry/drift, новые зашифрованные строки, stop timeout и restart failure.
  Mock service tests не являются измерением production downtime.

Private evidence и exact JSON:
`~/Library/Application Support/Tacticum/releases/multisite-ci-core-sync-20261014/`.
Server package: `/root/tacticum-releases/MSCI-SEC-20261014T090802Z/`.
Secret backup/candidate не публикуются в Git, логи или CI artifacts.

## Исполнение и восстановление

1. Зафиксировать решение владельца; проверить публичную доступность обоих сайтов.
2. Под двумя существующими release locks повторить preflight, затем quiescence.
   Stop intent пишется до service command: timeout не скрывает частичную остановку.
3. Повторить preflight без web/cron PHP workers; при drift возобновить сервисы и
   остановиться до записи. Удалённые/внешние ручные изменения не считаются
   разрешёнными конкурентными действиями в этом окне.
4. Сверить и атомарно заменить candidate; восстановить активные ранее сервисы.
   Journal содержит наблюдаемый settings digest и ошибки restart.
5. При отказе до замены ключ прежний, но раскрытие сохраняется. После замены
   новый ключ остаётся: автоматического возврата раскрытого ключа нет.
   При SIGKILL/потере хоста сначала прочитать journal и текущий digest, затем
   восстановить `httpd`, `nginx`, `crond` адресно; повторный apply запрещён.
6. Проверить главные/ключевые маршруты и security boundaries обоих сайтов,
   состояние services, конфигурацию и новые ошибки. Не отправлять реальные лиды.
   Выполнить 15 минут наблюдения и сохранить две совпадающие копии evidence.

CMS admin credentials в `.env` не обнаружены. Полноценный повторный вход
администратора требует существующей авторизованной сессии/владельца; его нельзя
подменить успешным GET login page. Пока обязательная проверка недоступна,
результат остаётся `applied_unverified`, а не `verified`.

Этот план **не** разрешает history rewrite, изменение GitHub visibility,
DB-password reset, создание storage или установку нового deploy controller.
Он закрывает только адресную часть P0; остальные security findings остаются
открытыми до своей проверки/устранения.

## Исполнение 14.10.2026

Владелец разрешил exact STATEFUL apply ответом «2. Да, сейчас.»; scope и
operator digest сохранены. Действует ранее принятое решение об отсутствии stage.
Перед запуском оба сайта вернули HTTP 200, local/server backup и JSON plan
совпали по SHA-256; свободно около 45 GB и 3 млн inodes.

Оператор завершился с code 0 за 5,28 секунды (вся операция, не измерение
пользовательского downtime). `crond → nginx → httpd` остановлены;
`httpd → nginx → crond` запущены. Target имеет candidate SHA-256,
uid/gid 600:600 и mode 0600; php-fpm остался inactive. Шесть проверенных
таблиц по-прежнему пусты. Journal: `applied_unverified`, restart errors нет.

HTTP smoke 12 маршрутов двух сайтов passed; на Divatech admin/private paths
403 и неизвестная страница 404. Tacticum browser: 6 desktop/mobile cases
главной, ADP и contacts passed, runtime errors 0, SEO checks passed.
Divatech browser replay после восстановления test environment также passed:
1440/390 px, изображения, menu/anchor, SEO, 404 и forms-off, ошибок JS/5xx нет.
Сверены 453 manifest entries предыдущего выпуска, drift нет; временных
.settings-rotation-* не осталось. В четырёх проверенных nginx/httpd error logs
новых critical entries не найдено.
Мониторинг завершён: 21 цикл / 252 маршрутные проверки за 900,47 секунды, все passed.
Повторный admin login запрошен у владельца, ответ пока отсутствует.

Failure ledger текущего хода:
- Python urllib: локальный CA trust не настроен; переход на системный curl с
  включённой TLS verification дал успешный pre-health. Production не менялся
  до успешной проверки.
- Divatech browser replay: Python Playwright отсутствует; offline uv cache
  не содержит package, CUA iab/chrome недоступны. Среда теста восстановлена
  из package registry через uv; повтор того же reviewed script на текущем
  production прошёл. Предыдущие неуспешные попытки сохранены как диагностика.

Значения ключей/config не записаны в публичные evidence. Полный security P0
остаётся открыт: опубликованная история, другие найденные credentials и
полнота инвентаризации рассматриваются отдельно.

## Историческая контрольная точка 10:11 UTC

- HTTP monitoring: 15 минут, 21 цикл, 252 проверки, отклонений нет.
- Tacticum browser: 6 desktop/mobile cases passed; Divatech: 2 viewport scenarios passed.
- Повторная проверка четырёх nginx/httpd error logs: новых critical entries 0;
  неразобранных critical lines 0. Это проверенные журналы, не утверждение об отсутствии любых ошибок системы.
- Aggregate acceptance evidence сохранён mode 0600 локально и на сервере;
  SHA-256 обеих копий: `c2c57cfb4029adc9d1263927335102a0c8d5e34b33d59c415898336cdf7e015b`.
- Evidence: `rotation-acceptance.json`, сверка: `rotation-evidence-verification.json`
  в ранее указанных private directories. Копии evidence совпадают; новый verified
  BASE не продвинут, поскольку обязательный admin relogin ещё не подтверждён.
- Применение завершено, техническая проверка passed; общий статус ротации
  `applied_unverified`, единственный оставшийся gate этого scope — CMS admin relogin.
- Владелец получил запрос проверки входа во время наблюдения. Новое разрешение
  на уже выполненную замену не требуется. P0 целиком и общий CI/CD план остаются открыты.

Фактическое календарное время текущего хода: 09:51:42–10:11:02 UTC, 19 минут
20 секунд. Из него monitoring window 15 минут шёл параллельно с проверками и
документацией; `active_effort=unknown`, чистый `wait=unknown` — отдельный
хронометраж отсутствует. Полный остаток CI/CD по-прежнему `partial_unknown`.

## Завершение приёмки — 14.10.2026

Источник последнего gate: владелец сообщил «в админ-панель зашел, все ок.».
Это owner-confirmed login, а не автоматизированная проверка учётной записи.
Вместе с ранее полученными техническими evidence закрывает admin relogin gate.

Под обоими существующими release locks повторно проверены live config digest,
тип файла, uid/gid/mode и SHA-256 прежнего acceptance evidence. Drift отсутствует.
Сохранены две совпадающие `BASE-CRYPTO-STATEFUL.json` mode 0600 — локально и
в server private release directory. SHA-256: `41563fc01bd66bc0f40ee39eb33be75d59d1a703d03f41b5ed1c1413e75d18e9`.
BASE содержит только hash/metadata изменённого config, plan/operator/approval
references, technical evidence hash и подтверждение владельца; секретов нет.

Ротация имеет статус `verified`. Старый operator journal и acceptance record
с `applied_unverified` сохраняются как история исполнения; финальный record
и `rotation-final-verification.json` фиксируют переход. Application FILE_ONLY
BASE не заменён и не объявлен multisite v2 BASE. Новый apply, restart либо
изменение ключа при завершении приёмки не выполнялись.

Оставшиеся P0 findings и общий CI/CD план остаются открытыми.
