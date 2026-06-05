<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!function_exists('tacticum_product_page_string')) {
    $productPagePath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/product_page.php';
    if (file_exists($productPagePath)) {
        require_once $productPagePath;
    }
}

$page = $arParams['PAGE_DATA'] ?? [];
if (!is_array($page)) {
    $page = [];
}

$arResult = [
    'PAGE' => $page,
    'UNAVAILABLE' => ($page['_status'] ?? '') === 'unavailable',
];

$this->IncludeComponentTemplate();
