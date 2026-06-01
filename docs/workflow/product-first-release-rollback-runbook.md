# Product-First Release Rollback Runbook

Дата: 01.06.2026
Scope: product-first public layer for `Platform / Agents / Dev / Forum`, ecosystem homepage, product-aware page copy, CTA context and navigation.

## Когда Использовать

Использовать, если после deploy/cache refresh появились P0/P1 проблемы:

- 500/white screen на `/`, `/platform/`, `/agents/`, `/dev/`, `/forum/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/about/`, `/contacts/`;
- верхнее меню, mobile menu или footer ломают доступ к money pages `/offer/`, `/price/`, `/calculator/`, `/aiagents/`;
- формы перестали открываться, валидироваться или отправляться;
- SEO/rendered head для новых страниц отдаёт неверный canonical, missing title/description/H1 или страницы выпали из static sitemap после генерации;
- claim/legal blocker требует быстро убрать публичный product-first слой.

## Что Откатывать В Первую Очередь

1. Navigation exposure:
   - root `.top.menu.php`;
   - `.bottom.menu.php`;
   - `platform/.left.menu.php`, если product dropdown вызывает проблему.
2. New product pages:
   - `platform/index.php`;
   - `agents/index.php`;
   - `dev/index.php`;
   - `forum/index.php`;
   - shared renderer changes in `local/php_interface/include/product_page.php`.
3. Reframed existing pages, only if affected by incident:
   - `/`;
   - `/services/`;
   - `/calculator/`;
   - `/offer/` catalog/detail templates;
   - `/price/`;
   - `/aiagents/`;
   - `/about/`;
   - `/contacts/`.
4. Documentation-only changes do not need emergency rollback unless they block deploy checks.

## Safe Rollback Modes

| Mode | Когда выбирать | Действие |
|---|---|---|
| Hide product navigation | Product pages load, but menu/SEO/UX is wrong | Remove product dropdown/header/footer exposure, keep pages accessible by direct URL for diagnosis |
| Disable product pages | Product pages themselves fail | Remove new page directories from deploy or revert page entries and renderer changes together |
| Revert existing-page reframing | Existing money flow regressed | Revert only the affected existing page while preserving unrelated product pages |
| Full product-first rollback | Legal/claim blocker or broad production regression | Revert all product-first page, menu, renderer and CTA-context changes from the product-first release commit |

## Cache And Deploy Notes

- After rollback deploy, clear Bitrix managed cache, component HTML cache, composite HTML pages and CSS/JS asset cache.
- If menu rollback is involved, clear menu component cache before smoke.
- If `/offer/` templates changed, clear offer component/cache and verify `/offer/`, a valid `/offer/<code>/`, invalid detail 404 and `/offer/sitemap.php`.
- If `/price/` changed, clear `bitrix/cache/s1/bitrix/news.list/*` and composite cache, then verify team presets.

## Required Smoke After Rollback

Run automated checks where available:

```bash
npm run seo:check
npm run gaps:known
```

After deploy/cache refresh:

```bash
npm run seo:check:prod
npm run visual:smoke
npm run browser:smoke:price
```

For product-first release verification after a forward deploy, use the aggregate automated check:

```bash
npm run release:product-first:prod-check
```

Manual smoke:

| Area | URLs / Actions | Expected |
|---|---|---|
| Navigation | `/`, top menu, mobile menu, footer | `/offer/`, `/services/`, `/price/`, `/calculator/`, `/aiagents/` remain reachable |
| Product URLs | `/platform/`, `/agents/`, `/dev/`, `/forum/` | Either intentionally unavailable by rollback decision or render without 500 |
| Forms | affected CTA forms | Validation, consent, CSRF and success/error states work |
| AI/chat | `/`, `/calculator/`, `/price/` if touched | No raw upstream stack/PII; controlled errors are user-safe |
| Contacts | `/contacts/` | Phone/email/legal/map visible if page was touched |
| SEO | sitemap/canonical/rendered head | No missing title/description/H1/canonical on affected public URLs |

## Evidence

Record rollback evidence in the release issue:

- rollback mode used;
- commit/PR deployed;
- deploy time;
- caches cleared;
- automated command results;
- production smoke manifest paths;
- affected URLs;
- remaining follow-up gaps;
- owner/date for strict release sign-off closure.

Use `docs/workflow/release-signoff-2026-06-01-product-first.draft.json` as the product-first release draft. Strict closure requires replacing pending gates with `passed` or `not_applicable` and running:

```bash
npm run release:signoff:check -- docs/workflow/release-signoff-2026-06-01-product-first.draft.json
```
