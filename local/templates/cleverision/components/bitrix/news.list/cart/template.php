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

$uid = uniqid("selectedServices_");
?>
<div class="cmpSrvSelected" id="<?=$uid?>">
    <div class="container">
        <div class="control">
            <div class="row justify-content-between">
                <div class="col-auto counter smaller">
                    Всего выбрано услуг: <span class="selectedCounter"><?=count($arResult["ITEMS"])?></span>
                </div>
                <div class="col-auto">
                    <a href="/services/">
                        <button type="button" class="white">ДОБАВИТЬ УСЛУГИ</button>
                    </a>
                    <button type="button" class="default" data-toggle="modal" data-target="#modalService">ОТПРАВИТЬ ЗАЯВКУ</button>
                </div>
            </div>
        </div>
        <div class="separator"></div>
        <div class="row list">
            <?
            foreach( $arResult["ITEMS"] as $arItem ){
                $arDetailImg = CFile::ResizeImageGet(
                    $arItem["DETAIL_PICTURE"],
                    array("width" => 238, "height" => 142),
                    BX_RESIZE_IMAGE_EXACT,
                    true
                );
                ?>
                <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 itemWrapper" data-item-id="<?=$arItem["ID"]?>">
                    <div class="item">
                        <a href="<?=$arItem["DETAIL_PAGE_URL"]?>">
                            <img src="<?=$arDetailImg["src"]?>" />
                        </a>
                        <div class="desc">
                            <div class="group">
                                <?=$arItem["SECTION_NAME"]?>
                            </div>
                            <div class="text">
                                <a href="<?=$arItem["DETAIL_PAGE_URL"]?>">
                                    <?=$arItem["NAME"]?>
                                </a>
                            </div>
                            <div class="status">
                                <div class="added">
                                    <div class="icon"></div>
                                    <span>УСЛУГА ДОБАВЛЕНА</span>
                                </div>
                                <div class="delete"></div>
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
    $(function(){
        const cmp = $('#<?=$uid?>');
        const items = $(cmp).find('.itemWrapper');

        $.each(items, function(index, item) {
            $(item).find('.delete').click(function(){
                const id = $(item).data('item-id');
                if (id) {
                    main.removeFromCart(id, function(result){
                        $(item).remove();
                    })
                }
            })
        });
    })
</script>
