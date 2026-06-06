#!/usr/bin/env php
<?php

declare(strict_types=1);

use Bitrix\Main\Loader;

require_once __DIR__ . '/bitrix-cli-env.php';

tacticum_tools_reexec_with_short_open_tag($argv);

const TACTICUM_PRICE_TEAM_PRESETS_IBLOCK_TYPE = 'tacticum_content';

final class TacticumPriceTeamPresetsMigration
{
    private bool $apply;
    private string $documentRoot;
    private array $siteIds = [];

    public function __construct(bool $apply, string $documentRoot)
    {
        $this->apply = $apply;
        $this->documentRoot = rtrim($documentRoot, '/');
    }

    public function run(): void
    {
        $this->loadBitrix();
        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Bitrix iblock module is not available.');
        }

        $this->siteIds = $this->siteIds();
        $this->ensureIblockType();

        $presetsIblockId = $this->ensureIblock('tacticum_team_presets', 'Tacticum Team Presets', 130);
        $rolesIblockId = $this->ensureIblock('tacticum_team_preset_roles', 'Tacticum Team Preset Roles', 140);
        $ratesIblockId = function_exists('tacticum_rest_get_iblock_id') ? tacticum_rest_get_iblock_id('rates') : 0;

        if ($presetsIblockId > 0) {
            $this->ensurePresetProperties($presetsIblockId);
        }
        if ($presetsIblockId > 0 && $rolesIblockId > 0) {
            $this->ensureRoleProperties($rolesIblockId, $presetsIblockId, $ratesIblockId);
            $this->seed($presetsIblockId, $rolesIblockId, $ratesIblockId);
        } else {
            $this->line('Seed skipped until both team preset iblocks exist.');
        }

        $this->printConfigHints($presetsIblockId, $rolesIblockId);
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

    private function line(string $message): void
    {
        echo $message . PHP_EOL;
    }

    private function action(string $message, callable $callback): mixed
    {
        if (!$this->apply) {
            $this->line('[dry-run] ' . $message);
            return null;
        }

        $this->line('[apply] ' . $message);
        return $callback();
    }

    private function siteIds(): array
    {
        $siteIds = [];
        $result = CSite::GetList('sort', 'asc', ['ACTIVE' => 'Y']);
        while ($site = $result->Fetch()) {
            $siteId = trim((string)($site['ID'] ?? ''));
            if ($siteId !== '') {
                $siteIds[] = $siteId;
            }
        }

        return $siteIds !== [] ? $siteIds : ['s1'];
    }

    private function ensureIblockType(): void
    {
        $existing = CIBlockType::GetByID(TACTICUM_PRICE_TEAM_PRESETS_IBLOCK_TYPE)->Fetch();
        if (is_array($existing)) {
            $this->line('Iblock type exists: ' . TACTICUM_PRICE_TEAM_PRESETS_IBLOCK_TYPE);
            return;
        }

        $this->action('Create iblock type ' . TACTICUM_PRICE_TEAM_PRESETS_IBLOCK_TYPE, static function (): void {
            $type = new CIBlockType();
            $ok = $type->Add([
                'ID' => TACTICUM_PRICE_TEAM_PRESETS_IBLOCK_TYPE,
                'SECTIONS' => 'N',
                'IN_RSS' => 'N',
                'SORT' => 500,
                'LANG' => [
                    'ru' => ['NAME' => 'Tacticum content', 'SECTION_NAME' => 'Разделы', 'ELEMENT_NAME' => 'Элементы'],
                    'en' => ['NAME' => 'Tacticum content', 'SECTION_NAME' => 'Sections', 'ELEMENT_NAME' => 'Elements'],
                ],
            ]);
            if (!$ok) {
                throw new RuntimeException('Failed to create iblock type ' . TACTICUM_PRICE_TEAM_PRESETS_IBLOCK_TYPE);
            }
        });
    }

    private function ensureIblock(string $code, string $name, int $sort): int
    {
        $id = $this->findIblockId($code);
        if ($id > 0) {
            $this->line("Iblock exists: {$code} (#{$id})");
            return $id;
        }

        $createdId = $this->action("Create iblock {$code}", function () use ($code, $name, $sort): int {
            $iblock = new CIBlock();
            $id = (int)$iblock->Add([
                'ACTIVE' => 'Y',
                'NAME' => $name,
                'CODE' => $code,
                'XML_ID' => $code,
                'IBLOCK_TYPE_ID' => TACTICUM_PRICE_TEAM_PRESETS_IBLOCK_TYPE,
                'SITE_ID' => $this->siteIds,
                'SORT' => $sort,
                'GROUP_ID' => ['2' => 'R'],
                'INDEX_ELEMENT' => 'N',
                'INDEX_SECTION' => 'N',
                'VERSION' => 2,
            ]);
            if ($id <= 0) {
                throw new RuntimeException("Failed to create iblock {$code}: " . (string)$iblock->LAST_ERROR);
            }

            return $id;
        });

        return is_int($createdId) ? $createdId : 0;
    }

    private function findIblockId(string $code): int
    {
        $result = CIBlock::GetList(['ID' => 'ASC'], ['CODE' => $code, 'CHECK_PERMISSIONS' => 'N']);
        $iblock = $result->Fetch();

        return is_array($iblock) ? (int)$iblock['ID'] : 0;
    }

    private function ensurePresetProperties(int $iblockId): void
    {
        foreach ([
            $this->stringProperty('SCENARIO', 'Scenario', 100),
            $this->stringProperty('DEFAULT_WORKLOAD', 'Default workload', 110),
            $this->stringProperty('RECOMMENDED_DURATION', 'Recommended duration', 120),
            $this->stringProperty('VERSION', 'Version', 130),
            $this->stringProperty('ANALYTICS_CODE', 'Analytics code', 140),
        ] as $property) {
            $this->ensureProperty($iblockId, $property);
        }
    }

    private function ensureRoleProperties(int $rolesIblockId, int $presetsIblockId, int $ratesIblockId): void
    {
        foreach ([
            $this->elementProperty('PRESET', 'Team preset', 100, $presetsIblockId),
            $this->elementProperty('RATE_ELEMENT', 'Rate element', 110, $ratesIblockId),
            $this->stringProperty('PREFERRED_LEVELS', 'Preferred levels', 120, 'Y'),
            $this->numberProperty('QUANTITY', 'Quantity', 130),
            $this->stringProperty('REQUIRED', 'Required', 140),
            $this->stringProperty('ROLE_KEY', 'Role key', 150),
            $this->stringProperty('FALLBACK_KEYWORDS', 'Fallback keywords', 160, 'Y'),
        ] as $property) {
            $this->ensureProperty($rolesIblockId, $property);
        }
    }

    private function ensureProperty(int $iblockId, array $fields): int
    {
        $code = (string)$fields['CODE'];
        $existingId = $this->findPropertyId($iblockId, $code);
        if ($existingId > 0) {
            $this->line("Property exists: iblock #{$iblockId} {$code} (#{$existingId})");
            return $existingId;
        }

        $createdId = $this->action("Create property {$code} on iblock #{$iblockId}", static function () use ($iblockId, $fields): int {
            $property = new CIBlockProperty();
            $id = (int)$property->Add(array_merge($fields, ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y']));
            if ($id <= 0) {
                throw new RuntimeException("Failed to create property {$fields['CODE']} on iblock #{$iblockId}: " . (string)$property->LAST_ERROR);
            }

            return $id;
        });

        return is_int($createdId) ? $createdId : 0;
    }

    private function findPropertyId(int $iblockId, string $code): int
    {
        $result = CIBlockProperty::GetList(['ID' => 'ASC'], ['IBLOCK_ID' => $iblockId, 'CODE' => $code]);
        $property = $result->Fetch();

        return is_array($property) ? (int)$property['ID'] : 0;
    }

    private function stringProperty(string $code, string $name, int $sort, string $multiple = 'N'): array
    {
        return ['NAME' => $name, 'CODE' => $code, 'SORT' => $sort, 'PROPERTY_TYPE' => 'S', 'MULTIPLE' => $multiple];
    }

    private function numberProperty(string $code, string $name, int $sort): array
    {
        return ['NAME' => $name, 'CODE' => $code, 'SORT' => $sort, 'PROPERTY_TYPE' => 'N', 'MULTIPLE' => 'N'];
    }

    private function elementProperty(string $code, string $name, int $sort, int $linkIblockId): array
    {
        return [
            'NAME' => $name,
            'CODE' => $code,
            'SORT' => $sort,
            'PROPERTY_TYPE' => 'E',
            'LINK_IBLOCK_ID' => $linkIblockId,
            'MULTIPLE' => 'N',
        ];
    }

    private function seed(int $presetsIblockId, int $rolesIblockId, int $ratesIblockId): void
    {
        foreach ($this->presetDefinitions() as $sort => $preset) {
            $presetId = $this->seedPreset($presetsIblockId, $preset, ($sort + 1) * 100);
            if ($presetId <= 0) {
                $this->line('Role seed postponed for preset ' . $preset['code'] . ' until apply creates the preset row.');
                continue;
            }

            foreach ($preset['roles'] as $roleIndex => $role) {
                $rateElementId = $this->matchRateElement($ratesIblockId, $role['keywords'], $role['preferredLevels']);
                $this->seedRole($rolesIblockId, $presetId, $preset['code'], $role, ($roleIndex + 1) * 100, $rateElementId);
            }
        }
    }

    private function seedPreset(int $iblockId, array $preset, int $sort): int
    {
        $existingId = $this->findElementId($iblockId, $preset['code']);
        if ($existingId > 0) {
            $this->line("Preset exists: {$preset['code']} (#{$existingId})");
            return $existingId;
        }

        $createdId = $this->action('Create preset ' . $preset['code'], function () use ($iblockId, $preset, $sort): int {
            $element = new CIBlockElement();
            $id = (int)$element->Add([
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'CODE' => $preset['code'],
                'XML_ID' => 'team-preset-' . $preset['code'],
                'NAME' => $preset['label'],
                'PREVIEW_TEXT' => $preset['description'],
                'SORT' => $sort,
                'PROPERTY_VALUES' => [
                    'SCENARIO' => $preset['scenario'],
                    'DEFAULT_WORKLOAD' => $preset['defaultWorkload'],
                    'RECOMMENDED_DURATION' => $preset['recommendedDuration'],
                    'VERSION' => 'bitrix-v1',
                    'ANALYTICS_CODE' => $preset['code'],
                ],
            ]);
            if ($id <= 0) {
                throw new RuntimeException('Failed to create preset ' . $preset['code'] . ': ' . (string)$element->LAST_ERROR);
            }

            return $id;
        });

        return is_int($createdId) ? $createdId : 0;
    }

    private function seedRole(int $iblockId, int $presetId, string $presetCode, array $role, int $sort, int $rateElementId): int
    {
        $code = $presetCode . '-' . $role['roleKey'];
        $existingId = $this->findElementId($iblockId, $code);
        if ($existingId > 0) {
            $this->line("Preset role exists: {$code} (#{$existingId})");
            return $existingId;
        }

        if ($rateElementId <= 0) {
            $this->line('Rate element not matched for role ' . $code . '; fallback keywords will be stored for review.');
        }

        $createdId = $this->action('Create preset role ' . $code, function () use ($iblockId, $presetId, $role, $sort, $rateElementId, $code): int {
            $element = new CIBlockElement();
            $properties = [
                'PRESET' => $presetId,
                'PREFERRED_LEVELS' => $role['preferredLevels'],
                'QUANTITY' => (string)$role['quantity'],
                'REQUIRED' => 'Y',
                'ROLE_KEY' => $role['roleKey'],
                'FALLBACK_KEYWORDS' => $role['keywords'],
            ];
            if ($rateElementId > 0) {
                $properties['RATE_ELEMENT'] = $rateElementId;
            }

            $id = (int)$element->Add([
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'CODE' => $code,
                'XML_ID' => 'team-preset-role-' . $code,
                'NAME' => $role['label'],
                'SORT' => $sort,
                'PROPERTY_VALUES' => $properties,
            ]);
            if ($id <= 0) {
                throw new RuntimeException('Failed to create preset role ' . $code . ': ' . (string)$element->LAST_ERROR);
            }

            return $id;
        });

        return is_int($createdId) ? $createdId : 0;
    }

    private function findElementId(int $iblockId, string $code): int
    {
        if ($iblockId <= 0) {
            return 0;
        }

        $result = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, '=CODE' => $code, 'CHECK_PERMISSIONS' => 'N'],
            false,
            ['nTopCount' => 1],
            ['ID']
        );
        $element = $result->Fetch();

        return is_array($element) ? (int)$element['ID'] : 0;
    }

    private function matchRateElement(int $ratesIblockId, array $keywords, array $preferredLevels): int
    {
        if ($ratesIblockId <= 0) {
            return 0;
        }

        $preferredRanks = [];
        foreach (array_values($preferredLevels) as $index => $level) {
            $preferredRanks[$this->normalize((string)$level)] = $index;
        }

        $matches = [];
        $result = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            ['IBLOCK_ID' => $ratesIblockId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'SORT']
        );
        while ($element = $result->GetNextElement()) {
            $fields = $element->GetFields();
            $properties = $element->GetProperties();
            $haystack = $this->normalize((string)($fields['NAME'] ?? '') . ' ' . (string)($fields['CODE'] ?? ''));
            $matched = false;
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($haystack, $this->normalize($keyword))) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                continue;
            }

            $level = $this->normalize((string)($properties['LEVEL']['VALUE'] ?? ''));
            $matches[] = [
                'id' => (int)($fields['ID'] ?? 0),
                'rank' => $preferredRanks[$level] ?? 999,
                'sort' => (int)($fields['SORT'] ?? 500),
            ];
        }

        usort($matches, static fn(array $left, array $right): int => ($left['rank'] <=> $right['rank']) ?: ($left['sort'] <=> $right['sort']) ?: ($left['id'] <=> $right['id']));

        return (int)($matches[0]['id'] ?? 0);
    }

    private function normalize(string $value): string
    {
        return str_replace('ё', 'е', mb_strtolower(trim($value)));
    }

    private function presetDefinitions(): array
    {
        return [
            [
                'code' => 'mvp',
                'label' => 'MVP',
                'description' => 'Аналитика, дизайн, разработка, QA',
                'scenario' => 'mvp',
                'defaultWorkload' => 'part-time',
                'recommendedDuration' => '2-3-months',
                'roles' => [
                    $this->role('analyst', 'Аналитик', ['бизнес-аналит', 'аналитик']),
                    $this->role('ux-ui', 'UX/UI', ['ux', 'ui', 'дизайн', 'designer']),
                    $this->role('frontend', 'Frontend', ['frontend', 'front-end', 'фронтенд']),
                    $this->role('backend', 'Backend', ['backend', 'back-end', 'python', 'php', 'java', 'node', 'разработчик', 'developer']),
                    $this->role('qa', 'QA', ['qa', 'quality', 'тест', 'тестирование']),
                ],
            ],
            [
                'code' => 'discovery',
                'label' => 'Discovery',
                'description' => 'Аналитик, архитектор, UX/UI',
                'scenario' => 'discovery',
                'defaultWorkload' => 'part-time',
                'recommendedDuration' => '1-month',
                'roles' => [
                    $this->role('analyst', 'Аналитик', ['бизнес-аналит', 'аналитик']),
                    $this->role('architect', 'Архитектор', ['архитектор', 'architect', 'tech lead', 'lead']),
                    $this->role('ux-ui', 'UX/UI', ['ux', 'ui', 'дизайн', 'designer']),
                ],
            ],
            [
                'code' => 'support',
                'label' => 'Support',
                'description' => 'Backend, DevOps, QA',
                'scenario' => 'support',
                'defaultWorkload' => 'part-time',
                'recommendedDuration' => '3-6-months',
                'roles' => [
                    $this->role('backend', 'Backend', ['backend', 'back-end', 'python', 'php', 'java', 'node', 'разработчик', 'developer']),
                    $this->role('devops', 'DevOps', ['devops', 'инфраструктура', 'sre']),
                    $this->role('qa', 'QA', ['qa', 'quality', 'тест', 'тестирование']),
                ],
            ],
            [
                'code' => 'qa-burst',
                'label' => 'QA burst',
                'description' => 'Усиление тестирования перед релизом',
                'scenario' => 'qa',
                'defaultWorkload' => 'part-time',
                'recommendedDuration' => '2-weeks',
                'roles' => [
                    $this->role('qa', 'QA', ['qa', 'quality', 'тест', 'тестирование'], 2),
                    $this->role('qa-automation', 'QA automation', ['автоматиз', 'automation', 'автотест']),
                ],
            ],
        ];
    }

    private function role(string $key, string $label, array $keywords, int $quantity = 1): array
    {
        return [
            'roleKey' => $key,
            'label' => $label,
            'keywords' => $keywords,
            'preferredLevels' => ['Middle', 'Senior', 'Junior', 'Lead'],
            'quantity' => $quantity,
        ];
    }

    private function printConfigHints(int $presetsIblockId, int $rolesIblockId): void
    {
        $this->line('Config hints for local/php_interface/include/tacticum_config.php:');
        $this->line("'team_presets' => {$presetsIblockId},");
        $this->line("'team_preset_roles' => {$rolesIblockId},");
        $this->line("'price' => ['team_presets_source' => 'bitrix', 'team_presets_cache_ttl' => 300, 'allow_team_presets_fallback' => false],");
    }
}

function tacticum_price_team_presets_migration_usage(): string
{
    return <<<TEXT
Usage:
  php tools/price-team-presets-migration.php [--apply] [--document-root=/path/to/site]

Creates tacticum_team_presets and tacticum_team_preset_roles iblocks and seeds
current quick team presets. Dry-run by default.

TEXT;
}

$options = ['apply' => false, 'document_root' => getenv('DOCUMENT_ROOT') ?: dirname(__DIR__), 'help' => false];
foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        $options['help'] = true;
        continue;
    }
    if ($argument === '--apply') {
        $options['apply'] = true;
        continue;
    }
    if (str_starts_with($argument, '--document-root=')) {
        $options['document_root'] = substr($argument, strlen('--document-root='));
        continue;
    }
    fwrite(STDERR, 'Unknown argument: ' . $argument . PHP_EOL);
    fwrite(STDERR, tacticum_price_team_presets_migration_usage());
    exit(2);
}

if ($options['help']) {
    echo tacticum_price_team_presets_migration_usage();
    exit(0);
}

$documentRoot = realpath((string)$options['document_root']);
if ($documentRoot === false) {
    fwrite(STDERR, 'Document root does not exist: ' . (string)$options['document_root'] . PHP_EOL);
    exit(2);
}

try {
    (new TacticumPriceTeamPresetsMigration((bool)$options['apply'], $documentRoot))->run();
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
