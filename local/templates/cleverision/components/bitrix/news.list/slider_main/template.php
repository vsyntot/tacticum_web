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
<div class="cmpMainSlider d-none d-sm-block">
    <div class="container-fluid px-0">
        <div id="indexMainSlider" class="carousel slide indexSlider" data-ride="carousel">
            <ol class="carousel-indicators custom">
                <?
                foreach( $arResult["ITEMS"] as $key => $arItem ){
                    $isActiveSet = false;
                    ?>
                    <li data-target="#indexMainSlider" data-slide-to="<?=$key?>" class="<?=(empty($key)) ? "active" : ""?>"></li>
                    <?
                }
                ?>
            </ol>
            <div class="carousel-inner">
                <?
                foreach( $arResult["ITEMS"] as $key => $arItem ){
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                    ?>
                    <div class="carousel-item<?=(empty($key)) ? " active" : ""?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                        <img class="d-block w-100" src="<?=$arItem["DETAIL_PICTURE"]["SRC"]?>" alt="">
                    </div>
                    <?
                }
                ?>
            </div>
            <a class="carousel-control-prev" href="#indexMainSlider" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#indexMainSlider" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>
</div>
