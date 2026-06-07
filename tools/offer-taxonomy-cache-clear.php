#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

function tacticum_offer_taxonomy_cache_clear_options(array $argv): array
{
    $options = ['document_root' => dirname(__DIR__), 'dry_run' => false, 'json' => false, 'help' => false];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') { $options['help'] = true; continue; }
        if ($argument === '--dry-run') { $options['dry_run'] = true; continue; }
        if ($argument === '--json') { $options['json'] = true; continue; }
        if (str_starts_with($argument, '--document-root=')) { $options['document_root'] = substr($argument, 16); continue; }
        throw new InvalidArgumentException('Unknown argument: ' . $argument);
    }
    $documentRoot = realpath((string)$options['document_root']);
    if ($documentRoot === false) { throw new RuntimeException('Document root does not exist.'); }
    $options['document_root'] = $documentRoot;
    return $options;
}

function tacticum_offer_taxonomy_cache_clear_path(string $documentRoot, string $relativePath): array
{
    $path = rtrim($documentRoot, '/') . '/' . ltrim($relativePath, '/');
    return ['relative_path' => $relativePath, 'path' => $path, 'exists' => file_exists($path), 'cleared' => false];
}

function tacticum_offer_taxonomy_cache_clear_is_safe(string $documentRoot, string $path): bool
{
    $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/') . '/';
    $path = str_replace('\\', '/', $path);
    $htmlPagesPath = rtrim($documentRoot . 'bitrix/html_pages', '/');
    return str_starts_with($path, $documentRoot . 'bitrix/cache/') || $path === $htmlPagesPath || str_starts_with($path, $htmlPagesPath . '/');
}

function tacticum_offer_taxonomy_cache_clear_delete(string $path): void
{
    if (!file_exists($path)) { return; }
    if (is_link($path) || is_file($path)) { unlink($path); return; }
    if (!is_dir($path)) { return; }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $item) {
        $itemPath = $item->getPathname();
        $item->isDir() && !$item->isLink() ? rmdir($itemPath) : unlink($itemPath);
    }
    rmdir($path);
}

function tacticum_offer_taxonomy_cache_clear_contents(string $documentRoot, string $path): bool
{
    if (!file_exists($path) || !is_dir($path)) { return false; }
    if (!tacticum_offer_taxonomy_cache_clear_is_safe($documentRoot, $path)) {
        throw new RuntimeException('Refusing to clear unsafe cache path: ' . $path);
    }
    foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
        tacticum_offer_taxonomy_cache_clear_delete($item->getPathname());
    }
    return true;
}

try {
    $options = tacticum_offer_taxonomy_cache_clear_options($argv);
    if ($options['help']) {
        echo "Usage:\n  php tools/offer-taxonomy-cache-clear.php [--dry-run] [--json] [--document-root=/path]\n";
        exit(0);
    }

    $documentRoot = (string)$options['document_root'];
    $prolog = $documentRoot . '/bitrix/modules/main/include/prolog_before.php';
    if (!is_file($prolog)) { throw new RuntimeException('Bitrix prolog not found: ' . $prolog); }
    $_SERVER['DOCUMENT_ROOT'] = $documentRoot;
    $_SERVER['REQUEST_METHOD'] = 'CLI';
    define('NO_KEEP_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);
    require $prolog;

    $runtimePath = $documentRoot . '/local/php_interface/include/offer_catalog_cache.php';
    if (is_file($runtimePath)) { require_once $runtimePath; }
    if (!function_exists('tacticum_offer_catalog_clear_cache')) {
        throw new RuntimeException('Offer catalog cache helper is unavailable.');
    }

    $offerConfig = function_exists('tacticum_rest_get_config_section') ? tacticum_rest_get_config_section('offer') : [];
    $iblockIds = function_exists('tacticum_offer_catalog_related_cache_iblock_ids') ? tacticum_offer_catalog_related_cache_iblock_ids() : [];
    $paths = [
        tacticum_offer_taxonomy_cache_clear_path($documentRoot, 'bitrix/cache/tacticum'),
        tacticum_offer_taxonomy_cache_clear_path($documentRoot, 'bitrix/cache/s1/bitrix/news.list'),
        tacticum_offer_taxonomy_cache_clear_path($documentRoot, 'bitrix/html_pages'),
        tacticum_offer_taxonomy_cache_clear_path($documentRoot, 'bitrix/cache/js/s1/tacticum'),
        tacticum_offer_taxonomy_cache_clear_path($documentRoot, 'bitrix/cache/css/s1/tacticum'),
    ];
    $payload = [
        'success' => true,
        'dry_run' => (bool)$options['dry_run'],
        'cache_cleared' => false,
        'source_mode' => (string)($offerConfig['taxonomy_source'] ?? 'fallback'),
        'fallback_allowed' => (bool)($offerConfig['allow_taxonomy_fallback'] ?? true),
        'cache_ttl' => (int)($offerConfig['taxonomy_cache_ttl'] ?? 300),
        'iblock_ids' => array_values(array_map('intval', $iblockIds)),
        'managed_tags' => array_map(static fn(int $id): string => 'iblock_id_' . $id, array_map('intval', $iblockIds)),
        'render_cache_paths' => $paths,
    ];

    if (!$options['dry_run']) {
        tacticum_offer_catalog_clear_cache(0);
        foreach ($payload['render_cache_paths'] as &$pathInfo) {
            $pathInfo['cleared'] = tacticum_offer_taxonomy_cache_clear_contents($documentRoot, (string)$pathInfo['path']);
            $pathInfo['exists_after'] = file_exists((string)$pathInfo['path']);
        }
        unset($pathInfo);
        $payload['cache_cleared'] = true;
    }

    if ($options['json']) {
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        echo 'Offer taxonomy cache clear' . PHP_EOL;
        echo 'Document root: ' . $documentRoot . PHP_EOL;
        echo 'Source mode: ' . $payload['source_mode'] . PHP_EOL;
        echo 'Fallback allowed: ' . ($payload['fallback_allowed'] ? 'yes' : 'no') . PHP_EOL;
        echo 'Managed tags: ' . implode(', ', $payload['managed_tags']) . PHP_EOL;
        echo $options['dry_run'] ? 'Dry-run completed.' . PHP_EOL : 'Offer taxonomy cache clear completed.' . PHP_EOL;
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
