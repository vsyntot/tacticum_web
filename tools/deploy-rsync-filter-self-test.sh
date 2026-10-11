#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
workflow="$repo_root/.github/workflows/deploy.yml"
wrapper="$repo_root/tools/production-server-write-wrapper.sh"
controller="$repo_root/tools/production-file-only-controller.py"

if grep -q 'rsync' "$workflow"; then
  echo "Trusted deploy workflow must not contain legacy rsync mutation"
  exit 1
fi
for required in \
  'production-readonly' \
  'SSH_READONLY_PRIVATE_KEY' \
  'SSH_WRITE_PRIVATE_KEY' \
  'candidate.tar.gz' \
  'BASE/PROD/CANDIDATE' \
  'production-file-only-controller'; do
  if ! grep -q -- "$required" "$workflow" "$wrapper" "$controller"; then
    echo "Trusted deploy contract is missing: $required"
    exit 1
  fi
done

if grep -q 'npm ci' "$workflow"; then
  deploy_block="$(awk '/^  deploy:/{flag=1} /^  smoke:/{flag=0} flag' "$workflow")"
  if grep -q 'npm ci' <<< "$deploy_block"; then
    echo "Privileged deploy job must not install candidate dependencies"
    exit 1
  fi
fi

echo "Trusted production controller self-test passed."
