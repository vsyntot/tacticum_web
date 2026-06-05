<?php

declare(strict_types=1);

namespace Tacticum\Rest;

use Bitrix\Main\Data\Cache;

final class RateLimiter
{
    public static function hit(string $action, int $limit = 20, int $ttl = 60): void
    {
        $ip = Security::clientIp();
        if ($ip === '') {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        $sessid = bitrix_sessid();
        $cacheKey = 'tacticum_rest_' . $action . '_' . md5($ip . '|' . $sessid);
        $cacheDir = '/tacticum/rest_rate';
        $cache = Cache::createInstance();

        $count = 1;
        if ($cache->initCache($ttl, $cacheKey, $cacheDir)) {
            $data = $cache->getVars();
            $count = ((int)($data['count'] ?? 0)) + 1;
        }

        $cache->clean($cacheKey, $cacheDir);
        if ($cache->startDataCache($ttl, $cacheKey, $cacheDir)) {
            $cache->endDataCache(['count' => $count, 'ts' => time()]);
        }

        if ($count > $limit) {
            Response::error(429, 'rate_limited', 'Слишком много запросов. Попробуйте позже.');
        }
    }

    public static function classes(): array
    {
        $classes = self::defaultClasses();
        $policyPath = dirname(__DIR__, 3) . '/rest/endpoint_policy.json';
        if (!is_file($policyPath) || !is_readable($policyPath)) {
            return $classes;
        }

        $policy = json_decode((string)file_get_contents($policyPath), true);
        if (!is_array($policy) || !is_array($policy['risk_classes'] ?? null)) {
            return $classes;
        }

        foreach ($policy['risk_classes'] as $riskClass => $settings) {
            $riskClass = strtoupper(trim((string)$riskClass));
            if ($riskClass === '' || !is_array($settings)) {
                continue;
            }

            $limit = isset($settings['limit']) && is_numeric($settings['limit']) ? (int)$settings['limit'] : 0;
            $ttl = isset($settings['ttl']) && is_numeric($settings['ttl']) ? (int)$settings['ttl'] : 0;
            if ($limit <= 0 || $ttl <= 0) {
                continue;
            }

            $classes[$riskClass] = [
                'limit' => $limit,
                'ttl' => $ttl,
            ];
        }

        return $classes;
    }

    private static function defaultClasses(): array
    {
        return [
            'CONFIG_HEALTH_GET' => ['limit' => 5, 'ttl' => 60],
            'PUBLIC_LEAD_POST' => ['limit' => 20, 'ttl' => 60],
            'PUBLIC_CHAT_POST' => ['limit' => 20, 'ttl' => 60],
            'PUBLIC_STAFF_POST' => ['limit' => 20, 'ttl' => 60],
            'SCOPED_PREFILL_POST' => ['limit' => 20, 'ttl' => 60],
            'PUBLIC_RESOLVER_POST' => ['limit' => 20, 'ttl' => 60],
            'LEGACY_ALIAS_POST' => ['limit' => 20, 'ttl' => 60],
            'PRIVATE_PROOF_DOC' => ['limit' => 5, 'ttl' => 60],
            'INTERNAL_ADMIN_OR_INTEGRATION' => ['limit' => 10, 'ttl' => 60],
        ];
    }

    public static function classSettings(string $riskClass): array
    {
        $riskClass = strtoupper(trim($riskClass));
        $classes = self::classes();
        $settings = $classes[$riskClass] ?? $classes['PUBLIC_LEAD_POST'];
        $overrides = Config::section('rest')['rate_limits'] ?? [];
        $override = [];

        if (is_array($overrides)) {
            foreach ($overrides as $configuredRiskClass => $configuredSettings) {
                if (strtoupper(trim((string)$configuredRiskClass)) === $riskClass && is_array($configuredSettings)) {
                    $override = $configuredSettings;
                    break;
                }
            }
        }

        foreach (['limit', 'ttl'] as $key) {
            if (array_key_exists($key, $override) && is_numeric($override[$key])) {
                $settings[$key] = (int)$override[$key];
            }
            if ((int)$settings[$key] <= 0) {
                $settings[$key] = $classes[$riskClass][$key] ?? $classes['PUBLIC_LEAD_POST'][$key];
            }
        }

        return $settings;
    }

    public static function byClass(string $riskClass, string $action): void
    {
        $riskClass = strtoupper(trim($riskClass));
        $action = trim($action);
        $settings = self::classSettings($riskClass);
        self::hit($action !== '' ? $action : strtolower($riskClass), (int)$settings['limit'], (int)$settings['ttl']);
    }
}
