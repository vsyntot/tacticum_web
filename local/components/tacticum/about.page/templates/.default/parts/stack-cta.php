<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div id="stack">
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Технологии, с которыми мы работаем</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Показываем не список модных инструментов, а контуры, которые проверяем перед запуском
                    корпоративного AI-решения.
                </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-robot-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Машинное обучение</h3>
                    <p class="text-gray-600 text-sm">TensorFlow, PyTorch, scikit-learn</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-chat-3-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Поиск по знаниям</h3>
                    <p class="text-gray-600 text-sm">LLM, RAG, векторный поиск и контроль источников</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-eye-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Компьютерное зрение</h3>
                    <p class="text-gray-600 text-sm">OpenCV, YOLO, ResNet</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-database-2-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Данные и события</h3>
                    <p class="text-gray-600 text-sm">Хранилища, очереди и потоковая обработка</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-cloud-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Инфраструктура</h3>
                    <p class="text-gray-600 text-sm">Контейнеры, хранилища, очереди и сервисы запуска</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-code-s-slash-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Языки программирования</h3>
                    <p class="text-gray-600 text-sm">Python, PHP, JavaScript и интеграционный код</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-settings-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">DevOps</h3>
                    <p class="text-gray-600 text-sm">Docker, Kubernetes, CI/CD</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-dashboard-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Аналитика</h3>
                    <p class="text-gray-600 text-sm">Дашборды, события, воронки и отчетность</p>
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
        "TITLE" => "Обсудим задачу с командой Tacticum",
        "TEXT" => "Расскажите, какой результат нужен бизнесу. Мы подскажем, что лучше начать первым: оценку, discovery, команду или прототип.",
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
