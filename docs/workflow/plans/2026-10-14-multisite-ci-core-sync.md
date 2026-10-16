# План: единая разработка, сборка и поставка Tacticum и Divatech

Дата: 14.10.2026. Редакция: 2 — реализация.
Статус документа: подготовлен для самостоятельного чтения и принятия решений.
Статус реализации: `in_progress`; начаты P0/P1/P4/P5, P2 помечен `deferred_external`, остальные этапы не приняты.
Workflow lane: `Security / Integration`.

### Текущий этап — 16.10.2026, подготовка двухкорневого релиза в CI

- Owner: Codex / project-manager; input: `main=9bde0abd9316c5caaf2ab0a7426fc00c1d7c2656`, чистое дерево, PR #75 смержен.
- `version_source`: `/Users/ivanmonakhov/.codex/skills/project-manager/SKILL.md` и прочитанный snapshot пользователя; `loaded_version`: `2.7.28`.
- Accepted scope: продолжение принятого CI/build follow-up; локальные tooling/workflow/docs, PR и read-only production inspection. GitLab и выбор хранилищ исключены. Серверные BASE, application/config/DB этим этапом не меняются.
- Output: clean build обоих корней в CI, canonical validation production manifest, immutable candidate и точный two-way review без права apply/promotion.
- Acceptance: `/privacy/` входит в сборку; повреждённые/однокорневые manifests и несовместимая platform identity отклоняются; production credentials доступны только manifest job; build/review выполняются без них; CI run сохраняет результат, `productionMutationAllowed=false` при любом совпадении.
- Next gate: three-way reconciliation с текущим dual BASE и exact plan; two-way report не подменяет BASE и не разрешает deployment. Legacy deploy остаётся закрыт прежним guard.
- Forecast: сверка 10–15 мин работы/календарно; ближайшая реализация 30–60 мин работы, 40–90 мин календарно; весь остаток `partial_unknown` до сверки устаревших этапов с evidence. Основание: существующие multisite builder/transport и время предыдущих CI runs.
- Уточнение evidence: проверка clean committed HEAD после PR #75 включает `primary=399`, `divatech=11`. Прежние 399+10 были результатом сборки предыдущего коммита, не дефектом scope.

Issue: не создан. Gap IDs: `MSCI-01` — `MSCI-10`.
Координатор подготовки плана: Codex / project-manager. Исполнитель реализации: Codex; ответственные роли указаны по действующему governance.
`version_source`: `/Users/ivanmonakhov/.codex/skills/project-manager/SKILL.md` и приложенный пользователем snapshot; `loaded_version`: `2.7.27` (прочитан при продолжении исполнения).

## 1. Цель, полномочия и границы

Обеспечить разработку обоих сайтов из текущего репозитория, воспроизводимую сборку, доставку общего кода и двух публичных корней, а также контролируемую подготовку переноса штатных обновлений Битрикс в локальное окружение.

Пользователь принял исполнение плана сообщением «$project-manager погнали в реализацию». Авторизация покрывает локальные изменения, проверки и подготовку reviewable production plans. Вопросы репозитория Git-провайдера, snapshot/BASE/evidence storage и backup-хранилища явно вынесены владельцем в отдельную будущую тему и не являются решениями этого плана.

- `accepted_scope`: кодовая база, CI, сборка двух сайтов, fixture-контроллер/финалайзер и подготовительная документация; production activation не входит.
- `artifact_recorded_scope`: разделы 5–10 в части локальных и CI-проверок; production gates не превращаются в одобрение по факту записи.
- `read_scope`: репозиторий, инструкции, проектные credentials в памяти, read-only GitHub/production inspection и агрегаты без PII.
- `write_scope`: проектный tooling/CI/docs и Git index в ветке `feat/multisite-delivery-core-sync`; подготовительные private release files вне webroot сервера и локального Git.
- Off-limits без отдельного адресного решения: live production application/config/DB, Git provider migration/history/settings, создание или выбор storage. Vendor code вручную не редактируется; исключение секретного config из index не удаляет рабочий файл.
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

Текущая модель: репозиторий хранит код обоих сайтов, а fixture tooling фиксирует контракты сборки и доставки. Реальный vendor snapshot и `core.lock.json` подключаются только после принятия отдельной инфраструктурной темы; этот документ не выбирает место хранения, стоимость или права доступа.

| Решение | Рекомендация | Когда необходимо | Статус |
|---|---|---|---|
| D1: внешняя инфраструктурная тема | Репозиторий Git-провайдер, snapshot/BASE/evidence/backup storage и их миграция | Отдельная будущая задача владельца | out_of_scope_current_plan |
| D2: устранение раскрытия | Проверить область применения, подготовить ротацию и очистку Git; изменение видимости — возможное временное ограничение доступа, не устранение уже сделанных копий | Для внешних изменений P0 | exact crypto rotation verified; history/visibility не согласованы |
| D3: модель доверенного CI и BASE | Недоверенный PR без private credentials; доверенная интеграция отдельно; operational BASE и независимая долговечная копия с ограниченными правами | До выдачи доступов P5/P7 | proposed; Architect/DevOps готовят конкретную схему |
| D4: первый production выпуск | Bounded план установки контроллера и отдельный application release с проверкой обоих сайтов | P9 | proposed; ранее принятый no-stage подход сохраняется |

Роли из governance: PM — scope/решения, Architect — contracts/trust boundaries, DevOps — снимки/сборка/доставка/восстановление, Dev — реализация, QA — раннее review и приёмка. Именные назначения и дополнительные расходы не утверждены. При продолжении сначала проверить уже принятые решения; повторно их не запрашивать.

## 4. Порядок, зависимости и оценка

Каждый этап завершается своим проверяемым результатом. Работа над безопасным локальным кодом и fixtures может продолжаться независимо от ожидания конкретного production approval. Публикация новых снимков и production writes не обходят незакрытые security/compatibility gates.

| Этап | Результат | depends_on | Роль | Статус | Предварительная работа / календарное время без ожидания |
|---|---|---|---|---|---|
| P0 | Устранение раскрытия и защита от повторения | — | Security reviewer + DevOps, PM | in_progress | Подготовка 2–4 ч / 2–4 ч; полный срок partial_unknown |
| P1 | Согласованные contracts и карта владения | — | Architect + PM + QA | partial | 2–4 ч / 2–4 ч |
| P2 | Проверяемый импорт платформы и core lock | P0, P1; external infrastructure topic | DevOps + Dev | deferred_external | 4–8 ч / 4–8 ч |
| P3 | Изолированная среда обоих сайтов | P2 | Dev + QA | todo | 4–8 ч / 4–8 ч |
| P4 | Одна воспроизводимая сборка двух корней | P1 | DevOps + Dev | partial | 3–6 ч / 3–6 ч |
| P5 | Классификация и обязательные CI gates | P1, P4; D3 | DevOps + QA | partial | 3–6 ч / 3–6 ч |
| P6 | Многокорневой контроллер и восстановление | P1, P4 | DevOps + Architect + QA | partial | 6–12 ч / 6–12 ч |
| P7 | Smoke, наблюдение и финализация BASE | P3, P5, P6; D3 | QA + DevOps | partial | 3–6 ч / 3–6 ч |
| P8 | Сквозная репетиция обновления и доставки | P2, P3, P7 | QA + DevOps | todo | 3–6 ч / 3–6 ч |
| P9 | Проверенный первый production цикл | P0–P8; D4 | DevOps + QA, владелец approval | todo | 2–4 ч / 2–4 ч плюс approval и наблюдение |
| P10 | Эксплуатационная передача | P9 | PM + DevOps + QA | todo | 1–2 ч / не менее 24 ч с контрольной проверкой |

Основание оценок: один технический исполнитель, расширение существующих manifest/controller/fixtures, две текущие цели доставки, отсутствие смены хостинга и дизайна. Это оценки планирования, не бюджет и не обещание срока. Известная подготовка текущего scope оценивается отдельно; P2/P3 и production activation остаются `deferred_external` до отдельной инфраструктурной темы и принятого exact plan.

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
5. Определить правила core import, app drift reconciliation, совместимости схемы БД и platform lock. Внешнюю инфраструктурную тему здесь не выбирать; D3 и production trust оформлять отдельным accepted plan.

Выход: принятые схемы scope/release/core lock и threat model; каждый путь имеет владельца. Приёмка: однозначное сопоставление обоих корней, общих файлов и исключений, отсутствие symlink traversal и дублирующей доставки `local/` через s2.

### P2. Сделать импорт платформы из production

1. Инвентаризировать vendor code, штатные и Marketplace-модули, версии и обнаруженные custom files под `bitrix/`. Не считать весь каталог однородным ядром; неожиданные кастомизации вынести на решение.
2. Реализовать отдельный allowlisted read/export механизм с минимальными правами. Действующий forced-command `manifest` не расширять до произвольного shell. Root/password helper не превращать в штатный CI transport.
3. Создавать snapshot в стабильном окне после штатного обновления: проверить завершение updater и согласованность до/после чтения; при изменении входов прерывать импорт. Архив исключает secrets/license, backups, runtime, данные пользователей и `.git`.
4. Проверять полный manifest содержимого, digest архива, size/type/mode, пути, дубликаты, ссылки и отсутствие запрещённых данных. Сверка только размера архива не является проверкой содержимого.
5. После принятия отдельной инфраструктурной темы сохранить immutable snapshot во внешнем контуре, который будет определён там. `core.lock.json` содержит snapshot ID/digest, vendor manifest digest, module versions, профиль PHP/extensions/DB, schema compatibility ID, provenance и версию export rules. Секретов и DB dumps в lock нет.
6. Подготовить импорт в отдельной ветке/каталоге после принятия внешнего platform contract. Не удалять production core; заменить тотальный CI запрет на разрешённый metadata import workflow и запрет ручных vendor patches.
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
| CI/server settings | Только отдельные reviewed изменения protections, credentials и wrappers; миграция Git-провайдера/хранилища не входит |

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
| Отсутствие принятого platform snapshot/BASE | Остановить platform import и production activation; не подменять его synthetic fixture или последней серверной копией |
| Partial apply/неуспешный smoke | Journal, stop, согласованный адресный rollback; новый verified BASE не создаётся |
| CI green без проверки реальной платформы | Разделять unit/config fixtures и обязательный trusted integration; фиксировать проверенную platform identity |
| Простой двух сайтов из-за общего ядра/БД | Общий update/recovery plan и smoke обоих; многосайтовость не является независимой отказоустойчивостью |

RTO/RPO и retention для production остаются governance prerequisites будущего production-плана. Этот документ их не выбирает и не превращает в текущую задачу. До любого будущего production write обязан быть конкретный rollback plan для каждого scope, включая controller/config; application backup не заменяет STATEFUL backup.

## 10. Definition of Done реализации и следующий шаг

Текущий план считается выполненным, когда кодовая база обоих сайтов собирается из чистого checkout, обязательные CI-проверки и отрицательные сценарии проходят, fixture-контроллер/финалайзер имеют проверяемый recovery-контур, а документация и acceptance отражают фактические ограничения. Production installation, platform import и первый production release требуют отдельного exact plan и не закрываются локальными тестами.

Внешняя инфраструктурная тема владельца (выбор Git-провайдера, миграция репозитория, snapshot/BASE/evidence/backup storage и связанная история) в этот план не входит. Здесь нет задач, сроков, provider comparison или acceptance по этой теме; она будет заведена отдельно.

## 11. Проверка документа и текущий статус

План отделяет текущие инженерные результаты от будущей production активации. На `main` уже смержены и проверены:

- PR #55 — единый CI/build пакет, 7 required checks, clean multisite build и classifier;
- PR #57 — fixture-only trusted controller/finalizer и recovery self-tests;
- PR #58 — документационная и verification-сверка;
- PR #59 — отдельный adoption plan для дальнейшего перехода к production-контроллеру.

Последний main run `38574979561` завершён `success`: семь quality jobs и classifier прошли, production artifact/inspect/reconcile/apply/smoke были ожидаемо пропущены. Локально проходят `npm run ci:delivery`, `npm run ci:php`, `npm run ci:multisite-build` и `python3 tools/multisite-trusted-controller-self-test.py`. Clean build подтверждён для 399 primary и 10 Divatech entries; fixture archive и manifest сверяются, временные результаты удаляются.

Текущий результат: `in_progress`. Закрыта локальная проверяемая часть CI и fixture-контуров. P0-аудит зафиксирован в [redacted evidence](../evidence/2026-10-14-p0-security-audit.json): indexed scan и crypto tests passed, но bounded history check нашёл 3 historical crypto-key blobs; полная credential inventory и remediation не выполнены. P1 contract evidence зафиксирован в [P1 evidence](../evidence/2026-10-14-p1-contract-evidence.json): новый multisite scope `2026-10-14.multisite.1` расходится с legacy production scope `2026-10-11.1`, поэтому P1 остаётся partial. P4/P5 evidence зафиксирован в [CI evidence](../evidence/2026-10-14-p4-p5-ci-evidence.json): clean build, 449 PHP files и 7 required checks passed; техническая часть текущего scope partial до P2/P3 integration. P6/P7 evidence зафиксирован в [controller evidence](../evidence/2026-10-14-p6-p7-controller-evidence.json): 8 fixture scenarios passed, но productionReady=false и real transport не подключён. Открыты P2/P3 real platform/runtime integration, production wrapper installation, real two-root release/recovery и P10 operational handoff. Эти пункты не объявляются закрытыми по зелёному CI.

Failure ledger P0: all-object history scan остановлен после превышения безопасного окна из-за неэффективного построчного IPC; код, Git history и production не изменялись. Повтор выполнен в bounded-режиме по секретным путям и names, поэтому результат остаётся `partial`, а не `passed`.

## 12. Граница следующего шага

До принятия отдельной инфраструктурной темы продолжается только текущий scope: поддержка общей кодовой базы, CI, clean build, fixture-контроллера/финалайзера, документации и read-only проверок. Production mutation не выполняется.

Когда владелец откроет отдельную тему инфраструктуры, её решения должны быть заново сопоставлены с P2/P3/P6/P7/P8/P9, production governance и ADR-016. Существующий fixture evidence можно использовать как входные данные, но он не является разрешением на production release.

### Контрольная точка: fixture P6/P7 controller/finalizer — 14.10.2026

`tools/multisite-trusted-controller.py` и `tools/multisite-release-finalizer.py` принимают только связанные plan/approval/manifest/artifact, проверяют canonical mappings двух корней, lock, disk/inode preflight, safe extraction, backup/restore rehearsal и явные file operations. Журнал сохраняет состояния `prepared`/`applying`/`applied_unverified` либо `failed_requires_recovery`; recovery проверяет drift и восстанавливает затронутые файлы. Finalizer принимает только smoke/monitoring/cache evidence, делает CAS по предыдущему BASE и требует монотонную последовательность.

`python3 tools/multisite-trusted-controller-self-test.py` прошёл 8 изолированных сценариев, включая safe extract/symlink denial, stale PROD, busy lock, create/replace/delete/rename, failure between roots, restore, smoke denial, dual BASE, CAS и partial-promotion recovery. Это локальная проверка transaction kernel; productionReady не менялся, production transport и real platform integration не подключены.

### Merge и main verification

Пакет controller/finalizer смержен через PR #57, merge commit `55509cd6646811c282bbfd2f8680b3b1cfbf035f`. Main run `38574979561` завершён `success`; production jobs имеют ожидаемый `skipped`. Локальный `main` синхронизирован с `origin/main`, рабочее дерево чистое, локальный `.settings.php` сохранён.

Следующий reviewable результат — поддерживать этот CI/fixture baseline и подготовить отдельный accepted plan для real platform/runtime и production activation, когда внешняя инфраструктурная тема будет открыта владельцем.

### Продолжение реализации: encrypted transport — 16.10.2026

Первый подэтап принятого follow-up выполнен частично. Production control-plane
на сервере уже был установлен и его pinned hashes сохранены; локальные
`ci:delivery`, `ci:php`, `ci:multisite-build` и production control self-test
прошли. Локальный readonly key из прежнего access contract не совпадал с
установленным forced-command entry, поэтому создан отдельный набор
passphrase-protected CI keys для manifest/write/finalizer. На сервер добавлены
только публичные ключи с `restrict` и соответствующими forced commands, с
backup/restore rehearsal. Repository secrets созданы без раскрытия значений.

Commit `d397e821f` добавляет загрузку encrypted keys через `ssh-agent` в deploy
workflow и отдельный read-only workflow
`.github/workflows/production-transport-smoke.yml`. GitHub push пока не прошёл:
branch protection требует PR и OAuth token требует `workflow` scope. До
прохождения device authorization transport smoke остаётся `pending`; никаких
новых production application writes этим шагом не выполнялось.

### Контрольная точка: read-only multisite release review — 16.10.2026

PR #76 (`ec1b2226e`) добавил ручной review-only workflow `multisite-release-review.yml`.
Run `38897349988` прошёл все семь quality jobs, encrypted manifest transport и
clean build из `main`; production mutation и BASE promotion не выполнялись.
Оба корня собраны полностью: `primary=399`, `divatech=11`, включая
`sites/divatech/privacy/index.php`.

Двухсторонняя сверка PROD↔CANDIDATE обнаружила два известных primary drift-файла:
`local/templates/tacticum/components/bitrix/menu/bottommenu/.description.php` и
`local/templates/tacticum/components/bitrix/menu/topmenu/.description.php`. Они
совпадают с двумя файлами ранее принятого FILE_ONLY-релиза 15.10.2026; новый
контур правильно оставил `base_status=NOT_LOADED`,
`productionMutationAllowed=false` и `baselinePromotionAllowed=false`. Это не
разрешение на повторную доставку и не promotion BASE. Evidence:
[evidence](../evidence/2026-10-16-multisite-release-review.json).

Следующий gate: exact three-way `BASE/PROD/CANDIDATE` plan с решением владельца
по этим двум известным различиям, затем отдельная проверка cache/monitoring и
только после этого — P9/P10.

### Контрольная точка: encrypted transport и fail-closed deploy guard — 16.10.2026

Подэтап P5/P7 по transport завершён в статусе `verified_partial`. PR #66
внёс поддержку passphrase-protected SSH keys в deploy workflow; PR #70
добавил обязательную проверку schema v2, scope `2026-10-14.multisite.1` и
наличие обоих корней в read-only smoke. PR #71 сделал классификацию deploy
fail-closed: недоступный исторический baseline теперь оставляет job зелёным
только как `manual_review_required`, с `inspect=false`; production inspection и
mutation пропускаются.

GitHub Environment secrets `production-readonly` и `production` обновлены на
root forced-command transport с pinned known-hosts и зашифрованными ключами.
Run `38892340827` подтвердил read-only production manifest: 410 entries,
`primary=399`, `divatech=11`, manifest
`02161da788f219083a5f29bf44bcdec2626dba0dcf7a80230a9e439cab1ed7f`.
Run `38892899943` прошёл с `manual_review_required`; application mutation не
выполнялась. Полное evidence сохранено в
`docs/workflow/evidence/2026-10-16-multisite-transport.json`.

Остаются P2/P3 platform snapshot и real runtime integration, P6/P7
production apply/recovery/finalizer, P8 end-to-end rehearsal, P9 exact plan
и P10 handoff. GitLab, snapshot/BASE/evidence/backup storage и история
репозитория по-прежнему исключены владельцем из текущего плана.
