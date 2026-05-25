<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_ID' => ['PARENT' => 'BASE', 'NAME' => 'FAQ iblock ID', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'SECTION_KEY' => ['PARENT' => 'BASE', 'NAME' => 'Semantic FAQ section key', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'PARENT_SECTION' => ['PARENT' => 'BASE', 'NAME' => 'Parent section ID fallback', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'PARENT_SECTION_CODE' => ['PARENT' => 'BASE', 'NAME' => 'Parent section code', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'SECTION_CLASS' => ['PARENT' => 'VISUAL', 'NAME' => 'Section CSS class', 'TYPE' => 'STRING', 'DEFAULT' => 'py-16'],
        'NEWS_COUNT' => ['PARENT' => 'BASE', 'NAME' => 'Items count', 'TYPE' => 'STRING', 'DEFAULT' => '0'],
    ],
];
