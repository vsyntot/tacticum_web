<?php

namespace Tacticum\Offer;

use Bitrix\Main\Data\Cache;
use Tacticum\Rest\Config;

final class CatalogCache
{
    public const CACHE_DIR = '/tacticum/offer_catalog';
    public const CACHE_TTL = 900;

    public static function cacheId(int $iblockId): string
    {
        $offerConfig = Config::section('offer');
        return 'offer_catalog_items_v3_' . md5(implode('|', [
            $iblockId,
            Config::iblockId('offer_taxonomy_terms'),
            (string)($offerConfig['taxonomy_source'] ?? 'fallback'),
        ]));
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
            foreach (self::relatedIblockIds($iblockId) as $relatedIblockId) {
                $CACHE_MANAGER->RegisterTag(self::tag($relatedIblockId));
            }
        }
    }

    public static function relatedIblockIds(int $offerIblockId = 0): array
    {
        $ids = [];
        foreach ([$offerIblockId, Config::iblockId('offer'), Config::iblockId('offer_taxonomy_terms')] as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
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
                $CACHE_MANAGER->ClearByTag(self::tag($tagIblockId));
            }
        }
    }
}
