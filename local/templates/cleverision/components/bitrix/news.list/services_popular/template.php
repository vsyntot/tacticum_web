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

$uid = uniqid("popServ_");
?>
<div class="cmpPopularServices" id="<?=$uid?>">
    <div class="container">
        <div class="row titleBlock">
            <div class="title col-12 col-md-auto d-flex align-items-md-center">Популярные услуги</div>
            <div class="ml-auto col-12 col-md-auto d-flex align-items-md-end">
                <a class="allServices" href="<?=$arResult["LIST_PAGE_URL"]?>">все услуги в каталоге &gt;&gt;</a>
            </div>
        </div>
        <div class="slides">
            <?
            foreach( $arResult["ITEMS"] as $key => $arItem ){
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                $arDetailImg = CFile::ResizeImageGet(
                    $arItem["DETAIL_PICTURE"],
                    array("width" => 238, "height" => 143),
                    BX_RESIZE_IMAGE_EXACT,
                    true
                );

                $isAdded = !empty($_SESSION["ADDED_SERVICES"]) && isset($_SESSION["ADDED_SERVICES"][$arItem["ID"]])
                ?>
                <div>
                    <div class="slide" id="<?=$this->GetEditAreaId($arItem['ID']);?>" data-item-id="<?=$arItem["ID"]?>">
                        <a class="img" href="<?=$arItem["DETAIL_PAGE_URL"]?>">
                            <img src="<?=$arDetailImg["src"]?>" />
                        </a>
                        <div class="desc">
                            <div class="group">
                                <div class="group"><?=$arItem["SECTION_NAME"]?></div>
                            </div>
                            <a class="text" href="<?=$arItem["DETAIL_PAGE_URL"]?>">
                                <?=$arItem["NAME"]?>
                            </a>
                            <div class="added<?=($isAdded)?" d-flex":""?>">
                                <div class="status">
                                    <div class="icon"></div>
                                    <div class="label">УСЛУГА ДОБАВЛЕНА</div>
                                </div>
                                <a class="link" href="/cart/">к списку &gt;&gt;</a>
                            </div>
                            <div class="add<?=(!$isAdded)?" d-flex":""?>">
                                <div class="icon"></div>
                                <div class="label">добавить услугу в список</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?
            }
            ?>
        </div>
    </div>
</div>
<script>
    $(function() {
        const cmp = $('#<?=$uid?>');
        const items = $(cmp).find('.slide');

        $.each(items, function(index, item) {
            $(item).find('.add').click(function(){
                const id = $(item).data('item-id');
                if (id) {
                    main.addToCart(id, function(result){
                        if(result.success){
                            $(item).find('.add').removeClass('d-flex');
                            $(item).find('.added').addClass('d-flex');
                        }
                    })
                }
            })
        });

        if ($.fn.slick) {
            $('#<?=$uid?> .slides').slick({
                speed: 300,
                slidesToShow: 4,
                slidesToScroll: 4,
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        }
    })
</script>
