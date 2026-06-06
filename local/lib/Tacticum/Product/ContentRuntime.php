<?php

namespace Tacticum\Product;

use Bitrix\Main\Data\Cache;

final class ContentRuntime
{
    private const CODES = [
        'platform' => 'platform.php',
        'agents' => 'agents.php',
        'dev' => 'dev.php',
        'forum' => 'forum.php',
    ];

    public static function codes(): array
    {
        return self::CODES;
    }

    public static function source(): string
    {
        $source = self::configuredSource();
        if ($source !== 'bitrix' && !self::fallbackAllowed()) {
            return 'bitrix';
        }

        return $source;
    }

    public static function configuredSource(): string
    {
        $source = strtolower(trim((string)(self::productsConfig()['source'] ?? 'bitrix')));

        return in_array($source, ['auto', 'bitrix', 'fallback'], true) ? $source : 'bitrix';
    }

    public static function fallbackAllowed(): bool
    {
        $value = self::productsConfig()['allow_fallback'] ?? false;
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int)$value === 1;
        }
        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'y'], true);
        }

        return false;
    }

    public static function cacheTtl(): int
    {
        $ttl = self::productsConfig()['cache_ttl'] ?? null;
        if ($ttl === null && function_exists('tacticum_api_cache_ttl')) {
            $ttl = tacticum_api_cache_ttl('products');
        }

        $ttl = (int)($ttl ?? 300);

        return $ttl < 0 ? 0 : $ttl;
    }

    public static function cacheDir(): string
    {
        return '/tacticum/product_content';
    }

    public static function schemaVersion(): string
    {
        return 'v1';
    }

    public static function cacheKey(string $productCode): string
    {
        $iblockIds = [];
        foreach (['products', 'product_blocks', 'product_use_cases', 'faq'] as $key) {
            $iblockIds[$key] = function_exists('tacticum_rest_get_iblock_id')
                ? tacticum_rest_get_iblock_id($key)
                : 0;
        }

        $cacheIdentity = [
            'schema_version' => self::schemaVersion(),
            'source_mode' => self::source(),
            'configured_source' => self::configuredSource(),
            'fallback_allowed' => self::fallbackAllowed(),
            'iblock_ids' => $iblockIds,
        ];

        return 'product_content_' . $productCode . '_' . md5(serialize($cacheIdentity));
    }

    public static function relatedIblockIds(): array
    {
        $ids = [];
        foreach (['products', 'product_blocks', 'product_use_cases', 'faq'] as $key) {
            $id = function_exists('tacticum_rest_get_iblock_id')
                ? tacticum_rest_get_iblock_id($key)
                : 0;
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
            $CACHE_MANAGER->StartTagCache(self::cacheDir());
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

    public static function clearCache(int $iblockId = 0): void
    {
        Cache::createInstance()->cleanDir(self::cacheDir());

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

    private static function productsConfig(): array
    {
        return function_exists('tacticum_rest_get_config_section')
            ? tacticum_rest_get_config_section('products')
            : [];
    }
}
