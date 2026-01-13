<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');

$data = json_decode(file_get_contents('php://input'), true);

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_sale_staff');

if (!is_array($data)) {
    tacticum_rest_error(400, 'invalid_json', 'Некорректные данные формы.');
}

tacticum_rest_check_csrf($data);

$client_name = trim((string)($data['name'] ?? ''));
$company = trim((string)($data['company'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$task = trim((string)($data['description'] ?? ($data['task'] ?? '')));
$startDate = trim((string)($data['startDate'] ?? ''));
$duration = trim((string)($data['duration'] ?? ''));
$specialist = trim((string)($data['specialist'] ?? ''));
$rate = trim((string)($data['rate'] ?? ''));
$cnt = 1;
$level = trim((string)($data['level'] ?? ''));

$phone_normalized = tacticum_rest_normalize_phone($phone);

$errors = [];
if ($client_name === '') {
    $errors[] = 'name';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'email';
}
if ($phone === '' || !tacticum_rest_is_valid_phone($phone_normalized)) {
    $errors[] = 'phone';
}
if ($task !== '' && mb_strlen($task) > 2000) {
    $errors[] = 'description';
}
if ($client_name !== '' && mb_strlen($client_name) > 200) {
    $errors[] = 'name';
}
if ($company !== '' && mb_strlen($company) > 200) {
    $errors[] = 'company';
}
if ($specialist !== '' && mb_strlen($specialist) > 200) {
    $errors[] = 'specialist';
}
if ($level !== '' && mb_strlen($level) > 100) {
    $errors[] = 'level';
}
if ($startDate !== '' && mb_strlen($startDate) > 50) {
    $errors[] = 'startDate';
}
if ($duration !== '' && mb_strlen($duration) > 50) {
    $errors[] = 'duration';
}
if (!empty($errors)) {
    tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: ' . implode(', ', $errors) . '.');
}

$payload = [
    'client_name'       => $client_name,
    'company'    => $company,
    'email'      => $email,
    'phone'      => $phone_normalized,
    'task'       => $task,
    'start_date'  => $startDate,
    'worker_timeline'   => $duration,
    'workers' => [
        [
            'role' => $specialist,
            'level'      => $level,
            'cost_per_hour'       => $rate,
            'amount_of_workers'       => $cnt,
        ]
    ],
];

if (isset($data['group_id'])) {
    $payload['group_id'] = trim((string)$data['group_id']);
}

AddMessage2Log(serialize(tacticum_rest_mask_pii($data)), "sale_workers_data");
AddMessage2Log(serialize(tacticum_rest_mask_pii($payload)), "sale_workers_request");

$base_url = tacticum_rest_get_ai_setting('AI_SERVICE_BASE_URL', 'https://5.35.90.193:8000');
$endpoint_url = tacticum_rest_build_url($base_url, '/tacticum/v1/sale/workers');

$ch = curl_init($endpoint_url);
tacticum_rest_apply_curl_defaults($ch);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
tacticum_rest_log_tls_error($ch, 'tacticum_sale_staff');
curl_close($ch);

$masked_response = is_string($response) ? tacticum_rest_mask_string($response) : $response;
AddMessage2Log(serialize($masked_response), "sale_workers_response");

if ($http_status !== 200 || !$response) {
    $error_text = is_string($response) && $response ? tacticum_rest_mask_string($response) : 'AI endpoint error';
    tacticum_rest_error(502, 'upstream_error', $error_text);
}

if (is_string($response) && !empty($response)) {
    $json = json_decode($response, true);
    if ($json === null) {
        echo json_encode(['success' => true]);
        exit;
    }
    echo $response;
} else {
    echo json_encode(['success' => true]);
}
