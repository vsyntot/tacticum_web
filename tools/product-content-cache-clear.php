#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

function tacticum_product_content_cache_clear_usage(): string
{
    return <<<TEXT
Usage:
  php tools/product-content-cache-clear.php [--dry-run] [--json] [--document-root=/path/to/site]

Clears the Bitrix product content cache directory and managed-cache tags for configured
product content iblocks and product FAQ relations. Use after product content migration,
admin content edits, product FAQ source switch or products.source switch/rollback.

TEXT;
}

function tacticum_product_content_cache_clear_options(array $argv): array
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

function tacticum_product_content_cache_clear_line(string $message): void
{
    echo $message . PHP_EOL;
}

function tacticum_product_content_cache_clear_json(array $payload): void
{
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

try {
    $options = tacticum_product_content_cache_clear_options($argv);
    if ($options['help']) {
        echo tacticum_product_content_cache_clear_usage();
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
    tacticum_tools_require_product_content_runtime($documentRoot);

    if (!function_exists('tacticum_product_content_clear_cache')) {
        throw new RuntimeException('Product content cache helper tacticum_product_content_clear_cache() is unavailable.');
    }

    $cacheDir = function_exists('tacticum_product_content_cache_dir')
        ? tacticum_product_content_cache_dir()
        : '/tacticum/product_content';
    $source = function_exists('tacticum_product_content_source')
        ? tacticum_product_content_source()
        : 'unknown';
    $configuredSource = function_exists('tacticum_product_content_configured_source')
        ? tacticum_product_content_configured_source()
        : $source;
    $fallbackAllowed = function_exists('tacticum_product_content_fallback_allowed')
        && tacticum_product_content_fallback_allowed();
    $ttl = function_exists('tacticum_product_content_cache_ttl')
        ? tacticum_product_content_cache_ttl()
        : 0;
    $schemaVersion = function_exists('tacticum_product_content_schema_version')
        ? tacticum_product_content_schema_version()
        : 'unknown';
    $iblockIds = function_exists('tacticum_product_content_related_iblock_ids')
        ? tacticum_product_content_related_iblock_ids()
        : [];
    $managedTags = empty($iblockIds)
        ? []
        : array_map(static fn (int $id): string => 'iblock_id_' . $id, $iblockIds);
    $payload = [
        'success' => true,
        'dry_run' => (bool)$options['dry_run'],
        'cache_cleared' => false,
        'source_mode' => $source,
        'configured_source' => $configuredSource,
        'fallback_allowed' => $fallbackAllowed,
        'cache_ttl' => $ttl,
        'schema_version' => $schemaVersion,
        'cache_dir' => $cacheDir,
        'iblock_ids' => array_values(array_map('intval', $iblockIds)),
        'managed_tags' => $managedTags,
        'warnings' => [],
        'errors' => [],
    ];

    if ($options['json']) {
        if (!$options['dry_run']) {
            tacticum_product_content_clear_cache();
            $payload['cache_cleared'] = true;
        }
        tacticum_product_content_cache_clear_json($payload);
        exit(0);
    }

    tacticum_product_content_cache_clear_line('Product content cache clear');
    tacticum_product_content_cache_clear_line('Document root: ' . $documentRoot);
    tacticum_product_content_cache_clear_line('Cache dir: ' . $cacheDir);
    tacticum_product_content_cache_clear_line('Product source mode: ' . $source);
    tacticum_product_content_cache_clear_line('Configured product source: ' . $configuredSource);
    tacticum_product_content_cache_clear_line('Product fallback allowed: ' . ($fallbackAllowed ? 'yes' : 'no'));
    tacticum_product_content_cache_clear_line('Product cache TTL: ' . $ttl);
    tacticum_product_content_cache_clear_line('Product schema version: ' . $schemaVersion);
    tacticum_product_content_cache_clear_line('Managed tags: ' . (empty($iblockIds)
        ? '-'
        : implode(', ', $managedTags)));

    if ($options['dry_run']) {
        tacticum_product_content_cache_clear_line('');
        tacticum_product_content_cache_clear_line('Dry run: cache was not cleared.');
        exit(0);
    }

    tacticum_product_content_clear_cache();
    $payload['cache_cleared'] = true;

    if ($options['json']) {
        tacticum_product_content_cache_clear_json($payload);
        exit(0);
    }

    tacticum_product_content_cache_clear_line('');
    tacticum_product_content_cache_clear_line('Product content cache clear completed.');
    exit(0);
} catch (Throwable $exception) {
    if (in_array('--json', $argv ?? [], true)) {
        tacticum_product_content_cache_clear_json([
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
