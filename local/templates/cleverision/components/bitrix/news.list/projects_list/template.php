<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->SetAdditionalCSS("/layout/components/done_projects_list/style.css");

$displayMode = "blocks";
$displayClass = "var-blocks";
if (isset($_GET["view"]) && $_GET["view"] === "line") {
    $displayMode = "lines";
    $displayClass = "var-lines";
}
?>
<div class="cmpDoneProjects">
    <div class="container">
        <div class="list <?=$displayClass?>">
            <div class="row">
                <?
                if ($displayMode === "blocks") {
                    foreach( $arResult["ITEMS"] as $arItem ){
                        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                        $arDetailImg = CFile::ResizeImageGet(
                            $arItem["DETAIL_PICTURE"],
                            array("width" => 346, "height" => 227),
                            BX_RESIZE_IMAGE_EXACT,
                            true
                        );
                        $imageSrc = $arDetailImg["src"];

                        $groupName = $arItem["DISPLAY_PROPERTIES"]["GROUP"]["LINK_SECTION_VALUE"]
                            [$arItem["DISPLAY_PROPERTIES"]["GROUP"]["VALUE"]]
                            ["NAME"]
                        ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <a class="item" href="<?=$arItem["DETAIL_PAGE_URL"]?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-center">
                                        <div class="imgWrapper">
                                            <img src="<?=$imageSrc?>">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-auto timestamp d-flex align-items-center"><?=$arItem["ACTIVE_FROM"]?></div>
                                    <div class="col-12 col-sm d-flex align-items-center overflow-hidden text-left text-sm-right">
                                        <div class="group w-100"><?=$groupName?></div>
                                    </div>
                                    <div class="col-12 title"><?=$arItem["NAME"]?></div>
                                </div>
                            </a>
                        </div>
                        <?
                    }
                } elseif ($displayMode === "lines") {
                    foreach( $arResult["ITEMS"] as $arItem ){
                        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                        $arDetailImg = CFile::ResizeImageGet(
                            $arItem["DETAIL_PICTURE"],
                            array("width" => 257, "height" => 167),
                            BX_RESIZE_IMAGE_EXACT,
                            true
                        );
                        $imageSrc = $arDetailImg["src"];

                        $groupName = $arItem["DISPLAY_PROPERTIES"]["GROUP"]["LINK_SECTION_VALUE"]
                            [$arItem["DISPLAY_PROPERTIES"]["GROUP"]["VALUE"]]
                            ["NAME"]
                        ?>
                        <div class="col-12">
                            <a class="item" href="<?=$arItem["DETAIL_PAGE_URL"]?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                                <div class="row flex-md-nowrap">
                                    <div class="col-12 col-md-auto d-flex justify-content-center justify-content-md-start">
                                        <div>
                                            <div class="imgWrapper">
                                                <img src="<?=$imageSrc?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md pl-md-0 overflow-hidden">
                                        <div class="row desc">
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-12 col-md-auto timestamp"><?=$arItem["ACTIVE_FROM"]?></div>
                                                    <div class="col-12 col-md group"><?=$groupName?></div>
                                                </div>
                                            </div>
                                            <div class="col-12 title"><?=$arItem["NAME"]?></div>
                                            <div class="col-12 text"><?=$arItem["PREVIEW_TEXT"]?></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
    <?=$arResult["NAV_STRING"]?>
<?endif;?>
