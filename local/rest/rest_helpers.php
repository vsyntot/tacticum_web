<?php

use Tacticum\Rest\Api;
use Tacticum\Rest\Config;
use Tacticum\Rest\ConfigValidator;
use Tacticum\Rest\Masker;
use Tacticum\Rest\Outbound;
use Tacticum\Rest\RateLimiter;
use Tacticum\Rest\Response;
use Tacticum\Rest\Security;
use Tacticum\Rest\Text;

if (!class_exists(Config::class)) {
    $autoloadPath = (string)($_SERVER['DOCUMENT_ROOT'] ?? '') . '/local/php_interface/include/autoload.php';
    if ($autoloadPath !== '' && is_file($autoloadPath)) {
        require_once $autoloadPath;
    }
}

function tacticum_rest_get_config(): array { return Config::all(); }
function tacticum_rest_get_config_section_defaults(string $section): array { return Config::sectionDefaults($section); }
function tacticum_rest_send_noindex_header(): void { Response::sendNoindexHeader(); }
function tacticum_rest_get_config_section(string $section): array { return Config::section($section); }
function tacticum_rest_get_iblock_id(string $key, int $default = 0): int { return Config::iblockId($key, $default); }
function tacticum_rest_validate_config(array $scopes = ['api', 'ai', 'telegram', 'offer', 'rest']): array
{
    return ConfigValidator::validate($scopes);
}
function tacticum_rest_response(bool $success, string $code, ?string $message, array $extra = [], int $status = 200): void
{
    Response::send($success, $code, $message, $extra, $status);
}
function tacticum_rest_error(int $status, string $code, string $message, array $extra = []): void
{
    Response::error($status, $code, $message, $extra);
}
function tacticum_rest_require_method(string $method): void { Response::requireMethod($method); }
function tacticum_rest_read_json_body(string $message = 'Некорректные данные формы.'): array
{
    return Response::readJsonBody($message);
}
function tacticum_rest_html_to_text(string $html): string { return Text::htmlToText($html); }
function tacticum_rest_is_allowed_host(string $host): bool { return Security::isAllowedHost($host); }
function tacticum_rest_get_allowed_origins(): array { return Security::allowedOrigins(); }
function tacticum_rest_normalize_ip(string $ip): string { return Security::normalizeIp($ip); }
function tacticum_rest_is_allowed_ip(string $ip, array $allowed_ips): bool { return Security::isAllowedIp($ip, $allowed_ips); }
function tacticum_rest_get_client_ip(): string { return Security::clientIp(); }
function tacticum_api_normalize_property(array $property): array { return Api::normalizeProperty($property); }
function tacticum_rest_is_allowed_origin(string $host, array $allowed_origins = []): bool
{
    return Security::isAllowedOrigin($host, $allowed_origins);
}
function tacticum_rest_normalize_host(string $host): string { return Security::normalizeHost($host); }
function tacticum_rest_validate_origin(): void { Security::validateOrigin(); }
function tacticum_rest_has_allowed_browser_source(): bool { return Security::hasAllowedBrowserSource(); }
function tacticum_rest_check_csrf(?array $data = null, bool $allowAllowedBrowserSource = false): void
{
    Security::checkCsrf($data, $allowAllowedBrowserSource);
}
function tacticum_rest_rate_limit(string $action, int $limit = 20, int $ttl = 60): void
{
    RateLimiter::hit($action, $limit, $ttl);
}
function tacticum_rest_rate_limit_classes(): array { return RateLimiter::classes(); }
function tacticum_rest_rate_limit_class_settings(string $riskClass): array { return RateLimiter::classSettings($riskClass); }
function tacticum_rest_rate_limit_by_class(string $riskClass, string $action): void
{
    RateLimiter::byClass($riskClass, $action);
}
function tacticum_api_bootstrap(string $action): int { return Api::bootstrap($action); }
function tacticum_api_cache_ttl(string $action, int $default = 300): int { return Api::cacheTtl($action, $default); }
function tacticum_api_cached_payload(string $action, int $iblockId, callable $builder, array $cacheContext = []): array
{
    return Api::cachedPayload($action, $iblockId, $builder, $cacheContext);
}
function tacticum_api_fetch_elements(int $iblockId, array $select, array $filter = [], array $order = ['SORT' => 'ASC'])
{
    return Api::fetchElements($iblockId, $select, $filter, $order);
}
function tacticum_api_fetch_content_items(int $iblockId, array $select, array $filter = [], array $order = ['SORT' => 'ASC']): array
{
    return Api::fetchContentItems($iblockId, $select, $filter, $order);
}
function tacticum_rest_normalize_phone(string $phone): string { return Text::normalizePhone($phone); }
function tacticum_rest_is_valid_phone(string $phone): bool { return Text::isValidPhone($phone); }
function tacticum_rest_mask_email(string $email): string { return Masker::email($email); }
function tacticum_rest_mask_phone(string $phone): string { return Masker::phone($phone); }
function tacticum_rest_mask_free_text(string $value): string { return Masker::freeText($value); }
function tacticum_rest_is_sensitive_log_key(string $key): bool { return Masker::isSensitiveLogKey($key); }
function tacticum_rest_mask_pii(array $payload): array { return Masker::pii($payload); }
function tacticum_rest_mask_string(string $value): string { return Masker::string($value); }
function tacticum_rest_get_ai_setting(string $key, string $default = ''): string { return Config::aiSetting($key, $default); }
function tacticum_rest_get_required_https_ai_url(string $key, string $serviceLabel = 'сервиса обработки'): string
{
    return Config::requiredHttpsAiUrl($key, $serviceLabel);
}
function tacticum_rest_get_ai_endpoint_path(string $key, string $default): string
{
    return Config::aiEndpointPath($key, $default);
}
function tacticum_rest_build_url(string $base_url, string $path): string { return Config::buildUrl($base_url, $path); }
function tacticum_rest_apply_curl_defaults($ch): void { Outbound::applyCurlDefaults($ch); }
function tacticum_rest_post_json(string $endpoint_url, array $payload, string $context): array
{
    return Outbound::postJson($endpoint_url, $payload, $context);
}
function tacticum_rest_post_json_retry_without_group_id(string $endpoint_url, array $payload, string $context): array
{
    return Outbound::postJsonRetryWithoutGroupId($endpoint_url, $payload, $context);
}
function tacticum_rest_submit_chat_agent_sale(
    array $payload,
    string $context,
    ?string $logPrefix = null,
    string $curlErrorMessage = 'Ошибка соединения с внешним сервисом.'
): array {
    return Outbound::submitChatAgentSale($payload, $context, $logPrefix, $curlErrorMessage);
}
function tacticum_rest_is_successful_upstream_response(array $result): bool
{
    return Outbound::isSuccessfulUpstreamResponse($result);
}
function tacticum_rest_fail_chat_agent_sale_upstream(
    array $result,
    string $context,
    string $message = 'Ошибка отправки во внешний сервис.'
): void {
    Outbound::failChatAgentSaleUpstream($result, $context, $message);
}
function tacticum_rest_fail_on_curl_error(array $result, string $context, string $message = 'Ошибка соединения с внешним сервисом.'): void
{
    Outbound::failOnCurlError($result, $context, $message);
}
