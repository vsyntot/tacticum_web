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

tacticum_rest_check_csrf($data, true);

$client_name = trim((string)($data['name'] ?? ''));
$company = trim((string)($data['company'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$task = trim((string)($data['message'] ?? $data['description'] ?? $data['task'] ?? ''));
$startDate = trim((string)($data['startDate'] ?? $data['start_date'] ?? ''));
$duration = trim((string)($data['duration'] ?? ''));
$specialist = trim((string)($data['specialist'] ?? ''));
$rate = trim((string)($data['rate'] ?? ''));
$level = trim((string)($data['level'] ?? ''));
$page_url = trim((string)($data['page_url'] ?? ($_SERVER['HTTP_REFERER'] ?? '')));
$group_id = trim((string)($data['group_id'] ?? ''));
$form_id = trim((string)($data['form_id'] ?? ''));
$amount_raw = $data['amount_of_workers'] ?? $data['amount'] ?? 1;
$amount_of_workers = is_numeric($amount_raw) && (int)$amount_raw > 0 ? (int)$amount_raw : 1;

$phone_normalized = tacticum_rest_normalize_phone($phone);
$cost_per_hour = preg_replace('/[^\d.,]/', '', $rate);
if ($cost_per_hour === '') {
    $cost_per_hour = $rate;
}

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
if ($task === '') {
    $errors[] = 'message';
}
if ($specialist === '') {
    $errors[] = 'specialist';
}
if ($startDate === '') {
    $errors[] = 'startDate';
}
if ($duration === '') {
    $errors[] = 'duration';
}
if ($client_name !== '' && mb_strlen($client_name) > 200) {
    $errors[] = 'name';
}
if ($company !== '' && mb_strlen($company) > 200) {
    $errors[] = 'company';
}
if ($task !== '' && mb_strlen($task) > 2000) {
    $errors[] = 'message';
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
if ($page_url !== '' && mb_strlen($page_url) > 1000) {
    $errors[] = 'page_url';
}
if ($group_id !== '' && mb_strlen($group_id) > 64) {
    $errors[] = 'group_id';
}
if ($form_id !== '' && mb_strlen($form_id) > 100) {
    $errors[] = 'form_id';
}
if ($amount_of_workers > 100) {
    $errors[] = 'amount_of_workers';
}
if (!empty($errors)) {
    tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: ' . implode(', ', array_unique($errors)) . '.');
}

$durationLabels = [
    '1-month' => '1 месяц',
    '3-months' => '3 месяца',
    '6-months' => '6 месяцев',
];
$durationLabel = $durationLabels[$duration] ?? $duration;

$staffPayload = [
    'client_name' => $client_name,
    'company' => $company,
    'email' => $email,
    'phone' => $phone_normalized,
    'task' => $task,
    'start_date' => $startDate,
    'worker_timeline' => $duration,
    'workers' => [
        [
            'role' => $specialist,
            'level' => $level,
            'cost_per_hour' => $cost_per_hour,
            'amount_of_workers' => $amount_of_workers,
        ],
    ],
];

if ($group_id !== '') {
    $staffPayload['group_id'] = $group_id;
}
if ($page_url !== '') {
    $staffPayload['page_url'] = $page_url;
}
if ($form_id !== '') {
    $staffPayload['form_id'] = $form_id;
}

$taskParts = [
    'Заявка на специалиста',
    'Специалист: ' . $specialist . ($level !== '' ? ' (' . $level . ')' : ''),
    'Количество: ' . $amount_of_workers,
];
if ($cost_per_hour !== '') {
    $taskParts[] = 'Ставка: ' . $cost_per_hour . ' руб/час';
}
if ($startDate !== '') {
    $taskParts[] = 'Дата начала: ' . $startDate;
}
if ($durationLabel !== '') {
    $taskParts[] = 'Срок работы: ' . $durationLabel;
}
$taskParts[] = 'Описание задачи: ' . $task;
if ($page_url !== '') {
    $taskParts[] = 'Страница: ' . $page_url;
}
if ($form_id !== '') {
    $taskParts[] = 'Form ID: ' . $form_id;
}

$hotSalePayload = [
    'name' => $client_name,
    'company' => $company,
    'email' => $email,
    'phone' => $phone_normalized,
    'task' => implode("\n", $taskParts),
];

if ($group_id !== '') {
    $hotSalePayload['group_id'] = $group_id;
}
if ($page_url !== '') {
    $hotSalePayload['page_url'] = $page_url;
}

AddMessage2Log(serialize(tacticum_rest_mask_pii($data)), 'tacticum_sale_staff_data');
AddMessage2Log(serialize(tacticum_rest_mask_pii($staffPayload)), 'tacticum_sale_staff_staff_payload');
AddMessage2Log(serialize(tacticum_rest_mask_pii($hotSalePayload)), 'tacticum_sale_staff_hot_sale_payload');

$base_url = tacticum_rest_get_required_https_ai_url('AI_SERVICE_BASE_URL');
$endpoint_url = tacticum_rest_build_url($base_url, '/tacticum/v1/chat_agent/sale');

$ch = curl_init($endpoint_url);
tacticum_rest_apply_curl_defaults($ch);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($hotSalePayload, JSON_UNESCAPED_UNICODE));

$response = curl_exec($ch);
$curl_error_no = curl_errno($ch);
$curl_error = curl_error($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
tacticum_rest_log_tls_error($ch, 'tacticum_sale_staff_hot_sale');
curl_close($ch);

$masked_response = is_string($response) ? tacticum_rest_mask_string($response) : $response;
AddMessage2Log(serialize($masked_response), 'tacticum_sale_staff_response');

if ($curl_error_no !== 0) {
    AddMessage2Log("Curl error (tacticum_sale_staff_hot_sale): errno={$curl_error_no}; error={$curl_error}", 'tacticum_sale_staff_error');
    tacticum_rest_error(502, 'curl_error', 'Ошибка отправки во внешний сервис.');
}

if ($http_status !== 200 || !$response) {
    $error_text = is_string($response) && $response ? tacticum_rest_mask_string($response) : 'AI endpoint error';
    AddMessage2Log($error_text, 'tacticum_sale_staff_upstream_error');
    tacticum_rest_error(502, 'upstream_error', 'Ошибка отправки во внешний сервис.');
}

tacticum_rest_response(true, 'ok', null);
