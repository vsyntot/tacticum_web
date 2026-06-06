#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStoragePageContentLiveApprovalTemplate
{
    private string $documentRoot;
    private string $outputPath;
    private bool $force;
    private string $page;
    private array $errors = [];

    public function __construct(string $documentRoot, string $outputPath, bool $force, string $page)
    {
        $this->documentRoot = rtrim($documentRoot, '/');
        $this->outputPath = $outputPath;
        $this->force = $force;
        $this->page = trim($page);
    }

    public function run(): int
    {
        $this->bootstrap();
        if (!Loader::includeModule('iblock')) {
            $this->errors[] = 'Bitrix iblock module is unavailable.';
            return $this->finish([]);
        }

        $sectionsIblockId = $this->iblockId('page_sections');
        $blocksIblockId = $this->iblockId('page_blocks');
        if ($sectionsIblockId <= 0) {
            $this->errors[] = 'Missing page_sections iblock config key.';
        }
        if ($blocksIblockId <= 0) {
            $this->errors[] = 'Missing page_blocks iblock config key.';
        }
        if (!empty($this->errors)) {
            return $this->finish([]);
        }

        $items = $this->sectionItems($sectionsIblockId, $blocksIblockId);
        if (empty($items)) {
            $this->errors[] = 'No active page-content sections found for the selected scope.';
            return $this->finish([]);
        }

        $payload = $this->payload($items);
        if ($this->outputPath !== '') {
            if (is_file($this->outputPath) && !$this->force) {
                $this->errors[] = 'Output file already exists; pass --force to overwrite: ' . $this->outputPath;
                return $this->finish($payload);
            }
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            if (!is_string($json)) {
                $this->errors[] = 'Failed to encode approval template JSON.';
                return $this->finish($payload);
            }
            if (file_put_contents($this->outputPath, $json . PHP_EOL) === false) {
                $this->errors[] = 'Failed to write approval template: ' . $this->outputPath;
                return $this->finish($payload);
            }
        }

        return $this->finish($payload);
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

    private function sectionItems(int $sectionsIblockId, int $blocksIblockId): array
    {
        $items = [];
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $sectionsIblockId,
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'SORT']
        );

        while ($element = $result->Fetch()) {
            $sectionId = (int)($element['ID'] ?? 0);
            if ($sectionId <= 0) {
                continue;
            }
            $pageKey = $this->propertyString($sectionsIblockId, $sectionId, 'PAGE_KEY');
            $sectionKey = $this->propertyString($sectionsIblockId, $sectionId, 'SECTION_KEY');
            $currentStatus = $this->propertyString($sectionsIblockId, $sectionId, 'MIGRATION_STATUS');
            $templateKey = $this->propertyString($sectionsIblockId, $sectionId, 'TEMPLATE_KEY');
            if ($this->page !== '' && $pageKey !== $this->page) {
                continue;
            }
            if ($pageKey === '' || $sectionKey === '') {
                continue;
            }

            $blockCounts = $this->blockCounts($blocksIblockId, $sectionId);
            $items[] = [
                'id' => $sectionId,
                'page' => $pageKey,
                'section_key' => $sectionKey,
                'template_key' => $templateKey,
                'decision' => 'pending',
                'current_status' => $currentStatus,
                'target_status' => '',
                'section_live_approved' => false,
                'fallback_retirement_approved' => false,
                'blocks_total' => $blockCounts['total'],
                'blocks_active' => $blockCounts['active'],
            ];
        }

        return $items;
    }

    private function payload(array $items): array
    {
        return [
            'schema' => 'tacticum.content_storage.page_content_live_approval.v1',
            'status' => 'draft',
            'date' => date('Y-m-d'),
            'source' => 'tools/content-storage-page-content-live-approval-template.php',
            'release_evidence' => false,
            'source_switch_approved' => false,
            'generated_from' => [
                'page' => $this->page !== '' ? $this->page : 'all',
                'active_only' => true,
                'raw_copy_included' => false,
                'admin_links_included' => false,
            ],
            'rules' => [
                'This draft stores only section IDs, page keys, section keys, template keys, statuses and approval booleans.',
                'Do not add page copy, CTA text, contacts, admin URLs, screenshots or raw HTML.',
                'Decision promote_live approves only page_sections.MIGRATION_STATUS=live.',
                'Decision keep_shadow leaves a section in shadow mode.',
                'Decision demote_shadow is rollback to shadow mode.',
                'This approval does not set page_content.source=bitrix and does not retire PHP fallback partials.',
            ],
            'owners' => $this->ownerRows(),
            'gates' => [
                'config_runtime_check' => false,
                'strict_page_content_audit' => false,
                'governance_check' => false,
                'seo_check' => false,
                'rollback_plan' => false,
                'post_switch_visual_smoke_required' => true,
                'post_switch_browser_smoke_required' => true,
            ],
            'items' => $items,
        ];
    }

    private function ownerRows(): array
    {
        $owners = [];
        foreach (['architect', 'content', 'frontend', 'qa', 'seo'] as $owner) {
            $owners[$owner] = [
                'approved' => false,
                'approved_at' => '',
                'evidence_ref' => '',
            ];
        }

        return $owners;
    }

    private function blockCounts(int $blocksIblockId, int $sectionId): array
    {
        $total = 0;
        $active = 0;
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $blocksIblockId,
                '=PROPERTY_SECTION' => $sectionId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'ACTIVE']
        );
        while ($element = $result->Fetch()) {
            $total++;
            if (($element['ACTIVE'] ?? 'N') === 'Y') {
                $active++;
            }
        }

        return ['total' => $total, 'active' => $active];
    }

    private function propertyString(int $iblockId, int $elementId, string $code): string
    {
        $result = CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc', 'id' => 'asc'], ['CODE' => $code]);
        $property = $result->Fetch();
        $value = is_array($property) ? ($property['VALUE'] ?? '') : '';
        if (is_array($value)) {
            $value = reset($value);
        }

        return trim((string)$value);
    }

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id($key) : 0;
    }

    private function finish(array $payload): int
    {
        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                fwrite(STDERR, $error . PHP_EOL);
            }
            return 1;
        }

        if ($this->outputPath !== '') {
            fwrite(STDERR, 'Page-content live approval draft written: ' . $this->outputPath . PHP_EOL);
            fwrite(STDERR, 'Items: ' . count($payload['items'] ?? []) . ', source_switch_approved=false' . PHP_EOL);
        } else {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
        }

        return 0;
    }
}

function tacticum_content_storage_page_content_live_approval_template_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-page-content-live-approval-template.php [--page=/services/] [--output=/tmp/page-content-live-approval.draft.json] [--force] [--document-root=/path/to/site]

Generates a no-raw-copy owner approval draft for promoting page-content sections
from shadow to live. The draft does not approve page_content.source=bitrix and
does not retire PHP fallback partials.

TEXT;
}

$outputPath = '';
$force = false;
$page = '';
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) && trim((string)$_SERVER['DOCUMENT_ROOT']) !== ''
    ? (string)$_SERVER['DOCUMENT_ROOT']
    : dirname(__DIR__);

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        echo tacticum_content_storage_page_content_live_approval_template_usage();
        exit(0);
    }
    if ($argument === '--force') {
        $force = true;
        continue;
    }
    if (str_starts_with($argument, '--output=')) {
        $outputPath = substr($argument, strlen('--output='));
        continue;
    }
    if (str_starts_with($argument, '--page=')) {
        $page = substr($argument, strlen('--page='));
        continue;
    }
    if (str_starts_with($argument, '--document-root=')) {
        $documentRoot = substr($argument, strlen('--document-root='));
        continue;
    }

    fwrite(STDERR, "Unknown argument: {$argument}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_page_content_live_approval_template_usage());
    exit(2);
}

try {
    $tool = new TacticumContentStoragePageContentLiveApprovalTemplate($documentRoot, $outputPath, $force, $page);
    exit($tool->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_page_content_live_approval_template_usage());
    exit(1);
}
