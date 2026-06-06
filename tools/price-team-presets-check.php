#!/usr/bin/env php
<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Tacticum\Price\TeamPresetService;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

final class TacticumPriceTeamPresetsCheck
{
    private bool $strict;
    private bool $json;
    private string $documentRoot;
    private array $errors = [];
    private array $warnings = [];

    public function __construct(bool $strict, bool $json, string $documentRoot)
    {
        $this->strict = $strict;
        $this->json = $json;
        $this->documentRoot = rtrim($documentRoot, '/');
    }

    public function run(): int
    {
        $this->loadBitrix();
        if (!Loader::includeModule('iblock')) {
            $this->errors[] = 'Bitrix iblock module is not available.';
        }

        $config = function_exists('tacticum_rest_get_config_section') ? tacticum_rest_get_config_section('price') : [];
        $source = (string)($config['team_presets_source'] ?? 'fallback');
        $allowFallback = (bool)($config['allow_team_presets_fallback'] ?? true);
        $cacheTtl = isset($config['team_presets_cache_ttl']) ? (int)$config['team_presets_cache_ttl'] : null;
        $presetsIblockId = function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id('team_presets') : 0;
        $rolesIblockId = function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id('team_preset_roles') : 0;
        $ratesIblockId = function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id('rates') : 0;

        foreach (function_exists('tacticum_rest_validate_config') ? tacticum_rest_validate_config(['price']) : [] as $error) {
            $this->errors[] = 'config.' . ($error['key'] ?? 'unknown') . ':' . ($error['code'] ?? 'unknown');
        }

        if ($this->strict) {
            if ($source !== 'bitrix') {
                $this->errors[] = 'Strict mode requires price.team_presets_source=bitrix.';
            }
            if ($allowFallback) {
                $this->errors[] = 'Strict mode requires price.allow_team_presets_fallback=false.';
            }
        }

        $schema = $this->schemaSummary($presetsIblockId, $rolesIblockId, $ratesIblockId);
        $rows = $this->rowSummary($presetsIblockId, $rolesIblockId);
        $runtimePresets = class_exists(TeamPresetService::class) ? TeamPresetService::presets() : [];

        if ($source === 'bitrix' || $this->strict) {
            foreach (['team_presets' => $presetsIblockId, 'team_preset_roles' => $rolesIblockId, 'rates' => $ratesIblockId] as $key => $id) {
                if ($id <= 0) {
                    $this->errors[] = "Missing iblock config key {$key}.";
                }
            }
            if (!$schema['valid']) {
                $this->errors[] = 'Team preset schema is incomplete.';
            }
            if ($rows['active_presets'] < 1) {
                $this->errors[] = 'No active team presets found.';
            }
            if ($rows['active_roles'] < 1) {
                $this->errors[] = 'No active team preset roles found.';
            }
            if ($rows['roles_without_rate_element'] > 0) {
                $this->errors[] = 'Some team preset roles do not have RATE_ELEMENT relation.';
            }
        } elseif ($presetsIblockId <= 0 || $rolesIblockId <= 0) {
            $this->warnings[] = 'Bitrix team preset iblock IDs are not configured; runtime will use fallback while allowed.';
        }

        $summary = [
            'schema' => 'tacticum.price.team_presets_check.v1',
            'strict' => $this->strict,
            'valid' => $this->errors === [],
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'config' => [
                'team_presets_source' => $source,
                'team_presets_cache_ttl' => $cacheTtl,
                'allow_team_presets_fallback' => $allowFallback,
            ],
            'iblocks' => [
                'team_presets' => $presetsIblockId,
                'team_preset_roles' => $rolesIblockId,
                'rates' => $ratesIblockId,
            ],
            'schema_summary' => $schema,
            'row_summary' => $rows,
            'runtime' => [
                'presets_count' => count($runtimePresets),
                'source' => (string)($runtimePresets[0]['source'] ?? 'none'),
                'codes' => array_map(static fn(array $preset): string => (string)($preset['code'] ?? ''), $runtimePresets),
            ],
        ];

        if ($this->json) {
            echo json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
        } else {
            $this->printSummary($summary);
        }

        return $summary['valid'] ? 0 : 1;
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

    private function schemaSummary(int $presetsIblockId, int $rolesIblockId, int $ratesIblockId): array
    {
        $requiredPresetProperties = ['SCENARIO', 'DEFAULT_WORKLOAD', 'RECOMMENDED_DURATION', 'VERSION', 'ANALYTICS_CODE'];
        $requiredRoleProperties = ['PRESET', 'RATE_ELEMENT', 'PREFERRED_LEVELS', 'QUANTITY', 'REQUIRED', 'ROLE_KEY', 'FALLBACK_KEYWORDS'];
        $presetProperties = $this->propertyCodes($presetsIblockId);
        $roleProperties = $this->propertyCodes($rolesIblockId);
        $missingPreset = array_values(array_diff($requiredPresetProperties, $presetProperties));
        $missingRole = array_values(array_diff($requiredRoleProperties, $roleProperties));
        $presetLink = $this->propertyLinkIblockId($rolesIblockId, 'PRESET');
        $rateLink = $this->propertyLinkIblockId($rolesIblockId, 'RATE_ELEMENT');

        return [
            'valid' => $missingPreset === []
                && $missingRole === []
                && ($presetsIblockId <= 0 || $presetLink === $presetsIblockId)
                && ($ratesIblockId <= 0 || $rateLink === $ratesIblockId),
            'missing_team_presets_properties' => $missingPreset,
            'missing_team_preset_roles_properties' => $missingRole,
            'preset_relation_link_iblock_id' => $presetLink,
            'rate_relation_link_iblock_id' => $rateLink,
        ];
    }

    private function rowSummary(int $presetsIblockId, int $rolesIblockId): array
    {
        $presetIds = $this->activeElementIds($presetsIblockId);
        $roles = $this->activeRoleRows($rolesIblockId);
        $rolesWithoutPreset = 0;
        $rolesWithoutRate = 0;
        foreach ($roles as $role) {
            if ((int)($role['preset_id'] ?? 0) <= 0) {
                $rolesWithoutPreset++;
            }
            if ((int)($role['rate_element_id'] ?? 0) <= 0) {
                $rolesWithoutRate++;
            }
        }

        return [
            'active_presets' => count($presetIds),
            'active_roles' => count($roles),
            'roles_without_preset' => $rolesWithoutPreset,
            'roles_without_rate_element' => $rolesWithoutRate,
        ];
    }

    private function activeElementIds(int $iblockId): array
    {
        if ($iblockId <= 0) {
            return [];
        }

        $ids = [];
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID']
        );
        while ($row = $result->Fetch()) {
            $ids[] = (int)($row['ID'] ?? 0);
        }

        return array_values(array_filter($ids));
    }

    private function activeRoleRows(int $iblockId): array
    {
        if ($iblockId <= 0) {
            return [];
        }

        $roles = [];
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID', 'IBLOCK_ID']
        );
        while ($element = $result->GetNextElement()) {
            $fields = $element->GetFields();
            $properties = $element->GetProperties();
            $roles[] = [
                'id' => (int)($fields['ID'] ?? 0),
                'preset_id' => (int)($properties['PRESET']['VALUE'] ?? 0),
                'rate_element_id' => (int)($properties['RATE_ELEMENT']['VALUE'] ?? 0),
            ];
        }

        return $roles;
    }

    private function propertyCodes(int $iblockId): array
    {
        if ($iblockId <= 0) {
            return [];
        }

        $codes = [];
        $result = CIBlockProperty::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y']);
        while ($property = $result->Fetch()) {
            $code = trim((string)($property['CODE'] ?? ''));
            if ($code !== '') {
                $codes[] = $code;
            }
        }

        return $codes;
    }

    private function propertyLinkIblockId(int $iblockId, string $code): int
    {
        if ($iblockId <= 0) {
            return 0;
        }

        $result = CIBlockProperty::GetList(['ID' => 'ASC'], ['IBLOCK_ID' => $iblockId, 'CODE' => $code, 'ACTIVE' => 'Y']);
        $property = $result->Fetch();

        return is_array($property) ? (int)($property['LINK_IBLOCK_ID'] ?? 0) : 0;
    }

    private function printSummary(array $summary): void
    {
        echo 'Price Team Presets Check' . PHP_EOL;
        echo 'Valid: ' . ($summary['valid'] ? 'yes' : 'no') . PHP_EOL;
        echo 'Mode: source=' . $summary['config']['team_presets_source']
            . ', fallback=' . ($summary['config']['allow_team_presets_fallback'] ? 'true' : 'false')
            . ', cache_ttl=' . (string)$summary['config']['team_presets_cache_ttl'] . PHP_EOL;
        echo 'Iblocks: team_presets=#' . $summary['iblocks']['team_presets']
            . ', team_preset_roles=#' . $summary['iblocks']['team_preset_roles']
            . ', rates=#' . $summary['iblocks']['rates'] . PHP_EOL;
        echo 'Rows: presets=' . $summary['row_summary']['active_presets']
            . ', roles=' . $summary['row_summary']['active_roles']
            . ', roles_without_rate=' . $summary['row_summary']['roles_without_rate_element'] . PHP_EOL;
        echo 'Runtime: presets=' . $summary['runtime']['presets_count']
            . ', source=' . $summary['runtime']['source']
            . ', codes=' . implode(',', array_filter($summary['runtime']['codes'])) . PHP_EOL;

        foreach ($summary['warnings'] as $warning) {
            echo 'Warning: ' . $warning . PHP_EOL;
        }
        foreach ($summary['errors'] as $error) {
            echo 'Error: ' . $error . PHP_EOL;
        }
    }
}

function tacticum_price_team_presets_check_usage(): string
{
    return <<<TEXT
Usage:
  php tools/price-team-presets-check.php [--strict] [--json] [--document-root=/path/to/site]

Checks team preset config, Bitrix schema and safe aggregate rows.
Strict mode is the gate before switching price.team_presets_source=bitrix.

TEXT;
}

$options = ['strict' => false, 'json' => false, 'document_root' => getenv('DOCUMENT_ROOT') ?: dirname(__DIR__), 'help' => false];
foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        $options['help'] = true;
        continue;
    }
    if ($argument === '--strict') {
        $options['strict'] = true;
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
    fwrite(STDERR, 'Unknown argument: ' . $argument . PHP_EOL);
    fwrite(STDERR, tacticum_price_team_presets_check_usage());
    exit(2);
}

if ($options['help']) {
    echo tacticum_price_team_presets_check_usage();
    exit(0);
}

$documentRoot = realpath((string)$options['document_root']);
if ($documentRoot === false) {
    fwrite(STDERR, 'Document root does not exist: ' . (string)$options['document_root'] . PHP_EOL);
    exit(2);
}

try {
    exit((new TacticumPriceTeamPresetsCheck((bool)$options['strict'], (bool)$options['json'], $documentRoot))->run());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
