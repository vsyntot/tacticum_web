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

$arResult = [
    'AIAGENTS_IBLOCK_ID' => TacticumComponentParams::int($arParams, 'IBLOCK_ID'),
    'FAQ_IBLOCK_ID' => TacticumComponentParams::int($arParams, 'FAQ_IBLOCK_ID'),
    'FAQ_SECTION_KEY' => TacticumComponentParams::string($arParams, 'FAQ_SECTION_KEY', 'aiagents'),
    'FAQ_PARENT_SECTION' => TacticumComponentParams::string($arParams, 'FAQ_PARENT_SECTION'),
];

$this->IncludeComponentTemplate();
