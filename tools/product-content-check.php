#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

final class TacticumProductContentCheck
{
    private string $documentRoot;
    private bool $strict;
    private array $errors = [];
    private array $warnings = [];
    private array $rows = [];

    public function __construct(string $documentRoot, bool $strict)
    {
        $this->documentRoot = rtrim($documentRoot, '/');
        $this->strict = $strict;
    }

    public function run(): int
    {
        if (!Loader::includeModule('iblock')) {
            $this->error('Bitrix iblock module is unavailable.');
            return $this->finish();
        }

        foreach (['products', 'product_blocks', 'product_use_cases'] as $key) {
            $id = $this->iblockId($key);
            if ($id <= 0) {
                $this->error("Missing iblock config key: {$key}");
                continue;
            }

            $this->line("Iblock {$key}: #{$id}");
        }

        $productsIblockId = $this->iblockId('products');
        $this->checkProducts();
        if ($productsIblockId > 0) {
            $this->checkRelationProperties($productsIblockId);
        }

        return $this->finish();
    }

    private function checkProducts(): void
    {
        if (!function_exists('tacticum_product_content_codes')) {
            $this->error('Product content helper tacticum_product_content_codes() is unavailable.');
            return;
        }
        if (!function_exists('tacticum_product_content_bitrix_data')) {
            $this->error('Product content helper tacticum_product_content_bitrix_data() is unavailable.');
            return;
        }

        $source = function_exists('tacticum_product_content_source')
            ? tacticum_product_content_source()
            : 'unknown';
        $this->line("Product source mode: {$source}");
        if ($source === 'fallback') {
            $this->warnOrError('Product source mode is fallback; public product pages will not read Bitrix product content.');
        }
        if (function_exists('tacticum_product_content_cache_ttl')) {
            $this->line('Product cache TTL: ' . tacticum_product_content_cache_ttl());
        }

        foreach (array_keys(tacticum_product_content_codes()) as $productCode) {
            $page = tacticum_product_content_bitrix_data((string)$productCode);
            $minimumRenderable = !empty($page)
                && function_exists('tacticum_product_content_is_minimum_renderable')
                && tacticum_product_content_is_minimum_renderable($page);
            $diagnostics = is_array($page['_diagnostics'] ?? null)
                ? $page['_diagnostics']
                : $this->fallbackDiagnostics($page);
            $missingBlocks = is_array($diagnostics['missing_to_be_blocks'] ?? null)
                ? $diagnostics['missing_to_be_blocks']
                : [];
            $useCaseCount = is_array($page['use_cases']['items'] ?? null)
                ? count($page['use_cases']['items'])
                : 0;

            $this->rows[] = [
                'code' => (string)$productCode,
                'status' => $minimumRenderable ? 'ok' : 'not-renderable',
                'source' => (string)($page['_source'] ?? 'none'),
                'use_cases' => $useCaseCount,
                'missing_blocks' => $missingBlocks,
            ];

            if (!$minimumRenderable) {
                $this->error("Product {$productCode} is not minimum-renderable from Bitrix.");
            }
            if ($useCaseCount <= 0) {
                $this->warnOrError("Product {$productCode} has no Bitrix use cases.");
            }
            if (!empty($missingBlocks)) {
                $this->warnOrError("Product {$productCode} misses TO BE blocks: " . implode(', ', $missingBlocks));
            }
        }
    }

    private function checkRelationProperties(int $productsIblockId): void
    {
        foreach (['faq', 'cases', 'offer', 'services', 'aiagents'] as $key) {
            $iblockId = $this->iblockId($key);
            if ($iblockId <= 0) {
                $this->warning("Skip relation check for {$key}: iblock key is absent.");
                continue;
            }

            $propertyId = $this->propertyId($iblockId, 'PRODUCT');
            if ($propertyId <= 0) {
                $this->warnOrError("Iblock {$key} (#{$iblockId}) has no PRODUCT relation property.");
                continue;
            }

            $linkIblockId = $this->propertyLinkIblockId($propertyId);
            if ($linkIblockId !== $productsIblockId) {
                $this->warnOrError("Iblock {$key} PRODUCT relation points to #{$linkIblockId}, expected #{$productsIblockId}.");
            }
        }
    }

    private function fallbackDiagnostics(array $page): array
    {
        if (function_exists('tacticum_product_content_completeness_diagnostics')) {
            return tacticum_product_content_completeness_diagnostics($page);
        }

        return [
            'minimum_renderable' => false,
            'missing_to_be_blocks' => [],
        ];
    }

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id')
            ? tacticum_rest_get_iblock_id($key)
            : 0;
    }

    private function propertyId(int $iblockId, string $code): int
    {
        $result = CIBlockProperty::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'CODE' => $code,
            ]
        );
        $property = $result->Fetch();

        return is_array($property) ? (int)$property['ID'] : 0;
    }

    private function propertyLinkIblockId(int $propertyId): int
    {
        $result = CIBlockProperty::GetByID($propertyId);
        $property = $result->Fetch();

        return is_array($property) ? (int)($property['LINK_IBLOCK_ID'] ?? 0) : 0;
    }

    private function warnOrError(string $message): void
    {
        if ($this->strict) {
            $this->error($message);
            return;
        }

        $this->warning($message);
    }

    private function error(string $message): void
    {
        $this->errors[] = $message;
    }

    private function warning(string $message): void
    {
        $this->warnings[] = $message;
    }

    private function line(string $message): void
    {
        echo $message . PHP_EOL;
    }

    private function finish(): int
    {
        if (!empty($this->rows)) {
            $this->line('');
            $this->line('Product content rows:');
            foreach ($this->rows as $row) {
                $missing = empty($row['missing_blocks'])
                    ? '-'
                    : implode(',', $row['missing_blocks']);
                $this->line(sprintf(
                    '- %s: %s, source=%s, use_cases=%d, missing_blocks=%s',
                    $row['code'],
                    $row['status'],
                    $row['source'],
                    $row['use_cases'],
                    $missing
                ));
            }
        }

        if (!empty($this->warnings)) {
            $this->line('');
            $this->line('Warnings:');
            foreach ($this->warnings as $warning) {
                $this->line('- ' . $warning);
            }
        }

        if (!empty($this->errors)) {
            $this->line('');
            $this->line('Errors:');
            foreach ($this->errors as $error) {
                $this->line('- ' . $error);
            }
            return 1;
        }

        $this->line('');
        $this->line($this->strict
            ? 'Product content strict check passed.'
            : 'Product content check passed.');

        return 0;
    }
}

function tacticum_product_content_check_usage(): string
{
    return <<<TEXT
Usage:
  php tools/product-content-check.php [--strict] [--document-root=/path/to/site]

Default mode fails only on missing core Bitrix product content. Strict mode also fails on missing TO BE blocks, missing use cases and missing product relation properties.

TEXT;
}

function tacticum_product_content_check_options(array $argv): array
{
    $options = [
        'strict' => false,
        'document_root' => dirname(__DIR__),
        'help' => false,
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $options['help'] = true;
            continue;
        }
        if ($argument === '--strict') {
            $options['strict'] = true;
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

try {
    $options = tacticum_product_content_check_options($argv);
    if ($options['help']) {
        echo tacticum_product_content_check_usage();
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

    $check = new TacticumProductContentCheck($documentRoot, (bool)$options['strict']);
    exit($check->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
