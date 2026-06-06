#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStorageAudit
{
    private const IBLOCK_KEYS = [
        'offer',
        'vacancies',
        'clients',
        'feedback',
        'faq',
        'rates',
        'services',
        'cases',
        'team',
        'policies',
        'aiagents',
        'page_sections',
        'page_blocks',
        'products',
        'product_blocks',
        'product_use_cases',
    ];

    private const PRODUCT_RELATION_KEYS = [
        'faq',
        'cases',
        'offer',
        'services',
        'aiagents',
        'feedback',
        'clients',
    ];

    private const PUBLIC_API_PATHS = [
        'faq' => '/local/api/faq.php',
        'cases' => '/local/api/cases.php',
        'services' => '/local/api/services.php',
        'rates' => '/local/api/rates.php',
    ];

    private const PAGE_SECTION_REQUIRED_PROPERTIES = [
        'PAGE_KEY' => ['type' => 'S', 'multiple' => 'N'],
        'SECTION_KEY' => ['type' => 'S', 'multiple' => 'N'],
        'TEMPLATE_KEY' => ['type' => 'S', 'multiple' => 'N'],
        'MIGRATION_STATUS' => ['type' => 'S', 'multiple' => 'N'],
    ];

    private const PAGE_BLOCK_REQUIRED_PROPERTIES = [
        'SECTION' => ['type' => 'E', 'multiple' => 'N', 'link' => 'page_sections'],
        'BLOCK_KEY' => ['type' => 'S', 'multiple' => 'N'],
        'ITEM_TYPE' => ['type' => 'S', 'multiple' => 'N'],
    ];

    private string $documentRoot;
    private string $scope;
    private bool $strict;
    private bool $json;
    private string $baseUrl;
    private array $errors = [];
    private array $warnings = [];

    public function __construct(string $documentRoot, string $scope, bool $strict, bool $json, string $baseUrl)
    {
        $this->documentRoot = rtrim($documentRoot, '/');
        $this->scope = $scope;
        $this->strict = $strict;
        $this->json = $json;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function run(): int
    {
        $this->bootstrap();
        if (!Loader::includeModule('iblock')) {
            $this->error('Bitrix iblock module is unavailable.');
            return $this->finish([
                'iblocks' => [],
                'relations' => [],
                'products' => [],
                'page_content_schema' => [],
                'page_content_rows' => [],
                'public_api' => [],
            ]);
        }

        $productsIblockId = $this->iblockId('products');
        $iblocks = $this->iblockSummary();
        $relations = $this->relationSummary($productsIblockId);
        $products = $this->productSummary($productsIblockId, [
            'faq' => $this->iblockId('faq'),
            'cases' => $this->iblockId('cases'),
            'feedback' => $this->iblockId('feedback'),
            'clients' => $this->iblockId('clients'),
            'aiagents' => $this->iblockId('aiagents'),
        ]);
        $pageContentSchema = $this->pageContentSchemaSummary();
        $pageContentRows = $this->pageContentRowsSummary();
        $publicApi = $this->publicApiSummary();

        $this->checkRequiredConfig($iblocks);
        $this->checkRelations($relations);
        $this->checkProductFaq($products);
        $this->checkProductProof($products);
        $this->checkProductAiagents($products, $iblocks);
        $this->checkPageContentSchema($iblocks, $pageContentSchema);
        $this->checkPageContentRows($pageContentRows);
        $this->checkServices($iblocks);

        return $this->finish([
            'iblocks' => $iblocks,
            'relations' => $relations,
            'products' => $products,
            'page_content_schema' => $pageContentSchema,
            'page_content_rows' => $pageContentRows,
            'public_api' => $publicApi,
        ]);
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

    private function iblockSummary(): array
    {
        $summary = [];
        foreach (self::IBLOCK_KEYS as $key) {
            if (!$this->scopeMatches($key)) {
                continue;
            }

            $id = $this->iblockId($key);
            $total = $id > 0 ? $this->elementCount($id, false) : 0;
            $active = $id > 0 ? $this->elementCount($id, true) : 0;
            $summary[$key] = [
                'id' => $id,
                'configured' => $id > 0,
                'elements_total' => $total,
                'elements_active' => $active,
                'inactive_or_filtered' => max(0, $total - $active),
            ];
        }

        return $summary;
    }

    private function relationSummary(int $productsIblockId): array
    {
        $summary = [];
        foreach (self::PRODUCT_RELATION_KEYS as $key) {
            if (!$this->scopeMatches($key) && !$this->scopeMatches('proof')) {
                continue;
            }

            $iblockId = $this->iblockId($key);
            $property = $iblockId > 0 ? $this->propertyFields($iblockId, 'PRODUCT') : null;
            $linkIblockId = $property !== null ? $this->propertyLinkIblockId((int)$property['ID']) : 0;
            $summary[$key] = [
                'iblock_id' => $iblockId,
                'property_present' => $property !== null,
                'property_active' => $property !== null && (($property['ACTIVE'] ?? 'N') === 'Y'),
                'multiple' => (string)($property['MULTIPLE'] ?? ''),
                'link_iblock_id' => $linkIblockId,
                'link_ok' => $productsIblockId > 0 && $linkIblockId === $productsIblockId,
            ];
        }

        return $summary;
    }

    private function productSummary(int $productsIblockId, array $relatedIblockIds): array
    {
        if (
            $productsIblockId <= 0
            || (!$this->scopeMatches('faq') && !$this->scopeMatches('proof') && !$this->scopeMatches('aiagents'))
        ) {
            return [];
        }

        $rows = [];
        foreach ($this->productCodes() as $productCode) {
            $productId = $this->findElementId($productsIblockId, (string)$productCode);
            $row = [
                'code' => (string)$productCode,
                'product_found' => $productId > 0,
            ];

            if ($this->scopeMatches('faq')) {
                $faqIblockId = (int)($relatedIblockIds['faq'] ?? 0);
                $sectionId = $faqIblockId > 0 ? $this->findSectionId($faqIblockId, (string)$productCode) : 0;
                $sectionStats = $productId > 0 && $faqIblockId > 0
                    ? $this->relatedElementSectionStats($faqIblockId, $productId, $sectionId)
                    : ['in_section' => 0, 'without_section' => 0];
                $row += [
                    'faq_section_code' => (string)$productCode,
                    'faq_section_id' => $sectionId,
                    'faq_items' => $productId > 0 && $faqIblockId > 0
                        ? $this->relatedElementCount($faqIblockId, $productId)
                        : 0,
                    'faq_items_in_section' => $sectionStats['in_section'],
                    'faq_items_without_section' => $sectionStats['without_section'],
                ];
            }

            if ($this->scopeMatches('proof')) {
                $cases = $productId > 0 ? $this->relatedElementCount((int)($relatedIblockIds['cases'] ?? 0), $productId) : 0;
                $feedback = $productId > 0 ? $this->relatedElementCount((int)($relatedIblockIds['feedback'] ?? 0), $productId) : 0;
                $clients = $productId > 0 ? $this->relatedElementCount((int)($relatedIblockIds['clients'] ?? 0), $productId) : 0;
                $row += [
                    'cases_items' => $cases,
                    'feedback_items' => $feedback,
                    'clients_items' => $clients,
                    'proof_items_total' => $cases + $feedback + $clients,
                ];
            }

            if ($this->scopeMatches('aiagents')) {
                $row += [
                    'aiagents_items' => $productId > 0
                        ? $this->relatedElementCount((int)($relatedIblockIds['aiagents'] ?? 0), $productId)
                        : 0,
                ];
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function publicApiSummary(): array
    {
        $summary = [];
        foreach (self::PUBLIC_API_PATHS as $key => $path) {
            if (!$this->scopeMatches($key)) {
                continue;
            }

            $summary[$key] = $this->baseUrl === ''
                ? ['checked' => false, 'status' => 0, 'items' => null]
                : $this->fetchPublicApiCount($path);
        }

        return $summary;
    }

    private function pageContentSchemaSummary(): array
    {
        if ($this->scope !== 'page-content') {
            return [];
        }

        $sectionsIblockId = $this->iblockId('page_sections');
        $blocksIblockId = $this->iblockId('page_blocks');

        return [
            'page_sections' => [
                'iblock_id' => $sectionsIblockId,
                'required_properties' => $this->propertySchemaSummary(
                    $sectionsIblockId,
                    self::PAGE_SECTION_REQUIRED_PROPERTIES,
                    []
                ),
            ],
            'page_blocks' => [
                'iblock_id' => $blocksIblockId,
                'required_properties' => $this->propertySchemaSummary(
                    $blocksIblockId,
                    self::PAGE_BLOCK_REQUIRED_PROPERTIES,
                    ['page_sections' => $sectionsIblockId]
                ),
            ],
        ];
    }

    private function propertySchemaSummary(int $iblockId, array $expected, array $linkIblockIds): array
    {
        $rows = [];
        foreach ($expected as $code => $rules) {
            $property = $iblockId > 0 ? $this->propertyFields($iblockId, (string)$code) : null;
            $linkIblockId = $property !== null && (($property['PROPERTY_TYPE'] ?? '') === 'E')
                ? $this->propertyLinkIblockId((int)$property['ID'])
                : 0;
            $expectedLinkKey = (string)($rules['link'] ?? '');
            $expectedLinkIblockId = $expectedLinkKey !== '' ? (int)($linkIblockIds[$expectedLinkKey] ?? 0) : 0;
            $rows[(string)$code] = [
                'present' => $property !== null,
                'active' => $property !== null && (($property['ACTIVE'] ?? 'N') === 'Y'),
                'type' => (string)($property['PROPERTY_TYPE'] ?? ''),
                'expected_type' => (string)($rules['type'] ?? ''),
                'multiple' => (string)($property['MULTIPLE'] ?? ''),
                'expected_multiple' => (string)($rules['multiple'] ?? ''),
                'link_iblock_id' => $linkIblockId,
                'expected_link_iblock_id' => $expectedLinkIblockId,
                'type_ok' => $property !== null && (string)($property['PROPERTY_TYPE'] ?? '') === (string)($rules['type'] ?? ''),
                'multiple_ok' => $property !== null && (string)($property['MULTIPLE'] ?? '') === (string)($rules['multiple'] ?? ''),
                'link_ok' => $expectedLinkKey === ''
                    || ($expectedLinkIblockId > 0 && $linkIblockId === $expectedLinkIblockId),
            ];
        }

        return $rows;
    }

    private function pageContentRowsSummary(): array
    {
        if ($this->scope !== 'page-content') {
            return [];
        }

        $sectionsIblockId = $this->iblockId('page_sections');
        $blocksIblockId = $this->iblockId('page_blocks');
        if ($sectionsIblockId <= 0 || $blocksIblockId <= 0) {
            return [
                'pages' => [],
                'orphan_blocks' => 0,
            ];
        }

        $pages = [];
        $sectionPages = [];
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $sectionsIblockId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'ACTIVE', 'CODE']
        );
        while ($element = $result->Fetch()) {
            $sectionId = (int)($element['ID'] ?? 0);
            if ($sectionId <= 0) {
                continue;
            }
            $pageKey = $this->elementPropertyString($sectionsIblockId, $sectionId, 'PAGE_KEY');
            $sectionKey = $this->elementPropertyString($sectionsIblockId, $sectionId, 'SECTION_KEY');
            $migrationStatus = $this->elementPropertyString($sectionsIblockId, $sectionId, 'MIGRATION_STATUS');
            if ($pageKey === '') {
                $pageKey = 'unknown';
            }
            if ($sectionKey === '') {
                $sectionKey = (string)($element['CODE'] ?? 'unknown');
            }
            if ($migrationStatus === '') {
                $migrationStatus = 'unknown';
            }

            if (!isset($pages[$pageKey])) {
                $pages[$pageKey] = [
                    'sections_total' => 0,
                    'sections_active' => 0,
                    'blocks_total' => 0,
                    'blocks_active' => 0,
                    'migration_statuses' => [],
                    'section_keys' => [],
                ];
            }
            $pages[$pageKey]['sections_total']++;
            if (($element['ACTIVE'] ?? 'N') === 'Y') {
                $pages[$pageKey]['sections_active']++;
            }
            $pages[$pageKey]['migration_statuses'][$migrationStatus] = (int)($pages[$pageKey]['migration_statuses'][$migrationStatus] ?? 0) + 1;
            $pages[$pageKey]['section_keys'][$sectionKey] = [
                'active' => ($element['ACTIVE'] ?? 'N') === 'Y',
                'migration_status' => $migrationStatus,
                'blocks_total' => 0,
                'blocks_active' => 0,
            ];
            $sectionPages[$sectionId] = [$pageKey, $sectionKey];
        }

        $orphanBlocks = 0;
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $blocksIblockId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'ACTIVE']
        );
        while ($element = $result->Fetch()) {
            $blockId = (int)($element['ID'] ?? 0);
            $sectionId = $this->elementPropertyInt($blocksIblockId, $blockId, 'SECTION');
            if ($sectionId <= 0 || !isset($sectionPages[$sectionId])) {
                $orphanBlocks++;
                continue;
            }
            [$pageKey, $sectionKey] = $sectionPages[$sectionId];
            $pages[$pageKey]['blocks_total']++;
            $pages[$pageKey]['section_keys'][$sectionKey]['blocks_total']++;
            if (($element['ACTIVE'] ?? 'N') === 'Y') {
                $pages[$pageKey]['blocks_active']++;
                $pages[$pageKey]['section_keys'][$sectionKey]['blocks_active']++;
            }
        }

        ksort($pages);
        foreach ($pages as &$page) {
            ksort($page['migration_statuses']);
            ksort($page['section_keys']);
        }
        unset($page);

        return [
            'pages' => $pages,
            'orphan_blocks' => $orphanBlocks,
        ];
    }

    private function checkRequiredConfig(array $iblocks): void
    {
        foreach (['clients', 'faq', 'services', 'cases', 'feedback'] as $key) {
            if (!isset($iblocks[$key]) || !$this->scopeMatches($key)) {
                continue;
            }
            if (($iblocks[$key]['id'] ?? 0) <= 0) {
                $this->warnOrError("Missing iblock config key: {$key}.");
            }
        }
    }

    private function checkRelations(array $relations): void
    {
        foreach ($relations as $key => $relation) {
            if (($relation['iblock_id'] ?? 0) <= 0) {
                $this->warnOrError("Relation target {$key} is not configured.");
                continue;
            }
            if (($relation['property_present'] ?? false) !== true) {
                $this->warnOrError("Iblock {$key} has no PRODUCT relation property.");
                continue;
            }
            if (($relation['property_active'] ?? false) !== true) {
                $this->warnOrError("Iblock {$key} PRODUCT relation property is inactive.");
            }
            if (($relation['link_ok'] ?? false) !== true) {
                $this->warnOrError("Iblock {$key} PRODUCT relation does not point to products iblock.");
            }
        }
    }

    private function checkProductFaq(array $products): void
    {
        if (!$this->scopeMatches('faq')) {
            return;
        }

        foreach ($products as $row) {
            if (($row['product_found'] ?? false) !== true) {
                $this->warnOrError('Product ' . (string)($row['code'] ?? 'unknown') . ' is missing.');
                continue;
            }
            if ((int)($row['faq_items'] ?? 0) < 3) {
                $this->warnOrError('Product ' . (string)($row['code'] ?? 'unknown') . ' has fewer than 3 related FAQ items.');
            }
            if ((int)($row['faq_section_id'] ?? 0) <= 0) {
                $this->warnOrError('Product ' . (string)($row['code'] ?? 'unknown') . ' has no FAQ section with matching code.');
                continue;
            }
            if ((int)($row['faq_items_without_section'] ?? 0) > 0) {
                $this->warnOrError('Product ' . (string)($row['code'] ?? 'unknown') . ' has related FAQ items outside its product FAQ section.');
            }
        }
    }

    private function checkProductProof(array $products): void
    {
        if (!$this->scopeMatches('proof')) {
            return;
        }

        foreach ($products as $row) {
            if (($row['product_found'] ?? false) !== true) {
                $this->warnOrError('Product ' . (string)($row['code'] ?? 'unknown') . ' is missing for proof relation audit.');
            }
        }
    }

    private function checkProductAiagents(array $products, array $iblocks): void
    {
        if (!$this->scopeMatches('aiagents')) {
            return;
        }

        $activeAiagents = (int)($iblocks['aiagents']['elements_active'] ?? 0);
        foreach ($products as $row) {
            if (($row['product_found'] ?? false) !== true) {
                $this->warnOrError('Product ' . (string)($row['code'] ?? 'unknown') . ' is missing for aiagents relation audit.');
                continue;
            }

            $code = (string)($row['code'] ?? '');
            $count = (int)($row['aiagents_items'] ?? 0);
            if ($code === 'agents') {
                if ($activeAiagents > 0 && $count < $activeAiagents) {
                    $this->warnOrError("Active aiagents elements are not fully tagged to product agents: {$count}/{$activeAiagents}.");
                }
                continue;
            }

            if ($count > 0) {
                $this->warnOrError("Product {$code} has aiagents demo items, but demo-agent catalog should relate only to agents.");
            }
        }
    }

    private function checkServices(array $iblocks): void
    {
        if (!$this->scopeMatches('services') || !isset($iblocks['services'])) {
            return;
        }

        $active = (int)($iblocks['services']['elements_active'] ?? 0);
        if ($active < 6) {
            $this->warnOrError("Services iblock has fewer than 6 active target service cards: {$active}.");
        }
    }

    private function checkPageContentSchema(array $iblocks, array $schema): void
    {
        if ($this->scope !== 'page-content') {
            return;
        }

        foreach (['page_sections', 'page_blocks'] as $key) {
            if ((int)($iblocks[$key]['id'] ?? 0) <= 0) {
                $this->warnOrError("Missing iblock config key: {$key}.");
            }
        }

        foreach ($schema as $iblockKey => $iblockSchema) {
            foreach (($iblockSchema['required_properties'] ?? []) as $code => $property) {
                if (($property['present'] ?? false) !== true) {
                    $this->warnOrError("Iblock {$iblockKey} has no required {$code} property.");
                    continue;
                }
                if (($property['active'] ?? false) !== true) {
                    $this->warnOrError("Iblock {$iblockKey} property {$code} is inactive.");
                }
                if (($property['type_ok'] ?? false) !== true) {
                    $this->warnOrError("Iblock {$iblockKey} property {$code} has unexpected type.");
                }
                if (($property['multiple_ok'] ?? false) !== true) {
                    $this->warnOrError("Iblock {$iblockKey} property {$code} has unexpected multiplicity.");
                }
                if (($property['link_ok'] ?? false) !== true) {
                    $this->warnOrError("Iblock {$iblockKey} property {$code} has unexpected link target.");
                }
            }
        }
    }

    private function checkPageContentRows(array $rows): void
    {
        if ($this->scope !== 'page-content' || empty($rows)) {
            return;
        }

        if ((int)($rows['orphan_blocks'] ?? 0) > 0) {
            $this->warnOrError('Page-content has blocks without a valid SECTION relation: ' . (int)$rows['orphan_blocks'] . '.');
        }
    }

    private function finish(array $summary): int
    {
        $payload = [
            'success' => empty($this->errors),
            'strict' => $this->strict,
            'scope' => $this->scope,
            'base_url_checked' => $this->baseUrl !== '',
            'iblocks' => $summary['iblocks'],
            'relations' => $summary['relations'],
            'products' => $summary['products'],
            'page_content_schema' => $summary['page_content_schema'],
            'page_content_rows' => $summary['page_content_rows'],
            'public_api' => $summary['public_api'],
            'warnings' => $this->warnings,
            'errors' => $this->errors,
        ];

        if ($this->json) {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

            return empty($this->errors) ? 0 : 1;
        }

        $this->line('Content storage audit');
        $this->line('Scope: ' . $this->scope . ', strict=' . ($this->strict ? 'yes' : 'no'));
        $this->line('Public API base URL checked: ' . ($this->baseUrl !== '' ? 'yes' : 'no'));

        $this->line('');
        $this->line('Iblocks:');
        foreach ($summary['iblocks'] as $key => $row) {
            $this->line(sprintf(
                '- %s: #%d, active=%d, total=%d, inactive_or_filtered=%d',
                $key,
                (int)$row['id'],
                (int)$row['elements_active'],
                (int)$row['elements_total'],
                (int)$row['inactive_or_filtered']
            ));
        }

        $this->line('');
        $this->line('PRODUCT relations:');
        foreach ($summary['relations'] as $key => $row) {
            $this->line(sprintf(
                '- %s: present=%s, active=%s, multiple=%s, link_ok=%s',
                $key,
                !empty($row['property_present']) ? 'yes' : 'no',
                !empty($row['property_active']) ? 'yes' : 'no',
                (string)($row['multiple'] ?? ''),
                !empty($row['link_ok']) ? 'yes' : 'no'
            ));
        }

        if (!empty($summary['products']) && $this->scopeMatches('faq')) {
            $this->line('');
            $this->line('Product FAQ relation counts:');
            foreach ($summary['products'] as $row) {
                $this->line(sprintf(
                    '- %s: product_found=%s, faq_items=%d, section_id=%d, in_section=%d, outside_section=%d',
                    (string)$row['code'],
                    !empty($row['product_found']) ? 'yes' : 'no',
                    (int)$row['faq_items'],
                    (int)($row['faq_section_id'] ?? 0),
                    (int)($row['faq_items_in_section'] ?? 0),
                    (int)($row['faq_items_without_section'] ?? 0)
                ));
            }
        }

        if (!empty($summary['products']) && $this->scopeMatches('proof')) {
            $this->line('');
            $this->line('Product proof relation counts:');
            foreach ($summary['products'] as $row) {
                $this->line(sprintf(
                    '- %s: product_found=%s, cases=%d, feedback=%d, clients=%d, total=%d',
                    (string)$row['code'],
                    !empty($row['product_found']) ? 'yes' : 'no',
                    (int)($row['cases_items'] ?? 0),
                    (int)($row['feedback_items'] ?? 0),
                    (int)($row['clients_items'] ?? 0),
                    (int)($row['proof_items_total'] ?? 0)
                ));
            }
        }

        if (!empty($summary['products']) && $this->scopeMatches('aiagents')) {
            $this->line('');
            $this->line('Product aiagents relation counts:');
            foreach ($summary['products'] as $row) {
                $this->line(sprintf(
                    '- %s: product_found=%s, aiagents=%d',
                    (string)$row['code'],
                    !empty($row['product_found']) ? 'yes' : 'no',
                    (int)($row['aiagents_items'] ?? 0)
                ));
            }
        }

        if (!empty($summary['page_content_schema']) && $this->scope === 'page-content') {
            $this->line('');
            $this->line('Page-content schema:');
            foreach ($summary['page_content_schema'] as $iblockKey => $row) {
                $this->line(sprintf('- %s: iblock #%d', $iblockKey, (int)($row['iblock_id'] ?? 0)));
                foreach (($row['required_properties'] ?? []) as $code => $property) {
                    $this->line(sprintf(
                        '  - %s: present=%s, active=%s, type_ok=%s, multiple_ok=%s, link_ok=%s',
                        (string)$code,
                        !empty($property['present']) ? 'yes' : 'no',
                        !empty($property['active']) ? 'yes' : 'no',
                        !empty($property['type_ok']) ? 'yes' : 'no',
                        !empty($property['multiple_ok']) ? 'yes' : 'no',
                        !empty($property['link_ok']) ? 'yes' : 'no'
                    ));
                }
            }
        }

        if (!empty($summary['page_content_rows']) && $this->scope === 'page-content') {
            $this->line('');
            $this->line('Page-content rows:');
            $this->line('- orphan_blocks=' . (int)($summary['page_content_rows']['orphan_blocks'] ?? 0));
            foreach (($summary['page_content_rows']['pages'] ?? []) as $pageKey => $row) {
                $this->line(sprintf(
                    '- %s: sections=%d/%d, blocks=%d/%d, statuses=%s',
                    (string)$pageKey,
                    (int)($row['sections_active'] ?? 0),
                    (int)($row['sections_total'] ?? 0),
                    (int)($row['blocks_active'] ?? 0),
                    (int)($row['blocks_total'] ?? 0),
                    $this->statusList($row['migration_statuses'] ?? [])
                ));
            }
        }

        if (!empty($summary['public_api'])) {
            $this->line('');
            $this->line('Public API counts:');
            foreach ($summary['public_api'] as $key => $row) {
                $this->line(sprintf(
                    '- %s: checked=%s, status=%d, items=%s',
                    $key,
                    !empty($row['checked']) ? 'yes' : 'no',
                    (int)($row['status'] ?? 0),
                    $row['items'] === null ? '-' : (string)$row['items']
                ));
            }
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
        }

        return empty($this->errors) ? 0 : 1;
    }

    private function fetchPublicApiCount(string $path): array
    {
        $url = $this->baseUrl . $path;
        $context = stream_context_create([
            'http' => [
                'timeout' => 8,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        $status = 0;
        foreach (($http_response_header ?? []) as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d+)/', (string)$header, $match)) {
                $status = (int)$match[1];
                break;
            }
        }

        $decoded = is_string($body) ? json_decode($body, true) : null;
        $items = null;
        if (is_array($decoded)) {
            $payload = is_array($decoded['data'] ?? null) ? $decoded['data'] : $decoded;
            if (is_array($payload['items'] ?? null)) {
                $items = count($payload['items']);
            }
        }

        return [
            'checked' => true,
            'status' => $status,
            'items' => $items,
        ];
    }

    private function elementCount(int $iblockId, bool $activeOnly): int
    {
        $filter = [
            'IBLOCK_ID' => $iblockId,
            'CHECK_PERMISSIONS' => 'N',
        ];
        if ($activeOnly) {
            $filter['ACTIVE'] = 'Y';
        }

        $count = 0;
        $result = CIBlockElement::GetList(['ID' => 'ASC'], $filter, false, false, ['ID']);
        while ($result->Fetch()) {
            $count++;
        }

        return $count;
    }

    private function relatedElementCount(int $iblockId, int $productId): int
    {
        if ($iblockId <= 0 || $productId <= 0) {
            return 0;
        }

        $count = 0;
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'PROPERTY_PRODUCT' => $productId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID']
        );
        while ($result->Fetch()) {
            $count++;
        }

        return $count;
    }

    private function relatedElementSectionStats(int $iblockId, int $productId, int $expectedSectionId): array
    {
        $inSection = 0;
        $withoutSection = 0;
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'PROPERTY_PRODUCT' => $productId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID']
        );
        while ($element = $result->Fetch()) {
            $sectionIds = $this->elementSectionIds((int)$element['ID']);
            if ($expectedSectionId > 0 && in_array($expectedSectionId, $sectionIds, true)) {
                $inSection++;
                continue;
            }

            $withoutSection++;
        }

        return [
            'in_section' => $inSection,
            'without_section' => $withoutSection,
        ];
    }

    private function elementSectionIds(int $elementId): array
    {
        if ($elementId <= 0) {
            return [];
        }

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

    private function findElementId(int $iblockId, string $code): int
    {
        if ($iblockId <= 0 || $code === '') {
            return 0;
        }

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

    private function findSectionId(int $iblockId, string $code): int
    {
        if ($iblockId <= 0 || $code === '') {
            return 0;
        }

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

    private function elementPropertyString(int $iblockId, int $elementId, string $code): string
    {
        $result = CIBlockElement::GetProperty(
            $iblockId,
            $elementId,
            ['sort' => 'asc', 'id' => 'asc'],
            ['CODE' => $code]
        );
        $property = $result->Fetch();
        $value = is_array($property) ? $property['VALUE'] ?? '' : '';

        return trim((string)$value);
    }

    private function elementPropertyInt(int $iblockId, int $elementId, string $code): int
    {
        return (int)$this->elementPropertyString($iblockId, $elementId, $code);
    }

    private function statusList(array $statuses): string
    {
        if (empty($statuses)) {
            return '-';
        }

        $parts = [];
        foreach ($statuses as $status => $count) {
            $parts[] = (string)$status . ':' . (int)$count;
        }

        return implode(',', $parts);
    }

    private function productCodes(): array
    {
        if (function_exists('tacticum_product_content_codes')) {
            return array_keys(tacticum_product_content_codes());
        }

        return ['platform', 'agents', 'dev', 'forum'];
    }

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id($key) : 0;
    }

    private function scopeMatches(string $key): bool
    {
        if ($this->scope === 'all') {
            return true;
        }
        if ($this->scope === 'proof') {
            return in_array($key, ['cases', 'feedback', 'clients', 'proof'], true);
        }
        if ($this->scope === 'page-content') {
            return in_array($key, ['page_sections', 'page_blocks', 'page-content'], true);
        }

        return $this->scope === $key;
    }

    private function warnOrError(string $message): void
    {
        if ($this->strict) {
            $this->error($message);
            return;
        }

        $this->warnings[] = $message;
    }

    private function error(string $message): void
    {
        $this->errors[] = $message;
    }

    private function line(string $message): void
    {
        if (!$this->json) {
            echo $message . PHP_EOL;
        }
    }
}

function tacticum_content_storage_audit_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-audit.php [--scope=all|faq|services|cases|feedback|clients|aiagents|proof|page-content] [--strict] [--json] [--base-url=https://tacticum.ru] [--document-root=/path/to/site]

Prints safe aggregate content-storage evidence: configured iblocks, active/total
counts, PRODUCT relation coverage, product FAQ/proof/aiagents relation counts,
page-content schema readiness and optional
public API item counts. It does not print element names, raw properties or PII.

TEXT;
}

function tacticum_content_storage_audit_options(array $argv): array
{
    $options = [
        'document_root' => dirname(__DIR__),
        'scope' => 'all',
        'strict' => false,
        'json' => false,
        'base_url' => (string)(getenv('TACTICUM_CONTENT_AUDIT_BASE_URL') ?: ''),
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
        if (str_starts_with($argument, '--scope=')) {
            $options['scope'] = substr($argument, strlen('--scope='));
            continue;
        }
        if (str_starts_with($argument, '--base-url=')) {
            $options['base_url'] = substr($argument, strlen('--base-url='));
            continue;
        }
        if (str_starts_with($argument, '--document-root=')) {
            $options['document_root'] = substr($argument, strlen('--document-root='));
            continue;
        }

        throw new InvalidArgumentException('Unknown argument: ' . $argument);
    }

    $allowedScopes = ['all', 'faq', 'services', 'cases', 'feedback', 'clients', 'aiagents', 'proof', 'page-content'];
    if (!in_array($options['scope'], $allowedScopes, true)) {
        throw new InvalidArgumentException('Unknown scope: ' . (string)$options['scope']);
    }

    $baseUrl = rtrim(trim((string)$options['base_url']), '/');
    if ($baseUrl !== '') {
        $scheme = strtolower((string)parse_url($baseUrl, PHP_URL_SCHEME));
        if ($scheme !== 'https') {
            throw new InvalidArgumentException('--base-url must use HTTPS.');
        }
    }
    $options['base_url'] = $baseUrl;

    $documentRoot = realpath((string)$options['document_root']);
    if ($documentRoot === false) {
        throw new RuntimeException('Document root does not exist: ' . (string)$options['document_root']);
    }
    $options['document_root'] = $documentRoot;

    return $options;
}

try {
    $options = tacticum_content_storage_audit_options($argv);
    if ($options['help']) {
        echo tacticum_content_storage_audit_usage();
        exit(0);
    }

    $audit = new TacticumContentStorageAudit(
        (string)$options['document_root'],
        (string)$options['scope'],
        (bool)$options['strict'],
        (bool)$options['json'],
        (string)$options['base_url']
    );
    exit($audit->run());
} catch (Throwable $exception) {
    if (in_array('--json', $argv ?? [], true)) {
        echo json_encode([
            'success' => false,
            'strict' => in_array('--strict', $argv ?? [], true),
            'scope' => 'unknown',
            'warnings' => [],
            'errors' => [$exception->getMessage()],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
        exit(1);
    }

    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
