<?
use Tacticum\Content\IblockRepository;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as &$arItem) {
    $arItem['SECTIONS'] = IblockRepository::elementSections(
        (int)($arItem['ID'] ?? 0),
        ['ID', 'NAME', 'CODE', 'SECTION_PAGE_URL']
    );
}
unset($arItem);

