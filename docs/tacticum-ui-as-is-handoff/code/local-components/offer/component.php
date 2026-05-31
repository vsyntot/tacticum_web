<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$helperPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/offer_catalog.php';
if (file_exists($helperPath)) {
    require_once $helperPath;
}

$mode = (string)($arParams['MODE'] ?? 'list');
if (!in_array($mode, ['list', 'detail', 'not_found'], true)) {
    $mode = 'list';
}

$filters = $arParams['FILTERS'] ?? [];
if (!is_array($filters)) {
    $filters = [];
}

$element = $arParams['ELEMENT'] ?? [];
if (!is_array($element)) {
    $element = [];
}

$perPage = (int)($arParams['PER_PAGE'] ?? 24);
if ($perPage <= 0) {
    $perPage = 24;
}

$arResult = [
    'MODE' => $mode,
    'IBLOCK_ID' => (int)($arParams['IBLOCK_ID'] ?? 0),
    'FILTERS' => $filters,
    'ELEMENT' => $element,
    'PER_PAGE' => $perPage,
];

$this->IncludeComponentTemplate();
