# CSP Target-State Decision

Дата: 25.05.2026
Статус: accepted for stabilization
Связанный gap: `FSC-010`

## Decision

Для текущего стабилизационного релиза `Content-Security-Policy-Report-Only` является целевым состоянием. Enforcing CSP не включаем в Sprint 16.

## Reason

Сайт всё ещё использует Bitrix runtime, vendor analytics, Метрику, template assets и legacy-compatible fallback paths. Enforcing mode без отдельной report-only triage может сломать production UX, Метрику или админский toolbar. Текущий header уже поддерживает switch `security.csp_mode=report-only|enforce`, поэтому будущий rollout не требует нового публичного URL или изменения продуктового flow.

## Sprint 16 Notes

- `/contacts/` больше не зависит от Yandex Maps constructor script: wrong-map issue закрыт Yandex map widget iframe с координатами московского офиса + external route link.
- Yandex.Metrika остаётся централизованным asset `js/metrika.js`.
- `metrika-goals` остаётся внешним release gate, потому что подтверждение целей требует доступа к Яндекс.Метрике.

## Future Enforce Gate

Перед включением `security.csp_mode=enforce` нужен отдельный Security / Integration rollout:

- собрать report-only violations на production/staging;
- удалить неиспользуемые vendor origins;
- сократить или обосновать `unsafe-inline`;
- подтвердить Метрику, Bitrix admin toolbar, contact modal, forms, chat и `/price/` actions;
- иметь rollback на `report-only`.

Sprint 22 follow-up 04.06.2026: `docs/workflow/product-security-release-legacy-closure-decision-2026-06-04.md` expands this future gate into an approval package for `ARCH-007` and `SEC-003`: endpoint-sensitive release gate support, report-only triage, inline/vendor inventory, staging enforce smoke and rollback remain required before production enforce.

## Acceptance

`FSC-010` закрыт как accepted decision: report-only CSP является target state для стабилизации, enforce остаётся будущим hardening scope.
