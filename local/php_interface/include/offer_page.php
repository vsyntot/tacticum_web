<?php

use Tacticum\Offer\Page\{Query, Resolver, Response};

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

$offerCatalogHelper = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/offer_catalog.php';
if (file_exists($offerCatalogHelper)) {
    require_once $offerCatalogHelper;
}
unset($offerCatalogHelper);

if (!function_exists('tacticum_offer_page_catalog_query_keys')) {
    function tacticum_offer_page_catalog_query_keys(): array { return Query::catalogQueryKeys(); }
}

if (!function_exists('tacticum_offer_page_service_query_keys')) {
    function tacticum_offer_page_service_query_keys(): array { return Query::serviceQueryKeys(); }
}

if (!function_exists('tacticum_offer_page_current_path')) {
    function tacticum_offer_page_current_path(?string $requestUri = null): string { return Query::currentPath($requestUri); }
}

if (!function_exists('tacticum_offer_page_has_semantic_query')) {
    function tacticum_offer_page_has_semantic_query(array $query): bool { return Query::hasSemanticQuery($query); }
}

if (!function_exists('tacticum_offer_page_service_params')) {
    function tacticum_offer_page_service_params(array $query): array { return Query::serviceParams($query); }
}

if (!function_exists('tacticum_offer_page_append_service_params')) {
    function tacticum_offer_page_append_service_params(string $url, array $serviceParams): string { return Query::appendServiceParams($url, $serviceParams); }
}

if (!function_exists('tacticum_offer_page_request_code')) {
    function tacticum_offer_page_request_code(mixed $value): string { return Query::requestCode($value); }
}

if (!function_exists('tacticum_offer_page_resolve')) {
    function tacticum_offer_page_resolve(
        ?array $request = null,
        ?array $query = null,
        ?string $requestUri = null,
        ?string $queryString = null
    ): array {
        return Resolver::resolve($request, $query, $requestUri, $queryString);
    }
}

if (!function_exists('tacticum_offer_page_apply_redirects')) {
    function tacticum_offer_page_apply_redirects(array $state): void { Response::applyRedirects($state); }
}

if (!function_exists('tacticum_offer_page_apply_seo')) {
    function tacticum_offer_page_apply_seo(array $state): void { Response::applySeo($state); }
}

if (!function_exists('tacticum_offer_page_apply_template')) {
    function tacticum_offer_page_apply_template(array $state): void { Response::applyTemplate($state); }
}

if (!function_exists('tacticum_offer_page_component_params')) {
    function tacticum_offer_page_component_params(array $state): array { return Response::componentParams($state); }
}
