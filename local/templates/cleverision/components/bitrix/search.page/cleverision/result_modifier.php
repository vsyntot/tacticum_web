<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arElementsData = array();

if (count($arResult["SEARCH"]) > 0) {
    $targetIBlockId = "2";

    $arIdList = array();
    foreach ($arResult["SEARCH"] as $arSearchItem) {
        if ($arSearchItem["MODULE_ID"] === "iblock" && $arSearchItem["PARAM2"] === $targetIBlockId) {
            $arIdList[] = $arSearchItem["ITEM_ID"];
        }
    }

    $obElement = new CIBlockElement();
    $obElementList = $obElement->GetList(
        array(),
        array(
            "IBLOCK_ID" => $targetIBlockId,
            "ID" => $arIdList,
        ),
        false,
        false,
        array(
            "ID",
            "NAME",
            "DETAIL_TEXT",
            "DETAIL_PICTURE",
            "IBLOCK_SECTION_ID"
        )
    );

    while ($arElement = $obElementList->GetNext()) {
        $arElementsData[$arElement["ID"]] = $arElement;
    }
}

if (!empty($arElementsData)) {
    $arSectionIdList = array();
    $arSectionNames = array();
    $arSectionNamesRaw = array();

    $obSection = new CIBlockSection();
    foreach ($arElementsData as $arItem) {
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
            foreach ($arElementsData as &$arItem) {
                if (isset($arSectionNames[$arItem["IBLOCK_SECTION_ID"]])) {
                    $arItem["SECTION_NAME"] = $arSectionNames[$arItem["IBLOCK_SECTION_ID"]];
                    $arItem["~SECTION_NAME"] = $arSectionNamesRaw[$arItem["IBLOCK_SECTION_ID"]];
                }
            }
        }
    }
}

$arResult["ELEMENTS_DATA"] = $arElementsData;
// $this->__component->arResultCacheKeys = array_merge($this->__component->arResultCacheKeys, array('ID', 'IBLOCK_SECTION_ID', 'PROPERTIES'));
