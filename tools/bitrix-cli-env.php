<?php

declare(strict_types=1);

function tacticum_tools_reexec_with_short_open_tag(array $argv): void
{
    if (PHP_SAPI !== 'cli' || filter_var(ini_get('short_open_tag'), FILTER_VALIDATE_BOOLEAN)) {
        return;
    }

    $command = [escapeshellarg(PHP_BINARY), '-d', 'short_open_tag=1'];
    foreach ($argv as $argument) {
        $command[] = escapeshellarg((string)$argument);
    }

    passthru(implode(' ', $command), $exitCode);
    exit((int)$exitCode);
}

function tacticum_tools_require_product_content_runtime(string $documentRoot): void
{
    $runtimePath = rtrim($documentRoot, '/') . '/local/php_interface/include/product_content.php';
    if (is_file($runtimePath)) {
        require_once $runtimePath;
    }
}
