<?php

use Tacticum\Product\Page\{Cta, DataProvider, Schema, Text};

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_content_source')) {
    $productContentPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/product_content.php';
    if (is_file($productContentPath)) {
        require_once $productContentPath;
    }
    unset($productContentPath);
}

if (!function_exists('tacticum_product_page_string')) {
    function tacticum_product_page_string(array $data, string $key, string $default = ''): string { return Text::string($data, $key, $default); }
}

if (!function_exists('tacticum_product_page_html')) {
    function tacticum_product_page_html($value): string { return Text::html($value); }
}

if (!function_exists('tacticum_product_page_canonical_path')) {
    function tacticum_product_page_canonical_path(string $canonicalPath): string { return Text::canonicalPath($canonicalPath); }
}

if (!function_exists('tacticum_product_page_safe_href')) {
    function tacticum_product_page_safe_href($value, string $default = '#'): string { return Text::safeHref($value, $default); }
}

if (!function_exists('tacticum_product_page_is_safe_href')) {
    function tacticum_product_page_is_safe_href(string $href): bool { return Text::isSafeHref($href); }
}

if (!function_exists('tacticum_product_page_icon_class')) {
    function tacticum_product_page_icon_class($value, string $default = ''): string { return Text::iconClass($value, $default); }
}

if (!function_exists('tacticum_product_page_columns_class')) {
    function tacticum_product_page_columns_class($value, string $default = 'lg:grid-cols-3'): string { return Text::columnsClass($value, $default); }
}

if (!function_exists('tacticum_product_page_standard_scenario_options')) {
    function tacticum_product_page_standard_scenario_options(): array { return Cta::standardScenarioOptions(); }
}

if (!function_exists('tacticum_product_page_context_slug')) {
    function tacticum_product_page_context_slug($value, string $default = ''): string { return Text::contextSlug($value, $default); }
}

if (!function_exists('tacticum_product_page_cta_lead_context')) {
    function tacticum_product_page_cta_lead_context(array $page, array $cta): array { return Cta::leadContext($page, $cta); }
}

if (!function_exists('tacticum_product_page_cta_scenario_options')) {
    function tacticum_product_page_cta_scenario_options(array $cta): array { return Cta::scenarioOptions($cta); }
}

if (!function_exists('tacticum_product_page_data')) {
    function tacticum_product_page_data(string $productCode): array { return DataProvider::data($productCode); }
}

if (!function_exists('tacticum_product_page_unavailable_data')) {
    function tacticum_product_page_unavailable_data(string $productCode, array $bitrixData = []): array { return DataProvider::unavailableData($productCode, $bitrixData); }
}

if (!function_exists('tacticum_product_page_fallback_data')) {
    function tacticum_product_page_fallback_data(string $productCode): array { return DataProvider::fallbackData($productCode); }
}

if (!function_exists('tacticum_product_page_schema_text')) {
    function tacticum_product_page_schema_text($value): string { return Text::schemaText($value); }
}

if (!function_exists('tacticum_product_page_software_schema')) {
    function tacticum_product_page_software_schema(
        array $page,
        string $canonicalPath,
        string $applicationCategory = 'BusinessApplication',
        string $description = ''
    ): array {
        return Schema::software($page, $canonicalPath, $applicationCategory, $description);
    }
}

if (!function_exists('tacticum_product_page_faq_schema')) {
    function tacticum_product_page_faq_schema(array $page, string $canonicalPath): ?array { return Schema::faq($page, $canonicalPath); }
}

if (!function_exists('tacticum_product_page_schema')) {
    function tacticum_product_page_schema(
        array $page,
        string $canonicalPath,
        string $applicationCategory = 'BusinessApplication',
        string $description = ''
    ): array {
        return Schema::build($page, $canonicalPath, $applicationCategory, $description);
    }
}

$tacticumProductPageBlockIncludes = [
    '/local/php_interface/include/product_page_blocks/common.php',
    '/local/php_interface/include/product_page_blocks/fit_guide.php',
    '/local/php_interface/include/product_page_blocks/architecture.php',
    '/local/php_interface/include/product_page_blocks/use_cases.php',
    '/local/php_interface/include/product_page_blocks/procurement.php',
    '/local/php_interface/include/product_page_blocks/comparison.php',
    '/local/php_interface/include/product_page_blocks/rollout.php',
    '/local/php_interface/include/product_page_blocks/proof.php',
    '/local/php_interface/include/product_page_blocks/faq.php',
    '/local/php_interface/include/product_page_blocks/page.php',
];

foreach ($tacticumProductPageBlockIncludes as $relativePath) {
    $path = $_SERVER['DOCUMENT_ROOT'] . $relativePath;
    if (is_file($path)) {
        require_once $path;
    }
}

unset($tacticumProductPageBlockIncludes, $relativePath, $path);
