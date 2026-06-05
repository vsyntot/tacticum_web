<?php

namespace Tacticum\Offer;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;

final class CatalogService
{
    public static function findElement(int $offerId, string $offerCode): ?array
    {
        return CatalogRepository::findElement($offerId, $offerCode);
    }

    public static function clearCache(int $iblockId = 0): void
    {
        CatalogCache::clear($iblockId);
    }

    public static function items(int $iblockId): array
    {
        if ($iblockId <= 0 || !Loader::includeModule('iblock')) {
            return [];
        }

        $cache = Cache::createInstance();
        $cacheId = CatalogCache::cacheId($iblockId);
        $cacheDir = CatalogCache::CACHE_DIR;
        if ($cache->initCache(CatalogCache::CACHE_TTL, $cacheId, $cacheDir)) {
            $cached = $cache->getVars();
            if (is_array($cached) && isset($cached['items']) && is_array($cached['items'])) {
                return $cached['items'];
            }
        }

        $items = [];
        if ($cache->startDataCache(CatalogCache::CACHE_TTL, $cacheId, $cacheDir)) {
            CatalogCache::startTagCache($iblockId);
            $items = CatalogRepository::items($iblockId);
            CatalogCache::endTagCache();
            $cache->endDataCache(['items' => $items]);
        }

        return $items;
    }

    public static function prepare(int $iblockId, array $filters, int $perPage = 24): array
    {
        $items = self::items($iblockId);
        $filtered = CatalogFilters::filterItems($items, $filters);
        $total = count($filtered);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min(max(1, (int)($filters['page'] ?? 1)), $totalPages);
        $pageItems = array_slice($filtered, ($page - 1) * $perPage, $perPage);
        $budgets = array_filter(array_map(static fn(array $item): int => (int)($item['budget_amount'] ?? 0), $items));
        $options = CatalogFilters::options($items);

        return [
            'filters' => array_merge($filters, ['page' => $page]),
            'items' => $pageItems,
            'total' => $total,
            'all_total' => count($items),
            'page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'options' => $options,
            'has_filters' => CatalogFilters::hasFilters($filters),
            'pagination' => CatalogFilters::paginationRange($page, $totalPages),
            'stats' => [
                'sectors' => count($options['sectors']),
                'scenarios' => count($options['scenarios']),
                'budget_min' => $budgets !== [] ? min($budgets) : 0,
                'budget_max' => $budgets !== [] ? max($budgets) : 0,
            ],
        ];
    }
}
