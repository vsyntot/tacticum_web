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
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-6">Наша команда</h2>
            <p class="text-lg text-gray-600">
                Познакомьтесь с командой, которая развивает продукты Tacticum и помогает компаниям запускать
                AI-решения в реальных процессах.
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
                "PROPERTY_CODE" => ["POSITION", "EMAIL", "LINKEDIN"],
                "DISPLAY_BOTTOM_PAGER" => "N",
            ],
            false
        );
        ?>

        <div id="partners" class="tacticum-anchor-target bg-gray-50 rounded-2xl p-8 md:p-12">
            <h3 class="text-2xl font-bold text-secondary mb-8 text-center">Технологические контуры</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl p-6 text-center">
                    <div class="w-14 h-14 flex items-center justify-center mx-auto mb-4 rounded-lg bg-primary/10 text-primary">
                        <i class="ri-brain-line text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-secondary mb-2">LLM и RAG</h4>
                    <p class="text-sm text-gray-600">Модели, поиск по знаниям, память и контроль источников</p>
                </div>
                <div class="bg-white rounded-xl p-6 text-center">
                    <div class="w-14 h-14 flex items-center justify-center mx-auto mb-4 rounded-lg bg-primary/10 text-primary">
                        <i class="ri-plug-line text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-secondary mb-2">Интеграции</h4>
                    <p class="text-sm text-gray-600">CRM, ERP, wiki, helpdesk, документы и внутренние API</p>
                </div>
                <div class="bg-white rounded-xl p-6 text-center">
                    <div class="w-14 h-14 flex items-center justify-center mx-auto mb-4 rounded-lg bg-primary/10 text-primary">
                        <i class="ri-shield-check-line text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-secondary mb-2">Контроль</h4>
                    <p class="text-sm text-gray-600">Роли, аудит, журналирование, quality gates и наблюдаемость</p>
                </div>
                <div class="bg-white rounded-xl p-6 text-center">
                    <div class="w-14 h-14 flex items-center justify-center mx-auto mb-4 rounded-lg bg-primary/10 text-primary">
                        <i class="ri-rocket-line text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-secondary mb-2">Запуск</h4>
                    <p class="text-sm text-gray-600">Пилот, production rollout, поддержка и развитие продукта</p>
                </div>
            </div>
        </div>
    </div>
</section>
