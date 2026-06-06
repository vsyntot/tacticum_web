<?php

declare(strict_types=1);

namespace Tacticum\Price;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Tacticum\Rest\Config;

final class TeamPresetRepository
{
    public static function activePresets(int $ttl = 300): array
    {
        $presetsIblockId = Config::iblockId('team_presets');
        $rolesIblockId = Config::iblockId('team_preset_roles');
        if ($presetsIblockId <= 0 || $rolesIblockId <= 0 || !Loader::includeModule('iblock')) {
            return [];
        }

        $cache = Cache::createInstance();
        $cacheId = TeamPresetCache::cacheId($presetsIblockId, $rolesIblockId, Config::iblockId('rates'));
        $cacheDir = TeamPresetCache::CACHE_DIR;
        if ($ttl > 0 && $cache->initCache($ttl, $cacheId, $cacheDir)) {
            $payload = $cache->getVars();
            return is_array($payload) ? $payload : [];
        }

        $cacheStarted = $ttl > 0 && $cache->startDataCache($ttl, $cacheId, $cacheDir);
        if ($cacheStarted) {
            TeamPresetCache::startTagCache();
        }

        $presets = self::fetchPresets($presetsIblockId);
        if ($presets !== []) {
            $rolesByPreset = self::fetchRoles($rolesIblockId, array_keys($presets));
            foreach ($presets as $presetId => &$preset) {
                $preset['roles'] = $rolesByPreset[$presetId] ?? [];
            }
            unset($preset);
        }

        $result = array_values(array_filter($presets, static fn(array $preset): bool => $preset['roles'] !== []));
        usort($result, static fn(array $left, array $right): int => ($left['sort'] <=> $right['sort']) ?: strnatcasecmp($left['code'], $right['code']));

        if ($cacheStarted) {
            TeamPresetCache::endTagCache();
            $cache->endDataCache($result);
        }

        return $result;
    }

    private static function fetchPresets(int $iblockId): array
    {
        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'SORT', 'PREVIEW_TEXT', 'TIMESTAMP_X']
        );

        $presets = [];
        while ($element = $result->GetNextElement()) {
            $fields = $element->GetFields();
            $properties = $element->GetProperties();
            $id = (int)($fields['ID'] ?? 0);
            $code = trim((string)($fields['CODE'] ?? ''));
            if ($id <= 0 || $code === '') {
                continue;
            }

            $presets[$id] = [
                'id' => $id,
                'code' => $code,
                'label' => trim((string)($fields['NAME'] ?? $code)),
                'description' => trim((string)($fields['PREVIEW_TEXT'] ?? '')),
                'sort' => (int)($fields['SORT'] ?? 500),
                'scenario' => self::propertyScalar($properties, 'SCENARIO'),
                'defaultWorkload' => self::propertyScalar($properties, 'DEFAULT_WORKLOAD'),
                'recommendedDuration' => self::propertyScalar($properties, 'RECOMMENDED_DURATION'),
                'version' => self::propertyScalar($properties, 'VERSION', trim((string)($fields['TIMESTAMP_X'] ?? ''))),
                'analyticsCode' => self::propertyScalar($properties, 'ANALYTICS_CODE', $code),
                'source' => 'bitrix',
                'roles' => [],
            ];
        }

        return $presets;
    }

    private static function fetchRoles(int $iblockId, array $presetIds): array
    {
        $presetIds = array_values(array_filter(array_map('intval', $presetIds)));
        if ($presetIds === []) {
            return [];
        }

        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                '=PROPERTY_PRESET' => $presetIds,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'SORT']
        );

        $rolesByPreset = [];
        while ($element = $result->GetNextElement()) {
            $fields = $element->GetFields();
            $properties = $element->GetProperties();
            $presetId = (int)self::propertyScalar($properties, 'PRESET');
            if ($presetId <= 0) {
                continue;
            }

            $rateElementId = (int)self::propertyScalar($properties, 'RATE_ELEMENT');
            $quantity = (int)self::propertyScalar($properties, 'QUANTITY', '1');
            $quantity = max(1, min($quantity, 99));
            $preferredLevels = self::propertyValues($properties, 'PREFERRED_LEVELS');
            $keywords = self::propertyValues($properties, 'FALLBACK_KEYWORDS');

            $rolesByPreset[$presetId][] = [
                'id' => (int)($fields['ID'] ?? 0),
                'roleKey' => self::propertyScalar($properties, 'ROLE_KEY', trim((string)($fields['CODE'] ?? ''))),
                'rateElementId' => $rateElementId,
                'rateIds' => $rateElementId > 0 ? [$rateElementId] : [],
                'preferredLevels' => $preferredLevels !== [] ? $preferredLevels : ['Middle', 'Senior', 'Junior', 'Lead'],
                'quantity' => $quantity,
                'required' => self::truthy(self::propertyScalar($properties, 'REQUIRED', 'Y')),
                'keywords' => $keywords,
                'sort' => (int)($fields['SORT'] ?? 500),
            ];
        }

        return $rolesByPreset;
    }

    private static function propertyScalar(array $properties, string $code, string $default = ''): string
    {
        $values = self::propertyValues($properties, $code);
        return $values[0] ?? $default;
    }

    private static function propertyValues(array $properties, string $code): array
    {
        $value = $properties[$code]['VALUE'] ?? [];
        if (!is_array($value)) {
            $value = [$value];
        }

        return array_values(array_filter(array_map(
            static fn(mixed $item): string => trim((string)$item),
            $value
        ), static fn(string $item): bool => $item !== ''));
    }

    private static function truthy(string $value): bool
    {
        return in_array(mb_strtolower(trim($value)), ['1', 'y', 'yes', 'да', 'true'], true);
    }
}
