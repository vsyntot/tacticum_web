<?php
$GLOBALS['TACTICUM_PAGE_ASSETS'] = ['faq'];
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("AI-калькулятор - Тактикум");
$APPLICATION->SetPageProperty("description", "AI-калькулятор Tacticum помогает предварительно оценить сроки, бюджет и команду для AI-проекта.");
tacticum_apply_seo_defaults('/calculator/');
?>

<!-- AI Calculator Section -->
<section class="py-32 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">ИИ-калькулятор для оценки проекта</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Быстро оценим, сколько специалистов и времени потребуется под вашу задачу
            </p>
        </div>

        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="w-full lg:w-1/2">
                <div class="ai-chat-container shadow-lg" data-tacticum-chat="light" data-chat-surface="calculator">
                    <!-- Chat Header -->
                    <div class="bg-white p-4 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <div class="text-gray-500 text-sm">AI-калькулятор Tacticum</div>
                        </div>
                    </div>

                    <!-- Chat Body -->
                    <div class="p-6 space-y-6" data-chat-messages>
                        <!-- Welcome Message -->
                        <div class="bg-primary/10 rounded-lg p-4">
                            <p class="text-gray-700">
                                Здравствуйте! Я ИИ-ассистент Tacticum. Опишите вашу задачу, и я помогу оценить
                                необходимые ресурсы, состав команды и примерный бюджет.
                            </p>
                        </div>

                        <!-- Примерные сообщения/индикатор скрыты в верстке -->
                        <?/* <div class="bg-gray-100 rounded-lg p-4 ml-auto max-w-[80%]">...</div> */?>
                        <?/* <div class="ai-typing bg-primary/10 rounded-lg p-4 inline-block">...</div> */?>
                        <?/* <div class="ai-message bg-primary/10 rounded-lg p-4">...</div> */?>
                    </div>

                    <!-- Chat Input -->
                    <div class="bg-white p-4 border-t border-gray-200">
                        <div class="flex items-center gap-2">
                            <input type="text" placeholder="Опишите вашу задачу..."
                                   data-chat-input
                                   class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/50">
                            <button
                                    type="button"
                                    data-chat-send
                                    aria-label="Отправить сообщение"
                                    class="bg-primary w-10 h-10 rounded-full flex items-center justify-center text-white">
                                <i class="ri-send-plane-fill"></i>
                            </button>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button"
                                    data-chat-quick-reply
                                    data-message="Чат-бот"
                                    class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-200 transition-colors">
                                Чат-бот
                            </button>
                            <button type="button"
                                    data-chat-quick-reply
                                    data-message="Анализ данных"
                                    class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-200 transition-colors">
                                Анализ данных
                            </button>
                            <button type="button"
                                    data-chat-quick-reply
                                    data-message="Интеграция ИИ-агентов"
                                    class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-200 transition-colors">
                                Интеграция ИИ-агентов
                            </button>
                            <button type="button"
                                    data-chat-quick-reply
                                    data-message="Мобильное приложение"
                                    class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-200 transition-colors">
                                Мобильное приложение
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/2">
                <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
                    <h3 class="text-2xl font-bold text-secondary mb-6">Почему стоит доверять нашей оценке</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Reason 1 -->
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <i class="ri-user-star-line text-2xl text-primary"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-secondary mb-2">Экспертиза в AI-проектах</h4>
                                <p class="text-gray-600">
                                    Наша команда реализовала более 120+ проектов с использованием искусственного
                                    интеллекта и машинного обучения.
                                </p>
                            </div>
                        </div>
                        <!-- Reason 2 -->
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <i class="ri-eye-line text-2xl text-primary"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-secondary mb-2">Прозрачность оценки</h4>
                                <p class="text-gray-600">
                                    Мы детально объясняем, из чего складывается стоимость проекта и какие
                                    специалисты вам потребуются.
                                </p>
                            </div>
                        </div>
                        <!-- Reason 3 -->
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <i class="ri-gift-line text-2xl text-primary"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-secondary mb-2">Бесплатная консультация</h4>
                                <p class="text-gray-600">
                                    Первая консультация с нашими экспертами абсолютно бесплатна, без скрытых условий
                                    и обязательств.
                                </p>
                            </div>
                        </div>
                        <!-- Reason 4 -->
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <i class="ri-shield-check-line text-2xl text-primary"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-secondary mb-2">Гарантия результата</h4>
                                <p class="text-gray-600">
                                    Мы работаем до достижения поставленных целей и гарантируем качество результата.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-3xl font-bold text-primary">98%</p>
                                <p class="text-gray-600">довольных клиентов</p>
                            </div>
                            <div>
                                <p class="text-3xl font-bold text-primary">120+</p>
                                <p class="text-gray-600">реализованных проектов</p>
                            </div>
                            <div>
                                <p class="text-3xl font-bold text-primary">15+</p>
                                <p class="text-gray-600">лет опыта в IT</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>

<?php
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
                "FIELD_CODE" => ["ID","CODE","NAME","SORT","DETAIL_TEXT","IBLOCK_TYPE_ID","IBLOCK_ID",""],
                "PROPERTY_CODE" => ["",""],
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
                "HIDE_LINK_WHЕН_NO_DETAIL" => "N",
                "PARENT_SECTION" => "19",
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
    "form_id" => "calculator-cta",
];
include $_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/include/personal-offer-cta.php";
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
