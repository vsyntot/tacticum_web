<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');
tacticum_rest_send_noindex_header();

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_chat');
tacticum_rest_require_method('POST');

$data = tacticum_rest_read_json_body();
tacticum_rest_check_csrf($data, true);

$user_message = trim((string)($data['user_message'] ?? ''));

if ($user_message === '') {
    tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: user_message.');
}

if (mb_strlen($user_message) > 2000) {
    tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: user_message.');
}

$payload = [
    'user_message' => $user_message,
];

$group_id = trim((string)($data['group_id'] ?? ''));
if ($group_id !== '') {
    if (mb_strlen($group_id) > 64) {
        tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: group_id.');
    }
    $payload['group_id'] = $group_id;
}

$start_agent = trim((string)($data['startAgent'] ?? ''));
if ($start_agent !== '') {
    $allowed_agents = ['ITExpertAgent', 'SalesConsultantAgent', 'TriageAgent'];
    if (!in_array($start_agent, $allowed_agents, true)) {
        tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: startAgent.');
    }
}

$base_url = tacticum_rest_get_required_https_ai_url('AI_SERVICE_BASE_URL');
$endpoint_url = tacticum_rest_build_url($base_url, '/tacticum/v1/chat_agent');
if ($start_agent !== '') {
    $endpoint_url .= '?' . http_build_query(['startAgent' => $start_agent]);
}

$result = tacticum_rest_post_json($endpoint_url, $payload, 'tacticum_chat');
$response = $result['response'];
$http_status = (int)$result['http_status'];
$total_time = (float)$result['total_time'];

tacticum_rest_fail_on_curl_error($result, 'tacticum_chat', 'Ошибка соединения с AI endpoint.');

if ($http_status !== 200 || !$response) {
    tacticum_rest_error(502, 'upstream_http_error', 'AI endpoint error', [
        'upstream_status' => $http_status,
        'upstream_time' => $total_time,
    ]);
}

if (is_string($response)) {
    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        tacticum_rest_error(502, 'upstream_error', 'Invalid upstream JSON');
    }

    if (empty($decoded['response']) && empty($decoded['error']) && empty($decoded['message'])) {
        tacticum_rest_error(502, 'upstream_contract_error', 'AI endpoint returned unexpected payload.');
    }
}

echo $response;
