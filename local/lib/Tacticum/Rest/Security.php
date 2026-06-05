<?php
declare(strict_types=1);
namespace Tacticum\Rest;
final class Security
{
    public static function isAllowedHost(string $host): bool
    {
        $host = strtolower($host);
        return $host === 'tacticum.ru' || substr($host, -11) === '.tacticum.ru';
    }

    public static function allowedOrigins(): array
    {
        $origins = Config::section('rest')['allowed_origins'] ?? [];
        return is_array($origins) ? $origins : [];
    }

    public static function normalizeIp(string $ip): string
    {
        $ip = trim($ip);
        if ($ip === '') {
            return '';
        }

        $normalized = filter_var($ip, FILTER_VALIDATE_IP);
        return $normalized === false ? '' : $normalized;
    }

    public static function isAllowedIp(string $ip, array $allowedIps): bool
    {
        $ip = self::normalizeIp($ip);
        if ($ip === '' || empty($allowedIps)) {
            return false;
        }
        $ipBinary = inet_pton($ip);
        if ($ipBinary === false) {
            return false;
        }
        $ipLength = strlen($ipBinary);
        foreach ($allowedIps as $allowed) {
            $allowed = trim((string)$allowed);
            if ($allowed === '') {
                continue;
            }
            if (strpos($allowed, '/') !== false && self::matchesNetwork($ipBinary, $ipLength, $allowed)) {
                return true;
            }
            if (strpos($allowed, '/') === false && self::normalizeIp($allowed) === $ip) {
                return true;
            }
        }
        return false;
    }

    public static function clientIp(): string
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        $trustedProxies = Config::section('rest')['trusted_proxies'] ?? [];
        if (!is_array($trustedProxies)) {
            $trustedProxies = [];
        }

        if ($forwardedFor !== '' && $remoteAddr !== '' && self::isAllowedIp($remoteAddr, $trustedProxies)) {
            foreach (explode(',', $forwardedFor) as $part) {
                $candidate = self::normalizeIp(trim((string)$part));
                if ($candidate !== '') {
                    return $candidate;
                }
            }
        }
        return self::normalizeIp($remoteAddr);
    }

    public static function isAllowedOrigin(string $host, array $allowedOrigins = []): bool
    {
        $host = strtolower($host);
        if ($host === '') {
            return false;
        }
        if (empty($allowedOrigins)) {
            return self::isAllowedHost($host);
        }
        foreach ($allowedOrigins as $allowed) {
            $allowed = strtolower(trim((string)$allowed));
            if ($allowed === '') {
                continue;
            }
            if ($allowed === '*') {
                return true;
            }
            $allowedHost = strpos($allowed, '://') !== false ? (string)parse_url($allowed, PHP_URL_HOST) : $allowed;
            if ($allowedHost === '') {
                continue;
            }
            if (strpos($allowedHost, '*.') === 0 || strpos($allowedHost, '.') === 0) {
                $suffix = substr($allowedHost, 1);
                if ($suffix !== '' && substr($host, -strlen($suffix)) === $suffix) {
                    return true;
                }
                continue;
            }
            if ($host === $allowedHost) {
                return true;
            }
        }
        return false;
    }

    public static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host === '') {
            return '';
        }
        if ($host[0] === '[') {
            $end = strpos($host, ']');
            return $end === false ? $host : substr($host, 0, $end + 1);
        }
        $colonPos = strpos($host, ':');
        return $colonPos === false ? $host : substr($host, 0, $colonPos);
    }

    public static function validateOrigin(): void
    {
        $rest = Config::section('rest');
        $allowedOrigins = self::allowedOrigins();
        $allowNoOrigin = (bool)($rest['allow_no_origin'] ?? false);
        $originAllowed = false;
        $originHost = self::normalizeHost((string)parse_url((string)($_SERVER['HTTP_ORIGIN'] ?? ''), PHP_URL_HOST));
        if ($originHost !== '' && self::isAllowedOrigin($originHost, $allowedOrigins)) {
            $originAllowed = true;
        }
        $refererHost = self::normalizeHost((string)parse_url((string)($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_HOST));
        if ($refererHost !== '' && self::isAllowedOrigin($refererHost, $allowedOrigins)) {
            $originAllowed = true;
        }
        if ($originHost === '' && $refererHost === '') {
            $host = self::normalizeHost($_SERVER['HTTP_HOST'] ?? '');
            if ($host !== '' && self::isAllowedOrigin($host, $allowedOrigins)) {
                $originAllowed = true;
            }
            if (empty($allowedOrigins) && $allowNoOrigin) {
                $originAllowed = true;
            }
        }

        $allowedIps = $rest['allowed_ips'] ?? [];
        if (is_array($allowedIps) && !empty($allowedIps)) {
            $clientIp = self::clientIp();
            if ($clientIp === '' || !self::isAllowedIp($clientIp, $allowedIps)) {
                Response::error(403, 'invalid_ip', 'Недопустимый IP адрес источника.');
            }
        }
        if (!$originAllowed) {
            Response::error(403, 'invalid_origin', 'Недопустимый источник запроса.');
        }
    }

    public static function hasAllowedBrowserSource(): bool
    {
        $allowedOrigins = self::allowedOrigins();
        if (empty($allowedOrigins)) {
            return false;
        }
        $originHost = self::normalizeHost((string)parse_url((string)($_SERVER['HTTP_ORIGIN'] ?? ''), PHP_URL_HOST));
        if ($originHost !== '' && self::isAllowedOrigin($originHost, $allowedOrigins)) {
            return true;
        }
        $refererHost = self::normalizeHost((string)parse_url((string)($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_HOST));
        return $refererHost !== '' && self::isAllowedOrigin($refererHost, $allowedOrigins);
    }

    public static function checkCsrf(?array $data = null, bool $allowAllowedBrowserSource = false): void
    {
        $sessid = is_array($data) && isset($data['sessid']) ? (string)$data['sessid'] : '';
        $sessid = $sessid !== '' ? $sessid : (string)($_SERVER['HTTP_X_BITRIX_SESSID'] ?? '');
        $sessid = $sessid !== '' ? $sessid : (string)($_REQUEST['sessid'] ?? '');
        if ($sessid !== '') {
            if ($sessid !== bitrix_sessid()) {
                Response::error(403, 'invalid_csrf', 'Некорректный токен безопасности.');
            }
            return;
        }
        if ($allowAllowedBrowserSource && self::hasAllowedBrowserSource()) {
            return;
        }
        Response::error(403, 'invalid_csrf', 'Требуется токен безопасности.');
    }

    private static function matchesNetwork(string $ipBinary, int $ipLength, string $allowed): bool
    {
        [$network, $prefix] = array_pad(explode('/', $allowed, 2), 2, '');
        $network = self::normalizeIp($network);
        if ($network === '' || $prefix === '' || !ctype_digit($prefix)) {
            return false;
        }
        $networkBinary = inet_pton($network);
        if ($networkBinary === false || strlen($networkBinary) !== $ipLength) {
            return false;
        }
        $prefixLength = (int)$prefix;
        $maxPrefix = $ipLength * 8;
        if ($prefixLength < 0 || $prefixLength > $maxPrefix) {
            return false;
        }
        $bytes = intdiv($prefixLength, 8);
        $bits = $prefixLength % 8;
        if ($bytes > 0 && substr($ipBinary, 0, $bytes) !== substr($networkBinary, 0, $bytes)) {
            return false;
        }
        if ($bits === 0) {
            return true;
        }
        $mask = (~(0xff >> $bits)) & 0xff;
        return isset($ipBinary[$bytes], $networkBinary[$bytes])
            && (ord($ipBinary[$bytes]) & $mask) === (ord($networkBinary[$bytes]) & $mask);
    }
}
