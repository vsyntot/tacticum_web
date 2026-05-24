<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');
tacticum_rest_send_noindex_header();

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('health_config', 5, 60);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    tacticum_rest_error(405, 'method_not_allowed', 'Метод запроса не поддерживается.');
}

$scopes = ['api', 'ai', 'telegram', 'offer', 'content', 'rest', 'security'];
$errors = tacticum_rest_validate_config($scopes);

if (!empty($errors)) {
    tacticum_rest_error(500, 'config_invalid', 'Конфигурация приложения требует проверки.', [
        'errors' => $errors,
        'scopes' => $scopes,
    ]);
}

tacticum_rest_response(true, 'ok', null, [
    'scopes' => $scopes,
]);
