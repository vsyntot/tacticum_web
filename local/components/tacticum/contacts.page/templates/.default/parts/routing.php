<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Куда направить обращение</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Выберите ближайший следующий шаг
            </h2>
            <p class="text-lg text-gray-600">
                Если вы пока не уверены, с чего начать, опишите задачу в форме ниже. Мы маршрутизируем обращение:
                к продуктовой консультации, delivery-команде, оценке проекта или подбору специалистов.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Продуктовый пилот</h3>
                <p class="text-gray-600">
                    Platform, Agents, Dev или Forum: поможем выбрать продуктовый вход и формат проверки.
                </p>
            </a>
            <a href="/services/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-route-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Внедрение</h3>
                <p class="text-gray-600">
                    Discovery, архитектура, интеграции, запуск и развитие AI-решения в ваших процессах.
                </p>
            </a>
            <a href="/calculator/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-calculator-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Оценка проекта</h3>
                <p class="text-gray-600">
                    Быстрый ориентир по бюджету, срокам, ролям, рискам и следующему шагу.
                </p>
            </a>
            <a href="/price/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-team-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Команда</h3>
                <p class="text-gray-600">
                    Роли, уровни, загрузка и заявка на подключение специалистов под задачу.
                </p>
            </a>
        </div>
    </div>
</section>
