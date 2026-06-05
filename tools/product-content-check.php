#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumProductContentCheck
{
    private string $documentRoot;
    private bool $strict;
    private bool $json;
    private array $errors = [];
    private array $warnings = [];
    private array $rows = [];
    private array $iblocks = [];
    private string $productSource = 'unknown';
    private string $configuredProductSource = 'unknown';
    private ?bool $productFallbackAllowed = null;
    private ?int $productCacheTtl = null;
    private string $productSchemaVersion = 'unknown';
    private array $adminModel = [];

    private const SAFE_URL_PREFIXES = ['/', '#', 'https://'];

    private const REQUIRED_TOP_STRINGS = [
        'eyebrow',
        'title',
        'lead',
        'primary_cta_text',
        'secondary_cta_text',
        'secondary_cta_href',
    ];

    private const REQUIRED_TOP_ARRAYS = [
        'badges',
        'hero_cards',
        'sections',
    ];

    private const REQUIRED_TOP_OBJECTS = [
        'fit_guide',
        'architecture',
        'use_cases',
        'comparison',
        'procurement',
        'rollout',
        'proof',
        'faq',
        'cta',
    ];

    private const REQUIRED_TO_BE_BLOCKS = [
        'fit_guide',
        'architecture',
        'use_cases',
        'comparison',
        'procurement',
        'rollout',
        'proof',
        'faq',
        'cta',
    ];

    private const ALLOWED_PROOF_STATUSES = [
        'pilot-artifact',
        'private-evidence',
        'public-safe',
        'pending',
        'blocked',
    ];

    private const ALLOWED_CTA_SCENARIOS = [
        'pilot',
        'architecture-session',
        'procurement-security',
        'team-delivery',
        'estimate',
    ];

    private const ALLOWED_LEAD_CONTEXT_KEYS = [
        'lead_entry',
        'lead_page_role',
        'lead_product',
        'lead_intent',
        'lead_cta',
        'lead_next_step',
    ];

    private const ALLOWED_COLUMNS_CLASSES = [
        'lg:grid-cols-2',
        'lg:grid-cols-3',
        'lg:grid-cols-4',
    ];

    private const LEGACY_PRODUCT_JSON_PROPERTIES = [
        'BADGES_JSON',
        'HERO_CARDS_JSON',
        'CTA_JSON',
        'SOURCE_DATA_JSON',
    ];

    public function __construct(string $documentRoot, bool $strict, bool $json)
    {
        $this->documentRoot = rtrim($documentRoot, '/');
        $this->strict = $strict;
        $this->json = $json;
    }

    public function run(): int
    {
        if (!Loader::includeModule('iblock')) {
            $this->error('Bitrix iblock module is unavailable.');
            return $this->finish();
        }

        foreach (['products', 'product_blocks', 'product_use_cases'] as $key) {
            $id = $this->iblockId($key);
            if ($id <= 0) {
                $this->error("Missing iblock config key: {$key}");
                continue;
            }

            $this->iblocks[$key] = $id;
            $this->line("Iblock {$key}: #{$id}");
        }

        $productsIblockId = $this->iblockId('products');
        $this->checkAdminEditableSchema();
        $this->checkProducts();
        if ($productsIblockId > 0) {
            $this->checkRelationProperties($productsIblockId);
        }

        return $this->finish();
    }

    private function checkAdminEditableSchema(): void
    {
        $required = $this->expectedAdminEditableSchema();

        $summary = [];
        foreach ($required as $iblockKey => $propertySpecs) {
            $iblockId = $this->iblockId($iblockKey);
            if ($iblockId <= 0) {
                continue;
            }

            $missing = [];
            $inactive = [];
            $mismatched = [];
            foreach ($propertySpecs as $propertyCode => $propertySpec) {
                $property = $this->propertyFields($iblockId, $propertyCode);
                if ($property === null) {
                    $missing[] = $propertyCode;
                    continue;
                }

                if (($property['ACTIVE'] ?? 'N') !== 'Y') {
                    $inactive[] = $propertyCode;
                }

                foreach ($this->propertySchemaMismatches($property, $propertySpec) as $mismatch) {
                    $mismatched[] = $propertyCode . ': ' . $mismatch;
                }
            }

            $summary[$iblockKey] = [
                'required_properties' => count($propertySpecs),
                'missing_properties' => $missing,
                'inactive_properties' => $inactive,
                'mismatched_properties' => $mismatched,
            ];

            if (!empty($missing)) {
                $this->warnOrError("Iblock {$iblockKey} misses admin-editable V2 properties: " . implode(', ', $missing));
            }
            if (!empty($inactive)) {
                $this->warnOrError("Iblock {$iblockKey} has inactive admin-editable V2 properties: " . implode(', ', $inactive));
            }
            if (!empty($mismatched)) {
                $this->warnOrError("Iblock {$iblockKey} has mismatched admin-editable V2 properties: " . implode('; ', $mismatched));
            }
        }

        $legacyJson = $this->legacyJsonUsage();
        foreach ($legacyJson as $key => $count) {
            if ($count > 0) {
                $this->warnOrError("Legacy JSON retirement is incomplete: {$key}={$count}.");
            }
        }

        $this->adminModel = [
            'v2_schema' => $summary,
            'legacy_json' => $legacyJson,
        ];
    }

    private function expectedAdminEditableSchema(): array
    {
        $string = ['type' => 'S', 'multiple' => 'N'];
        $list = ['type' => 'S', 'multiple' => 'Y'];

        return [
            'products' => [
                'PRODUCT_CODE' => $string,
                'EYEBROW' => $string,
                'PRODUCT_TITLE' => $string,
                'PRODUCT_LEAD' => $string,
                'PRIMARY_CTA_TEXT' => $string,
                'SECONDARY_CTA_TEXT' => $string,
                'SECONDARY_CTA_HREF' => $string,
                'BADGE' => $list,
                'CTA_FORM_ID' => $string,
                'CTA_FIELD_PREFIX' => $string,
                'CTA_TITLE' => $string,
                'CTA_TEXT' => $string,
                'CTA_FORM_TITLE' => $string,
                'CTA_BUTTON_TEXT' => $string,
                'CTA_SCENARIO_LABEL' => $string,
                'CTA_SCENARIO_EMPTY_LABEL' => $string,
                'CTA_LEAD_ENTRY' => $string,
                'CTA_LEAD_PAGE_ROLE' => $string,
                'CTA_LEAD_PRODUCT' => $string,
                'CTA_LEAD_INTENT' => $string,
                'CTA_LEAD_CTA' => $string,
                'CTA_LEAD_NEXT_STEP' => $string,
            ],
            'product_blocks' => [
                'PRODUCT' => ['type' => 'E', 'multiple' => 'N', 'link_iblock_key' => 'products'],
                'BLOCK_TYPE' => $string,
                'BLOCK_KEY' => $string,
                'PARENT_BLOCK' => ['type' => 'E', 'multiple' => 'N', 'link_iblock_key' => 'product_blocks'],
                'ITEM_TYPE' => $string,
                'EYEBROW' => $string,
                'THEME' => $string,
                'TONE' => $string,
                'COLUMNS_CLASS' => $string,
                'NOTE_TITLE' => $string,
                'NOTE_TEXT' => $string,
                'CTA_TEXT' => $string,
                'CTA_HREF' => $string,
                'ICON' => $string,
                'META' => $string,
                'HREF' => $string,
                'PROOF_STATUS' => $string,
                'ITEMS' => $list,
                'VALUE' => $string,
                'LABEL' => $string,
                'FORM_ID' => $string,
                'FIELD_PREFIX' => $string,
                'FORM_TITLE' => $string,
                'BUTTON_TEXT' => $string,
                'SCENARIO_LABEL' => $string,
                'SCENARIO_EMPTY_LABEL' => $string,
                'LEAD_ENTRY' => $string,
                'LEAD_PAGE_ROLE' => $string,
                'LEAD_PRODUCT' => $string,
                'LEAD_INTENT' => $string,
                'LEAD_CTA' => $string,
                'LEAD_NEXT_STEP' => $string,
            ],
            'product_use_cases' => [
                'PRODUCT' => ['type' => 'E', 'multiple' => 'N', 'link_iblock_key' => 'products'],
                'TRIGGER' => $string,
                'OWNER' => $string,
                'PILOT_INPUT' => $string,
                'PILOT_OUTPUT' => $string,
                'LIMITATION' => $string,
                'PROOF_STATUS' => $string,
                'CTA_INTENT' => $string,
            ],
        ];
    }

    private function propertySchemaMismatches(array $property, array $expected): array
    {
        $mismatches = [];
        if ((string)($property['PROPERTY_TYPE'] ?? '') !== (string)($expected['type'] ?? '')) {
            $mismatches[] = 'type expected ' . (string)($expected['type'] ?? '') . ', got ' . (string)($property['PROPERTY_TYPE'] ?? '');
        }
        if ((string)($property['MULTIPLE'] ?? '') !== (string)($expected['multiple'] ?? '')) {
            $mismatches[] = 'multiple expected ' . (string)($expected['multiple'] ?? '') . ', got ' . (string)($property['MULTIPLE'] ?? '');
        }
        if (isset($expected['link_iblock_key'])) {
            $expectedIblockId = $this->iblockId((string)$expected['link_iblock_key']);
            $actualIblockId = (int)($property['LINK_IBLOCK_ID'] ?? 0);
            if ($expectedIblockId <= 0 || $actualIblockId !== $expectedIblockId) {
                $mismatches[] = "link iblock expected #{$expectedIblockId}, got #{$actualIblockId}";
            }
        }

        return $mismatches;
    }

    private function checkProducts(): void
    {
        if (!function_exists('tacticum_product_content_codes')) {
            $this->error('Product content helper tacticum_product_content_codes() is unavailable.');
            return;
        }
        if (!function_exists('tacticum_product_content_bitrix_data')) {
            $this->error('Product content helper tacticum_product_content_bitrix_data() is unavailable.');
            return;
        }

        $source = function_exists('tacticum_product_content_source')
            ? tacticum_product_content_source()
            : 'unknown';
        $this->productSource = $source;
        $this->line("Product source mode: {$source}");
        if (function_exists('tacticum_product_content_configured_source')) {
            $this->configuredProductSource = tacticum_product_content_configured_source();
            $this->line('Configured product source: ' . $this->configuredProductSource);
        }
        if (function_exists('tacticum_product_content_fallback_allowed')) {
            $this->productFallbackAllowed = tacticum_product_content_fallback_allowed();
            $this->line('Product fallback allowed: ' . ($this->productFallbackAllowed ? 'yes' : 'no'));
        }
        if ($source !== 'bitrix') {
            $this->warnOrError('Product source mode must be bitrix; silent product fallback is disabled by owner decision.');
        }
        if ($this->productFallbackAllowed === true) {
            $this->warnOrError('Product fallback must be disabled for release evidence.');
        }
        if (function_exists('tacticum_product_content_cache_ttl')) {
            $this->productCacheTtl = tacticum_product_content_cache_ttl();
            $this->line('Product cache TTL: ' . $this->productCacheTtl);
        }
        if (function_exists('tacticum_product_content_schema_version')) {
            $this->productSchemaVersion = tacticum_product_content_schema_version();
            $this->line('Product schema version: ' . $this->productSchemaVersion);
        }

        foreach (array_keys(tacticum_product_content_codes()) as $productCode) {
            $page = tacticum_product_content_bitrix_data((string)$productCode);
            $minimumRenderable = !empty($page)
                && function_exists('tacticum_product_content_is_minimum_renderable')
                && tacticum_product_content_is_minimum_renderable($page);
            $diagnostics = is_array($page['_diagnostics'] ?? null)
                ? $page['_diagnostics']
                : $this->fallbackDiagnostics($page);
            $missingBlocks = is_array($diagnostics['missing_to_be_blocks'] ?? null)
                ? $diagnostics['missing_to_be_blocks']
                : [];
            $useCaseCount = is_array($page['use_cases']['items'] ?? null)
                ? count($page['use_cases']['items'])
                : 0;
            $schemaIssues = !empty($page)
                ? $this->productSchemaIssues((string)$productCode, $page)
                : [];

            $this->rows[] = [
                'code' => (string)$productCode,
                'status' => $minimumRenderable ? 'ok' : 'not-renderable',
                'source' => (string)($page['_source'] ?? 'none'),
                'use_cases' => $useCaseCount,
                'missing_blocks' => $missingBlocks,
                'schema_issues' => count($schemaIssues),
            ];

            if (!$minimumRenderable) {
                $this->error("Product {$productCode} is not minimum-renderable from Bitrix.");
            }
            if ($useCaseCount <= 0) {
                $this->warnOrError("Product {$productCode} has no Bitrix use cases.");
            }
            if (!empty($missingBlocks)) {
                $this->warnOrError("Product {$productCode} misses TO BE blocks: " . implode(', ', $missingBlocks));
            }
            foreach ($schemaIssues as $schemaIssue) {
                $this->warnOrError($schemaIssue);
            }
        }
    }

    private function checkRelationProperties(int $productsIblockId): void
    {
        foreach (['faq', 'cases', 'offer', 'services', 'aiagents'] as $key) {
            $iblockId = $this->iblockId($key);
            if ($iblockId <= 0) {
                $this->warning("Skip relation check for {$key}: iblock key is absent.");
                continue;
            }

            $propertyId = $this->propertyId($iblockId, 'PRODUCT');
            if ($propertyId <= 0) {
                $this->warnOrError("Iblock {$key} (#{$iblockId}) has no PRODUCT relation property.");
                continue;
            }

            $linkIblockId = $this->propertyLinkIblockId($propertyId);
            if ($linkIblockId !== $productsIblockId) {
                $this->warnOrError("Iblock {$key} PRODUCT relation points to #{$linkIblockId}, expected #{$productsIblockId}.");
            }
        }
    }

    private function legacyJsonUsage(): array
    {
        return [
            'products_json_properties' => $this->countProductJsonProperties(),
            'products_active_json_properties' => $this->countActiveProductJsonProperties(),
            'product_blocks_json_texts' => $this->countJsonDetailTexts($this->iblockId('product_blocks')),
            'product_use_cases_json_texts' => $this->countJsonDetailTexts($this->iblockId('product_use_cases')),
        ];
    }

    private function countProductJsonProperties(): int
    {
        $iblockId = $this->iblockId('products');
        if ($iblockId <= 0 || !class_exists('CIBlockElement')) {
            return 0;
        }

        $count = 0;
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

            foreach (self::LEGACY_PRODUCT_JSON_PROPERTIES as $code) {
                $value = $this->propertyValue($iblockId, $elementId, $code);
                if ($this->looksLikeJson($value)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    private function countActiveProductJsonProperties(): int
    {
        $iblockId = $this->iblockId('products');
        if ($iblockId <= 0 || !class_exists('CIBlockProperty')) {
            return 0;
        }

        $count = 0;
        $result = CIBlockProperty::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
            ]
        );
        while ($property = $result->Fetch()) {
            if (in_array((string)($property['CODE'] ?? ''), self::LEGACY_PRODUCT_JSON_PROPERTIES, true)) {
                $count++;
            }
        }

        return $count;
    }

    private function countJsonDetailTexts(int $iblockId): int
    {
        if ($iblockId <= 0 || !class_exists('CIBlockElement')) {
            return 0;
        }

        $count = 0;
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'DETAIL_TEXT']
        );
        while ($element = $result->Fetch()) {
            if ($this->looksLikeJson($element['DETAIL_TEXT'] ?? null)) {
                $count++;
            }
        }

        return $count;
    }

    private function propertyValue(int $iblockId, int $elementId, string $code): mixed
    {
        $result = CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc'], ['CODE' => $code]);
        $property = $result->Fetch();

        return is_array($property) ? ($property['VALUE'] ?? null) : null;
    }

    private function looksLikeJson(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $value = trim($value);
        if ($value === '' || (!str_starts_with($value, '{') && !str_starts_with($value, '['))) {
            return false;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded);
    }

    private function fallbackDiagnostics(array $page): array
    {
        if (function_exists('tacticum_product_content_completeness_diagnostics')) {
            return tacticum_product_content_completeness_diagnostics($page);
        }

        return [
            'minimum_renderable' => false,
            'missing_to_be_blocks' => [],
        ];
    }

    private function productSchemaIssues(string $productCode, array $page): array
    {
        $issues = [];
        $path = static fn (string $key): string => "{$productCode}.{$key}";

        foreach (self::REQUIRED_TOP_STRINGS as $key) {
            $this->requireString($page, $key, $path($key), $issues);
        }
        foreach (self::REQUIRED_TOP_ARRAYS as $key) {
            $this->requireArray($page, $key, $path($key), $issues, 1);
        }
        foreach (self::REQUIRED_TOP_OBJECTS as $key) {
            $this->requireAssocArray($page, $key, $path($key), $issues);
        }
        foreach (self::REQUIRED_TO_BE_BLOCKS as $key) {
            if (!array_key_exists($key, $page)) {
                $issues[] = "{$path($key)} is required by product schema.";
            }
        }

        $this->validateUrl((string)($page['secondary_cta_href'] ?? ''), $path('secondary_cta_href'), $issues);
        $this->validateHeroCards(is_array($page['hero_cards'] ?? null) ? $page['hero_cards'] : [], $path('hero_cards'), $issues);
        $this->validateFitGuide(is_array($page['fit_guide'] ?? null) ? $page['fit_guide'] : [], $path('fit_guide'), $issues);
        $this->validateSections(is_array($page['sections'] ?? null) ? $page['sections'] : [], $path('sections'), $issues);
        $this->validateArchitecture(is_array($page['architecture'] ?? null) ? $page['architecture'] : [], $path('architecture'), $issues);
        $this->validateUseCases(is_array($page['use_cases'] ?? null) ? $page['use_cases'] : [], $path('use_cases'), $issues);
        $this->validateComparison(is_array($page['comparison'] ?? null) ? $page['comparison'] : [], $path('comparison'), $issues);
        $this->validateProcurement(is_array($page['procurement'] ?? null) ? $page['procurement'] : [], $path('procurement'), $issues);
        $this->validateRollout(is_array($page['rollout'] ?? null) ? $page['rollout'] : [], $path('rollout'), $issues);
        $this->validateProof(is_array($page['proof'] ?? null) ? $page['proof'] : [], $path('proof'), $issues);
        $this->validateFaq(is_array($page['faq'] ?? null) ? $page['faq'] : [], $path('faq'), $issues);
        $this->validateCta($productCode, is_array($page['cta'] ?? null) ? $page['cta'] : [], $path('cta'), $issues);

        return $issues;
    }

    private function validateHeroCards(array $cards, string $path, array &$issues): void
    {
        if (count($cards) < 1) {
            $issues[] = "{$path} must contain at least 1 card.";
            return;
        }

        foreach ($cards as $index => $card) {
            if (!is_array($card)) {
                $issues[] = "{$path}[{$index}] must be an object.";
                continue;
            }
            $this->requireStrings($card, ['title', 'text'], "{$path}[{$index}]", $issues);
        }
    }

    private function validateFitGuide(array $fitGuide, string $path, array &$issues): void
    {
        foreach (['fits', 'not_fits', 'start'] as $columnKey) {
            $columnPath = "{$path}.{$columnKey}";
            if (!is_array($fitGuide[$columnKey] ?? null)) {
                $issues[] = "{$columnPath} must be an object.";
                continue;
            }
            $items = $fitGuide[$columnKey]['items'] ?? null;
            $this->requireArray($fitGuide[$columnKey], 'items', "{$columnPath}.items", $issues, 1);
            $this->validateStringArray(is_array($items) ? $items : [], "{$columnPath}.items", $issues);
        }
    }

    private function validateSections(array $sections, string $path, array &$issues): void
    {
        if (count($sections) < 1) {
            $issues[] = "{$path} must contain at least 1 section.";
            return;
        }

        foreach ($sections as $index => $section) {
            $sectionPath = "{$path}[{$index}]";
            if (!is_array($section)) {
                $issues[] = "{$sectionPath} must be an object.";
                continue;
            }
            if (!$this->isNonEmptyString($section['title'] ?? null) && !$this->isNonEmptyString($section['text'] ?? null)) {
                $issues[] = "{$sectionPath} must have at least title or text.";
            }
            if (array_key_exists('columns_class', $section)) {
                $this->validateColumnsClass((string)$section['columns_class'], "{$sectionPath}.columns_class", $issues);
            }
            if (array_key_exists('cards', $section)) {
                $this->requireArray($section, 'cards', "{$sectionPath}.cards", $issues, 1);
                if (is_array($section['cards'] ?? null)) {
                    $this->validateCardItems($section['cards'], ['title', 'text'], "{$sectionPath}.cards", $issues);
                }
            }
        }
    }

    private function validateColumnsClass(string $value, string $path, array &$issues): void
    {
        $value = trim($value);
        if ($value === '') {
            return;
        }

        if (!in_array($value, self::ALLOWED_COLUMNS_CLASSES, true)) {
            $issues[] = "{$path} must be one of: " . implode(', ', self::ALLOWED_COLUMNS_CLASSES) . '.';
        }
    }

    private function validateArchitecture(array $architecture, string $path, array &$issues): void
    {
        $this->requireStrings($architecture, ['title', 'text'], $path, $issues);
        $this->requireArray($architecture, 'layers', "{$path}.layers", $issues, 1);
        if (!is_array($architecture['layers'] ?? null)) {
            return;
        }

        foreach ($architecture['layers'] as $index => $layer) {
            $layerPath = "{$path}.layers[{$index}]";
            if (!is_array($layer)) {
                $issues[] = "{$layerPath} must be an object.";
                continue;
            }
            $this->requireStrings($layer, ['title', 'text'], $layerPath, $issues);
            $this->requireArray($layer, 'items', "{$layerPath}.items", $issues, 1);
            $this->validateStringArray(is_array($layer['items'] ?? null) ? $layer['items'] : [], "{$layerPath}.items", $issues);
        }
    }

    private function validateUseCases(array $useCases, string $path, array &$issues): void
    {
        $this->requireStrings($useCases, ['title', 'text'], $path, $issues);
        $this->requireArray($useCases, 'items', "{$path}.items", $issues, 3);
        if (!is_array($useCases['items'] ?? null)) {
            return;
        }

        foreach ($useCases['items'] as $index => $item) {
            $itemPath = "{$path}.items[{$index}]";
            if (!is_array($item)) {
                $issues[] = "{$itemPath} must be an object.";
                continue;
            }
            $this->requireStrings($item, ['title', 'trigger', 'owner', 'pilot_input', 'pilot_output', 'limitation'], $itemPath, $issues);
            if (
                array_key_exists('proof_status', $item)
                && $this->isNonEmptyString($item['proof_status'])
                && !in_array((string)$item['proof_status'], self::ALLOWED_PROOF_STATUSES, true)
            ) {
                $issues[] = "{$itemPath}.proof_status has unsupported value: {$item['proof_status']}.";
            }
        }
    }

    private function validateComparison(array $comparison, string $path, array &$issues): void
    {
        $this->requireStrings($comparison, ['title', 'text'], $path, $issues);
        $this->requireArray($comparison, 'columns', "{$path}.columns", $issues, 2);
        if (!is_array($comparison['columns'] ?? null)) {
            return;
        }

        foreach ($comparison['columns'] as $index => $column) {
            $columnPath = "{$path}.columns[{$index}]";
            if (!is_array($column)) {
                $issues[] = "{$columnPath} must be an object.";
                continue;
            }
            $this->requireStrings($column, ['title', 'text'], $columnPath, $issues);
            $this->requireArray($column, 'items', "{$columnPath}.items", $issues, 1);
            $this->validateStringArray(is_array($column['items'] ?? null) ? $column['items'] : [], "{$columnPath}.items", $issues);
            if (array_key_exists('href', $column)) {
                $this->validateUrl((string)$column['href'], "{$columnPath}.href", $issues);
            }
        }
    }

    private function validateProcurement(array $procurement, string $path, array &$issues): void
    {
        $this->requireStrings($procurement, ['title', 'text', 'note_text'], $path, $issues);
        if (array_key_exists('cta_href', $procurement)) {
            $this->validateUrl((string)$procurement['cta_href'], "{$path}.cta_href", $issues);
        }
        $this->requireArray($procurement, 'items', "{$path}.items", $issues, 1);
        $this->validateCardItems(is_array($procurement['items'] ?? null) ? $procurement['items'] : [], ['title', 'text'], "{$path}.items", $issues);
    }

    private function validateRollout(array $rollout, string $path, array &$issues): void
    {
        $this->requireStrings($rollout, ['title', 'text'], $path, $issues);
        $this->requireArray($rollout, 'steps', "{$path}.steps", $issues, 3);
        $this->validateCardItems(is_array($rollout['steps'] ?? null) ? $rollout['steps'] : [], ['title', 'text'], "{$path}.steps", $issues);
    }

    private function validateProof(array $proof, string $path, array &$issues): void
    {
        $this->requireStrings($proof, ['title', 'text'], $path, $issues);
        $this->requireArray($proof, 'items', "{$path}.items", $issues, 3);
        $this->validateCardItems(is_array($proof['items'] ?? null) ? $proof['items'] : [], ['meta', 'title', 'text'], "{$path}.items", $issues);
    }

    private function validateFaq(array $faq, string $path, array &$issues): void
    {
        $this->requireStrings($faq, ['title', 'text'], $path, $issues);
        $this->requireArray($faq, 'items', "{$path}.items", $issues, 3);
        $this->validateCardItems(is_array($faq['items'] ?? null) ? $faq['items'] : [], ['question', 'answer'], "{$path}.items", $issues);
    }

    private function validateCta(string $productCode, array $cta, string $path, array &$issues): void
    {
        $this->requireStrings(
            $cta,
            ['form_id', 'field_prefix', 'title', 'text', 'form_title', 'button_text', 'scenario_label', 'scenario_empty_label'],
            $path,
            $issues
        );
        $this->requireArray($cta, 'scenario_options', "{$path}.scenario_options", $issues, 1);
        $this->validateCardItems(
            is_array($cta['scenario_options'] ?? null) ? $cta['scenario_options'] : [],
            ['VALUE', 'LABEL'],
            "{$path}.scenario_options",
            $issues
        );
        if (is_array($cta['scenario_options'] ?? null)) {
            foreach ($cta['scenario_options'] as $index => $option) {
                if (!is_array($option)) {
                    continue;
                }

                $value = (string)($option['VALUE'] ?? '');
                if (!in_array($value, self::ALLOWED_CTA_SCENARIOS, true)) {
                    $issues[] = "{$path}.scenario_options[{$index}].VALUE has unsupported value: {$value}.";
                }
            }
        }
        $this->requireAssocArray($cta, 'lead_context', "{$path}.lead_context", $issues);
        if (!is_array($cta['lead_context'] ?? null)) {
            return;
        }

        $leadContext = $cta['lead_context'];
        $this->requireStrings(
            $leadContext,
            self::ALLOWED_LEAD_CONTEXT_KEYS,
            "{$path}.lead_context",
            $issues
        );
        foreach ($leadContext as $key => $value) {
            if (!in_array((string)$key, self::ALLOWED_LEAD_CONTEXT_KEYS, true)) {
                $issues[] = "{$path}.lead_context.{$key} is not an allowed key.";
                continue;
            }
            if (is_scalar($value) && !preg_match('/^[a-z0-9_.-]+$/', (string)$value)) {
                $issues[] = "{$path}.lead_context.{$key} must use a slug-like controlled value.";
            }
        }
        if (($leadContext['lead_product'] ?? '') !== $productCode) {
            $issues[] = "{$path}.lead_context.lead_product must match product code {$productCode}.";
        }
    }

    private function validateCardItems(array $items, array $requiredStrings, string $path, array &$issues): void
    {
        foreach ($items as $index => $item) {
            $itemPath = "{$path}[{$index}]";
            if (!is_array($item)) {
                $issues[] = "{$itemPath} must be an object.";
                continue;
            }
            $this->requireStrings($item, $requiredStrings, $itemPath, $issues);
            if (array_key_exists('icon', $item)) {
                $this->validateIconClass((string)$item['icon'], "{$itemPath}.icon", $issues);
            }
        }
    }

    private function validateIconClass(string $value, string $path, array &$issues): void
    {
        $value = trim($value);
        if ($value === '') {
            return;
        }

        if (!preg_match('/^ri-[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            $issues[] = "{$path} must be a single RemixIcon class token.";
        }
    }

    private function validateStringArray(array $items, string $path, array &$issues): void
    {
        foreach ($items as $index => $item) {
            if (!$this->isNonEmptyString($item)) {
                $issues[] = "{$path}[{$index}] must be a non-empty string.";
            }
        }
    }

    private function validateUrl(string $value, string $path, array &$issues): void
    {
        $value = trim($value);
        if (!$this->isNonEmptyString($value)) {
            $issues[] = "{$path} must be a non-empty URL/path string.";
            return;
        }

        if ($this->isSafeUrl($value)) {
            return;
        }

        $issues[] = "{$path} must start with one of: " . implode(', ', self::SAFE_URL_PREFIXES) . '.';
    }

    private function isSafeUrl(string $value): bool
    {
        if ($value === '' || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            return false;
        }

        if (str_starts_with($value, 'https://') || str_starts_with($value, '#')) {
            return true;
        }

        return str_starts_with($value, '/')
            && !str_starts_with($value, '//')
            && !str_starts_with($value, '/\\');
    }

    private function requireStrings(array $data, array $keys, string $path, array &$issues): void
    {
        foreach ($keys as $key) {
            $this->requireString($data, $key, "{$path}.{$key}", $issues);
        }
    }

    private function requireString(array $data, string $key, string $path, array &$issues): void
    {
        if (!$this->isNonEmptyString($data[$key] ?? null)) {
            $issues[] = "{$path} must be a non-empty string.";
        }
    }

    private function requireArray(array $data, string $key, string $path, array &$issues, int $minItems = 0): void
    {
        if (!is_array($data[$key] ?? null)) {
            $issues[] = "{$path} must be an array.";
            return;
        }
        if (count($data[$key]) < $minItems) {
            $issues[] = "{$path} must contain at least {$minItems} item(s).";
        }
    }

    private function requireAssocArray(array $data, string $key, string $path, array &$issues): void
    {
        if (!is_array($data[$key] ?? null)) {
            $issues[] = "{$path} must be an object.";
        }
    }

    private function isNonEmptyString(mixed $value): bool
    {
        return is_scalar($value) && trim((string)$value) !== '';
    }

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id')
            ? tacticum_rest_get_iblock_id($key)
            : 0;
    }

    private function propertyId(int $iblockId, string $code): int
    {
        $property = $this->propertyFields($iblockId, $code);

        return is_array($property) ? (int)$property['ID'] : 0;
    }

    private function propertyFields(int $iblockId, string $code): ?array
    {
        $result = CIBlockProperty::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'CODE' => $code,
            ]
        );
        $property = $result->Fetch();

        return is_array($property) ? $property : null;
    }

    private function propertyLinkIblockId(int $propertyId): int
    {
        $result = CIBlockProperty::GetByID($propertyId);
        $property = $result->Fetch();

        return is_array($property) ? (int)($property['LINK_IBLOCK_ID'] ?? 0) : 0;
    }

    private function warnOrError(string $message): void
    {
        if ($this->strict) {
            $this->error($message);
            return;
        }

        $this->warning($message);
    }

    private function error(string $message): void
    {
        $this->errors[] = $message;
    }

    private function warning(string $message): void
    {
        $this->warnings[] = $message;
    }

    private function line(string $message): void
    {
        if ($this->json) {
            return;
        }

        echo $message . PHP_EOL;
    }

    private function finish(): int
    {
        if ($this->json) {
            $payload = [
                'success' => empty($this->errors),
                'strict' => $this->strict,
                'source_mode' => $this->productSource,
                'configured_source' => $this->configuredProductSource,
                'fallback_allowed' => $this->productFallbackAllowed,
                'cache_ttl' => $this->productCacheTtl,
                'schema_version' => $this->productSchemaVersion,
                'iblocks' => $this->iblocks,
                'admin_model' => $this->adminModel,
                'rows' => $this->rows,
                'warnings' => $this->warnings,
                'errors' => $this->errors,
            ];

            echo json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            ) . PHP_EOL;

            return empty($this->errors) ? 0 : 1;
        }

        if (!empty($this->rows)) {
            $this->line('');
            $this->line('Product content rows:');
            foreach ($this->rows as $row) {
                $missing = empty($row['missing_blocks'])
                    ? '-'
                    : implode(',', $row['missing_blocks']);
                $this->line(sprintf(
                    '- %s: %s, source=%s, use_cases=%d, missing_blocks=%s, schema_issues=%d',
                    $row['code'],
                    $row['status'],
                    $row['source'],
                    $row['use_cases'],
                    $missing,
                    (int)($row['schema_issues'] ?? 0)
                ));
            }
        }

        if (!empty($this->adminModel)) {
            $legacyJson = $this->adminModel['legacy_json'] ?? [];
            $this->line('');
            $this->line('Product content admin model:');
            $this->line('- V2 schema properties: ' . (empty($this->adminModel['v2_schema'] ?? []) ? 'not checked' : 'checked'));
            $this->line('- Legacy JSON usage: products_json_properties=' . (int)($legacyJson['products_json_properties'] ?? 0)
                . ', products_active_json_properties=' . (int)($legacyJson['products_active_json_properties'] ?? 0)
                . ', product_blocks_json_texts=' . (int)($legacyJson['product_blocks_json_texts'] ?? 0)
                . ', product_use_cases_json_texts=' . (int)($legacyJson['product_use_cases_json_texts'] ?? 0));
        }

        if (!empty($this->warnings)) {
            $this->line('');
            $this->line('Warnings:');
            foreach ($this->warnings as $warning) {
                $this->line('- ' . $warning);
            }
        }

        if (!empty($this->errors)) {
            $this->line('');
            $this->line('Errors:');
            foreach ($this->errors as $error) {
                $this->line('- ' . $error);
            }
            return 1;
        }

        $this->line('');
        $this->line($this->strict
            ? 'Product content strict check passed.'
            : 'Product content check passed.');

        return 0;
    }
}

function tacticum_product_content_check_usage(): string
{
    return <<<TEXT
Usage:
  php tools/product-content-check.php [--strict] [--json] [--document-root=/path/to/site]

Default mode fails only on missing core Bitrix product content. Strict mode also fails on missing TO BE blocks, missing use cases, typed product page schema issues and missing product relation properties.
JSON mode prints safe machine-readable release evidence without PII.

TEXT;
}

function tacticum_product_content_check_options(array $argv): array
{
    $options = [
        'strict' => false,
        'json' => false,
        'document_root' => dirname(__DIR__),
        'help' => false,
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $options['help'] = true;
            continue;
        }
        if ($argument === '--strict') {
            $options['strict'] = true;
            continue;
        }
        if ($argument === '--json') {
            $options['json'] = true;
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
    $options = tacticum_product_content_check_options($argv);
    if ($options['help']) {
        echo tacticum_product_content_check_usage();
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

    $check = new TacticumProductContentCheck($documentRoot, (bool)$options['strict'], (bool)$options['json']);
    exit($check->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
