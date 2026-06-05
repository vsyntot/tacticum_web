<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$page = $arResult['PAGE'] ?? [];
if (!is_array($page)) {
    $page = [];
}

if ($page === [] && function_exists('tacticum_product_page_unavailable_data')) {
    $page = tacticum_product_page_unavailable_data((string)($arResult['PRODUCT_CODE'] ?? 'product'), []);
}

if (function_exists('tacticum_render_product_page')) {
    tacticum_render_product_page($page);
}
