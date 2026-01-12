<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Создание AI-бота в Telegram - Тактикум");
?>

<!-- Hero секция -->
<section class="hero-section relative">
    <div class="hero-overlay w-full min-h-[600px] flex items-center">
        <div class="container mx-auto px-4 md:px-6 py-20">
            <div class="max-w-2xl">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    Создай собственного AI-бота-продавца за 60 секунд и начни продавать уже сегодня!
                </h1>
                <p class="text-xl text-gray-700 mb-8">
                    Расскажи о своём бизнесе — и получи готового бота в Telegram, который будет вести диалог и продавать твои услуги
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#demoagents" target="_blank" class="bg-primary text-white py-3 px-8 !rounded-button flex items-center justify-center hover:bg-primary/90 transition-colors whitespace-nowrap">
                        <i class="ri-telegram-line mr-2"></i>
                        Попробовать готового Telegram-бота
                    </a>
                    <!-- Просто прокрутка к блоку #demo (без модалки) -->
                    <a href="#demo" class="bg-primary text-white py-3 px-8 !rounded-button flex items-center justify-center hover:bg-primary/90 transition-colors whitespace-nowrap">
                        <i class="ri-shopping-cart-line mr-2"></i>Создать своего Telegram-бота
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Как это работает -->
<section id="how-it-works" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6">
        <h2 class="text-3xl font-bold text-center mb-16">Как это работает?</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <div class="hidden md:block step-connector"></div>

            <!-- Шаг 1 -->
            <div class="bg-white p-8 rounded-lg shadow-sm relative">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6 mx-auto">
                    <i class="ri-chat-3-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-center mb-4">Опиши, чем ты занимаешься</h3>
                <p class="text-gray-600 text-center mb-6">Бот спросит о твоих услугах, компании, целевой аудитории</p>
                <div class="flex items-center justify-center">
                    <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3">
                        <a href="#demo"><i class="ri-add-line"></i></a>
                    </div>
                    <span class="text-sm text-gray-600">Простые вопросы — быстрые ответы</span>
                </div>
            </div>

            <!-- Шаг 2 -->
            <div class="bg-white p-8 rounded-lg shadow-sm relative">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6 mx-auto">
                    <i class="ri-robot-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-center mb-4">Получай бота за минуту</h3>
                <p class="text-gray-600 text-center mb-6">Персональный AI-ассистент обрабатывает твои ответы и готовит диалог</p>
                <div class="flex items-center justify-center">
                    <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3">
                        <a href="#demo"><i class="ri-add-line"></i></a>
                    </div>
                    <span class="text-sm text-gray-600">Мгновенная настройка под ваш бизнес</span>
                </div>
            </div>

            <!-- Шаг 3 -->
            <div class="bg-white p-8 rounded-lg shadow-sm relative">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6 mx-auto">
                    <i class="ri-test-tube-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-center mb-4">Проведи демо-тест</h3>
                <p class="text-gray-600 text-center mb-6">Пообщайся с ботом прямо в Telegram, посмотри, как он ведёт разговор и предлагает твои услуги</p>
                <div class="flex items-center justify-center">
                    <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3">
                        <a href="#demo"><i class="ri-add-line"></i></a>
                    </div>
                    <span class="text-sm text-gray-600">Сразу готов к работе с клиентами</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "aiagents",
        [
                "COMPONENT_TEMPLATE" => "aiagents",
                "IBLOCK_TYPE" => "services",
                "IBLOCK_ID" => "20",
                "NEWS_COUNT" => "3",
                "SORT_BY1" => "RAND",
                "SORT_ORDER1" => "ASC",
                "SORT_BY2" => "ID",
                "SORT_ORDER2" => "DESC",
                "FILTER_NAME" => "",
                "FIELD_CODE" => ["ID","CODE","NAME","SORT","PREVIEW_TEXT","PREVIEW_PICTURE","IBLOCK_TYPE_ID","IBLOCK_ID"],
                "PROPERTY_CODE" => ["LINK"],
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

<!-- Наши услуги -->
<section id="services" class="py-20">
    <div class="container mx-auto px-4 md:px-6">
        <h2 class="text-3xl font-bold text-center mb-16">Чем мы поможем?</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Услуга 1 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-attachment-2 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Подготовка сценариев общения</h3>
                <p class="text-gray-600">Разработаем индивидуальные сценарии диалогов, адаптированные под специфику вашего бизнеса и целевую аудиторию</p>
            </div>

            <!-- Услуга 2 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-tools-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Настройка AI-логики</h3>
                <p class="text-gray-600">Внедрим NLP-модели, настроим интеграцию с вашей CRM-системой и базой клиентов для максимальной эффективности</p>
            </div>

            <!-- Услуга 3 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Персонализация образа бота</h3>
                <p class="text-gray-600">Настроим тон, характер и манеру общения бота так, чтобы он идеально соответствовал вашему бренду</p>
            </div>

            <!-- Услуга 4 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-flask-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Запуск и тестирование</h3>
                <p class="text-gray-600">Проведем тщательное тестирование бота перед запуском, чтобы он был идеально отлажен до первого общения с клиентами</p>
            </div>

            <!-- Услуга 5 -->
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-bar-chart-2-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Поддержка и аналитика</h3>
                <p class="text-gray-600">Предоставим детальные метрики, проведем анализ эффективности и будем постоянно дорабатывать бота</p>
            </div>

            <!-- Дополнительный блок -->
            <div class="bg-primary/5 p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <i class="ri-lightbulb-flash-line text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-4">Индивидуальный подход</h3>
                <p class="text-gray-600">Каждый бот создается с учетом особенностей вашего бизнеса, продуктов и целевой аудитории</p>
                <a href="#contact" class="inline-block mt-6 text-primary font-medium hover:underline">Обсудить ваш проект →</a>
            </div>
        </div>
    </div>
</section>

<!-- Демо блок -->
<section id="demo" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6">
        <h2 class="text-3xl font-bold text-center mb-6">Попробуй уже сейчас — перейди в Telegram и создай своего бота</h2>
        <p class="text-xl text-gray-600 text-center mb-12 max-w-3xl mx-auto">Убедись сам, насколько просто создать и настроить своего AI-бота продавца</p>

        <div class="flex flex-col md:flex-row items-center justify-center gap-12">
            <div class="w-full md:w-1/2 max-w-md">
                <div class="bg-gray-100 p-4 rounded-2xl shadow-sm">
                    <div class="bg-white rounded-xl p-4 shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white mr-3">
                                <i class="ri-robot-line"></i>
                            </div>
                            <div>
                                <div class="font-semibold">AI-Бот Продавец</div>
                                <div class="text-xs text-gray-500">Онлайн</div>
                            </div>
                        </div>

                        <div class="space-y-4 mb-4">
                            <div class="bg-gray-100 p-3 rounded-lg rounded-tl-none max-w-[80%]">
                                <p class="text-sm">Привет! Я AI-бот, который поможет вам создать собственного бота-продавца. Расскажите, чем занимается ваш бизнес?</p>
                            </div>

                            <div class="bg-primary/10 p-3 rounded-lg rounded-tr-none max-w-[80%] ml-auto">
                                <p class="text-sm">Мы занимаемся разработкой мобильных приложений для малого бизнеса</p>
                            </div>

                            <div class="bg-gray-100 p-3 rounded-lg rounded-tl-none max-w-[80%]">
                                <p class="text-sm">Отлично! Какие услуги вы предлагаете клиентам? Можете перечислить основные?</p>
                            </div>

                            <div class="bg-primary/10 p-3 rounded-lg rounded-tr-none max-w-[80%] ml-auto">
                                <p class="text-sm">Разработка приложений под iOS и Android, дизайн интерфейсов, интеграция с CRM и платежными системами</p>
                            </div>

                            <div class="bg-gray-100 p-3 rounded-lg rounded-tl-none max-w-[80%]">
                                <p class="text-sm">Спасибо! На основе ваших ответов я создаю бота-продавца. Он будет готов через несколько секунд...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-1/2 max-w-md flex flex-col items-center">
                <h3 class="text-2xl font-semibold mb-6">Ваш бот будет:</h3>
                <ul class="space-y-4 mb-8 w-full">
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>Рассказывать о ваших услугах разработки приложений</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>Отвечать на вопросы о процессе и стоимости разработки</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>Собирать контакты заинтересованных клиентов</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>Назначать консультации с вашими специалистами</span>
                    </li>
                </ul>

                <a href="https://t.me/tacticum_father_bot" target="_blank" class="bg-primary text-white py-3 px-8 !rounded-button flex items-center justify-center hover:bg-primary/90 transition-colors whitespace-nowrap">
                    <i class="ri-telegram-line mr-2"></i>
                    Создать своего агента в Telegram
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Форма обратной связи (inline) -->
<section id="contact" class="py-20">
    <div class="container mx-auto px-4 md:px-6">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm p-8 md:p-12">
            <h2 class="text-3xl font-bold text-center mb-8">Не готов пробовать? Напиши нам — подготовим бот-прототип!</h2>

            <form id="contactFormInline">
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
                    <label for="projectInline" class="block text-sm font-medium text-gray-700 mb-1">Кратко о проекте</label>
                    <textarea id="projectInline" name="project" rows="4" class="w-full px-4 py-3 border border-gray-300 !rounded-button"></textarea>
                </div>

                <div class="flex items-start gap-2 mb-8">
                    <input type="checkbox" id="agreementInline" class="mt-1">
                    <label for="agreementInline" class="text-sm text-gray-600">
                        Я согласен на обработку персональных данных и принимаю условия
                        <a href="/policies/" class="underline">политики конфиденциальности</a>
                    </label>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-3 !rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
                    Отправить заявку
                </button>
            </form>
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
                "PARENT_SECTION" => "18",
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

<!-- JS: отправка inline-формы через tacticum_offer.php -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Общая функция отправки
        async function sendOffer({name, company, email, phone, task}) {
            const group_id = window.tacticum_offer_context?.groupId || '';
            const page_url = window.location.href;

            const resp = await fetch('/local/rest/tacticum_offer.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ name, company, email, phone, task, group_id, page_url })
            });
            return resp.json();
        }

        // Валидация и хэндлер для встроенной формы
        const inlineForm = document.getElementById('contactFormInline');
        if (inlineForm) {
            inlineForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const name = document.getElementById('nameInline').value.trim();
                const company = document.getElementById('companyInline').value.trim();
                const phone = document.getElementById('phoneInline').value.trim();
                const email = document.getElementById('emailInline').value.trim();
                const task = document.getElementById('projectInline').value.trim();
                const agreement = document.getElementById('agreementInline').checked;

                if (!name || !email || !phone || !agreement) {
                    alert('Пожалуйста, заполните обязательные поля (Имя, Email, Телефон) и отметьте согласие на обработку данных.');
                    return;
                }

                try {
                    const res = await sendOffer({ name, company, email, phone, task });
                    if (res.success) {
                        alert('Ваша заявка отправлена! Менеджер свяжется с вами.');
                        inlineForm.reset();
                    } else {
                        alert('Ошибка отправки: ' + (res.error || 'Неизвестная ошибка'));
                    }
                } catch (err) {
                    alert('Ошибка соединения: ' + err.message);
                }
            });
        }
    });
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>