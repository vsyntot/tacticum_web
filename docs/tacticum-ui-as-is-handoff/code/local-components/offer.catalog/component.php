<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$helperPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/offer_catalog.php';
if (file_exists($helperPath)) {
    require_once $helperPath;
}

$iblockId = (int)($arParams['IBLOCK_ID'] ?? 0);
$perPage = (int)($arParams['PER_PAGE'] ?? 24);
if ($perPage <= 0) {
    $perPage = 24;
}

$filters = $arParams['FILTERS'] ?? [];
if (!is_array($filters)) {
    $filters = [];
}

$arResult['CATALOG'] = function_exists('tacticum_offer_catalog_prepare')
    ? tacticum_offer_catalog_prepare($iblockId, $filters, $perPage)
    : [
        'filters' => $filters,
        'items' => [],
        'total' => 0,
        'all_total' => 0,
        'page' => 1,
        'total_pages' => 1,
        'per_page' => $perPage,
        'options' => ['sectors' => [], 'scenarios' => [], 'phases' => [], 'budgets' => []],
        'has_filters' => false,
        'pagination' => [],
        'stats' => ['sectors' => 0, 'scenarios' => 0, 'budget_min' => 0, 'budget_max' => 0],
    ];

$this->IncludeComponentTemplate();
