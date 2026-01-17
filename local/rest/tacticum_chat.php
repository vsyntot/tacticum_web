<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');

$data = json_decode(file_get_contents('php://input'), true);

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_chat');

if (!is_array($data)) {
    tacticum_rest_error(400, 'invalid_json', 'Некорректные данные формы.');
}

tacticum_rest_check_csrf($data);

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

// Передаём group_id, если он есть
if (!empty($data['group_id'])) {
    $payload['group_id'] = $data['group_id'];
}

// Передаём startAgent ТОЛЬКО если явно пришёл с фронта
if (!empty($data['startAgent'])) {
    $payload['startAgent'] = $data['startAgent'];
}

AddMessage2Log(serialize(tacticum_rest_mask_pii($data)), "data");
AddMessage2Log(serialize(tacticum_rest_mask_pii($payload)), "request");

$base_url = tacticum_rest_get_ai_setting('AI_SERVICE_BASE_URL', 'http://5.35.90.193:8000');
$endpoint_url = tacticum_rest_build_url($base_url, '/tacticum/v1/chat_agent');

$ch = curl_init($endpoint_url);
tacticum_rest_apply_curl_defaults($ch);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
tacticum_rest_log_tls_error($ch, 'tacticum_chat');
curl_close($ch);

$masked_response = is_string($response) ? tacticum_rest_mask_string($response) : $response;
AddMessage2Log(serialize($masked_response), "response");

if ($http_status !== 200 || !$response) {
    tacticum_rest_error(502, 'upstream_error', 'AI endpoint error');
}

if (is_string($response)) {
    $decoded = json_decode($response, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        $masked_response = tacticum_rest_mask_string($response);
        AddMessage2Log('tacticum_chat invalid JSON: ' . serialize($masked_response), 'tacticum_chat');
        tacticum_rest_error(502, 'upstream_error', 'Invalid upstream JSON');
    }
}

echo $response;
