#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

const TACTICUM_PRODUCT_CONTENT_IBLOCK_TYPE = 'tacticum_content';

final class TacticumProductContentMigration
{
    private const LEGACY_PRODUCT_JSON_PROPERTIES = [
        'BADGES_JSON',
        'HERO_CARDS_JSON',
        'CTA_JSON',
        'SOURCE_DATA_JSON',
    ];

    private bool $apply;
    private bool $updateSeedContent;
    private bool $retireLegacyJson;
    private string $documentRoot;
    private array $siteIds = [];

    public function __construct(bool $apply, bool $updateSeedContent, bool $retireLegacyJson, string $documentRoot)
    {
        $this->apply = $apply;
        $this->updateSeedContent = $updateSeedContent;
        $this->retireLegacyJson = $retireLegacyJson;
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
        if ($productsIblockId > 0 && $this->retireLegacyJson) {
            $this->retireProductLegacyJson($productsIblockId);
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

    private function listProperty(string $code, string $name, int $sort): array
    {
        return $this->stringProperty($code, $name, $sort, 'Y');
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
            $this->listProperty('BADGE', 'Hero badge', 170),
            $this->stringProperty('CTA_FORM_ID', 'CTA form id', 200),
            $this->stringProperty('CTA_FIELD_PREFIX', 'CTA field prefix', 210),
            $this->stringProperty('CTA_TITLE', 'CTA title', 220),
            $this->stringProperty('CTA_TEXT', 'CTA text', 230),
            $this->stringProperty('CTA_FORM_TITLE', 'CTA form title', 240),
            $this->stringProperty('CTA_BUTTON_TEXT', 'CTA button text', 250),
            $this->stringProperty('CTA_SCENARIO_LABEL', 'CTA scenario label', 260),
            $this->stringProperty('CTA_SCENARIO_EMPTY_LABEL', 'CTA scenario empty label', 270),
            $this->stringProperty('CTA_LEAD_ENTRY', 'CTA lead entry', 280),
            $this->stringProperty('CTA_LEAD_PAGE_ROLE', 'CTA lead page role', 290),
            $this->stringProperty('CTA_LEAD_PRODUCT', 'CTA lead product', 300),
            $this->stringProperty('CTA_LEAD_INTENT', 'CTA lead intent', 310),
            $this->stringProperty('CTA_LEAD_CTA', 'CTA lead CTA', 320),
            $this->stringProperty('CTA_LEAD_NEXT_STEP', 'CTA lead next step', 330),
        ];

        if (!$this->retireLegacyJson) {
            $properties[] = $this->stringProperty('BADGES_JSON', 'Legacy badges JSON', 800);
            $properties[] = $this->stringProperty('HERO_CARDS_JSON', 'Legacy hero cards JSON', 810);
            $properties[] = $this->stringProperty('CTA_JSON', 'Legacy CTA JSON', 820);
            $properties[] = $this->stringProperty('SOURCE_DATA_JSON', 'Initial source data JSON', 900);
        }

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
            $this->elementProperty('PARENT_BLOCK', 'Parent block', 130, $iblockId),
            $this->stringProperty('ITEM_TYPE', 'Item type', 140),
            $this->stringProperty('EYEBROW', 'Eyebrow', 150),
            $this->stringProperty('THEME', 'Theme', 160),
            $this->stringProperty('TONE', 'Tone', 170),
            $this->stringProperty('COLUMNS_CLASS', 'Columns class', 180),
            $this->stringProperty('NOTE_TITLE', 'Note title', 190),
            $this->stringProperty('NOTE_TEXT', 'Note text', 200),
            $this->stringProperty('CTA_TEXT', 'CTA text', 210),
            $this->stringProperty('CTA_HREF', 'CTA href', 220),
            $this->stringProperty('ICON', 'Icon class', 230),
            $this->stringProperty('META', 'Meta label', 240),
            $this->stringProperty('HREF', 'Link href', 250),
            $this->stringProperty('PROOF_STATUS', 'Proof status', 260),
            $this->listProperty('ITEMS', 'Bullet item', 270),
            $this->stringProperty('VALUE', 'Controlled value', 280),
            $this->stringProperty('LABEL', 'Public label', 290),
            $this->stringProperty('FORM_ID', 'Form id', 300),
            $this->stringProperty('FIELD_PREFIX', 'Field prefix', 310),
            $this->stringProperty('FORM_TITLE', 'Form title', 320),
            $this->stringProperty('BUTTON_TEXT', 'Button text', 330),
            $this->stringProperty('SCENARIO_LABEL', 'Scenario label', 340),
            $this->stringProperty('SCENARIO_EMPTY_LABEL', 'Scenario empty label', 350),
            $this->stringProperty('LEAD_ENTRY', 'Lead entry', 360),
            $this->stringProperty('LEAD_PAGE_ROLE', 'Lead page role', 370),
            $this->stringProperty('LEAD_PRODUCT', 'Lead product', 380),
            $this->stringProperty('LEAD_INTENT', 'Lead intent', 390),
            $this->stringProperty('LEAD_CTA', 'Lead CTA', 400),
            $this->stringProperty('LEAD_NEXT_STEP', 'Lead next step', 410),
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
            'BADGE' => $this->stringList($data['badges'] ?? []),
            'CTA_FORM_ID' => $this->stringValue($data['cta']['form_id'] ?? ''),
            'CTA_FIELD_PREFIX' => $this->stringValue($data['cta']['field_prefix'] ?? ''),
            'CTA_TITLE' => $this->stringValue($data['cta']['title'] ?? ''),
            'CTA_TEXT' => $this->stringValue($data['cta']['text'] ?? ''),
            'CTA_FORM_TITLE' => $this->stringValue($data['cta']['form_title'] ?? ''),
            'CTA_BUTTON_TEXT' => $this->stringValue($data['cta']['button_text'] ?? ''),
            'CTA_SCENARIO_LABEL' => $this->stringValue($data['cta']['scenario_label'] ?? ''),
            'CTA_SCENARIO_EMPTY_LABEL' => $this->stringValue($data['cta']['scenario_empty_label'] ?? ''),
            'CTA_LEAD_ENTRY' => $this->stringValue($data['cta']['lead_context']['lead_entry'] ?? ''),
            'CTA_LEAD_PAGE_ROLE' => $this->stringValue($data['cta']['lead_context']['lead_page_role'] ?? ''),
            'CTA_LEAD_PRODUCT' => $this->stringValue($data['cta']['lead_context']['lead_product'] ?? $productCode),
            'CTA_LEAD_INTENT' => $this->stringValue($data['cta']['lead_context']['lead_intent'] ?? ''),
            'CTA_LEAD_CTA' => $this->stringValue($data['cta']['lead_context']['lead_cta'] ?? ''),
            'CTA_LEAD_NEXT_STEP' => $this->stringValue($data['cta']['lead_context']['lead_next_step'] ?? ''),
        ];
        if (!$this->retireLegacyJson) {
            $properties = array_merge($properties, [
                'BADGES_JSON' => $this->jsonValue($data['badges'] ?? []),
                'HERO_CARDS_JSON' => $this->jsonValue($data['hero_cards'] ?? []),
                'CTA_JSON' => $this->jsonValue($data['cta'] ?? []),
                'SOURCE_DATA_JSON' => $this->jsonValue($data),
            ]);
        }

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
            $this->seedProductBlock($iblockId, $productId, $productCode, $block, $sort);
            $sort += 100;
        }
    }

    private function retireProductLegacyJson(int $iblockId): void
    {
        $this->clearProductLegacyJsonValues($iblockId);

        foreach (self::LEGACY_PRODUCT_JSON_PROPERTIES as $code) {
            $propertyId = $this->findPropertyId($iblockId, $code);
            if ($propertyId <= 0) {
                $this->line("Legacy product JSON property is absent: {$code}");
                continue;
            }

            $this->action("Deactivate legacy product JSON property {$code} (#{$propertyId})", static function () use ($propertyId, $code): void {
                $property = new CIBlockProperty();
                if (!$property->Update($propertyId, ['ACTIVE' => 'N'])) {
                    throw new RuntimeException("Failed to deactivate property {$code}: " . (string)$property->LAST_ERROR);
                }
            });
        }
    }

    private function clearProductLegacyJsonValues(int $iblockId): void
    {
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID']
        );

        while ($element = $result->Fetch()) {
            $elementId = (int)($element['ID'] ?? 0);
            if ($elementId <= 0) {
                continue;
            }

            $this->action("Clear legacy product JSON values for element #{$elementId}", static function () use ($iblockId, $elementId): void {
                $values = [];
                foreach (self::LEGACY_PRODUCT_JSON_PROPERTIES as $code) {
                    $values[$code] = '';
                }

                CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $values);
            });
        }
    }

    private function seedProductBlock(
        int $iblockId,
        int $productId,
        string $productCode,
        array $block,
        int $sort
    ): void {
        $type = $this->stringValue($block['type'] ?? '');
        $key = $this->stringValue($block['key'] ?? '');
        $name = $this->stringValue($block['name'] ?? '');
        $payload = is_array($block['payload'] ?? null) ? $block['payload'] : [];
        if ($type === '' || $key === '') {
            return;
        }

        $code = $productCode . '-' . $key;
        $existingId = $this->findElementId($iblockId, $code);
        $properties = array_merge(
            [
                'PRODUCT' => $productId,
                'BLOCK_TYPE' => $type,
                'BLOCK_KEY' => $key,
            ],
            $this->blockContainerProperties($type, $payload, $productCode)
        );
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'NAME' => $name !== '' ? $name : $this->blockName($type, $payload),
            'CODE' => $code,
            'XML_ID' => 'tacticum-product-block-' . $code,
            'SORT' => $sort,
            'PREVIEW_TEXT' => $this->stringValue($payload['eyebrow'] ?? ''),
            'PREVIEW_TEXT_TYPE' => 'text',
            'DETAIL_TEXT' => $this->stringValue($payload['text'] ?? ''),
            'DETAIL_TEXT_TYPE' => 'text',
        ];

        $blockId = $existingId;
        if ($existingId > 0) {
            $this->line("Product block exists: {$code} (#{$existingId})");
            if ($this->updateSeedContent) {
                $this->updateElement($iblockId, $existingId, $fields, $properties, "Update product block {$code}");
            }
        } else {
            $blockId = $this->createElement($fields, $properties, "Create product block {$code}");
        }

        if ($blockId > 0) {
            $this->seedProductBlockChildren($iblockId, $productId, $blockId, $productCode, $type, $key, $payload);
        }
    }

    private function blockContainerProperties(string $type, array $payload, string $productCode): array
    {
        $properties = [
            'EYEBROW' => $this->stringValue($payload['eyebrow'] ?? ''),
            'THEME' => $this->stringValue($payload['theme'] ?? ''),
            'TONE' => $this->stringValue($payload['tone'] ?? ''),
            'COLUMNS_CLASS' => $this->stringValue($payload['columns_class'] ?? ''),
            'NOTE_TITLE' => $this->stringValue($payload['note_title'] ?? ''),
            'NOTE_TEXT' => $this->stringValue($payload['note_text'] ?? ''),
            'CTA_TEXT' => $this->stringValue($payload['cta_text'] ?? ''),
            'CTA_HREF' => $this->stringValue($payload['cta_href'] ?? ''),
        ];

        if ($type === 'cta') {
            $properties = array_merge($properties, [
                'FORM_ID' => $this->stringValue($payload['form_id'] ?? ''),
                'FIELD_PREFIX' => $this->stringValue($payload['field_prefix'] ?? ''),
                'FORM_TITLE' => $this->stringValue($payload['form_title'] ?? ''),
                'BUTTON_TEXT' => $this->stringValue($payload['button_text'] ?? ''),
                'SCENARIO_LABEL' => $this->stringValue($payload['scenario_label'] ?? ''),
                'SCENARIO_EMPTY_LABEL' => $this->stringValue($payload['scenario_empty_label'] ?? ''),
                'LEAD_ENTRY' => $this->stringValue($payload['lead_context']['lead_entry'] ?? ''),
                'LEAD_PAGE_ROLE' => $this->stringValue($payload['lead_context']['lead_page_role'] ?? ''),
                'LEAD_PRODUCT' => $this->stringValue($payload['lead_context']['lead_product'] ?? $productCode),
                'LEAD_INTENT' => $this->stringValue($payload['lead_context']['lead_intent'] ?? ''),
                'LEAD_CTA' => $this->stringValue($payload['lead_context']['lead_cta'] ?? ''),
                'LEAD_NEXT_STEP' => $this->stringValue($payload['lead_context']['lead_next_step'] ?? ''),
            ]);
        }

        return $properties;
    }

    private function seedProductBlockChildren(
        int $iblockId,
        int $productId,
        int $parentBlockId,
        string $productCode,
        string $type,
        string $key,
        array $payload
    ): void {
        $children = match ($type) {
            'hero' => $this->blockChildrenFromCards($payload['hero_cards'] ?? [], 'hero_card'),
            'fit_guide' => $this->fitGuideChildren($payload),
            'section' => $this->blockChildrenFromCards($payload['cards'] ?? [], 'card'),
            'architecture' => $this->blockChildrenFromCards($payload['layers'] ?? [], 'layer'),
            'comparison' => $this->blockChildrenFromCards($payload['columns'] ?? [], 'column'),
            'procurement' => $this->blockChildrenFromCards($payload['items'] ?? [], 'item'),
            'rollout' => $this->blockChildrenFromCards($payload['steps'] ?? [], 'step'),
            'proof' => $this->blockChildrenFromCards($payload['items'] ?? [], 'proof_item'),
            'faq' => $this->faqChildren($payload['items'] ?? []),
            'cta' => $this->scenarioOptionChildren($payload['scenario_options'] ?? []),
            default => [],
        };

        $sort = 100;
        foreach ($children as $child) {
            $childKey = $this->stringValue($child['key'] ?? ('item-' . $sort));
            $childCode = $productCode . '-' . $key . '-' . $childKey;
            $childName = $this->stringValue($child['title'] ?? '');
            if ($childName === '') {
                $childName = $childKey;
            }
            $properties = array_merge(
                [
                    'PRODUCT' => $productId,
                    'PARENT_BLOCK' => $parentBlockId,
                    'BLOCK_TYPE' => $type,
                    'BLOCK_KEY' => $childKey,
                    'ITEM_TYPE' => $this->stringValue($child['item_type'] ?? 'item'),
                ],
                is_array($child['properties'] ?? null) ? $child['properties'] : []
            );
            $fields = [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'NAME' => $childName,
                'CODE' => $childCode,
                'XML_ID' => 'tacticum-product-block-item-' . $childCode,
                'SORT' => $sort,
                'PREVIEW_TEXT' => $this->stringValue($child['preview_text'] ?? ''),
                'PREVIEW_TEXT_TYPE' => 'text',
                'DETAIL_TEXT' => $this->stringValue($child['text'] ?? ''),
                'DETAIL_TEXT_TYPE' => 'text',
            ];

            $existingId = $this->findElementId($iblockId, $childCode);
            if ($existingId > 0) {
                $this->line("Product block item exists: {$childCode} (#{$existingId})");
                if ($this->updateSeedContent) {
                    $this->updateElement($iblockId, $existingId, $fields, $properties, "Update product block item {$childCode}");
                }
            } else {
                $this->createElement($fields, $properties, "Create product block item {$childCode}");
            }

            $sort += 100;
        }
    }

    private function blockChildrenFromCards(mixed $cards, string $itemType): array
    {
        if (!is_array($cards)) {
            return [];
        }

        $children = [];
        foreach ($cards as $index => $card) {
            if (!is_array($card)) {
                continue;
            }

            $children[] = [
                'key' => $itemType . '-' . ((int)$index + 1),
                'item_type' => $itemType,
                'title' => $this->stringValue($card['title'] ?? $card['question'] ?? ''),
                'text' => $this->stringValue($card['text'] ?? $card['answer'] ?? ''),
                'properties' => [
                    'ICON' => $this->stringValue($card['icon'] ?? ''),
                    'META' => $this->stringValue($card['meta'] ?? ''),
                    'HREF' => $this->stringValue($card['href'] ?? ''),
                    'TONE' => $this->stringValue($card['tone'] ?? ''),
                    'PROOF_STATUS' => $this->stringValue($card['proof_status'] ?? ''),
                    'ITEMS' => $this->stringList($card['items'] ?? []),
                ],
            ];
        }

        return $children;
    }

    private function fitGuideChildren(array $payload): array
    {
        $children = [];
        foreach (['fits', 'not_fits', 'start'] as $key) {
            $column = is_array($payload[$key] ?? null) ? $payload[$key] : [];
            if (empty($column)) {
                continue;
            }

            $children[] = [
                'key' => $key,
                'item_type' => $key,
                'title' => $this->stringValue($column['title'] ?? ''),
                'text' => '',
                'properties' => [
                    'TONE' => $this->stringValue($column['tone'] ?? ''),
                    'ITEMS' => $this->stringList($column['items'] ?? []),
                ],
            ];
        }

        return $children;
    }

    private function faqChildren(mixed $items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $children = [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $children[] = [
                'key' => 'faq-' . ((int)$index + 1),
                'item_type' => 'faq_item',
                'title' => $this->stringValue($item['question'] ?? ''),
                'text' => $this->stringValue($item['answer'] ?? ''),
                'properties' => [],
            ];
        }

        return $children;
    }

    private function scenarioOptionChildren(mixed $items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $children = [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = $this->stringValue($item['LABEL'] ?? '');
            $value = $this->stringValue($item['VALUE'] ?? '');
            $children[] = [
                'key' => 'scenario-' . ((int)$index + 1),
                'item_type' => 'scenario_option',
                'title' => $label,
                'text' => '',
                'properties' => [
                    'VALUE' => $value,
                    'LABEL' => $label,
                ],
            ];
        }

        return $children;
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
                'DETAIL_TEXT' => $properties['LIMITATION'],
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
  php tools/product-content-migration.php [--apply] [--update-seed-content] [--retire-legacy-json] [--document-root=/path/to/site]

Default mode is dry-run. Use --apply to create missing schema and seed records.
Existing seeded content is create-only by default; use --update-seed-content to update existing records from product_data/*.php.
Use --retire-legacy-json after V2 schema/content is in place to stop seeding product JSON values, clear existing JSON values and deactivate legacy product JSON properties in admin.

TEXT;
}

function tacticum_product_content_migration_options(array $argv): array
{
    $options = [
        'apply' => false,
        'update_seed_content' => false,
        'retire_legacy_json' => false,
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
        if ($argument === '--retire-legacy-json') {
            $options['retire_legacy_json'] = true;
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
    tacticum_tools_require_product_content_runtime($documentRoot);

    if (!Loader::includeModule('iblock')) {
        throw new RuntimeException('Bitrix iblock module is unavailable.');
    }

    $migration = new TacticumProductContentMigration(
        (bool)$options['apply'],
        (bool)$options['update_seed_content'],
        (bool)$options['retire_legacy_json'],
        $documentRoot
    );
    $migration->run();
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
