#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumProductDevToAdpMigration
{
    private const LEGACY_CODE = 'dev';
    private const ACTIVE_CODE = 'adp';
    private const PRODUCT_XML_ID = 'tacticum-product-dev';
    private const IBLOCK_KEYS = [
        'products',
        'product_blocks',
        'product_use_cases',
        'faq',
        'cases',
        'feedback',
        'clients',
        'page_blocks',
    ];
    /**
     * Product pages can mention another product by name. Those references
     * must follow a product rename even when their owning element belongs to
     * a different product.
     */
    private const GLOBAL_RENAME_IBLOCK_KEYS = [
        'product_blocks',
        'product_use_cases',
        'faq',
        'cases',
        'feedback',
        'clients',
        'page_blocks',
    ];

    public function __construct(
        private readonly bool $apply,
        private readonly bool $json,
        private readonly string $documentRoot,
    ) {
    }

    public function run(): int
    {
        $this->bootstrap();

        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Bitrix iblock module is unavailable.');
        }

        $productIblockId = $this->iblockId('products');
        if ($productIblockId <= 0) {
            throw new RuntimeException('Configured products iblock is missing.');
        }

        $legacyProduct = $this->findProduct($productIblockId, self::LEGACY_CODE);
        $activeProduct = $this->findProduct($productIblockId, self::ACTIVE_CODE);
        if ($legacyProduct === null && $activeProduct === null) {
            throw new RuntimeException('Neither legacy dev product nor active adp product exists.');
        }
        if ($legacyProduct !== null && $activeProduct !== null && (int)$activeProduct['ID'] !== (int)$legacyProduct['ID']) {
            throw new RuntimeException('An independent adp product already exists; refusing to merge records.');
        }

        $productId = (int)($legacyProduct ?? $activeProduct)['ID'];
        $candidates = $this->collectCandidates($productId);
        $plan = [];
        foreach ($candidates as $candidate) {
            $change = $this->buildChange($candidate);
            if ($change !== null) {
                $plan[] = $change;
            }
        }

        if ($this->apply) {
            foreach ($plan as $change) {
                $this->applyChange($change);
            }
        }

        $summary = [
            'legacy_product_id' => $productId,
            'legacy_product_code' => self::LEGACY_CODE,
            'active_product_code' => self::ACTIVE_CODE,
            'candidates' => count($candidates),
            'changes' => count($plan),
            'mode' => $this->apply ? 'apply' : 'dry-run',
            'xml_id_policy' => 'preserve_existing_xml_id',
            'writes_dev' => false,
            'already_migrated' => $legacyProduct === null && $plan === [],
        ];

        return $this->finish($plan, $summary);
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
    }

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id')
            ? (int)tacticum_rest_get_iblock_id($key)
            : 0;
    }

    private function findProduct(int $iblockId, string $code): ?array
    {
        $baseFilter = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'CHECK_PERMISSIONS' => 'N',
        ];
        $select = ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID', 'PREVIEW_TEXT', 'DETAIL_TEXT'];

        $byCode = $this->findProductRows($baseFilter + ['=CODE' => $code], $select);
        if (count($byCode) > 1) {
            throw new RuntimeException('Product lookup is ambiguous for code ' . $code . '.');
        }
        if ($byCode !== []) {
            return $byCode[0];
        }

        // The preserved legacy XML_ID belongs to both pre/post migration states.
        // It must not make an ADP row look like a DEV row (or hide a collision).
        $byXmlId = $this->findProductRows($baseFilter + ['=XML_ID' => self::PRODUCT_XML_ID, '=CODE' => $code], $select);
        if (count($byXmlId) > 1) {
            throw new RuntimeException('Product lookup is ambiguous for XML_ID ' . self::PRODUCT_XML_ID . '.');
        }

        return $byXmlId[0] ?? null;
    }

    private function findProductRows(array $filter, array $select): array
    {
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            $filter,
            false,
            ['nTopCount' => 2],
            $select
        );
        $rows = [];
        while ($row = $result->Fetch()) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function collectCandidates(int $productId): array
    {
        $candidates = [];
        foreach (self::IBLOCK_KEYS as $key) {
            $iblockId = $this->iblockId($key);
            if ($iblockId <= 0) {
                continue;
            }

            $propertyCodes = $this->propertyCodes($iblockId);
            $globalRenameScan = in_array($key, self::GLOBAL_RENAME_IBLOCK_KEYS, true);
            $filter = [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'N',
            ];
            if ($key === 'products') {
                $filter['ID'] = $productId;
            }
            if (!$globalRenameScan && isset($propertyCodes['PRODUCT'])) {
                $filter['PROPERTY_PRODUCT'] = $productId;
            }

            $result = CIBlockElement::GetList(
                ['ID' => 'ASC'],
                $filter,
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID', 'PREVIEW_TEXT', 'DETAIL_TEXT']
            );
            while ($element = $result->Fetch()) {
                $elementId = (int)($element['ID'] ?? 0);
                if ($elementId <= 0) {
                    continue;
                }
                $element['_properties'] = $this->properties($iblockId, $elementId);
                if ($key !== 'products' && !$globalRenameScan && !$this->isProductScoped($element, $propertyCodes, $productId)) {
                    continue;
                }
                $element['_iblock_key'] = $key;
                $element['_property_codes'] = $propertyCodes;
                $candidates[$iblockId . ':' . $elementId] = $element;
            }
        }

        return array_values($candidates);
    }

    private function isProductScoped(array $element, array $propertyCodes, int $productId): bool
    {
        $properties = $element['_properties'] ?? [];
        if (isset($propertyCodes['PRODUCT']) && $this->containsValue($properties['PRODUCT'] ?? null, (string)$productId)) {
            return true;
        }

        foreach (['PRODUCT_CODE', 'LEAD_PRODUCT', 'CTA_LEAD_PRODUCT', 'PRODUCT_FAMILY'] as $code) {
            if (!$this->containsValue($properties[$code] ?? null, self::LEGACY_CODE)) {
                continue;
            }
            return true;
        }

        $haystack = implode(' ', [
            (string)($element['NAME'] ?? ''),
            (string)($element['CODE'] ?? ''),
            (string)($element['PREVIEW_TEXT'] ?? ''),
            (string)($element['DETAIL_TEXT'] ?? ''),
        ]);

        return $this->containsLegacyMarker($haystack);
    }

    private function propertyCodes(int $iblockId): array
    {
        $codes = [];
        $result = CIBlockProperty::GetList(['ID' => 'ASC'], ['IBLOCK_ID' => $iblockId]);
        while ($property = $result->Fetch()) {
            $code = trim((string)($property['CODE'] ?? ''));
            if ($code !== '') {
                $codes[$code] = (int)$property['ID'];
            }
        }

        return $codes;
    }

    private function properties(int $iblockId, int $elementId): array
    {
        $properties = [];
        $result = CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc', 'id' => 'asc'], []);
        while ($property = $result->Fetch()) {
            $code = trim((string)($property['CODE'] ?? ''));
            if ($code === '') {
                continue;
            }
            $value = $property['VALUE'] ?? null;
            if (($property['MULTIPLE'] ?? 'N') === 'Y') {
                $properties[$code] ??= [];
                if ($value !== null && $value !== '') {
                    $properties[$code][] = $value;
                }
                continue;
            }
            $properties[$code] = $value;
        }

        return $properties;
    }

    private function buildChange(array $element): ?array
    {
        $iblockId = (int)$element['IBLOCK_ID'];
        $fields = [];
        foreach (['NAME', 'CODE', 'PREVIEW_TEXT', 'DETAIL_TEXT'] as $field) {
            $old = (string)($element[$field] ?? '');
            $new = $this->rename($old, $field === 'CODE');
            if ($new !== $old) {
                $fields[$field] = $new;
            }
        }

        $properties = [];
        foreach (($element['_properties'] ?? []) as $code => $value) {
            if (in_array($code, ['PRODUCT', 'PARENT_BLOCK'], true)) {
                continue;
            }
            $newValue = $this->renameProperty($value);
            if ($newValue !== $value) {
                $properties[$code] = $newValue;
            }
        }

        if ($fields === [] && $properties === []) {
            return null;
        }

        return [
            'iblock_key' => (string)($element['_iblock_key'] ?? ''),
            'iblock_id' => $iblockId,
            'element_id' => (int)$element['ID'],
            'old_code' => (string)($element['CODE'] ?? ''),
            'new_code' => (string)($fields['CODE'] ?? $element['CODE'] ?? ''),
            'fields' => $fields,
            'properties' => $properties,
        ];
    }

    private function applyChange(array $change): void
    {
        $elementId = (int)$change['element_id'];
        $iblockId = (int)$change['iblock_id'];
        $element = new CIBlockElement();
        if (!empty($change['fields']) && !$element->Update($elementId, $change['fields'])) {
            // Bitrix errors may contain user content: report the ID, not raw text.
            throw new RuntimeException('Failed to update element #' . $elementId . '.');
        }
        if (!empty($change['properties'])) {
            CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $change['properties']);
            $actual = $this->properties($iblockId, $elementId);
            foreach ($change['properties'] as $code => $expected) {
                if (!array_key_exists($code, $actual) || $actual[$code] !== $expected) {
                    throw new RuntimeException('Property verification failed for element #' . $elementId . '.');
                }
            }
        }
    }

    private function renameProperty(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->renameProperty($item), $value);
        }
        if (!is_string($value)) {
            return $value;
        }

        return $this->rename($value, false);
    }

    private function rename(string $value, bool $code): string
    {
        if ($value === '') {
            return $value;
        }
        if ($code) {
            return preg_replace('/^dev(?=-|$)/', self::ACTIVE_CODE, $value) ?? $value;
        }

        $value = str_replace('/dev/', '/adp/', $value);
        $value = preg_replace('/\bTacticum Dev\b/u', 'Tacticum ADP', $value) ?? $value;
        $value = preg_replace('/\bDev\b/u', 'ADP', $value) ?? $value;
        $value = preg_replace('/\bDEV\b/u', 'ADP', $value) ?? $value;
        $value = preg_replace('/\bdev\b/u', 'adp', $value) ?? $value;

        return $value;
    }

    private function containsValue(mixed $value, string $needle): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->containsValue($item, $needle)) {
                    return true;
                }
            }
            return false;
        }

        return is_scalar($value) && trim((string)$value) === $needle;
    }

    private function containsLegacyMarker(string $value): bool
    {
        return str_contains($value, '/dev/')
            || preg_match('/\bTacticum Dev\b|\bDev\b|\bDEV\b|\bdev\b/u', $value) === 1;
    }

    private function finish(array $plan, array $summary): int
    {
        if ($this->json) {
            echo json_encode(['success' => true, 'summary' => $summary, 'plan' => $plan], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            return 0;
        }

        echo 'ADP migration ' . $summary['mode'] . ': candidates=' . ($summary['candidates'] ?? 0)
            . ', changes=' . ($summary['changes'] ?? 0) . PHP_EOL;
        foreach ($plan as $change) {
            echo ($this->apply ? '[apply] ' : '[dry-run] ')
                . $change['iblock_key'] . '#' . $change['element_id']
                . ' ' . $change['old_code'] . ' -> ' . $change['new_code'] . PHP_EOL;
        }

        return 0;
    }
}

function tacticum_product_dev_to_adp_usage(): string
{
    return <<<TEXT
Usage:
  php tools/product-dev-to-adp-migration.php [--dry-run] [--json] [--document-root=/path/to/site]
  php tools/product-dev-to-adp-migration.php --apply --confirm-stateful [--json] [--document-root=/path/to/site]

Dry-run is the default. Apply mode is intentionally explicit and requires
--confirm-stateful after backup/restore rehearsal and production lock approval.
The migration preserves element IDs and existing XML_ID values, changes active
product identity/content references from dev to adp, and never creates new dev writes.
TEXT;
}

try {
    $options = [
        'apply' => false,
        'dry_run' => false,
        'confirm_stateful' => false,
        'json' => false,
        'document_root' => dirname(__DIR__),
    ];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            echo tacticum_product_dev_to_adp_usage();
            exit(0);
        }
        if ($argument === '--apply') {
            $options['apply'] = true;
            continue;
        }
        if ($argument === '--dry-run') {
            $options['dry_run'] = true;
            continue;
        }
        if ($argument === '--confirm-stateful') {
            $options['confirm_stateful'] = true;
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

    if ($options['apply'] && $options['dry_run']) {
        throw new InvalidArgumentException('--apply and --dry-run cannot be combined.');
    }
    if ($options['apply'] && !$options['confirm_stateful']) {
        throw new RuntimeException('--apply requires --confirm-stateful after the approved backup/restore gate.');
    }

    $documentRoot = realpath((string)$options['document_root']);
    if ($documentRoot === false) {
        throw new RuntimeException('Document root does not exist: ' . (string)$options['document_root']);
    }

    $migration = new TacticumProductDevToAdpMigration(
        (bool)$options['apply'],
        (bool)$options['json'],
        $documentRoot,
    );
    exit($migration->run());
} catch (Throwable $exception) {
    fwrite(STDERR, 'ADP migration failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
