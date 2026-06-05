<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'Tacticum\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $documentRoot = (string)($_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($documentRoot === '') {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', '/', $relativeClass);
    $path = $documentRoot . '/local/lib/Tacticum/' . $relativePath . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});
