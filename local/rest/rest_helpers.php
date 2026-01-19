<?php
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Data\Cache;

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

function tacticum_rest_html_to_text(string $html): string
{
    if ($html === '') {
        return '';
    }

    $text = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
    $text = preg_replace('/<\/\s*(p|li)\s*>/i', "\n", $text);
    $text = strip_tags($text);
    $text = htmlspecialchars_decode($text, ENT_QUOTES | ENT_HTML5);
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/ *\n */', "\n", $text);
    $text = preg_replace("/\n{2,}/", "\n", $text);

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

function tacticum_rest_validate_origin(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $rest = tacticum_rest_get_config_section('rest');
    $allowed_origins = tacticum_rest_get_allowed_origins();
    $allow_no_origin = (bool)($rest['allow_no_origin'] ?? false);

    $origin_host = $origin ? (string)parse_url($origin, PHP_URL_HOST) : '';
    if ($origin_host !== '' && tacticum_rest_is_allowed_origin($origin_host, $allowed_origins)) {
        return;
    }

    $referer_host = $referer ? (string)parse_url($referer, PHP_URL_HOST) : '';
    if ($referer_host !== '' && tacticum_rest_is_allowed_origin($referer_host, $allowed_origins)) {
        return;
    }

    if ($origin_host === '' && $referer_host === '') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== '' && tacticum_rest_is_allowed_origin($host, $allowed_origins)) {
            return;
        }
        if (empty($allowed_origins) && $allow_no_origin) {
            return;
        }
    }

    tacticum_rest_error(403, 'invalid_origin', 'Недопустимый источник запроса.');
}

function tacticum_rest_check_csrf(?array $data = null): void
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

    $session_cookie = session_name();
    $has_session_cookie = $session_cookie !== '' && isset($_COOKIE[$session_cookie]);
    if ($has_session_cookie && bitrix_sessid() !== '') {
        return;
    }

    tacticum_rest_error(403, 'invalid_csrf', 'Некорректный токен безопасности.');
}

function tacticum_rest_rate_limit(string $action, int $limit = 20, int $ttl = 60): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
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

function tacticum_rest_mask_pii(array $payload): array
{
    $masked = [];
    foreach ($payload as $key => $value) {
        if (is_array($value)) {
            $masked[$key] = tacticum_rest_mask_pii($value);
            continue;
        }

        if ($key === 'email' && is_string($value)) {
            $masked[$key] = tacticum_rest_mask_email($value);
            continue;
        }

        if ($key === 'phone' && is_string($value)) {
            $masked[$key] = tacticum_rest_mask_phone($value);
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
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
