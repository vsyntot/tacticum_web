<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

$tacticumIncludes = [
    '/local/rest/rest_helpers.php',
    '/local/php_interface/include/content_helpers.php',
    '/local/php_interface/include/content_migrations.php',
    '/local/php_interface/include/site_helpers.php',
    '/local/php_interface/include/component_helpers.php',
    '/local/php_interface/include/seo_helpers.php',
    '/local/php_interface/include/product_content.php',
    '/local/php_interface/include/product_page.php',
    '/local/php_interface/include/offer_catalog_cache.php',
    '/local/php_interface/include/calcrequests_rest.php',
];

foreach ($tacticumIncludes as $relativePath) {
    $path = $_SERVER['DOCUMENT_ROOT'] . $relativePath;
    if (file_exists($path)) {
        require_once $path;
    }
}

if (function_exists('tacticum_register_offer_catalog_cache_handlers')) {
    tacticum_register_offer_catalog_cache_handlers();
}

if (function_exists('tacticum_register_product_content_cache_handlers')) {
    tacticum_register_product_content_cache_handlers();
}

if (function_exists('tacticum_register_calcrequests_rest')) {
    tacticum_register_calcrequests_rest();
}
