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

$uid = uniqid("serviceList_");

$displayMode = "blocks";
$displayClass = "var-blocks";
if (isset($_GET["view"]) && $_GET["view"] === "line") {
    $displayMode = "lines";
    $displayClass = "var-lines";
}
?>
<div class="cmpServiceList" id="<?=$uid?>">
    <div class="container">
        <div class="list <?=$displayClass?>">
            <?
            if ($displayMode === "blocks") {
                ?>
                <div class="row">
                    <?
                    foreach( $arResult["ITEMS"] as $key => $arItem ){
                        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                        $arDetailImg = CFile::ResizeImageGet(
                            $arItem["DETAIL_PICTURE"],
                            array("width" => 284, "height" => 143),
                            BX_RESIZE_IMAGE_EXACT,
                            true
                        );

                        $isAdded = !empty($_SESSION["ADDED_SERVICES"]) && isset($_SESSION["ADDED_SERVICES"][$arItem["ID"]])
                        ?>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                            <div class="item" id="<?=$this->GetEditAreaId($arItem['ID']);?>" data-item-id="<?=$arItem["ID"]?>">
                                <a class="img" href="<?=$arItem["DETAIL_PAGE_URL"]?>">
                                    <img src="<?=$arDetailImg["src"]?>" />
                                </a>
                                <div class="desc">
                                    <div class="group">
                                        <?=$arItem["SECTION_NAME"]?>
                                    </div>
                                    <a class="text" href="<?=$arItem["DETAIL_PAGE_URL"]?>" title="<?=$arItem["NAME"]?>">
                                        <?=TruncateText( $arItem["NAME"], 90 );?>
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
                <?
            } else {
                foreach( $arResult["ITEMS"] as $key => $arItem ){
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                    $arDetailImg = CFile::ResizeImageGet(
                        $arItem["DETAIL_PICTURE"],
                        array("width" => 164, "height" => 89),
                        BX_RESIZE_IMAGE_EXACT,
                        true
                    );

                    $isAdded = !empty($_SESSION["ADDED_SERVICES"]) && isset($_SESSION["ADDED_SERVICES"][$arItem["ID"]])
                    ?>
                    <div class="item" data-item-id="<?=$arItem["ID"]?>">
                        <div class="row" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                            <div class="col-12 col-sm-auto d-flex justify-content-center justify-content-sm-start">
                                <div>
                                    <a href="<?=$arItem["DETAIL_PAGE_URL"]?>">
                                        <img src="<?=$arDetailImg["src"]?>">
                                    </a>
                                </div>
                            </div>
                            <div class="col desc">
                                <div class="group"><?=$arItem["SECTION_NAME"]?></div>
                                <a class="title" href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"];?></a>
                            </div>
                            <div class="col-12 col-md-auto d-flex justify-content-center">
                                <div class="statusWrapper">
                                    <div class="add<?=(!$isAdded)?" d-flex":""?>">
                                        <div class="icon"></div>
                                        <div class="text">добавить услугу в список</div>
                                    </div>
                                    <div class="added<?=($isAdded)?" d-flex":""?>">
                                        <div class="status d-flex">
                                            <div class="icon"></div>
                                            <div class="text">УСЛУГА ДОБАВЛЕНА</div>
                                        </div>
                                        <a class="link" href="/cart/">перейти к списку &gt;&gt;</a>
                                    </div>
                                </div>
                            </div>
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

<script>
    $(function(){
        const cmp = $('#<?=$uid?>');
        const items = $(cmp).find('.item');

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
    })
</script>
