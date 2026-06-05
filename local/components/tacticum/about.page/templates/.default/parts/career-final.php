<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<section id="career-section" class="tacticum-anchor-target py-20">
    <span id="careers" class="tacticum-anchor-alias" aria-hidden="true"></span>
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-6">Карьера в Tacticum</h2>
            <p class="text-lg text-gray-600">
                Мы всегда ищем талантливых специалистов, которые разделяют наши ценности и стремятся создавать
                инновационные решения для бизнеса.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
            <div class="bg-white rounded-xl p-8 shadow-sm">
                <h3 class="text-xl font-bold text-secondary mb-6">Корпоративная культура</h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                            <i class="ri-team-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-700">Командная работа</h4>
                            <p class="text-gray-600">Мы ценим сотрудничество и взаимную поддержку, работая вместе для достижения общих целей.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                            <i class="ri-book-open-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-700">Постоянное обучение</h4>
                            <p class="text-gray-600">Мы поощряем профессиональное развитие и предоставляем возможности для обучения и роста.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                            <i class="ri-creative-commons-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-700">Инновационное мышление</h4>
                            <p class="text-gray-600">Мы поощряем креативность и смелость в поиске новых решений сложных задач.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                            <i class="ri-scales-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-700">Баланс работы и жизни</h4>
                            <p class="text-gray-600">Мы верим в важность здорового баланса между работой и личной жизнью.</p>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-xl p-8 shadow-sm">
                <h3 class="text-xl font-bold text-secondary mb-6">Преимущества работы у нас</h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                            <i class="ri-briefcase-4-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-700">Интересные проекты</h4>
                            <p class="text-gray-600">Работа над сложными и интересными задачами в различных отраслях бизнеса.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                            <i class="ri-arrow-up-circle-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-700">Карьерный рост</h4>
                            <p class="text-gray-600">Возможности для профессионального и карьерного развития внутри компании.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                            <i class="ri-health-book-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-700">Социальный пакет</h4>
                            <p class="text-gray-600">Конкурентная заработная плата, ДМС, корпоративные мероприятия и другие бонусы.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
                            <i class="ri-home-office-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-700">Гибкий формат работы</h4>
                            <p class="text-gray-600">Возможность работать удаленно или в комфортабельном офисе в центре города.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <?php
        $APPLICATION->IncludeComponent(
            "tacticum:content.list",
            "",
            [
                "NEWS_LIST_TEMPLATE" => "vacancies",
                "IBLOCK_KEY" => "vacancies",
                "IBLOCK_TYPE" => "company",
                "NEWS_COUNT" => "0",
                "SORT_BY1" => "SORT",
                "SORT_ORDER1" => "ASC",
                "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
                "PROPERTY_CODE" => ["TIME", "LOCATION", "TYPE"],
                "DISPLAY_BOTTOM_PAGER" => "N",
            ],
            false
        );
        ?>
    </div>
</section>

<section class="py-20 bg-gradient-to-r from-secondary to-primary text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Готовы начать сотрудничество?</h2>
            <p class="text-lg mb-8 text-blue-100">
                Свяжитесь с нами, чтобы обсудить, как искусственный интеллект и автоматизация могут помочь вашему
                бизнесу достичь новых высот.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a
                        class="inline-block bg-white text-primary px-8 py-3 rounded-button hover:bg-white/90 transition-colors whitespace-nowrap text-center"
                        href="/calculator/">
                    Познакомиться ближе
                </a>
                <a
                        class="inline-block bg-white/10 backdrop-blur-sm text-white border border-white/30 px-8 py-3 rounded-button hover:bg-white/20 transition-colors whitespace-nowrap text-center"
                        href="#contact-form">
                    Связаться с командой
                </a>
            </div>
        </div>
    </div>
</section>
