<?php

namespace Tacticum\Offer\Page;

final class Resolver
{
    public static function resolve(
        ?array $request = null,
        ?array $query = null,
        ?string $requestUri = null,
        ?string $queryString = null
    ): array {
        $snapshot = RequestSnapshot::current();
        if ($request !== null || $query !== null || $requestUri !== null || $queryString !== null) {
            $snapshot = RequestSnapshot::fromArrays(
                $request ?? $snapshot['request'],
                $query ?? $snapshot['query'],
                $requestUri ?? $snapshot['request_uri'],
                $queryString ?? $snapshot['query_string']
            );
        }
        $request = $snapshot['request'];
        $query = $snapshot['query'];
        $queryString = $snapshot['query_string'];

        $currentPath = Query::currentPath((string)$snapshot['request_uri']);
        $isCatalogPath = function_exists('tacticum_offer_catalog_is_catalog_path')
            && tacticum_offer_catalog_is_catalog_path($currentPath);
        $pathCode = '';
        if (!$isCatalogPath && preg_match('#^/offer/([A-Za-z0-9_-]+)/?$#', $currentPath, $matches)) {
            $pathCode = Query::requestCode($matches[1] ?? '');
        }

        $offerId = (int)($request['ID'] ?? 0);
        $offerCode = $pathCode !== ''
            ? $pathCode
            : Query::requestCode($request['CODE'] ?? '');
        $isDetailRequest = !$isCatalogPath && ($offerId > 0 || $offerCode !== '');
        $pathFilters = function_exists('tacticum_offer_catalog_path_filters')
            ? tacticum_offer_catalog_path_filters($currentPath)
            : [];
        $filters = function_exists('tacticum_offer_catalog_normalize_filters')
            ? tacticum_offer_catalog_normalize_filters($query, $pathFilters)
            : [];
        $serviceParams = Query::serviceParams($query);
        $state = [
            'mode' => 'list',
            'current_path' => $currentPath,
            'is_catalog_path' => $isCatalogPath,
            'is_detail_request' => $isDetailRequest,
            'offer_id' => $offerId,
            'offer_code' => $offerCode,
            'element' => null,
            'canonical_path' => '/offer/',
            'filters' => $filters,
            'service_params' => $serviceParams,
            'redirect' => null,
            'per_page' => 24,
        ];

        if ($isDetailRequest) {
            return self::resolveDetail($state, $offerId, $offerCode, $currentPath);
        }

        return self::resolveCatalog($state, $filters, $query, $serviceParams, $currentPath, $queryString);
    }

    private static function resolveDetail(array $state, int $offerId, string $offerCode, string $currentPath): array
    {
        $element = function_exists('tacticum_offer_find_element')
            ? tacticum_offer_find_element($offerId, $offerCode)
            : null;
        if ($element === null) {
            $state['mode'] = 'not_found';
            return $state;
        }

        $canonicalPath = tacticum_offer_detail_path((string)$element['CODE']);
        $state['mode'] = 'detail';
        $state['element'] = $element;
        $state['canonical_path'] = $canonicalPath;

        if ($offerId > 0 || $currentPath !== $canonicalPath) {
            $state['redirect'] = [
                'url' => $canonicalPath,
                'status' => '301 Moved Permanently',
            ];
        }

        return $state;
    }

    private static function resolveCatalog(
        array $state,
        array $filters,
        array $query,
        array $serviceParams,
        string $currentPath,
        string $queryString
    ): array {
        if (!function_exists('tacticum_offer_catalog_url')) {
            return $state;
        }

        $catalogUrl = Query::appendServiceParams(tacticum_offer_catalog_url($filters), $serviceParams);
        $currentCatalogUrl = $currentPath . ($queryString !== '' ? '?' . $queryString : '');
        $hasSemanticQuery = Query::hasSemanticQuery($query);
        $hasFilters = function_exists('tacticum_offer_catalog_has_filters')
            && tacticum_offer_catalog_has_filters($filters, true);

        if ($state['is_catalog_path'] && !$hasFilters) {
            $state['redirect'] = [
                'url' => Query::appendServiceParams('/offer/', $serviceParams),
                'status' => '301 Moved Permanently',
            ];
            return $state;
        }

        if (!$state['is_catalog_path'] && $hasSemanticQuery && rtrim($currentPath, '/') === '/offer' && $currentCatalogUrl !== $catalogUrl) {
            $state['redirect'] = [
                'url' => $catalogUrl,
                'status' => '302 Found',
            ];
            return $state;
        }

        if ($state['is_catalog_path'] && $hasFilters && $currentCatalogUrl !== $catalogUrl) {
            $state['redirect'] = [
                'url' => $catalogUrl,
                'status' => '302 Found',
            ];
            return $state;
        }

        if (
            !$state['is_catalog_path']
            && function_exists('tacticum_offer_catalog_has_pretty_segments')
            && tacticum_offer_catalog_has_pretty_segments($filters)
        ) {
            $state['redirect'] = [
                'url' => $catalogUrl,
                'status' => '302 Found',
            ];
        }

        return $state;
    }
}
