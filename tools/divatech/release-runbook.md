# Divatech STATEFUL — ограниченный ручной релиз, revision 1

Этот runbook исполняется только после approval владельца на `release-plan.json`: plan_id, artifact SHA-256, production file/state hashes, expiry, tombstone и режим запуска. Он не является разрешением сам по себе. Автоматического STATEFUL controller в текущем проекте нет; FILE_ONLY controller не используется для этих действий.

## Принятые в кандидате границы

- Предложенный первый запуск: английский лендинг с контактами из исходного архива. Формы выключены; endpoint и закрытое хранилище подготовлены. Внешних уведомлений и тестовых заявок в production нет. Server-owned `tacticum_config.php` не меняется.
- 44 regular file destinations указаны в manifest: 4 изменяемых общих файла приложения, дополнение существующего `dehydrated/domains.txt`, остальные — новые файлы. Три operator members исполняются/используются только из private release directory, не из webroot.
- Только пять симлинков из manifest: три общих каталога в новом public root и два включаемых vhost в Nginx. Никаких `rsync --delete` по общим ссылкам.
- Единственное удаление существующего публичного файла — исходный ZIP по полному пути и совпавшему исходному SHA-256. Сначала сохранить копию вне webroot.
- Два advisory lock: `/run/lock/tacticum-production.lock` и `/run/lock/tacticum-adp-release.lock`; держать непрерывно от финальной сверки до post-apply manifest. Если lock занят — не продолжать.
- Root используется только для bounded filesystem/service/ACME operations штатными утилитами. PHP API-миграция запускается **как пользователь bitrix** из приватного каталога вне webroot; не исполнять candidate PHP от root.
- Не менять ядро, существующие s1 шаблоны/домены, Tacticum certificate, main DB credentials, общие ACME config/hook/client, `ssl_options.conf`, cron и существующий FILE_ONLY controller.

## 1. Финальный preflight и backup

1. Сверить approval/expiry, artifact и manifest hashes, все 66 наблюдаемых filesystem paths и production metadata state. Проверить canonical roots и отсутствие symlink ancestry у создаваемых путей. Проверить owner/group bitrix, занятость backend 8887, свободные disk/inodes, DNS A/AAAA обоих имён и Tacticum HTTP/TLS baseline.
2. При **любом** внешнем drift остановиться, пересобрать точный план и получить новый approval. Совпадение четырёх старых app hashes с Git HEAD проверено на этапе подготовки, но не заменяет финальную сверку под lock.
3. Создать root-only release/evidence directory вне webroot (0700), загрузить утверждённый архив. Сверить SHA-256 и все tar member hashes/пути/типы до распаковки. Special files и symlinks в tar запрещены.
4. Под lock повторить backup изменяемых существующих файлов и metadata-таблиц БД, сохранить original mode/uid/gid/link targets и существование каждого нового target. Таблицы: `b_lang`, `b_lang_domain`, `b_site_template`, `b_iblock`, `b_iblock_type`, `b_iblock_type_lang`, `b_iblock_site`, `b_iblock_property`, `b_iblock_group`, `b_event_type`, `b_event_message`, `b_event_message_site`. Для новых объектов дополнительно сохранить API journal. Не выводить содержимое backup в журнал/чат.
5. Распаковать файловый backup в безопасный private temporary directory и проверить hashes/metadata. Дамп metadata проверить восстановлением в изолированной disposable MySQL schema, без Bitrix bootstrap/агентов/почты. Локальная подготовительная репетиция этого backup уже прошла; до live apply сохранить evidence актуального backup под lock. При невозможности проверки — остановиться до live mutation.

## 2. Закрытая HTTP/ACME фаза

1. Создать только новый site-settings directory. Установить `operator/nginx-http-maintenance.conf` как `/etc/nginx/bx/site_avaliable/bx_ext_divatech.space.conf`, root:root 0644; создать соответствующий allowlisted `site_enabled` link.
2. `nginx -t`; при ошибке удалить только добавленные конфиг/link и повторить тест. Reload разрешён только после успешного теста. Tacticum baseline должен остаться прежним.
3. Убедиться: Divatech HTTP отдаёт 503 для `/`, тестовый случайный ACME token из `/var/www/dehydrated` читается по HTTP **на обоих доменах**. После проверки удалить только созданный token. Не пользоваться приватными ключами/аккаунтными файлами как тестовым содержимым.
4. Сверить hash существующего ACME client/hook и выполнить строго:

```sh
/home/bitrix/dehydrated/dehydrated -c --domain divatech.space --domain www.divatech.space
```

Вывод хранить приватно; не запускать общий BitrixEnv LE wizard и не применять `--force`. У клиента собственный lock: при конфликте с renewal дождаться завершения и заново проверить предикаты, не снимать чужой lock. Новые cert/key создаются только в Divatech cert directory; существующие ключи Tacticum не передаются и не заменяются. При ошибке issuance оставить Divatech закрытым, основной сайт проверить; не объявлять release успешным.

5. Проверить SAN обоих доменов, срок, chain и соответствие cert/key, не печатая private key. Записать только public certificate fingerprint. Локальный hash ключа — в private evidence при необходимости.

## 3. Файлы и CMS

1. Создать новый root `/home/bitrix/ext_www/divatech.space` (bitrix:bitrix, 0755), отдельные session/upload temporary dirs (0770), ссылки `bitrix/local/upload` с точными targets. Установить app/site файлы строго по manifest; права 0644, owner bitrix:bitrix. Сравнить hashes после копирования. Для существующих четырёх общих файлов использовать временный файл рядом и atomic rename; их backup уже проверен.
2. Operator PHP скопировать в private каталог bitrix вне webroot (0700), сверить hashes. Перед `apply` получить dry-run, сопоставить metadata hash с планом и указать новый journal. Root-путь s1 `/home/bitrix/www`, site-root `/home/bitrix/ext_www/divatech.space`. Скрипт создаёт s2 **неактивным**, задаёт английскую культуру, шаблон, домен; добавляет отдельный закрытый инфоблок и mail template. Повторный apply не заменяет разбор ошибки prepared journal.
3. `--mode=verify`: единственная s2 template/domain binding, s1 default и template без изменения, приватные права хранилища, точные свойства и почтовая привязка только s2. Сохранить before/after state hashes и назначенные ID; не добавлять s2 к чужим инфоблокам.
4. Установить остальные файлы инфраструктуры по manifest: Apache port/site, tmpfiles, boundary, HTTPS vhost, оба domain-list файла. В `domains-all.txt` строка Tacticum сохраняется точно. Live certificate paths в HTTPS config обеспечивают продление существующим cron без копирования private key. Новую строку cron не создавать.
5. Создать HTTPS enabled symlink. Пока s2 не активен, держать временный site-specific `00-release-closed.conf` с `return 503;`. Его путь и временное содержимое входят в exact plan; после публикации файл удаляется. HTTP maintenance остаётся 503, ACME доступен.
6. `httpd -t`, `nginx -t`, проверка certificate SAN и new backend port. Никакого reload при провале. При успехе graceful reload обоих сервисов. Проверить, что Tacticum всё ещё работает. Повторить hash-проверку protected shared configs.

## 4. Активация и публикация

1. Выполнить `--mode=activate` как bitrix с текущим ожидаемым state hash и существующим journal. Проверить s2 active/default=N, s1 default=Y.
2. Проверить новый backend через loopback/Host до внешнего открытия: DIVA template, корректная разметка, формы отсутствуют, 404 правильный. Доступ к public page должен проходить обычную Bitrix permission model, без NOT_CHECK_PERMISSIONS в index.php.
3. Установить финальный `nginx-http.conf` на место maintenance, удалить только собственный `00-release-closed.conf`, провести `nginx -t` и reload. Query-preserving canonical redirects обязаны работать для HTTP, www, старого Tilda URL и index.php.
4. После подтверждения отдельного backup и hash удалить только source ZIP tombstone. Проверить его прежний публичный URL на 403/404. Проверить исключение source ZIP из будущего Git/deploy.

## 5. Приёмка, наблюдение и BASE

- Divatech: HTTPS/TLS обоих имён, root 200, www/HTTP/legacy redirects, canonical/OG/lang=en/один H1, robots/sitemap, 404, desktop/mobile, menu/keyboard, изображения, no console errors. В контактах правильные mailto/tel. Форм и Tilda network requests нет.
- Неотрицательные security проверки: Divatech не открывает Tacticum API/REST, private configs, source archive, upload PHP и второй admin. Не отправлять production lead/email ради smoke.
- Tacticum: `/`, `/adp/`, `/price/`, `/calculator/`, `/offer/`, `/contacts/`, robots/sitemap, ресурсы и консоль; старый `/dev/` redirect. Сравнить HTTP statuses и site/template identity с baseline. Неверный CSRF не должен доходить до AI/почты.
- Наблюдение 15 минут: 5xx на обязательных URL, ошибки TLS, неверный сайт/шаблон, открытие private routes, новые PHP fatal — stop threshold: **любой** такой случай. Отмечать временные интервалы и результаты, не обещать отсутствие фоновых ошибок без журналов. Не публиковать raw access logs/PII.
- Сверить installed file/link metadata с manifest и CMS state с ожидаемой моделью. Сохранить две совпадающие hash-only BASE copies: локально в private evidence и на сервере root-only, включая оба roots и infrastructure. Для общего FILE_ONLY контура отдельно обновить его полный BASE после review/merge исходников; до этого его релизы не запускать.
- Only then: `verified`. Certificate issuance, HTTP 200 или успешный API apply по отдельности не означают завершение.

## Откат

Автоматический откат не включён. При регрессии остановить публикацию s2 собственным maintenance include; Tacticum baseline проверить сразу. Адресный rollback выполняется по отдельному решению владельца после сверки фактического состояния и backup/restore evidence.

Если в Divatech уже есть заявки, не удалять инфоблок и не восстанавливать всю общую БД. API rollback специально отказывается от удаления при наличии элементов или постороннем drift. При отсутствии заявок и совпавшем journal: API rollback как bitrix, восстановление пяти существующих regular files и source ZIP из backup, удаление только added files/links из manifest, восстановление предыдущего состояния Nginx/Apache после config tests. Сайт s1, его runtime данные и другие каталоги не удалять. Наличие одного source ZIP на старом публичном URL после аварийного rollback зафиксировать отдельно; не считать это целевым состоянием.

Новые Divatech certificate files при неуспешной раскатке сохранить в private evidence до разбора, не отзывать сертификат автоматически. Временные staging/restore/token/maintenance файлы убрать после проверки; release backups и audit evidence не являются временными файлами и остаются защищёнными.
