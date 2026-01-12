<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->SetAdditionalCSS("/layout/components/article_list/style.css");

$displayMode = "blocks";
$displayClass = "var-blocks";
if (isset($_GET["view"]) && $_GET["view"] === "line") {
    $displayMode = "lines";
    $displayClass = "var-lines";
}
?>
<div class="cmpArticleList">
    <div class="container">
        <div class="list <?=$displayClass?>">
            <?
            if ($displayMode === "blocks") {
                ?>
                <div class="row align-items-stretch">
                    <?
                    foreach( $arResult["ITEMS"] as $arItem ){
                        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                        $imageSrc = "";
                        if (!empty($arItem["DETAIL_PICTURE"])) {
                            $arDetailImg = CFile::ResizeImageGet(
                                $arItem["DETAIL_PICTURE"],
                                array("width" => 158, "height" => 158),
                                BX_RESIZE_IMAGE_EXACT,
                                true
                            );
                            $imageSrc = $arDetailImg["src"];
                        }
                        ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="itemWrapper">
                                <a class="item" href="<?=$arItem["DETAIL_PAGE_URL"]?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                                    <div class="row">
                                        <?
                                        if (!empty($imageSrc)) {
                                            ?>
                                            <div class="col-auto d-none d-sm-block">
                                                <img class="image" src="<?=$imageSrc?>">
                                            </div>
                                            <?
                                        }
                                        ?>
                                        <div class="col desc pl-3<?=!empty($imageSrc) ? " pl-sm-0" : ""?>">
                                            <div class="timestamp"><?=$arItem["DATE_ACTIVE_FROM"]?></div>
                                            <div class="title"><?=$arItem["NAME"]?></div>
                                            <div class="text"><?=$arItem["PREVIEW_TEXT"]?></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                </div>
                <?
            } elseif ($displayMode === "lines") {
                foreach( $arResult["ITEMS"] as $arItem ) {
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                    $imageSrc = "";
                    if (!empty($arItem["DETAIL_PICTURE"])) {
                        $arDetailImg = CFile::ResizeImageGet(
                            $arItem["DETAIL_PICTURE"],
                            array("width" => 105, "height" => 105),
                            BX_RESIZE_IMAGE_EXACT,
                            true
                        );
                        $imageSrc = $arDetailImg["src"];
                    }
                    ?>
                    <div class="row">
                        <div class="col-12">
                            <a class="item" href="<?=$arItem["DETAIL_PAGE_URL"]?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                                <div class="row">
                                    <?
                                    if (!empty($imageSrc)) {
                                        ?>
                                        <div class="col-md-auto d-none d-md-block">
                                            <img class="image" src="<?=$imageSrc?>">
                                        </div>
                                        <?
                                    }
                                    ?>
                                    <div class="col pl-4 <?=!empty($imageSrc) ? " pl-md-0" : ""?> desc">
                                        <div class="timestamp"><?=$arItem["DATE_ACTIVE_FROM"]?></div>
                                        <div class="title"><?=$arItem["NAME"]?></div>
                                        <div class="text"><?=$arItem["PREVIEW_TEXT"]?></div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <?
                }
            }
            ?>

        </div>
    </div>
</div>

<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
    <?=$arResult["NAV_STRING"]?>
<?endif;?>
