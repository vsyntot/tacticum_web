<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");
$APPLICATION->SetTitle("Внедрение AI-решений и автоматизация для бизнеса - Тактикум");
$APPLICATION->SetPageProperty("description", "Tacticum проектирует и внедряет AI- и IT-решения для бизнеса: discovery, архитектура, разработка, интеграции, запуск и развитие продукта.");
tacticum_apply_seo_defaults('/services/', [
    'image' => SITE_TEMPLATE_PATH . '/images/services_hero_bg.jpg',
    'image_width' => 1536,
    'image_height' => 592,
    'schema' => [
        '@type' => 'Service',
        '@id' => tacticum_public_url('/services/#service'),
        'name' => 'Внедрение AI-решений и автоматизация для бизнеса',
        'serviceType' => 'AI consulting, ML development, chatbots and business automation',
        'provider' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
        'areaServed' => 'RU',
        'url' => tacticum_public_url('/services/'),
    ],
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<section class="services-hero-bg pt-24 min-h-[400px] flex items-center">
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-secondary">Внедрим AI-решение от идеи до рабочего процесса</h1>
            <p class="text-lg md:text-xl mb-8 text-gray-600">
                Берем бизнес-задачу, разбираем ограничения, проектируем архитектуру, собираем команду и доводим
                AI-продукт до запуска в ваших системах: CRM, ERP, документообороте, аналитике или клиентских каналах.
            </p>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="/services/#contact-form"
                        class="inline-block bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap text-center">
                    Обсудить внедрение
                </a>
                <a href="/offer/"
                        class="inline-block bg-white text-secondary border border-gray-200 px-8 py-3 rounded-button hover:border-primary hover:text-primary transition-colors whitespace-nowrap text-center">
                    Смотреть похожие расчеты
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="/offer/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-file-search-line text-2xl text-primary"></i>
                </div>
                <h2 class="text-xl font-bold text-secondary mb-2">Сначала нужна оценка</h2>
                <p class="text-gray-600">Начните с примеров расчетов по отрасли, сценарию, команде и бюджету.</p>
            </a>
            <a href="/price/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-team-line text-2xl text-primary"></i>
                </div>
                <h2 class="text-xl font-bold text-secondary mb-2">Нужна delivery-команда</h2>
                <p class="text-gray-600">Подберите роли и уровни специалистов под запуск, доработку или поддержку.</p>
            </a>
            <a href="/calculator/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-calculator-line text-2xl text-primary"></i>
                </div>
                <h2 class="text-xl font-bold text-secondary mb-2">Нужен быстрый ориентир</h2>
                <p class="text-gray-600">Опишите задачу AI-калькулятору и получите черновую структуру оценки.</p>
            </a>
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Delivery layer</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Внедрение как путь от продукта к рабочему процессу
            </h2>
            <p class="text-lg text-gray-600">
                Продуктовая линейка отвечает на вопрос, что именно запускать. Внедрение отвечает на вопрос,
                как безопасно довести это до данных, интеграций, пользователей и production-контроля.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Platform assessment</h3>
                <p class="text-gray-600">
                    Проверяем, нужен ли общий AI-контур: модели, RAG, инструменты, доступы, аудит и эксплуатация.
                </p>
            </a>
            <a href="/agents/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Agents pilot</h3>
                <p class="text-gray-600">
                    Выбираем 1-2 ассистента, готовим документы и сценарии, подключаем безопасный handoff к команде.
                </p>
            </a>
            <a href="/dev/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-code-box-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Dev workflow</h3>
                <p class="text-gray-600">
                    Описываем профиль команды, knowledge layer, design token rules и quality gates для AI-разработки.
                </p>
            </a>
            <a href="/forum/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-customer-service-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Forum launch</h3>
                <p class="text-gray-600">
                    Разбираем поток обращений, проектируем сценарный граф, LLM-обогащение, аналитику и журнал диалогов.
                </p>
            </a>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:content.list",
    "",
    [
        "NEWS_LIST_TEMPLATE" => "services",
        "IBLOCK_KEY" => "services",
        "IBLOCK_TYPE" => "services",
        "NEWS_COUNT" => "3",
        "SORT_BY1" => "SORT",
        "SORT_ORDER1" => "ASC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "PROPERTY_CODE" => ["OPTIONS"],
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ],
    false
);
?>

<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Как мы доводим AI-инициативу до запуска</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Процесс помогает не покупать технологию ради технологии: сначала проверяем ценность и данные, затем
                собираем понятный scope, команду, интеграции и план внедрения.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">1</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Discovery</h3>
                <p class="text-gray-600">Фиксируем цель, процесс, данные, риски и критерии готовности</p>
            </div>
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">2</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Архитектура</h3>
                <p class="text-gray-600">Проектируем решение, интеграции, роли команды и этапы поставки</p>
            </div>
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">3</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Разработка</h3>
                <p class="text-gray-600">Собираем MVP или production-модуль короткими управляемыми итерациями</p>
            </div>
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">4</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Внедрение</h3>
                <p class="text-gray-600">Подключаем к системам, обучаем пользователей и настраиваем контроль качества</p>
            </div>
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">5</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Развитие</h3>
                <p class="text-gray-600">Измеряем эффект, дорабатываем сценарии и масштабируем решение</p>
            </div>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:content.list",
    "",
    [
        "NEWS_LIST_TEMPLATE" => "cases",
        "IBLOCK_KEY" => "cases",
        "IBLOCK_TYPE" => "company",
        "NEWS_COUNT" => "3",
        "SORT_BY1" => "RAND",
        "SORT_ORDER1" => "DESC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ],
    false
);
?>

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
                <h3 class="text-lg font-bold text-secondary mb-2">Облачные технологии</h3>
                <p class="text-gray-600 text-sm">AWS, Google Cloud, Azure</p>
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

<?php
$APPLICATION->IncludeComponent(
    "tacticum:lead.cta",
    "",
    [
        "TYPE" => "project-discussion",
        "FORM_ID" => "services-cta",
        "FORM_HTML_ID" => "services-cta-form",
        "FIELD_PREFIX" => "services",
        "TITLE" => "Обсудим внедрение AI-решения",
        "TEXT" => "Оставьте задачу, которую нужно автоматизировать или усилить AI. Мы уточним данные, ограничения, интеграции и предложим ближайший рабочий шаг.",
        "FORM_TITLE" => "Заявка на обсуждение внедрения",
        "MESSAGE_LABEL" => "Какая бизнес-задача требует решения",
        "MESSAGE_PLACEHOLDER" => "Например: хотим снизить ручную обработку документов в отделе продаж",
        "BUTTON_TEXT" => "Обсудить внедрение",
        "LEAD_CONTEXT" => [
            "lead_entry" => "services",
            "lead_page_role" => "implementation-entry",
            "lead_intent" => "ai-automation-delivery",
            "lead_product" => "ecosystem",
            "lead_scenario" => "product-delivery",
            "lead_cta" => "services-cta",
            "lead_next_step" => "discovery-call",
        ],
    ],
    false
);
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => tacticum_iblock_id('faq'),
        "SECTION_KEY" => "services",
    ],
    false
);
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
