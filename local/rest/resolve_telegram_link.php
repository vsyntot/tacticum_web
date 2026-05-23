<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('resolve_telegram_link');
tacticum_rest_require_method('POST');

$data = tacticum_rest_read_json_body();
tacticum_rest_check_csrf($data);

$page_url = isset($data['url']) ? trim((string)$data['url']) : '';
$bot_name = isset($data['bot_name']) ? trim((string)$data['bot_name']) : '';

// Мини-валидация
if ($page_url === '' || $bot_name === '' || mb_strlen($page_url) > 1000 || mb_strlen($bot_name) > 200) {
    tacticum_rest_error(400, 'validation_error', 'Missing required fields: url, bot_name');
}

// Мягкая санитизация только для page_url
$sanitized_url = filter_var($page_url, FILTER_SANITIZE_URL);

// bot_name — ПОЛНАЯ исходная ссылка, передаём как есть
$payload = [
    'url'      => $sanitized_url,
    'bot_name' => $bot_name,
];

// Запрос к внешнему сервису
$resolver_base = tacticum_rest_get_required_https_ai_url('TELEGRAM_RESOLVER_URL', 'сервиса Telegram resolver');
$endpoint_url = tacticum_rest_build_url($resolver_base, '/tacticum/v1/chat_agent/get_bot_link');

$result = tacticum_rest_post_json($endpoint_url, $payload, 'resolve_telegram_link');
$response = $result['response'];
$http_status = (int)$result['http_status'];

tacticum_rest_fail_on_curl_error($result, 'resolve_telegram_link');

if ($response === false || $http_status !== 200) {
    tacticum_rest_error(502, 'upstream_error', 'Upstream error', ['status' => $http_status]);
}

// Парсим ответ
$decoded = json_decode($response, true);
if (!is_array($decoded)) {
    tacticum_rest_error(502, 'upstream_error', 'Invalid upstream JSON');
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
