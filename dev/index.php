<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Dev - управление AI-assisted разработкой");
$APPLICATION->SetPageProperty("description", "Tacticum Dev помогает инженерным организациям управлять AI-assisted разработкой: профили, knowledge layer, design token compliance, quality gates и traceability.");
tacticum_apply_seo_defaults('/dev/');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");

tacticum_render_product_page([
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
    'cta' => [
        'form_id' => 'dev-cta',
        'field_prefix' => 'dev',
        'title' => 'Оценим готовность вашей команды к AI-assisted workflow',
        'text' => 'Расскажите о стеке, размере команды и текущем процессе разработки. Мы предложим формат assessment или пилота на одной команде.',
        'form_title' => 'Заявка по Tacticum Dev',
        'button_text' => 'Обсудить Tacticum Dev',
        'message_placeholder' => 'Например: 20 разработчиков, web/mobile, уже используем AI-инструменты, хотим стандартизировать процесс',
        'lead_context' => [
            'lead_entry' => 'dev',
            'lead_page_role' => 'product-page',
            'lead_product' => 'dev',
            'lead_intent' => 'engineering-workflow-assessment',
            'lead_cta' => 'dev-cta',
            'lead_next_step' => 'engineering-assessment',
        ],
    ],
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
