<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
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

$uid = uniqid("searchPage_");

$displayMode = "blocks";
$displayClass = "var-blocks";
if (isset($_GET["view"]) && $_GET["view"] === "line") {
    $displayMode = "lines";
    $displayClass = "var-lines";
}
?>
<div class="cmpSearchPage" id="<?=$uid?>">
    <form action="" method="get">
        <div class="container">
            <div class="row titleBlock">
                <div class="col-12 col-lg-4 cmpTitle">
                    Поиск в услугах:
                </div>
                <div class="col-12 col-sm-8 col-lg-5">
                    <input type="text" name="q" value="<?=$arResult["REQUEST"]["QUERY"]?>" size="40" />
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <input type="submit" value="ПОИСК" />
                    <input type="hidden" name="how" value="<?echo $arResult["REQUEST"]["HOW"]=="d"? "d": "r"?>" />
                </div>
            </div>

            <?if(isset($arResult["REQUEST"]["ORIGINAL_QUERY"])):
                ?>
                <div class="search-language-guess">
                    В запросе &quot;<a href="<?=$arResult["ORIGINAL_QUERY_URL"]?>"><?=$arResult["REQUEST"]["ORIGINAL_QUERY"]?></a>&quot; восстановлена раскладка клавиатуры.
                </div><br /><?
            endif;?>

            <?if($arResult["REQUEST"]["QUERY"] === false && $arResult["REQUEST"]["TAGS"] === false):?>
            <?elseif($arResult["ERROR_CODE"]!=0):?>
                <p>Ошибка поиска</p>
                <?ShowError($arResult["ERROR_TEXT"]);?>
            <?elseif(count($arResult["SEARCH"])>0):?>
                <div class="resultCountBlock">Найдено совпадений: <span class="counter"><?=$arResult["NAV_RESULT"]->SelectedRowsCount()?></span></div>
                <?
                $APPLICATION->IncludeComponent(
                    "cleverision:list.control",
                    "",
                    Array(
                        "SEARCH_SORT" => "Y",
                    )
                );
                ?>
                <div class="result <?=$displayClass?>">
                    <?
                    if ($displayMode === "blocks") {
                        ?>
                        <div class="row">
                            <?
                            foreach($arResult["SEARCH"] as $arSearchItem) {
                                $arItem = $arResult["ELEMENTS_DATA"][$arSearchItem["ITEM_ID"]];
                                $isAdded = !empty($_SESSION["ADDED_SERVICES"]) && isset($_SESSION["ADDED_SERVICES"][$arItem["ID"]]);

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
                                        <a class="img" href="<?=$arSearchItem["URL"]?>">
                                            <img src="<?=$arDetailImg["src"]?>" />
                                        </a>
                                        <div class="desc">
                                            <div class="group">
                                                <?=$arItem["SECTION_NAME"]?>
                                            </div>
                                            <a class="text" href="<?=$arSearchItem["URL"]?>">
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
                        <?
                    } else {
                        foreach($arResult["SEARCH"] as $arSearchItem) {
                            $arItem = $arResult["ELEMENTS_DATA"][$arSearchItem["ITEM_ID"]];
                            $isAdded = !empty($_SESSION["ADDED_SERVICES"]) && isset($_SESSION["ADDED_SERVICES"][$arItem["ID"]]);

                            $arDetailImg = CFile::ResizeImageGet(
                                $arItem["DETAIL_PICTURE"],
                                array("width" => 164, "height" => 89),
                                BX_RESIZE_IMAGE_EXACT,
                                true
                            );
                            ?>
                            <div class="item" data-item-id="<?=$arItem["ID"]?>">
                                <div class="row">
                                    <div class="col-12 col-sm-auto d-flex justify-content-center justify-content-sm-start">
                                        <a href="<?=$arSearchItem["URL"]?>">
                                            <img src="<?=$arDetailImg["src"]?>">
                                        </a>
                                    </div>
                                    <div class="col desc">
                                        <div class="group"><?=$arItem["SECTION_NAME"]?></div>
                                        <a class="title" href="<?=$arSearchItem["URL"]?>"><?=$arItem["NAME"]?></a>
                                    </div>
                                    <div class="col-12 col-md-auto d-flex justify-content-center">
                                        <div class="statusWrapper">
                                            <div class="add align-items-center <?=!$isAdded ? " d-flex" : ""?>">
                                                <div class="icon"></div>
                                                <div class="text">добавить услугу в список</div>
                                            </div>
                                            <div class="added<?=$isAdded ? " d-flex" : ""?>">
                                                <div class="d-flex">
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

                <?if($arParams["DISPLAY_BOTTOM_PAGER"] != "N") echo $arResult["NAV_STRING"]?>

                <?/*
                <p>
                <?if($arResult["REQUEST"]["HOW"]=="d"):?>
                    <a href="<?=$arResult["URL"]?>&amp;how=r<?echo $arResult["REQUEST"]["FROM"]? '&amp;from='.$arResult["REQUEST"]["FROM"]: ''?><?echo $arResult["REQUEST"]["TO"]? '&amp;to='.$arResult["REQUEST"]["TO"]: ''?>"><?=GetMessage("SEARCH_SORT_BY_RANK")?></a>&nbsp;|&nbsp;<b><?=GetMessage("SEARCH_SORTED_BY_DATE")?></b>
                <?else:?>
                    <b><?=GetMessage("SEARCH_SORTED_BY_RANK")?></b>&nbsp;|&nbsp;<a href="<?=$arResult["URL"]?>&amp;how=d<?echo $arResult["REQUEST"]["FROM"]? '&amp;from='.$arResult["REQUEST"]["FROM"]: ''?><?echo $arResult["REQUEST"]["TO"]? '&amp;to='.$arResult["REQUEST"]["TO"]: ''?>"><?=GetMessage("SEARCH_SORT_BY_DATE")?></a>
                <?endif;?>
                </p>
                */?>
            <?else:?>
                <span>Ничего не найдено</span>
            <?endif;?>
        </div>
    </form>
</div>

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
