<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//echo "<pre>"; print_r($arResult); echo "</pre>";
?>

<?if(!empty($arResult["ITEMS"])){?>
    <div class="bg-gray-50 rounded-2xl p-8 md:p-12">
        <h3 class="text-2xl font-bold text-secondary mb-8 text-center">Открытые вакансии</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?foreach( $arResult["ITEMS"] as $arItem ){
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));?>
                <?php
                // Экранируем пользовательские данные; не удалять при правках шаблона.
                $vacancyName = htmlspecialcharsbx($arItem["NAME"]);
                $vacancyLocation = htmlspecialcharsbx($arItem["PROPERTIES"]["LOCATION"]["VALUE"]);
                $vacancyType = htmlspecialcharsbx($arItem["PROPERTIES"]["TYPE"]["VALUE"]);
                $vacancyTime = htmlspecialcharsbx($arItem["PROPERTIES"]["TIME"]["VALUE"]);
                $vacancyPreview = \Bitrix\Main\Text\Converter::getHtmlConverter()->sanitize($arItem["PREVIEW_TEXT"]);
                ?>

                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-lg font-bold text-secondary"><?=$vacancyName?></h4>
                            <p class="text-gray-500"><?=$vacancyLocation?> / <?=$vacancyType?></p>
                        </div>
                        <span class="bg-primary/10 text-primary text-xs font-medium px-3 py-1 rounded-full"><?=$vacancyTime?></span>
                    </div>
                    <p class="text-gray-600 mb-4"><?=$vacancyPreview?></p>
                   <?/*<button class="text-primary font-medium flex items-center gap-1 hover:gap-2 transition-all">
                        Подробнее <i class="ri-arrow-right-line"></i>
                    </button>*/?>
                </div>
            <?}?>
        </div>
        <?/*<div class="text-center mt-8">
            <a href="#" class="inline-block bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">Смотреть все вакансии</a>
        </div>*/?>
    </div>
<?}?>
