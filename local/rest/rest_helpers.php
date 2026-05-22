<?php
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;

function tacticum_rest_get_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $config = [];
    $config_path = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/tacticum_config.php';
    if (file_exists($config_path)) {
        $loaded = include $config_path;
        if (is_array($loaded)) {
            $config = $loaded;
        }
    }

    return $config;
}

function tacticum_rest_get_config_section(string $section): array
{
    $config = tacticum_rest_get_config();
    $section_data = $config[$section] ?? [];
    return is_array($section_data) ? $section_data : [];
}

function tacticum_rest_get_iblock_id(string $key, int $default = 0): int
{
    $iblocks = tacticum_rest_get_config_section('iblocks');
    if (array_key_exists($key, $iblocks)) {
        return (int)$iblocks[$key];
    }

    return $default;
}

function tacticum_rest_validate_config(array $scopes = ['api', 'ai', 'telegram', 'offer', 'rest']): array
{
    $errors = [];
    $scopes = array_values(array_unique($scopes));

    $addError = static function (string $key, string $code) use (&$errors): void {
        $errors[] = [
            'key' => $key,
            'code' => $code,
        ];
    };

    $checkIblock = function (string $key) use ($addError): void {
        if (tacticum_rest_get_iblock_id($key) <= 0) {
            $addError('iblocks.' . $key, 'missing_or_invalid');
        }
    };

    $checkHttpsUrl = function (string $key) use ($addError): void {
        $value = trim(tacticum_rest_get_ai_setting($key));
        if ($value === '') {
            $addError('base_urls.' . $key, 'missing');
            return;
        }
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            $addError('base_urls.' . $key, 'invalid_url');
            return;
        }
        $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
        if ($scheme !== 'https') {
            $addError('base_urls.' . $key, 'https_required');
        }
    };

    if (in_array('api', $scopes, true)) {
        foreach (['cases', 'faq', 'rates', 'services'] as $key) {
            $checkIblock($key);
        }

        $api = tacticum_rest_get_config_section('api');
        $defaultTtl = $api['cache_ttl_default'] ?? null;
        if ($defaultTtl !== null && !is_numeric($defaultTtl)) {
            $addError('api.cache_ttl_default', 'invalid_type');
        }

        $ttlByAction = $api['cache_ttl'] ?? [];
        if ($ttlByAction !== [] && !is_array($ttlByAction)) {
            $addError('api.cache_ttl', 'invalid_type');
        } elseif (is_array($ttlByAction)) {
            foreach ($ttlByAction as $key => $ttl) {
                if (!is_numeric($ttl)) {
                    $addError('api.cache_ttl.' . (string)$key, 'invalid_type');
                }
            }
        }
    }

    if (in_array('offer', $scopes, true)) {
        $checkIblock('offer');
    }

    if (in_array('content', $scopes, true)) {
        foreach (['vacancies', 'feedback', 'team', 'policies', 'aiagents'] as $key) {
            $checkIblock($key);
        }
    }

    if (in_array('ai', $scopes, true)) {
        $checkHttpsUrl('AI_SERVICE_BASE_URL');
    }

    if (in_array('telegram', $scopes, true)) {
        $checkHttpsUrl('TELEGRAM_RESOLVER_URL');
    }

    if (in_array('rest', $scopes, true)) {
        $rest = tacticum_rest_get_config_section('rest');
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
    }

    return $errors;
}

function tacticum_rest_response(bool $success, string $code, ?string $message, array $extra = [], int $status = 200): void
{
    http_response_code($status);
    $payload = [
        'success' => $success,
        'code' => $code,
    ];

    if ($message !== null) {
        $payload['message'] = $message;
    }

    if (!empty($extra)) {
        $payload = array_merge($payload, $extra);
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function tacticum_rest_error(int $status, string $code, string $message, array $extra = []): void
{
    tacticum_rest_response(false, $code, $message, $extra, $status);
}

function tacticum_rest_require_method(string $method): void
{
    if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== strtoupper($method)) {
        tacticum_rest_error(405, 'method_not_allowed', 'Метод запроса не поддерживается.');
    }
}

function tacticum_rest_read_json_body(string $message = 'Некорректные данные формы.'): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        tacticum_rest_error(400, 'invalid_json', $message);
    }

    return $data;
}

function tacticum_rest_html_to_text(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $html = str_replace(["\\r\\n", "\\n", "\\r", "\\t"], ["\n", "\n", "\n", "\t"], $html);
    $html = preg_replace('~\R~u', "\n", $html);

    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $html = str_replace(
        ["\xC2\xA0", "\xE2\x80\xAF", "\xE2\x80\x89"],
        " ",
        $html
    );

    $html = preg_replace('~<\s*br\s*/?\s*>~iu', "\n", $html);
    $html = preg_replace('~<\s*(p|div|section|article|blockquote|h[1-6])\b[^>]*>~iu', "\n", $html);
    $html = preg_replace('~</\s*(p|div|section|article|blockquote|h[1-6])\s*>~iu', "\n\n", $html);
    $html = preg_replace('~<\s*li\b[^>]*>~iu', "• ", $html);
    $html = preg_replace('~</\s*li\s*>~iu', "\n", $html);
    $html = preg_replace('~</\s*(ul|ol)\s*>~iu', "\n", $html);

    $text = strip_tags($html);

    $text = str_replace("\t", " ", $text);
    $text = preg_replace('~[ ]{2,}~u', ' ', $text);
    $text = preg_replace('~ *\n *~u', "\n", $text);
    $text = preg_replace("~\n{3,}~u", "\n\n", $text);
    $text = preg_replace('~\s+([.,;:!?])~u', '$1', $text);
    $text = preg_replace('~\s*—\s*~u', ' — ', $text);
    $text = preg_replace('~\s+\)~u', ')', $text);
    $text = preg_replace('~\s+%~u', '%', $text);
    $text = preg_replace('~[ ]{2,}~u', ' ', $text);
    $text = preg_replace('~ *\n *~u', "\n", $text);

    return trim($text);
}

function tacticum_rest_is_allowed_host(string $host): bool
{
    $host = strtolower($host);
    if ($host === 'tacticum.ru') {
        return true;
    }

    return substr($host, -11) === '.tacticum.ru';
}

function tacticum_rest_get_allowed_origins(): array
{
    $rest = tacticum_rest_get_config_section('rest');
    $origins = $rest['allowed_origins'] ?? [];
    return is_array($origins) ? $origins : [];
}

function tacticum_rest_normalize_ip(string $ip): string
{
    $ip = trim($ip);
    if ($ip === '') {
        return '';
    }

    $normalized = filter_var($ip, FILTER_VALIDATE_IP);
    return $normalized === false ? '' : $normalized;
}

function tacticum_rest_is_allowed_ip(string $ip, array $allowed_ips): bool
{
    $ip = tacticum_rest_normalize_ip($ip);
    if ($ip === '' || empty($allowed_ips)) {
        return false;
    }

    $ip_binary = inet_pton($ip);
    if ($ip_binary === false) {
        return false;
    }

    $ip_length = strlen($ip_binary);

    foreach ($allowed_ips as $allowed) {
        $allowed = trim((string)$allowed);
        if ($allowed === '') {
            continue;
        }

        if (strpos($allowed, '/') !== false) {
            [$network, $prefix] = array_pad(explode('/', $allowed, 2), 2, '');
            $network = tacticum_rest_normalize_ip($network);
            if ($network === '' || $prefix === '' || !ctype_digit($prefix)) {
                continue;
            }

            $network_binary = inet_pton($network);
            if ($network_binary === false || strlen($network_binary) !== $ip_length) {
                continue;
            }

            $prefix_length = (int)$prefix;
            $max_prefix = $ip_length * 8;
            if ($prefix_length < 0 || $prefix_length > $max_prefix) {
                continue;
            }

            $bytes = intdiv($prefix_length, 8);
            $bits = $prefix_length % 8;

            if ($bytes > 0 && substr($ip_binary, 0, $bytes) !== substr($network_binary, 0, $bytes)) {
                continue;
            }

            if ($bits === 0) {
                return true;
            }

            $mask = (~(0xff >> $bits)) & 0xff;
            if (isset($ip_binary[$bytes], $network_binary[$bytes])) {
                if ((ord($ip_binary[$bytes]) & $mask) === (ord($network_binary[$bytes]) & $mask)) {
                    return true;
                }
            }
            continue;
        }

        $allowed_ip = tacticum_rest_normalize_ip($allowed);
        if ($allowed_ip !== '' && $allowed_ip === $ip) {
            return true;
        }
    }

    return false;
}

function tacticum_rest_get_client_ip(): string
{
    $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
    $forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    $rest = tacticum_rest_get_config_section('rest');
    $trusted_proxies = $rest['trusted_proxies'] ?? [];
    if (!is_array($trusted_proxies)) {
        $trusted_proxies = [];
    }

    if ($forwarded_for !== '' && $remote_addr !== '' && tacticum_rest_is_allowed_ip($remote_addr, $trusted_proxies)) {
        $parts = explode(',', $forwarded_for);
        foreach ($parts as $part) {
            $candidate = tacticum_rest_normalize_ip(trim((string)$part));
            if ($candidate !== '') {
                return $candidate;
            }
        }
    }

    return tacticum_rest_normalize_ip($remote_addr);
}

/**
 * Normalize iblock property metadata for API responses.
 *
 * @param array $property
 * @return array{type:string,multiple:bool,value:mixed}|array{type:string,multiple:bool,values:array}
 */
function tacticum_api_normalize_property(array $property): array
{
    $property_type = (string)($property['PROPERTY_TYPE'] ?? '');
    $user_type = (string)($property['USER_TYPE'] ?? '');
    $type = $property_type;
    if ($user_type !== '') {
        $type = $type === '' ? $user_type : $type . ':' . $user_type;
    }

    $multiple = ($property['MULTIPLE'] ?? 'N') === 'Y';
    $value = $property['VALUE'] ?? null;

    if ($multiple) {
        $values = [];
        if (is_array($value)) {
            $values = array_values($value);
        } elseif ($value !== null && $value !== '') {
            $values = [$value];
        }

        return [
            'type' => $type,
            'multiple' => true,
            'values' => $values,
        ];
    }

    return [
        'type' => $type,
        'multiple' => false,
        'value' => $value,
    ];
}

function tacticum_rest_is_allowed_origin(string $host, array $allowed_origins = []): bool
{
    $host = strtolower($host);
    if ($host === '') {
        return false;
    }

    if (empty($allowed_origins)) {
        return tacticum_rest_is_allowed_host($host);
    }

    foreach ($allowed_origins as $allowed) {
        $allowed = strtolower(trim((string)$allowed));
        if ($allowed === '') {
            continue;
        }

        if ($allowed === '*') {
            return true;
        }

        $allowed_host = $allowed;
        if (strpos($allowed, '://') !== false) {
            $allowed_host = (string)parse_url($allowed, PHP_URL_HOST);
        }

        if ($allowed_host === '') {
            continue;
        }

        if (strpos($allowed_host, '*.') === 0 || strpos($allowed_host, '.') === 0) {
            $suffix = substr($allowed_host, 1);
            if ($suffix !== '' && substr($host, -strlen($suffix)) === $suffix) {
                return true;
            }
            continue;
        }

        if ($host === $allowed_host) {
            return true;
        }
    }

    return false;
}

function tacticum_rest_normalize_host(string $host): string
{
    $host = strtolower(trim($host));
    if ($host === '') {
        return '';
    }

    if ($host[0] === '[') {
        $end = strpos($host, ']');
        return $end === false ? $host : substr($host, 0, $end + 1);
    }

    $colon_pos = strpos($host, ':');
    if ($colon_pos === false) {
        return $host;
    }

    return substr($host, 0, $colon_pos);
}

function tacticum_rest_validate_origin(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $rest = tacticum_rest_get_config_section('rest');
    $allowed_origins = tacticum_rest_get_allowed_origins();
    $allow_no_origin = (bool)($rest['allow_no_origin'] ?? false);
    $origin_allowed = false;

    $origin_host = $origin ? (string)parse_url($origin, PHP_URL_HOST) : '';
    $origin_host = tacticum_rest_normalize_host($origin_host);
    if ($origin_host !== '' && tacticum_rest_is_allowed_origin($origin_host, $allowed_origins)) {
        $origin_allowed = true;
    }

    $referer_host = $referer ? (string)parse_url($referer, PHP_URL_HOST) : '';
    $referer_host = tacticum_rest_normalize_host($referer_host);
    if ($referer_host !== '' && tacticum_rest_is_allowed_origin($referer_host, $allowed_origins)) {
        $origin_allowed = true;
    }

    if ($origin_host === '' && $referer_host === '') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $host = tacticum_rest_normalize_host($host);
        if ($host !== '' && tacticum_rest_is_allowed_origin($host, $allowed_origins)) {
            $origin_allowed = true;
        }
        if (empty($allowed_origins) && $allow_no_origin) {
            $origin_allowed = true;
        }
    }

    $allowed_ips = $rest['allowed_ips'] ?? [];
    if (is_array($allowed_ips) && !empty($allowed_ips)) {
        $client_ip = tacticum_rest_get_client_ip();
        if ($client_ip === '' || !tacticum_rest_is_allowed_ip($client_ip, $allowed_ips)) {
            tacticum_rest_error(403, 'invalid_ip', 'Недопустимый IP адрес источника.');
        }
    }

    if ($origin_allowed) {
        return;
    }

    tacticum_rest_error(403, 'invalid_origin', 'Недопустимый источник запроса.');
}

function tacticum_rest_has_allowed_browser_source(): bool
{
    $allowed_origins = tacticum_rest_get_allowed_origins();
    if (empty($allowed_origins)) {
        return false;
    }

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $origin_host = $origin ? (string)parse_url($origin, PHP_URL_HOST) : '';
    $origin_host = tacticum_rest_normalize_host($origin_host);
    if ($origin_host !== '' && tacticum_rest_is_allowed_origin($origin_host, $allowed_origins)) {
        return true;
    }

    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $referer_host = $referer ? (string)parse_url($referer, PHP_URL_HOST) : '';
    $referer_host = tacticum_rest_normalize_host($referer_host);
    return $referer_host !== '' && tacticum_rest_is_allowed_origin($referer_host, $allowed_origins);
}

function tacticum_rest_check_csrf(?array $data = null, bool $allowAllowedBrowserSource = false): void
{
    $sessid = '';
    if (is_array($data) && isset($data['sessid'])) {
        $sessid = (string)$data['sessid'];
    }

    if ($sessid === '' && isset($_SERVER['HTTP_X_BITRIX_SESSID'])) {
        $sessid = (string)$_SERVER['HTTP_X_BITRIX_SESSID'];
    }

    if ($sessid === '' && isset($_REQUEST['sessid'])) {
        $sessid = (string)$_REQUEST['sessid'];
    }

    if ($sessid !== '') {
        if ($sessid !== bitrix_sessid()) {
            tacticum_rest_error(403, 'invalid_csrf', 'Некорректный токен безопасности.');
        }
        return;
    }

    if ($allowAllowedBrowserSource && tacticum_rest_has_allowed_browser_source()) {
        return;
    }

    tacticum_rest_error(403, 'invalid_csrf', 'Требуется токен безопасности.');
}

function tacticum_rest_rate_limit(string $action, int $limit = 20, int $ttl = 60): void
{
    $ip = tacticum_rest_get_client_ip();
    if ($ip === '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    $sessid = bitrix_sessid();
    $cache_key = 'tacticum_rest_' . $action . '_' . md5($ip . '|' . $sessid);
    $cache_dir = '/tacticum/rest_rate';
    $cache = Cache::createInstance();

    $count = 1;
    if ($cache->initCache($ttl, $cache_key, $cache_dir)) {
        $data = $cache->getVars();
        $count = ((int)($data['count'] ?? 0)) + 1;
    }

    $cache->clean($cache_key, $cache_dir);
    if ($cache->startDataCache($ttl, $cache_key, $cache_dir)) {
        $cache->endDataCache(['count' => $count, 'ts' => time()]);
    }

    if ($count > $limit) {
        tacticum_rest_error(429, 'rate_limited', 'Слишком много запросов. Попробуйте позже.');
    }
}

function tacticum_api_bootstrap(string $action): int
{
    tacticum_rest_validate_origin();
    tacticum_rest_rate_limit($action);

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        tacticum_rest_error(405, 'method_not_allowed', 'Метод запроса не поддерживается.');
    }

    if (!Loader::includeModule('iblock')) {
        tacticum_rest_error(500, 'iblock_missing', 'Модуль инфоблоков не установлен.');
    }

    $iblockId = tacticum_rest_get_iblock_id($action);
    if ($iblockId <= 0) {
        tacticum_rest_error(500, 'iblock_not_configured', 'Инфоблок не настроен.');
    }

    return $iblockId;
}

function tacticum_api_cache_ttl(string $action, int $default = 300): int
{
    $api = tacticum_rest_get_config_section('api');
    $ttl = $api['cache_ttl_default'] ?? $default;
    if (isset($api['cache_ttl']) && is_array($api['cache_ttl']) && array_key_exists($action, $api['cache_ttl'])) {
        $ttl = $api['cache_ttl'][$action];
    }

    $ttl = (int)$ttl;
    return $ttl < 0 ? 0 : $ttl;
}

function tacticum_api_cached_payload(string $action, int $iblockId, callable $builder, array $cacheContext = []): array
{
    $ttl = tacticum_api_cache_ttl($action);
    if ($ttl <= 0) {
        $payload = $builder();
        return is_array($payload) ? $payload : [];
    }

    $cache = Cache::createInstance();
    $cacheKey = 'tacticum_api_' . $action . '_' . md5($iblockId . '|' . serialize($cacheContext));
    $cacheDir = '/tacticum/api';

    if ($cache->initCache($ttl, $cacheKey, $cacheDir)) {
        $vars = $cache->getVars();
        if (is_array($vars) && isset($vars['payload']) && is_array($vars['payload'])) {
            return $vars['payload'];
        }
    }

    $payload = $builder();
    if (!is_array($payload)) {
        $payload = [];
    }

    if ($cache->startDataCache($ttl, $cacheKey, $cacheDir)) {
        $cache->endDataCache(['payload' => $payload]);
    }

    return $payload;
}

function tacticum_api_fetch_elements(int $iblockId, array $select, array $filter = [], array $order = ['SORT' => 'ASC'])
{
    $filter = array_merge([
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
    ], $filter);

    return CIBlockElement::GetList($order, $filter, false, false, $select);
}

function tacticum_rest_normalize_phone(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone);
    if ($digits === '') {
        return '';
    }

    $has_plus = strpos($phone, '+') === 0;
    return $has_plus ? '+' . $digits : $digits;
}

function tacticum_rest_is_valid_phone(string $phone): bool
{
    $normalized = tacticum_rest_normalize_phone($phone);
    if ($normalized === '') {
        return false;
    }

    return (bool)preg_match('/^\+?\d{7,15}$/', $normalized);
}

function tacticum_rest_mask_email(string $email): string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }

    [$user, $domain] = explode('@', $email, 2);
    $length = mb_strlen($user);
    if ($length <= 2) {
        $masked_user = str_repeat('*', $length);
    } else {
        $masked_user = mb_substr($user, 0, 1) . str_repeat('*', $length - 2) . mb_substr($user, -1);
    }

    return $masked_user . '@' . $domain;
}

function tacticum_rest_mask_phone(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone);
    if ($digits === '') {
        return $phone;
    }

    $masked = str_repeat('*', max(0, strlen($digits) - 2)) . substr($digits, -2);
    return $masked;
}

function tacticum_rest_mask_free_text(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    return '[masked:' . mb_strlen($value) . ']';
}

function tacticum_rest_is_sensitive_log_key(string $key): bool
{
    $key = strtolower($key);
    if (in_array($key, [
        'name',
        'client_name',
        'first_name',
        'last_name',
        'patronymic',
        'company',
        'message',
        'task',
        'description',
        'project',
        'summary',
        'response',
        'user_message',
        'comment',
        'comments',
    ], true)) {
        return true;
    }

    return (bool)preg_match('~(name|message|task|description|summary|comment|response|text)$~', $key);
}

function tacticum_rest_mask_pii(array $payload): array
{
    $masked = [];
    foreach ($payload as $key => $value) {
        $keyString = is_string($key) ? $key : (string)$key;

        if (is_array($value)) {
            $masked[$key] = tacticum_rest_mask_pii($value);
            continue;
        }

        if ($keyString === 'email' && is_string($value)) {
            $masked[$key] = tacticum_rest_mask_email($value);
            continue;
        }

        if ($keyString === 'phone' && is_string($value)) {
            $masked[$key] = tacticum_rest_mask_phone($value);
            continue;
        }

        if (is_string($value)) {
            $masked[$key] = tacticum_rest_is_sensitive_log_key($keyString)
                ? tacticum_rest_mask_free_text($value)
                : tacticum_rest_mask_string($value);
            continue;
        }

        $masked[$key] = $value;
    }

    return $masked;
}

function tacticum_rest_mask_string(string $value): string
{
    $value = preg_replace('/([A-Z0-9._%+-]+)@([A-Z0-9.-]+\.[A-Z]{2,})/i', '***@$2', $value);
    $value = preg_replace('/\+?\d[\d\s().-]{6,}\d/', '***', $value);
    return $value;
}

function tacticum_rest_get_ai_setting(string $key, string $default = ''): string
{
    $base_urls = tacticum_rest_get_config_section('base_urls');
    if (isset($base_urls[$key]) && $base_urls[$key] !== '') {
        return (string)$base_urls[$key];
    }

    $config = Configuration::getValue('ai_services');
    if (is_array($config) && isset($config[$key]) && $config[$key] !== '') {
        return (string)$config[$key];
    }

    return $default;
}

function tacticum_rest_get_required_https_ai_url(string $key, string $serviceLabel = 'сервиса обработки'): string
{
    $url = trim(tacticum_rest_get_ai_setting($key));
    if ($url === '') {
        tacticum_rest_error(500, 'config_error', 'Не настроен адрес ' . $serviceLabel . '.');
    }

    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        tacticum_rest_error(500, 'config_error', 'Некорректный адрес ' . $serviceLabel . '.');
    }

    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    if ($scheme !== 'https') {
        tacticum_rest_error(500, 'config_error', 'Адрес ' . $serviceLabel . ' должен использовать HTTPS.');
    }

    return $url;
}

function tacticum_rest_build_url(string $base_url, string $path): string
{
    if ($base_url === '') {
        return $path;
    }

    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

function tacticum_rest_apply_curl_defaults($ch): void
{
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
}

function tacticum_rest_post_json(string $endpoint_url, array $payload, string $context): array
{
    $ch = curl_init($endpoint_url);
    tacticum_rest_apply_curl_defaults($ch);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $response = curl_exec($ch);
    $result = [
        'response' => $response,
        'curl_error_no' => curl_errno($ch),
        'curl_error' => curl_error($ch),
        'http_status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'total_time' => curl_getinfo($ch, CURLINFO_TOTAL_TIME),
        'start_transfer_time' => curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME),
    ];

    tacticum_rest_log_tls_error($ch, $context);
    curl_close($ch);

    return $result;
}

function tacticum_rest_post_json_retry_without_group_id(string $endpoint_url, array $payload, string $context): array
{
    $result = tacticum_rest_post_json($endpoint_url, $payload, $context);
    $http_status = (int)($result['http_status'] ?? 0);
    if ($http_status >= 200 && $http_status < 300) {
        return $result;
    }

    if ((int)($result['curl_error_no'] ?? 0) !== 0) {
        return $result;
    }

    $group_id = trim((string)($payload['group_id'] ?? ''));
    if ($group_id === '') {
        return $result;
    }

    $response = $result['response'] ?? '';
    $response_length = is_string($response) ? strlen($response) : 0;
    AddMessage2Log(
        "Retry without group_id ({$context}): initial_http_status={$http_status}; response_length={$response_length}",
        $context . '_group_retry'
    );

    $retry_payload = $payload;
    unset($retry_payload['group_id']);

    $retry_result = tacticum_rest_post_json($endpoint_url, $retry_payload, $context . '_without_group_id');
    $retry_result['retried_without_group_id'] = true;
    $retry_result['initial_http_status'] = $http_status;

    return $retry_result;
}

function tacticum_rest_fail_on_curl_error(array $result, string $context, string $message = 'Ошибка соединения с внешним сервисом.'): void
{
    $curl_error_no = (int)($result['curl_error_no'] ?? 0);
    if ($curl_error_no === 0) {
        return;
    }

    $curl_error = (string)($result['curl_error'] ?? '');
    $total_time = (float)($result['total_time'] ?? 0);
    $start_transfer_time = (float)($result['start_transfer_time'] ?? 0);
    $http_status = (int)($result['http_status'] ?? 0);

    AddMessage2Log("Curl error ({$context}): errno={$curl_error_no}; error={$curl_error}; total_time={$total_time}; start_transfer_time={$start_transfer_time}", $context . '_error');
    $code = ($curl_error_no === CURLE_OPERATION_TIMEOUTED) ? 'upstream_timeout' : 'curl_error';
    tacticum_rest_error(502, $code, $message, [
        'upstream_status' => $http_status,
        'upstream_time' => $total_time,
    ]);
}

function tacticum_rest_log_tls_error($ch, string $context): void
{
    $error_no = curl_errno($ch);
    if ($error_no === 0) {
        return;
    }

    $tls_errors = [
        CURLE_SSL_CONNECT_ERROR,
        CURLE_SSL_CERTPROBLEM,
        CURLE_SSL_CACERT,
//        CURLE_PEER_FAILED_VERIFICATION,
        CURLE_SSL_CACERT_BADFILE,
//        CURLE_SSL_ISSUER_ERROR,
    ];

    if (!in_array($error_no, $tls_errors, true)) {
        return;
    }

    $info = curl_getinfo($ch);
    $url = $info['url'] ?? '';
    $message = sprintf(
        'TLS error (%s): errno=%d; error=%s; url=%s',
        $context,
        $error_no,
        curl_error($ch),
        $url
    );

    AddMessage2Log($message, 'tacticum_tls');
}
