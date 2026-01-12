<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?php
$curPage = $APPLICATION->GetCurPage(false);
$menuTree = $arResult['MENU_TREE'];
?>

<?if (!empty($menuTree)){?>
    <nav class="hidden md:flex items-center space-x-8">
        <?foreach($menuTree as $arItem):?>
            <?if ($arItem["PERMISSION"] > "D"):?>
                <div class="relative group">
                    <a href="<?=$arItem["LINK"]?>"
                       class="nav-link <?=($curPage === $arItem["LINK"] ? 'active ' : '')?>text-secondary hover:text-primary transition-colors flex items-center">
                        <?=$arItem["TEXT"]?>
                        <?if (!empty($arItem["CHILDREN"])):?>
                            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        <?endif;?>
                    </a>
                    <?if (!empty($arItem["CHILDREN"])):?>
                        <div class="absolute left-0 top-full z-20 min-w-[180px] rounded-xl bg-white shadow-lg opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                            <?foreach($arItem["CHILDREN"] as $child):?>
                                <?if ($child["PERMISSION"] > "D"):?>
                                    <a href="<?=$child["LINK"]?>"
                                       class="block px-4 py-2 text-sm text-secondary hover:text-primary hover:bg-gray-100 transition-colors <?=($curPage === $child["LINK"] ? 'font-bold' : '')?>">
                                        <?=$child["TEXT"]?>
                                    </a>
                                <?endif;?>
                            <?endforeach;?>
                        </div>
                    <?endif;?>
                </div>
            <?endif;?>
        <?endforeach;?>
    </nav>
<?}?>
