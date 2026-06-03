#!/usr/bin/env php
<?php
declare(strict_types=1);

function tacticum_product_content_cache_clear_usage(): string
{
    return <<<TEXT
Usage:
  php tools/product-content-cache-clear.php [--dry-run] [--document-root=/path/to/site]

Clears the Bitrix product content cache directory and managed-cache tags for configured
product content iblocks. Use after product content migration, admin content edits,
or products.source switch/rollback.

TEXT;
}

function tacticum_product_content_cache_clear_options(array $argv): array
{
    $options = [
        'document_root' => dirname(__DIR__),
        'dry_run' => false,
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

    if (!function_exists('tacticum_product_content_clear_cache')) {
        throw new RuntimeException('Product content cache helper tacticum_product_content_clear_cache() is unavailable.');
    }

    $cacheDir = function_exists('tacticum_product_content_cache_dir')
        ? tacticum_product_content_cache_dir()
        : '/tacticum/product_content';
    $source = function_exists('tacticum_product_content_source')
        ? tacticum_product_content_source()
        : 'unknown';
    $ttl = function_exists('tacticum_product_content_cache_ttl')
        ? tacticum_product_content_cache_ttl()
        : 0;
    $iblockIds = function_exists('tacticum_product_content_related_iblock_ids')
        ? tacticum_product_content_related_iblock_ids()
        : [];

    tacticum_product_content_cache_clear_line('Product content cache clear');
    tacticum_product_content_cache_clear_line('Document root: ' . $documentRoot);
    tacticum_product_content_cache_clear_line('Cache dir: ' . $cacheDir);
    tacticum_product_content_cache_clear_line('Product source mode: ' . $source);
    tacticum_product_content_cache_clear_line('Product cache TTL: ' . $ttl);
    tacticum_product_content_cache_clear_line('Managed tags: ' . (empty($iblockIds)
        ? '-'
        : implode(', ', array_map(static fn (int $id): string => 'iblock_id_' . $id, $iblockIds))));

    if ($options['dry_run']) {
        tacticum_product_content_cache_clear_line('');
        tacticum_product_content_cache_clear_line('Dry run: cache was not cleared.');
        exit(0);
    }

    tacticum_product_content_clear_cache();

    tacticum_product_content_cache_clear_line('');
    tacticum_product_content_cache_clear_line('Product content cache clear completed.');
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
