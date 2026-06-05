<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<section class="py-20 bg-white" data-home-block="commercial-next-steps">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Выберите следующий коммерческий шаг</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Продуктовая модель не заменяет текущие входы. Можно начать с оценки, внедрения, команды или
                быстрого AI-бота, если так проще проверить гипотезу.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <a href="/offer/" class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300" data-home-commercial-link="offer">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-file-search-line text-3xl text-primary" aria-hidden="true"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Рассчитать проект</h3>
                <p class="text-gray-600">
                    Сравните похожие расчеты по отраслям и получите базу для персональной сметы.
                </p>
            </a>
            <a href="/services/" class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300" data-home-commercial-link="services">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-settings-line text-3xl text-primary" aria-hidden="true"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">
                    Внедрить AI-решение
                </h3>
                <p class="text-gray-600">
                    Пройдем discovery, разработку, интеграции и запуск в существующие процессы.
                </p>
            </a>
            <a href="/price/" class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300" data-home-commercial-link="price">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-team-line text-3xl text-primary" aria-hidden="true"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Собрать команду</h3>
                <p class="text-gray-600">
                    Подберите роли, уровни и загрузку, чтобы быстро оценить состав delivery-команды.
                </p>
            </a>
            <a href="/aiagents/" class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300" data-home-commercial-link="aiagents">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-robot-2-line text-3xl text-primary" aria-hidden="true"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Запустить AI-бота</h3>
                <p class="text-gray-600">
                    Проверьте Telegram-сценарий на демо-агентах и запросите прототип под вашу воронку.
                </p>
            </a>
        </div>
    </div>
</section>
