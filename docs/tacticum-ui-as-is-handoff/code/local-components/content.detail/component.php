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

$resolveFirstElementId = static function (int $iblockId): int {
    if ($iblockId <= 0 || !\Bitrix\Main\Loader::includeModule('iblock')) {
        return 0;
    }

    $result = \CIBlockElement::GetList(
        ['SORT' => 'ASC', 'ID' => 'ASC'],
        ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 1],
        ['ID']
    );
    $element = $result->Fetch();

    return $element ? (int)$element['ID'] : 0;
};

$template = TacticumComponentParams::string($arParams, 'DETAIL_TEMPLATE');
if ($template === '') {
    $template = TacticumComponentParams::string($arParams, 'TEMPLATE');
}
$template = TacticumComponentParams::token($template);

$iblockId = (int)($arParams['IBLOCK_ID'] ?? 0);
$iblockKey = TacticumComponentParams::string($arParams, 'IBLOCK_KEY');
if ($iblockId <= 0 && $iblockKey !== '' && function_exists('tacticum_iblock_id')) {
    $iblockId = tacticum_iblock_id($iblockKey);
}

$elementCode = TacticumComponentParams::token(TacticumComponentParams::string($arParams, 'ELEMENT_CODE'));
$elementId = max(0, (int)($arParams['ELEMENT_ID'] ?? 0));
if ($elementId <= 0 && $elementCode === '') {
    $elementId = $resolveFirstElementId($iblockId);
}

$arResult['DETAIL_TEMPLATE'] = $template;
$arResult['DETAIL_PARAMS'] = [
    'COMPONENT_TEMPLATE' => $template,
    'IBLOCK_TYPE' => TacticumComponentParams::string($arParams, 'IBLOCK_TYPE', 'company'),
    'IBLOCK_ID' => $iblockId,
    'ELEMENT_ID' => $elementId > 0 ? (string)$elementId : '',
    'ELEMENT_CODE' => $elementCode,
    'CHECK_DATES' => TacticumComponentParams::string($arParams, 'CHECK_DATES', 'Y'),
    'FIELD_CODE' => TacticumComponentParams::list($arParams, 'FIELD_CODE', ['ID', 'CODE', 'NAME', 'DETAIL_TEXT']),
    'PROPERTY_CODE' => TacticumComponentParams::list($arParams, 'PROPERTY_CODE'),
    'IBLOCK_URL' => '',
    'DETAIL_URL' => TacticumComponentParams::string($arParams, 'DETAIL_URL'),
    'AJAX_MODE' => 'N',
    'AJAX_OPTION_JUMP' => 'N',
    'AJAX_OPTION_STYLE' => 'Y',
    'AJAX_OPTION_HISTORY' => 'N',
    'AJAX_OPTION_ADDITIONAL' => '',
    'CACHE_TYPE' => TacticumComponentParams::string($arParams, 'CACHE_TYPE', 'A'),
    'CACHE_TIME' => TacticumComponentParams::string($arParams, 'CACHE_TIME', '36000000'),
    'CACHE_GROUPS' => TacticumComponentParams::string($arParams, 'CACHE_GROUPS', 'Y'),
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
    'ACTIVE_DATE_FORMAT' => TacticumComponentParams::string($arParams, 'ACTIVE_DATE_FORMAT', 'd.m.Y'),
    'USE_PERMISSIONS' => 'N',
    'STRICT_SECTION_CHECK' => 'N',
    'PAGER_TEMPLATE' => TacticumComponentParams::string($arParams, 'PAGER_TEMPLATE', '.default'),
    'DISPLAY_TOP_PAGER' => 'N',
    'DISPLAY_BOTTOM_PAGER' => TacticumComponentParams::string($arParams, 'DISPLAY_BOTTOM_PAGER', 'Y'),
    'PAGER_TITLE' => TacticumComponentParams::string($arParams, 'PAGER_TITLE', 'Страница'),
    'PAGER_SHOW_ALL' => 'N',
    'PAGER_BASE_LINK_ENABLE' => 'N',
    'SET_STATUS_404' => 'N',
    'SHOW_404' => 'N',
    'MESSAGE_404' => '',
];

$this->IncludeComponentTemplate();
