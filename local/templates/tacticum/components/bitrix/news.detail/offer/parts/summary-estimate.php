<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<section class="pt-20 py-12 md:py-32 bg-white">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl md:text-3xl font-bold text-center mb-12">
            <?=htmlspecialcharsbx($offerH1)?>
        </h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-file-list-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">
                            Краткое описание вашей потребности
                        </h3>
                        <p class="text-gray-700">
                            <?=$summaryText?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-crosshair-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Основные цели MVP</h3>
                        <?php if (!empty($goals)):?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?php foreach ($goals as $sGoal):?>
                                    <li><?=htmlspecialcharsbx($sGoal)?></li>
                                <?php endforeach;?>
                            </ul>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-function-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">
                            Ключевые функциональные требования
                        </h3>
                        <?php if (!empty($functionalRequirements)):?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?php foreach ($functionalRequirements as $sFR):?>
                                    <li><?=htmlspecialcharsbx($sFR)?></li>
                                <?php endforeach;?>
                            </ul>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-settings-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">
                            Нефункциональные требования
                        </h3>
                        <?php if (!empty($nonfunctionalRequirements)):?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?php foreach ($nonfunctionalRequirements as $sNFR):?>
                                    <li><?=htmlspecialcharsbx($sNFR)?></li>
                                <?php endforeach;?>
                            </ul>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-team-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Команда проекта</h3>
                        <?php if (!empty($teamMembers)):?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?php foreach ($teamMembers as $sTeamMember):?>
                                    <li><?=htmlspecialcharsbx($sTeamMember)?></li>
                                <?php endforeach;?>
                            </ul>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-code-s-slash-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">
                            Технологический стек
                        </h3>
                        <?php if (!empty($stackItems)):?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?php foreach ($stackItems as $sStackItem):?>
                                    <li><?=htmlspecialcharsbx($sStackItem)?></li>
                                <?php endforeach;?>
                            </ul>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-secondary to-primary text-white rounded-lg p-4 sm:p-6 md:p-8 shadow-xl md:col-span-2 relative overflow-hidden">
                <div class="absolute inset-0 bg-white/10"></div>

                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row md:items-start gap-6 md:gap-10 mb-6">
                        <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center bg-white/10 rounded-full mx-auto md:mx-0 md:mr-4">
                            <i class="ri-money-dollar-circle-line text-white ri-xl"></i>
                        </div>
                        <div class="w-full">
                            <h3 class="text-2xl sm:text-2xl font-semibold mb-6 sm:mb-8 text-white text-center md:text-left">
                                Предварительная оценка MVP
                            </h3>
                            <div class="bg-white/10 backdrop-blur-sm p-4 sm:p-6 md:p-8 rounded-lg border border-white/10 mb-6 sm:mb-8">
                                <div class="flex flex-col md:flex-row justify-between items-center gap-6 md:gap-8">
                                    <div class="text-center md:text-left mb-4 md:mb-0">
                                        <h4 class="text-base sm:text-lg text-white/80 mb-2 sm:mb-3">
                                            Стоимость разработки
                                        </h4>
                                        <p class="text-white font-bold text-xl sm:text-3xl md:text-4xl whitespace-nowrap">
                                            <?=$budget?>*
                                        </p>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-6 sm:gap-12 items-stretch">
                                        <div class="text-center flex flex-col justify-between">
                                            <div class="w-12 h-12 sm:w-16 sm:h-16 flex items-center justify-center bg-white/10 rounded-full mb-2 sm:mb-3 mx-auto border border-white/10">
                                                <i class="ri-time-line text-white ri-xl"></i>
                                            </div>
                                            <p class="text-white/80 flex-1 text-xs sm:text-sm mb-1 sm:mb-2">
                                                Плановый срок
                                            </p>
                                            <p class="text-white font-semibold text-base sm:text-lg">
                                                <?=$timeline?>
                                            </p>
                                        </div>
                                        <div class="text-center flex flex-col justify-between">
                                            <div class="w-12 h-12 sm:w-16 sm:h-16 flex items-center justify-center bg-white/10 rounded-full mb-2 sm:mb-3 mx-auto border border-white/10">
                                                <i class="ri-team-line text-white ri-xl"></i>
                                            </div>
                                            <p class="text-white/80 flex-1 text-xs sm:text-sm mb-1 sm:mb-2">Команда</p>
                                            <p class="text-white font-semibold text-base sm:text-lg">
                                                <?=count($teamMembers)?> человек
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-white/60 mt-4 sm:mt-6 text-center md:text-left">
                                * Окончательная стоимость будет определена после детального анализа требований и
                                согласования объема работ.
                            </p>
                        </div>
                        <div class="flex flex-col items-center text-center mt-6 md:mt-8 md:ml-6 w-full md:w-auto">
                            <p class="text-white/80 text-base sm:text-lg mb-4 sm:mb-6">
                                Похожая задача не заменяет персональную смету. Отправьте контекст примера, и мы
                                уточним scope, данные, интеграции и состав команды под ваш проект.
                            </p>
                            <a href="#CTA" data-tacticum-prefill-target="#message" data-tacticum-prefill-value="<?=$projectInfo?>" class="bg-primary text-white px-6 sm:px-8 py-2 sm:py-3 rounded-button hover:bg-primary/50 transition-colors whitespace-nowrap">
                                <i class="ri-mail-send-line"></i>
                                Уточнить по своей задаче
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
