<?php

namespace Tacticum\Offer;

final class CatalogTaxonomy
{
    private const PUBLIC_LABELS = [
        'sector' => [
            'beauty' => 'бьюти и салоны',
            'e-commerce' => 'онлайн-торговля',
        ],
        'scenario' => [
            'data platform и mlops' => 'Платформа данных и MLOps',
            'voice analytics и контроль качества' => 'Голосовая аналитика и контроль качества',
        ],
        'phase' => [
            'production-внедрение' => 'внедрение в рабочую эксплуатацию',
        ],
    ];

    private const FEATURED_OPTION_KEYS = [
        'sectors' => [
            'meditsina',
            'riteyl',
            'proizvodstvo',
            'finansy',
            'logistika',
            'e-commerce',
            'nedvizhimost',
            'obrazovanie',
        ],
        'scenarios' => [
            'ai-assistent-podderzhki',
            'ai-kopaylot-dlya-sotrudnikov',
            'ai-poisk-po-korporativnym-znaniyam',
            'rpa-i-dokumentooborot',
            'bi-i-upravlencheskaya-analitika',
            'prognozirovanie-sprosa',
            'integratsionnaya-shina-i-api',
            'prediktivnaya-analitika-oborudovaniya',
        ],
    ];

    public static function publicLabel(string $dimension, string $label): string
    {
        $label = CatalogMapper::trim($label);
        if ($label === '') {
            return '';
        }

        return self::PUBLIC_LABELS[$dimension][mb_strtolower($label)] ?? $label;
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
        $allowedKeys = self::FEATURED_OPTION_KEYS[$group] ?? [];
        if ($allowedKeys === [] || empty($options[$group]) || !is_array($options[$group])) {
            return [];
        }

        $optionsByKey = [];
        foreach ($options[$group] as $option) {
            if (!is_array($option)) {
                continue;
            }
            $key = (string)($option['key'] ?? '');
            if ($key !== '' && (int)($option['count'] ?? 0) > 0) {
                $optionsByKey[$key] = $option;
            }
        }

        return array_values(array_filter(array_map(
            static fn(string $key): ?array => $optionsByKey[$key] ?? null,
            $allowedKeys
        )));
    }
}
