#!/usr/bin/env bash

set -euo pipefail

missing=()
[ -n "${SSH_PRIVATE_KEY:-}" ] || missing+=(SSH_PRIVATE_KEY)
[ -n "${SSH_KNOWN_HOSTS:-}" ] || missing+=(SSH_KNOWN_HOSTS)
[ -n "${SSH_HOST:-}" ] || missing+=(SSH_HOST)
[ -n "${SSH_USER:-}" ] || missing+=(SSH_USER)
[ -n "${DEPLOY_PATH:-}" ] || missing+=(DEPLOY_PATH)

if [ "${#missing[@]}" -gt 0 ]; then
  echo "::error::Missing required production secrets: ${missing[*]}"
  exit 1
fi

key_header="${SSH_PRIVATE_KEY%%$'\n'*}"
if [[ ! "$key_header" =~ ^-----BEGIN[[:space:]](OPENSSH[[:space:]]|RSA[[:space:]]|EC[[:space:]]|DSA[[:space:]])?PRIVATE[[:space:]]KEY-----$ ]]; then
  echo "::error::SSH_PRIVATE_KEY is not a supported PEM/OpenSSH private key"
  exit 1
fi

if [[ ! "$SSH_HOST" =~ ^[A-Za-z0-9._-]+$ ]]; then
  echo "::error::SSH_HOST contains unsupported characters"
  exit 1
fi

if [[ "$SSH_KNOWN_HOSTS" != *"ssh-"* ]]; then
  echo "::error::SSH_KNOWN_HOSTS must contain an SSH host key entry"
  exit 1
fi

if [[ ! "$SSH_USER" =~ ^[A-Za-z0-9._-]+$ ]]; then
  echo "::error::SSH_USER contains unsupported characters"
  exit 1
fi

if [[ ! "$DEPLOY_PATH" =~ ^/[A-Za-z0-9._/-]*$ ]]; then
  echo "::error::DEPLOY_PATH must be a simple absolute path"
  exit 1
fi

if [ "$DEPLOY_PATH" = "/" ]; then
  echo "::error::DEPLOY_PATH must not be filesystem root"
  exit 1
fi

if [[ "/$DEPLOY_PATH/" == *"/../"* ]] || [[ "$DEPLOY_PATH" == *"//"* ]]; then
  echo "::error::DEPLOY_PATH must not contain parent traversal or empty segments"
  exit 1
fi

echo "Deployment configuration is present and structurally valid."
