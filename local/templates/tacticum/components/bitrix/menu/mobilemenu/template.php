<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?php
$curPage = $APPLICATION->GetCurPage(false);
$menuTree = $arResult['MENU_TREE'];
?>

<?if (!empty($menuTree)){?>
    <div id="tacticum-mobile-menu" class="fixed inset-0 bg-secondary/95 z-50 flex flex-col items-center justify-center transform translate-x-full transition-transform duration-300">
        <button type="button" class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center cursor-pointer text-white tacticum-mobile-menu-close" aria-label="Закрыть меню">
            <i class="ri-close-line text-2xl"></i>
        </button>
        <nav class="flex flex-col items-center space-y-6 text-xl">
            <?foreach($menuTree as $arItem):?>
                <?if ($arItem["PERMISSION"] > "D"):?>
                    <?php
                    $itemLink = $arItem["LINK"];
                    $itemLinkEsc = htmlspecialcharsbx($itemLink);
                    $itemTextEsc = htmlspecialcharsbx($arItem["TEXT"]);
                    $isActive = ($curPage === $itemLink);
                    ?>
                    <div class="flex flex-col items-center">
                        <a href="<?=$itemLinkEsc?>"
                           class="<?=($isActive ? 'text-primary ' : 'text-white ')?>hover:text-primary transition-colors">
                            <?=$itemTextEsc?>
                        </a>
                        <?if (!empty($arItem["CHILDREN"])):?>
                            <div class="mt-3 flex flex-col items-center space-y-3 text-base">
                                <?foreach($arItem["CHILDREN"] as $child):?>
                                    <?if ($child["PERMISSION"] > "D"):?>
                                        <?php
                                        $childLink = $child["LINK"];
                                        $childLinkEsc = htmlspecialcharsbx($childLink);
                                        $childTextEsc = htmlspecialcharsbx($child["TEXT"]);
                                        $childActive = ($curPage === $childLink);
                                        ?>
                                        <a href="<?=$childLinkEsc?>"
                                           class="<?=($childActive ? 'text-primary ' : 'text-white/80 ')?>hover:text-primary transition-colors">
                                            <?=$childTextEsc?>
                                        </a>
                                    <?endif;?>
                                <?endforeach;?>
                            </div>
                        <?endif;?>
                    </div>
                <?endif;?>
            <?endforeach;?>
        </nav>
        <div class="mt-12">
            <button class="bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap tacticum-contact-btn">Связаться с нами</button>
        </div>
    </div>
<?}?>
