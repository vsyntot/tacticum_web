<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("О компании Tacticum - команда корпоративных AI-продуктов");
$APPLICATION->SetPageProperty("description", "О компании Tacticum: команда, подход и опыт разработки корпоративных AI-продуктов, внедрения AI-решений, автоматизации и интеграций.");
tacticum_apply_seo_defaults('/about/', [
    'image' => SITE_TEMPLATE_PATH . '/images/about_hero_bg.jpg',
    'image_width' => 1536,
    'image_height' => 800,
    'schema' => [
        '@type' => 'AboutPage',
        '@id' => tacticum_public_url('/about/#about-page'),
        'name' => 'О компании Tacticum',
        'url' => tacticum_public_url('/about/'),
        'mainEntity' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
    ],
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<section class="about-hero-bg min-h-[500px] pt-24 flex items-center">
    <div class="container mx-auto px-4 py-20">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight text-secondary">Команда Tacticum развивает корпоративные AI-продукты</h1>
            <p class="text-lg md:text-xl mb-8 text-gray-700">
                Мы соединяем продуктовую разработку, AI-инженерию и внедрение в бизнес-процессы: от Platform и
                Agents до Dev, Forum, интеграций, оценки проекта и команды под delivery.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="/platform/" class="inline-block bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap text-center">Смотреть продукты</a>
                <a href="/calculator/" class="inline-block bg-white text-secondary border border-gray-200 px-8 py-3 rounded-button hover:border-primary hover:text-primary transition-colors whitespace-nowrap text-center">Оценить задачу</a>
            </div>
        </div>
    </div>
</section>

<section id="about-company" class="tacticum-anchor-target py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-6">Кто мы?</h2>
            <div class="bg-primary/5 border-l-4 border-primary p-6 rounded-lg mb-8">
                <p class="text-xl italic text-gray-700">"Мы строим AI-решения так, чтобы они проходили путь от идеи и пилота до рабочего процесса, команды и эксплуатации"</p>
            </div>
            <p class="text-lg text-gray-600">
                Tacticum — это команда в области разработки программного обеспечения, искусственного интеллекта и
                автоматизации бизнес-процессов. Мы развиваем собственную продуктовую линейку и сохраняем delivery
                практику, чтобы помогать компаниям переходить от гипотезы к проверяемому результату.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="stat-card bg-white rounded-xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                <div class="text-4xl font-bold text-primary mb-2">AI/IT</div>
                <p class="text-gray-600">Проектная разработка и внедрение</p>
            </div>
            <div class="stat-card bg-white rounded-xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                <div class="text-4xl font-bold text-primary mb-2">B2B</div>
                <p class="text-gray-600">Отраслевые сценарии и интеграции</p>
            </div>
            <div class="stat-card bg-white rounded-xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                <div class="text-4xl font-bold text-primary mb-2">Team</div>
                <p class="text-gray-600">Подбор ролей под этап и задачу</p>
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
                        Запуск линейки собственных AI-продуктов для автоматизации бизнес-процессов. Формирование
                        повторяемых подходов к оценке, пилотированию и внедрению AI-решений.
                    </p>
                </div>
                <div class="relative pl-14">
                    <div class="absolute left-0 top-0 w-12 h-12 bg-primary rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">2025</span>
                    </div>
                    <h4 class="text-xl font-bold text-secondary mb-2">Сегодня</h4>
                    <p class="text-gray-600">
                        Сегодня Tacticum — это команда аналитиков, инженеров и разработчиков, которая помогает
                        компаниям оценивать, проектировать и внедрять AI- и IT-решения под реальные бизнес-задачи.
                        Мы продолжаем развивать экспертизу в автоматизации, интеграциях и прикладном искусственном
                        интеллекте.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="mb-12 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Vendor trust</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Почему product-first модель требует сильной delivery-команды
            </h2>
            <p class="text-lg text-gray-600">
                Корпоративный AI-продукт не живет только в презентации или прототипе. Ему нужны архитектура,
                данные, интеграции, безопасность, эксплуатация, команда и понятный путь от пилота до production.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Архитектура</h3>
                <p class="text-gray-600">
                    Проектируем общий AI-контур, RAG, интеграции, доступы, аудит и эксплуатационные ограничения.
                </p>
            </a>
            <a href="/services/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-route-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Внедрение</h3>
                <p class="text-gray-600">
                    Ведем discovery, пилот, интеграции, запуск и развитие решения короткими управляемыми этапами.
                </p>
            </a>
            <a href="/price/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-team-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Команда</h3>
                <p class="text-gray-600">
                    Подбираем роли под product workstream: аналитика, backend, data, integration, QA, PM и DevOps.
                </p>
            </a>
            <a href="/offer/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-file-search-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Оценка</h3>
                <p class="text-gray-600">
                    Используем примеры расчетов и калькулятор как безопасный старт для уточнения scope и рисков.
                </p>
            </a>
        </div>
    </div>
</section>

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
                    <h3 class="text-lg font-bold text-secondary mb-2">Инфраструктура</h3>
                    <p class="text-gray-600 text-sm">Контейнеры, хранилища, очереди и runtime-сервисы</p>
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
$APPLICATION->IncludeComponent(
    "tacticum:lead.cta",
    "",
    [
        "TYPE" => "project-discussion",
        "FORM_ID" => "about-cta",
        "FORM_HTML_ID" => "about-cta-form",
        "FIELD_PREFIX" => "about",
        "TITLE" => "Обсудим задачу с командой Tacticum",
        "TEXT" => "Расскажите, какой результат нужен бизнесу. Мы подскажем, что лучше начать первым: оценку, discovery, команду или прототип.",
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

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
