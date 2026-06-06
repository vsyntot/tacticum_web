#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStorageAiagentsTagging
{
    private bool $apply;
    private bool $json;
    private string $documentRoot;
    private array $warnings = [];
    private array $errors = [];
    private array $actions = [];

    public function __construct(bool $apply, bool $json, string $documentRoot)
    {
        $this->apply = $apply;
        $this->json = $json;
        $this->documentRoot = rtrim($documentRoot, '/');
    }

    public function run(): int
    {
        $this->bootstrap();
        if (!Loader::includeModule('iblock')) {
            $this->errors[] = 'Bitrix iblock module is unavailable.';
            return $this->finish();
        }

        $aiagentsIblockId = $this->iblockId('aiagents');
        $productsIblockId = $this->iblockId('products');
        if ($aiagentsIblockId <= 0) {
            $this->errors[] = 'Missing aiagents iblock config key.';
        }
        if ($productsIblockId <= 0) {
            $this->errors[] = 'Missing products iblock config key.';
        }
        if (!empty($this->errors)) {
            return $this->finish();
        }

        $agentsProductId = $this->findElementIdByCode($productsIblockId, 'agents');
        if ($agentsProductId <= 0) {
            $this->errors[] = 'Product agents is missing in products iblock.';
            return $this->finish();
        }

        $this->validateProductRelation($aiagentsIblockId, $productsIblockId);
        if (!empty($this->errors)) {
            return $this->finish();
        }

        foreach ($this->activeAiagents($aiagentsIblockId) as $elementId) {
            $currentIds = $this->productIdsForElement($aiagentsIblockId, $elementId);
            $targetIds = [$agentsProductId];
            $changed = $currentIds !== $targetIds;
            $this->actions[] = [
                'iblock' => 'aiagents',
                'id' => $elementId,
                'current_product_codes' => $this->codesForIds($currentIds, [$agentsProductId => 'agents']),
                'target_product_codes' => ['agents'],
                'changed' => $changed,
                'status' => $changed ? ($this->apply ? 'pending_apply' : 'would_update') : 'unchanged',
            ];
        }

        if (empty($this->actions)) {
            $this->warnings[] = 'No active aiagents elements found.';
        }

        if ($this->apply && empty($this->errors)) {
            $this->applyActions($aiagentsIblockId, $agentsProductId);
        }

        return $this->finish();
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

    private function validateProductRelation(int $aiagentsIblockId, int $productsIblockId): void
    {
        $property = $this->propertyFields($aiagentsIblockId, 'PRODUCT');
        if ($property === null) {
            $this->errors[] = 'aiagents iblock has no PRODUCT relation property.';
            return;
        }
        if (($property['ACTIVE'] ?? 'N') !== 'Y') {
            $this->errors[] = 'aiagents PRODUCT relation property is inactive.';
        }
        if (($property['MULTIPLE'] ?? 'N') !== 'Y') {
            $this->errors[] = 'aiagents PRODUCT relation property must be multiple.';
        }
        if ($this->propertyLinkIblockId((int)($property['ID'] ?? 0)) !== $productsIblockId) {
            $this->errors[] = 'aiagents PRODUCT relation property must point to products iblock.';
        }
    }

    private function activeAiagents(int $aiagentsIblockId): array
    {
        $ids = [];
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $aiagentsIblockId,
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID']
        );

        while ($element = $result->Fetch()) {
            $id = (int)($element['ID'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function applyActions(int $aiagentsIblockId, int $agentsProductId): void
    {
        foreach ($this->actions as $index => $action) {
            if (($action['changed'] ?? false) !== true) {
                continue;
            }

            $elementId = (int)$action['id'];
            CIBlockElement::SetPropertyValuesEx($elementId, $aiagentsIblockId, ['PRODUCT' => [$agentsProductId]]);

            $actualIds = $this->productIdsForElement($aiagentsIblockId, $elementId);
            if ($actualIds !== [$agentsProductId]) {
                $this->actions[$index]['status'] = 'verify_failed';
                $this->errors[] = "aiagents #{$elementId} PRODUCT relation verification failed.";
                continue;
            }

            $this->actions[$index]['status'] = 'applied';
        }
    }

    private function finish(): int
    {
        $summary = $this->summary();
        if ($this->json) {
            echo json_encode([
                'success' => empty($this->errors),
                'mode' => $this->apply ? 'apply' : 'dry-run',
                'safe_for_release_evidence' => false,
                'release_evidence_hint' => 'Use content-storage-audit --scope=aiagents aggregate output after apply.',
                'summary' => $summary,
                'actions' => $this->actions,
                'warnings' => $this->warnings,
                'errors' => $this->errors,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

            return empty($this->errors) ? 0 : 1;
        }

        $this->line(empty($this->errors)
            ? 'Content storage AI agents tagging check passed.'
            : 'Content storage AI agents tagging failed.');
        $this->line('Mode: ' . ($this->apply ? 'apply' : 'dry-run'));
        $this->line('Release evidence: use aggregate content-storage-audit output after apply.');
        $this->line('');

        foreach ($this->actions as $action) {
            $prefix = ($action['status'] ?? '') === 'applied'
                ? '[apply]'
                : (($action['status'] ?? '') === 'would_update' ? '[dry-run]' : '[check]');
            $this->line(sprintf(
                '%s Set aiagents #%d PRODUCT=%s current=%s status=%s',
                $prefix,
                (int)$action['id'],
                $this->codeList($action['target_product_codes'] ?? []),
                $this->codeList($action['current_product_codes'] ?? []),
                (string)($action['status'] ?? '')
            ));
        }

        $this->line('');
        $this->line('AI agents tagging summary:'
            . ' total=' . $summary['total']
            . ', changed=' . $summary['changed']
            . ', unchanged=' . $summary['unchanged']
            . ', applied=' . $summary['applied']
            . ', mode=' . ($this->apply ? 'apply' : 'dry-run'));

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

    private function summary(): array
    {
        $summary = [
            'total' => count($this->actions),
            'changed' => 0,
            'unchanged' => 0,
            'applied' => 0,
        ];

        foreach ($this->actions as $action) {
            if (($action['changed'] ?? false) === true) {
                $summary['changed']++;
            } else {
                $summary['unchanged']++;
            }
            if (($action['status'] ?? '') === 'applied') {
                $summary['applied']++;
            }
        }

        return $summary;
    }

    private function productIdsForElement(int $iblockId, int $elementId): array
    {
        $ids = [];
        $result = CIBlockElement::GetProperty(
            $iblockId,
            $elementId,
            ['sort' => 'asc', 'id' => 'asc'],
            ['CODE' => 'PRODUCT']
        );
        while ($property = $result->Fetch()) {
            $productId = (int)($property['VALUE'] ?? 0);
            if ($productId > 0) {
                $ids[] = $productId;
            }
        }

        $ids = array_values(array_unique($ids));
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    private function codesForIds(array $ids, array $productById): array
    {
        $codes = [];
        foreach ($ids as $id) {
            $codes[] = $productById[(int)$id] ?? ('unknown:' . (int)$id);
        }

        return $codes;
    }

    private function codeList(array $codes): string
    {
        return count($codes) > 0 ? implode(',', array_map('strval', $codes)) : '-';
    }

    private function findElementIdByCode(int $iblockId, string $code): int
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

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id($key) : 0;
    }

    private function line(string $message): void
    {
        if (!$this->json) {
            echo $message . PHP_EOL;
        }
    }
}

function tacticum_content_storage_aiagents_tagging_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-aiagents-tagging.php [--apply] [--json] [--document-root=/path/to/site]

Tags active aiagents demo/prototype elements with PRODUCT=agents. Dry-run is
the default. The tool changes only the PRODUCT relation and does not print
element names, copy, contacts or raw claims.

TEXT;
}

$apply = false;
$json = false;
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) && trim((string)$_SERVER['DOCUMENT_ROOT']) !== ''
    ? (string)$_SERVER['DOCUMENT_ROOT']
    : dirname(__DIR__);

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        echo tacticum_content_storage_aiagents_tagging_usage();
        exit(0);
    }
    if ($argument === '--apply') {
        $apply = true;
        continue;
    }
    if ($argument === '--json') {
        $json = true;
        continue;
    }
    if (str_starts_with($argument, '--document-root=')) {
        $documentRoot = substr($argument, strlen('--document-root='));
        continue;
    }

    fwrite(STDERR, "Unknown argument: {$argument}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_aiagents_tagging_usage());
    exit(2);
}

try {
    $tool = new TacticumContentStorageAiagentsTagging($apply, $json, $documentRoot);
    exit($tool->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_aiagents_tagging_usage());
    exit(1);
}
