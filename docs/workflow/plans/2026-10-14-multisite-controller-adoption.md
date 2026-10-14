# Codex Plan: Adoption trusted multisite controller

Issue: не создан
Gap ID: `MSCI-04`, `MSCI-05`, `MSCI-07`, `MSCI-08`
Workflow lane: `Security / Integration`
Owner agent: Codex / project-manager
Date: 14.10.2026
Status: `prepared`, production execution not authorized

## Goal

Подготовить проверяемый путь перехода от fixture-only controller/finalizer к
доверенному FILE_ONLY контуру для `/home/bitrix/www` и
`/home/bitrix/ext_www/divatech.space`, сохранив общий код, явные операции,
recovery и двухсайтовую проверку.

## Non-Goals

- не устанавливать controller на production;
- не выполнять SSH, rsync, backup, cache clear или application apply;
- не импортировать настоящий `bitrix/` до решения D1 и pinned platform lock;
- не выбирать provider для snapshot/BASE storage до GitLab migration;
- не менять БД, инфоблоки, конфигурацию, vhost или Divatech forms;
- не считать fixture evidence production acceptance.

## Context Read

- [x] `AGENTS.md`
- [x] `docs/workflow/current-state.md`
- [x] `docs/workflow/gap-analysis.md`
- [x] `docs/workflow/production-deployment-governance.md`
- [x] `docs/adr/ADR-013-github-actions-delivery-gates.md`
- [x] `docs/adr/ADR-015-divatech-multisite.md`
- [x] `docs/adr/ADR-016-multisite-platform-delivery.md`
- [x] `tools/multisite-scope.json`
- [x] `tools/multisite-trusted-controller.py`
- [x] `tools/multisite-release-finalizer.py`

## Current Behavior

- PR #55/#56 установили семь required checks и раннюю классификацию release.
- PR #57 добавил fixture-only controller/finalizer; main run
  `38574979561` прошёл, production jobs были пропущены.
- Candidate controller принимает два canonical roots, explicit operations,
  archive/manifest bindings, lock, disk checks, backup/restore rehearsal и
  journal recovery. Finalizer принимает только smoke/monitoring/cache evidence.
- `tools/multisite-scope.json` сохраняет `productionReady=false`.
- Реальный Bitrix snapshot, production transport, independent BASE domains и
  cache/OPcache hooks не подключены.

## Preconditions / gates

| Gate | Required evidence | Current status |
|---|---|---|
| D1 storage | Независимое закрытое место для core snapshots, BASE, release evidence и backups; retention/access | `deferred` до GitLab |
| P0 security | Bounded history/credential inventory, required rotations, no new secret findings | `in_progress` |
| P1 contract | Accepted scope/core/schema identity и owner mapping | `partial` |
| P2 platform | Production export/import, full manifest, digest, module versions, schema compatibility | `blocked_by D1` |
| P3 runtime | Clean bootstrap двух сайтов на pinned Bitrix/PHP/DB profile, no real integrations | `todo` |
| D3 trust | Раздельные read-only/write credentials, forced-command wrappers, known-hosts and denial tests | `proposed` |
| D4 approval | Exact owner approval of controller installation and first release | `not_requested` |

Любой `blocked`, `unknown` или `not_run` gate останавливает переход к
production. Отсутствие stage-хоста покрывается только отдельным bounded waiver;
оно не отменяет backup/restore rehearsal и post-deploy verification.

## Planned Changes

| Area | Change |
|---|---|
| Scope | Зафиксировать mapping `primary → /home/bitrix/www`, `divatech → /home/bitrix/ext_www/divatech.space`; shared `local/` доставляется только через primary |
| Platform | После D1 создать immutable `core.lock.json` с snapshot/vendor/schema/PHP identity; исключить secrets, uploads, DB и runtime |
| Transport | Установить только forced-command wrappers; PR jobs не получают private data; personal key не используется штатным CI |
| Controller | Развернуть точную версию controller отдельно от candidate; проверять canonical paths, owner, symlink boundaries, disk/inodes, artifact and plan digests |
| Reconciliation | Read-only `BASE ↔ PROD ↔ CANDIDATE`; unknown/conflict/stateful/control-plane → stop; no broad `rsync --delete` |
| Backup | До apply создать полный managed-scope backup, выполнить restore rehearsal в private directory и сохранить redacted evidence |
| Apply | Только explicit create/replace/delete/rename в пределах approved plan; общий server lock от final manifest до post-manifest |
| Verification | После credential teardown выполнить Tacticum + Divatech smoke, SEO/security routes, cache policy and monitoring window |
| Finalizer | При успешной проверке записать две совпадающие BASE copies с commit/artifact/plan/controller/schema binding; CAS и monotonic sequence обязательны |
| Recovery | При partial/failure сохранить journal, остановить apply, проверить foreign drift, адресно восстановить managed files и не продвигать BASE |

## Exact first release sequence

1. Зафиксировать выбранные storage/evidence endpoints и access policy после
   GitLab migration; проверить, что две BASE copies находятся в независимых
   trust domains.
2. Снять production platform inventory после завершения штатного Bitrix updater;
   повторить inventory до публикации snapshot и остановиться при drift.
3. Выполнить P2 export/import в isolated checkout и собрать `core.lock.json`.
4. Поднять P3 runtime с двумя document roots, synthetic DB/mail sink и
   отключёнными cron, webhooks, AI и внешними платными вызовами.
5. Собрать immutable candidate из clean commit; проверить manifest/artifact
   digests и `FILE_ONLY` classification.
6. Получить read-only PROD manifest, выполнить reconciliation и подготовить
   exact plan с перечисленными deletions/tombstones.
7. Получить owner approval, зафиксировать expiry, plan/artifact/PROD hashes и
   staging evidence либо bounded no-stage waiver.
8. В maintenance window получить lock, повторить final PROD manifest, пройти
   disk/inode checks, backup и restore rehearsal.
9. Применить candidate explicit operations к обоим roots; сохранить journal и
   post-manifest.
10. Удалить write credentials, выполнить smoke/monitoring/cache checks. При
    любом stop condition восстановить managed scope по approved recovery plan.
11. Только при полном success вызвать finalizer; проверить обе BASE copies и
    повторный no-op без application write.

## Stop conditions

- отсутствует D1 snapshot/BASE/evidence storage;
- platform/schema identity не совпадает с lock;
- PROD drift, unknown path, forbidden path или stale approval;
- изменяется `.settings.php`, БД, schema, iblock/data или runtime-owned path;
- нет staging evidence/approved waiver;
- не получен lock, не прошли disk/inode/backup/restore checks;
- post-manifest, smoke, monitoring или две BASE copies не совпали;
- controller/finalizer journal повреждён или обнаружен foreign drift.

## Verification

### Automated

```bash
npm run ci:delivery
npm run ci:php
npm run ci:multisite-build
python3 tools/multisite-trusted-controller-self-test.py
```

Fixture acceptance сейчас: 8 controller/finalizer сценариев, 10 transaction
recovery tests, PHP 449 files, clean build 399+10. Эти проверки не доказывают
P2/P3/P8/P9.

### Production acceptance (future only)

- read-only manifest обеих roots до/после;
- exact plan/approval and release class;
- backup/restore rehearsal evidence;
- public/security/SEO smoke обеих sites;
- monitoring window and stop thresholds;
- две независимые совпадающие BASE copies;
- повторный no-op без application writes.

## Rollback

До первого write сохраняется managed-scope backup. При failure controller
останавливается, journal остаётся `failed_requires_recovery`, BASE не
продвигается. Recovery восстанавливает только затронутые файлы и удаляет
созданные controller directories/files; production core, DB, uploads и
server-owned config не затрагиваются. Для STATEFUL изменений применяется
отдельный migration rollback plan.

## Next action

После переноса в GitLab владелец выбирает D1 storage и принимает exact
control-plane plan. До этого разрешены только fixture/CI checks и read-only
production probes; production controller и первый release остаются
`not_started`.

## Docs To Update

- [ ] `docs/workflow/gap-analysis.md` после каждого закрытого gate
- [ ] `docs/workflow/current-state.md` после merge/release evidence
- [ ] `docs/adr/ADR-016-multisite-platform-delivery.md` после принятия D1/D3/D4
