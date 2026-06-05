<?php

namespace Tacticum\Offer\Page;

final class Query
{
    public static function catalogQueryKeys(): array
    {
        return ['q', 'sector', 'scenario', 'budget', 'phase', 'sort', 'page'];
    }

    public static function serviceQueryKeys(): array
    {
        return [
            'clear_cache',
            'clear_cache_session',
            'bitrix_include_areas',
            'bitrix_show_mode',
            'show_page_exec_time',
            'show_include_exec_time',
            'show_sql_stat',
            'show_cache_stat',
        ];
    }

    public static function currentPath(?string $requestUri = null): string
    {
        $requestUri = $requestUri ?? RequestSnapshot::currentRequestUri();
        $path = parse_url($requestUri, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : '/';
    }

    public static function hasSemanticQuery(array $query): bool
    {
        foreach (self::catalogQueryKeys() as $queryKey) {
            if (array_key_exists($queryKey, $query)) {
                return true;
            }
        }

        return false;
    }

    public static function serviceParams(array $query): array
    {
        $params = [];
        foreach (self::serviceQueryKeys() as $queryKey) {
            $value = $query[$queryKey] ?? null;
            if ($value === null || is_array($value)) {
                continue;
            }
            $params[$queryKey] = (string)$value;
        }

        return $params;
    }

    public static function appendServiceParams(string $url, array $serviceParams): string
    {
        if ($serviceParams === []) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($serviceParams);
    }

    public static function requestCode(mixed $value): string
    {
        if (is_array($value)) {
            return '';
        }

        return trim(rawurldecode((string)$value));
    }
}
