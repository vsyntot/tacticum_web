#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStorageProofTaggingHelper
{
    private const PROOF_KEYS = ['cases', 'feedback', 'clients'];

    private string $documentRoot;
    private string $scope;
    private bool $json;
    private array $warnings = [];
    private array $errors = [];

    public function __construct(string $documentRoot, string $scope, bool $json)
    {
        $this->documentRoot = rtrim($documentRoot, '/');
        $this->scope = $scope;
        $this->json = $json;
    }

    public function run(): int
    {
        $this->bootstrap();
        if (!Loader::includeModule('iblock')) {
            $this->errors[] = 'Bitrix iblock module is unavailable.';
            return $this->finish([
                'products' => [],
                'iblocks' => [],
                'items' => [],
            ]);
        }

        $productMap = $this->productMap();
        $iblocks = [];
        $items = [];
        foreach (self::PROOF_KEYS as $key) {
            if (!$this->scopeMatches($key)) {
                continue;
            }

            $iblockId = $this->iblockId($key);
            if ($iblockId <= 0) {
                $this->warnings[] = "Iblock {$key} is not configured.";
                $iblocks[$key] = $this->emptyIblockSummary();
                continue;
            }

            $iblockInfo = $this->iblockInfo($iblockId);
            $rows = $this->proofItems($key, $iblockId, $iblockInfo['type'], $productMap['by_id']);
            $summary = $this->proofSummary($rows, $productMap['codes']);
            $iblocks[$key] = [
                'id' => $iblockId,
                'configured' => true,
                'iblock_type' => $iblockInfo['type'],
            ] + $summary;

            foreach ($rows as $row) {
                if (($row['active'] ?? false) === true) {
                    $items[] = $row;
                }
            }
        }

        return $this->finish([
            'products' => $productMap['products'],
            'iblocks' => $iblocks,
            'items' => $items,
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

    private function productMap(): array
    {
        $productsIblockId = $this->iblockId('products');
        $products = [];
        $byId = [];
        foreach ($this->productCodes() as $code) {
            $id = $productsIblockId > 0 ? $this->findElementId($productsIblockId, (string)$code) : 0;
            $products[] = [
                'code' => (string)$code,
                'id' => $id,
                'found' => $id > 0,
            ];
            if ($id > 0) {
                $byId[$id] = (string)$code;
            }
        }

        if ($productsIblockId <= 0) {
            $this->warnings[] = 'Products iblock is not configured.';
        }

        return [
            'products' => $products,
            'by_id' => $byId,
            'codes' => array_map(static fn (array $row): string => (string)$row['code'], $products),
        ];
    }

    private function proofItems(string $key, int $iblockId, string $iblockType, array $productById): array
    {
        $items = [];
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'ACTIVE']
        );
        while ($element = $result->Fetch()) {
            $id = (int)($element['ID'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $productCodes = $this->productCodesForElement($iblockId, $id, $productById);
            $active = (($element['ACTIVE'] ?? 'N') === 'Y');
            $items[] = [
                'iblock' => $key,
                'id' => $id,
                'active' => $active,
                'product_codes' => $productCodes,
                'product_count' => count($productCodes),
                'needs_owner_review' => $active && count($productCodes) === 0,
                'admin_edit_path' => $this->adminEditPath($iblockType, $iblockId, $id),
            ];
        }

        return $items;
    }

    private function proofSummary(array $rows, array $productCodes): array
    {
        $active = 0;
        $taggedActive = 0;
        $untaggedActive = 0;
        $productCounts = array_fill_keys($productCodes, 0);
        foreach ($rows as $row) {
            if (($row['active'] ?? false) !== true) {
                continue;
            }

            $active++;
            $codes = is_array($row['product_codes'] ?? null) ? $row['product_codes'] : [];
            if (count($codes) > 0) {
                $taggedActive++;
            } else {
                $untaggedActive++;
            }

            foreach ($codes as $code) {
                if (array_key_exists((string)$code, $productCounts)) {
                    $productCounts[(string)$code]++;
                }
            }
        }

        return [
            'elements_total' => count($rows),
            'elements_active' => $active,
            'inactive_or_filtered' => max(0, count($rows) - $active),
            'tagged_active' => $taggedActive,
            'untagged_active' => $untaggedActive,
            'product_counts' => $productCounts,
        ];
    }

    private function productCodesForElement(int $iblockId, int $elementId, array $productById): array
    {
        $codes = [];
        $result = CIBlockElement::GetProperty(
            $iblockId,
            $elementId,
            ['sort' => 'asc', 'id' => 'asc'],
            ['CODE' => 'PRODUCT']
        );
        while ($property = $result->Fetch()) {
            $productId = (int)($property['VALUE'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $codes[] = $productById[$productId] ?? ('unknown:' . $productId);
        }

        return array_values(array_unique($codes));
    }

    private function finish(array $payload): int
    {
        $output = [
            'success' => empty($this->errors),
            'scope' => $this->scope,
            'safe_for_release_evidence' => false,
            'release_evidence_hint' => 'Use content-storage-audit aggregate output for release evidence; this helper is an internal owner-review worksheet.',
            'products' => $payload['products'],
            'iblocks' => $payload['iblocks'],
            'items' => $payload['items'],
            'warnings' => $this->warnings,
            'errors' => $this->errors,
        ];

        if ($this->json) {
            echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

            return empty($this->errors) ? 0 : 1;
        }

        $this->line('Content storage proof tagging helper');
        $this->line('Scope: ' . $this->scope);
        $this->line('Release evidence: use aggregate content-storage-audit output, not this worksheet.');

        $this->line('');
        $this->line('Products:');
        foreach ($payload['products'] as $product) {
            $this->line(sprintf(
                '- %s: found=%s, id=%d',
                (string)$product['code'],
                !empty($product['found']) ? 'yes' : 'no',
                (int)$product['id']
            ));
        }

        $this->line('');
        $this->line('Proof iblocks:');
        foreach ($payload['iblocks'] as $key => $iblock) {
            $this->line(sprintf(
                '- %s: #%d, active=%d, total=%d, tagged_active=%d, untagged_active=%d',
                (string)$key,
                (int)($iblock['id'] ?? 0),
                (int)($iblock['elements_active'] ?? 0),
                (int)($iblock['elements_total'] ?? 0),
                (int)($iblock['tagged_active'] ?? 0),
                (int)($iblock['untagged_active'] ?? 0)
            ));
        }

        $this->line('');
        $this->line('Active owner-review items:');
        foreach ($payload['items'] as $item) {
            $codes = is_array($item['product_codes'] ?? null) && count($item['product_codes']) > 0
                ? implode(',', $item['product_codes'])
                : '-';
            $this->line(sprintf(
                '- %s #%d: products=%s, needs_owner_review=%s, edit=%s',
                (string)$item['iblock'],
                (int)$item['id'],
                $codes,
                !empty($item['needs_owner_review']) ? 'yes' : 'no',
                (string)$item['admin_edit_path']
            ));
        }

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

    private function emptyIblockSummary(): array
    {
        return [
            'id' => 0,
            'configured' => false,
            'iblock_type' => '',
            'elements_total' => 0,
            'elements_active' => 0,
            'inactive_or_filtered' => 0,
            'tagged_active' => 0,
            'untagged_active' => 0,
            'product_counts' => [],
        ];
    }

    private function iblockInfo(int $iblockId): array
    {
        $result = CIBlock::GetList(
            ['ID' => 'ASC'],
            [
                'ID' => $iblockId,
                'CHECK_PERMISSIONS' => 'N',
            ]
        );
        $iblock = $result->Fetch();

        return [
            'type' => is_array($iblock) ? (string)($iblock['IBLOCK_TYPE_ID'] ?? '') : '',
        ];
    }

    private function adminEditPath(string $iblockType, int $iblockId, int $elementId): string
    {
        $query = http_build_query([
            'IBLOCK_ID' => $iblockId,
            'type' => $iblockType,
            'ID' => $elementId,
            'lang' => 'ru',
            'find_section_section' => 0,
            'WF' => 'Y',
        ]);

        return '/bitrix/admin/iblock_element_edit.php?' . $query;
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
        return $this->scope === 'all' || $this->scope === $key;
    }

    private function line(string $message): void
    {
        if (!$this->json) {
            echo $message . PHP_EOL;
        }
    }
}

function tacticum_content_storage_proof_tagging_helper_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-proof-tagging-helper.php [--scope=all|cases|feedback|clients] [--json] [--document-root=/path/to/site]

Prints an internal owner-review worksheet for product proof tagging. The helper
is read-only: it shows active element IDs, current PRODUCT tags and Bitrix admin
edit paths. It does not print names, preview text, detail text, contacts or raw
claims. Use tools/content-storage-audit.php aggregate output for release evidence.

TEXT;
}

$scope = 'all';
$json = false;
$defaultDocumentRoot = isset($_SERVER['DOCUMENT_ROOT']) && trim((string)$_SERVER['DOCUMENT_ROOT']) !== ''
    ? (string)$_SERVER['DOCUMENT_ROOT']
    : dirname(__DIR__);
$documentRoot = $defaultDocumentRoot;

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        echo tacticum_content_storage_proof_tagging_helper_usage();
        exit(0);
    }
    if ($argument === '--json') {
        $json = true;
        continue;
    }
    if (str_starts_with($argument, '--scope=')) {
        $scope = substr($argument, strlen('--scope='));
        continue;
    }
    if (str_starts_with($argument, '--document-root=')) {
        $documentRoot = substr($argument, strlen('--document-root='));
        continue;
    }

    fwrite(STDERR, "Unknown argument: {$argument}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_proof_tagging_helper_usage());
    exit(2);
}

if (!in_array($scope, ['all', 'cases', 'feedback', 'clients'], true)) {
    fwrite(STDERR, "Unknown scope: {$scope}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_proof_tagging_helper_usage());
    exit(2);
}

try {
    $helper = new TacticumContentStorageProofTaggingHelper($documentRoot, $scope, $json);
    exit($helper->run());
} catch (Throwable $exception) {
    if ($json) {
        echo json_encode([
            'success' => false,
            'scope' => $scope,
            'safe_for_release_evidence' => false,
            'warnings' => [],
            'errors' => [$exception->getMessage()],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
    } else {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    }

    exit(1);
}
