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

$this->addExternalCss(SITE_TEMPLATE_PATH."/styles/slick.css");
$this->addExternalJs(SITE_TEMPLATE_PATH."/js/slick/slick.min.js");

$this->addExternalCss(SITE_TEMPLATE_PATH."/js/fancybox/jquery.fancybox.css");
$this->addExternalJs(SITE_TEMPLATE_PATH."/js/fancybox/jquery.fancybox.js");

$uid = uniqid("indexDocList_");
?>
<div class="cmpIndexDocList <?=$uid?>">
    <div class="container">
        <div class="row titleBlock">
            <div class="title col d-flex align-items-md-center">Документация</div>
        </div>
        <div class="slides">
            <?
            foreach( $arResult["ITEMS"] as $arItem ){
                if (empty($arItem["DETAIL_PICTURE"])) {
                    continue;
                }
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                $arDetailImg = CFile::ResizeImageGet(
                    $arItem["DETAIL_PICTURE"],
                    array("width" => 184, "height" => 215),
                    BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                    true
                );
                ?>
                <div class="slide" id="<?=$this->getEditAreaId($arItem['ID']);?>">
                    <a data-fancybox="docs" href="<?=$arItem["DETAIL_PICTURE"]["SRC"]?>">
                        <img src="<?=$arDetailImg["src"]?>">
                    </a>
                </div>
                <?
            }
            ?>
        </div>
    </div>
</div>
<script>
    if (window.$ && $.fn.slick) {
        $('.<?=$uid?> .slides').slick({
            speed: 300,
            slidesToShow: 5,
            slidesToScroll: 5,
            infinite: false,
            responsive: [
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 4
                    }
                },
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 576,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    }
</script>
