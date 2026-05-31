<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as &$arItem) {
    $arSections = [];
    $dbSections = CIBlockElement::GetElementGroups(
        $arItem['ID'],
        true,
        ['ID', 'NAME', 'CODE', 'SECTION_PAGE_URL']
    );
    while ($arSection = $dbSections->Fetch()) {
        $arSections[] = $arSection;
    }
    $arItem['SECTIONS'] = $arSections;
}
unset($arItem);


