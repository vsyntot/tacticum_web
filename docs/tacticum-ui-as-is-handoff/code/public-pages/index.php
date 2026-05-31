<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq,chat");
$APPLICATION->SetTitle("Тактикум - AI-решения, расчет проекта и команды под задачу");
$APPLICATION->SetPageProperty("description", "Tacticum помогает оценить, спроектировать и внедрить AI- и IT-решения: расчет бюджета, подбор команды, разработка, интеграции и AI-боты для бизнеса.");
tacticum_apply_seo_defaults('/', [
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<section class="hero-bg min-h-screen pt-24 flex items-center">
    <div class="container mx-auto px-4 py-20">
        <div class="flex flex-col md:flex-row items-center gap-12">
            <div class="w-full md:w-1/2 text-white">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Оценим, спроектируем и внедрим AI-решение под вашу бизнес-задачу
                </h1>
                <p class="text-lg md:text-xl mb-8 text-blue-100">
                    Tacticum помогает компаниям пройти путь от идеи и предварительной сметы до рабочей команды,
                    интеграций и запуска AI-продукта в бизнес-процессы.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="/offer/" class="bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap text-center">Посмотреть примеры расчетов</a>
                    <a href="/services/#contact-form" class="bg-white/10 backdrop-blur-sm text-white border border-white/30 px-8 py-3 rounded-button hover:bg-white/20 transition-colors whitespace-nowrap text-center">Обсудить внедрение</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-8">
                    <a href="/calculator/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors">
                        <span class="block text-sm text-blue-100">Нужен ориентир</span>
                        <span class="block font-semibold">Рассчитать бюджет и команду</span>
                    </a>
                    <a href="/price/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors">
                        <span class="block text-sm text-blue-100">Нужны люди</span>
                        <span class="block font-semibold">Собрать команду под задачу</span>
                    </a>
                </div>
            </div>
            <?php
            $APPLICATION->IncludeComponent(
                "tacticum:chat.surface",
                "",
                [
                    "VARIANT" => "hero",
                    "SURFACE" => "hero",
                    "ROOT_CLASS" => "w-full md:w-1/2 relative",
                    "TITLE" => "AI-ассистент Tacticum",
                    "PLACEHOLDER" => "Введите ваш вопрос...",
                    "INITIAL_USER_MESSAGE" => "Как искусственный интеллект может помочь оптимизировать наши бизнес-процессы?",
                    "INTRO" => "Искусственный интеллект может значительно оптимизировать ваши бизнес-процессы через:",
                    "INTRO_ITEMS" => [
                        "Автоматизацию рутинных задач",
                        "Предиктивную аналитику для прогнозирования трендов",
                        "Интеллектуальную обработку документов",
                        "Оптимизацию цепочек поставок",
                        "Персонализацию клиентского опыта",
                    ],
                    "INTRO_OUTRO" => "Давайте обсудим, какие конкретные процессы в вашей компании требуют оптимизации?",
                ],
                false
            );
            ?>
        </div>
    </div>
</section>

<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Выберите свой вход в работу с Tacticum</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Каждый путь ведет к своему следующему шагу: точной оценке, внедрению, команде или быстрому
                прототипу AI-бота.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <a href="/offer/" class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-file-search-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Рассчитать проект</h3>
                <p class="text-gray-600">
                    Сравните похожие расчеты по отраслям и получите базу для персональной сметы.
                </p>
            </a>
            <a href="/services/" class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-settings-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">
                    Внедрить AI-решение
                </h3>
                <p class="text-gray-600">
                    Пройдем discovery, разработку, интеграции и запуск в существующие процессы.
                </p>
            </a>
            <a href="/price/" class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-team-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Собрать команду</h3>
                <p class="text-gray-600">
                    Подберите роли, уровни и загрузку, чтобы быстро оценить состав delivery-команды.
                </p>
            </a>
            <a href="/aiagents/" class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-robot-2-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Запустить AI-бота</h3>
                <p class="text-gray-600">
                    Проверьте Telegram-сценарий на демо-агентах и запросите прототип под вашу воронку.
                </p>
            </a>
        </div>
    </div>
</section>

<?
$APPLICATION->IncludeComponent(
    "tacticum:content.list",
    "",
    [
        "NEWS_LIST_TEMPLATE" => "cases",
        "IBLOCK_KEY" => "cases",
        "IBLOCK_TYPE" => "company",
        "NEWS_COUNT" => "3",
        "SORT_BY1" => "RAND",
        "SORT_ORDER1" => "ASC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ],
    false
);
?>

<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <?
        $APPLICATION->IncludeComponent(
            "tacticum:content.list",
            "",
            [
                "NEWS_LIST_TEMPLATE" => "feedback",
                "IBLOCK_KEY" => "feedback",
                "IBLOCK_TYPE" => "company",
                "NEWS_COUNT" => "3",
                "SORT_BY1" => "SORT",
                "SORT_ORDER1" => "ASC",
                "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
                "PROPERTY_CODE" => ["NAME", "COMPANY", "POSITION", "RATING"],
                "DISPLAY_BOTTOM_PAGER" => "Y",
            ],
            false
        );
        ?>
    </div>
</section>

<div id="calculator-root">
    <section id="calculator" class="py-20 bg-gradient-to-r from-secondary to-primary text-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">
                        Хотите понять бюджет до старта разработки?
                    </h2>
                    <p class="text-lg mb-8 text-blue-100">
                        AI-калькулятор собирает вводные и готовит предварительный артефакт: диапазон бюджета,
                        сроки, роли в команде, ключевые риски и следующий шаг для точной сметы.
                    </p>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 mb-6">
                        <div class="flex flex-col h-[400px]">
                            <div class="flex-1 overflow-y-auto mb-4 space-y-4" id="chatMessages">
                                <div class="bg-white/5 rounded-lg p-3">
                                    <p class="text-sm text-white/70 mb-1">AI-ассистент:</p>
                                    <p class="text-white">
                                        Здравствуйте! Я помогу оценить ваш AI-проект. Расскажите,
                                        какую задачу вы хотите решить?
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" id="userMessage" placeholder="Введите сообщение..." class="flex-1 bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/30"/>
                                <button id="sendMessage" class="bg-white text-primary w-12 h-12 rounded-full flex items-center justify-center hover:bg-white/90 transition-colors">
                                    <i class="ri-send-plane-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-1/2">
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 shadow-lg">
                        <h3 class="text-xl font-bold mb-6">Примеры оценок проектов</h3>
                        <div class="space-y-6">
                            <div class="bg-white/10 rounded-lg p-4">
                                <h4 class="font-bold mb-2">Система предиктивного обслуживания оборудования</h4>
                                <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                    <div>
                                        <p class="text-white/60">Отрасль:</p>
                                        <p>Производство</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сложность:</p>
                                        <p>Средняя</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сроки:</p>
                                        <p>3-4 месяца</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Команда:</p>
                                        <p>5 специалистов</p>
                                    </div>
                                </div>
                                <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden">
                                    <div class="bg-primary h-full tacticum-progress-bar--65"></div>
                                </div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-4">
                                <h4 class="font-bold mb-2">Чат-бот с AI для клиентской поддержки</h4>
                                <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                    <div>
                                        <p class="text-white/60">Отрасль:</p>
                                        <p>Электронная коммерция</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сложность:</p>
                                        <p>Низкая</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сроки:</p>
                                        <p>1-2 месяца</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Команда:</p>
                                        <p>3 специалиста</p>
                                    </div>
                                </div>
                                <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden">
                                    <div class="bg-primary h-full tacticum-progress-bar--35"></div>
                                </div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-4">
                                <h4 class="font-bold mb-2">
                                    Система компьютерного зрения для контроля качества
                                </h4>
                                <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                    <div>
                                        <p class="text-white/60">Отрасль:</p>
                                        <p>Автомобилестроение</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сложность:</p>
                                        <p>Высокая</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сроки:</p>
                                        <p>5-6 месяцев</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Команда:</p>
                                        <p>7 специалистов</p>
                                    </div>
                                </div>
                                <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden">
                                    <div class="bg-primary h-full tacticum-progress-bar--85"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => tacticum_iblock_id('faq'),
        "SECTION_KEY" => "home",
    ],
    false
);
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:lead.cta",
    "",
    [
        "TYPE" => "personal-offer",
        "FORM_ID" => "home-cta",
        "FORM_HTML_ID" => "cta-form",
        "TITLE" => "Получите точную оценку под вашу задачу",
        "TEXT" => "Опишите бизнес-задачу, желаемый срок и текущие ограничения. Мы свяжемся, уточним вводные и подготовим следующий шаг: discovery, расчет или подбор команды.",
        "MESSAGE_LABEL" => "Кратко опишите задачу",
        "MESSAGE_PLACEHOLDER" => "Например: хотим автоматизировать обработку заявок, есть CRM и база исторических обращений",
        "BUTTON_TEXT" => "Получить следующий шаг",
        "LEAD_CONTEXT" => [
            "lead_entry" => "home",
            "lead_page_role" => "main-router",
            "lead_intent" => "choose-commercial-entry",
            "lead_cta" => "home-cta",
            "lead_next_step" => "qualification-call-or-project-estimate",
        ],
    ],
    false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
