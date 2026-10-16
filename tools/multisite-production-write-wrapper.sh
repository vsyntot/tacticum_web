#!/usr/bin/env bash
set -euo pipefail

STATE_ROOT="/var/lib/tacticum-deploy/multisite/2026-10-14.multisite.1"
TOOL_ROOT="/usr/local/lib/tacticum-deploy/multisite"
CONTROLLER="${TOOL_ROOT}/multisite-production-controller.py"
CONTROLLER_HASH_FILE="${TOOL_ROOT}/multisite-production-controller.sha256"
PRIMARY_ROOT="/home/bitrix/www"
DIVATECH_ROOT="/home/bitrix/ext_www/divatech.space"

fail() { printf '%s\n' "$1" >&2; exit 126; }
is_hex64() { [[ "${1:-}" =~ ^[0-9a-f]{64}$ ]]; }
is_kind() { [[ "${1:-}" =~ ^(plan|base|prod|candidate|approval|artifact|evidence)$ ]]; }

read -r -a argv <<< "${SSH_ORIGINAL_COMMAND:-}"
[ "${#argv[@]}" -ge 1 ] || fail 'empty command denied'

case "${argv[0]}" in
  put)
    [ "${#argv[@]}" -eq 4 ] || fail 'put requires kind plan_id sha256'
    is_kind "${argv[1]}" || fail 'unknown upload kind'
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
      evidence) target='finalizer-evidence.json' ;;
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
    plan_dir="${STATE_ROOT}/${argv[1]}"
    for file in plan.json prod.json candidate.json approval.json; do
      [ -f "${plan_dir}/${file}" ] || fail "missing ${file}"
    done
    [ -f "${plan_dir}/artifact.tar.gz" ] || fail 'missing artifact'
    exec /usr/bin/env -i PATH=/usr/bin:/bin /usr/bin/python3 "${CONTROLLER}" apply \
      --primary-root "${PRIMARY_ROOT}" --divatech-root "${DIVATECH_ROOT}" \
      --state "${plan_dir}" --plan "${plan_dir}/plan.json" --prod "${plan_dir}/prod.json" \
      --candidate "${plan_dir}/candidate.json" --approval "${plan_dir}/approval.json" \
      --artifact "${plan_dir}/artifact.tar.gz"
    ;;
  recover)
    [ "${#argv[@]}" -eq 2 ] || fail 'recover requires plan_id'
    is_hex64 "${argv[1]}" || fail 'invalid plan id'
    plan_dir="${STATE_ROOT}/${argv[1]}"
    exec /usr/bin/env -i PATH=/usr/bin:/bin /usr/bin/python3 "${CONTROLLER}" recover \
      --primary-root "${PRIMARY_ROOT}" --divatech-root "${DIVATECH_ROOT}" --state "${plan_dir}"
    ;;
  *)
    fail 'write controller command denied'
    ;;
esac
