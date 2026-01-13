<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');
$data = json_decode(file_get_contents('php://input'), true);

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_offer');

if (!is_array($data)) {
    tacticum_rest_error(400, 'invalid_json', 'Некорректные данные формы.');
}

tacticum_rest_check_csrf($data);

$name = trim((string)($data['name'] ?? ''));
$company = trim((string)($data['company'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$task = trim((string)($data['task'] ?? ''));
$page_url = trim((string)($data['page_url'] ?? ''));

$phone_normalized = tacticum_rest_normalize_phone($phone);

$errors = [];
if ($name === '') {
    $errors[] = 'name';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'email';
}
if ($phone === '' || !tacticum_rest_is_valid_phone($phone_normalized)) {
    $errors[] = 'phone';
}
if ($task !== '' && mb_strlen($task) > 2000) {
    $errors[] = 'task';
}
if ($name !== '' && mb_strlen($name) > 200) {
    $errors[] = 'name';
}
if ($company !== '' && mb_strlen($company) > 200) {
    $errors[] = 'company';
}
if ($page_url !== '' && mb_strlen($page_url) > 1000) {
    $errors[] = 'page_url';
}

if (!empty($errors)) {
    tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: ' . implode(', ', $errors) . '.');
}

$payload = [
    'name' => $name,
    'company' => $company,
    'email' => $email,
    'phone' => $phone_normalized,
    'task' => $task,
];

if ($page_url) {
    $payload['page_url'] = $page_url;
}

if (isset($data['group_id']) && !empty($data['group_id'])) {
    $payload['group_id'] = trim((string)$data['group_id']);
}

AddMessage2Log(serialize(tacticum_rest_mask_pii($payload)), "sale_request");

$base_url = tacticum_rest_get_ai_setting('AI_SERVICE_BASE_URL', 'http://5.35.90.193:8000');
$endpoint_url = tacticum_rest_build_url($base_url, '/tacticum/v1/chat_agent/sale');

$ch = curl_init($endpoint_url);
tacticum_rest_apply_curl_defaults($ch);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
tacticum_rest_log_tls_error($ch, 'tacticum_offer');
curl_close($ch);

$masked_response = is_string($response) ? tacticum_rest_mask_string($response) : $response;
AddMessage2Log(serialize($masked_response), "sale_response");

if ($http_status === 200 && $response) {
    echo json_encode(['success' => true]);
    exit;
}

tacticum_rest_error(502, 'upstream_error', 'Ошибка отправки во внешний сервис.');
