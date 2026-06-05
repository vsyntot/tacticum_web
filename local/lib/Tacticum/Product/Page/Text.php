<?php

namespace Tacticum\Product\Page;

final class Text
{
    public static function string(array $data, string $key, string $default = ''): string
    {
        $value = $data[$key] ?? $default;

        return is_scalar($value) ? trim((string)$value) : $default;
    }

    public static function html(mixed $value): string
    {
        return htmlspecialcharsbx((string)$value);
    }

    public static function canonicalPath(string $canonicalPath): string
    {
        $path = trim($canonicalPath);
        if ($path === '') {
            return '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return str_ends_with($path, '/') ? $path : $path . '/';
    }

    public static function safeHref(mixed $value, string $default = '#'): string
    {
        $href = is_scalar($value) ? trim((string)$value) : '';
        if (self::isSafeHref($href)) {
            return $href;
        }

        $fallback = trim($default);
        if ($fallback === '') {
            return '';
        }

        return self::isSafeHref($fallback) ? $fallback : '#';
    }

    public static function isSafeHref(string $href): bool
    {
        if ($href === '' || preg_match('/[\x00-\x1F\x7F]/', $href)) {
            return false;
        }

        if (str_starts_with($href, 'https://') || str_starts_with($href, '#')) {
            return true;
        }

        return str_starts_with($href, '/')
            && !str_starts_with($href, '//')
            && !str_starts_with($href, '/\\');
    }

    public static function iconClass(mixed $value, string $default = ''): string
    {
        $icon = is_scalar($value) ? trim((string)$value) : '';
        if (preg_match('/^ri-[a-z0-9]+(?:-[a-z0-9]+)*$/', $icon)) {
            return $icon;
        }

        $fallback = trim($default);
        if ($fallback !== '' && preg_match('/^ri-[a-z0-9]+(?:-[a-z0-9]+)*$/', $fallback)) {
            return $fallback;
        }

        return '';
    }

    public static function columnsClass(mixed $value, string $default = 'lg:grid-cols-3'): string
    {
        $allowed = ['lg:grid-cols-2', 'lg:grid-cols-3', 'lg:grid-cols-4'];
        $class = is_scalar($value) ? trim((string)$value) : '';
        if (in_array($class, $allowed, true)) {
            return $class;
        }

        $fallback = trim($default);

        return in_array($fallback, $allowed, true) ? $fallback : 'lg:grid-cols-3';
    }

    public static function contextSlug(mixed $value, string $default = ''): string
    {
        if (!is_scalar($value)) {
            return $default;
        }

        $normalized = strtolower(trim((string)$value));
        $normalized = preg_replace('/[^a-z0-9_.-]+/', '-', $normalized) ?: '';
        $normalized = trim($normalized, '-_.');
        if ($normalized === '') {
            return $default;
        }

        return mb_substr($normalized, 0, 80);
    }

    public static function schemaText(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        if (function_exists('tacticum_json_ld_text')) {
            return tacticum_json_ld_text((string)$value);
        }

        return trim(strip_tags(html_entity_decode((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
    }
}
