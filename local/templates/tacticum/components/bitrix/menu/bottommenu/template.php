<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)){?>
    <?foreach($arResult as $itemID => $arItem){?>
        <?if ($arItem["PERMISSION"] > "D"){?>
            <?if(empty($arItem["LINK"])){?>
                <?if(!empty($arResult[$itemID-1]["TEXT"])){?>
                    </ul>
                    </div>
                <?}?>
                <div>
                <h3 class="text-lg font-bold mb-6"><?=$arItem["TEXT"]?></h3>
                <ul class="space-y-3">
            <?}
            else{?>
                <li><a href="<?=$arItem["LINK"]?>" class="text-white/70 hover:text-white transition-colors"><?=$arItem["TEXT"]?></a></li>
            <?}?>
        <?}?>
    <?}?>
    </ul>
    </div>
<?}?>