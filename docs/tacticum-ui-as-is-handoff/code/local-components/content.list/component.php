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

$template = TacticumComponentParams::string($arParams, 'NEWS_LIST_TEMPLATE');
if ($template === '') {
    $template = TacticumComponentParams::string($arParams, 'TEMPLATE');
}
$template = TacticumComponentParams::token($template);

$iblockId = (int)($arParams['IBLOCK_ID'] ?? 0);
$iblockKey = TacticumComponentParams::string($arParams, 'IBLOCK_KEY');
if ($iblockId <= 0 && $iblockKey !== '' && function_exists('tacticum_iblock_id')) {
    $iblockId = tacticum_iblock_id($iblockKey);
}

$newsCount = TacticumComponentParams::string($arParams, 'NEWS_COUNT', '3');
if ($newsCount === '' || !ctype_digit($newsCount)) {
    $newsCount = '3';
}

$sortOrder1 = strtoupper(TacticumComponentParams::string($arParams, 'SORT_ORDER1', 'ASC'));
if (!in_array($sortOrder1, ['ASC', 'DESC', 'RAND'], true)) {
    $sortOrder1 = 'ASC';
}

$sortOrder2 = strtoupper(TacticumComponentParams::string($arParams, 'SORT_ORDER2', 'DESC'));
if (!in_array($sortOrder2, ['ASC', 'DESC'], true)) {
    $sortOrder2 = 'DESC';
}

$arResult['NEWS_LIST_TEMPLATE'] = $template;
$arResult['NEWS_LIST_PARAMS'] = [
    'COMPONENT_TEMPLATE' => $template,
    'IBLOCK_TYPE' => TacticumComponentParams::string($arParams, 'IBLOCK_TYPE', 'company'),
    'IBLOCK_ID' => $iblockId,
    'NEWS_COUNT' => $newsCount,
    'SORT_BY1' => TacticumComponentParams::string($arParams, 'SORT_BY1', 'SORT'),
    'SORT_ORDER1' => $sortOrder1,
    'SORT_BY2' => TacticumComponentParams::string($arParams, 'SORT_BY2', 'ID'),
    'SORT_ORDER2' => $sortOrder2,
    'FILTER_NAME' => TacticumComponentParams::string($arParams, 'FILTER_NAME'),
    'FIELD_CODE' => TacticumComponentParams::list($arParams, 'FIELD_CODE'),
    'PROPERTY_CODE' => TacticumComponentParams::list($arParams, 'PROPERTY_CODE'),
    'CHECK_DATES' => TacticumComponentParams::yesNo($arParams, 'CHECK_DATES', 'Y'),
    'DETAIL_URL' => TacticumComponentParams::string($arParams, 'DETAIL_URL'),
    'AJAX_MODE' => 'N',
    'AJAX_OPTION_JUMP' => 'N',
    'AJAX_OPTION_STYLE' => 'Y',
    'AJAX_OPTION_HISTORY' => 'N',
    'AJAX_OPTION_ADDITIONAL' => '',
    'CACHE_TYPE' => TacticumComponentParams::string($arParams, 'CACHE_TYPE', 'A'),
    'CACHE_TIME' => TacticumComponentParams::string($arParams, 'CACHE_TIME', '36000000'),
    'CACHE_FILTER' => TacticumComponentParams::yesNo($arParams, 'CACHE_FILTER'),
    'CACHE_GROUPS' => TacticumComponentParams::yesNo($arParams, 'CACHE_GROUPS', 'Y'),
    'PREVIEW_TRUNCATE_LEN' => TacticumComponentParams::string($arParams, 'PREVIEW_TRUNCATE_LEN'),
    'ACTIVE_DATE_FORMAT' => TacticumComponentParams::string($arParams, 'ACTIVE_DATE_FORMAT', 'd.m.Y'),
    'SET_TITLE' => 'N',
    'SET_BROWSER_TITLE' => 'N',
    'SET_META_KEYWORDS' => 'N',
    'SET_META_DESCRIPTION' => 'N',
    'SET_LAST_MODIFIED' => 'N',
    'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
    'ADD_SECTIONS_CHAIN' => 'N',
    'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
    'PARENT_SECTION' => TacticumComponentParams::string($arParams, 'PARENT_SECTION'),
    'PARENT_SECTION_CODE' => TacticumComponentParams::string($arParams, 'PARENT_SECTION_CODE'),
    'INCLUDE_SUBSECTIONS' => TacticumComponentParams::yesNo($arParams, 'INCLUDE_SUBSECTIONS'),
    'STRICT_SECTION_CHECK' => TacticumComponentParams::yesNo($arParams, 'STRICT_SECTION_CHECK'),
    'PAGER_TEMPLATE' => TacticumComponentParams::string($arParams, 'PAGER_TEMPLATE', '.default'),
    'DISPLAY_TOP_PAGER' => 'N',
    'DISPLAY_BOTTOM_PAGER' => TacticumComponentParams::yesNo($arParams, 'DISPLAY_BOTTOM_PAGER', 'Y'),
    'PAGER_TITLE' => TacticumComponentParams::string($arParams, 'PAGER_TITLE', 'Новости'),
    'PAGER_SHOW_ALWAYS' => 'N',
    'PAGER_DESC_NUMBERING' => 'N',
    'PAGER_DESC_NUMBERING_CACHE_TIME' => TacticumComponentParams::string($arParams, 'PAGER_DESC_NUMBERING_CACHE_TIME', '36000'),
    'PAGER_SHOW_ALL' => 'N',
    'PAGER_BASE_LINK_ENABLE' => 'N',
    'SET_STATUS_404' => 'N',
    'SHOW_404' => 'N',
    'MESSAGE_404' => '',
];

$this->IncludeComponentTemplate();
