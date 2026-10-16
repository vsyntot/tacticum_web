#!/usr/bin/env bash
set -euo pipefail

if [[ "${SSH_ORIGINAL_COMMAND:-}" != "manifest" ]]; then
  printf '%s\n' 'multisite manifest-only access' >&2
  exit 126
fi

TOOL_ROOT="/usr/local/lib/tacticum-deploy/multisite"
PLATFORM_LOCK="${TOOL_ROOT}/core.lock.json"
exec /usr/bin/env -i PATH=/usr/bin:/bin /usr/bin/node \
  "${TOOL_ROOT}/multisite-production-manifest.mjs" \
  --primary-root=/home/bitrix/www \
  --divatech-root=/home/bitrix/ext_www/divatech.space \
  --platform="${PLATFORM_LOCK}"
