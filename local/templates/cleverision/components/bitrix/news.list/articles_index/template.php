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
$this->setFrameMode( true );
?>
<div class="cmpIndexArticles">
    <div class="container">
        <div class="row titleBlock">
            <div class="title col-12 col-md-auto d-flex align-items-md-center">Статьи</div>
            <div class="ml-auto col-12 col-md-auto d-flex align-items-md-end">
                <a class="allServices" href="<?=$arResult["LIST_PAGE_URL"]?>">все статьи &gt;&gt;</a>
            </div>
        </div>
        <div class="row">
            <?
            foreach( $arResult["ITEMS"] as $arItem ){
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                ?>
                <div class="col-sm-12 col-xl-4">
                    <a class="item" href="#" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                        <div class="row">
                            <?
                            if (!empty($arItem["DETAIL_PICTURE"])) {
                                $arDetailImg = CFile::ResizeImageGet(
                                    $arItem["DETAIL_PICTURE"],
                                    array("width" => 240, "height" => 240),
                                    BX_RESIZE_IMAGE_EXACT,
                                    true
                                );
                                ?>
                                <div class="col col-md-3 col-xl">
                                    <img class="w-100" src="<?=$arDetailImg["src"]?>" alt="">
                                </div>
                                <?
                            }
                            ?>
                            <div class="col textContent">
                                <div class="timestamp"><?=$arItem["DATE_ACTIVE_FROM"]?></div>
                                <div class="title"><?=$arItem["NAME"]?></div>
                                <div class="text"><?=$arItem["PREVIEW_TEXT"]?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <?
            }
            ?>
        </div>
    </div>
</div>
