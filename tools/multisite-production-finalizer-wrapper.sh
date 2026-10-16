#!/usr/bin/env bash
set -euo pipefail

if [[ "${SSH_ORIGINAL_COMMAND:-}" != finalize\ * ]]; then
  printf '%s\n' 'finalizer command denied' >&2
  exit 126
fi
read -r verb plan_id <<< "${SSH_ORIGINAL_COMMAND}"
[ "${verb}" = finalize ] || { printf '%s\n' 'finalizer command denied' >&2; exit 126; }
[[ "${plan_id:-}" =~ ^[0-9a-f]{64}$ ]] || { printf '%s\n' 'invalid plan id' >&2; exit 126; }

STATE_ROOT="/var/lib/tacticum-deploy/multisite/2026-10-14.multisite.1"
TOOL_ROOT="/usr/local/lib/tacticum-deploy/multisite"
FINALIZER="${TOOL_ROOT}/multisite-production-finalizer.py"
FINALIZER_HASH_FILE="${TOOL_ROOT}/multisite-production-finalizer.sha256"
plan_dir="${STATE_ROOT}/${plan_id}"

[ -f "${FINALIZER_HASH_FILE}" ] || { printf '%s\n' 'finalizer pin missing' >&2; exit 126; }
(cd "${TOOL_ROOT}" && sha256sum --check --status -- "${FINALIZER_HASH_FILE}") || { printf '%s\n' 'finalizer pin mismatch' >&2; exit 126; }
[ -f "${plan_dir}/candidate.json" ] || { printf '%s\n' 'candidate missing' >&2; exit 126; }
[ -f "${plan_dir}/finalizer-evidence.json" ] || { printf '%s\n' 'finalizer evidence missing' >&2; exit 126; }

exec /usr/bin/env -i PATH=/usr/bin:/bin /usr/bin/python3 "${FINALIZER}" promote \
  --operational "${STATE_ROOT}/base/operational" \
  --durable "${STATE_ROOT}/base/durable" \
  --state "${STATE_ROOT}/base/state" \
  --evidence "${plan_dir}/finalizer-evidence.json" \
  --candidate "${plan_dir}/candidate.json"
