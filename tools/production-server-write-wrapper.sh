#!/usr/bin/env bash
set -euo pipefail

# Root-owned forced-command entry point for the dedicated CI write key.
# The SSH key must include restrict,command="..." and this script never
# evaluates SSH_ORIGINAL_COMMAND as shell code.

STATE_ROOT="/var/lib/tacticum-deploy"
SITE_ROOT="/home/bitrix/www"
TOOL_ROOT="/usr/local/lib/tacticum-deploy"
CONTROLLER="${TOOL_ROOT}/production-file-only-controller.py"
CONTROLLER_HASH_FILE="${TOOL_ROOT}/production-file-only-controller.sha256"

fail() {
  printf '%s\n' "$1" >&2
  exit 126
}

read -r -a argv <<< "${SSH_ORIGINAL_COMMAND:-}"
[ "${#argv[@]}" -ge 1 ] || fail 'empty command denied'

is_hex64() { [[ "${1:-}" =~ ^[0-9a-f]{64}$ ]]; }
is_kind() { [[ "${1:-}" =~ ^(plan|prod|candidate|approval|artifact)$ ]]; }

case "${argv[0]}" in
  put)
    [ "${#argv[@]}" -eq 4 ] || fail 'put requires kind plan_id sha256'
    is_kind "${argv[1]}" || [[ "${argv[1]}" == 'base' ]] || fail 'unknown upload kind'
    is_hex64 "${argv[2]}" || fail 'invalid plan id'
    is_hex64 "${argv[3]}" || fail 'invalid content hash'
    plan_dir="${STATE_ROOT}/${argv[2]}"
    mkdir -p -- "${plan_dir}"
    chmod 700 -- "${plan_dir}"
    temp="$(mktemp --tmpdir="${plan_dir}" .incoming.XXXXXX)"
    trap 'unlink -- "$temp" 2>/dev/null || true' EXIT
    umask 077
    cat >"${temp}"
    actual="$(sha256sum -- "${temp}" | awk '{print $1}')"
    [ "${actual}" = "${argv[3]}" ] || fail 'content hash mismatch'
    case "${argv[1]}" in
      plan) target='plan.json' ;;
      base) target='base.json' ;;
      prod) target='prod.json' ;;
      candidate) target='candidate.json' ;;
      approval) target='approval.json' ;;
      artifact) target='artifact.tar.gz' ;;
    esac
    install -o root -g root -m 600 -- "${temp}" "${plan_dir}/${target}"
    trap - EXIT
    unlink -- "${temp}"
    printf '%s\n' "stored ${target} for ${argv[2]}"
    ;;
  apply)
    [ "${#argv[@]}" -eq 2 ] || fail 'apply requires plan_id'
    is_hex64 "${argv[1]}" || fail 'invalid plan id'
    [ -f "${CONTROLLER_HASH_FILE}" ] || fail 'controller pin missing'
    (cd "${TOOL_ROOT}" && sha256sum --check --status -- "${CONTROLLER_HASH_FILE}") || fail 'controller pin mismatch'
    exec /usr/bin/env -i PATH=/usr/bin:/bin \
      /usr/bin/python3 "${CONTROLLER}" \
      --root "${SITE_ROOT}" \
      --state "${STATE_ROOT}/${argv[1]}" \
      --tool-root "${TOOL_ROOT}" \
      --apply
    ;;
  *)
    fail 'write controller command denied'
    ;;
esac
