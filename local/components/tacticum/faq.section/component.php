<?php

use Tacticum\Content\IblockRepository;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!class_exists('TacticumComponentParams')) {
    $helperPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/component_helpers.php';
    if (file_exists($helperPath)) {
        require_once $helperPath;
    }
}

$fallbackIds = static function (): array {
    $contentConfig = function_exists('tacticum_rest_get_config_section')
        ? tacticum_rest_get_config_section('content')
        : [];
    $fallbacks = $contentConfig['faq_section_fallback_ids'] ?? [];
    if (!is_array($fallbacks)) {
        return [];
    }

    $normalized = [];
    foreach ($fallbacks as $key => $id) {
        $cleanKey = TacticumComponentParams::token((string)$key);
        $id = (int)$id;
        if ($cleanKey !== '' && $id > 0) {
            $normalized[$cleanKey] = (string)$id;
        }
    }

    return $normalized;
};

$candidateCodes = static function (string $sectionKey): array {
    $sectionKey = TacticumComponentParams::token($sectionKey);
    if ($sectionKey === '') {
        return [];
    }

    $aliases = [
        'home' => ['home', 'main', 'faq-home', 'home-faq'],
        'main' => ['main', 'home', 'faq-main', 'main-faq'],
    ];
    $codes = $aliases[$sectionKey] ?? [$sectionKey, 'faq-' . $sectionKey, $sectionKey . '-faq'];

    return array_values(array_unique(array_filter($codes, 'strlen')));
};

$resolveParentSection = static function (
    int $iblockId,
    string $sectionKey,
    string $fallbackSection
) use ($candidateCodes, $fallbackIds): array {
    $sectionKey = TacticumComponentParams::token($sectionKey);
    if ($sectionKey !== '') {
        $resolved = IblockRepository::sectionIdByCodes($iblockId, $candidateCodes($sectionKey));
        if ($resolved !== '') {
            return ['id' => $resolved, 'status' => 'resolved'];
        }

        $fallback = $fallbackIds()[$sectionKey] ?? $fallbackSection;
        if ($fallback !== '') {
            return ['id' => $fallback, 'status' => 'fallback'];
        }

        return ['id' => '', 'status' => 'missing'];
    }

    return ['id' => $fallbackSection, 'status' => $fallbackSection === '' ? 'unscoped' : 'explicit'];
};

$iblockId = (int)($arParams['IBLOCK_ID'] ?? 0);
$newsCount = TacticumComponentParams::string($arParams, 'NEWS_COUNT', '0');
if ($newsCount === '' || !ctype_digit($newsCount)) {
    $newsCount = '0';
}
$sectionKey = TacticumComponentParams::string($arParams, 'SECTION_KEY');
$parentSectionResult = $resolveParentSection(
    $iblockId,
    $sectionKey,
    TacticumComponentParams::string($arParams, 'PARENT_SECTION')
);
$parentSection = $parentSectionResult['id'];
$sectionStatus = $parentSectionResult['status'];

$arResult['FAQ_SECTION_KEY'] = TacticumComponentParams::token($sectionKey);
$arResult['FAQ_SECTION_STATUS'] = $sectionStatus;
$arResult['FAQ_SECTION_PARENT'] = $parentSection;

$arResult['NEWS_LIST_PARAMS'] = [
    'COMPONENT_TEMPLATE' => 'faq',
    'SECTION_CLASS' => TacticumComponentParams::string($arParams, 'SECTION_CLASS', 'py-16'),
    'IBLOCK_TYPE' => TacticumComponentParams::string($arParams, 'IBLOCK_TYPE', 'company'),
    'IBLOCK_ID' => $iblockId,
    'NEWS_COUNT' => $newsCount,
    'SORT_BY1' => TacticumComponentParams::string($arParams, 'SORT_BY1', 'SORT'),
    'SORT_ORDER1' => TacticumComponentParams::string($arParams, 'SORT_ORDER1', 'ASC'),
    'SORT_BY2' => TacticumComponentParams::string($arParams, 'SORT_BY2', 'ID'),
    'SORT_ORDER2' => TacticumComponentParams::string($arParams, 'SORT_ORDER2', 'DESC'),
    'FILTER_NAME' => TacticumComponentParams::string($arParams, 'FILTER_NAME'),
    'FIELD_CODE' => ['ID', 'CODE', 'NAME', 'SORT', 'DETAIL_TEXT', 'IBLOCK_TYPE_ID', 'IBLOCK_ID'],
    'PROPERTY_CODE' => [],
    'CHECK_DATES' => TacticumComponentParams::string($arParams, 'CHECK_DATES', 'Y'),
    'DETAIL_URL' => TacticumComponentParams::string($arParams, 'DETAIL_URL'),
    'AJAX_MODE' => 'N',
    'AJAX_OPTION_JUMP' => 'N',
    'AJAX_OPTION_STYLE' => 'Y',
    'AJAX_OPTION_HISTORY' => 'N',
    'AJAX_OPTION_ADDITIONAL' => '',
    'CACHE_TYPE' => TacticumComponentParams::string($arParams, 'CACHE_TYPE', 'A'),
    'CACHE_TIME' => TacticumComponentParams::string($arParams, 'CACHE_TIME', '36000000'),
    'CACHE_FILTER' => TacticumComponentParams::string($arParams, 'CACHE_FILTER', 'N'),
    'CACHE_GROUPS' => TacticumComponentParams::string($arParams, 'CACHE_GROUPS', 'Y'),
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
    'PARENT_SECTION' => $parentSection,
    'PARENT_SECTION_CODE' => $sectionKey === '' ? TacticumComponentParams::string($arParams, 'PARENT_SECTION_CODE') : '',
    'INCLUDE_SUBSECTIONS' => TacticumComponentParams::string($arParams, 'INCLUDE_SUBSECTIONS', 'N'),
    'STRICT_SECTION_CHECK' => TacticumComponentParams::string($arParams, 'STRICT_SECTION_CHECK', 'N'),
    'PAGER_TEMPLATE' => TacticumComponentParams::string($arParams, 'PAGER_TEMPLATE', '.default'),
    'DISPLAY_TOP_PAGER' => 'N',
    'DISPLAY_BOTTOM_PAGER' => 'N',
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
