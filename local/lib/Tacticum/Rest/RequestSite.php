<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class RequestSite
{
    /** Return true when the site-specific origin policy handled the request. */
    public static function validateOriginContext(?string $siteContext): bool
    {
        if ($siteContext !== null) {
            if ($siteContext !== 'divatech' || !\Tacticum\Divatech\Origin::allowed($_SERVER, defined('SITE_ID') ? SITE_ID : '')) {
                Response::error(403, 'invalid_origin', 'Invalid request origin.');
            }
            return true;
        }
        if (defined('SITE_ID') && SITE_ID === 's2') {
            Response::error(403, 'invalid_site', 'Endpoint is unavailable for this site.');
        }
        return false;
    }
}
