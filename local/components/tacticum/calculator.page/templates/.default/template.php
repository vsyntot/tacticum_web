<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
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
                            "Platform для RAG и доступа к данным",
                            "Agents для HR или поддержки",
                            "Dev workflow для инженерной команды",
                            "Forum для клиентских диалогов",
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
                                    Что нужно уточнить перед точной сметой: данные, интеграции, уровень поддержки, безопасность и нагрузка.
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

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Product-aware estimate</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Что можно оценить через AI-калькулятор
            </h2>
            <p class="text-lg text-gray-600">
                Калькулятор остается быстрым entry point для сметы, но теперь помогает привязать задачу к продуктовой
                модели Tacticum: платформенному ядру, ассистентам, инженерному workflow или клиентским диалогам.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Platform</h3>
                <p class="text-gray-600">
                    Оценка общего AI-контура: LLM Gateway, RAG, память, инструменты, доступы, аудит и эксплуатация.
                </p>
            </a>
            <a href="/agents/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Agents</h3>
                <p class="text-gray-600">
                    Проверка ассистента для HR, юридического, бухгалтерии, поддержки, IT helpdesk или базы знаний.
                </p>
            </a>
            <a href="/dev/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-git-branch-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Dev</h3>
                <p class="text-gray-600">
                    Оценка пилота AI-assisted workflow: профили, knowledge layer, rules, quality gates и метрики.
                </p>
            </a>
            <a href="/forum/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-flow-chart text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Forum</h3>
                <p class="text-gray-600">
                    Оценка потока обращений: сценарный граф, LLM-обогащение, аналитика, журнал и интеграции.
                </p>
            </a>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => $arResult['FAQ_IBLOCK_ID'],
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
            "lead_product" => "ecosystem",
            "lead_scenario" => "product-estimate",
            "lead_cta" => "calculator-cta",
            "lead_next_step" => "estimate-review",
        ],
    ],
    false
);
?>
