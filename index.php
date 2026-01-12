<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Тактикум - Искусственный интеллект для вашего бизнеса");
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
                    <button onclick="window.location.href='/#calculator';" class="bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap"><a href="#calculator">Оценить свою идею</a></button>
                    <button class="bg-white/10 backdrop-blur-sm text-white border border-white/30 px-8 py-3 rounded-button hover:bg-white/20 transition-colors whitespace-nowrap"><a href="#contact-form">Получить консультацию</a></button>
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
                "IBLOCK_ID" => "13",
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
                        "IBLOCK_ID" => "9",
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
                                    <div class="bg-primary h-full" style="width: 65%"></div>
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
                                    <div class="bg-primary h-full" style="width: 35%"></div>
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
                                    <div class="bg-primary h-full" style="width: 85%"></div>
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
                "IBLOCK_ID" => "10",
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

<!-- Contact Form Section -->
<div id="contact-form">
    <section class="py-16 bg-gradient-to-r from-secondary to-primary text-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">Получите персональное предложение</h2>
                    <p class="text-lg mb-8 text-blue-100">
                        Оставьте заявку, и наш менеджер свяжется с вами в течение 2 часов, чтобы обсудить детали и
                        подготовить индивидуальное предложение с учетом всех доступных скидок и акций.
                    </p>
                    <form id="cta-form" class="bg-white/10 backdrop-blur-sm rounded-xl p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="relative">
                                <input type="text" id="cta-name" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-name" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Имя</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="cta-company" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-company" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Компания</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="cta-email" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-email" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Email</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="cta-phone" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-phone" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Телефон</label>
                            </div>
                        </div>
                        <div class="relative mb-6">
                            <textarea id="cta-message" rows="4" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30"></textarea>
                            <label for="cta-message" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Опишите ваш проект или интересующее предложение</label>
                        </div>
                        <div class="flex items-start gap-2 mb-6">
                            <input type="checkbox" id="cta-agreement" class="mt-1 appearance-none w-4 h-4 border border-white/30 rounded bg-white/5 checked:bg-primary checked:border-0 relative">
                            <label for="cta-agreement" class="text-sm text-white/70">Я согласен на обработку персональных данных и принимаю условия <a href="#" class="underline hover:text-white">политики конфиденциальности</a></label>
                        </div>
                        <button type="submit" class="w-full bg-white text-primary font-medium px-6 py-3 rounded-button hover:bg-white/90 transition-colors whitespace-nowrap">Получить персональное предложение</button>
                    </form>
                </div>
                <div class="w-full md:w-1/2">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/specialoffer.jpg" alt="Персональное предложение" class="w-full h-auto rounded-xl shadow-lg object-cover object-top">
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ================== ЧАТЫ И CTA: JS ================== -->
<script id="tacticum-chats">
    document.addEventListener('DOMContentLoaded', function () {
        // =========================
        // HERO-чат (#main_chat)
        // =========================
        (function initHeroChat(){
            const root = document.getElementById('main_chat');
            if (!root) return;

            const chatArea  = root.querySelector('.space-y-4');
            const chatInput = root.querySelector('input');
            const sendBtn   = root.querySelector('#aichat');

            if (!chatArea || !chatInput || !sendBtn) return;

            let hero_group_id = null;

            function appendHero(role, htmlText) {
                const div = document.createElement('div');
                div.className = (role === 'user')
                    ? 'bg-white/10 rounded-lg p-3 text-white'
                    : 'bg-primary/20 rounded-lg p-3 text-white';
                div.innerHTML = `<p class="text-sm text-white/70 mb-1">${role === 'user' ? 'Пользователь:' : 'AI-ассистент:'}</p>
<p>${htmlText}</p>`;
                chatArea.insertBefore(div, chatArea.lastElementChild);
                chatArea.scrollTop = chatArea.scrollHeight;
            }

            function showTypingHero() {
                if (chatArea.querySelector('.typing-indicator-container')) return;
                const div = document.createElement('div');
                div.className = 'bg-primary/20 rounded-lg p-3 text-white typing-indicator-container';
                div.innerHTML = `<p class="text-sm text-white/70 mb-1">AI-ассистент:</p>
<div class="typing-indicator"><span></span><span></span><span></span></div>`;
                chatArea.insertBefore(div, chatArea.lastElementChild);
                chatArea.scrollTop = chatArea.scrollHeight;
            }
            function hideTypingHero() {
                const el = chatArea.querySelector('.typing-indicator-container');
                if (el) el.remove();
            }

            function sendHero() {
                const message = (chatInput.value || '').trim();
                if (!message) return;

                appendHero('user', message.replace(/\n/g, '<br>'));
                chatInput.value = '';
                sendBtn.disabled = true;
                showTypingHero();

                const body = { user_message: message };
                if (hero_group_id) body.group_id = hero_group_id; // НЕТ startAgent

                fetch('/local/rest/tacticum_chat.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(body)
                })
                    .then(r => r.json())
                    .then(res => {
                        hideTypingHero();
                        if (res.response) {
                            const tail = (res.bitrix_url)
                                ? `<br/><br/><a href="#contact-form" class="offer-link">Оформить заявку</a> <a href="${res.bitrix_url}" target="_blank">Полный расчет</a> <a href="/" target="_blank">Новый расчет</a>`
                                : '';
                            appendHero('ai', (res.response || '').replace(/\n/g, '<br>') + tail);
                            if (res.group_id) hero_group_id = res.group_id;
                        } else if (res.error) {
                            appendHero('ai', 'Ошибка ответа от AI: ' + res.error);
                        } else {
                            appendHero('ai', 'Неожиданный ответ сервера.');
                        }
                    })
                    .catch(err => {
                        hideTypingHero();
                        appendHero('ai', 'Ошибка запроса: ' + err.message);
                    })
                    .finally(() => { sendBtn.disabled = false; });
            }

            sendBtn.addEventListener('click', sendHero);
            chatInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') sendHero(); });

            // Автозаполнение формы из ответа (только клики внутри HERO-чата)
            chatArea.addEventListener('click', function(e) {
                if (!e.target.matches('.offer-link')) return;
                e.preventDefault();
                if (!hero_group_id) { alert('Не найден идентификатор обращения.'); return; }

                // очистка формы
                const nameEl = document.getElementById('cta-name');
                const msgEl  = document.getElementById('cta-message');
                if (nameEl) nameEl.value = '';
                if (msgEl)  msgEl.value  = '';

                fetch('/local/rest/tacticum_prefill.php?group_id=' + encodeURIComponent(hero_group_id))
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            if (nameEl) nameEl.value = res.client_name || '';
                            if (msgEl)  msgEl.value  = res.summary || '';
                            window.tacticum_offer_context = { groupId: res.group_id, task: res.summary };
                        } else {
                            alert(res.error || 'Ошибка автозаполнения формы');
                        }
                        const cf = document.getElementById('contact-form');
                        if (cf) cf.scrollIntoView({behavior: 'smooth'});
                        if (nameEl) nameEl.focus();
                    })
                    .catch(err => alert('Ошибка получения данных: ' + err.message));
            });
        })();

        // =========================
        // КАЛЬКУЛЯТОР (section#calculator)
        // =========================
        (function initCalculatorChat(){
            const calcSection = document.querySelector('section#calculator');
            if (!calcSection) return;

            const msgs     = calcSection.querySelector('#chatMessages');
            const inputEl  = calcSection.querySelector('#userMessage');
            const sendBtn  = calcSection.querySelector('#sendMessage');
            if (!msgs || !inputEl || !sendBtn) return;

            let calc_group_id = null;
            let calc_started  = false; // чтобы один раз отправить startAgent

            function appendCalc(role, text) {
                const div = document.createElement('div');
                if (role === 'user') {
                    div.className = 'bg-white/5 rounded-lg p-3 text-white';
                    div.innerHTML = `<p class="text-sm text-white/70 mb-1">Пользователь:</p><p>${text}</p>`;
                } else {
                    div.className = 'bg-white/10 rounded-lg p-3 text-white';
                    div.innerHTML = `<p class="text-sm text-white/70 mb-1">AI-ассистент:</p><p>${text}</p>`;
                }
                msgs.appendChild(div);
                msgs.scrollTop = msgs.scrollHeight;
            }
            function showTypingCalc() {
                if (msgs.querySelector('.typing-indicator-container')) return;
                const div = document.createElement('div');
                div.className = 'bg-white/10 rounded-lg p-3 text-white typing-indicator-container';
                div.innerHTML = `<p class="text-sm text-white/70 mb-1">AI-ассистент:</p>
<div class="typing-indicator"><span></span><span></span><span></span></div>`;
                msgs.appendChild(div);
                msgs.scrollTop = msgs.scrollHeight;
            }
            function hideTypingCalc() {
                const el = msgs.querySelector('.typing-indicator-container');
                if (el) el.remove();
            }

            function sendCalc() {
                const raw = (inputEl.value || '').trim();
                if (!raw) return;

                appendCalc('user', raw.replace(/\n/g, '<br>'));
                inputEl.value = '';
                sendBtn.disabled = true;
                showTypingCalc();

                const body = { user_message: raw };

                if (calc_group_id) {
                    body.group_id = calc_group_id;
                } else if (!calc_started) {
                    body.startAgent = 'ITExpertAgent'; // ключевое отличие калькулятора
                    calc_started = true;
                }

                fetch('/local/rest/tacticum_chat.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(body)
                })
                    .then(r => r.json())
                    .then(res => {
                        hideTypingCalc();
                        if (res.response) {
                            appendCalc('ai', (res.response || '').replace(/\n/g, '<br>'));
                            if (res.group_id) calc_group_id = res.group_id;
                        } else if (res.error) {
                            appendCalc('ai', 'Ошибка ответа от AI: ' + res.error);
                        } else {
                            appendCalc('ai', 'Неожиданный ответ сервера.');
                        }
                    })
                    .catch(err => {
                        hideTypingCalc();
                        appendCalc('ai', 'Ошибка запроса: ' + err.message);
                    })
                    .finally(() => { sendBtn.disabled = false; });
            }

            sendBtn.addEventListener('click', sendCalc);
            inputEl.addEventListener('keydown', e => { if (e.key === 'Enter') sendCalc(); });
        })();

        // =========================
        // CTA-форма
        // =========================
        (function initCtaForm(){
            const ctaForm = document.getElementById('cta-form');
            if (!ctaForm) return;
            const ctaSubmitBtn = ctaForm.querySelector('button[type="submit"]');
            ctaForm.addEventListener('submit', function(e) {
                e.preventDefault();
                ctaSubmitBtn.disabled = true;

                const name = document.getElementById('cta-name')?.value.trim() || '';
                const company = document.getElementById('cta-company')?.value.trim() || '';
                const email = document.getElementById('cta-email')?.value.trim() || '';
                const phone = document.getElementById('cta-phone')?.value.trim() || '';
                const task = document.getElementById('cta-message')?.value.trim() || '';
                const agreement = document.getElementById('cta-agreement')?.checked;
                const group_id = window.tacticum_offer_context?.groupId || '';
                const page_url = window.location.href;

                if (!name || !email || !phone || !agreement) {
                    alert('Пожалуйста, заполните обязательные поля и согласие!');
                    ctaSubmitBtn.disabled = false;
                    return;
                }

                fetch('/local/rest/tacticum_offer.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ name, company, email, phone, task, group_id, page_url })
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            alert('Ваша заявка отправлена! Менеджер свяжется с вами.');
                            ctaForm.reset();
                        } else {
                            alert('Ошибка отправки: ' + (res.error || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(err => alert('Ошибка соединения: ' + err.message))
                    .finally(() => ctaSubmitBtn.disabled = false);
            });
        })();
    });
</script>

<!-- Локальные стили для индикатора печати (если нет в общем CSS) -->
<style>
    .typing-indicator { display:inline-flex; gap:6px; vertical-align:middle; }
    .typing-indicator span { width:6px; height:6px; border-radius:50%; display:block; opacity:0.5; animation: ti-bounce 1s infinite ease-in-out; }
    .typing-indicator span:nth-child(2){ animation-delay:.15s; }
    .typing-indicator span:nth-child(3){ animation-delay:.3s; }
    @keyframes ti-bounce { 0%,80%,100%{ transform:translateY(0); opacity:.5; } 40%{ transform:translateY(-6px); opacity:1; } }
</style>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>