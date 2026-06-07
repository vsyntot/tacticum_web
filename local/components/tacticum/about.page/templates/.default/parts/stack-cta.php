<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div id="technology" class="tacticum-anchor-target">
    <span id="stack" class="tacticum-anchor-alias" aria-hidden="true"></span>
    <section class="tacticum-readiness-section py-16">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Что проверяем перед запуском</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Это не список библиотек. Перед пилотом мы собираем карту готовности: сценарий, данные,
                    интеграции, риски, экономику и ответственность после первого запуска.
                </p>
            </div>
            <div class="tacticum-readiness-grid">
                <article class="tacticum-readiness-card">
                    <div class="tacticum-readiness-card__icon">
                        <i class="ri-compass-3-line" aria-hidden="true"></i>
                    </div>
                    <h3 class="tacticum-readiness-card__title">Сценарий и эффект</h3>
                    <p class="tacticum-readiness-card__text">
                        Проверяем цель, пользователя, границы пилота, критерий результата и экономику первого шага.
                    </p>
                    <p class="tacticum-readiness-card__result">
                        Итог: понятный сценарий и критерии, по которым можно принять решение о пилоте.
                    </p>
                </article>
                <article class="tacticum-readiness-card">
                    <div class="tacticum-readiness-card__icon">
                        <i class="ri-database-2-line" aria-hidden="true"></i>
                    </div>
                    <h3 class="tacticum-readiness-card__title">Данные и знания</h3>
                    <p class="tacticum-readiness-card__text">
                        Смотрим источники, качество, доступы, правила использования, проверку источников и ограничения ответов AI.
                    </p>
                    <p class="tacticum-readiness-card__result">
                        Итог: список источников, ограничений и вопросов, которые нельзя игнорировать на запуске.
                    </p>
                </article>
                <article class="tacticum-readiness-card">
                    <div class="tacticum-readiness-card__icon">
                        <i class="ri-plug-line" aria-hidden="true"></i>
                    </div>
                    <h3 class="tacticum-readiness-card__title">Интеграции и контур</h3>
                    <p class="tacticum-readiness-card__text">
                        Уточняем рабочие системы, документы, внутренние API, среду запуска и границы ответственности.
                    </p>
                    <p class="tacticum-readiness-card__result">
                        Итог: предварительная схема контура и список интеграций для оценки работ.
                    </p>
                </article>
                <article class="tacticum-readiness-card">
                    <div class="tacticum-readiness-card__icon">
                        <i class="ri-shield-check-line" aria-hidden="true"></i>
                    </div>
                    <h3 class="tacticum-readiness-card__title">Риски и эксплуатация</h3>
                    <p class="tacticum-readiness-card__text">
                        Фиксируем роли, аудит, журналирование, контроль качества, поддержку, обновления и стоимость запросов.
                    </p>
                    <p class="tacticum-readiness-card__result">
                        Итог: карта рисков, состав команды и безопасный следующий шаг.
                    </p>
                </article>
            </div>
        </div>
    </section>
</div>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:lead.cta",
    "",
    [
        "TYPE" => "project-discussion",
        "FORM_ID" => "about-cta",
        "FORM_HTML_ID" => "about-cta-form",
        "FIELD_PREFIX" => "about-cta",
        "TITLE" => "Сверим задачу и первый безопасный шаг",
        "TEXT" => "Опишите бизнес-сценарий, ограничения и желаемый результат. Мы подскажем, что разумнее делать сначала: оценку, пилот, интеграцию или команду под запуск.",
        "FORM_TITLE" => "Оставить заявку",
        "BUTTON_TEXT" => "Обсудить задачу",
        "LEAD_CONTEXT" => [
            "lead_entry" => "about",
            "lead_page_role" => "trust-entry",
            "lead_intent" => "discuss-company-fit",
            "lead_product" => "ecosystem",
            "lead_cta" => "about-cta",
            "lead_next_step" => "qualification-call",
        ],
    ],
    false
);
?>
