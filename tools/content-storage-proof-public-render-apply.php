#!/usr/bin/env php
<?php
declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumContentStorageProofPublicRenderApply
{
    private const ALLOWED_IBLOCKS = ['cases', 'feedback', 'clients'];
    private const ALLOWED_PRODUCTS = ['platform', 'agents', 'dev', 'forum'];
    private const REQUIRED_OWNERS = ['content', 'sales', 'seo'];
    private const PUBLIC_RENDER_PROPERTY = 'PUBLIC_RENDER_APPROVED';
    private const FORBIDDEN_KEYS = [
        'name',
        'title',
        'text',
        'preview_text',
        'detail_text',
        'client_name',
        'contact',
        'email',
        'phone',
        'url',
        'admin_edit_path',
        'admin_url',
    ];

    private bool $apply;
    private bool $json;
    private string $documentRoot;
    private string $approvalPath;
    private array $warnings = [];
    private array $errors = [];
    private array $actions = [];
    private array $schemaActions = [];

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

        $productsIblockId = $this->iblockId('products');
        if ($productsIblockId <= 0) {
            $this->errors[] = 'Missing products iblock config key.';
            return $this->finish();
        }

        $productMap = $this->productMap($productsIblockId);
        $iblockMap = $this->proofIblockMap($productsIblockId);
        if (!empty($this->errors)) {
            return $this->finish();
        }

        foreach ($payload['items'] as $item) {
            $this->planItem($item, $iblockMap, $productMap);
        }

        if (empty($this->errors) && $this->apply) {
            $this->applyActions();
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

        if (($payload['schema'] ?? '') !== 'tacticum.content_storage.proof_tagging_approval.v1') {
            $this->errors[] = 'schema must be tacticum.content_storage.proof_tagging_approval.v1.';
        }
        if (($payload['status'] ?? '') !== 'approved') {
            $this->errors[] = 'status must be approved. Draft approvals cannot enable public proof rendering.';
        }
        if (($payload['release_evidence'] ?? null) !== false) {
            $this->errors[] = 'release_evidence must be false; use aggregate proof audit and product HTTP output for release evidence.';
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

        $items = $payload['items'] ?? null;
        if (!is_array($items)) {
            $this->errors[] = 'items must be an array.';
            return;
        }

        $seen = [];
        foreach ($items as $index => $item) {
            $path = 'items[' . $index . ']';
            if (!is_array($item)) {
                $this->errors[] = "{$path} must be an object.";
                continue;
            }

            $iblock = (string)($item['iblock'] ?? '');
            $id = (int)($item['id'] ?? 0);
            if (!in_array($iblock, self::ALLOWED_IBLOCKS, true)) {
                $this->errors[] = "{$path}.iblock must be one of cases, feedback, clients.";
            }
            if ($id <= 0) {
                $this->errors[] = "{$path}.id must be a positive integer.";
            }
            $key = $iblock . ':' . $id;
            if (isset($seen[$key])) {
                $this->errors[] = "{$path} duplicates {$key}.";
            }
            $seen[$key] = true;

            $decision = (string)($item['decision'] ?? '');
            if (!in_array($decision, ['tag', 'global', 'not_public'], true)) {
                $this->errors[] = "{$path}.decision must be tag, global or not_public for public render apply.";
            }

            $productCodes = $item['product_codes'] ?? null;
            if (!is_array($productCodes)) {
                $this->errors[] = "{$path}.product_codes must be an array.";
                $productCodes = [];
            }

            $productSet = [];
            foreach ($productCodes as $code) {
                $code = (string)$code;
                if (!in_array($code, self::ALLOWED_PRODUCTS, true)) {
                    $this->errors[] = "{$path}.product_codes contains unknown product code: {$code}.";
                }
                if (isset($productSet[$code])) {
                    $this->errors[] = "{$path}.product_codes contains duplicate product code: {$code}.";
                }
                $productSet[$code] = true;
            }

            if (!is_bool($item['product_tag_approved'] ?? null)) {
                $this->errors[] = "{$path}.product_tag_approved must be boolean.";
            }
            if (!is_bool($item['public_render_approved'] ?? null)) {
                $this->errors[] = "{$path}.public_render_approved must be boolean.";
            }

            $productCount = count($productCodes);
            if ($decision === 'tag') {
                if ($productCount === 0) {
                    $this->errors[] = "{$path}.product_codes must be non-empty when decision=tag.";
                }
                if (($item['product_tag_approved'] ?? null) !== true) {
                    $this->errors[] = "{$path}.product_tag_approved must be true when decision=tag.";
                }
            }
            if (($decision === 'global' || $decision === 'not_public') && $productCount > 0) {
                $this->errors[] = "{$path}.product_codes must be empty when decision={$decision}.";
            }
            if (($item['public_render_approved'] ?? false) === true && $decision !== 'tag') {
                $this->errors[] = "{$path}.public_render_approved can be true only when decision=tag.";
            }
            if (($item['public_render_approved'] ?? false) === true && ($item['product_tag_approved'] ?? null) !== true) {
                $this->errors[] = "{$path}.product_tag_approved must be true when public_render_approved=true.";
            }
        }
    }

    private function validateNoRawContent(mixed $value, string $path): void
    {
        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $keyString = is_string($key) ? $key : (string)$key;
                if (is_string($key) && in_array(strtolower($key), self::FORBIDDEN_KEYS, true)) {
                    $this->errors[] = "{$path}.{$keyString} is forbidden; do not store raw proof copy, contacts or admin links.";
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

    private function productMap(int $productsIblockId): array
    {
        $byCode = [];
        $byId = [];
        foreach ($this->productCodes() as $code) {
            $id = $this->findElementIdByCode($productsIblockId, $code);
            if ($id <= 0) {
                $this->errors[] = "Product {$code} is missing in products iblock.";
                continue;
            }
            $byCode[$code] = $id;
            $byId[$id] = $code;
        }

        return [
            'by_code' => $byCode,
            'by_id' => $byId,
        ];
    }

    private function proofIblockMap(int $productsIblockId): array
    {
        $map = [];
        foreach (self::ALLOWED_IBLOCKS as $key) {
            $iblockId = $this->iblockId($key);
            if ($iblockId <= 0) {
                $this->errors[] = "Missing {$key} iblock config key.";
                continue;
            }

            $productProperty = $this->propertyFields($iblockId, 'PRODUCT');
            if ($productProperty === null) {
                $this->errors[] = "{$key} iblock has no PRODUCT relation property.";
                continue;
            }
            if (($productProperty['ACTIVE'] ?? 'N') !== 'Y') {
                $this->errors[] = "{$key} PRODUCT relation property is inactive.";
            }
            if (($productProperty['MULTIPLE'] ?? 'N') !== 'Y') {
                $this->errors[] = "{$key} PRODUCT relation property must be multiple.";
            }
            if ($this->propertyLinkIblockId((int)($productProperty['ID'] ?? 0)) !== $productsIblockId) {
                $this->errors[] = "{$key} PRODUCT relation property must point to products iblock.";
            }

            $this->ensurePublicRenderProperty($key, $iblockId);
            $map[$key] = $iblockId;
        }

        return $map;
    }

    private function ensurePublicRenderProperty(string $iblockKey, int $iblockId): void
    {
        $property = $this->propertyFields($iblockId, self::PUBLIC_RENDER_PROPERTY);
        if ($property !== null) {
            $this->schemaActions[] = [
                'iblock' => $iblockKey,
                'iblock_id' => $iblockId,
                'property' => self::PUBLIC_RENDER_PROPERTY,
                'changed' => false,
                'status' => 'exists',
            ];
            if (($property['ACTIVE'] ?? 'N') !== 'Y') {
                $this->errors[] = "{$iblockKey} " . self::PUBLIC_RENDER_PROPERTY . ' property is inactive.';
            }
            if (($property['PROPERTY_TYPE'] ?? '') !== 'S') {
                $this->errors[] = "{$iblockKey} " . self::PUBLIC_RENDER_PROPERTY . ' property must be string.';
            }
            if (($property['MULTIPLE'] ?? 'N') !== 'N') {
                $this->errors[] = "{$iblockKey} " . self::PUBLIC_RENDER_PROPERTY . ' property must be single.';
            }
            return;
        }

        $action = [
            'iblock' => $iblockKey,
            'iblock_id' => $iblockId,
            'property' => self::PUBLIC_RENDER_PROPERTY,
            'changed' => true,
            'status' => $this->apply ? 'pending_apply' : 'would_create',
        ];

        if ($this->apply) {
            $createdId = $this->createPublicRenderProperty($iblockId);
            $action['status'] = 'created';
            $action['property_id'] = $createdId;
        }

        $this->schemaActions[] = $action;
    }

    private function createPublicRenderProperty(int $iblockId): int
    {
        $property = new CIBlockProperty();
        $id = (int)$property->Add([
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'NAME' => 'Public proof render approved',
            'CODE' => self::PUBLIC_RENDER_PROPERTY,
            'SORT' => 900,
            'PROPERTY_TYPE' => 'S',
            'MULTIPLE' => 'N',
            'FILTRABLE' => 'Y',
        ]);

        if ($id <= 0) {
            throw new RuntimeException('Failed to create property ' . self::PUBLIC_RENDER_PROPERTY . ' on iblock #' . $iblockId . ': ' . (string)$property->LAST_ERROR);
        }

        return $id;
    }

    private function planItem(array $item, array $iblockMap, array $productMap): void
    {
        $iblock = (string)$item['iblock'];
        $elementId = (int)$item['id'];
        $iblockId = (int)($iblockMap[$iblock] ?? 0);
        if ($iblockId <= 0) {
            $this->errors[] = "Cannot plan {$iblock} #{$elementId}: iblock is not available.";
            return;
        }

        $element = $this->elementInfo($iblockId, $elementId);
        if ($element === null) {
            $this->errors[] = "{$iblock} #{$elementId} does not exist in configured iblock.";
            return;
        }
        if (($element['ACTIVE'] ?? 'N') !== 'Y') {
            $this->warnings[] = "{$iblock} #{$elementId} is inactive; public render approval will not affect active product proof.";
        }

        $targetPublic = (($item['decision'] ?? '') === 'tag')
            && (($item['product_tag_approved'] ?? false) === true)
            && (($item['public_render_approved'] ?? false) === true);
        $targetProductIds = [];
        foreach (($item['product_codes'] ?? []) as $code) {
            $productId = (int)($productMap['by_code'][(string)$code] ?? 0);
            if ($productId <= 0) {
                $this->errors[] = "Product {$code} is not available for {$iblock} #{$elementId}.";
                continue;
            }
            $targetProductIds[] = $productId;
        }
        $targetProductIds = $this->normalizeIds($targetProductIds);
        $currentProductIds = $this->productIdsForElement($iblockId, $elementId);
        if ($targetPublic && $currentProductIds !== $targetProductIds) {
            $this->errors[] = "{$iblock} #{$elementId} PRODUCT relation must match approved product_codes before public render can be approved.";
        }

        $currentPublic = $this->publicRenderApproved($iblockId, $elementId);
        $changed = $currentPublic !== $targetPublic;
        $this->actions[] = [
            'iblock' => $iblock,
            'iblock_id' => $iblockId,
            'id' => $elementId,
            'decision' => (string)$item['decision'],
            'current_public_render_approved' => $currentPublic,
            'target_public_render_approved' => $targetPublic,
            'current_product_codes' => $this->codesForIds($currentProductIds, $productMap['by_id']),
            'target_product_codes' => $this->codesForIds($targetProductIds, $productMap['by_id']),
            'changed' => $changed,
            'status' => $changed ? ($this->apply ? 'pending_apply' : 'would_update') : 'unchanged',
        ];
    }

    private function applyActions(): void
    {
        foreach ($this->actions as $index => $action) {
            if (($action['changed'] ?? false) !== true) {
                continue;
            }

            $iblockId = (int)$action['iblock_id'];
            $elementId = (int)$action['id'];
            $targetPublic = (bool)$action['target_public_render_approved'];
            CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [self::PUBLIC_RENDER_PROPERTY => $targetPublic ? 'Y' : '']);

            $actualPublic = $this->publicRenderApproved($iblockId, $elementId);
            if ($actualPublic !== $targetPublic) {
                $this->actions[$index]['status'] = 'verify_failed';
                $this->actions[$index]['actual_public_render_approved'] = $actualPublic;
                $this->errors[] = (string)$action['iblock'] . " #{$elementId} " . self::PUBLIC_RENDER_PROPERTY . ' verification failed.';
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
                'approval_path' => $this->approvalPath,
                'safe_for_release_evidence' => false,
                'release_evidence_hint' => 'Use content-storage-audit --scope=proof aggregate output and product HTTP proof_source markers after apply.',
                'summary' => $summary,
                'schema_actions' => $this->safeSchemaActions(),
                'actions' => $this->safeActions(),
                'warnings' => $this->warnings,
                'errors' => $this->errors,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

            return empty($this->errors) ? 0 : 1;
        }

        $this->line(empty($this->errors)
            ? 'Content storage proof public render apply check passed.'
            : 'Content storage proof public render apply failed.');
        $this->line('Mode: ' . ($this->apply ? 'apply' : 'dry-run'));
        $this->line('Release evidence: use aggregate content-storage-audit output and product HTTP proof_source markers after apply.');
        $this->line('');

        foreach ($this->schemaActions as $schemaAction) {
            $prefix = ($schemaAction['status'] ?? '') === 'created'
                ? '[apply]'
                : (($schemaAction['status'] ?? '') === 'would_create' ? '[dry-run]' : '[check]');
            $this->line(sprintf(
                '%s %s property %s on %s #%d status=%s',
                $prefix,
                ($schemaAction['changed'] ?? false) ? 'Ensure' : 'Check',
                (string)$schemaAction['property'],
                (string)$schemaAction['iblock'],
                (int)$schemaAction['iblock_id'],
                (string)$schemaAction['status']
            ));
        }

        foreach ($this->actions as $action) {
            $prefix = ($action['status'] ?? '') === 'applied'
                ? '[apply]'
                : (($action['status'] ?? '') === 'would_update' ? '[dry-run]' : '[check]');
            $this->line(sprintf(
                '%s Set %s #%d %s=%s current=%s decision=%s products=%s status=%s',
                $prefix,
                (string)$action['iblock'],
                (int)$action['id'],
                self::PUBLIC_RENDER_PROPERTY,
                !empty($action['target_public_render_approved']) ? 'Y' : '-',
                !empty($action['current_public_render_approved']) ? 'Y' : '-',
                (string)$action['decision'],
                $this->codeList($action['target_product_codes'] ?? []),
                (string)$action['status']
            ));
        }

        $this->line('');
        $this->line('Proof public render summary:'
            . ' total=' . $summary['total']
            . ', approve=' . $summary['approve']
            . ', clear=' . $summary['clear']
            . ', changed=' . $summary['changed']
            . ', unchanged=' . $summary['unchanged']
            . ', applied=' . $summary['applied']
            . ', schema_created=' . $summary['schema_created']
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

    private function safeSchemaActions(): array
    {
        return array_map(static function (array $action): array {
            return [
                'iblock' => $action['iblock'],
                'property' => $action['property'],
                'changed' => $action['changed'],
                'status' => $action['status'],
            ];
        }, $this->schemaActions);
    }

    private function safeActions(): array
    {
        return array_map(static function (array $action): array {
            return [
                'iblock' => $action['iblock'],
                'id' => $action['id'],
                'decision' => $action['decision'],
                'current_public_render_approved' => $action['current_public_render_approved'],
                'target_public_render_approved' => $action['target_public_render_approved'],
                'current_product_codes' => $action['current_product_codes'],
                'target_product_codes' => $action['target_product_codes'],
                'changed' => $action['changed'],
                'status' => $action['status'],
                'actual_public_render_approved' => $action['actual_public_render_approved'] ?? null,
            ];
        }, $this->actions);
    }

    private function summary(): array
    {
        $summary = [
            'total' => count($this->actions),
            'approve' => 0,
            'clear' => 0,
            'changed' => 0,
            'unchanged' => 0,
            'applied' => 0,
            'schema_created' => 0,
        ];

        foreach ($this->schemaActions as $schemaAction) {
            if (($schemaAction['status'] ?? '') === 'created') {
                $summary['schema_created']++;
            }
        }
        foreach ($this->actions as $action) {
            if (($action['target_public_render_approved'] ?? false) === true) {
                $summary['approve']++;
            } else {
                $summary['clear']++;
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

    private function publicRenderApproved(int $iblockId, int $elementId): bool
    {
        $value = $this->propertyValue($iblockId, $elementId, self::PUBLIC_RENDER_PROPERTY);

        return strtoupper(trim((string)$value)) === 'Y';
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

        return $this->normalizeIds($ids);
    }

    private function propertyValue(int $iblockId, int $elementId, string $code): mixed
    {
        $result = CIBlockElement::GetProperty(
            $iblockId,
            $elementId,
            ['sort' => 'asc', 'id' => 'asc'],
            ['CODE' => $code]
        );
        $property = $result->Fetch();

        return is_array($property) ? ($property['VALUE'] ?? null) : null;
    }

    private function normalizeIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    private function codesForIds(array $ids, array $productById): array
    {
        $codes = [];
        foreach ($this->normalizeIds($ids) as $id) {
            $codes[] = $productById[$id] ?? ('unknown:' . $id);
        }

        return $codes;
    }

    private function codeList(array $codes): string
    {
        return count($codes) > 0 ? implode(',', array_map('strval', $codes)) : '-';
    }

    private function productCodes(): array
    {
        if (function_exists('tacticum_product_content_codes')) {
            return array_keys(tacticum_product_content_codes());
        }

        return self::ALLOWED_PRODUCTS;
    }

    private function elementInfo(int $iblockId, int $elementId): ?array
    {
        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ID' => $elementId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            ['nTopCount' => 1],
            ['ID', 'ACTIVE']
        );
        $element = $result->Fetch();

        return is_array($element) ? $element : null;
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
        if ($propertyId <= 0) {
            return 0;
        }

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

function tacticum_content_storage_proof_public_render_apply_usage(): string
{
    return <<<TEXT
Usage:
  php tools/content-storage-proof-public-render-apply.php --approval=/path/to/approved.json [--apply] [--json] [--document-root=/path/to/site]
  php tools/content-storage-proof-public-render-apply.php /path/to/approved.json [--apply]

Creates/checks the PUBLIC_RENDER_APPROVED proof gate on cases/feedback/clients
and applies owner-approved public render flags. Dry-run is the default. The
approval JSON must have status=approved; draft files are rejected. The tool
changes only PUBLIC_RENDER_APPROVED and does not print proof names, copy,
contacts or raw claims.

TEXT;
}

$apply = false;
$json = false;
$approvalPath = '';
$defaultDocumentRoot = isset($_SERVER['DOCUMENT_ROOT']) && trim((string)$_SERVER['DOCUMENT_ROOT']) !== ''
    ? (string)$_SERVER['DOCUMENT_ROOT']
    : dirname(__DIR__);
$documentRoot = $defaultDocumentRoot;

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        echo tacticum_content_storage_proof_public_render_apply_usage();
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
    fwrite(STDERR, tacticum_content_storage_proof_public_render_apply_usage());
    exit(2);
}

try {
    $tool = new TacticumContentStorageProofPublicRenderApply($apply, $json, $documentRoot, $approvalPath);
    exit($tool->run());
} catch (Throwable $exception) {
    if ($json) {
        echo json_encode([
            'success' => false,
            'mode' => $apply ? 'apply' : 'dry-run',
            'safe_for_release_evidence' => false,
            'warnings' => [],
            'errors' => [$exception->getMessage()],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
    } else {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL . PHP_EOL);
        fwrite(STDERR, tacticum_content_storage_proof_public_render_apply_usage());
    }

    exit(1);
}
