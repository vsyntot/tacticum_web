<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=UTF-8');

$data = json_decode(file_get_contents('php://input'), true);
$user_message = $data['user_message'] ?? '';

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

AddMessage2Log(serialize($data), "data");
AddMessage2Log(serialize($payload), "request");

$ch = curl_init('http://5.35.90.193:8000/tacticum/v1/chat_agent');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

AddMessage2Log(serialize($response), "response");

if ($http_status !== 200 || !$response) {
    http_response_code(500);
    echo json_encode(['error' => 'AI endpoint error']);
    exit;
}

echo $response;