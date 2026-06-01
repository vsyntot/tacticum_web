<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Dev - управление AI-assisted разработкой");
$APPLICATION->SetPageProperty("description", "Tacticum Dev помогает инженерным организациям управлять AI-assisted разработкой: профили, knowledge layer, design token compliance, quality gates и traceability.");
$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");

$tacticumProductPage = [
    'eyebrow' => 'Tacticum Dev',
    'title' => 'AI-assisted разработка без потери архитектуры и качества',
    'lead' => 'Governance-слой для инженерных команд, которые уже используют AI-инструменты в разработке и хотят управлять профилями, знаниями, design tokens, quality gates и traceability на уровне процесса.',
    'primary_cta_text' => 'Оценить готовность команды',
    'secondary_cta_text' => 'Delivery-подход',
    'secondary_cta_href' => '/services/',
    'badges' => [
        'Engineering profiles',
        'RE Knowledge Layer',
        'Design Token Layer',
        'Quality Gates',
    ],
    'hero_cards' => [
        [
            'title' => 'Не замена инженеров',
            'text' => 'Публичная модель Tacticum Dev сфокусирована на управляемости AI-разработки: архитектуре, повторяемости, проверках и качестве.',
        ],
        [
            'title' => 'Не еще один чат',
            'text' => 'Ценность не в генерации кода сама по себе, а в профилях, knowledge layer, gates и правилах работы с конкретным стеком.',
        ],
        [
            'title' => 'Пилот на одной команде',
            'text' => 'Стартовая проверка должна показать, какие процессы, знания и quality gates действительно нужны вашей команде.',
        ],
    ],
    'sections' => [
        [
            'theme' => 'white',
            'eyebrow' => 'Проблема',
            'title' => 'Обычный AI-coding быстро упирается в governance',
            'text' => 'AI-ассистент может ускорить написание кода, но без общей дисциплины растут риски: drift архитектуры, нарушение дизайн-системы, слабая трассируемость и неодинаковое качество между командами.',
            'cards' => [
                [
                    'icon' => 'ri-git-merge-line',
                    'title' => 'Architecture drift',
                    'text' => 'Разные инженеры и агенты принимают локальные решения, которые не всегда совпадают с целевой архитектурой.',
                ],
                [
                    'icon' => 'ri-palette-line',
                    'title' => 'Design violations',
                    'text' => 'AI легко подставляет цвета, размеры и паттерны, которые не соответствуют продуктовой дизайн-системе.',
                ],
                [
                    'icon' => 'ri-bug-line',
                    'title' => 'Regression risk',
                    'text' => 'В brownfield-коде важно учитывать старые соглашения, edge cases и стековые ограничения.',
                ],
            ],
        ],
        [
            'theme' => 'muted',
            'eyebrow' => 'Слои продукта',
            'title' => 'Что добавляет Tacticum Dev',
            'columns_class' => 'lg:grid-cols-3',
            'cards' => [
                [
                    'icon' => 'ri-profile-line',
                    'title' => 'Профили вместо личных настроек',
                    'text' => 'Единые правила работы с AI-инструментами для стека, команды и типа задач.',
                ],
                [
                    'icon' => 'ri-book-open-line',
                    'title' => 'RE Knowledge Layer',
                    'text' => 'Контекст требований, ADR, use cases, ограничений и поведения продукта.',
                ],
                [
                    'icon' => 'ri-brush-line',
                    'title' => 'Design Token Layer',
                    'text' => 'Контроль соответствия UI-решений дизайн-системе и токенам.',
                ],
                [
                    'icon' => 'ri-checkbox-multiple-line',
                    'title' => 'Analysis Gate',
                    'text' => 'Структурирование задачи до кода: цель, решение, риски, тесты и acceptance criteria.',
                ],
                [
                    'icon' => 'ri-shield-check-line',
                    'title' => 'Quality Gates',
                    'text' => 'Проверки, которые помогают не превращать AI-ускорение в долг и регрессии.',
                ],
                [
                    'icon' => 'ri-stack-line',
                    'title' => 'Stack-specific bundles',
                    'text' => 'Правила и инструменты для конкретных стеков: web, mobile, backend, desktop или mixed codebase.',
                ],
            ],
        ],
        [
            'theme' => 'white',
            'eyebrow' => 'Пилот',
            'title' => 'Что стоит проверить первым',
            'columns_class' => 'lg:grid-cols-4',
            'cards' => [
                [
                    'meta' => '01',
                    'title' => 'Одна команда',
                    'text' => 'Выбираем команду и тип задач, где AI уже используется или может дать понятную пользу.',
                ],
                [
                    'meta' => '02',
                    'title' => 'Один стек',
                    'text' => 'Фиксируем правила, codebase context, ограничения и ожидаемые проверки.',
                ],
                [
                    'meta' => '03',
                    'title' => 'Один workflow',
                    'text' => 'Проектируем путь от задачи до merge request: анализ, реализация, тесты, review.',
                ],
                [
                    'meta' => '04',
                    'title' => 'Метрики',
                    'text' => 'Сравниваем lead time, качество review, regression rate и соблюдение правил.',
                ],
            ],
        ],
    ],
    'architecture' => [
        'title' => 'Dev использует Platform как runtime и knowledge backbone',
        'text' => 'Tacticum Dev не должен хранить все знания и инструменты локально в каждом проекте. Platform дает RAG, MCP Runtime, Workflow Spec Engine, identity и наблюдаемость.',
        'layers' => [
            [
                'title' => 'Dev product layer',
                'text' => 'Профили, workflow, design token rules, stack bundles и quality gates.',
                'items' => ['Profiles', 'Feature Lifecycle', 'Design Tokens', 'Quality Gates'],
            ],
            [
                'title' => 'Platform services',
                'text' => 'Knowledge/RAG, MCP Runtime, Workflow Spec Engine, access scopes and audit.',
                'items' => ['RAG', 'MCP Runtime', 'Workflow', 'RBAC', 'Audit'],
            ],
        ],
    ],
    'rollout' => [
        'title' => 'Как внедряется Tacticum Dev',
        'text' => 'Пилот строится вокруг одной команды, одного стека и одного workflow. Это помогает проверить правила и gates до расширения на весь engineering-контур.',
        'steps' => [
            [
                'title' => 'Диагностика процесса',
                'text' => 'Смотрим текущий путь задачи от постановки до merge, источники знаний, дизайн-систему, тесты и review practices.',
            ],
            [
                'title' => 'Правила и профили',
                'text' => 'Фиксируем AI-профили, ограничения codebase, knowledge layer, design token rules и критерии качества.',
            ],
            [
                'title' => 'Пилот workflow',
                'text' => 'Прогоняем один тип задач через analysis gate, реализацию, тесты и review, отслеживая качество и повторяемость.',
            ],
            [
                'title' => 'Расширение практики',
                'text' => 'По итогам пилота уточняем gates, ownership, обучение команды и порядок подключения других стеков.',
            ],
        ],
    ],
    'proof' => [
        'title' => 'Что подтверждаем в Dev-пилоте',
        'text' => 'Публично не обещаем проценты ускорения или сокращения ошибок. Сначала проверяем, какие правила реально помогают команде работать с AI управляемо.',
        'items' => [
            [
                'meta' => 'Workflow',
                'title' => 'Трассировка от задачи до review',
                'text' => 'Фиксируем, как задача проходит analysis gate, реализацию, тесты и review, и где AI требует дополнительного контроля.',
            ],
            [
                'meta' => 'Rules',
                'title' => 'Проверка quality gates',
                'text' => 'Смотрим, какие архитектурные, тестовые и security-проверки должны быть обязательными для выбранного стека.',
            ],
            [
                'meta' => 'Design',
                'title' => 'Соответствие дизайн-системе',
                'text' => 'Проверяем, какие design token rules и UI-ограничения нужны, чтобы AI-изменения не расходились с утвержденной системой.',
            ],
        ],
    ],
    'faq' => [
        'title' => 'Вопросы по Tacticum Dev',
        'text' => 'Как рассматривать Dev как governance-слой для AI-assisted разработки, а не просто набор промптов.',
        'items' => [
            [
                'question' => 'Зачем нужен Dev, если команда уже использует AI-инструменты?',
                'answer' => 'Отдельные инструменты помогают писать код, но не задают общие правила для архитектуры, дизайн-системы, review, тестов и трассируемости. Dev нужен, когда AI-assisted workflow нужно сделать повторяемым и управляемым.',
            ],
            [
                'question' => 'С чего начинать пилот Tacticum Dev?',
                'answer' => 'Лучше начать с одной команды, одного стека и одного рабочего процесса от задачи до merge request. Затем фиксируются метрики, quality gates и правила, которые действительно нужны в этом контуре.',
            ],
            [
                'question' => 'Как Dev связан с дизайн-системой?',
                'answer' => 'Design Token Layer и проверки помогают удерживать UI-реализацию в рамках принятой дизайн-системы. Конкретные правила зависят от того, какие токены, компоненты и запреты утверждены в вашей системе.',
            ],
        ],
    ],
    'cta' => [
        'form_id' => 'dev-cta',
        'field_prefix' => 'dev',
        'title' => 'Оценим готовность вашей команды к AI-assisted workflow',
        'text' => 'Расскажите о стеке, размере команды и текущем процессе разработки. Мы предложим формат assessment или пилота на одной команде.',
        'form_title' => 'Заявка по Tacticum Dev',
        'button_text' => 'Обсудить Tacticum Dev',
        'message_placeholder' => 'Например: 20 разработчиков, web/mobile, уже используем AI-инструменты, хотим стандартизировать процесс',
        'scenario_label' => 'Фокус оценки',
        'scenario_empty_label' => 'Выберите ближайший сценарий',
        'scenario_options' => [
            [
                'VALUE' => 'ai-workflow-assessment',
                'LABEL' => 'Оценить текущий AI-assisted workflow',
            ],
            [
                'VALUE' => 'quality-gates-pilot',
                'LABEL' => 'Пилот quality gates на команде',
            ],
            [
                'VALUE' => 'design-system-guardrails',
                'LABEL' => 'Связать AI-разработку с дизайн-системой',
            ],
        ],
        'lead_context' => [
            'lead_entry' => 'dev',
            'lead_page_role' => 'product-page',
            'lead_product' => 'dev',
            'lead_intent' => 'engineering-workflow-assessment',
            'lead_cta' => 'dev-cta',
            'lead_next_step' => 'engineering-assessment',
        ],
    ],
];

tacticum_apply_seo_defaults('/dev/', [
    'schema' => tacticum_product_page_schema(
        $tacticumProductPage,
        '/dev/',
        'DeveloperApplication',
        'Governance-слой для AI-assisted разработки: профили, knowledge layer, design token rules, workflow gates, quality gates и traceability.'
    ),
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");

tacticum_render_product_page($tacticumProductPage);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
