# CSP Report-Only Baseline — 24.05.2026

Status: `done`

## Scope

Baseline для Sprint 10 item `S10-010`: проверить, что template остаётся в `Content-Security-Policy-Report-Only`, enforcing CSP не включён, а production `/contacts/` не получает browser/runtime/network regressions на странице с картой и централизованной Метрикой.

Goal-level проверка событий Яндекс.Метрики не входит в этот baseline и остаётся gate `metrika-goals`.

## Evidence

| Check | Result | Evidence |
|---|---|---|
| CSP mode | passed | `curl -I https://tacticum.ru/contacts/` возвращает `content-security-policy-report-only`; enforcing `content-security-policy` header не включён |
| Security companion headers | passed | `/contacts/` отдаёт `x-content-type-options: nosniff` и `x-frame-options: SAMEORIGIN` |
| Contacts rendered smoke | passed | `TACTICUM_VISUAL_BASE_URL=https://tacticum.ru TACTICUM_VISUAL_PAGES=/contacts/ TACTICUM_EXPECT_SEO_HEAD=1 npm run visual:smoke` |
| Browser/runtime baseline | passed | manifest не содержит `pageErrors`, `consoleErrors`, `networkErrors`, `brokenImages`, horizontal overflow или `seoErrors` для desktop/mobile `/contacts/` |
| Production CSS/JS e2e | passed | `npm run e2e:css-js:prod` прошёл после deploy offer clear-cache fix |

Manifest:

```text
/var/folders/57/qk1pl2_d2ydgzzhvk4p3swrw0000gn/T/tacticum-visual-smoke-2026-05-24T11-28-31-227Z/manifest.json
```

## Decision

- CSP остаётся `report-only`.
- Enforcing CSP не включать без отдельного rollout/rollback issue.
- Любые новые vendor domains для карты, Метрики, fonts или analytics добавлять через отдельный Security / Integration review и повторять этот baseline.
