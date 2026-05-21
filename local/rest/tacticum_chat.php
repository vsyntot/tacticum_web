<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_chat');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    tacticum_rest_error(405, 'method_not_allowed', 'Метод запроса не поддерживается.');
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    tacticum_rest_error(400, 'invalid_json', 'Некорректные данные формы.');
}

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

AddMessage2Log(serialize(tacticum_rest_mask_pii($data)), "tacticum_chat_data");
$request_log = $payload;
if ($start_agent !== '') {
    $request_log['startAgent'] = $start_agent;
}
AddMessage2Log(serialize(tacticum_rest_mask_pii($request_log)), "tacticum_chat_request");

$base_url = tacticum_rest_get_required_https_ai_url('AI_SERVICE_BASE_URL');
$endpoint_url = tacticum_rest_build_url($base_url, '/tacticum/v1/chat_agent');
if ($start_agent !== '') {
    $endpoint_url .= '?' . http_build_query(['startAgent' => $start_agent]);
}

$result = tacticum_rest_post_json($endpoint_url, $payload, 'tacticum_chat');
$response = $result['response'];
$http_status = (int)$result['http_status'];
$total_time = (float)$result['total_time'];
$start_transfer_time = (float)$result['start_transfer_time'];

$masked_response = is_string($response) ? tacticum_rest_mask_string($response) : $response;
AddMessage2Log(serialize($masked_response), "tacticum_chat_response");

tacticum_rest_fail_on_curl_error($result, 'tacticum_chat', 'Ошибка соединения с AI endpoint.');

if ($http_status !== 200 || !$response) {
    $response_length = is_string($response) ? strlen($response) : 0;
    $decoded_error = is_string($response) ? json_decode($response, true) : null;
    $upstream_error = is_array($decoded_error)
        ? (string)($decoded_error['title'] ?? $decoded_error['description'] ?? '')
        : '';

    AddMessage2Log("Upstream error (tacticum_chat): http_status={$http_status}; response_length={$response_length}; total_time={$total_time}; start_transfer_time={$start_transfer_time}; upstream_error={$upstream_error}", 'tacticum_chat_error');
    tacticum_rest_error(502, 'upstream_http_error', 'AI endpoint error', [
        'upstream_status' => $http_status,
        'upstream_time' => $total_time,
    ]);
}

if (is_string($response)) {
    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        $masked_response = tacticum_rest_mask_string($response);
        AddMessage2Log('tacticum_chat invalid JSON: ' . serialize($masked_response), 'tacticum_chat');
        tacticum_rest_error(502, 'upstream_error', 'Invalid upstream JSON');
    }

    if (empty($decoded['response']) && empty($decoded['error']) && empty($decoded['message'])) {
        AddMessage2Log('tacticum_chat unexpected JSON shape: ' . serialize(tacticum_rest_mask_pii($decoded)), 'tacticum_chat');
        tacticum_rest_error(502, 'upstream_contract_error', 'AI endpoint returned unexpected payload.');
    }
}

echo $response;
