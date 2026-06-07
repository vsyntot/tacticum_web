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

        $sections = $this->selectedSections();
        if ($sections === []) {
            $this->errors[] = 'Unsupported page-content seed wave: ' . $this->wave . '. Use wave_1, wave_2 or all.';
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

        foreach ($sections as $section) {
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
        $migrationStatus = 'shadow';
        if ($sectionId > 0) {
            $existingStatus = $this->propertyString($sectionsIblockId, $sectionId, 'MIGRATION_STATUS');
            if ($existingStatus !== '') {
                $migrationStatus = $existingStatus;
            }
        }
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
            'MIGRATION_STATUS' => $migrationStatus,
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

    private function propertyString(int $iblockId, int $elementId, string $code): string
    {
        $result = CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc', 'id' => 'asc'], ['CODE' => $code]);
        $property = $result->Fetch();
        $value = is_array($property) ? ($property['VALUE'] ?? '') : '';
        if (is_array($value)) {
            $value = reset($value);
        }

        return trim((string)$value);
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

    private function selectedSections(): array
    {
        return match ($this->wave) {
            'wave_1' => $this->waveOneSections(),
            'wave_2' => $this->waveTwoSections(),
            'all' => array_merge($this->waveOneSections(), $this->waveTwoSections()),
            default => [],
        };
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
                'eyebrow' => 'Слой внедрения',
                'title' => 'Внедрение как путь от продукта к рабочему процессу',
                'text' => 'Продуктовая линейка отвечает на вопрос, что именно запускать. Внедрение отвечает на вопрос, как безопасно довести это до данных, интеграций, пользователей и production-контроля.',
                'theme' => 'gray',
                'blocks' => [
                    $this->block('platform', 'product_card', 'Предпроектная оценка Platform', 'Проверяем, нужен ли общий AI-контур: модели, RAG, инструменты, доступы, аудит и эксплуатация.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'meta' => 'product=platform']),
                    $this->block('agents', 'product_card', 'Пилот Agents', 'Выбираем 1-2 ассистента, готовим документы и сценарии, подключаем безопасную передачу к команде.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'meta' => 'product=agents']),
                    $this->block('dev', 'product_card', 'Процесс Dev', 'Описываем профиль команды, слой знаний, правила дизайн-токенов и проверки качества для AI-разработки.', ['icon' => 'ri-code-box-line', 'href' => '/dev/', 'meta' => 'product=dev']),
                    $this->block('forum', 'product_card', 'Запуск Forum', 'Разбираем поток обращений, проектируем сценарный граф, LLM-обогащение, аналитику и журнал диалогов.', ['icon' => 'ri-customer-service-2-line', 'href' => '/forum/', 'meta' => 'product=forum']),
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
                'text' => 'Показываем не список модных инструментов, а контуры, которые проверяем перед запуском корпоративного AI-решения.',
                'blocks' => [
                    $this->block('ml', 'tech_card', 'Машинное обучение', 'TensorFlow, PyTorch, scikit-learn', ['icon' => 'ri-robot-line']),
                    $this->block('nlp', 'tech_card', 'Поиск по знаниям', 'LLM, RAG, векторный поиск и контроль источников', ['icon' => 'ri-chat-3-line']),
                    $this->block('cv', 'tech_card', 'Компьютерное зрение', 'OpenCV, YOLO, ResNet', ['icon' => 'ri-eye-line']),
                    $this->block('big-data', 'tech_card', 'Данные и события', 'Хранилища, очереди и потоковая обработка', ['icon' => 'ri-database-2-line']),
                    $this->block('infrastructure', 'tech_card', 'Инфраструктура', 'Контейнеры, хранилища, очереди и сервисы запуска', ['icon' => 'ri-cloud-line']),
                    $this->block('languages', 'tech_card', 'Языки программирования', 'Python, PHP, JavaScript и интеграционный код', ['icon' => 'ri-code-s-slash-line']),
                    $this->block('devops', 'tech_card', 'DevOps', 'Docker, Kubernetes, CI/CD', ['icon' => 'ri-settings-line']),
                    $this->block('visualization', 'tech_card', 'Аналитика', 'Дашборды, события, воронки и отчетность', ['icon' => 'ri-dashboard-line']),
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
                    $this->block('fast-start', 'feature_card', 'Быстрый старт работы', 'После согласования границ работ и доступов подключаем специалистов короткими управляемыми итерациями.', ['icon' => 'ri-rocket-line']),
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
                'eyebrow' => 'Команда под этап внедрения',
                'title' => 'Команда под продуктовый пилот или этап внедрения',
                'text' => 'Страница команды не становится страницей лицензий на продукты. Это по-прежнему способ оценить роли, загрузку и старт команды для внедрения Platform, Agents, Dev, Forum или отдельной AI-интеграции.',
                'blocks' => [
                    $this->block('platform-team', 'product_card', 'Команда Platform', 'Архитектор, серверная разработка, данные и поиск по базе знаний, интеграции, QA и DevOps для платформенной предпроектной оценки или пилота.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'meta' => 'product=platform']),
                    $this->block('agents-pilot', 'product_card', 'Пилот Agents', 'Аналитик, настройка промптов и поиска по знаниям, серверная разработка, интеграции и QA для запуска ассистента в одном подразделении.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'meta' => 'product=agents']),
                    $this->block('dev-workflow', 'product_card', 'Процесс Dev', 'Engineering lead, архитектор, владелец дизайн-системы, QA и DevOps для пилота AI-assisted процесса.', ['icon' => 'ri-code-box-line', 'href' => '/dev/', 'meta' => 'product=dev']),
                    $this->block('forum-launch', 'product_card', 'Запуск Forum', 'CX-аналитик, сценарист, серверная разработка, интеграции, QA и PM для первого потока обращений.', ['icon' => 'ri-customer-service-2-line', 'href' => '/forum/', 'meta' => 'product=forum']),
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
                'eyebrow' => 'Слой подтверждений',
                'title' => 'Используйте примеры как мост к продуктовой архитектуре',
                'text' => 'Каталог расчетов остается proof и estimate layer. Он помогает найти похожую задачу, а затем связать ее с подходящим продуктовым входом: Platform, Agents, Dev или Forum.',
                'blocks' => [
                    $this->block('platform-examples', 'product_card', 'Примеры Platform', 'RAG, LLM Gateway, доступы, интеграции и общий AI-контур для нескольких сценариев.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'meta' => 'product=platform']),
                    $this->block('agents-examples', 'product_card', 'Примеры Agents', 'Ассистенты для внутренних функций, базы знаний, поддержки, документов и типовых запросов.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'meta' => 'product=agents']),
                    $this->block('dev-examples', 'product_card', 'Примеры Dev', 'Пилоты для инженерных команд: процесс, слой знаний, правила и проверки качества.', ['icon' => 'ri-code-box-line', 'href' => '/dev/', 'meta' => 'product=dev']),
                    $this->block('forum-examples', 'product_card', 'Примеры Forum', 'Клиентские диалоги, сценарные графы, LLM-обогащение, аналитика и эскалации.', ['icon' => 'ri-customer-service-2-line', 'href' => '/forum/', 'meta' => 'product=forum']),
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

    private function waveTwoSections(): array
    {
        return [
            [
                'page' => '/',
                'section_key' => 'ecosystem',
                'sort' => 200,
                'template_key' => 'product-card-grid',
                'fallback_partial' => 'local/components/tacticum/home.page/templates/.default/parts/ecosystem.php',
                'owner_scope' => 'marketing',
                'eyebrow' => 'Экосистема',
                'title' => 'Общее AI-ядро и прикладные продукты поверх него',
                'text' => 'Продуктовая модель Tacticum строится вокруг одной архитектуры: Platform отвечает за runtime, модели, знания, инструменты и контроль, а Agents, Dev и Forum решают прикладные задачи разных команд.',
                'blocks' => [
                    $this->block('platform', 'product_card', 'Tacticum Platform', 'Единый слой для LLM Gateway, RAG, памяти, MCP-инструментов, RBAC, аудита, observability и контроля стоимости.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'meta' => 'product=platform']),
                    $this->block('agents', 'product_card', 'Tacticum Agents', 'Корпоративные ассистенты для HR, юридического, бухгалтерии, поддержки, IT helpdesk и базы знаний.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'meta' => 'product=agents']),
                    $this->block('dev', 'product_card', 'Tacticum Dev', 'Контур управления AI-assisted разработкой: профили, знания, дизайн-токены и проверки качества.', ['icon' => 'ri-git-branch-line', 'href' => '/dev/', 'meta' => 'product=dev']),
                    $this->block('forum', 'product_card', 'Tacticum Forum', 'Управляемые клиентские диалоги: сценарные графы, LLM-обогащение, аналитика и журналирование.', ['icon' => 'ri-flow-chart', 'href' => '/forum/', 'meta' => 'product=forum']),
                ],
            ],
            [
                'page' => '/',
                'section_key' => 'fit-matrix',
                'sort' => 300,
                'template_key' => 'product-card-grid',
                'fallback_partial' => 'local/components/tacticum/home.page/templates/.default/parts/fit-matrix.php',
                'owner_scope' => 'marketing',
                'eyebrow' => 'Как выбрать продукт',
                'title' => 'Начните с ситуации, а не с названия продукта',
                'text' => 'Короткая матрица разделяет платформенные, функциональные, инженерные и клиентские сценарии без обещаний результата до discovery.',
                'theme' => 'gray',
                'blocks' => [
                    $this->block('platform-fit', 'product_card', 'Единый AI-контур', 'Если AI-сценариев несколько и нужны общие RAG, модели, инструменты, доступы, audit и контроль стоимости.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'label' => 'Platform']),
                    $this->block('agents-fit', 'product_card', 'Ассистенты для функций', 'Для HR, legal, finance, support, IT helpdesk и базы знаний, где есть документы, правила и handoff к человеку.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'label' => 'Agents']),
                    $this->block('dev-fit', 'product_card', 'AI-assisted workflow', 'Для инженерных команд, которым нужно удержать architecture, review, tests и design tokens при работе с AI.', ['icon' => 'ri-git-branch-line', 'href' => '/dev/', 'label' => 'Dev']),
                    $this->block('forum-fit', 'product_card', 'Клиентские диалоги', 'Для поддержки и продаж, где нужны сценарии, LLM-уточнения, эскалации и журнал диалогов.', ['icon' => 'ri-flow-chart', 'href' => '/forum/', 'label' => 'Forum']),
                ],
            ],
            [
                'page' => '/',
                'section_key' => 'commercial',
                'sort' => 500,
                'template_key' => 'routing-card-grid',
                'fallback_partial' => 'local/components/tacticum/home.page/templates/.default/parts/commercial.php',
                'owner_scope' => 'marketing',
                'title' => 'Выберите следующий коммерческий шаг',
                'text' => 'Можно начать с оценки, внедрения, команды или быстрого AI-бота, если так проще проверить гипотезу.',
                'blocks' => [
                    $this->block('estimate', 'routing_card', 'Рассчитать проект', 'Сравните похожие расчеты по отраслям и получите базу для персональной сметы.', ['icon' => 'ri-file-search-line', 'href' => '/offer/']),
                    $this->block('delivery', 'routing_card', 'Внедрить AI-решение', 'Пройдем discovery, разработку, интеграции и запуск в существующие процессы.', ['icon' => 'ri-settings-line', 'href' => '/services/']),
                    $this->block('team', 'routing_card', 'Собрать команду', 'Подберите роли, уровни и загрузку, чтобы быстро оценить состав delivery-команды.', ['icon' => 'ri-team-line', 'href' => '/price/']),
                    $this->block('bot', 'routing_card', 'Запустить AI-бота', 'Проверьте Telegram-сценарий на демо-агентах и запросите прототип под вашу воронку.', ['icon' => 'ri-robot-2-line', 'href' => '/aiagents/']),
                ],
            ],
            [
                'page' => '/about/',
                'section_key' => 'company-trust',
                'sort' => 200,
                'template_key' => 'routing-card-grid',
                'fallback_partial' => 'local/components/tacticum/about.page/templates/.default/parts/company-trust.php',
                'owner_scope' => 'marketing',
                'eyebrow' => 'Доверие к команде',
                'title' => 'Как мы снижаем риски корпоративного AI-запуска',
                'text' => 'Перед пилотом фиксируем бизнес-сценарий, данные, интеграции, ограничения безопасности, роли команды и критерии перехода к рабочему запуску.',
                'blocks' => [
                    $this->block('scenario', 'routing_card', 'Сценарий и границы', 'Определяем, какой процесс усиливает AI, кто будет пользоваться решением и что считать результатом пилота.', ['icon' => 'ri-compass-3-line', 'href' => '/calculator/']),
                    $this->block('architecture', 'routing_card', 'Контур данных и интеграций', 'Проверяем источники знаний, доступы, внутренние системы, ограничения безопасности и будущую эксплуатацию.', ['icon' => 'ri-stack-line', 'href' => '/platform/']),
                    $this->block('risk-control', 'routing_card', 'Риски и контроль', 'Фиксируем роли, аудит, журналирование, проверки качества и правила перехода к следующему этапу.', ['icon' => 'ri-shield-check-line', 'href' => '/services/']),
                    $this->block('launch-plan', 'routing_card', 'План запуска', 'Собираем состав работ, команду, бюджетный диапазон и понятный первый шаг без лишнего объема.', ['icon' => 'ri-calculator-line', 'href' => '/price/']),
                ],
            ],
            [
                'page' => '/about/',
                'section_key' => 'values-team',
                'sort' => 400,
                'template_key' => 'feature-card-grid',
                'fallback_partial' => 'local/components/tacticum/about.page/templates/.default/parts/values-team.php',
                'owner_scope' => 'marketing',
                'title' => 'Как мы работаем',
                'text' => 'Фокусируемся на проверяемом результате: сначала уточняем задачу и ограничения, затем собираем пилот, команду и план внедрения.',
                'blocks' => [
                    $this->block('hypothesis', 'feature_card', 'Проверяем гипотезу', 'Не начинаем с большого проекта, пока не понятны сценарий, данные и критерии результата.', ['icon' => 'ri-lightbulb-line']),
                    $this->block('responsibility', 'feature_card', 'Фиксируем ответственность', 'Разделяем роли клиента и команды Tacticum: кто дает данные, кто внедряет и кто поддерживает запуск.', ['icon' => 'ri-eye-line']),
                    $this->block('constraints', 'feature_card', 'Работаем с ограничениями', 'Учитываем доступы, безопасность, интеграции, сроки и готовность пользователей к изменению процесса.', ['icon' => 'ri-shape-line']),
                ],
            ],
            [
                'page' => '/about/',
                'section_key' => 'career-final',
                'sort' => 700,
                'template_key' => 'feature-card-grid',
                'fallback_partial' => 'local/components/tacticum/about.page/templates/.default/parts/career-final.php',
                'owner_scope' => 'marketing',
                'title' => 'Начать работу с Tacticum',
                'text' => 'Коротко опишите задачу, процесс и ограничения. Мы поможем выбрать формат первого шага: оценку, пилот, интеграцию или команду под запуск.',
                'blocks' => [
                    $this->block('task', 'feature_card', 'Задача и контекст', 'Опишите процесс, пользователей, ожидаемый результат и текущие ограничения.', ['icon' => 'ri-file-list-3-fill']),
                    $this->block('data-access', 'feature_card', 'Данные и доступы', 'Понимаем, какие источники знаний, системы и правила безопасности нужны для пилота.', ['icon' => 'ri-lock-2-fill']),
                    $this->block('first-step', 'feature_card', 'Первый шаг', 'Предложим оценку, прототип, пилот или состав команды без лишнего объема работ.', ['icon' => 'ri-route-fill']),
                    $this->block('team-scope', 'feature_card', 'Команда под запуск', 'Соберем роли и ответственность под этап: аналитика, разработка, интеграции, QA и сопровождение.', ['icon' => 'ri-team-fill']),
                ],
            ],
            [
                'page' => '/calculator/',
                'section_key' => 'calculator-outcome-cards',
                'sort' => 200,
                'template_key' => 'calculator-chat-outcome',
                'fallback_partial' => 'local/components/tacticum/calculator.page/templates/.default/template.php',
                'owner_scope' => 'marketing',
                'title' => 'Что вы получите после диалога',
                'text' => 'AI-калькулятор формирует предварительный артефакт оценки: бюджетный диапазон, сроки, команду, риски и вопросы для уточнения.',
                'blocks' => [
                    $this->block('budget', 'feature_card', 'Бюджетный диапазон', 'Предварительная вилка бюджета с пояснением, какие блоки влияют на стоимость.', ['icon' => 'ri-money-dollar-circle-line']),
                    $this->block('timeline', 'feature_card', 'Сроки и этапы', 'Черновой план: discovery, MVP, интеграции, тестирование и запуск.', ['icon' => 'ri-calendar-check-line']),
                    $this->block('team', 'feature_card', 'Состав команды', 'Роли, которые обычно нужны для такого проекта: аналитик, backend, ML, QA, PM и другие.', ['icon' => 'ri-team-line']),
                    $this->block('risks', 'feature_card', 'Риски и вопросы', 'Что нужно уточнить перед точной сметой: данные, интеграции, поддержка, безопасность и нагрузка.', ['icon' => 'ri-alert-line']),
                ],
            ],
            [
                'page' => '/calculator/',
                'section_key' => 'product-aware-estimate-cards',
                'sort' => 300,
                'template_key' => 'product-card-grid',
                'fallback_partial' => 'local/components/tacticum/calculator.page/templates/.default/template.php',
                'owner_scope' => 'marketing',
                'eyebrow' => 'Оценка с учетом продукта',
                'title' => 'Что можно оценить через AI-калькулятор',
                'text' => 'Калькулятор помогает привязать задачу к продуктовой модели Tacticum: платформенному ядру, ассистентам, инженерному workflow или клиентским диалогам.',
                'theme' => 'gray',
                'blocks' => [
                    $this->block('platform', 'product_card', 'Platform', 'Оценка общего AI-контура: LLM Gateway, RAG, память, инструменты, доступы, аудит и эксплуатация.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'meta' => 'product=platform']),
                    $this->block('agents', 'product_card', 'Agents', 'Проверка ассистента для HR, юридического, бухгалтерии, поддержки, IT helpdesk или базы знаний.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'meta' => 'product=agents']),
                    $this->block('dev', 'product_card', 'Dev', 'Оценка пилота AI-assisted процесса: профили, слой знаний, правила, проверки качества и метрики.', ['icon' => 'ri-git-branch-line', 'href' => '/dev/', 'meta' => 'product=dev']),
                    $this->block('forum', 'product_card', 'Forum', 'Оценка потока обращений: сценарный граф, LLM-обогащение, аналитика, журнал и интеграции.', ['icon' => 'ri-flow-chart', 'href' => '/forum/', 'meta' => 'product=forum']),
                ],
            ],
            [
                'page' => '/aiagents/',
                'section_key' => 'agents-bridge',
                'sort' => 200,
                'template_key' => 'product-card-grid',
                'fallback_partial' => 'local/components/tacticum/aiagents/templates/.default/parts/agents-bridge.php',
                'owner_scope' => 'marketing',
                'eyebrow' => 'Tacticum Agents',
                'title' => 'AI-бот как первый сценарий корпоративных ассистентов',
                'text' => 'Страница остается быстрым входом в Telegram-бот прототип. Если задача шире одного бота, переходите к Tacticum Agents.',
                'blocks' => [
                    $this->block('prototype', 'product_card', 'Быстрый прототип', 'Проверить диалог, вопросы квалификации и handoff менеджеру.', ['icon' => 'ri-telegram-line', 'href' => '#demo']),
                    $this->block('agents-pilot', 'product_card', 'Пилот Agents', 'Запустить ассистента с документами, правилами доступа и интеграциями.', ['icon' => 'ri-robot-2-line', 'href' => '/agents/', 'meta' => 'product=agents']),
                    $this->block('platform-path', 'product_card', 'Путь к Platform', 'Вынести RAG, память, инструменты и аудит в общий AI-контур.', ['icon' => 'ri-stack-line', 'href' => '/platform/', 'meta' => 'product=platform']),
                ],
            ],
            [
                'page' => '/aiagents/',
                'section_key' => 'how-it-works',
                'sort' => 400,
                'template_key' => 'step-list',
                'fallback_partial' => 'local/components/tacticum/aiagents/templates/.default/parts/how-it-works.php',
                'owner_scope' => 'marketing',
                'title' => 'Как бот переходит из демо в рабочий сценарий',
                'blocks' => [
                    $this->block('scenario', 'step', 'Опишите бизнес-сценарий', 'Какие продукты продаете, кто клиент, какие вопросы бот должен закрывать.', ['value' => '1']),
                    $this->block('dialog', 'step', 'Проверьте диалог', 'AI-ассистент собирает черновой сценарий и показывает, как будет отвечать клиентам.', ['value' => '2']),
                    $this->block('implementation', 'step', 'Решите, что внедрять', 'После демо можно запросить прототип, CRM-интеграцию или полноценный проект внедрения.', ['value' => '3']),
                ],
            ],
            [
                'page' => '/aiagents/',
                'section_key' => 'services',
                'sort' => 600,
                'template_key' => 'tech-grid',
                'fallback_partial' => 'local/components/tacticum/aiagents/templates/.default/parts/services.php',
                'owner_scope' => 'marketing',
                'title' => 'Где AI-бот становится частью B2B-процесса',
                'blocks' => [
                    $this->block('qualification', 'tech_card', 'Сценарии квалификации', 'Опишем вопросы, развилки и критерии передачи лида менеджеру.', ['icon' => 'ri-attachment-2']),
                    $this->block('knowledge', 'tech_card', 'AI-логика и знания', 'Настроим ответы на основе услуг, документов, FAQ и ограничений бренда.', ['icon' => 'ri-tools-line']),
                    $this->block('tone', 'tech_card', 'Тон и правила общения', 'Согласуем стиль, допустимые обещания, стоп-темы и передачу сложных вопросов человеку.', ['icon' => 'ri-robot-2-line']),
                    $this->block('testing', 'tech_card', 'Тестирование и запуск', 'Проверим диалоги, лид-формы, Telegram-сценарии и корректность передачи данных.', ['icon' => 'ri-flask-line']),
                    $this->block('analytics', 'tech_card', 'Аналитика и развитие', 'Смотрим, где диалог теряет клиента, и дорабатываем сценарии после запуска.', ['icon' => 'ri-bar-chart-2-line']),
                    $this->block('implementation-link', 'tech_card', 'Связь с основным проектом', 'Если бот требует CRM, базы знаний или аналитики, подключаем команду внедрения Tacticum.', ['icon' => 'ri-lightbulb-flash-line', 'href' => '/services/']),
                ],
            ],
        ];
    }
}

function tacticum_content_storage_page_content_seed_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-page-content-seed.php [--wave=wave_1|wave_2|all] [--apply] [--json] [--document-root=/path/to/site]

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
