#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStorageServicesSeed
{
    private bool $apply;
    private string $documentRoot;

    public function __construct(bool $apply, string $documentRoot)
    {
        $this->apply = $apply;
        $this->documentRoot = rtrim($documentRoot, '/');
    }

    public function run(): void
    {
        $this->bootstrap();

        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Bitrix iblock module is unavailable.');
        }

        $servicesIblockId = $this->iblockId('services');
        if ($servicesIblockId <= 0) {
            throw new RuntimeException('Missing services iblock config key.');
        }

        foreach (['OPTIONS', 'CLASS', 'LINK', 'LINKTEXT'] as $propertyCode) {
            if ($this->propertyId($servicesIblockId, $propertyCode) <= 0) {
                throw new RuntimeException("Services iblock has no {$propertyCode} property.");
            }
        }

        $hasProductRelation = $this->propertyId($servicesIblockId, 'PRODUCT') > 0;
        if (!$hasProductRelation) {
            $this->line('Warning: services iblock has no PRODUCT relation property; product links will be skipped.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($this->services() as $service) {
            $code = $this->stringValue($service['code'] ?? '');
            if ($code === '') {
                $skipped++;
                continue;
            }

            $existingId = $this->findElementIdByCode($servicesIblockId, $code);
            if ($existingId <= 0) {
                $existingId = $this->findElementIdByLegacyNames(
                    $servicesIblockId,
                    is_array($service['legacy_names'] ?? null) ? $service['legacy_names'] : []
                );
            }

            $conflictId = $this->findElementIdByCode($servicesIblockId, $code);
            if ($existingId > 0 && $conflictId > 0 && $conflictId !== $existingId) {
                throw new RuntimeException("Cannot update {$code}: another element already uses this CODE.");
            }

            $properties = [
                'OPTIONS' => $this->stringList($service['options'] ?? []),
                'CLASS' => $this->stringValue($service['class'] ?? ''),
                'LINK' => $this->stringValue($service['link'] ?? ''),
                'LINKTEXT' => $this->stringValue($service['linktext'] ?? ''),
            ];
            if ($hasProductRelation) {
                $properties['PRODUCT'] = $this->productIds($service['product_codes'] ?? []);
            }

            $fields = [
                'IBLOCK_ID' => $servicesIblockId,
                'ACTIVE' => 'Y',
                'NAME' => $this->stringValue($service['name'] ?? ''),
                'CODE' => $code,
                'XML_ID' => 'tacticum-service-' . $code,
                'SORT' => (int)($service['sort'] ?? 500),
                'PREVIEW_TEXT' => $this->stringValue($service['preview'] ?? ''),
                'PREVIEW_TEXT_TYPE' => 'text',
                'DETAIL_TEXT' => $this->detailText($service),
                'DETAIL_TEXT_TYPE' => 'text',
            ];

            if ($existingId > 0) {
                $this->updateElement($servicesIblockId, $existingId, $fields, $properties, "Update service {$code}");
                $updated++;
            } else {
                $id = $this->createElement($fields, $properties, "Create service {$code}");
                if ($id > 0 || !$this->apply) {
                    $created++;
                }
            }
        }

        $this->line('');
        $this->line('Services seed summary:'
            . ' created=' . $created
            . ', updated=' . $updated
            . ', skipped=' . $skipped
            . ', mode=' . ($this->apply ? 'apply' : 'dry-run'));
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

    private function services(): array
    {
        return [
            [
                'code' => 'preproject-estimate-roadmap',
                'sort' => 100,
                'name' => 'Предпроектная оценка и дорожная карта',
                'preview' => 'Разбираем задачу, бюджет, сроки, команду и риски до старта разработки или пилота.',
                'class' => 'file-chart',
                'link' => '/offer/',
                'linktext' => 'Смотреть расчеты',
                'options' => [
                    'Разбор цели, отрасли, сценария и ограничений',
                    'Оценка бюджета, сроков, команды и рисков',
                    'Сравнение похожих расчетов с вашей задачей',
                    'План первого этапа и следующий шаг',
                ],
                'product_codes' => [],
            ],
            [
                'code' => 'ai-discovery-architecture',
                'sort' => 200,
                'name' => 'AI discovery и архитектура внедрения',
                'preview' => 'Проектируем контур данных, интеграций, доступа и эксплуатации для безопасного AI-внедрения.',
                'class' => 'stack',
                'link' => '/platform/',
                'linktext' => 'Разобрать архитектуру',
                'options' => [
                    'Карта систем, данных, доступов и ролей',
                    'Выбор AI-контура: RAG, модели, интеграции, аудит',
                    'Оценка production-readiness и ограничений',
                    'Архитектурная сессия для CIO, CTO или CISO',
                ],
                'product_codes' => ['platform'],
            ],
            [
                'code' => 'ai-agent-pilot',
                'sort' => 300,
                'name' => 'Пилот AI-агента на ваших данных',
                'preview' => 'Запускаем проверяемый сценарий AI-ассистента на документах, знаниях и правилах handoff.',
                'class' => 'robot-2',
                'link' => '/agents/',
                'linktext' => 'Запустить пилот',
                'options' => [
                    'Выбор 1-2 сценариев и критериев качества',
                    'Подготовка документов, базы знаний и handoff',
                    'Интеграция с CRM, сайтом, Telegram или внутренними системами',
                    'План запуска, тестирования и развития',
                ],
                'product_codes' => ['agents'],
                'legacy_names' => ['Разработка и внедрение ИИ‑агентов', 'Разработка и внедрение ИИ-агентов'],
            ],
            [
                'code' => 'customer-dialog-automation',
                'sort' => 400,
                'name' => 'Автоматизация клиентских диалогов',
                'preview' => 'Проектируем сценарии поддержки, продаж или консультаций с контролем качества и эскалациями.',
                'class' => 'customer-service-2',
                'link' => '/forum/',
                'linktext' => 'Разобрать диалоги',
                'options' => [
                    'Разбор обращений, интентов и эскалаций',
                    'Сценарный граф и LLM-обогащение ответов',
                    'Журнал диалогов, аналитика и контроль качества',
                    'Пилот для поддержки, продаж или консультаций',
                ],
                'product_codes' => ['forum'],
            ],
            [
                'code' => 'ai-it-development-integrations',
                'sort' => 500,
                'name' => 'AI/IT-разработка и интеграции',
                'preview' => 'Собираем backend, frontend, ML и интеграционный слой вокруг бизнес-процесса.',
                'class' => 'code-box',
                'link' => '/dev/',
                'linktext' => 'Обсудить разработку',
                'options' => [
                    'Backend, frontend и ML-разработка под бизнес-процесс',
                    'Интеграции с CRM, ERP, 1С и документооборотом',
                    'QA, DevOps, мониторинг и release gates',
                    'Доработка существующих систем без переписывания с нуля',
                ],
                'product_codes' => ['dev'],
            ],
            [
                'code' => 'managed-ai-it-team',
                'sort' => 600,
                'name' => 'Управляемая AI/IT-команда',
                'preview' => 'Подбираем роли и формат подключения команды под roadmap, поддержку или отдельный scope.',
                'class' => 'team',
                'link' => '/price/',
                'linktext' => 'Подобрать команду',
                'options' => [
                    'Подбор ролей под scope, сроки и загрузку',
                    'Python, ML, backend, frontend, QA и DevOps',
                    'T&M или выделенная команда под roadmap',
                    'Прозрачные ставки, состав и формат подключения',
                ],
                'product_codes' => ['dev'],
                'legacy_names' => ['Time & Material', 'Time and Material'],
            ],
        ];
    }

    private function detailText(array $service): string
    {
        $lines = [
            $this->stringValue($service['preview'] ?? ''),
            '',
            'Что входит:',
        ];
        foreach ($this->stringList($service['options'] ?? []) as $option) {
            $lines[] = '- ' . $option;
        }

        return trim(implode(PHP_EOL, $lines));
    }

    private function productIds(mixed $productCodes): array
    {
        $ids = [];
        foreach ($this->stringList($productCodes) as $productCode) {
            $productsIblockId = $this->iblockId('products');
            $productId = $productsIblockId > 0 ? $this->findElementIdByCode($productsIblockId, $productCode) : 0;
            if ($productId <= 0) {
                $this->line("Warning: product {$productCode} is missing; relation skipped.");
                continue;
            }

            $ids[] = $productId;
        }

        return array_values(array_unique($ids));
    }

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id($key) : 0;
    }

    private function findElementIdByCode(int $iblockId, string $code): int
    {
        if ($iblockId <= 0 || $code === '') {
            return 0;
        }

        return $this->findElementId($iblockId, ['=CODE' => $code]);
    }

    private function findElementIdByLegacyNames(int $iblockId, array $names): int
    {
        foreach ($this->stringList($names) as $name) {
            $id = $this->findElementId($iblockId, ['=NAME' => $name]);
            if ($id > 0) {
                return $id;
            }
        }

        return 0;
    }

    private function findElementId(int $iblockId, array $filter): int
    {
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            array_merge([
                'IBLOCK_ID' => $iblockId,
                'CHECK_PERMISSIONS' => 'N',
            ], $filter),
            false,
            ['nTopCount' => 1],
            ['ID']
        );
        $element = $result->Fetch();

        return is_array($element) ? (int)$element['ID'] : 0;
    }

    private function propertyId(int $iblockId, string $code): int
    {
        $result = CIBlockProperty::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'CODE' => $code,
            ]
        );
        $property = $result->Fetch();

        return is_array($property) ? (int)$property['ID'] : 0;
    }

    private function createElement(array $fields, array $properties, string $message): int
    {
        $createdId = $this->action($message, static function () use ($fields, $properties, $message): int {
            $element = new CIBlockElement();
            $id = (int)$element->Add(array_merge($fields, [
                'PROPERTY_VALUES' => $properties,
            ]));
            if ($id <= 0) {
                throw new RuntimeException($message . ': ' . (string)$element->LAST_ERROR);
            }

            return $id;
        });

        return is_int($createdId) ? $createdId : 0;
    }

    private function updateElement(int $iblockId, int $elementId, array $fields, array $properties, string $message): void
    {
        $this->action($message, static function () use ($iblockId, $elementId, $fields, $properties, $message): void {
            $element = new CIBlockElement();
            $updateFields = $fields;
            unset($updateFields['IBLOCK_ID']);
            if (!$element->Update($elementId, $updateFields)) {
                throw new RuntimeException($message . ': ' . (string)$element->LAST_ERROR);
            }

            CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $properties);
        });
    }

    private function action(string $message, callable $callback): mixed
    {
        if (!$this->apply) {
            $this->line('[dry-run] ' . $message);
            return null;
        }

        $this->line('[apply] ' . $message);

        return $callback();
    }

    private function line(string $message): void
    {
        echo $message . PHP_EOL;
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? trim((string)$value) : '';
    }

    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $items = [];
        foreach ($value as $item) {
            if (!is_scalar($item)) {
                continue;
            }

            $item = trim((string)$item);
            if ($item !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }
}

function tacticum_content_storage_services_seed_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-services-seed.php [--apply] [--document-root=/path/to/site]

Seeds the target services #12 catalog with six delivery packages. Default mode
is dry-run. The script updates legacy "Разработка и внедрение ИИ-агентов" and
"Time & Material" rows by name if stable codes are not present.

TEXT;
}

function tacticum_content_storage_services_seed_options(array $argv): array
{
    $options = [
        'apply' => false,
        'document_root' => dirname(__DIR__),
        'help' => false,
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $options['help'] = true;
            continue;
        }
        if ($argument === '--apply') {
            $options['apply'] = true;
            continue;
        }
        if (str_starts_with($argument, '--document-root=')) {
            $options['document_root'] = substr($argument, strlen('--document-root='));
            continue;
        }

        throw new InvalidArgumentException('Unknown argument: ' . $argument);
    }

    $documentRoot = realpath((string)$options['document_root']);
    if ($documentRoot === false) {
        throw new RuntimeException('Document root does not exist: ' . (string)$options['document_root']);
    }
    $options['document_root'] = $documentRoot;

    return $options;
}

try {
    $options = tacticum_content_storage_services_seed_options($argv);
    if ($options['help']) {
        echo tacticum_content_storage_services_seed_usage();
        exit(0);
    }

    $seed = new TacticumContentStorageServicesSeed(
        (bool)$options['apply'],
        (string)$options['document_root']
    );
    $seed->run();
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
