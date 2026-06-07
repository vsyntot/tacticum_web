<?php

declare(strict_types=1);

namespace Tacticum\Offer;

use Bitrix\Main\Data\Cache;
use Tacticum\Rest\Config;

final class OfferTaxonomyCache
{
    public const CACHE_DIR = '/tacticum/offer_taxonomy';

    private const RELATED_IBLOCK_KEYS = ['offer', 'offer_taxonomy_terms'];

    public static function cacheId(int $termsIblockId = 0): string
    {
        $termsIblockId = $termsIblockId > 0 ? $termsIblockId : Config::iblockId('offer_taxonomy_terms');

        return 'offer_taxonomy_terms_' . md5((string)$termsIblockId);
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
