<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Тарифы - Тактикум");
?>

<!-- Hero Section -->
<section class="price-hero-bg pt-24">
    <div class="container mx-auto px-4 py-16">
        <div class="flex flex-col items-center text-center">
            <div class="text-white max-w-3xl">
                <div class="flex items-center justify-center gap-2 mb-4 text-sm">
                    <a href="/" data-readdy="true" class="text-blue-200 hover:text-white transition-colors">Главная</a>
                    <i class="ri-arrow-right-s-line text-blue-200"></i>
                    <span>Тарифы</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">Почасовые ставки IT-специалистов по модели T&M</h1>
                <p class="text-lg md:text-xl mb-8 text-blue-100 max-w-2xl mx-auto">
                    Мы предлагаем гибкое расширение вашей команды под задачи бизнеса — вы платите только за
                    отработанные часы специалистов нужного профиля. Получите доступ к профессионалам с релевантной
                    экспертизой без затрат на найм и долгий поиск.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
            <!-- Benefit 1 -->
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-time-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary mb-2">Экономия времени и бюджета</h3>
                <p class="text-gray-600">
                    Нет затрат на поиск, найм и удержание специалистов. Платите только за фактические часы работы.
                </p>
            </div>
            <!-- Benefit 2 -->
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-rocket-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary mb-2">Быстрый старт работы</h3>
                <p class="text-gray-600">
                    Подключаем специалистов к вашему проекту в течение 1-3 дней после согласования условий.
                </p>
            </div>
            <!-- Benefit 3 -->
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-price-tag-3-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary mb-2">Прозрачное ценообразование</h3>
                <p class="text-gray-600">
                    Фиксированные почасовые ставки без скрытых платежей. Понятная система учета рабочего времени.
                </p>
            </div>
            <!-- Benefit 4 -->
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-scales-3-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg фонt-bold text-secondary mb-2">Возможность быстро масштабировать команду</h3>
                <p class="text-gray-600">
                    Легко увеличивайте или уменьшайте количество специалистов в зависимости от текущих потребностей.
                </p>
            </div>
            <!-- Benefit 5 -->
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-file-paper-2-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary mb-2">Работаем по договору и с НДС</h3>
                <p class="text-gray-600">
                    Полностью легальное сотрудничество с заключением договора и предоставлением всех необходимых
                    документов.
                </p>
            </div>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "price",
        [
                "COMPONENT_TEMPLATE" => "price",
                "IBLOCK_TYPE" => "services",
                "IBLOCK_ID" => "11",
                "NEWS_COUNT" => "9999",
                "SORT_BY1" => "SORT",
                "SORT_ORDER1" => "ASC",
                "SORT_BY2" => "ID",
                "SORT_ORDER2" => "DESC",
                "FILTER_NAME" => "",
                "FIELD_CODE" => ["ID","CODE","NAME","SORT","IBLOCK_TYPE_ID","IBLOCK_ID"],
                "PROPERTY_CODE" => ["LEVEL","PRICE","OPTIONS","POPULAR"],
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
                "PARENT_SECTION" => "",
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

<!-- AI Calculator Section -->
<div id="calculator">
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <!-- LIVE чат, как на /calculator/ -->
                <div class="w-full md:w-1/2">
                    <div class="ai-chat-container shadow-lg">
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
                        <div class="p-6 space-y-6">
                            <!-- Welcome Message -->
                            <div class="bg-primary/10 rounded-lg p-4">
                                <p class="text-gray-700">
                                    Здравствуйте! Я ИИ-ассистент Tacticum. Опишите вашу задачу, и я помогу оценить
                                    необходимые ресурсы, состав команды и примерный бюджет.
                                </p>
                            </div>
                            <?/* примеры и typing — добавляются динамически */?>
                        </div>

                        <!-- Chat Input -->
                        <div class="bg-white p-4 border-t border-gray-200">
                            <div class="flex items-center gap-2">
                                <input type="text" placeholder="Опишите вашу задачу..."
                                       class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/50">
                                <button class="bg-primary w-10 h-10 rounded-full flex items-center justify-center text-white">
                                    <i class="ri-send-plane-fill"></i>
                                </button>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-200 transition-colors">Чат-бот</button>
                                <button class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-200 transition-colors">Анализ данных</button>
                                <button class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-200 transition-colors">Интеграция ИИ-агентов</button>
                                <button class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-200 transition-colors">Мобильное приложение</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Изображение справа -->
                <div class="w-full md:w-1/2">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/ai.jpg"
                         alt="AI-калькулятор" class="w-full h-auto rounded-xl shadow-lg object-cover object-top">
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
                "PARENT_SECTION" => "21",
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
                    <form id="pricing-cta-form" class="bg-white/10 backdrop-blur-sm rounded-xl p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="relative">
                                <input type="text" id="cta-name" placeholder=" "
                                       class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-name" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Имя</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="cta-company" placeholder=" "
                                       class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-company" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Компания</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="cta-email" placeholder=" "
                                       class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-email" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Email</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="cta-phone" placeholder=" "
                                       class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-phone" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Телефон</label>
                            </div>
                        </div>
                        <div class="relative mb-6">
                            <textarea id="cta-message" rows="4" placeholder=" "
                                      class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30"></textarea>
                            <label for="cta-message" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Опишите ваш
                                проект или интересующее предложение</label>
                        </div>
                        <div class="flex items-start gap-2 mb-6">
                            <input type="checkbox" id="cta-agreement"
                                   class="mt-1 appearance-none w-4 h-4 border border-white/30 rounded bg-white/5 checked:bg-primary checked:border-0 relative">
                            <label for="cta-agreement" class="text-sm text-white/70">
                                Я согласен на обработку персональных данных и принимаю условия
                                <a href="/policies/" class="underline hover:text-white">политики конфиденциальности</a>
                            </label>
                        </div>
                        <button type="submit"
                                class="w-full bg-white text-primary font-medium px-6 py-3 rounded-button hover:bg-white/90 transition-colors whitespace-nowrap">
                            Получить персональное предложение
                        </button>
                    </form>
                </div>
                <div class="w-full md:w-1/2">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/specialoffer.jpg"
                         alt="Персональное предложение"
                         class="w-full h-auto rounded-xl shadow-lg object-cover object-top">
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ===== ЛОКАЛЬНАЯ логика чата для блока на /price/ ===== -->
<script>
    // Блокируем глобальный футер-чат на этой странице
    window.TACTICUM_DISABLE_FOOTER_AI_CHAT = true;

    document.addEventListener("DOMContentLoaded", function () {
        const aiChatRoot = document.querySelector("#calculator .ai-chat-container");
        if (!aiChatRoot) return;

        let group_id = null;

        const chatInput       = aiChatRoot.querySelector("input[type='text']");
        const sendButton      = aiChatRoot.querySelector("button");
        const aiChatContainer = aiChatRoot.querySelector(".p-6");
        const quickReplies    = aiChatRoot.querySelectorAll(".mt-3 button");

        if (!chatInput || !sendButton || !aiChatContainer) return;

        function addUserMessage(message) {
            const el = document.createElement("div");
            el.className = "bg-gray-100 rounded-lg p-4 ml-auto max-w-[80%]";
            el.innerHTML = `<p class="text-gray-700"></p>`;
            el.querySelector("p").textContent = message;
            aiChatContainer.appendChild(el);
            aiChatContainer.scrollTop = aiChatContainer.scrollHeight;
        }

        function addAIMessage(text, bitrix_url = null) {
            const el = document.createElement("div");
            el.className = "bg-primary/10 rounded-lg p-4";
            el.innerHTML = `
                <p class="text-gray-700"></p>
                ${bitrix_url ? `<p class="mt-3"><a class="underline text-primary" href="${bitrix_url}" target="_blank" rel="noopener">Полный расчет</a></p>` : ""}
            `;
            el.querySelector("p").textContent = text;
            aiChatContainer.appendChild(el);
            aiChatContainer.scrollTop = aiChatContainer.scrollHeight;
        }

        function showTypingIndicator() {
            const el = document.createElement("div");
            el.className = "ai-typing bg-primary/10 rounded-lg p-4 inline-block";
            el.innerHTML = `
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            `;
            aiChatContainer.appendChild(el);
            aiChatContainer.scrollTop = aiChatContainer.scrollHeight;
            return el;
        }

        function sendMessage(message) {
            if (!message || !message.trim()) return;

            chatInput.value = "";
            sendButton.disabled = true;

            addUserMessage(message);
            const typing = showTypingIndicator();

            fetch('/local/rest/tacticum_chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ user_message: message, group_id })
            })
                .then(r => r.json())
                .then(res => {
                    typing.remove();
                    if (res && res.response) {
                        addAIMessage(res.response, res.bitrix_url || null);
                        if (res.group_id) group_id = res.group_id;
                    } else if (res && res.error) {
                        addAIMessage("Ошибка ответа от AI: " + res.error);
                    } else {
                        addAIMessage("Неожиданный ответ сервера.");
                    }
                })
                .catch(err => {
                    typing.remove();
                    addAIMessage("Ошибка запроса: " + err.message);
                })
                .finally(() => {
                    sendButton.disabled = false;
                });
        }

        // Кнопка отправки
        sendButton.addEventListener("click", function () {
            sendMessage(chatInput.value);
        });

        // Enter для отправки
        chatInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter") sendMessage(chatInput.value);
        });

        // Быстрые подсказки
        quickReplies.forEach(btn => {
            btn.addEventListener("click", function () {
                sendMessage(this.textContent.trim());
            });
        });
    });
</script>

<!-- JS: обработка формы на странице "Тарифы" (без изменений) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('pricing-cta-form');
        if (!form) return;

        const submitBtn = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitBtn.disabled = true;

            const name = document.getElementById('cta-name').value.trim();
            const company = document.getElementById('cta-company').value.trim();
            const email = document.getElementById('cta-email').value.trim();
            const phone = document.getElementById('cta-phone').value.trim();
            const task = document.getElementById('cta-message').value.trim();
            const agreement = document.getElementById('cta-agreement').checked;

            const group_id = window.tacticum_offer_context?.groupId || '';
            const page_url = window.location.href;

            if (!name || !email || !phone || !agreement) {
                alert('Пожалуйста, заполните обязательные поля и отметьте согласие на обработку данных.');
                submitBtn.disabled = false;
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
                        form.reset();
                    } else {
                        alert('Ошибка отправки: ' + (res.error || 'Неизвестная ошибка'));
                    }
                })
                .catch(err => alert('Ошибка соединения: ' + err.message))
                .finally(() => { submitBtn.disabled = false; });
        });
    });
</script>

<style>
    /* Индикатор печати — как на /calculator/ */
    .typing-indicator { display:inline-flex; gap:6px; vertical-align:middle; }
    .typing-indicator span {
        width:6px; height:6px; border-radius:50%; display:block; opacity:0.5;
        animation: ti-bounce 1s infinite ease-in-out;
    }
    .typing-indicator span:nth-child(2){ animation-delay:.15s; }
    .typing-indicator span:nth-child(3){ animation-delay:.3s; }
    @keyframes ti-bounce {
        0%, 80%, 100% { transform: translateY(0); opacity:.5; }
        40%           { transform: translateY(-6px); opacity:1;  }
    }
</style>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>