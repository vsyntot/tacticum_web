<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!class_exists('TacticumOfferCatalogCache')) {
    final class TacticumOfferCatalogCache
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
            \Bitrix\Main\Data\Cache::createInstance()->cleanDir(self::CACHE_DIR);

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
}

if (!function_exists('tacticum_offer_catalog_clear_cache')) {
    function tacticum_offer_catalog_clear_cache(int $iblockId = 0): void
    {
        if (class_exists('TacticumOfferCatalogCache')) {
            TacticumOfferCatalogCache::clear($iblockId);
        }
    }
}

if (!function_exists('tacticum_offer_catalog_clear_cache_on_iblock_change')) {
    function tacticum_offer_catalog_clear_cache_on_iblock_change(array &$fields): void
    {
        $iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
        $offerIblockId = function_exists('tacticum_iblock_id') ? tacticum_iblock_id('offer') : 0;
        if ($offerIblockId > 0 && $iblockId === $offerIblockId) {
            tacticum_offer_catalog_clear_cache($offerIblockId);
        }
    }
}

if (!function_exists('tacticum_offer_catalog_clear_cache_on_element_delete')) {
    function tacticum_offer_catalog_clear_cache_on_element_delete(array $fields): void
    {
        $iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
        $offerIblockId = function_exists('tacticum_iblock_id') ? tacticum_iblock_id('offer') : 0;
        if ($offerIblockId > 0 && $iblockId === $offerIblockId) {
            tacticum_offer_catalog_clear_cache($offerIblockId);
        }
    }
}

if (!function_exists('tacticum_offer_catalog_clear_cache_on_property_change')) {
    function tacticum_offer_catalog_clear_cache_on_property_change(mixed $elementId, mixed $iblockId = 0): void
    {
        $iblockId = (int)$iblockId;
        $offerIblockId = function_exists('tacticum_iblock_id') ? tacticum_iblock_id('offer') : 0;
        if ($offerIblockId > 0 && $iblockId === $offerIblockId) {
            tacticum_offer_catalog_clear_cache($offerIblockId);
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
