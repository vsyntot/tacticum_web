# Exact plan: multisite production control-plane adoption

Plan ID: `f7c6f3507806c437cd75ac0c9466b137ff546dac45adfe0be0c9f5d6e4719ef5`
Release class: `STATEFUL`
Scope: `2026-10-14.multisite.1`
Status: `completed`

## Purpose

Install and verify the trusted two-root delivery contour for Tacticum and Divatech. This plan does not update Bitrix core, database, schema, iblocks, Divatech forms, GitLab/storage or GitHub PR refs.

## Target

- Tacticum root: `/home/bitrix/www`
- Divatech root: `/home/bitrix/ext_www/divatech.space`
- Shared runtime links remain server-owned and are never packaged as application files.
- Operational state: `/var/lib/tacticum-deploy/multisite/2026-10-14.multisite.1`
- Root-owned tooling: `/usr/local/lib/tacticum-deploy/multisite`

## Readiness checkpoint — 2026-10-15

`candidate`, не production acceptance:

- Local evidence is green for the reviewed commit: delivery contracts, PHP
  checks, two-root clean build (`399 + 10` files), 14-route public smoke and
  8 isolated trusted-controller/finalizer scenarios. Details are recorded in
  [`local-gates-refresh`](../evidence/2026-10-14-local-gates-refresh.json).
- The production host is reachable for bounded read-only inspection, but the
  observed server contour still exposes the legacy single-root manifest/write
  wrappers. The multisite production controller and finalizer from this plan
  are not installed.
- The dedicated CI transport key is not currently accepted by the host. The
  personal break-glass key is not a CI credential and cannot satisfy this
  gate.
- Local production entry points are now prepared and self-tested as
  `multisite-controller-v2-production.1` and
  `multisite-finalizer-v2-production.1`; this is local evidence only and does
  not imply server installation. See
  [`multisite-production-control-local`](../evidence/2026-10-15-multisite-production-control-local.json).
- `productionReady` remains `false`; no production apply, cache mutation,
  Bitrix core update, database/schema/iblock change or upload synchronization
  is authorized by this draft.

The checkpoint is based on the local gate refresh and the bounded host
inspection already recorded in the project handoff. It must be refreshed after
the owner approves this exact plan and before the first production write.

The 2026-10-15 platform gate is now verified in
[`platform-snapshot-and-backup-rehearsal`](../evidence/2026-10-15-platform-snapshot-and-backup-rehearsal.json):
the immutable vendor snapshot was checksum-verified and imported into a new
isolated directory, the schema profile was collected without reading table
data, and both managed roots passed an archive/restore rehearsal. The private
`core.lock.json` and snapshot remain outside the repository; the remote
staging directories were removed. This advances readiness to the control-plane
installation gate and does not promote BASE or permit an application apply.

The bounded FILE_ONLY release and dual-BASE promotion are recorded in
[`multisite-file-only-release-and-base`](../evidence/2026-10-15-multisite-file-only-release-and-base.json).
The release changed two approved menu description files, passed HTTP smoke and
three monitoring rounds for both domains, and promoted byte-identical BASE
copies at sequence 1. The exact no-op rerun and its zero-write verification are
recorded in [`multisite-noop-rerun`](../evidence/2026-10-16-multisite-noop-rerun.json).

## Closeout — 2026-10-16

The exact no-op package was rebuilt after the previous approval window expired,
with a fresh short-lived approval bound to plan
`c777783908b1b5d147d30b4073beeee6e266e26ba0c3cefad55df54c8c35cf31`.
The production controller returned `converged_unverified` with zero operations,
zero metadata updates and zero created directories. A fresh two-root manifest
matched the candidate entries, and the five declared public smoke routes
returned HTTP 200. BASE sequence 1 was left unchanged because the no-op did not
alter application state. Current exact-plan acceptance is complete; GitLab,
storage migration and future product changes remain outside this plan.
The readiness, drift and approval paragraphs below are preserved as historical
pre-apply checkpoints and are superseded by this closeout and its linked
evidence.

## Drift decision — 2026-10-15

The owner selected option 1: accept the observed current PROD as the baseline
candidate. The decision is recorded in
[`multisite-baseline-adoption-decision`](../evidence/2026-10-15-multisite-baseline-adoption-decision.json).
This resolves the semantic drift decision, but it does not itself promote a
BASE record. Promotion remains pending installation and verification of the
multisite trust contour, private dual BASE domains, lock/CAS and backup/restore
rehearsal. No application or stateful site files are changed by this decision.

## Ordered execution

1. Freeze writers and capture host identity, PHP/MySQL versions, both-root manifests and current BASE records. Stop on drift or unstable manifests.
2. Generate separate passphrase-protected CI read/write keys. Install only public keys with forced commands, `restrict`, pinned known hosts and denial checks for shell, SFTP, PTY, forwarding and arbitrary commands. The personal bootstrap key is not used by CI.
3. Install the multisite manifest wrapper, write wrapper, controller and finalizer. Pin their hashes and keep the existing single-root wrapper available for rollback.
4. Create private operational/durable BASE domains and state journal with mode `0700`; run backup/restore rehearsal for both managed roots.
5. Build one immutable multisite candidate artifact from the reviewed commit. Bind artifact, candidate/PROD manifests, scope, platform identity and controller versions into an exact plan.
6. Run three-way reconciliation. Any `CONFLICT`, `PROD_CHANGE`, unknown path or stateful trigger stops the release.
7. Execute one small approved `FILE_ONLY` release touching both roots, under the server lock. The controller may change only explicit application files and tombstones.
8. Run unprivileged smoke for Tacticum and Divatech, cache/OPcache checks and the declared monitoring window. The release remains `applied_unverified` on any failure.
9. Run the finalizer only after smoke/monitoring evidence. Promote two byte-identical BASE records using CAS and a monotonic sequence.
10. Repeat the exact plan as a no-op and verify zero application writes.

## Acceptance

- Both manifest wrappers return stable, complete two-root evidence with no `bitrix`, `upload`, secrets or runtime paths.
- Controller and finalizer hashes match the approved plan.
- Backup/restore rehearsal, lock, disk/inode and denial checks pass.
- One two-root FILE_ONLY release, smoke, monitoring, dual BASE promotion and no-op rerun pass.
- Rollback evidence is preserved without changing Bitrix core, DB, uploads or server-owned config.

## Stop conditions

Missing dedicated CI keys, unstable manifests, unresolved drift, platform/schema mismatch, failed backup/restore, controller hash mismatch, unsafe SSH capability, failed smoke/monitoring or BASE CAS mismatch.

## Approval

The owner approved proceeding with this exact plan in the current session. That
approval authorizes the bounded read-only preflight and evidence capture. Any
production mutation remains blocked until the final candidate/artifact/manifest
digests are bound to this `plan_id` and the drift gate is resolved.

The machine-readable plan is kept in the private release directory; no credentials or PII are stored in this document.
