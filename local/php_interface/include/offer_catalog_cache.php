<?php

use Tacticum\Offer\CatalogCache;
use Tacticum\Offer\OfferTaxonomyService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!class_exists(CatalogCache::class)) {
    $catalogCachePath = $_SERVER['DOCUMENT_ROOT'] . '/local/lib/Tacticum/Offer/CatalogCache.php';
    if (is_file($catalogCachePath)) {
        require_once $catalogCachePath;
    }
}

if (!class_exists('TacticumOfferCatalogCache')) {
    final class TacticumOfferCatalogCache
    {
        public const CACHE_DIR = CatalogCache::CACHE_DIR;
        public const CACHE_TTL = CatalogCache::CACHE_TTL;

        public static function cacheId(int $iblockId): string
        {
            return CatalogCache::cacheId($iblockId);
        }

        public static function tag(int $iblockId): string
        {
            return CatalogCache::tag($iblockId);
        }

        public static function startTagCache(int $iblockId): void
        {
            CatalogCache::startTagCache($iblockId);
        }

        public static function endTagCache(): void
        {
            CatalogCache::endTagCache();
        }

        public static function clear(int $iblockId = 0): void
        {
            CatalogCache::clear($iblockId);
        }

        public static function relatedIblockIds(): array
        {
            return CatalogCache::relatedIblockIds();
        }
    }
}

if (!function_exists('tacticum_offer_catalog_clear_cache')) {
    function tacticum_offer_catalog_clear_cache(int $iblockId = 0): void
    {
        if (class_exists('TacticumOfferCatalogCache')) {
            TacticumOfferCatalogCache::clear($iblockId);
        }
        if (class_exists(OfferTaxonomyService::class)) {
            OfferTaxonomyService::clearCache($iblockId);
        }
    }
}

if (!function_exists('tacticum_offer_catalog_related_cache_iblock_ids')) {
    function tacticum_offer_catalog_related_cache_iblock_ids(): array
    {
        return class_exists('TacticumOfferCatalogCache') ? TacticumOfferCatalogCache::relatedIblockIds() : [];
    }
}

if (!function_exists('tacticum_offer_catalog_clear_cache_on_iblock_change')) {
    function tacticum_offer_catalog_clear_cache_on_iblock_change(array &$fields): void
    {
        $iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
        if ($iblockId > 0 && in_array($iblockId, tacticum_offer_catalog_related_cache_iblock_ids(), true)) {
            tacticum_offer_catalog_clear_cache($iblockId);
        }
    }
}

if (!function_exists('tacticum_offer_catalog_clear_cache_on_element_delete')) {
    function tacticum_offer_catalog_clear_cache_on_element_delete(array $fields): void
    {
        $iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
        if ($iblockId > 0 && in_array($iblockId, tacticum_offer_catalog_related_cache_iblock_ids(), true)) {
            tacticum_offer_catalog_clear_cache($iblockId);
        }
    }
}

if (!function_exists('tacticum_offer_catalog_clear_cache_on_property_change')) {
    function tacticum_offer_catalog_clear_cache_on_property_change(mixed $elementId, mixed $iblockId = 0): void
    {
        $iblockId = (int)$iblockId;
        if ($iblockId > 0 && in_array($iblockId, tacticum_offer_catalog_related_cache_iblock_ids(), true)) {
            tacticum_offer_catalog_clear_cache($iblockId);
        }
    }
}

if (!function_exists('tacticum_register_offer_catalog_cache_handlers')) {
    function tacticum_register_offer_catalog_cache_handlers(): void
    {
        static $registered = false;
        if ($registered || !class_exists('Bitrix\Main\EventManager')) {
            return;
        }
        $registered = true;

        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', 'tacticum_offer_catalog_clear_cache_on_iblock_change');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', 'tacticum_offer_catalog_clear_cache_on_iblock_change');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', 'tacticum_offer_catalog_clear_cache_on_element_delete');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValues', 'tacticum_offer_catalog_clear_cache_on_property_change');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValuesEx', 'tacticum_offer_catalog_clear_cache_on_property_change');
    }
}
