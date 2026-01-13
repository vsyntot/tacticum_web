<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<div id="demoagents">
    <!-- Case Studies Section -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Попробуй готового демо-агента</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Оцени возможности - запусти демо-агента прямо сейчас и убедись в эффективности решения на практике
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?
                foreach( $arResult["ITEMS"] as $arItem ){
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM'))) ?>
                    <?php
                    // Экранируем пользовательские данные; HTML допускаем только через whitelist.
                    $sanitizeHtml = static fn($text) => strip_tags((string)$text, '<br><b><strong><i><em><p><ul><ol><li><a><span>');
                    $agentName = htmlspecialcharsbx($arItem["NAME"]);
                    $agentImage = htmlspecialcharsbx($arItem["PREVIEW_PICTURE"]["SRC"]);
                    $agentPreview = $sanitizeHtml($arItem["PREVIEW_TEXT"]);
                    $agentLink = htmlspecialcharsbx($arItem["PROPERTIES"]["LINK"]["VALUE"]);
                    ?>
                    <!-- Case Study 1 -->
                    <div class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300">
                        <div class="h-80 overflow-hidden">
                            <img src="<?=$agentImage?>" alt="<?=$agentName?>" class="w-full h-full object-cover object-top"/>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <?foreach( $arItem["SECTIONS"] as $arItemSection ){?>
                                    <span class="bg-primary/10 text-primary text-xs font-medium px-3 py-1 rounded-full"><?=htmlspecialcharsbx($arItemSection["NAME"])?></span>
                                <?}?>
                            </div>
                            <h3 class="text-xl font-bold text-secondary mb-3"><?=$agentName?></h3>
                            <p class="text-gray-600 mb-4">
                                <?=$agentPreview?>
                            </p>
                            <a href="<?=$agentLink?>" target="_blank" class="text-primary font-medium flex items-center gap-1 hover:gap-2 transition-all">Пообщаться с агентом <i class="ri-arrow-right-line"></i></a>
                        </div>
                    </div>
                <?}?>
            </div>
            <?/*<div class="text-center mt-12">
                <a href="#" class="inline-block bg-white border border-primary text-primary px-8 py-3 rounded-button hover:bg-primary hover:text-white transition-colors whitespace-nowrap">Смотреть все кейсы</a>
            </div>*/?>
        </div>
    </section>
</div>
