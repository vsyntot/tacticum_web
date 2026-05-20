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

tacticum_rest_check_csrf($data, true);

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
$base_url = tacticum_rest_get_required_https_ai_url('AI_SERVICE_BASE_URL');

if ($is_specialist_order) {
    $rate_raw = (string)($data['rate'] ?? '');
    $start_date = trim((string)($data['startDate'] ?? $data['start_date'] ?? ''));
    $duration = trim((string)($data['duration'] ?? ''));
    $specialist = trim((string)($data['specialist'] ?? ''));
    $level = trim((string)($data['level'] ?? ''));

    $durationLabels = [
        '1-month' => '1 месяц',
        '3-months' => '3 месяца',
        '6-months' => '6 месяцев',
    ];
    $durationLabel = $durationLabels[$duration] ?? $duration;

    $taskParts = [];
    if ($specialist !== '') {
        $taskParts[] = 'Специалист: ' . $specialist . ($level !== '' ? ' (' . $level . ')' : '');
    }
    if ($rate_raw !== '') {
        $taskParts[] = 'Ставка: ' . $rate_raw . ' руб/час';
    }
    if ($start_date !== '') {
        $taskParts[] = 'Дата начала: ' . $start_date;
    }
    if ($durationLabel !== '') {
        $taskParts[] = 'Срок работы: ' . $durationLabel;
    }
    $taskParts[] = 'Описание задачи: ' . $message;

    $payload['task'] = implode("\n", $taskParts);
}

AddMessage2Log(serialize(tacticum_rest_mask_pii($payload)), 'tacticum_form_request');

$chat_agent_url = tacticum_rest_build_url($base_url, '/tacticum/v1/chat_agent/sale');
$result = tacticum_rest_post_json($chat_agent_url, $payload, 'tacticum_form_chat_agent');
$response = $result['response'];
$http_status = (int)$result['http_status'];

$masked_response = is_string($response) ? tacticum_rest_mask_string($response) : $response;
AddMessage2Log(serialize($masked_response), 'tacticum_form_response');

tacticum_rest_fail_on_curl_error($result, 'tacticum_form_chat_agent', 'Ошибка отправки во внешний сервис.');

if ($http_status === 200 && $response) {
    tacticum_form_response(true, null, 'ok');
}

tacticum_rest_error(502, 'upstream_error', 'Ошибка отправки во внешний сервис.');
