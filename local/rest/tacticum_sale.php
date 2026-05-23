<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');
header('Deprecation: true');
header('Sunset: Wed, 30 Sep 2026 00:00:00 GMT');
header('Link: </local/rest/tacticum_form.php>; rel="successor-version"');

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_sale');
tacticum_rest_require_method('POST');

$data = tacticum_rest_read_json_body();
tacticum_rest_check_csrf($data);

$name = trim((string)($data['name'] ?? ''));
$company = trim((string)($data['company'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$task = trim((string)($data['task'] ?? ''));
$group_id = trim((string)($data['group_id'] ?? ''));

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
if ($group_id !== '' && mb_strlen($group_id) > 64) {
    $errors[] = 'group_id';
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
    'group_id' => $group_id,
];

$result = tacticum_rest_submit_chat_agent_sale($payload, 'tacticum_sale');

if (tacticum_rest_is_successful_upstream_response($result)) {
    echo json_encode(['success' => true]);
    exit;
}

tacticum_rest_fail_chat_agent_sale_upstream($result, 'tacticum_sale');
