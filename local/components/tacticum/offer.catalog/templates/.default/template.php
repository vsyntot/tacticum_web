<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$offerCatalog = $arResult['CATALOG'] ?? [
    'filters' => [],
    'items' => [],
    'total' => 0,
    'all_total' => 0,
    'page' => 1,
    'total_pages' => 1,
    'per_page' => 24,
    'options' => ['sectors' => [], 'scenarios' => [], 'phases' => [], 'budgets' => []],
    'has_filters' => false,
    'pagination' => [],
    'stats' => ['sectors' => 0, 'scenarios' => 0, 'budget_min' => 0, 'budget_max' => 0],
];
$offerFilters = array_merge(
    ['q' => '', 'sector' => '', 'scenario' => '', 'budget' => '', 'phase' => '', 'sort' => 'new', 'page' => 1],
    is_array($offerCatalog['filters'] ?? null) ? $offerCatalog['filters'] : []
);
$offerOptions = array_merge(
    ['sectors' => [], 'scenarios' => [], 'phases' => [], 'budgets' => []],
    is_array($offerCatalog['options'] ?? null) ? $offerCatalog['options'] : []
);
$offerStats = array_merge(
    ['sectors' => 0, 'scenarios' => 0, 'budget_min' => 0, 'budget_max' => 0],
    is_array($offerCatalog['stats'] ?? null) ? $offerCatalog['stats'] : []
);
$offerMoney = static fn($amount) => $amount > 0 ? number_format((int)$amount, 0, '', ' ') . ' руб.' : 'по запросу';
$offerSelected = static fn($current, $value) => (string)$current === (string)$value ? ' selected' : '';
$offerUrl = static fn(array $filters, array $overrides = []) => function_exists('tacticum_offer_catalog_url')
    ? tacticum_offer_catalog_url($filters, $overrides)
    : '/offer/';
$offerResultFrom = $offerCatalog['total'] > 0 ? (($offerCatalog['page'] - 1) * $offerCatalog['per_page'] + 1) : 0;
$offerResultTo = min($offerCatalog['total'], $offerCatalog['page'] * $offerCatalog['per_page']);

foreach ([
    'hero',
    'product-bridge',
    'quick-filters',
    'filter-form',
    'results',
    'pagination',
    'bottom-cta',
] as $offerCatalogPart) {
    require __DIR__ . '/parts/' . $offerCatalogPart . '.php';
}
