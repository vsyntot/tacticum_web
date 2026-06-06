#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStorageProofTaggingProposal
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
        $summary = [
            'tag' => 0,
            'global' => 0,
            'not_public' => 0,
        ];

        foreach (self::PROOF_KEYS as $key) {
            if (!$this->scopeMatches($key)) {
                continue;
            }

            $iblockId = $this->iblockId($key);
            if ($iblockId <= 0) {
                $this->warnings[] = "Iblock {$key} is not configured.";
                continue;
            }

            foreach ($this->activeItems($key, $iblockId) as $row) {
                $proposal = $this->proposalFor($row);
                $summary[$proposal['decision']]++;
                $items[] = [
                    'iblock' => $row['iblock'],
                    'id' => $row['id'],
                    'decision' => $proposal['decision'],
                    'product_codes' => $proposal['product_codes'],
                    'product_tag_approved' => $proposal['decision'] === 'tag',
                    'public_render_approved' => false,
                    'proposal_reason' => $proposal['reason'],
                ];
            }
        }

        $payload = [
            'schema' => 'tacticum.content_storage.proof_tagging_approval.v1',
            'status' => 'draft',
            'date' => date('Y-m-d'),
            'source' => 'tools/content-storage-proof-tagging-proposal.php',
            'release_evidence' => false,
            'generated_from' => [
                'scope' => $this->scope,
                'active_only' => true,
                'raw_copy_included' => false,
                'admin_links_included' => false,
                'proposal_only' => true,
            ],
            'rules' => [
                'This proposal stores only iblock keys, element IDs, product codes, decisions and approval booleans.',
                'Do not add customer names, testimonial text, case text, contacts, admin URLs or raw claims.',
                'Review proposal_reason and product_codes before owner approval.',
                'Decision tag means the proposed PRODUCT relation should be owner-approved before apply.',
                'public_render_approved stays false until separate public proof rendering approval.',
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
            throw new RuntimeException('Failed to encode proof tagging proposal JSON.');
        }
        $json .= PHP_EOL;

        if ($this->outputPath !== '') {
            $this->writeOutput($json);
            fwrite(STDERR, 'Proof tagging proposal draft written: ' . $this->outputPath . PHP_EOL);
            fwrite(STDERR, 'Items: ' . count($items)
                . ', tag=' . $summary['tag']
                . ', global=' . $summary['global']
                . ', not_public=' . $summary['not_public']
                . ', scope=' . $this->scope . PHP_EOL);
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

    private function activeItems(string $key, int $iblockId): array
    {
        $items = [];
        $select = ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT'];
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            $select
        );

        while ($element = $result->Fetch()) {
            $id = (int)($element['ID'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $items[] = [
                'iblock' => $key,
                'id' => $id,
                'name' => $this->plain((string)($element['NAME'] ?? '')),
                'preview' => $this->plain((string)($element['PREVIEW_TEXT'] ?? '')),
                'detail' => $this->plain((string)($element['DETAIL_TEXT'] ?? '')),
                'sections' => $this->sectionNames($id),
                'properties' => $this->properties($iblockId, $id),
            ];
        }

        return $items;
    }

    private function proposalFor(array $row): array
    {
        if (($row['iblock'] ?? '') === 'clients') {
            return [
                'decision' => 'global',
                'product_codes' => [],
                'reason' => 'Client logo/trust item has no product-specific proof context in the current iblock fields.',
            ];
        }

        $text = $this->lower(implode(' ', [
            $row['name'] ?? '',
            $row['preview'] ?? '',
            $row['detail'] ?? '',
            implode(' ', is_array($row['sections'] ?? null) ? $row['sections'] : []),
            implode(' ', is_array($row['properties'] ?? null) ? $row['properties'] : []),
        ]));

        $products = [];
        $reasons = [];

        if ($this->hasAny($text, ['sap', 'superset', 'clickhouse', 'hadoop', 'airflow', 'python', 'etl', 'импортозам', 'bi-стек', 'би-стек'])) {
            $products = $this->addProducts($products, ['dev', 'platform']);
            $reasons[] = 'BI/data stack, migration or engineering implementation evidence.';
        }
        if ($this->hasAny($text, ['предиктив', 'прогноз', 'скоринг', 'скор-балл', 'скор балл', 'модель', 'рекомендац'])) {
            $products = $this->addProducts($products, ['platform', 'dev']);
            $reasons[] = 'ML/analytics model evidence fits Platform plus custom implementation.';
        }
        if ($this->hasAny($text, ['маршрут', 'доставк', 'tms', 'грузовик', 'мобильное приложение'])) {
            $products = $this->addProducts($products, ['platform', 'dev']);
            $reasons[] = 'Optimization algorithm and operational integrations fit Platform plus Dev.';
        }
        if ($this->hasAny($text, ['агент', 'бот', 'ассистент', 'telegram', 'dialogflow', 'faq', 'nlp', 'кандидат'])) {
            $products = $this->addProducts($products, ['agents']);
            $reasons[] = 'Assistant/bot/NLP/functional automation evidence fits Agents.';
        }
        if ($this->hasAny($text, ['клиент', 'поддержк', 'продаж', 'лид', 'обращен', 'звон', 'колл', 'оператор', 'omnichannel', 'whatsapp', 'голос', 'диалог', 'сайт'])) {
            $products = $this->addProducts($products, ['forum']);
            $reasons[] = 'Customer channel, support, sales or dialogue evidence fits Forum.';
        }

        if ($this->hasAny($text, ['sap', 'superset', 'clickhouse', 'hadoop', 'airflow', 'python', 'etl', 'импортозам', 'bi-стек', 'би-стек'])) {
            $products = $this->preferOnly($products, ['platform', 'dev']);
            $reasons[] = 'Data/BI migration evidence should not be broadened to bot or dialogue products.';
        }
        if ($this->hasAny($text, ['дефолт', 'скоринг', 'скор-балл', 'скор балл', 'прогнозирования рисков'])) {
            $products = $this->preferOnly($products, ['platform', 'dev']);
            $reasons[] = 'Risk scoring evidence is a model/platform implementation, not an Agents proof despite generic wording.';
        }
        if ($this->hasAny($text, ['маршрут', 'доставк', 'tms', 'грузовик', 'мобильное приложение'])) {
            $products = $this->preferOnly($products, ['platform', 'dev']);
            $reasons[] = 'Logistics optimization evidence is algorithm plus integration, not a dialogue proof.';
        }
        if (
            $this->hasAny($text, ['рекомендац'])
            && !$this->hasAny($text, ['агент', 'бот', 'ассистент', 'telegram', 'dialogflow', 'omnichannel', 'whatsapp', 'диалог'])
        ) {
            $products = $this->preferOnly($products, ['platform', 'dev']);
            $reasons[] = 'Recommendation-system evidence is product analytics/model proof, not customer-dialogue proof.';
        }
        if ($this->hasAny($text, ['колл', 'звон', 'оператор', 'ip-телефони', 'omnichannel', 'whatsapp'])) {
            $products = $this->addProducts($products, ['agents', 'forum']);
            $products = $this->preferOnly($products, ['agents', 'forum']);
            $reasons[] = 'Call-center or omnichannel evidence is best tied to Agents and Forum.';
        }
        if (
            $this->hasAny($text, ['поддержк', 'обращен', 'запрос'])
            && $this->hasAny($text, ['агент', 'бот', 'telegram', 'голос', 'crm'])
        ) {
            $products = $this->addProducts($products, ['agents', 'forum']);
            $products = $this->preferOnly($products, ['agents', 'forum']);
            $reasons[] = 'Support automation evidence is best tied to Agents and Forum.';
        }
        if ($this->hasAny($text, ['найм', 'кандидат'])) {
            $products = $this->preferOnly($products, ['agents']);
            $reasons[] = 'HR/candidate assistant is an internal functional assistant, not a customer dialogue proof.';
        }
        if ($this->hasAny($text, ['одежд', 'онлайн-продаж', 'онлайн продаж'])) {
            $products = $this->addProducts($products, ['agents', 'forum']);
            $products = $this->preferOnly($products, ['agents', 'forum']);
            $reasons[] = 'Retail sales agent evidence is best tied to Agents and Forum.';
        }

        if (empty($products)) {
            return [
                'decision' => 'global',
                'product_codes' => [],
                'reason' => 'No strong product-specific signal found; keep as global proof until owner review.',
            ];
        }

        return [
            'decision' => 'tag',
            'product_codes' => $products,
            'reason' => implode(' ', array_values(array_unique($reasons))),
        ];
    }

    private function properties(int $iblockId, int $elementId): array
    {
        $values = [];
        $result = CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc', 'id' => 'asc'], []);
        while ($property = $result->Fetch()) {
            $code = (string)($property['CODE'] ?? '');
            if ($code === 'PRODUCT') {
                continue;
            }
            $value = $property['VALUE'] ?? null;
            if (is_scalar($value)) {
                $plain = $this->plain((string)$value);
                if ($plain !== '') {
                    $values[] = $plain;
                }
            }
        }

        return $values;
    }

    private function sectionNames(int $elementId): array
    {
        $names = [];
        $result = CIBlockElement::GetElementGroups($elementId, true, ['ID', 'NAME']);
        while ($section = $result->Fetch()) {
            $name = $this->plain((string)($section['NAME'] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
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

    private function plain(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function lower(string $value): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    private function hasAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($text, $this->lower($needle))) {
                return true;
            }
        }

        return false;
    }

    private function addProducts(array $current, array $add): array
    {
        foreach ($add as $code) {
            if (!in_array($code, $current, true)) {
                $current[] = $code;
            }
        }

        return $this->orderedProducts($current);
    }

    private function preferOnly(array $current, array $preferred): array
    {
        $filtered = array_values(array_filter($current, static fn (string $code): bool => in_array($code, $preferred, true)));

        return $this->orderedProducts(!empty($filtered) ? $filtered : $preferred);
    }

    private function orderedProducts(array $codes): array
    {
        $allowedOrder = ['platform', 'agents', 'dev', 'forum'];
        $set = array_fill_keys($codes, true);
        $ordered = [];
        foreach ($allowedOrder as $code) {
            if (isset($set[$code])) {
                $ordered[] = $code;
            }
        }

        return $ordered;
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

function tacticum_content_storage_proof_tagging_proposal_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-proof-tagging-proposal.php [--scope=all|cases|feedback|clients] [--output=/tmp/content-storage-proof-tagging-proposal.draft.json] [--force] [--document-root=/path/to/site]

Generates a draft owner approval JSON with proposed PRODUCT tags for active
cases/feedback/clients items. The proposal reads proof fields internally, but
the output stores only IDs, product codes, decisions and short proposal reasons.
It is not release evidence and it keeps public_render_approved=false.

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
        echo tacticum_content_storage_proof_tagging_proposal_usage();
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
    fwrite(STDERR, tacticum_content_storage_proof_tagging_proposal_usage());
    exit(2);
}

if (!in_array($scope, ['all', 'cases', 'feedback', 'clients'], true)) {
    fwrite(STDERR, "Unknown scope: {$scope}" . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_proof_tagging_proposal_usage());
    exit(2);
}

try {
    $tool = new TacticumContentStorageProofTaggingProposal($documentRoot, $scope, $outputPath, $force);
    exit($tool->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL . PHP_EOL);
    fwrite(STDERR, tacticum_content_storage_proof_tagging_proposal_usage());
    exit(1);
}
