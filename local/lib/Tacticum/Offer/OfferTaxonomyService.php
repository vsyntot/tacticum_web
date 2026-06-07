<?php

declare(strict_types=1);

namespace Tacticum\Offer;

use Tacticum\Rest\Config;

final class OfferTaxonomyService
{
    private static ?array $model = null;

    public static function model(): array
    {
        if (self::$model !== null) {
            return self::$model;
        }

        $config = Config::section('offer');
        $source = (string)($config['taxonomy_source'] ?? 'fallback');
        $ttl = isset($config['taxonomy_cache_ttl']) ? (int)$config['taxonomy_cache_ttl'] : 300;
        $allowFallback = (bool)($config['allow_taxonomy_fallback'] ?? true);
        $bitrixTerms = in_array($source, ['bitrix', 'auto'], true) ? OfferTaxonomyRepository::activeTerms($ttl) : [];

        $terms = $bitrixTerms;
        $resolvedSource = $bitrixTerms !== [] ? 'bitrix' : $source;
        if ($terms === [] && ($source !== 'bitrix' || $allowFallback)) {
            $terms = OfferTaxonomyFallback::terms();
            $resolvedSource = 'fallback';
        }

        self::$model = self::buildModel($terms, $resolvedSource);
        return self::$model;
    }

    public static function publicLabel(string $dimension, string $label): string
    {
        $label = CatalogMapper::trim($label);
        return $label === '' ? '' : (self::term($dimension, $label)['publicLabel'] ?? $label);
    }

    public static function canonicalCode(string $dimension, string $label): string
    {
        $label = CatalogMapper::trim($label);
        if ($label === '') {
            return '';
        }

        return self::term($dimension, $label)['code'] ?? CatalogMapper::slug($label);
    }

    public static function term(string $dimension, string $label): ?array
    {
        $dimension = self::dimension($dimension);
        $alias = self::normalizeAlias($label);
        return $dimension !== '' && $alias !== '' ? (self::model()['aliasMap'][$dimension][$alias] ?? null) : null;
    }

    public static function normalizeOptions(array $options, string $group): array
    {
        $dimension = self::dimensionForGroup($group);
        $terms = self::model()['termsByDimension'][$dimension] ?? [];
        $byCode = [];
        foreach ($options as $option) {
            if (is_array($option) && (int)($option['count'] ?? 0) > 0) {
                $byCode[(string)($option['key'] ?? '')] = $option;
            }
        }

        $result = [];
        foreach ($terms as $term) {
            $code = (string)$term['code'];
            if (!($term['active'] ?? false) || !isset($byCode[$code])) {
                continue;
            }
            $result[] = array_merge($byCode[$code], ['label' => (string)$term['publicLabel']]);
            unset($byCode[$code]);
        }
        uasort($byCode, static fn(array $a, array $b): int => strcasecmp((string)$a['label'], (string)$b['label']));

        return array_values(array_merge($result, $byCode));
    }

    public static function featuredOptions(array $options, string $group): array
    {
        $dimension = self::dimensionForGroup($group);
        $optionsByKey = [];
        foreach ((array)($options[$group] ?? []) as $option) {
            if (is_array($option) && (int)($option['count'] ?? 0) > 0) {
                $optionsByKey[(string)($option['key'] ?? '')] = $option;
            }
        }

        $featured = [];
        foreach (self::model()['featured'][$dimension] ?? [] as $term) {
            $code = (string)$term['code'];
            if (isset($optionsByKey[$code])) {
                $featured[] = array_merge($optionsByKey[$code], ['label' => (string)$term['publicLabel']]);
            }
        }

        return $featured;
    }

    public static function relatedIblockIds(): array { return OfferTaxonomyCache::relatedIblockIds(); }
    public static function clearCache(int $iblockId = 0): void { OfferTaxonomyCache::clear($iblockId); self::$model = null; }

    private static function buildModel(array $terms, string $source): array
    {
        $byDimension = [];
        $aliasMap = [];
        $featured = [];
        foreach ($terms as $term) {
            $term = self::normalizeTerm((array)$term);
            if ($term === null) {
                continue;
            }
            $dimension = $term['dimension'];
            $byDimension[$dimension][] = $term;
            if ($term['featured'] && $term['active']) {
                $featured[$dimension][] = $term;
            }
            foreach (array_unique(array_merge($term['aliases'], [$term['code'], $term['publicLabel'], $term['shortLabel']])) as $alias) {
                $alias = self::normalizeAlias((string)$alias);
                if ($alias !== '') {
                    $aliasMap[$dimension][$alias] = $term;
                }
            }
        }
        foreach ($byDimension as &$dimensionTerms) {
            usort($dimensionTerms, static fn(array $a, array $b): int => ($a['sort'] <=> $b['sort']) ?: strnatcasecmp($a['code'], $b['code']));
        }
        unset($dimensionTerms);

        return ['schema' => 'tacticum.offer.taxonomy.v1', 'source' => $source, 'termsByDimension' => $byDimension, 'aliasMap' => $aliasMap, 'featured' => $featured];
    }

    private static function normalizeTerm(array $term): ?array
    {
        $dimension = self::dimension((string)($term['dimension'] ?? ''));
        $code = CatalogFilters::cleanKey($term['code'] ?? '');
        $label = CatalogMapper::trim((string)($term['publicLabel'] ?? ''));
        if ($dimension === '' || $code === '' || $label === '') {
            return null;
        }

        return ['dimension' => $dimension, 'code' => $code, 'publicLabel' => $label, 'shortLabel' => CatalogMapper::trim((string)($term['shortLabel'] ?? $label)), 'aliases' => self::stringList($term['aliases'] ?? []), 'sort' => max(1, (int)($term['sort'] ?? 500)), 'active' => (bool)($term['active'] ?? true), 'featured' => (bool)($term['featured'] ?? false), 'productFamily' => CatalogMapper::trim((string)($term['productFamily'] ?? ''))];
    }

    private static function dimensionForGroup(string $group): string
    {
        return ['sectors' => 'sector', 'scenarios' => 'scenario', 'phases' => 'phase', 'budgets' => 'budget'][$group] ?? '';
    }

    private static function dimension(string $dimension): string
    {
        return in_array($dimension, ['sector', 'scenario', 'phase', 'budget'], true) ? $dimension : '';
    }

    private static function normalizeAlias(string $value): string { return mb_strtolower(CatalogMapper::trim($value)); }
    private static function stringList(mixed $value): array
    {
        return array_values(array_filter(array_map(static fn(mixed $item): string => CatalogMapper::trim((string)$item), (array)$value), static fn(string $item): bool => $item !== ''));
    }
}
