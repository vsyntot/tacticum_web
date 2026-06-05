<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<section class="py-12 md:py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-center gap-3 mb-4">
            <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center bg-amber-100 rounded-full">

                <i class="ri-error-warning-fill text-amber-500 ri-2x"></i>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-center">
                Возможные риски при реализации без профессиональной команды
            </h2>
        </div>
        <p class="text-gray-600 text-center max-w-3xl mx-auto mb-12">
            Самостоятельное внедрение новых IT-решений часто приводит к
            неучтенным расходам, задержкам и техническим ошибкам. Партнёрство с
            профессиональной командой помогает избежать типовых ошибок
        </p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <?php if (is_array($arResult["PROPERTIES"]["TECH_RISKS"]["VALUE"]) && !empty($arResult["PROPERTIES"]["TECH_RISKS"]["VALUE"])):?>
                <div class="bg-red-50/50 rounded-xl p-8 border border-red-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center bg-red-100 rounded-full">
                            <i class="ri-error-warning-line text-red-500 ri-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold">Технологические риски</h3>
                    </div>
                    <ul class="space-y-4">
                        <?php foreach ($arResult["PROPERTIES"]["TECH_RISKS"]["VALUE"] as $iTechRisksIndex => $sTechRisksItem):?>
                            <li class="flex items-start gap-3">
                                <i class="ri-close-circle-line text-red-500 mt-1"></i>
                                <div>
                                    <p class="font-medium mb-1"><?=tacticum_escape_iblock_text((string)$sTechRisksItem)?></p>
                                    <?php if (isset($arResult["PROPERTIES"]["TECH_RISKS"]["DESCRIPTION"][$iTechRisksIndex]) && !empty($arResult["PROPERTIES"]["TECH_RISKS"]["DESCRIPTION"][$iTechRisksIndex])):?>
                                        <p class="text-gray-600 text-sm"><?=tacticum_escape_iblock_text((string)$arResult["PROPERTIES"]["TECH_RISKS"]["DESCRIPTION"][$iTechRisksIndex])?></p>
                                    <?php endif;?>
                                </div>
                            </li>
                        <?php endforeach;?>
                    </ul>
                </div>
            <?php endif;?>
            <?php if (is_array($arResult["PROPERTIES"]["BUSINESS_RISKS"]["VALUE"]) && !empty($arResult["PROPERTIES"]["BUSINESS_RISKS"]["VALUE"])):?>
                <div class="bg-amber-50/50 rounded-xl p-8 border border-amber-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center bg-amber-100 rounded-full">
                            <i class="ri-funds-line text-amber-500 ri-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold">Бизнес-риски</h3>
                    </div>
                    <ul class="space-y-4">
                        <?php foreach ($arResult["PROPERTIES"]["BUSINESS_RISKS"]["VALUE"] as $iBusinessRisksIndex => $sBusinessRisksItem):?>
                            <li class="flex items-start gap-3">
                                <i class="ri-close-circle-line text-amber-500 mt-1"></i>
                                <div>
                                    <p class="font-medium mb-1"><?=tacticum_escape_iblock_text((string)$sBusinessRisksItem)?></p>
                                    <?php if (isset($arResult["PROPERTIES"]["BUSINESS_RISKS"]["DESCRIPTION"][$iBusinessRisksIndex]) && !empty($arResult["PROPERTIES"]["BUSINESS_RISKS"]["DESCRIPTION"][$iBusinessRisksIndex])):?>
                                        <p class="text-gray-600 text-sm"><?=tacticum_escape_iblock_text((string)$arResult["PROPERTIES"]["BUSINESS_RISKS"]["DESCRIPTION"][$iBusinessRisksIndex])?></p>
                                    <?php endif;?>
                                </div>
                            </li>
                        <?php endforeach;?>
                    </ul>
                </div>
            <?php endif;?>
        </div>
        <div class="bg-gradient-to-br from-primary/5 to-primary/10 rounded-xl p-8 relative overflow-hidden">
            <div class="relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-6">
                        Как Тактикум поможет снизить риски?
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-lg p-6 shadow-sm">
                            <div class="w-12 h-12 flex items-center justify-center bg-primary/10 rounded-full mx-auto mb-4">
                                <i class="ri-shield-check-line text-primary ri-lg"></i>
                            </div>
                            <h4 class="font-medium mb-2">
                                Профессиональная экспертиза
                            </h4>
                            <p class="text-gray-600 text-sm">
                                Практический опыт в реализации сложных AI- и IT-проектов
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-6 shadow-sm">
                            <div class="w-12 h-12 flex items-center justify-center bg-primary/10 rounded-full mx-auto mb-4">
                                <i class="ri-team-line text-primary ri-lg"></i>
                            </div>
                            <h4 class="font-medium mb-2">Готовая команда</h4>
                            <p class="text-gray-600 text-sm">
                                Все необходимые специалисты уже работают вместе
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-6 shadow-sm">
                            <div class="w-12 h-12 flex items-center justify-center bg-primary/10 rounded-full mx-auto mb-4">
                                <i class="ri-road-map-line text-primary ri-lg"></i>
                            </div>
                            <h4 class="font-medium mb-2">Отлаженные процессы</h4>
                            <p class="text-gray-600 text-sm">
                                Проверенная методология ведения проектов
                            </p>
                        </div>
                    </div>
                    <a href="#CTA" data-tacticum-prefill-target="#message" data-tacticum-prefill-value="<?=$projectInfo?>"
                            class="inline-flex w-fit px-8 py-3 bg-primary text-white !rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap shadow-lg text-lg font-medium items-center gap-2 mx-auto">
                        <i class="ri-shield-check-line"></i>
                        Снизить риски с командой
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
