<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$curPage = $APPLICATION->GetCurPage();
?>

<!-- Project Summary Section -->
<section class="pt-20 py-12 md:py-32 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-12">
            Предварительная оценка вашего проекта
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Project Description -->
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
                            <?=$arResult["PROPERTIES"]["SUMMARY"]["VALUE"]["TEXT"]?>
                        </p>
                    </div>
                </div>
            </div>
            <!-- MVP Goals -->
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-target-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Основные цели MVP</h3>
                        <?if(is_array($arResult["PROPERTIES"]["GOALS"]["VALUE"]) && !empty($arResult["PROPERTIES"]["GOALS"]["VALUE"])){?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?foreach ($arResult["PROPERTIES"]["GOALS"]["VALUE"] as $sGoal){?>
                                    <li><?=$sGoal?></li>
                                <?}?>
                            </ul>
                        <?}?>
                    </div>
                </div>
            </div>
            <!-- Functional Requirements -->
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-function-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">
                            Ключевые функциональные требования
                        </h3>
                        <?if(is_array($arResult["PROPERTIES"]["FUNCTIONAL_REQUIREMENTS"]["VALUE"]) && !empty($arResult["PROPERTIES"]["FUNCTIONAL_REQUIREMENTS"]["VALUE"])){?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?foreach ($arResult["PROPERTIES"]["FUNCTIONAL_REQUIREMENTS"]["VALUE"] as $sFR){?>
                                    <li><?=$sFR?></li>
                                <?}?>
                            </ul>
                        <?}?>
                    </div>
                </div>
            </div>
            <!-- Non-functional Requirements -->
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-settings-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">
                            Нефункциональные требования
                        </h3>
                        <?if(is_array($arResult["PROPERTIES"]["NONFUNCTIONAL_REQUIREMENTS"]["VALUE"]) && !empty($arResult["PROPERTIES"]["NONFUNCTIONAL_REQUIREMENTS"]["VALUE"])){?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?foreach ($arResult["PROPERTIES"]["NONFUNCTIONAL_REQUIREMENTS"]["VALUE"] as $sNFR){?>
                                    <li><?=$sNFR?></li>
                                <?}?>
                            </ul>
                        <?}?>
                    </div>
                </div>
            </div>
            <!-- Project Team -->
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-team-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Команда проекта</h3>
                        <?if(is_array($arResult["PROPERTIES"]["TEAM"]["VALUE"]) && !empty($arResult["PROPERTIES"]["TEAM"]["VALUE"])){?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?foreach ($arResult["PROPERTIES"]["TEAM"]["VALUE"] as $sTeamMember){?>
                                    <li><?=$sTeamMember?></li>
                                <?}?>
                            </ul>
                        <?}?>
                    </div>
                </div>
            </div>
            <!-- Technology Stack -->
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                        <i class="ri-code-s-slash-line text-primary ri-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2">
                            Технологический стек
                        </h3>
                        <?if(is_array($arResult["PROPERTIES"]["STACK"]["VALUE"]) && !empty($arResult["PROPERTIES"]["STACK"]["VALUE"])){?>
                            <ul class="list-disc list-inside text-gray-700 space-y-2">
                                <?foreach ($arResult["PROPERTIES"]["STACK"]["VALUE"] as $sStackItem){?>
                                    <li><?=$sStackItem?></li>
                                <?}?>
                            </ul>
                        <?}?>
                    </div>
                </div>
            </div>
            <!-- Budget -->
            <div class="to-primary rounded-lg p-4 sm:p-6 md:p-8 shadow-xl md:col-span-2 relative overflow-hidden">
                <!-- Фон (абсолютный) fixit -->
                <div class="absolute inset-0 opacity-10 bg-[url('https://readdy.ai/api/search-image?query=abstract%2520geometric%2520pattern%2520with%2520deep%2520blue%2520gradient%2520and%2520subtle%2520tech%2520lines%2C%2520modern%2520minimal%2520design&amp;width=800&amp;height=400&amp;seq=budget1&amp;orientation=landscape')] bg-cover bg-center"></div>

                <div class="relative z-10">
                    <!-- flex-column на моб, row на md+ -->
                    <div class="flex flex-col md:flex-row md:items-start gap-6 md:gap-10 mb-6">
                        <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center bg-white/10 rounded-full mx-auto md:mx-0 md:mr-4">
                            <i class="ri-money-dollar-circle-line text-white ri-xl"></i>
                        </div>
                        <div class="w-full">
                            <h3 class="text-2xl sm:text-2xl font-semibold mb-6 sm:mb-8 text-white text-center md:text-left">
                                Предварительная оценка MVP
                            </h3>
                            <div class="bg-white/10 backdrop-blur-sm p-4 sm:p-6 md:p-8 rounded-lg border border-white/10 mb-6 sm:mb-8">
                                <!-- flex-column на моб, row на md+ -->
                                <div class="flex flex-col md:flex-row justify-between items-center gap-6 md:gap-8">
                                    <div class="text-center md:text-left mb-4 md:mb-0">
                                        <h4 class="text-base sm:text-lg text-white/80 mb-2 sm:mb-3">
                                            Стоимость разработки
                                        </h4>
                                        <p class="text-white font-bold text-xl sm:text-3xl md:text-4xl whitespace-nowrap">
                                            <?=$arResult["PROPERTIES"]["BUDGET"]["VALUE"]?>*
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
                                                <?=$arResult["PROPERTIES"]["TIMELINE"]["VALUE"]?>
                                            </p>
                                        </div>
                                        <div class="text-center flex flex-col justify-between">
                                            <div class="w-12 h-12 sm:w-16 sm:h-16 flex items-center justify-center bg-white/10 rounded-full mb-2 sm:mb-3 mx-auto border border-white/10">
                                                <i class="ri-team-line text-white ri-xl"></i>
                                            </div>
                                            <p class="text-white/80 flex-1 text-xs sm:text-sm mb-1 sm:mb-2">Команда</p>
                                            <p class="text-white font-semibold text-base sm:text-lg">
                                                <?=count($arResult["PROPERTIES"]["TEAM"]["VALUE"])?> человек
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
                                Хотите получить индивидуальное коммерческое предложение, технико-экономическое
                                обоснование или задать вопросы по архитектуре и команде?
                            </p>
                            <button onclick="window.location.href='<?=$curPage?>#CTA';" class="bg-primary text-white px-6 sm:px-8 py-2 sm:py-3 rounded-button hover:bg-primary/50 transition-colors whitespace-nowrap">
                                <i class="ri-mail-send-line"></i>
                                Получить предложение
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Risk Section -->
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
            <?if(is_array($arResult["PROPERTIES"]["TECH_RISKS"]["VALUE"]) && !empty($arResult["PROPERTIES"]["TECH_RISKS"]["VALUE"])){?>
                <div class="bg-red-50/50 rounded-xl p-8 border border-red-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center bg-red-100 rounded-full">
                            <i class="ri-error-warning-line text-red-500 ri-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold">Технологические риски</h3>
                    </div>
                    <ul class="space-y-4">
                        <?foreach ($arResult["PROPERTIES"]["TECH_RISKS"]["VALUE"] as $iTechRisksIndex => $sTechRisksItem){?>
                            <li class="flex items-start gap-3">
                                <i class="ri-close-circle-line text-red-500 mt-1"></i>
                                <div>
                                    <p class="font-medium mb-1"><?=$sTechRisksItem?></p>
                                    <?if(isset($arResult["PROPERTIES"]["TECH_RISKS"]["DESCRIPTION"][$iTechRisksIndex]) && !empty($arResult["PROPERTIES"]["TECH_RISKS"]["DESCRIPTION"][$iTechRisksIndex])){?>
                                        <p class="text-gray-600 text-sm"><?=$arResult["PROPERTIES"]["TECH_RISKS"]["DESCRIPTION"][$iTechRisksIndex]?></p>
                                    <?}?>
                                </div>
                            </li>
                        <?}?>
                    </ul>
                </div>
            <?}?>
            <?if(is_array($arResult["PROPERTIES"]["BUSINESS_RISKS"]["VALUE"]) && !empty($arResult["PROPERTIES"]["BUSINESS_RISKS"]["VALUE"])){?>
                <div class="bg-amber-50/50 rounded-xl p-8 border border-amber-100">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center bg-amber-100 rounded-full">
                            <i class="ri-funds-line text-amber-500 ri-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold">Бизнес-риски</h3>
                    </div>
                    <ul class="space-y-4">
                        <?foreach ($arResult["PROPERTIES"]["BUSINESS_RISKS"]["VALUE"] as $iBusinessRisksIndex => $sBusinessRisksItem){?>
                            <li class="flex items-start gap-3">
                                <i class="ri-close-circle-line text-amber-500 mt-1"></i>
                                <div>
                                    <p class="font-medium mb-1"><?=$sBusinessRisksItem?></p>
                                    <?if(isset($arResult["PROPERTIES"]["BUSINESS_RISKS"]["DESCRIPTION"][$iBusinessRisksIndex]) && !empty($arResult["PROPERTIES"]["BUSINESS_RISKS"]["DESCRIPTION"][$iBusinessRisksIndex])){?>
                                        <p class="text-gray-600 text-sm"><?=$arResult["PROPERTIES"]["BUSINESS_RISKS"]["DESCRIPTION"][$iBusinessRisksIndex]?></p>
                                    <?}?>
                                </div>
                            </li>
                        <?}?>
                    </ul>
                </div>
            <?}?>
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
                                Более 10 лет опыта в реализации сложных IT-проектов
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
                    <button onclick="window.location.href='<?=$curPage?>#CTA';"
                            class="px-8 py-3 bg-primary text-white !rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap shadow-lg text-lg font-medium flex items-center gap-2 mx-auto">
                        <i class="ri-shield-check-line"></i>
                        Получить консультацию
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section id='CTA' class="py-12 md:py-16 bg-gradient-to-b from-white to-indigo-50/50">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto bg-white rounded-xl p-8 shadow-lg">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-6">
                Готовы обсудить ваш проект?
            </h2>
            <p class="text-center text-gray-700 mb-8">
                Мы открыты к диалогу на любом этапе: проконсультируем по вашей идее, проведём аудит или оперативно
                подключимся к разработке. Оставьте заявку — и наш эксперт свяжется с вами для обсуждения деталей и
                возможных шагов сотрудничества.
            </p>

            <form id="applicationForm" class="space-y-6" data-tacticum-form data-form-id="offer-cta">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Имя</label>
                        <input type="text" id="name" name="name" required placeholder="Укажите как к Вам обращаться"
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                    <div>
                        <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Компания</label>
                        <input type="text" id="company" name="company" placeholder="Укажите название Вашей компании"
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Укажите Вашу почту"
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                        <input type="tel" id="phone" name="phone" required placeholder="Укажите Ваш контактный номер"
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Дополнительная
                        информация</label>
                    <textarea id="message" name="message" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50"
                              placeholder="Укажите любые дополнительные пожелания или вопросы..."></textarea>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="consent" name="consent" data-tacticum-consent required checked>
                    <label for="consent" class="text-sm text-gray-600">
                        Я согласен на обработку персональных данных и принимаю
                        <a href="#" class="text-primary hover:underline">условия использования</a>
                    </label>
                </div>

                <div class="flex justify-center">
                    <button type="submit"
                            class="px-8 py-3 bg-primary text-white !rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap shadow-lg shadow-primary/30 text-lg">
                        Отправить заявку
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-12 md:py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-12">
            Почему стоит доверить реализацию нам
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="w-14 h-14 flex items-center justify-center bg-primary/10 rounded-full mb-6 mx-auto">
                    <i class="ri-money-dollar-circle-line text-primary ri-2x"></i>
                </div>
                <h3 class="text-xl font-semibold text-center mb-4">
                    Прозрачная оценка бюджета и сроков
                </h3>
                <p class="text-gray-700 text-center">
                    Мы предоставляем детальную смету и поэтапный план работ от MVP
                    до полномасштабного решения. Вы всегда знаете, за что платите и
                    когда получите результат.
                </p>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="w-14 h-14 flex items-center justify-center bg-primary/10 rounded-full mb-6 mx-auto">
                    <i class="ri-bank-line text-primary ri-2x"></i>
                </div>
                <h3 class="text-xl font-semibold text-center mb-4">
                    Экспертиза в различных отраслях
                </h3>
                <p class="text-gray-700 text-center">
                    Наша команда имеет богатый опыт разработки в банковской сфере,
                    ритейле, телекоме и стартапах. Мы понимаем специфику вашего
                    бизнеса и предлагаем оптимальные решения.
                </p>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-lg shadow-gray-100/50 hover:shadow-xl hover:shadow-gray-100/50 transition-shadow">
                <div class="w-14 h-14 flex items-center justify-center bg-primary/10 rounded-full mb-6 mx-auto">
                    <i class="ri-rocket-line text-primary ri-2x"></i>
                </div>
                <h3 class="text-xl font-semibold text-center mb-4">
                    Быстрый старт работ
                </h3>
                <p class="text-gray-700 text-center">
                    Мы готовы начать работу над вашим проектом в течение 7 дней
                    после согласования условий. Наша гибкая методология позволяет
                    быстро адаптироваться к изменениям требований.
                </p>
            </div>
        </div>
        <div class="mt-12 text-center">
            <div class="flex flex-wrap justify-center gap-4">
                <div class="flex items-center bg-gray-100 px-4 py-2 rounded-full">
                    <div class="w-8 h-8 flex items-center justify-center bg-primary/10 rounded-full mr-2">
                        <i class="ri-check-line text-primary"></i>
                    </div>
                    <span class="text-gray-700">Более 150 успешных проектов</span>
                </div>
                <div class="flex items-center bg-gray-100 px-4 py-2 rounded-full">
                    <div class="w-8 h-8 flex items-center justify-center bg-primary/10 rounded-full mr-2">
                        <i class="ri-check-line text-primary"></i>
                    </div>
                    <span class="text-gray-700">Команда из 50+ специалистов</span>
                </div>
                <div class="flex items-center bg-gray-100 px-4 py-2 rounded-full">
                    <div class="w-8 h-8 flex items-center justify-center bg-primary/10 rounded-full mr-2">
                        <i class="ri-check-line text-primary"></i>
                    </div>
                    <span class="text-gray-700">Поддержка 24/7</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?
$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "faq",
    [
        "COMPONENT_TEMPLATE" => "faq",
        "IBLOCK_TYPE" => "company",
        "IBLOCK_ID" => "10",
        "NEWS_COUNT" => "0",
        "SORT_BY1" => "SORT",
        "SORT_ORDER1" => "ASC",
        "SORT_BY2" => "ID",
        "SORT_ORDER2" => "DESC",
        "FILTER_NAME" => "",
        "FIELD_CODE" => [
            0 => "ID",
            1 => "CODE",
            2 => "NAME",
            3 => "SORT",
            4 => "DETAIL_TEXT",
            5 => "IBLOCK_TYPE_ID",
            6 => "IBLOCK_ID",
            7 => "",
        ],
        "PROPERTY_CODE" => [
            0 => "",
            1 => "",
        ],
        "CHECK_DATES" => "Y",
        "DETAIL_URL" => "",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "36000000",
        "CACHE_FILTER" => "N",
        "CACHE_GROUPS" => "Y",
        "PREVIEW_TRUNCATE_LEN" => "",
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "SET_TITLE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_LAST_MODIFIED" => "N",
        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
        "ADD_SECTIONS_CHAIN" => "N",
        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
        "PARENT_SECTION" => "19",
        "PARENT_SECTION_CODE" => "",
        "INCLUDE_SUBSECTIONS" => "N",
        "STRICT_SECTION_CHECK" => "N",
        "PAGER_TEMPLATE" => ".default",
        "DISPLAY_TOP_PAGER" => "N",
        "DISPLAY_BOTTOM_PAGER" => "N",
        "PAGER_TITLE" => "Новости",
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "N",
        "PAGER_BASE_LINK_ENABLE" => "N",
        "SET_STATUS_404" => "N",
        "SHOW_404" => "N",
        "MESSAGE_404" => ""
    ],
    false
);
?>
