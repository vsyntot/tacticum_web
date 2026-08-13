#!/usr/bin/env bash

set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
preflight="$script_dir/deploy-config-preflight.sh"
temp_dir="$(mktemp -d)"
trap 'rm -rf "$temp_dir"' EXIT

run_case() {
  local case_name="$1"
  local expected_status="$2"
  local expected_message="$3"
  shift 3

  set +e
  env -i PATH="$PATH" "$@" bash "$preflight" >"$temp_dir/$case_name.log" 2>&1
  local actual_status=$?
  set -e

  if [ "$actual_status" -ne "$expected_status" ]; then
    echo "Self-test $case_name: expected status $expected_status, got $actual_status"
    cat "$temp_dir/$case_name.log"
    exit 1
  fi

  if ! grep -Fq "$expected_message" "$temp_dir/$case_name.log"; then
    echo "Self-test $case_name: expected message not found: $expected_message"
    cat "$temp_dir/$case_name.log"
    exit 1
  fi
}

run_case missing 1 "Missing required production secrets" \
  env
run_case unsafe_host 1 "SSH_HOST contains unsupported characters" \
  env \
  SSH_PRIVATE_KEY="-----BEGIN OPENSSH PRIVATE KEY-----" \
  SSH_KNOWN_HOSTS="example.com ssh-ed25519 AAAATEST" \
  SSH_HOST="example.com;id" \
  SSH_USER="deploy" \
  DEPLOY_PATH="/var/www/tacticum"
run_case unsafe_path 1 "DEPLOY_PATH must not be filesystem root" \
  env \
  SSH_PRIVATE_KEY="-----BEGIN OPENSSH PRIVATE KEY-----" \
  SSH_KNOWN_HOSTS="example.com ssh-ed25519 AAAATEST" \
  SSH_HOST="example.com" \
  SSH_USER="deploy" \
  DEPLOY_PATH="/"
run_case valid 0 "Deployment configuration is present and structurally valid" \
  env \
  SSH_PRIVATE_KEY="-----BEGIN OPENSSH PRIVATE KEY-----" \
  SSH_KNOWN_HOSTS="example.com ssh-ed25519 AAAATEST" \
  SSH_HOST="example.com" \
  SSH_USER="deploy" \
  DEPLOY_PATH="/var/www/tacticum"

echo "Deployment configuration preflight self-test passed."
