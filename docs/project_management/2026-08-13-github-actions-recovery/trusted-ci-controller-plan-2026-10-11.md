# Trusted CI FILE_ONLY controller plan

Статус: `in_progress`  
Контур: `Security / Integration Lane`, `FILE_ONLY`; application mutation в рамках этого шага не выполняется.

## Цель

Заменить production shell/`rsync` job на bounded GitHub Actions controller, сохранив запуск от `main`, но разделив read-only inventory и write/apply credentials. Controller принимает только exact `plan_id`, immutable artifact и approved `BASE/PROD/CANDIDATE` binding.

## Accepted scope

- `.github/workflows/deploy.yml` — artifact, read-only manifest, three-way plan, protected production approval, forced-command upload/apply и smoke jobs.
- `tools/production-file-only-controller.py` — generic server-side FILE_ONLY controller.
- `tools/production-server-write-wrapper.sh` — root-owned SSH forced-command boundary.
- `tools/production_artifact_build.py` — deterministic regular-file artifact builder.
- Dedicated CI read-only/write Ed25519 keys, pinned known_hosts and server-side authorized-key lines.

## Invariants

- Personal local SSH private key не используется GitHub Actions.
- CI keys не выдаются `pull_request` jobs.
- Privileged job не запускает `npm ci` и candidate scripts; `npm ci`/smoke выполняются в unprivileged jobs.
- `bitrix/`, `upload/`, server-owned config и stateful/control-plane paths не входят в FILE_ONLY artifact.
- Production apply запускается только после environment reviewer gate; exact plan/artifact hashes and expiry are uploaded to the forced controller.
- `BASE` is the reviewed manifest `production-base-manifest-2026-10-11.json`; any PROD drift blocks the run until reconciliation.

## Rollback

1. Disable the `production` environment reviewer or cancel the run before approval.
2. Revoke the CI write public key from root `authorized_keys` and remove the forced wrapper if the controller pin or boundary fails.
3. Restore the previous `deploy.yml` from the last main commit only after an explicit owner decision; do not re-enable legacy `rsync` by accident.
4. Application rollback remains the controller's per-plan backup/restore path and requires a separate approved FILE_ONLY plan.

## Acceptance

- `bash -n`/offline self-tests and contract self-test pass.
- Server wrapper accepts only `put {base,plan,prod,candidate,approval,artifact}` and `apply <plan_id>`; shell/SFTP/PTY/forwarding probes remain denied.
- Read-only key still returns only `manifest`.
- GitHub secrets are present only in `production-readonly`/`production` environments; repository and PR jobs have no production secrets.
- First workflow run is a controlled dry review; no application mutation is accepted until exact plan summary, reviewer approval, controller report and post-deploy smoke are all present.

## Evidence

Hashes, remote authorized-key backup, workflow run and controller report are recorded in the recovery carrier after installation. Secret values are never committed or logged.

The first controlled workflow run must leave the production reviewer gate pending until the redacted plan summary and artifact digest have been inspected.
