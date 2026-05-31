<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

$offerElement = is_array($arResult['ELEMENT'] ?? null) ? $arResult['ELEMENT'] : [];
$elementCode = trim((string)($offerElement['CODE'] ?? ''));
if ($elementCode === '') {
    include __DIR__ . '/not-found.php';
    return;
}

$APPLICATION->IncludeComponent(
    'bitrix:news.detail',
    'offer',
    [
        'COMPONENT_TEMPLATE' => 'offer',
        'IBLOCK_TYPE' => 'client_requests',
        'IBLOCK_ID' => (int)($arResult['IBLOCK_ID'] ?? 0),
        'ELEMENT_ID' => '',
        'ELEMENT_CODE' => $elementCode,
        'CHECK_DATES' => 'Y',
        'FIELD_CODE' => [
            0 => 'ID',
            1 => 'CODE',
            2 => 'NAME',
            3 => '',
        ],
        'PROPERTY_CODE' => [
            0 => 'IS_FINAL',
            1 => 'GROUP_ID',
            2 => 'RESPONSE_ID',
            3 => 'RESPONSE',
            4 => 'SUMMARY',
            5 => 'GOALS',
            6 => 'BUSINESS_CONTEXT',
            7 => 'FUNCTIONAL_REQUIREMENTS',
            8 => 'NONFUNCTIONAL_REQUIREMENTS',
            9 => 'TEAM',
            10 => 'STACK',
            11 => 'BUDGET',
            12 => 'TIMELINE',
            13 => 'CLIENT_NAME',
            14 => 'TITLE',
            15 => 'DESCRIPTION',
            16 => 'KEYWORDS',
            17 => 'H1',
            18 => 'TECH_RISKS',
            19 => 'BUSINESS_RISKS',
            20 => '',
        ],
        'IBLOCK_URL' => '/offer/',
        'DETAIL_URL' => '/offer/#ELEMENT_CODE#/',
        'AJAX_MODE' => 'N',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_STYLE' => 'Y',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_OPTION_ADDITIONAL' => '',
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => '36000000',
        'CACHE_GROUPS' => 'Y',
        'SET_TITLE' => 'N',
        'SET_CANONICAL_URL' => 'N',
        'SET_BROWSER_TITLE' => 'N',
        'BROWSER_TITLE' => '-',
        'SET_META_KEYWORDS' => 'N',
        'META_KEYWORDS' => '-',
        'SET_META_DESCRIPTION' => 'N',
        'META_DESCRIPTION' => '-',
        'SET_LAST_MODIFIED' => 'N',
        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
        'ADD_SECTIONS_CHAIN' => 'N',
        'ADD_ELEMENT_CHAIN' => 'N',
        'ACTIVE_DATE_FORMAT' => 'd.m.Y',
        'USE_PERMISSIONS' => 'N',
        'STRICT_SECTION_CHECK' => 'N',
        'PAGER_TEMPLATE' => '.default',
        'DISPLAY_TOP_PAGER' => 'N',
        'DISPLAY_BOTTOM_PAGER' => 'Y',
        'PAGER_TITLE' => 'Страница',
        'PAGER_SHOW_ALL' => 'N',
        'PAGER_BASE_LINK_ENABLE' => 'N',
        'SET_STATUS_404' => 'Y',
        'SHOW_404' => 'N',
        'MESSAGE_404' => 'Предложение не найдено',
    ],
    $component ?? false
);
