#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStoragePageContentSeed
{
    private bool $apply;
    private bool $json;
    private string $wave;
    private string $documentRoot;
    private array $actions = [];
    private array $warnings = [];
    private array $errors = [];

    public function __construct(bool $apply, bool $json, string $wave, string $documentRoot)
    {
        $this->apply = $apply;
        $this->json = $json;
        $this->wave = $wave;
        $this->documentRoot = rtrim($documentRoot, '/');
    }

    public function run(): int
    {
        $this->bootstrap();
        if (!Loader::includeModule('iblock')) {
            $this->errors[] = 'Bitrix iblock module is unavailable.';
            return $this->finish();
        }

        if ($this->wave !== 'wave_1') {
            $this->errors[] = 'Unsupported page-content seed wave: ' . $this->wave . '. Only wave_1 is available.';
            return $this->finish();
        }

        $sectionsIblockId = $this->iblockId('page_sections');
        $blocksIblockId = $this->iblockId('page_blocks');
        if ($sectionsIblockId <= 0) {
            $this->errors[] = 'Missing page_sections iblock config key.';
        }
        if ($blocksIblockId <= 0) {
            $this->errors[] = 'Missing page_blocks iblock config key.';
        }
        if (!empty($this->errors)) {
            return $this->finish();
        }

        $this->validateProperties($sectionsIblockId, [
            'PAGE_KEY', 'SECTION_KEY', 'TEMPLATE_KEY', 'MIGRATION_STATUS', 'EYEBROW', 'TITLE', 'TEXT',
            'THEME', 'TONE', 'CTA_TEXT', 'CTA_HREF', 'FALLBACK_PARTIAL', 'OWNER_SCOPE',
        ], 'page_sections');
        $this->validateProperties($blocksIblockId, [
            'SECTION', 'BLOCK_KEY', 'ITEM_TYPE', 'TITLE', 'TEXT', 'ICON', 'HREF', 'META', 'VALUE', 'LABEL', 'TONE', 'PROOF_STATUS',
        ], 'page_blocks');
        if (!empty($this->errors)) {
            return $this->finish();
        }

        foreach ($this->waveOneSections() as $section) {
            $this->seedSection($sectionsIblockId, $blocksIblockId, $section);
        }

        return $this->finish();
    }

    private function bootstrap(): void
    {
        $prolog = $this->documentRoot . '/bitrix/modules/main/include/prolog_before.php';
        if (!is_file($prolog)) {
            throw new RuntimeException('Bitrix prolog not found: ' . $prolog);
        }

        $_SERVER['DOCUMENT_ROOT'] = $this->documentRoot;
        $_SERVER['REQUEST_METHOD'] = 'CLI';
        define('NO_KEEP_STATISTIC', true);
        define('NOT_CHECK_PERMISSIONS', true);

        require $prolog;
        tacticum_tools_require_product_content_runtime($this->documentRoot);
    }

    private function validateProperties(int $iblockId, array $codes, string $iblockKey): void
    {
        foreach ($codes as $code) {
            if ($this->propertyId($iblockId, (string)$code) <= 0) {
                $this->errors[] = "Iblock {$iblockKey} has no {$code} property.";
            }
        }
    }

    private function seedSection(int $sectionsIblockId, int $blocksIblockId, array $section): void
    {
        $pageKey = $this->stringValue($section['page'] ?? '');
        $sectionKey = $this->stringValue($section['section_key'] ?? '');
        if ($pageKey === '' || $sectionKey === '') {
            $this->warnings[] = 'Skip page-content section with empty page or section key.';
            return;
        }

        $sectionCode = $this->sectionCode($pageKey, $sectionKey);
        $sectionId = $this->findElementIdByCode($sectionsIblockId, $sectionCode);
        $title = $this->stringValue($section['title'] ?? '');
        $text = $this->stringValue($section['text'] ?? '');
        $sectionFields = [
            'IBLOCK_ID' => $sectionsIblockId,
            'ACTIVE' => 'Y',
            'NAME' => $title !== '' ? $title : $sectionCode,
            'CODE' => $sectionCode,
            'XML_ID' => 'tacticum-page-section-' . $sectionCode,
            'SORT' => (int)($section['sort'] ?? 500),
            'PREVIEW_TEXT' => $text,
            'PREVIEW_TEXT_TYPE' => 'text',
            'DETAIL_TEXT' => $text,
            'DETAIL_TEXT_TYPE' => 'text',
        ];
        $sectionProperties = [
            'PAGE_KEY' => $pageKey,
            'SECTION_KEY' => $sectionKey,
            'TEMPLATE_KEY' => $this->stringValue($section['template_key'] ?? ''),
            'MIGRATION_STATUS' => 'shadow',
            'EYEBROW' => $this->stringValue($section['eyebrow'] ?? ''),
            'TITLE' => $title,
            'TEXT' => $text,
            'THEME' => $this->stringValue($section['theme'] ?? ''),
            'TONE' => $this->stringValue($section['tone'] ?? ''),
            'CTA_TEXT' => $this->stringValue($section['cta_text'] ?? ''),
            'CTA_HREF' => $this->stringValue($section['cta_href'] ?? ''),
            'FALLBACK_PARTIAL' => $this->stringValue($section['fallback_partial'] ?? ''),
            'OWNER_SCOPE' => $this->stringValue($section['owner_scope'] ?? 'content'),
        ];

        if ($sectionId > 0) {
            $this->updateElement($sectionsIblockId, $sectionId, $sectionFields, $sectionProperties, 'page_section', $sectionCode);
        } else {
            $sectionId = $this->createElement($sectionFields, $sectionProperties, 'page_section', $sectionCode);
        }

        $sort = 100;
        foreach ($this->blocks($section['blocks'] ?? []) as $block) {
            $block['sort'] = $sort;
            $this->seedBlock($blocksIblockId, $sectionCode, $sectionId, $block);
            $sort += 100;
        }
    }

    private function seedBlock(int $blocksIblockId, string $sectionCode, int $sectionId, array $block): void
    {
        $blockKey = $this->stringValue($block['block_key'] ?? '');
        if ($blockKey === '') {
            $this->warnings[] = 'Skip page-content block with empty block key under ' . $sectionCode . '.';
            return;
        }

        $blockCode = $sectionCode . '-' . $blockKey;
        $blockId = $this->findElementIdByCode($blocksIblockId, $blockCode);
        $title = $this->stringValue($block['title'] ?? '');
        $text = $this->stringValue($block['text'] ?? '');
        $blockFields = [
            'IBLOCK_ID' => $blocksIblockId,
            'ACTIVE' => 'Y',
            'NAME' => $title !== '' ? $title : $blockCode,
            'CODE' => $blockCode,
            'XML_ID' => 'tacticum-page-block-' . $blockCode,
            'SORT' => (int)($block['sort'] ?? 500),
            'PREVIEW_TEXT' => $text,
            'PREVIEW_TEXT_TYPE' => 'text',
            'DETAIL_TEXT' => $text,
            'DETAIL_TEXT_TYPE' => 'text',
        ];
        $blockProperties = [
            'SECTION' => $sectionId,
            'BLOCK_KEY' => $blockKey,
            'ITEM_TYPE' => $this->stringValue($block['item_type'] ?? 'card'),
            'TITLE' => $title,
            'TEXT' => $text,
            'ICON' => $this->stringValue($block['icon'] ?? ''),
            'HREF' => $this->stringValue($block['href'] ?? ''),
            'META' => $this->stringValue($block['meta'] ?? ''),
            'VALUE' => $this->stringValue($block['value'] ?? ''),
            'LABEL' => $this->stringValue($block['label'] ?? ''),
            'TONE' => $this->stringValue($block['tone'] ?? ''),
            'PROOF_STATUS' => $this->stringValue($block['proof_status'] ?? 'not_public'),
        ];

        if ($blockId > 0) {
            $this->updateElement($blocksIblockId, $blockId, $blockFields, $blockProperties, 'page_block', $blockCode);
        } else {
            $this->createElement($blockFields, $blockProperties, 'page_block', $blockCode);
        }
    }

    private function createElement(array $fields, array $properties, string $kind, string $code): int
    {
        $this->actions[] = ['kind' => $kind, 'code' => $code, 'action' => 'create', 'status' => $this->apply ? 'pending_apply' : 'would_create'];
        if (!$this->apply) {
            return 0;
        }

        $element = new CIBlockElement();
        $id = (int)$element->Add(array_merge($fields, ['PROPERTY_VALUES' => $properties]));
        if ($id <= 0) {
            $this->errors[] = "Failed to create {$kind} {$code}: " . (string)$element->LAST_ERROR;
            $this->actions[count($this->actions) - 1]['status'] = 'failed';
            return 0;
        }

        $this->actions[count($this->actions) - 1]['id'] = $id;
        $this->actions[count($this->actions) - 1]['status'] = 'applied';

        return $id;
    }

    private function updateElement(int $iblockId, int $elementId, array $fields, array $properties, string $kind, string $code): void
    {
        $this->actions[] = ['kind' => $kind, 'code' => $code, 'id' => $elementId, 'action' => 'update', 'status' => $this->apply ? 'pending_apply' : 'would_update'];
        if (!$this->apply) {
            return;
        }

        $element = new CIBlockElement();
        if (!$element->Update($elementId, $fields)) {
            $this->errors[] = "Failed to update {$kind} {$code}: " . (string)$element->LAST_ERROR;
            $this->actions[count($this->actions) - 1]['status'] = 'failed';
            return;
        }
        CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $properties);
        $this->actions[count($this->actions) - 1]['status'] = 'applied';
    }

    private function finish(): int
    {
        $summary = $this->summary();
        if ($this->json) {
            echo json_encode([
                'success' => empty($this->errors),
                'mode' => $this->apply ? 'apply' : 'dry-run',
                'wave' => $this->wave,
                'safe_for_release_evidence' => false,
                'release_evidence_hint' => 'Use content-storage-audit --scope=page-content aggregate output after apply.',
                'summary' => $summary,
                'actions' => $this->actions,
                'warnings' => $this->warnings,
                'errors' => $this->errors,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

            return empty($this->errors) ? 0 : 1;
        }

        $this->line(empty($this->errors)
            ? 'Content storage page-content seed check passed.'
            : 'Content storage page-content seed failed.');
        $this->line('Mode: ' . ($this->apply ? 'apply' : 'dry-run'));
        $this->line('Wave: ' . $this->wave);
        $this->line('Runtime switch: unchanged. Seeded rows must stay shadow until page-level owner approval and smoke checks pass.');
        $this->line('Release evidence: use aggregate content-storage-audit output after apply.');
        $this->line('');

        foreach ($this->actions as $action) {
            $prefix = ($action['status'] ?? '') === 'applied'
                ? '[apply]'
                : ((string)($action['status'] ?? '') === 'would_create' || (string)($action['status'] ?? '') === 'would_update' ? '[dry-run]' : '[check]');
            $this->line(sprintf(
                '%s %s %s %s%s status=%s',
                $prefix,
                (string)$action['action'],
                (string)$action['kind'],
                (string)$action['code'],
                isset($action['id']) ? ' #' . (int)$action['id'] : '',
                (string)($action['status'] ?? '')
            ));
        }

        $this->line('');
        $this->line('Page-content seed summary:'
            . ' sections_created=' . $summary['sections_created']
            . ', sections_updated=' . $summary['sections_updated']
            . ', blocks_created=' . $summary['blocks_created']
            . ', blocks_updated=' . $summary['blocks_updated']
            . ', applied=' . $summary['applied']
            . ', mode=' . ($this->apply ? 'apply' : 'dry-run'));

        foreach (['warnings' => $this->warnings, 'errors' => $this->errors] as $label => $messages) {
            if (empty($messages)) {
                continue;
            }
            $this->line('');
            $this->line(ucfirst($label) . ':');
            foreach ($messages as $message) {
                $this->line('- ' . $message);
            }
        }

        return empty($this->errors) ? 0 : 1;
    }

    private function summary(): array
    {
        $summary = [
            'sections_created' => 0,
            'sections_updated' => 0,
            'blocks_created' => 0,
            'blocks_updated' => 0,
            'applied' => 0,
            'total_actions' => count($this->actions),
        ];
        foreach ($this->actions as $action) {
            $kind = (string)($action['kind'] ?? '');
            $actionType = (string)($action['action'] ?? '');
            if ($kind === 'page_section' && $actionType === 'create') {
                $summary['sections_created']++;
            } elseif ($kind === 'page_section' && $actionType === 'update') {
                $summary['sections_updated']++;
            } elseif ($kind === 'page_block' && $actionType === 'create') {
                $summary['blocks_created']++;
            } elseif ($kind === 'page_block' && $actionType === 'update') {
                $summary['blocks_updated']++;
            }
            if (($action['status'] ?? '') === 'applied') {
                $summary['applied']++;
            }
        }

        return $summary;
    }

    private function line(string $message): void
    {
        echo $message . PHP_EOL;
    }

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id($key) : 0;
    }

    private function propertyId(int $iblockId, string $code): int
    {
        $result = CIBlockProperty::GetList(['ID' => 'ASC'], ['IBLOCK_ID' => $iblockId, 'CODE' => $code]);
        $property = $result->Fetch();

        return is_array($property) ? (int)$property['ID'] : 0;
    }

    private function findElementIdByCode(int $iblockId, string $code): int
    {
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, '=CODE' => $code, 'CHECK_PERMISSIONS' => 'N'],
            false,
            ['nTopCount' => 1],
            ['ID']
        );
        $element = $result->Fetch();

        return is_array($element) ? (int)$element['ID'] : 0;
    }

    private function sectionCode(string $pageKey, string $sectionKey): string
    {
        $page = trim($pageKey, '/');
        if ($page === '') {
            $page = 'home';
        }
        $page = str_replace('/', '-', $page);

        return $page . '-' . $sectionKey;
    }

    private function stringValue(mixed $value): string
    {
        return trim((string)$value);
    }

    private function blocks(mixed $blocks): array
    {
        return is_array($blocks) ? array_values(array_filter($blocks, 'is_array')) : [];
    }

    private function block(string $key, string $type, string $title, string $text, array $extra = []): array
    {
        return array_merge([
            'block_key' => $key,
            'item_type' => $type,
            'title' => $title,
            'text' => $text,
        ], $extra);
    }

    private function waveOneSections(): array
    {
        return [
            [
                'page' => '/services/',
                'section_key' => 'delivery-layer',
                'sort' => 200,
                'template_key' => 'product-card-grid',
                'fallback_partial' => 'local/components/tacticum/services.page/templates/.default/parts/delivery-layer.php',
                'owner_scope' => 'marketing',
                'eyebrow' => 'Delivery layer',
                'title' => 'Внедрение как путь от продукта к рабочему процессу',
                'text' => 'Продуктовая линейка отвечает на вопрос, что именно запускать. Внедрение отвечает на вопрос, как безопасно довести это до данных, интеграций, пользователей и production-контроля.',
                'theme' => 'gray',
                'blocks' => [
                    $this->block('platform', 'product_card', 'Platform assessment', 'Проверяем, нужен ли общий AI-контур: модели, RAG, инструменты, доступы, аудит и эксплуатация.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'meta' => 'product=platform']),
                    $this->block('agents', 'product_card', 'Agents pilot', 'Выбираем 1-2 ассистента, готовим документы и сценарии, подключаем безопасный handoff к команде.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'meta' => 'product=agents']),
                    $this->block('dev', 'product_card', 'Dev workflow', 'Описываем профиль команды, knowledge layer, design token rules и quality gates для AI-разработки.', ['icon' => 'ri-code-box-line', 'href' => '/dev/', 'meta' => 'product=dev']),
                    $this->block('forum', 'product_card', 'Forum launch', 'Разбираем поток обращений, проектируем сценарный граф, LLM-обогащение, аналитику и журнал диалогов.', ['icon' => 'ri-customer-service-2-line', 'href' => '/forum/', 'meta' => 'product=forum']),
                ],
            ],
            [
                'page' => '/services/',
                'section_key' => 'process',
                'sort' => 400,
                'template_key' => 'step-list',
                'fallback_partial' => 'local/components/tacticum/services.page/templates/.default/parts/process.php',
                'owner_scope' => 'marketing',
                'title' => 'Как мы доводим AI-инициативу до запуска',
                'text' => 'Процесс помогает не покупать технологию ради технологии: сначала проверяем ценность и данные, затем собираем понятный scope, команду, интеграции и план внедрения.',
                'blocks' => [
                    $this->block('step-1', 'step', 'Discovery', 'Фиксируем цель, процесс, данные, риски и критерии готовности', ['value' => '1']),
                    $this->block('step-2', 'step', 'Архитектура', 'Проектируем решение, интеграции, роли команды и этапы поставки', ['value' => '2']),
                    $this->block('step-3', 'step', 'Разработка', 'Собираем MVP или production-модуль короткими управляемыми итерациями', ['value' => '3']),
                    $this->block('step-4', 'step', 'Внедрение', 'Подключаем к системам, обучаем пользователей и настраиваем контроль качества', ['value' => '4']),
                    $this->block('step-5', 'step', 'Развитие', 'Измеряем эффект, дорабатываем сценарии и масштабируем решение', ['value' => '5']),
                ],
            ],
            [
                'page' => '/services/',
                'section_key' => 'tech',
                'sort' => 700,
                'template_key' => 'tech-grid',
                'fallback_partial' => 'local/components/tacticum/services.page/templates/.default/parts/tech.php',
                'owner_scope' => 'marketing',
                'title' => 'Технологии, с которыми мы работаем',
                'text' => 'Мы используем передовые технологии и инструменты для создания эффективных AI-решений',
                'blocks' => [
                    $this->block('ml', 'tech_card', 'Машинное обучение', 'TensorFlow, PyTorch, scikit-learn', ['icon' => 'ri-robot-line']),
                    $this->block('nlp', 'tech_card', 'Обработка языка', 'BERT, GPT, NLTK, spaCy', ['icon' => 'ri-chat-3-line']),
                    $this->block('cv', 'tech_card', 'Компьютерное зрение', 'OpenCV, YOLO, ResNet', ['icon' => 'ri-eye-line']),
                    $this->block('big-data', 'tech_card', 'Большие данные', 'Hadoop, Spark, Kafka', ['icon' => 'ri-database-2-line']),
                    $this->block('infrastructure', 'tech_card', 'Инфраструктура', 'Контейнеры, хранилища, очереди и runtime-сервисы', ['icon' => 'ri-cloud-line']),
                    $this->block('languages', 'tech_card', 'Языки программирования', 'Python, Java, JavaScript', ['icon' => 'ri-code-s-slash-line']),
                    $this->block('devops', 'tech_card', 'DevOps', 'Docker, Kubernetes, CI/CD', ['icon' => 'ri-settings-line']),
                    $this->block('visualization', 'tech_card', 'Визуализация данных', 'Tableau, Power BI, D3.js', ['icon' => 'ri-dashboard-line']),
                ],
            ],
            [
                'page' => '/price/',
                'section_key' => 'features',
                'sort' => 200,
                'template_key' => 'feature-card-grid',
                'fallback_partial' => 'local/components/tacticum/price.page/templates/.default/parts/features.php',
                'owner_scope' => 'marketing',
                'theme' => 'gray',
                'blocks' => [
                    $this->block('composition', 'feature_card', 'Состав под задачу', 'Подбираем роли под конкретный этап: discovery, MVP, интеграции, support или релизный рывок.', ['icon' => 'ri-time-line']),
                    $this->block('fast-start', 'feature_card', 'Быстрый старт работы', 'После согласования scope и доступов подключаем специалистов короткими управляемыми итерациями.', ['icon' => 'ri-rocket-line']),
                    $this->block('transparent-rates', 'feature_card', 'Прозрачные ставки', 'Видите ставку, уровень и примерный месячный бюджет до того, как оставите заявку.', ['icon' => 'ri-price-tag-3-line']),
                    $this->block('scale', 'feature_card', 'Масштабирование команды', 'Можно начать с узкого состава и расширять его по мере появления задач и данных.', ['icon' => 'ri-scales-3-line']),
                    $this->block('contract', 'feature_card', 'Договор и понятная зона ответственности', 'Фиксируем формат работы, коммуникации, отчетность и ожидаемый результат этапа.', ['icon' => 'ri-file-paper-2-line']),
                ],
            ],
            [
                'page' => '/price/',
                'section_key' => 'workstreams',
                'sort' => 300,
                'template_key' => 'product-card-grid',
                'fallback_partial' => 'local/components/tacticum/price.page/templates/.default/parts/workstreams.php',
                'owner_scope' => 'marketing',
                'eyebrow' => 'Product workstreams',
                'title' => 'Команда под продуктовый пилот или delivery-этап',
                'text' => 'Страница команды не становится страницей лицензий на продукты. Это по-прежнему способ оценить роли, загрузку и старт команды для внедрения Platform, Agents, Dev, Forum или отдельной AI-интеграции.',
                'blocks' => [
                    $this->block('platform-team', 'product_card', 'Platform team', 'Архитектор, backend, data/RAG, integration, QA и DevOps для платформенного assessment или пилота.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'meta' => 'product=platform']),
                    $this->block('agents-pilot', 'product_card', 'Agents pilot', 'Аналитик, prompt/RAG, backend, integration и QA для запуска ассистента в одном подразделении.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'meta' => 'product=agents']),
                    $this->block('dev-workflow', 'product_card', 'Dev workflow', 'Engineering lead, архитектор, design system owner, QA и DevOps для пилота AI-assisted процесса.', ['icon' => 'ri-code-box-line', 'href' => '/dev/', 'meta' => 'product=dev']),
                    $this->block('forum-launch', 'product_card', 'Forum launch', 'CX-аналитик, сценарист, backend, integration, QA и PM для первого потока обращений.', ['icon' => 'ri-customer-service-2-line', 'href' => '/forum/', 'meta' => 'product=forum']),
                ],
            ],
            [
                'page' => '/contacts/',
                'section_key' => 'routing',
                'sort' => 200,
                'template_key' => 'routing-card-grid',
                'fallback_partial' => 'local/components/tacticum/contacts.page/templates/.default/parts/routing.php',
                'owner_scope' => 'marketing',
                'eyebrow' => 'Куда направить обращение',
                'title' => 'Выберите ближайший следующий шаг',
                'text' => 'Если вы пока не уверены, с чего начать, опишите задачу в форме ниже. Мы маршрутизируем обращение: к продуктовой консультации, delivery-команде, оценке проекта или подбору специалистов.',
                'blocks' => [
                    $this->block('product-pilot', 'routing_card', 'Продуктовый пилот', 'Platform, Agents, Dev или Forum: поможем выбрать продуктовый вход и формат проверки.', ['icon' => 'ri-stack-line', 'href' => '/platform/']),
                    $this->block('implementation', 'routing_card', 'Внедрение', 'Discovery, архитектура, интеграции, запуск и развитие AI-решения в ваших процессах.', ['icon' => 'ri-route-line', 'href' => '/services/']),
                    $this->block('project-estimate', 'routing_card', 'Оценка проекта', 'Быстрый ориентир по бюджету, срокам, ролям, рискам и следующему шагу.', ['icon' => 'ri-calculator-line', 'href' => '/calculator/']),
                    $this->block('team', 'routing_card', 'Команда', 'Роли, уровни, загрузка и заявка на подключение специалистов под задачу.', ['icon' => 'ri-team-line', 'href' => '/price/']),
                ],
            ],
            [
                'page' => '/contacts/',
                'section_key' => 'cards',
                'sort' => 300,
                'template_key' => 'contact-card-grid',
                'fallback_partial' => 'local/components/tacticum/contacts.page/templates/.default/parts/cards.php',
                'owner_scope' => 'marketing',
                'blocks' => [
                    $this->block('phone', 'contact_card', 'Телефон', 'Мы доступны с понедельника по пятницу с 9:00 до 18:00 по московскому времени', ['icon' => 'ri-phone-line', 'href' => 'tel:+74955612084', 'label' => '+7 (495) 561-20-84']),
                    $this->block('email', 'contact_card', 'Email', 'Отправьте нам email, и мы ответим вам в течение 24 часов в рабочие дни', ['icon' => 'ri-mail-line', 'href' => 'mailto:project@tacticum.ru', 'label' => 'project@tacticum.ru']),
                    $this->block('office', 'contact_card', 'Офис', 'Офисная карточка использует contact page config для названия площадки, ориентира и юридического адреса.', ['icon' => 'ri-map-pin-line', 'href' => '#map', 'label' => 'Показать на карте', 'meta' => 'source=contact_config']),
                ],
            ],
            [
                'page' => '/offer/',
                'section_key' => 'product-bridge',
                'sort' => 200,
                'template_key' => 'product-card-grid',
                'fallback_partial' => 'local/components/tacticum/offer.catalog/templates/.default/parts/product-bridge.php',
                'owner_scope' => 'marketing',
                'eyebrow' => 'Proof layer',
                'title' => 'Используйте примеры как мост к продуктовой архитектуре',
                'text' => 'Каталог расчетов остается proof и estimate layer. Он помогает найти похожую задачу, а затем связать ее с подходящим продуктовым входом: Platform, Agents, Dev или Forum.',
                'blocks' => [
                    $this->block('platform-examples', 'product_card', 'Platform examples', 'RAG, LLM Gateway, доступы, интеграции и общий AI-контур для нескольких сценариев.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'meta' => 'product=platform']),
                    $this->block('agents-examples', 'product_card', 'Agents examples', 'Ассистенты для внутренних функций, базы знаний, поддержки, документов и типовых запросов.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'meta' => 'product=agents']),
                    $this->block('dev-examples', 'product_card', 'Dev examples', 'Пилоты для инженерных команд: workflow, knowledge layer, rules и quality gates.', ['icon' => 'ri-code-box-line', 'href' => '/dev/', 'meta' => 'product=dev']),
                    $this->block('forum-examples', 'product_card', 'Forum examples', 'Клиентские диалоги, сценарные графы, LLM-обогащение, аналитика и эскалации.', ['icon' => 'ri-customer-service-2-line', 'href' => '/forum/', 'meta' => 'product=forum']),
                ],
            ],
            [
                'page' => '/offer/',
                'section_key' => 'bottom-cta',
                'sort' => 700,
                'template_key' => 'cta-band',
                'fallback_partial' => 'local/components/tacticum/offer.catalog/templates/.default/parts/bottom-cta.php',
                'owner_scope' => 'marketing',
                'title' => 'Нашли похожую задачу?',
                'text' => 'Откройте карточку, перенесите ее контекст в заявку или начните с AI-калькулятора. Мы уточним отрасль, ограничения, интеграции и подготовим следующий шаг к точной смете.',
                'cta_text' => 'Начать точную оценку',
                'cta_href' => '/calculator/',
                'theme' => 'gray',
                'blocks' => [],
            ],
        ];
    }
}

function tacticum_content_storage_page_content_seed_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-page-content-seed.php [--wave=wave_1] [--apply] [--json] [--document-root=/path/to/site]

Seeds structured page_sections/page_blocks rows for generic page sections in
shadow mode. Dry-run is the default. The tool does not change public runtime,
does not retire PHP fallback partials and does not print raw page copy in logs.

TEXT;
}

$apply = false;
$json = false;
$wave = 'wave_1';
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) && trim((string)$_SERVER['DOCUMENT_ROOT']) !== ''
    ? (string)$_SERVER['DOCUMENT_ROOT']
    : dirname(__DIR__);

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        echo tacticum_content_storage_page_content_seed_usage();
        exit(0);
    }
    if ($argument === '--apply') {
        $apply = true;
        continue;
    }
    if ($argument === '--json') {
        $json = true;
        continue;
    }
    if (str_starts_with($argument, '--wave=')) {
        $wave = substr($argument, strlen('--wave='));
        continue;
    }
    if (str_starts_with($argument, '--document-root=')) {
        $documentRoot = substr($argument, strlen('--document-root='));
        continue;
    }

    fwrite(STDERR, "Unknown argument: {$argument}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_page_content_seed_usage());
    exit(2);
}

try {
    $tool = new TacticumContentStoragePageContentSeed($apply, $json, $wave, $documentRoot);
    exit($tool->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_page_content_seed_usage());
    exit(1);
}
