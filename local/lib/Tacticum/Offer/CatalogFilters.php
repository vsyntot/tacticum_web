<?php

namespace Tacticum\Offer;

final class CatalogFilters
{
    public static function cleanKey(mixed $value): string
    {
        if (is_array($value)) {
            return '';
        }

        $value = trim((string)$value);
        return preg_match('/^[a-z0-9_-]{1,80}$/', $value) ? $value : '';
    }

    public static function isCatalogPath(string $path): bool
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        return preg_match('#^/offer/catalog(?:/|$)#', $path) === 1;
    }

    public static function pathFilters(string $path): array
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        if (!self::isCatalogPath($path)) {
            return [];
        }

        $segments = array_values(array_filter(
            explode('/', trim(preg_replace('#^/offer/catalog/?#', '', $path) ?: '', '/')),
            static fn(string $segment): bool => $segment !== ''
        ));
        $filters = [];
        for ($index = 0, $count = count($segments); $index < $count; $index += 2) {
            $key = strtolower(rawurldecode((string)$segments[$index]));
            $value = rawurldecode((string)($segments[$index + 1] ?? ''));
            if (in_array($key, ['sector', 'scenario', 'budget', 'phase'], true)) {
                $cleanValue = self::cleanKey($value);
                if ($cleanValue !== '') {
                    $filters[$key] = $cleanValue;
                }
            } elseif ($key === 'page' && filter_var($value, FILTER_VALIDATE_INT) !== false) {
                $filters['page'] = max(1, (int)$value);
            }
        }
        return $filters;
    }

    public static function hasPrettySegments(array $filters): bool
    {
        foreach (['sector', 'scenario', 'budget', 'phase'] as $key) {
            if (trim((string)($filters[$key] ?? '')) !== '') {
                return true;
            }
        }
        return (int)($filters['page'] ?? 1) > 1;
    }

    public static function normalize(array $source, array $pathFilters = []): array
    {
        $source = $pathFilters !== [] ? array_merge($source, $pathFilters) : $source;
        $q = is_array($source['q'] ?? '') ? '' : CatalogMapper::trim((string)($source['q'] ?? ''));
        $sort = self::cleanKey($source['sort'] ?? '');
        $pageRaw = $source['page'] ?? 1;
        $page = is_array($pageRaw) || filter_var($pageRaw, FILTER_VALIDATE_INT) === false ? 1 : (int)$pageRaw;

        return [
            'q' => mb_substr($q, 0, 80),
            'sector' => self::cleanKey($source['sector'] ?? ''),
            'scenario' => self::cleanKey($source['scenario'] ?? ''),
            'budget' => self::cleanKey($source['budget'] ?? ''),
            'phase' => self::cleanKey($source['phase'] ?? ''),
            'sort' => in_array($sort, ['new', 'budget-desc', 'budget-asc'], true) ? $sort : 'new',
            'page' => max(1, $page),
        ];
    }

    public static function hasFilters(array $filters, bool $includePage = false): bool
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

    public static function filterItems(array $items, array $filters): array
    {
        $q = mb_strtolower(CatalogMapper::trim((string)($filters['q'] ?? '')));
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

        usort($filtered, static function (array $a, array $b) use ($filters): int {
            $sort = (string)($filters['sort'] ?? 'new');
            if ($sort === 'budget-desc') {
                return ((int)($b['budget_amount'] ?? 0)) <=> ((int)($a['budget_amount'] ?? 0));
            }
            if ($sort === 'budget-asc') {
                return ((int)($a['budget_amount'] ?? 0)) <=> ((int)($b['budget_amount'] ?? 0));
            }
            $dateCompare = ((int)($b['date_sort'] ?? 0)) <=> ((int)($a['date_sort'] ?? 0));

            return $dateCompare !== 0 ? $dateCompare : ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
        });

        return $filtered;
    }

    public static function options(array $items): array
    {
        $options = ['sectors' => [], 'scenarios' => [], 'phases' => [], 'budgets' => []];
        foreach ($items as $item) {
            self::addOption($options, 'sectors', (string)$item['sector_key'], (string)$item['sector']);
            self::addOption($options, 'scenarios', (string)$item['scenario_key'], (string)$item['scenario']);
            self::addOption($options, 'phases', (string)$item['phase_key'], (string)$item['phase']);
        }
        foreach (CatalogTaxonomy::budgetBuckets() as $key => $bucket) {
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
        $options['budgets'] = array_values(array_filter($options['budgets'], static fn(array $option): bool => (int)$option['count'] > 0));

        return $options;
    }

    public static function featuredOptions(array $options, string $group): array
    {
        return CatalogTaxonomy::featuredOptions($options, $group);
    }

    public static function addOption(array &$options, string $group, string $key, string $label): void
    {
        if ($key === '' || $label === '') {
            return;
        }
        if (!isset($options[$group][$key])) {
            $options[$group][$key] = ['key' => $key, 'label' => $label, 'count' => 0];
        }
        $options[$group][$key]['count']++;
    }

    public static function url(array $filters, array $overrides = []): string
    {
        $next = self::normalize(array_merge($filters, $overrides));
        $segments = [];
        foreach (['sector', 'scenario', 'budget', 'phase'] as $key) {
            $value = trim((string)($next[$key] ?? ''));
            if ($value !== '') {
                $segments[] = $key;
                $segments[] = rawurlencode($value);
            }
        }
        if ((int)($next['page'] ?? 1) > 1) {
            $segments[] = 'page';
            $segments[] = (string)(int)$next['page'];
        }

        $params = [];
        $q = trim((string)($next['q'] ?? ''));
        if ($q !== '') {
            $params['q'] = $q;
        }
        if (($next['sort'] ?? 'new') !== 'new') {
            $params['sort'] = (string)$next['sort'];
        }
        $path = $segments === [] ? '/offer/' : '/offer/catalog/' . implode('/', $segments) . '/';

        return $path . ($params !== [] ? '?' . http_build_query($params) : '');
    }

    public static function paginationRange(int $currentPage, int $totalPages): array
    {
        if ($totalPages <= 7) {
            return range(1, $totalPages);
        }

        $pages = [1, $totalPages];
        for ($page = max(2, $currentPage - 1); $page <= min($totalPages - 1, $currentPage + 1); $page++) {
            $pages[] = $page;
        }
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
