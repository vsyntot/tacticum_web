<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Услуги - Тактикум");
?>

<!-- Hero Section -->
<section class="services-hero-bg pt-24 min-h-[400px] flex items-center">
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-secondary">AI-решения для вашего бизнеса</h1>
            <p class="text-lg md:text-xl mb-8 text-gray-600">
                Мы предлагаем полный спектр услуг по внедрению искусственного интеллекта — от консалтинга и
                разработки до интеграции и поддержки. Наши решения помогают бизнесу автоматизировать процессы,
                оптимизировать расходы и увеличивать прибыль.
            </p>
            <!-- исправлен якорь -->
            <button onclick="window.location.href='/services/#contact-form';"
                    class="bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
                Запросить расчет
            </button>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "services",
        [
                "COMPONENT_TEMPLATE" => "services",
                "IBLOCK_TYPE" => "services",
                "IBLOCK_ID" => "12",
                "NEWS_COUNT" => "3",
                "SORT_BY1" => "SORT",
                "SORT_ORDER1" => "ASC",
                "SORT_BY2" => "ID",
                "SORT_ORDER2" => "DESC",
                "FILTER_NAME" => "",
                "FIELD_CODE" => ["ID","CODE","NAME","SORT","PREVIEW_TEXT","DETAIL_TEXT","IBLOCK_TYPE_ID","IBLOCK_ID"],
                "PROPERTY_CODE" => ["OPTIONS"],
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

<!-- How it works Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Как это работает</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Наш структурированный подход к внедрению AI-решений обеспечивает максимальную эффективность и
                прозрачность на всех этапах проекта
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Step 1 -->
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">1</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Анализ</h3>
                <p class="text-gray-600">Изучаем бизнес-процессы и определяем точки роста</p>
            </div>
            <!-- Step 2 -->
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">2</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Планирование</h3>
                <p class="text-gray-600">Разрабатываем стратегию и выбираем технологии</p>
            </div>
            <!-- Step 3 -->
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">3</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Разработка</h3>
                <p class="text-gray-600">Создаем и тестируем AI-решение</p>
            </div>
            <!-- Step 4 -->
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">4</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Внедрение</h3>
                <p class="text-gray-600">Интегрируем решение в бизнес-процессы</p>
            </div>
            <!-- Step 5 -->
            <div class="step-item text-center px-4">
                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">5</div>
                <h3 class="text-lg font-bold text-secondary mb-2">Поддержка</h3>
                <p class="text-gray-600">Обеспечиваем сопровождение и развитие</p>
            </div>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "cases",
        [
                "COMPONENT_TEMPLATE" => "cases",
                "IBLOCK_TYPE" => "company",
                "IBLOCK_ID" => "13",
                "NEWS_COUNT" => "3",
                "SORT_BY1" => "RAND",
                "SORT_ORDER1" => "DESC",
                "SORT_BY2" => "ID",
                "SORT_ORDER2" => "DESC",
                "FILTER_NAME" => "",
                "FIELD_CODE" => ["ID","CODE","NAME","SORT","PREVIEW_TEXT","PREVIEW_PICTURE","DETAIL_TEXT","IBLOCK_TYPE_ID","IBLOCK_ID"],
                "PROPERTY_CODE" => [],
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

<!-- Technologies Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Технологии, с которыми мы работаем</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Мы используем передовые технологии и инструменты для создания эффективных AI-решений
            </p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
            <!-- Tech cards... (без изменений) -->
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

<!-- Contact Form Section -->
<div id="contact-form">
    <section class="py-16 bg-gradient-to-r from-secondary to-primary text-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">Готовы обсудить ваш проект?</h2>
                    <p class="text-lg mb-8 text-blue-100">
                        Заполните форму, и наши специалисты свяжутся с вами в течение 24 часов для обсуждения деталей и
                        подготовки индивидуального предложения.
                    </p>
                    <div class="space-y-6 mb-8">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0"><i class="ri-medal-line text-2xl"></i></div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Опыт и экспертиза</h3>
                                <p class="text-blue-100">Более 50 успешно реализованных AI-проектов в различных отраслях</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0"><i class="ri-team-line text-2xl"></i></div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Команда профессионалов</h3>
                                <p class="text-blue-100">Сертифицированные специалисты с опытом работы в ведущих IT-компаниях</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0"><i class="ri-rocket-line text-2xl"></i></div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Быстрый старт</h3>
                                <p class="text-blue-100">Начинаем работу над проектом в течение 3-5 дней после согласования</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Форма заявки -->
                <div class="w-full md:w-1/2">
                    <form id="services-cta-form" class="bg-white/10 backdrop-blur-sm rounded-xl p-6" data-tacticum-form data-form-id="services-cta">
                        <h3 class="text-xl font-bold mb-6">Оставить заявку</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="relative">
                                <input type="text" id="services-name" name="name" required placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="services-name" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Имя</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="services-company" name="company" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="services-company" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Компания</label>
                            </div>
                            <div class="relative">
                                <input type="email" id="services-email" name="email" required placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="services-email" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Email</label>
                            </div>
                            <div class="relative">
                                <input type="tel" id="services-phone" name="phone" required placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="services-phone" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Телефон</label>
                            </div>
                        </div>
                        <div class="relative mb-6">
                            <textarea id="services-message" name="message" rows="4" required placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30"></textarea>
                            <label for="services-message" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Опишите ваш проект</label>
                        </div>
                        <div class="flex items-start gap-2 mb-6">
                            <input type="checkbox" id="services-agreement" data-tacticum-consent required class="mt-1 appearance-none w-4 h-4 border border-white/30 rounded bg-white/5 checked:bg-primary checked:border-0 relative">
                            <label for="services-agreement" class="text-sm text-white/70">
                                Я согласен на обработку персональных данных и принимаю условия
                                <a href="/policies/" class="underline hover:text-white">политики конфиденциальности</a>
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-white text-primary font-medium px-6 py-3 rounded-button hover:bg-white/90 transition-colors whitespace-nowrap">Запросить расчет</button>
                    </form>
                </div>
                <!-- /Форма заявки -->
            </div>
        </div>
    </section>
</div>

<?php
$APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "faq",
        [
                "COMPONENT_TEMPLATE" => "faq",
                "IBLOCK_TYPE" => "company",
                "IBLOCK_ID" => "10",
                "NEWS_COUNT" => "0",
                "SORT_BY1" => "SORT",
                "SORT_ORDER1" => "ASC",
                "SORT_BY2" => "ID",
                "SORT_ORDER2" => "DESC",
                "FILTER_NAME" => "",
                "FIELD_CODE" => ["ID","CODE","NAME","SORT","DETAIL_TEXT","IBLOCK_TYPE_ID","IBLOCK_ID"],
                "PROPERTY_CODE" => [],
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
                "PARENT_SECTION" => "20",
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

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
