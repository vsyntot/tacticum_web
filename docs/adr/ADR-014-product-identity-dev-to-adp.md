# ADR-014: Product identity migration from Dev to ADP

Статус: принято; production FILE_ONLY и STATEFUL migration выполнены 2026-10-11
Дата: 2026-10-11

## Контекст

Публичный продукт `Tacticum Dev` должен получить имя `ADP` и канонический путь
`/adp/`. Текущий Bitrix product record, связанные блоки/use cases/FAQ, формы,
аналитика и локальный runtime используют `dev`. Переименование затрагивает
файлы, публичные URL и данные инфоблоков, поэтому состоит из FILE_ONLY и
отдельной STATEFUL-фазы.

## Решение

1. Канонический публичный и активный внутренний код — `adp`; новая форма —
   `adp-cta`.
2. `/dev/` остаётся постоянным query-preserving HTTP 301 на `/adp/`; старый
   `dev-cta` принимается только как ограниченный compatibility alias и не
   используется для новых записей.
3. Позиционирование, claims и proof не переписываются в рамках этого ADR.
4. Bitrix migration сохраняет element IDs, XML_ID и исторические значения в
   CRM/BI, но переводит активные product/block/use-case/FAQ code и связи на
   `adp`. Independent `adp` collision блокирует применение.
5. Local runtime использует единый `ProductIdentity` и временный legacy-read
   mapping `dev → adp`. Production apply допускается только после exact plan,
   backup/restore rehearsal, staging evidence или bounded owner waiver, lock и
   post-deploy verification. Для этого проекта staging waiver зафиксирован
   владельцем: stage-хоста нет, поэтому smoke выполнен непосредственно в
   production.

## Последствия

- Sitemap, canonical, menus, forms, analytics context and generated checks use
  `/adp/` and `adp`.
- Historical `dev` data remains queryable through an explicit reporting/legacy
  mapping; it is not rewritten blindly.
- The migration CLI is dry-run by default. Production apply on 2026-10-11
  preserved IDs/XML_IDs and completed 62 primary product changes, 7 page-content
  changes and 3 cross-product Platform block changes. The final idempotent
  dry-run reports zero changes.
- The release backup/restore rehearsal passed: the root-only DB/files backup
  remains at `/root/tacticum-adp-20261011131939`, and the temporary restore DB
  with 361 tables was removed after verification.
- A previously exposed DB credential was rotated and the active Bitrix DB
  connection was revalidated; the value is intentionally not recorded here.
- Post-deploy rendered, public-precheck, product-source, SEO, visual and
  browser/action smoke checks passed. Matching hash-only BASE manifests are
  recorded locally and in the root-only rollback store. The product owner is
  assigned release, rollback and monitoring ownership; the 24–72-hour
  observation window remains active.

## Rollback

FILE_ONLY rollback restores the previous artifact, `/dev/` page and sitemap.
STATEFUL rollback uses the rehearsed Bitrix/DB restore or an approved reverse
migration, followed by cache clear and relation/render verification. No ad-hoc
production reverse migration is permitted.
