<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div id="stack">
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Что проверяем перед запуском</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Для корпоративного AI важен не перечень библиотек, а готовность решения к данным, пользователям,
                    интеграциям, рискам и сопровождению после пилота.
                </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-compass-3-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Сценарий</h3>
                    <p class="text-gray-600 text-sm">Цель, пользователь, границы пилота и критерии результата</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-database-2-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Данные</h3>
                    <p class="text-gray-600 text-sm">Источники, качество, доступы и правила использования</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-chat-3-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Ответы AI</h3>
                    <p class="text-gray-600 text-sm">Модели, знания, память, ограничения и проверка источников</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-plug-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Интеграции</h3>
                    <p class="text-gray-600 text-sm">CRM, ERP, базы знаний, документы и рабочие системы</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-shield-check-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Контроль</h3>
                    <p class="text-gray-600 text-sm">Роли, аудит, журналирование и управляемые проверки качества</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-dashboard-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Наблюдаемость</h3>
                    <p class="text-gray-600 text-sm">События, ошибки, стоимость запросов и поведение пользователей</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-cloud-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Эксплуатация</h3>
                    <p class="text-gray-600 text-sm">Среда запуска, обновления, поддержка и зона ответственности</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-calculator-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Экономика</h3>
                    <p class="text-gray-600 text-sm">Состав работ, команда, бюджетный диапазон и следующий шаг</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:lead.cta",
    "",
    [
        "TYPE" => "project-discussion",
        "FORM_ID" => "about-cta",
        "FORM_HTML_ID" => "about-cta-form",
        "FIELD_PREFIX" => "about-cta",
        "TITLE" => "Сверим задачу и первый безопасный шаг",
        "TEXT" => "Опишите бизнес-сценарий, ограничения и желаемый результат. Мы подскажем, что разумнее делать сначала: оценку, пилот, интеграцию или команду под запуск.",
        "FORM_TITLE" => "Оставить заявку",
        "BUTTON_TEXT" => "Обсудить задачу",
        "LEAD_CONTEXT" => [
            "lead_entry" => "about",
            "lead_page_role" => "trust-entry",
            "lead_intent" => "discuss-company-fit",
            "lead_product" => "ecosystem",
            "lead_cta" => "about-cta",
            "lead_next_step" => "qualification-call",
        ],
    ],
    false
);
?>
