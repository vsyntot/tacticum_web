<?php

declare(strict_types=1);

namespace Tacticum\Rest;

use Bitrix\Main\Config\Configuration;

final class Config
{
    private static ?array $config = null;

    public static function all(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        self::$config = [];
        $configPath = (string)($_SERVER['DOCUMENT_ROOT'] ?? '') . '/local/php_interface/include/tacticum_config.php';
        if ($configPath !== '' && is_file($configPath)) {
            $loaded = include $configPath;
            if (is_array($loaded)) {
                self::$config = $loaded;
            }
        }

        return self::$config;
    }

    public static function sectionDefaults(string $section): array
    {
        return match ($section) {
            'page_content' => [
                'source' => 'fallback',
                'live_status' => 'live',
                'allow_fallback' => true,
            ],
            'price' => [
                'team_presets_source' => 'fallback',
                'team_presets_cache_ttl' => 300,
                'allow_team_presets_fallback' => true,
            ],
            'offer' => [
                'taxonomy_source' => 'fallback',
                'taxonomy_cache_ttl' => 300,
                'allow_taxonomy_fallback' => true,
            ],
            'content' => [
                'faq_section_fallback_ids' => [
                    'home' => 17,
                    'main' => 17,
                    'aiagents' => 18,
                    'calculator' => 19,
                    'offer' => 19,
                    'services' => 20,
                    'price' => 21,
                ],
            ],
            default => [],
        };
    }

    public static function section(string $section): array
    {
        $config = self::all();
        $sectionData = $config[$section] ?? [];
        if (!is_array($sectionData)) {
            $sectionData = [];
        }

        return array_replace_recursive(self::sectionDefaults($section), $sectionData);
    }

    public static function iblockId(string $key, int $default = 0): int
    {
        $iblocks = self::section('iblocks');
        return array_key_exists($key, $iblocks) ? (int)$iblocks[$key] : $default;
    }

    public static function aiSetting(string $key, string $default = ''): string
    {
        $baseUrls = self::section('base_urls');
        if (isset($baseUrls[$key]) && $baseUrls[$key] !== '') {
            return (string)$baseUrls[$key];
        }

        $config = Configuration::getValue('ai_services');
        if (is_array($config) && isset($config[$key]) && $config[$key] !== '') {
            return (string)$config[$key];
        }

        return $default;
    }

    public static function requiredHttpsAiUrl(string $key, string $serviceLabel = 'сервиса обработки'): string
    {
        $url = trim(self::aiSetting($key));
        if ($url === '') {
            Response::error(500, 'config_error', 'Не настроен адрес ' . $serviceLabel . '.');
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            Response::error(500, 'config_error', 'Некорректный адрес ' . $serviceLabel . '.');
        }

        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        if ($scheme !== 'https') {
            Response::error(500, 'config_error', 'Адрес ' . $serviceLabel . ' должен использовать HTTPS.');
        }

        return $url;
    }

    public static function aiEndpointPath(string $key, string $default): string
    {
        $ai = self::section('ai');
        $endpointPaths = $ai['endpoint_paths'] ?? [];
        $path = is_array($endpointPaths) && isset($endpointPaths[$key])
            ? trim((string)$endpointPaths[$key])
            : trim($default);

        if ($path === '') {
            return '/' . ltrim($default, '/');
        }

        if (strpos($path, '://') !== false || strpos($path, '//') === 0) {
            Response::error(500, 'config_error', 'AI endpoint path должен быть относительным путём без host.');
        }
        if ($path[0] !== '/') {
            Response::error(500, 'config_error', 'AI endpoint path должен начинаться с /.');
        }

        return $path;
    }

    public static function buildUrl(string $baseUrl, string $path): string
    {
        if ($baseUrl === '') {
            return $path;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}
