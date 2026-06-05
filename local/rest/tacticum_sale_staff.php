<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

use Tacticum\Rest\StaffOrderPayload;

header('Content-Type: application/json; charset=UTF-8');
tacticum_rest_send_noindex_header();

tacticum_rest_validate_origin();
tacticum_rest_rate_limit_by_class('PUBLIC_STAFF_POST', 'tacticum_sale_staff');
tacticum_rest_require_method('POST');

$data = tacticum_rest_read_json_body();
tacticum_rest_check_csrf($data, true);

$hotSalePayload = StaffOrderPayload::build($data, $_SERVER);
$base_url = tacticum_rest_get_required_https_ai_url('AI_SERVICE_BASE_URL');
$endpoint_path = tacticum_rest_get_ai_endpoint_path('staff_sale', '/tacticum/v1/chat_agent/sale');
$endpoint_url = tacticum_rest_build_url($base_url, $endpoint_path);
$result = tacticum_rest_post_json_retry_without_group_id(
    $endpoint_url,
    $hotSalePayload,
    'tacticum_sale_staff_hot_sale'
);
$http_status = (int)$result['http_status'];

tacticum_rest_fail_on_curl_error($result, 'tacticum_sale_staff_hot_sale', 'Ошибка отправки во внешний сервис.');

if ($http_status < 200 || $http_status >= 300) {
    tacticum_rest_error(502, 'upstream_error', 'Ошибка отправки во внешний сервис.');
}

tacticum_rest_response(true, 'ok', null);
