<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-6">Ценности и подход</h2>
            <p class="text-lg text-gray-600">
                Наши ценности определяют то, как мы работаем и взаимодействуем с клиентами. Мы не просто
                консультируем — мы становимся частью вашей команды и вместе достигаем результатов.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="value-card bg-white rounded-xl p-8 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-lightbulb-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Инновационность</h3>
                <p class="text-gray-600">
                    Мы постоянно исследуем новые технологии и подходы, чтобы предлагать нашим клиентам самые
                    современные и эффективные решения. Инновации — это не просто слово, это наш образ мышления.
                </p>
            </div>
            <div class="value-card bg-white rounded-xl p-8 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-eye-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Прозрачность</h3>
                <p class="text-gray-600">
                    Мы верим в открытую коммуникацию и честность во всех аспектах работы. Наши клиенты всегда знают,
                    на каком этапе находится проект, какие результаты достигнуты и какие шаги планируются дальше.
                </p>
            </div>
            <div class="value-card bg-white rounded-xl p-8 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-shape-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Гибкость</h3>
                <p class="text-gray-600">
                    Мы адаптируемся к потребностям и особенностям каждого клиента. Наш подход не шаблонный — мы
                    разрабатываем индивидуальные решения, которые наилучшим образом соответствуют вашим целям и
                    задачам.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-8 md:p-12">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2">
                    <h3 class="text-2xl font-bold text-secondary mb-4">От консалтинга до результата</h3>
                    <p class="text-gray-600 mb-6">
                        Мы не только советуем, но и внедряем. Наша команда сопровождает проект на всех этапах — от
                        анализа потребностей и разработки концепции до внедрения решения и оценки результатов.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                                <i class="ri-check-line text-primary"></i>
                            </div>
                            <span class="text-gray-600">Глубокий анализ бизнес-процессов и потребностей</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                                <i class="ri-check-line text-primary"></i>
                            </div>
                            <span class="text-gray-600">Разработка индивидуальных решений под ваши задачи</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                                <i class="ri-check-line text-primary"></i>
                            </div>
                            <span class="text-gray-600">Полное сопровождение на этапе внедрения</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                                <i class="ri-check-line text-primary"></i>
                            </div>
                            <span class="text-gray-600">Обучение вашей команды работе с новыми технологиями</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                                <i class="ri-check-line text-primary"></i>
                            </div>
                            <span class="text-gray-600">Постоянная поддержка и развитие решения</span>
                        </li>
                    </ul>
                </div>
                <div class="w-full md:w-1/2">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/about.jpg" width="768" height="512" alt="От консалтинга до результата" loading="lazy" decoding="async" class="w-full h-auto rounded-xl shadow-md">
                </div>
            </div>
        </div>
    </div>
</section>

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
