#!/usr/bin/env bash
# Portable, unprivileged entrypoint for local runs and any CI provider.
set -euo pipefail
cd "$(dirname "$0")/../.."
node tools/multisite-contract-self-test.mjs
node tools/production-release-gate-self-test.mjs
python3 tools/production-artifact-build-self-test.py
python3 tools/platform-snapshot-self-test.py
python3 tools/repository-security-self-test.py
python3 tools/multisite-public-smoke-self-test.py
python3 tools/security/crypto-key-rotation-test.py
python3 tools/security/crypto-key-rotation-apply-test.py
