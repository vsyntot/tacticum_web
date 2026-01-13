<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');
function tacticum_form_response(bool $success, ?string $error, string $code, array $extra = []): void
{
    $payload = array_merge([
        'success' => $success,
        'error' => $error,
        'code' => $code,
    ], $extra);

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_form');

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    tacticum_rest_error(400, 'invalid_json', 'Некорректные данные формы.');
}

$data = array_map(static fn($value) => is_string($value) ? trim($value) : $value, $data);

tacticum_rest_check_csrf($data);

$name = trim((string)($data['name'] ?? ''));
$company = trim((string)($data['company'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$message = trim((string)($data['message'] ?? $data['task'] ?? $data['description'] ?? $data['project'] ?? ''));
$page_url = trim((string)($data['page_url'] ?? ($_SERVER['HTTP_REFERER'] ?? '')));
$group_id = trim((string)($data['group_id'] ?? ''));

$phone_normalized = tacticum_rest_normalize_phone($phone);

$missing = [];
if ($name === '') {
    $missing[] = 'name';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $missing[] = 'email';
}
if ($phone === '' || !tacticum_rest_is_valid_phone($phone_normalized)) {
    $missing[] = 'phone';
}
if ($message === '') {
    $missing[] = 'message';
}
if ($page_url === '') {
    $missing[] = 'page_url';
}

if (mb_strlen($name) > 200) {
    $missing[] = 'name';
}
if ($company !== '' && mb_strlen($company) > 200) {
    $missing[] = 'company';
}
if (mb_strlen($message) > 2000) {
    $missing[] = 'message';
}
if ($page_url !== '' && mb_strlen($page_url) > 1000) {
    $missing[] = 'page_url';
}
if ($group_id !== '' && mb_strlen($group_id) > 64) {
    $missing[] = 'group_id';
}

if (!empty($missing)) {
    $fields = implode(', ', $missing);
    tacticum_rest_error(400, 'validation_error', "Некорректные или обязательные поля: {$fields}.");
}

$payload = [
    'name' => $name,
    'company' => $company,
    'email' => $email,
    'phone' => $phone_normalized,
    'task' => $message,
    'page_url' => $page_url,
];

if ($group_id !== '') {
    $payload['group_id'] = $group_id;
}

$form_id = trim((string)($data['form_id'] ?? ''));
if ($form_id !== '') {
    $payload['form_id'] = $form_id;
}

$is_specialist_order = !empty($data['specialist']) || !empty($data['rate']) || !empty($data['duration']);

if ($is_specialist_order) {
    $rate_raw = (string)($data['rate'] ?? '');
    $rate_value = preg_replace('/[^\d.,]/', '', $rate_raw);
    $start_date = trim((string)($data['startDate'] ?? $data['start_date'] ?? ''));
    $duration = trim((string)($data['duration'] ?? ''));
    $specialist = trim((string)($data['specialist'] ?? ''));
    $level = trim((string)($data['level'] ?? ''));

    $payload = [
        'client_name' => $name,
        'company' => $company,
        'email' => $email,
        'phone' => $phone_normalized,
        'task' => $message,
        'start_date' => $start_date,
        'worker_timeline' => $duration,
        'workers' => [
            [
                'role' => $specialist,
                'level' => $level,
                'cost_per_hour' => $rate_value !== '' ? $rate_value : $rate_raw,
                'amount_of_workers' => 1,
            ],
        ],
    ];

    if ($group_id !== '') {
        $payload['group_id'] = $group_id;
    }

    if ($page_url !== '') {
        $payload['page_url'] = $page_url;
    }

    AddMessage2Log(serialize(tacticum_rest_mask_pii($payload)), 'tacticum_form_workers_request');

    $ch = curl_init('http://5.35.90.193:8000/tacticum/v1/sale/workers');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $masked_response = is_string($response) ? tacticum_rest_mask_string($response) : $response;
    AddMessage2Log(serialize($masked_response), 'tacticum_form_workers_response');

    if ($http_status !== 200 || !$response) {
        tacticum_rest_error(502, 'upstream_error', 'Ошибка отправки во внешний сервис.');
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        tacticum_form_response(true, null, 'ok');
    }

    tacticum_form_response(true, null, 'ok', ['data' => $decoded]);
}

AddMessage2Log(serialize(tacticum_rest_mask_pii($payload)), 'tacticum_form_request');

$ch = curl_init('http://5.35.90.193:8000/tacticum/v1/chat_agent/sale');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$masked_response = is_string($response) ? tacticum_rest_mask_string($response) : $response;
AddMessage2Log(serialize($masked_response), 'tacticum_form_response');

if ($http_status === 200 && $response) {
    tacticum_form_response(true, null, 'ok');
}

tacticum_rest_error(502, 'upstream_error', 'Ошибка отправки во внешний сервис.');
