<?php

namespace Tacticum\Offer;

final class CatalogTaxonomy
{
    public static function publicLabel(string $dimension, string $label): string
    {
        return OfferTaxonomyService::publicLabel($dimension, $label);
    }

    public static function canonicalCode(string $dimension, string $label): string
    {
        return OfferTaxonomyService::canonicalCode($dimension, $label);
    }

    public static function formatBudgetAmount(int $amount): string
    {
        return $amount > 0 ? number_format($amount, 0, '', ' ') . ' руб.' : '';
    }

    public static function budgetBuckets(): array
    {
        return [
            'up-to-1m' => ['label' => 'до 1 млн руб.', 'min' => 0, 'max' => 1000000],
            '1-3m' => ['label' => '1-3 млн руб.', 'min' => 1000000, 'max' => 3000000],
            '3-7m' => ['label' => '3-7 млн руб.', 'min' => 3000000, 'max' => 7000000],
            '7-15m' => ['label' => '7-15 млн руб.', 'min' => 7000000, 'max' => 15000000],
            '15-30m' => ['label' => '15-30 млн руб.', 'min' => 15000000, 'max' => 30000000],
            '30-75m' => ['label' => '30-75 млн руб.', 'min' => 30000000, 'max' => 75000000],
            '75m-plus' => ['label' => '75+ млн руб.', 'min' => 75000000, 'max' => PHP_INT_MAX],
        ];
    }

    public static function budgetBucket(int $amount): array
    {
        foreach (self::budgetBuckets() as $key => $bucket) {
            if ($amount >= $bucket['min'] && $amount <= $bucket['max']) {
                return ['key' => $key, 'label' => $bucket['label']];
            }
        }

        return ['key' => '', 'label' => ''];
    }

    public static function featuredOptions(array $options, string $group): array
    {
        return OfferTaxonomyService::featuredOptions($options, $group);
    }

    public static function normalizeOptions(array $options, string $group): array
    {
        return OfferTaxonomyService::normalizeOptions($options, $group);
    }
}
