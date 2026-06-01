<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq,charts,chat");
$APPLICATION->SetTitle("Команда под AI- и IT-задачу: роли, ставки и быстрый старт - Тактикум");
$APPLICATION->SetPageProperty("description", "Соберите управляемую AI- или IT-команду под задачу: роли, уровни специалистов, ставки, пресеты команды и заявка на старт работ.");
tacticum_apply_seo_defaults('/price/', [
    'image' => SITE_TEMPLATE_PATH . '/images/price_hero_bg.jpg',
    'image_width' => 1536,
    'image_height' => 592,
    'schema' => [
        '@type' => 'Service',
        '@id' => tacticum_public_url('/price/#staff-service'),
        'name' => 'Подбор IT-специалистов и AI-команд',
        'serviceType' => 'IT staff augmentation and AI delivery teams',
        'provider' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
        'areaServed' => 'RU',
        'url' => tacticum_public_url('/price/'),
    ],
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<section class="price-hero-bg pt-24">
    <div class="container mx-auto px-4 py-16">
        <div class="flex flex-col items-center text-center">
            <div class="text-white max-w-3xl">
                <div class="flex items-center justify-center gap-2 mb-4 text-sm">
                    <a href="/" data-readdy="true" class="text-blue-200 hover:text-white transition-colors">Главная</a>
                    <i class="ri-arrow-right-s-line text-blue-200"></i>
                <span>Команда и ставки</span>
            </div>
                <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">Соберите AI- и IT-команду под вашу задачу</h1>
                <p class="text-lg md:text-xl mb-8 text-blue-100 max-w-2xl mx-auto">
                    Выберите роли, уровни и загрузку, чтобы быстро оценить состав работ. Ставки остаются прозрачным
                    ориентиром, а заявка помогает уточнить команду, ответственность и формат подключения.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <a href="#price-list" class="inline-flex items-center justify-center gap-2 rounded-button bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                        <i class="ri-team-line"></i>
                        Подобрать состав
                    </a>
                    <a href="/offer/" class="inline-flex items-center justify-center gap-2 rounded-button border border-white/30 bg-white/10 px-6 py-3 text-sm font-medium text-white hover:bg-white/20 transition-colors">
                        <i class="ri-file-search-line"></i>
                        Сравнить с расчетами
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-time-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary mb-2">Состав под задачу</h3>
                <p class="text-gray-600">
                    Подбираем роли под конкретный этап: discovery, MVP, интеграции, support или релизный рывок.
                </p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-rocket-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary mb-2">Быстрый старт работы</h3>
                <p class="text-gray-600">
                    После согласования scope и доступов подключаем специалистов короткими управляемыми итерациями.
                </p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-price-tag-3-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary mb-2">Прозрачные ставки</h3>
                <p class="text-gray-600">
                    Видите ставку, уровень и примерный месячный бюджет до того, как оставите заявку.
                </p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-scales-3-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary mb-2">Масштабирование команды</h3>
                <p class="text-gray-600">
                    Можно начать с узкого состава и расширять его по мере появления задач и данных.
                </p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                    <i class="ri-file-paper-2-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-lg font-bold text-secondary mb-2">Договор и понятная зона ответственности</h3>
                <p class="text-gray-600">
                    Фиксируем формат работы, коммуникации, отчетность и ожидаемый результат этапа.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Product workstreams</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Команда под продуктовый пилот или delivery-этап
            </h2>
            <p class="text-lg text-gray-600">
                Страница команды не становится страницей лицензий на продукты. Это по-прежнему способ оценить роли,
                загрузку и старт команды для внедрения Platform, Agents, Dev, Forum или отдельной AI-интеграции.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Platform team</h3>
                <p class="text-gray-600">
                    Архитектор, backend, data/RAG, integration, QA и DevOps для платформенного assessment или пилота.
                </p>
            </a>
            <a href="/agents/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Agents pilot</h3>
                <p class="text-gray-600">
                    Аналитик, prompt/RAG, backend, integration и QA для запуска ассистента в одном подразделении.
                </p>
            </a>
            <a href="/dev/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-code-box-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Dev workflow</h3>
                <p class="text-gray-600">
                    Engineering lead, архитектор, design system owner, QA и DevOps для пилота AI-assisted процесса.
                </p>
            </a>
            <a href="/forum/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-customer-service-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Forum launch</h3>
                <p class="text-gray-600">
                    CX-аналитик, сценарист, backend, integration, QA и PM для первого потока обращений.
                </p>
            </a>
        </div>
    </div>
</section>

<div id="price-list">
<?php
$APPLICATION->IncludeComponent(
    "tacticum:content.list",
    "",
    [
        "NEWS_LIST_TEMPLATE" => "price",
        "IBLOCK_KEY" => "rates",
        "IBLOCK_TYPE" => "services",
        "NEWS_COUNT" => "9999",
        "SORT_BY1" => "SORT",
        "SORT_ORDER1" => "ASC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "PROPERTY_CODE" => ["LEVEL", "PRICE", "OPTIONS", "POPULAR"],
        "DISPLAY_BOTTOM_PAGER" => "N",
    ],
    false
);
?>
</div>

<div id="calculator">
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <?php
                    $APPLICATION->IncludeComponent(
                        "tacticum:chat.surface",
                        "",
                        [
                            "VARIANT" => "light",
                            "SURFACE" => "price",
                            "ROOT_CLASS" => "ai-chat-container shadow-lg",
                            "TITLE" => "AI-калькулятор Tacticum",
                            "INTRO" => "Здравствуйте! Опишите задачу, текущий этап и ограничения. Я помогу наметить состав команды, роли и ориентир бюджета, а точный план уточнит специалист Tacticum.",
                            "PLACEHOLDER" => "Опишите вашу задачу...",
                            "QUICK_REPLIES" => [
                                "Platform assessment",
                                "Agents pilot",
                                "Dev workflow",
                                "Forum launch",
                            ],
                        ],
                        false
                    );
                    ?>
                </div>

                <div class="w-full md:w-1/2">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/ai.jpg"
                         width="608" height="512" alt="AI-калькулятор" loading="lazy" decoding="async" class="w-full h-auto rounded-xl shadow-lg object-cover object-top">
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
        "SECTION_KEY" => "price",
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
        "FORM_ID" => "price-cta",
        "FORM_HTML_ID" => "pricing-cta-form",
        "TITLE" => "Нужен состав команды под вашу задачу?",
        "TEXT" => "Опишите, какой результат нужен, текущий этап и желаемый срок. Мы предложим роли, уровень специалистов, формат загрузки и следующий шаг по подключению.",
        "MESSAGE_LABEL" => "Опишите задачу для команды",
        "MESSAGE_PLACEHOLDER" => "Например: нужен MVP личного кабинета с AI-подсказками, старт в июне, команда на 2-3 месяца",
        "BUTTON_TEXT" => "Подобрать команду",
        "LEAD_CONTEXT" => [
            "lead_entry" => "price",
            "lead_page_role" => "team-entry",
            "lead_intent" => "build-managed-team",
            "lead_product" => "ecosystem",
            "lead_scenario" => "product-team",
            "lead_cta" => "price-cta",
            "lead_next_step" => "team-scope-and-staffing",
        ],
    ],
    false
);
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
