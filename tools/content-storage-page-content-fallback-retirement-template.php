#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStoragePageContentFallbackRetirementTemplate
{
    private const WAVE_PAGES = [
        'wave_1' => ['/services/', '/price/', '/contacts/', '/offer/'],
        'wave_2' => ['/', '/about/', '/calculator/', '/aiagents/'],
    ];

    private string $documentRoot;
    private string $outputPath;
    private bool $force;
    private string $page;
    private string $wave;
    /** @var string[] */
    private array $errors = [];

    public function __construct(string $documentRoot, string $outputPath, bool $force, string $page, string $wave)
    {
        $this->documentRoot = rtrim($documentRoot, '/');
        $this->outputPath = $outputPath;
        $this->force = $force;
        $this->page = trim($page);
        $this->wave = trim($wave);
    }

    public function run(): int
    {
        $this->bootstrap();
        $this->validateScope();
        if (!empty($this->errors)) {
            return $this->finish([]);
        }
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
            $this->errors[] = 'No active live page-content sections found for the selected scope.';
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
                $this->errors[] = 'Failed to encode fallback retirement template JSON.';
                return $this->finish($payload);
            }
            if (file_put_contents($this->outputPath, $json . PHP_EOL) === false) {
                $this->errors[] = 'Failed to write fallback retirement template: ' . $this->outputPath;
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
        $wavePages = $this->wavePages();
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $sectionsIblockId,
                'ACTIVE' => 'Y',
                '=PROPERTY_MIGRATION_STATUS' => 'live',
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
            $fallbackPartial = $this->propertyString($sectionsIblockId, $sectionId, 'FALLBACK_PARTIAL');
            if ($this->page !== '' && $pageKey !== $this->page) {
                continue;
            }
            if ($wavePages !== [] && !in_array($pageKey, $wavePages, true)) {
                continue;
            }
            if (!$this->isAllowedSection($pageKey, $sectionKey)) {
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
                'fallback_partial_present' => $fallbackPartial !== '',
                'blocks_total' => $blockCounts['total'],
                'blocks_active' => $blockCounts['active'],
                'admin_editability_approved' => false,
                'fallback_retirement_approved' => false,
            ];
        }

        return $items;
    }

    private function payload(array $items): array
    {
        $config = function_exists('tacticum_rest_get_config_section') ? tacticum_rest_get_config_section('page_content') : [];
        $source = is_array($config) ? (string)($config['source'] ?? 'fallback') : 'fallback';
        $allowFallback = is_array($config) ? (bool)($config['allow_fallback'] ?? true) : true;

        return [
            'schema' => 'tacticum.content_storage.page_content_fallback_retirement.v1',
            'status' => 'draft',
            'date' => date('Y-m-d'),
            'source' => 'tools/content-storage-page-content-fallback-retirement-template.php',
            'release_evidence' => false,
            'retirement_allowed' => false,
            'generated_from' => [
                'page' => $this->page !== '' ? $this->page : 'all',
                'wave' => $this->wave !== '' ? $this->wave : 'all',
                'active_only' => true,
                'live_only' => true,
                'raw_copy_included' => false,
                'admin_links_included' => false,
                'fallback_partial_values_included' => false,
                'runtime_source' => $source,
                'runtime_allow_fallback' => $allowFallback,
            ],
            'rules' => [
                'This draft stores only section IDs, page keys, section keys, template keys, statuses, block counts and approval booleans.',
                'Do not add page copy, CTA text, contacts, admin URLs, screenshots, raw HTML or request data.',
                'Do not retire PHP fallback partials while status is draft.',
                'Do not set retirement_allowed=true until all evidence and owner gates are true.',
                'Fallback retirement is a separate code change and deployment.',
            ],
            'owners' => $this->ownerRows(),
            'production_evidence' => $this->productionEvidenceRows(),
            'owner_gates' => $this->ownerGateRows(),
            'required_final_rechecks' => $this->requiredFinalRechecks($items),
            'rollback_plan' => [
                'required' => true,
                'strategy' => 'Set page_content.source=fallback, keep allow_fallback=true and redeploy previous partial fallback code if needed.',
                'final_recheck_command' => 'npm run page-content:source:http:fallback:prod',
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

    private function productionEvidenceRows(): array
    {
        $rows = [];
        foreach ([
            'config_runtime_check_passed',
            'strict_page_content_audit_passed',
            'page_content_source_http_passed',
            'seo_check_prod_passed',
            'targeted_visual_smoke_passed',
            'targeted_browser_smoke_passed',
            'allow_fallback_true',
            'rollback_plan_approved',
            'admin_editability_review_passed',
        ] as $key) {
            $rows[$key] = false;
        }

        return $rows;
    }

    private function ownerGateRows(): array
    {
        $rows = [];
        foreach ([
            'architect_runtime_boundary_approved',
            'content_admin_editability_approved',
            'frontend_fallback_removal_approved',
            'qa_rollback_window_approved',
            'seo_no_regression_approved',
        ] as $key) {
            $rows[$key] = false;
        }

        return $rows;
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

    private function isAllowedSection(string $pageKey, string $sectionKey): bool
    {
        $allowed = [
            '/services/' => ['delivery-layer', 'process', 'tech'],
            '/price/' => ['features', 'workstreams'],
            '/contacts/' => ['routing', 'cards'],
            '/offer/' => ['product-bridge', 'bottom-cta'],
            '/' => ['ecosystem', 'fit-matrix', 'commercial'],
            '/about/' => ['company-trust', 'values-team', 'career-final'],
            '/calculator/' => ['calculator-outcome-cards', 'product-aware-estimate-cards'],
            '/aiagents/' => ['agents-bridge', 'how-it-works', 'services'],
        ];

        return isset($allowed[$pageKey]) && in_array($sectionKey, $allowed[$pageKey], true);
    }

    private function validateScope(): void
    {
        if ($this->page !== '' && $this->wave !== '') {
            $this->errors[] = '--page and --wave are mutually exclusive. Use one scoped selector.';
        }
        if ($this->wave !== '' && $this->wave !== 'all' && !array_key_exists($this->wave, self::WAVE_PAGES)) {
            $this->errors[] = '--wave must be one of: wave_1, wave_2, all.';
        }
    }

    private function wavePages(): array
    {
        if ($this->wave === '' || $this->wave === 'all') {
            return [];
        }

        return self::WAVE_PAGES[$this->wave] ?? [];
    }

    private function requiredFinalRechecks(array $items): array
    {
        $pages = $this->pageListFromItems($items);
        $pagesCsv = implode(',', $pages);

        return array_merge([
            'npm run config:runtime:check',
        ], $this->sourceRecheckCommands($pages), [
            'php tools/content-storage-audit.php --scope=page-content --strict --json',
            'npm run seo:check:prod',
            'TACTICUM_VISUAL_PAGES=' . $pagesCsv . ' npm run visual:smoke:prod',
            'TACTICUM_VISUAL_PAGES=' . $pagesCsv . ' npm run browser:smoke:prod',
        ]);
    }

    private function sourceRecheckCommands(array $pages): array
    {
        $commands = [];
        if ($this->containsAnyPage($pages, self::WAVE_PAGES['wave_1'])) {
            $commands[] = 'npm run page-content:source:http:prod';
        }
        if ($this->containsAnyPage($pages, self::WAVE_PAGES['wave_2'])) {
            $commands[] = 'npm run page-content:source:http:wave2:prod';
        }

        return $commands;
    }

    private function containsAnyPage(array $pages, array $candidates): bool
    {
        foreach ($pages as $page) {
            if (in_array($page, $candidates, true)) {
                return true;
            }
        }

        return false;
    }

    private function pageListFromItems(array $items): array
    {
        $pages = [];
        foreach ($items as $item) {
            $page = is_array($item) ? (string)($item['page'] ?? '') : '';
            if ($page !== '' && !in_array($page, $pages, true)) {
                $pages[] = $page;
            }
        }

        $order = array_merge(self::WAVE_PAGES['wave_1'], self::WAVE_PAGES['wave_2']);
        usort($pages, static function (string $left, string $right) use ($order): int {
            $leftIndex = array_search($left, $order, true);
            $rightIndex = array_search($right, $order, true);
            $leftRank = $leftIndex === false ? 1000 : (int)$leftIndex;
            $rightRank = $rightIndex === false ? 1000 : (int)$rightIndex;

            return $leftRank <=> $rightRank;
        });

        return $pages;
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
            fwrite(STDERR, 'Page-content fallback retirement draft written: ' . $this->outputPath . PHP_EOL);
            fwrite(STDERR, 'Items: ' . count($payload['items'] ?? []) . ', page=' . ($this->page !== '' ? $this->page : 'all') . ', wave=' . ($this->wave !== '' ? $this->wave : 'all') . ', retirement_allowed=false' . PHP_EOL);
        } else {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
        }

        return 0;
    }
}

function tacticum_content_storage_page_content_fallback_retirement_template_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-page-content-fallback-retirement-template.php [--page=/services/|--wave=wave_1|wave_2|all] [--output=/tmp/page-content-fallback-retirement.draft.json] [--force] [--document-root=/path/to/site]

Generates a no-raw-copy owner approval draft for retiring PHP fallback partials
for live page-content sections. The draft does not change public runtime, does
not remove partials and does not print raw page copy.

TEXT;
}

function tacticum_content_storage_page_content_fallback_retirement_parse_args(array $argv): array
{
    $options = [
        'document_root' => getenv('DOCUMENT_ROOT') ?: dirname(__DIR__),
        'output' => '',
        'force' => false,
        'page' => '',
        'wave' => '',
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            echo tacticum_content_storage_page_content_fallback_retirement_template_usage();
            exit(0);
        }
        if ($arg === '--force') {
            $options['force'] = true;
            continue;
        }
        if (str_starts_with($arg, '--document-root=')) {
            $options['document_root'] = substr($arg, strlen('--document-root='));
            continue;
        }
        if (str_starts_with($arg, '--output=')) {
            $options['output'] = substr($arg, strlen('--output='));
            continue;
        }
        if (str_starts_with($arg, '--page=')) {
            $options['page'] = substr($arg, strlen('--page='));
            continue;
        }
        if (str_starts_with($arg, '--wave=')) {
            $options['wave'] = substr($arg, strlen('--wave='));
            continue;
        }
        fwrite(STDERR, 'Unknown argument: ' . $arg . PHP_EOL . PHP_EOL);
        fwrite(STDERR, tacticum_content_storage_page_content_fallback_retirement_template_usage());
        exit(1);
    }

    return $options;
}

try {
    $options = tacticum_content_storage_page_content_fallback_retirement_parse_args($argv);
    $tool = new TacticumContentStoragePageContentFallbackRetirementTemplate(
        (string)$options['document_root'],
        (string)$options['output'],
        (bool)$options['force'],
        (string)$options['page'],
        (string)$options['wave']
    );
    exit($tool->run());
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_page_content_fallback_retirement_template_usage());
    exit(1);
}
