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
                    Мы используем передовые технологии и инструменты для создания эффективных AI-решений
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
                    <h3 class="text-lg font-bold text-secondary mb-2">Обработка языка</h3>
                    <p class="text-gray-600 text-sm">BERT, GPT, NLTK, spaCy</p>
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
                    <h3 class="text-lg font-bold text-secondary mb-2">Большие данные</h3>
                    <p class="text-gray-600 text-sm">Hadoop, Spark, Kafka</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-cloud-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Инфраструктура</h3>
                    <p class="text-gray-600 text-sm">Контейнеры, хранилища, очереди и runtime-сервисы</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-code-s-slash-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Языки программирования</h3>
                    <p class="text-gray-600 text-sm">Python, Java, JavaScript</p>
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
                    <h3 class="text-lg font-bold text-secondary mb-2">Визуализация данных</h3>
                    <p class="text-gray-600 text-sm">Tableau, Power BI, D3.js</p>
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
        "FIELD_PREFIX" => "about",
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
