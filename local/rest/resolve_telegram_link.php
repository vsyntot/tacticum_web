<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=UTF-8');

// Читаем вход
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$page_url = isset($data['url']) ? trim((string)$data['url']) : '';
$bot_name = isset($data['bot_name']) ? trim((string)$data['bot_name']) : '';

// Мини-валидация
if ($page_url === '' || $bot_name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: url, bot_name']);
    exit;
}

// Мягкая санитизация только для page_url
$sanitized_url = filter_var($page_url, FILTER_SANITIZE_URL);

// bot_name — ПОЛНАЯ исходная ссылка, передаём как есть
$payload = [
    'url'      => $sanitized_url,
    'bot_name' => $bot_name,
];

AddMessage2Log('resolve_tg_link input: ' . serialize($data), 'tacticum_resolve_tg');
AddMessage2Log('resolve_tg_link payload: ' . serialize($payload), 'tacticum_resolve_tg');

// Запрос к внешнему сервису
$ch = curl_init('http://5.35.90.193:8000/tacticum/v1/chat_agent/get_bot_link');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err = curl_error($ch);
curl_close($ch);

AddMessage2Log("resolve_tg_link http_status: {$http_status}; resp: " . serialize($response) . "; err: " . $curl_err, 'tacticum_resolve_tg');

if ($response === false || $http_status !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'Upstream error', 'status' => $http_status]);
    exit;
}

// Парсим ответ
$decoded = json_decode($response, true);
if (!is_array($decoded)) {
    http_response_code(502);
    echo json_encode(['error' => 'Invalid upstream JSON']);
    exit;
}

// Апстрим гарантированно возвращает bot_link
$link = isset($decoded['bot_link']) ? trim((string)$decoded['bot_link']) : '';

if ($link === '') {
    http_response_code(204); // нет содержимого — фронт оставит исходный href
    exit;
}

// Успех
http_response_code(200);
echo json_encode(['link' => $link], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
