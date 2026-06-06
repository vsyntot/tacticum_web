#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStorageFaqMigration
{
    private bool $apply;
    private bool $updateExisting;
    private string $documentRoot;

    public function __construct(bool $apply, bool $updateExisting, string $documentRoot)
    {
        $this->apply = $apply;
        $this->updateExisting = $updateExisting;
        $this->documentRoot = rtrim($documentRoot, '/');
    }

    public function run(): void
    {
        $this->bootstrap();

        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Bitrix iblock module is unavailable.');
        }

        $productsIblockId = $this->iblockId('products');
        $faqIblockId = $this->iblockId('faq');
        if ($productsIblockId <= 0 || $faqIblockId <= 0) {
            throw new RuntimeException('Missing products or faq iblock config key.');
        }
        if ($this->propertyId($faqIblockId, 'PRODUCT') <= 0) {
            throw new RuntimeException('FAQ iblock has no PRODUCT relation property. Run product-content-migration first.');
        }

        $created = 0;
        $updated = 0;
        $existing = 0;
        $skipped = 0;
        $sectionsCreated = 0;
        $sectionsExisting = 0;
        $sectionLinks = 0;

        $rootSectionId = $this->ensureSection(
            $faqIblockId,
            0,
            'products',
            'Product FAQ',
            900,
            $sectionsCreated,
            $sectionsExisting
        );

        $productIndex = 0;
        foreach ($this->seedFiles() as $productCode => $relativeFile) {
            $productIndex++;
            $productId = $this->findElementId($productsIblockId, (string)$productCode);
            if ($productId <= 0) {
                $this->line("Skip {$productCode}: product element is missing.");
                $skipped++;
                continue;
            }

            $productSectionId = $this->ensureSection(
                $faqIblockId,
                $rootSectionId,
                (string)$productCode,
                $this->productSectionName((string)$productCode),
                $productIndex * 100,
                $sectionsCreated,
                $sectionsExisting
            );

            $data = $this->loadSeedData((string)$relativeFile);
            $items = is_array($data['faq']['items'] ?? null) ? $data['faq']['items'] : [];
            if (empty($items)) {
                $this->line("Skip {$productCode}: seed FAQ is empty.");
                $skipped++;
                continue;
            }

            $sort = 100;
            foreach ($items as $index => $item) {
                if (!is_array($item)) {
                    $skipped++;
                    continue;
                }

                $question = $this->stringValue($item['question'] ?? '');
                $answer = $this->stringValue($item['answer'] ?? '');
                if ($question === '' || $answer === '') {
                    $skipped++;
                    continue;
                }

                $code = $productCode . '-product-faq-' . ((int)$index + 1);
                $fields = [
                    'IBLOCK_ID' => $faqIblockId,
                    'ACTIVE' => 'Y',
                    'NAME' => $question,
                    'CODE' => $code,
                    'XML_ID' => 'tacticum-' . $code,
                    'SORT' => $sort,
                    'DETAIL_TEXT' => $answer,
                    'DETAIL_TEXT_TYPE' => 'text',
                ];
                if ($productSectionId > 0) {
                    $fields['IBLOCK_SECTION_ID'] = $productSectionId;
                }
                $properties = [
                    'PRODUCT' => $productId,
                ];

                $existingId = $this->findElementId($faqIblockId, $code);
                if ($existingId > 0) {
                    if ($this->updateExisting) {
                        $this->updateElement($faqIblockId, $existingId, $fields, $properties, "Update FAQ {$code}");
                        $updated++;
                    } else {
                        $this->line("FAQ exists: {$code} (#{$existingId})");
                        $existing++;
                    }
                    if ($productSectionId > 0 && $this->ensureElementSection($existingId, $productSectionId, "Link FAQ {$code} to section {$productCode}")) {
                        $sectionLinks++;
                    }
                } else {
                    $id = $this->createElement($fields, $properties, "Create FAQ {$code}");
                    if ($id > 0 || !$this->apply) {
                        $created++;
                    }
                }

                $sort += 100;
            }
        }

        $this->line('');
        $this->line('FAQ migration summary:'
            . ' created=' . $created
            . ', updated=' . $updated
            . ', existing=' . $existing
            . ', skipped=' . $skipped
            . ', sections_created=' . $sectionsCreated
            . ', sections_existing=' . $sectionsExisting
            . ', section_links=' . $sectionLinks
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

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id($key) : 0;
    }

    private function seedFiles(): array
    {
        if (function_exists('tacticum_product_content_codes')) {
            return tacticum_product_content_codes();
        }

        return [
            'platform' => 'platform.php',
            'agents' => 'agents.php',
            'dev' => 'dev.php',
            'forum' => 'forum.php',
        ];
    }

    private function loadSeedData(string $relativeFile): array
    {
        $path = $this->documentRoot . '/local/php_interface/include/product_data/' . basename($relativeFile);
        if (!is_file($path)) {
            return [];
        }

        $data = require $path;

        return is_array($data) ? $data : [];
    }

    private function findElementId(int $iblockId, string $code): int
    {
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                '=CODE' => $code,
                'CHECK_PERMISSIONS' => 'N',
            ],
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

    private function ensureSection(
        int $iblockId,
        int $parentSectionId,
        string $code,
        string $name,
        int $sort,
        int &$created,
        int &$existing
    ): int {
        $sectionId = $this->findSectionId($iblockId, $code);
        if ($sectionId > 0) {
            $this->line("FAQ section exists: {$code} (#{$sectionId})");
            $existing++;
            return $sectionId;
        }

        $fields = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'NAME' => $name,
            'CODE' => $code,
            'XML_ID' => 'tacticum-faq-section-' . $code,
            'SORT' => $sort,
        ];
        if ($parentSectionId > 0) {
            $fields['IBLOCK_SECTION_ID'] = $parentSectionId;
        }

        $sectionId = $this->createSection($fields, "Create FAQ section {$code}");
        if ($sectionId > 0 || !$this->apply) {
            $created++;
        }

        return $sectionId;
    }

    private function findSectionId(int $iblockId, string $code): int
    {
        $result = CIBlockSection::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                '=CODE' => $code,
            ],
            false,
            ['ID']
        );
        $section = $result->Fetch();

        return is_array($section) ? (int)$section['ID'] : 0;
    }

    private function createSection(array $fields, string $message): int
    {
        $createdId = $this->action($message, static function () use ($fields, $message): int {
            $section = new CIBlockSection();
            $id = (int)$section->Add($fields);
            if ($id <= 0) {
                throw new RuntimeException($message . ': ' . (string)$section->LAST_ERROR);
            }

            return $id;
        });

        return is_int($createdId) ? $createdId : 0;
    }

    private function ensureElementSection(int $elementId, int $sectionId, string $message): bool
    {
        $sectionIds = $this->elementSectionIds($elementId);
        if (in_array($sectionId, $sectionIds, true)) {
            return false;
        }

        $this->action($message, static function () use ($elementId, $sectionId, $sectionIds): void {
            $nextSectionIds = array_values(array_unique(array_merge($sectionIds, [$sectionId])));
            CIBlockElement::SetElementSection($elementId, $nextSectionIds);
        });

        return true;
    }

    private function elementSectionIds(int $elementId): array
    {
        $result = CIBlockElement::GetElementGroups($elementId, true, ['ID']);
        $ids = [];
        while ($section = $result->Fetch()) {
            $id = (int)($section['ID'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function productSectionName(string $productCode): string
    {
        return match ($productCode) {
            'platform' => 'Platform',
            'agents' => 'Agents',
            'dev' => 'Dev',
            'forum' => 'Forum',
            default => ucfirst($productCode),
        };
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
            unset($updateFields['IBLOCK_ID'], $updateFields['CODE'], $updateFields['XML_ID'], $updateFields['IBLOCK_SECTION_ID']);
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
}

function tacticum_content_storage_faq_migration_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-faq-migration.php [--apply] [--update-existing] [--document-root=/path/to/site]

Seeds product FAQ items from product_data/*.php into the configured faq iblock
with PRODUCT relation. Default mode is dry-run and does not print raw FAQ text.

TEXT;
}

function tacticum_content_storage_faq_migration_options(array $argv): array
{
    $options = [
        'apply' => false,
        'update_existing' => false,
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
        if ($argument === '--update-existing') {
            $options['update_existing'] = true;
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
    $options = tacticum_content_storage_faq_migration_options($argv);
    if ($options['help']) {
        echo tacticum_content_storage_faq_migration_usage();
        exit(0);
    }

    $migration = new TacticumContentStorageFaqMigration(
        (bool)$options['apply'],
        (bool)$options['update_existing'],
        (string)$options['document_root']
    );
    $migration->run();
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
