<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return [];
}

return [
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
    'fit_guide' => [
        'eyebrow' => 'Product fit',
        'title' => 'Когда смотреть Tacticum Dev',
        'text' => 'Dev полезен, когда команда уже использует AI-инструменты или готовится к этому, но хочет сохранить архитектурные правила, дизайн-систему, тесты и traceability.',
        'fits' => [
            'items' => [
                'AI-assisted разработка уже появилась в командах, но правила, review и quality gates различаются.',
                'Есть brownfield-код, дизайн-система, ADR, тесты и требования, которые AI должен учитывать.',
                'Нужна проверяемая практика для одной команды, стека или workflow перед масштабированием.',
            ],
        ],
        'not_fits' => [
            'items' => [
                'Цель сформулирована как замена инженерной команды, а не управление качеством и процессом.',
                'Нет владельца engineering workflow, codebase context или готовности обсуждать gates.',
                'Нужен быстрый greenfield-прототип без требований к архитектуре, review и сопровождению.',
            ],
        ],
        'start' => [
            'items' => [
                'Выбрать одну команду, один стек и один тип задач от постановки до review.',
                'Собрать правила архитектуры, design tokens, тестовые ожидания и текущие AI-практики.',
                'Проверить analysis gate, реализацию, тесты и review на ограниченном workflow.',
            ],
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
    'use_cases' => [
        'title' => 'Какие Dev-workflows проверять первыми',
        'text' => 'Dev-пилот лучше строить вокруг одного workflow, где можно проверить не только скорость, но и качество, traceability и соблюдение правил.',
        'items' => [
            [
                'title' => 'AI-assisted workflow governance',
                'trigger' => 'Команды уже используют AI-инструменты, но правила анализа задач, реализации, тестов и review различаются.',
                'owner' => 'CTO, Head of Engineering, engineering excellence или tech lead.',
                'pilot_input' => 'Один тип задач, текущий workflow, правила review, тестовые ожидания, ADR и ограничения codebase.',
                'pilot_output' => 'Workflow policy, analysis gate, quality gates и список правил для масштабирования на другие команды.',
                'limitation' => 'Не обещает универсальное ускорение без измерения на выбранной команде и типе задач.',
            ],
            [
                'title' => 'Design-system compliance',
                'trigger' => 'AI генерирует UI, который расходится с токенами, компонентами и визуальными правилами продукта.',
                'owner' => 'Design system owner, frontend lead или product design lead.',
                'pilot_input' => 'Токены, компоненты, запреты, примеры экранов, UI acceptance criteria и текущий frontend stack.',
                'pilot_output' => 'Design token guardrail map, UI review checklist и правила для AI-assisted frontend changes.',
                'limitation' => 'Требует утвержденной дизайн-системы или хотя бы стабильного списка токенов и компонентных правил.',
            ],
            [
                'title' => 'Brownfield refactor control',
                'trigger' => 'AI помогает быстрее менять legacy-код, но растет риск regression, нарушения архитектуры и неучтенных edge cases.',
                'owner' => 'Tech lead, architect, QA lead или владелец brownfield-направления.',
                'pilot_input' => 'Один модуль, known risks, тесты, архитектурные ограничения, acceptance criteria и rollback expectations.',
                'pilot_output' => 'Refactor analysis gate, required checks, review protocol and regression-risk map.',
                'limitation' => 'Не заменяет тесты, review и архитектурную ответственность владельцев кода.',
            ],
        ],
    ],
    'comparison' => [
        'title' => 'Dev - не команда разработки и не внутренний ассистент',
        'text' => 'Tacticum Dev нужен для управления AI-assisted engineering workflow. Delivery-команда, продуктовый ассистент и оценка проекта остаются отдельными входами.',
        'columns' => [
            [
                'title' => 'Выбирайте Dev',
                'text' => 'Когда уже есть инженерный процесс и AI нужно сделать управляемым.',
                'items' => [
                    'Rules, profiles, knowledge layer, design tokens and quality gates.',
                    'Пилот на одной команде, одном стеке и одном workflow.',
                    'Фокус на architecture, review, tests and traceability.',
                ],
            ],
            [
                'title' => 'Выбирайте Agents',
                'text' => 'Когда нужен ассистент для бизнес-функции, а не engineering workflow.',
                'items' => [
                    'HR, legal, finance, support, IT или база знаний.',
                    'Документы, доступы, business handoff and RAG quality.',
                    'Функциональный владелец вместо engineering owner.',
                ],
                'href' => '/agents/',
                'link_text' => 'Смотреть Agents',
            ],
            [
                'title' => 'Выбирайте Price / Services',
                'text' => 'Когда нужен состав команды или внедрение, а не governance-продукт.',
                'items' => [
                    'Price - роли, уровни, загрузка и бюджет команды.',
                    'Services - discovery, разработка, интеграции и запуск.',
                    'Offer / calculator - предварительная оценка проекта.',
                ],
                'href' => '/price/',
                'link_text' => 'Собрать команду',
            ],
        ],
    ],
    'procurement' => [
        'title' => 'Что согласовать перед Dev-пилотом',
        'text' => 'Dev-пилот затрагивает codebase context, инженерные правила, дизайн-систему, тесты и review-процесс. До старта нужно явно определить границы доступа и критерии качества.',
        'items' => [
            [
                'icon' => 'ri-git-branch-line',
                'title' => 'Codebase и доступы',
                'text' => 'Какие репозитории, ветки, окружения и данные доступны для пилота, какие зоны остаются read-only или вне контура.',
            ],
            [
                'icon' => 'ri-brush-line',
                'title' => 'Design system и правила UI',
                'text' => 'Какие токены, компоненты, запреты и acceptance criteria должны учитываться в AI-assisted workflow.',
            ],
            [
                'icon' => 'ri-checkbox-multiple-line',
                'title' => 'Quality gates',
                'text' => 'Какие проверки обязательны: анализ задачи, тесты, security review, architecture review, visual review и критерии merge.',
            ],
        ],
        'note_text' => 'Не публикуем обещания про сокращение команды, проценты ускорения, снижение ошибок или универсальные quality gates без пилотной evidence.',
        'cta_text' => 'Обсудить Dev-пилот',
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
