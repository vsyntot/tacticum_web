#!/usr/bin/env php
<?php

declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

const TACTICUM_OFFER_TAXONOMY_IBLOCK_TYPE = 'tacticum_content';
const TACTICUM_OFFER_TAXONOMY_APPROVAL = 'docs/workflow/offer-taxonomy-presets-owner-approval-2026-06-07.approved.json';
const TACTICUM_OFFER_TAXONOMY_EMBEDDED_APPROVAL = 'tools/offer-taxonomy-approved-model.php';

final class TacticumOfferTaxonomyMigration
{
    private array $siteIds = [];
    private string $approvalSource = '';

    public function __construct(private bool $apply, private string $documentRoot, private string $approvalPath) {}

    public function run(): void
    {
        $approval = $this->approval();
        $this->loadBitrix();
        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Bitrix iblock module is not available.');
        }

        $this->siteIds = $this->siteIds();
        $this->ensureIblockType();
        $termsIblockId = $this->ensureIblock('tacticum_offer_taxonomy_terms', 'Tacticum Offer Taxonomy Terms', 150);
        if ($termsIblockId > 0) {
            $this->ensureTermProperties($termsIblockId);
            $this->seedTerms($termsIblockId, (array)$approval['terms']);
        }
        $this->printConfigHints($termsIblockId);
    }

    private function approval(): array
    {
        $path = $this->absolutePath($this->approvalPath);
        if (is_file($path)) {
            $payload = json_decode((string)file_get_contents($path), true);
            $this->approvalSource = $path;
        } elseif ($this->approvalPath === TACTICUM_OFFER_TAXONOMY_APPROVAL) {
            $embeddedPath = $this->absolutePath(TACTICUM_OFFER_TAXONOMY_EMBEDDED_APPROVAL);
            $payload = is_file($embeddedPath) ? require $embeddedPath : null;
            $this->approvalSource = 'embedded:' . TACTICUM_OFFER_TAXONOMY_EMBEDDED_APPROVAL;
        } else {
            throw new RuntimeException('Approval JSON not found: ' . $path);
        }
        if (!is_array($payload) || ($payload['status'] ?? '') !== 'approved') {
            throw new RuntimeException('Approval JSON must have status=approved.');
        }
        if (($payload['runtime_switch_approved'] ?? null) !== false || ($payload['iblock_apply_approved'] ?? null) !== false) {
            throw new RuntimeException('Approval JSON must not approve runtime switch or iblock apply.');
        }
        if (($payload['decisions']['taxonomy_source'] ?? '') !== 'bitrix_terms') {
            throw new RuntimeException('Approval JSON must approve decisions.taxonomy_source=bitrix_terms.');
        }

        return $payload;
    }

    private function absolutePath(string $path): string
    {
        return str_starts_with($path, '/') ? $path : dirname(__DIR__) . '/' . $path;
    }

    private function loadBitrix(): void
    {
        $prolog = $this->documentRoot . '/bitrix/modules/main/include/prolog_before.php';
        if (!is_file($prolog)) {
            throw new RuntimeException('Bitrix prolog_before.php not found under document root: ' . $this->documentRoot);
        }

        $_SERVER['DOCUMENT_ROOT'] = $this->documentRoot;
        define('NO_KEEP_STATISTIC', true);
        define('NOT_CHECK_PERMISSIONS', true);
        require_once $prolog;
    }

    private function line(string $message): void { echo $message . PHP_EOL; }

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
        $siteIds = [];
        $result = CSite::GetList('sort', 'asc', ['ACTIVE' => 'Y']);
        while ($site = $result->Fetch()) {
            $siteId = trim((string)($site['ID'] ?? ''));
            if ($siteId !== '') {
                $siteIds[] = $siteId;
            }
        }

        return $siteIds !== [] ? $siteIds : ['s1'];
    }

    private function ensureIblockType(): void
    {
        if (is_array(CIBlockType::GetByID(TACTICUM_OFFER_TAXONOMY_IBLOCK_TYPE)->Fetch())) {
            $this->line('Iblock type exists: ' . TACTICUM_OFFER_TAXONOMY_IBLOCK_TYPE);
            return;
        }

        $this->action('Create iblock type ' . TACTICUM_OFFER_TAXONOMY_IBLOCK_TYPE, static function (): void {
            $type = new CIBlockType();
            $ok = $type->Add([
                'ID' => TACTICUM_OFFER_TAXONOMY_IBLOCK_TYPE,
                'SECTIONS' => 'N',
                'IN_RSS' => 'N',
                'SORT' => 500,
                'LANG' => [
                    'ru' => ['NAME' => 'Tacticum content', 'SECTION_NAME' => 'Разделы', 'ELEMENT_NAME' => 'Элементы'],
                    'en' => ['NAME' => 'Tacticum content', 'SECTION_NAME' => 'Sections', 'ELEMENT_NAME' => 'Elements'],
                ],
            ]);
            if (!$ok) {
                throw new RuntimeException('Failed to create iblock type.');
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
                'ACTIVE' => 'Y', 'NAME' => $name, 'CODE' => $code, 'XML_ID' => $code,
                'IBLOCK_TYPE_ID' => TACTICUM_OFFER_TAXONOMY_IBLOCK_TYPE, 'SITE_ID' => $this->siteIds,
                'SORT' => $sort, 'GROUP_ID' => ['2' => 'R'], 'INDEX_ELEMENT' => 'N', 'INDEX_SECTION' => 'N', 'VERSION' => 2,
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
        $result = CIBlock::GetList(['ID' => 'ASC'], ['CODE' => $code, 'CHECK_PERMISSIONS' => 'N']);
        $iblock = $result->Fetch();
        return is_array($iblock) ? (int)$iblock['ID'] : 0;
    }

    private function ensureTermProperties(int $iblockId): void
    {
        foreach ([
            $this->stringProperty('DIMENSION', 'Dimension', 100),
            $this->stringProperty('PUBLIC_LABEL', 'Public label', 110),
            $this->stringProperty('SHORT_LABEL', 'Short label', 120),
            $this->stringProperty('ALIASES', 'Aliases', 130, 'Y'),
            $this->stringProperty('FEATURED', 'Featured', 140),
            $this->stringProperty('PRODUCT_FAMILY', 'Product family', 150),
            $this->numberProperty('BUDGET_MIN', 'Budget min', 160),
            $this->numberProperty('BUDGET_MAX', 'Budget max', 170),
        ] as $property) {
            $this->ensureProperty($iblockId, $property);
        }
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
            $id = (int)$property->Add(array_merge($fields, ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y']));
            if ($id <= 0) {
                throw new RuntimeException("Failed to create property {$fields['CODE']}: " . (string)$property->LAST_ERROR);
            }
            return $id;
        });

        return is_int($createdId) ? $createdId : 0;
    }

    private function findPropertyId(int $iblockId, string $code): int
    {
        $result = CIBlockProperty::GetList(['ID' => 'ASC'], ['IBLOCK_ID' => $iblockId, 'CODE' => $code]);
        $property = $result->Fetch();
        return is_array($property) ? (int)$property['ID'] : 0;
    }

    private function seedTerms(int $iblockId, array $terms): void
    {
        foreach ($terms as $term) {
            if (!is_array($term) || ($term['active'] ?? false) !== true) {
                continue;
            }
            $this->seedTerm($iblockId, $term);
        }
    }

    private function seedTerm(int $iblockId, array $term): void
    {
        $dimension = trim((string)($term['dimension'] ?? ''));
        $code = trim((string)($term['code'] ?? ''));
        $label = trim((string)($term['public_label'] ?? ''));
        if ($dimension === '' || $code === '' || $label === '') {
            return;
        }

        $existingId = $this->findTermId($iblockId, $dimension, $code);
        $fields = ['NAME' => $label, 'CODE' => $code, 'XML_ID' => $dimension . ':' . $code, 'SORT' => (int)($term['sort'] ?? 500), 'ACTIVE' => 'Y'];
        $properties = [
            'DIMENSION' => $dimension,
            'PUBLIC_LABEL' => $label,
            'SHORT_LABEL' => trim((string)($term['short_label'] ?? $label)),
            'ALIASES' => array_values((array)($term['aliases'] ?? [])),
            'FEATURED' => ($term['featured'] ?? false) ? 'Y' : 'N',
            'PRODUCT_FAMILY' => trim((string)($term['product_family'] ?? '')),
        ];

        if ($existingId > 0) {
            $this->action("Update term {$dimension}:{$code} (#{$existingId})", static function () use ($iblockId, $existingId, $fields, $properties): void {
                $element = new CIBlockElement();
                if (!$element->Update($existingId, $fields)) {
                    throw new RuntimeException('Failed to update term #' . $existingId . ': ' . (string)$element->LAST_ERROR);
                }
                CIBlockElement::SetPropertyValuesEx($existingId, $iblockId, $properties);
            });
            return;
        }

        $this->action("Create term {$dimension}:{$code}", static function () use ($iblockId, $fields, $properties): void {
            $element = new CIBlockElement();
            $id = (int)$element->Add(array_merge($fields, ['IBLOCK_ID' => $iblockId, 'PROPERTY_VALUES' => $properties]));
            if ($id <= 0) {
                throw new RuntimeException('Failed to create term: ' . (string)$element->LAST_ERROR);
            }
        });
    }

    private function findTermId(int $iblockId, string $dimension, string $code): int
    {
        $result = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, '=CODE' => $code, '=PROPERTY_DIMENSION' => $dimension, 'CHECK_PERMISSIONS' => 'N'], false, ['nTopCount' => 1], ['ID']);
        $row = $result->Fetch();
        return is_array($row) ? (int)$row['ID'] : 0;
    }

    private function stringProperty(string $code, string $name, int $sort, string $multiple = 'N'): array
    {
        return ['NAME' => $name, 'CODE' => $code, 'PROPERTY_TYPE' => 'S', 'MULTIPLE' => $multiple, 'SORT' => $sort];
    }

    private function numberProperty(string $code, string $name, int $sort): array
    {
        return ['NAME' => $name, 'CODE' => $code, 'PROPERTY_TYPE' => 'N', 'MULTIPLE' => 'N', 'SORT' => $sort];
    }

    private function printConfigHints(int $termsIblockId): void
    {
        $this->line('Approval source: ' . $this->approvalSource);
        $this->line('Config registry hint after apply:');
        $this->line("- iblocks.offer_taxonomy_terms => {$termsIblockId}");
        $this->line('- offer.taxonomy_source stays fallback/auto until strict checks and cache clear pass.');
    }
}

function tacticum_offer_taxonomy_migration_options(array $argv): array
{
    $options = ['apply' => false, 'document_root' => dirname(__DIR__), 'approval' => TACTICUM_OFFER_TAXONOMY_APPROVAL, 'help' => false];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') { $options['help'] = true; continue; }
        if ($argument === '--apply') { $options['apply'] = true; continue; }
        if (str_starts_with($argument, '--document-root=')) { $options['document_root'] = substr($argument, 16); continue; }
        if (str_starts_with($argument, '--approval=')) { $options['approval'] = substr($argument, 11); continue; }
        throw new InvalidArgumentException('Unknown argument: ' . $argument);
    }
    $documentRoot = realpath((string)$options['document_root']);
    if ($documentRoot === false) { throw new RuntimeException('Document root does not exist.'); }
    $options['document_root'] = $documentRoot;
    return $options;
}

try {
    $options = tacticum_offer_taxonomy_migration_options($argv);
    if ($options['help']) {
        echo "Usage:\n  php tools/offer-taxonomy-migration.php [--apply] [--approval=approved.json] [--document-root=/path]\n";
        echo "\nDefault approval path may be absent on production deploys; in that case the script uses the embedded approved model from tools/.\n";
        exit(0);
    }
    (new TacticumOfferTaxonomyMigration((bool)$options['apply'], (string)$options['document_root'], (string)$options['approval']))->run();
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
