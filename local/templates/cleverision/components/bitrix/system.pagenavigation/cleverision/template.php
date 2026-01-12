<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);

if(!$arResult["NavShowAlways"])
{
    if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
        return;
}

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");
?>

<div class="cmpPagination">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-md-auto text text-center">Страницы:</div>
            <?if($arResult["bDescPageNumbering"] === true):?>

                <?if ($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
                    <?if($arResult["bSavePage"]):?>
                        <a class="col-auto page" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>">1</a>
                    <?else:?>
                        <a class="col-auto page" href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>">1</a>
                    <?endif?>
                <?else:?>
                    <div class="col-auto page active">1</div>
                <?endif?>

                <?
                $arResult["nStartPage"]--;
                while($arResult["nStartPage"] >= $arResult["nEndPage"]+1):
                    ?>
                    <?$NavRecordGroupPrint = $arResult["NavPageCount"] - $arResult["nStartPage"] + 1;?>

                    <?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
                    <div class="col-auto page active"><?=$NavRecordGroupPrint?></div>
                <?else:?>
                    <a class="col-auto page" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"]?>"><?=$NavRecordGroupPrint?></a><
                <?endif?>

                    <?$arResult["nStartPage"]--?>
                <?endwhile?>

                <?if ($arResult["NavPageNomer"] > 1):?>
                    <?if($arResult["NavPageCount"] > 1):?>
                        <a class="col-auto page" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=1"><?=$arResult["NavPageCount"]?></a>
                    <?endif?>
                <?else:?>
                    <?if($arResult["NavPageCount"] > 1):?>
                        <div class="col-auto page active"><?=$arResult["NavPageCount"]?></div>
                    <?endif?>
                <?endif?>

            <?else:?>

                <?if ($arResult["NavPageNomer"] > 1):?>
                    <?if($arResult["bSavePage"]):?>
                        <a class="col-auto page" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=1">1</a>
                    <?else:?>
                        <a class="col-auto page" href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>">1</a>
                    <?endif?>
                <?else:?>
                    <div class="col-auto page active">1</div>
                <?endif?>

                <?
                $arResult["nStartPage"]++;
                while($arResult["nStartPage"] <= $arResult["nEndPage"]-1):
                    ?>
                    <?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
                    <div class="col-auto page active"><?=$arResult["nStartPage"]?></div>
                <?else:?>
                    <a class="col-auto page" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"]?>"><?=$arResult["nStartPage"]?></a>
                <?endif?>
                    <?$arResult["nStartPage"]++?>
                <?endwhile?>

                <?if($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
                    <?if($arResult["NavPageCount"] > 1):?>
                        <a class="col-auto page" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["NavPageCount"]?>"><?=$arResult["NavPageCount"]?></a>
                    <?endif?>
                <?else:?>
                    <?if($arResult["NavPageCount"] > 1):?>
                        <div class="col-auto page active"><?=$arResult["NavPageCount"]?></div>
                    <?endif?>
                <?endif?>
            <?endif?>
        </div>
    </div>
</div>
