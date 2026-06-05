<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<section class="pt-32 pb-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_380px] gap-10 lg:gap-14 items-start">
            <div>
                <h1 class="text-3xl md:text-5xl font-bold text-secondary mb-6">
                    Примеры расчетов AI- и IT-проектов
                </h1>
                <p class="text-lg text-gray-600 max-w-3xl mb-10">
                    Собрали типовые оценки по отраслям, задачам, командам и бюджетам. Используйте их как ориентир,
                    а не как финальную смету: точную оценку нужно уточнять по вашим данным, интеграциям и ограничениям.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="/calculator/" class="inline-flex items-center justify-center gap-2 rounded-button bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                        <i class="ri-calculator-line"></i>
                        Рассчитать свой проект
                    </a>
                    <a href="#offer-catalog" class="inline-flex items-center justify-center gap-2 rounded-button border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors">
                        <i class="ri-list-check-2"></i>
                        Смотреть примеры
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-3xl font-bold text-secondary"><?=number_format((int)$offerCatalog['all_total'], 0, '', ' ')?></p>
                    <p class="text-sm text-gray-600 mt-1">активных расчетов</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-3xl font-bold text-secondary"><?=number_format((int)$offerStats['sectors'], 0, '', ' ')?></p>
                    <p class="text-sm text-gray-600 mt-1">отраслей</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-3xl font-bold text-secondary"><?=number_format((int)$offerStats['scenarios'], 0, '', ' ')?></p>
                    <p class="text-sm text-gray-600 mt-1">типов задач</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-2xl font-bold text-secondary"><?=htmlspecialcharsbx($offerMoney((int)$offerStats['budget_max']))?></p>
                    <p class="text-sm text-gray-600 mt-1">верхняя оценка</p>
                </div>
            </div>
        </div>
    </div>
</section>
