<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

global $APPLICATION;
?>

<section class="hero-section relative">
    <div class="hero-overlay w-full min-h-[600px] flex items-center">
        <div class="container mx-auto px-4 md:px-6 py-20">
            <div class="max-w-2xl">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    Проверьте AI-бота для продаж и консультаций в Telegram
                </h1>
                <p class="text-xl text-gray-700 mb-8">
                    Быстро соберите прототип диалога, протестируйте демо-агентов и решите, нужен ли вам отдельный
                    бот, интеграция с CRM или полноценное AI-внедрение.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#demoagents" class="bg-primary text-white py-3 px-8 !rounded-button flex items-center justify-center hover:bg-primary/90 transition-colors whitespace-nowrap">
                        <i class="ri-telegram-line mr-2"></i>
                        Посмотреть демо-агентов
                    </a>
                    <a href="#demo" class="bg-primary text-white py-3 px-8 !rounded-button flex items-center justify-center hover:bg-primary/90 transition-colors whitespace-nowrap">
                        <i class="ri-flask-line mr-2"></i>Запросить прототип
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6">
        <h2 class="text-3xl font-bold text-center mb-16">Как бот переходит из демо в рабочий сценарий</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <div class="hidden md:block step-connector"></div>

            <div class="bg-white p-8 rounded-lg shadow-sm relative">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6 mx-auto">
                    <i class="ri-chat-3-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-center mb-4">Опишите бизнес-сценарий</h3>
                <p class="text-gray-600 text-center mb-6">Какие продукты продаете, кто клиент, какие вопросы бот должен закрывать</p>
                <div class="flex items-center justify-center">
                    <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3">
                        <a href="#demo"><i class="ri-add-line"></i></a>
                    </div>
                    <span class="text-sm text-gray-600">Минимум вводных для первого прототипа</span>
                </div>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-sm relative">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6 mx-auto">
                    <i class="ri-robot-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-center mb-4">Проверьте диалог</h3>
                <p class="text-gray-600 text-center mb-6">AI-ассистент собирает черновой сценарий и показывает, как будет отвечать клиентам</p>
                <div class="flex items-center justify-center">
                    <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3">
                        <a href="#demo"><i class="ri-add-line"></i></a>
                    </div>
                    <span class="text-sm text-gray-600">Без интеграций и долгой подготовки</span>
                </div>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-sm relative">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6 mx-auto">
                    <i class="ri-test-tube-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-center mb-4">Решите, что внедрять</h3>
                <p class="text-gray-600 text-center mb-6">После демо можно запросить прототип, CRM-интеграцию или полноценный проект внедрения</p>
                <div class="flex items-center justify-center">
                    <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3">
                        <a href="#demo"><i class="ri-add-line"></i></a>
                    </div>
                    <span class="text-sm text-gray-600">Следующий шаг зависит от вашей воронки</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:content.list",
    "",
    [
        "NEWS_LIST_TEMPLATE" => "aiagents",
        "IBLOCK_ID" => (string)$arResult['AIAGENTS_IBLOCK_ID'],
        "IBLOCK_TYPE" => "services",
        "NEWS_COUNT" => "3",
        "SORT_BY1" => "RAND",
        "SORT_ORDER1" => "ASC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "PREVIEW_PICTURE", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "PROPERTY_CODE" => ["LINK"],
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ],
    $component
);
?>

<section id="services" class="py-20">
    <div class="container mx-auto px-4 md:px-6">
        <h2 class="text-3xl font-bold text-center mb-16">Где AI-бот становится частью B2B-процесса</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Услуга 1 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-attachment-2 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Сценарии квалификации</h3>
                <p class="text-gray-600">Опишем вопросы, развилки и критерии передачи лида менеджеру</p>
            </div>

            <!-- Услуга 2 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-tools-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">AI-логика и знания</h3>
                <p class="text-gray-600">Настроим ответы на основе ваших услуг, документов, FAQ и ограничений бренда</p>
            </div>

            <!-- Услуга 3 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Тон и правила общения</h3>
                <p class="text-gray-600">Согласуем стиль, допустимые обещания, стоп-темы и передачу сложных вопросов человеку</p>
            </div>

            <!-- Услуга 4 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-flask-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Тестирование и запуск</h3>
                <p class="text-gray-600">Проверим диалоги, лид-формы, Telegram-сценарии и корректность передачи данных</p>
            </div>

            <!-- Услуга 5 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-bar-chart-2-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Аналитика и развитие</h3>
                <p class="text-gray-600">Смотрим, где диалог теряет клиента, и дорабатываем сценарии после запуска</p>
            </div>

            <!-- Дополнительный блок -->
            <div class="bg-primary/5 p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-lightbulb-flash-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Связь с основным проектом</h3>
                <p class="text-gray-600">Если бот требует CRM, биллинга, базы знаний или аналитики, подключаем команду внедрения Tacticum</p>
                <a href="/services/" class="inline-block mt-6 text-primary font-medium hover:underline">Посмотреть внедрение →</a>
            </div>
        </div>
    </div>
</section>

<section id="demo" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6">
        <h2 class="text-3xl font-bold text-center mb-6">Проверьте прототип в Telegram</h2>
        <p class="text-xl text-gray-600 text-center mb-12 max-w-3xl mx-auto">Демо поможет увидеть механику диалога до того, как вы будете планировать интеграции и разработку</p>

        <div class="flex flex-col md:flex-row items-center justify-center gap-12">
            <div class="w-full md:w-1/2 max-w-md">
                <div class="bg-gray-100 p-4 rounded-2xl shadow-sm">
                    <div class="bg-white rounded-xl p-4 shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white mr-3">
                                <i class="ri-robot-line"></i>
                            </div>
                            <div>
                                <div class="font-semibold">AI-бот для квалификации</div>
                                <div class="text-xs text-gray-500">Онлайн</div>
                            </div>
                        </div>

                        <div class="space-y-4 mb-4">
                            <div class="bg-gray-100 p-3 rounded-lg rounded-tl-none max-w-[80%]">
                                <p class="text-sm">Здравствуйте! Я помогу проверить сценарий AI-бота. Чем занимается ваша компания и какой лид нужно квалифицировать?</p>
                            </div>

                            <div class="bg-primary/10 p-3 rounded-lg rounded-tr-none max-w-[80%] ml-auto">
                                <p class="text-sm">Мы продаем B2B-сервис для логистики и хотим быстрее обрабатывать входящие заявки</p>
                            </div>

                            <div class="bg-gray-100 p-3 rounded-lg rounded-tl-none max-w-[80%]">
                                <p class="text-sm">Какие критерии важны для квалификации: размер компании, регион, срок запуска, бюджет или текущая система?</p>
                            </div>

                            <div class="bg-primary/10 p-3 rounded-lg rounded-tr-none max-w-[80%] ml-auto">
                                <p class="text-sm">Нужны регион, объем заявок, текущая CRM и срок внедрения</p>
                            </div>

                            <div class="bg-gray-100 p-3 rounded-lg rounded-tl-none max-w-[80%]">
                                <p class="text-sm">Понял. Я соберу черновой сценарий, а команда Tacticum поможет уточнить интеграции и передачу лида менеджеру.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-1/2 max-w-md flex flex-col items-center">
                <h3 class="text-2xl font-semibold mb-6">Что можно проверить в демо:</h3>
                <ul class="space-y-4 mb-8 w-full">
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>как бот объясняет ваши услуги и задает уточняющие вопросы</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>какие критерии квалификации нужны до передачи менеджеру</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>какие данные стоит собирать в Telegram-сценарии</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>какие интеграции понадобятся для production-запуска</span>
                    </li>
                </ul>

                <a href="https://t.me/tacticum_father_bot" target="_blank" rel="noopener" data-tacticum-tg-resolve class="bg-primary text-white py-3 px-8 !rounded-button flex items-center justify-center hover:bg-primary/90 transition-colors whitespace-nowrap">
                    <i class="ri-telegram-line mr-2"></i>
                    Открыть демо в Telegram
                </a>
            </div>
        </div>
    </div>
</section>

<section id="contact" class="py-20">
    <div class="container mx-auto px-4 md:px-6">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm p-8 md:p-12">
            <h2 class="text-3xl font-bold text-center mb-8">Нужен бот-прототип под ваш процесс?</h2>

            <form id="contactFormInline" data-tacticum-form data-form-id="aiagents-inline">
                <input type="hidden" name="lead_entry" value="aiagents">
                <input type="hidden" name="lead_page_role" value="telegram-bot-entry">
                <input type="hidden" name="lead_intent" value="request-ai-bot-prototype">
                <input type="hidden" name="lead_cta" value="aiagents-inline">
                <input type="hidden" name="lead_next_step" value="bot-prototype-review">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="nameInline" class="block text-sm font-medium text-gray-700 mb-1">Имя *</label>
                        <input type="text" id="nameInline" name="name" required class="w-full px-4 py-3 border border-gray-300 !rounded-button">
                    </div>
                    <div>
                        <label for="companyInline" class="block text-sm font-medium text-gray-700 mb-1">Компания</label>
                        <input type="text" id="companyInline" name="company" class="w-full px-4 py-3 border border-gray-300 !rounded-button">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="phoneInline" class="block text-sm font-medium text-gray-700 mb-1">Телефон *</label>
                        <input type="tel" id="phoneInline" name="phone" required class="w-full px-4 py-3 border border-gray-300 !rounded-button">
                    </div>
                    <div>
                        <label for="emailInline" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" id="emailInline" name="email" required class="w-full px-4 py-3 border border-gray-300 !rounded-button">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="projectInline" class="block text-sm font-medium text-gray-700 mb-1">Какой сценарий должен закрывать бот</label>
                    <textarea id="projectInline" name="message" required rows="4" class="w-full px-4 py-3 border border-gray-300 !rounded-button"></textarea>
                </div>

                <div class="flex items-start gap-2 mb-8">
                    <input type="checkbox" id="agreementInline" data-tacticum-consent required class="mt-1">
                    <label for="agreementInline" class="text-sm text-gray-600">
                        Я согласен на обработку персональных данных и принимаю условия
                        <a href="/policies/" target="_blank" rel="noopener" class="underline">политики конфиденциальности</a>
                    </label>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-3 !rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
                    Запросить бот-прототип
                </button>
            </form>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => $arResult['FAQ_IBLOCK_ID'],
        "SECTION_KEY" => $arResult['FAQ_SECTION_KEY'],
        "PARENT_SECTION" => $arResult['FAQ_PARENT_SECTION'],
        "SECTION_CLASS" => "py-16 bg-gray-50",
    ],
    $component
);
?>
