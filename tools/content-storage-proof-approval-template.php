#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStorageProofApprovalTemplate
{
    private const PROOF_KEYS = ['cases', 'feedback', 'clients'];

    private string $documentRoot;
    private string $scope;
    private string $outputPath;
    private bool $force;
    private array $warnings = [];

    public function __construct(string $documentRoot, string $scope, string $outputPath, bool $force)
    {
        $this->documentRoot = rtrim($documentRoot, '/');
        $this->scope = $scope;
        $this->outputPath = $outputPath;
        $this->force = $force;
    }

    public function run(): int
    {
        $this->bootstrap();
        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Bitrix iblock module is unavailable.');
        }

        $items = [];
        foreach (self::PROOF_KEYS as $key) {
            if (!$this->scopeMatches($key)) {
                continue;
            }

            $iblockId = $this->iblockId($key);
            if ($iblockId <= 0) {
                $this->warnings[] = "Iblock {$key} is not configured.";
                continue;
            }

            foreach ($this->activeElementIds($iblockId) as $elementId) {
                $items[] = [
                    'iblock' => $key,
                    'id' => $elementId,
                    'decision' => 'pending',
                    'product_codes' => [],
                    'product_tag_approved' => false,
                    'public_render_approved' => false,
                ];
            }
        }

        $payload = [
            'schema' => 'tacticum.content_storage.proof_tagging_approval.v1',
            'status' => 'draft',
            'date' => date('Y-m-d'),
            'source' => 'tools/content-storage-proof-approval-template.php',
            'release_evidence' => false,
            'generated_from' => [
                'scope' => $this->scope,
                'active_only' => true,
                'raw_copy_included' => false,
                'admin_links_included' => false,
            ],
            'rules' => [
                'This draft stores only iblock keys, element IDs, product codes and approval booleans.',
                'Do not add customer names, testimonial text, case text, contacts, admin URLs or raw claims.',
                'Decision tag means owners approved PRODUCT tagging only; public_render_approved is a separate gate.',
                'Decision global means the item remains general proof and is not tied to a product page.',
                'Decision not_public means the item must not render in product proof blocks.',
            ],
            'owners' => [
                'content' => [
                    'approved' => false,
                    'approved_at' => '',
                    'evidence_ref' => '',
                ],
                'sales' => [
                    'approved' => false,
                    'approved_at' => '',
                    'evidence_ref' => '',
                ],
                'seo' => [
                    'approved' => false,
                    'approved_at' => '',
                    'evidence_ref' => '',
                ],
            ],
            'items' => $items,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if (!is_string($json)) {
            throw new RuntimeException('Failed to encode approval template JSON.');
        }

        $json .= PHP_EOL;
        if ($this->outputPath !== '') {
            $this->writeOutput($json);
            fwrite(STDERR, 'Proof approval draft written: ' . $this->outputPath . PHP_EOL);
            fwrite(STDERR, 'Items: ' . count($items) . ', scope=' . $this->scope . PHP_EOL);
            foreach ($this->warnings as $warning) {
                fwrite(STDERR, 'Warning: ' . $warning . PHP_EOL);
            }

            return 0;
        }

        echo $json;
        foreach ($this->warnings as $warning) {
            fwrite(STDERR, 'Warning: ' . $warning . PHP_EOL);
        }

        return 0;
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

    private function activeElementIds(int $iblockId): array
    {
        $ids = [];
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
            $id = (int)($element['ID'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function writeOutput(string $json): void
    {
        if (is_file($this->outputPath) && !$this->force) {
            throw new RuntimeException('Output file already exists. Use --force to overwrite: ' . $this->outputPath);
        }

        $directory = dirname($this->outputPath);
        if ($directory !== '' && $directory !== '.' && !is_dir($directory)) {
            throw new RuntimeException('Output directory does not exist: ' . $directory);
        }

        if (file_put_contents($this->outputPath, $json, LOCK_EX) === false) {
            throw new RuntimeException('Failed to write output file: ' . $this->outputPath);
        }
    }

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id($key) : 0;
    }

    private function scopeMatches(string $key): bool
    {
        return $this->scope === 'all' || $this->scope === $key;
    }
}

function tacticum_content_storage_proof_approval_template_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-proof-approval-template.php [--scope=all|cases|feedback|clients] [--output=/tmp/content-storage-proof-tagging-approval.draft.json] [--force] [--document-root=/path/to/site]

Generates a no-raw-copy owner approval draft for active cases/feedback/clients
items. The output contains only iblock keys, element IDs, pending decisions and
approval booleans. It does not include proof names, texts, contacts or Bitrix
admin links. Use content-storage-proof-tagging-helper.php separately for the
internal owner-review worksheet with admin edit paths.

TEXT;
}

$scope = 'all';
$outputPath = '';
$force = false;
$defaultDocumentRoot = isset($_SERVER['DOCUMENT_ROOT']) && trim((string)$_SERVER['DOCUMENT_ROOT']) !== ''
    ? (string)$_SERVER['DOCUMENT_ROOT']
    : dirname(__DIR__);
$documentRoot = $defaultDocumentRoot;

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        echo tacticum_content_storage_proof_approval_template_usage();
        exit(0);
    }
    if ($argument === '--force') {
        $force = true;
        continue;
    }
    if (str_starts_with($argument, '--scope=')) {
        $scope = substr($argument, strlen('--scope='));
        continue;
    }
    if (str_starts_with($argument, '--output=')) {
        $outputPath = substr($argument, strlen('--output='));
        continue;
    }
    if (str_starts_with($argument, '--document-root=')) {
        $documentRoot = substr($argument, strlen('--document-root='));
        continue;
    }

    fwrite(STDERR, "Unknown argument: {$argument}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_proof_approval_template_usage());
    exit(2);
}

if (!in_array($scope, ['all', 'cases', 'feedback', 'clients'], true)) {
    fwrite(STDERR, "Unknown scope: {$scope}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_proof_approval_template_usage());
    exit(2);
}

try {
    $tool = new TacticumContentStorageProofApprovalTemplate($documentRoot, $scope, $outputPath, $force);
    exit($tool->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_proof_approval_template_usage());
    exit(1);
}
