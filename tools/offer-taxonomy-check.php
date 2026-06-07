#!/usr/bin/env php
<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Tacticum\Offer\OfferTaxonomyService;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumOfferTaxonomyCheck
{
    private array $errors = [];
    private array $warnings = [];

    public function __construct(private bool $strict, private bool $json, private string $documentRoot) {}

    public function run(): int
    {
        $this->loadBitrix();
        if (!Loader::includeModule('iblock')) {
            $this->errors[] = 'Bitrix iblock module is not available.';
        }

        $config = function_exists('tacticum_rest_get_config_section') ? tacticum_rest_get_config_section('offer') : [];
        $source = (string)($config['taxonomy_source'] ?? 'fallback');
        $allowFallback = (bool)($config['allow_taxonomy_fallback'] ?? true);
        $cacheTtl = isset($config['taxonomy_cache_ttl']) ? (int)$config['taxonomy_cache_ttl'] : null;
        $termsIblockId = function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id('offer_taxonomy_terms') : 0;

        foreach (function_exists('tacticum_rest_validate_config') ? tacticum_rest_validate_config(['offer']) : [] as $error) {
            $this->errors[] = 'config.' . ($error['key'] ?? 'unknown') . ':' . ($error['code'] ?? 'unknown');
        }
        if ($this->strict) {
            if ($source !== 'bitrix') {
                $this->errors[] = 'Strict mode requires offer.taxonomy_source=bitrix.';
            }
            if ($allowFallback) {
                $this->errors[] = 'Strict mode requires offer.allow_taxonomy_fallback=false.';
            }
        }

        $schema = $this->schemaSummary($termsIblockId);
        $rows = $this->rowSummary($termsIblockId);
        $runtime = class_exists(OfferTaxonomyService::class) ? OfferTaxonomyService::model() : ['source' => 'none', 'termsByDimension' => []];

        if ($source === 'bitrix' || $this->strict) {
            if ($termsIblockId <= 0) {
                $this->errors[] = 'Missing iblock config key offer_taxonomy_terms.';
            }
            if (!$schema['valid']) {
                $this->errors[] = 'Offer taxonomy schema is incomplete.';
            }
            foreach (['sector', 'scenario', 'phase'] as $dimension) {
                if (($rows['active_by_dimension'][$dimension] ?? 0) < 1) {
                    $this->errors[] = "No active {$dimension} taxonomy terms found.";
                }
            }
        } elseif ($termsIblockId <= 0) {
            $this->warnings[] = 'Offer taxonomy terms iblock is not configured; runtime uses fallback while allowed.';
        } elseif ($schema['valid'] && ($rows['active_total'] ?? 0) < 1) {
            $this->errors[] = 'Configured offer taxonomy terms iblock has no readable active taxonomy terms.';
        }

        $summary = [
            'schema' => 'tacticum.offer.taxonomy_check.v1',
            'strict' => $this->strict,
            'valid' => $this->errors === [],
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'config' => ['taxonomy_source' => $source, 'taxonomy_cache_ttl' => $cacheTtl, 'allow_taxonomy_fallback' => $allowFallback],
            'iblocks' => ['offer_taxonomy_terms' => $termsIblockId],
            'schema_summary' => $schema,
            'row_summary' => $rows,
            'runtime' => [
                'source' => (string)($runtime['source'] ?? 'none'),
                'terms_by_dimension' => array_map('count', (array)($runtime['termsByDimension'] ?? [])),
            ],
        ];

        if ($this->json) {
            echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        } else {
            $this->printSummary($summary);
        }

        return $summary['valid'] ? 0 : 1;
    }

    private function loadBitrix(): void
    {
        $prolog = $this->documentRoot . '/bitrix/modules/main/include/prolog_before.php';
        if (!is_file($prolog)) {
            throw new RuntimeException('Bitrix prolog_before.php not found under document root: ' . $this->documentRoot);
        }
        $_SERVER['DOCUMENT_ROOT'] = $this->documentRoot;
        define('NO_KEEP_STATISTIC', true);
        define('NOT_CHECK_PERMISSIONS', true);
        require_once $prolog;
    }

    private function schemaSummary(int $iblockId): array
    {
        $required = ['DIMENSION', 'PUBLIC_LABEL', 'SHORT_LABEL', 'ALIASES', 'FEATURED', 'PRODUCT_FAMILY', 'BUDGET_MIN', 'BUDGET_MAX'];
        $properties = $this->propertyCodes($iblockId);
        $missing = array_values(array_diff($required, $properties));
        return ['valid' => $iblockId > 0 && $missing === [], 'missing_properties' => $missing];
    }

    private function rowSummary(int $iblockId): array
    {
        $summary = [
            'active_elements_seen' => 0,
            'active_total' => 0,
            'active_by_dimension' => ['sector' => 0, 'scenario' => 0, 'phase' => 0, 'budget' => 0],
            'featured_total' => 0,
            'duplicate_aliases' => 0,
            'unknown_dimension_rows' => 0,
        ];
        if ($iblockId <= 0) {
            return $summary;
        }

        $aliases = [];
        $result = CIBlockElement::GetList(['SORT' => 'ASC'], ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'], false, false, ['ID', 'IBLOCK_ID', 'CODE']);
        while ($element = $result->GetNextElement()) {
            $summary['active_elements_seen']++;
            $properties = $element->GetProperties();
            $dimension = trim((string)($properties['DIMENSION']['VALUE'] ?? ''));
            if (!array_key_exists($dimension, $summary['active_by_dimension'])) {
                $summary['unknown_dimension_rows']++;
                continue;
            }
            $summary['active_total']++;
            $summary['active_by_dimension'][$dimension]++;
            if ($this->truthy((string)($properties['FEATURED']['VALUE'] ?? ''))) {
                $summary['featured_total']++;
            }
            foreach ((array)($properties['ALIASES']['VALUE'] ?? []) as $alias) {
                $key = $dimension . ':' . mb_strtolower(trim((string)$alias));
                if (isset($aliases[$key])) {
                    $summary['duplicate_aliases']++;
                }
                $aliases[$key] = true;
            }
        }
        if ($summary['duplicate_aliases'] > 0) {
            $this->errors[] = 'Duplicate taxonomy aliases found.';
        }
        if ($summary['unknown_dimension_rows'] > 0) {
            $this->errors[] = 'Offer taxonomy rows with unknown or unreadable DIMENSION found.';
        }

        return $summary;
    }

    private function propertyCodes(int $iblockId): array
    {
        if ($iblockId <= 0) {
            return [];
        }
        $codes = [];
        $result = CIBlockProperty::GetList(['SORT' => 'ASC'], ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y']);
        while ($property = $result->Fetch()) {
            $codes[] = trim((string)($property['CODE'] ?? ''));
        }
        return array_values(array_filter($codes));
    }

    private function truthy(string $value): bool { return in_array(mb_strtolower(trim($value)), ['1', 'y', 'yes', 'да', 'true'], true); }

    private function printSummary(array $summary): void
    {
        echo 'Offer Taxonomy Check' . PHP_EOL;
        echo 'Valid: ' . ($summary['valid'] ? 'yes' : 'no') . PHP_EOL;
        echo 'Mode: source=' . $summary['config']['taxonomy_source'] . ', fallback=' . ($summary['config']['allow_taxonomy_fallback'] ? 'true' : 'false') . ', cache_ttl=' . (string)$summary['config']['taxonomy_cache_ttl'] . PHP_EOL;
        echo 'Iblocks: offer_taxonomy_terms=#' . $summary['iblocks']['offer_taxonomy_terms'] . PHP_EOL;
        echo 'Rows: active=' . $summary['row_summary']['active_total']
            . ', featured=' . $summary['row_summary']['featured_total']
            . ', seen=' . $summary['row_summary']['active_elements_seen']
            . ', unknown_dimension=' . $summary['row_summary']['unknown_dimension_rows']
            . PHP_EOL;
        echo 'Runtime: source=' . $summary['runtime']['source'] . ', terms=' . json_encode($summary['runtime']['terms_by_dimension'], JSON_UNESCAPED_UNICODE) . PHP_EOL;
        foreach ($summary['warnings'] as $warning) { echo 'WARNING: ' . $warning . PHP_EOL; }
        foreach ($summary['errors'] as $error) { echo 'ERROR: ' . $error . PHP_EOL; }
    }
}

function tacticum_offer_taxonomy_check_options(array $argv): array
{
    $options = ['strict' => false, 'json' => false, 'document_root' => dirname(__DIR__), 'help' => false];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') { $options['help'] = true; continue; }
        if ($argument === '--strict') { $options['strict'] = true; continue; }
        if ($argument === '--json') { $options['json'] = true; continue; }
        if (str_starts_with($argument, '--document-root=')) { $options['document_root'] = substr($argument, 16); continue; }
        throw new InvalidArgumentException('Unknown argument: ' . $argument);
    }
    $documentRoot = realpath((string)$options['document_root']);
    if ($documentRoot === false) { throw new RuntimeException('Document root does not exist.'); }
    $options['document_root'] = $documentRoot;
    return $options;
}

try {
    $options = tacticum_offer_taxonomy_check_options($argv);
    if ($options['help']) {
        echo "Usage:\n  php tools/offer-taxonomy-check.php [--strict] [--json] [--document-root=/path]\n";
        exit(0);
    }
    $exitCode = (new TacticumOfferTaxonomyCheck((bool)$options['strict'], (bool)$options['json'], (string)$options['document_root']))->run();
    exit($exitCode);
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
