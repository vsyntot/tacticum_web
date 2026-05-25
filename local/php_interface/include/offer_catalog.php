<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

$offerCatalogCacheHelper = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/offer_catalog_cache.php';
if (file_exists($offerCatalogCacheHelper)) {
    require_once $offerCatalogCacheHelper;
}

if (!function_exists('tacticum_offer_catalog_decode_text')) {
    function tacticum_offer_catalog_decode_text(string $value): string
    {
        if (function_exists('tacticum_decode_iblock_text')) {
            return tacticum_decode_iblock_text($value);
        }

        return html_entity_decode($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('tacticum_offer_find_element')) {
    function tacticum_offer_find_element(int $offerId, string $offerCode): ?array
    {
        return class_exists('TacticumOfferCatalogService')
            ? TacticumOfferCatalogService::findElement($offerId, $offerCode)
            : null;
    }
}

if (!function_exists('tacticum_offer_catalog_trim')) {
    function tacticum_offer_catalog_trim(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?: '');
    }
}

if (!function_exists('tacticum_offer_catalog_slug')) {
    function tacticum_offer_catalog_slug(string $label): string
    {
        $label = tacticum_offer_catalog_trim($label);
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
}

if (!function_exists('tacticum_offer_catalog_property_value')) {
    function tacticum_offer_catalog_property_value(array $properties, string $code): mixed
    {
        $property = $properties[$code] ?? null;
        if (!is_array($property)) {
            return null;
        }

        return $property['~VALUE'] ?? $property['VALUE'] ?? null;
    }
}

if (!function_exists('tacticum_offer_catalog_property_text')) {
    function tacticum_offer_catalog_property_text(array $properties, string $code): string
    {
        $value = tacticum_offer_catalog_property_value($properties, $code);
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

        return tacticum_offer_catalog_trim(tacticum_offer_catalog_decode_text(strip_tags((string)$value)));
    }
}

if (!function_exists('tacticum_offer_catalog_property_html')) {
    function tacticum_offer_catalog_property_html(array $properties, string $code): string
    {
        $value = tacticum_offer_catalog_property_value($properties, $code);
        if (is_array($value) && array_key_exists('TEXT', $value)) {
            $value = $value['TEXT'];
        }

        return (string)$value;
    }
}

if (!function_exists('tacticum_offer_catalog_property_list')) {
    function tacticum_offer_catalog_property_list(array $properties, string $code): array
    {
        $value = tacticum_offer_catalog_property_value($properties, $code);
        if ($value === null || $value === false || $value === '') {
            return [];
        }

        $items = is_array($value) ? $value : [$value];
        $result = [];
        foreach ($items as $item) {
            if (is_array($item) && array_key_exists('TEXT', $item)) {
                $item = $item['TEXT'];
            }
            if (is_array($item)) {
                continue;
            }

            $text = tacticum_offer_catalog_trim(tacticum_offer_catalog_decode_text(strip_tags((string)$item)));
            if ($text !== '') {
                $result[] = $text;
            }
        }

        return array_values(array_unique($result));
    }
}

if (!function_exists('tacticum_offer_catalog_excerpt')) {
    function tacticum_offer_catalog_excerpt(string $text, int $limit = 170): string
    {
        $text = tacticum_offer_catalog_trim($text);
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit - 1), " \t\n\r\0\x0B.,;:") . '...';
    }
}

if (!function_exists('tacticum_offer_catalog_budget_amount')) {
    function tacticum_offer_catalog_budget_amount(string $budgetRaw, array $response): int
    {
        $amount = (int)($response['budget_amount'] ?? 0);
        if ($amount > 0) {
            return $amount;
        }

        $digits = preg_replace('/[^\d]+/', '', $budgetRaw) ?: '';

        return $digits !== '' ? (int)$digits : 0;
    }
}

if (!function_exists('tacticum_offer_catalog_budget_buckets')) {
    function tacticum_offer_catalog_budget_buckets(): array
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
}

if (!function_exists('tacticum_offer_catalog_budget_bucket')) {
    function tacticum_offer_catalog_budget_bucket(int $amount): array
    {
        foreach (tacticum_offer_catalog_budget_buckets() as $key => $bucket) {
            if ($amount >= $bucket['min'] && $amount <= $bucket['max']) {
                return ['key' => $key, 'label' => $bucket['label']];
            }
        }

        return ['key' => '', 'label' => ''];
    }
}

if (!function_exists('tacticum_offer_catalog_response')) {
    function tacticum_offer_catalog_response(array $properties): array
    {
        $responseRaw = tacticum_offer_catalog_property_text($properties, 'RESPONSE');
        if ($responseRaw === '') {
            return [];
        }

        $response = json_decode($responseRaw, true);

        return is_array($response) ? $response : [];
    }
}

if (!function_exists('tacticum_offer_catalog_scenario_from_h1')) {
    function tacticum_offer_catalog_scenario_from_h1(string $h1): string
    {
        if (preg_match('/^(.+?)\s+для\s+/u', $h1, $matches)) {
            return tacticum_offer_catalog_trim((string)$matches[1]);
        }

        return $h1 !== '' ? tacticum_offer_catalog_trim($h1) : 'Расчет проекта';
    }
}

if (!function_exists('tacticum_offer_catalog_item_from_element')) {
    function tacticum_offer_catalog_item_from_element(array $fields, array $properties): ?array
    {
        $code = trim((string)($fields['CODE'] ?? ''));
        if ($code === '' || !preg_match('/^[A-Za-z0-9_-]{1,120}$/', $code)) {
            return null;
        }

        $response = tacticum_offer_catalog_response($properties);
        $title = tacticum_offer_catalog_property_text($properties, 'H1');
        if ($title === '') {
            $title = tacticum_offer_catalog_property_text($properties, 'TITLE');
        }
        if ($title === '') {
            $title = tacticum_offer_catalog_decode_text((string)($fields['NAME'] ?? ''));
        }

        $summary = tacticum_offer_catalog_property_text($properties, 'SUMMARY');
        if ($summary === '') {
            $summary = tacticum_offer_catalog_property_text($properties, 'BUSINESS_CONTEXT');
        }

        $budgetRaw = tacticum_offer_catalog_property_text($properties, 'BUDGET');
        $budgetAmount = tacticum_offer_catalog_budget_amount($budgetRaw, $response);
        $budgetBucket = tacticum_offer_catalog_budget_bucket($budgetAmount);
        $goals = tacticum_offer_catalog_property_list($properties, 'GOALS');
        $team = tacticum_offer_catalog_property_list($properties, 'TEAM');
        $stack = tacticum_offer_catalog_property_list($properties, 'STACK');
        $sector = tacticum_offer_catalog_trim((string)($response['sector'] ?? 'Другие отрасли'));
        $region = tacticum_offer_catalog_trim((string)($response['region'] ?? ''));
        $phase = tacticum_offer_catalog_trim((string)($response['phase'] ?? ''));
        $scenario = tacticum_offer_catalog_trim((string)($response['scenario'] ?? ''));
        if ($scenario === '') {
            $scenario = tacticum_offer_catalog_scenario_from_h1($title);
        }
        $dateSortRaw = (string)(($fields['DATE_ACTIVE_FROM'] ?? '') ?: ($fields['DATE_CREATE'] ?? '') ?: ($fields['TIMESTAMP_X'] ?? ''));
        $dateSort = function_exists('MakeTimeStamp')
            ? (int)MakeTimeStamp($dateSortRaw)
            : (int)strtotime($dateSortRaw);

        $haystack = mb_strtolower(implode(' ', array_filter([
            $title,
            $summary,
            $sector,
            $region,
            $phase,
            $scenario,
            $budgetRaw,
            implode(' ', $goals),
            implode(' ', $team),
            implode(' ', $stack),
            tacticum_offer_catalog_property_text($properties, 'FUNCTIONAL_REQUIREMENTS'),
            tacticum_offer_catalog_property_text($properties, 'NONFUNCTIONAL_REQUIREMENTS'),
        ], 'strlen')));

        return [
            'id' => (int)($fields['ID'] ?? 0),
            'name' => tacticum_offer_catalog_decode_text((string)($fields['NAME'] ?? '')),
            'code' => $code,
            'url' => tacticum_offer_detail_path($code),
            'date_create' => (string)($fields['DATE_CREATE'] ?? ''),
            'date_active_from' => (string)($fields['DATE_ACTIVE_FROM'] ?? ''),
            'timestamp_x' => (string)($fields['TIMESTAMP_X'] ?? ''),
            'date_sort' => $dateSort,
            'title' => $title !== '' ? $title : 'Пример расчета проекта',
            'summary' => tacticum_offer_catalog_excerpt($summary),
            'goals' => array_slice($goals, 0, 2),
            'team_count' => count($team),
            'stack' => array_slice($stack, 0, 3),
            'budget' => $budgetRaw,
            'budget_amount' => $budgetAmount,
            'budget_bucket' => $budgetBucket['key'],
            'budget_bucket_label' => $budgetBucket['label'],
            'timeline' => tacticum_offer_catalog_property_text($properties, 'TIMELINE'),
            'sector' => $sector,
            'sector_key' => tacticum_offer_catalog_slug($sector),
            'scenario' => $scenario,
            'scenario_key' => tacticum_offer_catalog_slug($scenario),
            'region' => $region,
            'phase' => $phase,
            'phase_key' => tacticum_offer_catalog_slug($phase),
            'is_synthetic' => str_starts_with(
                tacticum_offer_catalog_property_text($properties, 'GROUP_ID'),
                'offer-seed-'
            ),
            'haystack' => $haystack,
        ];
    }
}

if (!class_exists('TacticumOfferCatalogRepository')) {
    final class TacticumOfferCatalogRepository
    {
        public function findElement(int $offerId, string $offerCode): ?array
        {
            if (!\Bitrix\Main\Loader::includeModule('iblock')) {
                return null;
            }

            $iblockId = tacticum_iblock_id('offer');
            if ($iblockId <= 0) {
                return null;
            }

            $filter = [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
            ];
            if ($offerCode !== '') {
                if (!preg_match('/^[A-Za-z0-9_-]{1,120}$/', $offerCode)) {
                    return null;
                }
                $filter['=CODE'] = $offerCode;
            } elseif ($offerId > 0) {
                $filter['=ID'] = $offerId;
            } else {
                return null;
            }

            $result = \CIBlockElement::GetList(
                [],
                $filter,
                false,
                ['nTopCount' => 1],
                [
                    'ID',
                    'IBLOCK_ID',
                    'NAME',
                    'CODE',
                    'DATE_CREATE',
                    'TIMESTAMP_X',
                    'PROPERTY_TITLE',
                    'PROPERTY_DESCRIPTION',
                    'PROPERTY_H1',
                ]
            );
            $element = $result->Fetch();
            if (!$element || trim((string)$element['CODE']) === '') {
                return null;
            }

            $keywords = [];
            $propertyResult = \CIBlockElement::GetProperty(
                $iblockId,
                (int)$element['ID'],
                ['sort' => 'asc'],
                ['CODE' => 'KEYWORDS']
            );
            while ($property = $propertyResult->Fetch()) {
                $value = trim(tacticum_offer_catalog_decode_text((string)($property['VALUE'] ?? '')));
                if ($value !== '') {
                    $keywords[] = $value;
                }
            }

            $title = trim(tacticum_offer_catalog_decode_text((string)($element['PROPERTY_TITLE_VALUE'] ?? '')));
            if ($title === '') {
                $title = trim(tacticum_offer_catalog_decode_text((string)($element['NAME'] ?? '')));
            }
            if ($title === '') {
                $title = 'Пример расчета проекта - Тактикум';
            }

            $description = trim(tacticum_offer_catalog_decode_text((string)($element['PROPERTY_DESCRIPTION_VALUE'] ?? '')));
            if ($description === '') {
                $description = 'Пример расчета AI-проекта Tacticum: состав работ, команда, сроки и бюджет.';
            }

            $element['SEO_TITLE'] = $title;
            $element['SEO_DESCRIPTION'] = $description;
            $element['SEO_H1'] = trim(tacticum_offer_catalog_decode_text((string)($element['PROPERTY_H1_VALUE'] ?? '')));
            $element['KEYWORDS'] = $keywords;

            return $element;
        }

        public function items(int $iblockId): array
        {
            if ($iblockId <= 0 || !\Bitrix\Main\Loader::includeModule('iblock')) {
                return [];
            }

            $items = [];
            $result = \CIBlockElement::GetList(
                ['ID' => 'DESC'],
                ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'DATE_CREATE', 'DATE_ACTIVE_FROM', 'TIMESTAMP_X']
            );

            while ($element = $result->GetNextElement()) {
                $fields = $element->GetFields();
                $properties = $element->GetProperties();
                $item = tacticum_offer_catalog_item_from_element($fields, $properties);
                if ($item !== null) {
                    $items[] = $item;
                }
            }

            return $items;
        }
    }
}

if (!class_exists('TacticumOfferCatalogService')) {
    final class TacticumOfferCatalogService
    {
        public static function repository(): TacticumOfferCatalogRepository
        {
            static $repository = null;

            if (!$repository instanceof TacticumOfferCatalogRepository) {
                $repository = new TacticumOfferCatalogRepository();
            }

            return $repository;
        }

        public static function findElement(int $offerId, string $offerCode): ?array
        {
            return self::repository()->findElement($offerId, $offerCode);
        }

        public static function clearCache(int $iblockId = 0): void
        {
            TacticumOfferCatalogCache::clear($iblockId);
        }

        public static function items(int $iblockId): array
        {
            if ($iblockId <= 0 || !\Bitrix\Main\Loader::includeModule('iblock')) {
                return [];
            }

            $cache = \Bitrix\Main\Data\Cache::createInstance();
            $cacheId = TacticumOfferCatalogCache::cacheId($iblockId);
            $cacheDir = TacticumOfferCatalogCache::CACHE_DIR;

            if ($cache->initCache(TacticumOfferCatalogCache::CACHE_TTL, $cacheId, $cacheDir)) {
                $cached = $cache->getVars();
                if (is_array($cached) && isset($cached['items']) && is_array($cached['items'])) {
                    return $cached['items'];
                }
            }

            $items = [];
            if ($cache->startDataCache(TacticumOfferCatalogCache::CACHE_TTL, $cacheId, $cacheDir)) {
                TacticumOfferCatalogCache::startTagCache($iblockId);
                $items = self::repository()->items($iblockId);
                TacticumOfferCatalogCache::endTagCache();

                $cache->endDataCache(['items' => $items]);
            }

            return $items;
        }

        public static function prepare(int $iblockId, array $filters, int $perPage = 24): array
        {
            $items = self::items($iblockId);
            $filtered = tacticum_offer_catalog_filter_items($items, $filters);
            $total = count($filtered);
            $totalPages = max(1, (int)ceil($total / $perPage));
            $page = min(max(1, (int)($filters['page'] ?? 1)), $totalPages);
            $pageItems = array_slice($filtered, ($page - 1) * $perPage, $perPage);
            $budgets = array_filter(array_map(static fn(array $item): int => (int)($item['budget_amount'] ?? 0), $items));
            $options = tacticum_offer_catalog_options($items);

            return [
                'filters' => array_merge($filters, ['page' => $page]),
                'items' => $pageItems,
                'total' => $total,
                'all_total' => count($items),
                'page' => $page,
                'total_pages' => $totalPages,
                'per_page' => $perPage,
                'options' => $options,
                'has_filters' => tacticum_offer_catalog_has_filters($filters),
                'pagination' => tacticum_offer_catalog_pagination_range($page, $totalPages),
                'stats' => [
                    'sectors' => count($options['sectors']),
                    'scenarios' => count($options['scenarios']),
                    'budget_min' => $budgets !== [] ? min($budgets) : 0,
                    'budget_max' => $budgets !== [] ? max($budgets) : 0,
                ],
            ];
        }
    }
}

if (!function_exists('tacticum_offer_catalog_items')) {
    function tacticum_offer_catalog_items(int $iblockId): array
    {
        return TacticumOfferCatalogService::items($iblockId);
    }
}

if (!function_exists('tacticum_offer_catalog_clean_key')) {
    function tacticum_offer_catalog_clean_key(mixed $value): string
    {
        if (is_array($value)) {
            return '';
        }

        $value = trim((string)$value);

        return preg_match('/^[a-z0-9_-]{1,80}$/', $value) ? $value : '';
    }
}

if (!function_exists('tacticum_offer_catalog_is_catalog_path')) {
    function tacticum_offer_catalog_is_catalog_path(string $path): bool
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;

        return preg_match('#^/offer/catalog(?:/|$)#', $path) === 1;
    }
}

if (!function_exists('tacticum_offer_catalog_path_filters')) {
    function tacticum_offer_catalog_path_filters(string $path): array
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        if (!tacticum_offer_catalog_is_catalog_path($path)) {
            return [];
        }

        $relative = preg_replace('#^/offer/catalog/?#', '', $path) ?: '';
        $segments = array_values(array_filter(
            explode('/', trim($relative, '/')),
            static fn(string $segment): bool => $segment !== ''
        ));

        $filters = [];
        $segmentCount = count($segments);
        for ($index = 0; $index < $segmentCount; $index += 2) {
            $key = strtolower(rawurldecode((string)$segments[$index]));
            $value = rawurldecode((string)($segments[$index + 1] ?? ''));

            if (in_array($key, ['sector', 'scenario', 'budget', 'phase'], true)) {
                $cleanValue = tacticum_offer_catalog_clean_key($value);
                if ($cleanValue !== '') {
                    $filters[$key] = $cleanValue;
                }
                continue;
            }

            if ($key === 'page' && filter_var($value, FILTER_VALIDATE_INT) !== false) {
                $filters['page'] = max(1, (int)$value);
            }
        }

        return $filters;
    }
}

if (!function_exists('tacticum_offer_catalog_has_pretty_segments')) {
    function tacticum_offer_catalog_has_pretty_segments(array $filters): bool
    {
        foreach (['sector', 'scenario', 'budget', 'phase'] as $key) {
            if (trim((string)($filters[$key] ?? '')) !== '') {
                return true;
            }
        }

        return (int)($filters['page'] ?? 1) > 1;
    }
}

if (!function_exists('tacticum_offer_catalog_normalize_filters')) {
    function tacticum_offer_catalog_normalize_filters(array $source, array $pathFilters = []): array
    {
        if ($pathFilters !== []) {
            $source = array_merge($source, $pathFilters);
        }

        $q = $source['q'] ?? '';
        $q = is_array($q) ? '' : tacticum_offer_catalog_trim((string)$q);
        $q = mb_substr($q, 0, 80);
        $sort = tacticum_offer_catalog_clean_key($source['sort'] ?? '');
        if (!in_array($sort, ['new', 'budget-desc', 'budget-asc'], true)) {
            $sort = 'new';
        }

        $pageRaw = $source['page'] ?? 1;
        $page = is_array($pageRaw) || filter_var($pageRaw, FILTER_VALIDATE_INT) === false ? 1 : (int)$pageRaw;

        return [
            'q' => $q,
            'sector' => tacticum_offer_catalog_clean_key($source['sector'] ?? ''),
            'scenario' => tacticum_offer_catalog_clean_key($source['scenario'] ?? ''),
            'budget' => tacticum_offer_catalog_clean_key($source['budget'] ?? ''),
            'phase' => tacticum_offer_catalog_clean_key($source['phase'] ?? ''),
            'sort' => $sort,
            'page' => max(1, $page),
        ];
    }
}

if (!function_exists('tacticum_offer_catalog_has_filters')) {
    function tacticum_offer_catalog_has_filters(array $filters, bool $includePage = false): bool
    {
        foreach (['q', 'sector', 'scenario', 'budget', 'phase'] as $key) {
            if (trim((string)($filters[$key] ?? '')) !== '') {
                return true;
            }
        }

        if (($filters['sort'] ?? 'new') !== 'new') {
            return true;
        }

        return $includePage && (int)($filters['page'] ?? 1) > 1;
    }
}

if (!function_exists('tacticum_offer_catalog_filter_items')) {
    function tacticum_offer_catalog_filter_items(array $items, array $filters): array
    {
        $q = mb_strtolower(tacticum_offer_catalog_trim((string)($filters['q'] ?? '')));
        $filtered = array_values(array_filter($items, static function (array $item) use ($filters, $q): bool {
            foreach (['sector', 'scenario', 'budget', 'phase'] as $key) {
                $filterValue = (string)($filters[$key] ?? '');
                $itemKey = $key === 'budget' ? 'budget_bucket' : $key . '_key';
                if ($filterValue !== '' && (string)($item[$itemKey] ?? '') !== $filterValue) {
                    return false;
                }
            }

            return $q === '' || str_contains((string)($item['haystack'] ?? ''), $q);
        }));

        $sort = (string)($filters['sort'] ?? 'new');
        usort($filtered, static function (array $a, array $b) use ($sort): int {
            if ($sort === 'budget-desc') {
                return ((int)($b['budget_amount'] ?? 0)) <=> ((int)($a['budget_amount'] ?? 0));
            }
            if ($sort === 'budget-asc') {
                return ((int)($a['budget_amount'] ?? 0)) <=> ((int)($b['budget_amount'] ?? 0));
            }

            $dateCompare = ((int)($b['date_sort'] ?? 0)) <=> ((int)($a['date_sort'] ?? 0));
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
        });

        return $filtered;
    }
}

if (!function_exists('tacticum_offer_catalog_add_option')) {
    function tacticum_offer_catalog_add_option(array &$options, string $group, string $key, string $label): void
    {
        if ($key === '' || $label === '') {
            return;
        }

        if (!isset($options[$group][$key])) {
            $options[$group][$key] = ['key' => $key, 'label' => $label, 'count' => 0];
        }
        $options[$group][$key]['count']++;
    }
}

if (!function_exists('tacticum_offer_catalog_options')) {
    function tacticum_offer_catalog_options(array $items): array
    {
        $options = [
            'sectors' => [],
            'scenarios' => [],
            'phases' => [],
            'budgets' => [],
        ];

        foreach ($items as $item) {
            tacticum_offer_catalog_add_option($options, 'sectors', (string)$item['sector_key'], (string)$item['sector']);
            tacticum_offer_catalog_add_option($options, 'scenarios', (string)$item['scenario_key'], (string)$item['scenario']);
            tacticum_offer_catalog_add_option($options, 'phases', (string)$item['phase_key'], (string)$item['phase']);
        }

        foreach (tacticum_offer_catalog_budget_buckets() as $key => $bucket) {
            $options['budgets'][$key] = ['key' => $key, 'label' => $bucket['label'], 'count' => 0];
        }
        foreach ($items as $item) {
            $budgetKey = (string)($item['budget_bucket'] ?? '');
            if ($budgetKey !== '' && isset($options['budgets'][$budgetKey])) {
                $options['budgets'][$budgetKey]['count']++;
            }
        }

        foreach (['sectors', 'scenarios', 'phases'] as $group) {
            uasort($options[$group], static fn(array $a, array $b): int => strcasecmp($a['label'], $b['label']));
            $options[$group] = array_values($options[$group]);
        }
        $options['budgets'] = array_values(array_filter(
            $options['budgets'],
            static fn(array $option): bool => (int)$option['count'] > 0
        ));

        return $options;
    }
}

if (!function_exists('tacticum_offer_catalog_url')) {
    function tacticum_offer_catalog_url(array $filters, array $overrides = []): string
    {
        $next = tacticum_offer_catalog_normalize_filters(array_merge($filters, $overrides));
        $pathSegments = [];
        foreach (['sector', 'scenario', 'budget', 'phase'] as $key) {
            $value = trim((string)($next[$key] ?? ''));
            if ($value !== '') {
                $pathSegments[] = $key;
                $pathSegments[] = rawurlencode($value);
            }
        }
        if ((int)($next['page'] ?? 1) > 1) {
            $pathSegments[] = 'page';
            $pathSegments[] = (string)(int)$next['page'];
        }

        $params = [];
        $q = trim((string)($next['q'] ?? ''));
        if ($q !== '') {
            $params['q'] = $q;
        }
        if (($next['sort'] ?? 'new') !== 'new') {
            $params['sort'] = (string)$next['sort'];
        }
        $path = $pathSegments === []
            ? '/offer/'
            : '/offer/catalog/' . implode('/', $pathSegments) . '/';

        return $path . ($params !== [] ? '?' . http_build_query($params) : '');
    }
}

if (!function_exists('tacticum_offer_catalog_pagination_range')) {
    function tacticum_offer_catalog_pagination_range(int $currentPage, int $totalPages): array
    {
        if ($totalPages <= 7) {
            return range(1, $totalPages);
        }

        $pages = [1];
        for ($page = max(2, $currentPage - 1); $page <= min($totalPages - 1, $currentPage + 1); $page++) {
            $pages[] = $page;
        }
        $pages[] = $totalPages;
        $pages = array_values(array_unique($pages));
        sort($pages);

        $range = [];
        $previous = 0;
        foreach ($pages as $page) {
            if ($previous > 0 && $page - $previous > 1) {
                $range[] = 'ellipsis';
            }
            $range[] = $page;
            $previous = $page;
        }

        return $range;
    }
}

if (!function_exists('tacticum_offer_catalog_prepare')) {
    function tacticum_offer_catalog_prepare(int $iblockId, array $filters, int $perPage = 24): array
    {
        return TacticumOfferCatalogService::prepare($iblockId, $filters, $perPage);
    }
}
