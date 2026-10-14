<?php

declare(strict_types=1);

// Only for php -S on loopback with an ISOLATED clone and synthetic database.
if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}
$base = (string)getenv('DIVATECH_REHEARSAL_ROOT');
if ($base === '' || !is_file($base . '/.isolated-rehearsal')) {
    http_response_code(503);
    exit('Isolated rehearsal marker missing.');
}
$host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
$site = $host === 'divatech.space' ? 'divatech' : 'www';
$_SERVER['DOCUMENT_ROOT'] = $base . '/' . $site;
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = '443';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath($_SERVER['DOCUMENT_ROOT'] . $path);
if ($path === '/') { $file = realpath($_SERVER['DOCUMENT_ROOT'] . '/index.php'); }
$_SERVER['SCRIPT_NAME'] = $path === '/' ? '/index.php' : $path;
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$_SERVER['SCRIPT_FILENAME'] = $file ?: $_SERVER['DOCUMENT_ROOT'] . '/404.php';
if ($file && is_file($file) && str_starts_with($file, realpath($base) . '/')) {
    if (str_ends_with($file, '.php')) { require $file; return; }
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $types = ['css' => 'text/css', 'js' => 'application/javascript', 'svg' => 'image/svg+xml', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'ico' => 'image/x-icon', 'xml' => 'application/xml', 'txt' => 'text/plain'];
    if (!isset($types[$ext])) { http_response_code(404); return; }
    header('Content-Type: ' . $types[$ext]); readfile($file); return;
}
require $_SERVER['DOCUMENT_ROOT'] . '/404.php';
