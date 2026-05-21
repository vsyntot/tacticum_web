<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("О компании - Тактикум");
$APPLICATION->SetPageProperty("description", "О компании Tacticum: команда, подход и опыт разработки программного обеспечения, внедрения AI-решений и автоматизации бизнеса.");
tacticum_apply_seo_defaults('/about/');
?>

<!-- Hero Section -->
<section class="about-hero-bg min-h-[500px] pt-24 flex items-center">
    <div class="container mx-auto px-4 py-20">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight text-secondary">О компании Тактикум</h1>
            <p class="text-lg md:text-xl mb-8 text-gray-700">
                Мы разрабатываем программное обеспечение и помогаем компаниям быстро внедрять инновации с помощью
                ИИ и автоматизации, превращая технологические возможности в реальные бизнес-результаты.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Исправлен якорь -->
                <a href="/calculator/" class="inline-block bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap text-center">Познакомиться ближе</a>
                <!-- <button class="bg-white text-primary border border-primary px-8 py-3 rounded-button hover:bg-gray-50 transition-colors whitespace-nowrap">Связаться с командой</button> -->
            </div>
        </div>
    </div>
</section>

<!-- Who We Are Section -->
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-6">Кто мы?</h2>
            <div class="bg-primary/5 border-l-4 border-primary p-6 rounded-lg mb-8">
                <p class="text-xl italic text-gray-700">"Мы разрабатываем программные решения и помогаем компаниям быстро внедрять инновации с помощью ИИ и автоматизации"</p>
            </div>
            <p class="text-lg text-gray-600">
                Tacticum — это команда экспертов в области разработки программного обеспечения, искусственного
                интеллекта и автоматизации бизнес-процессов. Мы фокусируемся на глубокой экспертизе и индивидуальном
                подходе к каждому клиенту, что позволяет нам создавать решения, которые действительно работают и
                приносят измеримую пользу.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="stat-card bg-white rounded-xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                <div class="text-4xl font-bold text-primary mb-2">120+</div>
                <p class="text-gray-600">Реализованных проектов</p>
            </div>
            <div class="stat-card bg-white rounded-xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                <div class="text-4xl font-bold text-primary mb-2">18</div>
                <p class="text-gray-600">Отраслей бизнеса</p>
            </div>
            <div class="stat-card bg-white rounded-xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                <div class="text-4xl font-bold text-primary mb-2">85%</div>
                <p class="text-gray-600">Клиентов работают с нами более года</p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-2xl p-8 md:p-12">
            <h3 class="text-2xl font-bold text-secondary mb-8 text-center">История компании</h3>
            <div class="space-y-12">
                <div class="relative pl-14 timeline-item">
                    <div class="absolute left-0 top-0 w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <span class="text-primary font-bold">2019</span>
                    </div>
                    <h4 class="text-xl font-bold text-secondary mb-2">Предыстория команды</h4>
                    <p class="text-gray-600">
                        Будущая команда Tacticum начала работать над проектами в области искусственного интеллекта,
                        машинного обучения и разработки программных решений для автоматизации реальных бизнес-задач.
                    </p>
                </div>
                <div class="relative pl-14 timeline-item">
                    <div class="absolute left-0 top-0 w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <span class="text-primary font-bold">2020</span>
                    </div>
                    <h4 class="text-xl font-bold text-secondary mb-2">Первые крупные проекты</h4>
                    <p class="text-gray-600">
                        Реализация первых масштабных проектов в сфере логистики и ритейла. Формирование ключевых
                        методологий и подходов к внедрению AI-решений.
                    </p>
                </div>
                <div class="relative pl-14 timeline-item">
                    <div class="absolute left-0 top-0 w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <span class="text-primary font-bold">2022</span>
                    </div>
                    <h4 class="text-xl font-bold text-secondary mb-2">Регистрация ООО и расширение экспертизы</h4>
                    <p class="text-gray-600">
                        Зарегистрировано ООО «Тактикум» с основным ОКВЭД 62.01 «Разработка компьютерного программного
                        обеспечения». Команда расширила спектр услуг и начала работу с более сложными проектами.
                    </p>
                </div>
                <div class="relative pl-14 timeline-item">
                    <div class="absolute left-0 top-0 w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <span class="text-primary font-bold">2023</span>
                    </div>
                    <h4 class="text-xl font-bold text-secondary mb-2">Развитие собственных продуктов</h4>
                    <p class="text-gray-600">
                        Запуск линейки собственных AI-продуктов для автоматизации бизнес-процессов. Получение
                        статуса технологического партнера ведущих IT-компаний.
                    </p>
                </div>
                <div class="relative pl-14">
                    <div class="absolute left-0 top-0 w-12 h-12 bg-primary rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">2025</span>
                    </div>
                    <h4 class="text-xl font-bold text-secondary mb-2">Сегодня</h4>
                    <p class="text-gray-600">
                        Сегодня Tacticum — это команда из более чем 50 специалистов, реализовавших свыше 120
                        проектов в 18 отраслях бизнеса. Мы продолжаем расти и развиваться, помогая нашим клиентам
                        достигать новых высот с помощью искусственного интеллекта и автоматизации.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-3л md:text-4xl font-bold text-secondary mb-6">Ценности и подход</h2>
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
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/about.jpg" alt="От консалтинга до результата" class="w-full h-auto rounded-xl shadow-md">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section id="team-section" class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-6">Наша команда</h2>
            <p class="text-lg text-gray-600">
                Познакомьтесь с экспертами, которые делают Tacticum лидером в области AI-решений для бизнеса. Наша
                команда объединяет профессионалов с многолетним опытом в различных областях.
            </p>
        </div>

        <?php
        $APPLICATION->IncludeComponent(
                "bitrix:news.list",
                "team",
                [
                        "COMPONENT_TEMPLATE" => "team",
                        "IBLOCK_TYPE" => "company",
                        "IBLOCK_ID" => tacticum_iblock_id('team'),
                        "NEWS_COUNT" => "3",
                        "SORT_BY1" => "SORT",
                        "SORT_ORDER1" => "ASC",
                        "SORT_BY2" => "ID",
                        "SORT_ORDER2" => "DESC",
                        "FILTER_NAME" => "",
                        "FIELD_CODE" => ["ID","CODE","NAME","SORT","PREVIEW_TEXT","DETAIL_TEXT","IBLOCK_TYPE_ID","IBLOCK_ID"],
                        "PROPERTY_CODE" => ["POSITION","EMAIL","LINKEDIN"],
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

        <div id="partners" class="bg-gray-50 rounded-2xl p-8 md:p-12">
            <h3 class="text-2xl font-bold text-secondary mb-8 text-center">Партнерства и сертификации</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl p-6 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 flex items-center justify-center mb-4">
                        <i class="ri-microsoft-fill text-4xl text-gray-500"></i>
                    </div>
                    <h4 class="font-bold text-secondary">Microsoft</h4>
                    <p class="text-sm text-gray-500">AI Partner</p>
                </div>
                <div class="bg-white rounded-xl p-6 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 flex items-center justify-center mb-4">
                        <i class="ri-google-fill text-4xl text-gray-500"></i>
                    </div>
                    <h4 class="font-bold text-secondary">Google</h4>
                    <p class="text-sm text-gray-500">Cloud Partner</p>
                </div>
                <div class="bg-white rounded-xl p-6 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 flex items-center justify-center mb-4">
                        <i class="ri-amazon-fill text-4xl text-gray-500"></i>
                    </div>
                    <h4 class="font-bold text-secondary">AWS</h4>
                    <p class="text-sm text-gray-500">Advanced Partner</p>
                </div>
                <div class="bg-white rounded-xl p-6 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 flex items-center justify-center mb-4">
                        <i class="ri-openai-fill text-4xl text-gray-500"></i>
                    </div>
                    <h4 class="font-bold text-secondary">OpenAI</h4>
                    <p class="text-sm text-gray-500">Solution Partner</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tech Stack Section -->
<div id="stack">
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Технологии, с которыми мы работаем</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Мы используем передовые технологии и инструменты для создания эффективных AI-решений
                </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-robot-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Машинное обучение</h3>
                    <p class="text-gray-600 text-sm">TensorFlow, PyTorch, scikit-learn</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-chat-3-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Обработка языка</h3>
                    <p class="text-gray-600 text-sm">BERT, GPT, NLTK, spaCy</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-eye-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Компьютерное зрение</h3>
                    <p class="text-gray-600 text-sm">OpenCV, YOLO, ResNet</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-database-2-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Большие данные</h3>
                    <p class="text-gray-600 text-sm">Hadoop, Spark, Kafka</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-cloud-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Облачные технологии</h3>
                    <p class="text-gray-600 text-sm">AWS, Google Cloud, Azure</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-code-s-slash-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Языки программирования</h3>
                    <p class="text-gray-600 text-sm">Python, Java, JavaScript</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-settings-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">DevOps</h3>
                    <p class="text-gray-600 text-sm">Docker, Kubernetes, CI/CD</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-dashboard-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-2">Визуализация данных</h3>
                    <p class="text-gray-600 text-sm">Tableau, Power BI, D3.js</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
$tacticumProjectDiscussionCta = [
    "form_id" => "about-cta",
    "form_html_id" => "about-cta-form",
    "field_prefix" => "about",
];
include $_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/include/project-discussion-cta.php";
?>

<!-- Careers Section -->
<section id="career-section" class="py-20">
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
                            <i class="ri-balance-fill text-primary"></i>
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
                "bitrix:news.list",
                "vacancies",
                [
                        "COMPONENT_TEMPLATE" => "vacancies",
                        "IBLOCK_TYPE" => "company",
                        "IBLOCK_ID" => tacticum_iblock_id('vacancies'),
                        "NEWS_COUNT" => "0",
                        "SORT_BY1" => "SORT",
                        "SORT_ORDER1" => "ASC",
                        "SORT_BY2" => "ID",
                        "SORT_ORDER2" => "DESC",
                        "FILTER_NAME" => "",
                        "FIELD_CODE" => ["ID","CODE","NAME","SORT","PREVIEW_TEXT","DETAIL_TEXT","IBLOCK_TYPE_ID","IBLOCK_ID"],
                        "PROPERTY_CODE" => ["TIME","LOCATION","TYPE"],
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
    </div>
</section>

<!-- CTA Section -->
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

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
