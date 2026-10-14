#!/usr/bin/env bash
set -euo pipefail
task_root=$(cd "$(dirname "$0")/../.." && pwd)
docker run --rm --network none \
  --mount "type=bind,src=$task_root/tools/divatech/infrastructure,dst=/candidate,readonly" \
  --mount "type=bind,src=$task_root/sites/divatech,dst=/site-source,readonly" \
  php:8.4-apache@sha256:901b0dbcd2419cc9cd307ea05e57403449ce722ccd5b32335da6e8d39f2b1ee0 sh -c '
set -eu
a2enmod rewrite >/dev/null
mkdir -p /etc/apache2/logs /home/bitrix/ext_www/divatech.space /home/bitrix/www/local/php_interface/include /home/bitrix/www/bitrix /tmp/php_sessions/ext_www/divatech.space /tmp/php_upload/ext_www/divatech.space
cp /candidate/apache-port.conf /etc/apache2/conf-enabled/divatech-port.conf
cp /candidate/apache-site.conf /etc/apache2/sites-enabled/divatech.conf
cp /site-source/.htaccess /home/bitrix/ext_www/divatech.space/.htaccess
ln -s /home/bitrix/www/local /home/bitrix/ext_www/divatech.space/local
printf "%s" "<?php echo \"divatech fixture\";" > /home/bitrix/ext_www/divatech.space/index.php
printf "%s" "<?php http_response_code(404); echo \"missing\";" > /home/bitrix/ext_www/divatech.space/404.php
printf "%s" "<?php echo \"must stay private\";" > /home/bitrix/www/local/php_interface/include/tacticum_config.php
apache2ctl -t
apache2-foreground >/tmp/apache-test.log 2>&1 &
for attempt in 1 2 3 4 5; do
  if curl -fsS -H "Host: divatech.space" http://127.0.0.1:8887/ >/tmp/body 2>/dev/null; then break; fi
  sleep 1
done
test "$(cat /tmp/body)" = "divatech fixture"
test "$(curl -s -o /dev/null -w "%{http_code}" -H "Host: divatech.space" http://127.0.0.1:8887/missing)" = 404
test "$(curl -s -o /dev/null -w "%{http_code}" -H "Host: divatech.space" http://127.0.0.1:8887/local/php_interface/include/tacticum_config.php)" = 403
echo "Apache: syntax, document root, 404 and shared-config denial passed"
'
