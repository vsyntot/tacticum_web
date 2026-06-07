<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$offerTemplateFolder = (string)($templateFolder ?? '/local/components/tacticum/offer.catalog/templates/.default');
$this->addExternalCss($offerTemplateFolder . '/style.css');
$this->addExternalJs($offerTemplateFolder . '/script.js');

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
$offerCatalogHref = static function (array $filters, array $overrides = []) use ($offerUrl): string {
    $url = $offerUrl($filters, $overrides);
    return str_contains($url, '#') ? $url : $url . '#offer-catalog';
};
$offerOptionLabel = static function (string $group, string $key) use ($offerOptions): string {
    $options = is_array($offerOptions[$group] ?? null) ? $offerOptions[$group] : [];
    foreach ($options as $option) {
        if ((string)($option['key'] ?? '') === $key) {
            return (string)($option['label'] ?? $key);
        }
    }

    return $key;
};
$offerSortLabels = [
    'new' => 'сначала новые',
    'budget-desc' => 'бюджет по убыванию',
    'budget-asc' => 'бюджет по возрастанию',
];
$offerAppliedFilters = [];
if (trim((string)$offerFilters['q']) !== '') {
    $offerAppliedFilters[] = [
        'label' => 'Поиск',
        'value' => trim((string)$offerFilters['q']),
        'href' => $offerCatalogHref($offerFilters, ['q' => '', 'page' => 1]),
    ];
}
foreach ([
    'sector' => ['label' => 'Отрасль', 'group' => 'sectors'],
    'scenario' => ['label' => 'Тип задачи', 'group' => 'scenarios'],
    'budget' => ['label' => 'Бюджет', 'group' => 'budgets'],
    'phase' => ['label' => 'Формат', 'group' => 'phases'],
] as $filterKey => $filterMeta) {
    $filterValue = trim((string)($offerFilters[$filterKey] ?? ''));
    if ($filterValue === '') {
        continue;
    }
    $offerAppliedFilters[] = [
        'label' => $filterMeta['label'],
        'value' => $offerOptionLabel($filterMeta['group'], $filterValue),
        'href' => $offerCatalogHref($offerFilters, [$filterKey => '', 'page' => 1]),
    ];
}
if ((string)($offerFilters['sort'] ?? 'new') !== 'new') {
    $sortValue = (string)$offerFilters['sort'];
    $offerAppliedFilters[] = [
        'label' => 'Сортировка',
        'value' => $offerSortLabels[$sortValue] ?? $sortValue,
        'href' => $offerCatalogHref($offerFilters, ['sort' => 'new', 'page' => 1]),
    ];
}
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
