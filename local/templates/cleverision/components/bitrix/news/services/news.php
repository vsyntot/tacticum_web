<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<div class="container">
    <div class="pageTitle">Услуги компании</div>
</div>

<?
$obRequest = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$getNewUri = function() use ($obRequest) {
    return new \Bitrix\Main\Web\Uri($obRequest->getRequestUri());
};

$arSectionList = array();

$obSection = new CIBlockSection();
$obSectionList = $obSection->GetList(
    array(),
    array(
        "IBLOCK_ID" => 2,
    ),
    false,
    array(
        "ID",
        "NAME"
    )
);

while ($arSection = $obSectionList->GetNext()) {
    $arSectionList[$arSection["ID"]] = $arSection;
}

$group = isset($_GET["group"]) ? $_GET["group"] : "";
?>
<div class="cmpGroupList">
    <div class="container">
        <div class="list">
            <?
            if (!$group || $group === "all") {
                ?>
                <div class="item active">Все виды услуг</div>
                <?
            } else {
                ?>
                <a class="item" href="<?=$getNewUri()->deleteParams(array("group"))->getUri()?>">Все виды услуг</a>
                <?
            }

            foreach($arSectionList as $arSection){
                if ($group === $arSection["ID"]) {
                    ?>
                    <div class="item active"><?=$arSection["NAME"]?></div>
                    <?
                } else {
                    ?>
                    <a class="item" href="<?=$getNewUri()->addParams(array("group" => $arSection["ID"]))->getUri()?>"><?=$arSection["NAME"]?></a>
                    <?
                }
            }
            ?>
        </div>
    </div>
</div>

<?
$APPLICATION->IncludeComponent(
    "cleverision:list.control",
    "",
    Array()
);
?>

<?
$sort = "NAME";
$sortDir = "ASC";
if (isset($_GET["sort"]) && $_GET["sort"] === "popular") {
    $sort = "PROPERTY_IS_POPULAR";
    $sortDir = "DESC";
}

$newsCount = 12;
if (isset($_GET["psize"]) && filter_var($_GET["psize"], FILTER_VALIDATE_INT) && $_GET["psize"] > 0) {
    $newsCount = (int)$_GET["psize"];
}
?>

<?
global $arServicesFilter;
$arServicesFilter = array();
if (filter_var($group, FILTER_VALIDATE_INT) > 0) {
    $arServicesFilter["SECTION_ID"] = $group;
}
$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "services_list",
    Array(
        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "NEWS_COUNT" => $newsCount,
        "SORT_BY1" => $sort,
        "SORT_ORDER1" => $sortDir,
        "FIELD_CODE" => $arParams["LIST_FIELD_CODE"],
        "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
        "DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["detail"],
        "SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
        "IBLOCK_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["news"],
        "DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
        "SET_TITLE" => $arParams["SET_TITLE"],
        "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
        "MESSAGE_404" => $arParams["MESSAGE_404"],
        "SET_STATUS_404" => $arParams["SET_STATUS_404"],
        "SHOW_404" => $arParams["SHOW_404"],
        "FILE_404" => $arParams["FILE_404"],
        "INCLUDE_IBLOCK_INTO_CHAIN" => $arParams["INCLUDE_IBLOCK_INTO_CHAIN"],
        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
        "CACHE_TIME" => $arParams["CACHE_TIME"],
        "CACHE_FILTER" => $arParams["CACHE_FILTER"],
        "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
        "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
        "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
        "PAGER_TITLE" => $arParams["PAGER_TITLE"],
        "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
        "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
        "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
        "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
        "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
        "PAGER_BASE_LINK_ENABLE" => $arParams["PAGER_BASE_LINK_ENABLE"],
        "PAGER_BASE_LINK" => $arParams["PAGER_BASE_LINK"],
        "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
        "DISPLAY_DATE" => $arParams["DISPLAY_DATE"],
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PICTURE" => $arParams["DISPLAY_PICTURE"],
        "DISPLAY_PREVIEW_TEXT" => $arParams["DISPLAY_PREVIEW_TEXT"],
        "PREVIEW_TRUNCATE_LEN" => $arParams["PREVIEW_TRUNCATE_LEN"],
        "ACTIVE_DATE_FORMAT" => $arParams["LIST_ACTIVE_DATE_FORMAT"],
        "USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
        "GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
        "FILTER_NAME" => "arServicesFilter",
        "HIDE_LINK_WHEN_NO_DETAIL" => $arParams["HIDE_LINK_WHEN_NO_DETAIL"],
        "CHECK_DATES" => $arParams["CHECK_DATES"],
    ),
    $component
);
unset($arServicesFilter);
?>
