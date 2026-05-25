<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_iblock_id')) {
    function tacticum_iblock_id(string $key): int
    {
        if (function_exists('tacticum_rest_get_iblock_id')) {
            return tacticum_rest_get_iblock_id($key);
        }

        return 0;
    }
}

if (!function_exists('tacticum_public_url')) {
    function tacticum_public_url(string $path = '/'): string
    {
        $path = trim($path);
        if ($path === '') {
            $path = '/';
        }

        if (filter_var($path, FILTER_VALIDATE_URL) !== false) {
            return $path;
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return 'https://tacticum.ru' . $path;
    }
}

if (!function_exists('tacticum_offer_detail_path')) {
    function tacticum_offer_detail_path(string $code): string
    {
        $code = trim($code, "/ \t\n\r\0\x0B");
        if ($code === '') {
            return '/offer/';
        }

        return '/offer/' . rawurlencode($code) . '/';
    }
}

if (!function_exists('tacticum_offer_detail_url')) {
    function tacticum_offer_detail_url(string $code): string
    {
        return tacticum_public_url(tacticum_offer_detail_path($code));
    }
}
