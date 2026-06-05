<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-phone-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Телефон</h3>
                <p class="text-gray-600 mb-6">
                    Мы доступны с понедельника по пятницу с 9:00 до 18:00 по
                    московскому времени
                </p>
                <div class="mt-auto">
                    <a href="tel:+74955612084" class="text-lg font-medium text-primary hover:text-primary/80 transition-colors flex items-center gap-2">+7 (495) 561-20-84<i class="ri-external-link-line"></i></a>
                </div>
            </div>

            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-mail-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Email</h3>
                <p class="text-gray-600 mb-6">Отправьте нам email, и мы ответим вам в течение 24 часов в рабочие дни</p>
                <div class="mt-auto">
                    <a href="mailto:project@tacticum.ru" class="text-lg font-medium text-primary hover:text-primary/80 transition-colors flex items-center gap-2">project@tacticum.ru<i class="ri-external-link-line"></i></a>
                </div>
            </div>

            <div
                class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-map-pin-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Офис</h3>
                <p class="text-gray-700 font-medium mb-3">
                    <?=htmlspecialcharsbx($arResult['OFFICE_PLACE_NAME'])?>, <?=htmlspecialcharsbx($arResult['OFFICE_LANDMARK_NAME'])?>
                </p>
                <p class="text-gray-600 mb-6">Юридический адрес: <?=htmlspecialcharsbx($arResult['OFFICE_ADDRESS'])?></p>
                <div class="mt-auto">
                    <a href="#map" class="text-lg font-medium text-primary hover:text-primary/80 transition-colors flex items-center gap-2">Показать на карте<i class="ri-arrow-down-line"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
