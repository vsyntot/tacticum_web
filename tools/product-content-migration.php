#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

const TACTICUM_PRODUCT_CONTENT_IBLOCK_TYPE = 'tacticum_content';

final class TacticumProductContentMigration
{
    private bool $apply;
    private bool $updateSeedContent;
    private string $documentRoot;
    private array $siteIds = [];

    public function __construct(bool $apply, bool $updateSeedContent, string $documentRoot)
    {
        $this->apply = $apply;
        $this->updateSeedContent = $updateSeedContent;
        $this->documentRoot = rtrim($documentRoot, '/');
    }

    public function run(): void
    {
        $this->siteIds = $this->siteIds();

        $this->ensureIblockType();

        $productsIblockId = $this->ensureIblock('tacticum_products', 'Tacticum Products', 100);
        $blocksIblockId = $this->ensureIblock('tacticum_product_blocks', 'Tacticum Product Blocks', 110);
        $useCasesIblockId = $this->ensureIblock('tacticum_product_use_cases', 'Tacticum Product Use Cases', 120);

        if ($productsIblockId > 0) {
            $this->ensureProductsProperties($productsIblockId);
        }
        if ($productsIblockId > 0 && $blocksIblockId > 0) {
            $this->ensureProductBlocksProperties($blocksIblockId, $productsIblockId);
        }
        if ($productsIblockId > 0 && $useCasesIblockId > 0) {
            $this->ensureProductUseCasesProperties($useCasesIblockId, $productsIblockId);
        }
        if ($productsIblockId > 0) {
            $this->ensureExistingIblockProductRelations($productsIblockId);
        }

        if ($productsIblockId > 0 && $blocksIblockId > 0 && $useCasesIblockId > 0) {
            $this->seedProducts($productsIblockId, $blocksIblockId, $useCasesIblockId);
        } else {
            $this->line('Seed skipped until all product iblocks exist.');
        }

        $this->printConfigHints($productsIblockId, $blocksIblockId, $useCasesIblockId);
    }

    private function line(string $message): void
    {
        echo $message . PHP_EOL;
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

    private function siteIds(): array
    {
        if (!class_exists('CSite')) {
            return ['s1'];
        }

        $siteIds = [];
        $result = CSite::GetList('sort', 'asc', ['ACTIVE' => 'Y']);
        while ($site = $result->Fetch()) {
            $siteId = trim((string)($site['ID'] ?? ''));
            if ($siteId !== '') {
                $siteIds[] = $siteId;
            }
        }

        return !empty($siteIds) ? $siteIds : ['s1'];
    }

    private function ensureIblockType(): void
    {
        $existing = CIBlockType::GetByID(TACTICUM_PRODUCT_CONTENT_IBLOCK_TYPE)->Fetch();
        if (is_array($existing)) {
            $this->line('Iblock type exists: ' . TACTICUM_PRODUCT_CONTENT_IBLOCK_TYPE);
            return;
        }

        $this->action('Create iblock type ' . TACTICUM_PRODUCT_CONTENT_IBLOCK_TYPE, static function (): void {
            $type = new CIBlockType();
            $result = $type->Add([
                'ID' => TACTICUM_PRODUCT_CONTENT_IBLOCK_TYPE,
                'SECTIONS' => 'N',
                'IN_RSS' => 'N',
                'SORT' => 500,
                'LANG' => [
                    'ru' => [
                        'NAME' => 'Tacticum product content',
                        'SECTION_NAME' => 'Разделы',
                        'ELEMENT_NAME' => 'Элементы',
                    ],
                    'en' => [
                        'NAME' => 'Tacticum product content',
                        'SECTION_NAME' => 'Sections',
                        'ELEMENT_NAME' => 'Elements',
                    ],
                ],
            ]);

            if (!$result) {
                throw new RuntimeException('Failed to create iblock type ' . TACTICUM_PRODUCT_CONTENT_IBLOCK_TYPE);
            }
        });
    }

    private function ensureIblock(string $code, string $name, int $sort): int
    {
        $id = $this->findIblockId($code);
        if ($id > 0) {
            $this->line("Iblock exists: {$code} (#{$id})");
            return $id;
        }

        $createdId = $this->action("Create iblock {$code}", function () use ($code, $name, $sort): int {
            $iblock = new CIBlock();
            $id = (int)$iblock->Add([
                'ACTIVE' => 'Y',
                'NAME' => $name,
                'CODE' => $code,
                'XML_ID' => $code,
                'IBLOCK_TYPE_ID' => TACTICUM_PRODUCT_CONTENT_IBLOCK_TYPE,
                'SITE_ID' => $this->siteIds,
                'SORT' => $sort,
                'GROUP_ID' => ['2' => 'R'],
                'INDEX_ELEMENT' => 'N',
                'INDEX_SECTION' => 'N',
                'VERSION' => 2,
            ]);

            if ($id <= 0) {
                throw new RuntimeException("Failed to create iblock {$code}: " . (string)$iblock->LAST_ERROR);
            }

            return $id;
        });

        return is_int($createdId) ? $createdId : 0;
    }

    private function findIblockId(string $code): int
    {
        $result = CIBlock::GetList(
            ['ID' => 'ASC'],
            [
                'CODE' => $code,
                'CHECK_PERMISSIONS' => 'N',
            ]
        );
        $iblock = $result->Fetch();

        return is_array($iblock) ? (int)$iblock['ID'] : 0;
    }

    private function ensureProperty(int $iblockId, array $fields): int
    {
        $code = (string)$fields['CODE'];
        $existingId = $this->findPropertyId($iblockId, $code);
        if ($existingId > 0) {
            $this->line("Property exists: iblock #{$iblockId} {$code} (#{$existingId})");
            return $existingId;
        }

        $createdId = $this->action("Create property {$code} on iblock #{$iblockId}", static function () use ($iblockId, $fields): int {
            $property = new CIBlockProperty();
            $id = (int)$property->Add(array_merge($fields, [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
            ]));

            if ($id <= 0) {
                throw new RuntimeException("Failed to create property {$fields['CODE']} on iblock #{$iblockId}: " . (string)$property->LAST_ERROR);
            }

            return $id;
        });

        return is_int($createdId) ? $createdId : 0;
    }

    private function findPropertyId(int $iblockId, string $code): int
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

    private function stringProperty(string $code, string $name, int $sort, string $multiple = 'N'): array
    {
        return [
            'NAME' => $name,
            'CODE' => $code,
            'SORT' => $sort,
            'PROPERTY_TYPE' => 'S',
            'MULTIPLE' => $multiple,
        ];
    }

    private function elementProperty(string $code, string $name, int $sort, int $linkIblockId, string $multiple = 'N'): array
    {
        return [
            'NAME' => $name,
            'CODE' => $code,
            'SORT' => $sort,
            'PROPERTY_TYPE' => 'E',
            'LINK_IBLOCK_ID' => $linkIblockId,
            'MULTIPLE' => $multiple,
        ];
    }

    private function ensureProductsProperties(int $iblockId): void
    {
        $properties = [
            $this->stringProperty('PRODUCT_CODE', 'Product code', 100),
            $this->stringProperty('EYEBROW', 'Eyebrow / public product name', 110),
            $this->stringProperty('PRODUCT_TITLE', 'Product title', 120),
            $this->stringProperty('PRODUCT_LEAD', 'Product lead', 130),
            $this->stringProperty('PRIMARY_CTA_TEXT', 'Primary CTA text', 140),
            $this->stringProperty('SECONDARY_CTA_TEXT', 'Secondary CTA text', 150),
            $this->stringProperty('SECONDARY_CTA_HREF', 'Secondary CTA href', 160),
            $this->stringProperty('BADGES_JSON', 'Badges JSON', 170),
            $this->stringProperty('HERO_CARDS_JSON', 'Hero cards JSON', 180),
            $this->stringProperty('CTA_JSON', 'CTA JSON', 190),
            $this->stringProperty('SOURCE_DATA_JSON', 'Initial source data JSON', 900),
        ];

        foreach ($properties as $property) {
            $this->ensureProperty($iblockId, $property);
        }
    }

    private function ensureProductBlocksProperties(int $iblockId, int $productsIblockId): void
    {
        $properties = [
            $this->elementProperty('PRODUCT', 'Product', 100, $productsIblockId),
            $this->stringProperty('BLOCK_TYPE', 'Block type', 110),
            $this->stringProperty('BLOCK_KEY', 'Block key', 120),
        ];

        foreach ($properties as $property) {
            $this->ensureProperty($iblockId, $property);
        }
    }

    private function ensureProductUseCasesProperties(int $iblockId, int $productsIblockId): void
    {
        $properties = [
            $this->elementProperty('PRODUCT', 'Product', 100, $productsIblockId),
            $this->stringProperty('TRIGGER', 'Trigger', 110),
            $this->stringProperty('OWNER', 'Owner / buyer role', 120),
            $this->stringProperty('PILOT_INPUT', 'Pilot input', 130),
            $this->stringProperty('PILOT_OUTPUT', 'Pilot output', 140),
            $this->stringProperty('LIMITATION', 'Limitation', 150),
            $this->stringProperty('PROOF_STATUS', 'Proof status', 160),
            $this->stringProperty('CTA_INTENT', 'CTA intent', 170),
        ];

        foreach ($properties as $property) {
            $this->ensureProperty($iblockId, $property);
        }
    }

    private function ensureExistingIblockProductRelations(int $productsIblockId): void
    {
        foreach (['faq', 'cases', 'offer', 'services', 'aiagents'] as $key) {
            $iblockId = function_exists('tacticum_rest_get_iblock_id')
                ? tacticum_rest_get_iblock_id($key)
                : 0;
            if ($iblockId <= 0) {
                $this->line("Skip relation property for {$key}: iblock key is missing in config.");
                continue;
            }

            $this->ensureProperty(
                $iblockId,
                $this->elementProperty('PRODUCT', 'Product relation', 900, $productsIblockId, 'Y')
            );
        }
    }

    private function seedProducts(int $productsIblockId, int $blocksIblockId, int $useCasesIblockId): void
    {
        $sort = 100;
        foreach ($this->seedFiles() as $productCode => $relativeFile) {
            $data = $this->loadSeedData($relativeFile);
            if (empty($data)) {
                $this->line("Skip {$productCode}: seed data is empty.");
                continue;
            }

            $productId = $this->seedProductElement($productsIblockId, $productCode, $data, $sort);
            if ($productId <= 0) {
                $this->line("Skip child seed for {$productCode}: product id unavailable.");
                $sort += 100;
                continue;
            }

            $this->seedProductBlocks($blocksIblockId, $productId, $productCode, $data);
            $this->seedProductUseCases($useCasesIblockId, $productId, $productCode, $data);
            $sort += 100;
        }
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
        $path = $this->documentRoot . '/local/php_interface/include/product_data/' . $relativeFile;
        if (!is_file($path)) {
            return [];
        }

        $data = require $path;

        return is_array($data) ? $data : [];
    }

    private function seedProductElement(int $iblockId, string $productCode, array $data, int $sort): int
    {
        $existingId = $this->findElementId($iblockId, $productCode);
        $properties = [
            'PRODUCT_CODE' => $productCode,
            'EYEBROW' => $this->stringValue($data['eyebrow'] ?? ''),
            'PRODUCT_TITLE' => $this->stringValue($data['title'] ?? ''),
            'PRODUCT_LEAD' => $this->stringValue($data['lead'] ?? ''),
            'PRIMARY_CTA_TEXT' => $this->stringValue($data['primary_cta_text'] ?? ''),
            'SECONDARY_CTA_TEXT' => $this->stringValue($data['secondary_cta_text'] ?? ''),
            'SECONDARY_CTA_HREF' => $this->stringValue($data['secondary_cta_href'] ?? ''),
            'BADGES_JSON' => $this->jsonValue($data['badges'] ?? []),
            'HERO_CARDS_JSON' => $this->jsonValue($data['hero_cards'] ?? []),
            'CTA_JSON' => $this->jsonValue($data['cta'] ?? []),
            'SOURCE_DATA_JSON' => $this->jsonValue($data),
        ];
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'NAME' => $properties['EYEBROW'] !== '' ? $properties['EYEBROW'] : $productCode,
            'CODE' => $productCode,
            'XML_ID' => 'tacticum-product-' . $productCode,
            'SORT' => $sort,
            'PREVIEW_TEXT' => $properties['PRODUCT_LEAD'],
            'PREVIEW_TEXT_TYPE' => 'text',
            'DETAIL_TEXT' => $properties['PRODUCT_TITLE'],
            'DETAIL_TEXT_TYPE' => 'text',
        ];

        if ($existingId > 0) {
            $this->line("Product exists: {$productCode} (#{$existingId})");
            if ($this->updateSeedContent) {
                $this->updateElement($iblockId, $existingId, $fields, $properties, "Update product {$productCode}");
            }
            return $existingId;
        }

        return $this->createElement($fields, $properties, "Create product {$productCode}");
    }

    private function seedProductBlocks(int $iblockId, int $productId, string $productCode, array $data): void
    {
        $blocks = [];
        $blocks[] = [
            'type' => 'hero',
            'key' => 'hero',
            'name' => 'Hero',
            'payload' => [
                'badges' => is_array($data['badges'] ?? null) ? $data['badges'] : [],
                'hero_cards' => is_array($data['hero_cards'] ?? null) ? $data['hero_cards'] : [],
                'primary_cta_text' => $this->stringValue($data['primary_cta_text'] ?? ''),
                'secondary_cta_text' => $this->stringValue($data['secondary_cta_text'] ?? ''),
                'secondary_cta_href' => $this->stringValue($data['secondary_cta_href'] ?? ''),
            ],
        ];

        foreach (['fit_guide', 'architecture', 'comparison', 'procurement', 'rollout', 'proof', 'faq'] as $key) {
            if (!empty($data[$key]) && is_array($data[$key])) {
                $blocks[] = [
                    'type' => $key,
                    'key' => $key,
                    'name' => $this->blockName($key, $data[$key]),
                    'payload' => $data[$key],
                ];
            }
        }

        foreach (($data['sections'] ?? []) as $index => $section) {
            if (!is_array($section)) {
                continue;
            }
            $blocks[] = [
                'type' => 'section',
                'key' => 'section-' . ((int)$index + 1),
                'name' => $this->blockName('section', $section),
                'payload' => $section,
            ];
        }

        if (!empty($data['use_cases']) && is_array($data['use_cases'])) {
            $useCaseMeta = $data['use_cases'];
            unset($useCaseMeta['items']);
            $blocks[] = [
                'type' => 'use_cases',
                'key' => 'use_cases',
                'name' => $this->blockName('use_cases', $useCaseMeta),
                'payload' => $useCaseMeta,
            ];
        }

        if (!empty($data['cta']) && is_array($data['cta'])) {
            $blocks[] = [
                'type' => 'cta',
                'key' => 'cta',
                'name' => $this->blockName('cta', $data['cta']),
                'payload' => $data['cta'],
            ];
        }

        $sort = 100;
        foreach ($blocks as $block) {
            $code = $productCode . '-' . $block['key'];
            $existingId = $this->findElementId($iblockId, $code);
            $properties = [
                'PRODUCT' => $productId,
                'BLOCK_TYPE' => $block['type'],
                'BLOCK_KEY' => $block['key'],
            ];
            $fields = [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'NAME' => $block['name'],
                'CODE' => $code,
                'XML_ID' => 'tacticum-product-block-' . $code,
                'SORT' => $sort,
                'DETAIL_TEXT' => $this->jsonValue($block['payload']),
                'DETAIL_TEXT_TYPE' => 'text',
            ];

            if ($existingId > 0) {
                $this->line("Product block exists: {$code} (#{$existingId})");
                if ($this->updateSeedContent) {
                    $this->updateElement($iblockId, $existingId, $fields, $properties, "Update product block {$code}");
                }
            } else {
                $this->createElement($fields, $properties, "Create product block {$code}");
            }

            $sort += 100;
        }
    }

    private function seedProductUseCases(int $iblockId, int $productId, string $productCode, array $data): void
    {
        $items = $data['use_cases']['items'] ?? [];
        if (!is_array($items)) {
            return;
        }

        $sort = 100;
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $code = $productCode . '-use-case-' . ((int)$index + 1);
            $existingId = $this->findElementId($iblockId, $code);
            $properties = [
                'PRODUCT' => $productId,
                'TRIGGER' => $this->stringValue($item['trigger'] ?? ''),
                'OWNER' => $this->stringValue($item['owner'] ?? ''),
                'PILOT_INPUT' => $this->stringValue($item['pilot_input'] ?? ''),
                'PILOT_OUTPUT' => $this->stringValue($item['pilot_output'] ?? ''),
                'LIMITATION' => $this->stringValue($item['limitation'] ?? ''),
                'PROOF_STATUS' => $this->stringValue($item['proof_status'] ?? 'pilot-artifact'),
                'CTA_INTENT' => $this->stringValue($item['cta_intent'] ?? ''),
            ];
            $fields = [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'NAME' => $this->stringValue($item['title'] ?? $code),
                'CODE' => $code,
                'XML_ID' => 'tacticum-product-use-case-' . $code,
                'SORT' => $sort,
                'PREVIEW_TEXT' => $properties['TRIGGER'],
                'PREVIEW_TEXT_TYPE' => 'text',
                'DETAIL_TEXT' => $this->jsonValue($item),
                'DETAIL_TEXT_TYPE' => 'text',
            ];

            if ($existingId > 0) {
                $this->line("Product use case exists: {$code} (#{$existingId})");
                if ($this->updateSeedContent) {
                    $this->updateElement($iblockId, $existingId, $fields, $properties, "Update product use case {$code}");
                }
            } else {
                $this->createElement($fields, $properties, "Create product use case {$code}");
            }

            $sort += 100;
        }
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
            unset($updateFields['IBLOCK_ID'], $updateFields['CODE'], $updateFields['XML_ID']);
            if (!$element->Update($elementId, $updateFields)) {
                throw new RuntimeException($message . ': ' . (string)$element->LAST_ERROR);
            }

            CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $properties);
        });
    }

    private function blockName(string $type, array $payload): string
    {
        $title = $this->stringValue($payload['title'] ?? '');
        if ($title !== '') {
            return $title;
        }

        return ucfirst(str_replace('_', ' ', $type));
    }

    private function stringValue(mixed $value): string
    {
        if (is_scalar($value)) {
            return trim((string)$value);
        }

        return '';
    }

    private function jsonValue(mixed $value): string
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return is_string($json) ? $json : '{}';
    }

    private function printConfigHints(int $productsIblockId, int $blocksIblockId, int $useCasesIblockId): void
    {
        $this->line('');
        $this->line('Config registry hints for local/php_interface/include/tacticum_config.php:');
        $this->line("'products' => {$productsIblockId},");
        $this->line("'product_blocks' => {$blocksIblockId},");
        $this->line("'product_use_cases' => {$useCasesIblockId},");
        $this->line('');
        $this->line("Product source flag:");
        $this->line("'products' => ['source' => 'bitrix', 'allow_fallback' => false],");
    }
}

function tacticum_product_content_migration_usage(): string
{
    return <<<TEXT
Usage:
  php tools/product-content-migration.php [--apply] [--update-seed-content] [--document-root=/path/to/site]

Default mode is dry-run. Use --apply to create missing schema and seed records.
Existing seeded content is create-only by default; use --update-seed-content to update existing records from product_data/*.php.

TEXT;
}

function tacticum_product_content_migration_options(array $argv): array
{
    $options = [
        'apply' => false,
        'update_seed_content' => false,
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
        if ($argument === '--update-seed-content') {
            $options['update_seed_content'] = true;
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
    $options = tacticum_product_content_migration_options($argv);
    if ($options['help']) {
        echo tacticum_product_content_migration_usage();
        exit(0);
    }

    $documentRoot = (string)$options['document_root'];
    $prolog = $documentRoot . '/bitrix/modules/main/include/prolog_before.php';
    if (!is_file($prolog)) {
        throw new RuntimeException('Bitrix prolog not found: ' . $prolog);
    }

    $_SERVER['DOCUMENT_ROOT'] = $documentRoot;
    $_SERVER['REQUEST_METHOD'] = 'CLI';
    define('NO_KEEP_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);

    require $prolog;

    if (!Loader::includeModule('iblock')) {
        throw new RuntimeException('Bitrix iblock module is unavailable.');
    }

    $migration = new TacticumProductContentMigration(
        (bool)$options['apply'],
        (bool)$options['update_seed_content'],
        $documentRoot
    );
    $migration->run();
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
