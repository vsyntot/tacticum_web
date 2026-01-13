<?php
use Bitrix\Main\Data\Cache;

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

function tacticum_rest_is_allowed_host(string $host): bool
{
    $host = strtolower($host);
    if ($host === 'tacticum.ru') {
        return true;
    }

    return substr($host, -11) === '.tacticum.ru';
}

function tacticum_rest_validate_origin(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    $origin_host = $origin ? (string)parse_url($origin, PHP_URL_HOST) : '';
    if ($origin_host !== '' && tacticum_rest_is_allowed_host($origin_host)) {
        return;
    }

    $referer_host = $referer ? (string)parse_url($referer, PHP_URL_HOST) : '';
    if ($referer_host !== '' && tacticum_rest_is_allowed_host($referer_host)) {
        return;
    }

    if ($origin_host === '' && $referer_host === '') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== '' && tacticum_rest_is_allowed_host($host)) {
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
