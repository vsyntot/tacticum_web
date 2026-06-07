<?php

use Tacticum\Offer\CatalogFilters;
use Tacticum\Offer\CatalogMapper;
use Tacticum\Offer\CatalogRepository;
use Tacticum\Offer\CatalogService;
use Tacticum\Offer\CatalogTaxonomy;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

$offerCatalogCacheHelper = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/offer_catalog_cache.php';
if (file_exists($offerCatalogCacheHelper)) {
    require_once $offerCatalogCacheHelper;
}

if (!function_exists('tacticum_offer_catalog_decode_text')) {
    function tacticum_offer_catalog_decode_text(string $value): string { return CatalogMapper::decodeText($value); }
}
if (!function_exists('tacticum_offer_find_element')) {
    function tacticum_offer_find_element(int $offerId, string $offerCode): ?array { return TacticumOfferCatalogService::findElement($offerId, $offerCode); }
}
if (!function_exists('tacticum_offer_catalog_trim')) {
    function tacticum_offer_catalog_trim(string $value): string { return CatalogMapper::trim($value); }
}
if (!function_exists('tacticum_offer_catalog_slug')) {
    function tacticum_offer_catalog_slug(string $label): string { return CatalogMapper::slug($label); }
}
if (!function_exists('tacticum_offer_catalog_property_value')) {
    function tacticum_offer_catalog_property_value(array $properties, string $code): mixed { return CatalogMapper::propertyValue($properties, $code); }
}
if (!function_exists('tacticum_offer_catalog_property_text')) {
    function tacticum_offer_catalog_property_text(array $properties, string $code): string { return CatalogMapper::propertyText($properties, $code); }
}
if (!function_exists('tacticum_offer_catalog_property_html')) {
    function tacticum_offer_catalog_property_html(array $properties, string $code): string { return CatalogMapper::propertyHtml($properties, $code); }
}
if (!function_exists('tacticum_offer_catalog_property_list')) {
    function tacticum_offer_catalog_property_list(array $properties, string $code): array { return CatalogMapper::propertyList($properties, $code); }
}
if (!function_exists('tacticum_offer_catalog_excerpt')) {
    function tacticum_offer_catalog_excerpt(string $text, int $limit = 170): string { return CatalogMapper::excerpt($text, $limit); }
}
if (!function_exists('tacticum_offer_catalog_public_taxonomy_label')) {
    function tacticum_offer_catalog_public_taxonomy_label(string $dimension, string $label): string { return CatalogTaxonomy::publicLabel($dimension, $label); }
}
if (!function_exists('tacticum_offer_catalog_budget_amount')) {
    function tacticum_offer_catalog_budget_amount(string $budgetRaw, array $response): int { return CatalogMapper::budgetAmount($budgetRaw, $response); }
}
if (!function_exists('tacticum_offer_catalog_format_budget_amount')) {
    function tacticum_offer_catalog_format_budget_amount(int $amount): string { return CatalogTaxonomy::formatBudgetAmount($amount); }
}
if (!function_exists('tacticum_offer_catalog_budget_buckets')) {
    function tacticum_offer_catalog_budget_buckets(): array { return CatalogTaxonomy::budgetBuckets(); }
}
if (!function_exists('tacticum_offer_catalog_budget_bucket')) {
    function tacticum_offer_catalog_budget_bucket(int $amount): array { return CatalogTaxonomy::budgetBucket($amount); }
}
if (!function_exists('tacticum_offer_catalog_response')) {
    function tacticum_offer_catalog_response(array $properties): array { return CatalogMapper::response($properties); }
}
if (!function_exists('tacticum_offer_catalog_scenario_from_h1')) {
    function tacticum_offer_catalog_scenario_from_h1(string $h1): string { return CatalogMapper::scenarioFromH1($h1); }
}
if (!function_exists('tacticum_offer_catalog_item_from_element')) {
    function tacticum_offer_catalog_item_from_element(array $fields, array $properties): ?array { return CatalogMapper::itemFromElement($fields, $properties); }
}

if (!class_exists('TacticumOfferCatalogRepository')) {
    final class TacticumOfferCatalogRepository
    {
        public function findElement(int $offerId, string $offerCode): ?array { return CatalogRepository::findElement($offerId, $offerCode); }
        public function items(int $iblockId): array { return CatalogRepository::items($iblockId); }
    }
}

if (!class_exists('TacticumOfferCatalogService')) {
    final class TacticumOfferCatalogService
    {
        public static function repository(): TacticumOfferCatalogRepository
        {
            static $repository = null;
            if (!$repository instanceof TacticumOfferCatalogRepository) {
                $repository = new TacticumOfferCatalogRepository();
            }

            return $repository;
        }

        public static function findElement(int $offerId, string $offerCode): ?array { return CatalogService::findElement($offerId, $offerCode); }
        public static function clearCache(int $iblockId = 0): void { CatalogService::clearCache($iblockId); }
        public static function items(int $iblockId): array { return CatalogService::items($iblockId); }
        public static function prepare(int $iblockId, array $filters, int $perPage = 24): array { return CatalogService::prepare($iblockId, $filters, $perPage); }
    }
}

if (!function_exists('tacticum_offer_catalog_items')) {
    function tacticum_offer_catalog_items(int $iblockId): array { return TacticumOfferCatalogService::items($iblockId); }
}
if (!function_exists('tacticum_offer_catalog_clean_key')) {
    function tacticum_offer_catalog_clean_key(mixed $value): string { return CatalogFilters::cleanKey($value); }
}
if (!function_exists('tacticum_offer_catalog_is_catalog_path')) {
    function tacticum_offer_catalog_is_catalog_path(string $path): bool { return CatalogFilters::isCatalogPath($path); }
}
if (!function_exists('tacticum_offer_catalog_path_filters')) {
    function tacticum_offer_catalog_path_filters(string $path): array { return CatalogFilters::pathFilters($path); }
}
if (!function_exists('tacticum_offer_catalog_has_pretty_segments')) {
    function tacticum_offer_catalog_has_pretty_segments(array $filters): bool { return CatalogFilters::hasPrettySegments($filters); }
}
if (!function_exists('tacticum_offer_catalog_normalize_filters')) {
    function tacticum_offer_catalog_normalize_filters(array $source, array $pathFilters = []): array { return CatalogFilters::normalize($source, $pathFilters); }
}
if (!function_exists('tacticum_offer_catalog_has_filters')) {
    function tacticum_offer_catalog_has_filters(array $filters, bool $includePage = false): bool { return CatalogFilters::hasFilters($filters, $includePage); }
}
if (!function_exists('tacticum_offer_catalog_filter_items')) {
    function tacticum_offer_catalog_filter_items(array $items, array $filters): array { return CatalogFilters::filterItems($items, $filters); }
}
if (!function_exists('tacticum_offer_catalog_add_option')) {
    function tacticum_offer_catalog_add_option(array &$options, string $group, string $key, string $label): void { CatalogFilters::addOption($options, $group, $key, $label); }
}
if (!function_exists('tacticum_offer_catalog_options')) {
    function tacticum_offer_catalog_options(array $items): array { return CatalogFilters::options($items); }
}
if (!function_exists('tacticum_offer_catalog_featured_options')) {
    function tacticum_offer_catalog_featured_options(array $options, string $group): array { return CatalogFilters::featuredOptions($options, $group); }
}
if (!function_exists('tacticum_offer_catalog_url')) {
    function tacticum_offer_catalog_url(array $filters, array $overrides = []): string { return CatalogFilters::url($filters, $overrides); }
}
if (!function_exists('tacticum_offer_catalog_pagination_range')) {
    function tacticum_offer_catalog_pagination_range(int $currentPage, int $totalPages): array { return CatalogFilters::paginationRange($currentPage, $totalPages); }
}
if (!function_exists('tacticum_offer_catalog_prepare')) {
    function tacticum_offer_catalog_prepare(int $iblockId, array $filters, int $perPage = 24): array { return TacticumOfferCatalogService::prepare($iblockId, $filters, $perPage); }
}
