<?php

declare(strict_types=1);

namespace Tacticum\Offer;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Tacticum\Rest\Config;

final class OfferTaxonomyRepository
{
    public static function activeTerms(int $ttl = 300): array
    {
        $iblockId = Config::iblockId('offer_taxonomy_terms');
        if ($iblockId <= 0 || !Loader::includeModule('iblock')) {
            return [];
        }

        $cache = Cache::createInstance();
        $cacheId = OfferTaxonomyCache::cacheId($iblockId);
        $cacheDir = OfferTaxonomyCache::CACHE_DIR;
        if ($ttl > 0 && $cache->initCache($ttl, $cacheId, $cacheDir)) {
            $payload = $cache->getVars();
            return is_array($payload) ? $payload : [];
        }

        $cacheStarted = $ttl > 0 && $cache->startDataCache($ttl, $cacheId, $cacheDir);
        if ($cacheStarted) {
            OfferTaxonomyCache::startTagCache();
        }

        $terms = self::fetchTerms($iblockId);

        if ($cacheStarted) {
            OfferTaxonomyCache::endTagCache();
            $cache->endDataCache($terms);
        }

        return $terms;
    }

    private static function fetchTerms(int $iblockId): array
    {
        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'SORT']
        );

        $terms = [];
        while ($element = $result->GetNextElement()) {
            $fields = $element->GetFields();
            $properties = $element->GetProperties();
            $code = trim((string)($fields['CODE'] ?? ''));
            $dimension = self::propertyScalar($properties, 'DIMENSION');
            if ($code === '' || $dimension === '') {
                continue;
            }

            $publicLabel = self::propertyScalar($properties, 'PUBLIC_LABEL', trim((string)($fields['NAME'] ?? $code)));
            $terms[] = [
                'id' => (int)($fields['ID'] ?? 0),
                'dimension' => $dimension,
                'code' => $code,
                'publicLabel' => $publicLabel,
                'shortLabel' => self::propertyScalar($properties, 'SHORT_LABEL', $publicLabel),
                'aliases' => self::propertyValues($properties, 'ALIASES'),
                'sort' => (int)($fields['SORT'] ?? 500),
                'active' => true,
                'featured' => self::truthy(self::propertyScalar($properties, 'FEATURED')),
                'productFamily' => self::propertyScalar($properties, 'PRODUCT_FAMILY'),
                'source' => 'bitrix',
            ];
        }

        return $terms;
    }

    private static function propertyScalar(array $properties, string $code, string $default = ''): string
    {
        return self::propertyValues($properties, $code)[0] ?? $default;
    }

    private static function propertyValues(array $properties, string $code): array
    {
        $value = $properties[$code]['VALUE'] ?? [];
        if (!is_array($value)) {
            $value = [$value];
        }

        return array_values(array_filter(array_map(
            static fn(mixed $item): string => CatalogMapper::trim((string)$item),
            $value
        ), static fn(string $item): bool => $item !== ''));
    }

    private static function truthy(string $value): bool
    {
        return in_array(mb_strtolower(trim($value)), ['1', 'y', 'yes', 'да', 'true'], true);
    }
}
