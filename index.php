<?
$GLOBALS['TACTICUM_PAGE_ASSETS'] = ['faq'];
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Тактикум - Искусственный интеллект для вашего бизнеса");
$APPLICATION->SetPageProperty("description", "AI-решения для бизнеса: автоматизация процессов, AI-консалтинг, внедрение ML и интеллектуальных ассистентов от Tacticum.");
tacticum_apply_seo_defaults('/');
?>

<!-- Hero Section -->
<section class="hero-bg min-h-screen pt-24 flex items-center">
    <div class="container mx-auto px-4 py-20">
        <div class="flex flex-col md:flex-row items-center gap-12">
            <div class="w-full md:w-1/2 text-white">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">Искусственный интеллект для реального бизнеса</h1>
                <p class="text-lg md:text-xl mb-8 text-blue-100">
                    Tacticum — компания, помогающая автоматизировать процессы,
                    усиливать аналитику и расти с помощью современных AI-решений.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#calculator" class="bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap text-center">Оценить свою идею</a>
                    <a href="#contact-form" class="bg-white/10 backdrop-blur-sm text-white border border-white/30 px-8 py-3 rounded-button hover:bg-white/20 transition-colors whitespace-nowrap text-center">Получить консультацию</a>
                </div>
            </div>
            <div class="w-full md:w-1/2 relative" id="main_chat">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-lg">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        <div class="text-white/70 text-sm">AI-ассистент Tacticum</div>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-white/10 rounded-lg p-3 text-white">
                            <p class="text-sm text-white/70 mb-1">Пользователь:</p>
                            <p>
                                Как искусственный интеллект может помочь оптимизировать наши
                                бизнес-процессы?
                            </p>
                        </div>
                        <div class="bg-primary/20 rounded-lg p-3 text-white">
                            <p class="text-sm text-white/70 mb-1">AI-ассистент:</p>
                            <p>
                                Искусственный интеллект может значительно оптимизировать
                                ваши бизнес-процессы через:
                            </p>
                            <ul class="list-disc pl-5 mt-2 space-y-1">
                                <li>Автоматизацию рутинных задач</li>
                                <li>Предиктивную аналитику для прогнозирования трендов</li>
                                <li>Интеллектуальную обработку документов</li>
                                <li>Оптимизацию цепочек поставок</li>
                                <li>Персонализацию клиентского опыта</li>
                            </ul>
                            <p class="mt-2">
                                Давайте обсудим, какие конкретные процессы в вашей компании
                                требуют оптимизации?
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" placeholder="Введите ваш вопрос..." class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                            <button id="aichat" class="bg-primary w-10 h-10 rounded-full flex items-center justify-center text-white"><i class="ri-send-plane-fill"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Что мы делаем?</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Мы помогаем компаниям внедрять передовые технологии искусственного
                интеллекта для решения реальных бизнес-задач
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Feature 1 -->
            <div class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-ai-generate-fill text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Внедряем AI/ML-решения</h3>
                <p class="text-gray-600">
                    Разрабатываем и интегрируем искусственный интеллект и машинное
                    обучение в ваши существующие системы и процессы
                </p>
            </div>
            <!-- Feature 2 -->
            <div class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-settings-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">
                    Дорабатываем и автоматизируем процессы
                </h3>
                <p class="text-gray-600">
                    Оптимизируем рабочие процессы с помощью автоматизации, сокращая
                    время выполнения задач и минимизируя ошибки
                </p>
            </div>
            <!-- Feature 3 -->
            <div class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-team-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Усиливаем команду специалистами</h3>
                <p class="text-gray-600">
                    Предоставляем квалифицированных разработчиков и инженеров данных
                    для усиления вашей технической команды
                </p>
            </div>
            <!-- Feature 4 -->
            <div class="feature-card bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-line-chart-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Реальные кейсы с измеримым эффектом</h3>
                <p class="text-gray-600">
                    Фокусируемся на достижении конкретных бизнес-результатов с
                    измеримыми показателями эффективности
                </p>
            </div>
        </div>
    </div>
</section>

<?
$APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "cases",
        [
                "COMPONENT_TEMPLATE" => "cases",
                "IBLOCK_TYPE" => "company",
                "IBLOCK_ID" => tacticum_iblock_id('cases'),
                "NEWS_COUNT" => "3",
                "SORT_BY1" => "RAND",
                "SORT_ORDER1" => "ASC",
                "SORT_BY2" => "ID",
                "SORT_ORDER2" => "DESC",
                "FILTER_NAME" => "",
                "FIELD_CODE" => [
                        0 => "ID",
                        1 => "CODE",
                        2 => "NAME",
                        3 => "SORT",
                        4 => "PREVIEW_TEXT",
                        5 => "PREVIEW_PICTURE",
                        6 => "DETAIL_TEXT",
                        7 => "IBLOCK_TYPE_ID",
                        8 => "IBLOCK_ID",
                        9 => "",
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
                "PARENT_SECTION" => "",
                "PARENT_SECTION_CODE" => "",
                "INCLUDE_SUBSECTIONS" => "N",
                "STRICT_SECTION_CHECK" => "N",
                "PAGER_TEMPLATE" => ".default",
                "DISPLAY_TOP_PAGER" => "N",
                "DISPLAY_BOTTOM_PAGER" => "Y",
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

<!-- Testimonials Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <?
        $APPLICATION->IncludeComponent(
                "bitrix:news.list",
                "feedback",
                [
                        "COMPONENT_TEMPLATE" => "feedback",
                        "IBLOCK_TYPE" => "company",
                        "IBLOCK_ID" => tacticum_iblock_id('feedback'),
                        "NEWS_COUNT" => "3",
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
                                0 => "NAME",
                                1 => "COMPANY",
                                2 => "POSITION",
                                3 => "RATING",
                                4 => "",
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
                        "INCLUDE_IBLOCK_INТО_CHAIN" => "N",
                        "ADD_SECTIONS_CHAIN" => "N",
                        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                        "PARENT_SECTION" => "",
                        "PARENT_SECTION_CODE" => "",
                        "INCLUDE_SUBSECTIONS" => "N",
                        "STRICT_SECTION_CHECK" => "N",
                        "PAGER_TEMPLATE" => ".default",
                        "DISPLAY_TOP_PAGER" => "N",
                        "DISPLAY_BOTTOM_PAGER" => "Y",
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
        <?
        /* Клиенты, если понадобится
        $APPLICATION->IncludeComponent("bitrix:news.list","clients",[ ... ],false);
        */
        ?>
    </div>
</section>

<div id="calculator-root">
    <!-- Lead Magnet Section -->
    <section id="calculator" class="py-20 bg-gradient-to-r from-secondary to-primary text-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">
                        Не уверены, сколько ресурсов потребуется для вашей идеи?
                    </h2>
                    <p class="text-lg mb-8 text-blue-100">
                        Попробуйте наш бесплатный AI-калькулятор, который поможет оценить
                        сроки, бюджет и необходимые ресурсы для реализации вашего
                        AI-проекта.
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

<?
$APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "faq",
        [
                "COMPONENT_TEMPLATE" => "faq",
                "IBLOCK_TYPE" => "company",
                "IBLOCK_ID" => tacticum_iblock_id('faq'),
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
                "PARENT_SECTION" => "17",
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

<?php
$tacticumPersonalOfferCta = [
    "form_id" => "home-cta",
    "form_html_id" => "cta-form",
];
include $_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/include/personal-offer-cta.php";
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
