<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class ConfigValidator
{
    public static function validate(array $scopes = ['api', 'ai', 'telegram', 'offer', 'rest']): array
    {
        $errors = [];
        $scopes = array_values(array_unique($scopes));
        $addError = static function (string $key, string $code) use (&$errors): void {
            $errors[] = ['key' => $key, 'code' => $code];
        };
        $checkIblock = static function (string $key) use ($addError): void {
            if (Config::iblockId($key) <= 0) {
                $addError('iblocks.' . $key, 'missing_or_invalid');
            }
        };
        $checkHttpsUrl = static function (string $key) use ($addError): void {
            $value = trim(Config::aiSetting($key));
            if ($value === '') {
                $addError('base_urls.' . $key, 'missing');
                return;
            }
            if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                $addError('base_urls.' . $key, 'invalid_url');
                return;
            }
            if (strtolower((string)parse_url($value, PHP_URL_SCHEME)) !== 'https') {
                $addError('base_urls.' . $key, 'https_required');
            }
        };
        $checkEndpointPath = static function (string $key) use ($addError): void {
            $endpointPaths = Config::section('ai')['endpoint_paths'] ?? [];
            if (!is_array($endpointPaths) || !array_key_exists($key, $endpointPaths)) {
                return;
            }

            $value = trim((string)$endpointPaths[$key]);
            if ($value === '') {
                $addError('ai.endpoint_paths.' . $key, 'missing');
                return;
            }
            if (strpos($value, '://') !== false || strpos($value, '//') === 0) {
                $addError('ai.endpoint_paths.' . $key, 'path_required');
                return;
            }
            if ($value[0] !== '/') {
                $addError('ai.endpoint_paths.' . $key, 'leading_slash_required');
                return;
            }
            if (mb_strlen($value) > 200) {
                $addError('ai.endpoint_paths.' . $key, 'too_long');
            }
        };

        if (in_array('api', $scopes, true)) {
            foreach (['cases', 'faq', 'rates', 'services'] as $key) {
                $checkIblock($key);
            }
            self::validateApi($addError);
        }
        if (in_array('offer', $scopes, true)) {
            $checkIblock('offer');
            OfferConfigValidator::validate($checkIblock, $addError);
        }
        if (in_array('content', $scopes, true)) {
            foreach (['vacancies', 'clients', 'feedback', 'team', 'policies', 'aiagents'] as $key) {
                $checkIblock($key);
            }
        }
        if (in_array('products', $scopes, true)) {
            self::validateProducts($checkIblock, $addError);
        }
        if (in_array('page_content', $scopes, true)) {
            self::validatePageContent($checkIblock, $addError);
        }
        if (in_array('price', $scopes, true)) {
            PriceConfigValidator::validate($checkIblock, $addError);
        }
        if (in_array('ai', $scopes, true)) {
            $checkHttpsUrl('AI_SERVICE_BASE_URL');
            $checkEndpointPath('chat_agent_sale');
            $checkEndpointPath('staff_sale');
        }
        if (in_array('security', $scopes, true)) {
            self::validateSecurity($addError);
        }
        if (in_array('telegram', $scopes, true)) {
            $checkHttpsUrl('TELEGRAM_RESOLVER_URL');
        }
        if (in_array('rest', $scopes, true)) {
            self::validateRest($addError);
        }

        return $errors;
    }

    private static function validateApi(callable $addError): void
    {
        $api = Config::section('api');
        $defaultTtl = $api['cache_ttl_default'] ?? null;
        if ($defaultTtl !== null && !is_numeric($defaultTtl)) {
            $addError('api.cache_ttl_default', 'invalid_type');
        }

        $ttlByAction = $api['cache_ttl'] ?? [];
        if ($ttlByAction !== [] && !is_array($ttlByAction)) {
            $addError('api.cache_ttl', 'invalid_type');
            return;
        }
        foreach ((array)$ttlByAction as $key => $ttl) {
            if (!is_numeric($ttl)) {
                $addError('api.cache_ttl.' . (string)$key, 'invalid_type');
            }
        }
    }

    private static function validateProducts(callable $checkIblock, callable $addError): void
    {
        foreach (['products', 'product_blocks', 'product_use_cases'] as $key) {
            $checkIblock($key);
        }

        $products = Config::section('products');
        $source = $products['source'] ?? 'bitrix';
        if (!is_string($source) || !in_array($source, ['auto', 'bitrix', 'fallback'], true)) {
            $addError('products.source', 'invalid_value');
        } elseif ($source !== 'bitrix') {
            $addError('products.source', 'must_be_bitrix');
        }
        if (($products['allow_fallback'] ?? false) !== false) {
            $addError('products.allow_fallback', 'must_be_false');
        }
        if (($products['cache_ttl'] ?? null) !== null && !is_numeric($products['cache_ttl'])) {
            $addError('products.cache_ttl', 'invalid_type');
        }
    }

    private static function validatePageContent(callable $checkIblock, callable $addError): void
    {
        foreach (['page_sections', 'page_blocks'] as $key) {
            $checkIblock($key);
        }

        $pageContent = Config::section('page_content');
        $source = $pageContent['source'] ?? 'fallback';
        if (!is_string($source) || !in_array($source, ['fallback', 'bitrix'], true)) {
            $addError('page_content.source', 'invalid_value');
        }

        $liveStatus = $pageContent['live_status'] ?? 'live';
        if (!is_string($liveStatus) || !in_array($liveStatus, ['live'], true)) {
            $addError('page_content.live_status', 'must_be_live');
        }

        if (($pageContent['allow_fallback'] ?? true) !== true) {
            $addError('page_content.allow_fallback', 'must_be_true');
        }
    }

    private static function validateSecurity(callable $addError): void
    {
        $cspMode = Config::section('security')['csp_mode'] ?? 'report-only';
        if (!is_string($cspMode) || !in_array($cspMode, ['report-only', 'enforce'], true)) {
            $addError('security.csp_mode', 'invalid_value');
        }
    }

    private static function validateRest(callable $addError): void
    {
        $rest = Config::section('rest');
        $allowedOrigins = $rest['allowed_origins'] ?? [];
        $allowNoOrigin = (bool)($rest['allow_no_origin'] ?? false);
        if (!$allowNoOrigin && (!is_array($allowedOrigins) || empty($allowedOrigins))) {
            $addError('rest.allowed_origins', 'missing');
        }
        if (isset($rest['allowed_ips']) && !is_array($rest['allowed_ips'])) {
            $addError('rest.allowed_ips', 'invalid_type');
        }
        if (isset($rest['trusted_proxies']) && !is_array($rest['trusted_proxies'])) {
            $addError('rest.trusted_proxies', 'invalid_type');
        }

        $rateLimits = $rest['rate_limits'] ?? [];
        if ($rateLimits !== [] && !is_array($rateLimits)) {
            $addError('rest.rate_limits', 'invalid_type');
            return;
        }
        foreach ((array)$rateLimits as $riskClass => $settings) {
            $riskClassKey = strtoupper(trim((string)$riskClass));
            if (!array_key_exists($riskClassKey, RateLimiter::classes())) {
                $addError('rest.rate_limits.' . (string)$riskClass, 'unknown_class');
                continue;
            }
            if (!is_array($settings)) {
                $addError('rest.rate_limits.' . (string)$riskClass, 'invalid_type');
                continue;
            }
            foreach (['limit', 'ttl'] as $key) {
                if (array_key_exists($key, $settings) && (!is_numeric($settings[$key]) || (int)$settings[$key] <= 0)) {
                    $addError('rest.rate_limits.' . $riskClassKey . '.' . $key, 'invalid_value');
                }
            }
        }
    }
}
