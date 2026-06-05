<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!class_exists('TacticumComponentParams')) {
    $helperPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/component_helpers.php';
    if (file_exists($helperPath)) {
        require_once $helperPath;
    }
}

if (!function_exists('tacticum_product_page_data')) {
    $productPagePath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/product_page.php';
    if (file_exists($productPagePath)) {
        require_once $productPagePath;
    }
}

$productCode = TacticumComponentParams::token((string)($arParams['PRODUCT_CODE'] ?? ''));
$canonicalPath = TacticumComponentParams::string($arParams, 'CANONICAL_PATH', $productCode === '' ? '' : '/' . $productCode . '/');
$applicationCategory = TacticumComponentParams::string($arParams, 'APPLICATION_CATEGORY', 'BusinessApplication');
$schemaDescription = TacticumComponentParams::string($arParams, 'SCHEMA_DESCRIPTION');
$applySeoDefaults = TacticumComponentParams::yesNo($arParams, 'APPLY_SEO_DEFAULTS', 'Y') === 'Y';
$prepareOnly = TacticumComponentParams::yesNo($arParams, 'PREPARE_ONLY', 'N') === 'Y';
$page = $arParams['PAGE_DATA'] ?? [];
if (!is_array($page)) {
    $page = [];
}

if ($page === [] && $productCode !== '' && function_exists('tacticum_product_page_data')) {
    $page = tacticum_product_page_data($productCode);
}

$arResult = [
    'PRODUCT_CODE' => $productCode,
    'CANONICAL_PATH' => $canonicalPath,
    'APPLICATION_CATEGORY' => $applicationCategory,
    'SCHEMA_DESCRIPTION' => $schemaDescription,
    'PAGE' => $page,
];

if (
    $applySeoDefaults
    && $canonicalPath !== ''
    && function_exists('tacticum_apply_seo_defaults')
    && function_exists('tacticum_product_page_schema')
) {
    tacticum_apply_seo_defaults($canonicalPath, [
        'schema' => tacticum_product_page_schema(
            $page,
            $canonicalPath,
            $applicationCategory,
            $schemaDescription
        ),
    ]);
}

if ($prepareOnly) {
    return $arResult;
}

$this->IncludeComponentTemplate();
