<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php
if (function_exists('tacticum_page_content_render_if_live')) {
    tacticum_page_content_render_if_live('/about/', 'values-team');
}

// Fallback body retired after owner-approved page-content fallback retirement.
// Retired page-content fallback: tacticum_page_content_render_if_live('/about/', 'values-team').
?>

<section id="team-section" class="tacticum-anchor-target py-20">
    <span id="team" class="tacticum-anchor-alias" aria-hidden="true"></span>
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-6">Кто отвечает за запуск</h2>
            <p class="text-lg text-gray-600">
                Показываем людей, которые отвечают за продуктовую и инженерную сторону запуска. Состав рабочей
                команды подбираем отдельно: под сценарий, данные, интеграции, контроль качества и сопровождение.
            </p>
        </div>

        <?php
        $APPLICATION->IncludeComponent(
            "tacticum:content.list",
            "",
            [
                "NEWS_LIST_TEMPLATE" => "team",
                "IBLOCK_KEY" => "team",
                "IBLOCK_TYPE" => "company",
                "NEWS_COUNT" => "3",
                "SORT_BY1" => "SORT",
                "SORT_ORDER1" => "ASC",
                "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
                "PROPERTY_CODE" => ["PHOTO", "POSITION", "EMAIL", "LINKEDIN"],
                "DISPLAY_BOTTOM_PAGER" => "N",
            ],
            false
        );
        ?>

        <div class="tacticum-launch-roles" aria-label="Типовой состав команды запуска">
            <div class="tacticum-launch-roles__intro">
                <p class="tacticum-launch-roles__eyebrow">Состав под задачу</p>
                <h3 class="tacticum-launch-roles__title">Не все роли нужны сразу</h3>
                <p class="tacticum-launch-roles__text">
                    На старте фиксируем ответственных со стороны клиента и Tacticum, затем подключаем только те роли,
                    которые нужны для проверки сценария и безопасного первого запуска.
                </p>
            </div>
            <div class="tacticum-launch-roles__list" role="list">
                <div class="tacticum-launch-role" role="listitem">
                    <span class="tacticum-launch-role__label">Аналитика</span>
                    <span class="tacticum-launch-role__text">сценарий, пользователи, критерии результата</span>
                </div>
                <div class="tacticum-launch-role" role="listitem">
                    <span class="tacticum-launch-role__label">Инженерия</span>
                    <span class="tacticum-launch-role__text">серверная разработка, поиск по знаниям, интеграции</span>
                </div>
                <div class="tacticum-launch-role" role="listitem">
                    <span class="tacticum-launch-role__label">Качество</span>
                    <span class="tacticum-launch-role__text">тестирование, контроль ответов, безопасность запуска</span>
                </div>
                <div class="tacticum-launch-role" role="listitem">
                    <span class="tacticum-launch-role__label">Сопровождение</span>
                    <span class="tacticum-launch-role__text">управление запуском, инфраструктура, поддержка</span>
                </div>
            </div>
        </div>
    </div>
</section>
