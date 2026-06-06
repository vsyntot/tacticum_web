#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStoragePageContentLiveApply
{
    private const REQUIRED_OWNERS = ['architect', 'content', 'frontend', 'qa', 'seo'];
    private const REQUIRED_GATES = [
        'config_runtime_check',
        'strict_page_content_audit',
        'governance_check',
        'seo_check',
        'rollback_plan',
        'post_switch_visual_smoke_required',
        'post_switch_browser_smoke_required',
    ];
    private const ALLOWED_SECTIONS = [
        '/services/' => ['delivery-layer', 'process', 'tech'],
        '/price/' => ['features', 'workstreams'],
        '/contacts/' => ['routing', 'cards'],
        '/offer/' => ['product-bridge', 'bottom-cta'],
        '/' => ['ecosystem', 'fit-matrix', 'commercial'],
        '/about/' => ['company-trust', 'values-team', 'career-final'],
        '/calculator/' => ['calculator-outcome-cards', 'product-aware-estimate-cards'],
        '/aiagents/' => ['agents-bridge', 'how-it-works', 'services'],
    ];
    private const FORBIDDEN_KEYS = [
        'name',
        'title',
        'text',
        'copy',
        'html',
        'raw_html',
        'preview_text',
        'detail_text',
        'eyebrow',
        'cta_text',
        'admin_edit_path',
        'admin_url',
        'email',
        'phone',
        'contact',
    ];

    private bool $apply;
    private bool $json;
    private string $documentRoot;
    private string $approvalPath;
    private array $warnings = [];
    private array $errors = [];
    private array $actions = [];

    public function __construct(bool $apply, bool $json, string $documentRoot, string $approvalPath)
    {
        $this->apply = $apply;
        $this->json = $json;
        $this->documentRoot = rtrim($documentRoot, '/');
        $this->approvalPath = $approvalPath;
    }

    public function run(): int
    {
        $payload = $this->readApproval();
        $this->validateApproval($payload);
        if (!empty($this->errors)) {
            return $this->finish();
        }

        $this->bootstrap();
        if (!Loader::includeModule('iblock')) {
            $this->errors[] = 'Bitrix iblock module is unavailable.';
            return $this->finish();
        }

        $sectionsIblockId = $this->iblockId('page_sections');
        if ($sectionsIblockId <= 0) {
            $this->errors[] = 'Missing page_sections iblock config key.';
            return $this->finish();
        }
        if ($this->propertyId($sectionsIblockId, 'MIGRATION_STATUS') <= 0) {
            $this->errors[] = 'page_sections iblock has no MIGRATION_STATUS property.';
            return $this->finish();
        }

        foreach ($payload['items'] as $item) {
            $this->planItem($sectionsIblockId, $item);
        }

        if ($this->apply && empty($this->errors)) {
            $this->applyActions($sectionsIblockId);
        }

        return $this->finish();
    }

    private function readApproval(): array
    {
        if ($this->approvalPath === '') {
            throw new RuntimeException('Missing approval JSON path.');
        }
        if (!is_file($this->approvalPath)) {
            throw new RuntimeException('Approval JSON not found: ' . $this->approvalPath);
        }
        $json = file_get_contents($this->approvalPath);
        if ($json === false) {
            throw new RuntimeException('Cannot read approval JSON: ' . $this->approvalPath);
        }
        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($payload)) {
            throw new RuntimeException('Approval JSON must decode to an object.');
        }

        return $payload;
    }

    private function validateApproval(array $payload): void
    {
        $this->validateNoRawContent($payload, 'payload');

        if (($payload['schema'] ?? '') !== 'tacticum.content_storage.page_content_live_approval.v1') {
            $this->errors[] = 'schema must be tacticum.content_storage.page_content_live_approval.v1.';
        }
        if (($payload['status'] ?? '') !== 'approved') {
            $this->errors[] = 'status must be approved. Draft approvals are allowed only for read-only validation.';
        }
        if (($payload['release_evidence'] ?? null) !== false) {
            $this->errors[] = 'release_evidence must be false; use aggregate page-content audit output for release evidence.';
        }
        if (($payload['source_switch_approved'] ?? null) !== false) {
            $this->errors[] = 'source_switch_approved must be false; this tool does not switch page_content.source.';
        }

        $owners = $payload['owners'] ?? null;
        if (!is_array($owners)) {
            $this->errors[] = 'owners must be an object.';
        } else {
            foreach (self::REQUIRED_OWNERS as $owner) {
                $row = $owners[$owner] ?? null;
                if (!is_array($row)) {
                    $this->errors[] = "owners.{$owner} must be an object.";
                    continue;
                }
                if (($row['approved'] ?? null) !== true) {
                    $this->errors[] = "owners.{$owner}.approved must be true.";
                }
                if (!is_string($row['approved_at'] ?? null) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$row['approved_at'])) {
                    $this->errors[] = "owners.{$owner}.approved_at must be YYYY-MM-DD.";
                }
                if (!is_string($row['evidence_ref'] ?? null) || trim((string)$row['evidence_ref']) === '') {
                    $this->errors[] = "owners.{$owner}.evidence_ref is required.";
                }
            }
        }

        $gates = $payload['gates'] ?? null;
        if (!is_array($gates)) {
            $this->errors[] = 'gates must be an object.';
        } else {
            foreach (self::REQUIRED_GATES as $gate) {
                if (($gates[$gate] ?? null) !== true) {
                    $this->errors[] = "gates.{$gate} must be true.";
                }
            }
        }

        $items = $payload['items'] ?? null;
        if (!is_array($items) || empty($items)) {
            $this->errors[] = 'items must be a non-empty array.';
            return;
        }

        $seen = [];
        foreach ($items as $index => $item) {
            $path = 'items[' . $index . ']';
            if (!is_array($item)) {
                $this->errors[] = "{$path} must be an object.";
                continue;
            }
            $id = (int)($item['id'] ?? 0);
            $page = (string)($item['page'] ?? '');
            $sectionKey = (string)($item['section_key'] ?? '');
            $decision = (string)($item['decision'] ?? '');
            $targetStatus = (string)($item['target_status'] ?? '');

            if ($id <= 0) {
                $this->errors[] = "{$path}.id must be a positive integer.";
            }
            if (!isset(self::ALLOWED_SECTIONS[$page])) {
                $this->errors[] = "{$path}.page is not allowed.";
            } elseif (!in_array($sectionKey, self::ALLOWED_SECTIONS[$page], true)) {
                $this->errors[] = "{$path}.section_key is not allowed for page {$page}.";
            }
            $key = $page . ':' . $sectionKey;
            if (isset($seen[$key])) {
                $this->errors[] = "{$path} duplicates {$key}.";
            }
            $seen[$key] = true;

            if (!in_array($decision, ['promote_live', 'keep_shadow', 'demote_shadow'], true)) {
                $this->errors[] = "{$path}.decision must be promote_live, keep_shadow or demote_shadow for apply.";
            }
            if (!is_bool($item['section_live_approved'] ?? null)) {
                $this->errors[] = "{$path}.section_live_approved must be boolean.";
            }
            if (($item['fallback_retirement_approved'] ?? null) !== false) {
                $this->errors[] = "{$path}.fallback_retirement_approved must be false.";
            }
            if ($decision === 'promote_live') {
                if ($targetStatus !== 'live') {
                    $this->errors[] = "{$path}.target_status must be live when decision=promote_live.";
                }
                if (($item['section_live_approved'] ?? null) !== true) {
                    $this->errors[] = "{$path}.section_live_approved must be true when decision=promote_live.";
                }
            }
            if (($decision === 'keep_shadow' || $decision === 'demote_shadow') && $targetStatus !== 'shadow') {
                $this->errors[] = "{$path}.target_status must be shadow when decision={$decision}.";
            }
            if (($decision === 'keep_shadow' || $decision === 'demote_shadow') && ($item['section_live_approved'] ?? null) !== false) {
                $this->errors[] = "{$path}.section_live_approved must be false when decision={$decision}.";
            }
        }
    }

    private function validateNoRawContent(mixed $value, string $path): void
    {
        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $keyString = is_string($key) ? $key : (string)$key;
                if (is_string($key) && in_array(strtolower($key), self::FORBIDDEN_KEYS, true)) {
                    $this->errors[] = "{$path}.{$keyString} is forbidden; do not store raw page copy, contacts or admin links.";
                }
                $this->validateNoRawContent($child, $path . '.' . $keyString);
            }
            return;
        }
        if (!is_string($value)) {
            return;
        }
        if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $value)) {
            $this->errors[] = "{$path} appears to contain an email address.";
        }
        $digitCount = strlen(preg_replace('/\D/', '', $value) ?? '');
        if ($digitCount >= 10 && preg_match('/(?:\+?\d[\s().-]*){10,}/', $value)) {
            $this->errors[] = "{$path} appears to contain a phone-like value.";
        }
        if (preg_match('/https?:\/\//i', $value)) {
            $this->errors[] = "{$path} appears to contain a URL.";
        }
        if (preg_match('/\/bitrix\/admin\//i', $value)) {
            $this->errors[] = "{$path} appears to contain a Bitrix admin URL.";
        }
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

    private function planItem(int $sectionsIblockId, array $item): void
    {
        $id = (int)($item['id'] ?? 0);
        $page = (string)($item['page'] ?? '');
        $sectionKey = (string)($item['section_key'] ?? '');
        $decision = (string)($item['decision'] ?? '');
        $targetStatus = (string)($item['target_status'] ?? '');
        $actual = $this->findSection($sectionsIblockId, $page, $sectionKey);
        if ($actual === null) {
            $this->errors[] = "Page-content section not found: {$page} {$sectionKey}.";
            return;
        }
        if ((int)$actual['id'] !== $id) {
            $this->errors[] = "Page-content section ID mismatch for {$page} {$sectionKey}: approval #{$id}, actual #" . (int)$actual['id'] . '.';
            return;
        }

        $currentStatus = (string)$actual['migration_status'];
        if ($decision === 'keep_shadow') {
            if ($currentStatus !== 'shadow') {
                $this->errors[] = "Decision keep_shadow requires current shadow status for {$page} {$sectionKey}; current={$currentStatus}. Use demote_shadow for rollback.";
                return;
            }
            $targetStatus = 'shadow';
        }
        $changed = $currentStatus !== $targetStatus;
        $this->actions[] = [
            'id' => $id,
            'page' => $page,
            'section_key' => $sectionKey,
            'decision' => $decision,
            'current_status' => $currentStatus,
            'target_status' => $targetStatus,
            'changed' => $changed,
            'status' => $changed ? ($this->apply ? 'pending_apply' : 'would_update') : 'unchanged',
        ];
    }

    private function applyActions(int $sectionsIblockId): void
    {
        foreach ($this->actions as $index => $action) {
            if (($action['changed'] ?? false) !== true) {
                continue;
            }
            $id = (int)$action['id'];
            $targetStatus = (string)$action['target_status'];
            CIBlockElement::SetPropertyValuesEx($id, $sectionsIblockId, ['MIGRATION_STATUS' => $targetStatus]);
            $actualStatus = $this->propertyString($sectionsIblockId, $id, 'MIGRATION_STATUS');
            if ($actualStatus !== $targetStatus) {
                $this->actions[$index]['status'] = 'verify_failed';
                $this->errors[] = "page_section #{$id} MIGRATION_STATUS verification failed.";
                continue;
            }
            $this->actions[$index]['status'] = 'applied';
        }
    }

    private function findSection(int $sectionsIblockId, string $page, string $sectionKey): ?array
    {
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $sectionsIblockId,
                '=PROPERTY_PAGE_KEY' => $page,
                '=PROPERTY_SECTION_KEY' => $sectionKey,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            ['nTopCount' => 1],
            ['ID']
        );
        $element = $result->Fetch();
        if (!is_array($element)) {
            return null;
        }
        $id = (int)($element['ID'] ?? 0);
        if ($id <= 0) {
            return null;
        }

        return [
            'id' => $id,
            'migration_status' => $this->propertyString($sectionsIblockId, $id, 'MIGRATION_STATUS'),
        ];
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

    private function propertyId(int $iblockId, string $code): int
    {
        $result = CIBlockProperty::GetList(['ID' => 'ASC'], ['IBLOCK_ID' => $iblockId, 'CODE' => $code]);
        $property = $result->Fetch();

        return is_array($property) ? (int)$property['ID'] : 0;
    }

    private function iblockId(string $key): int
    {
        return function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id($key) : 0;
    }

    private function finish(): int
    {
        $summary = $this->summary();
        if ($this->json) {
            echo json_encode([
                'success' => empty($this->errors),
                'mode' => $this->apply ? 'apply' : 'dry-run',
                'safe_for_release_evidence' => false,
                'release_evidence_hint' => 'Use content-storage-audit --scope=page-content aggregate output after apply.',
                'runtime_switch_changed' => false,
                'summary' => $summary,
                'actions' => $this->actions,
                'warnings' => $this->warnings,
                'errors' => $this->errors,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

            return empty($this->errors) ? 0 : 1;
        }

        $this->line(empty($this->errors)
            ? 'Content storage page-content live apply check passed.'
            : 'Content storage page-content live apply failed.');
        $this->line('Mode: ' . ($this->apply ? 'apply' : 'dry-run'));
        $this->line('Runtime switch: unchanged. This tool does not set page_content.source=bitrix or retire PHP fallback partials.');
        $this->line('Release evidence: use aggregate content-storage-audit output after apply.');
        $this->line('');

        foreach ($this->actions as $action) {
            $prefix = ($action['status'] ?? '') === 'applied'
                ? '[apply]'
                : (($action['status'] ?? '') === 'would_update' ? '[dry-run]' : '[check]');
            $this->line(sprintf(
                '%s Set page_section #%d %s %s MIGRATION_STATUS=%s current=%s decision=%s status=%s',
                $prefix,
                (int)$action['id'],
                (string)$action['page'],
                (string)$action['section_key'],
                (string)$action['target_status'],
                (string)$action['current_status'],
                (string)$action['decision'],
                (string)$action['status']
            ));
        }

        $this->line('');
        $this->line('Page-content live summary:'
            . ' total=' . $summary['total']
            . ', promote_live=' . $summary['promote_live']
            . ', keep_shadow=' . $summary['keep_shadow']
            . ', demote_shadow=' . $summary['demote_shadow']
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
            'promote_live' => 0,
            'keep_shadow' => 0,
            'demote_shadow' => 0,
            'changed' => 0,
            'unchanged' => 0,
            'applied' => 0,
        ];
        foreach ($this->actions as $action) {
            $decision = (string)($action['decision'] ?? '');
            if (array_key_exists($decision, $summary)) {
                $summary[$decision]++;
            }
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

    private function line(string $message): void
    {
        echo $message . PHP_EOL;
    }
}

function tacticum_content_storage_page_content_live_apply_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-page-content-live-apply.php --approval=/path/to/approved.json [--apply] [--json] [--document-root=/path/to/site]
  php tools/content-storage-page-content-live-apply.php /path/to/approved.json [--apply]

Applies owner-approved page-content section live-status decisions. Dry-run is the
default. The approval JSON must have status=approved. The tool changes only
page_sections.MIGRATION_STATUS and does not set page_content.source=bitrix or
retire PHP fallback partials.

TEXT;
}

$apply = false;
$json = false;
$approvalPath = '';
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) && trim((string)$_SERVER['DOCUMENT_ROOT']) !== ''
    ? (string)$_SERVER['DOCUMENT_ROOT']
    : dirname(__DIR__);

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        echo tacticum_content_storage_page_content_live_apply_usage();
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
    if (str_starts_with($argument, '--approval=')) {
        $approvalPath = substr($argument, strlen('--approval='));
        continue;
    }
    if (str_starts_with($argument, '--document-root=')) {
        $documentRoot = substr($argument, strlen('--document-root='));
        continue;
    }
    if ($approvalPath === '') {
        $approvalPath = $argument;
        continue;
    }

    fwrite(STDERR, "Unknown argument: {$argument}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_page_content_live_apply_usage());
    exit(2);
}

try {
    $tool = new TacticumContentStoragePageContentLiveApply($apply, $json, $documentRoot, $approvalPath);
    exit($tool->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_page_content_live_apply_usage());
    exit(1);
}
