<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq,chat");
$APPLICATION->SetTitle("AI-калькулятор проекта: бюджет, сроки, команда и риски - Тактикум");
$APPLICATION->SetPageProperty("description", "AI-калькулятор Tacticum помогает получить предварительный артефакт оценки проекта: бюджетный диапазон, сроки, состав команды, риски и следующий шаг.");
tacticum_apply_seo_defaults('/calculator/', [
    'image' => SITE_TEMPLATE_PATH . '/images/calculator_hero_bg.jpg',
    'image_width' => 1536,
    'image_height' => 592,
    'schema' => [
        '@type' => 'WebApplication',
        '@id' => tacticum_public_url('/calculator/#ai-calculator'),
        'name' => 'AI-калькулятор Tacticum',
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Web',
        'url' => tacticum_public_url('/calculator/'),
        'provider' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
    ],
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<section class="py-32 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-secondary mb-4">AI-калькулятор для предварительной оценки проекта</h1>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Опишите задачу и получите черновой артефакт: бюджетный диапазон, сроки, состав команды,
                ключевые риски и понятный следующий шаг к точной смете.
            </p>
        </div>

        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="w-full lg:w-1/2">
                <?php
                $APPLICATION->IncludeComponent(
                    "tacticum:chat.surface",
                    "",
                    [
                        "VARIANT" => "light",
                        "SURFACE" => "calculator",
                        "ROOT_CLASS" => "ai-chat-container shadow-lg",
                        "TITLE" => "AI-калькулятор Tacticum",
                        "INTRO" => "Здравствуйте! Расскажите о задаче, отрасли, текущих системах и сроке. Я подготовлю предварительную структуру оценки, а команда Tacticum уточнит ее по требованиям.",
                        "PLACEHOLDER" => "Опишите вашу задачу...",
                        "QUICK_REPLIES" => [
                            "AI-бот для продаж",
                            "OCR документов",
                            "Прогноз спроса",
                            "Интеграция с CRM",
                        ],
                    ],
                    false
                );
                ?>
            </div>

            <div class="w-full lg:w-1/2">
                <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
                    <h3 class="text-2xl font-bold text-secondary mb-6">Что вы получите после диалога</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <i class="ri-money-dollar-circle-line text-2xl text-primary"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-secondary mb-2">Бюджетный диапазон</h4>
                                <p class="text-gray-600">
                                    Предварительная вилка бюджета с пояснением, какие блоки влияют на стоимость.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <i class="ri-calendar-check-line text-2xl text-primary"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-secondary mb-2">Сроки и этапы</h4>
                                <p class="text-gray-600">
                                    Черновой план: discovery, MVP, интеграции, тестирование и запуск.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <i class="ri-team-line text-2xl text-primary"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-secondary mb-2">Состав команды</h4>
                                <p class="text-gray-600">
                                    Роли, которые обычно нужны для такого проекта: аналитик, backend, ML, QA, PM и другие.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <i class="ri-alert-line text-2xl text-primary"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-secondary mb-2">Риски и вопросы</h4>
                                <p class="text-gray-600">
                                    Что нужно уточнить перед точной сметой: данные, интеграции, SLA, безопасность и нагрузка.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h4 class="text-lg font-semibold text-secondary mb-4">Пример формата результата</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-sm text-gray-500 mb-1">Команда</p>
                                <p class="font-semibold text-secondary">5-6 ролей</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-sm text-gray-500 mb-1">Срок</p>
                                <p class="font-semibold text-secondary">8-12 недель</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-sm text-gray-500 mb-1">Следующий шаг</p>
                                <p class="font-semibold text-secondary">уточнить scope</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-4">
                            Это не финальная смета: точность зависит от требований, данных, интеграций и ограничений
                            вашей инфраструктуры.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => tacticum_iblock_id('faq'),
        "SECTION_KEY" => "calculator",
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
        "FORM_ID" => "calculator-cta",
        "TITLE" => "Уточнить предварительную оценку",
        "TEXT" => "Пришлите вводные из калькулятора или краткое описание задачи. Мы проверим гипотезы, зададим уточняющие вопросы и подготовим следующий шаг к точной смете.",
        "MESSAGE_LABEL" => "Вводные для уточнения оценки",
        "MESSAGE_PLACEHOLDER" => "Например: нужен AI-модуль для обработки заявок, CRM уже есть, данные за 2 года",
        "BUTTON_TEXT" => "Уточнить оценку",
        "LEAD_CONTEXT" => [
            "lead_entry" => "calculator",
            "lead_page_role" => "estimate-entry",
            "lead_intent" => "clarify-budget-timeline-team",
            "lead_cta" => "calculator-cta",
            "lead_next_step" => "estimate-review",
        ],
    ],
    false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
