<?php

declare(strict_types=1);

namespace Tacticum\Price;

use Bitrix\Main\Data\Cache;
use Tacticum\Rest\Config;

final class TeamPresetCache
{
    public const CACHE_DIR = '/tacticum/price_team_presets';

    private const RELATED_IBLOCK_KEYS = [
        'team_presets',
        'team_preset_roles',
        'rates',
    ];

    public static function cacheId(int $presetsIblockId = 0, int $rolesIblockId = 0, int $ratesIblockId = 0): string
    {
        $presetsIblockId = $presetsIblockId > 0 ? $presetsIblockId : Config::iblockId('team_presets');
        $rolesIblockId = $rolesIblockId > 0 ? $rolesIblockId : Config::iblockId('team_preset_roles');
        $ratesIblockId = $ratesIblockId > 0 ? $ratesIblockId : Config::iblockId('rates');

        return 'team_presets_' . md5(implode('|', [$presetsIblockId, $rolesIblockId, $ratesIblockId]));
    }

    public static function relatedIblockIds(): array
    {
        $ids = [];
        foreach (self::RELATED_IBLOCK_KEYS as $key) {
            $id = Config::iblockId($key);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    public static function startTagCache(): void
    {
        global $CACHE_MANAGER;

        if (
            defined('BX_COMP_MANAGED_CACHE')
            && is_object($CACHE_MANAGER)
            && method_exists($CACHE_MANAGER, 'StartTagCache')
            && method_exists($CACHE_MANAGER, 'RegisterTag')
        ) {
            $CACHE_MANAGER->StartTagCache(self::CACHE_DIR);
            foreach (self::relatedIblockIds() as $iblockId) {
                $CACHE_MANAGER->RegisterTag('iblock_id_' . $iblockId);
            }
        }
    }

    public static function endTagCache(): void
    {
        global $CACHE_MANAGER;

        if (
            defined('BX_COMP_MANAGED_CACHE')
            && is_object($CACHE_MANAGER)
            && method_exists($CACHE_MANAGER, 'EndTagCache')
        ) {
            $CACHE_MANAGER->EndTagCache();
        }
    }

    public static function clear(int $iblockId = 0): void
    {
        Cache::createInstance()->cleanDir(self::CACHE_DIR);

        global $CACHE_MANAGER;
        if (
            defined('BX_COMP_MANAGED_CACHE')
            && is_object($CACHE_MANAGER)
            && method_exists($CACHE_MANAGER, 'ClearByTag')
        ) {
            $tagIblockIds = $iblockId > 0 ? [$iblockId] : self::relatedIblockIds();
            foreach ($tagIblockIds as $tagIblockId) {
                $CACHE_MANAGER->ClearByTag('iblock_id_' . $tagIblockId);
            }
        }
    }
}
