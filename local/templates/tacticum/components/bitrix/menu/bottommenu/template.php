<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)){?>
    <?foreach($arResult as $itemID => $arItem){?>
        <?if ($arItem["PERMISSION"] > "D"){?>
            <?php
            // Экранируем строки меню; не удалять при правках шаблона.
            $itemLink = $arItem["LINK"];
            $itemLinkEsc = htmlspecialcharsbx($itemLink);
            $itemTextEsc = htmlspecialcharsbx($arItem["TEXT"]);
            ?>
            <?if(empty($arItem["LINK"])){?>
                <?if(!empty($arResult[$itemID-1]["TEXT"])){?>
                    </ul>
                    </div>
                <?}?>
                <div>
                <h3 class="text-lg font-bold mb-6"><?=$itemTextEsc?></h3>
                <ul class="space-y-3">
            <?}
            else{?>
                <li><a href="<?=$itemLinkEsc?>" class="text-white/70 hover:text-white transition-colors"><?=$itemTextEsc?></a></li>
            <?}?>
        <?}?>
    <?}?>
    </ul>
    </div>
<?}?>
