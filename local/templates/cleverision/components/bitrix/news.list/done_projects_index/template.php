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

$sliderId = uniqid("projDoneSlider_");
?>
<div class="cmpProjectsDone">
    <div class="container">
        <div class="row titleBlock">
            <div class="title col-12 col-md-auto d-flex align-items-md-center">Выполненные работы</div>
            <div class="ml-auto col-12 col-md-auto d-flex align-items-md-end">
                <a class="allServices" href="<?=$arResult["LIST_PAGE_URL"]?>">все завершенные проекты &gt;&gt;</a>
            </div>
        </div>
        <div id="<?=$sliderId?>" class="carousel slide projDoneSlider" data-ride="carousel">
            <div class="carousel-inner">
                <?
                foreach( $arResult["ITEMS"] as $key => $arItem ){
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                    ?>
                    <div class="carousel-item<?=empty($key) ? " active" : ""?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                        <div class="row">
                            <div class="col-sm-12 col-lg description d-none d-md-block">
                                <a class="title" href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a>
                                <?=$arItem["PREVIEW_TEXT"]?>
                            </div>
                            <div class="col-sm-12 col-lg">
                                <div class="picture">
                                    <img src="<?=$arItem["DETAIL_PICTURE"]["SRC"]?>" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                }
                ?>
            </div>
            <ol class="carousel-indicators custom slideSelector">
                <?
                foreach( $arResult["ITEMS"] as $key => $arItem ){
                    ?>
                    <li data-target="#<?=$sliderId?>" data-slide-to="<?=$key?>" class="<?=empty($key) ? "active" : ""?>"></li>
                    <?
                }
                ?>
            </ol>
        </div>
    </div>
</div>
