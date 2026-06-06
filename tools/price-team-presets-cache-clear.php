#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

function tacticum_price_team_presets_cache_clear_usage(): string
{
    return <<<TEXT
Usage:
  php tools/price-team-presets-cache-clear.php [--dry-run] [--json] [--document-root=/path/to/site]

Clears price team presets runtime cache, managed-cache tags, price news.list component
cache, Bitrix composite HTML cache and template asset cache. Use after team preset
migration/finalize, admin preset edits, price template deploy or rollback.

TEXT;
}

function tacticum_price_team_presets_cache_clear_options(array $argv): array
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

function tacticum_price_team_presets_cache_clear_line(string $message): void
{
    echo $message . PHP_EOL;
}

function tacticum_price_team_presets_cache_clear_json(array $payload): void
{
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

function tacticum_price_team_presets_cache_clear_path_status(string $documentRoot, string $relativePath): array
{
    $path = rtrim($documentRoot, '/') . '/' . ltrim($relativePath, '/');

    return [
        'relative_path' => $relativePath,
        'path' => $path,
        'exists' => file_exists($path),
        'cleared' => false,
    ];
}

function tacticum_price_team_presets_cache_clear_is_safe_path(string $documentRoot, string $path): bool
{
    $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/') . '/';
    $path = str_replace('\\', '/', $path);
    $htmlPagesPath = rtrim($documentRoot . 'bitrix/html_pages', '/');

    return str_starts_with($path, $documentRoot . 'bitrix/cache/')
        || $path === $htmlPagesPath
        || str_starts_with($path, $htmlPagesPath . '/');
}

function tacticum_price_team_presets_cache_clear_delete_path(string $path): void
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

function tacticum_price_team_presets_cache_clear_delete_contents(string $documentRoot, string $path): bool
{
    if (!file_exists($path) || !is_dir($path)) {
        return false;
    }
    if (!tacticum_price_team_presets_cache_clear_is_safe_path($documentRoot, $path)) {
        throw new RuntimeException('Refusing to clear unsafe cache path: ' . $path);
    }

    $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        tacticum_price_team_presets_cache_clear_delete_path($item->getPathname());
    }

    return true;
}

try {
    $options = tacticum_price_team_presets_cache_clear_options($argv);
    if ($options['help']) {
        echo tacticum_price_team_presets_cache_clear_usage();
        exit(0);
    }

    $documentRoot = (string)$options['document_root'];
    $prolog = $documentRoot . '/bitrix/modules/main/include/prolog_before.php';
    if (!is_file($prolog)) {
        throw new RuntimeException('Bitrix prolog not found: ' . $prolog);
    }

    $_SERVER['DOCUMENT_ROOT'] = $documentRoot;
    $_SERVER['REQUEST_METHOD'] = 'CLI';
    define('NO_KEEP_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);

    require $prolog;

    $runtimePath = $documentRoot . '/local/php_interface/include/price_team_presets.php';
    if (is_file($runtimePath)) {
        require_once $runtimePath;
    }

    if (!function_exists('tacticum_price_team_presets_clear_cache')) {
        throw new RuntimeException('Price team presets cache helper tacticum_price_team_presets_clear_cache() is unavailable.');
    }

    $priceConfig = function_exists('tacticum_rest_get_config_section')
        ? tacticum_rest_get_config_section('price')
        : [];
    $source = (string)($priceConfig['team_presets_source'] ?? 'fallback');
    $fallbackAllowed = (bool)($priceConfig['allow_team_presets_fallback'] ?? true);
    $ttl = (int)($priceConfig['team_presets_cache_ttl'] ?? 300);
    $cacheDir = function_exists('tacticum_price_team_presets_cache_dir')
        ? tacticum_price_team_presets_cache_dir()
        : '/tacticum/price_team_presets';
    $iblockIds = function_exists('tacticum_price_team_presets_related_iblock_ids')
        ? tacticum_price_team_presets_related_iblock_ids()
        : [];
    $managedTags = array_map(static fn (int $id): string => 'iblock_id_' . $id, array_map('intval', $iblockIds));
    $renderCachePaths = [
        tacticum_price_team_presets_cache_clear_path_status($documentRoot, 'bitrix/cache/s1/bitrix/news.list'),
        tacticum_price_team_presets_cache_clear_path_status($documentRoot, 'bitrix/html_pages'),
        tacticum_price_team_presets_cache_clear_path_status($documentRoot, 'bitrix/cache/js/s1/tacticum'),
        tacticum_price_team_presets_cache_clear_path_status($documentRoot, 'bitrix/cache/css/s1/tacticum'),
    ];

    $payload = [
        'success' => true,
        'dry_run' => (bool)$options['dry_run'],
        'cache_cleared' => false,
        'source_mode' => $source,
        'fallback_allowed' => $fallbackAllowed,
        'cache_ttl' => $ttl,
        'cache_dir' => $cacheDir,
        'iblock_ids' => array_values(array_map('intval', $iblockIds)),
        'managed_tags' => $managedTags,
        'render_cache_paths' => $renderCachePaths,
        'warnings' => [],
        'errors' => [],
    ];

    if (!$options['dry_run']) {
        tacticum_price_team_presets_clear_cache(0, true);
        foreach ($payload['render_cache_paths'] as &$pathInfo) {
            $existedBefore = (bool)$pathInfo['exists'];
            $pathInfo['cleared'] = tacticum_price_team_presets_cache_clear_delete_contents($documentRoot, (string)$pathInfo['path']);
            $pathInfo['exists_after'] = file_exists((string)$pathInfo['path']);
            $pathInfo['cleared_by'] = $pathInfo['cleared'] ? 'filesystem' : 'none';
            if (!$pathInfo['cleared'] && $existedBefore && !$pathInfo['exists_after']) {
                $pathInfo['cleared'] = true;
                $pathInfo['cleared_by'] = 'bitrix_api';
            }
        }
        unset($pathInfo);
        $payload['cache_cleared'] = true;
    }

    if ($options['json']) {
        tacticum_price_team_presets_cache_clear_json($payload);
        exit(0);
    }

    tacticum_price_team_presets_cache_clear_line('Price team presets cache clear');
    tacticum_price_team_presets_cache_clear_line('Document root: ' . $documentRoot);
    tacticum_price_team_presets_cache_clear_line('Source mode: ' . $source);
    tacticum_price_team_presets_cache_clear_line('Fallback allowed: ' . ($fallbackAllowed ? 'yes' : 'no'));
    tacticum_price_team_presets_cache_clear_line('Cache TTL: ' . $ttl);
    tacticum_price_team_presets_cache_clear_line('Cache dir: ' . $cacheDir);
    tacticum_price_team_presets_cache_clear_line('Managed tags: ' . ($managedTags === [] ? '-' : implode(', ', $managedTags)));
    tacticum_price_team_presets_cache_clear_line('Render cache paths:');
    foreach ($payload['render_cache_paths'] as $pathInfo) {
        $status = $pathInfo['exists'] ? 'exists' : 'missing';
        if (!$options['dry_run']) {
            $status .= $pathInfo['cleared'] ? ', cleared' : ', not cleared';
            if (($pathInfo['cleared_by'] ?? 'none') !== 'none') {
                $status .= ' by ' . $pathInfo['cleared_by'];
            }
        }
        tacticum_price_team_presets_cache_clear_line('- ' . $pathInfo['relative_path'] . ': ' . $status);
    }

    if ($options['dry_run']) {
        tacticum_price_team_presets_cache_clear_line('');
        tacticum_price_team_presets_cache_clear_line('Dry run: cache was not cleared.');
        exit(0);
    }

    tacticum_price_team_presets_cache_clear_line('');
    tacticum_price_team_presets_cache_clear_line('Price team presets cache clear completed.');
    exit(0);
} catch (Throwable $exception) {
    if (in_array('--json', $argv ?? [], true)) {
        tacticum_price_team_presets_cache_clear_json([
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
