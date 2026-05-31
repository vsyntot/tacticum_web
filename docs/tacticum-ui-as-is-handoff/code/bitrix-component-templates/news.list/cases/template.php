<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<div id="cases">
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Наши кейсы</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Реальные истории успеха наших клиентов, которые трансформировали
                    свой бизнес с помощью AI-решений
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?
                foreach( $arResult["ITEMS"] as $arItem ){
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM'))) ?>
                    <?php
                    $caseName = tacticum_escape_iblock_text((string)$arItem["NAME"]);
                    $caseImage = htmlspecialcharsbx($arItem["PREVIEW_PICTURE"]["SRC"]);
                    $casePreviewRaw = (string)($arItem["~PREVIEW_TEXT"] ?? $arItem["PREVIEW_TEXT"] ?? "");
                    $casePreview = tacticum_sanitize_iblock_html($casePreviewRaw);
                    ?>
                    <div class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300">
                        <div class="h-80 overflow-hidden">
                            <img src="<?=$caseImage?>" alt="<?=$caseName?>" loading="lazy" decoding="async" class="w-full h-full object-cover object-top"/>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <?foreach( $arItem["SECTIONS"] as $arItemSection ){?>
                                    <span class="bg-primary/10 text-primary text-xs font-medium px-3 py-1 rounded-full"><?=tacticum_escape_iblock_text((string)$arItemSection["NAME"])?></span>
                                <?}?>
                            </div>
                            <h3 class="text-xl font-bold text-secondary mb-3"><?=$caseName?></h3>
                            <p class="text-gray-600 mb-4">
                                <?=$casePreview?>
                            </p>
                        </div>
                    </div>
                <?}?>
            </div>
        </div>
    </section>
</div>
