#!/bin/bash
set -euo pipefail

if [[ "${SSH_ORIGINAL_COMMAND:-}" != "manifest" ]]; then
  printf '%s\n' 'manifest-only access' >&2
  exit 126
fi

exec /usr/bin/env -i PATH=/usr/bin:/bin /usr/bin/node \
  /usr/local/lib/tacticum-deploy/production-server-manifest.mjs
