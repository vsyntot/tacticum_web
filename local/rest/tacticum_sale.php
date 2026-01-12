<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=UTF-8');
$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? '';
$company = $data['company'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$task = $data['task'] ?? '';
$group_id = $data['group_id'] ?? '';

if (!$name || !$email || !$phone) {
    echo json_encode(['error' => 'Не заполнены обязательные поля']);
    exit;
}

$payload = [
    'name' => $name,
    'company' => $company,
    'email' => $email,
    'phone' => $phone,
    'task' => $task,
    'group_id' => $group_id,
];

AddMessage2Log(serialize($payload), "sale_request");

$ch = curl_init('http://5.35.90.193:8000/tacticum/v1/chat_agent/sale');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

AddMessage2Log(serialize($response), "sale_response");

if ($http_status === 200 && $response) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Ошибка отправки во внешний сервис']);
}
