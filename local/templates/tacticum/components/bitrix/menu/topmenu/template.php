<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?php
$curPage = $APPLICATION->GetCurPage(false);
$menuTree = $arResult['MENU_TREE'];
?>

<?if (!empty($menuTree)){?>
    <nav class="hidden md:flex items-center space-x-8">
        <?foreach($menuTree as $arItem):?>
            <?if ($arItem["PERMISSION"] > "D"):?>
                <?php
                // Экранируем строки меню; не удалять при правках шаблона.
                $itemLink = $arItem["LINK"];
                $itemLinkEsc = htmlspecialcharsbx($itemLink);
                $itemTextEsc = htmlspecialcharsbx($arItem["TEXT"]);
                ?>
                <div class="relative group">
                    <a href="<?=$itemLinkEsc?>"
                       class="nav-link <?=($curPage === $itemLink ? 'active ' : '')?>text-secondary hover:text-primary transition-colors flex items-center">
                        <?=$itemTextEsc?>
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
                                    <?php
                                    $childLink = $child["LINK"];
                                    $childLinkEsc = htmlspecialcharsbx($childLink);
                                    $childTextEsc = htmlspecialcharsbx($child["TEXT"]);
                                    ?>
                                    <a href="<?=$childLinkEsc?>"
                                       class="block px-4 py-2 text-sm text-secondary hover:text-primary hover:bg-gray-100 transition-colors <?=($curPage === $childLink ? 'font-bold' : '')?>">
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
<?}?>
