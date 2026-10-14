<?php

declare(strict_types=1);

namespace Tacticum\Divatech;

final class Origin
{
    public static function allowed(array $server, string $siteId): bool
    {
        if ($siteId !== 's2' || !in_array(strtolower((string)($server['HTTP_HOST'] ?? '')), ['divatech.space', 'divatech.space:443', 'divatech.space:80'], true)) {
            return false;
        }
        // An explicit bad Origin cannot be rescued by Referer. No originless automation.
        if (isset($server['HTTP_ORIGIN'])) {
            return in_array($server['HTTP_ORIGIN'], ['https://divatech.space', 'https://divatech.space:443'], true);
        }
        $url = parse_url((string)($server['HTTP_REFERER'] ?? ''));
        return is_array($url) && ($url['scheme'] ?? '') === 'https'
            && ($url['host'] ?? '') === 'divatech.space' && ($url['port'] ?? 443) === 443
            && !isset($url['user']) && !isset($url['pass']);
    }
}
