<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq,chat");
$APPLICATION->SetTitle("Тактикум - экосистема корпоративных AI-продуктов");
$APPLICATION->SetPageProperty("description", "Tacticum развивает корпоративную AI-экосистему: Platform, Agents, Dev и Forum, а также помогает оценить, внедрить и запустить AI-решения в бизнес-процессах.");
tacticum_apply_seo_defaults('/', [
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<section class="hero-bg pt-24">
    <div class="container mx-auto px-4 py-16">
        <div class="flex flex-col md:flex-row items-center gap-12">
            <div class="w-full md:w-1/2 text-white">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Tacticum: платформа и продукты для корпоративного AI
                </h1>
                <p class="text-lg md:text-xl mb-8 text-blue-100">
                    Соединяем Platform, Agents, Dev и Forum с внедрением, оценкой проекта и командой. Можно начать
                    с продуктового пилота, архитектурной сессии, расчета бюджета или подбора delivery-команды.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="/platform/" class="bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap text-center">Смотреть Platform</a>
                    <a href="/offer/" class="bg-white/10 backdrop-blur-sm text-white border border-white/30 px-8 py-3 rounded-button hover:bg-white/20 transition-colors whitespace-nowrap text-center">Рассчитать проект</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-8">
                    <a href="/platform/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors">
                        <span class="block text-sm text-blue-100">Ядро экосистемы</span>
                        <span class="block font-semibold">Tacticum Platform</span>
                    </a>
                    <a href="/agents/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors">
                        <span class="block text-sm text-blue-100">Бизнес-функции</span>
                        <span class="block font-semibold">Tacticum Agents</span>
                    </a>
                    <a href="/dev/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors">
                        <span class="block text-sm text-blue-100">Инженерные команды</span>
                        <span class="block font-semibold">Tacticum Dev</span>
                    </a>
                    <a href="/forum/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors">
                        <span class="block text-sm text-blue-100">Клиентские диалоги</span>
                        <span class="block font-semibold">Tacticum Forum</span>
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
                    "INITIAL_USER_MESSAGE" => "Как понять, какой продукт Tacticum подходит для нашей задачи?",
                    "INTRO" => "Обычно выбор начинается с того, где находится задача:",
                    "INTRO_ITEMS" => [
                        "Platform - если нужен единый AI-контур, RAG, инструменты, доступы и аудит",
                        "Agents - если нужны ассистенты для HR, юридического, поддержки или базы знаний",
                        "Dev - если нужно управлять AI-assisted разработкой в инженерной команде",
                        "Forum - если важны управляемые клиентские диалоги и сценарии с LLM",
                    ],
                    "INTRO_OUTRO" => "Если продуктовый вход пока не очевиден, можно начать с расчета, discovery или короткой архитектурной сессии.",
                ],
                false
            );
            ?>
        </div>
    </div>
</section>

<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="mb-12 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Экосистема</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Общее AI-ядро и прикладные продукты поверх него
            </h2>
            <p class="text-lg text-gray-600">
                Продуктовая модель Tacticum строится вокруг одной архитектуры: Platform отвечает за runtime,
                модели, знания, инструменты и контроль, а Agents, Dev и Forum решают прикладные задачи разных команд.
            </p>
        </div>
        <div class="space-y-6">
            <a href="/platform/" class="block rounded-xl border border-primary/20 bg-white p-6 hover:border-primary transition-colors">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Platform core</p>
                        <h3 class="mb-3 text-2xl font-bold text-secondary">Tacticum Platform</h3>
                        <p class="text-gray-600">
                            Единый слой для LLM Gateway, RAG, памяти, MCP-инструментов, RBAC, аудита,
                            observability и контроля стоимости в корпоративном контуре.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 lg:max-w-md lg:justify-end">
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">LLM Gateway</span>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">RAG / Memory</span>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">MCP Runtime</span>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">RBAC / Audit</span>
                    </div>
                </div>
            </a>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="/agents/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="ri-robot-2-line text-2xl"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-secondary">Tacticum Agents</h3>
                    <p class="text-gray-600">
                        Корпоративные ассистенты для HR, юридического, бухгалтерии, поддержки, IT helpdesk и базы знаний.
                    </p>
                </a>
                <a href="/dev/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="ri-git-branch-line text-2xl"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-secondary">Tacticum Dev</h3>
                    <p class="text-gray-600">
                        Governance-слой для AI-assisted разработки: профили, знания, design tokens и quality gates.
                    </p>
                </a>
                <a href="/forum/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="ri-flow-chart text-2xl"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-secondary">Tacticum Forum</h3>
                    <p class="text-gray-600">
                        Управляемые клиентские диалоги: сценарные графы, LLM-обогащение, аналитика и журналирование.
                    </p>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="mb-12 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Как выбрать продукт</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Начните с ситуации, а не с названия продукта
            </h2>
            <p class="text-lg text-gray-600">
                Если продуктовый вход пока не очевиден, используйте короткую матрицу. Она разделяет платформенные,
                функциональные, инженерные и клиентские сценарии без обещаний результата до discovery.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-primary hover:shadow-md transition-all">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Platform</p>
                <h3 class="mb-3 text-xl font-bold text-secondary">Единый AI-контур</h3>
                <p class="mb-4 text-gray-600">
                    Выбирайте, если AI-сценариев несколько и нужны общие RAG, модели, инструменты, доступы, audit и контроль стоимости.
                </p>
                <p class="text-sm font-medium text-secondary">Старт: architecture assessment</p>
            </a>
            <a href="/agents/" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-primary hover:shadow-md transition-all">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Agents</p>
                <h3 class="mb-3 text-xl font-bold text-secondary">Ассистенты для функций</h3>
                <p class="mb-4 text-gray-600">
                    Подходит для HR, legal, finance, support, IT helpdesk и базы знаний, где есть документы, правила и handoff к человеку.
                </p>
                <p class="text-sm font-medium text-secondary">Старт: выбор 1-2 сценариев</p>
            </a>
            <a href="/dev/" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-primary hover:shadow-md transition-all">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-git-branch-line text-2xl"></i>
                </div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Dev</p>
                <h3 class="mb-3 text-xl font-bold text-secondary">AI-assisted workflow</h3>
                <p class="mb-4 text-gray-600">
                    Смотрите Dev, если команда уже использует AI-инструменты и нужно удержать architecture, review, tests и design tokens.
                </p>
                <p class="text-sm font-medium text-secondary">Старт: пилот на одной команде</p>
            </a>
            <a href="/forum/" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-primary hover:shadow-md transition-all">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-flow-chart text-2xl"></i>
                </div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Forum</p>
                <h3 class="mb-3 text-xl font-bold text-secondary">Клиентские диалоги</h3>
                <p class="mb-4 text-gray-600">
                    Выбирайте Forum для каналов поддержки и продаж, где нужны сценарии, LLM-уточнения, эскалации и журнал диалогов.
                </p>
                <p class="text-sm font-medium text-secondary">Старт: разбор потока обращений</p>
            </a>
        </div>
    </div>
</section>

<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Выберите следующий коммерческий шаг</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Продуктовая модель не заменяет текущие входы. Можно начать с оценки, внедрения, команды или
                быстрого AI-бота, если так проще проверить гипотезу.
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
        "TITLE" => "Подберем правильный вход в экосистему Tacticum",
        "TEXT" => "Опишите бизнес-задачу, желаемый срок и текущие ограничения. Мы свяжемся, уточним вводные и предложим следующий шаг: продуктовый пилот, discovery, расчет или подбор команды.",
        "MESSAGE_LABEL" => "Кратко опишите задачу",
        "MESSAGE_PLACEHOLDER" => "Например: хотим запустить AI-ассистента, нужен RAG по документам или нужно упорядочить AI-assisted разработку",
        "BUTTON_TEXT" => "Получить следующий шаг",
        "LEAD_CONTEXT" => [
            "lead_entry" => "home",
            "lead_page_role" => "ecosystem-router",
            "lead_intent" => "choose-product-or-commercial-entry",
            "lead_product" => "ecosystem",
            "lead_scenario" => "product-routing",
            "lead_cta" => "home-cta",
            "lead_next_step" => "product-discovery-or-project-estimate",
        ],
    ],
    false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
