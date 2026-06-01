<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Forum - сценарии и LLM для клиентских коммуникаций");
$APPLICATION->SetPageProperty("description", "Tacticum Forum - диалоговая платформа для клиентских коммуникаций: сценарные графы, LLM-обогащение, аналитика воронок, A/B-проверки и журнал диалогов.");
tacticum_apply_seo_defaults('/forum/');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");

tacticum_render_product_page([
    'eyebrow' => 'Tacticum Forum',
    'title' => 'Сценарии и LLM для управляемых клиентских коммуникаций',
    'lead' => 'Диалоговая платформа для контакт-центров и цифровых каналов, где критичные пути остаются сценарными, а LLM помогает распознавать намерения, уточнять запросы и находить новые потребности клиентов.',
    'primary_cta_text' => 'Разобрать поток обращений',
    'secondary_cta_text' => 'Внедрение и интеграции',
    'secondary_cta_href' => '/services/',
    'badges' => [
        'Scenario DSL',
        'LLM enrichment',
        'A/B tests',
        'Dialog journal',
    ],
    'hero_cards' => [
        [
            'title' => 'Не чистый LLM-бот',
            'text' => 'Для критичных ответов и бизнес-процессов нужны управляемые сценарии, checkpoints и журналируемость.',
        ],
        [
            'title' => 'Не старое дерево сценариев',
            'text' => 'LLM помогает распознавать намерение, работать с уточнениями и находить новые потребности по реальным диалогам.',
        ],
        [
            'title' => 'Пилот по потоку обращений',
            'text' => 'Начинать лучше с одного типового потока, где понятны правила ответа, эскалации и метрики.',
        ],
    ],
    'sections' => [
        [
            'theme' => 'white',
            'eyebrow' => 'Проблема',
            'title' => 'Две крайности в клиентских ботах плохо работают в enterprise',
            'cards' => [
                [
                    'icon' => 'ri-node-tree',
                    'title' => 'Жесткие сценарии',
                    'text' => 'Контролируемы, но быстро становятся сложными, плохо понимают живые формулировки и требуют постоянной ручной поддержки.',
                ],
                [
                    'icon' => 'ri-chat-smile-3-line',
                    'title' => 'Чистый LLM',
                    'text' => 'Гибок в диалоге, но для критичных путей нужен контроль ответов, эскалаций, источников и аудита.',
                ],
                [
                    'icon' => 'ri-customer-service-line',
                    'title' => 'Операторская нагрузка',
                    'text' => 'Без качественной автоматизации типовые обращения продолжают занимать время команды поддержки.',
                ],
            ],
        ],
        [
            'theme' => 'muted',
            'eyebrow' => 'Решение',
            'title' => 'Forum совмещает сценарный контроль и LLM-гибкость',
            'columns_class' => 'lg:grid-cols-3',
            'cards' => [
                [
                    'icon' => 'ri-flow-chart',
                    'title' => 'Scenario DSL',
                    'text' => 'Сценарии описываются как управляемые графы с сообщениями, условиями, вводом, checkpoints и завершением.',
                ],
                [
                    'icon' => 'ri-lightbulb-flash-line',
                    'title' => 'LLM enrichment',
                    'text' => 'Модель помогает распознать намерение, обработать уточнение или предложить новую потребность для разметки.',
                ],
                [
                    'icon' => 'ri-line-chart-line',
                    'title' => 'Funnel analytics',
                    'text' => 'Воронка показывает, где клиент выходит из сценария, где нужна эскалация и где формулировки требуют проверки.',
                ],
                [
                    'icon' => 'ri-flask-line',
                    'title' => 'A/B checks',
                    'text' => 'Можно проверять порядок шагов, формулировки, checkpoints и условия передачи оператору.',
                ],
                [
                    'icon' => 'ri-archive-line',
                    'title' => 'Dialog journal',
                    'text' => 'Диалоги и события сохраняются для анализа качества, расследований и улучшения сценариев.',
                ],
                [
                    'icon' => 'ri-plug-2-line',
                    'title' => 'Интеграции',
                    'text' => 'Подключение каналов и корпоративных систем проектируется через общий платформенный слой.',
                ],
            ],
        ],
        [
            'theme' => 'white',
            'eyebrow' => 'Где применять',
            'title' => 'Типовые контуры для первого запуска',
            'cards' => [
                [
                    'icon' => 'ri-shopping-cart-2-line',
                    'title' => 'E-commerce и retail',
                    'text' => 'Статусы заказов, возвраты, доставка, подбор сценариев и передача сложных обращений оператору.',
                ],
                [
                    'icon' => 'ri-bank-card-line',
                    'title' => 'Финансы и страхование',
                    'text' => 'Управляемые ответы по продуктам, документам, заявкам и статусам обращений.',
                ],
                [
                    'icon' => 'ri-building-line',
                    'title' => 'Госуслуги и внутренний support',
                    'text' => 'Регламентированные сценарии, понятная эскалация и журнал диалогов.',
                ],
            ],
        ],
    ],
    'architecture' => [
        'title' => 'Forum работает поверх общего runtime',
        'text' => 'Scenario Manifest исполняется платформенным runtime, а LLM-вызовы, RAG, права доступа, audit и observability остаются в Platform.',
        'layers' => [
            [
                'title' => 'Forum product layer',
                'text' => 'Сценарии, редактор, маршрутизация, A/B-проверки, аналитика и журнал диалогов.',
                'items' => ['Scenario DSL', 'Needs Catalog', 'A/B tests', 'Funnel analytics', 'Journal'],
            ],
            [
                'title' => 'Platform services',
                'text' => 'Единые модели, память, RAG, права доступа, каналы, инструменты и аудит.',
                'items' => ['Agent Runtime', 'LLM Gateway', 'RAG', 'Connectors', 'Audit'],
            ],
        ],
    ],
    'cta' => [
        'form_id' => 'forum-cta',
        'field_prefix' => 'forum',
        'title' => 'Разберем ваш поток обращений',
        'text' => 'Опишите канал, типовые запросы и текущий процесс поддержки. Мы предложим сценарий пилота и метрики проверки.',
        'form_title' => 'Заявка по Tacticum Forum',
        'button_text' => 'Обсудить Forum',
        'message_placeholder' => 'Например: входящие обращения в веб-чате и Telegram, типовые вопросы по заказам и возвратам',
        'lead_context' => [
            'lead_entry' => 'forum',
            'lead_page_role' => 'product-page',
            'lead_product' => 'forum',
            'lead_intent' => 'dialog-flow-assessment',
            'lead_cta' => 'forum-cta',
            'lead_next_step' => 'flow-assessment',
        ],
    ],
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
