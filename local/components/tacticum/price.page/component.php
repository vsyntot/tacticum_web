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

$faqIblockId = TacticumComponentParams::int($arParams, 'FAQ_IBLOCK_ID');
if ($faqIblockId <= 0 && function_exists('tacticum_iblock_id')) {
    $faqIblockId = tacticum_iblock_id('faq');
}

$arResult = [
    'FAQ_IBLOCK_ID' => $faqIblockId,
];

$this->IncludeComponentTemplate();
