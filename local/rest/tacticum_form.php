<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

use Tacticum\Rest\LeadPayload;

header('Content-Type: application/json; charset=UTF-8');
tacticum_rest_send_noindex_header();

tacticum_rest_validate_origin();
tacticum_rest_rate_limit_by_class('PUBLIC_LEAD_POST', 'tacticum_form');
tacticum_rest_require_method('POST');

$data = tacticum_rest_read_json_body();
tacticum_rest_check_csrf($data, true);

$payload = LeadPayload::build($data, $_SERVER);
$result = tacticum_rest_submit_chat_agent_sale(
    $payload,
    'tacticum_form_chat_agent',
    'tacticum_form',
    'Ошибка отправки во внешний сервис.'
);

if (tacticum_rest_is_successful_upstream_response($result)) {
    LeadPayload::respond(true, null, 'ok');
}

tacticum_rest_fail_chat_agent_sale_upstream($result, 'tacticum_form_chat_agent');
