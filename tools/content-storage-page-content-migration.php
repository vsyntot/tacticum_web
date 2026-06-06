#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

const TACTICUM_PAGE_CONTENT_IBLOCK_TYPE = 'tacticum_content';
const TACTICUM_PAGE_CONTENT_MODEL_SCHEMA = 'tacticum.content_storage.page_content_model.v1';
const TACTICUM_PAGE_CONTENT_DEFAULT_MODEL = 'docs/workflow/content-storage-page-content-model-2026-06-05.draft.json';

final class TacticumContentStoragePageContentMigration
{
    private bool $apply;
    private string $modelPath;
    private string $documentRoot;
    private array $siteIds = [];
    private array $summary = [
        'iblocks_created' => 0,
        'iblocks_existing' => 0,
        'properties_created' => 0,
        'properties_existing' => 0,
        'properties_skipped' => 0,
    ];

    public function __construct(bool $apply, string $modelPath, string $documentRoot)
    {
        $this->apply = $apply;
        $this->modelPath = $modelPath;
        $this->documentRoot = rtrim($documentRoot, '/');
    }

    public function run(): void
    {
        $modelInfo = $this->loadModel();
        $model = $modelInfo['model'];
        $this->validateModel($model);
        if ($this->apply && !$this->isApprovedModel($model)) {
            throw new RuntimeException('Page-content model must be status=approved with all owner gates true before --apply.');
        }

        $this->bootstrap();
        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Bitrix iblock module is unavailable.');
        }

        $this->line('Content storage page-content migration');
        $this->line('Mode: ' . ($this->apply ? 'apply' : 'dry-run'));
        $this->line('Model source: ' . $modelInfo['source']);
        $this->line('Model status: ' . (string)$model['status']);
        if (!$this->apply) {
            $this->line('Dry-run only: --apply requires an approved owner-gated model.');
        }
        $this->line('');

        $this->siteIds = $this->siteIds();
        $this->ensureIblockType();

        $sectionsIblockId = $this->ensureIblock('tacticum_page_sections', 'Tacticum Page Sections', 130);
        $blocksIblockId = $this->ensureIblock('tacticum_page_blocks', 'Tacticum Page Blocks', 140);

        if ($sectionsIblockId > 0) {
            $this->ensureSectionProperties($sectionsIblockId);
        } elseif (!$this->apply) {
            $this->previewProperties('tacticum_page_sections', $this->sectionProperties());
        } else {
            $this->summary['properties_skipped'] += count($this->sectionProperties());
            $this->line('Page section properties skipped until page_sections iblock exists.');
        }

        if ($sectionsIblockId > 0 && $blocksIblockId > 0) {
            $this->ensureBlockProperties($blocksIblockId, $sectionsIblockId);
        } elseif (!$this->apply) {
            $this->previewProperties('tacticum_page_blocks', $this->blockProperties(0));
        } else {
            $this->summary['properties_skipped'] += count($this->blockProperties(0));
            $this->line('Page block properties skipped until page_sections/page_blocks iblocks exist.');
        }

        $this->line('');
        $this->line('Page-content migration summary:'
            . ' iblocks_created=' . $this->summary['iblocks_created']
            . ', iblocks_existing=' . $this->summary['iblocks_existing']
            . ', properties_created=' . $this->summary['properties_created']
            . ', properties_existing=' . $this->summary['properties_existing']
            . ', properties_skipped=' . $this->summary['properties_skipped']
            . ', mode=' . ($this->apply ? 'apply' : 'dry-run'));

        $this->line('');
        $this->line('Config registry hints for local/php_interface/include/tacticum_config.php:');
        $this->line("'page_sections' => {$sectionsIblockId},");
        $this->line("'page_blocks' => {$blocksIblockId},");
        $this->line('');
        $this->line('Runtime switch: unchanged. Public pages must keep PHP fallback until page-level owner approval, seed evidence and smoke checks pass.');
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

    private function loadModel(): array
    {
        $path = $this->modelPath;
        $isDefault = $path === TACTICUM_PAGE_CONTENT_DEFAULT_MODEL
            || str_ends_with($path, '/' . TACTICUM_PAGE_CONTENT_DEFAULT_MODEL);
        if (!is_file($path)) {
            if (!$isDefault) {
                throw new RuntimeException('Page-content model file not found: ' . $path);
            }

            return [
                'source' => 'embedded default draft; ' . $path . ' is not present',
                'model' => $this->defaultDraftModel(),
            ];
        }

        $decoded = json_decode((string)file_get_contents($path), true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Page-content model JSON is invalid: ' . $path);
        }

        return [
            'source' => $path,
            'model' => $decoded,
        ];
    }

    private function defaultDraftModel(): array
    {
        return [
            'schema' => TACTICUM_PAGE_CONTENT_MODEL_SCHEMA,
            'status' => 'draft',
            'release_evidence' => false,
            'decision_owner_required' => true,
            'storage' => [
                'recommended' => [
                    'section_iblock_key' => 'page_sections',
                    'block_iblock_key' => 'page_blocks',
                ],
            ],
            'section_schema' => [
                'iblock_key' => 'page_sections',
                'required_fields' => ['PAGE_KEY', 'SECTION_KEY', 'TEMPLATE_KEY', 'MIGRATION_STATUS'],
            ],
            'block_schema' => [
                'iblock_key' => 'page_blocks',
                'required_fields' => ['SECTION', 'BLOCK_KEY', 'ITEM_TYPE'],
            ],
            'owner_gates' => [
                'architect' => false,
                'content' => false,
                'frontend' => false,
                'qa' => false,
                'seo' => false,
            ],
        ];
    }

    private function validateModel(array $model): void
    {
        $errors = [];
        if (($model['schema'] ?? '') !== TACTICUM_PAGE_CONTENT_MODEL_SCHEMA) {
            $errors[] = 'schema must be ' . TACTICUM_PAGE_CONTENT_MODEL_SCHEMA . '.';
        }
        if (!in_array(($model['status'] ?? ''), ['draft', 'approved'], true)) {
            $errors[] = 'status must be draft or approved.';
        }
        if (($model['release_evidence'] ?? false) !== false) {
            $errors[] = 'release_evidence must be false.';
        }
        if (($model['decision_owner_required'] ?? true) !== true) {
            $errors[] = 'decision_owner_required must be true.';
        }

        $recommended = is_array($model['storage']['recommended'] ?? null) ? $model['storage']['recommended'] : [];
        if (($recommended['section_iblock_key'] ?? '') !== 'page_sections') {
            $errors[] = 'storage.recommended.section_iblock_key must be page_sections.';
        }
        if (($recommended['block_iblock_key'] ?? '') !== 'page_blocks') {
            $errors[] = 'storage.recommended.block_iblock_key must be page_blocks.';
        }

        $sectionSchema = is_array($model['section_schema'] ?? null) ? $model['section_schema'] : [];
        if (($sectionSchema['iblock_key'] ?? '') !== 'page_sections') {
            $errors[] = 'section_schema.iblock_key must be page_sections.';
        }
        $this->requireFields($sectionSchema['required_fields'] ?? [], ['PAGE_KEY', 'SECTION_KEY', 'TEMPLATE_KEY', 'MIGRATION_STATUS'], 'section_schema.required_fields', $errors);

        $blockSchema = is_array($model['block_schema'] ?? null) ? $model['block_schema'] : [];
        if (($blockSchema['iblock_key'] ?? '') !== 'page_blocks') {
            $errors[] = 'block_schema.iblock_key must be page_blocks.';
        }
        $this->requireFields($blockSchema['required_fields'] ?? [], ['SECTION', 'BLOCK_KEY', 'ITEM_TYPE'], 'block_schema.required_fields', $errors);

        if (($model['status'] ?? '') === 'approved' && !$this->isApprovedModel($model)) {
            $errors[] = 'approved model requires architect/content/frontend/qa/seo owner gates true.';
        }

        if (!empty($errors)) {
            throw new RuntimeException("Page-content model check failed:\n- " . implode("\n- ", $errors));
        }
    }

    private function requireFields(mixed $actual, array $required, string $path, array &$errors): void
    {
        $set = array_fill_keys(is_array($actual) ? array_map('strval', $actual) : [], true);
        foreach ($required as $field) {
            if (!isset($set[$field])) {
                $errors[] = "{$path} must include {$field}.";
            }
        }
    }

    private function isApprovedModel(array $model): bool
    {
        if (($model['status'] ?? '') !== 'approved') {
            return false;
        }
        $gates = is_array($model['owner_gates'] ?? null) ? $model['owner_gates'] : [];
        foreach (['architect', 'content', 'frontend', 'qa', 'seo'] as $owner) {
            if (($gates[$owner] ?? false) !== true) {
                return false;
            }
        }

        return true;
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
        $existing = CIBlockType::GetByID(TACTICUM_PAGE_CONTENT_IBLOCK_TYPE)->Fetch();
        if (is_array($existing)) {
            $this->line('Iblock type exists: ' . TACTICUM_PAGE_CONTENT_IBLOCK_TYPE);
            return;
        }

        $this->action('Create iblock type ' . TACTICUM_PAGE_CONTENT_IBLOCK_TYPE, static function (): void {
            $type = new CIBlockType();
            $result = $type->Add([
                'ID' => TACTICUM_PAGE_CONTENT_IBLOCK_TYPE,
                'SECTIONS' => 'N',
                'IN_RSS' => 'N',
                'SORT' => 500,
                'LANG' => [
                    'ru' => [
                        'NAME' => 'Tacticum content',
                        'SECTION_NAME' => 'Разделы',
                        'ELEMENT_NAME' => 'Элементы',
                    ],
                    'en' => [
                        'NAME' => 'Tacticum content',
                        'SECTION_NAME' => 'Sections',
                        'ELEMENT_NAME' => 'Elements',
                    ],
                ],
            ]);

            if (!$result) {
                throw new RuntimeException('Failed to create iblock type ' . TACTICUM_PAGE_CONTENT_IBLOCK_TYPE);
            }
        });
    }

    private function ensureIblock(string $code, string $name, int $sort): int
    {
        $id = $this->findIblockId($code);
        if ($id > 0) {
            $this->summary['iblocks_existing']++;
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
                'IBLOCK_TYPE_ID' => TACTICUM_PAGE_CONTENT_IBLOCK_TYPE,
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

        if (is_int($createdId) && $createdId > 0) {
            $this->summary['iblocks_created']++;
            return $createdId;
        }
        $this->summary['iblocks_created']++;

        return 0;
    }

    private function ensureSectionProperties(int $sectionsIblockId): void
    {
        foreach ($this->sectionProperties() as $property) {
            $this->ensureProperty($sectionsIblockId, $property);
        }
    }

    private function ensureBlockProperties(int $blocksIblockId, int $sectionsIblockId): void
    {
        foreach ($this->blockProperties($sectionsIblockId) as $property) {
            $this->ensureProperty($blocksIblockId, $property);
        }
    }

    private function previewProperties(string $iblockCode, array $properties): void
    {
        foreach ($properties as $property) {
            $code = (string)($property['CODE'] ?? '');
            if ($code === '') {
                continue;
            }

            $this->summary['properties_created']++;
            $this->line("[dry-run] Create property {$code} on iblock {$iblockCode} after iblock creation");
        }
    }

    private function sectionProperties(): array
    {
        return [
            $this->stringProperty('PAGE_KEY', 'Page key', 100),
            $this->stringProperty('SECTION_KEY', 'Section key', 110),
            $this->stringProperty('TEMPLATE_KEY', 'Template key', 120),
            $this->stringProperty('MIGRATION_STATUS', 'Migration status', 130),
            $this->stringProperty('EYEBROW', 'Eyebrow', 140),
            $this->stringProperty('TITLE', 'Title', 150),
            $this->textProperty('TEXT', 'Text', 160),
            $this->stringProperty('THEME', 'Theme', 170),
            $this->stringProperty('TONE', 'Tone', 180),
            $this->stringProperty('CTA_TEXT', 'CTA text', 190),
            $this->stringProperty('CTA_HREF', 'CTA href', 200),
            $this->stringProperty('FALLBACK_PARTIAL', 'Fallback partial', 210),
            $this->stringProperty('OWNER_SCOPE', 'Owner scope', 220),
        ];
    }

    private function blockProperties(int $sectionsIblockId): array
    {
        return [
            $this->elementProperty('SECTION', 'Page section', 100, $sectionsIblockId),
            $this->stringProperty('BLOCK_KEY', 'Block key', 110),
            $this->stringProperty('ITEM_TYPE', 'Item type', 120),
            $this->stringProperty('TITLE', 'Title', 130),
            $this->textProperty('TEXT', 'Text', 140),
            $this->stringProperty('ICON', 'Icon', 150),
            $this->stringProperty('HREF', 'Href', 160),
            $this->stringProperty('META', 'Meta', 170),
            $this->stringProperty('VALUE', 'Value', 180),
            $this->stringProperty('LABEL', 'Label', 190),
            $this->stringProperty('TONE', 'Tone', 200),
            $this->stringProperty('PROOF_STATUS', 'Proof status', 210),
        ];
    }

    private function ensureProperty(int $iblockId, array $fields): int
    {
        $code = (string)$fields['CODE'];
        $existingId = $this->findPropertyId($iblockId, $code);
        if ($existingId > 0) {
            $this->summary['properties_existing']++;
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

        $this->summary['properties_created']++;

        return is_int($createdId) ? $createdId : 0;
    }

    private function stringProperty(string $code, string $name, int $sort): array
    {
        return [
            'NAME' => $name,
            'CODE' => $code,
            'SORT' => $sort,
            'PROPERTY_TYPE' => 'S',
            'MULTIPLE' => 'N',
        ];
    }

    private function textProperty(string $code, string $name, int $sort): array
    {
        return array_merge($this->stringProperty($code, $name, $sort), [
            'ROW_COUNT' => 4,
            'COL_COUNT' => 80,
        ]);
    }

    private function elementProperty(string $code, string $name, int $sort, int $linkIblockId): array
    {
        return [
            'NAME' => $name,
            'CODE' => $code,
            'SORT' => $sort,
            'PROPERTY_TYPE' => 'E',
            'LINK_IBLOCK_ID' => $linkIblockId,
            'MULTIPLE' => 'N',
        ];
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
}

function tacticum_content_storage_page_content_migration_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-page-content-migration.php [--model=path/to/model.json] [--apply] [--document-root=/path/to/site]

Creates the structural page_sections/page_blocks iblock schema for generic page
sections. Dry-run is the default. --apply requires status=approved and all
architect/content/frontend/qa/seo owner gates set to true in the model JSON.
The tool does not seed page copy and does not change public runtime rendering.

TEXT;
}

$apply = false;
$modelPath = TACTICUM_PAGE_CONTENT_DEFAULT_MODEL;
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) && trim((string)$_SERVER['DOCUMENT_ROOT']) !== ''
    ? (string)$_SERVER['DOCUMENT_ROOT']
    : dirname(__DIR__);

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        echo tacticum_content_storage_page_content_migration_usage();
        exit(0);
    }
    if ($argument === '--apply') {
        $apply = true;
        continue;
    }
    if (str_starts_with($argument, '--model=')) {
        $modelPath = substr($argument, strlen('--model='));
        continue;
    }
    if (str_starts_with($argument, '--document-root=')) {
        $documentRoot = substr($argument, strlen('--document-root='));
        continue;
    }

    fwrite(STDERR, "Unknown argument: {$argument}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_page_content_migration_usage());
    exit(2);
}

try {
    $tool = new TacticumContentStoragePageContentMigration($apply, $modelPath, $documentRoot);
    $tool->run();
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_page_content_migration_usage());
    exit(1);
}
