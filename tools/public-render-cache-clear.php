#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

function tacticum_public_render_cache_clear_usage(): string
{
    return <<<TEXT
Usage:
  php tools/public-render-cache-clear.php [--dry-run] [--json] [--document-root=/path/to/site]

Clears public rendered output caches that can keep stale menu/template/page HTML after
footer, public page partial, page-content renderer or template asset deploys. This does
not change business data, Bitrix content rows or product runtime data.

TEXT;
}

function tacticum_public_render_cache_clear_options(array $argv): array
{
    $options = [
        'document_root' => dirname(__DIR__),
        'dry_run' => false,
        'json' => false,
        'help' => false,
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $options['help'] = true;
            continue;
        }
        if ($argument === '--dry-run') {
            $options['dry_run'] = true;
            continue;
        }
        if ($argument === '--json') {
            $options['json'] = true;
            continue;
        }
        if (str_starts_with($argument, '--document-root=')) {
            $options['document_root'] = substr($argument, strlen('--document-root='));
            continue;
        }

        throw new InvalidArgumentException('Unknown argument: ' . $argument);
    }

    $documentRoot = realpath((string)$options['document_root']);
    if ($documentRoot === false) {
        throw new RuntimeException('Document root does not exist: ' . (string)$options['document_root']);
    }
    $options['document_root'] = $documentRoot;

    return $options;
}

function tacticum_public_render_cache_clear_line(string $message): void
{
    echo $message . PHP_EOL;
}

function tacticum_public_render_cache_clear_json(array $payload): void
{
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

function tacticum_public_render_cache_clear_path_status(string $documentRoot, string $relativePath): array
{
    $path = rtrim($documentRoot, '/') . '/' . ltrim($relativePath, '/');

    return [
        'relative_path' => $relativePath,
        'path' => $path,
        'exists' => file_exists($path),
        'cleared' => false,
        'cleared_by' => 'none',
    ];
}

function tacticum_public_render_cache_clear_is_safe_path(string $documentRoot, string $path): bool
{
    $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/') . '/';
    $path = str_replace('\\', '/', $path);
    $safeRoots = [
        rtrim($documentRoot . 'bitrix/cache', '/'),
        rtrim($documentRoot . 'bitrix/managed_cache', '/'),
        rtrim($documentRoot . 'bitrix/html_pages', '/'),
    ];

    foreach ($safeRoots as $safeRoot) {
        if ($path === $safeRoot || str_starts_with($path, $safeRoot . '/')) {
            return true;
        }
    }

    return false;
}

function tacticum_public_render_cache_clear_delete_path(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_link($path) || is_file($path)) {
        unlink($path);
        return;
    }

    if (!is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        $itemPath = $item->getPathname();
        if ($item->isDir() && !$item->isLink()) {
            rmdir($itemPath);
        } else {
            unlink($itemPath);
        }
    }

    rmdir($path);
}

function tacticum_public_render_cache_clear_delete_contents(string $documentRoot, string $path): bool
{
    if (!file_exists($path) || !is_dir($path)) {
        return false;
    }
    if (!tacticum_public_render_cache_clear_is_safe_path($documentRoot, $path)) {
        throw new RuntimeException('Refusing to clear unsafe cache path: ' . $path);
    }

    $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        tacticum_public_render_cache_clear_delete_path($item->getPathname());
    }

    return true;
}

try {
    $options = tacticum_public_render_cache_clear_options($argv);
    if ($options['help']) {
        echo tacticum_public_render_cache_clear_usage();
        exit(0);
    }

    $documentRoot = (string)$options['document_root'];
    $renderCachePaths = [
        tacticum_public_render_cache_clear_path_status($documentRoot, 'bitrix/managed_cache'),
        tacticum_public_render_cache_clear_path_status($documentRoot, 'bitrix/cache/tacticum'),
        tacticum_public_render_cache_clear_path_status($documentRoot, 'bitrix/cache/s1/bitrix/menu'),
        tacticum_public_render_cache_clear_path_status($documentRoot, 'bitrix/cache/s1/bitrix/news.list'),
        tacticum_public_render_cache_clear_path_status($documentRoot, 'bitrix/cache/s1/bitrix/news.detail'),
        tacticum_public_render_cache_clear_path_status($documentRoot, 'bitrix/html_pages'),
        tacticum_public_render_cache_clear_path_status($documentRoot, 'bitrix/cache/css/s1/tacticum'),
        tacticum_public_render_cache_clear_path_status($documentRoot, 'bitrix/cache/js/s1/tacticum'),
    ];
    $componentCacheClears = [];
    $payload = [
        'success' => true,
        'dry_run' => (bool)$options['dry_run'],
        'cache_cleared' => false,
        'document_root' => $documentRoot,
        'render_cache_paths' => $renderCachePaths,
        'component_cache_clears' => $componentCacheClears,
        'warnings' => [],
        'errors' => [],
    ];

    if (!$options['dry_run']) {
        $prolog = $documentRoot . '/bitrix/modules/main/include/prolog_before.php';
        if (is_file($prolog)) {
            $_SERVER['DOCUMENT_ROOT'] = $documentRoot;
            $_SERVER['REQUEST_METHOD'] = 'CLI';
            define('NO_KEEP_STATISTIC', true);
            define('NOT_CHECK_PERMISSIONS', true);
            require $prolog;

            if (class_exists('CBitrixComponent') && method_exists('CBitrixComponent', 'clearComponentCache')) {
                foreach (['bitrix:menu', 'bitrix:news.list', 'bitrix:news.detail'] as $componentName) {
                    \CBitrixComponent::clearComponentCache($componentName);
                    $componentCacheClears[] = $componentName;
                }
            } else {
                $payload['warnings'][] = 'CBitrixComponent::clearComponentCache is unavailable; filesystem cache clear will still run.';
            }
        } else {
            $payload['warnings'][] = 'Bitrix prolog not found; filesystem cache clear will still run.';
        }

        foreach ($payload['render_cache_paths'] as &$pathInfo) {
            $existedBefore = (bool)$pathInfo['exists'];
            $pathInfo['cleared'] = tacticum_public_render_cache_clear_delete_contents($documentRoot, (string)$pathInfo['path']);
            $pathInfo['exists_after'] = file_exists((string)$pathInfo['path']);
            $pathInfo['cleared_by'] = $pathInfo['cleared'] ? 'filesystem' : 'none';
            if (!$pathInfo['cleared'] && $existedBefore && !$pathInfo['exists_after']) {
                $pathInfo['cleared'] = true;
                $pathInfo['cleared_by'] = 'bitrix_api';
            }
        }
        unset($pathInfo);

        $payload['component_cache_clears'] = $componentCacheClears;
        $payload['cache_cleared'] = true;
    }

    if ($options['json']) {
        tacticum_public_render_cache_clear_json($payload);
        exit(0);
    }

    tacticum_public_render_cache_clear_line('Public render cache clear');
    tacticum_public_render_cache_clear_line('Document root: ' . $documentRoot);
    tacticum_public_render_cache_clear_line('Component cache clears: ' . ($componentCacheClears === [] ? '-' : implode(', ', $componentCacheClears)));
    tacticum_public_render_cache_clear_line('Render cache paths:');
    foreach ($payload['render_cache_paths'] as $pathInfo) {
        $status = ($pathInfo['exists'] ? 'exists' : 'missing');
        if (!$options['dry_run']) {
            $status .= $pathInfo['cleared'] ? ', cleared' : ', not cleared';
            if (($pathInfo['cleared_by'] ?? 'none') !== 'none') {
                $status .= ' by ' . $pathInfo['cleared_by'];
            }
        }
        tacticum_public_render_cache_clear_line('- ' . $pathInfo['relative_path'] . ': ' . $status);
    }
    if ($payload['warnings'] !== []) {
        tacticum_public_render_cache_clear_line('Warnings:');
        foreach ($payload['warnings'] as $warning) {
            tacticum_public_render_cache_clear_line('- ' . $warning);
        }
    }
    if ($options['dry_run']) {
        tacticum_public_render_cache_clear_line('');
        tacticum_public_render_cache_clear_line('Dry run: cache was not cleared.');
        exit(0);
    }

    tacticum_public_render_cache_clear_line('');
    tacticum_public_render_cache_clear_line('Public render cache clear completed.');
    exit(0);
} catch (Throwable $exception) {
    if (in_array('--json', $argv ?? [], true)) {
        tacticum_public_render_cache_clear_json([
            'success' => false,
            'dry_run' => in_array('--dry-run', $argv ?? [], true),
            'cache_cleared' => false,
            'errors' => [$exception->getMessage()],
            'warnings' => [],
        ]);
        exit(1);
    }
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
