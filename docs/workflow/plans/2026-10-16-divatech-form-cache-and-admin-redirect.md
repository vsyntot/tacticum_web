# Divatech form cache and admin entry-point correction — 16.10.2026

Статус: `verified`.
Workflow lane: `Incident` + `Security / Integration`.
Release class: `STATEFUL` (Nginx site-boundary include) + `FILE_ONLY` (asset cache-busting).
Owner: project-manager; production approver: владелец проекта по текущему запросу.

## Наблюдения

- Browser access log showed a Divatech page loading an old Bitrix combined JS
  bundle (1965 bytes), followed by `422 invalid_fields`; the current source
  `local/templates/divatech/script.js` and direct API smoke already passed.
- The page uses Bitrix Asset combined URLs, so a browser can retain the old
  bundle even after the source file is replaced.
- The current bundle disabled the form controls before constructing `FormData`.
  Disabled controls are omitted from `FormData`, so the visible email, name and
  message were not sent and the endpoint returned `422 invalid_fields`.
- `https://divatech.space/bitrix/admin/` is rejected by the site-boundary
  Nginx include before PHP. The canonical Bitrix admin host is
  `https://tacticum.ru/bitrix/admin/` and returns the login surface.

## Accepted change

1. Add `filemtime` query versions to the Divatech CSS/JS Asset registrations.
   This changes only public asset URLs and does not alter the form payload.
2. Change the two exact Divatech admin locations from terminal 403 to a fixed
   301 redirect to `https://tacticum.ru`, preserving the requested path and
   query string. No second admin runtime is enabled on Divatech.
3. Construct the JSON payload before disabling the controls for the loading
   state, preserving all submitted values while keeping the controls locked
   during the request.

## Production procedure

1. Build a private release record with the two repo files and the exact
   site-boundary include; record SHA-256 and pre-hashes.
2. Acquire the existing production release lock.
3. Back up the exact Nginx include and repo-owned runtime files; rehearse
   restore in a disposable directory before mutation.
4. Install the include, run `nginx -t`, reload Nginx, and deploy the runtime
   files through the existing FILE_ONLY contour.
5. Verify the live page emits versioned asset URLs, the live bundle contains
   the current form code, API contact/callback smoke returns `200`, invalid
   consent remains `422`, and admin paths return the expected 301 locations.
6. On any failure, restore the exact Nginx include/runtime backups under the
   same lock, run `nginx -t` and repeat smoke. Remove temporary staging after
   the evidence is written.

## Acceptance

- A fresh page load no longer reuses the stale Divatech combined JS/CSS URLs.
- The live combined JS bundle constructs `FormData` before `setLoading`; form
  values remain present in the request while the UI is disabled.
- Production code, the live bundle and one real browser submit are verified;
  the user confirmed that the form now works.
- Synthetic contact and callback requests return `200 request_saved`; invalid
  consent, CSRF and foreign Origin remain rejected.
- `/bitrix/admin` and `/bitrix/admin/...` on Divatech return 301 to the same
  path on `https://tacticum.ru`, with query string preserved.
- `https://tacticum.ru/bitrix/admin/` remains the only Bitrix admin runtime.
- Nginx syntax, backup/restore rehearsal, post-deploy smoke and cleanup are
  evidenced without secrets or PII.

## Failure ledger

- Local `python3 tools/divatech/nginx-test.py` could not start its pinned
  container because the local Docker daemon socket was unavailable. It was
  not treated as a production failure; the same boundary was validated with
  `nginx -t` and live HTTP redirect smoke on production.
- The first browser report was reproduced against an old combined Bitrix
  bundle. After the file-mtime asset version was deployed, the live page
  emitted new JS/CSS bundle URLs and the current form markers were present.
