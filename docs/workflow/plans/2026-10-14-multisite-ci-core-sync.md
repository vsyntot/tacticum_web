# План: единая разработка, сборка и поставка Tacticum и Divatech

Дата: 14.10.2026. Редакция: 2 — реализация.
Статус документа: подготовлен для самостоятельного чтения и принятия решений.
Статус реализации: `in_progress`; начаты P0/P1/P2/P4/P5, остальные этапы не приняты.
Workflow lane: `Security / Integration`.
Issue: не создан. Gap IDs: `MSCI-01` — `MSCI-10`.
Координатор подготовки плана: Codex / project-manager. Исполнитель реализации: Codex; ответственные роли указаны по действующему governance.
`version_source`: `/Users/ivanmonakhov/.codex/skills/project-manager/SKILL.md` и приложенный пользователем snapshot; `loaded_version`: `2.7.26` (прочитан при начале исполнения).

## 1. Цель, полномочия и границы

Обеспечить разработку обоих сайтов из текущего репозитория, воспроизводимую сборку, доставку общего кода и двух публичных корней, а также контролируемый перенос штатных обновлений Битрикс с production в локальное окружение и фиксацию версии платформы в Git.

Пользователь принял исполнение плана сообщением «$project-manager погнали в реализацию». Авторизация покрывает локальные изменения, проверки и подготовку reviewable production plans. Конкретные решения D1–D4 сохраняются: создание внешнего хранилища, ротация действующих секретов, history rewrite и новая production automation требуют адресного решения по подготовленному результату.

- `accepted_scope`: реализация P0–P10 в пределах действующего governance; текущий результат `in_progress`.
- `artifact_recorded_scope`: разделы 5–10; D1–D4 не превращаются в одобрение по факту записи.
- `read_scope`: репозиторий, инструкции, проектные credentials в памяти, read-only GitHub/production inspection и агрегаты без PII.
- `write_scope`: проектный tooling/CI/docs и Git index в ветке `feat/multisite-delivery-core-sync`; подготовительные private release files вне webroot сервера и локального Git.
- Off-limits без отдельного адресного решения: live production application/config/DB, GitHub visibility/history/settings, создание storage. Vendor code вручную не редактируется; исключение секретного config из index не удаляет рабочий файл.
- Классы данных: публичный код и документация, несекретные метаданные релиза; секретные backup/candidate config только в private release directories с правами 0700/0600. В Git и выводах их значений нет.

Существующий контактный запуск Divatech остаётся отдельным завершённым результатом. Этот план не открывает формы Divatech, не меняет позиционирование, контент, домены или инфраструктуру хостинга. Новый публичный stage-хост не требуется: владелец ранее принял работу без него. Для конкретной новой раскатки сохраняются exact plan, backup/restore и применимые условия waiver; отсутствие stage не отменяет изолированные тесты.

## 2. Прочитанные источники и подтверждённый исходный срез

Основные источники:

- `AGENTS.md`, [workflow](../README.md), [правила кода](../../../.github/copilot-instructions.md).
- [Production governance](../production-deployment-governance.md), [ADR-013](../../adr/ADR-013-github-actions-delivery-gates.md), [ADR-015](../../adr/ADR-015-divatech-multisite.md).
- [План запуска Divatech](2026-10-13-divatech-bitrix-multisite.md), [результаты доставки](2026-10-14-divatech-delivery-review.json).
- [Scope](../../../tools/deploy-scope.json), [контракт сравнения](../../../tools/production-deploy-contract.mjs), [контроллер](../../../tools/production-file-only-controller.py).
- [Deploy workflow](../../../.github/workflows/deploy.yml), [Quality Gate](../../../.github/workflows/pr-check.yml), [сборщик](../../../tools/production_artifact_build.py).
- Локальный `tacticum-web-local-sync/SKILL.md` и `scripts/sync.mjs`: изучены как действующий механизм; синхронизация в ходе грилля не запускалась.
- [Битрикс: резервное копирование](https://dev.1c-bitrix.ru/user_help/settings/utilities/dump/dump.php): при обновлении меняются ядро и структура БД; требуется их совместимость.
- [GitHub: удаление чувствительных данных](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/removing-sensitive-data-from-a-repository): ротация раскрытых секретов и последствия переписывания истории.

База грилля: `main`, commit `d694a5a018624202677b1c32c8ddf8773f21cf89`; рабочее дерево до записи плана чистое. Проверки выполнены 14.10.2026; перед исполнением требуется сверка изменений, а не слепое повторение старого approval.

| ID | Установленный факт и основание | Граница вывода |
|---|---|---|
| E1 | GitHub API: `vsyntot/tacticum_web` публичный, `bitrix/.settings.php` есть в HEAD. До ротации сравнение значений в памяти подтвердило совпадение `crypto_key` с production; пароль БД не совпал | Подтверждено раскрытие конфигурационного секрета; использование злоумышленником не исследовалось. Полная история и другие окружения ещё не проверены |
| E2 | `git ls-files bitrix` — 76 954 файла; Quality Gate запрещает любые изменения `bitrix/`; install/version.php модулей исключены `.gitignore` | Нет согласованного штатного процесса обновления tracked vendor snapshot |
| E3 | Изолированная проверка реальной функции sync: другое серверное содержимое с более старым mtime пропущено; локальный файл, отсутствующий на сервере, сохранён. Архивируется только `PROD_WEB_ROOT` | Это merge по времени, не доказательство равенства ядра; полный production sync не выполнялся |
| E4 | Изменение файла ядра в fixture не меняет действующий PROD manifest | Drift ядра не связан с допуском приложения к доставке |
| E5 | Изменение `sites/divatech/index.php` не меняет candidate manifest. В workflow post-deploy команды направлены на Tacticum | Общая автоматизация не охватывает полный второй корень и его runtime smoke |
| E6 | Классификатор возвращает STATEFUL для config example, но workflow проверяет только статусы сравнения и записывает FILE_ONLY | Path classification не включена в реальный допуск; одна классификация по путям также не заменяет семантический review |
| E7 | Fixture CONVERGED вызывает deploy_required=true при пустом changed_paths; старый BASE при следующем изменении даёт CONFLICT | Нужны корректный no-op и управляемое продвижение BASE |
| E8 | Контроллер требует совпадения наборов путей PROD/CANDIDATE; автоматической финализации verified/dual BASE после smoke в workflow нет | Поддерживается ограниченная замена существующих файлов, не общий жизненный цикл релиза |
| E9 | Две даты сборки меняют sitemap; одинаковые payload/manifest/output path с разным временем gzip меняют digest архива | Неизменяемость однажды собранного артефакта есть; побайтовая воспроизводимость повторной сборки не обеспечена |
| E10 | CI PHP 8.4, server PHP 8.5.11. Divatech fixtures подменяют Bitrix/DB. В обязательных checks main отсутствует `Divatech virtual host boundaries` | Локальные проверки полезны, но не доказывают интеграцию полного приложения с фактической платформой |

Действующий общий FILE_ONLY BASE содержит 399 entries. Его нельзя автоматически считать BASE нового многокорневого scope. Исторические сведения об открытых задачах CI в governance/current-state и более поздние внедрения расходятся; сверить их адресно в P1, не отменяя обязательные controls.

## 3. Целевая модель владения и решений

| Область | Источник истины | Разрешённое направление |
|---|---|---|
| Код приложения, два шаблона и публичные корни | Git после review | Git → immutable artifact → production; локальные правки → PR |
| Ядро и штатные/Marketplace-модули | Проверенное состояние после штатного обновления production | Production → очищенный закрытый snapshot → pinned dependency локальной среды и CI |
| Конфигурация инфраструктуры, схемы и миграции приложения | Reviewed definitions в Git; фактическое состояние отдельно проверяется | Через STATEFUL/control-plane план с prerequisites и rollback |
| Контент, заявки, пользователи, uploads | Production | Отдельный защищённый backup; для тестов synthetic fixtures или согласованная обезличенная копия |
| Пароли, ключи, лицензия, адреса подключений окружения | Защищённая конфигурация окружения | Подстановка вне Git и application artifact |
| Cache, sessions, логи, временные файлы | Runtime каждого окружения | Не синхронизируются как исходники |

Предлагаемый основной вариант: текущий репозиторий хранит код обоих сайтов и `core.lock.json`; точные vendor files хранятся в закрытом неизменяемом snapshot. Чистый checkout с разрешённым read-only доступом к snapshot позволяет подготовить приложение без подключения к живому production. Место хранения, стоимость и права ещё не выбраны; новый сервис этим документом не создаётся.

| Решение | Рекомендация | Когда необходимо | Статус |
|---|---|---|---|
| D1: хранение ядра и видимость Git | Закрытые snapshots + lock в текущем Git. Альтернатива: очищенный vendor snapshot в закрытом Git с отдельным import lane | До P2 и изменения отслеживания ядра | deferred владельцем до переноса в GitLab |
| D2: устранение раскрытия | Проверить область применения, подготовить ротацию и очистку Git; изменение видимости — возможное временное ограничение доступа, не устранение уже сделанных копий | Для внешних изменений P0 | exact crypto rotation verified; history/visibility не согласованы |
| D3: модель доверенного CI и BASE | Недоверенный PR без private credentials; доверенная интеграция отдельно; operational BASE и независимая долговечная копия с ограниченными правами | До выдачи доступов P5/P7 | proposed; Architect/DevOps готовят конкретную схему |
| D4: первый production выпуск | Bounded план установки контроллера и отдельный application release с проверкой обоих сайтов | P9 | proposed; ранее принятый no-stage подход сохраняется |

Роли из governance: PM — scope/решения, Architect — contracts/trust boundaries, DevOps — снимки/сборка/доставка/восстановление, Dev — реализация, QA — раннее review и приёмка. Именные назначения и дополнительные расходы не утверждены. При продолжении сначала проверить уже принятые решения; повторно их не запрашивать.

## 4. Порядок, зависимости и оценка

Каждый этап завершается своим проверяемым результатом. Работа над безопасным локальным кодом и fixtures может продолжаться независимо от ожидания конкретного production approval. Публикация новых снимков и production writes не обходят незакрытые security/compatibility gates.

| Этап | Результат | depends_on | Роль | Статус | Предварительная работа / календарное время без ожидания |
|---|---|---|---|---|---|
| P0 | Устранение раскрытия и защита от повторения | — | Security reviewer + DevOps, PM | in_progress | Подготовка 2–4 ч / 2–4 ч; полный срок partial_unknown |
| P1 | Согласованные contracts и карта владения | — | Architect + PM + QA | in_progress | 2–4 ч / 2–4 ч |
| P2 | Проверяемый импорт платформы и core lock | P0, P1; D1 | DevOps + Dev | in_progress | 4–8 ч / 4–8 ч |
| P3 | Изолированная среда обоих сайтов | P2 | Dev + QA | todo | 4–8 ч / 4–8 ч |
| P4 | Одна воспроизводимая сборка двух корней | P1 | DevOps + Dev | in_progress | 3–6 ч / 3–6 ч |
| P5 | Классификация и обязательные CI gates | P1, P4; D3 | DevOps + QA | in_progress | 3–6 ч / 3–6 ч |
| P6 | Многокорневой контроллер и восстановление | P1, P4 | DevOps + Architect + QA | in_progress | 6–12 ч / 6–12 ч |
| P7 | Smoke, наблюдение и финализация BASE | P3, P5, P6; D3 | QA + DevOps | in_progress | 3–6 ч / 3–6 ч |
| P8 | Сквозная репетиция обновления и доставки | P2, P3, P7 | QA + DevOps | todo | 3–6 ч / 3–6 ч |
| P9 | Проверенный первый production цикл | P0–P8; D4 | DevOps + QA, владелец approval | todo | 2–4 ч / 2–4 ч плюс approval и наблюдение |
| P10 | Эксплуатационная передача | P9 | PM + DevOps + QA | todo | 1–2 ч / не менее 24 ч с контрольной проверкой |

Основание оценок: один технический исполнитель, расширение существующих manifest/controller/fixtures, две текущие цели доставки, отсутствие смены хостинга и дизайна. Это оценки планирования, не бюджет и не обещание срока. Известная подготовка P0 и P1–P10 суммарно: 33–66 рабочих часов; календарно при последовательной работе около 5–10 рабочих дней, без неопределённой части P0, ожидания решений/storage и внешних зависимостей. Общий срок — `partial_unknown` до оценки безопасной ротации, совместимой тестовой БД и выбора хранилища. Пересчитать после P0/P1.

## 5. Пошаговые действия и приёмка этапов

### P0. Устранить раскрытие секретов

1. Сохранить минимальное доказательство раскрытия вне публичного Git: path/commit, тип поля, факт совпадения; не сохранять значения в отчёте.
2. Проверить HEAD, историю и относящиеся к проекту artifacts/config на другие секреты. Сначала агрегаты и пути; не выводить содержимое. Проверить релевантные окружения, не делать вывод о безопасности старого пароля только по несовпадению с production.
3. Установить использование `crypto_key` в текущей версии Битрикс, зависимые подписи/токены/данные и последствия ротации. Не заменять ключ произвольной строкой без проверки. Подготовить отдельный STATEFUL/security plan с backup, порядком изменений и проверками обоих сайтов.
4. Подготовить исправление ignore/secret checks и безопасный пример конфигурации. Исключить чувствительный файл из Git index без удаления рабочей production-конфигурации; это отдельное адресное действие по принятому плану, не разрешение редактировать ядро.
5. Представить конкретные изменения credentials/visibility/history для решения D2; выполнить после применимой авторизации. При очистке истории сохранить соответствие старых/новых commit SHA и не переписывать исторические hashes evidence как будто это прежние подписи.
6. Проверить оба сайта, зависимые интеграции, отсутствие действующих раскрытых значений в новой публикации и невозможность повторного commit секретных файлов. Для внешних копий/старых клонов явно сохранить ограничения очистки.

Выход: redacted security record, проверенная ротация либо явно открытая зависимая часть, очищенный набор отслеживаемых конфигов, regression fixtures секретных путей. P0 не закрывается только добавлением `.gitignore`. Доказательств компрометации нет; проверка инцидента при необходимости выделяется адресно.

### P1. Зафиксировать contracts и границы владения

1. Сверить актуальные ADR-013/015, governance и фактическую реализацию. Отметить исторические записи, не выдавая их за текущий status.
2. Описать versioned multi-root scope с ключами `shared`, `tacticum`, `divatech`, отображением source→target, exact files, additions/deletions, excluded/runtime paths и допустимыми links.
3. Сохранить текущую структуру Git: первый сайт в существующих разделах, второй в `sites/divatech/`, общий код в `local/`. Массовый перенос первого сайта не нужен.
4. Разделить FILE_ONLY приложения, STATEFUL config/DB и изменение доверенного controller/workflow. Изменение публичного файла второго корня само по себе может быть FILE_ONLY после поддержки mapping; изменение s2/DB/vhost остаётся STATEFUL.
5. Определить правила core import, app drift reconciliation, совместимости схемы БД и platform lock. Выбрать D1/D3; подготовить отдельный ADR либо адресное дополнение существующих ADR с явным решением владельца.

Выход: принятые схемы scope/release/core lock и threat model; каждый путь имеет владельца. Приёмка: однозначное сопоставление обоих корней, общих файлов и исключений, отсутствие symlink traversal и дублирующей доставки `local/` через s2.

### P2. Сделать импорт платформы из production

1. Инвентаризировать vendor code, штатные и Marketplace-модули, версии и обнаруженные custom files под `bitrix/`. Не считать весь каталог однородным ядром; неожиданные кастомизации вынести на решение.
2. Реализовать отдельный allowlisted read/export механизм с минимальными правами. Действующий forced-command `manifest` не расширять до произвольного shell. Root/password helper не превращать в штатный CI transport.
3. Создавать snapshot в стабильном окне после штатного обновления: проверить завершение updater и согласованность до/после чтения; при изменении входов прерывать импорт. Архив исключает secrets/license, backups, runtime, данные пользователей и `.git`.
4. Проверять полный manifest содержимого, digest архива, size/type/mode, пути, дубликаты, ссылки и отсутствие запрещённых данных. Сверка только размера архива не является проверкой содержимого.
5. Сохранить immutable snapshot в принятом закрытом хранилище. `core.lock.json` содержит snapshot ID/digest, vendor manifest digest, module versions, профиль PHP/extensions/DB, schema compatibility ID, provenance и версию export rules. Секретов и DB dumps в lock нет.
6. Подготовить импорт в отдельной ветке/каталоге. При варианте D1 со snapshots снять vendor code с отслеживания Git отдельным проверенным изменением, сохранив локально нужный snapshot; не удалять production core. Заменить тотальный CI запрет на разрешённый metadata import workflow и запрет ручных vendor patches.
7. Обновлять локальный vendor snapshot целиком по manifest, включая удаления только внутри его границы. Сохранить прежний snapshot для переключения назад; грязное дерево приложения не перетирать.
8. Старый `tacticum-web-local-sync` оставить явно ограниченным ручным file merge либо обновить его на тонкий вызов versioned repository tooling после отдельного принятия изменения skill. Он не должен оставаться скрытой второй реализацией платформенной синхронизации.

Выход: export/import tooling, core lock, закрытый snapshot, manifest и cleanup evidence. Приёмка: одинаковые входы дают одинаковый digest; equal/newer local mtime не влияет на результат; отсутствующий upstream vendor file удаляется из новой локальной копии; app/config/upload остаются нетронутыми. Архивы staging удаляются после проверки; необходимые snapshot/backup не объявляются временными файлами.

### P3. Поднять воспроизводимую среду обоих сайтов

1. Добавить repository-owned bootstrap из clean checkout и pinned core snapshot. Закрепить проверенные версии образов и расширений; целевой профиль соответствует production PHP 8.5.11/MySQL 8.4.11 на исходном срезе. Перепроверить версии при реализации; PHP 8.4 можно сохранить дополнительной совместимостью.
2. Поднять оба document root, общие links, host routing и тестовую БД. Фиксировать конфигурацию среды и версию synthetic fixtures, а не машинные пути автора.
3. Создать необходимые сайты, шаблоны, инфоблоки и минимальные данные через конфиг/API/migrations. Не хардкодить новые production IDs. Не считать произвольный свежий install совместимым с обновлённым ядром без проверки schema/module versions.
4. Отключить реальные почту, cron/агентов, AI, webhooks и платные внешние вызовы. Использовать локальный mail sink/stubs и ограничения исходящего доступа. Production credentials в окружении отсутствуют.
5. Если synthetic data недостаточно, отдельно подготовить согласованную обезличенную копию совместимой схемы; исходный дамп не выдавать PR jobs и не коммитить.

Выход: одна документированная команда подготовки, два доступных локальных сайта, versioned test data. Приёмка: повторный bootstrap идемпотентен, оба сайта работают с реальным Bitrix bootstrap, сетевые побочные эффекты отсутствуют, cleanup удаляет только созданные среды.

### P4. Собрать единый воспроизводимый артефакт

1. Расширить builder/manifest на общий код и оба корня по одному scope. Production paths выбирает trusted mapping; candidate не задаёт произвольные absolute destinations.
2. Связать release manifest с commit/tree, core lock, schema prerequisites, scope/controller versions и test evidence. Фиксировать affected sites; изменение shared code затрагивает оба сайта.
3. Устранить зависимость sitemap от текущего дня: источник lastmod — подтверждённая дата изменения страницы или явный versioned input. Зафиксировать timezone/locale/порядок/mode и tar/gzip metadata.
4. Собирать frontend в чистом окружении из lockfile. Хэшировать фактически включённые файлы; проверять соответствие manifest до публикации пакета.
5. Исключить ядро и runtime secrets из application artifact; ядро подключается к тестовой среде по pinned dependency. Артефакт доставки не перезаписывает штатно обновлённый production core.

Выход: единый build с payload двух сайтов и manifest. Приёмка: два независимых clean build одного набора входов дают одинаковые hashes; изменение каждого корня отражается в плане; docs-only изменения не создают application diff; forbidden paths блокируются.

### P5. Включить классификацию и обязательные CI gates

1. Вычислять полный набор изменений PR/релиза относительно корректной базы, включая multi-commit push, renames, additions/deletions. Подключить реальный classifier; unknown/control-plane/stateful не маскируются FILE_ONLY.
2. Сохранить semantic review совместимости данных: path classifier не доказывает отсутствие DB impact внутри произвольного PHP.
3. Добавить secret scan и проверки запрещённых путей до публикации artifacts/import PR. Согласовать ограничения сканирования vendor и истории; не скрывать исключениями реальные секреты.
4. Включить fast checks обоих сайтов, runtime matrix и trusted integration на pinned core. Недоверенные PR/forks не получают production/private snapshot credentials; механизм передачи проверенного snapshot в доверенную среду определить в D3.
5. Защитить изменение workflow/controller/scope отдельным review и версией; privileged controller не исполняет candidate scripts и dependency installers. Контроллер обновляется отдельным control-plane release.
6. После проверки новых jobs обновить required status checks main, включая multisite/integration/security; проверить фактический отказ merge при падении каждой обязательной проверки. Настройки review и правила для владельца оформить явно, без предположения о независимом reviewer, которого нет.

Выход: проверенный CI graph и branch/environment policy. Приёмка: core import разрешён только по выбранному контракту, mixed/stateful release не применяет файловую половину, failed обязательный job блокирует нужный переход, PR code не получает production secrets.

### P6. Расширить контроллер доставки и восстановления

1. Принимать только артефакт с точными mappings двух корней и совпадающими версиями схемы/контроллера. Валидировать source/destination realpath, type, ссылки и дубликаты.
2. Поддержать create/replace/delete/rename как явные операции. Удаления перечислены в approved plan; не использовать широкий `rsync --delete` по webroot или общим links.
3. Под общим server lock проверить app PROD manifest, core fingerprint, schema prerequisites, разрешение окна обновлений, место/inodes и неизменность approved inputs.
4. Сохранить затрагиваемые файлы обоих корней, metadata и исходное отсутствие новых targets. Проверить восстановление в private directory до применения.
5. Применять dependency-compatible порядок; сохранить phase journal. Если два корня нельзя менять совместимо на месте, предусмотреть согласованное короткое maintenance window; не называть такой deploy атомарным.
6. При сбое остановить операции, сохранить evidence и сделать адресный recovery plan. Rollback восстанавливает старые файлы, удаляет только добавленные этим релизом и не затрагивает core/DB/runtime.
7. Проверить cache/OPcache policy обоих сайтов с учётом no-cache Divatech; сформировать post-manifest, но пока только статус applied, не verified.

Выход: trusted controller v2, failure journal и проверенный recovery. Приёмка: fixtures create/replace/delete/rename, symlink escape, stale plan, конкурирующий запуск, disk failure и прерывание между корнями дают ожидаемый результат; unsafe apply прекращается до записи.

### P7. Завершать релиз проверкой и новым BASE

1. Добавить доменную smoke matrix: Tacticum основные страницы/сценарии и Divatech главная/404/assets/redirects/canonical/robots/sitemap/mobile/security boundaries. Формы Divatech остаются off; production тесты не отправляют реальные обращения.
2. Выбирать deploy_required по LOCAL_CHANGE. Для CONVERGED выполнить reconciliation/verification без application write; совпадение файлов само по себе не даёт права объявить новый BASE verified.
3. После credential teardown выполнять smoke и заранее заданное окно наблюдения. Ошибка оставляет release applied_unverified/failed и блокирует автоматическое продвижение BASE.
4. Добавить trusted finalizer: проверить manifests и evidence, записать две совпадающие BASE в независимых trust domains с release/core/schema/commit binding. Чтение следующего BASE не зависит от жёстко прописанного исторического JSON-файла.
5. Защитить promotion от гонки: compare-and-swap предыдущего BASE, монотонная последовательность релизов, проверка active release. Частичная запись одной копии требует recovery, а не выдачи verified.
6. Установить retention и доступы отдельно для snapshot, BASE, backups и CI logs; срок GitHub artifact не должен быть единственным сроком жизни BASE.

Выход: завершённый release lifecycle. Приёмка: failed smoke не двигает BASE; второй релиз и повторный запуск первого не создают ложный deploy/conflict; расхождение независимых копий выявляется до следующей записи.

### P8. Пройти сквозные репетиции

1. На disposable среде повторить цепочку «изменение platform snapshot → import → lock PR → bootstrap → CI обоих сайтов → application release».
2. Проверить соответствие ядра схеме БД. В сценарии обновления платформы restore использует согласованную пару core+DB; файловый откат ядра на новую схему не принимается.
3. Выполнить все сценарии A1–A12 из раздела 8, включая ложные mtime, удаление vendor file, out-of-band prod change и прерывание записи BASE.
4. Проверить application hotfix round trip: read-only наблюдение drift → отдельная ветка → review → новый artifact; не копировать весь production webroot в текущий worktree.
5. Измерить время backup/restore и доставки на репрезентативных данных. Только после измерения предложить RTO/RPO и длительность окна; не выводить их из размера Tilda ZIP.

Выход: воспроизводимый evidence пакет без secrets/PII. Приёмка: обязательные сценарии passed, открытые limitations названы; повторный фактический core update production ради теста не нужен.

### P9. Выпустить ограниченный production цикл

1. Подготовить точный control-plane план установки wrappers/controller/scope и initial multi-root BASE; старый 399-file BASE не использовать как автоматически полный новый scope. Bootstrap допускается только после полной сверки и verification.
2. Сопоставить новый scope с фактическим production и устранить drift через отдельные решения. Зафиксировать hashes, TTL approval, backups, rollback и владельца наблюдения. Перед writes обновить доступы/host identity только если факты изменились.
3. После exact approval установить новый механизм адресно, проверить denial cases ключей и доступность обоих сайтов. Не заменять одновременно core/DB без отдельного STATEFUL плана.
4. Проверить no-op план, затем отдельный согласованный малый FILE_ONLY релиз, затрагивающий оба назначения. Один no-op не доказывает работоспособность apply. Не вводить публичную функциональность только ради теста; выбрать безопасный проверяемый diff при подготовке конкретного релиза.
5. Выполнить backup/restore, apply, smoke/monitoring, finalizer и независимую проверку BASE. Проверить повторный запуск без записи. При regression — stop/recovery по approved plan.

Выход: production-proven pipeline для обоих сайтов. Приёмка: actual apply и no-op имеют evidence; ручная раскатка не выдана за успешный автоматический apply.

### P10. Передать рабочий процесс в эксплуатацию

1. Опубликовать краткие runbooks: разработка, выпуск приложения, update Битрикс, импорт ядра, reconciliation hotfix, восстановление, отказ CI/BASE.
2. Свести current-state/gaps/ADR/governance, заменив устаревшие ограничения только подтверждёнными решениями. Сохранить историю прежних релизов.
3. Назначить исполнителя контрольной проверки через 24 часа после выпуска. Автоматизация мониторинга — только явная созданная и проверенная задача; запись в плане не является scheduler.
4. Закрывать gaps по их acceptance/evidence, а не по числу commits или зелёному PR. Удалить временные snapshots/staging/containers/logs; сохранить нужные защищённые backups и release records.

Выход: команда может выполнить оба штатных цикла по runbooks; недостающие назначения/контрольные проверки остаются открытыми, если ещё не выполнены.

## 6. Штатные циклы после внедрения

### Изменение приложения

Clean branch → pinned platform/local environment → изменения общего кода/сайтов → PR checks → trusted integration → immutable build → app/core/schema preflight → three-way plan двух корней → exact approval → lock/backup/restore/apply → smoke обоих сайтов → наблюдение → verified dual BASE.

### Обновление Битрикс через штатную систему

Оператор открывает update session, запрещает новые deploy и дожидается текущего → backup core+DB и restore readiness → штатный updater → проверка обоих сайтов → стабильный snapshot и совместимый DB profile → import/lock PR → CI → локальная среда обновляется → application delivery снова допускается.

Важно: веб-updater не обязан соблюдать lock нашего контроллера. Для гарантии взаимоисключения нужен организационно-технический вход в update session, согласованный с администратором; конкретный enforcement проверить в P1/P8. Изменение core вне этого окна выявляется preflight и инвалидирует approval. Если исключить гонку с неуправляемым администратором технически не удалось, это остаётся явным ограничением, а не заявлением о гарантированной блокировке.

### Ручной production hotfix

Наблюдение drift → минимальный private snapshot изменённых app files → secret scan → отдельная reconciliation branch → review/tests → approved plan. Выбор «принять production», «вернуть Git» или «объединить» фиксируется для конкретных paths/hashes. Dirty worktree не перезаписывается.

## 7. Предполагаемые изменения файлов

Новые имена — предложения, а не уже существующие инструменты.

| Область | Изменение |
|---|---|
| `tools/deploy-scope.json`, `tools/production-deploy-contract.mjs`, CLI/self-tests | Versioned multi-root ownership, операции, binding и classification |
| `tools/production_artifact_build.py`, tests, `tools/static-sitemap-generate.mjs` | Детерминированная сборка и generated inputs |
| `tools/production-file-only-controller.py`, wrappers/manifest tooling | Два корня, create/delete, platform preflight, journal/recovery |
| Предлагаемые `core.lock.json`, `tools/core-sync/`, схемы | Export/import/provenance; единственная versioned реализация sync |
| Предлагаемые environment/bootstrap/fixture files | Два сайта, pinned PHP/DB, реальные Bitrix integration tests без production data |
| `.github/workflows/pr-check.yml`, `deploy.yml`, `sitemap.yml` | Классификация, security, integration, build once/promote, smoke/finalization |
| `.gitignore`, secret-scan policy | Исключение environment-owned файлов; выбранная стратегия vendor tracking |
| Документация ADR/workflow/skills | Согласованные новые contracts и замена старой mtime-sync инструкции после принятия |
| GitHub/server settings | Только отдельные reviewed изменения protections, credentials, wrappers/storage |

На этом шаге эти изменения не выполняются. Application URLs/SEO не меняются; sitemap/robots обоих сайтов включаются в проверки. Правила `Asset`, D7, config helpers и REST bootstrap сохраняются. Штатное обновление ядра и импорт проверенного snapshot не должны превратиться в ручное редактирование vendor code.

## 8. Матрица приёмки

| ID | Сценарий | Ожидаемый проверяемый результат | Этап |
|---|---|---|---|
| A1 | Fresh checkout + pinned snapshot + synthetic DB | Оба сайта запускаются; production credentials и исходящие реальные интеграции отсутствуют | P3/P8 |
| A2 | Два clean build одинаковых входов, разные часы/каталоги | Совпадают file manifests и archive digests | P4/P8 |
| A3 | Изменение каждого public root и shared code | План содержит правильные назначения; shared change требует smoke обоих | P4/P7/P8 |
| A4 | Create/replace/delete/rename в обоих корнях | Только approved paths меняются; backup/recovery восстанавливает исходное состояние | P6/P8 |
| A5 | STATEFUL/mixed/control-plane/unknown diff | Автоматический FILE_ONLY apply не начинается | P5/P8 |
| A6 | PROD hotfix, stale approval, changed core/schema | Понятная причина stop; никакой тихой перезаписи или продолжения по старому approval | P6/P8 |
| A7 | Повторный запуск, CONVERGED и следующий обычный релиз | No-op без application write; следующий релиз использует verified BASE без ложного конфликта | P7/P8/P9 |
| A8 | Сбой между корнями или во время BASE promotion | Состояние partial/failed, старый verified BASE сохранён; проверенный адресный recovery | P6/P7/P8 |
| A9 | Vendor update: новые/изменённые/удалённые файлы и произвольные mtime | Локальная платформа совпадает с snapshot; app/config/runtime сохранены | P2/P8 |
| A10 | Реальный Bitrix bootstrap обоих сайтов на целевой платформе | Host/SITE_ID/template, routing, content, forms-off, origin/security и SEO корректны | P3/P7/P8 |
| A11 | PR/fork и попытка включить secret/path escape | Нет production credentials/private data; unsafe artifact/import блокируется до публикации | P0/P2/P5/P8 |
| A12 | Окно core update пересекается с deploy | Второй процесс не допускается штатным способом; out-of-band изменение детектируется | P6/P8 |

Тесты локальных fixtures не заменяют production smoke. Для P9 предварительно предлагается 15 минут первичного наблюдения плюс контроль через 24 часа; длительность и интервалы фиксируются в exact plan. Предлагаемые stop conditions: воспроизводимый сбой любого обязательного маршрута/контракта, manifest mismatch, нарушение security boundary или новая критическая ошибка в приложении. Единичный сетевой сбой отделять от дефекта bounded-повтором; критерии не менять после получения неудобного результата.

## 9. Риски, восстановление и ограничения

| Риск | Действие и условие остановки |
|---|---|
| Ротация нарушит подписи/зависимые данные | Сначала карта использования и репетиция; не возвращать раскрытый ключ как обычное решение rollback, определить безопасный recovery отдельно |
| Очистка истории сломает SHA references/старые клоны | Согласованный план, old→new mapping, защита от повторного внесения секретов; BASE/attestations перепривязать явно |
| Неполный snapshot во время update | Стабильное окно + проверки до/после; не публиковать mixed snapshot |
| Несовместимость core и DB | Фиксировать совместимую пару; update rollback рассматривает данные и потерю новых записей, не восстанавливает общую БД поверх работающих сайтов автоматически |
| Symlink traversal или удаление shared data | Trusted mappings, lstat/realpath checks, explicit operations, hard exclusions и negative fixtures |
| Потеря локальной работы | Изолированный import/worktree, stop при конфликте, Git reconciliation приложения вместо mtime overwrite |
| Недоступность snapshot storage/BASE | Проверяемое наличие и восстановление; нельзя молча брать latest или только server copy |
| Partial apply/неуспешный smoke | Journal, stop, согласованный адресный rollback; новый verified BASE не создаётся |
| CI green без проверки реальной платформы | Разделять unit/config fixtures и обязательный trusted integration; фиксировать проверенную platform identity |
| Простой двух сайтов из-за общего ядра/БД | Общий update/recovery plan и smoke обоих; многосайтовость не является независимой отказоустойчивостью |

RTO/RPO, retention, стоимость storage и полный перечень секретов пока не измерены/не согласованы. Не выдавать их за действующие обязательства. До P9 обязан быть конкретный rollback plan для каждого production scope, включая изменения controller/config; application backup не заменяет STATEFUL backup.

## 10. Definition of Done реализации и следующий шаг

Реализация считается завершённой, когда A1–A12 подтверждены, security scope P0 закрыт, применимые ADR/contracts приняты, фактическая доставка обоих сайтов и повторный no-op проверены, последний BASE сохранён в двух независимых копиях, runbooks согласованы с фактическими инструментами и P10 выполнен. Waiver допускается только по действующему governance; неотработанные gates не скрываются зелёным workflow.

Следующий шаг реализации: завершить локальный multi-root artifact/contract и CI fixtures, подготовить безопасный platform export/import. D1: принято ограничение независимости от GitHub в связи с будущим переносом в self-managed GitLab; сравниваются private S3 и GitLab Generic Package Registry. D2: exact crypto rotation verified после owner-confirmed admin login и dual scoped BASE. History cleanup не разрешён. Ни один production gate не считается пройденным по локальным тестам.

## 11. Проверка документа и учёт текущего хода

Назначение: самостоятельное чтение владельцем проекта и исполнителями. Документ отделяет наблюдения грилля от предложений, содержит результаты каждого этапа, зависимости, acceptance, recovery и открытые решения. Прежняя приёмка запуска Divatech не отменена; новые gaps относятся к общему жизненному циклу двух сайтов.

Прогноз подготовки плана: 2–4 минуты сверка + 6–10 минут запись/проверка; всего 8–14 минут работы и календарно. Начало: 14.10.2026 08:52:34 UTC. Проверка структуры выполнена в 08:58:25 UTC: 11 этапов, 12 сценариев приёмки, 10 evidence items и 10 gaps; ID уникальны, зависимости не имеют циклов, все локальные ссылки плана разрешаются. `git diff --check` прошёл для изменённых отслеживаемых документов. Проведена смысловая сверка цели, границ, оценок и принятия решений; исправлены две формулировки без изменения scope.

`calendar_elapsed` до этой контрольной точки — 5 минут 51 секунда; `active_effort=unknown`, `wait=unknown`: раздельный учёт не ведётся. Прогноз оказался с запасом благодаря повторному использованию уже проверенных источников грилля. Подготовка документа завершена; реализация P0–P10, commit/push, новые удалённые проверки и production mutations в текущем ходе не выполнялись.

## 12. Execution checkpoints — 14.10.2026

Записи ниже хронологические. Последний статус D2: ротация verified после подтверждения входа владельцем и записи dual scoped BASE; D1 уточнён: GitHub storage исключён, выбор S3/GitLab Registry остаётся открытым. Ранние записи «не применён / approval pending» описывают состояние до разрешения владельца.

- Ветка `feat/multisite-delivery-core-sync`, исходный HEAD `d694a5a018624202677b1c32c8ddf8773f21cf89`; commit/push ещё не выполнены.
- P0: `bitrix/.settings.php` исключён из index, локальный файл сохранён; добавлены ignore и индексный secret guard с regression fixtures. Это не очистка опубликованной истории и не ротация.
- P0: подготовлен private STATEFUL crypto plan `3bcad6b05653b7b5293007ffce486fc34ea1acb584a176db6dccce23915e13a5`; backup/restore rehearsal и проверка нового Cipher/config passed. Live config не менялся. В шести проверенных таблицах зашифрованных полей 0 строк; повторная проверка перед apply и контроль новых записей обязательны. Старые подписи/cookies после смены ключа могут потребовать обновления/повторного входа.
- Secret-history проверка пока ограничена двумя известными config paths; полная история не признана чистой. Один bounded DB authentication probe раскрытого исторического пароля через локальный socket не удался; другие auth paths не проверены.
- P1/P4: прототип v2 scope/manifest охватывает оба корня, shared impact, create/delete, CONVERGED и platform mismatch; `productionReady=false`. Legacy production controller ещё не заменён.
- P4: gzip/tar metadata нормализованы; исходные файлы сверяются с manifest, archive publishing exclusive. Sitemap lastmod перенесён в versioned input. Self-tests passed; full clean-checkout build ещё впереди.
- P5: guard включён в Quality Gate; remote run/required check settings ещё не изменены.
- Private evidence: `~/Library/Application Support/Tacticum/releases/multisite-ci-core-sync-20261014/`; значения конфигурации не копировать в Git.
- Forecast исполнения на старте: ближайший локальный пакет 30–60 минут работы и календарно; весь план 33–66 рабочих часов / около 5–10 рабочих дней плюс неизвестная часть P0 и внешние зависимости. Полная оценка `partial_unknown`. `calendar_elapsed`, `active_effort`, `wait` текущего исполнения — `unknown` (раздельный хронометраж отсутствует).
- Failure ledger: начальный scan нашёл документационный crypto sample и malformed private-key fixture; reviewed exceptions закреплены exact file digest, повторный scan passed. Это узкие исключения, не подавление реальных конфигурационных секретов. Ошибки поиска двух имён tooling исправлены; артефакты ими не изменялись.

### Локальная контрольная точка: contract/build/import/CI

- Добавлены [ADR-016 candidate](../../adr/ADR-016-multisite-platform-delivery.md), [tooling/runbook](../../../tools/multisite-delivery.md), v2 manifest/CLI и provider-neutral snapshot tooling. `productionReady=false` сохранён; legacy live controller не заменён.
- Сборка реального worktree с явно synthetic platform fixture: 399 primary + 10 Divatech entries; два архива совпали, SHA-256 `459e6e079f5718c2dd73ac19c7184cd32a980cc098dbd43df448ef81b4010c3b`, 8 533 920 bytes. Известные длинные значения PASSWORD/TOKEN/SECRET из `.env` в entries не обнаружены. Это ограниченная проверка, не полный secret audit; fixture lock не является release lock. Временные build outputs удалены.
- Read-only production vendor inventory: 71 831 файл, 1 020 105 850 bytes, manifest `6ef7c7d006f2a10e20ab2fa4a1fe042af753880706fada9daad9ff6a10f22149`. Core main 26.800.0 (classes/general/version.php), iblock 26.0.100; 18 module directories, component namespace `bitrix`, templates `.default`, `furniture_blue`, `landing24`. Не выполнено доказательство vendor origin каждого файла.
- Уточнение case sensitivity: обнаружены 6 пар каталогов `landing/lang/{en,ru,kz}/lib/Transfer`/`transfer` и `Import`/`import`; среди экспортируемых файлов коллизий нет (`caseSensitiveRequired=false`). Empty directories не входят в snapshot. Общая защита импорта на несовместимые volumes проверена synthetic collision fixture.
- Export/import fixtures: digest, metadata, traversal/symlink/duplicate/missing members, changed source, cleanup, case-sensitive requirement, mtime-independent new snapshot и upstream deletion passed. Production snapshot не создан/не опубликован, реальный core lock пока отсутствует.
- CI: новые delivery fixtures и PHP 8.5 syntax/contracts. Старый PHP 8.4 check name сохранён. Добавлен full-diff release classifier от verified Divatech merge commit; STATEFUL/control-plane/unknown блокируют старый FILE_ONLY flow. CONVERGED больше не требует application write. Это переходная защита, не v2 finalizer и не автоматически принятый BASE.
- Локально passed: multisite contract+actual archive CLI; legacy deploy contract; release gate; artifact builder; platform snapshot (5 tests); repository secret guard self-test; crypto replacement self-test; Divatech 28+15+16 contract/regression checks; YAML и shell syntax трёх workflows; sitemap/config/SEO/Bitrix architecture checks. Remote GitHub run/required check settings ещё не изменены.
- Дополнение failure ledger: production Python не поддерживает `hashlib.file_digest`; заменено на streaming SHA-256, повторная inventory прошла. Начальная case-fold проверка считала empty directories блокером экспорта; requirements теперь вычисляются по file manifest, несовместимый import всё ещё блокируется. SEO check ожидал текущую дату; согласован с versioned input, повторный check passed.
- Приёмка A1–A12 не закрыта: A2/A3/A5/A6/A7/A9/A11 имеют частичные локальные evidence; A1/A4/A8/A10/A12 и real runtime/integration/production cycle остаются открытыми согласно полному смыслу сценариев.
- Следующий адресный decision: D1 storage. Для D2 есть private preparation evidence; live rotation operator с quiescence/recovery подготовлен и проверен на fixtures; exact approval ещё не получен. P6/P7 trusted controller, smoke/finalizer и P8–P10 не объявляются выполненными.
- Forecast: подготовка контракта/build/security уже выполнена частично; диапазон оставшегося полного плана пока `partial_unknown`, исходные 33–66 рабочих часов / 5–10 рабочих дней не пересчитаны в обещание. Основание неопределённости — невыбранный storage, незакрытый P0 и совместимый core+DB bootstrap. `active_effort`, `calendar_elapsed`, `wait` раздельно не измерены.

### D2: подготовлен исполнимый план ротации

[Exact STATEFUL plan](2026-10-14-crypto-key-rotation-execution.md): `7ff5ac7c9d71772f5968bb2ab7ae8330ed0fd8e7e69826da69d9894437c9fef0`. Private operator/helper/JSON размещены вне webroot; повторный read-only preflight passed, `approval.json` отсутствует, сервисы и live config не изменялись. Для устранения гонки encrypted rows предусмотрена краткая остановка nginx/httpd/crond и проверка PHP workers. Шесть isolated tests passed, включая recovery при timeout и отказе restart. Повторный admin login остаётся явной проверкой владельца/существующей сессии; CMS credentials в `.env` не обнаружены.

Последняя общая проверка: 449 PHP application/tooling files прошли syntax на локальном PHP 8.5.7; full Git-index secret scan passed, config на диске сохранён; ссылки новых docs и staged whitespace check прошли. Source/fixture код текущей ветки проиндексирован, commit/push не выполнялись. Дополнение failure ledger: после добавления restart-failure fixture блок drift assertion оказался в неверном тесте; перемещён в expiry/drift test, повтор всех 6 tests passed.

Измеряемый календарный интервал: ветка создана 09:00:30 UTC 14.10.2026; контрольная точка до подготовки apply operator — 09:38:07 UTC (37 минут 37 секунд). Это интервал ветки, не полный учёт активной работы; `active_effort` и `wait` unknown. Полный план остаётся `in_progress`/`partial_unknown`, forecast не заменяет фактическую приёмку.

Decision checkpoint 2026-10-14 09:47:02 UTC: D1 (private GitHub snapshot repo / existing private S3) и exact D2 rotation approval запрошены, ответ ещё не получен. Approval не выводится из ожидания; live apply не выполнен. Все текущие изменения staged, commit/push отсутствуют. Календарно от создания ветки 46 минут; active_effort/wait unknown. Новые staged files после полного index scan проверены адресно тем же guard; YAML/shell syntax и docs links passed.


### D1: первоначальное сравнение — 14.10.2026 (рекомендация GitHub superseded)

Decision status: `pending`; владелец запросил детали до решения. Хранилище
не создавалось, снимок production не публиковался. Рекомендация ниже не является
принятым изменением архитектуры или бюджета.

**Что остаётся единым.** Репозиторий `tacticum_web` хранит приложение обоих
сайтов, shared custom code, deployment tooling, manifests и будущий core lock.
Отдельное private storage содержит очищенные vendor snapshots. Snapshot —
зависимость сборки; дополнительный repository для Release assets не является
второй кодовой базой приложения.

**Что хранится.** Архив разрешённых vendor files, manifest/checksums, версии
модулей и metadata совместимости. Исключаются `.env`, `.settings.php`, license,
DB dump, uploads, cache и `bitrix/backup`. Source review и secret scan обязательны
до публикации: автоматический allowlist не доказывает происхождение каждого файла.
Текущий inventory: 71 831 файл / 1 020 105 850 bytes без сжатия. Размер реального
архива ещё не измерен. 8,53 MB application artifact — другой объект.

| Критерий | Private GitHub Releases (`vsyntot/tacticum-platform`) | Private S3-compatible bucket |
|---|---|---|
| Что получает разработчик | Версионный Release asset + checksum, скачивание CLI | Объект по immutable key/version + checksum, скачивание CLI |
| Интеграция | Те же GitHub accounts/UI, отдельная ограниченная межрепозиторная авторизация | Bucket, IAM/service account, endpoint/region, retention и billing у выбранного провайдера |
| Контроль изменения | Включить immutable releases до публикации; pin digest независимо от режима | Уникальный content-addressed key; versioning/Object Lock при поддержке провайдера; pin digest |
| Отказ хранилища | CI и snapshots зависят от GitHub одновременно; сайты сами продолжают работать | Независимый provider уменьшает общий риск GitHub; CI всё равно требует доступа к bucket |
| Расходы | Release quotas отличаются от Actions artifacts; compute/runner usage оценивается отдельно | Storage, запросы, исходящий traffic, дополнительные версии и retention по тарифам провайдера |
| Эксплуатация | Меньше новых компонентов при текущем GitHub workflow | Выгоднее, если private S3 уже эксплуатируется и есть ответственный за доступ/backup |
| Резервирование | Нужна независимая копия для отказа/потери GitHub account | Нужен отдельный recovery scope/account; один bucket не равен гарантированному backup |

GitHub ограничивает один Release asset величиной **меньше 2 GiB**; на странице
документации не указан общий лимит размера release или bandwidth. Архив хранится
как asset, не коммитится в Git и не использует Git LFS. Не смешивать с Actions
artifacts и их сроками хранения. [Источник](https://docs.github.com/en/repositories/releasing-projects-on-github/about-releases).

Immutable releases блокируют изменение assets/tag; release целиком всё же
можно удалить. Следовательно, это защита публикации от изменения, но не
независимый архив восстановления. [Источник](https://docs.github.com/en/code-security/concepts/supply-chain-security/immutable-releases).

Обычный GITHUB_TOKEN ограничен репозиторием workflow. Для чтения private
platform repo потребуется отдельная GitHub App/ограниченная авторизация;
trusted build получает read-only доступ, публикация имеет отдельные полномочия.
Произвольным PR/fork jobs private core и секреты не передаются.
[Источник](https://docs.github.com/en/apps/creating-github-apps/authenticating-with-a-github-app/making-authenticated-api-requests-with-a-github-app-in-a-github-actions-workflow).

S3 — интерфейс, а не выбранный поставщик. Например, AWS Object Lock работает
вместе с versioning и защищает конкретные версии объектов; наличие и ограничения
аналогичного механизма у другого провайдера нужно проверять отдельно.
[Источник](https://docs.aws.amazon.com/AmazonS3/latest/userguide/object-lock.html).
Не включать бессрочное/длительное удержание без отдельного решения о retention.

**Рабочий цикл после настройки:** штатное обновление Битрикс на production →
стабильное окно и export/review → private snapshot и его digest → PR с core lock →
точный import локально и в trusted CI → проверки обоих сайтов. Application deploy
не перезаписывает production core. Пока snapshot/lock не соответствуют production,
поставка приложения останавливается. Откат lock не откатывает изменённую БД:
для восстановления production нужна согласованная пара core+DB и отдельный план.

**Масштаб/стоимость.** Иллюстрация, не замер: 10 snapshots по 1 GB дают 10 GB
хранения; 100 полных скачиваний такого snapshot дают около 100 GB traffic до
учёта кэша. Нельзя назвать сумму без фактического сжатия, частоты обновлений,
retention и выбранного S3 provider. Скорость тоже требует transfer benchmark;
сам тип storage не гарантирует более быструю сборку.

**Рекомендация:** при отсутствии уже обслуживаемого private S3 начать с private
GitHub Releases, immutable publication и pin по digest; отдельно сохранить
независимую recovery copy. Если private S3 уже доступен и обслуживается,
рассмотреть его как primary storage, не заводя второй сервис ради snapshots.
Изменение места хранения позже допускается с сохранением digest и проверенной
миграцией ссылок; приложение обоих сайтов от этого не разделяется.

### D2: исполнение после решения владельца

Источник разрешения: сообщение «1. дай здесь больше деталей, пока не могу
принять решение. 2. Да, сейчас.». Пункт 1 оставляет D1 pending; пункт 2 принимает
ранее предъявленный exact plan `7ff5ac7c9d71772f5968bb2ab7ae8330ed0fd8e7e69826da69d9894437c9fef0`.
Никакие дополнительные storage/history/visibility действия из этого не выводятся.

14.10.2026 09:55 UTC: operator exit 0, новый config digest совпал, services
восстановлены, шесть таблиц пусты. [Execution record](2026-10-14-crypto-key-rotation-execution.md).
Статус `applied_unverified`: 15-minute monitoring выполняется, admin relogin
ожидается. Это адресное устранение использования раскрытого ключа, не закрытие P0.

Прогноз хода: 10–20 минут работы / 20–35 минут календарно с 15 минутами
наблюдения; исполнитель Codex, граница — exact rotation и пояснение D1.
Общий план остаётся `partial_unknown` из-за storage, security backlog и
интеграционной среды. `version_source` — локальный SKILL.md project-manager,
`loaded_version=2.7.26`, повторно прочитан. Начало текущего хода 09:51:42 UTC;
`active_effort` и точное разделение `wait` пока unknown.


### Итог текущего хода — 14.10.2026 10:11 UTC

D1 остаётся pending: подробное сравнение GitHub Releases/S3 и рабочий цикл
приведены выше; storage не создан. D2 exact rotation выполнена, technical checks
passed: 252 HTTP checks за 15 минут, browser обоих сайтов, 453 manifest entries,
config digest/permissions/services и четыре error logs. Две private acceptance
copies совпадают по SHA-256 `c2c57cfb4029adc9d1263927335102a0c8d5e34b33d59c415898336cdf7e015b`.
Административный повторный вход пока не подтверждён; status `applied_unverified`,
verified BASE не продвинут. Следующий шаг этой части — подтверждение входа
владельцем и завершение STATEFUL acceptance. History cleanup, полный secret
inventory, P3/P6–P10 и remote CI delivery остаются отдельным незавершённым scope.

Failure ledger хода сохранён в execution record: локальный Python CA trust
обойдён использованием системного curl с TLS verification; отсутствующий
Playwright/offline cache и недоступные CUA browsers разрешены восстановлением
изолированной тестовой среды через uv, повторный browser replay passed.
Это проблемы локального инструментария; обнаруженного production дефекта нет.

`calendar_elapsed=19m20s` (09:51:42–10:11:02 UTC), `active_effort=unknown`,
чистый `wait=unknown`; 15-minute monitoring перекрывался с другими проверками
и подготовкой пояснения. Ближайший согласованный scope реализован, приёмка
ротации partial по admin gate; общий план `in_progress`, полный forecast
`partial_unknown`. Commit/push новых изменений в этом ходе не выполнялся.


### Уточнение владельца: GitLab и закрытие ротации — 14.10.2026

`version_source`: приложенный snapshot project-manager SKILL.md;
`loaded_version=2.7.26`. Parent остаётся `in_progress`.

- D2 rotation: владелец сообщил «в админ-панель зашел, все ок.». Оставшийся
  admin gate passed по owner confirmation. Config hash/metadata и private
  technical evidence повторно сверены под двумя locks; drift отсутствует.
  Две scoped `BASE-CRYPTO-STATEFUL.json` mode 0600 совпадают по SHA-256
  `41563fc01bd66bc0f40ee39eb33be75d59d1a703d03f41b5ed1c1413e75d18e9`. Ротация verified;
  common application BASE не заменён. Security P0 целиком остаётся открыт.
- D1 accepted constraint: владелец не хочет привязки к GitHub и планирует
  перенос проекта в отдельно развёрнутый GitLab. Предыдущее предложение
  private GitHub Releases superseded; такого storage не создавали.
- D1 proposal: независимый S3-compatible storage как основной вариант;
  GitLab Generic Package Registry как вариант без нового сервиса, если он
  уже включён и обслуживается. Выбор provider, endpoint, IAM/retention,
  расходование средств и создание инфраструктуры не считаются одобренными.
- Ограниченная проверка доступной конфигурации: релевантных S3/GitLab/MinIO/
  bucket параметров в именах `.env` и адреса GitLab в `docs/tools/.github`
  не найдено. Это не доказывает отсутствия инфраструктуры в организации;
  подключение к неизвестным системам `not_checked`. У владельца запрошены
  URL GitLab и наличие S3, без передачи секретов в чат.
- Read/write scope хода: текущие workflow/ADR docs и private rotation evidence;
  server write только append scoped BASE вне webroot по принятой ротации.
  Read-only current config metadata/digest; secret values не выводились.
  Миграция GitLab, создание registry/bucket, изменение приложения и новые
  credentials в этот ход не входят. Доступы к GitHub не расширяются.

**Предлагаемый план с учётом GitLab:**
1. Получить URL/версию GitLab и сведения о существующем S3; проверить registry,
   runners, storage limits и отдельное резервирование в разрешённых пределах.
2. Выбрать private storage по фактической инфраструктуре. Для S3 — endpoint,
   bucket и разделённые read/publish permissions; для GitLab Registry — package
   naming/versioning и job/deploy token scope. Не переносить личные SSH keys в CI.
3. Сохранить provider-neutral export/import/build/verify в кодовой базе. Lock
   фиксирует содержимое/platform identity; transport configuration и credentials
   задаются окружением. Не закладывать GitHub release/repository ID как identity.
4. Проверить реальный export, secret review, publication/download по digest и
   локальный import; затем trusted integration обоих сайтов по P2/P3.
5. При согласовании самого переноса перенести thin CI orchestration в GitLab,
   protected refs/environments, approval/evidence и delivery credentials.
   Перенос YAML не переносит branch protections, secrets, runners или approvals
   автоматически. Отдельно отключить старый write deploy trigger при cutover,
   чтобы два CI не поставляли одновременно; общий server lock сохраняется.
6. Проверить один согласованный release/no-op/recovery цикл из GitLab и
   независимость snapshot восстановления. Пока этот scope не принят и не
   проверен, GitLab delivery не объявляется работающим.

Прогноз текущего уточнения: 5–10 минут работы и календарно при готовых evidence.
Было: admin gate открытый; осталось: ротация закрыта, D1 требует конкретных
сведений об инфраструктуре. Полный CI/CD forecast — `partial_unknown`; время
переноса GitLab ещё не оценено без URL/версии/runners и принятого scope.

Контрольная точка 10:29:54 UTC: scoped dual BASE verified; 5 изменённых документов прошли проверку локальных ссылок и bounded secret guard, staged whitespace check passed. `calendar_elapsed=2m03s` от 10:27:51 UTC; `active_effort=unknown`, `wait=unknown` (раздельного учёта нет). Время меньше прогноза благодаря готовым technical evidence; новых operational probes с отказом не было. Ответ об URL GitLab/S3 пока ожидается, подключение и создание инфраструктуры не выполнялись.


### Scope revision: хранилища после GitLab — 14.10.2026

Источник: владелец «задачу с бэкапами и хранилищами позже решим (после переезда
в gitlab), давай пока остальное добьем». D1 и развёртывание нового backup/storage
явно deferred до переезда. Вопрос об endpoint закрыт как неактуальный сейчас.
Это не отказ от обязательного backup/restore rehearsal перед production writes.

Активный scope: общие команды проверки/сборки двух сайтов, CI без production
секретов, воспроизводимость из чистого Git commit, классификация и отрицательные
сценарии доставки, повторно используемый smoke, review-пакет и проверка CI.
P2 публикация snapshot, P3 real pinned runtime и зависимая production приёмка
P8/P9 сохраняют открытые зависимости; synthetic lock не становится настоящим.
Новая production automation не активируется без своего exact control-plane plan.

Исполнитель Codex; project-manager snapshot 2.7.26 + devops-engineer 1.1.1.
Ближайший пакет: 30–60 минут работы/календарно, исходя из готовых scope/build
fixtures и необходимости интеграции с CI. Общий остаток `partial_unknown`:
часть явно deferred, real platform integration зависит от будущего D1/P2/P3.
Чтение/запись: текущий repo tooling/workflows/docs; GitHub feature branch/PR/CI
для review входит в принятую работу по поставке. Production writes этого пакета
не разрешаются автоматически. Source secrets не публикуются; новые хранилища,
GitLab migration и history rewrite в текущий scope не включены.


### Реализация независимого пакета после отсрочки D1

- Feature commit `04226a76c605e8440cb1f564a6081f3f158d9e6e`, [PR #55](https://github.com/vsyntot/tacticum_web/pull/55).
- [Quality Gate 38550856809](https://github.com/vsyntot/tacticum_web/actions/runs/38550856809): 7/7 jobs passed, включая обе PHP версии, vhost boundaries, secret conventions, delivery fixtures и clean multisite build.
- Две чистые сборки source commit локально: 399 primary + 10 Divatech entries.
  В GitHub PR проверен merge commit `6c8f7137f83d01d05e2a8695e4996ec04c63d7ee`.
  Локальный Mac и Linux runner дали одинаковый archive SHA-256:
  `1367891e256e7e4494f3e31cb7df855d2686c11eeb1ff6f502fbd983a95de52a`, 8 533 889 bytes.
  Нормализованный synthetic lock служит только упаковке, не release identity.
- Portable `ci:delivery`, `ci:php`, `ci:multisite-build` больше не зависят от GitHub API;
  frontend пересобирается через `npm ci`/CSS generator в каждом новом checkout.
  Archive members/content сверены с manifest; temporary outputs удалены.
- Portable public smoke: 14 routes s1+s2 passed, включая host identity, PHP error
  markers, forms-off и security boundaries. Нет POST/lead/mail/admin-login действий.
- Дополнены негативные fixtures production hotfix/CONFLICT, nested environment
  paths и mixed control-plane changes. 449 PHP files + s1/s2 contracts passed.
- P6 candidate transaction kernel: реальные локальные create/replace/delete,
  journal до write, restore rehearsal, явный recovery нового экземпляра,
  отказ overwrite нового hotfix, защита corrupted backup/root rebinding,
  nonrecursive cleanup и CONVERGED без application writes. 10 tests passed.
  Это проверка ядра операций, не production controller и не общий release lock.
- Required checks main пока 3 старых; подготовлен адресный patch до 7 checks,
  strict и существующие app bindings сохраняются. Изменение остальных настроек
  protection не входит в patch. Negative merge enforcement ещё не тестировался.
- Failure ledger: попытка скачать CI receipt по feature SHA не нашла artifact:
  PR job именует его по merge SHA. После чтения artifact inventory скачан нужный
  receipt; hash comparison passed. Неверный lookup не менял код/production.

P4 имеет реальное clean-build evidence (packaging), P5 remote checks passed;
P6/P7/P8 получили частичные fixtures/portable smoke. Закрытие полного P4/P5/P6
по исходным acceptance зависит также от real platform integration, trusted
controller/finalizer и actual release. D1 deferred не отменяет эти критерии.


### GitHub control-plane cut: текущая активация CI

Accepted scope: завершение оставшейся CI работы по последнему решению владельца;
новые storage/backup systems deferred. Production server mutation не выполняется.

- Main protection: адресный patch required checks с 3 до 7 применён. Read-back
  подтвердил strict=true, сохранение всех прочих protection fields и старых
  app bindings. Before/after evidence в private release directory.
- В deploy workflow добавлен предварительный full-diff classifier **до**
  artifact/production access. Только FILE_ONLY допускается к старому read/plan
  контуру. Другие классы явно дают `separate_review_required`, downstream jobs
  пропускаются; успешный CI не объявляется verified release. Второй drift gate
  перед apply остаётся строгим и выдаёт failure при конфликте.
- Для текущего diff классификация BLOCKED_FORBIDDEN из-за удаления environment
  config из Git (оно отдельно разрешено secret guard); также есть control-plane
  changes. Следовательно, после merge этого пакета application apply не должен
  запускаться. Это ожидаемый controlled stop, не ошибка качества сборки.
- Full-diff начинается с прежнего verified baseline. Следующие app releases
  тоже требуют reviewed adoption нового baseline/controller, пока между baseline
  и HEAD остаются control-plane/platform changes. Обход baseline не добавляется.
- Merge данного CI/tooling пакета не является установкой production controller,
  выдачей новых prod credentials либо выполнением P9. После CI требуется
  проверить фактический main run и отсутствие production inspect/apply jobs.


### Проверка merge и возврат в общий план — 14.10.2026

[PR #55](https://github.com/vsyntot/tacticum_web/pull/55) смержен 10:48:02 UTC,
commit `4c88c6318fc04f0f2c9cc1de891e5961640a6010`. Последний PR Quality Gate
38551914978 и [main run 38552094300](https://github.com/vsyntot/tacticum_web/actions/runs/38552094300)
успешны: все 7 quality jobs passed. Classifier passed; artifact, production
inspect, reconcile, apply и post-deploy smoke skipped. Sitemap run 38552094207
passed. Локальный повтор classifier на merge commit дал BLOCKED_FORBIDDEN /
separate_review_required; фактический main skip согласуется с этим решением.

Итог независимого пакета: merged_and_ci_verified. Общий план остаётся
in_progress. [Структурированный evidence](2026-10-14-multisite-ci-evidence.json)
отделяет CI, packaging и candidate recovery от production acceptance.

Git history audit: 46 713 уникальных blobs, 825 868 824 bytes, 0 пропущенных
по лимиту 16 MiB объектов, 4 известных reviewed fixture совпадения. Найдены
3 исторических blob `bitrix/.settings.php` с literal_crypto_key. Значения
секретов не выводились и не записывались в evidence. Это ограниченная проверка
известных шаблонов, не полная инвентаризация credentials; история не переписана.
Текущий config отсутствует в Git-дереве и сохранён локально. Подтверждённая
ротация production crypto_key не закрывает весь MSCI-01.

Остаток и зависимости:
- D1/new storage и backup infrastructure: deferred по решению владельца.
- P2/P3: настоящий pinned snapshot/runtime, публикация и full integration
  требуют возвращения к отложенной архитектуре; synthetic fixture их не заменяет.
- P6/P7/P9: candidate kernel готов к дальнейшей интеграции, но trusted wrapper,
  общий release lock, cache policy, независимый finalizer и реальный двухсайтовый
  release/recovery остаются открыты. Их активация требует отдельного exact
  control-plane plan с реальной platform identity и evidence stores.
- Автоматическая поставка будущих app changes пока удерживается full-diff gate:
  прежний verified baseline нельзя молча продвинуть поверх control-plane changes.
- MSCI-01: отдельный разбор исторических credentials и history remediation.

Следующее действие после возвращения к инфраструктуре: выбрать независимое от
Git-провайдера место snapshot/evidence, закончить P2/P3, затем связать и проверить
P6/P7/P9. Обязательные release backups/restore rehearsal действуют и сейчас.

Контрольная точка 10:53 UTC: calendar_elapsed около 21 минуты от первого
зафиксированного clock 10:32:06 UTC; active_effort=unknown, wait=unknown,
раздельный учёт не вёлся. Прогноз был 30–60 минут работы/календарно; осталось
5–10 минут работы/календарно на docs review/CI/merge. Общий остаток
partial_unknown из-за явно отложенных зависимостей. Новых production writes
в этом CI пакете не было; единственный неуспешный artifact lookup описан выше.

### Контрольная точка: fixture P6/P7 controller/finalizer — 14.10.2026

В текущем checkout добавлены `tools/multisite-trusted-controller.py` и
`tools/multisite-release-finalizer.py`. Первый принимает только явно связанный
plan/approval/manifest/artifact, проверяет canonical mappings двух корней,
platform/scope identity, lock, disk/inode preflight, safe archive extraction,
backup/restore rehearsal и явные create/replace/delete/rename operations.
Журнал сохраняет `prepared`/`applying`/`applied_unverified` либо
`failed_requires_recovery`; recovery проверяет drift и возвращает изменённые
файлы/созданные каталоги. Второй принимает только smoke/monitoring/cache-clear
evidence, делает CAS по предыдущему BASE и active release, требует монотонную
последовательность и пишет две совпадающие private BASE copies; partial write
имеет адресный recovery.

`python3 tools/multisite-trusted-controller-self-test.py` прошёл 8 изолированных
сценариев, включая safe extract/symlink denial, stale PROD, busy lock,
create/replace/delete/rename, failure between roots, restore, smoke denial,
dual BASE, CAS и partial-promotion recovery, включая recovery второй promotion
при уже существующем BASE. Тест включён в `ci:delivery` и не
меняет число обязательных CI jobs. Это закрывает локальную проверяемую часть
P6/P7; `productionReady` не менялся, SSH/production transport, storage,
cache/OPcache hooks, real platform integration и production BASE остаются вне
scope. Следующий gate — отдельный exact control-plane plan и P2/P3 evidence.

### Merge и main verification fixture P6/P7 — 14.10.2026

Пакет смержен через [PR #57](https://github.com/vsyntot/tacticum_web/pull/57),
merge commit `55509cd6646811c282bbfd2f8680b3b1cfbf035f`. [Main run
38574979561](https://github.com/vsyntot/tacticum_web/actions/runs/38574979561)
завершён `success`: все 7 quality jobs и classifier прошли; artifact,
production inspect/reconcile/apply и post-deploy smoke имеют ожидаемый
`skipped`. Это подтверждает fixture/control-plane пакет, но не production
release. Локальный `main` синхронизирован с `origin/main`, рабочее дерево
чистое, локальный `.settings.php` сохранён.

Failure ledger: первая попытка merge использовала сокращённый SHA и была
отклонена GitHub API; повтор с полным SHA прошёл. Код, production и настройки
branch protection не изменились из-за неудачной попытки.
