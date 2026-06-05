<?php

use Tacticum\Seo\{FaqSchema, JsonLd, Meta};

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_json_ld_text')) {
    function tacticum_json_ld_text(string $text): string { return JsonLd::text($text); }
}

if (!function_exists('tacticum_add_json_ld')) {
    function tacticum_add_json_ld(array $data, string $key = ''): void { JsonLd::add($data, $key); }
}

if (!function_exists('tacticum_default_json_ld_graph')) {
    function tacticum_default_json_ld_graph(string $canonicalUrl, string $canonicalPath, string $title): array
    {
        return JsonLd::defaultGraph($canonicalUrl, $canonicalPath, $title);
    }
}

if (!function_exists('tacticum_normalize_json_ld_items')) {
    function tacticum_normalize_json_ld_items(array $items): array { return JsonLd::normalizeItems($items); }
}

if (!function_exists('tacticum_add_robots_meta')) {
    function tacticum_add_robots_meta(string $robots): void { Meta::addRobots($robots); }
}

if (!function_exists('tacticum_faq_json_ld')) {
    function tacticum_faq_json_ld(int $limit = 20): ?array { return FaqSchema::build($limit); }
}

if (!function_exists('tacticum_apply_seo_defaults')) {
    function tacticum_apply_seo_defaults(?string $canonicalPath = null, array $options = []): void
    {
        Meta::applyDefaults($canonicalPath, $options);
    }
}
