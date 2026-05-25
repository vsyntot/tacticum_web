<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

$offerCatalogHelper = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/offer_catalog.php';
if (file_exists($offerCatalogHelper)) {
    require_once $offerCatalogHelper;
}

if (!function_exists('tacticum_offer_page_catalog_query_keys')) {
    function tacticum_offer_page_catalog_query_keys(): array
    {
        return ['q', 'sector', 'scenario', 'budget', 'phase', 'sort', 'page'];
    }
}

if (!function_exists('tacticum_offer_page_service_query_keys')) {
    function tacticum_offer_page_service_query_keys(): array
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
}

if (!function_exists('tacticum_offer_page_current_path')) {
    function tacticum_offer_page_current_path(?string $requestUri = null): string
    {
        $requestUri = $requestUri ?? (string)($_SERVER['REQUEST_URI'] ?? '');
        $path = parse_url($requestUri, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : '/';
    }
}

if (!function_exists('tacticum_offer_page_has_semantic_query')) {
    function tacticum_offer_page_has_semantic_query(array $query): bool
    {
        foreach (tacticum_offer_page_catalog_query_keys() as $queryKey) {
            if (array_key_exists($queryKey, $query)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('tacticum_offer_page_service_params')) {
    function tacticum_offer_page_service_params(array $query): array
    {
        $params = [];
        foreach (tacticum_offer_page_service_query_keys() as $queryKey) {
            $value = $query[$queryKey] ?? null;
            if ($value === null || is_array($value)) {
                continue;
            }
            $params[$queryKey] = (string)$value;
        }

        return $params;
    }
}

if (!function_exists('tacticum_offer_page_append_service_params')) {
    function tacticum_offer_page_append_service_params(string $url, array $serviceParams): string
    {
        if ($serviceParams === []) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($serviceParams);
    }
}

if (!function_exists('tacticum_offer_page_request_code')) {
    function tacticum_offer_page_request_code(mixed $value): string
    {
        if (is_array($value)) {
            return '';
        }

        return trim(rawurldecode((string)$value));
    }
}

if (!function_exists('tacticum_offer_page_resolve')) {
    function tacticum_offer_page_resolve(
        ?array $request = null,
        ?array $query = null,
        ?string $requestUri = null,
        ?string $queryString = null
    ): array {
        $request = $request ?? (array)$_REQUEST;
        $query = $query ?? (array)$_GET;
        $queryString = $queryString ?? (string)($_SERVER['QUERY_STRING'] ?? '');

        $currentPath = tacticum_offer_page_current_path($requestUri);
        $isCatalogPath = function_exists('tacticum_offer_catalog_is_catalog_path')
            && tacticum_offer_catalog_is_catalog_path($currentPath);
        $pathCode = '';
        if (!$isCatalogPath && preg_match('#^/offer/([A-Za-z0-9_-]+)/?$#', $currentPath, $matches)) {
            $pathCode = tacticum_offer_page_request_code($matches[1] ?? '');
        }

        $offerId = (int)($request['ID'] ?? 0);
        $offerCode = $pathCode !== ''
            ? $pathCode
            : tacticum_offer_page_request_code($request['CODE'] ?? '');
        $isDetailRequest = !$isCatalogPath && ($offerId > 0 || $offerCode !== '');
        $pathFilters = function_exists('tacticum_offer_catalog_path_filters')
            ? tacticum_offer_catalog_path_filters($currentPath)
            : [];
        $filters = function_exists('tacticum_offer_catalog_normalize_filters')
            ? tacticum_offer_catalog_normalize_filters($query, $pathFilters)
            : [];
        $serviceParams = tacticum_offer_page_service_params($query);
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

        if (!function_exists('tacticum_offer_catalog_url')) {
            return $state;
        }

        $catalogUrl = tacticum_offer_page_append_service_params(
            tacticum_offer_catalog_url($filters),
            $serviceParams
        );
        $currentCatalogUrl = $currentPath . ($queryString !== '' ? '?' . $queryString : '');
        $hasSemanticQuery = tacticum_offer_page_has_semantic_query($query);

        if (
            $isCatalogPath
            && function_exists('tacticum_offer_catalog_has_filters')
            && !tacticum_offer_catalog_has_filters($filters, true)
        ) {
            $state['redirect'] = [
                'url' => tacticum_offer_page_append_service_params('/offer/', $serviceParams),
                'status' => '301 Moved Permanently',
            ];
            return $state;
        }

        if (!$isCatalogPath && $hasSemanticQuery && rtrim($currentPath, '/') === '/offer' && $currentCatalogUrl !== $catalogUrl) {
            $state['redirect'] = [
                'url' => $catalogUrl,
                'status' => '302 Found',
            ];
            return $state;
        }

        if (
            $isCatalogPath
            && function_exists('tacticum_offer_catalog_has_filters')
            && tacticum_offer_catalog_has_filters($filters, true)
            && $currentCatalogUrl !== $catalogUrl
        ) {
            $state['redirect'] = [
                'url' => $catalogUrl,
                'status' => '302 Found',
            ];
            return $state;
        }

        if (
            !$isCatalogPath
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

if (!function_exists('tacticum_offer_page_apply_redirects')) {
    function tacticum_offer_page_apply_redirects(array $state): void
    {
        $redirect = $state['redirect'] ?? null;
        if (!is_array($redirect)) {
            return;
        }

        $url = (string)($redirect['url'] ?? '');
        if ($url === '') {
            return;
        }

        LocalRedirect($url, true, (string)($redirect['status'] ?? '302 Found'));
    }
}

if (!function_exists('tacticum_offer_page_apply_seo')) {
    function tacticum_offer_page_apply_seo(array $state): void
    {
        global $APPLICATION;

        $mode = (string)($state['mode'] ?? 'list');
        if ($mode === 'not_found') {
            CHTTP::SetStatus('404 Not Found');
            @define('ERROR_404', 'Y');
            $APPLICATION->SetTitle('Предложение не найдено - Тактикум');
            $APPLICATION->SetPageProperty('description', 'Запрошенный пример расчета не найден или больше недоступен.');
            tacticum_add_robots_meta('noindex,nofollow');
            return;
        }

        if ($mode === 'detail') {
            $element = is_array($state['element'] ?? null) ? $state['element'] : [];
            $APPLICATION->SetTitle((string)($element['SEO_TITLE'] ?? 'Пример расчета проекта - Тактикум'));
            $APPLICATION->SetPageProperty(
                'description',
                (string)($element['SEO_DESCRIPTION'] ?? 'Пример расчета AI-проекта Tacticum: состав работ, команда, сроки и бюджет.')
            );
            if (!empty($element['KEYWORDS']) && is_array($element['KEYWORDS'])) {
                $APPLICATION->SetPageProperty('keywords', implode(', ', $element['KEYWORDS']));
            }
            tacticum_apply_seo_defaults((string)($state['canonical_path'] ?? '/offer/'), ['type' => 'article']);
            return;
        }

        $APPLICATION->SetTitle('Примеры расчетов AI- и IT-проектов по отраслям - Тактикум');
        $APPLICATION->SetPageProperty(
            'description',
            'Каталог примеров оценки AI- и IT-проектов по отраслям и сценариям: команда, сроки, стек, бюджет и переход к персональной смете.'
        );
        $seoOptions = [
            'type' => 'website',
            'schema' => [
                [
                    '@type' => 'CollectionPage',
                    'name' => 'Примеры расчетов AI- и IT-проектов по отраслям',
                    'description' => 'Каталог примеров оценки AI- и IT-проектов по отраслям, задачам, срокам, бюджету и команде.',
                    'url' => tacticum_public_url('/offer/'),
                ],
            ],
        ];
        $filters = is_array($state['filters'] ?? null) ? $state['filters'] : [];
        if (function_exists('tacticum_offer_catalog_has_filters') && tacticum_offer_catalog_has_filters($filters, true)) {
            $seoOptions['robots'] = 'noindex,follow';
        }
        tacticum_apply_seo_defaults('/offer/', $seoOptions);
    }
}

if (!function_exists('tacticum_offer_page_apply_template')) {
    function tacticum_offer_page_apply_template(array $state): void
    {
        global $APPLICATION;

        $APPLICATION->SetPageProperty('tacticum_page_assets', 'faq');
        $APPLICATION->SetPageProperty('tacticum_body_class', 'bg-gray-50');
    }
}

if (!function_exists('tacticum_offer_page_component_params')) {
    function tacticum_offer_page_component_params(array $state): array
    {
        return [
            'IBLOCK_ID' => tacticum_iblock_id('offer'),
            'MODE' => (string)($state['mode'] ?? 'list'),
            'FILTERS' => is_array($state['filters'] ?? null) ? $state['filters'] : [],
            'ELEMENT' => is_array($state['element'] ?? null) ? $state['element'] : [],
            'PER_PAGE' => (int)($state['per_page'] ?? 24),
        ];
    }
}
