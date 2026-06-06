<?php

use Tacticum\Price\TeamPresetService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_price_team_presets_cache_dir')) {
    function tacticum_price_team_presets_cache_dir(): string
    {
        return TeamPresetService::cacheDir();
    }
}

if (!function_exists('tacticum_price_team_presets_related_iblock_ids')) {
    function tacticum_price_team_presets_related_iblock_ids(): array
    {
        return TeamPresetService::relatedIblockIds();
    }
}

if (!function_exists('tacticum_price_team_presets_clear_render_cache')) {
    function tacticum_price_team_presets_clear_render_cache(): void
    {
        if (class_exists('CBitrixComponent') && method_exists('CBitrixComponent', 'clearComponentCache')) {
            \CBitrixComponent::clearComponentCache('bitrix:news.list');
        }

        if (class_exists('Bitrix\Main\Data\StaticHtmlCache')) {
            $staticHtmlCache = \Bitrix\Main\Data\StaticHtmlCache::getInstance();
            if (is_object($staticHtmlCache) && method_exists($staticHtmlCache, 'deleteAll')) {
                $staticHtmlCache->deleteAll();
            }
        }

        if (class_exists('CHTMLPagesCache') && method_exists('CHTMLPagesCache', 'CleanAll')) {
            \CHTMLPagesCache::CleanAll();
        }
    }
}

if (!function_exists('tacticum_price_team_presets_clear_cache')) {
    function tacticum_price_team_presets_clear_cache(int $iblockId = 0, bool $clearRenderCache = true): void
    {
        TeamPresetService::clearCache($iblockId);

        if ($clearRenderCache) {
            tacticum_price_team_presets_clear_render_cache();
        }
    }
}

if (!function_exists('tacticum_price_team_presets_clear_cache_for_iblock')) {
    function tacticum_price_team_presets_clear_cache_for_iblock(mixed $iblockId): void
    {
        $iblockId = (int)$iblockId;
        if ($iblockId > 0 && in_array($iblockId, tacticum_price_team_presets_related_iblock_ids(), true)) {
            tacticum_price_team_presets_clear_cache($iblockId);
        }
    }
}

if (!function_exists('tacticum_price_team_presets_clear_cache_on_iblock_change')) {
    function tacticum_price_team_presets_clear_cache_on_iblock_change(array &$fields): void
    {
        tacticum_price_team_presets_clear_cache_for_iblock($fields['IBLOCK_ID'] ?? 0);
    }
}

if (!function_exists('tacticum_price_team_presets_clear_cache_on_element_delete')) {
    function tacticum_price_team_presets_clear_cache_on_element_delete(array $fields): void
    {
        tacticum_price_team_presets_clear_cache_for_iblock($fields['IBLOCK_ID'] ?? 0);
    }
}

if (!function_exists('tacticum_price_team_presets_clear_cache_on_property_change')) {
    function tacticum_price_team_presets_clear_cache_on_property_change(mixed $elementId, mixed $iblockId = 0): void
    {
        tacticum_price_team_presets_clear_cache_for_iblock($iblockId);
    }
}

if (!function_exists('tacticum_register_price_team_presets_cache_handlers')) {
    function tacticum_register_price_team_presets_cache_handlers(): void
    {
        static $registered = false;
        if ($registered || !class_exists('Bitrix\Main\EventManager')) {
            return;
        }
        $registered = true;

        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', 'tacticum_price_team_presets_clear_cache_on_iblock_change');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', 'tacticum_price_team_presets_clear_cache_on_iblock_change');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', 'tacticum_price_team_presets_clear_cache_on_element_delete');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValues', 'tacticum_price_team_presets_clear_cache_on_property_change');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValuesEx', 'tacticum_price_team_presets_clear_cache_on_property_change');
    }
}
