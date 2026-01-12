<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult["ITEMS"])) {
    $arSectionIdList = array();
    $arSectionNames = array();
    $arSectionNamesRaw = array();

    $obSection = new CIBlockSection();
    foreach ($arResult["ITEMS"] as $arItem) {
        if ($arItem["IBLOCK_SECTION_ID"] > 0) {
            $arSectionIdList[$arItem["ID"]] = $arItem["IBLOCK_SECTION_ID"];
        }
    }

    if (!empty($arSectionIdList)) {
        $obSectionList = $obSection->GetList(
            array(),
            array(
                "ID" => $arSectionIdList,
            ),
            false,
            array("ID", "NAME",)
        );
        while ($arSection = $obSectionList->GetNext()) {
            $arSectionNames[$arSection["ID"]] = $arSection["NAME"];
            $arSectionNamesRaw[$arSection["ID"]] = $arSection["~NAME"];
        }

        if (!empty($arSectionNames)) {
            foreach ($arResult["ITEMS"] as &$arItem) {
                if (isset($arSectionNames[$arItem["IBLOCK_SECTION_ID"]])) {
                    $arItem["SECTION_NAME"] = $arSectionNames[$arItem["IBLOCK_SECTION_ID"]];
                    $arItem["~SECTION_NAME"] = $arSectionNamesRaw[$arItem["IBLOCK_SECTION_ID"]];
                }
            }
        }
    }
}

// $this->__component->arResultCacheKeys = array_merge($this->__component->arResultCacheKeys, array('ID', 'IBLOCK_SECTION_ID', 'PROPERTIES'));
