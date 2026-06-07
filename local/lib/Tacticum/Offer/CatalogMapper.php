<?php

namespace Tacticum\Offer;

final class CatalogMapper
{
    public static function decodeText(string $value): string
    {
        return function_exists('tacticum_decode_iblock_text')
            ? tacticum_decode_iblock_text($value)
            : html_entity_decode($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function trim(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?: '');
    }

    public static function slug(string $label): string
    {
        $label = self::trim($label);
        if ($label === '') {
            return '';
        }

        $slug = \CUtil::translit($label, 'ru', [
            'replace_space' => '-',
            'replace_other' => '-',
            'change_case' => 'L',
        ]);
        $slug = trim(preg_replace('/[^a-z0-9_-]+/', '-', (string)$slug) ?: '', '-');

        return $slug !== '' ? mb_substr($slug, 0, 80) : 'value-' . hash('crc32b', $label);
    }

    public static function propertyValue(array $properties, string $code): mixed
    {
        $property = $properties[$code] ?? null;

        return is_array($property) ? ($property['~VALUE'] ?? $property['VALUE'] ?? null) : null;
    }

    public static function propertyText(array $properties, string $code): string
    {
        $value = self::propertyValue($properties, $code);
        if (is_array($value) && array_key_exists('TEXT', $value)) {
            $value = $value['TEXT'];
        } elseif (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                if (is_array($item) && array_key_exists('TEXT', $item)) {
                    $item = $item['TEXT'];
                }
                if (!is_array($item)) {
                    $parts[] = (string)$item;
                }
            }
            $value = implode(' ', $parts);
        }

        return self::trim(self::decodeText(strip_tags((string)$value)));
    }

    public static function propertyHtml(array $properties, string $code): string
    {
        $value = self::propertyValue($properties, $code);
        if (is_array($value) && array_key_exists('TEXT', $value)) {
            $value = $value['TEXT'];
        }

        return (string)$value;
    }

    public static function propertyList(array $properties, string $code): array
    {
        $value = self::propertyValue($properties, $code);
        if ($value === null || $value === false || $value === '') {
            return [];
        }

        $result = [];
        foreach (is_array($value) ? $value : [$value] as $item) {
            if (is_array($item) && array_key_exists('TEXT', $item)) {
                $item = $item['TEXT'];
            }
            if (!is_array($item)) {
                $text = self::trim(self::decodeText(strip_tags((string)$item)));
                if ($text !== '') {
                    $result[] = $text;
                }
            }
        }

        return array_values(array_unique($result));
    }

    public static function excerpt(string $text, int $limit = 170): string
    {
        $text = self::trim($text);
        return mb_strlen($text) <= $limit
            ? $text
            : rtrim(mb_substr($text, 0, $limit - 1), " \t\n\r\0\x0B.,;:") . '...';
    }

    public static function budgetAmount(string $budgetRaw, array $response): int
    {
        $amount = (int)($response['budget_amount'] ?? 0);
        if ($amount > 0) {
            return $amount;
        }

        $digits = preg_replace('/[^\d]+/', '', $budgetRaw) ?: '';

        return $digits !== '' ? (int)$digits : 0;
    }

    public static function response(array $properties): array
    {
        $responseRaw = self::propertyText($properties, 'RESPONSE');
        $response = $responseRaw !== '' ? json_decode($responseRaw, true) : [];

        return is_array($response) ? $response : [];
    }

    public static function scenarioFromH1(string $h1): string
    {
        if (preg_match('/^(.+?)\s+для\s+/u', $h1, $matches)) {
            return self::trim((string)$matches[1]);
        }

        return $h1 !== '' ? self::trim($h1) : 'Расчет проекта';
    }

    public static function itemFromElement(array $fields, array $properties): ?array
    {
        $code = trim((string)($fields['CODE'] ?? ''));
        if ($code === '' || !preg_match('/^[A-Za-z0-9_-]{1,120}$/', $code)) {
            return null;
        }

        $response = self::response($properties);
        $title = self::propertyText($properties, 'H1') ?: self::propertyText($properties, 'TITLE');
        $title = $title !== '' ? $title : self::decodeText((string)($fields['NAME'] ?? ''));
        $summary = self::propertyText($properties, 'SUMMARY') ?: self::propertyText($properties, 'BUSINESS_CONTEXT');
        $budgetRaw = self::propertyText($properties, 'BUDGET');
        $budgetAmount = self::budgetAmount($budgetRaw, $response);
        $budgetBucket = CatalogTaxonomy::budgetBucket($budgetAmount);
        $goals = self::propertyList($properties, 'GOALS');
        $team = self::propertyList($properties, 'TEAM');
        $stack = self::propertyList($properties, 'STACK');
        $sectorRaw = self::trim((string)($response['sector'] ?? 'Другие отрасли'));
        $region = self::trim((string)($response['region'] ?? ''));
        $phaseRaw = self::trim((string)($response['phase'] ?? ''));
        $scenarioRaw = self::trim((string)($response['scenario'] ?? '')) ?: self::scenarioFromH1($title);
        $sector = CatalogTaxonomy::publicLabel('sector', $sectorRaw);
        $phase = CatalogTaxonomy::publicLabel('phase', $phaseRaw);
        $scenario = CatalogTaxonomy::publicLabel('scenario', $scenarioRaw);
        $dateSortRaw = (string)(($fields['DATE_ACTIVE_FROM'] ?? '') ?: ($fields['DATE_CREATE'] ?? '') ?: ($fields['TIMESTAMP_X'] ?? ''));
        $dateSort = function_exists('MakeTimeStamp') ? (int)MakeTimeStamp($dateSortRaw) : (int)strtotime($dateSortRaw);

        return [
            'id' => (int)($fields['ID'] ?? 0),
            'name' => self::decodeText((string)($fields['NAME'] ?? '')),
            'code' => $code,
            'url' => function_exists('tacticum_offer_detail_path') ? tacticum_offer_detail_path($code) : '/offer/' . $code . '/',
            'date_create' => (string)($fields['DATE_CREATE'] ?? ''),
            'date_active_from' => (string)($fields['DATE_ACTIVE_FROM'] ?? ''),
            'timestamp_x' => (string)($fields['TIMESTAMP_X'] ?? ''),
            'date_sort' => $dateSort,
            'title' => $title !== '' ? $title : 'Пример расчета проекта',
            'summary' => self::excerpt($summary),
            'goals' => array_slice($goals, 0, 2),
            'team_count' => count($team),
            'stack' => array_slice($stack, 0, 3),
            'budget' => $budgetRaw,
            'budget_amount' => $budgetAmount,
            'budget_display' => CatalogTaxonomy::formatBudgetAmount($budgetAmount),
            'budget_bucket' => $budgetBucket['key'],
            'budget_bucket_label' => $budgetBucket['label'],
            'timeline' => self::propertyText($properties, 'TIMELINE'),
            'sector' => $sector,
            'sector_key' => self::slug($sectorRaw),
            'scenario' => $scenario,
            'scenario_key' => self::slug($scenarioRaw),
            'region' => $region,
            'phase' => $phase,
            'phase_key' => self::slug($phaseRaw),
            'is_synthetic' => str_starts_with(self::propertyText($properties, 'GROUP_ID'), 'offer-seed-'),
            'haystack' => mb_strtolower(implode(' ', array_filter([
                $title,
                $summary,
                $sectorRaw,
                $sector,
                $region,
                $phaseRaw,
                $phase,
                $scenarioRaw,
                $scenario,
                $budgetRaw,
                implode(' ', $goals),
                implode(' ', $team),
                implode(' ', $stack),
                self::propertyText($properties, 'FUNCTIONAL_REQUIREMENTS'),
                self::propertyText($properties, 'NONFUNCTIONAL_REQUIREMENTS'),
            ], 'strlen'))),
        ];
    }
}
