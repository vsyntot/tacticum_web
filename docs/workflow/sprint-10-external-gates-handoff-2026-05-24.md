# Sprint 10 External Gates Handoff — 24.05.2026

Status: `external handoff`

Этот artifact закрывает оставшийся Sprint 10 хвост как управляемый handoff. Кодовые, SEO, CSS/JS, CSP и routing gates закрыты. Ниже остались только проверки, которые нельзя честно выполнить из репозитория без авторизованного доступа к staging/CRM/upstream, Яндекс.Метрике и Bitrix admin или без создания controlled test lead.

## Public Prechecks Completed

| Area | Result | Evidence |
|---|---|---|
| Bitrix admin surface | passed as unauthenticated precheck | `curl -I https://tacticum.ru/bitrix/admin/` вернул `HTTP/2 200`, `x-bitrix-ajax-status: Authorize`, без 500/white screen |
| REST safe method guard | passed | GET `/local/rest/tacticum_form.php`, `tacticum_chat.php`, `tacticum_prefill.php`, `tacticum_sale_staff.php` вернули controlled `405` |
| Metrika asset wiring | passed as code/public precheck | `local/templates/tacticum/js/metrika.js` содержит counter `103471113`; production HTML содержит noscript pixel `https://mc.yandex.ru/watch/103471113` |
| Production browser/CSS/JS | passed | `npm run e2e:css-js:prod` passed after deploy; manifests recorded in release sign-off draft |
| Production SEO/offer clear-cache | passed | `/offer/marketingoviy-marketpleys-dlya-medikov-i-klinik/?clear_cache=Y` вернул `200`; `npm run seo:check:prod` passed |

## Repository Closure Refresh - 25.05.2026

| Area | Result | Evidence |
|---|---|---|
| Static repository guards | passed | `seo:check`, `css:check`, `template-styles:check`, `config:check`, `sale:sunset:check`, `release:signoff:draft-check`, `release:signoff:summary`, `release:signoff:self-test` |
| Production-safe checks | passed | `seo:check:prod`; unauthenticated `/bitrix/admin/` returned authorize screen without 500; GET guards for form/chat/prefill/staff/resolver returned controlled `405` JSON |
| Local PHP preflight | degraded | PHP CLI отсутствует локально; GitHub PHP 8.4 lint остаётся обязательным fallback |
| Current working-tree post-deploy smoke | passed | 25.05.2026: production `/contacts/` уже рендерит карту `Тактикум` (`oid=243968538014`); `visual:smoke` с `TACTICUM_EXPECT_SEO_HEAD=1` прошёл по 9 URL, `browser:smoke` прошёл по 9 URL, focused `/price/` smoke прошёл `price team presets/summary`; manifests лежат в `/tmp/tacticum-release-closure-2026-05-25/` |
| Controlled production success-flow | blocked by upstream | 25.05.2026: default lead form, modal form, AI chat и staff-order вернули upstream `502`; prefill controlled empty state вернул ожидаемый `404 not_found`; sanitized evidence `/tmp/tacticum-release-closure-2026-05-25/controlled-success-flow.json` |

## External Gates

| Sprint ID | Release Gate | Owner | Due | Required Evidence | Repository Status |
|---|---|---|---|---|---|
| S10-002 | `manual-success-flow` | QA + Backend/Frontend | before strict release closure | Staging или controlled production evidence для default form, modal form, AI chat, prefill, staff-order; no PII | follow-up |
| S10-003 | `staff-sale-upstream` | Architect + Backend + QA + DevOps | before strict release closure | Upstream/CRM confirms `workers_json`, `team_preset`, `monthly_budget_estimate`, `endDate`; no raw payload | follow-up |
| S10-004 | `metrika-goals` | PM/Marketing + QA | before strict release closure | Yandex.Metrika confirms affected form/chat/prefill/staff-order goals/events; params contain no PII | follow-up |
| S10-005 | `bitrix-admin` | QA/Admin + DevOps | before strict release closure | Authenticated `/bitrix/admin/` opens; public admin toolbar works after deploy/cache refresh; no cookie/session evidence | follow-up |
| S10-008 | `legacy-sunset` follow-up | PM + Backend | `30.06.2026` inventory, `31.08.2026` migration plan | `legacy-sale-alias-consumer-inventory.md` filled from access logs/CRM aggregate reports | follow-up |

## Evidence Rules

- Не хранить в репозитории имя, телефон, email, текст заявки, raw request/response payload, cookie, session, CSRF token, secret или полный screenshot с PII.
- Использовать safe IDs: lead ID, upstream request ID, internal ticket/report ID, masked `group_id`, timestamp, owner, result.
- После заполнения evidence обновить `docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json` и выполнить strict check:

```bash
npm run release:signoff:check -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
```

## Closure Rule

Sprint 10 считается code/repository complete. Release issue нельзя закрывать strict sign-off, пока `manual-success-flow`, `metrika-goals`, `bitrix-admin` и `staff-sale-upstream` остаются `pending`.
