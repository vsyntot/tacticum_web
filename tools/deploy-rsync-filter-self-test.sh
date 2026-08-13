#!/usr/bin/env bash

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
workflow="$repo_root/.github/workflows/deploy.yml"
temp_dir="$(mktemp -d)"
trap 'rm -rf "$temp_dir"' EXIT

if ! grep -Fq -- "--exclude='/php_interface/include/tacticum_config.php'" "$workflow"; then
  echo "Deploy workflow must protect tacticum_config.php with a source-relative rsync exclusion"
  exit 1
fi

mkdir -p "$temp_dir/source/php_interface/include" "$temp_dir/target/php_interface/include"
printf '%s\n' 'tracked fixture' > "$temp_dir/source/php_interface/include/tracked.php"
printf '%s\n' 'server-owned secret fixture' > "$temp_dir/target/php_interface/include/tacticum_config.php"

rsync -a --delete \
  --exclude='/php_interface/include/tacticum_config.php' \
  "$temp_dir/source/" \
  "$temp_dir/target/"

test -f "$temp_dir/target/php_interface/include/tracked.php"
grep -Fq 'server-owned secret fixture' "$temp_dir/target/php_interface/include/tacticum_config.php"

echo "Deploy rsync filter self-test passed."
