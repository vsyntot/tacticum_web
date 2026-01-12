<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$obSection = new CIBlockSection();
if (!empty($arResult) && $arResult["IBLOCK_SECTION_ID"] > 0) {
    $obSectionList = $obSection->GetList(
        array(),
        array(
            "ID" => $arResult["IBLOCK_SECTION_ID"],
        ),
        false,
        array("ID", "NAME",)
    );
    if ($arSection = $obSectionList->GetNext()) {
        $arResult["SECTION_NAME"] = $arSection["NAME"];
        $arResult["~SECTION_NAME"] = $arSection["~NAME"];
    }
}

// $this->__component->arResultCacheKeys = array_merge($this->__component->arResultCacheKeys, array('ID', 'IBLOCK_SECTION_ID', 'PROPERTIES'));
