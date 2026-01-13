<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?if(!empty($arResult["ITEMS"])){?>
    <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Нам доверяют</h2>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
            Отзывы наших клиентов о сотрудничестве и результатах внедрения
            AI-решений
        </p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?foreach( $arResult["ITEMS"] as $arItem ){
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')))
            ?>
            <?php
            // Экранируем пользовательские данные; HTML допускаем только через sanitize().
            $reviewText = \Bitrix\Main\Text\Converter::getHtmlConverter()->sanitize($arItem["DETAIL_TEXT"]);
            $reviewName = htmlspecialcharsbx($arItem["PROPERTIES"]["NAME"]["VALUE"]);
            $reviewPosition = htmlspecialcharsbx($arItem["PROPERTIES"]["POSITION"]["VALUE"]);
            $reviewCompany = htmlspecialcharsbx($arItem["PROPERTIES"]["COMPANY"]["VALUE"]);
            $reviewInitials = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), array_slice(preg_split('/\s+/u', trim($arItem["PROPERTIES"]["NAME"]["VALUE"])), 0, 2))));
            $reviewInitialsEsc = htmlspecialcharsbx($reviewInitials);
            ?>
            <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col">
                <div class="flex items-center gap-1 text-yellow-400 mb-4">
                    <?
                    $normalizedInputRating = str_replace(',', '.', str_replace(' ', '', trim($arItem["PROPERTIES"]["RATING"]["VALUE"])));
                    $rating = (float)$normalizedInputRating;
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                    $maxStars = 5;

                    for ($i = 0; $i < $fullStars; $i++) {?>
                        <i class="ri-star-fill"></i>
                    <?}
                    if ($hasHalfStar) {?>
                        <i class="ri-star-half-fill"></i>
                        <?$fullStars++; // считаем её за занятую позицию
                    }
                    for ($i = $fullStars; $i < $maxStars; $i++) {?>
                        <i class="ri-star-line"></i>
                    <?}?>
                </div>
                <p class="text-gray-600 mb-6 italic grow">
                    "<?=$reviewText?>"
                </p>
                <div class="flex items-center gap-4 mt-auto">
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <span class="text-primary font-bold"><?=$reviewInitialsEsc?></span>
                    </div>
                    <div>
                        <h4 class="font-bold text-secondary"><?=$reviewName?></h4>
                        <p class="text-sm text-gray-500">
                            <?=$reviewPosition?>, "<?=$reviewCompany?>"
                        </p>
                    </div>
                </div>
            </div>
        <?}?>
    </div>
<?}?>
