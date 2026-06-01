<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Platform - платформа для корпоративных AI-продуктов");
$APPLICATION->SetPageProperty("description", "Tacticum Platform - единое инфраструктурное ядро для корпоративных AI-продуктов: LLM-шлюз, RAG, память, MCP-инструменты, права доступа, аудит и контроль стоимости.");
tacticum_apply_seo_defaults('/platform/');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");

tacticum_render_product_page([
    'eyebrow' => 'Tacticum Platform',
    'title' => 'Единое ядро для корпоративных AI-продуктов',
    'lead' => 'Платформенный слой для организаций, которым нужно запускать несколько AI-сценариев без дублирования LLM-шлюза, RAG, памяти, прав доступа, инструментов и аудита в каждом проекте.',
    'primary_cta_text' => 'Обсудить платформенный пилот',
    'secondary_cta_text' => 'Как внедряем',
    'secondary_cta_href' => '/services/',
    'badges' => [
        'LLM Gateway',
        'RAG и корпоративная память',
        'MCP Runtime',
        'RBAC, tenancy, audit',
    ],
    'hero_cards' => [
        [
            'title' => 'Зачем нужна платформа',
            'text' => 'Когда AI-сценариев становится больше одного, общая инфраструктура дешевле и управляемее, чем отдельный стек под каждого бота или ассистента.',
        ],
        [
            'title' => 'Как используется',
            'text' => 'Platform исполняет общие runtime, data, access и observability-сервисы, а Agents, Dev и Forum описывают доменную логику поверх нее.',
        ],
        [
            'title' => 'Как начать',
            'text' => 'Обычно стартуем с короткого assessment: какие AI-сценарии уже есть, где дублируется инфраструктура и какой контур нужен для пилота.',
        ],
    ],
    'sections' => [
        [
            'theme' => 'white',
            'eyebrow' => 'Для кого',
            'title' => 'Когда Platform становится отдельной ценностью',
            'text' => 'Платформа нужна не ради архитектуры как таковой. Она оправдана, когда AI уже должен жить в корпоративном контуре, интегрироваться с системами и проходить техническое и security-ревью.',
            'cards' => [
                [
                    'icon' => 'ri-building-4-line',
                    'title' => 'Несколько AI-приложений',
                    'text' => 'Разные команды запускают ассистентов, ботов, RAG-поиск или аналитику и начинают повторять одни и те же инфраструктурные слои.',
                ],
                [
                    'icon' => 'ri-shield-check-line',
                    'title' => 'Нужны контроль и аудит',
                    'text' => 'Важны разграничение доступа, журналирование действий, управляемые ключи провайдеров и понятная политика работы с данными.',
                ],
                [
                    'icon' => 'ri-route-line',
                    'title' => 'Нужен путь к production',
                    'text' => 'Пилот должен превращаться в поддерживаемый контур, а не оставаться набором скриптов, промптов и локальных интеграций.',
                ],
            ],
        ],
        [
            'theme' => 'muted',
            'eyebrow' => 'Что ломается без ядра',
            'title' => 'Self-build быстро превращается в несколько несовместимых AI-стеков',
            'cards' => [
                [
                    'icon' => 'ri-key-2-line',
                    'title' => 'Ключи и провайдеры',
                    'text' => 'Каждый проект хранит и ротирует ключи по-своему, а стоимость LLM-вызовов сложно связать с конкретным бизнес-сценарием.',
                ],
                [
                    'icon' => 'ri-database-2-line',
                    'title' => 'RAG и память',
                    'text' => 'Индексация документов, память диалога и доступ к корпоративным данным повторяются в разных реализациях.',
                ],
                [
                    'icon' => 'ri-tools-line',
                    'title' => 'Инструменты',
                    'text' => 'Парсеры документов, интеграции с CRM/ERP/wiki и служебные инструменты пишутся заново вместо общего каталога.',
                ],
            ],
        ],
        [
            'theme' => 'white',
            'eyebrow' => 'Модули',
            'title' => 'Что входит в платформенный слой',
            'columns_class' => 'lg:grid-cols-3',
            'cards' => [
                [
                    'icon' => 'ri-cpu-line',
                    'title' => 'Agent Runtime',
                    'text' => 'Единый исполнитель декларативных графов и сценариев для прикладных продуктов.',
                ],
                [
                    'icon' => 'ri-links-line',
                    'title' => 'LLM Gateway',
                    'text' => 'Абстракция над моделями и провайдерами с routing, квотами и контролем стоимости.',
                ],
                [
                    'icon' => 'ri-search-eye-line',
                    'title' => 'Knowledge / RAG',
                    'text' => 'Индексация и поиск по корпоративным документам для ассистентов и сценариев.',
                ],
                [
                    'icon' => 'ri-lock-password-line',
                    'title' => 'Identity / RBAC',
                    'text' => 'Пользователи, роли, scopes и правила доступа к данным и инструментам.',
                ],
                [
                    'icon' => 'ri-plug-line',
                    'title' => 'MCP Runtime',
                    'text' => 'Каталог и выполнение инструментов, через которые AI-продукты работают с системами.',
                ],
                [
                    'icon' => 'ri-pulse-line',
                    'title' => 'Observability',
                    'text' => 'Трассировка, события, контроль качества и прозрачность расходов на AI-вызовы.',
                ],
            ],
        ],
    ],
    'architecture' => [
        'title' => 'Platform внизу, продукты сверху',
        'text' => 'Прикладные продукты не должны заново реализовывать движки. Они описывают свою доменную логику, а общие runtime, data, access и ops-сервисы остаются в Platform.',
        'layers' => [
            [
                'title' => 'Прикладные продукты',
                'text' => 'Agents, Dev и Forum решают разные бизнес-задачи, но используют общий платформенный контур.',
                'items' => ['Tacticum Agents', 'Tacticum Dev', 'Tacticum Forum'],
            ],
            [
                'title' => 'Платформенное ядро',
                'text' => 'Общие сервисы исполнения, моделей, данных, доступа, инструментов и наблюдаемости.',
                'items' => ['Runtime', 'LLM', 'RAG', 'Memory', 'MCP', 'RBAC', 'Audit'],
            ],
            [
                'title' => 'Контур заказчика',
                'text' => 'Архитектура deployment и интеграций уточняется на discovery и зависит от требований к данным, системам и эксплуатации.',
                'items' => ['SaaS', 'on-prem', 'hybrid', 'integration'],
            ],
        ],
    ],
    'cta' => [
        'form_id' => 'platform-cta',
        'field_prefix' => 'platform',
        'title' => 'Оценим, нужна ли вам отдельная AI-платформа',
        'text' => 'Опишите текущие AI-сценарии, системы и ограничения. Мы предложим формат assessment, пилота или архитектурной сессии.',
        'form_title' => 'Заявка по Tacticum Platform',
        'button_text' => 'Обсудить Platform',
        'lead_context' => [
            'lead_entry' => 'platform',
            'lead_page_role' => 'product-page',
            'lead_product' => 'platform',
            'lead_intent' => 'platform-assessment',
            'lead_cta' => 'platform-cta',
            'lead_next_step' => 'architecture-session',
        ],
    ],
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
