<?php

declare(strict_types=1);

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/rest/rest_helpers.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: private, no-store');
tacticum_rest_send_noindex_header();
tacticum_rest_validate_origin('divatech');
tacticum_rest_rate_limit_by_class('PUBLIC_LEAD_POST', 'divatech_lead');
tacticum_rest_require_method('POST');
$data = tacticum_rest_read_json_body('Invalid form data.', 16384);
tacticum_rest_check_csrf($data);

if (!\Tacticum\Divatech\Settings::formsEnabled()) {
    tacticum_rest_error(503, 'forms_unavailable', 'Please contact us by email or phone.');
}
try {
    $payload = \Tacticum\Divatech\LeadPayload::parse($data);
} catch (\InvalidArgumentException) {
    tacticum_rest_error(422, 'invalid_fields', 'Please check the required fields.');
}
try {
    \Tacticum\Divatech\LeadService::save($payload);
} catch (\Throwable) {
    tacticum_rest_error(503, 'temporarily_unavailable', 'Please try again later or contact us by email.');
}
tacticum_rest_response(true, 'request_saved', 'Thank you for your request!');
