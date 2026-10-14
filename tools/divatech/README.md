# Divatech multisite — локальный кандидат и STATEFUL delivery

Это инструменты отдельного релиза второго сайта. Они не запускают deployment и не обходят `docs/workflow/production-deployment-governance.md`. Исходный подробный план: `docs/workflow/plans/2026-10-13-divatech-bitrix-multisite.md`; решение — ADR-015.

## Локальные проверки

```sh
php tools/divatech/contracts-test.php
node --check local/templates/divatech/script.js
npm run rest:endpoints:check
npm run rest:endpoints:self-test
npm run config:check
npm run bitrix:check
python3 tools/divatech/nginx-test.py
bash tools/divatech/apache-test.sh
```

`rehearsal-router.php`, `http-test.py` и `browser-test.py` используются **только** с изолированной копией и синтетическими данными. В текущей репетиции база MySQL 8.4 в контейнере `tacticum-multisite-mysql` доступна на `127.0.0.1:13364`, PHP — на `127.0.0.1:8766`. Корень `/tmp/divatech-rehearsal` содержит отдельные `www` и `divatech`, marker `.isolated-rehearsal`, собственные settings и config без production secrets. В dbconn заданы `NO_AGENT_CHECK` и `DisableEventsCheck`, sendmail отключён; Docker БД не опубликован на внешнем интерфейсе.

Нельзя использовать рабочий локальный `bitrix/.settings.php` или подключать тесты к production DB. Копия ядра должна быть чистой: sync поверх старого дерева оставил устаревшие классы, поэтому для репетиции использованы свежие main/iblock в отдельном каталоге. Основной `bitrix/` не исправлялся.

```sh
DIVATECH_REHEARSAL_ROOT=/tmp/divatech-rehearsal php -d short_open_tag=1 \
  -d sendmail_path=/usr/bin/false -S 127.0.0.1:8766 tools/divatech/rehearsal-router.php
```

После создания тестовых сайтов и включения форм **только в конфиге репетиции**:

```sh
python3 tools/divatech/http-test.py
# Python environment with playwright and installed Chrome:
python3 tools/divatech/browser-test.py
```

Тесты отправляют синтетические заявки и проверяют постановку уведомлений в отключённую очередь; они не подтверждают реальную доставку email. Browser test перехватывает все обращения к divatech.space и направляет их на loopback, остальные сети блокирует. Часть сценариев намеренно получает 403/413/422/429/503. Screenshot policy для false/true forms проверяется отдельно; тестовые consent/адреса никогда не публикуются.

## API-миграция

Первый dry-run требует уже подготовленных локальных корней; в production они создаются инфраструктурным этапом, после его approval. Состояние, полученное через prolog, не считать строго read-only production inventory: Bitrix может обновить runtime/cache. Для production initial inventory применять прямое read-only чтение metadata.

```sh
php -d short_open_tag=1 tools/divatech/migrate.php \
  --root=/tmp/divatech-rehearsal/www \
  --site-root=/tmp/divatech-rehearsal/divatech --mode=dry-run
```

Для `apply`, `activate`, `rollback` обязательны `--expect=<state_sha256>` и `--journal=<private path outside webroots>`. Production release lock, точный approval, резервная копия, проверка восстановления и границы вмешательства обеспечивает оператор до запуска. Команда с одним `--expect` не заменяет этот контракт. `verify` проверяет созданные сущности без их изменения. `apply` создаёт неактивный s2; повтор с принадлежащим ему applied journal проверяет состояние. Журнал prepared/неизвестное состояние требует разбора причины, а не удаления и слепого повтора.

Идентификатор созданного инфоблока берётся из проверенного результата и добавляется под `iblocks.divatech_leads` в server-owned конфиг. `divatech.forms_enabled=false` остаётся до решения по recipient, sender, policy/consent и хранению. Почтовая очередь с `queued` не доказывает SMTP delivery. Тестовая отправка в реальный ящик требует согласованного получателя.

## Пакет и инфраструктурный этап

```sh
python3 tools/divatech/package.py --output /absolute/private/new-release-directory
```

Builder создаёт `candidate.tar`, `manifest.json`, `manifest.sha256`, заново читает каждый member и сверяет hash. Артефакт не содержит config/secrets, исходный ZIP или ядро. Shared-файлы адресованы в `/home/bitrix/www`, site-файлы — в `/home/bitrix/ext_www/divatech.space`. Файлы `operator/*` исполняются из private release directory, не копируются в webroot. Source ZIP tombstone ограничен одним путём и исходным hash.

На установленном BitrixEnv 9.0-10 исследован штатный link-site вызов:

```sh
/opt/webdir/bin/bx-sites -a create -s divatech.space -t link \
  --kernel_root /home/bitrix/www -r /home/bitrix/ext_www/divatech.space
```

Этот вариант **не выбран для текущего кандидата и не выполнялся**. Мастер создаёт фоновые Ansible jobs и имеет дополнительные побочные изменения. В текущем пакете для review подготовлены отдельные HTTP/HTTPS/Apache/tmpfiles конфиги по установленным шаблонам и три точных symlink. Это позволяет описать и проверить весь файловый diff до запуска. Новые конфиги лежат в стандартных BitrixEnv путях, но будущий запуск его мастера может их перегенерировать: сначала выполнять reconciliation. CMS s2 создаётся отдельной API-миграцией.

Кандидат `nginx-site-boundary.conf` устанавливается как дополнительный site-specific include, не заменяет общие правила BitrixEnv. Важен `^~ /local/`: обычный prefix уступил бы общему PHP regex. Корень `/` минует composite cache, `.php`/архивы под assets запрещены, legacy URL/`www` сохраняют query string при редиректе. Синтаксис и precedence проверены в контейнере; перед production нужен тест с фактическими полными Nginx/Apache конфигурациями.

TLS оформляется отдельным сертификатом на `divatech.space` и `www.divatech.space`. Установленный BitrixEnv LE wizard обновляет общую dehydrated checkout/config/hook, выпускает сертификаты для всех доменов и меняет cron; `configure_cert` также меняет shared ssl_options (в установленном шаблоне keepalive_requests=1000, на сервере=150). Поэтому текущий кандидат использует существующий dehydrated с двумя явными `--domain`, а HTTPS config напрямую ссылается на live fullchain/privkey/chain Divatech. Копирования ключей в Git/артефакт нет. Новый domains-файл и адресное дополнение общего domains.txt обеспечивают обработку существующим cron. Сертификат Tacticum, checkout/config/hook/ssl_options и расписание сохраняются и сверяются по hash.

## Что ещё требуется до production apply

1. Выбор контактного запуска либо заполненные настройки форм и согласованное хранение заявок.
2. Exact infrastructure/DB/config diff, hashes, plan_id, expiry и approved tombstone. Кандидат приложения собран, но `production_apply_ready=false` — точный инфраструктурный план ещё не закрыт.
3. Reconciliation runtime/site/config, licence eligibility, closed test window; полный backup изменяемого scope и production restore rehearsal под lock. Синтетическая локальная репетиция не заменяет проверку текущего production backup.
4. Approval владельца на конкретный STATEFUL план и bounded waiver недоказанных staging частей, если они остаются.
5. Apply, конфиг-тесты, сертификаты, site activation, проверка обеих площадок, browser/API/SEO smoke, monitoring и dual BASE.

При ошибках после начала Bitrix API или wizard не предполагать атомарность: legacy API и Ansible имеют собственные коммиты/рестарты. Сверить журнал и фактическое состояние, затем адресно восстановить. При реальных новых заявках не применять полный DB restore.


### BitrixEnv error fallback

В установленном BitrixEnv `errors.conf` содержит `error_page 404 = @fallback`: обычный `return 404` в закрывающем location может повторно передать исходный запрос в приложение. Boundary include использует явный `error_page 403 404 = @divatech_denied` и terminal named handler. Не заменять его голым 404. `nginx-test.py` воспроизводит этот механизм и проверяет 21 сценарий; native config tests и внешние denial checks всё равно обязательны. Production correction и evidence: план 13.10.2026, `DIVA-FIX-3c159bad6601`.

Review regressions (journal provenance, direct Security caller isolation and split-prolog order): `php tools/divatech/review-regression-test.php` and the same command with `--s1`. These use local fixtures and do not bootstrap a live Bitrix database.
