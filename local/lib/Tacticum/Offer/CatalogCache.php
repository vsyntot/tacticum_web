<?php

namespace Tacticum\Offer;

use Bitrix\Main\Data\Cache;

final class CatalogCache
{
    public const CACHE_DIR = '/tacticum/offer_catalog';
    public const CACHE_TTL = 900;

    public static function cacheId(int $iblockId): string
    {
        return 'offer_catalog_items_v2_' . $iblockId;
    }

    public static function tag(int $iblockId): string
    {
        return 'iblock_id_' . $iblockId;
    }

    public static function startTagCache(int $iblockId): void
    {
        global $CACHE_MANAGER;

        if (
            defined('BX_COMP_MANAGED_CACHE')
            && is_object($CACHE_MANAGER)
            && method_exists($CACHE_MANAGER, 'StartTagCache')
            && method_exists($CACHE_MANAGER, 'RegisterTag')
        ) {
            $CACHE_MANAGER->StartTagCache(self::CACHE_DIR);
            $CACHE_MANAGER->RegisterTag(self::tag($iblockId));
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
            $iblockId > 0
            && defined('BX_COMP_MANAGED_CACHE')
            && is_object($CACHE_MANAGER)
            && method_exists($CACHE_MANAGER, 'ClearByTag')
        ) {
            $CACHE_MANAGER->ClearByTag(self::tag($iblockId));
        }
    }
}
